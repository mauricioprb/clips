<?php

declare(strict_types=1);

namespace Clips;

final class MonthSummary
{
    /**
     * @param list<TimeEntry> $entries
     * @return array{
     *     days: list<array{date: string, isWorkday: bool, holiday: ?string, minutes: int, entries: list<TimeEntry>}>,
     *     loggedMinutes: int,
     *     workdayMinutes: int,
     *     targetMinutes: int,
     * }
     */
    public static function of(Month $month, array $entries, HolidayCalendar $calendar, int $dailyTargetMinutes): array
    {
        usort($entries, fn(TimeEntry $a, TimeEntry $b): int => [$a->date, $a->startMinute] <=> [$b->date, $b->startMinute]);
        $entriesByDate = [];

        foreach ($entries as $entry) {
            $entriesByDate[$entry->date][] = $entry;
        }

        $days = [];

        foreach ($month->days() as $day) {
            $date = $day->format('Y-m-d');
            $holiday = $calendar->nameOn($date);
            $dayEntries = $entriesByDate[$date] ?? [];

            $days[] = [
                'date' => $date,
                'isWorkday' => (int) $day->format('N') <= 5 && $holiday === null,
                'holiday' => $holiday,
                'minutes' => array_sum(array_map(fn(TimeEntry $entry): int => $entry->durationMinutes(), $dayEntries)),
                'entries' => $dayEntries,
            ];
        }

        $workdays = array_filter($days, fn(array $day): bool => $day['isWorkday']);

        return [
            'days' => $days,
            'loggedMinutes' => array_sum(array_column($days, 'minutes')),
            'workdayMinutes' => array_sum(array_column($workdays, 'minutes')),
            'targetMinutes' => count($workdays) * $dailyTargetMinutes,
        ];
    }
}
