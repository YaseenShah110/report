<template>
  <div class="toolbar-wrap" :class="{ dark: isDark }">
    <!-- ══ TOP BAR ══════════════════════════════════════════════════════════ -->
    <header class="top-bar">
      <!-- Left -->
      <div class="tb-left">
        <button class="icon-btn" @click="goBack" title="Back to Reports">
          <i class="fa-solid fa-arrow-left" />
        </button>
        <div class="divv" />

        <div class="title-area">
          <div class="title-row">
            <input
              :value="report.title"
              @input="$emit('update:title', $event.target.value)"
              @blur="$emit('save')"
              @keydown.enter.prevent="$emit('save')"
              class="title-input"
              placeholder="Untitled Report"
              spellcheck="false"
            />
            <button class="status-pill" :class="report.status" @click="$emit('change-status')" title="Click to cycle status">
              <span class="status-dot" />
              {{ report.status }}
              <i class="fa-solid fa-chevron-down" style="font-size:7px;opacity:.6" />
            </button>
          </div>
          <div class="save-row">
            <template v-if="isSaving"><i class="fa-solid fa-spinner fa-spin" /><span>Saving…</span></template>
            <template v-else-if="lastSaved && !isDirty"><i class="fa-solid fa-check-circle" style="color:var(--success)" /><span>Saved {{ lastSaved }}</span></template>
            <template v-else-if="isDirty"><span class="pulse-dot" /><span>Unsaved changes</span></template>
          </div>
        </div>
      </div>

      <!-- Center -->
      <div class="tb-center">
        <!-- Undo/Redo -->
        <button class="icon-btn" @click="$emit('undo')" :disabled="!canUndo" title="Undo (Ctrl+Z)"><i class="fa-solid fa-undo" /></button>
        <button class="icon-btn" @click="$emit('redo')" :disabled="!canRedo" title="Redo (Ctrl+Y)"><i class="fa-solid fa-redo" /></button>
        <div class="divv" />

        <!-- Zoom -->
        <button class="icon-btn" @click="$emit('zoom-out')" title="Zoom Out (Ctrl+-)"><i class="fa-solid fa-minus" /></button>
        <button class="zoom-val" @click="$emit('zoom-reset')" title="Reset Zoom">{{ zoom }}%</button>
        <button class="icon-btn" @click="$emit('zoom-in')" title="Zoom In (Ctrl++)"><i class="fa-solid fa-plus" /></button>
        <div class="divv" />

        <!-- Canvas toggles -->
        <button class="icon-btn" :class="{ active: showGrid }" @click="$emit('toggle-grid')" title="Grid (Ctrl+G)"><i class="fa-solid fa-border-all" /></button>
        <button class="icon-btn" :class="{ active: snapToGrid }" @click="$emit('toggle-snap')" title="Snap"><i class="fa-solid fa-magnet" /></button>
        <button class="icon-btn" :class="{ active: showRulers }" @click="$emit('toggle-rulers')" title="Rulers"><i class="fa-solid fa-ruler-combined" /></button>
        <button class="icon-btn" :class="{ active: measureMode }" @click="$emit('toggle-measure')" title="Measure (Ctrl+M)"><i class="fa-solid fa-ruler" /></button>
        <div class="divv" />

        <!-- Tools -->
        <button class="icon-btn" @click="$emit('toggle-find')" title="Find & Replace (Ctrl+F)"><i class="fa-solid fa-magnifying-glass" /></button>
        <button class="icon-btn" @click="$emit('toggle-command')" title="Command Palette (Ctrl+K)"><i class="fa-solid fa-terminal" /></button>
        <button class="ai-btn" :class="{ active: showAI }" @click="$emit('toggle-ai')" title="AI Assistant">
          <i class="fa-solid fa-wand-magic-sparkles" /> AI
        </button>
        <button class="icon-btn" @click="$emit('presentation')" title="Presentation Mode (Ctrl+F5)"><i class="fa-solid fa-play" /></button>
      </div>

      <!-- Right -->
      <div class="tb-right">
        <!-- Panel toggles -->
        <button class="icon-btn" :class="{ active: !leftCollapsed }" @click="$emit('toggle-left-panel')" title="Left Panel"><i class="fa-solid fa-sidebar" /></button>
        <button class="icon-btn" :class="{ active: !rightCollapsed }" @click="$emit('toggle-right-panel')" title="Right Panel"><i class="fa-solid fa-sidebar-flip" /></button>
        <div class="divv" />

        <!-- Actions -->
        <button class="btn-sec" @click="$emit('preview')" title="Preview (Ctrl+P)"><i class="fa-solid fa-eye" /><span class="bl">Preview</span></button>
        <button class="btn-sec" @click="$emit('print-preview')" title="Print"><i class="fa-solid fa-print" /><span class="bl">Print</span></button>

        <!-- Export dropdown -->
        <div class="dropdown-wrap" ref="exportWrap">
          <button class="btn-primary" @click="showExport = !showExport">
            <i class="fa-solid fa-download" /><span class="bl">Export</span><i class="fa-solid fa-chevron-down" style="font-size:8px;opacity:.7;margin-left:2px" />
          </button>
          <Transition name="ddrop">
            <div v-if="showExport" class="dropdown-menu" @click.stop>
              <div class="dd-head">Export As</div>
              <button class="dd-item" @click="doExport('pdf')">
                <span class="dd-icon" style="background:#ef4444"><i class="fa-solid fa-file-pdf" /></span>
                <div><strong>PDF Document</strong><small>Best for sharing & printing</small></div>
              </button>
              <button class="dd-item" @click="doExport('image')">
                <span class="dd-icon" style="background:#8b5cf6"><i class="fa-solid fa-file-image" /></span>
                <div><strong>PNG Image</strong><small>High-quality screenshot</small></div>
              </button>
              <button class="dd-item" @click="doExport('excel')">
                <span class="dd-icon" style="background:#10b981"><i class="fa-solid fa-file-excel" /></span>
                <div><strong>Excel (.xlsx)</strong><small>Tables & chart data</small></div>
              </button>
              <button class="dd-item" @click="doExport('csv')">
                <span class="dd-icon" style="background:#3b82f6"><i class="fa-solid fa-file-csv" /></span>
                <div><strong>CSV Data</strong><small>Raw tabular data</small></div>
              </button>
              <div class="dd-sep" />
              <button class="dd-item" @click="doShare">
                <span class="dd-icon" style="background:#06b6d4"><i class="fa-solid fa-share-nodes" /></span>
                <div><strong>Share Link</strong><small>Copy public URL</small></div>
              </button>
              <button class="dd-item" @click="doEmail">
                <span class="dd-icon" style="background:#f59e0b"><i class="fa-solid fa-envelope" /></span>
                <div><strong>Email Report</strong><small>Send to email address</small></div>
              </button>
            </div>
          </Transition>
        </div>

        <!-- Misc -->
        <button class="icon-btn" @click="$emit('share')" title="Share"><i class="fa-solid fa-share-nodes" /></button>
        <button class="icon-btn" @click="$emit('toggle-dark')" :title="isDark ? 'Light Mode' : 'Dark Mode'">
          <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" />
        </button>
        <button class="icon-btn" @click="$emit('toggle-fullscreen')" :title="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen (F11)'">
          <i :class="isFullscreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand'" />
        </button>
      </div>
    </header>

    <!-- ══ FORMATTING RIBBON (shown when element selected) ══════════════════ -->
    <Transition name="ribbon-slide">
      <div v-if="selectedEl && !selectedEl.locked" class="ribbon">
        <div class="ribbon-inner">

          <!-- Font family -->
          <select :value="selectedEl.styles?.fontFamily || ''" @change="$emit('apply-style','fontFamily',$event.target.value)" class="r-select font-sel">
            <option value="">Font…</option>
            <option v-for="f in FONTS" :key="f.v" :value="f.v">{{ f.l }}</option>
          </select>

          <!-- Font size -->
          <select :value="selectedEl.styles?.fontSize || 14" @change="$emit('apply-style','fontSize',+$event.target.value)" class="r-select size-sel">
            <option v-for="sz in SIZES" :key="sz" :value="sz">{{ sz }}px</option>
          </select>

          <div class="r-sep" />

          <!-- Bold, Italic, Underline, Strike -->
          <button class="r-btn" :class="{ active: selectedEl.styles?.fontWeight==='700' }" @click="$emit('toggle-fmt','fontWeight','700','400')" title="Bold (Ctrl+B)"><b>B</b></button>
          <button class="r-btn ital" :class="{ active: selectedEl.styles?.fontStyle==='italic' }" @click="$emit('toggle-fmt','fontStyle','italic','normal')" title="Italic (Ctrl+I)"><i>I</i></button>
          <button class="r-btn" :class="{ active: selectedEl.styles?.textDecoration==='underline' }" @click="$emit('toggle-fmt','textDecoration','underline','none')" title="Underline (Ctrl+U)"><u>U</u></button>
          <button class="r-btn" :class="{ active: selectedEl.styles?.textDecoration==='line-through' }" @click="$emit('toggle-fmt','textDecoration','line-through','none')" title="Strikethrough"><s>S</s></button>

          <div class="r-sep" />

          <!-- Text color -->
          <div class="r-color-wrap" title="Text Color">
            <div class="r-color-preview text-prev" :style="{ background: selectedEl.styles?.color || '#000' }">A</div>
            <input type="color" :value="selectedEl.styles?.color || '#000000'" @input="$emit('apply-style','color',$event.target.value)" class="r-color-input" />
          </div>

          <!-- Background color -->
          <div class="r-color-wrap" title="Background">
            <div class="r-color-preview bg-prev" :style="{ background: selectedEl.styles?.backgroundColor === 'transparent' ? '#fff' : (selectedEl.styles?.backgroundColor || '#fff') }">
              <i class="fa-solid fa-fill-drip" style="font-size:8px" />
            </div>
            <input type="color" :value="safeColor(selectedEl.styles?.backgroundColor)" @input="$emit('apply-style','backgroundColor',$event.target.value)" class="r-color-input" />
          </div>

          <div class="r-sep" />

          <!-- Alignment -->
          <button v-for="a in ['left','center','right','justify']" :key="a" class="r-btn" :class="{ active: selectedEl.styles?.textAlign===a }" @click="$emit('apply-style','textAlign',a)">
            <i :class="`fa-solid fa-align-${a}`" />
          </button>

          <div class="r-sep" />

          <!-- Layer controls -->
          <button class="r-btn" @click="$emit('bring-front')" title="Bring to Front"><i class="fa-solid fa-angles-up" /></button>
          <button class="r-btn" @click="$emit('send-back')" title="Send to Back"><i class="fa-solid fa-angles-down" /></button>

          <div class="r-sep" />

          <!-- Duplicate / Lock / Delete -->
          <button class="r-btn" @click="$emit('duplicate-el')" title="Duplicate (Ctrl+D)"><i class="fa-solid fa-clone" /></button>
          <button class="r-btn" :class="{ active: selectedEl.locked }" @click="$emit('lock-el')" title="Lock/Unlock">
            <i :class="selectedEl.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
          </button>

          <!-- Multi-select actions -->
          <template v-if="(selectedEls?.length || 0) > 1">
            <div class="r-sep" />
            <button class="r-btn" @click="$emit('group-elements')" title="Group Elements"><i class="fa-solid fa-object-group" /></button>
            <select class="r-select" @change="$emit('align-elements',$event.target.value); $event.target.value=''" style="width:90px">
              <option value="">Align…</option>
              <option value="left">← Left</option>
              <option value="center-h">↔ Center H</option>
              <option value="right">→ Right</option>
              <option value="top">↑ Top</option>
              <option value="center-v">↕ Center V</option>
              <option value="bottom">↓ Bottom</option>
              <option value="distribute-h">⇔ Distribute H</option>
              <option value="distribute-v">⇕ Distribute V</option>
            </select>
          </template>

          <div class="r-spacer" />
          <button class="r-btn danger" @click="$emit('delete-el')" title="Delete (Del)"><i class="fa-solid fa-trash-can" /></button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, default: () => ({}) },
  isDirty: { type: Boolean, default: false },
  isSaving: { type: Boolean, default: false },
  lastSaved: { type: String, default: '' },
  zoom: { type: Number, default: 100 },
  canUndo: { type: Boolean, default: false },
  canRedo: { type: Boolean, default: false },
  selectedEl: { type: Object, default: null },
  selectedEls: { type: Array, default: () => [] },
  showGrid: { type: Boolean, default: true },
  snapToGrid: { type: Boolean, default: true },
  showRulers: { type: Boolean, default: false },
  measureMode: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
  isFullscreen: { type: Boolean, default: false },
  showAI: { type: Boolean, default: false },
  leftCollapsed: { type: Boolean, default: false },
  rightCollapsed: { type: Boolean, default: false },
  reportSlug: { type: String, default: '' },
})

const emit = defineEmits([
  'update:title','save','undo','redo','zoom-in','zoom-out','zoom-reset',
  'toggle-grid','toggle-snap','toggle-rulers','toggle-dark','toggle-fullscreen',
  'toggle-ai','preview','print-preview','export-pdf','export-png','export-excel','export-csv',
  'share','change-status','apply-style','toggle-fmt','delete-el','duplicate-el',
  'lock-el','bring-front','send-back','toggle-left-panel','toggle-right-panel',
  'toggle-measure','toggle-find','toggle-command','presentation','email-report',
  'group-elements','ungroup-elements','align-elements',
])

const showExport = ref(false)
const exportWrap = ref(null)

const FONTS = [
  { v:"'DM Sans', sans-serif", l:'DM Sans' },
  { v:"'Inter', sans-serif", l:'Inter' },
  { v:"'Plus Jakarta Sans', sans-serif", l:'Plus Jakarta Sans' },
  { v:"'Space Grotesk', sans-serif", l:'Space Grotesk' },
  { v:"'Sora', sans-serif", l:'Sora' },
  { v:"'Outfit', sans-serif", l:'Outfit' },
  { v:"'Nunito', sans-serif", l:'Nunito' },
  { v:"Georgia, serif", l:'Georgia' },
  { v:"'Playfair Display', serif", l:'Playfair Display' },
  { v:"'Times New Roman', serif", l:'Times New Roman' },
  { v:"'Fira Code', monospace", l:'Fira Code' },
]
const SIZES = [8,9,10,11,12,13,14,16,18,20,22,24,28,32,36,40,48,56,64,72,80,96,120,144]

function goBack() { router.visit(route('reports.index')) }

function doExport(type) {
  showExport.value = false
  const map = { pdf:'export-pdf', image:'export-png', excel:'export-excel', csv:'export-csv' }
  emit(map[type])
}
function doShare() { showExport.value = false; emit('share') }
function doEmail() { showExport.value = false; emit('email-report') }

function safeColor(v) { return (!v || v === 'transparent') ? '#ffffff' : v }

// Close dropdown on outside click
function onOutsideClick(e) {
  if (showExport.value && exportWrap.value && !exportWrap.value.contains(e.target)) {
    showExport.value = false
  }
}
onMounted(() => document.addEventListener('click', onOutsideClick))
onBeforeUnmount(() => document.removeEventListener('click', onOutsideClick))
</script>

<style scoped>
.toolbar-wrap {
  flex-shrink: 0; display: flex; flex-direction: column;
  background: var(--bg-panel, #fff); border-bottom: 1px solid var(--border, #e2e8f0);
  box-shadow: var(--shadow-xs, 0 1px 2px rgba(0,0,0,0.04));
}

/* ── Top Bar ──────────────────────────────────────────────────────────────── */
.top-bar {
  display: flex; align-items: center; justify-content: space-between;
  height: 48px; padding: 0 10px; gap: 6px;
}
.tb-left, .tb-center, .tb-right { display: flex; align-items: center; gap: 5px; }
.tb-center { flex: 1; justify-content: center; }

.divv { width: 1px; height: 22px; background: var(--border, #e2e8f0); flex-shrink: 0; }

/* Title */
.title-area { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.title-row { display: flex; align-items: center; gap: 7px; }
.title-input {
  background: transparent; border: none; outline: none; font-size: 13px; font-weight: 700;
  color: var(--text-primary, #0f172a); min-width: 140px; max-width: 240px;
  padding: 3px 7px; border-radius: 5px; transition: background .15s; font-family: inherit;
}
.title-input:hover { background: var(--bg-secondary, #f8fafc); }
.title-input:focus { background: var(--bg-secondary, #f8fafc); box-shadow: 0 0 0 2px var(--accent, #6366f1); }

.status-pill {
  display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px;
  border-radius: 99px; font-size: 9px; font-weight: 700; text-transform: capitalize;
  border: 1.5px solid transparent; cursor: pointer; transition: all .2s; background: transparent;
}
.status-pill:hover { transform: scale(1.04); }
.status-pill.draft { background: rgba(245,158,11,.1); color: #d97706; border-color: rgba(245,158,11,.3); }
.status-pill.published { background: rgba(16,185,129,.1); color: #059669; border-color: rgba(16,185,129,.3); }
.status-pill.archived { background: rgba(148,163,184,.1); color: #64748b; border-color: rgba(148,163,184,.3); }
.status-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; animation: dotPulse 2s ease-in-out infinite; }
@keyframes dotPulse { 0%,100%{opacity:1} 50%{opacity:.4} }

.save-row { display: flex; align-items: center; gap: 4px; font-size: 9px; color: var(--text-muted, #94a3b8); padding-left: 2px; }
.pulse-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--warning, #f59e0b); display: inline-block; animation: sbPulse 1.5s ease-in-out infinite; }
@keyframes sbPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.5)} }

/* Buttons */
.icon-btn {
  width: 30px; height: 30px; border: none; background: transparent; border-radius: 7px;
  cursor: pointer; color: var(--text-secondary, #475569); font-size: 12px;
  display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0;
}
.icon-btn:hover { background: var(--bg-secondary, #f8fafc); color: var(--text-primary, #0f172a); }
.icon-btn.active { background: var(--accent-light, rgba(99,102,241,.08)); color: var(--accent, #6366f1); }
.icon-btn:disabled { opacity: .3; cursor: not-allowed; }

.ai-btn {
  display: inline-flex; align-items: center; gap: 4px; padding: 0 10px; height: 30px;
  border: 1px solid rgba(99,102,241,.2); background: rgba(99,102,241,.06);
  border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700;
  color: var(--accent, #6366f1); transition: all .15s; font-family: inherit;
}
.ai-btn:hover { background: rgba(99,102,241,.12); border-color: var(--accent, #6366f1); }
.ai-btn.active { background: linear-gradient(135deg,var(--accent,#6366f1),#8b5cf6); color: #fff; border-color: transparent; box-shadow: 0 2px 10px rgba(99,102,241,.35); }

.zoom-val {
  min-width: 44px; text-align: center; font-size: 11px; font-weight: 700;
  color: var(--text-primary, #0f172a); cursor: pointer; padding: 4px 2px;
  border-radius: 5px; background: transparent; border: none; font-family: inherit; transition: background .15s;
}
.zoom-val:hover { background: var(--bg-secondary, #f8fafc); }

.btn-sec {
  display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px;
  border: 1px solid var(--border, #e2e8f0); background: var(--bg-primary, #fff);
  color: var(--text-primary, #0f172a); border-radius: 7px; cursor: pointer;
  font-size: 11px; font-weight: 500; transition: all .15s; font-family: inherit;
}
.btn-sec:hover { border-color: var(--accent, #6366f1); color: var(--accent, #6366f1); background: var(--accent-light, rgba(99,102,241,.06)); }

.btn-primary {
  display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px;
  border: none; background: var(--accent, #6366f1); color: #fff; border-radius: 7px;
  cursor: pointer; font-size: 11px; font-weight: 600; transition: all .15s; font-family: inherit;
  box-shadow: 0 1px 3px rgba(99,102,241,.3);
}
.btn-primary:hover { background: var(--accent-hover, #4f46e5); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,.35); }

.bl { display: none; }
@media (min-width: 900px) { .bl { display: inline; } }

/* Export Dropdown */
.dropdown-wrap { position: relative; }
.dropdown-menu {
  position: absolute; top: calc(100% + 6px); right: 0; min-width: 250px;
  background: var(--bg-panel, #fff); border: 1px solid var(--border, #e2e8f0);
  border-radius: 12px; box-shadow: var(--shadow-xl, 0 12px 40px rgba(0,0,0,.16));
  padding: 6px; z-index: 500; overflow: hidden;
}
.dd-head { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted, #94a3b8); padding: 5px 8px 4px; }
.dd-item {
  display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 9px;
  border: none; background: transparent; cursor: pointer; font-size: 12px;
  border-radius: 7px; text-align: left; transition: background .1s; font-family: inherit;
  color: var(--text-primary, #0f172a);
}
.dd-item:hover { background: var(--bg-secondary, #f8fafc); }
.dd-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #fff; flex-shrink: 0; }
.dd-item div { display: flex; flex-direction: column; }
.dd-item strong { font-weight: 600; font-size: 12px; }
.dd-item small { font-size: 10px; color: var(--text-muted, #94a3b8); margin-top: 1px; }
.dd-sep { height: 1px; background: var(--border, #e2e8f0); margin: 4px 8px; }
.ddrop-enter-active { animation: ddIn .15s ease; }
.ddrop-leave-active { animation: ddIn .1s ease reverse; }
@keyframes ddIn { from{opacity:0;transform:translateY(-4px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }

/* ── Formatting Ribbon ─────────────────────────────────────────────────── */
.ribbon {
  background: var(--bg-panel, #fff); border-top: 1px solid var(--border, #e2e8f0);
  height: 38px; overflow: hidden; flex-shrink: 0;
}
.ribbon-inner {
  display: flex; align-items: center; gap: 1px; height: 100%; padding: 0 10px;
  overflow-x: auto; scrollbar-width: none;
}
.ribbon-inner::-webkit-scrollbar { display: none; }

.r-btn {
  display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px;
  border: none; background: transparent; border-radius: 5px; cursor: pointer;
  color: var(--text-secondary, #475569); font-size: 12px; font-weight: 600;
  transition: all .12s; flex-shrink: 0; font-family: inherit;
}
.r-btn:hover { background: var(--bg-secondary, #f8fafc); color: var(--text-primary, #0f172a); }
.r-btn.active { background: var(--accent-light, rgba(99,102,241,.08)); color: var(--accent, #6366f1); }
.r-btn.ital i { font-style: italic; }
.r-btn.danger:hover { background: rgba(239,68,68,.08); color: #ef4444; }

.r-sep { width: 1px; height: 20px; background: var(--border, #e2e8f0); margin: 0 4px; flex-shrink: 0; }
.r-spacer { flex: 1; }

.r-select {
  border: 1px solid var(--border, #e2e8f0); background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a); border-radius: 5px; padding: 3px 5px;
  font-size: 10px; cursor: pointer; outline: none; font-family: inherit; height: 26px;
}
.r-select:hover { border-color: var(--accent, #6366f1); }
.font-sel { width: 110px; }
.size-sel { width: 56px; }

.r-color-wrap { position: relative; cursor: pointer; flex-shrink: 0; }
.r-color-preview {
  width: 24px; height: 24px; border-radius: 5px; border: 1.5px solid var(--border, #e2e8f0);
  display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 900;
  color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,.4); overflow: hidden;
}
.r-color-preview.bg-prev { color: var(--text-muted, #94a3b8); }
.r-color-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

/* Ribbon animation */
.ribbon-slide-enter-active { animation: ribIn .2s ease; }
.ribbon-slide-leave-active { animation: ribIn .15s ease reverse; }
@keyframes ribIn {
  from { opacity: 0; transform: translateY(-6px); max-height: 0; }
  to { opacity: 1; transform: translateY(0); max-height: 38px; }
}

@media (max-width: 768px) {
  .title-input { max-width: 130px; font-size: 12px; }
  .save-row { display: none; }
  .tb-center { display: none; }
}
@media (max-width: 480px) {
  .tb-left .divv { display: none; }
  .title-input { max-width: 100px; }
}
</style>