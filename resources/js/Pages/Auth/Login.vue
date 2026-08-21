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
                <label for="email" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <input 
                    id="email" 
                    type="email" 
                    v-model="form.email" 
                    required 
                    autofocus 
                    class="w-full px-4 py-3 rounded-2xl bg-slate-900/80 border border-slate-800 text-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 transition shadow-inner placeholder-slate-500"
                    placeholder="name@company.com"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-400 font-medium">
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                        Password
                    </label>
                </div>
                <div class="relative">
                    <input 
                        id="password" 
                        :type="showPassword ? 'text' : 'password'" 
                        v-model="form.password" 
                        required 
                        class="w-full pl-4 pr-11 py-3 rounded-2xl bg-slate-900/80 border border-slate-800 text-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 transition shadow-inner placeholder-slate-500"
                        placeholder="••••••••"
                    />
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-3 text-slate-400 hover:text-slate-200 text-xs font-bold">
                        <span v-if="!showPassword">👁️</span>
                        <span v-else>🙈</span>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400 font-medium">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded-md border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500/20" />
                    <span class="text-xs text-slate-400 font-medium">Remember session</span>
                </label>
            </div>

            <div class="pt-2">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50"
                >
                    <span v-if="form.processing">Authenticating...</span>
                    <span v-else>Sign In to Workspace &rarr;</span>
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
