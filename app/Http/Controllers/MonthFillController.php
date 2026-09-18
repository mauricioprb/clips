<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FillMonth;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MonthFillController
{
    public function __invoke(Request $request, FillMonth $fillMonth, int $year, int $month): RedirectResponse
    {
        $created = $fillMonth->execute($request->user(), CarbonImmutable::create($year, $month));

        return back()->with('status', $created > 0 ? 'month-filled' : 'month-already-filled');
    }
}
