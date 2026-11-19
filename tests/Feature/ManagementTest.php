<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ContentSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_admin_and_writer_management_are_protected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->withSession(['session_version' => 1]);
        $this->post('/admin/manage/users', ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email, 'role' => 'penulis', 'active' => 1])->assertStatus(422);
        $this->assertSame('admin', $admin->fresh()->role);
        foreach (['users', 'categories', 'tags', 'settings', 'banners', 'breaking', 'featured', 'pages', 'audit'] as $module) {
            $this->get('/admin/manage/'.$module)->assertOk();
        }
        $writer = User::factory()->create();
        $this->actingAs($writer)->withSession(['session_version' => 1]);
        $this->get('/admin/manage/users')->assertForbidden();
        $this->post('/admin/manage/categories', ['name' => 'X'])->assertForbidden();
    }

    public function test_script_and_arbitrary_iframe_are_sanitized(): void
    {
        $html = app(ContentSanitizer::class)->clean('<p onclick="alert(1)">Berita</p><a href="javascript:alert(1)">Klik</a><iframe src="https://evil.test"></iframe><img src="https://evil.test/a.jpg"><script>alert(1)</script>');
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('evil.test', $html);
        $this->assertStringNotContainsString('<script', $html);
    }
}
