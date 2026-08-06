<!--
  StatusBar.vue — Editor Footer Status Bar
  ════════════════════════════════════════════════════════════════════
  Left:   page navigator (◀ Page N of T ▶) + element counter
  Center: zoom controls (−  100%  +)  with preset dropdown
  Right:  word count · selected count · save status pill
  ════════════════════════════════════════════════════════════════════
-->
<template>
  <footer class="sb-root" :class="{ 'sb-dark': isDark }" role="status" aria-label="Editor status">

    <!-- Left: page nav + element count -->
    <div class="sb-left">
      <button class="sb-nav-btn" @click="$emit('go-to-page', currentPage - 1)" :disabled="currentPage === 0"
        aria-label="Previous page" title="Previous Page"><i class="fa-solid fa-chevron-left" /></button>

      <div class="sb-page-indicator" @click="showPageJump = !showPageJump" title="Jump to page" role="button"
        aria-haspopup="true">
        <i class="fa-regular fa-file-lines sb-page-icon" aria-hidden="true" />
        <span class="sb-page-text">
          Page <strong>{{ currentPage + 1 }}</strong> / {{ totalPages }}
        </span>

        <!-- Jump input -->
        <div v-if="showPageJump" class="sb-jump-pop" @click.stop>
          <input ref="jumpInputRef" type="number" :min="1" :max="totalPages" v-model.number="jumpTo"
            class="sb-jump-input" @keydown.enter="doJump" @keydown.escape="showPageJump = false"
            aria-label="Jump to page number" />
          <button class="sb-jump-btn" @click="doJump">Go</button>
        </div>
      </div>

      <button class="sb-nav-btn" @click="$emit('go-to-page', currentPage + 1)" :disabled="currentPage >= totalPages - 1"
        aria-label="Next page" title="Next Page"><i class="fa-solid fa-chevron-right" /></button>

      <div class="sb-divider" aria-hidden="true" />

      <span v-if="selectedCount > 0" class="sb-badge sb-badge--accent"
        aria-label="`${selectedCount} elements selected`">
        <i class="fa-solid fa-vector-square" />
        {{ selectedCount }} selected
      </span>

      <span class="sb-stat" aria-label="`${totalElements} elements on page`">
        <i class="fa-solid fa-shapes" />
        {{ totalElements }} els
      </span>
    </div>

    <!-- Center: zoom -->
    <div class="sb-center" role="toolbar" aria-label="Zoom controls">
      <button class="sb-zoom-btn" @click="$emit('zoom-out')" title="Zoom Out [Ctrl+Alt+-]" aria-label="Zoom out"><i
          class="fa-solid fa-minus" /></button>

      <div class="sb-zoom-display-wrap" ref="zoomMenuRef">
        <button class="sb-zoom-display" @click="showZoomMenu = !showZoomMenu" @dblclick="$emit('zoom-reset')"
          :title="`${zoom}% — click for presets, double-click to reset`" aria-haspopup="true"
          :aria-expanded="showZoomMenu">{{ zoom }}%</button>

        <div v-if="showZoomMenu" class="sb-zoom-menu" role="menu">
          <button v-for="z in ZOOM_PRESETS" :key="z" class="sb-zoom-opt" :class="{ active: zoom === z }"
            @click="selectZoom(z)" role="menuitem">{{ z }}%</button>
          <div class="sb-zoom-sep" />
          <button class="sb-zoom-opt" @click="selectZoom('fit')" role="menuitem">
            <i class="fa-solid fa-expand-arrows-alt" /> Fit page
          </button>
          <button class="sb-zoom-opt" @click="$emit('zoom-reset'); showZoomMenu = false" role="menuitem">
            <i class="fa-solid fa-crosshairs" /> Reset (100%)
          </button>
        </div>
      </div>

      <button class="sb-zoom-btn" @click="$emit('zoom-in')" title="Zoom In [Ctrl+Alt+=]" aria-label="Zoom in"><i
          class="fa-solid fa-plus" /></button>
    </div>

    <!-- Right: word count + save -->
    <div class="sb-right">
      <span class="sb-stat" :title="`Word count: ${wordCount}`" aria-label="`${wordCount} words`">
        <i class="fa-solid fa-spell-check" />
        {{ wordCount }} words
      </span>

      <div class="sb-divider" aria-hidden="true" />

      <!-- Save status -->
      <div class="sb-save-status" :class="{
        'is-saving': isSaving,
        'is-saved': lastSaved && !isDirty && !isSaving,
        'is-dirty': isDirty && !isSaving,
      }" :title="saveTitle" aria-live="polite" aria-atomic="true">
        <template v-if="isSaving">
          <i class="fa-solid fa-spinner fa-spin" /> Saving…
        </template>
        <template v-else-if="lastSaved && !isDirty">
          <i class="fa-solid fa-circle-check" /> Saved {{ lastSaved }}
        </template>
        <template v-else-if="isDirty">
          <span class="sb-dirty-dot" aria-hidden="true" /> Unsaved
        </template>
        <template v-else>
          <i class="fa-regular fa-cloud" /> Cloud
        </template>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  report: { type: Object, required: true },
  currentPage: { type: Number, default: 0 },
  totalPages: { type: Number, default: 1 },
  selectedCount: { type: Number, default: 0 },
  zoom: { type: Number, default: 100 },
  isDark: { type: Boolean, default: false },
  wordCount: { type: Number, default: 0 },
  isDirty: { type: Boolean, default: false },
  isSaving: { type: Boolean, default: false },
  lastSaved: { type: String, default: '' },
})

const emit = defineEmits(['zoom-in', 'zoom-out', 'zoom-reset', 'go-to-page', 'zoom-set'])

// ── State ────────────────────────────────────────────────────────────
const showZoomMenu = ref(false)
const showPageJump = ref(false)
const jumpTo = ref(1)
const jumpInputRef = ref(null)
const zoomMenuRef = ref(null)

// ── Constants ─────────────────────────────────────────────────────────
const ZOOM_PRESETS = [25, 50, 75, 100, 125, 150, 175, 200, 250, 300]

// ── Computed ──────────────────────────────────────────────────────────
const totalElements = computed(
  () => props.report.content?.[props.currentPage]?.elements?.length ?? 0
)

const saveTitle = computed(() => {
  if (props.isSaving) return 'Saving…'
  if (props.lastSaved && !props.isDirty) return `Last saved ${props.lastSaved}`
  if (props.isDirty) return 'Unsaved changes — auto-saves in 1.5s'
  return 'All changes saved'
})

// ── Methods ───────────────────────────────────────────────────────────
function selectZoom(z) {
  showZoomMenu.value = false
  if (z === 'fit') { emit('zoom-reset'); return }
  emit('zoom-set', z)
}

function doJump() {
  showPageJump.value = false
  const idx = Math.max(0, Math.min(props.totalPages - 1, (jumpTo.value || 1) - 1))
  emit('go-to-page', idx)
}

watch(showPageJump, async (v) => {
  if (v) {
    jumpTo.value = props.currentPage + 1
    await nextTick()
    jumpInputRef.value?.focus()
    jumpInputRef.value?.select()
  }
})

// Close menus on outside click
function onDocClick(e) {
  if (zoomMenuRef.value && !zoomMenuRef.value.contains(e.target)) showZoomMenu.value = false
  if (showPageJump.value && !e.target.closest('.sb-page-indicator')) showPageJump.value = false
}

onMounted(() => document.addEventListener('click', onDocClick, true))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick, true))
</script>

<style scoped>
/* ═══ ROOT ═══════════════════════════════════════════════════════════ */
.sb-root {
  --sb-bg: #ffffff;
  --sb-border: #e2e8f0;
  --sb-text: #475569;
  --sb-text2: #64748b;
  --sb-text3: #94a3b8;
  --sb-accent: #6366f1;
  --sb-accent-l: rgba(99, 102, 241, .08);
  --sb-success: #10b981;
  --sb-warning: #f59e0b;

  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 36px;
  padding: 0 12px;
  flex-shrink: 0;
  background: var(--sb-bg);
  border-top: 1px solid var(--sb-border);
  font-size: 11px;
  color: var(--sb-text);
  gap: 12px;
  position: relative;
}

.sb-root.sb-dark {
  --sb-bg: #0f172a;
  --sb-border: #1e2d45;
  --sb-text: #64748b;
  --sb-text2: #475569;
  --sb-text3: #334155;
  --sb-accent: #818cf8;
}

/* ─── Sections ─── */
.sb-left,
.sb-right {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  flex: 1;
}

.sb-right {
  justify-content: flex-end;
}

.sb-center {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

/* ─── Divider ─── */
.sb-divider {
  width: 1px;
  height: 16px;
  background: var(--sb-border);
  flex-shrink: 0;
}

/* ─── Stats ─── */
.sb-stat {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  color: var(--sb-text2);
  white-space: nowrap;
}

.sb-stat i {
  font-size: 9px;
}

.sb-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 99px;
  font-size: 10px;
  font-weight: 600;
}

.sb-badge--accent {
  background: var(--sb-accent-l);
  color: var(--sb-accent);
}

/* ─── Page navigation ─── */
.sb-nav-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  border-radius: 5px;
  cursor: pointer;
  color: var(--sb-text2);
  font-size: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
}

.sb-nav-btn:hover:not(:disabled) {
  background: var(--sb-accent-l);
  color: var(--sb-accent);
}

.sb-nav-btn:disabled {
  opacity: .3;
  cursor: not-allowed;
}

.sb-page-indicator {
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  padding: 2px 8px;
  border-radius: 6px;
  position: relative;
  white-space: nowrap;
  transition: background .14s;
  user-select: none;
}

.sb-page-indicator:hover {
  background: var(--sb-accent-l);
  color: var(--sb-accent);
}

.sb-page-icon {
  font-size: 10px;
}

.sb-page-text {
  font-size: 10px;
  font-weight: 500;
}

.sb-page-text strong {
  font-weight: 700;
  color: var(--sb-accent);
}

/* Page jump popup */
.sb-jump-pop {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  background: #fff;
  border: 1px solid var(--sb-border);
  border-radius: 8px;
  padding: 8px;
  display: flex;
  gap: 6px;
  z-index: 200;
  box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
  min-width: 130px;
}

.sb-root.sb-dark .sb-jump-pop {
  background: #1a2236;
}

.sb-jump-input {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--sb-border);
  border-radius: 6px;
  background: var(--sb-bg);
  color: var(--sb-text);
  font-size: 12px;
  outline: none;
  font-family: inherit;
  text-align: center;
  min-width: 0;
}

.sb-jump-input:focus {
  border-color: var(--sb-accent);
}

.sb-jump-btn {
  padding: 5px 10px;
  border: none;
  border-radius: 6px;
  background: var(--sb-accent);
  color: #fff;
  cursor: pointer;
  font-size: 11px;
  font-family: inherit;
  font-weight: 600;
  flex-shrink: 0;
  transition: background .14s;
}

.sb-jump-btn:hover {
  background: #4f46e5;
}

/* ─── Zoom ─── */
.sb-zoom-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  border-radius: 5px;
  cursor: pointer;
  color: var(--sb-text2);
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
}

.sb-zoom-btn:hover {
  background: var(--sb-accent-l);
  color: var(--sb-accent);
}

.sb-zoom-display-wrap {
  position: relative;
}

.sb-zoom-display {
  min-width: 52px;
  padding: 3px 8px;
  border: 1px solid var(--sb-border);
  border-radius: 6px;
  background: transparent;
  color: var(--sb-text);
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: all .14s;
  text-align: center;
}

.sb-zoom-display:hover {
  border-color: var(--sb-accent);
  color: var(--sb-accent);
  background: var(--sb-accent-l);
}

.sb-zoom-menu {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  background: #fff;
  border: 1px solid var(--sb-border);
  border-radius: 10px;
  padding: 6px;
  z-index: 200;
  box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
  min-width: 130px;
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.sb-root.sb-dark .sb-zoom-menu {
  background: #1a2236;
}

.sb-zoom-opt {
  padding: 6px 12px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--sb-text);
  font-size: 11px;
  border-radius: 6px;
  text-align: left;
  font-family: inherit;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background .1s;
}

.sb-zoom-opt:hover,
.sb-zoom-opt.active {
  background: var(--sb-accent-l);
  color: var(--sb-accent);
  font-weight: 600;
}

.sb-zoom-sep {
  height: 1px;
  background: var(--sb-border);
  margin: 4px 0;
}

/* ─── Save status ─── */
.sb-save-status {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 600;
  color: var(--sb-text3);
  padding: 3px 9px;
  border-radius: 99px;
  white-space: nowrap;
}

.sb-save-status.is-saving {
  color: var(--sb-accent);
}

.sb-save-status.is-saved {
  color: var(--sb-success);
}

.sb-save-status.is-dirty {
  color: var(--sb-warning);
}

.sb-dirty-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
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

/* ─── Responsive ─── */
@media (max-width: 900px) {
  .sb-stat {
    display: none;
  }

  .sb-divider {
    display: none;
  }
}

@media (max-width: 600px) {
  .sb-zoom-display {
    min-width: 44px;
  }
}
</style>