<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadSo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UploadSoController extends Controller
{
    /**
     * Display a listing of Upload SOs with search, sorting, and pagination.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort', 'created_at');
        $sortDir = $request->query('dir', 'desc');

        $uploadSos = UploadSo::with('creator')
            ->search($search)
            ->when($status && $status !== 'all', function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->sort($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return view('admin.upload_sos.index', compact(
            'uploadSos',
            'search',
            'status',
            'sortBy',
            'sortDir'
        ));
    }

    /**
     * Show the form for uploading/creating a new Upload SO.
     */
    public function create(): View
    {
        return view('admin.upload_sos.create');
    }

    /**
     * Store a newly created Upload SO document in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:100', 'unique:upload_sos,so_number'],
            'billed_from' => ['required', 'string', 'max:100'],
            'billed_to' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['pending', 'billed', 'paid', 'cancelled'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'so_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'slip_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'remarks' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $soImagePath = $request->file('so_image')->store('upload_sos/documents', 'public');
            $slipImagePath = null;

            if ($request->hasFile('slip_image')) {
                $slipImagePath = $request->file('slip_image')->store('upload_sos/slips', 'public');
            }

            UploadSo::create([
                'so_number' => $validated['so_number'],
                'billed_from' => $validated['billed_from'],
                'billed_to' => $validated['billed_to'],
                'status' => $validated['status'],
                'amount' => $validated['amount'] ?? null,
                'so_image' => $soImagePath,
                'slip_image' => $slipImagePath,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('admin.upload-sos.index')
            ->with('success', "Sales Order '{$validated['so_number']}' uploaded successfully.");
    }

    /**
     * Display the specified Upload SO details.
     */
    public function show(UploadSo $uploadSo): View
    {
        $uploadSo->load('creator');

        return view('admin.upload_sos.show', compact('uploadSo'));
    }

    /**
     * Show the form for editing the specified Upload SO.
     */
    public function edit(UploadSo $uploadSo): View
    {
        return view('admin.upload_sos.edit', compact('uploadSo'));
    }

    /**
     * Update the specified Upload SO in storage.
     */
    public function update(Request $request, UploadSo $uploadSo): RedirectResponse
    {
        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:100', Rule::unique('upload_sos')->ignore($uploadSo->id)],
            'billed_from' => ['required', 'string', 'max:100'],
            'billed_to' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['pending', 'billed', 'paid', 'cancelled'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'so_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'slip_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'remarks' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $uploadSo, $validated) {
            if ($request->hasFile('so_image')) {
                if ($uploadSo->so_image) {
                    Storage::disk('public')->delete($uploadSo->so_image);
                }
                $uploadSo->so_image = $request->file('so_image')->store('upload_sos/documents', 'public');
            }

            if ($request->hasFile('slip_image')) {
                if ($uploadSo->slip_image) {
                    Storage::disk('public')->delete($uploadSo->slip_image);
                }
                $uploadSo->slip_image = $request->file('slip_image')->store('upload_sos/slips', 'public');
            }

            $uploadSo->update([
                'so_number' => $validated['so_number'],
                'billed_from' => $validated['billed_from'],
                'billed_to' => $validated['billed_to'],
                'status' => $validated['status'],
                'amount' => $validated['amount'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);
        });

        return redirect()->route('admin.upload-sos.index')
            ->with('success', "Sales Order '{$uploadSo->so_number}' updated successfully.");
    }

    /**
     * Remove the specified Upload SO from storage.
     */
    public function destroy(UploadSo $uploadSo): RedirectResponse
    {
        $soNumber = $uploadSo->so_number;

        if ($uploadSo->so_image) {
            Storage::disk('public')->delete($uploadSo->so_image);
        }
        if ($uploadSo->slip_image) {
            Storage::disk('public')->delete($uploadSo->slip_image);
        }

        $uploadSo->delete();

        return redirect()->route('admin.upload-sos.index')
            ->with('success', "Sales Order '{$soNumber}' deleted successfully.");
    }
}
