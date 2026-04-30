<!-- resources/js/Pages/Admin/Analytics/Users.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Users Analytics</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Detailed user statistics and activity</p>
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
                        <p class="text-xs text-slate-500 mb-1">Total Users</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_users }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Users with Reports</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.users_with_reports }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Users with Tasks</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.users_with_tasks }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 mb-1">Avg Reports/User</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.avg_reports_per_user }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 mb-6 border border-slate-200 dark:border-slate-700">
                    <div class="flex flex-wrap gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input v-model="filters.search" type="text" placeholder="Search users..." 
                                    @keyup.enter="applyFilters"
                                    class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            </div>
                        </div>
                        <select v-model="filters.role" @change="applyFilters" 
                            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            <option value="">All Roles</option>
                            <option v-for="role in roles" :key="role.name" :value="role.name">{{ role.name }}</option>
                        </select>
                        <select v-model="filters.sort" @change="applyFilters"
                            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            <option value="created_at">Date Joined</option>
                            <option value="reports_count">Most Reports</option>
                            <option value="tasks_assigned">Most Tasks</option>
                            <option value="name">Name</option>
                        </select>
                        <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold">
                            Apply
                        </button>
                        <button @click="resetFilters" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Reports</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Tasks</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Last Activity</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
                                                <p class="text-xs text-slate-500">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-file-lines text-indigo-500 text-sm"></i>
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ user.reports_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-tasks text-amber-500 text-sm"></i>
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ user.tasks_assigned }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-clock text-slate-400 text-xs"></i>
                                            <span class="text-sm text-slate-500">{{ user.last_activity ? timeAgo(user.last_activity) : 'Never' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 text-sm">
                                            View Profile
                                        </Link>
                                     </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                        <Pagination :links="users.links" />
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
    users: Object,
    summary: Object,
    roles: Array,
    filters: Object
})

const filters = reactive({
    search: props.filters.search || '',
    role: props.filters.role || '',
    sort: props.filters.sort || 'created_at'
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
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
    router.get(route('admin.analytics.users'), filters, { preserveState: true })
}

const resetFilters = () => {
    filters.search = ''
    filters.role = ''
    filters.sort = 'created_at'
    applyFilters()
}

const exportData = () => {
    window.open(route('admin.analytics.export', { type: 'users', ...filters }), '_blank')
}
</script>