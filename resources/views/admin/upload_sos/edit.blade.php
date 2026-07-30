@extends('layouts.app')

@section('header', 'Edit Sales Order')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    soFromPreset: ['Cloud', 'Dragon', 'Cosmic'].includes('{{ $uploadSo->so_from }}') ? '{{ $uploadSo->so_from }}' : 'Other',
    soFromCustom: '{{ $uploadSo->so_from }}',
    billedToPreset: ['Prativa', 'PBS', 'Prativa Plus Two', 'Prativa School', 'EGA'].includes('{{ $uploadSo->billed_to }}') ? '{{ $uploadSo->billed_to }}' : 'Other',
    billedToCustom: '{{ $uploadSo->billed_to }}'
}">

    <!-- Page Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Sales Order {{ $uploadSo->so_number }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update Sales Order parameters or replace uploaded image files.</p>
        </div>
        <a href="{{ route('admin.upload-sos.index') }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:underline">
            ← Back to Upload SOs
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
                <p class="font-bold">Please correct the validation errors below:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.upload-sos.update', $uploadSo) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Section 1: Sales Order Reference Details -->
            <div class="space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Sales Order Information
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                    <!-- SO Number -->
                    <div>
                        <label for="so_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            SO Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="so_number" name="so_number" value="{{ old('so_number', $uploadSo->so_number) }}" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- SO From -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            SO From <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="soFromPreset" @change="if (soFromPreset !== 'Other') soFromCustom = soFromPreset; else soFromCustom = ''"
                            class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-2">
                            <option value="Cosmic">Cosmic</option>
                            <option value="Cloud">Cloud</option>
                            <option value="Dragon">Dragon</option>
                            <option value="Other">Other (Custom Write-in)</option>
                        </select>
                        <input type="text" name="so_from" x-model="soFromCustom" required placeholder="Enter SO source..."
                            :readonly="soFromPreset !== 'Other'"
                            :class="soFromPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/60 opacity-80 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Billed To -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            To <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="billedToPreset" @change="if (billedToPreset !== 'Other') billedToCustom = billedToPreset; else billedToCustom = ''"
                            class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-2">
                            <option value="Prativa">Prativa</option>
                            <option value="PBS">PBS</option>
                            <option value="Prativa Plus Two">Prativa Plus Two</option>
                            <option value="Prativa School">Prativa School</option>
                            <option value="EGA">EGA</option>
                            <option value="Other">Other (Custom Write-in)</option>
                        </select>
                        <input type="text" name="billed_to" x-model="billedToCustom" required placeholder="Enter customer name..."
                            :readonly="billedToPreset !== 'Other'"
                            :class="billedToPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/60 opacity-80 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Status <span class="text-rose-500">*</span>
                        </label>
                        <select id="status" name="status" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="pending" {{ old('status', $uploadSo->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="billed" {{ old('status', $uploadSo->status) === 'billed' ? 'selected' : '' }}>Billed</option>
                            <option value="paid" {{ old('status', $uploadSo->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ old('status', $uploadSo->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Replace Uploaded Images -->
            <div class="space-y-4 pt-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Sales Order Image Attachments
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- SO Image File -->
                    <div class="space-y-2">
                        <label for="so_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Primary Sales Order Image
                        </label>
                        @if ($uploadSo->so_image_url)
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <img src="{{ $uploadSo->so_image_url }}" alt="Current SO Image" class="w-12 h-12 object-cover rounded-lg">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Current file attached</span>
                            </div>
                        @endif
                        <input type="file" id="so_image" name="so_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                    </div>

                    <!-- Slip Image File -->
                    <div class="space-y-2">
                        <label for="slip_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Payment Slip Proof Image
                        </label>
                        @if ($uploadSo->slip_image_url)
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <img src="{{ $uploadSo->slip_image_url }}" alt="Current Slip Image" class="w-12 h-12 object-cover rounded-lg">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Current slip attached</span>
                            </div>
                        @endif
                        <input type="file" id="slip_image" name="slip_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                    </div>
                </div>
            </div>

            <!-- Section 3: Remarks & Description -->
            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="remarks" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Remarks</label>
                        <textarea id="remarks" name="remarks" rows="2" placeholder="Notes..."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('remarks', $uploadSo->remarks) }}</textarea>
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Description</label>
                        <textarea id="description" name="description" rows="2" placeholder="Description..."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $uploadSo->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.upload-sos.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition">
                    Update Sales Order
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
