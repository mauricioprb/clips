<?php

declare(strict_types=1);

use App\Http\Middleware\ThrottleFortifyWrites;
use Laravel\Fortify\Features;

return [

    'guard' => 'web',

    'passwords' => 'users',

    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    'home' => '/mes',

    'prefix' => '',

    'domain' => null,

    'middleware' => ['web', ThrottleFortifyWrites::class],

    'limiters' => [
        'login' => 'login',
    ],

    'paths' => [
        'login' => '/entrar',
        'logout' => '/sair',
        'password' => [
            'request' => '/esqueci-a-senha',
            'email' => '/esqueci-a-senha',
            'reset' => '/redefinir-senha/{token}',
            'update' => '/redefinir-senha',
        ],
        'user-profile-information' => [
            'update' => '/configuracoes/perfil',
        ],
        'user-password' => [
            'update' => '/configuracoes/senha',
        ],
    ],

    'views' => true,

    'features' => [
        Features::resetPasswords(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
    ],

];
