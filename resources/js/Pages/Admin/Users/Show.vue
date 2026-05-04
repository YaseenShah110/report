<!--
  Admin/Users/Show.vue - User Details Page
  -----------------------------------------------------------
  Displays detailed information about a specific user.
  Shows user profile, statistics (reports, tasks), and actions.
  Admins can edit user or impersonate from this page.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2 sm:gap-3">
          <Link :href="route('admin.users.index')" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left text-slate-500 text-sm"></i>
          </Link>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">User Details: {{ user.name }}</h2>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('admin.users.edit', user.id)" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors">
            <i class="fa-solid fa-pen mr-1.5"></i> Edit User
          </Link>
          <button v-if="user.id !== $page.props.auth.user.id" @click="impersonate" class="px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-mask mr-1.5"></i> Impersonate
          </button>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-5xl mx-auto">
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Main User Info Card -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
          
          <!-- User Avatar & Basic Info -->
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 mb-6">
            <!-- Avatar with gradient -->
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl sm:text-2xl font-bold shadow-lg flex-shrink-0">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            
            <div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">{{ user.name }}</h3>
              <p class="text-xs sm:text-sm text-slate-500">{{ user.email }}</p>
              
              <!-- Badges -->
              <div class="flex flex-wrap gap-1.5 mt-2">
                <!-- Role Badges -->
                <span v-for="role in user.roles" :key="role.id" 
                      class="px-2 py-0.5 text-[10px] sm:text-xs rounded-full font-medium"
                      :class="role.name === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 
                             role.name === 'manager' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 
                             'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'">
                  {{ role.name }}
                </span>
                
                <!-- Premium Badge -->
                <span v-if="user.is_premium" class="px-2 py-0.5 text-[10px] sm:text-xs rounded-full font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                  <i class="fa-solid fa-crown mr-1 text-[8px]"></i> Premium
                </span>
                
                <!-- Verification Status -->
                <span :class="user.email_verified_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" 
                      class="px-2 py-0.5 text-[10px] sm:text-xs rounded-full font-medium">
                  <i :class="user.email_verified_at ? 'fa-solid fa-check-circle' : 'fa-solid fa-clock'"></i>
                  {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                </span>
              </div>
            </div>
          </div>
          
          <!-- User Information Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 border-t border-slate-200 dark:border-slate-700 pt-4 sm:pt-6">
            <div class="p-3 sm:p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
              <p class="text-[10px] sm:text-xs text-slate-500 uppercase tracking-wider">User ID</p>
              <p class="text-sm sm:text-base font-mono font-medium text-slate-900 dark:text-white mt-1">#{{ user.id }}</p>
            </div>
            <div class="p-3 sm:p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
              <p class="text-[10px] sm:text-xs text-slate-500 uppercase tracking-wider">Joined</p>
              <p class="text-sm sm:text-base font-medium text-slate-900 dark:text-white mt-1">{{ formatDate(user.created_at) }}</p>
            </div>
            <div class="p-3 sm:p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
              <p class="text-[10px] sm:text-xs text-slate-500 uppercase tracking-wider">Account Type</p>
              <p class="text-sm sm:text-base font-medium text-slate-900 dark:text-white mt-1">{{ user.is_premium ? 'Premium' : 'Free' }}</p>
            </div>
            <div class="p-3 sm:p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
              <p class="text-[10px] sm:text-xs text-slate-500 uppercase tracking-wider">Roles</p>
              <p class="text-sm sm:text-base font-medium text-slate-900 dark:text-white mt-1">{{ user.roles?.map(r => r.name).join(', ') || 'No roles' }}</p>
            </div>
          </div>
        </div>
        
        <!-- Statistics Sidebar -->
        <div class="space-y-4 sm:space-y-6">
          
          <!-- Stats Card -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-4">User Statistics</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Total Reports</span>
                <span class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.total_reports }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Assigned Reports</span>
                <span class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.assigned_reports }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Completed Tasks</span>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ stats.completed_tasks }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Pending Tasks</span>
                <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ stats.pending_tasks }}</span>
              </div>
            </div>
          </div>
          
          <!-- Quick Actions Card -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
              <Link :href="route('admin.users.edit', user.id)" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors">
                <i class="fa-solid fa-pen-to-square text-xs"></i> Edit User
              </Link>
              <button v-if="user.id !== $page.props.auth.user.id" @click="impersonate" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-mask text-xs"></i> Impersonate User
              </button>
              <Link :href="route('admin.users.activities', user.id)" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-clock-rotate-left text-xs"></i> View Activity Logs
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
/**
 * User Details Page Script
 * Handles: displaying user info, impersonation, navigation
 */
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const page = usePage()
const props = defineProps({ 
  user: Object,   // User model with roles
  stats: Object   // User statistics
})

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

/**
 * Impersonate this user (admin only)
 */
const impersonate = () => {
  router.post(route('admin.users.impersonate', props.user.id), {}, {
    onSuccess: () => {
      window.showToast?.('Impersonating user', 'info')
    }
  })
}
</script>