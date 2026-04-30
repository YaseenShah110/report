<!-- resources/js/Pages/Reports/Index.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            My Reports
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage and organize all your reports</p>
        </div>
        <Link :href="route('reports.create')" 
          class="group relative inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 hover:scale-105">
          <i class="fa-solid fa-plus"></i>
          <span>New Report</span>
          <div class="absolute inset-0 rounded-xl bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        </Link>
      </div>
    </template>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div v-for="stat in statCards" :key="stat.label" 
          class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300 group">
          <div class="flex items-center justify-between mb-2">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all group-hover:scale-110"
              :class="stat.bgClass">
              <i :class="[stat.icon, stat.iconClass]" class="text-lg"></i>
            </div>
            <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 mb-6 border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-3">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
              <input v-model="filters.search" type="text" placeholder="Search reports..." 
                @input="debouncedSearch"
                class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
          <div class="flex gap-2">
            <button v-for="status in ['all', 'draft', 'published', 'archived']" :key="status"
              @click="filters.status = status; loadReports()"
              :class="filters.status === status ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300'"
              class="px-3 py-1.5 text-xs font-medium rounded-full capitalize transition-all">
              {{ status }}
              <span class="ml-1 text-xs opacity-70">{{ getStatusCount(status) }}</span>
            </button>
          </div>
          <select v-model="filters.sort" @change="loadReports"
            class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500">
            <option value="updated_at">Last Modified</option>
            <option value="created_at">Date Created</option>
            <option value="title">Title A-Z</option>
          </select>
          <div class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
            <button v-for="mode in ['grid', 'list']" :key="mode" @click="viewMode = mode"
              :class="viewMode === mode ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'"
              class="p-2 rounded-lg transition-all">
              <i :class="mode === 'grid' ? 'fa-solid fa-grip' : 'fa-solid fa-list'" class="text-sm"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <div v-for="report in reports.data" :key="report.id" 
          class="group bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-xl transition-all duration-300">
          
          <!-- Thumbnail -->
          <div class="relative h-40 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center opacity-30 group-hover:scale-110 transition-transform duration-500">
              <i class="fa-solid fa-file-lines text-6xl text-indigo-400"></i>
            </div>
            <div class="absolute top-3 right-3">
              <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full capitalize"
                :class="getStatusBadgeClass(report.status)">
                {{ report.status }}
              </span>
            </div>
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
              <Link :href="route('reports.edit', report.slug)" 
                class="px-3 py-1.5 bg-white text-slate-900 rounded-lg text-xs font-semibold hover:bg-indigo-50 transition-colors">
                <i class="fa-solid fa-pen mr-1"></i> Edit
              </Link>
              <Link :href="route('reports.preview', report.slug)" target="_blank"
                class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-eye mr-1"></i> Preview
              </Link>
            </div>
          </div>

          <!-- Info -->
          <div class="p-4">
            <h3 class="font-semibold text-slate-900 dark:text-white text-sm line-clamp-1">{{ report.title }}</h3>
            <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
              <i class="fa-regular fa-calendar"></i>
              <span>{{ formatDate(report.updated_at) }}</span>
              <span class="w-1 h-1 rounded-full bg-slate-300"></span>
              <i class="fa-regular fa-file"></i>
              <span>{{ report.total_pages || 1 }} pages</span>
            </div>
            <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-100 dark:border-slate-700">
              <div class="flex gap-1">
                <Link :href="route('reports.edit', report.slug)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors" title="Edit">
                  <i class="fa-solid fa-pen text-xs"></i>
                </Link>
                <Link :href="route('reports.preview', report.slug)" target="_blank" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors" title="Preview">
                  <i class="fa-solid fa-eye text-xs"></i>
                </Link>
                <a :href="route('reports.download', report.slug)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors" title="Download PDF">
                  <i class="fa-solid fa-download text-xs"></i>
                </a>
              </div>
              <div class="flex gap-1">
                <button @click="duplicateReport(report)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors" title="Duplicate">
                  <i class="fa-regular fa-clone text-xs"></i>
                </button>
                <button @click="openShareModal(report)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors" title="Share">
                  <i class="fa-solid fa-share-alt text-xs"></i>
                </button>
                <button @click="confirmDelete(report)" class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors" title="Delete">
                  <i class="fa-solid fa-trash text-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else-if="viewMode === 'list'" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Report</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Pages</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Last Modified</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-for="report in reports.data" :key="report.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="getStatusIconBg(report.status)">
                      <i :class="getStatusIcon(report.status)" class="text-sm"></i>
                    </div>
                    <div>
                      <p class="font-medium text-slate-900 dark:text-white">{{ report.title }}</p>
                      <p class="text-xs text-slate-500 mt-0.5">{{ report.template?.name || 'Custom' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize" :class="getStatusBadgeClass(report.status)">
                    {{ report.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ report.total_pages || 1 }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ formatDate(report.updated_at) }}</td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Link :href="route('reports.edit', report.slug)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600" title="Edit">
                      <i class="fa-solid fa-pen text-sm"></i>
                    </Link>
                    <Link :href="route('reports.preview', report.slug)" target="_blank" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600" title="Preview">
                      <i class="fa-solid fa-eye text-sm"></i>
                    </Link>
                    <a :href="route('reports.download', report.slug)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600" title="PDF">
                      <i class="fa-solid fa-download text-sm"></i>
                    </a>
                    <button @click="duplicateReport(report)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600" title="Duplicate">
                      <i class="fa-regular fa-clone text-sm"></i>
                    </button>
                    <button @click="confirmDelete(report)" class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500" title="Delete">
                      <i class="fa-solid fa-trash text-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!reports.data?.length" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 py-16 text-center">
        <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
          <i class="fa-solid fa-file-lines text-3xl text-slate-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No reports yet</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Create your first report to get started</p>
        <Link :href="route('reports.create')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold transition-colors">
          <i class="fa-solid fa-plus"></i> Create Report
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="reports.links?.length > 3" class="mt-6">
        <Pagination :links="reports.links" />
      </div>
    </div>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div v-if="deleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModal.show = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
              <i class="fa-solid fa-trash text-red-600 dark:text-red-400 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Report?</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">
              Are you sure you want to delete <span class="font-semibold text-slate-900 dark:text-white">"{{ deleteModal.report?.title }}"</span>?
              This action cannot be undone.
            </p>
            <div class="flex gap-3">
              <button @click="deleteModal.show = false" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Cancel
              </button>
              <button @click="deleteReport" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-colors">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Share Modal -->
    <Teleport to="body">
      <div v-if="shareModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="shareModal.show = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <i class="fa-solid fa-share-alt text-indigo-600"></i>
              </div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">Share Report</h3>
            </div>
            <button @click="shareModal.show = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
              <i class="fa-solid fa-xmark text-xl text-slate-400"></i>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Share Link</label>
              <div class="flex gap-2">
                <input :value="shareModal.link" readonly class="flex-1 px-3 py-2 text-sm font-mono bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg">
                <button @click="copyLink" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors">
                  Copy
                </button>
              </div>
            </div>
            <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
              <div>
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Public Access</p>
                <p class="text-xs text-slate-500">Anyone with the link can view</p>
              </div>
              <button @click="togglePublicAccess" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="shareModal.isPublic ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-600'">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="shareModal.isPublic ? 'translate-x-6' : 'translate-x-1'"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  reports: Object,
  stats: Object
})

const viewMode = ref('grid')
const deleteModal = ref({ show: false, report: null })
const shareModal = ref({ show: false, report: null, link: '', isPublic: false })
let searchTimeout = null

const filters = reactive({
  search: '',
  status: 'all',
  sort: 'updated_at'
})

const statCards = computed(() => [
  { label: 'Total', value: props.stats?.total || 0, icon: 'fa-solid fa-file-lines', bgClass: 'bg-indigo-100 dark:bg-indigo-900/30', iconClass: 'text-indigo-600' },
  { label: 'Published', value: props.stats?.published || 0, icon: 'fa-solid fa-globe', bgClass: 'bg-emerald-100 dark:bg-emerald-900/30', iconClass: 'text-emerald-600' },
  { label: 'Drafts', value: props.stats?.draft || 0, icon: 'fa-solid fa-pen-fancy', bgClass: 'bg-amber-100 dark:bg-amber-900/30', iconClass: 'text-amber-600' },
  { label: 'Archived', value: props.stats?.archived || 0, icon: 'fa-solid fa-archive', bgClass: 'bg-slate-100 dark:bg-slate-700', iconClass: 'text-slate-600' }
])

const getStatusCount = (status) => {
  if (status === 'all') return props.stats?.total || 0
  return props.stats?.[status] || 0
}

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    published: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    archived: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-400'
  }
  return classes[status] || classes.draft
}

const getStatusIcon = (status) => {
  const icons = {
    draft: 'fa-solid fa-pen-fancy',
    published: 'fa-solid fa-check-circle',
    archived: 'fa-solid fa-archive'
  }
  return icons[status] || 'fa-solid fa-file'
}

const getStatusIconBg = (status) => {
  const classes = {
    draft: 'bg-amber-50 dark:bg-amber-900/20',
    published: 'bg-emerald-50 dark:bg-emerald-900/20',
    archived: 'bg-slate-100 dark:bg-slate-700'
  }
  return classes[status] || classes.draft
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  const diff = Math.floor((Date.now() - new Date(date)) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
  return new Date(date).toLocaleDateString()
}

const loadReports = () => {
  router.get(route('reports.index'), filters, { preserveState: true, preserveScroll: true })
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadReports(), 500)
}

const confirmDelete = (report) => {
  deleteModal.value = { show: true, report }
}

const deleteReport = () => {
  router.delete(route('reports.destroy', deleteModal.value.report.slug), {
    onSuccess: () => {
      deleteModal.value.show = false
    }
  })
}

const duplicateReport = (report) => {
  router.post(route('reports.duplicate', report.slug))
}

const openShareModal = async (report) => {
  try {
    const response = await axios.post(route('reports.share', report.slug))
    shareModal.value = {
      show: true,
      report: report,
      link: response.data.url,
      isPublic: true
    }
  } catch (error) {
    console.error('Failed to generate share link', error)
  }
}

const copyLink = () => {
  navigator.clipboard.writeText(shareModal.value.link)
  // Show toast notification
}

const togglePublicAccess = async () => {
  if (shareModal.value.isPublic) {
    await axios.delete(route('reports.share.revoke', shareModal.value.report.slug))
    shareModal.value.isPublic = false
  } else {
    const response = await axios.post(route('reports.share', shareModal.value.report.slug))
    shareModal.value.link = response.data.url
    shareModal.value.isPublic = true
  }
}
</script>

<style scoped>
@keyframes scale-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.animate-scale-in {
  animation: scale-in 0.2s ease-out forwards;
}
</style>