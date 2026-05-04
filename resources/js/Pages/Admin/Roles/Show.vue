<!--
  Admin/Roles/Show.vue - Role Details Page
  -----------------------------------------------------------
  Displays role information including assigned permissions and users.
  Shows permissions with green badges for assigned, gray for unassigned.
  Admin only access.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2 sm:gap-3">
        <Link :href="route('admin.roles.index')" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-chevron-left text-slate-500"></i>
        </Link>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Role Details: {{ role.name }}</h2>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Main Content - Permissions -->
        <div class="lg:col-span-2">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-4 sm:mb-6">
              Permissions
              <span class="text-xs sm:text-sm font-normal text-slate-500 ml-2">({{ role.permissions?.length || 0 }} assigned)</span>
            </h3>
            
            <!-- Permissions grouped by category -->
            <div v-for="(perms, category) in permissions" :key="category" class="mb-4 sm:mb-6">
              <h4 class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 capitalize">
                {{ category }}
                <span class="text-[10px] sm:text-xs font-normal text-slate-400 ml-1">
                  ({{ countAssigned(perms) }}/{{ perms.length }})
                </span>
              </h4>
              <div class="flex flex-wrap gap-1.5 sm:gap-2">
                <!-- Each permission as a badge -->
                <span 
                  v-for="perm in perms" :key="perm.name" 
                  class="px-2 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs rounded-full font-medium transition-all"
                  :class="isPermissionAssigned(perm.name) 
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' 
                    : 'bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-500 border border-slate-200 dark:border-slate-600'"
                >
                  <i :class="isPermissionAssigned(perm.name) ? 'fa-solid fa-check-circle' : 'fa-regular fa-circle'" class="mr-1 text-[8px] sm:text-[10px]"></i>
                  {{ perm.name }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar - Role Info & Users -->
        <div class="space-y-4 sm:space-y-6">
          
          <!-- Role Information Card -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-4">Role Information</h3>
            <div class="space-y-3 text-xs sm:text-sm">
              <div>
                <p class="text-slate-500 dark:text-slate-400">Role Name</p>
                <p class="font-medium text-slate-900 dark:text-white capitalize">{{ role.name }}</p>
              </div>
              <div>
                <p class="text-slate-500 dark:text-slate-400">Guard Name</p>
                <p class="font-medium text-slate-900 dark:text-white">{{ role.guard_name || 'web' }}</p>
              </div>
              <div>
                <p class="text-slate-500 dark:text-slate-400">Permissions Count</p>
                <p class="font-medium text-slate-900 dark:text-white">{{ role.permissions?.length || 0 }}</p>
              </div>
              <div>
                <p class="text-slate-500 dark:text-slate-400">Users Assigned</p>
                <p class="font-medium text-slate-900 dark:text-white">{{ role.users_count || users?.length || 0 }}</p>
              </div>
              <div>
                <p class="text-slate-500 dark:text-slate-400">Created At</p>
                <p class="font-medium text-slate-900 dark:text-white">{{ formatDate(role.created_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Users with this Role -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-4">
              Users with this Role
              <span class="text-xs font-normal text-slate-500 ml-1">({{ users?.length || 0 }})</span>
            </h3>
            
            <div v-if="users && users.length > 0" class="space-y-2">
              <div v-for="user in users.slice(0, 10)" :key="user.id" 
                   class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-[10px] sm:text-xs font-bold flex-shrink-0">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <p class="text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{{ user.name }}</p>
                  <p class="text-[10px] sm:text-xs text-slate-400 truncate">{{ user.email }}</p>
                </div>
              </div>
              <p v-if="users.length > 10" class="text-[10px] sm:text-xs text-slate-400 text-center mt-2">
                +{{ users.length - 10 }} more users
              </p>
            </div>
            
            <div v-else class="text-center py-4 text-slate-400 text-xs sm:text-sm">
              <i class="fa-solid fa-users-slash text-lg sm:text-xl mb-1 block"></i>
              No users assigned to this role
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
            <div class="space-y-2">
              <Link :href="route('admin.roles.edit', role.id)" 
                    class="w-full flex items-center justify-center gap-1.5 px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors">
                <i class="fa-solid fa-pen-to-square text-xs"></i> Edit Role
              </Link>
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
  role: Object,        // Role with permissions
  permissions: Object, // All permissions grouped by category
  users: Array         // Users with this role
})

/**
 * Check if a permission is assigned to this role
 */
const isPermissionAssigned = (permName) => {
  return props.role?.permissions?.includes(permName) || false
}

/**
 * Count how many permissions in a category are assigned
 */
const countAssigned = (perms) => {
  return perms.filter(p => isPermissionAssigned(p.name)).length
}

/**
 * Format date for display
 */
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>