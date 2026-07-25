<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_roles(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/roles');

        $response->assertStatus(200);
        $response->assertSee('Super Admin');
    }

    public function test_authorized_user_can_create_role(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/roles', [
            'name' => 'Auditor',
            'permissions' => ['dashboard.view', 'users.view'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'Auditor']);

        $role = Role::findByName('Auditor');
        $this->assertTrue($role->hasPermissionTo('users.view'));
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $superAdminRole = Role::findByName('Super Admin');

        $response = $this->actingAs($this->admin)->delete("/admin/roles/{$superAdminRole->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
    }
}
