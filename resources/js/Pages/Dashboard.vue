<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <!-- Greeting + avatar -->
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-black text-base shadow-lg shadow-indigo-500/25 select-none"
                        >
                            {{
                                $page.props.auth.user.name
                                    .charAt(0)
                                    .toUpperCase()
                            }}
                        </div>
                        <div
                            class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 rounded-full border-2 border-white"
                        ></div>
                    </div>
                    <div>
                        <h2
                            class="text-sm font-black text-slate-800 tracking-tight leading-none"
                        >
                            Good {{ timeOfDay }},
                            {{ $page.props.auth.user.name.split(" ")[0] }}
                        </h2>
                        <p
                            class="text-[11px] text-slate-400 mt-0.5 font-medium"
                        >
                            {{ todayLabel }} · {{ currentTime }}
                        </p>
                    </div>
                </div>

                <!-- Header actions -->
                <div class="flex items-center gap-2">
                    <!-- Search -->
                    <div class="relative hidden sm:block">
                        <svg
                            class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            v-model="searchQuery"
                            placeholder="Search reports…"
                            class="pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 focus:bg-white w-44 focus:w-60 transition-all duration-200 text-slate-700 placeholder:text-slate-400"
                        />
                    </div>

                    <!-- Notification bell -->
                    <button
                        class="relative p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-all shadow-sm hover:shadow"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                        <span
                            v-if="(stats?.draft_reports || 0) > 0"
                            class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white"
                        ></span>
                    </button>

                    <!-- New Report CTA -->
                    <Link
                        :href="route('reports.create')"
                        class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-px active:translate-y-0"
                    >
                        <svg
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        New Report
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- STAT CARDS ROW                                             -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="(card, idx) in statCards"
                        :key="card.key"
                        class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 group cursor-default"
                        :style="{ animationDelay: idx * 80 + 'ms' }"
                        style="animation: cardIn 0.4s ease both"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                                :class="[
                                    card.color.iconBg,
                                    card.color.iconHover,
                                ]"
                            >
                                <span
                                    class="text-xl leading-none select-none"
                                    >{{ card.icon }}</span
                                >
                            </div>
                            <span
                                class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                :class="card.color.badge"
                                >{{ card.badge }}</span
                            >
                        </div>
                        <div
                            class="text-3xl font-black text-slate-800 mb-0.5 tabular-nums"
                        >
                            {{ stats?.[card.key] ?? card.fallback }}
                        </div>
                        <div class="text-xs text-slate-400 font-semibold mb-3">
                            {{ card.label }}
                        </div>
                        <div
                            class="h-1.5 bg-slate-100 rounded-full overflow-hidden"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :class="card.color.bar"
                                :style="{ width: card.progress }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- MAIN GRID: Reports + Right sidebar                         -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- ── Recent Reports Table (2 cols) ── -->
                    <div
                        class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col"
                    >
                        <!-- Card header -->
                        <div
                            class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-3.5 h-3.5 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h3
                                        class="font-bold text-slate-800 text-sm leading-none"
                                    >
                                        Recent Reports
                                    </h3>
                                    <p
                                        class="text-[10px] text-slate-400 mt-0.5"
                                    >
                                        Your latest report activity
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Filter pills -->
                                <div
                                    class="flex items-center bg-slate-100 rounded-lg p-0.5 gap-0.5"
                                >
                                    <button
                                        v-for="f in STATUS_FILTERS"
                                        :key="f"
                                        @click="activeFilter = f"
                                        class="px-2.5 py-1 text-[10px] font-bold rounded-md transition-all capitalize"
                                        :class="
                                            activeFilter === f
                                                ? 'bg-white text-slate-700 shadow-sm'
                                                : 'text-slate-400 hover:text-slate-600'
                                        "
                                    >
                                        {{ f }}
                                    </button>
                                </div>
                                <Link
                                    :href="route('reports.index')"
                                    class="text-[11px] text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1 px-2 py-1 hover:bg-indigo-50 rounded-lg transition-colors"
                                >
                                    All
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Report rows -->
                        <div class="flex-1 min-h-0 overflow-auto">
                            <template v-if="filteredReports.length > 0">
                                <div
                                    v-for="(report, idx) in filteredReports"
                                    :key="report.id"
                                    class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50/80 border-b border-slate-50/80 last:border-0 transition-all group/row report-row"
                                    :style="{ animationDelay: idx * 50 + 'ms' }"
                                >
                                    <!-- Icon -->
                                    <div
                                        class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                                        :style="{
                                            backgroundColor:
                                                STATUS_COLORS[report.status]
                                                    ?.bg,
                                        }"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            :style="{
                                                color: STATUS_COLORS[
                                                    report.status
                                                ]?.icon,
                                            }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                    </div>
                                    <!-- Meta -->
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="flex items-center gap-2 min-w-0"
                                        >
                                            <span
                                                class="font-semibold text-slate-800 text-sm truncate"
                                                >{{ report.title }}</span
                                            >
                                            <span
                                                class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-full capitalize leading-none"
                                                :style="{
                                                    backgroundColor:
                                                        STATUS_COLORS[
                                                            report.status
                                                        ]?.bg,
                                                    color: STATUS_COLORS[
                                                        report.status
                                                    ]?.text,
                                                }"
                                            >
                                                {{ report.status }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center gap-1.5 mt-0.5"
                                        >
                                            <span
                                                class="text-[10px] text-slate-400 font-medium"
                                                >{{
                                                    formatDate(
                                                        report.updated_at,
                                                    )
                                                }}</span
                                            >
                                            <span
                                                class="w-1 h-1 rounded-full bg-slate-300 flex-shrink-0"
                                            ></span>
                                            <span
                                                class="text-[10px] text-slate-400"
                                                >{{
                                                    report.pages_count || 1
                                                }}
                                                page{{
                                                    (report.pages_count ||
                                                        1) !== 1
                                                        ? "s"
                                                        : ""
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                    <!-- Hover actions -->
                                    <div
                                        class="flex items-center gap-1 opacity-0 group-hover/row:opacity-100 transition-all duration-150 translate-x-1 group-hover/row:translate-x-0"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'reports.edit',
                                                    report.slug,
                                                )
                                            "
                                            class="p-1.5 rounded-lg hover:bg-indigo-100 text-slate-400 hover:text-indigo-600 transition-colors"
                                            title="Edit"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="
                                                route(
                                                    'reports.preview',
                                                    report.slug,
                                                )
                                            "
                                            target="_blank"
                                            class="p-1.5 rounded-lg hover:bg-emerald-100 text-slate-400 hover:text-emerald-600 transition-colors"
                                            title="Preview"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </Link>
                                        <a
                                            :href="
                                                route(
                                                    'reports.download',
                                                    report.slug,
                                                )
                                            "
                                            target="_blank"
                                            class="p-1.5 rounded-lg hover:bg-rose-100 text-slate-400 hover:text-rose-600 transition-colors"
                                            title="Download PDF"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty -->
                            <div v-else class="py-16 px-6 text-center">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4"
                                >
                                    <svg
                                        class="w-7 h-7 text-slate-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                                <p
                                    class="text-slate-500 font-bold text-sm mb-1"
                                >
                                    {{
                                        activeFilter === "all"
                                            ? "No reports yet"
                                            : `No ${activeFilter} reports`
                                    }}
                                </p>
                                <p class="text-slate-400 text-xs mb-5">
                                    {{
                                        activeFilter === "all"
                                            ? "Create your first report to get started."
                                            : "Try a different filter."
                                    }}
                                </p>
                                <Link
                                    v-if="activeFilter === 'all'"
                                    :href="route('reports.create')"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-500/20"
                                >
                                    <svg
                                        class="w-3.5 h-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                    Create First Report
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- ── Right column ── -->
                    <div class="flex flex-col gap-4">
                        <!-- Quick Actions -->
                        <div
                            class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
                        >
                            <div
                                class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2"
                            >
                                <svg
                                    class="w-3.5 h-3.5 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"
                                    />
                                </svg>
                                <h3
                                    class="font-bold text-slate-700 text-xs uppercase tracking-wider"
                                >
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="p-2 space-y-0.5">
                                <Link
                                    v-for="action in QUICK_ACTIONS"
                                    :key="action.label"
                                    :href="route(action.route)"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all group/action"
                                    :class="action.hoverBg"
                                >
                                    <div
                                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors flex-shrink-0"
                                        :class="[
                                            action.iconBg,
                                            action.iconHoverBg,
                                        ]"
                                    >
                                        <span
                                            class="text-base leading-none select-none"
                                            >{{ action.icon }}</span
                                        >
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="text-xs text-slate-700 font-bold leading-none"
                                        >
                                            {{ action.label }}
                                        </div>
                                        <div
                                            class="text-[10px] text-slate-400 mt-0.5"
                                        >
                                            {{ action.desc }}
                                        </div>
                                    </div>
                                    <svg
                                        class="w-3.5 h-3.5 text-slate-300 transition-all duration-150 group-hover/action:translate-x-0.5"
                                        :class="action.arrowHover"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Breakdown mini chart -->
                        <div
                            class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"
                        >
                            <div
                                class="flex items-center justify-between mb-3.5"
                            >
                                <div>
                                    <h3
                                        class="font-bold text-slate-800 text-xs uppercase tracking-wider"
                                    >
                                        Status Breakdown
                                    </h3>
                                    <p
                                        class="text-[10px] text-slate-400 mt-0.5"
                                    >
                                        Distribution overview
                                    </p>
                                </div>
                                <span
                                    class="text-xs font-black text-slate-700 tabular-nums"
                                    >{{ stats?.total_reports || 0 }}</span
                                >
                            </div>
                            <!-- Stacked bar -->
                            <div
                                class="h-2 rounded-full overflow-hidden flex bg-slate-100 mb-3"
                            >
                                <div
                                    v-for="item in BREAKDOWN_ITEMS"
                                    :key="item.key"
                                    class="h-full transition-all duration-700 first:rounded-l-full last:rounded-r-full"
                                    :class="item.bar"
                                    :style="{ width: breakdownPct(item.key) }"
                                ></div>
                            </div>
                            <!-- Legend rows -->
                            <div class="space-y-2">
                                <div
                                    v-for="item in BREAKDOWN_ITEMS"
                                    :key="item.key"
                                >
                                    <div
                                        class="flex items-center justify-between mb-1"
                                    >
                                        <div class="flex items-center gap-1.5">
                                            <div
                                                class="w-2 h-2 rounded-sm flex-shrink-0"
                                                :class="item.bar"
                                            ></div>
                                            <span
                                                class="text-[11px] font-semibold text-slate-600"
                                                >{{ item.label }}</span
                                            >
                                        </div>
                                        <span
                                            class="text-[11px] font-black"
                                            :class="item.textColor"
                                            >{{ stats?.[item.key] || 0 }}</span
                                        >
                                    </div>
                                    <div
                                        class="h-1 bg-slate-100 rounded-full overflow-hidden"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all duration-700"
                                            :class="item.bar"
                                            :style="{
                                                width: breakdownPct(item.key),
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rotating tips card -->
                        <div
                            class="rounded-2xl p-4 text-white overflow-hidden relative select-none"
                            style="
                                background: linear-gradient(
                                    135deg,
                                    #4338ca 0%,
                                    #6d28d9 60%,
                                    #5b21b6 100%
                                );
                            "
                        >
                            <!-- Dot grid texture -->
                            <div
                                class="absolute inset-0 opacity-[0.07]"
                                style="
                                    background-image: radial-gradient(
                                        circle,
                                        white 1px,
                                        transparent 1px
                                    );
                                    background-size: 18px 18px;
                                "
                            ></div>
                            <div
                                class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-white/10 blur-2xl"
                            ></div>
                            <div class="relative">
                                <div
                                    class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center mb-2.5"
                                >
                                    <svg
                                        class="w-3.5 h-3.5 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"
                                        />
                                    </svg>
                                </div>
                                <p
                                    class="text-[10px] font-black uppercase tracking-widest text-indigo-300 mb-1"
                                >
                                    Pro Tip {{ tipIndex + 1 }}/{{ TIPS.length }}
                                </p>
                                <p
                                    class="text-[11px] text-indigo-100 leading-relaxed"
                                >
                                    {{ TIPS[tipIndex].text }}
                                </p>
                                <div
                                    class="flex items-center justify-between mt-3"
                                >
                                    <div class="flex gap-1">
                                        <button
                                            v-for="(_, i) in TIPS"
                                            :key="i"
                                            @click="tipIndex = i"
                                            class="h-1.5 rounded-full transition-all duration-200"
                                            :class="
                                                tipIndex === i
                                                    ? 'w-4 bg-white'
                                                    : 'w-1.5 bg-white/30 hover:bg-white/50'
                                            "
                                        ></button>
                                    </div>
                                    <button
                                        @click="
                                            tipIndex =
                                                (tipIndex + 1) % TIPS.length
                                        "
                                        class="text-[10px] text-indigo-300 hover:text-white font-bold transition-colors"
                                    >
                                        Next →
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════ -->
                <!-- BOTTOM ROW: Shortcuts + Element Library                    -->
                <!-- ══════════════════════════════════════════════════════════ -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Keyboard Shortcuts -->
                    <div
                        class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
                    >
                        <div
                            class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2"
                        >
                            <div
                                class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center"
                            >
                                <svg
                                    class="w-3 h-3 text-slate-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                                    />
                                </svg>
                            </div>
                            <h3
                                class="font-bold text-slate-700 text-xs uppercase tracking-wider"
                            >
                                Editor Shortcuts
                            </h3>
                        </div>
                        <div class="p-4 space-y-1.5">
                            <div
                                v-for="sc in SHORTCUTS"
                                :key="sc.key"
                                class="flex items-center justify-between py-0.5 group/sc"
                            >
                                <span
                                    class="text-[11px] text-slate-500 group-hover/sc:text-slate-700 transition-colors font-medium"
                                    >{{ sc.action }}</span
                                >
                                <kbd
                                    class="text-[10px] bg-slate-100 group-hover/sc:bg-slate-200 text-slate-600 px-2 py-0.5 rounded-lg border border-slate-200 font-mono font-bold transition-colors whitespace-nowrap"
                                    >{{ sc.key }}</kbd
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Element Library -->
                    <div
                        class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-3.5 h-3.5 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h3
                                        class="font-bold text-slate-800 text-xs uppercase tracking-wider leading-none"
                                    >
                                        Element Library
                                    </h3>
                                    <p
                                        class="text-[10px] text-slate-400 mt-0.5"
                                    >
                                        Drag any onto your canvas
                                    </p>
                                </div>
                            </div>
                            <span
                                class="text-[10px] text-indigo-600 font-black bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100"
                                >{{ ELEMENT_TOTAL }}+ types</span
                            >
                        </div>

                        <!-- Categorised grid -->
                        <div class="space-y-3.5">
                            <div
                                v-for="group in ELEMENT_GROUPS"
                                :key="group.label"
                            >
                                <div
                                    class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1.5 flex items-center gap-2"
                                >
                                    <span>{{ group.label }}</span>
                                    <div class="flex-1 h-px bg-slate-100"></div>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <div
                                        v-for="el in group.items"
                                        :key="el.name"
                                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/80 transition-all cursor-default group/el"
                                    >
                                        <span class="text-sm leading-none">{{
                                            el.icon
                                        }}</span>
                                        <span
                                            class="text-[10px] text-slate-500 group-hover/el:text-indigo-600 font-semibold transition-colors leading-none"
                                            >{{ el.name }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";

// ─── Props ────────────────────────────────────────────────────────────────────
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

// ─── Reactive state ───────────────────────────────────────────────────────────
const searchQuery = ref("");
const activeFilter = ref("all");
const tipIndex = ref(0);
const currentTime = ref("");
let clockTimer = null;

// ─── Constants ────────────────────────────────────────────────────────────────
const STATUS_FILTERS = ["all", "published", "draft", "archived"];

const STATUS_COLORS = {
    published: { bg: "#dcfce7", text: "#15803d", icon: "#16a34a" },
    draft: { bg: "#fef9c3", text: "#a16207", icon: "#ca8a04" },
    archived: { bg: "#f1f5f9", text: "#64748b", icon: "#94a3b8" },
};

const statCards = [
    {
        key: "total_reports",
        label: "Total Reports",
        badge: "All time",
        icon: "📄",
        fallback: 0,
        progress: "65%",
        color: {
            iconBg: "bg-indigo-50",
            iconHover: "group-hover:bg-indigo-100",
            badge: "bg-indigo-50 text-indigo-600",
            bar: "bg-indigo-500",
        },
    },
    {
        key: "published_reports",
        label: "Published",
        badge: "Live",
        icon: "✅",
        fallback: 0,
        progress: "45%",
        color: {
            iconBg: "bg-emerald-50",
            iconHover: "group-hover:bg-emerald-100",
            badge: "bg-emerald-50 text-emerald-600",
            bar: "bg-emerald-500",
        },
    },
    {
        key: "draft_reports",
        label: "Drafts",
        badge: "In progress",
        icon: "✏️",
        fallback: 0,
        progress: "30%",
        color: {
            iconBg: "bg-amber-50",
            iconHover: "group-hover:bg-amber-100",
            badge: "bg-amber-50 text-amber-600",
            bar: "bg-amber-400",
        },
    },
    {
        key: "total_templates",
        label: "Templates",
        badge: "Available",
        icon: "🗂️",
        fallback: 12,
        progress: "80%",
        color: {
            iconBg: "bg-violet-50",
            iconHover: "group-hover:bg-violet-100",
            badge: "bg-violet-50 text-violet-600",
            bar: "bg-violet-500",
        },
    },
];

const BREAKDOWN_ITEMS = [
    {
        key: "published_reports",
        label: "Published",
        bar: "bg-emerald-500",
        textColor: "text-emerald-600",
    },
    {
        key: "draft_reports",
        label: "Drafts",
        bar: "bg-amber-400",
        textColor: "text-amber-600",
    },
    {
        key: "archived_reports",
        label: "Archived",
        bar: "bg-slate-300",
        textColor: "text-slate-500",
    },
];

const QUICK_ACTIONS = [
    {
        route: "reports.create",
        label: "New Report",
        desc: "Start from scratch",
        icon: "📝",
        hoverBg: "hover:bg-indigo-50",
        iconBg: "bg-indigo-100",
        iconHoverBg: "group-hover/action:bg-indigo-200",
        arrowHover: "group-hover/action:text-indigo-400",
    },
    {
        route: "templates.index",
        label: "Browse Templates",
        desc: "Start from a template",
        icon: "🗂️",
        hoverBg: "hover:bg-violet-50",
        iconBg: "bg-violet-100",
        iconHoverBg: "group-hover/action:bg-violet-200",
        arrowHover: "group-hover/action:text-violet-400",
    },
    {
        route: "reports.index",
        label: "All Reports",
        desc: "View & manage all",
        icon: "📋",
        hoverBg: "hover:bg-emerald-50",
        iconBg: "bg-emerald-100",
        iconHoverBg: "group-hover/action:bg-emerald-200",
        arrowHover: "group-hover/action:text-emerald-400",
    },
    {
        route: "profile.edit",
        label: "Profile Settings",
        desc: "Update your account",
        icon: "👤",
        hoverBg: "hover:bg-slate-50",
        iconBg: "bg-slate-100",
        iconHoverBg: "group-hover/action:bg-slate-200",
        arrowHover: "group-hover/action:text-slate-400",
    },
];

const SHORTCUTS = [
    { key: "Ctrl + Z", action: "Undo" },
    { key: "Ctrl + Y", action: "Redo" },
    { key: "Ctrl + S", action: "Save report" },
    { key: "Ctrl + D", action: "Duplicate element" },
    { key: "Delete", action: "Remove element" },
    { key: "Esc", action: "Deselect all" },
    { key: "D", action: "Toggle drag mode" },
    { key: "F", action: "Fullscreen" },
    { key: "↑↓←→", action: "Nudge element 1px" },
    { key: "Shift + ↑↓", action: "Nudge element 10px" },
    { key: "Ctrl + ±", action: "Zoom in / out" },
    { key: "Ctrl + 0", action: "Reset zoom to 100%" },
];

const TIPS = [
    {
        text: "Hold Shift + arrow keys to nudge elements by 10px at once for precise positioning.",
    },
    {
        text: "Press D anywhere to toggle Drag Mode — freely reposition elements on the canvas.",
    },
    {
        text: "Ctrl+Z / Ctrl+Y gives you up to 80 undo / redo states so you can always go back.",
    },
    {
        text: "Drag elements from the sidebar and drop them exactly where you want on the canvas.",
    },
    {
        text: "Press F to enter fullscreen mode for a distraction-free editing experience.",
    },
    {
        text: "Use the Alignment tools in the Properties panel to snap elements to any edge or center.",
    },
];

const ELEMENT_GROUPS = [
    {
        label: "Text & Content",
        items: [
            { icon: "📝", name: "Heading" },
            { icon: "🔤", name: "Subheading" },
            { icon: "¶", name: "Paragraph" },
            { icon: "❝", name: "Quote" },
            { icon: "❞", name: "Block Quote" },
            { icon: "🖊️", name: "Highlight" },
            { icon: "≡", name: "List" },
            { icon: "🔗", name: "Link" },
            { icon: "🏷️", name: "Badge" },
            { icon: "ℹ️", name: "Callout" },
            { icon: "<>", name: "Code" },
            { icon: "★", name: "Icon" },
            { icon: "⭐", name: "Rating" },
        ],
    },
    {
        label: "Charts & Data",
        items: [
            { icon: "📊", name: "Bar Chart" },
            { icon: "📈", name: "Line Chart" },
            { icon: "🥧", name: "Pie Chart" },
            { icon: "○", name: "Doughnut" },
            { icon: "▨", name: "Area Chart" },
            { icon: "✦", name: "Radar" },
            { icon: "▣", name: "KPI Card" },
            { icon: "▬", name: "Progress" },
            { icon: "📋", name: "Table" },
        ],
    },
    {
        label: "Shapes & Layout",
        items: [
            { icon: "🖼️", name: "Image" },
            { icon: "▭", name: "Rectangle" },
            { icon: "●", name: "Circle" },
            { icon: "▲", name: "Triangle" },
            { icon: "—", name: "Line" },
            { icon: "→", name: "Arrow" },
            { icon: "─", name: "Divider" },
            { icon: "□", name: "Spacer" },
        ],
    },
    {
        label: "Report Elements",
        items: [
            { icon: "#", name: "Page No." },
            { icon: "📅", name: "Date" },
            { icon: "✍️", name: "Signature" },
            { icon: "📑", name: "TOC" },
            { icon: "⏱️", name: "Timeline" },
        ],
    },
];

const ELEMENT_TOTAL = ELEMENT_GROUPS.reduce(
    (sum, g) => sum + g.items.length,
    0,
);

// ─── Computed ─────────────────────────────────────────────────────────────────
const timeOfDay = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? "morning" : h < 17 ? "afternoon" : "evening";
});

const todayLabel = computed(() =>
    new Date().toLocaleDateString("en-US", {
        weekday: "long",
        month: "short",
        day: "numeric",
    }),
);

const filteredReports = computed(() => {
    let r = props.recentReports || [];
    if (activeFilter.value !== "all")
        r = r.filter((x) => x.status === activeFilter.value);
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        r = r.filter((x) => x.title?.toLowerCase().includes(q));
    }
    return r;
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
const breakdownPct = (key) => {
    const total = props.stats?.total_reports || 0;
    if (!total) return "0%";
    return Math.round(((props.stats?.[key] || 0) / total) * 100) + "%";
};

const formatDate = (date) => {
    const d = new Date(date),
        now = new Date();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return "just now";
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
    return d.toLocaleDateString("en-US", { month: "short", day: "numeric" });
};

const updateTime = () => {
    currentTime.value = new Date().toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
    });
};

onMounted(() => {
    updateTime();
    clockTimer = setInterval(updateTime, 30000);
});
onBeforeUnmount(() => {
    if (clockTimer) clearInterval(clockTimer);
});
</script>

<style scoped>
@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes rowIn {
    from {
        opacity: 0;
        transform: translateX(-6px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.report-row {
    animation: rowIn 0.25s ease both;
}

/* Smooth search expand */
input {
    transition:
        width 0.2s ease,
        box-shadow 0.2s ease;
}
</style>
