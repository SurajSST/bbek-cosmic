<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();

const form = useForm({
    file: null,
});

const isDragging = ref(false);
const fileName = ref('');

function handleDrop(e) {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file) {
        form.file = file;
        fileName.value = file.name;
    }
}

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        form.file = file;
        fileName.value = file.name;
    }
}

function submit() {
    form.post('/admin/sales-orders/bulk-upload', {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout title="Bulk Upload Sales Orders">
        <Head title="Bulk Upload Sales Orders" />

        <div class="max-w-4xl mx-auto space-y-6">
            
            <!-- Breadcrumb -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/sales-orders" class="text-slate-400 hover:text-indigo-600 transition">Sales Orders</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Bulk CSV / Excel Upload</span>
                </div>
                <Link href="/admin/sales-orders" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; Back to Orders
                </Link>
            </div>

            <!-- Upload Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-6">
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading flex items-center gap-2">
                            <span>📊</span> Batch Import Spreadsheet
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Upload multi-order CSV/Excel files with automated validation and rollback protection.
                        </p>
                    </div>

                    <a 
                        href="/admin/sales-orders/bulk-upload/sample" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 transition shadow-xs"
                    >
                        <span>📥</span>
                        <span>Download Sample CSV</span>
                    </a>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Drag & Drop Zone -->
                    <div 
                        @dragover.prevent="isDragging = true" 
                        @dragleave.prevent="isDragging = false" 
                        @drop.prevent="handleDrop"
                        class="border-2 border-dashed rounded-3xl p-8 text-center transition cursor-pointer"
                        :class="isDragging ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-400'"
                    >
                        <input type="file" ref="fileInput" @change="handleFileSelect" accept=".csv,.xlsx,.xls" class="hidden" id="bulk_file" />
                        <label for="bulk_file" class="cursor-pointer block space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                                📄
                            </div>
                            <div>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">Click to browse</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400"> or drag and drop spreadsheet here</span>
                            </div>
                            <p class="text-[11px] text-slate-400">
                                Supports .CSV, .XLSX, or .XLS (Up to 10MB)
                            </p>
                        </label>

                        <div v-if="fileName" class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                            <span>✓ Selected: {{ fileName }}</span>
                        </div>
                    </div>

                    <p v-if="form.errors.file" class="text-xs text-rose-500 font-semibold">{{ form.errors.file }}</p>

                    <!-- Instructions Card -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-xs space-y-2">
                        <p class="font-bold text-slate-800 dark:text-slate-200">Required CSV Columns:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px] font-mono text-slate-600 dark:text-slate-400">
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">so_number</div>
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">so_from</div>
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">billed_to</div>
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">billed_status</div>
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">product_name</div>
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">quantity</div>
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">unit_price</div>
                            <div class="p-2 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">bill_no (opt)</div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Link href="/admin/sales-orders" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">Cancel</Link>
                        <button type="submit" :disabled="form.processing || !form.file" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50">
                            <span v-if="form.processing">Processing Upload...</span>
                            <span v-else>Import Sales Orders &rarr;</span>
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
