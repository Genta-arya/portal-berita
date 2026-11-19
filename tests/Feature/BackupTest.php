<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_restores_database_and_private_media(): void
    {
        Storage::fake('local');
        $u = User::factory()->create();
        Storage::disk('local')->put('editorial/demo/480.webp', 'media-test');
        $a = new Article(['title' => 'Artikel dalam backup', 'slug' => 'backup']);
        $a->author_id = $u->id;
        $a->save();
        $path = tempnam(sys_get_temp_dir(), 'portal-backup');
        $s = app(BackupService::class);
        $s->create($path);
        Schema::disableForeignKeyConstraints();
        foreach (array_reverse(BackupService::TABLES) as $table) {
            DB::table($table)->delete();
        }Schema::enableForeignKeyConstraints();
        Storage::disk('local')->delete('editorial/demo/480.webp');
        $s->restore($path);
        $this->assertSame($u->email, User::first()->email);
        $this->assertSame(2, User::first()->session_version);
        $this->assertSame('Artikel dalam backup', Article::first()->title);
        $this->assertSame('media-test', Storage::disk('local')->get('editorial/demo/480.webp'));
        unlink($path);
    }
}
