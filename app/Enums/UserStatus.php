<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case InvitationPending = 'invitation_pending';
    case Blocked = 'blocked';
}
