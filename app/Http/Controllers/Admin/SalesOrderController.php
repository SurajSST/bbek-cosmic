<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
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
            'so_from' => ['required', 'string', 'max:100'],
            'billed_from' => ['required', 'string', 'max:100'],
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
                'so_from' => $validated['so_from'],
                'billed_from' => $validated['billed_from'],
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

            ActivityLog::record('created_sales_order', "Created Sales Order '{$order->so_number}' with " . count($validated['items']) . " item(s)", $order);
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
            'so_from' => ['required', 'string', 'max:100'],
            'billed_from' => ['required', 'string', 'max:100'],
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
                'so_from' => $validated['so_from'],
                'billed_from' => $validated['billed_from'],
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

            ActivityLog::record('updated_sales_order', "Updated Sales Order '{$salesOrder->so_number}'", $salesOrder);
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

        ActivityLog::record('deleted_sales_order', "Deleted Sales Order '{$soNumber}'");

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

        ActivityLog::record('returned_sales_order_item', "Processed return for item '{$item->product_name}' ({$newReturnedQty} returned)", $item);

        return back()->with('success', "Return status updated for item '{$item->product_name}'.");
    }

    /**
     * Show the bulk upload form for Sales Orders.
     */
    public function bulkUploadForm(): View
    {
        return view('admin.sales_orders.bulk_upload');
    }

    /**
     * Download a sample CSV file pre-formatted for Sales Orders bulk upload.
     */
    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sales_orders_bulk_upload_sample.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'so_number',
                'so_from',
                'billed_from',
                'billed_to',
                'billed_status',
                'bill_no',
                'so_remarks',
                'so_description',
                'product_name',
                'quantity',
                'unit_price',
                'item_remarks',
            ]);

            // Sample SO 1 (SO-2026-001 with 2 items)
            fputcsv($file, [
                'SO-2026-001',
                'Cloud',
                'Cosmic Store HQ',
                'Acme Enterprise',
                'billed',
                'INV-9001',
                'Urgent shipment',
                'Bulk office order',
                'Wireless Ergonomic Mouse',
                '5',
                '45.00',
                'Black Color',
            ]);

            fputcsv($file, [
                'SO-2026-001',
                'Cloud',
                'Cosmic Store HQ',
                'Acme Enterprise',
                'billed',
                'INV-9001',
                'Urgent shipment',
                'Bulk office order',
                'Mechanical Gaming Keyboard',
                '2',
                '120.00',
                'RGB Backlit',
            ]);

            // Sample SO 2 (SO-2026-002 with 1 item)
            fputcsv($file, [
                'SO-2026-002',
                'Dragon',
                'Cosmic Electronics',
                'Global Solutions Ltd',
                'pending',
                '',
                'Standard delivery',
                '',
                '4K USB-C Monitor 27"',
                '1',
                '350.00',
                'IPS Panel',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process the uploaded Excel/CSV sheet for Sales Orders & products.
     */
    public function processBulkUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['csv', 'txt', 'xlsx', 'xls'])) {
            return back()->withErrors(['file' => 'Invalid file format. Please upload a .csv file.']);
        }

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Unable to open uploaded file.']);
        }

        // Detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) {
            $delimiter = ';';
        } elseif (strpos($firstLine, "\t") !== false && strpos($firstLine, ',') === false) {
            $delimiter = "\t";
        }

        $headerRow = fgetcsv($handle, 0, $delimiter);
        if (!$headerRow) {
            fclose($handle);
            return back()->withErrors(['file' => 'Uploaded file is empty or corrupted.']);
        }

        // Clean UTF-8 BOM
        $headerRow[0] = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $headerRow[0]);

        // Map headers
        $headerMap = [];
        foreach ($headerRow as $index => $colName) {
            $normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', '-'], '_', $colName))));
            $headerMap[$index] = $normalized;
        }

        $rowNumber = 1;
        $parsedData = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($data, fn($val) => trim((string)$val) !== ''))) {
                continue;
            }

            $rowMap = [];
            foreach ($data as $index => $val) {
                $key = $headerMap[$index] ?? "col_$index";
                $rowMap[$key] = trim((string)$val);
            }
            $rowMap['_row_num'] = $rowNumber;
            $parsedData[] = $rowMap;
        }
        fclose($handle);

        if (empty($parsedData)) {
            return back()->withErrors(['file' => 'No data rows found in the uploaded file.']);
        }

        // Group rows by SO Number
        $groupedSos = [];
        $unnamedErrors = [];

        foreach ($parsedData as $row) {
            $soNumber = $row['so_number'] ?? $row['sonumber'] ?? $row['so_no'] ?? $row['so_num'] ?? '';

            if (empty($soNumber)) {
                $unnamedErrors[] = "Row {$row['_row_num']}: Missing 'so_number'.";
                continue;
            }

            if (!isset($groupedSos[$soNumber])) {
                $groupedSos[$soNumber] = [
                    'header' => $row,
                    'items' => [],
                    'rows' => [],
                ];
            }
            $groupedSos[$soNumber]['items'][] = $row;
            $groupedSos[$soNumber]['rows'][] = $row['_row_num'];
        }

        $successSoCount = 0;
        $totalItemsCreated = 0;
        $errors = $unnamedErrors;

        foreach ($groupedSos as $soNumber => $group) {
            $rowListStr = implode(', ', $group['rows']);

            // Duplicate SO check
            if (SalesOrder::where('so_number', $soNumber)->exists()) {
                $errors[] = "SO '{$soNumber}' (Rows {$rowListStr}): Sales order number already exists in database.";
                continue;
            }

            $headerRow = $group['header'];
            $soFrom = $headerRow['so_from'] ?? $headerRow['sofrom'] ?? $headerRow['from_so'] ?? $headerRow['so_from_name'] ?? '';
            $billedFrom = $headerRow['billed_from'] ?? $headerRow['from'] ?? $soFrom;
            $billedTo = $headerRow['billed_to'] ?? $headerRow['to'] ?? '';
            $billedStatus = strtolower($headerRow['billed_status'] ?? $headerRow['status'] ?? 'pending');

            if (!in_array($billedStatus, ['pending', 'billed', 'paid', 'cancelled'])) {
                $billedStatus = 'pending';
            }

            $billNo = $headerRow['bill_no'] ?? $headerRow['bill_number'] ?? $headerRow['invoice_no'] ?? null;
            $remarks = $headerRow['so_remarks'] ?? $headerRow['remarks'] ?? null;
            $description = $headerRow['so_description'] ?? $headerRow['description'] ?? null;

            // Header Validation - Only so_number and so_from required
            $soErrors = [];
            if (empty($soFrom)) {
                $soErrors[] = "Missing 'so_from'";
            }

            // Items Validation
            $validItems = [];
            foreach ($group['items'] as $itemRow) {
                $rNum = $itemRow['_row_num'];
                $productName = $itemRow['product_name'] ?? $itemRow['product'] ?? $itemRow['item_name'] ?? $itemRow['item'] ?? '';
                $qtyRaw = trim((string)($itemRow['quantity'] ?? $itemRow['qty'] ?? ''));
                $priceRaw = trim((string)($itemRow['unit_price'] ?? $itemRow['price'] ?? $itemRow['rate'] ?? ''));
                $itemRemarks = $itemRow['item_remarks'] ?? $itemRow['product_remarks'] ?? null;

                if (empty($productName)) {
                    $soErrors[] = "Row {$rNum}: Missing 'product_name'";
                }

                $quantity = 1;
                if ($qtyRaw !== '') {
                    if (!is_numeric($qtyRaw) || (int)$qtyRaw < 1) {
                        $soErrors[] = "Row {$rNum}: Quantity must be a valid number (at least 1)";
                    } else {
                        $quantity = (int)$qtyRaw;
                    }
                }

                $unitPrice = 0.0;
                if ($priceRaw !== '') {
                    if (!is_numeric($priceRaw) || (float)$priceRaw < 0) {
                        $soErrors[] = "Row {$rNum}: Unit price must be a valid non-negative number";
                    } else {
                        $unitPrice = (float)$priceRaw;
                    }
                }

                $validItems[] = [
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                    'return_status' => 'not_returned',
                    'returned_quantity' => 0,
                    'remarks' => $itemRemarks,
                ];
            }

            if (!empty($soErrors)) {
                $errors[] = "SO '{$soNumber}' (Rows {$rowListStr}) skipped due to errors: " . implode(', ', $soErrors);
                continue;
            }

            // Perform DB Transaction for SO and Items
            DB::transaction(function () use ($soNumber, $soFrom, $billedFrom, $billedTo, $billedStatus, $billNo, $remarks, $description, $validItems, &$totalItemsCreated) {
                $so = SalesOrder::create([
                    'so_number' => $soNumber,
                    'so_from' => $soFrom,
                    'billed_from' => $billedFrom,
                    'billed_to' => $billedTo,
                    'billed_status' => $billedStatus,
                    'bill_no' => $billNo,
                    'remarks' => $remarks,
                    'description' => $description,
                    'created_by' => Auth::id(),
                ]);

                foreach ($validItems as $itemData) {
                    $so->items()->create($itemData);
                    $totalItemsCreated++;
                }
            });

            $successSoCount++;
        }

        $message = "Bulk upload process completed. {$successSoCount} Sales Order(s) created with {$totalItemsCreated} total product item(s).";

        ActivityLog::record('bulk_uploaded_sales_orders', $message);

        if (!empty($errors)) {
            return redirect()->route('admin.sales-orders.bulk-upload')
                ->with('success', $message)
                ->with('bulk_errors', $errors);
        }

        return redirect()->route('admin.sales-orders.index')->with('success', $message);
    }
}
