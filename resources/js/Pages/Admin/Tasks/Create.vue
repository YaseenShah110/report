<!--
  Admin/Tasks/Create.vue - Create Task Page
  -----------------------------------------------------------
  Form to create a new task. Admins can assign to any user,
  set priority, due date (with time), and link to a report.
  Enhanced with beautiful datetime picker + quick presets.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2 sm:gap-3">
        <Link :href="route('admin.tasks.index')"
          class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-chevron-left text-slate-500"></i>
        </Link>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Create New Task</h2>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-2xl mx-auto">
      <form @submit.prevent="submit"
        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6 space-y-4 sm:space-y-6">

        <!-- Title -->
        <div>
          <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Task Title
            <span class="text-red-500">*</span></label>
          <input type="text" v-model="form.title" required
            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500"
            placeholder="Enter task title">
          <p v-if="form.errors.title" class="text-red-500 text-[10px] sm:text-xs mt-1">{{ form.errors.title }}</p>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-xs sm:text-sm font-semibold mb-1.5">Description</label>
          <textarea v-model="form.description" rows="4"
            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"
            placeholder="Describe the task details..."></textarea>
        </div>

        <!-- Assign To & Priority -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Assign To <span
                class="text-red-500">*</span></label>
            <select v-model="form.assigned_to" required
              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
              <option value="">Select User</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Priority</label>
            <select v-model="form.priority"
              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>

        <!-- Due Date & Report -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="datetime-picker-wrapper">
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Due Date & Time</label>
            <div class="relative">
              <input type="datetime-local" v-model="form.due_date"
                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" />
              <button v-if="form.due_date" type="button" @click="form.due_date = ''"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="fa-solid fa-times-circle text-sm"></i>
              </button>
            </div>
            <!-- Quick preset buttons -->
            <div class="flex flex-wrap gap-2 mt-2">
              <button type="button" @click="setDueDatePreset('tomorrow9am')"
                class="text-[11px] px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all">Tomorrow
                9am</button>
              <button type="button" @click="setDueDatePreset('nextWeek')"
                class="text-[11px] px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all">Next
                week</button>
              <button type="button" @click="setDueDatePreset('endOfDay')"
                class="text-[11px] px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all">Today
                5pm</button>
            </div>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Related Report</label>
            <select v-model="form.report_id"
              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
              <option value="">None</option>
              <option v-for="report in reports" :key="report.id" :value="report.id">{{ report.title }}</option>
            </select>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3 pt-4">
          <Link :href="route('admin.tasks.index')"
            class="flex-1 px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-center text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Cancel</Link>
          <button type="submit" :disabled="form.processing"
            class="flex-1 px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs sm:text-sm font-semibold flex items-center justify-center gap-2">
            <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i> {{ form.processing ? 'Creating...' :
            'Create Task' }}
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ users: Array, reports: Array })

const form = useForm({
  title: '',
  description: '',
  assigned_to: '',
  priority: 'medium',
  due_date: '',
  report_id: ''
})

const setDueDatePreset = (preset) => {
  const now = new Date()
  let target = new Date()
  switch (preset) {
    case 'tomorrow9am':
      target.setDate(now.getDate() + 1)
      target.setHours(9, 0, 0, 0)
      break
    case 'nextWeek':
      target.setDate(now.getDate() + 7)
      target.setHours(10, 0, 0, 0)
      break
    case 'endOfDay':
      target.setHours(17, 0, 0, 0)
      if (target < now) target.setDate(now.getDate() + 1) // if already past 5pm, go tomorrow
      break
    default: return
  }
  // Format to YYYY-MM-DDTHH:MM
  const year = target.getFullYear()
  const month = String(target.getMonth() + 1).padStart(2, '0')
  const day = String(target.getDate()).padStart(2, '0')
  const hours = String(target.getHours()).padStart(2, '0')
  const minutes = String(target.getMinutes()).padStart(2, '0')
  form.due_date = `${year}-${month}-${day}T${hours}:${minutes}`
}

const submit = () => form.post(route('admin.tasks.store'))
</script>

<style scoped>
/* Additional styling for datetime picker (optional) */
.datetime-picker-wrapper input[type="datetime-local"] {
  color-scheme: light dark;
}
</style>