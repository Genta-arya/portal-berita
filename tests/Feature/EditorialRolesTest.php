<?php
namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EditorialRolesTest extends TestCase
{
    use RefreshDatabase;

    private function loginAs(User $user): void { $this->actingAs($user)->withSession(['session_version'=>$user->session_version]); }
    private function payload(string $role='penulis'): array
    {
        return ['name'=>'Akun Baru','email'=>'baru@example.test','role'=>$role,'active'=>1,'password'=>'kapuasraya','password_confirmation'=>'kapuasraya'];
    }
    private function article(User $user,string $title): Article
    {
        $article=new Article(['title'=>$title,'slug'=>strtolower($title)]);$article->author_id=$user->id;$article->save();return $article;
    }

    public function test_writer_only_lists_creates_and_edits_own_drafts_and_cannot_publish_or_manage_accounts(): void
    {
        $writer=User::factory()->create();$other=User::factory()->create();
        $mine=$this->article($writer,'MilikSaya');$theirs=$this->article($other,'MilikOrangLain');$this->loginAs($writer);
        $this->get('/admin/articles?author_id='.$other->id)->assertOk()->assertViewHas('articles',fn($items)=>$items->isEmpty());
        $this->get('/admin/articles')->assertOk()->assertViewHas('articles',fn($items)=>$items->pluck('id')->all()===[$mine->id]);
        $this->post('/admin/articles',['title'=>'DraftBaru','author_id'=>$other->id])->assertRedirect();
        $this->assertDatabaseHas('articles',['title'=>'DraftBaru','author_id'=>$writer->id]);
        $this->putJson('/admin/articles/'.$mine->id,['version'=>1,'title'=>'JudulDiubah','slug'=>'judul-diubah','body'=>'<p>Naskah sendiri.</p>'])->assertOk();
        $this->get('/admin/articles/'.$theirs->id.'/edit')->assertForbidden();
        $this->get('/admin/articles/'.$theirs->id.'/preview')->assertForbidden();
        $this->putJson('/admin/articles/'.$theirs->id,['version'=>1,'title'=>'Serangan','slug'=>'serangan'])->assertForbidden();
        $this->post('/admin/articles/'.$theirs->id.'/restore/1',['version'=>1])->assertForbidden();
        $this->post('/admin/articles/'.$mine->id.'/assign',['author_id'=>$other->id,'version'=>2])->assertForbidden();
        $this->post('/admin/articles/'.$mine->id.'/transition',['action'=>'approve','version'=>2])->assertForbidden();
        $this->delete('/admin/articles/'.$mine->id,['version'=>2])->assertForbidden();
        $this->post('/admin/manage/users',$this->payload('admin'))->assertForbidden();
        $this->get('/admin/manage/users')->assertForbidden();
    }

    public function test_admin_cannot_create_promote_or_reset_privileged_accounts(): void
    {
        $developer=User::factory()->create(['role'=>'developer']);$admin=User::factory()->create(['role'=>'admin']);$writer=User::factory()->create();$this->loginAs($admin);
        foreach(['admin','developer'] as $role)$this->post('/admin/manage/users',$this->payload($role))->assertForbidden();
        $this->post('/admin/manage/users',[...$this->payload('admin'),'id'=>$writer->id,'email'=>$writer->email])->assertForbidden();
        $this->post('/admin/manage/users',[...$this->payload('penulis'),'id'=>$developer->id,'email'=>$developer->email])->assertForbidden();
        $this->post('/admin/manage/users',[...$this->payload('penulis'),'id'=>$admin->id,'email'=>$admin->email])->assertForbidden();
        $this->assertSame('developer',$developer->fresh()->role);
        $this->assertSame('penulis',$writer->fresh()->role);
        $this->post('/admin/manage/users',$this->payload())->assertRedirect();
        $created=User::where('email','baru@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('kapuasraya',$created->password));
        $this->assertNotSame('kapuasraya',$created->password);
        $this->get('/admin/manage/users')->assertOk()->assertDontSee('<option value="admin"',false)->assertDontSee('<option value="developer"',false);
    }

    public function test_developer_can_create_admin_and_access_editorial_tools(): void
    {
        $dev=User::factory()->create(['role'=>'developer']);$writer=User::factory()->create();$article=$this->article($writer,'Naskah');$this->loginAs($dev);
        $this->post('/admin/manage/users',$this->payload('admin'))->assertRedirect();
        $this->assertDatabaseHas('users',['email'=>'baru@example.test','role'=>'admin']);
        foreach(['/admin','/admin/manage/users','/admin/manage/settings','/admin/manage/trending','/admin/articles/'.$article->id.'/edit'] as $url)$this->get($url)->assertOk();
        $this->putJson('/admin/articles/'.$article->id,['version'=>1,'title'=>'EditDeveloper','slug'=>'edit-developer'])->assertOk();
        $this->post('/admin/articles/'.$article->id.'/assign',['author_id'=>$dev->id,'version'=>2])->assertRedirect();
        $this->assertDatabaseHas('articles',['id'=>$article->id,'author_id'=>$dev->id]);
    }

    public function test_password_and_confirmation_are_required_for_new_users_and_optional_when_unchanged(): void
    {
        $dev=User::factory()->create(['role'=>'developer']);$writer=User::factory()->create();$hash=$writer->password;$this->loginAs($dev);
        $this->post('/admin/manage/users',[...$this->payload(),'password'=>'','password_confirmation'=>''])->assertSessionHasErrors('password');
        $this->post('/admin/manage/users',[...$this->payload(),'password_confirmation'=>'different'])->assertSessionHasErrors('password');
        $this->post('/admin/manage/users',['id'=>$writer->id,'name'=>$writer->name,'email'=>$writer->email,'role'=>'penulis','active'=>1,'password'=>'','password_confirmation'=>''])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($hash,$writer->fresh()->password);
        $version=$writer->session_version;
        $this->post('/admin/manage/users',[...$this->payload(),'id'=>$writer->id,'email'=>$writer->email])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('kapuasraya',$writer->fresh()->password));
        $this->assertSame($version+1,$writer->fresh()->session_version);
    }

    public function test_last_developer_cannot_be_demoted_or_disabled_and_last_admin_is_protected(): void
    {
        $dev=User::factory()->create(['role'=>'developer']);$admin=User::factory()->create(['role'=>'admin']);$this->loginAs($dev);
        $this->post('/admin/manage/users',[...$this->payload('penulis'),'id'=>$dev->id,'email'=>$dev->email])->assertForbidden();
        $this->post('/admin/manage/users',[...$this->payload('developer'),'id'=>$dev->id,'email'=>$dev->email,'active'=>0])->assertForbidden();
        $this->post('/admin/manage/users',[...$this->payload('penulis'),'id'=>$admin->id,'email'=>$admin->email])->assertStatus(422);
    }

    public function test_cli_admin_creation_requires_developer_actor(): void
    {
        $this->artisan('editorial:create-admin',['email'=>'cli@example.test','name'=>'CLI'])->assertExitCode(1);
        $admin=User::factory()->create(['role'=>'admin']);
        $this->artisan('editorial:create-admin',['email'=>'cli@example.test','name'=>'CLI','--developer'=>$admin->id])->assertExitCode(1);
        $this->assertDatabaseMissing('users',['email'=>'cli@example.test']);
    }
}
