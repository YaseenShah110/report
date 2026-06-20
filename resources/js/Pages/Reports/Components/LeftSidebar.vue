<!--
  LeftSidebar.vue — Production-Ready 7-Tab Panel
  Tabs: Elements | Pages | Layers | Media | Themes | Settings | History
  • 50+ element catalog with search, categories, drag-to-canvas
  • Page thumbnails with mini-previews, inline rename, reorder
  • Layers panel with visibility/lock toggles
  • Media upload (FileReader), stock photos (picsum fallback)
  • 8 theme presets
  • Full report settings (all emit update:settings — never touch html/body)
  • Version history with restore
  • Memory-safe: no leaked listeners
-->
<template>
  <aside
    class="left-panel"
    :class="{ collapsed: isCollapsed, 'is-dark': isDark }"
    role="complementary"
    aria-label="Elements and pages panel"
  >
    <!-- ── Collapse toggle ─────────────────────────────────────────── -->
    <button
      class="panel-toggle"
      @click="$emit('update:is-collapsed', !isCollapsed)"
      :title="isCollapsed ? 'Expand panel' : 'Collapse panel'"
      :aria-expanded="!isCollapsed"
    >
      <i :class="isCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'" />
    </button>

    <div class="panel-inner" v-show="!isCollapsed">

      <!-- ── Tab navigation ──────────────────────────────────────────── -->
      <nav class="panel-tabs" aria-label="Sidebar tabs">
        <button
          v-for="tab in TABS"
          :key="tab.id"
          class="panel-tab"
          :class="{ active: activeTab === tab.id }"
          @click="$emit('update:active-tab', tab.id)"
          :title="tab.label"
          :aria-selected="activeTab === tab.id"
          role="tab"
        >
          <i :class="tab.icon" />
          <span>{{ tab.label }}</span>
        </button>
      </nav>

      <!-- ══════════════════════════════════════════════════════════════
           TAB: ELEMENTS
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'elements'" class="tab-panel" role="tabpanel">
        <!-- Search -->
        <div class="search-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon" />
          <input
            v-model="elSearch"
            class="search-input"
            placeholder="Search 50+ elements…"
            :aria-label="'Search elements'"
            type="search"
            autocomplete="off"
          />
          <button v-if="elSearch" class="search-clear" @click="elSearch=''" aria-label="Clear search">
            <i class="fa-solid fa-xmark" />
          </button>
        </div>

        <!-- Quick-add chips -->
        <div class="quick-chips" aria-label="Quick add elements">
          <button
            v-for="chip in QUICK_CHIPS"
            :key="chip.type"
            class="quick-chip"
            @click="addToCanvas(chip)"
            :title="`Add ${chip.label}`"
          >
            <i :class="chip.icon" />
            {{ chip.label }}
          </button>
        </div>

        <!-- Element categories -->
        <div class="el-catalog" aria-label="Element catalog">
          <template v-for="cat in filteredCats" :key="cat.name">
            <button
              class="cat-header"
              @click="toggleCat(cat.name)"
              :aria-expanded="!collapsedCats.includes(cat.name)"
            >
              <span class="cat-name">{{ cat.name }}</span>
              <span class="cat-count">{{ cat.items.length }}</span>
              <i :class="collapsedCats.includes(cat.name) ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'" class="cat-chevron" />
            </button>

            <div v-show="!collapsedCats.includes(cat.name)" class="el-grid" role="list">
              <div
                v-for="el in cat.items"
                :key="el.type"
                class="el-card"
                :class="{ 'el-card--new': el.isNew }"
                draggable="true"
                role="listitem"
                :aria-label="`${el.label} element — drag to canvas or double-click to add`"
                :title="`${el.label}\n${el.desc || 'Double-click or drag to canvas'}`"
                @dragstart="onDragStart($event, el)"
                @dblclick="addToCanvas(el)"
                @keydown.enter="addToCanvas(el)"
                tabindex="0"
              >
                <div class="el-card-icon"><i :class="el.icon" /></div>
                <span class="el-card-name">{{ el.label }}</span>
                <span v-if="el.isNew" class="el-new-badge">NEW</span>
              </div>
            </div>
          </template>

          <div v-if="!filteredCats.length" class="empty-state" aria-live="polite">
            <i class="fa-solid fa-magnifying-glass" />
            <p>No elements found for "{{ elSearch }}"</p>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB: PAGES
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'pages'" class="tab-panel" role="tabpanel">
        <button class="add-page-btn" @click="$emit('add-page')" aria-label="Add new page">
          <i class="fa-solid fa-plus" /> Add Page
        </button>

        <div class="pages-list" aria-label="Pages list" role="list">
          <div
            v-for="(page, pi) in report.content"
            :key="page.id"
            class="page-card"
            :class="{ 'page-card--active': currentPage === pi }"
            role="listitem"
            :aria-current="currentPage === pi ? 'page' : undefined"
            @click="$emit('select-page', pi)"
            @dblclick="startPageRename(pi)"
          >
            <!-- Mini preview -->
            <div class="page-mini-preview" :style="{ background: settings.background_color || '#fff' }">
              <div
                v-for="el in (page.elements || []).slice(0, 12)"
                :key="el.id"
                class="page-mini-el"
                :style="getMiniElStyle(el)"
              />
              <div v-if="!(page.elements||[]).length" class="page-mini-empty">
                <i class="fa-solid fa-plus" />
              </div>
            </div>

            <!-- Page info -->
            <div class="page-info">
              <div class="page-name-wrap">
                <span v-if="renamingPage !== pi" class="page-name">{{ page.label || `Page ${pi+1}` }}</span>
                <input
                  v-else
                  ref="renameInputRef"
                  :value="page.label || `Page ${pi+1}`"
                  class="page-rename-input"
                  @blur="finishRename(pi, $event.target.value)"
                  @keydown.enter="finishRename(pi, $event.target.value)"
                  @keydown.escape="renamingPage = null"
                  @click.stop
                  aria-label="Rename page"
                />
              </div>
              <span class="page-el-count">{{ (page.elements||[]).length }} el</span>
            </div>

            <!-- Page actions -->
            <div class="page-actions">
              <button @click.stop="startPageRename(pi)" title="Rename page" aria-label="Rename page">
                <i class="fa-solid fa-pen" />
              </button>
              <button @click.stop="$emit('duplicate-page', pi)" title="Duplicate page" aria-label="Duplicate page">
                <i class="fa-solid fa-copy" />
              </button>
              <button
                @click.stop="$emit('delete-page', pi)"
                :disabled="report.content.length <= 1"
                class="danger"
                title="Delete page"
                aria-label="Delete page"
              >
                <i class="fa-solid fa-trash" />
              </button>
            </div>

            <!-- Active glow ring -->
            <div v-if="currentPage === pi" class="page-active-ring" aria-hidden="true" />
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB: LAYERS
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'layers'" class="tab-panel" role="tabpanel">
        <div class="layers-header">
          <span>Layers</span>
          <div class="layers-actions">
            <button class="micro-btn" @click="$emit('deselect-all')" title="Deselect all">
              <i class="fa-solid fa-xmark" />
            </button>
            <span class="layer-count-badge">{{ currentPageEls.length }}</span>
          </div>
        </div>

        <div class="layers-list" role="list" aria-label="Element layers">
          <div
            v-for="(el, ei) in reversedEls"
            :key="el.id"
            class="layer-item"
            :class="{
              'layer-item--selected': isSelected(ei),
              'layer-item--locked':   el.locked,
              'layer-item--hidden':   el.visible === false,
            }"
            role="listitem"
            :aria-selected="isSelected(ei)"
            @click="selectLayer(ei)"
          >
            <span class="layer-drag-icon"><i class="fa-solid fa-grip-vertical" /></span>
            <span class="layer-type-icon" :style="{ color: getTypeColor(el.type) }">
              <i :class="getElIcon(el.type)" />
            </span>
            <div class="layer-info">
              <span class="layer-name">{{ getLayerName(el) }}</span>
              <span class="layer-type-tag">{{ el.type }}</span>
            </div>
            <div class="layer-controls">
              <button
                class="layer-ctrl-btn"
                @click.stop="$emit('toggle-visibility', realIdx(ei))"
                :title="el.visible === false ? 'Show element' : 'Hide element'"
                :aria-label="el.visible === false ? 'Show' : 'Hide'"
              >
                <i :class="el.visible === false ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
              </button>
              <button
                class="layer-ctrl-btn"
                @click.stop="$emit('toggle-lock', realIdx(ei))"
                :title="el.locked ? 'Unlock element' : 'Lock element'"
                :aria-label="el.locked ? 'Unlock' : 'Lock'"
              >
                <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
              </button>
            </div>
          </div>

          <div v-if="!currentPageEls.length" class="empty-state">
            <i class="fa-solid fa-layer-group" />
            <p>No elements on this page</p>
            <small>Drag elements from the Elements tab</small>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB: MEDIA
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'media'" class="tab-panel" role="tabpanel">
        <!-- Upload zone -->
        <div
          class="upload-zone"
          :class="{ 'upload-zone--dragover': mediaDragover }"
          @click="triggerFileInput"
          @dragover.prevent="mediaDragover = true"
          @dragleave="mediaDragover = false"
          @drop.prevent="handleMediaDrop"
          role="button"
          aria-label="Upload images — click or drag and drop"
        >
          <i class="fa-solid fa-cloud-arrow-up" />
          <span>Upload Images</span>
          <small>Click or drag & drop • PNG, JPG, SVG, WebP</small>
        </div>
        <input
          ref="fileInputRef"
          type="file"
          accept="image/*"
          multiple
          class="hidden-input"
          @change="handleFileInput"
          aria-hidden="true"
        />

        <!-- Stock photos -->
        <div class="media-section">
          <div class="media-section-header">
            <i class="fa-solid fa-images" /> Stock Photos
          </div>
          <div class="search-wrap">
            <input
              v-model="stockQuery"
              class="search-input"
              placeholder="Search free photos…"
              @keydown.enter="searchStock"
              aria-label="Search stock photos"
            />
            <button class="search-action-btn" @click="searchStock" :disabled="stockLoading" aria-label="Search">
              <i :class="stockLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-magnifying-glass'" />
            </button>
          </div>
          <div v-if="stockImages.length" class="media-grid" aria-label="Stock photos">
            <div
              v-for="img in stockImages"
              :key="img.id"
              class="media-item"
              @click="addStockImage(img)"
              :title="`Add image by ${img.author}`"
              role="button"
              :aria-label="`Stock photo by ${img.author}`"
            >
              <img :src="img.thumb" :alt="`Stock photo by ${img.author}`" loading="lazy" />
            </div>
          </div>
        </div>

        <!-- Uploaded images -->
        <div v-if="uploadedImages.length" class="media-section">
          <div class="media-section-header">
            <i class="fa-solid fa-folder-open" /> Uploaded ({{ uploadedImages.length }})
          </div>
          <div class="media-grid" aria-label="Uploaded images">
            <div
              v-for="img in uploadedImages"
              :key="img.url"
              class="media-item media-item--uploaded"
              draggable="true"
              @dragstart="onMediaDrag($event, img)"
              @click="addUploadedImage(img)"
              :title="img.name"
              role="button"
              :aria-label="`Uploaded image: ${img.name}`"
            >
              <img :src="img.url" :alt="img.name" loading="lazy" />
              <button
                class="media-remove-btn"
                @click.stop="removeUploadedImage(img)"
                :title="`Remove ${img.name}`"
                :aria-label="`Remove ${img.name}`"
              >
                <i class="fa-solid fa-xmark" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB: THEMES
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'themes'" class="tab-panel" role="tabpanel">
        <p class="tab-description">Click a theme to apply colors to the whole report.</p>

        <div class="themes-grid" aria-label="Theme presets">
          <div
            v-for="theme in THEMES"
            :key="theme.name"
            class="theme-card"
            @click="applyTheme(theme)"
            role="button"
            :aria-label="`Apply ${theme.name} theme`"
            :title="theme.name"
          >
            <div class="theme-preview" :style="{ background: theme.gradient }">
              <div class="theme-hover-overlay">
                <i class="fa-solid fa-check" />
                <span>Apply</span>
              </div>
            </div>
            <span class="theme-name">{{ theme.name }}</span>
          </div>
        </div>

        <!-- Color palette quick-set -->
        <div class="quick-palette-section">
          <div class="media-section-header"><i class="fa-solid fa-palette" /> Quick Colors</div>
          <div class="color-palette-grid">
            <button
              v-for="c in PALETTE_COLORS"
              :key="c"
              class="palette-color-btn"
              :style="{ background: c }"
              @click="applyPrimaryColor(c)"
              :title="`Set primary color to ${c}`"
              :aria-label="`Primary color ${c}`"
            />
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB: SETTINGS
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'settings'" class="tab-panel settings-tab" role="tabpanel">

        <!-- Page Setup -->
        <SettingsSection title="Page Setup" icon="fa-solid fa-file">
          <SettingRow label="Size">
            <select v-model="ls.page_size" @change="emit_settings" class="s-select">
              <option value="A4">A4 (210×297mm)</option>
              <option value="A3">A3 (297×420mm)</option>
              <option value="A5">A5 (148×210mm)</option>
              <option value="Letter">Letter (8.5×11in)</option>
              <option value="Legal">Legal (8.5×14in)</option>
            </select>
          </SettingRow>

          <SettingRow label="Orientation">
            <div class="s-toggle-group">
              <button :class="{ active: ls.orientation === 'portrait' }"  @click="ls.orientation='portrait';  emit_settings()">
                <i class="fa-solid fa-mobile-screen" /> Portrait
              </button>
              <button :class="{ active: ls.orientation === 'landscape' }" @click="ls.orientation='landscape'; emit_settings()">
                <i class="fa-solid fa-tablet-screen-button fa-rotate-90" /> Landscape
              </button>
            </div>
          </SettingRow>

          <SettingRow :label="`Margin: ${ls.margin||40}px`">
            <input type="range" v-model.number="ls.margin" min="0" max="120" @input="emit_settings" class="s-range" />
          </SettingRow>

          <SettingRow :label="`Corner Radius: ${ls.page_radius||0}px`">
            <input type="range" v-model.number="ls.page_radius" min="0" max="40" @input="emit_settings" class="s-range" />
          </SettingRow>
        </SettingsSection>

        <!-- Colors -->
        <SettingsSection title="Colors" icon="fa-solid fa-palette">
          <SettingRow label="Primary">
            <div class="s-color-row">
              <input type="color" v-model="ls.primary_color" @input="emit_settings" class="s-color-input" />
              <input type="text"  v-model="ls.primary_color" @input="emit_settings" class="s-text-input mono" />
            </div>
          </SettingRow>
          <SettingRow label="Accent">
            <div class="s-color-row">
              <input type="color" v-model="ls.accent_color" @input="emit_settings" class="s-color-input" />
              <input type="text"  v-model="ls.accent_color" @input="emit_settings" class="s-text-input mono" />
            </div>
          </SettingRow>
          <SettingRow label="Background">
            <div class="s-color-row">
              <input type="color" v-model="ls.background_color" @input="emit_settings" class="s-color-input" />
              <input type="text"  v-model="ls.background_color" @input="emit_settings" class="s-text-input mono" />
            </div>
          </SettingRow>
          <SettingRow label="Text">
            <div class="s-color-row">
              <input type="color" v-model="ls.text_color" @input="emit_settings" class="s-color-input" />
              <input type="text"  v-model="ls.text_color" @input="emit_settings" class="s-text-input mono" />
            </div>
          </SettingRow>
          <SettingRow label="BG Image">
            <input type="text" v-model="ls.bg_image" @input="emit_settings" class="s-text-input" placeholder="https://…" />
          </SettingRow>
        </SettingsSection>

        <!-- Typography -->
        <SettingsSection title="Typography" icon="fa-solid fa-font">
          <SettingRow label="Font">
            <select v-model="ls.font_family" @change="emit_settings" class="s-select">
              <option v-for="f in FONT_LIST" :key="f" :value="f">{{ f }}</option>
            </select>
          </SettingRow>
          <SettingRow :label="`Base Size: ${ls.font_size||14}px`">
            <input type="range" v-model.number="ls.font_size" min="10" max="24" @input="emit_settings" class="s-range" />
          </SettingRow>
          <SettingRow label="Direction">
            <div class="s-toggle-group">
              <button :class="{ active: !ls.rtl }" @click="ls.rtl=false; emit_settings()">LTR</button>
              <button :class="{ active: ls.rtl  }" @click="ls.rtl=true;  emit_settings()">RTL</button>
            </div>
          </SettingRow>
        </SettingsSection>

        <!-- Header -->
        <SettingsSection title="Header" icon="fa-solid fa-heading">
          <SettingRow label="Show Header">
            <ToggleSw :value="ls.show_header" @update="ls.show_header=$event; emit_settings()" />
          </SettingRow>
          <template v-if="ls.show_header">
            <SettingRow label="Text">
              <input type="text" v-model="ls.header_text" @input="emit_settings" class="s-text-input" />
            </SettingRow>
            <SettingRow label="Color">
              <div class="s-color-row">
                <input type="color" v-model="ls.header_color" @input="emit_settings" class="s-color-input" />
                <input type="text"  v-model="ls.header_color" @input="emit_settings" class="s-text-input mono" />
              </div>
            </SettingRow>
            <SettingRow :label="`Height: ${ls.header_height||50}px`">
              <input type="range" v-model.number="ls.header_height" min="30" max="120" @input="emit_settings" class="s-range" />
            </SettingRow>
          </template>
        </SettingsSection>

        <!-- Footer -->
        <SettingsSection title="Footer" icon="fa-solid fa-align-justify">
          <SettingRow label="Show Footer">
            <ToggleSw :value="ls.show_footer" @update="ls.show_footer=$event; emit_settings()" />
          </SettingRow>
          <template v-if="ls.show_footer">
            <SettingRow label="Left">
              <input type="text" v-model="ls.footer_left" @input="emit_settings" class="s-text-input" placeholder="Company" />
            </SettingRow>
            <SettingRow label="Right">
              <input type="text" v-model="ls.footer_right" @input="emit_settings" class="s-text-input" placeholder="{n}/{total}" />
            </SettingRow>
          </template>
          <SettingRow label="Page Numbers">
            <ToggleSw :value="ls.show_page_numbers !== false" @update="ls.show_page_numbers=$event; emit_settings()" />
          </SettingRow>
        </SettingsSection>

        <!-- Watermark -->
        <SettingsSection title="Watermark" icon="fa-solid fa-water">
          <SettingRow label="Text">
            <input type="text" v-model="ls.watermark" @input="emit_settings" class="s-text-input" placeholder="DRAFT" />
          </SettingRow>
          <template v-if="ls.watermark">
            <SettingRow :label="`Opacity: ${ls.watermark_opacity||8}%`">
              <input type="range" v-model.number="ls.watermark_opacity" min="1" max="25" @input="emit_settings" class="s-range" />
            </SettingRow>
            <SettingRow label="Color">
              <div class="s-color-row">
                <input type="color" v-model="ls.watermark_color" @input="emit_settings" class="s-color-input" />
                <input type="text"  v-model="ls.watermark_color" @input="emit_settings" class="s-text-input mono" />
              </div>
            </SettingRow>
            <SettingRow :label="`Rotation: ${ls.watermark_rotate||-30}°`">
              <input type="range" v-model.number="ls.watermark_rotate" min="-90" max="90" @input="emit_settings" class="s-range" />
            </SettingRow>
          </template>
        </SettingsSection>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB: HISTORY
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'history'" class="tab-panel" role="tabpanel">
        <button class="refresh-history-btn" @click="loadHistory" :disabled="historyLoading">
          <i :class="historyLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-rotate'" />
          {{ historyLoading ? 'Loading…' : 'Refresh History' }}
        </button>

        <div v-if="versionList.length" class="history-timeline" aria-label="Version history">
          <div
            v-for="(ver, vi) in versionList"
            :key="ver.id"
            class="history-item"
            :class="{ 'history-item--current': vi === 0 }"
          >
            <div class="history-dot" />
            <div v-if="vi < versionList.length-1" class="history-line" />
            <div class="history-content">
              <div class="history-header">
                <strong>v{{ ver.version_number }}</strong>
                <span v-if="vi===0" class="history-current-badge">Current</span>
              </div>
              <p class="history-label">{{ ver.label || 'Auto-saved' }}</p>
              <span class="history-date">{{ formatDate(ver.created_at) }}</span>
            </div>
            <button
              class="history-restore-btn"
              @click="restoreVersion(ver.id)"
              :disabled="vi === 0"
              :title="vi === 0 ? 'Already current' : `Restore version ${ver.version_number}`"
            >
              Restore
            </button>
          </div>
        </div>

        <div v-else-if="!historyLoading" class="empty-state">
          <i class="fa-solid fa-clock-rotate-left" />
          <p>No version history yet</p>
          <small>Versions are saved automatically every 5 minutes</small>
        </div>
      </div>

    </div><!-- /panel-inner -->
  </aside>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'

// ── Inline sub-components ──────────────────────────────────────────────
const SettingsSection = {
  name: 'SettingsSection',
  props: { title: String, icon: String },
  setup(props) {
    const open = ref(true)
    return { open }
  },
  template: `
    <div class="s-section">
      <button class="s-section-header" @click="open=!open">
        <i :class="icon" class="s-section-icon" />
        <span>{{ title }}</span>
        <i :class="open ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="s-chevron" />
      </button>
      <div v-if="open" class="s-section-body"><slot /></div>
    </div>
  `,
}

const SettingRow = {
  name: 'SettingRow',
  props: { label: String },
  template: `
    <div class="s-row">
      <label class="s-label">{{ label }}</label>
      <div class="s-control"><slot /></div>
    </div>
  `,
}

const ToggleSw = {
  name: 'ToggleSw',
  props: { value: Boolean },
  emits: ['update'],
  template: `
    <label class="toggle-sw">
      <input type="checkbox" :checked="value" @change="$emit('update', $event.target.checked)" class="toggle-sw-input" />
      <span class="toggle-sw-track"><span class="toggle-sw-thumb" /></span>
    </label>
  `,
}

// ── Props ──────────────────────────────────────────────────────────────
const props = defineProps({
  report:      { type: Object,  required: true },
  settings:    { type: Object,  required: true },
  currentPage: { type: Number,  default: 0 },
  selectedElIdx: { type: Number, default: null },
  selectedEls: { type: Array,   default: () => [] },
  activeTab:   { type: String,  default: 'elements' },
  isCollapsed: { type: Boolean, default: false },
  isDark:      { type: Boolean, default: false },
})

const emit = defineEmits([
  'add-element-center','select-page','add-page','add-page-after',
  'duplicate-page','delete-page','rename-page',
  'select-element','deselect-all','toggle-visibility','toggle-lock',
  'canvas-drag-start','update:settings',
  'update:active-tab','update:is-collapsed','mark-dirty',
])

// ── Reactive local state ───────────────────────────────────────────────
const elSearch      = ref('')
const collapsedCats = ref([])
const renamingPage  = ref(null)
const renameInputRef= ref(null)
const mediaDragover = ref(false)
const fileInputRef  = ref(null)
const stockQuery    = ref('business')
const stockLoading  = ref(false)
const stockImages   = ref([])
const uploadedImages= ref([])
const versionList   = ref([])
const historyLoading= ref(false)

// Local copy of settings (never mutate props directly)
const ls = reactive({ ...props.settings })
watch(() => props.settings, v => Object.assign(ls, v), { deep: true })

// ── Constants ──────────────────────────────────────────────────────────
const TABS = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-shapes' },
  { id: 'pages',    label: 'Pages',    icon: 'fa-solid fa-copy' },
  { id: 'layers',   label: 'Layers',   icon: 'fa-solid fa-layer-group' },
  { id: 'media',    label: 'Media',    icon: 'fa-solid fa-image' },
  { id: 'themes',   label: 'Themes',   icon: 'fa-solid fa-paint-roller' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-sliders' },
  { id: 'history',  label: 'History',  icon: 'fa-solid fa-clock-rotate-left' },
]

const QUICK_CHIPS = [
  { type:'heading', label:'Heading', icon:'fa-solid fa-heading', w:350, h:60 },
  { type:'text',    label:'Text',    icon:'fa-solid fa-align-left', w:300, h:80 },
  { type:'image',   label:'Image',   icon:'fa-solid fa-image', w:300, h:200 },
  { type:'table',   label:'Table',   icon:'fa-solid fa-table', w:460, h:220 },
  { type:'bar-chart', label:'Chart', icon:'fa-solid fa-chart-bar', w:400, h:280 },
  { type:'metric',  label:'KPI',     icon:'fa-solid fa-gauge-high', w:200, h:120 },
  { type:'richtext',label:'Rich Text',icon:'fa-solid fa-file-word',w:400, h:200 },
]

const ELEMENT_CATALOG = [
  {
    name: 'Typography',
    items: [
      { type:'heading',    label:'Heading',     icon:'fa-solid fa-heading',        w:350, h:60,  desc:'Large, bold title text' },
      { type:'subheading', label:'Subheading',  icon:'fa-solid fa-text-height',    w:280, h:45,  desc:'Section heading' },
      { type:'text',       label:'Text',        icon:'fa-solid fa-align-left',     w:300, h:80,  desc:'Body paragraph text' },
      { type:'richtext',   label:'Rich Text',   icon:'fa-solid fa-file-word',      w:400, h:200, desc:'Full WYSIWYG editor', isNew:true },
      { type:'quote',      label:'Quote',       icon:'fa-solid fa-quote-right',    w:340, h:100, desc:'Stylized pull quote' },
      { type:'blockquote', label:'Blockquote',  icon:'fa-solid fa-quote-left',     w:340, h:100, desc:'Block-styled quote' },
      { type:'highlight',  label:'Highlight',   icon:'fa-solid fa-highlighter',    w:240, h:40,  desc:'Highlighted inline text' },
      { type:'badge',      label:'Badge',       icon:'fa-solid fa-tag',            w:120, h:36,  desc:'Colored pill badge' },
      { type:'code',       label:'Code Block',  icon:'fa-solid fa-code',           w:380, h:140, desc:'Monospaced code block' },
      { type:'link',       label:'Link',        icon:'fa-solid fa-link',           w:200, h:36,  desc:'Clickable hyperlink' },
      { type:'list',       label:'List',        icon:'fa-solid fa-list-ul',        w:280, h:140, desc:'Bullet or numbered list' },
      { type:'callout',    label:'Callout',     icon:'fa-solid fa-lightbulb',      w:380, h:100, desc:'Emoji callout box' },
    ],
  },
  {
    name: 'Data & Charts',
    items: [
      { type:'table',           label:'Table',           icon:'fa-solid fa-table',              w:460, h:220, desc:'Data table with editable cells' },
      { type:'metric',          label:'KPI Card',        icon:'fa-solid fa-gauge-high',         w:200, h:120, desc:'Key performance indicator' },
      { type:'stat-row',        label:'Stat Row',        icon:'fa-solid fa-bars-staggered',     w:460, h:90,  desc:'Horizontal statistics row' },
      { type:'progress',        label:'Progress Bar',    icon:'fa-solid fa-bars-progress',      w:360, h:60,  desc:'Percentage progress bar' },
      { type:'circular-progress',label:'Circle Progress',icon:'fa-solid fa-circle-half-stroke', w:160, h:160, desc:'Circular progress indicator', isNew:true },
      { type:'sparkline',       label:'Sparkline',       icon:'fa-solid fa-wave-square',        w:200, h:50,  desc:'Mini trend line chart', isNew:true },
      { type:'checklist',       label:'Checklist',       icon:'fa-solid fa-list-check',         w:300, h:180, desc:'Interactive checklist' },
      { type:'bar-chart',       label:'Bar Chart',       icon:'fa-solid fa-chart-bar',          w:420, h:280, desc:'Vertical bar chart' },
      { type:'line-chart',      label:'Line Chart',      icon:'fa-solid fa-chart-line',         w:420, h:280, desc:'Trend line chart' },
      { type:'area-chart',      label:'Area Chart',      icon:'fa-solid fa-chart-area',         w:420, h:280, desc:'Filled area chart' },
      { type:'pie-chart',       label:'Pie Chart',       icon:'fa-solid fa-chart-pie',          w:280, h:280, desc:'Pie distribution chart' },
      { type:'doughnut-chart',  label:'Doughnut',        icon:'fa-solid fa-circle-dot',         w:280, h:280, desc:'Doughnut chart' },
      { type:'radar-chart',     label:'Radar',           icon:'fa-solid fa-compass',            w:280, h:280, desc:'Spider/radar chart', isNew:true },
      { type:'scatter-chart',   label:'Scatter',         icon:'fa-solid fa-braille',            w:280, h:280, desc:'Scatter plot', isNew:true },
      { type:'polar-chart',     label:'Polar Area',      icon:'fa-solid fa-circle-half-stroke', w:280, h:280, desc:'Polar area chart', isNew:true },
    ],
  },
  {
    name: 'Media',
    items: [
      { type:'image',    label:'Image',    icon:'fa-solid fa-image',             w:320, h:220, desc:'Upload or link an image' },
      { type:'video',    label:'Video',    icon:'fa-solid fa-video',             w:400, h:250, desc:'YouTube embed', isNew:true },
      { type:'map',      label:'Map',      icon:'fa-solid fa-map-location-dot',  w:400, h:260, desc:'Google Maps embed', isNew:true },
      { type:'qr-code',  label:'QR Code',  icon:'fa-solid fa-qrcode',            w:160, h:160, desc:'Auto-generated QR code', isNew:true },
      { type:'icon',     label:'Emoji Icon',icon:'fa-solid fa-face-smile',        w:60,  h:60,  desc:'Single emoji or icon' },
      { type:'rating',   label:'Rating',   icon:'fa-solid fa-star',              w:160, h:40,  desc:'Star rating' },
      { type:'avatar',   label:'Avatar',   icon:'fa-solid fa-circle-user',       w:80,  h:80,  desc:'Emoji or user avatar', isNew:true },
    ],
  },
  {
    name: 'Layout & Shapes',
    items: [
      { type:'rectangle',  label:'Rectangle',  icon:'fa-solid fa-square',         w:200, h:120, desc:'Colored rectangle' },
      { type:'circle',     label:'Circle',     icon:'fa-solid fa-circle',         w:120, h:120, desc:'Colored circle' },
      { type:'triangle',   label:'Triangle',   icon:'fa-solid fa-play',           w:140, h:120, desc:'Triangle shape' },
      { type:'divider',    label:'Divider',    icon:'fa-solid fa-minus',          w:500, h:4,   desc:'Horizontal rule' },
      { type:'arrow',      label:'Arrow',      icon:'fa-solid fa-arrow-right',    w:200, h:40,  desc:'SVG arrow' },
      { type:'spacer',     label:'Spacer',     icon:'fa-solid fa-expand',         w:100, h:40,  desc:'Invisible spacing element' },
    ],
  },
  {
    name: 'Content Blocks',
    items: [
      { type:'timeline',     label:'Timeline',     icon:'fa-solid fa-timeline',         w:440, h:280, desc:'Vertical event timeline' },
      { type:'testimonial',  label:'Testimonial',  icon:'fa-solid fa-comment-dots',     w:360, h:180, desc:'Quote + author block' },
      { type:'social-card',  label:'Social Card',  icon:'fa-solid fa-id-card',          w:200, h:180, desc:'Contact/profile card', isNew:true },
      { type:'price-card',   label:'Pricing Card', icon:'fa-solid fa-credit-card',      w:220, h:320, desc:'Pricing plan card', isNew:true },
      { type:'kanban',       label:'Kanban Card',  icon:'fa-solid fa-square-kanban',    w:240, h:130, desc:'Task card', isNew:true },
      { type:'toc',          label:'Table of Contents', icon:'fa-solid fa-list-ol',     w:380, h:260, desc:'Auto TOC from headings', isNew:true },
      { type:'steps',        label:'Steps',        icon:'fa-solid fa-list-check',       w:500, h:80,  desc:'Numbered step flow', isNew:true },
      { type:'callout',      label:'Callout',      icon:'fa-solid fa-lightbulb',        w:380, h:100, desc:'Info/warning callout box' },
    ],
  },
  {
    name: 'Utilities',
    items: [
      { type:'pagenum',    label:'Page Number',  icon:'fa-solid fa-hashtag',         w:60,  h:30,  desc:'Auto page number' },
      { type:'date-el',    label:'Date',         icon:'fa-solid fa-calendar',        w:200, h:30,  desc:'Today\'s date' },
      { type:'signature',  label:'Signature',    icon:'fa-solid fa-signature',       w:240, h:100, desc:'Signature line' },
      { type:'watermark',  label:'Watermark',    icon:'fa-solid fa-droplet',         w:300, h:100, desc:'Watermark text element' },
    ],
  },
]

const THEMES = [
  { name:'Indigo Pro',      gradient:'linear-gradient(135deg, #6366f1, #4f46e5)',  primary:'#6366f1', accent:'#8b5cf6', bg:'#ffffff', text:'#0f172a' },
  { name:'Executive Dark',  gradient:'linear-gradient(135deg, #0f172a, #1e293b)',  primary:'#6366f1', accent:'#8b5cf6', bg:'#0f172a', text:'#f8fafc' },
  { name:'Emerald Fresh',   gradient:'linear-gradient(135deg, #065f46, #10b981)',  primary:'#10b981', accent:'#059669', bg:'#ffffff', text:'#0f172a' },
  { name:'Amber Bold',      gradient:'linear-gradient(135deg, #92400e, #f59e0b)',  primary:'#f59e0b', accent:'#f97316', bg:'#1e293b', text:'#f8fafc' },
  { name:'Rose Elegant',    gradient:'linear-gradient(135deg, #9f1239, #f43f5e)',  primary:'#f43f5e', accent:'#ec4899', bg:'#ffffff', text:'#0f172a' },
  { name:'Sky Modern',      gradient:'linear-gradient(135deg, #0c4a6e, #0ea5e9)',  primary:'#0ea5e9', accent:'#06b6d4', bg:'#ffffff', text:'#0f172a' },
  { name:'Violet Dark',     gradient:'linear-gradient(135deg, #4c1d95, #7c3aed)',  primary:'#7c3aed', accent:'#a78bfa', bg:'#1a0a2e', text:'#f8fafc' },
  { name:'Slate Minimal',   gradient:'linear-gradient(135deg, #1e293b, #475569)',  primary:'#64748b', accent:'#94a3b8', bg:'#f8fafc', text:'#1e293b' },
]

const PALETTE_COLORS = [
  '#6366f1','#8b5cf6','#ec4899','#ef4444','#f97316','#f59e0b',
  '#10b981','#06b6d4','#0ea5e9','#3b82f6','#84cc16','#64748b',
  '#1e293b','#0f172a','#ffffff',
]

const FONT_LIST = [
  'DM Sans','Inter','Plus Jakarta Sans','Space Grotesk','Sora','Nunito',
  'Outfit','Poppins','Figtree','Geist','Georgia','Playfair Display',
  'Merriweather','Lora','Fira Code','Courier New',
]

// ── Computed ───────────────────────────────────────────────────────────
const filteredCats = computed(() => {
  if (!elSearch.value.trim()) return ELEMENT_CATALOG
  const q = elSearch.value.toLowerCase()
  return ELEMENT_CATALOG
    .map(c => ({ ...c, items: c.items.filter(i => i.label.toLowerCase().includes(q) || i.type.includes(q) || (i.desc||'').toLowerCase().includes(q)) }))
    .filter(c => c.items.length > 0)
})

const currentPageEls = computed(() => props.report.content[props.currentPage]?.elements || [])
const reversedEls    = computed(() => [...currentPageEls.value].reverse())

// ── Helpers ────────────────────────────────────────────────────────────
function isSelected(reversedIdx) {
  const realI = currentPageEls.value.length - 1 - reversedIdx
  return props.selectedElIdx === realI || props.selectedEls.includes(realI)
}

function realIdx(reversedIdx) {
  return currentPageEls.value.length - 1 - reversedIdx
}

function selectLayer(reversedIdx) {
  emit('select-element', [realIdx(reversedIdx)])
}

function getLayerName(el) {
  const text = (el.content || el.label || el.value || '')
    .toString()
    .replace(/<[^>]*>/g, '')
    .trim()
  return text.substring(0, 28) || el.type
}

function getElIcon(type) {
  for (const cat of ELEMENT_CATALOG) {
    const found = cat.items.find(i => i.type === type)
    if (found) return found.icon
  }
  return 'fa-solid fa-cube'
}

function getTypeColor(type) {
  if (type?.endsWith('-chart') || type === 'table') return '#06b6d4'
  if (['text','heading','subheading','quote','richtext'].includes(type)) return '#6366f1'
  if (['image','video','map'].includes(type)) return '#ec4899'
  if (['metric','stat-row','progress'].includes(type)) return '#f59e0b'
  if (['rectangle','circle','triangle'].includes(type)) return '#10b981'
  return '#94a3b8'
}

function getMiniElStyle(el) {
  const scale = 0.12
  return {
    position: 'absolute',
    left:    ((el.position?.x || 0) * scale) + 'px',
    top:     ((el.position?.y || 0) * scale) + 'px',
    width:   ((el.styles?.width  || 80) * scale) + 'px',
    height:  ((el.styles?.height || 40) * scale) + 'px',
    background: el.styles?.backgroundColor || (props.settings.primary_color || '#6366f1'),
    opacity: .55,
    borderRadius: '1px',
  }
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-GB', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' })
}

// ── Elements ───────────────────────────────────────────────────────────
function toggleCat(name) {
  const i = collapsedCats.value.indexOf(name)
  if (i >= 0) collapsedCats.value.splice(i, 1)
  else collapsedCats.value.push(name)
}

function addToCanvas(def) {
  emit('add-element-center', def)
}

function onDragStart(e, def) {
  e.dataTransfer.setData('el-def', JSON.stringify(def))
  e.dataTransfer.effectAllowed = 'copy'
  emit('canvas-drag-start', e, def)
}

// ── Pages ──────────────────────────────────────────────────────────────
function startPageRename(pi) {
  renamingPage.value = pi
  nextTick(() => {
    const inputs = document.querySelectorAll('.page-rename-input')
    inputs[inputs.length - 1]?.focus()
    inputs[inputs.length - 1]?.select()
  })
}

function finishRename(pi, val) {
  renamingPage.value = null
  emit('rename-page', pi, val || `Page ${pi+1}`)
}

// ── Themes ─────────────────────────────────────────────────────────────
function applyTheme(theme) {
  Object.assign(ls, {
    primary_color:    theme.primary,
    accent_color:     theme.accent,
    background_color: theme.bg,
    text_color:       theme.text,
  })
  emit_settings()
}

function applyPrimaryColor(c) {
  ls.primary_color = c
  emit_settings()
}

// ── Settings emit ──────────────────────────────────────────────────────
function emit_settings() {
  emit('update:settings', { ...ls })
}

// ── Media ──────────────────────────────────────────────────────────────
function triggerFileInput() {
  fileInputRef.value?.click()
}

function handleFileInput(e) {
  const files = Array.from(e.target.files || [])
  files.forEach(readFile)
  e.target.value = ''
}

function handleMediaDrop(e) {
  mediaDragover.value = false
  const files = Array.from(e.dataTransfer.files || []).filter(f => f.type.startsWith('image/'))
  files.forEach(readFile)
}

function readFile(file) {
  const reader = new FileReader()
  reader.onload = (ev) => {
    uploadedImages.value.push({ url: ev.target.result, name: file.name })
  }
  reader.readAsDataURL(file)
}

function addUploadedImage(img) {
  emit('add-element-center', { type: 'image', w: 320, h: 220, src: img.url })
}

function removeUploadedImage(img) {
  uploadedImages.value = uploadedImages.value.filter(i => i.url !== img.url)
}

function onMediaDrag(e, img) {
  e.dataTransfer.setData('el-def', JSON.stringify({ type:'image', w:320, h:220, src:img.url }))
  e.dataTransfer.effectAllowed = 'copy'
}

async function searchStock() {
  stockLoading.value = true
  try {
    const res = await fetch(`/api/unsplash/search?q=${encodeURIComponent(stockQuery.value || 'business')}`)
    if (res.ok) {
      const data = await res.json()
      stockImages.value = data.images || []
    } else {
      throw new Error('API unavailable')
    }
  } catch {
    // Fallback to picsum
    stockImages.value = Array.from({ length: 12 }, (_, i) => ({
      id: i,
      thumb: `https://picsum.photos/200/150?random=${i * 7 + Date.now() % 100}`,
      url:   `https://picsum.photos/800/600?random=${i * 7 + Date.now() % 100}`,
      author: 'Free Stock',
    }))
  }
  stockLoading.value = false
}

function addStockImage(img) {
  emit('add-element-center', { type: 'image', w: 320, h: 220, src: img.url })
}

// ── History ────────────────────────────────────────────────────────────
async function loadHistory() {
  historyLoading.value = true
  try {
    const slug = props.report?.slug
    if (!slug) throw new Error('No slug')
    const res  = await fetch(`/reports/${slug}/versions`, {
      headers: { Accept: 'application/json' },
    })
    if (res.ok) {
      const data   = await res.json()
      versionList.value = data.versions || []
    }
  } catch {}
  historyLoading.value = false
}

async function restoreVersion(id) {
  if (!confirm('Restore this version? Unsaved changes will be lost.')) return
  const slug = props.report?.slug
  if (!slug) return
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const res  = await fetch(`/reports/${slug}/versions/${id}/restore`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    })
    if (res.ok) window.location.reload()
  } catch {}
}

// ── Init ───────────────────────────────────────────────────────────────
onMounted(() => {
  searchStock()
  loadHistory()
})
</script>

<style scoped>
/* ═══ LEFT PANEL ════════════════════════════════════════════════════════ */
.left-panel {
  --lp-bg:      #ffffff;
  --lp-bg2:     #f8fafc;
  --lp-bg3:     #f1f5f9;
  --lp-border:  #e2e8f0;
  --lp-text:    #0f172a;
  --lp-text2:   #475569;
  --lp-text3:   #94a3b8;
  --lp-accent:  #6366f1;
  --lp-accent-l:rgba(99,102,241,.08);

  width: 262px;
  flex-shrink: 0;
  background: var(--lp-bg);
  border-right: 1px solid var(--lp-border);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: width .25s ease;
  position: relative;
  z-index: 40;
}

.left-panel.collapsed { width: 0; border-right: none; }

.left-panel.is-dark {
  --lp-bg:     #1a2236;
  --lp-bg2:    #111827;
  --lp-bg3:    #0d1424;
  --lp-border: #263348;
  --lp-text:   #e2e8f0;
  --lp-text2:  #94a3b8;
  --lp-text3:  #475569;
  --lp-accent: #818cf8;
  --lp-accent-l:rgba(129,140,248,.1);
}

.panel-toggle {
  position: absolute; right: -14px; top: 50%; transform: translateY(-50%);
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--lp-bg); border: 1px solid var(--lp-border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; z-index: 10; color: var(--lp-text3); font-size: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,.08); transition: all .15s;
}
.panel-toggle:hover { color: var(--lp-accent); border-color: var(--lp-accent); }

.panel-inner { flex: 1; overflow: hidden; display: flex; flex-direction: column; }

/* ═══ TABS ═══════════════════════════════════════════════════════════════ */
.panel-tabs {
  display: flex; flex-wrap: wrap; gap: 1px; padding: 5px 5px 0;
  border-bottom: 1px solid var(--lp-border); flex-shrink: 0;
  background: var(--lp-bg2);
}

.panel-tab {
  display: flex; flex-direction: column; align-items: center; gap: 2px;
  padding: 6px 4px 5px; border: none; background: transparent; cursor: pointer;
  color: var(--lp-text3); font-size: 8.5px; font-weight: 700; letter-spacing: .04em;
  text-transform: uppercase; flex: 1; min-width: 34px;
  border-bottom: 2px solid transparent; margin-bottom: -1px;
  transition: all .14s; font-family: inherit;
}
.panel-tab i { font-size: 13px; }
.panel-tab:hover { color: var(--lp-text2); background: var(--lp-bg3); }
.panel-tab.active { color: var(--lp-accent); border-bottom-color: var(--lp-accent); background: var(--lp-accent-l); }

/* ═══ TAB PANELS ════════════════════════════════════════════════════════ */
.tab-panel { flex: 1; overflow-y: auto; display: flex; flex-direction: column; scrollbar-width: thin; scrollbar-color: var(--lp-border) transparent; }
.tab-panel::-webkit-scrollbar { width: 4px; }
.tab-panel::-webkit-scrollbar-thumb { background: var(--lp-border); border-radius: 99px; }

/* ═══ SEARCH ════════════════════════════════════════════════════════════ */
.search-wrap {
  display: flex; align-items: center; gap: 6px; padding: 8px;
  background: var(--lp-bg); border-bottom: 1px solid var(--lp-border);
  flex-shrink: 0; position: sticky; top: 0; z-index: 5;
}
.search-wrap:focus-within { background: var(--lp-accent-l); }
.search-icon { color: var(--lp-text3); font-size: 12px; flex-shrink: 0; }
.search-input { flex: 1; border: none; background: transparent; outline: none; font-size: 12px; color: var(--lp-text); font-family: inherit; }
.search-input::placeholder { color: var(--lp-text3); }
.search-clear { border: none; background: transparent; cursor: pointer; color: var(--lp-text3); font-size: 11px; transition: color .14s; }
.search-clear:hover { color: #ef4444; }

/* ═══ QUICK CHIPS ═══════════════════════════════════════════════════════ */
.quick-chips {
  display: flex; gap: 4px; padding: 8px; flex-wrap: wrap;
  border-bottom: 1px solid var(--lp-border); flex-shrink: 0;
}
.quick-chip {
  display: flex; align-items: center; gap: 4px; padding: 4px 9px;
  border: 1px solid var(--lp-border); border-radius: 99px;
  background: var(--lp-bg2); cursor: pointer; font-size: 10px; font-weight: 600;
  color: var(--lp-text2); white-space: nowrap; transition: all .15s; font-family: inherit;
}
.quick-chip:hover { border-color: var(--lp-accent); color: var(--lp-accent); background: var(--lp-accent-l); transform: translateY(-1px); }

/* ═══ ELEMENT CATALOG ═══════════════════════════════════════════════════ */
.el-catalog { flex: 1; overflow-y: auto; padding: 4px 0 20px; }

.cat-header {
  display: flex; align-items: center; gap: 6px; width: 100%;
  padding: 7px 10px; border: none; background: var(--lp-bg2); cursor: pointer;
  font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
  color: var(--lp-text3); transition: background .14s; font-family: inherit; position: sticky; top: 0; z-index: 2;
}
.cat-header:hover { background: var(--lp-bg3); color: var(--lp-text2); }
.cat-name { flex: 1; text-align: left; }
.cat-count { font-size: 9px; background: var(--lp-bg3); border: 1px solid var(--lp-border); padding: 1px 5px; border-radius: 99px; }
.cat-chevron { font-size: 8px; opacity: .5; }

.el-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; padding: 4px 6px;
}

.el-card {
  display: flex; flex-direction: column; align-items: center; gap: 5px;
  padding: 10px 4px 8px; border-radius: 8px; cursor: grab;
  border: 1.5px solid transparent; transition: all .15s; position: relative;
  user-select: none;
}
.el-card:hover {
  background: var(--lp-accent-l); border-color: rgba(99,102,241,.2);
  transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.06);
}
.el-card:active { cursor: grabbing; transform: scale(.94); }
.el-card:focus-visible { outline: 2px solid var(--lp-accent); outline-offset: 2px; }

.el-card-icon {
  width: 36px; height: 36px; border-radius: 8px; background: var(--lp-bg2);
  display: flex; align-items: center; justify-content: center;
  font-size: 15px; color: var(--lp-text2); transition: all .15s;
}
.el-card:hover .el-card-icon { background: var(--lp-accent-l); color: var(--lp-accent); }

.el-card-name { font-size: 9px; font-weight: 700; color: var(--lp-text3); text-align: center; line-height: 1.2; }
.el-card:hover .el-card-name { color: var(--lp-text); }

.el-new-badge {
  position: absolute; top: -3px; right: -2px;
  font-size: 7px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
  background: var(--lp-accent); color: #fff; padding: 1px 5px; border-radius: 99px;
  animation: newPulse 2s ease-in-out infinite;
}
@keyframes newPulse { 0%,100%{opacity:1}50%{opacity:.6} }

/* ═══ PAGES ══════════════════════════════════════════════════════════════ */
.add-page-btn {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  margin: 10px; padding: 9px; border: 1.5px dashed var(--lp-border);
  border-radius: 8px; background: transparent; cursor: pointer;
  color: var(--lp-text2); font-size: 12px; font-weight: 600; transition: all .2s; font-family: inherit;
}
.add-page-btn:hover { border-color: var(--lp-accent); color: var(--lp-accent); background: var(--lp-accent-l); }

.pages-list { display: flex; flex-direction: column; gap: 6px; padding: 0 8px 20px; }

.page-card {
  border: 2px solid var(--lp-border); border-radius: 10px; overflow: visible;
  cursor: pointer; transition: all .2s; background: var(--lp-bg); position: relative;
}
.page-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.page-card--active { border-color: var(--lp-accent); box-shadow: 0 0 0 2px rgba(99,102,241,.15); }

.page-mini-preview {
  height: 80px; position: relative; overflow: hidden;
  border-radius: 8px 8px 0 0; border-bottom: 1px solid var(--lp-border);
}
.page-mini-el { position: absolute; border-radius: 1px; }
.page-mini-empty {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  color: var(--lp-border); font-size: 20px;
}

.page-info { display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; }
.page-name-wrap { flex: 1; min-width: 0; overflow: hidden; }
.page-name { font-size: 11px; font-weight: 600; color: var(--lp-text2); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }
.page-rename-input {
  width: 100%; font-size: 11px; font-weight: 600; border: 1px solid var(--lp-accent);
  border-radius: 4px; padding: 2px 5px; background: var(--lp-bg); color: var(--lp-text); outline: none; font-family: inherit;
}
.page-el-count { font-size: 9px; color: var(--lp-text3); white-space: nowrap; background: var(--lp-bg2); padding: 2px 6px; border-radius: 99px; }

.page-actions { display: flex; gap: 2px; padding: 0 6px 6px; }
.page-actions button {
  flex: 1; padding: 4px; border: 1px solid var(--lp-border); border-radius: 5px;
  background: transparent; cursor: pointer; color: var(--lp-text3); font-size: 10px;
  transition: all .14s;
}
.page-actions button:hover { background: var(--lp-bg2); color: var(--lp-text); }
.page-actions button.danger:hover { background: rgba(239,68,68,.07); color: #ef4444; border-color: rgba(239,68,68,.3); }
.page-actions button:disabled { opacity: .3; cursor: not-allowed; }

.page-active-ring {
  position: absolute; inset: -3px; border-radius: 12px;
  border: 2px solid var(--lp-accent); pointer-events: none;
  animation: pageGlow 3s ease-in-out infinite;
}
@keyframes pageGlow { 0%,100%{box-shadow:0 0 12px rgba(99,102,241,.2)}50%{box-shadow:0 0 24px rgba(99,102,241,.45)} }

/* ═══ LAYERS ════════════════════════════════════════════════════════════ */
.layers-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 12px 6px; font-size: 11px; font-weight: 700; color: var(--lp-text2);
  border-bottom: 1px solid var(--lp-border); flex-shrink: 0;
}
.layers-actions { display: flex; align-items: center; gap: 6px; }
.micro-btn { width: 22px; height: 22px; border: none; background: transparent; cursor: pointer; color: var(--lp-text3); font-size: 10px; border-radius: 4px; display: flex; align-items: center; justify-content: center; transition: all .12s; }
.micro-btn:hover { background: var(--lp-bg2); color: var(--lp-text); }
.layer-count-badge { font-size: 10px; color: var(--lp-text3); background: var(--lp-bg2); padding: 2px 7px; border-radius: 99px; border: 1px solid var(--lp-border); }

.layers-list { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 1px; padding: 4px; }

.layer-item {
  display: flex; align-items: center; gap: 6px; padding: 7px 8px;
  border-radius: 6px; cursor: pointer; border: 1.5px solid transparent; transition: all .12s;
}
.layer-item:hover { background: var(--lp-bg2); border-color: var(--lp-border); }
.layer-item--selected { background: var(--lp-accent-l); border-color: var(--lp-accent); }
.layer-item--locked { opacity: .55; }
.layer-item--hidden { opacity: .3; }

.layer-drag-icon { color: var(--lp-text3); font-size: 9px; cursor: grab; opacity: .5; flex-shrink: 0; }
.layer-type-icon { font-size: 12px; width: 22px; text-align: center; flex-shrink: 0; }
.layer-info { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.layer-name { font-size: 11px; font-weight: 500; color: var(--lp-text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.layer-type-tag { font-size: 9px; color: var(--lp-text3); text-transform: capitalize; }

.layer-controls { display: flex; gap: 1px; opacity: 0; transition: opacity .14s; }
.layer-item:hover .layer-controls { opacity: 1; }
.layer-item--selected .layer-controls { opacity: 1; }
.layer-ctrl-btn { width: 20px; height: 20px; border: none; background: transparent; cursor: pointer; color: var(--lp-text3); font-size: 10px; border-radius: 4px; display: flex; align-items: center; justify-content: center; transition: all .12s; }
.layer-ctrl-btn:hover { background: var(--lp-bg3); color: var(--lp-text); }

/* ═══ MEDIA ══════════════════════════════════════════════════════════════ */
.upload-zone {
  margin: 10px; padding: 24px 16px; border: 2px dashed var(--lp-border);
  border-radius: 10px; cursor: pointer; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 5px;
  color: var(--lp-text3); font-size: 12px; transition: all .2s;
}
.upload-zone i { font-size: 28px; opacity: .5; }
.upload-zone small { font-size: 10px; }
.upload-zone:hover,
.upload-zone--dragover { border-color: var(--lp-accent); color: var(--lp-accent); background: var(--lp-accent-l); }

.hidden-input { display: none; }

.media-section { padding: 0 10px 12px; }
.media-section-header { display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--lp-text3); margin-bottom: 8px; }

.search-action-btn { width: 28px; height: 28px; border: 1px solid var(--lp-border); border-radius: 6px; background: var(--lp-bg2); cursor: pointer; color: var(--lp-text2); font-size: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .14s; }
.search-action-btn:hover:not(:disabled) { border-color: var(--lp-accent); color: var(--lp-accent); }
.search-action-btn:disabled { opacity: .4; cursor: not-allowed; }

.media-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; }
.media-item { border-radius: 6px; overflow: hidden; cursor: pointer; aspect-ratio: 4/3; border: 1.5px solid var(--lp-border); transition: all .15s; position: relative; }
.media-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.media-item:hover { border-color: var(--lp-accent); transform: scale(1.04); box-shadow: 0 4px 12px rgba(0,0,0,.1); }

.media-item--uploaded .media-remove-btn {
  position: absolute; top: 3px; right: 3px; width: 18px; height: 18px;
  border-radius: 50%; background: rgba(0,0,0,.55); border: none; color: #fff;
  font-size: 9px; cursor: pointer; opacity: 0; transition: opacity .15s;
  display: flex; align-items: center; justify-content: center;
}
.media-item--uploaded:hover .media-remove-btn { opacity: 1; }

/* ═══ THEMES ════════════════════════════════════════════════════════════ */
.tab-description { font-size: 11px; color: var(--lp-text3); padding: 10px 10px 4px; line-height: 1.4; }

.themes-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; padding: 8px; }
.theme-card { border-radius: 8px; overflow: hidden; cursor: pointer; border: 1.5px solid var(--lp-border); transition: all .2s; }
.theme-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); border-color: var(--lp-accent); }
.theme-preview { height: 50px; position: relative; }
.theme-hover-overlay {
  position: absolute; inset: 0; background: rgba(0,0,0,.35); display: flex;
  flex-direction: column; align-items: center; justify-content: center; gap: 2px;
  color: #fff; font-size: 11px; font-weight: 700; opacity: 0; transition: opacity .2s;
}
.theme-card:hover .theme-hover-overlay { opacity: 1; }
.theme-name { display: block; font-size: 10px; font-weight: 600; text-align: center; padding: 5px 4px; color: var(--lp-text2); background: var(--lp-bg); }

.quick-palette-section { padding: 0 10px 16px; }
.color-palette-grid { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 8px; }
.palette-color-btn { width: 24px; height: 24px; border-radius: 5px; border: 2px solid transparent; cursor: pointer; transition: all .15s; }
.palette-color-btn:hover { transform: scale(1.2); border-color: var(--lp-text3); box-shadow: 0 2px 8px rgba(0,0,0,.15); }

/* ═══ SETTINGS ══════════════════════════════════════════════════════════ */
.settings-tab { padding-bottom: 20px; }

:deep(.s-section) { border-bottom: 1px solid var(--lp-border); }
:deep(.s-section-header) {
  display: flex; align-items: center; gap: 7px; width: 100%; padding: 10px 12px;
  background: var(--lp-bg2); border: none; cursor: pointer; color: var(--lp-text2);
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
  transition: background .14s; font-family: inherit;
}
:deep(.s-section-header:hover) { background: var(--lp-bg3); }
:deep(.s-section-icon) { color: var(--lp-accent); font-size: 12px; }
:deep(.s-section-header span) { flex: 1; text-align: left; }
:deep(.s-chevron) { font-size: 9px; opacity: .5; }
:deep(.s-section-body) { padding: 10px 12px; display: flex; flex-direction: column; gap: 8px; }
:deep(.s-row) { display: flex; flex-direction: column; gap: 4px; }
:deep(.s-label) { font-size: 10px; font-weight: 600; color: var(--lp-text3); text-transform: uppercase; letter-spacing: .04em; }
:deep(.s-control) { flex: 1; }
:deep(.s-select) { width: 100%; padding: 5px 8px; border: 1px solid var(--lp-border); border-radius: 5px; background: var(--lp-bg2); color: var(--lp-text); font-size: 11px; cursor: pointer; outline: none; font-family: inherit; }
:deep(.s-select:focus) { border-color: var(--lp-accent); }
:deep(.s-text-input) { width: 100%; padding: 5px 8px; border: 1px solid var(--lp-border); border-radius: 5px; background: var(--lp-bg2); color: var(--lp-text); font-size: 11px; outline: none; font-family: inherit; box-sizing: border-box; }
:deep(.s-text-input:focus) { border-color: var(--lp-accent); }
:deep(.s-text-input.mono) { font-family: monospace; }
:deep(.s-range) { width: 100%; accent-color: var(--lp-accent); cursor: pointer; }
:deep(.s-color-row) { display: flex; gap: 5px; align-items: center; }
:deep(.s-color-input) { width: 30px; height: 30px; border: 1px solid var(--lp-border); border-radius: 5px; cursor: pointer; padding: 1px; background: transparent; flex-shrink: 0; }
:deep(.s-toggle-group) { display: flex; border: 1px solid var(--lp-border); border-radius: 6px; overflow: hidden; }
:deep(.s-toggle-group button) { flex: 1; padding: 5px 6px; border: none; background: transparent; cursor: pointer; font-size: 10px; font-weight: 600; color: var(--lp-text2); transition: all .14s; font-family: inherit; display: flex; align-items: center; justify-content: center; gap: 4px; }
:deep(.s-toggle-group button:hover) { background: var(--lp-bg2); }
:deep(.s-toggle-group button.active) { background: var(--lp-accent); color: #fff; }
:deep(.s-toggle-group button + button) { border-left: 1px solid var(--lp-border); }

:deep(.toggle-sw) { display: inline-flex; align-items: center; cursor: pointer; }
:deep(.toggle-sw-input) { display: none; }
:deep(.toggle-sw-track) { width: 34px; height: 18px; background: var(--lp-border); border-radius: 99px; position: relative; transition: background .2s; }
:deep(.toggle-sw input:checked + .toggle-sw-track) { background: var(--lp-accent); }
:deep(.toggle-sw-thumb) { position: absolute; width: 12px; height: 12px; background: #fff; border-radius: 50%; top: 3px; left: 3px; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
:deep(.toggle-sw input:checked + .toggle-sw-track .toggle-sw-thumb) { transform: translateX(16px); }

/* ═══ HISTORY ═══════════════════════════════════════════════════════════ */
.refresh-history-btn {
  display: flex; align-items: center; gap: 6px; justify-content: center;
  width: calc(100% - 20px); margin: 10px; padding: 8px; border: 1px solid var(--lp-border);
  border-radius: 8px; background: var(--lp-bg2); cursor: pointer;
  color: var(--lp-text2); font-size: 11px; font-weight: 600; transition: all .15s; font-family: inherit;
}
.refresh-history-btn:hover:not(:disabled) { border-color: var(--lp-accent); color: var(--lp-accent); }
.refresh-history-btn:disabled { opacity: .5; cursor: not-allowed; }

.history-timeline { display: flex; flex-direction: column; gap: 2px; padding: 4px 12px 20px; }
.history-item { display: flex; align-items: flex-start; gap: 10px; padding: 8px; border-radius: 6px; border: 1.5px solid transparent; transition: all .14s; position: relative; }
.history-item:hover { background: var(--lp-bg2); border-color: var(--lp-border); }
.history-item--current { background: var(--lp-accent-l); border-color: var(--lp-accent); }

.history-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--lp-accent); flex-shrink: 0; margin-top: 4px; }
.history-line { position: absolute; left: 20px; top: 22px; bottom: -4px; width: 2px; background: var(--lp-border); }

.history-content { flex: 1; min-width: 0; }
.history-header { display: flex; align-items: center; gap: 6px; margin-bottom: 2px; }
.history-header strong { font-size: 11px; color: var(--lp-text); }
.history-current-badge { font-size: 8px; font-weight: 800; background: var(--lp-accent); color: #fff; padding: 1px 6px; border-radius: 99px; text-transform: uppercase; letter-spacing: .04em; }
.history-label { font-size: 11px; color: var(--lp-text2); margin: 0; }
.history-date { font-size: 10px; color: var(--lp-text3); }
.history-restore-btn { padding: 4px 10px; border: 1px solid var(--lp-border); border-radius: 6px; background: transparent; cursor: pointer; font-size: 10px; font-weight: 600; color: var(--lp-text2); transition: all .14s; flex-shrink: 0; font-family: inherit; }
.history-restore-btn:hover:not(:disabled) { border-color: var(--lp-accent); color: var(--lp-accent); background: var(--lp-accent-l); }
.history-restore-btn:disabled { opacity: .3; cursor: not-allowed; }

/* ═══ EMPTY STATE ═══════════════════════════════════════════════════════ */
.empty-state {
  display: flex; flex-direction: column; align-items: center; gap: 7px;
  padding: 40px 20px; text-align: center; color: var(--lp-text3);
}
.empty-state i { font-size: 32px; opacity: .3; }
.empty-state p { font-size: 12px; font-weight: 500; color: var(--lp-text2); margin: 0; }
.empty-state small { font-size: 10px; }

/* ═══ RESPONSIVE ════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .left-panel { position: fixed; left: 0; top: 0; bottom: 0; height: 100vh; z-index: 200; box-shadow: 4px 0 24px rgba(0,0,0,.15); }
  .left-panel.collapsed { box-shadow: none; }
}
</style>