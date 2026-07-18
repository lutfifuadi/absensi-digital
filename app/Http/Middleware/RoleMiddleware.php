<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        $activeRole = session('active_role', $user->role);
        if (! in_array($activeRole, $roles, true)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
