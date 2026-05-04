<!--
  Admin/Analytics/Reports.vue - Reports Analytics Page
  -----------------------------------------------------------
  Detailed analytics for all reports in the system.
  Features: Summary cards, search, filter by status, sort, export CSV.
  Responsive table with horizontal scroll on mobile.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Reports Analytics</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Detailed report statistics</p>
        </div>
        <button @click="exportData" 
          class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-download text-xs"></i> Export CSV
        </button>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Summary Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700">
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1">Total Pages</p>
          <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_pages }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700">
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1">Avg Pages/Report</p>
          <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">{{ summary.avg_pages_per_report }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700">
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1">Total Shares</p>
          <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_shares }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700">
          <p class="text-[10px] sm:text-xs text-slate-500 mb-1">Shared Reports</p>
          <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">{{ summary.reports_with_shares }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-2 sm:gap-3">
          <div class="flex-1 min-w-[150px]">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
              <input v-model="filters.search" type="text" placeholder="Search reports..." @keyup.enter="applyFilters"
                class="w-full pl-8 sm:pl-9 pr-3 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
            </div>
          </div>
          <select v-model="filters.status" @change="applyFilters" class="px-2 sm:px-3 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
          <select v-model="filters.sort" @change="applyFilters" class="px-2 sm:px-3 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
            <option value="created_at">Date Created</option>
            <option value="updated_at">Last Modified</option>
            <option value="title">Title</option>
          </select>
          <button @click="applyFilters" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold">Apply</button>
          <button @click="resetFilters" class="px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm">Reset</button>
        </div>
      </div>

      <!-- Reports Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase">Report</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase">Author</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase">Status</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden sm:table-cell">Pages</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden md:table-cell">Shares</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase hidden lg:table-cell">Created</th>
                <th class="px-3 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-semibold uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-for="report in reports.data" :key="report.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-lines text-slate-400 text-xs"></i>
                    <span class="font-medium text-xs sm:text-sm truncate">{{ report.title }}</span>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex items-center gap-1.5">
                    <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                      <span class="text-[9px] sm:text-[10px] font-bold text-indigo-600">{{ report.user_name?.charAt(0) }}</span>
                    </div>
                    <span class="text-xs sm:text-sm truncate">{{ report.user_name }}</span>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <span :class="getStatusClass(report.status)" class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full capitalize">{{ report.status }}</span>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm hidden sm:table-cell">{{ report.pages }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm hidden md:table-cell">{{ report.shares }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500 hidden lg:table-cell">{{ formatDate(report.created_at) }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                  <Link :href="route('reports.edit', report.slug)" class="text-indigo-600 hover:text-indigo-800 text-[10px] sm:text-xs font-medium">View Report</Link>
                </td>
              </tr>
              <tr v-if="!reports.data?.length">
                <td colspan="7" class="py-12 text-center text-slate-400">No reports found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="reports.links?.length > 3" class="px-3 sm:px-6 py-3 border-t">
          <Pagination :links="reports.links" :from="reports.from" :to="reports.to" :total="reports.total" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({ reports: Object, summary: Object, filters: Object })

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  sort: props.filters?.sort || 'created_at'
})

const getStatusClass = (status) => ({
  draft: 'bg-amber-100 text-amber-700', published: 'bg-emerald-100 text-emerald-700', archived: 'bg-slate-100 text-slate-700'
}[status] || 'bg-gray-100')

const formatDate = (date) => date ? new Date(date).toLocaleDateString() : 'N/A'
const applyFilters = () => router.get(route('admin.analytics.reports'), filters, { preserveState: true })
const resetFilters = () => { filters.search = ''; filters.status = ''; filters.sort = 'created_at'; applyFilters() }
const exportData = () => window.open(route('admin.analytics.export', { type: 'reports', ...filters }), '_blank')
</script>