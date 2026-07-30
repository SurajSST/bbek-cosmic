<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Bill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillController extends Controller
{
    /**
     * Display a listing of bills with search, sorting, and pagination.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort', 'created_at');
        $sortDir = $request->query('dir', 'desc');

        $bills = Bill::with('creator')
            ->search($search)
            ->when($status && $status !== 'all', function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->sort($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return view('admin.bills.index', compact(
            'bills',
            'search',
            'status',
            'sortBy',
            'sortDir'
        ));
    }

    /**
     * Show the form for uploading/creating a new bill.
     */
    public function create(): View
    {
        return view('admin.bills.create');
    }

    /**
     * Store a newly created bill document in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_number' => ['required', 'string', 'max:100', 'unique:bills,bill_number'],
            'billed_from' => ['required', 'string', 'max:100'],
            'billed_to' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['pending', 'billed', 'paid', 'cancelled'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'bill_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'slip_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'remarks' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $billImagePath = $request->file('bill_image')->store('bills/receipts', 'public');
            $slipImagePath = null;

            if ($request->hasFile('slip_image')) {
                $slipImagePath = $request->file('slip_image')->store('bills/slips', 'public');
            }

            $createdBill = Bill::create([
                'bill_number' => $validated['bill_number'],
                'billed_from' => $validated['billed_from'],
                'billed_to' => $validated['billed_to'],
                'status' => $validated['status'],
                'amount' => $validated['amount'] ?? null,
                'bill_image' => $billImagePath,
                'slip_image' => $slipImagePath,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => Auth::id(),
            ]);

            ActivityLog::record('created_bill', "Uploaded Bill '{$createdBill->bill_number}'", $createdBill);
        });

        return redirect()->route('admin.bills.index')
            ->with('success', "Bill '{$validated['bill_number']}' uploaded successfully.");
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill): View
    {
        $bill->load('creator');

        return view('admin.bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified bill.
     */
    public function edit(Bill $bill): View
    {
        return view('admin.bills.edit', compact('bill'));
    }

    /**
     * Update the specified bill in storage.
     */
    public function update(Request $request, Bill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'bill_number' => ['required', 'string', 'max:100', Rule::unique('bills')->ignore($bill->id)],
            'billed_from' => ['required', 'string', 'max:100'],
            'billed_to' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['pending', 'billed', 'paid', 'cancelled'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'bill_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'slip_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'remarks' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $bill, $validated) {
            if ($request->hasFile('bill_image')) {
                if ($bill->bill_image) {
                    Storage::disk('public')->delete($bill->bill_image);
                }
                $bill->bill_image = $request->file('bill_image')->store('bills/receipts', 'public');
            }

            if ($request->hasFile('slip_image')) {
                if ($bill->slip_image) {
                    Storage::disk('public')->delete($bill->slip_image);
                }
                $bill->slip_image = $request->file('slip_image')->store('bills/slips', 'public');
            }

            $bill->update([
                'bill_number' => $validated['bill_number'],
                'billed_from' => $validated['billed_from'],
                'billed_to' => $validated['billed_to'],
                'status' => $validated['status'],
                'amount' => $validated['amount'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            ActivityLog::record('updated_bill', "Updated Bill '{$bill->bill_number}'", $bill);
        });

        return redirect()->route('admin.bills.index')
            ->with('success', "Bill '{$bill->bill_number}' updated successfully.");
    }

    /**
     * Remove the specified bill from storage.
     */
    public function destroy(Bill $bill): RedirectResponse
    {
        $billNumber = $bill->bill_number;

        if ($bill->bill_image) {
            Storage::disk('public')->delete($bill->bill_image);
        }
        if ($bill->slip_image) {
            Storage::disk('public')->delete($bill->slip_image);
        }

        $bill->delete();

        ActivityLog::record('deleted_bill', "Deleted Bill '{$billNumber}'");

        return redirect()->route('admin.bills.index')
            ->with('success', "Bill '{$billNumber}' deleted successfully.");
    }
}
