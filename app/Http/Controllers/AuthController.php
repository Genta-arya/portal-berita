<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $user = $request->user()?->fresh();
        if ($user && $user->active && in_array($user->role, ['developer','admin','editor','penulis'], true)
            && $request->session()->get('session_version') === $user->session_version) {
            return redirect('/admin')->header('Cache-Control', 'private, no-store');
        }
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return response()->view('auth.login')->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt([...$credentials, 'active' => true])) {
            throw ValidationException::withMessages(['email' => 'Email atau password tidak sesuai.']);
        }
        $request->session()->regenerate();
        $request->session()->put('session_version', $request->user()->session_version);

        return redirect()->intended('/admin');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
