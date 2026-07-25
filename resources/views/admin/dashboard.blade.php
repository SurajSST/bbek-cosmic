@extends('layouts.app')

@section('header', 'System Overview')

@section('content')
<div class="space-y-6">

    <!-- Hero Welcome Banner -->
    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-violet-800 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-3xl space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-white">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                SaaS System Online
            </div>
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight font-heading">
                Welcome back, {{ Auth::user()->name }} 👋
            </h2>
            <p class="text-indigo-100 text-xs sm:text-sm leading-relaxed">
                Logged in as <strong class="text-white bg-white/20 px-2 py-0.5 rounded-lg">{{ Auth::user()->roles->pluck('name')->implode(', ') ?: 'No Role' }}</strong>. Control billing, access rights, and sales order operations.
            </p>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Users -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Users</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold font-heading text-slate-900 dark:text-white">{{ number_format($stats['total_users']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1 font-medium">Registered accounts</div>
        </div>

        <!-- Active Users -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Active Users</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold font-heading text-slate-900 dark:text-white">{{ number_format($stats['active_users']) }}</div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-medium">Enabled accounts</div>
        </div>

        <!-- Inactive Users -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Inactive Users</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold font-heading text-slate-900 dark:text-white">{{ number_format($stats['inactive_users']) }}</div>
            <div class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">Deactivated</div>
        </div>

        <!-- Total Roles -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Roles</span>
                <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold font-heading text-slate-900 dark:text-white">{{ number_format($stats['total_roles']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1 font-medium">Spatie RBAC groups</div>
        </div>

        <!-- Total Permissions -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Permissions</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                </div>
            </div>
            <div class="text-2xl font-bold font-heading text-slate-900 dark:text-white">{{ number_format($stats['total_permissions']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1 font-medium">Granular capabilities</div>
        </div>
    </div>

    <!-- Recent Users & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Recent Registrations Table -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">Recent User Accounts</h3>
                    <p class="text-xs text-slate-400">Latest user registrations and Spatie role assignments</p>
                </div>
                @can('users.view')
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Manage Users →</a>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400">
                            <th class="py-3 px-3 font-semibold uppercase tracking-wider">User</th>
                            <th class="py-3 px-3 font-semibold uppercase tracking-wider">Roles</th>
                            <th class="py-3 px-3 font-semibold uppercase tracking-wider">Status</th>
                            <th class="py-3 px-3 font-semibold uppercase tracking-wider">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse ($recentUsers as $user)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $user->name }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    @forelse ($user->roles as $r)
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 italic">No role</span>
                                    @endforelse
                                </td>
                                <td class="py-3 px-3">
                                    @if ($user->status === 'active')
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Active</span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-400 font-medium">
                                    {{ $user->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">No users registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Architecture Readiness Summary -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 p-6 shadow-sm flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">Architecture Verified</h3>
                    <p class="text-xs text-slate-400">Enterprise SaaS foundation active</p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-xs text-slate-700 dark:text-slate-300">
                        <div class="w-5 h-5 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">✓</div>
                        <span class="font-medium">Spatie RBAC Authorization Engine</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-700 dark:text-slate-300">
                        <div class="w-5 h-5 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">✓</div>
                        <span class="font-medium">Strict Middleware Policy Defense</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-700 dark:text-slate-300">
                        <div class="w-5 h-5 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">✓</div>
                        <span class="font-medium">Sales Order Master-Detail Module</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-700 dark:text-slate-300">
                        <div class="w-5 h-5 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">✓</div>
                        <span class="font-medium">Super Admin Safeguard Protection</span>
                    </div>
                </div>
            </div>

            @can('sales-orders.view')
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('admin.sales-orders.index') }}" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 transition">
                        <span>Go to Sales Orders Module</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            @endcan
        </div>

    </div>
</div>
@endsection
