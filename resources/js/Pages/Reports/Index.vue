<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">My Reports</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ stats.total }} reports total</p>
                </div>
                <Link :href="route('reports.create')"
                    class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-indigo-200 dark:shadow-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Report
                </Link>
            </div>
        </template>

        <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Stats row -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div v-for="s in statCards" :key="s.label"
                        class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                        <div class="text-2xl font-bold" :class="s.color">{{ s.value }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ s.label }}</div>
                    </div>
                </div>

                <!-- Search + filter toolbar -->
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input v-model="searchQ" @input="debouncedSearch" placeholder="Search reports…"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    </div>
                    <div class="flex items-center gap-2">
                        <button v-for="s in ['all','draft','published','archived']" :key="s"
                            @click="filterStatus = s; loadReports()"
                            :class="filterStatus===s ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300'"
                            class="px-3 py-1.5 text-xs font-medium rounded-full capitalize transition-all">{{ s }}</button>
                    </div>
                    <select v-model="sortBy" @change="loadReports"
                        class="text-xs bg-white dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 focus:outline-none">
                        <option value="updated_at">Last Modified</option>
                        <option value="created_at">Date Created</option>
                        <option value="title">Title A–Z</option>
                    </select>
                    <!-- View toggle -->
                    <div class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1 ml-auto">
                        <button v-for="v in ['grid','list']" :key="v" @click="viewMode=v"
                            :class="viewMode===v ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'"
                            class="p-1.5 rounded-lg transition-all">
                            <svg v-if="v==='grid'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Grid view -->
                <div v-if="viewMode==='grid' && reports.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <div v-for="report in reports.data" :key="report.id"
                        class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg dark:hover:shadow-slate-900 transition-all overflow-hidden">
                        <!-- Thumbnail area -->
                        <div class="h-36 bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20 relative overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center opacity-30">
                                <svg class="w-20 h-20 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="absolute top-2.5 right-2.5">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full capitalize"
                                    :class="statusBadge(report.status)">{{ report.status }}</span>
                            </div>
                            <!-- Quick actions overlay -->
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                <Link :href="route('reports.edit', report.slug)"
                                    class="bg-white text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow hover:bg-indigo-50 transition-colors">
                                    Edit
                                </Link>
                                <Link :href="route('reports.preview', report.slug)" target="_blank"
                                    class="bg-indigo-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow hover:bg-indigo-700 transition-colors">
                                    Preview
                                </Link>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900 dark:text-white text-sm truncate">{{ report.title }}</h3>
                            <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-400 dark:text-slate-500">
                                <span>{{ formatDate(report.updated_at) }}</span>
                                <span v-if="report.template">· {{ report.template.name }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex gap-1">
                                    <Link :href="route('reports.edit', report.slug)" class="icon-btn" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </Link>
                                    <Link :href="route('reports.preview', report.slug)" target="_blank" class="icon-btn" title="Preview">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </Link>
                                    <a :href="route('reports.download', report.slug)" class="icon-btn" title="Download PDF">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </div>
                                <button @click="confirmDelete(report)" class="icon-btn text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List view -->
                <div v-else-if="viewMode==='list' && reports.data.length" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div v-for="report in reports.data" :key="report.id"
                        class="flex items-center gap-4 px-5 py-3.5 border-b border-slate-50 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors group">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                            :class="statusIconBg(report.status)">
                            <svg class="w-4 h-4" :class="statusIconColor(report.status)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-slate-900 dark:text-white truncate">{{ report.title }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-2 mt-0.5">
                                <span>{{ formatDate(report.updated_at) }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600 inline-block"/>
                                <span class="capitalize px-1.5 py-0.5 rounded text-[10px] font-semibold" :class="statusBadge(report.status)">{{ report.status }}</span>
                                <span v-if="report.template" class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600 inline-block"/>
                                <span v-if="report.template">{{ report.template.name }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Link :href="route('reports.edit', report.slug)" class="icon-btn" title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </Link>
                            <Link :href="route('reports.preview', report.slug)" target="_blank" class="icon-btn" title="Preview">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </Link>
                            <a :href="route('reports.download', report.slug)" class="icon-btn" title="PDF">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            <button @click="duplicateReport(report)" class="icon-btn" title="Duplicate">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                            <button @click="confirmDelete(report)" class="icon-btn text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="!reports.data.length" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 py-20 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="font-semibold text-slate-500 dark:text-slate-400 mb-1">No reports found</p>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mb-5">{{ searchQ ? 'Try a different search term' : 'Create your first report to get started' }}</p>
                    <Link :href="route('reports.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Report
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="reports.last_page > 1" class="flex items-center justify-between mt-6">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Showing {{ reports.from }}–{{ reports.to }} of {{ reports.total }}
                    </p>
                    <div class="flex gap-1">
                        <Link v-for="link in reports.links" :key="link.label"
                            :href="link.url || '#'"
                            :class="[link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700', !link.url ? 'opacity-40 cursor-default' : '']"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                            v-html="link.label"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirm modal -->
        <Teleport to="body">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteTarget=null"/>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 w-full max-w-sm z-10">
                    <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h3 class="text-center font-bold text-slate-900 dark:text-white mb-1">Delete Report?</h3>
                    <p class="text-center text-sm text-slate-500 dark:text-slate-400 mb-5">"{{ deleteTarget?.title }}" will be permanently deleted.</p>
                    <div class="flex gap-3">
                        <button @click="deleteTarget=null" class="flex-1 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                        <button @click="deleteReport" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition-colors">Delete</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    reports: Object,
    stats: { type: Object, default: () => ({ total:0, published:0, draft:0, archived:0 }) }
});

const viewMode    = ref('list');
const searchQ     = ref('');
const filterStatus= ref('all');
const sortBy      = ref('updated_at');
const deleteTarget= ref(null);

const statCards = computed(() => [
    { label:'Total',     value: props.stats.total,     color:'text-indigo-600 dark:text-indigo-400' },
    { label:'Published', value: props.stats.published, color:'text-emerald-600 dark:text-emerald-400' },
    { label:'Drafts',    value: props.stats.draft,     color:'text-amber-600 dark:text-amber-400' },
    { label:'Archived',  value: props.stats.archived,  color:'text-slate-500 dark:text-slate-400' },
]);

let searchTimer = null;
const debouncedSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadReports, 400); };

const loadReports = () => {
    router.get(route('reports.index'), {
        search: searchQ.value || undefined,
        status: filterStatus.value !== 'all' ? filterStatus.value : undefined,
        sort:   sortBy.value,
    }, { preserveState: true, preserveScroll: true });
};

const statusBadge = (s) => ({
    draft:     'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
    published: 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
    archived:  'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
}[s] ?? 'bg-slate-100 text-slate-600');
const statusIconBg    = s => ({ draft:'bg-amber-100 dark:bg-amber-900/30', published:'bg-emerald-100 dark:bg-emerald-900/30', archived:'bg-slate-100 dark:bg-slate-700' }[s]??'bg-slate-100');
const statusIconColor = s => ({ draft:'text-amber-600', published:'text-emerald-600', archived:'text-slate-500' }[s]??'text-slate-500');

const formatDate = (d) => {
    const diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60)    return 'just now';
    if (diff < 3600)  return `${Math.floor(diff/60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
    if (diff < 604800)return `${Math.floor(diff/86400)}d ago`;
    return new Date(d).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
};

const confirmDelete = (r) => { deleteTarget.value = r; };
const deleteReport  = () => {
    if (!deleteTarget.value) return;
    router.delete(route('reports.destroy', deleteTarget.value.slug), {
        onFinish: () => { deleteTarget.value = null; }
    });
};
const duplicateReport = (r) => {
    router.post(route('reports.duplicate', r.slug));
};
</script>

<style scoped>
.icon-btn { @apply p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors; }
</style>