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
    public function handle(Request $request, Closure $next, $requiredRole): Response
    {
        $user = $request->user(); // user yang login via sanctum

        if (! $user || $user->role !== $requiredRole) {
            return response()->json([
                'message' => 'Forbidden: insufficient role'
            ], 403);
        }

        return $next($request);
    }
}
