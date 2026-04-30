<!-- resources/js/Pages/Admin/Roles/Show.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.roles.index')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-chevron-left"></i>
                </Link>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Role Details: {{ role.name }}</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Permissions</h3>
                            <div v-for="(perms, category) in permissions" :key="category" class="mb-6">
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 capitalize">{{ category }}</h4>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="perm in perms" :key="perm.name" 
                                        class="px-2 py-1 text-xs rounded-full"
                                        :class="role.permissions.includes(perm.name) 
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' 
                                            : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                                        {{ perm.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Role Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Role Name</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white capitalize">{{ role.name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Permissions Count</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ role.permissions.length }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Users Assigned</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ users.length }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Created At</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ formatDate(role.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Users with this Role</h3>
                            <div class="space-y-2">
                                <div v-for="user in users.slice(0,5)" :key="user.id" class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                        <span class="text-xs font-bold text-indigo-600">{{ user.name.charAt(0) }}</span>
                                    </div>
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ user.name }}</span>
                                </div>
                                <p v-if="users.length > 5" class="text-xs text-slate-500">+{{ users.length - 5 }} more users</p>
                            </div>
                        </div>
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
    role: Object,
    permissions: Object,
    users: Array
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}
</script>