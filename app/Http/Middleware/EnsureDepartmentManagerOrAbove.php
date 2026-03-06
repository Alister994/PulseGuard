<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartmentManagerOrAbove
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if (! $user->isSuperAdmin() && ! $user->isBranchAdmin() && ! $user->isHr() && ! $user->isDepartmentManager()) {
            abort(403, 'Department Manager or higher access required.');
        }

        return $next($request);
    }
}
