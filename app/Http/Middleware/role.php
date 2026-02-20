<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // allow only authenticated users with role 'user'
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (! isset($user->role) || $user->role !== 'user') {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
