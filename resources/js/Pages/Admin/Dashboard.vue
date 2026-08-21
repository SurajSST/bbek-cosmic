<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    },
    recent_orders: {
        type: Array,
        default: () => []
    },
    recent_activities: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

function calculateOrderTotal(order) {
    if (!order.items || order.items.length === 0) return 0;
    return order.items.reduce((sum, item) => sum + Number(item.total_price || (item.quantity * item.unit_price)), 0);
}

function formatCurrency(amount) {
    return 'Rs. ' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
    <AuthenticatedLayout title="Operational Dashboard">
        <Head title="Dashboard" />

        <!-- Greeting Header -->
        <div class="glass-card rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-xl sm:text-2xl font-black font-heading tracking-tight text-slate-900 dark:text-white">
                        Welcome back, {{ user.name }}
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        Live
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Here is your real-time enterprise overview of sales orders, receipts, and system operations.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <Link href="/admin/sales-orders/create" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>New Order</span>
                </Link>
                <Link href="/admin/sales-orders/bulk-upload" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>Bulk Upload</span>
                </Link>
            </div>
        </div>

        <!-- Metric KPI Cards (4 Column Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Total Revenue -->
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 relative overflow-hidden group hover:border-indigo-500/40 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Total Revenue</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">Rs</span>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight">
                        {{ formatCurrency(stats.revenue) }}
                    </div>
                    <div class="flex items-center gap-1 mt-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                        <span>↑ Active Portfolio</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Sales Orders Breakdown -->
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 relative overflow-hidden group hover:border-indigo-500/40 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Sales Orders</span>
                    <span class="w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center font-bold text-xs">📦</span>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight">
                        {{ stats.total_orders || 0 }}
                    </div>
                    <div class="flex items-center gap-2 mt-1.5 text-[10px] font-bold">
                        <span class="text-amber-500 font-semibold">{{ stats.pending_orders || 0 }} Pending</span>
                        <span class="text-slate-300 dark:text-slate-700">•</span>
                        <span class="text-blue-500 font-semibold">{{ stats.approved_orders || 0 }} Billed</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Receipts & Uploads -->
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 relative overflow-hidden group hover:border-indigo-500/40 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Bills & Uploads</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">📷</span>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight">
                        {{ (stats.total_bills || 0) + (stats.total_uploaded_sos || 0) }}
                    </div>
                    <div class="flex items-center gap-2 mt-1.5 text-[10px] font-bold">
                        <span class="text-slate-500">{{ stats.total_bills || 0 }} Bills</span>
                        <span class="text-slate-300 dark:text-slate-700">•</span>
                        <span class="text-slate-500">{{ stats.total_uploaded_sos || 0 }} Uploaded SOs</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: RBAC & System Users -->
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 relative overflow-hidden group hover:border-indigo-500/40 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">System Accounts</span>
                    <span class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs">👥</span>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight">
                        {{ stats.total_users || 0 }}
                    </div>
                    <div class="flex items-center gap-2 mt-1.5 text-[10px] font-bold">
                        <span class="text-emerald-500 font-semibold">{{ stats.active_users || 0 }} Active</span>
                        <span class="text-slate-300 dark:text-slate-700">•</span>
                        <span class="text-slate-500">{{ stats.total_roles || 0 }} Roles</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Quick Access Action Modules Grid -->
        <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                    Quick Operational Shortcuts
                </h3>
                <span class="text-[11px] text-slate-400 font-mono">Fast Access</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <Link href="/admin/sales-orders/create" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200/60 dark:border-slate-700/60 transition group space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                        ✨
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-indigo-600 transition">Create SO</div>
                        <p class="text-[10px] text-slate-400">Add sales entry</p>
                    </div>
                </Link>

                <Link href="/admin/bills/create" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-slate-200/60 dark:border-slate-700/60 transition group space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                        📷
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-emerald-600 transition">Snap Bill</div>
                        <p class="text-[10px] text-slate-400">Capture receipt</p>
                    </div>
                </Link>

                <Link href="/admin/upload-sos/create" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-slate-200/60 dark:border-slate-700/60 transition group space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                        🖼️
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-amber-600 transition">Upload SO</div>
                        <p class="text-[10px] text-slate-400">Physical slip</p>
                    </div>
                </Link>

                <Link href="/admin/sales-orders/bulk-upload" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 hover:bg-violet-50 dark:hover:bg-violet-950/40 border border-slate-200/60 dark:border-slate-700/60 transition group space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center text-lg">
                        📊
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-violet-600 transition">Bulk Import</div>
                        <p class="text-[10px] text-slate-400">Excel / CSV batch</p>
                    </div>
                </Link>
            </div>
        </div>

        <!-- 2 Column Section: Recent Sales Orders + Recent Activity Stream -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left 2 Cols: Recent Sales Orders -->
            <div class="lg:col-span-2 glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                            Recent Sales Orders
                        </h3>
                    </div>
                    <Link href="/admin/sales-orders" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View All &rarr;
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] font-bold uppercase text-slate-400 border-b border-slate-100 dark:border-slate-800 font-mono">
                                <th class="pb-2.5">SO #</th>
                                <th class="pb-2.5">Billed To (Customer)</th>
                                <th class="pb-2.5">Date</th>
                                <th class="pb-2.5">Amount</th>
                                <th class="pb-2.5">Status</th>
                                <th class="pb-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <tr v-for="order in recent_orders" :key="order.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    <Link :href="`/admin/sales-orders/${order.id}`" class="hover:underline">
                                        #{{ order.so_number }}
                                    </Link>
                                </td>
                                <td class="py-3 font-semibold text-slate-900 dark:text-slate-100">
                                    {{ order.billed_to }}
                                </td>
                                <td class="py-3 text-slate-400 font-mono text-[11px]">
                                    {{ order.created_at ? new Date(order.created_at).toLocaleDateString() : '—' }}
                                </td>
                                <td class="py-3 font-bold font-mono text-slate-900 dark:text-slate-100">
                                    {{ formatCurrency(calculateOrderTotal(order)) }}
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                        :class="{
                                            'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20': order.billed_status === 'pending',
                                            'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20': order.billed_status === 'billed',
                                            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': order.billed_status === 'paid',
                                            'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20': order.billed_status === 'cancelled'
                                        }">
                                        {{ order.billed_status }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <Link :href="`/admin/sales-orders/${order.id}`" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold transition">
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="recent_orders.length === 0">
                                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                                    No recent sales orders found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right 1 Col: Live Activity Logs Feed -->
            <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                            Live Activity Stream
                        </h3>
                    </div>
                    <Link href="/admin/activity-logs" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Logs &rarr;
                    </Link>
                </div>

                <div class="space-y-3">
                    <div v-for="act in recent_activities" :key="act.id" class="p-3 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[130px]">
                                {{ act.user?.name || act.user_name || 'System' }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ act.created_at ? new Date(act.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 font-medium line-clamp-1">
                            {{ act.description }}
                        </p>
                    </div>

                    <div v-if="recent_activities.length === 0" class="py-6 text-center text-slate-400 text-xs">
                        No activity recorded yet.
                    </div>
                </div>
            </div>

        </div>

    </AuthenticatedLayout>
</template>
