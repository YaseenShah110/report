<!-- resources/js/Pages/Dashboard.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            Dashboard
          </h2>
          <!-- SHARED DATA USAGE - Welcome message with user name -->
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Welcome back, {{ $page.props.auth.user?.name?.split(' ')[0] || 'there' }}! Here's your report summary.
          </p>
        </div>
        <Link :href="route('reports.create')" 
          class="group relative inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 hover:scale-105">
          <i class="fa-solid fa-plus"></i>
          <span>New Report</span>
          <div class="absolute inset-0 rounded-xl bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        </Link>
      </div>
    </template>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
      
      <!-- SHARED DATA USAGE - Premium Banner -->
      <div v-if="$page.props.auth.user?.is_premium" 
           class="mb-6 p-4 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-3">
            <i class="fa-solid fa-crown text-2xl"></i>
            <div>
              <p class="font-bold">Premium Member</p>
              <p class="text-sm opacity-90">You have access to all AI features and advanced analytics</p>
            </div>
          </div>
          <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold">Active Plan</span>
        </div>
      </div>
      
      <!-- SHARED DATA USAGE - Upgrade Banner -->
      <div v-else class="mb-6 p-4 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-3">
            <i class="fa-solid fa-gem text-2xl text-amber-500"></i>
            <div>
              <p class="font-semibold text-slate-900 dark:text-white">Upgrade to Premium</p>
              <p class="text-sm text-slate-500 dark:text-slate-400">Unlock AI content generation and advanced features</p>
            </div>
          </div>
          <button class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
            Upgrade Now →
          </button>
        </div>
      </div>

      <!-- SHARED DATA USAGE - Overdue Tasks Warning -->
      <div v-if="$page.props.notifications?.overdue_tasks > 0" 
           class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
          <i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i>
          <div>
            <p class="font-semibold text-red-700 dark:text-red-400">Overdue Tasks Alert</p>
            <p class="text-sm text-red-600 dark:text-red-300">You have {{ $page.props.notifications.overdue_tasks }} overdue task(s) that require immediate attention!</p>
          </div>
        </div>
        <Link :href="route('admin.tasks.index', { status: 'overdue' })" 
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">
          View Tasks →
        </Link>
      </div>

      <!-- Stats Cards (your existing stats cards - no changes needed) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div v-for="(stat, index) in statCards" :key="stat.label" 
          class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 animate-slide-up"
          :style="{ animationDelay: `${index * 100}ms` }">
          <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110"
              :class="stat.iconBg">
              <i :class="[stat.icon, stat.iconColor]" class="text-xl"></i>
            </div>
            <div class="flex items-center gap-1">
              <i :class="stat.trend >= 0 ? 'fa-solid fa-arrow-up text-emerald-500' : 'fa-solid fa-arrow-down text-red-500'" class="text-xs"></i>
              <span :class="stat.trend >= 0 ? 'text-emerald-600' : 'text-red-600'" class="text-xs font-semibold">{{ Math.abs(stat.trend) }}%</span>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</p>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ stat.label }}</p>
          <div class="mt-3 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-1000" :class="stat.progressBar" :style="{ width: stat.progress }"></div>
          </div>
        </div>
      </div>

      <!-- Charts Row (your existing charts - no changes) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Reports Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-base font-semibold text-slate-900 dark:text-white">Report Activity</h3>
              <p class="text-xs text-slate-500 mt-1">Last 30 days</p>
            </div>
            <div class="flex items-center gap-1">
              <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
              <span class="text-xs text-slate-500">Reports Created</span>
            </div>
          </div>
          <div class="h-64">
            <canvas ref="reportsChart"></canvas>
          </div>
        </div>

        <!-- Task Completion -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-base font-semibold text-slate-900 dark:text-white">Task Completion</h3>
              <p class="text-xs text-slate-500 mt-1">This month</p>
            </div>
            <div class="relative w-20 h-20">
              <svg class="w-full h-full transform -rotate-90">
                <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="6" fill="none" class="text-slate-200 dark:text-slate-700"/>
                <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="6" fill="none" 
                  :stroke-dasharray="`${stats.completed_tasks / (stats.completed_tasks + stats.pending_tasks) * 214} 214`"
                  class="text-indigo-500 transition-all duration-1000"/>
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-xl font-bold text-slate-900 dark:text-white">{{ taskCompletionRate }}%</span>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <i class="fa-solid fa-check-circle text-emerald-600 text-sm"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Completed</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.completed_tasks }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                <i class="fa-solid fa-clock text-amber-600 text-sm"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Pending</p>
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.pending_tasks }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Reports & Activities (your existing - no changes) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Reports -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <i class="fa-solid fa-file-lines text-indigo-600 text-sm"></i>
              </div>
              <h3 class="font-semibold text-slate-900 dark:text-white">Recent Reports</h3>
            </div>
            <Link :href="route('reports.index')" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold flex items-center gap-1">
              View All <i class="fa-solid fa-arrow-right text-xs"></i>
            </Link>
          </div>
          <div class="divide-y divide-slate-200 dark:divide-slate-700">
            <div v-for="report in recentReports.slice(0,5)" :key="report.id" class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                    :class="report.status === 'published' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                    <i :class="report.status === 'published' ? 'fa-solid fa-check-circle text-emerald-600' : 'fa-solid fa-pen-fancy text-amber-600'" class="text-sm"></i>
                  </div>
                  <div>
                    <p class="font-medium text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ report.title }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ formatDate(report.updated_at) }}</p>
                  </div>
                </div>
                <Link :href="route('reports.edit', report.slug)" class="opacity-0 group-hover:opacity-100 transition-all">
                  <i class="fa-solid fa-arrow-right text-indigo-600"></i>
                </Link>
              </div>
            </div>
            <div v-if="!recentReports.length" class="px-6 py-8 text-center">
              <i class="fa-solid fa-inbox text-4xl text-slate-400 mb-3"></i>
              <p class="text-sm text-slate-500">No reports yet</p>
              <Link :href="route('reports.create')" class="text-indigo-600 text-sm font-semibold mt-2 inline-block">Create your first report →</Link>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
              <i class="fa-solid fa-clock-rotate-left text-violet-600 text-sm"></i>
            </div>
            <h3 class="font-semibold text-slate-900 dark:text-white">Recent Activity</h3>
          </div>
          <div class="divide-y divide-slate-200 dark:divide-slate-700 max-h-[400px] overflow-y-auto">
            <div v-for="activity in recentActivities" :key="activity.created_at" class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                  :class="getActivityIconClass(activity.action)">
                  <i :class="getActivityIcon(activity.action)" class="text-xs"></i>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-slate-700 dark:text-slate-300">{{ formatActivityMessage(activity) }}</p>
                  <p class="text-xs text-slate-500 mt-1">{{ timeAgo(activity.created_at) }}</p>
                </div>
              </div>
            </div>
            <div v-if="!recentActivities.length" class="px-6 py-8 text-center">
              <i class="fa-solid fa-mug-hot text-4xl text-slate-400 mb-3"></i>
              <p class="text-sm text-slate-500">No recent activity</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Chart from 'chart.js/auto'

const page = usePage()
const reportsChart = ref(null)

const props = defineProps({
  recentReports: Array,
  stats: Object,
  recentActivities: Array,
  chartData: Object,
  notifications: Array
})

// SHARED DATA USAGE - Get user's first name for welcome message
// (already used in template above)

const statCards = computed(() => [
  { 
    label: 'Total Reports', 
    value: props.stats.total_reports, 
    icon: 'fa-solid fa-file-lines',
    iconBg: 'bg-indigo-100 dark:bg-indigo-900/30',
    iconColor: 'text-indigo-600',
    trend: 12,
    progress: '70%',
    progressBar: 'bg-indigo-500'
  },
  { 
    label: 'Published', 
    value: props.stats.published_reports, 
    icon: 'fa-solid fa-globe',
    iconBg: 'bg-emerald-100 dark:bg-emerald-900/30',
    iconColor: 'text-emerald-600',
    trend: 8,
    progress: `${Math.round((props.stats.published_reports / props.stats.total_reports) * 100)}%`,
    progressBar: 'bg-emerald-500'
  },
  { 
    label: 'Shared with Me', 
    // SHARED DATA USAGE - Use assigned_reports from notifications
    value: page.props.notifications?.assigned_reports || 0, 
    icon: 'fa-solid fa-share-alt',
    iconBg: 'bg-violet-100 dark:bg-violet-900/30',
    iconColor: 'text-violet-600',
    trend: 5,
    progress: '45%',
    progressBar: 'bg-violet-500'
  },
  { 
    label: 'Tasks Completed', 
    value: props.stats.completed_tasks, 
    icon: 'fa-solid fa-check-circle',
    iconBg: 'bg-amber-100 dark:bg-amber-900/30',
    iconColor: 'text-amber-600',
    trend: 15,
    progress: `${Math.round((props.stats.completed_tasks / (props.stats.completed_tasks + props.stats.pending_tasks)) * 100)}%`,
    progressBar: 'bg-amber-500'
  }
])

const taskCompletionRate = computed(() => {
  const total = props.stats.completed_tasks + props.stats.pending_tasks
  return total > 0 ? Math.round((props.stats.completed_tasks / total) * 100) : 0
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
  return 'fa-solid fa-info'
}

const getActivityIconClass = (action) => {
  if (action?.includes('created')) return 'bg-green-100 text-green-600 dark:bg-green-900/30'
  if (action?.includes('updated')) return 'bg-blue-100 text-blue-600 dark:bg-blue-900/30'
  if (action?.includes('deleted')) return 'bg-red-100 text-red-600 dark:bg-red-900/30'
  if (action?.includes('shared')) return 'bg-purple-100 text-purple-600 dark:bg-purple-900/30'
  return 'bg-slate-100 text-slate-600 dark:bg-slate-700'
}

const formatActivityMessage = (activity) => {
  if (activity?.details?.report_title) {
    return `${activity.action?.replace('_', ' ') || ''} report "${activity.details.report_title}"`
  }
  if (activity?.details?.task_title) {
    return `${activity.action?.replace('_', ' ') || ''} task "${activity.details.task_title}"`
  }
  return activity?.action?.replace('_', ' ') || ''
}

const initChart = () => {
  const ctx = document.getElementById('reportsChart')?.getContext('2d')
  if (ctx && props.chartData?.reports_last_30_days) {
    if (reportsChart.value) reportsChart.value.destroy()
    reportsChart.value = new Chart(ctx, {
      type: 'line',
      data: {
        labels: props.chartData.reports_last_30_days.labels,
        datasets: [{
          label: 'Reports Created',
          data: props.chartData.reports_last_30_days.values,
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#6366f1',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
      }
    })
  }
}

onMounted(() => {
  initChart()
})
</script>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-slide-up {
  animation: slide-up 0.5s ease-out forwards;
}
</style>