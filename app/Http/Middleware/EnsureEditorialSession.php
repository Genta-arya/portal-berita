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
        if (! $user || ! $user->active || ! in_array($user->role, ['developer', 'admin', 'editor', 'penulis'], true)
            || $request->session()->get('session_version') !== $user->session_version) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login');
        }

        Auth::setUser($user);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex');
        return $response;
    }
}
