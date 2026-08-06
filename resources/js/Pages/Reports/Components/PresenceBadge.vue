<!--
  PresenceBadge.vue — "Who's editing this report" avatar stack
  • Up to N overlapping avatar circles (initials, per-user color)
  • "+N" overflow bubble when more than `max` editors are active
  • Pulsing live dot + count, hover tooltip listing names
  • Renders nothing when no other editors are present (no layout shift)
  • Dark-mode aware, isolated styles (safe inside the editor shell)
-->
<template>
    <div v-if="editors.length" class="presence-badge" :class="{ 'is-dark': isDark }" :title="tooltipText" role="status"
        :aria-label="`${editors.length} other ${editors.length === 1 ? 'person' : 'people'} editing this report`">
        <div class="presence-stack" aria-hidden="true">
            <span v-for="e in visibleEditors" :key="e.id" class="presence-avatar" :style="{ background: e.color }">{{
                e.initials }}</span>
            <span v-if="overflowCount > 0" class="presence-avatar presence-avatar--more">+{{ overflowCount }}</span>
        </div>
        <span class="presence-count">
            <span class="presence-dot" aria-hidden="true" />
            {{ editors.length }} editing
        </span>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    editors: { type: Array, default: () => [] }, // [{ id, name, initials, color }]
    isDark: { type: Boolean, default: false },
    max: { type: Number, default: 4 },
});

const visibleEditors = computed(() => props.editors.slice(0, props.max));
const overflowCount = computed(() =>
    Math.max(0, props.editors.length - props.max),
);
const tooltipText = computed(() => props.editors.map((e) => e.name).join(", "));
</script>

<style scoped>
.presence-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 10px 4px 4px;
    border-radius: 99px;
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.18);
    flex-shrink: 0;
    cursor: default;
    user-select: none;
}

.is-dark.presence-badge {
    background: rgba(129, 140, 248, 0.12);
    border-color: rgba(129, 140, 248, 0.22);
}

.presence-stack {
    display: flex;
}

.presence-avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 800;
    color: #fff;
    border: 2px solid #fff;
    margin-left: -7px;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.is-dark .presence-avatar {
    border-color: #1a2236;
}

.presence-avatar:first-child {
    margin-left: 0;
}

.presence-avatar--more {
    background: #64748b !important;
    font-size: 8px;
}

.presence-count {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 10.5px;
    font-weight: 700;
    color: #6366f1;
    white-space: nowrap;
}

.is-dark .presence-count {
    color: #a5b4fc;
}

.presence-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #22c55e;
    animation: presencePulse 1.8s ease-in-out infinite;
}

@keyframes presencePulse {

    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.5;
        transform: scale(1.3);
    }
}

/* Hide on narrow toolbars rather than overflow/wrap awkwardly */
@media (max-width: 1100px) {
    .presence-badge {
        display: none;
    }
}
</style>