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

const form = useForm({
    _method: 'PUT',
    so_number: props.salesOrder.so_number,
    so_from: props.salesOrder.so_from || '',
    billed_from: props.salesOrder.billed_from || '',
    billed_to: props.salesOrder.billed_to,
    billed_status: props.salesOrder.billed_status,
    bill_no: props.salesOrder.bill_no || '',
    bill_image: null,
    slip_image: null,
    remarks: props.salesOrder.remarks || '',
    description: props.salesOrder.description || '',
    items: (props.salesOrder.items && props.salesOrder.items.length > 0) ? props.salesOrder.items.map(item => ({
        id: item.id,
        product_name: item.product_name,
        quantity: item.quantity,
        unit_price: item.unit_price,
        remarks: item.remarks || ''
    })) : [
        { product_name: '', quantity: 1, unit_price: 0, remarks: '' }
    ]
});

const billImagePreview = ref(props.salesOrder.bill_image ? `/storage/${props.salesOrder.bill_image}` : null);
const slipImagePreview = ref(props.salesOrder.slip_image ? `/storage/${props.salesOrder.slip_image}` : null);

const billCameraInput = ref(null);
const billGalleryInput = ref(null);
const slipCameraInput = ref(null);
const slipGalleryInput = ref(null);

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
    form.post(`/admin/sales-orders/${props.salesOrder.id}`);
}
</script>

<template>
    <AuthenticatedLayout :title="`Edit Order #${salesOrder.so_number}`">
        <Head :title="`Edit Order #${salesOrder.so_number}`" />

        <div class="max-w-5xl mx-auto space-y-6">
            
            <!-- Header Bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/sales-orders" class="text-slate-400 hover:text-indigo-600 transition">Sales Orders</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Edit #{{ salesOrder.so_number }}</span>
                </div>
                <Link :href="`/admin/sales-orders/${salesOrder.id}`" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; View Details
                </Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- Section 1: Order Metadata -->
                <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span>📦</span> Order Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                SO Number *
                            </label>
                            <input 
                                type="text" 
                                v-model="form.so_number" 
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="form.errors.so_number" class="text-xs text-rose-500 mt-1">{{ form.errors.so_number }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                SO From (Origin / Salesperson) *
                            </label>
                            <input 
                                type="text" 
                                v-model="form.so_from" 
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="form.errors.so_from" class="text-xs text-rose-500 mt-1">{{ form.errors.so_from }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Billed To (Customer / Entity) *
                            </label>
                            <input 
                                type="text" 
                                v-model="form.billed_to" 
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="form.errors.billed_to" class="text-xs text-rose-500 mt-1">{{ form.errors.billed_to }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Billed From
                            </label>
                            <input 
                                type="text" 
                                v-model="form.billed_from" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Billed Status *
                            </label>
                            <select 
                                v-model="form.billed_status" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="pending">Pending</option>
                                <option value="billed">Billed</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Associated Bill # (Optional)
                            </label>
                            <input 
                                type="text" 
                                v-model="form.bill_no" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Order Items & Pricing -->
                <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading flex items-center gap-2">
                                <span>🛍️</span> Products & Line Items
                            </h3>
                        </div>
                        <button 
                            type="button" 
                            @click="addItem" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition"
                        >
                            + Add Line Item
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div 
                            v-for="(item, index) in form.items" 
                            :key="index" 
                            class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end"
                        >
                            <div class="sm:col-span-5">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                    Product Name *
                                </label>
                                <input 
                                    type="text" 
                                    v-model="item.product_name" 
                                    required 
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                    Qty *
                                </label>
                                <input 
                                    type="number" 
                                    v-model="item.quantity" 
                                    min="1" 
                                    required 
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                    Unit Price (Rs.) *
                                </label>
                                <input 
                                    type="number" 
                                    v-model="item.unit_price" 
                                    min="0" 
                                    step="0.01" 
                                    required 
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Subtotal</span>
                                <div class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ formatCurrency(item.quantity * item.unit_price) }}
                                </div>
                            </div>

                            <div class="sm:col-span-1 text-right">
                                <button 
                                    type="button" 
                                    @click="removeItem(index)" 
                                    :disabled="form.items.length === 1"
                                    class="p-2 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/60 text-rose-500 hover:text-rose-700 transition disabled:opacity-30"
                                    title="Remove item"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 flex items-center justify-between">
                        <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300">Total Items: {{ form.items.length }}</span>
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
                            Replace Attachments
                        </h3>

                        <!-- Bill Image -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider font-mono">
                                Bill / Invoice Image
                            </label>

                            <input type="file" ref="billCameraInput" accept="image/*" capture="environment" @change="handleBillImage" class="hidden" />
                            <input type="file" ref="billGalleryInput" accept="image/*" @change="handleBillImage" class="hidden" />

                            <div v-if="billImagePreview" class="relative w-full h-40 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-900 mb-2">
                                <img :src="billImagePreview" class="w-full h-full object-contain" alt="Bill Preview" />
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="billCameraInput.click()" class="py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <span>📸</span> <span>Open Camera</span>
                                </button>
                                <button type="button" @click="billGalleryInput.click()" class="py-2 px-3 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                    <span>📁</span> <span>Photo Library</span>
                                </button>
                            </div>
                        </div>

                        <!-- Slip Image -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider font-mono">
                                Delivery Slip Image
                            </label>

                            <input type="file" ref="slipCameraInput" accept="image/*" capture="environment" @change="handleSlipImage" class="hidden" />
                            <input type="file" ref="slipGalleryInput" accept="image/*" @change="handleSlipImage" class="hidden" />

                            <div v-if="slipImagePreview" class="relative w-full h-40 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-900 mb-2">
                                <img :src="slipImagePreview" class="w-full h-full object-contain" alt="Slip Preview" />
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="slipCameraInput.click()" class="py-2 px-3 rounded-xl bg-indigo-600/90 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <span>📸</span> <span>Open Camera</span>
                                </button>
                                <button type="button" @click="slipGalleryInput.click()" class="py-2 px-3 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                    <span>📁</span> <span>Photo Library</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">
                            Remarks & Instructions
                        </h3>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Remarks
                            </label>
                            <textarea v-model="form.remarks" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Detailed Description
                            </label>
                            <textarea v-model="form.description" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold"></textarea>
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/60 dark:border-slate-800">
                    <Link :href="`/admin/sales-orders/${salesOrder.id}`" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50">
                        Update Sales Order &rarr;
                    </button>
                </div>

            </form>

        </div>
    </AuthenticatedLayout>
</template>
