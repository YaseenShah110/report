<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   TopToolbar.vue - Top Bar + Formatting Ribbon + All Controls  ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
    <div>
        <!-- ═══ TOP BAR ═══════════════════════════════════════════════ -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button
                    class="icon-btn"
                    @click="goBack"
                    title="Back to Reports"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <div class="divider-v"></div>

                <div class="title-group">
                    <div class="title-row">
                        <input
                            :value="report.title"
                            @input="$emit('update:title', $event.target.value)"
                            @blur="$emit('save')"
                            class="title-input"
                            placeholder="Untitled Report"
                            spellcheck="false"
                        />
                        <button
                            class="status-pill"
                            :class="report.status"
                            @click="$emit('change-status')"
                            title="Click to cycle: Draft → Published → Archived"
                        >
                            <span class="status-dot"></span>
                            {{ report.status }}
                            <i
                                class="fa-solid fa-chevron-down status-arrow"
                            ></i>
                        </button>
                    </div>
                    <div
                        class="save-indicator"
                        :class="{
                            saving: isSaving,
                            saved: lastSaved && !isDirty,
                        }"
                    >
                        <template v-if="isSaving"
                            ><i class="fa-solid fa-spinner fa-spin"></i>
                            Saving...</template
                        >
                        <template v-else-if="lastSaved && !isDirty"
                            ><i class="fa-solid fa-check-circle"></i> Saved
                            {{ lastSaved }}</template
                        >
                        <template v-else-if="isDirty"
                            ><span class="pulse-dot"></span> Unsaved
                            changes</template
                        >
                    </div>
                </div>
            </div>

            <div class="top-bar-center">
                <!-- Undo/Redo -->
                <button
                    class="icon-btn"
                    @click="$emit('undo')"
                    :disabled="!canUndo"
                    title="Undo (Ctrl+Z)"
                >
                    <i class="fa-solid fa-undo"></i>
                </button>
                <button
                    class="icon-btn"
                    @click="$emit('redo')"
                    :disabled="!canRedo"
                    title="Redo (Ctrl+Y)"
                >
                    <i class="fa-solid fa-redo"></i>
                </button>
                <div class="divider-v"></div>

                <!-- Zoom -->
                <button
                    class="icon-btn"
                    @click="$emit('zoom-out')"
                    title="Zoom Out (Ctrl+-)"
                >
                    <i class="fa-solid fa-minus"></i>
                </button>
                <button
                    class="zoom-display"
                    @click="$emit('zoom-reset')"
                    title="Reset Zoom (100%)"
                >
                    {{ zoom }}%
                </button>
                <button
                    class="icon-btn"
                    @click="$emit('zoom-in')"
                    title="Zoom In (Ctrl++)"
                >
                    <i class="fa-solid fa-plus"></i>
                </button>
                <div class="divider-v"></div>

                <!-- Canvas Controls -->
                <button
                    class="icon-btn"
                    :class="{ active: showGrid }"
                    @click="$emit('toggle-grid')"
                    title="Toggle Grid (Ctrl+G)"
                >
                    <i class="fa-solid fa-border-all"></i>
                </button>
                <button
                    class="icon-btn"
                    :class="{ active: snapToGrid }"
                    @click="$emit('toggle-snap')"
                    title="Snap to Grid"
                >
                    <i class="fa-solid fa-magnet"></i>
                </button>
                <button
                    class="icon-btn"
                    :class="{ active: showRulers }"
                    @click="$emit('toggle-rulers')"
                    title="Rulers"
                >
                    <i class="fa-solid fa-ruler-combined"></i>
                </button>
                <button
                    class="icon-btn"
                    @click="$emit('toggle-measure')"
                    title="Measure Tool (Ctrl+M)"
                >
                    <i class="fa-solid fa-ruler"></i>
                </button>
                <div class="divider-v"></div>

                <!-- Find & Replace -->
                <button
                    class="icon-btn"
                    @click="$emit('toggle-find')"
                    title="Find & Replace (Ctrl+F)"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <!-- AI -->
                <button
                    class="icon-btn ai-btn"
                    :class="{ active: showAI }"
                    @click="$emit('toggle-ai')"
                    title="AI Assistant (Ctrl+Space)"
                >
                    <i class="fa-solid fa-wand-magic-sparkles"></i> AI
                </button>

                <!-- Presentation -->
                <button
                    class="icon-btn"
                    @click="$emit('presentation')"
                    title="Presentation Mode (Ctrl+F5)"
                >
                    <i class="fa-solid fa-play"></i>
                </button>
            </div>

            <div class="top-bar-right">
                <!-- Panel Toggles -->
                <button
                    class="icon-btn"
                    :class="{ active: !leftCollapsed }"
                    @click="$emit('toggle-left-panel')"
                    title="Toggle Left Panel"
                >
                    <i class="fa-solid fa-sidebar"></i>
                </button>
                <button
                    class="icon-btn"
                    :class="{ active: !rightCollapsed }"
                    @click="$emit('toggle-right-panel')"
                    title="Toggle Right Panel"
                >
                    <i class="fa-solid fa-sidebar-flip"></i>
                </button>
                <div class="divider-v"></div>

                <!-- Preview & Print -->
                <button
                    class="btn-secondary"
                    @click="$emit('preview')"
                    title="Preview Report (Ctrl+P)"
                >
                    <i class="fa-solid fa-eye"></i>
                    <span class="btn-label">Preview</span>
                </button>
                <button
                    class="btn-secondary"
                    @click="$emit('print-preview')"
                    title="Print Preview"
                >
                    <i class="fa-solid fa-print"></i>
                    <span class="btn-label">Print</span>
                </button>

                <!-- Export Dropdown -->
                <div class="dropdown-wrap">
                    <button
                        class="btn-primary"
                        @click="showExport = !showExport"
                    >
                        <i class="fa-solid fa-download"></i>
                        <span class="btn-label">Export</span>
                    </button>
                    <Transition name="dropdown">
                        <div v-if="showExport" class="dropdown-menu">
                            <div class="dropdown-header">Export As</div>
                            <button @click="doExport('pdf')">
                                <span class="export-icon pdf"
                                    ><i class="fa-solid fa-file-pdf"></i
                                ></span>
                                <div>
                                    <strong>PDF Document</strong
                                    ><small>Best for printing</small>
                                </div>
                            </button>
                            <button @click="doExport('png')">
                                <span class="export-icon img"
                                    ><i class="fa-solid fa-file-image"></i
                                ></span>
                                <div>
                                    <strong>PNG Image</strong
                                    ><small>High quality screenshot</small>
                                </div>
                            </button>
                            <button @click="doExport('excel')">
                                <span class="export-icon xls"
                                    ><i class="fa-solid fa-file-excel"></i
                                ></span>
                                <div>
                                    <strong>Excel</strong
                                    ><small>Tables & data</small>
                                </div>
                            </button>
                            <button @click="doExport('csv')">
                                <span class="export-icon csv"
                                    ><i class="fa-solid fa-file-csv"></i
                                ></span>
                                <div>
                                    <strong>CSV Data</strong
                                    ><small>Raw data export</small>
                                </div>
                            </button>
                            <hr class="dropdown-sep" />
                            <button @click="$emit('share')">
                                <i class="fa-solid fa-share-nodes"></i> Share
                                Link
                            </button>
                            <button @click="$emit('email-report')">
                                <i class="fa-solid fa-envelope"></i> Email
                                Report
                            </button>
                        </div>
                    </Transition>
                </div>

                <!-- Share -->
                <button
                    class="icon-btn"
                    @click="$emit('share')"
                    title="Share Report"
                >
                    <i class="fa-solid fa-share-nodes"></i>
                </button>

                <!-- Theme -->
                <button
                    class="icon-btn"
                    @click="$emit('toggle-dark')"
                    :title="isDark ? 'Light Mode' : 'Dark Mode'"
                >
                    <i
                        :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"
                    ></i>
                </button>

                <!-- Fullscreen -->
                <button
                    class="icon-btn"
                    @click="$emit('toggle-fullscreen')"
                    :title="
                        isFullscreen ? 'Exit Fullscreen' : 'Fullscreen (F11)'
                    "
                >
                    <i
                        :class="
                            isFullscreen
                                ? 'fa-solid fa-compress'
                                : 'fa-solid fa-expand'
                        "
                    ></i>
                </button>
            </div>
        </header>

        <!-- ═══ FORMATTING RIBBON ══════════════════════════════════════ -->
        <Transition name="slide-down">
            <div v-if="selectedEl && !selectedEl.locked" class="ribbon">
                <div class="ribbon-inner">
                    <!-- Font -->
                    <select
                        :value="selectedEl.styles?.fontFamily || 'Inter'"
                        @change="
                            $emit(
                                'apply-style',
                                'fontFamily',
                                $event.target.value,
                            )
                        "
                        class="ribbon-select font-select"
                    >
                        <option v-for="f in fontList" :key="f" :value="f">
                            {{ f }}
                        </option>
                    </select>
                    <select
                        :value="selectedEl.styles?.fontSize || 14"
                        @change="
                            $emit(
                                'apply-style',
                                'fontSize',
                                +$event.target.value,
                            )
                        "
                        class="ribbon-select size-select"
                    >
                        <option v-for="s in fontSizes" :key="s" :value="s">
                            {{ s }}px
                        </option>
                    </select>
                    <div class="ribbon-sep"></div>
                    <!-- Text Style -->
                    <button
                        class="ribbon-btn"
                        :class="{
                            active: selectedEl.styles?.fontWeight === '700',
                        }"
                        @click="$emit('toggle-fmt', 'fontWeight', '700', '400')"
                        title="Bold (Ctrl+B)"
                    >
                        <b>B</b>
                    </button>
                    <button
                        class="ribbon-btn italic-btn"
                        :class="{
                            active: selectedEl.styles?.fontStyle === 'italic',
                        }"
                        @click="
                            $emit('toggle-fmt', 'fontStyle', 'italic', 'normal')
                        "
                        title="Italic (Ctrl+I)"
                    >
                        <i>I</i>
                    </button>
                    <button
                        class="ribbon-btn"
                        :class="{
                            active:
                                selectedEl.styles?.textDecoration ===
                                'underline',
                        }"
                        @click="
                            $emit(
                                'toggle-fmt',
                                'textDecoration',
                                'underline',
                                'none',
                            )
                        "
                        title="Underline (Ctrl+U)"
                    >
                        <u>U</u>
                    </button>
                    <button
                        class="ribbon-btn strikethrough-btn"
                        :class="{
                            active:
                                selectedEl.styles?.textDecoration ===
                                'line-through',
                        }"
                        @click="
                            $emit(
                                'toggle-fmt',
                                'textDecoration',
                                'line-through',
                                'none',
                            )
                        "
                        title="Strikethrough"
                    >
                        <s>S</s>
                    </button>
                    <div class="ribbon-sep"></div>
                    <!-- Colors -->
                    <div class="color-picker-wrap">
                        <span
                            class="color-swatch text-swatch"
                            :style="{
                                background: selectedEl.styles?.color || '#000',
                            }"
                            >A</span
                        ><input
                            type="color"
                            :value="selectedEl.styles?.color || '#000'"
                            @input="
                                $emit(
                                    'apply-style',
                                    'color',
                                    $event.target.value,
                                )
                            "
                            class="color-hidden"
                        />
                    </div>
                    <div class="color-picker-wrap">
                        <span
                            class="color-swatch bg-swatch"
                            :style="{
                                background:
                                    selectedEl.styles?.backgroundColor ===
                                    'transparent'
                                        ? '#fff'
                                        : selectedEl.styles?.backgroundColor ||
                                          '#fff',
                            }"
                            ><i class="fa-solid fa-fill-drip"></i></span
                        ><input
                            type="color"
                            :value="
                                selectedEl.styles?.backgroundColor ===
                                'transparent'
                                    ? '#fff'
                                    : selectedEl.styles?.backgroundColor ||
                                      '#fff'
                            "
                            @input="
                                $emit(
                                    'apply-style',
                                    'backgroundColor',
                                    $event.target.value,
                                )
                            "
                            class="color-hidden"
                        />
                    </div>
                    <div class="ribbon-sep"></div>
                    <!-- Alignment -->
                    <button
                        class="ribbon-btn"
                        :class="{
                            active: selectedEl.styles?.textAlign === 'left',
                        }"
                        @click="$emit('apply-style', 'textAlign', 'left')"
                    >
                        <i class="fa-solid fa-align-left"></i>
                    </button>
                    <button
                        class="ribbon-btn"
                        :class="{
                            active: selectedEl.styles?.textAlign === 'center',
                        }"
                        @click="$emit('apply-style', 'textAlign', 'center')"
                    >
                        <i class="fa-solid fa-align-center"></i>
                    </button>
                    <button
                        class="ribbon-btn"
                        :class="{
                            active: selectedEl.styles?.textAlign === 'right',
                        }"
                        @click="$emit('apply-style', 'textAlign', 'right')"
                    >
                        <i class="fa-solid fa-align-right"></i>
                    </button>
                    <button
                        class="ribbon-btn"
                        :class="{
                            active: selectedEl.styles?.textAlign === 'justify',
                        }"
                        @click="$emit('apply-style', 'textAlign', 'justify')"
                    >
                        <i class="fa-solid fa-align-justify"></i>
                    </button>
                    <div class="ribbon-sep"></div>
                    <!-- Layer Controls -->
                    <button
                        class="ribbon-btn"
                        @click="$emit('bring-front')"
                        title="Bring to Front"
                    >
                        <i class="fa-solid fa-angles-up"></i>
                    </button>
                    <button
                        class="ribbon-btn"
                        @click="$emit('send-back')"
                        title="Send to Back"
                    >
                        <i class="fa-solid fa-angles-down"></i>
                    </button>
                    <button
                        class="ribbon-btn"
                        @click="$emit('duplicate-el')"
                        title="Duplicate (Ctrl+D)"
                    >
                        <i class="fa-solid fa-clone"></i>
                    </button>
                    <button
                        class="ribbon-btn"
                        :class="{ active: selectedEl?.locked }"
                        @click="$emit('lock-el')"
                        title="Lock/Unlock"
                    >
                        <i
                            :class="
                                selectedEl?.locked
                                    ? 'fa-solid fa-lock'
                                    : 'fa-solid fa-lock-open'
                            "
                        ></i>
                    </button>
                    <div class="ribbon-spacer"></div>
                    <!-- Delete -->
                    <button
                        class="ribbon-btn danger-btn"
                        @click="$emit('delete-el')"
                        title="Delete (Del)"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    report: Object,
    settings: Object,
    isDirty: Boolean,
    isSaving: Boolean,
    lastSaved: String,
    zoom: Number,
    canUndo: Boolean,
    canRedo: Boolean,
    selectedEl: Object,
    showGrid: Boolean,
    snapToGrid: Boolean,
    showRulers: Boolean,
    isDark: Boolean,
    isFullscreen: Boolean,
    showAI: Boolean,
    leftCollapsed: Boolean,
    rightCollapsed: Boolean,
});

const emit = defineEmits([
    "update:title",
    "save",
    "undo",
    "redo",
    "zoom-in",
    "zoom-out",
    "zoom-reset",
    "toggle-grid",
    "toggle-snap",
    "toggle-rulers",
    "toggle-dark",
    "toggle-fullscreen",
    "toggle-ai",
    "preview",
    "print-preview",
    "export-pdf",
    "export-png",
    "export-excel",
    "export-csv",
    "share",
    "change-status",
    "apply-style",
    "toggle-fmt",
    "delete-el",
    "duplicate-el",
    "lock-el",
    "bring-front",
    "send-back",
    "toggle-left-panel",
    "toggle-right-panel",
    "toggle-measure",
    "toggle-find",
    "presentation",
    "email-report",
]);

const showExport = ref(false);
const fontList = [
    "Inter",
    "DM Sans",
    "Plus Jakarta Sans",
    "Space Grotesk",
    "Sora",
    "Outfit",
    "Nunito",
    "Georgia",
    "Playfair Display",
    "Times New Roman",
    "Courier New",
    "Fira Code",
];
const fontSizes = [
    8, 9, 10, 11, 12, 13, 14, 16, 18, 20, 24, 28, 32, 36, 42, 48, 56, 64, 72,
    96, 128,
];

function goBack() {
    router.visit(route("reports.index"));
}
function doExport(type) {
    showExport.value = false;
    const map = {
        pdf: "export-pdf",
        png: "export-png",
        excel: "export-excel",
        csv: "export-csv",
    };
    emit(map[type]);
}
</script>

<style scoped>
.top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 48px;
    padding: 0 10px;
    background: var(--bg-panel, #fff);
    border-bottom: 1px solid var(--border, #e2e8f0);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    gap: 6px;
    z-index: 100;
    flex-shrink: 0;
}
.top-bar-left,
.top-bar-center,
.top-bar-right {
    display: flex;
    align-items: center;
    gap: 6px;
}
.top-bar-center {
    flex: 1;
    justify-content: center;
}
.title-group {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
}
.title-row {
    display: flex;
    align-items: center;
    gap: 8px;
}
.title-input {
    background: transparent;
    border: none;
    outline: none;
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    min-width: 150px;
    max-width: 260px;
    padding: 3px 8px;
    border-radius: 6px;
    transition: background 0.15s;
}
.title-input:hover {
    background: var(--bg-secondary);
}
.title-input:focus {
    background: var(--bg-secondary);
    box-shadow: 0 0 0 2px var(--accent);
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 99px;
    font-size: 10px;
    font-weight: 700;
    text-transform: capitalize;
    letter-spacing: 0.03em;
    border: 1.5px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    background: transparent;
}
.status-pill:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.status-pill.draft {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border-color: rgba(245, 158, 11, 0.3);
}
.status-pill.published {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border-color: rgba(16, 185, 129, 0.3);
}
.status-pill.archived {
    background: rgba(148, 163, 184, 0.1);
    color: #64748b;
    border-color: rgba(148, 163, 184, 0.3);
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    animation: statusPulse 2s ease-in-out infinite;
}
.status-arrow {
    font-size: 8px;
    opacity: 0.6;
}
@keyframes statusPulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
.save-indicator {
    font-size: 10px;
    color: var(--text-muted);
    padding-left: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.save-indicator.saving {
    color: var(--accent);
}
.save-indicator.saved {
    color: var(--success);
}
.pulse-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--warning);
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.5);
    }
}
.divider-v {
    width: 1px;
    height: 22px;
    background: var(--border);
    flex-shrink: 0;
}
.icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 7px;
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 13px;
    transition: all 0.15s;
    flex-shrink: 0;
}
.icon-btn:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.icon-btn.active {
    background: var(--accent-light);
    color: var(--accent);
}
.icon-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
.icon-btn.ai-btn {
    width: auto;
    padding: 0 10px;
    font-size: 11px;
    font-weight: 700;
    gap: 5px;
    background: linear-gradient(
        135deg,
        rgba(99, 102, 241, 0.08),
        rgba(139, 92, 246, 0.05)
    );
    border: 1px solid rgba(99, 102, 241, 0.15);
}
.icon-btn.ai-btn:hover {
    background: linear-gradient(
        135deg,
        rgba(99, 102, 241, 0.15),
        rgba(139, 92, 246, 0.1)
    );
    border-color: rgba(99, 102, 241, 0.3);
}
.icon-btn.ai-btn.active {
    background: linear-gradient(135deg, var(--accent), #8b5cf6);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 2px 12px rgba(99, 102, 241, 0.4);
}
.zoom-group {
    display: flex;
    align-items: center;
    gap: 1px;
}
.zoom-display {
    width: 48px;
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-primary);
    cursor: pointer;
    padding: 4px 0;
    border-radius: 5px;
    background: transparent;
    border: none;
    transition: background 0.15s;
}
.zoom-display:hover {
    background: var(--bg-secondary);
}
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border: none;
    background: var(--accent);
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.15s;
    box-shadow: 0 1px 3px rgba(99, 102, 241, 0.3);
}
.btn-primary:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
}
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 10px;
    border: 1px solid var(--border);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 7px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 500;
    transition: all 0.15s;
}
.btn-secondary:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-light);
}
.btn-label {
    display: none;
}
@media (min-width: 768px) {
    .btn-label {
        display: inline;
    }
}
.dropdown-wrap {
    position: relative;
}
.dropdown-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    padding: 6px;
    min-width: 240px;
    z-index: 300;
}
.dropdown-header {
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    padding: 6px 10px 4px;
}
.dropdown-menu button {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 8px 10px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text-primary);
    font-size: 12px;
    border-radius: 7px;
    text-align: left;
    transition: background 0.1s;
}
.dropdown-menu button:hover {
    background: var(--bg-secondary);
}
.dropdown-menu button div {
    display: flex;
    flex-direction: column;
}
.dropdown-menu button strong {
    font-weight: 600;
    font-size: 12px;
}
.dropdown-menu button small {
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 1px;
}
.export-icon {
    width: 32px;
    height: 32px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #fff;
    flex-shrink: 0;
}
.export-icon.pdf {
    background: #ef4444;
}
.export-icon.img {
    background: #8b5cf6;
}
.export-icon.xls {
    background: #10b981;
}
.export-icon.csv {
    background: #3b82f6;
}
.dropdown-sep {
    border: none;
    border-top: 1px solid var(--border);
    margin: 4px 8px;
}
.dropdown-enter-active {
    animation: dropIn 0.15s ease;
}
.dropdown-leave-active {
    animation: dropIn 0.1s ease reverse;
}
@keyframes dropIn {
    from {
        opacity: 0;
        transform: translateY(-4px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.ribbon {
    background: var(--bg-panel);
    border-bottom: 1px solid var(--border);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    flex-shrink: 0;
}
.ribbon-inner {
    display: flex;
    align-items: center;
    gap: 2px;
    height: 40px;
    padding: 0 10px;
    overflow-x: auto;
    scrollbar-width: none;
}
.ribbon-inner::-webkit-scrollbar {
    display: none;
}
.ribbon-group {
    display: flex;
    align-items: center;
    gap: 1px;
}
.ribbon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    border-radius: 5px;
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 12px;
    transition: all 0.12s;
    flex-shrink: 0;
}
.ribbon-btn:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.ribbon-btn.active {
    background: var(--accent-light);
    color: var(--accent);
    font-weight: 700;
}
.ribbon-btn.danger-btn:hover {
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444;
}
.ribbon-btn.italic-btn i {
    font-style: italic;
}
.ribbon-btn.strikethrough-btn s {
    text-decoration: line-through;
}
.ribbon-select {
    border: 1px solid var(--border);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 5px;
    padding: 4px 6px;
    font-size: 11px;
    cursor: pointer;
    height: 28px;
    outline: none;
    font-family: inherit;
    transition: border-color 0.15s;
}
.ribbon-select:hover {
    border-color: var(--accent);
}
.ribbon-select:focus {
    border-color: var(--accent);
}
.font-select {
    width: 125px;
}
.size-select {
    width: 58px;
}
.ribbon-sep {
    width: 1px;
    height: 20px;
    background: var(--border);
    margin: 0 5px;
    flex-shrink: 0;
}
.ribbon-spacer {
    flex: 1;
}
.color-picker-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.color-swatch {
    width: 22px;
    height: 22px;
    border-radius: 4px;
    border: 1.5px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    overflow: hidden;
    transition: border-color 0.15s;
}
.text-swatch {
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}
.bg-swatch {
    color: var(--text-muted);
}
.color-hidden {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    top: 0;
    left: 0;
}
.slide-down-enter-active {
    animation: slideDown 0.2s ease;
}
.slide-down-leave-active {
    animation: slideDown 0.15s ease reverse;
}
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-6px);
        max-height: 0;
    }
    to {
        opacity: 1;
        transform: translateY(0);
        max-height: 40px;
    }
}
@media (max-width: 768px) {
    .title-input {
        max-width: 140px;
        font-size: 13px;
    }
    .save-indicator {
        display: none;
    }
    .zoom-group {
        display: none;
    }
    .ribbon {
        display: none;
    }
    .btn-label {
        display: none;
    }
}
@media (max-width: 480px) {
    .top-bar-center {
        display: none;
    }
    .title-input {
        max-width: 100px;
    }
}
</style>
