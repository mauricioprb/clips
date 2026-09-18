<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HolidayRecurrence;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Holiday> */
class HolidayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'recurrence' => HolidayRecurrence::Yearly,
            'date' => fake()->date(),
        ];
    }
}
