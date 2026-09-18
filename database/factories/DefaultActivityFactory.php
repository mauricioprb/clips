<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Priority;
use App\Models\DefaultActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DefaultActivity> */
class DefaultActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'description' => fake()->unique()->sentence(2),
            'priority' => Priority::Medium,
        ];
    }
}
