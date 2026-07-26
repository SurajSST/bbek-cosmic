<?php

namespace Tests\Feature;

use App\Models\UploadSo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadSoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_upload_sos_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/upload-sos');

        $response->assertStatus(200);
        $response->assertSee('Upload SO');
    }

    public function test_unauthorized_user_cannot_view_upload_sos(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/admin/upload-sos');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_upload_so_with_images(): void
    {
        Storage::fake('public');

        $soFile = UploadedFile::fake()->image('so_doc.jpg');
        $slipFile = UploadedFile::fake()->image('payment_slip.png');

        $response = $this->actingAs($this->admin)->post('/admin/upload-sos', [
            'so_number' => 'SO-UP-8800',
            'billed_from' => 'Dragon',
            'billed_to' => 'PBS',
            'status' => 'billed',
            'amount' => 4500.00,
            'so_image' => $soFile,
            'slip_image' => $slipFile,
            'remarks' => 'Quick SO photo scan',
        ]);

        $response->assertRedirect(route('admin.upload-sos.index'));

        $this->assertDatabaseHas('upload_sos', [
            'so_number' => 'SO-UP-8800',
            'billed_from' => 'Dragon',
            'billed_to' => 'PBS',
            'status' => 'billed',
            'amount' => 4500.00,
        ]);
    }

    public function test_search_upload_sos_by_so_number_and_party(): void
    {
        UploadSo::create([
            'so_number' => 'SO-SEARCH-1',
            'billed_from' => 'Cloud',
            'billed_to' => 'EGA',
            'status' => 'billed',
        ]);

        UploadSo::create([
            'so_number' => 'SO-SEARCH-2',
            'billed_from' => 'Cosmic',
            'billed_to' => 'Prativa School',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/upload-sos?search=SO-SEARCH-1');
        $response->assertSee('SO-SEARCH-1');
        $response->assertDontSee('SO-SEARCH-2');
    }

    public function test_authorized_user_can_update_upload_so(): void
    {
        $so = UploadSo::create([
            'so_number' => 'SO-EDIT-1',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'status' => 'pending',
            'amount' => 200.00,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/upload-sos/{$so->id}", [
            'so_number' => 'SO-EDIT-1-UPDATED',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'status' => 'paid',
            'amount' => 350.00,
        ]);

        $response->assertRedirect(route('admin.upload-sos.index'));

        $this->assertDatabaseHas('upload_sos', [
            'id' => $so->id,
            'so_number' => 'SO-EDIT-1-UPDATED',
            'status' => 'paid',
            'amount' => 350.00,
        ]);
    }

    public function test_authorized_user_can_delete_upload_so(): void
    {
        $so = UploadSo::create([
            'so_number' => 'SO-DEL-1',
            'billed_from' => 'Dragon',
            'billed_to' => 'EGA',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/upload-sos/{$so->id}");

        $response->assertRedirect(route('admin.upload-sos.index'));
        $this->assertDatabaseMissing('upload_sos', ['id' => $so->id]);
    }
}
