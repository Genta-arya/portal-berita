<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicationBoundariesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::factory()->create(['role' => 'admin']);
        $this->actingAs($u)->withSession(['session_version' => 1]);

        return $u;
    }

    public function test_banner_overlap_is_rejected_and_expired_content_is_hidden(): void
    {
        Storage::fake('local');
        $this->admin();
        $this->post('/admin/media', ['image' => UploadedFile::fake()->image('banner.jpg'), 'alt' => 'Banner', 'credit' => 'Pemilik'])->assertRedirect();
        $mid = DB::table('media')->value('id');
        $start = now()->subMinute()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        $end = now()->addMinute()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        $data = ['slot' => 'home_top', 'media_id' => $mid, 'alt' => 'Sponsor uji', 'url' => 'https://example.com', 'starts_at' => $start, 'ends_at' => $end, 'active' => 1];
        $this->post('/admin/manage/banners', $data)->assertRedirect();
        $this->post('/admin/manage/banners', $data)->assertStatus(422);
        $this->post('/admin/manage/breaking', ['text' => 'Breaking uji', 'starts_at' => $start, 'ends_at' => $end, 'active' => 1])->assertRedirect();
        $this->get('/')->assertSee('Sponsor uji')->assertSee('Breaking uji');
        $this->travel(2)->minutes();
        $this->get('/')->assertDontSee('Sponsor uji')->assertDontSee('Breaking uji');
        $this->post('/logout');
        $this->get('/media/'.$mid.'/480')->assertNotFound();
    }

    public function test_dangerous_upload_and_anonymous_preview_are_rejected(): void
    {
        Storage::fake('local');
        $this->admin();
        $this->postJson('/admin/media', ['image' => UploadedFile::fake()->create('evil.svg', 1, 'image/svg+xml'), 'alt' => 'x', 'credit' => 'x'])->assertUnprocessable();
        $this->postJson('/admin/media', ['image' => UploadedFile::fake()->image('large.jpg')->size(6000), 'alt' => 'x', 'credit' => 'x'])->assertUnprocessable();
        $this->post('/admin/articles', ['title' => 'Draft privat']);
        $id = DB::table('articles')->value('id');
        $this->get('/admin/articles/'.$id.'/edit')->assertOk()->assertSee('article-editor');
        $this->get('/admin/articles/'.$id.'/preview')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->post('/logout');
        $this->get('/admin/articles/'.$id.'/preview')->assertRedirect('/login');
    }
}
