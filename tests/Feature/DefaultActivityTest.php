<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Models\DefaultActivity;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists the user activities alphabetically', function (): void {
    $user = User::factory()->create();
    DefaultActivity::factory()->for($user)->create(['description' => 'Revisão']);
    DefaultActivity::factory()->for($user)->create(['description' => 'Leitura']);

    $this->actingAs($user)
        ->get('/atividades-padrao')
        ->assertInertia(fn (Assert $page) => $page
            ->component('DefaultActivities/Index')
            ->where('activities.0.description', 'Leitura')
            ->where('activities.1.description', 'Revisão'));
});

it('creates, updates and deletes an activity', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/atividades-padrao', ['description' => 'Leitura', 'priority' => 'high'])->assertSessionHas('status', 'activity-saved');
    $activity = $user->defaultActivities()->sole();
    expect($activity->priority)->toBe(Priority::High);

    $this->actingAs($user)->put("/atividades-padrao/{$activity->id}", ['description' => 'Leitura', 'priority' => 'low'])->assertSessionHasNoErrors();
    expect($activity->fresh()->priority)->toBe(Priority::Low);

    $this->actingAs($user)->delete("/atividades-padrao/{$activity->id}")->assertSessionHas('status', 'activity-deleted');
    expect($user->defaultActivities()->exists())->toBeFalse();
});

it('rejects an unknown priority', function (): void {
    $this->actingAs(User::factory()->create())
        ->post('/atividades-padrao', ['description' => 'Leitura', 'priority' => 'urgent'])
        ->assertSessionHasErrors('priority');
});

it('hides activities from other users', function (): void {
    $activity = DefaultActivity::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put("/atividades-padrao/{$activity->id}", ['description' => 'X', 'priority' => 'low'])
        ->assertNotFound();
});
