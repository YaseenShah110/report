<!--
  Admin/Activities/Index.vue - Activity Logs Page
  -----------------------------------------------------------
  Displays all system activity logs for administrators.
  Features: Search, filter by user/action, clear old logs, export CSV.
  Responsive table with color-coded action badges.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Activity Logs</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Track all system activities</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="exportActivities" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-download text-xs"></i> Export
          </button>
          <button @click="clearActivities" v-if="activities.data?.length" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border border-red-200 dark:border-red-900/50 rounded-xl text-xs sm:text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
            <i class="fa-solid fa-trash text-xs"></i> Clear Old
          </button>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">Total Activities</p><p class="text-xl sm:text-2xl font-bold">{{ stats.total }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">Today</p><p class="text-xl sm:text-2xl font-bold text-indigo-600">{{ stats.today }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">This Week</p><p class="text-xl sm:text-2xl font-bold text-emerald-600">{{ stats.this_week }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border"><p class="text-[10px] sm:text-xs text-slate-500 mb-1">This Month</p><p class="text-xl sm:text-2xl font-bold text-violet-600">{{ stats.this_month }}</p></div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 sm:gap-3">
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input v-model="filters.search" type="text" placeholder="Search user..." @keyup.enter="applyFilters" class="w-full pl-8 pr-3 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
          </div>
          <select v-model="filters.user_id" @change="applyFilters" class="px-3 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
            <option value="">All Users</option>
            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
          </select>
          <select v-model="filters.action" @change="applyFilters" class="px-3 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
            <option value="">All Actions</option>
            <option v-for="action in actions" :key="action" :value="action">{{ action.replace('_', ' ') }}</option>
          </select>
          <div class="flex gap-2">
            <button @click="applyFilters" class="flex-1 px-4 py-2 sm:py-2.5 bg-indigo-600 text-white rounded-xl text-xs sm:text-sm font-semibold">Apply</button>
            <button @click="resetFilters" class="flex-1 px-4 py-2 sm:py-2.5 border rounded-xl text-xs sm:text-sm">Reset</button>
          </div>
        </div>
      </div>

      <!-- Activities Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase">User</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase">Action</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden md:table-cell">Details</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden lg:table-cell">IP Address</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase">Timestamp</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="activity in activities.data" :key="activity.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                      <span class="text-[10px] sm:text-xs font-bold text-indigo-600">{{ activity.user?.name?.charAt(0) || 'S' }}</span>
                    </div>
                    <div class="min-w-0"><p class="text-xs sm:text-sm font-medium truncate">{{ activity.user?.name || 'System' }}</p><p class="text-[10px] text-slate-500 truncate hidden sm:block">{{ activity.user?.email }}</p></div>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <span :class="getActionClass(activity.action)" class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full whitespace-nowrap">{{ activity.action.replace('_', ' ') }}</span>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell"><p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 max-w-xs truncate">{{ formatDetails(activity.details) }}</p></td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden lg:table-cell"><code class="text-[10px] sm:text-xs bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">{{ activity.ip_address || 'N/A' }}</code></td>
                <td class="px-3 sm:px-6 py-3 sm:py-4"><span class="text-xs sm:text-sm">{{ formatDate(activity.created_at) }}</span><span class="text-[10px] text-slate-500 block">{{ timeAgo(activity.created_at) }}</span></td>
              </tr>
              <tr v-if="!activities.data?.length"><td colspan="5" class="py-12 text-center text-slate-400">No activities found.</td></tr>
            </tbody>
          </table>
        </div>
        <div v-if="activities.links?.length > 3" class="px-3 sm:px-6 py-3 border-t"><Pagination :links="activities.links" :from="activities.from" :to="activities.to" :total="activities.total" /></div>
      </div>
    </div>

    <!-- Clear Activities Modal -->
    <Teleport to="body">
      <div v-if="showClearModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showClearModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <div class="p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-bold mb-4">Clear Old Activities</h3>
            <p class="text-xs sm:text-sm text-slate-500 mb-4">Delete activities older than:</p>
            <select v-model="clearDays" class="w-full px-4 py-2 border rounded-xl bg-white dark:bg-slate-900 text-sm">
              <option value="30">30 days</option><option value="60">60 days</option><option value="90">90 days</option><option value="180">180 days</option><option value="365">1 year</option>
            </select>
          </div>
          <div class="px-4 sm:px-6 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-t flex gap-3">
            <button @click="showClearModal = false" class="flex-1 px-4 py-2 border rounded-xl text-sm">Cancel</button>
            <button @click="performClear" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold">Clear Activities</button>
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

const props = defineProps({ activities: Object, users: Array, actions: Array, stats: Object, filters: Object })

const showClearModal = ref(false)
const clearDays = ref(90)

const filters = reactive({ search: props.filters?.search || '', user_id: props.filters?.user_id || '', action: props.filters?.action || '' })

const getActionClass = (action) => {
  if (action.includes('created')) return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
  if (action.includes('updated')) return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
  if (action.includes('deleted')) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  return 'bg-slate-100 text-slate-700'
}

const formatDetails = (details) => {
  if (!details) return 'No details'
  if (details.report_title) return `Report: ${details.report_title}`
  if (details.task_title) return `Task: ${details.task_title}`
  if (details.user_name) return `User: ${details.user_name}`
  return JSON.stringify(details).substring(0, 100)
}

const formatDate = (date) => new Date(date).toLocaleString()
const timeAgo = (date) => {
  const seconds = Math.floor((Date.now() - new Date(date)) / 1000)
  if (seconds < 60) return 'just now'
  if (seconds < 3600) return `${Math.floor(seconds/60)} min ago`
  if (seconds < 86400) return `${Math.floor(seconds/3600)} hrs ago`
  return `${Math.floor(seconds/86400)} days ago`
}

const applyFilters = () => router.get(route('admin.activities.index'), filters, { preserveState: true })
const resetFilters = () => { filters.search = ''; filters.user_id = ''; filters.action = ''; applyFilters() }
const exportActivities = () => window.open(route('admin.activities.export', filters), '_blank')
const clearActivities = () => { showClearModal.value = true }
const performClear = () => router.delete(route('admin.activities.clear'), { data: { days: clearDays.value }, onSuccess: () => { showClearModal.value = false } })
</script>

<style scoped>
@keyframes scale-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.animate-scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>