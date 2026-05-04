<!--
  Admin/Analytics/Users.vue - Users Analytics Page
  -----------------------------------------------------------
  Detailed analytics for all users in the system.
  Features: Summary cards, search, filter by role, sort, export CSV.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Users Analytics</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Detailed user statistics and activity</p>
        </div>
        <button @click="exportData" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-download text-xs"></i> Export CSV
        </button>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Summary Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">Total Users</p><p class="text-xl sm:text-2xl font-bold">{{ summary.total_users }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">Users with Reports</p><p class="text-xl sm:text-2xl font-bold">{{ summary.users_with_reports }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">Users with Tasks</p><p class="text-xl sm:text-2xl font-bold">{{ summary.users_with_tasks }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">Avg Reports/User</p><p class="text-xl sm:text-2xl font-bold">{{ summary.avg_reports_per_user }}</p></div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border">
        <div class="flex flex-wrap gap-2 sm:gap-3">
          <div class="flex-1 min-w-[150px]"><div class="relative"><i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i><input v-model="filters.search" type="text" placeholder="Search users..." @keyup.enter="applyFilters" class="w-full pl-8 pr-3 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"></div></div>
          <select v-model="filters.role" @change="applyFilters" class="px-2 sm:px-3 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"><option value="">All Roles</option><option v-for="role in roles" :key="role.name" :value="role.name">{{ role.name }}</option></select>
          <select v-model="filters.sort" @change="applyFilters" class="px-2 sm:px-3 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"><option value="created_at">Date Joined</option><option value="reports_count">Most Reports</option><option value="tasks_assigned">Most Tasks</option><option value="name">Name</option></select>
          <button @click="applyFilters" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 text-white rounded-xl text-xs sm:text-sm font-semibold">Apply</button>
          <button @click="resetFilters" class="px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl text-xs sm:text-sm">Reset</button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase">User</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden sm:table-cell">Reports</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden sm:table-cell">Tasks</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden md:table-cell">Joined</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden lg:table-cell">Last Activity</th>
                <th class="px-3 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-semibold uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-xs">{{ user.name.charAt(0).toUpperCase() }}</div>
                    <div class="min-w-0"><p class="font-medium text-xs sm:text-sm truncate">{{ user.name }}</p><p class="text-[10px] sm:text-xs text-slate-500 truncate">{{ user.email }}</p></div>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm hidden sm:table-cell">{{ user.reports_count }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm hidden sm:table-cell">{{ user.tasks_assigned }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500 hidden md:table-cell">{{ formatDate(user.created_at) }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm hidden lg:table-cell">{{ user.last_activity ? timeAgo(user.last_activity) : 'Never' }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                  <Link :href="route('admin.users.show', user.id)" class="text-indigo-600 hover:text-indigo-800 text-[10px] sm:text-xs font-medium">View Profile</Link>
                </td>
              </tr>
              <tr v-if="!users.data?.length"><td colspan="6" class="py-12 text-center text-slate-400">No users found.</td></tr>
            </tbody>
          </table>
        </div>
        <div v-if="users.links?.length > 3" class="px-3 sm:px-6 py-3 border-t"><Pagination :links="users.links" :from="users.from" :to="users.to" :total="users.total" /></div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({ users: Object, summary: Object, roles: Array, filters: Object })

const filters = reactive({ search: props.filters?.search || '', role: props.filters?.role || '', sort: props.filters?.sort || 'created_at' })

const formatDate = (date) => date ? new Date(date).toLocaleDateString() : 'N/A'
const timeAgo = (date) => {
  if (!date) return 'Never'
  const seconds = Math.floor((Date.now() - new Date(date)) / 1000)
  if (seconds < 60) return 'Just now'
  if (seconds < 3600) return `${Math.floor(seconds/60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds/3600)}h ago`
  return `${Math.floor(seconds/86400)}d ago`
}

const applyFilters = () => router.get(route('admin.analytics.users'), filters, { preserveState: true })
const resetFilters = () => { filters.search = ''; filters.role = ''; filters.sort = 'created_at'; applyFilters() }
const exportData = () => window.open(route('admin.analytics.export', { type: 'users', ...filters }), '_blank')
</script>