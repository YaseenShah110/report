<!-- resources/js/Pages/Admin/Roles/Index.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Role Management</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage roles and permissions</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.roles.create')" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl">
                        <i class="fa-solid fa-plus"></i>
                        Create Role
                    </Link>
                    <Link :href="route('admin.roles.permissions')" 
                        class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-key"></i>
                        Manage Permissions
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Total Roles</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_roles }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-users text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Total Permissions</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_permissions }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-key text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Users with Roles</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_users_with_roles }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-user-check text-emerald-600 dark:text-emerald-400"></i>
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
                                <input v-model="filters.search" type="text" placeholder="Search roles..." 
                                    class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            </div>
                        </div>
                        <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold">
                            Apply
                        </button>
                        <button @click="resetFilters" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Roles Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Role Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Permissions</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Users</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Created</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="role in roles.data" :key="role.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                                                <i class="fa-solid fa-shield-alt text-white text-xs"></i>
                                            </div>
                                            <span class="font-medium text-slate-900 dark:text-white capitalize">{{ role.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs rounded-full">
                                            {{ role.permissions_count }} permissions
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ role.users_count }} users</td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-sm">{{ formatDate(role.created_at) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.roles.show', role.id)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400">
                                                <i class="fa-solid fa-eye"></i>
                                            </Link>
                                            <Link :href="route('admin.roles.edit', role.id)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400">
                                                <i class="fa-solid fa-pen"></i>
                                            </Link>
                                            <button v-if="role.name !== 'admin'" @click="confirmDelete(role)" class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                        <Pagination :links="roles.links" />
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
                            <i class="fa-solid fa-trash text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete Role</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-6">
                            Are you sure you want to delete role <span class="font-semibold">{{ deleteModal.role?.name }}</span>?
                        </p>
                        <div class="flex gap-3">
                            <button @click="deleteModal.show = false" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl">Cancel</button>
                            <button @click="deleteRole" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    roles: Object,
    permissions: Object,
    stats: Object,
    filters: Object
})

const deleteModal = ref({ show: false, role: null })
const filters = reactive({
    search: props.filters.search || ''
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}

const applyFilters = () => {
    router.get(route('admin.roles.index'), filters, { preserveState: true })
}

const resetFilters = () => {
    filters.search = ''
    applyFilters()
}

const confirmDelete = (role) => {
    deleteModal.value = { show: true, role }
}

const deleteRole = () => {
    router.delete(route('admin.roles.destroy', deleteModal.value.role.id), {
        onSuccess: () => {
            deleteModal.value.show = false
        }
    })
}
</script>