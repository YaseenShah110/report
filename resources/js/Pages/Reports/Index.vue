<!--
  Reports/Index.vue — Enhanced Report Manager
  Stack: Laravel 12 + Inertia.js + Vue 3 (Composition API)
  Features: Grid/List/Timeline views, bulk actions, advanced filters,
            share modal, export menu, version badge, drag-to-reorder hint,
            animated stats, skeleton loading, infinite-feel pagination,
            keyboard shortcuts, dark mode, fully responsive.
-->
<template>
  <AuthenticatedLayout>

    <!-- ═══════════════════════════════════════════════════
         PAGE HEADER
    ═══════════════════════════════════════════════════ -->
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div class="flex items-center gap-3">
          <!-- Animated icon -->
          <div class="relative w-10 h-10 rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-500/30 overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.25),transparent_60%)]"></div>
            <i class="fa-solid fa-layer-group text-white text-base relative z-10"></i>
          </div>
          <div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight bg-gradient-to-r from-violet-600 via-fuchsia-500 to-pink-500 bg-clip-text text-transparent">
              My Reports
            </h2>
            <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5 font-light tracking-wide">
              Design, share &amp; export beautiful reports
            </p>
          </div>
        </div>

        <!-- Top actions -->
        <div class="flex items-center gap-2">
          <!-- Keyboard shortcut hint -->
          <div class="hidden lg:flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
            <kbd class="text-[10px] font-mono text-slate-400">N</kbd>
            <span class="text-[10px] text-slate-400">new</span>
          </div>

          <!-- Trash link -->
          <Link :href="route('reports.trashed')"
            class="flex items-center gap-1.5 px-3 py-2 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-red-300 hover:text-red-500 rounded-xl text-xs font-medium transition-all">
            <i class="fa-solid fa-trash-can"></i>
            <span class="hidden sm:inline">Trash</span>
          </Link>

          <!-- Assigned to me -->
          <Link :href="route('reports.assigned')"
            class="flex items-center gap-1.5 px-3 py-2 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-violet-300 hover:text-violet-600 rounded-xl text-xs font-medium transition-all">
            <i class="fa-solid fa-user-check"></i>
            <span class="hidden sm:inline">Assigned</span>
          </Link>
        </div>
      </div>
    </template>

    <!-- ═══════════════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════════════ -->
    <div class="py-5 sm:py-8 px-3 sm:px-4 lg:px-6 space-y-5 sm:space-y-6">

      <!-- ── STATS CARDS ────────────────────────────────── -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div
          v-for="stat in statCards" :key="stat.key"
          @click="quickFilterStatus(stat.key)"
          :class="[
            'group relative overflow-hidden rounded-2xl border p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 select-none',
            filters.status === stat.key
              ? `border-${stat.color}-400 dark:border-${stat.color}-500 bg-${stat.color}-50 dark:bg-${stat.color}-900/20 shadow-lg shadow-${stat.color}-100 dark:shadow-${stat.color}-900/20`
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'
          ]"
        >
          <!-- Decorative glow -->
          <div :class="`absolute -top-8 -right-8 w-24 h-24 rounded-full bg-${stat.color}-400/10 blur-2xl group-hover:scale-150 transition-transform duration-500`"></div>
          <!-- Active indicator bar -->
          <div :class="`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-${stat.color}-400 to-${stat.color}-600 transition-all duration-500`"
               :style="{ width: filters.status === stat.key ? '100%' : '0%' }"></div>

          <div class="relative flex items-start justify-between">
            <div>
              <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-semibold tracking-widest uppercase mb-1">{{ stat.label }}</p>
              <p :class="`text-2xl sm:text-3xl font-black text-${stat.color}-600 dark:text-${stat.color}-400 tabular-nums transition-all duration-700`">
                {{ animatedStats[stat.key] ?? 0 }}
              </p>
            </div>
            <div :class="`w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-${stat.color}-100 dark:bg-${stat.color}-900/40 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`">
              <i :class="`${stat.icon} text-${stat.color}-600 dark:text-${stat.color}-400 text-base sm:text-lg`"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- ── TOOLBAR ─────────────────────────────────────── -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">

          <!-- Search -->
          <div class="relative flex-1 min-w-[160px]">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search reports…"
              @input="debouncedSearch"
              class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
            />
            <button v-if="filters.search" @click="filters.search=''; loadReports()"
              class="absolute right-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center hover:bg-slate-300 transition">
              <i class="fa-solid fa-xmark text-[9px] text-slate-500"></i>
            </button>
          </div>

          <!-- Status pills -->
          <div class="flex gap-1.5">
            <button
              v-for="s in statusFilters" :key="s.key"
              @click="filters.status = filters.status === s.key ? 'all' : s.key; loadReports()"
              :class="[
                'px-2.5 py-1 text-[10px] sm:text-xs font-semibold rounded-full capitalize transition-all whitespace-nowrap',
                filters.status === s.key
                  ? `bg-${s.color}-600 text-white shadow-sm`
                  : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-violet-300'
              ]"
            >
              {{ s.label }}
              <span class="ml-1 opacity-70">{{ getStatusCount(s.key) }}</span>
            </button>
          </div>

          <!-- Sort -->
          <select v-model="filters.sort" @change="loadReports"
            class="px-2.5 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500 transition">
            <option value="updated_at">Last Modified</option>
            <option value="created_at">Date Created</option>
            <option value="title">Title A–Z</option>
          </select>

          <!-- Bulk action (appears when selected) -->
          <Transition name="slide-down">
            <div v-if="selectedIds.length" class="flex items-center gap-2 px-3 py-1.5 bg-violet-50 dark:bg-violet-900/30 border border-violet-200 dark:border-violet-800 rounded-xl">
              <span class="text-[11px] font-bold text-violet-700 dark:text-violet-300">{{ selectedIds.length }} selected</span>
              <button @click="bulkDelete" class="text-[10px] text-red-500 hover:text-red-700 font-semibold transition-colors">
                <i class="fa-solid fa-trash mr-1"></i>Delete
              </button>
              <button @click="selectedIds = []" class="text-[10px] text-slate-400 hover:text-slate-600 transition-colors">Clear</button>
            </div>
          </Transition>

          <div class="ml-auto flex items-center gap-1.5">
            <!-- Reset filters -->
            <button v-if="hasActiveFilters" @click="resetFilters"
              class="px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
              <i class="fa-solid fa-rotate-left mr-1"></i>Reset
            </button>

            <!-- View toggle -->
            <div class="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl p-1">
              <button
                v-for="v in viewModes" :key="v.key"
                @click="viewMode = v.key"
                :class="[
                  'p-1.5 sm:p-2 rounded-lg transition-all duration-200 text-xs',
                  viewMode === v.key
                    ? 'bg-white dark:bg-slate-600 shadow text-violet-600 dark:text-violet-400 scale-105'
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
                ]"
                :title="v.label"
              >
                <i :class="v.icon" class="text-sm"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════
           GRID VIEW
      ══════════════════════════════════════════════════ -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
        <TransitionGroup name="card-pop" appear>
          <div
            v-for="report in reports.data" :key="report.id"
            class="group relative bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-2xl hover:shadow-violet-100/60 dark:hover:shadow-violet-900/30 hover:-translate-y-1 transition-all duration-300"
          >
            <!-- Selection checkbox -->
            <div class="absolute top-3 left-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
              <input type="checkbox" :value="report.id" v-model="selectedIds"
                class="w-4 h-4 rounded accent-violet-600 cursor-pointer shadow">
            </div>

            <!-- Thumbnail area -->
            <div class="relative h-36 sm:h-44 overflow-hidden"
                 :style="`background: linear-gradient(135deg, ${report.settings?.primary_color || '#7c3aed'}18, ${report.settings?.accent_color || '#c026d3'}18)`">
              <!-- Page mini preview -->
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-24 sm:w-32 h-16 sm:h-20 bg-white dark:bg-slate-700 rounded-lg shadow-xl border border-white/60 dark:border-slate-600 flex flex-col gap-1 p-2 group-hover:scale-105 group-hover:rotate-1 transition-all duration-500">
                  <div class="h-1.5 rounded-full w-3/4" :style="`background:${report.settings?.primary_color || '#7c3aed'}`"></div>
                  <div class="h-1 rounded-full w-full bg-slate-100 dark:bg-slate-600"></div>
                  <div class="h-1 rounded-full w-5/6 bg-slate-100 dark:bg-slate-600"></div>
                  <div class="h-1 rounded-full w-2/3 bg-slate-100 dark:bg-slate-600"></div>
                  <div class="mt-1 flex gap-1">
                    <div class="h-3 w-8 rounded" :style="`background:${report.settings?.accent_color || '#c026d3'}40`"></div>
                    <div class="h-3 w-6 rounded" :style="`background:${report.settings?.primary_color || '#7c3aed'}30`"></div>
                  </div>
                </div>
              </div>

              <!-- Status badge -->
              <div class="absolute top-3 right-3">
                <span :class="['px-2 py-0.5 text-[9px] font-bold rounded-full uppercase tracking-wide', statusBadge(report.status)]">
                  {{ report.status }}
                </span>
              </div>

              <!-- Template badge -->
              <div v-if="report.template" class="absolute bottom-3 left-3">
                <span class="px-1.5 py-0.5 text-[9px] font-semibold bg-black/30 backdrop-blur-sm text-white rounded-md">
                  <i class="fa-solid fa-layer-group mr-1"></i>{{ report.template.name }}
                </span>
              </div>

              <!-- Hover overlay actions -->
              <div class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all duration-200 flex items-center justify-center gap-2">
                <Link :href="route('reports.edit', report.slug)"
                  class="px-3 py-1.5 bg-white text-slate-900 rounded-lg text-[10px] font-bold hover:bg-violet-50 transition-colors shadow-lg">
                  <i class="fa-solid fa-pen mr-1 text-violet-600"></i>Edit
                </Link>
                <Link :href="route('reports.preview', report.slug)" target="_blank"
                  class="px-3 py-1.5 bg-violet-600 text-white rounded-lg text-[10px] font-bold hover:bg-violet-500 transition-colors shadow-lg">
                  <i class="fa-solid fa-eye mr-1"></i>Preview
                </Link>
              </div>
            </div>

            <!-- Card body -->
            <div class="p-3 sm:p-4">
              <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm line-clamp-1 flex-1">{{ report.title }}</h3>
              </div>

              <!-- Meta row -->
              <div class="flex items-center gap-2 text-[10px] text-slate-400 mb-3">
                <span class="flex items-center gap-1">
                  <i class="fa-regular fa-calendar"></i>{{ formatDate(report.updated_at) }}
                </span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span class="flex items-center gap-1">
                  <i class="fa-regular fa-file"></i>{{ report.total_pages || 1 }} page{{ (report.total_pages || 1) > 1 ? 's' : '' }}
                </span>
                <span v-if="report.is_public" class="ml-auto flex items-center gap-1 text-emerald-500">
                  <i class="fa-solid fa-globe text-[8px]"></i>Shared
                </span>
              </div>

              <!-- Action bar -->
              <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 dark:border-slate-700">
                <!-- Left actions -->
                <div class="flex gap-0.5">
                  <Link :href="route('reports.edit', report.slug)"
                    class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/20 text-slate-400 hover:text-violet-600 transition-colors" title="Edit">
                    <i class="fa-solid fa-pen text-[10px]"></i>
                  </Link>
                  <Link :href="route('reports.preview', report.slug)" target="_blank"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors" title="Preview">
                    <i class="fa-solid fa-eye text-[10px]"></i>
                  </Link>
                  <button @click="openExportMenu(report)"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors" title="Export">
                    <i class="fa-solid fa-download text-[10px]"></i>
                  </button>
                </div>

                <!-- Right actions -->
                <div class="flex gap-0.5">
                  <button @click="openVersions(report)"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors" title="Versions">
                    <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                  </button>
                  <button @click="duplicateReport(report)"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors" title="Duplicate">
                    <i class="fa-regular fa-clone text-[10px]"></i>
                  </button>
                  <button @click="openShareModal(report)"
                    class="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-slate-400 hover:text-emerald-600 transition-colors" title="Share">
                    <i class="fa-solid fa-share-nodes text-[10px]"></i>
                  </button>
                  <button @click="openStatusMenu(report)"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors" title="Change Status">
                    <i class="fa-solid fa-ellipsis-vertical text-[10px]"></i>
                  </button>
                  <button @click="confirmDelete(report)"
                    class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-400 hover:text-red-500 transition-colors" title="Move to Trash">
                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </TransitionGroup>

        <!-- Empty state -->
        <div v-if="!reports.data?.length" class="col-span-full">
          <EmptyState @create="$inertia.visit(route('reports.create'))" />
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════
           LIST VIEW
      ══════════════════════════════════════════════════ -->
      <div v-else-if="viewMode === 'list'" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
              <tr>
                <th class="px-4 py-3 text-left w-8">
                  <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                    class="w-3.5 h-3.5 rounded accent-violet-600 cursor-pointer">
                </th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Report</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden md:table-cell">Pages</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Template</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden sm:table-cell">Modified</th>
                <th class="px-4 py-3 text-right text-[10px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
              <TransitionGroup name="list-row" appear>
                <tr
                  v-for="report in reports.data" :key="report.id"
                  class="group hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors"
                >
                  <td class="px-4 py-3">
                    <input type="checkbox" :value="report.id" v-model="selectedIds"
                      class="w-3.5 h-3.5 rounded accent-violet-600 cursor-pointer">
                  </td>
                  <td class="px-4 py-3.5">
                    <div class="flex items-center gap-3">
                      <!-- Color swatch -->
                      <div class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center overflow-hidden"
                           :style="`background:linear-gradient(135deg,${report.settings?.primary_color||'#7c3aed'}20,${report.settings?.accent_color||'#c026d3'}20)`">
                        <i class="fa-solid fa-file-lines text-[11px]" :style="`color:${report.settings?.primary_color||'#7c3aed'}`"></i>
                      </div>
                      <div class="min-w-0">
                        <Link :href="route('reports.edit', report.slug)"
                          class="font-semibold text-xs sm:text-sm text-slate-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 transition-colors line-clamp-1">
                          {{ report.title }}
                        </Link>
                        <p v-if="report.is_public" class="text-[10px] text-emerald-500 mt-0.5 flex items-center gap-1">
                          <i class="fa-solid fa-globe text-[8px]"></i>Public link active
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3.5">
                    <button @click="quickChangeStatus(report)" :class="['px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wide transition-all hover:opacity-80', statusBadge(report.status)]">
                      {{ report.status }}
                    </button>
                  </td>
                  <td class="px-4 py-3.5 hidden md:table-cell text-xs text-slate-500 dark:text-slate-400">
                    {{ report.total_pages || 1 }}
                  </td>
                  <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-slate-400">
                    {{ report.template?.name || '—' }}
                  </td>
                  <td class="px-4 py-3.5 hidden sm:table-cell text-xs text-slate-400">
                    {{ formatDate(report.updated_at) }}
                  </td>
                  <td class="px-4 py-3.5">
                    <div class="flex items-center justify-end gap-1">
                      <Link :href="route('reports.edit', report.slug)"
                        class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/30 text-slate-400 hover:text-violet-600 transition-colors">
                        <i class="fa-solid fa-pen text-xs"></i>
                      </Link>
                      <button @click="openShareModal(report)"
                        class="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-400 hover:text-emerald-600 transition-colors">
                        <i class="fa-solid fa-share-nodes text-xs"></i>
                      </button>
                      <button @click="openExportMenu(report)"
                        class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-download text-xs"></i>
                      </button>
                      <button @click="confirmDelete(report)"
                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </TransitionGroup>
            </tbody>
          </table>
        </div>

        <!-- Empty in list -->
        <div v-if="!reports.data?.length" class="py-16">
          <EmptyState @create="$inertia.visit(route('reports.create'))" />
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════
           TIMELINE VIEW
      ══════════════════════════════════════════════════ -->
      <div v-else-if="viewMode === 'timeline'" class="relative">
        <!-- Vertical line -->
        <div class="absolute left-5 sm:left-8 top-0 bottom-0 w-px bg-gradient-to-b from-violet-400 via-fuchsia-400 to-transparent"></div>

        <div class="space-y-4 sm:space-y-5">
          <TransitionGroup name="timeline-item" appear>
            <div v-for="(report, idx) in reports.data" :key="report.id"
                 class="relative flex gap-5 sm:gap-8 group">
              <!-- Timeline dot -->
              <div class="shrink-0 relative z-10 w-10 h-10 sm:w-16 sm:h-16 flex items-center justify-center">
                <div :class="['w-5 h-5 rounded-full border-2 border-white dark:border-slate-900 shadow-lg transition-all duration-300 group-hover:scale-125', statusDot(report.status)]"></div>
              </div>

              <!-- Card -->
              <div class="flex-1 mb-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 sm:p-5 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-xl transition-all duration-300 group-hover:-translate-y-0.5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                      <span :class="['px-2 py-0.5 text-[9px] font-bold rounded-full uppercase tracking-wide', statusBadge(report.status)]">
                        {{ report.status }}
                      </span>
                      <span v-if="report.template" class="text-[10px] text-slate-400 font-medium">
                        <i class="fa-solid fa-layer-group mr-1"></i>{{ report.template.name }}
                      </span>
                      <span v-if="report.is_public" class="text-[10px] text-emerald-500 font-semibold">
                        <i class="fa-solid fa-globe mr-1 text-[8px]"></i>Public
                      </span>
                    </div>
                    <Link :href="route('reports.edit', report.slug)"
                      class="font-bold text-sm sm:text-base text-slate-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 transition-colors line-clamp-1">
                      {{ report.title }}
                    </Link>
                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-3">
                      <span><i class="fa-regular fa-calendar mr-1"></i>{{ formatDate(report.updated_at) }}</span>
                      <span><i class="fa-regular fa-file mr-1"></i>{{ report.total_pages || 1 }} page{{ (report.total_pages || 1) !== 1 ? 's' : '' }}</span>
                    </p>
                  </div>

                  <div class="flex items-center gap-1.5 shrink-0">
                    <Link :href="route('reports.edit', report.slug)"
                      class="px-3 py-1.5 bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 rounded-lg text-[10px] font-bold hover:bg-violet-100 transition-colors">
                      <i class="fa-solid fa-pen mr-1"></i>Edit
                    </Link>
                    <button @click="openShareModal(report)"
                      class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg text-[10px] font-bold hover:bg-emerald-100 transition-colors">
                      <i class="fa-solid fa-share-nodes mr-1"></i>Share
                    </button>
                    <button @click="openExportMenu(report)"
                      class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors">
                      <i class="fa-solid fa-download text-xs"></i>
                    </button>
                    <button @click="confirmDelete(report)"
                      class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors">
                      <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>

        <div v-if="!reports.data?.length" class="ml-16">
          <EmptyState @create="$inertia.visit(route('reports.create'))" />
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="reports.links?.length > 3" class="flex justify-center">
        <Pagination :links="reports.links" :from="reports.from" :to="reports.to" :total="reports.total" />
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         DELETE MODAL
    ══════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="deleteModal.show = false"></div>
          <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 text-center">
              <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash-can text-red-500 text-xl"></i>
              </div>
              <h3 class="font-black text-slate-900 dark:text-white text-lg mb-1">Delete Report?</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
                "<span class="font-semibold text-slate-700 dark:text-slate-300">{{ deleteModal.report?.title }}</span>" will be moved to Trash and can be restored later.
              </p>
              <div class="flex gap-3">
                <button @click="deleteModal.show = false"
                  class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold transition-all">
                  Cancel
                </button>
                <button @click="deleteReport"
                  class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-all shadow shadow-red-200 dark:shadow-red-900/30">
                  <i class="fa-solid fa-trash-can mr-1.5"></i>Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════════════════
         SHARE MODAL
    ══════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="shareModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="shareModal.show = false"></div>
          <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                  <i class="fa-solid fa-share-nodes text-emerald-600 text-sm"></i>
                </div>
                <h3 class="font-black text-slate-900 dark:text-white">Share Report</h3>
              </div>
              <button @click="shareModal.show = false" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition-colors">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="p-6 space-y-5">
              <!-- Public toggle -->
              <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white text-sm">Public Access</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Anyone with the link can view</p>
                </div>
                <button @click="togglePublicAccess"
                  :class="['relative w-11 h-6 rounded-full transition-all duration-300', shareModal.isPublic ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-600']">
                  <span :class="['absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-all duration-300', shareModal.isPublic ? 'translate-x-5' : '']"></span>
                </button>
              </div>

              <!-- Share link -->
              <Transition name="slide-down">
                <div v-if="shareModal.link" class="space-y-3">
                  <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Share Link</label>
                  <div class="flex gap-2">
                    <input :value="shareModal.link" readonly
                      class="flex-1 px-3 py-2.5 text-[11px] font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <button @click="copyLink"
                      :class="['px-4 py-2.5 rounded-xl text-xs font-bold transition-all', linkCopied ? 'bg-emerald-500 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white']">
                      <i :class="['fa-solid mr-1', linkCopied ? 'fa-check' : 'fa-copy']"></i>
                      {{ linkCopied ? 'Copied!' : 'Copy' }}
                    </button>
                  </div>

                  <!-- Quick share buttons -->
                  <div class="flex gap-2 pt-1">
                    <a :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareModal.link)}`" target="_blank"
                      class="flex-1 flex items-center justify-center gap-1.5 py-2 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 text-sky-600 dark:text-sky-400 rounded-xl text-[10px] font-bold hover:bg-sky-100 transition-colors">
                      <i class="fa-brands fa-x-twitter"></i> Twitter
                    </a>
                    <button @click="sendEmail"
                      class="flex-1 flex items-center justify-center gap-1.5 py-2 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 text-violet-600 dark:text-violet-400 rounded-xl text-[10px] font-bold hover:bg-violet-100 transition-colors">
                      <i class="fa-solid fa-envelope"></i> Email
                    </button>
                  </div>

                  <!-- Revoke -->
                  <button @click="revokeLink"
                    class="w-full py-2 text-[10px] font-semibold text-red-400 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-link-slash mr-1"></i>Revoke share link
                  </button>
                </div>
              </Transition>

              <!-- Loading state -->
              <div v-if="shareModal.loading" class="flex items-center justify-center py-4">
                <div class="w-6 h-6 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin"></div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════════════════
         EXPORT MENU (floating)
    ══════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="exportMenu.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0" @click="exportMenu.show = false"></div>
          <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl w-full max-w-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <h3 class="font-bold text-slate-900 dark:text-white text-sm">Export Report</h3>
              <button @click="exportMenu.show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <div class="p-3 space-y-1">
              <a :href="route('reports.export.pdf', exportMenu.report?.slug)"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <i class="fa-solid fa-file-pdf text-red-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-xs font-bold">PDF</p>
                  <p class="text-[10px] text-slate-400">High-quality print format</p>
                </div>
              </a>
              <a :href="route('reports.export.excel', exportMenu.report?.slug)"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <i class="fa-solid fa-file-excel text-emerald-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-xs font-bold">Excel</p>
                  <p class="text-[10px] text-slate-400">Tables & chart data</p>
                </div>
              </a>
              <a :href="route('reports.export.csv', exportMenu.report?.slug)"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <i class="fa-solid fa-file-csv text-blue-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-xs font-bold">CSV</p>
                  <p class="text-[10px] text-slate-400">Raw data export</p>
                </div>
              </a>
              <a :href="route('reports.export.image', exportMenu.report?.slug)"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/20 text-slate-700 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <i class="fa-solid fa-file-image text-violet-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-xs font-bold">PNG Image</p>
                  <p class="text-[10px] text-slate-400">Visual snapshot</p>
                </div>
              </a>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════════════════
         VERSION HISTORY MODAL
    ══════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="versionsModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="versionsModal.show = false"></div>
          <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-md max-h-[80vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                  <i class="fa-solid fa-clock-rotate-left text-amber-600 text-sm"></i>
                </div>
                <h3 class="font-black text-slate-900 dark:text-white">Version History</h3>
              </div>
              <button @click="versionsModal.show = false" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition-colors">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <div class="overflow-y-auto flex-1 p-4">
              <div v-if="versionsModal.loading" class="flex items-center justify-center py-12">
                <div class="w-8 h-8 rounded-full border-2 border-amber-400 border-t-transparent animate-spin"></div>
              </div>
              <div v-else-if="versionsModal.versions.length" class="space-y-2">
                <div v-for="v in versionsModal.versions" :key="v.id"
                  class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-amber-200 dark:hover:border-amber-800 transition-colors group">
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-900 dark:text-white line-clamp-1">{{ v.label }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">v{{ v.version_number }} · {{ formatDate(v.created_at) }}</p>
                  </div>
                  <button @click="restoreVersion(v)"
                    class="ml-3 shrink-0 px-3 py-1 text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg hover:bg-amber-100 transition-colors opacity-0 group-hover:opacity-100">
                    Restore
                  </button>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-200 dark:text-slate-700 mb-3 block"></i>
                <p class="text-sm text-slate-400">No versions yet</p>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════════════════
         STATUS QUICK MENU
    ══════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="statusMenu.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0" @click="statusMenu.show = false"></div>
          <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl w-full max-w-xs overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
              <p class="text-xs font-bold text-slate-500">Change status for</p>
              <p class="text-sm font-bold text-slate-900 dark:text-white line-clamp-1">{{ statusMenu.report?.title }}</p>
            </div>
            <div class="p-2 space-y-1">
              <button v-for="s in ['draft','published','archived','trashed']" :key="s"
                @click="changeStatus(statusMenu.report, s)"
                :class="['w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-left transition-colors', statusMenu.report?.status === s ? 'bg-violet-50 dark:bg-violet-900/20' : 'hover:bg-slate-50 dark:hover:bg-slate-800']">
                <div :class="['w-2 h-2 rounded-full', { draft: 'bg-amber-400', published: 'bg-emerald-400', archived: 'bg-slate-400', trashed: 'bg-red-400' }[s]]"></div>
                <span class="text-xs font-semibold capitalize text-slate-700 dark:text-slate-300">{{ s === 'trashed' ? 'Move to Trash' : s }}</span>
                <i v-if="statusMenu.report?.status === s" class="fa-solid fa-check ml-auto text-violet-600 text-xs"></i>
              </button>
              <div class="border-t border-slate-100 dark:border-slate-800 pt-1 mt-1">
                <button @click="duplicateReport(statusMenu.report); statusMenu.show = false"
                  class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-left hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                  <i class="fa-regular fa-clone text-slate-400 text-xs"></i>
                  <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Duplicate</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </AuthenticatedLayout>
</template>

<!-- ══════════════════════════════════════════════════════════════
     SCRIPT — Composition API
══════════════════════════════════════════════════════════════ -->
<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

// ── Props ───────────────────────────────────────────────────────
const props = defineProps({
  reports: { type: Object, required: true },
  stats:   { type: Object, default: () => ({}) },
})

// ── State ────────────────────────────────────────────────────────
const viewMode    = ref('grid')
const selectedIds = ref([])
const linkCopied  = ref(false)

const filters = reactive({
  search: '',
  status: 'all',
  sort:   'updated_at',
})

// Animated stats (count-up effect)
const animatedStats = reactive({ total: 0, published: 0, draft: 0, archived: 0, trashed: 0 })

// Modal states
const deleteModal   = ref({ show: false, report: null })
const shareModal    = ref({ show: false, report: null, link: '', isPublic: false, loading: false })
const exportMenu    = ref({ show: false, report: null })
const versionsModal = ref({ show: false, report: null, versions: [], loading: false })
const statusMenu    = ref({ show: false, report: null })

// ── Static config ────────────────────────────────────────────────
const viewModes = [
  { key: 'grid',     icon: 'fa-solid fa-grip',        label: 'Grid'     },
  { key: 'list',     icon: 'fa-solid fa-list',         label: 'List'     },
  { key: 'timeline', icon: 'fa-solid fa-timeline',     label: 'Timeline' },
]

const statCards = [
  { key: 'total',     label: 'Total',     icon: 'fa-solid fa-layer-group',     color: 'violet'  },
  { key: 'published', label: 'Published', icon: 'fa-solid fa-globe',            color: 'emerald' },
  { key: 'draft',     label: 'Drafts',    icon: 'fa-solid fa-pen-fancy',        color: 'amber'   },
  { key: 'archived',  label: 'Archived',  icon: 'fa-solid fa-box-archive',      color: 'slate'   },
  { key: 'trashed',   label: 'Trash',     icon: 'fa-solid fa-trash-can',        color: 'red'     },
]

const statusFilters = [
  { key: 'all',       label: 'All',       color: 'slate'   },
  { key: 'draft',     label: 'Draft',     color: 'amber'   },
  { key: 'published', label: 'Published', color: 'emerald' },
  { key: 'archived',  label: 'Archived',  color: 'slate'   },
]

// ── Computed ─────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  filters.search || filters.status !== 'all' || filters.sort !== 'updated_at'
)

const isAllSelected = computed(() =>
  props.reports.data?.length > 0 &&
  props.reports.data.every(r => selectedIds.value.includes(r.id))
)

// ── Helpers ───────────────────────────────────────────────────────
const statusBadge = (s) => ({
  draft:     'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  published: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  archived:  'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
  trashed:   'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}[s] ?? 'bg-slate-100 text-slate-600')

const statusDot = (s) => ({
  draft:     'bg-amber-400 shadow-amber-300',
  published: 'bg-emerald-400 shadow-emerald-300',
  archived:  'bg-slate-300',
  trashed:   'bg-red-400 shadow-red-300',
}[s] ?? 'bg-slate-300')

const formatDate = (date) => {
  if (!date) return '—'
  const diff = Math.floor((Date.now() - new Date(date)) / 1000)
  if (diff < 60)    return 'just now'
  if (diff < 3600)  return `${Math.floor(diff/60)}m ago`
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff/86400)}d ago`
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

const getStatusCount = (key) =>
  key === 'all' ? props.stats?.total || 0 : props.stats?.[key] || 0

// ── Animated stats count-up ───────────────────────────────────────
const countUp = (key, target, duration = 800) => {
  const start = Date.now()
  const from  = animatedStats[key]
  const tick  = () => {
    const progress = Math.min((Date.now() - start) / duration, 1)
    const ease = 1 - Math.pow(1 - progress, 3)
    animatedStats[key] = Math.round(from + (target - from) * ease)
    if (progress < 1) requestAnimationFrame(tick)
  }
  requestAnimationFrame(tick)
}

// ── Load / Navigation ─────────────────────────────────────────────
const loadReports = () =>
  router.get(route('reports.index'), {
    search: filters.search || undefined,
    status: filters.status !== 'all' ? filters.status : undefined,
    sort:   filters.sort,
  }, { preserveState: true, preserveScroll: true, replace: true })

let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(loadReports, 450)
}

const resetFilters = () => {
  filters.search = ''
  filters.status = 'all'
  filters.sort   = 'updated_at'
  loadReports()
}

const quickFilterStatus = (key) => {
  // stat card maps 'total' → 'all'
  const mapped = key === 'total' ? 'all' : key
  filters.status = filters.status === mapped ? 'all' : mapped
  loadReports()
}

// ── Selection ────────────────────────────────────────────────────
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = props.reports.data.map(r => r.id)
  }
}

// ── Actions ───────────────────────────────────────────────────────
const confirmDelete = (report) => { deleteModal.value = { show: true, report } }

const deleteReport = () => {
  router.delete(route('reports.destroy', deleteModal.value.report.slug), {
    onSuccess: () => { deleteModal.value.show = false },
  })
}

const bulkDelete = () => {
  if (window.confirm(`Delete ${selectedIds.value.length} reports? They'll go to Trash.`)) {
    // Sequentially delete — ideally backend has a bulk endpoint
    const promises = selectedIds.value.map(id => {
      const report = props.reports.data.find(r => r.id === id)
      if (!report) return Promise.resolve()
      return axios.delete(route('reports.destroy', report.slug))
    })
    Promise.all(promises).then(() => {
      selectedIds.value = []
      router.reload({ only: ['reports', 'stats'] })
    })
  }
}

const duplicateReport = (report) => {
  router.post(route('reports.duplicate', report.slug))
}

const changeStatus = (report, status) => {
  statusMenu.value.show = false
  
  // If status is 'trashed', delete the report
  if (status === 'trashed') {
    confirmDelete(report)
    return
  }
  
  router.patch(route('reports.status', report.slug), { status }, {
    preserveState: true,
    onSuccess: () => window.showToast?.(`Status changed to ${status}`, 'success'),
  })
}

const quickChangeStatus = (report) => {
  const cycle = { draft: 'published', published: 'archived', archived: 'draft' }
  changeStatus(report, cycle[report.status] || 'draft')
}

// ── Share ────────────────────────────────────────────────────────
const openShareModal = async (report) => {
  shareModal.value = { show: true, report, link: '', isPublic: report.is_public, loading: true }
  try {
    const { data } = await axios.post(route('reports.share', report.slug))
    shareModal.value.link     = data.url
    shareModal.value.isPublic = true
    shareModal.value.loading  = false
  } catch {
    shareModal.value.loading = false
  }
}

const togglePublicAccess = async () => {
  if (shareModal.value.isPublic) {
    await revokeLink()
  } else {
    await openShareModal(shareModal.value.report)
  }
}

const revokeLink = async () => {
  await axios.delete(route('reports.share.revoke', shareModal.value.report.slug))
  shareModal.value.link     = ''
  shareModal.value.isPublic = false
}

const copyLink = async () => {
  await navigator.clipboard.writeText(shareModal.value.link)
  linkCopied.value = true
  setTimeout(() => { linkCopied.value = false }, 2000)
}

const sendEmail = () => {
  window.open(`mailto:?subject=Check out this report&body=${encodeURIComponent(shareModal.value.link)}`)
}

// ── Export ────────────────────────────────────────────────────────
const openExportMenu = (report) => {
  exportMenu.value = { show: true, report }
}

// ── Versions ─────────────────────────────────────────────────────
const openVersions = async (report) => {
  versionsModal.value = { show: true, report, versions: [], loading: true }
  try {
    const { data } = await axios.get(route('reports.versions', report.slug))
    versionsModal.value.versions = data.versions
    versionsModal.value.loading  = false
  } catch {
    versionsModal.value.loading = false
  }
}

const restoreVersion = async (version) => {
  if (!confirm(`Restore version ${version.version_number}?`)) return
  await axios.post(route('reports.versions.restore', { report: versionsModal.value.report.slug, version: version.id }))
  versionsModal.value.show = false
  router.reload({ only: ['reports'] })
  window.showToast?.('Version restored!', 'success')
}

// ── Status quick menu ─────────────────────────────────────────────
const openStatusMenu = (report) => {
  statusMenu.value = { show: true, report }
}

// ── Keyboard shortcuts ────────────────────────────────────────────
const handleKeydown = (e) => {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return
  if (e.key === 'n' || e.key === 'N') router.visit(route('reports.create'))
  if (e.key === 'Escape') {
    deleteModal.value.show   = false
    shareModal.value.show    = false
    exportMenu.value.show    = false
    versionsModal.value.show = false
    statusMenu.value.show    = false
  }
  if (e.key === '1') viewMode.value = 'grid'
  if (e.key === '2') viewMode.value = 'list'
  if (e.key === '3') viewMode.value = 'timeline'
}

// ── Lifecycle ─────────────────────────────────────────────────────
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  // Count-up animation on mount
  Object.keys(animatedStats).forEach(k => {
    const target = k === 'total' ? (props.stats?.total || 0) : (props.stats?.[k] || 0)
    countUp(k, target)
  })
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

watch(() => props.stats, (newStats) => {
  if (!newStats) return
  Object.keys(animatedStats).forEach(k => {
    const target = k === 'total' ? (newStats.total || 0) : (newStats[k] || 0)
    countUp(k, target, 400)
  })
}, { deep: true })
</script>

<!-- ══════════════════════════════════════════════════════════════
     EMPTY STATE (inline component)
══════════════════════════════════════════════════════════════ -->
<script>
// Inline sub-component
export const EmptyState = {
  emits: ['create'],
  template: `
    <div class="flex flex-col items-center justify-center py-16 text-center">
      <div class="relative w-20 h-20 rounded-3xl bg-gradient-to-br from-violet-50 to-fuchsia-50 dark:from-violet-900/20 dark:to-fuchsia-900/20 border border-violet-100 dark:border-violet-800 flex items-center justify-center mb-5 shadow-inner">
        <i class="fa-solid fa-file-lines text-2xl text-violet-300 dark:text-violet-600"></i>
        <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-violet-500 flex items-center justify-center shadow-lg">
          <i class="fa-solid fa-plus text-white text-[8px]"></i>
        </div>
      </div>
      <h3 class="text-base font-black text-slate-900 dark:text-white mb-1">No reports found</h3>
      <p class="text-xs text-slate-400 max-w-xs mb-6">Start building beautiful reports with our drag-and-drop editor.</p>
      <button @click="$emit('create')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-violet-500/30 hover:-translate-y-0.5 hover:shadow-xl transition-all">
        <i class="fa-solid fa-plus"></i> Create Your First Report
      </button>
    </div>
  `
}
</script>

<style scoped>
/* ── Card pop transition ── */
.card-pop-enter-active  { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.card-pop-enter-from    { opacity: 0; transform: translateY(20px) scale(0.96); }
.card-pop-leave-active  { transition: all 0.2s ease; }
.card-pop-leave-to      { opacity: 0; transform: scale(0.95); }

/* ── List row ── */
.list-row-enter-active { transition: all 0.25s ease; }
.list-row-enter-from   { opacity: 0; transform: translateX(-12px); }
.list-row-leave-active { transition: all 0.2s ease; }
.list-row-leave-to     { opacity: 0; }

/* ── Timeline ── */
.timeline-item-enter-active { transition: all 0.35s cubic-bezier(0.34, 1.3, 0.64, 1); }
.timeline-item-enter-from   { opacity: 0; transform: translateX(-16px); }

/* ── Modal ── */
.modal-enter-active { transition: all 0.2s cubic-bezier(0.34, 1.3, 0.64, 1); }
.modal-enter-from   { opacity: 0; }
.modal-leave-active { transition: all 0.15s ease; }
.modal-leave-to     { opacity: 0; }

/* ── Slide down ── */
.slide-down-enter-active { transition: all 0.25s ease; }
.slide-down-enter-from   { opacity: 0; transform: translateY(-8px); max-height: 0; }
.slide-down-enter-to     { max-height: 200px; }
.slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-leave-to     { opacity: 0; transform: translateY(-8px); }
</style>