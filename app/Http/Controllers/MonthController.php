<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SummarizeMonth;
use App\Models\TimeEntry;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthController
{
    public function show(Request $request, SummarizeMonth $summarizeMonth, ?int $year = null, ?int $month = null): Response
    {
        $user = $request->user();
        $current = $year !== null && $month !== null
            ? CarbonImmutable::create($year, $month)
            : CarbonImmutable::now()->startOfMonth();
        $summary = $summarizeMonth->execute($user, $current);

        return Inertia::render('Month/Show', [
            'month' => $current->format('Y-m'),
            'days' => array_map(fn (array $day): array => [
                'date' => $day['date']->toDateString(),
                'isWorkday' => $day['isWorkday'],
                'holiday' => $day['holiday'],
                'minutes' => $day['minutes'],
                'entries' => $day['entries']->map(fn (TimeEntry $entry): array => [
                    'id' => $entry->id,
                    'startMinute' => $entry->start_minute,
                    'endMinute' => $entry->end_minute,
                    'description' => $entry->description,
                    'source' => $entry->source->value,
                ])->values(),
            ], $summary['days']),
            'loggedMinutes' => $summary['loggedMinutes'],
            'workdayMinutes' => $summary['workdayMinutes'],
            'targetMinutes' => $summary['targetMinutes'],
            'dailyTargetMinutes' => $user->dailyWorkloadMinutes(),
            'setup' => [
                'profile' => $user->hasScholarshipProfile(),
                'schedule' => $user->weeklySlots()->exists(),
                'activities' => $user->defaultActivities()->exists(),
            ],
            'suggestions' => $user->defaultActivities()->pluck('description')
                ->merge($user->weeklySlots()->pluck('description'))
                ->unique()
                ->sort()
                ->values(),
        ]);
    }
}
