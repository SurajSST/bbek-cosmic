@extends('layouts.app')

@section('header', 'Permission Registry')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Permission Registry</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Granular module-based Spatie capabilities protecting system backend routes and actions.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- Search Keyword Input -->
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="relative min-w-[240px]">
                <input type="hidden" name="module" value="{{ request('module', 'all') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search permissions..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </form>

            @can('permissions.create')
                <button onclick="document.getElementById('createPermissionModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Permission
                </button>
            @endcan
        </div>
    </div>

    <!-- Module Filter Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800 scrollbar-none">
        <a href="{{ route('admin.permissions.index', ['module' => 'all', 'search' => request('search')]) }}"
            class="px-3 py-2 rounded-xl text-xs font-semibold shrink-0 transition flex items-center gap-2 {{ request('module', 'all') === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <span>All Modules</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ request('module', 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                {{ array_sum($modulesList) }}
            </span>
        </a>

        @foreach ($modulesList as $modName => $count)
            <a href="{{ route('admin.permissions.index', ['module' => strtolower($modName), 'search' => request('search')]) }}"
                class="px-3 py-2 rounded-xl text-xs font-semibold shrink-0 transition flex items-center gap-2 {{ strtolower(request('module')) === strtolower($modName) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span>{{ $modName }}</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ strtolower(request('module')) === strtolower($modName) ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $count }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Grouped Permissions Display -->
    <div class="space-y-6">
        @forelse ($groupedPermissions as $module => $modulePermissions)
            @php
                $moduleIcon = match(strtolower($module)) {
                    'users' => '👥',
                    'roles' => '🛡️',
                    'permissions' => '🔑',
                    'dashboard' => '📊',
                    'members' => '👤',
                    'savings' => '💰',
                    'loans' => '🏦',
                    'expenses' => '💸',
                    default => '⚡'
                };
            @endphp

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold text-sm flex items-center justify-center">
                            {{ $moduleIcon }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                {{ $module }} Module
                            </h3>
                            <span class="text-[11px] text-slate-400">Standard convention: {{ strtolower($module) }}.*</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                        {{ count($modulePermissions) }} permission(s)
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($modulePermissions as $perm)
                        @php
                            $action = explode('.', $perm->name)[1] ?? 'view';
                            $actionStyle = match($action) {
                                'view' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                'create' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                'edit' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                'delete' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                default => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800'
                            };
                            $isProtected = in_array($perm->name, [
                                'dashboard.view',
                                'users.view', 'users.create', 'users.edit', 'users.delete',
                                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                                'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete'
                            ]);
                        @endphp

                        <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">
                                        {{ $perm->name }}
                                    </span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md border {{ $actionStyle }}">
                                        {{ $action }}
                                    </span>
                                </div>

                                <!-- Assigned Roles Badges -->
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @forelse ($perm->roles as $r)
                                        <span class="px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 text-[10px] font-semibold border border-purple-200/60 dark:border-purple-800/60">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-[10px] text-slate-400 italic">Unassigned</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-[11px]">
                                @if ($isProtected)
                                    <span class="text-amber-600 dark:text-amber-400 font-medium">Core System</span>
                                @else
                                    <span class="text-slate-400">Custom Capability</span>
                                @endif

                                @can('permissions.delete')
                                    @if (!$isProtected)
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}" onsubmit="return confirm('Delete permission {{ $perm->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline font-semibold">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                <p class="text-slate-400 text-sm">No permissions found matching your search or module filter.</p>
            </div>
        @endforelse
    </div>

    <!-- Create Permission Modal -->
    <div id="createPermissionModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">New Granular Permission</h3>
                <button onclick="document.getElementById('createPermissionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    ✕
                </button>
            </div>

            <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-4">
                @csrf

                <!-- Suggested Modules Select or Custom Input -->
                <div x-data="{ isCustom: false }">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Target Module</label>
                    <template x-if="!isCustom">
                        <div class="flex gap-2">
                            <select name="module" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="users">Users</option>
                                <option value="roles">Roles</option>
                                <option value="permissions">Permissions</option>
                                <option value="dashboard">Dashboard</option>
                                <option value="members">Members (Future)</option>
                                <option value="savings">Savings (Future)</option>
                                <option value="loans">Loans (Future)</option>
                                <option value="expenses">Expenses (Future)</option>
                                <option value="dividends">Dividends (Future)</option>
                                <option value="ledger">Ledger (Future)</option>
                                <option value="reports">Reports (Future)</option>
                            </select>
                            <button type="button" @click="isCustom = true" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold shrink-0">Custom</button>
                        </div>
                    </template>
                    <template x-if="isCustom">
                        <div class="flex gap-2">
                            <input type="text" name="module" required placeholder="e.g. inventory"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="button" @click="isCustom = false" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold shrink-0">Preset</button>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Action Name</label>
                    <input type="text" name="action" required placeholder="e.g. view, create, edit, delete, repay, approve"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-[11px] text-indigo-700 dark:text-indigo-300">
                    Resulting permission convention: <span class="font-mono font-bold">module.action</span> (e.g. <span class="font-mono">savings.create</span>). Automatically assigned to Super Admin.
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('createPermissionModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/20">
                        Create Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
