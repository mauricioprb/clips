<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /** @param array<string, mixed> $input */
    public function update(User $user, array $input): void
    {
        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'advisor_name' => ['required', 'string', 'max:255'],
            'scholarship_name' => ['required', 'string', 'max:255'],
            'laboratories' => ['required', 'array', 'min:1', 'max:10'],
            'laboratories.*' => ['required', 'string', 'distinct', 'max:120'],
            'weekly_workload_hours' => ['required', 'numeric', 'min:1', 'max:44', 'multiple_of:0.5'],
        ])->validate();

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'advisor_name' => $validated['advisor_name'],
            'scholarship_name' => $validated['scholarship_name'],
            'laboratories' => array_values($validated['laboratories']),
            'weekly_workload_minutes' => (int) round($validated['weekly_workload_hours'] * 60),
        ])->save();
    }
}
