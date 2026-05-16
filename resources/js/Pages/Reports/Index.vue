<!--
  Reports/Index.vue — Ultra-Enhanced Report Manager
  Stack: Laravel 12 + Inertia.js + Vue 3 (Composition API)
  Matches MyTasks.vue visual style. Features:
  - Grid / List / Timeline views
  - Inline Trash (no page redirect)
  - Progress bars per report
  - Assign-to modal
  - Share modal with public toggle
  - Status inline editable
  - Export reports list (CSV/JSON)
  - Version history modal
  - Bulk actions
  - Animated stats cards
  - Keyboard shortcuts
  - Dark + Light theme, fully responsive / mobile-first
  - EmptyReportsState included inline (no separate file needed)
-->
<template>
    <AuthenticatedLayout>
        <!-- ═══ PAGE HEADER ═══ -->
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4"
            >
                <div>
                    <h2
                        class="text-xl sm:text-2xl font-bold tracking-tight bg-gradient-to-r from-violet-500 via-fuchsia-500 to-pink-500 bg-clip-text text-transparent"
                    >
                        My Reports
                    </h2>
                    <p
                        class="text-xs sm:text-sm text-slate-400 mt-0.5 font-light tracking-wide"
                    >
                        Design, share &amp; export beautiful reports
                    </p>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                    <!-- View Toggle -->
                    <div
                        class="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1"
                    >
                        <button
                            v-for="mode in viewModes"
                            :key="mode.key"
                            @click="viewMode = mode.key"
                            :class="[
                                'p-2 rounded-lg transition-all duration-200 text-xs',
                                viewMode === mode.key
                                    ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-400 scale-105'
                                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300',
                            ]"
                            :title="mode.label"
                        >
                            <i :class="mode.icon" class="text-sm"></i>
                        </button>
                        <!-- Export List Button -->
                        <div class="relative" ref="exportListRef">
                            <button
                                @click.stop="exportListMenu = !exportListMenu"
                                class="group flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-300 dark:hover:border-violet-700 hover:text-violet-600 dark:hover:text-violet-400 transition-all text-xs font-medium"
                            >
                                <i
                                    class="fa-solid fa-download group-hover:translate-y-0.5 transition-transform"
                                ></i>
                                <span class="hidden sm:inline"
                                    >Export List</span
                                >
                                <i
                                    class="fa-solid fa-chevron-down text-[9px] ml-0.5 transition-transform"
                                    :class="exportListMenu ? 'rotate-180' : ''"
                                ></i>
                            </button>
                            <Transition name="slide-down">
                                <div
                                    v-if="exportListMenu"
                                    class="absolute right-0 top-full mt-1.5 w-44 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 overflow-hidden"
                                    @click.stop
                                >
                                    <button
                                        @click.stop="exportReportsList('csv')"
                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 transition-colors"
                                    >
                                        <i
                                            class="fa-solid fa-file-csv text-blue-500 w-4"
                                        ></i>
                                        Export CSV
                                    </button>
                                    <button
                                        @click.stop="exportReportsList('json')"
                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 transition-colors"
                                    >
                                        <i
                                            class="fa-solid fa-file-code text-emerald-500 w-4"
                                        ></i>
                                        Export JSON
                                    </button>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ═══ MAIN CONTENT ═══ -->
        <div class="py-5 sm:py-8 px-3 sm:px-4 lg:px-6 space-y-5 sm:space-y-7">
            <!-- ── STATS CARDS ── -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.key"
                    @click="quickFilterStatus(stat.key)"
                    :class="[
                        'group relative overflow-hidden rounded-2xl border p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 select-none',
                        activeStatKey === stat.key
                            ? `border-${stat.color}-400 dark:border-${stat.color}-600 bg-${stat.color}-50 dark:bg-${stat.color}-900/20 shadow-lg shadow-${stat.color}-100 dark:shadow-${stat.color}-900/20`
                            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600',
                    ]"
                >
                    <div
                        :class="`absolute -top-6 -right-6 w-20 h-20 rounded-full bg-${stat.color}-400/10 blur-2xl group-hover:scale-150 transition-transform duration-500`"
                    ></div>

                    <div class="relative flex items-start justify-between">
                        <div>
                            <p
                                class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium tracking-wide uppercase mb-1"
                            >
                                {{ stat.label }}
                            </p>
                            <p
                                :class="`text-2xl sm:text-3xl font-black text-${stat.color}-600 dark:text-${stat.color}-400 tabular-nums`"
                            >
                                {{ stats?.[stat.key] ?? 0 }}
                            </p>
                        </div>
                        <div
                            :class="`w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-${stat.color}-100 dark:bg-${stat.color}-900/40 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`"
                        >
                            <i
                                :class="`${stat.icon} text-${stat.color}-600 dark:text-${stat.color}-400 text-base sm:text-lg`"
                            ></i>
                        </div>
                    </div>
                    <div
                        :class="`absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-${stat.color}-400 to-${stat.color}-600 transition-all duration-300`"
                        :style="{
                            width: activeStatKey === stat.key ? '100%' : '0%',
                        }"
                    ></div>
                </div>
            </div>

            <!-- ── FILTERS ── -->
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4 shadow-sm"
            >
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <!-- Search -->
                    <div class="relative flex-1 min-w-[160px]">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"
                        ></i>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search reports…"
                            @input="debouncedSearch"
                            class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                        />
                    </div>

                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="filter-select"
                    >
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>

                    <select
                        v-model="filters.sort"
                        @change="applyFilters"
                        class="filter-select hidden sm:block"
                    >
                        <option value="updated_at">Last Modified</option>
                        <option value="created_at">Date Created</option>
                        <option value="title">Title A–Z</option>
                    </select>

                    <!-- Bulk actions when selected -->
                    <Transition name="slide-down">
                        <div
                            v-if="selectedIds.length"
                            class="flex items-center gap-2 px-3 py-1.5 bg-violet-50 dark:bg-violet-900/30 border border-violet-200 dark:border-violet-800 rounded-xl"
                        >
                            <span
                                class="text-[11px] font-bold text-violet-700 dark:text-violet-300"
                                >{{ selectedIds.length }} selected</span
                            >
                            <button
                                @click="bulkDelete"
                                class="text-[10px] text-red-500 hover:text-red-700 font-semibold transition-colors"
                            >
                                <i class="fa-solid fa-trash mr-1"></i>Delete
                            </button>
                            <button
                                @click="selectedIds = []"
                                class="text-[10px] text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </Transition>

                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-violet-200 dark:shadow-violet-900/30"
                    >
                        Apply
                    </button>
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-xs transition-all"
                    >
                        <i class="fa-solid fa-xmark mr-1"></i>Reset
                    </button>
                </div>
            </div>

            <!-- ════════════════════════════════════════
                 INLINE TRASH PANEL
            ════════════════════════════════════════ -->
            <Transition name="slide-down">
                <div
                    v-if="showTrashPanel"
                    class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/30 rounded-2xl overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-4 sm:px-6 py-3 border-b border-red-200 dark:border-red-900/30"
                    >
                        <div class="flex items-center gap-2">
                            <i
                                class="fa-solid fa-trash-can text-red-500 text-sm"
                            ></i>
                            <h3
                                class="font-bold text-red-700 dark:text-red-400 text-sm"
                            >
                                Trash
                            </h3>
                            <span
                                class="text-[10px] bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 px-2 py-0.5 rounded-full font-bold"
                            >
                                {{ trashedReports.length }} items
                            </span>
                        </div>
                        <button
                            @click="
                                activeStatKey = '';
                                showTrashPanel = false;
                            "
                            class="text-red-400 hover:text-red-600 transition-colors text-xs"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div
                            v-if="trashedReports.length === 0"
                            class="text-center py-8 text-slate-400 text-xs"
                        >
                            No trashed reports
                        </div>
                        <div v-else class="space-y-2.5">
                            <div
                                v-for="r in trashedReports"
                                :key="r.id"
                                class="flex items-center gap-3 bg-white dark:bg-slate-800 border border-red-100 dark:border-red-900/20 rounded-xl p-3 sm:p-4 group"
                            >
                                <div
                                    class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center"
                                    :style="`background:linear-gradient(135deg,${r.settings?.primary_color || '#7c3aed'}20,${r.settings?.accent_color || '#c026d3'}20)`"
                                >
                                    <i
                                        class="fa-solid fa-file-lines text-[11px]"
                                        :style="`color:${r.settings?.primary_color || '#7c3aed'}`"
                                    ></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="font-semibold text-xs text-slate-700 dark:text-slate-300 line-clamp-1"
                                    >
                                        {{ r.title }}
                                    </p>
                                    <p
                                        class="text-[10px] text-slate-400 mt-0.5"
                                    >
                                        Deleted {{ formatDate(r.deleted_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        @click="restoreReport(r)"
                                        class="px-2.5 py-1 text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 border border-emerald-200 dark:border-emerald-800 rounded-lg hover:bg-emerald-100 transition-colors"
                                    >
                                        <i
                                            class="fa-solid fa-rotate-left mr-1"
                                        ></i
                                        >Restore
                                    </button>
                                    <button
                                        @click="confirmForceDelete(r)"
                                        class="px-2.5 py-1 text-[10px] font-bold bg-red-50 dark:bg-red-900/30 text-red-600 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 transition-colors"
                                    >
                                        <i class="fa-solid fa-trash mr-1"></i
                                        >Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- ════════════════════════════════════════
                 GRID VIEW
            ════════════════════════════════════════ -->
            <div
                v-if="viewMode === 'grid' && !showTrashPanel"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5"
            >
                <TransitionGroup name="card-pop" appear>
                    <div
                        v-for="report in reports.data"
                        :key="report.id"
                        class="group relative bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-2xl hover:shadow-violet-100/60 dark:hover:shadow-violet-900/30 hover:-translate-y-1 transition-all duration-300"
                    >
                        <!-- Priority/status stripe top -->
                        <div
                            :class="`absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r ${statusGradient(report.status)}`"
                        ></div>

                        <!-- Selection -->
                        <div
                            class="absolute top-3 left-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            <input
                                type="checkbox"
                                :value="report.id"
                                v-model="selectedIds"
                                class="w-4 h-4 rounded accent-violet-600 cursor-pointer shadow"
                            />
                        </div>

                        <!-- Thumbnail -->
                        <div
                            class="relative h-36 sm:h-44 overflow-hidden"
                            :style="`background: linear-gradient(135deg, ${report.settings?.primary_color || '#7c3aed'}18, ${report.settings?.accent_color || '#c026d3'}18)`"
                        >
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <div
                                    class="w-24 sm:w-32 h-16 sm:h-20 bg-white dark:bg-slate-700 rounded-lg shadow-xl border border-white/60 dark:border-slate-600 flex flex-col gap-1 p-2 group-hover:scale-105 group-hover:rotate-1 transition-all duration-500"
                                >
                                    <div
                                        class="h-1.5 rounded-full w-3/4"
                                        :style="`background:${report.settings?.primary_color || '#7c3aed'}`"
                                    ></div>
                                    <div
                                        class="h-1 rounded-full w-full bg-slate-100 dark:bg-slate-600"
                                    ></div>
                                    <div
                                        class="h-1 rounded-full w-5/6 bg-slate-100 dark:bg-slate-600"
                                    ></div>
                                    <div
                                        class="h-1 rounded-full w-2/3 bg-slate-100 dark:bg-slate-600"
                                    ></div>
                                    <div class="mt-1 flex gap-1">
                                        <div
                                            class="h-3 w-8 rounded"
                                            :style="`background:${report.settings?.accent_color || '#c026d3'}40`"
                                        ></div>
                                        <div
                                            class="h-3 w-6 rounded"
                                            :style="`background:${report.settings?.primary_color || '#7c3aed'}30`"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status badge -->
                            <div class="absolute top-3 right-3">
                                <span
                                    :class="[
                                        'px-2 py-0.5 text-[9px] font-bold rounded-full uppercase tracking-wide',
                                        statusBadge(report.status),
                                    ]"
                                >
                                    {{ report.status }}
                                </span>
                            </div>

                            <!-- Template badge -->
                            <div
                                v-if="report.template"
                                class="absolute bottom-3 left-3"
                            >
                                <span
                                    class="px-1.5 py-0.5 text-[9px] font-semibold bg-black/30 backdrop-blur-sm text-white rounded-md"
                                >
                                    <i class="fa-solid fa-layer-group mr-1"></i
                                    >{{ report.template.name }}
                                </span>
                            </div>

                            <!-- Hover overlay -->
                            <div
                                class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all duration-200 flex items-center justify-center gap-2"
                            >
                                <Link
                                    :href="route('reports.edit', report.slug)"
                                    class="px-3 py-1.5 bg-white text-slate-900 rounded-lg text-[10px] font-bold hover:bg-violet-50 transition-colors shadow-lg"
                                >
                                    <i
                                        class="fa-solid fa-pen mr-1 text-violet-600"
                                    ></i
                                    >Edit
                                </Link>
                                <Link
                                    :href="
                                        route('reports.preview', report.slug)
                                    "
                                    target="_blank"
                                    class="px-3 py-1.5 bg-violet-600 text-white rounded-lg text-[10px] font-bold hover:bg-violet-500 transition-colors shadow-lg"
                                >
                                    <i class="fa-solid fa-eye mr-1"></i>Preview
                                </Link>
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="p-3 sm:p-4">
                            <div
                                class="flex items-start justify-between gap-2 mb-2"
                            >
                                <h3
                                    class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm line-clamp-1 flex-1"
                                >
                                    {{ report.title }}
                                </h3>
                                <span
                                    v-if="report.is_public"
                                    class="shrink-0 text-[9px] text-emerald-500 font-bold flex items-center gap-1"
                                >
                                    <i class="fa-solid fa-globe text-[8px]"></i
                                    >Public
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-3">
                                <div
                                    class="flex justify-between text-[10px] text-slate-400 mb-1.5"
                                >
                                    <span>Completion</span>
                                    <span class="font-semibold"
                                        >{{ reportProgress(report) }}%</span
                                    >
                                </div>
                                <div
                                    class="relative w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"
                                >
                                    <div
                                        class="absolute top-0 left-0 h-full rounded-full transition-all duration-700 ease-out"
                                        :class="progressColor(report.status)"
                                        :style="{
                                            width: `${reportProgress(report)}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div
                                class="flex items-center gap-2 text-[10px] text-slate-400 mb-3 flex-wrap"
                            >
                                <span class="flex items-center gap-1">
                                    <i class="fa-regular fa-calendar"></i
                                    >{{ formatDate(report.updated_at) }}
                                </span>
                                <span
                                    class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"
                                ></span>
                                <span class="flex items-center gap-1">
                                    <i class="fa-regular fa-file"></i
                                    >{{ getReportPageCount(report) }} page{{
                                        getReportPageCount(report) !== 1
                                            ? "s"
                                            : ""
                                    }}
                                </span>
                                <span
                                    v-if="report.assignments_count > 0"
                                    class="ml-auto flex items-center gap-1 text-violet-500"
                                >
                                    <i class="fa-solid fa-users text-[8px]"></i
                                    >{{ report.assignments_count }}
                                </span>
                            </div>

                            <!-- Action bar -->
                            <div
                                class="flex items-center justify-between pt-2.5 border-t border-slate-100 dark:border-slate-700 gap-2"
                            >
                                <select
                                    :value="report.status"
                                    @change="
                                        (e) =>
                                            quickChangeStatus(
                                                report,
                                                e.target.value,
                                            )
                                    "
                                    class="text-[11px] border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-violet-500 transition flex-shrink-0"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>

                                <div
                                    class="flex items-center gap-0.5 flex-wrap justify-end"
                                >
                                    <Link
                                        :href="
                                            route('reports.edit', report.slug)
                                        "
                                        class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/20 text-slate-400 hover:text-violet-500 transition-colors"
                                        title="Edit"
                                    >
                                        <i
                                            class="fa-solid fa-pen text-[10px]"
                                        ></i>
                                    </Link>
                                    <button
                                        @click="openShareModal(report)"
                                        class="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-slate-400 hover:text-emerald-500 transition-colors"
                                        title="Share"
                                    >
                                        <i
                                            class="fa-solid fa-share-nodes text-[10px]"
                                        ></i>
                                    </button>
                                    <button
                                        @click="openAssignModal(report)"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-slate-400 hover:text-blue-500 transition-colors"
                                        title="Assign"
                                    >
                                        <i
                                            class="fa-solid fa-user-plus text-[10px]"
                                        ></i>
                                    </button>
                                    <button
                                        @click="openExportMenu(report)"
                                        class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors"
                                        title="Export"
                                    >
                                        <i
                                            class="fa-solid fa-download text-[10px]"
                                        ></i>
                                    </button>
                                    <button
                                        @click="openVersions(report)"
                                        class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 text-slate-400 hover:text-amber-500 transition-colors"
                                        title="Versions"
                                    >
                                        <i
                                            class="fa-solid fa-clock-rotate-left text-[10px]"
                                        ></i>
                                    </button>
                                    <button
                                        @click="duplicateReport(report)"
                                        class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors"
                                        title="Duplicate"
                                    >
                                        <i
                                            class="fa-regular fa-clone text-[10px]"
                                        ></i>
                                    </button>
                                    <button
                                        @click="confirmDelete(report)"
                                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-400 hover:text-red-500 transition-colors"
                                        title="Trash"
                                    >
                                        <i
                                            class="fa-solid fa-trash-can text-[10px]"
                                        ></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <div v-if="!reports.data?.length" class="col-span-full">
                    <EmptyReportsState />
                </div>
            </div>

            <!-- ════════════════════════════════════════
                 LIST VIEW
            ════════════════════════════════════════ -->
            <div
                v-else-if="viewMode === 'list' && !showTrashPanel"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm"
            >
                <div
                    class="overflow-x-auto"
                    style="-webkit-overflow-scrolling: touch"
                >
                    <table class="w-full text-sm min-w-[600px]">
                        <thead>
                            <tr
                                class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30"
                            >
                                <th class="px-4 py-3 w-8">
                                    <input
                                        type="checkbox"
                                        @change="toggleSelectAll"
                                        :checked="isAllSelected"
                                        class="w-3.5 h-3.5 rounded accent-violet-600 cursor-pointer"
                                    />
                                </th>
                                <th
                                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider"
                                >
                                    Report
                                </th>
                                <th
                                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell"
                                >
                                    Progress
                                </th>
                                <th
                                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell"
                                >
                                    Assigned
                                </th>
                                <th
                                    class="text-left px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell"
                                >
                                    Modified
                                </th>
                                <th
                                    class="text-right px-4 sm:px-6 py-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-700/50"
                        >
                            <TransitionGroup name="list-row" appear>
                                <tr
                                    v-for="report in reports.data"
                                    :key="report.id"
                                    class="group hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :value="report.id"
                                            v-model="selectedIds"
                                            class="w-3.5 h-3.5 rounded accent-violet-600 cursor-pointer"
                                        />
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center overflow-hidden"
                                                :style="`background:linear-gradient(135deg,${report.settings?.primary_color || '#7c3aed'}20,${report.settings?.accent_color || '#c026d3'}20)`"
                                            >
                                                <i
                                                    class="fa-solid fa-file-lines text-[11px]"
                                                    :style="`color:${report.settings?.primary_color || '#7c3aed'}`"
                                                ></i>
                                            </div>
                                            <div class="min-w-0">
                                                <Link
                                                    :href="
                                                        route(
                                                            'reports.edit',
                                                            report.slug,
                                                        )
                                                    "
                                                    class="font-semibold text-xs sm:text-sm text-slate-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 transition-colors line-clamp-1"
                                                >
                                                    {{ report.title }}
                                                </Link>
                                                <p
                                                    class="text-[10px] text-slate-400 mt-0.5"
                                                >
                                                    {{
                                                        getReportPageCount(
                                                            report,
                                                        )
                                                    }}
                                                    page{{
                                                        getReportPageCount(
                                                            report,
                                                        ) !== 1
                                                            ? "s"
                                                            : ""
                                                    }}
                                                    ·
                                                    {{
                                                        report.template?.name ||
                                                        "No template"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5">
                                        <select
                                            :value="report.status"
                                            @change="
                                                (e) =>
                                                    quickChangeStatus(
                                                        report,
                                                        e.target.value,
                                                    )
                                            "
                                            class="text-[11px] border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-violet-500"
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="published">
                                                Published
                                            </option>
                                            <option value="archived">
                                                Archived
                                            </option>
                                        </select>
                                    </td>
                                    <td
                                        class="px-4 sm:px-6 py-3.5 hidden md:table-cell"
                                    >
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-20 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"
                                            >
                                                <div
                                                    class="h-full rounded-full transition-all duration-500"
                                                    :class="
                                                        progressColor(
                                                            report.status,
                                                        )
                                                    "
                                                    :style="{
                                                        width: `${reportProgress(report)}%`,
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-[10px] text-slate-400"
                                                >{{
                                                    reportProgress(report)
                                                }}%</span
                                            >
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 sm:px-6 py-3.5 hidden lg:table-cell"
                                    >
                                        <span
                                            v-if="report.assignments_count > 0"
                                            class="text-[11px] text-violet-500 flex items-center gap-1"
                                        >
                                            <i
                                                class="fa-solid fa-users text-[9px]"
                                            ></i
                                            >{{ report.assignments_count }}
                                            assigned
                                        </span>
                                        <span
                                            v-else
                                            class="text-[11px] text-slate-400"
                                            >—</span
                                        >
                                    </td>
                                    <td
                                        class="px-4 sm:px-6 py-3.5 hidden sm:table-cell text-xs text-slate-400"
                                    >
                                        {{ formatDate(report.updated_at) }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 text-right">
                                        <div
                                            class="flex items-center justify-end gap-1"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'reports.edit',
                                                        report.slug,
                                                    )
                                                "
                                                class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/30 text-slate-400 hover:text-violet-500 transition-colors"
                                            >
                                                <i
                                                    class="fa-solid fa-pen text-xs"
                                                ></i>
                                            </Link>
                                            <button
                                                @click="openShareModal(report)"
                                                class="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-400 hover:text-emerald-500 transition-colors"
                                            >
                                                <i
                                                    class="fa-solid fa-share-nodes text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="openAssignModal(report)"
                                                class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-400 hover:text-blue-500 transition-colors"
                                            >
                                                <i
                                                    class="fa-solid fa-user-plus text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="openExportMenu(report)"
                                                class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors"
                                            >
                                                <i
                                                    class="fa-solid fa-download text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="openVersions(report)"
                                                class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 text-slate-400 hover:text-amber-500 transition-colors"
                                            >
                                                <i
                                                    class="fa-solid fa-clock-rotate-left text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="duplicateReport(report)"
                                                class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors"
                                            >
                                                <i
                                                    class="fa-regular fa-clone text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="confirmDelete(report)"
                                                class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors"
                                            >
                                                <i
                                                    class="fa-solid fa-trash-can text-xs"
                                                ></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </TransitionGroup>
                        </tbody>
                    </table>
                </div>
                <div v-if="!reports.data?.length" class="py-16">
                    <EmptyReportsState />
                </div>
                <div
                    v-if="reports.data?.length"
                    class="px-4 sm:px-6 py-3 border-t border-slate-100 dark:border-slate-700"
                >
                    <Pagination :links="reports.links" />
                </div>
            </div>

            <!-- ════════════════════════════════════════
                 TIMELINE VIEW
            ════════════════════════════════════════ -->
            <div
                v-else-if="viewMode === 'timeline' && !showTrashPanel"
                class="relative"
            >
                <div
                    class="absolute left-5 sm:left-8 top-0 bottom-0 w-px bg-gradient-to-b from-violet-400 via-fuchsia-400 to-transparent"
                ></div>

                <div class="space-y-4 sm:space-y-5">
                    <TransitionGroup name="timeline-item" appear>
                        <div
                            v-for="report in reports.data"
                            :key="report.id"
                            class="relative flex gap-5 sm:gap-8 group"
                        >
                            <!-- Dot -->
                            <div
                                class="shrink-0 relative z-10 w-10 h-10 sm:w-16 sm:h-16 flex items-center justify-center"
                            >
                                <div
                                    :class="[
                                        'w-5 h-5 rounded-full border-2 border-white dark:border-slate-900 shadow-lg transition-all duration-300 group-hover:scale-125',
                                        statusDot(report.status),
                                    ]"
                                ></div>
                            </div>

                            <!-- Card -->
                            <div
                                class="flex-1 mb-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 sm:p-5 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-xl transition-all duration-300 group-hover:-translate-y-0.5 relative overflow-hidden"
                            >
                                <div
                                    :class="`absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl bg-gradient-to-r ${statusGradient(report.status)}`"
                                ></div>

                                <div
                                    class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3"
                                >
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="flex items-center gap-2 mb-1 flex-wrap"
                                        >
                                            <span
                                                :class="[
                                                    'px-2 py-0.5 text-[9px] font-bold rounded-full uppercase tracking-wide',
                                                    statusBadge(report.status),
                                                ]"
                                            >
                                                {{ report.status }}
                                            </span>
                                            <span
                                                v-if="report.template"
                                                class="text-[10px] text-slate-400 font-medium"
                                            >
                                                <i
                                                    class="fa-solid fa-layer-group mr-1"
                                                ></i
                                                >{{ report.template.name }}
                                            </span>
                                            <span
                                                v-if="report.is_public"
                                                class="text-[10px] text-emerald-500 font-semibold"
                                            >
                                                <i
                                                    class="fa-solid fa-globe mr-1 text-[8px]"
                                                ></i
                                                >Public
                                            </span>
                                        </div>
                                        <Link
                                            :href="
                                                route(
                                                    'reports.edit',
                                                    report.slug,
                                                )
                                            "
                                            class="font-bold text-sm sm:text-base text-slate-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 transition-colors line-clamp-1"
                                        >
                                            {{ report.title }}
                                        </Link>

                                        <div class="mt-2 mb-2">
                                            <div
                                                class="flex justify-between text-[10px] text-slate-400 mb-1"
                                            >
                                                <span>Completion</span
                                                ><span
                                                    >{{
                                                        reportProgress(report)
                                                    }}%</span
                                                >
                                            </div>
                                            <div
                                                class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"
                                            >
                                                <div
                                                    class="h-full rounded-full transition-all duration-700"
                                                    :class="
                                                        progressColor(
                                                            report.status,
                                                        )
                                                    "
                                                    :style="{
                                                        width: `${reportProgress(report)}%`,
                                                    }"
                                                ></div>
                                            </div>
                                        </div>

                                        <p
                                            class="text-[11px] text-slate-400 flex items-center gap-3 flex-wrap"
                                        >
                                            <span
                                                ><i
                                                    class="fa-regular fa-calendar mr-1"
                                                ></i
                                                >{{
                                                    formatDate(
                                                        report.updated_at,
                                                    )
                                                }}</span
                                            >
                                            <span
                                                ><i
                                                    class="fa-regular fa-file mr-1"
                                                ></i
                                                >{{
                                                    getReportPageCount(report)
                                                }}
                                                page{{
                                                    getReportPageCount(
                                                        report,
                                                    ) !== 1
                                                        ? "s"
                                                        : ""
                                                }}</span
                                            >
                                            <span
                                                v-if="
                                                    report.assignments_count > 0
                                                "
                                                ><i
                                                    class="fa-solid fa-users mr-1"
                                                ></i
                                                >{{
                                                    report.assignments_count
                                                }}</span
                                            >
                                        </p>
                                    </div>

                                    <div
                                        class="flex flex-wrap items-center gap-1.5 shrink-0"
                                    >
                                        <select
                                            :value="report.status"
                                            @change="
                                                (e) =>
                                                    quickChangeStatus(
                                                        report,
                                                        e.target.value,
                                                    )
                                            "
                                            class="text-[11px] border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1.5 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-violet-500"
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="published">
                                                Published
                                            </option>
                                            <option value="archived">
                                                Archived
                                            </option>
                                        </select>
                                        <Link
                                            :href="
                                                route(
                                                    'reports.edit',
                                                    report.slug,
                                                )
                                            "
                                            class="px-3 py-1.5 bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 rounded-lg text-[10px] font-bold hover:bg-violet-100 transition-colors"
                                        >
                                            <i class="fa-solid fa-pen mr-1"></i
                                            >Edit
                                        </Link>
                                        <button
                                            @click="openShareModal(report)"
                                            class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg text-[10px] font-bold hover:bg-emerald-100 transition-colors"
                                        >
                                            <i
                                                class="fa-solid fa-share-nodes mr-1"
                                            ></i
                                            >Share
                                        </button>
                                        <button
                                            @click="openAssignModal(report)"
                                            class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-bold hover:bg-blue-100 transition-colors"
                                        >
                                            <i
                                                class="fa-solid fa-user-plus mr-1"
                                            ></i
                                            >Assign
                                        </button>
                                        <button
                                            @click="openExportMenu(report)"
                                            class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors"
                                        >
                                            <i
                                                class="fa-solid fa-download text-xs"
                                            ></i>
                                        </button>
                                        <button
                                            @click="openVersions(report)"
                                            class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 text-slate-400 hover:text-amber-500 transition-colors"
                                        >
                                            <i
                                                class="fa-solid fa-clock-rotate-left text-xs"
                                            ></i>
                                        </button>
                                        <button
                                            @click="duplicateReport(report)"
                                            class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors"
                                        >
                                            <i
                                                class="fa-regular fa-clone text-xs"
                                            ></i>
                                        </button>
                                        <button
                                            @click="confirmDelete(report)"
                                            class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors"
                                        >
                                            <i
                                                class="fa-solid fa-trash-can text-xs"
                                            ></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
                <div v-if="!reports.data?.length" class="ml-16">
                    <EmptyReportsState />
                </div>
            </div>

            <!-- ── Empty fallback ── -->
            <div
                v-if="
                    !reports.data?.length &&
                    !showTrashPanel &&
                    viewMode !== 'grid' &&
                    viewMode !== 'list' &&
                    viewMode !== 'timeline'
                "
                class="flex flex-col items-center justify-center py-16 text-center"
            >
                <EmptyReportsState />
            </div>

            <!-- ── Pagination (grid / timeline) ── -->
            <div
                v-if="
                    !showTrashPanel &&
                    viewMode !== 'list' &&
                    reports.data?.length
                "
                class="flex justify-center"
            >
                <Pagination :links="reports.links" />
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════
             DELETE CONFIRM MODAL
        ════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="deleteModal.show"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-md"
                        @click="deleteModal.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-md overflow-hidden"
                    >
                        <div class="p-6 text-center">
                            <div
                                class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4"
                            >
                                <i
                                    class="fa-solid fa-trash-can text-red-500 text-xl"
                                ></i>
                            </div>
                            <h3
                                class="font-black text-slate-900 dark:text-white text-lg mb-1"
                            >
                                Move to Trash?
                            </h3>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400 mb-6"
                            >
                                "<span
                                    class="font-semibold text-slate-700 dark:text-slate-300"
                                    >{{ deleteModal.report?.title }}</span
                                >" will be moved to Trash.
                            </p>
                            <div class="flex gap-3">
                                <button
                                    @click="deleteModal.show = false"
                                    class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold transition-all"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="deleteReport"
                                    class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-all shadow shadow-red-200 dark:shadow-red-900/30"
                                >
                                    <i class="fa-solid fa-trash-can mr-1.5"></i
                                    >Move to Trash
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ════════════════════════════════════════════════════
             FORCE DELETE CONFIRM MODAL
        ════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="forceDeleteModal.show"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-md"
                        @click="forceDeleteModal.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-md overflow-hidden"
                    >
                        <div class="p-6 text-center">
                            <div
                                class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4"
                            >
                                <i
                                    class="fa-solid fa-circle-exclamation text-red-500 text-xl"
                                ></i>
                            </div>
                            <h3
                                class="font-black text-slate-900 dark:text-white text-lg mb-1"
                            >
                                Permanently Delete?
                            </h3>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400 mb-6"
                            >
                                This cannot be undone. "<span
                                    class="font-semibold text-slate-700 dark:text-slate-300"
                                    >{{ forceDeleteModal.report?.title }}</span
                                >" will be gone forever.
                            </p>
                            <div class="flex gap-3">
                                <button
                                    @click="forceDeleteModal.show = false"
                                    class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold transition-all"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="forceDeleteReport"
                                    class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-all"
                                >
                                    <i class="fa-solid fa-trash-can mr-1.5"></i
                                    >Delete Forever
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ════════════════════════════════════════════════════
             SHARE MODAL
        ════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="shareModal.show"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-md"
                        @click="shareModal.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-md overflow-hidden"
                    >
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center"
                                >
                                    <i
                                        class="fa-solid fa-share-nodes text-emerald-600 text-sm"
                                    ></i>
                                </div>
                                <h3
                                    class="font-black text-slate-900 dark:text-white"
                                >
                                    Share Report
                                </h3>
                            </div>
                            <button
                                @click="shareModal.show = false"
                                class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition-colors"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="p-6 space-y-5">
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700"
                            >
                                <div>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white text-sm"
                                    >
                                        Public Access
                                    </p>
                                    <p
                                        class="text-[11px] text-slate-400 mt-0.5"
                                    >
                                        Anyone with the link can view
                                    </p>
                                </div>
                                <button
                                    @click="togglePublicAccess"
                                    :class="[
                                        'relative w-11 h-6 rounded-full transition-all duration-300',
                                        shareModal.isPublic
                                            ? 'bg-emerald-500'
                                            : 'bg-slate-200 dark:bg-slate-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-all duration-300',
                                            shareModal.isPublic
                                                ? 'translate-x-5'
                                                : '',
                                        ]"
                                    ></span>
                                </button>
                            </div>

                            <Transition name="slide-down">
                                <div v-if="shareModal.link" class="space-y-3">
                                    <label
                                        class="text-xs font-bold text-slate-500 uppercase tracking-wider"
                                        >Share Link</label
                                    >
                                    <div class="flex gap-2">
                                        <input
                                            :value="shareModal.link"
                                            readonly
                                            class="flex-1 min-w-0 px-3 py-2.5 text-[11px] font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 focus:outline-none"
                                        />
                                        <button
                                            @click="copyShareLink"
                                            :class="[
                                                'shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold transition-all',
                                                linkCopied
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'bg-emerald-600 hover:bg-emerald-700 text-white',
                                            ]"
                                        >
                                            <i
                                                :class="[
                                                    'fa-solid mr-1',
                                                    linkCopied
                                                        ? 'fa-check'
                                                        : 'fa-copy',
                                                ]"
                                            ></i
                                            >{{
                                                linkCopied ? "Copied!" : "Copy"
                                            }}
                                        </button>
                                    </div>
                                    <div class="flex gap-2">
                                        <a
                                            :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareModal.link)}`"
                                            target="_blank"
                                            class="flex-1 flex items-center justify-center gap-1.5 py-2 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 text-sky-600 dark:text-sky-400 rounded-xl text-[10px] font-bold hover:bg-sky-100 transition-colors"
                                        >
                                            <i
                                                class="fa-brands fa-x-twitter"
                                            ></i
                                            >Twitter
                                        </a>
                                        <button
                                            @click="sendEmailShare"
                                            class="flex-1 flex items-center justify-center gap-1.5 py-2 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 text-violet-600 dark:text-violet-400 rounded-xl text-[10px] font-bold hover:bg-violet-100 transition-colors"
                                        >
                                            <i class="fa-solid fa-envelope"></i
                                            >Email
                                        </button>
                                    </div>
                                    <button
                                        @click="revokeShareLink"
                                        class="w-full py-2 text-[10px] font-semibold text-red-400 hover:text-red-500 transition-colors"
                                    >
                                        <i
                                            class="fa-solid fa-link-slash mr-1"
                                        ></i
                                        >Revoke share link
                                    </button>
                                </div>
                            </Transition>

                            <div
                                v-if="shareModal.loading"
                                class="flex items-center justify-center py-4"
                            >
                                <div
                                    class="w-6 h-6 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ════════════════════════════════════════════════════
             ASSIGN MODAL
        ════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="assignModal.show"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-md"
                        @click="assignModal.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-md overflow-hidden"
                    >
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center"
                                >
                                    <i
                                        class="fa-solid fa-user-plus text-blue-600 text-sm"
                                    ></i>
                                </div>
                                <h3
                                    class="font-black text-slate-900 dark:text-white"
                                >
                                    Assign Report
                                </h3>
                            </div>
                            <button
                                @click="assignModal.show = false"
                                class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition-colors"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                Assigning:
                                <span
                                    class="font-semibold text-slate-700 dark:text-slate-300"
                                    >{{ assignModal.report?.title }}</span
                                >
                            </p>
                            <div class="relative">
                                <label
                                    class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block"
                                    >User ID or Email</label
                                >
                                <input
                                    v-model="assignModal.userInput"
                                    @input="handleAssignUserInput"
                                    type="text"
                                    placeholder="Enter user ID or email…"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                />
                                <div
                                    v-if="
                                        userSuggestions.length ||
                                        assignModal.userInput.trim().length >= 1
                                    "
                                    class="absolute left-0 right-0 z-20 mt-1 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl overflow-hidden max-h-56 overflow-y-auto scroll-smooth"
                                    style="scroll-behavior: smooth"
                                >
                                    <template v-if="userSuggestions.length">
                                        <button
                                            v-for="user in userSuggestions"
                                            :key="user.id"
                                            type="button"
                                            @click="selectUser(user)"
                                            class="w-full text-left px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <div
                                                class="flex items-center justify-between gap-3"
                                            >
                                                <span>{{ user.email }}</span>
                                                <span
                                                    class="text-xs text-slate-400"
                                                    >ID {{ user.id }}</span
                                                >
                                            </div>
                                            <div
                                                class="text-xs text-slate-500 dark:text-slate-400 mt-1"
                                            >
                                                {{ user.name }}
                                            </div>
                                        </button>
                                    </template>
                                    <div
                                        v-else
                                        class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        No users found.
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block"
                                    >Permission</label
                                >
                                <select
                                    v-model="assignModal.permission"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                >
                                    <option value="view">View only</option>
                                    <option value="edit">Can edit</option>
                                    <option value="manage">Full manage</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block"
                                    >Expires (optional)</label
                                >
                                <input
                                    v-model="assignModal.expiresAt"
                                    type="date"
                                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                />
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button
                                    @click="assignModal.show = false"
                                    class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="submitAssign"
                                    :disabled="!resolveAssignUserId()"
                                    class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all shadow shadow-blue-200 dark:shadow-blue-900/30"
                                >
                                    <i class="fa-solid fa-user-plus mr-1.5"></i
                                    >Assign
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ════════════════════════════════════════════════════
             EXPORT MENU MODAL
        ════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="exportMenu.show"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                >
                    <div
                        class="absolute inset-0"
                        @click="exportMenu.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-xs overflow-hidden"
                    >
                        <div
                            class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between"
                        >
                            <h3
                                class="font-bold text-slate-900 dark:text-white text-sm"
                            >
                                Export Report
                            </h3>
                            <button
                                @click="exportMenu.show = false"
                                class="text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="p-3 space-y-1">
                            <a
                                :href="
                                    route(
                                        'reports.export.pdf',
                                        exportMenu.report?.slug,
                                    )
                                "
                                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 transition-colors group"
                            >
                                <div
                                    class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center group-hover:scale-110 transition-transform"
                                >
                                    <i
                                        class="fa-solid fa-file-pdf text-red-500 text-sm"
                                    ></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold">PDF</p>
                                    <p class="text-[10px] text-slate-400">
                                        High-quality print format
                                    </p>
                                </div>
                            </a>
                            <a
                                :href="
                                    route(
                                        'reports.export.excel',
                                        exportMenu.report?.slug,
                                    )
                                "
                                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors group"
                            >
                                <div
                                    class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center group-hover:scale-110 transition-transform"
                                >
                                    <i
                                        class="fa-solid fa-file-excel text-emerald-500 text-sm"
                                    ></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold">Excel</p>
                                    <p class="text-[10px] text-slate-400">
                                        Tables & chart data
                                    </p>
                                </div>
                            </a>
                            <a
                                :href="
                                    route(
                                        'reports.export.csv',
                                        exportMenu.report?.slug,
                                    )
                                "
                                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors group"
                            >
                                <div
                                    class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center group-hover:scale-110 transition-transform"
                                >
                                    <i
                                        class="fa-solid fa-file-csv text-blue-500 text-sm"
                                    ></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold">CSV</p>
                                    <p class="text-[10px] text-slate-400">
                                        Raw data export
                                    </p>
                                </div>
                            </a>
                            <a
                                :href="
                                    route(
                                        'reports.export.image',
                                        exportMenu.report?.slug,
                                    )
                                "
                                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-violet-50 dark:hover:bg-violet-900/20 text-slate-700 dark:text-slate-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors group"
                            >
                                <div
                                    class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center group-hover:scale-110 transition-transform"
                                >
                                    <i
                                        class="fa-solid fa-file-image text-violet-500 text-sm"
                                    ></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold">PNG Image</p>
                                    <p class="text-[10px] text-slate-400">
                                        Visual snapshot
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ════════════════════════════════════════════════════
             VERSION HISTORY MODAL
        ════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="versionsModal.show"
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-md"
                        @click="versionsModal.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-md max-h-[80vh] overflow-hidden flex flex-col"
                    >
                        <div
                            class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center"
                                >
                                    <i
                                        class="fa-solid fa-clock-rotate-left text-amber-600 text-sm"
                                    ></i>
                                </div>
                                <h3
                                    class="font-black text-slate-900 dark:text-white"
                                >
                                    Version History
                                </h3>
                            </div>
                            <button
                                @click="versionsModal.show = false"
                                class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition-colors"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="overflow-y-auto flex-1 p-4">
                            <div
                                v-if="versionsModal.loading"
                                class="flex items-center justify-center py-12"
                            >
                                <div
                                    class="w-8 h-8 rounded-full border-2 border-amber-400 border-t-transparent animate-spin"
                                ></div>
                            </div>
                            <div
                                v-else-if="versionsModal.versions.length"
                                class="space-y-2"
                            >
                                <div
                                    v-for="v in versionsModal.versions"
                                    :key="v.id"
                                    class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-amber-200 dark:hover:border-amber-800 transition-colors group"
                                >
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-xs font-semibold text-slate-900 dark:text-white line-clamp-1"
                                        >
                                            {{ v.label }}
                                        </p>
                                        <p
                                            class="text-[10px] text-slate-400 mt-0.5"
                                        >
                                            v{{ v.version_number }} ·
                                            {{ formatDate(v.created_at) }}
                                        </p>
                                    </div>
                                    <button
                                        @click="restoreVersion(v)"
                                        class="ml-3 shrink-0 px-3 py-1 text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg hover:bg-amber-100 transition-colors opacity-0 group-hover:opacity-100"
                                    >
                                        Restore
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <i
                                    class="fa-solid fa-clock-rotate-left text-3xl text-slate-200 dark:text-slate-700 mb-3 block"
                                ></i>
                                <p class="text-sm text-slate-400">
                                    No versions yet
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    onMounted,
    onUnmounted,
    defineComponent,
    h,
} from "vue";
import { Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";

// ── Inline EmptyReportsState component (no separate file needed) ────
const EmptyReportsState = defineComponent({
    name: "EmptyReportsState",
    setup() {
        return () =>
            h(
                "div",
                {
                    class: "flex flex-col items-center justify-center py-16 text-center",
                },
                [
                    h(
                        "div",
                        {
                            class: "w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center mb-4 shadow-inner",
                        },
                        [
                            h("i", {
                                class: "fa-solid fa-file-lines text-2xl text-slate-300 dark:text-slate-600",
                            }),
                        ],
                    ),
                    h(
                        "h3",
                        {
                            class: "text-base font-bold text-slate-900 dark:text-white mb-1",
                        },
                        "No reports found",
                    ),
                    h(
                        "p",
                        { class: "text-xs text-slate-400 max-w-xs mb-5" },
                        "No reports match your filters, or you haven't created any yet.",
                    ),
                    h(
                        "button",
                        {
                            class: "px-5 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-semibold transition-all shadow shadow-violet-200 dark:shadow-violet-900/30",
                            onClick: () =>
                                router.visit(route("reports.create")),
                        },
                        [
                            h("i", { class: "fa-solid fa-plus mr-1.5" }),
                            "Create Report",
                        ],
                    ),
                ],
            );
    },
});

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
    reports: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    trashedReports: { type: Array, default: () => [] },
});

// ── Reactive State ─────────────────────────────────────────────────
const viewMode = ref("grid");
const activeStatKey = ref("");
const showTrashPanel = ref(false);
const selectedIds = ref([]);
const linkCopied = ref(false);
const exportListMenu = ref(false);
const exportListRef = ref(null);

const filters = reactive({
    search: props.filters?.search || "",
    status: props.filters?.status || "",
    sort: props.filters?.sort || "updated_at",
});

// Modal states
const deleteModal = ref({ show: false, report: null });
const forceDeleteModal = ref({ show: false, report: null });
const shareModal = ref({
    show: false,
    report: null,
    link: "",
    isPublic: false,
    loading: false,
});
const assignModal = ref({
    show: false,
    report: null,
    userId: null,
    userInput: "",
    permission: "view",
    expiresAt: "",
});
const userSuggestions = ref([]);
const isFetchingUsers = ref(false);
const exportMenu = ref({ show: false, report: null });
const versionsModal = ref({
    show: false,
    report: null,
    versions: [],
    loading: false,
});

// ── Static config ──────────────────────────────────────────────────
const viewModes = [
    { key: "grid", icon: "fa-solid fa-grip", label: "Grid" },
    { key: "list", icon: "fa-solid fa-list", label: "List" },
    { key: "timeline", icon: "fa-solid fa-timeline", label: "Timeline" },
];

const statCards = [
    {
        key: "total",
        label: "Total",
        icon: "fa-solid fa-layer-group",
        color: "violet",
    },
    {
        key: "published",
        label: "Published",
        icon: "fa-solid fa-globe",
        color: "emerald",
    },
    {
        key: "draft",
        label: "Drafts",
        icon: "fa-solid fa-pen-fancy",
        color: "amber",
    },
    {
        key: "archived",
        label: "Archived",
        icon: "fa-solid fa-box-archive",
        color: "slate",
    },
    {
        key: "trashed",
        label: "Trash",
        icon: "fa-solid fa-trash-can",
        color: "red",
    },
];

// ── Computed ───────────────────────────────────────────────────────
const hasActiveFilters = computed(
    () => filters.search || filters.status || filters.sort !== "updated_at",
);

const isAllSelected = computed(
    () =>
        props.reports.data?.length > 0 &&
        props.reports.data.every((r) => selectedIds.value.includes(r.id)),
);

// ── Helpers ────────────────────────────────────────────────────────
const statusBadge = (s) =>
    ({
        draft: "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300",
        published:
            "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300",
        archived:
            "bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400",
        trashed: "bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300",
    })[s] ?? "bg-slate-100 text-slate-600";

const statusDot = (s) =>
    ({
        draft: "bg-amber-400 shadow-amber-300",
        published: "bg-emerald-400 shadow-emerald-300",
        archived: "bg-slate-300",
        trashed: "bg-red-400 shadow-red-300",
    })[s] ?? "bg-slate-300";

const statusGradient = (s) =>
    ({
        draft: "from-amber-300 to-amber-500",
        published: "from-emerald-400 to-emerald-600",
        archived: "from-slate-300 to-slate-400",
        trashed: "from-red-400 to-red-600",
    })[s] ?? "from-violet-400 to-fuchsia-500";

const progressColor = (s) =>
    ({
        draft: "bg-gradient-to-r from-amber-400 to-amber-500",
        published: "bg-gradient-to-r from-emerald-400 to-emerald-500",
        archived: "bg-gradient-to-r from-slate-300 to-slate-400",
    })[s] ?? "bg-gradient-to-r from-violet-400 to-fuchsia-500";

const reportProgress = (report) => {
    if (report.status === "published") return 100;
    if (report.status === "archived") return 80;
    const pages = report.total_pages || 1;
    return Math.min(70, Math.max(10, pages * 15));
};

const formatDate = (date) => {
    if (!date) return "—";
    const diff = Math.floor((Date.now() - new Date(date)) / 1000);
    if (diff < 60) return "just now";
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
    return new Date(date).toLocaleDateString("en-GB", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
};

const getReportPageCount = (report) => {
    if (report.total_pages && report.total_pages > 0) return report.total_pages;
    if (report.content && Array.isArray(report.content))
        return Math.max(1, report.content.length);
    return 1;
};

// ── Filters ────────────────────────────────────────────────────────
const applyFilters = () =>
    router.get(
        route("reports.index"),
        {
            search: filters.search || undefined,
            status: filters.status || undefined,
            sort: filters.sort,
        },
        { preserveState: true, replace: true },
    );

const resetFilters = () => {
    filters.search = "";
    filters.status = "";
    filters.sort = "updated_at";
    applyFilters();
};

const quickFilterStatus = (key) => {
    if (key === "trashed") {
        activeStatKey.value =
            activeStatKey.value === "trashed" ? "" : "trashed";
        showTrashPanel.value = activeStatKey.value === "trashed";
        return;
    }
    showTrashPanel.value = false;
    const mapped = key === "total" ? "" : key;
    activeStatKey.value = activeStatKey.value === mapped ? "" : mapped;
    filters.status = activeStatKey.value;
    applyFilters();
};

let searchTimer = null;
const debouncedSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 450);
};

// ── Selection ──────────────────────────────────────────────────────
const toggleSelectAll = () => {
    selectedIds.value = isAllSelected.value
        ? []
        : props.reports.data.map((r) => r.id);
};

// ── Actions ────────────────────────────────────────────────────────
const quickChangeStatus = (report, status) => {
    router.patch(
        route("reports.status", report.slug),
        { status },
        {
            preserveState: true,
            onSuccess: () =>
                window.showToast?.(`Status changed to ${status}`, "success"),
        },
    );
};

const confirmDelete = (report) => {
    deleteModal.value = { show: true, report };
};

const deleteReport = () => {
    router.delete(route("reports.destroy", deleteModal.value.report.slug), {
        onSuccess: () => {
            deleteModal.value.show = false;
            window.showToast?.("Moved to trash", "success");
        },
    });
};

const confirmForceDelete = (report) => {
    forceDeleteModal.value = { show: true, report };
};

const forceDeleteReport = () => {
    router.delete(
        route("reports.force-delete", forceDeleteModal.value.report.slug),
        {
            onSuccess: () => {
                forceDeleteModal.value.show = false;
                window.showToast?.("Permanently deleted", "success");
            },
        },
    );
};

const restoreReport = (report) => {
    router.post(
        route("reports.restore", report.slug),
        {},
        {
            onSuccess: () => window.showToast?.("Report restored!", "success"),
        },
    );
};

const duplicateReport = (report) => {
    router.post(
        route("reports.duplicate", report.slug),
        {},
        {
            onSuccess: () =>
                window.showToast?.("Report duplicated!", "success"),
        },
    );
};

const bulkDelete = () => {
    if (!confirm(`Move ${selectedIds.value.length} reports to Trash?`)) return;
    const slugs = selectedIds.value
        .map((id) => props.reports.data.find((r) => r.id === id)?.slug)
        .filter(Boolean);
    slugs.forEach((slug) =>
        router.delete(route("reports.destroy", slug), { preserveState: true }),
    );
    selectedIds.value = [];
};

// ── Share ──────────────────────────────────────────────────────────
const openShareModal = async (report) => {
    shareModal.value = {
        show: true,
        report,
        link: "",
        isPublic: report.is_public || false,
        loading: true,
    };
    try {
        const res = await fetch(route("reports.share", report.slug), {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")
                    ?.content,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
        });
        const data = await res.json();
        shareModal.value.link = data.url || "";
        shareModal.value.isPublic = true;
    } finally {
        shareModal.value.loading = false;
    }
};

const togglePublicAccess = async () => {
    if (shareModal.value.isPublic) await revokeShareLink();
    else await openShareModal(shareModal.value.report);
};

const revokeShareLink = async () => {
    await fetch(route("reports.share.revoke", shareModal.value.report.slug), {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")
                ?.content,
        },
    });
    shareModal.value.link = "";
    shareModal.value.isPublic = false;
};

const copyShareLink = async () => {
    await navigator.clipboard.writeText(shareModal.value.link);
    linkCopied.value = true;
    setTimeout(() => {
        linkCopied.value = false;
    }, 2000);
};

const sendEmailShare = () =>
    window.open(
        `mailto:?subject=Check out this report&body=${encodeURIComponent(shareModal.value.link)}`,
    );

// ── Assign ─────────────────────────────────────────────────────────
const fetchUserSuggestions = (query = "") => {
    const trimmed = query.trim();
    if (trimmed.length === 0) {
        isFetchingUsers.value = false;
        userSuggestions.value = [];
        return;
    }

    isFetchingUsers.value = true;
    userSuggestions.value = [];

    window.axios
        .get("/api/search/users", {
            params: { q: trimmed },
        })
        .then((response) => {
            userSuggestions.value = response.data?.users || [];
        })
        .catch(() => {
            userSuggestions.value = [];
        })
        .finally(() => {
            isFetchingUsers.value = false;
        });
};

let assignUserSearchTimer = null;
const handleAssignUserInput = () => {
    assignModal.value.userId = null;
    clearTimeout(assignUserSearchTimer);
    fetchUserSuggestions(assignModal.value.userInput);
};

const resolveAssignUserId = () => {
    if (assignModal.value.userId) return assignModal.value.userId;

    const typed = assignModal.value.userInput.trim();
    if (!typed) return null;

    const numeric = Number(typed);
    if (Number.isInteger(numeric)) return numeric;

    const matched = userSuggestions.value.find(
        (user) =>
            user.email.toLowerCase() === typed.toLowerCase() ||
            user.name.toLowerCase() === typed.toLowerCase(),
    );

    return matched?.id || null;
};

const selectUser = (user) => {
    assignModal.value.userId = user.id;
    assignModal.value.userInput = user.email;
    userSuggestions.value = [];
};

const openAssignModal = (report) => {
    assignModal.value = {
        show: true,
        report,
        userId: null,
        userInput: "",
        permission: "view",
        expiresAt: "",
    };
    userSuggestions.value = [];
};

const submitAssign = () => {
    const user_id = resolveAssignUserId();
    if (!user_id) {
        window.showToast?.("Please enter a valid user email or ID.", "error");
        return;
    }

    router.post(
        route("reports.assign", assignModal.value.report.slug),
        {
            user_id,
            permission: assignModal.value.permission,
            expires_at: assignModal.value.expiresAt || null,
        },
        {
            preserveState: true,
            onSuccess: () => {
                assignModal.value.show = false;
                window.showToast?.("Report assigned successfully!", "success");
            },
            onError: (errors) => {
                const message =
                    errors?.user_id?.[0] ||
                    errors?.permission?.[0] ||
                    errors?.expires_at?.[0] ||
                    "Assignment failed. Please check the information and try again.";
                window.showToast?.(message, "error");
            },
        },
    );
};

// ── Export ─────────────────────────────────────────────────────────
const openExportMenu = (report) => {
    exportMenu.value = { show: true, report };
};

const exportReportsList = (format) => {
    exportListMenu.value = false;
    const data = props.reports.data || [];

    if (format === "json") {
        const json = JSON.stringify(
            data.map((r) => ({
                id: r.id,
                title: r.title,
                status: r.status,
                pages: r.total_pages || 1,
                template: r.template?.name || "",
                updated_at: r.updated_at,
                is_public: r.is_public,
            })),
            null,
            2,
        );
        downloadBlob(json, "reports.json", "application/json");
        return;
    }

    const headers = [
        "ID",
        "Title",
        "Status",
        "Pages",
        "Template",
        "Public",
        "Modified",
    ];
    const rows = data.map((r) => [
        r.id,
        `"${(r.title || "").replace(/"/g, '""')}"`,
        r.status,
        r.total_pages || 1,
        r.template?.name || "",
        r.is_public ? "Yes" : "No",
        r.updated_at,
    ]);
    const csv = [headers, ...rows].map((r) => r.join(",")).join("\n");
    downloadBlob(csv, "reports.csv", "text/csv");
};

const downloadBlob = (content, filename, type) => {
    const blob = new Blob([content], { type });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
};

// ── Versions ───────────────────────────────────────────────────────
const openVersions = async (report) => {
    versionsModal.value = { show: true, report, versions: [], loading: true };
    try {
        const res = await fetch(route("reports.versions", report.slug), {
            headers: { Accept: "application/json" },
        });
        const data = await res.json();
        versionsModal.value.versions = data.versions || [];
    } finally {
        versionsModal.value.loading = false;
    }
};

const restoreVersion = async (version) => {
    if (!confirm(`Restore version ${version.version_number}?`)) return;
    await fetch(
        route("reports.versions.restore", {
            report: versionsModal.value.report.slug,
            version: version.id,
        }),
        {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")
                    ?.content,
            },
        },
    );
    versionsModal.value.show = false;
    router.reload({ only: ["reports"] });
    window.showToast?.("Version restored!", "success");
};

// ── Keyboard shortcuts ─────────────────────────────────────────────
const handleKeydown = (e) => {
    if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) return;
    if (e.key === "n" || e.key === "N") router.visit(route("reports.create"));
    if (e.key === "Escape") {
        deleteModal.value.show = false;
        forceDeleteModal.value.show = false;
        shareModal.value.show = false;
        assignModal.value.show = false;
        exportMenu.value.show = false;
        versionsModal.value.show = false;
        exportListMenu.value = false;
    }
    if (e.key === "1") viewMode.value = "grid";
    if (e.key === "2") viewMode.value = "list";
    if (e.key === "3") viewMode.value = "timeline";
};

const handleOutsideClick = (e) => {
    // Only close if click is truly outside the export list ref
    if (
        exportListRef.value &&
        !exportListRef.value.contains(e.target) &&
        exportListMenu.value
    ) {
        exportListMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
    document.addEventListener("click", handleOutsideClick);
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
    document.removeEventListener("click", handleOutsideClick);
});
</script>

<style scoped>
.filter-select {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    font-size: 0.75rem;
    border-width: 1px;
    border-style: solid;
    border-color: #e2e8f0;
    border-radius: 1rem;
    background-color: #ffffff;
    color: #0f172a;
    transition: all 0.2s ease;
}

@media (min-width: 640px) {
    .filter-select {
        padding-top: 0.625rem;
        padding-bottom: 0.625rem;
        font-size: 0.875rem;
    }
}

.dark .filter-select {
    border-color: #334155;
    background-color: #0f172a;
    color: #e2e8f0;
}

.filter-select:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.2);
    border-color: transparent;
}

.card-pop-enter-active {
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.card-pop-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(0.97);
}
.card-pop-leave-active {
    transition: all 0.2s ease;
}
.card-pop-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

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

.timeline-item-enter-active {
    transition: all 0.35s cubic-bezier(0.34, 1.3, 0.64, 1);
}
.timeline-item-enter-from {
    opacity: 0;
    transform: translateX(-16px);
}

.modal-enter-active {
    transition: all 0.25s cubic-bezier(0.34, 1.3, 0.64, 1);
}
.modal-enter-from {
    opacity: 0;
}
.modal-leave-active {
    transition: all 0.18s ease;
}
.modal-leave-to {
    opacity: 0;
}

.slide-down-enter-active {
    transition: all 0.25s ease;
}
.slide-down-enter-from {
    opacity: 0;
    transform: translateY(-8px);
}
.slide-down-leave-active {
    transition: all 0.2s ease;
}
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
<!-- final -->
<!-- in report/index
make sure that any login users can share or assign their own reports and also notify using sweet alert on success or fail and also make sure that if any report is shared then their must a badge of shared and also show number of users to whom the report is assign by the owner
and donot change any things

make alert beautiful and mindblowing on other then admin under share model is not showing like for admin 
remove ther permistion level from sharing and assigning report in this file only and -->