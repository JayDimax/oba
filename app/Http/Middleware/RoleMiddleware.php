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
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        // Optional: redirect if not logged in
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has the specified role
        if ($user->role !== $role) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
