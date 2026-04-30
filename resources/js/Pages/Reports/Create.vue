<!-- resources/js/Pages/Reports/Create.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="route('reports.index')" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          <i class="fa-solid fa-arrow-left text-slate-500"></i>
        </Link>
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create New Report</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Choose a template or start from scratch</p>
        </div>
      </div>
    </template>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Step 1: Choose Template -->
        <div v-if="step === 1" class="animate-fade-in">
          <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-semibold mb-4">
              <i class="fa-solid fa-layer-group"></i>
              <span>Step 1 of 2</span>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Choose a Template</h3>
            <p class="text-slate-500 dark:text-slate-400">Start with a professional template or build from scratch</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
            <!-- Blank Template -->
            <div @click="selectTemplate(null)"
              :class="selectedTemplate === null ? 'ring-2 ring-indigo-500 border-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600'"
              class="cursor-pointer rounded-2xl border-2 bg-white dark:bg-slate-800 flex flex-col items-center justify-center min-h-[280px] gap-3 transition-all hover:shadow-xl group">
              <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 transition-all">
                <i class="fa-solid fa-plus text-2xl text-slate-400 group-hover:text-indigo-500"></i>
              </div>
              <div class="text-center">
                <p class="font-bold text-slate-700 dark:text-slate-300">Blank Canvas</p>
                <p class="text-xs text-slate-400 mt-1">Start from scratch</p>
              </div>
            </div>

            <!-- Template Cards -->
            <div v-for="tpl in templates" :key="tpl.id" 
              @click="selectTemplate(tpl)"
              :class="selectedTemplate?.id === tpl.id ? 'ring-2 ring-indigo-500 border-indigo-300' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600'"
              class="cursor-pointer rounded-2xl border-2 bg-white dark:bg-slate-800 overflow-hidden transition-all hover:shadow-xl group">
              <div class="relative h-36 overflow-hidden" :style="{ background: tpl.cover_gradient || 'linear-gradient(135deg, #6366f1, #8b5cf6)' }">
                <div class="absolute inset-0 flex items-center justify-center opacity-30 group-hover:opacity-100 transition-opacity">
                  <i class="fa-solid fa-file-lines text-white text-4xl"></i>
                </div>
                <div v-if="tpl.badge" class="absolute top-2 right-2">
                  <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-400 text-amber-900">{{ tpl.badge }}</span>
                </div>
              </div>
              <div class="p-4">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ tpl.name }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ tpl.description }}</p>
                <div class="flex flex-wrap gap-1 mt-2">
                  <span v-for="tag in tpl.tags?.slice(0,2)" :key="tag" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 rounded text-[9px]">{{ tag }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-center">
            <button @click="step = 2" :disabled="!templateSelected"
              class="group relative inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/25 disabled:opacity-50 disabled:cursor-not-allowed">
              Continue
              <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </button>
          </div>
        </div>

        <!-- Step 2: Configure Report -->
        <div v-else class="max-w-lg mx-auto animate-fade-in">
          <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-semibold mb-4">
              <i class="fa-solid fa-sliders-h"></i>
              <span>Step 2 of 2</span>
            </div>
            <div class="w-20 h-20 rounded-2xl mx-auto mb-4 overflow-hidden shadow-lg" :style="{ background: selectedTemplate?.cover_gradient || 'linear-gradient(135deg, #6366f1, #8b5cf6)' }">
              <div class="w-full h-full flex items-center justify-center">
                <i class="fa-solid fa-file-lines text-white text-3xl opacity-70"></i>
              </div>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ selectedTemplate?.name || 'Blank Canvas' }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure your report settings</p>
          </div>

          <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-5 shadow-xl">
            <div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Report Title <span class="text-red-500">*</span></label>
              <input v-model="form.title" type="text" ref="titleInput" required
                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all"
                placeholder="e.g., Q4 2024 Annual Report">
              <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Page Size</label>
                <select v-model="form.initial_settings.page_size" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800">
                  <option value="A4">A4 (210 × 297 mm)</option>
                  <option value="Letter">Letter (8.5 × 11 in)</option>
                  <option value="Legal">Legal (8.5 × 14 in)</option>
                  <option value="A3">A3 (297 × 420 mm)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Orientation</label>
                <select v-model="form.initial_settings.orientation" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800">
                  <option value="portrait">Portrait</option>
                  <option value="landscape">Landscape</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Primary Color</label>
                <div class="flex gap-2">
                  <input type="color" v-model="form.initial_settings.primary_color" class="w-10 h-9 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer">
                  <input type="text" v-model="form.initial_settings.primary_color" class="flex-1 px-2 py-1.5 text-xs font-mono border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800">
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Font Family</label>
                <select v-model="form.initial_settings.font_family" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800">
                  <option value="'DM Sans', sans-serif">DM Sans</option>
                  <option value="'Inter', sans-serif">Inter</option>
                  <option value="Georgia, serif">Georgia</option>
                  <option value="'Playfair Display', serif">Playfair Display</option>
                </select>
              </div>
            </div>

            <button type="button" @click="showAdvanced = !showAdvanced" class="text-sm text-indigo-600 flex items-center gap-1">
              <i :class="showAdvanced ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-xs"></i>
              Advanced Settings
            </button>

            <div v-show="showAdvanced" class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-200 dark:border-slate-700">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Background Color</label>
                <input type="color" v-model="form.initial_settings.background_color" class="w-full h-9 rounded-lg border border-slate-200 dark:border-slate-600">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Margin (px)</label>
                <input type="number" v-model="form.initial_settings.margin" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Footer Left</label>
                <input v-model="form.initial_settings.footer_left" placeholder="Company Name" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Footer Right</label>
                <input v-model="form.initial_settings.footer_right" placeholder="Page {n}" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg">
              </div>
            </div>

            <div class="flex gap-3 pt-4">
              <button type="button" @click="step = 1" class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                ← Back
              </button>
              <button type="submit" :disabled="form.processing || !form.title"
                class="flex-[2] px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 disabled:opacity-50 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-2">
                <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                {{ form.processing ? 'Creating...' : 'Create & Open Editor' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  templates: Array,
  selectedTemplate: Object
})

const step = ref(1)
const selectedTemplate = ref(props.selectedTemplate || null)
const templateSelected = ref(!!props.selectedTemplate)
const showAdvanced = ref(false)
const titleInput = ref(null)

const form = useForm({
  title: '',
  template_id: selectedTemplate.value?.id || null,
  initial_settings: {
    page_size: selectedTemplate.value?.settings?.page_size || 'A4',
    orientation: selectedTemplate.value?.settings?.orientation || 'portrait',
    primary_color: selectedTemplate.value?.settings?.primary_color || '#6366f1',
    background_color: selectedTemplate.value?.settings?.background_color || '#ffffff',
    font_family: selectedTemplate.value?.settings?.font_family || "'DM Sans', sans-serif",
    margin: selectedTemplate.value?.settings?.margin || 40,
    footer_left: selectedTemplate.value?.settings?.footer_left || '',
    footer_right: selectedTemplate.value?.settings?.footer_right || 'Page {n}',
    show_page_numbers: true
  }
})

const selectTemplate = (tpl) => {
  selectedTemplate.value = tpl
  templateSelected.value = true
  form.template_id = tpl?.id || null
  
  if (tpl?.settings) {
    form.initial_settings = {
      page_size: tpl.settings.page_size || 'A4',
      orientation: tpl.settings.orientation || 'portrait',
      primary_color: tpl.settings.primary_color || '#6366f1',
      background_color: tpl.settings.background_color || '#ffffff',
      font_family: tpl.settings.font_family || "'DM Sans', sans-serif",
      margin: tpl.settings.margin || 40,
      footer_left: tpl.settings.footer_left || '',
      footer_right: tpl.settings.footer_right || 'Page {n}',
      show_page_numbers: tpl.settings.show_page_numbers ?? true
    }
  }
}

const submit = () => {
  form.post(route('reports.store'), {
    onSuccess: () => {
      // Redirect handled by controller
    }
  })
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-in {
  animation: fade-in 0.4s ease-out forwards;
}
</style>