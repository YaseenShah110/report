<template>
  <div ref="editorShell" class="editor-shell"
    :class="{ 'is-dark': isDark, 'is-fullscreen': isFullscreen, 'is-presenting': presentationMode }"
    @keydown="handleKeyboard" tabindex="0">
    <!-- TOP TOOLBAR -->
    <TopToolbar :report="report" :settings="settings" :is-dirty="isDirty" :is-saving="isSaving" :last-saved="lastSaved"
      :zoom="zoom" :can-undo="canUndo" :can-redo="canRedo" :selected-el="selectedEl" :selected-els="selectedEls"
      :show-grid="showGrid" :snap-to-grid="snapToGrid" :show-rulers="showRulers" :is-dark="isDark"
      :is-fullscreen="isFullscreen" :show-ai="showAI" :left-collapsed="leftCollapsed" :right-collapsed="rightCollapsed"
      :report-slug="report.slug" @update:title="report.title = $event; markDirty()" @save="saveNow" @undo="undo"
      @redo="redo" @zoom-in="zoomIn" @zoom-out="zoomOut" @zoom-reset="zoom = 100" @toggle-grid="showGrid = !showGrid"
      @toggle-snap="snapToGrid = !snapToGrid" @toggle-rulers="showRulers = !showRulers" @toggle-dark="toggleDark"
      @toggle-fullscreen="toggleFullscreen" @toggle-ai="showAI = !showAI" @preview="previewReport"
      @print-preview="printPreview" @export-pdf="exportFile('pdf')" @export-png="exportFile('image')"
      @export-excel="exportFile('excel')" @export-csv="exportFile('csv')" @share="shareReport"
      @change-status="cycleStatus" @apply-style="applyStyle" @toggle-fmt="toggleFmt" @delete-el="deleteSelected"
      @duplicate-el="duplicateSelected" @lock-el="lockElement" @bring-front="bringToFront" @send-back="sendToBack"
      @toggle-left-panel="leftCollapsed = !leftCollapsed" @toggle-right-panel="rightCollapsed = !rightCollapsed"
      @toggle-measure="measureMode = !measureMode" @toggle-find="showFindReplace = !showFindReplace"
      @toggle-command="showCommandPalette = !showCommandPalette" @presentation="startPresentation"
      @email-report="emailReport" @group-elements="groupSelected" @ungroup-elements="ungroupSelected"
      @align-elements="alignSelected" />

    <!-- EDITOR BODY -->
    <div class="editor-body">
      <!-- LEFT SIDEBAR -->
      <LeftSidebar :report="report" :settings="settings" :current-page="currentPage" :selected-el-idx="selectedElIdx"
        :selected-els="selectedEls" :active-tab="activeLeftTab" :is-collapsed="leftCollapsed" :is-dark="isDark"
        @add-element-center="addElementCenter" @select-page="goToPage" @add-page="addPage"
        @duplicate-page="duplicatePage" @delete-page="deletePage" @rename-page="renamePage" @move-page="movePage"
        @select-element="selectElementByIdx" @deselect-all="deselectAll" @toggle-visibility="toggleVis"
        @toggle-lock="toggleLock" @upload-image="handleUploadedImage" @apply-template="applyQuickTemplate"
        @update:settings="onSettingsUpdate" @update:active-tab="activeLeftTab = $event"
        @canvas-drag-start="onElDragStart" @update:is-collapsed="leftCollapsed = $event"
        @add-element-at="addElementAtCoords" />

      <!-- MAIN CANVAS -->
      <EditorCanvas :report="report" :settings="settings" :current-page="currentPage" :selected-el-idx="selectedElIdx"
        :selected-els="selectedEls" :editing-el-idx="editingElIdx" :zoom="zoom" :show-grid="showGrid"
        :snap-to-grid="snapToGrid" :show-rulers="showRulers" :grid-size="gridSize" :is-dragging-el="isDraggingEl"
        :rubber-band="rubberBand" :drop-target-page="dropTargetPage" :measure-mode="measureMode" :is-dark="isDark"
        :page-count="report.content.length" :style-painter-active="stylePainterActive"
        @select-element="selectElementByIdx" @deselect-all="deselectAll" @add-element="addElementAtPosition"
        @select-page="goToPage" @add-page="addPage" @start-editing="startEditing" @stop-editing="editingElIdx = null"
        @update-text-content="updateTextContent" @element-mouse-down="onElementMouseDown" @resize-start="startResize"
        @rotate-start="startRotate" @canvas-drop="onCanvasDrop" @canvas-drag-end="isDraggingEl = false"
        @rubber-band-start="startRubberBand" @rubber-band-move="handleRubberBandMove" @rubber-band-end="endRubberBand"
        @zoom-wheel="handleZoomWheel" @page-dblclick="onPageDblClick" @context-menu="showElContextMenu"
        @image-upload="triggerImageUpload" @image-replace="triggerImageReplace" @go-to-page="goToPage"
        @mark-dirty="markDirty" @zoom-reset="zoom = 100" @element-cross-page="moveElementToPage"
        @scroll-to-page="scrollToPage" @style-painter-apply="applyStylePainter" @duplicate-page="duplicatePage"
        @delete-page="deletePage" />

      <!-- RIGHT SIDEBAR -->
      <RightSidebar :selected-el="selectedEl" :selected-els-count="selectedEls.length" :settings="settings"
        :active-section="activeRightSection" :current-page-elements="currentPageElements" :clipboard="clipboard"
        :style-painter-clipboard="stylePainterClipboard" :is-collapsed="rightCollapsed" :is-dark="isDark"
        :current-page="currentPage" :total-pages="report.content.length" @update:style="applyStyle"
        @update:content="updateElementContent" @delete-el="deleteSelected" @duplicate-el="duplicateSelected"
        @copy-el="copyElement" @paste-el="pasteElement" @lock-el="lockElement" @bring-front="bringToFront"
        @send-back="sendToBack" @align-to-page="alignToPage" @update:settings="onSettingsUpdate"
        @update:active-section="activeRightSection = $event" @add-table-row="addTableRow"
        @add-table-col="addTableColumn" @remove-table-row="removeTableRow" @remove-table-col="removeTableColumn"
        @set-chart-labels="setChartLabels" @set-chart-values="setChartValues" @add-timeline-item="addTimelineItem"
        @remove-timeline-item="removeTimelineItem" @add-checklist-item="addChecklistItem"
        @remove-checklist-item="removeChecklistItem" @add-stat-item="addStatItem" @remove-stat-item="removeStatItem"
        @reset-styles="resetElementStyles" @style-painter-copy="stylePainterCopy"
        @style-painter-paste="stylePainterPaste" @style-painter-activate="stylePainterActive = !stylePainterActive"
        @mark-dirty="markDirty" @image-replace="triggerImageReplace" @refresh-toc="refreshTOC"
        @update:is-collapsed="rightCollapsed = $event" @update-el-prop="updateElProp" @update-position="updatePosition"
        @move-element-to-page="moveElementToPage" />
    </div>

    <!-- STATUS BAR -->
    <StatusBar :current-page="currentPage" :total-pages="report.content.length"
      :elements-count="currentPageElements.length" :selected-el="selectedEl" :selected-count="selectedEls.length"
      :zoom="zoom" :is-dirty="isDirty" :is-saving="isSaving" :last-saved="lastSaved" :page-size="settings.page_size"
      :orientation="settings.orientation" :words-count="wordsCount" :cursor-pos="cursorPos" :is-dark="isDark"
      :grid-size="gridSize" :snap-to-grid="snapToGrid" :measure-mode="measureMode" @zoom-reset="zoom = 100"
      @zoom-to="zoom = $event" @toggle-snap="snapToGrid = !snapToGrid" @toggle-measure="measureMode = !measureMode"
      @update-grid-size="gridSize = $event" />

    <!-- OVERLAYS -->
    <AiPanel v-if="showAI" :visible="showAI" :report="report" :is-dark="isDark" :selected-element="selectedEl"
      @close="showAI = false" @insert-content="insertAiContent" @insert-chart="insertAiChart" />

    <CommandPalette v-if="showCommandPalette" :is-dark="isDark" @close="showCommandPalette = false"
      @execute="executeCommand" />

    <ShortcutOverlay v-if="showShortcuts" @close="showShortcuts = false" />
    <OnboardingTour v-if="showOnboarding" @complete="completeOnboarding" />

    <ContextMenu :show="contextMenu.show" :x="contextMenu.x" :y="contextMenu.y" :items="contextMenu.items"
      :is-dark="isDark" @close="contextMenu.show = false" />

    <ConfettiOverlay v-if="showConfetti" @complete="showConfetti = false" />
    <ToastContainer :toasts="toasts" @remove="removeToast" />

    <!-- FIND & REPLACE -->
    <Teleport to="body">
      <Transition name="slide-up">
        <div v-if="showFindReplace" class="fr-panel" :class="{ dark: isDark }">
          <div class="fr-header">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Find & Replace</span>
            <button @click="showFindReplace = false"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <div class="fr-body">
            <div class="fr-row">
              <input v-model="findText" class="fr-input" placeholder="Find..." @input="findInReport"
                @keydown.enter="replaceOne" />
            </div>
            <div class="fr-row">
              <input v-model="replaceText" class="fr-input" placeholder="Replace with..." @keydown.enter="replaceAll" />
            </div>
            <div v-if="findMatches.length" class="fr-count">{{ findCurrentIdx + 1 }} / {{ findMatches.length }} matches
            </div>
            <div class="fr-actions">
              <button @click="navigateMatch(-1)" class="fr-btn secondary" :disabled="!findMatches.length"><i
                  class="fa-solid fa-chevron-up"></i></button>
              <button @click="navigateMatch(1)" class="fr-btn secondary" :disabled="!findMatches.length"><i
                  class="fa-solid fa-chevron-down"></i></button>
              <button @click="replaceOne" class="fr-btn secondary" :disabled="!findMatches.length">Replace</button>
              <button @click="replaceAll" class="fr-btn">Replace All</button>
            </div>
            <div class="fr-results">
              <div v-for="(m, i) in findMatches.slice(0, 10)" :key="i" class="fr-match"
                :class="{ active: i === findCurrentIdx }" @click="goToMatch(m, i)">
                <span class="fr-match-page">P{{ m.pi + 1 }}</span>
                <span class="fr-match-preview">{{ m.preview }}</span>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- PRESENTATION MODE -->
    <Teleport to="body">
      <div v-if="presentationMode" class="pres-overlay" @click.self="nextSlide">
        <div class="pres-page" v-if="report.content[presentationPage]" :style="getPresPageStyle()">
          <div v-for="el in getSortedElements(report.content[presentationPage]?.elements || [])" :key="el.id"
            :style="getPresElStyle(el)" v-html="getPresElContent(el)"></div>
        </div>
        <div class="pres-controls" @click.stop>
          <button @click.stop="prevSlide" :disabled="presentationPage === 0"><i
              class="fa-solid fa-chevron-left"></i></button>
          <span>{{ presentationPage + 1 }} / {{ report.content.length }}</span>
          <button @click.stop="nextSlide" :disabled="presentationPage >= report.content.length - 1"><i
              class="fa-solid fa-chevron-right"></i></button>
          <span class="pres-sep">|</span>
          <button @click.stop="presentationMode = false"><i class="fa-solid fa-xmark"></i> Exit</button>
        </div>
      </div>
    </Teleport>

    <!-- HIDDEN FILE INPUT -->
    <input ref="fileInput" type="file" accept="image/*" class="hidden" multiple @change="handleFilePick" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import TopToolbar from './Components/TopToolbar.vue'
import LeftSidebar from './Components/LeftSidebar.vue'
import EditorCanvas from './Components/EditorCanvas.vue'
import RightSidebar from './Components/RightSidebar.vue'
import StatusBar from './Components/StatusBar.vue'
import AiPanel from './Components/AiPanel.vue'
import CommandPalette from './Components/CommandPalette.vue'
import ShortcutOverlay from './Components/ShortcutOverlay.vue'
import OnboardingTour from './Components/OnboardingTour.vue'
import ContextMenu from './Components/ContextMenu.vue'
import ConfettiOverlay from './Components/ConfettiOverlay.vue'
import ToastContainer from './Components/ToastContainer.vue'

// ─── Props ───────────────────────────────────────────────────────────────────
const props = defineProps({ report: { type: Object, required: true } })

// ─── Default Settings ─────────────────────────────────────────────────────────
function defaultSettings() {
  return {
    page_size: 'A4', orientation: 'portrait', margin: 40, page_radius: 0,
    background_color: '#ffffff', bg_image: '', primary_color: '#6366f1',
    accent_color: '#8b5cf6', text_color: '#0f172a', font_family: "'DM Sans', sans-serif",
    font_size: 14, show_header: false, header_text: '', header_color: '#1e293b',
    header_height: 50, show_footer: false, footer_left: '', footer_right: 'Page {n}',
    footer_center: '', show_page_numbers: true, watermark: '', watermark_opacity: 8,
    watermark_rotate: -30, rtl: false, custom_w: 794, custom_h: 1123,
    snap_threshold: 8, dark_mode_preview: false, page_shadow: true,
    ruler_color: '#94a3b8', grid_color: '#e2e8f0',
  }
}

// ─── Reactive State ────────────────────────────────────────────────────────────
const report = reactive(JSON.parse(JSON.stringify(props.report)))
const settings = reactive({ ...defaultSettings(), ...(props.report?.settings || {}) })

// Ensure content exists
if (!report.content?.length) {
  report.content = [{ id: uid(), label: 'Page 1', elements: [] }]
}

// UI State
const isDark = ref(localStorage.getItem('rg_theme') === 'dark')
const isFullscreen = ref(false)
const zoom = ref(100)
const gridSize = ref(10)
const showGrid = ref(true)
const snapToGrid = ref(true)
const showRulers = ref(false)
const showAI = ref(false)
const showCommandPalette = ref(false)
const showShortcuts = ref(false)
const showConfetti = ref(false)
const showFindReplace = ref(false)
const showOnboarding = ref(!localStorage.getItem('rg_onboarded'))
const isDirty = ref(false)
const isSaving = ref(false)
const lastSaved = ref('')
const leftCollapsed = ref(false)
const rightCollapsed = ref(false)
const measureMode = ref(false)
const presentationMode = ref(false)
const presentationPage = ref(0)
const stylePainterActive = ref(false)
const cursorPos = ref({ x: 0, y: 0 })

// Selection
const currentPage = ref(0)
const selectedElIdx = ref(null)
const selectedEls = ref([])
const editingElIdx = ref(null)
const isDraggingEl = ref(false)
const dropTargetPage = ref(null)
const activeLeftTab = ref('elements')
const activeRightSection = ref('props')

// Clipboard
const clipboard = ref(null)
const stylePainterClipboard = ref(null)
const currentImageTarget = ref(null)

// Find/Replace
const findText = ref('')
const replaceText = ref('')
const findMatches = ref([])
const findCurrentIdx = ref(0)

// Rubber Band
const rubberBand = reactive({ active: false, startX: 0, startY: 0, x: 0, y: 0, w: 0, h: 0 })

// Context Menu
const contextMenu = reactive({ show: false, x: 0, y: 0, items: [] })

// Toasts
const toasts = ref([])
let toastIdCounter = 0

// Undo/Redo
const undoStack = ref([])
const redoStack = ref([])

// Refs
const editorShell = ref(null)
const fileInput = ref(null)

// Timers
let saveTimer = null
let autoSaveInterval = null

// ─── Computed ──────────────────────────────────────────────────────────────────
const currentPageElements = computed(() => report.content[currentPage.value]?.elements || [])
const selectedEl = computed(() =>
  selectedElIdx.value !== null && currentPageElements.value[selectedElIdx.value]
    ? currentPageElements.value[selectedElIdx.value] : null
)
const canUndo = computed(() => undoStack.value.length > 0)
const canRedo = computed(() => redoStack.value.length > 0)
const wordsCount = computed(() => {
  let w = 0
  report.content.forEach(p => p.elements?.forEach(e => {
    if (e.content && typeof e.content === 'string')
      w += e.content.replace(/<[^>]*>/g, '').split(/\s+/).filter(Boolean).length
  }))
  return w
})

// ─── Helpers ───────────────────────────────────────────────────────────────────
function uid() {
  return crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random().toString(36).slice(2)}`
}
function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}
function getPageDims() {
  const sz = {
    A4: { portrait: { w: 794, h: 1123 }, landscape: { w: 1123, h: 794 } },
    Letter: { portrait: { w: 816, h: 1056 }, landscape: { w: 1056, h: 816 } },
    Legal: { portrait: { w: 816, h: 1344 }, landscape: { w: 1344, h: 816 } },
    A3: { portrait: { w: 1123, h: 1587 }, landscape: { w: 1587, h: 1123 } },
    A5: { portrait: { w: 559, h: 794 }, landscape: { w: 794, h: 559 } },
    custom: { portrait: { w: settings.custom_w || 794, h: settings.custom_h || 1123 }, landscape: { w: settings.custom_h || 1123, h: settings.custom_w || 794 } }
  }
  return sz[settings.page_size]?.[settings.orientation] || sz.A4.portrait
}
function snap(val) {
  return snapToGrid.value ? Math.round(val / gridSize.value) * gridSize.value : val
}
function getSortedElements(elements) {
  return [...(elements || [])].sort((a, b) => (a.styles?.zIndex || 1) - (b.styles?.zIndex || 1))
}

// ─── Toast ─────────────────────────────────────────────────────────────────────
function showToast(message, type = 'success', duration = 3000) {
  const id = ++toastIdCounter
  toasts.value.push({ id, message, type })
  setTimeout(() => removeToast(id), duration)
}
function removeToast(id) { toasts.value = toasts.value.filter(t => t.id !== id) }

// ─── Settings Update ───────────────────────────────────────────────────────────
function onSettingsUpdate(newSettings) {
  // Isolated: never let editor UI styles bleed into report settings
  Object.assign(settings, newSettings)
  markDirty()
}

// ─── Save ──────────────────────────────────────────────────────────────────────
function markDirty() {
  isDirty.value = true
  clearTimeout(saveTimer)
  saveTimer = setTimeout(autoSave, 2000)
}
async function autoSave() {
  if (isDirty.value && !isSaving.value) await saveNow()
}
async function saveNow() {
  if (isSaving.value) return
  isSaving.value = true
  try {
    const payload = {
      title: report.title,
      content: JSON.parse(JSON.stringify(report.content)),
      settings: JSON.parse(JSON.stringify(settings)),
    }
    const res = await fetch(route('reports.update', report.slug), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf(), Accept: 'application/json' },
      body: JSON.stringify(payload),
    })
    if (res.ok) {
      isDirty.value = false
      lastSaved.value = new Date().toLocaleTimeString()
      // Local draft backup
      try {
        localStorage.setItem(`rg_draft_${report.slug}`, JSON.stringify({ ...payload, savedAt: Date.now() }))
      } catch (_) { }
    }
  } catch (e) {
    showToast('Auto-save failed', 'error')
  }
  isSaving.value = false
}

// ─── Undo/Redo ─────────────────────────────────────────────────────────────────
function captureState() {
  return JSON.stringify({ content: report.content, settings: JSON.parse(JSON.stringify(settings)) })
}
function pushUndo() {
  undoStack.value.push(captureState())
  if (undoStack.value.length > 100) undoStack.value.shift()
  redoStack.value = []
}
function undo() {
  if (!undoStack.value.length) return
  redoStack.value.push(captureState())
  const s = JSON.parse(undoStack.value.pop())
  report.content = s.content
  Object.assign(settings, s.settings)
  deselectAll()
}
function redo() {
  if (!redoStack.value.length) return
  undoStack.value.push(captureState())
  const s = JSON.parse(redoStack.value.pop())
  report.content = s.content
  Object.assign(settings, s.settings)
  deselectAll()
}

// ─── Element Creation ──────────────────────────────────────────────────────────
function createElement(def, x, y) {
  const dims = getPageDims()
  const el = {
    id: uid(), type: def.type,
    content: def.content || getDefaultContent(def.type),
    src: def.src || undefined,
    position: { x: Math.max(0, snap(x)), y: Math.max(0, snap(y)) },
    styles: {
      width: def.w || 200, height: def.h || 80,
      fontSize: def.type === 'heading' ? 32 : def.type === 'subheading' ? 22 : 14,
      fontWeight: ['heading', 'subheading'].includes(def.type) ? '700' : '400',
      fontFamily: settings.font_family,
      color: settings.text_color,
      textAlign: 'left', lineHeight: 1.6, letterSpacing: 0,
      backgroundColor: ['rectangle', 'circle', 'triangle', 'star', 'hexagon'].includes(def.type)
        ? (settings.primary_color || '#6366f1') : 'transparent',
      opacity: 100, borderRadius: def.type === 'circle' ? 999 : 0,
      rotate: 0, zIndex: (currentPageElements.value.length || 0) + 1,
      borderWidth: 0, borderColor: '#000', borderStyle: 'solid',
      boxShadow: 'none', padding: 8, textTransform: 'none',
      textDecoration: 'none', fontStyle: 'normal', scaleX: 1, scaleY: 1,
      blur: 0, brightness: 100, contrast: 100, grayscale: 0, saturate: 100,
      mixBlendMode: 'normal', overflow: 'visible',
    },
    locked: false, visible: true, groupId: null,
  }
  applyTypeDefaults(el, def)
  return el
}

function getDefaultContent(type) {
  const map = {
    text: 'Click to edit text', heading: 'Heading', subheading: 'Subheading',
    quote: '"An inspiring quote goes here"', callout: 'Callout message',
    code: '// Your code here\nconsole.log("Hello World");',
    badge: 'Badge', link: 'https://example.com', richtext: '<p>Start typing...</p>',
    blockquote: 'Blockquote text here', highlight: 'Highlighted text',
    toc: 'Table of Contents', signature: 'Your Name', pagenum: '', date: '',
  }
  return map[type] || ''
}

function applyTypeDefaults(el, def) {
  const pc = settings.primary_color || '#6366f1'
  switch (el.type) {
    case 'metric':
      el.label = def.label || 'Revenue'; el.value = '$48K'; el.change = '+12%'; el.changeType = 'positive'; el.changePeriod = 'vs last month'
      el.styles.backgroundColor = '#f8fafc'; el.styles.borderRadius = 12; el.styles.borderWidth = 1; el.styles.borderColor = '#e2e8f0'
      break
    case 'table':
      el.columns = ['Column 1', 'Column 2', 'Column 3']
      el.data = [
        { 'Column 1': 'Row 1', 'Column 2': 'Data', 'Column 3': 'Value' },
        { 'Column 1': 'Row 2', 'Column 2': 'Data', 'Column 3': 'Value' },
        { 'Column 1': 'Row 3', 'Column 2': 'Data', 'Column 3': 'Value' },
      ]
      el.styles.backgroundColor = '#fff'
      break
    case 'bar-chart': case 'line-chart': case 'area-chart': case 'pie-chart':
    case 'doughnut-chart': case 'radar-chart': case 'scatter-chart': case 'bubble-chart':
    case 'polar-chart': case 'funnel-chart':
      el.chartTitle = def.label || 'Chart'
      el.chartData = { labels: ['Q1', 'Q2', 'Q3', 'Q4'], values: [25, 40, 35, 55] }
      el.chartColor = pc; el.chartDatasetLabel = 'Dataset'
      el.styles.backgroundColor = '#fff'; el.styles.borderRadius = 8
      el.styles.padding = 12
      break
    case 'progress':
      el.label = 'Progress'; el.value = 65
      el.styles.height = 60; el.styles.width = 350
      break
    case 'circular-progress':
      el.label = 'Score'; el.value = 78
      el.styles.width = 140; el.styles.height = 140
      break
    case 'checklist':
      el.items = [{ text: 'Task one', checked: true }, { text: 'Task two', checked: false }, { text: 'Task three', checked: false }]
      break
    case 'timeline':
      el.items = [{ date: 'Q1 2024', label: 'Project Start', desc: 'Initial planning phase' }, { date: 'Q2 2024', label: 'Development', desc: 'Core features built' }, { date: 'Q3 2024', label: 'Launch', desc: 'Public release' }]
      break
    case 'stat-row':
      el.stats = [{ value: '12.4K', label: 'Users' }, { value: '$48K', label: 'Revenue' }, { value: '94%', label: 'Satisfaction' }]
      el.styles.backgroundColor = '#f8fafc'; el.styles.borderRadius = 8; el.styles.padding = 16
      break
    case 'testimonial':
      el.author = 'Jane Smith'; el.role = 'CEO, Acme Corp'
      el.styles.backgroundColor = '#f8fafc'; el.styles.borderRadius = 12; el.styles.padding = 20
      break
    case 'callout':
      el.emoji = '💡'
      el.styles.backgroundColor = pc + '10'; el.styles.borderRadius = 8
      break
    case 'rating':
      el.value = 4; el.styles.height = 40
      break
    case 'qr-code':
      el.qrText = 'https://example.com'; el.qrSize = 150
      el.styles.backgroundColor = '#fff'; el.styles.borderRadius = 8
      break
    case 'video':
      el.videoUrl = ''
      el.styles.backgroundColor = '#000'; el.styles.borderRadius = 8
      break
    case 'map':
      el.mapAddress = ''
      el.styles.backgroundColor = '#e2e8f0'; el.styles.borderRadius = 8
      break
    case 'sparkline':
      el.sparkData = [3, 7, 4, 9, 6, 8, 5, 10, 7, 9]; el.styles.height = 48
      break
    case 'icon':
      el.content = '⭐'; el.styles.fontSize = 40
      break
    case 'divider':
      el.styles.height = 2; el.styles.width = 400
      el.styles.backgroundColor = '#e2e8f0'
      break
    case 'arrow':
      el.styles.height = 40; el.styles.width = 200
      el.arrowDirection = 'right'; el.arrowStyle = 'filled'
      break
    case 'image':
      el.src = ''; el.alt = 'Image'
      el.styles.borderRadius = 8; el.styles.objectFit = 'cover'
      break
    case 'signature':
      el.styles.height = 80
      break
    case 'toc':
      el.tocItems = []; el.styles.padding = 16
      break
    case 'list':
      el.items = ['First item', 'Second item', 'Third item']
      el.styles.listStyle = 'bullet'
      break
    case 'price-card':
      el.plan = 'Pro Plan'; el.price = '$49'; el.period = '/month'
      el.features = ['Unlimited reports', 'Custom branding', 'Priority support']
      el.styles.backgroundColor = '#fff'; el.styles.borderRadius = 16; el.styles.padding = 24
      break
    case 'kanban':
      el.status = 'In Progress'; el.priority = 'medium'; el.due = 'Dec 31, 2024'
      el.styles.backgroundColor = '#fff'; el.styles.borderRadius = 8; el.styles.padding = 12
      break
    case 'social-card':
      el.avatar = '👤'; el.subtitle = 'Product Manager'
      el.styles.backgroundColor = '#fff'; el.styles.borderRadius = 16; el.styles.padding = 20
      break
    case 'watermark':
      el.styles.opacity = 10; el.styles.rotate = -30; el.styles.fontSize = 48
      break
    case 'html-embed':
      el.htmlContent = '<div style="padding:16px;background:#f0f4ff;border-radius:8px;text-align:center;"><strong>HTML Embed</strong><p>Edit in properties</p></div>'
      break
  }
}

function addElement(def, x, y, pageIdx = null) {
  pushUndo()
  const pi = pageIdx !== null ? pageIdx : currentPage.value
  const el = createElement(def, x, y)
  if (!report.content[pi]) return
  report.content[pi].elements.push(el)
  if (pi === currentPage.value) {
    selectedElIdx.value = report.content[pi].elements.length - 1
    selectedEls.value = []
  }
  markDirty()
  return el
}

function addElementCenter(def) {
  const dims = getPageDims()
  const m = settings.margin || 40
  const x = (dims.w - (def.w || 200)) / 2
  const y = dims.h / 3
  addElement(def, x, y)
}

function addElementAtPosition({ def, x, y, pageIdx }) {
  addElement(def, x || 100, y || 100, pageIdx)
}

function addElementAtCoords({ def, x, y }) {
  addElement(def, x, y)
}

// ─── Element Operations ────────────────────────────────────────────────────────
function selectElementByIdx(idx) {
  selectedElIdx.value = idx
  selectedEls.value = []
  editingElIdx.value = null
}

function deselectAll() {
  selectedElIdx.value = null
  selectedEls.value = []
  editingElIdx.value = null
}

function startEditing({ pageIndex, elementIndex }) {
  const el = report.content[pageIndex]?.elements[elementIndex]
  if (!el || el.locked) return
  selectElementByIdx(elementIndex)
  editingElIdx.value = elementIndex
}

function deleteSelected() {
  if (selectedEls.value.length > 1) {
    pushUndo()
    const sorted = [...selectedEls.value].sort((a, b) => b - a)
    sorted.forEach(i => currentPageElements.value.splice(i, 1))
    deselectAll(); markDirty()
  } else if (selectedElIdx.value !== null) {
    pushUndo()
    currentPageElements.value.splice(selectedElIdx.value, 1)
    deselectAll(); markDirty()
  }
}

function duplicateSelected() {
  if (!selectedEl.value) return
  pushUndo()
  const copy = JSON.parse(JSON.stringify(selectedEl.value))
  copy.id = uid(); copy.position.x += 20; copy.position.y += 20
  copy.styles.zIndex = (copy.styles.zIndex || 1) + 1
  currentPageElements.value.push(copy)
  selectedElIdx.value = currentPageElements.value.length - 1
  markDirty()
}

function copyElement() {
  if (selectedEl.value) {
    clipboard.value = JSON.parse(JSON.stringify(selectedEl.value))
    showToast('Copied to clipboard', 'success')
  }
}

function pasteElement() {
  if (!clipboard.value) return
  pushUndo()
  const copy = JSON.parse(JSON.stringify(clipboard.value))
  copy.id = uid(); copy.position.x += 20; copy.position.y += 20
  currentPageElements.value.push(copy)
  selectedElIdx.value = currentPageElements.value.length - 1
  markDirty()
}

function lockElement() {
  if (selectedEl.value) { selectedEl.value.locked = !selectedEl.value.locked; markDirty() }
}

function bringToFront() {
  if (!selectedEl.value) return
  const maxZ = Math.max(...currentPageElements.value.map(e => e.styles?.zIndex || 1))
  selectedEl.value.styles.zIndex = maxZ + 1; markDirty()
}

function sendToBack() {
  if (!selectedEl.value) return
  const minZ = Math.min(...currentPageElements.value.map(e => e.styles?.zIndex || 1))
  selectedEl.value.styles.zIndex = Math.max(0, minZ - 1); markDirty()
}

function toggleVis(idx) {
  const el = currentPageElements.value[idx]
  if (el) { el.visible = el.visible === false ? true : false; markDirty() }
}

function toggleLock(idx) {
  const el = currentPageElements.value[idx]
  if (el) { el.locked = !el.locked; markDirty() }
}

function applyStyle(prop, val) {
  // Apply to all selected elements if multi-selected
  const targets = selectedEls.value.length > 1
    ? selectedEls.value.map(i => currentPageElements.value[i]).filter(Boolean)
    : selectedEl.value ? [selectedEl.value] : []
  targets.forEach(el => {
    if (!el.styles) el.styles = {}
    el.styles[prop] = val
  })
  markDirty()
}

function toggleFmt(prop, onVal, offVal) {
  if (!selectedEl.value?.styles) return
  applyStyle(prop, selectedEl.value.styles[prop] === onVal ? offVal : onVal)
}

function updateElementContent(content) {
  if (selectedEl.value) { selectedEl.value.content = content; markDirty() }
}

function updateTextContent({ pageIndex, elementIndex, content }) {
  const el = report.content[pageIndex]?.elements[elementIndex]
  if (el) { el.content = content; markDirty() }
}

function updateElProp({ prop, value }) {
  if (selectedEl.value) { selectedEl.value[prop] = value; markDirty() }
}

function updatePosition({ axis, value }) {
  if (selectedEl.value?.position) { selectedEl.value.position[axis] = snap(value); markDirty() }
}

function resetElementStyles() {
  if (!selectedEl.value) return
  pushUndo()
  const fresh = createElement({ type: selectedEl.value.type, w: selectedEl.value.styles.width, h: selectedEl.value.styles.height }, 0, 0)
  selectedEl.value.styles = fresh.styles
  markDirty()
}

// ─── Style Painter ──────────────────────────────────────────────────────────────
function stylePainterCopy() {
  if (selectedEl.value?.styles) {
    stylePainterClipboard.value = JSON.parse(JSON.stringify(selectedEl.value.styles))
    showToast('Style copied! Click elements to apply.', 'success')
    stylePainterActive.value = true
  }
}
function stylePainterPaste() {
  if (stylePainterClipboard.value && selectedEl.value) {
    pushUndo()
    const { width, height, zIndex } = selectedEl.value.styles
    selectedEl.value.styles = { ...stylePainterClipboard.value, width, height, zIndex }
    markDirty(); showToast('Style applied!', 'success')
  }
}
function applyStylePainter(idx) {
  if (!stylePainterClipboard.value || idx === null) return
  const el = currentPageElements.value[idx]
  if (!el) return
  pushUndo()
  const { width, height, zIndex } = el.styles
  el.styles = { ...stylePainterClipboard.value, width, height, zIndex }
  markDirty(); showToast('Style applied!', 'success')
}

// ─── Group/Ungroup ──────────────────────────────────────────────────────────────
function groupSelected() {
  if (selectedEls.value.length < 2) return
  pushUndo()
  const gid = uid()
  selectedEls.value.forEach(i => {
    const el = currentPageElements.value[i]
    if (el) el.groupId = gid
  })
  showToast(`Grouped ${selectedEls.value.length} elements`, 'success')
  markDirty()
}

function ungroupSelected() {
  if (!selectedEl.value?.groupId) return
  pushUndo()
  const gid = selectedEl.value.groupId
  currentPageElements.value.forEach(el => { if (el.groupId === gid) el.groupId = null })
  showToast('Ungrouped', 'success')
  markDirty()
}

// ─── Alignment ────────────────────────────────────────────────────────────────
function alignSelected(direction) {
  if (selectedEls.value.length < 2) {
    alignToPage(direction)
    return
  }
  pushUndo()
  const els = selectedEls.value.map(i => currentPageElements.value[i]).filter(Boolean)
  const minX = Math.min(...els.map(e => e.position.x))
  const maxX = Math.max(...els.map(e => e.position.x + e.styles.width))
  const minY = Math.min(...els.map(e => e.position.y))
  const maxY = Math.max(...els.map(e => e.position.y + e.styles.height))
  const centerX = (minX + maxX) / 2
  const centerY = (minY + maxY) / 2
  els.forEach(el => {
    if (direction === 'left') el.position.x = minX
    else if (direction === 'right') el.position.x = maxX - el.styles.width
    else if (direction === 'center-h') el.position.x = centerX - el.styles.width / 2
    else if (direction === 'top') el.position.y = minY
    else if (direction === 'bottom') el.position.y = maxY - el.styles.height
    else if (direction === 'center-v') el.position.y = centerY - el.styles.height / 2
    else if (direction === 'distribute-h') {
      // distribute horizontally
      const sorted = [...els].sort((a, b) => a.position.x - b.position.x)
      const gap = (maxX - minX - sorted.reduce((s, e) => s + e.styles.width, 0)) / (sorted.length - 1)
      let x = minX
      sorted.forEach(e => { e.position.x = x; x += e.styles.width + gap })
    }
    else if (direction === 'distribute-v') {
      const sorted = [...els].sort((a, b) => a.position.y - b.position.y)
      const gap = (maxY - minY - sorted.reduce((s, e) => s + e.styles.height, 0)) / (sorted.length - 1)
      let y = minY
      sorted.forEach(e => { e.position.y = y; y += e.styles.height + gap })
    }
  })
  markDirty()
}

function alignToPage(dir) {
  if (!selectedEl.value) return
  pushUndo()
  const dims = getPageDims()
  const el = selectedEl.value
  const m = settings.margin || 0
  const targets = selectedEls.value.length > 1
    ? selectedEls.value.map(i => currentPageElements.value[i]).filter(Boolean)
    : [el]
  targets.forEach(e => {
    if (dir === 'left') e.position.x = m
    else if (dir === 'right') e.position.x = dims.w - e.styles.width - m
    else if (dir === 'center-h') e.position.x = (dims.w - e.styles.width) / 2
    else if (dir === 'top') e.position.y = m
    else if (dir === 'bottom') e.position.y = dims.h - e.styles.height - m
    else if (dir === 'center-v') e.position.y = (dims.h - e.styles.height) / 2
  })
  markDirty()
}

// ─── Table/Chart/List helpers ──────────────────────────────────────────────────
function addTableRow() {
  if (selectedEl.value?.type !== 'table') return
  pushUndo()
  const row = {}; selectedEl.value.columns.forEach(c => row[c] = ''); selectedEl.value.data.push(row); markDirty()
}
function addTableColumn() {
  if (selectedEl.value?.type !== 'table') return
  pushUndo()
  const col = 'Col ' + (selectedEl.value.columns.length + 1)
  selectedEl.value.columns.push(col); selectedEl.value.data.forEach(r => r[col] = ''); markDirty()
}
function removeTableRow() {
  if (selectedEl.value?.type !== 'table' || selectedEl.value.data.length <= 1) return
  pushUndo(); selectedEl.value.data.pop(); markDirty()
}
function removeTableColumn() {
  if (selectedEl.value?.type !== 'table' || selectedEl.value.columns.length <= 1) return
  pushUndo(); const col = selectedEl.value.columns.pop(); selectedEl.value.data.forEach(r => delete r[col]); markDirty()
}
function setChartLabels(labels) {
  if (selectedEl.value?.chartData) { selectedEl.value.chartData.labels = labels; markDirty() }
}
function setChartValues(values) {
  if (selectedEl.value?.chartData) { selectedEl.value.chartData.values = values; markDirty() }
}
function addTimelineItem() {
  if (selectedEl.value?.type !== 'timeline') return
  if (!selectedEl.value.items) selectedEl.value.items = []
  selectedEl.value.items.push({ date: '', label: 'New Event', desc: '' }); markDirty()
}
function removeTimelineItem(idx) {
  selectedEl.value?.items?.splice(idx, 1); markDirty()
}
function addChecklistItem() {
  if (!selectedEl.value?.items) selectedEl.value.items = []
  selectedEl.value.items.push({ text: 'New item', checked: false }); markDirty()
}
function removeChecklistItem(idx) {
  selectedEl.value?.items?.splice(idx, 1); markDirty()
}
function addStatItem() {
  if (!selectedEl.value?.stats) selectedEl.value.stats = []
  selectedEl.value.stats.push({ value: '0', label: 'Metric' }); markDirty()
}
function removeStatItem(idx) {
  selectedEl.value?.stats?.splice(idx, 1); markDirty()
}

// ─── Pages ────────────────────────────────────────────────────────────────────
function goToPage(idx) {
  deselectAll()
  currentPage.value = Math.max(0, Math.min(idx, report.content.length - 1))
}

function scrollToPage(idx) { goToPage(idx) }

function addPage() {
  pushUndo()
  // New page inherits settings from current page (colors, fonts etc.)
  const newPage = {
    id: uid(),
    label: 'Page ' + (report.content.length + 1),
    elements: [],
    // Inherit background from settings
    background: settings.background_color,
  }
  report.content.push(newPage)
  goToPage(report.content.length - 1)
  markDirty()
  showToast('Page added', 'success')
}

function duplicatePage(idx) {
  pushUndo()
  const copy = JSON.parse(JSON.stringify(report.content[idx]))
  copy.id = uid(); copy.label = (copy.label || `Page ${idx + 1}`) + ' (Copy)'
  copy.elements = copy.elements.map(el => ({ ...el, id: uid() }))
  report.content.splice(idx + 1, 0, copy)
  goToPage(idx + 1); markDirty()
}

function deletePage(idx) {
  if (report.content.length <= 1) { showToast('Cannot delete the only page', 'error'); return }
  pushUndo()
  report.content.splice(idx, 1)
  if (currentPage.value >= report.content.length) currentPage.value = report.content.length - 1
  markDirty()
}

function renamePage(idx, label) {
  report.content[idx].label = label; markDirty()
}

function movePage(from, to) {
  pushUndo()
  const [page] = report.content.splice(from, 1)
  report.content.splice(to, 0, page)
  currentPage.value = to; markDirty()
}

// ─── Cross-page Element Move ──────────────────────────────────────────────────
function moveElementToPage({ elementIdx, fromPage, toPage, x, y }) {
  if (fromPage === toPage) return
  pushUndo()
  const el = report.content[fromPage]?.elements.splice(elementIdx, 1)[0]
  if (!el || !report.content[toPage]) return
  el.position = { x: x || el.position.x, y: y || el.position.y }
  report.content[toPage].elements.push(el)
  if (toPage === currentPage.value) {
    selectedElIdx.value = report.content[toPage].elements.length - 1
  } else {
    deselectAll()
  }
  markDirty(); showToast(`Moved to page ${toPage + 1}`, 'success')
}

// ─── Drag / Resize / Rotate ────────────────────────────────────────────────────
function onElDragStart(e, def) {
  e.dataTransfer.setData('el-def', JSON.stringify(def)); isDraggingEl.value = true
}

function onCanvasDrop({ def, x, y, pageIdx }) {
  addElement(def, x, y, pageIdx); isDraggingEl.value = false; dropTargetPage.value = null
}

function onElementMouseDown({ event, pageIndex, elementIndex }) {
  if (event.button !== 0) return
  const el = report.content[pageIndex]?.elements[elementIndex]
  if (!el || el.locked) return

  if (pageIndex !== currentPage.value) goToPage(pageIndex)

  // Multi-select with shift
  if (event.shiftKey) {
    const i = selectedEls.value.indexOf(elementIndex)
    if (i >= 0) selectedEls.value.splice(i, 1)
    else selectedEls.value = [...selectedEls.value, elementIndex]
    if (selectedElIdx.value === null) selectedElIdx.value = elementIndex
  } else {
    if (!selectedEls.value.includes(elementIndex)) {
      selectElementByIdx(elementIndex)
    }
  }

  const scale = zoom.value / 100
  const startX = event.clientX, startY = event.clientY
  const movingEls = (selectedEls.value.length > 1 ? selectedEls.value : [elementIndex])
    .map(i => ({
      idx: i,
      ox: report.content[pageIndex].elements[i]?.position.x || 0,
      oy: report.content[pageIndex].elements[i]?.position.y || 0,
    }))

  let moved = false, undoPushed = false

  const onMove = (ev) => {
    const dx = (ev.clientX - startX) / scale, dy = (ev.clientY - startY) / scale
    if (!moved && Math.abs(dx) + Math.abs(dy) > 3) { moved = true }
    if (moved) {
      if (!undoPushed) { pushUndo(); undoPushed = true }
      movingEls.forEach(({ idx: i, ox, oy }) => {
        const targetEl = report.content[pageIndex]?.elements[i]
        if (!targetEl) return
        targetEl.position.x = Math.max(0, snap(ox + dx))
        targetEl.position.y = Math.max(0, snap(oy + dy))
      })
    }
    cursorPos.value = { x: Math.round(el.position.x), y: Math.round(el.position.y) }
  }

  const onUp = () => {
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)
    if (moved) markDirty()
  }

  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}

function startResize({ event, pageIndex, elementIndex, handle }) {
  event.stopPropagation(); event.preventDefault()
  const el = report.content[pageIndex]?.elements[elementIndex]
  if (!el) return
  pushUndo()
  const scale = zoom.value / 100
  const startX = event.clientX, startY = event.clientY
  const ow = el.styles.width, oh = el.styles.height
  const ox = el.position.x, oy = el.position.y
  const MIN = 20

  const onMove = (ev) => {
    const dx = (ev.clientX - startX) / scale, dy = (ev.clientY - startY) / scale
    let nw = ow, nh = oh, nx = ox, ny = oy

    if (handle.includes('e')) nw = Math.max(MIN, ow + dx)
    if (handle.includes('s')) nh = Math.max(MIN, oh + dy)
    if (handle.includes('w')) { nw = Math.max(MIN, ow - dx); nx = ox + (ow - nw) }
    if (handle.includes('n')) { nh = Math.max(MIN, oh - dy); ny = oy + (oh - nh) }

    if (el.styles.lockAspect) {
      const ratio = ow / oh
      if (handle.includes('e') || handle.includes('w')) nh = nw / ratio
      else nw = nh * ratio
    }

    el.styles.width = Math.round(nw); el.styles.height = Math.round(nh)
    el.position.x = Math.round(nx); el.position.y = Math.round(ny)
  }

  const onUp = () => {
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)
    markDirty()
  }
  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}

function startRotate({ event, pageIndex, elementIndex }) {
  event.stopPropagation(); event.preventDefault()
  const el = report.content[pageIndex]?.elements[elementIndex]
  if (!el) return
  pushUndo()

  const pageEl = document.querySelector(`[data-page-index="${pageIndex}"]`)
  if (!pageEl) return
  const scale = zoom.value / 100
  const rect = pageEl.getBoundingClientRect()
  const cx = rect.left + (el.position.x + el.styles.width / 2) * scale
  const cy = rect.top + (el.position.y + el.styles.height / 2) * scale

  const onMove = (ev) => {
    const angle = Math.atan2(ev.clientY - cy, ev.clientX - cx) * 180 / Math.PI + 90
    el.styles.rotate = Math.round(angle)
  }
  const onUp = () => {
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)
    markDirty()
  }
  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}

// ─── Rubber Band ───────────────────────────────────────────────────────────────
function startRubberBand(e) {
  rubberBand.active = true; rubberBand.startX = e.clientX; rubberBand.startY = e.clientY
  rubberBand.x = e.clientX; rubberBand.y = e.clientY; rubberBand.w = 0; rubberBand.h = 0
}
function handleRubberBandMove(e) {
  if (!rubberBand.active) return
  rubberBand.x = Math.min(e.clientX, rubberBand.startX)
  rubberBand.y = Math.min(e.clientY, rubberBand.startY)
  rubberBand.w = Math.abs(e.clientX - rubberBand.startX)
  rubberBand.h = Math.abs(e.clientY - rubberBand.startY)
}
function endRubberBand(e) {
  if (!rubberBand.active) return
  rubberBand.active = false
  if (rubberBand.w < 5 && rubberBand.h < 5) return
  const pageEl = document.querySelector(`[data-page-index="${currentPage.value}"]`)
  if (!pageEl) return
  const rect = pageEl.getBoundingClientRect()
  const scale = zoom.value / 100
  const bx = (rubberBand.x - rect.left) / scale
  const by = (rubberBand.y - rect.top) / scale
  const bw = rubberBand.w / scale, bh = rubberBand.h / scale
  const sel = []
  currentPageElements.value.forEach((el, i) => {
    if (el.position.x < bx + bw && el.position.x + el.styles.width > bx &&
      el.position.y < by + bh && el.position.y + el.styles.height > by) sel.push(i)
  })
  if (sel.length) { selectedEls.value = sel; selectedElIdx.value = sel[0] }
}

// ─── Zoom ──────────────────────────────────────────────────────────────────────
function zoomIn() { zoom.value = Math.min(zoom.value + 10, 400) }
function zoomOut() { zoom.value = Math.max(zoom.value - 10, 25) }
function handleZoomWheel(e) { e.deltaY < 0 ? zoomIn() : zoomOut() }

// ─── Context Menu ─────────────────────────────────────────────────────────────
function showElContextMenu(e, pi, ei) {
  if (ei !== null && ei !== undefined) selectElementByIdx(ei)
  contextMenu.show = true; contextMenu.x = e.clientX; contextMenu.y = e.clientY
  const el = ei !== null ? currentPageElements.value[ei] : null
  contextMenu.items = [
    { label: 'Edit', icon: 'fa-solid fa-pen-to-square', action: () => startEditing({ pageIndex: pi, elementIndex: ei }) },
    { label: 'Duplicate', icon: 'fa-solid fa-clone', shortcut: 'Ctrl+D', action: duplicateSelected },
    { label: 'Copy', icon: 'fa-solid fa-copy', shortcut: 'Ctrl+C', action: copyElement },
    { label: 'Paste', icon: 'fa-solid fa-paste', shortcut: 'Ctrl+V', action: pasteElement, disabled: !clipboard.value },
    '---',
    { label: 'Copy Style', icon: 'fa-solid fa-paintbrush', action: stylePainterCopy },
    { label: 'Paste Style', icon: 'fa-solid fa-brush', action: stylePainterPaste, disabled: !stylePainterClipboard.value },
    '---',
    { label: 'Bring to Front', icon: 'fa-solid fa-angles-up', action: bringToFront },
    { label: 'Send to Back', icon: 'fa-solid fa-angles-down', action: sendToBack },
    '---',
    selectedEls.value.length > 1
      ? { label: 'Group Elements', icon: 'fa-solid fa-object-group', action: groupSelected }
      : { label: el?.groupId ? 'Ungroup' : 'Group', icon: 'fa-solid fa-object-group', action: el?.groupId ? ungroupSelected : groupSelected },
    '---',
    { label: el?.locked ? 'Unlock' : 'Lock', icon: el?.locked ? 'fa-solid fa-lock-open' : 'fa-solid fa-lock', action: lockElement },
    { label: 'Delete', icon: 'fa-solid fa-trash', shortcut: 'Del', danger: true, action: deleteSelected },
  ]
}

// ─── Image ─────────────────────────────────────────────────────────────────────
function triggerImageUpload(pi, ei) {
  currentImageTarget.value = { pi, ei, mode: 'upload' }; fileInput.value?.click()
}
function triggerImageReplace(el) {
  currentImageTarget.value = { el, mode: 'replace' }; fileInput.value?.click()
}
function handleUploadedImage(files) {
  Array.from(files).forEach(file => {
    const reader = new FileReader()
    reader.onload = (ev) => {
      if (currentImageTarget.value?.mode === 'replace' && currentImageTarget.value.el) {
        currentImageTarget.value.el.src = ev.target.result; markDirty(); return
      }
      const dims = getPageDims()
      addElement({ type: 'image', w: 300, h: 200, src: ev.target.result }, dims.w / 2 - 150, dims.h / 3)
    }
    reader.readAsDataURL(file)
  })
}
function handleFilePick(e) {
  handleUploadedImage(e.target.files); e.target.value = ''
}

// ─── Page Dblclick ─────────────────────────────────────────────────────────────
function onPageDblClick({ event, pageIndex }) {
  goToPage(pageIndex)
  const pageEl = document.querySelector(`[data-page-index="${pageIndex}"]`)
  if (!pageEl) return
  const rect = pageEl.getBoundingClientRect()
  const scale = zoom.value / 100
  const x = (event.clientX - rect.left) / scale
  const y = (event.clientY - rect.top) / scale
  addElement({ type: 'text', w: 200, h: 40 }, x, y)
}

// ─── Quick Template ────────────────────────────────────────────────────────────
function applyQuickTemplate(tpl) {
  pushUndo()
  if (tpl.primary_color) settings.primary_color = tpl.primary_color
  if (tpl.background_color) settings.background_color = tpl.background_color
  if (tpl.font_family) settings.font_family = tpl.font_family
  markDirty(); showToast(`Applied "${tpl.name}"`, 'success')
}

// ─── AI ───────────────────────────────────────────────────────────────────────
function insertAiContent({ type, content }) {
  const dims = getPageDims()
  const el = addElement({ type: type || 'text', w: 350, h: 120 }, dims.w / 2 - 175, dims.h / 3)
  if (el) el.content = content
}
function insertAiChart(data) {
  const dims = getPageDims()
  const el = addElement({ type: data.suggested_chart_type || 'bar-chart', w: 420, h: 290 }, dims.w / 2 - 210, dims.h / 3)
  if (el) { el.chartData = { labels: data.labels, values: data.values }; el.chartTitle = data.title }
}

// ─── TOC ──────────────────────────────────────────────────────────────────────
function refreshTOC() {
  if (!selectedEl.value || selectedEl.value.type !== 'toc') return
  selectedEl.value.tocItems = []
  report.content.forEach((page, pi) => {
    page.elements.forEach(e => {
      if (['heading', 'subheading'].includes(e.type)) {
        const text = (e.content || '').replace(/<[^>]*>/g, '').trim()
        if (text) selectedEl.value.tocItems.push({ text, page: pi + 1, level: e.type === 'heading' ? 1 : 2 })
      }
    })
  })
  markDirty()
}

// ─── Find/Replace ─────────────────────────────────────────────────────────────
function findInReport() {
  findMatches.value = []; findCurrentIdx.value = 0
  if (!findText.value) return
  const q = findText.value.toLowerCase()
  report.content.forEach((p, pi) => p.elements.forEach((el, ei) => {
    if (el.content && typeof el.content === 'string') {
      const txt = el.content.replace(/<[^>]*>/g, '')
      if (txt.toLowerCase().includes(q)) {
        const idx = txt.toLowerCase().indexOf(q)
        const s = Math.max(0, idx - 25), end = Math.min(txt.length, idx + q.length + 25)
        findMatches.value.push({ pi, ei, preview: (s > 0 ? '…' : '') + txt.substring(s, end) + (end < txt.length ? '…' : ''), el })
      }
    }
  }))
}
function navigateMatch(dir) {
  if (!findMatches.value.length) return
  findCurrentIdx.value = (findCurrentIdx.value + dir + findMatches.value.length) % findMatches.value.length
  goToMatch(findMatches.value[findCurrentIdx.value], findCurrentIdx.value)
}
function goToMatch(m, idx) {
  goToPage(m.pi); selectElementByIdx(m.ei); findCurrentIdx.value = idx
}
function replaceOne() {
  if (!findMatches.value.length) return
  const m = findMatches.value[findCurrentIdx.value]
  pushUndo()
  m.el.content = m.el.content.replace(new RegExp(findText.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i'), replaceText.value)
  markDirty(); findInReport()
}
function replaceAll() {
  if (!findMatches.value.length) return
  pushUndo()
  const re = new RegExp(findText.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi')
  let count = 0
  report.content.forEach(p => p.elements.forEach(el => {
    if (el.content && typeof el.content === 'string') {
      const old = el.content; el.content = el.content.replace(re, replaceText.value)
      if (el.content !== old) count++
    }
  }))
  markDirty(); showToast(`Replaced in ${count} elements`, 'success'); findInReport()
}

// ─── Presentation ─────────────────────────────────────────────────────────────
function startPresentation() {
  presentationPage.value = currentPage.value; presentationMode.value = true
}
function nextSlide() {
  if (presentationPage.value < report.content.length - 1) presentationPage.value++
  else presentationMode.value = false
}
function prevSlide() {
  if (presentationPage.value > 0) presentationPage.value--
}
function getPresPageStyle() {
  const dims = getPageDims()
  const scale = Math.min((window.innerWidth - 80) / dims.w, (window.innerHeight - 120) / dims.h)
  return {
    width: dims.w + 'px', height: dims.h + 'px',
    background: settings.background_color || '#fff',
    position: 'relative', overflow: 'hidden', borderRadius: '4px',
    transform: `scale(${scale})`, transformOrigin: 'top center',
    fontFamily: settings.font_family, padding: (settings.margin || 40) + 'px',
  }
}
function getPresElStyle(el) {
  const s = el.styles || {}
  return {
    position: 'absolute', left: (el.position?.x || 0) + 'px', top: (el.position?.y || 0) + 'px',
    width: (s.width || 100) + 'px', fontSize: (s.fontSize || 14) + 'px',
    color: s.color || '#000', textAlign: s.textAlign || 'left', fontWeight: s.fontWeight || '400', overflow: 'hidden',
  }
}
function getPresElContent(el) { return el.content || '' }

// ─── Status Cycle ──────────────────────────────────────────────────────────────
async function cycleStatus() {
  const statuses = ['draft', 'published', 'archived']
  const ns = statuses[(statuses.indexOf(report.status) + 1) % statuses.length]
  try {
    const res = await fetch(route('reports.status', report.slug), {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf(), Accept: 'application/json' },
      body: JSON.stringify({ status: ns }),
    })
    if (res.ok) {
      report.status = ns
      showToast(`Status: ${ns}`, 'success')
      if (ns === 'published') showConfetti.value = true
    }
  } catch (e) { showToast('Failed to update status', 'error') }
}

// ─── Export / Share ────────────────────────────────────────────────────────────
function exportFile(type) {
  const urls = {
    pdf: route('reports.download', report.slug),
    image: route('reports.export.image', report.slug),
    excel: route('reports.export.excel', report.slug),
    csv: route('reports.export.csv', report.slug),
  }
  window.open(urls[type], '_blank')
}
function previewReport() { window.open(route('reports.preview', report.slug), '_blank') }
function printPreview() { window.open(route('reports.preview', report.slug) + '?print=1', '_blank') }
async function shareReport() {
  try {
    const res = await fetch(route('reports.share', report.slug), {
      method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrf(), Accept: 'application/json' },
    })
    const data = await res.json()
    if (data.url) { await navigator.clipboard.writeText(data.url); showToast('Share link copied!', 'success') }
  } catch (e) { showToast('Share failed', 'error') }
}
async function emailReport() {
  const email = prompt('Enter email address:')
  if (!email) return
  try {
    await fetch(route('reports.email', report.slug), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf(), Accept: 'application/json' },
      body: JSON.stringify({ email }),
    })
    showToast('Report emailed!', 'success')
  } catch (e) { showToast('Email failed', 'error') }
}

// ─── Theme ─────────────────────────────────────────────────────────────────────
function toggleDark() {
  isDark.value = !isDark.value
  localStorage.setItem('rg_theme', isDark.value ? 'dark' : 'light')
  // DO NOT affect html/body class - editor has its own theme
}
function toggleFullscreen() {
  if (!isFullscreen.value) { editorShell.value?.requestFullscreen?.(); isFullscreen.value = true }
  else { document.exitFullscreen?.(); isFullscreen.value = false }
}
function completeOnboarding() { showOnboarding.value = false; localStorage.setItem('rg_onboarded', '1') }

// ─── Command Palette ───────────────────────────────────────────────────────────
function executeCommand(cmd) {
  const actions = {
    save: saveNow, undo, redo, delete: deleteSelected, duplicate: duplicateSelected,
    copy: copyElement, paste: pasteElement, 'select-all': () => {
      selectedEls.value = currentPageElements.value.map((_, i) => i); selectedElIdx.value = 0
    },
    deselect: deselectAll, 'add-page': addPage,
    'toggle-grid': () => showGrid.value = !showGrid.value,
    'toggle-dark': toggleDark, 'toggle-fullscreen': toggleFullscreen,
    'zoom-fit': () => zoom.value = 100, 'zoom-in': zoomIn, 'zoom-out': zoomOut,
    preview: previewReport, print: printPreview, presentation: startPresentation,
    'find-replace': () => showFindReplace.value = !showFindReplace.value,
    share: shareReport, email: emailReport,
    'bring-front': bringToFront, 'send-back': sendToBack,
    group: groupSelected, ungroup: ungroupSelected,
  }
  if (actions[cmd]) actions[cmd]()
}

// ─── Keyboard ──────────────────────────────────────────────────────────────────
function handleKeyboard(e) {
  const ctrl = e.ctrlKey || e.metaKey
  const editable = e.target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)

  // Block specific browser shortcuts we handle
  if (ctrl && !editable) {
    const blocked = ['s', 'z', 'y', 'c', 'v', 'd', 'a', 'f', 'p', 'b', 'i', 'u', 'm', 'g', 'n', 'k', '/']
    if (blocked.includes(e.key.toLowerCase())) { e.preventDefault(); e.stopPropagation() }
  }
  if (e.key === 'F11') { e.preventDefault(); e.stopPropagation() }

  if (ctrl && e.key === 'k') { showCommandPalette.value = !showCommandPalette.value; return }
  if (ctrl && e.key === '/') { showShortcuts.value = !showShortcuts.value; return }
  if (ctrl && e.key === 's') { saveNow(); return }
  if (ctrl && e.key === 'z') { undo(); return }
  if (ctrl && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { redo(); return }
  if (ctrl && e.key === 'c' && !editable) { copyElement(); return }
  if (ctrl && e.key === 'v' && !editable) { pasteElement(); return }
  if (ctrl && e.key === 'd' && !editable) { e.preventDefault(); duplicateSelected(); return }
  if (ctrl && e.key === 'a' && !editable) { selectedEls.value = currentPageElements.value.map((_, i) => i); selectedElIdx.value = 0; return }
  if (ctrl && e.key === 'f') { showFindReplace.value = !showFindReplace.value; return }
  if (ctrl && e.key === 'g') { showGrid.value = !showGrid.value; return }
  if (ctrl && e.key === 'n' && !editable) { addPage(); return }
  if (ctrl && e.key === 'm') { measureMode.value = !measureMode.value; return }
  if (ctrl && e.key === 'p' && !editable) { previewReport(); return }
  if (ctrl && e.key === 'b' && !editable) { toggleFmt('fontWeight', '700', '400'); return }
  if (ctrl && e.key === 'i' && !editable) { toggleFmt('fontStyle', 'italic', 'normal'); return }
  if (ctrl && e.key === 'u' && !editable) { toggleFmt('textDecoration', 'underline', 'none'); return }
  if (ctrl && e.key === 'F5') { startPresentation(); return }
  if (e.key === 'F11') { toggleFullscreen(); return }

  if (presentationMode.value) {
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === ' ') { nextSlide(); return }
    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { prevSlide(); return }
    if (e.key === 'Escape') { presentationMode.value = false; return }
  }

  if (e.key === 'Escape') {
    deselectAll(); showCommandPalette.value = false; showShortcuts.value = false
    contextMenu.show = false; showFindReplace.value = false; stylePainterActive.value = false; return
  }
  if ((e.key === 'Delete' || e.key === 'Backspace') && !editable) { deleteSelected(); return }

  // Arrow nudge
  if (!editable && selectedEl.value) {
    const STEP = e.shiftKey ? 10 : 1
    if (e.key === 'ArrowLeft') { e.preventDefault(); selectedEl.value.position.x = Math.max(0, snap(selectedEl.value.position.x - STEP)); markDirty() }
    else if (e.key === 'ArrowRight') { e.preventDefault(); selectedEl.value.position.x = snap(selectedEl.value.position.x + STEP); markDirty() }
    else if (e.key === 'ArrowUp') { e.preventDefault(); selectedEl.value.position.y = Math.max(0, snap(selectedEl.value.position.y - STEP)); markDirty() }
    else if (e.key === 'ArrowDown') { e.preventDefault(); selectedEl.value.position.y = snap(selectedEl.value.position.y + STEP); markDirty() }
  }

  // Page navigation
  if (e.key === 'PageDown' && !editable) { goToPage(currentPage.value + 1) }
  if (e.key === 'PageUp' && !editable) { goToPage(currentPage.value - 1) }
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
  // Apply theme to shell only, NOT html element
  pushUndo()
  editorShell.value?.focus()

  // Draft recovery
  try {
    const draft = localStorage.getItem(`rg_draft_${report.slug}`)
    if (draft) {
      const data = JSON.parse(draft)
      const savedAt = new Date(data.savedAt)
      const serverUpdated = new Date(report.updated_at || 0)
      if (savedAt > serverUpdated && confirm(`Recover unsaved draft from ${savedAt.toLocaleTimeString()}?`)) {
        report.content = data.content
        Object.assign(settings, data.settings)
        isDirty.value = true; showToast('Draft recovered!', 'warning')
      }
      localStorage.removeItem(`rg_draft_${report.slug}`)
    }
  } catch (_) { }

  autoSaveInterval = setInterval(() => { if (isDirty.value) saveNow() }, 30000)
  document.addEventListener('fullscreenchange', () => { isFullscreen.value = !!document.fullscreenElement })

  // Prevent default browser shortcuts globally while editor is focused
  const preventBrowserShortcuts = (e) => {
    if (!editorShell.value?.contains(document.activeElement) && document.activeElement !== editorShell.value) return
    const ctrl = e.ctrlKey || e.metaKey
    const editable = e.target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)
    if (ctrl && !editable) {
      const blocked = ['s', 'z', 'y', 'd', 'a', 'p', 'g', 'n']
      if (blocked.includes(e.key.toLowerCase())) { e.preventDefault(); e.stopPropagation() }
    }
    if (e.key === 'F11') { e.preventDefault(); e.stopPropagation() }
  }
  document.addEventListener('keydown', preventBrowserShortcuts, { capture: true })

  // Store cleanup
  window.__rgPreventShortcuts = preventBrowserShortcuts
})

onBeforeUnmount(() => {
  clearTimeout(saveTimer); clearInterval(autoSaveInterval)
  if (window.__rgPreventShortcuts) {
    document.removeEventListener('keydown', window.__rgPreventShortcuts, { capture: true })
  }
})

// Watch dark mode - apply to shell only
watch(isDark, (val) => {
  if (editorShell.value) editorShell.value.setAttribute('data-theme', val ? 'dark' : 'light')
})

// Expose for template helpers
window.showToast = showToast
</script>

<style>
/* Editor Shell - Completely isolated from page/app styles */
.editor-shell {
  --bg: #f1f5f9;
  --bg-panel: #ffffff;
  --bg-secondary: #f8fafc;
  --bg-tertiary: #f1f5f9;
  --border: #e2e8f0;
  --border-light: #f1f5f9;
  --border-hover: #cbd5e1;
  --text-primary: #0f172a;
  --text-secondary: #475569;
  --text-muted: #94a3b8;
  --accent: #6366f1;
  --accent-hover: #4f46e5;
  --accent-light: rgba(99, 102, 241, 0.08);
  --accent-soft: rgba(99, 102, 241, 0.15);
  --danger: #ef4444;
  --danger-light: rgba(239, 68, 68, 0.08);
  --success: #10b981;
  --warning: #f59e0b;
  --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
  --shadow-xl: 0 12px 40px rgba(0, 0, 0, 0.16);

  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
  background: var(--bg);
  color: var(--text-primary);
  font-size: 13px;
  line-height: 1.5;
  outline: none;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  /* Isolate from parent page styles */
  isolation: isolate;
  contain: layout style;
}

/* Dark theme applied via data attribute, NOT class on html */
.editor-shell.is-dark,
.editor-shell[data-theme="dark"] {
  --bg: #0b1120;
  --bg-panel: #1a2236;
  --bg-secondary: #111827;
  --bg-tertiary: #0f172a;
  --border: #263348;
  --border-light: #1e2a3d;
  --border-hover: #334155;
  --text-primary: #e2e8f0;
  --text-secondary: #94a3b8;
  --text-muted: #64748b;
  --accent: #818cf8;
  --accent-hover: #6366f1;
  --accent-light: rgba(129, 140, 248, 0.1);
  --accent-soft: rgba(129, 140, 248, 0.18);
  --danger: #f87171;
  --danger-light: rgba(248, 113, 113, 0.1);
  --success: #34d399;
  --warning: #fbbf24;
  --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.3);
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.5);
  --shadow-xl: 0 12px 40px rgba(0, 0, 0, 0.6);
}

.editor-shell.is-fullscreen {
  position: fixed;
  inset: 0;
  z-index: 9999;
}

/* Reset within editor */
.editor-shell *,
.editor-shell *::before,
.editor-shell *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.editor-shell input,
.editor-shell select,
.editor-shell textarea,
.editor-shell button {
  font-family: inherit;
  font-size: inherit;
}

.editor-shell .hidden {
  display: none !important;
}

.editor-body {
  flex: 1;
  display: flex;
  overflow: hidden;
  min-height: 0;
}

/* Scrollbar */
.editor-shell ::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}

.editor-shell ::-webkit-scrollbar-track {
  background: transparent;
}

.editor-shell ::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 99px;
}

.editor-shell ::-webkit-scrollbar-thumb:hover {
  background: var(--text-muted);
}

.editor-shell ::selection {
  background: var(--accent);
  color: #fff;
}

.editor-shell *:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* Find & Replace Panel */
.fr-panel {
  position: fixed;
  right: 20px;
  top: 80px;
  width: 340px;
  max-height: 480px;
  background: var(--bg-panel);
  border: 1px solid var(--border);
  border-radius: 14px;
  box-shadow: var(--shadow-xl);
  z-index: 500;
  display: flex;
  flex-direction: column;
}

.fr-panel.dark {
  --bg-panel: #1a2236;
  --border: #263348;
  --text-primary: #e2e8f0;
  --bg-secondary: #111827;
}

.fr-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-bottom: 1px solid var(--border);
  font-weight: 700;
  font-size: 12px;
  color: var(--text-primary);
}

.fr-header i {
  color: var(--accent);
}

.fr-header button {
  margin-left: auto;
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
  font-size: 14px;
}

.fr-body {
  padding: 10px;
  overflow-y: auto;
}

.fr-row {
  margin-bottom: 6px;
}

.fr-input {
  width: 100%;
  padding: 7px 10px;
  border: 1px solid var(--border);
  border-radius: 7px;
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 12px;
  outline: none;
}

.fr-input:focus {
  border-color: var(--accent);
}

.fr-count {
  font-size: 11px;
  color: var(--text-muted);
  margin-bottom: 6px;
}

.fr-actions {
  display: flex;
  gap: 4px;
  margin-bottom: 8px;
}

.fr-btn {
  flex: 1;
  padding: 6px 10px;
  border: none;
  background: var(--accent);
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
  font-family: inherit;
}

.fr-btn.secondary {
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border);
}

.fr-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.fr-results {
  max-height: 200px;
  overflow-y: auto;
}

.fr-match {
  padding: 6px 8px;
  font-size: 11px;
  color: var(--text-secondary);
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  gap: 6px;
}

.fr-match:hover,
.fr-match.active {
  background: var(--accent-light);
  color: var(--accent);
}

.fr-match-page {
  font-weight: 700;
  color: var(--accent);
  flex-shrink: 0;
}

/* Presentation */
.pres-overlay {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.pres-page {
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  transition: none;
}

.pres-controls {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(12px);
  padding: 10px 20px;
  border-radius: 99px;
  color: #fff;
}

.pres-controls button {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.pres-controls button:hover {
  background: rgba(255, 255, 255, 0.25);
}

.pres-controls button:disabled {
  opacity: 0.3;
}

.pres-sep {
  opacity: 0.3;
}

/* Animations */
.slide-up-enter-active {
  animation: slideUp 0.25s ease;
}

.slide-up-leave-active {
  animation: slideUp 0.2s ease reverse;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>