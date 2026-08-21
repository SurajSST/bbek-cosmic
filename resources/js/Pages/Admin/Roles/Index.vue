<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    roles: {
        type: Array,
        default: () => []
    },
    totalPermissionsCount: {
        type: Number,
        default: 0
    },
    filters: {
        type: Object,
        default: () => ({ search: '' })
    }
});

const search = ref(props.filters?.search || '');

function handleFilter() {
    router.get('/admin/roles', {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function deleteRole(id, name) {
    if (confirm(`Are you sure you want to delete role "${name}"?`)) {
        router.delete(`/admin/roles/${id}`);
    }
}
</script>

<template>
    <AuthenticatedLayout title="Role Management">
        <Head title="Roles" />

        <div class="space-y-6">
            
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        v-model="search" 
                        @keyup.enter="handleFilter"
                        placeholder="Search role name..." 
                        class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <Link href="/admin/roles/create" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                    <span>New Role</span>
                </Link>
            </div>

            <!-- Role Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div v-for="role in roles" :key="role.id" class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4 hover:border-indigo-500/40 transition">
                    
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 font-mono">Role ID: #{{ role.id }}</span>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                                {{ role.name }}
                            </h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold">
                            {{ role.users_count || 0 }} Users
                        </span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mb-1.5">
                            <span>Permissions:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ role.permissions?.length || 0 }} / {{ totalPermissionsCount }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full transition-all duration-300" :style="`width: ${(role.permissions?.length / (totalPermissionsCount || 1)) * 100}%`"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link :href="`/admin/roles/${role.id}/edit`" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-slate-700 dark:text-slate-300 hover:text-indigo-600 text-xs font-bold transition">
                            Edit Permissions
                        </Link>
                        <button v-if="role.name !== 'Super Admin'" @click="deleteRole(role.id, role.name)" class="px-3 py-1.5 rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-bold transition">
                            Delete
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
