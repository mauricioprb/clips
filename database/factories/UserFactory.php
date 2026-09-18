<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password1',
            'advisor_name' => fake()->name(),
            'scholarship_name' => 'Iniciação Científica',
            'laboratories' => ['Laboratório de Nanociências'],
            'weekly_workload_minutes' => 1200,
            'is_admin' => false,
            'is_active' => true,
            'password_set_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['is_admin' => true]);
    }

    public function pendingInvitation(): static
    {
        return $this->state(['password_set_at' => null, 'invitation_sent_at' => now()]);
    }

    public function blocked(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withoutProfile(): static
    {
        return $this->state([
            'advisor_name' => null,
            'scholarship_name' => null,
            'laboratories' => null,
        ]);
    }
}
