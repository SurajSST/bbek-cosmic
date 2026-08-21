<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    roles: {
        type: Array,
        default: () => []
    },
    userRoles: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    password: '',
    status: props.user.status,
    roles: [...props.userRoles]
});

function toggleRole(roleName) {
    const idx = form.roles.indexOf(roleName);
    if (idx > -1) {
        form.roles.splice(idx, 1);
    } else {
        form.roles.push(roleName);
    }
}

function submit() {
    form.post(`/admin/users/${props.user.id}`);
}
</script>

<template>
    <AuthenticatedLayout :title="`Edit User: ${user.name}`">
        <Head :title="`Edit User: ${user.name}`" />

        <div class="max-w-3xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/users" class="text-slate-400 hover:text-indigo-600 transition">Users</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Edit {{ user.name }}</span>
                </div>
                <Link href="/admin/users" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; Back
                </Link>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-6">
                
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">
                        Edit User Account
                    </h3>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Full Name *
                            </label>
                            <input type="text" v-model="form.name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Email Address *
                            </label>
                            <input type="email" v-model="form.email" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.email" class="text-xs text-rose-500 mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                New Password (Leave blank to keep unchanged)
                            </label>
                            <input type="password" v-model="form.password" minlength="8" placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="form.errors.password" class="text-xs text-rose-500 mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                                Account Status
                            </label>
                            <select v-model="form.status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Role Assignment -->
                    <div class="space-y-2 pt-2">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Assigned Roles
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <div v-for="r in roles" :key="r.id" 
                                @click="toggleRole(r.name)"
                                class="p-3 rounded-2xl border transition cursor-pointer flex items-center justify-between"
                                :class="form.roles.includes(r.name) ? 'bg-indigo-50 dark:bg-indigo-950/70 border-indigo-500 text-indigo-700 dark:text-indigo-300' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                            >
                                <span class="text-xs font-bold">{{ r.name }}</span>
                                <span v-if="form.roles.includes(r.name)" class="text-xs font-black">✓</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link href="/admin/users" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50">
                            Update Account &rarr;
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
