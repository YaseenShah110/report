<template>
    <transition name="dropdown">
        <div v-if="contextMenu.show"
            class="fixed z-[9999] w-52 rounded-xl shadow-xl border overflow-hidden py-1"
            :class="dark ? 'bg-slate-800 border-slate-700' : 'bg-white border-slate-200'"
            :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }">
            <button v-for="item in menuItems" :key="item.label"
                @click="item.action(); $emit('close')"
                class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-left transition-colors"
                :class="item.danger
                    ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10'
                    : dark ? 'text-slate-300 hover:bg-white/10' : 'text-slate-600 hover:bg-slate-50'">
                <i :class="item.icon + ' text-sm w-4 text-center'"></i>
                <span class="flex-1">{{ item.label }}</span>
                <kbd v-if="item.key" class="text-[9px] font-mono px-1 rounded"
                    :class="dark ? 'text-slate-500 bg-slate-700' : 'text-slate-400 bg-slate-100'">{{ item.key }}</kbd>
            </button>
        </div>
    </transition>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    dark: Boolean,
    contextMenu: Object,
    pages: Array,
    selectedElement: Object,
    selectedPageIndex: Number,
});

const emit = defineEmits([
    "close","duplicate","delete","lock","move-to-page",
    "bring-to-front","send-to-back","push-history",
]);

const menuItems = computed(() => {
    if (!props.contextMenu.el) return [];
    const el = props.contextMenu.el;
    const pi = props.contextMenu.pi;
    return [
        { icon: "fa-solid fa-copy",     label: "Duplicate",        key: "Ctrl+D", action: () => emit("duplicate", pi, el) },
        { icon: "fa-solid fa-arrow-up",   label: "Bring to Front",  key: null,     action: () => emit("bring-to-front") },
        { icon: "fa-solid fa-arrow-down", label: "Send to Back",    key: null,     action: () => emit("send-to-back") },
        {
            icon: el.hidden ? "fa-solid fa-eye" : "fa-solid fa-eye-slash",
            label: el.hidden ? "Show Element" : "Hide Element",
            key: null,
            action: () => { el.hidden = !el.hidden; emit("push-history"); },
        },
        {
            icon: el.locked ? "fa-solid fa-lock-open" : "fa-solid fa-lock",
            label: el.locked ? "Unlock" : "Lock",
            key: null,
            action: () => emit("lock", el),
        },
        ...(pi > 0 ? [{
            icon: "fa-solid fa-angle-up",
            label: "Move to Prev Page", key: null,
            action: () => emit("move-to-page", el, pi, pi - 1),
        }] : []),
        ...(pi < props.pages.length - 1 ? [{
            icon: "fa-solid fa-angle-down",
            label: "Move to Next Page", key: null,
            action: () => emit("move-to-page", el, pi, pi + 1),
        }] : []),
        { icon: "fa-solid fa-trash", label: "Delete", key: "Del", action: () => emit("delete", pi, el.id), danger: true },
    ];
});
</script>

<style scoped>
.dropdown-enter-active { transition: all 0.15s ease-out; }
.dropdown-leave-active { transition: all 0.1s ease-in; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-4px) scale(0.97); }
</style>