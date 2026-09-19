<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\InviteUser;
use App\Actions\SendInvitation;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController
{
    public function index(Request $request): Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'isAdmin' => $user->is_admin,
                    'isSelf' => $user->is($request->user()),
                    'status' => $user->status()->value,
                    'invitationSentAt' => $user->invitation_sent_at?->toIso8601String(),
                ]),
        ]);
    }

    public function store(Request $request, InviteUser $inviteUser): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'is_admin' => ['boolean'],
        ]);

        $inviteUser->execute($validated['name'], $validated['email'], $request->boolean('is_admin'));

        return back()->with('status', 'user-invited');
    }

    public function resendInvitation(User $user, SendInvitation $sendInvitation): RedirectResponse
    {
        abort_unless($user->status() === UserStatus::InvitationPending, 422);

        $sendInvitation->execute($user);

        return back()->with('status', 'invitation-resent');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403);

        $validated = $request->validate([
            'active' => ['required_without:is_admin', 'boolean'],
            'is_admin' => ['required_without:active', 'boolean'],
        ]);

        if (array_key_exists('is_admin', $validated)) {
            $user->forceFill(['is_admin' => $validated['is_admin']])->save();

            return back()->with('status', $validated['is_admin'] ? 'user-promoted' : 'user-demoted');
        }

        DB::transaction(function () use ($user, $validated): void {
            $user->forceFill(['is_active' => $validated['active'], 'remember_token' => Str::random(60)])->save();

            if (! $validated['active']) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        });

        return back()->with('status', $validated['active'] ? 'user-unblocked' : 'user-blocked');
    }
}
