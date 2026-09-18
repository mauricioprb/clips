<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EntrySource;
use App\Http\Requests\TimeEntryRequest;
use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;

class TimeEntryController
{
    public function store(TimeEntryRequest $request): RedirectResponse
    {
        $request->user()->timeEntries()->create([...$request->validated(), 'source' => EntrySource::Manual]);

        return back()->with('status', 'entry-saved');
    }

    public function update(TimeEntryRequest $request, TimeEntry $timeEntry): RedirectResponse
    {
        $timeEntry->update([...$request->validated(), 'source' => EntrySource::Manual]);

        return back()->with('status', 'entry-saved');
    }

    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        $timeEntry->delete();

        return back()->with('status', 'entry-deleted');
    }
}
