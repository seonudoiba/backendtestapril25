<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompanyScopeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // SuperAdmin can access everything without company scope
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }
        
        // Regular users must have a company
        if ($user && !$user->company_id) {
            return response()->json([
                'message' => 'User account not properly configured. Please contact administrator.'
            ], 403);
        }
        
        // Scope regular users to their company
        if ($user && $user->company_id) {
            $request->merge(['company_id' => $user->company_id]);
            $request->attributes->set('company_id', $user->company_id);
        }

        return $next($request);
    }
}