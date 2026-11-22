<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class HiddenDeveloperTest extends TestCase
{
    use RefreshDatabase;
    public function test_developer_is_excluded_from_rows_search_counts_and_role_choices(): void
    {
        $dev=User::factory()->create(['role'=>'developer','email'=>'hidden@example.test']);
        $admin=User::factory()->create(['role'=>'admin']);$writer=User::factory()->create();
        foreach([$dev,$admin] as $actor){
            $this->actingAs($actor)->withSession(['session_version'=>$actor->session_version]);
            $this->get('/admin/manage/users')->assertOk()->assertViewHas('total',2)->assertViewHas('activeCount',2)
                ->assertViewHas('rows',fn($rows)=>!$rows->contains('id',$dev->id))
                ->assertDontSee('hidden@example.test')->assertDontSee('<option value="developer"',false);
            $this->get('/admin/manage/users?q=hidden')->assertOk()->assertViewHas('rows',fn($rows)=>$rows->isEmpty());
        }
    }
    public function test_even_developer_cannot_assign_developer_role_or_edit_hidden_accounts_via_user_endpoint(): void
    {
        $dev=User::factory()->create(['role'=>'developer']);$this->actingAs($dev)->withSession(['session_version'=>$dev->session_version]);
        $data=['name'=>'Baru','email'=>'baru@example.test','role'=>'developer','active'=>1,'password'=>'kapuasraya','password_confirmation'=>'kapuasraya'];
        $this->post('/admin/manage/users',$data)->assertForbidden();
        $this->post('/admin/manage/users',[...$data,'id'=>$dev->id,'email'=>$dev->email,'role'=>'penulis'])->assertForbidden();
        $this->assertSame('developer',$dev->fresh()->role);
        $this->assertDatabaseMissing('users',['email'=>'baru@example.test']);
    }
}
