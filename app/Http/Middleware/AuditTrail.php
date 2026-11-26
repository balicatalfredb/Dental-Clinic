<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditTrail
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            $event = $request->isMethodSafe() ? 'access' : 'transaction';
            AuditLog::record($event, $request->route()?->getName() ?? $request->method().' '.$request->path(), $request->user());

            return $response;
        } catch (Throwable $exception) {
            $route = $request->route()?->getName() ?? $request->method().' '.$request->path();
            AuditLog::record('error', 'Unhandled '.class_basename($exception).' in '.$route.'.', $request->user());

            throw $exception;
        }
    }
}
