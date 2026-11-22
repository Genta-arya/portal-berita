<?php

use App\Models\Article;
use App\Models\User;
use App\Services\BackupService;
use App\Services\EditorialService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

Artisan::command('editorial:backup {path}', function () {
    $path = $this->argument('path');
    if (! is_dir(dirname($path))) {
        $this->error('Direktori tujuan harus sudah tersedia dan berada di luar public.');

        return 1;
    }
    $public = realpath(public_path());
    $parent = realpath(dirname($path));
    if (str_starts_with(strtolower($parent), strtolower($public))) {
        $this->error('Backup tidak boleh disimpan dalam public.');

        return 1;
    }
    app(BackupService::class)->create($path);
    $this->info('Backup dibuat. Simpan salinan di lokasi terpisah.');
});
Artisan::command('editorial:restore {path}', function () {
    app(BackupService::class)->restore($this->argument('path'));
    $this->info('Backup dipulihkan. Semua sesi sebelumnya dibatalkan.');
});
Artisan::command('editorial:reset-password {email}', function () {
    $user = User::where('email', $this->argument('email'))->firstOrFail();
    $password = $this->secret('Password baru (minimal 12 karakter)');
    if (! is_string($password) || strlen($password) < 8) {
        $this->error('Password terlalu pendek.');

        return 1;
    }
    $user->password = $password;
    $user->session_version++;
    $user->remember_token = null;
    $user->save();
    app(EditorialService::class)->audit(null, 'cli_password_reset', 'user', $user->id);
    $this->info('Password diganti dan sesi lama dibatalkan.');
});

Artisan::command('editorial:publish-due', function () {
    $count = 0;
    Article::where('status', 'scheduled')->where('scheduled_at', '<=', now())->orderBy('id')->chunkById(100, function ($articles) use (&$count) {
        foreach ($articles as $article) {
            try {
                app(EditorialService::class)->transition($article, null, 'due', $article->version);
                $count++;
            } catch (HttpException $e) {
                if (! in_array($e->getStatusCode(), [409, 422])) {
                    throw $e;
                }
            } catch (ValidationException $e) {
                report($e);
            }
        }
    });
    $this->info($count.' artikel diterbitkan.');
})->purpose('Menerbitkan jadwal yang jatuh tempo secara idempoten');
Schedule::command('editorial:publish-due')->everyMinute()->withoutOverlapping();
Artisan::command('editorial:prune', function () {
    $db = DB::class;
    $ids = $db::table('article_view_daily')->where('date', '<', now()->subDays(90)->toDateString())->limit(1000)->pluck('id');
    $db::table('article_view_daily')->whereIn('id', $ids)->delete();
    $ids = $db::table('audit_logs')->where('created_at', '<', now()->subYear())->limit(1000)->pluck('id');
    $db::table('audit_logs')->whereIn('id', $ids)->delete();
    $this->info('Agregat kedaluwarsa dibersihkan.');
});
Schedule::command('editorial:prune')->daily();

Artisan::command('editorial:create-admin {email} {name} {--developer= : ID developer aktif yang membuat akun}', function () {
    $developer = User::find($this->option('developer'));
    if (!$developer || !$developer->isDeveloper()) {
        $this->error('Pembuatan admin memerlukan --developer=ID developer aktif.');
        return 1;
    }
    $email = $this->argument('email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
        $this->error('Email tidak valid atau sudah digunakan.');

        return 1;
    }
    $password = $this->secret('Password admin (minimal 8 karakter)');
    if (! is_string($password) || strlen($password) < 8) {
        $this->error('Password minimal 8 karakter.');

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
    app(EditorialService::class)->audit($developer, 'cli_admin_created', 'user', $user->id);
    $this->info('Akun admin dibuat.');

    return 0;
})->purpose('Membuat akun admin melalui terminal tanpa password bawaan');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
