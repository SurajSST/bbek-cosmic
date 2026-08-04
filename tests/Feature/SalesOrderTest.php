<?php

namespace Tests\Feature;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_sales_orders_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/sales-orders');

        $response->assertStatus(200);
        $response->assertSee('Sales Orders');
    }

    public function test_unauthorized_user_cannot_view_sales_orders(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/admin/sales-orders');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_create_sales_order_with_items_and_images(): void
    {
        Storage::fake('public');

        $billFile = UploadedFile::fake()->image('bill.jpg');
        $slipFile = UploadedFile::fake()->image('slip.png');

        $response = $this->actingAs($this->admin)->post('/admin/sales-orders', [
            'so_number' => 'SO-TEST-100',
            'so_from' => 'Cloud',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'billed_status' => 'billed',
            'bill_no' => 'BILL-999',
            'bill_image' => $billFile,
            'slip_image' => $slipFile,
            'remarks' => 'Test order remarks',
            'items' => [
                [
                    'product_name' => 'Server Maintenance',
                    'quantity' => 2,
                    'unit_price' => 500,
                    'remarks' => 'Monthly contract',
                ],
                [
                    'product_name' => 'SSD Upgrade',
                    'quantity' => 1,
                    'unit_price' => 200,
                    'remarks' => 'Hardware',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.sales-orders.index'));

        $this->assertDatabaseHas('sales_orders', [
            'so_number' => 'SO-TEST-100',
            'so_from' => 'Cloud',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'bill_no' => 'BILL-999',
        ]);

        $this->assertDatabaseHas('sales_order_items', [
            'product_name' => 'Server Maintenance',
            'quantity' => 2,
            'total_price' => 1000.00,
        ]);
    }

    public function test_bulk_upload_requires_only_so_number_so_from_and_product(): void
    {
        $csvContent = "so_number,so_from,product_name\nSO-BULK-99,Dragon,Graphics Card RX 7900";
        $csvFile = UploadedFile::fake()->createWithContent('bulk_orders.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/sales-orders/bulk-upload', [
            'file' => $csvFile,
        ]);

        $response->assertRedirect(route('admin.sales-orders.index'));

        $this->assertDatabaseHas('sales_orders', [
            'so_number' => 'SO-BULK-99',
            'so_from' => 'Dragon',
        ]);

        $this->assertDatabaseHas('sales_order_items', [
            'product_name' => 'Graphics Card RX 7900',
            'quantity' => 1,
        ]);
    }

    public function test_search_by_so_number_and_bill_no(): void
    {
        $order1 = SalesOrder::factory()->create([
            'so_number' => 'SO-UNIQUE-111',
            'so_from' => 'Dragon',
            'bill_no' => 'INV-111',
            'billed_from' => 'Dragon',
            'billed_to' => 'EGA',
        ]);

        $order2 = SalesOrder::factory()->create([
            'so_number' => 'SO-UNIQUE-222',
            'so_from' => 'Cosmic',
            'bill_no' => 'INV-222',
            'billed_from' => 'Cosmic',
            'billed_to' => 'Prativa School',
        ]);

        // Search by SO Number
        $response1 = $this->actingAs($this->admin)->get('/admin/sales-orders?search=SO-UNIQUE-111');
        $response1->assertSee('SO-UNIQUE-111');
        $response1->assertDontSee('SO-UNIQUE-222');

        // Search by Bill No
        $response2 = $this->actingAs($this->admin)->get('/admin/sales-orders?search=INV-222');
        $response2->assertSee('SO-UNIQUE-222');
        $response2->assertDontSee('SO-UNIQUE-111');
    }

    public function test_item_level_return_workflow(): void
    {
        $order = SalesOrder::create([
            'so_number' => 'SO-RETURN-101',
            'so_from' => 'Cloud',
            'billed_from' => 'Cloud',
            'billed_to' => 'Prativa Plus Two',
            'billed_status' => 'paid',
        ]);

        $item = $order->items()->create([
            'product_name' => 'Laptops',
            'quantity' => 5,
            'unit_price' => 1000,
            'total_price' => 5000,
            'return_status' => 'not_returned',
        ]);

        // Process partial return of 2 laptops
        $response = $this->actingAs($this->admin)->post("/admin/sales-orders/items/{$item->id}/return", [
            'returned_quantity' => 2,
            'remarks' => 'Defective screens',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sales_order_items', [
            'id' => $item->id,
            'returned_quantity' => 2,
            'return_status' => 'partially_returned',
        ]);
    }

    public function test_authorized_user_can_delete_sales_order(): void
    {
        $order = SalesOrder::create([
            'so_number' => 'SO-DEL-001',
            'so_from' => 'Dragon',
            'billed_from' => 'Dragon',
            'billed_to' => 'PBS',
            'billed_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/sales-orders/{$order->id}");

        $response->assertRedirect(route('admin.sales-orders.index'));
        $this->assertDatabaseMissing('sales_orders', ['id' => $order->id]);
    }

    public function test_authorized_user_can_create_sales_order_without_billed_from(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/sales-orders', [
            'so_number' => 'SO-NO-BILLED-FROM',
            'so_from' => 'Cloud',
            'billed_to' => 'PBS',
            'billed_status' => 'pending',
            'items' => [
                [
                    'product_name' => 'Service Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.sales-orders.index'));

        $this->assertDatabaseHas('sales_orders', [
            'so_number' => 'SO-NO-BILLED-FROM',
            'billed_from' => null,
        ]);
    }

    public function test_authorized_user_can_update_sales_order_without_billed_from(): void
    {
        $order = SalesOrder::create([
            'so_number' => 'SO-EDIT-OPTIONAL',
            'so_from' => 'Cloud',
            'billed_from' => 'Cloud',
            'billed_to' => 'PBS',
            'billed_status' => 'pending',
        ]);

        $item = $order->items()->create([
            'product_name' => 'Initial Item',
            'quantity' => 1,
            'unit_price' => 50,
            'total_price' => 50,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/sales-orders/{$order->id}", [
            'so_number' => 'SO-EDIT-OPTIONAL',
            'so_from' => 'Cloud',
            'billed_from' => '',
            'billed_to' => 'PBS',
            'billed_status' => 'billed',
            'items' => [
                [
                    'id' => $item->id,
                    'product_name' => 'Initial Item',
                    'quantity' => 1,
                    'unit_price' => 50,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.sales-orders.index'));

        $this->assertDatabaseHas('sales_orders', [
            'id' => $order->id,
            'billed_from' => null,
        ]);
    }
}
