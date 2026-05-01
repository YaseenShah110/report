<!-- resources/js/Pages/Dashboard.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            Dashboard
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Welcome back, {{ firstName }}! Here's your report summary.
          </p>
        </div>
        <Link :href="route('reports.create')" 
          class="group relative inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:scale-105"
          :style="{ background: `linear-gradient(135deg, ${accentColor}, ${accentColor}cc)`, borderRadius: `${borderRadius}px` }">
          <i class="fa-solid fa-plus"></i>
          <span>New Report</span>
          <div class="absolute inset-0 rounded-xl bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        </Link>
      </div>
    </template>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
      
      <!-- Premium Banner -->
      <div v-if="$page.props.auth.user?.is_premium" 
           class="mb-6 p-5 rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white shadow-xl relative overflow-hidden group"
           :style="{ borderRadius: `${borderRadius}px` }">
        <div class="absolute inset-0 bg-white/20 transform -skew-x-12 translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
        <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center" :style="{ borderRadius: `${borderRadius}px` }">
              <i class="fa-solid fa-crown text-2xl"></i>
            </div>
            <div>
              <p class="font-bold text-xl">Premium Member</p>
              <p class="text-sm opacity-90">You have access to all AI features and advanced analytics</p>
            </div>
          </div>
          <span class="px-4 py-1.5 bg-white/20 backdrop-blur rounded-full text-xs font-semibold">Active Plan</span>
        </div>
      </div>
      
      <!-- Upgrade Banner -->
      <div v-else class="mb-6 p-5 rounded-2xl bg-gradient-to-r from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 border border-slate-200 dark:border-slate-600 shadow-lg relative overflow-hidden group cursor-pointer"
           :style="{ borderRadius: `${borderRadius}px` }"
           @click="upgradeToPremium">
        <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-orange-500/10 transform -skew-x-12 translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
        <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg" :style="{ borderRadius: `${borderRadius}px` }">
              <i class="fa-solid fa-gem text-2xl text-white"></i>
            </div>
            <div>
              <p class="font-bold text-xl text-slate-900 dark:text-white">Upgrade to Premium</p>
              <p class="text-sm text-slate-600 dark:text-slate-400">Unlock AI content generation and advanced features</p>
            </div>
          </div>
          <button class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-amber-500/25 hover:shadow-xl" :style="{ borderRadius: `${borderRadius}px` }">
            Upgrade Now →
          </button>
        </div>
      </div>

      <!-- Overdue Tasks Warning -->
      <div v-if="$page.props.notifications?.overdue_tasks > 0" 
           class="mb-6 p-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-l-4 border-red-500 rounded-xl flex items-center justify-between flex-wrap gap-4 animate-pulse-slow"
           :style="{ borderRadius: `${borderRadius}px` }">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center" :style="{ borderRadius: `${borderRadius}px` }">
            <i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i>
          </div>
          <div>
            <p class="font-semibold text-red-700 dark:text-red-400">⚠️ Overdue Tasks Alert</p>
            <p class="text-sm text-red-600 dark:text-red-300">You have <span class="font-bold text-lg">{{ $page.props.notifications.overdue_tasks }}</span> overdue task(s) that require immediate attention!</p>
          </div>
        </div>
        <Link :href="route('admin.tasks.index', { status: 'overdue' })" 
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-all hover:scale-105" :style="{ borderRadius: `${borderRadius}px` }">
          View Tasks →
        </Link>
      </div>

      <!-- Welcome Widget -->
      <div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 border border-indigo-100 dark:border-indigo-800"
           :style="{ borderRadius: `${borderRadius}px` }">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg" :style="{ borderRadius: `${borderRadius}px` }">
              <i class="fa-solid fa-chart-line text-white text-2xl"></i>
            </div>
            <div>
              <p class="text-slate-600 dark:text-slate-400">Today is</p>
              <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ currentDate }}</p>
              <p class="text-sm text-slate-500">{{ currentTime }}</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-slate-500">Your productivity score</p>
            <div class="flex items-center gap-2">
              <div class="relative w-32 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden" :style="{ borderRadius: `${borderRadius}px` }">
                <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000" :style="{ background: `linear-gradient(90deg, ${accentColor}, ${accentColor}cc)`, width: `${productivityScore}%` }"></div>
              </div>
              <span class="text-lg font-bold" :style="{ color: accentColor }">{{ productivityScore }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div v-for="(stat, index) in statCards" :key="stat.label" 
          class="group bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 animate-slide-up hover:-translate-y-1 cursor-pointer"
          :style="{ animationDelay: `${index * 100}ms`, borderRadius: `${borderRadius}px` }"
          @click="stat.onClick">
          <div class="flex items-center justify-between mb-4">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:rotate-6"
                 :style="{ backgroundColor: `${stat.iconBg}`, borderRadius: `${borderRadius}px` }">
              <i :class="[stat.icon, stat.iconColor]" class="text-2xl"></i>
            </div>
            <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700" :style="{ borderRadius: `${borderRadius}px` }">
              <i :class="stat.trend >= 0 ? 'fa-solid fa-arrow-up text-emerald-500' : 'fa-solid fa-arrow-down text-red-500'" class="text-xs"></i>
              <span :class="stat.trend >= 0 ? 'text-emerald-600' : 'text-red-600'" class="text-xs font-semibold">{{ Math.abs(stat.trend) }}%</span>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</p>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ stat.label }}</p>
          <div class="mt-4 h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden" :style="{ borderRadius: `${borderRadius}px` }">
            <div class="h-full rounded-full transition-all duration-1000 group-hover:opacity-80" :class="stat.progressBar" :style="{ width: stat.progress }"></div>
          </div>
          <p class="text-xs text-slate-400 mt-2">{{ stat.sub }}</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Reports Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300"
             :style="{ borderRadius: `${borderRadius}px` }">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Report Activity</h3>
              <p class="text-xs text-slate-500 mt-1">Last 30 days</p>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full" :style="{ background: accentColor }"></div>
              <span class="text-xs text-slate-500">Reports Created</span>
            </div>
          </div>
          <div class="h-72">
            <canvas ref="reportsChart"></canvas>
          </div>
        </div>

        <!-- Task Completion -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300"
             :style="{ borderRadius: `${borderRadius}px` }">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Task Completion</h3>
              <p class="text-xs text-slate-500 mt-1">This month</p>
            </div>
            <div class="relative w-24 h-24">
              <svg class="w-full h-full transform -rotate-90">
                <circle cx="48" cy="48" r="42" stroke="currentColor" stroke-width="8" fill="none" class="text-slate-200 dark:text-slate-700"/>
                <circle cx="48" cy="48" r="42" stroke="currentColor" stroke-width="8" fill="none" 
                  :stroke-dasharray="`${taskCompletionRate * 2.64} 264`"
                  :style="{ stroke: accentColor }"/>
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ taskCompletionRate }}%</span>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center" :style="{ borderRadius: `${borderRadius}px` }">
                <i class="fa-solid fa-check-circle text-emerald-600 text-lg"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Completed</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.completed_tasks }}</p>
              </div>
            </div>
            <div class="h-12 w-px bg-slate-200 dark:bg-slate-700"></div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center" :style="{ borderRadius: `${borderRadius}px` }">
                <i class="fa-solid fa-clock text-amber-600 text-lg"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Pending</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.pending_tasks }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Second Row Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- User Growth Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300"
             :style="{ borderRadius: `${borderRadius}px` }">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">User Growth</h3>
              <p class="text-xs text-slate-500 mt-1">New users over time</p>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
              <span class="text-xs text-slate-500">New Registrations</span>
            </div>
          </div>
          <div class="h-72">
            <canvas ref="usersChart"></canvas>
          </div>
        </div>

        <!-- Popular Report Types -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300"
             :style="{ borderRadius: `${borderRadius}px` }">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Popular Report Types</h3>
              <p class="text-xs text-slate-500 mt-1">Most used templates</p>
            </div>
          </div>
          <div class="h-72">
            <canvas ref="typesChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Reports & Activities -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Reports -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all duration-300"
             :style="{ borderRadius: `${borderRadius}px` }">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/30">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center" :style="{ borderRadius: `${borderRadius}px` }">
                <i class="fa-solid fa-file-lines text-indigo-600 text-sm"></i>
              </div>
              <h3 class="font-semibold text-slate-900 dark:text-white">Recent Reports</h3>
            </div>
            <Link :href="route('reports.index')" class="text-xs font-semibold flex items-center gap-1 group" :style="{ color: accentColor }">
              View All 
              <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
            </Link>
          </div>
          <div class="divide-y divide-slate-200 dark:divide-slate-700">
            <div v-for="report in recentReports.slice(0,5)" :key="report.id" 
                 class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-300 group cursor-pointer"
                 @click="goToReport(report.slug)">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-all group-hover:scale-110" :style="{ borderRadius: `${borderRadius}px` }"
                    :class="report.status === 'published' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                    <i :class="report.status === 'published' ? 'fa-solid fa-check-circle text-emerald-600' : 'fa-solid fa-pen-fancy text-amber-600'" class="text-xl"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ report.title }}</p>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-xs text-slate-500">{{ formatDate(report.updated_at) }}</span>
                      <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                      <span class="text-xs text-slate-500">{{ report.total_pages || 1 }} pages</span>
                    </div>
                  </div>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-400 group-hover:translate-x-1 transition-all" :style="{ color: accentColor }"></i>
              </div>
            </div>
            <div v-if="!recentReports.length" class="px-6 py-12 text-center">
              <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4" :style="{ borderRadius: `${borderRadius}px` }">
                <i class="fa-solid fa-inbox text-3xl text-slate-400"></i>
              </div>
              <p class="text-sm text-slate-500">No reports yet</p>
              <Link :href="route('reports.create')" class="text-indigo-600 text-sm font-semibold mt-3 inline-flex items-center gap-1 group">
                Create your first report 
                <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
              </Link>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all duration-300"
             :style="{ borderRadius: `${borderRadius}px` }">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center" :style="{ borderRadius: `${borderRadius}px` }">
                <i class="fa-solid fa-clock-rotate-left text-violet-600 text-sm"></i>
              </div>
              <h3 class="font-semibold text-slate-900 dark:text-white">Recent Activity</h3>
            </div>
          </div>
          <div class="divide-y divide-slate-200 dark:divide-slate-700 max-h-[500px] overflow-y-auto">
            <div v-for="activity in recentActivities" :key="activity.created_at" 
                 class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-300">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-300 hover:scale-110" :style="{ borderRadius: `${borderRadius}px` }"
                  :class="getActivityIconClass(activity.action)">
                  <i :class="getActivityIcon(activity.action)" class="text-sm"></i>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ formatActivityMessage(activity) }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <i class="fa-regular fa-clock text-xs text-slate-400"></i>
                    <p class="text-xs text-slate-500">{{ timeAgo(activity.created_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="!recentActivities.length" class="px-6 py-12 text-center">
              <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4" :style="{ borderRadius: `${borderRadius}px` }">
                <i class="fa-solid fa-mug-hot text-3xl text-slate-400"></i>
              </div>
              <p class="text-sm text-slate-500">No recent activity</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="action in quickActions" :key="action.label" 
               @click="action.handler"
               class="group bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300 cursor-pointer hover:-translate-y-1"
               :style="{ borderRadius: `${borderRadius}px` }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all group-hover:scale-110" :style="{ backgroundColor: action.bgColor, borderRadius: `${borderRadius}px` }">
                <i :class="[action.icon, action.iconColor]" class="text-lg"></i>
              </div>
              <div>
                <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ action.label }}</p>
                <p class="text-xs text-slate-500">{{ action.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Chart from 'chart.js/auto'

const page = usePage()
const reportsChart = ref(null)
const usersChart = ref(null)
const typesChart = ref(null)

const props = defineProps({
  recentReports: Array,
  stats: Object,
  recentActivities: Array,
  chartData: Object,
  notifications: Array
})

// Get dynamic styles from localStorage or use defaults
const accentColor = ref('#6366f1')
const borderRadius = ref(12)

const firstName = computed(() => {
  return page.props.auth.user?.name?.split(' ')[0] || 'there'
})

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

const currentTime = computed(() => {
  return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
})

const productivityScore = computed(() => {
  const completed = props.stats?.completed_tasks || 0
  const pending = props.stats?.pending_tasks || 0
  const total = completed + pending
  return total > 0 ? Math.round((completed / total) * 100) : 0
})

const statCards = computed(() => [
  { 
    label: 'Total Reports', 
    value: props.stats?.total_reports || 0, 
    icon: 'fa-solid fa-file-lines',
    iconBg: '#6366f120',
    iconColor: 'text-indigo-600',
    trend: 12,
    progress: `${Math.min(100, (props.stats?.total_reports / 100) * 100)}%`,
    progressBar: 'bg-indigo-500',
    sub: 'All time created',
    onClick: () => router.get(route('reports.index'))
  },
  { 
    label: 'Published Reports', 
    value: props.stats?.published_reports || 0, 
    icon: 'fa-solid fa-globe',
    iconBg: '#10b98120',
    iconColor: 'text-emerald-600',
    trend: 8,
    progress: `${Math.round((props.stats?.published_reports / (props.stats?.total_reports || 1)) * 100)}%`,
    progressBar: 'bg-emerald-500',
    sub: `${Math.round((props.stats?.published_reports / (props.stats?.total_reports || 1)) * 100)}% publish rate`,
    onClick: () => router.get(route('reports.index', { status: 'published' }))
  },
  { 
    label: 'Shared with Me', 
    value: page.props.notifications?.assigned_reports || 0, 
    icon: 'fa-solid fa-share-alt',
    iconBg: '#8b5cf620',
    iconColor: 'text-violet-600',
    trend: 5,
    progress: `${Math.min(100, ((page.props.notifications?.assigned_reports || 0) / 20) * 100)}%`,
    progressBar: 'bg-violet-500',
    sub: 'Reports shared with you',
    onClick: () => router.get(route('reports.assigned'))
  },
  { 
    label: 'Tasks Completed', 
    value: props.stats?.completed_tasks || 0, 
    icon: 'fa-solid fa-check-circle',
    iconBg: '#f59e0b20',
    iconColor: 'text-amber-600',
    trend: 15,
    progress: `${Math.round((props.stats?.completed_tasks / ((props.stats?.completed_tasks || 0) + (props.stats?.pending_tasks || 1))) * 100)}%`,
    progressBar: 'bg-amber-500',
    sub: `${Math.round((props.stats?.completed_tasks / ((props.stats?.completed_tasks || 0) + (props.stats?.pending_tasks || 1))) * 100)}% completion rate`,
    onClick: () => router.get(route('tasks.my'))
  }
])

const quickActions = [
  { label: 'New Report', desc: 'Create from scratch', icon: 'fa-solid fa-plus', bgColor: '#6366f120', iconColor: 'text-indigo-600', handler: () => router.get(route('reports.create')) },
  { label: 'My Tasks', desc: 'View assigned tasks', icon: 'fa-solid fa-tasks', bgColor: '#10b98120', iconColor: 'text-emerald-600', handler: () => router.get(route('tasks.my')) },
  { label: 'Shared Reports', desc: 'Reports shared with you', icon: 'fa-solid fa-share-alt', bgColor: '#8b5cf620', iconColor: 'text-violet-600', handler: () => router.get(route('reports.assigned')) },
  { label: 'Templates', desc: 'Browse templates', icon: 'fa-solid fa-layer-group', bgColor: '#f59e0b20', iconColor: 'text-amber-600', handler: () => router.get(route('templates.index')) }
]

const taskCompletionRate = computed(() => {
  const total = (props.stats?.completed_tasks || 0) + (props.stats?.pending_tasks || 0)
  return total > 0 ? Math.round(((props.stats?.completed_tasks || 0) / total) * 100) : 0
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  const diff = Math.floor((Date.now() - new Date(date)) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff/60)}m ago`
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff/86400)}d ago`
  return new Date(date).toLocaleDateString()
}

const timeAgo = (date) => {
  const seconds = Math.floor((Date.now() - new Date(date)) / 1000)
  if (seconds < 60) return 'just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`
  return new Date(date).toLocaleDateString()
}

const getActivityIcon = (action) => {
  if (action?.includes('created')) return 'fa-solid fa-plus'
  if (action?.includes('updated')) return 'fa-solid fa-pen'
  if (action?.includes('deleted')) return 'fa-solid fa-trash'
  if (action?.includes('shared')) return 'fa-solid fa-share-alt'
  if (action?.includes('assigned')) return 'fa-solid fa-user-check'
  return 'fa-solid fa-info'
}

const getActivityIconClass = (action) => {
  if (action?.includes('created')) return 'bg-green-100 text-green-600 dark:bg-green-900/30'
  if (action?.includes('updated')) return 'bg-blue-100 text-blue-600 dark:bg-blue-900/30'
  if (action?.includes('deleted')) return 'bg-red-100 text-red-600 dark:bg-red-900/30'
  if (action?.includes('shared')) return 'bg-purple-100 text-purple-600 dark:bg-purple-900/30'
  if (action?.includes('assigned')) return 'bg-amber-100 text-amber-600 dark:bg-amber-900/30'
  return 'bg-slate-100 text-slate-600 dark:bg-slate-700'
}

const formatActivityMessage = (activity) => {
  if (activity?.details?.report_title) {
    return `${activity.action?.replace('_', ' ') || ''} report "${activity.details.report_title}"`
  }
  if (activity?.details?.task_title) {
    return `${activity.action?.replace('_', ' ') || ''} task "${activity.details.task_title}"`
  }
  if (activity?.details?.user_name) {
    return `${activity.action?.replace('_', ' ') || ''} user "${activity.details.user_name}"`
  }
  return activity?.action?.replace('_', ' ') || ''
}

const goToReport = (slug) => {
  router.get(route('reports.edit', slug))
}

const upgradeToPremium = () => {
  window.showToast?.('Premium upgrade feature coming soon!', 'info')
}

const loadStyles = () => {
  const savedAccent = localStorage.getItem('accent-color') || '#6366f1'
  const savedRadius = localStorage.getItem('border-radius') || 12
  accentColor.value = savedAccent
  borderRadius.value = parseInt(savedRadius)
}

const initCharts = () => {
  // Reports Chart
  const reportsCtx = document.getElementById('reportsChart')?.getContext('2d')
  if (reportsCtx && props.chartData?.reports_last_30_days) {
    if (reportsChart.value) reportsChart.value.destroy()
    reportsChart.value = new Chart(reportsCtx, {
      type: 'line',
      data: {
        labels: props.chartData.reports_last_30_days.labels,
        datasets: [{
          label: 'Reports Created',
          data: props.chartData.reports_last_30_days.values,
          borderColor: accentColor.value,
          backgroundColor: `${accentColor.value}20`,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: accentColor.value,
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f1f5f9',
            bodyColor: '#94a3b8',
            borderColor: accentColor.value,
            borderWidth: 1,
            cornerRadius: 8,
            padding: 10
          }
        },
        scales: { 
          y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { stepSize: 1 } }, 
          x: { grid: { display: false } } 
        }
      }
    })
  }

  // Users Chart
  const usersCtx = document.getElementById('usersChart')?.getContext('2d')
  if (usersCtx && props.chartData?.user_growth) {
    if (usersChart.value) usersChart.value.destroy()
    usersChart.value = new Chart(usersCtx, {
      type: 'bar',
      data: {
        labels: props.chartData.user_growth.labels,
        datasets: [{
          label: 'New Users',
          data: props.chartData.user_growth.values,
          backgroundColor: '#10b981',
          borderRadius: 8,
          barPercentage: 0.6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f1f5f9',
            bodyColor: '#94a3b8',
            borderColor: '#10b981',
            borderWidth: 1,
            cornerRadius: 8,
            padding: 10
          }
        },
        scales: {
          y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { stepSize: 1 } },
          x: { grid: { display: false } }
        }
      }
    })
  }

  // Types Chart
  const typesCtx = document.getElementById('typesChart')?.getContext('2d')
  if (typesCtx && props.chartData?.popular_report_types) {
    if (typesChart.value) typesChart.value.destroy()
    typesChart.value = new Chart(typesCtx, {
      type: 'doughnut',
      data: {
        labels: props.chartData.popular_report_types.labels,
        datasets: [{
          data: props.chartData.popular_report_types.values,
          backgroundColor: ['#6366f1', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'],
          borderWidth: 0,
          hoverOffset: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12, font: { size: 11 } } },
          tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f1f5f9',
            bodyColor: '#94a3b8',
            borderWidth: 1,
            cornerRadius: 8,
            padding: 10,
            callbacks: {
              label: (context) => {
                const label = context.label || ''
                const value = context.raw || 0
                const total = context.dataset.data.reduce((a, b) => a + b, 0)
                const percentage = ((value / total) * 100).toFixed(1)
                return `${label}: ${value} (${percentage}%)`
              }
            }
          }
        },
        cutout: '60%'
      }
    })
  }
}

onMounted(() => {
  loadStyles()
  initCharts()
})

// Watch for storage changes to update styles dynamically
window.addEventListener('storage', (e) => {
  if (e.key === 'accent-color') {
    accentColor.value = e.newValue || '#6366f1'
    initCharts()
  }
  if (e.key === 'border-radius') {
    borderRadius.value = parseInt(e.newValue || 12)
  }
})
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-slide-up {
  animation: slide-up 0.5s ease-out forwards;
}

@keyframes pulse-slow {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.85;
  }
}
.animate-pulse-slow {
  animation: pulse-slow 2s ease-in-out infinite;
}
</style>