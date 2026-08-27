<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    salesOrder: {
        type: Object,
        required: true
    }
});

const returnModalOpen = ref(false);
const activeItem = ref(null);
const activePreviewModal = ref(null);
const returnForm = useForm({
    returned_quantity: 1,
    remarks: ''
});

function openReturnModal(item) {
    activeItem.value = item;
    returnForm.returned_quantity = 1;
    returnForm.remarks = '';
    returnModalOpen.value = true;
}

function submitReturn() {
    if (!activeItem.value) return;
    returnForm.post(`/admin/sales-orders/items/${activeItem.value.id}/return`, {
        preserveScroll: true,
        onSuccess: () => {
            returnModalOpen.value = false;
            activeItem.value = null;
        }
    });
}

const grandTotal = computed(() => {
    if (!props.salesOrder.items) return 0;
    return props.salesOrder.items.reduce((sum, item) => {
        return sum + (Number(item.quantity || 0) * Number(item.unit_price || 0));
    }, 0);
});

function formatCurrency(amount) {
    return 'Rs. ' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
    <AuthenticatedLayout :title="`Order #${salesOrder.so_number}`">
        <Head :title="`Order #${salesOrder.so_number}`" />

        <div class="max-w-5xl mx-auto space-y-6">
            
            <!-- Header Card -->
            <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
                            #{{ salesOrder.so_number }}
                        </h2>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider"
                            :class="{
                                'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20': salesOrder.billed_status === 'pending',
                                'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20': salesOrder.billed_status === 'billed',
                                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': salesOrder.billed_status === 'paid',
                                'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20': salesOrder.billed_status === 'cancelled'
                            }">
                            {{ salesOrder.billed_status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Created on {{ new Date(salesOrder.created_at).toLocaleDateString() }} • Recorded by {{ salesOrder.creator?.name || 'System' }}
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link :href="`/admin/sales-orders/${salesOrder.id}/edit`" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition">
                        Edit Order
                    </Link>
                    <Link href="/admin/sales-orders" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                        &larr; Back
                    </Link>
                </div>
            </div>

            <!-- Routing & Financial Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider text-slate-400 font-mono">
                        Order & Routing Information
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">SO From (Origin)</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ salesOrder.so_from || '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Billed To (Customer)</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ salesOrder.billed_to }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Billed From</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ salesOrder.billed_from || '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500">Official Bill / Invoice No.</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ salesOrder.bill_no || '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider text-slate-400 font-mono">
                        Financial Overview
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Total Items Count</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ salesOrder.items?.length || 0 }} SKU(s)</span>
                        </div>
                        <div class="flex justify-between py-2 items-center">
                            <span class="font-bold text-slate-900 dark:text-white text-sm">Order Grand Total</span>
                            <span class="font-heading font-black text-indigo-600 dark:text-indigo-400 text-xl">{{ formatCurrency(grandTotal) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Items Table -->
            <div class="glass-card rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800/80 overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                        Order Line Items ({{ salesOrder.items?.length || 0 }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800/80">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                                <th class="px-6 py-3.5">Product Name</th>
                                <th class="px-6 py-3.5">Quantity</th>
                                <th class="px-6 py-3.5">Unit Price</th>
                                <th class="px-6 py-3.5">Total Price</th>
                                <th class="px-6 py-3.5">Return Status</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <tr v-for="item in salesOrder.items" :key="item.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    {{ item.product_name }}
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-600 dark:text-slate-400">
                                    {{ formatCurrency(item.unit_price) }}
                                </td>
                                <td class="px-6 py-4 font-mono font-black text-indigo-600 dark:text-indigo-400">
                                    {{ formatCurrency(item.total_price || (item.quantity * item.unit_price)) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                        :class="{
                                            'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400': item.return_status === 'not_returned',
                                            'bg-amber-500/10 text-amber-600 dark:text-amber-400': item.return_status === 'partially_returned',
                                            'bg-rose-500/10 text-rose-600 dark:text-rose-400': item.return_status === 'returned'
                                        }">
                                        {{ item.return_status || 'not_returned' }}
                                        <span v-if="item.returned_quantity > 0">({{ item.returned_quantity }})</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        v-if="item.return_status !== 'returned'" 
                                        @click="openReturnModal(item)" 
                                        class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-bold hover:bg-indigo-100 transition text-[11px]"
                                    >
                                        Process Return
                                    </button>
                                    <span v-else class="text-slate-400 text-[11px]">Fully Returned</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Attachments & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                        Attached Documents
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-if="salesOrder.bill_image" class="space-y-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400">Bill Image</span>
                            <button type="button" @click="activePreviewModal = `/storage/${salesOrder.bill_image}`" class="block w-full rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 group relative cursor-zoom-in">
                                <img :src="`/storage/${salesOrder.bill_image}`" class="w-full h-32 object-cover" />
                                <span class="absolute inset-0 bg-slate-950/0 group-hover:bg-slate-950/30 flex items-center justify-center text-white text-[11px] font-bold opacity-0 group-hover:opacity-100 transition">🔍 View Full Size</span>
                            </button>
                        </div>
                        <div v-if="salesOrder.slip_image" class="space-y-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400">Delivery Slip</span>
                            <button type="button" @click="activePreviewModal = `/storage/${salesOrder.slip_image}`" class="block w-full rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 group relative cursor-zoom-in">
                                <img :src="`/storage/${salesOrder.slip_image}`" class="w-full h-32 object-cover" />
                                <span class="absolute inset-0 bg-slate-950/0 group-hover:bg-slate-950/30 flex items-center justify-center text-white text-[11px] font-bold opacity-0 group-hover:opacity-100 transition">🔍 View Full Size</span>
                            </button>
                        </div>
                    </div>
                    <p v-if="!salesOrder.bill_image && !salesOrder.slip_image" class="text-xs text-slate-400">
                        No attachments uploaded for this order.
                    </p>
                </div>

                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                        Remarks & Description
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Remarks</span>
                            <p class="text-slate-700 dark:text-slate-300">{{ salesOrder.remarks || 'None' }}</p>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Description</span>
                            <p class="text-slate-700 dark:text-slate-300">{{ salesOrder.description || 'None' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Item Return Modal -->
        <div v-if="returnModalOpen" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">
                        Process Return: {{ activeItem?.product_name }}
                    </h3>
                    <button @click="returnModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs">✕</button>
                </div>

                <form @submit.prevent="submitReturn" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">
                            Returned Quantity (Max: {{ (activeItem?.quantity || 1) - (activeItem?.returned_quantity || 0) }})
                        </label>
                        <input type="number" v-model="returnForm.returned_quantity" min="1" :max="(activeItem?.quantity || 1) - (activeItem?.returned_quantity || 0)" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">
                            Return Remarks
                        </label>
                        <textarea v-model="returnForm.remarks" rows="2" placeholder="Reason for return or item condition..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="returnModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold">Cancel</button>
                        <button type="submit" :disabled="returnForm.processing" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition disabled:opacity-50">
                            Confirm Return
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Image Lightbox Modal -->
        <div v-if="activePreviewModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4" @click.self="activePreviewModal = null">
            <div class="relative max-w-2xl max-h-[85vh] bg-slate-900 rounded-3xl overflow-hidden p-2 border border-slate-800 shadow-2xl">
                <button @click="activePreviewModal = null" class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full bg-slate-950/70 text-white flex items-center justify-center hover:bg-slate-900 text-xs">
                    ✕
                </button>
                <img :src="activePreviewModal" class="w-full h-auto max-h-[80vh] object-contain rounded-2xl" alt="Attachment Preview" />
            </div>
        </div>

    </AuthenticatedLayout>
</template>
