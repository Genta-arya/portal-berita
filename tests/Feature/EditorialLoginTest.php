<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['password' => 'password', 'active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_changed_session_version_revokes_access(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $user->increment('session_version');
        $this->get('/admin')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_valid_session_skips_login_form_for_every_editorial_role(): void
    {
        foreach (['developer','admin','editor','penulis'] as $role) {
            $user = User::factory()->create(['role'=>$role]);
            $this->actingAs($user)->withSession(['session_version'=>$user->session_version])
                ->get('/login')->assertRedirect('/admin')->assertHeader('Cache-Control','no-store, private');
        }
    }

    public function test_stale_or_inactive_session_returns_login_form_without_redirect_loop(): void
    {
        foreach (['inactive','revoked','invalid_role'] as $case) {
            $user = User::factory()->create();
            $this->actingAs($user)->withSession(['session_version'=>$user->session_version]);
            if ($case === 'inactive') { $user->active=false; $user->save(); }
            elseif ($case === 'revoked') $user->increment('session_version');
            else { $user->role = 'invalid'; $user->save(); }
            $this->get('/login')->assertOk()->assertSee('Masuk ke redaksi');
            $this->assertGuest();
        }
    }

    public function test_guest_still_sees_login_form(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke redaksi');
    }
}
