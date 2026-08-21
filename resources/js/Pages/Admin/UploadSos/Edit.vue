<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    uploadSo: {
        type: Object,
        required: true
    }
});

const form = useForm({
    _method: 'PUT',
    so_number: props.uploadSo.so_number,
    so_from: props.uploadSo.so_from || '',
    billed_from: props.uploadSo.billed_from || '',
    billed_to: props.uploadSo.billed_to,
    status: props.uploadSo.status,
    amount: props.uploadSo.amount || '',
    so_image: null,
    slip_image: null,
    remarks: props.uploadSo.remarks || '',
    description: props.uploadSo.description || ''
});

const soImagePreview = ref(props.uploadSo.so_image ? `/storage/${props.uploadSo.so_image}` : null);
const slipImagePreview = ref(props.uploadSo.slip_image ? `/storage/${props.uploadSo.slip_image}` : null);

const soCameraInput = ref(null);
const soGalleryInput = ref(null);
const slipCameraInput = ref(null);
const slipGalleryInput = ref(null);

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
    form.post(`/admin/upload-sos/${props.uploadSo.id}`);
}
</script>

<template>
    <AuthenticatedLayout :title="`Edit SO Slip #${uploadSo.so_number}`">
        <Head :title="`Edit SO Slip #${uploadSo.so_number}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/upload-sos" class="text-slate-400 hover:text-indigo-600 transition">Upload SO</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Edit #{{ uploadSo.so_number }}</span>
                </div>
                <Link :href="`/admin/upload-sos/${uploadSo.id}`" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; View Details
                </Link>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-6">
                
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading flex items-center gap-2">
                        <span>✏️</span> Edit SO Slip Information
                    </h3>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                SO Slip Number *
                            </label>
                            <input type="text" v-model="form.so_number" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.so_number" class="text-xs text-rose-500 mt-1">{{ form.errors.so_number }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Total Amount (Rs.)
                            </label>
                            <input type="number" v-model="form.amount" min="0" step="0.01" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.amount" class="text-xs text-rose-500 mt-1">{{ form.errors.amount }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                SO From (Origin / Salesperson) *
                            </label>
                            <input type="text" v-model="form.so_from" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.so_from" class="text-xs text-rose-500 mt-1">{{ form.errors.so_from }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Billed To (Customer Name / Entity) *
                            </label>
                            <input type="text" v-model="form.billed_to" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.billed_to" class="text-xs text-rose-500 mt-1">{{ form.errors.billed_to }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Billed From
                            </label>
                            <input type="text" v-model="form.billed_from" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
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
                    </div>

                    <!-- Enhanced Image Uploads (Camera + Gallery) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        
                        <!-- SO Image -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80 space-y-3">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider font-mono">
                                Replace SO Document Image
                            </label>

                            <!-- Hidden inputs -->
                            <input type="file" ref="soCameraInput" accept="image/*" capture="environment" @change="handleSoImage" class="hidden" />
                            <input type="file" ref="soGalleryInput" accept="image/*" @change="handleSoImage" class="hidden" />

                            <div v-if="soImagePreview" class="relative w-full h-44 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-900 mb-2">
                                <img :src="soImagePreview" class="w-full h-full object-contain" alt="SO Preview" />
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="soCameraInput.click()" class="py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <span>📸</span> <span>Open Camera</span>
                                </button>
                                <button type="button" @click="soGalleryInput.click()" class="py-2 px-3 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                    <span>📁</span> <span>Photo Library</span>
                                </button>
                            </div>
                            <p v-if="form.errors.so_image" class="text-xs text-rose-500 font-semibold">{{ form.errors.so_image }}</p>
                        </div>

                        <!-- Delivery Slip -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80 space-y-3">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider font-mono">
                                Replace Delivery Slip
                            </label>

                            <!-- Hidden inputs -->
                            <input type="file" ref="slipCameraInput" accept="image/*" capture="environment" @change="handleSlipImage" class="hidden" />
                            <input type="file" ref="slipGalleryInput" accept="image/*" @change="handleSlipImage" class="hidden" />

                            <div v-if="slipImagePreview" class="relative w-full h-44 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-900 mb-2">
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Remarks
                            </label>
                            <textarea v-model="form.remarks" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5 font-mono">
                                Description
                            </label>
                            <textarea v-model="form.description" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link :href="`/admin/upload-sos/${uploadSo.id}`" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50 flex items-center gap-2">
                            <span v-if="form.processing">Updating SO Slip...</span>
                            <span v-else>Update SO Slip &rarr;</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
