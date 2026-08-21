<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Cosmic Bill') }} — SaaS Enterprise Billing</title>

    <!-- PWA Meta Tags & Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#070a12" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Cosmic Bill">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">

    <!-- Anti-flicker Theme Initialization -->
    <script>
        (function() {
            function applyTheme() {
                const theme = localStorage.getItem('theme') || 'system';
                const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            applyTheme();

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if ((localStorage.getItem('theme') || 'system') === 'system') {
                    applyTheme();
                }
            });
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 text-slate-900 dark:bg-[#070a12] dark:text-slate-100 antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-150 overflow-hidden">
    <div x-data="{ 
        sidebarOpen: false, 
        pwaInstallPrompt: null,
        theme: localStorage.getItem('theme') || 'system',
        isDarkActive: document.documentElement.classList.contains('dark'),
        passwordModalOpen: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }},
        showCurrentPass: false,
        showNewPass: false,
        showConfirmPass: false,
        init() {
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.pwaInstallPrompt = e;
            });
            window.addEventListener('theme-changed', (e) => {
                this.theme = e.detail.theme;
                this.isDarkActive = document.documentElement.classList.contains('dark');
            });
        },
        installPwa() {
            if (this.pwaInstallPrompt) {
                this.pwaInstallPrompt.prompt();
                this.pwaInstallPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        this.pwaInstallPrompt = null;
                    }
                });
            }
        },
        updateTheme(newTheme) {
            this.theme = newTheme;
            localStorage.setItem('theme', newTheme);
            const isDark = newTheme === 'dark' || (newTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            this.isDarkActive = isDark;
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: newTheme } }));
        }
    }" class="h-screen flex overflow-hidden">
        
        <!-- Sidebar Navigation -->
        @include('layouts.navigation')

        <!-- Main Content Column -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden bg-slate-50 dark:bg-[#070a12]">
            
            <!-- Sticky Top Header Navbar -->
            <header class="sticky top-0 z-30 bg-white/90 dark:bg-[#0a0e17]/90 backdrop-blur-xl border-b border-slate-200/70 dark:border-slate-800/80 px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between shadow-2xs shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md border border-indigo-200/50 dark:border-indigo-800/50">Cosmic Portal</span>
                        </div>
                        <h1 class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white leading-tight mt-0.5">
                            @yield('header', 'Dashboard')
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3.5">
                    
                    <!-- PWA Install Button (Dynamic) -->
                    <template x-if="pwaInstallPrompt">
                        <button @click="installPwa()" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold transition hover:scale-105 active:scale-95 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Install App</span>
                        </button>
                    </template>

                    <!-- Quick Action Button -->
                    @can('sales-orders.create')
                        <a href="{{ route('admin.sales-orders.create') }}" class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                            <span>New Order</span>
                        </a>
                    @endcan

                    <!-- Theme Selector Dropdown -->
                    <div class="relative" x-data="{ openMenu: false }">
                        <button @click="openMenu = !openMenu" @click.away="openMenu = false" class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 transition shadow-2xs" title="Switch Theme">
                            <template x-if="!isDarkActive">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </template>
                            <template x-if="isDarkActive">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            </template>
                        </button>

                        <div x-show="openMenu" x-cloak class="absolute right-0 mt-2 w-44 py-1.5 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 z-50 overflow-hidden">
                            <button @click="updateTheme('light'); openMenu = false" class="w-full px-3.5 py-2 text-left text-xs font-medium flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800 transition" :class="theme === 'light' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                <span class="flex items-center gap-2">☀️ Light Mode</span>
                                <span x-show="theme === 'light'">✓</span>
                            </button>

                            <button @click="updateTheme('dark'); openMenu = false" class="w-full px-3.5 py-2 text-left text-xs font-medium flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800 transition" :class="theme === 'dark' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                <span class="flex items-center gap-2">🌙 Dark Mode</span>
                                <span x-show="theme === 'dark'">✓</span>
                            </button>

                            <button @click="updateTheme('system'); openMenu = false" class="w-full px-3.5 py-2 text-left text-xs font-medium flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800 transition" :class="theme === 'system' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                <span class="flex items-center gap-2">💻 System Auto</span>
                                <span x-show="theme === 'system'">✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- User Profile Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 p-1 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition border border-transparent hover:border-slate-200 dark:hover:border-slate-800">
                            <div class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-left pr-1">
                                <span class="block text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                    {{ Auth::user()->name }}
                                </span>
                                <span class="block text-[10px] text-slate-400 font-medium">
                                    {{ Auth::user()->roles->first()?->name ?? 'User' }}
                                </span>
                            </div>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-56 py-2 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 z-50">
                            <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                                <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Signed in as</p>
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate mt-0.5">{{ Auth::user()->name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                            </div>

                            <!-- Change Password Action -->
                            <div class="py-1 border-b border-slate-100 dark:border-slate-800">
                                <button type="button" @click="passwordModalOpen = true; open = false" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2.5 transition">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    <span>Change Password</span>
                                </button>
                            </div>

                            <!-- Sign Out -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center gap-2.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7 space-y-6">
                <!-- Notifications / Flash Banners -->
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-900 dark:text-emerald-200 flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                            <span class="text-xs font-semibold">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 text-xs p-1">✕</button>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-900 dark:text-rose-200 flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-lg bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs shrink-0">⚠️</div>
                            <span class="text-xs font-semibold">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-300 text-xs p-1">✕</button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <!-- Change Password Modal -->
        <div x-show="passwordModalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="passwordModalOpen = false" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl space-y-5">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                            🔑
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">Change Password</h3>
                            <p class="text-xs text-slate-400">Update your account login credentials</p>
                        </div>
                    </div>
                    <button type="button" @click="passwordModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm p-1">✕</button>
                </div>

                @if ($errors->has('current_password') || $errors->has('password'))
                    <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
                        @if ($errors->has('current_password'))
                            <p class="font-medium">• {{ $errors->first('current_password') }}</p>
                        @endif
                        @if ($errors->has('password'))
                            <p class="font-medium">• {{ $errors->first('password') }}</p>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div>
                        <label for="modal_current_password" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Current Password
                        </label>
                        <div class="relative">
                            <input :type="showCurrentPass ? 'text' : 'password'" id="modal_current_password" name="current_password" required
                                class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                placeholder="Enter existing password">
                            <button type="button" @click="showCurrentPass = !showCurrentPass" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold">
                                <span x-show="!showCurrentPass">👁️</span>
                                <span x-show="showCurrentPass">🙈</span>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="modal_new_password" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            New Password
                        </label>
                        <div class="relative">
                            <input :type="showNewPass ? 'text' : 'password'" id="modal_new_password" name="password" required minlength="8"
                                class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                placeholder="Min. 8 characters">
                            <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold">
                                <span x-show="!showNewPass">👁️</span>
                                <span x-show="showNewPass">🙈</span>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="modal_confirm_password" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPass ? 'text' : 'password'" id="modal_confirm_password" name="password_confirmation" required minlength="8"
                                class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                placeholder="Re-type new password">
                            <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold">
                                <span x-show="!showConfirmPass">👁️</span>
                                <span x-show="showConfirmPass">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                        <button type="button" @click="passwordModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
