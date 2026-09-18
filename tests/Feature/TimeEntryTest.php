<?php

declare(strict_types=1);

use App\Enums\EntrySource;
use App\Models\TimeEntry;
use App\Models\User;

it('creates a manual entry', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/lancamentos', ['date' => '2026-03-02', 'start_minute' => 480, 'end_minute' => 600, 'description' => 'Leitura'])
        ->assertRedirect()
        ->assertSessionHas('status', 'entry-saved');

    $entry = $user->timeEntries()->sole();

    expect($entry->date->toDateString())->toBe('2026-03-02')
        ->and($entry->durationMinutes())->toBe(120)
        ->and($entry->source)->toBe(EntrySource::Manual);
});

it('rejects an end before the start', function (): void {
    $this->actingAs(User::factory()->create())
        ->post('/lancamentos', ['date' => '2026-03-02', 'start_minute' => 600, 'end_minute' => 480, 'description' => 'Leitura'])
        ->assertSessionHasErrors('end_minute');
});

it('rejects an entry that overlaps another on the same day', function (): void {
    $user = User::factory()->create();
    TimeEntry::factory()->for($user)->create(['date' => '2026-03-02', 'start_minute' => 480, 'end_minute' => 600]);

    $this->actingAs($user)
        ->post('/lancamentos', ['date' => '2026-03-02', 'start_minute' => 540, 'end_minute' => 660, 'description' => 'Leitura'])
        ->assertSessionHasErrors('start_minute');

    $this->actingAs($user)
        ->post('/lancamentos', ['date' => '2026-03-03', 'start_minute' => 540, 'end_minute' => 660, 'description' => 'Leitura'])
        ->assertSessionHasNoErrors();
});

it('allows an entry to be updated over its own time range and marks it as manual', function (): void {
    $user = User::factory()->create();
    $entry = TimeEntry::factory()->for($user)->create([
        'date' => '2026-03-02',
        'start_minute' => 480,
        'end_minute' => 600,
        'source' => EntrySource::Activity,
    ]);

    $this->actingAs($user)
        ->put("/lancamentos/{$entry->id}", ['date' => '2026-03-02', 'start_minute' => 510, 'end_minute' => 630, 'description' => 'Revisão'])
        ->assertSessionHasNoErrors();

    expect($entry->fresh())
        ->start_minute->toBe(510)
        ->description->toBe('Revisão')
        ->source->toBe(EntrySource::Manual);
});

it('hides entries from other users', function (): void {
    $entry = TimeEntry::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->put("/lancamentos/{$entry->id}", ['date' => '2026-03-02', 'start_minute' => 480, 'end_minute' => 600, 'description' => 'X'])
        ->assertNotFound();

    $this->actingAs($intruder)->delete("/lancamentos/{$entry->id}")->assertNotFound();

    expect($entry->fresh())->not->toBeNull();
});

it('deletes an entry', function (): void {
    $entry = TimeEntry::factory()->create();

    $this->actingAs($entry->user)->delete("/lancamentos/{$entry->id}")->assertSessionHas('status', 'entry-deleted');

    expect($entry->fresh())->toBeNull();
});
