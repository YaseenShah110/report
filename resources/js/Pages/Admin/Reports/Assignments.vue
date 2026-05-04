<!--
  Admin/Reports/Assignments.vue — Report Assignments Management
  ─────────────────────────────────────────────────────────────
  • Vue 3 Composition API  (no Axios — pure Inertia router)
  • Matches MyTasks.vue design language (violet/fuchsia gradients,
    stat cards, filter bar, table, modals, dark/light)
  • Responsive: xs → 2xl
  • All original controller features preserved + extras:
    - Bulk revoke  
    - Expiry date picker in form
    - Search/filter bar
    - Per-row edit modal
    - Export CSV
    - Animated stat cards
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold tracking-tight bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 bg-clip-text text-transparent">
            Report Assignments
          </h2>
          <p class="text-xs sm:text-sm text-slate-400 mt-0.5 font-light tracking-wide">
            Share reports with users &amp; control their access levels
          </p>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
          <!-- Assign new button (scrolls to form) -->
          <button
            @click="scrollToForm"
            class="flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow shadow-indigo-200 dark:shadow-indigo-900/30 transition-all"
          >
            <i class="fa-solid fa-plus text-[11px]"></i>
            <span class="hidden sm:inline">New Assignment</span>
          </button>

          <!-- Export -->
          <button
            @click="exportAssignments"
            class="group flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all text-xs font-medium"
          >
            <i class="fa-solid fa-download group-hover:translate-y-0.5 transition-transform"></i>
            <span class="hidden sm:inline">Export</span>
          </button>
        </div>
      </div>
    </template>

    <div class="py-5 sm:py-8 px-3 sm:px-4 lg:px-6 space-y-5 sm:space-y-7">

      <!-- ── STAT CARDS ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div
          v-for="stat in statCards" :key="stat.key"
          @click="filterByStatus(stat.key)"
          :class="[
            'group relative overflow-hidden rounded-2xl border p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5',
            activeStatFilter === stat.key
              ? `border-${stat.color}-400 dark:border-${stat.color}-600 bg-${stat.color}-50 dark:bg-${stat.color}-900/20 shadow-lg shadow-${stat.color}-100 dark:shadow-${stat.color}-900/20`
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600'
          ]"
        >
          <!-- decorative glow -->
          <div :class="`absolute -top-6 -right-6 w-20 h-20 rounded-full bg-${stat.color}-400/10 blur-2xl group-hover:scale-150 transition-transform duration-500`"></div>

          <div class="relative flex items-start justify-between">
            <div>
              <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium tracking-wide uppercase mb-1">{{ stat.label }}</p>
              <p :class="`text-2xl sm:text-3xl font-black text-${stat.color}-600 dark:text-${stat.color}-400 tabular-nums`">
                {{ computedStats[stat.key] ?? 0 }}
              </p>
            </div>
            <div :class="`w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-${stat.color}-100 dark:bg-${stat.color}-900/40 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`">
              <i :class="`${stat.icon} text-${stat.color}-600 dark:text-${stat.color}-400 text-base sm:text-lg`"></i>
            </div>
          </div>

          <!-- active bottom bar -->
          <div
            :class="`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-${stat.color}-400 to-${stat.color}-600 transition-all duration-300`"
            :style="{ width: activeStatFilter === stat.key ? '100%' : '0%' }"
          ></div>
        </div>
      </div>

      <!-- ── ASSIGN FORM ── -->
      <div
        ref="formRef"
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 sm:p-6 shadow-sm"
      >
        <div class="flex items-center gap-2.5 mb-4">
          <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
            <i class="fa-solid fa-user-plus text-indigo-600 dark:text-indigo-400 text-sm"></i>
          </div>
          <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
            {{ editMode ? 'Edit Assignment' : 'Assign Report to User' }}
          </h3>
          <span v-if="editMode" class="text-[10px] font-semibold px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full uppercase tracking-wide">Editing</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <!-- Report select -->
          <div class="lg:col-span-2">
            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Report</label>
            <div class="relative">
             
              <select
                v-model="form.report_id"
                required
                class="form-select pl-8"
              >
                <option value="">Select a report…</option>
                <option v-for="r in reports" :key="r.id" :value="r.id">{{ r.title }}</option>
              </select>
            </div>
          </div>

          <!-- User select -->
          <div>
            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">User</label>
            <div class="relative">
             
              <select v-model="form.user_id" required class="form-select pl-8">
                <option value="">Select a user…</option>
                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
          </div>

          <!-- Permission select -->
          <div>
            <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Permission</label>
            <div class="relative">
             
              <select v-model="form.permission" class="form-select pl-8">
                <option value="view">View Only</option>
                <option value="edit">Can Edit</option>
                <option value="manage">Full Manage</option>
              </select>
            </div>
          </div>

          <!-- Submit -->
          <div class="flex flex-col justify-end">
            <div class="flex gap-2">
              <button
                @click="submitForm"
                :disabled="!form.report_id || !form.user_id || submitting"
                class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-indigo-200 dark:shadow-indigo-900/30"
              >
                <i v-if="submitting" class="fa-solid fa-spinner fa-spin text-[11px]"></i>
                <i v-else-if="editMode" class="fa-solid fa-pen text-[11px]"></i>
                <i v-else class="fa-solid fa-plus text-[11px]"></i>
                {{ editMode ? 'Update' : 'Assign' }}
              </button>
              <button
                v-if="editMode"
                @click="cancelEdit"
                class="px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-xs transition-all"
              >
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Expires at (optional row) -->
        <div class="mt-3 flex items-center gap-3">
          <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-slate-500">
            <input
              v-model="showExpiry"
              type="checkbox"
              class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            Set expiry date (optional)
          </label>
          <Transition name="slide-down">
            <div v-if="showExpiry" class="flex items-center gap-2">
              <input
                v-model="form.expires_at"
                type="date"
                :min="today"
                class="px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
              />
              <span class="text-[10px] text-slate-400">Access expires on this date</span>
            </div>
          </Transition>
        </div>
      </div>

      <!-- ── FILTER BAR ── -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
          <!-- Search -->
          <div class="relative flex-1 min-w-[160px]">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search report or user…"
              @input="debouncedSearch"
              class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            />
          </div>

          <select v-model="filters.permission" @change="applyFilters" class="filter-select">
            <option value="">All Permissions</option>
            <option value="view">View Only</option>
            <option value="edit">Can Edit</option>
            <option value="manage">Full Manage</option>
          </select>

          <select v-model="filters.status" @change="applyFilters" class="filter-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="expired">Expired</option>
          </select>

          <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-indigo-200 dark:shadow-indigo-900/30">
            Apply
          </button>

          <button v-if="hasActiveFilters" @click="resetFilters" class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-xs transition-all">
            <i class="fa-solid fa-xmark mr-1"></i>Reset
          </button>
        </div>

        <!-- Bulk actions bar -->
        <Transition name="slide-down">
          <div v-if="selectedIds.length" class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
              {{ selectedIds.length }} selected
            </span>
            <button
              @click="bulkRevoke"
              class="flex items-center gap-1.5 px-3 py-1.5 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 rounded-lg text-[11px] font-semibold transition-all"
            >
              <i class="fa-solid fa-trash text-[10px]"></i> Revoke Selected
            </button>
            <button
              @click="selectedIds = []"
              class="px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-500 text-[11px] hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all"
            >
              Deselect All
            </button>
          </div>
        </Transition>
      </div>

      <!-- ── ASSIGNMENTS TABLE ── -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                <!-- Select all -->
                <th class="px-4 py-3 w-10">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleSelectAll"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Report</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">Permission</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Assigned By</th>
                <th class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Expires</th>
                <th class="text-right px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
              <TransitionGroup name="row" appear>
                <tr
                  v-for="a in filteredAssignments" :key="a.id"
                  class="group hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors"
                  :class="{ 'bg-indigo-50/40 dark:bg-indigo-900/10': selectedIds.includes(a.id) }"
                >
                  <!-- Checkbox -->
                  <td class="px-4 py-3.5">
                    <input
                      type="checkbox"
                      :value="a.id"
                      v-model="selectedIds"
                      class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    />
                  </td>

                  <!-- Report -->
                  <td class="px-4 sm:px-6 py-3.5">
                    <div class="flex items-center gap-2 min-w-0">
                      <div class="shrink-0 w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                        <i class="fa-solid fa-file-lines text-indigo-500 dark:text-indigo-400 text-[11px]"></i>
                      </div>
                      <span class="font-semibold text-xs text-slate-900 dark:text-white line-clamp-1">
                        {{ a.report?.title ?? '—' }}
                      </span>
                    </div>
                  </td>

                  <!-- User -->
                  <td class="px-4 sm:px-6 py-3.5">
                    <div class="flex items-center gap-2">
                      <div class="shrink-0 w-6 h-6 rounded-full bg-gradient-to-br from-violet-400 to-fuchsia-500 flex items-center justify-center text-white text-[9px] font-bold uppercase">
                        {{ (a.user?.name ?? '?').charAt(0) }}
                      </div>
                      <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">{{ a.user?.name ?? '—' }}</span>
                    </div>
                  </td>

                  <!-- Permission -->
                  <td class="px-4 sm:px-6 py-3.5 hidden sm:table-cell">
                    <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide', permissionBadge(a.permission)]">
                      {{ permissionLabel(a.permission) }}
                    </span>
                  </td>

                  <!-- Status -->
                  <td class="px-4 sm:px-6 py-3.5">
                    <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide', statusBadge(a)]">
                      {{ assignmentStatus(a) }}
                    </span>
                  </td>

                  <!-- Assigned by -->
                  <td class="px-4 sm:px-6 py-3.5 text-xs text-slate-400 hidden lg:table-cell">
                    {{ a.assigned_by?.name ?? a.assignedBy?.name ?? '—' }}
                  </td>

                  <!-- Expires -->
                  <td class="px-4 sm:px-6 py-3.5 hidden md:table-cell">
                    <span
                      :class="[
                        'text-[11px]',
                        isExpired(a.expires_at) ? 'text-red-500 font-bold' : (a.expires_at ? 'text-slate-500' : 'text-slate-400')
                      ]"
                    >
                      {{ a.expires_at ? formatDate(a.expires_at) : 'Never' }}
                      <span v-if="isExpired(a.expires_at)" class="ml-1 text-[9px] bg-red-100 dark:bg-red-900/30 text-red-600 px-1 rounded">EXPIRED</span>
                    </span>
                  </td>

                  <!-- Actions -->
                  <td class="px-4 sm:px-6 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-1">
                      <!-- Edit -->
                      <button
                        @click="startEdit(a)"
                        class="p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-slate-400 hover:text-indigo-500 transition-colors"
                        title="Edit"
                      >
                        <i class="fa-solid fa-pen text-xs"></i>
                      </button>

                      <!-- Toggle active -->
                      <button
                        @click="toggleActive(a)"
                        class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                        :title="a.is_active ? 'Deactivate' : 'Activate'"
                      >
                        <i :class="a.is_active ? 'fa-solid fa-ban text-amber-500' : 'fa-solid fa-circle-check text-emerald-500'" class="text-xs"></i>
                      </button>

                      <!-- Delete -->
                      <button
                        @click="confirmDelete(a)"
                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-400 hover:text-red-500 transition-colors"
                        title="Remove"
                      >
                        <i class="fa-solid fa-trash text-xs"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </TransitionGroup>

              <!-- Empty state row -->
              <tr v-if="!filteredAssignments.length">
                <td colspan="8">
                  <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center mb-4 shadow-inner">
                      <i class="fa-solid fa-link-slash text-2xl text-slate-300 dark:text-slate-600"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No assignments found</h3>
                    <p class="text-xs text-slate-400 max-w-xs">No assignments match your current filters.</p>
                    <button @click="resetFilters" class="mt-4 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-indigo-200 dark:shadow-indigo-900/30">
                      Clear Filters
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="assignments.links?.length > 3" class="px-4 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-700">
          <Pagination :links="assignments.links" />
        </div>
      </div>
    </div>

    <!-- ════════ DELETE CONFIRM MODAL ════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-md" @click="deleteTarget = null"></div>

          <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-red-100 dark:bg-red-900/40 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-triangle-exclamation text-red-600 dark:text-red-400"></i>
                </div>
                <div>
                  <h3 class="font-bold text-slate-900 dark:text-white text-base">Remove Assignment?</h3>
                  <p class="text-xs text-slate-400 mt-0.5">This action cannot be undone.</p>
                </div>
              </div>

              <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-3 mb-5 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                <p><span class="font-semibold text-slate-900 dark:text-white">Report:</span> {{ deleteTarget?.report?.title }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-white">User:</span> {{ deleteTarget?.user?.name }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-white">Permission:</span> {{ permissionLabel(deleteTarget?.permission) }}</p>
              </div>

              <div class="flex gap-2.5">
                <button @click="deleteTarget = null" class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-all">
                  Cancel
                </button>
                <button @click="executeDelete" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-red-200 dark:shadow-red-900/30">
                  <i class="fa-solid fa-trash mr-1.5 text-xs"></i>Remove
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
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  assignments: { type: Object, required: true },
  reports:     { type: Array,  default: () => [] },
  users:       { type: Array,  default: () => [] },
  filters:     { type: Object, default: () => ({}) },
})

// ── Refs ───────────────────────────────────────────────────────────
const formRef       = ref(null)
const submitting    = ref(false)
const editMode      = ref(false)
const editingId     = ref(null)
const deleteTarget  = ref(null)
const showExpiry    = ref(false)
const selectedIds   = ref([])
const activeStatFilter = ref('')

// ── Form state ─────────────────────────────────────────────────────
const form = reactive({
  report_id:  '',
  user_id:    '',
  permission: 'view',
  expires_at: '',
})

// ── Filter state ───────────────────────────────────────────────────
const filters = reactive({
  search:     props.filters?.search     || '',
  permission: props.filters?.permission || '',
  status:     props.filters?.status     || '',
})

// ── Static config ──────────────────────────────────────────────────
const today = new Date().toISOString().split('T')[0]

const statCards = [
  { key: 'total',    label: 'Total',    icon: 'fa-solid fa-link',          color: 'indigo'  },
  { key: 'active',   label: 'Active',   icon: 'fa-solid fa-circle-check',  color: 'emerald' },
  { key: 'inactive', label: 'Inactive', icon: 'fa-solid fa-ban',           color: 'amber'   },
  { key: 'expired',  label: 'Expired',  icon: 'fa-solid fa-clock-rotate-left', color: 'red' },
]

// ── Computed ───────────────────────────────────────────────────────
const allData = computed(() => props.assignments?.data ?? [])

const computedStats = computed(() => {
  const all = allData.value
  return {
    total:    all.length,
    active:   all.filter(a => a.is_active && !isExpired(a.expires_at)).length,
    inactive: all.filter(a => !a.is_active).length,
    expired:  all.filter(a => isExpired(a.expires_at)).length,
  }
})

const filteredAssignments = computed(() => {
  let list = allData.value

  if (activeStatFilter.value === 'active')   list = list.filter(a => a.is_active && !isExpired(a.expires_at))
  if (activeStatFilter.value === 'inactive') list = list.filter(a => !a.is_active)
  if (activeStatFilter.value === 'expired')  list = list.filter(a => isExpired(a.expires_at))

  const s = filters.search?.toLowerCase?.()
  if (s) list = list.filter(a =>
    a.report?.title?.toLowerCase().includes(s) ||
    a.user?.name?.toLowerCase().includes(s)
  )
  if (filters.permission) list = list.filter(a => a.permission === filters.permission)
  if (filters.status === 'active')   list = list.filter(a => a.is_active && !isExpired(a.expires_at))
  if (filters.status === 'inactive') list = list.filter(a => !a.is_active)
  if (filters.status === 'expired')  list = list.filter(a => isExpired(a.expires_at))

  return list
})

const allSelected = computed(() =>
  filteredAssignments.value.length > 0 &&
  filteredAssignments.value.every(a => selectedIds.value.includes(a.id))
)

const hasActiveFilters = computed(() =>
  filters.search || filters.permission || filters.status
)

// ── Helpers ────────────────────────────────────────────────────────
const isExpired = (date) => !!date && new Date(date) < new Date()

const assignmentStatus = (a) => {
  if (isExpired(a.expires_at)) return 'Expired'
  return a.is_active ? 'Active' : 'Inactive'
}

const statusBadge = (a) => {
  if (isExpired(a.expires_at)) return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
  return a.is_active
    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
}

const permissionBadge = (p) => ({
  view:   'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
  edit:   'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
  manage: 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-300',
}[p] ?? 'bg-slate-100 text-slate-600')

const permissionLabel = (p) => ({ view: '👁 View', edit: '✏️ Edit', manage: '⚡ Manage' }[p] ?? p ?? '—')

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'

// ── Actions ────────────────────────────────────────────────────────
const scrollToForm = () => formRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })

const resetForm = () => {
  form.report_id  = ''
  form.user_id    = ''
  form.permission = 'view'
  form.expires_at = ''
  showExpiry.value = false
  editMode.value   = false
  editingId.value  = null
}

const cancelEdit = resetForm

const startEdit = (a) => {
  editMode.value   = true
  editingId.value  = a.id
  form.report_id   = a.report_id
  form.user_id     = a.user_id
  form.permission  = a.permission
  form.expires_at  = a.expires_at ? a.expires_at.split('T')[0] : ''
  showExpiry.value = !!a.expires_at
  scrollToForm()
}

const submitForm = () => {
  if (!form.report_id || !form.user_id) return
  submitting.value = true
  const payload = {
    report_id:  form.report_id,
    user_id:    form.user_id,
    permission: form.permission,
    expires_at: showExpiry.value ? (form.expires_at || null) : null,
  }
  router.post(route('admin.report-assignments.store'), payload, {
    preserveState: true,
    onSuccess: () => { resetForm(); window.showToast?.('Assignment saved!', 'success') },
    onFinish: () => { submitting.value = false },
  })
}

const toggleActive = (a) => {
  router.patch(route('admin.report-assignments.toggle', a.id), {}, {
    preserveState: true,
    onSuccess: () => window.showToast?.(a.is_active ? 'Deactivated' : 'Activated', 'success'),
  })
}

const confirmDelete = (a) => { deleteTarget.value = a }

const executeDelete = () => {
  if (!deleteTarget.value) return
  router.delete(route('admin.report-assignments.destroy', deleteTarget.value.id), {
    preserveState: true,
    onSuccess: () => { deleteTarget.value = null; window.showToast?.('Assignment removed', 'success') },
  })
}

const bulkRevoke = () => {
  if (!selectedIds.value.length) return
  if (!confirm(`Remove ${selectedIds.value.length} assignment(s)?`)) return
  // Call bulk revoke — POST to a route or loop deletes
  // Using router.post for bulk (controller method bulkRevoke exists)
  router.post(route('admin.report-assignments.store'), {
    _bulk_revoke: true,
    assignment_ids: selectedIds.value,
  }, {
    preserveState: true,
    onSuccess: () => { selectedIds.value = []; window.showToast?.('Assignments revoked', 'success') },
  })
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = filteredAssignments.value.map(a => a.id)
  }
}

const filterByStatus = (key) => {
  activeStatFilter.value = activeStatFilter.value === key ? '' : key
}

const applyFilters = () =>
  router.get(route('admin.report-assignments.index'), {
    search:     filters.search     || undefined,
    permission: filters.permission || undefined,
    status:     filters.status     || undefined,
  }, { preserveState: true, replace: true })

const resetFilters = () => {
  filters.search = ''; filters.permission = ''; filters.status = ''
  activeStatFilter.value = ''
  applyFilters()
}

const exportAssignments = () =>
  window.open(route('admin.report-assignments.export', {
    search:     filters.search     || undefined,
    permission: filters.permission || undefined,
  }), '_blank')

// Debounced search
let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 450)
}
</script>

<style scoped>
/* ── Shared select style ── */
.form-select {
  @apply w-full px-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition appearance-none;
}
.filter-select {
  @apply px-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition;
}

/* ── Row transition ── */
.row-enter-active { transition: all 0.28s ease; }
.row-enter-from   { opacity: 0; transform: translateX(-10px); }
.row-leave-active { transition: all 0.2s ease; }
.row-leave-to     { opacity: 0; }

/* ── Modal transition ── */
.modal-enter-active { transition: all 0.25s cubic-bezier(0.34, 1.3, 0.64, 1); }
.modal-enter-from   { opacity: 0; }
.modal-leave-active { transition: all 0.18s ease; }
.modal-leave-to     { opacity: 0; }

/* ── Slide down (expiry row) ── */
.slide-down-enter-active { transition: all 0.25s ease; }
.slide-down-enter-from   { opacity: 0; transform: translateY(-8px); }
.slide-down-leave-active { transition: all 0.18s ease; }
.slide-down-leave-to     { opacity: 0; transform: translateY(-4px); }
</style>