<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // SuperAdmin has access to everything
        if ($request->user()->role === 'SuperAdmin') {
            return $next($request);
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Unauthorized - Insufficient permissions',
                'required_roles' => $roles,
                'your_role' => $request->user()->role
            ], 403);
        }

        return $next($request);
    }
}