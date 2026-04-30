<!-- resources/js/Pages/Admin/Reports/Assignments.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Report Assignments</h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Create Assignment Form -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-8">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Assign Report to User</h3>
                    <form @submit.prevent="createAssignment" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <select v-model="newAssignment.report_id" required class="px-4 py-2.5 border rounded-xl bg-white dark:bg-slate-900">
                            <option value="">Select Report</option>
                            <option v-for="report in reports" :key="report.id" :value="report.id">{{ report.title }}</option>
                        </select>
                        <select v-model="newAssignment.user_id" required class="px-4 py-2.5 border rounded-xl bg-white dark:bg-slate-900">
                            <option value="">Select User</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <select v-model="newAssignment.permission" class="px-4 py-2.5 border rounded-xl bg-white dark:bg-slate-900">
                            <option value="view">View Only</option>
                            <option value="edit">Can Edit</option>
                            <option value="manage">Full Manage</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold">
                            Assign Report
                        </button>
                    </form>
                </div>

                <!-- Assignments List -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold">Report</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold">Permission</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold">Expires</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="assignment in assignments.data" :key="assignment.id">
                                    <td class="px-6 py-4 font-medium">{{ assignment.report?.title }}</td>
                                    <td class="px-6 py-4">{{ assignment.user?.name }}</td>
                                    <td class="px-6 py-4 capitalize">{{ assignment.permission }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="assignment.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 text-xs rounded-full">
                                            {{ assignment.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ assignment.expires_at ? new Date(assignment.expires_at).toLocaleDateString() : 'Never' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="toggleActive(assignment)" class="p-2 rounded-lg hover:bg-slate-100 mr-2">
                                            <i :class="assignment.is_active ? 'fa-solid fa-ban' : 'fa-solid fa-check-circle'"></i>
                                        </button>
                                        <button @click="deleteAssignment(assignment)" class="p-2 rounded-lg hover:bg-red-100 text-red-600">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t">
                        <Pagination :links="assignments.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    assignments: Object,
    reports: Array,
    users: Array
})

const newAssignment = reactive({
    report_id: '',
    user_id: '',
    permission: 'view'
})

const createAssignment = () => {
    router.post(route('admin.report-assignments.store'), newAssignment, {
        onSuccess: () => {
            newAssignment.report_id = ''
            newAssignment.user_id = ''
            newAssignment.permission = 'view'
        }
    })
}

const toggleActive = (assignment) => {
    router.patch(route('admin.report-assignments.toggle', assignment.id))
}

const deleteAssignment = (assignment) => {
    if (confirm('Remove this assignment?')) {
        router.delete(route('admin.report-assignments.destroy', assignment.id))
    }
}
</script>