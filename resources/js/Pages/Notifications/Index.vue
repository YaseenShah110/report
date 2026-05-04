<!--
  Notifications/Index.vue - All Notifications Page
  -----------------------------------------------------------
  Displays all notifications for the current user.
  Supports filtering by: all, unread, read, trashed.
  Actions: mark as read, mark all read, delete, restore.
  Responsive design with mobile-first approach.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Notifications</h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Stay updated with your latest activities</p>
        </div>
        <button 
          v-if="unreadCount > 0"
          @click="markAllAsRead" 
          class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-xs sm:text-sm font-medium rounded-xl transition-colors"
        >
          <i class="fa-solid fa-check-double text-xs"></i> Mark All as Read
        </button>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-4xl mx-auto">
      
      <!-- Filter Tabs -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-2 sm:p-4 mb-4 sm:mb-6">
        <div class="flex flex-wrap gap-1.5 sm:gap-3">
          <button
            v-for="filter in filterOptions"
            :key="filter.value"
            @click="activeFilter = filter.value"
            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-medium transition-all flex items-center gap-1.5"
            :class="activeFilter === filter.value 
              ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/25' 
              : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600'"
          >
            <i :class="filter.icon" class="text-[10px] sm:text-xs"></i>
            {{ filter.label }}
            <span v-if="filter.count > 0" class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] sm:text-xs"
                  :class="activeFilter === filter.value ? 'bg-white/20' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'">
              {{ filter.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="space-y-2 sm:space-y-3">
        
        <!-- Empty State -->
        <div v-if="notifications.data.length === 0" class="text-center py-12 sm:py-16 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
          <i class="fa-solid fa-bell-slash text-3xl sm:text-5xl text-slate-300 dark:text-slate-600 mb-3 sm:mb-4 block"></i>
          <h3 class="text-base sm:text-lg font-semibold text-slate-500 dark:text-slate-400">No notifications</h3>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">You're all caught up!</p>
        </div>

        <!-- Notification Items -->
        <div 
          v-for="notification in notifications.data" 
          :key="notification.id"
          class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border transition-all duration-200 hover:shadow-md"
          :class="[
            notification.read_at 
              ? 'border-slate-200 dark:border-slate-700' 
              : 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/30 dark:bg-indigo-900/10',
            notification.trashed ? 'opacity-60' : ''
          ]"
        >
          <div class="p-3 sm:p-5">
            <div class="flex items-start gap-2 sm:gap-4">
              
              <!-- Notification Icon -->
              <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                   :class="notification.read_at ? 'bg-slate-100 dark:bg-slate-700' : 'bg-indigo-100 dark:bg-indigo-900/30'"
                   :style="{ background: !notification.read_at ? (notification.color || '#6366f1') + '20' : '' }">
                <i :class="notification.icon || 'fa-solid fa-bell'" class="text-base sm:text-lg" :style="{ color: notification.color || '#6366f1' }"></i>
              </div>

              <!-- Notification Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <h4 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white">{{ notification.title }}</h4>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">{{ notification.message }}</p>
                  </div>
                  
                  <!-- Action Buttons -->
                  <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                    <!-- Type Badge -->
                    <span class="text-[9px] sm:text-[10px] px-1.5 sm:px-2 py-0.5 rounded-full capitalize font-medium"
                          :class="getTypeClass(notification.type)">
                      {{ formatType(notification.type) }}
                    </span>
                  </div>
                </div>

                <!-- Footer: Time + Actions -->
                <div class="flex items-center justify-between mt-2 sm:mt-3">
                  <span class="text-[10px] sm:text-xs text-slate-400">
                    <i class="fa-regular fa-clock mr-1"></i>
                    {{ notification.time_ago }}
                  </span>
                  
                  <div class="flex items-center gap-1.5 sm:gap-2">
                    <!-- Unread indicator -->
                    <span v-if="!notification.read_at" class="w-2 h-2 rounded-full flex-shrink-0" :style="{ background: notification.color || '#6366f1' }"></span>
                    
                    <!-- Mark as Read button -->
                    <button v-if="!notification.read_at" @click="markAsRead(notification.id)" class="text-[10px] sm:text-xs text-indigo-500 hover:text-indigo-600 font-medium">
                      Mark read
                    </button>
                    
                    <!-- Restore button (for trashed) -->
                    <button v-if="notification.trashed" @click="restoreNotification(notification.id)" class="text-[10px] sm:text-xs text-amber-500 hover:text-amber-600 font-medium">
                      <i class="fa-solid fa-rotate-left mr-1"></i> Restore
                    </button>
                    
                    <!-- Delete button -->
                    <button v-if="!notification.trashed" @click="softDelete(notification.id)" class="text-[10px] sm:text-xs text-red-500 hover:text-red-600 font-medium">
                      <i class="fa-solid fa-trash mr-1"></i> Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="notifications.links" class="mt-4 sm:mt-6">
        <Pagination :links="notifications.links" :from="notifications.from" :to="notifications.to" :total="notifications.total" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
/**
 * Notifications Page Script
 * Handles: filtering, mark read, mark all read, soft delete, restore
 */
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

// Props from Laravel controller
const props = defineProps({ 
  notifications: Object,   // Paginated notifications
  unread_count: Number,    // Unread count
  trashed_count: Number,   // Trashed count
  filters: Object          // Current filter
})

// Active filter tab
const activeFilter = ref(props.filters?.filter || 'all')

// Filter options with counts
const filterOptions = computed(() => [
  { value: 'all', label: 'All', icon: 'fa-solid fa-bell', count: props.notifications?.total || 0 },
  { value: 'unread', label: 'Unread', icon: 'fa-solid fa-circle', count: props.unread_count || 0 },
  { value: 'read', label: 'Read', icon: 'fa-solid fa-check', count: (props.notifications?.total || 0) - (props.unread_count || 0) },
  { value: 'trashed', label: 'Trash', icon: 'fa-solid fa-trash', count: props.trashed_count || 0 }
])

/**
 * Format notification type for display
 */
const formatType = (type) => {
  return type?.replace(/_/g, ' ') || 'Info'
}

/**
 * Get CSS class for notification type badge
 */
const getTypeClass = (type) => {
  const classes = {
    'task_created': 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    'task_completed': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    'report_assigned': 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    'report_shared': 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
  }
  return classes[type] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'
}

/**
 * Mark a single notification as read
 */
const markAsRead = async (id) => {
  try {
    await axios.put(route('notifications.mark-read', id))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

/**
 * Mark ALL notifications as read
 */
const markAllAsRead = async () => {
  try {
    await axios.put(route('notifications.mark-all-read'))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

/**
 * Soft delete a notification
 */
const softDelete = async (id) => {
  try {
    await axios.delete(route('notifications.destroy', id))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to delete notification:', error)
  }
}

/**
 * Restore a soft-deleted notification
 */
const restoreNotification = async (id) => {
  try {
    await axios.post(route('notifications.restore', id))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to restore notification:', error)
  }
}

/**
 * Watch for filter changes and reload the page
 */
watch(activeFilter, (value) => {
  router.get(route('notifications.index', { filter: value }), {}, {
    preserveState: true,
    preserveScroll: true,
    only: ['notifications'],
  })
})
</script>