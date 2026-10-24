<?php

declare(strict_types=1);

namespace Clips;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;

final readonly class Month
{
    private DateTimeImmutable $start;

    private function __construct(public int $year, public int $month)
    {
        $this->start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month), new DateTimeZone('UTC'));
    }

    public static function fromString(string $value): ?self
    {
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $value, $parts) !== 1) {
            return null;
        }

        return new self((int) $parts[1], (int) $parts[2]);
    }

    public function firstDay(): string
    {
        return $this->start->format('Y-m-d');
    }

    public function lastDay(): string
    {
        return $this->start->modify('last day of this month')->format('Y-m-d');
    }

    /** @return list<DateTimeImmutable> */
    public function days(): array
    {
        $end = $this->start->modify('first day of next month');

        return iterator_to_array(new DatePeriod($this->start, new DateInterval('P1D'), $end), false);
    }

    public function format(string $pattern): string
    {
        return $this->start->format($pattern);
    }
}
