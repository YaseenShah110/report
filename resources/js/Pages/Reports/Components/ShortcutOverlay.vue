<!--
  ShortcutOverlay.vue — Keyboard Shortcut Reference
  ═══════════════════════════════════════════════════════════════════
  Shows all editor keyboard shortcuts in a searchable, categorized
  modal. Opened by:
    • Ctrl+Alt+? or Ctrl+? (from Editor.vue shortcut handler)
    • The "Shortcuts" entry in the Command Palette
    • Clicking "Keyboard Shortcuts" in the TopToolbar help menu

  All shortcuts use Ctrl+Alt+* to avoid browser conflicts (except
  Ctrl+K for command palette, which is safe).
  ═══════════════════════════════════════════════════════════════════
-->
<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <div class="so-backdrop" @click.self="$emit('close')" aria-modal="true" role="dialog"
      aria-label="Keyboard shortcuts">

      <div class="so-panel" :class="{ 'so-dark': isDark }">

        <!-- Header -->
        <div class="so-header">
          <div class="so-header-left">
            <i class="fa-solid fa-keyboard so-header-icon" aria-hidden="true" />
            <div>
              <h2 class="so-title">Keyboard Shortcuts</h2>
              <p class="so-subtitle">All shortcuts use <kbd>Ctrl+Alt</kbd> prefix to avoid browser conflicts</p>
            </div>
          </div>
          <button class="so-close" @click="$emit('close')" aria-label="Close shortcuts overlay">
            <i class="fa-solid fa-xmark" />
          </button>
        </div>

        <!-- Search -->
        <div class="so-search-wrap">
          <i class="fa-solid fa-magnifying-glass so-search-icon" aria-hidden="true" />
          <input ref="searchRef" v-model="query" placeholder="Search shortcuts…" class="so-search"
            aria-label="Search keyboard shortcuts" />
          <button v-if="query" class="so-search-clear" @click="query = ''" aria-label="Clear search">
            <i class="fa-solid fa-xmark" />
          </button>
        </div>

        <!-- Shortcut groups -->
        <div class="so-body">
          <template v-for="group in filteredGroups" :key="group.label">
            <div class="so-group">
              <div class="so-group-label">
                <i :class="group.icon" aria-hidden="true" />
                {{ group.label }}
              </div>
              <div class="so-grid">
                <div v-for="sc in group.items" :key="sc.keys.join()" class="so-row">
                  <span class="so-desc">{{ sc.desc }}</span>
                  <div class="so-keys">
                    <kbd v-for="k in sc.keys" :key="k" class="so-key">{{ k }}</kbd>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <div v-if="filteredGroups.length === 0" class="so-empty">
            <i class="fa-solid fa-face-meh" />
            <span>No shortcuts match "{{ query }}"</span>
          </div>
        </div>

        <!-- Footer tip -->
        <div class="so-footer">
          <i class="fa-solid fa-circle-info" />
          Press <kbd>Esc</kbd> to close · Press <kbd>Ctrl+K</kbd> to open the command palette
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'

const props = defineProps({
  isDark: { type: Boolean, default: false },
})

defineEmits(['close'])

const query = ref('')
const searchRef = ref(null)

// ── All shortcuts ─────────────────────────────────────────────────
const GROUPS = [
  {
    label: 'History',
    icon: 'fa-solid fa-clock-rotate-left',
    items: [
      { desc: 'Undo', keys: ['Ctrl', 'Alt', 'Z'] },
      { desc: 'Redo', keys: ['Ctrl', 'Alt', 'Y'] },
    ],
  },
  {
    label: 'Zoom & View',
    icon: 'fa-solid fa-magnifying-glass',
    items: [
      { desc: 'Zoom In', keys: ['Ctrl', 'Alt', '='] },
      { desc: 'Zoom Out', keys: ['Ctrl', 'Alt', '-'] },
      { desc: 'Reset Zoom 100%', keys: ['Ctrl', 'Alt', '0'] },
      { desc: 'Ctrl+Scroll', keys: ['Ctrl', 'Scroll'] },
      { desc: 'Toggle Grid', keys: ['Ctrl', 'Alt', 'G'] },
      { desc: 'Snap to Grid', keys: ['Ctrl', 'Alt', 'S'] },
      { desc: 'Toggle Rulers', keys: ['Ctrl', 'Alt', 'R'] },
      { desc: 'Toggle Dark Mode', keys: ['Ctrl', 'Alt', 'D'] },
      { desc: 'Full Screen', keys: ['F11'] },
      { desc: 'Left Panel', keys: ['Ctrl', 'Alt', 'L'] },
    ],
  },
  {
    label: 'Selection & Elements',
    icon: 'fa-solid fa-arrow-pointer',
    items: [
      { desc: 'Select All', keys: ['Ctrl', 'A'] },
      { desc: 'Multi-select', keys: ['Shift', 'Click'] },
      { desc: 'Delete element', keys: ['Del'] },
      { desc: 'Duplicate element', keys: ['Ctrl', 'Alt', 'Q'] },
      { desc: 'Bring to Front', keys: ['Ctrl', 'Alt', 'B'] },
      { desc: 'Send to Back', keys: ['Ctrl', 'Alt', 'E'] },
      { desc: 'Lock / Unlock', keys: ['Ctrl', 'Alt', 'K'] },
      { desc: 'Copy Style', keys: ['Ctrl', 'Alt', 'C'] },
      { desc: 'Paste Style', keys: ['Ctrl', 'Alt', 'X'] },
      { desc: 'Deselect / Cancel', keys: ['Esc'] },
    ],
  },
  {
    label: 'Text Formatting',
    icon: 'fa-solid fa-font',
    items: [
      { desc: 'Bold', keys: ['Ctrl', 'Alt', 'B'] },
      { desc: 'Italic', keys: ['Ctrl', 'Alt', 'I'] },
      { desc: 'Underline', keys: ['Ctrl', 'Alt', 'U'] },
      { desc: 'Align Left', keys: ['Ctrl', 'Alt', '['] },
      { desc: 'Align Center', keys: ['Ctrl', 'Alt', '\\'] },
      { desc: 'Align Right', keys: ['Ctrl', 'Alt', ']'] },
      { desc: 'Start editing', keys: ['Enter'] },
      { desc: 'Stop editing', keys: ['Esc'] },
    ],
  },
  {
    label: 'Pages',
    icon: 'fa-solid fa-book-open',
    items: [
      { desc: 'Add Page', keys: ['Ctrl', 'Alt', 'N'] },
      { desc: 'Previous Page', keys: ['Ctrl', 'Alt', '↑'] },
      { desc: 'Next Page', keys: ['Ctrl', 'Alt', '↓'] },
      { desc: 'Duplicate Page', keys: ['Ctrl', 'Alt', 'P'] },
    ],
  },
  {
    label: 'File & Export',
    icon: 'fa-solid fa-file-export',
    items: [
      { desc: 'Save', keys: ['Ctrl', 'Alt', 'S'] },
      { desc: 'Preview', keys: ['Ctrl', 'Alt', 'V'] },
      { desc: 'Print', keys: ['Ctrl', 'Alt', 'P'] },
      { desc: 'Export PDF', keys: ['Ctrl', 'Alt', 'E'] },
    ],
  },
  {
    label: 'Tools & Panels',
    icon: 'fa-solid fa-toolbox',
    items: [
      { desc: 'Command Palette', keys: ['Ctrl', 'K'] },
      { desc: 'AI Assistant', keys: ['Ctrl', 'Alt', 'A'] },
      { desc: 'Find & Replace', keys: ['Ctrl', 'Alt', 'F'] },
      { desc: 'This help screen', keys: ['Ctrl', 'Alt', '?'] },
    ],
  },
]

const filteredGroups = computed(() => {
  if (!query.value.trim()) return GROUPS
  const q = query.value.toLowerCase()
  return GROUPS.map(g => ({
    ...g,
    items: g.items.filter(s =>
      s.desc.toLowerCase().includes(q) ||
      s.keys.some(k => k.toLowerCase().includes(q))
    ),
  })).filter(g => g.items.length)
})

// ── Close on ESC ──────────────────────────────────────────────────
function onKey(e) {
  if (e.key === 'Escape') emit('close')
}

onMounted(async () => {
  document.addEventListener('keydown', onKey)
  await nextTick()
  searchRef.value?.focus()
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKey)
})

const emit = defineEmits(['close'])
</script>

<style scoped>
/* ═══ BACKDROP ═══════════════════════════════════════════════════════ */
.so-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9500;
  background: rgba(0, 0, 0, .55);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  animation: fadeIn .18s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0
  }

  to {
    opacity: 1
  }
}

/* ═══ PANEL ══════════════════════════════════════════════════════════ */
.so-panel {
  --so-bg: #ffffff;
  --so-bg2: #f8fafc;
  --so-border: #e2e8f0;
  --so-text: #0f172a;
  --so-text2: #475569;
  --so-text3: #94a3b8;
  --so-accent: #6366f1;
  --so-accent-l: rgba(99, 102, 241, .08);

  background: var(--so-bg);
  border: 1px solid var(--so-border);
  border-radius: 16px;
  box-shadow: 0 24px 80px rgba(0, 0, 0, .22);
  width: 100%;
  max-width: 760px;
  max-height: 88vh;
  display: flex;
  flex-direction: column;
  animation: panelIn .2s cubic-bezier(.16, 1, .3, 1);
  overflow: hidden;
}

.so-dark {
  --so-bg: #111827;
  --so-bg2: #1a2236;
  --so-border: #1e2d45;
  --so-text: #e2e8f0;
  --so-text2: #94a3b8;
  --so-text3: #475569;
  --so-accent: #818cf8;
  --so-accent-l: rgba(129, 140, 248, .1);
}

@keyframes panelIn {
  from {
    opacity: 0;
    transform: scale(.96) translateY(10px);
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* ═══ HEADER ═════════════════════════════════════════════════════════ */
.so-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 16px;
  border-bottom: 1px solid var(--so-border);
  flex-shrink: 0;
}

.so-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.so-header-icon {
  font-size: 22px;
  color: var(--so-accent);
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
  margin: 3px 0 0;
}

.so-close {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  border: 1px solid var(--so-border);
  background: transparent;
  cursor: pointer;
  color: var(--so-text2);
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
}

.so-close:hover {
  background: var(--so-bg2);
  color: var(--so-text);
}

/* ═══ SEARCH ═════════════════════════════════════════════════════════ */
.so-search-wrap {
  position: relative;
  padding: 12px 24px;
  border-bottom: 1px solid var(--so-border);
  flex-shrink: 0;
}

.so-search {
  width: 100%;
  padding: 9px 36px;
  border: 1px solid var(--so-border);
  border-radius: 9px;
  background: var(--so-bg2);
  color: var(--so-text);
  font-size: 13px;
  outline: none;
  font-family: inherit;
  transition: border-color .14s;
}

.so-search:focus {
  border-color: var(--so-accent);
}

.so-search::placeholder {
  color: var(--so-text3);
}

.so-search-icon {
  position: absolute;
  left: 37px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--so-text3);
  font-size: 12px;
  pointer-events: none;
}

.so-search-clear {
  position: absolute;
  right: 37px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  color: var(--so-text3);
  cursor: pointer;
  font-size: 12px;
  padding: 3px;
}

.so-search-clear:hover {
  color: var(--so-text);
}

/* ═══ BODY ═══════════════════════════════════════════════════════════ */
.so-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px 24px 8px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  scrollbar-width: thin;
  scrollbar-color: var(--so-border) transparent;
}

/* ═══ GROUP ══════════════════════════════════════════════════════════ */
.so-group-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: var(--so-text3);
  margin-bottom: 8px;
}

.so-grid {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

/* ═══ ROW ════════════════════════════════════════════════════════════ */
.so-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 7px 10px;
  border-radius: 7px;
  gap: 16px;
  transition: background .1s;
}

.so-row:hover {
  background: var(--so-accent-l);
}

.so-desc {
  font-size: 12px;
  color: var(--so-text);
  font-weight: 500;
  flex: 1;
}

.so-keys {
  display: flex;
  align-items: center;
  gap: 3px;
  flex-shrink: 0;
}

.so-key {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 24px;
  padding: 0 7px;
  background: var(--so-bg2);
  border: 1px solid var(--so-border);
  border-bottom-width: 2px;
  border-radius: 5px;
  font-family: inherit;
  font-size: 10px;
  font-weight: 700;
  color: var(--so-text2);
  white-space: nowrap;
  box-shadow: 0 1px 0 var(--so-border);
}

/* ═══ FOOTER ═════════════════════════════════════════════════════════ */
.so-footer {
  padding: 12px 24px;
  border-top: 1px solid var(--so-border);
  font-size: 11px;
  color: var(--so-text3);
  display: flex;
  align-items: center;
  gap: 7px;
  flex-shrink: 0;
}

.so-footer kbd {
  font-family: inherit;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 4px;
  background: var(--so-bg2);
  border: 1px solid var(--so-border);
  color: var(--so-text2);
}

/* ═══ EMPTY ══════════════════════════════════════════════════════════ */
.so-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 48px;
  color: var(--so-text3);
  text-align: center;
  font-size: 13px;
}

.so-empty i {
  font-size: 28px;
  opacity: .3;
}

/* ═══ RESPONSIVE ═════════════════════════════════════════════════════ */
@media (max-width: 600px) {
  .so-panel {
    max-width: 100%;
    border-radius: 12px;
  }

  .so-grid {
    gap: 4px;
  }

  .so-key {
    min-width: 24px;
    font-size: 9px;
  }
}
</style>