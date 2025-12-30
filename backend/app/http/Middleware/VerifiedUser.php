<?php

namespace App\Http\Middleware;

use Closure;

class VerifiedUser
{
    public function handle($request, Closure $next)
    {
        if (!$request->user()->is_verified) {
            return response()->json(['error' => 'User not verified'], 403);
        }
        return $next($request);
    }
}
