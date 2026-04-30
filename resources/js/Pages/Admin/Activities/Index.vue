<!-- resources/js/Pages/Admin/Activities/Index.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Activity Logs</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track all system activities</p>
                </div>
                <div class="flex gap-2">
                    <button @click="exportActivities" 
                        class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-download"></i>
                        Export
                    </button>
                    <button @click="clearActivities" v-if="activities.data?.length" 
                        class="inline-flex items-center gap-2 px-4 py-2 border border-red-200 dark:border-red-900/50 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">
                        <i class="fa-solid fa-trash"></i>
                        Clear Old
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Total Activities</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Today</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ stats.today }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">This Week</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ stats.this_week }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">This Month</p>
                        <p class="text-2xl font-bold text-violet-600">{{ stats.this_month }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 mb-6 border border-slate-200 dark:border-slate-700">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input v-model="filters.search" type="text" placeholder="Search user..." 
                                @keyup.enter="applyFilters"
                                class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                        </div>
                        <select v-model="filters.user_id" @change="applyFilters" 
                            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            <option value="">All Users</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <select v-model="filters.action" @change="applyFilters" 
                            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            <option value="">All Actions</option>
                            <option v-for="action in actions" :key="action" :value="action">{{ action.replace('_', ' ') }}</option>
                        </select>
                        <div class="flex gap-2">
                            <button @click="applyFilters" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold">
                                Apply
                            </button>
                            <button @click="resetFilters" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Activities Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="activity in activities.data" :key="activity.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                                <span class="text-xs font-bold text-indigo-600">{{ activity.user?.name?.charAt(0) || 'S' }}</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ activity.user?.name || 'System' }}</p>
                                                <p class="text-xs text-slate-500">{{ activity.user?.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="getActionClass(activity.action)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ activity.action.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs">
                                            <p class="text-sm text-slate-700 dark:text-slate-300">{{ formatDetails(activity.details) }}</p>
                                            <p v-if="activity.entity_type" class="text-xs text-slate-500 mt-1">{{ activity.entity_type }} #{{ activity.entity_id }}</p>
                                        </div>
                                     </td>
                                    <td class="px-6 py-4">
                                        <code class="text-xs bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">{{ activity.ip_address || 'N/A' }}</code>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ formatDate(activity.created_at) }}</span>
                                            <span class="text-xs text-slate-500">{{ timeAgo(activity.created_at) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                        <Pagination :links="activities.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Clear Activities Modal -->
        <Teleport to="body">
            <div v-if="showClearModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showClearModal = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Clear Old Activities</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Delete activities older than:</p>
                        <select v-model="clearDays" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                        <button @click="showClearModal = false" class="flex-1 px-4 py-2 border rounded-xl">Cancel</button>
                        <button @click="performClear" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold">
                            Clear Activities
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    activities: Object,
    users: Array,
    actions: Array,
    stats: Object,
    filters: Object
})

const showClearModal = ref(false)
const clearDays = ref(90)

const filters = reactive({
    search: props.filters.search || '',
    user_id: props.filters.user_id || '',
    action: props.filters.action || ''
})

const getActionClass = (action) => {
    if (action.includes('created')) return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
    if (action.includes('updated')) return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
    if (action.includes('deleted')) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
    if (action.includes('assigned')) return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
    return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-400'
}

const formatDetails = (details) => {
    if (!details) return 'No additional details'
    if (typeof details === 'string') return details
    if (details.report_title) return `Report: ${details.report_title}`
    if (details.user_name) return `User: ${details.user_name}`
    if (details.task_title) return `Task: ${details.task_title}`
    return JSON.stringify(details)
}

const formatDate = (date) => {
    return new Date(date).toLocaleString()
}

const timeAgo = (date) => {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000)
    const intervals = [
        { label: 'year', seconds: 31536000 },
        { label: 'month', seconds: 2592000 },
        { label: 'week', seconds: 604800 },
        { label: 'day', seconds: 86400 },
        { label: 'hour', seconds: 3600 },
        { label: 'minute', seconds: 60 }
    ]
    for (const interval of intervals) {
        const count = Math.floor(seconds / interval.seconds)
        if (count >= 1) {
            return `${count} ${interval.label}${count !== 1 ? 's' : ''} ago`
        }
    }
    return 'just now'
}

const applyFilters = () => {
    router.get(route('admin.activities.index'), filters, { preserveState: true })
}

const resetFilters = () => {
    filters.search = ''
    filters.user_id = ''
    filters.action = ''
    applyFilters()
}

const exportActivities = () => {
    window.open(route('admin.activities.export', filters), '_blank')
}

const clearActivities = () => {
    showClearModal.value = true
}

const performClear = () => {
    router.delete(route('admin.activities.clear'), {
        data: { days: clearDays.value },
        onSuccess: () => {
            showClearModal.value = false
        }
    })
}
</script>