<script setup>
import { ref, onMounted } from 'vue';

defineProps({
    title: {
        type: String,
        default: 'Welcome'
    }
});

const currentTheme = ref(localStorage.getItem('theme') || 'system');
const isDarkActive = ref(true);

function applyTheme(theme) {
    currentTheme.value = theme;
    localStorage.setItem('theme', theme);
    const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
        document.documentElement.classList.add('dark');
        isDarkActive.value = true;
    } else {
        document.documentElement.classList.remove('dark');
        isDarkActive.value = false;
    }
}

function toggleTheme() {
    applyTheme(isDarkActive.value ? 'light' : 'dark');
}

onMounted(() => {
    applyTheme(currentTheme.value);
});
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 bg-slate-50 dark:bg-[#070a12] text-slate-900 dark:text-slate-100 relative overflow-hidden font-sans select-none pwa-safe-top pwa-safe-bottom transition-colors duration-300">
        
        <!-- Glowing Ambient Lighting Mesh Background -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-br from-indigo-500/10 dark:from-indigo-600/20 via-violet-500/10 dark:via-violet-600/15 to-transparent rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-gradient-to-tl from-purple-500/10 dark:from-purple-600/15 via-pink-500/10 dark:via-pink-600/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Right Theme Toggle -->
        <!-- Offset by the safe-area inset: `absolute` resolves against the padding box,
             so the container's pwa-safe-top padding does not push this down on notched
             devices and it would collide with the status bar. -->
        <div class="absolute top-[calc(env(safe-area-inset-top,0px)+1rem)] right-[calc(env(safe-area-inset-right,0px)+1rem)] z-20">
            <button 
                type="button" 
                @click="toggleTheme" 
                class="p-2.5 rounded-2xl bg-white/80 dark:bg-slate-900/80 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition shadow-lg backdrop-blur-md"
                title="Toggle Theme Mode"
            >
                <svg v-if="!isDarkActive" class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <svg v-else class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </button>
        </div>

        <div class="w-full max-w-md relative z-10 space-y-6">
            
            <!-- Brand Logo Header -->
            <div class="text-center space-y-2.5">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 text-white font-black text-2xl shadow-xl shadow-indigo-600/30">
                    C
                </div>
                <div>
                    <h1 class="text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight">
                        Cosmic Bill
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Enterprise Invoicing, Sales Orders & Role Engine
                    </p>
                </div>
            </div>

            <!-- Content Card (Adaptive Light/Dark Container) -->
            <div class="relative bg-white dark:bg-slate-900/85 backdrop-blur-2xl rounded-3xl p-6 sm:p-8 shadow-xl dark:shadow-2xl shadow-slate-200/50 dark:shadow-slate-950/80 border border-slate-200/90 dark:border-slate-800/90 overflow-hidden transition-all duration-300">
                <!-- Top Accent Line -->
                <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-75"></div>
                <slot />
            </div>

            <!-- Footer Copyright -->
            <div class="text-center text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                &copy; {{ new Date().getFullYear() }} Cosmic Bill Inc. All rights reserved.
            </div>
        </div>

    </div>
</template>
