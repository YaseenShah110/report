<!-- resources/js/Pages/Admin/Roles/Permissions.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Permission Management</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage system permissions</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.roles.index')" 
                        class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Roles
                    </Link>
                    <button @click="showCreateModal = true" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl">
                        <i class="fa-solid fa-plus"></i>
                        Create Permission
                    </button>
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
                                <p class="text-sm text-slate-500 dark:text-slate-400">Total Permissions</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_permissions }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-key text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Total Roles</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_roles }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Permission Groups</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ Object.keys(permissions).length }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-layer-group text-emerald-600 dark:text-emerald-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions by Category -->
                <div class="space-y-6">
                    <div v-for="(perms, category) in permissions" :key="category" 
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white capitalize">{{ category }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ perms.length }} permissions</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-for="permission in perms" :key="permission.name" 
                                    class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/30 rounded-xl group hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-circle-check text-indigo-500 text-xs"></i>
                                        <span class="text-sm text-slate-700 dark:text-slate-300 font-mono">{{ permission.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editPermission(permission)" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700">
                                            <i class="fa-solid fa-pen text-xs text-slate-500"></i>
                                        </button>
                                        <button @click="confirmDeletePermission(permission)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30">
                                            <i class="fa-solid fa-trash text-xs text-red-500"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Permission Modal -->
        <Teleport to="body">
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showCreateModal = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md">
                    <form @submit.prevent="createPermission">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Create Permission</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Permission Name</label>
                                    <input type="text" v-model="newPermission.name" required
                                        class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                        placeholder="e.g., edit-reports">
                                    <p class="text-xs text-slate-500 mt-1">Use format: action-resource (e.g., view-users, edit-reports)</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Group (Optional)</label>
                                    <input type="text" v-model="newPermission.group"
                                        class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                        placeholder="e.g., Reports, Users, Tasks">
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                            <button type="button" @click="showCreateModal = false" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl">Cancel</button>
                            <button type="submit" :disabled="creating" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold">
                                {{ creating ? 'Creating...' : 'Create Permission' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Edit Permission Modal -->
        <Teleport to="body">
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showEditModal = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md">
                    <form @submit.prevent="updatePermission">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Edit Permission</h3>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Permission Name</label>
                                <input type="text" v-model="editingPermission.name" required
                                    class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                            <button type="button" @click="showEditModal = false" class="flex-1 px-4 py-2 border rounded-xl">Cancel</button>
                            <button type="submit" :disabled="updating" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold">
                                {{ updating ? 'Updating...' : 'Update Permission' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md">
                    <div class="p-6 text-center">
                        <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-trash text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete Permission</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-6">
                            Are you sure you want to delete permission <span class="font-semibold">{{ deleteTarget?.name }}</span>?
                        </p>
                        <div class="flex gap-3">
                            <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border rounded-xl">Cancel</button>
                            <button @click="deletePermission" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold">
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
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    permissions: Object,
    stats: Object
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const creating = ref(false)
const updating = ref(false)

const newPermission = reactive({
    name: '',
    group: ''
})

const editingPermission = reactive({
    id: null,
    name: ''
})

const deleteTarget = ref(null)

const createPermission = () => {
    creating.value = true
    router.post(route('admin.roles.permissions.store'), newPermission, {
        onSuccess: () => {
            showCreateModal.value = false
            newPermission.name = ''
            newPermission.group = ''
            creating.value = false
        },
        onError: () => {
            creating.value = false
        }
    })
}

const editPermission = (permission) => {
    editingPermission.id = permission.id
    editingPermission.name = permission.name
    showEditModal.value = true
}

const updatePermission = () => {
    updating.value = true
    router.put(route('admin.roles.permissions.update', editingPermission.id), {
        name: editingPermission.name
    }, {
        onSuccess: () => {
            showEditModal.value = false
            updating.value = false
        },
        onError: () => {
            updating.value = false
        }
    })
}

const confirmDeletePermission = (permission) => {
    deleteTarget.value = permission
    showDeleteModal.value = true
}

const deletePermission = () => {
    router.delete(route('admin.roles.permissions.destroy', deleteTarget.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            deleteTarget.value = null
        }
    })
}
</script>