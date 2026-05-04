<!--
  Admin/Tasks/Index.vue — Task Management (Admin View)
  ─────────────────────────────────────────────────────
  Full Vue 3 Composition API rewrite.
  All logic preserved. Zero axios. Fast Inertia router only.
  Design: Refined dark-command-centre aesthetic.
-->
<template>
  <AuthenticatedLayout>

    <!-- ══ HEADER ══════════════════════════════════════════════ -->
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold tracking-tight bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">
            Task Management
          </h2>
          <p class="text-xs sm:text-sm text-slate-400 mt-0.5 font-light tracking-wide">
            Create and manage tasks for team members
          </p>
        </div>
        <Link
          :href="route('admin.tasks.create')"
          class="group inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs sm:text-sm font-bold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:-translate-y-0.5"
        >
          <i class="fa-solid fa-plus group-hover:rotate-90 transition-transform duration-200"></i>
          Create Task
        </Link>
      </div>
    </template>

    <div class="py-5 sm:py-8 px-3 sm:px-4 lg:px-6 space-y-5 sm:space-y-7">

      <!-- ══ OVERDUE ALERT BANNER ══════════════════════════════ -->
      <Transition name="slide-down">
        <div
          v-if="overdueCount > 0"
          class="relative overflow-hidden flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 sm:p-5 rounded-2xl border border-red-200 dark:border-red-800/60 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-950/40 dark:to-rose-950/30"
        >
          <!-- animated bg pulse -->
          <div class="absolute inset-0 bg-red-400/5 animate-pulse pointer-events-none rounded-2xl"></div>

          <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/50 flex items-center justify-center shrink-0 shadow-inner">
              <i class="fa-solid fa-circle-exclamation text-red-500 text-lg animate-bounce"></i>
            </div>
            <div>
              <p class="font-bold text-red-700 dark:text-red-400 text-sm">Overdue Tasks Alert</p>
              <p class="text-xs text-red-600/80 dark:text-red-300/80">
                <span class="font-black">{{ overdueCount }}</span> overdue task(s) need immediate attention!
              </p>
            </div>
          </div>

          <button
            @click="jumpToOverdue"
            class="relative shrink-0 px-4 py-2.5 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-bold transition-all duration-200 hover:-translate-y-0.5 shadow-lg shadow-red-500/25"
          >
            View Overdue →
          </button>
        </div>
      </Transition>

      <!-- ══ STATS CARDS ════════════════════════════════════════ -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div
          v-for="(card, i) in statCards" :key="card.label"
          @click="card.onClick"
          :style="{ animationDelay: `${i * 80}ms` }"
          :class="[
            'group relative overflow-hidden rounded-2xl border p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 animate-fade-in',
            activeStatKey === card.filterKey
              ? `border-${card.color}-400 dark:border-${card.color}-600 bg-${card.color}-50 dark:bg-${card.color}-900/20 shadow-lg shadow-${card.color}-100 dark:shadow-${card.color}-900/20`
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'
          ]"
        >
          <!-- decorative glow -->
          <div :class="`absolute -top-6 -right-6 w-20 h-20 rounded-full bg-${card.color}-400/10 blur-2xl group-hover:scale-150 transition-transform duration-500 pointer-events-none`"></div>

          <div class="relative flex items-start justify-between">
            <div>
              <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium tracking-wide uppercase mb-1">{{ card.label }}</p>
              <p :class="`text-2xl sm:text-3xl font-black text-${card.color}-600 dark:text-${card.color}-400 tabular-nums`">
                {{ card.value }}
              </p>
              <p v-if="card.subtext" class="text-[9px] sm:text-[10px] text-slate-400 mt-1 leading-tight">{{ card.subtext }}</p>
            </div>
            <div :class="`w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-${card.color}-100 dark:bg-${card.color}-900/40 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`">
              <i :class="`${card.icon} text-${card.color}-600 dark:text-${card.color}-400 text-base sm:text-lg`"></i>
            </div>
          </div>

          <!-- bottom bar indicator -->
          <div
            :class="`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-${card.color}-400 to-${card.color}-600 transition-all duration-300`"
            :style="{ width: activeStatKey === card.filterKey ? '100%' : '0%' }"
          ></div>
        </div>
      </div>

      <!-- ══ FILTER BAR ══════════════════════════════════════════ -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">

          <!-- Search -->
          <div class="relative flex-1 min-w-[160px]">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search tasks…"
              @input="debouncedSearch"
              class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            />
          </div>

          <select v-model="filters.status" @change="applyFilters" class="filter-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="overdue">Overdue</option>
          </select>

          <select v-model="filters.priority" @change="applyFilters" class="filter-select">
            <option value="">All Priority</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>

          <select v-model="filters.assigned_to" @change="applyFilters" class="filter-select hidden sm:block">
            <option value="">All Users</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>

          <select v-model="filters.trashed" @change="applyFilters" class="filter-select hidden md:block">
            <option value="">Active</option>
            <option value="1">Trashed</option>
          </select>

          <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-indigo-200 dark:shadow-indigo-900/30">
            Apply
          </button>
          <button v-if="hasActiveFilters" @click="resetFilters" class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-xs transition-all">
            <i class="fa-solid fa-xmark mr-1"></i>Reset
          </button>
        </div>
      </div>

      <!-- ══ TASKS TABLE ════════════════════════════════════════ -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">

        <!-- table header count -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 border-b border-slate-100 dark:border-slate-700">
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
            <span class="text-slate-900 dark:text-white font-black">{{ tasks.total ?? tasks.data?.length ?? 0 }}</span> tasks found
          </p>
          <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
            <span v-if="filters.status" :class="`px-2 py-0.5 rounded-full font-semibold ${statusBadge(filters.status)}`">{{ filters.status.replace('_',' ') }}</span>
            <span v-if="filters.priority" :class="`px-2 py-0.5 rounded-full font-semibold ${priorityBadge(filters.priority)}`">{{ filters.priority }}</span>
            <span v-if="filters.trashed === '1'" class="px-2 py-0.5 rounded-full font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
              <i class="fa-solid fa-trash-can mr-1 text-[8px]"></i>Trashed
            </span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Task</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Assigned To</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Priority</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">Status</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Due Date</th>
                <th class="text-right px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
              <TransitionGroup name="row" appear>
                <tr
                  v-for="task in tasks.data" :key="task.id"
                  :class="[
                    'group hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors',
                    task.deleted_at ? 'bg-red-50/30 dark:bg-red-950/10' : ''
                  ]"
                >
                  <!-- Task title + desc -->
                  <td class="px-4 sm:px-6 py-3.5">
                    <div class="flex items-start gap-2.5">
                      <div :class="`shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full ${task.deleted_at ? 'bg-red-400' : priorityDot(task.priority)}`"></div>
                      <div class="min-w-0">
                        <p :class="['font-semibold text-xs line-clamp-1', task.deleted_at ? 'text-slate-400 dark:text-slate-500 line-through' : 'text-slate-900 dark:text-white']">
                          {{ task.title }}
                        </p>
                        <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ task.description || 'No description' }}</p>
                        <!-- mobile badges -->
                        <div class="flex items-center gap-1.5 mt-1.5 sm:hidden flex-wrap">
                          <span v-if="!task.deleted_at" :class="`text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase ${statusBadge(task.status)}`">
                            {{ task.status.replace('_',' ') }}
                          </span>
                          <span v-if="task.deleted_at" class="text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                            Deleted
                          </span>
                          <span v-if="task.due_date && !task.deleted_at" :class="['text-[9px] font-semibold', isOverdue(task.due_date, task.status) ? 'text-red-500' : 'text-slate-400']">
                            <i class="fa-regular fa-calendar text-[8px] mr-0.5"></i>{{ formatDate(task.due_date) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Assigned To -->
                  <td class="px-4 sm:px-6 py-3.5 hidden md:table-cell">
                    <div class="flex items-center gap-2.5">
                      <div :class="[
                        'shrink-0 w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-white text-[10px] sm:text-xs font-black shadow-sm',
                        task.deleted_at ? 'bg-gradient-to-br from-slate-400 to-slate-500' : 'bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500'
                      ]">
                        {{ task.assigned_to?.name?.charAt(0)?.toUpperCase() || '?' }}
                      </div>
                      <div class="min-w-0">
                        <p :class="['text-xs font-semibold truncate', task.deleted_at ? 'text-slate-400 dark:text-slate-500' : 'text-slate-800 dark:text-slate-200']">
                          {{ task.assigned_to?.name || 'Unassigned' }}
                        </p>
                        <p v-if="task.assigned_by" class="text-[10px] text-slate-400 truncate">by {{ task.assigned_by?.name }}</p>
                      </div>
                    </div>
                  </td>

                  <!-- Priority -->
                  <td class="px-4 sm:px-6 py-3.5">
                    <span v-if="!task.deleted_at" :class="`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide ${priorityBadge(task.priority)}`">
                      {{ task.priority }}
                    </span>
                    <span v-else class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                      —
                    </span>
                  </td>

                  <!-- Status -->
                  <td class="px-4 sm:px-6 py-3.5 hidden sm:table-cell">
                    <span v-if="!task.deleted_at" :class="`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide ${statusBadge(task.status)}`">
                      {{ task.status.replace('_', ' ') }}
                    </span>
                    <span v-else class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                      Deleted
                    </span>
                  </td>

                  <!-- Due Date -->
                  <td class="px-4 sm:px-6 py-3.5 hidden lg:table-cell">
                    <div v-if="task.due_date && !task.deleted_at" class="flex items-center gap-1.5">
                      <span
                        :class="['text-[11px] font-medium', isOverdue(task.due_date, task.status) ? 'text-red-500 dark:text-red-400 font-bold' : 'text-slate-500 dark:text-slate-400']"
                      >
                        {{ formatDate(task.due_date) }}
                      </span>
                      <span v-if="isOverdue(task.due_date, task.status)" class="text-[9px] bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-1 rounded font-bold">LATE</span>
                    </div>
                    <span v-else-if="task.deleted_at" class="text-[11px] text-slate-400">
                      {{ task.deleted_at ? formatDate(task.deleted_at) : '—' }}
                    </span>
                    <span v-else class="text-[11px] text-slate-400">—</span>
                  </td>

                  <!-- Actions -->
                  <td class="px-4 sm:px-6 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-0.5 sm:gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                      <!-- Restore button (only for trashed tasks) -->
                      <button
                        v-if="task.deleted_at"
                        @click="restoreTask(task)"
                        class="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-400 hover:text-emerald-500 transition-colors"
                        title="Restore Task"
                      >
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                      </button>
                      
                      <!-- Force delete button (only for trashed tasks) -->
                      <button
                        v-if="task.deleted_at"
                        @click="confirmForceDelete(task)"
                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors"
                        title="Permanently Delete"
                      >
                        <i class="fa-solid fa-skull text-xs"></i>
                      </button>
                      
                      <!-- Regular actions (only for non-trashed tasks) -->
                      <template v-if="!task.deleted_at">
                        <Link
                          :href="route('admin.tasks.show', task.id)"
                          class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/30 text-slate-400 hover:text-violet-500 transition-colors"
                          title="View"
                        >
                          <i class="fa-regular fa-eye text-xs"></i>
                        </Link>
                        <Link
                          :href="route('admin.tasks.edit', task.id)"
                          class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 text-slate-400 hover:text-amber-500 transition-colors"
                          title="Edit"
                        >
                          <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </Link>
                        <button
                          @click="confirmDelete(task)"
                          class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors"
                          title="Delete"
                        >
                          <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </TransitionGroup>

              <!-- Empty row -->
              <tr v-if="!tasks.data?.length">
                <td colspan="6" class="py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-inner">
                      <i class="fa-solid fa-list-check text-xl text-slate-300 dark:text-slate-600"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-400">No tasks found</p>
                    <p class="text-xs text-slate-400 max-w-xs">No tasks match your current filters. Try adjusting or resetting them.</p>
                    <button v-if="hasActiveFilters" @click="resetFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-indigo-200 dark:shadow-indigo-900/30">
                      Clear Filters
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="tasks.links?.length > 3" class="px-4 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-700">
          <Pagination :links="tasks.links" />
        </div>
      </div>
    </div>

    <!-- ══ DELETE CONFIRMATION MODAL ════════════════════════════ -->
    <ConfirmationModal
      :show="deleteModal.show"
      title="Delete Task?"
      :message="`Are you sure you want to delete &quot;${deleteModal.task?.title}&quot;? It will be moved to trash.`"
      confirm-text="Move to Trash"
      icon="fa-solid fa-trash"
      @close="deleteModal.show = false"
      @confirm="deleteTask"
    />

    <!-- ══ FORCE DELETE CONFIRMATION MODAL ═════════════════════ -->
    <ConfirmationModal
      :show="forceDeleteModal.show"
      title="Permanently Delete Task?"
      :message="`Are you sure you want to permanently delete &quot;${forceDeleteModal.task?.title}&quot;? This action CANNOT be undone.`"
      confirm-text="Delete Permanently"
      icon="fa-solid fa-skull"
      @close="forceDeleteModal.show = false"
      @confirm="forceDeleteTask"
    />

  </AuthenticatedLayout>
</template>

<script setup>
/**
 * Admin/Tasks/Index.vue
 * Vue 3 Composition API — zero axios, Inertia router only.
 * All original logic preserved + performance optimised.
 */
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  tasks:   { type: Object, required: true },
  users:   { type: Array,  default: () => [] },
  stats:   { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()

// ── State ──────────────────────────────────────────────────────────
const deleteModal = ref({ show: false, task: null })
const forceDeleteModal = ref({ show: false, task: null })

const filters = reactive({
  search:      props.filters?.search      || '',
  status:      props.filters?.status      || '',
  priority:    props.filters?.priority    || '',
  assigned_to: props.filters?.assigned_to || '',
  trashed:     props.filters?.trashed     || '',
})

// ── Computed ───────────────────────────────────────────────────────
const overdueCount = computed(
  () => page.props.notifications?.overdue_tasks ?? 0
)

const hasActiveFilters = computed(() =>
  filters.search || filters.status || filters.priority ||
  filters.assigned_to || filters.trashed
)

const activeStatKey = computed(() => filters.status || (filters.trashed === '1' ? '_trashed' : ''))

// Static card config — no reactive overhead
const statCards = computed(() => [
  {
    label:     'Total',
    value:     props.stats?.total       ?? 0,
    icon:      'fa-solid fa-layer-group',
    color:     'indigo',
    filterKey: '',
    subtext:   null,
    onClick:   () => setStatusFilter(''),
  },
  {
    label:     'Pending',
    value:     props.stats?.pending     ?? 0,
    icon:      'fa-solid fa-clock',
    color:     'amber',
    filterKey: 'pending',
    subtext:   page.props.notifications?.pending_tasks
      ? `${page.props.notifications.pending_tasks} assigned to you`
      : null,
    onClick:   () => setStatusFilter('pending'),
  },
  {
    label:     'In Progress',
    value:     props.stats?.in_progress ?? 0,
    icon:      'fa-solid fa-spinner',
    color:     'blue',
    filterKey: 'in_progress',
    subtext:   null,
    onClick:   () => setStatusFilter('in_progress'),
  },
  {
    label:     'Completed',
    value:     props.stats?.completed   ?? 0,
    icon:      'fa-solid fa-circle-check',
    color:     'emerald',
    filterKey: 'completed',
    subtext:   null,
    onClick:   () => setStatusFilter('completed'),
  },
  {
    label:     'Trashed',
    value:     props.stats?.trashed     ?? 0,
    icon:      'fa-solid fa-trash-can',
    color:     'red',
    filterKey: '_trashed',
    subtext:   page.props.notifications?.overdue_tasks
      ? `${page.props.notifications.overdue_tasks} overdue`
      : null,
    onClick:   () => setTrashedFilter(),
  },
])

// ── Pure helpers ──────────────────────────────────────────────────
const priorityDot = (p) => ({
  low:    'bg-sky-400',
  medium: 'bg-green-400',
  high:   'bg-orange-400',
  urgent: 'bg-red-500',
}[p] ?? 'bg-slate-400')

const priorityBadge = (p) => ({
  low:    'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
  medium: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  high:   'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
  urgent: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}[p] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300')

const statusBadge = (s) => ({
  pending:     'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  completed:   'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  overdue:     'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}[s] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300')

const formatDate = (date) =>
  date
    ? new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
    : '—'

const isOverdue = (date, status) =>
  !!date && new Date(date) < new Date() && status !== 'completed'

// ── Actions ───────────────────────────────────────────────────────
const applyFilters = () =>
  router.get(route('admin.tasks.index'), filters, { preserveState: true, replace: true })

const resetFilters = () => {
  filters.search = ''; filters.status = ''; filters.priority = ''
  filters.assigned_to = ''; filters.trashed = ''
  applyFilters()
}

const setStatusFilter = (status) => {
  filters.status = status
  filters.trashed = ''
  applyFilters()
}

const setTrashedFilter = () => {
  filters.trashed = '1'
  filters.status = ''
  applyFilters()
}

const jumpToOverdue = () => {
  filters.status = 'overdue'
  filters.trashed = ''
  applyFilters()
}

const confirmDelete = (task) => {
  deleteModal.value = { show: true, task }
}

const confirmForceDelete = (task) => {
  forceDeleteModal.value = { show: true, task }
}

const deleteTask = () => {
  router.delete(route('admin.tasks.destroy', deleteModal.value.task.id), {
    preserveState: false,
    onSuccess: () => {
      deleteModal.value.show = false
      window.showToast?.('Task moved to trash', 'success')
    },
  })
}

const restoreTask = (task) => {
  router.post(route('admin.tasks.restore', task.id), {}, {
    preserveState: false,
    onSuccess: () => {
      window.showToast?.('Task restored successfully', 'success')
    },
  })
}

const forceDeleteTask = () => {
  router.delete(route('admin.tasks.force-delete', forceDeleteModal.value.task.id), {
    preserveState: false,
    onSuccess: () => {
      forceDeleteModal.value.show = false
      window.showToast?.('Task permanently deleted', 'success')
    },
  })
}

// Debounced search
let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 450)
}
</script>

<style scoped>
/* ── Filter select shared style ── */
.filter-select {
  @apply px-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition;
}

/* ── Slide-down banner transition ── */
.slide-down-enter-active { transition: all 0.35s cubic-bezier(0.34, 1.4, 0.64, 1); }
.slide-down-enter-from   { opacity: 0; transform: translateY(-12px); }
.slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-leave-to     { opacity: 0; transform: translateY(-8px); }

/* ── Table row transition ── */
.row-enter-active { transition: all 0.25s ease; }
.row-enter-from   { opacity: 0; transform: translateX(-12px); }
.row-leave-active { transition: all 0.2s ease; }
.row-leave-to     { opacity: 0; }

/* ── Staggered card fade-in ── */
@keyframes fade-in {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.45s ease both;
}
</style>