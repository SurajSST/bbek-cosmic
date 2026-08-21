<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Sign In" />

        <form @submit.prevent="submit" class="space-y-4">
            
            <div>
                <label for="email" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5 font-mono">
                    Email Address *
                </label>
                <div class="relative">
                    <input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        required 
                        autofocus 
                        autocomplete="username"
                        class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-950/70 border border-slate-750/80 text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner placeholder-slate-500"
                        placeholder="name@company.com"
                    />
                    <svg class="w-4 h-4 text-indigo-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-400 font-semibold">
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider font-mono">
                        Password *
                    </label>
                </div>
                <div class="relative">
                    <input 
                        id="password" 
                        :type="showPassword ? 'text' : 'password'" 
                        v-model="form.password" 
                        required 
                        autocomplete="current-password"
                        class="w-full pl-10 pr-11 py-3 rounded-2xl bg-slate-950/70 border border-slate-750/80 text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner placeholder-slate-500"
                        placeholder="••••••••"
                    />
                    <svg class="w-4 h-4 text-indigo-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <button 
                        type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-200 transition" 
                        :title="showPassword ? 'Hide password' : 'Show password'"
                    >
                        <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400 font-semibold">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded-md border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500/20" />
                    <span class="text-xs text-slate-400 font-medium">Keep me signed in</span>
                </label>
            </div>

            <div class="pt-2">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="form.processing">Authenticating...</span>
                    <span v-else>Sign In to Workspace &rarr;</span>
                </button>
            </div>

        </form>
    </GuestLayout>
</template>
