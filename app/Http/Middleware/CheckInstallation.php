<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installed = file_exists(storage_path('installed'));

        if (!$installed && !$request->is('install*')) {
            return redirect('/install');
        }

        if ($installed && $request->is('install*')) {
            return redirect('/');
        }

        return $next($request);
    }
}
