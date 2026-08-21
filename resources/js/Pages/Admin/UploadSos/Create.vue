<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    so_number: 'USO-' + Math.floor(100000 + Math.random() * 900000),
    so_from: '',
    billed_from: '',
    billed_to: '',
    status: 'billed',
    amount: '',
    so_image: null,
    slip_image: null,
    remarks: '',
    description: ''
});

const soImagePreview = ref(null);
const slipImagePreview = ref(null);

function handleSoImage(e) {
    const file = e.target.files[0];
    if (file) {
        form.so_image = file;
        const reader = new FileReader();
        reader.onload = (ev) => { soImagePreview.value = ev.target.result; };
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

function submit() {
    form.post('/admin/upload-sos');
}
</script>

<template>
    <AuthenticatedLayout title="Upload Sales Order Slip">
        <Head title="Upload SO Slip" />

        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/upload-sos" class="text-slate-400 hover:text-indigo-600 transition">Upload SO</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Upload New Slip</span>
                </div>
                <Link href="/admin/upload-sos" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; Back
                </Link>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-6">
                
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading flex items-center gap-2">
                        <span>🖼️</span> Sales Order Slip Document Details
                    </h3>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                SO Slip Number *
                            </label>
                            <input type="text" v-model="form.so_number" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.so_number" class="text-xs text-rose-500 mt-1">{{ form.errors.so_number }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Total Amount (Rs.)
                            </label>
                            <input type="number" v-model="form.amount" min="0" step="0.01" placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.amount" class="text-xs text-rose-500 mt-1">{{ form.errors.amount }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                SO From (Origin / Salesperson) *
                            </label>
                            <input type="text" v-model="form.so_from" required placeholder="e.g. Sales Team Alpha" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.so_from" class="text-xs text-rose-500 mt-1">{{ form.errors.so_from }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Billed To (Customer Name / Entity) *
                            </label>
                            <input type="text" v-model="form.billed_to" required placeholder="Customer or Client Name" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.billed_to" class="text-xs text-rose-500 mt-1">{{ form.errors.billed_to }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Billed From
                            </label>
                            <input type="text" v-model="form.billed_from" placeholder="Billing branch or warehouse" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Status *
                            </label>
                            <select v-model="form.status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="pending">Pending</option>
                                <option value="billed">Billed</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- File Uploads -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                SO Document Image *
                            </label>
                            <input type="file" required accept="image/*" capture="environment" @change="handleSoImage" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <p v-if="form.errors.so_image" class="text-xs text-rose-500 mt-1">{{ form.errors.so_image }}</p>
                            <div v-if="soImagePreview" class="w-28 h-28 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 mt-2">
                                <img :src="soImagePreview" class="w-full h-full object-cover" />
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Delivery Slip (Optional)
                            </label>
                            <input type="file" accept="image/*" capture="environment" @change="handleSlipImage" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <div v-if="slipImagePreview" class="w-28 h-28 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 mt-2">
                                <img :src="slipImagePreview" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Remarks
                            </label>
                            <textarea v-model="form.remarks" rows="2" placeholder="Internal remarks..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold"></textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Description
                            </label>
                            <textarea v-model="form.description" rows="2" placeholder="Detailed notes..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link href="/admin/upload-sos" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50">
                            Save SO Slip &rarr;
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
