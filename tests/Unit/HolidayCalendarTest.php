<?php

declare(strict_types=1);

use App\Enums\HolidayRecurrence;
use App\Models\Holiday;
use App\Support\HolidayCalendar;
use Carbon\CarbonImmutable;

it('computes Easter Sunday', function (int $year, string $easter): void {
    expect(HolidayCalendar::easter($year)->toDateString())->toBe($easter);
})->with([
    [2024, '2024-03-31'],
    [2025, '2025-04-20'],
    [2026, '2026-04-05'],
    [2027, '2027-03-28'],
]);

it('lists national holidays including the Easter based ones', function (): void {
    $holidays = (new HolidayCalendar)->forYear(2026);

    expect($holidays)
        ->toHaveKey('2026-01-01', 'Confraternização Universal')
        ->toHaveKey('2026-02-17', 'Carnaval')
        ->toHaveKey('2026-04-03', 'Paixão de Cristo')
        ->toHaveKey('2026-11-20', 'Dia Nacional de Zumbi e da Consciência Negra')
        ->not->toHaveKey('2026-09-20')
        ->not->toHaveKey('2026-06-04');
});

it('applies user holidays to any year according to their recurrence', function (): void {
    $calendar = new HolidayCalendar(collect([
        new Holiday(['name' => 'Revolução Farroupilha', 'recurrence' => HolidayRecurrence::Yearly, 'date' => '2020-09-20']),
        new Holiday(['name' => 'Corpus Christi', 'recurrence' => HolidayRecurrence::CorpusChristi, 'date' => null]),
        new Holiday(['name' => 'Recesso', 'recurrence' => HolidayRecurrence::Once, 'date' => '2025-12-26']),
    ]));

    expect($calendar->nameOn(CarbonImmutable::parse('2019-09-20')))->toBe('Revolução Farroupilha')
        ->and($calendar->nameOn(CarbonImmutable::parse('2031-09-20')))->toBe('Revolução Farroupilha')
        ->and($calendar->nameOn(CarbonImmutable::parse('2026-06-04')))->toBe('Corpus Christi')
        ->and($calendar->nameOn(CarbonImmutable::parse('2025-12-26')))->toBe('Recesso')
        ->and($calendar->nameOn(CarbonImmutable::parse('2026-12-26')))->toBeNull();
});

it('keeps the national name when a user holiday falls on the same day', function (): void {
    $calendar = new HolidayCalendar(collect([
        new Holiday(['name' => 'Feriado local', 'recurrence' => HolidayRecurrence::Yearly, 'date' => '2020-12-25']),
    ]));

    expect($calendar->nameOn(CarbonImmutable::parse('2026-12-25')))->toBe('Natal');
});

it('skips a yearly February 29 holiday outside leap years', function (): void {
    $calendar = new HolidayCalendar(collect([
        new Holiday(['name' => 'Bissexto', 'recurrence' => HolidayRecurrence::Yearly, 'date' => '2024-02-29']),
    ]));

    expect($calendar->forYear(2025))->not->toContain('Bissexto')
        ->and($calendar->forYear(2032))->toHaveKey('2032-02-29', 'Bissexto');
});
