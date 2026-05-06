<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   StatusBar - Bottom Status Bar                                  ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <footer class="status-bar">
    <div class="status-left">
      <span class="status-item">
        <i class="fa-solid fa-file"></i>
        Page {{ currentPage + 1 }} / {{ totalPages }}
      </span>
      <span class="status-sep">·</span>
      <span class="status-item">
        <i class="fa-solid fa-cubes"></i>
        {{ totalElements }} elements
      </span>
      <span class="status-sep">·</span>
      <span class="status-item">{{ pageSize }} {{ orientation }}</span>
      <span v-if="selectedEl" class="status-sep">·</span>
      <span v-if="selectedEl" class="status-item">
        <i class="fa-solid fa-cube"></i>
        {{ selectedEl.type }}
      </span>
    </div>
    
    <div class="status-center">
      <span class="save-status" :class="{ saved: lastSaved && !isDirty, saving: isSaving, unsaved: isDirty }">
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
          <i class="fa-solid fa-check"></i> All saved
        </template>
      </span>
    </div>
    
    <div class="status-right">
      <span class="status-item" @click="$emit('zoom-reset')" title="Reset Zoom (100%)">
        {{ zoom }}%
      </span>
      <span class="status-sep">·</span>
      <span class="status-item">{{ elementsCount }} el on page</span>
    </div>
  </footer>
</template>

<script setup>
defineProps({
  currentPage: Number,
  totalPages: Number,
  elementsCount: Number,
  totalElements: Number,
  selectedEl: Object,
  zoom: Number,
  isDirty: Boolean,
  isSaving: Boolean,
  lastSaved: String,
  pageSize: String,
  orientation: String,
})

defineEmits(['zoom-reset'])
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
}

.status-left, .status-right {
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
}

.status-item i { font-size: 9px; opacity: 0.6; }

.status-sep { opacity: 0.3; }

.save-status {
  font-weight: 600;
  font-size: 10px;
}

.save-status.saved { color: var(--success, #10b981); }
.save-status.saving { color: var(--accent, #6366f1); }
.save-status.unsaved { color: var(--warning, #f59e0b); }

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