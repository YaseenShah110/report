<!-- resources/js/Pages/Admin/Tasks/Create.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.tasks.index')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-chevron-left"></i>
                </Link>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create New Task</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Task Title <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.title" required
                            class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter task title">
                        <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
                        <textarea v-model="form.description" rows="4"
                            class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            placeholder="Describe the task details..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Assign To <span class="text-red-500">*</span></label>
                            <select v-model="form.assigned_to" required class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900">
                                <option value="">Select User</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Priority</label>
                            <select v-model="form.priority" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Due Date</label>
                            <input type="date" v-model="form.due_date"
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Related Report</label>
                            <select v-model="form.report_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900">
                                <option value="">None</option>
                                <option v-for="report in reports" :key="report.id" :value="report.id">{{ report.title }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <Link :href="route('admin.tasks.index')" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-center text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold flex items-center justify-center gap-2">
                            <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                            {{ form.processing ? 'Creating...' : 'Create Task' }}
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
    users: Array,
    reports: Array
})

const form = useForm({
    title: '',
    description: '',
    assigned_to: '',
    priority: 'medium',
    due_date: '',
    report_id: ''
})

const submit = () => {
    form.post(route('admin.tasks.store'))
}
</script>