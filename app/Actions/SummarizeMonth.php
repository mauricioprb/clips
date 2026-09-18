<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TimeEntry;
use App\Models\User;
use App\Support\HolidayCalendar;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Support\Collection;

class SummarizeMonth
{
    /**
     * @return array{
     *     days: list<array{date: CarbonImmutable, isWorkday: bool, holiday: ?string, minutes: int, entries: Collection<int, TimeEntry>}>,
     *     loggedMinutes: int,
     *     workdayMinutes: int,
     *     targetMinutes: int,
     * }
     */
    public function execute(User $user, CarbonImmutable $month): array
    {
        $calendar = new HolidayCalendar($user->holidays);
        $entriesByDate = $user->timeEntries()
            ->within($month->startOfMonth(), $month->endOfMonth())
            ->orderBy('date')
            ->orderBy('start_minute')
            ->get()
            ->groupBy(fn (TimeEntry $entry): string => $entry->date->toDateString());

        $days = [];

        foreach (CarbonPeriodImmutable::create($month->startOfMonth(), $month->endOfMonth()) as $day) {
            $holiday = $calendar->nameOn($day);
            $entries = $entriesByDate->get($day->toDateString(), new Collection);

            $days[] = [
                'date' => $day,
                'isWorkday' => $day->isWeekday() && $holiday === null,
                'holiday' => $holiday,
                'minutes' => $entries->sum(fn (TimeEntry $entry): int => $entry->durationMinutes()),
                'entries' => $entries,
            ];
        }

        $workdays = array_filter($days, fn (array $day): bool => $day['isWorkday']);

        return [
            'days' => $days,
            'loggedMinutes' => array_sum(array_column($days, 'minutes')),
            'workdayMinutes' => array_sum(array_column($workdays, 'minutes')),
            'targetMinutes' => count($workdays) * $user->dailyWorkloadMinutes(),
        ];
    }
}
