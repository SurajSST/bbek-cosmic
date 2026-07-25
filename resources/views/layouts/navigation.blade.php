<!-- Backdrop Overlay for Mobile -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0 border-r border-slate-800 shadow-2xl md:shadow-none">
    
    <!-- Brand Logo / Header -->
    <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 font-bold text-lg">
                C
            </div>
            <div class="flex flex-col">
                <span class="font-bold text-white text-base leading-tight tracking-wide">Cosmic Bill</span>
                <span class="text-[10px] text-indigo-400 font-semibold tracking-wider uppercase">SaaS Engine</span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto py-6 px-4 space-y-6">

        <!-- Core Administration -->
        <div>
            <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Core System</p>
            <nav class="space-y-1">
                @can('dashboard.view')
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600/15 text-indigo-400 font-semibold border border-indigo-500/30' : 'hover:bg-slate-800/60 hover:text-slate-100' }}">
                        <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                @endcan
            </nav>
        </div>

        <!-- Access Control -->
        @canany(['users.view', 'roles.view', 'permissions.view'])
            <div>
                <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Access Control</p>
                <nav class="space-y-1">
                    @can('users.view')
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600/15 text-indigo-400 font-semibold border border-indigo-500/30' : 'hover:bg-slate-800/60 hover:text-slate-100' }}">
                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            User Management
                        </a>
                    @endcan

                    @can('roles.view')
                        <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-600/15 text-indigo-400 font-semibold border border-indigo-500/30' : 'hover:bg-slate-800/60 hover:text-slate-100' }}">
                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            Role Management
                        </a>
                    @endcan

                    @can('permissions.view')
                        <a href="{{ route('admin.permissions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.permissions.*') ? 'bg-indigo-600/15 text-indigo-400 font-semibold border border-indigo-500/30' : 'hover:bg-slate-800/60 hover:text-slate-100' }}">
                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                            Permission Registry
                        </a>
                    @endcan
                </nav>
            </div>
        @endcanany

        <!-- Placeholder section for future modules -->
        <div>
            <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Future Modules</p>
            <div class="px-3 py-3 rounded-xl bg-slate-800/40 border border-slate-800 text-xs text-slate-400 space-y-1">
                <div class="font-medium text-slate-300">Ready for expansion:</div>
                <div class="flex flex-wrap gap-1 pt-1">
                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400">Members</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400">Savings</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400">Loans</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400">Expenses</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400">Reports</span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Footer Summary -->
    <div class="p-4 border-t border-slate-800 bg-slate-900/60 flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>System Active</span>
        </div>
        <span class="font-mono text-[10px] text-slate-500">v1.0.0</span>
    </div>
</aside>
