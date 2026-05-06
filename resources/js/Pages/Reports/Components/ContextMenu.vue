<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   ContextMenu.vue - Right-Click Context Menu                   ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
    <Teleport to="body">
        <Transition name="context">
            <div
                v-if="show"
                class="context-menu"
                :style="{ left: x + 'px', top: y + 'px' }"
                @click.stop
            >
                <template v-for="(item, idx) in items" :key="idx">
                    <div v-if="item === '---'" class="context-sep"></div>
                    <button
                        v-else
                        @click="
                            item.action();
                            $emit('close');
                        "
                        :class="{ 'context-danger': item.danger }"
                        :disabled="item.disabled"
                    >
                        <span class="context-icon" v-if="item.icon"
                            ><i :class="item.icon"></i
                        ></span>
                        <span class="context-label">{{ item.label }}</span>
                        <kbd v-if="item.shortcut" class="context-shortcut">{{
                            item.shortcut
                        }}</kbd>
                    </button>
                </template>
            </div>
        </Transition>
        <div
            v-if="show"
            class="context-backdrop"
            @click="$emit('close')"
            @contextmenu.prevent="$emit('close')"
        ></div>
    </Teleport>
</template>

<script setup>
defineProps({
    show: { type: Boolean, default: false },
    x: { type: Number, default: 0 },
    y: { type: Number, default: 0 },
    items: { type: Array, default: () => [] },
});
defineEmits(["close"]);
</script>

<style scoped>
.context-menu {
    position: fixed;
    z-index: 10000;
    min-width: 200px;
    max-width: 280px;
    background: var(--bg-panel, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
    box-shadow:
        0 12px 40px rgba(0, 0, 0, 0.15),
        0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 6px;
    overflow: hidden;
}

.context-menu button {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text-primary, #0f172a);
    font-size: 12px;
    font-weight: 500;
    text-align: left;
    border-radius: 6px;
    transition: all 0.1s;
    font-family: inherit;
}
.context-menu button:hover:not(:disabled) {
    background: var(--bg-secondary, #f8fafc);
}
.context-menu button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.context-menu button.context-danger {
    color: var(--danger, #ef4444);
}
.context-menu button.context-danger:hover:not(:disabled) {
    background: rgba(239, 68, 68, 0.06);
}

.context-icon {
    width: 20px;
    text-align: center;
    font-size: 12px;
    opacity: 0.7;
    flex-shrink: 0;
}
.context-label {
    flex: 1;
}

.context-shortcut {
    font-size: 10px;
    color: var(--text-muted, #94a3b8);
    background: var(--bg-secondary, #f8fafc);
    padding: 1px 6px;
    border-radius: 4px;
    border: 1px solid var(--border, #e2e8f0);
    font-weight: 500;
}

.context-sep {
    height: 1px;
    background: var(--border, #e2e8f0);
    margin: 4px 8px;
}
.context-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
}

.context-enter-active {
    animation: ctxIn 0.12s ease;
}
.context-leave-active {
    animation: ctxIn 0.1s ease reverse;
}
@keyframes ctxIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-4px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>
