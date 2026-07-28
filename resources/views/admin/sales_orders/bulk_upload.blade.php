@extends('layouts.app')

@section('header', 'Bulk Upload Sales Orders')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ fileName: '', dragging: false }">

    <!-- Header & Back Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Bulk Upload Sales Orders with Products
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Import multiple Sales Orders and product items simultaneously using an Excel or CSV file.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sales-orders.bulk-upload.sample') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download Sample File
            </a>
            <a href="{{ route('admin.sales-orders.index') }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                ← Back to Orders
            </a>
        </div>
    </div>

    <!-- Alert / Validation Feedback -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="font-bold text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-2">
            <p class="font-bold text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Upload Validation Error:
            </p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('bulk_errors') && count(session('bulk_errors')) > 0)
        <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs space-y-3">
            <div class="flex items-center gap-2 font-bold text-sm text-amber-800 dark:text-amber-300">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Some Sales Orders could not be uploaded ({{ count(session('bulk_errors')) }} issues found):
            </div>
            <div class="max-h-60 overflow-y-auto space-y-1.5 pr-2 font-mono text-[11px]">
                @foreach (session('bulk_errors') as $bulkErr)
                    <div class="p-2 rounded-lg bg-amber-100/60 dark:bg-amber-900/40 border border-amber-200/60 dark:border-amber-800/60">
                        {{ $bulkErr }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Upload Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
        <form method="POST" action="{{ route('admin.sales-orders.bulk-upload.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- File Drag & Drop -->
            <div class="space-y-2">
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Upload Spreadsheet File (.csv / .xlsx)
                </label>

                <div 
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name || ''"
                    :class="dragging ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20' : 'border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-950/50'"
                    class="relative border-2 border-dashed rounded-3xl p-8 text-center transition-all cursor-pointer group hover:border-indigo-500 dark:hover:border-indigo-400"
                    @click="$refs.fileInput.click()"
                >
                    <input 
                        type="file" 
                        name="file" 
                        x-ref="fileInput" 
                        class="hidden" 
                        accept=".csv,.txt,.xlsx,.xls"
                        @change="fileName = $event.target.files[0]?.name || ''"
                    >

                    <div class="flex flex-col items-center justify-center space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">
                                <span class="text-indigo-600 dark:text-indigo-400 underline">Click to browse</span> or drag and drop your file here
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Supports CSV or Excel files up to 10MB</p>
                        </div>

                        <div x-show="fileName" x-cloak class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-200 text-xs font-mono font-semibold border border-indigo-200 dark:border-indigo-800">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span x-text="fileName"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <a href="{{ route('admin.sales-orders.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-500 transition shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Process & Upload Sales Orders
                </button>
            </div>
        </form>
    </div>

    <!-- Instruction & Column Mapping Guide -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                Excel / CSV Structure & Column Reference Guide
            </h3>
            <a href="{{ route('admin.sales-orders.bulk-upload.sample') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Download Pre-formatted Sample Template (.csv)
            </a>
        </div>

        <div class="text-xs text-slate-600 dark:text-slate-400 space-y-3">
            <p>
                <strong class="text-slate-900 dark:text-white">How multi-product Sales Orders work:</strong>
                If multiple rows in your spreadsheet have the same <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 font-mono font-bold">so_number</code>, they will be combined into a single Sales Order with multiple product line items.
            </p>
        </div>

        <!-- Table of Columns -->
        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Column Name</th>
                        <th class="px-4 py-3">Required</th>
                        <th class="px-4 py-3">Data Type</th>
                        <th class="px-4 py-3">Description & Examples</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-600 dark:text-slate-300 font-mono">
                    <tr class="bg-slate-50/30 dark:bg-slate-900/30">
                        <td class="px-4 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">so_number</td>
                        <td class="px-4 py-2.5 text-rose-500 font-sans font-bold">Yes</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">String</td>
                        <td class="px-4 py-2.5 font-sans">Unique Sales Order number (e.g. <code class="font-mono">SO-2026-001</code>). Group rows by matching SO number.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">so_from</td>
                        <td class="px-4 py-2.5 text-rose-500 font-sans font-bold">Yes</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">String</td>
                        <td class="px-4 py-2.5 font-sans">Sales order source (e.g. <code class="font-mono">Cloud</code>, <code class="font-mono">Dragon</code>, <code class="font-mono">Cosmic</code>).</td>
                    </tr>
                    <tr class="bg-slate-50/30 dark:bg-slate-900/30">
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">billed_from</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">String</td>
                        <td class="px-4 py-2.5 font-sans">Entity/Store issuing the bill (defaults to <code class="font-mono">so_from</code> if omitted).</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">billed_to</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">String</td>
                        <td class="px-4 py-2.5 font-sans">Customer / Client receiving the bill.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">billed_status</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">Enum</td>
                        <td class="px-4 py-2.5 font-sans">Order status: <code class="font-mono">pending</code>, <code class="font-mono">billed</code>, <code class="font-mono">paid</code>, or <code class="font-mono">cancelled</code>. Default: <code class="font-mono">pending</code>.</td>
                    </tr>
                    <tr class="bg-slate-50/30 dark:bg-slate-900/30">
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">bill_no</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">String</td>
                        <td class="px-4 py-2.5 font-sans">Associated invoice or bill reference number (e.g. <code class="font-mono">INV-9001</code>).</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">so_remarks</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">Text</td>
                        <td class="px-4 py-2.5 font-sans">Internal order-level notes or remarks.</td>
                    </tr>
                    <tr class="bg-slate-50/30 dark:bg-slate-900/30">
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">so_description</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">Text</td>
                        <td class="px-4 py-2.5 font-sans">Detailed summary or description of the order.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">product_name</td>
                        <td class="px-4 py-2.5 text-rose-500 font-sans font-bold">Yes</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">String</td>
                        <td class="px-4 py-2.5 font-sans">Name of the product or item (e.g. <code class="font-mono">Wireless Ergonomic Mouse</code>).</td>
                    </tr>
                    <tr class="bg-slate-50/30 dark:bg-slate-900/30">
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">quantity</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">Integer</td>
                        <td class="px-4 py-2.5 font-sans">Item quantity (defaults to <code class="font-mono">1</code> if omitted).</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">unit_price</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">Decimal</td>
                        <td class="px-4 py-2.5 font-sans">Price per unit (defaults to <code class="font-mono">0.00</code> if omitted). Total price is auto-calculated.</td>
                    </tr>
                    <tr class="bg-slate-50/30 dark:bg-slate-900/30">
                        <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-300">item_remarks</td>
                        <td class="px-4 py-2.5 text-slate-400 font-sans">Optional</td>
                        <td class="px-4 py-2.5 text-slate-500 font-sans">String</td>
                        <td class="px-4 py-2.5 font-sans">Item-specific notes (e.g. <code class="font-mono">Model X, Color: Black</code>).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
