<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_scopes_article_and_traffic_data_to_the_writer(): void
    {
        $writer = User::factory()->create(['role' => 'penulis']);
        $other = User::factory()->create();
        $mine = new Article(['title' => 'Artikel milik penulis', 'slug' => 'milik-penulis']);
        $mine->author_id = $writer->id;
        $mine->save();
        $theirs = new Article(['title' => 'Artikel privat penulis lain', 'slug' => 'penulis-lain']);
        $theirs->author_id = $other->id;
        $theirs->save();
        DB::table('article_view_daily')->insert([
            ['article_id' => $mine->id, 'date' => now()->toDateString(), 'views' => 12],
            ['article_id' => $theirs->id, 'date' => now()->toDateString(), 'views' => 99],
        ]);
        $this->actingAs($writer)->withSession(['session_version' => $writer->session_version])->get('/admin')
            ->assertOk()->assertSee('Artikel milik penulis')->assertDontSee('Artikel privat penulis lain')
            ->assertViewHas('weekViews', 12)->assertViewHas('views', 12)
            ->assertViewHas('traffic', fn ($traffic) => $traffic->count() === 7 && $traffic->sum('total') === 12)
            ->assertViewHas('counts', fn ($counts) => $counts['draft'] === 1);
    }

    public function test_empty_dashboard_has_zero_filled_traffic_and_no_growth_claim(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->withSession(['session_version' => $admin->session_version])->get('/admin')
            ->assertOk()->assertViewHas('weekViews', 0)->assertViewHas('growth', null)
            ->assertViewHas('traffic', fn ($traffic) => $traffic->count() === 7 && $traffic->sum('total') === 0)
            ->assertSee('Belum ada artikel.')->assertSee('Belum ada data tampilan');
    }
}
