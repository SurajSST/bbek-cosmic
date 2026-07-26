@extends('layouts.app')

@section('header', 'Bill Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-mono">Bill {{ $bill->bill_number }}</h2>
                @php
                    $statusStyle = match($bill->status) {
                        'paid' => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                        'billed' => 'bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                        'pending' => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                        'cancelled' => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                        default => 'bg-slate-50 text-slate-700 border-slate-200'
                    };
                @endphp
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusStyle }}">
                    {{ $bill->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Uploaded on {{ $bill->created_at->format('F d, Y \a\t h:i A') }} @if($bill->creator) by {{ $bill->creator->name }} @endif</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.bills.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                ← Back
            </a>
            @can('bills.edit')
                <a href="{{ route('admin.bills.edit', $bill) }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow transition">
                    Edit Bill
                </a>
            @endcan
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
        
        <!-- Key Metadata Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">From</span>
                <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ $bill->billed_from }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">To</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $bill->billed_to }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bill Amount</span>
                <span class="text-sm font-mono font-extrabold text-slate-900 dark:text-white">
                    {{ $bill->amount ? 'NRs. ' . number_format($bill->amount, 2) : 'N/A' }}
                </span>
            </div>
        </div>

        @if ($bill->remarks || $bill->description)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                @if ($bill->remarks)
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Remarks</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/40 p-3 rounded-xl border border-slate-200 dark:border-slate-800">{{ $bill->remarks }}</p>
                    </div>
                @endif

                @if ($bill->description)
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Description</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/40 p-3 rounded-xl border border-slate-200 dark:border-slate-800">{{ $bill->description }}</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Attached Images Section -->
        <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                Attached Receipt & Documents
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Bill Image -->
                <div>
                    <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Primary Bill Receipt Image</span>
                    @if ($bill->bill_image_url)
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-950/20 p-2 space-y-2">
                            <img src="{{ $bill->bill_image_url }}" alt="Bill Receipt" class="w-full max-h-96 object-contain rounded-xl">
                            <div class="flex justify-end p-1">
                                <a href="{{ $bill->bill_image_url }}" target="_blank" download class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500">
                                    Download Image
                                </a>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">No bill receipt image attached.</p>
                    @endif
                </div>

                <!-- Slip Image -->
                <div>
                    <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Secondary Slip / Payment Proof Image</span>
                    @if ($bill->slip_image_url)
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-950/20 p-2 space-y-2">
                            <img src="{{ $bill->slip_image_url }}" alt="Payment Slip" class="w-full max-h-96 object-contain rounded-xl">
                            <div class="flex justify-end p-1">
                                <a href="{{ $bill->slip_image_url }}" target="_blank" download class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500">
                                    Download Slip
                                </a>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">No payment slip image attached.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
