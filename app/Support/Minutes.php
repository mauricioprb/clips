<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class Minutes
{
    public static function fromClock(string $clock): int
    {
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $clock, $parts) !== 1) {
            throw new InvalidArgumentException("Invalid clock time [{$clock}].");
        }

        return (int) $parts[1] * 60 + (int) $parts[2];
    }

    public static function toClock(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public static function toHours(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $rest === 0 ? "{$hours}h" : sprintf('%d:%02dh', $hours, $rest);
    }
}
