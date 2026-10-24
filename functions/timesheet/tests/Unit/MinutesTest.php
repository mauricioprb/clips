<?php

declare(strict_types=1);

use Clips\Minutes;

it('prints minutes as a clock time', function (int $minutes, string $clock) {
    expect(Minutes::toClock($minutes))->toBe($clock);
})->with([
    'midnight' => [0, '00:00'],
    'morning' => [480, '08:00'],
    'afternoon' => [870, '14:30'],
    'end of day' => [1440, '24:00'],
]);

it('formats durations the way the report prints them', function (int $minutes, string $expected) {
    expect(Minutes::toHours($minutes))->toBe($expected);
})->with([
    'zero' => [0, '0h'],
    'whole hours' => [240, '4h'],
    'half hour' => [270, '4:30h'],
    'minutes' => [65, '1:05h'],
]);
