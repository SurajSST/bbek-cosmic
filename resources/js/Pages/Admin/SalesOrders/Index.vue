<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    salesOrders: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: '', sort: 'created_at', dir: 'desc' })
    }
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const activeImageModal = ref(null);

function handleFilter() {
    router.get('/admin/sales-orders', {
        search: search.value,
        status: status.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilter() {
    search.value = '';
    status.value = '';
    handleFilter();
}

function calculateOrderTotal(order) {
    if (!order.items || order.items.length === 0) return 0;
    return order.items.reduce((sum, item) => sum + Number(item.total_price || (item.quantity * item.unit_price)), 0);
}

function formatCurrency(amount) {
    return 'Rs. ' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function deleteOrder(id, soNumber) {
    if (confirm(`Are you sure you want to delete Sales Order #${soNumber}?`)) {
        router.delete(`/admin/sales-orders/${id}`);
    }
}
</script>

<template>
    <AuthenticatedLayout title="Sales Orders">
        <Head title="Sales Orders" />

        <div class="space-y-6">
            
            <!-- Top Controls Bar -->
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                
                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 max-w-2xl">
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            v-model="search" 
                            @keyup.enter="handleFilter"
                            placeholder="Search SO #, customer, origin..." 
                            class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <select 
                        v-model="status" 
                        @change="handleFilter"
                        class="px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="billed">Billed</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <button 
                        @click="handleFilter"
                        class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition"
                    >
                        Filter
                    </button>

                    <button 
                        v-if="search || status"
                        @click="resetFilter"
                        class="px-3 py-2.5 rounded-2xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-semibold transition"
                    >
                        Reset
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2.5 shrink-0">
                    <Link href="/admin/sales-orders/bulk-upload" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <span>Bulk Import</span>
                    </Link>

                    <Link href="/admin/sales-orders/create" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition hover:scale-105 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                        <span>New Order</span>
                    </Link>
                </div>

            </div>

            <!-- Table Card -->
            <div class="glass-card rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800/80">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                                <th class="px-5 py-3.5">SO #</th>
                                <th class="px-5 py-3.5">Billed To (Customer)</th>
                                <th class="px-5 py-3.5">Origin (SO From)</th>
                                <th class="px-5 py-3.5">Items</th>
                                <th class="px-5 py-3.5">Total Amount</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5">Attachments</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <tr v-for="order in salesOrders.data" :key="order.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                
                                <td class="px-5 py-4">
                                    <Link :href="`/admin/sales-orders/${order.id}`" class="font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        #{{ order.so_number }}
                                    </Link>
                                    <span class="block text-[10px] text-slate-400 font-mono">
                                        {{ order.created_at ? new Date(order.created_at).toLocaleDateString() : '' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ order.billed_to }}</span>
                                    <span class="text-[10px] text-slate-400" v-if="order.billed_from">From: {{ order.billed_from }}</span>
                                </td>

                                <td class="px-5 py-4 font-medium text-slate-600 dark:text-slate-300">
                                    {{ order.so_from || '—' }}
                                </td>

                                <td class="px-5 py-4 font-mono">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold">
                                        {{ order.items?.length || 0 }} SKU(s)
                                    </span>
                                </td>

                                <td class="px-5 py-4 font-mono font-black text-slate-900 dark:text-white">
                                    {{ formatCurrency(calculateOrderTotal(order)) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider"
                                        :class="{
                                            'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20': order.billed_status === 'pending',
                                            'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20': order.billed_status === 'billed',
                                            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': order.billed_status === 'paid',
                                            'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20': order.billed_status === 'cancelled'
                                        }">
                                        {{ order.billed_status }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <button v-if="order.bill_image" @click="activeImageModal = `/storage/${order.bill_image}`" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-xs" title="View Bill Image">
                                            🧾
                                        </button>
                                        <button v-if="order.slip_image" @click="activeImageModal = `/storage/${order.slip_image}`" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-xs" title="View Delivery Slip">
                                            📄
                                        </button>
                                        <span v-if="!order.bill_image && !order.slip_image" class="text-slate-300 dark:text-slate-700 text-xs">—</span>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/admin/sales-orders/${order.id}`" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-indigo-600 transition" title="View Order">
                                            👁️
                                        </Link>
                                        <Link :href="`/admin/sales-orders/${order.id}/edit`" class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-950 text-amber-600 dark:text-amber-400 transition" title="Edit Order">
                                            ✏️
                                        </Link>
                                        <button @click="deleteOrder(order.id, order.so_number)" class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950 text-rose-600 dark:text-rose-400 transition" title="Delete Order">
                                            🗑️
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr v-if="!salesOrders.data || salesOrders.data.length === 0">
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-xs">
                                    No sales orders found matching criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="salesOrders.links && salesOrders.links.length > 3" class="px-5 py-3.5 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-[11px] text-slate-500">
                        Showing page {{ salesOrders.current_page }} of {{ salesOrders.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in salesOrders.links" :key="i">
                            <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded-xl text-xs font-semibold transition"
                                :class="link.active ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                                v-html="link.label">
                            </Link>
                            <span v-else class="px-2 py-1 text-slate-300 dark:text-slate-700 text-xs" v-html="link.label"></span>
                        </template>
                    </div>
                </div>

            </div>

        </div>

        <!-- Image Lightbox Modal -->
        <div v-if="activeImageModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4" @click.self="activeImageModal = null">
            <div class="relative max-w-2xl max-h-[85vh] bg-slate-900 rounded-3xl overflow-hidden p-2 border border-slate-800 shadow-2xl">
                <button @click="activeImageModal = null" class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full bg-slate-950/70 text-white flex items-center justify-center hover:bg-slate-900 text-xs">
                    ✕
                </button>
                <img :src="activeImageModal" class="w-full h-auto max-h-[80vh] object-contain rounded-2xl" alt="Attachment Preview" />
            </div>
        </div>

    </AuthenticatedLayout>
</template>
