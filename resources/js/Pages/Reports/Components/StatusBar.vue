<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   StatusBar.vue - Bottom Status Bar with Live Stats            ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <footer class="status-bar">
    <div class="status-left">
      <span class="status-item" title="Current Page">
        <i class="fa-solid fa-file"></i>
        Page {{ currentPage + 1 }} / {{ totalPages }}
      </span>
      <span class="status-sep">·</span>
      <span class="status-item" title="Total Elements">
        <i class="fa-solid fa-cubes"></i>
        {{ totalElements }} elements
      </span>
      <span class="status-sep">·</span>
      <span class="status-item" title="Page Size">
        {{ pageSize }} {{ orientation }}
      </span>
      <template v-if="selectedEl">
        <span class="status-sep">·</span>
        <span class="status-item" title="Selected Element">
          <i class="fa-solid fa-cube"></i>
          {{ selectedEl.type }}
        </span>
      </template>
      <template v-if="wordsCount > 0">
        <span class="status-sep">·</span>
        <span class="status-item" :title="charsCount + ' characters'">
          <i class="fa-solid fa-text-height"></i>
          {{ wordsCount }} words
        </span>
      </template>
    </div>

    <div class="status-center">
      <span class="save-status" :class="saveStateClass">
        <template v-if="isSaving">
          <i class="fa-solid fa-spinner fa-spin"></i> Saving...
        </template>
        <template v-else-if="lastSaved && !isDirty">
          <i class="fa-solid fa-check-circle"></i> Saved {{ lastSaved }}
        </template>
        <template v-else-if="isDirty">
          <span class="pulse-dot"></span> Unsaved changes
        </template>
        <template v-else>
          <i class="fa-solid fa-check"></i> Ready
        </template>
      </span>
    </div>

    <div class="status-right">
      <span class="status-item clickable" @click="$emit('zoom-reset')" title="Reset Zoom (100%)">
        {{ zoom }}%
      </span>
      <span class="status-sep">·</span>
      <span class="status-item">
        {{ elementsCount }} el on page
      </span>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, default: 0 },
  totalPages: { type: Number, default: 1 },
  elementsCount: { type: Number, default: 0 },
  totalElements: { type: Number, default: 0 },
  selectedEl: { type: Object, default: null },
  zoom: { type: Number, default: 100 },
  isDirty: { type: Boolean, default: false },
  isSaving: { type: Boolean, default: false },
  lastSaved: { type: String, default: '' },
  pageSize: { type: String, default: 'A4' },
  orientation: { type: String, default: 'portrait' },
  wordsCount: { type: Number, default: 0 },
  charsCount: { type: Number, default: 0 },
  isDark: { type: Boolean, default: false },
})

defineEmits(['zoom-reset'])

const saveStateClass = computed(() => ({
  saved: props.lastSaved && !props.isDirty,
  saving: props.isSaving,
  unsaved: props.isDirty,
  ready: !props.isDirty && !props.lastSaved,
}))
</script>

<style scoped>
.status-bar {
  height: 28px;
  padding: 0 14px;
  background: var(--bg-panel, #ffffff);
  border-top: 1px solid var(--border, #e2e8f0);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 10px;
  color: var(--text-muted, #94a3b8);
  flex-shrink: 0;
  user-select: none;
  gap: 12px;
  z-index: 50;
}

.status-left,
.status-right {
  display: flex;
  align-items: center;
  gap: 4px;
}

.status-center {
  flex: 1;
  text-align: center;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 500;
  cursor: default;
  white-space: nowrap;
}

.status-item i {
  font-size: 9px;
  opacity: 0.6;
}

.status-item.clickable {
  cursor: pointer;
  transition: color 0.15s;
}

.status-item.clickable:hover {
  color: var(--accent, #6366f1);
}

.status-sep {
  opacity: 0.3;
}

.save-status {
  font-weight: 600;
  font-size: 10px;
  display: flex;
  align-items: center;
  gap: 5px;
  justify-content: center;
}

.save-status.saved {
  color: var(--success, #10b981);
}

.save-status.saving {
  color: var(--accent, #6366f1);
}

.save-status.unsaved {
  color: var(--warning, #f59e0b);
}

.save-status.ready {
  color: var(--text-muted, #94a3b8);
}

.pulse-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--warning, #f59e0b);
  display: inline-block;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}
</style>