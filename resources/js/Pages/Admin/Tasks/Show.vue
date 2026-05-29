<!--
  Admin/Tasks/Show.vue - Task Details Page
  -----------------------------------------------------------
  Displays full task details with activity log and related tasks.
  Admins can edit the task from this page.
  Enhanced: Shows completion notes and related report properly.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2 sm:gap-3">
          <Link :href="route('admin.tasks.index')"
            class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left text-slate-500"></i>
          </Link>
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Task Details</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ task.title }}</p>
          </div>
        </div>
        <Link :href="route('admin.tasks.edit', task.id)"
          class="px-3 sm:px-4 py-1.5 sm:py-2 bg-indigo-600 text-white rounded-lg text-xs sm:text-sm font-semibold">Edit
          Task</Link>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-4xl mx-auto">

      <!-- Task Info Card -->
      <div
        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
          <span :class="getStatusBadge(task.status)"
            class="px-2 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold rounded-full">{{ task.status }}</span>
          <span :class="getPriorityBadge(task.priority)"
            class="px-2 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold rounded-full">{{ task.priority }}</span>
        </div>
        <h3 class="text-base sm:text-lg font-bold mb-2">{{ task.title }}</h3>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mb-4">{{ task.description || 'No description provided' }}</p>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
          <div>
            <p class="text-[10px] sm:text-xs text-slate-500">Assigned To</p>
            <p class="font-medium text-sm">{{ task.assigned_to?.name }}</p>
          </div>
          <div>
            <p class="text-[10px] sm:text-xs text-slate-500">Assigned By</p>
            <p class="font-medium text-sm">{{ task.assigned_by?.name }}</p>
          </div>
          <div>
            <p class="text-[10px] sm:text-xs text-slate-500">Due Date</p>
            <!-- ✅ display full datetime -->
           <p class="font-medium text-sm" :class="{ 'text-red-500': isOverdue }">{{ task.due_date ?
              formatDateTime(task.due_date) : 'No due date' }}</p>
          </div>
          <div>
            <p class="text-[10px] sm:text-xs text-slate-500">Created</p>
            <p class="font-medium text-sm">{{ task.created_at ? formatDateTime(task.created_at) : '—' }}</p>
          </div>
          <div v-if="task.completed_at">
            <p class="text-[10px] sm:text-xs text-slate-500">Completed</p>
            <p class="font-medium text-sm">{{ formatDateTime(task.completed_at) }}</p>
          </div>
        </div>

        <!-- Completion Notes Section -->
        <div v-if="task.completion_notes || task.status === 'completed'"
          class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
          <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
              <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-sm"></i>
              <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Completion Notes</p>
            </div>
            <p class="text-sm text-emerald-800 dark:text-emerald-300 italic">
              {{ task.completion_notes || 'No completion notes provided.' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Related Report (if any) -->
      <div v-if="task.report" class="bg-white dark:bg-slate-800 rounded-2xl border p-4 sm:p-6 mb-4 sm:mb-6">
        <h3 class="text-base sm:text-lg font-semibold mb-3">Related Report</h3>
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-sm">{{ task.report.title }}</p>
            <p class="text-xs text-slate-500">Status: {{ task.report.status }}</p>
          </div>
          <Link :href="route('reports.edit', task.report.slug)" class="text-indigo-600 hover:text-indigo-700 text-sm">
            View Report →
          </Link>
        </div>
      </div>

      <!-- Activity Log -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-semibold mb-3">Activity Log</h3>
        <div class="space-y-2 sm:space-y-3">
          <div v-for="activity in activities" :key="activity.created_at"
            class="flex items-start gap-2 sm:gap-3 text-xs sm:text-sm">
            <i class="fa-solid fa-circle text-[6px] mt-1.5 text-indigo-500 flex-shrink-0"></i>
            <div>
              <p class="text-slate-700 dark:text-slate-300">{{ activity.action?.replace('_', ' ') }}</p>
              <p class="text-[10px] sm:text-xs text-slate-500">{{ timeAgo(activity.created_at) }}</p>
            </div>
          </div>
          <div v-if="!activities.length" class="text-center text-slate-500 py-4 text-sm">No activity recorded</div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  task: Object,
  activities: Array,
  relatedTasks: Array
})

const isOverdue = computed(() =>
  props.task.due_date && new Date(props.task.due_date) < new Date() && props.task.status !== 'completed'
)

const getStatusBadge = (s) => ({
  pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
  in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  completed: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  overdue: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
}[s] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')

const getPriorityBadge = (p) => ({
  low: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  medium: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
  urgent: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
}[p] || 'bg-gray-100 text-gray-700')

// ✅ added formatDateTime helper for full date+time display
const formatDateTime = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

const timeAgo = (date) => {
  const seconds = Math.floor((Date.now() - new Date(date)) / 1000)
  if (seconds < 60) return 'just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  return `${Math.floor(seconds / 86400)}d ago`
}
</script>