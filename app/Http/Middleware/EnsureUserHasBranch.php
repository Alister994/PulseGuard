<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasBranch
{
    /**
     * Ensure user has at least one branch (location) assigned, unless Super Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if ($user->isSuperAdmin()) {
            return $next($request);
        }
        if ($user->location_id !== null) {
            return $next($request);
        }
        if ($user->locations()->exists()) {
            return $next($request);
        }
        abort(403, 'No branch assigned. Contact administrator.');
    }
}
