<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   ReportGen ULTIMATE - Editor.vue (Master Orchestrator)        ║
  ║   All 150 Features - Template-Ready - No Browser Shortcuts     ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
    <div
        ref="editorShell"
        class="editor-shell"
        :class="{ dark: isDark, fullscreen: isFullscreen }"
        @keydown.prevent="handleKeyboard"
        tabindex="0"
    >
        <!-- ═══ TOP TOOLBAR ═══════════════════════════════════════════ -->
        <TopToolbar
            :report="report"
            :settings="settings"
            :is-dirty="isDirty"
            :is-saving="isSaving"
            :last-saved="lastSaved"
            :zoom="zoom"
            :can-undo="canUndo"
            :can-redo="canRedo"
            :selected-el="selectedEl"
            :show-grid="showGrid"
            :snap-to-grid="snapToGrid"
            :show-rulers="showRulers"
            :is-dark="isDark"
            :is-fullscreen="isFullscreen"
            :show-ai="showAI"
            :left-collapsed="leftCollapsed"
            :right-collapsed="rightCollapsed"
            @update:title="
                report.title = $event;
                markDirty();
            "
            @save="saveNow"
            @undo="undo"
            @redo="redo"
            @zoom-in="zoomIn"
            @zoom-out="zoomOut"
            @zoom-reset="zoom = 100"
            @toggle-grid="showGrid = !showGrid"
            @toggle-snap="snapToGrid = !snapToGrid"
            @toggle-rulers="showRulers = !showRulers"
            @toggle-dark="toggleDark"
            @toggle-fullscreen="toggleFullscreen"
            @toggle-ai="showAI = !showAI"
            @preview="previewReport"
            @print-preview="printPreview"
            @export-pdf="exportFile('pdf')"
            @export-png="exportFile('image')"
            @export-excel="exportFile('excel')"
            @export-csv="exportFile('csv')"
            @share="shareReport"
            @change-status="cycleStatus"
            @apply-style="applyStyle"
            @toggle-fmt="toggleFmt"
            @delete-el="deleteSelected"
            @duplicate-el="duplicateSelected"
            @lock-el="lockElement"
            @bring-front="bringToFront"
            @send-back="sendToBack"
            @toggle-left-panel="leftCollapsed = !leftCollapsed"
            @toggle-right-panel="rightCollapsed = !rightCollapsed"
            @toggle-measure="measureMode = !measureMode"
            @toggle-find="showFindReplace = !showFindReplace"
            @presentation="startPresentation"
            @email-report="emailReport"
        />

        <!-- ═══ MAIN WORKSPACE ════════════════════════════════════════ -->
        <div class="editor-body">
            <!-- ─── LEFT SIDEBAR ──────────────────────────────────────── -->
            <LeftSidebar
                :report="report"
                :settings="settings"
                :current-page="currentPage"
                :selected-el-idx="selectedElIdx"
                :selected-els="selectedEls"
                :active-tab="activeLeftTab"
                :is-collapsed="leftCollapsed"
                @add-element-center="addElementCenter"
                @select-page="goToPage"
                @add-page="addPage"
                @duplicate-page="duplicatePage"
                @delete-page="deletePage"
                @rename-page="renamePage"
                @select-element="selectElementByIdx"
                @toggle-visibility="toggleVis"
                @toggle-lock="toggleLock"
                @upload-image="handleImageUpload"
                @apply-template="applyQuickTemplate"
                @deselect-all="deselectAll"
                @update:settings="
                    Object.assign(settings, $event);
                    markDirty();
                "
                @update:active-tab="activeLeftTab = $event"
                @canvas-drag-start="onElDragStart"
                @update:is-collapsed="leftCollapsed = $event"
            />

            <!-- ─── CANVAS ────────────────────────────────────────────── -->
            <EditorCanvas
                :report="report"
                :settings="settings"
                :current-page="currentPage"
                :selected-el-idx="selectedElIdx"
                :selected-els="selectedEls"
                :editing-el-idx="editingElIdx"
                :zoom="zoom"
                :show-grid="showGrid"
                :snap-to-grid="snapToGrid"
                :show-rulers="showRulers"
                :is-dragging-el="isDraggingEl"
                :rubber-band="rubberBand"
                :drop-target-page="dropTargetPage"
                :measure-mode="measureMode"
                :presentation-mode="presentationMode"
                :presentation-page="presentationPage"
                :is-dark="isDark"
                @select-element="selectElementByIdx"
                @deselect-all="deselectAll"
                @add-element="addElementAtPosition"
                @select-page="goToPage"
                @add-page="addPage"
                @start-editing="startEditing"
                @update-text-content="updateTextContent"
                @element-mouse-down="onElementMouseDown"
                @resize-start="startResize"
                @rotate-start="startRotate"
                @canvas-drop="onCanvasDrop"
                @canvas-drag-end="isDraggingEl = false"
                @rubber-band-start="startRubberBand"
                @rubber-band-move="handleRubberBandMove"
                @rubber-band-end="endRubberBand"
                @zoom-wheel="handleZoomWheel"
                @page-dblclick="onPageDblClick"
                @context-menu="showElContextMenu"
                @image-upload="triggerImageUpload"
                @image-replace="triggerImageReplace"
                @go-to-page="goToPage"
                @mark-dirty="markDirty"
                @zoom-reset="zoom = 100"
            />

            <!-- ─── RIGHT SIDEBAR ─────────────────────────────────────── -->
            <RightSidebar
                :selected-el="selectedEl"
                :settings="settings"
                :active-section="activeRightSection"
                :current-page-elements="currentPageElements"
                :clipboard="clipboard"
                :style-painter-clipboard="stylePainterClipboard"
                :report="report"
                :is-collapsed="rightCollapsed"
                :is-dark="isDark"
                @update:style="applyStyle"
                @update:content="updateElementContent"
                @delete-el="deleteSelected"
                @duplicate-el="duplicateSelected"
                @copy-el="copyElement"
                @paste-el="pasteElement"
                @lock-el="lockElement"
                @bring-front="bringToFront"
                @send-back="sendToBack"
                @align-to-page="alignToPage"
                @update:settings="
                    Object.assign(settings, $event);
                    markDirty();
                "
                @update:active-section="activeRightSection = $event"
                @add-table-row="addTableRow"
                @add-table-col="addTableColumn"
                @remove-table-row="removeTableRow"
                @remove-table-col="removeTableColumn"
                @set-chart-labels="setChartLabels"
                @set-chart-values="setChartValues"
                @add-timeline-item="addTimelineItem"
                @remove-timeline-item="removeTimelineItem"
                @add-checklist-item="addChecklistItem"
                @remove-checklist-item="removeChecklistItem"
                @add-stat-item="addStatItem"
                @remove-stat-item="removeStatItem"
                @reset-styles="resetElementStyles"
                @style-painter-copy="stylePainterCopy"
                @style-painter-paste="stylePainterPaste"
                @mark-dirty="markDirty"
                @image-replace="triggerImageReplace"
                @refresh-toc="refreshTOC"
                @update:is-collapsed="rightCollapsed = $event"
            />
        </div>

        <!-- ═══ STATUS BAR ═══════════════════════════════════════════ -->
        <StatusBar
            :current-page="currentPage"
            :total-pages="report.content.length"
            :elements-count="currentPageElements.length"
            :total-elements="totalElements"
            :selected-el="selectedEl"
            :zoom="zoom"
            :is-dirty="isDirty"
            :is-saving="isSaving"
            :last-saved="lastSaved"
            :page-size="settings.page_size"
            :orientation="settings.orientation"
            :words-count="wordsCount"
            :chars-count="charsCount"
            :is-dark="isDark"
            @zoom-reset="zoom = 100"
        />

        <!-- ═══ ALL OVERLAY PANELS ═══════════════════════════════════ -->
        <AiPanel
          v-if="showAI"
            :visible="showAI"
            :report="report"
            :is-dark="isDark"
            :selected-element="selectedEl"
            @close="showAI = false"
            @insert-content="insertAiContent"
            @insert-chart="insertAiChart"
        />

        <CommandPalette
            v-if="showCommandPalette"
            @close="showCommandPalette = false"
            @execute="executeCommand"
        />
        <ShortcutOverlay v-if="showShortcuts" @close="showShortcuts = false" />
        <OnboardingTour v-if="showOnboarding" @complete="completeOnboarding" />
        <ContextMenu
            :show="contextMenu.show"
            :x="contextMenu.x"
            :y="contextMenu.y"
            :items="contextMenu.items"
            @close="contextMenu.show = false"
        />
        <ConfettiOverlay v-if="showConfetti" @complete="showConfetti = false" />
        <ToastContainer :toasts="toasts" @remove="removeToast" />

        <!-- ═══ FIND & REPLACE ═══════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="ai-slide">
                <div v-if="showFindReplace" class="fr-panel">
                    <div class="fr-header">
                        <span>Find & Replace</span
                        ><button @click="showFindReplace = false">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="fr-body">
                        <div class="fr-row">
                            <input
                                v-model="findText"
                                placeholder="Find..."
                                @input="findInReport"
                                class="fr-input"
                            />
                        </div>
                        <div class="fr-row">
                            <input
                                v-model="replaceText"
                                placeholder="Replace with..."
                                class="fr-input"
                            />
                        </div>
                        <div class="fr-count" v-if="findMatches.length">
                            {{ findMatches.length }} matches
                        </div>
                        <div class="fr-actions">
                            <button @click="replaceAll" class="fr-btn">
                                Replace All</button
                            ><button
                                @click="replaceOne"
                                class="fr-btn secondary"
                            >
                                Replace Next
                            </button>
                        </div>
                        <div class="fr-results">
                            <div
                                v-for="(m, i) in findMatches.slice(0, 20)"
                                :key="i"
                                class="fr-match"
                                @click="goToMatch(m)"
                            >
                                {{ m.preview }}
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ PRESENTATION OVERLAY ═══════════════════════════════════ -->
        <Teleport to="body">
            <div
                v-if="presentationMode"
                class="pres-overlay"
                @click="nextSlide"
            >
                <div
                    class="pres-page"
                    v-if="report.content[presentationPage]"
                    :style="getPresPageStyle()"
                >
                    <div
                        v-for="el in report.content[presentationPage]
                            ?.elements || []"
                        :key="el.id"
                        :style="getPresElStyle(el)"
                        v-html="el.content"
                    ></div>
                </div>
                <div class="pres-controls">
                    <button
                        @click.stop="prevSlide"
                        :disabled="presentationPage === 0"
                    >
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <span
                        >{{ presentationPage + 1 }}/{{
                            report.content.length
                        }}</span
                    >
                    <button @click.stop="nextSlide">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <button @click.stop="presentationMode = false">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- ═══ HIDDEN FILE INPUT ═══════════════════════════════════ -->
        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            multiple
            @change="handleFilePick"
        />
    </div>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    onMounted,
    onBeforeUnmount,
    nextTick,
    watch,
} from "vue";
import { router } from "@inertiajs/vue3";

// ═══ Component Imports ═══════════════════════════════════════════
import TopToolbar from "./Components/TopToolbar.vue";
import LeftSidebar from "./Components/LeftSidebar.vue";
import EditorCanvas from "./Components/EditorCanvas.vue";
import RightSidebar from "./Components/RightSidebar.vue";
import StatusBar from "./Components/StatusBar.vue";
import AiPanel from "./Components/AiPanel.vue";
import CommandPalette from "./Components/CommandPalette.vue";
import ShortcutOverlay from "./Components/ShortcutOverlay.vue";
import OnboardingTour from "./Components/OnboardingTour.vue";
import ContextMenu from "./Components/ContextMenu.vue";
import ConfettiOverlay from "./Components/ConfettiOverlay.vue";
import ToastContainer from "./Components/ToastContainer.vue";

// ═══════════════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════════════
const props = defineProps({ report: { type: Object, required: true } });

// ═══════════════════════════════════════════════════════════════════
// DEFAULT SETTINGS
// ═══════════════════════════════════════════════════════════════════
function defaultSettingsObj() {
    return {
        page_size: "A4",
        orientation: "portrait",
        margin: 40,
        page_radius: 0,
        background_color: "#ffffff",
        bg_image: "",
        primary_color: "#6366f1",
        accent_color: "#8b5cf6",
        text_color: "#0f172a",
        font_family: "Inter",
        font_size: 14,
        show_header: false,
        header_text: "",
        header_color: "#1e293b",
        show_footer: false,
        footer_left: "",
        footer_right: "",
        show_page_numbers: true,
        watermark: "",
        watermark_opacity: 5,
        rtl: false,
        custom_w: 794,
        custom_h: 1123,
    };
}

// ═══════════════════════════════════════════════════════════════════
// CORE REACTIVE STATE
// ═══════════════════════════════════════════════════════════════════
const report = reactive(JSON.parse(JSON.stringify(props.report)));
const defaults = defaultSettingsObj();
const incomingSettings = props.report?.settings || {};
const mergedSettings = { ...defaults, ...incomingSettings };
const settings = reactive(JSON.parse(JSON.stringify(mergedSettings)));

// Initialize content if empty
if (!report.content?.length) {
    report.content = [{ id: uid(), label: "Page 1", elements: [] }];
}

// ═══════════════════════════════════════════════════════════════════
// UI STATE
// ═══════════════════════════════════════════════════════════════════
const isDark = ref(
    localStorage.getItem("theme") === "dark" ||
        (!localStorage.getItem("theme") &&
            window.matchMedia?.("(prefers-color-scheme: dark)").matches),
);
const isFullscreen = ref(false);
const zoom = ref(100);
const showGrid = ref(true);
const snapToGrid = ref(true);
const showRulers = ref(false);
const showAI = ref(false);
const showCommandPalette = ref(false);
const showShortcuts = ref(false);
const showConfetti = ref(false);
const showFindReplace = ref(false);
const showOnboarding = ref(!localStorage.getItem("rg_onboarded"));
const isDirty = ref(false);
const isSaving = ref(false);
const lastSaved = ref("");
const editorShell = ref(null);
const fileInput = ref(null);
const leftCollapsed = ref(false);
const rightCollapsed = ref(false);
const measureMode = ref(false);
const presentationMode = ref(false);
const presentationPage = ref(0);

// ═══════════════════════════════════════════════════════════════════
// SELECTION
// ═══════════════════════════════════════════════════════════════════
const currentPage = ref(0);
const selectedElIdx = ref(null);
const selectedEls = ref([]);
const editingElIdx = ref(null);
const isDraggingEl = ref(false);
const dropTargetPage = ref(null);
const activeLeftTab = ref("elements");
const activeRightSection = ref("props");

// ═══════════════════════════════════════════════════════════════════
// CLIPBOARD & FIND
// ═══════════════════════════════════════════════════════════════════
const clipboard = ref(null);
const stylePainterClipboard = ref(null);
const currentImageTarget = ref(null);
const findText = ref("");
const replaceText = ref("");
const findMatches = ref([]);

// ═══════════════════════════════════════════════════════════════════
// RUBBER BAND
// ═══════════════════════════════════════════════════════════════════
const rubberBand = reactive({
    active: false,
    startX: 0,
    startY: 0,
    x: 0,
    y: 0,
    w: 0,
    h: 0,
});

// ═══════════════════════════════════════════════════════════════════
// CONTEXT MENU
// ═══════════════════════════════════════════════════════════════════
const contextMenu = reactive({ show: false, x: 0, y: 0, items: [] });

// ═══════════════════════════════════════════════════════════════════
// TOASTS
// ═══════════════════════════════════════════════════════════════════
const toasts = ref([]);
let toastId = 0;

// ═══════════════════════════════════════════════════════════════════
// UNDO/REDO
// ═══════════════════════════════════════════════════════════════════
const undoStack = ref([]);
const redoStack = ref([]);

// ═══════════════════════════════════════════════════════════════════
// TIMERS
// ═══════════════════════════════════════════════════════════════════
let saveTimer = null;
let autoSaveInterval = null;

// ═══════════════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════════════
const currentPageElements = computed(
    () => report.content[currentPage.value]?.elements || [],
);
const selectedEl = computed(() =>
    selectedElIdx.value !== null &&
    currentPageElements.value[selectedElIdx.value]
        ? currentPageElements.value[selectedElIdx.value]
        : null,
);
const canUndo = computed(() => undoStack.value.length > 0);
const canRedo = computed(() => redoStack.value.length > 0);
const totalElements = computed(() =>
    report.content.reduce((s, p) => s + (p.elements?.length || 0), 0),
);
const wordsCount = computed(() => {
    let w = 0;
    report.content.forEach((p) =>
        p.elements?.forEach((e) => {
            if (e.content && typeof e.content === "string")
                w += e.content
                    .replace(/<[^>]*>/g, "")
                    .split(/\s+/)
                    .filter(Boolean).length;
        }),
    );
    return w;
});
const charsCount = computed(() => {
    let c = 0;
    report.content.forEach((p) =>
        p.elements?.forEach((e) => {
            if (e.content && typeof e.content === "string")
                c += e.content.replace(/<[^>]*>/g, "").length;
        }),
    );
    return c;
});

// ═══════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════
function uid() {
    return crypto.randomUUID
        ? crypto.randomUUID()
        : Math.random().toString(36).slice(2) + Date.now().toString(36);
}
function getCsrf() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || ""
    );
}
function getPageDims() {
    const sz = {
        A4: { portrait: { w: 794, h: 1123 }, landscape: { w: 1123, h: 794 } },
        Letter: {
            portrait: { w: 816, h: 1056 },
            landscape: { w: 1056, h: 816 },
        },
        Legal: {
            portrait: { w: 816, h: 1344 },
            landscape: { w: 1344, h: 816 },
        },
        A3: { portrait: { w: 1123, h: 1587 }, landscape: { w: 1587, h: 1123 } },
        A5: { portrait: { w: 559, h: 794 }, landscape: { w: 794, h: 559 } },
        custom: {
            portrait: {
                w: settings.custom_w || 794,
                h: settings.custom_h || 1123,
            },
            landscape: {
                w: settings.custom_h || 1123,
                h: settings.custom_w || 794,
            },
        },
    };
    return sz[settings.page_size]?.[settings.orientation] || sz.A4.portrait;
}

// ═══════════════════════════════════════════════════════════════════
// TOAST
// ═══════════════════════════════════════════════════════════════════
function showToast(msg, type = "success", dur = 3000) {
    const id = ++toastId;
    toasts.value.push({ id, message: msg, type });
    setTimeout(() => removeToast(id), dur);
}
function removeToast(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

// ═══════════════════════════════════════════════════════════════════
// SAVE
// ═══════════════════════════════════════════════════════════════════
function markDirty() {
    isDirty.value = true;
    clearTimeout(saveTimer);
    saveTimer = setTimeout(autoSave, 1500);
}
async function autoSave() {
    if (isDirty.value && !isSaving.value) await saveNow();
}
async function saveNow() {
    if (isSaving.value) return;
    isSaving.value = true;
    try {
        const res = await fetch(route("reports.update", report.slug), {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrf(),
                Accept: "application/json",
            },
            body: JSON.stringify({
                title: report.title,
                content: report.content,
                settings: JSON.parse(JSON.stringify(settings)),
            }),
        });
        if (res.ok) {
            isDirty.value = false;
            lastSaved.value = new Date().toLocaleTimeString();
        }
        localStorage.setItem(
            "rg_draft_" + report.slug,
            JSON.stringify({
                content: report.content,
                settings: JSON.parse(JSON.stringify(settings)),
                savedAt: Date.now(),
            }),
        );
    } catch (e) {
        showToast("Save failed", "error");
    }
    isSaving.value = false;
}

// ═══════════════════════════════════════════════════════════════════
// UNDO/REDO
// ═══════════════════════════════════════════════════════════════════
function pushUndo() {
    undoStack.value.push(
        JSON.stringify({
            content: report.content,
            settings: JSON.parse(JSON.stringify(settings)),
        }),
    );
    if (undoStack.value.length > 100) undoStack.value.shift();
    redoStack.value = [];
    isDirty.value = true;
}
function undo() {
    if (!undoStack.value.length) return;
    redoStack.value.push(
        JSON.stringify({
            content: report.content,
            settings: JSON.parse(JSON.stringify(settings)),
        }),
    );
    const s = JSON.parse(undoStack.value.pop());
    report.content = s.content;
    Object.assign(settings, s.settings);
    selectedElIdx.value = null;
    selectedEls.value = [];
}
function redo() {
    if (!redoStack.value.length) return;
    undoStack.value.push(
        JSON.stringify({
            content: report.content,
            settings: JSON.parse(JSON.stringify(settings)),
        }),
    );
    const s = JSON.parse(redoStack.value.pop());
    report.content = s.content;
    Object.assign(settings, s.settings);
    selectedElIdx.value = null;
    selectedEls.value = [];
}

// ═══════════════════════════════════════════════════════════════════
// ELEMENT CREATION
// ═══════════════════════════════════════════════════════════════════
function createElement(def, x, y) {
    const snap = snapToGrid.value ? (v) => Math.round(v / 10) * 10 : (v) => v;
    const el = {
        id: uid(),
        type: def.type,
        content:
            def.defaultContent ||
            (def.type === "text"
                ? "Start typing..."
                : def.type === "richtext"
                  ? "<p>Start typing...</p>"
                  : ""),
        position: { x: Math.max(0, snap(x)), y: Math.max(0, snap(y)) },
        styles: {
            width: def.w || 200,
            height: def.h || 80,
            fontSize:
                def.type === "heading"
                    ? 28
                    : def.type === "subheading"
                      ? 20
                      : 14,
            fontWeight: def.type === "heading" ? "700" : "400",
            fontFamily: settings.font_family || "Inter",
            color: settings.text_color || "#0f172a",
            textAlign: "left",
            lineHeight: 1.5,
            letterSpacing: 0,
            backgroundColor: ["rectangle", "circle", "triangle"].includes(
                def.type,
            )
                ? settings.primary_color || "#6366f1"
                : "transparent",
            opacity: 100,
            borderRadius: 0,
            rotate: 0,
            zIndex: (currentPageElements.value.length || 0) + 1,
            borderWidth: 0,
            borderColor: "#000",
            borderStyle: "solid",
            boxShadow: "none",
            filter: "none",
            mixBlendMode: "normal",
            padding: 8,
            textTransform: "none",
            textDecoration: "none",
            fontStyle: "normal",
            scaleX: 1,
            scaleY: 1,
            blur: 0,
            brightness: 100,
            contrast: 100,
            grayscale: 0,
        },
        locked: false,
        visible: true,
    };
    // Type-specific defaults
    if (el.type === "metric") {
        el.label = "Revenue";
        el.value = "$48K";
        el.change = "+12%";
        el.changeType = "positive";
        el.styles.backgroundColor = "#f8fafc";
        el.styles.borderRadius = 12;
    }
    if (el.type === "table") {
        el.columns = ["Col 1", "Col 2", "Col 3"];
        el.data = [{ "Col 1": "", "Col 2": "", "Col 3": "" }];
        el.styles.backgroundColor = "#fff";
    }
    if (el.type?.endsWith("-chart")) {
        el.chartTitle = def.label || "Chart";
        el.chartData = {
            labels: ["Q1", "Q2", "Q3", "Q4"],
            values: [25, 40, 35, 55],
        };
        el.styles.backgroundColor = "#fff";
        el.styles.borderRadius = 8;
    }
    if (el.type === "progress") {
        el.label = "Progress";
        el.value = 65;
    }
    if (el.type === "checklist")
        el.items = [
            { text: "Task 1", checked: false },
            { text: "Task 2", checked: false },
        ];
    if (el.type === "timeline")
        el.items = [
            { date: "Q1", label: "Start", desc: "Description" },
            { date: "Q2", label: "Launch", desc: "Description" },
        ];
    if (el.type === "stat-row") {
        el.stats = [
            { value: "12.4K", label: "Users" },
            { value: "$48K", label: "Revenue" },
            { value: "94%", label: "Satisfaction" },
        ];
        el.styles.backgroundColor = "#f8fafc";
        el.styles.borderRadius = 8;
    }
    if (el.type === "testimonial") {
        el.author = "Jane Doe";
        el.role = "CEO";
        el.styles.borderRadius = 12;
    }
    if (el.type === "callout") {
        el.emoji = "💡";
        el.styles.backgroundColor =
            (settings.primary_color || "#6366f1") + "15";
        el.styles.borderRadius = 8;
    }
    if (el.type === "rating") el.value = 4;
    if (el.type === "qr-code") {
        el.qrText = "https://example.com";
        el.qrSize = 150;
        el.styles.backgroundColor = "#fff";
        el.styles.borderRadius = 8;
    }
    if (el.type === "video") {
        el.videoUrl = "";
        el.styles.backgroundColor = "#000";
        el.styles.borderRadius = 8;
    }
    if (el.type === "map") {
        el.mapAddress = "";
        el.styles.backgroundColor = "#e2e8f0";
        el.styles.borderRadius = 8;
    }
    if (el.type === "sparkline") {
        el.sparkData = [3, 7, 4, 9, 6, 8, 5, 10, 7, 9];
        el.styles.backgroundColor = "transparent";
        el.styles.height = 40;
    }
    if (el.type === "toc") {
        el.tocItems = [];
        el.content = "Table of Contents";
    }
    if (el.type === "richtext") {
        el.content = "<p>Start typing with rich formatting...</p>";
    }
    return el;
}

function addElement(def, x, y) {
    pushUndo();
    const el = createElement(def, x, y);
    currentPageElements.value.push(el);
    selectedElIdx.value = currentPageElements.value.length - 1;
    selectedEls.value = [];
    markDirty();
    return el;
}
function addElementCenter(def) {
    const dims = getPageDims();
    addElement(def, (dims.w - (def.w || 200)) / 2, dims.h / 3);
}
function addElementAtPosition({ def, x, y }) {
    addElement(def, x || 100, y || 100);
}

// ═══════════════════════════════════════════════════════════════════
// ELEMENT OPERATIONS
// ═══════════════════════════════════════════════════════════════════
function selectElementByIdx(idx) {
    selectedElIdx.value = idx;
    selectedEls.value = [];
    editingElIdx.value = null;
}
function deselectAll() {
    selectedElIdx.value = null;
    selectedEls.value = [];
    editingElIdx.value = null;
}
function startEditing({ pageIndex, elementIndex }) {
    if (currentPageElements.value[elementIndex]?.locked) return;
    selectElementByIdx(elementIndex);
    editingElIdx.value = elementIndex;
}
function deleteSelected() {
    if (selectedElIdx.value === null) return;
    pushUndo();
    currentPageElements.value.splice(selectedElIdx.value, 1);
    selectedElIdx.value = null;
    selectedEls.value = [];
    markDirty();
}
function duplicateSelected() {
    if (!selectedEl.value) return;
    pushUndo();
    const copy = JSON.parse(JSON.stringify(selectedEl.value));
    copy.id = uid();
    copy.position.x += 24;
    copy.position.y += 24;
    copy.styles.zIndex = (copy.styles.zIndex || 1) + 1;
    currentPageElements.value.push(copy);
    selectedElIdx.value = currentPageElements.value.length - 1;
    markDirty();
}
function copyElement() {
    if (selectedEl.value) {
        clipboard.value = JSON.parse(JSON.stringify(selectedEl.value));
        showToast("Copied", "success");
    }
}
function pasteElement() {
    if (!clipboard.value) return;
    pushUndo();
    const copy = JSON.parse(JSON.stringify(clipboard.value));
    copy.id = uid();
    copy.position.x += 30;
    copy.position.y += 30;
    currentPageElements.value.push(copy);
    selectedElIdx.value = currentPageElements.value.length - 1;
    markDirty();
}
function lockElement() {
    if (selectedEl.value) {
        selectedEl.value.locked = !selectedEl.value.locked;
        markDirty();
    }
}
function bringToFront() {
    if (selectedEl.value) {
        const mz = Math.max(
            ...currentPageElements.value.map((e) => e.styles?.zIndex || 1),
            0,
        );
        selectedEl.value.styles.zIndex = mz + 1;
        markDirty();
    }
}
function sendToBack() {
    if (selectedEl.value) {
        selectedEl.value.styles.zIndex = 0;
        markDirty();
    }
}
function toggleVis(idx) {
    const el = currentPageElements.value[idx];
    if (el) {
        el.visible = el.visible === false ? true : false;
        markDirty();
    }
}
function toggleLock(idx) {
    const el = currentPageElements.value[idx];
    if (el) {
        el.locked = !el.locked;
        markDirty();
    }
}
function applyStyle(prop, val) {
    if (!selectedEl.value) return;
    if (!selectedEl.value.styles) selectedEl.value.styles = {};
    selectedEl.value.styles[prop] = val;
    markDirty();
}
function toggleFmt(prop, onVal, offVal) {
    if (!selectedEl.value?.styles) return;
    applyStyle(prop, selectedEl.value.styles[prop] === onVal ? offVal : onVal);
}
function updateElementContent(content) {
    if (selectedEl.value) {
        selectedEl.value.content = content;
        markDirty();
    }
}
function updateTextContent({ content }) {
    if (selectedEl.value) {
        selectedEl.value.content = content;
        markDirty();
    }
}
function resetElementStyles() {
    if (!selectedEl.value) return;
    selectedEl.value.styles = createElement(
        { type: "text", w: 200, h: 80 },
        0,
        0,
    ).styles;
    markDirty();
}
function stylePainterCopy() {
    if (selectedEl.value?.styles) {
        stylePainterClipboard.value = JSON.parse(
            JSON.stringify(selectedEl.value.styles),
        );
        showToast("Style copied!", "success");
    }
}
function stylePainterPaste() {
    if (stylePainterClipboard.value && selectedEl.value) {
        selectedEl.value.styles = JSON.parse(
            JSON.stringify(stylePainterClipboard.value),
        );
        markDirty();
        showToast("Style applied!", "success");
    }
}

// ═══════════════════════════════════════════════════════════════════
// TABLE / CHART / LIST OPERATIONS
// ═══════════════════════════════════════════════════════════════════
function addTableRow() {
    if (selectedEl.value?.type === "table") {
        const row = {};
        selectedEl.value.columns.forEach((c) => (row[c] = ""));
        selectedEl.value.data.push(row);
        markDirty();
    }
}
function addTableColumn() {
    if (selectedEl.value?.type === "table") {
        const col = "Col " + (selectedEl.value.columns.length + 1);
        selectedEl.value.columns.push(col);
        selectedEl.value.data.forEach((r) => (r[col] = ""));
        markDirty();
    }
}
function removeTableRow() {
    if (
        selectedEl.value?.type === "table" &&
        selectedEl.value.data.length > 1
    ) {
        selectedEl.value.data.pop();
        markDirty();
    }
}
function removeTableColumn() {
    if (
        selectedEl.value?.type === "table" &&
        selectedEl.value.columns.length > 1
    ) {
        const col = selectedEl.value.columns.pop();
        selectedEl.value.data.forEach((r) => delete r[col]);
        markDirty();
    }
}
function setChartLabels(labels) {
    if (selectedEl.value?.chartData) {
        selectedEl.value.chartData.labels = labels;
        markDirty();
    }
}
function setChartValues(values) {
    if (selectedEl.value?.chartData) {
        selectedEl.value.chartData.values = values;
        markDirty();
    }
}
function addTimelineItem() {
    if (selectedEl.value?.type === "timeline") {
        if (!selectedEl.value.items) selectedEl.value.items = [];
        selectedEl.value.items.push({ date: "", label: "", desc: "" });
        markDirty();
    }
}
function removeTimelineItem(idx) {
    if (selectedEl.value?.type === "timeline") {
        selectedEl.value.items.splice(idx, 1);
        markDirty();
    }
}
function addChecklistItem() {
    if (selectedEl.value?.type === "checklist") {
        if (!selectedEl.value.items) selectedEl.value.items = [];
        selectedEl.value.items.push({ text: "New item", checked: false });
        markDirty();
    }
}
function removeChecklistItem(idx) {
    if (selectedEl.value?.type === "checklist") {
        selectedEl.value.items.splice(idx, 1);
        markDirty();
    }
}
function addStatItem() {
    if (selectedEl.value?.type === "stat-row") {
        if (!selectedEl.value.stats) selectedEl.value.stats = [];
        selectedEl.value.stats.push({ value: "0", label: "Metric" });
        markDirty();
    }
}
function removeStatItem(idx) {
    if (selectedEl.value?.type === "stat-row") {
        selectedEl.value.stats.splice(idx, 1);
        markDirty();
    }
}

// ═══════════════════════════════════════════════════════════════════
// PAGES - Template Inheritance
// ═══════════════════════════════════════════════════════════════════
function goToPage(idx) {
    selectedElIdx.value = null;
    selectedEls.value = [];
    editingElIdx.value = null;
    currentPage.value = Math.max(0, Math.min(idx, report.content.length - 1));
}
function addPage() {
    pushUndo();
    report.content.push({
        id: uid(),
        label: "Page " + (report.content.length + 1),
        elements: [],
    });
    goToPage(report.content.length - 1);
    markDirty();
}
function duplicatePage(idx) {
    pushUndo();
    const copy = JSON.parse(JSON.stringify(report.content[idx]));
    copy.id = uid();
    copy.label = (copy.label || "Page " + (idx + 1)) + " (Copy)";
    copy.elements = copy.elements.map((el) => ({ ...el, id: uid() }));
    report.content.splice(idx + 1, 0, copy);
    goToPage(idx + 1);
    markDirty();
}
function deletePage(idx) {
    if (report.content.length <= 1) {
        showToast("Cannot delete only page", "error");
        return;
    }
    pushUndo();
    report.content.splice(idx, 1);
    if (currentPage.value >= report.content.length)
        currentPage.value = report.content.length - 1;
    markDirty();
}
function renamePage(idx, label) {
    report.content[idx].label = label;
    markDirty();
}

// ═══════════════════════════════════════════════════════════════════
// DRAG / RESIZE / ROTATE
// ═══════════════════════════════════════════════════════════════════
function onElDragStart(e, def) {
    e.dataTransfer.setData("el-def", JSON.stringify(def));
    isDraggingEl.value = true;
}
function onCanvasDrop({ def, x, y }) {
    addElement(def, x, y);
    isDraggingEl.value = false;
    dropTargetPage.value = null;
}
function onElementMouseDown({ event, pageIndex, elementIndex }) {
    if (event.button !== 0) return;
    const el = report.content[pageIndex].elements[elementIndex];
    if (!el || el.locked) return;
    if (event.shiftKey) {
        if (selectedEls.value.includes(elementIndex))
            selectedEls.value = selectedEls.value.filter(
                (i) => i !== elementIndex,
            );
        else selectedEls.value = [...selectedEls.value, elementIndex];
    } else {
        selectElementByIdx(elementIndex);
    }
    const scale = zoom.value / 100,
        startX = event.clientX,
        startY = event.clientY;
    const els = (
        selectedEls.value.length > 1 ? selectedEls.value : [elementIndex]
    ).map((i) => ({
        idx: i,
        ox: report.content[pageIndex].elements[i].position.x,
        oy: report.content[pageIndex].elements[i].position.y,
    }));
    let moved = false;
    const onMove = (ev) => {
        const dx = (ev.clientX - startX) / scale,
            dy = (ev.clientY - startY) / scale;
        if (!moved && Math.abs(dx) + Math.abs(dy) > 2) {
            pushUndo();
            moved = true;
        }
        els.forEach(({ idx: i, ox, oy }) => {
            if (!report.content[pageIndex].elements[i]) return;
            let nx = ox + dx,
                ny = oy + dy;
            if (snapToGrid.value) {
                nx = Math.round(nx / 10) * 10;
                ny = Math.round(ny / 10) * 10;
            }
            report.content[pageIndex].elements[i].position.x = Math.max(0, nx);
            report.content[pageIndex].elements[i].position.y = Math.max(0, ny);
        });
    };
    const onUp = () => {
        document.removeEventListener("mousemove", onMove);
        document.removeEventListener("mouseup", onUp);
        if (moved) markDirty();
    };
    document.addEventListener("mousemove", onMove);
    document.addEventListener("mouseup", onUp);
}
function startResize({ event, pageIndex, elementIndex, handle }) {
    event.stopPropagation();
    event.preventDefault();
    const el = report.content[pageIndex].elements[elementIndex];
    if (!el) return;
    const scale = zoom.value / 100,
        startX = event.clientX,
        startY = event.clientY,
        ow = el.styles.width,
        oh = el.styles.height,
        ox = el.position.x,
        oy = el.position.y;
    pushUndo();
    const onMove = (ev) => {
        const dx = (ev.clientX - startX) / scale,
            dy = (ev.clientY - startY) / scale,
            MIN = 20;
        if (handle.includes("e")) el.styles.width = Math.max(MIN, ow + dx);
        if (handle.includes("s")) el.styles.height = Math.max(MIN, oh + dy);
        if (handle.includes("w")) {
            el.styles.width = Math.max(MIN, ow - dx);
            el.position.x = ox + (ow - el.styles.width);
        }
        if (handle.includes("n")) {
            el.styles.height = Math.max(MIN, oh - dy);
            el.position.y = oy + (oh - el.styles.height);
        }
    };
    const onUp = () => {
        document.removeEventListener("mousemove", onMove);
        document.removeEventListener("mouseup", onUp);
        markDirty();
    };
    document.addEventListener("mousemove", onMove);
    document.addEventListener("mouseup", onUp);
}
function startRotate({ event, pageIndex, elementIndex }) {
    event.stopPropagation();
    event.preventDefault();
    const el = report.content[pageIndex].elements[elementIndex];
    if (!el) return;
    const rect = event.target.closest(".report-page")?.getBoundingClientRect();
    if (!rect) return;
    const scale = zoom.value / 100,
        cx = rect.left + (el.position.x + el.styles.width / 2) * scale,
        cy = rect.top + (el.position.y + el.styles.height / 2) * scale;
    pushUndo();
    const onMove = (ev) => {
        const angle =
            (Math.atan2(ev.clientY - cy, ev.clientX - cx) * 180) / Math.PI + 90;
        el.styles.rotate = Math.round(angle);
    };
    const onUp = () => {
        document.removeEventListener("mousemove", onMove);
        document.removeEventListener("mouseup", onUp);
        markDirty();
    };
    document.addEventListener("mousemove", onMove);
    document.addEventListener("mouseup", onUp);
}

// ═══════════════════════════════════════════════════════════════════
// RUBBER BAND
// ═══════════════════════════════════════════════════════════════════
function startRubberBand(e) {
    if (
        e.target.closest(".canvas-element") ||
        e.target.closest(".page-navigation") ||
        e.target.closest(".add-page-btn")
    )
        return;
    rubberBand.active = true;
    rubberBand.startX = e.clientX;
    rubberBand.startY = e.clientY;
}
function handleRubberBandMove(e) {
    if (!rubberBand.active) return;
    rubberBand.x = Math.min(e.clientX, rubberBand.startX);
    rubberBand.y = Math.min(e.clientY, rubberBand.startY);
    rubberBand.w = Math.abs(e.clientX - rubberBand.startX);
    rubberBand.h = Math.abs(e.clientY - rubberBand.startY);
}
function endRubberBand() {
    if (!rubberBand.active) return;
    rubberBand.active = false;
    if (rubberBand.w > 5 && rubberBand.h > 5) {
        const pageEl = document.querySelector(".report-page");
        if (!pageEl) return;
        const rect = pageEl.getBoundingClientRect();
        const scale = zoom.value / 100;
        const bx = (rubberBand.x - rect.left) / scale,
            by = (rubberBand.y - rect.top) / scale,
            bw = rubberBand.w / scale,
            bh = rubberBand.h / scale;
        const sel = [];
        currentPageElements.value.forEach((el, i) => {
            if (
                el.position.x < bx + bw &&
                el.position.x + el.styles.width > bx &&
                el.position.y < by + bh &&
                el.position.y + el.styles.height > by
            )
                sel.push(i);
        });
        if (sel.length) {
            selectedEls.value = sel;
            selectedElIdx.value = sel[0];
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// ZOOM
// ═══════════════════════════════════════════════════════════════════
function zoomIn() {
    zoom.value = Math.min(zoom.value + 10, 400);
}
function zoomOut() {
    zoom.value = Math.max(zoom.value - 10, 25);
}
function handleZoomWheel(e) {
    if (e.deltaY < 0) zoomIn();
    else zoomOut();
}

// ═══════════════════════════════════════════════════════════════════
// CONTEXT MENU
// ═══════════════════════════════════════════════════════════════════
function showElContextMenu(e, pi, ei) {
    if (ei !== null && ei !== undefined) selectElementByIdx(ei);
    contextMenu.show = true;
    contextMenu.x = e.clientX;
    contextMenu.y = e.clientY;
    contextMenu.items = [
        {
            label: "Edit",
            icon: "fa-solid fa-pen-to-square",
            action: () => startEditing({ pageIndex: pi, elementIndex: ei }),
        },
        {
            label: "Duplicate",
            icon: "fa-solid fa-clone",
            shortcut: "Ctrl+D",
            action: duplicateSelected,
        },
        {
            label: "Copy",
            icon: "fa-solid fa-copy",
            shortcut: "Ctrl+C",
            action: copyElement,
        },
        {
            label: "Paste",
            icon: "fa-solid fa-paste",
            shortcut: "Ctrl+V",
            action: pasteElement,
            disabled: !clipboard.value,
        },
        "---",
        {
            label: "Copy Style",
            icon: "fa-solid fa-paintbrush",
            action: stylePainterCopy,
        },
        {
            label: "Paste Style",
            icon: "fa-solid fa-brush",
            action: stylePainterPaste,
            disabled: !stylePainterClipboard.value,
        },
        "---",
        {
            label: "Bring to Front",
            icon: "fa-solid fa-angles-up",
            action: bringToFront,
        },
        {
            label: "Send to Back",
            icon: "fa-solid fa-angles-down",
            action: sendToBack,
        },
        "---",
        {
            label:
                ei !== null && currentPageElements.value[ei]?.locked
                    ? "Unlock"
                    : "Lock",
            icon: "fa-solid fa-lock",
            action: lockElement,
        },
        {
            label: "Delete",
            icon: "fa-solid fa-trash",
            shortcut: "Del",
            danger: true,
            action: deleteSelected,
        },
    ];
}

// ═══════════════════════════════════════════════════════════════════
// ALIGN
// ═══════════════════════════════════════════════════════════════════
function alignToPage(dir) {
    if (!selectedEl.value) return;
    const dims = getPageDims(),
        el = selectedEl.value,
        m = settings.margin || 0;
    if (dir === "left") el.position.x = m;
    else if (dir === "right") el.position.x = dims.w - el.styles.width - m;
    else if (dir === "center-h") el.position.x = (dims.w - el.styles.width) / 2;
    else if (dir === "top") el.position.y = m;
    else if (dir === "bottom") el.position.y = dims.h - el.styles.height - m;
    else if (dir === "center-v")
        el.position.y = (dims.h - el.styles.height) / 2;
    markDirty();
}

// ═══════════════════════════════════════════════════════════════════
// IMAGE
// ═══════════════════════════════════════════════════════════════════
function triggerImageUpload(pi, ei) {
    currentImageTarget.value = { pi, ei };
    fileInput.value?.click();
}
function triggerImageReplace(el) {
    currentImageTarget.value = { el };
    fileInput.value?.click();
}
function handleImageUpload(files) {
    Array.from(files).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (ev) => {
            if (currentImageTarget.value?.el) {
                currentImageTarget.value.el.src = ev.target.result;
                currentImageTarget.value = null;
                markDirty();
                return;
            }
            const dims = getPageDims();
            addElement(
                { type: "image", w: 300, h: 200, src: ev.target.result },
                dims.w / 2 - 150,
                dims.h / 3,
            );
        };
        reader.readAsDataURL(file);
    });
}
function handleFilePick(e) {
    handleImageUpload(e.target.files);
    e.target.value = "";
}

// ═══════════════════════════════════════════════════════════════════
// PAGE DBLCLICK
// ═══════════════════════════════════════════════════════════════════
function onPageDblClick({ event }) {
    const rect = event.target.closest(".report-page")?.getBoundingClientRect();
    if (!rect) return;
    const scale = zoom.value / 100;
    addElement(
        { type: "text", w: 200, h: 40 },
        (event.clientX - rect.left) / scale,
        (event.clientY - rect.top) / scale,
    );
}

// ═══════════════════════════════════════════════════════════════════
// STATUS CYCLE
// ═══════════════════════════════════════════════════════════════════
async function cycleStatus() {
    const statuses = ["draft", "published", "archived"];
    const ns =
        statuses[(statuses.indexOf(report.status) + 1) % statuses.length];
    try {
        const res = await fetch(route("reports.status", report.slug), {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrf(),
                Accept: "application/json",
            },
            body: JSON.stringify({ status: ns }),
        });
        if (res.ok) {
            report.status = ns;
            showToast("Report " + ns, "success");
            if (ns === "published") showConfetti.value = true;
        }
    } catch (e) {
        showToast("Failed", "error");
    }
}

// ═══════════════════════════════════════════════════════════════════
// EXPORT / SHARE / PRINT / EMAIL
// ═══════════════════════════════════════════════════════════════════
function exportFile(type) {
    const urls = {
        pdf: route("reports.download", report.slug),
        image: route("reports.export.image", report.slug),
        excel: route("reports.export.excel", report.slug),
        csv: route("reports.export.csv", report.slug),
    };
    window.open(urls[type], "_blank");
}
function previewReport() {
    window.open(route("reports.preview", report.slug), "_blank");
}
async function shareReport() {
    try {
        const res = await fetch(route("reports.share", report.slug), {
            method: "POST",
            headers: { "X-CSRF-TOKEN": getCsrf(), Accept: "application/json" },
        });
        const data = await res.json();
        if (data.url) {
            await navigator.clipboard.writeText(data.url);
            showToast("Link copied!", "success");
        }
    } catch (e) {
        showToast("Failed", "error");
    }
}
function printPreview() {
    const w = window.open("", "_blank", "width=1000,height=800");
    w.document.write(
        "<html><head><title>Print - " +
            report.title +
            "</title><style>body{font-family:Inter;background:#e2e8f0;display:flex;flex-wrap:wrap;justify-content:center;gap:20px;padding:40px}.page{background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.15)}@media print{body{background:#fff;padding:0}.page{box-shadow:none;page-break-after:always}}</style></head><body>",
    );
    report.content.forEach((page, pi) => {
        const dims = getPageDims();
        w.document.write(
            '<div class="page" style="width:' +
                dims.w +
                "px;min-height:" +
                dims.h +
                "px;padding:" +
                (settings.margin || 40) +
                'px">',
        );
        page.elements.forEach((el) => {
            w.document.write(
                '<div style="position:absolute;left:' +
                    (el.position?.x || 0) +
                    "px;top:" +
                    (el.position?.y || 0) +
                    "px;width:" +
                    (el.styles?.width || 100) +
                    "px;font-size:" +
                    (el.styles?.fontSize || 14) +
                    'px">' +
                    (el.content || "") +
                    "</div>",
            );
        });
        w.document.write("</div>");
    });
    w.document.write("</body></html>");
    w.document.close();
    setTimeout(() => w.print(), 500);
}
async function emailReport() {
    const email = prompt("Enter email address to send this report:");
    if (!email) return;
    try {
        const res = await fetch(route("reports.email", report.slug), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrf(),
                Accept: "application/json",
            },
            body: JSON.stringify({ email }),
        });
        if (res.ok) showToast("Report emailed!", "success");
        else showToast("Failed to send", "error");
    } catch (e) {
        showToast("Failed to send", "error");
    }
}

// ═══════════════════════════════════════════════════════════════════
// TEMPLATE
// ═══════════════════════════════════════════════════════════════════
function applyQuickTemplate(tpl) {
    const m = tpl.gradient?.match(/#[a-f0-9]{6}/gi);
    if (m && m.length >= 2) {
        settings.primary_color = m[0];
        if (m.length >= 2) settings.background_color = m[1];
        markDirty();
        showToast('"' + tpl.name + '" applied', "success");
    }
}

// ═══════════════════════════════════════════════════════════════════
// AI
// ═══════════════════════════════════════════════════════════════════
function insertAiContent({ type, content }) {
    const dims = getPageDims();
    const el = createElement(
        { type: type || "text", w: 350, h: 120 },
        dims.w / 2 - 175,
        dims.h / 3,
    );
    el.content = content;
    currentPageElements.value.push(el);
    selectedElIdx.value = currentPageElements.value.length - 1;
    markDirty();
}
function insertAiChart(data) {
    const dims = getPageDims();
    const el = createElement(
        { type: data.suggested_chart_type || "bar-chart", w: 400, h: 280 },
        dims.w / 2 - 200,
        dims.h / 3,
    );
    el.chartData = { labels: data.labels, values: data.values };
    el.chartTitle = data.title;
    currentPageElements.value.push(el);
    selectedElIdx.value = currentPageElements.value.length - 1;
    markDirty();
}

// ═══════════════════════════════════════════════════════════════════
// FIND & REPLACE
// ═══════════════════════════════════════════════════════════════════
function findInReport() {
    findMatches.value = [];
    if (!findText.value) return;
    const q = findText.value.toLowerCase();
    report.content.forEach((p, pi) =>
        p.elements.forEach((el, ei) => {
            if (el.content && typeof el.content === "string") {
                const txt = el.content.replace(/<[^>]*>/g, "");
                if (txt.toLowerCase().includes(q)) {
                    const idx = txt.toLowerCase().indexOf(q),
                        start = Math.max(0, idx - 30),
                        end = Math.min(txt.length, idx + q.length + 30);
                    findMatches.value.push({
                        pi,
                        ei,
                        preview:
                            (start > 0 ? "…" : "") +
                            txt.substring(start, end) +
                            (end < txt.length ? "…" : ""),
                        full: txt,
                    });
                }
            }
        }),
    );
}
function replaceAll() {
    if (!findText.value) return;
    const q = findText.value,
        r = replaceText.value;
    let count = 0;
    report.content.forEach((p) =>
        p.elements.forEach((el) => {
            if (el.content && typeof el.content === "string") {
                const re = new RegExp(
                    q.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"),
                    "gi",
                );
                const old = el.content;
                el.content = el.content.replace(re, r);
                if (el.content !== old) count++;
            }
        }),
    );
    if (count > 0) {
        markDirty();
        showToast("Replaced " + count + " occurrences", "success");
        findInReport();
    }
}
function replaceOne() {
    if (!findText.value || !findMatches.value.length) return;
    const m = findMatches.value[0];
    const el = report.content[m.pi].elements[m.ei];
    el.content = el.content.replace(
        new RegExp(findText.value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "i"),
        replaceText.value,
    );
    markDirty();
    findInReport();
}
function goToMatch(m) {
    goToPage(m.pi);
    selectElementByIdx(m.ei);
}

// ═══════════════════════════════════════════════════════════════════
// TOC
// ═══════════════════════════════════════════════════════════════════
function refreshTOC() {
    if (!selectedEl.value || selectedEl.value.type !== "toc") return;
    selectedEl.value.tocItems = [];
    report.content.forEach((page, pi) => {
        page.elements.forEach((e) => {
            if (["heading", "subheading"].includes(e.type)) {
                const text = (e.content || "").replace(/<[^>]*>/g, "").trim();
                if (text)
                    selectedEl.value.tocItems.push({
                        text,
                        page: pi + 1,
                        level: e.type === "heading" ? 1 : 2,
                    });
            }
        });
    });
    markDirty();
}

// ═══════════════════════════════════════════════════════════════════
// PRESENTATION
// ═══════════════════════════════════════════════════════════════════
function startPresentation() {
    presentationMode.value = true;
    presentationPage.value = currentPage.value;
}
function nextSlide() {
    if (presentationPage.value < report.content.length - 1)
        presentationPage.value++;
    else presentationMode.value = false;
}
function prevSlide() {
    if (presentationPage.value > 0) presentationPage.value--;
}
function getPresPageStyle() {
    const dims = getPageDims();
    return {
        width: dims.w + "px",
        height: dims.h + "px",
        background: "#fff",
        position: "relative",
        overflow: "hidden",
        borderRadius: "4px",
        padding: (settings.margin || 40) + "px",
        fontFamily: settings.font_family || "Inter",
    };
}
function getPresElStyle(el) {
    return {
        position: "absolute",
        left: (el.position?.x || 0) + "px",
        top: (el.position?.y || 0) + "px",
        width: (el.styles?.width || 100) + "px",
        fontSize: (el.styles?.fontSize || 14) + "px",
        color: el.styles?.color || "#000",
        textAlign: el.styles?.textAlign || "left",
        fontWeight: el.styles?.fontWeight || "400",
        overflow: "hidden",
    };
}

// ═══════════════════════════════════════════════════════════════════
// COMMAND PALETTE
// ═══════════════════════════════════════════════════════════════════
function executeCommand(cmd) {
    const actions = {
        save: saveNow,
        undo,
        redo,
        delete: deleteSelected,
        duplicate: duplicateSelected,
        copy: copyElement,
        paste: pasteElement,
        "select-all": () => {
            selectedEls.value = currentPageElements.value.map((_, i) => i);
            selectedElIdx.value = 0;
        },
        deselect: deselectAll,
        "add-page": addPage,
        "toggle-grid": () => (showGrid.value = !showGrid.value),
        "toggle-dark": toggleDark,
        "toggle-fullscreen": toggleFullscreen,
        "zoom-fit": () => (zoom.value = 100),
        "zoom-in": zoomIn,
        "zoom-out": zoomOut,
        preview: previewReport,
        print: printPreview,
        presentation: startPresentation,
        "find-replace": () => (showFindReplace.value = !showFindReplace.value),
        share: shareReport,
        email: emailReport,
    };
    if (actions[cmd]) actions[cmd]();
}

// ═══════════════════════════════════════════════════════════════════
// THEME
// ═══════════════════════════════════════════════════════════════════
function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark", isDark.value);
    localStorage.setItem("theme", isDark.value ? "dark" : "light");
}
function toggleFullscreen() {
    if (!isFullscreen.value) {
        editorShell.value?.requestFullscreen?.();
        isFullscreen.value = true;
    } else {
        document.exitFullscreen?.();
        isFullscreen.value = false;
    }
}
function completeOnboarding() {
    showOnboarding.value = false;
    localStorage.setItem("rg_onboarded", "1");
}

// ═══════════════════════════════════════════════════════════════════
// KEYBOARD - ALL SHORTCUTS (Prevent Browser Defaults)
// ═══════════════════════════════════════════════════════════════════
function handleKeyboard(e) {
    const ctrl = e.ctrlKey || e.metaKey;
    const editable =
        e.target.isContentEditable ||
        ["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName);

    // Block ALL browser shortcuts we handle
    if (
        ctrl &&
        [
            "k",
            "/",
            "s",
            "z",
            "y",
            "c",
            "v",
            "d",
            "a",
            "f",
            "p",
            "b",
            "i",
            "u",
            "m",
            "g",
            "n",
        ].includes(e.key) &&
        !editable
    ) {
        e.preventDefault();
        e.stopPropagation();
    }

    if (ctrl && e.key === "k") {
        showCommandPalette.value = !showCommandPalette.value;
        return;
    }
    if (ctrl && e.key === "/" && !editable) {
        showShortcuts.value = !showShortcuts.value;
        return;
    }
    if (ctrl && e.key === "s") {
        saveNow();
        return;
    }
    if (ctrl && e.key === "z") {
        undo();
        return;
    }
    if (ctrl && e.key === "y") {
        redo();
        return;
    }
    if (ctrl && e.key === "c" && !editable) {
        copyElement();
        return;
    }
    if (ctrl && e.key === "v" && !editable) {
        pasteElement();
        return;
    }
    if (ctrl && e.key === "d" && !editable) {
        duplicateSelected();
        return;
    }
    if (ctrl && e.key === "a" && !editable) {
        selectedEls.value = currentPageElements.value.map((_, i) => i);
        selectedElIdx.value = 0;
        return;
    }
    if (ctrl && e.key === "f" && !editable) {
        showFindReplace.value = !showFindReplace.value;
        return;
    }
    if (ctrl && e.key === "F5") {
        startPresentation();
        return;
    }
    if (ctrl && e.key === "m") {
        measureMode.value = !measureMode.value;
        return;
    }
    if (ctrl && e.key === "p" && !editable) {
        previewReport();
        return;
    }
    if (ctrl && e.key === "b" && !editable) {
        toggleFmt("fontWeight", "700", "400");
        return;
    }
    if (ctrl && e.key === "i" && !editable) {
        toggleFmt("fontStyle", "italic", "normal");
        return;
    }
    if (ctrl && e.key === "u" && !editable) {
        toggleFmt("textDecoration", "underline", "none");
        return;
    }
    if (ctrl && e.key === "g" && !editable) {
        showGrid.value = !showGrid.value;
        return;
    }
    if (ctrl && e.key === "n" && !editable) {
        addPage();
        return;
    }

    if ((e.key === "Delete" || e.key === "Backspace") && !editable) {
        deleteSelected();
        return;
    }
    if (e.key === "Escape") {
        deselectAll();
        showCommandPalette.value = false;
        showShortcuts.value = false;
        contextMenu.show = false;
        presentationMode.value = false;
        showFindReplace.value = false;
        return;
    }
    if (e.key === "F11") {
        toggleFullscreen();
        return;
    }

    // Presentation navigation
    if (presentationMode.value) {
        if (e.key === "ArrowRight" || e.key === "ArrowDown" || e.key === " ") {
            e.preventDefault();
            nextSlide();
            return;
        }
        if (e.key === "ArrowLeft" || e.key === "ArrowUp") {
            e.preventDefault();
            prevSlide();
            return;
        }
    }

    // Nudge arrows
    if (!editable && selectedEl.value) {
        const STEP = e.shiftKey ? 10 : 1;
        if (e.key === "ArrowLeft") {
            e.preventDefault();
            selectedEl.value.position.x = Math.max(
                0,
                selectedEl.value.position.x - STEP,
            );
            markDirty();
        } else if (e.key === "ArrowRight") {
            e.preventDefault();
            selectedEl.value.position.x += STEP;
            markDirty();
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            selectedEl.value.position.y = Math.max(
                0,
                selectedEl.value.position.y - STEP,
            );
            markDirty();
        } else if (e.key === "ArrowDown") {
            e.preventDefault();
            selectedEl.value.position.y += STEP;
            markDirty();
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════════════════════
onMounted(() => {
    document.documentElement.classList.toggle("dark", isDark.value);
    pushUndo();
    // Auto-recovery
    const draft = localStorage.getItem("rg_draft_" + report.slug);
    if (draft) {
        try {
            const data = JSON.parse(draft);
            const savedAt = new Date(data.savedAt);
            const serverUpdated = new Date(report.updated_at);
            if (
                savedAt > serverUpdated &&
                confirm(
                    "Recover unsaved changes from " +
                        savedAt.toLocaleTimeString() +
                        "?",
                )
            ) {
                report.content = data.content;
                Object.assign(settings, data.settings);
                isDirty.value = true;
                showToast("Draft recovered!", "warning");
            }
            localStorage.removeItem("rg_draft_" + report.slug);
        } catch (e) {}
    }
    autoSaveInterval = setInterval(() => {
        if (isDirty.value) saveNow();
    }, 30000);
    document.addEventListener("fullscreenchange", () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
});
onBeforeUnmount(() => {
    clearTimeout(saveTimer);
    clearInterval(autoSaveInterval);
});
</script>

<style>
.editor-shell {
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --bg-panel: #ffffff;
    --border: #e2e8f0;
    --border-light: #f1f5f9;
    --border-hover: #cbd5e1;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
    --accent: #6366f1;
    --accent-hover: #4f46e5;
    --accent-light: rgba(99, 102, 241, 0.08);
    --accent-soft: rgba(99, 102, 241, 0.15);
    --danger: #ef4444;
    --danger-light: rgba(239, 68, 68, 0.08);
    --success: #10b981;
    --warning: #f59e0b;
    --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
    --shadow-xl: 0 12px 40px rgba(0, 0, 0, 0.16);
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    font-family:
        "Inter",
        "DM Sans",
        system-ui,
        -apple-system,
        sans-serif;
    background: var(--bg-tertiary);
    color: var(--text-primary);
    font-size: 13px;
    line-height: 1.5;
    outline: none;
    -webkit-font-smoothing: antialiased;
}
.editor-shell.dark {
    --bg-primary: #1a2236;
    --bg-secondary: #111827;
    --bg-tertiary: #0b1120;
    --bg-panel: #1a2236;
    --border: #263348;
    --border-light: #1e2a3d;
    --border-hover: #334155;
    --text-primary: #e2e8f0;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --accent: #818cf8;
    --accent-hover: #6366f1;
    --accent-light: rgba(129, 140, 248, 0.1);
    --accent-soft: rgba(129, 140, 248, 0.18);
    --danger: #f87171;
    --danger-light: rgba(248, 113, 113, 0.1);
    --success: #34d399;
    --warning: #fbbf24;
    --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.3);
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.4);
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.5);
    --shadow-xl: 0 12px 40px rgba(0, 0, 0, 0.6);
}
.editor-shell.fullscreen {
    position: fixed;
    inset: 0;
    z-index: 9999;
}
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
input,
select,
textarea,
button {
    font-family: inherit;
    font-size: inherit;
}
.hidden {
    display: none !important;
}
.editor-body {
    flex: 1;
    display: flex;
    overflow: hidden;
    min-height: 0;
}
::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 99px;
}
::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}
::selection {
    background: var(--accent);
    color: #fff;
}
*:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 2px;
}
.fr-panel {
    position: fixed;
    right: 20px;
    top: 80px;
    width: 350px;
    max-height: 500px;
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
    z-index: 500;
    display: flex;
    flex-direction: column;
}
.fr-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    font-weight: 700;
    font-size: 13px;
}
.fr-body {
    padding: 10px;
    overflow-y: auto;
}
.fr-row {
    margin-bottom: 6px;
}
.fr-input {
    width: 100%;
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 7px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 12px;
    outline: none;
}
.fr-input:focus {
    border-color: var(--accent);
}
.fr-count {
    font-size: 11px;
    color: var(--text-muted);
    margin-bottom: 6px;
}
.fr-actions {
    display: flex;
    gap: 6px;
}
.fr-btn {
    padding: 6px 12px;
    border: none;
    background: var(--accent);
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
}
.fr-btn.secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border);
}
.fr-results {
    max-height: 200px;
    overflow-y: auto;
    margin-top: 8px;
}
.fr-match {
    padding: 6px 8px;
    font-size: 11px;
    color: var(--text-secondary);
    cursor: pointer;
    border-radius: 4px;
}
.fr-match:hover {
    background: var(--bg-secondary);
}
.pres-overlay {
    position: fixed;
    inset: 0;
    background: #000;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.pres-page {
    background: #fff;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    position: relative;
    overflow: hidden;
    border-radius: 4px;
}
.pres-controls {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 10px 20px;
    border-radius: 99px;
    z-index: 10000;
}
.pres-controls button {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pres-controls button:hover {
    background: rgba(255, 255, 255, 0.3);
}
.pres-controls button:disabled {
    opacity: 0.3;
}
.pres-controls span {
    color: #fff;
    font-size: 13px;
    font-weight: 600;
}
@media (max-width: 768px) {
    .editor-shell {
        font-size: 12px;
    }
}
</style>
