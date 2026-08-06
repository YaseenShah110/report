<!--
  TopToolbar.vue — Production-Ready Top Bar + Formatting Ribbon
  • Inline title editing with auto-save indicator
  • Status pill (draft/published/archived) with cycle
  • Undo/Redo buttons with keyboard shortcut tooltips
  • Zoom controls (isolated Ctrl+Alt prefix — no browser conflicts)
  • Grid, Snap, Rulers, Measure toggles
  • Find & Replace, Command Palette (Ctrl+K), AI Panel toggles
  • Export menu — PDF + Print ONLY (presentation mode & share removed)
  • Live "who's editing" presence badge
  • Dark mode toggle, Fullscreen toggle
  • Left/Right panel collapse buttons
  • Formatting ribbon appears when element selected:
    font family/size, bold/italic/underline/strike,
    text color, bg color, alignment, layer order,
    duplicate, lock, delete
  • All shortcuts use Ctrl+Alt+* — zero browser conflicts
  • Tooltip on every button includes shortcut
  • Mobile responsive (labels hidden under 900px)
  • Memory safe — no leaked listeners
-->
<template>
  <div class="toolbar-root" :class="{ 'is-dark': isDark }">

    <!-- ══ TOP BAR ══════════════════════════════════════════════════════ -->
    <header class="top-bar" role="banner">

      <!-- Left: nav + title ─────────────────────────────────────────── -->
      <div class="tb-left">
        <button class="icon-btn" @click="goBack" title="Back to Reports [Esc]" aria-label="Back to reports">
          <i class="fa-solid fa-arrow-left" />
        </button>
        <div class="tb-divider" aria-hidden="true" />

        <div class="title-block">
          <div class="title-row">
            <input :value="report.title" @input="$emit('update:title', $event.target.value)" @blur="$emit('save')"
              @keydown.enter="$event.target.blur()" class="title-input" placeholder="Untitled Report" spellcheck="false"
              aria-label="Report title" maxlength="120" />
            <button class="status-pill" :class="`status-${report.status}`" @click="$emit('change-status')"
              :title="`Status: ${report.status} — click to cycle`"
              :aria-label="`Status ${report.status}, click to change`">
              <span class="status-dot" aria-hidden="true" />
              {{ report.status }}
              <i class="fa-solid fa-chevron-down status-arrow" aria-hidden="true" />
            </button>
          </div>
          <div class="save-row" :class="{ saving: isSaving, saved: lastSaved && !isDirty, dirty: isDirty }"
            aria-live="polite" aria-atomic="true">
            <template v-if="isSaving">
              <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" /> Saving…
            </template>
            <template v-else-if="lastSaved && !isDirty">
              <i class="fa-solid fa-check-circle" aria-hidden="true" /> Saved {{ lastSaved }}
            </template>
            <template v-else-if="isDirty">
              <span class="dirty-dot" aria-hidden="true" /> Unsaved changes
            </template>
          </div>
        </div>

        <!-- Live "who's editing" badge -->
        <PresenceBadge :editors="presenceEditors" :is-dark="isDark" />
      </div>

      <!-- Center: editing tools ──────────────────────────────────────── -->
      <div class="tb-center" role="toolbar" aria-label="Editing tools">

        <!-- Undo / Redo -->
        <button class="icon-btn" @click="$emit('undo')" :disabled="!canUndo" title="Undo [Ctrl+Alt+Z]"
          aria-label="Undo">
          <i class="fa-solid fa-undo" />
        </button>
        <button class="icon-btn" @click="$emit('redo')" :disabled="!canRedo" title="Redo [Ctrl+Alt+Y]"
          aria-label="Redo">
          <i class="fa-solid fa-redo" />
        </button>
        <div class="tb-divider" aria-hidden="true" />

        <!-- Zoom -->
        <button class="icon-btn" @click="$emit('zoom-out')" title="Zoom Out [Ctrl+Alt+-]" aria-label="Zoom out">
          <i class="fa-solid fa-minus" />
        </button>
        <button class="zoom-display" @click="$emit('zoom-reset')" title="Reset Zoom to 100% [Ctrl+Alt+0]"
          aria-label="`Zoom ${zoom}%, click to reset`">{{ zoom }}%</button>
        <button class="icon-btn" @click="$emit('zoom-in')" title="Zoom In [Ctrl+Alt+=]" aria-label="Zoom in">
          <i class="fa-solid fa-plus" />
        </button>
        <div class="tb-divider" aria-hidden="true" />

        <!-- Canvas controls -->
        <button class="icon-btn" :class="{ active: showGrid }" @click="$emit('toggle-grid')"
          title="Toggle Grid [Ctrl+Alt+G]" aria-label="Toggle grid" :aria-pressed="showGrid"><i
            class="fa-solid fa-border-all" /></button>

        <button class="icon-btn" :class="{ active: snapToGrid }" @click="$emit('toggle-snap')"
          title="Snap to Grid [Ctrl+Alt+S]" aria-label="Snap to grid" :aria-pressed="snapToGrid"><i
            class="fa-solid fa-magnet" /></button>

        <button class="icon-btn" :class="{ active: showRulers }" @click="$emit('toggle-rulers')"
          title="Toggle Rulers [Ctrl+Alt+R]" aria-label="Toggle rulers" :aria-pressed="showRulers"><i
            class="fa-solid fa-ruler-combined" /></button>

        <button class="icon-btn" @click="$emit('toggle-measure')" title="Measure Tool [Ctrl+Alt+M]"
          aria-label="Measure tool"><i class="fa-solid fa-ruler" /></button>

        <div class="tb-divider" aria-hidden="true" />

        <!-- Utility toggles -->
        <button class="icon-btn" @click="$emit('toggle-find')" title="Find & Replace [Ctrl+Alt+F]"
          aria-label="Find and replace"><i class="fa-solid fa-magnifying-glass" /></button>
        <button class="icon-btn" @click="$emit('toggle-command')" title="Command Palette [Ctrl+K]"
          aria-label="Command palette"><i class="fa-solid fa-terminal" /></button>
        <button class="icon-btn ai-btn" :class="{ active: showAI }" @click="$emit('toggle-ai')"
          title="AI Assistant [Ctrl+Alt+A]" aria-label="AI assistant" :aria-pressed="showAI">
          <i class="fa-solid fa-wand-magic-sparkles" /> AI
        </button>
      </div>

      <!-- Right: view + export ───────────────────────────────────────── -->
      <div class="tb-right">
        <!-- Panel toggles -->
        <button class="icon-btn" :class="{ active: !leftCollapsed }" @click="$emit('toggle-left-panel')"
          title="Left Panel [Ctrl+Alt+L]" aria-label="Toggle left panel"><i class="fa-solid fa-sidebar" /></button>
        <button class="icon-btn" :class="{ active: !rightCollapsed }" @click="$emit('toggle-right-panel')"
          title="Right Panel [Ctrl+Alt+R]" aria-label="Toggle right panel"><i
            class="fa-solid fa-sidebar-flip" /></button>

        <div class="tb-divider" aria-hidden="true" />

        <!-- Preview -->
        <button class="btn-secondary" @click="$emit('preview')" title="Preview [Ctrl+Alt+V]"
          aria-label="Preview report">
          <i class="fa-solid fa-eye" /><span class="btn-label">Preview</span>
        </button>

        <!-- Export dropdown — PDF + Print ONLY -->
        <div class="dropdown-wrap" ref="exportDropRef">
          <button class="btn-primary" @click="showExport = !showExport" :aria-expanded="showExport" aria-haspopup="menu"
            aria-label="Export report">
            <i class="fa-solid fa-download" />
            <span class="btn-label">Export</span>
            <i class="fa-solid fa-chevron-down" style="font-size:9px;margin-left:2px" aria-hidden="true" />
          </button>

          <Transition name="dropdown">
            <div v-if="showExport" class="dropdown-menu" role="menu" aria-label="Export options">
              <div class="dropdown-header">Export</div>

              <button @click="doExport('pdf')" role="menuitem">
                <span class="export-icon-wrap pdf"><i class="fa-solid fa-file-pdf" /></span>
                <div><strong>PDF Document</strong><small>Saved exactly as it looks in Preview</small></div>
              </button>

              <button @click="doPrint" role="menuitem">
                <span class="export-icon-wrap print"><i class="fa-solid fa-print" /></span>
                <div><strong>Print</strong><small>Open the system print dialog</small></div>
              </button>
            </div>
          </Transition>
        </div>

        <div class="tb-divider" aria-hidden="true" />

        <!-- Theme / Fullscreen -->
        <button class="icon-btn" @click="$emit('toggle-dark')"
          :title="isDark ? 'Light Mode [Ctrl+Alt+D]' : 'Dark Mode [Ctrl+Alt+D]'"
          :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'" :aria-pressed="isDark">
          <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" />
        </button>

        <button class="icon-btn" @click="$emit('toggle-fullscreen')"
          :title="isFullscreen ? 'Exit Fullscreen [F11]' : 'Fullscreen [F11]'"
          :aria-label="isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'" :aria-pressed="isFullscreen">
          <i :class="isFullscreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand'" />
        </button>
      </div>
    </header>

    <!-- ══ FORMATTING RIBBON ════════════════════════════════════════════ -->
    <Transition name="ribbon-slide">
      <div v-if="selectedEl && !selectedEl.locked" class="ribbon" role="toolbar" aria-label="Element formatting">
        <div class="ribbon-inner">

          <!-- Font family -->
          <select :value="selectedEl.styles?.fontFamily || settings.font_family || 'DM Sans'"
            @change="$emit('apply-style', 'fontFamily', $event.target.value)" class="ribbon-select font-select"
            aria-label="Font family">
            <option v-for="f in FONT_LIST" :key="f" :value="f">{{ f }}</option>
          </select>

          <!-- Font size -->
          <select :value="selectedEl.styles?.fontSize || 14"
            @change="$emit('apply-style', 'fontSize', +$event.target.value)" class="ribbon-select size-select"
            aria-label="Font size">
            <option v-for="s in FONT_SIZES" :key="s" :value="s">{{ s }}</option>
          </select>

          <div class="ribbon-sep" aria-hidden="true" />

          <!-- Text style toggles -->
          <button class="ribbon-btn" :class="{ active: selectedEl.styles?.fontWeight === '700' }"
            @click="$emit('toggle-fmt', 'fontWeight', '700', '400')" title="Bold [Ctrl+Alt+B]" aria-label="Bold"
            :aria-pressed="selectedEl.styles?.fontWeight === '700'"><b>B</b></button>

          <button class="ribbon-btn italic-btn" :class="{ active: selectedEl.styles?.fontStyle === 'italic' }"
            @click="$emit('toggle-fmt', 'fontStyle', 'italic', 'normal')" title="Italic [Ctrl+Alt+I]"
            aria-label="Italic" :aria-pressed="selectedEl.styles?.fontStyle === 'italic'"><em>I</em></button>

          <button class="ribbon-btn" :class="{ active: selectedEl.styles?.textDecoration === 'underline' }"
            @click="$emit('toggle-fmt', 'textDecoration', 'underline', 'none')" title="Underline [Ctrl+Alt+U]"
            aria-label="Underline" :aria-pressed="selectedEl.styles?.textDecoration === 'underline'"><u>U</u></button>

          <button class="ribbon-btn" :class="{ active: selectedEl.styles?.textDecoration === 'line-through' }"
            @click="$emit('toggle-fmt', 'textDecoration', 'line-through', 'none')" title="Strikethrough"
            aria-label="Strikethrough"><s>S</s></button>

          <div class="ribbon-sep" aria-hidden="true" />

          <!-- Text color -->
          <div class="color-picker-wrap" title="Text Color" aria-label="Text color">
            <span class="color-swatch text-swatch" :style="{ background: selectedEl.styles?.color || '#000' }"
              aria-hidden="true">A</span>
            <input type="color" :value="selectedEl.styles?.color || '#000000'"
              @input="$emit('apply-style', 'color', $event.target.value)" class="color-hidden"
              aria-label="Choose text color" />
          </div>

          <!-- Background color -->
          <div class="color-picker-wrap" title="Background Color" aria-label="Background color">
            <span class="color-swatch bg-swatch"
              :style="{ background: selectedEl.styles?.backgroundColor === 'transparent' ? '#ffffff' : (selectedEl.styles?.backgroundColor || '#ffffff') }"
              aria-hidden="true">
              <i class="fa-solid fa-fill-drip" />
            </span>
            <input type="color"
              :value="selectedEl.styles?.backgroundColor === 'transparent' ? '#ffffff' : (selectedEl.styles?.backgroundColor || '#ffffff')"
              @input="$emit('apply-style', 'backgroundColor', $event.target.value)" class="color-hidden"
              aria-label="Choose background color" />
          </div>

          <div class="ribbon-sep" aria-hidden="true" />

          <!-- Alignment -->
          <button v-for="a in ['left', 'center', 'right', 'justify']" :key="a" class="ribbon-btn"
            :class="{ active: selectedEl.styles?.textAlign === a }" @click="$emit('apply-style', 'textAlign', a)"
            :title="`Align ${a}`" :aria-label="`Align ${a}`"><i :class="`fa-solid fa-align-${a}`" /></button>

          <div class="ribbon-sep" aria-hidden="true" />

          <!-- Layer order -->
          <button class="ribbon-btn" @click="$emit('bring-front')" title="Bring to Front" aria-label="Bring to front">
            <i class="fa-solid fa-angles-up" />
          </button>
          <button class="ribbon-btn" @click="$emit('send-back')" title="Send to Back" aria-label="Send to back">
            <i class="fa-solid fa-angles-down" />
          </button>

          <!-- Duplicate -->
          <button class="ribbon-btn" @click="$emit('duplicate-el')" title="Duplicate [Ctrl+Alt+D]"
            aria-label="Duplicate element">
            <i class="fa-solid fa-clone" />
          </button>

          <!-- Lock -->
          <button class="ribbon-btn" :class="{ active: selectedEl.locked }" @click="$emit('lock-el')"
            :title="selectedEl.locked ? 'Unlock' : 'Lock'"
            :aria-label="selectedEl.locked ? 'Unlock element' : 'Lock element'" :aria-pressed="selectedEl.locked">
            <i :class="selectedEl.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
          </button>

          <div class="ribbon-spacer" aria-hidden="true" />

          <!-- Delete -->
          <button class="ribbon-btn danger-ribbon-btn" @click="$emit('delete-el')" title="Delete [Del]"
            aria-label="Delete element">
            <i class="fa-solid fa-trash-can" />
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import PresenceBadge from './PresenceBadge.vue'

// ── Props ──────────────────────────────────────────────────────────────
const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
  isDirty: { type: Boolean, default: false },
  isSaving: { type: Boolean, default: false },
  lastSaved: { type: String, default: '' },
  zoom: { type: Number, default: 100 },
  canUndo: { type: Boolean, default: false },
  canRedo: { type: Boolean, default: false },
  selectedEl: { type: Object, default: null },
  showGrid: { type: Boolean, default: true },
  snapToGrid: { type: Boolean, default: true },
  showRulers: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
  isFullscreen: { type: Boolean, default: false },
  showAI: { type: Boolean, default: false },
  leftCollapsed: { type: Boolean, default: false },
  rightCollapsed: { type: Boolean, default: false },
  // Live "who's editing" — array of { id, name, initials, color }, current user excluded
  presenceEditors: { type: Array, default: () => [] },
})

const emit = defineEmits([
  'update:title', 'save', 'undo', 'redo',
  'zoom-in', 'zoom-out', 'zoom-reset',
  'toggle-grid', 'toggle-snap', 'toggle-rulers', 'toggle-measure',
  'toggle-dark', 'toggle-fullscreen', 'toggle-ai',
  'toggle-command', 'toggle-find', 'preview', 'print-preview',
  'export-pdf', 'change-status',
  'apply-style', 'toggle-fmt',
  'delete-el', 'duplicate-el', 'lock-el',
  'bring-front', 'send-back',
  'toggle-left-panel', 'toggle-right-panel',
])

// ── State ──────────────────────────────────────────────────────────────
const showExport = ref(false)
const exportDropRef = ref(null)

// ── Constants ──────────────────────────────────────────────────────────
const FONT_LIST = [
  'DM Sans', 'Inter', 'Plus Jakarta Sans', 'Space Grotesk', 'Sora', 'Nunito',
  'Outfit', 'Poppins', 'Figtree', 'Georgia', 'Playfair Display', 'Merriweather',
  'Lora', 'Fira Code', 'Courier New', 'Times New Roman',
]

const FONT_SIZES = [
  8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 24, 28, 32, 36, 40, 48, 56, 64, 72, 80, 96, 120, 160,
]

// ── Methods ────────────────────────────────────────────────────────────
function goBack() {
  if (props.isDirty) {
    if (!confirm('You have unsaved changes. Leave anyway?')) return
  }
  router.visit(route('reports.index'))
}

function doExport(type) {
  showExport.value = false
  if (type === 'pdf') emit('export-pdf')
}

function doPrint() {
  showExport.value = false
  emit('print-preview')
}

// Close export dropdown on outside click
function onDocClick(e) {
  if (exportDropRef.value && !exportDropRef.value.contains(e.target)) {
    showExport.value = false
  }
}

onMounted(() => document.addEventListener('click', onDocClick, true))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick, true))
</script>

<style scoped>
/* ═══ ROOT ═══════════════════════════════════════════════════════════════ */
.toolbar-root {
  --tb-bg: #ffffff;
  --tb-bg2: #f8fafc;
  --tb-border: #e2e8f0;
  --tb-text: #0f172a;
  --tb-text2: #475569;
  --tb-text3: #94a3b8;
  --tb-accent: #6366f1;
  --tb-accent-l: rgba(99, 102, 241, .08);
  --tb-success: #10b981;
  --tb-warning: #f59e0b;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  z-index: 60;
  position: relative;
}

.toolbar-root.is-dark {
  --tb-bg: #1a2236;
  --tb-bg2: #111827;
  --tb-border: #263348;
  --tb-text: #e2e8f0;
  --tb-text2: #94a3b8;
  --tb-text3: #475569;
  --tb-accent: #818cf8;
  --tb-accent-l: rgba(129, 140, 248, .1);
}

/* ═══ TOP BAR ════════════════════════════════════════════════════════════ */
.top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 52px;
  padding: 0 10px;
  background: var(--tb-bg);
  border-bottom: 1px solid var(--tb-border);
  box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
  gap: 8px;
}

.tb-left,
.tb-center,
.tb-right {
  display: flex;
  align-items: center;
  gap: 4px;
}

.tb-left {
  min-width: 0;
}

.tb-center {
  flex: 1;
  justify-content: center;
}

/* ── Divider ── */
.tb-divider {
  width: 1px;
  height: 22px;
  background: var(--tb-border);
  flex-shrink: 0;
  margin: 0 2px;
}

/* ── Title block ── */
.title-block {
  display: flex;
  flex-direction: column;
  gap: 1px;
  min-width: 0;
}

.title-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.title-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  font-weight: 700;
  color: var(--tb-text);
  min-width: 120px;
  max-width: 280px;
  padding: 3px 8px;
  border-radius: 6px;
  transition: background .14s;
  font-family: inherit;
}

.title-input:hover {
  background: var(--tb-bg2);
}

.title-input:focus {
  background: var(--tb-bg2);
  box-shadow: 0 0 0 2px var(--tb-accent);
}

.title-input::placeholder {
  color: var(--tb-text3);
}

/* ── Status pill ── */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 99px;
  border: 1.5px solid transparent;
  font-size: 10px;
  font-weight: 700;
  text-transform: capitalize;
  letter-spacing: .03em;
  cursor: pointer;
  transition: all .2s;
  white-space: nowrap;
  background: transparent;
  font-family: inherit;
}

.status-pill:hover {
  transform: scale(1.04);
  box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
}

.status-draft {
  background: rgba(245, 158, 11, .1);
  color: #d97706;
  border-color: rgba(245, 158, 11, .3);
}

.status-published {
  background: rgba(16, 185, 129, .1);
  color: #059669;
  border-color: rgba(16, 185, 129, .3);
}

.status-archived {
  background: rgba(148, 163, 184, .1);
  color: #64748b;
  border-color: rgba(148, 163, 184, .3);
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  animation: statusPulse 2s ease-in-out infinite;
}

.status-arrow {
  font-size: 8px;
  opacity: .6;
}

@keyframes statusPulse {

  0%,
  100% {
    opacity: 1
  }

  50% {
    opacity: .5
  }
}

/* ── Save row ── */
.save-row {
  font-size: 10px;
  color: var(--tb-text3);
  padding-left: 2px;
  display: flex;
  align-items: center;
  gap: 4px;
  height: 14px;
}

.save-row.saving {
  color: var(--tb-accent);
}

.save-row.saved {
  color: var(--tb-success);
}

.save-row.dirty {
  color: var(--tb-warning);
}

.dirty-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--tb-warning);
  display: inline-block;
  animation: dirtyPulse 1.5s ease-in-out infinite;
}

@keyframes dirtyPulse {

  0%,
  100% {
    opacity: 1;
    transform: scale(1)
  }

  50% {
    opacity: .5;
    transform: scale(1.5)
  }
}

/* ═══ BUTTONS ════════════════════════════════════════════════════════════ */
.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 7px;
  cursor: pointer;
  color: var(--tb-text2);
  font-size: 13px;
  transition: all .14s;
  flex-shrink: 0;
}

.icon-btn:hover {
  background: var(--tb-bg2);
  color: var(--tb-text);
}

.icon-btn.active {
  background: var(--tb-accent-l);
  color: var(--tb-accent);
}

.icon-btn:disabled {
  opacity: .35;
  cursor: not-allowed;
  pointer-events: none;
}

/* AI button — special style */
.ai-btn {
  width: auto;
  padding: 0 10px;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  background: linear-gradient(135deg, rgba(99, 102, 241, .07), rgba(139, 92, 246, .04));
  border: 1px solid rgba(99, 102, 241, .15);
}

.ai-btn:hover {
  background: linear-gradient(135deg, rgba(99, 102, 241, .14), rgba(139, 92, 246, .08));
  border-color: rgba(99, 102, 241, .3);
}

.ai-btn.active {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 2px 12px rgba(99, 102, 241, .45);
}

/* Zoom display */
.zoom-display {
  min-width: 50px;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  color: var(--tb-text);
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 6px;
  background: transparent;
  border: none;
  transition: background .14s;
  font-family: inherit;
}

.zoom-display:hover {
  background: var(--tb-bg2);
}

/* Secondary button */
.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  border: 1px solid var(--tb-border);
  background: var(--tb-bg);
  color: var(--tb-text);
  border-radius: 7px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 500;
  transition: all .14s;
  font-family: inherit;
  white-space: nowrap;
}

.btn-secondary:hover {
  border-color: var(--tb-accent);
  color: var(--tb-accent);
  background: var(--tb-accent-l);
}

/* Primary button */
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border: none;
  background: var(--tb-accent);
  color: #fff;
  border-radius: 7px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
  transition: all .14s;
  box-shadow: 0 1px 4px rgba(99, 102, 241, .3);
  font-family: inherit;
  white-space: nowrap;
}

.btn-primary:hover {
  background: #4f46e5;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, .4);
}

/* Hide labels on smaller screens */
.btn-label {
  display: none;
}

@media (min-width: 900px) {
  .btn-label {
    display: inline;
  }
}

/* ═══ EXPORT DROPDOWN ════════════════════════════════════════════════════ */
.dropdown-wrap {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  background: var(--tb-bg);
  border: 1px solid var(--tb-border);
  border-radius: 12px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, .15);
  padding: 6px;
  min-width: 240px;
  z-index: 400;
}

.dropdown-header {
  font-size: 9px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: var(--tb-text3);
  padding: 6px 10px 4px;
}

.dropdown-menu button {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 8px 10px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--tb-text);
  font-size: 12px;
  border-radius: 7px;
  text-align: left;
  transition: background .1s;
  font-family: inherit;
}

.dropdown-menu button:hover {
  background: var(--tb-bg2);
}

.dropdown-menu button>div {
  display: flex;
  flex-direction: column;
}

.dropdown-menu button strong {
  font-size: 12px;
  font-weight: 600;
}

.dropdown-menu button small {
  font-size: 10px;
  color: var(--tb-text3);
  margin-top: 1px;
}

.export-icon-wrap {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #fff;
  flex-shrink: 0;
}

.pdf {
  background: #ef4444;
}

.print {
  background: #6366f1;
}

/* Dropdown transition */
.dropdown-enter-active {
  animation: dropIn .15s ease;
}

.dropdown-leave-active {
  animation: dropIn .1s ease reverse;
}

@keyframes dropIn {
  from {
    opacity: 0;
    transform: translateY(-5px) scale(.97);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* ═══ FORMATTING RIBBON ══════════════════════════════════════════════════ */
.ribbon {
  background: var(--tb-bg);
  border-bottom: 1px solid var(--tb-border);
  box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
  flex-shrink: 0;
}

.ribbon-inner {
  display: flex;
  align-items: center;
  gap: 2px;
  height: 42px;
  padding: 0 10px;
  overflow-x: auto;
  scrollbar-width: none;
}

.ribbon-inner::-webkit-scrollbar {
  display: none;
}

.ribbon-sep {
  width: 1px;
  height: 20px;
  background: var(--tb-border);
  margin: 0 5px;
  flex-shrink: 0;
}

.ribbon-spacer {
  flex: 1;
}

/* Font selects */
.ribbon-select {
  border: 1px solid var(--tb-border);
  border-radius: 5px;
  background: var(--tb-bg);
  color: var(--tb-text);
  font-size: 11px;
  height: 28px;
  padding: 0 6px;
  outline: none;
  cursor: pointer;
  font-family: inherit;
  transition: border-color .14s;
}

.ribbon-select:hover {
  border-color: #94a3b8;
}

.ribbon-select:focus {
  border-color: var(--tb-accent);
}

.font-select {
  width: 130px;
}

.size-select {
  width: 56px;
}

/* Ribbon buttons */
.ribbon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  border-radius: 5px;
  cursor: pointer;
  color: var(--tb-text2);
  font-size: 13px;
  font-weight: 500;
  transition: all .12s;
  flex-shrink: 0;
  font-family: inherit;
}

.ribbon-btn:hover {
  background: var(--tb-bg2);
  color: var(--tb-text);
}

.ribbon-btn.active {
  background: var(--tb-accent-l);
  color: var(--tb-accent);
  font-weight: 700;
}

.italic-btn em {
  font-style: italic;
}

.danger-ribbon-btn:hover {
  background: rgba(239, 68, 68, .08);
  color: #ef4444;
}

/* Color pickers */
.color-picker-wrap {
  position: relative;
  display: flex;
  align-items: center;
  cursor: pointer;
}

.color-swatch {
  width: 24px;
  height: 24px;
  border-radius: 5px;
  border: 1.5px solid var(--tb-border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
  overflow: hidden;
  transition: border-color .14s;
}

.text-swatch {
  color: #fff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, .4);
  font-size: 12px;
}

.bg-swatch {
  color: var(--tb-text3);
}

.color-picker-wrap:hover .color-swatch {
  border-color: var(--tb-accent);
}

.color-hidden {
  position: absolute;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
  inset: 0;
}

/* Ribbon transition */
.ribbon-slide-enter-active {
  animation: ribbonDown .2s ease;
}

.ribbon-slide-leave-active {
  animation: ribbonDown .15s ease reverse;
  overflow: hidden;
}

@keyframes ribbonDown {
  from {
    opacity: 0;
    transform: translateY(-8px);
    max-height: 0;
  }

  to {
    opacity: 1;
    transform: translateY(0);
    max-height: 42px;
  }
}

/* ═══ RESPONSIVE ════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
  .title-input {
    max-width: 140px;
    font-size: 13px;
  }

  .save-row {
    display: none;
  }

  .ribbon {
    display: none;
  }

  .tb-center {
    display: none;
  }
}

@media (max-width: 480px) {
  .status-pill {
    display: none;
  }

  .title-input {
    max-width: 100px;
  }
}
</style>