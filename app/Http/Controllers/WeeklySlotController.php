<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WeeklySlotRequest;
use App\Models\WeeklySlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeeklySlotController
{
    public function index(Request $request): Response
    {
        return Inertia::render('WeeklySlots/Index', [
            'weeklyWorkloadMinutes' => $request->user()->weekly_workload_minutes,
            'slots' => $request->user()->weeklySlots()
                ->orderBy('weekday')
                ->orderBy('start_minute')
                ->get()
                ->map(fn (WeeklySlot $slot): array => [
                    'id' => $slot->id,
                    'weekday' => $slot->weekday,
                    'startMinute' => $slot->start_minute,
                    'endMinute' => $slot->end_minute,
                    'description' => $slot->description,
                    'validFrom' => $slot->valid_from?->toDateString(),
                    'validUntil' => $slot->valid_until?->toDateString(),
                ]),
        ]);
    }

    public function store(WeeklySlotRequest $request): RedirectResponse
    {
        $request->user()->weeklySlots()->create($request->validated());

        return back()->with('status', 'slot-saved');
    }

    public function update(WeeklySlotRequest $request, WeeklySlot $weeklySlot): RedirectResponse
    {
        $weeklySlot->update($request->validated());

        return back()->with('status', 'slot-saved');
    }

    public function destroy(WeeklySlot $weeklySlot): RedirectResponse
    {
        $weeklySlot->delete();

        return back()->with('status', 'slot-deleted');
    }
}
