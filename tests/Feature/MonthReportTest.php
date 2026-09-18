<?php

declare(strict_types=1);

use App\Actions\SummarizeMonth;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\CarbonImmutable;

it('downloads the monthly report as a PDF', function (): void {
    $user = User::factory()->create();
    TimeEntry::factory()->for($user)->create(['date' => '2026-03-02', 'start_minute' => 480, 'end_minute' => 720]);

    $response = $this->actingAs($user)->get('/mes/2026/3/relatorio');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('relatorio-2026-03.pdf');

    expect($response->getContent())->toStartWith('%PDF');
});

it('lists workdays only and totals their hours', function (): void {
    $user = User::factory()->create(['name' => 'Ana Souza']);
    TimeEntry::factory()->for($user)->create(['date' => '2026-03-02', 'start_minute' => 480, 'end_minute' => 750, 'description' => 'Pesquisa']);
    TimeEntry::factory()->for($user)->create(['date' => '2026-03-07', 'start_minute' => 480, 'end_minute' => 540, 'description' => 'Sábado']);

    $month = CarbonImmutable::create(2026, 3);
    $html = view('reports.monthly', [
        'user' => $user,
        'month' => $month,
        'summary' => app(SummarizeMonth::class)->execute($user, $month),
    ])->render();

    expect($html)
        ->toContain('Ana Souza')
        ->toContain('02/03/2026')
        ->toContain('08:00-12:30')
        ->toContain('4:30h')
        ->not->toContain('07/03/2026')
        ->not->toContain('Sábado');
});
