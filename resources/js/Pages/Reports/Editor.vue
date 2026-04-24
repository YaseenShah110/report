<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3 h-full px-1">
                <!-- Left: breadcrumb + title -->
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <button @click="router.get(route('reports.index'))"
                        class="flex items-center gap-1 text-xs font-semibold shrink-0 px-2 py-1.5 rounded-lg transition-all text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:text-slate-100 dark:hover:bg-white/10">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                        Reports
                    </button>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <input v-model="form.title"
                        class="text-sm font-bold bg-transparent border-0 border-b-2 border-transparent focus:border-indigo-500 focus:outline-none px-1 py-0.5 min-w-0 flex-1 transition-colors text-slate-800 dark:text-slate-100 placeholder:text-slate-300 dark:placeholder:text-slate-600"
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
                        :class="dragMode ? 'bg-violet-600 text-white border-violet-600 shadow-md shadow-violet-500/30' : 'bg-white dark:bg-white/10 text-slate-500 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/15'">
                        <i class="fa-solid fa-hand text-xs"></i>
                        <span class="hidden sm:inline">{{ dragMode ? "Drag ON" : "Drag" }}</span>
                    </button>

                    <!-- Grid Toggle -->
                    <button @click="showGrid = !showGrid" :title="showGrid ? 'Hide Grid (G)' : 'Show Grid (G)'"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="showGrid ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10'">
                        <i class="fa-solid fa-border-all text-xs"></i>
                    </button>

                    <!-- Rulers Toggle -->
                    <button @click="showRulers = !showRulers" title="Toggle Rulers (R)"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="showRulers ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10'">
                        <i class="fa-solid fa-ruler text-xs"></i>
                    </button>

                    <div class="w-px h-5 mx-0.5 bg-slate-200 dark:bg-slate-700"></div>

                    <!-- Undo/Redo -->
                    <button @click="undo" :disabled="historyIndex <= 0" title="Undo (Ctrl+Z)"
                        class="p-1.5 rounded-lg transition-colors disabled:opacity-25 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                    </button>
                    <button @click="redo" :disabled="historyIndex >= history.length - 1" title="Redo (Ctrl+Y)"
                        class="p-1.5 rounded-lg transition-colors disabled:opacity-25 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10">
                        <i class="fa-solid fa-rotate-right text-xs"></i>
                    </button>

                    <div class="w-px h-5 mx-0.5 bg-slate-200 dark:bg-slate-700"></div>

                    <!-- Fullscreen -->
                    <button @click="toggleFullscreen" title="Fullscreen (F)"
                        class="p-1.5 rounded-lg transition-colors text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/10">
                        <i class="fa-solid fa-expand text-xs"></i>
                    </button>

                    <div class="w-px h-5 mx-0.5 bg-slate-200 dark:bg-slate-700"></div>

                    <!-- Status Badge -->
                    <div class="relative" ref="statusMenuRef">
                        <button @click="showStatusMenu = !showStatusMenu"
                            class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg border transition-all border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusDot"></span>
                            <span class="hidden sm:inline capitalize">{{ reportStatus }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>
                        <transition name="dropdown">
                            <div v-if="showStatusMenu"
                                class="absolute right-0 top-full mt-1 w-40 rounded-xl shadow-xl border overflow-hidden z-50 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700">
                                <button v-for="s in ['draft','published','archived']" :key="s"
                                    @click="updateStatus(s)"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold transition-colors text-left text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                    <span class="w-2 h-2 rounded-full" :class="s==='published'?'bg-emerald-500':s==='draft'?'bg-amber-500':'bg-slate-400'"></span>
                                    <span class="capitalize">{{ s }}</span>
                                </button>
                            </div>
                        </transition>
                    </div>

                    <!-- Save -->
                    <button @click="saveReport"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-slate-800 dark:bg-slate-600 hover:bg-slate-700 dark:hover:bg-slate-500 text-white">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span class="hidden sm:inline">Save</span>
                    </button>

                    <!-- Preview -->
                    <button @click="previewReport"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all">
                        <i class="fa-solid fa-eye text-xs"></i>
                        <span class="hidden sm:inline">Preview</span>
                    </button>

                    <!-- Export -->
                    <div class="relative" ref="exportMenuRef">
                        <button @click="showExportMenu = !showExportMenu"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition-all">
                            <i class="fa-solid fa-file-export text-xs"></i>
                            <span class="hidden sm:inline">Export</span>
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>
                        <transition name="dropdown">
                            <div v-if="showExportMenu"
                                class="absolute right-0 top-full mt-1 w-52 rounded-xl shadow-xl border overflow-hidden z-50 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700">
                                <button v-for="fmt in EXPORT_FORMATS" :key="fmt.key" @click="exportAs(fmt.key)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold transition-colors text-left text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
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

        <!-- MAIN EDITOR LAYOUT -->
        <div class="flex overflow-hidden transition-colors duration-200 bg-slate-100 dark:bg-slate-950"
            :style="{ height: 'calc(100vh - 56px)' }">

            <!-- LEFT SIDEBAR -->
            <EditorLeftSidebar
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
                :left-tab="leftTab"
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
                @select-page="selectedPageIndex = $event"
            />

            <!-- RIGHT PROPERTIES PANEL -->
            <EditorPropertiesPanel
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
                @add-table-row="addTableRow"
                @add-table-column="addTableColumn"
                @remove-table-row="removeTableRow"
                @remove-table-column="removeTableColumn"
            />
        </div>

        <!-- Context Menu -->
        <EditorContextMenu
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

        <!-- Toast Notification -->
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
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick, provide } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";
import Chart from "chart.js/auto";

import EditorLeftSidebar from "@/Components/Editor/EditorLeftSidebar.vue";
import EditorCanvas from "@/Components/Editor/EditorCanvas.vue";
import EditorPropertiesPanel from "@/Components/Editor/EditorPropertiesPanel.vue";
import EditorContextMenu from "@/Components/Editor/EditorContextMenu.vue";

// ─── Constants ───────────────────────────────────────────────────────────────
const EXPORT_FORMATS = [
    { key: "pdf",   label: "PDF Document", desc: "Print-ready PDF",      icon: "fa-solid fa-file-pdf text-red-500" },
    { key: "xlsx",  label: "Excel Sheet",  desc: "Data in .xlsx",        icon: "fa-solid fa-file-excel text-emerald-500" },
    { key: "csv",   label: "CSV File",     desc: "Raw data export",      icon: "fa-solid fa-file-csv text-blue-500" },
    { key: "png",   label: "PNG Image",    desc: "High-res screenshot",  icon: "fa-solid fa-file-image text-purple-500" },
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

const CALLOUT_PRESETS = {
    info:    { bg: "#eff6ff", border: "#bfdbfe", color: "#1e40af", emoji: "ℹ️" },
    warning: { bg: "#fffbeb", border: "#fde68a", color: "#92400e", emoji: "⚠️" },
    success: { bg: "#ecfdf5", border: "#a7f3d0", color: "#065f46", emoji: "✅" },
    danger:  { bg: "#fef2f2", border: "#fecaca", color: "#991b1b", emoji: "🚨" },
    neutral: { bg: "#f8fafc", border: "#e2e8f0", color: "#334155", emoji: "📝" },
    purple:  { bg: "#faf5ff", border: "#e9d5ff", color: "#6b21a8", emoji: "💡" },
};

const SHORTCUTS = [
    { key: "Ctrl+Z",   desc: "Undo" },
    { key: "Ctrl+Y",   desc: "Redo" },
    { key: "Ctrl+S",   desc: "Save" },
    { key: "Ctrl+D",   desc: "Duplicate" },
    { key: "Del",      desc: "Delete" },
    { key: "D",        desc: "Drag Mode" },
    { key: "G",        desc: "Grid" },
    { key: "R",        desc: "Rulers" },
    { key: "F",        desc: "Fullscreen" },
    { key: "Esc",      desc: "Deselect" },
    { key: "↑↓←→",    desc: "Nudge 1px" },
    { key: "Shift+↑",  desc: "Nudge 10px" },
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
            { type: "heading",    name: "Heading H1",         icon: "fa-solid fa-heading",           color: "#6366f1" },
            { type: "subheading", name: "Heading H2",         icon: "fa-solid fa-h",                 color: "#8b5cf6" },
            { type: "text",       name: "Paragraph",          icon: "fa-solid fa-paragraph",          color: "#64748b" },
            { type: "quote",      name: "Quote",              icon: "fa-solid fa-quote-left",         color: "#0ea5e9" },
            { type: "blockquote", name: "Block Quote",        icon: "fa-solid fa-quote-right",        color: "#0ea5e9" },
            { type: "highlight",  name: "Highlight",          icon: "fa-solid fa-highlighter",        color: "#f59e0b" },
            { type: "list",       name: "Bullet List",        icon: "fa-solid fa-list",              color: "#10b981" },
            { type: "checklist",  name: "Checklist",          icon: "fa-solid fa-list-check",        color: "#10b981" },
            { type: "link",       name: "Hyperlink",          icon: "fa-solid fa-link",              color: "#3b82f6" },
            { type: "badge",      name: "Badge / Tag",        icon: "fa-solid fa-tag",               color: "#f97316" },
            { type: "callout",    name: "Callout Box",        icon: "fa-solid fa-circle-info",       color: "#0ea5e9" },
            { type: "alert",      name: "Alert Banner",       icon: "fa-solid fa-triangle-exclamation", color: "#ef4444" },
            { type: "code",       name: "Code Block",         icon: "fa-solid fa-code",              color: "#475569" },
            { type: "icon",       name: "Icon / Emoji",       icon: "fa-solid fa-star",              color: "#f59e0b" },
            { type: "rating",     name: "Star Rating",        icon: "fa-regular fa-star",             color: "#f59e0b" },
            { type: "toc",        name: "Table of Contents",  icon: "fa-solid fa-list-ol",           color: "#8b5cf6" },
        ],
    },
    {
        name: "Charts & Data", icon: "fa-solid fa-chart-bar",
        items: [
            { type: "bar-chart",         name: "Bar Chart",          icon: "fa-solid fa-chart-bar",          color: "#6366f1" },
            { type: "line-chart",        name: "Line Chart",         icon: "fa-solid fa-chart-line",         color: "#3b82f6" },
            { type: "pie-chart",         name: "Pie Chart",          icon: "fa-solid fa-chart-pie",          color: "#f59e0b" },
            { type: "doughnut-chart",    name: "Doughnut Chart",     icon: "fa-solid fa-circle-half-stroke", color: "#ec4899" },
            { type: "area-chart",        name: "Area Chart",         icon: "fa-solid fa-chart-area",         color: "#8b5cf6" },
            { type: "radar-chart",       name: "Radar Chart",        icon: "fa-solid fa-circle-nodes",       color: "#14b8a6" },
            { type: "table",             name: "Data Table",         icon: "fa-solid fa-table",              color: "#64748b" },
            { type: "metric",            name: "KPI Card",           icon: "fa-solid fa-square-poll-vertical", color: "#6366f1" },
            { type: "progress",          name: "Progress Bar",       icon: "fa-solid fa-bars-progress",      color: "#10b981" },
            { type: "circular-progress", name: "Circular Progress",  icon: "fa-solid fa-circle-notch",       color: "#f97316" },
            { type: "stat-row",          name: "Stats Row",          icon: "fa-solid fa-table-columns",      color: "#8b5cf6" },
            { type: "timeline",          name: "Timeline",           icon: "fa-solid fa-timeline",           color: "#0ea5e9" },
        ],
    },
    {
        name: "Media", icon: "fa-solid fa-photo-film",
        items: [
            { type: "image",      name: "Image",        icon: "fa-solid fa-image",     color: "#6366f1" },
            { type: "video",      name: "Video Embed",  icon: "fa-solid fa-film",      color: "#ef4444" },
            { type: "icon-block", name: "Icon Block",   icon: "fa-solid fa-icons",     color: "#f59e0b" },
        ],
    },
    {
        name: "Shapes & Layout", icon: "fa-solid fa-shapes",
        items: [
            { type: "rectangle",    name: "Rectangle",    icon: "fa-regular fa-square",            color: "#6366f1" },
            { type: "circle",       name: "Circle",       icon: "fa-regular fa-circle",            color: "#ec4899" },
            { type: "triangle",     name: "Triangle",     icon: "fa-solid fa-play fa-rotate-270",  color: "#f59e0b" },
            { type: "star",         name: "Star Shape",   icon: "fa-regular fa-star",              color: "#f97316" },
            { type: "line",         name: "Line",         icon: "fa-solid fa-minus",               color: "#94a3b8" },
            { type: "arrow",        name: "Arrow",        icon: "fa-solid fa-arrow-right",         color: "#6366f1" },
            { type: "divider",      name: "Divider",      icon: "fa-solid fa-grip-lines",          color: "#e2e8f0" },
            { type: "spacer",       name: "Spacer",       icon: "fa-solid fa-arrows-up-down",      color: "#94a3b8" },
        ],
    },
    {
        name: "Cards & Components", icon: "fa-solid fa-id-card",
        items: [
            { type: "social-card",  name: "Profile Card",   icon: "fa-solid fa-id-badge",       color: "#6366f1" },
            { type: "testimonial",  name: "Testimonial",    icon: "fa-solid fa-comment-dots",   color: "#8b5cf6" },
            { type: "kanban",       name: "Kanban Card",    icon: "fa-solid fa-thumbtack",      color: "#f97316" },
            { type: "price-card",   name: "Pricing Card",   icon: "fa-solid fa-dollar-sign",    color: "#10b981" },
            { type: "feature-card", name: "Feature Card",   icon: "fa-solid fa-star-of-life",   color: "#6366f1" },
            { type: "alert-card",   name: "Alert Card",     icon: "fa-solid fa-bell",           color: "#ef4444" },
        ],
    },
    {
        name: "Report Elements", icon: "fa-solid fa-file-lines",
        items: [
            { type: "pagenum",    name: "Page Number",   icon: "fa-solid fa-hashtag",          color: "#94a3b8" },
            { type: "date",       name: "Current Date",  icon: "fa-solid fa-calendar-days",    color: "#64748b" },
            { type: "signature",  name: "Signature",     icon: "fa-solid fa-signature",        color: "#334155" },
            { type: "watermark",  name: "Watermark",     icon: "fa-solid fa-droplet",          color: "#94a3b8" },
        ],
    },
];

// ─── Props ───────────────────────────────────────────────────────────────────
const props = defineProps({ report: Object });

// ─── State ───────────────────────────────────────────────────────────────────
const pages = ref(
    props.report?.content?.length
        ? props.report.content
        : [{ id: uuidv4(), label: "Page 1", elements: [] }]
);
const selectedElement   = ref(null);
const selectedPageIndex = ref(0);
const zoom              = ref(100);
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
const showStatusMenu    = ref(false);
const exportMenuRef     = ref(null);
const statusMenuRef     = ref(null);
const reportStatus      = ref(props.report?.status || "draft");
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
    "w-full text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all"
);

const statusDot = computed(() => ({
    draft: "bg-amber-400",
    published: "bg-emerald-500",
    archived: "bg-slate-400",
}[reportStatus.value] || "bg-amber-400"));

// ─── Element factory ─────────────────────────────────────────────────────────
const createDefaultElement = (type, x = 60, y = 60) => {
    const base = {
        id: uuidv4(),
        type,
        position: { x, y },
        styles: { width: 200, height: 50, zIndex: 1, opacity: 100 },
    };
    const P = rs.primary_color;
    const presets = {
        heading:    { content: "Report Heading", styles: { ...base.styles, width: 460, height: 64, fontSize: 32, fontWeight: "700", color: "#0f172a" } },
        subheading: { content: "Section Title",  styles: { ...base.styles, width: 340, height: 44, fontSize: 20, fontWeight: "600", color: "#1e293b" } },
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
        date:       { styles: { ...base.styles, width: 220, height: 30, fontSize: 13, color: "#64748b" } },
        pagenum:    { styles: { ...base.styles, width: 100, height: 24, fontSize: 12, textAlign: "center", color: "#94a3b8" } },
        signature:  { label: "Authorised Signature", styles: { ...base.styles, width: 260, height: 80 } },
        toc:        { title: "Table of Contents", items: [{ label: "Section 1", page: 1 }, { label: "Section 2", page: 3 }], styles: { ...base.styles, width: 360, height: 160, fontSize: 12, color: "#334155" } },
        timeline:   { items: [{ label: "Milestone 1", date: "2024 Q1", desc: "Kickoff" }, { label: "Milestone 2", date: "2024 Q3", desc: "Launch" }], styles: { ...base.styles, width: 340, height: 200 } },
        image:      { src: "", alt: "", styles: { ...base.styles, width: 320, height: 220, borderRadius: 8 } },
        table:      { columns: ["Name", "Value", "Change"], data: [{ Name: "Revenue", Value: "$1.2M", Change: "+12%" }, { Name: "Users", Value: "8,400", Change: "+5%" }], styles: { ...base.styles, width: 460, height: 130, headerBg: P, headerColor: "#fff", evenRowBg: "#fff", oddRowBg: "#f8fafc" } },
        "bar-chart":         { chartData: { labels: ["Q1","Q2","Q3","Q4"], values: [42,68,55,81] }, chartTitle: "Quarterly Performance", chartColor: P, legendPosition: "bottom", styles: { ...base.styles, width: 400, height: 280 } },
        "line-chart":        { chartData: { labels: ["Jan","Feb","Mar","Apr","May","Jun"], values: [30,48,36,70,60,85] }, chartTitle: "Trend Analysis", chartColor: "#3b82f6", legendPosition: "bottom", styles: { ...base.styles, width: 400, height: 280 } },
        "pie-chart":         { chartData: { labels: ["Cat A","Cat B","Cat C","Cat D"], values: [35,25,22,18] }, chartTitle: "Distribution", legendPosition: "bottom", pieColors: ["#6366f1","#10b981","#f59e0b","#ef4444"], styles: { ...base.styles, width: 320, height: 300 } },
        "doughnut-chart":    { chartData: { labels: ["Seg A","Seg B","Seg C"], values: [40,35,25] }, chartTitle: "Composition", legendPosition: "bottom", pieColors: ["#6366f1","#10b981","#f59e0b"], styles: { ...base.styles, width: 320, height: 300 } },
        "area-chart":        { chartData: { labels: ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"], values: [20,45,38,60,55,72,80] }, chartTitle: "Weekly Overview", chartColor: "#8b5cf6", legendPosition: "bottom", styles: { ...base.styles, width: 400, height: 280 } },
        "radar-chart":       { chartData: { labels: ["Speed","Accuracy","Efficiency","Quality","Innovation"], values: [80,92,75,88,70] }, chartTitle: "Performance", chartColor: "#14b8a6", legendPosition: "bottom", styles: { ...base.styles, width: 340, height: 320 } },
        metric:              { label: "Total Revenue", value: "$48,293", change: "12.5%", changeType: "positive", changePeriod: "vs last month", styles: { ...base.styles, width: 240, height: 120, backgroundColor: "#f8fafc", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 14 } },
        progress:            { label: "Completion Rate", value: 72, styles: { ...base.styles, width: 340, height: 50, color: P, trackColor: "#e2e8f0", trackHeight: 8 } },
        "circular-progress": { label: "Progress", value: 72, styles: { ...base.styles, width: 180, height: 200, color: P, trackColor: "#e2e8f0" } },
        "stat-row":          { stats: [{ label: "Revenue", value: "$48K" }, { label: "Users", value: "8.4K" }, { label: "Growth", value: "+12%" }], styles: { ...base.styles, width: 460, height: 100 } },
        rectangle:           { styles: { ...base.styles, width: 200, height: 120, backgroundColor: "#e0e7ff", borderRadius: 10 } },
        circle:              { styles: { ...base.styles, width: 100, height: 100, backgroundColor: P } },
        triangle:            { styles: { ...base.styles, width: 120, height: 120, backgroundColor: "#f59e0b" } },
        star:                { styles: { ...base.styles, width: 80,  height: 80,  backgroundColor: P } },
        line:                { styles: { ...base.styles, width: 240, height: 20, borderWidth: 2, color: "#cbd5e1" } },
        arrow:               { styles: { ...base.styles, width: 200, height: 30, borderWidth: 2, color: P } },
        divider:             { styles: { ...base.styles, width: 460, height: 20, borderWidth: 1, borderStyle: "solid", color: "#e2e8f0" } },
        spacer:              { styles: { ...base.styles, width: 200, height: 40 } },
        "social-card":       { content: "Name Here", subtitle: "Title", avatar: "👤", styles: { ...base.styles, width: 240, height: 200, backgroundColor: "#fff", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 16 } },
        testimonial:         { content: "Amazing product!", author: "John Doe", role: "CEO", styles: { ...base.styles, width: 300, height: 180, backgroundColor: "#f8fafc", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 16 } },
        kanban:              { content: "Task Title", status: "In Progress", due: "Dec 31", styles: { ...base.styles, width: 220, height: 100, backgroundColor: "#fff", borderColor: "#e2e8f0", accentColor: P } },
        "price-card":        { plan: "Pro Plan", price: "$29", period: "/month", features: ["Feature 1","Feature 2","Feature 3"], styles: { ...base.styles, width: 220, height: 280, backgroundColor: "#fff", borderColor: "#e2e8f0", borderWidth: 1, borderRadius: 16 } },
        "feature-card":      { content: "Feature Name", subtitle: "Description", emoji: "🚀", styles: { ...base.styles, width: 200, height: 160, backgroundColor: "#f8fafc", borderRadius: 16, borderWidth: 1, borderColor: "#e2e8f0" } },
        "alert-card":        { content: "Alert message here", styles: { ...base.styles, width: 380, height: 80, backgroundColor: "#fef2f2", borderRadius: 12, borderWidth: 1, borderColor: "#fecaca" } },
        watermark:           { content: "CONFIDENTIAL", styles: { ...base.styles, width: 400, height: 120, opacity: 20, fontSize: 48, color: "#94a3b8", rotate: -30 } },
    };
    const d = presets[type] || {};
    return { ...base, ...d, styles: { ...base.styles, ...(d.styles || {}) } };
};

// ─── Page template builder ────────────────────────────────────────────────────
const newBlankPage = (index) => ({
    id: uuidv4(),
    label: `Page ${index + 1}`,
    elements: [],
    _template: {
        background_color: rs.background_color,
        primary_color: rs.primary_color,
        font_family: rs.font_family,
        page_size: rs.page_size,
        orientation: rs.orientation,
    }
});

// ─── Element operations ───────────────────────────────────────────────────────
const addElement = (type) => {
    const pi = selectedPageIndex.value ?? 0;
    addElementAtPosition(type, pi, 60, 60 + (pages.value[pi].elements.length * 15));
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

// ─── Canvas drag ──────────────────────────────────────────────────────────────
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
const onTextBlur       = (el, e) => { el.content = e.target.innerHTML || e.target.innerText; pushHistory(); };
const onListItemBlur   = (el, i, e) => { el.items[i] = e.target.innerText; pushHistory(); };
const addListItem      = (el) => { el.items.push("New item"); pushHistory(); };
const onChecklistItemBlur = (el, i, e) => { el.items[i].text = e.target.innerText; pushHistory(); };
const addChecklistItem = (el) => { el.items.push({ text: "New task", checked: false }); pushHistory(); };
const onTableCellBlur  = (el, ri, ci, e) => { el.data[ri][el.columns[ci]] = e.target.innerText; pushHistory(); };
const onTableHeaderBlur = (el, col, e) => {
    const newName = e.target.innerText.trim() || col;
    if (newName !== col) {
        const idx = el.columns.indexOf(col);
        if (idx > -1) { el.columns[idx] = newName; el.data.forEach(r => { r[newName] = r[col]; delete r[col]; }); }
    }
    pushHistory();
};
const addTableRow    = (el) => { const r = {}; el.columns.forEach(c => r[c] = ""); el.data.push(r); pushHistory(); };
const addTableColumn = (el) => { const n = `Col ${el.columns.length + 1}`; el.columns.push(n); el.data.forEach(r => r[n] = ""); pushHistory(); };
const removeTableRow    = (el) => { if (el.data.length > 1) { el.data.pop(); pushHistory(); } };
const removeTableColumn = (el) => { if (el.columns.length > 1) { const l = el.columns.pop(); el.data.forEach(r => delete r[l]); pushHistory(); } };
const onTocTitleBlur = (el, e) => { el.title = e.target.innerText; pushHistory(); };
const onTocItemBlur  = (el, i, field, e) => { el.items[i][field] = e.target.innerText; pushHistory(); };
const addTocItem     = (el) => { el.items.push({ label: `Section ${el.items.length + 1}`, page: 1 }); pushHistory(); };
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
        const primaryC = el.chartColor || rs.primary_color;
        const chart = new Chart(canvas.getContext("2d"), {
            type,
            data: {
                labels: el.chartData?.labels || [],
                datasets: [{
                    label: el.chartDatasetLabel || el.chartTitle || "Dataset",
                    data: el.chartData?.values || [],
                    backgroundColor: (type === "pie" || type === "doughnut") ? colors : (isArea ? primaryC + "30" : primaryC + "cc"),
                    borderColor: (type === "pie" || type === "doughnut") ? colors.map(c => c + "dd") : primaryC,
                    borderWidth: 2, fill: isArea, tension: (el.type === "line-chart" || isArea) ? 0.4 : 0,
                    pointBackgroundColor: primaryC, pointRadius: 4, pointHoverRadius: 6,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 600 },
                plugins: {
                    legend: { position: el.legendPosition === "none" ? false : (el.legendPosition || "bottom"), display: el.legendPosition !== "none", labels: { padding: 12, font: { size: 11 }, boxWidth: 12 } },
                    title: { display: !!el.chartTitle, text: el.chartTitle, font: { size: 13, weight: "600" }, color: "#1e293b" },
                    tooltip: { backgroundColor: "rgba(15,23,42,.9)", titleColor: "#f1f5f9", bodyColor: "#cbd5e1", cornerRadius: 8, padding: 10 },
                },
                scales: (type === "pie" || type === "doughnut" || type === "radar") ? {} : {
                    x: { grid: { color: "#f8fafc" }, ticks: { font: { size: 11 }, color: "#94a3b8" } },
                    y: { grid: { color: "#f1f5f9" }, ticks: { font: { size: 11 }, color: "#94a3b8" } },
                },
            },
        });
        chartInstances.set(el.id, chart);
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
    const file = e.target?.files?.[0];
    if (!file) return;
    if (!file.type.startsWith("image/")) { showToast("Only image files are allowed.", "error"); return; }
    const localUrl = URL.createObjectURL(file);
    if (el) { el.src = localUrl; el._uploading = true; }
    try {
        const fd = new FormData(); fd.append("image", file);
        const res = await axios.post("/api/upload-image", fd);
        if (el) { el.src = res.data.url; URL.revokeObjectURL(localUrl); }
        showToast("Image uploaded!"); pushHistory();
    } catch { showToast("Upload failed — using local preview.", "error"); }
    finally { if (el) el._uploading = false; if (e.target) e.target.value = ""; }
};

// ─── Page management ──────────────────────────────────────────────────────────
const addPageAfter = (i) => {
    const newPage = newBlankPage(pages.value.length);
    pages.value.splice(i + 1, 0, newPage);
    selectedPageIndex.value = i + 1;
    pushHistory();
    showToast("New page added");
};
const removePage = (i) => {
    if (pages.value.length <= 1) { showToast("Cannot delete the only page", "error"); return; }
    pages.value.splice(i, 1);
    if (selectedPageIndex.value >= pages.value.length) selectedPageIndex.value = pages.value.length - 1;
    pushHistory();
};
const duplicatePage = (i) => {
    const clone = JSON.parse(JSON.stringify(pages.value[i]));
    clone.id = uuidv4();
    clone.label = `${clone.label || `Page ${i + 1}`} (Copy)`;
    clone.elements = clone.elements.map(el => ({ ...el, id: uuidv4() }));
    pages.value.splice(i + 1, 0, clone);
    selectedPageIndex.value = i + 1;
    nextTick(() => clone.elements.forEach(el => { if (isChartType(el.type)) initChart(el); }));
    pushHistory();
    showToast("Page duplicated");
};

const toggleCategory = (name) => {
    const idx = collapsedCategories.value.indexOf(name);
    if (idx > -1) collapsedCategories.value.splice(idx, 1);
    else collapsedCategories.value.push(name);
};

// ─── Layer order ──────────────────────────────────────────────────────────────
const bringForward = () => { if (!selectedElement.value) return; selectedElement.value.styles.zIndex = (selectedElement.value.styles.zIndex || 1) + 1; pushHistory(); };
const sendBackward  = () => { if (!selectedElement.value) return; selectedElement.value.styles.zIndex = Math.max(1, (selectedElement.value.styles.zIndex || 1) - 1); pushHistory(); };
const bringToFront  = () => {
    if (!selectedElement.value) return;
    const maxZ = Math.max(...pages.value[selectedPageIndex.value].elements.map(e => e.styles?.zIndex || 1));
    selectedElement.value.styles.zIndex = maxZ + 1; pushHistory();
};
const sendToBack = () => {
    if (!selectedElement.value) return;
    const minZ = Math.min(...pages.value[selectedPageIndex.value].elements.map(e => e.styles?.zIndex || 1));
    selectedElement.value.styles.zIndex = Math.max(1, minZ - 1); pushHistory();
};

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
    if (history.value.length > 80) { history.value.shift(); historyIndex.value--; }
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

const updateStatus = async (status) => {
    showStatusMenu.value = false;
    try {
        await axios.patch(route("reports.status", props.report.slug), { status });
        reportStatus.value = status;
        showToast(`Status updated to ${status}`);
    } catch { showToast("Failed to update status", "error"); }
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
    contextMenu.x    = Math.min(e.clientX, window.innerWidth - 220);
    contextMenu.y    = Math.min(e.clientY, window.innerHeight - 320);
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
    if ((e.ctrlKey || e.metaKey) && (e.key === "y" || (e.shiftKey && e.key === "z"))) { e.preventDefault(); redo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === "s") { e.preventDefault(); saveReport(); }
    if ((e.ctrlKey || e.metaKey) && e.key === "d") { e.preventDefault(); if (selectedElement.value) duplicateElement(selectedPageIndex.value, selectedElement.value); }
    if (e.key === "d" && !e.ctrlKey && !e.metaKey) dragMode.value = !dragMode.value;
    if (e.key === "g" && !e.ctrlKey && !e.metaKey) showGrid.value = !showGrid.value;
    if (e.key === "r" && !e.ctrlKey && !e.metaKey) showRulers.value = !showRulers.value;
    if (e.key === "f" && !e.ctrlKey && !e.metaKey) toggleFullscreen();
    if (e.key === "Escape") { deselectAll(); contextMenu.show = false; showExportMenu.value = false; showStatusMenu.value = false; }
    if ((e.key === "Delete" || e.key === "Backspace") && selectedElement.value) { deleteSelectedElement(); }
    if (selectedElement.value && ["ArrowUp","ArrowDown","ArrowLeft","ArrowRight"].includes(e.key)) {
        e.preventDefault();
        const step = e.shiftKey ? 10 : 1;
        if (e.key === "ArrowUp")    selectedElement.value.position.y -= step;
        if (e.key === "ArrowDown")  selectedElement.value.position.y += step;
        if (e.key === "ArrowLeft")  selectedElement.value.position.x -= step;
        if (e.key === "ArrowRight") selectedElement.value.position.x += step;
        pushHistory();
    }
};

const handleOutsideClick = (e) => {
    if (exportMenuRef.value && !exportMenuRef.value.contains(e.target)) showExportMenu.value = false;
    if (statusMenuRef.value && !statusMenuRef.value.contains(e.target)) showStatusMenu.value = false;
    if (contextMenu.show && !e.target.closest(".context-menu")) contextMenu.show = false;
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
    document.addEventListener("click", handleOutsideClick);
    nextTick(reinitAllCharts);
});
onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeyDown);
    document.removeEventListener("click", handleOutsideClick);
    chartInstances.forEach(c => c.destroy());
    if (saveTimeout) clearTimeout(saveTimeout);
});

// ─── Provide to children ──────────────────────────────────────────────────────
provide("isChartType",        isChartType);
provide("initChart",          initChart);
provide("createDefaultElement", createDefaultElement);
provide("CALLOUT_PRESETS",    CALLOUT_PRESETS);
provide("FONTS",              FONTS);
provide("FONT_WEIGHTS",       FONT_WEIGHTS);
provide("PAGE_SIZES",         PAGE_SIZES);
provide("SHORTCUTS",          SHORTCUTS);
provide("QUICK_ADD",          QUICK_ADD);
provide("ELEMENT_CATEGORIES", ELEMENT_CATEGORIES);
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


<!-- give the code in artifact
carefully analyze the project code, the Pages/report/editor.vue is splitted into components 
the dynamic report generator is must be modern and proffesional and when the item is selected from left side bar then the related all possible setting is not showing on right sidebar ,
Add more editable items like modern charts ,graphs,list,tables,carts,  whose values,styles fully editableand changeable and smore then 50 using fontawsome icons
what are the errors and what to be done for there 
give solutions to problems and for files first give the code in artifact for pages/reports/editor.vue 
 nice  now with out changing any thing use fontawsom icon and add all setings related to pages ,font,fontsize,family,header,footer settings,page number settings and all other settings and also remove the dark mode functions but do not remove the drark mode style classes from tags  as i have add drag mode toggle buutton on my header component and when new page is created then the new page must be according to the selected template AND keep reponsive design in mind while adding new features and components. and dont remove any features from the existing code just add new features and components as per the requirements and also make sure that all the features are working properly and there are no bugs in the code.

in the left sidebar the items are categorized into sections like "Basic Elements", "Charts", "Media", "Data Visualization", "Layout", and "Cards". Each section has a list of elements that can be added to the report. When an element is selected, its related settings should be displayed in the right sidebar for editing.
must contain all the settings related to page number, color, font side ,fotor,header,footer and all other settings related to the report and also add fontawsome icons for each setting in the right sidebar to make it more user friendly and modern.
also add page layer settings and options to manage layers of elements on the page like bring to front, send to back, align left, align right, align center etc with fontawsome icons for each action in the right sidebar when an element is selected.
make sure in leftside bar under page section when click on any page that page must be active and its elements should be displayed on the canvas for editing and also the settings of that page should be displayed in the right sidebar when that page is selected.
remove dark mode toggle function but keep the dark mode styles in place so that it can be toggled from header component and also make sure that when new page is created then it should follow the selected template settings and also make sure that all the features are working properly and there are no bugs in the code.
make sure when new page is created then it should follow the selected template settings and also make sure that all the features are working properly and there are no bugs in the code. -->



<!-- give the code in artifact
create modern and proffessional dynamic report generator dashboard with modern charts,graphs and also add functionality to dashboard for publish,darft,delete,duplicate and also add functionality to share the report with other users and also add functionality to export the report in different formats like pdf, excel, csv etc and also add functionality to set permissions for the report and also add functionality to track the changes made to the report and also add functionality to revert back to previous versions of the report and also add functionality to collaborate with other users in real time on the same report and
keep optimize and responsive design in mind while adding new features and components. and also make sure that all the features are working properly and there are no bugs in the code. -->~