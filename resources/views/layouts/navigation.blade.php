<!-- Backdrop Overlay for Mobile -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-md md:hidden"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-slate-950 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0 border-r border-slate-800/80 shadow-2xl md:shadow-none">
    
    <!-- Brand Logo / Header -->
    <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800/80 bg-slate-950/40">
        <a href="{{ Auth::user()->getHomeRoute() }}" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 font-extrabold text-lg group-hover:scale-105 transition">
                C
            </div>
            <div class="flex flex-col">
                <span class="font-extrabold text-white text-base leading-tight tracking-wide font-heading">Cosmic Bill</span>
                <span class="text-[10px] text-indigo-400 font-bold tracking-widest uppercase">SaaS Enterprise</span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto py-6 px-3.5 space-y-6">

        <!-- Core System -->
        @can('dashboard.view')
            <div>
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2.5">Core System</p>
                <nav class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-400 font-bold border border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' }}">
                        @if(request()->routeIs('admin.dashboard'))
                            <span class="absolute left-0 top-2.5 bottom-2.5 w-1 rounded-r-full bg-indigo-500"></span>
                        @endif
                        <svg class="w-4 h-4 text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                </nav>
            </div>
        @endcan

        <!-- Sales & Operations -->
        @canany(['sales-orders.view', 'bills.view', 'upload-sos.view'])
            <div>
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2.5">Sales & Operations</p>
                <nav class="space-y-1">
                    @can('sales-orders.view')
                        <a href="{{ route('admin.sales-orders.index') }}" class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ request()->routeIs('admin.sales-orders.*') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-400 font-bold border border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.sales-orders.*'))
                                <span class="absolute left-0 top-2.5 bottom-2.5 w-1 rounded-r-full bg-indigo-500"></span>
                            @endif
                            <svg class="w-4 h-4 text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            Sales Orders
                        </a>
                    @endcan

                    @can('bills.view')
                        <a href="{{ route('admin.bills.index') }}" class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ request()->routeIs('admin.bills.*') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-400 font-bold border border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.bills.*'))
                                <span class="absolute left-0 top-2.5 bottom-2.5 w-1 rounded-r-full bg-indigo-500"></span>
                            @endif
                            <svg class="w-4 h-4 text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Upload Bill
                        </a>
                    @endcan

                    @can('upload-sos.view')
                        <a href="{{ route('admin.upload-sos.index') }}" class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ request()->routeIs('admin.upload-sos.*') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-400 font-bold border border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.upload-sos.*'))
                                <span class="absolute left-0 top-2.5 bottom-2.5 w-1 rounded-r-full bg-indigo-500"></span>
                            @endif
                            <svg class="w-4 h-4 text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Upload SO
                        </a>
                    @endcan
                </nav>
            </div>
        @endcanany

        <!-- Access Control -->
        @canany(['users.view', 'roles.view', 'permissions.view'])
            <div>
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2.5">Access Control</p>
                <nav class="space-y-1">
                    @can('users.view')
                        <a href="{{ route('admin.users.index') }}" class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-400 font-bold border border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.users.*'))
                                <span class="absolute left-0 top-2.5 bottom-2.5 w-1 rounded-r-full bg-indigo-500"></span>
                            @endif
                            <svg class="w-4 h-4 text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            User Management
                        </a>
                    @endcan

                    @can('roles.view')
                        <a href="{{ route('admin.roles.index') }}" class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ request()->routeIs('admin.roles.*') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-400 font-bold border border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.roles.*'))
                                <span class="absolute left-0 top-2.5 bottom-2.5 w-1 rounded-r-full bg-indigo-500"></span>
                            @endif
                            <svg class="w-4 h-4 text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            Role Management
                        </a>
                    @endcan

                    @can('permissions.view')
                        <a href="{{ route('admin.permissions.index') }}" class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ request()->routeIs('admin.permissions.*') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-400 font-bold border border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'text-slate-400 hover:bg-slate-900/80 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.permissions.*'))
                                <span class="absolute left-0 top-2.5 bottom-2.5 w-1 rounded-r-full bg-indigo-500"></span>
                            @endif
                            <svg class="w-4 h-4 text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                            Permission Registry
                        </a>
                    @endcan
                </nav>
            </div>
        @endcanany

        <!-- Future Modules Card -->
        <!-- <div>
            <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2.5">Expansion Roadmap</p>
            <div class="px-3.5 py-3.5 rounded-2xl bg-slate-900/80 border border-slate-800/90 text-xs text-slate-400 space-y-2">
                <div class="flex items-center justify-between text-slate-300 font-medium">
                    <span>Future Modules</span>
                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 font-bold">Planned</span>
                </div>
                <div class="flex flex-wrap gap-1">
                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-[10px] text-slate-400 border border-slate-800">Members</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-[10px] text-slate-400 border border-slate-800">Savings</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-[10px] text-slate-400 border border-slate-800">Loans</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-[10px] text-slate-400 border border-slate-800">Expenses</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-950 text-[10px] text-slate-400 border border-slate-800">Reports</span>
                </div>
            </div>
        </div> -->
    </div>

    <!-- User Footer Summary -->
    <div class="p-4 border-t border-slate-800/80 bg-slate-950/60 flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="font-medium text-slate-300">Live Workspace</span>
        </div>
        <span class="font-mono text-[10px] text-slate-400 font-semibold">v1.2.0</span>
    </div>
</aside>
