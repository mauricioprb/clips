<?php

declare(strict_types=1);

use Clips\DefaultActivity;
use Clips\EntrySource;
use Clips\Holiday;
use Clips\HolidayCalendar;
use Clips\HolidayRecurrence;
use Clips\Month;
use Clips\MonthFiller;
use Clips\Priority;
use Clips\TimeEntry;
use Clips\WeeklySlot;

/**
 * @param list<WeeklySlot> $slots
 * @param list<DefaultActivity> $activities
 * @param list<TimeEntry> $entries
 * @param list<Holiday> $holidays
 * @return array{create: list<TimeEntry>, reschedule: list<TimeEntry>}
 */
function fillMarch(array $slots = [], array $activities = [], array $entries = [], array $holidays = [], int $dailyTarget = 240): array
{
    return MonthFiller::fill(Month::fromString('2026-03'), $slots, $activities, $entries, new HolidayCalendar($holidays), $dailyTarget);
}

/**
 * @param list<TimeEntry> $entries
 * @param array{create: list<TimeEntry>, reschedule: list<TimeEntry>} $plan
 * @return list<TimeEntry>
 */
function applyPlan(array $entries, array $plan): array
{
    $byId = [];

    foreach ([...$entries, ...$plan['create']] as $entry) {
        $byId[$entry->id ?? 'new-' . count($byId)] = $entry;
    }

    foreach ($plan['reschedule'] as $entry) {
        $byId[$entry->id] = $entry;
    }

    $saved = [];

    foreach ($byId as $id => $entry) {
        $saved[] = new TimeEntry($entry->date, $entry->startMinute, $entry->endMinute, $entry->description, $entry->source, (string) $id);
    }

    return $saved;
}

/**
 * @param list<TimeEntry> $entries
 * @return list<array{int, int, string}>
 */
function onDate(array $entries, string $date): array
{
    $day = array_values(array_filter($entries, fn(TimeEntry $entry): bool => $entry->date === $date));
    usort($day, fn(TimeEntry $a, TimeEntry $b): int => $a->startMinute <=> $b->startMinute);

    return array_map(fn(TimeEntry $entry): array => [$entry->startMinute, $entry->endMinute, $entry->description], $day);
}

$research = new DefaultActivity('Pesquisa', Priority::Medium);

it('fills every workday up to the daily target and leaves weekends empty', function () use ($research) {
    $created = fillMarch(activities: [$research])['create'];

    expect($created)->toHaveCount(22)
        ->and(array_filter($created, fn(TimeEntry $entry): bool => (int) date('N', strtotime($entry->date)) >= 6))->toBe([])
        ->and(array_unique(array_map(fn(TimeEntry $entry): string => "{$entry->startMinute}-{$entry->endMinute}", $created)))->toBe(['480-720'])
        ->and(array_unique(array_map(fn(TimeEntry $entry): EntrySource => $entry->source, $created), SORT_REGULAR))->toBe([EntrySource::Activity]);
});

it('skips national and user holidays', function () use ($research) {
    $created = fillMarch(activities: [$research], holidays: [new Holiday('Recesso', HolidayRecurrence::Once, '2026-03-10')])['create'];

    expect($created)->toHaveCount(21)
        ->and(onDate($created, '2026-03-10'))->toBe([]);
});

it('places the weekly schedule first and completes the day with activities', function () use ($research) {
    $created = fillMarch([new WeeklySlot(2, 840, 960, 'Aula')], [$research])['create'];

    expect(onDate($created, '2026-03-03'))->toBe([[480, 600, 'Pesquisa'], [840, 960, 'Aula']]);
});

it('respects weekly slot validity dates', function () {
    $created = fillMarch([new WeeklySlot(1, 840, 960, 'Aula', '2026-03-16', '2026-03-23')])['create'];

    expect(array_map(fn(TimeEntry $entry): string => $entry->date, $created))->toBe(['2026-03-16', '2026-03-23']);
});

it('fills around manual entries without touching them', function () use ($research) {
    $manual = new TimeEntry('2026-03-02', 540, 660, 'Reunião', EntrySource::Manual, 'manual');
    $plan = fillMarch(activities: [$research], entries: [$manual]);

    expect(onDate(applyPlan([$manual], $plan), '2026-03-02'))->toBe([
        [480, 540, 'Pesquisa'],
        [540, 660, 'Reunião'],
        [660, 720, 'Pesquisa'],
    ])->and($plan['reschedule'])->toBe([]);
});

it('does not create a schedule entry over a manual entry', function () {
    $manual = new TimeEntry('2026-03-02', 900, 1020, 'Banca', EntrySource::Manual, 'manual');

    expect(onDate(fillMarch([new WeeklySlot(1, 840, 960, 'Aula')], entries: [$manual])['create'], '2026-03-02'))->toBe([]);
});

it('creates nothing when run twice', function () use ($research) {
    $slots = [new WeeklySlot(3, 840, 960, 'Aula')];
    $saved = applyPlan([], fillMarch($slots, [$research]));

    expect(fillMarch($slots, [$research], $saved))->toBe(['create' => [], 'reschedule' => []]);
});

it('moves generated schedule entries when the slot changes', function () {
    $saved = applyPlan([], fillMarch([new WeeklySlot(1, 840, 960, 'Aula')]));
    $plan = fillMarch([new WeeklySlot(1, 900, 1020, 'Aula')], entries: $saved);

    expect($plan['create'])->toBe([])
        ->and($plan['reschedule'])->toHaveCount(5)
        ->and(array_unique(array_map(fn(TimeEntry $entry): int => $entry->startMinute, applyPlan($saved, $plan))))->toBe([900]);
});

it('uses activities proportionally to their priority', function () {
    $created = fillMarch(activities: [
        new DefaultActivity('Baixa', Priority::Low),
        new DefaultActivity('Média', Priority::Medium),
        new DefaultActivity('Alta', Priority::High),
    ])['create'];

    expect(array_count_values(array_map(fn(TimeEntry $entry): string => $entry->description, $created)))
        ->toBe(['Alta' => 11, 'Média' => 7, 'Baixa' => 4]);
});

it('splits the target between morning and afternoon when the morning is full', function () use ($research) {
    $created = fillMarch(activities: [$research], dailyTarget: 360)['create'];

    expect(onDate($created, '2026-03-02'))->toBe([[480, 720, 'Pesquisa'], [840, 960, 'Pesquisa']]);
});

it('adds nothing on a day that already reached the target', function () use ($research) {
    $manual = new TimeEntry('2026-03-02', 600, 840, 'Experimento', EntrySource::Manual, 'manual');

    expect(onDate(fillMarch(activities: [$research], entries: [$manual])['create'], '2026-03-02'))->toBe([]);
});
