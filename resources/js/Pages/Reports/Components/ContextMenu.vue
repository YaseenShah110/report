<!--
  ContextMenu.vue — Right-click Context Menu
  ═══════════════════════════════════════════════════════════════════
  Three context types (auto-detected via props):
  • Element context (ei !== null) — duplicate, delete, lock/unlock,
    bring front, send back, copy style, paste style, align options
  • Page context   (pi !== null, ei === null) — add before/after,
    duplicate page, delete page, move up/down
  • Canvas context (both null) — add page, paste element

  Features:
  • Renders via Teleport to body — never clipped by overflow:hidden
  • Smart repositioning if menu would overflow viewport
  • Keyboard navigation (↑ ↓ Enter Esc)
  • Closes on outside click or scroll
  • Divider separators between logical groups
  • Keyboard shortcut hints on each item
  • Fully accessible (role=menu, role=menuitem, aria-*)
  ═══════════════════════════════════════════════════════════════════
-->
<template>
    <Teleport to="body">
        <div ref="menuRef" class="ctx-menu" :class="{ 'ctx-dark': isDark }" :style="menuStyle" role="menu"
            aria-label="Context menu" @keydown="onKeyDown" @click.stop>
            <!-- ── ELEMENT CONTEXT ──────────────────────────────────────── -->
            <template v-if="isElCtx">
                <div class="ctx-header">
                    <i :class="elIcon" />
                    {{ elTypeLabel }}
                </div>

                <button class="ctx-item" @click="emit('duplicate')" role="menuitem">
                    <i class="fa-solid fa-clone ctx-icon" />
                    <span>Duplicate</span>
                    <kbd>Ctrl+Alt+Q</kbd>
                </button>

                <button class="ctx-item" @click="emit('copy-style')" role="menuitem">
                    <i class="fa-solid fa-paintbrush ctx-icon" />
                    <span>Copy Style</span>
                    <kbd>Ctrl+Alt+C</kbd>
                </button>

                <button class="ctx-item" @click="emit('paste-style')" role="menuitem">
                    <i class="fa-solid fa-paste ctx-icon" />
                    <span>Paste Style</span>
                    <kbd>Ctrl+Alt+X</kbd>
                </button>

                <div class="ctx-sep" role="separator" />

                <button class="ctx-item" @click="emit('bring-front')" role="menuitem">
                    <i class="fa-solid fa-angles-up ctx-icon" />
                    <span>Bring to Front</span>
                    <kbd>Ctrl+Alt+B</kbd>
                </button>

                <button class="ctx-item" @click="emit('send-back')" role="menuitem">
                    <i class="fa-solid fa-angles-down ctx-icon" />
                    <span>Send to Back</span>
                    <kbd>Ctrl+Alt+E</kbd>
                </button>

                <div class="ctx-sep" role="separator" />

                <!-- Align submenu trigger -->
                <div class="ctx-item ctx-item--has-sub" @mouseenter="showAlign = true" @mouseleave="showAlign = false"
                    role="menuitem" aria-haspopup="true">
                    <i class="fa-solid fa-align-center ctx-icon" />
                    <span>Align to Page</span>
                    <i class="fa-solid fa-chevron-right ctx-sub-arrow" aria-hidden="true" />

                    <div v-if="showAlign" class="ctx-submenu" role="menu">
                        <button class="ctx-item" @click="emit('align', 'left')" role="menuitem"><i
                                class="fa-solid fa-align-left ctx-icon" /> Left</button>
                        <button class="ctx-item" @click="emit('align', 'center')" role="menuitem"><i
                                class="fa-solid fa-align-center ctx-icon" /> Center H</button>
                        <button class="ctx-item" @click="emit('align', 'right')" role="menuitem"><i
                                class="fa-solid fa-align-right ctx-icon" /> Right</button>
                        <div class="ctx-sep" role="separator" />
                        <button class="ctx-item" @click="emit('align', 'top')" role="menuitem"><i
                                class="fa-solid fa-arrow-up-to-line ctx-icon" /> Top</button>
                        <button class="ctx-item" @click="emit('align', 'middle')" role="menuitem"><i
                                class="fa-solid fa-arrows-up-down ctx-icon" /> Center V</button>
                        <button class="ctx-item" @click="emit('align', 'bottom')" role="menuitem"><i
                                class="fa-solid fa-arrow-down-to-line ctx-icon" /> Bottom</button>
                    </div>
                </div>

                <div class="ctx-sep" role="separator" />

                <button class="ctx-item" @click="emit('lock')" role="menuitem">
                    <i :class="['ctx-icon', el?.locked ? 'fa-solid fa-lock-open' : 'fa-solid fa-lock']" />
                    <span>{{ el?.locked ? 'Unlock' : 'Lock' }}</span>
                </button>

                <div class="ctx-sep" role="separator" />

                <button class="ctx-item ctx-item--danger" @click="emit('delete')" role="menuitem">
                    <i class="fa-solid fa-trash-can ctx-icon" />
                    <span>Delete</span>
                    <kbd>Del</kbd>
                </button>
            </template>

            <!-- ── PAGE CONTEXT ────────────────────────────────────────── -->
            <template v-else-if="isPageCtx">
                <div class="ctx-header">
                    <i class="fa-regular fa-file-lines" />
                    Page {{ (pi ?? 0) + 1 }}
                </div>

                <button class="ctx-item" @click="emit('add-page-before', pi)" role="menuitem">
                    <i class="fa-solid fa-arrow-up-from-bracket ctx-icon" />
                    <span>Insert Page Before</span>
                </button>

                <button class="ctx-item" @click="emit('add-page-after', pi)" role="menuitem">
                    <i class="fa-solid fa-arrow-down-to-bracket ctx-icon" />
                    <span>Insert Page After</span>
                </button>

                <div class="ctx-sep" role="separator" />

                <button class="ctx-item" @click="emit('duplicate-page')" role="menuitem">
                    <i class="fa-solid fa-copy ctx-icon" />
                    <span>Duplicate Page</span>
                </button>

                <button class="ctx-item" @click="emit('move-page-up')" role="menuitem">
                    <i class="fa-solid fa-chevron-up ctx-icon" />
                    <span>Move Up</span>
                </button>

                <button class="ctx-item" @click="emit('move-page-down')" role="menuitem">
                    <i class="fa-solid fa-chevron-down ctx-icon" />
                    <span>Move Down</span>
                </button>

                <div class="ctx-sep" role="separator" />

                <button class="ctx-item ctx-item--danger" @click="emit('delete-page')" role="menuitem">
                    <i class="fa-solid fa-trash ctx-icon" />
                    <span>Delete Page</span>
                </button>
            </template>

            <!-- ── CANVAS CONTEXT ──────────────────────────────────────── -->
            <template v-else>
                <div class="ctx-header">
                    <i class="fa-solid fa-vector-square" />
                    Canvas
                </div>

                <button class="ctx-item" @click="emit('add-page')" role="menuitem">
                    <i class="fa-solid fa-plus ctx-icon" />
                    <span>Add Page</span>
                </button>

                <button class="ctx-item" @click="emit('paste-style')" role="menuitem">
                    <i class="fa-solid fa-paste ctx-icon" />
                    <span>Paste</span>
                    <kbd>Ctrl+V</kbd>
                </button>
            </template>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'

// ── Props ───────────────────────────────────────────────────────────
const props = defineProps({
    x: { type: Number, default: 0 },
    y: { type: Number, default: 0 },
    pi: { type: Number, default: null },
    ei: { type: Number, default: null },
    el: { type: Object, default: null },
    isDark: { type: Boolean, default: false },
})

const emit = defineEmits([
    'close', 'duplicate', 'delete', 'lock',
    'bring-front', 'send-back',
    'copy-style', 'paste-style',
    'align',
    'add-page', 'add-page-before', 'add-page-after',
    'duplicate-page', 'delete-page',
    'move-page-up', 'move-page-down',
])

// ── State ────────────────────────────────────────────────────────────
const menuRef = ref(null)
const showAlign = ref(false)

// Adjusted position (avoids viewport overflow)
const adjustedX = ref(props.x)
const adjustedY = ref(props.y)

// ── Context detection ────────────────────────────────────────────────
const isElCtx = computed(() => props.ei !== null && props.ei !== undefined)
const isPageCtx = computed(() => !isElCtx.value && props.pi !== null && props.pi !== undefined)

const EL_ICONS = {
    heading: 'fa-solid fa-heading', subheading: 'fa-solid fa-text-height',
    text: 'fa-solid fa-align-left', richtext: 'fa-solid fa-file-pen',
    image: 'fa-solid fa-image', table: 'fa-solid fa-table',
    'bar-chart': 'fa-solid fa-chart-column', 'line-chart': 'fa-solid fa-chart-line',
    'pie-chart': 'fa-solid fa-chart-pie', metric: 'fa-solid fa-arrow-trend-up',
    rectangle: 'fa-regular fa-square', circle: 'fa-regular fa-circle',
    divider: 'fa-solid fa-minus',
}

const elIcon = computed(() => EL_ICONS[props.el?.type] || 'fa-solid fa-cube')
const elTypeLabel = computed(() =>
    (props.el?.type || 'Element').replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
)

const menuStyle = computed(() => ({
    position: 'fixed',
    left: adjustedX.value + 'px',
    top: adjustedY.value + 'px',
    zIndex: 9000,
}))

// ── Close helpers ────────────────────────────────────────────────────
function closeAll(action) {
    if (action) action()
    emit('close')
}

// Wrap every emit call to auto-close
const originalEmit = emit
function emitAndClose(event, ...args) {
    originalEmit(event, ...args)
    originalEmit('close')
}

// ── Keyboard navigation ──────────────────────────────────────────────
function onKeyDown(e) {
    if (e.key === 'Escape') { emit('close'); return }

    const items = menuRef.value?.querySelectorAll('.ctx-item:not(:disabled)') || []
    const active = document.activeElement
    const idx = Array.from(items).indexOf(active)

    if (e.key === 'ArrowDown') {
        e.preventDefault()
        items[idx < items.length - 1 ? idx + 1 : 0]?.focus()
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        items[idx > 0 ? idx - 1 : items.length - 1]?.focus()
    } else if (e.key === 'Enter' && document.activeElement?.classList.contains('ctx-item')) {
        document.activeElement.click()
    }
}

// ── Viewport adjustment + outside-click ──────────────────────────────
onMounted(async () => {
    await nextTick()
    const el = menuRef.value
    if (!el) return

    const rect = el.getBoundingClientRect()
    const vw = window.innerWidth, vh = window.innerHeight

    adjustedX.value = props.x + rect.width > vw ? Math.max(0, props.x - rect.width) : props.x
    adjustedY.value = props.y + rect.height > vh ? Math.max(0, props.y - rect.height) : props.y

    // Focus first item for keyboard nav
    el.querySelector('.ctx-item')?.focus()

    document.addEventListener('click', onOutside, true)
    document.addEventListener('scroll', onOutside, true)
    document.addEventListener('keydown', onEsc)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', onOutside, true)
    document.removeEventListener('scroll', onOutside, true)
    document.removeEventListener('keydown', onEsc)
})

function onOutside(e) {
    if (menuRef.value && !menuRef.value.contains(e.target)) emit('close')
}

function onEsc(e) {
    if (e.key === 'Escape') emit('close')
}
</script>

<style scoped>
/* ═══ MENU SHELL ═════════════════════════════════════════════════════ */
.ctx-menu {
    --cm-bg: #ffffff;
    --cm-border: #e2e8f0;
    --cm-text: #0f172a;
    --cm-text2: #475569;
    --cm-text3: #94a3b8;
    --cm-accent: #6366f1;
    --cm-accent-l: rgba(99, 102, 241, .08);
    --cm-danger: #ef4444;
    --cm-danger-l: rgba(239, 68, 68, .07);
    --cm-shadow: 0 12px 40px rgba(0, 0, 0, .18), 0 3px 10px rgba(0, 0, 0, .1);

    background: var(--cm-bg);
    border: 1px solid var(--cm-border);
    border-radius: 12px;
    box-shadow: var(--cm-shadow);
    padding: 5px;
    min-width: 210px;
    max-width: 280px;
    font-size: 12px;
    outline: none;
    animation: ctxIn .14s cubic-bezier(.16, 1, .3, 1);
}

.ctx-dark {
    --cm-bg: #1a2236;
    --cm-border: #263348;
    --cm-text: #e2e8f0;
    --cm-text2: #94a3b8;
    --cm-text3: #475569;
    --cm-accent-l: rgba(129, 140, 248, .1);
    --cm-danger-l: rgba(239, 68, 68, .12);
    --cm-shadow: 0 12px 40px rgba(0, 0, 0, .4), 0 3px 10px rgba(0, 0, 0, .25);
}

@keyframes ctxIn {
    from {
        opacity: 0;
        transform: scale(.94) translateY(-4px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* ═══ HEADER ═════════════════════════════════════════════════════════ */
.ctx-header {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px 11px 5px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--cm-text3);
    border-bottom: 1px solid var(--cm-border);
    margin-bottom: 3px;
}

/* ═══ ITEMS ══════════════════════════════════════════════════════════ */
.ctx-item {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 8px 11px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--cm-text);
    border-radius: 7px;
    text-align: left;
    font-size: 12px;
    font-family: inherit;
    font-weight: 500;
    transition: background .1s, color .1s;
    outline: none;
    position: relative;
}

.ctx-item:hover,
.ctx-item:focus {
    background: var(--cm-accent-l);
    color: var(--cm-accent);
}

.ctx-item--danger {
    color: var(--cm-danger) !important;
}

.ctx-item--danger:hover,
.ctx-item--danger:focus {
    background: var(--cm-danger-l) !important;
    color: var(--cm-danger) !important;
}

.ctx-item span {
    flex: 1;
}

.ctx-icon {
    width: 14px;
    font-size: 12px;
    color: var(--cm-text3);
    flex-shrink: 0;
}

.ctx-item:hover .ctx-icon,
.ctx-item:focus .ctx-icon {
    color: inherit;
}

.ctx-item--danger .ctx-icon {
    color: var(--cm-danger);
}

kbd {
    font-family: monospace;
    font-size: 9px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 4px;
    letter-spacing: .02em;
    background: var(--cm-border);
    color: var(--cm-text3);
    white-space: nowrap;
}

/* ═══ SEPARATOR ══════════════════════════════════════════════════════ */
.ctx-sep {
    height: 1px;
    background: var(--cm-border);
    margin: 4px 8px;
}

/* ═══ SUBMENU ════════════════════════════════════════════════════════ */
.ctx-item--has-sub {
    cursor: default;
}

.ctx-sub-arrow {
    font-size: 9px;
    margin-left: auto;
    color: var(--cm-text3);
}

.ctx-submenu {
    position: absolute;
    left: 100%;
    top: -5px;
    background: var(--cm-bg);
    border: 1px solid var(--cm-border);
    border-radius: 10px;
    padding: 5px;
    min-width: 160px;
    box-shadow: var(--cm-shadow);
    animation: ctxIn .12s cubic-bezier(.16, 1, .3, 1);
    z-index: 9001;
}
</style>