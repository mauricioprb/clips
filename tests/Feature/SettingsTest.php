<?php

declare(strict_types=1);

use App\Enums\HolidayRecurrence;
use App\Models\Holiday;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the scholarship profile', function (): void {
    $user = User::factory()->create(['weekly_workload_minutes' => 1200]);

    $this->actingAs($user)
        ->get('/configuracoes/perfil')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Settings/Profile')
            ->where('profile.email', $user->email)
            ->where('profile.weeklyWorkloadHours', 20));
});

it('updates the scholarship profile', function (): void {
    $user = User::factory()->withoutProfile()->create();

    $this->actingAs($user)
        ->put('/configuracoes/perfil', [
            'name' => 'Ana Souza',
            'email' => 'ana@example.com',
            'advisor_name' => 'Prof. Carlos Lima',
            'scholarship_name' => 'CAPES',
            'laboratories' => ['Lab 1', 'Sala 204'],
            'weekly_workload_hours' => 12.5,
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh())
        ->advisor_name->toBe('Prof. Carlos Lima')
        ->laboratories->toBe(['Lab 1', 'Sala 204'])
        ->weekly_workload_minutes->toBe(750)
        ->hasScholarshipProfile()->toBeTrue();
});

it('validates the scholarship profile', function (): void {
    $this->actingAs(User::factory()->create())
        ->put('/configuracoes/perfil', ['name' => 'Ana', 'email' => 'ana@example.com', 'laboratories' => [], 'weekly_workload_hours' => 50])
        ->assertSessionHasErrors(['advisor_name', 'scholarship_name', 'laboratories', 'weekly_workload_hours']);
});

it('lists user holidays and the resulting calendar for the year', function (): void {
    $this->travelTo('2026-03-15');
    $user = User::factory()->create();
    Holiday::factory()->for($user)->create(['name' => 'Aniversário da cidade', 'date' => '2020-05-17']);

    $this->actingAs($user)
        ->get('/configuracoes/feriados')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Settings/Holidays')
            ->where('year', 2026)
            ->has('holidays', 1)
            ->where('calendar', fn ($calendar) => collect($calendar)->contains(['date' => '2026-05-17', 'name' => 'Aniversário da cidade'])));
});

it('adds and removes holidays', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/configuracoes/feriados', ['name' => 'Corpus Christi', 'recurrence' => 'corpus_christi'])->assertSessionHasNoErrors();
    $this->actingAs($user)->post('/configuracoes/feriados', ['name' => 'Sem data', 'recurrence' => 'yearly'])->assertSessionHasErrors('date');

    $holiday = $user->holidays()->sole();
    expect($holiday->recurrence)->toBe(HolidayRecurrence::CorpusChristi);

    $this->actingAs(User::factory()->create())->delete("/configuracoes/feriados/{$holiday->id}")->assertNotFound();
    $this->actingAs($user)->delete("/configuracoes/feriados/{$holiday->id}")->assertSessionHas('status', 'holiday-deleted');
});

it('changes the password', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/configuracoes/senha', ['current_password' => 'password1', 'password' => 'novaSenha9', 'password_confirmation' => 'novaSenha9'])
        ->assertSessionHasNoErrors();

    expect(Hash::check('novaSenha9', $user->fresh()->password))->toBeTrue();
});

it('deletes the account and its data after confirming the password', function (): void {
    $user = User::factory()->create();
    $user->timeEntries()->create(['date' => '2026-03-02', 'start_minute' => 480, 'end_minute' => 600, 'description' => 'X', 'source' => 'manual']);

    $this->actingAs($user)->delete('/configuracoes/conta', ['password' => 'errada'])->assertSessionHasErrors('password');
    expect($user->fresh())->not->toBeNull();

    $this->actingAs($user)->delete('/configuracoes/conta', ['password' => 'password1'])->assertRedirect('/entrar');

    $this->assertGuest();
    expect(User::find($user->id))->toBeNull()
        ->and(TimeEntry::count())->toBe(0);
});
