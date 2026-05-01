<!-- resources/js/Pages/Admin/Tasks/Show.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="route('admin.tasks.index')" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
          <i class="fa-solid fa-arrow-left"></i>
        </Link>
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Task Details</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ task.title }}</p>
        </div>
        <Link :href="route('admin.tasks.edit', task.id)" class="ml-auto px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold">
          Edit Task
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Task Info -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <div class="flex items-center justify-between mb-4">
            <span :class="getStatusBadge(task.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
              {{ task.status }}
            </span>
            <span :class="getPriorityBadge(task.priority)" class="px-3 py-1 text-xs font-semibold rounded-full">
              {{ task.priority }}
            </span>
          </div>
          <h3 class="text-lg font-bold mb-2">{{ task.title }}</h3>
          <p class="text-slate-600 dark:text-slate-400 mb-4">{{ task.description || 'No description provided' }}</p>
          
          <div class="grid grid-cols-2 gap-4 pt-4 border-t">
            <div>
              <p class="text-xs text-slate-500">Assigned To</p>
              <p class="font-medium">{{ task.assigned_to?.name }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Assigned By</p>
              <p class="font-medium">{{ task.assigned_by?.name }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Due Date</p>
              <p class="font-medium" :class="{ 'text-red-500': isOverdue }">{{ task.due_date || 'No due date' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Created At</p>
              <p class="font-medium">{{ formatDate(task.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Related Report -->
        <div v-if="task.report" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <h3 class="text-lg font-semibold mb-3">Related Report</h3>
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">{{ task.report.title }}</p>
              <p class="text-sm text-slate-500">Status: {{ task.report.status }}</p>
            </div>
            <Link :href="route('reports.edit', task.report.slug)" class="text-indigo-600 hover:text-indigo-700">
              View Report →
            </Link>
          </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
          <h3 class="text-lg font-semibold mb-3">Activity Log</h3>
          <div class="space-y-3">
            <div v-for="activity in activities" :key="activity.created_at" class="flex items-start gap-3 text-sm">
              <i class="fa-solid fa-circle text-[6px] mt-2 text-indigo-500"></i>
              <div>
                <p>{{ activity.action.replace('_', ' ') }}</p>
                <p class="text-xs text-slate-500">{{ timeAgo(activity.created_at) }}</p>
              </div>
            </div>
            <div v-if="!activities.length" class="text-center text-slate-500 py-4">No activity recorded</div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  task: Object,
  activities: Array,
  relatedTasks: Array
})

const isOverdue = computed(() => props.task.due_date && new Date(props.task.due_date) < new Date() && props.task.status !== 'completed')

const getStatusBadge = (status) => ({
  pending: 'bg-yellow-100 text-yellow-700',
  in_progress: 'bg-blue-100 text-blue-700',
  completed: 'bg-green-100 text-green-700',
  overdue: 'bg-red-100 text-red-700'
})[status] || 'bg-gray-100 text-gray-700'

const getPriorityBadge = (priority) => ({
  low: 'bg-blue-100 text-blue-700',
  medium: 'bg-green-100 text-green-700',
  high: 'bg-orange-100 text-orange-700',
  urgent: 'bg-red-100 text-red-700'
})[priority] || 'bg-gray-100 text-gray-700'

const formatDate = (date) => new Date(date).toLocaleDateString()
const timeAgo = (date) => { const seconds = Math.floor((Date.now() - new Date(date)) / 1000); if (seconds < 60) return 'just now'; if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`; if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`; return `${Math.floor(seconds / 86400)} days ago` }
</script>