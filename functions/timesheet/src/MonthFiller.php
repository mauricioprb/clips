<?php

declare(strict_types=1);

namespace Clips;

use DateTimeImmutable;
use Generator;

final class MonthFiller
{
    private const int MORNING_START = 480;
    private const int AFTERNOON_START = 840;
    private const int MORNING_MAX_MINUTES = 240;
    private const int END_OF_DAY = 1440;

    /**
     * @param list<WeeklySlot> $slots
     * @param list<DefaultActivity> $activities
     * @param list<TimeEntry> $entries
     * @return array{create: list<TimeEntry>, reschedule: list<TimeEntry>}
     */
    public static function fill(
        Month $month,
        array $slots,
        array $activities,
        array $entries,
        HolidayCalendar $calendar,
        int $dailyTargetMinutes,
    ): array {
        usort($slots, fn(WeeklySlot $a, WeeklySlot $b): int => $a->startMinute <=> $b->startMinute);
        $rotation = self::rotation($activities);
        $entriesByDate = [];
        $plan = ['create' => [], 'reschedule' => []];

        foreach ($entries as $entry) {
            $entriesByDate[$entry->date][] = $entry;
        }

        foreach ($month->days() as $day) {
            $date = $day->format('Y-m-d');

            if ((int) $day->format('N') >= 6 || $calendar->nameOn($date) !== null) {
                continue;
            }

            $dayEntries = self::applySchedule($day, $slots, $entriesByDate[$date] ?? [], $plan);

            if ($rotation !== null) {
                self::fillWithActivities($date, $dayEntries, $rotation, $dailyTargetMinutes, $plan);
            }
        }

        return $plan;
    }

    /**
     * @param list<WeeklySlot> $slots
     * @param list<TimeEntry> $dayEntries
     * @param array{create: list<TimeEntry>, reschedule: list<TimeEntry>} $plan
     * @return list<TimeEntry>
     */
    private static function applySchedule(DateTimeImmutable $day, array $slots, array $dayEntries, array &$plan): array
    {
        foreach ($slots as $slot) {
            if (!$slot->appliesTo($day)) {
                continue;
            }

            $scheduledIndex = array_find_key(
                $dayEntries,
                fn(TimeEntry $entry): bool => $entry->source === EntrySource::Schedule
                    && $entry->description === $slot->description,
            );

            if ($scheduledIndex !== null) {
                $scheduled = $dayEntries[$scheduledIndex];

                if ($scheduled->startMinute !== $slot->startMinute || $scheduled->endMinute !== $slot->endMinute) {
                    $dayEntries[$scheduledIndex] = $scheduled->withTimes($slot->startMinute, $slot->endMinute);
                    $plan['reschedule'][] = $dayEntries[$scheduledIndex];
                }

                continue;
            }

            if (array_any($dayEntries, fn(TimeEntry $entry): bool => $entry->overlaps($slot->startMinute, $slot->endMinute))) {
                continue;
            }

            $created = new TimeEntry($day->format('Y-m-d'), $slot->startMinute, $slot->endMinute, $slot->description, EntrySource::Schedule);
            $dayEntries[] = $created;
            $plan['create'][] = $created;
        }

        return $dayEntries;
    }

    /**
     * @param list<TimeEntry> $dayEntries
     * @param Generator<int, DefaultActivity> $rotation
     * @param array{create: list<TimeEntry>, reschedule: list<TimeEntry>} $plan
     */
    private static function fillWithActivities(string $date, array $dayEntries, Generator $rotation, int $dailyTargetMinutes, array &$plan): void
    {
        $missing = $dailyTargetMinutes - array_sum(array_map(fn(TimeEntry $entry): int => $entry->durationMinutes(), $dayEntries));
        $shifts = [
            [self::MORNING_START, self::AFTERNOON_START, min($missing, self::MORNING_MAX_MINUTES)],
            [self::AFTERNOON_START, self::END_OF_DAY, null],
        ];

        foreach ($shifts as [$from, $until, $budget]) {
            $budget ??= $missing;

            foreach (self::freeWindows($dayEntries, $from, $until) as [$start, $end]) {
                if ($budget <= 0) {
                    break;
                }

                $length = min($end - $start, $budget);
                $activity = $rotation->current();
                $rotation->next();

                $created = new TimeEntry($date, $start, $start + $length, $activity->description, EntrySource::Activity);
                $dayEntries[] = $created;
                $plan['create'][] = $created;
                $budget -= $length;
                $missing -= $length;
            }
        }
    }

    /**
     * @param list<TimeEntry> $dayEntries
     * @return list<array{int, int}>
     */
    private static function freeWindows(array $dayEntries, int $from, int $until): array
    {
        usort($dayEntries, fn(TimeEntry $a, TimeEntry $b): int => $a->startMinute <=> $b->startMinute);
        $windows = [];
        $cursor = $from;

        foreach ($dayEntries as $entry) {
            if ($entry->startMinute >= $until) {
                break;
            }

            if ($entry->startMinute > $cursor) {
                $windows[] = [$cursor, $entry->startMinute];
            }

            $cursor = max($cursor, $entry->endMinute);
        }

        if ($cursor < $until) {
            $windows[] = [$cursor, $until];
        }

        return $windows;
    }

    /**
     * @param list<DefaultActivity> $activities
     * @return Generator<int, DefaultActivity>|null
     */
    private static function rotation(array $activities): ?Generator
    {
        if ($activities === []) {
            return null;
        }

        usort(
            $activities,
            fn(DefaultActivity $a, DefaultActivity $b): int => ($b->priority->weight() <=> $a->priority->weight())
                ?: strcmp($a->description, $b->description),
        );

        return (function () use ($activities): Generator {
            $totalWeight = array_sum(array_map(fn(DefaultActivity $activity): int => $activity->priority->weight(), $activities));
            $current = array_fill(0, count($activities), 0);

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
}
