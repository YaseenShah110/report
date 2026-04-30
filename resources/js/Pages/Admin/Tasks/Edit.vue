<!-- resources/js/Pages/Admin/Tasks/Edit.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.tasks.index')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-chevron-left"></i>
                </Link>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Edit Task</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Task Title</label>
                        <input type="text" v-model="form.title" required class="w-full px-4 py-2.5 border rounded-xl bg-white dark:bg-slate-900">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
                        <textarea v-model="form.description" rows="4" class="w-full px-4 py-2.5 border rounded-xl bg-white dark:bg-slate-900"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Assign To</label>
                            <select v-model="form.assigned_to" class="w-full px-4 py-2.5 border rounded-xl">
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Priority</label>
                            <select v-model="form.priority" class="w-full px-4 py-2.5 border rounded-xl">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Status</label>
                            <select v-model="form.status" class="w-full px-4 py-2.5 border rounded-xl">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Due Date</label>
                            <input type="date" v-model="form.due_date" class="w-full px-4 py-2.5 border rounded-xl">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Related Report</label>
                        <select v-model="form.report_id" class="w-full px-4 py-2.5 border rounded-xl">
                            <option value="">None</option>
                            <option v-for="report in reports" :key="report.id" :value="report.id">{{ report.title }}</option>
                        </select>
                    </div>

                    <div v-if="form.status === 'completed'" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                        <label class="block text-sm font-semibold mb-1.5">Completion Notes</label>
                        <textarea v-model="form.completion_notes" rows="2" class="w-full px-4 py-2.5 border rounded-xl" placeholder="Add notes about task completion..."></textarea>
                    </div>

                    <div class="flex gap-3">
                        <Link :href="route('admin.tasks.index')" class="flex-1 px-4 py-2 border rounded-xl text-center">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold">
                            Update Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    task: Object,
    users: Array,
    reports: Array
})

const form = useForm({
    title: props.task.title,
    description: props.task.description,
    assigned_to: props.task.assigned_to,
    priority: props.task.priority,
    status: props.task.status,
    due_date: props.task.due_date,
    report_id: props.task.report_id,
    completion_notes: props.task.completion_notes
})

const submit = () => {
    form.put(route('admin.tasks.update', props.task.id))
}
</script>