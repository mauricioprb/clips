<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\WeeklySlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WeeklySlot> */
class WeeklySlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weekday' => fake()->numberBetween(1, 5),
            'start_minute' => 840,
            'end_minute' => 960,
            'description' => fake()->sentence(3),
            'valid_from' => null,
            'valid_until' => null,
        ];
    }
}
