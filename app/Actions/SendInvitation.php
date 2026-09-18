<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\Password;
use LogicException;

class SendInvitation
{
    public function execute(User $user): string
    {
        if ($user->status() !== UserStatus::InvitationPending) {
            throw new LogicException('Only pending invitations can be sent.');
        }

        $token = Password::broker('invitations')->createToken($user);
        $url = route('invitation.show', ['token' => $token, 'email' => $user->email]);

        $user->forceFill(['invitation_sent_at' => now()])->save();
        $user->notify((new UserInvitation($url))->locale('pt_BR'));

        return $url;
    }
}
