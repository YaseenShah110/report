<!--
  CommandPalette.vue — Editor Command Palette (Ctrl+K)
  ═══════════════════════════════════════════════════════════════════
  40+ commands across 7 categories, fuzzy-searched in real time.
  Keyboard-only operable: ↑↓ to move, Enter to execute, Esc to close.
  Recent commands tracked in localStorage (last 5 shown at top).
  Emits: @execute(commandId)  @close
  ═══════════════════════════════════════════════════════════════════
-->
<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <div class="cp-backdrop" @click.self="$emit('close')" role="dialog" aria-modal="true"
            aria-label="Command palette">

            <div class="cp-panel" :class="{ 'cp-dark': isDark }">

                <!-- Search input -->
                <div class="cp-search-wrap">
                    <i class="fa-solid fa-terminal cp-search-icon" aria-hidden="true" />
                    <input ref="inputRef" v-model="query" class="cp-input" placeholder="Search commands…"
                        aria-label="Search commands" autocomplete="off" spellcheck="false"
                        @keydown.arrow-down.prevent="moveDown" @keydown.arrow-up.prevent="moveUp"
                        @keydown.enter.prevent="runActive" @keydown.escape.prevent="$emit('close')" />
                    <kbd class="cp-esc-hint">esc</kbd>
                </div>

                <!-- Results list -->
                <div class="cp-results" ref="listRef" role="listbox" aria-label="Commands">

                    <!-- Recent (only when no query) -->
                    <template v-if="!query.trim() && recentCommands.length">
                        <div class="cp-group-label">Recent</div>
                        <button v-for="cmd in recentList" :key="`recent-${cmd.id}`" class="cp-item"
                            :class="{ 'cp-item-active': activeIndex === getGlobalIdx(`recent-${cmd.id}`) }"
                            @mouseenter="setActive(getGlobalIdx(`recent-${cmd.id}`))" @click="run(cmd)" role="option"
                            :aria-selected="activeIndex === getGlobalIdx(`recent-${cmd.id}`)">
                            <div class="cp-item-icon" :style="{ background: cmd.color + '18', color: cmd.color }">
                                <i :class="cmd.icon" />
                            </div>
                            <div class="cp-item-body">
                                <span class="cp-item-name">{{ cmd.name }}</span>
                                <span class="cp-item-cat">{{ cmd.category }}</span>
                            </div>
                            <kbd v-if="cmd.shortcut" class="cp-shortcut">{{ cmd.shortcut }}</kbd>
                        </button>
                        <div class="cp-sep" />
                    </template>

                    <!-- Grouped results -->
                    <template v-for="group in visibleGroups" :key="group.category">
                        <div class="cp-group-label">
                            <i :class="group.icon" />
                            {{ group.category }}
                        </div>
                        <button v-for="cmd in group.items" :key="cmd.id" class="cp-item"
                            :class="{ 'cp-item-active': activeIndex === cmd.__globalIdx }"
                            @mouseenter="setActive(cmd.__globalIdx)" @click="run(cmd)" role="option"
                            :aria-selected="activeIndex === cmd.__globalIdx"
                            :ref="el => { if (activeIndex === cmd.__globalIdx) activeEl = el }">
                            <div class="cp-item-icon" :style="{ background: cmd.color + '18', color: cmd.color }">
                                <i :class="cmd.icon" />
                            </div>
                            <div class="cp-item-body">
                                <span class="cp-item-name" v-html="highlight(cmd.name)" />
                                <span class="cp-item-cat">{{ cmd.category }}</span>
                            </div>
                            <kbd v-if="cmd.shortcut" class="cp-shortcut">{{ cmd.shortcut }}</kbd>
                        </button>
                    </template>

                    <!-- Empty -->
                    <div v-if="totalCount === 0" class="cp-empty">
                        <i class="fa-solid fa-face-meh" />
                        <span>No commands match "<strong>{{ query }}</strong>"</span>
                    </div>

                </div>

                <!-- Footer -->
                <div class="cp-footer">
                    <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
                    <span><kbd>Enter</kbd> Execute</span>
                    <span><kbd>Esc</kbd> Close</span>
                    <span class="cp-count" v-if="totalCount > 0">{{ totalCount }} command{{ totalCount !== 1 ? 's' : ''
                        }}</span>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

// ── Props / Emits ────────────────────────────────────────────────────
const props = defineProps({
    isDark: { type: Boolean, default: false },
    report: { type: Object, required: true },
    settings: { type: Object, required: true },
})

const emit = defineEmits(['close', 'execute'])

// ── State ─────────────────────────────────────────────────────────────
const query = ref('')
const activeIndex = ref(0)
const inputRef = ref(null)
const listRef = ref(null)
const activeEl = ref(null)

const RECENT_KEY = 'editor-recent-commands'
const recentCommands = ref(
    JSON.parse(localStorage.getItem(RECENT_KEY) || '[]')
)

// ── All commands ───────────────────────────────────────────────────────
const ALL_COMMANDS = [
    // ── History ──
    { id: 'undo', name: 'Undo', icon: 'fa-solid fa-undo', color: '#6366f1', category: 'History', shortcut: 'Ctrl+Alt+Z' },
    { id: 'redo', name: 'Redo', icon: 'fa-solid fa-redo', color: '#6366f1', category: 'History', shortcut: 'Ctrl+Alt+Y' },

    // ── View / Zoom ──
    { id: 'zoom-in', name: 'Zoom In', icon: 'fa-solid fa-magnifying-glass-plus', color: '#06b6d4', category: 'View', shortcut: 'Ctrl+Alt+=' },
    { id: 'zoom-out', name: 'Zoom Out', icon: 'fa-solid fa-magnifying-glass-minus', color: '#06b6d4', category: 'View', shortcut: 'Ctrl+Alt+-' },
    { id: 'zoom-reset', name: 'Reset Zoom to 100%', icon: 'fa-solid fa-crosshairs', color: '#06b6d4', category: 'View', shortcut: 'Ctrl+Alt+0' },
    { id: 'toggle-grid', name: 'Toggle Grid', icon: 'fa-solid fa-border-all', color: '#06b6d4', category: 'View', shortcut: 'Ctrl+Alt+G' },
    { id: 'toggle-snap', name: 'Toggle Snap to Grid', icon: 'fa-solid fa-magnet', color: '#06b6d4', category: 'View', shortcut: 'Ctrl+Alt+S' },
    { id: 'toggle-rulers', name: 'Toggle Rulers', icon: 'fa-solid fa-ruler-combined', color: '#06b6d4', category: 'View', shortcut: 'Ctrl+Alt+R' },
    { id: 'toggle-dark', name: 'Toggle Dark Mode', icon: 'fa-solid fa-moon', color: '#475569', category: 'View', shortcut: 'Ctrl+Alt+D' },
    { id: 'fullscreen', name: 'Toggle Fullscreen', icon: 'fa-solid fa-expand', color: '#475569', category: 'View', shortcut: 'F11' },
    { id: 'toggle-left-panel', name: 'Toggle Left Panel', icon: 'fa-solid fa-sidebar', color: '#475569', category: 'View', shortcut: 'Ctrl+Alt+L' },
    { id: 'toggle-right-panel', name: 'Toggle Right Panel', icon: 'fa-solid fa-sidebar-flip', color: '#475569', category: 'View', shortcut: '' },

    // ── Pages ──
    { id: 'add-page', name: 'Add New Page', icon: 'fa-solid fa-plus', color: '#10b981', category: 'Pages', shortcut: '' },
    { id: 'duplicate-page', name: 'Duplicate Current Page', icon: 'fa-solid fa-copy', color: '#10b981', category: 'Pages', shortcut: '' },
    { id: 'delete-page', name: 'Delete Current Page', icon: 'fa-solid fa-trash', color: '#ef4444', category: 'Pages', shortcut: '' },

    // ── Elements ──
    { id: 'delete-el', name: 'Delete Selected Element', icon: 'fa-solid fa-trash-can', color: '#ef4444', category: 'Elements', shortcut: 'Del' },
    { id: 'duplicate-el', name: 'Duplicate Element', icon: 'fa-solid fa-clone', color: '#6366f1', category: 'Elements', shortcut: 'Ctrl+Alt+Q' },
    { id: 'lock-el', name: 'Lock / Unlock Element', icon: 'fa-solid fa-lock', color: '#f59e0b', category: 'Elements', shortcut: '' },
    { id: 'bring-front', name: 'Bring Element to Front', icon: 'fa-solid fa-angles-up', color: '#6366f1', category: 'Elements', shortcut: 'Ctrl+Alt+B' },
    { id: 'send-back', name: 'Send Element to Back', icon: 'fa-solid fa-angles-down', color: '#6366f1', category: 'Elements', shortcut: 'Ctrl+Alt+E' },
    { id: 'copy-style', name: 'Copy Element Style', icon: 'fa-solid fa-paintbrush', color: '#8b5cf6', category: 'Elements', shortcut: 'Ctrl+Alt+C' },
    { id: 'paste-style', name: 'Paste Element Style', icon: 'fa-solid fa-paste', color: '#8b5cf6', category: 'Elements', shortcut: 'Ctrl+Alt+X' },

    // ── File ──
    { id: 'save', name: 'Save Report', icon: 'fa-solid fa-floppy-disk', color: '#10b981', category: 'File', shortcut: '' },
    { id: 'preview', name: 'Open Preview', icon: 'fa-solid fa-eye', color: '#06b6d4', category: 'File', shortcut: 'Ctrl+Alt+V' },
    { id: 'export-pdf', name: 'Export as PDF', icon: 'fa-solid fa-file-pdf', color: '#ef4444', category: 'File', shortcut: '' },
    { id: 'print', name: 'Print Report', icon: 'fa-solid fa-print', color: '#475569', category: 'File', shortcut: 'Ctrl+Alt+P' },

    // ── Tools ──
    { id: 'toggle-ai', name: 'Open AI Assistant', icon: 'fa-solid fa-wand-magic-sparkles', color: '#8b5cf6', category: 'Tools', shortcut: 'Ctrl+Alt+A' },
    { id: 'find-replace', name: 'Find & Replace', icon: 'fa-solid fa-magnifying-glass', color: '#06b6d4', category: 'Tools', shortcut: 'Ctrl+Alt+F' },
    { id: 'shortcuts', name: 'Show Keyboard Shortcuts', icon: 'fa-solid fa-keyboard', color: '#475569', category: 'Tools', shortcut: 'Ctrl+Alt+?' },

    // ── Insert Elements ──
    { id: 'insert-heading', name: 'Insert Heading', icon: 'fa-solid fa-heading', color: '#6366f1', category: 'Insert', shortcut: '' },
    { id: 'insert-text', name: 'Insert Text Block', icon: 'fa-solid fa-align-left', color: '#6366f1', category: 'Insert', shortcut: '' },
    { id: 'insert-image', name: 'Insert Image', icon: 'fa-solid fa-image', color: '#8b5cf6', category: 'Insert', shortcut: '' },
    { id: 'insert-table', name: 'Insert Table', icon: 'fa-solid fa-table', color: '#f59e0b', category: 'Insert', shortcut: '' },
    { id: 'insert-bar-chart', name: 'Insert Bar Chart', icon: 'fa-solid fa-chart-column', color: '#10b981', category: 'Insert', shortcut: '' },
    { id: 'insert-line-chart', name: 'Insert Line Chart', icon: 'fa-solid fa-chart-line', color: '#10b981', category: 'Insert', shortcut: '' },
    { id: 'insert-pie-chart', name: 'Insert Pie Chart', icon: 'fa-solid fa-chart-pie', color: '#10b981', category: 'Insert', shortcut: '' },
    { id: 'insert-metric', name: 'Insert KPI Metric', icon: 'fa-solid fa-arrow-trend-up', color: '#06b6d4', category: 'Insert', shortcut: '' },
    { id: 'insert-divider', name: 'Insert Divider', icon: 'fa-solid fa-minus', color: '#94a3b8', category: 'Insert', shortcut: '' },
    { id: 'insert-rectangle', name: 'Insert Rectangle', icon: 'fa-regular fa-square', color: '#ef4444', category: 'Insert', shortcut: '' },

    // ── Settings ──
    { id: 'settings-a4', name: 'Set Page Size: A4', icon: 'fa-solid fa-file', color: '#475569', category: 'Settings', shortcut: '' },
    { id: 'settings-letter', name: 'Set Page Size: Letter', icon: 'fa-solid fa-file', color: '#475569', category: 'Settings', shortcut: '' },
    { id: 'settings-portrait', name: 'Set Orientation: Portrait', icon: 'fa-solid fa-file', color: '#475569', category: 'Settings', shortcut: '' },
    { id: 'settings-landscape', name: 'Set Orientation: Landscape', icon: 'fa-solid fa-file-invoice', color: '#475569', category: 'Settings', shortcut: '' },
]

// Category icons
const CATEGORY_ICONS = {
    History: 'fa-solid fa-clock-rotate-left',
    View: 'fa-solid fa-eye',
    Pages: 'fa-solid fa-book-open',
    Elements: 'fa-solid fa-shapes',
    File: 'fa-solid fa-folder-open',
    Tools: 'fa-solid fa-toolbox',
    Insert: 'fa-solid fa-plus-circle',
    Settings: 'fa-solid fa-gear',
}

// ── Filtering + grouping ──────────────────────────────────────────────
const flatResults = computed(() => {
    const q = query.value.trim().toLowerCase()
    const cmds = q
        ? ALL_COMMANDS.filter(c =>
            c.name.toLowerCase().includes(q) ||
            c.category.toLowerCase().includes(q) ||
            c.id.toLowerCase().includes(q)
        )
        : ALL_COMMANDS

    // Assign global index for keyboard nav
    let idx = 0
    if (!q && recentCommands.value.length) idx = recentCommands.value.length

    return cmds.map(c => ({ ...c, __globalIdx: idx++ }))
})

const visibleGroups = computed(() => {
    const groups = {}
    flatResults.value.forEach(cmd => {
        if (!groups[cmd.category]) groups[cmd.category] = { category: cmd.category, icon: CATEGORY_ICONS[cmd.category] || 'fa-solid fa-circle', items: [] }
        groups[cmd.category].items.push(cmd)
    })
    return Object.values(groups)
})

const totalCount = computed(() => flatResults.value.length)

const recentList = computed(() =>
    recentCommands.value
        .map(id => ALL_COMMANDS.find(c => c.id === id))
        .filter(Boolean)
        .map((c, i) => ({ ...c, __globalIdx: i }))
)

// ── Keyboard navigation ───────────────────────────────────────────────
const maxIdx = computed(() => {
    const recent = !query.value.trim() ? recentList.value.length : 0
    return recent + totalCount.value - 1
})

function moveDown() {
    activeIndex.value = activeIndex.value < maxIdx.value ? activeIndex.value + 1 : 0
    scrollToActive()
}

function moveUp() {
    activeIndex.value = activeIndex.value > 0 ? activeIndex.value - 1 : maxIdx.value
    scrollToActive()
}

function setActive(idx) { activeIndex.value = idx }

function scrollToActive() {
    nextTick(() => {
        const el = listRef.value?.querySelector('.cp-item-active')
        el?.scrollIntoView({ block: 'nearest' })
    })
}

function getGlobalIdx(key) {
    // For recent items, index = array position
    const match = recentList.value.find(c => `recent-${c.id}` === key)
    return match?.__globalIdx ?? -1
}

function runActive() {
    // Find the command at activeIndex
    if (!query.value.trim() && activeIndex.value < recentList.value.length) {
        run(recentList.value[activeIndex.value])
        return
    }
    const flat = flatResults.value
    const cmd = flat.find(c => c.__globalIdx === activeIndex.value)
    if (cmd) run(cmd)
}

function run(cmd) {
    // Save to recent
    const recents = [cmd.id, ...recentCommands.value.filter(id => id !== cmd.id)].slice(0, 5)
    recentCommands.value = recents
    localStorage.setItem(RECENT_KEY, JSON.stringify(recents))

    emit('execute', cmd.id)
    emit('close')
}

// ── Fuzzy highlight ───────────────────────────────────────────────────
function highlight(name) {
    const q = query.value.trim()
    if (!q) return name
    const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi')
    return name.replace(re, '<mark>$1</mark>')
}

// ── Reset active on query change ──────────────────────────────────────
watch(query, () => { activeIndex.value = 0 })

// ── Mount ─────────────────────────────────────────────────────────────
onMounted(async () => {
    await nextTick()
    inputRef.value?.focus()
})
</script>

<style scoped>
/* ═══ BACKDROP ═══════════════════════════════════════════════════════ */
.cp-backdrop {
    position: fixed;
    inset: 0;
    z-index: 8000;
    background: rgba(0, 0, 0, .5);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 12vh;
    animation: fadeIn .15s ease;
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
.cp-panel {
    --cp-bg: #ffffff;
    --cp-bg2: #f8fafc;
    --cp-border: #e2e8f0;
    --cp-text: #0f172a;
    --cp-text2: #475569;
    --cp-text3: #94a3b8;
    --cp-accent: #6366f1;
    --cp-accent-l: rgba(99, 102, 241, .08);
    --cp-mark: rgba(99, 102, 241, .2);

    width: 100%;
    max-width: 600px;
    background: var(--cp-bg);
    border: 1px solid var(--cp-border);
    border-radius: 16px;
    box-shadow: 0 24px 80px rgba(0, 0, 0, .22);
    display: flex;
    flex-direction: column;
    max-height: 74vh;
    overflow: hidden;
    animation: panelIn .18s cubic-bezier(.16, 1, .3, 1);
}

.cp-dark {
    --cp-bg: #111827;
    --cp-bg2: #1a2236;
    --cp-border: #1e2d45;
    --cp-text: #e2e8f0;
    --cp-text2: #94a3b8;
    --cp-text3: #475569;
    --cp-accent-l: rgba(129, 140, 248, .1);
    --cp-mark: rgba(129, 140, 248, .25);
}

@keyframes panelIn {
    from {
        opacity: 0;
        transform: scale(.96) translateY(-10px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* ═══ SEARCH ═════════════════════════════════════════════════════════ */
.cp-search-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--cp-border);
    flex-shrink: 0;
}

.cp-search-icon {
    color: var(--cp-text3);
    font-size: 14px;
    flex-shrink: 0;
}

.cp-input {
    flex: 1;
    border: none;
    background: transparent;
    color: var(--cp-text);
    font-size: 15px;
    outline: none;
    font-family: inherit;
    font-weight: 500;
}

.cp-input::placeholder {
    color: var(--cp-text3);
}

.cp-esc-hint {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 5px;
    background: var(--cp-bg2);
    border: 1px solid var(--cp-border);
    color: var(--cp-text3);
    font-family: inherit;
    flex-shrink: 0;
}

/* ═══ RESULTS ════════════════════════════════════════════════════════ */
.cp-results {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
    scrollbar-width: thin;
    scrollbar-color: var(--cp-border) transparent;
}

.cp-group-label {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px 4px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--cp-text3);
}

/* ═══ ITEM ═══════════════════════════════════════════════════════════ */
.cp-item {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    padding: 9px 10px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--cp-text);
    border-radius: 9px;
    text-align: left;
    font-family: inherit;
    font-size: 13px;
    transition: background .1s;
    outline: none;
}

.cp-item-active {
    background: var(--cp-accent-l);
}

.cp-item-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.cp-item-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.cp-item-name {
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Fuzzy highlight */
:deep(.cp-item-name mark) {
    background: var(--cp-mark);
    color: var(--cp-accent);
    font-weight: 700;
    border-radius: 2px;
    padding: 0 1px;
}

.cp-item-cat {
    font-size: 10px;
    color: var(--cp-text3);
    font-weight: 400;
}

.cp-shortcut {
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 5px;
    white-space: nowrap;
    background: var(--cp-bg2);
    border: 1px solid var(--cp-border);
    color: var(--cp-text3);
    font-family: inherit;
    flex-shrink: 0;
}

.cp-sep {
    height: 1px;
    background: var(--cp-border);
    margin: 6px 8px;
}

/* ═══ EMPTY ══════════════════════════════════════════════════════════ */
.cp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 48px 24px;
    color: var(--cp-text3);
    text-align: center;
    font-size: 13px;
}

.cp-empty i {
    font-size: 28px;
    opacity: .3;
}

.cp-empty strong {
    color: var(--cp-text2);
}

/* ═══ FOOTER ═════════════════════════════════════════════════════════ */
.cp-footer {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 10px 18px;
    border-top: 1px solid var(--cp-border);
    font-size: 10px;
    color: var(--cp-text3);
    flex-shrink: 0;
}

.cp-footer kbd {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    padding: 2px 5px;
    border-radius: 4px;
    background: var(--cp-bg2);
    border: 1px solid var(--cp-border);
    font-family: inherit;
    font-size: 9px;
    color: var(--cp-text3);
    margin-right: 2px;
}

.cp-count {
    margin-left: auto;
    font-weight: 600;
    color: var(--cp-text2);
}

/* ═══ RESPONSIVE ═════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .cp-backdrop {
        padding-top: 5vh;
    }

    .cp-panel {
        max-width: 100%;
        border-radius: 12px;
    }
}
</style>