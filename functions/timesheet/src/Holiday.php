<?php

declare(strict_types=1);

namespace Clips;

final readonly class Holiday
{
    public function __construct(
        public string $name,
        public HolidayRecurrence $recurrence,
        public ?string $date,
    ) {}
}
