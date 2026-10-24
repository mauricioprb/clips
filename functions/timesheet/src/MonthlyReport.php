<?php

declare(strict_types=1);

namespace Clips;

use Dompdf\Dompdf;

final class MonthlyReport
{
    private const string FALLBACK = 'Não informado';

    /**
     * @param array{name: string, advisorName: ?string, scholarshipName: ?string, laboratories: list<string>, weeklyWorkloadMinutes: int} $scholar
     * @param array{days: list<array{date: string, isWorkday: bool, holiday: ?string, minutes: int, entries: list<TimeEntry>}>, workdayMinutes: int} $summary
     */
    public static function pdf(array $scholar, Month $month, array $summary): string
    {
        $assets = dirname(__DIR__) . '/assets';
        $pdf = new Dompdf(['chroot' => $assets]);
        $pdf->loadHtml(self::html($scholar, $month, $summary, $assets));
        $pdf->setPaper('A4');
        $pdf->render();

        return $pdf->output() ?? '';
    }

    /**
     * @param array{name: string, advisorName: ?string, scholarshipName: ?string, laboratories: list<string>, weeklyWorkloadMinutes: int} $scholar
     * @param array{days: list<array{date: string, isWorkday: bool, holiday: ?string, minutes: int, entries: list<TimeEntry>}>, workdayMinutes: int} $summary
     */
    private static function html(array $scholar, Month $month, array $summary, string $assets): string
    {
        $e = fn(string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $orFallback = fn(?string $text): string => $e($text === null || $text === '' ? self::FALLBACK : $text);
        $period = $month->format('m/Y');
        $laboratories = $orFallback(implode(', ', $scholar['laboratories']));
        $rows = '';

        foreach ($summary['days'] as $day) {
            if (!$day['isWorkday']) {
                continue;
            }

            $entries = $day['entries'];
            $schedule = $entries === [] ? '-' : implode(' | ', array_map(
                fn(TimeEntry $entry): string => Minutes::toClock($entry->startMinute) . '-' . Minutes::toClock($entry->endMinute),
                $entries,
            ));
            $activities = $entries === [] ? '-' : $e(implode('; ', array_map(fn(TimeEntry $entry): string => $entry->description, $entries)));
            $date = implode('/', array_reverse(explode('-', $day['date'])));
            $hours = Minutes::toHours($day['minutes']);
            $rows .= "<tr><td>{$date}</td><td>{$schedule}</td><td>{$activities}</td><td>{$hours}</td></tr>";
        }

        $total = Minutes::toHours($summary['workdayMinutes']);
        $workload = Minutes::toHours($scholar['weeklyWorkloadMinutes']);

        return <<<HTML
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="utf-8">
                <title>Relatório {$period}</title>
                <style>
                    @page { margin: 50px; }
                    body { font-family: Helvetica, sans-serif; font-size: 10px; color: #000; }
                    .logos { width: 100%; margin-bottom: 24px; }
                    .logos td { vertical-align: top; }
                    .logos img { width: 140px; }
                    .details p { margin: 0 0 2px; font-size: 11px; }
                    .entries { width: 100%; border-collapse: collapse; margin-top: 12px; }
                    .entries th, .entries td { border: 0.6px solid #000; padding: 2px 6px; text-align: left; vertical-align: top; }
                    .entries th { background: #d9d9d9; font-weight: bold; }
                    .entries thead { display: table-header-group; }
                    .entries tr { page-break-inside: avoid; }
                    .date, .workload { width: 90px; }
                    .schedule { width: 180px; }
                    .total td { background: #3b6e15; color: #fff; font-weight: bold; border-color: #3b6e15; }
                    .total .amount { text-align: right; }
                    .signatures { width: 100%; margin-top: 70px; page-break-inside: avoid; }
                    .signatures td { width: 50%; text-align: center; padding: 0 40px; }
                    .signatures span { display: block; border-top: 1px solid #000; padding-top: 6px; }
                </style>
            </head>
            <body>
                <table class="logos">
                    <tr>
                        <td><img src="{$assets}/logo-ufn.jpg" alt="UFN"></td>
                        <td style="text-align: right;"><img src="{$assets}/logo-nano.jpg" alt="Nanociências"></td>
                    </tr>
                </table>
                <div class="details">
                    <p>Bolsista: <strong>{$e($scholar['name'])}</strong></p>
                    <p>Orientador: <strong>{$orFallback($scholar['advisorName'])}</strong></p>
                    <p>Laboratório(s) / Sala(s): <strong>{$laboratories}</strong></p>
                    <p>Bolsa: <strong>{$orFallback($scholar['scholarshipName'])}</strong></p>
                    <p>Carga horária semanal: <strong>{$workload}</strong></p>
                    <p>Mês/Ano: <strong>{$period}</strong></p>
                </div>
                <table class="entries">
                    <thead>
                        <tr>
                            <th class="date">Data</th>
                            <th class="schedule">Horário</th>
                            <th>Atividades</th>
                            <th class="workload">Carga horária</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rows}
                        <tr class="total">
                            <td colspan="3">Total de horas mensais</td>
                            <td class="amount">{$total}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="signatures">
                    <tr>
                        <td><span>Aluno</span></td>
                        <td><span>Orientador</span></td>
                    </tr>
                </table>
            </body>
            </html>
            HTML;
    }
}
