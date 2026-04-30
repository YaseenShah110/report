<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 z-40 h-screen w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-all duration-300"
      :class="{ '-translate-x-64': sidebarCollapsed }">
      
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between px-4 py-4 border-b border-slate-200 dark:border-slate-700">
          <Link :href="route('dashboard')" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
              <i class="fa-solid fa-chart-line text-white text-sm"></i>
            </div>
            <span class="font-bold text-slate-900 dark:text-white">ReportGen</span>
            <span class="text-[10px] text-indigo-500">v{{ $page.props.app?.version || '1.0' }}</span>
          </Link>
          <button @click="sidebarCollapsed = !sidebarCollapsed" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
            <i class="fa-solid fa-chevron-left text-slate-500 text-sm" :class="{ 'rotate-180': sidebarCollapsed }"></i>
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
          <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <i class="fa-solid fa-gauge-high w-5"></i>
            <span>Dashboard</span>
          </NavLink>
          <NavLink :href="route('reports.index')" :active="route().current('reports.*')">
            <i class="fa-solid fa-file-lines w-5"></i>
            <span>My Reports</span>
          </NavLink>
          <NavLink :href="route('templates.index')" :active="route().current('templates.*')">
            <i class="fa-solid fa-layer-group w-5"></i>
            <span>Templates</span>
          </NavLink>

          <!-- Admin Section -->
          <template v-if="$page.props.auth.user?.roles?.includes('admin')">
            <div class="my-4 border-t border-slate-200 dark:border-slate-700"></div>
            <div class="px-3 py-1 text-xs font-semibold text-slate-400 uppercase">Administration</div>
            <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
              <i class="fa-solid fa-users w-5"></i>
              <span>Users</span>
            </NavLink>
            <NavLink :href="route('admin.tasks.index')" :active="route().current('admin.tasks.*')">
              <i class="fa-solid fa-tasks w-5"></i>
              <span>Tasks</span>
            </NavLink>
            <NavLink :href="route('admin.roles.index')" :active="route().current('admin.roles.*')">
              <i class="fa-solid fa-shield-hal w-5"></i>
              <span>Roles</span>
            </NavLink>
            <NavLink :href="route('admin.report-assignments.index')" :active="route().current('admin.report-assignments.*')">
              <i class="fa-solid fa-share-alt w-5"></i>
              <span>Assignments</span>
            </NavLink>
            <NavLink :href="route('admin.analytics.index')" :active="route().current('admin.analytics.*')">
              <i class="fa-solid fa-chart-line w-5"></i>
              <span>Analytics</span>
            </NavLink>
            <NavLink :href="route('admin.activities.index')" :active="route().current('admin.activities.*')">
              <i class="fa-solid fa-clock-rotate-left w-5"></i>
              <span>Activities</span>
            </NavLink>
          </template>
        </nav>

        <!-- User Footer with Notifications -->
        <div class="p-3 border-t border-slate-200 dark:border-slate-700 space-y-2">
          
          <!-- Notification Badges -->
          <div class="grid grid-cols-3 gap-1 mb-2">
            <Link :href="route('admin.tasks.index', { status: 'pending' })" 
                  class="relative flex flex-col items-center p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group">
              <i class="fa-solid fa-tasks text-slate-500 text-lg"></i>
              <span class="text-[10px] text-slate-500 mt-1">Pending</span>
              <span v-if="$page.props.notifications?.pending_tasks > 0" 
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                {{ $page.props.notifications.pending_tasks > 99 ? '99+' : $page.props.notifications.pending_tasks }}
              </span>
            </Link>

            <Link :href="route('admin.tasks.index', { status: 'overdue' })" 
                  class="relative flex flex-col items-center p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group">
              <i class="fa-solid fa-clock text-slate-500 text-lg"></i>
              <span class="text-[10px] text-slate-500 mt-1">Overdue</span>
              <span v-if="$page.props.notifications?.overdue_tasks > 0" 
                    class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                {{ $page.props.notifications.overdue_tasks > 99 ? '99+' : $page.props.notifications.overdue_tasks }}
              </span>
            </Link>

            <Link :href="route('reports.index')" 
                  class="relative flex flex-col items-center p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group">
              <i class="fa-solid fa-share-alt text-slate-500 text-lg"></i>
              <span class="text-[10px] text-slate-500 mt-1">Shared</span>
              <span v-if="$page.props.notifications?.assigned_reports > 0" 
                    class="absolute -top-1 -right-1 bg-indigo-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                {{ $page.props.notifications.assigned_reports > 99 ? '99+' : $page.props.notifications.assigned_reports }}
              </span>
            </Link>
          </div>

          <!-- User Menu -->
          <div class="relative">
            <button @click="showUserMenu = !showUserMenu" 
                    class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors w-full">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                {{ userInitial }}
              </div>
              <div class="flex-1 text-left">
                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $page.props.auth.user?.name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ $page.props.auth.user?.email }}</p>
              </div>
              <i class="fa-solid fa-chevron-down text-slate-400 text-xs" :class="{ 'rotate-180': showUserMenu }"></i>
            </button>

            <div v-show="showUserMenu" class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
              <Link :href="route('profile.edit')" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-user"></i> Profile
              </Link>
              <button @click="toggleDark" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i> {{ isDark ? 'Light Mode' : 'Dark Mode' }}
              </button>
              <div class="border-t border-slate-200 dark:border-slate-700"></div>
              <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                <i class="fa-solid fa-sign-out-alt"></i> Sign Out
              </Link>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="transition-all duration-300" :class="sidebarCollapsed ? 'ml-0' : 'ml-64'">
      
      <!-- Top Navbar -->
      <nav class="sticky top-0 z-30 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
        <div class="px-4 sm:px-6">
          <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-4">
              <button @click="sidebarCollapsed = !sidebarCollapsed" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 lg:hidden">
                <i class="fa-solid fa-bars text-slate-500"></i>
              </button>
              <div class="hidden lg:block">
                <slot name="header" />
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <!-- Environment Badge -->
              <div class="hidden md:block">
                <span :class="$page.props.app?.environment === 'production' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'" 
                      class="text-[10px] font-bold px-2 py-1 rounded-full">
                  {{ $page.props.app?.environment?.toUpperCase() || 'DEV' }}
                </span>
              </div>

              <!-- Premium Badge -->
              <div v-if="$page.props.auth.user?.is_premium" class="hidden md:block">
                <span class="bg-gradient-to-r from-amber-500 to-yellow-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                  <i class="fa-solid fa-crown text-[8px] mr-1"></i> PREMIUM
                </span>
              </div>

              <button @click="toggleDark" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500">
                <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
              </button>
              
              <Link :href="route('reports.create')" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <i class="fa-solid fa-plus mr-1"></i> New Report
              </Link>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Header (Mobile) -->
      <div class="p-4 sm:p-6 lg:hidden">
        <slot name="header" />
      </div>

      <!-- Main Content -->
      <main class="p-4 sm:p-6">
        <slot />
      </main>
    </div>

    <!-- Flash Messages Toast -->
    <div v-if="$page.props.flash?.success" 
         class="fixed top-20 right-4 z-50 flex items-center gap-2 bg-emerald-500 text-white px-4 py-3 rounded-xl shadow-lg animate-slide-in-right">
      <i class="fa-solid fa-circle-check"></i>
      <span>{{ $page.props.flash.success }}</span>
      <button @click="$page.props.flash.success = null" class="ml-2 hover:text-emerald-200">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>

    <div v-if="$page.props.flash?.error" 
         class="fixed top-20 right-4 z-50 flex items-center gap-2 bg-red-500 text-white px-4 py-3 rounded-xl shadow-lg animate-slide-in-right">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $page.props.flash.error }}</span>
      <button @click="$page.props.flash.error = null" class="ml-2 hover:text-red-200">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>

    <div v-if="$page.props.flash?.warning" 
         class="fixed top-20 right-4 z-50 flex items-center gap-2 bg-amber-500 text-white px-4 py-3 rounded-xl shadow-lg animate-slide-in-right">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <span>{{ $page.props.flash.warning }}</span>
      <button @click="$page.props.flash.warning = null" class="ml-2 hover:text-amber-200">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>

    <div v-if="$page.props.flash?.info" 
         class="fixed top-20 right-4 z-50 flex items-center gap-2 bg-blue-500 text-white px-4 py-3 rounded-xl shadow-lg animate-slide-in-right">
      <i class="fa-solid fa-circle-info"></i>
      <span>{{ $page.props.flash.info }}</span>
      <button @click="$page.props.flash.info = null" class="ml-2 hover:text-blue-200">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import NavLink from '@/Components/NavLink.vue'

const page = usePage()
const sidebarCollapsed = ref(false)
const showUserMenu = ref(false)
const isDark = ref(false)

const userInitial = computed(() => {
  return page.props.auth.user?.name?.charAt(0)?.toUpperCase() || 'U'
})

const toggleDark = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

onMounted(() => {
  const saved = localStorage.getItem('theme')
  isDark.value = saved === 'dark'
  document.documentElement.classList.toggle('dark', isDark.value)
})
</script>

<style scoped>
@keyframes slide-in-right {
  from {
    opacity: 0;
    transform: translateX(100px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
.animate-slide-in-right {
  animation: slide-in-right 0.3s ease-out forwards;
}
</style>