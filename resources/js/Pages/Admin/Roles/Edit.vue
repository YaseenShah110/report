<!--
  Admin/Roles/Edit.vue - Edit Role Page
  -----------------------------------------------------------
  Form to edit an existing role's name and permissions.
  Admin role name cannot be changed (disabled input).
  Admin only access.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2 sm:gap-3">
        <Link :href="route('admin.roles.index')" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-chevron-left text-slate-500"></i>
        </Link>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Edit Role: {{ role.name }}</h2>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-3xl mx-auto">
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
          <!-- Role Name -->
          <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Role Name
            </label>
            <input 
              type="text" 
              v-model="form.name" 
              required 
              :disabled="role.name === 'admin'"
              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500"
              :class="{ 'opacity-50 cursor-not-allowed': role.name === 'admin' }"
            >
            <p v-if="role.name === 'admin'" class="text-amber-500 text-[10px] sm:text-xs mt-1">
              <i class="fa-solid fa-lock mr-1"></i> Admin role name cannot be changed
            </p>
            <p v-if="form.errors.name" class="text-red-500 text-[10px] sm:text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- Permissions -->
          <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
              Permissions
            </label>
            
            <div v-for="(perms, category) in permissions" :key="category" class="mb-4 sm:mb-6">
              <h4 class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 capitalize">
                {{ category }}
              </h4>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                <label v-for="perm in perms" :key="perm.name" 
                       class="flex items-center gap-1.5 sm:gap-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 p-1.5 rounded-lg transition-colors">
                  <input 
                    type="checkbox" 
                    :value="perm.name" 
                    v-model="form.permissions"
                    :disabled="role.name === 'admin'"
                    class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500"
                    :class="{ 'opacity-50 cursor-not-allowed': role.name === 'admin' }"
                  >
                  <span class="text-xs sm:text-sm text-slate-700 dark:text-slate-300">{{ perm.name }}</span>
                </label>
              </div>
            </div>
            <p v-if="role.name === 'admin'" class="text-amber-500 text-[10px] sm:text-xs">
              <i class="fa-solid fa-lock mr-1"></i> Admin role always has all permissions
            </p>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
          <Link :href="route('admin.roles.index')" 
                class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-center text-xs sm:text-sm">
            Cancel
          </Link>
          <button 
            type="submit" 
            :disabled="form.processing"
            class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs sm:text-sm font-semibold">
            {{ form.processing ? 'Updating...' : 'Update Role' }}
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  role: Object,           // Current role data
  permissions: Object,    // All permissions grouped by category
  rolePermissions: Array  // Current role's permission names
})

// Initialize form with current role data
const form = useForm({
  name: props.role.name,
  permissions: props.rolePermissions || []
})

// Submit form to update role
const submit = () => {
  form.put(route('admin.roles.update', props.role.id))
}
</script>