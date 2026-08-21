<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    groupedPermissions: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    name: '',
    permissions: []
});

function togglePermission(permName) {
    const idx = form.permissions.indexOf(permName);
    if (idx > -1) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(permName);
    }
}

function selectGroup(perms) {
    const permNames = perms.map(p => p.name);
    const allSelected = permNames.every(name => form.permissions.includes(name));
    if (allSelected) {
        form.permissions = form.permissions.filter(name => !permNames.includes(name));
    } else {
        permNames.forEach(name => {
            if (!form.permissions.includes(name)) form.permissions.push(name);
        });
    }
}

function submit() {
    form.post('/admin/roles');
}
</script>

<template>
    <AuthenticatedLayout title="Create Security Role">
        <Head title="Create Role" />

        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <Link href="/admin/roles" class="text-slate-400 hover:text-indigo-600 transition">Roles</Link>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">Create Role</span>
                </div>
                <Link href="/admin/roles" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    &larr; Back to Roles
                </Link>
            </div>

            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-6">
                
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">
                        Role Information & RBAC Matrix
                    </h3>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Role Name *
                        </label>
                        <input type="text" v-model="form.name" required placeholder="e.g. Sales Manager, Billing Auditor" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 max-w-md" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Permission Groups -->
                    <div class="space-y-5">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Assign Permissions by Module
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60 space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
                                    <span class="font-bold text-xs text-slate-900 dark:text-white uppercase font-mono tracking-wider">{{ groupName }}</span>
                                    <button type="button" @click="selectGroup(perms)" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Toggle All
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 gap-2">
                                    <div v-for="p in perms" :key="p.id" 
                                        @click="togglePermission(p.name)"
                                        class="p-2.5 rounded-xl border transition cursor-pointer flex items-center justify-between text-xs"
                                        :class="form.permissions.includes(p.name) ? 'bg-indigo-50 dark:bg-indigo-950/70 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-bold' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-medium'"
                                    >
                                        <span>{{ p.name }}</span>
                                        <span v-if="form.permissions.includes(p.name)">✓</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link href="/admin/roles" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50">
                            Save Role &rarr;
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
