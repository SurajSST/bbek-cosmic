<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard'
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const flash = computed(() => page.props.flash || {});
const errors = computed(() => page.props.errors || {});

// State Management
const sidebarOpen = ref(false);
const themeMenuOpen = ref(false);
const profileMenuOpen = ref(false);
const quickActionSheetOpen = ref(false);
const passwordModalOpen = ref(false);
const pwaInstallPrompt = ref(null);
const isOnline = ref(navigator.onLine);
const showOnlineBanner = ref(false);
const isPageLoading = ref(false);

// Theme State
const currentTheme = ref(localStorage.getItem('theme') || 'system');
const isDarkActive = ref(document.documentElement.classList.contains('dark'));

function applyTheme(theme) {
    currentTheme.value = theme;
    localStorage.setItem('theme', theme);
    const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    isDarkActive.value = isDark;
}

function setTheme(theme) {
    applyTheme(theme);
    themeMenuOpen.value = false;
}

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrentPass = ref(false);
const showNewPass = ref(false);
const showConfirmPass = ref(false);

function submitPasswordChange() {
    passwordForm.put('/admin/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordModalOpen.value = false;
            passwordForm.reset();
        },
    });
}

// PWA Install
function installPwa() {
    if (pwaInstallPrompt.value) {
        pwaInstallPrompt.value.prompt();
        pwaInstallPrompt.value.userChoice.then((choice) => {
            if (choice.outcome === 'accepted') {
                pwaInstallPrompt.value = null;
            }
        });
    }
}

// Permission Helper
function can(permissionName) {
    const permissions = user.value?.permissions || [];
    const roles = user.value?.roles || [];
    if (roles.includes('Super Admin')) return true;
    return permissions.includes(permissionName);
}

// Route Active Checker
function isUrl(...urls) {
    const currentUrl = page.url;
    return urls.some(url => currentUrl.startsWith(url));
}

// Lifecycle listeners
let removeStartListener = null;
let removeFinishListener = null;

const handleBeforeInstallPrompt = (e) => {
    e.preventDefault();
    pwaInstallPrompt.value = e;
};

const handleOnline = () => {
    isOnline.value = true;
    showOnlineBanner.value = true;
    setTimeout(() => { showOnlineBanner.value = false; }, 4000);
};

const handleOffline = () => {
    isOnline.value = false;
};

const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        sidebarOpen.value = false;
        themeMenuOpen.value = false;
        profileMenuOpen.value = false;
        quickActionSheetOpen.value = false;
        passwordModalOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    window.addEventListener('keydown', handleKeydown);

    removeStartListener = router.on('start', () => {
        isPageLoading.value = true;
    });

    removeFinishListener = router.on('finish', () => {
        isPageLoading.value = false;
        sidebarOpen.value = false; // Auto close drawer on navigation
    });
});

onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.removeEventListener('online', handleOnline);
    window.removeEventListener('offline', handleOffline);
    window.removeEventListener('keydown', handleKeydown);
    if (removeStartListener) removeStartListener();
    if (removeFinishListener) removeFinishListener();
});
</script>

<template>
    <div class="h-[var(--app-height)] flex overflow-hidden bg-slate-50 dark:bg-[#070a12] text-slate-900 dark:text-slate-100 antialiased font-sans">
        
        <!-- Prominent Glowing Top Loading Bar for SPA Transitions -->
        <div v-if="isPageLoading" class="fixed top-0 inset-x-0 h-1 z-[100] overflow-hidden pointer-events-none" :style="{ marginTop: 'env(safe-area-inset-top, 0px)' }">
            <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 animate-pulse w-full shadow-lg shadow-indigo-500/50"></div>
        </div>

        <!-- Mobile Drawer Backdrop Overlay (Closes drawer on outside click) -->
        <Transition
            enter-active-class="transition-opacity ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="sidebarOpen" 
                @click="sidebarOpen = false" 
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 md:hidden"
                aria-label="Close sidebar backdrop"
            ></div>
        </Transition>

        <!-- Sidebar Navigation (Desktop + Slide-over Drawer on Mobile) -->
        <aside 
            class="w-64 bg-white dark:bg-[#0a0e17] text-slate-700 dark:text-slate-300 border-r border-slate-200/80 dark:border-slate-800/80 flex flex-col justify-between shrink-0 transition-transform duration-300 z-50 fixed inset-y-0 left-0 md:static md:translate-x-0 shadow-xs"
            :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0'"
        >
            <div class="flex flex-col h-full overflow-hidden">
                
                <!-- Brand Header / Logo with Mobile Safe-Area Inset Top padding -->
                <div class="px-5 pt-[max(env(safe-area-inset-top,0px),0.85rem)] pb-3.5 flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-[#070a10] shrink-0">
                    <Link href="/admin/dashboard" @click="sidebarOpen = false" class="flex items-center gap-3 group">
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
                    </Link>

                    <!-- Mobile Close Button -->
                    <button @click="sidebarOpen = false" class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Close Menu">
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
                            <Link href="/admin/dashboard" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/dashboard') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/dashboard')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                                <span>Dashboard</span>
                            </Link>

                            <Link v-if="can('activity-logs.view')" href="/admin/activity-logs" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/activity-logs') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/activity-logs')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/activity-logs') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Activity Logs</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Section 2: Sales & Billing -->
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 font-mono">
                            Sales & Operations
                        </p>
                        <div class="space-y-1">
                            <Link v-if="can('sales-orders.view')" href="/admin/sales-orders" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/sales-orders') && !isUrl('/admin/sales-orders/bulk-upload') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/sales-orders') && !isUrl('/admin/sales-orders/bulk-upload')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/sales-orders') && !isUrl('/admin/sales-orders/bulk-upload') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <span>Sales Orders</span>
                            </Link>

                            <Link v-if="can('sales-orders.bulk-upload') || can('sales-orders.create')" href="/admin/sales-orders/bulk-upload" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/sales-orders/bulk-upload') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/sales-orders/bulk-upload')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/sales-orders/bulk-upload') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                <span>Bulk Upload SO</span>
                            </Link>

                            <Link v-if="can('bills.view')" href="/admin/bills" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/bills') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/bills')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/bills') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Upload Bill</span>
                            </Link>

                            <Link v-if="can('upload-sos.view')" href="/admin/upload-sos" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/upload-sos') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/upload-sos')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/upload-sos') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Upload SO</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Section 3: Access Control -->
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 font-mono">
                            Access Control
                        </p>
                        <div class="space-y-1">
                            <Link v-if="can('users.view')" href="/admin/users" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/users') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/users')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/users') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span>User Management</span>
                            </Link>

                            <Link v-if="can('roles.view')" href="/admin/roles" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/roles') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/roles')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/roles') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span>Role Management</span>
                            </Link>

                            <Link v-if="can('permissions.view')" href="/admin/permissions" @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150 relative group"
                                :class="isUrl('/admin/permissions') ? 'bg-indigo-50 dark:bg-indigo-600/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/50 font-medium'">
                                <span v-if="isUrl('/admin/permissions')" class="absolute left-0 top-2 bottom-2 w-1 bg-indigo-600 dark:bg-indigo-500 rounded-r"></span>
                                <svg class="w-4 h-4" :class="isUrl('/admin/permissions') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-200'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                <span>Permission Registry</span>
                            </Link>
                        </div>
                    </div>

                </div>

                <!-- Footer Status -->
                <div class="p-3 border-t border-slate-200/80 dark:border-slate-800/80 bg-slate-50/50 dark:bg-[#070a10] text-xs flex items-center justify-between text-slate-500 shrink-0 pwa-safe-bottom">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">System Online</span>
                    </div>
                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-200/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400">PWA v2.0</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-[var(--app-height)] overflow-hidden bg-slate-50 dark:bg-[#070a12]">
            
            <!-- Sticky Top Header Navbar -->
            <header class="sticky top-0 z-30 glass-header border-b border-slate-200/70 dark:border-slate-800/80 px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between shadow-2xs shrink-0 pwa-safe-top">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden sm:inline-flex md:hidden p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md border border-indigo-200/50 dark:border-indigo-800/50">Cosmic Portal</span>
                            <span v-if="!isOnline" class="px-2 py-0.5 rounded-md bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-[10px] font-bold animate-pulse">
                                ⚡ Offline Cache
                            </span>
                        </div>
                        <h1 class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white leading-tight mt-0.5">
                            {{ props.title }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3.5">
                    
                    <!-- PWA Desktop Install Button -->
                    <button v-if="pwaInstallPrompt" @click="installPwa" class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold transition hover:scale-105 active:scale-95 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Install App</span>
                    </button>

                    <!-- Quick Action Button -->
                    <Link v-if="can('sales-orders.create')" href="/admin/sales-orders/create" class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                        <span>New Order</span>
                    </Link>

                    <!-- Theme Selector Dropdown -->
                    <div class="relative">
                        <button @click="themeMenuOpen = !themeMenuOpen; profileMenuOpen = false" class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 transition shadow-2xs" title="Switch Theme">
                            <svg v-if="!isDarkActive" class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <svg v-else class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        </button>

                        <div v-if="themeMenuOpen" @click="themeMenuOpen = false" class="fixed inset-0 z-40"></div>

                        <div v-show="themeMenuOpen" class="absolute right-0 mt-2 w-44 py-1.5 bg-white dark:bg-[#0c121e] rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 z-50 overflow-hidden">
                            <button @click="setTheme('light')" class="w-full px-3.5 py-2 text-left text-xs font-medium flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800/80 transition" :class="currentTheme === 'light' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                <span class="flex items-center gap-2">☀️ Light Mode</span>
                                <span v-show="currentTheme === 'light'">✓</span>
                            </button>

                            <button @click="setTheme('dark')" class="w-full px-3.5 py-2 text-left text-xs font-medium flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800/80 transition" :class="currentTheme === 'dark' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                <span class="flex items-center gap-2">🌙 Dark Mode</span>
                                <span v-show="currentTheme === 'dark'">✓</span>
                            </button>

                            <button @click="setTheme('system')" class="w-full px-3.5 py-2 text-left text-xs font-medium flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800/80 transition" :class="currentTheme === 'system' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                <span class="flex items-center gap-2">💻 System Auto</span>
                                <span v-show="currentTheme === 'system'">✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- User Profile Menu -->
                    <div class="relative">
                        <button @click="profileMenuOpen = !profileMenuOpen; themeMenuOpen = false" class="flex items-center gap-2 p-1 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition border border-transparent hover:border-slate-200 dark:hover:border-slate-800">
                            <div class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ (user?.name || 'A').charAt(0).toUpperCase() }}
                            </div>
                            <div class="hidden sm:block text-left pr-1">
                                <span class="block text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                    {{ user?.name }}
                                </span>
                                <span class="block text-[10px] text-slate-400 font-medium">
                                    {{ user?.roles?.[0] || 'User' }}
                                </span>
                            </div>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <div v-if="profileMenuOpen" @click="profileMenuOpen = false" class="fixed inset-0 z-40"></div>

                        <div v-show="profileMenuOpen" class="absolute right-0 mt-2 w-56 py-2 bg-white dark:bg-[#0c121e] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 z-50">
                            <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                                <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Signed in as</p>
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate mt-0.5">{{ user?.name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ user?.email }}</p>
                            </div>

                            <div class="py-1 border-b border-slate-100 dark:border-slate-800">
                                <button type="button" @click="passwordModalOpen = true; profileMenuOpen = false" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80 flex items-center gap-2.5 transition">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    <span>Change Password</span>
                                </button>
                            </div>

                            <Link href="/logout" method="post" as="button" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center gap-2.5 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                <span>Sign Out</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area (SPA Instant View Mount) -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7 space-y-6 touch-scroll pb-24 md:pb-8">
                
                <!-- Flash Messages -->
                <div v-if="flash.success" class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-900 dark:text-emerald-200 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                        <span class="text-xs font-semibold">{{ flash.success }}</span>
                    </div>
                </div>

                <div v-if="flash.error" class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-900 dark:text-rose-200 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs shrink-0">⚠️</div>
                        <span class="text-xs font-semibold">{{ flash.error }}</span>
                    </div>
                </div>

                <!-- Page View Slot -->
                <slot />
            </main>

            <!-- PWA Mobile Bottom Navigation Bar (Mobile <= 768px) -->
            <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 glass-bottom-bar border-t border-slate-200/80 dark:border-slate-800/80 pwa-safe-bottom">
                <div class="grid grid-cols-5 items-center justify-around px-2 py-1.5">
                    
                    <!-- 1. Dashboard -->
                    <Link href="/admin/dashboard" @click="sidebarOpen = false" class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold transition" :class="isUrl('/admin/dashboard') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span>Home</span>
                    </Link>

                    <!-- 2. Sales Orders -->
                    <Link v-if="can('sales-orders.view')" href="/admin/sales-orders" @click="sidebarOpen = false" class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold transition" :class="isUrl('/admin/sales-orders') && !isUrl('/admin/sales-orders/bulk-upload') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span>Orders</span>
                    </Link>
                    <div v-else></div>

                    <!-- 3. Floating Action Center Button -->
                    <div class="flex justify-center -mt-5">
                        <button type="button" @click="quickActionSheetOpen = true" class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/40 hover:scale-105 active:scale-95 transition-transform" title="Quick Actions">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>

                    <!-- 4. Bills -->
                    <Link v-if="can('bills.view')" href="/admin/bills" @click="sidebarOpen = false" class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold transition" :class="isUrl('/admin/bills') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Bills</span>
                    </Link>
                    <div v-else></div>

                    <!-- 5. Mobile Drawer Trigger -->
                    <button type="button" @click="sidebarOpen = !sidebarOpen" class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition" :class="sidebarOpen ? 'text-indigo-600 dark:text-indigo-400 font-bold' : ''">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <span>Menu</span>
                    </button>
                </div>
            </nav>
        </div>

        <!-- Quick Actions Bottom Sheet (Mobile & Quick Access) -->
        <Transition
            enter-active-class="transition-opacity ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="quickActionSheetOpen" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex flex-col justify-end p-3 sm:p-4" @click.self="quickActionSheetOpen = false">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-2xl max-w-lg w-full mx-auto space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="font-bold text-sm font-heading text-slate-900 dark:text-white">Quick Actions</h3>
                        <button @click="quickActionSheetOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 text-xs">✕</button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <Link v-if="can('sales-orders.create')" href="/admin/sales-orders/create" @click="quickActionSheetOpen = false" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 border border-slate-200 dark:border-slate-700 transition flex flex-col gap-1">
                            <span class="text-xl">✨</span>
                            <span class="font-bold text-xs text-slate-900 dark:text-white">Create SO</span>
                            <span class="text-[10px] text-slate-400">Add sales order</span>
                        </Link>

                        <Link v-if="can('bills.create')" href="/admin/bills/create" @click="quickActionSheetOpen = false" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 border border-slate-200 dark:border-slate-700 transition flex flex-col gap-1">
                            <span class="text-xl">📷</span>
                            <span class="font-bold text-xs text-slate-900 dark:text-white">Snap Bill</span>
                            <span class="text-[10px] text-slate-400">Capture receipt</span>
                        </Link>

                        <Link v-if="can('upload-sos.create')" href="/admin/upload-sos/create" @click="quickActionSheetOpen = false" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-amber-50 dark:hover:bg-amber-950/50 border border-slate-200 dark:border-slate-700 transition flex flex-col gap-1">
                            <span class="text-xl">🖼️</span>
                            <span class="font-bold text-xs text-slate-900 dark:text-white">Upload SO</span>
                            <span class="text-[10px] text-slate-400">Upload paper slip</span>
                        </Link>

                        <Link v-if="can('sales-orders.bulk-upload')" href="/admin/sales-orders/bulk-upload" @click="quickActionSheetOpen = false" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 hover:bg-violet-50 dark:hover:bg-violet-950/50 border border-slate-200 dark:border-slate-700 transition flex flex-col gap-1">
                            <span class="text-xl">📊</span>
                            <span class="font-bold text-xs text-slate-900 dark:text-white">Bulk Import</span>
                            <span class="text-[10px] text-slate-400">Import CSV/Excel</span>
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Change Password Modal -->
        <Transition
            enter-active-class="transition-opacity ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="passwordModalOpen" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4" @click.self="passwordModalOpen = false">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">🔒</span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">Change Password</h3>
                        </div>
                        <button @click="passwordModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs">✕</button>
                    </div>

                    <form @submit.prevent="submitPasswordChange" class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Current Password *</label>
                            <div class="relative">
                                <input :type="showCurrentPass ? 'text' : 'password'" v-model="passwordForm.current_password" required class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold" />
                                <button type="button" @click="showCurrentPass = !showCurrentPass" class="absolute right-3 top-2.5 text-slate-400 text-xs">
                                    {{ showCurrentPass ? '🙈' : '👁️' }}
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.current_password" class="text-xs text-rose-500 mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">New Password (Min 8 chars) *</label>
                            <div class="relative">
                                <input :type="showNewPass ? 'text' : 'password'" v-model="passwordForm.password" required minlength="8" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold" />
                                <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3 top-2.5 text-slate-400 text-xs">
                                    {{ showNewPass ? '🙈' : '👁️' }}
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password" class="text-xs text-rose-500 mt-1">{{ passwordForm.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Confirm New Password *</label>
                            <div class="relative">
                                <input :type="showConfirmPass ? 'text' : 'password'" v-model="passwordForm.password_confirmation" required minlength="8" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold" />
                                <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-2.5 text-slate-400 text-xs">
                                    {{ showConfirmPass ? '🙈' : '👁️' }}
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="passwordModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold">Cancel</button>
                            <button type="submit" :disabled="passwordForm.processing" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition disabled:opacity-50">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

    </div>
</template>
