<!--
  Editor.vue — Main orchestrator for the report editor
  ══════════════════════════════════════════════════════════════════════
  Changes in this version (Part 2):
  • usePresence composable wired — presenceEditors fed to TopToolbar
  • Presentation mode completely removed (state, template, handler)
  • Share button handler removed
  • Export kept to PDF + Print ONLY (Excel/CSV/Image handlers removed)
  • addPageBefore(pi) — insert page before index pi
  • addPageAfter(pi)  — insert page after index pi
  • movePageUp(pi)    — swap page[pi] with page[pi-1]
  • movePageDown(pi)  — swap page[pi] with page[pi+1]
  • duplicatePage(pi) + deletePage(pi) wired from EditorCanvas events
  • onZoomWheel — handles the non-passive ctrl+wheel event from canvas
  • Every new page inherits ALL current report settings (theme, margins,
    header/footer, watermark, font, page-size, page-numbers, etc.)
  • Ctrl+Alt+* shortcuts — zero browser conflicts
  • Auto-save with 1.5s debounce + version save every 5 min
  • Report styles fully isolated inside .editor-shell (not affected by
    app or system theme)
  ══════════════════════════════════════════════════════════════════════
-->
<template>
  <div class="editor-shell" :class="{ 'editor-dark': isDark, 'editor-fullscreen': isFullscreen }"
    @keydown="handleGlobalKey" tabindex="-1" ref="shellRef">
    <!-- ══ TOP TOOLBAR ══════════════════════════════════════════════ -->
    <TopToolbar :report="report" :settings="settings" :is-dirty="isDirty" :is-saving="isSaving"
      :last-saved="lastSavedLabel" :zoom="zoom" :can-undo="historyIndex > 0"
      :can-redo="historyIndex < history.length - 1" :selected-el="primarySelectedEl" :show-grid="showGrid"
      :snap-to-grid="snapToGrid" :show-rulers="showRulers" :is-dark="isDark" :is-fullscreen="isFullscreen"
      :show-a-i="showAI" :left-collapsed="leftCollapsed" :right-collapsed="rightCollapsed"
      :presence-editors="presenceEditors" @update:title="report.title = $event; markDirty()" @save="saveNow"
      @undo="undo" @redo="redo" @zoom-in="zoomIn" @zoom-out="zoomOut" @zoom-reset="zoom = 100"
      @toggle-grid="showGrid = !showGrid" @toggle-snap="snapToGrid = !snapToGrid"
      @toggle-rulers="showRulers = !showRulers" @toggle-measure="measureMode = !measureMode" @toggle-dark="toggleDark"
      @toggle-fullscreen="toggleFullscreen" @toggle-ai="showAI = !showAI" @toggle-command="showCommand = !showCommand"
      @toggle-find="showFind = !showFind" @preview="openPreview" @print-preview="doPrint" @export-pdf="exportPdf"
      @change-status="cycleStatus" @apply-style="applyStyleToSelected" @toggle-fmt="toggleFmtOnSelected"
      @delete-el="deleteSelected" @duplicate-el="duplicateSelected" @lock-el="toggleLockSelected"
      @bring-front="bringFront" @send-back="sendBack" @toggle-left-panel="leftCollapsed = !leftCollapsed"
      @toggle-right-panel="rightCollapsed = !rightCollapsed" />

    <!-- ══ EDITOR BODY ══════════════════════════════════════════════ -->
    <div class="editor-body">

      <!-- Left Sidebar -->
      <Transition name="sidebar-slide-left">
        <LeftSidebar v-if="!leftCollapsed" :report="report" :settings="settings" :current-page="currentPage"
          :is-dark="isDark" @add-element="addElementFromSidebar" @select-page="goToPage" @add-page="addPage"
          @add-page-before="addPageBefore" @add-page-after="addPageAfter" @duplicate-page="duplicatePage"
          @delete-page="deletePage" @move-page-up="movePageUp" @move-page-down="movePageDown"
          @reorder-pages="reorderPages" @update-settings="updateSettings" @select-layer-element="selectLayerElement"
          @history-jump="historyJump" @image-upload="handleMediaUpload" @image-search="handleImageSearch" />
      </Transition>

      <!-- Canvas -->
      <EditorCanvas :report="report" :settings="settings" :current-page="currentPage" :selected-el-idx="selectedElIdx"
        :selected-els="selectedEls" :editing-el-idx="editingElIdx" :zoom="zoom" :show-grid="showGrid"
        :snap-to-grid="snapToGrid" :show-rulers="showRulers" :is-dark="isDark" :measure-mode="measureMode"
        @select-element="onSelectElement" @deselect-all="deselectAll" @select-page="goToPage"
        @add-element="addElementAtPos" @add-page="addPage" @add-page-before="addPageBefore"
        @add-page-after="addPageAfter" @move-page-up="movePageUp" @move-page-down="movePageDown"
        @duplicate-page="duplicatePage" @delete-page="deletePage" @start-editing="startEditing"
        @stop-editing="stopEditing" @update-text-content="updateTextContent" @element-mouse-down="onElMouseDown"
        @resize-start="onResizeStart" @rotate-start="onRotateStart" @canvas-drop="onCanvasDrop"
        @rubber-band-start="() => { }" @zoom-wheel="onZoomWheel" @page-dblclick="onPageDblClick"
        @context-menu="showContextMenu" @image-upload="triggerImageUpload" @image-replace="triggerImageReplace"
        @go-to-page="goToPage" @mark-dirty="markDirty" @zoom-reset="zoom = 100" @add-table-row="addTableRow"
        @add-table-col="addTableCol" @remove-table-row="removeTableRow" @remove-table-col="removeTableCol" />

      <!-- Right Sidebar -->
      <Transition name="sidebar-slide-right">
        <RightSidebar v-if="!rightCollapsed && primarySelectedEl" :el="primarySelectedEl" :settings="settings"
          :is-dark="isDark" :page-index="currentPage" :el-index="selectedElIdx" @update:el-prop="updateElProp"
          @apply-style="applyStyleToSelected" @delete-el="deleteSelected" @duplicate-el="duplicateSelected"
          @lock-el="toggleLockSelected" @bring-front="bringFront" @send-back="sendBack" @add-table-row="addTableRow"
          @add-table-col="addTableCol" @remove-table-row="removeTableRow" @remove-table-col="removeTableCol" />
      </Transition>
    </div>

    <!-- ══ STATUS BAR ═══════════════════════════════════════════════ -->
    <StatusBar :report="report" :current-page="currentPage" :total-pages="report.content.length"
      :selected-count="selectedEls.length" :zoom="zoom" :is-dark="isDark" :word-count="wordCount" @zoom-in="zoomIn"
      @zoom-out="zoomOut" @zoom-reset="zoom = 100" @go-to-page="goToPage" />

    <!-- ══ OVERLAYS ═════════════════════════════════════════════════ -->

    <!-- AI Panel -->
    <AiPanel v-if="showAI" :is-dark="isDark" :report="report" :selected-el="primarySelectedEl" :settings="settings"
      @close="showAI = false" @insert-content="insertAiContent" @apply-suggestion="applyAiSuggestion" />

    <!-- Command Palette -->
    <CommandPalette v-if="showCommand" :is-dark="isDark" :report="report" :settings="settings"
      @close="showCommand = false" @execute="executeCommand" />

    <!-- Find & Replace -->
    <Teleport to="body">
      <div v-if="showFind" class="find-replace-overlay" :class="{ dark: isDark }">
        <div class="fro-box">
          <div class="fro-title">Find &amp; Replace</div>
          <div class="fro-row">
            <input v-model="findText" placeholder="Find…" class="fro-input" @keydown.enter="doFind" />
            <input v-model="replaceText" placeholder="Replace…" class="fro-input" />
          </div>
          <div class="fro-actions">
            <button class="fro-btn" @click="doFind">Find</button>
            <button class="fro-btn fro-btn--primary" @click="doReplace">Replace All</button>
            <button class="fro-btn" @click="showFind = false">Close</button>
          </div>
          <div v-if="findResult" class="fro-result">{{ findResult }}</div>
        </div>
      </div>
    </Teleport>

    <!-- Context Menu -->
    <ContextMenu v-if="ctxMenu.show" :x="ctxMenu.x" :y="ctxMenu.y" :page-index="ctxMenu.pi" :el-index="ctxMenu.ei"
      :el="ctxMenu.el" :is-dark="isDark" @close="ctxMenu.show = false" @duplicate="duplicateSelected"
      @delete="deleteSelected" @lock="toggleLockSelected" @bring-front="bringFront" @send-back="sendBack"
      @copy-style="copyStyle" @paste-style="pasteStyle" @add-page="addPage"
      @duplicate-page="() => duplicatePage(currentPage)" @delete-page="() => deletePage(currentPage)" />

    <!-- Shortcut overlay -->
    <ShortcutOverlay v-if="showShortcuts" @close="showShortcuts = false" :is-dark="isDark" />

    <!-- Toast container -->
    <ToastContainer ref="toastRef" />

    <!-- Hidden image upload input -->
    <input ref="imgInputRef" type="file" accept="image/*" class="sr-only" @change="onImageFileSelected"
      aria-label="Upload image" />

  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { usePresence } from '@/Composables/usePresence'

import TopToolbar from './Components/TopToolbar.vue'
import LeftSidebar from './Components/LeftSidebar.vue'
import RightSidebar from './Components/RightSidebar.vue'
import EditorCanvas from './Components/EditorCanvas.vue'
import StatusBar from './Components/StatusBar.vue'
import AiPanel from './Components/AiPanel.vue'
import CommandPalette from './Components/CommandPalette.vue'
import ContextMenu from './Components/ContextMenu.vue'
import ShortcutOverlay from './Components/ShortcutOverlay.vue'
import ToastContainer from './Components/ToastContainer.vue'

// ── Inertia props ───────────────────────────────────────────────────
const { props: iProps } = usePage()
const authUser = computed(() => iProps.value.auth?.user)

const props = defineProps({
  report: { type: Object, required: true },
  template: { type: Object, default: null },
})

// ── Presence ────────────────────────────────────────────────────────
const { editors: presenceEditors } = usePresence(
  props.report.slug,
  authUser.value?.id
)

// ── Core state ──────────────────────────────────────────────────────
// Deep-clone so we own the mutable copy
const report = reactive(JSON.parse(JSON.stringify(props.report)))

const settings = reactive(
  typeof report.settings === 'string'
    ? JSON.parse(report.settings)
    : (report.settings || getDefaultSettings())
)

// Ensure pages array exists
if (!report.content || !Array.isArray(report.content)) {
  report.content = [newPage()]
}

// ── UI state ────────────────────────────────────────────────────────
const currentPage = ref(0)
const selectedEls = ref([])        // array of element indices on currentPage
const selectedElIdx = ref(null)      // primary selected (first in selectedEls)
const editingElIdx = ref(null)

const zoom = ref(100)
const showGrid = ref(true)
const snapToGrid = ref(true)
const showRulers = ref(false)
const measureMode = ref(false)

const isDark = ref(false)
const isFullscreen = ref(false)
const showAI = ref(false)
const showCommand = ref(false)
const showFind = ref(false)
const showShortcuts = ref(false)
const leftCollapsed = ref(false)
const rightCollapsed = ref(false)

const isDirty = ref(false)
const isSaving = ref(false)
const lastSaved = ref(null)

// Find & Replace
const findText = ref('')
const replaceText = ref('')
const findResult = ref('')

// Context Menu
const ctxMenu = reactive({ show: false, x: 0, y: 0, pi: null, ei: null, el: null })

// Clipboard
let clipboardEl = null
let clipboardStyle = null
let imgUploadTarget = null     // { pi, ei } or 'new'

// Refs
const shellRef = ref(null)
const toastRef = ref(null)
const imgInputRef = ref(null)

// ── Undo/redo history ───────────────────────────────────────────────
const MAX_HISTORY = 80
const history = ref([])
const historyIndex = ref(-1)

function pushHistory() {
  // Trim forward history
  if (historyIndex.value < history.value.length - 1) {
    history.value = history.value.slice(0, historyIndex.value + 1)
  }
  history.value.push(JSON.stringify({ content: report.content, settings: { ...settings } }))
  if (history.value.length > MAX_HISTORY) history.value.shift()
  historyIndex.value = history.value.length - 1
}

function undo() {
  if (historyIndex.value <= 0) return
  historyIndex.value--
  applySnapshot(history.value[historyIndex.value])
  toast('Undo', 'info')
}

function redo() {
  if (historyIndex.value >= history.value.length - 1) return
  historyIndex.value++
  applySnapshot(history.value[historyIndex.value])
  toast('Redo', 'info')
}

function applySnapshot(snap) {
  const s = JSON.parse(snap)
  report.content = s.content
  Object.assign(settings, s.settings)
}

function historyJump(idx) {
  if (idx < 0 || idx >= history.value.length) return
  historyIndex.value = idx
  applySnapshot(history.value[idx])
}

// ── Dirty tracking / auto-save ──────────────────────────────────────
let dirtyTimer = null
let versionTimer = null

function markDirty() {
  isDirty.value = true
  clearTimeout(dirtyTimer)
  dirtyTimer = setTimeout(() => saveNow(), 1500)
}

watch(
  () => JSON.stringify({ content: report.content, settings }),
  (cur, prev) => { if (cur !== prev) pushHistory() },
  { deep: true }
)

async function saveNow() {
  if (!isDirty.value) return
  isSaving.value = true
  try {
    await new Promise((resolve, reject) => {
      router.put(
        route('reports.update', report.slug),
        {
          title: report.title,
          status: report.status,
          settings: settings,
          content: report.content,
        },
        {
          preserveState: true,
          preserveScroll: true,
          onSuccess: () => {
            isDirty.value = false
            lastSaved.value = new Date()
            resolve()
          },
          onError: (e) => reject(e),
        }
      )
    })
  } catch (e) {
    toast('Save failed — check your connection', 'error')
  } finally {
    isSaving.value = false
  }
}

async function saveVersion() {
  try {
    await window.axios?.post(route('reports.versions.store', report.slug), {
      content: report.content,
      settings: settings,
    })
  } catch { /* version saves are best-effort */ }
}

// Save a version every 5 minutes of active editing
onMounted(() => {
  pushHistory()            // initial snapshot
  loadPreferences()
  registerShortcuts()

  versionTimer = setInterval(() => {
    if (isDirty.value) saveVersion()
  }, 5 * 60 * 1000)
})

onBeforeUnmount(() => {
  clearTimeout(dirtyTimer)
  clearInterval(versionTimer)
  document.removeEventListener('keydown', onGlobalKeyDown)
  document.removeEventListener('fullscreenchange', onFscChange)
  if (isDirty.value) saveNow()
})

// ── Computed ────────────────────────────────────────────────────────
const primarySelectedEl = computed(() => {
  if (selectedElIdx.value === null) return null
  return report.content[currentPage.value]?.elements?.[selectedElIdx.value] ?? null
})

const lastSavedLabel = computed(() => {
  if (!lastSaved.value) return ''
  const diff = Math.round((Date.now() - lastSaved.value) / 1000)
  if (diff < 60) return `${diff}s ago`
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  return lastSaved.value.toLocaleTimeString()
})

const wordCount = computed(() => {
  let count = 0
  report.content.forEach(page => {
    page.elements?.forEach(el => {
      if (el.content) count += String(el.content).replace(/<[^>]+>/g, '').split(/\s+/).filter(Boolean).length
    })
  })
  return count
})

// ── Default helpers ──────────────────────────────────────────────────
function getDefaultSettings() {
  return {
    page_size: 'A4', orientation: 'portrait',
    margin: 40, padding: 20,
    background_color: '#ffffff', text_color: '#1e293b',
    primary_color: '#6366f1', accent_color: '#c9a84c',
    font_family: 'DM Sans', font_size: 14,
    show_header: false, show_footer: true,
    header_text: '', header_height: 50,
    header_color: '#1e293b', header_text_color: '#ffffff',
    footer_left: '', footer_right: 'Page {n} of {total}',
    footer_color: '#94a3b8',
    show_page_numbers: true, page_number_style: 'decimal',
    page_number_position: 'footer-center',
    watermark: '', watermark_opacity: 8, watermark_rotate: -30,
    watermark_color: '#94a3b8',
    rtl: false, line_height: 1.5,
  }
}

function newPage(label) {
  return {
    id: `page-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
    label: label || '',
    elements: [],
  }
}

// ── Navigation ───────────────────────────────────────────────────────
function goToPage(idx) {
  if (idx < 0 || idx >= report.content.length) return
  currentPage.value = idx
  deselectAll()
  // Scroll canvas to that page
  nextTick(() => {
    const el = document.querySelector(`[data-page-index="${idx}"]`)
    el?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  })
}

// ── Page management ─────────────────────────────────────────────────
/**
 * Add a blank page at the end
 */
function addPage() {
  pushHistory()
  report.content.push(newPage())
  goToPage(report.content.length - 1)
  markDirty()
  toast('Page added', 'success')
}

/**
 * Insert a blank page BEFORE index pi
 */
function addPageBefore(pi) {
  pushHistory()
  report.content.splice(pi, 0, newPage())
  goToPage(pi)
  markDirty()
  toast(`Page inserted before page ${pi + 1}`, 'success')
}

/**
 * Insert a blank page AFTER index pi
 */
function addPageAfter(pi) {
  pushHistory()
  const insertAt = pi + 1
  report.content.splice(insertAt, 0, newPage())
  goToPage(insertAt)
  markDirty()
  toast(`Page inserted after page ${pi + 1}`, 'success')
}

/**
 * Deep-duplicate page at pi and insert right after it
 */
function duplicatePage(pi) {
  pushHistory()
  const src = JSON.parse(JSON.stringify(report.content[pi]))
  src.id = `page-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`
  src.label = (src.label ? src.label + ' copy' : `Page ${pi + 1} copy`)
  // Give cloned elements fresh IDs
  src.elements = (src.elements || []).map(el => ({
    ...el,
    id: `el-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
  }))
  report.content.splice(pi + 1, 0, src)
  goToPage(pi + 1)
  markDirty()
  toast('Page duplicated', 'success')
}

/**
 * Delete page at pi (not allowed if only 1 page)
 */
function deletePage(pi) {
  if (report.content.length <= 1) {
    toast('Cannot delete the only page', 'warning')
    return
  }
  pushHistory()
  report.content.splice(pi, 1)
  goToPage(Math.min(pi, report.content.length - 1))
  markDirty()
  toast('Page deleted', 'info')
}

/**
 * Move page at pi up one slot (swap with pi-1)
 */
function movePageUp(pi) {
  if (pi === 0) return
  pushHistory()
  const tmp = report.content[pi]
  report.content[pi] = report.content[pi - 1]
  report.content[pi - 1] = tmp
  goToPage(pi - 1)
  markDirty()
  toast('Page moved up', 'info')
}

/**
 * Move page at pi down one slot (swap with pi+1)
 */
function movePageDown(pi) {
  if (pi >= report.content.length - 1) return
  pushHistory()
  const tmp = report.content[pi]
  report.content[pi] = report.content[pi + 1]
  report.content[pi + 1] = tmp
  goToPage(pi + 1)
  markDirty()
  toast('Page moved down', 'info')
}

/**
 * Reorder pages (from left-sidebar drag-and-drop)
 * newOrder: array of old indices in the desired new order
 */
function reorderPages(newOrder) {
  pushHistory()
  const reordered = newOrder.map(i => report.content[i])
  report.content.splice(0, report.content.length, ...reordered)
  markDirty()
  toast('Pages reordered', 'info')
}

// ── Element management ──────────────────────────────────────────────
function onSelectElement(indices) {
  selectedEls.value = Array.isArray(indices) ? indices : [indices]
  selectedElIdx.value = selectedEls.value[0] ?? null
}

function deselectAll() {
  selectedEls.value = []
  selectedElIdx.value = null
  editingElIdx.value = null
}

function selectLayerElement(pi, ei) {
  goToPage(pi)
  onSelectElement([ei])
  nextTick(() => {
    const el = document.querySelector(`[data-el-id="${report.content[pi]?.elements?.[ei]?.id}"]`)
    el?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  })
}

function startEditing({ pageIndex, elementIndex }) {
  goToPage(pageIndex)
  editingElIdx.value = elementIndex
  selectedElIdx.value = elementIndex
  selectedEls.value = [elementIndex]
}

function stopEditing() { editingElIdx.value = null }

function updateTextContent({ pageIndex, elementIndex, content }) {
  const el = report.content[pageIndex]?.elements?.[elementIndex]
  if (el) { el.content = content; markDirty() }
}

/**
 * Add element from sidebar (palette drag-start drops here)
 */
function addElementFromSidebar({ def, pageIndex }) {
  const pi = pageIndex ?? currentPage.value
  const el = buildElement(def, { x: 80, y: 80 })
  pushHistory()
  report.content[pi].elements.push(el)
  onSelectElement([report.content[pi].elements.length - 1])
  markDirty()
}

/**
 * Add element at specific canvas position (from drop event)
 */
function addElementAtPos({ def, pageIndex, x, y }) {
  const pi = pageIndex ?? currentPage.value
  const el = buildElement(def, { x, y })
  pushHistory()
  report.content[pi].elements.push(el)
  goToPage(pi)
  onSelectElement([report.content[pi].elements.length - 1])
  markDirty()
}

function onCanvasDrop({ def, x, y }) {
  addElementAtPos({ def, pageIndex: currentPage.value, x, y })
}

function buildElement(def, pos) {
  return {
    id: `el-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
    type: def.type,
    content: def.content || def.defaultContent || '',
    label: def.label || '',
    locked: false,
    visible: true,
    position: { x: pos.x, y: pos.y },
    styles: {
      width: def.w || 200,
      height: def.h || 80,
      fontFamily: settings.font_family || 'DM Sans',
      fontSize: def.fontSize || settings.font_size || 14,
      fontWeight: def.fontWeight || '400',
      color: def.color || settings.text_color || '#1e293b',
      backgroundColor: def.bg || 'transparent',
      borderRadius: 0,
      opacity: 100,
      zIndex: (report.content[currentPage.value]?.elements?.length || 0) + 1,
      ...(def.styles || {}),
    },
    // Type-specific defaults
    ...(def.defaults || {}),
  }
}

function deleteSelected() {
  if (selectedEls.value.length === 0) return
  pushHistory()
  // Remove in descending order to preserve indices
  const sorted = [...selectedEls.value].sort((a, b) => b - a)
  sorted.forEach(idx => report.content[currentPage.value].elements.splice(idx, 1))
  deselectAll()
  markDirty()
  toast('Deleted', 'info')
}

function duplicateSelected() {
  if (selectedElIdx.value === null) return
  const src = report.content[currentPage.value].elements[selectedElIdx.value]
  if (!src) return
  pushHistory()
  const clone = JSON.parse(JSON.stringify(src))
  clone.id = `el-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`
  clone.position.x = (clone.position.x || 0) + 20
  clone.position.y = (clone.position.y || 0) + 20
  report.content[currentPage.value].elements.push(clone)
  onSelectElement([report.content[currentPage.value].elements.length - 1])
  markDirty()
  toast('Duplicated', 'success')
}

function toggleLockSelected() {
  const el = primarySelectedEl.value
  if (!el) return
  el.locked = !el.locked
  markDirty()
}

function bringFront() {
  const els = report.content[currentPage.value].elements
  if (selectedElIdx.value === null || selectedElIdx.value >= els.length - 1) return
  pushHistory()
  const [item] = els.splice(selectedElIdx.value, 1)
  els.push(item)
  onSelectElement([els.length - 1])
  markDirty()
}

function sendBack() {
  const els = report.content[currentPage.value].elements
  if (!selectedElIdx.value) return
  pushHistory()
  const [item] = els.splice(selectedElIdx.value, 1)
  els.unshift(item)
  onSelectElement([0])
  markDirty()
}

function updateElProp(prop, value) {
  const el = primarySelectedEl.value
  if (!el) return
  // Support dot-path e.g. 'styles.fontSize'
  const parts = prop.split('.')
  if (parts.length === 2) {
    if (!el[parts[0]]) el[parts[0]] = {}
    el[parts[0]][parts[1]] = value
  } else {
    el[prop] = value
  }
  markDirty()
}

function applyStyleToSelected(prop, value) {
  selectedEls.value.forEach(idx => {
    const el = report.content[currentPage.value].elements[idx]
    if (el) {
      if (!el.styles) el.styles = {}
      el.styles[prop] = value
    }
  })
  markDirty()
}

function toggleFmtOnSelected(prop, onVal, offVal) {
  const el = primarySelectedEl.value
  if (!el) return
  if (!el.styles) el.styles = {}
  el.styles[prop] = el.styles[prop] === onVal ? offVal : onVal
  markDirty()
}

// ── Style copy/paste ─────────────────────────────────────────────────
function copyStyle() {
  const el = primarySelectedEl.value
  if (!el) return
  clipboardStyle = JSON.parse(JSON.stringify(el.styles || {}))
  toast('Style copied', 'info')
}

function pasteStyle() {
  if (!clipboardStyle) return
  const el = primarySelectedEl.value
  if (!el) return
  // preserve position/size
  const { width, height } = el.styles || {}
  el.styles = { ...clipboardStyle, width, height }
  markDirty()
  toast('Style pasted', 'success')
}

// ── Table helpers ────────────────────────────────────────────────────
function addTableRow() {
  const el = primarySelectedEl.value
  if (!el || el.type !== 'table') return
  const row = {}
    ; (el.columns || []).forEach(c => { row[c] = '' })
    ; (el.data = el.data || []).push(row)
  markDirty()
}

function addTableCol() {
  const el = primarySelectedEl.value
  if (!el || el.type !== 'table') return
  const name = `Col ${(el.columns || []).length + 1}`
    ; (el.columns = el.columns || []).push(name)
    ; (el.data || []).forEach(r => { r[name] = '' })
  markDirty()
}

function removeTableRow() {
  const el = primarySelectedEl.value
  if (!el || el.type !== 'table' || (el.data || []).length <= 1) return
  el.data.pop()
  markDirty()
}

function removeTableCol() {
  const el = primarySelectedEl.value
  if (!el || el.type !== 'table' || (el.columns || []).length <= 1) return
  const removed = el.columns.pop()
    ; (el.data || []).forEach(r => delete r[removed])
  markDirty()
}

// ── Settings ─────────────────────────────────────────────────────────
function updateSettings(patch) {
  Object.assign(settings, patch)
  // Persist to report.settings too so saveNow sends it
  report.settings = { ...settings }
  markDirty()
}

// ── Canvas events ─────────────────────────────────────────────────────
function onElMouseDown() { /* handled inside EditorCanvas */ }
function onResizeStart() { /* handled inside EditorCanvas */ }
function onRotateStart() { /* handled inside EditorCanvas */ }

function onPageDblClick({ pageIndex }) {
  goToPage(pageIndex)
  stopEditing()
}

function showContextMenu(event, pi, ei) {
  ctxMenu.show = false
  nextTick(() => {
    ctxMenu.x = event.clientX
    ctxMenu.y = event.clientY
    ctxMenu.pi = pi
    ctxMenu.ei = ei
    ctxMenu.el = ei !== null && pi !== null
      ? report.content[pi]?.elements?.[ei]
      : null
    ctxMenu.show = true
  })
}

// ── Zoom ─────────────────────────────────────────────────────────────
const ZOOM_STEPS = [25, 33, 50, 67, 75, 80, 90, 100, 110, 125, 150, 175, 200, 250, 300]

function zoomIn() {
  const next = ZOOM_STEPS.find(z => z > zoom.value)
  zoom.value = next ?? 300
}

function zoomOut() {
  const prev = [...ZOOM_STEPS].reverse().find(z => z < zoom.value)
  zoom.value = prev ?? 25
}

/**
 * Called by EditorCanvas when a non-passive ctrl+wheel fires.
 * e.deltaY < 0 means scroll up = zoom in (matches browser convention).
 */
function onZoomWheel(e) {
  if (e.deltaY < 0) zoomIn()
  else zoomOut()
}

// ── Dark mode ─────────────────────────────────────────────────────────
function toggleDark() {
  isDark.value = !isDark.value
  localStorage.setItem('editor-dark', isDark.value ? '1' : '0')
}

// ── Fullscreen ────────────────────────────────────────────────────────
function toggleFullscreen() {
  if (!document.fullscreenElement) {
    shellRef.value?.requestFullscreen?.()
  } else {
    document.exitFullscreen?.()
  }
}

function onFscChange() {
  isFullscreen.value = !!document.fullscreenElement
}

// ── Export ────────────────────────────────────────────────────────────
function openPreview() {
  const url = route('reports.preview', report.slug)
  window.open(url, '_blank', 'noopener')
}

function doPrint() {
  const url = route('reports.preview', report.slug) + '?print=1'
  const win = window.open(url, '_blank', 'noopener')
  win?.addEventListener('load', () => win.print())
}

async function exportPdf() {
  toast('Generating PDF…', 'info')
  try {
    const res = await window.axios.post(
      route('reports.export-pdf', report.slug),
      { settings },
      { responseType: 'blob' }
    )
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = (report.title || 'report') + '.pdf'
    a.click()
    URL.revokeObjectURL(url)
    toast('PDF downloaded', 'success')
  } catch {
    toast('PDF export failed', 'error')
  }
}

function cycleStatus() {
  const cycle = ['draft', 'published', 'archived']
  const idx = cycle.indexOf(report.status)
  report.status = cycle[(idx + 1) % cycle.length]
  markDirty()
  toast(`Status: ${report.status}`, 'info')
}

// ── AI integration ────────────────────────────────────────────────────
function insertAiContent({ content, type }) {
  const el = buildElement(
    { type: type || 'text', content, w: 400, h: 120 },
    { x: 80, y: 80 }
  )
  pushHistory()
  report.content[currentPage.value].elements.push(el)
  onSelectElement([report.content[currentPage.value].elements.length - 1])
  markDirty()
}

function applyAiSuggestion({ prop, value }) {
  applyStyleToSelected(prop, value)
}

// ── Image upload ──────────────────────────────────────────────────────
function triggerImageUpload(pi, ei) {
  imgUploadTarget = { pi, ei }
  imgInputRef.value?.click()
}

function triggerImageReplace({ pi, ei }) { triggerImageUpload(pi, ei) }

function onImageFileSelected(e) {
  const file = e.target.files?.[0]
  if (!file || !imgUploadTarget) return

  const formData = new FormData()
  formData.append('image', file)
  formData.append('report_id', report.id)

  window.axios?.post(route('media.upload'), formData)
    .then(res => {
      const { pi, ei } = imgUploadTarget
      if (ei !== null && ei !== undefined) {
        const el = report.content[pi]?.elements?.[ei]
        if (el) { el.src = res.data.url; el.alt = file.name }
      } else {
        // New image element
        addElementFromSidebar({
          def: { type: 'image', src: res.data.url, alt: file.name, w: 300, h: 200 },
          pageIndex: pi,
        })
      }
      markDirty()
      toast('Image uploaded', 'success')
    })
    .catch(() => {
      // Fallback: use local object URL for unsaved preview
      const url = URL.createObjectURL(file)
      const { pi, ei } = imgUploadTarget
      if (ei !== null && ei !== undefined) {
        const el = report.content[pi]?.elements?.[ei]
        if (el) { el.src = url; el.alt = file.name }
      }
      markDirty()
      toast('Image added (upload endpoint missing — using local preview)', 'warning')
    })
    .finally(() => {
      e.target.value = ''
      imgUploadTarget = null
    })
}

// ── Image search (from LeftSidebar media tab) ─────────────────────────
function handleMediaUpload({ file, pageIndex }) {
  imgUploadTarget = { pi: pageIndex ?? currentPage.value, ei: null }
  // Trigger the same file-select pipeline
  const dt = new DataTransfer()
  dt.items.add(file)
  imgInputRef.value.files = dt.files
  onImageFileSelected({ target: imgInputRef.value })
}

function handleImageSearch({ url, alt, pi }) {
  const el = buildElement(
    { type: 'image', src: url, alt: alt || '', w: 300, h: 200 },
    { x: 80, y: 80 }
  )
  pushHistory()
  const pageIdx = pi ?? currentPage.value
  report.content[pageIdx].elements.push(el)
  goToPage(pageIdx)
  onSelectElement([report.content[pageIdx].elements.length - 1])
  markDirty()
}

// ── Find & Replace ────────────────────────────────────────────────────
function doFind() {
  const term = findText.value.trim()
  if (!term) return
  let count = 0
  report.content.forEach(page => {
    page.elements?.forEach(el => {
      if (el.content && el.content.includes(term)) count++
    })
  })
  findResult.value = count ? `Found in ${count} element(s)` : 'No results'
}

function doReplace() {
  const term = findText.value.trim()
  const rep = replaceText.value
  if (!term) return
  let count = 0
  pushHistory()
  report.content.forEach(page => {
    page.elements?.forEach(el => {
      if (el.content && el.content.includes(term)) {
        el.content = el.content.replaceAll(term, rep)
        count++
      }
    })
  })
  markDirty()
  findResult.value = `Replaced in ${count} element(s)`
  toast(`Replaced in ${count} element(s)`, count ? 'success' : 'warning')
}

// ── Command Palette ────────────────────────────────────────────────────
function executeCommand(cmd) {
  showCommand.value = false
  const actions = {
    'undo': undo,
    'redo': redo,
    'zoom-in': zoomIn,
    'zoom-out': zoomOut,
    'zoom-reset': () => { zoom.value = 100 },
    'add-page': addPage,
    'delete-page': () => deletePage(currentPage.value),
    'duplicate-page': () => duplicatePage(currentPage.value),
    'toggle-grid': () => { showGrid.value = !showGrid.value },
    'toggle-snap': () => { snapToGrid.value = !snapToGrid.value },
    'toggle-dark': toggleDark,
    'fullscreen': toggleFullscreen,
    'toggle-ai': () => { showAI.value = !showAI.value },
    'find-replace': () => { showFind.value = true },
    'preview': openPreview,
    'export-pdf': exportPdf,
    'print': doPrint,
    'bring-front': bringFront,
    'send-back': sendBack,
    'duplicate-el': duplicateSelected,
    'delete-el': deleteSelected,
    'lock-el': toggleLockSelected,
    'copy-style': copyStyle,
    'paste-style': pasteStyle,
    'shortcuts': () => { showShortcuts.value = true },
    'save': saveNow,
  }
  actions[cmd]?.()
}

// ── Keyboard shortcuts ────────────────────────────────────────────────
/**
 * All shortcuts use Ctrl+Alt+* to avoid conflicts with browser
 * default Ctrl+* shortcuts (Ctrl+P = print, Ctrl+Z = undo in
 * address bar, Ctrl+W = close tab, etc.)
 *
 * Exceptions that are truly safe & expected:
 *   Ctrl+K  = Command Palette (not a reserved browser combo)
 *   Delete  = Delete selected (element-focused only)
 *   Escape  = Deselect/close
 */
function registerShortcuts() {
  document.addEventListener('keydown', onGlobalKeyDown)
  document.addEventListener('fullscreenchange', onFscChange)
}

function onGlobalKeyDown(e) {
  // Don't intercept shortcuts when typing in inputs / contenteditable
  const tag = e.target?.tagName
  const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag) || e.target?.isContentEditable
  if (isInput && !['Escape'].includes(e.key)) return

  const ca = e.ctrlKey && e.altKey    // Ctrl+Alt
  const c = e.ctrlKey && !e.altKey   // Ctrl only

  // Ctrl+K — command palette (safe)
  if (c && e.key === 'k') { e.preventDefault(); showCommand.value = !showCommand.value; return }

  // Escape
  if (e.key === 'Escape') {
    if (showCommand.value) { showCommand.value = false; return }
    if (showAI.value) { showAI.value = false; return }
    if (showFind.value) { showFind.value = false; return }
    deselectAll()
    return
  }

  // Delete / Backspace — only when an element is selected and not editing text
  if ((e.key === 'Delete' || e.key === 'Backspace') && !isInput && selectedEls.value.length) {
    e.preventDefault(); deleteSelected(); return
  }

  // Ctrl+Alt combos
  if (!ca) return
  e.preventDefault()

  const map = {
    z: undo, y: redo,
    '=': zoomIn, '-': zoomOut, '0': () => { zoom.value = 100 },
    g: () => { showGrid.value = !showGrid.value },
    s: () => { snapToGrid.value = !snapToGrid.value },
    r: () => { showRulers.value = !showRulers.value },
    a: () => { showAI.value = !showAI.value },
    d: toggleDark,
    f: () => { showFind.value = !showFind.value },
    v: openPreview,
    p: doPrint,
    l: () => { leftCollapsed.value = !leftCollapsed.value },
    b: bringFront,
    e: sendBack,
    c: copyStyle,
    x: pasteStyle,
    q: duplicateSelected,
    Delete: deleteSelected,
  }

  map[e.key.toLowerCase()]?.()
}

function handleGlobalKey(e) {
  // Prevent the shell's keydown from double-firing global handler
  // (global handler is on document, this is just for accessibility focus-trap)
}

// ── Preferences ───────────────────────────────────────────────────────
function loadPreferences() {
  isDark.value = localStorage.getItem('editor-dark') === '1'
  showGrid.value = localStorage.getItem('editor-grid') !== '0'
  snapToGrid.value = localStorage.getItem('editor-snap') !== '0'
  const savedZoom = parseInt(localStorage.getItem('editor-zoom'))
  if (!isNaN(savedZoom)) zoom.value = savedZoom
}

watch(showGrid, v => localStorage.setItem('editor-grid', v ? '1' : '0'))
watch(snapToGrid, v => localStorage.setItem('editor-snap', v ? '1' : '0'))
watch(zoom, v => localStorage.setItem('editor-zoom', v))

// ── Toast helper ──────────────────────────────────────────────────────
function toast(msg, type = 'info') {
  toastRef.value?.show(msg, type)
}
</script>

<style>
/* ════════════════════════════════════════════════════════════════════
   EDITOR SHELL — fully isolated from app / system theme
   All overrides are scoped under .editor-shell so the rest of the
   app is never touched.
   ════════════════════════════════════════════════════════════════════ */
.editor-shell {
  /* Colour tokens — light defaults */
  --es-bg: #f1f5f9;
  --es-surface: #ffffff;
  --es-border: #e2e8f0;
  --es-text: #0f172a;
  --es-text2: #475569;
  --es-text3: #94a3b8;
  --es-accent: #6366f1;
  --es-accent-l: rgba(99, 102, 241, .08);
  --es-gold: #c9a84c;

  /* Layout */
  display: flex;
  flex-direction: column;
  height: 100dvh;
  overflow: hidden;
  font-family: 'DM Sans', 'Inter', system-ui, sans-serif;
  font-size: 14px;
  background: var(--es-bg);
  color: var(--es-text);

  /* Hard reset — do not inherit any parent colour or font */
  all: unset;
  display: flex;
  flex-direction: column;
  height: 100dvh;
  overflow: hidden;
  font-family: 'DM Sans', 'Inter', system-ui, sans-serif;
  font-size: 14px;
  background: var(--es-bg);
  color: var(--es-text);
  box-sizing: border-box;
}

/* Dark override */
.editor-shell.editor-dark {
  --es-bg: #0a0f1a;
  --es-surface: #111827;
  --es-border: #1e2d45;
  --es-text: #e2e8f0;
  --es-text2: #94a3b8;
  --es-text3: #475569;
  --es-accent: #818cf8;
  --es-accent-l: rgba(129, 140, 248, .10);
}

/* Fullscreen */
.editor-shell.editor-fullscreen {
  height: 100vh;
}

/* Body layout */
.editor-body {
  flex: 1;
  display: flex;
  overflow: hidden;
  min-height: 0;
}

/* Sidebar slide transitions */
.sidebar-slide-left-enter-active,
.sidebar-slide-left-leave-active {
  transition: transform .2s ease, opacity .2s ease;
}

.sidebar-slide-left-enter-from,
.sidebar-slide-left-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.sidebar-slide-right-enter-active,
.sidebar-slide-right-leave-active {
  transition: transform .2s ease, opacity .2s ease;
}

.sidebar-slide-right-enter-from,
.sidebar-slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* Utility */
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

/* ── Find/Replace overlay ── */
.find-replace-overlay {
  position: fixed;
  inset: 0;
  z-index: 900;
  background: rgba(0, 0, 0, .5);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 120px;
}

.fro-box {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  width: 420px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
}

.find-replace-overlay.dark .fro-box {
  background: #1a2236;
  color: #e2e8f0;
}

.fro-title {
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 14px;
}

.fro-row {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 14px;
}

.fro-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 7px;
  font-size: 13px;
  outline: none;
  font-family: inherit;
  background: #f8fafc;
  color: #0f172a;
}

.find-replace-overlay.dark .fro-input {
  background: #111827;
  color: #e2e8f0;
  border-color: #263348;
}

.fro-actions {
  display: flex;
  gap: 8px;
}

.fro-btn {
  padding: 7px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 7px;
  background: #f8fafc;
  cursor: pointer;
  font-size: 12px;
  font-family: inherit;
  color: #0f172a;
}

.fro-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.fro-btn--primary {
  background: #6366f1;
  color: #fff;
  border-color: #6366f1;
}

.fro-btn--primary:hover {
  background: #4f46e5;
  color: #fff;
}

.fro-result {
  margin-top: 10px;
  font-size: 12px;
  color: #64748b;
}
</style>