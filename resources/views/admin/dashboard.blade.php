@extends('layouts.app')

@section('header', 'System Overview')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Operational Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-white dark:bg-[#0c121e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <h2 class="text-lg sm:text-xl font-bold font-heading text-slate-900 dark:text-white">
                    Welcome back, {{ Auth::user()->name }}
                </h2>
                <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200/60 dark:border-indigo-800/60">
                    {{ Auth::user()->roles->first()?->name ?? 'Administrator' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Cosmic billing engine is operational. Monitor sales orders, receipts, and system integrity.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('sales-orders.create')
                <a href="{{ route('admin.sales-orders.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                    <span>New Order</span>
                </a>
            @endcan

            @can('bills.create')
                <a href="{{ route('admin.bills.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Upload Bill</span>
                </a>
            @endcan

            @can('sales-orders.create')
                <a href="{{ route('admin.sales-orders.bulk-upload') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>Bulk CSV</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- 4 Primary KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Sales Revenue -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0c121e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">Billed Volume</span>
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-white">
                    NRs. {{ number_format($stats['total_revenue'], 0) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">● Active</span>
                    <span>across {{ number_format($stats['total_sales_orders']) }} orders</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Sales Orders & Status Breakdown -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0c121e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">Sales Orders</span>
                <span class="p-2 rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 border border-violet-200/50 dark:border-violet-800/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </span>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white">
                    {{ number_format($stats['total_sales_orders']) }}
                </div>
                <div class="flex items-center gap-2 mt-1 text-[11px] font-medium">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $stats['paid_orders'] }} Paid</span>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $stats['billed_orders'] }} Billed</span>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $stats['pending_orders'] }} Pending</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Uploaded Bills & Receipts -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0c121e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">Bills & SO Slips</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/50 dark:border-blue-800/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white">
                    {{ number_format($stats['total_bills'] + $stats['total_upload_sos']) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                    <span>{{ $stats['total_bills'] }} Vendor Bills</span>
                    <span>•</span>
                    <span>{{ $stats['total_upload_sos'] }} SO Slips</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Access Control & Users -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0c121e] border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">Access & RBAC</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </span>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white">
                    {{ number_format($stats['active_users']) }} / {{ number_format($stats['total_users']) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                    <span>{{ $stats['total_roles'] }} Roles</span>
                    <span>•</span>
                    <span>{{ $stats['total_permissions'] }} Spatie Perms</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 2-Column Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column (2 Cols): Recent Sales Orders Table -->
        <div class="lg:col-span-2 bg-white dark:bg-[#0c121e] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                            Recent Sales Orders
                        </h3>
                        <p class="text-xs text-slate-400">Latest active client orders and billing summaries</p>
                    </div>
                    <a href="{{ route('admin.sales-orders.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <span>View All Orders</span>
                        <span>→</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-200/70 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                                <th class="py-3 px-4 font-semibold">SO Number</th>
                                <th class="py-3 px-4 font-semibold">Client / To</th>
                                <th class="py-3 px-4 font-semibold text-center">Status</th>
                                <th class="py-3 px-4 font-semibold text-right">Amount</th>
                                <th class="py-3 px-4 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse ($recentOrders as $order)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/40 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                        <a href="{{ route('admin.sales-orders.show', $order) }}" class="hover:underline">
                                            {{ $order->so_number }}
                                        </a>
                                        <span class="block text-[10px] text-slate-400 font-sans font-normal">{{ $order->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-slate-900 dark:text-slate-100">
                                        {{ $order->billed_to }}
                                        <span class="block text-[10px] text-slate-400">From: {{ $order->so_from }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @php
                                            $statusStyle = match($order->billed_status) {
                                                'paid' => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                                'billed' => 'bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                                'pending' => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                                'cancelled' => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                                default => 'bg-slate-50 text-slate-700 border-slate-200'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusStyle }}">
                                            {{ $order->billed_status }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        NRs. {{ number_format($order->items->sum('total_price'), 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('admin.sales-orders.show', $order) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-semibold text-xs transition">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                        No sales orders created yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50/50 dark:bg-slate-900/30 border-t border-slate-100 dark:border-slate-800 text-right">
                <a href="{{ route('admin.sales-orders.create') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                    + Create Sales Order
                </a>
            </div>
        </div>

        <!-- Right Column (1 Col): System Activity & Quick Actions -->
        <div class="space-y-6">
            
            <!-- Quick Actions Panel -->
            <div class="bg-white dark:bg-[#0c121e] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 p-5 shadow-xs space-y-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider font-heading">
                    Quick Operations
                </h3>
                <div class="grid grid-cols-2 gap-2.5">
                    @can('sales-orders.create')
                        <a href="{{ route('admin.sales-orders.create') }}" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200/80 dark:border-slate-800 text-left transition group">
                            <span class="block text-indigo-600 dark:text-indigo-400 font-bold text-xs group-hover:translate-x-0.5 transition-transform">➕ New Order</span>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Create Sales Order</span>
                        </a>
                    @endcan

                    @can('bills.create')
                        <a href="{{ route('admin.bills.create') }}" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200/80 dark:border-slate-800 text-left transition group">
                            <span class="block text-indigo-600 dark:text-indigo-400 font-bold text-xs group-hover:translate-x-0.5 transition-transform">📷 Upload Bill</span>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Capture/Attach receipt</span>
                        </a>
                    @endcan

                    @can('upload-sos.create')
                        <a href="{{ route('admin.upload-sos.create') }}" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200/80 dark:border-slate-800 text-left transition group">
                            <span class="block text-indigo-600 dark:text-indigo-400 font-bold text-xs group-hover:translate-x-0.5 transition-transform">🖼️ Upload SO</span>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Snap SO document</span>
                        </a>
                    @endcan

                    @can('sales-orders.create')
                        <a href="{{ route('admin.sales-orders.bulk-upload') }}" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200/80 dark:border-slate-800 text-left transition group">
                            <span class="block text-indigo-600 dark:text-indigo-400 font-bold text-xs group-hover:translate-x-0.5 transition-transform">📊 Bulk CSV</span>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Spreadsheet import</span>
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Recent System Activity Audit Trail -->
            <div class="bg-white dark:bg-[#0c121e] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2.5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider font-heading">
                        Audit Trail
                    </h3>
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        All Logs →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($recentActivities as $log)
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 shrink-0 mt-1.5"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-slate-800 dark:text-slate-200 font-medium truncate">{{ $log->description }}</p>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                                    <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $log->user_name }}</span>
                                    <span>•</span>
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">No activity recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
