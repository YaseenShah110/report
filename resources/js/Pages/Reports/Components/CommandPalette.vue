<!--
  CommandPalette.vue — Ctrl+K quick-action search
  • Fuzzy search across all commands
  • Keyboard: arrow up/down, enter, escape
  • Groups: File, Edit, View, Insert, Page, Help
  • Recent commands (localStorage)
  • Shortcut hints on every item
  • Dark mode aware
  • Closes on outside click / escape
  • Memory safe
-->
<template>
    <Teleport to="body">
        <div class="cp-backdrop" @click="$emit('close')" aria-hidden="true" />

        <div class="cp-shell" :class="{ 'is-dark': isDark }" role="dialog" aria-modal="true"
            aria-label="Command palette" @keydown="onKeyDown">
            <!-- Search input -->
            <div class="cp-search-wrap">
                <i class="fa-solid fa-magnifying-glass cp-search-icon" aria-hidden="true" />
                <input ref="inputRef" v-model="query" class="cp-input" placeholder="Type a command…" type="search"
                    autocomplete="off" spellcheck="false" aria-label="Search commands" aria-autocomplete="list"
                    :aria-activedescendant="focusedId" />
                <kbd class="cp-esc-hint" aria-label="Press Escape to close">esc</kbd>
            </div>

            <!-- Results -->
            <div class="cp-results" role="listbox" aria-label="Commands" ref="listRef">

                <!-- Recent (only when query empty) -->
                <template v-if="!query.trim() && recentIds.length">
                    <div class="cp-group-label">Recent</div>
                    <button v-for="(cmd) in recentCommands" :key="'r-' + cmd.id" :id="`cp-item-r-${cmd.id}`"
                        class="cp-item" :class="{ 'cp-item--focused': focusedId === `cp-item-r-${cmd.id}` }"
                        role="option" :aria-selected="focusedId === `cp-item-r-${cmd.id}`" @click="execute(cmd)"
                        @mouseenter="focusedId = `cp-item-r-${cmd.id}`">
                        <span class="cp-item-icon" :style="{ color: cmd.color || '#6366f1' }"><i
                                :class="cmd.icon" /></span>
                        <span class="cp-item-label">{{ cmd.label }}</span>
                        <span class="cp-item-group">{{ cmd.group }}</span>
                        <kbd v-if="cmd.shortcut" class="cp-item-shortcut">{{ cmd.shortcut }}</kbd>
                    </button>
                    <div class="cp-divider" aria-hidden="true" />
                </template>

                <!-- Grouped results -->
                <template v-for="group in groupedResults" :key="group.name">
                    <div class="cp-group-label">{{ group.name }}</div>
                    <button v-for="cmd in group.items" :key="cmd.id" :id="`cp-item-${cmd.id}`" class="cp-item"
                        :class="{ 'cp-item--focused': focusedId === `cp-item-${cmd.id}` }" role="option"
                        :aria-selected="focusedId === `cp-item-${cmd.id}`" @click="execute(cmd)"
                        @mouseenter="focusedId = `cp-item-${cmd.id}`">
                        <span class="cp-item-icon" :style="{ color: cmd.color || '#6366f1' }"><i
                                :class="cmd.icon" /></span>
                        <span class="cp-item-label">
                            <span v-html="highlight(cmd.label, query)" />
                        </span>
                        <span class="cp-item-group">{{ cmd.group }}</span>
                        <kbd v-if="cmd.shortcut" class="cp-item-shortcut">{{ cmd.shortcut }}</kbd>
                    </button>
                </template>

                <!-- Empty -->
                <div v-if="!groupedResults.length && !recentCommands.length" class="cp-empty" role="status">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true" />
                    <p>No commands found for <strong>"{{ query }}"</strong></p>
                </div>
            </div>

            <!-- Footer hint -->
            <div class="cp-footer" aria-hidden="true">
                <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
                <span><kbd>↵</kbd> Execute</span>
                <span><kbd>Esc</kbd> Close</span>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    isDark: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'execute'])

// ── State ──────────────────────────────────────────────────────────────
const inputRef = ref(null)
const listRef = ref(null)
const query = ref('')
const focusedId = ref('')
const recentIds = ref(JSON.parse(localStorage.getItem('rg_recent_cmds') || '[]'))

// ── All commands ───────────────────────────────────────────────────────
const COMMANDS = [
    // File
    { id: 'save', group: 'File', label: 'Save Report', icon: 'fa-solid fa-floppy-disk', shortcut: 'Ctrl+Alt+S', color: '#6366f1' },
    { id: 'preview', group: 'File', label: 'Preview Report', icon: 'fa-solid fa-eye', shortcut: 'Ctrl+Alt+V', color: '#06b6d4' },
    { id: 'print', group: 'File', label: 'Print Preview', icon: 'fa-solid fa-print', shortcut: '', color: '#64748b' },
    { id: 'export-pdf', group: 'File', label: 'Export as PDF', icon: 'fa-solid fa-file-pdf', shortcut: '', color: '#ef4444' },
    { id: 'export-image', group: 'File', label: 'Export as PNG Image', icon: 'fa-solid fa-file-image', shortcut: '', color: '#8b5cf6' },
    { id: 'export-excel', group: 'File', label: 'Export as Excel', icon: 'fa-solid fa-file-excel', shortcut: '', color: '#10b981' },
    { id: 'export-csv', group: 'File', label: 'Export as CSV', icon: 'fa-solid fa-file-csv', shortcut: '', color: '#3b82f6' },
    { id: 'share', group: 'File', label: 'Copy Share Link', icon: 'fa-solid fa-share-nodes', shortcut: '', color: '#06b6d4' },
    { id: 'email', group: 'File', label: 'Email Report', icon: 'fa-solid fa-envelope', shortcut: '', color: '#f59e0b' },
    // Edit
    { id: 'undo', group: 'Edit', label: 'Undo', icon: 'fa-solid fa-undo', shortcut: 'Ctrl+Alt+Z', color: '#6366f1' },
    { id: 'redo', group: 'Edit', label: 'Redo', icon: 'fa-solid fa-redo', shortcut: 'Ctrl+Alt+Y', color: '#6366f1' },
    { id: 'duplicate', group: 'Edit', label: 'Duplicate Element', icon: 'fa-solid fa-clone', shortcut: 'Ctrl+Alt+D', color: '#8b5cf6' },
    { id: 'delete', group: 'Edit', label: 'Delete Element', icon: 'fa-solid fa-trash', shortcut: 'Del', color: '#ef4444' },
    { id: 'copy', group: 'Edit', label: 'Copy Element', icon: 'fa-solid fa-copy', shortcut: 'Ctrl+Alt+C', color: '#64748b' },
    { id: 'paste', group: 'Edit', label: 'Paste Element', icon: 'fa-solid fa-paste', shortcut: 'Ctrl+Alt+V', color: '#64748b' },
    { id: 'select-all', group: 'Edit', label: 'Select All Elements', icon: 'fa-solid fa-check-double', shortcut: 'Ctrl+Alt+A', color: '#6366f1' },
    { id: 'deselect', group: 'Edit', label: 'Deselect All', icon: 'fa-solid fa-xmark', shortcut: 'Esc', color: '#94a3b8' },
    { id: 'find', group: 'Edit', label: 'Find & Replace', icon: 'fa-solid fa-magnifying-glass', shortcut: 'Ctrl+Alt+F', color: '#f59e0b' },
    { id: 'bring-front', group: 'Edit', label: 'Bring to Front', icon: 'fa-solid fa-angles-up', shortcut: '', color: '#6366f1' },
    { id: 'send-back', group: 'Edit', label: 'Send to Back', icon: 'fa-solid fa-angles-down', shortcut: '', color: '#6366f1' },
    { id: 'lock', group: 'Edit', label: 'Lock / Unlock Element', icon: 'fa-solid fa-lock', shortcut: '', color: '#f59e0b' },
    { id: 'copy-style', group: 'Edit', label: 'Copy Style', icon: 'fa-solid fa-paintbrush', shortcut: '', color: '#ec4899' },
    { id: 'paste-style', group: 'Edit', label: 'Paste Style', icon: 'fa-solid fa-brush', shortcut: '', color: '#ec4899' },
    { id: 'reset-styles', group: 'Edit', label: 'Reset Element Styles', icon: 'fa-solid fa-rotate-left', shortcut: '', color: '#94a3b8' },
    // View
    { id: 'toggle-grid', group: 'View', label: 'Toggle Grid', icon: 'fa-solid fa-border-all', shortcut: 'Ctrl+Alt+G', color: '#10b981' },
    { id: 'toggle-snap', group: 'View', label: 'Toggle Snap to Grid', icon: 'fa-solid fa-magnet', shortcut: 'Ctrl+Alt+S', color: '#10b981' },
    { id: 'toggle-rulers', group: 'View', label: 'Toggle Rulers', icon: 'fa-solid fa-ruler-combined', shortcut: 'Ctrl+Alt+R', color: '#10b981' },
    { id: 'toggle-dark', group: 'View', label: 'Toggle Dark Mode', icon: 'fa-solid fa-moon', shortcut: 'Ctrl+Alt+D', color: '#8b5cf6' },
    { id: 'fullscreen', group: 'View', label: 'Toggle Fullscreen', icon: 'fa-solid fa-expand', shortcut: 'F11', color: '#64748b' },
    { id: 'zoom-in', group: 'View', label: 'Zoom In', icon: 'fa-solid fa-magnifying-glass-plus', shortcut: 'Ctrl+Alt+=', color: '#06b6d4' },
    { id: 'zoom-out', group: 'View', label: 'Zoom Out', icon: 'fa-solid fa-magnifying-glass-minus', shortcut: 'Ctrl+Alt+-', color: '#06b6d4' },
    { id: 'zoom-reset', group: 'View', label: 'Reset Zoom (100%)', icon: 'fa-solid fa-magnifying-glass', shortcut: 'Ctrl+Alt+0', color: '#06b6d4' },
    { id: 'toggle-left', group: 'View', label: 'Toggle Left Panel', icon: 'fa-solid fa-sidebar', shortcut: 'Ctrl+Alt+L', color: '#64748b' },
    { id: 'toggle-right', group: 'View', label: 'Toggle Right Panel', icon: 'fa-solid fa-sidebar-flip', shortcut: '', color: '#64748b' },
    { id: 'toggle-ai', group: 'View', label: 'Toggle AI Assistant', icon: 'fa-solid fa-wand-magic-sparkles', shortcut: 'Ctrl+Alt+A', color: '#8b5cf6' },
    { id: 'presentation', group: 'View', label: 'Presentation Mode', icon: 'fa-solid fa-play', shortcut: 'Ctrl+Alt+P', color: '#f59e0b' },
    { id: 'measure', group: 'View', label: 'Measure Tool', icon: 'fa-solid fa-ruler', shortcut: 'Ctrl+Alt+M', color: '#06b6d4' },
    // Insert
    { id: 'add-heading', group: 'Insert', label: 'Add Heading', icon: 'fa-solid fa-heading', shortcut: '', color: '#6366f1' },
    { id: 'add-text', group: 'Insert', label: 'Add Text Block', icon: 'fa-solid fa-t', shortcut: '', color: '#6366f1' },
    { id: 'add-image', group: 'Insert', label: 'Add Image', icon: 'fa-solid fa-image', shortcut: '', color: '#ec4899' },
    { id: 'add-table', group: 'Insert', label: 'Add Table', icon: 'fa-solid fa-table', shortcut: '', color: '#10b981' },
    { id: 'add-chart', group: 'Insert', label: 'Add Bar Chart', icon: 'fa-solid fa-chart-bar', shortcut: '', color: '#f59e0b' },
    { id: 'add-metric', group: 'Insert', label: 'Add KPI Card', icon: 'fa-solid fa-gauge-high', shortcut: '', color: '#f97316' },
    { id: 'add-divider', group: 'Insert', label: 'Add Divider', icon: 'fa-solid fa-minus', shortcut: '', color: '#94a3b8' },
    { id: 'add-richtext', group: 'Insert', label: 'Add Rich Text', icon: 'fa-solid fa-file-word', shortcut: '', color: '#3b82f6' },
    // Page
    { id: 'add-page', group: 'Page', label: 'Add New Page', icon: 'fa-solid fa-plus', shortcut: 'Ctrl+Alt+N', color: '#6366f1' },
    { id: 'duplicate-page', group: 'Page', label: 'Duplicate Current Page', icon: 'fa-solid fa-copy', shortcut: '', color: '#6366f1' },
    { id: 'delete-page', group: 'Page', label: 'Delete Current Page', icon: 'fa-solid fa-trash', shortcut: '', color: '#ef4444' },
    // Help
    { id: 'shortcuts', group: 'Help', label: 'Keyboard Shortcuts', icon: 'fa-solid fa-keyboard', shortcut: 'Ctrl+Alt+/', color: '#94a3b8' },
]

// ── Computed ───────────────────────────────────────────────────────────
const recentCommands = computed(() =>
    recentIds.value
        .slice(0, 5)
        .map(id => COMMANDS.find(c => c.id === id))
        .filter(Boolean)
)

const filteredCommands = computed(() => {
    const q = query.value.trim().toLowerCase()
    if (!q) return COMMANDS
    return COMMANDS.filter(c =>
        c.label.toLowerCase().includes(q) ||
        c.group.toLowerCase().includes(q) ||
        c.id.includes(q)
    )
})

const groupedResults = computed(() => {
    const groups = {}
    for (const cmd of filteredCommands.value) {
        if (!groups[cmd.group]) groups[cmd.group] = []
        groups[cmd.group].push(cmd)
    }
    return Object.entries(groups).map(([name, items]) => ({ name, items }))
})

const flatVisible = computed(() => {
    const items = []
    if (!query.value.trim()) {
        recentCommands.value.forEach(c => items.push({ ...c, _rid: true }))
    }
    groupedResults.value.forEach(g => g.items.forEach(c => items.push(c)))
    return items
})

// ── Highlight matching text ────────────────────────────────────────────
function highlight(text, q) {
    if (!q.trim()) return text
    const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>')
}

// ── Execute a command ──────────────────────────────────────────────────
function execute(cmd) {
    // Save to recent
    recentIds.value = [cmd.id, ...recentIds.value.filter(id => id !== cmd.id)].slice(0, 8)
    localStorage.setItem('rg_recent_cmds', JSON.stringify(recentIds.value))
    emit('execute', cmd.id)
    emit('close')
}

// ── Keyboard navigation ────────────────────────────────────────────────
function onKeyDown(e) {
    if (e.key === 'Escape') { emit('close'); return }

    const flat = flatVisible.value
    const currentId = focusedId.value.replace('cp-item-r-', '').replace('cp-item-', '')
    const currentIdx = flat.findIndex(c => c.id === currentId)

    if (e.key === 'ArrowDown') {
        e.preventDefault()
        const next = flat[currentIdx + 1] || flat[0]
        if (next) focusedId.value = `cp-item-${next._rid ? 'r-' : ''}${next.id}`
        scrollFocused()
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        const prev = flat[currentIdx - 1] || flat[flat.length - 1]
        if (prev) focusedId.value = `cp-item-${prev._rid ? 'r-' : ''}${prev.id}`
        scrollFocused()
    } else if (e.key === 'Enter') {
        e.preventDefault()
        const focused = flat[currentIdx]
        if (focused) execute(focused)
    }
}

function scrollFocused() {
    nextTick(() => {
        const el = listRef.value?.querySelector(`[id="${focusedId.value}"]`)
        el?.scrollIntoView({ block: 'nearest' })
    })
}

// ── Reset on query change, auto-focus first item ───────────────────────
watch(query, () => {
    const first = flatVisible.value[0]
    if (first) focusedId.value = `cp-item-${first._rid ? 'r-' : ''}${first.id}`
    else focusedId.value = ''
})

// ── Mount: focus input ─────────────────────────────────────────────────
onMounted(() => {
    nextTick(() => inputRef.value?.focus())
})
</script>

<style scoped>
/* ── Backdrop ─────────────────────────────────────────────────────── */
.cp-backdrop {
    position: fixed;
    inset: 0;
    z-index: 8000;
    background: rgba(0, 0, 0, .45);
    backdrop-filter: blur(3px);
}

/* ── Shell ────────────────────────────────────────────────────────── */
.cp-shell {
    --cp-bg: #ffffff;
    --cp-bg2: #f8fafc;
    --cp-border: #e2e8f0;
    --cp-text: #0f172a;
    --cp-text2: #475569;
    --cp-text3: #94a3b8;
    --cp-accent: #6366f1;
    --cp-hover: #f1f5f9;
    --cp-shadow: 0 24px 80px rgba(0, 0, 0, .22), 0 4px 20px rgba(0, 0, 0, .1);

    position: fixed;
    top: 15vh;
    left: 50%;
    transform: translateX(-50%);
    width: 580px;
    max-width: calc(100vw - 32px);
    max-height: 70vh;
    background: var(--cp-bg);
    border: 1px solid var(--cp-border);
    border-radius: 16px;
    box-shadow: var(--cp-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 8001;
    animation: cpIn .18s cubic-bezier(.16, 1, .3, 1);
}

.cp-shell.is-dark {
    --cp-bg: #1a2236;
    --cp-bg2: #111827;
    --cp-border: #263348;
    --cp-text: #e2e8f0;
    --cp-text2: #94a3b8;
    --cp-text3: #475569;
    --cp-hover: #263348;
}

@keyframes cpIn {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-12px) scale(.97);
    }

    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0) scale(1);
    }
}

/* ── Search row ───────────────────────────────────────────────────── */
.cp-search-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--cp-border);
    flex-shrink: 0;
}

.cp-search-icon {
    color: var(--cp-text3);
    font-size: 14px;
}

.cp-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 15px;
    color: var(--cp-text);
    font-family: inherit;
}

.cp-input::placeholder {
    color: var(--cp-text3);
}

.cp-esc-hint {
    font-size: 10px;
    color: var(--cp-text3);
    background: var(--cp-bg2);
    border: 1px solid var(--cp-border);
    border-radius: 4px;
    padding: 2px 7px;
    font-family: inherit;
    flex-shrink: 0;
}

/* ── Results ──────────────────────────────────────────────────────── */
.cp-results {
    flex: 1;
    overflow-y: auto;
    padding: 6px;
    scrollbar-width: thin;
    scrollbar-color: var(--cp-border) transparent;
}

.cp-results::-webkit-scrollbar {
    width: 4px;
}

.cp-results::-webkit-scrollbar-thumb {
    background: var(--cp-border);
    border-radius: 99px;
}

/* ── Group label ──────────────────────────────────────────────────── */
.cp-group-label {
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--cp-text3);
    padding: 8px 10px 3px;
}

/* ── Divider ──────────────────────────────────────────────────────── */
.cp-divider {
    height: 1px;
    background: var(--cp-border);
    margin: 5px 8px;
}

/* ── Item ─────────────────────────────────────────────────────────── */
.cp-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 10px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    color: var(--cp-text);
    font-size: 13px;
    font-weight: 500;
    text-align: left;
    transition: background .1s;
    font-family: inherit;
}

.cp-item:hover,
.cp-item--focused {
    background: var(--cp-hover);
}

.cp-item-icon {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: currentColor;
    background: var(--cp-bg2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

.cp-item-label {
    flex: 1;
}

/* Highlight match */
:deep(.cp-item-label mark) {
    background: rgba(99, 102, 241, .18);
    color: var(--cp-accent);
    border-radius: 3px;
    padding: 0 2px;
}

.cp-item-group {
    font-size: 10px;
    color: var(--cp-text3);
    background: var(--cp-bg2);
    border: 1px solid var(--cp-border);
    border-radius: 4px;
    padding: 1px 6px;
    white-space: nowrap;
}

.cp-item-shortcut {
    font-size: 9px;
    color: var(--cp-text3);
    background: var(--cp-bg2);
    border: 1px solid var(--cp-border);
    border-radius: 4px;
    padding: 2px 6px;
    white-space: nowrap;
    font-family: inherit;
}

/* ── Empty ────────────────────────────────────────────────────────── */
.cp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 40px 20px;
    text-align: center;
    color: var(--cp-text3);
    font-size: 13px;
}

.cp-empty i {
    font-size: 28px;
    opacity: .3;
}

.cp-empty strong {
    color: var(--cp-text2);
}

/* ── Footer ───────────────────────────────────────────────────────── */
.cp-footer {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 8px 16px;
    border-top: 1px solid var(--cp-border);
    font-size: 10px;
    color: var(--cp-text3);
    flex-shrink: 0;
    background: var(--cp-bg2);
}

.cp-footer kbd {
    font-size: 9px;
    background: var(--cp-bg);
    border: 1px solid var(--cp-border);
    border-radius: 3px;
    padding: 1px 5px;
    font-family: inherit;
}
</style>