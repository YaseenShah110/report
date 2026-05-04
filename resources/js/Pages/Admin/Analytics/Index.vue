<!--
  Admin/Analytics/Index.vue - Analytics Dashboard
  -----------------------------------------------------------
  Displays system-wide analytics with charts and statistics.
  Features: Report stats, user stats, task stats, activity stats, sharing stats.
  All charts use Chart.js with responsive design.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Analytics Dashboard</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">View system statistics and insights</p>
        </div>
        <div class="flex items-center gap-2">
          <select v-model="period" @change="changePeriod" class="px-2 sm:px-3 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-xs sm:text-sm">
            <option value="7">Last 7 Days</option>
            <option value="30">Last 30 Days</option>
            <option value="90">Last 90 Days</option>
            <option value="365">Last Year</option>
          </select>
          <button @click="exportAnalytics" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-download text-xs"></i> Export
          </button>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        
        <!-- Reports Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">Total Reports</p>
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
              <i class="fa-solid fa-file-lines text-indigo-600 text-base sm:text-lg"></i>
            </div>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ reportStats.total }}</p>
          <div class="flex items-center gap-2 mt-2">
            <div :class="reportStats.trend >= 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'" class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold">
              <i :class="reportStats.trend >= 0 ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down'" class="text-[8px] mr-1"></i>{{ Math.abs(reportStats.trend) }}%
            </div>
            <span class="text-[10px] sm:text-xs text-slate-500">vs last period</span>
          </div>
        </div>

        <!-- Users Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">Total Users</p>
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
              <i class="fa-solid fa-users text-emerald-600 text-base sm:text-lg"></i>
            </div>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ userStats.total }}</p>
          <p class="text-[10px] sm:text-xs text-slate-500 mt-2">
            <span class="font-semibold text-emerald-600">{{ userStats.new_this_month }}</span> new this month
          </p>
        </div>

        <!-- Tasks Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">Tasks Overview</p>
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
              <i class="fa-solid fa-tasks text-amber-600 text-base sm:text-lg"></i>
            </div>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ taskStats.total }}</p>
          <div class="mt-2">
            <div class="flex justify-between text-[10px] sm:text-xs mb-1">
              <span class="text-slate-500">Completion Rate</span>
              <span class="font-semibold text-emerald-600">{{ taskStats.completion_rate }}%</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
              <div class="bg-emerald-500 h-1.5 rounded-full" :style="{ width: taskStats.completion_rate + '%' }"></div>
            </div>
          </div>
        </div>

        <!-- Activities Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">Total Activities</p>
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
              <i class="fa-solid fa-chart-line text-violet-600 text-base sm:text-lg"></i>
            </div>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ activityStats.total }}</p>
          <p class="text-[10px] sm:text-xs text-slate-500 mt-2">
            <span class="font-semibold text-violet-600">{{ activityStats.last_24h }}</span> in last 24 hours
          </p>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
        <!-- Reports Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white">Reports Created Trend</h3>
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-indigo-500"></span>
              <span class="text-[10px] sm:text-xs text-slate-500">Last {{ period }} days</span>
            </div>
          </div>
          <div class="h-56 sm:h-64">
            <canvas ref="reportsChartRef"></canvas>
          </div>
        </div>

        <!-- User Growth Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white">User Growth</h3>
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-emerald-500"></span>
              <span class="text-[10px] sm:text-xs text-slate-500">New users over time</span>
            </div>
          </div>
          <div class="h-56 sm:h-64">
            <canvas ref="usersChartRef"></canvas>
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Task Activity Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white">Task Activity</h3>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span><span class="text-[10px] sm:text-xs text-slate-500">Created</span></div>
              <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span class="text-[10px] sm:text-xs text-slate-500">Completed</span></div>
            </div>
          </div>
          <div class="h-56 sm:h-64">
            <canvas ref="tasksChartRef"></canvas>
          </div>
        </div>

        <!-- Popular Report Types -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700">
          <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-4">Popular Report Types</h3>
          <div class="h-56 sm:h-64">
            <canvas ref="typesChartRef"></canvas>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  reportStats: Object, userStats: Object, taskStats: Object,
  activityStats: Object, sharingStats: Object, chartData: Object, period: Number
})

const period = ref(props.period || 30)
const reportsChartRef = ref(null)
const usersChartRef = ref(null)
const tasksChartRef = ref(null)
const typesChartRef = ref(null)

let reportsChart = null, usersChart = null, tasksChart = null, typesChart = null

const changePeriod = () => router.get(route('admin.analytics.index'), { period: period.value }, { preserveState: true, preserveScroll: true })
const exportAnalytics = () => window.open(route('admin.analytics.export', { period: period.value, type: 'reports' }), '_blank')

const initCharts = () => {
  // Reports Chart
  if (reportsChartRef.value && props.chartData?.reports_created) {
    if (reportsChart) reportsChart.destroy()
    reportsChart = new Chart(reportsChartRef.value.getContext('2d'), {
      type: 'line',
      data: { labels: props.chartData.reports_created.labels, datasets: [{ label: 'Reports', data: props.chartData.reports_created.values, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', fill: true, tension: 0.4 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    })
  }
  // Users Chart
  if (usersChartRef.value && props.chartData?.user_growth) {
    if (usersChart) usersChart.destroy()
    usersChart = new Chart(usersChartRef.value.getContext('2d'), {
      type: 'line',
      data: { labels: props.chartData.user_growth.labels, datasets: [{ label: 'Users', data: props.chartData.user_growth.values, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    })
  }
  // Tasks Chart
  if (tasksChartRef.value && props.chartData?.task_completion) {
    if (tasksChart) tasksChart.destroy()
    tasksChart = new Chart(tasksChartRef.value.getContext('2d'), {
      type: 'bar',
      data: { labels: props.chartData.task_completion.labels, datasets: [
        { label: 'Created', data: props.chartData.task_completion.created, backgroundColor: '#6366f1' },
        { label: 'Completed', data: props.chartData.task_completion.completed, backgroundColor: '#10b981' }
      ]},
      options: { responsive: true, maintainAspectRatio: false }
    })
  }
  // Types Chart
  if (typesChartRef.value && props.chartData?.popular_report_types) {
    if (typesChart) typesChart.destroy()
    typesChart = new Chart(typesChartRef.value.getContext('2d'), {
      type: 'doughnut',
      data: { labels: props.chartData.popular_report_types.labels, datasets: [{ data: props.chartData.popular_report_types.values, backgroundColor: ['#6366f1','#8b5cf6','#10b981','#f59e0b','#ef4444'] }] },
      options: { responsive: true, maintainAspectRatio: false, cutout: '60%' }
    })
  }
}

onMounted(initCharts)
watch(() => props.chartData, initCharts, { deep: true })
onBeforeUnmount(() => {
  if (reportsChart) reportsChart.destroy()
  if (usersChart) usersChart.destroy()
  if (tasksChart) tasksChart.destroy()
  if (typesChart) typesChart.destroy()
})
</script>