@extends('layouts.app')

@section('header', 'Edit Bill')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    billedFromPreset: ['Cloud', 'Dragon', 'Cosmic'].includes('{{ $bill->billed_from }}') ? '{{ $bill->billed_from }}' : 'Other',
    billedFromCustom: '{{ $bill->billed_from }}',
    billedToPreset: ['PBS', 'Prativa Plus Two', 'Prativa School', 'EGA'].includes('{{ $bill->billed_to }}') ? '{{ $bill->billed_to }}' : 'Other',
    billedToCustom: '{{ $bill->billed_to }}'
}">

    <!-- Page Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Bill {{ $bill->bill_number }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update bill parameters, party details, or replace receipt files.</p>
        </div>
        <a href="{{ route('admin.bills.index') }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:underline">
            ← Back to Bills
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

        <form method="POST" action="{{ route('admin.bills.update', $bill) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Section 1: Bill Reference Details -->
            <div class="space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Bill Reference Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                    <!-- Bill Number -->
                    <div>
                        <label for="bill_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Bill Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="bill_number" name="bill_number" value="{{ old('bill_number', $bill->bill_number) }}" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Billed From -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            From <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="billedFromPreset" @change="if (billedFromPreset !== 'Other') billedFromCustom = billedFromPreset; else billedFromCustom = ''"
                            class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-2">
                            <option value="Cloud">Cloud</option>
                            <option value="Dragon">Dragon</option>
                            <option value="Cosmic">Cosmic</option>
                            <option value="Other">Other (Custom Write-in)</option>
                        </select>
                        <input type="text" name="billed_from" x-model="billedFromCustom" required placeholder="Enter billing source name..."
                            :readonly="billedFromPreset !== 'Other'"
                            :class="billedFromPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/60 opacity-80 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Billed To -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            To <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="billedToPreset" @change="if (billedToPreset !== 'Other') billedToCustom = billedToPreset; else billedToCustom = ''"
                            class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-2">
                            <option value="PBS">PBS</option>
                            <option value="Prativa Plus Two">Prativa Plus Two</option>
                            <option value="Prativa School">Prativa School</option>
                            <option value="EGA">EGA</option>
                            <option value="Other">Other (Custom Write-in)</option>
                        </select>
                        <input type="text" name="billed_to" x-model="billedToCustom" required placeholder="Enter target party name..."
                            :readonly="billedToPreset !== 'Other'"
                            :class="billedToPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/60 opacity-80 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Bill Status <span class="text-rose-500">*</span>
                        </label>
                        <select id="status" name="status" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="billed" {{ old('status', $bill->status) === 'billed' ? 'selected' : '' }}>Billed</option>
                            <option value="pending" {{ old('status', $bill->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('status', $bill->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ old('status', $bill->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Total Amount -->
                    <div>
                        <label for="amount" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Amount (NRs.) <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <input type="number" step="0.01" min="0" id="amount" name="amount" value="{{ old('amount', $bill->amount) }}" placeholder="0.00"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Section 2: Replace Uploaded Images -->
            <div class="space-y-4 pt-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Receipt Image File Attachments
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Bill Image File -->
                    <div class="space-y-2">
                        <label for="bill_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Primary Bill Receipt Image
                        </label>
                        @if ($bill->bill_image_url)
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <img src="{{ $bill->bill_image_url }}" alt="Current Bill Image" class="w-12 h-12 object-cover rounded-lg">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Current file attached</span>
                            </div>
                        @endif
                        <input type="file" id="bill_image" name="bill_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                    </div>

                    <!-- Slip Image File -->
                    <div class="space-y-2">
                        <label for="slip_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Payment Slip Proof Image
                        </label>
                        @if ($bill->slip_image_url)
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <img src="{{ $bill->slip_image_url }}" alt="Current Slip Image" class="w-12 h-12 object-cover rounded-lg">
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
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('remarks', $bill->remarks) }}</textarea>
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Description</label>
                        <textarea id="description" name="description" rows="2" placeholder="Description..."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $bill->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.bills.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition">
                    Update Bill
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
