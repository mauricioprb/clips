<?php

declare(strict_types=1);

namespace Clips;

use DateTimeImmutable;

final readonly class WeeklySlot
{
    public function __construct(
        public int $weekday,
        public int $startMinute,
        public int $endMinute,
        public string $description,
        public ?string $validFrom = null,
        public ?string $validUntil = null,
    ) {}

    public function appliesTo(DateTimeImmutable $day): bool
    {
        $date = $day->format('Y-m-d');

        return (int) $day->format('w') === $this->weekday
            && ($this->validFrom === null || $date >= $this->validFrom)
            && ($this->validUntil === null || $date <= $this->validUntil);
    }
}
