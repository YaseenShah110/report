<!--
  Reports/Index.vue - My Reports Page
  -----------------------------------------------------------
  Displays all reports owned by the current user.
  Features: Grid/List view toggle, search, filter by status,
            share, duplicate, delete, download, preview.
  Responsive design with mobile-first approach.
-->
<template>
  <AuthenticatedLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            My Reports
          </h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">
            Manage and organize all your reports
          </p>
        </div>
        <!-- Create Report Button -->
        <Link :href="route('reports.create')" 
          class="group relative inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:scale-105">
          <i class="fa-solid fa-plus text-xs"></i> New Report
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div v-for="stat in statCards" :key="stat.label" 
             class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300 group">
          <div class="flex items-center justify-between mb-2">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition-all group-hover:scale-110" :class="stat.bgClass">
              <i :class="[stat.icon, stat.iconClass]" class="text-base sm:text-lg"></i>
            </div>
            <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</span>
          </div>
          <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-2 sm:gap-3">
          <!-- Search Input -->
          <div class="flex-1 min-w-[150px] sm:min-w-[200px]">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-2 sm:left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
              <input v-model="filters.search" type="text" placeholder="Search reports..." @input="debouncedSearch"
                class="w-full pl-7 sm:pl-9 pr-2 sm:pr-3 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
          
          <!-- Status Filter Buttons -->
          <div class="flex gap-1.5 sm:gap-2">
            <button v-for="status in ['all','draft','published','archived']" :key="status"
              @click="filters.status = status; loadReports()"
              :class="filters.status === status ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300'"
              class="px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-medium rounded-full capitalize transition-all">
              {{ status }}
              <span class="ml-1 text-[9px] sm:text-xs opacity-70">{{ getStatusCount(status) }}</span>
            </button>
          </div>
          
          <!-- Sort Dropdown -->
          <select v-model="filters.sort" @change="loadReports" 
            class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300">
            <option value="updated_at">Last Modified</option>
            <option value="created_at">Date Created</option>
            <option value="title">Title A-Z</option>
          </select>
          
          <!-- View Toggle -->
          <div class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
            <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'" class="p-1.5 sm:p-2 rounded-lg transition-all">
              <i class="fa-solid fa-grip text-xs sm:text-sm"></i>
            </button>
            <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'" class="p-1.5 sm:p-2 rounded-lg transition-all">
              <i class="fa-solid fa-list text-xs sm:text-sm"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
        <div v-for="report in reports.data" :key="report.id" 
             class="group bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-xl transition-all duration-300">
          
          <!-- Report Thumbnail -->
          <div class="relative h-32 sm:h-40 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center opacity-30 group-hover:scale-110 transition-transform duration-500">
              <i class="fa-solid fa-file-lines text-4xl sm:text-6xl text-indigo-400"></i>
            </div>
            <!-- Status Badge -->
            <div class="absolute top-2 right-2 sm:top-3 sm:right-3">
              <span class="px-1.5 sm:px-2 py-0.5 text-[9px] sm:text-[10px] font-semibold rounded-full capitalize" :class="getStatusBadgeClass(report.status)">{{ report.status }}</span>
            </div>
            <!-- Hover Actions -->
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 sm:gap-3">
              <Link :href="route('reports.edit', report.slug)" class="px-2 sm:px-3 py-1 sm:py-1.5 bg-white text-slate-900 rounded-lg text-[10px] sm:text-xs font-semibold hover:bg-indigo-50"><i class="fa-solid fa-pen mr-1"></i>Edit</Link>
              <Link :href="route('reports.preview', report.slug)" target="_blank" class="px-2 sm:px-3 py-1 sm:py-1.5 bg-indigo-600 text-white rounded-lg text-[10px] sm:text-xs font-semibold"><i class="fa-solid fa-eye mr-1"></i>Preview</Link>
            </div>
          </div>

          <!-- Report Info -->
          <div class="p-3 sm:p-4">
            <h3 class="font-semibold text-slate-900 dark:text-white text-xs sm:text-sm line-clamp-1">{{ report.title }}</h3>
            <div class="flex items-center gap-1.5 sm:gap-2 mt-1 text-[10px] sm:text-xs text-slate-400">
              <i class="fa-regular fa-calendar"></i><span>{{ formatDate(report.updated_at) }}</span>
              <span class="w-1 h-1 rounded-full bg-slate-300"></span>
              <i class="fa-regular fa-file"></i><span>{{ report.total_pages || 1 }} pages</span>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center justify-between mt-2 sm:mt-3 pt-2 border-t border-slate-100 dark:border-slate-700">
              <div class="flex gap-0.5 sm:gap-1">
                <Link :href="route('reports.edit', report.slug)" class="p-1 sm:p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500"><i class="fa-solid fa-pen text-[10px] sm:text-xs"></i></Link>
                <Link :href="route('reports.preview', report.slug)" target="_blank" class="p-1 sm:p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500"><i class="fa-solid fa-eye text-[10px] sm:text-xs"></i></Link>
                <a :href="route('reports.download', report.slug)" class="p-1 sm:p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500"><i class="fa-solid fa-download text-[10px] sm:text-xs"></i></a>
              </div>
              <div class="flex gap-0.5 sm:gap-1">
                <button @click="duplicateReport(report)" class="p-1 sm:p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500"><i class="fa-regular fa-clone text-[10px] sm:text-xs"></i></button>
                <button @click="openShareModal(report)" class="p-1 sm:p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500"><i class="fa-solid fa-share-alt text-[10px] sm:text-xs"></i></button>
                <button @click="confirmDelete(report)" class="p-1 sm:p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500"><i class="fa-solid fa-trash text-[10px] sm:text-xs"></i></button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!reports.data?.length" class="col-span-full bg-white dark:bg-slate-800 rounded-2xl border py-12 sm:py-16 text-center">
          <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3 sm:mb-4">
            <i class="fa-solid fa-file-lines text-2xl sm:text-3xl text-slate-400"></i>
          </div>
          <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-white mb-1 sm:mb-2">No reports yet</h3>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-4 sm:mb-6">Create your first report to get started</p>
          <Link :href="route('reports.create')" class="inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold"><i class="fa-solid fa-plus"></i>Create Report</Link>
        </div>
      </div>

      <!-- List View -->
      <div v-else-if="viewMode === 'list'" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase">Report</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase">Pages</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Modified</th>
                <th class="px-3 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-semibold text-slate-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="report in reports.data" :key="report.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center flex-shrink-0" :class="getStatusIconBg(report.status)">
                      <i :class="getStatusIcon(report.status)" class="text-xs sm:text-sm"></i>
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-slate-900 dark:text-white text-xs sm:text-sm truncate">{{ report.title }}</p>
                      <p class="text-[10px] sm:text-xs text-slate-500 mt-0.5 truncate">{{ report.template?.name || 'Custom' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full capitalize" :class="getStatusBadgeClass(report.status)">{{ report.status }}</span>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-slate-600 dark:text-slate-300 text-xs sm:text-sm">{{ report.total_pages || 1 }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500 hidden sm:table-cell">{{ formatDate(report.updated_at) }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                  <div class="flex items-center justify-end gap-0.5 sm:gap-1">
                    <Link :href="route('reports.edit', report.slug)" class="p-1 sm:p-1.5 rounded-lg hover:bg-slate-100 text-slate-600"><i class="fa-solid fa-pen text-[10px] sm:text-xs"></i></Link>
                    <button @click="confirmDelete(report)" class="p-1 sm:p-1.5 rounded-lg hover:bg-red-100 text-red-500"><i class="fa-solid fa-trash text-[10px] sm:text-xs"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="reports.links?.length > 3" class="mt-4 sm:mt-6">
        <Pagination :links="reports.links" :from="reports.from" :to="reports.to" :total="reports.total" />
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal :show="deleteModal.show" title="Delete Report?" :message="`Are you sure you want to delete &quot;${deleteModal.report?.title}&quot;? This can be undone from Trash.`" @close="deleteModal.show = false" @confirm="deleteReport" />

    <!-- Share Modal -->
    <Teleport to="body">
      <div v-if="shareModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="shareModal.show = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <div class="flex items-center justify-between p-4 sm:p-5 border-b">
            <h3 class="text-base sm:text-lg font-bold">Share Report</h3>
            <button @click="shareModal.show = false" class="p-1 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <div class="p-4 sm:p-5 space-y-4">
            <div>
              <label class="block text-xs sm:text-sm font-semibold mb-1.5">Share Link</label>
              <div class="flex gap-2">
                <input :value="shareModal.link" readonly class="flex-1 px-3 py-2 text-xs font-mono bg-slate-100 dark:bg-slate-700 border rounded-lg">
                <button @click="copyLink" class="px-3 sm:px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs sm:text-sm font-semibold">Copy</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({ reports: Object, stats: Object })
const viewMode = ref('grid')
const deleteModal = ref({ show: false, report: null })
const shareModal = ref({ show: false, report: null, link: '', isPublic: false })
let searchTimeout = null

const filters = reactive({ search: '', status: 'all', sort: 'updated_at' })

const statCards = computed(() => [
  { label: 'Total', value: props.stats?.total || 0, icon: 'fa-solid fa-file-lines', bgClass: 'bg-indigo-100 dark:bg-indigo-900/30', iconClass: 'text-indigo-600' },
  { label: 'Published', value: props.stats?.published || 0, icon: 'fa-solid fa-globe', bgClass: 'bg-emerald-100 dark:bg-emerald-900/30', iconClass: 'text-emerald-600' },
  { label: 'Drafts', value: props.stats?.draft || 0, icon: 'fa-solid fa-pen-fancy', bgClass: 'bg-amber-100 dark:bg-amber-900/30', iconClass: 'text-amber-600' },
  { label: 'Archived', value: props.stats?.archived || 0, icon: 'fa-solid fa-archive', bgClass: 'bg-slate-100 dark:bg-slate-700', iconClass: 'text-slate-600' }
])

const getStatusCount = (status) => status === 'all' ? props.stats?.total || 0 : props.stats?.[status] || 0
const getStatusBadgeClass = (status) => ({ draft: 'bg-amber-100 text-amber-700', published: 'bg-emerald-100 text-emerald-700', archived: 'bg-slate-100 text-slate-700' }[status] || '')
const getStatusIcon = (status) => ({ draft: 'fa-solid fa-pen-fancy', published: 'fa-solid fa-check-circle', archived: 'fa-solid fa-archive' }[status] || 'fa-solid fa-file')
const getStatusIconBg = (status) => ({ draft: 'bg-amber-50 dark:bg-amber-900/20', published: 'bg-emerald-50 dark:bg-emerald-900/20', archived: 'bg-slate-100 dark:bg-slate-700' }[status] || '')

const formatDate = (date) => {
  if (!date) return 'N/A'
  const diff = Math.floor((Date.now() - new Date(date)) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff/60)}m ago`
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`
  return new Date(date).toLocaleDateString()
}

const loadReports = () => router.get(route('reports.index'), filters, { preserveState: true, preserveScroll: true })
const debouncedSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => loadReports(), 500) }

const confirmDelete = (report) => { deleteModal.value = { show: true, report } }
const deleteReport = () => router.delete(route('reports.destroy', deleteModal.value.report.slug), { onSuccess: () => { deleteModal.value.show = false } })
const duplicateReport = (report) => router.post(route('reports.duplicate', report.slug))

const openShareModal = async (report) => {
  try {
    const response = await axios.post(route('reports.share', report.slug))
    shareModal.value = { show: true, report, link: response.data.url, isPublic: true }
  } catch (e) { console.error(e) }
}
const copyLink = () => navigator.clipboard.writeText(shareModal.value.link)
</script>

<style scoped>
@keyframes scale-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.animate-scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>