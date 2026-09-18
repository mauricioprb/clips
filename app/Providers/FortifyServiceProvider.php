<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            FailedPasswordResetLinkRequestResponse::class,
            fn (): SuccessfulPasswordResetLinkRequestResponse => new SuccessfulPasswordResetLinkRequestResponse(Password::RESET_LINK_SENT),
        );
    }

    public function boot(): void
    {
        Fortify::authenticateUsing(function (Request $request): ?User {
            $user = User::where('email', Str::lower((string) $request->input(Fortify::username())))->first();

            return $user?->status() === UserStatus::Active && Hash::check((string) $request->input('password'), $user->password) ? $user : null;
        });
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->configureViews();
        $this->configureRateLimiting();
    }

    private function configureViews(): void
    {
        Fortify::loginView(fn () => Inertia::render('Auth/Login'));
        Fortify::requestPasswordResetLinkView(fn () => Inertia::render('Auth/ForgotPassword'));
        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('Auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => (string) $request->query('email', ''),
        ]));
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(Str::transliterate(Str::lower((string) $request->input(Fortify::username())) . '|' . $request->ip()));
        });

        RateLimiter::for('password-reset-link', function (Request $request): array {
            return [
                Limit::perMinute(5)->by('password-reset-link-ip|' . $request->ip()),
                Limit::perHour(3)->by('password-reset-link-email|' . $this->emailKey($request)),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request): array {
            return [
                Limit::perMinute(5)->by('password-reset-ip|' . $request->ip()),
                Limit::perHour(5)->by('password-reset-email|' . $this->emailKey($request)),
            ];
        });
    }

    private function emailKey(Request $request): string
    {
        $email = Str::lower(trim((string) $request->input(Fortify::email())));

        return $email !== '' ? $email : 'missing|' . $request->ip();
    }
}
