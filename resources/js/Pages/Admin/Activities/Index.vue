<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="act-title">
            <span class="act-title-icon"><i class="fa-solid fa-wave-square"></i></span>
            Activity Logs
          </h2>
          <p class="act-subtitle">Monitor every action across your system in real-time</p>
        </div>
      </div>
    </template>

    <div class="act-page">

      <!-- ═══ Page Action Bar (rendered once — outside #header slot) ═══ -->
      <!-- WHY HERE: AuthenticatedLayout renders #header slot twice (desktop
           nav + mobile nav panel). Buttons placed there appear duplicated on
           small screens. Placing them here renders them exactly once. -->
      <div class="act-action-bar">
        <div class="act-action-bar-left">
          <i class="fa-solid fa-wave-square text-indigo-500 text-sm"></i>
          <span class="act-action-bar-title">Activity Logs</span>
          <span class="act-action-bar-count">{{ activities.total ?? 0 }} records</span>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end">
          <button @click="exportActivities" class="act-btn act-btn-ghost">
            <i class="fa-solid fa-file-csv"></i>
            <span class="hidden sm:inline">Export CSV</span>
          </button>
          <button @click="openFilterDeleteModal" class="act-btn act-btn-warning" :disabled="!activities.data?.length">
            <i class="fa-solid fa-filter-circle-xmark"></i>
            <span class="hidden sm:inline">Filter Delete</span>
          </button>
          <button @click="openClearModal" class="act-btn act-btn-danger">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span class="hidden sm:inline">Clear Old</span>
          </button>
        </div>
      </div>

      <!-- ═══ Stats Cards ═══ -->
      <div class="act-stats-grid">
        <div v-for="(stat, i) in statsCards" :key="stat.label" class="act-stat-card" :style="`--delay: ${i * 80}ms`"
          :class="stat.cls">
          <div class="act-stat-inner">
            <div class="act-stat-icon-wrap">
              <i :class="stat.icon"></i>
            </div>
            <div>
              <p class="act-stat-label">{{ stat.label }}</p>
              <p class="act-stat-value">{{ animatedStats[stat.key] ?? stat.value }}</p>
            </div>
          </div>
          <div class="act-stat-bar" :style="`width:${stat.pct}%`"></div>
        </div>
      </div>

      <!-- ═══ Filters Panel ═══ -->
      <div class="act-filters-card">
        <div class="act-filters-header" @click="filtersExpanded = !filtersExpanded">
          <span class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <i class="fa-solid fa-sliders"></i> Filters
            <span v-if="activeFilterCount" class="act-filter-badge">{{ activeFilterCount }}</span>
          </span>
          <i class="fa-solid fa-chevron-down act-chevron" :class="{ 'act-chevron-open': filtersExpanded }"></i>
        </div>

        <Transition name="slide-down">
          <div v-if="filtersExpanded" class="act-filters-body">
            <div class="act-filters-grid">
              <div class="act-field-wrap">
                <label class="act-field-label">Search User</label>
                <div class="act-input-icon-wrap">
                  <i class="fa-solid fa-magnifying-glass act-input-icon"></i>
                  <input v-model="localFilters.search" type="text" placeholder="Name or email…"
                    @keyup.enter="applyFilters" class="act-input act-input-pl" />
                </div>
              </div>
              <div class="act-field-wrap">
                <label class="act-field-label">User</label>
                <select v-model="localFilters.user_id" @change="applyFilters" class="act-input">
                  <option value="">All Users</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
              <div class="act-field-wrap">
                <label class="act-field-label">Action Type</label>
                <select v-model="localFilters.action" @change="applyFilters" class="act-input">
                  <option value="">All Actions</option>
                  <option v-for="act in actions" :key="act" :value="act">{{ formatAction(act) }}</option>
                </select>
              </div>
              <div class="act-field-wrap">
                <label class="act-field-label">From Date</label>
                <input v-model="localFilters.date_from" type="date" class="act-input" @change="applyFilters" />
              </div>
              <div class="act-field-wrap">
                <label class="act-field-label">To Date</label>
                <input v-model="localFilters.date_to" type="date" class="act-input" @change="applyFilters" />
              </div>
              <div class="act-field-wrap">
                <label class="act-field-label">Sort By</label>
                <select v-model="localFilters.sort" @change="applyFilters" class="act-input">
                  <option value="created_at">Date</option>
                  <option value="action">Action</option>
                  <option value="user_id">User</option>
                </select>
              </div>
            </div>
            <div class="act-filter-actions">
              <button @click="applyFilters" class="act-btn act-btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Apply
              </button>
              <button @click="resetFilters" class="act-btn act-btn-ghost">
                <i class="fa-solid fa-rotate-left"></i> Reset
              </button>
              <button @click="toggleDirection" class="act-btn act-btn-ghost">
                <i
                  :class="localFilters.direction === 'desc' ? 'fa-solid fa-arrow-down-wide-short' : 'fa-solid fa-arrow-up-wide-short'"></i>
                {{ localFilters.direction === 'desc' ? 'Newest First' : 'Oldest First' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>

      <!-- ═══ Bulk Action Bar ═══ -->
      <Transition name="slide-down">
        <div v-if="selectedIds.length" class="act-bulk-bar">
          <span class="act-bulk-count">
            <i class="fa-solid fa-circle-check"></i>
            {{ selectedIds.length }} selected
          </span>
          <div class="flex items-center gap-2 flex-wrap">
            <button @click="selectAll" class="act-btn act-btn-ghost act-btn-xs">
              <i class="fa-solid fa-list-check"></i> Select All ({{ activities.data?.length }})
            </button>
            <button @click="deselectAll" class="act-btn act-btn-ghost act-btn-xs">
              <i class="fa-regular fa-square"></i> Deselect
            </button>
            <button @click="bulkDelete" class="act-btn act-btn-danger act-btn-xs" :disabled="submitting">
              <i class="fa-solid fa-trash-can" v-if="!submitting"></i>
              <i class="fa-solid fa-spinner fa-spin" v-else></i>
              Delete Selected
            </button>
          </div>
        </div>
      </Transition>

      <!-- ═══ Table ═══ -->
      <div class="act-table-card">
        <Transition name="fade">
          <div v-if="loading" class="act-loading-overlay">
            <div class="act-spinner"></div>
            <span class="act-loading-text">Loading activities…</span>
          </div>
        </Transition>

        <div class="act-table-scroll">
          <table class="act-table">
            <thead>
              <tr>
                <th class="act-th act-th-check">
                  <input type="checkbox" class="act-checkbox" :checked="allOnPageSelected"
                    :indeterminate.prop="someSelected" @change="togglePageSelection" />
                </th>
                <th class="act-th">#</th>
                <th class="act-th act-sortable" @click="setSort('user_id')">
                  User <i :class="sortIcon('user_id')"></i>
                </th>
                <th class="act-th act-sortable" @click="setSort('action')">
                  Action <i :class="sortIcon('action')"></i>
                </th>
                <th class="act-th hidden md:table-cell">Entity</th>
                <th class="act-th hidden lg:table-cell">Details</th>
                <th class="act-th hidden xl:table-cell">IP Address</th>
                <th class="act-th act-sortable" @click="setSort('created_at')">
                  Time <i :class="sortIcon('created_at')"></i>
                </th>
                <th class="act-th text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(activity, idx) in activities.data" :key="activity.id" class="act-tr"
                :class="{ 'act-tr-selected': selectedIds.includes(activity.id) }"
                :style="`--row-delay: ${Math.min(idx * 30, 300)}ms`">

                <td class="act-td act-td-check">
                  <input type="checkbox" class="act-checkbox" :value="activity.id" v-model="selectedIds" />
                </td>
                <td class="act-td act-td-num">
                  <span class="act-row-num">{{ (activities.from ?? 0) + idx }}</span>
                </td>
                <td class="act-td">
                  <div class="act-user-cell">
                    <div class="act-avatar" :style="`--av-color: ${avatarColor(activity.user?.name)}`">
                      {{ activity.user?.name?.charAt(0)?.toUpperCase() || 'S' }}
                    </div>
                    <div class="act-user-info">
                      <p class="act-user-name">{{ activity.user?.name || 'System' }}</p>
                      <p class="act-user-email hidden sm:block">{{ activity.user?.email || '—' }}</p>
                    </div>
                  </div>
                </td>
                <td class="act-td">
                  <span class="act-badge" :class="actionBadgeClass(activity.action)">
                    <i :class="actionIcon(activity.action)" class="text-[9px]"></i>
                    {{ formatAction(activity.action) }}
                  </span>
                </td>
                <td class="act-td hidden md:table-cell">
                  <span v-if="activity.entity_type" class="act-entity-chip">
                    <i :class="entityIcon(activity.entity_type)"></i>
                    {{ activity.entity_type }}
                    <span v-if="activity.entity_id" class="text-slate-400">#{{ activity.entity_id }}</span>
                  </span>
                  <span v-else class="text-slate-400 text-xs">—</span>
                </td>
                <td class="act-td hidden lg:table-cell">
                  <p class="act-detail-text" :title="formatDetails(activity.details)">
                    {{ formatDetails(activity.details) }}
                  </p>
                </td>
                <td class="act-td hidden xl:table-cell">
                  <code class="act-ip-code">{{ activity.ip_address || 'N/A' }}</code>
                </td>
                <td class="act-td">
                  <p class="act-time-main">{{ formatDate(activity.created_at) }}</p>
                  <p class="act-time-ago">{{ timeAgo(activity.created_at) }}</p>
                </td>
                <td class="act-td text-right">
                  <div class="act-row-actions">
                    <button @click="viewDetails(activity)" class="act-icon-btn" title="View Details">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                    <button @click="confirmSingleDelete(activity)" class="act-icon-btn act-icon-btn-danger"
                      title="Delete">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!activities.data?.length">
                <td colspan="9" class="act-empty">
                  <div class="act-empty-inner">
                    <div class="act-empty-icon"><i class="fa-solid fa-wave-square"></i></div>
                    <p class="act-empty-title">No activities found</p>
                    <p class="act-empty-sub">Try adjusting your filters or check back later.</p>
                    <button @click="resetFilters" class="act-btn act-btn-ghost mt-3">
                      <i class="fa-solid fa-rotate-left"></i> Clear Filters
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="activities.links?.length > 3" class="act-pagination">
          <span class="act-pagination-info">
            Showing <strong>{{ activities.from }}</strong>–<strong>{{ activities.to }}</strong> of <strong>{{
              activities.total }}</strong>
          </span>
          <Pagination :links="activities.links" :from="activities.from" :to="activities.to" :total="activities.total" />
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════ MODALS ══════════════════════════════════════════ -->
    <Teleport to="body">

      <!-- Clear Old Activities Modal -->
      <Transition name="modal">
        <div v-if="showClearModal" class="act-modal-backdrop" @click.self="showClearModal = false">
          <div class="act-modal">
            <div class="act-modal-header act-modal-header-danger">
              <div class="act-modal-icon-wrap">
                <i class="fa-solid fa-clock-rotate-left"></i>
              </div>
              <div>
                <h3 class="act-modal-title">Clear Old Activities</h3>
                <p class="act-modal-sub">Permanently delete logs older than the selected period</p>
              </div>
              <button @click="showClearModal = false" class="act-modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="act-modal-body">
              <p class="act-modal-desc">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                This action is <strong>irreversible</strong>. Select a time period:
              </p>
              <div class="act-days-grid">
                <button v-for="opt in dayOptions" :key="opt.value" @click="clearDays = opt.value" class="act-day-btn"
                  :class="{ 'act-day-btn-active': clearDays === opt.value }">
                  <span class="act-day-num">{{ opt.value }}</span>
                  <span class="act-day-label">{{ opt.label }}</span>
                </button>
              </div>
              <div class="act-modal-estimate">
                <i class="fa-solid fa-circle-info text-indigo-500"></i>
                Logs older than <strong>{{ clearDays }} days</strong> will be permanently removed.
              </div>
            </div>
            <div class="act-modal-footer">
              <button @click="showClearModal = false" class="act-btn act-btn-ghost flex-1">Cancel</button>
              <button @click="performClear" class="act-btn act-btn-danger flex-1" :disabled="submitting">
                <i class="fa-solid fa-trash" v-if="!submitting"></i>
                <i class="fa-solid fa-spinner fa-spin" v-else></i>
                {{ submitting ? 'Deleting…' : 'Clear Activities' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Filter-Based Delete Modal -->
      <Transition name="modal">
        <div v-if="showFilterDeleteModal" class="act-modal-backdrop" @click.self="showFilterDeleteModal = false">
          <div class="act-modal act-modal-lg">
            <div class="act-modal-header act-modal-header-warning">
              <div class="act-modal-icon-wrap act-modal-icon-warning">
                <i class="fa-solid fa-filter-circle-xmark"></i>
              </div>
              <div>
                <h3 class="act-modal-title">Delete by Filters</h3>
                <p class="act-modal-sub">Remove activities matching specific criteria</p>
              </div>
              <button @click="showFilterDeleteModal = false" class="act-modal-close"><i
                  class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="act-modal-body">
              <p class="act-modal-desc">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                Configure deletion criteria. Only matching records will be removed.
              </p>
              <div class="act-filters-grid">
                <div class="act-field-wrap">
                  <label class="act-field-label">Action Type</label>
                  <select v-model="deleteFilters.action" class="act-input">
                    <option value="">All Actions</option>
                    <option v-for="act in actions" :key="act" :value="act">{{ formatAction(act) }}</option>
                  </select>
                </div>
                <div class="act-field-wrap">
                  <label class="act-field-label">User</label>
                  <select v-model="deleteFilters.user_id" class="act-input">
                    <option value="">All Users</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                  </select>
                </div>
                <div class="act-field-wrap">
                  <label class="act-field-label">From Date</label>
                  <input v-model="deleteFilters.date_from" type="date" class="act-input" />
                </div>
                <div class="act-field-wrap">
                  <label class="act-field-label">To Date</label>
                  <input v-model="deleteFilters.date_to" type="date" class="act-input" />
                </div>
                <div class="act-field-wrap">
                  <label class="act-field-label">Entity Type</label>
                  <select v-model="deleteFilters.entity_type" class="act-input">
                    <option value="">All Entities</option>
                    <option value="report">Report</option>
                    <option value="task">Task</option>
                    <option value="user">User</option>
                    <option value="system">System</option>
                  </select>
                </div>
                <div class="act-field-wrap">
                  <label class="act-field-label">Older Than (days)</label>
                  <input v-model.number="deleteFilters.older_than_days" type="number" min="1" placeholder="e.g. 30"
                    class="act-input" />
                </div>
              </div>
              <div class="act-delete-preview">
                <i class="fa-solid fa-eye text-indigo-500 flex-shrink-0"></i>
                <span>Criteria: <strong>{{ filterDeleteSummary }}</strong></span>
              </div>
            </div>
            <div class="act-modal-footer">
              <button @click="showFilterDeleteModal = false" class="act-btn act-btn-ghost flex-1">Cancel</button>
              <button @click="performFilterDelete" class="act-btn act-btn-warning flex-1"
                :disabled="submitting || !hasDeleteFilters">
                <i class="fa-solid fa-filter-circle-xmark" v-if="!submitting"></i>
                <i class="fa-solid fa-spinner fa-spin" v-else></i>
                {{ submitting ? 'Deleting…' : 'Delete Matching' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Detail View Modal -->
      <Transition name="modal">
        <div v-if="detailActivity" class="act-modal-backdrop" @click.self="detailActivity = null">
          <div class="act-modal act-modal-lg">
            <div class="act-modal-header">
              <div class="act-modal-icon-wrap" :class="actionBadgeClass(detailActivity.action)">
                <i :class="actionIcon(detailActivity.action)"></i>
              </div>
              <div>
                <h3 class="act-modal-title">Activity Detail</h3>
                <p class="act-modal-sub">ID #{{ detailActivity.id }} · {{ timeAgo(detailActivity.created_at) }}</p>
              </div>
              <button @click="detailActivity = null" class="act-modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="act-modal-body">
              <div class="act-detail-grid">
                <div class="act-detail-row">
                  <span class="act-detail-key"><i class="fa-solid fa-user"></i> User</span>
                  <span class="act-detail-val">{{ detailActivity.user?.name || 'System' }}
                    <span class="text-slate-400 text-xs">({{ detailActivity.user?.email }})</span></span>
                </div>
                <div class="act-detail-row">
                  <span class="act-detail-key"><i class="fa-solid fa-bolt"></i> Action</span>
                  <span class="act-detail-val">
                    <span class="act-badge" :class="actionBadgeClass(detailActivity.action)">{{
                      formatAction(detailActivity.action) }}</span>
                  </span>
                </div>
                <div class="act-detail-row">
                  <span class="act-detail-key"><i class="fa-solid fa-cube"></i> Entity</span>
                  <span class="act-detail-val">{{ detailActivity.entity_type || '—' }}
                    {{ detailActivity.entity_id ? `#${detailActivity.entity_id}` : '' }}</span>
                </div>
                <div class="act-detail-row">
                  <span class="act-detail-key"><i class="fa-solid fa-network-wired"></i> IP</span>
                  <span class="act-detail-val"><code class="act-ip-code">{{ detailActivity.ip_address || 'N/A'
                  }}</code></span>
                </div>
                <div class="act-detail-row">
                  <span class="act-detail-key"><i class="fa-solid fa-calendar-clock"></i> Time</span>
                  <span class="act-detail-val">{{ formatDate(detailActivity.created_at) }}</span>
                </div>
              </div>
              <div v-if="detailActivity.details" class="act-detail-json-wrap">
                <p class="act-field-label mb-2"><i class="fa-solid fa-code"></i> Details Payload</p>
                <pre class="act-detail-json">{{ JSON.stringify(detailActivity.details, null, 2) }}</pre>
              </div>
              <div v-if="detailActivity.user_agent" class="mt-4">
                <p class="act-field-label mb-1"><i class="fa-solid fa-browser"></i> User Agent</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 break-all">{{ detailActivity.user_agent }}</p>
              </div>
            </div>
            <div class="act-modal-footer">
              <button @click="detailActivity = null" class="act-btn act-btn-ghost flex-1">Close</button>
              <button @click="confirmSingleDelete(detailActivity); detailActivity = null"
                class="act-btn act-btn-danger">
                <i class="fa-solid fa-trash-can"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </Transition>

    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

// ── Props ─────────────────────────────────────────────────────────────
const props = defineProps({
  activities: { type: Object, default: () => ({}) },
  users: { type: Array, default: () => [] },
  actions: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})


// ── State ─────────────────────────────────────────────────────────────
const loading = ref(false)
const submitting = ref(false)
const showClearModal = ref(false)
const showFilterDeleteModal = ref(false)
// Snapshot the days value at modal-open time so the confirm callback
// always uses the value the user actually selected, not a stale ref.
const clearDays = ref(90)
const filtersExpanded = ref(true)
const selectedIds = ref([])
const detailActivity = ref(null)

const localFilters = reactive({
  search: props.filters?.search || '',
  user_id: props.filters?.user_id || '',
  action: props.filters?.action || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
  sort: props.filters?.sort || 'created_at',
  direction: props.filters?.direction || 'desc',
})

const deleteFilters = reactive({
  action: '',
  user_id: '',
  date_from: '',
  date_to: '',
  entity_type: '',
  older_than_days: '',
})

// ── Memory-safe animated stats counter ───────────────────────────────
const animatedStats = reactive({})
const _rafIds = []

function animateCount(key, target) {
  const duration = 800
  const start = Date.now()
  let rafId
  const tick = () => {
    const elapsed = Date.now() - start
    const progress = Math.min(elapsed / duration, 1)
    const ease = 1 - Math.pow(1 - progress, 3)
    animatedStats[key] = Math.round(target * ease)
    if (progress < 1) {
      rafId = requestAnimationFrame(tick)
      _rafIds.push(rafId)
    }
  }
  rafId = requestAnimationFrame(tick)
  _rafIds.push(rafId)
}

onMounted(() => {
  // Initial animation on first page load
  animateCount('total', props.stats?.total ?? 0)
  animateCount('today', props.stats?.today ?? 0)
  animateCount('this_week', props.stats?.this_week ?? 0)
  animateCount('this_month', props.stats?.this_month ?? 0)
  document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
  _rafIds.forEach(id => cancelAnimationFrame(id))
  _rafIds.length = 0
  document.removeEventListener('keydown', onKeydown)
})

// FIX: Re-animate stat cards every time props.stats changes.
// Inertia partial reloads (only:['activities','stats','flash']) update
// props.stats reactively, but animatedStats won't update unless we watch.
watch(
  () => props.stats,
  (newStats) => {
    if (!newStats) return
    animateCount('total', newStats.total ?? 0)
    animateCount('today', newStats.today ?? 0)
    animateCount('this_week', newStats.this_week ?? 0)
    animateCount('this_month', newStats.this_month ?? 0)
  },
  { deep: true }
)

// ── Stats cards ───────────────────────────────────────────────────────
const statsCards = computed(() => {
  const t = props.stats?.total || 1
  return [
    { label: 'Total', key: 'total', value: props.stats?.total, icon: 'fa-solid fa-chart-line', cls: 'act-stat-total', pct: 100 },
    { label: 'Today', key: 'today', value: props.stats?.today, icon: 'fa-solid fa-sun', cls: 'act-stat-today', pct: Math.round(((props.stats?.today || 0) / t) * 100) },
    { label: 'This Week', key: 'this_week', value: props.stats?.this_week, icon: 'fa-solid fa-calendar-week', cls: 'act-stat-week', pct: Math.round(((props.stats?.this_week || 0) / t) * 100) },
    { label: 'This Month', key: 'this_month', value: props.stats?.this_month, icon: 'fa-solid fa-calendar-days', cls: 'act-stat-month', pct: Math.round(((props.stats?.this_month || 0) / t) * 100) },
  ]
})

const dayOptions = [
  { value: 7, label: 'days' },
  { value: 30, label: 'days' },
  { value: 60, label: 'days' },
  { value: 90, label: 'days' },
  { value: 180, label: 'days' },
  { value: 365, label: '1 year' },
]

// ── Computed helpers ──────────────────────────────────────────────────
const activeFilterCount = computed(() =>
  Object.entries(localFilters)
    .filter(([k, v]) => !['sort', 'direction'].includes(k) && v !== '' && v !== null && v !== undefined)
    .length
)

const pageActivityIds = computed(() => (props.activities.data || []).map(a => a.id))

const allOnPageSelected = computed(() =>
  pageActivityIds.value.length > 0 &&
  pageActivityIds.value.every(id => selectedIds.value.includes(id))
)

const someSelected = computed(() =>
  selectedIds.value.length > 0 && !allOnPageSelected.value
)

const hasDeleteFilters = computed(() =>
  Object.values(deleteFilters).some(v => v !== '' && v !== null && v !== undefined && v !== 0)
)

const filterDeleteSummary = computed(() => {
  const parts = []
  if (deleteFilters.action) parts.push(`Action: ${formatAction(deleteFilters.action)}`)
  if (deleteFilters.user_id) parts.push(`User ID: ${deleteFilters.user_id}`)
  if (deleteFilters.date_from) parts.push(`From: ${deleteFilters.date_from}`)
  if (deleteFilters.date_to) parts.push(`To: ${deleteFilters.date_to}`)
  if (deleteFilters.entity_type) parts.push(`Entity: ${deleteFilters.entity_type}`)
  if (deleteFilters.older_than_days) parts.push(`Older than ${deleteFilters.older_than_days}d`)
  return parts.length ? parts.join(' · ') : 'No criteria set'
})

// ── Pure helpers ──────────────────────────────────────────────────────
function avatarColor(name) {
  const colors = ['#6366f1', '#8b5cf6', '#ec4899', '#14b8a6', '#f59e0b', '#ef4444', '#3b82f6', '#10b981']
  if (!name) return colors[0]
  return colors[name.charCodeAt(0) % colors.length]
}
function formatAction(action) {
  return action?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) || action
}
function formatDate(date) {
  return new Date(date).toLocaleString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
  })
}
function timeAgo(date) {
  const s = Math.floor((Date.now() - new Date(date)) / 1000)
  if (s < 60) return 'just now'
  if (s < 3600) return `${Math.floor(s / 60)}m ago`
  if (s < 86400) return `${Math.floor(s / 3600)}h ago`
  return `${Math.floor(s / 86400)}d ago`
}
function formatDetails(details) {
  if (!details) return 'No details'
  if (typeof details === 'string') { try { details = JSON.parse(details) } catch { return details } }
  if (details.report_title) return `Report: ${details.report_title}`
  if (details.task_title) return `Task: ${details.task_title}`
  if (details.user_name) return `User: ${details.user_name}`
  if (details.deleted_count !== undefined) return `Deleted ${details.deleted_count} records`
  const entries = Object.entries(details)
  if (!entries.length) return 'No details'
  return entries.slice(0, 2).map(([k, v]) => `${k}: ${v}`).join(' · ')
}
function actionBadgeClass(action) {
  if (!action) return 'act-badge-default'
  if (action.includes('creat') || action.includes('add') || action.includes('register')) return 'act-badge-green'
  if (action.includes('updat') || action.includes('edit') || action.includes('modif') || action.includes('status')) return 'act-badge-blue'
  if (action.includes('delet') || action.includes('remov') || action.includes('clear')) return 'act-badge-red'
  if (action.includes('login') || action.includes('logout') || action.includes('auth')) return 'act-badge-purple'
  if (action.includes('export') || action.includes('download') || action.includes('share')) return 'act-badge-teal'
  if (action.includes('assign') || action.includes('transfer')) return 'act-badge-orange'
  return 'act-badge-default'
}
function actionIcon(action) {
  if (!action) return 'fa-solid fa-circle'
  if (action.includes('creat') || action.includes('add')) return 'fa-solid fa-plus'
  if (action.includes('updat') || action.includes('edit')) return 'fa-solid fa-pen'
  if (action.includes('delet') || action.includes('clear')) return 'fa-solid fa-trash'
  if (action.includes('login')) return 'fa-solid fa-right-to-bracket'
  if (action.includes('logout')) return 'fa-solid fa-right-from-bracket'
  if (action.includes('export') || action.includes('download')) return 'fa-solid fa-download'
  if (action.includes('assign')) return 'fa-solid fa-user-check'
  return 'fa-solid fa-bolt'
}
function entityIcon(type) {
  const map = { report: 'fa-solid fa-file-lines', task: 'fa-solid fa-list-check', user: 'fa-solid fa-user', system: 'fa-solid fa-server', template: 'fa-solid fa-layer-group' }
  return map[type] || 'fa-solid fa-cube'
}
function sortIcon(col) {
  if (localFilters.sort !== col) return 'fa-solid fa-sort text-slate-300'
  return localFilters.direction === 'asc' ? 'fa-solid fa-sort-up text-indigo-500' : 'fa-solid fa-sort-down text-indigo-500'
}

// Strips empty/null/undefined values — keeps URL clean & controller logic unambiguous
function cleanParams(obj) {
  return Object.fromEntries(
    Object.entries(obj).filter(([, v]) => v !== '' && v !== null && v !== undefined)
  )
}

// ── Navigation ────────────────────────────────────────────────────────
function applyFilters() {
  loading.value = true
  router.get(route('admin.activities.index'), cleanParams(localFilters), {
    preserveState: true,
    preserveScroll: true,
    only: ['activities', 'stats', 'flash'],
    onFinish: () => { loading.value = false },
  })
}
function resetFilters() {
  Object.assign(localFilters, { search: '', user_id: '', action: '', date_from: '', date_to: '', sort: 'created_at', direction: 'desc' })
  applyFilters()
}
function setSort(col) {
  localFilters.direction = localFilters.sort === col
    ? (localFilters.direction === 'asc' ? 'desc' : 'asc')
    : 'desc'
  localFilters.sort = col
  applyFilters()
}
function toggleDirection() {
  localFilters.direction = localFilters.direction === 'desc' ? 'asc' : 'desc'
  applyFilters()
}
function exportActivities() {
  const params = new URLSearchParams(cleanParams(localFilters)).toString()
  window.open(route('admin.activities.export') + (params ? '?' + params : ''), '_blank')
  window.showToast('CSV export started — check your downloads.', 'success')
}

// ── Selection ─────────────────────────────────────────────────────────
function togglePageSelection(e) { e.target.checked ? selectAll() : deselectAll() }
function selectAll() { pageActivityIds.value.forEach(id => { if (!selectedIds.value.includes(id)) selectedIds.value.push(id) }) }
function deselectAll() { selectedIds.value = selectedIds.value.filter(id => !pageActivityIds.value.includes(id)) }

// Modals
function openClearModal() { showClearModal.value = true }
function openFilterDeleteModal() { showFilterDeleteModal.value = true }
function viewDetails(activity) { detailActivity.value = activity }

// ══════════════════════════════════════════════════════════════════════
//  DELETE OPERATIONS
//
//  KEY FIX — router.delete correct Inertia v1 signature:
//    router.delete(url, options)          ← options object contains data:
//
//  KEY FIX — toasts are shown via the flash watcher above, NOT inside
//  onSuccess. This is because:
//    1. showAlert's onConfirm fires after a 220ms close animation.
//    2. Inertia's onSuccess fires after the server round-trip.
//    3. Both are async; reading flash from usePage() watch is the only
//       guaranteed-correct approach in Inertia + Vue 3.
//
//  submitting flag is still managed here for button disabled states.
// ══════════════════════════════════════════════════════════════════════

// SINGLE DELETE
function confirmSingleDelete(activity) {
  const name = activity.user?.name || 'System'
  window.showAlert({
    type: 'danger',
    title: 'Delete Activity',
    message: `Delete the <strong>${formatAction(activity.action)}</strong> log by <strong>${name}</strong>?<br>This cannot be undone.`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    onConfirm: () => {
      submitting.value = true
      router.delete(route('admin.activities.clear'), {
        data: { ids: [activity.id] },
        preserveScroll: true,
        onSuccess: () => {
          submitting.value = false
          window.showToast('Activity deleted successfully.', 'success')
        },
        onError: () => {
          submitting.value = false
          window.showToast('Failed to delete. Please try again.', 'error')
        },
      })
    },
  })
}

// BULK DELETE
function bulkDelete() {
  if (!selectedIds.value.length) return
  // Snapshot BEFORE any async work — avoids race condition on ref clear
  const idsToDelete = [...selectedIds.value]
  const count = idsToDelete.length
  window.showAlert({
    type: 'danger',
    title: 'Bulk Delete Activities',
    message: `Permanently delete <strong>${count}</strong> selected ${count === 1 ? 'activity' : 'activities'}?<br>This cannot be undone.`,
    confirmText: `Delete ${count} ${count === 1 ? 'Record' : 'Records'}`,
    cancelText: 'Cancel',
    onConfirm: () => {
      submitting.value = true
      router.delete(route('admin.activities.clear'), {
        data: { ids: idsToDelete },
        preserveScroll: true,
        onSuccess: () => {
          submitting.value = false
          selectedIds.value = []
          window.showToast(`${count} ${count === 1 ? 'activity' : 'activities'} deleted successfully.`, 'success')
        },
        onError: () => {
          submitting.value = false
          window.showToast('Bulk delete failed. Please try again.', 'error')
        },
      })
    },
  })
}

// CLEAR BY DAYS
// FIX: snapshot clearDays.value into a plain number BEFORE passing to
// showAlert — the ref value must not be read inside the async onConfirm
// closure because the modal might reset it before the callback fires.
function performClear() {
  const days = clearDays.value   // ← plain number snapshot, not a ref
  window.showAlert({
    type: 'danger',
    title: 'Clear Old Activities',
    message: `Delete all activities older than <strong>${days} days</strong>?<br>This is permanent and cannot be undone.`,
    confirmText: 'Yes, Clear',
    cancelText: 'Cancel',
    onConfirm: () => {
      submitting.value = true
      // FIX: send days as integer — cast explicitly so PHP intval() works
      router.delete(route('admin.activities.clear'), {
        data: { days: parseInt(days, 10) },
        preserveScroll: true,
        onSuccess: () => {
          submitting.value = false
          showClearModal.value = false
          window.showToast(`Activities older than ${days} days cleared successfully.`, 'success')
        },
        onError: () => {
          submitting.value = false
          window.showToast('Failed to clear activities. Please try again.', 'error')
        },
      })
    },
  })
}

// FILTER DELETE
// FIX: cleanParams removes empty-string values so the controller's
// mode-detection (has('days') vs has('ids') vs filter mode) works correctly.
// FIX: older_than_days cast to integer so PHP validation passes.
function performFilterDelete() {
  if (!hasDeleteFilters.value) {
    window.showToast('Set at least one filter criterion before deleting.', 'error')
    return
  }
  // Build payload — cast older_than_days to int if present, strip blanks
  const raw = { ...deleteFilters }
  if (raw.older_than_days) raw.older_than_days = parseInt(raw.older_than_days, 10)
  const payload = cleanParams(raw)

  window.showAlert({
    type: 'warning',
    title: 'Filter-Based Deletion',
    message: `Delete all activities matching:<br><strong>${filterDeleteSummary.value}</strong><br><br>This action is permanent.`,
    confirmText: 'Yes, Delete Matching',
    cancelText: 'Cancel',
    onConfirm: () => {
      submitting.value = true
      router.delete(route('admin.activities.clear'), {
        data: payload,
        preserveScroll: true,
        onSuccess: () => {
          submitting.value = false
          showFilterDeleteModal.value = false
          Object.keys(deleteFilters).forEach(k => { deleteFilters[k] = '' })
          window.showToast('Matching activities deleted successfully.', 'success')
        },
        onError: (errors) => {
          submitting.value = false
          const msg = errors ? Object.values(errors)[0] : null
          window.showToast(msg || 'Filter delete failed. Please try again.', 'error')
        },
      })
    },
  })
}

// ── Keyboard shortcuts ────────────────────────────────────────────────
function onKeydown(e) {
  if (e.key === 'Escape') {
    showClearModal.value = false
    showFilterDeleteModal.value = false
    detailActivity.value = null
  }
}
</script>

<style scoped>
.act-page {
  @apply py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto space-y-4 sm:space-y-5;
}

.act-title {
  @apply flex items-center gap-2 text-xl sm:text-2xl font-bold text-slate-900 dark:text-white;
}

.act-title-icon {
  @apply flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-sm;
}

.act-subtitle {
  @apply text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1;
}

.act-btn {
  @apply inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 whitespace-nowrap cursor-pointer;
}

.act-btn:disabled {
  @apply opacity-50 cursor-not-allowed pointer-events-none;
}

.act-btn-xs {
  @apply px-2.5 py-1.5 text-xs;
}

.act-btn-primary {
  @apply bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white shadow-sm;
}

.act-btn-ghost {
  @apply border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700;
}

.act-btn-danger {
  @apply bg-red-600 hover:bg-red-700 active:scale-95 text-white shadow-sm;
}

.act-btn-warning {
  @apply bg-amber-500 hover:bg-amber-600 active:scale-95 text-white shadow-sm;
}

.act-stats-grid {
  @apply grid grid-cols-2 sm:grid-cols-4 gap-3;
}

.act-stat-card {
  @apply relative bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700 overflow-hidden;
  animation: stat-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
  animation-delay: var(--delay, 0ms);
}

.act-stat-inner {
  @apply flex items-center gap-3;
}

.act-stat-icon-wrap {
  @apply flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl text-sm flex-shrink-0;
}

.act-stat-label {
  @apply text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium mb-0.5;
}

.act-stat-value {
  @apply text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tabular-nums;
}

.act-stat-bar {
  @apply absolute bottom-0 left-0 h-0.5 rounded-full;
  animation: bar-grow 1s ease-out both;
  animation-delay: calc(var(--delay, 0ms) + 300ms);
}

.act-stat-total .act-stat-icon-wrap {
  @apply bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400;
}

.act-stat-total .act-stat-bar {
  @apply bg-indigo-500;
}

.act-stat-today .act-stat-icon-wrap {
  @apply bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400;
}

.act-stat-today .act-stat-bar {
  @apply bg-amber-500;
}

.act-stat-week .act-stat-icon-wrap {
  @apply bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400;
}

.act-stat-week .act-stat-bar {
  @apply bg-emerald-500;
}

.act-stat-month .act-stat-icon-wrap {
  @apply bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400;
}

.act-stat-month .act-stat-bar {
  @apply bg-violet-500;
}

.act-filters-card {
  @apply bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden;
}

.act-filters-header {
  @apply flex items-center justify-between px-4 py-3 cursor-pointer select-none hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors;
}

.act-filter-badge {
  @apply inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold;
}

.act-chevron {
  @apply text-slate-400 transition-transform duration-300 text-xs;
}

.act-chevron-open {
  transform: rotate(180deg);
}

.act-filters-body {
  @apply px-4 pb-4 border-t border-slate-100 dark:border-slate-700/50 pt-4;
}

.act-filters-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4;
}

.act-filter-actions {
  @apply flex items-center gap-2 flex-wrap;
}

.act-field-wrap {
  @apply flex flex-col gap-1;
}

.act-field-label {
  @apply text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400;
}

.act-input {
  @apply w-full px-3 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all duration-200;
}

.act-input-icon-wrap {
  @apply relative;
}

.act-input-icon {
  @apply absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none;
}

.act-input-pl {
  @apply pl-8;
}

.act-bulk-bar {
  @apply flex items-center justify-between gap-3 flex-wrap bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-2xl px-4 py-3;
}

.act-bulk-count {
  @apply flex items-center gap-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300;
}

.act-table-card {
  @apply relative bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden;
}

.act-table-scroll {
  @apply overflow-x-auto;
}

.act-table {
  @apply w-full border-collapse;
}

.act-th {
  @apply px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap;
}

.act-th-check {
  @apply w-10 px-3 sm:px-4;
}

.act-sortable {
  @apply cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 select-none transition-colors;
}

.act-td {
  @apply px-3 sm:px-4 py-3 sm:py-3.5 text-sm text-slate-700 dark:text-slate-300 align-middle;
}

.act-td-check {
  @apply w-10;
}

.act-td-num {
  @apply w-12;
}

.act-tr {
  @apply border-b border-slate-100 dark:border-slate-700/50 transition-all duration-150;
  animation: row-in 0.3s ease-out both;
  animation-delay: var(--row-delay, 0ms);
}

.act-tr:last-child {
  @apply border-b-0;
}

.act-tr:hover {
  @apply bg-slate-50/80 dark:bg-slate-700/40;
}

.act-tr-selected {
  background-color: rgb(238 242 255 / 0.7) !important;
}

.dark .act-tr-selected {
  background-color: rgb(49 46 129 / 0.2) !important;
}

.act-checkbox {
  @apply w-4 h-4 rounded border-slate-300 dark:border-slate-600 accent-indigo-600 cursor-pointer;
}

.act-row-num {
  @apply inline-flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-[10px] font-mono font-semibold;
}

.act-user-cell {
  @apply flex items-center gap-2.5;
}

.act-avatar {
  @apply flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold;
  background: var(--av-color, #6366f1);
}

.act-user-info {
  @apply min-w-0;
}

.act-user-name {
  @apply text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[120px] sm:max-w-[160px];
}

.act-user-email {
  @apply text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[120px] sm:max-w-[160px];
}

.act-badge {
  @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold whitespace-nowrap;
}

.act-badge-green {
  @apply bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400;
}

.act-badge-blue {
  @apply bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400;
}

.act-badge-red {
  @apply bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400;
}

.act-badge-purple {
  @apply bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400;
}

.act-badge-teal {
  @apply bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-400;
}

.act-badge-orange {
  @apply bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-400;
}

.act-badge-default {
  @apply bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300;
}

.act-entity-chip {
  @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-mono;
}

.act-detail-text {
  @apply text-xs text-slate-600 dark:text-slate-400 max-w-[200px] truncate cursor-help;
}

.act-ip-code {
  @apply text-[10px] sm:text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-md font-mono;
}

.act-time-main {
  @apply text-xs font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap;
}

.act-time-ago {
  @apply text-[10px] text-slate-400 dark:text-slate-500 mt-0.5;
}

.act-row-actions {
  @apply flex items-center justify-end gap-1;
}

.act-icon-btn {
  @apply flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-400 dark:text-slate-500 text-xs hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-all duration-150;
}

.act-icon-btn-danger {
  @apply hover:border-red-300 dark:hover:border-red-700 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30;
}

.act-empty {
  @apply py-16 text-center;
}

.act-empty-inner {
  @apply flex flex-col items-center;
}

.act-empty-icon {
  @apply flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-300 dark:text-slate-600 text-3xl mb-4;
}

.act-empty-title {
  @apply text-base font-semibold text-slate-500 dark:text-slate-400 mb-1;
}

.act-empty-sub {
  @apply text-sm text-slate-400 dark:text-slate-500;
}

.act-pagination {
  @apply flex items-center justify-between gap-4 flex-wrap px-4 py-3 border-t border-slate-100 dark:border-slate-700;
}

.act-pagination-info {
  @apply text-xs text-slate-500 dark:text-slate-400;
}

.act-loading-overlay {
  @apply absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl;
}

.act-spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid;
  border-color: rgb(226 232 240);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

.dark .act-spinner {
  border-color: rgb(71 85 105);
  border-top-color: #6366f1;
}

.act-loading-text {
  @apply text-sm text-slate-500 dark:text-slate-400 font-medium;
}

/* Modals */
.act-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
}

.act-modal {
  @apply relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md;
  border: 1px solid rgba(148, 163, 184, 0.15);
}

.act-modal-lg {
  @apply max-w-xl;
}

.act-modal-header {
  @apply flex items-center gap-3 p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700;
}

.act-modal-header-danger .act-modal-icon-wrap {
  @apply bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400;
}

.act-modal-header-warning .act-modal-icon-wrap {
  @apply bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400;
}

.act-modal-icon-wrap {
  @apply flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-lg;
}

.act-modal-icon-warning {
  @apply bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400;
}

.act-modal-title {
  @apply text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-tight;
}

.act-modal-sub {
  @apply text-xs text-slate-500 dark:text-slate-400 mt-0.5;
}

.act-modal-close {
  @apply ml-auto flex items-center justify-center w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all text-sm;
}

.act-modal-body {
  @apply p-4 sm:p-5;
}

.act-modal-desc {
  @apply flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-4;
}

.act-modal-footer {
  @apply flex items-center gap-3 px-4 sm:px-5 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 rounded-b-2xl;
}

.act-days-grid {
  @apply grid grid-cols-3 gap-2 mb-4;
}

.act-day-btn {
  @apply flex flex-col items-center justify-center gap-0.5 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all duration-150 cursor-pointer;
}

.act-day-btn-active {
  @apply border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 shadow-sm;
}

.act-day-num {
  @apply text-lg font-bold leading-none;
}

.act-day-label {
  @apply text-[10px] font-medium;
}

.act-modal-estimate {
  @apply flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 rounded-xl px-3 py-2;
}

.act-delete-preview {
  @apply flex items-start gap-2 text-xs text-slate-600 dark:text-slate-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-3 py-2 mt-4;
}

.act-detail-grid {
  @apply space-y-2 mb-4;
}

.act-detail-row {
  @apply flex items-start gap-3 py-2 border-b border-slate-100 dark:border-slate-700 last:border-0;
}

.act-detail-key {
  @apply flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28 flex-shrink-0;
}

.act-detail-val {
  @apply text-sm text-slate-800 dark:text-slate-200 flex-1;
}

.act-detail-json-wrap {
  @apply mt-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-200 dark:border-slate-700;
}

.act-detail-json {
  @apply text-[11px] font-mono text-slate-700 dark:text-slate-300 overflow-auto max-h-40 leading-relaxed;
}

/* ─── Action Bar (replaces header slot buttons) ─────────────────── */
.act-action-bar {
  @apply flex items-center justify-between gap-3 flex-wrap bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 py-3;
}

.act-action-bar-left {
  @apply flex items-center gap-2 min-w-0;
}

.act-action-bar-title {
  @apply text-sm font-bold text-slate-800 dark:text-slate-200 hidden sm:block;
}

.act-action-bar-count {
  @apply text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full font-medium hidden sm:block;
}

/* Transitions */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
  max-height: 0;
}

.slide-down-enter-to,
.slide-down-leave-from {
  opacity: 1;
  transform: translateY(0);
  max-height: 600px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-enter-active {
  animation: modal-in 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-leave-active {
  animation: modal-out 0.2s ease-in forwards;
}

@keyframes stat-in {
  from {
    opacity: 0;
    transform: translateY(12px) scale(0.97);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes bar-grow {
  from {
    transform: scaleX(0);
    transform-origin: left;
  }

  to {
    transform: scaleX(1);
    transform-origin: left;
  }
}

@keyframes row-in {
  from {
    opacity: 0;
    transform: translateX(-6px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes modal-in {
  from {
    opacity: 0;
    transform: scale(0.94) translateY(10px);
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes modal-out {
  from {
    opacity: 1;
    transform: scale(1);
  }

  to {
    opacity: 0;
    transform: scale(0.96);
  }
}
</style>