<?php
namespace Tests\Feature;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PopularCarouselTest extends TestCase
{
    use RefreshDatabase;
    public function test_carousel_contains_at_most_five_published_articles_ranked_by_recent_views(): void
    {
        $user=User::factory()->create();
        for($i=1;$i<=7;$i++){
            $a=new Article(['title'=>'Berita '.$i,'slug'=>'berita-'.$i]);$a->author_id=$user->id;$a->status=$i===7?'withdrawn':'published';$a->published_at=now()->subHour();$a->save();
            DB::table('article_view_daily')->insert(['article_id'=>$a->id,'date'=>now()->toDateString(),'views'=>$i*10]);
        }
        DB::table('article_view_daily')->insert(['article_id'=>1,'date'=>now()->subDays(10)->toDateString(),'views'=>9999]);
        $this->get('/')->assertOk()->assertViewHas('featured',fn($articles)=>$articles->pluck('title')->all()===['Berita 6','Berita 5','Berita 4','Berita 3','Berita 2'])->assertViewHas('popularTitle','Terpopuler')->assertSee('data-headline-carousel',false);
    }
}
