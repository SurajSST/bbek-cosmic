@extends('layouts.app')

@section('header', 'Edit Sales Order')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    soFromPreset: ['Cloud', 'Dragon', 'Cosmic'].includes('{{ $salesOrder->so_from }}') ? '{{ $salesOrder->so_from }}' : 'Other',
    soFromCustom: '{{ $salesOrder->so_from }}',
    billedFromPreset: !'{{ $salesOrder->billed_from }}' ? '' : (['Cloud', 'Dragon', 'Cosmic'].includes('{{ $salesOrder->billed_from }}') ? '{{ $salesOrder->billed_from }}' : 'Other'),
    billedFromCustom: '{{ $salesOrder->billed_from }}',
    billedToPreset: ['PBS', 'Prativa Plus Two', 'Prativa School', 'EGA'].includes('{{ $salesOrder->billed_to }}') ? '{{ $salesOrder->billed_to }}' : 'Other',
    billedToCustom: '{{ $salesOrder->billed_to }}',
    items: {{ json_encode($salesOrder->items->map(function($i) {
        return [
            'id' => $i->id,
            'product_name' => $i->product_name,
            'quantity' => $i->quantity,
            'unit_price' => $i->unit_price,
            'remarks' => $i->remarks
        ];
    })) }},
    addItem() {
        this.items.push({ id: null, product_name: '', quantity: 1, unit_price: 0, remarks: '' });
    },
    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },
    calculateTotal() {
        return this.items.reduce((sum, i) => sum + ((parseFloat(i.quantity) || 0) * (parseFloat(i.unit_price) || 0)), 0).toFixed(2);
    }
}">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold font-heading text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Sales Order: {{ $salesOrder->so_number }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update sales order credentials, replace image attachments, and sync line items.</p>
        </div>
        <a href="{{ route('admin.sales-orders.index') }}" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            ← Back to Orders
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 shadow-xs">
        
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
                <p class="font-bold">Please correct the validation errors below:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.sales-orders.update', $salesOrder) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Section 1: Order Header Info -->
            <div class="space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-2.5 flex items-center gap-2 font-heading">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Sales Order Information & Parties
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- SO Number -->
                    <div>
                        <label for="so_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Sales Order (SO) #</label>
                        <input type="text" id="so_number" name="so_number" value="{{ old('so_number', $salesOrder->so_number) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- SO From Dropdown + Custom -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">SO From</label>
                        <div class="space-y-1.5">
                            <select x-model="soFromPreset" @change="if (soFromPreset !== 'Other') soFromCustom = soFromPreset; else soFromCustom = ''"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Cloud">Cloud</option>
                                <option value="Dragon">Dragon</option>
                                <option value="Cosmic">Cosmic</option>
                                <option value="Other">Other (Custom Write-in)</option>
                            </select>
                            <input type="text" name="so_from" x-model="soFromCustom" required placeholder="Enter SO source..."
                                :readonly="soFromPreset !== 'Other'"
                                :class="soFromPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/50 opacity-75 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Billed From Dropdown + Custom -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Billed From <span class="text-slate-400 font-normal lowercase">(opt)</span></label>
                        <div class="space-y-1.5">
                            <select x-model="billedFromPreset" @change="if (billedFromPreset !== 'Other' && billedFromPreset !== '') billedFromCustom = billedFromPreset; else if (billedFromPreset === '') billedFromCustom = ''; else billedFromCustom = ''"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Cloud">Cloud</option>
                                <option value="Dragon">Dragon</option>
                                <option value="Cosmic">Cosmic</option>
                                <option value="">-- None / Optional --</option>
                                <option value="Other">Other (Custom Write-in)</option>
                            </select>
                            <input type="text" name="billed_from" x-model="billedFromCustom" placeholder="Enter entity name..."
                                :readonly="billedFromPreset !== 'Other' && billedFromPreset !== ''"
                                :class="billedFromPreset !== 'Other' && billedFromPreset !== '' ? 'bg-slate-100 dark:bg-slate-800/50 opacity-75 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Billed To Dropdown + Custom -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Billed To</label>
                        <div class="space-y-1.5">
                            <select x-model="billedToPreset" @change="if (billedToPreset !== 'Other') billedToCustom = billedToPreset; else billedToCustom = ''"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="PBS">PBS</option>
                                <option value="Prativa Plus Two">Prativa Plus Two</option>
                                <option value="Prativa School">Prativa School</option>
                                <option value="EGA">EGA</option>
                                <option value="Other">Other (Custom Write-in)</option>
                            </select>
                            <input type="text" name="billed_to" x-model="billedToCustom" required placeholder="Enter customer name..."
                                :readonly="billedToPreset !== 'Other'"
                                :class="billedToPreset !== 'Other' ? 'bg-slate-100 dark:bg-slate-800/50 opacity-75 cursor-not-allowed' : 'bg-white dark:bg-slate-900'"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Billed Status -->
                    <div>
                        <label for="billed_status" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Billed Status</label>
                        <select id="billed_status" name="billed_status" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="pending" {{ old('billed_status', $salesOrder->billed_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="billed" {{ old('billed_status', $salesOrder->billed_status) === 'billed' ? 'selected' : '' }}>Billed</option>
                            <option value="paid" {{ old('billed_status', $salesOrder->billed_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ old('billed_status', $salesOrder->billed_status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Bill No -->
                    <div>
                        <label for="bill_no" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Bill No. <span class="text-slate-400 font-normal lowercase">(optional)</span></label>
                        <input type="text" id="bill_no" name="bill_no" value="{{ old('bill_no', $salesOrder->bill_no) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Bill Image File -->
                    <div>
                        <label for="bill_image" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Bill Image (Replace)</label>
                        <input type="file" id="bill_image" name="bill_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                        @if ($salesOrder->bill_image_url)
                            <div class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">✓ Current file attached</div>
                        @endif
                    </div>

                    <!-- Slip Image File -->
                    <div>
                        <label for="slip_image" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Slip Image (Replace)</label>
                        <input type="file" id="slip_image" name="slip_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                        @if ($salesOrder->slip_image_url)
                            <div class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">✓ Current file attached</div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                    <!-- Remarks -->
                    <div>
                        <label for="remarks" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Remarks</label>
                        <textarea id="remarks" name="remarks" rows="2"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('remarks', $salesOrder->remarks) }}</textarea>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Description</label>
                        <textarea id="description" name="description" rows="2"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $salesOrder->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Dynamic Line Items -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2.5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2 font-heading">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        Product / Maintenance Line Items
                    </h3>

                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 transition shadow-2xs">
                        + Add Item Row
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            
                            <input type="hidden" :name="`items[${index}][id]`" x-model="item.id">

                            <!-- Product Name -->
                            <div class="sm:col-span-4">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Product / Maintenance Name</label>
                                <input type="text" :name="`items[${index}][product_name]`" x-model="item.product_name" required
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Quantity -->
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quantity</label>
                                <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Unit Price -->
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Unit Price (NRs)</label>
                                <input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" min="0" required
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Item Remarks -->
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Remarks <span class="font-normal lowercase text-slate-400">(opt)</span></label>
                                <input type="text" :name="`items[${index}][remarks]`" x-model="item.remarks" placeholder="Optional notes..."
                                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Total Price -->
                            <div class="sm:col-span-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Total</label>
                                <div class="px-2 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-mono font-bold truncate">
                                    <span x-text="((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(0)"></span>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <div class="sm:col-span-1 text-right sm:pt-4">
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition" title="Remove item row">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Grand Total Summary Banner -->
                <div class="p-4 rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/80 flex items-center justify-between shadow-2xs">
                    <span class="text-xs font-bold text-indigo-950 dark:text-indigo-200 uppercase tracking-wider">Grand Total Sales Order Amount</span>
                    <span class="text-xl font-mono font-black text-indigo-600 dark:text-indigo-400">
                        NRs. <span x-text="calculateTotal()"></span>
                    </span>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.sales-orders.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                    Update Sales Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
