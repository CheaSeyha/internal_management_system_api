<?php

namespace App\Http\Middleware;

use App\Helper\ResponseHelper;
use Closure;
use Illuminate\Http\Request;

class CheckUserRoleBase
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // The only 2 roles we allow
        $allowed = [1, 2]; // super admin + admin
        if (!in_array($user->role_id, $allowed)) {
            return ResponseHelper::fail("Forbidden. You do not have permission to access this resource.", 403);
        }

        return $next($request);
    }
}
