<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_activity_logs(): void
    {
        ActivityLog::record('test_action', 'Test description for activity log');

        $response = $this->actingAs($this->admin)->get('/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertSee('Test description for activity log');
    }

    public function test_unauthorized_user_cannot_view_activity_logs(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/admin/activity-logs');

        $response->assertStatus(403);
    }
}
