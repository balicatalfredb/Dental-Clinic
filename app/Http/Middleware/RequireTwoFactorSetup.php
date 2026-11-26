<?php

namespace App\Http\Middleware;

use App\Services\SystemSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $allowedRoutes = [
            'profile',
            'profile.update',
            'two-factor.setup',
            'two-factor.enable',
            'two-factor.disable',
            'logout',
        ];

        if (
            $user
            && app(SystemSettings::class)->enabled('mfa_enabled', false)
            && ! $user->two_factor_enabled_at
            && ! $request->routeIs(...$allowedRoutes)
            && ! $request->routeIs('two-factor.email.*')
        ) {
            return to_route('two-factor.setup');
        }

        return $next($request);
    }
}
