<template>
  <aside class="left-panel" :class="{ collapsed: isCollapsed, dark: isDark }">
    <!-- Collapse Toggle -->
    <button class="panel-toggle" @click="toggle" :title="isCollapsed ? 'Expand' : 'Collapse'">
      <i :class="isCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'" />
    </button>

    <!-- Tab Bar -->
    <nav class="panel-tabs" v-show="!isCollapsed">
      <button v-for="tab in tabs" :key="tab.id" class="ptab" :class="{ active: activeTab === tab.id }" @click="$emit('update:active-tab', tab.id)" :title="tab.label">
        <i :class="tab.icon" />
        <span class="ptab-label">{{ tab.label }}</span>
      </button>
    </nav>

    <!-- Content -->
    <div class="panel-content" v-show="!isCollapsed">

      <!-- ═══ ELEMENTS ═══════════════════════════════════════════════ -->
      <div v-show="activeTab === 'elements'" class="tab-panel">
        <div class="search-wrap">
          <i class="fa-solid fa-search search-icon" />
          <input v-model="elSearch" class="search-input" placeholder="Search 50+ elements…" @input="searchEls" />
          <button v-if="elSearch" class="search-clear" @click="elSearch = ''"><i class="fa-solid fa-xmark" /></button>
        </div>

        <!-- Quick Add Chips -->
        <div class="quick-chips">
          <button v-for="q in quickEls" :key="q.type" class="qchip" @click="addCenter(q)" :title="q.label">
            <i :class="q.icon" />{{ q.label }}
          </button>
        </div>

        <!-- Categories -->
        <div class="el-scroll">
          <div v-for="cat in filteredCats" :key="cat.name" class="el-cat">
            <button class="cat-header" @click="toggleCat(cat.name)">
              <span>{{ cat.icon }} {{ cat.name }}</span>
              <span class="cat-badge">{{ cat.items.length }}</span>
              <i :class="collapsedCats.has(cat.name) ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'" class="cat-chevron" />
            </button>
            <div v-if="!collapsedCats.has(cat.name)" class="el-grid">
              <div
                v-for="el in cat.items" :key="el.type"
                class="el-card"
                :class="{ 'el-new': el.isNew, 'el-pro': el.isPro }"
                draggable="true"
                @dragstart="$emit('canvas-drag-start', $event, el)"
                @dblclick="addCenter(el)"
                :title="`${el.label} — double-click or drag`"
              >
                <div class="el-icon"><i :class="el.icon" /></div>
                <span class="el-name">{{ el.label }}</span>
                <span v-if="el.isNew" class="el-badge new">NEW</span>
                <span v-if="el.isPro" class="el-badge pro">PRO</span>
              </div>
            </div>
          </div>
          <div v-if="!filteredCats.length" class="empty-hint">
            <i class="fa-solid fa-search" /><p>No elements found</p>
          </div>
        </div>
      </div>

      <!-- ═══ PAGES ═══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'pages'" class="tab-panel">
        <button class="btn-add-page" @click="$emit('add-page')">
          <i class="fa-solid fa-plus" /> Add Page
        </button>
        <div class="pages-list" ref="pagesListEl">
          <div
            v-for="(page, pi) in report.content"
            :key="page.id"
            class="page-card"
            :class="{ active: currentPage === pi }"
            @click="$emit('select-page', pi)"
            @dblclick="startRename(pi)"
            draggable="true"
            @dragstart="pageDragStart($event, pi)"
            @dragover.prevent="pageDragOver($event, pi)"
            @drop.prevent="pageDrop($event, pi)"
            @dragleave="pageDragOver = null"
          >
            <!-- Mini preview -->
            <div class="page-thumb" :style="{ background: settings.background_color || '#fff' }">
              <div class="thumb-inner">
                <div v-for="(el, ei) in page.elements.slice(0, 12)" :key="ei" class="thumb-el" :style="getMiniElStyle(el)" />
              </div>
              <div v-if="!page.elements.length" class="thumb-empty"><i class="fa-solid fa-plus" /></div>
            </div>

            <!-- Info -->
            <div class="page-info">
              <div class="page-name-wrap">
                <span v-if="renamingPage !== pi">{{ page.label || `Page ${pi + 1}` }}</span>
                <input v-else
                  :value="page.label || `Page ${pi + 1}`"
                  class="page-rename-input"
                  @blur="finishRename(pi, $event.target.value)"
                  @keydown.enter="finishRename(pi, $event.target.value)"
                  @keydown.escape="renamingPage = null"
                  @click.stop ref="renameInputEl"
                />
              </div>
              <span class="page-el-count">{{ page.elements.length }} el</span>
            </div>

            <!-- Actions -->
            <div class="page-actions">
              <button @click.stop="$emit('duplicate-page', pi)" title="Duplicate"><i class="fa-solid fa-copy" /></button>
              <button @click.stop="$emit('delete-page', pi)" :disabled="report.content.length <= 1" class="danger" title="Delete"><i class="fa-solid fa-trash" /></button>
            </div>

            <div v-if="currentPage === pi" class="page-active-glow" />
          </div>
        </div>
      </div>

      <!-- ═══ LAYERS ═══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'layers'" class="tab-panel">
        <div class="layers-header">
          <span>Layers <span class="layer-count">{{ currentPageElements.length }}</span></span>
          <button class="micro-btn" @click="$emit('deselect-all')" title="Deselect all"><i class="fa-solid fa-xmark" /></button>
        </div>
        <div class="layers-list">
          <div
            v-for="(el, ei) in reversedElements" :key="el.id"
            class="layer-item"
            :class="{ selected: isLayerSelected(ei), locked: el.locked, hidden: el.visible === false, grouped: !!el.groupId }"
            @click="selectLayer(ei)"
          >
            <i class="fa-solid fa-grip-vertical drag-handle" />
            <i :class="getElIcon(el.type)" class="layer-type-icon" :style="{ color: getTypeColor(el.type) }" />
            <div class="layer-info">
              <span class="layer-name">{{ getLayerName(el) }}</span>
              <span class="layer-type">{{ el.type }}</span>
            </div>
            <div class="layer-ctrls">
              <button @click.stop="$emit('toggle-visibility', getRealIdx(ei))" :title="el.visible === false ? 'Show' : 'Hide'">
                <i :class="el.visible === false ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
              </button>
              <button @click.stop="$emit('toggle-lock', getRealIdx(ei))" :title="el.locked ? 'Unlock' : 'Lock'">
                <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
              </button>
            </div>
          </div>
          <div v-if="!currentPageElements.length" class="empty-hint">
            <i class="fa-solid fa-layer-group" /><p>No elements on this page</p>
          </div>
        </div>
      </div>

      <!-- ═══ MEDIA ═══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'media'" class="tab-panel">
        <!-- Upload -->
        <div class="upload-zone" @click="triggerUpload" @dragover.prevent @drop.prevent="onMediaDrop">
          <i class="fa-solid fa-cloud-arrow-up" />
          <span>Upload Images</span>
          <small>JPEG, PNG, WebP, SVG up to 10MB</small>
        </div>

        <!-- Stock Photos -->
        <div class="media-section">
          <div class="media-section-title"><i class="fa-solid fa-images" /> Stock Photos</div>
          <div class="search-wrap">
            <input v-model="stockQuery" class="search-input" placeholder="Search free photos…" @keydown.enter="searchStock" style="padding-left:10px" />
            <button class="micro-btn" @click="searchStock" :disabled="stockLoading">
              <i :class="stockLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-search'" />
            </button>
          </div>
          <div v-if="stockImages.length" class="stock-grid">
            <div v-for="img in stockImages" :key="img.id" class="stock-item" @click="addStockImage(img)" :title="img.author || 'Stock photo'">
              <img :src="img.thumb" loading="lazy" />
              <div class="stock-overlay"><i class="fa-solid fa-plus" /></div>
            </div>
          </div>
        </div>

        <!-- Uploaded -->
        <div v-if="uploadedImages.length" class="media-section">
          <div class="media-section-title"><i class="fa-solid fa-folder-open" /> Uploaded</div>
          <div class="uploaded-grid">
            <div v-for="img in uploadedImages" :key="img.url" class="uploaded-item" @click="addUploadedImage(img)">
              <img :src="img.url" loading="lazy" />
              <span class="uploaded-name">{{ img.name }}</span>
              <button class="remove-img" @click.stop="removeUploaded(img)"><i class="fa-solid fa-xmark" /></button>
            </div>
          </div>
        </div>

        <input ref="mediaFileInput" type="file" accept="image/*" multiple class="hidden" @change="onMediaFileInput" />
      </div>

      <!-- ═══ TEMPLATES ════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'templates'" class="tab-panel">
        <p class="tab-desc">Apply a color scheme to this report</p>
        <div class="template-grid">
          <div v-for="tpl in quickTemplates" :key="tpl.name" class="tpl-card" @click="$emit('apply-template', tpl)">
            <div class="tpl-preview" :style="{ background: tpl.gradient }">
              <div class="tpl-hover"><i class="fa-solid fa-check" /></div>
            </div>
            <span class="tpl-name">{{ tpl.name }}</span>
          </div>
        </div>
      </div>

      <!-- ═══ SETTINGS ════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'settings'" class="tab-panel settings-tab">
        <div class="settings-scroll">

          <!-- Page Setup -->
          <div class="settings-section">
            <div class="settings-title"><i class="fa-solid fa-file" /> Page Setup</div>
            <div class="form-group">
              <label>Size</label>
              <select :value="settings.page_size" @change="update('page_size', $event.target.value)" class="form-select">
                <option value="A4">A4 (210×297mm)</option>
                <option value="Letter">Letter (8.5×11in)</option>
                <option value="Legal">Legal (8.5×14in)</option>
                <option value="A3">A3 (297×420mm)</option>
                <option value="A5">A5 (148×210mm)</option>
                <option value="custom">Custom</option>
              </select>
            </div>
            <div v-if="settings.page_size === 'custom'" class="form-row">
              <div class="form-group">
                <label>Width (px)</label>
                <input type="number" :value="settings.custom_w" @input="update('custom_w', +$event.target.value)" class="form-input" />
              </div>
              <div class="form-group">
                <label>Height (px)</label>
                <input type="number" :value="settings.custom_h" @input="update('custom_h', +$event.target.value)" class="form-input" />
              </div>
            </div>
            <div class="form-group">
              <label>Orientation</label>
              <div class="toggle-group">
                <button :class="{ active: settings.orientation === 'portrait' }" @click="update('orientation', 'portrait')">
                  <i class="fa-solid fa-mobile-screen" /> Portrait
                </button>
                <button :class="{ active: settings.orientation === 'landscape' }" @click="update('orientation', 'landscape')">
                  <i class="fa-solid fa-mobile-screen fa-rotate-90" /> Landscape
                </button>
              </div>
            </div>
            <div class="form-group">
              <label>Margin: {{ settings.margin || 40 }}px</label>
              <input type="range" :value="settings.margin" @input="update('margin', +$event.target.value)" min="0" max="120" class="form-range" />
            </div>
            <div class="form-group">
              <label>Corner Radius: {{ settings.page_radius || 0 }}px</label>
              <input type="range" :value="settings.page_radius" @input="update('page_radius', +$event.target.value)" min="0" max="40" class="form-range" />
            </div>
          </div>

          <!-- Colors -->
          <div class="settings-section">
            <div class="settings-title"><i class="fa-solid fa-palette" /> Colors</div>
            <div class="form-group" v-for="colorKey in colorKeys" :key="colorKey.key">
              <label>{{ colorKey.label }}</label>
              <div class="color-row">
                <input type="color" :value="settings[colorKey.key]" @input="update(colorKey.key, $event.target.value)" class="color-input" />
                <input type="text" :value="settings[colorKey.key]" @input="update(colorKey.key, $event.target.value)" class="form-input mono" placeholder="#ffffff" />
              </div>
            </div>
            <div class="form-group">
              <label>Background Image URL</label>
              <input type="text" :value="settings.bg_image" @input="update('bg_image', $event.target.value)" class="form-input" placeholder="https://…" />
            </div>
          </div>

          <!-- Typography -->
          <div class="settings-section">
            <div class="settings-title"><i class="fa-solid fa-font" /> Typography</div>
            <div class="form-group">
              <label>Font Family</label>
              <select :value="settings.font_family" @change="update('font_family', $event.target.value)" class="form-select">
                <option v-for="f in fontList" :key="f.value" :value="f.value">{{ f.label }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Base Size: {{ settings.font_size || 14 }}px</label>
              <input type="range" :value="settings.font_size" @input="update('font_size', +$event.target.value)" min="10" max="24" class="form-range" />
            </div>
            <div class="form-group">
              <label>Text Direction</label>
              <div class="toggle-group">
                <button :class="{ active: !settings.rtl }" @click="update('rtl', false)">LTR →</button>
                <button :class="{ active: settings.rtl }" @click="update('rtl', true)">← RTL</button>
              </div>
            </div>
          </div>

          <!-- Header & Footer -->
          <div class="settings-section">
            <div class="settings-title"><i class="fa-solid fa-heading" /> Header & Footer</div>
            <div class="switch-row">
              <span>Show Header</span>
              <button class="toggle-switch" :class="{ on: settings.show_header }" @click="update('show_header', !settings.show_header)">
                <span class="switch-thumb" />
              </button>
            </div>
            <template v-if="settings.show_header">
              <div class="form-group">
                <label>Header Text</label>
                <input type="text" :value="settings.header_text" @input="update('header_text', $event.target.value)" class="form-input" />
              </div>
              <div class="form-group">
                <label>Header Color</label>
                <div class="color-row">
                  <input type="color" :value="settings.header_color" @input="update('header_color', $event.target.value)" class="color-input" />
                  <input type="text" :value="settings.header_color" @input="update('header_color', $event.target.value)" class="form-input mono" />
                </div>
              </div>
              <div class="form-group">
                <label>Height: {{ settings.header_height || 50 }}px</label>
                <input type="range" :value="settings.header_height" @input="update('header_height', +$event.target.value)" min="30" max="120" class="form-range" />
              </div>
            </template>
            <div class="switch-row">
              <span>Show Footer</span>
              <button class="toggle-switch" :class="{ on: settings.show_footer }" @click="update('show_footer', !settings.show_footer)">
                <span class="switch-thumb" />
              </button>
            </div>
            <template v-if="settings.show_footer">
              <div class="form-group">
                <label>Footer Left</label>
                <input type="text" :value="settings.footer_left" @input="update('footer_left', $event.target.value)" class="form-input" placeholder="Company name" />
              </div>
              <div class="form-group">
                <label>Footer Center</label>
                <input type="text" :value="settings.footer_center" @input="update('footer_center', $event.target.value)" class="form-input" placeholder="Confidential" />
              </div>
              <div class="form-group">
                <label>Footer Right</label>
                <input type="text" :value="settings.footer_right" @input="update('footer_right', $event.target.value)" class="form-input" placeholder="Page {n}" />
              </div>
            </template>
            <div class="switch-row">
              <span>Page Numbers</span>
              <button class="toggle-switch" :class="{ on: settings.show_page_numbers }" @click="update('show_page_numbers', !settings.show_page_numbers)">
                <span class="switch-thumb" />
              </button>
            </div>
          </div>

          <!-- Watermark -->
          <div class="settings-section">
            <div class="settings-title"><i class="fa-solid fa-water" /> Watermark</div>
            <div class="form-group">
              <label>Text</label>
              <input type="text" :value="settings.watermark" @input="update('watermark', $event.target.value)" class="form-input" placeholder="DRAFT / CONFIDENTIAL" />
            </div>
            <template v-if="settings.watermark">
              <div class="form-group">
                <label>Opacity: {{ settings.watermark_opacity || 8 }}%</label>
                <input type="range" :value="settings.watermark_opacity" @input="update('watermark_opacity', +$event.target.value)" min="1" max="50" class="form-range" />
              </div>
              <div class="form-group">
                <label>Rotation: {{ settings.watermark_rotate || -30 }}°</label>
                <input type="range" :value="settings.watermark_rotate" @input="update('watermark_rotate', +$event.target.value)" min="-90" max="90" class="form-range" />
              </div>
            </template>
          </div>

          <!-- Grid & Snap -->
          <div class="settings-section">
            <div class="settings-title"><i class="fa-solid fa-border-all" /> Grid & Snap</div>
            <div class="form-group">
              <label>Grid Size</label>
              <select :value="gridSize" @change="$emit('update-grid-size', +$event.target.value)" class="form-select">
                <option :value="5">5px (fine)</option>
                <option :value="10">10px (default)</option>
                <option :value="20">20px (coarse)</option>
                <option :value="40">40px (large)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ VERSIONS ═════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'versions'" class="tab-panel">
        <button class="btn-secondary full-width" @click="loadVersions" :disabled="versionsLoading">
          <i :class="versionsLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-rotate'" /> Refresh History
        </button>
        <div v-if="versions.length" class="versions-list">
          <div v-for="(ver, vi) in versions" :key="ver.id" class="version-item" :class="{ current: vi === 0 }">
            <div class="ver-dot" />
            <div v-if="vi < versions.length - 1" class="ver-line" />
            <div class="ver-body">
              <div class="ver-header">
                <strong>v{{ ver.version_number }}</strong>
                <span v-if="vi === 0" class="current-badge">Current</span>
              </div>
              <p class="ver-label">{{ ver.label || 'Auto-saved' }}</p>
              <span class="ver-date">{{ formatDate(ver.created_at) }}</span>
            </div>
            <button class="btn-secondary btn-sm" @click="restoreVersion(ver.id)" :disabled="vi === 0">Restore</button>
          </div>
        </div>
        <div v-else class="empty-hint">
          <i class="fa-solid fa-clock-rotate-left" /><p>No versions yet</p>
        </div>
      </div>

    </div>
  </aside>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted } from 'vue'

const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
  currentPage: { type: Number, default: 0 },
  selectedElIdx: { type: [Number, null], default: null },
  selectedEls: { type: Array, default: () => [] },
  activeTab: { type: String, default: 'elements' },
  isCollapsed: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
  gridSize: { type: Number, default: 10 },
})

const emit = defineEmits([
  'add-element-center', 'select-page', 'add-page', 'duplicate-page', 'delete-page', 'rename-page',
  'move-page', 'select-element', 'deselect-all', 'toggle-visibility', 'toggle-lock',
  'upload-image', 'apply-template', 'update:settings', 'update:active-tab', 'canvas-drag-start',
  'update:is-collapsed', 'add-element-at', 'update-grid-size',
])

// ── State ─────────────────────────────────────────────────────────────────────
const elSearch = ref('')
const collapsedCats = reactive(new Set())
const renamingPage = ref(null)
const renameInputEl = ref(null)
const pagesListEl = ref(null)
const mediaFileInput = ref(null)
const stockQuery = ref('business')
const stockImages = ref([])
const stockLoading = ref(false)
const uploadedImages = ref([])
const versions = ref([])
const versionsLoading = ref(false)
let pageDragFrom = null

// ── Tabs ──────────────────────────────────────────────────────────────────────
const tabs = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-shapes' },
  { id: 'pages', label: 'Pages', icon: 'fa-solid fa-copy' },
  { id: 'layers', label: 'Layers', icon: 'fa-solid fa-layer-group' },
  { id: 'media', label: 'Media', icon: 'fa-solid fa-image' },
  { id: 'templates', label: 'Themes', icon: 'fa-solid fa-swatchbook' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-sliders' },
  { id: 'versions', label: 'History', icon: 'fa-solid fa-clock-rotate-left' },
]

// ── Quick Elements ─────────────────────────────────────────────────────────────
const quickEls = [
  { type: 'heading', label: 'Heading', icon: 'fa-solid fa-heading', w: 350, h: 60 },
  { type: 'text', label: 'Text', icon: 'fa-solid fa-align-left', w: 250, h: 60 },
  { type: 'image', label: 'Image', icon: 'fa-solid fa-image', w: 300, h: 200 },
  { type: 'table', label: 'Table', icon: 'fa-solid fa-table', w: 460, h: 220 },
  { type: 'bar-chart', label: 'Chart', icon: 'fa-solid fa-chart-bar', w: 400, h: 280 },
  { type: 'metric', label: 'KPI', icon: 'fa-solid fa-chart-simple', w: 200, h: 120 },
]

// ── Element Catalog ────────────────────────────────────────────────────────────
const allCategories = [
  {
    name: 'Typography', icon: '🔤', items: [
      { type: 'text', label: 'Text', icon: 'fa-solid fa-align-left', w: 250, h: 60 },
      { type: 'heading', label: 'Heading', icon: 'fa-solid fa-heading', w: 350, h: 60 },
      { type: 'subheading', label: 'Subheading', icon: 'fa-solid fa-h', w: 280, h: 48 },
      { type: 'quote', label: 'Quote', icon: 'fa-solid fa-quote-right', w: 320, h: 80 },
      { type: 'blockquote', label: 'Blockquote', icon: 'fa-solid fa-quote-left', w: 300, h: 90 },
      { type: 'highlight', label: 'Highlight', icon: 'fa-solid fa-highlighter', w: 250, h: 40 },
      { type: 'badge', label: 'Badge', icon: 'fa-solid fa-tag', w: 120, h: 36 },
      { type: 'code', label: 'Code Block', icon: 'fa-solid fa-code', w: 360, h: 130 },
      { type: 'link', label: 'Link', icon: 'fa-solid fa-link', w: 200, h: 36 },
      { type: 'richtext', label: 'Rich Text', icon: 'fa-solid fa-file-word', w: 400, h: 200, isNew: true },
      { type: 'list', label: 'List', icon: 'fa-solid fa-list-ul', w: 280, h: 160 },
      { type: 'toc', label: 'Table of Contents', icon: 'fa-solid fa-list-ol', w: 360, h: 220, isNew: true },
    ]
  },
  {
    name: 'Data & Charts', icon: '📊', items: [
      { type: 'table', label: 'Table', icon: 'fa-solid fa-table', w: 460, h: 220 },
      { type: 'bar-chart', label: 'Bar Chart', icon: 'fa-solid fa-chart-bar', w: 400, h: 280 },
      { type: 'line-chart', label: 'Line Chart', icon: 'fa-solid fa-chart-line', w: 400, h: 280 },
      { type: 'area-chart', label: 'Area Chart', icon: 'fa-solid fa-chart-area', w: 400, h: 280 },
      { type: 'pie-chart', label: 'Pie Chart', icon: 'fa-solid fa-chart-pie', w: 300, h: 300 },
      { type: 'doughnut-chart', label: 'Doughnut', icon: 'fa-solid fa-circle-half-stroke', w: 300, h: 300 },
      { type: 'radar-chart', label: 'Radar Chart', icon: 'fa-solid fa-compass', w: 320, h: 320, isNew: true },
      { type: 'scatter-chart', label: 'Scatter', icon: 'fa-solid fa-braille', w: 360, h: 280, isNew: true },
      { type: 'polar-chart', label: 'Polar Area', icon: 'fa-solid fa-sun', w: 300, h: 300, isNew: true },
      { type: 'metric', label: 'KPI Card', icon: 'fa-solid fa-chart-simple', w: 200, h: 120 },
      { type: 'stat-row', label: 'Stat Row', icon: 'fa-solid fa-bars-staggered', w: 460, h: 100 },
      { type: 'progress', label: 'Progress Bar', icon: 'fa-solid fa-bars-progress', w: 360, h: 60 },
      { type: 'circular-progress', label: 'Circular Progress', icon: 'fa-solid fa-circle-notch', w: 140, h: 140, isNew: true },
      { type: 'sparkline', label: 'Sparkline', icon: 'fa-solid fa-wave-square', w: 200, h: 48, isNew: true },
    ]
  },
  {
    name: 'Media', icon: '🖼️', items: [
      { type: 'image', label: 'Image', icon: 'fa-solid fa-image', w: 300, h: 200 },
      { type: 'video', label: 'Video (YouTube)', icon: 'fa-solid fa-video', w: 420, h: 260, isNew: true },
      { type: 'map', label: 'Map (Google)', icon: 'fa-solid fa-map-location-dot', w: 400, h: 260, isNew: true },
      { type: 'qr-code', label: 'QR Code', icon: 'fa-solid fa-qrcode', w: 150, h: 150 },
      { type: 'icon', label: 'Emoji / Icon', icon: 'fa-solid fa-face-smile', w: 64, h: 64 },
      { type: 'rating', label: 'Star Rating', icon: 'fa-solid fa-star-half-stroke', w: 160, h: 44 },
    ]
  },
  {
    name: 'Content Blocks', icon: '📋', items: [
      { type: 'checklist', label: 'Checklist', icon: 'fa-solid fa-list-check', w: 300, h: 180 },
      { type: 'timeline', label: 'Timeline', icon: 'fa-solid fa-timeline', w: 420, h: 260 },
      { type: 'callout', label: 'Callout Box', icon: 'fa-solid fa-lightbulb', w: 380, h: 100 },
      { type: 'testimonial', label: 'Testimonial', icon: 'fa-solid fa-comment-dots', w: 360, h: 180 },
      { type: 'signature', label: 'Signature', icon: 'fa-solid fa-signature', w: 240, h: 90 },
      { type: 'price-card', label: 'Price Card', icon: 'fa-solid fa-tags', w: 220, h: 300, isNew: true },
      { type: 'social-card', label: 'Social Card', icon: 'fa-solid fa-id-card', w: 200, h: 200, isNew: true },
      { type: 'kanban', label: 'Kanban Card', icon: 'fa-solid fa-columns', w: 240, h: 130, isNew: true },
      { type: 'html-embed', label: 'HTML Embed', icon: 'fa-solid fa-code', w: 360, h: 200, isNew: true },
    ]
  },
  {
    name: 'Shapes & Layout', icon: '🔷', items: [
      { type: 'rectangle', label: 'Rectangle', icon: 'fa-solid fa-square', w: 200, h: 120 },
      { type: 'circle', label: 'Circle', icon: 'fa-solid fa-circle', w: 120, h: 120 },
      { type: 'triangle', label: 'Triangle', icon: 'fa-solid fa-play', w: 120, h: 100 },
      { type: 'star', label: 'Star', icon: 'fa-solid fa-star', w: 80, h: 80 },
      { type: 'hexagon', label: 'Hexagon', icon: 'fa-solid fa-hexagon', w: 100, h: 90, isNew: true },
      { type: 'divider', label: 'Divider Line', icon: 'fa-solid fa-minus', w: 400, h: 4 },
      { type: 'arrow', label: 'Arrow', icon: 'fa-solid fa-arrow-right', w: 200, h: 40 },
    ]
  },
  {
    name: 'Utilities', icon: '🛠️', items: [
      { type: 'pagenum', label: 'Page Number', icon: 'fa-solid fa-hashtag', w: 60, h: 30 },
      { type: 'date', label: 'Current Date', icon: 'fa-solid fa-calendar', w: 180, h: 30 },
      { type: 'watermark', label: 'Watermark Text', icon: 'fa-solid fa-water', w: 300, h: 120 },
    ]
  },
]

// ── Computed ───────────────────────────────────────────────────────────────────
const filteredCats = computed(() => {
  if (!elSearch.value) return allCategories
  const q = elSearch.value.toLowerCase()
  return allCategories.map(c => ({
    ...c,
    items: c.items.filter(i => i.label.toLowerCase().includes(q) || i.type.toLowerCase().includes(q))
  })).filter(c => c.items.length)
})

const currentPageElements = computed(() => props.report.content[props.currentPage]?.elements || [])
const reversedElements = computed(() => [...currentPageElements.value].reverse())

// ── Settings helpers ────────────────────────────────────────────────────────
const colorKeys = [
  { key: 'primary_color', label: 'Primary Color' },
  { key: 'accent_color', label: 'Accent Color' },
  { key: 'background_color', label: 'Background' },
  { key: 'text_color', label: 'Text Color' },
]

const fontList = [
  { value: "'DM Sans', sans-serif", label: 'DM Sans' },
  { value: "'Inter', sans-serif", label: 'Inter' },
  { value: "'Plus Jakarta Sans', sans-serif", label: 'Plus Jakarta Sans' },
  { value: "'Space Grotesk', sans-serif", label: 'Space Grotesk' },
  { value: "'Sora', sans-serif", label: 'Sora' },
  { value: "'Outfit', sans-serif", label: 'Outfit' },
  { value: "'Nunito', sans-serif", label: 'Nunito' },
  { value: "Georgia, serif", label: 'Georgia' },
  { value: "'Playfair Display', serif", label: 'Playfair Display' },
  { value: "'Times New Roman', serif", label: 'Times New Roman' },
  { value: "'Fira Code', monospace", label: 'Fira Code (Mono)' },
]

const quickTemplates = [
  { name: 'Indigo Pro', gradient: 'linear-gradient(135deg,#0f172a,#1e293b)', primary_color: '#6366f1', background_color: '#0f172a' },
  { name: 'Ocean Blue', gradient: 'linear-gradient(135deg,#1e40af,#3b82f6)', primary_color: '#3b82f6', background_color: '#ffffff' },
  { name: 'Emerald Fresh', gradient: 'linear-gradient(135deg,#065f46,#10b981)', primary_color: '#10b981', background_color: '#ffffff' },
  { name: 'Warm Amber', gradient: 'linear-gradient(135deg,#78350f,#f59e0b)', primary_color: '#f59e0b', background_color: '#ffffff' },
  { name: 'Rose Gold', gradient: 'linear-gradient(135deg,#9f1239,#f43f5e)', primary_color: '#f43f5e', background_color: '#fff5f5' },
  { name: 'Purple Pro', gradient: 'linear-gradient(135deg,#4c1d95,#8b5cf6)', primary_color: '#8b5cf6', background_color: '#ffffff' },
  { name: 'Midnight', gradient: 'linear-gradient(135deg,#0f172a,#334155)', primary_color: '#f59e0b', background_color: '#0f172a' },
  { name: 'Clean White', gradient: 'linear-gradient(135deg,#f8fafc,#e2e8f0)', primary_color: '#6366f1', background_color: '#ffffff' },
]

// ── Methods ────────────────────────────────────────────────────────────────────
function toggle() { emit('update:is-collapsed', !props.isCollapsed) }
function toggleCat(name) { collapsedCats.has(name) ? collapsedCats.delete(name) : collapsedCats.add(name) }
function searchEls() {} // Reactive, filteredCats handles it
function addCenter(def) { emit('add-element-center', def) }

function update(key, val) {
  // Emit isolated settings update - never touches html/body
  emit('update:settings', { ...props.settings, [key]: val })
}

// Page rename
function startRename(pi) {
  renamingPage.value = pi
  nextTick(() => {
    const inputs = document.querySelectorAll('.page-rename-input')
    inputs[inputs.length - 1]?.focus()
    inputs[inputs.length - 1]?.select()
  })
}
function finishRename(pi, val) {
  renamingPage.value = null
  emit('rename-page', pi, val || `Page ${pi + 1}`)
}

// Page drag to reorder
function pageDragStart(e, pi) { pageDragFrom = pi; e.dataTransfer.effectAllowed = 'move' }
function pageDragOver(e, pi) { e.dataTransfer.dropEffect = 'move' }
function pageDrop(e, pi) {
  if (pageDragFrom === null || pageDragFrom === pi) return
  emit('move-page', pageDragFrom, pi); pageDragFrom = null
}

// Layers
function isLayerSelected(reversedIdx) {
  const realIdx = getRealIdx(reversedIdx)
  return props.selectedElIdx === realIdx || props.selectedEls.includes(realIdx)
}
function getRealIdx(reversedIdx) { return currentPageElements.value.length - 1 - reversedIdx }
function selectLayer(reversedIdx) { emit('select-element', getRealIdx(reversedIdx)) }
function getLayerName(el) {
  const t = (el.content || '').toString().replace(/<[^>]*>/g, '').trim()
  return (t || el.type || 'Untitled').substring(0, 28)
}
function getElIcon(type) {
  const found = allCategories.flatMap(c => c.items).find(i => i.type === type)
  return found?.icon || 'fa-solid fa-cube'
}
function getTypeColor(type) {
  if (type.includes('chart')) return '#06b6d4'
  if (type === 'table') return '#10b981'
  if (type === 'image') return '#ec4899'
  if (type === 'metric') return '#f59e0b'
  if (type === 'heading' || type === 'subheading') return '#8b5cf6'
  if (type === 'text') return '#6366f1'
  return '#94a3b8'
}

// Page mini preview
function getMiniElStyle(el) {
  const scale = 0.12
  return {
    position: 'absolute',
    left: (el.position?.x || 0) * scale + 'px',
    top: (el.position?.y || 0) * scale + 'px',
    width: Math.max(2, (el.styles?.width || 100) * scale) + 'px',
    height: Math.max(1, (el.styles?.height || 50) * scale) + 'px',
    background: el.styles?.backgroundColor || props.settings.primary_color || '#6366f1',
    opacity: 0.65, borderRadius: '1px',
  }
}

// Media
function triggerUpload() { mediaFileInput.value?.click() }
function onMediaDrop(e) {
  const files = e.dataTransfer.files
  if (!files) return
  Array.from(files).forEach(file => {
    if (!file.type.startsWith('image/')) return
    readFile(file)
  })
}
function onMediaFileInput(e) {
  Array.from(e.target.files).forEach(readFile)
  e.target.value = ''
}
function readFile(file) {
  const r = new FileReader()
  r.onload = (ev) => { uploadedImages.value.push({ url: ev.target.result, name: file.name }) }
  r.readAsDataURL(file)
}
function removeUploaded(img) { uploadedImages.value = uploadedImages.value.filter(i => i.url !== img.url) }
function addUploadedImage(img) { emit('add-element-center', { type: 'image', w: 300, h: 200, src: img.url }) }
function addStockImage(img) { emit('add-element-center', { type: 'image', w: 300, h: 200, src: img.url }) }

async function searchStock() {
  stockLoading.value = true
  try {
    const res = await fetch(`/api/unsplash/search?q=${encodeURIComponent(stockQuery.value)}`, { headers: { Accept: 'application/json' } })
    if (res.ok) {
      const data = await res.json()
      stockImages.value = data.images || []
    }
  } catch (_) {
    // Fallback to picsum
    stockImages.value = Array.from({ length: 12 }, (_, i) => ({
      id: i, thumb: `https://picsum.photos/160/120?random=${i + Date.now() % 100}`,
      url: `https://picsum.photos/800/600?random=${i + Date.now() % 100}`, author: 'Free Stock',
    }))
  }
  stockLoading.value = false
}

// Versions
async function loadVersions() {
  versionsLoading.value = true
  try {
    const res = await fetch(route('reports.versions', props.report.slug), { headers: { Accept: 'application/json' } })
    if (res.ok) { const data = await res.json(); versions.value = data.versions || [] }
  } catch (_) {}
  versionsLoading.value = false
}

async function restoreVersion(id) {
  if (!confirm('Restore this version? Current unsaved changes will be lost.')) return
  try {
    await fetch(route('reports.versions.restore', { report: props.report.slug, version: id }), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', Accept: 'application/json' },
    })
    window.location.reload()
  } catch (_) {}
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function getCsrf() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }

onMounted(() => { searchStock() })
</script>

<style scoped>
.left-panel {
  width: 264px; flex-shrink: 0; background: var(--bg-panel, #fff);
  border-right: 1px solid var(--border, #e2e8f0); display: flex; flex-direction: column;
  overflow: hidden; transition: width 0.25s ease; position: relative; z-index: 50;
}
.left-panel.collapsed { width: 0; border-right: none; }
.panel-toggle {
  position: absolute; right: -14px; top: 50%; transform: translateY(-50%);
  width: 28px; height: 28px; border-radius: 50%; background: var(--bg-panel);
  border: 1px solid var(--border); cursor: pointer; color: var(--text-muted);
  font-size: 10px; display: flex; align-items: center; justify-content: center;
  box-shadow: var(--shadow-sm); z-index: 10; transition: all 0.15s;
}
.panel-toggle:hover { color: var(--accent); border-color: var(--accent); }

.panel-tabs {
  display: flex; gap: 1px; padding: 5px 5px 0; border-bottom: 1px solid var(--border);
  flex-shrink: 0; overflow-x: auto; scrollbar-width: none;
}
.panel-tabs::-webkit-scrollbar { display: none; }
.ptab {
  display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1;
  padding: 6px 2px; border: none; background: transparent; cursor: pointer;
  color: var(--text-muted); font-size: 8px; font-weight: 700; letter-spacing: .03em;
  border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all 0.15s;
  min-width: 0; white-space: nowrap;
}
.ptab i { font-size: 13px; }
.ptab:hover { color: var(--text-secondary); background: var(--bg-secondary); }
.ptab.active { color: var(--accent); border-bottom-color: var(--accent); }
.ptab-label { overflow: hidden; text-overflow: ellipsis; max-width: 36px; }

.panel-content { flex: 1; overflow: hidden; display: flex; flex-direction: column; }
.tab-panel { flex: 1; overflow-y: auto; padding: 8px; display: flex; flex-direction: column; gap: 6px; }
.settings-tab { padding: 0; }

/* Search */
.search-wrap { display: flex; align-items: center; gap: 4px; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 5px 8px; }
.search-wrap:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-light); }
.search-icon { color: var(--text-muted); font-size: 11px; flex-shrink: 0; }
.search-input { flex: 1; border: none; background: transparent; outline: none; font-size: 11px; color: var(--text-primary); font-family: inherit; }
.search-input::placeholder { color: var(--text-muted); }
.search-clear { background: transparent; border: none; cursor: pointer; color: var(--text-muted); font-size: 11px; padding: 0; }

/* Quick chips */
.quick-chips { display: flex; gap: 4px; flex-wrap: wrap; }
.qchip {
  display: flex; align-items: center; gap: 4px; padding: 4px 8px;
  border: 1px solid var(--border); border-radius: 99px; background: var(--bg-secondary);
  cursor: pointer; font-size: 10px; font-weight: 600; color: var(--text-secondary);
  transition: all 0.15s; font-family: inherit; white-space: nowrap;
}
.qchip:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); transform: translateY(-1px); }
.qchip i { font-size: 10px; }

/* Element categories */
.el-scroll { flex: 1; overflow-y: auto; }
.el-cat { margin-bottom: 4px; }
.cat-header {
  display: flex; align-items: center; width: 100%; padding: 5px 6px;
  border: none; background: transparent; cursor: pointer; font-size: 10px; font-weight: 700;
  color: var(--text-muted); letter-spacing: .05em; text-transform: uppercase; border-radius: 5px;
  transition: background 0.1s; font-family: inherit;
}
.cat-header:hover { background: var(--bg-secondary); }
.cat-badge { margin-left: auto; font-size: 9px; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 99px; padding: 0 5px; color: var(--text-muted); margin-right: 4px; }
.cat-chevron { font-size: 8px; opacity: 0.5; }
.el-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; padding: 3px 0 6px; }
.el-card {
  display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 8px 3px;
  border-radius: 8px; cursor: grab; border: 1px solid transparent;
  transition: all 0.15s; position: relative;
}
.el-card:hover { background: var(--accent-light); border-color: rgba(99,102,241,.2); transform: translateY(-2px); box-shadow: 0 3px 10px rgba(0,0,0,.06); }
.el-card:active { cursor: grabbing; transform: scale(.95); }
.el-icon { font-size: 16px; color: var(--text-secondary); width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: var(--bg-secondary); border-radius: 8px; }
.el-card:hover .el-icon { color: var(--accent); background: rgba(99,102,241,.1); }
.el-name { font-size: 9px; font-weight: 600; color: var(--text-muted); line-height: 1.2; text-align: center; }
.el-card:hover .el-name { color: var(--text-primary); }
.el-badge { position: absolute; top: -3px; right: -3px; font-size: 7px; font-weight: 800; padding: 1px 4px; border-radius: 99px; }
.el-badge.new { background: var(--accent); color: #fff; }
.el-badge.pro { background: #f59e0b; color: #fff; }

/* Pages */
.btn-add-page {
  display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%;
  padding: 8px; border: 1.5px dashed var(--border); background: transparent; border-radius: 8px;
  cursor: pointer; color: var(--text-muted); font-size: 12px; font-weight: 500;
  transition: all 0.2s; font-family: inherit; flex-shrink: 0;
}
.btn-add-page:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }

.pages-list { display: flex; flex-direction: column; gap: 6px; overflow-y: auto; }
.page-card {
  border: 2px solid var(--border); border-radius: 10px; overflow: hidden; cursor: pointer;
  transition: all 0.2s; background: var(--bg-primary); position: relative;
}
.page-card:hover { border-color: var(--border-hover); box-shadow: var(--shadow); transform: translateY(-1px); }
.page-card.active { border-color: var(--accent) !important; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }

.page-thumb { padding: 6px; background: var(--bg-secondary); aspect-ratio: 3/4; position: relative; overflow: hidden; }
.thumb-inner { position: relative; width: 100%; height: 100%; }
.thumb-el { position: absolute; border-radius: 1px; }
.thumb-empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 14px; opacity: 0.3; }

.page-info { display: flex; align-items: center; justify-content: space-between; padding: 5px 8px; }
.page-name-wrap { font-size: 10px; font-weight: 600; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
.page-rename-input { font-size: 10px; border: 1px solid var(--accent); border-radius: 4px; padding: 1px 4px; outline: none; background: var(--bg-panel); color: var(--text-primary); width: 100%; font-family: inherit; }
.page-el-count { font-size: 9px; color: var(--text-muted); background: var(--bg-secondary); border-radius: 99px; padding: 1px 6px; flex-shrink: 0; }

.page-actions { display: flex; gap: 2px; padding: 0 6px 6px; }
.page-actions button { flex: 1; padding: 3px; border: 1px solid var(--border); border-radius: 5px; background: transparent; cursor: pointer; color: var(--text-muted); font-size: 10px; transition: all 0.15s; }
.page-actions button:hover { background: var(--bg-secondary); color: var(--text-primary); }
.page-actions button.danger:hover { background: var(--danger-light); color: var(--danger); }
.page-actions button:disabled { opacity: 0.3; cursor: not-allowed; }
.page-active-glow { position: absolute; inset: -2px; border-radius: 10px; border: 2px solid var(--accent); pointer-events: none; animation: pageGlow 2.5s ease-in-out infinite; box-shadow: 0 0 16px rgba(99,102,241,.25); }
@keyframes pageGlow { 0%,100%{opacity:0.6} 50%{opacity:1} }

/* Layers */
.layers-header { display: flex; align-items: center; justify-content: space-between; font-size: 11px; font-weight: 600; color: var(--text-secondary); padding: 2px 0 6px; }
.layer-count { font-size: 9px; color: var(--text-muted); background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 99px; padding: 0 6px; margin-left: 4px; }
.micro-btn { width: 22px; height: 22px; border: none; background: transparent; cursor: pointer; color: var(--text-muted); border-radius: 4px; font-size: 11px; display: flex; align-items: center; justify-content: center; transition: all 0.12s; }
.micro-btn:hover { background: var(--bg-secondary); color: var(--text-primary); }

.layers-list { display: flex; flex-direction: column; gap: 1px; overflow-y: auto; }
.layer-item { display: flex; align-items: center; gap: 5px; padding: 6px 5px; border-radius: 6px; cursor: pointer; border: 1px solid transparent; transition: all 0.12s; }
.layer-item:hover { background: var(--bg-secondary); border-color: var(--border); }
.layer-item.selected { background: var(--accent-light); border-color: var(--accent); }
.layer-item.locked { opacity: 0.5; }
.layer-item.hidden { opacity: 0.35; }
.layer-item.grouped { border-left: 3px solid #10b981; }
.drag-handle { color: var(--text-muted); font-size: 10px; cursor: grab; opacity: 0.4; flex-shrink: 0; }
.layer-type-icon { font-size: 12px; width: 20px; text-align: center; flex-shrink: 0; }
.layer-info { flex: 1; min-width: 0; }
.layer-name { display: block; font-size: 10px; font-weight: 500; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.layer-type { display: block; font-size: 8px; color: var(--text-muted); text-transform: capitalize; }
.layer-ctrls { display: flex; gap: 1px; opacity: 0; transition: opacity 0.12s; }
.layer-item:hover .layer-ctrls { opacity: 1; }
.layer-ctrls button { width: 20px; height: 20px; border: none; background: transparent; cursor: pointer; color: var(--text-muted); border-radius: 3px; font-size: 9px; display: flex; align-items: center; justify-content: center; }
.layer-ctrls button:hover { background: var(--bg-secondary); color: var(--text-primary); }

/* Media */
.upload-zone {
  display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 20px 12px;
  border: 2px dashed var(--border); border-radius: 10px; cursor: pointer;
  color: var(--text-muted); font-size: 12px; transition: all 0.2s;
}
.upload-zone:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
.upload-zone i { font-size: 24px; opacity: 0.5; }

.media-section { display: flex; flex-direction: column; gap: 6px; }
.media-section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
.stock-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; }
.stock-item { border-radius: 5px; overflow: hidden; cursor: pointer; aspect-ratio: 4/3; border: 1px solid var(--border); position: relative; transition: all 0.15s; }
.stock-item:hover { transform: scale(1.04); border-color: var(--accent); }
.stock-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.stock-overlay { position: absolute; inset: 0; background: rgba(99,102,241,.5); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; opacity: 0; transition: opacity 0.2s; }
.stock-item:hover .stock-overlay { opacity: 1; }

.uploaded-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px; }
.uploaded-item { border-radius: 6px; overflow: hidden; cursor: pointer; border: 1px solid var(--border); position: relative; aspect-ratio: 1; transition: all 0.15s; }
.uploaded-item:hover { border-color: var(--accent); }
.uploaded-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.uploaded-name { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.6); color: #fff; font-size: 8px; padding: 2px 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.remove-img { position: absolute; top: 2px; right: 2px; width: 18px; height: 18px; border-radius: 50%; background: rgba(0,0,0,.5); border: none; color: #fff; font-size: 8px; cursor: pointer; opacity: 0; transition: opacity 0.15s; display: flex; align-items: center; justify-content: center; }
.uploaded-item:hover .remove-img { opacity: 1; }

/* Templates */
.tab-desc { font-size: 10px; color: var(--text-muted); }
.template-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; }
.tpl-card { border-radius: 8px; overflow: hidden; cursor: pointer; border: 1px solid var(--border); transition: all 0.2s; }
.tpl-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); border-color: var(--accent); }
.tpl-preview { height: 44px; position: relative; }
.tpl-hover { position: absolute; inset: 0; background: rgba(0,0,0,.3); display: flex; align-items: center; justify-content: center; color: #fff; opacity: 0; transition: opacity 0.2s; font-size: 14px; }
.tpl-card:hover .tpl-hover { opacity: 1; }
.tpl-name { display: block; font-size: 9px; font-weight: 600; padding: 4px 6px; text-align: center; color: var(--text-secondary); }

/* Settings */
.settings-scroll { display: flex; flex-direction: column; gap: 0; overflow-y: auto; flex: 1; }
.settings-section { padding: 10px 10px 6px; border-bottom: 1px solid var(--border-light, #f1f5f9); }
.settings-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); margin-bottom: 8px; display: flex; align-items: center; gap: 5px; }
.form-group { margin-bottom: 8px; }
.form-group label { display: block; font-size: 9px; font-weight: 600; color: var(--text-secondary); margin-bottom: 3px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.form-input { width: 100%; padding: 5px 7px; border: 1px solid var(--border); border-radius: 5px; background: var(--bg-secondary); color: var(--text-primary); font-size: 10px; outline: none; box-sizing: border-box; font-family: inherit; }
.form-input:focus { border-color: var(--accent); }
.form-input.mono { font-family: 'Courier New', monospace; }
.form-select { width: 100%; padding: 5px 7px; border: 1px solid var(--border); border-radius: 5px; background: var(--bg-secondary); color: var(--text-primary); font-size: 10px; outline: none; cursor: pointer; font-family: inherit; }
.form-range { width: 100%; accent-color: var(--accent); cursor: pointer; height: 4px; }
.color-row { display: flex; gap: 5px; align-items: center; }
.color-input { width: 28px; height: 28px; border: 1px solid var(--border); border-radius: 5px; cursor: pointer; padding: 1px; background: transparent; }
.toggle-group { display: flex; gap: 2px; }
.toggle-group button { flex: 1; padding: 5px 6px; border: 1px solid var(--border); border-radius: 5px; background: var(--bg-secondary); cursor: pointer; font-size: 9px; font-weight: 600; color: var(--text-muted); transition: all 0.15s; font-family: inherit; }
.toggle-group button.active { background: var(--accent-light); color: var(--accent); border-color: var(--accent); }
.switch-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; font-size: 10px; font-weight: 600; color: var(--text-secondary); }
.toggle-switch { width: 36px; height: 20px; border-radius: 99px; border: none; background: var(--border); cursor: pointer; position: relative; transition: background 0.2s; }
.toggle-switch.on { background: var(--accent); }
.switch-thumb { position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.toggle-switch.on .switch-thumb { transform: translateX(16px); }

/* Versions */
.btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 10px; border: 1px solid var(--border); background: var(--bg-secondary); color: var(--text-primary); border-radius: 6px; cursor: pointer; font-size: 10px; font-weight: 500; transition: all 0.15s; font-family: inherit; }
.btn-secondary:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
.btn-secondary:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-secondary.full-width { width: 100%; }
.btn-secondary.btn-sm { padding: 3px 8px; font-size: 9px; }
.versions-list { display: flex; flex-direction: column; gap: 2px; }
.version-item { display: flex; align-items: flex-start; gap: 8px; padding: 8px 6px; border-radius: 6px; border: 1px solid transparent; transition: all 0.15s; position: relative; }
.version-item:hover { background: var(--bg-secondary); border-color: var(--border); }
.version-item.current { background: var(--accent-light); border-color: var(--accent); }
.ver-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); flex-shrink: 0; margin-top: 3px; }
.ver-line { position: absolute; left: 17px; top: 20px; bottom: -8px; width: 2px; background: var(--border); }
.ver-body { flex: 1; min-width: 0; }
.ver-header { display: flex; align-items: center; gap: 6px; margin-bottom: 2px; }
.ver-header strong { font-size: 11px; color: var(--text-primary); }
.current-badge { font-size: 8px; font-weight: 700; background: var(--accent); color: #fff; padding: 1px 5px; border-radius: 99px; }
.ver-label { font-size: 10px; color: var(--text-secondary); }
.ver-date { font-size: 9px; color: var(--text-muted); }

/* Empty */
.empty-hint { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 24px; color: var(--text-muted); text-align: center; }
.empty-hint i { font-size: 28px; opacity: 0.25; }
.empty-hint p { font-size: 11px; font-weight: 500; }

.hidden { display: none !important; }

@media (max-width: 768px) {
  .left-panel { position: fixed; left: 0; top: 48px; bottom: 0; z-index: 150; box-shadow: 8px 0 32px rgba(0,0,0,.15); }
  .left-panel.collapsed { width: 0; box-shadow: none; }
}
</style>