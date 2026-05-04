<!--
  Profile/Edit.vue - Profile Settings Page
  -----------------------------------------------------------
  Allows users to update their name, email, and password.
  Uses Laravel Breeze/Inertia form handling.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Profile Settings</h2>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-xl mx-auto">
      
      <!-- Profile Form -->
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-6 space-y-5 sm:space-y-6 shadow-sm">
        
        <!-- Name Field -->
        <div>
          <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Full Name <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="form.name" 
            type="text" 
            required
            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
            placeholder="Your full name"
          >
          <p v-if="form.errors.name" class="text-red-500 text-[10px] sm:text-xs mt-1">{{ form.errors.name }}</p>
        </div>

        <!-- Email Field -->
        <div>
          <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Email Address <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="form.email" 
            type="email" 
            required
            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
            placeholder="your@email.com"
          >
          <p v-if="form.errors.email" class="text-red-500 text-[10px] sm:text-xs mt-1">{{ form.errors.email }}</p>
        </div>

        <!-- Password Section (Optional) -->
        <div class="border-t border-slate-200 dark:border-slate-700 pt-5 sm:pt-6">
          <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-3 sm:mb-4">
            Change Password <span class="text-xs text-slate-500 font-normal">(optional)</span>
          </h3>
          
          <div class="space-y-3 sm:space-y-4">
            <!-- New Password -->
            <div>
              <label class="block text-[10px] sm:text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">New Password</label>
              <input 
                v-model="form.password" 
                type="password"
                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                placeholder="Leave blank to keep current"
              >
              <p v-if="form.errors.password" class="text-red-500 text-[10px] sm:text-xs mt-1">{{ form.errors.password }}</p>
            </div>
            
            <!-- Confirm Password -->
            <div>
              <label class="block text-[10px] sm:text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Confirm Password</label>
              <input 
                v-model="form.password_confirmation" 
                type="password"
                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                placeholder="Re-enter new password"
              >
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center gap-3 pt-2">
          <Link :href="route('dashboard')" class="flex-1 px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-center text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Cancel
          </Link>
          <button 
            type="submit" 
            :disabled="form.processing"
            class="flex-[2] px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors flex items-center justify-center gap-2"
          >
            <i v-if="form.processing" class="fa-solid fa-spinner fa-spin text-xs"></i>
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
/**
 * Profile Edit Script
 * Uses Inertia useForm for form handling
 */
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const page = usePage()

// Initialize form with current user data
const form = useForm({
  name: page.props.auth.user?.name || '',
  email: page.props.auth.user?.email || '',
  password: '',
  password_confirmation: '',
})

/**
 * Submit the form to update profile
 */
const submit = () => {
  form.put(route('profile.update'), {
    onSuccess: () => {
      // Reset password fields after successful update
      form.password = ''
      form.password_confirmation = ''
      window.showToast?.('Profile updated successfully', 'success')
    }
  })
}
</script>