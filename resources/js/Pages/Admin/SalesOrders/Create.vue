<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    so_number: 'SO-' + Math.floor(100000 + Math.random() * 900000),
    so_from: '',
    billed_from: '',
    billed_to: '',
    billed_status: 'pending',
    bill_no: '',
    bill_image: null,
    slip_image: null,
    remarks: '',
    description: '',
    items: [
        { product_name: '', quantity: 1, unit_price: 0, remarks: '' }
    ]
});

const billImagePreview = ref(null);
const slipImagePreview = ref(null);

function addItem() {
    form.items.push({
        product_name: '',
        quantity: 1,
        unit_price: 0,
        remarks: ''
    });
}

function removeItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function handleBillImage(e) {
    const file = e.target.files[0];
    if (file) {
        form.bill_image = file;
        const reader = new FileReader();
        reader.onload = (ev) => { billImagePreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
}

function handleSlipImage(e) {
    const file = e.target.files[0];
    if (file) {
        form.slip_image = file;
        const reader = new FileReader();
        reader.onload = (ev) => { slipImagePreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
}

const grandTotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (Number(item.quantity || 0) * Number(item.unit_price || 0));
    }, 0);
});

function formatCurrency(amount) {
    return 'Rs. ' + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function submit() {
    form.post('/admin/sales-orders');
}
</script>

<template>
    <AuthenticatedLayout title="Create Sales Order">
        <Head title="Create Sales Order" />

        <div class="max-w-5xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/sales-orders" class="text-slate-400 hover:text-indigo-600 transition">Sales Orders</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Create Order</span>
                </div>
                <Link href="/admin/sales-orders" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; Back to List
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- Order Header Details -->
                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading flex items-center gap-2">
                        <span>📦</span> Order Information & Routing
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                SO Number *
                            </label>
                            <input type="text" v-model="form.so_number" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.so_number" class="text-[11px] text-rose-500 mt-1">{{ form.errors.so_number }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                SO From (Origin / Salesperson) *
                            </label>
                            <input type="text" v-model="form.so_from" required placeholder="e.g. Kathmandu Warehouse" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.so_from" class="text-[11px] text-rose-500 mt-1">{{ form.errors.so_from }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Status *
                            </label>
                            <select v-model="form.billed_status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="pending">Pending</option>
                                <option value="billed">Billed</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Billed To (Customer Name / Entity) *
                            </label>
                            <input type="text" v-model="form.billed_to" required placeholder="e.g. Acme Corporation" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.billed_to" class="text-[11px] text-rose-500 mt-1">{{ form.errors.billed_to }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Billed From (Billing Entity)
                            </label>
                            <input type="text" v-model="form.billed_from" placeholder="e.g. Cosmic Head Office" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Official Bill / Invoice No.
                            </label>
                            <input type="text" v-model="form.bill_no" placeholder="e.g. INV-2026-009" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading flex items-center gap-2">
                            <span>📋</span> Line Items
                        </h3>
                        <button type="button" @click="addItem" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50 text-xs font-bold hover:bg-indigo-100 transition">
                            + Add Item
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(item, idx) in form.items" :key="idx" class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            
                            <div class="sm:col-span-5">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Product Name *</label>
                                <input type="text" v-model="item.product_name" required placeholder="Item description" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quantity *</label>
                                <input type="number" v-model="item.quantity" min="1" required class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Unit Price *</label>
                                <input type="number" v-model="item.unit_price" min="0" step="0.01" required class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold" />
                            </div>

                            <div class="sm:col-span-2 text-right sm:text-left">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Total</label>
                                <span class="text-xs font-black font-mono text-indigo-600 dark:text-indigo-400">
                                    {{ formatCurrency(Number(item.quantity || 0) * Number(item.unit_price || 0)) }}
                                </span>
                            </div>

                            <div class="sm:col-span-1 flex justify-end">
                                <button type="button" @click="removeItem(idx)" :disabled="form.items.length <= 1" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 disabled:opacity-30 transition">
                                    🗑️
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Grand Total Summary -->
                    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="text-right space-y-1">
                            <span class="text-xs text-slate-500">Order Grand Total:</span>
                            <div class="text-2xl font-black font-heading text-indigo-600 dark:text-indigo-400">
                                {{ formatCurrency(grandTotal) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments & Notes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                            Bill & Delivery Slip Attachments
                        </h3>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Bill / Invoice Image
                            </label>
                            <input type="file" accept="image/*" @change="handleBillImage" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700" />
                            <div v-if="billImagePreview" class="mt-2 w-28 h-28 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                <img :src="billImagePreview" class="w-full h-full object-cover" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Delivery Slip Image
                            </label>
                            <input type="file" accept="image/*" @change="handleSlipImage" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700" />
                            <div v-if="slipImagePreview" class="mt-2 w-28 h-28 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                <img :src="slipImagePreview" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                            Remarks & Instructions
                        </h3>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Remarks
                            </label>
                            <textarea v-model="form.remarks" rows="2" placeholder="Internal remarks..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Detailed Description
                            </label>
                            <textarea v-model="form.description" rows="2" placeholder="Order or delivery notes..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold"></textarea>
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/60 dark:border-slate-800">
                    <Link href="/admin/sales-orders" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50">
                        Create Sales Order &rarr;
                    </button>
                </div>

            </form>

        </div>
    </AuthenticatedLayout>
</template>
