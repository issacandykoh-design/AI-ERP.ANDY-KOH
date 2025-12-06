<?php

namespace Modules\RestAPI\Http\Middleware;

use Closure;
use Open\RestAPI\Exceptions\UnauthorizedException;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {

        // Do not apply this middleware to OPTIONS request
        if ($request->getMethod() !== 'OPTIONS') {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($user->status == 'inactive') {
                $user->currentAccessToken()->delete();
                return response()->json(['error' => 'User account disabled'], 403);
            }
        }

        return $next($request);
    }
}
