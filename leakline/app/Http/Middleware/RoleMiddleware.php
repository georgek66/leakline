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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    Public function handle(Request $request, Closure $next, ...$roles)
    {


        $user = $request->user(); // null if not logged in

        // If this middleware is ever hit without auth, fail safely (no redirect loop)
        if (!$user) {
            abort(403);
        }

        $userRole = optional($user->role)->name;

        if (!$userRole || !in_array($userRole, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
