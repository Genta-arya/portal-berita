<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Str;

Artisan::command('editorial:create-admin {email} {name}', function () {
    $email = $this->argument('email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
        $this->error('Email tidak valid atau sudah digunakan.');
        return 1;
    }
    $password = $this->secret('Password admin (minimal 12 karakter)');
    if (! is_string($password) || strlen($password) < 12) {
        $this->error('Password minimal 12 karakter.');
        return 1;
    }
    $user = new User;
    $user->name = $this->argument('name');
    $user->email = $email;
    $user->password = $password;
    $user->role = 'admin';
    $user->active = true;
    $user->slug = Str::slug($user->name).'-'.Str::lower(Str::random(8));
    $user->save();
    $this->info('Akun admin dibuat.');
    return 0;
})->purpose('Membuat akun admin melalui terminal tanpa password bawaan');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
