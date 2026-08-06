<!--
  LeftSidebar.vue — 7-tab report editor sidebar (Part 3 complete)
  ═══════════════════════════════════════════════════════════════════
  Tabs:
  1. Elements  — Canva-style catalog; 50+ types in searchable groups;
                 draggable to canvas; double-click to quick-insert
  2. Pages     — thumbnail strip; drag-to-reorder (HTML5 DnD);
                 per-page action buttons; add/dup/del
  3. Layers    — element list for current page; click → locate on canvas;
                 drag to reorder z-index; eye + lock toggles
  4. Media     — image upload + free keyword image search
                 (Pixabay/Unsplash/Picsum proxy)
  5. Themes    — select pre-built colour + font themes; apply report-wide
  6. Settings  — ALL report-wide settings (header/footer, page numbers,
                 watermark, margin/padding, background, font, RTL, etc.)
  7. History   — snapshot list; click to jump; current position indicator
  ═══════════════════════════════════════════════════════════════════
-->
<template>
  <aside class="ls-root" :class="{ 'ls-dark': isDark }" aria-label="Editor sidebar">

    <!-- ══ TAB RAIL ══════════════════════════════════════════════════ -->
    <nav class="ls-rail" role="tablist" aria-label="Sidebar tabs">
      <button v-for="t in TABS" :key="t.id" class="ls-rail-btn" :class="{ active: activeTab === t.id }"
        @click="activeTab = t.id" role="tab" :aria-selected="activeTab === t.id" :aria-controls="`ls-panel-${t.id}`"
        :title="t.label">
        <i :class="t.icon" />
        <span class="ls-rail-label">{{ t.label }}</span>
      </button>
    </nav>

    <!-- ══ TAB PANELS ════════════════════════════════════════════════ -->
    <div class="ls-content">

      <!-- ─── 1. ELEMENTS ──────────────────────────────────────────── -->
      <div v-show="activeTab === 'elements'" id="ls-panel-elements" role="tabpanel" class="ls-panel">
        <div class="ls-panel-head">
          <span class="ls-panel-title">Elements</span>
          <div class="ls-search-wrap">
            <i class="fa-solid fa-magnifying-glass ls-search-icon" />
            <input v-model="elSearch" placeholder="Search elements…" class="ls-search-input"
              aria-label="Search elements" />
            <button v-if="elSearch" class="ls-search-clear" @click="elSearch = ''" aria-label="Clear search">
              <i class="fa-solid fa-xmark" />
            </button>
          </div>
        </div>

        <div class="ls-el-catalog">
          <template v-for="group in filteredCatalog" :key="group.id">
            <div class="ls-el-group">
              <button class="ls-el-group-head" @click="toggleGroup(group.id)"
                :aria-expanded="!collapsedGroups.includes(group.id)">
                <i :class="group.icon" />
                <span>{{ group.label }}</span>
                <i :class="collapsedGroups.includes(group.id) ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'"
                  class="ls-group-arrow" />
              </button>

              <div v-show="!collapsedGroups.includes(group.id)" class="ls-el-grid">
                <div v-for="el in group.items" :key="el.type" class="ls-el-card" draggable="true"
                  @dragstart="onElDragStart($event, el)" @dblclick="quickInsert(el)"
                  :title="`${el.label} — drag to canvas or double-click`" role="button"
                  :aria-label="`Add ${el.label} element`" tabindex="0" @keydown.enter="quickInsert(el)">
                  <div class="ls-el-icon" :style="{ color: group.color }">
                    <i :class="el.icon" />
                  </div>
                  <span class="ls-el-label">{{ el.label }}</span>
                </div>
              </div>
            </div>
          </template>

          <div v-if="filteredCatalog.length === 0" class="ls-empty">
            <i class="fa-solid fa-face-meh" />
            <span>No elements match "{{ elSearch }}"</span>
          </div>
        </div>
      </div>

      <!-- ─── 2. PAGES ─────────────────────────────────────────────── -->
      <div v-show="activeTab === 'pages'" id="ls-panel-pages" role="tabpanel" class="ls-panel">
        <div class="ls-panel-head">
          <span class="ls-panel-title">Pages ({{ report.content.length }})</span>
          <button class="ls-head-btn" @click="$emit('add-page')" title="Add page" aria-label="Add page">
            <i class="fa-solid fa-plus" />
          </button>
        </div>

        <div class="ls-pages-list" ref="pagesListRef">
          <div v-for="(page, pi) in report.content" :key="page.id" class="ls-page-item" :class="{
            'ls-page-active': currentPage === pi,
            'ls-page-drag-over': pageDragOver === pi,
          }" draggable="true" @dragstart="onPageDragStart($event, pi)" @dragend="onPageDragEnd"
            @dragover.prevent="onPageDragOver($event, pi)" @dragleave="pageDragOver = null"
            @drop.prevent="onPageDrop($event, pi)" @click="$emit('select-page', pi)"
            :aria-label="`Page ${pi + 1}${currentPage === pi ? ', current' : ''}`" role="button"
            :aria-pressed="currentPage === pi">
            <!-- Thumbnail -->
            <div class="ls-page-thumb" :style="getPageThumbStyle(page)">
              <span v-if="!(page.elements || []).length" class="ls-page-thumb-empty">
                <i class="fa-regular fa-file" />
              </span>
              <!-- Mini element previews -->
              <div v-for="el in (page.elements || []).slice(0, 8)" :key="el.id" class="ls-page-mini-el"
                :style="getMiniElStyle(el)" />
              <div class="ls-page-drag-handle" title="Drag to reorder">
                <i class="fa-solid fa-grip-vertical" />
              </div>
            </div>

            <!-- Page info -->
            <div class="ls-page-info">
              <span class="ls-page-num">{{ pi + 1 }}</span>
              <span class="ls-page-elcount">{{ (page.elements || []).length }} el{{ (page.elements || []).length !== 1 ?
                's' :
                '' }}</span>
            </div>

            <!-- Per-page actions -->
            <div class="ls-page-actions">
              <button @click.stop="$emit('add-page-before', pi)" title="Insert page before" class="lpa-btn"><i
                  class="fa-solid fa-arrow-up-to-line" /></button>
              <button @click.stop="$emit('add-page-after', pi)" title="Insert page after" class="lpa-btn"><i
                  class="fa-solid fa-arrow-down-to-line" /></button>
              <button @click.stop="$emit('duplicate-page', pi)" title="Duplicate" class="lpa-btn"><i
                  class="fa-solid fa-copy" /></button>
              <button @click.stop="$emit('move-page-up', pi)" title="Move up" class="lpa-btn" :disabled="pi === 0"><i
                  class="fa-solid fa-chevron-up" /></button>
              <button @click.stop="$emit('move-page-down', pi)" title="Move down" class="lpa-btn"
                :disabled="pi === report.content.length - 1"><i class="fa-solid fa-chevron-down" /></button>
              <button @click.stop="$emit('delete-page', pi)" title="Delete page" class="lpa-btn lpa-btn--danger"
                :disabled="report.content.length <= 1"><i class="fa-solid fa-trash" /></button>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── 3. LAYERS ────────────────────────────────────────────── -->
      <div v-show="activeTab === 'layers'" id="ls-panel-layers" role="tabpanel" class="ls-panel">
        <div class="ls-panel-head">
          <span class="ls-panel-title">Layers — Page {{ currentPage + 1 }}</span>
          <span class="ls-el-count-badge">{{ currentPageElements.length }}</span>
        </div>

        <div v-if="currentPageElements.length" class="ls-layers-list">
          <div v-for="(el, ei) in [...currentPageElements].reverse()" :key="el.id" class="ls-layer-item" :class="{
            'ls-layer-selected': isElSelected(currentPageElements.length - 1 - ei),
            'ls-layer-locked': el.locked,
            'ls-layer-hidden': el.visible === false,
            'ls-layer-drag-over': layerDragOver === ei,
          }" draggable="true" @dragstart="onLayerDragStart($event, ei)" @dragend="layerDragOver = null"
            @dragover.prevent="onLayerDragOver($event, ei)" @dragleave="layerDragOver = null"
            @drop.prevent="onLayerDrop($event, ei)" @click="locateElement(currentPageElements.length - 1 - ei)"
            role="button"
            :aria-label="`${el.type} element${el.locked ? ', locked' : ''}${el.visible === false ? ', hidden' : ''}`">
            <div class="ls-layer-drag" title="Drag to reorder">
              <i class="fa-solid fa-grip-vertical" />
            </div>

            <div class="ls-layer-icon" :title="el.type">
              <i :class="getElIcon(el.type)" />
            </div>

            <div class="ls-layer-name">
              <span class="ls-layer-type">{{ el.type }}</span>
              <span class="ls-layer-preview">{{ getElPreview(el) }}</span>
            </div>

            <div class="ls-layer-controls">
              <button class="ls-layer-btn" @click.stop="toggleVisibility(currentPageElements.length - 1 - ei)"
                :title="el.visible === false ? 'Show' : 'Hide'"
                :aria-label="el.visible === false ? 'Show element' : 'Hide element'"><i
                  :class="el.visible === false ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'" /></button>
              <button class="ls-layer-btn" @click.stop="toggleLock(currentPageElements.length - 1 - ei)"
                :title="el.locked ? 'Unlock' : 'Lock'" :aria-label="el.locked ? 'Unlock element' : 'Lock element'"><i
                  :class="el.locked ? 'fa-solid fa-lock' : 'fa-regular fa-lock-open'" /></button>
            </div>
          </div>
        </div>

        <div v-else class="ls-empty">
          <i class="fa-solid fa-layer-group" />
          <span>No elements on this page yet. Drag elements from the Elements tab.</span>
        </div>
      </div>

      <!-- ─── 4. MEDIA ─────────────────────────────────────────────── -->
      <div v-show="activeTab === 'media'" id="ls-panel-media" role="tabpanel" class="ls-panel">
        <div class="ls-panel-head">
          <span class="ls-panel-title">Media</span>
        </div>

        <!-- Upload -->
        <div class="ls-media-upload-zone" @click="triggerUpload" @dragover.prevent="uploadDragOver = true"
          @dragleave="uploadDragOver = false" @drop.prevent="onUploadDrop" :class="{ 'is-over': uploadDragOver }"
          role="button" aria-label="Upload image — click or drag and drop">
          <i class="fa-solid fa-cloud-arrow-up" />
          <span>Click or drop images to upload</span>
          <small>PNG, JPG, GIF, WEBP — max 10 MB</small>
          <input ref="uploadInputRef" type="file" accept="image/*" multiple class="sr-only" @change="onUploadChange" />
        </div>

        <!-- Search -->
        <div class="ls-media-search-bar">
          <div class="ls-search-wrap">
            <i class="fa-solid fa-magnifying-glass ls-search-icon" />
            <input v-model="imgQuery" placeholder="Search free images…" class="ls-search-input"
              @keydown.enter="searchImages" aria-label="Search free images" />
            <button v-if="imgQuery" class="ls-search-clear" @click="imgQuery = ''" aria-label="Clear">
              <i class="fa-solid fa-xmark" />
            </button>
          </div>
          <button class="ls-search-btn" @click="searchImages" :disabled="imgSearching" aria-label="Search">
            <i v-if="imgSearching" class="fa-solid fa-spinner fa-spin" />
            <i v-else class="fa-solid fa-search" />
          </button>
        </div>

        <div v-if="imgSource" class="ls-media-source-badge">
          <i class="fa-solid fa-circle-info" /> Images from {{ imgSource }}
        </div>

        <!-- Results grid -->
        <div v-if="imgResults.length" class="ls-media-grid">
          <div v-for="img in imgResults" :key="img.id" class="ls-media-card" @click="insertImage(img)"
            :title="`Insert: ${img.alt}`" role="button" :aria-label="`Insert image: ${img.alt}`">
            <img :src="img.thumb" :alt="img.alt" loading="lazy" />
            <div class="ls-media-card-overlay">
              <i class="fa-solid fa-plus" />
              <span>Insert</span>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="imgResults.length" class="ls-media-pagination">
          <button class="ls-pg-btn" @click="imgPage--; searchImages()" :disabled="imgPage <= 1">
            <i class="fa-solid fa-chevron-left" /> Prev
          </button>
          <span>Page {{ imgPage }}</span>
          <button class="ls-pg-btn" @click="imgPage++; searchImages()">
            Next <i class="fa-solid fa-chevron-right" />
          </button>
        </div>

        <div v-if="!imgResults.length && !imgSearching && imgQuery" class="ls-empty">
          <i class="fa-solid fa-image-slash" />
          <span>No images found for "{{ imgQuery }}"</span>
        </div>

        <div v-if="!imgQuery && !imgResults.length" class="ls-empty">
          <i class="fa-solid fa-images" />
          <span>Search for free photos to insert into your report</span>
        </div>
      </div>

      <!-- ─── 5. THEMES ────────────────────────────────────────────── -->
      <div v-show="activeTab === 'themes'" id="ls-panel-themes" role="tabpanel" class="ls-panel">
        <div class="ls-panel-head">
          <span class="ls-panel-title">Themes</span>
        </div>

        <div class="ls-themes-grid">
          <button v-for="theme in THEMES" :key="theme.id" class="ls-theme-card"
            :class="{ active: isThemeActive(theme) }" @click="applyTheme(theme)"
            :aria-label="`Apply ${theme.name} theme`" :aria-pressed="isThemeActive(theme)">
            <div class="ls-theme-swatch-row">
              <div class="ls-theme-swatch" :style="{ background: theme.primary }" />
              <div class="ls-theme-swatch" :style="{ background: theme.accent }" />
              <div class="ls-theme-swatch" :style="{ background: theme.bg }" />
              <div class="ls-theme-swatch" :style="{ background: theme.text }" />
            </div>
            <div class="ls-theme-preview"
              :style="{ background: theme.bg, color: theme.text, fontFamily: theme.fontFamily }">
              <div class="ls-tp-heading" :style="{ color: theme.primary }">Report Title</div>
              <div class="ls-tp-body">Body text preview</div>
              <div class="ls-tp-accent" :style="{ background: theme.accent }" />
            </div>
            <div class="ls-theme-name">{{ theme.name }}</div>
            <div class="ls-theme-check" v-if="isThemeActive(theme)"><i class="fa-solid fa-circle-check" /></div>
          </button>
        </div>
      </div>

      <!-- ─── 6. SETTINGS ──────────────────────────────────────────── -->
      <div v-show="activeTab === 'settings'" id="ls-panel-settings" role="tabpanel" class="ls-panel">
        <div class="ls-panel-head">
          <span class="ls-panel-title">Report Settings</span>
          <button class="ls-head-btn ls-head-btn--primary" @click="resetSettings" title="Reset to defaults"
            aria-label="Reset settings">
            <i class="fa-solid fa-rotate-left" />
          </button>
        </div>

        <div class="ls-settings-body">

          <!-- Page setup -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title">
              <i class="fa-solid fa-file" /> Page Setup
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Page Size</label>
              <select class="ls-select" v-model="localSettings.page_size" @change="emitSettings">
                <option>A4</option>
                <option>A3</option>
                <option>A5</option>
                <option value="Letter">Letter</option>
                <option value="Legal">Legal</option>
              </select>
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Orientation</label>
              <div class="ls-btn-group">
                <button :class="{ active: localSettings.orientation === 'portrait' }"
                  @click="setSetting('orientation', 'portrait')">
                  <i class="fa-solid fa-rectangle-portrait" /> Portrait
                </button>
                <button :class="{ active: localSettings.orientation === 'landscape' }"
                  @click="setSetting('orientation', 'landscape')">
                  <i class="fa-solid fa-rectangle-landscape" /> Landscape
                </button>
              </div>
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Margin <span class="ls-val">{{ localSettings.margin }}px</span></label>
              <input type="range" min="0" max="120" step="4" v-model.number="localSettings.margin" @input="emitSettings"
                class="ls-range" />
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Padding <span class="ls-val">{{ localSettings.padding }}px</span></label>
              <input type="range" min="0" max="80" step="4" v-model.number="localSettings.padding" @input="emitSettings"
                class="ls-range" />
            </div>
          </div>

          <!-- Background -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-fill" /> Background</div>
            <div class="ls-field-row">
              <label class="ls-label">Background Color</label>
              <input type="color" v-model="localSettings.background_color" @input="emitSettings" class="ls-color" />
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Text Color</label>
              <input type="color" v-model="localSettings.text_color" @input="emitSettings" class="ls-color" />
            </div>
          </div>

          <!-- Brand colors -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-palette" /> Brand Colors</div>
            <div class="ls-field-row">
              <label class="ls-label">Primary</label>
              <div class="ls-color-with-input">
                <input type="color" v-model="localSettings.primary_color" @input="emitSettings" class="ls-color" />
                <input type="text" v-model="localSettings.primary_color" @input="emitSettings" class="ls-text-sm"
                  maxlength="7" />
              </div>
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Accent</label>
              <div class="ls-color-with-input">
                <input type="color" v-model="localSettings.accent_color" @input="emitSettings" class="ls-color" />
                <input type="text" v-model="localSettings.accent_color" @input="emitSettings" class="ls-text-sm"
                  maxlength="7" />
              </div>
            </div>
          </div>

          <!-- Typography -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-font" /> Typography</div>
            <div class="ls-field-row">
              <label class="ls-label">Font Family</label>
              <select class="ls-select" v-model="localSettings.font_family" @change="emitSettings">
                <option v-for="f in FONTS" :key="f" :value="f" :style="{ fontFamily: f }">{{ f }}</option>
              </select>
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Base Font Size <span class="ls-val">{{ localSettings.font_size }}px</span></label>
              <input type="range" min="10" max="24" step="1" v-model.number="localSettings.font_size"
                @input="emitSettings" class="ls-range" />
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Line Height <span class="ls-val">{{ localSettings.line_height }}</span></label>
              <input type="range" min="1" max="3" step="0.05" v-model.number="localSettings.line_height"
                @input="emitSettings" class="ls-range" />
            </div>
            <div class="ls-field-row">
              <label class="ls-label">RTL Direction</label>
              <label class="ls-toggle">
                <input type="checkbox" v-model="localSettings.rtl" @change="emitSettings" />
                <span class="ls-toggle-track" /><span class="ls-toggle-label">{{ localSettings.rtl ? 'On' : 'Off'
                  }}</span>
              </label>
            </div>
          </div>

          <!-- Header -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-rectangle-ad" /> Header</div>
            <div class="ls-field-row">
              <label class="ls-label">Show Header</label>
              <label class="ls-toggle">
                <input type="checkbox" v-model="localSettings.show_header" @change="emitSettings" />
                <span class="ls-toggle-track" /><span class="ls-toggle-label">{{ localSettings.show_header ? 'On' :
                  'Off'
                  }}</span>
              </label>
            </div>
            <template v-if="localSettings.show_header">
              <div class="ls-field-row">
                <label class="ls-label">Header Text</label>
                <input class="ls-input" v-model="localSettings.header_text" @input="emitSettings"
                  placeholder="e.g. Annual Report 2025" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Height <span class="ls-val">{{ localSettings.header_height }}px</span></label>
                <input type="range" min="24" max="120" step="4" v-model.number="localSettings.header_height"
                  @input="emitSettings" class="ls-range" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Background</label>
                <input type="color" v-model="localSettings.header_color" @input="emitSettings" class="ls-color" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Text Color</label>
                <input type="color" v-model="localSettings.header_text_color" @input="emitSettings" class="ls-color" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Position</label>
                <select class="ls-select" v-model="localSettings.header_position" @change="emitSettings">
                  <option value="top">Top of page</option>
                  <option value="inside-top">Inside page (top)</option>
                </select>
              </div>
            </template>
          </div>

          <!-- Footer -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-rectangle-ad fa-rotate-180" /> Footer</div>
            <div class="ls-field-row">
              <label class="ls-label">Show Footer</label>
              <label class="ls-toggle">
                <input type="checkbox" v-model="localSettings.show_footer" @change="emitSettings" />
                <span class="ls-toggle-track" /><span class="ls-toggle-label">{{ localSettings.show_footer ? 'On' :
                  'Off'
                  }}</span>
              </label>
            </div>
            <template v-if="localSettings.show_footer">
              <div class="ls-field-row">
                <label class="ls-label">Left Text</label>
                <input class="ls-input" v-model="localSettings.footer_left" @input="emitSettings"
                  placeholder="Company Name" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Right Text</label>
                <input class="ls-input" v-model="localSettings.footer_right" @input="emitSettings"
                  placeholder="Page {n} of {total}" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Footer Color</label>
                <input type="color" v-model="localSettings.footer_color" @input="emitSettings" class="ls-color" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Footer Height <span class="ls-val">{{ localSettings.footer_height || 36
                    }}px</span></label>
                <input type="range" min="24" max="80" step="4" v-model.number="localSettings.footer_height"
                  @input="emitSettings" class="ls-range" />
              </div>
            </template>
          </div>

          <!-- Page Numbers -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-list-ol" /> Page Numbers</div>
            <div class="ls-field-row">
              <label class="ls-label">Show Page Numbers</label>
              <label class="ls-toggle">
                <input type="checkbox" v-model="localSettings.show_page_numbers" @change="emitSettings" />
                <span class="ls-toggle-track" /><span class="ls-toggle-label">{{ localSettings.show_page_numbers ? 'On'
                  :
                  'Off' }}</span>
              </label>
            </div>
            <template v-if="localSettings.show_page_numbers">
              <div class="ls-field-row">
                <label class="ls-label">Style</label>
                <select class="ls-select" v-model="localSettings.page_number_style" @change="emitSettings">
                  <option value="decimal">1, 2, 3 …</option>
                  <option value="of">Page 1 of N</option>
                  <option value="roman">i, ii, iii …</option>
                  <option value="alpha">A, B, C …</option>
                </select>
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Position</label>
                <select class="ls-select" v-model="localSettings.page_number_position" @change="emitSettings">
                  <option value="footer-left">Footer Left</option>
                  <option value="footer-center">Footer Center</option>
                  <option value="footer-right">Footer Right</option>
                  <option value="header-left">Header Left</option>
                  <option value="header-center">Header Center</option>
                  <option value="header-right">Header Right</option>
                </select>
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Start From</label>
                <input type="number" min="1" max="999" class="ls-input ls-input--sm"
                  v-model.number="localSettings.page_number_start" @input="emitSettings" />
              </div>
            </template>
          </div>

          <!-- Watermark -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-droplet" /> Watermark</div>
            <div class="ls-field-row">
              <label class="ls-label">Watermark Text</label>
              <input class="ls-input" v-model="localSettings.watermark" @input="emitSettings"
                placeholder="e.g. CONFIDENTIAL, DRAFT" />
            </div>
            <template v-if="localSettings.watermark">
              <div class="ls-field-row">
                <label class="ls-label">Color</label>
                <input type="color" v-model="localSettings.watermark_color" @input="emitSettings" class="ls-color" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Opacity <span class="ls-val">{{ localSettings.watermark_opacity
                    }}%</span></label>
                <input type="range" min="1" max="60" step="1" v-model.number="localSettings.watermark_opacity"
                  @input="emitSettings" class="ls-range" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Rotation <span class="ls-val">{{ localSettings.watermark_rotate
                    }}°</span></label>
                <input type="range" min="-90" max="90" step="5" v-model.number="localSettings.watermark_rotate"
                  @input="emitSettings" class="ls-range" />
              </div>
              <div class="ls-field-row">
                <label class="ls-label">Font Size <span class="ls-val">{{ localSettings.watermark_size || 72
                    }}px</span></label>
                <input type="range" min="24" max="200" step="4" v-model.number="localSettings.watermark_size"
                  @input="emitSettings" class="ls-range" />
              </div>
            </template>
          </div>

          <!-- Advanced -->
          <div class="ls-settings-section">
            <div class="ls-settings-section-title"><i class="fa-solid fa-sliders" /> Advanced</div>
            <div class="ls-field-row">
              <label class="ls-label">Page Border Radius <span class="ls-val">{{ localSettings.page_radius || 0
                  }}px</span></label>
              <input type="range" min="0" max="24" step="2" v-model.number="localSettings.page_radius"
                @input="emitSettings" class="ls-range" />
            </div>
            <div class="ls-field-row">
              <label class="ls-label">Page Shadow</label>
              <label class="ls-toggle">
                <input type="checkbox" v-model="localSettings.page_shadow" @change="emitSettings" />
                <span class="ls-toggle-track" /><span class="ls-toggle-label">{{ localSettings.page_shadow ? 'On' :
                  'Off'
                  }}</span>
              </label>
            </div>
          </div>

        </div><!-- /ls-settings-body -->
      </div>

      <!-- ─── 7. HISTORY ────────────────────────────────────────────── -->
      <div v-show="activeTab === 'history'" id="ls-panel-history" role="tabpanel" class="ls-panel">
        <div class="ls-panel-head">
          <span class="ls-panel-title">History</span>
          <span class="ls-el-count-badge">{{ history.length }}</span>
        </div>

        <div v-if="history.length" class="ls-history-list">
          <div v-for="(snap, idx) in [...history].reverse()" :key="idx" class="ls-history-item" :class="{
            'ls-history-current': history.length - 1 - idx === historyIndex,
            'ls-history-future': history.length - 1 - idx > historyIndex,
          }" @click="$emit('history-jump', history.length - 1 - idx)" role="button"
            :aria-label="`Restore snapshot ${idx + 1}`">
            <div class="ls-hist-icon">
              <i v-if="history.length - 1 - idx === historyIndex" class="fa-solid fa-circle-dot" />
              <i v-else-if="history.length - 1 - idx > historyIndex" class="fa-regular fa-circle" style="opacity:.35" />
              <i v-else class="fa-solid fa-circle-arrow-left" />
            </div>
            <div class="ls-hist-info">
              <span class="ls-hist-label">Snapshot {{ history.length - idx }}</span>
              <span class="ls-hist-sub">{{ getSnapSummary(snap) }}</span>
            </div>
            <div v-if="history.length - 1 - idx === historyIndex" class="ls-hist-current-badge">current</div>
          </div>
        </div>

        <div v-else class="ls-empty">
          <i class="fa-solid fa-clock-rotate-left" />
          <span>No history yet — start editing to build a timeline</span>
        </div>
      </div>

    </div><!-- /ls-content -->
  </aside>
</template>

<script setup>
import { ref, computed, reactive, watch, onMounted } from 'vue'

// ── Props ───────────────────────────────────────────────────────────
const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
  currentPage: { type: Number, default: 0 },
  isDark: { type: Boolean, default: false },
  history: { type: Array, default: () => [] },
  historyIndex: { type: Number, default: -1 },
})

// ── Emits ───────────────────────────────────────────────────────────
const emit = defineEmits([
  'add-element', 'select-page', 'add-page',
  'add-page-before', 'add-page-after',
  'duplicate-page', 'delete-page',
  'move-page-up', 'move-page-down',
  'reorder-pages',
  'update-settings',
  'select-layer-element',
  'history-jump',
  'image-upload', 'image-search',
  'mark-dirty',
])

// ── Tab state ───────────────────────────────────────────────────────
const TABS = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-shapes' },
  { id: 'pages', label: 'Pages', icon: 'fa-solid fa-book-open' },
  { id: 'layers', label: 'Layers', icon: 'fa-solid fa-layer-group' },
  { id: 'media', label: 'Media', icon: 'fa-solid fa-photo-film' },
  { id: 'themes', label: 'Themes', icon: 'fa-solid fa-wand-sparkles' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-gear' },
  { id: 'history', label: 'History', icon: 'fa-solid fa-clock-rotate-left' },
]
const activeTab = ref('elements')

// ── Element Catalog ─────────────────────────────────────────────────
const elSearch = ref('')
const collapsedGroups = ref([])

const CATALOG = [
  {
    id: 'text', label: 'Text', icon: 'fa-solid fa-font', color: '#6366f1',
    items: [
      { type: 'heading', label: 'Heading', icon: 'fa-solid fa-heading', w: 500, h: 60, defaultContent: 'Report Heading', styles: { fontSize: 32, fontWeight: '700' } },
      { type: 'subheading', label: 'Subheading', icon: 'fa-solid fa-text-height', w: 400, h: 44, defaultContent: 'Section Subheading', styles: { fontSize: 20, fontWeight: '600' } },
      { type: 'text', label: 'Paragraph', icon: 'fa-solid fa-align-left', w: 400, h: 100, defaultContent: 'Start typing your paragraph text here. This is a body text block suitable for report content.' },
      { type: 'quote', label: 'Quote', icon: 'fa-solid fa-quote-left', w: 380, h: 80, defaultContent: 'Inspiring quote goes here.' },
      { type: 'blockquote', label: 'Blockquote', icon: 'fa-solid fa-block-quote', w: 380, h: 100, defaultContent: 'Extended blockquote with more content.' },
      { type: 'list', label: 'List', icon: 'fa-solid fa-list', w: 280, h: 120 },
      { type: 'highlight', label: 'Highlight', icon: 'fa-solid fa-highlighter', w: 200, h: 40, defaultContent: 'Highlighted text' },
      { type: 'callout', label: 'Callout', icon: 'fa-solid fa-bullhorn', w: 380, h: 80, defaultContent: 'Important callout message' },
      { type: 'badge', label: 'Badge', icon: 'fa-solid fa-certificate', w: 100, h: 34, defaultContent: 'Badge' },
      { type: 'link', label: 'Link', icon: 'fa-solid fa-link', w: 200, h: 34, defaultContent: 'https://example.com' },
      { type: 'code', label: 'Code Block', icon: 'fa-solid fa-code', w: 400, h: 160, defaultContent: '// Your code here\nconsole.log("Hello, World!");' },
      { type: 'richtext', label: 'Rich Text', icon: 'fa-solid fa-file-pen', w: 400, h: 200 },
    ],
  },
  {
    id: 'data', label: 'Data & Charts', icon: 'fa-solid fa-chart-bar', color: '#10b981',
    items: [
      { type: 'metric', label: 'KPI Metric', icon: 'fa-solid fa-arrow-trend-up', w: 180, h: 120 },
      { type: 'progress', label: 'Progress Bar', icon: 'fa-solid fa-bars-progress', w: 300, h: 60 },
      { type: 'circular-progress', label: 'Ring Progress', icon: 'fa-solid fa-circle-notch', w: 160, h: 180 },
      { type: 'sparkline', label: 'Sparkline', icon: 'fa-solid fa-wave-square', w: 200, h: 60 },
      { type: 'stat-row', label: 'Stats Row', icon: 'fa-solid fa-table-columns', w: 500, h: 100 },
      { type: 'bar-chart', label: 'Bar Chart', icon: 'fa-solid fa-chart-column', w: 400, h: 260 },
      { type: 'line-chart', label: 'Line Chart', icon: 'fa-solid fa-chart-line', w: 400, h: 260 },
      { type: 'area-chart', label: 'Area Chart', icon: 'fa-solid fa-chart-area', w: 400, h: 260 },
      { type: 'pie-chart', label: 'Pie Chart', icon: 'fa-solid fa-chart-pie', w: 320, h: 280 },
      { type: 'doughnut-chart', label: 'Doughnut', icon: 'fa-solid fa-circle-half-stroke', w: 320, h: 280 },
      { type: 'radar-chart', label: 'Radar Chart', icon: 'fa-solid fa-circle-dot', w: 320, h: 280 },
    ],
  },
  {
    id: 'table', label: 'Tables', icon: 'fa-solid fa-table', color: '#f59e0b',
    items: [
      {
        type: 'table', label: 'Data Table', icon: 'fa-solid fa-table', w: 500, h: 200,
        defaults: {
          columns: ['Name', 'Value', 'Change', 'Status'],
          data: [
            { Name: 'Revenue', Value: '$1.2M', Change: '+12%', Status: 'Up' },
            { Name: 'Users', Value: '4,500', Change: '+5%', Status: 'Up' },
            { Name: 'Costs', Value: '$300K', Change: '-3%', Status: 'Down' },
          ],
        },
      },
    ],
  },
  {
    id: 'media', label: 'Media', icon: 'fa-solid fa-photo-film', color: '#8b5cf6',
    items: [
      { type: 'image', label: 'Image', icon: 'fa-solid fa-image', w: 300, h: 220 },
      { type: 'video', label: 'Video', icon: 'fa-solid fa-circle-play', w: 400, h: 240 },
      { type: 'map', label: 'Map', icon: 'fa-solid fa-map-location-dot', w: 400, h: 260 },
      { type: 'icon', label: 'Icon', icon: 'fa-solid fa-icons', w: 60, h: 60 },
      { type: 'avatar', label: 'Avatar', icon: 'fa-solid fa-user-circle', w: 80, h: 80 },
      { type: 'qr-code', label: 'QR Code', icon: 'fa-solid fa-qrcode', w: 160, h: 160 },
    ],
  },
  {
    id: 'shapes', label: 'Shapes', icon: 'fa-solid fa-vector-square', color: '#ef4444',
    items: [
      { type: 'rectangle', label: 'Rectangle', icon: 'fa-regular fa-square', w: 200, h: 120 },
      { type: 'circle', label: 'Circle', icon: 'fa-regular fa-circle', w: 120, h: 120 },
      { type: 'triangle', label: 'Triangle', icon: 'fa-solid fa-play fa-rotate-270', w: 120, h: 100 },
      { type: 'divider', label: 'Divider', icon: 'fa-solid fa-minus', w: 400, h: 10 },
      { type: 'arrow', label: 'Arrow', icon: 'fa-solid fa-arrow-right', w: 200, h: 40 },
      { type: 'spacer', label: 'Spacer', icon: 'fa-solid fa-arrows-up-down', w: 400, h: 40 },
    ],
  },
  {
    id: 'layout', label: 'Layout & Lists', icon: 'fa-solid fa-border-all', color: '#06b6d4',
    items: [
      { type: 'checklist', label: 'Checklist', icon: 'fa-solid fa-square-check', w: 300, h: 180, defaults: { items: [{ text: 'Task 1', checked: true }, { text: 'Task 2', checked: false }] } },
      { type: 'timeline', label: 'Timeline', icon: 'fa-solid fa-timeline', w: 380, h: 280, defaults: { items: [{ date: '2024 Q1', label: 'Milestone 1', desc: 'Project kicked off' }, { date: '2024 Q2', label: 'Milestone 2', desc: 'Phase 1 complete' }] } },
      { type: 'steps', label: 'Steps', icon: 'fa-solid fa-stairs', w: 500, h: 80, defaults: { items: [{ label: 'Plan' }, { label: 'Build' }, { label: 'Launch' }] } },
      { type: 'price-card', label: 'Pricing Card', icon: 'fa-solid fa-tag', w: 240, h: 340 },
      { type: 'social-card', label: 'Social Card', icon: 'fa-solid fa-id-card', w: 220, h: 180 },
      { type: 'kanban', label: 'Kanban Card', icon: 'fa-solid fa-columns', w: 200, h: 130 },
      { type: 'testimonial', label: 'Testimonial', icon: 'fa-solid fa-comment-quote', w: 320, h: 200 },
      { type: 'rating', label: 'Star Rating', icon: 'fa-solid fa-star', w: 160, h: 40, defaults: { value: 4 } },
    ],
  },
  {
    id: 'document', label: 'Document', icon: 'fa-solid fa-file-lines', color: '#64748b',
    items: [
      { type: 'toc', label: 'Table of Contents', icon: 'fa-solid fa-list-ol', w: 400, h: 260 },
      { type: 'pagenum', label: 'Page Number', icon: 'fa-solid fa-hashtag', w: 60, h: 34 },
      { type: 'date-el', label: 'Current Date', icon: 'fa-regular fa-calendar', w: 180, h: 34 },
      { type: 'signature', label: 'Signature Line', icon: 'fa-solid fa-signature', w: 260, h: 100 },
      { type: 'watermark', label: 'Watermark Text', icon: 'fa-solid fa-droplet', w: 300, h: 120, defaultContent: 'DRAFT' },
    ],
  },
]

const filteredCatalog = computed(() => {
  if (!elSearch.value.trim()) return CATALOG
  const q = elSearch.value.toLowerCase()
  return CATALOG.map(g => ({
    ...g,
    items: g.items.filter(el => el.label.toLowerCase().includes(q) || el.type.includes(q)),
  })).filter(g => g.items.length)
})

function toggleGroup(id) {
  const i = collapsedGroups.value.indexOf(id)
  i >= 0 ? collapsedGroups.value.splice(i, 1) : collapsedGroups.value.push(id)
}

function onElDragStart(e, elDef) {
  e.dataTransfer.setData('el-def', JSON.stringify(elDef))
  e.dataTransfer.effectAllowed = 'copy'
}

function quickInsert(elDef) {
  emit('add-element', { def: elDef, pageIndex: props.currentPage })
}

// ── Pages drag-and-drop ─────────────────────────────────────────────
const pagesListRef = ref(null)
let pageDragSrc = null
const pageDragOver = ref(null)

function onPageDragStart(e, pi) {
  pageDragSrc = pi
  e.dataTransfer.effectAllowed = 'move'
}

function onPageDragEnd() { pageDragOver.value = null }

function onPageDragOver(e, pi) {
  e.preventDefault()
  e.dataTransfer.dropEffect = 'move'
  pageDragOver.value = pi
}

function onPageDrop(e, pi) {
  e.preventDefault()
  pageDragOver.value = null
  if (pageDragSrc === null || pageDragSrc === pi) { pageDragSrc = null; return }
  const order = Array.from({ length: props.report.content.length }, (_, i) => i)
  order.splice(pi, 0, ...order.splice(pageDragSrc, 1))
  emit('reorder-pages', order)
  pageDragSrc = null
}

function getPageThumbStyle(page) {
  return {
    background: props.settings.background_color || '#fff',
    border: `1px solid ${props.settings.primary_color || '#6366f1'}22`,
  }
}

function getMiniElStyle(el) {
  const s = el.styles || {}
  const scale = 100 / 794  // A4 width scale factor for thumbnail
  return {
    position: 'absolute',
    left: ((el.position?.x || 0) * scale) + '%',
    top: ((el.position?.y || 0) * scale * 0.6) + '%',
    width: Math.min(90, (s.width || 100) * scale) + '%',
    height: '3px',
    background: s.backgroundColor !== 'transparent' && s.backgroundColor
      ? s.backgroundColor
      : (props.settings.primary_color || '#6366f1') + '50',
    borderRadius: '1px',
  }
}

// ── Layers ──────────────────────────────────────────────────────────
let layerDragSrc = null
const layerDragOver = ref(null)

const currentPageElements = computed(
  () => props.report.content[props.currentPage]?.elements || []
)

function isElSelected(idx) {
  return false // parent handles selection — we just emit
}

function locateElement(idx) {
  emit('select-layer-element', props.currentPage, idx)
}

function toggleVisibility(idx) {
  const el = currentPageElements.value[idx]
  if (el) { el.visible = el.visible === false ? true : false; emit('mark-dirty') }
}

function toggleLock(idx) {
  const el = currentPageElements.value[idx]
  if (el) { el.locked = !el.locked; emit('mark-dirty') }
}

function onLayerDragStart(e, idx) {
  layerDragSrc = idx
  e.dataTransfer.effectAllowed = 'move'
}

function onLayerDragOver(e, idx) {
  e.preventDefault()
  layerDragOver.value = idx
}

function onLayerDrop(e, toIdx) {
  e.preventDefault()
  layerDragOver.value = null
  if (layerDragSrc === null || layerDragSrc === toIdx) { layerDragSrc = null; return }
  const els = props.report.content[props.currentPage].elements
  const total = els.length
  // Reverse idx because we display reversed
  const fromReal = total - 1 - layerDragSrc
  const toReal = total - 1 - toIdx
  const [item] = els.splice(fromReal, 1)
  els.splice(toReal, 0, item)
  emit('mark-dirty')
  layerDragSrc = null
}

function getElIcon(type) {
  const map = {
    heading: 'fa-solid fa-heading', subheading: 'fa-solid fa-text-height', text: 'fa-solid fa-align-left',
    richtext: 'fa-solid fa-file-pen', quote: 'fa-solid fa-quote-left', blockquote: 'fa-solid fa-block-quote',
    image: 'fa-solid fa-image', video: 'fa-solid fa-video', table: 'fa-solid fa-table',
    'bar-chart': 'fa-solid fa-chart-column', 'line-chart': 'fa-solid fa-chart-line',
    'pie-chart': 'fa-solid fa-chart-pie', 'doughnut-chart': 'fa-solid fa-circle-half-stroke',
    'area-chart': 'fa-solid fa-chart-area', 'radar-chart': 'fa-solid fa-circle-dot',
    metric: 'fa-solid fa-arrow-trend-up', progress: 'fa-solid fa-bars-progress',
    'circular-progress': 'fa-solid fa-circle-notch', sparkline: 'fa-solid fa-wave-square',
    checklist: 'fa-solid fa-square-check', timeline: 'fa-solid fa-timeline',
    rectangle: 'fa-regular fa-square', circle: 'fa-regular fa-circle',
    divider: 'fa-solid fa-minus', arrow: 'fa-solid fa-arrow-right',
    signature: 'fa-solid fa-signature', toc: 'fa-solid fa-list-ol',
    pagenum: 'fa-solid fa-hashtag', watermark: 'fa-solid fa-droplet',
    callout: 'fa-solid fa-bullhorn', testimonial: 'fa-solid fa-comment-quote',
    'stat-row': 'fa-solid fa-table-columns', badge: 'fa-solid fa-certificate',
    code: 'fa-solid fa-code', list: 'fa-solid fa-list',
    'price-card': 'fa-solid fa-tag', 'social-card': 'fa-solid fa-id-card',
    rating: 'fa-solid fa-star', 'qr-code': 'fa-solid fa-qrcode',
    map: 'fa-solid fa-map-location-dot', icon: 'fa-solid fa-icons',
    avatar: 'fa-solid fa-user-circle', spacer: 'fa-solid fa-arrows-up-down',
    steps: 'fa-solid fa-stairs', kanban: 'fa-solid fa-columns',
  }
  return map[type] || 'fa-solid fa-cube'
}

function getElPreview(el) {
  if (el.content) return String(el.content).replace(/<[^>]+>/g, '').slice(0, 30)
  if (el.label) return el.label.slice(0, 30)
  return el.type
}

// ── Media ───────────────────────────────────────────────────────────
const uploadInputRef = ref(null)
const uploadDragOver = ref(false)
const imgQuery = ref('')
const imgResults = ref([])
const imgSearching = ref(false)
const imgSource = ref('')
const imgPage = ref(1)

function triggerUpload() { uploadInputRef.value?.click() }

function onUploadChange(e) {
  const files = Array.from(e.target.files || [])
  files.forEach(f => emit('image-upload', { file: f, pageIndex: props.currentPage }))
  e.target.value = ''
}

function onUploadDrop(e) {
  uploadDragOver.value = false
  const files = Array.from(e.dataTransfer.files || []).filter(f => f.type.startsWith('image/'))
  files.forEach(f => emit('image-upload', { file: f, pageIndex: props.currentPage }))
}

async function searchImages() {
  if (!imgQuery.value.trim()) return
  imgSearching.value = true
  imgResults.value = []
  try {
    const res = await window.axios.get(route('media.search'), {
      params: { q: imgQuery.value, per_page: 20, page: imgPage.value },
    })
    imgResults.value = res.data.images || []
    imgSource.value = res.data.source || ''
  } catch {
    // Fallback: use Lorem Picsum with keyword seed
    const seed = Math.abs([...imgQuery.value].reduce((a, c) => a + c.charCodeAt(0), 0))
    imgResults.value = Array.from({ length: 20 }, (_, i) => {
      const id = ((seed + i + (imgPage.value - 1) * 20) % 1000) + 1
      return { id, url: `https://picsum.photos/id/${id}/800/600`, thumb: `https://picsum.photos/id/${id}/200/150`, alt: imgQuery.value, source: 'picsum' }
    })
    imgSource.value = 'Lorem Picsum (offline fallback)'
  } finally {
    imgSearching.value = false
  }
}

function insertImage(img) {
  emit('image-search', { url: img.url, alt: img.alt, pi: props.currentPage })
}

// ── Themes ──────────────────────────────────────────────────────────
const THEMES = [
  { id: 'indigo', name: 'Indigo Pro', primary: '#6366f1', accent: '#818cf8', bg: '#ffffff', text: '#0f172a', fontFamily: 'DM Sans' },
  { id: 'obsidian', name: 'Obsidian', primary: '#c9a84c', accent: '#e0c070', bg: '#0d1117', text: '#e6edf3', fontFamily: 'Plus Jakarta Sans' },
  { id: 'emerald', name: 'Emerald', primary: '#10b981', accent: '#34d399', bg: '#ffffff', text: '#064e3b', fontFamily: 'Inter' },
  { id: 'rose', name: 'Rose', primary: '#f43f5e', accent: '#fb7185', bg: '#fff1f2', text: '#0f172a', fontFamily: 'Poppins' },
  { id: 'ocean', name: 'Ocean Blue', primary: '#0ea5e9', accent: '#38bdf8', bg: '#f0f9ff', text: '#0c4a6e', fontFamily: 'Figtree' },
  { id: 'slate', name: 'Corporate', primary: '#334155', accent: '#64748b', bg: '#f8fafc', text: '#0f172a', fontFamily: 'Space Grotesk' },
  { id: 'amber', name: 'Warm Amber', primary: '#d97706', accent: '#f59e0b', bg: '#fffbeb', text: '#451a03', fontFamily: 'Nunito' },
  { id: 'violet', name: 'Violet', primary: '#7c3aed', accent: '#a78bfa', bg: '#fdf4ff', text: '#2e1065', fontFamily: 'Outfit' },
  { id: 'teal', name: 'Teal Minimal', primary: '#0d9488', accent: '#14b8a6', bg: '#f0fdfa', text: '#134e4a', fontFamily: 'Sora' },
  { id: 'charcoal', name: 'Charcoal', primary: '#6366f1', accent: '#c9a84c', bg: '#1a1a2e', text: '#e2e8f0', fontFamily: 'Merriweather' },
]

function isThemeActive(theme) {
  return props.settings.primary_color === theme.primary &&
    props.settings.background_color === theme.bg
}

function applyTheme(theme) {
  emitSettings({
    primary_color: theme.primary,
    accent_color: theme.accent,
    background_color: theme.bg,
    text_color: theme.text,
    font_family: theme.fontFamily,
  })
}

// ── Settings ─────────────────────────────────────────────────────────
const FONTS = [
  'DM Sans', 'Inter', 'Plus Jakarta Sans', 'Space Grotesk', 'Sora', 'Nunito',
  'Outfit', 'Poppins', 'Figtree', 'Georgia', 'Playfair Display', 'Merriweather',
  'Lora', 'Fira Code', 'Courier New', 'Times New Roman',
]

// Local reactive copy so inputs feel instant (no round-trip lag)
const localSettings = reactive({ ...props.settings })

// Keep in sync when parent pushes updates
watch(() => props.settings, (v) => Object.assign(localSettings, v), { deep: true })

function setSetting(key, value) {
  localSettings[key] = value
  emitSettings()
}

function emitSettings(patch) {
  if (patch) Object.assign(localSettings, patch)
  emit('update-settings', { ...localSettings })
}

function resetSettings() {
  const defaults = {
    page_size: 'A4', orientation: 'portrait', margin: 40, padding: 20,
    background_color: '#ffffff', text_color: '#1e293b',
    primary_color: '#6366f1', accent_color: '#c9a84c',
    font_family: 'DM Sans', font_size: 14, line_height: 1.5,
    show_header: false, show_footer: true,
    header_text: '', header_height: 50,
    header_color: '#1e293b', header_text_color: '#ffffff',
    footer_left: '', footer_right: 'Page {n} of {total}', footer_color: '#94a3b8',
    show_page_numbers: true, page_number_style: 'decimal',
    page_number_position: 'footer-center', page_number_start: 1,
    watermark: '', watermark_opacity: 8, watermark_rotate: -30,
    watermark_color: '#94a3b8', watermark_size: 72, rtl: false,
    page_radius: 0, page_shadow: true,
  }
  Object.assign(localSettings, defaults)
  emitSettings()
}

// ── History helpers ──────────────────────────────────────────────────
function getSnapSummary(snap) {
  try {
    const s = JSON.parse(snap)
    const totalEls = s.content.reduce((a, p) => a + (p.elements?.length || 0), 0)
    return `${s.content.length} page${s.content.length !== 1 ? 's' : ''} · ${totalEls} element${totalEls !== 1 ? 's' : ''}`
  } catch { return '' }
}
</script>

<style scoped>
/* ═══ ROOT ═══════════════════════════════════════════════════════════ */
.ls-root {
  --ls-bg: #ffffff;
  --ls-bg2: #f8fafc;
  --ls-bg3: #f1f5f9;
  --ls-border: #e2e8f0;
  --ls-text: #0f172a;
  --ls-text2: #475569;
  --ls-text3: #94a3b8;
  --ls-accent: #6366f1;
  --ls-accent-l: rgba(99, 102, 241, .08);

  display: flex;
  width: 300px;
  min-width: 260px;
  max-width: 320px;
  height: 100%;
  background: var(--ls-bg);
  border-right: 1px solid var(--ls-border);
  flex-shrink: 0;
  overflow: hidden;
}

.ls-root.ls-dark {
  --ls-bg: #111827;
  --ls-bg2: #1a2236;
  --ls-bg3: #0f172a;
  --ls-border: #1e2d45;
  --ls-text: #e2e8f0;
  --ls-text2: #94a3b8;
  --ls-text3: #475569;
}

/* ═══ RAIL ═══════════════════════════════════════════════════════════ */
.ls-rail {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  width: 56px;
  min-width: 56px;
  padding: 10px 6px;
  background: var(--ls-bg2);
  border-right: 1px solid var(--ls-border);
  overflow-y: auto;
  scrollbar-width: none;
}

.ls-rail::-webkit-scrollbar {
  display: none;
}

.ls-rail-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  width: 44px;
  padding: 8px 4px;
  border: none;
  background: transparent;
  border-radius: 10px;
  cursor: pointer;
  color: var(--ls-text3);
  font-size: 14px;
  transition: all .14s;
  font-family: inherit;
  flex-shrink: 0;
}

.ls-rail-btn:hover {
  background: var(--ls-bg3);
  color: var(--ls-text);
}

.ls-rail-btn.active {
  background: var(--ls-accent-l);
  color: var(--ls-accent);
}

.ls-rail-label {
  font-size: 8.5px;
  font-weight: 600;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

/* ═══ CONTENT ════════════════════════════════════════════════════════ */
.ls-content {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.ls-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ═══ PANEL HEADER ═══════════════════════════════════════════════════ */
.ls-panel-head {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 14px 8px;
  flex-shrink: 0;
  border-bottom: 1px solid var(--ls-border);
}

.ls-panel-title {
  font-size: 12px;
  font-weight: 800;
  color: var(--ls-text);
  text-transform: uppercase;
  letter-spacing: .06em;
  flex: 1;
}

.ls-head-btn {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  border: 1px solid var(--ls-border);
  background: var(--ls-bg);
  cursor: pointer;
  color: var(--ls-text2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  transition: all .14s;
  flex-shrink: 0;
  font-family: inherit;
}

.ls-head-btn:hover {
  border-color: var(--ls-accent);
  color: var(--ls-accent);
}

.ls-head-btn--primary {
  background: var(--ls-accent);
  color: #fff;
  border-color: var(--ls-accent);
}

.ls-head-btn--primary:hover {
  background: #4f46e5;
}

.ls-el-count-badge {
  background: var(--ls-accent-l);
  color: var(--ls-accent);
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 99px;
}

/* ═══ SEARCH ═════════════════════════════════════════════════════════ */
.ls-search-wrap {
  position: relative;
  flex: 1;
}

.ls-search-input {
  width: 100%;
  padding: 7px 30px 7px 30px;
  border: 1px solid var(--ls-border);
  border-radius: 8px;
  background: var(--ls-bg2);
  color: var(--ls-text);
  font-size: 12px;
  outline: none;
  transition: border-color .14s;
  font-family: inherit;
}

.ls-search-input:focus {
  border-color: var(--ls-accent);
}

.ls-search-input::placeholder {
  color: var(--ls-text3);
}

.ls-search-icon {
  position: absolute;
  left: 9px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--ls-text3);
  font-size: 11px;
  pointer-events: none;
}

.ls-search-clear {
  position: absolute;
  right: 7px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  color: var(--ls-text3);
  cursor: pointer;
  font-size: 11px;
  padding: 2px;
  line-height: 1;
}

.ls-search-clear:hover {
  color: var(--ls-text);
}

.ls-search-btn {
  padding: 7px 10px;
  border: 1px solid var(--ls-accent);
  border-radius: 8px;
  background: var(--ls-accent);
  color: #fff;
  cursor: pointer;
  font-size: 11px;
  font-family: inherit;
  flex-shrink: 0;
}

.ls-search-btn:hover {
  background: #4f46e5;
}

.ls-search-btn:disabled {
  opacity: .6;
  cursor: not-allowed;
}

/* ═══ ELEMENT CATALOG ════════════════════════════════════════════════ */
.ls-el-catalog {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  scrollbar-width: thin;
  scrollbar-color: var(--ls-border) transparent;
}

.ls-el-group {
  margin-bottom: 6px;
}

.ls-el-group-head {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 10px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--ls-text2);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  border-radius: 8px;
  transition: background .1s;
  font-family: inherit;
}

.ls-el-group-head:hover {
  background: var(--ls-bg2);
}

.ls-el-group-head i:first-child {
  width: 14px;
}

.ls-group-arrow {
  margin-left: auto;
  font-size: 9px;
  color: var(--ls-text3);
}

.ls-el-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 6px;
  padding: 6px 4px 8px;
}

.ls-el-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  padding: 10px 6px;
  border: 1px solid var(--ls-border);
  border-radius: 10px;
  background: var(--ls-bg);
  cursor: grab;
  transition: all .15s;
  user-select: none;
  text-align: center;
}

.ls-el-card:hover {
  border-color: var(--ls-accent);
  background: var(--ls-accent-l);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, .15);
}

.ls-el-card:active {
  cursor: grabbing;
  transform: scale(.95);
}

.ls-el-card:focus-visible {
  outline: 2px solid var(--ls-accent);
  outline-offset: 2px;
}

.ls-el-icon {
  font-size: 18px;
  line-height: 1;
}

.ls-el-label {
  font-size: 9.5px;
  font-weight: 600;
  color: var(--ls-text2);
  line-height: 1.2;
}

/* ═══ PAGES ══════════════════════════════════════════════════════════ */
.ls-pages-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  scrollbar-width: thin;
  scrollbar-color: var(--ls-border) transparent;
}

.ls-page-item {
  border: 2px solid var(--ls-border);
  border-radius: 10px;
  background: var(--ls-bg);
  cursor: pointer;
  overflow: hidden;
  transition: all .15s;
  position: relative;
}

.ls-page-item:hover {
  border-color: var(--ls-accent);
}

.ls-page-active {
  border-color: var(--ls-accent) !important;
  box-shadow: 0 0 0 3px var(--ls-accent-l);
}

.ls-page-drag-over {
  border-color: #f59e0b !important;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, .2);
}

.ls-page-thumb {
  width: 100%;
  aspect-ratio: 794/1123;
  max-height: 140px;
  position: relative;
  overflow: hidden;
  border-radius: 6px 6px 0 0;
}

.ls-page-thumb-empty {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--ls-text3);
  font-size: 24px;
  opacity: .3;
}

.ls-page-drag-handle {
  position: absolute;
  top: 6px;
  left: 6px;
  width: 22px;
  height: 22px;
  background: rgba(0, 0, 0, .4);
  border-radius: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 9px;
  opacity: 0;
  transition: opacity .15s;
  cursor: grab;
}

.ls-page-item:hover .ls-page-drag-handle {
  opacity: 1;
}

.ls-page-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5px 10px;
}

.ls-page-num {
  font-size: 11px;
  font-weight: 700;
  color: var(--ls-text);
}

.ls-page-elcount {
  font-size: 10px;
  color: var(--ls-text3);
}

.ls-page-actions {
  display: flex;
  gap: 3px;
  padding: 5px 6px 6px;
  border-top: 1px solid var(--ls-border);
  flex-wrap: wrap;
  opacity: 0;
  transition: opacity .15s;
}

.ls-page-item:hover .ls-page-actions {
  opacity: 1;
}

.lpa-btn {
  flex: 1;
  min-width: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
  padding: 4px 6px;
  border: 1px solid var(--ls-border);
  background: var(--ls-bg);
  border-radius: 6px;
  cursor: pointer;
  color: var(--ls-text2);
  font-size: 10px;
  font-family: inherit;
  transition: all .12s;
  white-space: nowrap;
}

.lpa-btn:hover {
  border-color: var(--ls-accent);
  color: var(--ls-accent);
  background: var(--ls-accent-l);
}

.lpa-btn:disabled {
  opacity: .3;
  cursor: not-allowed;
  pointer-events: none;
}

.lpa-btn--danger:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: rgba(239, 68, 68, .06);
}

/* ═══ LAYERS ═════════════════════════════════════════════════════════ */
.ls-layers-list {
  flex: 1;
  overflow-y: auto;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  scrollbar-width: thin;
  scrollbar-color: var(--ls-border) transparent;
}

.ls-layer-item {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 7px 8px;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all .12s;
}

.ls-layer-item:hover {
  background: var(--ls-bg2);
  border-color: var(--ls-border);
}

.ls-layer-selected {
  background: var(--ls-accent-l) !important;
  border-color: var(--ls-accent) !important;
}

.ls-layer-hidden {
  opacity: .4;
}

.ls-layer-locked {
  background: rgba(245, 158, 11, .06);
  border-color: rgba(245, 158, 11, .2) !important;
}

.ls-layer-drag-over {
  border-color: #f59e0b !important;
  background: rgba(245, 158, 11, .1) !important;
}

.ls-layer-drag {
  color: var(--ls-text3);
  font-size: 10px;
  cursor: grab;
  flex-shrink: 0;
  opacity: 0;
  transition: opacity .15s;
}

.ls-layer-item:hover .ls-layer-drag {
  opacity: 1;
}

.ls-layer-icon {
  width: 26px;
  height: 26px;
  border-radius: 7px;
  background: var(--ls-bg2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  color: var(--ls-accent);
  flex-shrink: 0;
}

.ls-layer-name {
  flex: 1;
  min-width: 0;
}

.ls-layer-type {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: var(--ls-text);
  text-transform: capitalize;
}

.ls-layer-preview {
  display: block;
  font-size: 10px;
  color: var(--ls-text3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 110px;
}

.ls-layer-controls {
  display: flex;
  gap: 3px;
  flex-shrink: 0;
}

.ls-layer-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  border-radius: 5px;
  cursor: pointer;
  color: var(--ls-text3);
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .12s;
}

.ls-layer-btn:hover {
  background: var(--ls-bg3);
  color: var(--ls-text);
}

/* ═══ MEDIA ══════════════════════════════════════════════════════════ */
.ls-media-upload-zone {
  margin: 8px;
  padding: 20px 12px;
  border: 2px dashed var(--ls-border);
  border-radius: 10px;
  text-align: center;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  color: var(--ls-text3);
  font-size: 12px;
  transition: all .2s;
  flex-shrink: 0;
}

.ls-media-upload-zone i {
  font-size: 26px;
  color: var(--ls-accent);
  opacity: .7;
}

.ls-media-upload-zone:hover,
.ls-media-upload-zone.is-over {
  border-color: var(--ls-accent);
  background: var(--ls-accent-l);
  color: var(--ls-accent);
}

.ls-media-upload-zone small {
  font-size: 10px;
}

.ls-media-search-bar {
  display: flex;
  gap: 6px;
  padding: 0 8px 8px;
  flex-shrink: 0;
}

.ls-media-source-badge {
  margin: 0 8px 6px;
  font-size: 10px;
  color: var(--ls-text3);
  flex-shrink: 0;
}

.ls-media-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 6px;
  padding: 0 8px;
  overflow-y: auto;
  flex: 1;
  scrollbar-width: thin;
  scrollbar-color: var(--ls-border) transparent;
}

.ls-media-card {
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  position: relative;
  aspect-ratio: 4/3;
  background: var(--ls-bg2);
  border: 1px solid var(--ls-border);
  transition: all .15s;
}

.ls-media-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.ls-media-card:hover {
  border-color: var(--ls-accent);
  transform: scale(1.03);
}

.ls-media-card-overlay {
  position: absolute;
  inset: 0;
  background: rgba(99, 102, 241, .7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  gap: 4px;
  opacity: 0;
  transition: opacity .15s;
}

.ls-media-card:hover .ls-media-card-overlay {
  opacity: 1;
}

.ls-media-card-overlay span {
  font-size: 11px;
  font-weight: 600;
}

.ls-media-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px;
  flex-shrink: 0;
  font-size: 11px;
  color: var(--ls-text2);
}

.ls-pg-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 5px 10px;
  border: 1px solid var(--ls-border);
  border-radius: 7px;
  background: var(--ls-bg);
  color: var(--ls-text);
  cursor: pointer;
  font-size: 11px;
  font-family: inherit;
  transition: all .14s;
}

.ls-pg-btn:hover {
  border-color: var(--ls-accent);
  color: var(--ls-accent);
}

.ls-pg-btn:disabled {
  opacity: .4;
  cursor: not-allowed;
}

/* ═══ THEMES ═════════════════════════════════════════════════════════ */
.ls-themes-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  padding: 8px;
  overflow-y: auto;
  flex: 1;
  scrollbar-width: thin;
  scrollbar-color: var(--ls-border) transparent;
}

.ls-theme-card {
  border: 2px solid var(--ls-border);
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  background: var(--ls-bg);
  padding: 0 0 8px;
  transition: all .15s;
  text-align: left;
  font-family: inherit;
  position: relative;
}

.ls-theme-card:hover {
  border-color: var(--ls-accent);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, .1);
}

.ls-theme-card.active {
  border-color: var(--ls-accent);
  box-shadow: 0 0 0 3px var(--ls-accent-l);
}

.ls-theme-swatch-row {
  display: flex;
  gap: 0;
  height: 8px;
}

.ls-theme-swatch {
  flex: 1;
}

.ls-theme-preview {
  padding: 8px;
  margin: 6px 6px 4px;
  border-radius: 6px;
  min-height: 60px;
}

.ls-tp-heading {
  font-size: 11px;
  font-weight: 700;
  margin-bottom: 3px;
}

.ls-tp-body {
  font-size: 9px;
  opacity: .7;
  margin-bottom: 5px;
}

.ls-tp-accent {
  height: 3px;
  border-radius: 2px;
  width: 50%;
}

.ls-theme-name {
  font-size: 10px;
  font-weight: 700;
  color: var(--ls-text2);
  padding: 0 8px;
}

.ls-theme-check {
  position: absolute;
  top: 10px;
  right: 8px;
  color: var(--ls-accent);
  font-size: 14px;
}

/* ═══ SETTINGS ═══════════════════════════════════════════════════════ */
.ls-settings-body {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  scrollbar-width: thin;
  scrollbar-color: var(--ls-border) transparent;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.ls-settings-section {
  border: 1px solid var(--ls-border);
  border-radius: 10px;
  overflow: hidden;
  background: var(--ls-bg);
}

.ls-settings-section-title {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 9px 12px;
  background: var(--ls-bg2);
  font-size: 10.5px;
  font-weight: 800;
  color: var(--ls-text2);
  text-transform: uppercase;
  letter-spacing: .06em;
  border-bottom: 1px solid var(--ls-border);
}

.ls-field-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-bottom: 1px solid var(--ls-border);
}

.ls-field-row:last-child {
  border-bottom: none;
}

.ls-label {
  font-size: 11px;
  font-weight: 500;
  color: var(--ls-text2);
  flex: 1;
  white-space: nowrap;
  min-width: 80px;
}

.ls-val {
  font-size: 10px;
  color: var(--ls-accent);
  font-weight: 600;
  margin-left: 4px;
}

.ls-input {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--ls-border);
  border-radius: 6px;
  background: var(--ls-bg2);
  color: var(--ls-text);
  font-size: 11px;
  outline: none;
  transition: border-color .14s;
  font-family: inherit;
}

.ls-input:focus {
  border-color: var(--ls-accent);
}

.ls-input--sm {
  width: 70px;
  flex: none;
}

.ls-select {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--ls-border);
  border-radius: 6px;
  background: var(--ls-bg2);
  color: var(--ls-text);
  font-size: 11px;
  outline: none;
  cursor: pointer;
  font-family: inherit;
  transition: border-color .14s;
}

.ls-select:focus {
  border-color: var(--ls-accent);
}

.ls-color {
  width: 32px;
  height: 28px;
  border: 1px solid var(--ls-border);
  border-radius: 6px;
  cursor: pointer;
  padding: 2px;
  background: transparent;
}

.ls-color-with-input {
  display: flex;
  align-items: center;
  gap: 5px;
  flex: 1;
}

.ls-text-sm {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--ls-border);
  border-radius: 6px;
  background: var(--ls-bg2);
  color: var(--ls-text);
  font-size: 11px;
  outline: none;
  font-family: monospace;
}

.ls-text-sm:focus {
  border-color: var(--ls-accent);
}

.ls-range {
  flex: 1;
  accent-color: var(--ls-accent);
}

.ls-btn-group {
  display: flex;
  gap: 4px;
  flex: 1;
}

.ls-btn-group button {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--ls-border);
  border-radius: 6px;
  background: var(--ls-bg2);
  color: var(--ls-text2);
  cursor: pointer;
  font-size: 10px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all .12s;
  font-family: inherit;
}

.ls-btn-group button:hover {
  border-color: var(--ls-accent);
  color: var(--ls-accent);
}

.ls-btn-group button.active {
  background: var(--ls-accent);
  color: #fff;
  border-color: var(--ls-accent);
}

/* Toggle */
.ls-toggle {
  display: flex;
  align-items: center;
  gap: 7px;
  cursor: pointer;
}

.ls-toggle input {
  display: none;
}

.ls-toggle-track {
  width: 36px;
  height: 20px;
  border-radius: 10px;
  background: var(--ls-border);
  position: relative;
  transition: background .2s;
  flex-shrink: 0;
}

.ls-toggle input:checked~.ls-toggle-track {
  background: var(--ls-accent);
}

.ls-toggle-track::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #fff;
  transition: transform .2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
}

.ls-toggle input:checked~.ls-toggle-track::after {
  transform: translateX(16px);
}

.ls-toggle-label {
  font-size: 10px;
  font-weight: 600;
  color: var(--ls-text3);
}

/* ═══ HISTORY ════════════════════════════════════════════════════════ */
.ls-history-list {
  flex: 1;
  overflow-y: auto;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  scrollbar-width: thin;
  scrollbar-color: var(--ls-border) transparent;
}

.ls-history-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 9px 10px;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all .12s;
}

.ls-history-item:hover {
  background: var(--ls-bg2);
  border-color: var(--ls-border);
}

.ls-history-current {
  background: var(--ls-accent-l) !important;
  border-color: var(--ls-accent) !important;
}

.ls-history-future {
  opacity: .4;
}

.ls-hist-icon {
  color: var(--ls-accent);
  font-size: 13px;
  flex-shrink: 0;
}

.ls-hist-info {
  flex: 1;
  min-width: 0;
}

.ls-hist-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: var(--ls-text);
}

.ls-hist-sub {
  display: block;
  font-size: 10px;
  color: var(--ls-text3);
}

.ls-hist-current-badge {
  font-size: 9px;
  font-weight: 700;
  color: #fff;
  background: var(--ls-accent);
  padding: 2px 8px;
  border-radius: 99px;
}

/* ═══ EMPTY STATE ════════════════════════════════════════════════════ */
.ls-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: var(--ls-text3);
  text-align: center;
  padding: 32px 20px;
  font-size: 12px;
}

.ls-empty i {
  font-size: 28px;
  opacity: .3;
}

/* ═══ UTILITY ════════════════════════════════════════════════════════ */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* ═══ RESPONSIVE ═════════════════════════════════════════════════════ */
@media (max-width: 768px) {
  .ls-root {
    width: 260px;
    min-width: 220px;
  }

  .ls-rail {
    width: 46px;
    min-width: 46px;
  }

  .ls-rail-label {
    display: none;
  }
}
</style>