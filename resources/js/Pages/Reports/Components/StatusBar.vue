<!-- StatusBar.vue -->
<template>
  <footer class="status-bar" :class="{ dark: isDark }">
    <div class="sb-left">
      <span class="sb-item">
        <i class="fa-solid fa-file" />
        Page {{ currentPage + 1 }} / {{ totalPages }}
      </span>
      <span class="sb-sep">·</span>
      <span class="sb-item">
        <i class="fa-solid fa-cubes" />
        {{ elementsCount }} el
      </span>
      <template v-if="selectedEl">
        <span class="sb-sep">·</span>
        <span class="sb-item accent">
          <i class="fa-solid fa-cursor" />
          {{ selectedEl.type }} @ {{ Math.round(selectedEl.position?.x||0) }},{{ Math.round(selectedEl.position?.y||0) }}
        </span>
      </template>
      <template v-if="selectedCount > 1">
        <span class="sb-sep">·</span>
        <span class="sb-item accent">{{ selectedCount }} selected</span>
      </template>
      <template v-if="wordsCount > 0">
        <span class="sb-sep">·</span>
        <span class="sb-item"><i class="fa-solid fa-text-height" /> {{ wordsCount }} words</span>
      </template>
    </div>

    <div class="sb-center">
      <template v-if="isSaving">
        <span class="save-state saving"><i class="fa-solid fa-spinner fa-spin" /> Saving…</span>
      </template>
      <template v-else-if="lastSaved && !isDirty">
        <span class="save-state saved"><i class="fa-solid fa-check-circle" /> Saved {{ lastSaved }}</span>
      </template>
      <template v-else-if="isDirty">
        <span class="save-state unsaved"><span class="pulse-dot" /> Unsaved changes</span>
      </template>
      <template v-else>
        <span class="save-state ready"><i class="fa-solid fa-check" /> Ready</span>
      </template>
    </div>

    <div class="sb-right">
      <!-- Grid size -->
      <button class="sb-btn" @click="cycleGrid" :title="`Grid: ${gridSize}px`">
        <i class="fa-solid fa-border-all" /> {{ gridSize }}px
      </button>
      <!-- Snap -->
      <button class="sb-btn" :class="{ active: snapToGrid }" @click="$emit('toggle-snap')" title="Snap to Grid">
        <i class="fa-solid fa-magnet" />
      </button>
      <!-- Measure -->
      <button class="sb-btn" :class="{ active: measureMode }" @click="$emit('toggle-measure')" title="Measure Mode">
        <i class="fa-solid fa-ruler" />
      </button>
      <span class="sb-sep">·</span>
      <!-- Zoom controls -->
      <button class="sb-btn zoom-btn" @click="$emit('zoom-to', Math.max(25, zoom - 10))"><i class="fa-solid fa-minus" /></button>
      <button class="zoom-display" @click="$emit('zoom-reset')" title="Reset zoom (100%)">{{ zoom }}%</button>
      <button class="sb-btn zoom-btn" @click="$emit('zoom-to', Math.min(400, zoom + 10))"><i class="fa-solid fa-plus" /></button>
      <span class="sb-sep">·</span>
      <span class="sb-item">{{ pageSize }} {{ orientation }}</span>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps({
  currentPage: { type: Number, default: 0 },
  totalPages: { type: Number, default: 1 },
  elementsCount: { type: Number, default: 0 },
  selectedEl: { type: Object, default: null },
  selectedCount: { type: Number, default: 0 },
  zoom: { type: Number, default: 100 },
  isDirty: { type: Boolean, default: false },
  isSaving: { type: Boolean, default: false },
  lastSaved: { type: String, default: '' },
  pageSize: { type: String, default: 'A4' },
  orientation: { type: String, default: 'portrait' },
  wordsCount: { type: Number, default: 0 },
  isDark: { type: Boolean, default: false },
  gridSize: { type: Number, default: 10 },
  snapToGrid: { type: Boolean, default: true },
  measureMode: { type: Boolean, default: false },
})
const emit = defineEmits(['zoom-reset', 'zoom-to', 'toggle-snap', 'toggle-measure', 'update-grid-size'])
const GRID_SIZES = [5, 10, 20, 40]
function cycleGrid() {
  const idx = GRID_SIZES.indexOf(props.gridSize)
  emit('update-grid-size', GRID_SIZES[(idx + 1) % GRID_SIZES.length])
}
</script>

<style scoped>
.status-bar {
  height: 26px; display: flex; align-items: center; justify-content: space-between;
  padding: 0 12px; background: var(--bg-panel,#fff); border-top: 1px solid var(--border,#e2e8f0);
  font-size: 10px; color: var(--text-muted,#94a3b8); flex-shrink: 0; user-select: none; gap: 8px;
}
.sb-left, .sb-right { display: flex; align-items: center; gap: 5px; }
.sb-center { flex: 1; text-align: center; }
.sb-item { display: flex; align-items: center; gap: 3px; font-weight: 500; white-space: nowrap; }
.sb-item i { font-size: 9px; opacity: .6; }
.sb-item.accent { color: var(--accent,#6366f1); }
.sb-sep { opacity: .3; }
.save-state { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; }
.save-state.saving { color: var(--accent,#6366f1); }
.save-state.saved { color: var(--success,#10b981); }
.save-state.unsaved { color: var(--warning,#f59e0b); }
.save-state.ready { color: var(--text-muted,#94a3b8); }
.pulse-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--warning,#f59e0b); display: inline-block; animation: sbPulse 1.5s ease-in-out infinite; }
@keyframes sbPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.4)} }
.sb-btn { background: transparent; border: none; cursor: pointer; color: var(--text-muted,#94a3b8); padding: 2px 5px; border-radius: 4px; font-size: 10px; font-weight: 500; display: flex; align-items: center; gap: 3px; transition: all .15s; font-family: inherit; }
.sb-btn:hover { background: var(--bg-secondary,#f8fafc); color: var(--text-primary,#0f172a); }
.sb-btn.active { color: var(--accent,#6366f1); background: var(--accent-light,rgba(99,102,241,.08)); }
.sb-btn.zoom-btn { width: 20px; height: 20px; padding: 0; justify-content: center; }
.zoom-display { background: transparent; border: none; cursor: pointer; font-size: 10px; font-weight: 700; color: var(--accent,#6366f1); padding: 2px 5px; border-radius: 4px; font-family: inherit; min-width: 36px; }
.zoom-display:hover { background: var(--accent-light,rgba(99,102,241,.08)); }
</style>