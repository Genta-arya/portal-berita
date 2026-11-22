<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BreakingNewsSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticker_uses_only_active_in_window_admin_entries_and_reflects_edits(): void
    {
        $admin=User::factory()->create(['role'=>'admin']);
        $this->actingAs($admin)->withSession(['session_version'=>$admin->session_version]);
        $this->post('/admin/manage/breaking',[
            'text'=>'Kabar sinkron redaksi','url'=>'https://example.com/berita', 'active'=>1,
            'starts_at'=>now()->timezone('Asia/Jakarta')->subHour()->format('Y-m-d\TH:i'),
            'ends_at'=>now()->timezone('Asia/Jakarta')->addHour()->format('Y-m-d\TH:i'),
        ])->assertRedirect();
        $row=DB::table('breaking_news')->first();
        DB::table('breaking_news')->insert([
            ['text'=>'Kabar masa depan','active'=>1,'starts_at'=>now()->addDay(),'ends_at'=>now()->addDays(2)],
            ['text'=>'Kabar kedaluwarsa','active'=>1,'starts_at'=>now()->subDays(2),'ends_at'=>now()->subDay()],
            ['text'=>'Kabar nonaktif','active'=>0,'starts_at'=>now()->subHour(),'ends_at'=>now()->addHour()],
        ]);
        $this->get('/')->assertOk()->assertSee('Kabar sinkron redaksi')->assertSee('BREAKING NEWS')->assertDontSee('Kabar masa depan')->assertDontSee('Kabar kedaluwarsa')->assertDontSee('Kabar nonaktif');
        $data=['id'=>$row->id,'text'=>'Kabar diperbarui langsung','url'=>'https://example.com/update','active'=>1,'starts_at'=>now()->timezone('Asia/Jakarta')->subHour()->format('Y-m-d\TH:i'),'ends_at'=>now()->timezone('Asia/Jakarta')->addHour()->format('Y-m-d\TH:i')];
        $this->post('/admin/manage/breaking',$data)->assertRedirect();
        $this->get('/')->assertOk()->assertSee('Kabar diperbarui langsung')->assertSee('https://example.com/update',false)->assertDontSee('Kabar sinkron redaksi');
        $this->post('/admin/manage/breaking',[...$data,'active'=>0])->assertRedirect();
        $this->get('/')->assertOk()->assertDontSee('Kabar diperbarui langsung');
    }
}
