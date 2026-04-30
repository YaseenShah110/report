<!-- resources/js/Pages/Admin/Users/Index.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">User Management</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage system users and their roles</p>
                </div>
                <Link :href="route('admin.users.create')" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    <i class="fa-solid fa-plus"></i>
                    Add New User
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div v-for="stat in statsCards" :key="stat.label" 
                        class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ stat.value }}</p>
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
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input v-model="filters.search" type="text" placeholder="Search users..." 
                                    class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                        <select v-model="filters.role" class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm">
                            <option value="">All Roles</option>
                            <option v-for="role in roles" :key="role.name" :value="role.name">{{ role.name }}</option>
                        </select>
                        <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors">
                            Apply Filters
                        </button>
                        <button @click="resetFilters" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reports</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tasks</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="role in user.roles" :key="role.name" 
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="role.name === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'">
                                                {{ role.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ user.reports_count || 0 }}</td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ user.tasks_count || 0 }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="user.email_verified_at ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                            class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ user.email_verified_at ? 'Active' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.users.edit', user.id)" 
                                                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 transition-colors"
                                                title="Edit User">
                                                <i class="fa-solid fa-pen"></i>
                                            </Link>
                                            <button @click="impersonate(user)" 
                                                v-if="user.id !== $page.props.auth.user.id"
                                                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 transition-colors"
                                                title="Impersonate">
                                                <i class="fa-solid fa-mask"></i>
                                            </button>
                                            <button @click="confirmDelete(user)" 
                                                v-if="user.id !== $page.props.auth.user.id"
                                                class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                                title="Delete User">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                        <Pagination :links="users.links" />
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete User</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-6">
                            Are you sure you want to delete <span class="font-semibold text-slate-900 dark:text-white">{{ deleteModal.user?.name }}</span>? 
                            This action cannot be undone.
                        </p>
                        <div class="flex gap-3">
                            <button @click="deleteModal.show = false" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                Cancel
                            </button>
                            <button @click="deleteUser" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-colors">
                                Delete
                            </button>
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

const props = defineProps({
    users: Object,
    roles: Array,
    stats: Object,
    filters: Object
})

const page = usePage()
const deleteModal = ref({ show: false, user: null })
const filters = reactive({
    search: props.filters.search || '',
    role: props.filters.role || ''
})

const statsCards = computed(() => [
    { label: 'Total Users', value: props.stats.total, icon: 'fa-solid fa-users', bgClass: 'bg-indigo-100 dark:bg-indigo-900/30', iconClass: 'text-indigo-600 dark:text-indigo-400' },
    { label: 'Active Users', value: props.stats.active, icon: 'fa-solid fa-user-check', bgClass: 'bg-emerald-100 dark:bg-emerald-900/30', iconClass: 'text-emerald-600 dark:text-emerald-400' },
    { label: 'Premium Users', value: props.stats.premium, icon: 'fa-solid fa-crown', bgClass: 'bg-amber-100 dark:bg-amber-900/30', iconClass: 'text-amber-600 dark:text-amber-400' },
    { label: 'New Today', value: props.stats.new_today, icon: 'fa-solid fa-calendar-day', bgClass: 'bg-violet-100 dark:bg-violet-900/30', iconClass: 'text-violet-600 dark:text-violet-400' }
])

const applyFilters = () => {
    router.get(route('admin.users.index'), filters, { preserveState: true })
}

const resetFilters = () => {
    filters.search = ''
    filters.role = ''
    applyFilters()
}

const confirmDelete = (user) => {
    deleteModal.value = { show: true, user }
}

const deleteUser = () => {
    router.delete(route('admin.users.destroy', deleteModal.value.user.id), {
        onSuccess: () => {
            deleteModal.value.show = false
        }
    })
}

const impersonate = (user) => {
    router.post(route('admin.users.impersonate', user.id))
}
</script>