<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (
            !$user ||
            !in_array($user->role, $roles)

        ) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is disabled.'], 403);
        }


        if ($user->email_verified_at === null) {
            return response()->json(['message' => 'Please verify your email before accessing this resource.'], 403);
        }


        return $next($request);
    }
}
