@extends('layouts.app')

@section('header', 'Upload Bill')

@section('content')
<div x-data="{ imageModalOpen: false, modalImageUrl: '', modalTitle: '' }" class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold font-heading text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Upload Bill Documents
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage uploaded vendor bills, company receipts, payment slips, and reference details.</p>
        </div>

        @can('bills.create')
            <a href="{{ route('admin.bills.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition hover:scale-[1.02] active:scale-[0.98] shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                <span>Upload Bill / Receipt</span>
            </a>
        @endcan
    </div>

    <!-- Search & Filter Card -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
        <form method="GET" action="{{ route('admin.bills.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

            <!-- Search Keyword -->
            <div class="sm:col-span-6 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Bill Number, From, To..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Bill Statuses</option>
                    <option value="billed" {{ request('status') === 'billed' ? 'selected' : '' }}>Billed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="sm:col-span-3 flex gap-2">
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition">
                    Filter
                </button>
                @if (request('search') || (request('status') && request('status') !== 'all'))
                    <a href="{{ route('admin.bills.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-semibold transition flex items-center justify-center" title="Reset Filters">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bills Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="py-3.5 px-4 font-semibold w-12 text-center">S.N.</th>
                        
                        <!-- Sortable Bill Number -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.bills.index', array_merge(request()->query(), ['sort' => 'bill_number', 'dir' => request('sort') === 'bill_number' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Bill Number
                                @if(request('sort', 'created_at') === 'bill_number')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <!-- Sortable From -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.bills.index', array_merge(request()->query(), ['sort' => 'billed_from', 'dir' => request('sort') === 'billed_from' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                From
                                @if(request('sort') === 'billed_from')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <!-- Sortable To -->
                        <th class="py-3.5 px-4 font-semibold">
                            <a href="{{ route('admin.bills.index', array_merge(request()->query(), ['sort' => 'billed_to', 'dir' => request('sort') === 'billed_to' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                To
                                @if(request('sort') === 'billed_to')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <!-- Sortable Status -->
                        <th class="py-3.5 px-4 font-semibold text-center">
                            <a href="{{ route('admin.bills.index', array_merge(request()->query(), ['sort' => 'status', 'dir' => request('sort') === 'status' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Status
                                @if(request('sort') === 'status')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <!-- Sortable Amount -->
                        <th class="py-3.5 px-4 font-semibold text-right">
                            <a href="{{ route('admin.bills.index', array_merge(request()->query(), ['sort' => 'amount', 'dir' => request('sort') === 'amount' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                Amount
                                @if(request('sort') === 'amount')
                                    <span>{{ request('dir') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>

                        <th class="py-3.5 px-4 font-semibold text-center">Receipts</th>
                        <th class="py-3.5 px-4 font-semibold">Remarks</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                    @forelse ($bills as $index => $bill)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400 text-xs">
                                {{ $bills->firstItem() + $index }}
                            </td>

                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.bills.show', $bill) }}" class="font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline text-xs block">
                                    {{ $bill->bill_number }}
                                </a>
                                <span class="text-[10px] text-slate-400">{{ $bill->created_at->format('M d, Y') }}</span>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 font-semibold text-[11px]">
                                    {{ $bill->billed_from }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $bill->billed_to }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $statusStyle = match($bill->status) {
                                        'paid' => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                        'billed' => 'bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                        'pending' => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                        'cancelled' => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusStyle }}">
                                    {{ $bill->status }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white text-right text-xs">
                                {{ $bill->amount ? 'NRs. ' . number_format($bill->amount, 2) : '—' }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if ($bill->bill_image_url)
                                        <button @click="modalImageUrl = '{{ $bill->bill_image_url }}'; modalTitle = 'Bill Receipt ({{ $bill->bill_number }})'; imageModalOpen = true" class="group relative" title="View Bill Receipt">
                                            <img src="{{ $bill->bill_image_url }}" alt="Bill" class="w-8 h-8 object-cover rounded-lg border border-slate-200 dark:border-slate-700 shadow-2xs group-hover:scale-110 transition">
                                        </button>
                                    @endif
                                    @if ($bill->slip_image_url)
                                        <button @click="modalImageUrl = '{{ $bill->slip_image_url }}'; modalTitle = 'Slip Proof ({{ $bill->bill_number }})'; imageModalOpen = true" class="group relative" title="View Slip Proof">
                                            <img src="{{ $bill->slip_image_url }}" alt="Slip" class="w-8 h-8 object-cover rounded-lg border border-slate-200 dark:border-slate-700 shadow-2xs group-hover:scale-110 transition">
                                        </button>
                                    @endif
                                    @if (!$bill->bill_image_url && !$bill->slip_image_url)
                                        <span class="text-slate-400 text-[10px] italic">—</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate text-[11px]">
                                {{ $bill->remarks ?: '—' }}
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.bills.show', $bill) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Bill">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    @can('bills.edit')
                                        <a href="{{ route('admin.bills.edit', $bill) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Bill">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endcan

                                    @can('bills.delete')
                                        <form method="POST" action="{{ route('admin.bills.destroy', $bill) }}" onsubmit="return confirm('Are you sure you want to delete Bill {{ $bill->bill_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete Bill">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="font-bold text-slate-700 dark:text-slate-300">No bills found</p>
                                    <p class="text-xs text-slate-400">Upload your first bill receipt or adjust your search filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bills->hasPages())
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800">
                {{ $bills->links() }}
            </div>
        @endif
    </div>

    <!-- Image Lightbox Modal -->
    <div x-show="imageModalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="imageModalOpen = false" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-2xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm" x-text="modalTitle"></h3>
                <button @click="imageModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">✕</button>
            </div>
            <div class="flex justify-center p-2 bg-slate-950/40 rounded-2xl">
                <img :src="modalImageUrl" alt="Bill Full Image" class="max-h-[70vh] object-contain rounded-xl">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <a :href="modalImageUrl" target="_blank" download class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500">Download Image</a>
                <button @click="imageModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
