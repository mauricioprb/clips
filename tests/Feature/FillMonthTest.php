<?php

declare(strict_types=1);

use App\Actions\FillMonth;
use App\Enums\EntrySource;
use App\Enums\HolidayRecurrence;
use App\Enums\Priority;
use App\Models\DefaultActivity;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WeeklySlot;
use Carbon\CarbonImmutable;

function march(): CarbonImmutable
{
    return CarbonImmutable::create(2026, 3);
}

function fillMarch(User $user): int
{
    return app(FillMonth::class)->execute($user, march());
}

it('fills every workday up to the daily target and leaves weekends empty', function (): void {
    $user = User::factory()->create(['weekly_workload_minutes' => 1200]);
    DefaultActivity::factory()->for($user)->create(['description' => 'Pesquisa']);

    expect(fillMarch($user))->toBe(22);

    $entries = $user->timeEntries()->get();

    expect($entries)->toHaveCount(22)
        ->and($entries->every(fn (TimeEntry $entry): bool => $entry->date->isWeekday()))->toBeTrue()
        ->and($entries->every(fn (TimeEntry $entry): bool => $entry->start_minute === 480 && $entry->end_minute === 720))->toBeTrue()
        ->and($entries->every(fn (TimeEntry $entry): bool => $entry->source === EntrySource::Activity))->toBeTrue();
});

it('skips national and user holidays', function (): void {
    $user = User::factory()->create();
    DefaultActivity::factory()->for($user)->create();
    $user->holidays()->create(['name' => 'Recesso', 'recurrence' => HolidayRecurrence::Once, 'date' => '2026-03-10']);

    fillMarch($user);

    expect($user->timeEntries()->whereDate('date', '2026-03-10')->exists())->toBeFalse()
        ->and($user->timeEntries()->count())->toBe(21);
});

it('places the weekly schedule first and completes the day with activities', function (): void {
    $user = User::factory()->create();
    DefaultActivity::factory()->for($user)->create(['description' => 'Pesquisa']);
    WeeklySlot::factory()->for($user)->create(['weekday' => 2, 'start_minute' => 840, 'end_minute' => 960, 'description' => 'Aula']);

    fillMarch($user);

    $tuesday = $user->timeEntries()->whereDate('date', '2026-03-03')->orderBy('start_minute')->get();

    expect($tuesday->map->only(['start_minute', 'end_minute', 'description'])->all())->toBe([
        ['start_minute' => 480, 'end_minute' => 600, 'description' => 'Pesquisa'],
        ['start_minute' => 840, 'end_minute' => 960, 'description' => 'Aula'],
    ]);
});

it('respects weekly slot validity dates', function (): void {
    $user = User::factory()->create();
    WeeklySlot::factory()->for($user)->create(['weekday' => 1, 'valid_from' => '2026-03-16', 'valid_until' => '2026-03-23']);

    fillMarch($user);

    expect($user->timeEntries()->pluck('date')->map->toDateString()->all())->toBe(['2026-03-16', '2026-03-23']);
});

it('fills around manual entries without touching them', function (): void {
    $user = User::factory()->create();
    DefaultActivity::factory()->for($user)->create(['description' => 'Pesquisa']);
    $manual = TimeEntry::factory()->for($user)->create(['date' => '2026-03-02', 'start_minute' => 540, 'end_minute' => 660, 'description' => 'Reunião']);

    fillMarch($user);

    $monday = $user->timeEntries()->whereDate('date', '2026-03-02')->orderBy('start_minute')->get();

    expect($monday->map->only(['start_minute', 'end_minute'])->all())->toBe([
        ['start_minute' => 480, 'end_minute' => 540],
        ['start_minute' => 540, 'end_minute' => 660],
        ['start_minute' => 660, 'end_minute' => 720],
    ])->and($manual->fresh()->description)->toBe('Reunião');
});

it('does not create a schedule entry over a manual entry', function (): void {
    $user = User::factory()->create();
    WeeklySlot::factory()->for($user)->create(['weekday' => 1, 'start_minute' => 840, 'end_minute' => 960, 'description' => 'Aula']);
    TimeEntry::factory()->for($user)->create(['date' => '2026-03-02', 'start_minute' => 900, 'end_minute' => 1020, 'description' => 'Banca']);

    fillMarch($user);

    expect($user->timeEntries()->whereDate('date', '2026-03-02')->pluck('description')->all())->toBe(['Banca']);
});

it('creates nothing when run twice', function (): void {
    $user = User::factory()->create();
    DefaultActivity::factory()->for($user)->create();
    WeeklySlot::factory()->for($user)->create(['weekday' => 3]);

    fillMarch($user);
    $count = $user->timeEntries()->count();

    expect(fillMarch($user))->toBe(0)
        ->and($user->timeEntries()->count())->toBe($count);
});

it('moves generated schedule entries when the slot changes', function (): void {
    $user = User::factory()->create();
    $slot = WeeklySlot::factory()->for($user)->create(['weekday' => 1, 'start_minute' => 840, 'end_minute' => 960, 'description' => 'Aula']);

    fillMarch($user);
    $slot->update(['start_minute' => 900, 'end_minute' => 1020]);
    fillMarch($user);

    expect($user->timeEntries()->pluck('start_minute')->unique()->all())->toBe([900]);
});

it('uses activities proportionally to their priority', function (): void {
    $user = User::factory()->create();
    DefaultActivity::factory()->for($user)->create(['description' => 'Alta', 'priority' => Priority::High]);
    DefaultActivity::factory()->for($user)->create(['description' => 'Média', 'priority' => Priority::Medium]);
    DefaultActivity::factory()->for($user)->create(['description' => 'Baixa', 'priority' => Priority::Low]);

    fillMarch($user);

    expect($user->timeEntries()->pluck('description')->countBy()->all())
        ->toBe(['Alta' => 11, 'Média' => 7, 'Baixa' => 4]);
});

it('only touches the authenticated user and redirects back with a status', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    DefaultActivity::factory()->for($user)->create();
    DefaultActivity::factory()->for($other)->create();

    $this->actingAs($user)
        ->from('/mes/2026/3')
        ->post('/mes/2026/3/preencher')
        ->assertRedirect('/mes/2026/3')
        ->assertSessionHas('status', 'month-filled');

    expect($other->timeEntries()->count())->toBe(0);
});
