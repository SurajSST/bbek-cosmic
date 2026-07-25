@extends('layouts.app')

@section('header', 'Sales Orders')

@section('content')
<div x-data="{ imageModalOpen: false, modalImageUrl: '', modalTitle: '' }" class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Sales Orders</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage sales orders, billing details, attached receipts, and item-level returns.</p>
        </div>

        @can('sales-orders.create')
            <a href="{{ route('admin.sales-orders.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Sales Order
            </a>
        @endcan
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.sales-orders.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

            <!-- Search Keyword (SO Number, Billed From/To, or Bill No) -->
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by SO #, Bill No, From, To..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Billed Status Filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Billed Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="billed" {{ request('status') === 'billed' ? 'selected' : '' }}>Billed</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Submit Filter Button -->
            <div class="flex gap-2">
                <button type="submit" class="w-full px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-semibold transition">
                    Apply Filter
                </button>
                @if (request('search') || (request('status') && request('status') !== 'all'))
                    <a href="{{ route('admin.sales-orders.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-semibold transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Orders Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="py-3.5 px-4 font-semibold w-12 text-center">S.N.</th>
                        
                        <!-- Sortable SO Number -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['sort' => 'so_number', 'dir' => request('sort') === 'so_number' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Sales Order (SO)
                                @if(request('sort', 'created_at') === 'so_number')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <th class="py-3.5 px-4 font-semibold">Product / Items Summary</th>
                        
                        <!-- Sortable Billed From -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['sort' => 'billed_from', 'dir' => request('sort') === 'billed_from' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Billed From
                                @if(request('sort') === 'billed_from')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <!-- Sortable Billed To -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['sort' => 'billed_to', 'dir' => request('sort') === 'billed_to' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Billed To
                                @if(request('sort') === 'billed_to')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <!-- Sortable Billed Status -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['sort' => 'billed_status', 'dir' => request('sort') === 'billed_status' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Billed Status
                                @if(request('sort') === 'billed_status')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <!-- Sortable Bill No -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.sales-orders.index', array_merge(request()->query(), ['sort' => 'bill_no', 'dir' => request('sort') === 'bill_no' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Bill No.
                                @if(request('sort') === 'bill_no')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <th class="py-3.5 px-4 font-semibold text-center">Bill Image</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Slip Image</th>
                        <th class="py-3.5 px-4 font-semibold">Remarks</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse ($salesOrders as $index => $order)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <!-- S.N. -->
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400">
                                {{ $salesOrders->firstItem() + $index }}
                            </td>

                            <!-- Sales Order (SO) -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.sales-orders.show', $order) }}" class="font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline text-sm block">
                                    {{ $order->so_number }}
                                </a>
                                <span class="text-[10px] text-slate-400">{{ $order->created_at->format('M d, Y') }}</span>
                            </td>

                            <!-- Product / Items Summary -->
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ $order->items->first()?->product_name ?? 'No items' }}
                                        @if ($order->items->count() > 1)
                                            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 text-[10px] font-semibold">
                                                +{{ $order->items->count() - 1 }} more
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        Total: NRs. {{ number_format($order->items->sum('total_price'), 2) }}
                                    </div>
                                </div>
                            </td>

                            <!-- Billed From -->
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-semibold">
                                <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 font-medium">
                                    {{ $order->billed_from }}
                                </span>
                            </td>

                            <!-- Billed To -->
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-slate-100">
                                {{ $order->billed_to }}
                            </td>

                            <!-- Billed Status -->
                            <td class="py-3.5 px-4">
                                @php
                                    $statusStyle = match($order->billed_status) {
                                        'paid' => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                        'billed' => 'bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                        'pending' => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                        'cancelled' => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusStyle }}">
                                    {{ $order->billed_status }}
                                </span>
                            </td>

                            <!-- Bill No. -->
                            <td class="py-3.5 px-4 font-mono text-slate-700 dark:text-slate-300">
                                {{ $order->bill_no ?: '—' }}
                            </td>

                            <!-- Bill Image -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($order->bill_image_url)
                                    <button @click="modalImageUrl = '{{ $order->bill_image_url }}'; modalTitle = 'Bill Receipt ({{ $order->so_number }})'; imageModalOpen = true" class="group relative inline-block">
                                        <img src="{{ $order->bill_image_url }}" alt="Bill Image" class="w-9 h-9 object-cover rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm group-hover:scale-105 transition">
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">No file</span>
                                @endif
                            </td>

                            <!-- Slip Image -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($order->slip_image_url)
                                    <button @click="modalImageUrl = '{{ $order->slip_image_url }}'; modalTitle = 'Bank / Pay Slip ({{ $order->so_number }})'; imageModalOpen = true" class="group relative inline-block">
                                        <img src="{{ $order->slip_image_url }}" alt="Slip Image" class="w-9 h-9 object-cover rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm group-hover:scale-105 transition">
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">No file</span>
                                @endif
                            </td>

                            <!-- Remarks -->
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                {{ $order->remarks ?: '—' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.sales-orders.show', $order) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Order">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    @can('sales-orders.edit')
                                        <a href="{{ route('admin.sales-orders.edit', $order) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Order">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endcan

                                    @can('sales-orders.delete')
                                        <form method="POST" action="{{ route('admin.sales-orders.destroy', $order) }}" onsubmit="return confirm('Are you sure you want to delete Sales Order {{ $order->so_number }}? All items and uploaded receipts will be deleted.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete Order">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    <p class="font-medium text-slate-600 dark:text-slate-300">No sales orders found</p>
                                    <p class="text-xs text-slate-400">Create your first sales order or adjust your search filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($salesOrders->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $salesOrders->links() }}
            </div>
        @endif
    </div>

    <!-- Image Lightbox Modal -->
    <div x-show="imageModalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="imageModalOpen = false" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-2xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base" x-text="modalTitle"></h3>
                <button @click="imageModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">✕</button>
            </div>
            <div class="flex justify-center p-2 bg-slate-950/20 rounded-xl">
                <img :src="modalImageUrl" alt="Receipt Full Image" class="max-h-[70vh] object-contain rounded-lg">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <a :href="modalImageUrl" target="_blank" download class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500">Download Image</a>
                <button @click="imageModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
