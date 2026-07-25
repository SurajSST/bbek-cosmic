<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_user_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee($this->admin->name);
    }

    public function test_authorized_user_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'Jane Accountant',
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'status' => 'active',
            'roles' => ['Super Admin'],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_authorized_user_can_edit_user(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)->put("/admin/users/{$targetUser->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'status' => 'inactive',
            'roles' => [],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'status' => 'inactive',
        ]);
    }

    public function test_user_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/users/{$this->admin->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_cannot_delete_sole_super_admin(): void
    {
        $anotherAdmin = User::factory()->create(['status' => 'active']);
        $anotherAdmin->givePermissionTo('users.delete');

        $response = $this->actingAs($anotherAdmin)->delete("/admin/users/{$this->admin->id}");

        $response->assertSessionHas('error', 'Cannot delete the system\'s only Super Admin account.');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
