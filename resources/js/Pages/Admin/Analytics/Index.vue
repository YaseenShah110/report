<!-- resources/js/Pages/Admin/Analytics/Index.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Analytics Dashboard</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">View system statistics and insights</p>
                </div>
                <div class="flex gap-2">
                    <select v-model="period" @change="changePeriod" 
                        class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="365">Last Year</option>
                    </select>
                    <button @click="exportAnalytics" 
                        class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <i class="fa-solid fa-download"></i>
                        Export
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Reports Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Reports</p>
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-file-lines text-indigo-600 dark:text-indigo-400 text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ reportStats.total }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <div :class="reportStats.trend >= 0 ? 'bg-green-100 dark:bg-green-900/30 text-green-600' : 'bg-red-100 dark:bg-red-900/30 text-red-600'" 
                                class="px-2 py-0.5 rounded-full text-xs font-semibold">
                                <i :class="reportStats.trend >= 0 ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down'" class="text-xs mr-1"></i>
                                {{ Math.abs(reportStats.trend) }}%
                            </div>
                            <span class="text-xs text-slate-500">vs last period</span>
                        </div>
                    </div>

                    <!-- Users Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Users</p>
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-users text-emerald-600 dark:text-emerald-400 text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ userStats.total }}</p>
                        <p class="text-xs text-slate-500 mt-2">
                            <span class="font-semibold text-emerald-600">{{ userStats.new_this_month }}</span> new this month
                        </p>
                    </div>

                    <!-- Tasks Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tasks Overview</p>
                            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-tasks text-amber-600 dark:text-amber-400 text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ taskStats.total }}</p>
                        <div class="mt-2">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-500">Completion Rate</span>
                                <span class="font-semibold text-emerald-600">{{ taskStats.completion_rate }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                <div class="bg-emerald-500 h-1.5 rounded-full" :style="{ width: taskStats.completion_rate + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Activities Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Activities</p>
                            <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-chart-line text-violet-600 dark:text-violet-400 text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ activityStats.total }}</p>
                        <p class="text-xs text-slate-500 mt-2">
                            <span class="font-semibold text-violet-600">{{ activityStats.last_24h }}</span> in last 24 hours
                        </p>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Reports Chart -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Reports Created Trend</h3>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                                <span class="text-xs text-slate-500">Last {{ period }} days</span>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="reportsChart" ref="reportsChartRef"></canvas>
                        </div>
                    </div>

                    <!-- User Growth Chart -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">User Growth</h3>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="text-xs text-slate-500">New users over time</span>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="usersChart" ref="usersChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Task Activity Chart -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Task Activity</h3>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                                    <span class="text-xs text-slate-500">Created</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs text-slate-500">Completed</span>
                                </div>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="tasksChart" ref="tasksChartRef"></canvas>
                        </div>
                    </div>

                    <!-- Popular Report Types -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Popular Report Types</h3>
                        <div class="h-64">
                            <canvas id="typesChart" ref="typesChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Sharing Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Sharing Stats -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Report Sharing Statistics</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Total Shares</span>
                                <span class="text-lg font-bold text-slate-900 dark:text-white">{{ sharingStats.total_shares }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Active Shares</span>
                                <span class="text-lg font-bold text-emerald-600">{{ sharingStats.active_shares }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Expired Shares</span>
                                <span class="text-lg font-bold text-red-600">{{ sharingStats.expired_shares }}</span>
                            </div>
                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                                <p class="text-xs font-semibold text-slate-500 mb-2">Most Shared Reports</p>
                                <div v-for="report in sharingStats.most_shared_reports" :key="report.title" class="flex justify-between items-center py-1">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ report.title }}</span>
                                    <span class="text-xs font-semibold text-indigo-600">{{ report.share_count }} shares</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Most Active Users -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Most Active Users</h3>
                        <div class="space-y-3">
                            <div v-for="user in activityStats.most_active_users" :key="user.user_id" 
                                class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                                        {{ user.user_name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ user.user_name }}</p>
                                        <p class="text-xs text-slate-500">{{ user.activity_count }} activities</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-fire text-orange-500 text-xs"></i>
                                    <span class="text-xs font-semibold text-orange-500">{{ Math.round(user.activity_count / activityStats.total * 100) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 text-center">
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ reportStats.published }}</p>
                        <p class="text-xs text-slate-500 mt-1">Published Reports</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 text-center">
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ reportStats.draft }}</p>
                        <p class="text-xs text-slate-500 mt-1">Draft Reports</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 text-center">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ userStats.premium }}</p>
                        <p class="text-xs text-slate-500 mt-1">Premium Users</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 text-center">
                        <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ taskStats.overdue }}</p>
                        <p class="text-xs text-slate-500 mt-1">Overdue Tasks</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    reportStats: Object,
    userStats: Object,
    taskStats: Object,
    activityStats: Object,
    sharingStats: Object,
    chartData: Object,
    period: Number
})

const period = ref(props.period || 30)
const reportsChartRef = ref(null)
const usersChartRef = ref(null)
const tasksChartRef = ref(null)
const typesChartRef = ref(null)

let reportsChart = null
let usersChart = null
let tasksChart = null
let typesChart = null

const changePeriod = () => {
    router.get(route('admin.analytics.index'), { period: period.value }, { preserveState: true, preserveScroll: true })
}

const exportAnalytics = () => {
    window.open(route('admin.analytics.export', { period: period.value, type: 'reports' }), '_blank')
}

const initCharts = () => {
    // Reports Chart
    const reportsCtx = document.getElementById('reportsChart')?.getContext('2d')
    if (reportsCtx && props.chartData?.reports_created) {
        if (reportsChart) reportsChart.destroy()
        reportsChart = new Chart(reportsCtx, {
            type: 'line',
            data: {
                labels: props.chartData.reports_created.labels,
                datasets: [{
                    label: 'Reports Created',
                    data: props.chartData.reports_created.values,
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
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        })
    }

    // Users Chart
    const usersCtx = document.getElementById('usersChart')?.getContext('2d')
    if (usersCtx && props.chartData?.user_growth) {
        if (usersChart) usersChart.destroy()
        usersChart = new Chart(usersCtx, {
            type: 'line',
            data: {
                labels: props.chartData.user_growth.labels,
                datasets: [{
                    label: 'New Users',
                    data: props.chartData.user_growth.values,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
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
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        })
    }

    // Tasks Chart
    const tasksCtx = document.getElementById('tasksChart')?.getContext('2d')
    if (tasksCtx && props.chartData?.task_completion) {
        if (tasksChart) tasksChart.destroy()
        tasksChart = new Chart(tasksCtx, {
            type: 'bar',
            data: {
                labels: props.chartData.task_completion.labels,
                datasets: [
                    {
                        label: 'Tasks Created',
                        data: props.chartData.task_completion.created,
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Tasks Completed',
                        data: props.chartData.task_completion.completed,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { stepSize: 1, precision: 0 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        })
    }

    // Types Chart
    const typesCtx = document.getElementById('typesChart')?.getContext('2d')
    if (typesCtx && props.chartData?.popular_report_types) {
        if (typesChart) typesChart.destroy()
        typesChart = new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: props.chartData.popular_report_types.labels,
                datasets: [{
                    data: props.chartData.popular_report_types.values,
                    backgroundColor: ['#6366f1', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 12,
                            font: { size: 11 }
                        }
                    },
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
    initCharts()
})

watch(() => props.chartData, () => {
    initCharts()
}, { deep: true })

onBeforeUnmount(() => {
    if (reportsChart) reportsChart.destroy()
    if (usersChart) usersChart.destroy()
    if (tasksChart) tasksChart.destroy()
    if (typesChart) typesChart.destroy()
})
</script>