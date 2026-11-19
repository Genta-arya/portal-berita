<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ten_thousand_article_lists_remain_paginated_and_searchable(): void
    {
        $u = User::factory()->create();
        $c = Category::create(['name' => 'Uji kapasitas', 'slug' => 'uji']);
        for ($batch = 0; $batch < 10; $batch++) {
            $rows = [];
            for ($i = 0; $i < 1000; $i++) {
                $n = $batch * 1000 + $i;
                $rows[] = ['author_id' => $u->id, 'category_id' => $c->id, 'title' => 'Berita kapasitas '.$n, 'slug' => 'kapasitas-'.$n, 'body' => 'Isi pengujian kapasitas portal berita.', 'summary' => 'Ringkasan', 'status' => 'published', 'published_at' => now(), 'created_at' => now(), 'updated_at' => now()];
            }DB::table('articles')->insert($rows);
        }
        $start = microtime(true);
        $this->get('/terbaru?page=2')->assertOk()->assertSee('Berita kapasitas')->assertSee('page=3', false);
        $this->get('/cari?q=kapasitas')->assertOk()->assertSee('Berita kapasitas');
        $this->assertSame(10000, DB::table('articles')->count());
        file_put_contents(storage_path('framework/capacity-result.json'), json_encode(['articles' => 10000, 'driver' => config('database.default'), 'two_requests_ms' => round((microtime(true) - $start) * 1000, 2), 'note' => 'Serial test requests; bukan benchmark konkurensi atau Lighthouse.']));
    }
}
