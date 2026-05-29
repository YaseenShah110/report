<!--
  LeftSidebar.vue — FULLY ENHANCED
  • Elements tab: 50+ elements, search, quick-add chips, draggable cards
  • Charts category: dropdown to select chart type before dropping
  • Pages tab: mini previews, drag to reorder, rename, duplicate, delete
  • Layers tab: z-order list, visibility, lock, type icon, color
  • Media tab: upload zone, drag-drop, Unsplash search, uploaded grid
  • Templates tab: 8 quick-apply color themes
  • Settings tab: page size, orientation, colors, fonts, margins, header/footer
  • History tab: version list with restore
  • All settings isolated to editor — never touches html/body
-->
<template>
  <aside class="left-panel" :class="{ collapsed: isCollapsed }">
    <!-- Collapse toggle -->
    <button class="panel-toggle" @click="$emit('update:is-collapsed', !isCollapsed)"
      :title="isCollapsed ? 'Expand' : 'Collapse'">
      <i :class="isCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'" />
    </button>

    <!-- Tab nav -->
    <nav class="tab-nav" v-show="!isCollapsed">
      <button v-for="t in TABS" :key="t.id" class="tab-btn" :class="{ active: activeTab === t.id }"
        @click="activeTab = t.id" :title="t.label">
        <i :class="t.icon" />
        <span>{{ t.label }}</span>
      </button>
    </nav>

    <!-- Tab content -->
    <div class="tab-body" v-show="!isCollapsed">

      <!-- ══ ELEMENTS TAB ═══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'elements'" class="tab-panel">

        <!-- Search -->
        <div class="search-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon" />
          <input v-model="elSearch" class="search-input" placeholder="Search 50+ elements…" />
          <button v-if="elSearch" class="search-clear" @click="elSearch = ''"><i class="fa-solid fa-xmark" /></button>
        </div>

        <!-- Quick-add chips -->
        <div class="quick-chips">
          <button v-for="q in QUICK" :key="q.type" class="chip" @click="addCenter(q)" :title="q.label">
            <i :class="q.icon" /> {{ q.label }}
          </button>
        </div>

        <!-- Element categories -->
        <div class="el-cats">
          <div v-for="cat in filteredCats" :key="cat.name" class="el-cat">
            <button class="cat-hdr" @click="toggleCat(cat.name)">
              <i :class="cat.icon" />
              <span>{{ cat.name }}</span>
              <span class="cat-count">{{ cat.items.length }}</span>
              <i class="fa-solid fa-chevron-down cat-chev" :class="{ rotated: collapsedCats.includes(cat.name) }" />
            </button>

            <div v-if="!collapsedCats.includes(cat.name)" class="el-grid">

              <!-- Chart category: show type dropdown -->
              <template v-if="cat.name === 'Charts & Graphs'">
                <div class="chart-type-selector">
                  <div class="chart-type-label"><i class="fa-solid fa-chart-bar" /> Chart Type</div>
                  <select v-model="selectedChartType" class="chart-type-select">
                    <option v-for="ct in CHART_TYPES" :key="ct.value" :value="ct.value">{{ ct.label }}</option>
                  </select>
                  <div class="chart-drop-card" draggable="true"
                    @dragstart="onDragStart($event, { type: selectedChartType, w: 420, h: 280, label: getChartLabel(selectedChartType), icon: getChartIcon(selectedChartType) })"
                    @dblclick="addCenter({ type: selectedChartType, w: 420, h: 280, label: getChartLabel(selectedChartType), icon: getChartIcon(selectedChartType) })"
                    :title="`Drag or double-click to add ${getChartLabel(selectedChartType)}`">
                    <div class="chart-drop-preview">
                      <i :class="getChartIcon(selectedChartType)" />
                      <div>
                        <strong>{{ getChartLabel(selectedChartType) }}</strong>
                        <span>Drag to canvas or double-click</span>
                      </div>
                    </div>
                    <div class="chart-mini-preview">
                      <!-- Animated mini bars -->
                      <div v-if="selectedChartType.includes('bar')" class="mini-bars">
                        <div v-for="h in [60, 85, 45, 100, 70, 55]" :key="h" class="mini-bar"
                          :style="{ height: h + '%', background: settings.primary_color || '#6366f1' }" />
                      </div>
                      <div v-else-if="selectedChartType.includes('line') || selectedChartType.includes('area')"
                        class="mini-line">
                        <svg viewBox="0 0 100 40" preserveAspectRatio="none">
                          <polyline points="0,35 17,25 33,30 50,15 67,20 83,10 100,18" fill="none"
                            :stroke="settings.primary_color || '#6366f1'" stroke-width="2.5" stroke-linecap="round" />
                          <polyline v-if="selectedChartType.includes('area')"
                            points="0,35 17,25 33,30 50,15 67,20 83,10 100,18 100,40 0,40"
                            :fill="(settings.primary_color || '#6366f1') + '33'" stroke="none" />
                        </svg>
                      </div>
                      <div
                        v-else-if="selectedChartType.includes('pie') || selectedChartType.includes('doughnut') || selectedChartType.includes('polar')"
                        class="mini-pie">
                        <svg viewBox="0 0 40 40">
                          <circle cx="20" cy="20" r="15" fill="none" :stroke="settings.primary_color || '#6366f1'"
                            stroke-width="30" stroke-dasharray="47 95" />
                          <circle cx="20" cy="20" r="15" fill="none" stroke="#10b981" stroke-width="30"
                            stroke-dasharray="30 95" stroke-dashoffset="-47" />
                          <circle cx="20" cy="20" r="15" fill="none" stroke="#f59e0b" stroke-width="30"
                            stroke-dasharray="18 95" stroke-dashoffset="-77" />
                          <circle v-if="selectedChartType.includes('doughnut')" cx="20" cy="20" r="8"
                            fill="var(--bg-panel,#fff)" />
                        </svg>
                      </div>
                      <div v-else-if="selectedChartType.includes('radar')" class="mini-radar">
                        <svg viewBox="0 0 40 40">
                          <polygon points="20,3 37,14 30,33 10,33 3,14" fill="none" stroke="#e2e8f0" stroke-width="1" />
                          <polygon points="20,8 31,16 27,28 13,28 9,16" :fill="(settings.primary_color || '#6366f1') + '44'"
                            :stroke="settings.primary_color || '#6366f1'" stroke-width="1.5" />
                        </svg>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- All other element cards -->
              <template v-else>
                <div v-for="el in cat.items" :key="el.type" class="el-card" :class="{ 'el-card--new': el.isNew }"
                  draggable="true" @dragstart="onDragStart($event, el)" @dblclick="addCenter(el)"
                  :title="`${el.label} — drag or double-click to add`">
                  <div class="el-icon-wrap"><i :class="el.icon" /></div>
                  <span class="el-label">{{ el.label }}</span>
                  <span v-if="el.isNew" class="new-badge">NEW</span>
                </div>
              </template>

            </div><!-- el-grid -->
          </div><!-- el-cat -->

          <div v-if="!filteredCats.length" class="empty-hint">
            <i class="fa-solid fa-magnifying-glass" />
            <p>No elements match "{{ elSearch }}"</p>
          </div>
        </div><!-- el-cats -->
      </div>

      <!-- ══ PAGES TAB ══════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'pages'" class="tab-panel">
        <button class="add-page-btn" @click="$emit('add-page')">
          <i class="fa-solid fa-plus" /> Add New Page
        </button>
        <div class="pages-list">
          <div v-for="(page, pi) in report.content" :key="page.id" class="page-card"
            :class="{ active: currentPage === pi }" @click="$emit('select-page', pi)" @dblclick="startRename(pi)"
            draggable="true" @dragstart="pageDragStart($event, pi)" @dragover.prevent="pageDragOver($event, pi)"
            @drop="pageDrop($event, pi)">
            <!-- Mini page preview -->
            <div class="page-mini-wrap">
              <div class="page-mini" :style="{ background: settings.background_color || '#fff' }">
                <div v-for="el in (page.elements || []).slice(0, 12)" :key="el.id" class="mini-el"
                  :style="getMiniElStyle(el)" />
                <div v-if="!page.elements.length" class="mini-empty">
                  <i class="fa-solid fa-plus" />
                </div>
              </div>
            </div>
            <!-- Page info -->
            <div class="page-info">
              <div class="page-name-wrap">
                <span v-if="renamingPage !== pi" class="page-name">
                  {{ page.label || `Page ${pi + 1}` }}
                </span>
                <input v-else :value="page.label || `Page ${pi + 1}`" @blur="finishRename(pi, $event.target.value)"
                  @keydown.enter="finishRename(pi, $event.target.value)" @click.stop class="page-rename-input" />
              </div>
              <span class="page-el-count">{{ (page.elements || []).length }} el</span>
            </div>
            <!-- Page actions -->
            <div class="page-actions">
              <button @click.stop="$emit('duplicate-page', pi)" title="Duplicate"><i
                  class="fa-solid fa-copy" /></button>
              <button class="danger" @click.stop="$emit('delete-page', pi)" :disabled="report.content.length <= 1"
                title="Delete"><i class="fa-solid fa-trash" /></button>
            </div>
            <div v-if="currentPage === pi" class="page-active-glow" />
          </div>
        </div>
      </div>

      <!-- ══ LAYERS TAB ═════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'layers'" class="tab-panel">
        <div class="layers-header">
          <span>Layers <span class="layer-count">{{ currentPageElements.length }}</span></span>
          <button class="micro-btn" @click="$emit('deselect-all')" title="Deselect All"><i
              class="fa-solid fa-xmark" /></button>
        </div>
        <div class="layers-list">
          <div v-for="(el, ri) in reversedElements" :key="el.id" class="layer-item" :class="{
            selected: isLayerSelected(ri),
            locked: el.locked,
            hidden: el.visible === false,
          }" @click="selectLayer(ri)">
            <i class="fa-solid fa-grip-vertical drag-handle" />
            <i :class="getElIcon(el.type)" class="layer-type-icon" :style="{ color: getTypeColor(el.type) }" />
            <div class="layer-info">
              <span class="layer-name">{{ getLayerName(el) }}</span>
              <span class="layer-type">{{ el.type }}</span>
            </div>
            <div class="layer-ctrls">
              <button @click.stop="$emit('toggle-visibility', getRealIdx(ri))"
                :title="el.visible === false ? 'Show' : 'Hide'">
                <i :class="el.visible === false ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
              </button>
              <button @click.stop="$emit('toggle-lock', getRealIdx(ri))" :title="el.locked ? 'Unlock' : 'Lock'">
                <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
              </button>
            </div>
          </div>
          <div v-if="!currentPageElements.length" class="empty-hint">
            <i class="fa-solid fa-layer-group" />
            <p>No elements on this page</p>
          </div>
        </div>
      </div>

      <!-- ══ MEDIA TAB ══════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'media'" class="tab-panel">
        <!-- Upload zone -->
        <div class="upload-zone" @click="triggerUpload" @dragover.prevent @drop.prevent="onMediaDrop">
          <i class="fa-solid fa-cloud-arrow-up" />
          <span>Upload Images</span>
          <small>or drag & drop files here</small>
        </div>
        <input ref="mediaFileInput" type="file" accept="image/*" multiple class="hidden" @change="onMediaFileInput" />

        <!-- Stock photo search -->
        <div class="media-section">
          <div class="media-section-title"><i class="fa-solid fa-images" /> Free Stock Photos</div>
          <div class="search-wrap" style="margin-bottom:6px">
            <input v-model="stockQuery" class="search-input" placeholder="Search stock photos…"
              @keydown.enter="searchStock" />
            <button class="micro-btn" @click="searchStock" :disabled="stockLoading">
              <i :class="stockLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-magnifying-glass'" />
            </button>
          </div>
          <div class="stock-grid" v-if="stockImages.length">
            <div v-for="img in stockImages" :key="img.id" class="stock-item" @click="addStockImage(img)">
              <img :src="img.thumb" loading="lazy" />
              <div class="stock-overlay"><i class="fa-solid fa-plus" /></div>
            </div>
          </div>
        </div>

        <!-- Uploaded images -->
        <div class="media-section" v-if="uploadedImages.length">
          <div class="media-section-title"><i class="fa-solid fa-folder-open" /> Uploaded</div>
          <div class="uploaded-grid">
            <div v-for="img in uploadedImages" :key="img.url" class="uploaded-item" draggable="true"
              @dragstart="e => e.dataTransfer.setData('el-def', JSON.stringify({ type: 'image', w: 300, h: 200, src: img.url }))"
              @click="addUploadedImage(img)">
              <img :src="img.url" loading="lazy" />
              <span class="uploaded-name">{{ img.name }}</span>
              <button class="remove-img" @click.stop="removeUploaded(img)"><i class="fa-solid fa-xmark" /></button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ TEMPLATES TAB ══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'templates'" class="tab-panel">
        <p class="tab-desc">Click a theme to apply its colors and font to the current report settings.</p>
        <div class="template-grid">
          <div v-for="tpl in QUICK_TEMPLATES" :key="tpl.name" class="tpl-card" @click="applyTemplate(tpl)">
            <div class="tpl-preview" :style="{ background: tpl.gradient }">
              <div class="tpl-hover"><i class="fa-solid fa-check" /></div>
            </div>
            <span class="tpl-name">{{ tpl.name }}</span>
          </div>
        </div>
      </div>

      <!-- ══ SETTINGS TAB ═══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'settings'" class="tab-panel settings-scroll">

        <!-- Page Setup -->
        <div class="settings-section">
          <div class="settings-title"><i class="fa-solid fa-file" /> Page Setup</div>
          <div class="form-group">
            <label>Size</label>
            <select :value="settings.page_size || 'A4'" @change="update('page_size', $event.target.value)"
              class="form-select">
              <option>A4</option>
              <option>Letter</option>
              <option>Legal</option>
              <option>A3</option>
              <option>A5</option>
              <option value="custom">Custom</option>
            </select>
          </div>
          <div v-if="settings.page_size === 'custom'" class="form-row">
            <div class="form-group"><label>W (px)</label><input type="number" :value="settings.custom_w || 794"
                @input="update('custom_w', +$event.target.value)" class="form-input" /></div>
            <div class="form-group"><label>H (px)</label><input type="number" :value="settings.custom_h || 1123"
                @input="update('custom_h', +$event.target.value)" class="form-input" /></div>
          </div>
          <div class="form-group">
            <label>Orientation</label>
            <div class="toggle-group">
              <button :class="{ active: (settings.orientation || 'portrait') === 'portrait' }"
                @click="update('orientation', 'portrait')"><i class="fa-solid fa-mobile-screen-button" />
                Portrait</button>
              <button :class="{ active: settings.orientation === 'landscape' }"
                @click="update('orientation', 'landscape')"><i class="fa-solid fa-tablet-screen-button fa-rotate-90" />
                Landscape</button>
            </div>
          </div>
          <div class="form-group">
            <label>Margin: {{ settings.margin || 40 }}px</label>
            <input type="range" min="0" max="120" :value="settings.margin || 40"
              @input="update('margin', +$event.target.value)" class="form-range" />
          </div>
          <div class="form-group">
            <label>Page Radius: {{ settings.page_radius || 0 }}px</label>
            <input type="range" min="0" max="40" :value="settings.page_radius || 0"
              @input="update('page_radius', +$event.target.value)" class="form-range" />
          </div>
        </div>

        <!-- Colors -->
        <div class="settings-section">
          <div class="settings-title"><i class="fa-solid fa-palette" /> Colors</div>
          <div class="form-group">
            <label>Primary Color</label>
            <div class="color-row">
              <input type="color" :value="settings.primary_color || '#6366f1'"
                @input="update('primary_color', $event.target.value)" class="color-input" />
              <input type="text" :value="settings.primary_color || '#6366f1'"
                @input="update('primary_color', $event.target.value)" class="form-input mono" />
            </div>
          </div>
          <div class="form-group">
            <label>Accent Color</label>
            <div class="color-row">
              <input type="color" :value="settings.accent_color || '#8b5cf6'"
                @input="update('accent_color', $event.target.value)" class="color-input" />
              <input type="text" :value="settings.accent_color || '#8b5cf6'"
                @input="update('accent_color', $event.target.value)" class="form-input mono" />
            </div>
          </div>
          <div class="form-group">
            <label>Background</label>
            <div class="color-row">
              <input type="color" :value="settings.background_color || '#ffffff'"
                @input="update('background_color', $event.target.value)" class="color-input" />
              <input type="text" :value="settings.background_color || '#ffffff'"
                @input="update('background_color', $event.target.value)" class="form-input mono" />
            </div>
          </div>
          <div class="form-group">
            <label>Text Color</label>
            <div class="color-row">
              <input type="color" :value="settings.text_color || '#1e293b'"
                @input="update('text_color', $event.target.value)" class="color-input" />
              <input type="text" :value="settings.text_color || '#1e293b'"
                @input="update('text_color', $event.target.value)" class="form-input mono" />
            </div>
          </div>
          <div class="form-group">
            <label>Background Image URL</label>
            <input type="text" :value="settings.bg_image || ''" @input="update('bg_image', $event.target.value)"
              class="form-input" placeholder="https://…" />
          </div>
        </div>

        <!-- Typography -->
        <div class="settings-section">
          <div class="settings-title"><i class="fa-solid fa-font" /> Typography</div>
          <div class="form-group">
            <label>Font Family</label>
            <select :value="settings.font_family || 'Inter'" @change="update('font_family', $event.target.value)"
              class="form-select">
              <option v-for="f in FONTS" :key="f.v" :value="f.v">{{ f.l }}</option>
            </select>
          </div>
          <div class="form-group">
            <label>Base Size: {{ settings.font_size || 14 }}px</label>
            <input type="range" min="10" max="24" :value="settings.font_size || 14"
              @input="update('font_size', +$event.target.value)" class="form-range" />
          </div>
          <div class="form-group">
            <label>Direction</label>
            <div class="toggle-group">
              <button :class="{ active: !settings.rtl }" @click="update('rtl', false)">LTR →</button>
              <button :class="{ active: settings.rtl }" @click="update('rtl', true)">← RTL</button>
            </div>
          </div>
        </div>

        <!-- Header & Footer -->
        <div class="settings-section">
          <div class="settings-title"><i class="fa-solid fa-heading" /> Header</div>
          <div class="switch-row">
            <span>Show Header</span>
            <button class="toggle-switch" :class="{ on: settings.show_header }"
              @click="update('show_header', !settings.show_header)">
              <span class="switch-thumb" />
            </button>
          </div>
          <template v-if="settings.show_header">
            <div class="form-group"><label>Header Text</label><input type="text" :value="settings.header_text || ''"
                @input="update('header_text', $event.target.value)" class="form-input" /></div>
            <div class="form-group">
              <label>Header Color</label>
              <div class="color-row">
                <input type="color" :value="settings.header_color || '#1e293b'"
                  @input="update('header_color', $event.target.value)" class="color-input" />
                <input type="text" :value="settings.header_color || '#1e293b'"
                  @input="update('header_color', $event.target.value)" class="form-input mono" />
              </div>
            </div>
          </template>

          <div class="settings-title" style="margin-top:8px"><i class="fa-solid fa-shoe-prints fa-rotate-180" /> Footer
          </div>
          <div class="switch-row">
            <span>Show Footer</span>
            <button class="toggle-switch" :class="{ on: settings.show_footer }"
              @click="update('show_footer', !settings.show_footer)">
              <span class="switch-thumb" />
            </button>
          </div>
          <template v-if="settings.show_footer">
            <div class="form-group"><label>Footer Left</label><input type="text" :value="settings.footer_left || ''"
                @input="update('footer_left', $event.target.value)" class="form-input" /></div>
            <div class="form-group"><label>Footer Right</label><input type="text" :value="settings.footer_right || ''"
                @input="update('footer_right', $event.target.value)" class="form-input" placeholder="{n} / total" />
            </div>
          </template>
          <div class="switch-row">
            <span>Page Numbers</span>
            <button class="toggle-switch" :class="{ on: settings.show_page_numbers }"
              @click="update('show_page_numbers', !settings.show_page_numbers)">
              <span class="switch-thumb" />
            </button>
          </div>
        </div>

        <!-- Watermark -->
        <div class="settings-section">
          <div class="settings-title"><i class="fa-solid fa-water" /> Watermark</div>
          <div class="form-group"><label>Text</label><input type="text" :value="settings.watermark || ''"
              @input="update('watermark', $event.target.value)" class="form-input" placeholder="DRAFT, CONFIDENTIAL…" />
          </div>
          <div v-if="settings.watermark" class="form-group">
            <label>Opacity: {{ settings.watermark_opacity || 8 }}%</label>
            <input type="range" min="1" max="30" :value="settings.watermark_opacity || 8"
              @input="update('watermark_opacity', +$event.target.value)" class="form-range" />
          </div>
        </div>

      </div>

      <!-- ══ HISTORY TAB ════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'history'" class="tab-panel">
        <button class="btn-secondary full-width" @click="loadVersions" :disabled="versionsLoading">
          <i :class="versionsLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-rotate'" />
          {{ versionsLoading ? 'Loading…' : 'Refresh History' }}
        </button>
        <div class="versions-list">
          <div v-for="(ver, vi) in versions" :key="ver.id" class="version-item" :class="{ current: vi === 0 }">
            <div class="ver-dot" />
            <div v-if="vi < versions.length - 1" class="ver-line" />
            <div class="ver-body">
              <div class="ver-header">
                <strong>v{{ ver.version_number }}</strong>
                <span v-if="vi === 0" class="current-badge">Current</span>
              </div>
              <div class="ver-label">{{ ver.label || 'Auto-saved' }}</div>
              <div class="ver-date">{{ formatDate(ver.created_at) }}</div>
            </div>
            <button class="btn-secondary btn-sm" @click="restoreVersion(ver.id)" :disabled="vi === 0">Restore</button>
          </div>
          <div v-if="!versions.length" class="empty-hint">
            <i class="fa-solid fa-clock-rotate-left" />
            <p>No versions yet. Versions are saved automatically every 5 minutes.</p>
          </div>
        </div>
      </div>

    </div><!-- tab-body -->
  </aside>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'

const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
  currentPage: { type: Number, default: 0 },
  selectedElIdx: { type: [Number, null], default: null },
  selectedEls: { type: Array, default: () => [] },
  activeTab: { type: String, default: 'elements' },
  isCollapsed: { type: Boolean, default: false },
})

const emit = defineEmits([
  'add-element-center', 'select-page', 'add-page', 'duplicate-page', 'delete-page', 'rename-page',
  'select-element', 'deselect-all', 'toggle-visibility', 'toggle-lock',
  'update:settings', 'canvas-drag-start', 'move-page', 'update:is-collapsed',
])

// ── State ──────────────────────────────────────────────────────────────────────
const activeTab = ref(props.activeTab || 'elements')
const elSearch = ref('')
const collapsedCats = ref([])
const renamingPage = ref(null)
const mediaFileInput = ref(null)
const stockQuery = ref('business')
const stockImages = ref([])
const stockLoading = ref(false)
const uploadedImages = ref([])
const versions = ref([])
const versionsLoading = ref(false)
const selectedChartType = ref('bar-chart')
let pageDragFrom = null

// ── Constants ──────────────────────────────────────────────────────────────────
const TABS = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-shapes' },
  { id: 'pages', label: 'Pages', icon: 'fa-solid fa-copy' },
  { id: 'layers', label: 'Layers', icon: 'fa-solid fa-layer-group' },
  { id: 'media', label: 'Media', icon: 'fa-solid fa-image' },
  { id: 'templates', label: 'Themes', icon: 'fa-solid fa-paint-roller' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-sliders' },
  { id: 'history', label: 'History', icon: 'fa-solid fa-clock-rotate-left' },
]

const QUICK = [
  { type: 'heading', label: 'Heading', icon: 'fa-solid fa-heading', w: 350, h: 60 },
  { type: 'text', label: 'Text', icon: 'fa-solid fa-align-left', w: 260, h: 60 },
  { type: 'image', label: 'Image', icon: 'fa-solid fa-image', w: 300, h: 200 },
  { type: 'table', label: 'Table', icon: 'fa-solid fa-table', w: 460, h: 220 },
  { type: 'metric', label: 'KPI', icon: 'fa-solid fa-chart-simple', w: 200, h: 120 },
  { type: 'richtext', label: 'Rich Text', icon: 'fa-solid fa-file-word', w: 420, h: 220 },
]

const CHART_TYPES = [
  { value: 'bar-chart', label: 'Bar Chart' },
  { value: 'line-chart', label: 'Line Chart' },
  { value: 'area-chart', label: 'Area Chart' },
  { value: 'pie-chart', label: 'Pie Chart' },
  { value: 'doughnut-chart', label: 'Doughnut Chart' },
  { value: 'radar-chart', label: 'Radar Chart' },
  { value: 'polar-chart', label: 'Polar Area' },
  { value: 'scatter-chart', label: 'Scatter Plot' },
]

const FONTS = [
  { v: "'DM Sans', sans-serif", l: 'DM Sans' },
  { v: "'Inter', sans-serif", l: 'Inter' },
  { v: "'Plus Jakarta Sans', sans-serif", l: 'Plus Jakarta Sans' },
  { v: "'Space Grotesk', sans-serif", l: 'Space Grotesk' },
  { v: "'Sora', sans-serif", l: 'Sora' },
  { v: "'Outfit', sans-serif", l: 'Outfit' },
  { v: "'Nunito', sans-serif", l: 'Nunito' },
  { v: "Georgia, serif", l: 'Georgia' },
  { v: "'Playfair Display', serif", l: 'Playfair Display' },
  { v: "'Times New Roman', serif", l: 'Times New Roman' },
  { v: "'Fira Code', monospace", l: 'Fira Code' },
]

const QUICK_TEMPLATES = [
  { name: 'Executive Dark', gradient: 'linear-gradient(135deg,#0f172a,#6366f1)', primary: '#6366f1', bg: '#0f172a', text: '#e2e8f0', font: "'DM Sans',sans-serif" },
  { name: 'Modern Minimal', gradient: 'linear-gradient(135deg,#f0fdf4,#10b981)', primary: '#10b981', bg: '#ffffff', text: '#1e293b', font: "'DM Sans',sans-serif" },
  { name: 'Bold Analytics', gradient: 'linear-gradient(135deg,#1e293b,#f59e0b)', primary: '#f59e0b', bg: '#1e293b', text: '#f8fafc', font: "'DM Sans',sans-serif" },
  { name: 'Clean Professional', gradient: 'linear-gradient(135deg,#eef2ff,#6366f1)', primary: '#6366f1', bg: '#ffffff', text: '#1e293b', font: "'DM Sans',sans-serif" },
  { name: 'Healthcare Blue', gradient: 'linear-gradient(135deg,#f0f9ff,#0ea5e9)', primary: '#0ea5e9', bg: '#ffffff', text: '#0c4a6e', font: "'DM Sans',sans-serif" },
  { name: 'Tech Dark', gradient: 'linear-gradient(135deg,#0d1117,#2563eb)', primary: '#2563eb', bg: '#0d1117', text: '#e6edf3', font: "'DM Sans',sans-serif" },
  { name: 'Rose & Gold', gradient: 'linear-gradient(135deg,#fff1f2,#f43f5e)', primary: '#f43f5e', bg: '#ffffff', text: '#1e293b', font: "'Playfair Display',serif" },
  { name: 'Forest Green', gradient: 'linear-gradient(135deg,#f0fdf4,#16a34a)', primary: '#16a34a', bg: '#ffffff', text: '#1e293b', font: "'DM Sans',sans-serif" },
]

// Element catalog
const ALL_CATEGORIES = [
  {
    name: 'Typography', icon: 'fa-solid fa-font',
    items: [
      { type: 'text', label: 'Text', icon: 'fa-solid fa-align-left', w: 260, h: 60 },
      { type: 'heading', label: 'Heading', icon: 'fa-solid fa-heading', w: 350, h: 64 },
      { type: 'subheading', label: 'Subheading', icon: 'fa-solid fa-h', w: 280, h: 46 },
      { type: 'quote', label: 'Quote', icon: 'fa-solid fa-quote-right', w: 320, h: 80 },
      { type: 'blockquote', label: 'Blockquote', icon: 'fa-solid fa-quote-left', w: 300, h: 80 },
      { type: 'highlight', label: 'Highlight', icon: 'fa-solid fa-highlighter', w: 260, h: 40, isNew: true },
      { type: 'badge', label: 'Badge', icon: 'fa-solid fa-tag', w: 120, h: 36 },
      { type: 'code', label: 'Code Block', icon: 'fa-solid fa-code', w: 360, h: 140 },
      { type: 'link', label: 'Link', icon: 'fa-solid fa-link', w: 200, h: 36 },
      { type: 'richtext', label: 'Rich Text', icon: 'fa-solid fa-file-word', w: 420, h: 220, isNew: true },
    ],
  },
  {
    name: 'Charts & Graphs', icon: 'fa-solid fa-chart-bar',
    items: [], // Handled by special chart type selector UI
  },
  {
    name: 'Data & KPIs', icon: 'fa-solid fa-chart-simple',
    items: [
      { type: 'table', label: 'Table', icon: 'fa-solid fa-table', w: 460, h: 220 },
      { type: 'metric', label: 'KPI Card', icon: 'fa-solid fa-chart-simple', w: 200, h: 120 },
      { type: 'stat-row', label: 'Stat Row', icon: 'fa-solid fa-bars-staggered', w: 460, h: 80 },
      { type: 'progress', label: 'Progress Bar', icon: 'fa-solid fa-bars-progress', w: 360, h: 56 },
      { type: 'circular-progress', label: 'Circular %', icon: 'fa-solid fa-circle-notch', w: 140, h: 140, isNew: true },
      { type: 'sparkline', label: 'Sparkline', icon: 'fa-solid fa-wave-square', w: 200, h: 48, isNew: true },
    ],
  },
  {
    name: 'Content Blocks', icon: 'fa-solid fa-rectangle-list',
    items: [
      { type: 'checklist', label: 'Checklist', icon: 'fa-solid fa-list-check', w: 300, h: 200 },
      { type: 'timeline', label: 'Timeline', icon: 'fa-solid fa-timeline', w: 440, h: 260 },
      { type: 'callout', label: 'Callout Box', icon: 'fa-solid fa-lightbulb', w: 380, h: 100 },
      { type: 'testimonial', label: 'Testimonial', icon: 'fa-solid fa-comment-dots', w: 360, h: 160 },
      { type: 'signature', label: 'Signature', icon: 'fa-solid fa-signature', w: 220, h: 100 },
      { type: 'price-card', label: 'Price Card', icon: 'fa-solid fa-tags', w: 220, h: 300, isNew: true },
      { type: 'social-card', label: 'Social Card', icon: 'fa-solid fa-id-card', w: 200, h: 180, isNew: true },
      { type: 'kanban', label: 'Kanban Card', icon: 'fa-solid fa-columns', w: 220, h: 120, isNew: true },
      { type: 'toc', label: 'Table of Contents', icon: 'fa-solid fa-list-ol', w: 360, h: 200 },
      { type: 'html-embed', label: 'HTML Embed', icon: 'fa-solid fa-code', w: 380, h: 200, isNew: true },
    ],
  },
  {
    name: 'Media', icon: 'fa-solid fa-photo-film',
    items: [
      { type: 'image', label: 'Image', icon: 'fa-solid fa-image', w: 300, h: 200 },
      { type: 'video', label: 'YouTube Video', icon: 'fa-solid fa-video', w: 400, h: 250, isNew: true },
      { type: 'map', label: 'Google Map', icon: 'fa-solid fa-map-location-dot', w: 400, h: 260, isNew: true },
      { type: 'qr-code', label: 'QR Code', icon: 'fa-solid fa-qrcode', w: 150, h: 150, isNew: true },
      { type: 'icon', label: 'Emoji / Icon', icon: 'fa-solid fa-face-smile', w: 60, h: 60 },
      { type: 'rating', label: 'Star Rating', icon: 'fa-solid fa-star', w: 160, h: 40 },
    ],
  },
  {
    name: 'Shapes & Lines', icon: 'fa-solid fa-shapes',
    items: [
      { type: 'rectangle', label: 'Rectangle', icon: 'fa-solid fa-square', w: 200, h: 120 },
      { type: 'circle', label: 'Circle', icon: 'fa-solid fa-circle', w: 120, h: 120 },
      { type: 'triangle', label: 'Triangle', icon: 'fa-solid fa-play', w: 120, h: 100 },
      { type: 'star', label: 'Star', icon: 'fa-solid fa-star', w: 80, h: 80 },
      { type: 'divider', label: 'Divider', icon: 'fa-solid fa-minus', w: 500, h: 4 },
      { type: 'arrow', label: 'Arrow', icon: 'fa-solid fa-arrow-right', w: 200, h: 40 },
    ],
  },
  {
    name: 'Utilities', icon: 'fa-solid fa-gear',
    items: [
      { type: 'pagenum', label: 'Page Number', icon: 'fa-solid fa-hashtag', w: 60, h: 30 },
      { type: 'date', label: 'Date', icon: 'fa-solid fa-calendar', w: 200, h: 28 },
      { type: 'watermark', label: 'Watermark Text', icon: 'fa-solid fa-water', w: 400, h: 100 },
    ],
  },
]

// ── Computed ───────────────────────────────────────────────────────────────────
const currentPageElements = computed(() => props.report.content[props.currentPage]?.elements || [])
const reversedElements = computed(() => [...currentPageElements.value].reverse())

const filteredCats = computed(() => {
  if (!elSearch.value) return ALL_CATEGORIES
  const q = elSearch.value.toLowerCase()
  return ALL_CATEGORIES
    .map(c => ({
      ...c,
      items: c.items.filter(i => i.label.toLowerCase().includes(q) || i.type.includes(q))
    }))
    .filter(c => c.name === 'Charts & Graphs' ? q.includes('chart') || q.includes('graph') : c.items.length)
})

// ── Element helpers ────────────────────────────────────────────────────────────
function getChartLabel(type) { return CHART_TYPES.find(c => c.value === type)?.label || type }
function getChartIcon(type) {
  const m = {
    'bar-chart': 'fa-solid fa-chart-bar',
    'line-chart': 'fa-solid fa-chart-line',
    'area-chart': 'fa-solid fa-chart-area',
    'pie-chart': 'fa-solid fa-chart-pie',
    'doughnut-chart': 'fa-solid fa-circle-half-stroke',
    'radar-chart': 'fa-solid fa-compass',
    'polar-chart': 'fa-solid fa-sun',
    'scatter-chart': 'fa-solid fa-braille',
  }
  return m[type] || 'fa-solid fa-chart-bar'
}

function addCenter(def) {
  emit('add-element-center', def)
}

function onDragStart(e, def) {
  e.dataTransfer.setData('el-def', JSON.stringify(def))
  e.dataTransfer.effectAllowed = 'copy'
  emit('canvas-drag-start', e, def)
}

function toggleCat(name) {
  const i = collapsedCats.value.indexOf(name)
  if (i >= 0) collapsedCats.value.splice(i, 1)
  else collapsedCats.value.push(name)
}

// ── Settings update helper (isolated — never touches html/body) ─────────────
function update(key, val) {
  emit('update:settings', { ...props.settings, [key]: val })
}

// ── Template apply ─────────────────────────────────────────────────────────────
function applyTemplate(tpl) {
  emit('update:settings', {
    ...props.settings,
    primary_color: tpl.primary,
    background_color: tpl.bg,
    text_color: tpl.text,
    font_family: tpl.font,
  })
}

// ── Page rename ────────────────────────────────────────────────────────────────
function startRename(pi) {
  renamingPage.value = pi
  nextTick(() => {
    const inputs = document.querySelectorAll('.page-rename-input')
    const last = inputs[inputs.length - 1]
    last?.focus(); last?.select()
  })
}
function finishRename(pi, val) {
  renamingPage.value = null
  emit('rename-page', pi, val || `Page ${pi + 1}`)
}

// ── Page drag reorder ──────────────────────────────────────────────────────────
function pageDragStart(e, pi) { pageDragFrom = pi; e.dataTransfer.effectAllowed = 'move' }
function pageDragOver(e, pi) { e.dataTransfer.dropEffect = 'move' }
function pageDrop(e, pi) {
  if (pageDragFrom === null || pageDragFrom === pi) return
  emit('move-page', pageDragFrom, pi)
  pageDragFrom = null
}

// ── Layer helpers ──────────────────────────────────────────────────────────────
function getRealIdx(ri) { return currentPageElements.value.length - 1 - ri }
function isLayerSelected(ri) {
  const idx = getRealIdx(ri)
  return props.selectedElIdx === idx || props.selectedEls.includes(idx)
}
function selectLayer(ri) { emit('select-element', getRealIdx(ri)) }
function getLayerName(el) {
  const t = (el.content || '').toString().replace(/<[^>]*>/g, '').trim()
  return (t || el.label || el.chartTitle || el.type || 'Untitled').substring(0, 26)
}
function getElIcon(type) {
  const found = ALL_CATEGORIES.flatMap(c => c.items).find(i => i.type === type)
  return found?.icon || getChartIcon(type) || 'fa-solid fa-cube'
}
function getTypeColor(type) {
  if (type?.endsWith('-chart')) return '#06b6d4'
  const m = { table: '#10b981', image: '#ec4899', metric: '#f59e0b', heading: '#8b5cf6', subheading: '#8b5cf6', text: '#6366f1', richtext: '#6366f1' }
  return m[type] || '#94a3b8'
}

// ── Mini page preview ──────────────────────────────────────────────────────────
function getMiniElStyle(el) {
  const s = 0.12
  return {
    position: 'absolute',
    left: (el.position?.x || 0) * s + 'px',
    top: (el.position?.y || 0) * s + 'px',
    width: Math.max(2, (el.styles?.width || 100) * s) + 'px',
    height: Math.max(1, (el.styles?.height || 50) * s) + 'px',
    background: el.styles?.backgroundColor || props.settings.primary_color || '#6366f1',
    opacity: 0.65, borderRadius: '1px',
  }
}

// ── Media ──────────────────────────────────────────────────────────────────────
function triggerUpload() { mediaFileInput.value?.click() }
function onMediaDrop(e) { Array.from(e.dataTransfer.files).forEach(f => { if (f.type.startsWith('image/')) readFile(f) }) }
function onMediaFileInput(e) { Array.from(e.target.files).forEach(readFile); e.target.value = '' }
function readFile(file) {
  const r = new FileReader()
  r.onload = ev => uploadedImages.value.push({ url: ev.target.result, name: file.name })
  r.readAsDataURL(file)
}
function removeUploaded(img) { uploadedImages.value = uploadedImages.value.filter(i => i.url !== img.url) }
function addUploadedImage(img) { emit('add-element-center', { type: 'image', w: 300, h: 200, src: img.url }) }
function addStockImage(img) { emit('add-element-center', { type: 'image', w: 300, h: 200, src: img.url }) }

async function searchStock() {
  stockLoading.value = true
  try {
    const res = await fetch(`/api/unsplash/search?q=${encodeURIComponent(stockQuery.value)}`, { headers: { Accept: 'application/json' } })
    if (res.ok) { const d = await res.json(); stockImages.value = d.images || [] }
    else throw new Error('API error')
  } catch {
    stockImages.value = Array.from({ length: 12 }, (_, i) => ({
      id: i,
      thumb: `https://picsum.photos/160/120?random=${i + Date.now() % 999}`,
      url: `https://picsum.photos/800/600?random=${i + Date.now() % 999}`,
    }))
  }
  stockLoading.value = false
}

// ── Versions ───────────────────────────────────────────────────────────────────
async function loadVersions() {
  versionsLoading.value = true
  try {
    const res = await fetch(route('reports.versions', props.report.slug), { headers: { Accept: 'application/json' } })
    if (res.ok) { const d = await res.json(); versions.value = d.versions || [] }
  } catch { }
  versionsLoading.value = false
}

async function restoreVersion(id) {
  if (!confirm('Restore this version? Unsaved changes will be lost.')) return
  await fetch(route('reports.versions.restore', { report: props.report.slug, version: id }), {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', Accept: 'application/json' },
  })
  window.location.reload()
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
/* ── Panel shell ─────────────────────────────────────────────────────────────── */
.left-panel {
  width: 268px;
  flex-shrink: 0;
  background: var(--bg-panel, #fff);
  border-right: 1px solid var(--border, #e2e8f0);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: width .25s ease;
  position: relative;
  z-index: 50;
}

.left-panel.collapsed {
  width: 0;
  border-right: none;
}

.panel-toggle {
  position: absolute;
  right: -13px;
  top: 50%;
  transform: translateY(-50%);
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: var(--bg-panel, #fff);
  border: 1px solid var(--border, #e2e8f0);
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
  z-index: 10;
  transition: all .15s;
}

.panel-toggle:hover {
  color: #6366f1;
  border-color: #6366f1;
}

/* ── Tab nav ─────────────────────────────────────────────────────────────────── */
.tab-nav {
  display: flex;
  gap: 1px;
  padding: 6px 6px 0;
  border-bottom: 1px solid var(--border, #e2e8f0);
  flex-shrink: 0;
  overflow-x: auto;
  scrollbar-width: none;
}

.tab-nav::-webkit-scrollbar {
  display: none;
}

.tab-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: 6px 4px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 8px;
  font-weight: 600;
  color: var(--text-muted, #94a3b8);
  border-radius: 6px 6px 0 0;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  flex: 1;
  transition: all .15s;
  font-family: inherit;
  white-space: nowrap;
}

.tab-btn i {
  font-size: 12px;
}

.tab-btn:hover {
  color: var(--text-secondary, #475569);
  background: var(--bg-secondary, #f8fafc);
}

.tab-btn.active {
  color: #6366f1;
  border-bottom-color: #6366f1;
}

/* ── Tab body ────────────────────────────────────────────────────────────────── */
.tab-body {
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
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* ── Search ──────────────────────────────────────────────────────────────────── */
.search-wrap {
  display: flex;
  align-items: center;
  gap: 5px;
  background: var(--bg-secondary, #f8fafc);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 8px;
  padding: 5px 8px;
  transition: border-color .15s;
}

.search-wrap:focus-within {
  border-color: #6366f1;
}

.search-icon {
  color: var(--text-muted, #94a3b8);
  font-size: 11px;
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 11px;
  color: var(--text-primary, #0f172a);
  font-family: inherit;
}

.search-input::placeholder {
  color: var(--text-muted, #94a3b8);
}

.search-clear {
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  padding: 0;
}

/* ── Quick chips ─────────────────────────────────────────────────────────────── */
.quick-chips {
  display: flex;
  gap: 3px;
  flex-wrap: wrap;
}

.chip {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 9px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 99px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  font-size: 9px;
  font-weight: 600;
  color: var(--text-secondary, #475569);
  white-space: nowrap;
  transition: all .15s;
  font-family: inherit;
}

.chip:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .06);
  transform: translateY(-1px);
}

.chip i {
  font-size: 9px;
}

/* ── Element categories ──────────────────────────────────────────────────────── */
.el-cats {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.el-cat {
  border: 1px solid var(--border-light, #f1f5f9);
  border-radius: 8px;
  overflow: hidden;
}

.cat-hdr {
  display: flex;
  align-items: center;
  gap: 6px;
  width: 100%;
  padding: 7px 9px;
  border: none;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  font-size: 9px;
  font-weight: 700;
  color: var(--text-secondary, #475569);
  text-transform: uppercase;
  letter-spacing: .06em;
  transition: background .1s;
  font-family: inherit;
}

.cat-hdr:hover {
  background: var(--bg-tertiary, #f1f5f9);
}

.cat-hdr i:first-child {
  font-size: 11px;
  color: #6366f1;
}

.cat-count {
  font-size: 8px;
  color: var(--text-muted, #94a3b8);
  background: var(--bg-panel, #fff);
  padding: 1px 5px;
  border-radius: 99px;
  margin-left: auto;
}

.cat-chev {
  font-size: 8px !important;
  color: var(--text-muted, #94a3b8) !important;
  transition: transform .2s;
}

.cat-chev.rotated {
  transform: rotate(-90deg);
}

/* ── Element grid ────────────────────────────────────────────────────────────── */
.el-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 3px;
  padding: 6px;
}

.el-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 9px 4px;
  border-radius: 7px;
  cursor: grab;
  border: 1px solid transparent;
  transition: all .15s;
  position: relative;
  user-select: none;
}

.el-card:hover {
  background: rgba(99, 102, 241, .07);
  border-color: rgba(99, 102, 241, .2);
  transform: translateY(-2px);
}

.el-card:active {
  cursor: grabbing;
  transform: scale(.95);
}

.el-icon-wrap {
  width: 32px;
  height: 32px;
  border-radius: 7px;
  background: var(--bg-secondary, #f8fafc);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  color: var(--text-secondary, #475569);
  transition: all .15s;
}

.el-card:hover .el-icon-wrap {
  background: rgba(99, 102, 241, .12);
  color: #6366f1;
}

.el-label {
  font-size: 8px;
  font-weight: 600;
  color: var(--text-muted, #94a3b8);
  text-align: center;
  line-height: 1.2;
}

.el-card:hover .el-label {
  color: var(--text-primary, #0f172a);
}

.new-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: #6366f1;
  color: #fff;
  font-size: 6px;
  font-weight: 800;
  padding: 1px 4px;
  border-radius: 99px;
  text-transform: uppercase;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1
  }

  50% {
    opacity: .5
  }
}

/* ── Chart selector ──────────────────────────────────────────────────────────── */
.chart-type-selector {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 4px 0;
}

.chart-type-label {
  font-size: 9px;
  font-weight: 700;
  color: var(--text-secondary, #475569);
  display: flex;
  align-items: center;
  gap: 5px;
}

.chart-type-label i {
  color: #6366f1;
}

.chart-type-select {
  width: 100%;
  padding: 6px 8px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 7px;
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  font-size: 11px;
  font-family: inherit;
  outline: none;
  cursor: pointer;
  transition: border-color .15s;
}

.chart-type-select:focus {
  border-color: #6366f1;
}

.chart-drop-card {
  border: 1.5px dashed var(--border, #e2e8f0);
  border-radius: 8px;
  cursor: grab;
  transition: all .2s;
  overflow: hidden;
}

.chart-drop-card:hover {
  border-color: #6366f1;
  background: rgba(99, 102, 241, .04);
  transform: translateY(-1px);
}

.chart-drop-card:active {
  cursor: grabbing;
  transform: scale(.98);
}

.chart-drop-preview {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-bottom: 1px solid var(--border-light, #f1f5f9);
}

.chart-drop-preview i {
  font-size: 18px;
  color: #6366f1;
  flex-shrink: 0;
}

.chart-drop-preview div {
  min-width: 0;
}

.chart-drop-preview strong {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-primary, #0f172a);
}

.chart-drop-preview span {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
}

.chart-mini-preview {
  height: 60px;
  padding: 6px 10px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.mini-bars {
  display: flex;
  align-items: flex-end;
  gap: 3px;
  height: 100%;
  width: 100%;
}

.mini-bar {
  flex: 1;
  border-radius: 3px 3px 0 0;
  transition: height .3s ease;
  min-height: 4px;
}

.mini-line {
  width: 100%;
  height: 100%;
}

.mini-line svg {
  width: 100%;
  height: 100%;
}

.mini-pie {
  width: 48px;
  height: 48px;
}

.mini-pie svg {
  width: 100%;
  height: 100%;
}

.mini-radar {
  width: 48px;
  height: 48px;
}

.mini-radar svg {
  width: 100%;
  height: 100%;
}

/* ── Pages ───────────────────────────────────────────────────────────────────── */
.add-page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
  padding: 8px;
  border: 1.5px dashed var(--border, #e2e8f0);
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 11px;
  font-weight: 600;
  transition: all .2s;
  font-family: inherit;
}

.add-page-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .04);
}

.pages-list {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.page-card {
  border: 2px solid var(--border, #e2e8f0);
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: all .2s;
  background: var(--bg-primary, #fff);
  position: relative;
}

.page-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
  transform: translateY(-1px);
}

.page-card.active {
  border-color: #6366f1 !important;
}

.page-mini-wrap {
  padding: 6px;
  background: var(--bg-secondary, #f8fafc);
}

.page-mini {
  width: 100%;
  aspect-ratio: 3/4;
  position: relative;
  overflow: hidden;
  border-radius: 4px;
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
  font-size: 16px;
  opacity: .25;
}

.page-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 5px 8px;
  border-top: 1px solid var(--border-light, #f1f5f9);
}

.page-name-wrap {
  flex: 1;
  min-width: 0;
}

.page-name {
  display: block;
  font-size: 10px;
  font-weight: 600;
  color: var(--text-primary, #0f172a);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.page-rename-input {
  font-size: 10px;
  border: 1px solid #6366f1;
  border-radius: 4px;
  padding: 1px 4px;
  outline: none;
  background: var(--bg-panel, #fff);
  color: var(--text-primary, #0f172a);
  width: 100%;
  font-family: inherit;
}

.page-el-count {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
  background: var(--bg-secondary, #f8fafc);
  border-radius: 99px;
  padding: 1px 6px;
  flex-shrink: 0;
}

.page-actions {
  display: flex;
  gap: 2px;
  padding: 0 6px 6px;
}

.page-actions button {
  flex: 1;
  padding: 3px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  transition: all .15s;
}

.page-actions button:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

.page-actions button.danger:hover {
  background: rgba(239, 68, 68, .08);
  color: #ef4444;
}

.page-actions button:disabled {
  opacity: .3;
  cursor: not-allowed;
}

.page-active-glow {
  position: absolute;
  inset: -2px;
  border-radius: 10px;
  border: 2px solid #6366f1;
  pointer-events: none;
  animation: glowPulse 2.4s ease-in-out infinite;
}

@keyframes glowPulse {

  0%,
  100% {
    box-shadow: 0 0 8px rgba(99, 102, 241, .2)
  }

  50% {
    box-shadow: 0 0 20px rgba(99, 102, 241, .45)
  }
}

/* ── Layers ──────────────────────────────────────────────────────────────────── */
.layers-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary, #475569);
  padding: 2px 0 6px;
}

.layer-count {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
  background: var(--bg-secondary, #f8fafc);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 99px;
  padding: 0 6px;
  margin-left: 4px;
}

.micro-btn {
  width: 22px;
  height: 22px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  border-radius: 4px;
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .12s;
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
  gap: 5px;
  padding: 6px 5px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all .12s;
}

.layer-item:hover {
  background: var(--bg-secondary, #f8fafc);
  border-color: var(--border, #e2e8f0);
}

.layer-item.selected {
  background: rgba(99, 102, 241, .08);
  border-color: #6366f1;
}

.layer-item.locked {
  opacity: .5;
}

.layer-item.hidden {
  opacity: .35;
}

.drag-handle {
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  cursor: grab;
  opacity: .4;
  flex-shrink: 0;
}

.layer-type-icon {
  font-size: 12px;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}

.layer-info {
  flex: 1;
  min-width: 0;
}

.layer-name {
  display: block;
  font-size: 10px;
  font-weight: 500;
  color: var(--text-primary, #0f172a);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.layer-type {
  display: block;
  font-size: 8px;
  color: var(--text-muted, #94a3b8);
  text-transform: capitalize;
}

.layer-ctrls {
  display: flex;
  gap: 1px;
  opacity: 0;
  transition: opacity .12s;
}

.layer-item:hover .layer-ctrls {
  opacity: 1;
}

.layer-ctrls button {
  width: 20px;
  height: 20px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  border-radius: 3px;
  font-size: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.layer-ctrls button:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

/* ── Media ───────────────────────────────────────────────────────────────────── */
.upload-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 20px 12px;
  border: 2px dashed var(--border, #e2e8f0);
  border-radius: 10px;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 12px;
  transition: all .2s;
}

.upload-zone:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .04);
}

.upload-zone i {
  font-size: 24px;
  opacity: .5;
}

.media-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.media-section-title {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--text-muted, #94a3b8);
  display: flex;
  align-items: center;
  gap: 5px;
}

.stock-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 3px;
}

.stock-item {
  border-radius: 5px;
  overflow: hidden;
  cursor: pointer;
  aspect-ratio: 4/3;
  border: 1px solid var(--border, #e2e8f0);
  position: relative;
  transition: all .15s;
}

.stock-item:hover {
  transform: scale(1.04);
  border-color: #6366f1;
}

.stock-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.stock-overlay {
  position: absolute;
  inset: 0;
  background: rgba(99, 102, 241, .5);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  opacity: 0;
  transition: opacity .2s;
}

.stock-item:hover .stock-overlay {
  opacity: 1;
}

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
  transition: all .15s;
}

.uploaded-item:hover {
  border-color: #6366f1;
}

.uploaded-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.uploaded-name {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, .6);
  color: #fff;
  font-size: 8px;
  padding: 2px 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.remove-img {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: rgba(0, 0, 0, .5);
  border: none;
  color: #fff;
  font-size: 8px;
  cursor: pointer;
  opacity: 0;
  transition: opacity .15s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.uploaded-item:hover .remove-img {
  opacity: 1;
}

/* ── Templates ───────────────────────────────────────────────────────────────── */
.tab-desc {
  font-size: 10px;
  color: var(--text-muted, #94a3b8);
  line-height: 1.5;
}

.template-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 5px;
}

.tpl-card {
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid var(--border, #e2e8f0);
  transition: all .2s;
}

.tpl-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
  border-color: #6366f1;
}

.tpl-preview {
  height: 44px;
  position: relative;
}

.tpl-hover {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, .3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  opacity: 0;
  transition: opacity .2s;
  font-size: 14px;
}

.tpl-card:hover .tpl-hover {
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

/* ── Settings ────────────────────────────────────────────────────────────────── */
.settings-scroll {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.settings-section {
  padding: 10px;
  border-bottom: 1px solid var(--border-light, #f1f5f9);
}

.settings-title {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--text-muted, #94a3b8);
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.settings-title i {
  color: #6366f1;
}

.form-group {
  margin-bottom: 8px;
}

.form-group label {
  display: block;
  font-size: 9px;
  font-weight: 600;
  color: var(--text-secondary, #475569);
  margin-bottom: 3px;
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
  box-sizing: border-box;
  font-family: inherit;
}

.form-input:focus {
  border-color: #6366f1;
}

.form-input.mono {
  font-family: 'Courier New', monospace;
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
  accent-color: #6366f1;
  cursor: pointer;
  height: 4px;
}

.color-row {
  display: flex;
  gap: 5px;
  align-items: center;
}

.color-input {
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
  padding: 5px 6px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  font-size: 9px;
  font-weight: 600;
  color: var(--text-muted, #94a3b8);
  transition: all .15s;
  font-family: inherit;
}

.toggle-group button.active {
  background: rgba(99, 102, 241, .1);
  color: #6366f1;
  border-color: #6366f1;
}

.switch-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
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
  transition: background .2s;
}

.toggle-switch.on {
  background: #6366f1;
}

.switch-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
  transition: transform .2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
}

.toggle-switch.on .switch-thumb {
  transform: translateX(16px);
}

/* ── Versions ────────────────────────────────────────────────────────────────── */
.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 6px 10px;
  border: 1px solid var(--border, #e2e8f0);
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  border-radius: 6px;
  cursor: pointer;
  font-size: 10px;
  font-weight: 500;
  transition: all .15s;
  font-family: inherit;
}

.btn-secondary:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .06);
}

.btn-secondary:disabled {
  opacity: .4;
  cursor: not-allowed;
}

.btn-secondary.full-width {
  width: 100%;
}

.btn-secondary.btn-sm {
  padding: 3px 8px;
  font-size: 9px;
}

.versions-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.version-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 8px 6px;
  border-radius: 6px;
  border: 1px solid transparent;
  transition: all .15s;
  position: relative;
}

.version-item:hover {
  background: var(--bg-secondary, #f8fafc);
  border-color: var(--border, #e2e8f0);
}

.version-item.current {
  background: rgba(99, 102, 241, .06);
  border-color: #6366f1;
}

.ver-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #6366f1;
  flex-shrink: 0;
  margin-top: 3px;
}

.ver-line {
  position: absolute;
  left: 17px;
  top: 20px;
  bottom: -8px;
  width: 2px;
  background: var(--border, #e2e8f0);
}

.ver-body {
  flex: 1;
  min-width: 0;
}

.ver-header {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 2px;
}

.ver-header strong {
  font-size: 11px;
  color: var(--text-primary, #0f172a);
}

.current-badge {
  font-size: 8px;
  font-weight: 700;
  background: #6366f1;
  color: #fff;
  padding: 1px 5px;
  border-radius: 99px;
}

.ver-label {
  font-size: 10px;
  color: var(--text-secondary, #475569);
}

.ver-date {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
}

/* ── Empty ───────────────────────────────────────────────────────────────────── */
.empty-hint {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 24px;
  color: var(--text-muted, #94a3b8);
  text-align: center;
}

.empty-hint i {
  font-size: 28px;
  opacity: .25;
}

.empty-hint p {
  font-size: 10px;
  font-weight: 500;
  line-height: 1.5;
}

.hidden {
  display: none !important;
}

/* ── Responsive ──────────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .left-panel {
    position: fixed;
    left: 0;
    top: 48px;
    bottom: 0;
    z-index: 150;
    box-shadow: 8px 0 32px rgba(0, 0, 0, .15);
  }

  .left-panel.collapsed {
    width: 0;
    box-shadow: none;
  }
}
</style>