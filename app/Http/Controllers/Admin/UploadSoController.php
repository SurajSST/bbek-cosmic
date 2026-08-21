<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\UploadSo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UploadSoController extends Controller
{
    /**
     * Display a listing of Upload SOs with search, sorting, and pagination.
     */
    public function index(Request $request): Response
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

        return Inertia::render('Admin/UploadSos/Index', [
            'uploadSos' => $uploadSos,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sortBy,
                'dir' => $sortDir,
            ],
        ]);
    }

    /**
     * Show the form for uploading/creating a new Upload SO.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/UploadSos/Create');
    }

    /**
     * Store a newly created Upload SO document in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:100', 'unique:upload_sos,so_number'],
            'so_from' => ['required', 'string', 'max:100'],
            'billed_from' => ['nullable', 'string', 'max:100'],
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

            $createdSo = UploadSo::create([
                'so_number' => $validated['so_number'],
                'so_from' => $validated['so_from'],
                'billed_from' => $validated['billed_from'] ?? null,
                'billed_to' => $validated['billed_to'],
                'status' => $validated['status'],
                'amount' => $validated['amount'] ?? null,
                'so_image' => $soImagePath,
                'slip_image' => $slipImagePath,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => Auth::id(),
            ]);

            ActivityLog::record('uploaded_so_created', "Uploaded Sales Order '{$createdSo->so_number}'", $createdSo);
        });

        return redirect()->route('admin.upload-sos.index')
            ->with('success', "Sales Order '{$validated['so_number']}' uploaded successfully.");
    }

    /**
     * Display the specified Upload SO details.
     */
    public function show(UploadSo $uploadSo): Response
    {
        $uploadSo->load('creator');

        return Inertia::render('Admin/UploadSos/Show', [
            'uploadSo' => $uploadSo,
        ]);
    }

    /**
     * Show the form for editing the specified Upload SO.
     */
    public function edit(UploadSo $uploadSo): Response
    {
        return Inertia::render('Admin/UploadSos/Edit', [
            'uploadSo' => $uploadSo,
        ]);
    }

    /**
     * Update the specified Upload SO in storage.
     */
    public function update(Request $request, UploadSo $uploadSo): RedirectResponse
    {
        $validated = $request->validate([
            'so_number' => ['required', 'string', 'max:100', Rule::unique('upload_sos')->ignore($uploadSo->id)],
            'so_from' => ['required', 'string', 'max:100'],
            'billed_from' => ['nullable', 'string', 'max:100'],
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
                'so_from' => $validated['so_from'],
                'billed_from' => $validated['billed_from'] ?? null,
                'billed_to' => $validated['billed_to'],
                'status' => $validated['status'],
                'amount' => $validated['amount'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            ActivityLog::record('uploaded_so_updated', "Updated Uploaded Sales Order '{$uploadSo->so_number}'", $uploadSo);
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

        ActivityLog::record('uploaded_so_deleted', "Deleted Uploaded Sales Order '{$soNumber}'");

        return redirect()->route('admin.upload-sos.index')
            ->with('success', "Sales Order '{$soNumber}' deleted successfully.");
    }
}
