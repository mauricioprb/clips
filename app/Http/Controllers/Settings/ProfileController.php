<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Settings/Profile', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'advisorName' => $user->advisor_name,
                'scholarshipName' => $user->scholarship_name,
                'laboratories' => $user->laboratories ?? [],
                'weeklyWorkloadHours' => $user->weekly_workload_minutes / 60,
            ],
        ]);
    }
}
