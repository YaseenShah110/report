<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 transition-all duration-300"
       :style="{ fontFamily: fontFamily }">
    
    <!-- Keyboard Shortcuts Overlay -->
    <div v-if="showShortcuts" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showShortcuts = false"></div>
      <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 animate-scale-in"
           :style="{ borderRadius: `${cardRadius * 2}px` }">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">Keyboard Shortcuts</h3>
          <button @click="showShortcuts = false" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="space-y-3">
          <div v-for="shortcut in shortcutsList" :key="shortcut.key" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">
            <span class="text-sm">{{ shortcut.description }}</span>
            <kbd class="px-2 py-1 text-xs bg-slate-100 dark:bg-slate-700 rounded-md font-mono">{{ shortcut.key }}</kbd>
          </div>
        </div>
      </div>
    </div>

    <!-- Premium Sidebar -->
    <aside 
      ref="sidebarRef"
      class="fixed left-0 top-0 z-50 h-screen bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 shadow-2xl overflow-hidden flex flex-col"
      :class="sidebarCollapsed ? 'w-20' : 'w-72'"
      :style="{ borderRadius: sidebarCollapsed ? '0' : `0 ${cardRadius}px ${cardRadius}px 0` }"
    >
      <!-- Sidebar Header with Logo -->
      <div class="flex-shrink-0 flex items-center justify-between px-5 py-5 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3" :class="{ 'justify-center w-full': sidebarCollapsed }">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg relative group" 
               :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${cardRadius}px` }">
            <i class="fa-solid fa-chart-line text-white text-lg group-hover:animate-pulse"></i>
            <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-800" 
                  :class="{ 'animate-ping opacity-75': hasUnreadNotifications }"></span>
            <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-800"></span>
          </div>
          <transition name="fade-slide">
            <div v-if="!sidebarCollapsed" class="flex flex-col">
              <span class="font-bold text-slate-900 dark:text-white text-lg">ReportGen</span>
              <span class="text-[10px] font-semibold -mt-1" :style="{ color: accentColor }">Enterprise Edition</span>
            </div>
          </transition>
        </div>
        <button 
          @click="toggleSidebar" 
          class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all duration-300 hover:scale-110 flex-shrink-0"
          :class="{ 'rotate-180': sidebarCollapsed }"
          :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
          :aria-label="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
        >
          <i class="fa-solid fa-chevron-left text-slate-500 text-sm"></i>
        </button>
      </div>

      <!-- User Profile Section -->
      <div class="flex-shrink-0 p-4 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-3" :class="{ 'justify-center': sidebarCollapsed }">
          <div class="relative">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-lg"
                 :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${cardRadius}px` }"
                 :title="$page.props.auth.user?.name">
              {{ userInitial }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-800"
                 :title="isOnline ? 'Online' : 'Offline'"></div>
          </div>
          <transition name="fade-slide">
            <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
              <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $page.props.auth.user?.name }}</p>
              <p class="text-xs text-slate-500 truncate">{{ $page.props.auth.user?.email }}</p>
              <div class="flex items-center gap-1 mt-1">
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600">Pro</span>
                <span v-if="$page.props.auth.user?.is_premium" class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">
                  <i class="fa-solid fa-crown text-[8px] mr-0.5"></i>Premium
                </span>
                <span v-if="$page.props.auth.is_impersonating" class="text-[10px] px-1.5 py-0.5 rounded bg-red-100 text-red-700">
                  <i class="fa-solid fa-mask text-[8px] mr-0.5"></i>Impersonating
                </span>
              </div>
            </div>
          </transition>
        </div>
      </div>

      <!-- Navigation with Dropdowns - Scrollable Area -->
      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 scrollbar-thin">
        
        <!-- Dashboard -->
        <Link 
          :href="route('dashboard')"
          class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 group relative"
          :class="[
            route().current('dashboard') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
            sidebarCollapsed ? 'justify-center' : ''
          ]"
          :title="sidebarCollapsed ? 'Dashboard' : ''"
        >
          <i class="fa-solid fa-gauge-high text-lg group-hover:scale-110 transition-transform"></i>
          <transition name="fade-slide">
            <span v-if="!sidebarCollapsed" class="text-sm font-medium">Dashboard</span>
          </transition>
          <div v-if="route().current('dashboard')" class="absolute right-2 top-1/2 -translate-y-1/2 w-1.5 h-8 rounded-full" :style="{ background: accentColor }"></div>
        </Link>

        <!-- Reports Dropdown -->
        <div class="relative">
          <button 
            @click="toggleDropdown('reports')"
            class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 group"
            :class="[
              isReportsOpen || isReportsActive ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
              sidebarCollapsed ? 'justify-center' : ''
            ]"
            :title="sidebarCollapsed ? 'Reports' : ''"
            :aria-expanded="isReportsOpen"
          >
            <i class="fa-solid fa-file-lines text-lg group-hover:scale-110 transition-transform"></i>
            <transition name="fade-slide">
              <span v-if="!sidebarCollapsed" class="text-sm font-medium flex-1 text-left">Reports</span>
            </transition>
            <transition name="fade-slide">
              <i v-if="!sidebarCollapsed" :class="isReportsOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-xs transition-transform duration-300"></i>
            </transition>
          </button>
          
          <div v-show="isReportsOpen && !sidebarCollapsed" class="mt-1 ml-8 space-y-1 overflow-hidden transition-all duration-300">
            <Link :href="route('reports.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('reports.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-list-ul text-xs"></i>
              <span>All Reports</span>
            </Link>
            <Link :href="route('reports.create')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('reports.create') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-plus text-xs"></i>
              <span>Create Report</span>
              <kbd class="ml-auto text-[10px] text-slate-400">Ctrl+N</kbd>
            </Link>
            <Link :href="route('reports.assigned')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('reports.assigned') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-share-alt text-xs"></i>
              <span>Shared with Me</span>
              <span v-if="$page.props.notifications?.assigned_reports > 0" class="ml-auto bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full animate-pulse">{{ $page.props.notifications.assigned_reports }}</span>
            </Link>
            <Link :href="route('templates.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('templates.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-layer-group text-xs"></i>
              <span>Templates</span>
            </Link>
          </div>
        </div>

        <!-- My Tasks -->
        <Link 
          :href="route('admin.tasks.my')"
          class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 group relative"
          :class="[
            route().current('admin.tasks.my') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
            sidebarCollapsed ? 'justify-center' : ''
          ]"
          :title="sidebarCollapsed ? 'My Tasks' : ''"
        >
          <i class="fa-solid fa-tasks text-lg group-hover:scale-110 transition-transform"></i>
          <transition name="fade-slide">
            <span v-if="!sidebarCollapsed" class="text-sm font-medium">My Tasks</span>
          </transition>
          <span v-if="$page.props.notifications?.pending_tasks > 0" class="absolute top-1 right-1 bg-red-500 text-white text-[10px] min-w-[20px] h-5 rounded-full flex items-center justify-center px-1 animate-pulse">
            {{ $page.props.notifications.pending_tasks }}
          </span>
        </Link>

        <!-- Admin Dropdown -->
        <div v-if="$page.props.auth.user?.roles?.includes('admin')" class="relative">
          <button 
            @click="toggleDropdown('admin')"
            class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 group"
            :class="[
              isAdminOpen || isAdminActive ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
              sidebarCollapsed ? 'justify-center' : ''
            ]"
            :title="sidebarCollapsed ? 'Administration' : ''"
            :aria-expanded="isAdminOpen"
          >
            <i class="fa-solid fa-shield-halved text-lg group-hover:scale-110 transition-transform"></i>
            <transition name="fade-slide">
              <span v-if="!sidebarCollapsed" class="text-sm font-medium flex-1 text-left">Administration</span>
            </transition>
            <transition name="fade-slide">
              <i v-if="!sidebarCollapsed" :class="isAdminOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-xs transition-transform duration-300"></i>
            </transition>
          </button>
          
          <div v-show="isAdminOpen && !sidebarCollapsed" class="mt-1 ml-8 space-y-1 overflow-hidden transition-all duration-300">
            <Link :href="route('admin.users.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('admin.users.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-users text-xs"></i>
              <span>User Management</span>
            </Link>
            <Link :href="route('admin.roles.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('admin.roles.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-shield-halved text-xs"></i>
              <span>Roles & Permissions</span>
            </Link>
            <Link :href="route('admin.tasks.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('admin.tasks.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-tasks text-xs"></i>
              <span>All Tasks</span>
            </Link>
            <Link :href="route('admin.report-assignments.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('admin.report-assignments.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-share-alt text-xs"></i>
              <span>Report Assignments</span>
            </Link>
            <Link :href="route('admin.analytics.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('admin.analytics.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-chart-pie text-xs"></i>
              <span>Analytics</span>
            </Link>
            <Link :href="route('admin.activities.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('admin.activities.index') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-clock-rotate-left text-xs"></i>
              <span>Activity Logs</span>
            </Link>
          </div>
        </div>

        <!-- Settings Dropdown -->
        <div class="relative">
          <button 
            @click="toggleDropdown('settings')"
            class="flex items-center gap-3 w-full p-3 rounded-xl transition-all duration-200 group"
            :class="[
              isSettingsOpen ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
              sidebarCollapsed ? 'justify-center' : ''
            ]"
            :title="sidebarCollapsed ? 'Settings' : ''"
            :aria-expanded="isSettingsOpen"
          >
            <i class="fa-solid fa-gear text-lg group-hover:scale-110 transition-transform group-hover:rotate-90"></i>
            <transition name="fade-slide">
              <span v-if="!sidebarCollapsed" class="text-sm font-medium flex-1 text-left">Settings</span>
            </transition>
            <transition name="fade-slide">
              <i v-if="!sidebarCollapsed" :class="isSettingsOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-xs transition-transform duration-300"></i>
            </transition>
          </button>
          
          <div v-show="isSettingsOpen && !sidebarCollapsed" class="mt-1 ml-8 space-y-1 overflow-hidden transition-all duration-300">
            <button @click="openSettingsModal('appearance')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
              <i class="fa-solid fa-palette text-xs"></i>
              <span>Appearance</span>
            </button>
            <button @click="openSettingsModal('preferences')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
              <i class="fa-solid fa-sliders text-xs"></i>
              <span>Preferences</span>
            </button>
            <button @click="openSettingsModal('notifications')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
              <i class="fa-solid fa-bell text-xs"></i>
              <span>Notifications</span>
            </button>
            <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
            <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all duration-200 group w-full" :class="route().current('profile.edit') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'">
              <i class="fa-solid fa-user-pen text-xs"></i>
              <span>Edit Profile</span>
            </Link>
          </div>
        </div>

        <!-- Impersonation Banner -->
        <div v-if="$page.props.auth.is_impersonating" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
          <div class="flex items-center gap-2 text-red-600 dark:text-red-400 text-sm">
            <i class="fa-solid fa-mask"></i>
            <span class="font-medium">Impersonating User</span>
          </div>
          <Link :href="route('admin.users.stop-impersonate')" method="post" as="button"
                class="mt-2 w-full py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition-colors">
            Stop Impersonating
          </Link>
        </div>
      </nav>

      <!-- Sidebar Footer -->
      <div class="flex-shrink-0 p-4 border-t border-slate-200 dark:border-slate-700 space-y-2">
        <button 
          @click="toggleDark" 
          class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all w-full group"
          :class="{ 'justify-center': sidebarCollapsed }"
          :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        >
          <i :class="isDark ? 'fa-solid fa-sun text-amber-500' : 'fa-solid fa-moon text-indigo-500'" class="text-lg transition-transform group-hover:rotate-12"></i>
          <transition name="fade-slide">
            <span v-if="!sidebarCollapsed" class="text-sm">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
          </transition>
        </button>
        
        <Link 
          :href="route('logout')" 
          method="post" 
          as="button"
          class="flex items-center gap-3 p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/30 transition-all w-full group text-red-600"
          :class="{ 'justify-center': sidebarCollapsed }"
          title="Sign Out"
        >
          <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
          <transition name="fade-slide">
            <span v-if="!sidebarCollapsed" class="text-sm">Sign Out</span>
          </transition>
        </Link>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="transition-all duration-300" :class="sidebarCollapsed ? 'ml-20' : 'ml-72'">
      
      <!-- Top Navbar -->
      <nav class="sticky top-0 z-40 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-700">
        <div class="px-6 py-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <button @click="toggleSidebar" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 lg:hidden" aria-label="Toggle Sidebar">
                <i class="fa-solid fa-bars text-slate-500"></i>
              </button>
              <div class="hidden lg:flex items-center gap-2 text-sm">
                <slot name="header">
                  <span class="text-slate-900 dark:text-white font-semibold">{{ pageTitle }}</span>
                </slot>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <!-- Search with Command Palette -->
              <div class="hidden md:flex items-center bg-slate-100 dark:bg-slate-700 rounded-xl px-3 py-2 min-w-[280px] cursor-pointer hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                   @click="openSearchPalette">
                <i class="fa-solid fa-search text-slate-400 text-sm"></i>
                <span class="bg-transparent text-sm ml-2 text-slate-400">Search reports, tasks, users...</span>
                <kbd class="ml-auto text-xs text-slate-400 bg-slate-200 dark:bg-slate-600 px-1.5 py-0.5 rounded">⌘K</kbd>
              </div>

              <!-- Notifications -->
              <div class="relative">
                <button @click="toggleNotifications" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 relative" :aria-label="'Notifications' + (unreadCount > 0 ? ` (${unreadCount} unread)` : '')">
                  <i class="fa-solid fa-bell text-slate-500 text-lg"></i>
                  <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center px-1 animate-pulse">
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                  </span>
                </button>
                <div v-show="showNotifications" class="absolute right-0 mt-2 w-96 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50 animate-scale-in">
                  <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                      <h3 class="font-semibold text-slate-900 dark:text-white">Notifications</h3>
                      <p class="text-xs text-slate-500 mt-0.5">{{ unreadCount }} unread</p>
                    </div>
                    <div class="flex items-center gap-2">
                      <button v-if="unreadCount > 0" @click="markAllRead" class="text-xs text-indigo-500 hover:text-indigo-600 font-medium">
                        Mark all read
                      </button>
                      <button @click="fetchNotifications" class="text-xs text-slate-500 hover:text-slate-700 p-1 rounded" title="Refresh">
                        <i class="fa-solid fa-rotate" :class="{ 'animate-spin': loadingNotifications }"></i>
                      </button>
                    </div>
                  </div>
                  <div class="max-h-96 overflow-y-auto">
                    <div v-if="loadingNotifications" class="p-8 text-center text-slate-400">
                      <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 block"></i>
                      <p class="text-sm">Loading notifications...</p>
                    </div>
                    
                    <div v-else-if="notificationError" class="p-8 text-center">
                      <i class="fa-solid fa-triangle-exclamation text-2xl mb-2 block text-red-400"></i>
                      <p class="text-sm text-red-500">{{ notificationError }}</p>
                      <button @click="fetchNotifications" class="mt-2 text-xs text-indigo-500 hover:text-indigo-600">Retry</button>
                    </div>
                    
                    <div v-else-if="notifications.length === 0" class="p-8 text-center text-slate-400">
                      <i class="fa-solid fa-bell-slash text-2xl mb-2 block"></i>
                      <p class="text-sm">No notifications yet</p>
                      <p class="text-xs mt-1">We'll notify you when something happens</p>
                    </div>
                    
                    <div v-for="notification in notifications" :key="notification.id" 
                         class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer border-b border-slate-100 dark:border-slate-700 transition-colors"
                         :class="{ 'bg-indigo-50/30 dark:bg-indigo-900/10': !notification.read_at }"
                         @click="handleNotificationClick(notification)">
                      <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                             :class="notification.read_at ? 'bg-slate-100 dark:bg-slate-700' : 'bg-indigo-100 dark:bg-indigo-900/30'">
                          <i :class="notification.icon || 'fa-solid fa-bell'" class="text-sm" :style="{ color: notification.color || accentColor }"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ notification.title }}</p>
                            <button @click.stop="deleteNotification(notification.id)" class="text-slate-400 hover:text-red-500 p-1 rounded opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" title="Delete">
                              <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                          </div>
                          <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ notification.message }}</p>
                          <div class="flex items-center justify-between mt-2">
                            <span class="text-[11px] text-slate-400">{{ notification.time_ago || formatTimeAgo(notification.created_at) }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full capitalize"
                                  :class="getNotificationTypeClass(notification.type)">
                              {{ formatNotificationType(notification.type) }}
                            </span>
                          </div>
                        </div>
                        <div v-if="!notification.read_at" class="w-2 h-2 rounded-full mt-2 flex-shrink-0" :style="{ background: notification.color || accentColor }"></div>
                      </div>
                    </div>
                  </div>
                  <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <Link :href="route('notifications.index')" class="text-xs text-center block w-full text-indigo-500 hover:text-indigo-600 font-medium">
                      View all notifications
                    </Link>
                  </div>
                </div>
              </div>

              <!-- Quick Actions -->
              <div class="relative hidden md:block">
                <button @click="showQuickActions = !showQuickActions" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" title="Quick Actions">
                  <i class="fa-solid fa-bolt text-slate-500 text-lg"></i>
                </button>
                <div v-show="showQuickActions" class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50 animate-scale-in py-2">
                  <Link :href="route('reports.create')" class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-plus text-indigo-500"></i>
                    <span>New Report</span>
                  </Link>
                  <Link :href="route('reports.index')" class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-file-lines text-blue-500"></i>
                    <span>View Reports</span>
                  </Link>
                  <Link :href="route('admin.tasks.my')" class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-tasks text-amber-500"></i>
                    <span>My Tasks</span>
                  </Link>
                </div>
              </div>

              <!-- Settings Button -->
              <button @click="openSettingsModal('appearance')" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" title="Open Settings">
                <i class="fa-solid fa-sliders text-slate-500 text-lg"></i>
              </button>

              <button @click="toggleDark" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 lg:hidden" title="Toggle Theme">
                <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="text-slate-500 text-lg"></i>
              </button>
              
              <Link :href="route('reports.create')" class="hidden md:flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl hover:scale-105 active:scale-95"
                    :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${cardRadius}px` }">
                <i class="fa-solid fa-plus"></i>
                <span>New Report</span>
              </Link>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Content -->
      <main class="p-6" :class="{ 'compact-mode': compactMode }">
        <div class="lg:hidden mb-4">
          <slot name="header" />
        </div>
        <slot />
      </main>

      <!-- Footer -->
      <footer class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 text-center text-xs text-slate-400">
        <p>&copy; {{ new Date().getFullYear() }} ReportGen Enterprise. All rights reserved.</p>
      </footer>
    </div>

    <!-- Global Settings Modal -->
    <Teleport to="body">
      <div v-if="settingsModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="settingsModal.show = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden animate-scale-in"
             :style="{ borderRadius: `${cardRadius * 2}px` }"
             role="dialog"
             aria-modal="true"
             aria-labelledby="settings-title">
          <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                   :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${cardRadius}px` }">
                <i class="fa-solid fa-gear text-white"></i>
              </div>
              <div>
                <h2 id="settings-title" class="text-lg font-bold text-slate-900 dark:text-white">Settings</h2>
                <p class="text-xs text-slate-500">Customize your experience</p>
              </div>
            </div>
            <button @click="settingsModal.show = false" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Close settings">
              <i class="fa-solid fa-xmark text-xl text-slate-500"></i>
            </button>
          </div>

          <div class="flex">
            <!-- Settings Tabs -->
            <div class="w-48 border-r border-slate-200 dark:border-slate-700 p-3 space-y-1 overflow-y-auto">
              <button 
                v-for="tab in settingsTabs" 
                :key="tab.id"
                @click="activeSettingsTab = tab.id"
                class="flex items-center gap-3 w-full p-3 rounded-xl text-left transition-all"
                :class="activeSettingsTab === tab.id ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
              >
                <i :class="tab.icon" class="text-lg"></i>
                <span class="text-sm">{{ tab.label }}</span>
              </button>
            </div>

            <!-- Settings Content -->
            <div class="flex-1 p-6 overflow-y-auto max-h-[70vh]">
              <!-- Appearance Settings -->
              <div v-show="activeSettingsTab === 'appearance'" class="space-y-6">
                <div>
                  <label class="block text-sm font-semibold mb-2">Theme</label>
                  <div class="grid grid-cols-3 gap-3">
                    <button v-for="theme in themes" :key="theme.value" @click="setTheme(theme.value)"
                      class="p-3 rounded-xl border-2 transition-all capitalize"
                      :class="selectedTheme === theme.value ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-slate-200 dark:border-slate-700'"
                      :style="{ borderRadius: `${cardRadius}px` }">
                      <i :class="theme.icon" class="text-lg mb-1 block"></i>
                      <span class="text-xs">{{ theme.label }}</span>
                    </button>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2">Accent Color</label>
                  <div class="flex gap-3 flex-wrap">
                    <button v-for="color in accentColors" :key="color.value" @click="setAccentColor(color.value)"
                      class="w-10 h-10 rounded-full transition-all hover:scale-110 relative"
                      :style="{ backgroundColor: color.value }"
                      :class="selectedAccent === color.value ? 'ring-2 ring-offset-2 ring-indigo-500' : ''" 
                      :title="color.name"
                      :aria-label="`Set accent color to ${color.name}`">
                      <i v-if="selectedAccent === color.value" class="fa-solid fa-check text-white text-xs absolute inset-0 flex items-center justify-center"></i>
                    </button>
                  </div>
                  <div class="mt-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700/50" :style="{ borderRadius: `${cardRadius}px` }">
                    <p class="text-sm font-medium mb-2">Preview:</p>
                    <div class="flex gap-2 items-center">
                      <button class="px-3 py-1 text-white text-sm rounded-lg transition-all hover:opacity-90" :style="{ background: selectedAccent, borderRadius: `${cardRadius}px` }">Primary Button</button>
                      <span class="text-sm font-medium" :style="{ color: selectedAccent }">Colored Text</span>
                      <div class="w-8 h-8 rounded-full border-2" :style="{ borderColor: selectedAccent }"></div>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2">Font Family</label>
                  <select v-model="fontFamily" @change="applyFontFamily" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" :style="{ borderRadius: `${cardRadius}px` }">
                    <option value="'Inter', sans-serif">Inter (Default)</option>
                    <option value="'DM Sans', sans-serif">DM Sans</option>
                    <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                    <option value="'Poppins', sans-serif">Poppins</option>
                    <option value="Georgia, serif">Georgia</option>
                  </select>
                  <p class="text-xs text-slate-400 mt-1">Changes apply immediately</p>
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2">Base Font Size: <span class="font-bold" :style="{ color: accentColor }">{{ fontSize }}px</span></label>
                  <input type="range" v-model="fontSize" min="12" max="18" step="1" class="w-full accent-indigo-500">
                  <div class="flex justify-between text-xs text-slate-500 mt-1">
                    <span>12px</span>
                    <span class="font-bold" :style="{ fontSize: fontSize + 'px' }">Sample text</span>
                    <span>18px</span>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2">Border Radius: <span class="font-bold" :style="{ color: accentColor }">{{ borderRadius }}px</span></label>
                  <input type="range" v-model="borderRadius" min="4" max="24" step="2" class="w-full accent-indigo-500">
                  <div class="flex justify-between text-xs text-slate-500 mt-1">
                    <span>Sharp (4px)</span>
                    <span>Current: {{ borderRadius }}px</span>
                    <span>Round (24px)</span>
                  </div>
                </div>

                <button @click="saveAppearanceSettings" class="w-full py-2.5 text-white rounded-xl font-semibold transition-all hover:opacity-90 hover:shadow-lg active:scale-[0.98]"
                        :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${cardRadius}px` }">
                  <i class="fa-solid fa-check mr-2"></i> Save Appearance
                </button>
              </div>

              <!-- Preferences Settings -->
              <div v-show="activeSettingsTab === 'preferences'" class="space-y-5">
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl transition-all hover:bg-slate-100 dark:hover:bg-slate-700/70" :style="{ borderRadius: `${cardRadius}px` }">
                  <div>
                    <p class="font-medium">Compact Mode</p>
                    <p class="text-xs text-slate-500">Reduce padding and spacing</p>
                  </div>
                  <button @click="compactMode = !compactMode" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="compactMode ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm" :class="compactMode ? 'translate-x-6' : 'translate-x-1'"></span>
                  </button>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl transition-all hover:bg-slate-100 dark:hover:bg-slate-700/70" :style="{ borderRadius: `${cardRadius}px` }">
                  <div>
                    <p class="font-medium">Animations</p>
                    <p class="text-xs text-slate-500">Enable smooth transitions</p>
                  </div>
                  <button @click="enableAnimations = !enableAnimations" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="enableAnimations ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm" :class="enableAnimations ? 'translate-x-6' : 'translate-x-1'"></span>
                  </button>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl transition-all hover:bg-slate-100 dark:hover:bg-slate-700/70" :style="{ borderRadius: `${cardRadius}px` }">
                  <div>
                    <p class="font-medium">Auto-save Reports</p>
                    <p class="text-xs text-slate-500">Automatically save while editing</p>
                  </div>
                  <button @click="autoSave = !autoSave" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="autoSave ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm" :class="autoSave ? 'translate-x-6' : 'translate-x-1'"></span>
                  </button>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl transition-all hover:bg-slate-100 dark:hover:bg-slate-700/70" :style="{ borderRadius: `${cardRadius}px` }">
                  <div>
                    <p class="font-medium">Keyboard Shortcuts</p>
                    <p class="text-xs text-slate-500">View available keyboard shortcuts</p>
                  </div>
                  <button @click="openShortcuts" class="px-3 py-1.5 text-sm rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                    View All
                  </button>
                </div>

                <button @click="savePreferences" class="w-full py-2.5 text-white rounded-xl font-semibold transition-all hover:opacity-90 hover:shadow-lg active:scale-[0.98]"
                        :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${cardRadius}px` }">
                  <i class="fa-solid fa-check mr-2"></i> Save Preferences
                </button>
              </div>

              <!-- Notifications Settings -->
              <div v-show="activeSettingsTab === 'notifications'" class="space-y-4">
                <div v-for="notif in notificationTypes" :key="notif.key" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl transition-all hover:bg-slate-100 dark:hover:bg-slate-700/70" :style="{ borderRadius: `${cardRadius}px` }">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5" :style="{ background: accentColor + '15' }">
                      <i :class="getNotificationIcon(notif.key)" class="text-sm" :style="{ color: accentColor }"></i>
                    </div>
                    <div>
                      <p class="font-medium">{{ notif.label }}</p>
                      <p class="text-xs text-slate-500">{{ notif.desc }}</p>
                    </div>
                  </div>
                  <button @click="notifSettings[notif.key] = !notifSettings[notif.key]" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 flex-shrink-0" :class="notifSettings[notif.key] ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm" :class="notifSettings[notif.key] ? 'translate-x-6' : 'translate-x-1'"></span>
                  </button>
                </div>

                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800" :style="{ borderRadius: `${cardRadius}px` }">
                  <div class="flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                    <div>
                      <p class="text-sm font-medium text-amber-900 dark:text-amber-100">Notification Settings</p>
                      <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">These notification preferences are saved locally. Configure additional email preferences in your profile settings.</p>
                    </div>
                  </div>
                </div>

                <button @click="saveNotificationSettings" class="w-full py-2.5 text-white rounded-xl font-semibold transition-all hover:opacity-90 hover:shadow-lg active:scale-[0.98]"
                        :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${cardRadius}px` }">
                  <i class="fa-solid fa-check mr-2"></i> Save Notification Settings
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Search Palette -->
    <Teleport to="body">
      <div v-if="showSearchPalette" class="fixed inset-0 z-[60] flex items-start justify-center pt-[15vh]">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showSearchPalette = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-scale-in"
             :style="{ borderRadius: `${cardRadius * 2}px` }">
          <div class="flex items-center gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
            <i class="fa-solid fa-search text-slate-400"></i>
            <input 
              ref="searchInput"
              v-model="searchQuery"
              type="text" 
              placeholder="Search reports, tasks, users..."
              class="bg-transparent border-none focus:outline-none flex-1 text-sm text-slate-900 dark:text-white"
              @keydown.escape="showSearchPalette = false"
              @keydown.down.prevent="navigateSearchResults('down')"
              @keydown.up.prevent="navigateSearchResults('up')"
              @keydown.enter="selectSearchResult"
            />
            <kbd class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">ESC</kbd>
          </div>
          <div class="max-h-80 overflow-y-auto p-2">
            <p v-if="!searchQuery" class="text-center text-sm text-slate-400 py-8">
              <i class="fa-solid fa-magnifying-glass text-2xl mb-2 block"></i>
              Type to search...
            </p>
            <p v-else-if="searchResults.length === 0" class="text-center text-sm text-slate-400 py-8">
              <i class="fa-solid fa-circle-exclamation text-2xl mb-2 block"></i>
              No results found for "{{ searchQuery }}"
            </p>
            <div v-for="(result, index) in searchResults" :key="result.id"
                 class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors"
                 :class="searchIndex === index ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-700'"
                 @click="navigateToResult(result)"
                 @mouseenter="searchIndex = index">
              <i :class="result.icon" class="text-lg" :style="{ color: result.color }"></i>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate text-slate-900 dark:text-white">{{ result.title }}</p>
                <p class="text-xs text-slate-400 truncate">{{ result.subtitle }}</p>
              </div>
              <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-500">{{ result.type }}</span>
            </div>
          </div>
          <div v-if="searchResults.length > 0" class="p-2 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between text-xs text-slate-400 px-2">
              <span>Navigate with ↑↓ arrows</span>
              <span>Enter to select</span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const sidebarCollapsed = ref(false)
const showNotifications = ref(false)
const showQuickActions = ref(false)
const showSearchPalette = ref(false)
const showShortcuts = ref(false)
const isDark = ref(false)
const settingsModal = ref({ show: false })
const activeSettingsTab = ref('appearance')
const selectedTheme = ref('system')
const selectedAccent = ref('#6366f1')
const fontFamily = ref("'Inter', sans-serif")
const fontSize = ref(14)
const borderRadius = ref(12)
const compactMode = ref(false)
const enableAnimations = ref(true)
const autoSave = ref(true)
const searchQuery = ref('')
const searchIndex = ref(0)
const searchInput = ref(null)
const sidebarRef = ref(null)

// Dynamic notifications
const notifications = ref([])
const loadingNotifications = ref(false)
const notificationError = ref(null)
const pollingInterval = ref(null)

const isReportsOpen = ref(false)
const isAdminOpen = ref(false)
const isSettingsOpen = ref(false)

// Computed
const isReportsActive = computed(() => page.url.includes('/reports') || page.url.includes('/templates'))
const isAdminActive = computed(() => page.url.includes('/admin'))
const accentColor = computed(() => selectedAccent.value)
const cardRadius = computed(() => borderRadius.value + 'px')
const userInitial = computed(() => page.props.auth.user?.name?.charAt(0)?.toUpperCase() || 'U')
const isOnline = computed(() => typeof navigator !== 'undefined' ? navigator.onLine : true)

const unreadCount = computed(() => {
  // First check from page props, then from local state
  if (page.props.notifications?.unread_count !== undefined) {
    return page.props.notifications.unread_count
  }
  return notifications.value.filter(n => !n.read_at).length
})

const hasUnreadNotifications = computed(() => unreadCount.value > 0)

const pageTitle = computed(() => {
  const url = page.url
  if (url.includes('/admin/users')) return 'User Management'
  if (url.includes('/admin/roles')) return 'Roles & Permissions'
  if (url.includes('/admin/tasks')) return 'Task Management'
  if (url.includes('/admin/analytics')) return 'Analytics'
  if (url.includes('/admin/report-assignments')) return 'Report Assignments'
  if (url.includes('/admin/activities')) return 'Activity Logs'
  if (url.includes('/reports/create')) return 'Create Report'
  if (url.includes('/reports')) return 'Reports'
  if (url.includes('/templates')) return 'Templates'
  if (url.includes('/dashboard')) return 'Dashboard'
  if (url.includes('/profile')) return 'Profile Settings'
  if (url.includes('/my-tasks')) return 'My Tasks'
  if (url.includes('/notifications')) return 'Notifications'
  return 'Dashboard'
})

const themes = [
  { value: 'light', label: 'Light', icon: 'fa-solid fa-sun' },
  { value: 'dark', label: 'Dark', icon: 'fa-solid fa-moon' },
  { value: 'system', label: 'System', icon: 'fa-solid fa-laptop' }
]

const searchResults = computed(() => {
  if (!searchQuery.value) return []
  const query = searchQuery.value.toLowerCase()
  
  const allResults = [
    { id: '1', title: 'Q4 Sales Report', subtitle: 'Created by John Doe', type: 'Report', icon: 'fa-solid fa-file-lines', color: '#6366f1', link: '/reports/q4-sales' },
    { id: '2', title: 'User Management', subtitle: 'Admin Panel', type: 'Page', icon: 'fa-solid fa-users', color: '#8b5cf6', link: '/admin/users' },
    { id: '3', title: 'Quarterly Review Task', subtitle: 'Due in 3 days', type: 'Task', icon: 'fa-solid fa-tasks', color: '#f59e0b', link: '/admin/tasks/1' },
    { id: '4', title: 'John Smith', subtitle: 'john@example.com', type: 'User', icon: 'fa-solid fa-user', color: '#10b981', link: '/admin/users/1' },
    { id: '5', title: 'Analytics Dashboard', subtitle: 'View statistics and metrics', type: 'Page', icon: 'fa-solid fa-chart-pie', color: '#0ea5e9', link: '/admin/analytics' },
    { id: '6', title: 'Report Templates', subtitle: 'Browse available templates', type: 'Page', icon: 'fa-solid fa-layer-group', color: '#ec4899', link: '/templates' },
  ]
  
  return allResults.filter(r => 
    r.title.toLowerCase().includes(query) || 
    r.subtitle.toLowerCase().includes(query) ||
    r.type.toLowerCase().includes(query)
  )
})

const shortcutsList = computed(() => {
  const isMac = typeof navigator !== 'undefined' && navigator.platform?.toUpperCase().indexOf('MAC') >= 0
  const mod = isMac ? '⌘' : 'Ctrl'
  return [
    { key: `${mod} + K`, description: 'Open search palette' },
    { key: `${mod} + B`, description: 'Toggle sidebar' },
    { key: `${mod} + D`, description: 'Toggle dark mode' },
    { key: `${mod} + N`, description: 'Create new report' },
    { key: `${mod} + /`, description: 'Show keyboard shortcuts' },
    { key: `${mod} + ,`, description: 'Open settings' },
    { key: 'Esc', description: 'Close modals/palettes' },
  ]
})

const notifSettings = ref({ report_updates: true, task_reminders: true, team_mentions: true, weekly_digest: false })

const notificationTypes = [
  { key: 'report_updates', label: 'Report Updates', desc: 'Get notified when reports are updated, shared, or commented on' },
  { key: 'task_reminders', label: 'Task Reminders', desc: 'Reminders for pending tasks and approaching deadlines' },
  { key: 'team_mentions', label: 'Team Mentions', desc: 'When someone mentions you or assigns you to a report' },
  { key: 'weekly_digest', label: 'Weekly Digest', desc: 'Receive a weekly summary of all activities and updates' }
]

const accentColors = [
  { value: '#6366f1', name: 'Indigo' }, 
  { value: '#8b5cf6', name: 'Violet' }, 
  { value: '#ec4899', name: 'Pink' },
  { value: '#10b981', name: 'Emerald' }, 
  { value: '#f59e0b', name: 'Amber' }, 
  { value: '#ef4444', name: 'Red' },
  { value: '#0ea5e9', name: 'Sky' }, 
  { value: '#14b8a6', name: 'Teal' }
]

const settingsTabs = [
  { id: 'appearance', label: 'Appearance', icon: 'fa-solid fa-palette' },
  { id: 'preferences', label: 'Preferences', icon: 'fa-solid fa-sliders' },
  { id: 'notifications', label: 'Notifications', icon: 'fa-solid fa-bell' }
]

// Methods
const getNotificationIcon = (key) => {
  const icons = {
    report_updates: 'fa-solid fa-file-pen',
    task_reminders: 'fa-solid fa-clock',
    team_mentions: 'fa-solid fa-at',
    weekly_digest: 'fa-solid fa-envelope-open-text'
  }
  return icons[key] || 'fa-solid fa-bell'
}

const formatNotificationType = (type) => {
  const types = {
    'task_created': 'Task',
    'task_completed': 'Completed',
    'task_updated': 'Updated',
    'task_deleted': 'Deleted',
    'task_restored': 'Restored',
    'report_assigned': 'Assigned',
    'report_shared': 'Shared',
    'report_created': 'New',
    'report_updated': 'Updated',
    'report_deleted': 'Deleted',
    'report_restored': 'Restored',
    'user_mentioned': 'Mention',
    'system': 'System'
  }
  return types[type] || type?.replace(/_/g, ' ') || 'Info'
}

const getNotificationTypeClass = (type) => {
  const classes = {
    'task_created': 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    'task_completed': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    'task_deleted': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    'task_restored': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    'report_assigned': 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    'report_shared': 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
    'report_deleted': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    'report_restored': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    'user_mentioned': 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300',
  }
  return classes[type] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'
}

// Dynamic notification methods
const initNotifications = () => {
  // Load from page props first
  if (page.props.notifications?.items && page.props.notifications.items.length > 0) {
    notifications.value = page.props.notifications.items
  }
}

const fetchNotifications = async () => {
  loadingNotifications.value = true
  notificationError.value = null
  
  try {
    const response = await axios.get(route('notifications.latest'))
    notifications.value = response.data.notifications
    
    // Update page props unread count
    if (page.props.notifications) {
      page.props.notifications.unread_count = response.data.unread_count
    }
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
    // Only show error if we don't have any notifications loaded
    if (notifications.value.length === 0) {
      notificationError.value = 'Failed to load notifications'
    }
  } finally {
    loadingNotifications.value = false
  }
}

const markAsRead = async (notificationId) => {
  try {
    await axios.put(route('notifications.mark-read', notificationId))
    
    // Update local state
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.read_at = new Date().toISOString()
    }
    
    // Update page props
    if (page.props.notifications) {
      page.props.notifications.unread_count = Math.max(0, (page.props.notifications.unread_count || 1) - 1)
    }
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

const markAllRead = async () => {
  try {
    await axios.put(route('notifications.mark-all-read'))
    
    // Update local state
    notifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString()
    })
    
    // Update page props
    if (page.props.notifications) {
      page.props.notifications.unread_count = 0
      page.props.notifications.assigned_reports = 0
      page.props.notifications.pending_tasks = 0
    }
    
    showToast('All notifications marked as read')
  } catch (error) {
    console.error('Failed to mark all as read:', error)
    showToast('Failed to mark all as read', 'error')
  }
}

const deleteNotification = async (notificationId) => {
  try {
    await axios.delete(route('notifications.destroy', notificationId))
    
    // Remove from local state
    notifications.value = notifications.value.filter(n => n.id !== notificationId)
    
    showToast('Notification deleted', 'success')
  } catch (error) {
    console.error('Failed to delete notification:', error)
    showToast('Failed to delete notification', 'error')
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read if unread
  if (!notification.read_at) {
    await markAsRead(notification.id)
  }
  
  // Navigate to action URL
  if (notification.action_url) {
    router.visit(notification.action_url)
  }
  
  // Close dropdown
  showNotifications.value = false
}

const startNotificationPolling = () => {
  stopNotificationPolling()
  pollingInterval.value = setInterval(() => {
    fetchNotifications()
  }, 30000) // Poll every 30 seconds
}

const stopNotificationPolling = () => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
}

// Original methods (keep all)
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value)
}

const toggleDark = () => { 
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const toggleDropdown = (dropdown) => {
  if (sidebarCollapsed.value) return
  
  if (dropdown === 'reports') { 
    isReportsOpen.value = !isReportsOpen.value
    isAdminOpen.value = false
    isSettingsOpen.value = false
  } else if (dropdown === 'admin') { 
    isAdminOpen.value = !isAdminOpen.value
    isReportsOpen.value = false
    isSettingsOpen.value = false
  } else if (dropdown === 'settings') { 
    isSettingsOpen.value = !isSettingsOpen.value
    isReportsOpen.value = false
    isAdminOpen.value = false
  }
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showQuickActions.value = false
  
  // Fetch fresh notifications when opening
  if (showNotifications.value) {
    fetchNotifications()
  }
}

const openSearchPalette = () => {
  showSearchPalette.value = true
  searchQuery.value = ''
  searchIndex.value = 0
  nextTick(() => {
    searchInput.value?.focus()
  })
}

const navigateSearchResults = (direction) => {
  if (searchResults.value.length === 0) return
  if (direction === 'down') {
    searchIndex.value = Math.min(searchIndex.value + 1, searchResults.value.length - 1)
  } else {
    searchIndex.value = Math.max(searchIndex.value - 1, 0)
  }
}

const selectSearchResult = () => {
  if (searchResults.value[searchIndex.value]) {
    navigateToResult(searchResults.value[searchIndex.value])
  }
}

const navigateToResult = (result) => {
  showSearchPalette.value = false
  if (result.link) router.visit(result.link)
}

const formatTimeAgo = (date) => {
  if (!date) return ''
  const seconds = Math.floor((new Date() - new Date(date)) / 1000)
  if (seconds < 60) return 'Just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`
  return new Date(date).toLocaleDateString()
}

const setTheme = (theme) => {
  selectedTheme.value = theme
  if (theme === 'dark') { 
    isDark.value = true
    document.documentElement.classList.add('dark')
  } else if (theme === 'light') { 
    isDark.value = false
    document.documentElement.classList.remove('dark')
  } else { 
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    isDark.value = prefersDark
    document.documentElement.classList.toggle('dark', prefersDark)
  }
  localStorage.setItem('theme-preference', theme)
}

const setAccentColor = (color) => { 
  selectedAccent.value = color
  document.documentElement.style.setProperty('--accent-color', color)
  localStorage.setItem('accent-color', color)
}

const applyFontFamily = () => {
  document.documentElement.style.setProperty('--font-family', fontFamily.value)
  document.body.style.fontFamily = fontFamily.value
  localStorage.setItem('font-family', fontFamily.value)
}

const applyGlobalStyles = () => {
  document.body.style.fontSize = fontSize.value + 'px'
  document.documentElement.style.setProperty('--base-font-size', fontSize.value + 'px')
  document.documentElement.style.setProperty('--card-radius', borderRadius.value + 'px')
  if (compactMode.value) document.body.classList.add('compact-mode')
  else document.body.classList.remove('compact-mode')
  if (!enableAnimations.value) document.body.classList.add('reduce-motion')
  else document.body.classList.remove('reduce-motion')
  localStorage.setItem('font-size', fontSize.value)
  localStorage.setItem('border-radius', borderRadius.value)
  localStorage.setItem('compact-mode', compactMode.value)
  localStorage.setItem('animations', enableAnimations.value)
  localStorage.setItem('auto-save', autoSave.value)
}

const saveAppearanceSettings = () => { 
  applyGlobalStyles()
  settingsModal.value.show = false
  showToast('Appearance settings saved successfully!', 'success')
}

const savePreferences = () => { 
  applyGlobalStyles()
  settingsModal.value.show = false
  showToast('Preferences saved successfully!', 'success')
}

const saveNotificationSettings = () => { 
  localStorage.setItem('notif-settings', JSON.stringify(notifSettings.value))
  settingsModal.value.show = false
  showToast('Notification settings saved successfully!', 'success')
}

const showToast = (message, type = 'success') => {
  const toast = document.createElement('div')
  toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-xl text-white text-sm font-medium z-[100] shadow-lg ${
    type === 'success' ? 'bg-emerald-500' : type === 'error' ? 'bg-red-500' : 'bg-indigo-500'
  }`
  toast.style.cssText = `
    animation: slide-in 0.3s ease-out;
  `
  toast.innerHTML = `
    <div class="flex items-center gap-2">
      <i class="fa-solid fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
      <span>${message}</span>
    </div>
  `
  document.body.appendChild(toast)
  
  setTimeout(() => {
    toast.style.opacity = '0'
    toast.style.transform = 'translateX(100px)'
    toast.style.transition = 'all 0.3s ease'
    setTimeout(() => toast.remove(), 300)
  }, 3000)
}

const loadSettings = () => {
  selectedTheme.value = localStorage.getItem('theme-preference') || 'system'
  selectedAccent.value = localStorage.getItem('accent-color') || '#6366f1'
  fontFamily.value = localStorage.getItem('font-family') || "'Inter', sans-serif"
  fontSize.value = parseInt(localStorage.getItem('font-size') || 14)
  borderRadius.value = parseInt(localStorage.getItem('border-radius') || 12)
  compactMode.value = localStorage.getItem('compact-mode') === 'true'
  enableAnimations.value = localStorage.getItem('animations') !== 'false'
  autoSave.value = localStorage.getItem('auto-save') !== 'false'
  sidebarCollapsed.value = localStorage.getItem('sidebar-collapsed') === 'true'
  
  const savedNotifs = localStorage.getItem('notif-settings')
  if (savedNotifs) {
    try {
      notifSettings.value = { ...notifSettings.value, ...JSON.parse(savedNotifs) }
    } catch (e) {
      console.error('Failed to parse notification settings:', e)
    }
  }
  
  setTheme(selectedTheme.value)
  setAccentColor(selectedAccent.value)
  applyFontFamily()
  applyGlobalStyles()
}

const openSettingsModal = (tab = 'appearance') => { 
  activeSettingsTab.value = tab
  loadSettings()
  settingsModal.value.show = true
  showQuickActions.value = false
  showNotifications.value = false
}

const openShortcuts = () => {
  showShortcuts.value = !showShortcuts.value
}

// Keyboard shortcuts
const handleKeyboardShortcuts = (e) => {
  const isMac = typeof navigator !== 'undefined' && navigator.platform?.toUpperCase().indexOf('MAC') >= 0
  const modifier = isMac ? e.metaKey : e.ctrlKey
  
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
    if (e.key === 'Escape') {
      showSearchPalette.value = false
      showNotifications.value = false
      showQuickActions.value = false
      showShortcuts.value = false
      e.target.blur()
    }
    return
  }
  
  if (modifier && e.key === 'k') {
    e.preventDefault()
    openSearchPalette()
  } else if (modifier && e.key === 'b') {
    e.preventDefault()
    toggleSidebar()
  } else if (modifier && e.key === 'd') {
    e.preventDefault()
    toggleDark()
  } else if (modifier && e.key === 'n') {
    e.preventDefault()
    router.visit(route('reports.create'))
  } else if (modifier && e.key === '/') {
    e.preventDefault()
    openShortcuts()
  } else if (modifier && e.key === ',') {
    e.preventDefault()
    openSettingsModal()
  } else if (e.key === 'Escape') {
    showSearchPalette.value = false
    showNotifications.value = false
    showQuickActions.value = false
    showShortcuts.value = false
  }
}

const handleClickOutside = (e) => {
  if (showNotifications.value && !e.target.closest('.relative')) {
    showNotifications.value = false
  }
  if (showQuickActions.value && !e.target.closest('.relative')) {
    showQuickActions.value = false
  }
}

// Watchers
watch(fontSize, (val) => { 
  document.body.style.fontSize = val + 'px'
  document.documentElement.style.setProperty('--base-font-size', val + 'px')
})

watch(borderRadius, (val) => { 
  document.documentElement.style.setProperty('--card-radius', val + 'px')
})

watch(compactMode, (val) => { 
  if (val) document.body.classList.add('compact-mode')
  else document.body.classList.remove('compact-mode')
})

watch(enableAnimations, (val) => { 
  if (val) document.body.classList.remove('reduce-motion')
  else document.body.classList.add('reduce-motion')
})

// Watch for page prop changes (when navigation happens)
watch(() => page.props.notifications?.items, (newItems) => {
  if (newItems && newItems.length > 0) {
    notifications.value = newItems
  }
}, { deep: true })

// Lifecycle
onMounted(() => {
  const saved = localStorage.getItem('theme')
  isDark.value = saved === 'dark'
  document.documentElement.classList.toggle('dark', isDark.value)
  loadSettings()
  
  // Initialize notifications
  initNotifications()
  startNotificationPolling()
  
  document.addEventListener('keydown', handleKeyboardShortcuts)
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyboardShortcuts)
  document.removeEventListener('click', handleClickOutside)
  stopNotificationPolling()
})
</script>

<style scoped>
@keyframes scale-in {
  from { opacity: 0; transform: scale(0.95) translateY(-10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
.animate-scale-in { animation: scale-in 0.2s ease-out forwards; }

@keyframes slide-in {
  from { opacity: 0; transform: translateX(100px); }
  to { opacity: 1; transform: translateX(0); }
}

.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.2s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateX(-10px); }

.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
.dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #475569; }

.compact-mode .p-6 { padding: 1rem !important; }
.compact-mode .gap-6 { gap: 1rem !important; }
.compact-mode .mb-8 { margin-bottom: 1rem !important; }
.compact-mode .text-lg { font-size: 1rem !important; }
.compact-mode .p-4 { padding: 0.75rem !important; }

.reduce-motion *, .reduce-motion *::before, .reduce-motion *::after { 
  animation-duration: 0.01ms !important; 
  transition-duration: 0.01ms !important; 
}
</style>