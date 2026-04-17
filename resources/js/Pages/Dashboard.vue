<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                        Good {{ timeOfDay }}, {{ $page.props.auth.user.name.split(' ')[0] }} 👋
                    </h2>
                    <p class="text-sm text-slate-400 mt-0.5">Here's your reporting overview for today.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                        Filter
                    </button>
                    <Link :href="route('reports.create')"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Report
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-7">

                <!-- ── STAT CARDS ── -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <!-- Total Reports -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full">All time</span>
                        </div>
                        <div class="text-3xl font-black text-slate-800 mb-1">{{ stats?.total_reports || 0 }}</div>
                        <div class="text-sm text-slate-400 font-medium">Total Reports</div>
                        <div class="mt-3 h-1 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: 65%"></div>
                        </div>
                    </div>

                    <!-- Published -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full">Live</span>
                        </div>
                        <div class="text-3xl font-black text-slate-800 mb-1">{{ stats?.published_reports || 0 }}</div>
                        <div class="text-sm text-slate-400 font-medium">Published</div>
                        <div class="mt-3 h-1 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>

                    <!-- Drafts -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <span class="text-xs text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-full">In progress</span>
                        </div>
                        <div class="text-3xl font-black text-slate-800 mb-1">{{ stats?.draft_reports || 0 }}</div>
                        <div class="text-sm text-slate-400 font-medium">Drafts</div>
                        <div class="mt-3 h-1 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full" style="width: 30%"></div>
                        </div>
                    </div>

                    <!-- Templates -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center group-hover:bg-violet-100 transition-colors">
                                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            </div>
                            <span class="text-xs text-violet-600 font-semibold bg-violet-50 px-2 py-0.5 rounded-full">Available</span>
                        </div>
                        <div class="text-3xl font-black text-slate-800 mb-1">{{ stats?.total_templates || 12 }}</div>
                        <div class="text-sm text-slate-400 font-medium">Templates</div>
                        <div class="mt-3 h-1 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-violet-500 rounded-full" style="width: 80%"></div>
                        </div>
                    </div>
                </div>

                <!-- ── MAIN GRID ── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Recent Reports -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-slate-800">Recent Reports</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Your latest report activity</p>
                            </div>
                            <Link :href="route('reports.index')" class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold flex items-center gap-1">
                                View all
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </Link>
                        </div>

                        <div v-if="recentReports && recentReports.length > 0">
                            <div v-for="report in recentReports" :key="report.id"
                                class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 border-b border-slate-50 last:border-0 transition-colors group">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-base"
                                    :style="{ backgroundColor: statusColors[report.status]?.bg }">
                                    <svg class="w-5 h-5" :style="{ color: statusColors[report.status]?.icon }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-800 text-sm truncate">{{ report.title }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                                        <span>{{ formatDate(report.updated_at) }}</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                        <span class="capitalize px-1.5 py-0.5 rounded-md text-xs font-semibold"
                                            :style="{ backgroundColor: statusColors[report.status]?.bg, color: statusColors[report.status]?.text }">
                                            {{ report.status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Link :href="route('reports.edit', report.slug)"
                                        class="p-2 hover:bg-indigo-50 rounded-lg text-slate-400 hover:text-indigo-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </Link>
                                    <Link :href="route('reports.preview', report.slug)" target="_blank"
                                        class="p-2 hover:bg-emerald-50 rounded-lg text-slate-400 hover:text-emerald-600 transition-colors" title="Preview">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </Link>
                                    <a :href="route('reports.download', report.slug)" target="_blank"
                                        class="p-2 hover:bg-rose-50 rounded-lg text-slate-400 hover:text-rose-600 transition-colors" title="Download PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="px-6 py-16 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-slate-500 font-semibold mb-1">No reports yet</p>
                            <p class="text-slate-400 text-sm mb-5">Create your first report to get started.</p>
                            <Link :href="route('reports.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Create Report
                            </Link>
                        </div>
                    </div>

                    <!-- Right column -->
                    <div class="space-y-5">

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100">
                                <h3 class="font-bold text-slate-800 text-sm">Quick Actions</h3>
                            </div>
                            <div class="p-3 space-y-1">
                                <Link :href="route('reports.create')"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-indigo-50 transition-colors group">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm text-slate-700 font-semibold block">New Report</span>
                                        <span class="text-xs text-slate-400">Start from scratch</span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </Link>
                                <Link :href="route('templates.index')"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-violet-50 transition-colors group">
                                    <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition-colors">
                                        <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm text-slate-700 font-semibold block">Browse Templates</span>
                                        <span class="text-xs text-slate-400">Start from a template</span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </Link>
                                <Link :href="route('reports.index')"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-emerald-50 transition-colors group">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm text-slate-700 font-semibold block">All Reports</span>
                                        <span class="text-xs text-slate-400">View & manage</span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </Link>
                                <Link :href="route('profile.edit')"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors group">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm text-slate-700 font-semibold block">Profile Settings</span>
                                        <span class="text-xs text-slate-400">Update your info</span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Keyboard shortcuts card -->
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100">
                                <h3 class="font-bold text-slate-800 text-sm">Editor Shortcuts</h3>
                            </div>
                            <div class="p-4 space-y-2">
                                <div v-for="sc in shortcuts" :key="sc.key" class="flex items-center justify-between">
                                    <span class="text-xs text-slate-500">{{ sc.action }}</span>
                                    <kbd class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-lg border border-slate-200 font-mono">{{ sc.key }}</kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Gradient tip card -->
                        <div class="rounded-2xl p-5 text-white overflow-hidden relative"
                            style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full opacity-20"
                                style="background: radial-gradient(circle, white, transparent)"></div>
                            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center mb-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h4 class="font-bold text-sm mb-1">Pro Tip</h4>
                            <p class="text-xs text-indigo-200 leading-relaxed">Hold <strong class="text-white">Shift</strong> while pressing arrow keys to nudge elements by 10px at a time for precise positioning.</p>
                        </div>
                    </div>
                </div>

                <!-- ── ACTIVITY / USAGE ROW ── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Report status breakdown -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <h3 class="font-bold text-slate-800 text-sm mb-4">Report Breakdown</h3>
                        <div class="space-y-3">
                            <div v-for="item in [
                                { label: 'Published', key: 'published_reports', color: 'bg-emerald-500', textColor: 'text-emerald-600' },
                                { label: 'Draft', key: 'draft_reports', color: 'bg-amber-400', textColor: 'text-amber-600' },
                                { label: 'Archived', key: 'archived_reports', color: 'bg-slate-300', textColor: 'text-slate-500' },
                            ]" :key="item.key">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium text-slate-600">{{ item.label }}</span>
                                    <span class="text-xs font-bold" :class="item.textColor">{{ stats?.[item.key] || 0 }}</span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700" :class="item.color"
                                        :style="`width: ${stats?.total_reports ? Math.round((stats[item.key] || 0) / stats.total_reports * 100) : 0}%`"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400">Total</span>
                            <span class="text-sm font-black text-slate-800">{{ stats?.total_reports || 0 }} reports</span>
                        </div>
                    </div>

                    <!-- Element types usage hint -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-slate-800 text-sm">Available Elements</h3>
                            <span class="text-xs text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full font-semibold">25+ types</span>
                        </div>
                        <div class="grid grid-cols-5 sm:grid-cols-7 gap-2">
                            <div v-for="el in elementGuide" :key="el.name"
                                class="flex flex-col items-center gap-1.5 p-2 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50 transition-all cursor-default group">
                                <span class="text-lg">{{ el.icon }}</span>
                                <span class="text-[9px] text-slate-400 group-hover:text-indigo-600 font-medium text-center leading-tight transition-colors">{{ el.name }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-4">Drag any element from the sidebar in the Report Editor onto your canvas.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    recentReports: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total_reports: 0, published_reports: 0, draft_reports: 0, archived_reports: 0, total_templates: 0 }) },
});

const statusColors = {
    published: { bg: '#dcfce7', text: '#15803d', icon: '#16a34a' },
    draft:     { bg: '#fef9c3', text: '#a16207', icon: '#ca8a04' },
    archived:  { bg: '#f1f5f9', text: '#64748b', icon: '#94a3b8' },
};

const timeOfDay = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'morning';
    if (h < 17) return 'afternoon';
    return 'evening';
});

const formatDate = (date) => {
    const d = new Date(date);
    const now = new Date();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const shortcuts = [
    { key: 'Ctrl + Z', action: 'Undo' },
    { key: 'Ctrl + Y', action: 'Redo' },
    { key: 'Ctrl + S', action: 'Save report' },
    { key: 'Delete', action: 'Delete element' },
    { key: 'Esc', action: 'Deselect' },
    { key: '↑↓←→', action: 'Nudge element' },
    { key: 'Shift + ↑↓', action: 'Nudge ×10' },
];

const elementGuide = [
    { icon: '📝', name: 'Heading' },
    { icon: '🔤', name: 'Text' },
    { icon: '❝', name: 'Quote' },
    { icon: '≡', name: 'List' },
    { icon: '🔗', name: 'Link' },
    { icon: '🏷️', name: 'Badge' },
    { icon: '💻', name: 'Code' },
    { icon: '📊', name: 'Bar Chart' },
    { icon: '📈', name: 'Line Chart' },
    { icon: '🥧', name: 'Pie Chart' },
    { icon: '○', name: 'Doughnut' },
    { icon: '▨', name: 'Area' },
    { icon: '✦', name: 'Radar' },
    { icon: '▣', name: 'KPI Card' },
    { icon: '▬', name: 'Progress' },
    { icon: '📋', name: 'Table' },
    { icon: '🖼', name: 'Image' },
    { icon: '▮', name: 'Rectangle' },
    { icon: '●', name: 'Circle' },
    { icon: '▲', name: 'Triangle' },
    { icon: '—', name: 'Line' },
    { icon: '─', name: 'Divider' },
    { icon: '□', name: 'Spacer' },
    { icon: '#', name: 'Page Num' },
    { icon: '📅', name: 'Date' },
];
</script>