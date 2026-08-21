<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: 'Welcome'
    }
});
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 bg-[#070a12] text-slate-100 relative overflow-hidden font-sans select-none">
        
        <!-- Glowing Ambient Lighting Background -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10 space-y-6">
            
            <!-- Brand Logo Header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 text-white font-black text-2xl shadow-xl shadow-indigo-600/30">
                    C
                </div>
                <h1 class="text-2xl font-black font-heading text-white tracking-tight">
                    Cosmic Bill
                </h1>
                <p class="text-xs text-slate-400 font-medium">
                    Enterprise Invoicing, Sales Orders & Role Engine
                </p>
            </div>

            <!-- Content Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl border border-slate-800/80">
                <slot />
            </div>

            <!-- Footer Copyright -->
            <div class="text-center text-[11px] text-slate-500">
                &copy; {{ new Date().getFullYear() }} Cosmic Bill Inc. All rights reserved.
            </div>
        </div>

    </div>
</template>
