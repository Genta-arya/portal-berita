<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\KapuasRayaImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class KapuasRayaImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_is_private_sanitized_and_idempotent(): void
    {
        $root = storage_path('framework/testing/import-'.uniqid());
        File::ensureDirectoryExists($root);
        $fixtures = ['image-index' => [], 'media' => [], 'categories' => [['id' => 1, 'slug' => 'daerah', 'name' => 'Daerah', 'count' => 1]], 'tags' => [['id' => 2, 'slug' => 'kapuas', 'name' => 'Kapuas', 'count' => 1]], 'pages' => [['id' => 3, 'slug' => 'tentang-lama', 'title' => ['rendered' => 'Tentang'], 'content' => ['rendered' => '<p>Profil media</p>'], 'link' => 'https://kapuasraya.com/tentang/', 'date' => '2025-01-01T01:00:00']], 'posts' => [['id' => 5, 'slug' => 'berita-lama', 'title' => ['rendered' => 'Berita &amp; daerah'], 'excerpt' => ['rendered' => '<p>Ringkasan</p>'], 'content' => ['rendered' => '<p>Isi artikel lama</p><script>alert(1)</script>'], 'link' => 'https://kapuasraya.com/berita-lama/', 'date' => '2025-01-01T01:00:00', 'date_gmt' => '2024-12-31T18:00:00', 'modified' => '2025-01-01T02:00:00', 'author' => 4, 'categories' => [1], 'tags' => [2], 'featured_media' => 0]]];
        foreach ($fixtures as $name => $data) {
            file_put_contents($root.'/'.$name.'.json', json_encode($data));
        }
        try {
            $owner = User::factory()->create(['role' => 'admin']);
            $service = app(KapuasRayaImporter::class);
            $report = $service->run($root, $owner);
            $this->assertSame(1, $report['created']['articles']);
            $a = Article::first();
            $this->assertSame('draft', $a->status);
            $this->assertNull($a->published_at);
            $this->assertSame('Berita & daerah', $a->title);
            $this->assertStringNotContainsString('<script>', $a->body);
            $this->assertSame(1, $a->tags()->count());
            $this->assertFalse((bool) DB::table('pages')->value('active'));
            $this->get($a->url)->assertNotFound();
            $again = $service->run($root, $owner);
            $this->assertSame(0, $again['created']['articles']);
            $this->assertSame(1, Article::count());
            $this->assertSame(1, DB::table('pages')->count());
        } finally {
            File::deleteDirectory($root);
        }
    }
}
