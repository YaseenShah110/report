<!-- resources/js/Pages/Admin/Tasks/Index.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Task Management</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Create and manage tasks for team members</p>
        </div>
        <Link :href="route('admin.tasks.create')" 
          class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all">
          <i class="fa-solid fa-plus"></i>
          Create Task
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- SHARED DATA USAGE - Overdue Tasks Warning Banner -->
        <div v-if="$page.props.notifications?.overdue_tasks > 0" 
             class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i>
            <div>
              <p class="font-semibold text-red-700 dark:text-red-400">⚠️ Overdue Tasks Alert</p>
              <p class="text-sm text-red-600 dark:text-red-300">You have <span class="font-bold">{{ $page.props.notifications.overdue_tasks }}</span> overdue task(s) that need immediate attention!</p>
            </div>
          </div>
          <button @click="filters.status = 'overdue'; applyFilters();" 
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">
            View Overdue Tasks →
          </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
          <div v-for="stat in statCards" :key="stat.label" 
            class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</p>
                <!-- SHARED DATA USAGE - Personal pending tasks count -->
                <p v-if="stat.label === 'Pending'" class="text-xs text-slate-400 mt-1">You have <span class="font-bold text-amber-600">{{ $page.props.notifications?.pending_tasks || 0 }}</span> assigned</p>
                <!-- SHARED DATA USAGE - Personal overdue tasks count -->
                <p v-if="stat.label === 'Overdue'" class="text-xs text-red-500 mt-1">You have <span class="font-bold">{{ $page.props.notifications?.overdue_tasks || 0 }}</span> assigned</p>
              </div>
              <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="stat.bgClass">
                <i :class="[stat.icon, stat.iconClass]"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 mb-6 border border-slate-200 dark:border-slate-700">
          <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
              <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input v-model="filters.search" type="text" placeholder="Search tasks..." 
                  @keyup.enter="applyFilters"
                  class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
              </div>
            </div>
            <select v-model="filters.status" @change="applyFilters" 
              class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="overdue">Overdue</option>
            </select>
            <select v-model="filters.priority" @change="applyFilters" 
              class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
              <option value="">All Priority</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
            <select v-model="filters.assigned_to" @change="applyFilters" 
              class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
              <option value="">All Users</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
            <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold">
              Apply
            </button>
            <button @click="resetFilters" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
              Reset
            </button>
          </div>
        </div>

        <!-- Tasks Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Task</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Assigned To</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Priority</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Due Date</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                  <td class="px-6 py-4">
                    <div>
                      <p class="font-medium text-slate-900 dark:text-white">{{ task.title }}</p>
                      <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1">{{ task.description }}</p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ task.assigned_to?.name?.charAt(0) || '?' }}
                      </div>
                      <span class="text-sm text-slate-700 dark:text-slate-300">{{ task.assigned_to?.name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getPriorityColor(task.priority)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                      {{ task.priority }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getStatusColor(task.status)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                      {{ task.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-sm text-slate-600 dark:text-slate-400" :class="{ 'text-red-600 dark:text-red-400 font-semibold': task.due_date && new Date(task.due_date) < new Date() && task.status !== 'completed' }">
                      {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <Link :href="route('admin.tasks.edit', task.id)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400">
                        <i class="fa-solid fa-pen"></i>
                      </Link>
                      <button @click="confirmDelete(task)" class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            <Pagination :links="tasks.links" />
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div v-if="deleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModal.show = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 w-full max-w-md">
          <div class="text-center">
            <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
              <i class="fa-solid fa-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete Task</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">Are you sure you want to delete "{{ deleteModal.task?.title }}"?</p>
            <div class="flex gap-3">
              <button @click="deleteModal.show = false" class="flex-1 px-4 py-2 border rounded-xl">Cancel</button>
              <button @click="deleteTask" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const page = usePage()

const props = defineProps({
  tasks: Object,
  users: Array,
  stats: Object,
  filters: Object
})

const deleteModal = ref({ show: false, task: null })
const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  priority: props.filters?.priority || '',
  assigned_to: props.filters?.assigned_to || ''
})

const statCards = computed(() => [
  { label: 'Total Tasks', value: props.stats?.total || 0, icon: 'fa-solid fa-tasks', bgClass: 'bg-indigo-100 dark:bg-indigo-900/30', iconClass: 'text-indigo-600' },
  { label: 'Pending', value: props.stats?.pending || 0, icon: 'fa-solid fa-clock', bgClass: 'bg-yellow-100 dark:bg-yellow-900/30', iconClass: 'text-yellow-600' },
  { label: 'In Progress', value: props.stats?.in_progress || 0, icon: 'fa-solid fa-spinner', bgClass: 'bg-blue-100 dark:bg-blue-900/30', iconClass: 'text-blue-600' },
  { label: 'Completed', value: props.stats?.completed || 0, icon: 'fa-solid fa-check-circle', bgClass: 'bg-green-100 dark:bg-green-900/30', iconClass: 'text-green-600' },
  { label: 'Overdue', value: props.stats?.overdue || 0, icon: 'fa-solid fa-exclamation-triangle', bgClass: 'bg-red-100 dark:bg-red-900/30', iconClass: 'text-red-600' }
])

const getPriorityColor = (priority) => {
  const colors = {
    low: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    medium: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    urgent: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  }
  return colors[priority] || colors.medium
}

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    completed: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    overdue: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
  }
  return colors[status] || colors.pending
}

const applyFilters = () => {
  router.get(route('admin.tasks.index'), filters, { preserveState: true })
}

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  filters.priority = ''
  filters.assigned_to = ''
  applyFilters()
}

const confirmDelete = (task) => {
  deleteModal.value = { show: true, task }
}

const deleteTask = () => {
  router.delete(route('admin.tasks.destroy', deleteModal.value.task.id), {
    onSuccess: () => { deleteModal.value.show = false }
  })
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

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>