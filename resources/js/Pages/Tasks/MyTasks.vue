<!-- resources/js/Pages/Tasks/MyTasks.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            My Tasks
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your assigned tasks and track progress</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- View Toggle -->
          <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
            <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-600 shadow-sm text-indigo-600' : 'text-slate-500'" class="p-2 rounded-lg transition-all">
              <i class="fa-solid fa-grip"></i>
            </button>
            <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white dark:bg-slate-600 shadow-sm text-indigo-600' : 'text-slate-500'" class="p-2 rounded-lg transition-all">
              <i class="fa-solid fa-list"></i>
            </button>
            <button @click="viewMode = 'kanban'" :class="viewMode === 'kanban' ? 'bg-white dark:bg-slate-600 shadow-sm text-indigo-600' : 'text-slate-500'" class="p-2 rounded-lg transition-all">
              <i class="fa-solid fa-chart-simple"></i>
            </button>
          </div>
          <!-- Export Button -->
          <button @click="exportTasks" class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 hover:bg-slate-50 transition-all">
            <i class="fa-solid fa-download"></i>
          </button>
        </div>
      </div>
    </template>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group cursor-pointer" @click="filterByStatus('pending')">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Pending Tasks</p>
              <p class="text-3xl font-bold text-amber-600">{{ stats.pending || 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-clock text-amber-600 text-xl"></i>
            </div>
          </div>
          <div class="mt-2 text-xs text-slate-500">Needs your attention</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group cursor-pointer" @click="filterByStatus('in_progress')">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">In Progress</p>
              <p class="text-3xl font-bold text-blue-600">{{ stats.in_progress || 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-spinner text-blue-600 text-xl"></i>
            </div>
          </div>
          <div class="mt-2 text-xs text-slate-500">Currently working on</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group cursor-pointer" @click="filterByStatus('completed')">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Completed</p>
              <p class="text-3xl font-bold text-emerald-600">{{ stats.completed || 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-check-circle text-emerald-600 text-xl"></i>
            </div>
          </div>
          <div class="mt-2 text-xs text-slate-500">Great job!</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group cursor-pointer" @click="filterByStatus('overdue')">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Overdue</p>
              <p class="text-3xl font-bold text-red-600">{{ stats.overdue || 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-circle-exclamation text-red-600 text-xl"></i>
            </div>
          </div>
          <div class="mt-2 text-xs text-red-500">Requires immediate action</div>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 mb-6 border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input v-model="filters.search" type="text" placeholder="Search tasks by title or description..." @input="debouncedSearch" 
                class="w-full pl-11 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
          <select v-model="filters.status" @change="applyFilters" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-sm">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="overdue">Overdue</option>
          </select>
          <select v-model="filters.priority" @change="applyFilters" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-sm">
            <option value="">All Priority</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
          <select v-model="filters.sort" @change="applyFilters" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-sm">
            <option value="due_date_asc">Due Date (Earliest)</option>
            <option value="due_date_desc">Due Date (Latest)</option>
            <option value="priority">Priority (Highest)</option>
            <option value="created_at_desc">Newest First</option>
            <option value="created_at_asc">Oldest First</option>
          </select>
          <button @click="applyFilters" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold transition-all">Apply</button>
          <button @click="resetFilters" class="px-6 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 hover:bg-slate-50 transition-all">Reset</button>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="task in tasks.data" :key="task.id" class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 overflow-hidden hover:-translate-y-1">
          <div class="p-5">
            <!-- Header -->
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full" :class="getPriorityDotColor(task.priority)"></div>
                <h3 class="font-semibold text-slate-900 dark:text-white">{{ task.title }}</h3>
              </div>
              <span :class="getStatusBadgeClass(task.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ task.status }}
              </span>
            </div>
            
            <!-- Description -->
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 line-clamp-2">{{ task.description || 'No description provided' }}</p>
            
            <!-- Progress Bar -->
            <div class="mb-4">
              <div class="flex justify-between text-xs text-slate-500 mb-1">
                <span>Progress</span>
                <span>{{ getTaskProgress(task) }}%</span>
              </div>
              <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500" :class="getProgressColor(task.status)" :style="{ width: `${getTaskProgress(task)}%` }"></div>
              </div>
            </div>

            <!-- Meta Info -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-2">
                <span :class="getPriorityBadgeClass(task.priority)" class="px-2 py-0.5 text-[10px] font-semibold rounded-full">
                  {{ task.priority }}
                </span>
              </div>
              <div class="flex items-center gap-1 text-xs text-slate-500">
                <i class="fa-regular fa-calendar"></i>
                <span :class="{ 'text-red-500 font-semibold': isOverdue(task.due_date) && task.status !== 'completed' }">
                  {{ task.due_date ? formatDate(task.due_date) : 'No due date' }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
              <select v-model="task.status" @change="updateStatus(task)" class="text-xs border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 bg-white dark:bg-slate-800">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
              <div class="flex items-center gap-2">
                <button @click="openTaskDetails(task)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="View Details">
                  <i class="fa-regular fa-eye text-slate-500"></i>
                </button>
                <Link v-if="task.report" :href="route('reports.edit', task.report.slug)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="View Report">
                  <i class="fa-solid fa-file-lines text-slate-500"></i>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else-if="viewMode === 'list'" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Task</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Priority</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Due Date</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Progress</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-slate-50 transition-colors group">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium">{{ task.title }}</p>
                    <p class="text-sm text-slate-500 line-clamp-1">{{ task.description }}</p>
                  </div>
                </td>
                <td class="px-6 py-4"><span :class="getPriorityBadgeClass(task.priority)" class="px-2 py-1 text-xs rounded-full capitalize">{{ task.priority }}</span></td>
                <td class="px-6 py-4">
                  <select v-model="task.status" @change="updateStatus(task)" class="text-xs border border-slate-200 rounded-lg px-2 py-1">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                  </select>
                </td>
                <td class="px-6 py-4">
                  <span :class="{ 'text-red-500 font-semibold': isOverdue(task.due_date) && task.status !== 'completed' }">
                    {{ task.due_date ? formatDate(task.due_date) : 'No date' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all" :class="getProgressColor(task.status)" :style="{ width: `${getTaskProgress(task)}%` }"></div>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openTaskDetails(task)" class="p-1.5 rounded-lg hover:bg-slate-100" title="View Details">
                      <i class="fa-regular fa-eye text-slate-500"></i>
                    </button>
                    <Link v-if="task.report" :href="route('reports.edit', task.report.slug)" class="p-1.5 rounded-lg hover:bg-slate-100" title="View Report">
                      <i class="fa-solid fa-external-link text-slate-500"></i>
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-6 py-4 border-t"><Pagination :links="tasks.links" /></div>
      </div>

      <!-- Kanban View -->
      <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Pending Column -->
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-amber-700 dark:text-amber-400">Pending</h3>
            <span class="bg-amber-200 dark:bg-amber-800 text-amber-700 dark:text-amber-300 text-xs px-2 py-0.5 rounded-full">{{ getTasksByStatus('pending').length }}</span>
          </div>
          <div class="space-y-3">
            <div v-for="task in getTasksByStatus('pending')" :key="task.id" draggable="true" @dragstart="dragStart(task)" @dragend="dragEnd" class="bg-white dark:bg-slate-800 rounded-xl p-3 shadow-sm cursor-move">
              <p class="font-medium text-sm">{{ task.title }}</p>
              <div class="flex items-center justify-between mt-2">
                <span :class="getPriorityBadgeClass(task.priority)" class="text-[10px] px-1.5 py-0.5 rounded-full">{{ task.priority }}</span>
                <button @click="openTaskDetails(task)" class="text-indigo-500 text-xs">Details</button>
              </div>
            </div>
          </div>
        </div>

        <!-- In Progress Column -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-blue-700 dark:text-blue-400">In Progress</h3>
            <span class="bg-blue-200 dark:bg-blue-800 text-blue-700 dark:text-blue-300 text-xs px-2 py-0.5 rounded-full">{{ getTasksByStatus('in_progress').length }}</span>
          </div>
          <div class="space-y-3">
            <div v-for="task in getTasksByStatus('in_progress')" :key="task.id" class="bg-white dark:bg-slate-800 rounded-xl p-3 shadow-sm">
              <p class="font-medium text-sm">{{ task.title }}</p>
              <div class="flex items-center justify-between mt-2">
                <span :class="getPriorityBadgeClass(task.priority)" class="text-[10px] px-1.5 py-0.5 rounded-full">{{ task.priority }}</span>
                <select v-model="task.status" @change="updateStatus(task)" class="text-xs border rounded px-1 py-0.5">
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Completed Column -->
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-emerald-700 dark:text-emerald-400">Completed</h3>
            <span class="bg-emerald-200 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs px-2 py-0.5 rounded-full">{{ getTasksByStatus('completed').length }}</span>
          </div>
          <div class="space-y-3">
            <div v-for="task in getTasksByStatus('completed')" :key="task.id" class="bg-white dark:bg-slate-800 rounded-xl p-3 shadow-sm opacity-75">
              <p class="font-medium text-sm line-through">{{ task.title }}</p>
              <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-emerald-600">Completed</span>
                <button @click="openTaskDetails(task)" class="text-indigo-500 text-xs">Details</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Overdue Column -->
        <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-red-700 dark:text-red-400">Overdue</h3>
            <span class="bg-red-200 dark:bg-red-800 text-red-700 dark:text-red-300 text-xs px-2 py-0.5 rounded-full">{{ getTasksByStatus('overdue').length }}</span>
          </div>
          <div class="space-y-3">
            <div v-for="task in getTasksByStatus('overdue')" :key="task.id" class="bg-white dark:bg-slate-800 rounded-xl p-3 shadow-sm border-l-4 border-red-500">
              <p class="font-medium text-sm">{{ task.title }}</p>
              <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-red-600">Overdue by {{ getOverdueDays(task.due_date) }} days</span>
                <button @click="openTaskDetails(task)" class="text-indigo-500 text-xs">Details</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!tasks.data?.length" class="text-center py-16">
        <div class="w-24 h-24 rounded-3xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
          <i class="fa-solid fa-tasks text-3xl text-slate-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No tasks found</h3>
        <p class="text-slate-500 dark:text-slate-400">No tasks match your search criteria.</p>
        <button @click="resetFilters" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold">Clear Filters</button>
      </div>
    </div>

    <!-- Task Details Modal -->
    <Teleport to="body">
      <div v-if="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDetailsModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto animate-scale-in">
          <div class="sticky top-0 bg-white dark:bg-slate-800 p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold">Task Details</h3>
            <button @click="showDetailsModal = false" class="p-1 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark text-xl"></i></button>
          </div>
          <div class="p-5 space-y-4">
            <div><label class="text-xs text-slate-500 uppercase">Title</label><p class="font-medium">{{ selectedTask?.title }}</p></div>
            <div><label class="text-xs text-slate-500 uppercase">Description</label><p class="text-slate-600">{{ selectedTask?.description || 'No description' }}</p></div>
            <div class="grid grid-cols-2 gap-4">
              <div><label class="text-xs text-slate-500 uppercase">Priority</label><span :class="getPriorityBadgeClass(selectedTask?.priority)" class="px-2 py-1 text-xs rounded-full">{{ selectedTask?.priority }}</span></div>
              <div><label class="text-xs text-slate-500 uppercase">Status</label><select v-model="selectedTask.status" @change="updateStatus(selectedTask)" class="text-sm border rounded-lg px-2 py-1"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></div>
              <div><label class="text-xs text-slate-500 uppercase">Due Date</label><p class="font-medium" :class="{ 'text-red-500': isOverdue(selectedTask?.due_date) && selectedTask?.status !== 'completed' }">{{ selectedTask?.due_date ? formatDate(selectedTask.due_date) : 'No due date' }}</p></div>
              <div><label class="text-xs text-slate-500 uppercase">Created</label><p>{{ formatDate(selectedTask?.created_at) }}</p></div>
            </div>
            <div v-if="selectedTask?.completion_notes"><label class="text-xs text-slate-500 uppercase">Completion Notes</label><p class="text-slate-600 italic">{{ selectedTask.completion_notes }}</p></div>
            <div class="pt-4 border-t"><button @click="showDetailsModal = false" class="w-full py-2 bg-indigo-600 text-white rounded-lg font-semibold">Close</button></div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Completion Notes Modal -->
    <Teleport to="body">
      <div v-if="showNotesModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showNotesModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <div class="p-6">
            <h3 class="text-lg font-bold mb-4">Add Completion Notes</h3>
            <textarea v-model="completionNotes" rows="4" class="w-full px-4 py-2 border rounded-xl" placeholder="Add notes about task completion..."></textarea>
            <div class="flex gap-3 mt-4">
              <button @click="showNotesModal = false" class="flex-1 px-4 py-2 border rounded-xl">Cancel</button>
              <button @click="submitCompletionNotes" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-xl">Submit</button>
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({ tasks: Object, stats: Object, filters: Object })
const viewMode = ref('grid')
const showDetailsModal = ref(false)
const showNotesModal = ref(false)
const selectedTask = ref(null)
const currentTask = ref(null)
const completionNotes = ref('')

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  priority: props.filters?.priority || '',
  sort: props.filters?.sort || 'due_date_asc'
})

const getPriorityDotColor = (priority) => ({
  low: 'bg-blue-500', medium: 'bg-green-500', high: 'bg-orange-500', urgent: 'bg-red-500'
})[priority] || 'bg-gray-500'

const getPriorityBadgeClass = (priority) => ({
  low: 'bg-blue-100 text-blue-700', medium: 'bg-green-100 text-green-700',
  high: 'bg-orange-100 text-orange-700', urgent: 'bg-red-100 text-red-700'
})[priority] || 'bg-gray-100 text-gray-700'

const getStatusBadgeClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-700', in_progress: 'bg-blue-100 text-blue-700',
  completed: 'bg-green-100 text-green-700', overdue: 'bg-red-100 text-red-700'
})[status] || 'bg-gray-100 text-gray-700'

const getProgressColor = (status) => ({
  pending: 'bg-amber-500', in_progress: 'bg-blue-500', completed: 'bg-emerald-500', overdue: 'bg-red-500'
})[status] || 'bg-slate-500'

const getTaskProgress = (task) => {
  if (task.status === 'completed') return 100
  if (task.status === 'in_progress') return 50
  if (task.status === 'overdue') return 0
  return 10
}

const getTasksByStatus = (status) => {
  return (props.tasks?.data || []).filter(task => task.status === status)
}

const getOverdueDays = (dueDate) => {
  if (!dueDate) return 0
  const diff = Math.floor((new Date() - new Date(dueDate)) / (1000 * 60 * 60 * 24))
  return Math.max(0, diff)
}

const formatDate = (date) => new Date(date).toLocaleDateString()
const isOverdue = (date) => date && new Date(date) < new Date()

const applyFilters = () => router.get(route('tasks.my'), filters, { preserveState: true })
const resetFilters = () => { filters.search = ''; filters.status = ''; filters.priority = ''; filters.sort = 'due_date_asc'; applyFilters() }
const filterByStatus = (status) => { filters.status = status; applyFilters() }

const updateStatus = (task) => {
  if (task.status === 'completed') {
    currentTask.value = task
    showNotesModal.value = true
  } else {
    router.patch(route('admin.tasks.status', task.id), { status: task.status }, {
      onSuccess: () => window.showToast?.('Task status updated', 'success')
    })
  }
}

const submitCompletionNotes = () => {
  router.patch(route('admin.tasks.status', currentTask.value.id), {
    status: 'completed',
    completion_notes: completionNotes.value
  }, {
    onSuccess: () => {
      showNotesModal.value = false
      completionNotes.value = ''
      currentTask.value = null
      window.showToast?.('Task completed! Great job!', 'success')
    }
  })
}

const openTaskDetails = (task) => {
  selectedTask.value = { ...task }
  showDetailsModal.value = true
}

const exportTasks = () => {
  window.open(route('tasks.export', filters), '_blank')
}

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => applyFilters(), 500)
}

// Drag and drop for kanban (simplified)
let draggedTask = null
const dragStart = (task) => { draggedTask = task }
const dragEnd = () => { draggedTask = null }
</script>

<style scoped>
@keyframes scale-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.animate-scale-in { animation: scale-in 0.2s ease-out forwards; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>