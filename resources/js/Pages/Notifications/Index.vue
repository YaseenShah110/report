<!-- resources/js/Pages/Notifications/Index.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Notifications</h2>
          <p class="text-sm text-slate-500 mt-1">Stay updated with your latest activities</p>
        </div>
        <div class="flex items-center gap-3">
          <button 
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="px-4 py-2 bg-indigo-500 text-white text-sm font-medium rounded-xl hover:bg-indigo-600 transition-colors"
          >
            <i class="fa-solid fa-check-double mr-2"></i>
            Mark All as Read
          </button>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
          <button
            v-for="filter in filters"
            :key="filter.value"
            @click="activeFilter = filter.value"
            class="px-4 py-2 rounded-xl text-sm font-medium transition-all"
            :class="activeFilter === filter.value 
              ? 'bg-indigo-500 text-white shadow-lg' 
              : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600'"
          >
            <i :class="filter.icon" class="mr-2"></i>
            {{ filter.label }}
            <span v-if="filter.count > 0" class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs"
                  :class="activeFilter === filter.value ? 'bg-white/20' : 'bg-indigo-100 dark:bg-indigo-900 text-indigo-600'">
              {{ filter.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="space-y-3">
        <div v-if="notifications.data.length === 0" class="text-center py-12">
          <i class="fa-solid fa-bell-slash text-5xl text-slate-300 dark:text-slate-600 mb-4 block"></i>
          <h3 class="text-lg font-semibold text-slate-500 dark:text-slate-400">No notifications</h3>
          <p class="text-sm text-slate-400 mt-1">You're all caught up!</p>
        </div>

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
          <div class="p-5">
            <div class="flex items-start gap-4">
              <!-- Icon -->
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                   :class="notification.read_at ? 'bg-slate-100 dark:bg-slate-700' : 'bg-indigo-100 dark:bg-indigo-900/30'"
                   :style="{ background: !notification.read_at ? (notification.color || '#6366f1') + '20' : '' }">
                <i :class="notification.icon || 'fa-solid fa-bell'" class="text-lg" :style="{ color: notification.color || '#6366f1' }"></i>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ notification.title }}</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ notification.message }}</p>
                  </div>
                  
                  <!-- Actions -->
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-[11px] px-2 py-0.5 rounded-full capitalize"
                          :class="getTypeClass(notification.type)">
                      {{ formatType(notification.type) }}
                    </span>
                    
                    <div class="relative" v-if="!notification.trashed">
                      <button @click="toggleActionMenu(notification.id)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-ellipsis-vertical text-slate-400 text-sm"></i>
                      </button>
                      
                      <!-- Dropdown Menu -->
                      <div v-if="actionMenuId === notification.id" class="absolute right-0 mt-1 w-40 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 z-10 py-2 animate-scale-in">
                        <button 
                          v-if="!notification.read_at"
                          @click="markAsRead(notification.id)"
                          class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-600 flex items-center gap-2"
                        >
                          <i class="fa-solid fa-check text-green-500"></i>
                          Mark as Read
                        </button>
                        <button 
                          v-if="notification.read_at"
                          @click="markAsUnread(notification.id)"
                          class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-600 flex items-center gap-2"
                        >
                          <i class="fa-solid fa-circle text-amber-500"></i>
                          Mark as Unread
                        </button>
                        <button 
                          @click="softDelete(notification.id)"
                          class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-600 flex items-center gap-2 text-red-500"
                        >
                          <i class="fa-solid fa-trash"></i>
                          Delete
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between mt-3">
                  <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400">
                      <i class="fa-regular fa-clock mr-1"></i>
                      {{ notification.time_ago }}
                    </span>
                    <span v-if="!notification.read_at" class="w-2 h-2 rounded-full" :style="{ background: notification.color || '#6366f1' }"></span>
                  </div>
                  
                  <div class="flex items-center gap-2">
                    <button 
                      v-if="notification.trashed"
                      @click="restore(notification.id)"
                      class="text-xs text-amber-500 hover:text-amber-600 font-medium flex items-center gap-1"
                    >
                      <i class="fa-solid fa-rotate-left"></i>
                      Restore
                    </button>
                    <button 
                      v-if="!notification.trashed && notification.action_url"
                      @click="navigateToNotification(notification)"
                      class="text-xs text-indigo-500 hover:text-indigo-600 font-medium"
                    >
                      View Details
                      <i class="fa-solid fa-arrow-right ml-1"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="notifications.links" class="mt-6">
        <Pagination :links="notifications.links" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import axios from 'axios'

const page = usePage()
const props = defineProps({
  notifications: Object,
  unread_count: Number,
  trashed_count: Number,
  filters: Object,
})

const actionMenuId = ref(null)
const activeFilter = ref(props.filters?.filter || 'all')

const filters = computed(() => [
  { 
    value: 'all', 
    label: 'All', 
    icon: 'fa-solid fa-bell',
    count: props.notifications?.total || 0 
  },
  { 
    value: 'unread', 
    label: 'Unread', 
    icon: 'fa-solid fa-circle',
    count: props.unread_count || 0 
  },
  { 
    value: 'read', 
    label: 'Read', 
    icon: 'fa-solid fa-check',
    count: (props.notifications?.total || 0) - (props.unread_count || 0)
  },
  { 
    value: 'trashed', 
    label: 'Trash', 
    icon: 'fa-solid fa-trash',
    count: props.trashed_count || 0 
  },
])

const formatType = (type) => {
  return type?.replace(/_/g, ' ') || 'Info'
}

const getTypeClass = (type) => {
  const classes = {
    'task_created': 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    'task_completed': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    'report_assigned': 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    'report_shared': 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
  }
  return classes[type] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'
}

const toggleActionMenu = (id) => {
  actionMenuId.value = actionMenuId.value === id ? null : id
}

const markAsRead = async (id) => {
  try {
    await axios.put(route('notifications.mark-read', id))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
  actionMenuId.value = null
}

const markAsUnread = async (id) => {
  // You'll need to add this endpoint
  try {
    await axios.put(route('notifications.mark-unread', id))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to mark as unread:', error)
  }
  actionMenuId.value = null
}

const markAllAsRead = async () => {
  try {
    await axios.put(route('notifications.mark-all-read'))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const softDelete = async (id) => {
  try {
    await axios.delete(route('notifications.destroy', id))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to delete notification:', error)
  }
  actionMenuId.value = null
}

const restore = async (id) => {
  try {
    await axios.post(route('notifications.restore', id))
    router.reload({ only: ['notifications'] })
  } catch (error) {
    console.error('Failed to restore notification:', error)
  }
}

const navigateToNotification = async (notification) => {
  if (!notification.read_at) {
    await markAsRead(notification.id)
  }
  if (notification.action_url) {
    router.visit(notification.action_url)
  }
}

// Watch filter changes
import { watch } from 'vue'
watch(activeFilter, (value) => {
  router.get(route('notifications.index', { filter: value }), {}, {
    preserveState: true,
    preserveScroll: true,
    only: ['notifications'],
  })
})
</script>