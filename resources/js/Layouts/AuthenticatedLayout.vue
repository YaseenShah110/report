<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<template>
  <div
    class="min-h-screen transition-all duration-300"
    :class="isDark ? 'dark' : ''"
    :style="{
      fontFamily: currentFont,
      fontSize: currentFontSize + 'px',
      background: isDark
        ? 'linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%)'
        : 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)',
    }"
  >
    <!-- ═══════════════════════════════════════════════════ -->
    <!-- KEYBOARD SHORTCUTS OVERLAY -->
    <!-- ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="overlay">
        <div v-if="showShortcuts" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-md" @click="showShortcuts = false" />
          <div
            class="relative w-full max-w-md overflow-hidden shadow-2xl animate-scale-in"
            :style="{ borderRadius: cardRad + 'px' }"
            :class="isDark ? 'bg-slate-900 border border-slate-700' : 'bg-white border border-slate-200'"
          >
            <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-slate-700' : 'border-slate-100'">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" :style="{ background: accentGrad, borderRadius: cardRad * 0.75 + 'px' }">
                  <i class="fa-solid fa-keyboard text-white text-sm"></i>
                </div>
                <h3 class="font-bold text-base" :class="isDark ? 'text-white' : 'text-slate-900'">Keyboard Shortcuts</h3>
              </div>
              <button @click="showShortcuts = false" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors" :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <div class="p-4 space-y-1">
              <div
                v-for="sc in shortcutsList" :key="sc.key"
                class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors"
                :class="isDark ? 'hover:bg-slate-800' : 'hover:bg-slate-50'"
              >
                <span class="text-sm" :class="isDark ? 'text-slate-300' : 'text-slate-600'">{{ sc.description }}</span>
                <kbd class="px-2 py-1 text-xs font-mono rounded-md" :class="isDark ? 'bg-slate-700 text-slate-300' : 'bg-slate-100 text-slate-700'">{{ sc.key }}</kbd>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- SIDEBAR -->
    <!-- ═══════════════════════════════════════════════════ -->
    <aside
      ref="sidebarRef"
      class="fixed left-0 top-0 z-50 h-screen flex flex-col transition-all duration-300 overflow-hidden"
      :class="[
        sidebarCollapsed ? 'w-[72px]' : 'w-72',
        mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        isDark
          ? 'bg-slate-900/95 backdrop-blur-xl border-r border-slate-700/50'
          : 'bg-white/95 backdrop-blur-xl border-r border-slate-200/80 shadow-2xl shadow-slate-200/40',
      ]"
      :style="{ borderRadius: sidebarCollapsed ? '0' : `0 ${cardRad}px ${cardRad}px 0` }"
    >
      <!-- Sidebar: Logo Header -->
      <div class="flex-shrink-0 flex items-center px-4 py-4 border-b" :class="[sidebarCollapsed ? 'justify-center' : 'justify-between', isDark ? 'border-slate-700/50' : 'border-slate-100']">
        <div class="flex items-center gap-3" :class="{ 'w-full justify-center': sidebarCollapsed }">
          <div
            class="relative w-10 h-10 flex items-center justify-center shadow-lg flex-shrink-0"
            :style="{ background: accentGrad, borderRadius: cardRad * 0.8 + 'px' }"
          >
            <i class="fa-solid fa-chart-line text-white text-base"></i>
            <span
              class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2"
              :class="isDark ? 'border-slate-900' : 'border-white'"
              :style="hasUnread ? 'animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite' : ''"
            ></span>
            <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2" :class="isDark ? 'border-slate-900' : 'border-white'"></span>
          </div>
          <Transition name="fade-slide">
            <div v-if="!sidebarCollapsed">
              <p class="font-black text-lg leading-none" :class="isDark ? 'text-white' : 'text-slate-900'">ReportGen</p>
              <p class="text-[10px] font-semibold tracking-wide mt-0.5" :style="{ color: accentColor }">Enterprise Edition</p>
            </div>
          </Transition>
        </div>
        <button
          v-if="!sidebarCollapsed"
          @click="toggleSidebar"
          class="p-2 rounded-lg transition-all hover:scale-110 flex-shrink-0"
          :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'"
        >
          <i class="fa-solid fa-chevron-left text-xs"></i>
        </button>
      </div>

      <!-- Sidebar: User Profile -->
      <div class="flex-shrink-0 px-4 py-3 border-b" :class="isDark ? 'border-slate-700/50' : 'border-slate-100'">
        <div class="flex items-center gap-3" :class="{ 'justify-center': sidebarCollapsed }">
          <div class="relative flex-shrink-0">
            <div
              class="w-11 h-11 flex items-center justify-center text-white font-bold text-lg shadow-md"
              :style="{ background: accentGrad, borderRadius: cardRad * 0.8 + 'px' }"
              :title="authUser?.name"
            >{{ userInitial }}</div>
            <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2" :class="isDark ? 'border-slate-900' : 'border-white'"></div>
          </div>
          <Transition name="fade-slide">
            <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
              <p class="font-semibold text-sm truncate" :class="isDark ? 'text-white' : 'text-slate-900'">{{ authUser?.name }}</p>
              <p class="text-[11px] truncate" :class="isDark ? 'text-slate-500' : 'text-slate-400'">{{ authUser?.email }}</p>
              <div class="flex items-center gap-1 mt-1 flex-wrap">
                <span class="text-[9px] px-1.5 py-0.5 rounded font-semibold" :class="isDark ? 'bg-indigo-900/50 text-indigo-300' : 'bg-indigo-100 text-indigo-600'">Pro</span>
                <span v-if="authUser?.is_premium" class="text-[9px] px-1.5 py-0.5 rounded font-semibold" :class="isDark ? 'bg-amber-900/40 text-amber-300' : 'bg-amber-100 text-amber-700'">
                  <i class="fa-solid fa-crown mr-0.5 text-[8px]"></i>Premium
                </span>
                <span v-if="isImpersonating" class="text-[9px] px-1.5 py-0.5 rounded font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                  <i class="fa-solid fa-mask mr-0.5 text-[8px]"></i>Impersonating
                </span>
              </div>
            </div>
          </Transition>
        </div>
      </div>

      <!-- Sidebar: Navigation — Scrollable -->
      <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5 scrollbar-thin">

        <!-- SECTION: Main -->
        <Transition name="fade-slide">
          <p v-if="!sidebarCollapsed" class="px-3 pt-2 pb-1 text-[9px] font-black uppercase tracking-[0.15em]" :class="isDark ? 'text-slate-600' : 'text-slate-400'">Main</p>
        </Transition>

        <!-- Dashboard -->
        <NavItem
          :href="route('dashboard')"
          icon="fa-solid fa-gauge-high"
          label="Dashboard"
          :active="currentRoute('dashboard')"
          :collapsed="sidebarCollapsed"
          :accent="accentColor"
          :is-dark="isDark"
          :card-rad="cardRad"
        />

        <!-- SECTION: Reports -->
        <Transition name="fade-slide">
          <p v-if="!sidebarCollapsed" class="px-3 pt-3 pb-1 text-[9px] font-black uppercase tracking-[0.15em]" :class="isDark ? 'text-slate-600' : 'text-slate-400'">Reports</p>
        </Transition>
        <div v-if="sidebarCollapsed" class="my-1 border-t" :class="isDark ? 'border-slate-700/40' : 'border-slate-100'"></div>

        <!-- Reports Dropdown -->
        <NavDropdown
          icon="fa-solid fa-file-lines"
          label="Reports"
          :open="dropdowns.reports"
          :active="isReportsSection"
          :collapsed="sidebarCollapsed"
          :accent="accentColor"
          :is-dark="isDark"
          :card-rad="cardRad"
          @toggle="toggleDropdown('reports')"
        >
          <NavSubItem :href="route('reports.index')" icon="fa-solid fa-list-ul" label="All Reports" :active="currentRoute('reports.index')" :is-dark="isDark" :accent="accentColor" />
          <NavSubItem :href="route('reports.create')" icon="fa-solid fa-plus" label="Create Report" :active="currentRoute('reports.create')" :is-dark="isDark" :accent="accentColor" shortcut="Ctrl+N" />
          <NavSubItem :href="route('reports.assigned')" icon="fa-solid fa-share-alt" label="Shared with Me" :active="currentRoute('reports.assigned')" :is-dark="isDark" :accent="accentColor">
            <span v-if="pageNotifications?.assigned_reports > 0" class="ml-auto min-w-[18px] h-[18px] bg-red-500 text-white text-[9px] rounded-full flex items-center justify-center px-1 animate-pulse">
              {{ pageNotifications.assigned_reports }}
            </span>
          </NavSubItem>
          <NavSubItem :href="route('reports.trashed')" icon="fa-solid fa-trash-can" label="Trash" :active="currentRoute('reports.trashed')" :is-dark="isDark" :accent="accentColor" />
        </NavDropdown>

        <!-- Templates -->
        <NavItem
          :href="route('templates.index')"
          icon="fa-solid fa-layer-group"
          label="Templates"
          :active="currentRoute('templates.index')"
          :collapsed="sidebarCollapsed"
          :accent="accentColor"
          :is-dark="isDark"
          :card-rad="cardRad"
        />

        <!-- SECTION: Tasks -->
        <Transition name="fade-slide">
          <p v-if="!sidebarCollapsed" class="px-3 pt-3 pb-1 text-[9px] font-black uppercase tracking-[0.15em]" :class="isDark ? 'text-slate-600' : 'text-slate-400'">Tasks</p>
        </Transition>
        <div v-if="sidebarCollapsed" class="my-1 border-t" :class="isDark ? 'border-slate-700/40' : 'border-slate-100'"></div>

        <NavItem
          :href="route('admin.tasks.my')"
          icon="fa-solid fa-list-check"
          label="My Tasks"
          :active="currentRoute('admin.tasks.my')"
          :collapsed="sidebarCollapsed"
          :accent="accentColor"
          :is-dark="isDark"
          :card-rad="cardRad"
          :badge="pageNotifications?.pending_tasks > 0 ? pageNotifications.pending_tasks : null"
        />

        <!-- SECTION: Admin (role-gated: admin|manager) -->
        <template v-if="isAdminOrManager">
          <Transition name="fade-slide">
            <p v-if="!sidebarCollapsed" class="px-3 pt-3 pb-1 text-[9px] font-black uppercase tracking-[0.15em]" :class="isDark ? 'text-slate-600' : 'text-slate-400'">Administration</p>
          </Transition>
          <div v-if="sidebarCollapsed" class="my-1 border-t" :class="isDark ? 'border-slate-700/40' : 'border-slate-100'"></div>

          <!-- Users & Tasks (admin|manager) -->
          <NavDropdown
            icon="fa-solid fa-users-gear"
            label="People & Work"
            :open="dropdowns.people"
            :active="isPeopleSection"
            :collapsed="sidebarCollapsed"
            :accent="accentColor"
            :is-dark="isDark"
            :card-rad="cardRad"
            @toggle="toggleDropdown('people')"
          >
            <NavSubItem :href="route('admin.users.index')" icon="fa-solid fa-users" label="User Management" :active="currentRoute('admin.users.index')" :is-dark="isDark" :accent="accentColor" />
            <NavSubItem :href="route('admin.tasks.index')" icon="fa-solid fa-tasks" label="All Tasks" :active="currentRoute('admin.tasks.index')" :is-dark="isDark" :accent="accentColor" />
            <NavSubItem :href="route('admin.report-assignments.index')" icon="fa-solid fa-share-nodes" label="Report Assignments" :active="currentRoute('admin.report-assignments.index')" :is-dark="isDark" :accent="accentColor" />
          </NavDropdown>

          <!-- Analytics & Activity (admin|manager) -->
          <NavDropdown
            icon="fa-solid fa-chart-pie"
            label="Insights"
            :open="dropdowns.insights"
            :active="isInsightsSection"
            :collapsed="sidebarCollapsed"
            :accent="accentColor"
            :is-dark="isDark"
            :card-rad="cardRad"
            @toggle="toggleDropdown('insights')"
          >
            <NavSubItem :href="route('admin.analytics.index')" icon="fa-solid fa-chart-bar" label="Analytics" :active="currentRoute('admin.analytics.index')" :is-dark="isDark" :accent="accentColor" />
            <NavSubItem :href="route('admin.analytics.reports')" icon="fa-solid fa-file-chart-column" label="Report Analytics" :active="currentRoute('admin.analytics.reports')" :is-dark="isDark" :accent="accentColor" />
            <NavSubItem :href="route('admin.analytics.users')" icon="fa-solid fa-user-chart" label="User Analytics" :active="currentRoute('admin.analytics.users')" :is-dark="isDark" :accent="accentColor" />
            <NavSubItem :href="route('admin.activities.index')" icon="fa-solid fa-clock-rotate-left" label="Activity Logs" :active="currentRoute('admin.activities.index')" :is-dark="isDark" :accent="accentColor" />
          </NavDropdown>

          <!-- Roles (admin only) -->
          <template v-if="isAdmin">
            <NavDropdown
              icon="fa-solid fa-shield-halved"
              label="Roles & Access"
              :open="dropdowns.roles"
              :active="isRolesSection"
              :collapsed="sidebarCollapsed"
              :accent="accentColor"
              :is-dark="isDark"
              :card-rad="cardRad"
              @toggle="toggleDropdown('roles')"
            >
              <NavSubItem :href="route('admin.roles.index')" icon="fa-solid fa-shield" label="Roles" :active="currentRoute('admin.roles.index')" :is-dark="isDark" :accent="accentColor" />
              <NavSubItem :href="route('admin.roles.permissions')" icon="fa-solid fa-key" label="Permissions" :active="currentRoute('admin.roles.permissions')" :is-dark="isDark" :accent="accentColor" />
            </NavDropdown>
          </template>
        </template>

        <!-- SECTION: Account -->
        <Transition name="fade-slide">
          <p v-if="!sidebarCollapsed" class="px-3 pt-3 pb-1 text-[9px] font-black uppercase tracking-[0.15em]" :class="isDark ? 'text-slate-600' : 'text-slate-400'">Account</p>
        </Transition>
        <div v-if="sidebarCollapsed" class="my-1 border-t" :class="isDark ? 'border-slate-700/40' : 'border-slate-100'"></div>

        <NavItem
          :href="route('notifications.index')"
          icon="fa-solid fa-bell"
          label="Notifications"
          :active="currentRoute('notifications.index')"
          :collapsed="sidebarCollapsed"
          :accent="accentColor"
          :is-dark="isDark"
          :card-rad="cardRad"
          :badge="unreadCount > 0 ? unreadCount : null"
        />

        <NavDropdown
          icon="fa-solid fa-gear"
          label="Settings"
          :open="dropdowns.settings"
          :active="isSettingsSection"
          :collapsed="sidebarCollapsed"
          :accent="accentColor"
          :is-dark="isDark"
          :card-rad="cardRad"
          @toggle="toggleDropdown('settings')"
        >
          <button @click="openSettings('appearance')" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-xs transition-all" :class="isDark ? 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
            <i class="fa-solid fa-palette text-[10px] w-3"></i><span>Appearance</span>
          </button>
          <button @click="openSettings('preferences')" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-xs transition-all" :class="isDark ? 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
            <i class="fa-solid fa-sliders text-[10px] w-3"></i><span>Preferences</span>
          </button>
          <button @click="openSettings('notifications')" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-xs transition-all" :class="isDark ? 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
            <i class="fa-solid fa-bell text-[10px] w-3"></i><span>Notifications</span>
          </button>
          <div class="border-t my-1" :class="isDark ? 'border-slate-700' : 'border-slate-100'"></div>
          <NavSubItem :href="route('profile.edit')" icon="fa-solid fa-user-pen" label="Edit Profile" :active="currentRoute('profile.edit')" :is-dark="isDark" :accent="accentColor" />
        </NavDropdown>

        <!-- Impersonation Banner -->
        <div v-if="isImpersonating" class="mx-1 mt-2 p-3 rounded-xl border animate-pulse-slow" :class="isDark ? 'bg-red-900/20 border-red-800' : 'bg-red-50 border-red-200'">
          <div class="flex items-center gap-2 mb-2">
            <i class="fa-solid fa-mask text-red-500 text-xs"></i>
            <span class="text-xs font-semibold text-red-600">Impersonating User</span>
          </div>
          <Link
            :href="route('admin.users.stop-impersonate')"
            method="post"
            as="button"
            class="w-full py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded-lg transition-colors font-semibold"
          >Stop Impersonating</Link>
        </div>
      </nav>

      <!-- Sidebar: Footer -->
      <div class="flex-shrink-0 px-2 py-3 border-t space-y-0.5" :class="isDark ? 'border-slate-700/50' : 'border-slate-100'">
        <!-- Expand button when collapsed -->
        <button v-if="sidebarCollapsed" @click="toggleSidebar" class="w-full flex items-center justify-center p-3 rounded-xl transition-all" :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'">
          <i class="fa-solid fa-chevron-right text-sm"></i>
        </button>

        <button
          @click="toggleDark"
          class="w-full flex items-center gap-3 p-3 rounded-xl transition-all"
          :class="[sidebarCollapsed ? 'justify-center' : '', isDark ? 'hover:bg-slate-800' : 'hover:bg-slate-100']"
          :title="isDark ? 'Light Mode' : 'Dark Mode'"
        >
          <i :class="isDark ? 'fa-solid fa-sun text-amber-400' : 'fa-solid fa-moon text-indigo-500'" class="text-base transition-transform hover:rotate-12"></i>
          <Transition name="fade-slide">
            <span v-if="!sidebarCollapsed" class="text-xs font-medium" :class="isDark ? 'text-slate-300' : 'text-slate-600'">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
          </Transition>
        </button>

        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="w-full flex items-center gap-3 p-3 rounded-xl transition-all text-red-500 hover:bg-red-500/10"
          :class="sidebarCollapsed ? 'justify-center' : ''"
          title="Sign Out"
        >
          <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
          <Transition name="fade-slide">
            <span v-if="!sidebarCollapsed" class="text-xs font-semibold">Sign Out</span>
          </Transition>
        </Link>
      </div>
    </aside>

    <!-- Mobile sidebar overlay -->
    <Transition name="overlay">
      <div
        v-if="mobileMenuOpen"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
        @click="mobileMenuOpen = false"
      ></div>
    </Transition>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- MAIN CONTENT AREA -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div
      class="transition-all duration-300 min-h-screen flex flex-col"
      :class="sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-72'"
    >
      <!-- ─── TOP NAVBAR ─── -->
      <nav
        class="sticky top-0 z-40 border-b px-4 sm:px-6"
        :class="isDark
          ? 'bg-slate-900/80 backdrop-blur-xl border-slate-700/50'
          : 'bg-white/80 backdrop-blur-xl border-slate-200/80 shadow-sm'"
      >
        <div class="flex items-center justify-between h-14">
          <!-- Left: Mobile menu + breadcrumb -->
          <div class="flex items-center gap-3">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="lg:hidden p-2 rounded-xl transition-colors"
              :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'"
              aria-label="Toggle Menu"
            >
              <i class="fa-solid fa-bars text-base"></i>
            </button>
            <div class="hidden lg:flex items-center gap-2">
              <slot name="header">
                <div class="flex items-center gap-2">
                  <div class="w-1.5 h-5 rounded-full" :style="{ background: accentGrad }"></div>
                  <span class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-slate-900'">{{ pageTitle }}</span>
                </div>
              </slot>
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-1.5 sm:gap-2">

            <!-- Search Trigger -->
            <button
              @click="openSearch"
              class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-all"
              :class="isDark ? 'bg-slate-800 hover:bg-slate-700 text-slate-400' : 'bg-slate-100 hover:bg-slate-200 text-slate-400'"
            >
              <i class="fa-solid fa-magnifying-glass text-xs"></i>
              <span class="text-xs min-w-[140px] text-left">Search…</span>
              <kbd class="text-[10px] px-1.5 py-0.5 rounded font-mono" :class="isDark ? 'bg-slate-700 text-slate-500' : 'bg-slate-200 text-slate-400'">⌘K</kbd>
            </button>

            <!-- Notifications -->
            <div class="relative">
              <button
                @click="toggleNotifications"
                class="relative p-2 rounded-xl transition-colors"
                :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'"
                :aria-label="`Notifications${unreadCount > 0 ? ` (${unreadCount} unread)` : ''}`"
              >
                <i class="fa-solid fa-bell text-base"></i>
                <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 rounded-full text-white text-[9px] flex items-center justify-center px-1 font-bold animate-pulse">
                  {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
              </button>

              <!-- Notification Dropdown -->
              <Transition name="dropdown">
                <div
                  v-if="showNotifications"
                  class="absolute right-0 mt-2 w-[340px] sm:w-96 rounded-2xl shadow-2xl border overflow-hidden z-50"
                  :class="isDark ? 'bg-slate-900 border-slate-700' : 'bg-white border-slate-200'"
                >
                  <div class="flex items-center justify-between px-4 py-3 border-b" :class="isDark ? 'border-slate-700' : 'border-slate-100'">
                    <div>
                      <h3 class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-slate-900'">Notifications</h3>
                      <p class="text-[11px]" :class="isDark ? 'text-slate-500' : 'text-slate-400'">{{ unreadCount }} unread</p>
                    </div>
                    <div class="flex items-center gap-2">
                      <button v-if="unreadCount > 0" @click="markAllRead" class="text-xs font-semibold" :style="{ color: accentColor }">Mark all read</button>
                      <button @click="fetchNotifications" class="p-1.5 rounded-lg transition-colors" :class="isDark ? 'hover:bg-slate-800 text-slate-500' : 'hover:bg-slate-100 text-slate-400'" title="Refresh">
                        <i class="fa-solid fa-rotate text-xs" :class="{ 'animate-spin': loadingNotifications }"></i>
                      </button>
                    </div>
                  </div>

                  <div class="max-h-80 overflow-y-auto scrollbar-thin">
                    <div v-if="loadingNotifications" class="py-10 flex flex-col items-center gap-2">
                      <i class="fa-solid fa-spinner fa-spin text-2xl" :style="{ color: accentColor }"></i>
                      <p class="text-xs" :class="isDark ? 'text-slate-500' : 'text-slate-400'">Loading…</p>
                    </div>
                    <div v-else-if="notifError && !notifList.length" class="py-10 text-center">
                      <i class="fa-solid fa-triangle-exclamation text-2xl text-red-400 mb-2 block"></i>
                      <p class="text-xs text-red-400">Failed to load</p>
                      <button @click="fetchNotifications" class="mt-2 text-xs font-semibold" :style="{ color: accentColor }">Retry</button>
                    </div>
                    <div v-else-if="!notifList.length" class="py-10 text-center">
                      <i class="fa-solid fa-bell-slash text-2xl mb-2 block" :class="isDark ? 'text-slate-700' : 'text-slate-300'"></i>
                      <p class="text-xs" :class="isDark ? 'text-slate-500' : 'text-slate-400'">No notifications yet</p>
                    </div>
                    <div
                      v-for="n in notifList" :key="n.id"
                      class="flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors border-b last:border-b-0"
                      :class="[
                        isDark ? 'border-slate-800 hover:bg-slate-800/60' : 'border-slate-50 hover:bg-slate-50',
                        !n.read_at ? (isDark ? 'bg-indigo-900/10' : 'bg-indigo-50/40') : ''
                      ]"
                      @click="handleNotifClick(n)"
                    >
                      <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                           :class="n.read_at ? (isDark ? 'bg-slate-800' : 'bg-slate-100') : (isDark ? 'bg-indigo-900/40' : 'bg-indigo-100')">
                        <i :class="n.icon || 'fa-solid fa-bell'" class="text-xs" :style="{ color: n.color || accentColor }"></i>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold leading-snug" :class="isDark ? 'text-white' : 'text-slate-900'">{{ n.title }}</p>
                        <p class="text-[11px] mt-0.5 line-clamp-2" :class="isDark ? 'text-slate-500' : 'text-slate-400'">{{ n.message }}</p>
                        <div class="flex items-center gap-2 mt-1">
                          <span class="text-[10px]" :class="isDark ? 'text-slate-600' : 'text-slate-400'">{{ n.time_ago || formatTimeAgo(n.created_at) }}</span>
                          <span class="text-[9px] px-1.5 py-0.5 rounded-full font-semibold" :class="notifTypeClass(n.type)">{{ notifTypeLabel(n.type) }}</span>
                        </div>
                      </div>
                      <div v-if="!n.read_at" class="w-2 h-2 rounded-full flex-shrink-0 mt-1" :style="{ background: accentColor }"></div>
                    </div>
                  </div>

                  <div class="px-4 py-2.5 border-t" :class="isDark ? 'border-slate-700 bg-slate-900/50' : 'border-slate-100 bg-slate-50/50'">
                    <Link :href="route('notifications.index')" class="block text-center text-xs font-semibold" :style="{ color: accentColor }">View all notifications</Link>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Quick Actions -->
            <div class="relative hidden sm:block">
              <button
                @click="showQuickActions = !showQuickActions"
                class="p-2 rounded-xl transition-colors"
                :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'"
                title="Quick Actions"
              >
                <i class="fa-solid fa-bolt text-base"></i>
              </button>
              <Transition name="dropdown">
                <div v-if="showQuickActions" class="absolute right-0 mt-2 w-52 rounded-2xl shadow-2xl border py-1.5 z-50 overflow-hidden"
                     :class="isDark ? 'bg-slate-900 border-slate-700' : 'bg-white border-slate-200'">
                  <Link :href="route('reports.create')" class="flex items-center gap-3 px-4 py-2.5 text-xs transition-colors" :class="isDark ? 'hover:bg-slate-800 text-slate-300' : 'hover:bg-slate-50 text-slate-700'">
                    <i class="fa-solid fa-plus w-4" :style="{ color: accentColor }"></i> New Report
                  </Link>
                  <Link :href="route('reports.index')" class="flex items-center gap-3 px-4 py-2.5 text-xs transition-colors" :class="isDark ? 'hover:bg-slate-800 text-slate-300' : 'hover:bg-slate-50 text-slate-700'">
                    <i class="fa-solid fa-file-lines w-4 text-blue-500"></i> All Reports
                  </Link>
                  <Link :href="route('admin.tasks.my')" class="flex items-center gap-3 px-4 py-2.5 text-xs transition-colors" :class="isDark ? 'hover:bg-slate-800 text-slate-300' : 'hover:bg-slate-50 text-slate-700'">
                    <i class="fa-solid fa-list-check w-4 text-amber-500"></i> My Tasks
                  </Link>
                  <div class="border-t mx-2 my-1" :class="isDark ? 'border-slate-700' : 'border-slate-100'"></div>
                  <button @click="openSettings(); showQuickActions = false" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs transition-colors text-left" :class="isDark ? 'hover:bg-slate-800 text-slate-300' : 'hover:bg-slate-50 text-slate-700'">
                    <i class="fa-solid fa-gear w-4 text-slate-400"></i> Settings
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Settings -->
            <button
              @click="openSettings('appearance')"
              class="hidden sm:flex p-2 rounded-xl transition-colors"
              :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'"
              title="Settings"
            >
              <i class="fa-solid fa-sliders text-base"></i>
            </button>

            <!-- CTA: New Report -->
            <Link
              :href="route('reports.create')"
              class="hidden sm:flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-xl transition-all shadow-lg hover:shadow-xl hover:scale-105 active:scale-95"
              :style="{ background: accentGrad, borderRadius: cardRad * 0.8 + 'px' }"
            >
              <i class="fa-solid fa-plus"></i>
              <span class="hidden md:inline">New Report</span>
            </Link>
          </div>
        </div>
      </nav>

      <!-- ─── PAGE CONTENT ─── -->
      <main class="flex-1 p-4 sm:p-6" :class="compactMode ? 'compact-mode' : ''">
        <div class="lg:hidden mb-4">
          <slot name="header" />
        </div>
        <slot />
      </main>

      <!-- ─── FOOTER ─── -->
      <footer class="px-6 py-3 border-t text-center" :class="isDark ? 'border-slate-800 text-slate-700' : 'border-slate-100 text-slate-400'">
        <p class="text-[11px]">&copy; {{ currentYear }} ReportGen Enterprise. All rights reserved.</p>
      </footer>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- SEARCH PALETTE -->
    <!-- ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="overlay">
        <div v-if="showSearch" class="fixed inset-0 z-[60] flex items-start justify-center pt-[12vh] px-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showSearch = false" />
          <div
            class="relative w-full max-w-lg shadow-2xl overflow-hidden animate-scale-in"
            :style="{ borderRadius: cardRad + 'px' }"
            :class="isDark ? 'bg-slate-900 border border-slate-700' : 'bg-white border border-slate-200'"
          >
            <div class="flex items-center gap-3 px-4 py-3 border-b" :class="isDark ? 'border-slate-700' : 'border-slate-100'">
              <i class="fa-solid fa-magnifying-glass text-sm" :class="isDark ? 'text-slate-500' : 'text-slate-400'"></i>
              <input
                ref="searchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Search reports, tasks, users…"
                class="flex-1 bg-transparent text-sm border-none outline-none"
                :class="isDark ? 'text-white placeholder:text-slate-600' : 'text-slate-900 placeholder:text-slate-400'"
                @keydown.escape="showSearch = false"
                @keydown.down.prevent="searchIdx = Math.min(searchIdx + 1, filteredSearch.length - 1)"
                @keydown.up.prevent="searchIdx = Math.max(searchIdx - 1, 0)"
                @keydown.enter="goToSearchResult(filteredSearch[searchIdx])"
              />
              <kbd class="text-[10px] px-1.5 py-0.5 rounded font-mono" :class="isDark ? 'bg-slate-700 text-slate-500' : 'bg-slate-100 text-slate-400'">ESC</kbd>
            </div>
            <div class="max-h-72 overflow-y-auto p-2">
              <div v-if="!searchQuery" class="py-10 text-center">
                <i class="fa-solid fa-magnifying-glass text-2xl mb-2 block" :class="isDark ? 'text-slate-700' : 'text-slate-300'"></i>
                <p class="text-xs" :class="isDark ? 'text-slate-500' : 'text-slate-400'">Type to search…</p>
              </div>
              <div v-else-if="!filteredSearch.length" class="py-10 text-center">
                <i class="fa-solid fa-circle-xmark text-2xl mb-2 block" :class="isDark ? 'text-slate-700' : 'text-slate-300'"></i>
                <p class="text-xs" :class="isDark ? 'text-slate-500' : 'text-slate-400'">No results for "{{ searchQuery }}"</p>
              </div>
              <div
                v-for="(r, i) in filteredSearch" :key="r.id"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-colors"
                :class="[
                  searchIdx === i
                    ? (isDark ? 'bg-slate-800' : 'bg-indigo-50')
                    : (isDark ? 'hover:bg-slate-800' : 'hover:bg-slate-50')
                ]"
                @click="goToSearchResult(r)"
                @mouseenter="searchIdx = i"
              >
                <i :class="r.icon" class="text-base w-5 text-center" :style="{ color: r.color }"></i>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold truncate" :class="isDark ? 'text-white' : 'text-slate-900'">{{ r.title }}</p>
                  <p class="text-[11px] truncate" :class="isDark ? 'text-slate-500' : 'text-slate-400'">{{ r.subtitle }}</p>
                </div>
                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium flex-shrink-0" :class="isDark ? 'bg-slate-700 text-slate-400' : 'bg-slate-100 text-slate-500'">{{ r.type }}</span>
              </div>
            </div>
            <div v-if="filteredSearch.length" class="flex justify-between items-center px-4 py-2 border-t text-[10px]" :class="isDark ? 'border-slate-700 text-slate-600' : 'border-slate-100 text-slate-400'">
              <span>↑↓ to navigate</span><span>↵ to select</span>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- SETTINGS MODAL -->
    <!-- ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="overlay">
        <div v-if="settingsOpen" class="fixed inset-0 z-[65] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="settingsOpen = false" />
          <div
            class="relative w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-scale-in"
            :style="{ borderRadius: cardRad + 'px' }"
            :class="isDark ? 'bg-slate-900 border border-slate-700' : 'bg-white border border-slate-200'"
            role="dialog" aria-modal="true"
          >
            <!-- Settings Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0" :class="isDark ? 'border-slate-700' : 'border-slate-100'">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" :style="{ background: accentGrad, borderRadius: cardRad * 0.75 + 'px' }">
                  <i class="fa-solid fa-gear text-white text-sm"></i>
                </div>
                <div>
                  <h2 class="font-black text-base" :class="isDark ? 'text-white' : 'text-slate-900'">Settings</h2>
                  <p class="text-[11px]" :class="isDark ? 'text-slate-500' : 'text-slate-400'">Customize your experience</p>
                </div>
              </div>
              <button @click="settingsOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors" :class="isDark ? 'hover:bg-slate-800 text-slate-400' : 'hover:bg-slate-100 text-slate-500'">
                <i class="fa-solid fa-xmark text-lg"></i>
              </button>
            </div>

            <div class="flex flex-1 min-h-0">
              <!-- Settings Tabs -->
              <div class="w-36 sm:w-44 flex-shrink-0 border-r p-2 space-y-0.5 overflow-y-auto" :class="isDark ? 'border-slate-700' : 'border-slate-100'">
                <button
                  v-for="tab in settingsTabs" :key="tab.id"
                  @click="activeSettings = tab.id"
                  class="w-full flex items-center gap-2.5 p-2.5 rounded-xl text-left transition-all"
                  :class="activeSettings === tab.id
                    ? (isDark ? 'bg-indigo-900/40 text-indigo-300 font-semibold' : 'bg-indigo-50 text-indigo-600 font-semibold')
                    : (isDark ? 'text-slate-400 hover:bg-slate-800' : 'text-slate-500 hover:bg-slate-100')"
                >
                  <i :class="tab.icon" class="text-sm w-4 text-center"></i>
                  <span class="text-xs">{{ tab.label }}</span>
                </button>
              </div>

              <!-- Settings Content -->
              <div class="flex-1 overflow-y-auto p-5 scrollbar-thin">

                <!-- Appearance -->
                <div v-show="activeSettings === 'appearance'" class="space-y-6">
                  <div>
                    <label class="block text-xs font-bold mb-2.5" :class="isDark ? 'text-slate-300' : 'text-slate-700'">Theme</label>
                    <div class="grid grid-cols-3 gap-2">
                      <button v-for="t in themes" :key="t.value" @click="applyTheme(t.value)"
                        class="py-3 px-2 rounded-xl border-2 text-center transition-all"
                        :class="selectedTheme === t.value
                          ? (isDark ? 'border-indigo-500 bg-indigo-900/30' : 'border-indigo-500 bg-indigo-50')
                          : (isDark ? 'border-slate-700 hover:border-slate-600' : 'border-slate-200 hover:border-slate-300')"
                        :style="{ borderRadius: cardRad * 0.75 + 'px' }"
                      >
                        <i :class="t.icon" class="text-lg block mb-1, isDark ? 'text-slate-300' : 'text-slate-600'"></i>
                        <span class="text-[11px] font-medium" :class="isDark ? 'text-slate-400' : 'text-slate-500'">{{ t.label }}</span>
                      </button>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-bold mb-2.5" :class="isDark ? 'text-slate-300' : 'text-slate-700'">Accent Color</label>
                    <div class="flex gap-2.5 flex-wrap mb-3">
                      <button
                        v-for="c in accentColors" :key="c.value"
                        @click="setAccent(c.value)"
                        class="w-9 h-9 rounded-full transition-all hover:scale-110 relative shadow-md"
                        :style="{ backgroundColor: c.value }"
                        :class="accentColor === c.value ? 'ring-2 ring-offset-2 ring-white' : ''"
                        :title="c.name"
                      >
                        <i v-if="accentColor === c.value" class="fa-solid fa-check text-white text-[10px] absolute inset-0 flex items-center justify-center" style="display:flex"></i>
                      </button>
                    </div>
                    <!-- Live Preview -->
                    <div class="p-3 rounded-xl border" :class="isDark ? 'bg-slate-800 border-slate-700' : 'bg-slate-50 border-slate-200'" :style="{ borderRadius: cardRad * 0.75 + 'px' }">
                      <p class="text-[10px] font-semibold uppercase tracking-wide mb-2" :class="isDark ? 'text-slate-500' : 'text-slate-400'">Preview</p>
                      <div class="flex gap-2 items-center flex-wrap">
                        <button class="px-3 py-1.5 text-white text-xs rounded-lg font-semibold" :style="{ background: accentGrad, borderRadius: cardRad * 0.5 + 'px' }">Button</button>
                        <span class="text-xs font-bold" :style="{ color: accentColor }">Accent Text</span>
                        <div class="w-8 h-8 rounded-full border-2" :style="{ borderColor: accentColor }"></div>
                      </div>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-bold mb-2" :class="isDark ? 'text-slate-300' : 'text-slate-700'">Font Family</label>
                    <select v-model="currentFont" @change="persistFont" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 transition" :class="isDark ? 'bg-slate-800 border-slate-600 text-slate-300 focus:ring-indigo-500' : 'bg-white border-slate-200 text-slate-700 focus:ring-indigo-500'" :style="{ borderRadius: cardRad * 0.6 + 'px' }">
                      <option value="'Inter', sans-serif">Inter (Default)</option>
                      <option value="'DM Sans', sans-serif">DM Sans</option>
                      <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                      <option value="'Poppins', sans-serif">Poppins</option>
                      <option value="Georgia, serif">Georgia</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold mb-2" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                      Font Size: <span :style="{ color: accentColor }">{{ currentFontSize }}px</span>
                    </label>
                    <input type="range" v-model.number="currentFontSize" min="12" max="18" step="1" class="w-full accent-indigo-500" @change="persistFontSize" />
                  </div>

                  <div>
                    <label class="block text-xs font-bold mb-2" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                      Border Radius: <span :style="{ color: accentColor }">{{ cardRad }}px</span>
                    </label>
                    <input type="range" v-model.number="cardRad" min="4" max="24" step="2" class="w-full accent-indigo-500" @change="persistRadius" />
                  </div>

                  <button @click="saveAppearance" class="w-full py-2.5 text-white rounded-xl font-bold text-sm transition-all hover:opacity-90 hover:shadow-lg active:scale-[0.98]" :style="{ background: accentGrad, borderRadius: cardRad * 0.75 + 'px' }">
                    <i class="fa-solid fa-check mr-2"></i>Save Appearance
                  </button>
                </div>

                <!-- Preferences -->
                <div v-show="activeSettings === 'preferences'" class="space-y-3">
                  <div
                    v-for="pref in prefOptions" :key="pref.key"
                    class="flex items-center justify-between p-3.5 rounded-xl border transition-colors"
                    :class="isDark ? 'bg-slate-800/60 border-slate-700 hover:bg-slate-800' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                    :style="{ borderRadius: cardRad * 0.75 + 'px' }"
                  >
                    <div>
                      <p class="text-xs font-semibold" :class="isDark ? 'text-slate-200' : 'text-slate-800'">{{ pref.label }}</p>
                      <p class="text-[11px] mt-0.5" :class="isDark ? 'text-slate-500' : 'text-slate-400'">{{ pref.desc }}</p>
                    </div>
                    <button @click="prefs[pref.key] = !prefs[pref.key]" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 flex-shrink-0" :class="prefs[pref.key] ? 'bg-indigo-600' : (isDark ? 'bg-slate-600' : 'bg-slate-300')">
                      <span class="inline-block h-4 w-4 rounded-full bg-white shadow transition-transform duration-200" :class="prefs[pref.key] ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                  </div>

                  <div class="pt-1">
                    <button @click="showShortcuts = true; settingsOpen = false" class="w-full flex items-center justify-between p-3.5 rounded-xl border transition-colors text-left" :class="isDark ? 'bg-slate-800/60 border-slate-700 hover:bg-slate-800' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'" :style="{ borderRadius: cardRad * 0.75 + 'px' }">
                      <div>
                        <p class="text-xs font-semibold" :class="isDark ? 'text-slate-200' : 'text-slate-800'">Keyboard Shortcuts</p>
                        <p class="text-[11px]" :class="isDark ? 'text-slate-500' : 'text-slate-400'">View all shortcuts</p>
                      </div>
                      <span class="text-xs font-semibold px-2 py-1 rounded-lg" :class="isDark ? 'bg-slate-700 text-slate-300' : 'bg-slate-200 text-slate-600'">View</span>
                    </button>
                  </div>

                  <button @click="savePrefs" class="w-full py-2.5 text-white rounded-xl font-bold text-sm transition-all hover:opacity-90 hover:shadow-lg active:scale-[0.98]" :style="{ background: accentGrad, borderRadius: cardRad * 0.75 + 'px' }">
                    <i class="fa-solid fa-check mr-2"></i>Save Preferences
                  </button>
                </div>

                <!-- Notifications Settings -->
                <div v-show="activeSettings === 'notifications'" class="space-y-3">
                  <div
                    v-for="nt in notifTypeOptions" :key="nt.key"
                    class="flex items-center justify-between p-3.5 rounded-xl border transition-colors"
                    :class="isDark ? 'bg-slate-800/60 border-slate-700 hover:bg-slate-800' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                    :style="{ borderRadius: cardRad * 0.75 + 'px' }"
                  >
                    <div class="flex items-start gap-3">
                      <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :style="{ background: accentColor + '18' }">
                        <i :class="nt.icon" class="text-xs" :style="{ color: accentColor }"></i>
                      </div>
                      <div>
                        <p class="text-xs font-semibold" :class="isDark ? 'text-slate-200' : 'text-slate-800'">{{ nt.label }}</p>
                        <p class="text-[11px] mt-0.5" :class="isDark ? 'text-slate-500' : 'text-slate-400'">{{ nt.desc }}</p>
                      </div>
                    </div>
                    <button @click="notifPrefs[nt.key] = !notifPrefs[nt.key]" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 flex-shrink-0 ml-3" :class="notifPrefs[nt.key] ? 'bg-indigo-600' : (isDark ? 'bg-slate-600' : 'bg-slate-300')">
                      <span class="inline-block h-4 w-4 rounded-full bg-white shadow transition-transform duration-200" :class="notifPrefs[nt.key] ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                  </div>

                  <div class="p-4 rounded-xl border" :class="isDark ? 'bg-amber-900/10 border-amber-800' : 'bg-amber-50 border-amber-200'" :style="{ borderRadius: cardRad * 0.75 + 'px' }">
                    <div class="flex items-start gap-2.5">
                      <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm mt-0.5"></i>
                      <p class="text-xs" :class="isDark ? 'text-amber-300' : 'text-amber-700'">Preferences are saved locally. Configure email preferences in your profile.</p>
                    </div>
                  </div>

                  <button @click="saveNotifPrefs" class="w-full py-2.5 text-white rounded-xl font-bold text-sm transition-all hover:opacity-90 hover:shadow-lg active:scale-[0.98]" :style="{ background: accentGrad, borderRadius: cardRad * 0.75 + 'px' }">
                    <i class="fa-solid fa-check mr-2"></i>Save Notification Settings
                  </button>
                </div>

              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import {
  ref, computed, reactive, onMounted, onUnmounted,
  watch, nextTick, defineComponent, h
} from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

// ─── Page / Auth ────────────────────────────────────────────────
const page            = usePage()
const authUser        = computed(() => page.props.auth?.user)
const pageNotifications = computed(() => page.props.notifications)
const isAdmin         = computed(() => authUser.value?.roles?.includes('admin'))
const isManager       = computed(() => authUser.value?.roles?.includes('manager'))
const isAdminOrManager = computed(() => isAdmin.value || isManager.value)
const isImpersonating = computed(() => page.props.auth?.is_impersonating)
const userInitial     = computed(() => authUser.value?.name?.charAt(0)?.toUpperCase() || 'U')
const currentYear     = new Date().getFullYear()

// ─── Theme / Appearance (localStorage) ─────────────────────────
const isDark         = ref(false)
const selectedTheme  = ref('system')
const accentColor    = ref('#6366f1')
const currentFont    = ref("'Inter', sans-serif")
const currentFontSize = ref(14)
const cardRad        = ref(12)
const accentGrad     = computed(() => `linear-gradient(135deg, ${accentColor.value}, ${accentColor.value}cc)`)

// ─── Preferences ────────────────────────────────────────────────
const prefs = reactive({ compact: false, animations: true, autosave: true })
const notifPrefs = reactive({ report_updates: true, task_reminders: true, team_mentions: true, weekly_digest: false })
const compactMode = computed(() => prefs.compact)

// ─── UI State ───────────────────────────────────────────────────
const sidebarCollapsed  = ref(false)
const mobileMenuOpen    = ref(false)
const showNotifications = ref(false)
const showQuickActions  = ref(false)
const showSearch        = ref(false)
const showShortcuts     = ref(false)
const settingsOpen      = ref(false)
const activeSettings    = ref('appearance')
const sidebarRef        = ref(null)
const searchInputRef    = ref(null)
const searchQuery       = ref('')
const searchIdx         = ref(0)

// Dropdown state — single reactive object for O(1) toggle
const dropdowns = reactive({
  reports: false, people: false, insights: false, roles: false, settings: false
})

// ─── Notifications ───────────────────────────────────────────────
const notifList          = ref([])
const loadingNotifications = ref(false)
const notifError         = ref(null)
let   pollingTimer       = null

const unreadCount = computed(() => {
  if (pageNotifications.value?.unread_count !== undefined) return pageNotifications.value.unread_count
  return notifList.value.filter(n => !n.read_at).length
})
const hasUnread = computed(() => unreadCount.value > 0)

// ─── Route helpers ───────────────────────────────────────────────
const currentRoute    = (name) => page.component?.toLowerCase().includes(name.replace('.', '/').replace('_', '/').toLowerCase()) || (typeof route !== 'undefined' && route().current?.(name))
const isReportsSection = computed(() => page.url.includes('/reports') || page.url.includes('/templates'))
const isPeopleSection  = computed(() => page.url.includes('/admin/users') || page.url.includes('/admin/tasks') || page.url.includes('/admin/report-assignments'))
const isInsightsSection = computed(() => page.url.includes('/admin/analytics') || page.url.includes('/admin/activities'))
const isRolesSection   = computed(() => page.url.includes('/admin/roles'))
const isSettingsSection = computed(() => page.url.includes('/profile'))

const pageTitle = computed(() => {
  const u = page.url
  if (u.includes('/admin/users'))               return 'User Management'
  if (u.includes('/admin/roles'))               return 'Roles & Permissions'
  if (u.includes('/admin/tasks'))               return 'Task Management'
  if (u.includes('/admin/analytics/reports'))   return 'Report Analytics'
  if (u.includes('/admin/analytics/users'))     return 'User Analytics'
  if (u.includes('/admin/analytics'))           return 'Analytics'
  if (u.includes('/admin/report-assignments'))  return 'Report Assignments'
  if (u.includes('/admin/activities'))          return 'Activity Logs'
  if (u.includes('/reports/create'))            return 'Create Report'
  if (u.includes('/reports/assigned'))          return 'Shared with Me'
  if (u.includes('/reports/trashed'))           return 'Trash'
  if (u.includes('/reports'))                   return 'Reports'
  if (u.includes('/templates'))                 return 'Templates'
  if (u.includes('/dashboard'))                 return 'Dashboard'
  if (u.includes('/profile'))                   return 'Profile Settings'
  if (u.includes('/my-tasks'))                  return 'My Tasks'
  if (u.includes('/notifications'))             return 'Notifications'
  return 'Dashboard'
})

// ─── Static config ───────────────────────────────────────────────
const themes = [
  { value: 'light',  label: 'Light',  icon: 'fa-solid fa-sun'    },
  { value: 'dark',   label: 'Dark',   icon: 'fa-solid fa-moon'   },
  { value: 'system', label: 'System', icon: 'fa-solid fa-laptop' },
]

const accentColors = [
  { value: '#6366f1', name: 'Indigo'  },
  { value: '#8b5cf6', name: 'Violet'  },
  { value: '#ec4899', name: 'Pink'    },
  { value: '#10b981', name: 'Emerald' },
  { value: '#f59e0b', name: 'Amber'   },
  { value: '#ef4444', name: 'Red'     },
  { value: '#0ea5e9', name: 'Sky'     },
  { value: '#14b8a6', name: 'Teal'    },
]

const settingsTabs = [
  { id: 'appearance',    label: 'Appearance',    icon: 'fa-solid fa-palette'  },
  { id: 'preferences',   label: 'Preferences',   icon: 'fa-solid fa-sliders'  },
  { id: 'notifications', label: 'Notifications', icon: 'fa-solid fa-bell'     },
]

const prefOptions = [
  { key: 'compact',    label: 'Compact Mode',    desc: 'Reduce padding and spacing'       },
  { key: 'animations', label: 'Animations',       desc: 'Enable smooth transitions'        },
  { key: 'autosave',   label: 'Auto-save Reports', desc: 'Automatically save while editing' },
]

const notifTypeOptions = [
  { key: 'report_updates', label: 'Report Updates',  icon: 'fa-solid fa-file-pen',           desc: 'When reports are updated or shared'    },
  { key: 'task_reminders', label: 'Task Reminders',  icon: 'fa-solid fa-clock',              desc: 'Reminders for pending tasks'           },
  { key: 'team_mentions',  label: 'Team Mentions',   icon: 'fa-solid fa-at',                 desc: 'When someone mentions or assigns you'  },
  { key: 'weekly_digest',  label: 'Weekly Digest',   icon: 'fa-solid fa-envelope-open-text', desc: 'Weekly summary of all activities'      },
]

const shortcutsList = computed(() => {
  const mac = typeof navigator !== 'undefined' && /mac/i.test(navigator.platform)
  const M = mac ? '⌘' : 'Ctrl'
  return [
    { key: `${M}+K`, description: 'Open search palette'    },
    { key: `${M}+B`, description: 'Toggle sidebar'         },
    { key: `${M}+D`, description: 'Toggle dark mode'       },
    { key: `${M}+N`, description: 'Create new report'      },
    { key: `${M}+/`, description: 'Show keyboard shortcuts' },
    { key: `${M}+,`, description: 'Open settings'          },
    { key: 'Esc',    description: 'Close modals'           },
  ]
})

// ─── Search ──────────────────────────────────────────────────────
const searchData = [
  { id: '1', title: 'Q4 Sales Report',     subtitle: 'Reports',           type: 'Report', icon: 'fa-solid fa-file-lines',  color: '#6366f1', link: '/reports' },
  { id: '2', title: 'User Management',     subtitle: 'Admin Panel',       type: 'Page',   icon: 'fa-solid fa-users',       color: '#8b5cf6', link: '/admin/users' },
  { id: '3', title: 'My Tasks',            subtitle: 'Tasks',             type: 'Page',   icon: 'fa-solid fa-list-check',  color: '#f59e0b', link: '/my-tasks' },
  { id: '4', title: 'Analytics Dashboard', subtitle: 'Admin Panel',       type: 'Page',   icon: 'fa-solid fa-chart-pie',   color: '#0ea5e9', link: '/admin/analytics' },
  { id: '5', title: 'Templates',           subtitle: 'Report Templates',  type: 'Page',   icon: 'fa-solid fa-layer-group', color: '#ec4899', link: '/templates' },
  { id: '6', title: 'Activity Logs',       subtitle: 'Admin Panel',       type: 'Page',   icon: 'fa-solid fa-clock-rotate-left', color: '#10b981', link: '/admin/activities' },
]

const filteredSearch = computed(() => {
  if (!searchQuery.value) return []
  const q = searchQuery.value.toLowerCase()
  return searchData.filter(r => r.title.toLowerCase().includes(q) || r.subtitle.toLowerCase().includes(q) || r.type.toLowerCase().includes(q))
})

// ─── Notification helpers ────────────────────────────────────────
const notifTypeLabel = (type) => ({
  task_created: 'Task', task_completed: 'Done', task_updated: 'Updated', task_deleted: 'Deleted',
  report_assigned: 'Assigned', report_shared: 'Shared', report_created: 'New', report_updated: 'Updated',
  user_mentioned: 'Mention', system: 'System',
}[type] ?? (type?.replace(/_/g, ' ') || 'Info'))

const notifTypeClass = (type) => ({
  task_created: 'bg-indigo-100 text-indigo-700',
  task_completed: 'bg-emerald-100 text-emerald-700',
  task_deleted: 'bg-red-100 text-red-700',
  report_assigned: 'bg-violet-100 text-violet-700',
  report_shared: 'bg-cyan-100 text-cyan-700',
  user_mentioned: 'bg-pink-100 text-pink-700',
}[type] ?? 'bg-slate-100 text-slate-600')

const formatTimeAgo = (date) => {
  if (!date) return ''
  const s = Math.floor((Date.now() - new Date(date)) / 1000)
  if (s < 60)    return 'Just now'
  if (s < 3600)  return `${Math.floor(s / 60)}m ago`
  if (s < 86400) return `${Math.floor(s / 3600)}h ago`
  return `${Math.floor(s / 86400)}d ago`
}

// ─── Actions ────────────────────────────────────────────────────
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value)
}

const toggleDark = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const applyTheme = (t) => {
  selectedTheme.value = t
  if (t === 'dark')  { isDark.value = true;  document.documentElement.classList.add('dark') }
  else if (t === 'light') { isDark.value = false; document.documentElement.classList.remove('dark') }
  else { const sys = window.matchMedia('(prefers-color-scheme: dark)').matches; isDark.value = sys; document.documentElement.classList.toggle('dark', sys) }
  localStorage.setItem('theme-preference', t)
}

const setAccent = (c) => {
  accentColor.value = c
  document.documentElement.style.setProperty('--accent', c)
  localStorage.setItem('accent-color', c)
}

const persistFont     = () => localStorage.setItem('font-family', currentFont.value)
const persistFontSize = () => localStorage.setItem('font-size', currentFontSize.value)
const persistRadius   = () => localStorage.setItem('border-radius', cardRad.value)

const saveAppearance = () => {
  persistFont(); persistFontSize(); persistRadius()
  settingsOpen.value = false
  toast('Appearance saved!', 'success')
}

const savePrefs = () => {
  localStorage.setItem('prefs', JSON.stringify({ ...prefs }))
  applyPrefs()
  settingsOpen.value = false
  toast('Preferences saved!', 'success')
}

const applyPrefs = () => {
  document.body.classList.toggle('compact-mode', prefs.compact)
  document.body.classList.toggle('reduce-motion', !prefs.animations)
}

const saveNotifPrefs = () => {
  localStorage.setItem('notif-prefs', JSON.stringify({ ...notifPrefs }))
  settingsOpen.value = false
  toast('Notification settings saved!', 'success')
}

const toggleDropdown = (key) => {
  const cur = dropdowns[key]
  Object.keys(dropdowns).forEach(k => dropdowns[k] = false)
  dropdowns[key] = !cur
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showQuickActions.value = false
  if (showNotifications.value) fetchNotifications()
}

const openSearch = () => {
  showSearch.value = true
  searchQuery.value = ''
  searchIdx.value = 0
  nextTick(() => searchInputRef.value?.focus())
}

const openSettings = (tab = 'appearance') => {
  activeSettings.value = tab
  settingsOpen.value = true
  showQuickActions.value = false
  showNotifications.value = false
}

const goToSearchResult = (r) => {
  if (!r) return
  showSearch.value = false
  router.visit(r.link)
}

// ─── Notifications (Inertia-native fetch, no axios) ──────────────
const fetchNotifications = async () => {
  loadingNotifications.value = true
  notifError.value = null
  try {
    const res = await fetch(route('notifications.latest'), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    if (!res.ok) throw new Error('Network error')
    const data = await res.json()
    notifList.value = data.notifications ?? []
    if (pageNotifications.value) pageNotifications.value.unread_count = data.unread_count
  } catch (e) {
    if (!notifList.value.length) notifError.value = 'Failed to load notifications'
  } finally {
    loadingNotifications.value = false
  }
}

const markAsRead = async (id) => {
  try {
    await fetch(route('notifications.mark-read', id), {
      method: 'PUT',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' }
    })
    const n = notifList.value.find(x => x.id === id)
    if (n) n.read_at = new Date().toISOString()
    if (pageNotifications.value) pageNotifications.value.unread_count = Math.max(0, (pageNotifications.value.unread_count || 1) - 1)
  } catch {}
}

const markAllRead = async () => {
  try {
    await fetch(route('notifications.mark-all-read'), {
      method: 'PUT',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' }
    })
    notifList.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    if (pageNotifications.value) { pageNotifications.value.unread_count = 0; pageNotifications.value.pending_tasks = 0 }
    toast('All notifications marked as read')
  } catch { toast('Failed to mark all as read', 'error') }
}

const handleNotifClick = async (n) => {
  if (!n.read_at) await markAsRead(n.id)
  if (n.action_url) router.visit(n.action_url)
  showNotifications.value = false
}

// ─── Toast ───────────────────────────────────────────────────────
const toast = (msg, type = 'success') => {
  const el = document.createElement('div')
  const colors = { success: 'bg-emerald-500', error: 'bg-red-500', info: 'bg-indigo-500' }
  el.className = `fixed bottom-5 right-5 px-5 py-3 rounded-xl text-white text-sm font-semibold z-[100] shadow-xl flex items-center gap-2 ${colors[type] || colors.info}`
  el.style.cssText = 'animation: slide-in-right 0.3s ease-out'
  el.innerHTML = `<i class="fa-solid fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><span>${msg}</span>`
  document.body.appendChild(el)
  setTimeout(() => { el.style.transition = 'all 0.3s ease'; el.style.opacity = '0'; el.style.transform = 'translateX(40px)'; setTimeout(() => el.remove(), 300) }, 3000)
}

// ─── Keyboard shortcuts ──────────────────────────────────────────
const onKeydown = (e) => {
  const mac = /mac/i.test(navigator.platform)
  const mod = mac ? e.metaKey : e.ctrlKey
  const tag = e.target.tagName
  if (tag === 'INPUT' || tag === 'TEXTAREA' || e.target.isContentEditable) {
    if (e.key === 'Escape') { showSearch.value = false; showNotifications.value = false }
    return
  }
  if (e.key === 'Escape') { showSearch.value = false; showNotifications.value = false; showQuickActions.value = false; showShortcuts.value = false; settingsOpen.value = false; return }
  if (mod && e.key === 'k') { e.preventDefault(); openSearch() }
  else if (mod && e.key === 'b') { e.preventDefault(); toggleSidebar() }
  else if (mod && e.key === 'd') { e.preventDefault(); toggleDark() }
  else if (mod && e.key === 'n') { e.preventDefault(); router.visit(route('reports.create')) }
  else if (mod && e.key === '/') { e.preventDefault(); showShortcuts.value = !showShortcuts.value }
  else if (mod && e.key === ',') { e.preventDefault(); openSettings() }
}

const onClickOutside = (e) => {
  if (!e.target.closest('[data-notif-wrapper]')) showNotifications.value = false
  if (!e.target.closest('[data-qa-wrapper]')) showQuickActions.value = false
}

// ─── Load settings from localStorage ────────────────────────────
const loadSettings = () => {
  const stored = {
    theme:    localStorage.getItem('theme-preference') || 'system',
    accent:   localStorage.getItem('accent-color')     || '#6366f1',
    font:     localStorage.getItem('font-family')      || "'Inter', sans-serif",
    fontSize: parseInt(localStorage.getItem('font-size')    || 14),
    radius:   parseInt(localStorage.getItem('border-radius') || 12),
    collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
  }
  selectedTheme.value   = stored.theme
  accentColor.value     = stored.accent
  currentFont.value     = stored.font
  currentFontSize.value = stored.fontSize
  cardRad.value         = stored.radius
  sidebarCollapsed.value = stored.collapsed

  try { const p = JSON.parse(localStorage.getItem('prefs') || '{}');       Object.assign(prefs, p) } catch {}
  try { const n = JSON.parse(localStorage.getItem('notif-prefs') || '{}'); Object.assign(notifPrefs, n) } catch {}

  applyTheme(stored.theme)
  setAccent(stored.accent)
  applyPrefs()
}

// ─── Init notifications from page props ──────────────────────────
const initNotifications = () => {
  if (pageNotifications.value?.items?.length) notifList.value = pageNotifications.value.items
}

// ─── Lifecycle ───────────────────────────────────────────────────
onMounted(() => {
  loadSettings()
  initNotifications()
  fetchNotifications()
  pollingTimer = setInterval(fetchNotifications, 30000)
  document.addEventListener('keydown', onKeydown)
  document.addEventListener('click', onClickOutside)
})

onUnmounted(() => {
  clearInterval(pollingTimer)
  document.removeEventListener('keydown', onKeydown)
  document.removeEventListener('click', onClickOutside)
})

// Watch page prop changes after Inertia navigation
watch(() => pageNotifications.value?.items, (v) => { if (v?.length) notifList.value = v }, { deep: true })
watch(cardRad,         () => document.documentElement.style.setProperty('--card-radius', cardRad.value + 'px'))
watch(currentFontSize, () => document.body.style.fontSize = currentFontSize.value + 'px')
watch(() => prefs.compact,    applyPrefs)
watch(() => prefs.animations, applyPrefs)

// ─── Sub-components (inlined for single-file) ────────────────────
</script>

<!-- NavItem sub-component -->
<script>
// These are defined as inline components at the bottom
// and registered via app.js or locally

import { defineComponent as dc, h, Transition as Trans, computed as c } from 'vue'
import { Link } from '@inertiajs/vue3'

export const NavItem = dc({
  name: 'NavItem',
  props: { href: String, icon: String, label: String, active: Boolean, collapsed: Boolean, accent: String, isDark: Boolean, cardRad: Number, badge: [Number, String, null] },
  setup(p) {
    const bg = c(() => p.active
      ? (p.isDark ? 'bg-indigo-900/30 text-indigo-300' : 'bg-indigo-50 text-indigo-600')
      : (p.isDark ? 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'))
    return () => h(Link, {
      href: p.href,
      class: `flex items-center gap-3 w-full px-3 py-2.5 rounded-xl transition-all duration-200 relative ${p.collapsed ? 'justify-center' : ''} ${bg.value}`,
      title: p.collapsed ? p.label : '',
    }, {
      default: () => [
        h('i', { class: `${p.icon} text-base flex-shrink-0` }),
        !p.collapsed && h('span', { class: 'text-xs font-medium truncate' }, p.label),
        p.badge && h('span', {
          class: 'ml-auto min-w-[18px] h-[18px] bg-red-500 text-white text-[9px] rounded-full flex items-center justify-center px-1 animate-pulse font-bold',
          style: p.collapsed ? 'position:absolute;top:4px;right:4px' : ''
        }, p.badge > 99 ? '99+' : p.badge),
        p.active && !p.collapsed && h('div', { class: 'absolute right-2 top-1/2 -translate-y-1/2 w-1 h-6 rounded-full', style: { background: p.accent } }),
      ]
    })
  }
})

export const NavDropdown = dc({
  name: 'NavDropdown',
  props: { icon: String, label: String, open: Boolean, active: Boolean, collapsed: Boolean, accent: String, isDark: Boolean, cardRad: Number },
  emits: ['toggle'],
  setup(p, { emit, slots }) {
    const bg = c(() => p.open || p.active
      ? (p.isDark ? 'bg-indigo-900/30 text-indigo-300' : 'bg-indigo-50 text-indigo-600')
      : (p.isDark ? 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'))
    return () => h('div', { class: 'relative' }, [
      h('button', {
        class: `flex items-center gap-3 w-full px-3 py-2.5 rounded-xl transition-all duration-200 ${p.collapsed ? 'justify-center' : ''} ${bg.value}`,
        title: p.collapsed ? p.label : '',
        onClick: () => emit('toggle'),
      }, [
        h('i', { class: `${p.icon} text-base flex-shrink-0` }),
        !p.collapsed && h('span', { class: 'text-xs font-medium flex-1 text-left' }, p.label),
        !p.collapsed && h('i', { class: `fa-solid ${p.open ? 'fa-chevron-up' : 'fa-chevron-down'} text-[10px] transition-transform duration-200` }),
      ]),
      h(Trans, { name: 'sub-menu' }, {
        default: () => p.open && !p.collapsed && h('div', { class: 'mt-0.5 ml-7 space-y-0.5 pb-1' }, slots.default?.())
      })
    ])
  }
})

export const NavSubItem = dc({
  name: 'NavSubItem',
  props: { href: String, icon: String, label: String, active: Boolean, isDark: Boolean, accent: String, shortcut: String },
  setup(p, { slots }) {
    const bg = c(() => p.active
      ? (p.isDark ? 'text-indigo-300 bg-indigo-900/20' : 'text-indigo-600 bg-indigo-50/80')
      : (p.isDark ? 'text-slate-500 hover:bg-slate-800 hover:text-slate-300' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600'))
    return () => h(Link, {
      href: p.href,
      class: `flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs transition-all w-full ${bg.value}`,
    }, {
      default: () => [
        h('i', { class: `${p.icon} text-[10px] w-3 text-center flex-shrink-0` }),
        h('span', { class: 'flex-1 truncate' }, p.label),
        p.shortcut && h('kbd', { class: 'text-[9px] px-1 py-0.5 rounded font-mono opacity-50', style: p.isDark ? 'background:#334155;color:#94a3b8' : 'background:#f1f5f9;color:#94a3b8' }, p.shortcut),
        slots.default?.(),
      ]
    })
  }
})
</script>

<style>
/* ── Global resets & utilities ── */
.scrollbar-thin { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
.dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; }

@keyframes scale-in {
  from { opacity: 0; transform: scale(0.95) translateY(-8px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.animate-scale-in { animation: scale-in 0.2s cubic-bezier(0.34, 1.2, 0.64, 1) forwards; }

@keyframes slide-in-right {
  from { opacity: 0; transform: translateX(40px); }
  to   { opacity: 1; transform: translateX(0); }
}

@keyframes ping {
  75%, 100% { transform: scale(2); opacity: 0; }
}
.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4,0,0.6,1) infinite; }
@keyframes pulse { 0%,100% { opacity:1 } 50% { opacity:.6 } }

/* ── Transitions ── */
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.2s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

.dropdown-enter-active { transition: all 0.18s cubic-bezier(0.34, 1.3, 0.64, 1); }
.dropdown-enter-from   { opacity: 0; transform: translateY(-6px) scale(0.97); }
.dropdown-leave-active { transition: all 0.12s ease; }
.dropdown-leave-to     { opacity: 0; transform: translateY(-4px); }

.fade-slide-enter-active { transition: all 0.2s ease; }
.fade-slide-enter-from   { opacity: 0; transform: translateX(-8px); width: 0; overflow: hidden; }
.fade-slide-leave-active { transition: all 0.15s ease; }
.fade-slide-leave-to     { opacity: 0; transform: translateX(-8px); width: 0; overflow: hidden; }

.sub-menu-enter-active { transition: all 0.2s ease; max-height: 500px; }
.sub-menu-enter-from   { opacity: 0; max-height: 0; }
.sub-menu-leave-active { transition: all 0.15s ease; max-height: 500px; }
.sub-menu-leave-to     { opacity: 0; max-height: 0; overflow: hidden; }

/* ── Compact mode ── */
.compact-mode .p-6  { padding: 1rem !important; }
.compact-mode .p-5  { padding: 0.875rem !important; }
.compact-mode .p-4  { padding: 0.625rem !important; }
.compact-mode .gap-6 { gap: 0.75rem !important; }
.compact-mode .mb-8  { margin-bottom: 1rem !important; }

/* ── Reduced motion ── */
.reduce-motion *, .reduce-motion *::before, .reduce-motion *::after {
  animation-duration: 0.01ms !important;
  transition-duration: 0.01ms !important;
}

/* ── CSS variables (set by JS) ── */
:root { --accent: #6366f1; --card-radius: 12px; }
</style>