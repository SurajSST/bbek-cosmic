<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    roles: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: '', role: '' })
    }
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const role = ref(props.filters?.role || '');

function handleFilter() {
    router.get('/admin/users', {
        search: search.value,
        status: status.value,
        role: role.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilter() {
    search.value = '';
    status.value = '';
    role.value = '';
    handleFilter();
}

function deleteUser(id, name) {
    if (confirm(`Are you sure you want to delete user "${name}"?`)) {
        router.delete(`/admin/users/${id}`);
    }
}
</script>

<template>
    <AuthenticatedLayout title="User Management">
        <Head title="Users" />

        <div class="space-y-6">
            
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 max-w-2xl">
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            v-model="search" 
                            @keyup.enter="handleFilter"
                            placeholder="Search name, email..." 
                            class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <select 
                        v-model="status" 
                        @change="handleFilter"
                        class="px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <select 
                        v-model="role" 
                        @change="handleFilter"
                        class="px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Roles</option>
                        <option v-for="(rName, rKey) in roles" :key="rKey" :value="rKey">{{ rName }}</option>
                    </select>

                    <button @click="handleFilter" class="px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">Filter</button>
                    <button v-if="search || status || role" @click="resetFilter" class="px-3 py-2.5 text-rose-500 text-xs font-semibold">Reset</button>
                </div>

                <Link href="/admin/users/create" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                    <span>New User</span>
                </Link>

            </div>

            <div class="glass-card rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800/80">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                                <th class="px-5 py-3.5">User</th>
                                <th class="px-5 py-3.5">Email</th>
                                <th class="px-5 py-3.5">Assigned Roles</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5">Registered</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                                            {{ u.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ u.name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-500 dark:text-slate-400">
                                    {{ u.email }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="r in u.roles" :key="r.id" class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50 text-[10px] font-bold">
                                            {{ r.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase"
                                        :class="u.status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'">
                                        {{ u.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-400">
                                    {{ u.created_at ? new Date(u.created_at).toLocaleDateString() : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/admin/users/${u.id}/edit`" class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-950 text-amber-600 dark:text-amber-400 font-bold transition">
                                            ✏️
                                        </Link>
                                        <button @click="deleteUser(u.id, u.name)" class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950 text-rose-600 dark:text-rose-400 font-bold transition">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="users.links && users.links.length > 3" class="px-5 py-3.5 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-[11px] text-slate-500">
                        Showing page {{ users.current_page }} of {{ users.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in users.links" :key="i">
                            <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded-xl text-xs font-semibold transition"
                                :class="link.active ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                                v-html="link.label">
                            </Link>
                            <span v-else class="px-2 py-1 text-slate-300 dark:text-slate-700 text-xs" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
