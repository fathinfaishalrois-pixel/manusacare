<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DualGuardSessionTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $identity): User
    {
        $roleModel = Role::firstOrCreate(['slug' => $role], ['name' => ucfirst($role)]);
        $user = User::create([
            'name' => ucfirst($role).' Test',
            'identity_number' => $identity,
            'identity_type' => $role === 'admin' ? 'lainnya' : 'nis',
            'email' => $identity.'@example.test',
            'password' => 'PasswordAman123',
            'is_active' => true,
        ]);
        $user->roles()->attach($roleModel->id);
        return $user;
    }

    public function test_legacy_admin_web_session_is_migrated_to_admin_guard(): void
    {
        $admin = $this->user('admin', 'admin-legacy');

        $this->actingAs($admin, 'web')->get('/admin/login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
    }

    public function test_admin_and_reporter_can_remain_logged_in_in_separate_tabs(): void
    {
        $admin = $this->user('admin', 'admin-001');
        $reporter = $this->user('reporter', '12345678');

        $this->post('/admin/login', ['login' => 'admin-001', 'password' => 'PasswordAman123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');

        $this->post('/login', ['login' => '12345678', 'password' => 'PasswordAman123'])
            ->assertRedirect(route('reporter.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertAuthenticatedAs($reporter, 'web');

        $this->get('/admin/dashboard')->assertOk();
        $this->get('/dashboard')->assertOk();

        $this->post('/admin/logout')->assertRedirect(route('home'));
        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($reporter, 'web');
        $this->get('/dashboard')->assertOk();
    }
}
