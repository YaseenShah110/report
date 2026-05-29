<!--
  Admin/Tasks/Edit.vue - Edit Task Page
  -----------------------------------------------------------
  Form to edit an existing task. Supports status change,
  reassignment, and completion notes.
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
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Edit Task</h2>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-2xl mx-auto">
      <form @submit.prevent="submit"
        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6 space-y-4 sm:space-y-6">

        <div>
          <label class="block text-xs sm:text-sm font-semibold mb-1.5">Task Title</label>
          <input type="text" v-model="form.title" required
            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
        </div>

        <div>
          <label class="block text-xs sm:text-sm font-semibold mb-1.5">Description</label>
          <textarea v-model="form.description" rows="4"
            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Assign To</label><select
              v-model="form.assigned_to"
              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select></div>
          <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Priority</label><select
              v-model="form.priority"
              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select></div>
          <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Status</label><select v-model="form.status"
              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select></div>
          <div class="datetime-picker-wrapper">
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Due Date & Time</label>
            <div class="relative">
              <input type="datetime-local" :value="dueDateForInput" @input="updateDueDate"
                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 transition-all" />
              <button v-if="dueDateForInput" type="button" @click="clearDueDate"
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
        </div>

        <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Related Report</label><select
            v-model="form.report_id"
            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
            <option value="">None</option>
            <option v-for="report in reports" :key="report.id" :value="report.id">{{ report.title }}</option>
          </select></div>

        <!-- Completion Notes - shown when status is completed OR existing notes exist -->
        <div v-if="form.status === 'completed' || form.completion_notes"
          class="p-3 sm:p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
          <label class="block text-xs sm:text-sm font-semibold mb-1.5 text-emerald-700 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check mr-1"></i>Completion Notes
          </label>
          <textarea v-model="form.completion_notes" rows="3"
            class="w-full px-3 sm:px-4 py-2 border rounded-xl text-xs sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
            placeholder="Add notes about task completion..."></textarea>
          <p class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-1">These notes will be shown when viewing the
            task
            details.</p>
        </div>

        <div class="flex gap-3">
          <Link :href="route('admin.tasks.index')"
            class="flex-1 px-4 py-2 sm:py-2.5 border rounded-xl text-center text-xs sm:text-sm">Cancel</Link>
          <button type="submit" :disabled="form.processing"
            class="flex-1 px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs sm:text-sm font-semibold">Update
            Task</button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  task: Object,
  users: Array,
  reports: Array
})

// Convert database datetime to datetime-local format for input value
const dueDateForInput = computed(() => {
  if (!props.task.due_date) return ''
  let str = props.task.due_date
  if (str.includes(' ')) str = str.replace(' ', 'T')
  return str.slice(0, 16)
})

const updateDueDate = (e) => {
  form.due_date = e.target.value
}

const clearDueDate = () => {
  form.due_date = ''
}

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
      if (target < now) target.setDate(now.getDate() + 1)
      break
    default: return
  }
  const year = target.getFullYear()
  const month = String(target.getMonth() + 1).padStart(2, '0')
  const day = String(target.getDate()).padStart(2, '0')
  const hours = String(target.getHours()).padStart(2, '0')
  const minutes = String(target.getMinutes()).padStart(2, '0')
  form.due_date = `${year}-${month}-${day}T${hours}:${minutes}`
}

const form = useForm({
  title: props.task.title,
  description: props.task.description,
  assigned_to: props.task.assigned_to,
  priority: props.task.priority,
  status: props.task.status,
  due_date: props.task.due_date ? props.task.due_date.replace(' ', 'T').slice(0, 16) : '',
  report_id: props.task.report_id,
  completion_notes: props.task.completion_notes || ''
})

const submit = () => form.put(route('admin.tasks.update', props.task.id))
</script>

<style scoped>
/* Additional styling for datetime picker */
.datetime-picker-wrapper input[type="datetime-local"] {
  color-scheme: light dark;
}
</style>