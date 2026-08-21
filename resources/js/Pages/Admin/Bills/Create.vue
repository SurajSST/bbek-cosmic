<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    bill_number: 'BILL-' + Math.floor(100000 + Math.random() * 900000),
    billed_from: '',
    billed_to: '',
    status: 'billed',
    amount: '',
    bill_image: null,
    slip_image: null,
    remarks: '',
    description: ''
});

const billImagePreview = ref(null);
const slipImagePreview = ref(null);

const billCameraInput = ref(null);
const billGalleryInput = ref(null);
const slipCameraInput = ref(null);
const slipGalleryInput = ref(null);

function handleBillImage(e) {
    const file = e.target.files[0];
    if (file) {
        form.bill_image = file;
        const reader = new FileReader();
        reader.onload = (ev) => { billImagePreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
}

function removeBillImage() {
    form.bill_image = null;
    billImagePreview.value = null;
    if (billCameraInput.value) billCameraInput.value.value = '';
    if (billGalleryInput.value) billGalleryInput.value.value = '';
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

function removeSlipImage() {
    form.slip_image = null;
    slipImagePreview.value = null;
    if (slipCameraInput.value) slipCameraInput.value.value = '';
    if (slipGalleryInput.value) slipGalleryInput.value.value = '';
}

function submit() {
    form.post('/admin/bills');
}
</script>

<template>
    <AuthenticatedLayout title="Upload Vendor Bill">
        <Head title="Upload Vendor Bill" />

        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/bills" class="text-slate-400 hover:text-indigo-600 transition">Bills</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Upload New Bill</span>
                </div>
                <Link href="/admin/bills" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; Back
                </Link>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-6">
                
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading flex items-center gap-2">
                        <span>📷</span> Bill Document & Vendor Details
                    </h3>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Bill Number *
                            </label>
                            <input type="text" v-model="form.bill_number" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.bill_number" class="text-xs text-rose-500 mt-1">{{ form.errors.bill_number }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Total Amount (Rs.)
                            </label>
                            <input type="number" v-model="form.amount" min="0" step="0.01" placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.amount" class="text-xs text-rose-500 mt-1">{{ form.errors.amount }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Billed From (Vendor / Origin) *
                            </label>
                            <input type="text" v-model="form.billed_from" required placeholder="e.g. Supplier / Vendor Name" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.billed_from" class="text-xs text-rose-500 mt-1">{{ form.errors.billed_from }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Billed To (Client / Entity) *
                            </label>
                            <input type="text" v-model="form.billed_to" required placeholder="e.g. Acme HQ / Branch" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.billed_to" class="text-xs text-rose-500 mt-1">{{ form.errors.billed_to }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                            Status *
                        </label>
                        <select v-model="form.status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="pending">Pending</option>
                            <option value="billed">Billed</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Enhanced Image Uploads (Camera + Gallery) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        
                        <!-- Bill Image -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider font-mono">
                                    Bill / Receipt Image *
                                </label>
                                <span v-if="form.bill_image" class="text-[10px] font-bold text-emerald-500">✓ Image Attached</span>
                            </div>

                            <!-- Hidden native file inputs -->
                            <input type="file" ref="billCameraInput" accept="image/*" capture="environment" @change="handleBillImage" class="hidden" />
                            <input type="file" ref="billGalleryInput" accept="image/*" @change="handleBillImage" class="hidden" />

                            <div v-if="!billImagePreview" class="grid grid-cols-2 gap-2">
                                <button type="button" @click="billCameraInput.click()" class="p-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex flex-col items-center justify-center gap-1 transition active:scale-95 shadow-sm shadow-indigo-600/20">
                                    <span class="text-lg">📸</span>
                                    <span class="text-xs font-bold">Take Photo</span>
                                    <span class="text-[9px] opacity-80">Open Camera</span>
                                </button>
                                <button type="button" @click="billGalleryInput.click()" class="p-3.5 rounded-xl bg-slate-200/80 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 flex flex-col items-center justify-center gap-1 transition active:scale-95">
                                    <span class="text-lg">📁</span>
                                    <span class="text-xs font-bold">Choose File</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">Photo Library</span>
                                </button>
                            </div>

                            <div v-else class="space-y-2">
                                <div class="relative w-full h-44 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-900">
                                    <img :src="billImagePreview" class="w-full h-full object-contain" alt="Bill Preview" />
                                    <button type="button" @click="removeBillImage" class="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600 text-white text-xs hover:bg-rose-500 shadow-md transition" title="Remove Photo">
                                        🗑️ Remove
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="billCameraInput.click()" class="flex-1 py-1.5 px-3 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition">
                                        📸 Retake
                                    </button>
                                    <button type="button" @click="billGalleryInput.click()" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                                        📁 Choose Other
                                    </button>
                                </div>
                            </div>

                            <p v-if="form.errors.bill_image" class="text-xs text-rose-500 font-semibold">{{ form.errors.bill_image }}</p>
                        </div>

                        <!-- Delivery Slip -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider font-mono">
                                    Delivery Slip (Optional)
                                </label>
                                <span v-if="form.slip_image" class="text-[10px] font-bold text-emerald-500">✓ Slip Attached</span>
                            </div>

                            <!-- Hidden native file inputs -->
                            <input type="file" ref="slipCameraInput" accept="image/*" capture="environment" @change="handleSlipImage" class="hidden" />
                            <input type="file" ref="slipGalleryInput" accept="image/*" @change="handleSlipImage" class="hidden" />

                            <div v-if="!slipImagePreview" class="grid grid-cols-2 gap-2">
                                <button type="button" @click="slipCameraInput.click()" class="p-3.5 rounded-xl bg-indigo-600/90 hover:bg-indigo-500 text-white flex flex-col items-center justify-center gap-1 transition active:scale-95 shadow-sm shadow-indigo-600/20">
                                    <span class="text-lg">📸</span>
                                    <span class="text-xs font-bold">Take Photo</span>
                                    <span class="text-[9px] opacity-80">Open Camera</span>
                                </button>
                                <button type="button" @click="slipGalleryInput.click()" class="p-3.5 rounded-xl bg-slate-200/80 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 flex flex-col items-center justify-center gap-1 transition active:scale-95">
                                    <span class="text-lg">📁</span>
                                    <span class="text-xs font-bold">Choose File</span>
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">Photo Library</span>
                                </button>
                            </div>

                            <div v-else class="space-y-2">
                                <div class="relative w-full h-44 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-900">
                                    <img :src="slipImagePreview" class="w-full h-full object-contain" alt="Slip Preview" />
                                    <button type="button" @click="removeSlipImage" class="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600 text-white text-xs hover:bg-rose-500 shadow-md transition" title="Remove Photo">
                                        🗑️ Remove
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="slipCameraInput.click()" class="flex-1 py-1.5 px-3 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition">
                                        📸 Retake
                                    </button>
                                    <button type="button" @click="slipGalleryInput.click()" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                                        📁 Choose Other
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Remarks
                            </label>
                            <textarea v-model="form.remarks" rows="2" placeholder="Internal remarks..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Description
                            </label>
                            <textarea v-model="form.description" rows="2" placeholder="Bill details or payment notes..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link href="/admin/bills" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50 flex items-center gap-2">
                            <span v-if="form.processing">Saving Bill...</span>
                            <span v-else>Save Bill &rarr;</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
