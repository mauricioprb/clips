<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleFortifyWrites
{
    public function __construct(
        private readonly ThrottleRequests $throttleRequests,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $limiter = match (true) {
            $request->routeIs('password.email') => 'password-reset-link',
            $request->routeIs('password.update') => 'password-reset',
            default => null,
        };

        if ($limiter === null) {
            return $next($request);
        }

        return $this->throttleRequests->handle($request, $next, $limiter);
    }
}
