<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EntrySource;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TimeEntry> */
class TimeEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeThisYear()->format('Y-m-d'),
            'start_minute' => 480,
            'end_minute' => 720,
            'description' => fake()->sentence(3),
            'source' => EntrySource::Manual,
        ];
    }
}
