<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EntrySource;
use App\Models\DefaultActivity;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WeeklySlot;
use App\Support\HolidayCalendar;
use App\Support\Minutes;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriodImmutable;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FillMonth
{
    private const int END_OF_DAY = 1440;

    public function execute(User $user, CarbonImmutable $month): int
    {
        return DB::transaction(function () use ($user, $month): int {
            $calendar = new HolidayCalendar($user->holidays);
            $slots = $user->weeklySlots()->orderBy('start_minute')->get();
            $rotation = $this->rotation($user->defaultActivities()->get());
            $entriesByDate = $user->timeEntries()
                ->within($month->startOfMonth(), $month->endOfMonth())
                ->get()
                ->groupBy(fn (TimeEntry $entry): string => $entry->date->toDateString());

            $created = 0;

            foreach (CarbonPeriodImmutable::create($month->startOfMonth(), $month->endOfMonth()) as $day) {
                if ($day->isWeekend() || $calendar->nameOn($day) !== null) {
                    continue;
                }

                $entries = $entriesByDate->get($day->toDateString(), new Collection);
                $created += $this->applySchedule($user, $day, $slots, $entries);

                if ($rotation !== null) {
                    $created += $this->fillWithActivities($user, $day, $entries, $rotation);
                }
            }

            return $created;
        });
    }

    /**
     * @param  Collection<int, WeeklySlot>  $slots
     * @param  Collection<int, TimeEntry>  $entries
     */
    private function applySchedule(User $user, CarbonImmutable $day, Collection $slots, Collection $entries): int
    {
        $created = 0;

        foreach ($slots->filter(fn (WeeklySlot $slot): bool => $slot->appliesTo($day)) as $slot) {
            $scheduled = $entries->first(
                fn (TimeEntry $entry): bool => $entry->source === EntrySource::Schedule
                    && $entry->description === $slot->description,
            );

            if ($scheduled !== null) {
                $scheduled->update(['start_minute' => $slot->start_minute, 'end_minute' => $slot->end_minute]);

                continue;
            }

            if ($entries->contains(fn (TimeEntry $entry): bool => $entry->overlaps($slot->start_minute, $slot->end_minute))) {
                continue;
            }

            $entries->push($this->createEntry($user, $day, $slot->start_minute, $slot->end_minute, $slot->description, EntrySource::Schedule));
            $created++;
        }

        return $created;
    }

    /**
     * @param  Collection<int, TimeEntry>  $entries
     * @param  Generator<int, DefaultActivity>  $rotation
     */
    private function fillWithActivities(User $user, CarbonImmutable $day, Collection $entries, Generator $rotation): int
    {
        $morningStart = Minutes::fromClock(config('timesheet.morning_start'));
        $afternoonStart = Minutes::fromClock(config('timesheet.afternoon_start'));
        $missing = $user->dailyWorkloadMinutes() - $entries->sum(fn (TimeEntry $entry): int => $entry->durationMinutes());

        $shifts = [
            [$morningStart, $afternoonStart, min($missing, config('timesheet.morning_max_minutes'))],
            [$afternoonStart, self::END_OF_DAY, null],
        ];

        $created = 0;

        foreach ($shifts as [$from, $until, $budget]) {
            $budget ??= $missing;

            foreach ($this->freeWindows($entries, $from, $until) as [$start, $end]) {
                if ($budget <= 0) {
                    break;
                }

                $length = min($end - $start, $budget);
                $activity = $rotation->current();
                $rotation->next();

                $entries->push($this->createEntry($user, $day, $start, $start + $length, $activity->description, EntrySource::Activity));
                $budget -= $length;
                $missing -= $length;
                $created++;
            }
        }

        return $created;
    }

    /**
     * @param  Collection<int, TimeEntry>  $entries
     * @return list<array{int, int}>
     */
    private function freeWindows(Collection $entries, int $from, int $until): array
    {
        $windows = [];
        $cursor = $from;

        foreach ($entries->sortBy('start_minute') as $entry) {
            if ($entry->start_minute >= $until) {
                break;
            }

            if ($entry->start_minute > $cursor) {
                $windows[] = [$cursor, $entry->start_minute];
            }

            $cursor = max($cursor, $entry->end_minute);
        }

        if ($cursor < $until) {
            $windows[] = [$cursor, $until];
        }

        return $windows;
    }

    /**
     * @param  Collection<int, DefaultActivity>  $activities
     * @return Generator<int, DefaultActivity>|null
     */
    private function rotation(Collection $activities): ?Generator
    {
        if ($activities->isEmpty()) {
            return null;
        }

        $activities = $activities
            ->sortBy([
                fn (DefaultActivity $a, DefaultActivity $b): int => $b->priority->weight() <=> $a->priority->weight(),
                fn (DefaultActivity $a, DefaultActivity $b): int => strcmp($a->description, $b->description),
            ])
            ->values();

        return (function () use ($activities): Generator {
            $totalWeight = $activities->sum(fn (DefaultActivity $activity): int => $activity->priority->weight());
            $current = array_fill(0, $activities->count(), 0);

            while (true) {
                foreach ($activities as $index => $activity) {
                    $current[$index] += $activity->priority->weight();
                }

                $chosen = array_search(max($current), $current, true);
                $current[$chosen] -= $totalWeight;

                yield $activities[$chosen];
            }
        })();
    }

    private function createEntry(User $user, CarbonImmutable $day, int $start, int $end, string $description, EntrySource $source): TimeEntry
    {
        return $user->timeEntries()->create([
            'date' => $day->toDateString(),
            'start_minute' => $start,
            'end_minute' => $end,
            'description' => $description,
            'source' => $source,
        ]);
    }
}
