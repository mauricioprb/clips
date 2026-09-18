<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Requests\HolidayRequest;
use App\Models\Holiday;
use App\Support\HolidayCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController
{
    public function index(Request $request): Response
    {
        $holidays = $request->user()->holidays()->orderBy('name')->get();
        $year = now()->year;

        return Inertia::render('Settings/Holidays', [
            'year' => $year,
            'holidays' => $holidays->map(fn (Holiday $holiday): array => [
                'id' => $holiday->id,
                'name' => $holiday->name,
                'recurrence' => $holiday->recurrence->value,
                'date' => $holiday->date?->toDateString(),
            ]),
            'calendar' => collect((new HolidayCalendar($holidays))->forYear($year))
                ->map(fn (string $name, string $date): array => ['date' => $date, 'name' => $name])
                ->values(),
        ]);
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        $request->user()->holidays()->create($request->validated());

        return back()->with('status', 'holiday-saved');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return back()->with('status', 'holiday-deleted');
    }
}
