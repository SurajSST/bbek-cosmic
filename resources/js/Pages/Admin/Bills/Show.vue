<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    bill: {
        type: Object,
        required: true
    }
});

const activePreviewModal = ref(null);

function formatCurrency(amount) {
    return 'Rs. ' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
    <AuthenticatedLayout :title="`Bill #${bill.bill_number}`">
        <Head :title="`Bill #${bill.bill_number}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
                            #{{ bill.bill_number }}
                        </h2>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider"
                            :class="{
                                'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20': bill.status === 'pending',
                                'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20': bill.status === 'billed',
                                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': bill.status === 'paid',
                                'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20': bill.status === 'cancelled'
                            }">
                            {{ bill.status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Recorded on {{ new Date(bill.created_at).toLocaleDateString() }} • Recorded by {{ bill.creator?.name || 'System' }}
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link :href="`/admin/bills/${bill.id}/edit`" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition">
                        Edit Bill
                    </Link>
                    <Link href="/admin/bills" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                        &larr; Back
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider text-slate-400 font-mono">
                        Bill Details
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Billed From (Vendor)</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ bill.billed_from }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Billed To (Entity)</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ bill.billed_to }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Remarks</span>
                            <span class="text-slate-700 dark:text-slate-300">{{ bill.remarks || 'None' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Description</span>
                            <span class="text-slate-700 dark:text-slate-300">{{ bill.description || 'None' }}</span>
                        </div>
                        <div class="flex justify-between py-2 items-center">
                            <span class="font-bold text-slate-900 dark:text-white text-sm">Bill Amount</span>
                            <span class="font-heading font-black text-indigo-600 dark:text-indigo-400 text-xl">{{ formatCurrency(bill.amount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider text-slate-400 font-mono">
                        Document Attachments
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div v-if="bill.bill_image" class="space-y-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400">Bill Image</span>
                            <button type="button" @click="activePreviewModal = `/storage/${bill.bill_image}`" class="block w-full rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 group relative cursor-zoom-in">
                                <img :src="`/storage/${bill.bill_image}`" class="w-full h-36 object-cover" alt="Bill Receipt" />
                                <span class="absolute inset-0 bg-slate-950/0 group-hover:bg-slate-950/30 flex items-center justify-center text-white text-[11px] font-bold opacity-0 group-hover:opacity-100 transition">🔍 View Full Size</span>
                            </button>
                        </div>
                        <div v-if="bill.slip_image" class="space-y-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400">Delivery Slip</span>
                            <button type="button" @click="activePreviewModal = `/storage/${bill.slip_image}`" class="block w-full rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 group relative cursor-zoom-in">
                                <img :src="`/storage/${bill.slip_image}`" class="w-full h-36 object-cover" alt="Delivery Slip" />
                                <span class="absolute inset-0 bg-slate-950/0 group-hover:bg-slate-950/30 flex items-center justify-center text-white text-[11px] font-bold opacity-0 group-hover:opacity-100 transition">🔍 View Full Size</span>
                            </button>
                        </div>
                    </div>
                    <p v-if="!bill.bill_image && !bill.slip_image" class="text-xs text-slate-400">No attachments attached.</p>
                </div>

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
