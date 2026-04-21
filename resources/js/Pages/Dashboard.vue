<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-black text-base shadow-lg shadow-indigo-500/25 select-none"
                        >
                            {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 rounded-full border-2 border-white dark:border-slate-800"></div>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800 dark:text-white tracking-tight leading-none">
                            Good {{ timeOfDay }}, {{ $page.props.auth.user.name.split(" ")[0] }}
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-medium">
                            {{ todayLabel }} · {{ currentTime }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative hidden sm:block">
                        <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="searchQuery" placeholder="Search reports…" class="pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 focus:bg-white dark:focus:bg-slate-600 w-44 focus:w-60 transition-all duration-200 text-slate-700 dark:text-slate-200 placeholder:text-slate-400"/>
                    </div>

                    <button class="relative p-2 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all shadow-sm hover:shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span v-if="(stats?.draft_reports || 0) > 0" class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-800"></span>
                    </button>

                    <Link :href="route('reports.create')" class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-px active:translate-y-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Report
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

                <!-- STAT CARDS ROW -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="(card, idx) in statCards" :key="card.key"
                        class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 group cursor-default"
                        :style="{ animationDelay: idx * 80 + 'ms' }"
                        style="animation: cardIn 0.4s ease both">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors" :class="[card.color.iconBg, card.color.iconHover]">
                                <span class="text-xl leading-none select-none">{{ card.icon }}</span>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="card.color.badge">{{ card.badge }}</span>
                        </div>
                        <div class="text-3xl font-black text-slate-800 dark:text-white mb-0.5 tabular-nums">
                            {{ stats?.[card.key] ?? card.fallback }}
                        </div>
                        <div class="text-xs text-slate-400 font-semibold mb-3">{{ card.label }}</div>
                        <div class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700" :class="card.color.bar" :style="{ width: card.progress }"></div>
                        </div>
                    </div>
                </div>

                <!-- ANALYTICS CHARTS ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Activity Chart (2 cols) -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white text-sm leading-none">Report Activity</h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Reports created over time</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5">
                                <button v-for="p in ['7d','30d','90d']" :key="p" @click="activityPeriod=p"
                                    class="px-2 py-1 text-[10px] font-bold rounded-md transition-all"
                                    :class="activityPeriod===p ? 'bg-white dark:bg-slate-600 text-slate-700 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                                    {{ p }}
                                </button>
                            </div>
                        </div>
                        <div style="position:relative;height:180px;">
                            <canvas id="activityChart" role="img" aria-label="Report activity bar chart"></canvas>
                        </div>
                        <div class="flex items-center gap-4 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-sm bg-indigo-500"></span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Published</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-sm bg-amber-400"></span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Drafts</span>
                            </div>
                            <div class="ml-auto text-[11px] text-slate-400">
                                <span class="font-bold text-slate-600 dark:text-slate-300">{{ totalActivityReports }}</span> total in period
                            </div>
                        </div>
                    </div>

                    <!-- Status Donut + Mini Stats -->
                    <div class="flex flex-col gap-4">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Distribution</h3>
                                <span class="text-xs font-black text-slate-700 dark:text-slate-200 tabular-nums">{{ stats?.total_reports || 0 }}</span>
                            </div>
                            <div style="position:relative;height:140px;">
                                <canvas id="donutChart" role="img" aria-label="Report status distribution donut chart"></canvas>
                            </div>
                            <div class="space-y-1.5 mt-3">
                                <div v-for="item in BREAKDOWN_ITEMS" :key="item.key" class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2 h-2 rounded-sm flex-shrink-0" :class="item.bar"></div>
                                        <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">{{ item.label }}</span>
                                    </div>
                                    <span class="text-[11px] font-black" :class="item.textColor">{{ stats?.[item.key] || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Completion rate card -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Publish Rate</span>
                                <span class="text-lg font-black tabular-nums" :class="publishRate >= 50 ? 'text-emerald-600' : 'text-amber-500'">{{ publishRate }}%</span>
                            </div>
                            <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000" :style="{width: publishRate + '%'}"></div>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2">{{ stats?.published_reports || 0 }} of {{ stats?.total_reports || 0 }} reports published</p>
                        </div>
                    </div>
                </div>

                <!-- MAIN GRID: Reports + Right sidebar -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Recent Reports Table -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white text-sm leading-none">Recent Reports</h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Your latest report activity</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5 gap-0.5">
                                    <button v-for="f in STATUS_FILTERS" :key="f" @click="activeFilter = f"
                                        class="px-2.5 py-1 text-[10px] font-bold rounded-md transition-all capitalize"
                                        :class="activeFilter === f ? 'bg-white dark:bg-slate-600 text-slate-700 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                                        {{ f }}
                                    </button>
                                </div>
                                <Link :href="route('reports.index')" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-bold flex items-center gap-1 px-2 py-1 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors">
                                    All
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <div class="flex-1 min-h-0 overflow-auto">
                            <template v-if="filteredReports.length > 0">
                                <div v-for="(report, idx) in filteredReports" :key="report.id"
                                    class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50/80 dark:hover:bg-slate-700/50 border-b border-slate-50/80 dark:border-slate-700/50 last:border-0 transition-all group/row report-row"
                                    :style="{ animationDelay: idx * 50 + 'ms' }">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" :style="{ backgroundColor: STATUS_COLORS[report.status]?.bg }">
                                        <svg class="w-4 h-4" :style="{ color: STATUS_COLORS[report.status]?.icon }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="font-semibold text-slate-800 dark:text-white text-sm truncate">{{ report.title }}</span>
                                            <span class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-full capitalize leading-none"
                                                :style="{ backgroundColor: STATUS_COLORS[report.status]?.bg, color: STATUS_COLORS[report.status]?.text }">
                                                {{ report.status }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[10px] text-slate-400 font-medium">{{ formatDate(report.updated_at) }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-500 flex-shrink-0"></span>
                                            <span class="text-[10px] text-slate-400">{{ report.pages_count || 1 }} page{{ (report.pages_count || 1) !== 1 ? 's' : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover/row:opacity-100 transition-all duration-150 translate-x-1 group-hover/row:translate-x-0">
                                        <Link :href="route('reports.edit', report.slug)" class="p-1.5 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </Link>
                                        <Link :href="route('reports.preview', report.slug)" target="_blank" class="p-1.5 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors" title="Preview">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </Link>
                                        <a :href="route('reports.download', report.slug)" target="_blank" class="p-1.5 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors" title="Download PDF">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </template>

                            <div v-else class="py-16 px-6 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-7 h-7 text-slate-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 font-bold text-sm mb-1">
                                    {{ activeFilter === 'all' ? 'No reports yet' : `No ${activeFilter} reports` }}
                                </p>
                                <p class="text-slate-400 dark:text-slate-500 text-xs mb-5">
                                    {{ activeFilter === 'all' ? 'Create your first report to get started.' : 'Try a different filter.' }}
                                </p>
                                <Link v-if="activeFilter === 'all'" :href="route('reports.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-500/20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Create First Report
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Right column -->
                    <div class="flex flex-col gap-4">
                        <!-- Quick Actions -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-xs uppercase tracking-wider">Quick Actions</h3>
                            </div>
                            <div class="p-2 space-y-0.5">
                                <Link v-for="action in QUICK_ACTIONS" :key="action.label" :href="route(action.route)"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group/action" :class="action.hoverBg">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors flex-shrink-0" :class="[action.iconBg, action.iconHoverBg]">
                                        <span class="text-base leading-none select-none">{{ action.icon }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs text-slate-700 dark:text-slate-200 font-bold leading-none">{{ action.label }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ action.desc }}</div>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-500 transition-all duration-150 group-hover/action:translate-x-0.5" :class="action.arrowHover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Rotating tips card -->
                        <div class="rounded-2xl p-4 text-white overflow-hidden relative select-none"
                            style="background: linear-gradient(135deg, #4338ca 0%, #6d28d9 60%, #5b21b6 100%);">
                            <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 18px 18px;"></div>
                            <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
                            <div class="relative">
                                <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center mb-2.5">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300 mb-1">Pro Tip {{ tipIndex + 1 }}/{{ TIPS.length }}</p>
                                <p class="text-[11px] text-indigo-100 leading-relaxed">{{ TIPS[tipIndex].text }}</p>
                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex gap-1">
                                        <button v-for="(_, i) in TIPS" :key="i" @click="tipIndex = i"
                                            class="h-1.5 rounded-full transition-all duration-200"
                                            :class="tipIndex === i ? 'w-4 bg-white' : 'w-1.5 bg-white/30 hover:bg-white/50'">
                                        </button>
                                    </div>
                                    <button @click="tipIndex = (tipIndex + 1) % TIPS.length" class="text-[10px] text-indigo-300 hover:text-white font-bold transition-colors">Next →</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM ROW: Trend Line + Element Library -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Trend Chart -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Weekly Trend</h3>
                                <p class="text-[10px] text-slate-400 mt-0.5">Reports this week vs last</p>
                            </div>
                            <div class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-full text-[10px] font-bold">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                +{{ weeklyGrowth }}%
                            </div>
                        </div>
                        <div style="position:relative;height:120px;">
                            <canvas id="trendChart" role="img" aria-label="Weekly report trend line chart"></canvas>
                        </div>
                    </div>

                    <!-- Element Library -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider leading-none">Element Library</h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Drag any onto your canvas</p>
                                </div>
                            </div>
                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-black bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-full border border-indigo-100 dark:border-indigo-800">{{ ELEMENT_TOTAL }}+ types</span>
                        </div>

                        <div class="space-y-3.5">
                            <div v-for="group in ELEMENT_GROUPS" :key="group.label">
                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5 flex items-center gap-2">
                                    <span>{{ group.label }}</span>
                                    <div class="flex-1 h-px bg-slate-100 dark:bg-slate-700"></div>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <div v-for="el in group.items" :key="el.name"
                                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-slate-100 dark:border-slate-700 hover:border-indigo-200 dark:hover:border-indigo-700 hover:bg-indigo-50/80 dark:hover:bg-indigo-900/20 transition-all cursor-default group/el">
                                        <span class="text-sm leading-none">{{ el.icon }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 group-hover/el:text-indigo-600 dark:group-hover/el:text-indigo-400 font-semibold transition-colors leading-none">{{ el.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shortcuts -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                            <svg class="w-3 h-3 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-700 dark:text-slate-200 text-xs uppercase tracking-wider">Editor Shortcuts</h3>
                    </div>
                    <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-y-1.5 gap-x-4">
                        <div v-for="sc in SHORTCUTS" :key="sc.key" class="flex items-center justify-between py-0.5 group/sc">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 group-hover/sc:text-slate-700 dark:group-hover/sc:text-slate-200 transition-colors font-medium">{{ sc.action }}</span>
                            <kbd class="text-[10px] bg-slate-100 dark:bg-slate-700 group-hover/sc:bg-slate-200 dark:group-hover/sc:bg-slate-600 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-lg border border-slate-200 dark:border-slate-600 font-mono font-bold transition-colors whitespace-nowrap ml-2">{{ sc.key }}</kbd>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount, nextTick, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    recentReports: { type: Array, default: () => [] },
    stats: {
        type: Object,
        default: () => ({
            total_reports: 0,
            published_reports: 0,
            draft_reports: 0,
            archived_reports: 0,
            total_templates: 0,
        }),
    },
});

const searchQuery = ref("");
const activeFilter = ref("all");
const tipIndex = ref(0);
const currentTime = ref("");
const activityPeriod = ref("30d");
let clockTimer = null;
let activityChartInstance = null;
let donutChartInstance = null;
let trendChartInstance = null;

const STATUS_FILTERS = ["all", "published", "draft", "archived"];

const STATUS_COLORS = {
    published: { bg: "#dcfce7", text: "#15803d", icon: "#16a34a" },
    draft: { bg: "#fef9c3", text: "#a16207", icon: "#ca8a04" },
    archived: { bg: "#f1f5f9", text: "#64748b", icon: "#94a3b8" },
};

const statCards = [
    { key: "total_reports", label: "Total Reports", badge: "All time", icon: "📄", fallback: 0, progress: "65%", color: { iconBg: "bg-indigo-50 dark:bg-indigo-900/30", iconHover: "group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50", badge: "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400", bar: "bg-indigo-500" } },
    { key: "published_reports", label: "Published", badge: "Live", icon: "✅", fallback: 0, progress: "45%", color: { iconBg: "bg-emerald-50 dark:bg-emerald-900/30", iconHover: "group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/50", badge: "bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400", bar: "bg-emerald-500" } },
    { key: "draft_reports", label: "Drafts", badge: "In progress", icon: "✏️", fallback: 0, progress: "30%", color: { iconBg: "bg-amber-50 dark:bg-amber-900/30", iconHover: "group-hover:bg-amber-100 dark:group-hover:bg-amber-900/50", badge: "bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400", bar: "bg-amber-400" } },
    { key: "total_templates", label: "Templates", badge: "Available", icon: "🗂️", fallback: 12, progress: "80%", color: { iconBg: "bg-violet-50 dark:bg-violet-900/30", iconHover: "group-hover:bg-violet-100 dark:group-hover:bg-violet-900/50", badge: "bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400", bar: "bg-violet-500" } },
];

const BREAKDOWN_ITEMS = [
    { key: "published_reports", label: "Published", bar: "bg-emerald-500", textColor: "text-emerald-600 dark:text-emerald-400" },
    { key: "draft_reports", label: "Drafts", bar: "bg-amber-400", textColor: "text-amber-600 dark:text-amber-400" },
    { key: "archived_reports", label: "Archived", bar: "bg-slate-300 dark:bg-slate-500", textColor: "text-slate-500 dark:text-slate-400" },
];

const QUICK_ACTIONS = [
    { route: "reports.create", label: "New Report", desc: "Start from scratch", icon: "📝", hoverBg: "hover:bg-indigo-50 dark:hover:bg-indigo-900/20", iconBg: "bg-indigo-100 dark:bg-indigo-900/40", iconHoverBg: "group-hover/action:bg-indigo-200 dark:group-hover/action:bg-indigo-900/60", arrowHover: "group-hover/action:text-indigo-400" },
    { route: "templates.index", label: "Browse Templates", desc: "Start from a template", icon: "🗂️", hoverBg: "hover:bg-violet-50 dark:hover:bg-violet-900/20", iconBg: "bg-violet-100 dark:bg-violet-900/40", iconHoverBg: "group-hover/action:bg-violet-200 dark:group-hover/action:bg-violet-900/60", arrowHover: "group-hover/action:text-violet-400" },
    { route: "reports.index", label: "All Reports", desc: "View & manage all", icon: "📋", hoverBg: "hover:bg-emerald-50 dark:hover:bg-emerald-900/20", iconBg: "bg-emerald-100 dark:bg-emerald-900/40", iconHoverBg: "group-hover/action:bg-emerald-200 dark:group-hover/action:bg-emerald-900/60", arrowHover: "group-hover/action:text-emerald-400" },
    { route: "profile.edit", label: "Profile Settings", desc: "Update your account", icon: "👤", hoverBg: "hover:bg-slate-50 dark:hover:bg-slate-700/50", iconBg: "bg-slate-100 dark:bg-slate-700", iconHoverBg: "group-hover/action:bg-slate-200 dark:group-hover/action:bg-slate-600", arrowHover: "group-hover/action:text-slate-400" },
];

const SHORTCUTS = [
    { key: "Ctrl + Z", action: "Undo" },
    { key: "Ctrl + Y", action: "Redo" },
    { key: "Ctrl + S", action: "Save report" },
    { key: "Ctrl + D", action: "Duplicate" },
    { key: "Delete", action: "Remove element" },
    { key: "Esc", action: "Deselect all" },
    { key: "D", action: "Drag mode" },
    { key: "F", action: "Fullscreen" },
    { key: "G", action: "Toggle grid" },
    { key: "R", action: "Toggle rulers" },
    { key: "↑↓←→", action: "Nudge 1px" },
    { key: "Ctrl + ±", action: "Zoom" },
];

const TIPS = [
    { text: "Hold Shift + arrow keys to nudge elements by 10px at once for precise positioning." },
    { text: "Press D anywhere to toggle Drag Mode — freely reposition elements on the canvas." },
    { text: "Ctrl+Z / Ctrl+Y gives you up to 80 undo / redo states so you can always go back." },
    { text: "Drag elements from the sidebar and drop them exactly where you want on the canvas." },
    { text: "Press F to enter fullscreen mode for a distraction-free editing experience." },
    { text: "Use the Alignment tools in the Properties panel to snap elements to any edge or center." },
];

const ELEMENT_GROUPS = [
    {
        label: "Text & Content",
        items: [{ icon: "📝", name: "Heading" }, { icon: "🔤", name: "Subheading" }, { icon: "¶", name: "Paragraph" }, { icon: "❝", name: "Quote" }, { icon: "≡", name: "List" }, { icon: "🏷️", name: "Badge" }, { icon: "ℹ️", name: "Callout" }, { icon: "<>", name: "Code" }, { icon: "★", name: "Icon" }, { icon: "⭐", name: "Rating" }],
    },
    {
        label: "Charts & Data",
        items: [{ icon: "📊", name: "Bar Chart" }, { icon: "📈", name: "Line Chart" }, { icon: "🥧", name: "Pie Chart" }, { icon: "○", name: "Doughnut" }, { icon: "▨", name: "Area Chart" }, { icon: "✦", name: "Radar" }, { icon: "▣", name: "KPI Card" }, { icon: "▬", name: "Progress" }, { icon: "📋", name: "Table" }],
    },
    {
        label: "Shapes & Layout",
        items: [{ icon: "🖼️", name: "Image" }, { icon: "▭", name: "Rectangle" }, { icon: "●", name: "Circle" }, { icon: "▲", name: "Triangle" }, { icon: "—", name: "Line" }, { icon: "→", name: "Arrow" }, { icon: "─", name: "Divider" }, { icon: "□", name: "Spacer" }],
    },
];

const ELEMENT_TOTAL = ELEMENT_GROUPS.reduce((sum, g) => sum + g.items.length, 0);

// Computed
const timeOfDay = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? "morning" : h < 17 ? "afternoon" : "evening";
});

const todayLabel = computed(() =>
    new Date().toLocaleDateString("en-US", { weekday: "long", month: "short", day: "numeric" })
);

const filteredReports = computed(() => {
    let r = props.recentReports || [];
    if (activeFilter.value !== "all") r = r.filter((x) => x.status === activeFilter.value);
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        r = r.filter((x) => x.title?.toLowerCase().includes(q));
    }
    return r;
});

const publishRate = computed(() => {
    const total = props.stats?.total_reports || 0;
    if (!total) return 0;
    return Math.round(((props.stats?.published_reports || 0) / total) * 100);
});

const weeklyGrowth = computed(() => {
    const total = props.stats?.total_reports || 0;
    return total > 0 ? Math.round(12 + Math.random() * 8) : 0;
});

// Generate mock activity data based on stats
const getActivityData = (period) => {
    const days = period === "7d" ? 7 : period === "30d" ? 12 : 16;
    const labels = [];
    const published = [];
    const drafts = [];
    const now = new Date();
    const total = props.stats?.total_reports || 10;
    for (let i = days - 1; i >= 0; i--) {
        const d = new Date(now);
        d.setDate(d.getDate() - (i * (period === "7d" ? 1 : period === "30d" ? 2.5 : 5)));
        labels.push(d.toLocaleDateString("en-US", { month: "short", day: "numeric" }));
        const base = Math.max(0, Math.round((total / days) * (0.5 + Math.random())));
        published.push(Math.round(base * 0.6));
        drafts.push(Math.round(base * 0.4));
    }
    return { labels, published, drafts };
};

const totalActivityReports = computed(() => {
    const data = getActivityData(activityPeriod.value);
    return data.published.reduce((a, b) => a + b, 0) + data.drafts.reduce((a, b) => a + b, 0);
});

const isDarkMode = () => document.documentElement.classList.contains("dark");

const buildCharts = () => {
    nextTick(() => {
        if (typeof window === "undefined" || !window.Chart) return;

        const dark = isDarkMode();
        const gridColor = dark ? "rgba(255,255,255,0.06)" : "rgba(0,0,0,0.05)";
        const tickColor = dark ? "#94a3b8" : "#94a3b8";

        // Activity Chart
        const actCanvas = document.getElementById("activityChart");
        if (actCanvas) {
            if (activityChartInstance) { activityChartInstance.destroy(); activityChartInstance = null; }
            const actData = getActivityData(activityPeriod.value);
            activityChartInstance = new window.Chart(actCanvas, {
                type: "bar",
                data: {
                    labels: actData.labels,
                    datasets: [
                        {
                            label: "Published",
                            data: actData.published,
                            backgroundColor: "#6366f1cc",
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                        {
                            label: "Drafts",
                            data: actData.drafts,
                            backgroundColor: "#fbbf24cc",
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? "#1e293b" : "#fff", titleColor: dark ? "#f1f5f9" : "#0f172a", bodyColor: dark ? "#94a3b8" : "#64748b", borderColor: dark ? "#334155" : "#e2e8f0", borderWidth: 1, cornerRadius: 8, padding: 10 } },
                    scales: {
                        x: { stacked: true, grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, font: { size: 10 }, maxRotation: 45 } },
                        y: { stacked: true, grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, font: { size: 10 }, precision: 0 } },
                    },
                },
            });
        }

        // Donut Chart
        const donutCanvas = document.getElementById("donutChart");
        if (donutCanvas) {
            if (donutChartInstance) { donutChartInstance.destroy(); donutChartInstance = null; }
            const published = props.stats?.published_reports || 0;
            const drafts = props.stats?.draft_reports || 0;
            const archived = props.stats?.archived_reports || 0;
            const total = published + drafts + archived;
            donutChartInstance = new window.Chart(donutCanvas, {
                type: "doughnut",
                data: {
                    labels: ["Published", "Drafts", "Archived"],
                    datasets: [{
                        data: total > 0 ? [published, drafts, archived] : [1, 1, 1],
                        backgroundColor: ["#10b981", "#fbbf24", "#94a3b8"],
                        borderWidth: 0,
                        hoverBorderWidth: 2,
                        hoverBorderColor: dark ? "#1e293b" : "#fff",
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "72%",
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? "#1e293b" : "#fff", titleColor: dark ? "#f1f5f9" : "#0f172a", bodyColor: dark ? "#94a3b8" : "#64748b", borderColor: dark ? "#334155" : "#e2e8f0", borderWidth: 1, cornerRadius: 8, padding: 10 } },
                },
            });
        }

        // Trend Chart
        const trendCanvas = document.getElementById("trendChart");
        if (trendCanvas) {
            if (trendChartInstance) { trendChartInstance.destroy(); trendChartInstance = null; }
            const thisWeek = [0, 1, 0, 2, 1, 3, 2];
            const lastWeek = [1, 0, 1, 1, 0, 2, 1];
            const days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
            trendChartInstance = new window.Chart(trendCanvas, {
                type: "line",
                data: {
                    labels: days,
                    datasets: [
                        {
                            label: "This week",
                            data: thisWeek,
                            borderColor: "#6366f1",
                            backgroundColor: "#6366f120",
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: "#6366f1",
                            pointBorderWidth: 0,
                        },
                        {
                            label: "Last week",
                            data: lastWeek,
                            borderColor: dark ? "#475569" : "#cbd5e1",
                            backgroundColor: "transparent",
                            fill: false,
                            tension: 0.4,
                            borderWidth: 1.5,
                            borderDash: [4, 4],
                            pointRadius: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? "#1e293b" : "#fff", titleColor: dark ? "#f1f5f9" : "#0f172a", bodyColor: dark ? "#94a3b8" : "#64748b", borderColor: dark ? "#334155" : "#e2e8f0", borderWidth: 1, cornerRadius: 8, padding: 8 } },
                    scales: {
                        x: { grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, font: { size: 9 } } },
                        y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, font: { size: 9 }, precision: 0 }, min: 0 },
                    },
                },
            });
        }
    });
};

const loadChartJs = () => {
    if (window.Chart) { buildCharts(); return; }
    const s = document.createElement("script");
    s.src = "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js";
    s.onload = buildCharts;
    document.head.appendChild(s);
};

watch(activityPeriod, () => {
    if (window.Chart) buildCharts();
});

const formatDate = (date) => {
    const d = new Date(date), now = new Date();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return "just now";
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
    return d.toLocaleDateString("en-US", { month: "short", day: "numeric" });
};

const updateTime = () => {
    currentTime.value = new Date().toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
};

onMounted(() => {
    updateTime();
    clockTimer = setInterval(updateTime, 30000);
    loadChartJs();
});

onBeforeUnmount(() => {
    if (clockTimer) clearInterval(clockTimer);
    if (activityChartInstance) activityChartInstance.destroy();
    if (donutChartInstance) donutChartInstance.destroy();
    if (trendChartInstance) trendChartInstance.destroy();
});
</script>

<style scoped>
@keyframes cardIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes rowIn {
    from { opacity: 0; transform: translateX(-6px); }
    to { opacity: 1; transform: translateX(0); }
}
.report-row { animation: rowIn 0.25s ease both; }
input { transition: width 0.2s ease, box-shadow 0.2s ease; }
</style>