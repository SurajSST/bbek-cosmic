<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    groupedPermissions: {
        type: Object,
        default: () => ({})
    },
    modulesList: {
        type: Object,
        default: () => ({})
    },
    protectedPermissions: {
        type: Array,
        default: () => []
    },
    totalCount: {
        type: Number,
        default: 0
    },
    filters: {
        type: Object,
        default: () => ({ search: '', module: 'all' })
    }
});

const search = ref(props.filters?.search || '');
const activeModule = ref(props.filters?.module || 'all');
const modalOpen = ref(false);

const form = useForm({
    name: ''
});

function handleFilter() {
    router.get('/admin/permissions', {
        search: search.value,
        module: activeModule.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function setModule(m) {
    activeModule.value = m;
    handleFilter();
}

function deletePermission(id, name) {
    if (confirm(`Are you sure you want to delete permission "${name}"?`)) {
        router.delete(`/admin/permissions/${id}`);
    }
}

function createPermission() {
    form.post('/admin/permissions', {
        onSuccess: () => {
            modalOpen.value = false;
            form.reset();
        }
    });
}
</script>

<template>
    <AuthenticatedLayout title="Permission Registry">
        <Head title="Permissions" />

        <div class="space-y-6">
            
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        v-model="search" 
                        @keyup.enter="handleFilter"
                        placeholder="Search permission token..." 
                        class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <button @click="modalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
                    <span>New Permission</span>
                </button>
            </div>

            <!-- Module Navigation Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <button 
                    @click="setModule('all')" 
                    class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap"
                    :class="activeModule === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100'"
                >
                    All Modules ({{ totalCount }})
                </button>
                <button 
                    v-for="(count, mod) in modulesList" 
                    :key="mod"
                    @click="setModule(mod)" 
                    class="px-3.5 py-1.5 rounded-xl font-semibold transition whitespace-nowrap"
                    :class="activeModule.toLowerCase() === mod.toLowerCase() ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100'"
                >
                    {{ mod }} ({{ count }})
                </button>
            </div>

            <!-- Module Group Panels -->
            <div class="space-y-6">
                <div v-for="(perms, modName) in groupedPermissions" :key="modName" class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">{{ modName }} Permissions</h3>
                        </div>
                        <span class="text-[10px] font-mono font-bold text-slate-400">{{ perms.length }} tokens</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div v-for="p in perms" :key="p.id" class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                            <div class="space-y-0.5">
                                <span class="block font-mono text-xs font-bold text-slate-900 dark:text-white">{{ p.name }}</span>
                                <span class="block text-[10px] text-slate-400">{{ p.roles_count || 0 }} roles granted</span>
                            </div>
                            <button 
                                v-if="!protectedPermissions.includes(p.name)"
                                @click="deletePermission(p.id, p.name)"
                                class="p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg text-xs"
                                title="Delete Permission"
                            >
                                ✕
                            </button>
                            <span v-else class="text-[10px] font-bold text-slate-400 bg-slate-200/60 dark:bg-slate-700/60 px-1.5 py-0.5 rounded">Core</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Create Permission Modal -->
        <div v-if="modalOpen" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">Register New Permission</h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs">✕</button>
                </div>

                <form @submit.prevent="createPermission" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">
                            Permission Name (e.g. reports.export)
                        </label>
                        <input type="text" v-model="form.name" required placeholder="module.action" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition disabled:opacity-50">Create Permission</button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
