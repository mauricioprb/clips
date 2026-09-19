<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the auth pages', function (string $url, string $component): void {
    $this->get($url)->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['/entrar', 'Auth/Login'],
    ['/esqueci-a-senha', 'Auth/ForgotPassword'],
    ['/redefinir-senha/token?email=ana@example.com', 'Auth/ResetPassword'],
]);

it('logs in and out', function (): void {
    $user = User::factory()->create();

    $this->post('/entrar', ['email' => $user->email, 'password' => 'password1'])->assertRedirect('/mes');
    $this->assertAuthenticatedAs($user);

    $this->post('/sair')->assertRedirect('/');
    $this->assertGuest();
});

it('rejects wrong credentials', function (): void {
    $user = User::factory()->create();

    $this->post('/entrar', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('has no self registration', function (): void {
    $this->get('/cadastro')->assertNotFound();
    $this->post('/cadastro', ['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'segura123', 'password_confirmation' => 'segura123'])
        ->assertNotFound();

    expect(User::count())->toBe(0);
});

it('refuses blocked and pending users at login', function (): void {
    $blocked = User::factory()->blocked()->create();
    $pending = User::factory()->pendingInvitation()->create();

    $this->post('/entrar', ['email' => $blocked->email, 'password' => 'password1'])->assertSessionHasErrors('email');
    $this->post('/entrar', ['email' => $pending->email, 'password' => 'password1'])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('marks the password as set when a pending user resets it', function (): void {
    Notification::fake();
    $user = User::factory()->pendingInvitation()->create();

    $this->post('/esqueci-a-senha', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
        $this->post('/redefinir-senha', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'novaSenha9',
            'password_confirmation' => 'novaSenha9',
        ])->assertSessionHasNoErrors();

        return true;
    });

    expect($user->fresh()->password_set_at)->not->toBeNull();
});

it('sends a reset link without revealing whether the email exists', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/esqueci-a-senha', ['email' => $user->email])->assertSessionHas('status');
    $this->post('/esqueci-a-senha', ['email' => 'nobody@example.com'])->assertSessionHasNoErrors()->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets the password with a valid token', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/esqueci-a-senha', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
        $this->post('/redefinir-senha', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'novaSenha9',
            'password_confirmation' => 'novaSenha9',
        ])->assertSessionHasNoErrors()->assertRedirect('/entrar');

        return true;
    });
});

it('throttles reset link requests per email', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    foreach (range(1, 3) as $attempt) {
        $this->post('/esqueci-a-senha', ['email' => $user->email]);
    }

    $this->post('/esqueci-a-senha', ['email' => $user->email])->assertTooManyRequests();
});

it('sends the password reset email in Portuguese', function (): void {
    $mail = (new ResetPassword('token'))->toMail(User::factory()->make());

    expect($mail->subject)->toBe('Redefina sua senha do Clips')
        ->and($mail->actionText)->toBe('Redefinir senha');
});
