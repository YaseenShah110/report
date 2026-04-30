<!-- resources/js/Pages/Templates/Index.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Template Gallery</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Choose a template to start building your report</p>
        </div>
        <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
          <button v-for="v in ['grid', 'list']" :key="v" @click="viewMode = v"
            :class="viewMode === v ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500'"
            class="p-2 rounded-lg transition-all">
            <i :class="v === 'grid' ? 'fa-solid fa-grip' : 'fa-solid fa-list'" class="text-sm"></i>
          </button>
        </div>
      </div>
    </template>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Search & Filter -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
          <div class="flex-1 min-w-[200px] relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input v-model="search" type="text" placeholder="Search templates..." 
              class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
          </div>
          <div class="flex gap-2">
            <button v-for="cat in categories" :key="cat" @click="selectedCategory = cat"
              :class="selectedCategory === cat ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300'"
              class="px-3 py-1.5 text-xs font-medium rounded-full capitalize transition-all">
              {{ cat }}
            </button>
          </div>
        </div>

        <!-- Templates Grid -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <div v-for="tpl in filteredTemplates" :key="tpl.id" 
            class="group bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-xl transition-all duration-300 cursor-pointer"
            @click="openModal(tpl)">
            
            <div class="relative h-48 overflow-hidden" :style="{ background: tpl.cover_gradient || 'linear-gradient(135deg, #6366f1, #8b5cf6)' }">
              <div class="absolute inset-0 flex items-center justify-center opacity-20 group-hover:opacity-100 transition-opacity">
                <i class="fa-solid fa-file-lines text-white text-5xl"></i>
              </div>
              <div v-if="tpl.badge" class="absolute top-3 right-3">
                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-400 text-amber-900">{{ tpl.badge }}</span>
              </div>
              <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="px-4 py-2 bg-white text-slate-900 rounded-lg text-sm font-semibold transform scale-90 group-hover:scale-100 transition-transform">
                  Use Template
                </span>
              </div>
            </div>

            <div class="p-4">
              <h3 class="font-semibold text-slate-900 dark:text-white">{{ tpl.name }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ tpl.description }}</p>
              <div class="flex flex-wrap gap-1 mt-2">
                <span v-for="tag in tpl.tags?.slice(0,3)" :key="tag" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded text-[10px]">{{ tag }}</span>
              </div>
              <button class="mt-3 w-full py-2 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors">
                Use Template
              </button>
            </div>
          </div>

          <!-- Blank Template Card -->
          <div @click="openModal(null)" 
            class="group bg-white dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-400 transition-all cursor-pointer flex flex-col items-center justify-center min-h-[280px] gap-3">
            <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 flex items-center justify-center transition-all">
              <i class="fa-solid fa-plus text-slate-400 group-hover:text-indigo-500 text-2xl"></i>
            </div>
            <div class="text-center">
              <p class="font-semibold text-slate-700 dark:text-slate-300">Blank Canvas</p>
              <p class="text-xs text-slate-400 mt-1">Start from scratch</p>
            </div>
          </div>
        </div>

        <!-- List View -->
        <div v-else class="space-y-3">
          <div v-for="tpl in filteredTemplates" :key="tpl.id" 
            class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 transition-all cursor-pointer flex items-center gap-4"
            @click="openModal(tpl)">
            <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0" :style="{ background: tpl.cover_gradient }">
              <div class="w-full h-full flex items-center justify-center">
                <i class="fa-solid fa-file-lines text-white text-2xl opacity-50"></i>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-slate-900 dark:text-white">{{ tpl.name }}</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">{{ tpl.description }}</p>
              <div class="flex gap-1 mt-1">
                <span v-for="tag in tpl.tags?.slice(0,2)" :key="tag" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 rounded text-[10px]">{{ tag }}</span>
              </div>
            </div>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold">Use</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Report Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 px-6 py-4 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white">Create New Report</h3>
              <p class="text-sm text-slate-500">{{ selectedTemplate ? `Using: ${selectedTemplate.name}` : 'Blank canvas' }}</p>
            </div>
            <button @click="showModal = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
              <i class="fa-solid fa-xmark text-xl text-slate-400"></i>
            </button>
          </div>

          <div class="p-6 space-y-5">
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Report Title <span class="text-red-500">*</span></label>
              <input v-model="form.title" type="text" ref="titleInput" required
                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                placeholder="e.g., Q4 2024 Annual Report">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Page Size</label>
                <select v-model="form.settings.page_size" class="w-full px-3 py-2 text-sm border rounded-lg">
                  <option value="A4">A4</option><option value="Letter">Letter</option><option value="Legal">Legal</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Orientation</label>
                <select v-model="form.settings.orientation" class="w-full px-3 py-2 text-sm border rounded-lg">
                  <option value="portrait">Portrait</option><option value="landscape">Landscape</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Primary Color</label>
                <div class="flex gap-2">
                  <input type="color" v-model="form.settings.primary_color" class="w-10 h-9 rounded-lg border p-1">
                  <input type="text" v-model="form.settings.primary_color" class="flex-1 px-2 py-1.5 text-xs font-mono border rounded-lg">
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Font Family</label>
                <select v-model="form.settings.font_family" class="w-full px-3 py-2 text-sm border rounded-lg">
                  <option value="'DM Sans', sans-serif">DM Sans</option>
                  <option value="'Inter', sans-serif">Inter</option>
                  <option value="Georgia, serif">Georgia</option>
                </select>
              </div>
            </div>

            <button @click="toggleAdvanced" class="text-sm text-indigo-600 flex items-center gap-1">
              <i :class="showAdvanced ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'"></i>
              Advanced Settings
            </button>

            <div v-show="showAdvanced" class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-200 dark:border-slate-700">
              <div><label class="block text-xs font-semibold mb-1">Background</label><input type="color" v-model="form.settings.background_color" class="w-full h-9 rounded border"></div>
              <div><label class="block text-xs font-semibold mb-1">Margin (px)</label><input type="number" v-model="form.settings.margin" class="w-full px-3 py-2 text-sm border rounded-lg"></div>
              <div><label class="block text-xs font-semibold mb-1">Footer Left</label><input v-model="form.settings.footer_left" class="w-full px-3 py-2 text-sm border rounded-lg"></div>
              <div><label class="block text-xs font-semibold mb-1">Footer Right</label><input v-model="form.settings.footer_right" class="w-full px-3 py-2 text-sm border rounded-lg"></div>
            </div>
          </div>

          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex gap-3">
            <button @click="showModal = false" class="flex-1 px-4 py-2 border rounded-xl">Cancel</button>
            <button @click="createReport" :disabled="!form.title" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold">
              Create Report
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ templates: Array })

const viewMode = ref('grid')
const search = ref('')
const selectedCategory = ref('All')
const showModal = ref(false)
const selectedTemplate = ref(null)
const showAdvanced = ref(false)

const form = ref({
  title: '',
  template_id: null,
  settings: {
    page_size: 'A4',
    orientation: 'portrait',
    primary_color: '#6366f1',
    background_color: '#ffffff',
    font_family: "'DM Sans', sans-serif",
    margin: 40,
    footer_left: '',
    footer_right: 'Page {n}'
  }
})

const categories = computed(() => {
  const cats = new Set(['All'])
  props.templates?.forEach(tpl => { if (tpl.category) cats.add(tpl.category) })
  return Array.from(cats)
})

const filteredTemplates = computed(() => {
  let t = props.templates || []
  if (search.value) t = t.filter(x => x.name.toLowerCase().includes(search.value.toLowerCase()))
  if (selectedCategory.value !== 'All') t = t.filter(x => x.category === selectedCategory.value)
  return t
})

const openModal = (tpl) => {
  selectedTemplate.value = tpl
  form.value.title = ''
  form.value.template_id = tpl?.id || null
  if (tpl?.settings) Object.assign(form.value.settings, tpl.settings)
  showModal.value = true
  setTimeout(() => document.querySelector('input')?.focus(), 100)
}

const toggleAdvanced = () => showAdvanced.value = !showAdvanced.value

const createReport = () => {
  router.post(route('reports.store'), {
    title: form.value.title,
    template_id: form.value.template_id,
    initial_settings: form.value.settings
  })
}
</script>