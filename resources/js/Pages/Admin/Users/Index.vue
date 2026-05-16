<!--
  Admin/Users/Index.vue - Advanced User Management
  ========================================================
  Stack: Laravel 12 + Inertia.js + Vue 3 (Composition API)
  
  Features:
  - Grid / List / Timeline views with smooth transitions
  - Advanced search, filtering by role, sort options
  - Bulk selection & bulk actions
  - User status indicators (verified, premium, active)
  - Role & permission badges
  - Activity counts (reports, tasks)
  - Impersonation (admin only)
  - Soft delete with inline trash panel
  - Export to CSV/JSON
  - Real-time stats cards with animations
  - Dark & Light theme support
  - Fully responsive design
-->
<template>
  <AuthenticatedLayout>

    <!-- ═══ PAGE HEADER ═══ -->
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
          <h2
            class="text-xl sm:text-2xl font-bold tracking-tight bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">
            User Management
          </h2>
          <p class="text-xs sm:text-sm text-slate-400 mt-0.5 font-light tracking-wide">Manage system users, roles, and
            permissions</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- View Toggle -->
          <div
            class="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1">
            <button v-for="mode in viewModes" :key="mode.key" @click="viewMode = mode.key" :class="[
              'p-2 rounded-lg transition-all duration-200 text-xs',
              viewMode === mode.key
                ? 'bg-white dark:bg-slate-700 shadow text-indigo-600 dark:text-indigo-400 scale-105'
                : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
            ]" :title="mode.label">
              <i :class="mode.icon" class="text-sm"></i>
            </button>
          </div>

          <!-- Export Button -->
          <div class="relative">
            <button
              @click.stop="() => { console.log('Export button clicked, menu=', exportMenu); exportMenu = !exportMenu }"
              class="group flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all text-xs font-medium">
              <i class="fa-solid fa-download group-hover:translate-y-0.5 transition-transform"></i>
              <span class="hidden sm:inline">Export</span>
              <i class="fa-solid fa-chevron-down text-[9px] ml-0.5 transition-transform"
                :class="exportMenu ? 'rotate-180' : ''"></i>
            </button>
            <Transition name="slide-down">
              <div v-if="exportMenu"
                class="absolute right-0 top-full mt-1.5 w-44 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-40 overflow-hidden"
                @click.stop>
                <button @click.stop="exportUsers('csv')"
                  class="w-full flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 transition-colors">
                  <i class="fa-solid fa-file-csv text-blue-500 w-4"></i> Export CSV
                </button>
                <button @click.stop="exportUsers('json')"
                  class="w-full flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 transition-colors">
                  <i class="fa-solid fa-file-code text-emerald-500 w-4"></i> Export JSON
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </template>

    <!-- ═══ MAIN CONTENT ═══ -->
    <div class="py-5 sm:py-8 px-3 sm:px-4 lg:px-6 space-y-5 sm:space-y-7">

      <!-- ── LOADING OVERLAY ── -->
      <Transition name="fade">
        <div v-if="isLoading" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-40">
          <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-8 text-center">
            <div class="relative w-16 h-16 mx-auto mb-4">
              <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full animate-spin"
                style="clip-path: polygon(0 0, 30% 0, 30% 30%, 0 30%);"></div>
              <div class="absolute inset-2 bg-white dark:bg-slate-800 rounded-full"></div>
              <div class="absolute inset-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full animate-pulse">
              </div>
            </div>
            <p class="text-slate-900 dark:text-white font-bold text-lg">Loading</p>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Filtering users...</p>
          </div>
        </div>
      </Transition>

      <!-- ── STATS CARDS ── -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div v-for="stat in statCards" :key="stat.key" @click="quickFilterRole(stat.key)" :class="[
          'group relative overflow-hidden rounded-2xl border p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 select-none',
          activeStatKey === stat.key
            ? `border-${stat.color}-400 dark:border-${stat.color}-600 bg-${stat.color}-50 dark:bg-${stat.color}-900/20 shadow-lg shadow-${stat.color}-100 dark:shadow-${stat.color}-900/20`
            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'
        ]">
          <div
            :class="`absolute -top-6 -right-6 w-20 h-20 rounded-full bg-${stat.color}-400/10 blur-2xl group-hover:scale-150 transition-transform duration-500`">
          </div>

          <div class="relative flex items-start justify-between">
            <div>
              <p
                class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium tracking-wide uppercase mb-1">
                {{ stat.label }}</p>
              <p
                :class="`text-2xl sm:text-3xl font-black text-${stat.color}-600 dark:text-${stat.color}-400 tabular-nums`">
                {{ dynamicStats[stat.key] ?? 0 }}
              </p>
            </div>
            <div
              :class="`w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-${stat.color}-100 dark:bg-${stat.color}-900/40 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`">
              <i :class="`${stat.icon} text-${stat.color}-600 dark:text-${stat.color}-400 text-base sm:text-lg`"></i>
            </div>
          </div>
          <div
            :class="`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-${stat.color}-400 to-${stat.color}-600 transition-all duration-300`"
            :style="{ width: activeStatKey === stat.key ? '100%' : '0%' }"></div>
        </div>
      </div>

      <!-- ── FILTERS ── -->
      <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
          <!-- Search -->
          <div class="relative flex-1 min-w-[160px]">
            <i
              class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input v-model="filters.search" type="text" placeholder="Search users…" @input="debouncedSearch"
              class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
          </div>

          <select v-model="filters.role" @change="applyFilters" class="filter-select">
            <option value="">All Roles</option>
            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
          </select>

          <select v-model="filters.sort" @change="applyFilters" class="filter-select hidden sm:block">
            <option value="created_at">Date Joined</option>
            <option value="name">Name A–Z</option>
            <option value="email">Email</option>
            <option value="reports_count">Most Reports</option>
          </select>

          <!-- Bulk actions -->
          <Transition name="slide-down">
            <div v-if="selectedIds.length"
              class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-xl">
              <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300">{{ selectedIds.length }}
                selected</span>
              <button @click="bulkDelete"
                class="text-[10px] text-red-500 hover:text-red-700 font-semibold transition-colors">
                <i class="fa-solid fa-trash mr-1"></i>Delete
              </button>
              <button @click="selectedIds = []"
                class="text-[10px] text-slate-400 hover:text-slate-600 transition-colors">Clear</button>
            </div>
          </Transition>

          <button @click="applyFilters"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-indigo-200 dark:shadow-indigo-900/30">
            Apply
          </button>
          <button v-if="hasActiveFilters" @click="resetFilters"
            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-xs transition-all">
            <i class="fa-solid fa-xmark mr-1"></i>Reset
          </button>
        </div>
      </div>

      <!-- ════════════════════════════════════════
           INLINE TRASH PANEL
      ════════════════════════════════════════ -->
      <Transition name="slide-down">
        <div v-if="showTrashPanel"
          class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/30 rounded-2xl overflow-hidden">
          <div
            class="flex items-center justify-between px-4 sm:px-6 py-3 border-b border-red-200 dark:border-red-900/30">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-trash-can text-red-500 text-sm"></i>
              <h3 class="font-bold text-red-700 dark:text-red-400 text-sm">Trash</h3>
              <span
                class="text-[10px] bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 px-2 py-0.5 rounded-full font-bold">{{
                  trashedUsers.length }} items</span>
            </div>
            <button @click="activeStatKey = ''; showTrashPanel = false"
              class="text-red-400 hover:text-red-600 transition-colors text-xs">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <div class="p-4 sm:p-5">
            <div v-if="trashedUsers.length === 0" class="text-center py-8 text-slate-400 text-xs">No trashed users</div>
            <div v-else class="space-y-2.5">
              <div v-for="u in trashedUsers" :key="u.id"
                class="flex items-center gap-3 bg-white dark:bg-slate-800 border border-red-100 dark:border-red-900/20 rounded-xl p-3 sm:p-4 group">
                <div
                  class="shrink-0 w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs">
                  {{ u.name.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-xs text-slate-700 dark:text-slate-300 line-clamp-1">{{ u.name }}</p>
                  <p class="text-[10px] text-slate-400 mt-0.5">{{ u.email }}</p>
                </div>
                <div class="flex items-center gap-1.5">
                  <button @click="restoreUser(u)"
                    class="px-2.5 py-1 text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 border border-emerald-200 dark:border-emerald-800 rounded-lg hover:bg-emerald-100 transition-colors">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Restore
                  </button>
                  <button @click="confirmForceDelete(u)"
                    class="px-2.5 py-1 text-[10px] font-bold bg-red-50 dark:bg-red-900/30 text-red-600 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 transition-colors">
                    <i class="fa-solid fa-trash mr-1"></i>Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- ════════════════════════════════════════
           GRID VIEW
      ════════════════════════════════════════ -->
      <div v-if="viewMode === 'grid' && !showTrashPanel"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
        <TransitionGroup name="card-pop" appear>
          <div v-for="user in users?.data || []" :key="user.id"
            class="group relative bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-2xl hover:shadow-indigo-100/60 dark:hover:shadow-indigo-900/30 hover:-translate-y-1 transition-all duration-300">
            <!-- Status stripe top -->
            <div :class="`absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r ${userStatusGradient(user)}`"></div>

            <!-- Selection checkbox -->
            <div class="absolute top-3 left-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
              <input type="checkbox" :value="user.id" v-model="selectedIds"
                class="w-4 h-4 rounded accent-indigo-600 cursor-pointer shadow">
            </div>

            <!-- Avatar Section -->
            <div
              class="relative h-24 sm:h-28 overflow-hidden bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 flex items-center justify-center">
              <div
                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl sm:text-2xl group-hover:scale-110 transition-transform duration-300 shadow-lg">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
            </div>

            <!-- Card body -->
            <div class="p-3 sm:p-4">
              <!-- Name & Email -->
              <div class="mb-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm line-clamp-1">{{ user.name }}
                </h3>
                <p class="text-[10px] text-slate-400 line-clamp-1">{{ user.email }}</p>
              </div>

              <!-- Status Badges -->
              <div class="flex flex-wrap gap-1.5 mb-3">
                <span v-for="role in user.roles" :key="role.name"
                  class="px-1.5 py-0.5 text-[9px] font-bold rounded-full" :class="roleBadge(role.name)">
                  {{ role.name }}
                </span>
                <span v-if="user.email_verified_at"
                  class="px-1.5 py-0.5 text-[9px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                  <i class="fa-solid fa-check-circle mr-0.5"></i>Verified
                </span>
                <span v-if="user.is_premium"
                  class="px-1.5 py-0.5 text-[9px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                  <i class="fa-solid fa-crown mr-0.5 text-[7px]"></i>Premium
                </span>
              </div>

              <!-- Stats -->
              <div
                class="flex items-center gap-2 text-[10px] text-slate-400 mb-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                <span class="flex items-center gap-1"><i class="fa-solid fa-file-lines text-[9px]"></i>{{
                  user.reports_count
                }}</span>
                <span class="flex items-center gap-1"><i class="fa-solid fa-list-check text-[9px]"></i>{{
                  user.tasks_count
                }}</span>
                <span class="flex items-center gap-1 ml-auto"><i class="fa-regular fa-calendar text-[9px]"></i>{{
                  formatDate(user.created_at) }}</span>
              </div>

              <!-- Action buttons -->
              <div class="flex items-center gap-1">
                <Link :href="route('admin.users.show', user.id)"
                  class="flex-1 px-2 py-1.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors">
                  <i class="fa-solid fa-eye mr-1"></i>View
                </Link>
                <Link :href="route('admin.users.edit', user.id)"
                  class="flex-1 px-2 py-1.5 text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                  <i class="fa-solid fa-pen mr-1"></i>Edit
                </Link>
                <button v-if="isAdmin && $page.props.auth.user.id !== user.id" @click="impersonateUser(user)"
                  class="flex-1 px-2 py-1.5 text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-lg transition-colors">
                  <i class="fa-solid fa-mask mr-1"></i>Act
                </button>
                <button v-if="$page.props.auth.user.id !== user.id" @click="confirmDelete(user)"
                  class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-400 hover:text-red-500 transition-colors">
                  <i class="fa-solid fa-trash text-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </TransitionGroup>

        <!-- Empty state -->
        <div v-if="!users?.data?.length" class="col-span-full">
          <EmptyUsersState />
        </div>
      </div>

      <!-- ════════════════════════════════════════
           LIST VIEW
      ════════════════════════════════════════ -->
      <div v-else-if="viewMode === 'list' && !showTrashPanel"
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                <th class="px-4 py-3 w-8">
                  <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                    class="w-3.5 h-3.5 rounded accent-indigo-600 cursor-pointer">
                </th>
                <th
                  class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                  User</th>
                <th
                  class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                  Roles</th>
                <th
                  class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                  Status</th>
                <th
                  class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">
                  Reports</th>
                <th
                  class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">
                  Tasks</th>
                <th
                  class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">
                  Joined</th>
                <th
                  class="text-right px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                  Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
              <TransitionGroup name="list-row" appear>
                <tr v-for="user in users?.data || []" :key="user.id"
                  class="group hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                  <td class="px-4 py-3">
                    <input type="checkbox" :value="user.id" v-model="selectedIds"
                      class="w-3.5 h-3.5 rounded accent-indigo-600 cursor-pointer">
                  </td>
                  <td class="px-4 sm:px-6 py-3.5">
                    <div class="flex items-center gap-3">
                      <div
                        class="shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-md flex-shrink-0">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <p class="font-semibold text-slate-900 dark:text-white text-xs sm:text-sm truncate">{{ user.name
                        }}
                        </p>
                        <p class="text-[10px] sm:text-xs text-slate-500 truncate">{{ user.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 sm:px-6 py-3.5">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="role in user.roles" :key="role.name" :class="roleBadge(role.name)"
                        class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 text-[9px] sm:text-xs font-semibold rounded-full">
                        {{ role.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-4 sm:px-6 py-3.5 hidden sm:table-cell">
                    <div class="flex flex-wrap gap-1">
                      <span v-if="user.email_verified_at"
                        class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                        <i class="fa-solid fa-check-circle mr-0.5"></i>Verified
                      </span>
                      <span v-else
                        class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                        <i class="fa-solid fa-clock mr-0.5"></i>Pending
                      </span>
                      <span v-if="user.is_premium"
                        class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                        <i class="fa-solid fa-crown mr-0.5 text-[7px]"></i>Premium
                      </span>
                    </div>
                  </td>
                  <td class="px-4 sm:px-6 py-3.5 hidden md:table-cell text-xs text-slate-700 dark:text-slate-300">{{
                    user.reports_count }}</td>
                  <td class="px-4 sm:px-6 py-3.5 hidden md:table-cell text-xs text-slate-700 dark:text-slate-300">{{
                    user.tasks_count }}</td>
                  <td class="px-4 sm:px-6 py-3.5 hidden lg:table-cell text-xs text-slate-400">{{
                    formatDate(user.created_at)
                  }}</td>
                  <td class="px-4 sm:px-6 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-0.5">
                      <Link :href="route('admin.users.show', user.id)"
                        class="p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-slate-400 hover:text-indigo-600 transition-colors"
                        title="View">
                        <i class="fa-solid fa-eye text-xs"></i>
                      </Link>
                      <Link :href="route('admin.users.edit', user.id)"
                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-400 hover:text-blue-600 transition-colors"
                        title="Edit">
                        <i class="fa-solid fa-pen text-xs"></i>
                      </Link>
                      <button v-if="isAdmin && $page.props.auth.user.id !== user.id" @click="impersonateUser(user)"
                        class="p-1.5 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/30 text-slate-400 hover:text-purple-600 transition-colors"
                        title="Impersonate">
                        <i class="fa-solid fa-mask text-xs"></i>
                      </button>
                      <button v-if="$page.props.auth.user.id !== user.id" @click="confirmDelete(user)"
                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition-colors"
                        title="Delete">
                        <i class="fa-solid fa-trash text-xs"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </TransitionGroup>
            </tbody>
          </table>
        </div>
        <div v-if="!users?.data?.length" class="py-16">
          <EmptyUsersState />
        </div>
        <div v-if="users?.data?.length" class="px-4 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-700">
          <Pagination :links="users.links" />
        </div>
      </div>

      <!-- ════════════════════════════════════════
           TIMELINE VIEW
      ════════════════════════════════════════ -->
      <div v-else-if="viewMode === 'timeline' && !showTrashPanel" class="relative">
        <!-- Vertical line -->
        <div
          class="absolute left-5 sm:left-8 top-0 bottom-0 w-px bg-gradient-to-b from-indigo-400 via-purple-400 to-transparent">
        </div>

        <div class="space-y-4 sm:space-y-5">
          <TransitionGroup name="timeline-item" appear>
            <div v-for="user in users?.data || []" :key="user.id" class="relative flex gap-5 sm:gap-8 group">
              <!-- Dot -->
              <div class="shrink-0 relative z-10 w-10 h-10 sm:w-16 sm:h-16 flex items-center justify-center">
                <div
                  :class="['w-5 h-5 rounded-full border-2 border-white dark:border-slate-900 shadow-lg transition-all duration-300 group-hover:scale-125', userStatusDot(user)]">
                </div>
              </div>

              <!-- Card -->
              <div
                class="flex-1 mb-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 sm:p-5 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-xl transition-all duration-300 group-hover:-translate-y-0.5">
                <!-- Top stripe -->
                <div
                  :class="`absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl bg-gradient-to-r ${userStatusGradient(user)}`">
                </div>

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                  <div class="flex-1 min-w-0">
                    <!-- Header -->
                    <div class="flex items-center gap-2 mb-2">
                      <div
                        class="shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <p class="font-bold text-sm text-slate-900 dark:text-white line-clamp-1">{{ user.name }}</p>
                        <p class="text-[10px] text-slate-400 line-clamp-1">{{ user.email }}</p>
                      </div>
                    </div>

                    <!-- Badges -->
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                      <span v-for="role in user.roles" :key="role.name" :class="roleBadge(role.name)"
                        class="px-2 py-0.5 text-[9px] font-bold rounded-full">
                        {{ role.name }}
                      </span>
                      <span v-if="user.email_verified_at"
                        class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                        <i class="fa-solid fa-check-circle mr-0.5"></i>Verified
                      </span>
                      <span v-if="user.is_premium"
                        class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                        <i class="fa-solid fa-crown mr-0.5 text-[7px]"></i>Premium
                      </span>
                    </div>

                    <!-- Stats -->
                    <p class="text-[11px] text-slate-400 flex items-center gap-3 flex-wrap">
                      <span><i class="fa-solid fa-file-lines mr-1"></i>{{ user.reports_count }} reports</span>
                      <span><i class="fa-solid fa-list-check mr-1"></i>{{ user.tasks_count }} tasks</span>
                      <span><i class="fa-regular fa-calendar mr-1"></i>{{ formatDate(user.created_at) }}</span>
                    </p>
                  </div>

                  <!-- Actions -->
                  <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                    <Link :href="route('admin.users.show', user.id)"
                      class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-bold hover:bg-indigo-100 transition-colors">
                      <i class="fa-solid fa-eye mr-1"></i>View
                    </Link>
                    <Link :href="route('admin.users.edit', user.id)"
                      class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-bold hover:bg-blue-100 transition-colors">
                      <i class="fa-solid fa-pen mr-1"></i>Edit
                    </Link>
                    <button v-if="isAdmin && $page.props.auth.user.id !== user.id" @click="impersonateUser(user)"
                      class="px-3 py-1.5 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg text-[10px] font-bold hover:bg-purple-100 transition-colors">
                      <i class="fa-solid fa-mask mr-1"></i>Impersonate
                    </button>
                    <button v-if="$page.props.auth.user.id !== user.id" @click="confirmDelete(user)"
                      class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition-colors">
                      <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>
        <div v-if="!users?.data?.length" class="ml-16">
          <EmptyUsersState />
        </div>
      </div>

      <!-- ── Pagination ── -->
      <div v-if="!showTrashPanel && viewMode !== 'list' && users?.data?.length" class="flex justify-center">
        <Pagination :links="users.links" />
      </div>
    </div>

    <!-- ════════════════════════════════════════════════════
         DELETE CONFIRM MODAL
    ════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="deleteModal.show = false"></div>
          <div
            class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 text-center">
              <div
                class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash-can text-red-500 text-xl"></i>
              </div>
              <h3 class="font-black text-slate-900 dark:text-white text-lg mb-1">Move to Trash?</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
                "<span class="font-semibold text-slate-700 dark:text-slate-300">{{ deleteModal.user?.name }}</span>"
                will be
                moved to Trash.
              </p>
              <div class="flex gap-3">
                <button @click="deleteModal.show = false"
                  class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold transition-all">Cancel</button>
                <button @click="deleteUser"
                  class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-all shadow shadow-red-200 dark:shadow-red-900/30">
                  <i class="fa-solid fa-trash-can mr-1.5"></i>Move to Trash
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ════════════════════════════════════════════════════
         FORCE DELETE MODAL
    ════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="forceDeleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="forceDeleteModal.show = false"></div>
          <div
            class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 text-center">
              <div
                class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i>
              </div>
              <h3 class="font-black text-slate-900 dark:text-white text-lg mb-1">Permanently Delete?</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">This cannot be undone. "<span
                  class="font-semibold text-slate-700 dark:text-slate-300">{{ forceDeleteModal.user?.name }}</span>"
                will be
                gone forever.</p>
              <div class="flex gap-3">
                <button @click="forceDeleteModal.show = false"
                  class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold transition-all">Cancel</button>
                <button @click="forceDeleteUser"
                  class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-all">
                  <i class="fa-solid fa-trash-can mr-1.5"></i>Delete Forever
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  users: { type: Object, required: true },
  roles: { type: Array, required: true },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  trashedUsers: { type: Array, default: () => [] },
})

const page = usePage()

// ── State ──────────────────────────────────────────────────────────
const viewMode = ref('grid')
const activeStatKey = ref('')
const showTrashPanel = ref(false)
const selectedIds = ref([])
const exportMenu = ref(false)
const isLoading = ref(false)

const filters = reactive({
  search: props.filters?.search || '',
  role: props.filters?.role || '',
  sort: props.filters?.sort || 'created_at',
})

// Modal states
const deleteModal = ref({ show: false, user: null })
const forceDeleteModal = ref({ show: false, user: null })

// ── Static config ──────────────────────────────────────────────────
const viewModes = [
  { key: 'grid', icon: 'fa-solid fa-grip', label: 'Grid' },
  { key: 'list', icon: 'fa-solid fa-list', label: 'List' },
  { key: 'timeline', icon: 'fa-solid fa-timeline', label: 'Timeline' },
]

// ── Computed ───────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  filters.search || filters.role || filters.sort !== 'created_at'
)

const isAdmin = computed(() => page.props.auth.user?.roles?.some(r => r.name === 'admin'))

const isAllSelected = computed(() =>
  props.users?.data?.length > 0 && props.users.data.every(u => selectedIds.value.includes(u.id))
)

// Dynamic stats - use server stats primarily
const dynamicStats = computed(() => {
  return {
    total: props.stats?.total ?? 0,
    active: props.stats?.active ?? 0,
    admin: props.stats?.admin ?? 0,
    manager: props.stats?.manager ?? 0,
    user: props.stats?.user ?? 0,
    trashed: props.stats?.trashed ?? 0,
  }
})

const statCards = computed(() => [
  { key: 'total', label: 'Total', icon: 'fa-solid fa-users', color: 'indigo' },
  { key: 'active', label: 'Active', icon: 'fa-solid fa-circle-check', color: 'emerald' },
  { key: 'admin', label: 'Admin', icon: 'fa-solid fa-shield', color: 'red' },
  { key: 'manager', label: 'Manager', icon: 'fa-solid fa-briefcase', color: 'blue' },
  { key: 'user', label: 'Regular', icon: 'fa-solid fa-user', color: 'purple' },
  { key: 'trashed', label: 'Trash', icon: 'fa-solid fa-trash-can', color: 'red' },
])

// ── Helpers ────────────────────────────────────────────────────────
const roleBadge = (role) => ({
  admin: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  manager: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
  user: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
}[role] ?? 'bg-slate-100 text-slate-600')

const userStatusGradient = (user) => {
  if (!user.email_verified_at) return 'from-amber-300 to-amber-500'
  if (user.is_premium) return 'from-amber-400 to-amber-600'
  return 'from-indigo-400 to-purple-500'
}

const userStatusDot = (user) => {
  if (!user.email_verified_at) return 'bg-amber-400 shadow-amber-300'
  if (user.is_premium) return 'bg-amber-400 shadow-amber-300'
  return 'bg-emerald-400 shadow-emerald-300'
}

const formatDate = (date) => {
  if (!date) return '—'
  const diff = Math.floor((Date.now() - new Date(date)) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

// ── Navigation / Filters ────────────────────────────────────────────
const applyFilters = () => {
  router.get(route('admin.users.index'), {
    search: filters.search || undefined,
    role: filters.role || undefined,
    sort: filters.sort,
  }, {
    preserveState: true,
    replace: true,
  })
}

const resetFilters = () => {
  filters.search = ''; filters.role = ''; filters.sort = 'created_at'
  applyFilters()
}

const quickFilterRole = (key) => {
  console.log('🔵 quickFilterRole called with key:', key)
  console.log('Current activeStatKey:', activeStatKey.value)

  if (key === 'trashed') {
    console.log('Trashed clicked')
    activeStatKey.value = activeStatKey.value === 'trashed' ? '' : 'trashed'
    showTrashPanel.value = activeStatKey.value === 'trashed'
    console.log('Trash panel toggled:', showTrashPanel.value)
    return
  }

  showTrashPanel.value = false

  // Toggle active state instantly (like trash)
  if (activeStatKey.value === key) {
    console.log('Toggling off:', key)
    activeStatKey.value = ''
    filters.role = ''
  } else {
    console.log('Toggling on:', key)
    activeStatKey.value = key
    // Map stat keys to role names
    if (key === 'admin' || key === 'manager' || key === 'user') {
      filters.role = key
      console.log('Set role filter:', filters.role)
    } else {
      filters.role = ''
      console.log('Cleared role filter')
    }
  }

  console.log('Before router.get - filters:', filters)

  // Apply filter without loading - instant like trash
  router.get(route('admin.users.index'), {
    search: filters.search || undefined,
    role: filters.role || undefined,
    sort: filters.sort,
  }, {
    preserveState: true,
    replace: true,
    onSuccess: () => {
      console.log('✅ Filter applied successfully')
    },
    onError: (error) => {
      console.log('❌ Filter error:', error)
    }
  })
}

let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 450)
}

// ── Selection ──────────────────────────────────────────────────────
const toggleSelectAll = () => {
  selectedIds.value = isAllSelected.value ? [] : props.users.data.map(u => u.id)
}

// ── Actions ────────────────────────────────────────────────────────
const confirmDelete = (user) => { deleteModal.value = { show: true, user } }

const deleteUser = () => {
  router.delete(route('admin.users.destroy', deleteModal.value.user.id), {
    onSuccess: () => { deleteModal.value.show = false; window.showToast?.('Moved to trash', 'success') },
  })
}

const confirmForceDelete = (user) => { forceDeleteModal.value = { show: true, user } }

const forceDeleteUser = () => {
  router.delete(route('admin.users.force-delete', forceDeleteModal.value.user.id), {
    onSuccess: () => { forceDeleteModal.value.show = false; window.showToast?.('Permanently deleted', 'success') },
  })
}

const restoreUser = (user) => {
  router.post(route('admin.users.restore', user.id), {}, {
    onSuccess: () => window.showToast?.('User restored!', 'success'),
  })
}

const impersonateUser = (user) => {
  router.post(route('admin.users.impersonate', user.id), {}, {
    onSuccess: () => window.showToast?.(`Impersonating ${user.name}`, 'info'),
  })
}

const bulkDelete = () => {
  if (!confirm(`Move ${selectedIds.value.length} users to Trash?`)) return
  selectedIds.value.forEach(id => {
    const user = props.users?.data?.find(u => u.id === id)
    if (user) router.delete(route('admin.users.destroy', user.id), { preserveState: true })
  })
  selectedIds.value = []
}

// ── Export ─────────────────────────────────────────────────────────
const exportUsers = (format) => {
  console.log('=== EXPORT FUNCTION CALLED ===')
  console.log('Format:', format)

  exportMenu.value = false

  try {
    // Get data - try multiple ways
    let data = props.users?.data
    console.log('Data from props.users.data:', data)

    if (!data) {
      data = props.users
      console.log('Using props.users as data:', data)
    }

    if (!Array.isArray(data)) {
      console.log('Data is not array, trying to convert:', typeof data)
      window.showToast?.('❌ No user data available', 'error')
      return
    }

    console.log('Final data array length:', data.length)

    if (!data || data.length === 0) {
      console.log('❌ NO DATA')
      window.showToast?.('No users to export', 'warning')
      return
    }

    window.showToast?.(`📥 Exporting ${data.length} users...`, 'info')
    console.log('Toast shown')

    let content = ''
    let filename = ''
    let mimeType = ''

    if (format === 'csv') {
      console.log('Creating CSV...')
      try {
        const headers = [
          'ID',
          'Name',
          'Email',
          'Roles',
          'Email Verified',
          'Premium',
          'Total Reports',
          'Shared Reports',
          'Total Tasks',
          'Assigned Tasks',
          'Date Added',
          'Email Verified At'
        ]
        const rows = []

        for (let i = 0; i < data.length; i++) {
          const u = data[i]
          const roles = u.roles?.map(r => r.name).join('; ') || 'N/A'

          rows.push([
            u.id || '',
            u.name || '',
            u.email || '',
            roles,
            u.email_verified_at ? 'Yes' : 'No',
            u.is_premium ? 'Yes' : 'No',
            u.reports_count || 0,
            u.shared_reports_count || 0,
            u.tasks_count || 0,
            u.tasks_assigned_count || 0,
            u.created_at ? new Date(u.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A',
            u.email_verified_at ? new Date(u.email_verified_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'Not Verified',
          ])
        }

        const csvRows = [headers, ...rows]
        content = csvRows.map(r => r.map(c => `"${c}"`).join(',')).join('\n')
        filename = `users-details-${new Date().toISOString().split('T')[0]}.csv`
        mimeType = 'text/csv'

        console.log('CSV created, length:', content.length)
      } catch (e) {
        console.error('CSV creation error:', e)
        throw e
      }
    }
    else if (format === 'json') {
      console.log('Creating JSON...')
      try {
        const jsonData = data.map(u => ({
          id: u.id,
          name: u.name,
          email: u.email,
          roles: u.roles?.map(r => r.name) || [],
          email_verified: u.email_verified_at ? 'Yes' : 'No',
          premium: u.is_premium ? 'Yes' : 'No',
          total_reports: u.reports_count || 0,
          shared_reports: u.shared_reports_count || 0,
          total_tasks: u.tasks_count || 0,
          assigned_tasks: u.tasks_assigned_count || 0,
          date_added: u.created_at ? new Date(u.created_at).toLocaleDateString() : 'N/A',
          email_verified_at: u.email_verified_at ? new Date(u.email_verified_at).toLocaleDateString() : 'Not Verified',
        }))
        content = JSON.stringify(jsonData, null, 2)
        filename = `users-details-${new Date().toISOString().split('T')[0]}.json`
        mimeType = 'application/json'

        console.log('JSON created, length:', content.length)
      } catch (e) {
        console.error('JSON creation error:', e)
        throw e
      }
    }

    if (!content) {
      console.log('❌ NO CONTENT')
      window.showToast?.('❌ Failed to create file', 'error')
      return
    }

    console.log('Downloading with content length:', content.length)
    downloadBlob(content, filename, mimeType)

    window.showToast?.(`✅ Downloaded ${data.length} users with details`, 'success')

  } catch (error) {
    console.error('❌ EXPORT ERROR:', error)
    console.error('Error message:', error.message)
    console.error('Stack:', error.stack)
    window.showToast?.(`❌ Export failed: ${error.message}`, 'error')
  }
}

const downloadBlob = (content, filename, mimeType) => {
  console.log('🔵 downloadBlob START')
  console.log('Content length:', content.length)
  console.log('Filename:', filename)
  console.log('MIME type:', mimeType)

  try {
    // Step 1: Create blob
    console.log('Step 1: Creating blob...')
    const blob = new Blob([content], { type: mimeType })
    console.log('✅ Blob created:', blob.size, 'bytes')

    // Step 2: Create URL
    console.log('Step 2: Creating URL...')
    const url = URL.createObjectURL(blob)
    console.log('✅ URL created')

    // Step 3: Create link element
    console.log('Step 3: Creating link...')
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    console.log('✅ Link created')

    // Step 4: Append to body
    console.log('Step 4: Appending to body...')
    document.body.appendChild(link)
    console.log('✅ Appended')

    // Step 5: Trigger click
    console.log('Step 5: Clicking link...')
    link.click()
    console.log('✅ Clicked')

    // Step 6: Cleanup
    console.log('Step 6: Cleaning up...')
    setTimeout(() => {
      document.body.removeChild(link)
      URL.revokeObjectURL(url)
      console.log('✅ Cleanup done')
    }, 200)

    console.log('🟢 downloadBlob SUCCESS')

  } catch (error) {
    console.error('🔴 downloadBlob ERROR:', error.message)
    console.error('Full error:', error)
    throw error
  }
}

// ── Keyboard shortcuts ─────────────────────────────────────────────
const handleKeydown = (e) => {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return
  if (e.key === 'n' || e.key === 'N') router.visit(route('admin.users.create'))
  if (e.key === 'Escape') {
    deleteModal.value.show = false
    forceDeleteModal.value.show = false
    exportMenu.value = false
  }
  if (e.key === '1') viewMode.value = 'grid'
  if (e.key === '2') viewMode.value = 'list'
  if (e.key === '3') viewMode.value = 'timeline'
}

const exportRef = ref(null)
const handleOutsideClick = (e) => {
  // Only close if clicking far outside
  const exportMenu = document.querySelector('.export-menu-container')
  if (exportMenu && !exportMenu.contains(e.target) && exportMenu.value !== false) {
    console.log('Outside click detected')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  // Don't add outside click listener - use @click.stop in template instead

  // Add test function to window
  window.testExport = () => {
    console.log('🔴 TEST EXPORT CALLED')
    exportUsers('csv')
  }
  console.log('✅ Test function available: window.testExport()')
  console.log('✅ Export menu ready')
})
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<!-- ── Inline EmptyState sub-component ── -->
<script>
export const EmptyUsersState = {
  setup() {
    const { router } = require('@inertiajs/vue3')
    return { router }
  },
  template: `
    <div class="flex flex-col items-center justify-center py-16 text-center">
      <div class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center mb-4 shadow-inner">
        <i class="fa-solid fa-users text-2xl text-slate-300 dark:text-slate-600"></i>
      </div>
      <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No users found</h3>
      <p class="text-xs text-slate-400 max-w-xs mb-5">No users match your filters, or there are no users yet.</p>
      <button
        @click="router.visit(route('admin.users.create'))"
        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-indigo-200 dark:shadow-indigo-900/30"
      >
        <i class="fa-solid fa-plus mr-1.5"></i>Create User
      </button>
    </div>
  `
}
</script>

<style scoped>
/* ── Filter select shared style ── */
.filter-select {
  @apply px-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition;
}

/* ── Card pop transition ── */
.card-pop-enter-active {
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.card-pop-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(0.97);
}

.card-pop-leave-active {
  transition: all 0.2s ease;
}

.card-pop-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* ── List row transition ── */
.list-row-enter-active {
  transition: all 0.25s ease;
}

.list-row-enter-from {
  opacity: 0;
  transform: translateX(-12px);
}

.list-row-leave-active {
  transition: all 0.2s ease;
}

.list-row-leave-to {
  opacity: 0;
}

/* ── Timeline ── */
.timeline-item-enter-active {
  transition: all 0.35s cubic-bezier(0.34, 1.3, 0.64, 1);
}

.timeline-item-enter-from {
  opacity: 0;
  transform: translateX(-16px);
}

/* ── Modal ── */
.modal-enter-active {
  transition: all 0.25s cubic-bezier(0.34, 1.3, 0.64, 1);
}

.modal-enter-from {
  opacity: 0;
}

.modal-leave-active {
  transition: all 0.18s ease;
}

.modal-leave-to {
  opacity: 0;
}

/* ── Slide down ── */
.slide-down-enter-active {
  transition: all 0.25s ease;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}

.slide-down-leave-active {
  transition: all 0.2s ease;
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ── Fade ── */
.fade-enter-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
}

.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-leave-to {
  opacity: 0;
}
</style>