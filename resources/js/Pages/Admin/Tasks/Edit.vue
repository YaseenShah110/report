<!--
  Admin/Tasks/Edit.vue - Edit Task Page
  -----------------------------------------------------------
  Form to edit an existing task. Supports status change,
  reassignment, and completion notes.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2 sm:gap-3">
        <Link :href="route('admin.tasks.index')" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-chevron-left text-slate-500"></i>
        </Link>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Edit Task</h2>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-2xl mx-auto">
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6 space-y-4 sm:space-y-6">
        
        <div>
          <label class="block text-xs sm:text-sm font-semibold mb-1.5">Task Title</label>
          <input type="text" v-model="form.title" required class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
        </div>

        <div>
          <label class="block text-xs sm:text-sm font-semibold mb-1.5">Description</label>
          <textarea v-model="form.description" rows="4" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Assign To</label><select v-model="form.assigned_to" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"><option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option></select></div>
          <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Priority</label><select v-model="form.priority" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
          <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Status</label><select v-model="form.status" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></div>
          <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Due Date</label><input type="date" v-model="form.due_date" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"></div>
        </div>

        <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Related Report</label><select v-model="form.report_id" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"><option value="">None</option><option v-for="report in reports" :key="report.id" :value="report.id">{{ report.title }}</option></select></div>

        <div v-if="form.status === 'completed'" class="p-3 sm:p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
          <label class="block text-xs sm:text-sm font-semibold mb-1.5">Completion Notes</label>
          <textarea v-model="form.completion_notes" rows="2" class="w-full px-3 sm:px-4 py-2 border rounded-xl text-xs sm:text-sm" placeholder="Add notes about task completion..."></textarea>
        </div>

        <div class="flex gap-3">
          <Link :href="route('admin.tasks.index')" class="flex-1 px-4 py-2 sm:py-2.5 border rounded-xl text-center text-xs sm:text-sm">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs sm:text-sm font-semibold">Update Task</button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ task: Object, users: Array, reports: Array })

const form = useForm({
  title: props.task.title, description: props.task.description, assigned_to: props.task.assigned_to,
  priority: props.task.priority, status: props.task.status, due_date: props.task.due_date,
  report_id: props.task.report_id, completion_notes: props.task.completion_notes
})

const submit = () => form.put(route('admin.tasks.update', props.task.id))
</script>