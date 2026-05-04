<!-- resources/js/Pages/Reports/Trashed.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Trashed Reports</h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Reports that have been soft-deleted</p>
        </div>
        <Link :href="route('reports.index')" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-arrow-left text-xs"></i> Back to Reports
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      <!-- Search -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border border-slate-200 dark:border-slate-700">
        <div class="relative max-w-md">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input 
            v-model="filters.search" 
            type="text" 
            placeholder="Search trashed reports..." 
            @keyup.enter="applyFilters"
            class="w-full pl-9 pr-3 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500"
          >
        </div>
      </div>

      <!-- Reports List -->
      <div v-if="reports.data && reports.data.length > 0" class="space-y-3">
        <div v-for="report in reports.data" :key="report.id" 
             class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:shadow-md transition-all duration-200">
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-slate-900 dark:text-white text-sm sm:text-base truncate">{{ report.title }}</h3>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-[10px] sm:text-xs text-slate-500">
                <i class="fa-regular fa-clock mr-1"></i>Deleted {{ formatDate(report.deleted_at) }}
              </span>
              <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
              <span class="text-[10px] sm:text-xs text-slate-500">{{ report.total_pages || 1 }} pages</span>
            </div>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <button 
              @click="restoreReport(report)" 
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-lg text-xs sm:text-sm font-medium transition-colors flex items-center gap-1"
            >
              <i class="fa-solid fa-rotate-left text-[10px]"></i> Restore
            </button>
            <button 
              @click="confirmForceDelete(report)" 
              class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg text-xs sm:text-sm font-medium transition-colors flex items-center gap-1"
            >
              <i class="fa-solid fa-trash-can text-[10px]"></i> Delete Forever
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 py-12 sm:py-16 text-center">
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3 sm:mb-4">
          <i class="fa-solid fa-trash-can text-2xl sm:text-3xl text-slate-400"></i>
        </div>
        <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-white mb-1 sm:mb-2">No trashed reports</h3>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Deleted reports will appear here.</p>
      </div>

      <!-- Pagination -->
      <div v-if="reports.links && reports.links.length > 3" class="mt-4 sm:mt-6">
        <Pagination :links="reports.links" :from="reports.from" :to="reports.to" :total="reports.total" />
      </div>
    </div>

    <!-- Confirmation Modal -->
    <ConfirmationModal 
      :show="deleteModal.show" 
      title="Permanently Delete Report?" 
      :message="`Are you sure you want to permanently delete &quot;${deleteModal.report?.title}&quot;? This cannot be undone.`"
      confirm-text="Delete Forever"
      @close="deleteModal.show = false" 
      @confirm="forceDeleteReport" 
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({ 
  reports: Object, 
  filters: Object 
})

const filters = reactive({ 
  search: props.filters?.search || '' 
})

const deleteModal = ref({ show: false, report: null })

const formatDate = (date) => {
  if (!date) return 'N/A'
  const d = new Date(date)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'Just now'
  if (diff < 3600) return `${Math.floor(diff/60)}m ago`
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff/86400)}d ago`
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const applyFilters = () => {
  router.get(route('reports.trashed'), filters, { preserveState: true })
}

const restoreReport = (report) => {
  router.post(route('reports.restore', report.slug), {}, {
    preserveState: true,
    onSuccess: () => {
      window.showToast?.('Report restored successfully', 'success')
    }
  })
}

const confirmForceDelete = (report) => {
  deleteModal.value = { show: true, report }
}

const forceDeleteReport = () => {
  router.delete(route('reports.force-delete', deleteModal.value.report.slug), {
    preserveState: true,
    onSuccess: () => {
      deleteModal.value.show = false
      window.showToast?.('Report permanently deleted', 'success')
    }
  })
}
</script>