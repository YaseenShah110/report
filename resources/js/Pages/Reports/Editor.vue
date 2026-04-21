<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3 h-full px-1" :class="dark ? 'text-slate-100' : 'text-slate-800'">
                <!-- Left: breadcrumb + title -->
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <button @click="router.get(route('reports.index'))"
                        class="flex items-center gap-1 text-xs font-semibold shrink-0 px-2 py-1.5 rounded-lg transition-all"
                        :class="dark ? 'text-slate-400 hover:text-slate-100 hover:bg-white/10' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-100'">
                        <i class="fa-solid fa-chevron-left w-3.5 h-3.5 text-xs"></i>
                        Reports
                    </button>
                    <span :class="dark ? 'text-slate-700' : 'text-slate-300'">/</span>
                    <input v-model="form.title"
                        class="text-sm font-bold bg-transparent border-0 border-b-2 border-transparent focus:border-indigo-500 focus:outline-none px-1 py-0.5 min-w-0 flex-1 transition-colors"
                        :class="dark ? 'text-slate-100 placeholder:text-slate-600' : 'text-slate-800 placeholder:text-slate-300'"
                        placeholder="Untitled Report…" />
                    <transition name="fade">
                        <span v-if="saveStatus === 'saving'"
                            class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-500 shrink-0">
                            <i class="fa-solid fa-spinner fa-spin text-[10px]"></i>Saving…
                        </span>
                        <span v-else-if="saveStatus === 'saved'"
                            class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-500 shrink-0">
                            <i class="fa-solid fa-check text-[10px]"></i>Saved
                        </span>
                    </transition>
                </div>

                <!-- Right: toolbar -->
                <div class="flex items-center gap-1 shrink-0">
                    <!-- Drag Mode -->
                    <button @click="dragMode = !dragMode" title="Drag Mode (D)"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg border transition-all"
                        :class="dragMode ? 'bg-violet-600 text-white border-violet-600 shadow-md shadow-violet-500/30' : dark ? 'bg-white/10 text-slate-300 border-white/10 hover:bg-white/15' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'">
                        <i class="fa-solid fa-hand text-xs"></i>
                        {{ dragMode ? "Drag ON" : "Drag" }}
                    </button>

                    <!-- Grid Toggle -->
                    <button @click="showGrid = !showGrid" :title="showGrid ? 'Hide Grid (G)' : 'Show Grid (G)'"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="showGrid ? 'bg-indigo-100 text-indigo-600' : dark ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'">
                        <i class="fa-solid fa-border-all text-xs"></i>
                    </button>

                    <!-- Rulers Toggle -->
                    <button @click="showRulers = !showRulers" :title="showRulers ? 'Hide Rulers (R)' : 'Show Rulers (R)'"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="showRulers ? 'bg-indigo-100 text-indigo-600' : dark ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'">
                        <i class="fa-solid fa-ruler text-xs"></i>
                    </button>

                    <div class="w-px h-5 mx-0.5" :class="dark ? 'bg-slate-700' : 'bg-slate-200'"></div>

                    <!-- Undo/Redo -->
                    <button @click="undo" :disabled="historyIndex <= 0" title="Undo (Ctrl+Z)"
                        class="p-1.5 rounded-lg transition-colors disabled:opacity-25"
                        :class="dark ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                    </button>
                    <button @click="redo" :disabled="historyIndex >= history.length - 1" title="Redo (Ctrl+Y)"
                        class="p-1.5 rounded-lg transition-colors disabled:opacity-25"
                        :class="dark ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'">
                        <i class="fa-solid fa-rotate-right text-xs"></i>
                    </button>

                    <div class="w-px h-5 mx-0.5" :class="dark ? 'bg-slate-700' : 'bg-slate-200'"></div>

                    <!-- Fullscreen -->
                    <button @click="toggleFullscreen" title="Fullscreen (F)"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="dark ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'">
                        <i class="fa-solid fa-expand text-xs"></i>
                    </button>

                    <div class="w-px h-5 mx-0.5" :class="dark ? 'bg-slate-700' : 'bg-slate-200'"></div>

                    <!-- Save -->
                    <button @click="saveReport"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
                        :class="dark ? 'bg-slate-600 hover:bg-slate-500 text-white' : 'bg-slate-800 hover:bg-slate-700 text-white'">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>Save
                    </button>

                    <!-- Preview -->
                    <button @click="previewReport"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all">
                        <i class="fa-solid fa-eye text-xs"></i>Preview
                    </button>

                    <!-- Export -->
                    <div class="relative" ref="exportMenuRef">
                        <button @click="showExportMenu = !showExportMenu"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition-all">
                            <i class="fa-solid fa-file-export text-xs"></i>Export
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>
                        <transition name="dropdown">
                            <div v-if="showExportMenu"
                                class="absolute right-0 top-full mt-1 w-48 rounded-xl shadow-xl border overflow-hidden z-50"
                                :class="dark ? 'bg-slate-800 border-slate-700' : 'bg-white border-slate-200'">
                                <button v-for="fmt in EXPORT_FORMATS" :key="fmt.key" @click="exportAs(fmt.key)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold transition-colors text-left"
                                    :class="dark ? 'text-slate-300 hover:bg-white/10' : 'text-slate-600 hover:bg-slate-50'">
                                    <i :class="fmt.icon + ' text-base w-5 text-center'"></i>
                                    <div>
                                        <div class="font-bold">{{ fmt.label }}</div>
                                        <div class="text-[10px] text-slate-400">{{ fmt.desc }}</div>
                                    </div>
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </template>

        <!-- MAIN LAYOUT -->
        <div class="flex overflow-hidden transition-colors duration-200"
            :style="{ height: 'calc(100vh - 56px)' }"
            :class="dark ? 'bg-slate-950' : 'bg-slate-100'">

            <!-- LEFT SIDEBAR -->
            <EditorLeftSidebar
                :dark="dark"
                :left-tab="leftTab"
                :pages="pages"
                :selected-page-index="selectedPageIndex"
                :element-search="elementSearch"
                :collapsed-categories="collapsedCategories"
                :rs="rs"
                :show-grid="showGrid"
                :snap-to-grid="snapToGrid"
                :show-rulers="showRulers"
                :grid-size="gridSize"
                :input-cls="inputCls"
                @update:left-tab="leftTab = $event"
                @update:element-search="elementSearch = $event"
                @add-element="addElement"
                @select-page="selectedPageIndex = $event"
                @add-page-after="addPageAfter"
                @remove-page="removePage"
                @duplicate-page="duplicatePage"
                @toggle-category="toggleCategory"
                @update:rs="Object.assign(rs, $event)"
                @update:show-grid="showGrid = $event"
                @update:snap-to-grid="snapToGrid = $event"
                @update:show-rulers="showRulers = $event"
                @update:grid-size="gridSize = $event"
            />

            <!-- CANVAS -->
            <EditorCanvas
                :dark="dark"
                :pages="pages"
                :selected-element="selectedElement"
                :selected-page-index="selectedPageIndex"
                :zoom="zoom"
                :drag-mode="dragMode"
                :show-grid="showGrid"
                :show-rulers="showRulers"
                :grid-size="gridSize"
                :rs="rs"
                :canvas-dimensions="canvasDimensions"
                :history-index="historyIndex"
                :history-length="history.length"
                @update:zoom="zoom = $event"
                @select-element="selectElement"
                @deselect-all="deselectAll"
                @add-element-at="addElementAtPosition"
                @delete-element="deleteElement"
                @duplicate-element="duplicateElement"
                @move-element-to-page="moveElementToPage"
                @lock-element="lockElement"
                @add-page-after="addPageAfter"
                @remove-page="removePage"
                @duplicate-page="duplicatePage"
                @push-history="pushHistory"
                @upload-image="uploadImage"
                @open-context-menu="openContextMenu"
                @start-drag="startElementDrag"
                @start-resize="startResize"
                @start-rotation="startRotation"
                @fit-to-screen="fitToScreen"
                @text-blur="onTextBlur"
                @list-item-blur="onListItemBlur"
                @add-list-item="addListItem"
                @checklist-item-blur="onChecklistItemBlur"
                @add-checklist-item="addChecklistItem"
                @table-cell-blur="onTableCellBlur"
                @table-header-blur="onTableHeaderBlur"
                @add-table-row="addTableRow"
                @add-table-column="addTableColumn"
                @remove-table-row="removeTableRow"
                @remove-table-column="removeTableColumn"
                @toc-title-blur="onTocTitleBlur"
                @toc-item-blur="onTocItemBlur"
                @add-toc-item="addTocItem"
                @timeline-blur="onTimelineBlur"
                @add-timeline-item="addTimelineItem"
                @bring-to-front="bringToFront"
                @send-to-back="sendToBack"
                @bring-forward="bringForward"
                @send-backward="sendBackward"
                @align-element="alignElement"
            />

            <!-- RIGHT PROPERTIES PANEL -->
            <EditorPropertiesPanel
                :dark="dark"
                :selected-element="selectedElement"
                :pages="pages"
                :selected-page-index="selectedPageIndex"
                :rs="rs"
                :input-cls="inputCls"
                :chart-labels-input="chartLabelsInput"
                :chart-values-input="chartValuesInput"
                :chart-dataset-label="chartDatasetLabel"
                :chart-color="chartColor"
                :pie-colors-input="pieColorsInput"
                @deselect-all="deselectAll"
                @push-history="pushHistory"
                @add-element="addElement"
                @delete-selected="deleteSelectedElement"
                @bring-to-front="bringToFront"
                @send-to-back="sendToBack"
                @bring-forward="bringForward"
                @send-backward="sendBackward"
                @align-element="alignElement"
                @move-element-to-page="moveElementToPage"
                @update:chart-labels-input="chartLabelsInput = $event"
                @update:chart-values-input="chartValuesInput = $event"
                @update:chart-dataset-label="chartDatasetLabel = $event"
                @update:chart-color="chartColor = $event"
                @update:pie-colors-input="pieColorsInput = $event"
                @update-chart-data="updateChartData"
                @refresh-chart="refreshChart"
                @upload-image="uploadImage"
                @apply-callout-style="applyCalloutStyle"
            />
        </div>

        <!-- Context Menu -->
        <EditorContextMenu
            :dark="dark"
            :context-menu="contextMenu"
            :pages="pages"
            :selected-element="selectedElement"
            :selected-page-index="selectedPageIndex"
            @close="contextMenu.show = false"
            @duplicate="duplicateElement"
            @delete="deleteElement"
            @lock="lockElement"
            @move-to-page="moveElementToPage"
            @bring-to-front="bringToFront"
            @send-to-back="sendToBack"
            @push-history="pushHistory"
        />

        <!-- Toast -->
        <transition name="toast">
            <div v-if="toast.show"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[999] flex items-center gap-2.5 px-4 py-2.5 rounded-xl shadow-xl text-sm font-bold"
                :class="toast.type === 'error' ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'">
                <i :class="toast.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
                {{ toast.message }}
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";
import Chart from "chart.js/auto";

// Sub-components
import EditorLeftSidebar from "@/Components/Editor/EditorLeftSidebar.vue";
import EditorCanvas from "@/Components/Editor/EditorCanvas.vue";
import EditorPropertiesPanel from "@/Components/Editor/EditorPropertiesPanel.vue";
import EditorContextMenu from "@/Components/Editor/EditorContextMenu.vue";

// ─── Shared constants (exported for sub-components) ──────────────────────────
const EXPORT_FORMATS = [
    { key: "pdf",  label: "PDF Document", desc: "Print-ready PDF",   icon: "fa-solid fa-file-pdf" },
    { key: "xlsx", label: "Excel Sheet",  desc: "Data in .xlsx",     icon: "fa-solid fa-file-excel" },
    { key: "csv",  label: "CSV File",     desc: "Raw data export",   icon: "fa-solid fa-file-csv" },
    { key: "png",  label: "PNG Image",    desc: "High-res image",    icon: "fa-solid fa-file-image" },
];

const PAGE_SIZES = [
    { value: "A4",     label: "A4 (210 × 297 mm)" },
    { value: "Letter", label: "US Letter (8.5 × 11 in)" },
    { value: "Legal",  label: "Legal (8.5 × 14 in)" },
    { value: "A3",     label: "A3 (297 × 420 mm)" },
    { value: "A5",     label: "A5 (148 × 210 mm)" },
];

const FONTS = [
    { name: "DM Sans",           value: "'DM Sans', sans-serif" },
    { name: "Inter",             value: "'Inter', sans-serif" },
    { name: "Outfit",            value: "'Outfit', sans-serif" },
    { name: "Plus Jakarta Sans", value: "'Plus Jakarta Sans', sans-serif" },
    { name: "Georgia",           value: "Georgia, serif" },
    { name: "Playfair Display",  value: "'Playfair Display', serif" },
    { name: "Merriweather",      value: "'Merriweather', serif" },
    { name: "Lato",              value: "'Lato', sans-serif" },
    { name: "Nunito",            value: "'Nunito', sans-serif" },
    { name: "Courier New",       value: "'Courier New', monospace" },
    { name: "Trebuchet MS",      value: "'Trebuchet MS', sans-serif" },
    { name: "Times New Roman",   value: "'Times New Roman', serif" },
];

const FONT_WEIGHTS = [
    { label: "Thin",       value: "100" },
    { label: "Light",      value: "300" },
    { label: "Regular",    value: "400" },
    { label: "Medium",     value: "500" },
    { label: "Semi Bold",  value: "600" },
    { label: "Bold",       value: "700" },
    { label: "Extra Bold", value: "800" },
    { label: "Black",      value: "900" },
];

const SHADOW_MAP = {
    none:         "none",
    sm:           "0 1px 3px rgba(0,0,0,.12)",
    md:           "0 4px 12px rgba(0,0,0,.1)",
    lg:           "0 10px 30px rgba(0,0,0,.15)",
    xl:           "0 20px 60px rgba(0,0,0,.2)",
    "2xl":        "0 25px 80px rgba(0,0,0,.25)",
    inner:        "inset 0 2px 4px rgba(0,0,0,.1)",
    "glow-indigo":"0 0 20px rgba(99,102,241,.5)",
    "glow-emerald":"0 0 20px rgba(16,185,129,.5)",
    "glow-gold":  "0 0 20px rgba(245,158,11,.5)",
};

const CALLOUT_PRESETS = {
    info:    { bg: "#eff6ff", border: "#bfdbfe", color: "#1e40af", emoji: "ℹ️" },
    warning: { bg: "#fffbeb", border: "#fde68a", color: "#92400e", emoji: "⚠️" },
    success: { bg: "#ecfdf5", border: "#a7f3d0", color: "#065f46", emoji: "✅" },
    danger:  { bg: "#fef2f2", border: "#fecaca", color: "#991b1b", emoji: "🚨" },
    neutral: { bg: "#f8fafc", border: "#e2e8f0", color: "#334155", emoji: "📝" },
    purple:  { bg: "#faf5ff", border: "#e9d5ff", color: "#6b21a8", emoji: "💡" },
};

const CODE_LANGS = [
    "JavaScript","TypeScript","Python","PHP","HTML","CSS","SQL","JSON",
    "Bash","Java","C#","C++","Go","Rust","Ruby","YAML","Markdown","Vue",
];

const SHORTCUTS = [
    { key: "Ctrl+Z",    desc: "Undo" },
    { key: "Ctrl+Y",    desc: "Redo" },
    { key: "Ctrl+S",    desc: "Save" },
    { key: "Ctrl+D",    desc: "Duplicate" },
    { key: "Del",       desc: "Delete element" },
    { key: "D",         desc: "Toggle Drag" },
    { key: "G",         desc: "Toggle Grid" },
    { key: "R",         desc: "Toggle Rulers" },
    { key: "F",         desc: "Fullscreen" },
    { key: "Esc",       desc: "Deselect" },
    { key: "←↑→↓",     desc: "Nudge 1px" },
    { key: "Shift+←",  desc: "Nudge 10px" },
];

const QUICK_ADD = [
    { type: "heading",   label: "Heading",   icon: "fa-solid fa-heading" },
    { type: "text",      label: "Paragraph", icon: "fa-solid fa-paragraph" },
    { type: "metric",    label: "KPI Card",  icon: "fa-solid fa-chart-line" },
    { type: "bar-chart", label: "Bar Chart", icon: "fa-solid fa-chart-bar" },
    { type: "table",     label: "Table",     icon: "fa-solid fa-table" },
    { type: "image",     label: "Image",     icon: "fa-solid fa-image" },
    { type: "checklist", label: "Checklist", icon: "fa-solid fa-list-check" },
    { type: "progress",  label: "Progress",  icon: "fa-solid fa-bars-progress" },
];

const ELEMENT_CATEGORIES = [
    {
        name: "Text & Content", icon: "fa-solid fa-font",
        items: [
            { type: "heading",    name: "Heading H1",         icon: "fa-solid fa-heading" },
            { type: "subheading", name: "Heading H2",         icon: "fa-solid fa-h" },
            { type: "text",       name: "Paragraph",          icon: "fa-solid fa-paragraph" },
            { type: "quote",      name: "Quote",              icon: "fa-solid fa-quote-left" },
            { type: "blockquote", name: "Block Quote",        icon: "fa-solid fa-quote-right" },
            { type: "highlight",  name: "Highlight",          icon: "fa-solid fa-highlighter" },
            { type: "list",       name: "List",               icon: "fa-solid fa-list" },
            { type: "checklist",  name: "Checklist",          icon: "fa-solid fa-list-check" },
            { type: "link",       name: "Hyperlink",          icon: "fa-solid fa-link" },
            { type: "badge",      name: "Badge",              icon: "fa-solid fa-tag" },
            { type: "callout",    name: "Callout",            icon: "fa-solid fa-circle-info" },
            { type: "alert",      name: "Alert Banner",       icon: "fa-solid fa-triangle-exclamation" },
            { type: "code",       name: "Code Block",         icon: "fa-solid fa-code" },
            { type: "icon",       name: "Icon / Emoji",       icon: "fa-solid fa-star" },
            { type: "rating",     name: "Star Rating",        icon: "fa-regular fa-star" },
            { type: "toc",        name: "Table of Contents",  icon: "fa-solid fa-list-ol" },
        ],
    },
    {
        name: "Data & Charts", icon: "fa-solid fa-chart-bar",
        items: [
            { type: "table",               name: "Table",             icon: "fa-solid fa-table" },
            { type: "bar-chart",           name: "Bar Chart",         icon: "fa-solid fa-chart-bar" },
            { type: "line-chart",          name: "Line Chart",        icon: "fa-solid fa-chart-line" },
            { type: "pie-chart",           name: "Pie Chart",         icon: "fa-solid fa-chart-pie" },
            { type: "doughnut-chart",      name: "Doughnut",          icon: "fa-solid fa-circle-half-stroke" },
            { type: "area-chart",          name: "Area Chart",        icon: "fa-solid fa-chart-area" },
            { type: "radar-chart",         name: "Radar",             icon: "fa-solid fa-circle-nodes" },
            { type: "metric",              name: "KPI Card",          icon: "fa-solid fa-square-poll-vertical" },
            { type: "progress",            name: "Progress Bar",      icon: "fa-solid fa-bars-progress" },
            { type: "circular-progress",   name: "Circular Progress", icon: "fa-solid fa-circle-notch" },
            { type: "stat-row",            name: "Stats Row",         icon: "fa-solid fa-table-columns" },
            { type: "timeline",            name: "Timeline",          icon: "fa-solid fa-timeline" },
        ],
    },
    {
        name: "Media", icon: "fa-solid fa-photo-film",
        items: [
            { type: "image", name: "Image",       icon: "fa-solid fa-image" },
            { type: "video", name: "Video Embed", icon: "fa-solid fa-film" },
        ],
    },
    {
        name: "Shapes & Layout", icon: "fa-solid fa-shapes",
        items: [
            { type: "rectangle",    name: "Rectangle",   icon: "fa-regular fa-square" },
            { type: "circle",       name: "Circle",      icon: "fa-regular fa-circle" },
            { type: "triangle",     name: "Triangle",    icon: "fa-solid fa-play fa-rotate-270" },
            { type: "star",         name: "Star",        icon: "fa-regular fa-star" },
            { type: "line",         name: "Line",        icon: "fa-solid fa-minus" },
            { type: "arrow",        name: "Arrow →",     icon: "fa-solid fa-arrow-right" },
            { type: "double-arrow", name: "Double ↔",   icon: "fa-solid fa-arrows-left-right" },
            { type: "divider",      name: "Divider",     icon: "fa-solid fa-grip-lines" },
            { type: "spacer",       name: "Spacer",      icon: "fa-solid fa-square-dashed" },
        ],
    },
    {
        name: "Cards & Components", icon: "fa-solid fa-id-card",
        items: [
            { type: "social-card",  name: "Profile Card", icon: "fa-solid fa-id-badge" },
            { type: "testimonial",  name: "Testimonial",  icon: "fa-solid fa-comment-dots" },
            { type: "kanban",       name: "Kanban Card",  icon: "fa-solid fa-thumbtack" },
            { type: "price-card",   name: "Price Card",   icon: "fa-solid fa-dollar-sign" },
        ],
    },
    {
        name: "Report Elements", icon: "fa-solid fa-file-lines",
        items: [
            { type: "pagenum",   name: "Page Number", icon: "fa-solid fa-hashtag" },
            { type: "date",      name: "Date",        icon: "fa-solid fa-calendar-days" },
            { type: "signature", name: "Signature",   icon: "fa-solid fa-signature" },
        ],
    },
];

// ─── Props ───────────────────────────────────────────────────────────────────
const props = defineProps({ report: Object });

// ─── State ───────────────────────────────────────────────────────────────────
const pages = ref(
    props.report?.content?.length
        ? props.report.content
        : [{ id: uuidv4(), elements: [] }]
);
const selectedElement   = ref(null);
const selectedPageIndex = ref(0);
const zoom              = ref(100);
const dark              = ref(false);
const showGrid          = ref(false);
const snapToGrid        = ref(false);
const showRulers        = ref(false);
const dragMode          = ref(false);
const gridSize          = ref(20);
const leftTab           = ref("elements");
const saveStatus        = ref("idle");
const elementSearch     = ref("");
const collapsedCategories = ref([]);
const history           = ref([JSON.stringify(pages.value)]);
const historyIndex      = ref(0);
const showExportMenu    = ref(false);
const exportMenuRef     = ref(null);
const toast             = reactive({ show: false, message: "", type: "success" });
const contextMenu       = reactive({ show: false, x: 0, y: 0, el: null, pi: null });
const chartLabelsInput  = ref("");
const chartValuesInput  = ref("");
const chartDatasetLabel = ref("Dataset 1");
const chartColor        = ref("#6366f1");
const pieColorsInput    = ref("");
const chartInstances    = new Map();

// ─── Report Settings ─────────────────────────────────────────────────────────
const rs = reactive({
    page_size:         props.report?.settings?.page_size         || "A4",
    orientation:       props.report?.settings?.orientation       || "portrait",
    primary_color:     props.report?.settings?.primary_color     || "#6366f1",
    background_color:  props.report?.settings?.background_color  || "#ffffff",
    accent_color:      props.report?.settings?.accent_color      || "#8b5cf6",
    text_color:        props.report?.settings?.text_color        || "#0f172a",
    font_family:       props.report?.settings?.font_family       || "'DM Sans', sans-serif",
    font_size:         props.report?.settings?.font_size         || 14,
    margin:            props.report?.settings?.margin            || 40,
    show_page_numbers: props.report?.settings?.show_page_numbers ?? false,
    show_header:       props.report?.settings?.show_header       ?? false,
    show_footer:       props.report?.settings?.show_footer       ?? false,
    header_text:       props.report?.settings?.header_text       || "",
    footer_text:       props.report?.settings?.footer_text       || "",
    footer_left:       props.report?.settings?.footer_left       || "",
    footer_right:      props.report?.settings?.footer_right      || "",
    header_color:      props.report?.settings?.header_color      || "#1e293b",
    footer_color:      props.report?.settings?.footer_color      || "#1e293b",
    watermark:         props.report?.settings?.watermark         || "",
    rtl:               props.report?.settings?.rtl               ?? false,
    bg_image:          props.report?.settings?.bg_image          || "",
    page_radius:       props.report?.settings?.page_radius       || 0,
});

const form = useForm({ title: props.report?.title || "Untitled Report" });

// ─── Computed ─────────────────────────────────────────────────────────────────
const canvasDimensions = computed(() => {
    const sizes = {
        A4:     { portrait: { width: 794,  height: 1123 }, landscape: { width: 1123, height: 794  } },
        Letter: { portrait: { width: 816,  height: 1056 }, landscape: { width: 1056, height: 816  } },
        Legal:  { portrait: { width: 816,  height: 1344 }, landscape: { width: 1344, height: 816  } },
        A3:     { portrait: { width: 1123, height: 1587 }, landscape: { width: 1587, height: 1123 } },
        A5:     { portrait: { width: 559,  height: 794  }, landscape: { width: 794,  height: 559  } },
    };
    return sizes[rs.page_size]?.[rs.orientation] || sizes.A4.portrait;
});

const inputCls = computed(() =>
    dark.value
        ? "w-full text-xs bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-200 transition-all"
        : "w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 transition-all"
);

// ─── Element factory ─────────────────────────────────────────────────────────
const createDefaultElement = (type, x = 60, y = 60) => {
    const base = {
        id: uuidv4(),
        type,
        position: { x, y },
        styles: { width: 200, height: 50, zIndex: 1, opacity: 100 },
    };
    const P = rs.primary_color;
    const T = rs.text_color;
    const presets = {
        heading:    { content: "Report Heading", styles: { ...base.styles, width: 460, height: 64, fontSize: 32, fontWeight: "700", color: T || "#0f172a" } },
        subheading: { content: "Section Title",  styles: { ...base.styles, width: 340, height: 44, fontSize: 20, fontWeight: "600", color: T || "#1e293b" } },
        text:       { content: "Add your paragraph text here. Double-click to edit inline.", styles: { ...base.styles, width: 420, height: 80, fontSize: 14, color: "#334155", lineHeight: 1.7 } },
        quote:      { content: "An insightful quote goes here.", styles: { ...base.styles, width: 380, height: 80, fontSize: 15, fontStyle: "italic", color: "#475569", backgroundColor: "#f8fafc", padding: 16 } },
        blockquote: { content: "Block quote text here.", styles: { ...base.styles, width: 380, height: 100, fontSize: 14, color: "#334155", backgroundColor: P + "10", padding: 16, borderRadius: 12 } },
        highlight:  { content: "Highlighted text", styles: { ...base.styles, width: 200, height: 36, fontSize: 14, color: "#713f12", backgroundColor: "#fef9c3", padding: 6, borderRadius: 4 } },
        list:       { items: ["First item", "Second item", "Third item"], styles: { ...base.styles, width: 280, height: 120, fontSize: 14, color: "#334155" } },
        checklist:  { items: [{ text: "Task one", checked: false }, { text: "Task two", checked: true }], styles: { ...base.styles, width: 300, height: 100, fontSize: 13 } },
        link:       { content: "Click here", href: "https://example.com", target: "_blank", styles: { ...base.styles, width: 160, height: 32, fontSize: 14, color: P } },
        badge:      { content: "Status", styles: { ...base.styles, width: 90, height: 36 } },
        callout:    { content: "Add your callout note here.", emoji: "ℹ️", calloutStyle: "info", styles: { ...base.styles, width: 380, height: 90, backgroundColor: "#eff6ff", borderColor: "#bfdbfe", borderWidth: 1, borderRadius: 10 } },
        alert:      { content: "Important alert message.", emoji: "⚠️", styles: { ...base.styles, width: 380, height: 70, backgroundColor: "#fef3c7", borderColor: "#f59e0b", color: "#92400e" } },
        code:       { content: 'const x = "Hello, World!";\nconsole.log(x);', language: "JavaScript", styles: { ...base.styles, width: 380, height: 120 } },
        icon:       { content: "★", styles: { ...base.styles, width: 60, height: 60, fontSize: 40, color: P } },
        rating:     { value: 4, styles: { ...base.styles, width: 160, height: 48, color: "#f59e0b" } },
        date:       { format: "long", styles: { ...base.styles, width: 220, height: 30, fontSize: 13, color: "#64748b" } },
        pagenum:    { styles: { ...base.styles, width: 100, height: 24, fontSize: 12, textAlign: "center", color: "#94a3b8" } },
        signature:  { label: "Authorised Signature", styles: { ...base.styles, width: 260, height: 80, color: "#334155" } },
        toc:        { title: "Table of Contents", items: [{ label: "Section 1", page: 1 }, { label: "Section 2", page: 3 }], styles: { ...base.styles, width: 360, height: 160, fontSize: 12, color: "#334155" } },
        timeline:   { items: [{ label: "Milestone 1", date: "2024 Q1", desc: "Kickoff" }, { label: "Milestone 2", date: "2024 Q3", desc: "Launch" }], styles: { ...base.styles, width: 340, height: 200 } },
        image:      { src: "", alt: "", styles: { ...base.styles, width: 320, height: 220, borderRadius: 8 } },
        video:      { src: "", styles: { ...base.styles, width: 360, height: 220, borderRadius: 8 } },
        table:      { columns: ["Name", "Value", "Change"], data: [{ Name: "Revenue", Value: "$1.2M", Change: "+12%" }, { Name: "Users", Value: "8,400", Change: "+5%" }], styles: { ...base.styles, width: 460, height: 130, headerBg: P, headerColor: "#fff", evenRowBg: "#fff", oddRowBg: "#f8fafc" } },
        "bar-chart":         { chartData: { labels: ["Q1","Q2","Q3","Q4"], values: [42,68,55,81] }, chartTitle: "Quarterly Performance", legendPosition: "bottom", styles: { ...base.styles, width: 400, height: 280 } },
        "line-chart":        { chartData: { labels: ["Jan","Feb","Mar","Apr","May","Jun"], values: [30,48,36,70,60,85] }, chartTitle: "Trend Analysis", legendPosition: "bottom", styles: { ...base.styles, width: 400, height: 280 } },
        "pie-chart":         { chartData: { labels: ["Cat A","Cat B","Cat C","Cat D"], values: [35,25,22,18] }, chartTitle: "Distribution", legendPosition: "bottom", styles: { ...base.styles, width: 320, height: 300 } },
        "doughnut-chart":    { chartData: { labels: ["Seg A","Seg B","Seg C"], values: [40,35,25] }, chartTitle: "Composition", legendPosition: "bottom", styles: { ...base.styles, width: 320, height: 300 } },
        "area-chart":        { chartData: { labels: ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"], values: [20,45,38,60,55,72,80] }, chartTitle: "Weekly Overview", legendPosition: "bottom", styles: { ...base.styles, width: 400, height: 280 } },
        "radar-chart":       { chartData: { labels: ["Speed","Accuracy","Efficiency","Quality","Innovation"], values: [80,92,75,88,70] }, chartTitle: "Performance", legendPosition: "bottom", styles: { ...base.styles, width: 340, height: 320 } },
        metric:              { label: "Total Revenue", value: "$48,293", change: "12.5%", changeType: "positive", changePeriod: "vs last month", styles: { ...base.styles, width: 240, height: 120, backgroundColor: "#f8fafc", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 14 } },
        progress:            { label: "Completion Rate", value: 72, styles: { ...base.styles, width: 340, height: 50, color: P, trackColor: "#e2e8f0", trackHeight: 8 } },
        "circular-progress": { label: "Progress", value: 72, styles: { ...base.styles, width: 180, height: 200, color: P, trackColor: "#e2e8f0" } },
        "stat-row":          { cols: 3, stats: [{ label: "Revenue", value: "$48K" }, { label: "Users", value: "8.4K" }, { label: "Growth", value: "+12%" }], styles: { ...base.styles, width: 460, height: 100 } },
        rectangle:           { styles: { ...base.styles, width: 200, height: 120, backgroundColor: "#e0e7ff", borderRadius: 10 } },
        circle:              { styles: { ...base.styles, width: 100, height: 100, backgroundColor: P } },
        triangle:            { styles: { ...base.styles, width: 120, height: 120, backgroundColor: "#f59e0b" } },
        star:                { styles: { ...base.styles, width: 80,  height: 80,  backgroundColor: P } },
        line:                { styles: { ...base.styles, width: 240, height: 20, borderWidth: 2, color: "#cbd5e1" } },
        arrow:               { styles: { ...base.styles, width: 200, height: 30, borderWidth: 2, color: P } },
        "double-arrow":      { styles: { ...base.styles, width: 200, height: 30, borderWidth: 2, color: P } },
        divider:             { styles: { ...base.styles, width: 460, height: 20, borderWidth: 1, borderStyle: "solid", color: "#e2e8f0" } },
        spacer:              { styles: { ...base.styles, width: 200, height: 40 } },
        "social-card":       { content: "Name Here", subtitle: "Title", avatar: "👤", styles: { ...base.styles, width: 240, height: 200, backgroundColor: "#fff", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 16 } },
        testimonial:         { content: "Amazing product!", author: "John Doe", role: "CEO", avatar: "👤", styles: { ...base.styles, width: 300, height: 180, backgroundColor: "#f8fafc", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 16 } },
        kanban:              { content: "Task Title", status: "In Progress", due: "Dec 31", styles: { ...base.styles, width: 220, height: 100, backgroundColor: "#fff", borderColor: "#e2e8f0", accentColor: P } },
        "price-card":        { plan: "Pro Plan", price: "$29", period: "per month", features: ["Feature 1","Feature 2","Feature 3"], highlighted: false, styles: { ...base.styles, width: 220, height: 280, backgroundColor: "#fff", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 16 } },
    };
    const d = presets[type] || {};
    return { ...base, ...d, styles: { ...base.styles, ...(d.styles || {}) } };
};

// ─── Element operations ───────────────────────────────────────────────────────
const addElement = (type) => {
    const pi = selectedPageIndex.value ?? 0;
    addElementAtPosition(type, pi, 60, 60 + pages.value[pi].elements.length * 30);
};

const addElementAtPosition = (type, pi, x, y) => {
    const el = createDefaultElement(type, x, y);
    pages.value[pi].elements.push(el);
    nextTick(() => { if (isChartType(type)) initChart(el); });
    selectElement(el, pi);
    pushHistory();
};

const duplicateElement = (pi, el) => {
    const clone = JSON.parse(JSON.stringify(el));
    clone.id = uuidv4();
    clone.position = { x: el.position.x + 20, y: el.position.y + 20 };
    pages.value[pi].elements.push(clone);
    nextTick(() => { if (isChartType(clone.type)) initChart(clone); });
    selectElement(clone, pi);
    pushHistory();
};

const deleteElement = (pi, id) => {
    pages.value[pi].elements = pages.value[pi].elements.filter(e => e.id !== id);
    if (selectedElement.value?.id === id) selectedElement.value = null;
    pushHistory();
};

const deleteSelectedElement = () => {
    if (!selectedElement.value) return;
    deleteElement(selectedPageIndex.value, selectedElement.value.id);
};

const selectElement = (el, pi) => {
    selectedElement.value = el;
    selectedPageIndex.value = pi;
    if (isChartType(el.type) && el.chartData) {
        chartLabelsInput.value  = (el.chartData.labels || []).join(", ");
        chartValuesInput.value  = (el.chartData.values || []).join(", ");
        chartDatasetLabel.value = el.chartDatasetLabel || "Dataset 1";
        chartColor.value        = el.chartColor || rs.primary_color;
        pieColorsInput.value    = (el.pieColors || []).join(", ");
    }
};

const deselectAll = () => { selectedElement.value = null; };
const lockElement = (el) => { el.locked = !el.locked; pushHistory(); };

const moveElementToPage = (el, fromPi, toPi) => {
    if (toPi < 0 || toPi >= pages.value.length || toPi === fromPi) return;
    pages.value[fromPi].elements = pages.value[fromPi].elements.filter(e => e.id !== el.id);
    pages.value[toPi].elements.push({ ...el });
    selectedPageIndex.value = toPi;
    nextTick(() => { if (isChartType(el.type)) initChart(el); });
    pushHistory();
    showToast(`Moved to Page ${toPi + 1}`);
};

// ─── Element drag on canvas ───────────────────────────────────────────────────
let isDraggingEl = false, dSX = 0, dSY = 0, eSX = 0, eSY = 0;

const startElementDrag = (e, el, pi) => {
    if (e.button !== 0 || el.locked) return;
    selectElement(el, pi);
    isDraggingEl = true; dSX = e.clientX; dSY = e.clientY;
    eSX = el.position.x; eSY = el.position.y;
    window.addEventListener("mousemove", onDragMove);
    window.addEventListener("mouseup", stopDrag);
    e.preventDefault();
};
const onDragMove = (e) => {
    if (!isDraggingEl || !selectedElement.value) return;
    const zf = zoom.value / 100;
    let nx = eSX + (e.clientX - dSX) / zf;
    let ny = eSY + (e.clientY - dSY) / zf;
    if (snapToGrid.value) {
        nx = Math.round(nx / gridSize.value) * gridSize.value;
        ny = Math.round(ny / gridSize.value) * gridSize.value;
    }
    selectedElement.value.position.x = Math.max(0, nx);
    selectedElement.value.position.y = Math.max(0, ny);
};
const stopDrag = () => {
    isDraggingEl = false;
    window.removeEventListener("mousemove", onDragMove);
    window.removeEventListener("mouseup", stopDrag);
    pushHistory();
};

// ─── Resize ───────────────────────────────────────────────────────────────────
let isResizing = false, rDir = "", rSX = 0, rSY = 0, rSW = 0, rSH = 0, rSEX = 0, rSEY = 0;
const startResize = (dir, e) => {
    e.preventDefault();
    isResizing = true; rDir = dir; rSX = e.clientX; rSY = e.clientY;
    rSW = selectedElement.value.styles.width || 200;
    rSH = selectedElement.value.styles.height || 50;
    rSEX = selectedElement.value.position.x;
    rSEY = selectedElement.value.position.y;
    window.addEventListener("mousemove", onResizeMove);
    window.addEventListener("mouseup", stopResize);
};
const onResizeMove = (e) => {
    if (!isResizing || !selectedElement.value) return;
    const zf = zoom.value / 100, dx = (e.clientX - rSX) / zf, dy = (e.clientY - rSY) / zf;
    const MIN = 20, s = selectedElement.value.styles, p = selectedElement.value.position;
    if (rDir.includes("e")) s.width  = Math.max(MIN, rSW + dx);
    if (rDir.includes("s")) s.height = Math.max(MIN, rSH + dy);
    if (rDir.includes("w")) { const nw = Math.max(MIN, rSW - dx); p.x = rSEX + (rSW - nw); s.width = nw; }
    if (rDir.includes("n")) { const nh = Math.max(MIN, rSH - dy); p.y = rSEY + (rSH - nh); s.height = nh; }
};
const stopResize = () => {
    isResizing = false;
    window.removeEventListener("mousemove", onResizeMove);
    window.removeEventListener("mouseup", stopResize);
    pushHistory();
};

// ─── Rotation ─────────────────────────────────────────────────────────────────
const startRotation = (e, el) => {
    const rect = e.currentTarget.parentElement.getBoundingClientRect();
    const cx = rect.left + rect.width / 2, cy = rect.top + rect.height / 2;
    const onMove = (mv) => {
        el.styles.rotate = Math.round(Math.atan2(mv.clientY - cy, mv.clientX - cx) * (180 / Math.PI) + 90);
    };
    const onUp = () => { window.removeEventListener("mousemove", onMove); window.removeEventListener("mouseup", onUp); pushHistory(); };
    window.addEventListener("mousemove", onMove);
    window.addEventListener("mouseup", onUp);
    e.preventDefault();
};

// ─── Text editing ─────────────────────────────────────────────────────────────
const onTextBlur = (el, e) => { el.content = e.target.innerHTML || e.target.innerText; pushHistory(); };
const onListItemBlur = (el, i, e) => { el.items[i] = e.target.innerText; pushHistory(); };
const addListItem = (el) => { el.items.push("New item"); pushHistory(); };
const onChecklistItemBlur = (el, i, e) => { el.items[i].text = e.target.innerText; pushHistory(); };
const addChecklistItem = (el) => { el.items.push({ text: "New task", checked: false }); pushHistory(); };
const onTableCellBlur = (el, ri, ci, e) => { el.data[ri][el.columns[ci]] = e.target.innerText; pushHistory(); };
const onTableHeaderBlur = (el, col, e) => {
    const newName = e.target.innerText.trim() || col;
    if (newName !== col) {
        const idx = el.columns.indexOf(col);
        if (idx > -1) { el.columns[idx] = newName; el.data.forEach(r => { r[newName] = r[col]; delete r[col]; }); }
    }
    pushHistory();
};
const addTableRow = (el) => { const r = {}; el.columns.forEach(c => r[c] = ""); el.data.push(r); pushHistory(); };
const addTableColumn = (el) => { const n = `Col ${el.columns.length + 1}`; el.columns.push(n); el.data.forEach(r => r[n] = ""); pushHistory(); };
const removeTableRow = (el) => { if (el.data.length > 1) { el.data.pop(); pushHistory(); } };
const removeTableColumn = (el) => { if (el.columns.length > 1) { const l = el.columns.pop(); el.data.forEach(r => delete r[l]); pushHistory(); } };
const onTocTitleBlur = (el, e) => { el.title = e.target.innerText; pushHistory(); };
const onTocItemBlur = (el, i, field, e) => { el.items[i][field] = e.target.innerText; pushHistory(); };
const addTocItem = (el) => { el.items.push({ label: `Section ${el.items.length + 1}`, page: 1 }); pushHistory(); };
const onTimelineBlur = (el, i, field, e) => { el.items[i][field] = e.target.innerText; pushHistory(); };
const addTimelineItem = (el) => { el.items.push({ label: "New Milestone", date: "2026", desc: "Description" }); pushHistory(); };

const applyCalloutStyle = (el) => {
    const s = CALLOUT_PRESETS[el.calloutStyle] || CALLOUT_PRESETS.info;
    Object.assign(el.styles, { backgroundColor: s.bg, borderColor: s.border, color: s.color });
    if (!el.emoji) el.emoji = s.emoji;
    pushHistory();
};

// ─── Charts ───────────────────────────────────────────────────────────────────
const isChartType = t => ["bar-chart","line-chart","pie-chart","area-chart","doughnut-chart","radar-chart"].includes(t);
const chartTypeMap = { "bar-chart":"bar","line-chart":"line","pie-chart":"pie","area-chart":"line","doughnut-chart":"doughnut","radar-chart":"radar" };

const initChart = (el) => {
    nextTick(() => {
        const canvas = document.getElementById("chart-" + el.id);
        if (!canvas) return;
        if (chartInstances.has(el.id)) { chartInstances.get(el.id).destroy(); chartInstances.delete(el.id); }
        const type = chartTypeMap[el.type] || "bar";
        const colors = el.pieColors?.length ? el.pieColors : ["#6366f1","#10b981","#f59e0b","#ef4444","#8b5cf6","#06b6d4","#f97316","#ec4899"];
        const isArea = el.type === "area-chart";
        const chartInstance = new Chart(canvas.getContext("2d"), {
            type,
            data: {
                labels: el.chartData?.labels || [],
                datasets: [{
                    label:           el.chartDatasetLabel || el.chartTitle || "Dataset",
                    data:            el.chartData?.values || [],
                    backgroundColor: (type === "pie" || type === "doughnut") ? colors : (isArea ? (el.chartColor || rs.primary_color) + "30" : el.chartColor || rs.primary_color),
                    borderColor:     (type === "pie" || type === "doughnut") ? colors : el.chartColor || rs.primary_color,
                    borderWidth: 2, fill: isArea, tension: (el.type === "line-chart" || isArea) ? 0.4 : 0,
                    pointBackgroundColor: el.chartColor || rs.primary_color, pointRadius: type === "radar" ? 4 : 3,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: el.legendPosition === "none" ? false : (el.legendPosition || "bottom"), display: el.legendPosition !== "none", labels: { padding: 12, font: { size: 11 }, boxWidth: 12 } },
                    title:  { display: !!el.chartTitle, text: el.chartTitle, font: { size: 13, weight: "600" } },
                },
                scales: (type === "pie" || type === "doughnut" || type === "radar") ? {} : {
                    x: { grid: { color: "#f1f5f9" }, ticks: { font: { size: 11 } } },
                    y: { grid: { color: "#f1f5f9" }, ticks: { font: { size: 11 } } },
                },
            },
        });
        chartInstances.set(el.id, chartInstance);
    });
};

const refreshChart = (el) => { if (el && isChartType(el.type)) initChart(el); };
const updateChartData = () => {
    if (!selectedElement.value || !isChartType(selectedElement.value.type)) return;
    selectedElement.value.chartData = {
        labels: chartLabelsInput.value.split(",").map(s => s.trim()).filter(Boolean),
        values: chartValuesInput.value.split(",").map(s => parseFloat(s.trim())).filter(n => !isNaN(n)),
    };
    selectedElement.value.chartDatasetLabel = chartDatasetLabel.value;
    selectedElement.value.chartColor = chartColor.value;
    if (pieColorsInput.value) selectedElement.value.pieColors = pieColorsInput.value.split(",").map(s => s.trim());
    refreshChart(selectedElement.value);
    pushHistory();
};
const reinitAllCharts = () => pages.value.forEach(p => p.elements.forEach(el => { if (isChartType(el.type)) initChart(el); }));

// ─── Image upload ─────────────────────────────────────────────────────────────
const uploadImage = async (e, el) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith("image/")) { showToast("Only image files are allowed.", "error"); return; }
    if (file.size > 10 * 1024 * 1024) { showToast("Image must be under 10 MB.", "error"); return; }
    const localUrl = URL.createObjectURL(file);
    el.src = localUrl; el._uploading = true;
    try {
        const fd = new FormData(); fd.append("image", file);
        const res = await axios.post("/api/upload-image", fd, { headers: { "Content-Type": "multipart/form-data" } });
        el.src = res.data.url; URL.revokeObjectURL(localUrl);
        showToast("Image uploaded!"); pushHistory();
    } catch { showToast("Upload failed — local preview only.", "error"); }
    finally { el._uploading = false; if (e.target) e.target.value = ""; }
};

// ─── Page management ──────────────────────────────────────────────────────────
// New page inherits current template settings (blank page structure)
const newBlankPage = () => ({ id: uuidv4(), label: `Page ${pages.value.length + 1}`, elements: [] });

const addPageAfter = (i) => {
    pages.value.splice(i + 1, 0, newBlankPage());
    selectedPageIndex.value = i + 1;
    pushHistory();
};
const removePage = (i) => {
    if (pages.value.length <= 1) return;
    pages.value.splice(i, 1);
    if (selectedPageIndex.value >= pages.value.length) selectedPageIndex.value = pages.value.length - 1;
    pushHistory();
};
const duplicatePage = (i) => {
    const clone = JSON.parse(JSON.stringify(pages.value[i]));
    clone.id = uuidv4();
    clone.elements = clone.elements.map(el => ({ ...el, id: uuidv4() }));
    pages.value.splice(i + 1, 0, clone);
    nextTick(() => clone.elements.forEach(el => { if (isChartType(el.type)) initChart(el); }));
    pushHistory();
};

// ─── Layer order ──────────────────────────────────────────────────────────────
const bringForward = () => { if (!selectedElement.value) return; selectedElement.value.styles.zIndex = (selectedElement.value.styles.zIndex || 1) + 1; pushHistory(); };
const sendBackward  = () => { if (!selectedElement.value) return; selectedElement.value.styles.zIndex = Math.max(1, (selectedElement.value.styles.zIndex || 1) - 1); pushHistory(); };
const bringToFront  = () => { if (!selectedElement.value) return; const maxZ = Math.max(...pages.value[selectedPageIndex.value].elements.map(e => e.styles?.zIndex || 1)); selectedElement.value.styles.zIndex = maxZ + 1; pushHistory(); };
const sendToBack    = () => { if (!selectedElement.value) return; const minZ = Math.min(...pages.value[selectedPageIndex.value].elements.map(e => e.styles?.zIndex || 1)); selectedElement.value.styles.zIndex = Math.max(1, minZ - 1); pushHistory(); };

const alignElement = (dir) => {
    if (!selectedElement.value) return;
    const el = selectedElement.value, pw = canvasDimensions.value.width, ph = canvasDimensions.value.height;
    const w = el.styles.width || 200, h = el.styles.height || 50;
    if (dir === "left")     el.position.x = 0;
    if (dir === "right")    el.position.x = pw - w;
    if (dir === "center-h") el.position.x = (pw - w) / 2;
    if (dir === "top")      el.position.y = 0;
    if (dir === "bottom")   el.position.y = ph - h;
    if (dir === "center-v") el.position.y = (ph - h) / 2;
    pushHistory();
};

// ─── History ──────────────────────────────────────────────────────────────────
const pushHistory = () => {
    const snap = JSON.stringify(pages.value);
    if (snap === history.value[historyIndex.value]) return;
    history.value = history.value.slice(0, historyIndex.value + 1);
    history.value.push(snap);
    historyIndex.value = history.value.length - 1;
    if (history.value.length > 100) { history.value.shift(); historyIndex.value--; }
    autoSave();
};
const undo = () => { if (historyIndex.value <= 0) return; historyIndex.value--; pages.value = JSON.parse(history.value[historyIndex.value]); nextTick(reinitAllCharts); };
const redo = () => { if (historyIndex.value >= history.value.length - 1) return; historyIndex.value++; pages.value = JSON.parse(history.value[historyIndex.value]); nextTick(reinitAllCharts); };

// ─── Save / Export ────────────────────────────────────────────────────────────
let saveTimeout = null;
const autoSave = () => { if (saveTimeout) clearTimeout(saveTimeout); saveTimeout = setTimeout(saveReport, 2500); };

const saveReport = async () => {
    saveStatus.value = "saving";
    try {
        await axios.put(route("reports.update", props.report.slug), {
            title: form.title, content: pages.value, settings: rs,
        });
        saveStatus.value = "saved";
        setTimeout(() => { saveStatus.value = "idle"; }, 2000);
    } catch { saveStatus.value = "idle"; showToast("Save failed. Please try again.", "error"); }
};
const previewReport = () => window.open(route("reports.preview", props.report.slug), "_blank");
const exportAs = (fmt) => {
    showExportMenu.value = false;
    if (fmt === "pdf") window.open(route("reports.download", props.report.slug), "_blank");
    else showToast(`${fmt.toUpperCase()} export coming soon.`, "error");
};

// ─── Context Menu ────────────────────────────────────────────────────────────
const openContextMenu = (e, el, pi) => {
    contextMenu.show = true;
    contextMenu.x    = Math.min(e.clientX, window.innerWidth - 200);
    contextMenu.y    = Math.min(e.clientY, window.innerHeight - 300);
    contextMenu.el   = el;
    contextMenu.pi   = pi;
    selectElement(el, pi);
};

// ─── Fit / Fullscreen ─────────────────────────────────────────────────────────
const fitToScreen = (containerWidth) => {
    if (!containerWidth) return;
    zoom.value = Math.min(150, Math.max(25, Math.floor((containerWidth / canvasDimensions.value.width) * 100)));
};
const toggleFullscreen = () => {
    if (!document.fullscreenElement) document.documentElement.requestFullscreen?.().catch(() => {});
    else document.exitFullscreen?.();
};

// ─── Toast ───────────────────────────────────────────────────────────────────
const showToast = (message, type = "success") => {
    toast.message = message; toast.type = type; toast.show = true;
    setTimeout(() => { toast.show = false; }, 3000);
};

// ─── Keyboard ─────────────────────────────────────────────────────────────────
const handleKeyDown = (e) => {
    if (["INPUT","TEXTAREA","SELECT"].includes(e.target.tagName) || e.target.contentEditable === "true") return;
    if ((e.ctrlKey || e.metaKey) && e.key === "z") { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === "y") { e.preventDefault(); redo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === "s") { e.preventDefault(); saveReport(); }
    if ((e.ctrlKey || e.metaKey) && e.key === "d") { e.preventDefault(); if (selectedElement.value) duplicateElement(selectedPageIndex.value, selectedElement.value); }
    if (e.key === "D" && !e.ctrlKey) dragMode.value = !dragMode.value;
    if (e.key === "G" && !e.ctrlKey) showGrid.value = !showGrid.value;
    if (e.key === "R" && !e.ctrlKey) showRulers.value = !showRulers.value;
    if (e.key === "F" && !e.ctrlKey) toggleFullscreen();
    if (e.key === "Escape") { deselectAll(); contextMenu.show = false; }
    if (e.key === "Delete" || e.key === "Backspace") { if (selectedElement.value) deleteSelectedElement(); }
    if (selectedElement.value && ["ArrowUp","ArrowDown","ArrowLeft","ArrowRight"].includes(e.key)) {
        e.preventDefault();
        const step = e.shiftKey ? 10 : 1;
        if (e.key === "ArrowUp")    selectedElement.value.position.y -= step;
        if (e.key === "ArrowDown")  selectedElement.value.position.y += step;
        if (e.key === "ArrowLeft")  selectedElement.value.position.x -= step;
        if (e.key === "ArrowRight") selectedElement.value.position.x += step;
        pushHistory();
    }
    if (e.key === "=" && (e.ctrlKey || e.metaKey)) { e.preventDefault(); zoom.value = Math.min(200, zoom.value + 10); }
    if (e.key === "-" && (e.ctrlKey || e.metaKey)) { e.preventDefault(); zoom.value = Math.max(25, zoom.value - 10); }
    if (e.key === "0" && (e.ctrlKey || e.metaKey)) { e.preventDefault(); zoom.value = 100; }
};

const handleOutsideClick = (e) => {
    if (exportMenuRef.value && !exportMenuRef.value.contains(e.target)) showExportMenu.value = false;
    if (contextMenu.show) contextMenu.show = false;
};

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
    document.addEventListener("click", handleOutsideClick);
    nextTick(reinitAllCharts);
    dark.value = localStorage.getItem("rb-dark") === "1";
});
onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeyDown);
    document.removeEventListener("click", handleOutsideClick);
    chartInstances.forEach(c => c.destroy());
    if (saveTimeout) clearTimeout(saveTimeout);
});

watch(dark, v => localStorage.setItem("rb-dark", v ? "1" : "0"));

// Expose to sub-components via provide
import { provide } from "vue";
provide("isChartType",       isChartType);
provide("initChart",         initChart);
provide("createDefaultElement", createDefaultElement);
provide("SHADOW_MAP",        SHADOW_MAP);
provide("CALLOUT_PRESETS",   CALLOUT_PRESETS);
provide("CODE_LANGS",        CODE_LANGS);
provide("FONTS",             FONTS);
provide("FONT_WEIGHTS",      FONT_WEIGHTS);
provide("PAGE_SIZES",        PAGE_SIZES);
provide("SHORTCUTS",         SHORTCUTS);
provide("QUICK_ADD",         QUICK_ADD);
provide("ELEMENT_CATEGORIES",ELEMENT_CATEGORIES);
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.dropdown-enter-active { transition: all 0.15s ease-out; }
.dropdown-leave-active { transition: all 0.1s ease-in; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px) scale(0.97); }
.toast-enter-active { transition: all 0.25s cubic-bezier(0.175,0.885,0.32,1.275); }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(12px); }
</style>

<!-- thats nice  now with out changing any thing use fontawsom icon sand add all seetings related to pages ,font,fontsize,family,header,footer settings,page number settings and all other settings and also remove the dark mode functions but do not remove the drark mode style classes from tags  as i have add drag mode toggle buutton on my header component and when new page is created then the new page must be according to the selected template AND keep reponsive -->
