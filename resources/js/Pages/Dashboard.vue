<template>
  <AuthenticatedLayout>
    <template #header >
      <div class="flex items-center justify-between ">
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">
            Good {{ timeOfDay }},
            <span class="text-indigo-600 dark:text-indigo-400">{{ userName }}</span>
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            {{ today }} · {{ stats?.total_reports ?? 0 }} reports total
          </p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Dark mode toggle -->
          <button @click="toggleDark"
            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all"
            :title="isDark ? 'Light mode' : 'Dark mode'">
            <svg v-if="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </button>
          <Link :href="route('templates.index')"
            class="flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl hover:border-indigo-300 dark:hover:border-indigo-600 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
            Templates
          </Link>
          <Link :href="route('reports.create')"
            class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 dark:shadow-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Report
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-full">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- STATS ROW -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div v-for="stat in statsCards" :key="stat.label"
            class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-md dark:hover:shadow-slate-900 transition-all">
            <div class="flex items-center justify-between mb-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="stat.iconBg">
                <component :is="stat.icon" class="w-5 h-5" :class="stat.iconColor"/>
              </div>
              <span v-if="stat.change"
                class="text-xs font-medium px-2 py-0.5 rounded-full"
                :class="stat.changeUp ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400'">
                {{ stat.changeUp ? '↑' : '↓' }} {{ stat.change }}
              </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ stat.label }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- RECENT REPORTS -->
          <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
              <h3 class="font-semibold text-slate-900 dark:text-white">Recent Reports</h3>
              <div class="flex items-center gap-2">
                <div class="relative">
                  <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                  <input v-model="reportSearch" placeholder="Search…" class="pl-7 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 w-40"/>
                </div>
                <select v-model="statusFilter" class="text-xs bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1.5 focus:outline-none">
                  <option value="">All Status</option>
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
                <Link :href="route('reports.index')" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium whitespace-nowrap">View all →</Link>
              </div>
            </div>

            <div v-if="filteredReports.length">
              <div v-for="report in filteredReports" :key="report.id"
                class="flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-750 border-b border-slate-50 dark:border-slate-700/50 last:border-0 transition-colors group">
                <!-- Icon -->
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                  :class="statusStyle(report.status).iconBg">
                  <svg class="w-4 h-4" :class="statusStyle(report.status).iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-slate-900 dark:text-white text-sm truncate">{{ report.title }}</div>
                  <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-2">
                    <span>{{ formatDate(report.updated_at) }}</span>
                    <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600 inline-block"></span>
                    <span class="capitalize px-1.5 py-0.5 rounded text-[10px] font-semibold"
                      :class="statusStyle(report.status).badge">{{ report.status }}</span>
                    <span v-if="report.template" class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600 inline-block"></span>
                    <span v-if="report.template" class="text-slate-400">{{ report.template.name }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <Link :href="route('reports.edit', report.slug)"
                    class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors" title="Edit">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </Link>
                  <Link :href="route('reports.preview', report.slug)" target="_blank"
                    class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors" title="Preview">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </Link>
                  <a :href="route('reports.download', report.slug)"
                    class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors" title="Download PDF">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </a>
                </div>
              </div>
            </div>
            <div v-else class="px-6 py-16 text-center">
              <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <p class="font-medium text-slate-500 dark:text-slate-400 mb-1">No reports found</p>
              <p class="text-sm text-slate-400 dark:text-slate-500 mb-4">Create your first report to get started.</p>
              <Link :href="route('reports.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Report
              </Link>
            </div>
          </div>

          <!-- RIGHT COLUMN -->
          <div class="space-y-5">

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
              <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Quick Actions</h3>
              </div>
              <div class="p-2 space-y-0.5">
                <Link v-for="action in quickActions" :key="action.label" :href="action.href"
                  class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors group">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="action.iconBg">
                    <component :is="action.icon" class="w-4 h-4" :class="action.iconColor"/>
                  </div>
                  <span class="text-sm text-slate-700 dark:text-slate-300 font-medium flex-1">{{ action.label }}</span>
                  <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover:text-slate-500 dark:group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
              </div>
            </div>

            <!-- Storage / Usage -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
              <h3 class="font-semibold text-slate-900 dark:text-white text-sm mb-4">Usage This Month</h3>
              <div class="space-y-3">
                <div v-for="u in usageMetrics" :key="u.label">
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-slate-600 dark:text-slate-400">{{ u.label }}</span>
                    <span class="font-medium text-slate-900 dark:text-slate-200">{{ u.value }}</span>
                  </div>
                  <div class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700" :class="u.color" :style="{ width: u.pct + '%' }"/>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pro tip -->
            <div class="bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl p-5 text-white">
              <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              </div>
              <h4 class="font-semibold text-sm mb-1.5">Keyboard Shortcuts</h4>
              <div class="space-y-1 text-xs text-indigo-200">
                <div class="flex justify-between"><span>Undo / Redo</span><span class="font-mono">Ctrl+Z / Y</span></div>
                <div class="flex justify-between"><span>Save</span><span class="font-mono">Ctrl+S</span></div>
                <div class="flex justify-between"><span>Duplicate el.</span><span class="font-mono">Ctrl+D</span></div>
                <div class="flex justify-between"><span>Delete el.</span><span class="font-mono">Delete</span></div>
                <div class="flex justify-between"><span>Move (fine)</span><span class="font-mono">Arrow keys</span></div>
                <div class="flex justify-between"><span>Move (coarse)</span><span class="font-mono">Shift+Arrow</span></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  recentReports: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ total_reports:0, published_reports:0, draft_reports:0, archived_reports:0 }) }
});

const page       = usePage();
const isDark     = ref(false);
const reportSearch = ref('');
const statusFilter = ref('');
const today      = new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
const userName   = computed(() => page.props.auth?.user?.name?.split(' ')[0] ?? 'there');
const timeOfDay  = computed(() => { const h = new Date().getHours(); return h < 12 ? 'morning' : h < 17 ? 'afternoon' : 'evening'; });

const toggleDark = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

onMounted(() => {
  const saved = localStorage.getItem('theme');
  if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  }
});

// Icon components
const DocIcon = { template:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>` };
const CheckIcon = { template:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>` };
const PenIcon = { template:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>` };
const ArchiveIcon = { template:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>` };
const PlusIcon = { template:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>` };
const TemplateIcon = { template:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>` };
const ListIcon = { template:`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>` };

const statsCards = computed(() => [
  { label:'Total Reports',  value: props.stats?.total_reports ?? 0,     icon: DocIcon,     iconBg:'bg-indigo-100 dark:bg-indigo-900/40',  iconColor:'text-indigo-600 dark:text-indigo-400',  change:'12%', changeUp:true },
  { label:'Published',      value: props.stats?.published_reports ?? 0, icon: CheckIcon,   iconBg:'bg-emerald-100 dark:bg-emerald-900/40', iconColor:'text-emerald-600 dark:text-emerald-400',change:'5%',  changeUp:true },
  { label:'Drafts',         value: props.stats?.draft_reports ?? 0,     icon: PenIcon,     iconBg:'bg-amber-100 dark:bg-amber-900/40',    iconColor:'text-amber-600 dark:text-amber-400',    change:null },
  { label:'Archived',       value: props.stats?.archived_reports ?? 0,  icon: ArchiveIcon, iconBg:'bg-slate-100 dark:bg-slate-700',       iconColor:'text-slate-500 dark:text-slate-400',    change:null },
]);

const quickActions = [
  { label:'New Report',       href: route('reports.create'),    icon: PlusIcon,     iconBg:'bg-indigo-100 dark:bg-indigo-900/40',  iconColor:'text-indigo-600 dark:text-indigo-400' },
  { label:'Browse Templates', href: route('templates.index'),   icon: TemplateIcon, iconBg:'bg-purple-100 dark:bg-purple-900/40',  iconColor:'text-purple-600 dark:text-purple-400' },
  { label:'All Reports',      href: route('reports.index'),     icon: ListIcon,     iconBg:'bg-emerald-100 dark:bg-emerald-900/40',iconColor:'text-emerald-600 dark:text-emerald-400' },
];

const usageMetrics = computed(() => {
  const t = props.stats?.total_reports ?? 0;
  return [
    { label:'Reports Created', value:`${t} / 50`, pct:Math.min((t/50)*100,100), color:'bg-indigo-500' },
    { label:'PDFs Exported',   value:`${props.stats?.published_reports ?? 0} / 20`, pct:Math.min(((props.stats?.published_reports??0)/20)*100,100), color:'bg-emerald-500' },
    { label:'Storage Used',    value:'2.4 MB / 100 MB', pct:2.4, color:'bg-amber-500' },
  ];
});

const filteredReports = computed(() => {
  let r = props.recentReports ?? [];
  if (reportSearch.value) r = r.filter(x => x.title.toLowerCase().includes(reportSearch.value.toLowerCase()));
  if (statusFilter.value) r = r.filter(x => x.status === statusFilter.value);
  return r;
});

const statusStyle = (s) => ({
  draft:     { iconBg:'bg-amber-100 dark:bg-amber-900/30',  iconColor:'text-amber-600 dark:text-amber-400',   badge:'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400' },
  published: { iconBg:'bg-emerald-100 dark:bg-emerald-900/30', iconColor:'text-emerald-600 dark:text-emerald-400', badge:'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' },
  archived:  { iconBg:'bg-slate-100 dark:bg-slate-700',    iconColor:'text-slate-500 dark:text-slate-400',   badge:'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400' },
}[s] ?? { iconBg:'bg-slate-100', iconColor:'text-slate-500', badge:'bg-slate-100 text-slate-600' });

const formatDate = (d) => {
  const diff = Math.floor((Date.now() - new Date(d)) / 1000);
  if (diff < 60)    return 'just now';
  if (diff < 3600)  return `${Math.floor(diff/60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
  if (diff < 604800)return `${Math.floor(diff/86400)}d ago`;
  return new Date(d).toLocaleDateString('en-US', { month:'short', day:'numeric' });
};
</script>