<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * Restrict administrator accounts from accessing regular user-only routes
     * such as /my-posts and /messages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_admin) {
            if ($request->expectsJson()) {
                abort(403, 'Admin accounts cannot access user post management or direct messages.');
            }

            return redirect()->route('admin.dashboard')
                ->with('warning', 'Access to My Posts and Messages is restricted for Admin accounts. Please use the Admin Panel.');
        }

        return $next($request);
    }
}
