<?php

namespace Open\RestAPI\Middleware;

use Closure;
use Illuminate\Http\Request;
use Open\RestAPI\ApiResponse;
use Open\RestAPI\Exceptions\UnauthorizedException;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via Sanctum
        if (!auth('sanctum')->check()) {
            return response()->json(['error' => 'Authentication required'], 403);
        }

        // Set the authenticated user
        $user = auth('sanctum')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Invalid authentication token'], 403);
        }

        // Set the default guard to sanctum
        config(['auth.defaults.guard' => 'sanctum']);

        return $next($request);
    }
}
