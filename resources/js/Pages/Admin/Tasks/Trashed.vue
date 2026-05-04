<!--
  Admin/Tasks/Trashed.vue - Trashed Tasks Page
  -----------------------------------------------------------
  Displays soft-deleted tasks for administrators/managers.
  Admins can restore tasks or permanently delete them.
  Includes search functionality and responsive table design.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Trashed Tasks</h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Tasks that have been soft-deleted</p>
        </div>
        <Link :href="route('admin.tasks.index')" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-arrow-left text-xs"></i> Back to Tasks
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Search Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border border-slate-200 dark:border-slate-700">
        <div class="relative max-w-md">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input 
            v-model="filters.search" 
            type="text" 
            placeholder="Search trashed tasks by title..." 
            @keyup.enter="applyFilters"
            class="w-full pl-9 pr-3 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
          >
        </div>
      </div>

      <!-- Tasks Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Task</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Assigned To</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Priority</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Status</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Deleted At</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-right text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                
                <!-- Task Title & Description -->
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div>
                    <p class="font-medium text-slate-900 dark:text-white text-xs sm:text-sm">{{ task.title }}</p>
                    <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">{{ task.description || 'No description' }}</p>
                  </div>
                </td>
                
                <!-- Assigned To (hidden on mobile) -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center text-white text-[10px] sm:text-xs font-bold flex-shrink-0">
                      {{ task.assigned_to?.name?.charAt(0) || '?' }}
                    </div>
                    <span class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 truncate">{{ task.assigned_to?.name || 'Unassigned' }}</span>
                  </div>
                </td>
                
                <!-- Priority Badge (hidden on mobile) -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">
                  <span :class="getPriorityBadgeClass(task.priority)" class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full capitalize">
                    {{ task.priority }}
                  </span>
                </td>
                
                <!-- Status Badge (hidden on mobile) -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">
                  <span :class="getStatusBadgeClass(task.status)" class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full capitalize">
                    {{ task.status }}
                  </span>
                </td>
                
                <!-- Deleted At (hidden on tablet) -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500 dark:text-slate-400 hidden lg:table-cell">
                  {{ formatDate(task.deleted_at) }}
                </td>
                
                <!-- Action Buttons -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                  <div class="flex items-center justify-end gap-1 sm:gap-2">
                    <!-- Restore Button -->
                    <button 
                      @click="restoreTask(task)" 
                      class="p-1.5 sm:p-2 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 transition-colors"
                      title="Restore task"
                    >
                      <i class="fa-solid fa-rotate-left text-xs sm:text-sm"></i>
                    </button>
                    
                    <!-- Force Delete Button -->
                    <button 
                      @click="confirmForceDelete(task)" 
                      class="p-1.5 sm:p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                      title="Permanently delete task"
                    >
                      <i class="fa-solid fa-trash-can text-xs sm:text-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- Empty State -->
              <tr v-if="!tasks.data || tasks.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="text-slate-400">
                    <i class="fa-solid fa-tasks text-2xl sm:text-3xl mb-2 block"></i>
                    <p class="text-xs sm:text-sm">No trashed tasks found</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="tasks.links && tasks.links.length > 3" class="px-3 sm:px-6 py-3 sm:py-4 border-t border-slate-200 dark:border-slate-700">
          <Pagination :links="tasks.links" :from="tasks.from" :to="tasks.to" :total="tasks.total" />
        </div>
      </div>
    </div>

    <!-- Permanent Delete Confirmation Modal -->
    <ConfirmationModal 
      :show="deleteModal.show" 
      title="Permanently Delete Task?" 
      :message="`Are you sure you want to permanently delete &quot;${deleteModal.task?.title}&quot;? This action CANNOT be undone.`"
      confirm-text="Delete Forever"
      icon="fa-solid fa-triangle-exclamation"
      @close="deleteModal.show = false" 
      @confirm="forceDeleteTask" 
    />
  </AuthenticatedLayout>
</template>

<script setup>
/**
 * Trashed Tasks Page Script
 * Handles: searching, restoring, and permanently deleting trashed tasks
 */
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

// Props from Laravel controller
const props = defineProps({ 
  tasks: Object,    // Paginated trashed tasks
  filters: Object   // Search filter parameters
})

// Search filter (reactive)
const filters = reactive({ 
  search: props.filters?.search || '' 
})

// Delete confirmation modal state
const deleteModal = ref({ 
  show: false, 
  task: null 
})

/**
 * Format date for display
 */
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Get CSS classes for priority badge
 */
const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    medium: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    urgent: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  }
  return classes[priority] || 'bg-gray-100 text-gray-700'
}

/**
 * Get CSS classes for status badge
 */
const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    completed: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    overdue: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

/**
 * Apply search filter and reload page
 */
const applyFilters = () => {
  router.get(route('admin.tasks.trashed'), filters, { 
    preserveState: true 
  })
}

/**
 * Restore a soft-deleted task
 */
const restoreTask = (task) => {
  router.post(route('admin.tasks.restore', task.id), {}, {
    preserveState: true,
    onSuccess: () => {
      window.showToast?.('Task restored successfully', 'success')
    }
  })
}

/**
 * Show confirmation modal before permanent deletion
 */
const confirmForceDelete = (task) => {
  deleteModal.value = { 
    show: true, 
    task: task 
  }
}

/**
 * Permanently delete a task (cannot be recovered)
 */
const forceDeleteTask = () => {
  router.delete(route('admin.tasks.force-delete', deleteModal.value.task.id), {
    preserveState: true,
    onSuccess: () => {
      deleteModal.value.show = false
      window.showToast?.('Task permanently deleted', 'success')
    }
  })
}
</script>