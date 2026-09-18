@php
    use App\Support\Minutes;
    $fallback = 'Não informado';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório {{ $month->format('m/Y') }}</title>
    <style>
        @page { margin: 50px; }
        body { font-family: Helvetica, sans-serif; font-size: 10px; color: #000; }
        .logos { width: 100%; margin-bottom: 24px; }
        .logos td { vertical-align: top; }
        .logos img { width: 140px; }
        .details p { margin: 0 0 2px; font-size: 11px; }
        .details strong { font-weight: bold; }
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
            <td><img src="{{ public_path('images/logo_ufn.png') }}" alt="UFN"></td>
            <td style="text-align: right;"><img src="{{ public_path('images/logo_nano.png') }}" alt="Nanociências"></td>
        </tr>
    </table>

    <div class="details">
        <p>Bolsista: <strong>{{ $user->name }}</strong></p>
        <p>Orientador: <strong>{{ $user->advisor_name ?: $fallback }}</strong></p>
        <p>Laboratório(s) / Sala(s): <strong>{{ filled($user->laboratories) ? implode(', ', $user->laboratories) : $fallback }}</strong></p>
        <p>Bolsa: <strong>{{ $user->scholarship_name ?: $fallback }}</strong></p>
        <p>Carga horária semanal: <strong>{{ Minutes::toHours($user->weekly_workload_minutes) }}</strong></p>
        <p>Mês/Ano: <strong>{{ $month->format('m/Y') }}</strong></p>
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
            @foreach ($summary['days'] as $day)
                @continue(! $day['isWorkday'])
                <tr>
                    <td>{{ $day['date']->format('d/m/Y') }}</td>
                    <td>
                        {{ $day['entries']->isEmpty() ? '-' : $day['entries']->map(fn ($entry) => Minutes::toClock($entry->start_minute) . '-' . Minutes::toClock($entry->end_minute))->implode(' | ') }}
                    </td>
                    <td>{{ $day['entries']->isEmpty() ? '-' : $day['entries']->pluck('description')->implode('; ') }}</td>
                    <td>{{ Minutes::toHours($day['minutes']) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3">Total de horas mensais</td>
                <td class="amount">{{ Minutes::toHours($summary['workdayMinutes']) }}</td>
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
