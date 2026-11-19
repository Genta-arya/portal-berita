<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEditorialSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user()?->fresh();
        if (! $user || ! $user->active || ! in_array($user->role, ['admin', 'editor', 'penulis'], true)
            || $request->session()->get('session_version') !== $user->session_version) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login');
        }

        Auth::setUser($user);

        return $next($request)->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex');
    }
}
