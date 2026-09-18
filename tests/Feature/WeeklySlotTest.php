<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\WeeklySlot;
use Inertia\Testing\AssertableInertia as Assert;

it('lists only the user slots', function (): void {
    $user = User::factory()->create();
    WeeklySlot::factory()->for($user)->create(['weekday' => 2, 'description' => 'Aula']);
    WeeklySlot::factory()->create();

    $this->actingAs($user)
        ->get('/grade-semanal')
        ->assertInertia(fn (Assert $page) => $page
            ->component('WeeklySlots/Index')
            ->where('weeklyWorkloadMinutes', 1200)
            ->has('slots', 1)
            ->where('slots.0.description', 'Aula'));
});

it('creates, updates and deletes a slot', function (): void {
    $user = User::factory()->create();
    $payload = ['weekday' => 1, 'start_minute' => 840, 'end_minute' => 960, 'description' => 'Aula', 'valid_from' => '2026-03-01', 'valid_until' => '2026-07-15'];

    $this->actingAs($user)->post('/grade-semanal', $payload)->assertSessionHas('status', 'slot-saved');
    $slot = $user->weeklySlots()->sole();

    $this->actingAs($user)->put("/grade-semanal/{$slot->id}", [...$payload, 'end_minute' => 1020])->assertSessionHasNoErrors();
    expect($slot->fresh()->end_minute)->toBe(1020);

    $this->actingAs($user)->delete("/grade-semanal/{$slot->id}")->assertSessionHas('status', 'slot-deleted');
    expect($user->weeklySlots()->exists())->toBeFalse();
});

it('validates weekday, time range and validity period', function (array $override, string $field): void {
    $payload = ['weekday' => 1, 'start_minute' => 840, 'end_minute' => 960, 'description' => 'Aula'];

    $this->actingAs(User::factory()->create())
        ->post('/grade-semanal', [...$payload, ...$override])
        ->assertSessionHasErrors($field);
})->with([
    'saturday' => [['weekday' => 6], 'weekday'],
    'end before start' => [['end_minute' => 800], 'end_minute'],
    'period reversed' => [['valid_from' => '2026-07-01', 'valid_until' => '2026-03-01'], 'valid_until'],
]);

it('hides slots from other users', function (): void {
    $slot = WeeklySlot::factory()->create();

    $this->actingAs(User::factory()->create())->delete("/grade-semanal/{$slot->id}")->assertNotFound();
});
