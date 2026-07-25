<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of sales orders with search, sorting, and pagination.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort', 'created_at');
        $sortDir = $request->query('dir', 'desc');

        $salesOrders = SalesOrder::with(['items', 'creator'])
            ->search($search)
            ->when($status && $status !== 'all', function ($q) use ($status) {
                return $q->where('billed_status', $status);
            })
            ->sort($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return view('admin.sales_orders.index', compact(
            'salesOrders',
            'search',
            'status',
            'sortBy',
            'sortDir'
        ));
    }

    /**
     * Show the form for creating a new sales order.
     */
    public function create(): View
    {
        return view('admin.sales_orders.create');
    }

    /**
     * Store a newly created sales order and items in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:100', 'unique:sales_orders,so_number'],
            'billed_via' => ['required', 'string', 'max:100'],
            'billed_to' => ['required', 'string', 'max:255'],
            'billed_status' => ['required', 'string', Rule::in(['pending', 'billed', 'paid', 'cancelled'])],
            'bill_no' => ['nullable', 'string', 'max:100'],
            'bill_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'slip_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'remarks' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            
            // Items Validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $billImagePath = null;
            $slipImagePath = null;

            if ($request->hasFile('bill_image')) {
                $billImagePath = $request->file('bill_image')->store('sales_orders/bills', 'public');
            }

            if ($request->hasFile('slip_image')) {
                $slipImagePath = $request->file('slip_image')->store('sales_orders/slips', 'public');
            }

            $order = SalesOrder::create([
                'so_number' => $validated['so_number'],
                'billed_via' => $validated['billed_via'],
                'billed_to' => $validated['billed_to'],
                'billed_status' => $validated['billed_status'],
                'bill_no' => $validated['bill_no'] ?? null,
                'bill_image' => $billImagePath,
                'slip_image' => $slipImagePath,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['unit_price'];

                $order->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $qty * $price,
                    'return_status' => 'not_returned',
                    'returned_quantity' => 0,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.sales-orders.index')
            ->with('success', "Sales Order '{$validated['so_number']}' created successfully.");
    }

    /**
     * Display the specified sales order details.
     */
    public function show(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['items', 'creator']);

        return view('admin.sales_orders.show', compact('salesOrder'));
    }

    /**
     * Show the form for editing the specified sales order.
     */
    public function edit(SalesOrder $salesOrder): View
    {
        $salesOrder->load('items');

        return view('admin.sales_orders.edit', compact('salesOrder'));
    }

    /**
     * Update the specified sales order and items in storage.
     */
    public function update(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:100', Rule::unique('sales_orders')->ignore($salesOrder->id)],
            'billed_via' => ['required', 'string', 'max:100'],
            'billed_to' => ['required', 'string', 'max:255'],
            'billed_status' => ['required', 'string', Rule::in(['pending', 'billed', 'paid', 'cancelled'])],
            'bill_no' => ['nullable', 'string', 'max:100'],
            'bill_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'slip_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'remarks' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],

            // Items Validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $salesOrder, $validated) {
            // File Replacement Handling
            if ($request->hasFile('bill_image')) {
                if ($salesOrder->bill_image) {
                    Storage::disk('public')->delete($salesOrder->bill_image);
                }
                $salesOrder->bill_image = $request->file('bill_image')->store('sales_orders/bills', 'public');
            }

            if ($request->hasFile('slip_image')) {
                if ($salesOrder->slip_image) {
                    Storage::disk('public')->delete($salesOrder->slip_image);
                }
                $salesOrder->slip_image = $request->file('slip_image')->store('sales_orders/slips', 'public');
            }

            $salesOrder->update([
                'so_number' => $validated['so_number'],
                'billed_via' => $validated['billed_via'],
                'billed_to' => $validated['billed_to'],
                'billed_status' => $validated['billed_status'],
                'bill_no' => $validated['bill_no'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            // Sync items: track existing item IDs
            $keptItemIds = [];

            foreach ($validated['items'] as $itemData) {
                $qty = (int) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];

                if (!empty($itemData['id'])) {
                    // Update existing item
                    $item = SalesOrderItem::where('sales_order_id', $salesOrder->id)
                        ->findOrFail($itemData['id']);

                    $item->update([
                        'product_name' => $itemData['product_name'],
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $qty * $price,
                        'remarks' => $itemData['remarks'] ?? null,
                    ]);

                    $keptItemIds[] = $item->id;
                } else {
                    // Create new item
                    $newItem = $salesOrder->items()->create([
                        'product_name' => $itemData['product_name'],
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $qty * $price,
                        'return_status' => 'not_returned',
                        'returned_quantity' => 0,
                        'remarks' => $itemData['remarks'] ?? null,
                    ]);

                    $keptItemIds[] = $newItem->id;
                }
            }

            // Delete removed items
            $salesOrder->items()->whereNotIn('id', $keptItemIds)->delete();
        });

        return redirect()->route('admin.sales-orders.index')
            ->with('success', "Sales Order '{$salesOrder->so_number}' updated successfully.");
    }

    /**
     * Remove the specified sales order from storage.
     */
    public function destroy(SalesOrder $salesOrder): RedirectResponse
    {
        $soNumber = $salesOrder->so_number;

        // Clean up file uploads
        if ($salesOrder->bill_image) {
            Storage::disk('public')->delete($salesOrder->bill_image);
        }
        if ($salesOrder->slip_image) {
            Storage::disk('public')->delete($salesOrder->slip_image);
        }

        $salesOrder->delete();

        return redirect()->route('admin.sales-orders.index')
            ->with('success', "Sales Order '{$soNumber}' was deleted successfully.");
    }

    /**
     * Process item-level return workflow.
     */
    public function processReturn(Request $request, SalesOrderItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'returned_quantity' => ['required', 'integer', 'min:1', "max:{$item->quantity}"],
            'remarks' => ['nullable', 'string'],
        ]);

        $newReturnedQty = $validated['returned_quantity'];
        
        $status = 'partially_returned';
        if ($newReturnedQty >= $item->quantity) {
            $status = 'returned';
            $newReturnedQty = $item->quantity;
        }

        $item->update([
            'returned_quantity' => $newReturnedQty,
            'return_status' => $status,
            'returned_at' => now(),
            'remarks' => $validated['remarks'] ?? $item->remarks,
        ]);

        return back()->with('success', "Return status updated for item '{$item->product_name}'.");
    }
}
