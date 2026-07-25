<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_without_dashboard_view_permission_is_forbidden(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_user_with_dashboard_view_permission_can_access(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo('dashboard.view');

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_user_without_users_view_permission_cannot_access_user_list(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_user_without_users_create_permission_cannot_store_user(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo('users.view');

        $response = $this->actingAs($user)->post('/admin/users', [
            'name' => 'New Guy',
            'email' => 'newguy@example.com',
            'password' => 'Password123!',
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }
}
