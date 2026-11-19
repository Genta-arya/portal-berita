<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Services\EditorialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $u): void
    {
        $this->actingAs($u)->withSession(['session_version' => $u->session_version]);
    }

    private function fixture(): array
    {
        Storage::fake('local');
        $writer = User::factory()->create(['slug' => 'penulis']);
        $editor = User::factory()->create(['role' => 'editor', 'slug' => 'editor']);
        $category = Category::create(['name' => 'Nasional', 'slug' => 'nasional']);
        $this->login($writer);
        $this->post('/admin/media', ['image' => UploadedFile::fake()->image('foto.jpg', 600, 400), 'alt' => 'Foto berita', 'credit' => 'Redaksi'])->assertRedirect();
        $media = DB::table('media')->value('id');
        $this->post('/admin/articles', ['title' => 'Berita uji editorial'])->assertRedirect();
        $a = Article::first();
        $this->putJson('/admin/articles/'.$a->id, ['version' => $a->version, 'title' => $a->title, 'slug' => 'berita-uji', 'summary' => 'Ringkasan berita uji', 'body' => '<p>Isi berita uji yang cukup panjang untuk diterbitkan.</p><script>alert(1)</script>', 'category_id' => $category->id, 'hero_media_id' => $media, 'hero_alt' => 'Foto berita', 'hero_credit' => 'Redaksi', 'tags' => 'Indonesia'])->assertOk();

        return [$writer, $editor, $a->fresh(), $media];
    }

    public function test_full_review_publish_withdraw_flow_and_media_privacy(): void
    {
        [$writer,$editor,$a,$media] = $this->fixture();
        $s = app(EditorialService::class);
        $this->assertStringNotContainsString('<script>', $a->body);
        $this->post('/logout');
        $this->get('/media/'.$media.'/480')->assertNotFound();
        $this->get($a->url)->assertNotFound();
        $a = $s->transition($a, $writer, 'submit', $a->version);
        $a = $s->transition($a, $editor, 'revise', $a->version, 'Perbaiki judul');
        $a = $s->transition($a, $writer, 'submit', $a->version);
        $a = $s->transition($a, $editor, 'approve', $a->version);
        $a = $s->transition($a, $editor, 'publish', $a->version);
        $this->get($a->url)->assertOk()->assertSee($a->title)->assertSee('application/ld+json', false);
        $this->get('/media/'.$media.'/480')->assertOk();
        $this->get('/media/'.$media.'/original')->assertNotFound();
        foreach (['/', '/terbaru', '/kanal/nasional', '/tag/indonesia', '/penulis/penulis', '/cari?q=Berita', '/rss.xml'] as $url) {
            $this->get($url)->assertOk()->assertSee($a->title);
        }
        $this->get('/sitemap.xml?page=1')->assertOk()->assertSee($a->url);
        $this->get('/berita/'.$a->id.'/slug-lama')->assertRedirect($a->url)->assertStatus(301);
        $first = $a->published_at->toDateTimeString();
        $a = $s->transition($a, $editor, 'withdraw', $a->version, 'Koreksi fakta');
        $this->get($a->url)->assertGone();
        $this->get('/media/'.$media.'/480')->assertNotFound();
        $a = $s->transition($a, $editor, 'reopen', $a->version);
        $this->get($a->url)->assertGone();
        $this->assertSame($first, $a->published_at->toDateTimeString());
    }

    public function test_stale_version_and_published_autosave_are_rejected(): void
    {
        [$writer,$editor,$a] = $this->fixture();
        $payload = ['version' => 1, 'title' => 'Perubahan basi', 'slug' => 'basi', 'body' => 'Isi'];
        $this->putJson('/admin/articles/'.$a->id, $payload)->assertStatus(409);
        $other = User::factory()->create();
        $this->login($other);
        $this->get('/admin/articles/'.$a->id.'/edit')->assertForbidden();
        $this->putJson('/admin/articles/'.$a->id, $payload)->assertForbidden();
        $s = app(EditorialService::class);
        $a = $s->transition($a, $writer, 'submit', $a->version);
        $a = $s->transition($a, $editor, 'approve', $a->version);
        $a = $s->transition($a, $editor, 'publish', $a->version);
        $this->login($editor);
        $payload['version'] = $a->version;
        $payload['autosave'] = true;
        $this->putJson('/admin/articles/'.$a->id, $payload)->assertForbidden();
    }

    public function test_scheduler_is_idempotent_and_respects_cancellation(): void
    {
        [$writer,$editor,$a] = $this->fixture();
        $s = app(EditorialService::class);
        $a = $s->transition($a, $writer, 'submit', $a->version);
        $a = $s->transition($a, $editor, 'approve', $a->version);
        $a = $s->transition($a, $editor, 'schedule', $a->version, null, now()->addMinutes(5)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'));
        $this->login($writer);
        $this->post('/admin/articles/'.$a->id.'/transition', ['version' => $a->version, 'action' => 'due'])->assertForbidden();
        $a = $s->transition($a, $editor, 'cancel', $a->version);
        $this->travel(6)->minutes();
        $this->artisan('editorial:publish-due')->assertSuccessful();
        $this->assertSame('approved', $a->fresh()->status);
        $a = $s->transition($a, $editor, 'schedule', $a->version, null, now()->addMinute()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'));
        $this->travel(2)->minutes();
        $this->artisan('editorial:publish-due')->assertSuccessful();
        $version = $a->fresh()->version;
        $this->artisan('editorial:publish-due')->assertSuccessful();
        $this->assertSame($version, $a->fresh()->version);
        $this->assertSame('published', $a->fresh()->status);
    }

    public function test_restore_keeps_history_and_referenced_media_cannot_be_deleted(): void
    {
        [$writer,$editor,$a,$media] = $this->fixture();
        $revision = $a->revisions()->latest('version')->first();
        $this->login($writer);
        $this->putJson('/admin/articles/'.$a->id, ['version' => $a->version, 'title' => 'Judul baru', 'slug' => 'judul-baru', 'body' => '<p>Naskah berubah dan tetap aman.</p>'])->assertOk();
        $a->refresh();
        $this->post('/admin/articles/'.$a->id.'/restore/'.$revision->id, ['version' => $a->version])->assertRedirect();
        $this->assertSame('Berita uji editorial',$a->fresh()->title);
        $this->assertGreaterThan($revision->version,$a->fresh()->version);
        $this->delete('/admin/media/'.$media)->assertStatus(422);
        $this->assertGreaterThan(1,$a->revisions()->count());
    }
}
