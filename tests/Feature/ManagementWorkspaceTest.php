<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ManagementWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_are_searchable_and_edit_form_does_not_expose_raw_data(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        DB::table('pages')->insert([
            ['title'=>'Tentang Kami','slug'=>'tentang-kami','body'=>'<p>Profil ruang redaksi.</p>','active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['title'=>'Kebijakan Privasi','slug'=>'privasi','body'=>'<p>Kebijakan.</p>','active'=>false,'created_at'=>now(),'updated_at'=>now()],
        ]);
        $this->actingAs($admin)->withSession(['session_version'=>$admin->session_version]);
        $this->get('/admin/manage/pages')->assertOk()->assertSee('Tentang Kami')->assertSee('Kebijakan Privasi')
            ->assertSee('data-page-editor',false)->assertSee('management-dialog')->assertDontSee('ID untuk mengubah');
        $this->get('/admin/manage/pages?q=Tentang')->assertOk()->assertSee('Tentang Kami')->assertDontSee('Kebijakan Privasi');
        $this->get('/admin/manage/pages?state=inactive')->assertOk()->assertDontSee('<strong>Tentang Kami</strong>', false)->assertSee('<strong>Kebijakan Privasi</strong>', false)->assertViewHas('rows', fn ($rows) => $rows->count() === 1 && $rows->first()->title === 'Kebijakan Privasi');
    }

    public function test_page_create_and_update_preserve_formatting_and_sanitize_scripts(): void
    {
        $admin=User::factory()->create(['role'=>'admin']);
        $this->actingAs($admin)->withSession(['session_version'=>$admin->session_version]);
        $this->post('/admin/manage/pages',['title'=>'Tentang','slug'=>'tentang','body'=>'<h2>Redaksi</h2><p>Profil <strong>kami</strong>.</p><script>alert(1)</script>','active'=>1])->assertRedirect();
        $page=DB::table('pages')->where('slug','tentang')->first();
        $this->assertStringContainsString('<h2>Redaksi</h2>',$page->body);
        $this->assertStringNotContainsString('<script',$page->body);
        $this->post('/admin/manage/pages',['id'=>$page->id,'title'=>'Profil Redaksi','slug'=>'profil','body'=>'<p>Profil diperbarui.</p>','active'=>0])->assertRedirect();
        $this->assertDatabaseHas('pages',['id'=>$page->id,'title'=>'Profil Redaksi','active'=>0]);
    }

    public function test_user_list_does_not_serialize_passwords(): void
    {
        $admin=User::factory()->create(['role'=>'admin']);
        $this->actingAs($admin)->withSession(['session_version'=>$admin->session_version])->get('/admin/manage/users')
            ->assertOk()->assertSee($admin->email)->assertDontSee($admin->password)->assertDontSee('remember_token');
    }
}
