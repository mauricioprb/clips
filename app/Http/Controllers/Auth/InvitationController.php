<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController
{
    use PasswordValidationRules;

    public function show(Request $request, string $token): Response
    {
        $user = User::where('email', Str::lower((string) $request->query('email')))->first();
        $valid = $user?->status() === UserStatus::InvitationPending
            && Password::broker('invitations')->tokenExists($user, $token);

        return Inertia::render('Auth/AcceptInvitation', [
            'token' => $token,
            'email' => $valid ? $user->email : null,
            'name' => $valid ? $user->name : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => $this->passwordRules(),
        ]);

        $accepted = null;
        $status = Password::broker('invitations')->reset(
            [...$credentials, 'password_confirmation' => $request->input('password_confirmation')],
            function (User $user, string $password) use (&$accepted): void {
                if ($user->status() !== UserStatus::InvitationPending) {
                    return;
                }

                $user->forceFill([
                    'password' => $password,
                    'password_set_at' => now(),
                    'remember_token' => Str::random(60),
                ])->save();

                $accepted = $user;
            },
        );

        if ($status !== Password::PASSWORD_RESET || $accepted === null) {
            throw ValidationException::withMessages(['password' => __('passwords.invitation')]);
        }

        Auth::login($accepted);
        $request->session()->regenerate();

        return to_route('month.show')->with('status', 'invitation-accepted');
    }
}
