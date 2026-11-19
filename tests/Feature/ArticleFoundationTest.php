<?php
namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_cannot_edit_another_writer_or_published_content(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $article = new Article(['title' => 'Judul', 'slug' => 'judul']);
        $article->author_id = $owner->id;
        $article->save();
        $policy = new ArticlePolicy;
        $this->assertTrue($policy->update($owner, $article));
        $this->assertFalse($policy->view($other, $article));
        $article->status = 'published';
        $article->published_at = now();
        $article->save();
        $this->assertFalse($policy->update($owner, $article));
        $this->assertFalse($policy->delete($owner, $article));
    }

    public function test_public_scope_excludes_drafts_withdrawn_and_future_content(): void
    {
        $user = User::factory()->create();
        foreach (['draft', 'withdrawn', 'published'] as $status) {
            $article = new Article(['title' => $status, 'slug' => $status]);
            $article->author_id = $user->id;
            $article->status = $status;
            $article->published_at = now()->subMinute();
            $article->save();
        }
        $this->assertSame(['published'], Article::published()->pluck('title')->all());
        Article::where('status', 'published')->update(['published_at' => now()->addHour()]);
        $this->assertSame(0, Article::published()->count());
    }
}
