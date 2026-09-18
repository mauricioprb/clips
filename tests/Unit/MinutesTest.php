<?php

declare(strict_types=1);

use App\Support\Minutes;

it('converts clock times to minutes and back', function (string $clock, int $minutes): void {
    expect(Minutes::fromClock($clock))->toBe($minutes)
        ->and(Minutes::toClock($minutes))->toBe($clock);
})->with([
    'midnight' => ['00:00', 0],
    'morning' => ['08:00', 480],
    'afternoon' => ['14:30', 870],
    'last minute' => ['23:59', 1439],
]);

it('rejects malformed clock times', function (string $clock): void {
    Minutes::fromClock($clock);
})->with(['8:00', '24:00', '12:60', 'noon'])->throws(InvalidArgumentException::class);

it('formats durations the way the report prints them', function (int $minutes, string $expected): void {
    expect(Minutes::toHours($minutes))->toBe($expected);
})->with([
    'zero' => [0, '0h'],
    'whole hours' => [240, '4h'],
    'half hour' => [270, '4:30h'],
    'minutes' => [65, '1:05h'],
]);
