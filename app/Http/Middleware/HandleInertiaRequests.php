<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() === null ? null : [
                    ...$request->user()->only(['id', 'name', 'email']),
                    'isAdmin' => $request->user()->is_admin,
                ],
            ],
            'status' => fn (): ?string => $request->session()->get('status'),
        ];
    }
}
