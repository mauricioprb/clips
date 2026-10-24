<?php

declare(strict_types=1);

use Clips\EntrySource;
use Clips\HolidayCalendar;
use Clips\Month;
use Clips\MonthSummary;
use Clips\TimeEntry;

it('parses only valid months', function (string $value, ?array $expected) {
    $month = Month::fromString($value);

    expect($month === null ? null : [$month->year, $month->month])->toBe($expected);
})->with([
    'valid' => ['2026-03', [2026, 3]],
    'december' => ['2026-12', [2026, 12]],
    'month zero' => ['2026-00', null],
    'month thirteen' => ['2026-13', null],
    'single digit' => ['2026-3', null],
    'with day' => ['2026-03-01', null],
    'text' => ['março', null],
]);

it('summarizes a month against the workday target', function () {
    $summary = MonthSummary::of(Month::fromString('2026-04'), [
        new TimeEntry('2026-04-01', 840, 960, 'Aula', EntrySource::Schedule),
        new TimeEntry('2026-04-01', 480, 600, 'Pesquisa', EntrySource::Activity),
        new TimeEntry('2026-04-04', 480, 540, 'Sábado', EntrySource::Manual),
        new TimeEntry('2026-04-03', 480, 540, 'Feriado', EntrySource::Manual),
    ], new HolidayCalendar(), 240);

    $firstDay = $summary['days'][0];
    $goodFriday = $summary['days'][2];

    expect($summary['days'])->toHaveCount(30)
        ->and($firstDay['minutes'])->toBe(240)
        ->and(array_map(fn(TimeEntry $entry): int => $entry->startMinute, $firstDay['entries']))->toBe([480, 840])
        ->and($goodFriday)->toMatchArray(['isWorkday' => false, 'holiday' => 'Paixão de Cristo'])
        ->and($summary['loggedMinutes'])->toBe(360)
        ->and($summary['workdayMinutes'])->toBe(240)
        ->and($summary['targetMinutes'])->toBe(20 * 240);
});
