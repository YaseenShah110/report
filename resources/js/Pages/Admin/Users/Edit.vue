<!--
  Admin/Users/Edit.vue - Edit User Page
  -----------------------------------------------------------
  Form to edit user details, roles, premium status, and optional password change.
  Shows user statistics in sidebar with danger zone for deletion.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2 sm:gap-3">
        <Link :href="route('admin.users.index')" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-chevron-left text-slate-500"></i>
        </Link>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Edit User: {{ user.name }}</h2>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-5xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Main Form -->
        <div class="lg:col-span-2">
          <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border overflow-hidden">
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
              <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Full Name</label><input v-model="form.name" required class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"></div>
              <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Email</label><input v-model="form.email" required class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm"></div>
              
              <div class="border-t pt-4">
                <h3 class="text-sm font-semibold mb-3">Change Password (optional)</h3>
                <div class="space-y-3">
                  <input type="password" v-model="form.password" placeholder="New Password" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
                  <input type="password" v-model="form.password_confirmation" placeholder="Confirm Password" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
                </div>
              </div>
              
              <div><label class="block text-xs sm:text-sm font-semibold mb-1.5">Assign Roles</label><div class="flex flex-wrap gap-2 sm:gap-3"><label v-for="role in roles" :key="role.name" class="flex items-center gap-1.5"><input type="checkbox" :value="role.name" v-model="form.roles" class="rounded text-indigo-600"><span class="text-xs sm:text-sm">{{ role.name }}</span></label></div></div>
              
              <div class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                <div><label class="text-xs sm:text-sm font-semibold">Premium User</label><p class="text-[10px] sm:text-xs text-slate-500">Give premium access</p></div>
                <button type="button" @click="form.is_premium = !form.is_premium" class="relative inline-flex h-5 sm:h-6 w-10 sm:w-11 items-center rounded-full" :class="form.is_premium ? 'bg-indigo-600' : 'bg-slate-200'"><span class="inline-block h-3.5 sm:h-4 w-3.5 sm:w-4 rounded-full bg-white transition-transform" :class="form.is_premium ? 'translate-x-5 sm:translate-x-6' : 'translate-x-1'"></span></button>
              </div>
            </div>
            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-t flex gap-3">
              <Link :href="route('admin.users.index')" class="px-3 sm:px-4 py-2 rounded-xl border text-xs sm:text-sm">Cancel</Link>
              <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs sm:text-sm font-semibold">Save Changes</button>
            </div>
          </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4 sm:space-y-6">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border p-4 sm:p-6">
            <h3 class="text-sm font-semibold mb-4">User Statistics</h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between"><span class="text-slate-500">Total Reports</span><span class="font-bold">{{ stats.total_reports }}</span></div>
              <div class="flex justify-between"><span class="text-slate-500">Assigned Reports</span><span class="font-bold">{{ stats.assigned_reports }}</span></div>
              <div class="flex justify-between"><span class="text-slate-500">Completed Tasks</span><span class="font-bold text-green-600">{{ stats.completed_tasks }}</span></div>
              <div class="flex justify-between"><span class="text-slate-500">Pending Tasks</span><span class="font-bold text-amber-600">{{ stats.pending_tasks }}</span></div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-red-200 dark:border-red-900/50 p-4 sm:p-6">
            <h3 class="text-sm font-semibold text-red-600 mb-4">Danger Zone</h3>
            <button @click="showDeleteModal = true" class="w-full px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-100">Delete User</button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmationModal :show="showDeleteModal" title="Delete User?" :message="`Are you sure you want to delete ${user.name}?`" @close="showDeleteModal = false" @confirm="deleteUser" />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({ user: Object, roles: Array, userRoles: Array, stats: Object })
const showDeleteModal = ref(false)

const form = useForm({ name: props.user.name, email: props.user.email, password: '', password_confirmation: '', roles: props.userRoles, is_premium: props.user.is_premium })

const submit = () => form.put(route('admin.users.update', props.user.id))
const deleteUser = () => router.delete(route('admin.users.destroy', props.user.id), { onSuccess: () => { showDeleteModal.value = false; router.visit(route('admin.users.index')) } })
</script>