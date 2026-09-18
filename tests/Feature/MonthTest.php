<?php

declare(strict_types=1);

use App\Models\DefaultActivity;
use App\Models\TimeEntry;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function (): void {
    $this->get('/mes')->assertRedirect('/entrar');
    $this->get('/')->assertRedirect('/mes');
});

it('shows the current month by default', function (): void {
    $this->travelTo('2026-03-15 10:00');

    $this->actingAs(User::factory()->create())
        ->get('/mes')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Month/Show')
            ->where('month', '2026-03')
            ->has('days', 31));
});

it('summarizes hours against the monthly target', function (): void {
    $user = User::factory()->create(['weekly_workload_minutes' => 1200]);
    TimeEntry::factory()->for($user)->create(['date' => '2026-03-02', 'start_minute' => 480, 'end_minute' => 720]);
    TimeEntry::factory()->for($user)->create(['date' => '2026-03-07', 'start_minute' => 480, 'end_minute' => 540]);
    TimeEntry::factory()->create(['date' => '2026-03-02']);

    $this->actingAs($user)
        ->get('/mes/2026/3')
        ->assertInertia(fn (Assert $page) => $page
            ->where('loggedMinutes', 300)
            ->where('workdayMinutes', 240)
            ->where('targetMinutes', 22 * 240)
            ->where('days.1.minutes', 240)
            ->where('days.1.entries.0.source', 'manual')
            ->where('days.6.isWorkday', false)
            ->where('setup.profile', true)
            ->where('setup.schedule', false));
});

it('marks holidays and offers descriptions as suggestions', function (): void {
    $user = User::factory()->create();
    DefaultActivity::factory()->for($user)->create(['description' => 'Pesquisa']);

    $this->actingAs($user)
        ->get('/mes/2026/4')
        ->assertInertia(fn (Assert $page) => $page
            ->where('days.2.holiday', 'Paixão de Cristo')
            ->where('days.2.isWorkday', false)
            ->where('suggestions', ['Pesquisa']));
});

it('rejects invalid months', function (): void {
    $this->actingAs(User::factory()->create())->get('/mes/2026/13')->assertNotFound();
});
