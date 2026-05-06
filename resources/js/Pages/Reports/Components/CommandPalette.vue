<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   CommandPalette.vue - Quick Actions Search (Ctrl+K)           ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
    <Teleport to="body">
        <div class="palette-backdrop" @click="$emit('close')">
            <div class="palette-dialog" @click.stop>
                <div class="palette-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        ref="searchInput"
                        v-model="query"
                        class="palette-input"
                        placeholder="Type a command..."
                        @keydown.escape="$emit('close')"
                        @keydown.enter="executeSelected"
                        @keydown.down.prevent="moveDown"
                        @keydown.up.prevent="moveUp"
                    />
                    <kbd>esc</kbd>
                </div>

                <div class="palette-results">
                    <div
                        v-for="(cmd, idx) in filteredCommands"
                        :key="cmd.id"
                        class="palette-item"
                        :class="{ active: idx === selectedIdx }"
                        @click="execute(cmd)"
                        @mouseenter="selectedIdx = idx"
                    >
                        <i :class="cmd.icon" class="palette-item-icon"></i>
                        <span class="palette-item-label">{{ cmd.label }}</span>
                        <span class="palette-item-category">{{
                            cmd.category
                        }}</span>
                        <kbd v-if="cmd.shortcut">{{ cmd.shortcut }}</kbd>
                    </div>

                    <div v-if="!filteredCommands.length" class="palette-empty">
                        No commands found
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";

const emit = defineEmits(["close", "execute"]);

const query = ref("");
const selectedIdx = ref(0);
const searchInput = ref(null);

const commands = [
    {
        id: "save",
        label: "Save Report",
        icon: "fa-solid fa-floppy-disk",
        category: "File",
        shortcut: "Ctrl+S",
    },
    {
        id: "undo",
        label: "Undo",
        icon: "fa-solid fa-undo",
        category: "Edit",
        shortcut: "Ctrl+Z",
    },
    {
        id: "redo",
        label: "Redo",
        icon: "fa-solid fa-redo",
        category: "Edit",
        shortcut: "Ctrl+Y",
    },
    {
        id: "delete",
        label: "Delete Element",
        icon: "fa-solid fa-trash",
        category: "Edit",
        shortcut: "Del",
    },
    {
        id: "duplicate",
        label: "Duplicate Element",
        icon: "fa-solid fa-clone",
        category: "Edit",
        shortcut: "Ctrl+D",
    },
    {
        id: "copy",
        label: "Copy Element",
        icon: "fa-solid fa-copy",
        category: "Edit",
        shortcut: "Ctrl+C",
    },
    {
        id: "paste",
        label: "Paste Element",
        icon: "fa-solid fa-paste",
        category: "Edit",
        shortcut: "Ctrl+V",
    },
    {
        id: "select-all",
        label: "Select All Elements",
        icon: "fa-solid fa-check-double",
        category: "Select",
        shortcut: "Ctrl+A",
    },
    {
        id: "deselect",
        label: "Deselect All",
        icon: "fa-solid fa-xmark",
        category: "Select",
        shortcut: "Esc",
    },
    {
        id: "add-page",
        label: "Add New Page",
        icon: "fa-solid fa-plus",
        category: "Page",
        shortcut: "Ctrl+N",
    },
    {
        id: "toggle-grid",
        label: "Toggle Grid",
        icon: "fa-solid fa-border-all",
        category: "View",
        shortcut: "Ctrl+G",
    },
    {
        id: "toggle-dark",
        label: "Toggle Dark Mode",
        icon: "fa-solid fa-moon",
        category: "View",
    },
    {
        id: "toggle-fullscreen",
        label: "Toggle Fullscreen",
        icon: "fa-solid fa-expand",
        category: "View",
        shortcut: "F11",
    },
    {
        id: "zoom-in",
        label: "Zoom In",
        icon: "fa-solid fa-magnifying-glass-plus",
        category: "View",
        shortcut: "Ctrl++",
    },
    {
        id: "zoom-out",
        label: "Zoom Out",
        icon: "fa-solid fa-magnifying-glass-minus",
        category: "View",
        shortcut: "Ctrl+-",
    },
    {
        id: "zoom-fit",
        label: "Reset Zoom",
        icon: "fa-solid fa-magnifying-glass",
        category: "View",
        shortcut: "Ctrl+0",
    },
    {
        id: "preview",
        label: "Preview Report",
        icon: "fa-solid fa-eye",
        category: "File",
    },
    {
        id: "print",
        label: "Print Preview",
        icon: "fa-solid fa-print",
        category: "File",
    },
    {
        id: "presentation",
        label: "Presentation Mode",
        icon: "fa-solid fa-play",
        category: "View",
        shortcut: "Ctrl+F5",
    },
    {
        id: "find-replace",
        label: "Find & Replace",
        icon: "fa-solid fa-magnifying-glass",
        category: "Edit",
        shortcut: "Ctrl+F",
    },
    {
        id: "share",
        label: "Share Report",
        icon: "fa-solid fa-share-nodes",
        category: "File",
    },
    {
        id: "email",
        label: "Email Report",
        icon: "fa-solid fa-envelope",
        category: "File",
    },
];

const filteredCommands = computed(() => {
    if (!query.value) return commands;
    const q = query.value.toLowerCase();
    return commands.filter(
        (c) =>
            c.label.toLowerCase().includes(q) ||
            c.category.toLowerCase().includes(q),
    );
});

function moveDown() {
    selectedIdx.value = Math.min(
        selectedIdx.value + 1,
        filteredCommands.value.length - 1,
    );
}
function moveUp() {
    selectedIdx.value = Math.max(selectedIdx.value - 1, 0);
}
function executeSelected() {
    const cmd = filteredCommands.value[selectedIdx.value];
    if (cmd) execute(cmd);
}
function execute(cmd) {
    emit("execute", cmd.id);
}

onMounted(() => {
    nextTick(() => searchInput.value?.focus());
});
</script>

<style scoped>
.palette-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 15vh;
    z-index: 10000;
    backdrop-filter: blur(4px);
}

.palette-dialog {
    width: 560px;
    max-width: 90vw;
    background: var(--bg-panel, #ffffff);
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    border: 1px solid var(--border, #e2e8f0);
}

.palette-search {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border, #e2e8f0);
}

.palette-search i {
    color: var(--text-muted, #94a3b8);
    font-size: 14px;
}

.palette-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 15px;
    color: var(--text-primary, #0f172a);
    font-family: inherit;
}
.palette-input::placeholder {
    color: var(--text-muted, #94a3b8);
}

.palette-search kbd {
    font-size: 10px;
    color: var(--text-muted, #94a3b8);
    background: var(--bg-secondary, #f8fafc);
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid var(--border, #e2e8f0);
}

.palette-results {
    max-height: 320px;
    overflow-y: auto;
    padding: 6px;
}

.palette-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.1s;
}
.palette-item:hover,
.palette-item.active {
    background: var(--accent-light, rgba(99, 102, 241, 0.08));
}

.palette-item-icon {
    width: 24px;
    text-align: center;
    color: var(--accent, #6366f1);
    font-size: 13px;
}
.palette-item-label {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-primary, #0f172a);
}
.palette-item-category {
    font-size: 10px;
    color: var(--text-muted, #94a3b8);
}

.palette-item kbd {
    font-size: 9px;
    color: var(--text-muted, #94a3b8);
    background: var(--bg-secondary, #f8fafc);
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid var(--border, #e2e8f0);
}

.palette-empty {
    padding: 30px;
    text-align: center;
    color: var(--text-muted, #94a3b8);
    font-size: 13px;
}
</style>
