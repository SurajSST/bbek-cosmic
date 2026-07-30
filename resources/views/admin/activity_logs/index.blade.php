@extends('layouts.app')

@section('header', 'Activity Logs')

@section('content')
<div x-data="{ detailsModalOpen: false, modalTitle: '', modalProperties: null, modalIp: '', modalAgent: '' }" class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                System Activity Logs
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Audit trail recording key operations, user logins, and data modifications across the system.</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by User, Description, Action..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div>
                <select name="action" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="all" {{ request('action') === 'all' ? 'selected' : '' }}>All Actions</option>
                    @foreach ($distinctActions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="w-full px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-semibold transition">
                    Filter Logs
                </button>
                @if (request('search') || (request('action') && request('action') !== 'all'))
                    <a href="{{ route('admin.activity-logs.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-semibold transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="py-3.5 px-4 font-semibold w-12 text-center">S.N.</th>
                        <th class="py-3.5 px-4 font-semibold">User</th>
                        <th class="py-3.5 px-4 font-semibold">Action</th>
                        <th class="py-3.5 px-4 font-semibold">Description</th>
                        <th class="py-3.5 px-4 font-semibold">Subject</th>
                        <th class="py-3.5 px-4 font-semibold">IP Address</th>
                        <th class="py-3.5 px-4 font-semibold">Timestamp</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse ($activityLogs as $index => $log)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400">
                                {{ $activityLogs->firstItem() + $index }}
                            </td>

                            <!-- User -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $log->user_name }}</div>
                                @if($log->user)
                                    <div class="text-[10px] text-slate-400">{{ $log->user->email }}</div>
                                @endif
                            </td>

                            <!-- Action Badge -->
                            <td class="py-3.5 px-4">
                                @php
                                    $actionStyle = match(true) {
                                        str_contains($log->action, 'create') || str_contains($log->action, 'store') => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                        str_contains($log->action, 'update') || str_contains($log->action, 'edit') => 'bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                        str_contains($log->action, 'delete') || str_contains($log->action, 'destroy') => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                        str_contains($log->action, 'login') => 'bg-violet-50 dark:bg-violet-950/80 text-violet-700 dark:text-violet-300 border-violet-200 dark:border-violet-800',
                                        str_contains($log->action, 'logout') => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $actionStyle }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="py-3.5 px-4 font-medium text-slate-800 dark:text-slate-200 max-w-sm truncate">
                                {{ $log->description }}
                            </td>

                            <!-- Subject -->
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                @if($log->subject_type)
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-slate-400 text-[11px]">
                                {{ $log->ip_address ?: '—' }}
                            </td>

                            <!-- Timestamp -->
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 text-[11px]">
                                <span class="block font-semibold text-slate-700 dark:text-slate-300">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="text-[10px]">{{ $log->created_at->format('h:i:s A') }}</span>
                            </td>

                            <!-- Actions / Modal View -->
                            <td class="py-3.5 px-4 text-right">
                                <button @click="modalTitle = 'Log Entry Details #{{ $log->id }}'; modalProperties = {{ json_encode($log->properties) }}; modalIp = '{{ $log->ip_address }}'; modalAgent = '{{ addslashes($log->user_agent) }}'; detailsModalOpen = true"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-semibold transition">
                                    View Context
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="font-medium text-slate-600 dark:text-slate-300">No Activity Logs found</p>
                                    <p class="text-xs text-slate-400">System activities will be recorded automatically when operations are performed.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activityLogs->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $activityLogs->links() }}
            </div>
        @endif
    </div>

    <!-- Details Modal -->
    <div x-show="detailsModalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="detailsModalOpen = false" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-base" x-text="modalTitle"></h3>
                <button @click="detailsModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Client User Agent</span>
                    <p class="font-mono text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 break-all" x-text="modalAgent || 'N/A'"></p>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Properties & Context JSON</span>
                    <pre class="font-mono text-slate-800 dark:text-slate-200 bg-slate-950 text-emerald-400 p-3 rounded-xl border border-slate-800 overflow-x-auto max-h-60 text-[11px]" x-text="modalProperties ? JSON.stringify(modalProperties, null, 2) : 'No extra properties attached.'"></pre>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
                <button @click="detailsModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
