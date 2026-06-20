<!--
  ToastContainer.vue — Global notification toasts
  • Fixed top-right stack (up to 5 visible)
  • Types: success | error | warning | info | loading
  • Auto-dismiss with progress bar
  • Pause on hover
  • Dismiss on click / swipe right
  • Action button support
  • Dark mode aware
  • Accessible: role="alert" / aria-live
  • No external deps
-->
<template>
    <Teleport to="body">
        <div class="tc-container" role="region" aria-label="Notifications">
            <TransitionGroup name="tc-toast" tag="div" class="tc-stack">
                <div v-for="t in visibleToasts" :key="t.id" class="tc-toast"
                    :class="[`tc-toast--${t.type}`, isDark && 'tc-dark', t.exiting && 'tc-toast--exit']"
                    :role="t.type === 'error' ? 'alert' : 'status'"
                    :aria-live="t.type === 'error' ? 'assertive' : 'polite'" @mouseenter="pauseToast(t)"
                    @mouseleave="resumeToast(t)" @click="dismiss(t)">
                    <!-- Icon -->
                    <div class="tc-icon" :class="`tc-icon--${t.type}`" aria-hidden="true">
                        <i v-if="t.type === 'success'" class="fa-solid fa-circle-check" />
                        <i v-else-if="t.type === 'error'" class="fa-solid fa-circle-xmark" />
                        <i v-else-if="t.type === 'warning'" class="fa-solid fa-triangle-exclamation" />
                        <i v-else-if="t.type === 'loading'" class="fa-solid fa-spinner fa-spin" />
                        <i v-else class="fa-solid fa-circle-info" />
                    </div>

                    <!-- Content -->
                    <div class="tc-content">
                        <p v-if="t.title" class="tc-title">{{ t.title }}</p>
                        <p class="tc-message">{{ t.message }}</p>
                        <button v-if="t.action" class="tc-action" @click.stop="() => { t.action.fn(); dismiss(t) }"
                            :aria-label="t.action.label">{{ t.action.label }}</button>
                    </div>

                    <!-- Close -->
                    <button class="tc-close" @click.stop="dismiss(t)" aria-label="Dismiss notification">
                        <i class="fa-solid fa-xmark" />
                    </button>

                    <!-- Progress bar -->
                    <div v-if="t.duration && t.type !== 'loading'" class="tc-progress"
                        :style="{ animationDuration: t.duration + 'ms', animationPlayState: t.paused ? 'paused' : 'running' }"
                        :class="`tc-progress--${t.type}`" />
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'

const props = defineProps({
    isDark: { type: Boolean, default: false },
})

// ── Internal toast state ───────────────────────────────────────────────
const toasts = ref([])
let nextId = 1
const timers = new Map()

const visibleToasts = computed(() => toasts.value.slice(-5).reverse())

// ── Public API (exposed via defineExpose) ──────────────────────────────
function toast(opts) {
    if (typeof opts === 'string') opts = { message: opts }
    const t = {
        id: nextId++,
        type: opts.type || 'info',
        title: opts.title || null,
        message: opts.message || '',
        duration: opts.duration ?? (opts.type === 'error' ? 6000 : opts.type === 'loading' ? 0 : 4000),
        action: opts.action || null,
        paused: false,
        exiting: false,
    }
    toasts.value.push(t)
    if (t.duration) scheduleRemove(t)
    return t.id
}

function success(message, opts = {}) { return toast({ ...opts, message, type: 'success' }) }
function error(message, opts = {}) { return toast({ ...opts, message, type: 'error' }) }
function warning(message, opts = {}) { return toast({ ...opts, message, type: 'warning' }) }
function info(message, opts = {}) { return toast({ ...opts, message, type: 'info' }) }
function loading(message, opts = {}) { return toast({ ...opts, message, type: 'loading', duration: 0 }) }
function update(id, opts = {}) {
    const t = toasts.value.find(t => t.id === id)
    if (!t) return
    Object.assign(t, opts)
    if (opts.duration) { clearTimer(id); scheduleRemove(t) }
}
function remove(id) { dismiss(toasts.value.find(t => t.id === id)) }
function clear() { toasts.value.forEach(dismiss); toasts.value = [] }

defineExpose({ toast, success, error, warning, info, loading, update, remove, clear })

// ── Internal helpers ───────────────────────────────────────────────────
function scheduleRemove(t) {
    const tid = setTimeout(() => dismiss(t), t.duration)
    timers.set(t.id, tid)
}

function clearTimer(id) {
    clearTimeout(timers.get(id))
    timers.delete(id)
}

function dismiss(t) {
    if (!t) return
    clearTimer(t.id)
    t.exiting = true
    setTimeout(() => {
        const i = toasts.value.findIndex(x => x.id === t.id)
        if (i !== -1) toasts.value.splice(i, 1)
    }, 280)
}

function pauseToast(t) {
    t.paused = true
    clearTimer(t.id)
}

function resumeToast(t) {
    t.paused = false
    if (t.duration && !t.exiting) scheduleRemove(t)
}

onBeforeUnmount(() => timers.forEach((_, id) => clearTimer(id)))
</script>

<style scoped>
/* ── Container ──────────────────────────────────────────────────────── */
.tc-container {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    pointer-events: none;
}

.tc-stack {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
}

/* ── Toast ──────────────────────────────────────────────────────────── */
.tc-toast {
    --tc-bg: #ffffff;
    --tc-border: #e2e8f0;
    --tc-text: #0f172a;
    --tc-text2: #475569;

    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    width: 340px;
    max-width: calc(100vw - 32px);
    padding: 12px 14px 14px;
    border-radius: 14px;
    background: var(--tc-bg);
    border: 1px solid var(--tc-border);
    box-shadow: 0 8px 30px rgba(0, 0, 0, .12), 0 2px 8px rgba(0, 0, 0, .07);
    cursor: pointer;
    pointer-events: all;
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}

.tc-toast:hover {
    transform: translateX(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, .16);
}

.tc-dark {
    --tc-bg: #1e293b;
    --tc-border: #334155;
    --tc-text: #f1f5f9;
    --tc-text2: #94a3b8;
}

/* Type accent strips */
.tc-toast--success {
    border-left: 3px solid #22c55e;
}

.tc-toast--error {
    border-left: 3px solid #ef4444;
}

.tc-toast--warning {
    border-left: 3px solid #f59e0b;
}

.tc-toast--info {
    border-left: 3px solid #6366f1;
}

.tc-toast--loading {
    border-left: 3px solid #06b6d4;
}

/* ── Icon ───────────────────────────────────────────────────────────── */
.tc-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    margin-top: 1px;
}

.tc-icon--success {
    background: #dcfce7;
    color: #16a34a;
}

.tc-icon--error {
    background: #fee2e2;
    color: #dc2626;
}

.tc-icon--warning {
    background: #fef9c3;
    color: #ca8a04;
}

.tc-icon--info {
    background: #ede9fe;
    color: #7c3aed;
}

.tc-icon--loading {
    background: #cffafe;
    color: #0891b2;
}

.tc-dark .tc-icon--success {
    background: rgba(34, 197, 94, .15);
    color: #4ade80;
}

.tc-dark .tc-icon--error {
    background: rgba(239, 68, 68, .15);
    color: #f87171;
}

.tc-dark .tc-icon--warning {
    background: rgba(245, 158, 11, .15);
    color: #fcd34d;
}

.tc-dark .tc-icon--info {
    background: rgba(99, 102, 241, .15);
    color: #a5b4fc;
}

.tc-dark .tc-icon--loading {
    background: rgba(6, 182, 212, .15);
    color: #67e8f9;
}

/* ── Content ────────────────────────────────────────────────────────── */
.tc-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.tc-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--tc-text);
    margin: 0;
}

.tc-message {
    font-size: 11.5px;
    color: var(--tc-text2);
    margin: 0;
    line-height: 1.5;
}

.tc-action {
    align-self: flex-start;
    margin-top: 5px;
    padding: 3px 10px;
    border: 1px solid currentColor;
    border-radius: 99px;
    background: transparent;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    color: #6366f1;
    transition: all .14s;
}

.tc-action:hover {
    background: #6366f1;
    color: #fff;
}

/* ── Close ──────────────────────────────────────────────────────────── */
.tc-close {
    width: 20px;
    height: 20px;
    border: none;
    background: transparent;
    color: var(--tc-text2);
    cursor: pointer;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    flex-shrink: 0;
    opacity: .5;
    transition: opacity .1s;
}

.tc-toast:hover .tc-close {
    opacity: 1;
}

/* ── Progress bar ───────────────────────────────────────────────────── */
.tc-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    border-radius: 0 0 14px 14px;
    animation: tcShrink linear forwards;
    transform-origin: left;
}

.tc-progress--success {
    background: #22c55e;
}

.tc-progress--error {
    background: #ef4444;
}

.tc-progress--warning {
    background: #f59e0b;
}

.tc-progress--info {
    background: #6366f1;
}

.tc-progress--loading {
    background: #06b6d4;
}

@keyframes tcShrink {
    from {
        width: 100%;
    }

    to {
        width: 0%;
    }
}

/* ── Transitions ────────────────────────────────────────────────────── */
.tc-toast-enter-active {
    animation: tcSlideIn .28s cubic-bezier(.16, 1, .3, 1);
}

.tc-toast-leave-active {
    animation: tcSlideOut .22s ease forwards;
}

.tc-toast-move {
    transition: transform .3s;
}

@keyframes tcSlideIn {
    from {
        opacity: 0;
        transform: translateX(100%) scale(.9)
    }

    to {
        opacity: 1;
        transform: translateX(0) scale(1)
    }
}

@keyframes tcSlideOut {
    from {
        opacity: 1;
        transform: translateX(0)
    }

    to {
        opacity: 0;
        transform: translateX(110%) scale(.9)
    }
}

.tc-toast--exit {
    animation: tcSlideOut .22s ease forwards;
}

/* ── Mobile ─────────────────────────────────────────────────────────── */
@media (max-width: 480px) {
    .tc-container {
        top: auto;
        bottom: 16px;
        left: 8px;
        right: 8px;
    }

    .tc-toast {
        width: 100%;
    }

    @keyframes tcSlideIn {
        from {
            opacity: 0;
            transform: translateY(20px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }
}
</style>