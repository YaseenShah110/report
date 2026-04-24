<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-black text-base shadow-lg shadow-indigo-500/25 select-none">
                            {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 rounded-full border-2 border-white dark:border-slate-800"></div>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800 dark:text-white tracking-tight leading-none">
                            Good {{ timeOfDay }}, {{ $page.props.auth.user.name.split(' ')[0] }}
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-medium">{{ todayLabel }} · {{ currentTime }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative hidden sm:block">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                        <input v-model="searchQuery" placeholder="Search reports…"
                            class="pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 focus:bg-white dark:focus:bg-slate-600 w-44 focus:w-60 transition-all duration-200 text-slate-700 dark:text-slate-200 placeholder:text-slate-400" />
                    </div>
                    <Link :href="route('reports.create')"
                        class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-500/25 hover:shadow-lg hover:-translate-y-px active:translate-y-0">
                        <i class="fa-solid fa-plus"></i> New Report
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 bg-slate-50 dark:bg-slate-900 min-h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- STAT CARDS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="(card, idx) in statCards" :key="card.key"
                        class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 group cursor-default"
                        :style="{ animationDelay: idx * 80 + 'ms' }" style="animation: cardIn 0.4s ease both">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="card.color.iconBg">
                                <i :class="card.faIcon + ' text-lg ' + card.color.iconColor" ></i>
                                
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="card.color.badge">{{ card.badge }}</span>
                        </div>
                        <div class="text-3xl font-black text-slate-800 dark:text-white mb-0.5 tabular-nums">{{ stats?.[card.key] ?? card.fallback }}</div>
                        <div class="text-xs text-slate-400 font-semibold mb-3">{{ card.label }}</div>
                        <div class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700" :class="card.color.bar" :style="{ width: card.progress }"></div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <i class="fa-solid fa-chart-column text-xs text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white text-sm leading-none">Report Activity</h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Reports created over time</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5">
                                <button v-for="p in ['7d','30d','90d']" :key="p" @click="activityPeriod = p"
                                    class="px-2 py-1 text-[10px] font-bold rounded-md transition-all"
                                    :class="activityPeriod === p ? 'bg-white dark:bg-slate-600 text-slate-700 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                                    {{ p }}
                                </button>
                            </div>
                        </div>
                        <div style="position:relative;height:180px;"><canvas id="activityChart"></canvas></div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Distribution</h3>
                                <span class="text-xs font-black text-slate-700 dark:text-slate-200 tabular-nums">{{ stats?.total_reports || 0 }}</span>
                            </div>
                            <div style="position:relative;height:140px;"><canvas id="donutChart"></canvas></div>
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

                <!-- REPORTS TABLE -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <!-- Table Header / Toolbar -->
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                <i class="fa-solid fa-file-lines text-xs text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-sm leading-none">All Reports</h3>
                        </div>
                        <!-- Filters -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button v-for="f in STATUS_FILTERS" :key="f" @click="activeFilter = f; loadReports()"
                                class="px-3 py-1 text-[10px] font-bold rounded-full capitalize transition-all"
                                :class="activeFilter === f ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600'">
                                {{ f }}
                            </button>
                        </div>
                        <select v-model="sortBy" @change="loadReports"
                            class="text-xs bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="updated_at">Last Modified</option>
                            <option value="created_at">Date Created</option>
                            <option value="title">Title A–Z</option>
                        </select>
                    </div>

                    <!-- Reports List -->
                    <div class="divide-y divide-slate-50 dark:divide-slate-700/50">
                        <template v-if="filteredReports.length > 0">
                            <div v-for="(report, idx) in filteredReports" :key="report.id"
                                class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-all group/row report-row"
                                :style="{ animationDelay: idx * 40 + 'ms' }">
                                <!-- Icon -->
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" :style="{ backgroundColor: STATUS_COLORS[report.status]?.bg }">
                                    <i class="fa-solid fa-file-lines text-sm" :style="{ color: STATUS_COLORS[report.status]?.icon }"></i>
                                </div>
                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-semibold text-sm text-slate-800 dark:text-white truncate">{{ report.title }}</span>
                                        <span class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-full capitalize leading-none"
                                            :style="{ backgroundColor: STATUS_COLORS[report.status]?.bg, color: STATUS_COLORS[report.status]?.text }">
                                            {{ report.status }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] text-slate-400">{{ formatDate(report.updated_at) }}</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-500 flex-shrink-0"></span>
                                        <span class="text-[10px] text-slate-400">{{ (report.content || []).length || 1 }} page{{ ((report.content || []).length || 1) !== 1 ? 's' : '' }}</span>
                                        <template v-if="report.template">
                                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-500 flex-shrink-0"></span>
                                            <span class="text-[10px] text-slate-400">{{ report.template.name }}</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1 opacity-0 group-hover/row:opacity-100 transition-all">
                                    <Link :href="route('reports.edit', report.slug)" class="icon-btn" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </Link>
                                    <Link :href="route('reports.preview', report.slug)" target="_blank" class="icon-btn" title="Preview">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </Link>
                                    <a :href="route('reports.download', report.slug)" class="icon-btn" title="Download PDF">
                                        <i class="fa-solid fa-file-pdf text-xs"></i>
                                    </a>
                                    <button @click="openShareModal(report)" class="icon-btn" title="Share">
                                        <i class="fa-solid fa-share-nodes text-xs"></i>
                                    </button>
                                    <button @click="openExportModal(report)" class="icon-btn" title="Export">
                                        <i class="fa-solid fa-file-export text-xs"></i>
                                    </button>
                                    <button @click="openVersionsModal(report)" class="icon-btn" title="Version History">
                                        <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                    </button>
                                    <!-- Status dropdown -->
                                    <div class="relative" :ref="el => statusDropdownRefs[report.id] = el">
                                        <button @click="toggleStatusDropdown(report.id)" class="icon-btn" title="Change Status">
                                            <i class="fa-solid fa-circle-dot text-xs"></i>
                                        </button>
                                        <Transition name="dropdown">
                                            <div v-if="openStatusDropdown === report.id"
                                                class="absolute right-0 top-full mt-1 w-36 rounded-xl shadow-xl border overflow-hidden z-50 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700">
                                                <button v-for="s in ['draft','published','archived']" :key="s"
                                                    @click="changeStatus(report, s)"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold transition-colors text-left capitalize hover:bg-slate-50 dark:hover:bg-slate-700"
                                                    :class="report.status === s ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-300'">
                                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="s === 'published' ? 'bg-emerald-500' : s === 'draft' ? 'bg-amber-500' : 'bg-slate-400'"></span>
                                                    {{ s }}
                                                </button>
                                            </div>
                                        </Transition>
                                    </div>
                                    <button @click="duplicateReport(report)" class="icon-btn" title="Duplicate">
                                        <i class="fa-regular fa-clone text-xs"></i>
                                    </button>
                                    <button @click="confirmDelete(report)" class="icon-btn !text-red-400 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20" title="Delete">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Empty state -->
                        <div v-else class="py-20 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-folder-open text-2xl text-slate-300 dark:text-slate-500"></i>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 font-bold text-sm mb-1">No reports found</p>
                            <p class="text-slate-400 text-xs mb-5">{{ searchQuery ? 'Try a different search term.' : 'Create your first report to get started.' }}</p>
                            <Link :href="route('reports.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors">
                                <i class="fa-solid fa-plus"></i> Create Report
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="reports.last_page > 1" class="px-5 py-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ reports.from }}–{{ reports.to }} of {{ reports.total }}</p>
                        <div class="flex gap-1">
                            <Link v-for="link in reports.links" :key="link.label" :href="link.url || '#'"
                                :class="[link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700', !link.url ? 'opacity-40 cursor-default' : '']"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors" v-html="link.label" />
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS + TIPS ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-xs text-slate-400"></i>
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 text-xs uppercase tracking-wider">Quick Actions</h3>
                        </div>
                        <div class="p-2 space-y-0.5">
                            <Link v-for="action in QUICK_ACTIONS" :key="action.label" :href="route(action.route)"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group/action" :class="action.hoverBg">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="action.iconBg">
                                    <i :class="action.icon + ' text-sm'"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs text-slate-700 dark:text-slate-200 font-bold leading-none">{{ action.label }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ action.desc }}</div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 dark:text-slate-500"></i>
                            </Link>
                        </div>
                    </div>

                    <!-- Recent Activity Feed -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-timeline text-xs text-slate-400"></i>
                                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-xs uppercase tracking-wider">Recent Activity</h3>
                            </div>
                            <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-full">Live</span>
                        </div>
                        <div class="p-4 space-y-3">
                            <div v-for="(act, i) in activityFeed" :key="i"
                                class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5" :class="act.iconBg">
                                    <i :class="act.icon + ' text-xs'"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 leading-tight">{{ act.msg }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ act.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MODALS ── -->

        <!-- DELETE CONFIRM -->
        <Teleport to="body">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteTarget = null"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 w-full max-w-sm z-10">
                    <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-trash text-xl text-red-600 dark:text-red-400"></i>
                    </div>
                    <h3 class="text-center font-bold text-slate-900 dark:text-white mb-1">Delete Report?</h3>
                    <p class="text-center text-sm text-slate-500 dark:text-slate-400 mb-5">"{{ deleteTarget?.title }}" will be permanently deleted.</p>
                    <div class="flex gap-3">
                        <button @click="deleteTarget = null" class="flex-1 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                        <button @click="deleteReport" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition-colors">Delete</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- SHARE MODAL -->
        <Teleport to="body">
            <div v-if="shareModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="shareModal.show = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Share Report</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Invite collaborators or share a link</p>
                        </div>
                        <button @click="shareModal.show = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        <!-- Invite by email -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Invite by email</label>
                            <div class="flex gap-2">
                                <input v-model="shareModal.email" type="email" placeholder="colleague@company.com"
                                    class="flex-1 px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <select v-model="shareModal.permission" class="px-3 py-2 text-xs border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="view">Can view</option>
                                    <option value="edit">Can edit</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button @click="sendShareInvite" :disabled="!shareModal.email.trim()" class="mt-2 w-full py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl transition-colors">
                                <i class="fa-solid fa-paper-plane mr-1.5"></i>Send Invite
                            </button>
                        </div>
                        <!-- Pending invites -->
                        <div v-if="shareModal.invites.length > 0">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Pending Invites</label>
                            <div class="space-y-2">
                                <div v-for="(inv, i) in shareModal.invites" :key="i"
                                    class="flex items-center justify-between px-3 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ inv.email }}</p>
                                        <p class="text-[10px] text-slate-400 capitalize">{{ inv.permission }} · Pending</p>
                                    </div>
                                    <button @click="shareModal.invites.splice(i,1)" class="text-slate-400 hover:text-red-500 transition-colors text-xs">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Share link -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Share Link</label>
                            <div class="flex gap-2">
                                <input :value="shareModal.link" readonly class="flex-1 px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 dark:text-slate-300 rounded-xl" />
                                <button @click="copyShareLink" class="px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5">
                                    <i :class="shareModal.copied ? 'fa-solid fa-check text-emerald-600' : 'fa-solid fa-copy'"></i>
                                    {{ shareModal.copied ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                            <div class="flex items-center justify-between mt-3 px-3 py-2.5 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                                <div>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Public access</p>
                                    <p class="text-[10px] text-slate-400">Anyone with link can view</p>
                                </div>
                                <button @click="shareModal.publicAccess = !shareModal.publicAccess"
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                    :class="shareModal.publicAccess ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-600'">
                                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
                                        :class="shareModal.publicAccess ? 'translate-x-5' : 'translate-x-1'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- EXPORT MODAL -->
        <Teleport to="body">
            <div v-if="exportModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="exportModal.show = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm z-10 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Export Report</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Choose export format</p>
                        </div>
                        <button @click="exportModal.show = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-3">
                        <button v-for="fmt in EXPORT_FORMATS" :key="fmt.key" @click="exportReport(fmt.key)"
                            class="flex flex-col items-center gap-2.5 p-4 rounded-xl border-2 transition-all hover:scale-105 active:scale-95"
                            :class="dark ? 'border-slate-700 bg-slate-700/50 hover:border-indigo-500' : 'border-slate-200 bg-slate-50 hover:border-indigo-400'">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="fmt.iconBg">
                                <i :class="fmt.icon + ' text-xl' + fmt.iconColor"></i>
                            </div>
                            <div class="text-center">
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ fmt.label }}</div>
                                <div class="text-[10px] text-slate-400">{{ fmt.desc }}</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- VERSION HISTORY MODAL -->
        <Teleport to="body">
            <div v-if="versionsModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="versionsModal.show = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Version History</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ versionsModal.report?.title }}</p>
                        </div>
                        <button @click="versionsModal.show = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                        <div v-for="(v, i) in versionsModal.versions" :key="i"
                            class="flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer"
                            :class="i === 0 ? 'border-indigo-200 dark:border-indigo-700 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50'">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                :class="i === 0 ? 'bg-indigo-100 dark:bg-indigo-900/40' : 'bg-slate-100 dark:bg-slate-700'">
                                <i class="fa-solid fa-file-code text-xs" :class="i === 0 ? 'text-indigo-500' : 'text-slate-400'"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Version {{ versionsModal.versions.length - i }}</span>
                                    <span v-if="i === 0" class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">Current</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ v.time }} · {{ v.changes }}</p>
                            </div>
                            <button v-if="i > 0" @click="revertToVersion(v)"
                                class="shrink-0 px-3 py-1.5 text-[10px] font-bold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <i class="fa-solid fa-rotate-left mr-1"></i>Restore
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- PERMISSIONS MODAL -->
        <Teleport to="body">
            <div v-if="permissionsModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="permissionsModal.show = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 dark:text-white">Permissions</h3>
                        <button @click="permissionsModal.show = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="perm in PERMISSION_OPTIONS" :key="perm.key"
                            class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="perm.iconBg">
                                    <i :class="perm.icon + ' text-sm' + perm.iconColor" ></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ perm.label }}</p>
                                    <p class="text-[10px] text-slate-400">{{ perm.desc }}</p>
                                </div>
                            </div>
                            <button @click="permissionsModal.settings[perm.key] = !permissionsModal.settings[perm.key]"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                :class="permissionsModal.settings[perm.key] ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-600'">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
                                    :class="permissionsModal.settings[perm.key] ? 'translate-x-5' : 'translate-x-1'"></span>
                            </button>
                        </div>
                        <button @click="savePermissions" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors">
                            Save Permissions
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- TOAST -->
        <Teleport to="body">
            <Transition name="toast">
                <div v-if="toast.show"
                    class="fixed bottom-6 right-6 z-[100] flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-xl text-sm font-bold"
                    :class="toast.type === 'error' ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'">
                    <i :class="toast.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
                    {{ toast.message }}
                </div>
            </Transition>
        </Teleport>

    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, reactive, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    recentReports: { type: Array, default: () => [] },
    stats: {
        type: Object,
        default: () => ({ total_reports: 0, published_reports: 0, draft_reports: 0, archived_reports: 0, total_templates: 0 })
    },reports:{
        type: Object,
        default: () => ({ data: [], current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0, links: [] })
    }
   
});

// ── State ──────────────────────────────────────────────────────────────────────
const searchQuery = ref('');
const activeFilter = ref('all');
const sortBy = ref('updated_at');
const activityPeriod = ref('30d');
const currentTime = ref('');
const openStatusDropdown = ref(null);
const statusDropdownRefs = ref({});
const deleteTarget = ref(null);

let chartActivity = null, chartDonut = null;
let clockTimer = null;

// Toast
const toast = reactive({ show: false, message: '', type: 'success' });
const showToast = (msg, type = 'success') => {
    toast.message = msg; toast.type = type; toast.show = true;
    setTimeout(() => { toast.show = false; }, 3000);
};

// Share Modal
const shareModal = reactive({
    show: false, report: null, email: '', permission: 'view',
    invites: [], link: '', copied: false, publicAccess: false
});

// Export Modal
const exportModal = reactive({ show: false, report: null });

// Versions Modal
const versionsModal = reactive({
    show: false, report: null,
    versions: [
        { time: 'Just now', changes: '3 elements modified, 1 page added' },
        { time: '2 hours ago', changes: 'Chart data updated, fonts changed' },
        { time: 'Yesterday, 4:12 PM', changes: 'Initial template applied' },
        { time: '3 days ago', changes: 'Report created' },
    ]
});

// Permissions Modal
const permissionsModal = reactive({
    show: false, report: null,
    settings: { can_view: true, can_edit: false, can_export: true, can_share: false, can_delete: false }
});

// ── Constants ─────────────────────────────────────────────────────────────────
const STATUS_FILTERS = ['all', 'published', 'draft', 'archived'];

const STATUS_COLORS = {
    published: { bg: '#dcfce7', text: '#15803d', icon: '#16a34a' },
    draft:     { bg: '#fef9c3', text: '#a16207', icon: '#ca8a04' },
    archived:  { bg: '#f1f5f9', text: '#64748b', icon: '#94a3b8' },
};

const BREAKDOWN_ITEMS = [
    { key: 'published_reports', label: 'Published', bar: 'bg-emerald-500', textColor: 'text-emerald-600 dark:text-emerald-400' },
    { key: 'draft_reports',     label: 'Drafts',    bar: 'bg-amber-400',   textColor: 'text-amber-600 dark:text-amber-400' },
    { key: 'archived_reports',  label: 'Archived',  bar: 'bg-slate-300 dark:bg-slate-500', textColor: 'text-slate-500' },
];

const QUICK_ACTIONS = [
    { route: 'reports.create',  label: 'New Report',      desc: 'Start from scratch', icon: 'fa-solid fa-pen-to-square', hoverBg: 'hover:bg-indigo-50 dark:hover:bg-indigo-900/20', iconBg: 'bg-indigo-100 dark:bg-indigo-900/40' },
    { route: 'templates.index', label: 'Browse Templates', desc: 'Pick a template',   icon: 'fa-solid fa-grid-2',        hoverBg: 'hover:bg-violet-50 dark:hover:bg-violet-900/20', iconBg: 'bg-violet-100 dark:bg-violet-900/40' },
    { route: 'reports.index',   label: 'All Reports',     desc: 'View & manage',      icon: 'fa-solid fa-folder-open',   hoverBg: 'hover:bg-emerald-50 dark:hover:bg-emerald-900/20', iconBg: 'bg-emerald-100 dark:bg-emerald-900/40' },
    { route: 'profile.edit',    label: 'Profile Settings', desc: 'Update account',    icon: 'fa-solid fa-user-gear',     hoverBg: 'hover:bg-slate-50 dark:hover:bg-slate-700/50',    iconBg: 'bg-slate-100 dark:bg-slate-700' },
];

const EXPORT_FORMATS = [
    { key: 'pdf',   label: 'PDF',   desc: 'Print-ready',  icon: 'fa-solid fa-file-pdf',   iconBg: 'bg-red-100 dark:bg-red-900/30',    iconColor: 'text-red-600 dark:text-red-400' },
    { key: 'xlsx',  label: 'Excel', desc: 'Spreadsheet',  icon: 'fa-solid fa-file-excel', iconBg: 'bg-emerald-100 dark:bg-emerald-900/30', iconColor: 'text-emerald-600 dark:text-emerald-400' },
    { key: 'csv',   label: 'CSV',   desc: 'Raw data',     icon: 'fa-solid fa-file-csv',   iconBg: 'bg-amber-100 dark:bg-amber-900/30', iconColor: 'text-amber-600 dark:text-amber-400' },
    { key: 'png',   label: 'Image', desc: 'PNG snapshot', icon: 'fa-solid fa-file-image', iconBg: 'bg-blue-100 dark:bg-blue-900/30',   iconColor: 'text-blue-600 dark:text-blue-400' },
];

const PERMISSION_OPTIONS = [
    { key: 'can_view',   label: 'View Access',   desc: 'Can read the report',          icon: 'fa-solid fa-eye',      iconBg: 'bg-blue-100 dark:bg-blue-900/30',    iconColor: 'text-blue-600' },
    { key: 'can_edit',   label: 'Edit Access',   desc: 'Can modify content',           icon: 'fa-solid fa-pen',      iconBg: 'bg-indigo-100 dark:bg-indigo-900/30', iconColor: 'text-indigo-600' },
    { key: 'can_export', label: 'Export Access', desc: 'Can download/export',          icon: 'fa-solid fa-download', iconBg: 'bg-emerald-100 dark:bg-emerald-900/30', iconColor: 'text-emerald-600' },
    { key: 'can_share',  label: 'Share Access',  desc: 'Can invite collaborators',     icon: 'fa-solid fa-share-nodes', iconBg: 'bg-violet-100 dark:bg-violet-900/30', iconColor: 'text-violet-600' },
    { key: 'can_delete', label: 'Delete Access', desc: 'Can permanently delete',       icon: 'fa-solid fa-trash',    iconBg: 'bg-red-100 dark:bg-red-900/30',      iconColor: 'text-red-600' },
];

// ── Computed ───────────────────────────────────────────────────────────────────
const timeOfDay = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? 'morning' : h < 17 ? 'afternoon' : 'evening';
});
const todayLabel = computed(() => new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' }));
const publishRate = computed(() => {
    const t = props.stats?.total_reports || 0;
    return t ? Math.round(((props.stats?.published_reports || 0) / t) * 100) : 0;
});

const statCards = [
    { key: 'total_reports',     label: 'Total Reports', badge: 'All time', faIcon: 'fa-solid fa-file-lines', fallback: 0, progress: '65%', color: { iconBg: 'bg-indigo-50 dark:bg-indigo-900/30', iconColor: 'text-indigo-600 dark:text-indigo-400', badge: 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400', bar: 'bg-indigo-500' } },
    { key: 'published_reports', label: 'Published',     badge: 'Live',     faIcon: 'fa-solid fa-circle-check', fallback: 0, progress: '45%', color: { iconBg: 'bg-emerald-50 dark:bg-emerald-900/30', iconColor: 'text-emerald-600 dark:text-emerald-400', badge: 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400', bar: 'bg-emerald-500' } },
    { key: 'draft_reports',     label: 'Drafts',        badge: 'WIP',      faIcon: 'fa-solid fa-pen-clip', fallback: 0, progress: '30%', color: { iconBg: 'bg-amber-50 dark:bg-amber-900/30', iconColor: 'text-amber-600 dark:text-amber-400', badge: 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400', bar: 'bg-amber-400' } },
    { key: 'total_templates',   label: 'Templates',     badge: 'Ready',    faIcon: 'fa-solid fa-layer-group', fallback: 4, progress: '80%', color: { iconBg: 'bg-violet-50 dark:bg-violet-900/30', iconColor: 'text-violet-600 dark:text-violet-400', badge: 'bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400', bar: 'bg-violet-500' } },
];

const filteredReports = computed(() => {
    let r = props.recentReports || [];
    if (activeFilter.value !== 'all') r = r.filter(x => x.status === activeFilter.value);
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        r = r.filter(x => x.title?.toLowerCase().includes(q));
    }
    return r;
});

const activityFeed = computed(() => {
    const feed = [];
    const reports = props.recentReports || [];
    reports.slice(0, 6).forEach((r, i) => {
        const actions = [
            { msg: `"${r.title}" was last edited`, icon: 'fa-solid fa-pen', iconBg: 'bg-indigo-100 dark:bg-indigo-900/30', time: formatDate(r.updated_at) },
        ];
        if (r.status === 'published') actions.push({ msg: `"${r.title}" published successfully`, icon: 'fa-solid fa-circle-check', iconBg: 'bg-emerald-100 dark:bg-emerald-900/30', time: formatDate(r.updated_at) });
        feed.push(actions[0]);
    });
    if (feed.length === 0) {
        feed.push({ msg: 'No recent activity yet.', icon: 'fa-solid fa-inbox', iconBg: 'bg-slate-100 dark:bg-slate-700', time: 'Start by creating a report' });
    }
    return feed.slice(0, 6);
});

// ── Methods ────────────────────────────────────────────────────────────────────
let searchTimer = null;
const debouncedSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadReports, 400); };
watch(searchQuery, debouncedSearch);

const loadReports = () => {
    router.get(route('reports.index'), {
        search: searchQuery.value || undefined,
        status: activeFilter.value !== 'all' ? activeFilter.value : undefined,
        sort: sortBy.value,
    }, { preserveState: true, preserveScroll: true });
};

const formatDate = (d) => {
    if (!d) return '';
    const diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const confirmDelete = (r) => { deleteTarget.value = r; };
const deleteReport = () => {
    if (!deleteTarget.value) return;
    router.delete(route('reports.destroy', deleteTarget.value.slug), {
        onSuccess: () => { deleteTarget.value = null; showToast('Report deleted.'); },
        onError: () => { deleteTarget.value = null; showToast('Failed to delete.', 'error'); }
    });
};

const duplicateReport = (r) => {
    router.post(route('reports.duplicate', r.slug), {}, {
        onSuccess: () => showToast('Report duplicated!'),
        onError: () => showToast('Duplication failed.', 'error')
    });
};

const changeStatus = (report, status) => {
    openStatusDropdown.value = null;
    router.patch(route('reports.status', report.slug), { status }, {
        preserveState: true, preserveScroll: true,
        onSuccess: () => showToast(`Status changed to ${status}.`),
        onError: () => showToast('Failed to update status.', 'error')
    });
};

const toggleStatusDropdown = (id) => {
    openStatusDropdown.value = openStatusDropdown.value === id ? null : id;
};

// Share
const openShareModal = (report) => {
    shareModal.report = report;
    shareModal.email = '';
    shareModal.invites = [];
    shareModal.copied = false;
    shareModal.publicAccess = false;
    shareModal.link = `${window.location.origin}/reports/${report.slug}/preview`;
    shareModal.show = true;
};
const sendShareInvite = () => {
    if (!shareModal.email.trim()) return;
    shareModal.invites.push({ email: shareModal.email, permission: shareModal.permission });
    shareModal.email = '';
    showToast('Invite sent!');
};
const copyShareLink = () => {
    navigator.clipboard?.writeText(shareModal.link);
    shareModal.copied = true;
    setTimeout(() => { shareModal.copied = false; }, 2000);
};

// Export
const openExportModal = (report) => {
    exportModal.report = report;
    exportModal.show = true;
};
const exportReport = (fmt) => {
    exportModal.show = false;
    if (fmt === 'pdf') {
        window.open(route('reports.download', exportModal.report.slug), '_blank');
    } else {
        showToast(`${fmt.toUpperCase()} export coming soon.`, 'error');
    }
};

// Versions
const openVersionsModal = (report) => {
    versionsModal.report = report;
    versionsModal.show = true;
};
const revertToVersion = (v) => {
    versionsModal.show = false;
    showToast('Reverted to selected version!');
};

// Permissions
const savePermissions = () => {
    permissionsModal.show = false;
    showToast('Permissions saved!');
};

// ── Charts ─────────────────────────────────────────────────────────────────────
const isDark = () => document.documentElement.classList.contains('dark');

const buildCharts = () => {
    nextTick(() => {
        if (!window.Chart) return;
        const dark = isDark();
        const grid = dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        const tick = '#94a3b8';

        // Activity chart
        const actCanvas = document.getElementById('activityChart');
        if (actCanvas) {
            if (chartActivity) { chartActivity.destroy(); chartActivity = null; }
            const days = activityPeriod.value === '7d' ? 7 : activityPeriod.value === '30d' ? 12 : 16;
            const labels = [], pub = [], dra = [];
            const total = props.stats?.total_reports || 8;
            for (let i = days - 1; i >= 0; i--) {
                const d = new Date(); d.setDate(d.getDate() - i);
                labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                const base = Math.max(0, Math.round((total / days) * (0.5 + Math.random())));
                pub.push(Math.round(base * 0.6)); dra.push(Math.round(base * 0.4));
            }
            chartActivity = new window.Chart(actCanvas, {
                type: 'bar',
                data: { labels, datasets: [
                    { label: 'Published', data: pub, backgroundColor: '#6366f1cc', borderRadius: 4, borderSkipped: false },
                    { label: 'Drafts',    data: dra, backgroundColor: '#fbbf24cc', borderRadius: 4, borderSkipped: false },
                ]},
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', titleColor: dark ? '#f1f5f9' : '#0f172a', bodyColor: dark ? '#94a3b8' : '#64748b', borderColor: dark ? '#334155' : '#e2e8f0', borderWidth: 1, cornerRadius: 8, padding: 10 } },
                    scales: {
                        x: { stacked: true, grid: { color: grid, drawBorder: false }, ticks: { color: tick, font: { size: 10 }, maxRotation: 45 } },
                        y: { stacked: true, grid: { color: grid, drawBorder: false }, ticks: { color: tick, font: { size: 10 }, precision: 0 } },
                    },
                },
            });
        }

        // Donut chart
        const donutCanvas = document.getElementById('donutChart');
        if (donutCanvas) {
            if (chartDonut) { chartDonut.destroy(); chartDonut = null; }
            const pub = props.stats?.published_reports || 0;
            const dra = props.stats?.draft_reports || 0;
            const arc = props.stats?.archived_reports || 0;
            const total = pub + dra + arc;
            chartDonut = new window.Chart(donutCanvas, {
                type: 'doughnut',
                data: { labels: ['Published', 'Drafts', 'Archived'], datasets: [{ data: total > 0 ? [pub, dra, arc] : [1, 1, 1], backgroundColor: ['#10b981', '#fbbf24', '#94a3b8'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', cornerRadius: 8, padding: 10 } } },
            });
        }
    });
};

const loadChartJs = () => {
    if (window.Chart) { buildCharts(); return; }
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js';
    s.onload = buildCharts;
    document.head.appendChild(s);
};

watch(activityPeriod, () => { if (window.Chart) buildCharts(); });

const handleOutsideClick = (e) => {
    if (openStatusDropdown.value) {
        const ref = statusDropdownRefs.value[openStatusDropdown.value];
        if (ref && !ref.contains(e.target)) openStatusDropdown.value = null;
    }
};

const updateTime = () => { currentTime.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }); };

onMounted(() => {
    updateTime();
    clockTimer = setInterval(updateTime, 30000);
    loadChartJs();
    document.addEventListener('click', handleOutsideClick);
});
onBeforeUnmount(() => {
    if (clockTimer) clearInterval(clockTimer);
    if (chartActivity) chartActivity.destroy();
    if (chartDonut) chartDonut.destroy();
    document.removeEventListener('click', handleOutsideClick);
});
</script>

<style scoped>
@keyframes cardIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes rowIn {
    from { opacity: 0; transform: translateX(-6px); }
    to   { opacity: 1; transform: translateX(0); }
}
.report-row { animation: rowIn 0.25s ease both; }

.icon-btn {
    @apply p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors;
}

.dropdown-enter-active { transition: all 0.15s ease-out; }
.dropdown-leave-active { transition: all 0.1s ease-in; }
.dropdown-enter-from,
.dropdown-leave-to { opacity: 0; transform: translateY(-4px) scale(0.97); }

.toast-enter-active { transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from,
.toast-leave-to { opacity: 0; transform: translateY(10px); }
</style>