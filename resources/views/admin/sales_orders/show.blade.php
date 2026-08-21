@extends('layouts.app')

@section('header', 'Sales Order Details')

@section('content')
<div x-data="{ returnModalOpen: false, selectedItem: null }" class="max-w-5xl mx-auto space-y-6">

    <!-- Top Bar & Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black font-mono text-slate-900 dark:text-white">{{ $salesOrder->so_number }}</h2>
                @php
                    $statusStyle = match($salesOrder->billed_status) {
                        'paid' => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                        'billed' => 'bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                        'pending' => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                        'cancelled' => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                        default => 'bg-slate-50 text-slate-700 border-slate-200'
                    };
                @endphp
                <span class="px-3 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border {{ $statusStyle }}">
                    {{ $salesOrder->billed_status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Created {{ $salesOrder->created_at->format('M d, Y \a\t h:i A') }} by <strong class="text-slate-700 dark:text-slate-300">{{ $salesOrder->creator?->name ?? 'System' }}</strong></p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.sales-orders.index') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-750 transition shadow-2xs">
                ← Back to List
            </a>

            @can('sales-orders.edit')
                <a href="{{ route('admin.sales-orders.edit', $salesOrder) }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                    Edit Order
                </a>
            @endcan
        </div>
    </div>

    <!-- Header Details Card & Receipts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Info Grid -->
        <div class="md:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 p-6 shadow-xs space-y-5">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-2.5 font-heading">
                Order Information & Entities
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-3 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                    <span class="text-slate-400 font-bold text-[10px] uppercase block mb-1">SO From</span>
                    <span class="font-bold text-violet-600 dark:text-violet-400 text-sm">{{ $salesOrder->so_from ?: 'N/A' }}</span>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                    <span class="text-slate-400 font-bold text-[10px] uppercase block mb-1">Billed From</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ $salesOrder->billed_from ?: 'N/A' }}</span>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                    <span class="text-slate-400 font-bold text-[10px] uppercase block mb-1">Billed To</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $salesOrder->billed_to ?: 'N/A' }}</span>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                    <span class="text-slate-400 font-bold text-[10px] uppercase block mb-1">Bill No.</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $salesOrder->bill_no ?: 'N/A' }}</span>
                </div>
            </div>

            @if ($salesOrder->remarks || $salesOrder->description)
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    @if ($salesOrder->remarks)
                        <div>
                            <span class="text-slate-400 font-bold text-[10px] uppercase block mb-1">Remarks</span>
                            <p class="text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl border border-slate-200/60 dark:border-slate-800">{{ $salesOrder->remarks }}</p>
                        </div>
                    @endif

                    @if ($salesOrder->description)
                        <div>
                            <span class="text-slate-400 font-bold text-[10px] uppercase block mb-1">Description</span>
                            <p class="text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl border border-slate-200/60 dark:border-slate-800">{{ $salesOrder->description }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Attached Receipts Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 p-6 shadow-xs space-y-4">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-2.5 font-heading">
                Attached Receipts
            </h3>

            <div class="grid grid-cols-2 gap-3">
                <!-- Bill Image -->
                <div class="space-y-1.5 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Bill Image</span>
                    @if ($salesOrder->bill_image_url)
                        <a href="{{ $salesOrder->bill_image_url }}" target="_blank" class="block group">
                            <img src="{{ $salesOrder->bill_image_url }}" alt="Bill Receipt" class="w-full h-24 object-cover rounded-2xl border border-slate-200 dark:border-slate-700 group-hover:scale-105 transition-transform duration-200 shadow-2xs">
                        </a>
                    @else
                        <div class="w-full h-24 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs text-slate-400 italic">No File</div>
                    @endif
                </div>

                <!-- Slip Image -->
                <div class="space-y-1.5 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Bank Slip</span>
                    @if ($salesOrder->slip_image_url)
                        <a href="{{ $salesOrder->slip_image_url }}" target="_blank" class="block group">
                            <img src="{{ $salesOrder->slip_image_url }}" alt="Slip Proof" class="w-full h-24 object-cover rounded-2xl border border-slate-200 dark:border-slate-700 group-hover:scale-105 transition-transform duration-200 shadow-2xs">
                        </a>
                    @else
                        <div class="w-full h-24 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs text-slate-400 italic">No File</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items & Returns Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">
                Product / Maintenance Items & Return Status
            </h3>
            <span class="text-xs text-slate-400 font-medium">{{ $salesOrder->items->count() }} item(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-3 font-semibold">Product / Maintenance Name</th>
                        <th class="py-3 px-3 font-semibold text-center">Qty</th>
                        <th class="py-3 px-3 font-semibold text-right">Unit Price</th>
                        <th class="py-3 px-3 font-semibold text-right">Total Price</th>
                        <th class="py-3 px-3 font-semibold text-center">Return Status</th>
                        <th class="py-3 px-3 font-semibold text-center">Returned Qty</th>
                        <th class="py-3 px-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                    @foreach ($salesOrder->items as $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-3">
                                <div class="font-semibold text-slate-900 dark:text-slate-100 text-xs">{{ $item->product_name }}</div>
                                @if ($item->remarks)
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->remarks }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-medium">{{ $item->quantity }}</td>
                            <td class="py-3.5 px-3 text-right font-mono">NRs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">NRs. {{ number_format($item->total_price, 2) }}</td>

                            <!-- Return Status Badge -->
                            <td class="py-3.5 px-3 text-center">
                                @php
                                    $returnStyle = match($item->return_status) {
                                        'returned' => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                        'partially_returned' => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                        default => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $returnStyle }}">
                                    {{ str_replace('_', ' ', $item->return_status) }}
                                </span>
                            </td>

                            <!-- Returned Quantity -->
                            <td class="py-3.5 px-3 text-center font-mono">
                                @if ($item->returned_quantity > 0)
                                    <span class="font-bold text-rose-600 dark:text-rose-400">{{ $item->returned_quantity }} / {{ $item->quantity }}</span>
                                    @if ($item->returned_at)
                                        <span class="block text-[9px] text-slate-400">{{ $item->returned_at->format('M d') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>

                            <!-- Item Return Action Button -->
                            <td class="py-3.5 px-3 text-right">
                                @can('sales-orders.edit')
                                    @if ($item->return_status !== 'returned')
                                        <button @click="selectedItem = {{ json_encode($item) }}; returnModalOpen = true" class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 text-xs font-semibold transition shadow-2xs">
                                            Return Item
                                        </button>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Fully Returned</span>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Grand Total Summary Banner -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-sm">
            <span class="font-bold text-slate-700 dark:text-slate-300">Grand Total Billed Amount</span>
            <span class="font-mono text-xl font-black text-indigo-600 dark:text-indigo-400">
                NRs. {{ number_format($salesOrder->items->sum('total_price'), 2) }}
            </span>
        </div>
    </div>

    <!-- Item Return Modal -->
    <div x-show="returnModalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="returnModalOpen = false" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">Process Item Return</h3>
                <button @click="returnModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">✕</button>
            </div>

            <form x-bind:action="`/admin/sales-orders/items/${selectedItem?.id}/return`" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Product</label>
                    <div class="px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-800 dark:text-slate-200" x-text="selectedItem?.product_name"></div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Returned Quantity</label>
                    <input type="number" name="returned_quantity" x-bind:max="selectedItem?.quantity" min="1" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[11px] text-slate-400 mt-1 block">Maximum returnable quantity: <strong x-text="selectedItem?.quantity"></strong></span>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Return Remarks / Reason</label>
                    <textarea name="remarks" rows="2" placeholder="e.g. Defective unit, customer exchange..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                    <button type="button" @click="returnModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-md shadow-rose-600/25">
                        Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
