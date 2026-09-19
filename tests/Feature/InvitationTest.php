<?php

declare(strict_types=1);

use App\Actions\InviteUser;
use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

function invitationUrlFor(User $user): string
{
    $url = null;

    Notification::assertSentTo($user, UserInvitation::class, function (UserInvitation $notification) use (&$url): bool {
        $url = $notification->url;

        return true;
    });

    return parse_url($url, PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);
}

function invitationTokenFrom(string $url): string
{
    return basename(parse_url($url, PHP_URL_PATH));
}

it('lets an admin invite a person by name and email', function (): void {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/usuarios', ['name' => 'Ana Souza', 'email' => 'Ana@Example.com', 'is_admin' => false])
        ->assertSessionHas('status', 'user-invited');

    $invited = User::where('email', 'ana@example.com')->sole();

    expect($invited)
        ->status()->toBe(UserStatus::InvitationPending)
        ->is_admin->toBeFalse()
        ->invitation_sent_at->not->toBeNull()
        ->and($invited->holidays()->count())->toBe(2);

    Notification::assertSentTo($invited, UserInvitation::class);
});

it('rejects duplicate emails', function (): void {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'ana@example.com']);

    $this->actingAs($admin)->post('/usuarios', ['name' => 'Ana', 'email' => 'ana@example.com'])->assertSessionHasErrors('email');
});

it('keeps the user management area for admins only', function (): void {
    $member = User::factory()->create();
    $pending = User::factory()->pendingInvitation()->create();

    $this->actingAs($member)->get('/usuarios')->assertForbidden();
    $this->actingAs($member)->post('/usuarios', ['name' => 'X', 'email' => 'x@example.com'])->assertForbidden();
    $this->actingAs($member)->put("/usuarios/{$pending->id}", ['active' => false])->assertForbidden();
});

it('lists users with their access status', function (): void {
    $admin = User::factory()->admin()->create(['name' => 'Admin']);
    User::factory()->pendingInvitation()->create(['name' => 'Bruna']);
    User::factory()->blocked()->create(['name' => 'Carlos']);

    $this->actingAs($admin)
        ->get('/usuarios')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Index')
            ->where('users.0.isSelf', true)
            ->where('users.1.status', 'invitation_pending')
            ->where('users.2.status', 'blocked'));
});

it('accepts an invitation, sets the password and logs the person in', function (): void {
    Notification::fake();
    ['user' => $user] = app(InviteUser::class)->execute('Ana Souza', 'ana@example.com');
    $url = invitationUrlFor($user);

    $this->get($url)->assertInertia(fn (Assert $page) => $page
        ->component('Auth/AcceptInvitation')
        ->where('email', 'ana@example.com')
        ->where('name', 'Ana Souza'));

    $this->post('/convite', [
        'token' => invitationTokenFrom($url),
        'email' => 'ana@example.com',
        'password' => 'segura123',
        'password_confirmation' => 'segura123',
    ])->assertRedirect('/mes')->assertSessionHas('status', 'invitation-accepted');

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->status())->toBe(UserStatus::Active);
});

it('refuses invalid, used and expired invitations', function (): void {
    Notification::fake();
    ['user' => $user] = app(InviteUser::class)->execute('Ana Souza', 'ana@example.com');
    $token = invitationTokenFrom(invitationUrlFor($user));
    $payload = ['token' => $token, 'email' => 'ana@example.com', 'password' => 'segura123', 'password_confirmation' => 'segura123'];

    $this->get('/convite/invalido?email=ana@example.com')->assertInertia(fn (Assert $page) => $page->where('email', null));
    $this->post('/convite', [...$payload, 'token' => 'invalido'])->assertSessionHasErrors('password');

    $this->travel(8)->days();
    $this->post('/convite', $payload)->assertSessionHasErrors('password');
    $this->assertGuest();
});

it('does not let a blocked person accept an invitation', function (): void {
    Notification::fake();
    ['user' => $user] = app(InviteUser::class)->execute('Ana Souza', 'ana@example.com');
    $token = invitationTokenFrom(invitationUrlFor($user));
    $user->update(['is_active' => false]);

    $this->post('/convite', ['token' => $token, 'email' => 'ana@example.com', 'password' => 'segura123', 'password_confirmation' => 'segura123'])
        ->assertSessionHasErrors('password');
    $this->assertGuest();
});

it('resends a pending invitation only', function (): void {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $pending = User::factory()->pendingInvitation()->create();
    $active = User::factory()->create();

    $this->actingAs($admin)->post("/usuarios/{$pending->id}/convite")->assertSessionHas('status', 'invitation-resent');
    $this->actingAs($admin)->post("/usuarios/{$active->id}/convite")->assertStatus(422);

    Notification::assertSentTo($pending, UserInvitation::class);
    Notification::assertNotSentTo($active, UserInvitation::class);
});

it('blocks and unblocks a person but never the admin themselves', function (): void {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->actingAs($admin)->put("/usuarios/{$member->id}", ['active' => false])->assertSessionHas('status', 'user-blocked');
    expect($member->fresh()->status())->toBe(UserStatus::Blocked);

    $this->actingAs($admin)->put("/usuarios/{$member->id}", ['active' => true])->assertSessionHas('status', 'user-unblocked');
    expect($member->fresh()->status())->toBe(UserStatus::Active);

    $this->actingAs($admin)->put("/usuarios/{$admin->id}", ['active' => false])->assertForbidden();
});

it('grants and revokes the permission to invite, but not for the admin themselves', function (): void {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->actingAs($admin)->put("/usuarios/{$member->id}", ['is_admin' => true])->assertSessionHas('status', 'user-promoted');
    expect($member->fresh()->is_admin)->toBeTrue();

    $this->actingAs($member->fresh())->get('/usuarios')->assertOk();

    $this->actingAs($admin)->put("/usuarios/{$member->id}", ['is_admin' => false])->assertSessionHas('status', 'user-demoted');
    expect($member->fresh()->is_admin)->toBeFalse();

    $this->actingAs($admin)->put("/usuarios/{$admin->id}", ['is_admin' => false])->assertForbidden();
    $this->actingAs($admin)->put("/usuarios/{$member->id}", [])->assertSessionHasErrors(['active', 'is_admin']);
});

it('invites the first admin from the command line', function (): void {
    Notification::fake();

    $this->artisan('users:invite', ['email' => 'coord@example.com', 'name' => 'Coordenação', '--admin' => true])
        ->expectsOutputToContain('/convite/')
        ->assertSuccessful();

    expect(User::where('email', 'coord@example.com')->sole())
        ->is_admin->toBeTrue()
        ->status()->toBe(UserStatus::InvitationPending);

    $this->artisan('users:invite', ['email' => 'coord@example.com', 'name' => 'Outra'])->assertFailed();
});

it('renders the invitation email with the Clips branding', function (): void {
    $user = User::factory()->make(['name' => 'Ana Souza']);

    $html = (string) (new UserInvitation('https://clips.test/convite/abc'))->toMail($user)->render();

    expect($html)
        ->toContain('images/logo_clips_email.png')
        ->toContain('Olá, Ana Souza!')
        ->toContain('https://clips.test/convite/abc')
        ->toContain('#135fa6');
});
