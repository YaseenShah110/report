<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between ">
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Template Gallery</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Choose a template to start building your report</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="toggleDark" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all">
            <svg v-if="!isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          </button>
          <!-- View toggle -->
          <div class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
            <button v-for="v in ['grid','list']" :key="v" @click="viewMode=v"
              :class="viewMode===v ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'"
              class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all capitalize">{{ v }}</button>
          </div>
        </div>
      </div>
    </template>

    <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-full">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Search + Filter bar -->
        <div class="flex flex-wrap items-center gap-3 mb-8">
          <div class="relative flex-1 max-w-xs">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input v-model="search" placeholder="Search templates…"
              class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <button v-for="cat in categories" :key="cat"
              @click="activeCategory = cat"
              :class="activeCategory===cat ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600'"
              class="px-4 py-1.5 rounded-full text-sm font-medium transition-all">{{ cat }}</button>
          </div>
        </div>

        <!-- Grid view -->
        <div v-if="viewMode==='grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <div v-for="tpl in filteredTemplates" :key="tpl.id"
            class="group relative bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-xl dark:hover:shadow-slate-900 transition-all duration-300 cursor-pointer"
            @click="openModal(tpl)">

            <!-- Cover preview based on template settings -->
            <div class="relative aspect-[3/4] overflow-hidden" :style="{ background: getCoverGradient(tpl) }">
              <div class="absolute inset-0" v-html="getCoverHtml(tpl)"/>
              <!-- Hover overlay -->
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                <span class="bg-white/95 backdrop-blur text-slate-800 font-semibold text-sm px-4 py-2 rounded-full shadow-lg transform scale-90 group-hover:scale-100 transition-transform">
                  Use Template
                </span>
              </div>
              <!-- Badge -->
              <div v-if="tpl.badge" class="absolute top-3 right-3">
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-400 text-amber-900">
                  {{ tpl.badge }}
                </span>
              </div>
            </div>

            <!-- Info -->
            <div class="p-4">
              <h3 class="font-semibold text-slate-900 dark:text-white text-sm">{{ tpl.name }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ tpl.description }}</p>
              <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
                <span v-for="tag in (tpl.tags || [])" :key="tag"
                  class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded text-[10px] font-medium">{{ tag }}</span>
              </div>
              <button @click.stop="openModal(tpl)"
                class="mt-3 w-full py-1.5 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors">
                Use Template
              </button>
            </div>
          </div>

          <!-- Blank card -->
          <div @click="openModal(null)"
            class="group bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all cursor-pointer flex flex-col items-center justify-center min-h-[340px] gap-3">
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 flex items-center justify-center transition-colors">
              <svg class="w-6 h-6 text-slate-400 dark:text-slate-500 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div class="text-center">
              <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm">Blank Canvas</p>
              <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Start from scratch</p>
            </div>
          </div>
        </div>

        <!-- List view -->
        <div v-else class="space-y-3">
          <div v-for="tpl in filteredTemplates" :key="tpl.id"
            class="flex items-center gap-5 bg-white dark:bg-slate-800 rounded-xl px-5 py-4 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md transition-all cursor-pointer"
            @click="openModal(tpl)">
            <div class="w-16 h-20 rounded-xl overflow-hidden shrink-0" :style="{ background: getCoverGradient(tpl) }">
              <div class="w-full h-full" v-html="getCoverHtml(tpl)"/>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-slate-900 dark:text-white">{{ tpl.name }}</h3>
                <span v-if="tpl.badge" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400">
                  {{ tpl.badge }}
                </span>
              </div>
              <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ tpl.description }}</p>
              <div class="flex gap-1.5 mt-2">
                <span v-for="tag in (tpl.tags || [])" :key="tag"
                  class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded text-xs">{{ tag }}</span>
              </div>
            </div>
            <button @click.stop="openModal(tpl)" class="px-5 py-2 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shrink-0">
              Use
            </button>
          </div>
          <div @click="openModal(null)" class="flex items-center gap-5 bg-white dark:bg-slate-800 rounded-xl px-5 py-4 border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-400 transition-all cursor-pointer">
            <div class="w-16 h-20 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
              <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white">Blank Canvas</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Start from scratch with no template</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- CREATE REPORT MODAL -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal=false"/>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">

          <!-- Modal header -->
          <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white">Create New Report</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                {{ selectedTpl ? `Using: ${selectedTpl.name}` : 'Blank canvas' }}
              </p>
            </div>
            <button @click="showModal=false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Modal body -->
          <div class="px-6 py-5 space-y-5">

            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Report Title <span class="text-red-500">*</span></label>
              <input v-model="reportTitle" type="text" ref="titleInput"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                placeholder="e.g. Q4 2024 Annual Report…"
                @keydown.enter="createReport"/>
            </div>

            <!-- Quick settings - populated from template settings if available -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Page Size</label>
                <select v-model="reportSettings.page_size" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <option value="A4">A4</option>
                  <option value="Letter">Letter (US)</option>
                  <option value="Legal">Legal</option>
                  <option value="A3">A3</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Orientation</label>
                <select v-model="reportSettings.orientation" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <option value="portrait">Portrait</option>
                  <option value="landscape">Landscape</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Primary Color</label>
                <div class="flex gap-2">
                  <input type="color" v-model="reportSettings.primary_color" class="w-10 h-[38px] rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer p-0.5 bg-white dark:bg-slate-700"/>
                  <input type="text" v-model="reportSettings.primary_color" class="flex-1 px-3 py-2 text-xs font-mono border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Font Family</label>
                <select v-model="reportSettings.font_family" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <option value="'DM Sans', sans-serif">DM Sans</option>
                  <option value="'Inter', sans-serif">Inter</option>
                  <option value="Georgia, serif">Georgia</option>
                  <option value="'Playfair Display', serif">Playfair Display</option>
                  <option value="'Courier New', monospace">Courier New</option>
                </select>
              </div>
            </div>

            <!-- Extra settings accordion -->
            <div>
              <button @click="showExtra=!showExtra" class="flex items-center gap-2 text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-700 transition-colors">
                <svg class="w-4 h-4 transition-transform" :class="showExtra ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                Advanced Settings
              </button>
              <div v-show="showExtra" class="mt-3 grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Background Color</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="reportSettings.background_color" class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer p-0.5 bg-white dark:bg-slate-700"/>
                    <input type="text" v-model="reportSettings.background_color" class="flex-1 px-2 py-1.5 text-xs font-mono border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-400"/>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Margin (px)</label>
                  <input type="number" v-model.number="reportSettings.margin" class="w-full px-2 py-1.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-400" min="0" max="120"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Footer Left</label>
                  <input type="text" v-model="reportSettings.footer_left" placeholder="Company Name" class="w-full px-2 py-1.5 text-xs border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Footer Right</label>
                  <input type="text" v-model="reportSettings.footer_right" placeholder="Confidential" class="w-full px-2 py-1.5 text-xs border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-400"/>
                </div>
                <div class="col-span-2 flex items-center justify-between">
                  <label class="text-xs font-medium text-slate-600 dark:text-slate-400">Show Page Numbers</label>
                  <button @click="reportSettings.show_page_numbers=!reportSettings.show_page_numbers"
                    :class="reportSettings.show_page_numbers ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-600'"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors">
                    <span :class="reportSettings.show_page_numbers ? 'translate-x-5' : 'translate-x-1'" class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"/>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal footer -->
          <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex gap-3">
            <button @click="showModal=false" class="flex-1 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
              Cancel
            </button>
            <button @click="createReport" :disabled="!reportTitle.trim() || isCreating"
              class="flex-2 flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
              <svg v-if="isCreating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".75"/></svg>
              {{ isCreating ? 'Creating…' : 'Create Report' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ 
  templates: { type: Array, default: () => [] }
});

const viewMode   = ref('grid');
const search     = ref('');
const activeCategory = ref('All');
const showModal  = ref(false);
const selectedTpl= ref(null);
const reportTitle= ref('');
const showExtra  = ref(false);
const isCreating = ref(false);
const isDark     = ref(false);
const titleInput = ref(null);

const reportSettings = ref({
  page_size: 'A4', 
  orientation: 'portrait',
  primary_color: '#6366f1', 
  background_color: '#ffffff',
  font_family: "'DM Sans', sans-serif", 
  margin: 40,
  show_page_numbers: true, 
  footer_left: '', 
  footer_right: ''
});

// Extract unique categories from templates
const categories = computed(() => {
  const cats = new Set(['All']);
  props.templates.forEach(tpl => {
    if (tpl.category) cats.add(tpl.category);
    if (tpl.tags) tpl.tags.forEach(tag => cats.add(tag));
  });
  return Array.from(cats);
});

const filteredTemplates = computed(() => {
  let t = props.templates;
  if (search.value) {
    t = t.filter(x => 
      x.name.toLowerCase().includes(search.value.toLowerCase()) || 
      (x.description || '').toLowerCase().includes(search.value.toLowerCase())
    );
  }
  if (activeCategory.value !== 'All') {
    t = t.filter(x => 
      x.category === activeCategory.value || 
      (x.tags || []).includes(activeCategory.value)
    );
  }
  return t;
});

// Helper functions to get cover properties from template data
const getCoverGradient = (tpl) => {
  // Use settings.primary_color to generate gradient
  const primaryColor = tpl.settings?.primary_color || '#6366f1';
  const bgColor = tpl.settings?.background_color || '#ffffff';
  
  if (tpl.slug === 'executive-dark') {
    return 'linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%)';
  } else if (tpl.slug === 'modern-minimal') {
    return 'linear-gradient(135deg,#f8fafc 0%,#e0f2f1 100%)';
  } else if (tpl.slug === 'bold-analytics') {
    return 'linear-gradient(135deg,#1e293b 0%,#0f172a 100%)';
  } else if (tpl.slug === 'clean-professional') {
    return 'linear-gradient(135deg,#ffffff 0%,#f0f4ff 100%)';
  }
  return `linear-gradient(135deg, ${primaryColor} 0%, ${bgColor} 100%)`;
};

const getCoverHtml = (tpl) => {
  const covers = {
    'executive-dark': `<div class="absolute inset-0 flex flex-col justify-end p-5"><div class="w-6 h-1 rounded bg-indigo-500 mb-3"></div><h3 class="text-2xl font-black text-white mb-1 leading-tight">Annual<br>Report</h3><p class="text-xs text-slate-400">Executive Summary</p><div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-500"></div></div>`,
    'modern-minimal': `<div class="absolute inset-0 bg-white p-5 flex flex-col"><div class="w-4 h-4 rounded-full bg-emerald-500 mb-6"></div><div class="w-8 h-0.5 bg-slate-900 mb-3"></div><h3 class="text-2xl font-light text-slate-900 mb-1">Report<br>2024</h3><p class="text-xs text-slate-400 mt-auto">Minimal Edition</p></div>`,
    'bold-analytics': `<div class="absolute inset-0 p-5 flex flex-col justify-between"><p class="text-xs font-bold text-amber-400 tracking-widest">ANALYTICS</p><div><h3 class="text-3xl font-black text-white leading-none">DATA<br><span class="text-amber-400">REPORT</span></h3><div class="flex gap-1 mt-3"><div style="height:0.625rem" class="w-3 bg-amber-400 rounded-sm opacity-60 self-end"></div><div style="height:1rem" class="w-3 bg-amber-400 rounded-sm opacity-60 self-end"></div><div style="height:0.75rem" class="w-3 bg-amber-400 rounded-sm opacity-60 self-end"></div><div style="height:1.25rem" class="w-3 bg-amber-400 rounded-sm opacity-60 self-end"></div><div style="height:0.9375rem" class="w-3 bg-amber-400 rounded-sm opacity-60 self-end"></div></div></div></div>`,
    'clean-professional': `<div class="absolute inset-0 flex"><div class="w-16 bg-indigo-600 flex flex-col p-3 gap-4"><div class="w-8 h-8 rounded bg-white/20"></div><div class="mt-auto space-y-2"><div class="h-1 w-full rounded bg-white/40"></div><div class="h-1 w-3/4 rounded bg-white/30"></div><div class="h-1 w-1/2 rounded bg-white/20"></div></div></div><div class="flex-1 bg-white p-4 flex flex-col justify-end"><div class="bg-indigo-600 rounded-lg p-3 mb-2"><p class="text-white text-xs font-bold">Business Report</p><p class="text-indigo-200 text-[10px]">Q4 2024</p></div></div></div>`,
  };
  return covers[tpl.slug] || `<div class="absolute inset-0 flex items-center justify-center"><p class="text-white font-bold text-lg opacity-60">${tpl.name}</p></div>`;
};

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

const openModal = (tpl) => {
  selectedTpl.value = tpl;
  reportTitle.value = '';
  showExtra.value = false;
  isCreating.value = false;
  
  // Pre-fill settings from template if available
  if (tpl?.settings) {
    reportSettings.value = { 
      ...reportSettings.value, 
      ...tpl.settings,
      // Ensure we have all required fields
      page_size: tpl.settings.page_size || 'A4',
      orientation: tpl.settings.orientation || 'portrait',
      primary_color: tpl.settings.primary_color || '#6366f1',
      background_color: tpl.settings.background_color || '#ffffff',
      font_family: tpl.settings.font_family || "'DM Sans', sans-serif",
      margin: tpl.settings.margin || 40,
      show_page_numbers: tpl.settings.show_page_numbers ?? true,
      footer_left: tpl.settings.footer_left || '',
      footer_right: tpl.settings.footer_right || ''
    };
  } else {
    // Reset to defaults for blank canvas
    reportSettings.value = {
      page_size: 'A4',
      orientation: 'portrait',
      primary_color: '#6366f1',
      background_color: '#ffffff',
      font_family: "'DM Sans', sans-serif",
      margin: 40,
      show_page_numbers: true,
      footer_left: '',
      footer_right: ''
    };
  }
  
  showModal.value = true;
  nextTick(() => titleInput.value?.focus());
};

const createReport = () => {
  if (!reportTitle.value.trim() || isCreating.value) return;
  isCreating.value = true;
  
  router.post(route('reports.store'), {
    title: reportTitle.value.trim(),
    template_id: selectedTpl.value?.id || null,
    template_slug: selectedTpl.value?.slug || null,
    initial_settings: reportSettings.value,
    template_structure: selectedTpl.value?.structure || null,
  }, {
    onSuccess: () => {
      isCreating.value = false;
      showModal.value = false;
    },
    onError: () => { 
      isCreating.value = false; 
    }
  });
};
</script>