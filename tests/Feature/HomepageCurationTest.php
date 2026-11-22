<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomepageCurationTest extends TestCase
{
    use RefreshDatabase;

    private function article(User $user, string $title, ?int $categoryId = null): Article
    {
        $a = new Article(['title'=>$title,'slug'=>strtolower($title),'category_id'=>$categoryId]);
        $a->author_id=$user->id; $a->status='published'; $a->published_at=now()->subHour(); $a->save();
        return $a;
    }

    public function test_popular_ranking_and_counts_match_dashboard_including_ties_and_date_bounds(): void
    {
        $admin=User::factory()->create(['role'=>'admin']);
        $a=$this->article($admin,'Alpha');$b=$this->article($admin,'Beta');
        foreach([$a,$b] as $item) DB::table('article_view_daily')->insert(['article_id'=>$item->id,'date'=>now()->timezone('Asia/Jakarta')->toDateString(),'views'=>4]);
        DB::table('article_view_daily')->insert(['article_id'=>$a->id,'date'=>now()->subDays(7)->toDateString(),'views'=>999]);
        DB::table('article_view_daily')->insert(['article_id'=>$a->id,'date'=>now()->addDay()->toDateString(),'views'=>999]);
        $public=$this->get('/')->assertOk()->viewData('popular');
        $dashboard=$this->actingAs($admin)->withSession(['session_version'=>$admin->session_version])->get('/admin')->assertOk()->viewData('topArticles');
        $this->assertSame($public->pluck('id')->all(),$dashboard->pluck('id')->all());
        $this->assertSame([$b->id,$a->id],$public->pluck('id')->all());
        $this->assertSame([4,4],$public->pluck('total_views')->map(fn($v)=>(int)$v)->all());
    }

    public function test_curated_headlines_use_admin_order_and_do_not_restore_deleted_choices(): void
    {
        $admin=User::factory()->create(['role'=>'admin']);
        $a=$this->article($admin,'Alpha');$b=$this->article($admin,'Beta');
        DB::table('featured_placements')->insert([['article_id'=>$a->id,'location'=>'headline','position'=>2],['article_id'=>$b->id,'location'=>'headline','position'=>1]]);
        DB::table('site_settings')->insert(['key'=>'home_headlines_managed','value'=>'1']);
        $this->get('/')->assertOk()->assertViewHas('featured',fn($items)=>$items->pluck('id')->all()===[$b->id,$a->id]);
        $this->actingAs($admin)->withSession(['session_version'=>$admin->session_version]);
        $id=DB::table('featured_placements')->where('article_id',$a->id)->value('id');
        $this->post('/admin/manage/featured',['id'=>$id,'article_id'=>$a->id,'location'=>'headline','position'=>2])->assertRedirect();
        $this->assertDatabaseHas('featured_placements',['id'=>$id,'article_id'=>$a->id]);
        DB::table('featured_placements')->delete();
        $this->get('/')->assertOk()->assertViewHas('featured',fn($items)=>$items->isEmpty());
    }

    public function test_admin_can_curate_trending_topics_and_hide_inactive_choices(): void
    {
        $admin=User::factory()->create(['role'=>'admin']);
        $one=DB::table('categories')->insertGetId(['name'=>'Daerah','slug'=>'daerah','active'=>true]);
        $two=DB::table('categories')->insertGetId(['name'=>'Nasional','slug'=>'nasional','active'=>true]);
        $this->article($admin,'Alpha',$one);$this->article($admin,'Beta',$two);
        $this->actingAs($admin)->withSession(['session_version'=>$admin->session_version]);
        foreach([[$one,2],[$two,1]] as [$category,$position]) $this->post('/admin/manage/trending',['category_id'=>$category,'position'=>$position,'active'=>1])->assertRedirect();
        $this->get('/admin/manage/trending?q=Nasional')->assertOk()->assertViewHas('rows',fn($rows)=>$rows->count()===1);
        $this->get('/')->assertOk()->assertViewHas('trendingTopics',fn($topics)=>$topics->pluck('id')->all()===[$two,$one]);
        $id=DB::table('trending_topics')->where('category_id',$two)->value('id');
        $this->post('/admin/manage/trending',['id'=>$id,'category_id'=>$two,'position'=>1,'active'=>0])->assertRedirect();
        $this->get('/')->assertOk()->assertViewHas('trendingTopics',fn($topics)=>$topics->pluck('id')->all()===[$one]);
        $writer=User::factory()->create(['role'=>'penulis']);
        $this->actingAs($writer)->withSession(['session_version'=>$writer->session_version])->get('/admin/manage/trending')->assertForbidden();
    }
}
