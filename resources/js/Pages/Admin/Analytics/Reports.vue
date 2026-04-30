<!-- resources/js/Pages/Admin/Analytics/Reports.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Reports Analytics</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Detailed report statistics</p>
                </div>
                <div class="flex gap-2">
                    <button @click="exportData" 
                        class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-download"></i>
                        Export CSV
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Total Pages</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_pages }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Avg Pages/Report</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.avg_pages_per_report }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Total Shares</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_shares }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Shared Reports</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.reports_with_shares }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 mb-6 border border-slate-200 dark:border-slate-700">
                    <div class="flex flex-wrap gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input v-model="filters.search" type="text" placeholder="Search reports..." 
                                    @keyup.enter="applyFilters"
                                    class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            </div>
                        </div>
                        <select v-model="filters.status" @change="applyFilters" 
                            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                        <select v-model="filters.sort" @change="applyFilters"
                            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            <option value="created_at">Date Created</option>
                            <option value="updated_at">Last Modified</option>
                            <option value="title">Title</option>
                        </select>
                        <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold">
                            Apply
                        </button>
                        <button @click="resetFilters" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Reports Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Report</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Author</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Pages</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Shares</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Created</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="report in reports.data" :key="report.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-file-lines text-slate-400"></i>
                                            <span class="font-medium text-slate-900 dark:text-white">{{ report.title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                                <span class="text-xs font-bold text-indigo-600">{{ report.user_name?.charAt(0) }}</span>
                                            </div>
                                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ report.user_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="getStatusClass(report.status)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                                            {{ report.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ report.pages }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-share-alt text-slate-400 text-xs"></i>
                                            <span class="text-slate-600 dark:text-slate-300">{{ report.shares }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ formatDate(report.created_at) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('reports.edit', report.slug)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 text-sm">
                                            View Report
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                        <Pagination :links="reports.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    reports: Object,
    summary: Object,
    filters: Object
})

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    sort: props.filters.sort || 'created_at'
})

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        published: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        archived: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-400'
    }
    return classes[status] || classes.draft
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}

const applyFilters = () => {
    router.get(route('admin.analytics.reports'), filters, { preserveState: true })
}

const resetFilters = () => {
    filters.search = ''
    filters.status = ''
    filters.sort = 'created_at'
    applyFilters()
}

const exportData = () => {
    window.open(route('admin.analytics.export', { type: 'reports', ...filters }), '_blank')
}
</script>