<!-- resources/js/Pages/Tasks/MyTasks.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
          <h2
            class="text-xl sm:text-2xl font-bold tracking-tight bg-gradient-to-r from-violet-500 via-fuchsia-500 to-pink-500 bg-clip-text text-transparent">
            My Tasks
          </h2>
          <p class="text-xs sm:text-sm text-slate-400 mt-0.5 font-light tracking-wide">
            Manage Assigned Tasks and Track Progress
          </p>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
          <!-- View Toggle -->
          <div
            class="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1">
            <button v-for="mode in viewModes" :key="mode.key" @click="setViewMode(mode.key)" :class="[
              'p-2 rounded-lg transition-all duration-200 text-xs',
              viewMode === mode.key
                ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-400 scale-105'
                : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
            ]" :title="mode.label">
              <i :class="mode.icon" class="text-sm"></i>
            </button>
          </div>

          <!-- Export button — opens CSV download in new tab, no axios/fetch -->
          <button @click="exportTasks" :disabled="isExporting"
            class="group flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-700 hover:text-violet-600 dark:hover:text-violet-400 disabled:opacity-60 disabled:cursor-not-allowed transition-all text-xs font-medium">
            <i
              :class="isExporting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-file-csv group-hover:translate-y-0.5 transition-transform'">
            </i>
            <span class="hidden sm:inline">{{ isExporting ? 'Exporting…' : 'Export CSV' }}</span>
          </button>
        </div>
      </div>
    </template>

    <div class="py-5 sm:py-8 px-3 sm:px-4 lg:px-6 space-y-5 sm:space-y-7">

      <!-- Stats Cards (5 columns) -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div v-for="stat in statCards" :key="stat.key" @click="filterByStatus(stat.key)" :class="[
          'group relative overflow-hidden rounded-2xl border p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5',
          filters.status === stat.key
            ? `border-${stat.color}-400 dark:border-${stat.color}-600 bg-${stat.color}-50 dark:bg-${stat.color}-900/20 shadow-lg`
            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'
        ]">
          <div
            :class="`absolute -top-6 -right-6 w-20 h-20 rounded-full bg-${stat.color}-400/10 blur-2xl group-hover:scale-150 transition-transform duration-500`">
          </div>
          <div class="relative flex items-start justify-between">
            <div>
              <p
                class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium tracking-wide uppercase mb-1">
                {{ stat.label }}
              </p>
              <p
                :class="`text-2xl sm:text-3xl font-black text-${stat.color}-600 dark:text-${stat.color}-400 tabular-nums`">
                {{ stats?.[stat.key] ?? 0 }}
              </p>
            </div>
            <div
              :class="`w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-${stat.color}-100 dark:bg-${stat.color}-900/40 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`">
              <i :class="`${stat.icon} text-${stat.color}-600 dark:text-${stat.color}-400 text-base sm:text-lg`"></i>
            </div>
          </div>
          <div
            :class="`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-${stat.color}-400 to-${stat.color}-600 transition-all duration-300`"
            :style="{ width: filters.status === stat.key ? '100%' : '0%' }">
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
          <!-- Search -->
          <div class="relative flex-1 min-w-[160px]">
            <i
              class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input v-model="filters.search" type="text" placeholder="Search tasks…" @input="debouncedSearch"
              class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition" />
          </div>

          <select v-model="filters.status" @change="applyFilters" class="filter-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="overdue">Overdue</option>
            <option value="trashed">Trash</option>
          </select>

          <select v-model="filters.priority" @change="applyFilters" class="filter-select">
            <option value="">All Priority</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>

          <select v-model="filters.sort" @change="applyFilters" class="filter-select hidden sm:block">
            <option value="due_date_asc">Due: Earliest</option>
            <option value="due_date_desc">Due: Latest</option>
            <option value="priority">Priority</option>
            <option value="created_at_desc">Newest</option>
            <option value="created_at_asc">Oldest</option>
          </select>

          <button @click="applyFilters"
            class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-violet-200 dark:shadow-violet-900/30">
            Apply
          </button>

          <button v-if="hasActiveFilters" @click="resetFilters"
            class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-xs transition-all">
            <i class="fa-solid fa-xmark mr-1"></i>Reset
          </button>

          <!-- Bulk actions -->
          <template v-if="selectedIds.length">
            <template v-if="filters.status !== 'trashed'">
              <button @click="confirmBulkDelete"
                class="ml-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <i class="fa-solid fa-trash-can mr-1"></i>Move {{ selectedIds.length }} to Trash
              </button>
            </template>
            <template v-else>
              <div class="ml-auto flex items-center gap-2">
                <button @click="bulkRestoreSelected"
                  class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition-all">
                  <i class="fa-solid fa-rotate-left mr-1"></i>Restore {{ selectedIds.length }}
                </button>
                <button @click="bulkForceDeleteSelected"
                  class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold transition-all">
                  <i class="fa-solid fa-trash-can mr-1"></i>Delete Forever
                </button>
              </div>
            </template>
          </template>
        </div>
      </div>

      <!-- ═══════════════════ GRID VIEW ═══════════════════ -->
      <template v-if="viewMode === 'grid'">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
          <TransitionGroup name="task-card" appear>
            <div v-for="task in tasks.data" :key="task.id"
              class="group relative bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden hover:shadow-2xl hover:shadow-slate-200/60 dark:hover:shadow-slate-900/60 hover:-translate-y-1 transition-all duration-300">

              <div class="absolute top-4 right-4 z-10">
                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-violet-600"
                  :checked="selectedIds.includes(task.id)" @change="() => toggleTaskSelection(task)" />
              </div>

              <div :class="`absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r ${priorityGradient(task.priority)}`">
              </div>

              <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2 mb-3">
                  <div class="flex items-center gap-2 min-w-0">
                    <div :class="`shrink-0 w-2 h-2 rounded-full ${priorityDot(task.priority)}`"></div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm leading-tight line-clamp-2">
                      {{ task.title }}
                    </h3>
                  </div>
                  <span
                    :class="`shrink-0 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wide ${statusBadge(task.status)}`">
                    {{ task.status.replace('_', ' ') }}
                  </span>
                </div>

                <p class="text-xs text-slate-400 dark:text-slate-500 line-clamp-2 mb-4 leading-relaxed">
                  {{ task.description || 'No description provided.' }}
                </p>

                <!-- Progress -->
                <div class="mb-4">
                  <div class="flex justify-between text-[10px] text-slate-400 mb-1.5">
                    <span>Progress</span>
                    <span class="font-semibold">{{ taskProgress(task) }}%</span>
                  </div>
                  <div class="relative w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-700 ease-out"
                      :class="progressColor(task.status)" :style="{ width: `${taskProgress(task)}%` }">
                    </div>
                  </div>
                </div>

                <!-- Meta row -->
                <div class="flex flex-col gap-1 mb-3">
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Priority:</span>
                    <span
                      :class="`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide ${priorityBadge(task.priority)}`">
                      {{ task.priority }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Due:</span>
                    <div class="flex items-center gap-1.5 text-[11px]"
                      :class="isOverdue(task.due_date, task.status) && task.status !== 'completed' ? 'text-red-500 font-semibold' : 'text-slate-400'">
                      <span>{{ task.due_date ? formatDateTime(task.due_date) : 'No due date' }}</span>
                      <span v-if="isOverdue(task.due_date, task.status) && task.status !== 'completed'"
                        class="text-[9px] bg-red-100 dark:bg-red-900/30 text-red-600 px-1 rounded">
                        LATE
                      </span>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Assigned by:</span>
                    <span class="text-[11px] text-slate-500">{{ task.assigned_by || '—' }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Created:</span>
                    <span class="text-[11px] text-slate-500">{{ task.created_at ? formatDateTime(task.created_at) : '—'
                      }}</span>
                  </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
                  <div>
                    <template v-if="task.status === 'trashed'">
                      <span class="text-[11px] font-semibold text-red-600 dark:text-red-400">Trashed</span>
                    </template>
                    <template v-else>
                      <select :value="task.status === 'overdue' ? 'pending' : task.status"
                        @change="e => handleStatusChange(task, e.target.value)"
                        class="text-[11px] border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-violet-500 transition">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                      </select>
                    </template>
                  </div>
                  <div class="flex items-center gap-1">
                    <template v-if="task.status === 'trashed'">
                      <button @click="restoreTask(task)"
                        class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition-colors"
                        title="Restore">
                        <i class="fa-solid fa-rotate-left text-sm"></i>
                      </button>
                      <button @click="forceDeleteTask(task)"
                        class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition-colors"
                        title="Delete Forever">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                      </button>
                    </template>
                    <template v-else>
                      <button @click="openTaskDetails(task)"
                        class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/30 text-slate-400 hover:text-violet-500 transition-colors"
                        title="View Details">
                        <i class="fa-regular fa-eye text-sm"></i>
                      </button>
                      <Link v-if="task.report" :href="route('reports.edit', task.report.slug)"
                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-400 hover:text-blue-500 transition-colors"
                        title="View Report">
                        <i class="fa-solid fa-file-lines text-sm"></i>
                      </Link>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>

        <!-- Empty State -->
        <div v-if="!tasks.data?.length" class="flex flex-col items-center justify-center py-16 text-center">
          <div
            class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center mb-4 shadow-inner">
            <i class="fa-solid fa-list-check text-2xl text-slate-300 dark:text-slate-600"></i>
          </div>
          <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No tasks found</h3>
          <p class="text-xs text-slate-400 max-w-xs">No tasks match your current filters.</p>
          <button @click="resetFilters"
            class="mt-4 px-5 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-semibold transition-all">
            Clear Filters
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="tasks.data?.length" class="flex justify-center">
          <Pagination :links="tasks.links" />
        </div>
      </template>

      <!-- ═══════════════════ LIST VIEW ═══════════════════ -->
      <template v-else-if="viewMode === 'list'">
        <div
          class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                  <th
                    class="px-4 sm:px-6 py-3 text-left text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    <input type="checkbox" class="w-4 h-4 rounded border-slate-300" :checked="isAllSelected"
                      @change="toggleSelectAll" />
                  </th>
                  <th
                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Task</th>
                  <th
                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Priority</th>
                  <th
                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Status</th>
                  <th
                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                    Assigned By</th>
                  <th
                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                    Due Date</th>
                  <th
                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                    Created</th>
                  <th
                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">
                    Progress</th>
                  <th
                    class="text-right px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                <TransitionGroup name="list-row" appear>
                  <tr v-for="task in tasks.data" :key="task.id"
                    class="group hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 sm:px-6 py-3.5">
                      <input type="checkbox" class="w-4 h-4 rounded border-slate-300"
                        :checked="selectedIds.includes(task.id)" @change="() => toggleTaskSelection(task)" />
                    </td>
                    <td class="px-4 sm:px-6 py-3.5">
                      <div class="flex items-start gap-2.5">
                        <div :class="`shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full ${priorityDot(task.priority)}`"></div>
                        <div>
                          <p class="font-semibold text-xs text-slate-900 dark:text-white line-clamp-1">{{ task.title }}
                          </p>
                          <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ task.description }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 sm:px-6 py-3.5">
                      <span
                        :class="`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide ${priorityBadge(task.priority)}`">
                        {{ task.priority }}
                      </span>
                    </td>
                    <td class="px-4 sm:px-6 py-3.5">
                      <template v-if="task.status === 'trashed'">
                        <span class="text-[11px] font-semibold text-red-600 dark:text-red-400">Trashed</span>
                      </template>
                      <template v-else>
                        <select :value="task.status === 'overdue' ? 'pending' : task.status"
                          @change="e => handleStatusChange(task, e.target.value)"
                          class="text-[11px] border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-violet-500">
                          <option value="pending">Pending</option>
                          <option value="in_progress">In Progress</option>
                          <option value="completed">Completed</option>
                        </select>
                      </template>
                    </td>
                    <td class="px-4 sm:px-6 py-3.5 hidden sm:table-cell">
                      <span class="text-xs text-slate-600 dark:text-slate-400">{{ task.assigned_by || '—' }}</span>
                    </td>
                    <td class="px-4 sm:px-6 py-3.5 hidden sm:table-cell">
                      <span
                        :class="['text-xs sm:text-sm', isOverdue(task.due_date, task.status) && task.status !== 'completed' ? 'text-red-500 font-bold' : 'text-slate-900 dark:text-white']">
                        {{ task.due_date ? formatDateTime(task.due_date) : '—' }}
                      </span>
                    </td>
                    <td class="px-4 sm:px-6 py-3.5 hidden sm:table-cell">
                      <span class="text-xs sm:text-sm text-slate-900 dark:text-white">
                        {{ task.created_at ? formatDateTime(task.created_at) : '—' }}
                      </span>
                    </td>
                    <td class="px-4 sm:px-6 py-3.5 hidden md:table-cell">
                      <div class="flex items-center gap-2">
                        <div class="w-20 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                          <div class="h-full rounded-full transition-all duration-500"
                            :class="progressColor(task.status)" :style="{ width: `${taskProgress(task)}%` }">
                          </div>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ taskProgress(task) }}%</span>
                      </div>
                    </td>
                    <td class="px-4 sm:px-6 py-3.5 text-right">
                      <div class="flex items-center justify-end gap-1">
                        <template v-if="task.status === 'trashed'">
                          <button @click="restoreTask(task)"
                            class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition-colors"
                            title="Restore">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                          </button>
                          <button @click="forceDeleteTask(task)"
                            class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition-colors"
                            title="Delete Forever">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                          </button>
                        </template>
                        <template v-else>
                          <button @click="openTaskDetails(task)"
                            class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/30 text-slate-400 hover:text-violet-500 transition-colors">
                            <i class="fa-regular fa-eye text-xs"></i>
                          </button>
                          <Link v-if="task.report" :href="route('reports.edit', task.report.slug)"
                            class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-400 hover:text-blue-500 transition-colors">
                            <i class="fa-solid fa-external-link text-xs"></i>
                          </Link>
                        </template>
                      </div>
                    </td>
                  </tr>
                </TransitionGroup>

                <tr v-if="!tasks.data?.length">
                  <td colspan="9" class="py-16 text-center text-slate-400 text-sm">No tasks found</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="px-4 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-700">
            <Pagination :links="tasks.links" />
          </div>
        </div>
      </template>

      <!-- ═══════════════════ KANBAN VIEW ═══════════════════ -->
      <template v-else>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
          <div v-for="col in kanbanColumns" :key="col.key" :class="`rounded-2xl border p-3 sm:p-4 ${col.bg}`">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-2">
                <div :class="`w-2 h-2 rounded-full ${col.dot}`"></div>
                <h3 :class="`font-bold text-sm ${col.text}`">{{ col.label }}</h3>
              </div>
              <span :class="`text-[10px] font-bold px-2 py-0.5 rounded-full ${col.badge}`">
                {{ tasksByStatus(col.key).length }}
              </span>
            </div>
            <div class="space-y-2.5">
              <TransitionGroup name="kanban-card" appear>
                <div v-for="task in tasksByStatus(col.key)" :key="task.id"
                  :class="['bg-white dark:bg-slate-800 rounded-xl p-3 shadow-sm border border-white/60 dark:border-slate-700 cursor-pointer hover:shadow-md transition-all duration-200 hover:-translate-y-0.5', col.key === 'overdue' ? 'border-l-4 border-l-red-500' : '']"
                  draggable="true" @dragstart="dragStart(task)">
                  <p class="font-semibold text-xs text-slate-900 dark:text-white leading-snug"
                    :class="col.key === 'completed' ? 'line-through opacity-60' : ''">
                    {{ task.title }}
                  </p>
                  <div class="flex items-center justify-between mt-2.5 gap-1 flex-wrap">
                    <span
                      :class="`text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase ${priorityBadge(task.priority)}`">
                      {{ task.priority }}
                    </span>
                    <span v-if="col.key === 'overdue'" class="text-[10px] text-red-500 font-semibold">
                      {{ getOverdueDays(task.due_date) }}d overdue
                    </span>
                    <span v-else-if="col.key === 'completed'" class="text-[10px] text-emerald-500 font-medium">
                      Done ✓
                    </span>
                    <select v-else-if="col.key === 'in_progress'" :value="task.status"
                      @change="e => handleStatusChange(task, e.target.value)"
                      class="text-[10px] border border-slate-200 dark:border-slate-600 rounded px-1.5 py-0.5 bg-white dark:bg-slate-700 focus:outline-none focus:ring-1 focus:ring-violet-500"
                      @click.stop>
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                    </select>
                    <button @click="openTaskDetails(task)"
                      class="text-[10px] text-violet-500 hover:text-violet-700 font-medium transition-colors ml-auto">
                      Details →
                    </button>
                  </div>
                </div>
              </TransitionGroup>
              <div v-if="!tasksByStatus(col.key).length" class="text-center py-6">
                <i :class="`${col.icon} text-2xl opacity-20 ${col.text}`"></i>
                <p class="text-[10px] text-slate-400 mt-1">No tasks</p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- ═══════════════════ TASK DETAILS MODAL ═══════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="showDetailsModal = false"></div>
          <div
            class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-lg max-h-[92vh] overflow-hidden flex flex-col">
            <div
              class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
              <div class="flex items-center gap-2.5">
                <div :class="`w-2.5 h-2.5 rounded-full ${priorityDot(selectedTask?.priority)}`"></div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Task Details</h3>
              </div>
              <button @click="showDetailsModal = false"
                class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
              </button>
            </div>
            <div class="overflow-y-auto p-5 space-y-5 flex-1">
              <div>
                <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Title</label>
                <p class="mt-1 font-semibold text-slate-900 dark:text-white text-sm">{{ selectedTask?.title }}</p>
              </div>
              <div>
                <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Description</label>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                  {{ selectedTask?.description || 'No description provided.' }}
                </p>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Priority</label>
                  <div class="mt-1">
                    <span
                      :class="`text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide ${priorityBadge(selectedTask?.priority)}`">
                      {{ selectedTask?.priority }}
                    </span>
                  </div>
                </div>
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Status</label>
                  <div class="mt-1">
                    <select v-if="selectedTask?.status !== 'trashed'" v-model="selectedTask.status"
                      @change="updateStatus(selectedTask)"
                      class="text-xs border border-slate-200 dark:border-slate-600 rounded-lg px-2.5 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500 w-full">
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                    </select>
                    <span v-else class="text-[11px] font-semibold text-red-600 dark:text-red-400">Trashed</span>
                  </div>
                </div>
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Due Date</label>
                  <p class="mt-1 text-xs"
                    :class="isOverdue(selectedTask?.due_date, selectedTask?.status) && selectedTask?.status !== 'completed' ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-400'">
                    {{ selectedTask?.due_date ? formatDateTime(selectedTask.due_date) : 'No due date' }}
                  </p>
                </div>
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Created</label>
                  <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                    {{ selectedTask?.created_at ? formatDateTime(selectedTask.created_at) : '—' }}
                  </p>
                </div>

                <div v-if="selectedTask?.completed_at">
                  <label class="text-[10px] uppercase tracking-wider text-emerald-500 font-semibold">Completed
                    At</label>
                  <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                    {{ formatDateTime(selectedTask.completed_at) }}
                  </p>
                </div>

                <div v-if="selectedTask?.assigned_by">
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Assigned By</label>
                  <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ selectedTask.assigned_by }}</p>
                </div>
              </div>

              <!-- Completion Notes -->
              <div v-if="selectedTask?.status === 'completed'"
                class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-3">
                <label
                  class="text-[10px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400 font-semibold">
                  <i class="fa-solid fa-circle-check mr-1"></i>Completion Notes
                </label>
                <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300 italic leading-relaxed">
                  {{ selectedTask?.completion_notes || 'No completion notes provided.' }}
                </p>
              </div>

              <!-- Related Report -->
              <div v-if="selectedTask?.report"
                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-3">
                <label class="text-[10px] uppercase tracking-wider text-blue-600 dark:text-blue-400 font-semibold">
                  Related Report
                </label>
                <div class="flex items-center justify-between mt-1">
                  <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">{{ selectedTask.report.title }}</p>
                  <Link :href="route('reports.edit', selectedTask.report.slug)"
                    class="text-[10px] text-blue-500 hover:text-blue-700 font-semibold transition-colors">
                    View →
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═══════════════════ COMPLETION NOTES MODAL ═══════════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showNotesModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="cancelCompletion"></div>
          <div
            class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-5 sm:p-6">
              <div class="flex items-center gap-2.5 mb-1">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                  <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Mark as Completed</h3>
              </div>
              <p class="text-xs text-slate-400 mb-4 ml-10">Add optional notes about what was accomplished.</p>
              <textarea v-model="completionNotes" rows="4"
                class="w-full px-4 py-3 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none transition"
                placeholder="What did you accomplish? Any blockers? Notes for the team…">
          </textarea>
              <div class="flex gap-2.5 mt-4">
                <button @click="cancelCompletion"
                  class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-all">
                  Cancel
                </button>
                <button @click="submitCompletionNotes" :disabled="isSubmitting"
                  class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-emerald-200 dark:shadow-emerald-900/30">
                  <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin mr-1"></i>
                  Complete Task ✓
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═══════════════════ CONFIRM MODAL ═══════════════════ -->
    <ConfirmationModal :show="confirmModal.show" :title="confirmModal.title" :message="confirmModal.message"
      :confirm-text="confirmModal.confirmText" :icon="confirmModal.icon" @close="confirmModal.show = false"
      @confirm="confirmModal.onConfirm" />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

// Use the globally configured axios instance which has CSRF headers configured
const axios = window.axios

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  tasks: { type: Object, required: true },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

// ── State ──────────────────────────────────────────────────────────────────
const STORAGE_KEY = 'mytasks_view_mode'

const viewMode = ref(localStorage.getItem(STORAGE_KEY) || 'grid')
const showDetailsModal = ref(false)
const showNotesModal = ref(false)
const selectedTask = ref(null)
const currentTask = ref(null)
const completionNotes = ref('')
const selectedIds = ref([])
const isSubmitting = ref(false)
const isExporting = ref(false)   // ← export loading state

const confirmModal = reactive({
  show: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  icon: 'fa-solid fa-question',
  onConfirm: () => { },
})

const isAllSelected = computed(() =>
  props.tasks.data?.length > 0 && props.tasks.data.every(t => selectedIds.value.includes(t.id))
)

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  priority: props.filters?.priority || '',
  sort: props.filters?.sort || 'due_date_asc',
})

// ── Static config ──────────────────────────────────────────────────────────
const viewModes = [
  { key: 'grid', icon: 'fa-solid fa-grip', label: 'Grid' },
  { key: 'list', icon: 'fa-solid fa-list', label: 'List' },
  { key: 'kanban', icon: 'fa-solid fa-chart-simple', label: 'Kanban' },
]

const statCards = [
  { key: 'pending', label: 'Pending', icon: 'fa-solid fa-clock', color: 'amber' },
  { key: 'in_progress', label: 'In Progress', icon: 'fa-solid fa-spinner', color: 'blue' },
  { key: 'completed', label: 'Completed', icon: 'fa-solid fa-check-circle', color: 'emerald' },
  { key: 'overdue', label: 'Overdue', icon: 'fa-solid fa-circle-exclamation', color: 'red' },
  { key: 'trashed', label: 'Trash', icon: 'fa-solid fa-trash-can', color: 'slate' },
]

const kanbanColumns = [
  { key: 'pending', label: 'Pending', icon: 'fa-solid fa-clock', dot: 'bg-amber-400', text: 'text-amber-700 dark:text-amber-400', bg: 'bg-amber-50/70 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30', badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' },
  { key: 'in_progress', label: 'In Progress', icon: 'fa-solid fa-spinner', dot: 'bg-blue-400', text: 'text-blue-700 dark:text-blue-400', bg: 'bg-blue-50/70 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30', badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' },
  { key: 'completed', label: 'Completed', icon: 'fa-solid fa-check-circle', dot: 'bg-emerald-400', text: 'text-emerald-700 dark:text-emerald-400', bg: 'bg-emerald-50/70 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30', badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' },
  { key: 'overdue', label: 'Overdue', icon: 'fa-solid fa-circle-exclamation', dot: 'bg-red-400', text: 'text-red-700 dark:text-red-400', bg: 'bg-red-50/70 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30', badge: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' },
]

// ── Computed ───────────────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  filters.search || filters.status || filters.priority || filters.sort !== 'due_date_asc'
)

// ── Pure helpers ───────────────────────────────────────────────────────────
const priorityDot = p => ({
  low: 'bg-blue-400', medium: 'bg-green-400', high: 'bg-orange-400', urgent: 'bg-red-500'
}[p] ?? 'bg-slate-400')

const priorityBadge = p => ({
  low: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  medium: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
  urgent: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}[p] ?? 'bg-slate-100 text-slate-600')

const statusBadge = s => ({
  pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  overdue: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
  trashed: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}[s] ?? 'bg-slate-100 text-slate-600')

const progressColor = s => ({
  pending: 'bg-gradient-to-r from-amber-400 to-amber-500',
  in_progress: 'bg-gradient-to-r from-blue-400 to-violet-500',
  completed: 'bg-gradient-to-r from-emerald-400 to-emerald-500',
  overdue: 'bg-gradient-to-r from-red-400 to-red-500',
  trashed: 'bg-gradient-to-r from-red-400 to-red-500',
}[s] ?? 'bg-slate-400')

const priorityGradient = p => ({
  low: 'from-blue-300 to-blue-500',
  medium: 'from-green-300 to-green-500',
  high: 'from-orange-300 to-orange-500',
  urgent: 'from-red-400 to-pink-500',
}[p] ?? 'from-slate-300 to-slate-400')

const taskProgress = task =>
  task.status === 'completed' ? 100 :
    task.status === 'in_progress' ? 55 :
      task.status === 'overdue' ? 15 :
        task.status === 'trashed' ? 0 : 10

const tasksByStatus = status => {
  if (!props.tasks.data) return []
  if (status === 'trashed') return props.tasks.data.filter(t => t.status === 'trashed')
  if (status === 'overdue') return props.tasks.data.filter(t =>
    t.status === 'overdue' || (t.status !== 'completed' && t.due_date && new Date(t.due_date) < new Date()))
  if (status === 'pending') return props.tasks.data.filter(t =>
    t.status === 'pending' && !(t.due_date && new Date(t.due_date) < new Date()))
  if (status === 'in_progress') return props.tasks.data.filter(t =>
    t.status === 'in_progress' && !(t.due_date && new Date(t.due_date) < new Date()))
  return props.tasks.data.filter(t => t.status === status)
}

const getOverdueDays = dueDate =>
  dueDate ? Math.max(0, Math.floor((Date.now() - new Date(dueDate)) / 86400000)) : 0

const formatDateTime = date =>
  date
    ? new Date(date).toLocaleString('en-GB', {
      day: 'numeric', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    })
    : '—'

const isOverdue = (date, status) =>
  !!date && new Date(date) < new Date() && status !== 'completed'

// ── Navigation & filter actions ────────────────────────────────────────────
const setViewMode = mode => {
  viewMode.value = mode
  localStorage.setItem(STORAGE_KEY, mode)
}

const applyFilters = () => {
  selectedIds.value = []
  router.get(route('my-tasks.index'), filters, { preserveState: true, replace: true })
}

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  filters.priority = ''
  filters.sort = 'due_date_asc'
  selectedIds.value = []
  applyFilters()
}

const filterByStatus = status => {
  filters.status = filters.status === status ? '' : status
  applyFilters()
}

// ── EXPORT (no axios / fetch — pure browser navigation) ───────────────────
/**
 * Builds the export URL from the dedicated my-tasks.export route and the
 * current active filters, then opens it in a new tab so the browser
 * triggers the file download without any JS fetch / axios calls.
 *
 * The server streams the CSV with the correct Content-Disposition header,
 * so the browser downloads it directly.
 */
const exportTasks = () => {
  if (isExporting.value) return

  isExporting.value = true

  // Build query params from current reactive filters
  const params = new URLSearchParams({
    search: filters.search ?? '',
    status: filters.status ?? '',
    priority: filters.priority ?? '',
    sort: (typeof filters.sort === 'string' ? filters.sort : '') || 'due_date_asc',
  })

  // Remove empty params so the URL stays clean
  for (const [key, value] of [...params.entries()]) {
    if (!value) params.delete(key)
  }

  const exportUrl = route('my-tasks.export') + (params.toString() ? '?' + params.toString() : '')

  // Open in new tab — browser handles the download automatically
  window.open(exportUrl, '_blank', 'noopener,noreferrer')

  // Reset loading state after a short delay (download triggers instantly)
  setTimeout(() => { isExporting.value = false }, 1500)
}

// ── Status update ──────────────────────────────────────────────────────────
const updateStatus = task => {
  if (task.status === 'completed') {
    currentTask.value = task
    showNotesModal.value = true
  } else {
    router.patch(
      route('admin.tasks.status', task.id),
      { status: task.status },
      {
        preserveState: false,
        onSuccess: () => window.showToast?.('Status updated successfully', 'success'),
        onError: () => window.showToast?.('Failed to update status', 'error'),
      }
    )
  }
}

const handleStatusChange = (task, newStatus) => {
  task.status = newStatus
  if (selectedTask.value?.id === task.id) selectedTask.value.status = newStatus
  updateStatus(task)
}

const submitCompletionNotes = () => {
  if (isSubmitting.value) return
  isSubmitting.value = true

  router.patch(
    route('admin.tasks.status', currentTask.value.id),
    { status: 'completed', completion_notes: completionNotes.value },
    {
      preserveState: false,
      onSuccess: () => {
        showNotesModal.value = false
        if (selectedTask.value?.id === currentTask.value.id) {
          selectedTask.value.status = 'completed'
          selectedTask.value.completion_notes = completionNotes.value
        }
        completionNotes.value = ''
        currentTask.value = null
        window.showToast?.('Task completed! Great job! 🎉', 'success')
        applyFilters()
      },
      onError: () => window.showToast?.('Failed to complete task', 'error'),
      onFinish: () => { isSubmitting.value = false },
    }
  )
}

const cancelCompletion = () => {
  showNotesModal.value = false
  completionNotes.value = ''
  currentTask.value = null
}

// ── Details modal ──────────────────────────────────────────────────────────
const openTaskDetails = task => {
  selectedTask.value = { ...task }
  showDetailsModal.value = true
}

// ── Selection ──────────────────────────────────────────────────────────────
const toggleTaskSelection = task => {
  const idx = selectedIds.value.indexOf(task.id)
  idx === -1 ? selectedIds.value.push(task.id) : selectedIds.value.splice(idx, 1)
}

const toggleSelectAll = () => {
  isAllSelected.value
    ? (selectedIds.value = [])
    : (selectedIds.value = props.tasks.data?.map(t => t.id) || [])
}

// ── Confirm helper ─────────────────────────────────────────────────────────
const showConfirm = ({ title, message, confirmText, icon, onConfirm }) => {
  Object.assign(confirmModal, { show: true, title, message, confirmText, icon, onConfirm })
}

// ── Bulk delete ────────────────────────────────────────────────────────────
const confirmBulkDelete = () => {
  if (!selectedIds.value.length) return
  const count = selectedIds.value.length
  showConfirm({
    title: `Move ${count} task${count > 1 ? 's' : ''} to Trash?`,
    message: 'Selected tasks will be soft-deleted and can be restored later.',
    confirmText: 'Move to Trash',
    icon: 'fa-solid fa-trash',
    onConfirm: async () => {
      confirmModal.show = false
      try {
        await axios.post('/admin/tasks/bulk-delete', { task_ids: selectedIds.value })
        const n = selectedIds.value.length
        selectedIds.value = []
        applyFilters()
        window.showToast?.(`${n} task${n > 1 ? 's' : ''} moved to trash`, 'success')
      } catch (err) {
        window.showToast?.(err.response?.data?.message || 'Bulk delete failed', 'error')
      }
    },
  })
}

// ── Restore / force delete ─────────────────────────────────────────────────
const restoreTask = task => {
  showConfirm({
    title: `Restore "${task.title}"?`,
    message: 'This task will be restored and become active again.',
    confirmText: 'Restore',
    icon: 'fa-solid fa-rotate-left',
    onConfirm: async () => {
      confirmModal.show = false
      try {
        await axios.post(`/admin/tasks/${task.id}/restore`)
        applyFilters()
        window.showToast?.('Task restored successfully', 'success')
      } catch (err) {
        window.showToast?.(err.response?.data?.message || 'Restore failed', 'error')
      }
    },
  })
}

const forceDeleteTask = task => {
  showConfirm({
    title: `Permanently delete "${task.title}"?`,
    message: 'This action cannot be undone. The task will be removed forever.',
    confirmText: 'Delete Forever',
    icon: 'fa-solid fa-skull',
    onConfirm: async () => {
      confirmModal.show = false
      try {
        await axios.delete(`/admin/tasks/${task.id}/force`)
        applyFilters()
        window.showToast?.('Task permanently deleted', 'success')
      } catch (err) {
        window.showToast?.(err.response?.data?.message || 'Delete failed', 'error')
      }
    },
  })
}

const bulkRestoreSelected = () => {
  if (!selectedIds.value.length) return
  const count = selectedIds.value.length
  showConfirm({
    title: `Restore ${count} task${count > 1 ? 's' : ''}?`,
    message: 'All selected tasks will be restored.',
    confirmText: 'Restore All',
    icon: 'fa-solid fa-rotate-left',
    onConfirm: async () => {
      confirmModal.show = false
      try {
        await Promise.all(selectedIds.value.map(id => axios.post(`/admin/tasks/${id}/restore`)))
        window.showToast?.(`${count} task${count > 1 ? 's' : ''} restored`, 'success')
        selectedIds.value = []
        applyFilters()
      } catch (err) {
        window.showToast?.(err.response?.data?.message || 'Some restores failed', 'error')
      }
    },
  })
}

const bulkForceDeleteSelected = () => {
  if (!selectedIds.value.length) return
  const count = selectedIds.value.length
  showConfirm({
    title: `Permanently delete ${count} task${count > 1 ? 's' : ''}?`,
    message: 'This cannot be undone.',
    confirmText: 'Delete Forever',
    icon: 'fa-solid fa-skull',
    onConfirm: async () => {
      confirmModal.show = false
      try {
        await Promise.all(selectedIds.value.map(id => axios.delete(`/admin/tasks/${id}/force`)))
        window.showToast?.(`${count} task${count > 1 ? 's' : ''} permanently deleted`, 'success')
        selectedIds.value = []
        applyFilters()
      } catch (err) {
        window.showToast?.(err.response?.data?.message || 'Some deletes failed', 'error')
      }
    },
  })
}

// ── Debounced search ───────────────────────────────────────────────────────
let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 450)
}

// ── Kanban drag (UI only) ──────────────────────────────────────────────────
let draggedTask = null
const dragStart = task => { draggedTask = task }

// ── Lifecycle ──────────────────────────────────────────────────────────────
onMounted(() => {
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored && ['grid', 'list', 'kanban'].includes(stored)) viewMode.value = stored
})
</script>

<style scoped>
.filter-select {
  @apply px-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition;
}

/* Task card enter/leave */
.task-card-enter-active {
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.task-card-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(0.97);
}

.task-card-leave-active {
  transition: all 0.2s ease;
}

.task-card-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* List row enter/leave */
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

/* Kanban card enter/leave */
.kanban-card-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.4, 0.64, 1);
}

.kanban-card-enter-from {
  opacity: 0;
  transform: translateY(10px) scale(0.96);
}

.kanban-card-leave-active {
  transition: all 0.2s ease;
}

.kanban-card-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Modal enter/leave */
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
</style>