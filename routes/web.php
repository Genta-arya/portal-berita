<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureEditorialSession;

Route::get('/login', fn () => response()->view('auth.login')->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/admin', fn () => view('admin.dashboard'))->middleware(['auth', EnsureEditorialSession::class])->name('admin');

Route::get('/', function () {
    return view('home');
});
