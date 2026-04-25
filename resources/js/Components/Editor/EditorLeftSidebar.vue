<template>
  <aside
    class="w-64 flex flex-col overflow-hidden shrink-0 border-r transition-colors duration-200"
    :class="dark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'"
  >
    <!-- Tabs -->
    <div class="flex border-b shrink-0" :class="dark ? 'border-slate-800' : 'border-slate-200'">
      <button
        v-for="t in LEFT_TABS"
        :key="t.id"
        @click="$emit('update:left-tab', t.id)"
        class="flex-1 py-2.5 text-[10px] font-black uppercase tracking-wider transition-colors border-b-2 flex flex-col items-center gap-0.5"
        :class="leftTab === t.id
          ? 'border-indigo-500 text-indigo-500'
          : dark ? 'border-transparent text-slate-600 hover:text-slate-400' : 'border-transparent text-slate-400 hover:text-slate-600'"
      >
        <i :class="t.icon + ' text-sm leading-none'"></i>
        <span>{{ t.label }}</span>
      </button>
    </div>

    <!-- ═══ ELEMENTS TAB ═══ -->
    <template v-if="leftTab === 'elements'">
      <!-- Search -->
      <div class="p-2.5 border-b shrink-0" :class="dark ? 'border-slate-800' : 'border-slate-100'">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
          <input
            :value="elementSearch"
            @input="$emit('update:element-search', $event.target.value)"
            placeholder="Search 50+ elements…"
            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
            :class="dark ? 'bg-slate-800 border-slate-700 text-slate-200 placeholder:text-slate-600' : 'bg-slate-50 border-slate-200 text-slate-700'"
          />
          <button
            v-if="elementSearch"
            @click="$emit('update:element-search', '')"
            class="absolute right-2 top-2 text-slate-400 hover:text-slate-600"
          >
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>
      </div>

      <!-- Elements list -->
      <div class="flex-1 overflow-y-auto p-2 space-y-3">
        <!-- Search results count -->
        <div v-if="elementSearch" class="text-[10px] font-bold px-1" :class="dark ? 'text-slate-500' : 'text-slate-400'">
          <i class="fa-solid fa-filter mr-1"></i>
          {{ filteredCategories.reduce((n, c) => n + c.items.length, 0) }} results
        </div>

        <div v-for="cat in filteredCategories" :key="cat.name">
          <!-- Category header -->
          <button
            @click="$emit('toggle-category', cat.name)"
            class="w-full flex items-center gap-2 mb-1.5 select-none group"
            :class="dark ? 'text-slate-500 hover:text-slate-300' : 'text-slate-400 hover:text-slate-600'"
          >
            <div
              class="w-5 h-5 rounded-md flex items-center justify-center flex-shrink-0 transition-colors"
              :style="{ backgroundColor: cat.color + '20' }"
            >
              <i :class="cat.icon + ' text-[10px]'" :style="{ color: cat.color }"></i>
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest flex-1 text-left">{{ cat.name }}</span>
            <span
              class="text-[8px] font-bold px-1.5 py-0.5 rounded-full"
              :class="dark ? 'bg-slate-800 text-slate-600' : 'bg-slate-100 text-slate-400'"
            >{{ cat.items.length }}</span>
            <i
              class="fa-solid fa-chevron-down text-[9px] transition-transform duration-150"
              :class="collapsedCategories.includes(cat.name) ? '' : 'rotate-180'"
            ></i>
          </button>

          <!-- Elements grid -->
          <div v-show="!collapsedCategories.includes(cat.name)" class="grid grid-cols-2 gap-1.5">
            <div
              v-for="el in cat.items"
              :key="el.type"
              :draggable="true"
              @dragstart="handleDragStart($event, el)"
              @click="$emit('add-element', el.type)"
              class="group flex flex-col items-center gap-1 p-2 rounded-xl border cursor-grab active:cursor-grabbing select-none transition-all duration-150"
              :class="dark
                ? 'border-slate-800 bg-slate-800/50 hover:border-indigo-500/60 hover:bg-indigo-500/10'
                : 'border-slate-200 bg-white hover:border-indigo-400 hover:bg-indigo-50 hover:shadow-sm'"
            >
              <div
                class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                :class="dark ? 'bg-slate-700 group-hover:bg-indigo-500/20' : 'bg-slate-100 group-hover:bg-indigo-100'"
              >
                <i :class="el.icon + ' text-sm leading-none'" :style="{ color: dark ? '#94a3b8' : '#64748b' }"></i>
              </div>
              <span
                class="text-[9px] font-bold text-center leading-tight transition-colors"
                :class="dark ? 'text-slate-500 group-hover:text-indigo-400' : 'text-slate-500 group-hover:text-indigo-700'"
              >{{ el.name }}</span>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="filteredCategories.length === 0" class="flex flex-col items-center py-10 gap-3">
          <i class="fa-solid fa-face-meh text-2xl" :class="dark ? 'text-slate-600' : 'text-slate-300'"></i>
          <p class="text-xs" :class="dark ? 'text-slate-600' : 'text-slate-400'">No elements found</p>
        </div>
      </div>
    </template>

    <!-- ═══ PAGES TAB ═══ -->
    <template v-if="leftTab === 'pages'">
      <div class="flex-1 overflow-y-auto p-2.5 space-y-2">
        <div
          v-for="(page, pi) in pages"
          :key="page.id"
          @click="$emit('select-page', pi)"
          class="flex items-stretch gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all"
          :class="selectedPageIndex === pi
            ? 'border-indigo-500 bg-indigo-500/10 shadow-md shadow-indigo-500/10'
            : dark ? 'border-slate-800 bg-slate-800/50 hover:border-slate-700' : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm'"
        >
          <!-- Mini page thumbnail -->
          <div
            class="w-10 h-14 rounded-lg border flex-shrink-0 overflow-hidden relative"
            :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200 bg-white'"
          >
            <div class="absolute inset-0 p-1 flex flex-col gap-0.5">
              <div class="h-1 rounded-sm w-full" :class="dark ? 'bg-slate-600' : 'bg-slate-200'"></div>
              <div class="h-1 rounded-sm w-3/4" :class="dark ? 'bg-slate-600' : 'bg-slate-200'"></div>
              <div class="h-1 rounded-sm w-1/2 bg-indigo-400/60"></div>
              <div class="h-2 rounded-sm w-full mt-0.5" :class="dark ? 'bg-slate-700' : 'bg-slate-100'"></div>
              <div class="h-1 rounded-sm w-2/3" :class="dark ? 'bg-slate-700' : 'bg-slate-100'"></div>
            </div>
            <!-- Active indicator -->
            <div v-if="selectedPageIndex === pi" class="absolute inset-0 ring-2 ring-inset ring-indigo-500 rounded-lg"></div>
          </div>

          <div class="flex-1 min-w-0">
            <div class="text-[11px] font-bold truncate" :class="dark ? 'text-slate-200' : 'text-slate-700'">
              {{ page.label || `Page ${pi + 1}` }}
            </div>
            <div class="text-[9px] mt-0.5" :class="dark ? 'text-slate-600' : 'text-slate-400'">
              <i class="fa-solid fa-layer-group mr-1"></i>
              {{ page.elements.length }} element{{ page.elements.length !== 1 ? 's' : '' }}
            </div>
            <!-- Active badge -->
            <div v-if="selectedPageIndex === pi"
              class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 bg-indigo-500/15 text-indigo-500 rounded-full text-[9px] font-bold">
              <i class="fa-solid fa-circle-dot text-[8px]"></i> Active
            </div>
          </div>

          <div class="flex flex-col gap-1 items-center justify-center">
            <button @click.stop="$emit('duplicate-page', pi)"
              class="p-1 rounded-lg transition-colors"
              :class="dark ? 'text-slate-600 hover:text-indigo-400 hover:bg-indigo-500/10' : 'text-slate-300 hover:text-indigo-500 hover:bg-indigo-50'"
              title="Duplicate">
              <i class="fa-regular fa-clone text-xs"></i>
            </button>
            <button v-if="pages.length > 1" @click.stop="$emit('remove-page', pi)"
              class="p-1 rounded-lg transition-colors"
              :class="dark ? 'text-slate-700 hover:text-red-400 hover:bg-red-500/10' : 'text-slate-300 hover:text-red-500 hover:bg-red-50'"
              title="Delete">
              <i class="fa-solid fa-xmark text-xs"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Add page button -->
      <div class="p-2.5 border-t shrink-0" :class="dark ? 'border-slate-800' : 'border-slate-100'">
        <button
          @click="$emit('add-page-after', pages.length - 1)"
          class="w-full flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold border-2 border-dashed rounded-xl transition-all"
          :class="dark ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400 hover:bg-indigo-500/5' : 'border-slate-200 text-slate-400 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50'"
        >
          <i class="fa-solid fa-plus text-xs"></i> Add Page
        </button>
      </div>
    </template>

    <!-- ═══ SETTINGS TAB ═══ -->
    <template v-if="leftTab === 'settings'">
      <div class="flex-1 overflow-y-auto">

        <!-- Page Setup -->
        <SettingsSection title="Page Setup" icon="fa-solid fa-file" :dark="dark" :defaultOpen="true">
          <SettingField label="Page Size" icon="fa-solid fa-file-lines" :dark="dark">
            <select :value="rs.page_size" @change="emitRs('page_size', $event.target.value)" :class="inputCls">
              <option v-for="s in PAGE_SIZES" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
          </SettingField>
          <SettingField label="Orientation" icon="fa-solid fa-rotate" :dark="dark">
            <div class="grid grid-cols-2 gap-1.5">
              <button
                v-for="o in ['portrait', 'landscape']"
                :key="o"
                @click="emitRs('orientation', o)"
                class="flex items-center justify-center gap-1.5 py-2 rounded-xl border text-xs font-bold capitalize transition-all"
                :class="rs.orientation === o
                  ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-500/30'
                  : dark ? 'bg-slate-800 text-slate-400 border-slate-700 hover:border-indigo-500' : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300'"
              >
                <i :class="o === 'portrait' ? 'fa-solid fa-file' : 'fa-solid fa-file fa-rotate-90'" class="text-xs"></i>
                {{ o }}
              </button>
            </div>
          </SettingField>
          <SettingField label="Page Margin (px)" icon="fa-solid fa-expand" :dark="dark">
            <input type="number" :value="rs.margin" @change="emitRs('margin', +$event.target.value)"
              :class="inputCls" min="0" max="120" step="4" />
          </SettingField>
          <SettingField label="Corner Radius (px)" icon="fa-solid fa-vector-square" :dark="dark">
            <input type="number" :value="rs.page_radius" @change="emitRs('page_radius', +$event.target.value)"
              :class="inputCls" min="0" max="32" />
          </SettingField>
        </SettingsSection>

        <!-- Typography -->
        <SettingsSection title="Typography" icon="fa-solid fa-font" :dark="dark">
          <SettingField label="Font Family" icon="fa-solid fa-text-height" :dark="dark">
            <select :value="rs.font_family" @change="emitRs('font_family', $event.target.value)" :class="inputCls">
              <option v-for="f in FONTS" :key="f.value" :value="f.value">{{ f.name }}</option>
            </select>
          </SettingField>
          <SettingField label="Base Font Size (px)" icon="fa-solid fa-ruler-horizontal" :dark="dark">
            <input type="number" :value="rs.font_size" @change="emitRs('font_size', +$event.target.value)"
              :class="inputCls" min="8" max="32" step="1" />
          </SettingField>
        </SettingsSection>

        <!-- Brand Colors -->
        <SettingsSection title="Brand Colors" icon="fa-solid fa-palette" :dark="dark">
          <div class="space-y-2">
            <div v-for="cp in COLOR_PROPS" :key="cp.key">
              <label class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-wider mb-1"
                :class="dark ? 'text-slate-600' : 'text-slate-400'">
                <i :class="cp.icon"></i> {{ cp.label }}
              </label>
              <div class="flex items-center gap-2">
                <input type="color" :value="rs[cp.key]" @input="emitRs(cp.key, $event.target.value)"
                  class="w-8 h-8 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                  :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
                <input type="text" :value="rs[cp.key]" @input="emitRs(cp.key, $event.target.value)"
                  class="flex-1 min-w-0 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'" />
              </div>
            </div>
          </div>
        </SettingsSection>

        <!-- Header Settings -->
        <SettingsSection title="Header" icon="fa-solid fa-rectangle-ad" :dark="dark">
          <div class="flex items-center justify-between mb-2">
            <label class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">
              <i class="fa-solid fa-toggle-on mr-1 text-slate-400"></i>Show Header
            </label>
            <ToggleSwitch :value="rs.show_header" @toggle="emitRs('show_header', !rs.show_header)" :dark="dark" />
          </div>
          <template v-if="rs.show_header">
            <SettingField label="Header Text" icon="fa-solid fa-text-width" :dark="dark">
              <input type="text" :value="rs.header_text" @input="emitRs('header_text', $event.target.value)"
                :class="inputCls" placeholder="Header text…" />
            </SettingField>
            <div class="mt-2">
              <label class="flex items-center gap-1 text-[9px] font-black uppercase tracking-wider mb-1"
                :class="dark ? 'text-slate-600' : 'text-slate-400'">
                <i class="fa-solid fa-fill-drip"></i> Header BG
              </label>
              <div class="flex items-center gap-2">
                <input type="color" :value="rs.header_color" @input="emitRs('header_color', $event.target.value)"
                  class="w-8 h-8 rounded-lg border cursor-pointer p-0.5"
                  :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
                <input type="text" :value="rs.header_color" @input="emitRs('header_color', $event.target.value)"
                  class="flex-1 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none"
                  :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'" />
              </div>
            </div>
          </template>
        </SettingsSection>

        <!-- Footer Settings -->
        <SettingsSection title="Footer" icon="fa-solid fa-rectangle-ad fa-flip-vertical" :dark="dark">
          <div class="flex items-center justify-between mb-2">
            <label class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">
              <i class="fa-solid fa-toggle-on mr-1 text-slate-400"></i>Show Footer
            </label>
            <ToggleSwitch :value="rs.show_footer" @toggle="emitRs('show_footer', !rs.show_footer)" :dark="dark" />
          </div>
          <template v-if="rs.show_footer">
            <SettingField label="Footer Left Text" icon="fa-solid fa-align-left" :dark="dark">
              <input type="text" :value="rs.footer_left" @input="emitRs('footer_left', $event.target.value)"
                :class="inputCls" placeholder="Company name…" />
            </SettingField>
            <SettingField label="Footer Right Text" icon="fa-solid fa-align-right" :dark="dark">
              <input type="text" :value="rs.footer_right" @input="emitRs('footer_right', $event.target.value)"
                :class="inputCls" placeholder="Confidential…" />
            </SettingField>
            <div class="mt-2">
              <label class="flex items-center gap-1 text-[9px] font-black uppercase tracking-wider mb-1"
                :class="dark ? 'text-slate-600' : 'text-slate-400'">
                <i class="fa-solid fa-fill-drip"></i> Footer BG
              </label>
              <div class="flex items-center gap-2">
                <input type="color" :value="rs.footer_color" @input="emitRs('footer_color', $event.target.value)"
                  class="w-8 h-8 rounded-lg border cursor-pointer p-0.5"
                  :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
                <input type="text" :value="rs.footer_color" @input="emitRs('footer_color', $event.target.value)"
                  class="flex-1 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none"
                  :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'" />
              </div>
            </div>
          </template>
        </SettingsSection>

        <!-- Page Numbers -->
        <SettingsSection title="Page Numbers" icon="fa-solid fa-hashtag" :dark="dark">
          <div class="flex items-center justify-between">
            <label class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">
              <i class="fa-solid fa-hashtag mr-1 text-slate-400"></i>Show Page Numbers
            </label>
            <ToggleSwitch :value="rs.show_page_numbers" @toggle="emitRs('show_page_numbers', !rs.show_page_numbers)" :dark="dark" />
          </div>
        </SettingsSection>

        <!-- Canvas Tools -->
        <SettingsSection title="Canvas Tools" icon="fa-solid fa-ruler-combined" :dark="dark">
          <div class="space-y-2">
            <div class="flex items-center justify-between py-1">
              <label class="text-xs font-semibold flex items-center gap-1.5" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                <i class="fa-solid fa-border-all text-slate-400"></i> Grid Overlay
              </label>
              <ToggleSwitch :value="showGrid" @toggle="$emit('update:show-grid', !showGrid)" :dark="dark" />
            </div>
            <div class="flex items-center justify-between py-1 border-t" :class="dark ? 'border-slate-800' : 'border-slate-100'">
              <label class="text-xs font-semibold flex items-center gap-1.5" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                <i class="fa-solid fa-magnet text-slate-400"></i> Snap to Grid
              </label>
              <ToggleSwitch :value="snapToGrid" @toggle="$emit('update:snap-to-grid', !snapToGrid)" :dark="dark" />
            </div>
            <div class="flex items-center justify-between py-1 border-t" :class="dark ? 'border-slate-800' : 'border-slate-100'">
              <label class="text-xs font-semibold flex items-center gap-1.5" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                <i class="fa-solid fa-ruler text-slate-400"></i> Rulers
              </label>
              <ToggleSwitch :value="showRulers" @toggle="$emit('update:show-rulers', !showRulers)" :dark="dark" />
            </div>
          </div>
          <SettingField label="Grid Size (px)" icon="fa-solid fa-border-all" :dark="dark" class="mt-3">
            <input type="number" :value="gridSize" @change="$emit('update:grid-size', +$event.target.value)"
              :class="inputCls" min="5" max="60" step="5" />
          </SettingField>
        </SettingsSection>

        <!-- Watermark -->
        <SettingsSection title="Watermark" icon="fa-solid fa-droplet" :dark="dark">
          <SettingField label="Watermark Text" icon="fa-solid fa-font" :dark="dark">
            <input type="text" :value="rs.watermark" @input="emitRs('watermark', $event.target.value)"
              :class="inputCls" placeholder="DRAFT · CONFIDENTIAL" />
          </SettingField>
        </SettingsSection>

        <!-- Background -->
        <SettingsSection title="Background" icon="fa-solid fa-image" :dark="dark">
          <SettingField label="BG Image URL" icon="fa-solid fa-link" :dark="dark">
            <input type="url" :value="rs.bg_image" @input="emitRs('bg_image', $event.target.value)"
              :class="inputCls" placeholder="https://…" />
          </SettingField>
        </SettingsSection>

        <!-- RTL -->
        <SettingsSection title="Text Direction" icon="fa-solid fa-align-right" :dark="dark">
          <div class="flex items-center justify-between">
            <label class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">
              <i class="fa-solid fa-arrow-right-arrow-left mr-1 text-slate-400"></i> RTL Layout
            </label>
            <ToggleSwitch :value="rs.rtl" @toggle="emitRs('rtl', !rs.rtl)" :dark="dark" />
          </div>
        </SettingsSection>

      </div>
    </template>
  </aside>
</template>

<script setup>
import { computed, inject } from 'vue';

// Injected from Editor.vue
const FONTS = inject('FONTS');
const PAGE_SIZES = inject('PAGE_SIZES');
const ELEMENT_CATEGORIES = inject('ELEMENT_CATEGORIES');

const props = defineProps({
  dark: Boolean,
  leftTab: String,
  pages: Array,
  selectedPageIndex: Number,
  elementSearch: String,
  collapsedCategories: Array,
  rs: Object,
  showGrid: Boolean,
  snapToGrid: Boolean,
  showRulers: Boolean,
  gridSize: Number,
  inputCls: String,
});

const emit = defineEmits([
  'update:left-tab', 'update:element-search', 'add-element', 'select-page',
  'add-page-after', 'remove-page', 'duplicate-page', 'toggle-category',
  'update:rs', 'update:show-grid', 'update:snap-to-grid', 'update:show-rulers', 'update:grid-size',
]);

const LEFT_TABS = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-puzzle-piece' },
  { id: 'pages',    label: 'Pages',    icon: 'fa-regular fa-file' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-gear' },
];

// All 50+ elements in RICH categories
const ALL_CATEGORIES = [
  {
    name: 'Text & Typography',
    icon: 'fa-solid fa-font',
    color: '#6366f1',
    items: [
      { type: 'heading',    name: 'Heading H1',   icon: 'fa-solid fa-heading' },
      { type: 'subheading', name: 'Heading H2',   icon: 'fa-solid fa-h' },
      { type: 'text',       name: 'Paragraph',    icon: 'fa-solid fa-paragraph' },
      { type: 'quote',      name: 'Quote',        icon: 'fa-solid fa-quote-left' },
      { type: 'blockquote', name: 'Blockquote',   icon: 'fa-solid fa-quote-right' },
      { type: 'highlight',  name: 'Highlight',    icon: 'fa-solid fa-highlighter' },
      { type: 'list',       name: 'Bullet List',  icon: 'fa-solid fa-list-ul' },
      { type: 'checklist',  name: 'Checklist',    icon: 'fa-solid fa-list-check' },
      { type: 'code',       name: 'Code Block',   icon: 'fa-solid fa-code' },
      { type: 'link',       name: 'Hyperlink',    icon: 'fa-solid fa-link' },
    ],
  },
  {
    name: 'Labels & Callouts',
    icon: 'fa-solid fa-tag',
    color: '#8b5cf6',
    items: [
      { type: 'badge',      name: 'Badge',        icon: 'fa-solid fa-tag' },
      { type: 'callout',    name: 'Callout',      icon: 'fa-solid fa-circle-info' },
      { type: 'alert',      name: 'Alert',        icon: 'fa-solid fa-triangle-exclamation' },
      { type: 'icon',       name: 'Icon/Emoji',   icon: 'fa-solid fa-star' },
      { type: 'rating',     name: 'Star Rating',  icon: 'fa-regular fa-star' },
      { type: 'watermark',  name: 'Watermark',    icon: 'fa-solid fa-droplet' },
    ],
  },
  {
    name: 'Charts & Graphs',
    icon: 'fa-solid fa-chart-bar',
    color: '#10b981',
    items: [
      { type: 'bar-chart',       name: 'Bar Chart',   icon: 'fa-solid fa-chart-bar' },
      { type: 'line-chart',      name: 'Line Chart',  icon: 'fa-solid fa-chart-line' },
      { type: 'area-chart',      name: 'Area Chart',  icon: 'fa-solid fa-chart-area' },
      { type: 'pie-chart',       name: 'Pie Chart',   icon: 'fa-solid fa-chart-pie' },
      { type: 'doughnut-chart',  name: 'Doughnut',    icon: 'fa-solid fa-circle-half-stroke' },
      { type: 'radar-chart',     name: 'Radar',       icon: 'fa-solid fa-circle-nodes' },
    ],
  },
  {
    name: 'Data & KPIs',
    icon: 'fa-solid fa-database',
    color: '#f59e0b',
    items: [
      { type: 'metric',            name: 'KPI Card',       icon: 'fa-solid fa-square-poll-vertical' },
      { type: 'progress',          name: 'Progress Bar',   icon: 'fa-solid fa-bars-progress' },
      { type: 'circular-progress', name: 'Circle %',       icon: 'fa-solid fa-circle-notch' },
      { type: 'stat-row',          name: 'Stats Row',      icon: 'fa-solid fa-table-columns' },
      { type: 'table',             name: 'Data Table',     icon: 'fa-solid fa-table' },
      { type: 'timeline',          name: 'Timeline',       icon: 'fa-solid fa-timeline' },
      { type: 'toc',               name: 'Contents',       icon: 'fa-solid fa-list-ol' },
    ],
  },
  {
    name: 'Media & Embeds',
    icon: 'fa-solid fa-photo-film',
    color: '#ec4899',
    items: [
      { type: 'image', name: 'Image',       icon: 'fa-solid fa-image' },
      { type: 'video', name: 'Video Embed', icon: 'fa-solid fa-film' },
    ],
  },
  {
    name: 'Shapes & Lines',
    icon: 'fa-solid fa-shapes',
    color: '#0ea5e9',
    items: [
      { type: 'rectangle',    name: 'Rectangle',  icon: 'fa-regular fa-square' },
      { type: 'circle',       name: 'Circle',     icon: 'fa-regular fa-circle' },
      { type: 'triangle',     name: 'Triangle',   icon: 'fa-solid fa-play fa-rotate-270' },
      { type: 'star',         name: 'Star',       icon: 'fa-regular fa-star' },
      { type: 'line',         name: 'Line',       icon: 'fa-solid fa-minus' },
      { type: 'arrow',        name: 'Arrow →',    icon: 'fa-solid fa-arrow-right' },
      { type: 'double-arrow', name: 'Arrow ↔',   icon: 'fa-solid fa-arrows-left-right' },
      { type: 'divider',      name: 'Divider',    icon: 'fa-solid fa-grip-lines' },
      { type: 'spacer',       name: 'Spacer',     icon: 'fa-solid fa-square-dashed' },
    ],
  },
  {
    name: 'Cards & Components',
    icon: 'fa-solid fa-id-card',
    color: '#f97316',
    items: [
      { type: 'social-card',  name: 'Profile Card', icon: 'fa-solid fa-id-badge' },
      { type: 'testimonial',  name: 'Testimonial',  icon: 'fa-solid fa-comment-dots' },
      { type: 'kanban',       name: 'Kanban Card',  icon: 'fa-solid fa-thumbtack' },
      { type: 'price-card',   name: 'Price Card',   icon: 'fa-solid fa-dollar-sign' },
    ],
  },
  {
    name: 'Report Elements',
    icon: 'fa-solid fa-file-lines',
    color: '#64748b',
    items: [
      { type: 'pagenum',   name: 'Page Number', icon: 'fa-solid fa-hashtag' },
      { type: 'date',      name: 'Date',        icon: 'fa-solid fa-calendar-days' },
      { type: 'signature', name: 'Signature',   icon: 'fa-solid fa-signature' },
    ],
  },
];

const COLOR_PROPS = [
  { key: 'primary_color',    label: 'Primary',    icon: 'fa-solid fa-circle text-indigo-500' },
  { key: 'accent_color',     label: 'Accent',     icon: 'fa-solid fa-circle text-violet-500' },
  { key: 'background_color', label: 'Page BG',    icon: 'fa-solid fa-fill-drip' },
  { key: 'text_color',       label: 'Text',       icon: 'fa-solid fa-font' },
  { key: 'header_color',     label: 'Header BG',  icon: 'fa-solid fa-rectangle-ad' },
  { key: 'footer_color',     label: 'Footer BG',  icon: 'fa-solid fa-rectangle-ad fa-flip-vertical' },
];

const filteredCategories = computed(() => {
  const categories = ALL_CATEGORIES;
  if (!props.elementSearch?.trim()) return categories;
  const q = props.elementSearch.toLowerCase();
  return categories.map(c => ({
    ...c,
    items: c.items.filter(i => i.name.toLowerCase().includes(q) || i.type.includes(q)),
  })).filter(c => c.items.length > 0);
});

const emitRs = (key, value) => emit('update:rs', { ...props.rs, [key]: value });

const handleDragStart = (e, el) => {
  e.dataTransfer.setData('elementType', el.type);
  e.dataTransfer.effectAllowed = 'copy';
};
</script>

<!-- Inline sub-components -->
<script>
// ToggleSwitch
const ToggleSwitch = {
  name: 'ToggleSwitch',
  props: { value: Boolean, dark: Boolean },
  emits: ['toggle'],
  template: `
    <button @click="$emit('toggle')"
      class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 flex-shrink-0"
      :class="value ? 'bg-indigo-600' : dark ? 'bg-slate-700' : 'bg-slate-200'">
      <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-200"
        :class="value ? 'translate-x-5' : 'translate-x-1'"></span>
    </button>
  `,
};

// Settings section
const SettingsSection = {
  name: 'SettingsSection',
  props: { title: String, icon: String, dark: Boolean, defaultOpen: Boolean },
  data() { return { open: this.defaultOpen ?? false }; },
  template: `
    <div class="border-b" :class="dark ? 'border-slate-800' : 'border-slate-100'">
      <button @click="open = !open"
        class="w-full flex items-center justify-between px-4 py-2.5 transition-colors"
        :class="dark ? 'hover:bg-white/5' : 'hover:bg-slate-50'">
        <div class="flex items-center gap-2">
          <i :class="icon + ' text-[11px]'" :style="open ? 'color:#6366f1' : (dark ? 'color:#475569' : 'color:#94a3b8')"></i>
          <span class="text-[10px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-400' : 'text-slate-600'">{{ title }}</span>
        </div>
        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
          :class="[open ? 'rotate-180' : '', dark ? 'text-slate-600' : 'text-slate-400']"></i>
      </button>
      <div v-show="open" class="px-4 pb-4 pt-1 space-y-3">
        <slot />
      </div>
    </div>
  `,
};

// Setting field
const SettingField = {
  name: 'SettingField',
  props: { label: String, icon: String, dark: Boolean },
  template: `
    <div>
      <label class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-wider mb-1.5"
        :class="dark ? 'text-slate-600' : 'text-slate-400'">
        <i v-if="icon" :class="icon + ' text-[9px]'"></i>
        {{ label }}
      </label>
      <slot />
    </div>
  `,
};
</script>

<style scoped>
.prop-label {
  display: block;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 4px;
}
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
.dark ::-webkit-scrollbar-thumb { background: #334155; }
</style>