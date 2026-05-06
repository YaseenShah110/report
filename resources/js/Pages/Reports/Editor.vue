<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   ReportGen ULTIMATE - World's Most Advanced Report Editor     ║
  ║   Main Orchestrator - Editor.vue                                ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <div
    ref="editorShell"
    class="editor-shell"
    :class="{ 'dark': isDark, 'fullscreen': isFullscreen }"
    @keydown="handleKeyboard"
    tabindex="0"
  >
    
    <!-- ═══ TOP TOOLBAR ═══════════════════════════════════════════ -->
    <TopToolbar
      :report="report"
      :settings="settings"
      :is-dirty="isDirty"
      :is-saving="isSaving"
      :last-saved="lastSaved"
      :zoom="zoom"
      :can-undo="canUndo"
      :can-redo="canRedo"
      :selected-el="selectedEl"
      :show-grid="showGrid"
      :snap-to-grid="snapToGrid"
      :show-rulers="showRulers"
      :is-dark="isDark"
      :is-fullscreen="isFullscreen"
      :show-ai="showAI"
      @update:title="report.title = $event; markDirty()"
      @save="saveNow"
      @undo="undo"
      @redo="redo"
      @zoom-in="zoomIn"
      @zoom-out="zoomOut"
      @zoom-reset="zoom = 100"
      @toggle-grid="showGrid = !showGrid"
      @toggle-snap="snapToGrid = !snapToGrid"
      @toggle-rulers="showRulers = !showRulers"
      @toggle-dark="toggleDark"
      @toggle-fullscreen="toggleFullscreen"
      @toggle-ai="showAI = !showAI"
      @preview="previewReport"
      @export-pdf="exportFile('pdf')"
      @export-png="exportFile('image')"
      @export-excel="exportFile('excel')"
      @export-csv="exportFile('csv')"
      @share="shareReport"
      @change-status="cycleStatus"
      @apply-style="applyStyle"
      @toggle-fmt="toggleFmt"
      @delete-el="deleteSelected"
      @duplicate-el="duplicateSelected"
      @lock-el="lockElement"
      @bring-front="bringToFront"
      @send-back="sendToBack"
    />

    <!-- ═══ MAIN WORKSPACE ════════════════════════════════════════ -->
    <div class="editor-body">
      
      <!-- ─── LEFT SIDEBAR ──────────────────────────────────────── -->
      <LeftSidebar
        :report="report"
        :settings="settings"
        :current-page="currentPage"
        :selected-el-idx="selectedElIdx"
        :selected-els="selectedEls"
        :active-tab="activeLeftTab"
        @add-element-center="addElementCenter"
        @select-page="goToPage"
        @add-page="addPage"
        @duplicate-page="duplicatePage"
        @delete-page="deletePage"
        @rename-page="renamePage"
        @select-element="selectElementByIdx"
        @toggle-visibility="toggleElementVisibility"
        @toggle-lock="toggleElementLock"
        @upload-image="handleImageUpload"
        @apply-template="applyQuickTemplate"
        @deselect-all="deselectAll"
        @update:settings="Object.assign(settings, $event); markDirty()"
        @update:active-tab="activeLeftTab = $event"
        @canvas-drag-start="onElDragStart"
      />

      <!-- ─── CANVAS ────────────────────────────────────────────── -->
      <EditorCanvas
        :report="report"
        :settings="settings"
        :current-page="currentPage"
        :selected-el-idx="selectedElIdx"
        :selected-els="selectedEls"
        :editing-el-idx="editingElIdx"
        :zoom="zoom"
        :show-grid="showGrid"
        :snap-to-grid="snapToGrid"
        :show-rulers="showRulers"
        :is-dragging-el="isDraggingEl"
        :rubber-band="rubberBand"
        :drop-target-page="dropTargetPage"
        @select-element="selectElementByIdx"
        @deselect-all="deselectAll"
        @add-element="addElementAtPosition"
        @select-page="goToPage"
        @add-page="addPage"
        @start-editing="startEditing"
        @update-text-content="updateTextContent"
        @element-mouse-down="onElementMouseDown"
        @resize-start="startResize"
        @rotate-start="startRotate"
        @canvas-drop="onCanvasDrop"
        @canvas-drag-end="isDraggingEl = false"
        @rubber-band-start="startRubberBand"
        @rubber-band-move="handleRubberBandMove"
        @rubber-band-end="endRubberBand"
        @zoom-wheel="handleZoomWheel"
        @page-dblclick="onPageDblClick"
        @context-menu="showElContextMenu"
        @image-upload="triggerImageUpload"
        @image-replace="triggerImageReplace"
        @go-to-page="goToPage"
        @mark-dirty="markDirty"
        @zoom-reset="zoom = 100"
      />

      <!-- ─── RIGHT SIDEBAR ─────────────────────────────────────── -->
      <RightSidebar
        :selected-el="selectedEl"
        :settings="settings"
        :active-section="activeRightSection"
        :current-page-elements="currentPageElements"
        :clipboard="clipboard"
        :style-painter-clipboard="stylePainterClipboard"
        @update:style="applyStyle"
        @update:content="updateElementContent"
        @delete-el="deleteSelected"
        @duplicate-el="duplicateSelected"
        @copy-el="copyElement"
        @paste-el="pasteElement"
        @lock-el="lockElement"
        @bring-front="bringToFront"
        @send-back="sendToBack"
        @align-to-page="alignToPage"
        @update:settings="Object.assign(settings, $event); markDirty()"
        @update:active-section="activeRightSection = $event"
        @add-table-row="addTableRow"
        @add-table-col="addTableColumn"
        @remove-table-row="removeTableRow"
        @remove-table-col="removeTableColumn"
        @set-chart-labels="setChartLabels"
        @set-chart-values="setChartValues"
        @add-timeline-item="addTimelineItem"
        @remove-timeline-item="removeTimelineItem"
        @add-checklist-item="addChecklistItem"
        @remove-checklist-item="removeChecklistItem"
        @add-stat-item="addStatItem"
        @remove-stat-item="removeStatItem"
        @reset-styles="resetElementStyles"
        @style-painter-copy="stylePainterCopy"
        @style-painter-paste="stylePainterPaste"
        @mark-dirty="markDirty"
        @image-replace="triggerImageReplace"
      />

    </div>

    <!-- ═══ STATUS BAR ═══════════════════════════════════════════ -->
    <StatusBar
      :current-page="currentPage"
      :total-pages="report.content.length"
      :elements-count="currentPageElements.length"
      :total-elements="totalElements"
      :selected-el="selectedEl"
      :zoom="zoom"
      :is-dirty="isDirty"
      :is-saving="isSaving"
      :last-saved="lastSaved"
      :page-size="settings.page_size"
      :orientation="settings.orientation"
      @zoom-reset="zoom = 100"
    />

    <!-- ═══ AI PANEL ═══════════════════════════════════════════ -->
    <AiPanel
      :visible="showAI"
      :report="report"
      @close="showAI = false"
      @insert-content="insertAiContent"
      @insert-chart="insertAiChart"
    />

    <!-- ═══ COMMAND PALETTE ═══════════════════════════════════════ -->
    <CommandPalette
      v-if="showCommandPalette"
      @close="showCommandPalette = false"
      @execute="executeCommand"
    />

    <!-- ═══ KEYBOARD SHORTCUT OVERLAY ═══════════════════════════ -->
    <ShortcutOverlay
      v-if="showShortcuts"
      @close="showShortcuts = false"
    />

    <!-- ═══ ONBOARDING TOUR ═══════════════════════════════════════ -->
    <OnboardingTour
      v-if="showOnboarding"
      @complete="completeOnboarding"
    />

    <!-- ═══ CONTEXT MENU ═══════════════════════════════════════ -->
    <ContextMenu
      :show="contextMenu.show"
      :x="contextMenu.x"
      :y="contextMenu.y"
      :items="contextMenu.items"
      @close="contextMenu.show = false"
    />

    <!-- ═══ CONFETTI OVERLAY ═══════════════════════════════════ -->
    <ConfettiOverlay
      v-if="showConfetti"
      @complete="showConfetti = false"
    />

    <!-- ═══ TOAST CONTAINER ═══════════════════════════════════ -->
    <ToastContainer
      :toasts="toasts"
      @remove="removeToast"
    />

    <!-- ═══ HIDDEN FILE INPUT ═══════════════════════════════════ -->
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      class="hidden"
      multiple
      @change="handleFilePick"
    />

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'

// ═══ Component Imports ═══════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════════════
const props = defineProps({ report: { type: Object, required: true } })

// ═══════════════════════════════════════════════════════════════════
// CORE STATE
// ═══════════════════════════════════════════════════════════════════
const report = reactive(JSON.parse(JSON.stringify(props.report)))
const settings = reactive(JSON.parse(JSON.stringify(props.report?.settings || getDefaultSettings())))


// Ensure all default settings exist
function getDefaultSettings() {
  return {
    page_size: 'A4', orientation: 'portrait', margin: 40, page_radius: 0,
    background_color: '#ffffff', bg_image: '', primary_color: '#6366f1',
    accent_color: '#8b5cf6', text_color: '#0f172a', font_family: 'Inter',
    font_size: 14, show_header: false, header_text: '', header_color: '#1e293b',
    show_footer: false, footer_left: '', footer_right: '', show_page_numbers: true,
    watermark: '', watermark_opacity: 5, rtl: false, custom_w: 794, custom_h: 1123
  }
}
Object.keys(getDefaultSettings()).forEach(k => {
  if (settings[k] === undefined || settings[k] === null) settings[k] = getDefaultSettings()[k]
})

// Initialize content
if (!report.content?.length) {
  report.content = [{ id: uid(), label: 'Page 1', elements: [] }]
}

// ═══════════════════════════════════════════════════════════════════
// UI STATE
// ═══════════════════════════════════════════════════════════════════
const isDark = ref(localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia?.('(prefers-color-scheme: dark)').matches))
const isFullscreen = ref(false)
const zoom = ref(100)
const showGrid = ref(true)
const snapToGrid = ref(true)
const showRulers = ref(false)
const showAI = ref(false)
const showCommandPalette = ref(false)
const showShortcuts = ref(false)
const showConfetti = ref(false)
const showOnboarding = ref(!localStorage.getItem('rg_onboarded'))
const isDirty = ref(false)
const isSaving = ref(false)
const lastSaved = ref('')
const editorShell = ref(null)
const fileInput = ref(null)

// ═══════════════════════════════════════════════════════════════════
// SELECTION STATE
// ═══════════════════════════════════════════════════════════════════
const currentPage = ref(0)
const selectedElIdx = ref(null)
const selectedEls = ref([])
const editingElIdx = ref(null)
const isDraggingEl = ref(false)
const dropTargetPage = ref(null)
const activeLeftTab = ref('elements')
const activeRightSection = ref('props')

// ═══════════════════════════════════════════════════════════════════
// CLIPBOARD
// ═══════════════════════════════════════════════════════════════════
const clipboard = ref(null)
const stylePainterClipboard = ref(null)
const currentImageTarget = ref(null)

// ═══════════════════════════════════════════════════════════════════
// RUBBER BAND
// ═══════════════════════════════════════════════════════════════════
const rubberBand = reactive({ active: false, startX: 0, startY: 0, x: 0, y: 0, w: 0, h: 0 })

// ═══════════════════════════════════════════════════════════════════
// CONTEXT MENU
// ═══════════════════════════════════════════════════════════════════
const contextMenu = reactive({ show: false, x: 0, y: 0, items: [] })

// ═══════════════════════════════════════════════════════════════════
// TOASTS
// ═══════════════════════════════════════════════════════════════════
const toasts = ref([])
let toastId = 0

// ═══════════════════════════════════════════════════════════════════
// UNDO/REDO
// ═══════════════════════════════════════════════════════════════════
const undoStack = ref([])
const redoStack = ref([])

// ═══════════════════════════════════════════════════════════════════
// TIMERS
// ═══════════════════════════════════════════════════════════════════
let saveTimer = null
let autoSaveInterval = null

// ═══════════════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════════════
const currentPageElements = computed(() => report.content[currentPage.value]?.elements || [])
const selectedEl = computed(() => {
  if (selectedElIdx.value === null || !currentPageElements.value[selectedElIdx.value]) return null
  return currentPageElements.value[selectedElIdx.value]
})
const canUndo = computed(() => undoStack.value.length > 0)
const canRedo = computed(() => redoStack.value.length > 0)
const totalElements = computed(() => report.content.reduce((s, p) => s + (p.elements?.length || 0), 0))

// ═══════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════
function uid() { return crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).slice(2) + Date.now().toString(36) }
function getCsrf() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }
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

// ═══════════════════════════════════════════════════════════════════
// ELEMENT CATALOG (for type defaults)
// ═══════════════════════════════════════════════════════════════════
const elementCatalog = [
  {
    name: 'Text', items: [
      { type: 'text', label: 'Text', w: 200, h: 50, icon: 'fa-solid fa-align-left' },
      { type: 'heading', label: 'Heading', w: 350, h: 60, icon: 'fa-solid fa-heading', defaultContent: 'Heading' },
      { type: 'subheading', label: 'Subheading', w: 280, h: 45, icon: 'fa-solid fa-h', defaultContent: 'Subheading' },
      { type: 'quote', label: 'Quote', w: 300, h: 80, icon: 'fa-solid fa-quote-right', defaultContent: 'Inspiring quote...' },
      { type: 'code', label: 'Code', w: 360, h: 120, icon: 'fa-solid fa-code', defaultContent: '// code here' },
      { type: 'badge', label: 'Badge', w: 120, h: 35, icon: 'fa-solid fa-tag', defaultContent: 'Badge' },
      { type: 'link', label: 'Link', w: 200, h: 35, icon: 'fa-solid fa-link', defaultContent: 'https://...' },
    ]
  },
  {
    name: 'Data', items: [
      { type: 'table', label: 'Table', w: 460, h: 220, icon: 'fa-solid fa-table' },
      { type: 'metric', label: 'KPI Card', w: 200, h: 120, icon: 'fa-solid fa-chart-simple' },
      { type: 'progress', label: 'Progress', w: 350, h: 60, icon: 'fa-solid fa-bars-progress' },
      { type: 'bar-chart', label: 'Bar Chart', w: 400, h: 280, icon: 'fa-solid fa-chart-bar' },
      { type: 'line-chart', label: 'Line Chart', w: 400, h: 280, icon: 'fa-solid fa-chart-line' },
      { type: 'pie-chart', label: 'Pie Chart', w: 280, h: 280, icon: 'fa-solid fa-chart-pie' },
      { type: 'checklist', label: 'Checklist', w: 300, h: 180, icon: 'fa-solid fa-list-check' },
      { type: 'stat-row', label: 'Stat Row', w: 450, h: 90, icon: 'fa-solid fa-chart-simple' },
    ]
  },
  {
    name: 'Media', items: [
      { type: 'image', label: 'Image', w: 300, h: 200, icon: 'fa-solid fa-image' },
      { type: 'icon', label: 'Icon', w: 60, h: 60, icon: 'fa-solid fa-star', defaultContent: '⭐' },
      { type: 'rating', label: 'Rating', w: 160, h: 40, icon: 'fa-solid fa-star-half-stroke' },
      { type: 'qr-code', label: 'QR Code', w: 150, h: 150, icon: 'fa-solid fa-qrcode', isNew: true },
    ]
  },
  {
    name: 'Layout', items: [
      { type: 'rectangle', label: 'Rectangle', w: 200, h: 120, icon: 'fa-solid fa-square' },
      { type: 'circle', label: 'Circle', w: 120, h: 120, icon: 'fa-solid fa-circle' },
      { type: 'triangle', label: 'Triangle', w: 120, h: 100, icon: 'fa-solid fa-play' },
      { type: 'divider', label: 'Divider', w: 500, h: 4, icon: 'fa-solid fa-minus' },
      { type: 'arrow', label: 'Arrow', w: 200, h: 40, icon: 'fa-solid fa-arrow-right' },
      { type: 'callout', label: 'Callout', w: 380, h: 100, icon: 'fa-solid fa-lightbulb' },
      { type: 'timeline', label: 'Timeline', w: 420, h: 250, icon: 'fa-solid fa-timeline' },
      { type: 'testimonial', label: 'Testimonial', w: 360, h: 160, icon: 'fa-solid fa-comment-dots' },
      { type: 'signature', label: 'Signature', w: 220, h: 100, icon: 'fa-solid fa-signature' },
    ]
  },
]

// ═══════════════════════════════════════════════════════════════════
// TOAST SYSTEM
// ═══════════════════════════════════════════════════════════════════
function showToast(message, type = 'success', duration = 3000) {
  const id = ++toastId
  toasts.value.push({ id, message, type })
  setTimeout(() => removeToast(id), duration)
}
function removeToast(id) { toasts.value = toasts.value.filter(t => t.id !== id) }

// ═══════════════════════════════════════════════════════════════════
// SAVE SYSTEM
// ═══════════════════════════════════════════════════════════════════
function markDirty() { isDirty.value = true; clearTimeout(saveTimer); saveTimer = setTimeout(autoSave, 1500) }

async function autoSave() { if (isDirty.value && !isSaving.value) await saveNow() }

async function saveNow() {
  if (isSaving.value) return
  isSaving.value = true
  try {
    const res = await fetch(route('reports.update', report.slug), {
      method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ title: report.title, content: report.content, settings: JSON.parse(JSON.stringify(settings)) })
    })
    if (res.ok) { isDirty.value = false; lastSaved.value = new Date().toLocaleTimeString() }
  } catch (err) { console.warn('Save failed:', err); showToast('Save failed', 'error') }
  isSaving.value = false
}

// ═══════════════════════════════════════════════════════════════════
// UNDO / REDO
// ═══════════════════════════════════════════════════════════════════
function pushUndo() {
  undoStack.value.push(JSON.stringify({ content: report.content, settings: JSON.parse(JSON.stringify(settings)) }))
  if (undoStack.value.length > 100) undoStack.value.shift()
  redoStack.value = []; isDirty.value = true
}
function undo() {
  if (!undoStack.value.length) return
  redoStack.value.push(JSON.stringify({ content: report.content, settings: JSON.parse(JSON.stringify(settings)) }))
  const state = JSON.parse(undoStack.value.pop())
  report.content = state.content; Object.assign(settings, state.settings)
  selectedElIdx.value = null; selectedEls.value = []
}
function redo() {
  if (!redoStack.value.length) return
  undoStack.value.push(JSON.stringify({ content: report.content, settings: JSON.parse(JSON.stringify(settings)) }))
  const state = JSON.parse(redoStack.value.pop())
  report.content = state.content; Object.assign(settings, state.settings)
  selectedElIdx.value = null; selectedEls.value = []
}

// ═══════════════════════════════════════════════════════════════════
// ELEMENT CREATION
// ═══════════════════════════════════════════════════════════════════
function createElement(def, x, y) {
  const snap = snapToGrid.value ? (v) => Math.round(v / 10) * 10 : (v) => v
  const el = {
    id: uid(), type: def.type,
    content: def.defaultContent || (def.type === 'text' ? 'Start typing...' : def.type === 'heading' ? 'Heading' : ''),
    position: { x: Math.max(0, snap(x)), y: Math.max(0, snap(y)) },
    styles: {
      width: def.w || 200, height: def.h || 80,
      fontSize: def.type === 'heading' ? 28 : (def.type === 'subheading' ? 20 : 14),
      fontWeight: def.type === 'heading' ? '700' : '400',
      fontFamily: settings.font_family || 'Inter', color: settings.text_color || '#0f172a',
      textAlign: 'left', lineHeight: 1.5, letterSpacing: 0,
      backgroundColor: ['rectangle', 'circle', 'triangle'].includes(def.type) ? (settings.primary_color || '#6366f1') : 'transparent',
      opacity: 100, borderRadius: 0, rotate: 0, zIndex: 1,
      borderWidth: 0, borderColor: '#000000', borderStyle: 'solid',
      boxShadow: 'none', filter: 'none', mixBlendMode: 'normal',
      padding: 8, textTransform: 'none', textDecoration: 'none', fontStyle: 'normal',
      scaleX: 1, scaleY: 1, blur: 0, brightness: 100, contrast: 100, grayscale: 0,
    },
    locked: false, visible: true,
  }
  // Type-specific defaults
  if (el.type === 'metric') { el.label = 'Revenue'; el.value = '$48K'; el.change = '+12%'; el.changeType = 'positive'; el.styles.backgroundColor = '#f8fafc'; el.styles.borderRadius = 12 }
  if (el.type === 'table') { el.columns = ['Col 1', 'Col 2', 'Col 3']; el.data = [{ 'Col 1': '', 'Col 2': '', 'Col 3': '' }]; el.styles.backgroundColor = '#ffffff' }
  if (el.type?.endsWith('-chart')) { el.chartTitle = def.label || 'Chart'; el.chartData = { labels: ['Q1', 'Q2', 'Q3', 'Q4'], values: [25, 40, 35, 55] }; el.styles.backgroundColor = '#ffffff'; el.styles.borderRadius = 8 }
  if (el.type === 'progress') { el.label = 'Progress'; el.value = 65 }
  if (el.type === 'checklist') { el.items = [{ text: 'Task 1', checked: false }, { text: 'Task 2', checked: false }] }
  if (el.type === 'timeline') { el.items = [{ date: 'Q1', label: 'Start', desc: 'Description' }, { date: 'Q2', label: 'Launch', desc: 'Description' }] }
  if (el.type === 'stat-row') { el.stats = [{ value: '12.4K', label: 'Users' }, { value: '$48K', label: 'Revenue' }, { value: '94%', label: 'Satisfaction' }]; el.styles.backgroundColor = '#f8fafc'; el.styles.borderRadius = 8 }
  if (el.type === 'testimonial') { el.author = 'Jane Doe'; el.role = 'CEO'; el.styles.borderRadius = 12 }
  if (el.type === 'callout') { el.emoji = '💡'; el.styles.backgroundColor = (settings.primary_color || '#6366f1') + '15'; el.styles.borderRadius = 8 }
  if (el.type === 'rating') { el.value = 4 }
  if (el.type === 'qr-code') { el.qrText = 'https://example.com'; el.qrSize = 150; el.styles.backgroundColor = '#ffffff'; el.styles.borderRadius = 8 }
  return el
}

function addElement(def, x, y) {
  pushUndo()
  const el = createElement(def, x, y)
  currentPageElements.value.push(el)
  selectedElIdx.value = currentPageElements.value.length - 1
  selectedEls.value = []
  markDirty()
  return el
}

function addElementCenter(def) {
  const dims = getPageDims()
  addElement(def, (dims.w - (def.w || 200)) / 2, dims.h / 3)
}

function addElementAtPosition({ def, x, y }) { addElement(def, x || 100, y || 100) }

// ═══════════════════════════════════════════════════════════════════
// ELEMENT OPERATIONS
// ═══════════════════════════════════════════════════════════════════
function selectElementByIdx(idx) { selectedElIdx.value = idx; selectedEls.value = []; editingElIdx.value = null }
function deselectAll() { selectedElIdx.value = null; selectedEls.value = []; editingElIdx.value = null }
function startEditing({ pageIndex, elementIndex }) {
  if (currentPageElements.value[elementIndex]?.locked) return
  selectElementByIdx(elementIndex)
  editingElIdx.value = elementIndex
}
function deleteSelected() {
  if (selectedElIdx.value === null) return
  pushUndo(); currentPageElements.value.splice(selectedElIdx.value, 1)
  selectedElIdx.value = null; selectedEls.value = []; markDirty()
}
function duplicateSelected() {
  if (!selectedEl.value) return
  pushUndo()
  const copy = JSON.parse(JSON.stringify(selectedEl.value))
  copy.id = uid(); copy.position.x += 24; copy.position.y += 24; copy.styles.zIndex = (copy.styles.zIndex || 1) + 1
  currentPageElements.value.push(copy); selectedElIdx.value = currentPageElements.value.length - 1; markDirty()
}
function copyElement() { if (selectedEl.value) { clipboard.value = JSON.parse(JSON.stringify(selectedEl.value)); showToast('Copied', 'success') } }
function pasteElement() {
  if (!clipboard.value) return
  pushUndo(); const copy = JSON.parse(JSON.stringify(clipboard.value))
  copy.id = uid(); copy.position.x += 30; copy.position.y += 30
  currentPageElements.value.push(copy); selectedElIdx.value = currentPageElements.value.length - 1; markDirty()
}
function lockElement() { if (selectedEl.value) { selectedEl.value.locked = !selectedEl.value.locked; markDirty() } }
function bringToFront() { if (selectedEl.value) { const maxZ = Math.max(...currentPageElements.value.map(e => e.styles?.zIndex || 1), 0); selectedEl.value.styles.zIndex = maxZ + 1; markDirty() } }
function sendToBack() { if (selectedEl.value) { selectedEl.value.styles.zIndex = 0; markDirty() } }
function toggleElementVisibility(idx) { const el = currentPageElements.value[idx]; if (el) { el.visible = el.visible === false ? true : false; markDirty() } }
function toggleElementLock(idx) { const el = currentPageElements.value[idx]; if (el) { el.locked = !el.locked; markDirty() } }
function applyStyle(prop, val) { if (!selectedEl.value) return; if (!selectedEl.value.styles) selectedEl.value.styles = {}; selectedEl.value.styles[prop] = val; markDirty() }
function toggleFmt(prop, onVal, offVal) { if (!selectedEl.value?.styles) return; applyStyle(prop, selectedEl.value.styles[prop] === onVal ? offVal : onVal) }
function updateElementContent(content) { if (selectedEl.value) { selectedEl.value.content = content; markDirty() } }
function updateTextContent({ content }) { if (selectedEl.value) { selectedEl.value.content = content; markDirty() } }
function resetElementStyles() {
  if (!selectedEl.value) return
  const def = elementCatalog.flatMap(c => c.items).find(i => i.type === selectedEl.value.type)
  selectedEl.value.styles = createElement(def || { type: 'text', w: 200, h: 80 }, 0, 0).styles; markDirty()
}
function stylePainterCopy() { if (selectedEl.value?.styles) { stylePainterClipboard.value = JSON.parse(JSON.stringify(selectedEl.value.styles)); showToast('Style copied!', 'success') } }
function stylePainterPaste() { if (stylePainterClipboard.value && selectedEl.value) { selectedEl.value.styles = JSON.parse(JSON.stringify(stylePainterClipboard.value)); markDirty(); showToast('Style applied!', 'success') } }

// ═══════════════════════════════════════════════════════════════════
// TABLE OPERATIONS
// ═══════════════════════════════════════════════════════════════════
function addTableRow() { if (selectedEl.value?.type === 'table') { const row = {}; selectedEl.value.columns.forEach(c => row[c] = ''); selectedEl.value.data.push(row); markDirty() } }
function addTableColumn() { if (selectedEl.value?.type === 'table') { const col = `Col ${selectedEl.value.columns.length + 1}`; selectedEl.value.columns.push(col); selectedEl.value.data.forEach(r => r[col] = ''); markDirty() } }
function removeTableRow() { if (selectedEl.value?.type === 'table' && selectedEl.value.data.length > 1) { selectedEl.value.data.pop(); markDirty() } }
function removeTableColumn() { if (selectedEl.value?.type === 'table' && selectedEl.value.columns.length > 1) { const col = selectedEl.value.columns.pop(); selectedEl.value.data.forEach(r => delete r[col]); markDirty() } }

// ═══════════════════════════════════════════════════════════════════
// CHART OPERATIONS
// ═══════════════════════════════════════════════════════════════════
function setChartLabels(labels) { if (selectedEl.value?.chartData) { selectedEl.value.chartData.labels = labels; markDirty() } }
function setChartValues(values) { if (selectedEl.value?.chartData) { selectedEl.value.chartData.values = values; markDirty() } }

// ═══════════════════════════════════════════════════════════════════
// TIMELINE / CHECKLIST / STATS
// ═══════════════════════════════════════════════════════════════════
function addTimelineItem() { if (selectedEl.value?.type === 'timeline') { if (!selectedEl.value.items) selectedEl.value.items = []; selectedEl.value.items.push({ date: '', label: '', desc: '' }); markDirty() } }
function removeTimelineItem(idx) { if (selectedEl.value?.type === 'timeline') { selectedEl.value.items.splice(idx, 1); markDirty() } }
function addChecklistItem() { if (selectedEl.value?.type === 'checklist') { if (!selectedEl.value.items) selectedEl.value.items = []; selectedEl.value.items.push({ text: 'New item', checked: false }); markDirty() } }
function removeChecklistItem(idx) { if (selectedEl.value?.type === 'checklist') { selectedEl.value.items.splice(idx, 1); markDirty() } }
function addStatItem() { if (selectedEl.value?.type === 'stat-row') { if (!selectedEl.value.stats) selectedEl.value.stats = []; selectedEl.value.stats.push({ value: '0', label: 'Metric' }); markDirty() } }
function removeStatItem(idx) { if (selectedEl.value?.type === 'stat-row') { selectedEl.value.stats.splice(idx, 1); markDirty() } }

// ═══════════════════════════════════════════════════════════════════
// PAGE OPERATIONS
// ═══════════════════════════════════════════════════════════════════
function goToPage(idx) { selectedElIdx.value = null; selectedEls.value = []; editingElIdx.value = null; currentPage.value = Math.max(0, Math.min(idx, report.content.length - 1)) }
function addPage() { pushUndo(); report.content.push({ id: uid(), label: `Page ${report.content.length + 1}`, elements: [] }); goToPage(report.content.length - 1); markDirty() }
function duplicatePage(idx) { pushUndo(); const copy = JSON.parse(JSON.stringify(report.content[idx])); copy.id = uid(); copy.label = (copy.label || `Page ${idx + 1}`) + ' (Copy)'; copy.elements = copy.elements.map(el => ({ ...el, id: uid() })); report.content.splice(idx + 1, 0, copy); goToPage(idx + 1); markDirty() }
function deletePage(idx) { if (report.content.length <= 1) { showToast('Cannot delete the only page', 'error'); return }; pushUndo(); report.content.splice(idx, 1); if (currentPage.value >= report.content.length) currentPage.value = report.content.length - 1; markDirty() }
function renamePage(idx, label) { report.content[idx].label = label; markDirty() }

// ═══════════════════════════════════════════════════════════════════
// DRAG & RESIZE
// ═══════════════════════════════════════════════════════════════════
function onElDragStart(e, def) { e.dataTransfer.setData('el-def', JSON.stringify(def)); isDraggingEl.value = true }
function onCanvasDrop({ def, x, y }) { addElement(def, x, y); isDraggingEl.value = false; dropTargetPage.value = null }

function onElementMouseDown({ event, pageIndex, elementIndex }) {
  if (event.button !== 0) return
  const el = report.content[pageIndex].elements[elementIndex]
  if (!el || el.locked) return
  if (event.shiftKey) {
    if (selectedEls.value.includes(elementIndex)) selectedEls.value = selectedEls.value.filter(i => i !== elementIndex)
    else selectedEls.value = [...selectedEls.value, elementIndex]
  } else { selectElementByIdx(elementIndex) }
  const scale = zoom.value / 100; const startX = event.clientX; const startY = event.clientY
  const els = (selectedEls.value.length > 1 ? selectedEls.value : [elementIndex]).map(i => ({ idx: i, ox: report.content[pageIndex].elements[i].position.x, oy: report.content[pageIndex].elements[i].position.y }))
  let moved = false
  const onMove = (ev) => {
    const dx = (ev.clientX - startX) / scale; const dy = (ev.clientY - startY) / scale
    if (!moved && (Math.abs(dx) + Math.abs(dy)) > 2) { pushUndo(); moved = true }
    els.forEach(({ idx: i, ox, oy }) => {
      const elem = report.content[pageIndex].elements[i]
      if (!elem) return
      let nx = ox + dx; let ny = oy + dy
      if (snapToGrid.value) { nx = Math.round(nx / 10) * 10; ny = Math.round(ny / 10) * 10 }
      elem.position.x = Math.max(0, nx); elem.position.y = Math.max(0, ny)
    })
  }
  const onUp = () => { document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); if (moved) markDirty() }
  document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp)
}

function startResize({ event, pageIndex, elementIndex, handle }) {
  event.stopPropagation(); event.preventDefault()
  const el = report.content[pageIndex].elements[elementIndex]
  if (!el) return
  const scale = zoom.value / 100; const startX = event.clientX; const startY = event.clientY
  const ow = el.styles.width; const oh = el.styles.height; const ox = el.position.x; const oy = el.position.y
  pushUndo()
  const onMove = (ev) => {
    const dx = (ev.clientX - startX) / scale; const dy = (ev.clientY - startY) / scale; const MIN = 20
    if (handle.includes('e')) el.styles.width = Math.max(MIN, ow + dx)
    if (handle.includes('s')) el.styles.height = Math.max(MIN, oh + dy)
    if (handle.includes('w')) { el.styles.width = Math.max(MIN, ow - dx); el.position.x = ox + (ow - el.styles.width) }
    if (handle.includes('n')) { el.styles.height = Math.max(MIN, oh - dy); el.position.y = oy + (oh - el.styles.height) }
  }
  const onUp = () => { document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); markDirty() }
  document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp)
}

function startRotate({ event, pageIndex, elementIndex }) {
  event.stopPropagation(); event.preventDefault()
  const el = report.content[pageIndex].elements[elementIndex]
  if (!el) return
  const rect = event.target.closest('.report-page')?.getBoundingClientRect()
  if (!rect) return
  const scale = zoom.value / 100; const cx = rect.left + (el.position.x + el.styles.width / 2) * scale; const cy = rect.top + (el.position.y + el.styles.height / 2) * scale
  pushUndo()
  const onMove = (ev) => { const angle = Math.atan2(ev.clientY - cy, ev.clientX - cx) * 180 / Math.PI + 90; el.styles.rotate = Math.round(angle) }
  const onUp = () => { document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); markDirty() }
  document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp)
}

// ═══════════════════════════════════════════════════════════════════
// RUBBER BAND
// ═══════════════════════════════════════════════════════════════════
function startRubberBand(e) { if (e.target.closest('.canvas-element') || e.target.closest('.page-navigation') || e.target.closest('.add-page-btn')) return; rubberBand.active = true; rubberBand.startX = e.clientX; rubberBand.startY = e.clientY }
function handleRubberBandMove(e) { if (!rubberBand.active) return; rubberBand.x = Math.min(e.clientX, rubberBand.startX); rubberBand.y = Math.min(e.clientY, rubberBand.startY); rubberBand.w = Math.abs(e.clientX - rubberBand.startX); rubberBand.h = Math.abs(e.clientY - rubberBand.startY) }
function endRubberBand() {
  if (!rubberBand.active) return; rubberBand.active = false
  if (rubberBand.w > 5 && rubberBand.h > 5) {
    const pageEl = document.querySelector('.report-page')
    if (!pageEl) return
    const rect = pageEl.getBoundingClientRect(); const scale = zoom.value / 100
    const bx = (rubberBand.x - rect.left) / scale; const by = (rubberBand.y - rect.top) / scale; const bw = rubberBand.w / scale; const bh = rubberBand.h / scale
    const selected = []
    currentPageElements.value.forEach((el, i) => { if (el.position.x < bx + bw && el.position.x + el.styles.width > bx && el.position.y < by + bh && el.position.y + el.styles.height > by) selected.push(i) })
    if (selected.length) { selectedEls.value = selected; selectedElIdx.value = selected[0] }
  }
}

// ═══════════════════════════════════════════════════════════════════
// ZOOM
// ═══════════════════════════════════════════════════════════════════
function zoomIn() { zoom.value = Math.min(zoom.value + 10, 400) }
function zoomOut() { zoom.value = Math.max(zoom.value - 10, 25) }
function handleZoomWheel(e) { if (e.deltaY < 0) zoomIn(); else zoomOut() }

// ═══════════════════════════════════════════════════════════════════
// CONTEXT MENU
// ═══════════════════════════════════════════════════════════════════
function showElContextMenu(e, pi, ei) {
  if (ei !== null && ei !== undefined) selectElementByIdx(ei)
  contextMenu.show = true; contextMenu.x = e.clientX; contextMenu.y = e.clientY
  contextMenu.items = [
    { label: 'Edit Content', icon: 'fa-solid fa-pen-to-square', action: () => startEditing({ pageIndex: pi, elementIndex: ei }) },
    { label: 'Duplicate', icon: 'fa-solid fa-clone', shortcut: 'Ctrl+D', action: duplicateSelected },
    { label: 'Copy', icon: 'fa-solid fa-copy', shortcut: 'Ctrl+C', action: copyElement },
    { label: 'Paste', icon: 'fa-solid fa-paste', shortcut: 'Ctrl+V', action: pasteElement, disabled: !clipboard.value },
    '---',
    { label: 'Bring to Front', icon: 'fa-solid fa-angles-up', action: bringToFront },
    { label: 'Send to Back', icon: 'fa-solid fa-angles-down', action: sendToBack },
    '---',
    { label: 'Copy Style', icon: 'fa-solid fa-paintbrush', action: stylePainterCopy },
    { label: 'Paste Style', icon: 'fa-solid fa-brush', action: stylePainterPaste, disabled: !stylePainterClipboard.value },
    '---',
    { label: ei !== null && currentPageElements.value[ei]?.locked ? 'Unlock' : 'Lock', icon: 'fa-solid fa-lock', action: lockElement },
    { label: 'Delete', icon: 'fa-solid fa-trash', shortcut: 'Del', danger: true, action: deleteSelected },
  ]
}

// ═══════════════════════════════════════════════════════════════════
// ALIGN TO PAGE
// ═══════════════════════════════════════════════════════════════════
function alignToPage(dir) {
  if (!selectedEl.value) return
  const dims = getPageDims(); const el = selectedEl.value; const m = settings.margin || 0
  if (dir === 'left') el.position.x = m
  else if (dir === 'right') el.position.x = dims.w - el.styles.width - m
  else if (dir === 'center-h') el.position.x = (dims.w - el.styles.width) / 2
  else if (dir === 'top') el.position.y = m
  else if (dir === 'bottom') el.position.y = dims.h - el.styles.height - m
  else if (dir === 'center-v') el.position.y = (dims.h - el.styles.height) / 2
  markDirty()
}

// ═══════════════════════════════════════════════════════════════════
// IMAGE HANDLING
// ═══════════════════════════════════════════════════════════════════
function triggerImageUpload(pi, ei) { currentImageTarget.value = { pi, ei }; fileInput.value?.click() }
function triggerImageReplace(el) { currentImageTarget.value = { el }; fileInput.value?.click() }
function handleImageUpload(files) {
  Array.from(files).forEach(file => {
    const reader = new FileReader()
    reader.onload = (ev) => {
      if (currentImageTarget.value?.el) { currentImageTarget.value.el.src = ev.target.result; currentImageTarget.value = null; markDirty(); return }
      const dims = getPageDims(); addElement({ type: 'image', w: 300, h: 200, src: ev.target.result }, dims.w / 2 - 150, dims.h / 3)
    }
    reader.readAsDataURL(file)
  })
}
function handleFilePick(e) { handleImageUpload(e.target.files); e.target.value = '' }

// ═══════════════════════════════════════════════════════════════════
// PAGE DBLCLICK
// ═══════════════════════════════════════════════════════════════════
function onPageDblClick({ event }) {
  const rect = event.target.closest('.report-page')?.getBoundingClientRect()
  if (!rect) return
  const scale = zoom.value / 100
  addElement({ type: 'text', w: 200, h: 40 }, (event.clientX - rect.left) / scale, (event.clientY - rect.top) / scale)
}

// ═══════════════════════════════════════════════════════════════════
// STATUS CYCLE
// ═══════════════════════════════════════════════════════════════════
async function cycleStatus() {
  const statuses = ['draft', 'published', 'archived']
  const newStatus = statuses[(statuses.indexOf(report.status) + 1) % statuses.length]
  try {
    const res = await fetch(route('reports.status', report.slug), { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }, body: JSON.stringify({ status: newStatus }) })
    if (res.ok) { report.status = newStatus; showToast(`Report ${newStatus}`, 'success'); if (newStatus === 'published') showConfetti.value = true }
  } catch (err) { showToast('Failed to update status', 'error') }
}

// ═══════════════════════════════════════════════════════════════════
// EXPORT & SHARE
// ═══════════════════════════════════════════════════════════════════
function exportFile(type) {
  const urls = { pdf: route('reports.download', report.slug), image: route('reports.export.image', report.slug), excel: route('reports.export.excel', report.slug), csv: route('reports.export.csv', report.slug) }
  window.open(urls[type], '_blank')
}
function previewReport() { window.open(route('reports.preview', report.slug), '_blank') }
async function shareReport() {
  try {
    const res = await fetch(route('reports.share', report.slug), { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' } })
    const data = await res.json()
    if (data.url) { await navigator.clipboard.writeText(data.url); showToast('Share link copied!', 'success') }
  } catch (err) { showToast('Could not generate link', 'error') }
}

// ═══════════════════════════════════════════════════════════════════
// TEMPLATE
// ═══════════════════════════════════════════════════════════════════
function applyQuickTemplate(tpl) {
  const match = tpl.gradient?.match(/#[a-f0-9]{6}/gi)
  if (match && match.length >= 2) { settings.background_color = match[1]; settings.primary_color = match[0]; markDirty(); showToast(`"${tpl.name}" applied`, 'success') }
}

// ═══════════════════════════════════════════════════════════════════
// AI
// ═══════════════════════════════════════════════════════════════════
function insertAiContent(content) {
  const dims = getPageDims(); const el = createElement({ type: 'text', w: 350, h: 120 }, dims.w / 2 - 175, dims.h / 3)
  el.content = content; currentPageElements.value.push(el); selectedElIdx.value = currentPageElements.value.length - 1; markDirty()
}
function insertAiChart(data) {
  const dims = getPageDims(); const el = createElement({ type: data.suggested_chart_type || 'bar-chart', w: 400, h: 280 }, dims.w / 2 - 200, dims.h / 3)
  el.chartData = { labels: data.labels, values: data.values }; el.chartTitle = data.title
  currentPageElements.value.push(el); selectedElIdx.value = currentPageElements.value.length - 1; markDirty()
}

// ═══════════════════════════════════════════════════════════════════
// COMMAND PALETTE
// ═══════════════════════════════════════════════════════════════════
function executeCommand(cmd) {
  const actions = {
    save: saveNow, undo, redo, delete: deleteSelected, duplicate: duplicateSelected,
    copy: copyElement, paste: pasteElement, 'select-all': () => { selectedEls.value = currentPageElements.value.map((_, i) => i); selectedElIdx.value = 0 },
    deselect: deselectAll, 'add-page': addPage, 'toggle-grid': () => showGrid.value = !showGrid.value,
    'toggle-dark': toggleDark, 'toggle-fullscreen': toggleFullscreen,
    'zoom-fit': () => zoom.value = 100, 'zoom-in': zoomIn, 'zoom-out': zoomOut, preview: previewReport,
  }
  if (actions[cmd]) actions[cmd]()
}

// ═══════════════════════════════════════════════════════════════════
// THEME
// ═══════════════════════════════════════════════════════════════════
function toggleDark() { isDark.value = !isDark.value; document.documentElement.classList.toggle('dark', isDark.value); localStorage.setItem('theme', isDark.value ? 'dark' : 'light') }
function toggleFullscreen() { if (!isFullscreen.value) { editorShell.value?.requestFullscreen?.(); isFullscreen.value = true } else { document.exitFullscreen?.(); isFullscreen.value = false } }
function completeOnboarding() { showOnboarding.value = false; localStorage.setItem('rg_onboarded', '1') }

// ═══════════════════════════════════════════════════════════════════
// KEYBOARD
// ═══════════════════════════════════════════════════════════════════
function handleKeyboard(e) {
  const ctrl = e.ctrlKey || e.metaKey
  const editable = e.target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)
  if (ctrl && e.key === 'k') { e.preventDefault(); showCommandPalette.value = !showCommandPalette.value; return }
  if (ctrl && e.key === '/' && !editable) { e.preventDefault(); showShortcuts.value = !showShortcuts.value; return }
  if (ctrl && e.key === 's') { e.preventDefault(); saveNow(); return }
  if (ctrl && e.key === 'z') { e.preventDefault(); undo(); return }
  if (ctrl && e.key === 'y') { e.preventDefault(); redo(); return }
  if (ctrl && e.key === 'c' && !editable) { e.preventDefault(); copyElement(); return }
  if (ctrl && e.key === 'v' && !editable) { e.preventDefault(); pasteElement(); return }
  if (ctrl && e.key === 'd' && !editable) { e.preventDefault(); duplicateSelected(); return }
  if (ctrl && e.key === 'a' && !editable) { e.preventDefault(); selectedEls.value = currentPageElements.value.map((_, i) => i); selectedElIdx.value = 0; return }
  if ((e.key === 'Delete' || e.key === 'Backspace') && !editable) { e.preventDefault(); deleteSelected(); return }
  if (e.key === 'Escape') { deselectAll(); showCommandPalette.value = false; showShortcuts.value = false; contextMenu.show = false; return }
  if (e.key === 'F11') { e.preventDefault(); toggleFullscreen(); return }
  if (!editable && selectedEl.value) {
    const STEP = e.shiftKey ? 10 : 1
    if (e.key === 'ArrowLeft') { e.preventDefault(); selectedEl.value.position.x = Math.max(0, selectedEl.value.position.x - STEP); markDirty() }
    else if (e.key === 'ArrowRight') { e.preventDefault(); selectedEl.value.position.x += STEP; markDirty() }
    else if (e.key === 'ArrowUp') { e.preventDefault(); selectedEl.value.position.y = Math.max(0, selectedEl.value.position.y - STEP); markDirty() }
    else if (e.key === 'ArrowDown') { e.preventDefault(); selectedEl.value.position.y += STEP; markDirty() }
  }
  // Toggle Left Sidebar: Ctrl+Shift+L
if (ctrl && e.shiftKey && e.key === 'L') {
  e.preventDefault()
  // Need to expose this from LeftSidebar
  return
}

// Toggle Right Sidebar: Ctrl+Shift+R  
if (ctrl && e.shiftKey && e.key === 'R') {
  e.preventDefault()
  return
}
}

// ═══════════════════════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════════════════════
onMounted(() => {
  document.documentElement.classList.toggle('dark', isDark.value)
  pushUndo()
  autoSaveInterval = setInterval(() => { if (isDirty.value) saveNow() }, 30000)
  document.addEventListener('fullscreenchange', () => { isFullscreen.value = !!document.fullscreenElement })
})

onBeforeUnmount(() => {
  clearTimeout(saveTimer)
  clearInterval(autoSaveInterval)
  document.removeEventListener('fullscreenchange', () => {})
})
</script>

<style>
/* ═══ Global Editor Styles ──────────────────────────────────── */
.editor-shell {
  --bg-primary: #ffffff;
  --bg-secondary: #f8fafc;
  --bg-tertiary: #f1f5f9;
  --bg-panel: #ffffff;
  --border: #e2e8f0;
  --border-light: #f1f5f9;
  --border-hover: #cbd5e1;
  --text-primary: #0f172a;
  --text-secondary: #475569;
  --text-muted: #94a3b8;
  --accent: #6366f1;
  --accent-hover: #4f46e5;
  --accent-light: rgba(99,102,241,0.08);
  --accent-soft: rgba(99,102,241,0.15);
  --danger: #ef4444;
  --danger-light: rgba(239,68,68,0.08);
  --success: #10b981;
  --warning: #f59e0b;
  --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
  --shadow: 0 4px 12px rgba(0,0,0,0.08);
  --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
  --shadow-xl: 0 12px 40px rgba(0,0,0,0.16);
  
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  font-family: 'Inter', 'DM Sans', system-ui, -apple-system, sans-serif;
  background: var(--bg-tertiary);
  color: var(--text-primary);
  font-size: 13px;
  line-height: 1.5;
  outline: none;
  -webkit-font-smoothing: antialiased;
}

.editor-shell.dark {
  --bg-primary: #1a2236;
  --bg-secondary: #111827;
  --bg-tertiary: #0b1120;
  --bg-panel: #1a2236;
  --border: #263348;
  --border-light: #1e2a3d;
  --border-hover: #334155;
  --text-primary: #e2e8f0;
  --text-secondary: #94a3b8;
  --text-muted: #64748b;
  --accent: #818cf8;
  --accent-hover: #6366f1;
  --accent-light: rgba(129,140,248,0.1);
  --accent-soft: rgba(129,140,248,0.18);
  --danger: #f87171;
  --danger-light: rgba(248,113,113,0.1);
  --success: #34d399;
  --warning: #fbbf24;
  --shadow-xs: 0 1px 2px rgba(0,0,0,0.3);
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.4);
  --shadow: 0 4px 12px rgba(0,0,0,0.4);
  --shadow-lg: 0 8px 24px rgba(0,0,0,0.5);
  --shadow-xl: 0 12px 40px rgba(0,0,0,0.6);
}

.editor-shell.fullscreen {
  position: fixed;
  inset: 0;
  z-index: 9999;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
input, select, textarea, button { font-family: inherit; font-size: inherit; }
.hidden { display: none !important; }

.editor-body {
  flex: 1;
  display: flex;
  overflow: hidden;
  min-height: 0;
}
.panel-toggle {
  width: 28px !important;
  height: 28px !important;
  background: var(--accent) !important;
  color: #fff !important;
  font-size: 12px !important;
  opacity: 0.8;
}
.panel-toggle:hover {
  opacity: 1;
  transform: scale(1.1);
}

/* ── Scrollbar ─────────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }
::-webkit-scrollbar-corner { background: transparent; }

/* ── Selection ─────────────────────────────────────────────── */
::selection { background: var(--accent); color: #fff; }

/* ── Focus Visible ─────────────────────────────────────────── */
*:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 768px) {
  .editor-shell { font-size: 12px; }
}
</style>