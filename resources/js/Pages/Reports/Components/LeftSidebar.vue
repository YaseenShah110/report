<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   LeftSidebar - Elements, Pages, Layers, Media, Templates,     ║
  ║   Settings, Versions Tabs                                       ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <aside class="left-panel" :class="{ collapsed: isCollapsed }">
    <!-- Collapse Toggle -->
    <button class="panel-toggle" @click="isCollapsed = !isCollapsed" :title="isCollapsed ? 'Expand' : 'Collapse'">
      <i :class="isCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"></i>
    </button>

    <!-- Tab Navigation -->
    <nav class="panel-tabs" v-show="!isCollapsed">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        class="panel-tab"
        :class="{ active: activeTab === tab.id }"
        @click="$emit('update:active-tab', tab.id)"
        :title="tab.label"
      >
        <i :class="tab.icon"></i>
        <span class="tab-label">{{ tab.label }}</span>
        <span v-if="tab.badge" class="tab-badge">{{ tab.badge }}</span>
      </button>
    </nav>

    <!-- Tab Content -->
    <div class="panel-content" v-show="!isCollapsed">
      
      <!-- ═══ ELEMENTS TAB ═══════════════════════════════════════ -->
      <div v-show="activeTab === 'elements'" class="tab-panel">
        <div class="search-wrap">
          <i class="fa-solid fa-search search-icon"></i>
          <input
            v-model="elSearch"
            class="search-input"
            placeholder="Search 40+ elements..."
          />
        </div>

        <!-- Quick Presets -->
        <div class="presets-row">
          <button
            v-for="preset in quickPresets"
            :key="preset.type"
            class="preset-chip"
            @click="addElementToPage(preset)"
            :title="preset.label"
          >
            <i :class="preset.icon"></i>
            {{ preset.label }}
          </button>
        </div>

        <div class="elements-scroll">
          <div v-for="cat in filteredCategories" :key="cat.name" class="el-category">
            <button class="cat-header" @click="toggleCategory(cat.name)">
              <span>{{ cat.name }}</span>
              <span class="cat-count">{{ cat.items.length }}</span>
              <i :class="collapsedCats.includes(cat.name) ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'"></i>
            </button>
            
            <div v-if="!collapsedCats.includes(cat.name)" class="el-grid">
              <div
                v-for="el in cat.items"
                :key="el.type"
                class="el-card group"
                :class="{ 'el-new': el.isNew }"
                draggable="true"
                @dragstart="$emit('canvas-drag-start', $event, el)"
                @dblclick="addElementToPage(el)"
                :title="el.description || el.label"
              >
                <div class="el-preview">
                  <i :class="el.icon"></i>
                </div>
                <span class="el-name">{{ el.label }}</span>
                <span v-if="el.isNew" class="new-badge">NEW</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ PAGES TAB ═══════════════════════════════════════════ -->
      <div v-show="activeTab === 'pages'" class="tab-panel">
        <div class="pages-header">
          <button class="add-page-btn" @click="$emit('add-page')">
            <i class="fa-solid fa-plus"></i>
            <span>Add Page</span>
          </button>
          <div class="pages-info">
            {{ report.content.length }} page{{ report.content.length !== 1 ? 's' : '' }}
          </div>
        </div>

        <div class="pages-list" ref="pagesList">
          <div
            v-for="(page, pi) in report.content"
            :key="page.id"
            class="page-card"
            :class="{ active: currentPage === pi }"
            @click="$emit('select-page', pi)"
            @dblclick="startRename(pi)"
            draggable="true"
          >
            <!-- Page Preview -->
            <div class="page-preview">
              <div class="page-mini" :style="getMiniPageStyle()">
                <div
                  v-for="(el, ei) in page.elements.slice(0, 6)"
                  :key="ei"
                  class="mini-el"
                  :style="getMiniElStyle(el)"
                ></div>
                <div v-if="!page.elements.length" class="mini-empty">
                  <i class="fa-solid fa-plus"></i>
                </div>
              </div>
            </div>

            <!-- Page Info -->
            <div class="page-info">
              <div class="page-name">
                <span v-if="renamingPage !== pi">{{ page.label || `Page ${pi + 1}` }}</span>
                <input
                  v-else
                  ref="renameInput"
                  :value="page.label || `Page ${pi + 1}`"
                  @blur="finishRename(pi, $event.target.value)"
                  @keydown.enter="finishRename(pi, $event.target.value)"
                  @click.stop
                  class="page-rename-input"
                />
              </div>
              <span class="page-el-count">{{ page.elements.length }} el</span>
            </div>

            <!-- Page Actions -->
            <div class="page-actions">
              <button @click.stop="$emit('duplicate-page', pi)" title="Duplicate Page">
                <i class="fa-solid fa-copy"></i>
              </button>
              <button
                @click.stop="$emit('delete-page', pi)"
                :disabled="report.content.length <= 1"
                class="danger"
                title="Delete Page"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>

            <!-- Active Page Indicator -->
            <div v-if="currentPage === pi" class="active-glow"></div>
          </div>
        </div>
      </div>

      <!-- ═══ LAYERS TAB ═══════════════════════════════════════════ -->
      <div v-show="activeTab === 'layers'" class="tab-panel">
        <div class="layers-header">
          <span>Layers</span>
          <div class="layers-actions">
            <button class="micro-btn" @click="$emit('deselect-all')" title="Deselect All">
              <i class="fa-solid fa-xmark"></i>
            </button>
            <span class="layer-count">{{ currentPageElements.length }}</span>
          </div>
        </div>

        <div class="layers-list">
          <div
            v-for="(el, ei) in reversedElements"
            :key="el.id"
            class="layer-item"
            :class="{
              'layer-selected': isElementSelected(ei),
              'layer-locked': el.locked,
              'layer-hidden': el.visible === false,
              'layer-grouped': el.groupId
            }"
            @click="selectLayerElement(ei)"
          >
            <!-- Drag Handle -->
            <span class="layer-drag-handle">
              <i class="fa-solid fa-grip-vertical"></i>
            </span>

            <!-- Element Type Icon -->
            <span class="layer-type-icon" :style="{ color: getElColor(el.type) }">
              <i :class="getElIcon(el.type)"></i>
            </span>

            <!-- Element Name -->
            <span class="layer-name">
              {{ getLayerName(el) }}
              <small class="layer-type-label">{{ el.type }}</small>
            </span>

            <!-- Layer Controls -->
            <div class="layer-controls">
              <button
                @click.stop="$emit('toggle-visibility', currentPageElements.length - 1 - ei)"
                :title="el.visible === false ? 'Show' : 'Hide'"
              >
                <i :class="el.visible === false ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
              </button>
              <button
                @click.stop="$emit('toggle-lock', currentPageElements.length - 1 - ei)"
                :title="el.locked ? 'Unlock' : 'Lock'"
              >
                <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'"></i>
              </button>
            </div>
          </div>

          <div v-if="!currentPageElements.length" class="empty-state">
            <i class="fa-solid fa-layer-group"></i>
            <p>No elements on this page</p>
            <small>Drag elements from the Elements tab</small>
          </div>
        </div>
      </div>

      <!-- ═══ MEDIA TAB ═══════════════════════════════════════════ -->
      <div v-show="activeTab === 'media'" class="tab-panel">
        <!-- Upload Zone -->
        <div class="upload-zone" @click="triggerUpload" @dragover.prevent @drop.prevent="handleDrop">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <span>Upload Images</span>
          <small>or drag & drop files here</small>
          <small class="upload-formats">PNG, JPG, GIF, WebP, SVG (max 5MB)</small>
        </div>

        <!-- Unsplash Search -->
        <div class="unsplash-section">
          <div class="section-title">
            <i class="fa-solid fa-images"></i>
            Stock Photos
          </div>
          <div class="search-wrap">
            <input
              v-model="unsplashQuery"
              class="search-input"
              placeholder="Search free photos..."
              @keydown.enter="searchUnsplash"
            />
            <button class="micro-btn" @click="searchUnsplash" :disabled="unsplashLoading">
              <i :class="unsplashLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-search'"></i>
            </button>
          </div>
          <div class="unsplash-grid" v-if="unsplashImages.length">
            <div
              v-for="img in unsplashImages"
              :key="img.id"
              class="unsplash-item"
              @click="addStockImage(img)"
              :title="'Click to add'"
            >
              <img :src="img.thumb" :alt="img.author" loading="lazy" />
            </div>
          </div>
        </div>

        <!-- Uploaded Images -->
        <div class="uploaded-section" v-if="uploadedImages.length">
          <div class="section-title">
            <i class="fa-solid fa-folder-open"></i>
            Uploaded
          </div>
          <div class="uploaded-grid">
            <div
              v-for="img in uploadedImages"
              :key="img.url"
              class="uploaded-item"
              draggable="true"
              @dragstart="onMediaDrag($event, img)"
              @click="addImageToCanvas(img)"
            >
              <img :src="img.url" :alt="img.name" loading="lazy" />
              <span class="img-name">{{ img.name }}</span>
              <button class="remove-img" @click.stop="removeUploaded(img)" title="Remove">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
        </div>

        <input
          ref="mediaInput"
          type="file"
          accept="image/*"
          multiple
          class="hidden"
          @change="handleFileInput"
        />
      </div>

      <!-- ═══ TEMPLATES TAB ═══════════════════════════════════════ -->
      <div v-show="activeTab === 'templates'" class="tab-panel">
        <p class="tab-description">Apply a template style to the current page</p>
        
        <div class="section-title">Quick Templates</div>
        <div class="template-grid">
          <div
            v-for="tpl in quickTemplates"
            :key="tpl.name"
            class="template-card"
            @click="$emit('apply-template', tpl)"
            :title="tpl.name"
          >
            <div class="tpl-preview" :style="{ background: tpl.gradient }">
              <div class="tpl-overlay">
                <i class="fa-solid fa-check"></i>
                <span>Apply</span>
              </div>
            </div>
            <span class="tpl-name">{{ tpl.name }}</span>
          </div>
        </div>

        <div class="section-title">Saved Presets</div>
        <div class="presets-list" v-if="savedPresets.length">
          <div
            v-for="preset in savedPresets"
            :key="preset.name"
            class="preset-item"
            @click="applyPreset(preset)"
          >
            <span class="preset-color" :style="{ background: preset.styles?.color || '#6366f1' }"></span>
            <span>{{ preset.name }}</span>
          </div>
        </div>
        <div v-else class="empty-state small">
          <p>Save element styles as presets</p>
          <small>Right-click an element → Save as Preset</small>
        </div>
      </div>

      <!-- ═══ SETTINGS TAB ═══════════════════════════════════════ -->
      <div v-show="activeTab === 'settings'" class="tab-panel">
        <div class="settings-scroll">
          
          <!-- Page Setup -->
          <div class="settings-section">
            <div class="section-title">
              <i class="fa-solid fa-file"></i> Page Setup
            </div>
            
            <div class="form-group">
              <label>Page Size</label>
              <select v-model="localSettings.page_size" @change="emitSettings" class="form-select">
                <option value="A4">A4 (210 × 297mm)</option>
                <option value="Letter">Letter (216 × 279mm)</option>
                <option value="Legal">Legal (216 × 356mm)</option>
                <option value="A3">A3 (297 × 420mm)</option>
                <option value="A5">A5 (148 × 210mm)</option>
                <option value="custom">Custom</option>
              </select>
            </div>

            <div v-if="localSettings.page_size === 'custom'" class="form-row">
              <div class="form-group">
                <label>Width (px)</label>
                <input type="number" v-model.number="localSettings.custom_w" @change="emitSettings" class="form-input" />
              </div>
              <div class="form-group">
                <label>Height (px)</label>
                <input type="number" v-model.number="localSettings.custom_h" @change="emitSettings" class="form-input" />
              </div>
            </div>

            <div class="form-group">
              <label>Orientation</label>
              <div class="toggle-group">
                <button :class="{ active: localSettings.orientation === 'portrait' }" @click="localSettings.orientation = 'portrait'; emitSettings()">
                  <i class="fa-solid fa-phone"></i> Portrait
                </button>
                <button :class="{ active: localSettings.orientation === 'landscape' }" @click="localSettings.orientation = 'landscape'; emitSettings()">
                  <i class="fa-solid fa-phone fa-rotate-90"></i> Landscape
                </button>
              </div>
            </div>

            <div class="form-group">
              <label>Margin: {{ localSettings.margin || 40 }}px</label>
              <input type="range" v-model.number="localSettings.margin" min="0" max="120" @input="emitSettings" class="form-range" />
            </div>

            <div class="form-group">
              <label>Corner Radius: {{ localSettings.page_radius || 0 }}px</label>
              <input type="range" v-model.number="localSettings.page_radius" min="0" max="40" @input="emitSettings" class="form-range" />
            </div>
          </div>

          <!-- Colors -->
          <div class="settings-section">
            <div class="section-title">
              <i class="fa-solid fa-palette"></i> Colors
            </div>

            <div class="form-group">
              <label>Primary Color</label>
              <div class="color-input-row">
                <input type="color" v-model="localSettings.primary_color" @input="emitSettings" class="color-picker" />
                <input type="text" v-model="localSettings.primary_color" @input="emitSettings" class="form-input mono" />
              </div>
            </div>

            <div class="form-group">
              <label>Accent Color</label>
              <div class="color-input-row">
                <input type="color" v-model="localSettings.accent_color" @input="emitSettings" class="color-picker" />
                <input type="text" v-model="localSettings.accent_color" @input="emitSettings" class="form-input mono" />
              </div>
            </div>

            <div class="form-group">
              <label>Background Color</label>
              <div class="color-input-row">
                <input type="color" v-model="localSettings.background_color" @input="emitSettings" class="color-picker" />
                <input type="text" v-model="localSettings.background_color" @input="emitSettings" class="form-input mono" />
              </div>
            </div>

            <div class="form-group">
              <label>Text Color</label>
              <div class="color-input-row">
                <input type="color" v-model="localSettings.text_color" @input="emitSettings" class="color-picker" />
                <input type="text" v-model="localSettings.text_color" @input="emitSettings" class="form-input mono" />
              </div>
            </div>

            <div class="form-group">
              <label>Background Image URL</label>
              <input type="text" v-model="localSettings.bg_image" @input="emitSettings" class="form-input" placeholder="https://..." />
            </div>
          </div>

          <!-- Typography -->
          <div class="settings-section">
            <div class="section-title">
              <i class="fa-solid fa-font"></i> Typography
            </div>

            <div class="form-group">
              <label>Font Family</label>
              <select v-model="localSettings.font_family" @change="emitSettings" class="form-select">
                <option v-for="f in fontList" :key="f" :value="f">{{ f }}</option>
              </select>
            </div>

            <div class="form-group">
              <label>Base Font Size: {{ localSettings.font_size || 14 }}px</label>
              <input type="range" v-model.number="localSettings.font_size" min="10" max="24" @input="emitSettings" class="form-range" />
            </div>

            <div class="form-group">
              <label>Text Direction</label>
              <div class="toggle-group">
                <button :class="{ active: !localSettings.rtl }" @click="localSettings.rtl = false; emitSettings()">LTR</button>
                <button :class="{ active: localSettings.rtl }" @click="localSettings.rtl = true; emitSettings()">RTL</button>
              </div>
            </div>
          </div>

          <!-- Header & Footer -->
          <div class="settings-section">
            <div class="section-title">
              <i class="fa-solid fa-heading"></i> Header & Footer
            </div>

            <div class="switch-row">
              <span>Show Header</span>
              <button class="toggle-switch" :class="{ active: localSettings.show_header }" @click="localSettings.show_header = !localSettings.show_header; emitSettings()">
                <span class="switch-thumb"></span>
              </button>
            </div>

            <div v-if="localSettings.show_header" class="form-group">
              <label>Header Text</label>
              <input type="text" v-model="localSettings.header_text" @input="emitSettings" class="form-input" placeholder="Company Name" />
            </div>

            <div v-if="localSettings.show_header" class="form-group">
              <label>Header Color</label>
              <div class="color-input-row">
                <input type="color" v-model="localSettings.header_color" @input="emitSettings" class="color-picker" />
                <input type="text" v-model="localSettings.header_color" @input="emitSettings" class="form-input mono" />
              </div>
            </div>

            <div class="switch-row">
              <span>Show Footer</span>
              <button class="toggle-switch" :class="{ active: localSettings.show_footer }" @click="localSettings.show_footer = !localSettings.show_footer; emitSettings()">
                <span class="switch-thumb"></span>
              </button>
            </div>

            <div v-if="localSettings.show_footer" class="form-group">
              <label>Footer Left</label>
              <input type="text" v-model="localSettings.footer_left" @input="emitSettings" class="form-input" />
            </div>

            <div v-if="localSettings.show_footer" class="form-group">
              <label>Footer Right</label>
              <input type="text" v-model="localSettings.footer_right" @input="emitSettings" class="form-input" placeholder="{page} of {total}" />
            </div>

            <div class="switch-row">
              <span>Page Numbers</span>
              <button class="toggle-switch" :class="{ active: localSettings.show_page_numbers }" @click="localSettings.show_page_numbers = !localSettings.show_page_numbers; emitSettings()">
                <span class="switch-thumb"></span>
              </button>
            </div>
          </div>

          <!-- Watermark -->
          <div class="settings-section">
            <div class="section-title">
              <i class="fa-solid fa-water"></i> Watermark
            </div>

            <div class="form-group">
              <label>Watermark Text</label>
              <input type="text" v-model="localSettings.watermark" @input="emitSettings" class="form-input" placeholder="DRAFT / CONFIDENTIAL" />
            </div>

            <div class="form-group" v-if="localSettings.watermark">
              <label>Opacity: {{ localSettings.watermark_opacity || 5 }}%</label>
              <input type="range" v-model.number="localSettings.watermark_opacity" min="1" max="25" @input="emitSettings" class="form-range" />
            </div>
          </div>

        </div>
      </div>

      <!-- ═══ VERSIONS TAB ═══════════════════════════════════════ -->
      <div v-show="activeTab === 'versions'" class="tab-panel">
        <div class="versions-header">
          <button class="btn-secondary full-width" @click="loadVersions">
            <i class="fa-solid fa-rotate"></i> Refresh History
          </button>
        </div>

        <div class="versions-timeline" v-if="versionList.length">
          <div
            v-for="(ver, vi) in versionList"
            :key="ver.id"
            class="version-item"
            :class="{ 'version-current': vi === 0 }"
          >
            <div class="version-dot"></div>
            <div class="version-line" v-if="vi < versionList.length - 1"></div>
            <div class="version-content">
              <div class="version-header">
                <strong>v{{ ver.version_number }}</strong>
                <span v-if="vi === 0" class="current-badge">Current</span>
              </div>
              <p class="version-label">{{ ver.label }}</p>
              <span class="version-date">{{ formatDate(ver.created_at) }}</span>
            </div>
            <button
              class="btn-secondary btn-sm"
              @click="restoreVersion(ver.id)"
              :disabled="vi === 0"
              title="Restore this version"
            >
              Restore
            </button>
          </div>
        </div>

        <div v-else class="empty-state">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <p>No versions yet</p>
          <small>Versions are created automatically</small>
        </div>
      </div>

    </div>
  </aside>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'

// ═══════════════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════════════
const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
  currentPage: { type: Number, default: 0 },
  selectedElIdx: { type: [Number, null], default: null },
  selectedEls: { type: Array, default: () => [] },
  activeTab: { type: String, default: 'elements' },
})

// ═══════════════════════════════════════════════════════════════════
// EMITS
// ═══════════════════════════════════════════════════════════════════
const emit = defineEmits([
  'add-element', 'add-element-center', 'select-page',
  'add-page', 'duplicate-page', 'delete-page', 'rename-page',
  'select-element', 'deselect-all',
  'toggle-visibility', 'toggle-lock',
  'upload-image', 'apply-template',
  'update:settings', 'update:active-tab',
  'canvas-drag-start',
])

// ═══════════════════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════════════════
const isCollapsed = ref(false)
const elSearch = ref('')
const collapsedCats = ref([])
const renamingPage = ref(null)
const renameInput = ref(null)
const mediaInput = ref(null)
const unsplashQuery = ref('business')
const unsplashImages = ref([])
const unsplashLoading = ref(false)
const uploadedImages = ref([])
const versionList = ref([])
const savedPresets = ref([])

// Local settings copy
const localSettings = reactive({ ...props.settings })

// ═══════════════════════════════════════════════════════════════════
// CONSTANTS
// ═══════════════════════════════════════════════════════════════════
const tabs = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-shapes' },
  { id: 'pages', label: 'Pages', icon: 'fa-solid fa-copy', badge: computed(() => props.report.content.length) },
  { id: 'layers', label: 'Layers', icon: 'fa-solid fa-layer-group' },
  { id: 'media', label: 'Media', icon: 'fa-solid fa-image' },
  { id: 'templates', label: 'Layouts', icon: 'fa-solid fa-panorama' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-sliders' },
  { id: 'versions', label: 'History', icon: 'fa-solid fa-clock-rotate-left' },
]

const fontList = [
  'Inter', 'DM Sans', 'Plus Jakarta Sans', 'Space Grotesk', 'Sora',
  'Outfit', 'Nunito', 'Georgia', 'Playfair Display', 'Times New Roman',
]

const quickPresets = [
  { type: 'heading', label: 'Heading', icon: 'fa-solid fa-heading', w: 350, h: 60, defaultContent: 'Heading' },
  { type: 'text', label: 'Text', icon: 'fa-solid fa-align-left', w: 250, h: 60 },
  { type: 'image', label: 'Image', icon: 'fa-solid fa-image', w: 300, h: 200 },
  { type: 'table', label: 'Table', icon: 'fa-solid fa-table', w: 450, h: 200 },
  { type: 'metric', label: 'KPI', icon: 'fa-solid fa-chart-simple', w: 200, h: 120 },
]

const quickTemplates = [
  { name: 'Executive Dark', gradient: 'linear-gradient(135deg, #0f172a, #1e293b)' },
  { name: 'Modern Blue', gradient: 'linear-gradient(135deg, #1e40af, #3b82f6)' },
  { name: 'Emerald Fresh', gradient: 'linear-gradient(135deg, #065f46, #10b981)' },
  { name: 'Warm Amber', gradient: 'linear-gradient(135deg, #78350f, #f59e0b)' },
  { name: 'Rose Gold', gradient: 'linear-gradient(135deg, #9f1239, #f43f5e)' },
  { name: 'Purple Pro', gradient: 'linear-gradient(135deg, #4c1d95, #8b5cf6)' },
]

const elementCategories = [
  {
    name: 'Text',
    items: [
      { type: 'text', label: 'Text', icon: 'fa-solid fa-align-left', w: 200, h: 50 },
      { type: 'heading', label: 'Heading', icon: 'fa-solid fa-heading', w: 350, h: 60 },
      { type: 'subheading', label: 'Subheading', icon: 'fa-solid fa-h', w: 280, h: 45 },
      { type: 'quote', label: 'Quote', icon: 'fa-solid fa-quote-right', w: 320, h: 80 },
      { type: 'blockquote', label: 'Blockquote', icon: 'fa-solid fa-quote-left', w: 300, h: 80 },
      { type: 'highlight', label: 'Highlight', icon: 'fa-solid fa-highlighter', w: 250, h: 40, isNew: true },
      { type: 'badge', label: 'Badge', icon: 'fa-solid fa-tag', w: 120, h: 35 },
      { type: 'code', label: 'Code', icon: 'fa-solid fa-code', w: 350, h: 120 },
      { type: 'link', label: 'Link', icon: 'fa-solid fa-link', w: 200, h: 35 },
    ]
  },
  {
    name: 'Data & Charts',
    items: [
      { type: 'table', label: 'Table', icon: 'fa-solid fa-table', w: 460, h: 220 },
      { type: 'metric', label: 'KPI Card', icon: 'fa-solid fa-chart-simple', w: 200, h: 120 },
      { type: 'progress', label: 'Progress', icon: 'fa-solid fa-bars-progress', w: 350, h: 60 },
      { type: 'checklist', label: 'Checklist', icon: 'fa-solid fa-list-check', w: 300, h: 180 },
      { type: 'stat-row', label: 'Stat Row', icon: 'fa-solid fa-chart-bar', w: 450, h: 90 },
      { type: 'bar-chart', label: 'Bar Chart', icon: 'fa-solid fa-chart-simple', w: 400, h: 280 },
      { type: 'line-chart', label: 'Line Chart', icon: 'fa-solid fa-chart-line', w: 400, h: 280 },
      { type: 'pie-chart', label: 'Pie Chart', icon: 'fa-solid fa-chart-pie', w: 280, h: 280 },
      { type: 'area-chart', label: 'Area Chart', icon: 'fa-solid fa-chart-area', w: 400, h: 280, isNew: true },
      { type: 'doughnut-chart', label: 'Doughnut', icon: 'fa-solid fa-circle-half-stroke', w: 280, h: 280 },
    ]
  },
  {
    name: 'Media',
    items: [
      { type: 'image', label: 'Image', icon: 'fa-solid fa-image', w: 300, h: 200 },
      { type: 'icon', label: 'Icon', icon: 'fa-solid fa-star', w: 60, h: 60 },
      { type: 'rating', label: 'Rating', icon: 'fa-solid fa-star-half-stroke', w: 160, h: 40 },
      { type: 'qr-code', label: 'QR Code', icon: 'fa-solid fa-qrcode', w: 150, h: 150, isNew: true },
    ]
  },
  {
    name: 'Shapes & Layout',
    items: [
      { type: 'rectangle', label: 'Rectangle', icon: 'fa-solid fa-square', w: 200, h: 120 },
      { type: 'circle', label: 'Circle', icon: 'fa-solid fa-circle', w: 120, h: 120 },
      { type: 'triangle', label: 'Triangle', icon: 'fa-solid fa-play', w: 120, h: 100 },
      { type: 'divider', label: 'Divider', icon: 'fa-solid fa-minus', w: 500, h: 4 },
      { type: 'arrow', label: 'Arrow', icon: 'fa-solid fa-arrow-right', w: 200, h: 40 },
      { type: 'callout', label: 'Callout', icon: 'fa-solid fa-lightbulb', w: 380, h: 100 },
      { type: 'timeline', label: 'Timeline', icon: 'fa-solid fa-timeline', w: 420, h: 250 },
      { type: 'testimonial', label: 'Testimonial', icon: 'fa-solid fa-comment-dots', w: 360, h: 160 },
      { type: 'signature', label: 'Signature', icon: 'fa-solid fa-signature', w: 220, h: 100 },
    ]
  },
]

// ═══════════════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════════════
const currentPageElements = computed(() => props.report.content[props.currentPage]?.elements || [])
const reversedElements = computed(() => [...currentPageElements.value].reverse())
const filteredCategories = computed(() => {
  if (!elSearch.value) return elementCategories
  const q = elSearch.value.toLowerCase()
  return elementCategories
    .map(c => ({ ...c, items: c.items.filter(i => i.label.toLowerCase().includes(q) || i.type.includes(q)) }))
    .filter(c => c.items.length)
})

// ═══════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════
function getElIcon(type) {
  const all = elementCategories.flatMap(c => c.items)
  return all.find(i => i.type === type)?.icon || 'fa-solid fa-cube'
}

function getElColor(type) {
  const colors = {
    text: '#6366f1', heading: '#4f46e5', table: '#10b981', metric: '#f59e0b',
    progress: '#8b5cf6', image: '#ec4899', chart: '#06b6d4', shape: '#64748b',
  }
  for (const [key, color] of Object.entries(colors)) {
    if (type?.includes(key)) return color
  }
  return '#94a3b8'
}

function getLayerName(el) {
  const text = el.content?.toString?.() || ''
  const plain = text.replace(/<[^>]*>/g, '').trim()
  return plain.substring(0, 30) || el.type || 'Untitled'
}

function isElementSelected(ei) {
  const actualIndex = currentPageElements.value.length - 1 - ei
  return props.selectedElIdx === actualIndex || props.selectedEls.includes(actualIndex)
}

function getMiniPageStyle() {
  return {
    width: '100%',
    aspectRatio: '1/1.414',
    backgroundColor: props.settings.background_color || '#ffffff',
    position: 'relative',
    overflow: 'hidden',
    borderRadius: '3px',
  }
}

function getMiniElStyle(el) {
  const scale = 0.15
  return {
    position: 'absolute',
    left: (el.position?.x || 0) * scale + 'px',
    top: (el.position?.y || 0) * scale + 'px',
    width: ((el.styles?.width || 100) * scale) + 'px',
    height: ((el.styles?.height || 50) * scale) + 'px',
    background: el.styles?.backgroundColor || props.settings.primary_color || '#6366f1',
    opacity: 0.6,
    borderRadius: '1px',
  }
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

// ═══════════════════════════════════════════════════════════════════
// METHODS
// ═══════════════════════════════════════════════════════════════════
function addElementToPage(el) {
  emit('add-element-center', el)
}

function selectLayerElement(ei) {
  const actualIndex = currentPageElements.value.length - 1 - ei
  emit('select-element', actualIndex)
}

function toggleCategory(name) {
  const idx = collapsedCats.value.indexOf(name)
  if (idx >= 0) collapsedCats.value.splice(idx, 1)
  else collapsedCats.value.push(name)
}

function startRename(pi) {
  renamingPage.value = pi
  nextTick(() => {
    const inputs = document.querySelectorAll('.page-rename-input')
    if (inputs.length) inputs[inputs.length - 1]?.focus()
  })
}

function finishRename(pi, value) {
  renamingPage.value = null
  emit('rename-page', pi, value || `Page ${pi + 1}`)
}

function emitSettings() {
  emit('update:settings', { ...localSettings })
}

// Sync local settings when props change
watch(() => props.settings, (newVal) => {
  Object.assign(localSettings, newVal)
}, { deep: true })

// ═══════════════════════════════════════════════════════════════════
// MEDIA
// ═══════════════════════════════════════════════════════════════════
function triggerUpload() {
  mediaInput.value?.click()
}

function handleFileInput(e) {
  const files = e.target.files
  if (!files) return
  Array.from(files).forEach(file => {
    const reader = new FileReader()
    reader.onload = ev => {
      uploadedImages.value.push({ url: ev.target.result, name: file.name })
    }
    reader.readAsDataURL(file)
  })
  e.target.value = ''
}

function handleDrop(e) {
  const files = e.dataTransfer.files
  if (!files) return
  Array.from(files).forEach(file => {
    if (!file.type.startsWith('image/')) return
    const reader = new FileReader()
    reader.onload = ev => {
      uploadedImages.value.push({ url: ev.target.result, name: file.name })
    }
    reader.readAsDataURL(file)
  })
}

function onMediaDrag(e, img) {
  e.dataTransfer.setData('el-def', JSON.stringify({ type: 'image', w: 300, h: 200, src: img.url }))
}

function addImageToCanvas(img) {
  emit('add-element-center', { type: 'image', w: 300, h: 200, src: img.url })
}

function removeUploaded(img) {
  uploadedImages.value = uploadedImages.value.filter(i => i.url !== img.url)
}

// ═══════════════════════════════════════════════════════════════════
// UNSPLASH
// ═══════════════════════════════════════════════════════════════════
async function searchUnsplash() {
  if (!unsplashQuery.value.trim()) return
  unsplashLoading.value = true
  try {
    const res = await fetch(`/api/unsplash/search?q=${encodeURIComponent(unsplashQuery.value)}`, {
      headers: { 'Accept': 'application/json' }
    })
    if (res.ok) {
      const data = await res.json()
      unsplashImages.value = data.images || []
    }
  } catch (err) {
    console.warn('Unsplash search failed:', err)
    // Fallback: use placeholder images
    unsplashImages.value = Array.from({ length: 8 }, (_, i) => ({
      id: i,
      thumb: `https://picsum.photos/200/150?random=${i * 7 + Date.now() % 100}`,
      url: `https://picsum.photos/800/600?random=${i * 7 + Date.now() % 100}`,
      author: 'Free Stock',
    }))
  }
  unsplashLoading.value = false
}

function addStockImage(img) {
  emit('add-element-center', { type: 'image', w: 300, h: 200, src: img.url })
}

// ═══════════════════════════════════════════════════════════════════
// VERSIONS
// ═══════════════════════════════════════════════════════════════════
async function loadVersions() {
  try {
    const res = await fetch(route('reports.versions', props.report.slug), {
      headers: { 'Accept': 'application/json' }
    })
    if (res.ok) {
      const data = await res.json()
      versionList.value = data.versions || []
    }
  } catch (err) {
    console.warn('Failed to load versions:', err)
  }
}

async function restoreVersion(id) {
  if (!confirm('Restore this version? Current unsaved changes will be lost.')) return
  try {
    await fetch(route('reports.versions.restore', { report: props.report.slug, version: id }), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
    })
    window.location.reload()
  } catch (err) {
    console.warn('Failed to restore version:', err)
  }
}

function applyPreset(preset) {
  emit('add-element-center', preset)
}

// ═══════════════════════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════════════════════
onMounted(() => {
  loadVersions()
})

// Expose
defineExpose({ isCollapsed })
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════════════════
   LEFT PANEL STYLES
   ══════════════════════════════════════════════════════════════════ */

.left-panel {
  width: 260px;
  flex-shrink: 0;
  background: var(--bg-panel, #ffffff);
  border-right: 1px solid var(--border, #e2e8f0);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 50;
}

.left-panel.collapsed {
  width: 0;
  border-right: none;
}

/* ── Toggle ────────────────────────────────────────────────────── */
.panel-toggle {
  position: absolute;
  right: -14px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--bg-panel, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  transition: all 0.15s;
}

.panel-toggle:hover {
  color: var(--accent, #6366f1);
  border-color: var(--accent, #6366f1);
  box-shadow: 0 4px 16px rgba(99,102,241,0.2);
}

/* ── Tabs ──────────────────────────────────────────────────────── */
.panel-tabs {
  display: flex;
  gap: 2px;
  padding: 6px 6px 0;
  border-bottom: 1px solid var(--border, #e2e8f0);
  flex-shrink: 0;
  overflow-x: auto;
  scrollbar-width: none;
}

.panel-tabs::-webkit-scrollbar { display: none; }

.panel-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  padding: 6px 4px;
  border: none;
  background: transparent;
  border-radius: 6px 6px 0 0;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 9px;
  font-weight: 600;
  letter-spacing: 0.02em;
  transition: all 0.15s;
  flex: 1;
  min-width: 0;
  position: relative;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}

.panel-tab:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-secondary, #475569);
}

.panel-tab.active {
  background: var(--accent-light, rgba(99,102,241,0.08));
  color: var(--accent, #6366f1);
  border-bottom-color: var(--accent, #6366f1);
}

.panel-tab i { font-size: 13px; }

.tab-label {
  font-size: 8px;
  white-space: nowrap;
  letter-spacing: 0.03em;
}

.tab-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: var(--accent, #6366f1);
  color: #fff;
  font-size: 8px;
  font-weight: 700;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Panel Content ─────────────────────────────────────────────── */
.panel-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.tab-panel {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  scrollbar-width: thin;
}

/* ── Search ────────────────────────────────────────────────────── */
.search-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--bg-secondary, #f8fafc);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 8px;
  padding: 5px 8px;
  margin-bottom: 8px;
  transition: border-color 0.15s;
}

.search-wrap:focus-within {
  border-color: var(--accent, #6366f1);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.search-icon {
  color: var(--text-muted, #94a3b8);
  font-size: 11px;
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 11px;
  color: var(--text-primary, #0f172a);
}

.search-input::placeholder {
  color: var(--text-muted, #94a3b8);
}

/* ── Presets Row ───────────────────────────────────────────────── */
.presets-row {
  display: flex;
  gap: 4px;
  margin-bottom: 10px;
  overflow-x: auto;
  scrollbar-width: none;
  padding: 2px 0;
}

.presets-row::-webkit-scrollbar { display: none; }

.preset-chip {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 99px;
  background: var(--bg-primary, #ffffff);
  cursor: pointer;
  font-size: 10px;
  font-weight: 600;
  color: var(--text-secondary, #475569);
  white-space: nowrap;
  transition: all 0.15s;
}

.preset-chip:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
  transform: translateY(-1px);
}

.preset-chip i { font-size: 10px; }

/* ── Elements Scroll ───────────────────────────────────────────── */
.elements-scroll {
  flex: 1;
  overflow-y: auto;
}

/* ── Category ──────────────────────────────────────────────────── */
.el-category {
  margin-bottom: 8px;
}

.cat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 5px 4px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted, #94a3b8);
  letter-spacing: 0.06em;
  text-transform: uppercase;
  border-radius: 4px;
  transition: background 0.1s;
}

.cat-header:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-secondary, #475569);
}

.cat-count {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
  margin-left: auto;
  margin-right: 4px;
}

/* ── Element Grid ──────────────────────────────────────────────── */
.el-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 3px;
  padding: 3px 0;
}

.el-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px 3px;
  border-radius: 8px;
  cursor: grab;
  border: 1px solid transparent;
  transition: all 0.15s;
  user-select: none;
  text-align: center;
  position: relative;
}

.el-card:hover {
  background: var(--accent-light, rgba(99,102,241,0.06));
  border-color: rgba(99,102,241,0.2);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.el-card:active {
  cursor: grabbing;
  transform: scale(0.95);
}

.el-preview {
  font-size: 16px;
  color: var(--text-secondary, #475569);
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary, #f8fafc);
  border-radius: 7px;
  transition: all 0.15s;
}

.el-card:hover .el-preview {
  color: var(--accent, #6366f1);
  background: rgba(99,102,241,0.1);
}

.el-name {
  font-size: 9px;
  font-weight: 600;
  color: var(--text-muted, #94a3b8);
  line-height: 1.2;
}

.el-card:hover .el-name {
  color: var(--text-primary, #0f172a);
}

.new-badge {
  position: absolute;
  top: -3px;
  right: -3px;
  background: var(--accent, #6366f1);
  color: #fff;
  font-size: 7px;
  font-weight: 800;
  padding: 1px 5px;
  border-radius: 99px;
  letter-spacing: 0.05em;
  animation: pulse 2s ease-in-out infinite;
}

/* ── Pages ─────────────────────────────────────────────────────── */
.pages-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.add-page-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  width: 100%;
  padding: 8px;
  border: 1.5px dashed var(--border, #e2e8f0);
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s;
  margin-bottom: 8px;
}

.add-page-btn:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
  animation: glowPulse 1.5s ease-in-out infinite;
}

.pages-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.page-card {
  border: 2px solid var(--border, #e2e8f0);
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--bg-primary, #ffffff);
  position: relative;
}

.page-card:hover {
  border-color: var(--border-hover, #cbd5e1);
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
  transform: translateY(-1px);
}

.page-card.active {
  border-color: var(--accent, #6366f1) !important;
  box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}

/* ── Page Preview ──────────────────────────────────────────────── */
.page-preview {
  padding: 6px;
  background: var(--bg-secondary, #f8fafc);
}

.page-mini {
  background: #fff;
  position: relative;
  overflow: hidden;
  border: 1px solid var(--border-light, #f1f5f9);
}

.mini-el {
  position: absolute;
  border-radius: 1px;
}

.mini-empty {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted, #94a3b8);
  font-size: 14px;
  opacity: 0.3;
}

/* ── Page Info ─────────────────────────────────────────────────── */
.page-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 8px;
  border-top: 1px solid var(--border-light, #f1f5f9);
}

.page-name {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-primary, #0f172a);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

.page-el-count {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
  background: var(--bg-secondary, #f8fafc);
  padding: 1px 6px;
  border-radius: 99px;
}

.page-rename-input {
  border: 1px solid var(--accent, #6366f1);
  border-radius: 4px;
  padding: 1px 4px;
  font-size: 10px;
  font-weight: 600;
  outline: none;
  width: 100%;
  background: var(--bg-primary, #ffffff);
}

/* ── Page Actions ──────────────────────────────────────────────── */
.page-actions {
  display: flex;
  gap: 2px;
  padding: 0 8px 6px;
}

.page-actions button {
  flex: 1;
  padding: 3px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 4px;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  transition: all 0.15s;
}

.page-actions button:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

.page-actions button.danger:hover {
  background: rgba(239,68,68,0.08);
  color: #ef4444;
  border-color: rgba(239,68,68,0.3);
}

.page-actions button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* ── Active Glow ───────────────────────────────────────────────── */
.active-glow {
  position: absolute;
  inset: -2px;
  border-radius: 10px;
  border: 2px solid var(--accent, #6366f1);
  pointer-events: none;
  animation: glowPulse 2s ease-in-out infinite;
  box-shadow: 0 0 20px rgba(99,102,241,0.3), inset 0 0 20px rgba(99,102,241,0.05);
}

@keyframes glowPulse {
  0%, 100% { box-shadow: 0 0 15px rgba(99,102,241,0.2), inset 0 0 15px rgba(99,102,241,0.03); }
  50% { box-shadow: 0 0 30px rgba(99,102,241,0.4), inset 0 0 30px rgba(99,102,241,0.08); }
}

/* ── Layers ────────────────────────────────────────────────────── */
.layers-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 0 8px;
  font-weight: 600;
  font-size: 11px;
  color: var(--text-secondary, #475569);
}

.layers-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

.layer-count {
  background: var(--bg-secondary, #f8fafc);
  padding: 2px 7px;
  border-radius: 99px;
  font-size: 10px;
  color: var(--text-muted, #94a3b8);
}

.micro-btn {
  width: 22px;
  height: 22px;
  border: none;
  background: transparent;
  border-radius: 4px;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.12s;
}

.micro-btn:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

.layers-list {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.layer-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 6px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.12s;
  border: 1px solid transparent;
}

.layer-item:hover {
  background: var(--bg-secondary, #f8fafc);
  border-color: var(--border, #e2e8f0);
}

.layer-item.layer-selected {
  background: var(--accent-light, rgba(99,102,241,0.08));
  border-color: var(--accent, #6366f1);
}

.layer-item.layer-locked {
  opacity: 0.5;
}

.layer-item.layer-hidden {
  opacity: 0.35;
}

.layer-drag-handle {
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  cursor: grab;
  opacity: 0.5;
}

.layer-type-icon {
  font-size: 12px;
  width: 24px;
  text-align: center;
  flex-shrink: 0;
}

.layer-name {
  flex: 1;
  font-size: 10px;
  font-weight: 500;
  color: var(--text-primary, #0f172a);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: flex;
  flex-direction: column;
}

.layer-type-label {
  font-size: 8px;
  color: var(--text-muted, #94a3b8);
  text-transform: capitalize;
  font-weight: 400;
}

.layer-controls {
  display: flex;
  gap: 1px;
}

.layer-controls button {
  width: 20px;
  height: 20px;
  border: none;
  background: transparent;
  border-radius: 3px;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 9px;
  opacity: 0;
  transition: all 0.12s;
}

.layer-item:hover .layer-controls button {
  opacity: 1;
}

.layer-controls button:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

/* ── Empty State ───────────────────────────────────────────────── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 15px;
  text-align: center;
  color: var(--text-muted, #94a3b8);
}

.empty-state i {
  font-size: 28px;
  opacity: 0.3;
  margin-bottom: 8px;
}

.empty-state p {
  font-size: 11px;
  font-weight: 500;
  margin-bottom: 4px;
}

.empty-state small {
  font-size: 10px;
  opacity: 0.7;
}

.empty-state.small {
  padding: 15px;
}

.empty-state.small i {
  font-size: 20px;
}

/* ── Media Upload Zone ─────────────────────────────────────────── */
.upload-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 20px;
  border: 2px dashed var(--border, #e2e8f0);
  border-radius: 10px;
  cursor: pointer;
  margin-bottom: 10px;
  transition: all 0.2s;
  color: var(--text-muted, #94a3b8);
  font-size: 11px;
}

.upload-zone:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.04));
}

.upload-zone i {
  font-size: 24px;
  opacity: 0.5;
}

.upload-formats {
  font-size: 9px;
  opacity: 0.5;
}

/* ── Unsplash ──────────────────────────────────────────────────── */
.unsplash-section {
  margin-bottom: 10px;
}

.section-title {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-muted, #94a3b8);
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.unsplash-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 3px;
  margin-top: 6px;
}

.unsplash-item {
  border-radius: 5px;
  overflow: hidden;
  cursor: pointer;
  aspect-ratio: 4/3;
  border: 1px solid var(--border, #e2e8f0);
  transition: all 0.15s;
}

.unsplash-item:hover {
  transform: scale(1.05);
  border-color: var(--accent, #6366f1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  z-index: 1;
}

.unsplash-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* ── Uploaded Images ───────────────────────────────────────────── */
.uploaded-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 4px;
}

.uploaded-item {
  border-radius: 6px;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid var(--border, #e2e8f0);
  position: relative;
  aspect-ratio: 1;
  transition: all 0.15s;
}

.uploaded-item:hover {
  border-color: var(--accent, #6366f1);
  transform: translateY(-1px);
}

.uploaded-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.img-name {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0,0,0,0.6);
  color: #fff;
  font-size: 8px;
  padding: 2px 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.remove-img {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: rgba(0,0,0,0.5);
  border: none;
  color: #fff;
  font-size: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.15s;
}

.uploaded-item:hover .remove-img {
  opacity: 1;
}

/* ── Templates ──────────────────────────────────────────────────── */
.tab-description {
  font-size: 10px;
  color: var(--text-muted, #94a3b8);
  margin-bottom: 8px;
  line-height: 1.4;
}

.template-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 5px;
  margin-bottom: 12px;
}

.template-card {
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid var(--border, #e2e8f0);
  transition: all 0.2s;
}

.template-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.1);
  border-color: var(--accent, #6366f1);
}

.tpl-preview {
  height: 45px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tpl-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  color: #fff;
  font-size: 9px;
  font-weight: 600;
  opacity: 0;
  transition: opacity 0.2s;
}

.template-card:hover .tpl-overlay {
  opacity: 1;
}

.tpl-name {
  display: block;
  font-size: 9px;
  font-weight: 600;
  padding: 4px 6px;
  text-align: center;
  color: var(--text-secondary, #475569);
}

/* ── Settings ──────────────────────────────────────────────────── */
.settings-scroll {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.settings-section {
  padding: 8px 0;
  border-bottom: 1px solid var(--border-light, #f1f5f9);
}

.settings-section:last-child {
  border-bottom: none;
}

.form-group {
  margin-bottom: 7px;
}

.form-group label {
  display: block;
  font-size: 9px;
  font-weight: 600;
  color: var(--text-secondary, #475569);
  margin-bottom: 3px;
  letter-spacing: 0.02em;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
}

.form-input {
  width: 100%;
  padding: 5px 7px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  font-size: 10px;
  outline: none;
  transition: border-color 0.15s;
  box-sizing: border-box;
  font-family: inherit;
}

.form-input:focus {
  border-color: var(--accent, #6366f1);
}

.form-input.mono {
  font-family: 'Courier New', monospace;
  font-size: 10px;
}

.form-select {
  width: 100%;
  padding: 5px 7px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  font-size: 10px;
  outline: none;
  cursor: pointer;
  font-family: inherit;
}

.form-range {
  width: 100%;
  accent-color: var(--accent, #6366f1);
  height: 4px;
  cursor: pointer;
}

.color-input-row {
  display: flex;
  gap: 5px;
  align-items: center;
}

.color-picker {
  width: 28px;
  height: 28px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  cursor: pointer;
  padding: 1px;
  background: transparent;
}

.toggle-group {
  display: flex;
  gap: 2px;
}

.toggle-group button {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  font-size: 10px;
  font-weight: 500;
  color: var(--text-muted, #94a3b8);
  transition: all 0.15s;
  font-family: inherit;
}

.toggle-group button.active {
  background: var(--accent-light, rgba(99,102,241,0.1));
  color: var(--accent, #6366f1);
  border-color: var(--accent, #6366f1);
}

.switch-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 7px;
  font-size: 10px;
  font-weight: 600;
  color: var(--text-secondary, #475569);
}

.toggle-switch {
  width: 36px;
  height: 20px;
  border-radius: 99px;
  border: none;
  background: var(--border, #e2e8f0);
  cursor: pointer;
  position: relative;
  transition: background 0.2s;
}

.toggle-switch.active {
  background: var(--accent, #6366f1);
}

.switch-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle-switch.active .switch-thumb {
  transform: translateX(16px);
}

/* ── Versions ──────────────────────────────────────────────────── */
.versions-header {
  margin-bottom: 8px;
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 6px 10px;
  border: 1px solid var(--border, #e2e8f0);
  background: var(--bg-primary, #ffffff);
  color: var(--text-primary, #0f172a);
  border-radius: 6px;
  cursor: pointer;
  font-size: 10px;
  font-weight: 500;
  transition: all 0.15s;
  font-family: inherit;
}

.btn-secondary:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
}

.btn-secondary:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-secondary.full-width { width: 100%; }
.btn-secondary.btn-sm { padding: 3px 8px; font-size: 9px; }

.versions-timeline {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.version-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid transparent;
  transition: all 0.15s;
  position: relative;
}

.version-item:hover {
  background: var(--bg-secondary, #f8fafc);
  border-color: var(--border, #e2e8f0);
}

.version-item.version-current {
  background: var(--accent-light, rgba(99,102,241,0.06));
  border-color: var(--accent, #6366f1);
}

.version-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--accent, #6366f1);
  flex-shrink: 0;
  margin-top: 3px;
}

.version-line {
  position: absolute;
  left: 3px;
  top: 14px;
  bottom: -4px;
  width: 2px;
  background: var(--border, #e2e8f0);
}

.version-content {
  flex: 1;
  min-width: 0;
}

.version-header {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 2px;
}

.version-header strong {
  font-size: 11px;
  color: var(--text-primary, #0f172a);
}

.current-badge {
  font-size: 8px;
  font-weight: 700;
  background: var(--accent, #6366f1);
  color: #fff;
  padding: 1px 5px;
  border-radius: 99px;
}

.version-label {
  font-size: 10px;
  color: var(--text-secondary, #475569);
  margin-bottom: 1px;
}

.version-date {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
}

/* ── Hidden ────────────────────────────────────────────────────── */
.hidden {
  display: none !important;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .left-panel {
    position: fixed;
    left: 0;
    top: 48px;
    bottom: 0;
    z-index: 150;
    box-shadow: 8px 0 40px rgba(0,0,0,0.15);
  }
  
  .left-panel.collapsed {
    width: 0;
    box-shadow: none;
  }
}
</style>