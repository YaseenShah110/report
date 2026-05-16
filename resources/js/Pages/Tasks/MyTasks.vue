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
          <p class="text-xs sm:text-sm text-slate-400 mt-0.5 font-light tracking-wide">Manage Assigned Tasks and Track
            Progress</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- View Toggle -->
          <div
            class="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1">
            <button v-for="mode in viewModes" :key="mode.key" @click="viewMode = mode.key" :class="[
              'p-2 rounded-lg transition-all duration-200 text-xs',
              viewMode === mode.key
                ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-400 scale-105'
                : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
            ]" :title="mode.label">
              <i :class="mode.icon" class="text-sm"></i>
            </button>
          </div>
          <button @click="exportTasks"
            class="group flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-700 hover:text-violet-600 dark:hover:text-violet-400 transition-all text-xs font-medium">
            <i class="fa-solid fa-download group-hover:translate-y-0.5 transition-transform"></i>
            <span class="hidden sm:inline">Export</span>
          </button>
        </div>
      </div>
    </template>

    <div class="py-5 sm:py-8 px-3 sm:px-4 lg:px-6 space-y-5 sm:space-y-7">

      <!-- ── Stats Cards ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div v-for="stat in statCards" :key="stat.key" @click="filterByStatus(stat.key)" :class="[
          'group relative overflow-hidden rounded-2xl border p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5',
          filters.status === stat.key
            ? `border-${stat.color}-400 dark:border-${stat.color}-600 bg-${stat.color}-50 dark:bg-${stat.color}-900/20 shadow-lg shadow-${stat.color}-100 dark:shadow-${stat.color}-900/20`
            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'
        ]">
          <!-- decorative glow -->
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
                {{ stats?.[stat.key] ?? 0 }}
              </p>
            </div>
            <div
              :class="`w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-${stat.color}-100 dark:bg-${stat.color}-900/40 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`">
              <i :class="`${stat.icon} text-${stat.color}-600 dark:text-${stat.color}-400 text-base sm:text-lg`"></i>
            </div>
          </div>

          <!-- bottom bar -->
          <div
            :class="`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-${stat.color}-400 to-${stat.color}-600 transition-all duration-300`"
            :style="{ width: filters.status === stat.key ? '100%' : '0%' }"></div>
        </div>
      </div>

      <!-- ── Filters ── -->
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
          <button v-if="selectedIds.length && filters.status !== 'trashed'" @click="confirmBulkDelete"
            class="ml-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-red-200 dark:shadow-red-900/30">
            <i class="fa-solid fa-trash-can mr-1"></i>
            Move {{ selectedIds.length }} selected to Trash
          </button>
          <div v-else-if="selectedIds.length && filters.status === 'trashed'" class="ml-auto flex items-center gap-2">
            <button @click="bulkRestoreSelected"
              class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm">
              <i class="fa-solid fa-rotate-left mr-1"></i>Restore {{ selectedIds.length }}
            </button>
            <button @click="bulkForceDeleteSelected"
              class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm">
              <i class="fa-solid fa-trash-can mr-1"></i>Delete Forever
            </button>
          </div>
        </div>
      </div>

      <!-- ── GRID VIEW ── -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
        <TransitionGroup name="task-card" appear>
          <div v-for="task in tasks.data" :key="task.id"
            class="group relative bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden hover:shadow-2xl hover:shadow-slate-200/60 dark:hover:shadow-slate-900/60 hover:-translate-y-1 transition-all duration-300">
            <div class="absolute top-4 right-4 z-10">
              <input type="checkbox" class="w-4 h-4 rounded border-slate-300 bg-white dark:bg-slate-800 text-violet-600"
                :checked="selectedIds.includes(task.id)" @change="() => toggleTaskSelection(task)" />
            </div>
            <!-- priority stripe -->
            <div :class="`absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r ${priorityGradient(task.priority)}`">
            </div>

            <div class="p-4 sm:p-5">
              <!-- Header -->
              <div class="flex items-start justify-between gap-2 mb-3">
                <div class="flex items-center gap-2 min-w-0">
                  <div :class="`shrink-0 w-2 h-2 rounded-full ${priorityDot(task.priority)}`"></div>
                  <h3 class="font-semibold text-slate-900 dark:text-white text-sm leading-tight line-clamp-2">{{
                    task.title
                    }}</h3>
                </div>
                <span
                  :class="`shrink-0 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wide ${statusBadge(task.status)}`">
                  {{ task.status.replace('_', ' ') }}
                </span>
              </div>

              <!-- Description -->
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
                    :class="progressColor(task.status)" :style="{ width: `${taskProgress(task)}%` }"></div>
                </div>
              </div>

              <!-- Meta row -->
              <div class="flex items-center justify-between mb-4">
                <span
                  :class="`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide ${priorityBadge(task.priority)}`">
                  {{ task.priority }}
                </span>
                <div class="flex items-center gap-1.5 text-[11px]"
                  :class="isOverdue(task.due_date) && task.status !== 'completed' ? 'text-red-500 font-semibold' : 'text-slate-400'">
                  <i class="fa-regular fa-calendar text-[10px]"></i>
                  <span>{{ task.due_date ? formatDate(task.due_date) : 'No due date' }}</span>
                  <span v-if="isOverdue(task.due_date) && task.status !== 'completed'"
                    class="text-[9px] bg-red-100 dark:bg-red-900/30 text-red-600 px-1 rounded">LATE</span>
                </div>
              </div>

              <!-- Footer actions -->
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

      <!-- ── LIST VIEW ── -->
      <div v-else-if="viewMode === 'list'"
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
                  Due Date</th>
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
                      :class="`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide ${priorityBadge(task.priority)}`">{{
                      task.priority }}</span>
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
                    <span
                      :class="['text-[11px]', isOverdue(task.due_date) && task.status !== 'completed' ? 'text-red-500 font-bold' : 'text-slate-500']">
                      {{ task.due_date ? formatDate(task.due_date) : '—' }}
                    </span>
                  </td>
                  <td class="px-4 sm:px-6 py-3.5 hidden md:table-cell">
                    <div class="flex items-center gap-2">
                      <div class="w-20 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" :class="progressColor(task.status)"
                          :style="{ width: `${taskProgress(task)}%` }"></div>
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
            </tbody>
          </table>
        </div>
        <div class="px-4 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-700">
          <Pagination :links="tasks.links" />
        </div>
      </div>

      <!-- ── KANBAN VIEW ── -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
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
                    :class="`text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase ${priorityBadge(task.priority)}`">{{
                    task.priority }}</span>

                  <span v-if="col.key === 'overdue'" class="text-[10px] text-red-500 font-semibold">
                    {{ getOverdueDays(task.due_date) }}d overdue
                  </span>
                  <span v-else-if="col.key === 'completed'" class="text-[10px] text-emerald-500 font-medium">Done
                    ✓</span>
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

            <!-- Empty column state -->
            <div v-if="!tasksByStatus(col.key).length" class="text-center py-6">
              <i :class="`${col.icon} text-2xl opacity-20 ${col.text}`"></i>
              <p class="text-[10px] text-slate-400 mt-1">No tasks</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Empty State ── -->
      <div v-if="!tasks.data?.length" class="flex flex-col items-center justify-center py-16 text-center">
        <div
          class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center mb-4 shadow-inner">
          <i class="fa-solid fa-list-check text-2xl text-slate-300 dark:text-slate-600"></i>
        </div>
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No tasks found</h3>
        <p class="text-xs text-slate-400 max-w-xs">No tasks match your current filters. Try adjusting or resetting them.
        </p>
        <button @click="resetFilters"
          class="mt-4 px-5 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-violet-200 dark:shadow-violet-900/30">
          Clear Filters
        </button>
      </div>

      <!-- ── Pagination (grid view) ── -->
      <div v-if="viewMode === 'grid' && tasks.data?.length" class="flex justify-center">
        <Pagination :links="tasks.links" />
      </div>
    </div>

    <!-- ════════ TASK DETAILS MODAL ════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="showDetailsModal = false"></div>

          <div
            class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-lg max-h-[92vh] overflow-hidden flex flex-col">
            <!-- Modal header -->
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

            <!-- Modal body -->
            <div class="overflow-y-auto p-5 space-y-5 flex-1">
              <div>
                <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Title</label>
                <p class="mt-1 font-semibold text-slate-900 dark:text-white text-sm">{{ selectedTask?.title }}</p>
              </div>
              <div>
                <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Description</label>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ selectedTask?.description
                  ||
                  'No description provided.' }}</p>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Priority</label>
                  <div class="mt-1">
                    <span
                      :class="`text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide ${priorityBadge(selectedTask?.priority)}`">{{
                      selectedTask?.priority }}</span>
                  </div>
                </div>
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Status</label>
                  <div class="mt-1">
                    <select v-model="selectedTask.status" @change="updateStatus(selectedTask)"
                      class="text-xs border border-slate-200 dark:border-slate-600 rounded-lg px-2.5 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500 w-full">
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Due Date</label>
                  <p class="mt-1 text-xs"
                    :class="isOverdue(selectedTask?.due_date) && selectedTask?.status !== 'completed' ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-400'">
                    {{ selectedTask?.due_date ? formatDate(selectedTask.due_date) : 'No due date' }}
                  </p>
                </div>
                <div>
                  <label class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Created</label>
                  <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ formatDate(selectedTask?.created_at) }}
                  </p>
                </div>
              </div>
              <!-- <div v-if="selectedTask?.completion_notes"
                class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-3">
                <label
                  class="text-[10px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400 font-semibold">Completion
                  Notes</label>
                <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300 italic leading-relaxed">{{
                  selectedTask.completion_notes }}</p>
              </div> -->
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ════════ COMPLETION NOTES MODAL ════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showNotesModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="showNotesModal = false"></div>
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
                placeholder="What did you accomplish? Any blockers? Notes for the team…"></textarea>

              <div class="flex gap-2.5 mt-4">
                <button @click="showNotesModal = false"
                  class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-all">
                  Cancel
                </button>
                <button @click="submitCompletionNotes"
                  class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-emerald-200 dark:shadow-emerald-900/30">
                  Complete Task ✓
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
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  tasks: { type: Object, required: true },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

// ── State ──────────────────────────────────────────────────────────
const viewMode = ref('grid')
const showDetailsModal = ref(false)
const showNotesModal = ref(false)
const selectedTask = ref(null)
const currentTask = ref(null)
const completionNotes = ref('')
const selectedIds = ref([])

const isAllSelected = computed(() =>
  props.tasks.data?.length > 0 && props.tasks.data.every((task) => selectedIds.value.includes(task.id))
)

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  priority: props.filters?.priority || '',
  sort: props.filters?.sort || 'due_date_asc',
})

// ── Static config (no reactive overhead) ──────────────────────────
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
  { key: 'trashed', label: 'Trash', icon: 'fa-solid fa-trash-can', color: 'red' },
]

const kanbanColumns = [
  { key: 'pending', label: 'Pending', icon: 'fa-solid fa-clock', dot: 'bg-amber-400', text: 'text-amber-700 dark:text-amber-400', bg: 'bg-amber-50/70 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30', badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' },
  { key: 'in_progress', label: 'In Progress', icon: 'fa-solid fa-spinner', dot: 'bg-blue-400', text: 'text-blue-700 dark:text-blue-400', bg: 'bg-blue-50/70 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30', badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' },
  { key: 'completed', label: 'Completed', icon: 'fa-solid fa-check-circle', dot: 'bg-emerald-400', text: 'text-emerald-700 dark:text-emerald-400', bg: 'bg-emerald-50/70 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30', badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' },
  { key: 'overdue', label: 'Overdue', icon: 'fa-solid fa-circle-exclamation', dot: 'bg-red-400', text: 'text-red-700 dark:text-red-400', bg: 'bg-red-50/70 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30', badge: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' },
  { key: 'trashed', label: 'Trash', icon: 'fa-solid fa-trash-can', dot: 'bg-red-500', text: 'text-red-700 dark:text-red-400', bg: 'bg-red-50/70 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30', badge: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' },
]

// ── Computed ───────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  filters.search || filters.status || filters.priority || filters.sort !== 'due_date_asc'
)

// ── Pure helpers (no reactivity needed) ──────────────────────────
const priorityDot = (p) => ({ low: 'bg-blue-400', medium: 'bg-green-400', high: 'bg-orange-400', urgent: 'bg-red-500' }[p] ?? 'bg-slate-400')

const priorityBadge = (p) => ({
  low: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  medium: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
  urgent: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}[p] ?? 'bg-slate-100 text-slate-600')

const statusBadge = (s) => ({
  pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  overdue: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
  trashed: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}[s] ?? 'bg-slate-100 text-slate-600')

const progressColor = (s) => ({
  pending: 'bg-gradient-to-r from-amber-400 to-amber-500',
  in_progress: 'bg-gradient-to-r from-blue-400 to-violet-500',
  completed: 'bg-gradient-to-r from-emerald-400 to-emerald-500',
  overdue: 'bg-gradient-to-r from-red-400 to-red-500',
  trashed: 'bg-gradient-to-r from-red-400 to-red-500',
}[s] ?? 'bg-slate-400')

const priorityGradient = (p) => ({
  low: 'from-blue-300 to-blue-500',
  medium: 'from-green-300 to-green-500',
  high: 'from-orange-300 to-orange-500',
  urgent: 'from-red-400 to-pink-500',
}[p] ?? 'from-slate-300 to-slate-400')

const taskProgress = (task) => {
  if (task.status === 'completed') return 100
  if (task.status === 'in_progress') return 55
  if (task.status === 'overdue') return 15
  if (task.status === 'trashed') return 0
  return 10
}

// The controller remaps pending/in_progress tasks with past due_date → status:'overdue'.
// So we match exactly on status — but for 'pending' we also include tasks that the
// controller kept as 'pending' (not yet overdue). This is correct as-is because the
// controller already sets status='overdue' on overdue ones before sending to Inertia.
// However if a task was genuinely 'pending' in DB but NOT overdue, the controller keeps
// it as 'pending'. So the kanban columns just need to trust the status field from props.
const tasksByStatus = (status) => {
  if (!props.tasks.data) return []
  if (status === 'trashed') {
    return props.tasks.data.filter(t => t.status === 'trashed')
  }
  if (status === 'overdue') {
    // Show tasks explicitly marked overdue by controller OR tasks whose due_date has passed
    // and are not completed (safety net for any edge cases)
    return props.tasks.data.filter(t =>
      t.status === 'overdue' ||
      (t.status !== 'completed' && t.due_date && new Date(t.due_date) < new Date())
    )
  }
  if (status === 'pending') {
    // Pending = status is pending AND not actually overdue (exclude ones already in overdue col)
    return props.tasks.data.filter(t =>
      t.status === 'pending' && !(t.due_date && new Date(t.due_date) < new Date())
    )
  }
  if (status === 'in_progress') {
    // In progress = status is in_progress AND not actually overdue
    return props.tasks.data.filter(t =>
      t.status === 'in_progress' && !(t.due_date && new Date(t.due_date) < new Date())
    )
  }
  return props.tasks.data.filter(t => t.status === status)
}

const getOverdueDays = (dueDate) =>
  dueDate ? Math.max(0, Math.floor((Date.now() - new Date(dueDate)) / 86400000)) : 0

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'

const isOverdue = (date) => !!date && new Date(date) < new Date()

// ── Actions ───────────────────────────────────────────────────────
const applyFilters = () => {
  selectedIds.value = []
  router.get(route('admin.tasks.my'), filters, { preserveState: true, replace: true })
}

const resetFilters = () => {
  filters.search = ''; filters.status = ''; filters.priority = ''; filters.sort = 'due_date_asc'
  selectedIds.value = []
  applyFilters()
}

const filterByStatus = (status) => {
  filters.status = filters.status === status ? '' : status
  applyFilters()
}

const updateStatus = (task) => {
  if (task.status === 'completed') {
    currentTask.value = task
    showNotesModal.value = true
  } else {
    router.patch(route('admin.tasks.status', task.id), { status: task.status }, {
      preserveState: true,
      onSuccess: () => window.showToast?.('Status updated', 'success'),
    })
  }
}

// Unified handler for <select> @change events (receives string value)
const handleStatusChange = (task, newStatus) => {
  task.status = newStatus
  updateStatus(task)
}

const submitCompletionNotes = () => {
  router.patch(route('admin.tasks.status', currentTask.value.id), {
    status: 'completed',
    completion_notes: completionNotes.value,
  }, {
    preserveState: true,
    onSuccess: () => {
      showNotesModal.value = false
      completionNotes.value = ''
      currentTask.value = null
      window.showToast?.('Task completed! Great job! 🎉', 'success')
    },
  })
}

const openTaskDetails = (task) => {
  selectedTask.value = { ...task }
  showDetailsModal.value = true
}

const toggleTaskSelection = (task) => {
  const index = selectedIds.value.indexOf(task.id)
  if (index === -1) {
    selectedIds.value.push(task.id)
  } else {
    selectedIds.value.splice(index, 1)
  }
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = []
    return
  }
  selectedIds.value = props.tasks.data?.map((task) => task.id) || []
}

const confirmBulkDelete = async () => {
  if (!selectedIds.value.length) return
  const res = await Swal.fire({
    title: `Move ${selectedIds.value.length} tasks to Trash?`,
    text: 'This will move selected tasks to the Trash (soft delete).',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, move to Trash',
    cancelButtonText: 'Cancel',
  })
  if (!res.isConfirmed) return

  try {
    await axios.post('/admin/tasks/bulk-delete', { task_ids: selectedIds.value })
    selectedIds.value = []
    applyFilters()
    Swal.fire({ icon: 'success', title: 'Moved to Trash', timer: 1500, showConfirmButton: false })
  } catch (err) {
    const msg = err.response?.data?.message || `Request failed (${err.response?.status || 'error'})`
    Swal.fire({ icon: 'error', title: 'Error', text: msg })
  }
}

const restoreTask = async (task) => {
  const res = await Swal.fire({
    title: `Restore "${task.title}"?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Restore',
    cancelButtonText: 'Cancel',
  })
  if (!res.isConfirmed) return

  try {
    await axios.post(`/admin/tasks/${task.id}/restore`)
    applyFilters()
    Swal.fire({ icon: 'success', title: 'Restored', timer: 1400, showConfirmButton: false })
  } catch (err) {
    const msg = err.response?.data?.message || `Request failed (${err.response?.status || 'error'})`
    Swal.fire({ icon: 'error', title: 'Error', text: msg })
  }
}

const forceDeleteTask = async (task) => {
  const res = await Swal.fire({
    title: `Permanently delete "${task.title}"?`,
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete Forever',
    cancelButtonText: 'Cancel',
  })
  if (!res.isConfirmed) return

  try {
    await axios.delete(`/admin/tasks/${task.id}/force`)
    applyFilters()
    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1400, showConfirmButton: false })
  } catch (err) {
    const msg = err.response?.data?.message || `Request failed (${err.response?.status || 'error'})`
    Swal.fire({ icon: 'error', title: 'Error', text: msg })
  }
}

const bulkRestoreSelected = async () => {
  if (!selectedIds.value.length) return
  const res = await Swal.fire({
    title: `Restore ${selectedIds.value.length} tasks?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Restore',
    cancelButtonText: 'Cancel',
  })
  if (!res.isConfirmed) return

  try {
    await Promise.all(selectedIds.value.map(id => axios.post(`/admin/tasks/${id}/restore`)))
    selectedIds.value = []
    applyFilters()
    Swal.fire({ icon: 'success', title: 'Restored', timer: 1400, showConfirmButton: false })
  } catch (err) {
    const msg = err.response?.data?.message || `Request failed (${err.response?.status || 'error'})`
    Swal.fire({ icon: 'error', title: 'Error', text: msg })
  }
}

const bulkForceDeleteSelected = async () => {
  if (!selectedIds.value.length) return
  const res = await Swal.fire({
    title: `Permanently delete ${selectedIds.value.length} tasks?`,
    text: 'This cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete Forever',
    cancelButtonText: 'Cancel',
  })
  if (!res.isConfirmed) return

  try {
    await Promise.all(selectedIds.value.map(id => axios.delete(`/admin/tasks/${id}/force`)))
    selectedIds.value = []
    applyFilters()
    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1400, showConfirmButton: false })
  } catch (err) {
    const msg = err.response?.data?.message || `Request failed (${err.response?.status || 'error'})`
    Swal.fire({ icon: 'error', title: 'Error', text: msg })
  }
}

const exportTasks = () => window.open(route('admin.tasks.export', filters), '_blank')

// Debounced search
let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 450)
}

// Drag & drop
let draggedTask = null
const dragStart = (task) => { draggedTask = task }
</script>

<style scoped>
/* ── Filter select shared style ── */
.filter-select {
  @apply px-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition;
}

/* ── Task card transition ── */
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

/* ── Kanban card transition ── */
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

/* ── Modal transition ── */
.modal-enter-active {
  transition: all 0.25s cubic-bezier(0.34, 1.3, 0.64, 1);
}

.modal-enter-from {
  opacity: 0;
}

.modal-enter-from .relative {
  transform: scale(0.95) translateY(10px);
}

.modal-leave-active {
  transition: all 0.18s ease;
}

.modal-leave-to {
  opacity: 0;
}
</style>