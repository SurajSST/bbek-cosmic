@extends('layouts.app')

@section('header', 'Create Role')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    searchFilter: '',
    toggleAll(checked) {
        const checkboxes = $el.querySelectorAll('input[name=\'permissions[]\']');
        checkboxes.forEach(cb => cb.checked = checked);
    }
}">

    <!-- Header & Back Link -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create Security Role</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Define role credentials and configure granular Spatie permissions by module.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:underline">
            ← Back to Roles
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
        
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
                <p class="font-bold">Please correct the errors below:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-8">
            @csrf

            <!-- Role Name -->
            <div class="max-w-md">
                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Role Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    placeholder="e.g. Finance Manager, Operations Lead">
            </div>

            <!-- Permission Matrix Toolbar & Search Filter -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Assign Permissions by Module</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Permissions are grouped logically. Filter or select capabilities below.</p>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <!-- Matrix Search Filter -->
                        <div class="relative w-full sm:w-48">
                            <input type="text" x-model="searchFilter" placeholder="Filter permissions..."
                                class="w-full pl-8 pr-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        <!-- Global Check / Uncheck All -->
                        <button type="button" @click="toggleAll(true)" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 text-xs font-semibold shrink-0 transition">
                            Check All
                        </button>
                        <button type="button" @click="toggleAll(false)" class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 text-xs font-semibold shrink-0 transition">
                            Uncheck All
                        </button>
                    </div>
                </div>

                <!-- Grouped Permissions Modules -->
                <div class="space-y-6">
                    @foreach ($groupedPermissions as $module => $permissions)
                        @php
                            $moduleIcon = match(strtolower($module)) {
                                'users' => '👥',
                                'roles' => '🛡️',
                                'permissions' => '🔑',
                                'dashboard' => '📊',
                                'members' => '👤',
                                'savings' => '💰',
                                'loans' => '🏦',
                                default => '⚙️'
                            };
                        @endphp

                        <div x-data="{
                            moduleCheckedCount: 0,
                            updateCount() {
                                const checked = $el.querySelectorAll('input[type=checkbox]:checked');
                                this.moduleCheckedCount = checked.length;
                            },
                            toggleModule(val) {
                                const checkboxes = $el.querySelectorAll('input[type=checkbox]');
                                checkboxes.forEach(cb => cb.checked = val);
                                this.updateCount();
                            }
                        }" x-init="updateCount()" class="p-5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                            
                            <!-- Module Header & Counter -->
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ $moduleIcon }}</span>
                                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs uppercase tracking-wider">
                                        {{ $module }} Module
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-bold" x-text="moduleCheckedCount + ' / {{ count($permissions) }} selected'"></span>
                                </div>

                                <div class="flex items-center gap-2 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">
                                    <button type="button" @click="toggleModule(true)" class="hover:underline">Select All</button>
                                    <span class="text-slate-300 dark:text-slate-700">|</span>
                                    <button type="button" @click="toggleModule(false)" class="hover:underline text-slate-500 dark:text-slate-400">Clear</button>
                                </div>
                            </div>

                            <!-- Permissions Checkbox Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach ($permissions as $permission)
                                    <label x-show="!searchFilter || '{{ $permission->name }}'.toLowerCase().includes(searchFilter.toLowerCase())"
                                        class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                            @change="updateCount()"
                                            {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                            class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300 font-mono">
                                            {{ $permission->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition">
                    Save Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
