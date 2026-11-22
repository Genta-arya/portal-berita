<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;
    private function loginAs(User $user): void { $this->actingAs($user)->withSession(['session_version'=>$user->session_version]); }

    public function test_writer_can_upload_replace_and_remove_own_photo_without_changing_another_account(): void
    {
        Storage::fake('local');
        $writer=User::factory()->create();$other=User::factory()->create();$this->loginAs($writer);
        $this->get('/admin/profile')->assertOk()->assertSee('Simpan foto profil');
        $this->post('/admin/profile',['avatar'=>UploadedFile::fake()->image('foto.png',600,400),'id'=>$other->id,'role'=>'developer'])->assertRedirect()->assertSessionHasNoErrors();
        $first=$writer->fresh()->avatar_path;
        Storage::disk('local')->assertExists($first);
        $this->assertNull($other->fresh()->avatar_path);
        $this->assertSame('penulis',$writer->fresh()->role);
        $image=getimagesize(Storage::disk('local')->path($first));
        $this->assertSame([256,256],[$image[0],$image[1]]);
        $this->assertSame('image/jpeg',$image['mime']);
        $this->get('/admin/users/'.$writer->id.'/avatar')->assertOk()->assertHeader('Content-Type','image/jpeg')->assertHeader('X-Content-Type-Options','nosniff');
        $this->post('/admin/profile',['avatar'=>UploadedFile::fake()->image('baru.jpg')])->assertRedirect();
        $second=$writer->fresh()->avatar_path;$this->assertNotSame($first,$second);
        Storage::disk('local')->assertMissing($first);Storage::disk('local')->assertExists($second);
        $this->post('/admin/profile',['remove_avatar'=>1])->assertRedirect();
        $this->assertNull($writer->fresh()->avatar_path);Storage::disk('local')->assertMissing($second);
    }

    public function test_invalid_or_oversize_avatar_is_rejected(): void
    {
        Storage::fake('local');$writer=User::factory()->create();$this->loginAs($writer);
        $this->post('/admin/profile',['avatar'=>UploadedFile::fake()->createWithContent('evil.svg','<svg onload="alert(1)"></svg>')])->assertSessionHasErrors('avatar');
        $this->post('/admin/profile',['avatar'=>UploadedFile::fake()->image('large.jpg')->size(2049)])->assertSessionHasErrors('avatar');
        $this->assertNull($writer->fresh()->avatar_path);
        $this->assertSame([],Storage::disk('local')->allFiles());
    }

    public function test_account_photo_management_obeys_privileged_account_rules(): void
    {
        Storage::fake('local');$admin=User::factory()->create(['role'=>'admin']);$dev=User::factory()->create(['role'=>'developer']);$writer=User::factory()->create();$this->loginAs($admin);
        $this->post('/admin/manage/users',['id'=>$writer->id,'name'=>$writer->name,'email'=>$writer->email,'role'=>'penulis','active'=>1,'avatar'=>UploadedFile::fake()->image('writer.jpg')])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNotNull($writer->fresh()->avatar_path);
        $this->post('/admin/manage/users',['id'=>$dev->id,'name'=>$dev->name,'email'=>$dev->email,'role'=>'developer','active'=>1,'avatar'=>UploadedFile::fake()->image('dev.jpg')])->assertForbidden();
        $this->assertNull($dev->fresh()->avatar_path);
        $this->loginAs($dev);
        $this->post('/admin/manage/users',['id'=>$admin->id,'name'=>$admin->name,'email'=>$admin->email,'role'=>'admin','active'=>1,'avatar'=>UploadedFile::fake()->image('admin.jpg')])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNotNull($admin->fresh()->avatar_path);
    }

    public function test_avatar_requires_login(): void
    {
        $user=User::factory()->create();
        $this->get('/admin/profile')->assertRedirect('/login');
        $this->get('/admin/users/'.$user->id.'/avatar')->assertRedirect('/login');
    }
}
