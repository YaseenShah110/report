<!--
  ShortcutOverlay.vue — Keyboard Shortcuts Reference
  • Full-screen modal with blurred backdrop
  • Grouped by category with icons
  • Search/filter shortcuts live
  • Dark mode aware
  • Closes on Escape or backdrop click
  • Memory safe
-->
<template>
  <Teleport to="body">
    <Transition name="so-fade">
      <div v-if="visible" class="so-backdrop" @click.self="$emit('close')" role="dialog" aria-modal="true"
        aria-label="Keyboard shortcuts">
        <div class="so-shell" :class="isDark && 'so-dark'">

          <!-- Header -->
          <div class="so-header">
            <div class="so-header-left">
              <div class="so-icon-badge" aria-hidden="true"><i class="fa-solid fa-keyboard" /></div>
              <div>
                <h2 class="so-title">Keyboard Shortcuts</h2>
                <p class="so-subtitle">{{ filteredCount }} shortcuts available</p>
              </div>
            </div>
            <div class="so-header-right">
              <div class="so-search-box">
                <i class="fa-solid fa-magnifying-glass so-search-icon" aria-hidden="true" />
                <input v-model="search" class="so-search" placeholder="Search shortcuts…" type="search"
                  aria-label="Search keyboard shortcuts" ref="searchRef" />
              </div>
              <button class="so-close" @click="$emit('close')" aria-label="Close shortcuts overlay">
                <i class="fa-solid fa-xmark" />
              </button>
            </div>
          </div>

          <!-- Grid of groups -->
          <div class="so-body" role="list">
            <div v-for="group in filteredGroups" :key="group.name" class="so-group" role="listitem">
              <div class="so-group-header">
                <span class="so-group-icon" :style="{ background: group.color + '18', color: group.color }"
                  aria-hidden="true">
                  <i :class="group.icon" />
                </span>
                <span class="so-group-name">{{ group.name }}</span>
                <span class="so-group-count">{{ group.shortcuts.length }}</span>
              </div>
              <div class="so-group-rows">
                <div v-for="sc in group.shortcuts" :key="sc.label" class="so-row">
                  <span class="so-label">{{ sc.label }}</span>
                  <div class="so-keys" aria-label="Keys">
                    <kbd v-for="k in sc.keys" :key="k" class="so-key">{{ k }}</kbd>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty state -->
            <div v-if="!filteredGroups.length" class="so-empty" role="status">
              <i class="fa-solid fa-magnifying-glass" aria-hidden="true" />
              <p>No shortcuts found for <strong>"{{ search }}"</strong></p>
            </div>
          </div>

          <!-- Footer -->
          <div class="so-footer" aria-hidden="true">
            <span class="so-footer-tip"><kbd>Ctrl</kbd><kbd>Alt</kbd><kbd>/</kbd> Open anytime</span>
            <span class="so-footer-tip"><kbd>Esc</kbd> Close</span>
            <span class="so-footer-note">All shortcuts use <kbd>Ctrl+Alt</kbd> prefix to avoid browser conflicts</span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  visible: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
})
const emit = defineEmits(['close'])

const search = ref('')
const searchRef = ref(null)

const GROUPS = [
  {
    name: 'File', icon: 'fa-solid fa-file', color: '#6366f1',
    shortcuts: [
      { label: 'Save report', keys: ['Ctrl', 'Alt', 'S'] },
      { label: 'Export as PDF', keys: ['Ctrl', 'Alt', 'E'] },
      { label: 'Preview report', keys: ['Ctrl', 'Alt', 'V'] },
      { label: 'Print preview', keys: ['Ctrl', 'P'] },
    ],
  },
  {
    name: 'Edit', icon: 'fa-solid fa-pen', color: '#f59e0b',
    shortcuts: [
      { label: 'Undo', keys: ['Ctrl', 'Alt', 'Z'] },
      { label: 'Redo', keys: ['Ctrl', 'Alt', 'Y'] },
      { label: 'Copy element', keys: ['Ctrl', 'Alt', 'C'] },
      { label: 'Paste element', keys: ['Ctrl', 'Alt', 'V'] },
      { label: 'Duplicate element', keys: ['Ctrl', 'Alt', 'D'] },
      { label: 'Delete element', keys: ['Del'] },
      { label: 'Select all', keys: ['Ctrl', 'Alt', 'A'] },
      { label: 'Deselect', keys: ['Esc'] },
      { label: 'Find & Replace', keys: ['Ctrl', 'Alt', 'F'] },
    ],
  },
  {
    name: 'View', icon: 'fa-solid fa-eye', color: '#10b981',
    shortcuts: [
      { label: 'Toggle grid', keys: ['Ctrl', 'Alt', 'G'] },
      { label: 'Toggle snap to grid', keys: ['Ctrl', 'Alt', 'X'] },
      { label: 'Toggle rulers', keys: ['Ctrl', 'Alt', 'R'] },
      { label: 'Toggle measure tool', keys: ['Ctrl', 'Alt', 'M'] },
      { label: 'Toggle dark mode', keys: ['Ctrl', 'Alt', 'D'] },
      { label: 'Toggle fullscreen', keys: ['F11'] },
      { label: 'Toggle left panel', keys: ['Ctrl', 'Alt', 'L'] },
      { label: 'Toggle right panel', keys: ['Ctrl', 'Alt', '\\'] },
    ],
  },
  {
    name: 'Zoom', icon: 'fa-solid fa-magnifying-glass', color: '#06b6d4',
    shortcuts: [
      { label: 'Zoom in', keys: ['Ctrl', 'Alt', '='] },
      { label: 'Zoom out', keys: ['Ctrl', 'Alt', '-'] },
      { label: 'Reset zoom (100%)', keys: ['Ctrl', 'Alt', '0'] },
      { label: 'Zoom with scroll wheel', keys: ['Ctrl', 'Alt', '⬆⬇'] },
    ],
  },
  {
    name: 'Format', icon: 'fa-solid fa-bold', color: '#ec4899',
    shortcuts: [
      { label: 'Bold text', keys: ['Ctrl', 'Alt', 'B'] },
      { label: 'Italic text', keys: ['Ctrl', 'Alt', 'I'] },
      { label: 'Underline text', keys: ['Ctrl', 'Alt', 'U'] },
      { label: 'Bring to front', keys: ['Ctrl', 'Alt', ']'] },
      { label: 'Send to back', keys: ['Ctrl', 'Alt', '['] },
    ],
  },
  {
    name: 'Pages', icon: 'fa-solid fa-file-circle-plus', color: '#8b5cf6',
    shortcuts: [
      { label: 'Add new page', keys: ['Ctrl', 'Alt', 'N'] },
      { label: 'Next page', keys: ['Ctrl', 'Alt', '→'] },
      { label: 'Previous page', keys: ['Ctrl', 'Alt', '←'] },
    ],
  },
  {
    name: 'Tools', icon: 'fa-solid fa-wand-magic-sparkles', color: '#f97316',
    shortcuts: [
      { label: 'Open command palette', keys: ['Ctrl', 'K'] },
      { label: 'Toggle AI assistant', keys: ['Ctrl', 'Alt', 'A'] },
      { label: 'Presentation mode', keys: ['Ctrl', 'Alt', 'P'] },
      { label: 'Show shortcuts', keys: ['Ctrl', 'Alt', '/'] },
    ],
  },
  {
    name: 'Canvas', icon: 'fa-solid fa-vector-square', color: '#3b82f6',
    shortcuts: [
      { label: 'Drag canvas', keys: ['Space', 'Drag'] },
      { label: 'Multi-select (rubber band)', keys: ['Drag on empty area'] },
      { label: 'Constrain proportions', keys: ['Shift', 'Resize'] },
      { label: 'Nudge element 1px', keys: ['Arrow keys'] },
      { label: 'Nudge element 10px', keys: ['Shift', 'Arrow'] },
    ],
  },
]

const filteredGroups = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return GROUPS
  return GROUPS
    .map(g => ({
      ...g,
      shortcuts: g.shortcuts.filter(s => s.label.toLowerCase().includes(q) || g.name.toLowerCase().includes(q)),
    }))
    .filter(g => g.shortcuts.length)
})

const filteredCount = computed(() =>
  filteredGroups.value.reduce((s, g) => s + g.shortcuts.length, 0)
)

// Keyboard: Esc to close
function onKeyDown(e) { if (e.key === 'Escape') emit('close') }
onMounted(() => { document.addEventListener('keydown', onKeyDown); nextTick(() => searchRef.value?.focus()) })
onBeforeUnmount(() => document.removeEventListener('keydown', onKeyDown))

watch(() => props.visible, v => {
  if (v) nextTick(() => searchRef.value?.focus())
})
</script>

<style scoped>
.so-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9500;
  background: rgba(0, 0, 0, .55);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.so-shell {
  --so-bg: #ffffff;
  --so-bg2: #f8fafc;
  --so-border: #e2e8f0;
  --so-text: #0f172a;
  --so-text2: #475569;
  --so-text3: #94a3b8;
  --so-accent: #6366f1;
  --so-key-bg: #f1f5f9;
  --so-key-br: #cbd5e1;

  width: 900px;
  max-width: 100%;
  max-height: 88vh;
  background: var(--so-bg);
  border-radius: 20px;
  border: 1px solid var(--so-border);
  box-shadow: 0 32px 100px rgba(0, 0, 0, .22);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.so-dark {
  --so-bg: #1a2236;
  --so-bg2: #111827;
  --so-border: #263348;
  --so-text: #e2e8f0;
  --so-text2: #94a3b8;
  --so-text3: #475569;
  --so-key-bg: #263348;
  --so-key-br: #334155;
}

/* Header */
.so-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 16px;
  border-bottom: 1px solid var(--so-border);
  gap: 12px;
  flex-shrink: 0;
}

.so-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.so-header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.so-icon-badge {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  flex-shrink: 0;
}

.so-title {
  font-size: 16px;
  font-weight: 800;
  color: var(--so-text);
  margin: 0;
}

.so-subtitle {
  font-size: 11px;
  color: var(--so-text3);
  margin: 2px 0 0;
}

.so-search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: var(--so-bg2);
  border: 1px solid var(--so-border);
  border-radius: 10px;
}

.so-search-icon {
  color: var(--so-text3);
  font-size: 12px;
}

.so-search {
  border: none;
  outline: none;
  background: transparent;
  font-size: 13px;
  color: var(--so-text);
  font-family: inherit;
  width: 180px;
}

.so-search::placeholder {
  color: var(--so-text3);
}

.so-close {
  width: 34px;
  height: 34px;
  border: 1px solid var(--so-border);
  border-radius: 9px;
  background: transparent;
  cursor: pointer;
  color: var(--so-text3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  transition: all .14s;
}

.so-close:hover {
  background: #fee2e2;
  color: #ef4444;
  border-color: #fca5a5;
}

/* Body */
.so-body {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
  gap: 12px;
  padding: 16px 20px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--so-border) transparent;
}

.so-body::-webkit-scrollbar {
  width: 4px;
}

.so-body::-webkit-scrollbar-thumb {
  background: var(--so-border);
  border-radius: 99px;
}

/* Group */
.so-group {
  background: var(--so-bg2);
  border: 1px solid var(--so-border);
  border-radius: 14px;
  overflow: hidden;
}

.so-group-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 14px 8px;
}

.so-group-icon {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  flex-shrink: 0;
}

.so-group-name {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: var(--so-text);
  flex: 1;
}

.so-group-count {
  font-size: 9px;
  font-weight: 700;
  color: var(--so-text3);
  background: var(--so-bg);
  border: 1px solid var(--so-border);
  border-radius: 99px;
  padding: 1px 7px;
}

.so-group-rows {
  padding: 0 10px 10px;
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.so-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 6px;
  border-radius: 7px;
  gap: 10px;
}

.so-row:hover {
  background: var(--so-bg);
}

.so-label {
  font-size: 11.5px;
  color: var(--so-text2);
  flex: 1;
}

.so-keys {
  display: flex;
  gap: 3px;
  flex-shrink: 0;
}

.so-key {
  font-size: 10px;
  font-weight: 700;
  color: var(--so-text2);
  background: var(--so-key-bg);
  border: 1px solid var(--so-key-br);
  border-bottom-width: 2px;
  border-radius: 5px;
  padding: 2px 7px;
  font-family: inherit;
  white-space: nowrap;
}

/* Empty */
.so-empty {
  grid-column: 1/-1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 50px 20px;
  text-align: center;
  color: var(--so-text3);
  font-size: 13px;
}

.so-empty i {
  font-size: 30px;
  opacity: .3;
}

.so-empty strong {
  color: var(--so-text2);
}

/* Footer */
.so-footer {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 10px 24px;
  border-top: 1px solid var(--so-border);
  background: var(--so-bg2);
  flex-shrink: 0;
  font-size: 10px;
  color: var(--so-text3);
  flex-wrap: wrap;
}

.so-footer-tip {
  display: flex;
  align-items: center;
  gap: 4px;
}

.so-footer-note {
  margin-left: auto;
  font-style: italic;
}

.so-footer kbd {
  font-size: 9px;
  padding: 1px 5px;
  background: var(--so-key-bg);
  border: 1px solid var(--so-key-br);
  border-radius: 4px;
  font-family: inherit;
}

/* Transition */
.so-fade-enter-active {
  animation: soIn .2s cubic-bezier(.16, 1, .3, 1);
}

.so-fade-leave-active {
  animation: soIn .15s ease reverse;
}

@keyframes soIn {
  from {
    opacity: 0;
    transform: scale(.96);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>