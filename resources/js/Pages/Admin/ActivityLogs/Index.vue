<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    activityLogs: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    distinctActions: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({ search: '', action: '' })
    }
});

const search = ref(props.filters?.search || '');
const action = ref(props.filters?.action || '');
const activeLogModal = ref(null);

function handleFilter() {
    router.get('/admin/activity-logs', {
        search: search.value,
        action: action.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilter() {
    search.value = '';
    action.value = '';
    handleFilter();
}
</script>

<template>
    <AuthenticatedLayout title="System Activity Logs">
        <Head title="Activity Logs" />

        <div class="space-y-6">
            
            <div class="glass-card rounded-3xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 max-w-2xl">
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            v-model="search" 
                            @keyup.enter="handleFilter"
                            placeholder="Search description, user, action..." 
                            class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <select 
                        v-model="action" 
                        @change="handleFilter"
                        class="px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Actions</option>
                        <option v-for="act in distinctActions" :key="act" :value="act">{{ act }}</option>
                    </select>

                    <button @click="handleFilter" class="px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">Filter</button>
                    <button v-if="search || action" @click="resetFilter" class="px-3 py-2.5 text-rose-500 text-xs font-semibold">Reset</button>
                </div>
            </div>

            <div class="glass-card rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800/80">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                                <th class="px-5 py-3.5">Timestamp</th>
                                <th class="px-5 py-3.5">User</th>
                                <th class="px-5 py-3.5">Action</th>
                                <th class="px-5 py-3.5">Subject</th>
                                <th class="px-5 py-3.5">Description</th>
                                <th class="px-5 py-3.5 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <tr v-for="log in activityLogs.data" :key="log.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ log.created_at ? new Date(log.created_at).toLocaleString() : '—' }}
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ log.user?.name || log.user_name || 'System' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50 text-[10px] font-bold font-mono">
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                    {{ log.subject_type ? log.subject_type.split('\\').pop() : 'System' }} #{{ log.subject_id || '—' }}
                                </td>
                                <td class="px-5 py-4 text-slate-700 dark:text-slate-300 font-medium">
                                    {{ log.description }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button v-if="log.properties" @click="activeLogModal = log" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-indigo-500 font-bold transition">
                                        🔍 Inspect
                                    </button>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                            </tr>
                            <tr v-if="!activityLogs.data || activityLogs.data.length === 0">
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400 text-xs">
                                    No activity logs recorded.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="activityLogs.links && activityLogs.links.length > 3" class="px-5 py-3.5 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-[11px] text-slate-500">
                        Showing page {{ activityLogs.current_page }} of {{ activityLogs.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in activityLogs.links" :key="i">
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

        <!-- JSON Properties Modal -->
        <div v-if="activeLogModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base font-heading">
                        Audit Properties: {{ activeLogModal.action }}
                    </h3>
                    <button @click="activeLogModal = null" class="text-slate-400 hover:text-slate-600 text-xs">✕</button>
                </div>

                <div class="bg-slate-950 text-emerald-400 font-mono text-[11px] p-4 rounded-2xl overflow-x-auto max-h-80">
                    <pre>{{ JSON.stringify(activeLogModal.properties, null, 2) }}</pre>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button @click="activeLogModal = null" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold">Close</button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
