<!-- resources/js/Pages/Admin/Users/Edit.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.users.index')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                    <i class="fa-solid fa-chevron-left"></i>
                </Link>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Edit User: {{ user.name }}</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Form -->
                    <div class="lg:col-span-2">
                        <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="p-6 space-y-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Full Name</label>
                                    <input type="text" v-model="form.name" required
                                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                                    <input type="email" v-model="form.email" required
                                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                                </div>

                                <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Change Password (Optional)</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <input type="password" v-model="form.password" placeholder="New Password"
                                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <input type="password" v-model="form.password_confirmation" placeholder="Confirm Password"
                                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Assign Roles</label>
                                    <div class="flex flex-wrap gap-3">
                                        <label v-for="role in roles" :key="role.name" class="flex items-center gap-2">
                                            <input type="checkbox" :value="role.name" v-model="form.roles"
                                                class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ role.name }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                                    <div>
                                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Premium User</label>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Give this user premium access</p>
                                    </div>
                                    <button type="button" @click="form.is_premium = !form.is_premium"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                        :class="form.is_premium ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-600'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                            :class="form.is_premium ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                                <Link :href="route('admin.users.index')" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing"
                                    class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold transition-colors flex items-center justify-center gap-2">
                                    <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Stats Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">User Statistics</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Total Reports</span>
                                    <span class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.total_reports }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Assigned Reports</span>
                                    <span class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.assigned_reports }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Completed Tasks</span>
                                    <span class="text-lg font-bold text-green-600">{{ stats.completed_tasks }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Pending Tasks</span>
                                    <span class="text-lg font-bold text-amber-600">{{ stats.pending_tasks }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-red-200 dark:border-red-900/50 p-6">
                            <h3 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-4">Danger Zone</h3>
                            <button @click="showDeleteModal = true" class="w-full px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl text-sm font-semibold hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                                Delete User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 w-full max-w-md">
                    <div class="text-center">
                        <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-trash text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete User</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-6">
                            Are you sure you want to delete <span class="font-semibold">{{ user.name }}</span>? This action cannot be undone.
                        </p>
                        <div class="flex gap-3">
                            <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button @click="deleteUser" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold">
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
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    user: Object,
    roles: Array,
    userRoles: Array,
    stats: Object
})

const showDeleteModal = ref(false)

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.userRoles,
    is_premium: props.user.is_premium
})

const submit = () => {
    form.put(route('admin.users.update', props.user.id))
}

const deleteUser = () => {
    router.delete(route('admin.users.destroy', props.user.id))
}
</script>