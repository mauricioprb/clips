<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SummarizeMonth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MonthReportController
{
    public function __invoke(Request $request, SummarizeMonth $summarizeMonth, int $year, int $month): Response
    {
        $user = $request->user();
        $current = CarbonImmutable::create($year, $month);
        $summary = $summarizeMonth->execute($user, $current);

        return Pdf::loadView('reports.monthly', [
            'user' => $user,
            'month' => $current,
            'summary' => $summary,
        ])->download("relatorio-{$current->format('Y-m')}.pdf");
    }
}
