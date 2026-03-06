<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAdminOrSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if (! $user->isSuperAdmin() && ! $user->isBranchAdmin()) {
            abort(403, 'Branch Admin or Super Admin access required.');
        }

        return $next($request);
    }
}
