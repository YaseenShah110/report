<!--
  Templates/Index.vue - Template Gallery Page
  -----------------------------------------------------------
  Displays all available report templates.
  Users can browse, search, filter by category, and select a template.
  Grid/List view toggle for different display preferences.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Template Gallery</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Choose a template to start building your report</p>
        </div>
        <!-- View Mode Toggle -->
        <div class="flex items-center gap-1.5 sm:gap-2 bg-slate-100 dark:bg-slate-700 rounded-xl p-1 self-start">
          <button 
            v-for="v in ['grid', 'list']" :key="v" 
            @click="viewMode = v"
            :class="viewMode === v ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'"
            class="p-1.5 sm:p-2 rounded-lg transition-all"
          >
            <i :class="v === 'grid' ? 'fa-solid fa-grip' : 'fa-solid fa-list'" class="text-xs sm:text-sm"></i>
          </button>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6">
      <div class="max-w-7xl mx-auto">
        
        <!-- Search & Category Filters -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4 sm:mb-6">
          <!-- Search Input -->
          <div class="flex-1 min-w-[150px] sm:min-w-[200px] relative">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
            <input 
              v-model="search" 
              type="text" 
              placeholder="Search templates..." 
              class="w-full pl-8 sm:pl-9 pr-3 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
          </div>
          
          <!-- Category Buttons -->
          <div class="flex gap-1.5 sm:gap-2 flex-wrap">
            <button 
              v-for="cat in categories" :key="cat" 
              @click="selectedCategory = cat"
              :class="selectedCategory === cat ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600'"
              class="px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-medium rounded-full capitalize transition-all"
            >
              {{ cat }}
            </button>
          </div>
        </div>

        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
          
          <!-- Template Cards -->
          <div 
            v-for="tpl in filteredTemplates" :key="tpl.id" 
            class="group bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-xl transition-all duration-300 cursor-pointer"
            @click="openModal(tpl)"
          >
            <!-- Cover Gradient Area -->
            <div class="relative h-36 sm:h-48 overflow-hidden" :style="{ background: tpl.cover_gradient || 'linear-gradient(135deg, #6366f1, #8b5cf6)' }">
              <div class="absolute inset-0 flex items-center justify-center opacity-20 group-hover:opacity-100 transition-opacity">
                <i class="fa-solid fa-file-lines text-white text-4xl sm:text-5xl"></i>
              </div>
              
              <!-- Badge -->
              <div v-if="tpl.badge" class="absolute top-2 right-2 sm:top-3 sm:right-3">
                <span class="px-2 py-0.5 text-[9px] sm:text-xs font-bold rounded-full bg-amber-400 text-amber-900 shadow-sm">
                  {{ tpl.badge }}
                </span>
              </div>
              
              <!-- Hover Overlay -->
              <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="px-3 sm:px-4 py-1.5 sm:py-2 bg-white text-slate-900 rounded-lg text-xs sm:text-sm font-semibold transform scale-90 group-hover:scale-100 transition-transform">
                  Use Template
                </span>
              </div>
            </div>

            <!-- Card Info -->
            <div class="p-3 sm:p-4">
              <h3 class="font-semibold text-slate-900 dark:text-white text-sm sm:text-base">{{ tpl.name }}</h3>
              <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ tpl.description }}</p>
              
              <!-- Tags -->
              <div class="flex flex-wrap gap-1 mt-2">
                <span v-for="tag in tpl.tags?.slice(0,3)" :key="tag" 
                      class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded text-[9px] sm:text-[10px]">
                  {{ tag }}
                </span>
              </div>
              
              <!-- Use Template Button -->
              <button class="mt-3 w-full py-2 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-[10px] sm:text-xs font-semibold rounded-lg transition-colors">
                Use Template
              </button>
            </div>
          </div>

          <!-- Blank Canvas Card -->
          <div 
            @click="openModal(null)" 
            class="group bg-white dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition-all cursor-pointer flex flex-col items-center justify-center min-h-[280px] sm:min-h-[320px] gap-2 sm:gap-3"
          >
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-slate-100 dark:bg-slate-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 flex items-center justify-center transition-all">
              <i class="fa-solid fa-plus text-slate-400 group-hover:text-indigo-500 text-xl sm:text-2xl"></i>
            </div>
            <div class="text-center">
              <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm sm:text-base">Blank Canvas</p>
              <p class="text-[10px] sm:text-xs text-slate-400 mt-1">Start from scratch</p>
            </div>
          </div>
        </div>

        <!-- List View -->
        <div v-else class="space-y-2 sm:space-y-3">
          <div 
            v-for="tpl in filteredTemplates" :key="tpl.id" 
            class="bg-white dark:bg-slate-800 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all cursor-pointer flex items-center gap-3 sm:gap-4"
            @click="openModal(tpl)"
          >
            <!-- Thumbnail -->
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl overflow-hidden flex-shrink-0" :style="{ background: tpl.cover_gradient || 'linear-gradient(135deg, #6366f1, #8b5cf6)' }">
              <div class="w-full h-full flex items-center justify-center">
                <i class="fa-solid fa-file-lines text-white text-xl sm:text-2xl opacity-50"></i>
              </div>
            </div>
            
            <!-- Info -->
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-slate-900 dark:text-white text-sm sm:text-base">{{ tpl.name }}</h3>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 line-clamp-1">{{ tpl.description }}</p>
              <div class="flex gap-1 mt-1">
                <span v-for="tag in tpl.tags?.slice(0,2)" :key="tag" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded text-[9px] sm:text-[10px]">{{ tag }}</span>
              </div>
            </div>
            
            <!-- Use Button -->
            <button class="px-3 sm:px-4 py-1.5 sm:py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs sm:text-sm font-semibold flex-shrink-0 transition-colors">
              Use
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Report Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto animate-scale-in">
          
          <!-- Modal Header -->
          <div class="sticky top-0 bg-white dark:bg-slate-800 px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between z-10">
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Create New Report</h3>
              <p class="text-[10px] sm:text-xs text-slate-500 mt-0.5">{{ selectedTemplate ? `Using: ${selectedTemplate.name}` : 'Blank canvas' }}</p>
            </div>
            <button @click="showModal = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
              <i class="fa-solid fa-xmark text-xl text-slate-400"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
            <!-- Report Title -->
            <div>
              <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Report Title <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.title" 
                type="text" 
                required
                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="e.g., Q4 2024 Annual Report"
              >
            </div>

            <!-- Settings Grid -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
              <div>
                <label class="block text-[10px] sm:text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Page Size</label>
                <select v-model="form.settings.page_size" class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border rounded-lg bg-white dark:bg-slate-900">
                  <option value="A4">A4</option>
                  <option value="Letter">Letter</option>
                  <option value="Legal">Legal</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] sm:text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Orientation</label>
                <select v-model="form.settings.orientation" class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border rounded-lg bg-white dark:bg-slate-900">
                  <option value="portrait">Portrait</option>
                  <option value="landscape">Landscape</option>
                </select>
              </div>
            </div>
            
            <!-- Create Button -->
            <button @click="createReport" :disabled="!form.title" class="w-full py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors">
              Create Report
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
/**
 * Templates Page Script
 * Handles: search, category filter, grid/list view, create report modal
 */
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ templates: Array })

const viewMode = ref('grid')
const search = ref('')
const selectedCategory = ref('All')
const showModal = ref(false)
const selectedTemplate = ref(null)

// Form for creating report from template
const form = ref({
  title: '',
  settings: {
    page_size: 'A4',
    orientation: 'portrait'
  }
})

/**
 * Get unique categories from templates
 */
const categories = computed(() => {
  const cats = new Set(['All'])
  props.templates?.forEach(tpl => {
    if (tpl.category) cats.add(tpl.category)
  })
  return Array.from(cats)
})

/**
 * Filter templates by search query and category
 */
const filteredTemplates = computed(() => {
  let t = props.templates || []
  if (search.value) {
    t = t.filter(x => x.name.toLowerCase().includes(search.value.toLowerCase()))
  }
  if (selectedCategory.value !== 'All') {
    t = t.filter(x => x.category === selectedCategory.value)
  }
  return t
})

/**
 * Open the create report modal with selected template
 */
const openModal = (tpl) => {
  selectedTemplate.value = tpl
  form.value.title = ''
  form.value.settings = tpl?.settings || { page_size: 'A4', orientation: 'portrait' }
  showModal.value = true
}

/**
 * Create report from selected template
 */
const createReport = () => {
  router.post(route('reports.store'), {
    title: form.value.title,
    template_id: selectedTemplate.value?.id || null,
    initial_settings: form.value.settings
  })
}
</script>

<style scoped>
@keyframes scale-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.animate-scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>