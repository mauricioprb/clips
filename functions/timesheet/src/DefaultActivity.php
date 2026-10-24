<?php

declare(strict_types=1);

namespace Clips;

final readonly class DefaultActivity
{
    public function __construct(
        public string $description,
        public Priority $priority,
    ) {}
}
