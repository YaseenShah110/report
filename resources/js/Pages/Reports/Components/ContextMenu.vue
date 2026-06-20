<!--
  ContextMenu.vue — Right-click context menu for editor canvas
  • Shows on right-click of element or page
  • Keyboard navigable (arrow up/down, enter, escape)
  • Groups: Edit | Format | Layer | Page | Danger
  • Dividers between groups
  • Shortcut hints
  • Auto-positions to stay within viewport
  • Dark mode aware
  • Closes on outside click / scroll / escape
  • Memory safe — cleans all listeners on unmount
-->
<template>
    <Teleport to="body">
        <Transition name="ctx-fade">
            <div v-if="show" ref="menuRef" class="ctx-menu" :class="{ 'is-dark': isDark }" :style="menuStyle"
                role="menu" aria-label="Context menu" @keydown="onKeyDown" tabindex="-1">
                <template v-for="(item, idx) in items" :key="idx">
                    <!-- Divider -->
                    <div v-if="item === '---'" class="ctx-divider" role="separator" aria-hidden="true" />

                    <!-- Group label -->
                    <div v-else-if="item.type === 'label'" class="ctx-group-label">
                        {{ item.label }}
                    </div>

                    <!-- Menu item -->
                    <button v-else class="ctx-item" :class="{
                        'ctx-item--danger': item.danger,
                        'ctx-item--disabled': item.disabled,
                        'ctx-item--selected': focusedIdx === idx,
                    }" :disabled="item.disabled" role="menuitem" :aria-label="item.label" :aria-disabled="item.disabled"
                        @click="execute(item)" @mouseenter="focusedIdx = idx">
                        <span class="ctx-item-icon" aria-hidden="true">
                            <i :class="item.icon || 'fa-solid fa-circle-dot'" />
                        </span>
                        <span class="ctx-item-label">{{ item.label }}</span>
                        <kbd v-if="item.shortcut" class="ctx-shortcut" aria-label="`Shortcut: ${item.shortcut}`">
                            {{ item.shortcut }}
                        </kbd>
                        <i v-if="item.submenu" class="fa-solid fa-chevron-right ctx-arrow" aria-hidden="true" />
                    </button>
                </template>
            </div>
        </Transition>

        <!-- Backdrop — invisible click catcher -->
        <div v-if="show" class="ctx-backdrop" @click="$emit('close')" @contextmenu.prevent="$emit('close')"
            aria-hidden="true" />
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    x: { type: Number, default: 0 },
    y: { type: Number, default: 0 },
    items: { type: Array, default: () => [] },
    isDark: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'action'])

const menuRef = ref(null)
const focusedIdx = ref(-1)

// Auto-position: keep menu inside viewport
const menuStyle = computed(() => {
    const W = window.innerWidth
    const H = window.innerHeight
    const mw = 230  // approx menu width
    const mh = props.items.length * 34  // approx height

    const left = props.x + mw > W ? Math.max(4, props.x - mw) : props.x
    const top = props.y + mh > H ? Math.max(4, props.y - mh) : props.y

    return { left: left + 'px', top: top + 'px' }
})

// Focus menu on open, reset focused index
watch(() => props.show, async (v) => {
    if (v) {
        focusedIdx.value = -1
        await nextTick()
        menuRef.value?.focus()
        window.addEventListener('scroll', onScroll, { passive: true })
    } else {
        window.removeEventListener('scroll', onScroll)
    }
})

function onScroll() { emit('close') }

function execute(item) {
    if (item.disabled) return
    emit('close')
    if (typeof item.action === 'function') item.action()
    else emit('action', item.id || item.label)
}

// Keyboard navigation
function onKeyDown(e) {
    const actionableItems = props.items
        .map((item, idx) => ({ item, idx }))
        .filter(({ item }) => item !== '---' && item.type !== 'label' && !item.disabled)

    const currentPos = actionableItems.findIndex(({ idx }) => idx === focusedIdx.value)

    if (e.key === 'ArrowDown') {
        e.preventDefault()
        const next = actionableItems[currentPos + 1] || actionableItems[0]
        focusedIdx.value = next?.idx ?? -1
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        const prev = actionableItems[currentPos - 1] || actionableItems[actionableItems.length - 1]
        focusedIdx.value = prev?.idx ?? -1
    } else if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault()
        const focused = props.items[focusedIdx.value]
        if (focused && focused !== '---' && focused.type !== 'label') execute(focused)
    } else if (e.key === 'Escape') {
        emit('close')
    }
}

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
/* ── Base ──────────────────────────────────────────────────────────── */
.ctx-menu {
    --ctx-bg: #ffffff;
    --ctx-border: #e2e8f0;
    --ctx-text: #0f172a;
    --ctx-text2: #64748b;
    --ctx-hover: #f1f5f9;
    --ctx-accent: #6366f1;
    --ctx-danger: #ef4444;
    --ctx-danger-l: rgba(239, 68, 68, .06);
    --ctx-shadow: 0 10px 40px rgba(0, 0, 0, .14), 0 2px 8px rgba(0, 0, 0, .08);

    position: fixed;
    z-index: 9999;
    min-width: 220px;
    max-width: 280px;
    background: var(--ctx-bg);
    border: 1px solid var(--ctx-border);
    border-radius: 12px;
    box-shadow: var(--ctx-shadow);
    padding: 5px;
    outline: none;
}

.ctx-menu.is-dark {
    --ctx-bg: #1e2a3d;
    --ctx-border: #263348;
    --ctx-text: #e2e8f0;
    --ctx-text2: #64748b;
    --ctx-hover: #263348;
}

/* ── Backdrop ──────────────────────────────────────────────────────── */
.ctx-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9998;
    cursor: default;
}

/* ── Divider ───────────────────────────────────────────────────────── */
.ctx-divider {
    height: 1px;
    background: var(--ctx-border);
    margin: 4px 8px;
}

/* ── Group label ───────────────────────────────────────────────────── */
.ctx-group-label {
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--ctx-text2);
    padding: 6px 12px 2px;
}

/* ── Item ──────────────────────────────────────────────────────────── */
.ctx-item {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 8px 10px;
    border: none;
    background: transparent;
    border-radius: 7px;
    cursor: pointer;
    color: var(--ctx-text);
    font-size: 12px;
    font-weight: 500;
    text-align: left;
    transition: background .1s, color .1s;
    font-family: inherit;
    position: relative;
}

.ctx-item:hover:not(:disabled),
.ctx-item--selected:not(:disabled) {
    background: var(--ctx-hover);
}

.ctx-item--danger {
    color: var(--ctx-danger);
}

.ctx-item--danger:hover,
.ctx-item--danger.ctx-item--selected {
    background: var(--ctx-danger-l);
    color: var(--ctx-danger);
}

.ctx-item--disabled {
    opacity: .38;
    cursor: not-allowed;
    pointer-events: none;
}

/* ── Icon ──────────────────────────────────────────────────────────── */
.ctx-item-icon {
    width: 18px;
    text-align: center;
    font-size: 12px;
    color: var(--ctx-text2);
    flex-shrink: 0;
}

.ctx-item--danger .ctx-item-icon {
    color: var(--ctx-danger);
}

.ctx-item:hover .ctx-item-icon {
    color: var(--ctx-accent);
}

.ctx-item--danger:hover .ctx-item-icon {
    color: var(--ctx-danger);
}

/* ── Label ─────────────────────────────────────────────────────────── */
.ctx-item-label {
    flex: 1;
}

/* ── Shortcut ──────────────────────────────────────────────────────── */
.ctx-shortcut {
    font-size: 9px;
    font-weight: 600;
    color: var(--ctx-text2);
    background: var(--ctx-hover);
    border: 1px solid var(--ctx-border);
    border-radius: 4px;
    padding: 1px 5px;
    white-space: nowrap;
    font-family: inherit;
}

/* ── Submenu arrow ─────────────────────────────────────────────────── */
.ctx-arrow {
    font-size: 9px;
    color: var(--ctx-text2);
    opacity: .6;
}

/* ── Transition ────────────────────────────────────────────────────── */
.ctx-fade-enter-active {
    animation: ctxIn .13s cubic-bezier(.16, 1, .3, 1);
}

.ctx-fade-leave-active {
    animation: ctxIn .09s ease reverse;
}

@keyframes ctxIn {
    from {
        opacity: 0;
        transform: scale(.95) translateY(-4px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>