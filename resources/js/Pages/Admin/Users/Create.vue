<!--
  Admin/Users/Create.vue - Create User Page
  -----------------------------------------------------------
  Form to create a new user with role assignment and premium status.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2 sm:gap-3">
        <Link :href="route('admin.users.index')" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-chevron-left text-slate-500"></i>
        </Link>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Create New User</h2>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-3xl mx-auto">
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border overflow-hidden">
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Full Name <span class="text-red-500">*</span></label>
            <input type="text" v-model="form.name" required class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm" placeholder="John Doe">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Email <span class="text-red-500">*</span></label>
            <input type="email" v-model="form.email" required class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm" placeholder="john@example.com">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Password <span class="text-red-500">*</span></label>
            <input type="password" v-model="form.password" required class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm" placeholder="••••••••">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
            <input type="password" v-model="form.password_confirmation" required class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm" placeholder="••••••••">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-semibold mb-1.5">Assign Roles</label>
            <div class="flex flex-wrap gap-2 sm:gap-3">
              <label v-for="role in roles" :key="role.name" class="flex items-center gap-1.5 sm:gap-2">
                <input type="checkbox" :value="role.name" v-model="form.roles" class="rounded border-slate-300 text-indigo-600">
                <span class="text-xs sm:text-sm">{{ role.name }}</span>
              </label>
            </div>
          </div>
          <div class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
            <div><label class="text-xs sm:text-sm font-semibold">Premium User</label><p class="text-[10px] sm:text-xs text-slate-500">Give premium access</p></div>
            <button type="button" @click="form.is_premium = !form.is_premium" class="relative inline-flex h-5 sm:h-6 w-10 sm:w-11 items-center rounded-full transition-colors" :class="form.is_premium ? 'bg-indigo-600' : 'bg-slate-200'"><span class="inline-block h-3.5 sm:h-4 w-3.5 sm:w-4 rounded-full bg-white transition-transform" :class="form.is_premium ? 'translate-x-5 sm:translate-x-6' : 'translate-x-1'"></span></button>
          </div>
        </div>
        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-t flex gap-3">
          <Link :href="route('admin.users.index')" class="px-3 sm:px-4 py-2 rounded-xl border text-xs sm:text-sm">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs sm:text-sm font-semibold">{{ form.processing ? 'Creating...' : 'Create User' }}</button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const props = defineProps({ roles: Array })
const form = useForm({ name: '', email: '', password: '', password_confirmation: '', roles: [], is_premium: false })
const submit = () => form.post(route('admin.users.store'))
</script>