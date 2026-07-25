@extends('layouts.app')

@section('header', 'Role Management')

@section('content')
<div x-data="{ inspectModalOpen: false, selectedRole: null }" class="space-y-6">

    <!-- Top Action & Search Bar -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Security Roles</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure role-based access control and inspect granted Spatie capabilities.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- Search Input -->
            <form method="GET" action="{{ route('admin.roles.index') }}" class="relative min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </form>

            @can('roles.create')
                <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create New Role
                </a>
            @endcan
        </div>
    </div>

    <!-- Roles Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($roles as $role)
            @php
                $moduleBreakdown = [];
                foreach ($role->permissions as $p) {
                    $parts = explode('.', $p->name);
                    $mod = count($parts) > 1 ? ucfirst($parts[0]) : 'General';
                    $moduleBreakdown[$mod] = ($moduleBreakdown[$mod] ?? 0) + 1;
                }
                ksort($moduleBreakdown);
            @endphp

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div>
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white font-bold text-base flex items-center justify-center shadow-md shadow-purple-500/20">
                                🛡️
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                    {{ $role->name }}
                                </h3>
                                <span class="text-[11px] text-slate-400 font-mono">Guard: {{ $role->guard_name }}</span>
                            </div>
                        </div>

                        @if ($role->name === 'Super Admin')
                            <span class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-bold tracking-wider uppercase">
                                System Protected
                            </span>
                        @endif
                    </div>

                    <!-- Metrics Badges -->
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800/80">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block">Assigned Users</span>
                            <span class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($role->users_count) }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800/80">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block">Granted Permissions</span>
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $role->name === 'Super Admin' ? 'All (' . $totalPermissionsCount . ')' : $role->permissions_count }}
                            </span>
                        </div>
                    </div>

                    <!-- Module Capabilities Breakdown -->
                    <div class="mb-4">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Module Coverage</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($moduleBreakdown as $modName => $count)
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-medium border border-slate-200 dark:border-slate-700">
                                    {{ $modName }} <strong class="text-indigo-600 dark:text-indigo-400">({{ $count }})</strong>
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">No permissions assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <!-- Quick Inspect Modal Trigger -->
                    <button @click="selectedRole = {{ json_encode([
                        'name' => $role->name,
                        'users_count' => $role->users_count,
                        'permissions' => $role->permissions->pluck('name'),
                        'module_breakdown' => $moduleBreakdown
                    ]) }}; inspectModalOpen = true" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Inspect Capabilities
                    </button>

                    <div class="flex items-center gap-1.5">
                        @can('roles.edit')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 text-xs font-semibold transition">
                                Edit
                            </a>
                        @endcan

                        @can('roles.delete')
                            @if ($role->name !== 'Super Admin')
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete role {{ $role->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-xs font-semibold transition">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                <p class="text-slate-400 text-sm">No security roles matching your search criteria.</p>
            </div>
        @endforelse
    </div>

    <!-- Inspect Role Modal -->
    <div x-show="inspectModalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="inspectModalOpen = false" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold">
                        🛡️
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base" x-text="selectedRole?.name"></h3>
                        <p class="text-xs text-slate-400">Granted Spatie capabilities</p>
                    </div>
                </div>
                <button @click="inspectModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm p-1">
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto space-y-4 pr-1">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-xs flex justify-between text-slate-600 dark:text-slate-300 font-medium">
                    <span>Assigned Users: <strong class="text-slate-900 dark:text-white" x-text="selectedRole?.users_count"></strong></span>
                    <span>Total Permissions: <strong class="text-indigo-600 dark:text-indigo-400" x-text="selectedRole?.permissions?.length"></strong></span>
                </div>

                <div class="space-y-3">
                    <template x-for="(perms, mod) in selectedRole?.module_breakdown" :key="mod">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span x-text="mod"></span> Module
                            </h4>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="p in selectedRole?.permissions" :key="p">
                                    <span x-show="p.startsWith(mod.toLowerCase() + '.')" class="px-2 py-1 rounded-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-800 dark:text-slate-200" x-text="p"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button @click="inspectModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
