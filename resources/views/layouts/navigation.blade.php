<aside 
    class="w-64 bg-white dark:bg-[#0a0e17] text-slate-700 dark:text-slate-300 border-r border-slate-200/80 dark:border-slate-800/80 flex flex-col justify-between shrink-0 transition-transform duration-300 z-40 fixed inset-y-0 left-0 md:static md:translate-x-0 shadow-xs"
    :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0'"
>
    <div class="flex flex-col h-full overflow-hidden">
        
        <!-- Brand Header / Logo -->
        <div class="h-16 px-5 flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-[#070a10] shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 text-white font-black text-lg flex items-center justify-center shadow-md shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-200">
                    C
                </div>
                <div class="leading-tight">
                    <span class="block text-sm font-black font-heading tracking-tight text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition">
                        Cosmic Bill
                    </span>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-mono">
                        Enterprise
                    </span>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false" class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Navigation Scrollable Links -->
        <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6">
            
            <!-- Section 1: Overview -->
            <div>
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 font-mono">
                    Overview
                </p>
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                        @if(request()->routeIs('admin.dashboard'))
                            <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                        @endif
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    @can('activity-logs.view')
                        <a href="{{ route('admin.activity-logs.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.activity-logs.*') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.activity-logs.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.activity-logs.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Activity Logs</span>
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Section 2: Sales & Billing -->
            <div>
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 font-mono">
                    Sales & Operations
                </p>
                <div class="space-y-1">
                    @can('sales-orders.view')
                        <a href="{{ route('admin.sales-orders.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.sales-orders.index') || request()->routeIs('admin.sales-orders.show') || request()->routeIs('admin.sales-orders.create') || request()->routeIs('admin.sales-orders.edit') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.sales-orders.index') || request()->routeIs('admin.sales-orders.show') || request()->routeIs('admin.sales-orders.create') || request()->routeIs('admin.sales-orders.edit'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.sales-orders.*') && !request()->routeIs('admin.sales-orders.bulk-upload*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span>Sales Orders</span>
                        </a>
                    @endcan

                    @can('sales-orders.create')
                        <a href="{{ route('admin.sales-orders.bulk-upload') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.sales-orders.bulk-upload*') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.sales-orders.bulk-upload*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.sales-orders.bulk-upload*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <span>Bulk Upload SO</span>
                        </a>
                    @endcan

                    @can('bills.view')
                        <a href="{{ route('admin.bills.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.bills.*') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.bills.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.bills.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Upload Bill</span>
                        </a>
                    @endcan

                    @can('upload-sos.view')
                        <a href="{{ route('admin.upload-sos.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.upload-sos.*') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.upload-sos.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.upload-sos.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Upload SO</span>
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Section 3: Access Control -->
            <div>
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 font-mono">
                    Access Control
                </p>
                <div class="space-y-1">
                    @can('users.view')
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.users.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>User Management</span>
                        </a>
                    @endcan

                    @can('roles.view')
                        <a href="{{ route('admin.roles.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.roles.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.roles.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span>Role Management</span>
                        </a>
                    @endcan

                    @can('permissions.view')
                        <a href="{{ route('admin.permissions.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group {{ request()->routeIs('admin.permissions.*') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium' }}">
                            @if(request()->routeIs('admin.permissions.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                            @endif
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.permissions.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            <span>Permission Registry</span>
                        </a>
                    @endcan
                </div>
            </div>

        </div>

        <!-- Sidebar Footer Status -->
        <div class="p-3 border-t border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-[#070a10] flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] shrink-0">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-medium text-slate-700 dark:text-slate-300">System Online</span>
            </div>
            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-200/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400">PWA v1.0</span>
        </div>
    </div>
</aside>
