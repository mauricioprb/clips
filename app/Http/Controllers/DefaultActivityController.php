<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DefaultActivityRequest;
use App\Models\DefaultActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DefaultActivityController
{
    public function index(Request $request): Response
    {
        return Inertia::render('DefaultActivities/Index', [
            'activities' => $request->user()->defaultActivities()
                ->orderBy('description')
                ->get()
                ->map(fn (DefaultActivity $activity): array => [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'priority' => $activity->priority->value,
                ]),
        ]);
    }

    public function store(DefaultActivityRequest $request): RedirectResponse
    {
        $request->user()->defaultActivities()->create($request->validated());

        return back()->with('status', 'activity-saved');
    }

    public function update(DefaultActivityRequest $request, DefaultActivity $defaultActivity): RedirectResponse
    {
        $defaultActivity->update($request->validated());

        return back()->with('status', 'activity-saved');
    }

    public function destroy(DefaultActivity $defaultActivity): RedirectResponse
    {
        $defaultActivity->delete();

        return back()->with('status', 'activity-deleted');
    }
}
