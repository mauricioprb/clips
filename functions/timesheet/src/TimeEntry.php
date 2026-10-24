<?php

declare(strict_types=1);

namespace Clips;

final readonly class TimeEntry
{
    public function __construct(
        public string $date,
        public int $startMinute,
        public int $endMinute,
        public string $description,
        public EntrySource $source,
        public ?string $id = null,
    ) {}

    public function durationMinutes(): int
    {
        return $this->endMinute - $this->startMinute;
    }

    public function overlaps(int $startMinute, int $endMinute): bool
    {
        return $startMinute < $this->endMinute && $endMinute > $this->startMinute;
    }

    public function withTimes(int $startMinute, int $endMinute): self
    {
        return new self($this->date, $startMinute, $endMinute, $this->description, $this->source, $this->id);
    }
}
