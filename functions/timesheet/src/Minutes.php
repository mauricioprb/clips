<?php

declare(strict_types=1);

namespace Clips;

final class Minutes
{
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
