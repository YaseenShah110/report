<!--
  ToastContainer.vue — Global Toast Notification System
  ═══════════════════════════════════════════════════════════════════
  Usage (from any parent via ref):
    const toastRef = ref(null)
    toastRef.value?.show('Saved!', 'success')
    toastRef.value?.show('Something failed', 'error', 6000)

  Types:   success | error | warning | info | loading
  Options: message (string), type (string), duration (ms, default 3500)
           pass duration=0 for a persistent toast (must dismiss manually)

  Features:
  • Teleported to <body> — never clipped by overflow:hidden parents
  • Max 5 visible toasts, oldest auto-removed when exceeded
  • Slide-in from right, fade-out before removal (smooth)
  • Progress bar shows remaining display time
  • Click × to dismiss immediately
  • Pause timer on mouse-hover
  • Fully keyboard accessible (role=alert, aria-live)
  • Dark mode aware via CSS variables on .editor-shell or body
  ═══════════════════════════════════════════════════════════════════
-->
<template>
    <Teleport to="body">
        <div class="toast-container" aria-live="polite" aria-atomic="false">
            <TransitionGroup name="toast" tag="div" class="toast-list">
                <div v-for="t in toasts" :key="t.id" class="toast-item" :class="`toast-${t.type}`" role="alert"
                    :aria-label="`${t.type}: ${t.message}`" @mouseenter="pauseTimer(t)" @mouseleave="resumeTimer(t)">
                    <!-- Icon -->
                    <div class="toast-icon" aria-hidden="true">
                        <i :class="getIcon(t.type)" />
                    </div>

                    <!-- Body -->
                    <div class="toast-body">
                        <div class="toast-type-label">{{ getLabel(t.type) }}</div>
                        <div class="toast-message">{{ t.message }}</div>
                    </div>

                    <!-- Dismiss -->
                    <button class="toast-close" @click="dismiss(t.id)" :aria-label="`Dismiss: ${t.message}`"><i
                            class="fa-solid fa-xmark" /></button>

                    <!-- Progress bar -->
                    <div v-if="t.duration > 0" class="toast-progress" :style="{
                        animationDuration: t.duration + 'ms',
                        animationPlayState: t.paused ? 'paused' : 'running',
                    }" />
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { ref } from 'vue'

// ── State ───────────────────────────────────────────────────────────
const toasts = ref([])
const MAX = 5
let nextId = 1

// ── Public API (exposed via ref) ────────────────────────────────────
/**
 * show(message, type?, duration?)
 * @param {string}  message  - Text to display
 * @param {string}  type     - 'success'|'error'|'warning'|'info'|'loading'
 * @param {number}  duration - ms until auto-dismiss (0 = persistent)
 * @returns {number} toast id (pass to dismiss() for manual removal)
 */
function show(message, type = 'info', duration = 3500) {
    // Enforce max; remove oldest if over limit
    if (toasts.value.length >= MAX) {
        dismiss(toasts.value[0].id)
    }

    const id = nextId++
    const toast = {
        id, message, type, duration,
        paused: false,
        timer: null,
    }

    toasts.value.push(toast)

    if (duration > 0) {
        toast.timer = setTimeout(() => dismiss(id), duration)
    }

    return id
}

function dismiss(id) {
    const idx = toasts.value.findIndex(t => t.id === id)
    if (idx === -1) return
    const t = toasts.value[idx]
    clearTimeout(t.timer)
    toasts.value.splice(idx, 1)
}

function pauseTimer(t) {
    if (!t.timer || t.duration <= 0) return
    clearTimeout(t.timer)
    t.timer = null
    t.paused = true
}

function resumeTimer(t) {
    if (t.duration <= 0 || !t.paused) return
    // Restart with half the original duration (approximate remaining)
    t.paused = false
    t.timer = setTimeout(() => dismiss(t.id), t.duration / 2)
}

// ── Helpers ─────────────────────────────────────────────────────────
function getIcon(type) {
    return {
        success: 'fa-solid fa-circle-check',
        error: 'fa-solid fa-circle-xmark',
        warning: 'fa-solid fa-triangle-exclamation',
        info: 'fa-solid fa-circle-info',
        loading: 'fa-solid fa-spinner fa-spin',
    }[type] || 'fa-solid fa-circle-info'
}

function getLabel(type) {
    return { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info', loading: 'Loading' }[type] || 'Info'
}

// ── Expose ref API ──────────────────────────────────────────────────
defineExpose({ show, dismiss })
</script>

<style scoped>
/* ═══ CONTAINER ══════════════════════════════════════════════════════ */
.toast-container {
    position: fixed;
    bottom: 50px;
    /* above status bar */
    right: 20px;
    z-index: 9999;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.toast-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
}

/* ═══ TOAST ITEM ═════════════════════════════════════════════════════ */
.toast-item {
    pointer-events: all;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px 16px;
    /* extra bottom for progress bar */
    min-width: 280px;
    max-width: 400px;
    border-radius: 12px;
    border: 1px solid transparent;
    box-shadow: 0 8px 32px rgba(0, 0, 0, .18), 0 2px 8px rgba(0, 0, 0, .1);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(12px);
    cursor: default;
    /* prevent text selection during quick interactions */
    user-select: none;
}

/* Type colours */
.toast-success {
    background: rgba(240, 253, 244, .97);
    border-color: rgba(16, 185, 129, .25);
    color: #064e3b;
}

.toast-error {
    background: rgba(254, 242, 242, .97);
    border-color: rgba(239, 68, 68, .25);
    color: #7f1d1d;
}

.toast-warning {
    background: rgba(255, 251, 235, .97);
    border-color: rgba(245, 158, 11, .25);
    color: #78350f;
}

.toast-info {
    background: rgba(239, 246, 255, .97);
    border-color: rgba(99, 102, 241, .25);
    color: #1e1b4b;
}

.toast-loading {
    background: rgba(248, 250, 252, .97);
    border-color: rgba(148, 163, 184, .25);
    color: #0f172a;
}

/* Dark-mode: detect via prefers-color-scheme (also works when .editor-dark sets
   a data attribute, adjust selector if needed) */
@media (prefers-color-scheme: dark) {
    .toast-success {
        background: rgba(6, 78, 59, .92);
        border-color: rgba(16, 185, 129, .3);
        color: #d1fae5;
    }

    .toast-error {
        background: rgba(127, 29, 29, .92);
        border-color: rgba(239, 68, 68, .3);
        color: #fee2e2;
    }

    .toast-warning {
        background: rgba(120, 53, 15, .92);
        border-color: rgba(245, 158, 11, .3);
        color: #fef3c7;
    }

    .toast-info {
        background: rgba(30, 27, 75, .92);
        border-color: rgba(99, 102, 241, .3);
        color: #e0e7ff;
    }

    .toast-loading {
        background: rgba(15, 23, 42, .92);
        border-color: rgba(148, 163, 184, .2);
        color: #e2e8f0;
    }
}

/* ═══ ICON ═══════════════════════════════════════════════════════════ */
.toast-icon {
    font-size: 16px;
    flex-shrink: 0;
    margin-top: 1px;
    opacity: .9;
}

.toast-success .toast-icon {
    color: #10b981;
}

.toast-error .toast-icon {
    color: #ef4444;
}

.toast-warning .toast-icon {
    color: #f59e0b;
}

.toast-info .toast-icon {
    color: #6366f1;
}

.toast-loading .toast-icon {
    color: #64748b;
}

/* ═══ BODY ═══════════════════════════════════════════════════════════ */
.toast-body {
    flex: 1;
    min-width: 0;
}

.toast-type-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    opacity: .6;
    margin-bottom: 2px;
}

.toast-message {
    font-size: 12.5px;
    font-weight: 500;
    line-height: 1.4;
    word-break: break-word;
}

/* ═══ CLOSE ══════════════════════════════════════════════════════════ */
.toast-close {
    width: 20px;
    height: 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: currentColor;
    opacity: .45;
    font-size: 11px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity .15s, background .15s;
    flex-shrink: 0;
}

.toast-close:hover {
    opacity: .9;
    background: rgba(0, 0, 0, .08);
}

/* ═══ PROGRESS BAR ═══════════════════════════════════════════════════ */
.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 0 0 12px 12px;
    background: currentColor;
    opacity: .25;
    transform-origin: left;
    animation: toastProgress linear forwards;
}

@keyframes toastProgress {
    from {
        transform: scaleX(1);
    }

    to {
        transform: scaleX(0);
    }
}

/* ═══ TRANSITIONS ════════════════════════════════════════════════════ */
.toast-enter-active {
    animation: toastIn .28s cubic-bezier(.16, 1, .3, 1);
}

.toast-leave-active {
    animation: toastOut .2s ease forwards;
    position: absolute;
}

.toast-move {
    transition: transform .25s ease;
}

@keyframes toastIn {
    from {
        opacity: 0;
        transform: translateX(110%) scale(.92);
    }

    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

@keyframes toastOut {
    from {
        opacity: 1;
        transform: translateX(0) scale(1);
        max-height: 100px;
    }

    to {
        opacity: 0;
        transform: translateX(60%) scale(.9);
        max-height: 0;
        margin: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
}

/* ═══ RESPONSIVE ═════════════════════════════════════════════════════ */
@media (max-width: 500px) {
    .toast-container {
        left: 12px;
        right: 12px;
    }

    .toast-item {
        min-width: unset;
        max-width: 100%;
    }
}
</style>