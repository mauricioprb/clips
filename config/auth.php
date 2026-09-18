<?php

declare(strict_types=1);

return [

    'passwords' => [
        'invitations' => [
            'provider' => 'users',
            'table' => 'invitation_tokens',
            'expire' => 60 * 24 * 7,
            'throttle' => 0,
        ],
    ],

];
