<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_bills_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/bills');

        $response->assertStatus(200);
        $response->assertSee('Upload Bill');
    }

    public function test_unauthorized_user_cannot_view_bills(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/admin/bills');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_upload_bill_with_images(): void
    {
        Storage::fake('public');

        $billFile = UploadedFile::fake()->image('bill_receipt.jpg');
        $slipFile = UploadedFile::fake()->image('bank_slip.png');

        $response = $this->actingAs($this->admin)->post('/admin/bills', [
            'bill_number' => 'BILL-9900',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'status' => 'billed',
            'amount' => 2500.50,
            'bill_image' => $billFile,
            'slip_image' => $slipFile,
            'remarks' => 'Monthly hosting receipt',
        ]);

        $response->assertRedirect(route('admin.bills.index'));

        $this->assertDatabaseHas('bills', [
            'bill_number' => 'BILL-9900',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'status' => 'billed',
            'amount' => 2500.50,
        ]);
    }

    public function test_search_bills_by_bill_number_and_party(): void
    {
        Bill::create([
            'bill_number' => 'BILL-SEARCH-1',
            'billed_from' => 'Dragon',
            'billed_to' => 'EGA',
            'status' => 'billed',
        ]);

        Bill::create([
            'bill_number' => 'BILL-SEARCH-2',
            'billed_from' => 'Cosmic',
            'billed_to' => 'Prativa School',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/bills?search=BILL-SEARCH-1');
        $response->assertSee('BILL-SEARCH-1');
        $response->assertDontSee('BILL-SEARCH-2');
    }

    public function test_authorized_user_can_update_bill(): void
    {
        $bill = Bill::create([
            'bill_number' => 'BILL-EDIT-1',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/bills/{$bill->id}", [
            'bill_number' => 'BILL-EDIT-1-UPDATED',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'status' => 'paid',
            'amount' => 150.00,
        ]);

        $response->assertRedirect(route('admin.bills.index'));

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'bill_number' => 'BILL-EDIT-1-UPDATED',
            'status' => 'paid',
            'amount' => 150.00,
        ]);
    }

    public function test_authorized_user_can_delete_bill(): void
    {
        $bill = Bill::create([
            'bill_number' => 'BILL-DEL-1',
            'billed_from' => 'Dragon',
            'billed_to' => 'EGA',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/bills/{$bill->id}");

        $response->assertRedirect(route('admin.bills.index'));
        $this->assertDatabaseMissing('bills', ['id' => $bill->id]);
    }
}
