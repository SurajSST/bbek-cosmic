<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Cosmic Bill');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        $user->givePermissionTo('dashboard.view');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_sales_only_permission_user_redirects_to_sales_orders_index_after_login(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        $user->givePermissionTo('sales-orders.view');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.sales-orders.index'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_login_screen_does_not_contain_default_credentials(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('value="admin@example.com"', false);
        $response->assertDontSee('value="password"', false);
    }

    public function test_intended_unauthorized_url_is_cleared_on_login_for_non_super_admin_user(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        $user->givePermissionTo('sales-orders.view');

        // Simulate user attempting to access forbidden route before login
        $this->get(route('admin.users.index'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        // Should redirect to sales-orders index (their home route) rather than users index (which is forbidden)
        $response->assertRedirect(route('admin.sales-orders.index'));
    }

    public function test_user_with_bills_view_permission_redirects_to_bills_index_after_login(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        $user->givePermissionTo('bills.view');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.bills.index'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
