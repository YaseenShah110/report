<!-- resources/js/Pages/Admin/Roles/Edit.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.roles.index')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-chevron-left"></i>
                </Link>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Edit Role: {{ role.name }}</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Role Name</label>
                            <input type="text" v-model="form.name" required 
                                :disabled="role.name === 'admin'"
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                :class="{ 'opacity-50 cursor-not-allowed': role.name === 'admin' }">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Permissions</label>
                            <div v-for="(perms, category) in permissions" :key="category" class="mb-6">
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 capitalize">{{ category }}</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <label v-for="perm in perms" :key="perm.name" class="flex items-center gap-2">
                                        <input type="checkbox" :value="perm.name" v-model="form.permissions"
                                            :disabled="role.name === 'admin'"
                                            class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ perm.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                        <Link :href="route('admin.roles.index')" class="flex-1 px-4 py-2 border rounded-xl text-center">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold">
                            Update Role
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
    role: Object,
    permissions: Object,
    rolePermissions: Array
})

const form = useForm({
    name: props.role.name,
    permissions: props.rolePermissions
})

const submit = () => {
    form.put(route('admin.roles.update', props.role.id))
}
</script>