<!-- resources/js/Pages/Admin/Users/Create.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.users.index')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                    <i class="fa-solid fa-chevron-left"></i>
                </Link>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create New User</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <!-- Form Body -->
                    <div class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" v-model="form.name" required
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="John Doe">
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" v-model="form.email" required
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="john@example.com">
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password <span class="text-red-500">*</span></label>
                            <input type="password" v-model="form.password" required
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="••••••••">
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" v-model="form.password_confirmation" required
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="••••••••">
                        </div>

                        <!-- Roles -->
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

                        <!-- Premium User -->
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

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                        <Link :href="route('admin.users.index')" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold transition-colors flex items-center justify-center gap-2">
                            <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                            {{ form.processing ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    roles: Array
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    is_premium: false
})

const submit = () => {
    form.post(route('admin.users.store'))
}
</script>