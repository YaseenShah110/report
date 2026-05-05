<template>
    <div
        class="editor-shell"
        :class="{ dark: isDark, fullscreen: isFullscreen }"
        @keydown.stop="handleKeyboard"
        @click="closeAllMenus"
        tabindex="0"
        ref="editorShell"
    >
        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- TOP TOOLBAR                                                 -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button
                    class="icon-btn"
                    @click="goBack"
                    title="Back to Reports"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M19 12H5M12 5l-7 7 7 7" />
                    </svg>
                </button>
                <div class="divider-v"></div>
                <div class="doc-title-wrap">
                    <input
                        v-model="report.title"
                        @change="markDirty"
                        class="doc-title-input"
                        placeholder="Untitled Report"
                        spellcheck="false"
                    />
                    <span class="status-pill" :class="report.status">{{
                        report.status
                    }}</span>
                </div>
                <div
                    class="save-indicator"
                    :class="{ saving: saving, saved: lastSaved && !isDirty }"
                >
                    <span v-if="saving">Saving…</span>
                    <span v-else-if="lastSaved && !isDirty"
                        >Saved {{ lastSaved }}</span
                    >
                    <span v-else-if="isDirty">Unsaved</span>
                </div>
            </div>

            <div class="top-bar-center">
                <!-- Undo/Redo -->
                <button
                    class="icon-btn"
                    @click="undo"
                    :disabled="!canUndo"
                    title="Undo (Ctrl+Z)"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M3 7v6h6" />
                        <path d="M21 17a9 9 0 00-9-9 9 9 0 00-6 2.3L3 13" />
                    </svg>
                </button>
                <button
                    class="icon-btn"
                    @click="redo"
                    :disabled="!canRedo"
                    title="Redo (Ctrl+Y)"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M21 7v6h-6" />
                        <path d="M3 17a9 9 0 019-9 9 9 0 016 2.3l3 2.7" />
                    </svg>
                </button>
                <div class="divider-v"></div>

                <!-- Zoom -->
                <button class="icon-btn" @click="zoomOut" title="Zoom Out">
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                        <path d="M8 11h6" />
                    </svg>
                </button>
                <button
                    class="zoom-select"
                    @click="cycleZoom"
                    title="Reset Zoom"
                >
                    {{ zoom }}%
                </button>
                <button class="icon-btn" @click="zoomIn" title="Zoom In">
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                        <path d="M11 8v6M8 11h6" />
                    </svg>
                </button>
                <div class="divider-v"></div>

                <!-- Grid toggle -->
                <button
                    class="icon-btn"
                    :class="{ active: showGrid }"
                    @click="showGrid = !showGrid"
                    title="Toggle Grid"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                    </svg>
                </button>

                <!-- Snap toggle -->
                <button
                    class="icon-btn"
                    :class="{ active: snapToGrid }"
                    @click="snapToGrid = !snapToGrid"
                    title="Snap to Grid"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"
                        />
                    </svg>
                </button>

                <!-- Rulers toggle -->
                <button
                    class="icon-btn"
                    :class="{ active: showRulers }"
                    @click="showRulers = !showRulers"
                    title="Show Rulers"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M3 6l18 0M3 6l0 12 18 0 0-12" />
                        <path d="M7 6v4M11 6v6M15 6v4M19 6v6" />
                    </svg>
                </button>

                <div class="divider-v"></div>

                <!-- Comments toggle -->
                <button
                    class="icon-btn"
                    :class="{ active: showComments }"
                    @click="showComments = !showComments"
                    title="Comments"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"
                        />
                    </svg>
                </button>

                <!-- AI Assistant -->
                <button
                    class="icon-btn ai-btn"
                    :class="{ active: showAI }"
                    @click="showAI = !showAI"
                    title="AI Assistant (Ctrl+Space)"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4M12 16h.01" />
                    </svg>
                    AI
                </button>
            </div>

            <div class="top-bar-right">
                <button class="btn-secondary" @click="previewReport">
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                        />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    Preview
                </button>

                <!-- Export dropdown -->
                <div class="dropdown-wrap" @click.stop>
                    <button
                        class="btn-primary"
                        @click="showExportMenu = !showExportMenu"
                    >
                        <svg
                            width="13"
                            height="13"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        Export
                        <svg
                            width="10"
                            height="10"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div
                        v-if="showExportMenu"
                        class="dropdown-menu export-menu"
                    >
                        <button @click="downloadPDF" class="dropdown-item">
                            <span class="item-icon pdf">PDF</span> Export as PDF
                        </button>
                        <button @click="exportImage" class="dropdown-item">
                            <span class="item-icon img">PNG</span> Export as
                            Image
                        </button>
                        <button @click="exportCSV" class="dropdown-item">
                            <span class="item-icon csv">CSV</span> Export Data
                            (CSV)
                        </button>
                        <button @click="exportExcel" class="dropdown-item">
                            <span class="item-icon xls">XLS</span> Export as
                            Excel
                        </button>
                        <hr class="menu-sep" />
                        <button @click="shareReport" class="dropdown-item">
                            <svg
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="18" cy="5" r="3" />
                                <circle cx="6" cy="12" r="3" />
                                <circle cx="18" cy="19" r="3" />
                                <line
                                    x1="8.59"
                                    y1="13.51"
                                    x2="15.42"
                                    y2="17.49"
                                />
                                <line
                                    x1="15.41"
                                    y1="6.51"
                                    x2="8.59"
                                    y2="10.49"
                                />
                            </svg>
                            Share Link
                        </button>
                    </div>
                </div>

                <!-- Version history -->
                <button
                    class="icon-btn"
                    @click="showVersions = !showVersions"
                    title="Version History"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </button>

                <!-- Dark mode -->
                <button
                    class="icon-btn"
                    @click="toggleDark"
                    :title="isDark ? 'Light Mode' : 'Dark Mode'"
                >
                    <svg
                        v-if="isDark"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="5" />
                        <path
                            d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"
                        />
                    </svg>
                    <svg
                        v-else
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                </button>

                <!-- Fullscreen -->
                <button
                    class="icon-btn"
                    @click="toggleFullscreen"
                    :title="
                        isFullscreen ? 'Exit Fullscreen' : 'Fullscreen (F11)'
                    "
                >
                    <svg
                        v-if="!isFullscreen"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M8 3H5a2 2 0 00-2 2v3M16 3h3a2 2 0 012 2v3M21 16v3a2 2 0 01-2 2h-3M3 16v3a2 2 0 002 2h3"
                        />
                    </svg>
                    <svg
                        v-else
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M8 3v3a2 2 0 01-2 2H3M21 8h-3a2 2 0 01-2-2V3M3 16h3a2 2 0 012 2v3M16 21v-3a2 2 0 012-2h3"
                        />
                    </svg>
                </button>
            </div>
        </header>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- FORMAT RIBBON                                               -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="ribbon">
            <!-- Text formatting -->
            <div class="ribbon-group">
                <select
                    class="ribbon-select font-select"
                    v-model="fmt.fontFamily"
                    @change="applyStyle('fontFamily', fmt.fontFamily)"
                    title="Font Family"
                >
                    <option v-for="f in fonts" :key="f" :value="f">
                        {{ f }}
                    </option>
                </select>
                <select
                    class="ribbon-select size-select"
                    v-model.number="fmt.fontSize"
                    @change="applyStyle('fontSize', fmt.fontSize)"
                    title="Font Size"
                >
                    <option v-for="s in fontSizes" :key="s" :value="s">
                        {{ s }}
                    </option>
                </select>
            </div>

            <div class="divider-v"></div>

            <div class="ribbon-group">
                <button
                    class="ribbon-btn"
                    :class="{ active: fmt.fontWeight === '700' }"
                    @click="toggleFmt('fontWeight', '700', '400')"
                    title="Bold (Ctrl+B)"
                >
                    <b>B</b>
                </button>
                <button
                    class="ribbon-btn italic-btn"
                    :class="{ active: fmt.fontStyle === 'italic' }"
                    @click="toggleFmt('fontStyle', 'italic', 'normal')"
                    title="Italic (Ctrl+I)"
                >
                    <i>I</i>
                </button>
                <button
                    class="ribbon-btn"
                    :class="{ active: fmt.textDecoration === 'underline' }"
                    @click="toggleFmt('textDecoration', 'underline', 'none')"
                    title="Underline (Ctrl+U)"
                >
                    <u>U</u>
                </button>
                <button
                    class="ribbon-btn strikethrough"
                    :class="{ active: fmt.textDecoration === 'line-through' }"
                    @click="toggleFmt('textDecoration', 'line-through', 'none')"
                    title="Strikethrough"
                >
                    <s>S</s>
                </button>
            </div>

            <div class="divider-v"></div>

            <!-- Text color + bg color -->
            <div class="ribbon-group">
                <div class="color-picker-wrap" title="Text Color">
                    <button class="ribbon-btn color-btn">
                        <span
                            class="color-swatch text-swatch"
                            :style="{ background: fmt.color }"
                            >A</span
                        >
                    </button>
                    <input
                        type="color"
                        class="color-hidden"
                        :value="fmt.color"
                        @input="
                            applyStyle('color', $event.target.value);
                            fmt.color = $event.target.value;
                        "
                    />
                </div>
                <div class="color-picker-wrap" title="Background Color">
                    <button class="ribbon-btn color-btn">
                        <span
                            class="color-swatch bg-swatch"
                            :style="{ background: fmt.backgroundColor }"
                        >
                            <svg
                                width="12"
                                height="12"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M20.71 5.63l-2.34-2.34a1 1 0 00-1.41 0l-3.12 3.12-1.41-1.42-1.42 1.42 1.41 1.41-6.6 6.6A2 2 0 005 16v3h3a2 2 0 001.42-.59l6.6-6.6 1.41 1.42 1.42-1.42-1.42-1.41 3.12-3.12a1 1 0 000-1.65z"
                                />
                            </svg>
                        </span>
                    </button>
                    <input
                        type="color"
                        class="color-hidden"
                        :value="
                            fmt.backgroundColor === 'transparent'
                                ? '#ffffff'
                                : fmt.backgroundColor
                        "
                        @input="
                            applyStyle('backgroundColor', $event.target.value);
                            fmt.backgroundColor = $event.target.value;
                        "
                    />
                </div>
            </div>

            <div class="divider-v"></div>

            <!-- Alignment -->
            <div class="ribbon-group">
                <button
                    class="ribbon-btn"
                    :class="{ active: fmt.textAlign === 'left' }"
                    @click="applyStyle('textAlign', 'left')"
                    title="Align Left"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="15" y2="12" />
                        <line x1="3" y1="18" x2="18" y2="18" />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    :class="{ active: fmt.textAlign === 'center' }"
                    @click="applyStyle('textAlign', 'center')"
                    title="Center"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="6" y1="12" x2="18" y2="12" />
                        <line x1="4" y1="18" x2="20" y2="18" />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    :class="{ active: fmt.textAlign === 'right' }"
                    @click="applyStyle('textAlign', 'right')"
                    title="Align Right"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="9" y1="12" x2="21" y2="12" />
                        <line x1="6" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    :class="{ active: fmt.textAlign === 'justify' }"
                    @click="applyStyle('textAlign', 'justify')"
                    title="Justify"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="divider-v"></div>

            <!-- Layer controls -->
            <div class="ribbon-group">
                <button
                    class="ribbon-btn"
                    @click="bringToFront"
                    title="Bring to Front"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="3" width="13" height="13" />
                        <rect
                            x="8"
                            y="8"
                            width="13"
                            height="13"
                            fill="var(--bg-primary)"
                        />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    @click="sendToBack"
                    title="Send to Back"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="8" y="8" width="13" height="13" />
                        <rect
                            x="3"
                            y="3"
                            width="13"
                            height="13"
                            fill="var(--bg-primary)"
                        />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    @click="groupSelected"
                    :disabled="!multiSelected"
                    title="Group (Ctrl+G)"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="2" y="2" width="8" height="8" />
                        <rect x="14" y="2" width="8" height="8" />
                        <rect x="2" y="14" width="8" height="8" />
                        <rect x="14" y="14" width="8" height="8" />
                        <rect
                            x="1"
                            y="1"
                            width="22"
                            height="22"
                            rx="2"
                            stroke-dasharray="4 2"
                        />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    @click="lockElement"
                    :class="{ active: selectedEl?.locked }"
                    title="Lock Element"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0110 0v4" />
                    </svg>
                </button>
            </div>

            <div class="divider-v"></div>

            <!-- Distribute / Align -->
            <div class="ribbon-group">
                <button
                    class="ribbon-btn"
                    @click="alignElements('left')"
                    :disabled="!multiSelected"
                    title="Align Left Edges"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="3" y1="4" x2="3" y2="20" />
                        <rect x="5" y="6" width="10" height="4" rx="1" />
                        <rect x="5" y="14" width="14" height="4" rx="1" />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    @click="alignElements('center-h')"
                    :disabled="!multiSelected"
                    title="Center Horizontally"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="12" y1="4" x2="12" y2="20" />
                        <rect x="7" y="6" width="10" height="4" rx="1" />
                        <rect x="4" y="14" width="16" height="4" rx="1" />
                    </svg>
                </button>
                <button
                    class="ribbon-btn"
                    @click="distributeElements('h')"
                    :disabled="!multiSelected"
                    title="Distribute Horizontally"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="3" y1="4" x2="3" y2="20" />
                        <line x1="21" y1="4" x2="21" y2="20" />
                        <rect x="8" y="8" width="8" height="8" rx="1" />
                    </svg>
                </button>
            </div>

            <div class="ribbon-spacer"></div>

            <!-- Quick actions -->
            <div class="ribbon-group">
                <button
                    class="ribbon-btn danger"
                    @click="deleteSelected"
                    :disabled="!selectedEl"
                    title="Delete (Del)"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14H6L5 6" />
                        <path d="M10 11v6M14 11v6M9 6V4h6v2" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- MAIN EDITOR BODY                                            -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="editor-body">
            <!-- LEFT PANEL -->
            <aside class="left-panel" :class="{ collapsed: leftCollapsed }">
                <div
                    class="panel-collapse-btn"
                    @click="leftCollapsed = !leftCollapsed"
                    :title="leftCollapsed ? 'Expand Panel' : 'Collapse Panel'"
                >
                    <svg
                        width="12"
                        height="12"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <polyline
                            :points="
                                leftCollapsed
                                    ? '9 18 15 12 9 6'
                                    : '15 18 9 12 15 6'
                            "
                        />
                    </svg>
                </div>

                <div class="panel-tabs" v-if="!leftCollapsed">
                    <button
                        v-for="tab in leftTabs"
                        :key="tab.id"
                        class="panel-tab"
                        :class="{ active: activeLeftTab === tab.id }"
                        @click="activeLeftTab = tab.id"
                        :title="tab.label"
                    >
                        <span v-html="tab.icon"></span>
                        <span class="tab-label">{{ tab.label }}</span>
                    </button>
                </div>

                <div class="panel-content" v-if="!leftCollapsed">
                    <!-- ── ELEMENTS TAB ── -->
                    <div
                        v-show="activeLeftTab === 'elements'"
                        class="elements-tab"
                    >
                        <div class="search-wrap">
                            <svg
                                width="12"
                                height="12"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="11" cy="11" r="8" />
                                <path d="M21 21l-4.35-4.35" />
                            </svg>
                            <input
                                v-model="elSearch"
                                class="search-input"
                                placeholder="Search elements…"
                            />
                        </div>

                        <div
                            v-for="cat in filteredElCats"
                            :key="cat.name"
                            class="el-category"
                        >
                            <div
                                class="cat-header"
                                @click="toggleCat(cat.name)"
                            >
                                <span>{{ cat.name }}</span>
                                <svg
                                    width="10"
                                    height="10"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <polyline
                                        :points="
                                            collapsedCats.includes(cat.name)
                                                ? '9 18 15 12 9 6'
                                                : '18 15 12 9 6 15'
                                        "
                                    />
                                </svg>
                            </div>
                            <div
                                v-if="!collapsedCats.includes(cat.name)"
                                class="el-grid"
                            >
                                <div
                                    v-for="el in cat.items"
                                    :key="el.type"
                                    class="el-item"
                                    draggable="true"
                                    @dragstart="onElDragStart($event, el)"
                                    @dblclick="addElementCenter(el)"
                                    :title="
                                        el.label +
                                        '\n(Double-click or drag to canvas)'
                                    "
                                >
                                    <span
                                        class="el-icon"
                                        v-html="el.icon"
                                    ></span>
                                    <span class="el-label">{{ el.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── PAGES TAB ── -->
                    <div v-show="activeLeftTab === 'pages'" class="pages-tab">
                        <button class="add-page-btn" @click="addPage">
                            <svg
                                width="12"
                                height="12"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            Add Page
                        </button>

                        <div class="pages-list">
                            <div
                                v-for="(page, pi) in report.content"
                                :key="page.id"
                                class="page-thumb"
                                :class="{ active: currentPage === pi }"
                                @click="goToPage(pi)"
                                @contextmenu.prevent="
                                    pageContextMenu($event, pi)
                                "
                            >
                                <div
                                    class="page-thumb-preview"
                                    :style="pageThumbStyle"
                                >
                                    <div
                                        v-for="el in page.elements.slice(0, 8)"
                                        :key="el.id"
                                        class="thumb-el"
                                        :style="thumbElStyle(el)"
                                    ></div>
                                </div>
                                <div class="page-thumb-info">
                                    <span class="page-num">{{ pi + 1 }}</span>
                                    <input
                                        class="page-label-input"
                                        :value="page.label || `Page ${pi + 1}`"
                                        @change="
                                            renamePage(pi, $event.target.value)
                                        "
                                        @click.stop
                                    />
                                    <div class="page-actions">
                                        <button
                                            class="micro-btn"
                                            @click.stop="duplicatePage(pi)"
                                            title="Duplicate"
                                        >
                                            <svg
                                                width="10"
                                                height="10"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <rect
                                                    x="9"
                                                    y="9"
                                                    width="13"
                                                    height="13"
                                                    rx="2"
                                                />
                                                <path
                                                    d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            class="micro-btn danger"
                                            @click.stop="deletePage(pi)"
                                            title="Delete"
                                            :disabled="
                                                report.content.length <= 1
                                            "
                                        >
                                            <svg
                                                width="10"
                                                height="10"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <polyline
                                                    points="3 6 5 6 21 6"
                                                />
                                                <path d="M19 6l-1 14H6L5 6" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── LAYERS TAB ── -->
                    <div v-show="activeLeftTab === 'layers'" class="layers-tab">
                        <div class="layers-header">
                            <span>Layers</span>
                            <span class="layer-count">{{
                                currentPageElements.length
                            }}</span>
                        </div>
                        <div class="layers-list">
                            <div
                                v-for="(el, ei) in [
                                    ...currentPageElements,
                                ].reverse()"
                                :key="el.id"
                                class="layer-item"
                                :class="{
                                    active:
                                        selectedElIdx ===
                                        currentPageElements.length - 1 - ei,
                                    locked: el.locked,
                                    hidden: el.hidden,
                                }"
                                @click="
                                    selectElementByIdx(
                                        currentPageElements.length - 1 - ei,
                                    )
                                "
                            >
                                <span
                                    class="layer-type-icon"
                                    v-html="getElIcon(el.type)"
                                ></span>
                                <span class="layer-name">{{
                                    el.content?.toString().substring(0, 20) ||
                                    el.type
                                }}</span>
                                <div class="layer-controls">
                                    <button
                                        class="micro-btn"
                                        @click.stop="el.hidden = !el.hidden"
                                        :title="el.hidden ? 'Show' : 'Hide'"
                                    >
                                        <svg
                                            width="10"
                                            height="10"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                v-if="!el.hidden"
                                                d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                            />
                                            <circle
                                                v-if="!el.hidden"
                                                cx="12"
                                                cy="12"
                                                r="3"
                                            />
                                            <path
                                                v-if="el.hidden"
                                                d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19M1 1l22 22"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        class="micro-btn"
                                        @click.stop="el.locked = !el.locked"
                                        :title="el.locked ? 'Unlock' : 'Lock'"
                                    >
                                        <svg
                                            width="10"
                                            height="10"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <rect
                                                x="3"
                                                y="11"
                                                width="18"
                                                height="11"
                                                rx="2"
                                            />
                                            <path
                                                v-if="el.locked"
                                                d="M7 11V7a5 5 0 0110 0v4"
                                            />
                                            <path
                                                v-else
                                                d="M7 11V7a5 5 0 019.9-1"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div
                                v-if="!currentPageElements.length"
                                class="layers-empty"
                            >
                                No elements on this page
                            </div>
                        </div>
                    </div>

                    <!-- ── MEDIA TAB ── -->
                    <div v-show="activeLeftTab === 'media'" class="media-tab">
                        <div
                            class="media-upload-zone"
                            @click="triggerImageUpload"
                            @dragover.prevent
                            @drop.prevent="handleMediaDrop"
                        >
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                            <span>Upload Image</span>
                            <span class="upload-hint">or drag & drop</span>
                        </div>
                        <input
                            ref="imageInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleImageUpload"
                            multiple
                        />
                        <div class="media-grid">
                            <div
                                v-for="img in uploadedImages"
                                :key="img.url"
                                class="media-item"
                                draggable="true"
                                @dragstart="onMediaDragStart($event, img)"
                                @dblclick="addImageToCanvas(img)"
                            >
                                <img :src="img.url" :alt="img.name" />
                                <span>{{ img.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ── TEMPLATES TAB ── -->
                    <div
                        v-show="activeLeftTab === 'templates'"
                        class="templates-tab"
                    >
                        <p class="tab-hint">
                            Click a template to apply its style to the current
                            page
                        </p>
                        <div class="template-grid" v-if="quickTemplates.length">
                            <div
                                v-for="tpl in quickTemplates"
                                :key="tpl.name"
                                class="template-card"
                                @click="applyQuickTemplate(tpl)"
                            >
                                <div
                                    class="tpl-preview"
                                    :style="{ background: tpl.gradient }"
                                ></div>
                                <span>{{ tpl.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- ── CENTER CANVAS ── -->
            <main
                class="canvas-area"
                @dragover.prevent
                @drop="onCanvasDrop"
                @click.self="deselectAll"
                @mousedown.self="startRubberBand"
                @mousemove="handleCanvasMouseMove"
                @mouseup="endRubberBand"
                @wheel.ctrl.prevent="handleZoomWheel"
                ref="canvasArea"
            >
                <!-- Ruler Top -->
                <div v-if="showRulers" class="ruler ruler-top">
                    <canvas
                        ref="rulerTopCanvas"
                        class="ruler-canvas-h"
                    ></canvas>
                </div>

                <!-- Ruler Left -->
                <div v-if="showRulers" class="ruler ruler-left">
                    <canvas
                        ref="rulerLeftCanvas"
                        class="ruler-canvas-v"
                    ></canvas>
                </div>

                <!-- Rubber band selection -->
                <div
                    v-if="rubberBand.active"
                    class="rubber-band"
                    :style="rubberBandStyle"
                ></div>

                <!-- Page container -->
                <div class="page-container" :style="containerStyle">
                    <div
                        ref="pageEl"
                        class="report-page"
                        :class="{ 'show-grid': showGrid }"
                        :style="pageStyle"
                        @mousedown.self="deselectAll"
                        @dblclick.self="onPageDblClick"
                    >
                        <!-- Header -->
                        <div
                            v-if="settings.show_header"
                            class="page-header-bar"
                            :style="headerBarStyle"
                        >
                            <span
                                :contenteditable="true"
                                @blur="
                                    settings.header_text =
                                        $event.target.textContent;
                                    markDirty();
                                "
                                >{{
                                    settings.header_text || "Report Header"
                                }}</span
                            >
                        </div>

                        <!-- Watermark -->
                        <div
                            v-if="settings.watermark"
                            class="watermark"
                            :style="watermarkStyle"
                        >
                            {{ settings.watermark }}
                        </div>

                        <!-- Elements -->
                        <div
                            v-for="(el, ei) in currentPageElements"
                            :key="el.id"
                            v-show="!el.hidden"
                            class="canvas-element"
                            :class="{
                                selected: selectedElIdx === ei,
                                'multi-selected': selectedEls.includes(ei),
                                locked: el.locked,
                                editing: editingElIdx === ei,
                                'priority-stripe': el.styles?.priority,
                            }"
                            :style="elementStyle(el)"
                            :data-el-id="el.id"
                            @mousedown.stop="onElementMouseDown($event, ei)"
                            @dblclick.stop="startEditing(ei)"
                            @contextmenu.prevent.stop="
                                showElContextMenu($event, ei)
                            "
                        >
                            <!-- Priority stripe at top -->
                            <div
                                v-if="el.styles?.priority"
                                class="priority-stripe-bar"
                                :style="{
                                    background:
                                        priorityColors[el.styles.priority] ||
                                        '#6366f1',
                                }"
                            ></div>

                            <!-- Resize handles -->
                            <template
                                v-if="
                                    (selectedElIdx === ei ||
                                        selectedEls.includes(ei)) &&
                                    !el.locked
                                "
                            >
                                <div
                                    v-for="h in resizeHandles"
                                    :key="h"
                                    class="resize-handle"
                                    :class="h"
                                    @mousedown.stop="startResize($event, ei, h)"
                                ></div>
                                <div
                                    class="rotate-handle"
                                    @mousedown.stop="startRotate($event, ei)"
                                    title="Rotate"
                                >
                                    <svg
                                        width="10"
                                        height="10"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0118.8-4.3M22 12.5a10 10 0 01-18.8 4.2"
                                        />
                                    </svg>
                                </div>
                            </template>

                            <!-- Element content -->
                            <div class="el-content" :style="elContentStyle(el)">
                                <!-- Text / Heading / Quote / Code -->
                                <div
                                    v-if="
                                        [
                                            'text',
                                            'heading',
                                            'subheading',
                                            'quote',
                                            'blockquote',
                                            'highlight',
                                            'badge',
                                            'link',
                                            'code',
                                        ].includes(el.type)
                                    "
                                    :contenteditable="editingElIdx === ei"
                                    class="text-content"
                                    :class="el.type"
                                    @input="
                                        el.content = $event.target.innerHTML;
                                        markDirty();
                                    "
                                    @blur="editingElIdx = null"
                                    v-html="
                                        el.content || getPlaceholder(el.type)
                                    "
                                ></div>

                                <!-- Image -->
                                <div
                                    v-else-if="el.type === 'image'"
                                    class="image-content"
                                    @click="
                                        editingElIdx === ei
                                            ? triggerImageReplace(el)
                                            : null
                                    "
                                >
                                    <img
                                        v-if="el.src"
                                        :src="el.src"
                                        :alt="el.alt || 'Image'"
                                        :style="{
                                            objectFit:
                                                el.styles?.objectFit || 'cover',
                                            borderRadius:
                                                (el.styles?.borderRadius || 0) +
                                                'px',
                                        }"
                                    />
                                    <div
                                        v-else
                                        class="image-placeholder"
                                        @click="triggerImagePick(el)"
                                    >
                                        <svg
                                            width="28"
                                            height="28"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <rect
                                                x="3"
                                                y="3"
                                                width="18"
                                                height="18"
                                                rx="2"
                                            />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline
                                                points="21 15 16 10 5 21"
                                            />
                                        </svg>
                                        <span>Click to add image</span>
                                    </div>
                                </div>

                                <!-- Table -->
                                <div
                                    v-else-if="el.type === 'table'"
                                    class="table-content"
                                >
                                    <table
                                        class="data-table"
                                        :style="tableStyle(el)"
                                    >
                                        <thead>
                                            <tr>
                                                <th
                                                    v-for="(
                                                        col, ci
                                                    ) in el.columns"
                                                    :key="ci"
                                                    :style="
                                                        tableHeaderStyle(el)
                                                    "
                                                    :contenteditable="
                                                        editingElIdx === ei
                                                    "
                                                    @blur="
                                                        el.columns[ci] =
                                                            $event.target.textContent;
                                                        markDirty();
                                                    "
                                                >
                                                    {{ col }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(row, ri) in el.data"
                                                :key="ri"
                                            >
                                                <td
                                                    v-for="(
                                                        col, ci
                                                    ) in el.columns"
                                                    :key="ci"
                                                    :contenteditable="
                                                        editingElIdx === ei
                                                    "
                                                    @blur="
                                                        el.data[ri][col] =
                                                            $event.target.textContent;
                                                        markDirty();
                                                    "
                                                >
                                                    {{ row[col] || "" }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Metric / KPI -->
                                <div
                                    v-else-if="el.type === 'metric'"
                                    class="metric-content"
                                    :style="metricStyle(el)"
                                >
                                    <div class="metric-label">
                                        {{ el.label || "Metric" }}
                                    </div>
                                    <div
                                        class="metric-value"
                                        :style="{
                                            color:
                                                el.styles?.valueColor ||
                                                settings.primary_color,
                                        }"
                                    >
                                        {{ el.value || "0" }}
                                    </div>
                                    <div
                                        v-if="el.change"
                                        class="metric-change"
                                        :class="el.changeType"
                                    >
                                        <svg
                                            width="10"
                                            height="10"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <polyline
                                                :points="
                                                    el.changeType === 'positive'
                                                        ? '18 15 12 9 6 15'
                                                        : '6 9 12 15 18 9'
                                                "
                                            />
                                        </svg>
                                        {{ el.change }}
                                    </div>
                                </div>

                                <!-- Progress bar -->
                                <div
                                    v-else-if="el.type === 'progress'"
                                    class="progress-content"
                                >
                                    <div class="progress-header">
                                        <span>{{ el.label || "Progress" }}</span
                                        ><span>{{ el.value || 0 }}%</span>
                                    </div>
                                    <div class="progress-track">
                                        <div
                                            class="progress-fill"
                                            :style="{
                                                width: (el.value || 0) + '%',
                                                background:
                                                    el.styles?.color ||
                                                    settings.primary_color,
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Chart placeholder -->
                                <div
                                    v-else-if="isChart(el.type)"
                                    class="chart-placeholder"
                                >
                                    <div class="chart-icon">
                                        <svg
                                            width="32"
                                            height="32"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <rect
                                                x="2"
                                                y="3"
                                                width="20"
                                                height="14"
                                                rx="2"
                                            />
                                            <line
                                                x1="8"
                                                y1="21"
                                                x2="16"
                                                y2="21"
                                            />
                                            <line
                                                x1="12"
                                                y1="17"
                                                x2="12"
                                                y2="21"
                                            />
                                        </svg>
                                    </div>
                                    <span class="chart-type">{{
                                        el.chartTitle ||
                                        el.type
                                            .replace("-chart", "")
                                            .toUpperCase() + " Chart"
                                    }}</span>
                                    <span class="chart-hint"
                                        >Double-click to configure</span
                                    >
                                </div>

                                <!-- Shapes -->
                                <div
                                    v-else-if="el.type === 'rectangle'"
                                    class="shape rect"
                                    :style="shapeStyle(el)"
                                ></div>
                                <div
                                    v-else-if="el.type === 'circle'"
                                    class="shape circle"
                                    :style="shapeStyle(el)"
                                ></div>
                                <div
                                    v-else-if="el.type === 'triangle'"
                                    class="shape triangle"
                                    :style="triangleStyle(el)"
                                ></div>
                                <div
                                    v-else-if="el.type === 'divider'"
                                    class="divider-line"
                                    :style="dividerStyle(el)"
                                ></div>
                                <div
                                    v-else-if="el.type === 'arrow'"
                                    class="arrow-shape"
                                    :style="arrowStyle(el)"
                                >
                                    <svg
                                        width="100%"
                                        height="100%"
                                        viewBox="0 0 200 40"
                                        preserveAspectRatio="none"
                                    >
                                        <defs>
                                            <marker
                                                id="arrow"
                                                markerWidth="8"
                                                markerHeight="8"
                                                refX="6"
                                                refY="3"
                                                orient="auto"
                                            >
                                                <path
                                                    d="M0,0 L0,6 L9,3 z"
                                                    :fill="
                                                        el.styles?.color ||
                                                        settings.primary_color
                                                    "
                                                />
                                            </marker>
                                        </defs>
                                        <line
                                            x1="0"
                                            y1="20"
                                            x2="190"
                                            y2="20"
                                            :stroke="
                                                el.styles?.color ||
                                                settings.primary_color
                                            "
                                            :stroke-width="
                                                el.styles?.strokeWidth || 2
                                            "
                                            marker-end="url(#arrow)"
                                        />
                                    </svg>
                                </div>

                                <!-- Callout -->
                                <div
                                    v-else-if="el.type === 'callout'"
                                    class="callout"
                                    :style="calloutStyle(el)"
                                >
                                    <span class="callout-emoji">{{
                                        el.emoji || "💡"
                                    }}</span>
                                    <div
                                        :contenteditable="editingElIdx === ei"
                                        @blur="
                                            el.content =
                                                $event.target.innerHTML;
                                            markDirty();
                                        "
                                        v-html="
                                            el.content || 'Add callout text…'
                                        "
                                    ></div>
                                </div>

                                <!-- Timeline -->
                                <div
                                    v-else-if="el.type === 'timeline'"
                                    class="timeline"
                                >
                                    <div
                                        v-for="(item, ti) in el.items || []"
                                        :key="ti"
                                        class="timeline-item"
                                    >
                                        <div
                                            class="tl-dot"
                                            :style="{
                                                background:
                                                    settings.primary_color,
                                            }"
                                        ></div>
                                        <div
                                            v-if="ti < el.items.length - 1"
                                            class="tl-line"
                                        ></div>
                                        <div class="tl-content">
                                            <div class="tl-date">
                                                {{ item.date }}
                                            </div>
                                            <div class="tl-title">
                                                {{ item.label }}
                                            </div>
                                            <div class="tl-desc">
                                                {{ item.desc }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Checklist -->
                                <div
                                    v-else-if="el.type === 'checklist'"
                                    class="checklist"
                                >
                                    <div
                                        v-for="(item, ci) in el.items || []"
                                        :key="ci"
                                        class="check-item"
                                    >
                                        <div
                                            class="check-box"
                                            :class="{ checked: item.checked }"
                                            @click="
                                                item.checked = !item.checked;
                                                markDirty();
                                            "
                                            :style="{
                                                borderColor:
                                                    settings.primary_color,
                                                background: item.checked
                                                    ? settings.primary_color
                                                    : '',
                                            }"
                                        >
                                            <svg
                                                v-if="item.checked"
                                                width="10"
                                                height="10"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="white"
                                                stroke-width="3"
                                            >
                                                <polyline
                                                    points="20 6 9 17 4 12"
                                                />
                                            </svg>
                                        </div>
                                        <span
                                            :class="{
                                                'checked-text': item.checked,
                                            }"
                                            >{{ item.text }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Signature -->
                                <div
                                    v-else-if="el.type === 'signature'"
                                    class="signature"
                                >
                                    <div class="sig-line"></div>
                                    <div
                                        :contenteditable="editingElIdx === ei"
                                        @blur="
                                            el.content =
                                                $event.target.textContent;
                                            markDirty();
                                        "
                                        class="sig-name"
                                    >
                                        {{ el.content || "Signature" }}
                                    </div>
                                    <div class="sig-title">
                                        {{ el.label || "Authorized Signature" }}
                                    </div>
                                </div>

                                <!-- Icon element -->
                                <div
                                    v-else-if="el.type === 'icon'"
                                    class="icon-el"
                                    :style="{
                                        fontSize:
                                            (el.styles?.fontSize || 48) + 'px',
                                        color:
                                            el.styles?.color ||
                                            settings.primary_color,
                                    }"
                                >
                                    {{ el.content || "⭐" }}
                                </div>

                                <!-- Rating -->
                                <div
                                    v-else-if="el.type === 'rating'"
                                    class="rating-el"
                                >
                                    <span
                                        v-for="i in 5"
                                        :key="i"
                                        class="star"
                                        :style="{
                                            color:
                                                i <= (el.value || 0)
                                                    ? el.styles?.color ||
                                                      '#f59e0b'
                                                    : '#cbd5e1',
                                        }"
                                        >★</span
                                    >
                                </div>

                                <!-- Page number / Date -->
                                <div
                                    v-else-if="el.type === 'pagenum'"
                                    class="pagenum-el"
                                >
                                    {{ currentPage + 1 }}
                                </div>
                                <div
                                    v-else-if="el.type === 'date-el'"
                                    class="date-el"
                                >
                                    {{
                                        new Date().toLocaleDateString("en-US", {
                                            year: "numeric",
                                            month: "long",
                                            day: "numeric",
                                        })
                                    }}
                                </div>

                                <!-- Testimonial -->
                                <div
                                    v-else-if="el.type === 'testimonial'"
                                    class="testimonial"
                                    :style="testimonialStyle(el)"
                                >
                                    <div class="quote-mark">"</div>
                                    <div class="testimonial-text">
                                        {{ el.content || "Great product!" }}
                                    </div>
                                    <div class="testimonial-author">
                                        {{ el.author || "Jane Doe" }}
                                    </div>
                                    <div class="testimonial-role">
                                        {{ el.role || "CEO" }}
                                    </div>
                                </div>

                                <!-- Stat Row -->
                                <div
                                    v-else-if="el.type === 'stat-row'"
                                    class="stat-row"
                                >
                                    <div
                                        v-for="(stat, si) in el.stats || []"
                                        :key="si"
                                        class="stat-item"
                                    >
                                        <div
                                            class="stat-value"
                                            :style="{
                                                color: settings.primary_color,
                                            }"
                                        >
                                            {{ stat.value }}
                                        </div>
                                        <div class="stat-label">
                                            {{ stat.label }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Fallback -->
                                <div v-else class="el-fallback">
                                    {{ el.type }}
                                </div>
                            </div>

                            <!-- Selection info bar -->
                            <div
                                v-if="selectedElIdx === ei && !el.locked"
                                class="el-info-bar"
                            >
                                {{ Math.round(el.position.x) }},
                                {{ Math.round(el.position.y) }} —
                                {{ Math.round(el.styles.width) }} ×
                                {{ Math.round(el.styles.height) }}
                            </div>
                        </div>

                        <!-- Drop zone hint -->
                        <div
                            v-if="isDraggingEl && !currentPageElements.length"
                            class="drop-hint"
                        >
                            <svg
                                width="40"
                                height="40"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            <span>Drop element here</span>
                        </div>

                        <!-- Footer -->
                        <div
                            v-if="settings.show_footer"
                            class="page-footer-bar"
                            :style="footerBarStyle"
                        >
                            <span
                                :contenteditable="true"
                                @blur="
                                    settings.footer_left =
                                        $event.target.textContent;
                                    markDirty();
                                "
                                >{{
                                    settings.footer_left || "Left Footer"
                                }}</span
                            >
                            <span>Page {{ currentPage + 1 }}</span>
                            <span
                                :contenteditable="true"
                                @blur="
                                    settings.footer_right =
                                        $event.target.textContent;
                                    markDirty();
                                "
                                >{{
                                    settings.footer_right || "Right Footer"
                                }}</span
                            >
                        </div>
                    </div>

                    <!-- Page label -->
                    <div class="page-label-below">
                        Page {{ currentPage + 1 }} of
                        {{ report.content.length }} —
                        {{ currentPageElements.length }} elements
                    </div>
                </div>

                <!-- AI Panel overlay -->
                <div v-if="showAI" class="ai-panel" @click.stop>
                    <div class="ai-header">
                        <span>✨ AI Assistant</span>
                        <button class="icon-btn" @click="showAI = false">
                            ✕
                        </button>
                    </div>
                    <div class="ai-messages" ref="aiMessages">
                        <div
                            v-for="(msg, i) in aiConvo"
                            :key="i"
                            class="ai-msg"
                            :class="msg.role"
                        >
                            <div class="msg-bubble">{{ msg.text }}</div>
                        </div>
                    </div>
                    <div class="ai-input-row">
                        <textarea
                            v-model="aiPrompt"
                            class="ai-input"
                            placeholder="Ask AI to generate content, write text, suggest charts…"
                            @keydown.enter.meta.prevent="sendAI"
                            rows="2"
                        ></textarea>
                        <button
                            class="ai-send-btn"
                            @click="sendAI"
                            :disabled="aiLoading"
                        >
                            <span v-if="aiLoading">…</span>
                            <svg
                                v-else
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                        </button>
                    </div>
                </div>
            </main>

            <!-- RIGHT PANEL -->
            <aside class="right-panel" :class="{ collapsed: rightCollapsed }">
                <div
                    class="panel-collapse-btn right-collapse"
                    @click="rightCollapsed = !rightCollapsed"
                    :title="rightCollapsed ? 'Expand' : 'Collapse'"
                >
                    <svg
                        width="12"
                        height="12"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <polyline
                            :points="
                                rightCollapsed
                                    ? '15 18 9 12 15 6'
                                    : '9 18 15 12 9 6'
                            "
                        />
                    </svg>
                </div>

                <div v-if="!rightCollapsed" class="right-panel-content">
                    <!-- No Selection State -->
                    <div v-if="!selectedEl" class="no-selection">
                        <div class="no-sel-icon">
                            <svg
                                width="32"
                                height="32"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                            </svg>
                        </div>
                        <p>Select an element to edit its properties</p>
                        <p class="hint-text">
                            Or drag elements from the left panel
                        </p>
                    </div>

                    <!-- Element Properties -->
                    <div v-else class="props-panel">
                        <div class="props-header">
                            <span
                                class="el-type-badge"
                                v-html="getElIcon(selectedEl.type)"
                            ></span>
                            <span class="el-type-name">{{
                                selectedEl.type
                            }}</span>
                            <button
                                v-if="selectedEl.locked"
                                class="lock-indicator"
                                @click="selectedEl.locked = false"
                                title="Unlock"
                            >
                                🔒
                            </button>
                        </div>

                        <!-- Props Tabs -->
                        <div class="props-tabs">
                            <button
                                v-for="t in propsTabs"
                                :key="t"
                                class="props-tab"
                                :class="{ active: activePropsTab === t }"
                                @click="activePropsTab = t"
                            >
                                {{ t }}
                            </button>
                        </div>

                        <!-- ── STYLE TAB ── -->
                        <div
                            v-show="activePropsTab === 'Style'"
                            class="props-section-list"
                        >
                            <!-- Position & Size -->
                            <div class="prop-section">
                                <div class="prop-section-title">
                                    Position & Size
                                </div>
                                <div class="prop-grid-2">
                                    <div class="prop-field">
                                        <label>X</label>
                                        <input
                                            type="number"
                                            :value="
                                                Math.round(
                                                    selectedEl.position.x,
                                                )
                                            "
                                            @input="
                                                selectedEl.position.x =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                    </div>
                                    <div class="prop-field">
                                        <label>Y</label>
                                        <input
                                            type="number"
                                            :value="
                                                Math.round(
                                                    selectedEl.position.y,
                                                )
                                            "
                                            @input="
                                                selectedEl.position.y =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                    </div>
                                    <div class="prop-field">
                                        <label>W</label>
                                        <input
                                            type="number"
                                            :value="
                                                Math.round(
                                                    selectedEl.styles.width,
                                                )
                                            "
                                            @input="
                                                selectedEl.styles.width =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                    </div>
                                    <div class="prop-field">
                                        <label>H</label>
                                        <input
                                            type="number"
                                            :value="
                                                Math.round(
                                                    selectedEl.styles.height,
                                                )
                                            "
                                            @input="
                                                selectedEl.styles.height =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Rotation</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="-180"
                                            max="180"
                                            :value="
                                                selectedEl.styles.rotate || 0
                                            "
                                            @input="
                                                selectedEl.styles.rotate =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <span
                                            >{{
                                                selectedEl.styles.rotate || 0
                                            }}°</span
                                        >
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Z-Index</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="9999"
                                        :value="selectedEl.styles.zIndex || 1"
                                        @input="
                                            selectedEl.styles.zIndex =
                                                +$event.target.value;
                                            markDirty();
                                        "
                                        style="width: 80px"
                                    />
                                </div>
                            </div>

                            <!-- Fill & Colors -->
                            <div class="prop-section">
                                <div class="prop-section-title">
                                    Fill & Color
                                </div>
                                <div class="prop-row">
                                    <label>Background</label>
                                    <div class="color-row">
                                        <input
                                            type="color"
                                            :value="
                                                selectedEl.styles
                                                    .backgroundColor &&
                                                selectedEl.styles
                                                    .backgroundColor !==
                                                    'transparent'
                                                    ? selectedEl.styles
                                                          .backgroundColor
                                                    : '#ffffff'
                                            "
                                            @input="
                                                selectedEl.styles.backgroundColor =
                                                    $event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <input
                                            type="text"
                                            :value="
                                                selectedEl.styles
                                                    .backgroundColor ||
                                                'transparent'
                                            "
                                            @input="
                                                selectedEl.styles.backgroundColor =
                                                    $event.target.value;
                                                markDirty();
                                            "
                                            class="color-hex"
                                        />
                                        <button
                                            class="micro-btn"
                                            @click="
                                                selectedEl.styles.backgroundColor =
                                                    'transparent';
                                                markDirty();
                                            "
                                            title="No fill"
                                        >
                                            ∅
                                        </button>
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Opacity</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="0"
                                            max="100"
                                            :value="
                                                selectedEl.styles.opacity ?? 100
                                            "
                                            @input="
                                                selectedEl.styles.opacity =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <span
                                            >{{
                                                selectedEl.styles.opacity ??
                                                100
                                            }}%</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Border -->
                            <div class="prop-section">
                                <div class="prop-section-title">Border</div>
                                <div class="prop-row">
                                    <label>Width</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="20"
                                        :value="
                                            selectedEl.styles.borderWidth || 0
                                        "
                                        @input="
                                            selectedEl.styles.borderWidth =
                                                +$event.target.value;
                                            markDirty();
                                        "
                                        style="width: 70px"
                                    />
                                </div>
                                <div
                                    class="prop-row"
                                    v-if="selectedEl.styles.borderWidth"
                                >
                                    <label>Color</label>
                                    <div class="color-row">
                                        <input
                                            type="color"
                                            :value="
                                                selectedEl.styles.borderColor ||
                                                '#000000'
                                            "
                                            @input="
                                                selectedEl.styles.borderColor =
                                                    $event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <input
                                            type="text"
                                            :value="
                                                selectedEl.styles.borderColor ||
                                                '#000000'
                                            "
                                            @input="
                                                selectedEl.styles.borderColor =
                                                    $event.target.value;
                                                markDirty();
                                            "
                                            class="color-hex"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="prop-row"
                                    v-if="selectedEl.styles.borderWidth"
                                >
                                    <label>Style</label>
                                    <select
                                        :value="
                                            selectedEl.styles.borderStyle ||
                                            'solid'
                                        "
                                        @change="
                                            selectedEl.styles.borderStyle =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    >
                                        <option>solid</option>
                                        <option>dashed</option>
                                        <option>dotted</option>
                                        <option>double</option>
                                    </select>
                                </div>
                                <div class="prop-row">
                                    <label>Radius</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="0"
                                            max="100"
                                            :value="
                                                selectedEl.styles
                                                    .borderRadius || 0
                                            "
                                            @input="
                                                selectedEl.styles.borderRadius =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <span
                                            >{{
                                                selectedEl.styles
                                                    .borderRadius || 0
                                            }}px</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Shadow -->
                            <div class="prop-section">
                                <div class="prop-section-title">Shadow</div>
                                <div class="prop-row">
                                    <label>Box Shadow</label>
                                    <input
                                        type="text"
                                        :value="
                                            selectedEl.styles.boxShadow || ''
                                        "
                                        @input="
                                            selectedEl.styles.boxShadow =
                                                $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="0 4px 12px rgba(0,0,0,0.1)"
                                    />
                                </div>
                                <div class="shadow-presets">
                                    <button
                                        class="shadow-preset"
                                        @click="
                                            selectedEl.styles.boxShadow =
                                                'none';
                                            markDirty();
                                        "
                                    >
                                        None
                                    </button>
                                    <button
                                        class="shadow-preset"
                                        @click="
                                            selectedEl.styles.boxShadow =
                                                '0 2px 8px rgba(0,0,0,0.08)';
                                            markDirty();
                                        "
                                    >
                                        Soft
                                    </button>
                                    <button
                                        class="shadow-preset"
                                        @click="
                                            selectedEl.styles.boxShadow =
                                                '0 4px 20px rgba(0,0,0,0.15)';
                                            markDirty();
                                        "
                                    >
                                        Medium
                                    </button>
                                    <button
                                        class="shadow-preset"
                                        @click="
                                            selectedEl.styles.boxShadow =
                                                '0 8px 40px rgba(0,0,0,0.25)';
                                            markDirty();
                                        "
                                    >
                                        Heavy
                                    </button>
                                </div>
                            </div>

                            <!-- Priority Tag -->
                            <div class="prop-section">
                                <div class="prop-section-title">
                                    Priority Stripe
                                </div>
                                <div class="priority-presets">
                                    <button
                                        v-for="p in [
                                            'none',
                                            'low',
                                            'medium',
                                            'high',
                                            'urgent',
                                        ]"
                                        :key="p"
                                        class="priority-preset"
                                        :class="[
                                            p,
                                            {
                                                active:
                                                    (selectedEl.styles
                                                        ?.priority ||
                                                        'none') === p,
                                            },
                                        ]"
                                        @click="
                                            selectedEl.styles.priority =
                                                p === 'none' ? undefined : p;
                                            markDirty();
                                        "
                                    >
                                        {{ p }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ── TYPOGRAPHY TAB ── -->
                        <div
                            v-show="activePropsTab === 'Text'"
                            class="props-section-list"
                        >
                            <div
                                class="prop-section"
                                v-if="isTextEl(selectedEl.type)"
                            >
                                <div class="prop-row">
                                    <label>Font</label>
                                    <select
                                        :value="
                                            selectedEl.styles?.fontFamily ||
                                            'Inter'
                                        "
                                        @change="
                                            selectedEl.styles.fontFamily =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    >
                                        <option
                                            v-for="f in fonts"
                                            :key="f"
                                            :value="f"
                                        >
                                            {{ f }}
                                        </option>
                                    </select>
                                </div>
                                <div class="prop-grid-2">
                                    <div class="prop-field">
                                        <label>Size</label>
                                        <input
                                            type="number"
                                            min="8"
                                            max="200"
                                            :value="
                                                selectedEl.styles?.fontSize ||
                                                14
                                            "
                                            @input="
                                                selectedEl.styles.fontSize =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                    </div>
                                    <div class="prop-field">
                                        <label>Weight</label>
                                        <select
                                            :value="
                                                selectedEl.styles?.fontWeight ||
                                                '400'
                                            "
                                            @change="
                                                selectedEl.styles.fontWeight =
                                                    $event.target.value;
                                                markDirty();
                                            "
                                        >
                                            <option value="300">Light</option>
                                            <option value="400">Regular</option>
                                            <option value="500">Medium</option>
                                            <option value="600">
                                                Semibold
                                            </option>
                                            <option value="700">Bold</option>
                                            <option value="800">
                                                ExtraBold
                                            </option>
                                            <option value="900">Black</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Color</label>
                                    <div class="color-row">
                                        <input
                                            type="color"
                                            :value="
                                                selectedEl.styles?.color ||
                                                '#000000'
                                            "
                                            @input="
                                                selectedEl.styles.color =
                                                    $event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <input
                                            type="text"
                                            :value="
                                                selectedEl.styles?.color ||
                                                '#000000'
                                            "
                                            @input="
                                                selectedEl.styles.color =
                                                    $event.target.value;
                                                markDirty();
                                            "
                                            class="color-hex"
                                        />
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Align</label>
                                    <div class="btn-group">
                                        <button
                                            v-for="a in [
                                                'left',
                                                'center',
                                                'right',
                                                'justify',
                                            ]"
                                            :key="a"
                                            class="btn-group-btn"
                                            :class="{
                                                active:
                                                    selectedEl.styles
                                                        ?.textAlign === a,
                                            }"
                                            @click="
                                                selectedEl.styles.textAlign = a;
                                                markDirty();
                                            "
                                        >
                                            <svg
                                                width="12"
                                                height="12"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <template v-if="a === 'left'">
                                                    <line
                                                        x1="3"
                                                        y1="6"
                                                        x2="21"
                                                        y2="6"
                                                    />
                                                    <line
                                                        x1="3"
                                                        y1="12"
                                                        x2="15"
                                                        y2="12"
                                                    />
                                                    <line
                                                        x1="3"
                                                        y1="18"
                                                        x2="18"
                                                        y2="18"
                                                    />
                                                </template>
                                                <template
                                                    v-else-if="a === 'center'"
                                                >
                                                    <line
                                                        x1="3"
                                                        y1="6"
                                                        x2="21"
                                                        y2="6"
                                                    />
                                                    <line
                                                        x1="6"
                                                        y1="12"
                                                        x2="18"
                                                        y2="12"
                                                    />
                                                    <line
                                                        x1="4"
                                                        y1="18"
                                                        x2="20"
                                                        y2="18"
                                                    />
                                                </template>
                                                <template
                                                    v-else-if="a === 'right'"
                                                >
                                                    <line
                                                        x1="3"
                                                        y1="6"
                                                        x2="21"
                                                        y2="6"
                                                    />
                                                    <line
                                                        x1="9"
                                                        y1="12"
                                                        x2="21"
                                                        y2="12"
                                                    />
                                                    <line
                                                        x1="6"
                                                        y1="18"
                                                        x2="21"
                                                        y2="18"
                                                    />
                                                </template>
                                                <template v-else>
                                                    <line
                                                        x1="3"
                                                        y1="6"
                                                        x2="21"
                                                        y2="6"
                                                    />
                                                    <line
                                                        x1="3"
                                                        y1="12"
                                                        x2="21"
                                                        y2="12"
                                                    />
                                                    <line
                                                        x1="3"
                                                        y1="18"
                                                        x2="21"
                                                        y2="18"
                                                    />
                                                </template>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Line Height</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="1"
                                            max="3"
                                            step="0.1"
                                            :value="
                                                selectedEl.styles?.lineHeight ||
                                                1.5
                                            "
                                            @input="
                                                selectedEl.styles.lineHeight =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <span>{{
                                            Number(
                                                selectedEl.styles?.lineHeight ||
                                                    1.5,
                                            ).toFixed(1)
                                        }}</span>
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Letter Spacing</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="-2"
                                            max="10"
                                            step="0.5"
                                            :value="
                                                selectedEl.styles
                                                    ?.letterSpacing || 0
                                            "
                                            @input="
                                                selectedEl.styles.letterSpacing =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        />
                                        <span
                                            >{{
                                                selectedEl.styles
                                                    ?.letterSpacing || 0
                                            }}px</span
                                        >
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Transform</label>
                                    <select
                                        :value="
                                            selectedEl.styles?.textTransform ||
                                            'none'
                                        "
                                        @change="
                                            selectedEl.styles.textTransform =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    >
                                        <option>none</option>
                                        <option>uppercase</option>
                                        <option>lowercase</option>
                                        <option>capitalize</option>
                                    </select>
                                </div>
                                <div class="prop-row">
                                    <label>Padding</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="80"
                                        :value="selectedEl.styles?.padding || 0"
                                        @input="
                                            selectedEl.styles.padding =
                                                +$event.target.value;
                                            markDirty();
                                        "
                                        style="width: 70px"
                                    />
                                </div>
                            </div>
                            <div v-else class="no-text-props">
                                No typography options for this element type.
                            </div>
                        </div>

                        <!-- ── CONTENT TAB ── -->
                        <div
                            v-show="activePropsTab === 'Content'"
                            class="props-section-list"
                        >
                            <!-- Text content -->
                            <div
                                class="prop-section"
                                v-if="isTextEl(selectedEl.type)"
                            >
                                <div class="prop-section-title">Content</div>
                                <textarea
                                    class="content-textarea"
                                    :value="selectedEl.content || ''"
                                    @input="
                                        selectedEl.content =
                                            $event.target.value;
                                        markDirty();
                                    "
                                    rows="5"
                                    placeholder="Enter content…"
                                ></textarea>
                            </div>

                            <!-- Image -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'image'"
                            >
                                <div class="prop-section-title">Image</div>
                                <div class="prop-row">
                                    <label>URL</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.src || ''"
                                        @input="
                                            selectedEl.src =
                                                $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="https://…"
                                    />
                                </div>
                                <div class="prop-row">
                                    <label>Alt Text</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.alt || ''"
                                        @input="
                                            selectedEl.alt =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                                <div class="prop-row">
                                    <label>Fit</label
                                    ><select
                                        :value="
                                            selectedEl.styles?.objectFit ||
                                            'cover'
                                        "
                                        @change="
                                            selectedEl.styles.objectFit =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    >
                                        <option>cover</option>
                                        <option>contain</option>
                                        <option>fill</option>
                                        <option>none</option>
                                    </select>
                                </div>
                                <button
                                    class="btn-secondary full-width"
                                    @click="triggerImagePick(selectedEl)"
                                >
                                    Replace Image
                                </button>
                            </div>

                            <!-- Metric -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'metric'"
                            >
                                <div class="prop-row">
                                    <label>Label</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.label || ''"
                                        @input="
                                            selectedEl.label =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                                <div class="prop-row">
                                    <label>Value</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.value || ''"
                                        @input="
                                            selectedEl.value =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                                <div class="prop-row">
                                    <label>Change</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.change || ''"
                                        @input="
                                            selectedEl.change =
                                                $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="+12%"
                                    />
                                </div>
                                <div class="prop-row">
                                    <label>Type</label>
                                    <select
                                        :value="
                                            selectedEl.changeType || 'positive'
                                        "
                                        @change="
                                            selectedEl.changeType =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    >
                                        <option>positive</option>
                                        <option>negative</option>
                                    </select>
                                </div>
                                <div class="prop-row">
                                    <label>Value Color</label>
                                    <input
                                        type="color"
                                        :value="
                                            selectedEl.styles?.valueColor ||
                                            settings.primary_color
                                        "
                                        @input="
                                            selectedEl.styles.valueColor =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                            </div>

                            <!-- Progress -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'progress'"
                            >
                                <div class="prop-row">
                                    <label>Label</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.label || ''"
                                        @input="
                                            selectedEl.label =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                                <div class="prop-row">
                                    <label>Value (%)</label
                                    ><input
                                        type="range"
                                        min="0"
                                        max="100"
                                        :value="selectedEl.value || 0"
                                        @input="
                                            selectedEl.value =
                                                +$event.target.value;
                                            markDirty();
                                        "
                                    /><span>{{ selectedEl.value || 0 }}%</span>
                                </div>
                                <div class="prop-row">
                                    <label>Color</label
                                    ><input
                                        type="color"
                                        :value="
                                            selectedEl.styles?.color ||
                                            settings.primary_color
                                        "
                                        @input="
                                            selectedEl.styles.color =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                            </div>

                            <!-- Chart data -->
                            <div
                                class="prop-section"
                                v-if="isChart(selectedEl.type)"
                            >
                                <div class="prop-row">
                                    <label>Title</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.chartTitle || ''"
                                        @input="
                                            selectedEl.chartTitle =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                                <div class="prop-section-title">
                                    Labels (comma separated)
                                </div>
                                <input
                                    type="text"
                                    :value="
                                        (
                                            selectedEl.chartData?.labels || []
                                        ).join(',')
                                    "
                                    @input="setChartLabels($event.target.value)"
                                />
                                <div class="prop-section-title">
                                    Values (comma separated)
                                </div>
                                <input
                                    type="text"
                                    :value="
                                        (
                                            selectedEl.chartData?.values || []
                                        ).join(',')
                                    "
                                    @input="setChartValues($event.target.value)"
                                />
                            </div>

                            <!-- Table columns/rows editor -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'table'"
                            >
                                <div class="prop-section-title">Columns</div>
                                <div
                                    v-for="(col, ci) in selectedEl.columns"
                                    :key="ci"
                                    class="list-item-row"
                                >
                                    <input
                                        type="text"
                                        :value="col"
                                        @input="
                                            selectedEl.columns[ci] =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                    <button
                                        class="micro-btn danger"
                                        @click="removeTableColumn(ci)"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <button
                                    class="btn-secondary"
                                    @click="addTableColumn"
                                >
                                    + Add Column
                                </button>
                                <div class="prop-section-title">Rows</div>
                                <div class="prop-row">
                                    <span
                                        >{{
                                            (selectedEl.data || []).length
                                        }}
                                        rows</span
                                    ><button
                                        class="btn-secondary"
                                        @click="addTableRow"
                                    >
                                        + Add Row
                                    </button>
                                </div>
                            </div>

                            <!-- Callout emoji -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'callout'"
                            >
                                <div class="prop-row">
                                    <label>Emoji</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.emoji || '💡'"
                                        @input="
                                            selectedEl.emoji =
                                                $event.target.value;
                                            markDirty();
                                        "
                                        style="width: 70px; font-size: 18px"
                                    />
                                </div>
                            </div>

                            <!-- Timeline items -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'timeline'"
                            >
                                <div
                                    v-for="(item, ti) in selectedEl.items || []"
                                    :key="ti"
                                    class="timeline-editor-item"
                                >
                                    <input
                                        type="text"
                                        :value="item.date"
                                        @input="
                                            item.date = $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="Date"
                                    />
                                    <input
                                        type="text"
                                        :value="item.label"
                                        @input="
                                            item.label = $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="Title"
                                    />
                                    <input
                                        type="text"
                                        :value="item.desc"
                                        @input="
                                            item.desc = $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="Description"
                                    />
                                    <button
                                        class="micro-btn danger"
                                        @click="
                                            selectedEl.items.splice(ti, 1);
                                            markDirty();
                                        "
                                    >
                                        ✕
                                    </button>
                                </div>
                                <button
                                    class="btn-secondary"
                                    @click="
                                        selectedEl.items = [
                                            ...(selectedEl.items || []),
                                            { date: '', label: '', desc: '' },
                                        ];
                                        markDirty();
                                    "
                                >
                                    + Add Item
                                </button>
                            </div>

                            <!-- Checklist items -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'checklist'"
                            >
                                <div
                                    v-for="(item, ci) in selectedEl.items || []"
                                    :key="ci"
                                    class="list-item-row"
                                >
                                    <input
                                        type="text"
                                        :value="item.text"
                                        @input="
                                            item.text = $event.target.value;
                                            markDirty();
                                        "
                                    />
                                    <button
                                        class="micro-btn danger"
                                        @click="
                                            selectedEl.items.splice(ci, 1);
                                            markDirty();
                                        "
                                    >
                                        ✕
                                    </button>
                                </div>
                                <button
                                    class="btn-secondary"
                                    @click="
                                        selectedEl.items = [
                                            ...(selectedEl.items || []),
                                            {
                                                text: 'New item',
                                                checked: false,
                                            },
                                        ];
                                        markDirty();
                                    "
                                >
                                    + Add Item
                                </button>
                            </div>

                            <!-- Testimonial -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'testimonial'"
                            >
                                <div class="prop-row">
                                    <label>Author</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.author || ''"
                                        @input="
                                            selectedEl.author =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                                <div class="prop-row">
                                    <label>Role</label
                                    ><input
                                        type="text"
                                        :value="selectedEl.role || ''"
                                        @input="
                                            selectedEl.role =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                            </div>

                            <!-- Stat row -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'stat-row'"
                            >
                                <div
                                    v-for="(stat, si) in selectedEl.stats || []"
                                    :key="si"
                                    class="list-item-row"
                                >
                                    <input
                                        type="text"
                                        :value="stat.value"
                                        @input="
                                            stat.value = $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="Value"
                                    />
                                    <input
                                        type="text"
                                        :value="stat.label"
                                        @input="
                                            stat.label = $event.target.value;
                                            markDirty();
                                        "
                                        placeholder="Label"
                                    />
                                    <button
                                        class="micro-btn danger"
                                        @click="
                                            selectedEl.stats.splice(si, 1);
                                            markDirty();
                                        "
                                    >
                                        ✕
                                    </button>
                                </div>
                                <button
                                    class="btn-secondary"
                                    @click="
                                        selectedEl.stats = [
                                            ...(selectedEl.stats || []),
                                            { value: '0', label: 'Metric' },
                                        ];
                                        markDirty();
                                    "
                                >
                                    + Add Stat
                                </button>
                            </div>

                            <!-- Rating value -->
                            <div
                                class="prop-section"
                                v-if="selectedEl.type === 'rating'"
                            >
                                <div class="prop-row">
                                    <label>Rating (0–5)</label
                                    ><input
                                        type="range"
                                        min="0"
                                        max="5"
                                        step="0.5"
                                        :value="selectedEl.value || 0"
                                        @input="
                                            selectedEl.value =
                                                +$event.target.value;
                                            markDirty();
                                        "
                                    /><span>{{ selectedEl.value || 0 }}</span>
                                </div>
                                <div class="prop-row">
                                    <label>Color</label
                                    ><input
                                        type="color"
                                        :value="
                                            selectedEl.styles?.color ||
                                            '#f59e0b'
                                        "
                                        @input="
                                            selectedEl.styles.color =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ── EFFECTS TAB ── -->
                        <div
                            v-show="activePropsTab === 'Effects'"
                            class="props-section-list"
                        >
                            <div class="prop-section">
                                <div class="prop-section-title">Transform</div>
                                <div class="prop-row">
                                    <label>Flip H</label
                                    ><button
                                        class="toggle-btn"
                                        :class="{
                                            active:
                                                selectedEl.styles?.scaleX ===
                                                -1,
                                        }"
                                        @click="
                                            selectedEl.styles.scaleX =
                                                selectedEl.styles?.scaleX === -1
                                                    ? 1
                                                    : -1;
                                            markDirty();
                                        "
                                    >
                                        Flip Horizontal
                                    </button>
                                </div>
                                <div class="prop-row">
                                    <label>Flip V</label
                                    ><button
                                        class="toggle-btn"
                                        :class="{
                                            active:
                                                selectedEl.styles?.scaleY ===
                                                -1,
                                        }"
                                        @click="
                                            selectedEl.styles.scaleY =
                                                selectedEl.styles?.scaleY === -1
                                                    ? 1
                                                    : -1;
                                            markDirty();
                                        "
                                    >
                                        Flip Vertical
                                    </button>
                                </div>
                            </div>
                            <div class="prop-section">
                                <div class="prop-section-title">Filters</div>
                                <div class="prop-row">
                                    <label>Blur</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="0"
                                            max="20"
                                            :value="
                                                selectedEl.styles?.blur || 0
                                            "
                                            @input="
                                                selectedEl.styles.blur =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        /><span
                                            >{{
                                                selectedEl.styles?.blur || 0
                                            }}px</span
                                        >
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Brightness</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="0"
                                            max="200"
                                            :value="
                                                selectedEl.styles?.brightness ||
                                                100
                                            "
                                            @input="
                                                selectedEl.styles.brightness =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        /><span
                                            >{{
                                                selectedEl.styles?.brightness ||
                                                100
                                            }}%</span
                                        >
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Contrast</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="0"
                                            max="200"
                                            :value="
                                                selectedEl.styles?.contrast ||
                                                100
                                            "
                                            @input="
                                                selectedEl.styles.contrast =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        /><span
                                            >{{
                                                selectedEl.styles?.contrast ||
                                                100
                                            }}%</span
                                        >
                                    </div>
                                </div>
                                <div class="prop-row">
                                    <label>Grayscale</label>
                                    <div class="slider-row">
                                        <input
                                            type="range"
                                            min="0"
                                            max="100"
                                            :value="
                                                selectedEl.styles?.grayscale ||
                                                0
                                            "
                                            @input="
                                                selectedEl.styles.grayscale =
                                                    +$event.target.value;
                                                markDirty();
                                            "
                                        /><span
                                            >{{
                                                selectedEl.styles?.grayscale ||
                                                0
                                            }}%</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="prop-section">
                                <div class="prop-section-title">
                                    Hover Animation
                                </div>
                                <div class="prop-row">
                                    <label>Effect</label>
                                    <select
                                        :value="
                                            selectedEl.styles?.hoverEffect ||
                                            'none'
                                        "
                                        @change="
                                            selectedEl.styles.hoverEffect =
                                                $event.target.value;
                                            markDirty();
                                        "
                                    >
                                        <option>none</option>
                                        <option>lift</option>
                                        <option>pulse</option>
                                        <option>shake</option>
                                        <option>bounce</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ── ARRANGE TAB ── -->
                        <div
                            v-show="activePropsTab === 'Arrange'"
                            class="props-section-list"
                        >
                            <div class="prop-section">
                                <div class="prop-section-title">Layer</div>
                                <div class="prop-row-buttons">
                                    <button
                                        class="btn-secondary"
                                        @click="bringToFront"
                                    >
                                        Bring to Front
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="bringForward"
                                    >
                                        Bring Forward
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="sendBackward"
                                    >
                                        Send Backward
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="sendToBack"
                                    >
                                        Send to Back
                                    </button>
                                </div>
                            </div>
                            <div class="prop-section">
                                <div class="prop-section-title">
                                    Align to Page
                                </div>
                                <div class="prop-row-buttons">
                                    <button
                                        class="btn-secondary"
                                        @click="alignToPage('left')"
                                    >
                                        Left
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="alignToPage('center-h')"
                                    >
                                        Center H
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="alignToPage('right')"
                                    >
                                        Right
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="alignToPage('top')"
                                    >
                                        Top
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="alignToPage('center-v')"
                                    >
                                        Center V
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="alignToPage('bottom')"
                                    >
                                        Bottom
                                    </button>
                                </div>
                            </div>
                            <div class="prop-section">
                                <div class="prop-section-title">Actions</div>
                                <div class="prop-row-buttons">
                                    <button
                                        class="btn-secondary"
                                        @click="duplicateSelected"
                                    >
                                        Duplicate
                                    </button>
                                    <button
                                        class="btn-secondary"
                                        @click="copyElement"
                                    >
                                        Copy
                                    </button>
                                    <button
                                        class="btn-secondary danger"
                                        @click="deleteSelected"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SETTINGS (always visible via tab at bottom) -->
                    <div
                        v-if="activeRightSection === 'settings'"
                        class="page-settings-panel"
                    >
                        <div class="prop-section-title">Document Settings</div>
                        <div class="prop-row">
                            <label>Page Size</label
                            ><select
                                v-model="settings.page_size"
                                @change="markDirty"
                            >
                                <option>A4</option>
                                <option>Letter</option>
                                <option>Legal</option>
                                <option>A3</option>
                                <option>A5</option>
                            </select>
                        </div>
                        <div class="prop-row">
                            <label>Orientation</label
                            ><select
                                v-model="settings.orientation"
                                @change="markDirty"
                            >
                                <option>portrait</option>
                                <option>landscape</option>
                            </select>
                        </div>
                        <div class="prop-row">
                            <label>Primary Color</label>
                            <div class="color-row">
                                <input
                                    type="color"
                                    v-model="settings.primary_color"
                                    @input="markDirty"
                                /><input
                                    type="text"
                                    v-model="settings.primary_color"
                                    @input="markDirty"
                                    class="color-hex"
                                />
                            </div>
                        </div>
                        <div class="prop-row">
                            <label>Background</label>
                            <div class="color-row">
                                <input
                                    type="color"
                                    :value="
                                        settings.background_color || '#ffffff'
                                    "
                                    @input="
                                        settings.background_color =
                                            $event.target.value;
                                        markDirty();
                                    "
                                /><input
                                    type="text"
                                    v-model="settings.background_color"
                                    @input="markDirty"
                                    class="color-hex"
                                />
                            </div>
                        </div>
                        <div class="prop-row">
                            <label>Font</label
                            ><select
                                v-model="settings.font_family"
                                @change="markDirty"
                            >
                                <option v-for="f in fonts" :key="f" :value="f">
                                    {{ f }}
                                </option>
                            </select>
                        </div>
                        <div class="prop-row">
                            <label>Margin (px)</label
                            ><input
                                type="number"
                                min="0"
                                max="120"
                                v-model.number="settings.margin"
                                @input="markDirty"
                                style="width: 80px"
                            />
                        </div>
                        <hr class="sep" />
                        <div class="prop-row toggle-row">
                            <label>Show Header</label
                            ><input
                                type="checkbox"
                                v-model="settings.show_header"
                                @change="markDirty"
                            />
                        </div>
                        <div class="prop-row" v-if="settings.show_header">
                            <label>Header Text</label
                            ><input
                                type="text"
                                v-model="settings.header_text"
                                @input="markDirty"
                            />
                        </div>
                        <div class="prop-row toggle-row">
                            <label>Show Footer</label
                            ><input
                                type="checkbox"
                                v-model="settings.show_footer"
                                @change="markDirty"
                            />
                        </div>
                        <div class="prop-row" v-if="settings.show_footer">
                            <label>Footer Left</label
                            ><input
                                type="text"
                                v-model="settings.footer_left"
                                @input="markDirty"
                            />
                        </div>
                        <div class="prop-row" v-if="settings.show_footer">
                            <label>Footer Right</label
                            ><input
                                type="text"
                                v-model="settings.footer_right"
                                @input="markDirty"
                                placeholder="{n} for page num"
                            />
                        </div>
                        <hr class="sep" />
                        <div class="prop-row">
                            <label>Watermark</label
                            ><input
                                type="text"
                                v-model="settings.watermark"
                                @input="markDirty"
                                placeholder="CONFIDENTIAL"
                            />
                        </div>
                        <div class="prop-row" v-if="settings.watermark">
                            <label>Opacity</label>
                            <div class="slider-row">
                                <input
                                    type="range"
                                    min="1"
                                    max="30"
                                    v-model.number="settings.watermark_opacity"
                                    @input="markDirty"
                                /><span
                                    >{{
                                        settings.watermark_opacity || 8
                                    }}%</span
                                >
                            </div>
                        </div>
                        <div class="prop-row toggle-row">
                            <label>Page Numbers</label
                            ><input
                                type="checkbox"
                                v-model="settings.show_page_numbers"
                                @change="markDirty"
                            />
                        </div>
                    </div>

                    <!-- Right panel bottom nav -->
                    <div class="right-bottom-nav">
                        <button
                            class="rbn-btn"
                            :class="{ active: activeRightSection === 'props' }"
                            @click="activeRightSection = 'props'"
                        >
                            <svg
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="12" r="3" />
                                <path
                                    d="M20 12v2a2 2 0 01-2 2h-1.5a1 1 0 00-.8.4l-.9 1.2a1 1 0 01-1.6 0l-.9-1.2a1 1 0 00-.8-.4H10a2 2 0 01-2-2V12"
                                />
                            </svg>
                            Properties
                        </button>
                        <button
                            class="rbn-btn"
                            :class="{
                                active: activeRightSection === 'settings',
                            }"
                            @click="activeRightSection = 'settings'"
                        >
                            <svg
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="12" r="3" />
                                <path
                                    d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"
                                />
                            </svg>
                            Page
                        </button>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Status bar -->
        <footer class="status-bar">
            <span>{{ currentPageElements.length }} elements</span>
            <span class="sep-dot">·</span>
            <span
                >Page {{ currentPage + 1 }} / {{ report.content.length }}</span
            >
            <span class="sep-dot">·</span>
            <span>{{ settings.page_size }} {{ settings.orientation }}</span>
            <span class="sep-dot">·</span>
            <span>{{ zoom }}%</span>
            <span v-if="selectedEl" class="sep-dot">·</span>
            <span v-if="selectedEl">{{ selectedEl.type }} selected</span>
            <div class="status-right">
                <span v-if="isDirty" class="unsaved-dot"></span>
                <span v-if="isDirty">Unsaved changes</span>
            </div>
        </footer>

        <!-- Context Menu -->
        <div
            v-if="contextMenu.show"
            class="context-menu"
            :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
            @click.stop
        >
            <button
                v-for="item in contextMenu.items"
                :key="item.label"
                @click="
                    item.action();
                    contextMenu.show = false;
                "
                class="ctx-item"
                :class="{ danger: item.danger, separator: item.separator }"
            >
                <span v-html="item.icon"></span>
                {{ item.label }}
                <span v-if="item.shortcut" class="ctx-shortcut">{{
                    item.shortcut
                }}</span>
            </button>
        </div>

        <!-- Image input hidden -->
        <input
            ref="imageInputEl"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleImagePickChange"
        />

        <!-- Toast notifications -->
        <transition name="toast">
            <div v-if="toast.show" class="toast" :class="toast.type">
                {{ toast.message }}
            </div>
        </transition>
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
import { Link, router } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({ report: Object });

// ──────────────────────────────────────────────────────────────────
// STATE
// ──────────────────────────────────────────────────────────────────
const report = reactive(JSON.parse(JSON.stringify(props.report)));
const settings = reactive(
    JSON.parse(JSON.stringify(props.report?.settings || {})),
);

// Ensure defaults
if (!settings.page_size) settings.page_size = "A4";
if (!settings.orientation) settings.orientation = "portrait";
if (!settings.primary_color) settings.primary_color = "#6366f1";
if (!settings.background_color) settings.background_color = "#ffffff";
if (!settings.font_family) settings.font_family = "Inter";
if (!settings.margin) settings.margin = 40;
if (settings.watermark_opacity === undefined) settings.watermark_opacity = 8;

// Ensure pages exist and inherit template settings
if (!report.content || !report.content.length) {
    report.content = [{ id: uid(), label: "Page 1", elements: [] }];
}

// UI state
const isDark = ref(localStorage.getItem("theme") === "dark");
const isFullscreen = ref(false);
const zoom = ref(100);
const showGrid = ref(false);
const snapToGrid = ref(true);
const showRulers = ref(false);
const showComments = ref(false);
const showAI = ref(false);
const showVersions = ref(false);
const showExportMenu = ref(false);
const leftCollapsed = ref(false);
const rightCollapsed = ref(false);
const isDirty = ref(false);
const saving = ref(false);
const lastSaved = ref("");

// Page state
const currentPage = ref(0);
const selectedElIdx = ref(null);
const selectedEls = ref([]);
const editingElIdx = ref(null);
const isDraggingEl = ref(false);
const currentElForImage = ref(null);

// Panel state
const activeLeftTab = ref("elements");
const activePropsTab = ref("Style");
const activeRightSection = ref("props");
const elSearch = ref("");
const collapsedCats = ref([]);

// Format state (ribbon)
const fmt = reactive({
    fontFamily: "Inter",
    fontSize: 14,
    fontWeight: "400",
    fontStyle: "normal",
    textDecoration: "none",
    textAlign: "left",
    color: "#000000",
    backgroundColor: "transparent",
});

// Undo/Redo
const undoStack = ref([]);
const redoStack = ref([]);
const canUndo = computed(() => undoStack.value.length > 0);
const canRedo = computed(() => redoStack.value.length > 0);

// Drag/resize state
let dragInfo = null;
let resizeInfo = null;
let rotateInfo = null;

// Rubber band
const rubberBand = reactive({
    active: false,
    startX: 0,
    startY: 0,
    x: 0,
    y: 0,
    w: 0,
    h: 0,
});

// Context menu
const contextMenu = reactive({ show: false, x: 0, y: 0, items: [] });

// Toast
const toast = reactive({ show: false, message: "", type: "success" });

// AI
const aiConvo = ref([
    {
        role: "assistant",
        text: "Hi! I can help you generate content, suggest layouts, or write text for your report. What do you need?",
    },
]);
const aiPrompt = ref("");
const aiLoading = ref(false);

// Clipboard
let clipboard = null;

// Uploaded images
const uploadedImages = ref([]);

// Refs
const editorShell = ref(null);
const canvasArea = ref(null);
const pageEl = ref(null);
const imageInput = ref(null);
const imageInputEl = ref(null);
const aiMessages = ref(null);
const rulerTopCanvas = ref(null);
const rulerLeftCanvas = ref(null);
let saveTimeout = null;

// ──────────────────────────────────────────────────────────────────
// COMPUTED
// ──────────────────────────────────────────────────────────────────
const currentPageElements = computed(
    () => report.content[currentPage.value]?.elements || [],
);
const selectedEl = computed(() =>
    selectedElIdx.value !== null
        ? currentPageElements.value[selectedElIdx.value]
        : null,
);
const multiSelected = computed(() => selectedEls.value.length > 1);

const pageDims = computed(() => {
    const sizes = {
        A4: [794, 1123],
        Letter: [816, 1056],
        Legal: [816, 1344],
        A3: [1123, 1587],
        A5: [559, 794],
    };
    const [w, h] = sizes[settings.page_size] || sizes.A4;
    return settings.orientation === "landscape" ? { w: h, h: w } : { w, h };
});

const pageStyle = computed(() => ({
    width: pageDims.value.w + "px",
    minHeight: pageDims.value.h + "px",
    backgroundColor: settings.background_color || "#fff",
    fontFamily: `'${settings.font_family || "Inter"}', sans-serif`,
    position: "relative",
    overflow: "hidden",
    backgroundImage: showGrid.value
        ? "linear-gradient(rgba(99,102,241,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,0.08) 1px, transparent 1px)"
        : "none",
    backgroundSize: showGrid.value ? "20px 20px" : "auto",
}));

const containerStyle = computed(() => ({
    transform: `scale(${zoom.value / 100})`,
    transformOrigin: "top center",
    transition: "transform 0.2s ease",
}));

const pageThumbStyle = computed(() => ({
    width: "100%",
    height: "100%",
    backgroundColor: settings.background_color || "#fff",
    position: "relative",
    overflow: "hidden",
}));

const headerBarStyle = computed(() => ({
    position: "absolute",
    top: 0,
    left: 0,
    right: 0,
    height: "50px",
    backgroundColor: settings.header_color || "#1e293b",
    color: "#fff",
    display: "flex",
    alignItems: "center",
    padding: "0 40px",
    fontSize: "13px",
    fontWeight: "600",
    zIndex: 10,
}));

const footerBarStyle = computed(() => ({
    position: "absolute",
    bottom: 0,
    left: 0,
    right: 0,
    height: "35px",
    color: "#94a3b8",
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    padding: "0 40px",
    fontSize: "10px",
    borderTop: `1px solid ${settings.primary_color}20`,
    zIndex: 10,
}));

const watermarkStyle = computed(() => ({
    position: "absolute",
    top: "50%",
    left: "50%",
    transform: "translate(-50%, -50%) rotate(-30deg)",
    fontSize: "72px",
    fontWeight: "900",
    color: "#94a3b8",
    opacity: (settings.watermark_opacity || 8) / 100,
    whiteSpace: "nowrap",
    pointerEvents: "none",
    zIndex: 5,
    userSelect: "none",
}));

const rubberBandStyle = computed(() => ({
    position: "absolute",
    left: rubberBand.x + "px",
    top: rubberBand.y + "px",
    width: rubberBand.w + "px",
    height: rubberBand.h + "px",
    border: "1.5px dashed #6366f1",
    backgroundColor: "rgba(99,102,241,0.06)",
    pointerEvents: "none",
    zIndex: 1000,
}));

// ──────────────────────────────────────────────────────────────────
// ELEMENT CATALOG
// ──────────────────────────────────────────────────────────────────
const elementCatalog = [
    {
        name: "Text",
        items: [
            {
                type: "text",
                label: "Text",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>',
                w: 200,
                h: 50,
            },
            {
                type: "heading",
                label: "Heading",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M4 6h16M4 18h12"/></svg>',
                w: 320,
                h: 60,
                defaultContent: "Heading",
            },
            {
                type: "subheading",
                label: "Subheading",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M4 6h12"/></svg>',
                w: 280,
                h: 45,
                defaultContent: "Subheading",
            },
            {
                type: "quote",
                label: "Quote",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/></svg>',
                w: 300,
                h: 80,
                defaultContent: "Inspiring quote goes here.",
            },
            {
                type: "blockquote",
                label: "Blockquote",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>',
                w: 300,
                h: 80,
                defaultContent: "Blockquote content here.",
            },
            {
                type: "highlight",
                label: "Highlight",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
                w: 250,
                h: 40,
                defaultContent: "Highlighted text",
            },
            {
                type: "badge",
                label: "Badge",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>',
                w: 120,
                h: 35,
                defaultContent: "Badge",
            },
            {
                type: "code",
                label: "Code",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
                w: 360,
                h: 120,
                defaultContent: '// Your code here\nconsole.log("Hello World")',
            },
            {
                type: "link",
                label: "Link",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>',
                w: 200,
                h: 35,
                defaultContent: "https://example.com",
            },
        ],
    },
    {
        name: "Data & Charts",
        items: [
            {
                type: "metric",
                label: "KPI Card",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
                w: 180,
                h: 110,
                label_: "Metric",
                value_: "0",
                change_: "+0%",
            },
            {
                type: "stat-row",
                label: "Stat Row",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
                w: 450,
                h: 90,
            },
            {
                type: "progress",
                label: "Progress",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="12" x2="2" y2="12"/></svg>',
                w: 350,
                h: 60,
            },
            {
                type: "table",
                label: "Table",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>',
                w: 460,
                h: 220,
            },
            {
                type: "bar-chart",
                label: "Bar Chart",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>',
                w: 400,
                h: 280,
            },
            {
                type: "line-chart",
                label: "Line Chart",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
                w: 400,
                h: 280,
            },
            {
                type: "pie-chart",
                label: "Pie Chart",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 118 2.83"/><path d="M22 12A10 10 0 0012 2v10z"/></svg>',
                w: 280,
                h: 280,
            },
            {
                type: "doughnut-chart",
                label: "Doughnut",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>',
                w: 280,
                h: 280,
            },
            {
                type: "area-chart",
                label: "Area Chart",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 20l4-8 4 5 4-9 4 8"/><path d="M3 20h18"/></svg>',
                w: 400,
                h: 280,
            },
            {
                type: "radar-chart",
                label: "Radar",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 22 9 18 21 6 21 2 9"/></svg>',
                w: 300,
                h: 300,
            },
            {
                type: "checklist",
                label: "Checklist",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>',
                w: 280,
                h: 160,
            },
        ],
    },
    {
        name: "Media",
        items: [
            {
                type: "image",
                label: "Image",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
                w: 300,
                h: 200,
            },
            {
                type: "icon",
                label: "Icon",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                w: 80,
                h: 80,
                defaultContent: "⭐",
            },
            {
                type: "rating",
                label: "Rating",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                w: 160,
                h: 40,
            },
        ],
    },
    {
        name: "Layout",
        items: [
            {
                type: "callout",
                label: "Callout",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                w: 380,
                h: 100,
                defaultContent: "Add your callout message here.",
            },
            {
                type: "timeline",
                label: "Timeline",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
                w: 420,
                h: 250,
            },
            {
                type: "testimonial",
                label: "Testimonial",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-3c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>',
                w: 360,
                h: 160,
                author_: "Jane Doe",
                role_: "CEO",
                defaultContent:
                    "This is an amazing product that transformed our workflow.",
            },
            {
                type: "stat-row",
                label: "Stats",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>',
                w: 450,
                h: 90,
            },
            {
                type: "signature",
                label: "Signature",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l4-8 4 5 3-5 4 8"/><line x1="3" y1="20" x2="21" y2="20"/></svg>',
                w: 220,
                h: 100,
            },
            {
                type: "pagenum",
                label: "Page #",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
                w: 50,
                h: 30,
            },
            {
                type: "date-el",
                label: "Date",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                w: 180,
                h: 30,
            },
        ],
    },
    {
        name: "Shapes",
        items: [
            {
                type: "rectangle",
                label: "Rectangle",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>',
                w: 200,
                h: 120,
            },
            {
                type: "circle",
                label: "Circle",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>',
                w: 120,
                h: 120,
            },
            {
                type: "triangle",
                label: "Triangle",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>',
                w: 120,
                h: 100,
            },
            {
                type: "divider",
                label: "Divider",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/></svg>',
                w: 500,
                h: 4,
            },
            {
                type: "arrow",
                label: "Arrow",
                icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
                w: 200,
                h: 40,
            },
        ],
    },
];

const filteredElCats = computed(() => {
    if (!elSearch.value) return elementCatalog;
    const q = elSearch.value.toLowerCase();
    return elementCatalog
        .map((c) => ({
            ...c,
            items: c.items.filter(
                (i) => i.label.toLowerCase().includes(q) || i.type.includes(q),
            ),
        }))
        .filter((c) => c.items.length);
});

const fonts = [
    "Inter",
    "DM Sans",
    "Plus Jakarta Sans",
    "Georgia",
    "Playfair Display",
    "Courier New",
    "Roboto Mono",
    "Space Grotesk",
    "Outfit",
    "Nunito",
];
const fontSizes = [
    8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 24, 28, 32, 36, 42, 48, 56, 64,
    72, 96,
];

const leftTabs = [
    {
        id: "elements",
        label: "Elements",
        icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>',
    },
    {
        id: "pages",
        label: "Pages",
        icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
    },
    {
        id: "layers",
        label: "Layers",
        icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
    },
    {
        id: "media",
        label: "Media",
        icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
    },
    {
        id: "templates",
        label: "Layouts",
        icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>',
    },
];

const propsTabs = ["Style", "Text", "Content", "Effects", "Arrange"];

const resizeHandles = ["nw", "n", "ne", "e", "se", "s", "sw", "w"];

const priorityColors = {
    low: "#3b82f6",
    medium: "#f59e0b",
    high: "#f97316",
    urgent: "#ef4444",
};

const quickTemplates = [
    {
        name: "Executive Dark",
        gradient: "linear-gradient(135deg, #0f172a, #1e293b)",
    },
    {
        name: "Modern Blue",
        gradient: "linear-gradient(135deg, #1e40af, #3b82f6)",
    },
    {
        name: "Emerald Fresh",
        gradient: "linear-gradient(135deg, #065f46, #10b981)",
    },
    {
        name: "Warm Amber",
        gradient: "linear-gradient(135deg, #78350f, #f59e0b)",
    },
    {
        name: "Rose Gold",
        gradient: "linear-gradient(135deg, #9f1239, #f43f5e)",
    },
    {
        name: "Purple Pro",
        gradient: "linear-gradient(135deg, #4c1d95, #8b5cf6)",
    },
];

// ──────────────────────────────────────────────────────────────────
// ELEMENT HELPERS
// ──────────────────────────────────────────────────────────────────
function uid() {
    return Math.random().toString(36).slice(2) + Date.now().toString(36);
}

function isTextEl(type) {
    return [
        "text",
        "heading",
        "subheading",
        "quote",
        "blockquote",
        "highlight",
        "badge",
        "link",
        "code",
        "callout",
        "testimonial",
        "signature",
        "icon",
    ].includes(type);
}

function isChart(type) {
    return type?.endsWith("-chart");
}

function getPlaceholder(type) {
    const p = {
        text: "Double-click to edit",
        heading: "Heading",
        subheading: "Subheading",
        quote: "Inspiring quote…",
        blockquote: "Blockquote text…",
        highlight: "Highlighted text",
        badge: "Badge",
        code: "// Code here",
        link: "https://example.com",
        callout: "Callout message…",
    };
    return p[type] || "Text";
}

function getElIcon(type) {
    const el = elementCatalog
        .flatMap((c) => c.items)
        .find((i) => i.type === type);
    return (
        el?.icon ||
        '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>'
    );
}

function elementStyle(el) {
    const s = el.styles || {};
    const filters = [];
    if (s.blur) filters.push(`blur(${s.blur}px)`);
    if (s.brightness && s.brightness !== 100)
        filters.push(`brightness(${s.brightness}%)`);
    if (s.contrast && s.contrast !== 100)
        filters.push(`contrast(${s.contrast}%)`);
    if (s.grayscale) filters.push(`grayscale(${s.grayscale}%)`);

    return {
        position: "absolute",
        left: (el.position?.x || 0) + "px",
        top: (el.position?.y || 0) + "px",
        width: (s.width || 200) + "px",
        height: (s.height || 50) + "px",
        zIndex: s.zIndex || 1,
        opacity: el.hidden ? 0 : (s.opacity ?? 100) / 100,
        transform:
            [
                s.rotate ? `rotate(${s.rotate}deg)` : "",
                s.scaleX === -1 ? "scaleX(-1)" : "",
                s.scaleY === -1 ? "scaleY(-1)" : "",
            ]
                .filter(Boolean)
                .join(" ") || "none",
        borderRadius: (s.borderRadius || 0) + "px",
        border: s.borderWidth
            ? `${s.borderWidth}px ${s.borderStyle || "solid"} ${s.borderColor || "#000"}`
            : "none",
        boxShadow: s.boxShadow || "none",
        filter: filters.length ? filters.join(" ") : "none",
        cursor: el.locked ? "not-allowed" : "move",
        userSelect: "none",
        overflow: "hidden",
    };
}

function elContentStyle(el) {
    const s = el.styles || {};
    return {
        width: "100%",
        height: "100%",
        overflow: "hidden",
        fontFamily: s.fontFamily ? `'${s.fontFamily}', sans-serif` : "inherit",
        fontSize: (s.fontSize || 14) + "px",
        fontWeight: s.fontWeight || "400",
        fontStyle: s.fontStyle || "normal",
        textDecoration: s.textDecoration || "none",
        color: s.color || "inherit",
        textAlign: s.textAlign || "left",
        lineHeight: s.lineHeight || 1.5,
        letterSpacing: s.letterSpacing ? s.letterSpacing + "px" : "normal",
        textTransform: s.textTransform || "none",
        padding: s.padding ? s.padding + "px" : "0",
        backgroundColor: s.backgroundColor || "transparent",
    };
}

function shapeStyle(el) {
    const s = el.styles || {};
    return {
        width: "100%",
        height: "100%",
        backgroundColor: s.backgroundColor || settings.primary_color,
        borderRadius:
            el.type === "circle" ? "50%" : (s.borderRadius || 0) + "px",
    };
}

function triangleStyle(el) {
    const s = el.styles || {};
    const color = s.backgroundColor || settings.primary_color;
    return {
        width: 0,
        height: 0,
        borderLeft: (s.width || 60) / 2 + "px solid transparent",
        borderRight: (s.width || 60) / 2 + "px solid transparent",
        borderBottom: (s.height || 80) + "px solid " + color,
        backgroundColor: "transparent",
    };
}

function dividerStyle(el) {
    const s = el.styles || {};
    return {
        width: "100%",
        height: (s.borderWidth || 1) + "px",
        backgroundColor: s.color || "#e2e8f0",
        marginTop: (s.height || 4) / 2 - (s.borderWidth || 1) / 2 + "px",
    };
}

function arrowStyle(el) {
    return { width: "100%", height: "100%" };
}

function calloutStyle(el) {
    const s = el.styles || {};
    return {
        width: "100%",
        height: "100%",
        display: "flex",
        gap: "12px",
        padding: "16px",
        backgroundColor: s.backgroundColor || `${settings.primary_color}12`,
        borderLeft: `4px solid ${s.borderColor || settings.primary_color}`,
        borderRadius: (s.borderRadius || 8) + "px",
        alignItems: "flex-start",
        overflow: "hidden",
    };
}

function metricStyle(el) {
    const s = el.styles || {};
    return {
        width: "100%",
        height: "100%",
        padding: "16px",
        backgroundColor: s.backgroundColor || "#f8fafc",
        borderRadius: (s.borderRadius || 12) + "px",
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
    };
}

function tableStyle(el) {
    return { width: "100%", borderCollapse: "collapse", fontSize: "12px" };
}

function tableHeaderStyle(el) {
    return {
        backgroundColor: settings.primary_color,
        color: "#fff",
        padding: "8px 12px",
        textAlign: "left",
        fontWeight: 600,
    };
}

function testimonialStyle(el) {
    const s = el.styles || {};
    return {
        width: "100%",
        height: "100%",
        padding: "20px",
        backgroundColor: s.backgroundColor || "#f8fafc",
        borderRadius: (s.borderRadius || 12) + "px",
        border: `1px solid ${s.borderColor || "#e2e8f0"}`,
        display: "flex",
        flexDirection: "column",
        gap: "8px",
        overflow: "hidden",
    };
}

function thumbElStyle(el) {
    const scale = 0.13;
    const s = el.styles || {};
    return {
        position: "absolute",
        left: (el.position?.x || 0) * scale + "px",
        top: (el.position?.y || 0) * scale + "px",
        width: Math.max(4, (s.width || 100) * scale) + "px",
        height: Math.max(2, (s.height || 50) * scale) + "px",
        backgroundColor:
            s.backgroundColor || settings.primary_color || "#6366f1",
        borderRadius: "1px",
        opacity: 0.7,
    };
}

// ──────────────────────────────────────────────────────────────────
// CORE ELEMENT OPERATIONS
// ──────────────────────────────────────────────────────────────────
function createEl(def, x, y) {
    const snap = snapToGrid.value ? Math.round : (v) => v;
    const el = {
        id: uid(),
        type: def.type,
        content: def.defaultContent || getPlaceholder(def.type),
        position: {
            x: snap(x - (def.w || 200) / 2),
            y: snap(y - (def.h || 50) / 2),
        },
        styles: {
            width: def.w || 200,
            height: def.h || 50,
            fontSize:
                def.type === "heading"
                    ? 28
                    : def.type === "subheading"
                      ? 20
                      : 14,
            fontWeight:
                def.type === "heading"
                    ? "700"
                    : def.type === "subheading"
                      ? "600"
                      : "400",
            fontFamily: settings.font_family || "Inter",
            color:
                def.type === "heading"
                    ? settings.primary_color || "#6366f1"
                    : "#1e293b",
            textAlign: "left",
            lineHeight: 1.5,
            backgroundColor: ["rectangle", "circle"].includes(def.type)
                ? settings.primary_color || "#6366f1"
                : "transparent",
            opacity: 100,
            borderRadius: 0,
            rotate: 0,
            zIndex: (currentPageElements.value.length || 0) + 1,
        },
        locked: false,
        hidden: false,
    };

    // Type-specific defaults
    if (def.type === "metric") {
        el.label = "Revenue";
        el.value = "$48,290";
        el.change = "+12.5%";
        el.changeType = "positive";
        el.styles.backgroundColor = "#f8fafc";
        el.styles.borderRadius = 12;
    }
    if (def.type === "table") {
        el.columns = ["Column 1", "Column 2", "Column 3"];
        el.data = [
            {
                "Column 1": "Data 1",
                "Column 2": "Data 2",
                "Column 3": "Data 3",
            },
            {
                "Column 1": "Data 4",
                "Column 2": "Data 5",
                "Column 3": "Data 6",
            },
        ];
        el.styles.backgroundColor = "#fff";
    }
    if (isChart(def.type)) {
        el.chartTitle = def.label;
        el.chartData = {
            labels: ["Q1", "Q2", "Q3", "Q4"],
            values: [25, 40, 35, 55],
        };
        el.styles.backgroundColor = "#fff";
        el.styles.borderRadius = 8;
    }
    if (def.type === "progress") {
        el.label = "Progress";
        el.value = 65;
        el.styles.backgroundColor = "transparent";
    }
    if (def.type === "timeline") {
        el.items = [
            {
                date: "2024 Q1",
                label: "Project Start",
                desc: "Initial planning phase",
            },
            {
                date: "2024 Q2",
                label: "Development",
                desc: "Core development begins",
            },
            { date: "2024 Q3", label: "Launch", desc: "Product goes live" },
        ];
        el.styles.backgroundColor = "transparent";
    }
    if (def.type === "checklist") {
        el.items = [
            { text: "Design mockups", checked: true },
            { text: "Development", checked: false },
            { text: "Testing", checked: false },
        ];
        el.styles.backgroundColor = "transparent";
    }
    if (def.type === "stat-row") {
        el.stats = [
            { value: "12.4K", label: "Users" },
            { value: "$48K", label: "Revenue" },
            { value: "94%", label: "Satisfaction" },
        ];
        el.styles.backgroundColor = "#f8fafc";
        el.styles.borderRadius = 8;
    }
    if (def.type === "testimonial") {
        el.author = def.author_ || "Jane Doe";
        el.role = def.role_ || "CEO";
        el.styles.borderRadius = 12;
    }
    if (def.type === "signature") {
        el.label = "Authorized Signature";
        el.styles.backgroundColor = "transparent";
    }
    if (def.type === "callout") {
        el.emoji = "💡";
        el.styles.backgroundColor = `${settings.primary_color}12`;
        el.styles.borderRadius = 8;
    }
    if (def.type === "rating") {
        el.value = 4;
        el.styles.backgroundColor = "transparent";
    }
    if (def.type === "quote") {
        el.styles.borderLeft = `4px solid ${settings.primary_color}`;
        el.styles.paddingLeft = 16;
    }

    return el;
}

function addElement(def, x, y) {
    pushUndo();
    const page = report.content[currentPage.value];
    if (!page) return;
    const el = createEl(def, x, y);
    page.elements.push(el);
    selectedElIdx.value = page.elements.length - 1;
    markDirty();
    return el;
}

function addElementCenter(def) {
    const { w, h } = pageDims.value;
    addElement(def, w / 2, h / 3);
}

function deleteSelected() {
    if (!selectedEl.value) return;
    pushUndo();
    currentPageElements.value.splice(selectedElIdx.value, 1);
    selectedElIdx.value = null;
    markDirty();
    showMsg("Element deleted", "info");
}

function duplicateSelected() {
    if (!selectedEl.value) return;
    pushUndo();
    const copy = JSON.parse(JSON.stringify(selectedEl.value));
    copy.id = uid();
    copy.position.x += 20;
    copy.position.y += 20;
    copy.styles.zIndex = (copy.styles.zIndex || 1) + 1;
    currentPageElements.value.push(copy);
    selectedElIdx.value = currentPageElements.value.length - 1;
    markDirty();
}

function copyElement() {
    if (!selectedEl.value) return;
    clipboard = JSON.parse(JSON.stringify(selectedEl.value));
    showMsg("Copied to clipboard", "success");
}

function pasteElement() {
    if (!clipboard) return;
    pushUndo();
    const copy = JSON.parse(JSON.stringify(clipboard));
    copy.id = uid();
    copy.position.x += 30;
    copy.position.y += 30;
    currentPageElements.value.push(copy);
    selectedElIdx.value = currentPageElements.value.length - 1;
    markDirty();
}

function lockElement() {
    if (!selectedEl.value) return;
    selectedEl.value.locked = !selectedEl.value.locked;
    markDirty();
}

function bringToFront() {
    if (!selectedEl.value) return;
    selectedEl.value.styles.zIndex =
        Math.max(
            ...currentPageElements.value.map((e) => e.styles?.zIndex || 1),
        ) + 1;
    markDirty();
}

function bringForward() {
    if (!selectedEl.value) return;
    selectedEl.value.styles.zIndex = (selectedEl.value.styles.zIndex || 1) + 5;
    markDirty();
}

function sendBackward() {
    if (!selectedEl.value) return;
    selectedEl.value.styles.zIndex = Math.max(
        1,
        (selectedEl.value.styles.zIndex || 1) - 5,
    );
    markDirty();
}

function sendToBack() {
    if (!selectedEl.value) return;
    selectedEl.value.styles.zIndex = 1;
    markDirty();
}

function alignToPage(dir) {
    if (!selectedEl.value) return;
    const { w, h } = pageDims.value;
    const el = selectedEl.value;
    const ew = el.styles.width,
        eh = el.styles.height;
    if (dir === "left") el.position.x = settings.margin || 0;
    else if (dir === "right") el.position.x = w - ew - (settings.margin || 0);
    else if (dir === "center-h") el.position.x = (w - ew) / 2;
    else if (dir === "top") el.position.y = settings.margin || 0;
    else if (dir === "bottom") el.position.y = h - eh - (settings.margin || 0);
    else if (dir === "center-v") el.position.y = (h - eh) / 2;
    markDirty();
}

function alignElements(dir) {
    if (selectedEls.value.length < 2) return;
    pushUndo();
    const els = selectedEls.value.map((i) => currentPageElements.value[i]);
    if (dir === "left") {
        const minX = Math.min(...els.map((e) => e.position.x));
        els.forEach((e) => (e.position.x = minX));
    }
    if (dir === "center-h") {
        const cx =
            els.reduce((s, e) => s + e.position.x + e.styles.width / 2, 0) /
            els.length;
        els.forEach((e) => (e.position.x = cx - e.styles.width / 2));
    }
    if (dir === "right") {
        const maxX = Math.max(...els.map((e) => e.position.x + e.styles.width));
        els.forEach((e) => (e.position.x = maxX - e.styles.width));
    }
    markDirty();
}

function distributeElements() {
    if (selectedEls.value.length < 3) return;
    const els = selectedEls.value
        .map((i) => currentPageElements.value[i])
        .sort((a, b) => a.position.x - b.position.x);
    const totalW = els.reduce((s, e) => s + e.styles.width, 0);
    const space =
        (els[els.length - 1].position.x +
            els[els.length - 1].styles.width -
            els[0].position.x -
            totalW) /
        (els.length - 1);
    let x = els[0].position.x;
    els.forEach((e) => {
        e.position.x = x;
        x += e.styles.width + space;
    });
    markDirty();
}

function groupSelected() {
    showMsg("Group feature coming soon", "info");
}

function selectElementByIdx(idx) {
    selectedElIdx.value = idx;
    editingElIdx.value = null;
    if (selectedEl.value) {
        const s = selectedEl.value.styles || {};
        fmt.fontFamily = s.fontFamily || settings.font_family || "Inter";
        fmt.fontSize = s.fontSize || 14;
        fmt.fontWeight = s.fontWeight || "400";
        fmt.fontStyle = s.fontStyle || "normal";
        fmt.textDecoration = s.textDecoration || "none";
        fmt.textAlign = s.textAlign || "left";
        fmt.color = s.color || "#000000";
        fmt.backgroundColor = s.backgroundColor || "transparent";
    }
}

function deselectAll() {
    selectedElIdx.value = null;
    selectedEls.value = [];
    editingElIdx.value = null;
    contextMenu.show = false;
}

function startEditing(idx) {
    if (currentPageElements.value[idx]?.locked) return;
    selectElementByIdx(idx);
    editingElIdx.value = idx;
}

function applyStyle(prop, val) {
    if (!selectedEl.value) return;
    if (!selectedEl.value.styles) selectedEl.value.styles = {};
    selectedEl.value.styles[prop] = val;
    markDirty();
}

function toggleFmt(prop, onVal, offVal) {
    if (!selectedEl.value?.styles) return;
    const cur = selectedEl.value.styles[prop];
    applyStyle(prop, cur === onVal ? offVal : onVal);
    fmt[prop] = selectedEl.value.styles[prop];
}

function setChartLabels(str) {
    if (!selectedEl.value) return;
    if (!selectedEl.value.chartData) selectedEl.value.chartData = {};
    selectedEl.value.chartData.labels = str.split(",").map((s) => s.trim());
    markDirty();
}

function setChartValues(str) {
    if (!selectedEl.value) return;
    if (!selectedEl.value.chartData) selectedEl.value.chartData = {};
    selectedEl.value.chartData.values = str
        .split(",")
        .map((s) => +s.trim())
        .filter((v) => !isNaN(v));
    markDirty();
}

function addTableColumn() {
    if (!selectedEl.value) return;
    const col = `Col ${(selectedEl.value.columns?.length || 0) + 1}`;
    selectedEl.value.columns = [...(selectedEl.value.columns || []), col];
    selectedEl.value.data = (selectedEl.value.data || []).map((r) => ({
        ...r,
        [col]: "",
    }));
    markDirty();
}

function removeTableColumn(ci) {
    if (!selectedEl.value) return;
    const col = selectedEl.value.columns[ci];
    selectedEl.value.columns.splice(ci, 1);
    selectedEl.value.data = (selectedEl.value.data || []).map((r) => {
        const nr = { ...r };
        delete nr[col];
        return nr;
    });
    markDirty();
}

function addTableRow() {
    if (!selectedEl.value) return;
    const row = {};
    (selectedEl.value.columns || []).forEach((c) => (row[c] = ""));
    selectedEl.value.data = [...(selectedEl.value.data || []), row];
    markDirty();
}

// ──────────────────────────────────────────────────────────────────
// DRAG & DROP
// ──────────────────────────────────────────────────────────────────
function onElDragStart(e, def) {
    e.dataTransfer.setData("el-def", JSON.stringify(def));
    isDraggingEl.value = true;
}

function onMediaDragStart(e, img) {
    e.dataTransfer.setData(
        "el-def",
        JSON.stringify({ type: "image", w: 300, h: 200, src: img.url }),
    );
}

function onCanvasDrop(e) {
    isDraggingEl.value = false;
    const rect = pageEl.value?.getBoundingClientRect();
    if (!rect) return;

    const scale = zoom.value / 100;
    const x = (e.clientX - rect.left) / scale;
    const y = (e.clientY - rect.top) / scale;

    const defStr = e.dataTransfer.getData("el-def");
    if (!defStr) return;
    const def = JSON.parse(defStr);

    const el = addElement(def, x, y);
    if (def.src && el) el.src = def.src;
}

// ──────────────────────────────────────────────────────────────────
// MOUSE INTERACTION
// ──────────────────────────────────────────────────────────────────
function onElementMouseDown(e, idx) {
    e.stopPropagation();
    const el = currentPageElements.value[idx];
    if (!el || el.locked) return;

    if (e.shiftKey) {
        if (selectedEls.value.includes(idx)) {
            selectedEls.value = selectedEls.value.filter((i) => i !== idx);
        } else {
            selectedEls.value = [...selectedEls.value, idx];
            if (
                !selectedEls.value.includes(selectedElIdx.value) &&
                selectedElIdx.value !== null
            )
                selectedEls.value.push(selectedElIdx.value);
        }
    } else {
        if (!selectedEls.value.includes(idx)) {
            selectedEls.value = [];
            selectElementByIdx(idx);
        }
    }

    if (selectedEls.value.length <= 1) selectElementByIdx(idx);

    const scale = zoom.value / 100;
    dragInfo = {
        startX: e.clientX,
        startY: e.clientY,
        els: (selectedEls.value.length > 1 ? selectedEls.value : [idx]).map(
            (i) => ({
                idx: i,
                ox: currentPageElements.value[i].position.x,
                oy: currentPageElements.value[i].position.y,
            }),
        ),
        scale,
    };

    window.addEventListener("mousemove", onDragMove);
    window.addEventListener("mouseup", onDragEnd);
}

function onDragMove(e) {
    if (!dragInfo) return;
    const dx = (e.clientX - dragInfo.startX) / dragInfo.scale;
    const dy = (e.clientY - dragInfo.startY) / dragInfo.scale;

    dragInfo.els.forEach(({ idx, ox, oy }) => {
        const el = currentPageElements.value[idx];
        if (!el) return;
        let nx = ox + dx,
            ny = oy + dy;
        if (snapToGrid.value) {
            nx = Math.round(nx / 10) * 10;
            ny = Math.round(ny / 10) * 10;
        }
        el.position.x = nx;
        el.position.y = ny;
    });
}

function onDragEnd() {
    if (dragInfo) {
        markDirty();
        dragInfo = null;
    }
    window.removeEventListener("mousemove", onDragMove);
    window.removeEventListener("mouseup", onDragEnd);
}

// Resize
function startResize(e, idx, handle) {
    e.stopPropagation();
    e.preventDefault();
    const el = currentPageElements.value[idx];
    const scale = zoom.value / 100;
    resizeInfo = {
        idx,
        handle,
        scale,
        startX: e.clientX,
        startY: e.clientY,
        ox: el.position.x,
        oy: el.position.y,
        ow: el.styles.width,
        oh: el.styles.height,
    };
    window.addEventListener("mousemove", onResizeMove);
    window.addEventListener("mouseup", onResizeEnd);
}

function onResizeMove(e) {
    if (!resizeInfo) return;
    const { idx, handle, scale, startX, startY, ox, oy, ow, oh } = resizeInfo;
    const el = currentPageElements.value[idx];
    if (!el) return;
    const dx = (e.clientX - startX) / scale;
    const dy = (e.clientY - startY) / scale;
    const MIN = 20;

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
    if (snapToGrid.value) {
        el.styles.width = Math.round(el.styles.width / 10) * 10;
        el.styles.height = Math.round(el.styles.height / 10) * 10;
    }
}

function onResizeEnd() {
    if (resizeInfo) {
        markDirty();
        resizeInfo = null;
    }
    window.removeEventListener("mousemove", onResizeMove);
    window.removeEventListener("mouseup", onResizeEnd);
}

// Rotate
function startRotate(e, idx) {
    e.stopPropagation();
    e.preventDefault();
    const el = currentPageElements.value[idx];
    const rect = pageEl.value?.getBoundingClientRect();
    if (!rect) return;
    const scale = zoom.value / 100;
    const cx = rect.left + (el.position.x + el.styles.width / 2) * scale;
    const cy = rect.top + (el.position.y + el.styles.height / 2) * scale;
    rotateInfo = { idx, cx, cy };
    window.addEventListener("mousemove", onRotateMove);
    window.addEventListener("mouseup", onRotateEnd);
}

function onRotateMove(e) {
    if (!rotateInfo) return;
    const el = currentPageElements.value[rotateInfo.idx];
    if (!el) return;
    const angle =
        (Math.atan2(e.clientY - rotateInfo.cy, e.clientX - rotateInfo.cx) *
            180) /
            Math.PI +
        90;
    el.styles.rotate = Math.round(angle);
}

function onRotateEnd() {
    if (rotateInfo) {
        markDirty();
        rotateInfo = null;
    }
    window.removeEventListener("mousemove", onRotateMove);
    window.removeEventListener("mouseup", onRotateEnd);
}

// Rubber band
function startRubberBand(e) {
    if (e.target !== canvasArea.value && e.target !== pageEl.value) return;
    const rect = canvasArea.value?.getBoundingClientRect();
    if (!rect) return;
    rubberBand.active = true;
    rubberBand.startX = e.clientX - rect.left;
    rubberBand.startY = e.clientY - rect.top;
    rubberBand.x = rubberBand.startX;
    rubberBand.y = rubberBand.startY;
    rubberBand.w = 0;
    rubberBand.h = 0;
}

function handleCanvasMouseMove(e) {
    if (!rubberBand.active) return;
    const rect = canvasArea.value?.getBoundingClientRect();
    if (!rect) return;
    const mx = e.clientX - rect.left,
        my = e.clientY - rect.top;
    rubberBand.x = Math.min(mx, rubberBand.startX);
    rubberBand.y = Math.min(my, rubberBand.startY);
    rubberBand.w = Math.abs(mx - rubberBand.startX);
    rubberBand.h = Math.abs(my - rubberBand.startY);
}

function endRubberBand() {
    if (!rubberBand.active) return;
    rubberBand.active = false;
    // Select elements within band
    if (rubberBand.w > 5 && rubberBand.h > 5) {
        const rect = pageEl.value?.getBoundingClientRect();
        if (rect) {
            const scale = zoom.value / 100;
            const pageLeft = rect.left;
            const pageTop = rect.top;
            const bx =
                (rubberBand.x -
                    (pageLeft -
                        canvasArea.value?.getBoundingClientRect().left)) /
                scale;
            const by =
                (rubberBand.y -
                    (pageTop - canvasArea.value?.getBoundingClientRect().top)) /
                scale;
            const bw = rubberBand.w / scale,
                bh = rubberBand.h / scale;

            const sel = currentPageElements.value.reduce((acc, el, i) => {
                if (
                    el.position.x < bx + bw &&
                    el.position.x + el.styles.width > bx &&
                    el.position.y < by + bh &&
                    el.position.y + el.styles.height > by
                )
                    acc.push(i);
                return acc;
            }, []);
            if (sel.length) {
                selectedEls.value = sel;
                selectedElIdx.value = sel[0];
            }
        }
    }
}

function handleZoomWheel(e) {
    if (e.deltaY < 0) zoomIn();
    else zoomOut();
}

function onPageDblClick(e) {
    // dblclick on empty canvas: add text
    const rect = pageEl.value?.getBoundingClientRect();
    if (!rect) return;
    const scale = zoom.value / 100;
    const x = (e.clientX - rect.left) / scale;
    const y = (e.clientY - rect.top) / scale;
    addElement({ type: "text", w: 200, h: 40 }, x, y);
}

// ──────────────────────────────────────────────────────────────────
// CONTEXT MENU
// ──────────────────────────────────────────────────────────────────
function showElContextMenu(e, idx) {
    selectElementByIdx(idx);
    const el = currentPageElements.value[idx];
    contextMenu.show = true;
    contextMenu.x = e.clientX;
    contextMenu.y = e.clientY;
    contextMenu.items = [
        {
            label: "Edit Content",
            icon: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
            action: () => startEditing(idx),
        },
        {
            label: "Duplicate",
            icon: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>',
            shortcut: "Ctrl+D",
            action: () => duplicateSelected(),
        },
        {
            label: "Copy",
            shortcut: "Ctrl+C",
            icon: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>',
            action: copyElement,
        },
        { separator: true, label: "—", icon: "", action: () => {} },
        { label: "Bring to Front", icon: "", action: bringToFront },
        { label: "Send to Back", icon: "", action: sendToBack },
        { separator: true, label: "—", icon: "", action: () => {} },
        { label: el.locked ? "Unlock" : "Lock", icon: "", action: lockElement },
        {
            label: "Delete",
            icon: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>',
            shortcut: "Del",
            danger: true,
            action: deleteSelected,
        },
    ];
}

function closeAllMenus() {
    contextMenu.show = false;
    showExportMenu.value = false;
}

// ──────────────────────────────────────────────────────────────────
// PAGE OPERATIONS
// ──────────────────────────────────────────────────────────────────
function addPage() {
    pushUndo();
    report.content.push({
        id: uid(),
        label: `Page ${report.content.length + 1}`,
        elements: [],
        // Inherit template settings
        settings: JSON.parse(JSON.stringify(settings)),
    });
    goToPage(report.content.length - 1);
    markDirty();
}

function duplicatePage(idx) {
    pushUndo();
    const copy = JSON.parse(JSON.stringify(report.content[idx]));
    copy.id = uid();
    copy.elements = copy.elements.map((el) => ({ ...el, id: uid() }));
    copy.label = (copy.label || `Page ${idx + 1}`) + " (Copy)";
    report.content.splice(idx + 1, 0, copy);
    goToPage(idx + 1);
    markDirty();
}

function deletePage(idx) {
    if (report.content.length <= 1) {
        showMsg("Cannot delete the only page", "error");
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

function goToPage(idx) {
    selectedElIdx.value = null;
    selectedEls.value = [];
    currentPage.value = idx;
}

function pageContextMenu(e, idx) {
    contextMenu.show = true;
    contextMenu.x = e.clientX;
    contextMenu.y = e.clientY;
    contextMenu.items = [
        {
            label: "Add Page After",
            icon: "",
            action: () => {
                goToPage(idx);
                addPage();
            },
        },
        { label: "Duplicate Page", icon: "", action: () => duplicatePage(idx) },
        {
            label: "Delete Page",
            icon: "",
            danger: true,
            action: () => deletePage(idx),
        },
    ];
}

function toggleCat(name) {
    const idx = collapsedCats.value.indexOf(name);
    if (idx >= 0) collapsedCats.value.splice(idx, 1);
    else collapsedCats.value.push(name);
}

// ──────────────────────────────────────────────────────────────────
// UNDO / REDO
// ──────────────────────────────────────────────────────────────────
function pushUndo() {
    undoStack.value.push(JSON.stringify({ content: report.content, settings }));
    if (undoStack.value.length > 80) undoStack.value.shift();
    redoStack.value = [];
}

function undo() {
    if (!undoStack.value.length) return;
    redoStack.value.push(JSON.stringify({ content: report.content, settings }));
    const state = JSON.parse(undoStack.value.pop());
    Object.assign(report.content, state.content);
    report.content.length = state.content.length;
    Object.assign(settings, state.settings);
    markDirty();
}

function redo() {
    if (!redoStack.value.length) return;
    undoStack.value.push(JSON.stringify({ content: report.content, settings }));
    const state = JSON.parse(redoStack.value.pop());
    Object.assign(report.content, state.content);
    report.content.length = state.content.length;
    Object.assign(settings, state.settings);
    markDirty();
}

// ──────────────────────────────────────────────────────────────────
// ZOOM
// ──────────────────────────────────────────────────────────────────
function zoomIn() {
    zoom.value = Math.min(zoom.value + 10, 400);
}
function zoomOut() {
    zoom.value = Math.max(zoom.value - 10, 25);
}
function cycleZoom() {
    const presets = [50, 75, 100, 125, 150, 200];
    const idx = presets.findIndex((p) => p >= zoom.value);
    zoom.value =
        idx >= presets.length - 1 ? presets[0] : presets[idx + 1] || 100;
}

function fitToPage() {
    if (!canvasArea.value) return;
    const areaW = canvasArea.value.clientWidth - 80;
    const areaH = canvasArea.value.clientHeight - 80;
    const ratioW = areaW / pageDims.value.w;
    const ratioH = areaH / pageDims.value.h;
    zoom.value = Math.round(Math.min(ratioW, ratioH) * 100);
}

// ──────────────────────────────────────────────────────────────────
// SAVE / EXPORT
// ──────────────────────────────────────────────────────────────────
function markDirty() {
    isDirty.value = true;
    report.settings = JSON.parse(JSON.stringify(settings));
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(autoSave, 1500);
}

async function autoSave() {
    if (!isDirty.value) return;
    saving.value = true;
    try {
        await axios.put(route("reports.update", report.slug), {
            title: report.title,
            content: report.content,
            settings: settings,
        });
        isDirty.value = false;
        lastSaved.value = new Date().toLocaleTimeString();
    } catch (err) {
        console.error("Auto-save failed:", err);
    }
    saving.value = false;
}

function previewReport() {
    window.open(route("reports.preview", report.slug), "_blank");
}

function downloadPDF() {
    showExportMenu.value = false;
    window.open(route("reports.download", report.slug), "_blank");
}

function exportImage() {
    showExportMenu.value = false;
    window.open(route("reports.export.image", report.slug), "_blank");
}

function exportCSV() {
    showExportMenu.value = false;
    window.open(route("reports.export.csv", report.slug), "_blank");
}

function exportExcel() {
    showExportMenu.value = false;
    window.open(route("reports.export.excel", report.slug), "_blank");
}

async function shareReport() {
    showExportMenu.value = false;
    try {
        const res = await axios.post(route("reports.share", report.slug));
        await navigator.clipboard.writeText(res.data.url);
        showMsg("Share link copied to clipboard!", "success");
    } catch {
        showMsg("Could not generate share link", "error");
    }
}

function goBack() {
    router.get(route("reports.index"));
}

// ──────────────────────────────────────────────────────────────────
// TEMPLATE APPLICATION
// ──────────────────────────────────────────────────────────────────
function applyQuickTemplate(tpl) {
    // Apply gradient as background
    const page = report.content[currentPage.value];
    if (!page) return;
    // Extract first color from gradient and use as primary
    const match = tpl.gradient.match(/#[a-f0-9]{6}/gi);
    if (match && match.length >= 2) {
        settings.background_color = match[1];
        settings.primary_color = match[0];
        markDirty();
        showMsg(`Applied ${tpl.name} template`, "success");
    }
}

// ──────────────────────────────────────────────────────────────────
// IMAGE HANDLING
// ──────────────────────────────────────────────────────────────────
function triggerImageUpload() {
    imageInput.value?.click();
}

function handleImageUpload(e) {
    const files = e.target.files;
    if (!files) return;
    Array.from(files).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (ev) =>
            uploadedImages.value.push({
                url: ev.target.result,
                name: file.name,
            });
        reader.readAsDataURL(file);
    });
}

function handleMediaDrop(e) {
    const files = e.dataTransfer.files;
    if (!files) return;
    Array.from(files).forEach((file) => {
        if (!file.type.startsWith("image/")) return;
        const reader = new FileReader();
        reader.onload = (ev) =>
            uploadedImages.value.push({
                url: ev.target.result,
                name: file.name,
            });
        reader.readAsDataURL(file);
    });
}

function addImageToCanvas(img) {
    addElement(
        { type: "image", w: 300, h: 200, src: img.url },
        pageDims.value.w / 2,
        pageDims.value.h / 3,
    );
}

function triggerImagePick(el) {
    currentElForImage.value = el;
    imageInputEl.value?.click();
}

function triggerImageReplace(el) {
    currentElForImage.value = el;
    imageInputEl.value?.click();
}

function handleImagePickChange(e) {
    const file = e.target.files?.[0];
    if (!file || !currentElForImage.value) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        currentElForImage.value.src = ev.target.result;
        currentElForImage.value = null;
        markDirty();
    };
    reader.readAsDataURL(file);
}

// ──────────────────────────────────────────────────────────────────
// AI ASSISTANT
// ──────────────────────────────────────────────────────────────────
async function sendAI() {
    if (!aiPrompt.value.trim() || aiLoading.value) return;
    const msg = aiPrompt.value;
    aiPrompt.value = "";
    aiConvo.value.push({ role: "user", text: msg });
    aiLoading.value = true;

    try {
        const res = await axios.post("/api/ai/generate", {
            prompt: msg,
            type: "text",
        });
        aiConvo.value.push({ role: "assistant", text: res.data.result });
    } catch {
        aiConvo.value.push({
            role: "assistant",
            text: "Sorry, AI service is unavailable right now.",
        });
    }
    aiLoading.value = false;
    nextTick(() => {
        if (aiMessages.value)
            aiMessages.value.scrollTop = aiMessages.value.scrollHeight;
    });
}

// ──────────────────────────────────────────────────────────────────
// KEYBOARD SHORTCUTS
// ──────────────────────────────────────────────────────────────────
function handleKeyboard(e) {
    const ctrl = e.ctrlKey || e.metaKey;
    const editing =
        e.target.tagName === "INPUT" ||
        e.target.tagName === "TEXTAREA" ||
        e.target.isContentEditable;

    if (ctrl && e.key === "z") {
        e.preventDefault();
        undo();
        return;
    }
    if (ctrl && e.key === "y") {
        e.preventDefault();
        redo();
        return;
    }
    if (ctrl && e.key === "s") {
        e.preventDefault();
        autoSave();
        return;
    }
    if (ctrl && e.key === "d") {
        e.preventDefault();
        duplicateSelected();
        return;
    }
    if (ctrl && e.key === "c") {
        if (!editing) {
            e.preventDefault();
            copyElement();
        }
        return;
    }
    if (ctrl && e.key === "v") {
        if (!editing) {
            e.preventDefault();
            pasteElement();
        }
        return;
    }
    if (ctrl && e.key === "a") {
        if (!editing) {
            e.preventDefault();
            selectedEls.value = currentPageElements.value.map((_, i) => i);
            selectedElIdx.value = 0;
        }
        return;
    }
    if (ctrl && e.key === "g") {
        e.preventDefault();
        groupSelected();
        return;
    }
    if (ctrl && e.key === " ") {
        e.preventDefault();
        showAI.value = !showAI.value;
        return;
    }
    if ((ctrl && e.key === "+") || (ctrl && e.key === "=")) {
        e.preventDefault();
        zoomIn();
        return;
    }
    if (ctrl && e.key === "-") {
        e.preventDefault();
        zoomOut();
        return;
    }
    if (ctrl && e.key === "0") {
        e.preventDefault();
        zoom.value = 100;
        return;
    }
    if (ctrl && e.key === "b" && !editing) {
        e.preventDefault();
        toggleFmt("fontWeight", "700", "400");
        return;
    }
    if (ctrl && e.key === "i" && !editing) {
        e.preventDefault();
        toggleFmt("fontStyle", "italic", "normal");
        return;
    }
    if (ctrl && e.key === "u" && !editing) {
        e.preventDefault();
        toggleFmt("textDecoration", "underline", "none");
        return;
    }
    if (e.key === "F11") {
        e.preventDefault();
        toggleFullscreen();
        return;
    }

    if (!editing && selectedEl.value) {
        const STEP = e.shiftKey ? 10 : 1;
        if (e.key === "Delete" || e.key === "Backspace") {
            e.preventDefault();
            deleteSelected();
            return;
        }
        if (e.key === "Escape") {
            e.preventDefault();
            deselectAll();
            return;
        }
        if (e.key === "ArrowLeft") {
            e.preventDefault();
            selectedEl.value.position.x -= STEP;
            markDirty();
            return;
        }
        if (e.key === "ArrowRight") {
            e.preventDefault();
            selectedEl.value.position.x += STEP;
            markDirty();
            return;
        }
        if (e.key === "ArrowUp") {
            e.preventDefault();
            selectedEl.value.position.y -= STEP;
            markDirty();
            return;
        }
        if (e.key === "ArrowDown") {
            e.preventDefault();
            selectedEl.value.position.y += STEP;
            markDirty();
            return;
        }
    }
}

// ──────────────────────────────────────────────────────────────────
// UI HELPERS
// ──────────────────────────────────────────────────────────────────
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

function showMsg(message, type = "success") {
    toast.message = message;
    toast.type = type;
    toast.show = true;
    setTimeout(() => (toast.show = false), 3000);
}

// ──────────────────────────────────────────────────────────────────
// LIFECYCLE
// ──────────────────────────────────────────────────────────────────
onMounted(() => {
    document.documentElement.classList.toggle("dark", isDark.value);
    nextTick(fitToPage);
    document.addEventListener("fullscreenchange", () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
    document.addEventListener("click", closeAllMenus);

    // Init rulers
    if (showRulers.value) drawRulers();
});

onBeforeUnmount(() => {
    clearTimeout(saveTimeout);
    document.removeEventListener("click", closeAllMenus);
    window.removeEventListener("mousemove", onDragMove);
    window.removeEventListener("mouseup", onDragEnd);
    window.removeEventListener("mousemove", onResizeMove);
    window.removeEventListener("mouseup", onResizeEnd);
    window.removeEventListener("mousemove", onRotateMove);
    window.removeEventListener("mouseup", onRotateEnd);
});

watch(showRulers, (v) => {
    if (v) nextTick(drawRulers);
});
watch(zoom, () => {
    if (showRulers.value) nextTick(drawRulers);
});

function drawRulers() {
    const hc = rulerTopCanvas.value;
    const vc = rulerLeftCanvas.value;
    if (!hc || !vc) return;
    const scale = zoom.value / 100;
    // Horizontal ruler
    const hCtx = hc.getContext("2d");
    hc.width = pageDims.value.w * scale + 60;
    hc.height = 20;
    hCtx.fillStyle = isDark.value ? "#1e293b" : "#f8fafc";
    hCtx.fillRect(0, 0, hc.width, hc.height);
    hCtx.strokeStyle = isDark.value ? "#334155" : "#cbd5e1";
    hCtx.fillStyle = isDark.value ? "#94a3b8" : "#64748b";
    hCtx.font = "9px monospace";
    for (let i = 0; i <= pageDims.value.w; i += 50) {
        const x = i * scale;
        hCtx.beginPath();
        hCtx.moveTo(x, 12);
        hCtx.lineTo(x, 20);
        hCtx.stroke();
        hCtx.fillText(i, x + 2, 10);
    }
}
</script>

<style>
/* ──────────────────────────────────────────── */
/* BASE                                          */
/* ──────────────────────────────────────────── */
.editor-shell {
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --bg-panel: #ffffff;
    --border: #e2e8f0;
    --border-light: #f1f5f9;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
    --accent: #6366f1;
    --accent-hover: #4f46e5;
    --accent-light: rgba(99, 102, 241, 0.08);
    --danger: #ef4444;
    --success: #10b981;
    --warning: #f59e0b;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);

    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    font-family: "Inter", "DM Sans", system-ui, sans-serif;
    background: var(--bg-tertiary);
    color: var(--text-primary);
    font-size: 13px;
    outline: none;
}

.editor-shell.dark {
    --bg-primary: #1e293b;
    --bg-secondary: #0f172a;
    --bg-tertiary: #020617;
    --bg-panel: #1e293b;
    --border: #334155;
    --border-light: #1e293b;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #475569;
    --accent-light: rgba(99, 102, 241, 0.15);
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.5);
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

/* ──────────────────────────────────────────── */
/* TOP BAR                                       */
/* ──────────────────────────────────────────── */
.top-bar {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 48px;
    padding: 0 12px;
    background: var(--bg-panel);
    border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    gap: 8px;
    z-index: 100;
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

.doc-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.doc-title-input {
    background: none;
    border: none;
    outline: none;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    min-width: 160px;
    max-width: 280px;
    padding: 4px 8px;
    border-radius: 6px;
}
.doc-title-input:hover {
    background: var(--bg-secondary);
}
.doc-title-input:focus {
    background: var(--bg-secondary);
    box-shadow: 0 0 0 2px var(--accent);
}

.status-pill {
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    text-transform: capitalize;
    letter-spacing: 0.03em;
}
.status-pill.draft {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
}
.status-pill.published {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}
.status-pill.archived {
    background: rgba(148, 163, 184, 0.12);
    color: #64748b;
}

.save-indicator {
    font-size: 11px;
    color: var(--text-muted);
    padding: 2px 8px;
}
.save-indicator.saving {
    color: var(--accent);
}
.save-indicator.saved {
    color: var(--success);
}

.divider-v {
    width: 1px;
    height: 24px;
    background: var(--border);
    flex-shrink: 0;
}

/* Buttons */
.icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    width: 30px;
    height: 30px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    color: var(--text-secondary);
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
    font-weight: 600;
}
.icon-btn.ai-btn.active {
    background: linear-gradient(
        135deg,
        rgba(99, 102, 241, 0.15),
        rgba(139, 92, 246, 0.1)
    );
    color: var(--accent);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border: none;
    background: var(--accent);
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
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
    gap: 6px;
    padding: 6px 12px;
    border: 1px solid var(--border);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 7px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.15s;
}
.btn-secondary:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-light);
}
.btn-secondary.full-width {
    width: 100%;
    justify-content: center;
}
.btn-secondary.danger {
    color: var(--danger);
}
.btn-secondary.danger:hover {
    border-color: var(--danger);
    background: rgba(239, 68, 68, 0.06);
}

/* Dropdowns */
.dropdown-wrap {
    position: relative;
}
.dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    right: 0;
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: var(--shadow-lg);
    padding: 4px;
    min-width: 200px;
    z-index: 200;
}
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text-primary);
    font-size: 12px;
    border-radius: 6px;
    text-align: left;
    transition: background 0.1s;
}
.dropdown-item:hover {
    background: var(--bg-secondary);
}

.item-icon {
    width: 24px;
    height: 18px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 700;
}
.item-icon.pdf {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}
.item-icon.img {
    background: rgba(139, 92, 246, 0.1);
    color: #7c3aed;
}
.item-icon.csv {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}
.item-icon.xls {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}
.menu-sep {
    border: none;
    border-top: 1px solid var(--border);
    margin: 4px 0;
}

.zoom-select {
    border: 1px solid var(--border);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 5px;
    padding: 3px 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    min-width: 52px;
    text-align: center;
}
.zoom-select:hover {
    border-color: var(--accent);
}

/* ──────────────────────────────────────────── */
/* RIBBON                                        */
/* ──────────────────────────────────────────── */
.ribbon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 2px;
    height: 40px;
    padding: 0 8px;
    background: var(--bg-panel);
    border-bottom: 1px solid var(--border);
    overflow-x: auto;
    scrollbar-width: none;
}
.ribbon::-webkit-scrollbar {
    display: none;
}
.ribbon-spacer {
    flex: 1;
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
.ribbon-btn.danger:hover {
    background: rgba(239, 68, 68, 0.08);
    color: var(--danger);
}
.ribbon-btn b {
    font-size: 13px;
}
.ribbon-btn.italic-btn i {
    font-style: italic;
}
.ribbon-btn.strikethrough s {
    text-decoration: line-through;
}

.ribbon-select {
    border: 1px solid var(--border);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 5px;
    padding: 3px 4px;
    font-size: 11px;
    cursor: pointer;
    height: 28px;
}
.ribbon-select:hover {
    border-color: var(--accent);
}
.ribbon-select.font-select {
    width: 120px;
}
.ribbon-select.size-select {
    width: 55px;
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
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    overflow: hidden;
    transition: border-color 0.15s;
}
.text-swatch {
    color: white;
    font-size: 12px;
    font-weight: 800;
    border: 2px solid var(--border);
}
.bg-swatch {
    color: var(--text-muted);
}
.color-picker-wrap input[type="color"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    top: 0;
    left: 0;
}

.btn-group {
    display: flex;
    gap: 1px;
    background: var(--bg-secondary);
    border-radius: 5px;
    padding: 2px;
}
.btn-group-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    border-radius: 4px;
    cursor: pointer;
    color: var(--text-secondary);
    transition: all 0.12s;
}
.btn-group-btn:hover {
    background: var(--bg-primary);
}
.btn-group-btn.active {
    background: var(--bg-primary);
    color: var(--accent);
    box-shadow: var(--shadow-sm);
}

/* ──────────────────────────────────────────── */
/* EDITOR BODY                                   */
/* ──────────────────────────────────────────── */
.editor-body {
    flex: 1;
    display: flex;
    overflow: hidden;
}

/* ──────────────────────────────────────────── */
/* LEFT PANEL                                    */
/* ──────────────────────────────────────────── */
.left-panel {
    width: 240px;
    flex-shrink: 0;
    background: var(--bg-panel);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: width 0.2s ease;
    position: relative;
}
.left-panel.collapsed {
    width: 0;
    border-right: none;
}

.panel-collapse-btn {
    position: absolute;
    right: -12px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--bg-panel);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    color: var(--text-muted);
    box-shadow: var(--shadow-sm);
    transition: all 0.15s;
}
.panel-collapse-btn:hover {
    color: var(--accent);
    border-color: var(--accent);
}

.panel-tabs {
    display: flex;
    gap: 2px;
    padding: 8px 8px 0;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
    flex-wrap: wrap;
}
.panel-tab {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 6px 8px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.02em;
    transition: all 0.15s;
    flex: 1;
    min-width: 0;
}
.panel-tab:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.panel-tab.active {
    background: var(--accent-light);
    color: var(--accent);
}
.panel-tab svg {
    flex-shrink: 0;
}
.tab-label {
    font-size: 8.5px;
    letter-spacing: 0.03em;
    white-space: nowrap;
}

.panel-content {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
}

/* Elements */
.search-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 7px;
    padding: 6px 10px;
    margin-bottom: 10px;
}
.search-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 12px;
    color: var(--text-primary);
}

.el-category {
    margin-bottom: 6px;
}
.cat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 4px;
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    border-radius: 4px;
}
.cat-header:hover {
    background: var(--bg-secondary);
}

.el-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px;
    padding: 4px 0;
}
.el-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 4px;
    border-radius: 7px;
    cursor: grab;
    border: 1px solid transparent;
    transition: all 0.15s;
    user-select: none;
    text-align: center;
}
.el-item:hover {
    background: var(--accent-light);
    border-color: rgba(99, 102, 241, 0.2);
    transform: translateY(-1px);
}
.el-item:active {
    cursor: grabbing;
    transform: scale(0.95);
}
.el-icon {
    color: var(--text-secondary);
}
.el-item:hover .el-icon {
    color: var(--accent);
}
.el-label {
    font-size: 9.5px;
    color: var(--text-muted);
    line-height: 1.2;
}
.el-item:hover .el-label {
    color: var(--text-primary);
}

/* Pages */
.add-page-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 8px;
    margin-bottom: 8px;
    border: 1.5px dashed var(--border);
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 500;
    transition: all 0.15s;
}
.add-page-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-light);
}

.pages-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.page-thumb {
    border: 1.5px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.15s;
    background: var(--bg-secondary);
}
.page-thumb:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-sm);
}
.page-thumb.active {
    border-color: var(--accent);
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}
.page-thumb-preview {
    height: 80px;
    position: relative;
    overflow: hidden;
}
.thumb-el {
    position: absolute;
    border-radius: 1px;
}
.page-thumb-info {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 8px;
    border-top: 1px solid var(--border);
}
.page-num {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    flex-shrink: 0;
}
.page-label-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 11px;
    color: var(--text-primary);
    outline: none;
    min-width: 0;
}
.page-actions {
    display: flex;
    gap: 2px;
}

/* Layers */
.layers-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 0;
    margin-bottom: 6px;
    font-weight: 600;
    font-size: 12px;
}
.layer-count {
    background: var(--bg-secondary);
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
}
.layers-list {
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.layer-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.1s;
}
.layer-item:hover {
    background: var(--bg-secondary);
}
.layer-item.active {
    background: var(--accent-light);
}
.layer-item.locked {
    opacity: 0.5;
}
.layer-item.hidden {
    opacity: 0.35;
}
.layer-type-icon {
    color: var(--text-muted);
    flex-shrink: 0;
}
.layer-name {
    flex: 1;
    font-size: 11px;
    color: var(--text-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.layer-controls {
    display: flex;
    gap: 1px;
    opacity: 0;
    transition: opacity 0.1s;
}
.layer-item:hover .layer-controls {
    opacity: 1;
}
.layers-empty {
    text-align: center;
    padding: 20px;
    color: var(--text-muted);
    font-size: 11px;
}

/* Media */
.media-upload-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 20px;
    border: 1.5px dashed var(--border);
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 10px;
    transition: all 0.15s;
    color: var(--text-muted);
    font-size: 11px;
}
.media-upload-zone:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-light);
}
.upload-hint {
    font-size: 10px;
}
.media-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
}
.media-item {
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid var(--border);
}
.media-item img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
}
.media-item span {
    display: block;
    font-size: 9px;
    padding: 2px 4px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Templates */
.tab-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-bottom: 10px;
    line-height: 1.4;
}
.template-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
}
.template-card {
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid var(--border);
    transition: all 0.15s;
}
.template-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--accent);
}
.tpl-preview {
    height: 50px;
}
.template-card span {
    display: block;
    font-size: 10px;
    padding: 4px 6px;
    text-align: center;
    color: var(--text-secondary);
}

/* ──────────────────────────────────────────── */
/* CANVAS AREA                                   */
/* ──────────────────────────────────────────── */
.canvas-area {
    flex: 1;
    background: var(--bg-tertiary);
    overflow: auto;
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 40px;
}

.ruler {
    position: sticky;
    background: var(--bg-panel);
    z-index: 5;
    overflow: hidden;
}
.ruler-top {
    top: 0;
    height: 20px;
    left: 0;
    right: 0;
}
.ruler-left {
    left: 0;
    width: 20px;
}

.page-container {
    position: relative;
    transition: transform 0.2s ease;
    margin: 0 auto;
}

.report-page {
    position: relative;
    box-shadow:
        0 8px 40px rgba(0, 0, 0, 0.18),
        0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 2px;
    overflow: visible;
    cursor: default;
    transition: box-shadow 0.2s;
}

.report-page:focus-within {
    box-shadow:
        0 12px 60px rgba(0, 0, 0, 0.22),
        0 0 0 2px var(--accent);
}

.page-label-below {
    text-align: center;
    margin-top: 12px;
    font-size: 11px;
    color: var(--text-muted);
}

.drop-hint {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--text-muted);
    opacity: 0.4;
    pointer-events: none;
}

/* Canvas element */
.canvas-element {
    position: absolute;
    transition: box-shadow 0.1s;
    transform-origin: center;
}

.canvas-element:not(.locked):hover {
    outline: 1px solid rgba(99, 102, 241, 0.4);
}
.canvas-element.selected {
    outline: 2px solid var(--accent) !important;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}
.canvas-element.multi-selected {
    outline: 2px solid rgba(99, 102, 241, 0.6);
}
.canvas-element.editing {
    outline: 2px solid var(--accent) !important;
    cursor: text;
}
.canvas-element.locked {
    outline-color: var(--warning) !important;
}

/* Resize handles */
.resize-handle {
    position: absolute;
    width: 10px;
    height: 10px;
    background: white;
    border: 2px solid var(--accent);
    border-radius: 2px;
    z-index: 100;
}
.resize-handle.nw {
    top: -5px;
    left: -5px;
    cursor: nw-resize;
}
.resize-handle.n {
    top: -5px;
    left: calc(50% - 5px);
    cursor: n-resize;
}
.resize-handle.ne {
    top: -5px;
    right: -5px;
    cursor: ne-resize;
}
.resize-handle.e {
    top: calc(50% - 5px);
    right: -5px;
    cursor: e-resize;
}
.resize-handle.se {
    bottom: -5px;
    right: -5px;
    cursor: se-resize;
}
.resize-handle.s {
    bottom: -5px;
    left: calc(50% - 5px);
    cursor: s-resize;
}
.resize-handle.sw {
    bottom: -5px;
    left: -5px;
    cursor: sw-resize;
}
.resize-handle.w {
    top: calc(50% - 5px);
    left: -5px;
    cursor: w-resize;
}

.rotate-handle {
    position: absolute;
    top: -28px;
    left: calc(50% - 12px);
    width: 24px;
    height: 24px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: crosshair;
    z-index: 100;
    color: white;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
}

.el-info-bar {
    position: absolute;
    bottom: -22px;
    left: 0;
    font-size: 10px;
    color: var(--text-muted);
    white-space: nowrap;
    pointer-events: none;
}

/* Priority stripe */
.priority-stripe-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    z-index: 10;
    border-radius: 0;
}

/* Element content styles */
.text-content {
    width: 100%;
    height: 100%;
    overflow: auto;
    outline: none;
    word-wrap: break-word;
}
.text-content.code {
    background: #1e293b;
    color: #34d399;
    font-family: "Courier New", monospace;
    font-size: 12px;
    padding: 12px;
    border-radius: 6px;
    overflow: auto;
    white-space: pre;
}
.text-content.quote {
    border-left: 4px solid currentColor;
    padding-left: 12px;
    font-style: italic;
}
.text-content.blockquote {
    padding: 12px 16px;
    border-left: 4px solid var(--accent);
    background: rgba(99, 102, 241, 0.04);
    border-radius: 0 6px 6px 0;
}
.text-content.highlight {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    padding: 2px 8px;
    border-radius: 4px;
}
.text-content.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(99, 102, 241, 0.12);
    color: var(--accent);
    padding: 4px 14px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 12px;
    white-space: nowrap;
}

.callout {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.callout-emoji {
    font-size: 20px;
    flex-shrink: 0;
    line-height: 1.5;
}

.image-content {
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.image-content img {
    width: 100%;
    height: 100%;
    display: block;
}
.image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: var(--bg-secondary);
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
    border: 2px dashed var(--border);
    border-radius: 4px;
}
.image-placeholder:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.table-content {
    width: 100%;
    height: 100%;
    overflow: auto;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.data-table th,
.data-table td {
    padding: 7px 12px;
    border-bottom: 1px solid var(--border);
    text-align: left;
}
.data-table tr:nth-child(even) {
    background: var(--bg-secondary);
}

.metric-content {
    width: 100%;
    height: 100%;
}
.metric-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.metric-value {
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
}
.metric-change {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 12px;
    margin-top: 6px;
}
.metric-change.positive {
    color: var(--success);
}
.metric-change.negative {
    color: var(--danger);
}

.progress-content {
    width: 100%;
    padding: 8px 0;
}
.progress-header {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 8px;
}
.progress-track {
    height: 8px;
    background: var(--bg-secondary);
    border-radius: 4px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.chart-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: var(--bg-secondary);
    border: 1px dashed var(--border);
    border-radius: 8px;
    cursor: pointer;
}
.chart-icon {
    color: var(--text-muted);
}
.chart-type {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
}
.chart-hint {
    font-size: 10px;
    color: var(--text-muted);
}

.timeline {
    padding: 8px;
    overflow: auto;
    height: 100%;
}
.timeline-item {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
}
.tl-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
}
.tl-line {
    position: absolute;
    left: 4px;
    top: 14px;
    bottom: 0;
    width: 2px;
    background: var(--border);
}
.tl-content .tl-date {
    font-size: 10px;
    font-weight: 600;
    margin-bottom: 2px;
    opacity: 0.7;
}
.tl-content .tl-title {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 2px;
}
.tl-content .tl-desc {
    font-size: 11px;
    opacity: 0.7;
    line-height: 1.4;
}

.checklist {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 4px;
    overflow: auto;
    height: 100%;
}
.check-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}
.check-box {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border);
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s;
}
.check-box.checked {
    border-color: transparent;
}
.checked-text {
    text-decoration: line-through;
    opacity: 0.5;
}

.signature {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 8px;
}
.sig-line {
    flex: 1;
    border-bottom: 2px solid var(--border);
}
.sig-name {
    font-family: "Georgia", serif;
    font-style: italic;
    font-size: 18px;
    color: var(--text-muted);
    margin-top: 4px;
}
.sig-title {
    font-size: 10px;
    color: var(--text-muted);
}

.icon-el {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.rating-el {
    display: flex;
    gap: 2px;
    align-items: center;
    font-size: 22px;
}
.star {
    cursor: default;
}
.pagenum-el,
.date-el {
    font-size: 11px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.testimonial {
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.quote-mark {
    font-size: 36px;
    font-family: Georgia, serif;
    line-height: 0.8;
    opacity: 0.3;
}
.testimonial-text {
    font-style: italic;
    font-size: 13px;
    line-height: 1.6;
    flex: 1;
    overflow: hidden;
}
.testimonial-author {
    font-weight: 600;
    font-size: 12px;
    margin-top: 8px;
}
.testimonial-role {
    font-size: 10px;
    opacity: 0.6;
}

.stat-row {
    display: flex;
    align-items: center;
    justify-content: space-around;
    width: 100%;
    height: 100%;
    padding: 8px;
}
.stat-item {
    text-align: center;
    flex: 1;
}
.stat-value {
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
}
.stat-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    opacity: 0.6;
    margin-top: 4px;
}

.shape {
    width: 100%;
    height: 100%;
}
.divider-line {
    width: 100%;
}
.arrow-shape {
    width: 100%;
    height: 100%;
}
.el-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: var(--text-muted);
    border: 1px dashed var(--border);
    border-radius: 4px;
}

.rubber-band {
    pointer-events: none;
}

/* Page header/footer */
.page-header-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    color: white;
    z-index: 10;
    cursor: text;
}
.page-header-bar span:focus {
    outline: none;
}
.page-footer-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 10;
}

.watermark {
    pointer-events: none;
    user-select: none;
}

/* ──────────────────────────────────────────── */
/* RIGHT PANEL                                   */
/* ──────────────────────────────────────────── */
.right-panel {
    width: 280px;
    flex-shrink: 0;
    background: var(--bg-panel);
    border-left: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: width 0.2s ease;
    position: relative;
}
.right-panel.collapsed {
    width: 0;
    border-left: none;
}

.right-collapse {
    right: auto;
    left: -12px;
}

.right-panel-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* No selection */
.no-selection {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 24px;
    text-align: center;
}
.no-sel-icon {
    color: var(--text-muted);
    opacity: 0.4;
}
.no-selection p {
    font-size: 12px;
    color: var(--text-secondary);
    line-height: 1.5;
}
.hint-text {
    font-size: 11px;
    color: var(--text-muted);
}

/* Props panel */
.props-panel {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    flex: 1;
}

.props-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.el-type-badge {
    color: var(--accent);
}
.el-type-name {
    flex: 1;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}
.lock-indicator {
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    padding: 2px;
}

.props-tabs {
    display: flex;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.props-tab {
    flex: 1;
    padding: 7px 4px;
    border: none;
    background: transparent;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.15s;
    text-align: center;
}
.props-tab:hover {
    color: var(--text-primary);
}
.props-tab.active {
    color: var(--accent);
    border-bottom: 2px solid var(--accent);
}

.props-section-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
}

.prop-section {
    margin-bottom: 16px;
}
.prop-section-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    margin-bottom: 8px;
    padding-bottom: 4px;
    border-bottom: 1px solid var(--border-light);
}

.prop-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
    min-height: 28px;
}
.prop-row label {
    font-size: 11px;
    color: var(--text-secondary);
    width: 72px;
    flex-shrink: 0;
    font-weight: 500;
}
.prop-row input[type="text"],
.prop-row input[type="number"],
.prop-row select,
.prop-row textarea {
    flex: 1;
    min-width: 0;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 5px;
    padding: 4px 8px;
    color: var(--text-primary);
    outline: none;
    font-size: 11px;
}
.prop-row input:focus,
.prop-row select:focus {
    border-color: var(--accent);
}
.prop-row.toggle-row {
    justify-content: space-between;
}
.prop-row.toggle-row input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: var(--accent);
}

.slider-row {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    min-width: 0;
}
.slider-row input[type="range"] {
    flex: 1;
    accent-color: var(--accent);
    height: 4px;
}
.slider-row span {
    font-size: 11px;
    color: var(--text-secondary);
    min-width: 40px;
    text-align: right;
}

.prop-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-bottom: 6px;
}
.prop-field label {
    display: block;
    font-size: 10px;
    color: var(--text-muted);
    margin-bottom: 3px;
    font-weight: 600;
}
.prop-field input {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 5px;
    padding: 5px 8px;
    color: var(--text-primary);
    outline: none;
    font-size: 11px;
}
.prop-field input:focus {
    border-color: var(--accent);
}

.color-row {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
}
.color-row input[type="color"] {
    width: 28px;
    height: 28px;
    border-radius: 5px;
    border: 1px solid var(--border);
    cursor: pointer;
    padding: 2px;
}
.color-hex {
    flex: 1;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 5px;
    padding: 4px 8px;
    font-family: monospace;
    font-size: 11px;
    color: var(--text-primary);
    outline: none;
}
.color-hex:focus {
    border-color: var(--accent);
}

.shadow-presets {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
}
.shadow-preset {
    padding: 5px 4px;
    border: 1px solid var(--border);
    border-radius: 5px;
    background: var(--bg-secondary);
    cursor: pointer;
    font-size: 10px;
    color: var(--text-secondary);
    transition: all 0.15s;
    text-align: center;
}
.shadow-preset:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.priority-presets {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
.priority-preset {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.15s;
    text-transform: capitalize;
}
.priority-preset.none {
    border-color: var(--border);
    color: var(--text-muted);
}
.priority-preset.low {
    background: rgba(59, 130, 246, 0.1);
    color: #1d4ed8;
    border-color: rgba(59, 130, 246, 0.3);
}
.priority-preset.medium {
    background: rgba(245, 158, 11, 0.1);
    color: #b45309;
    border-color: rgba(245, 158, 11, 0.3);
}
.priority-preset.high {
    background: rgba(249, 115, 22, 0.1);
    color: #c2410c;
    border-color: rgba(249, 115, 22, 0.3);
}
.priority-preset.urgent {
    background: rgba(239, 68, 68, 0.1);
    color: #b91c1c;
    border-color: rgba(239, 68, 68, 0.3);
}
.priority-preset.active {
    box-shadow: 0 0 0 2px currentColor;
}

.content-textarea {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 8px;
    color: var(--text-primary);
    outline: none;
    font-size: 12px;
    resize: vertical;
    line-height: 1.5;
}
.content-textarea:focus {
    border-color: var(--accent);
}

.toggle-btn {
    padding: 5px 12px;
    border: 1px solid var(--border);
    border-radius: 5px;
    background: var(--bg-secondary);
    cursor: pointer;
    font-size: 11px;
    color: var(--text-secondary);
    transition: all 0.15s;
}
.toggle-btn:hover,
.toggle-btn.active {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-light);
}

.prop-row-buttons {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.list-item-row {
    display: flex;
    gap: 6px;
    align-items: center;
    margin-bottom: 4px;
}
.list-item-row input {
    flex: 1;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 5px;
    padding: 5px 8px;
    font-size: 11px;
    color: var(--text-primary);
    outline: none;
}
.list-item-row input:focus {
    border-color: var(--accent);
}

.timeline-editor-item {
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 6px;
    margin-bottom: 6px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
    position: relative;
}
.timeline-editor-item input {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 4px 6px;
    font-size: 10px;
    color: var(--text-primary);
    outline: none;
    width: 100%;
}
.timeline-editor-item input:focus {
    border-color: var(--accent);
}
.timeline-editor-item button {
    position: absolute;
    top: 4px;
    right: 4px;
}

.no-text-props {
    padding: 20px;
    text-align: center;
    color: var(--text-muted);
    font-size: 11px;
}

/* Page settings */
.page-settings-panel {
    flex: 1;
    overflow-y: auto;
    padding: 10px 12px;
}
.sep {
    border: none;
    border-top: 1px solid var(--border);
    margin: 10px 0;
}

/* Right bottom nav */
.right-bottom-nav {
    display: flex;
    border-top: 1px solid var(--border);
    flex-shrink: 0;
}
.rbn-btn {
    flex: 1;
    padding: 8px 4px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 10px;
    font-weight: 600;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.15s;
    letter-spacing: 0.02em;
}
.rbn-btn:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.rbn-btn.active {
    color: var(--accent);
    background: var(--accent-light);
}

/* ──────────────────────────────────────────── */
/* AI PANEL                                      */
/* ──────────────────────────────────────────── */
.ai-panel {
    position: absolute;
    right: 8px;
    top: 8px;
    width: 320px;
    max-height: 420px;
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    display: flex;
    flex-direction: column;
    z-index: 50;
}
.ai-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
    font-weight: 600;
}
.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 100px;
    max-height: 250px;
}
.ai-msg {
    display: flex;
}
.ai-msg.user {
    justify-content: flex-end;
}
.ai-msg.assistant {
    justify-content: flex-start;
}
.msg-bubble {
    max-width: 85%;
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 12px;
    line-height: 1.5;
}
.ai-msg.user .msg-bubble {
    background: var(--accent);
    color: white;
    border-radius: 12px 12px 2px 12px;
}
.ai-msg.assistant .msg-bubble {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-radius: 12px 12px 12px 2px;
}
.ai-input-row {
    display: flex;
    gap: 6px;
    padding: 8px;
    border-top: 1px solid var(--border);
}
.ai-input {
    flex: 1;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 7px 10px;
    color: var(--text-primary);
    outline: none;
    font-size: 12px;
    resize: none;
    line-height: 1.4;
}
.ai-input:focus {
    border-color: var(--accent);
}
.ai-send-btn {
    width: 34px;
    height: 34px;
    background: var(--accent);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    align-self: flex-end;
    transition: all 0.15s;
}
.ai-send-btn:hover {
    background: var(--accent-hover);
}
.ai-send-btn:disabled {
    opacity: 0.5;
}

/* ──────────────────────────────────────────── */
/* STATUS BAR                                    */
/* ──────────────────────────────────────────── */
.status-bar {
    flex-shrink: 0;
    height: 26px;
    background: var(--bg-panel);
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 14px;
    gap: 8px;
    font-size: 11px;
    color: var(--text-muted);
}
.sep-dot {
    opacity: 0.4;
}
.status-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 6px;
}
.unsaved-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--warning);
}

/* ──────────────────────────────────────────── */
/* CONTEXT MENU                                  */
/* ──────────────────────────────────────────── */
.context-menu {
    position: fixed;
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: var(--shadow-lg);
    padding: 4px;
    z-index: 1000;
    min-width: 200px;
}
.ctx-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 7px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text-primary);
    font-size: 12px;
    border-radius: 6px;
    text-align: left;
    transition: background 0.1s;
}
.ctx-item:hover {
    background: var(--bg-secondary);
}
.ctx-item.danger {
    color: var(--danger);
}
.ctx-item.danger:hover {
    background: rgba(239, 68, 68, 0.06);
}
.ctx-item.separator {
    height: 1px;
    background: var(--border);
    padding: 0;
    pointer-events: none;
}
.ctx-shortcut {
    margin-left: auto;
    font-size: 10px;
    color: var(--text-muted);
}

/* ──────────────────────────────────────────── */
/* MICRO BUTTONS                                 */
/* ──────────────────────────────────────────── */
.micro-btn {
    width: 20px;
    height: 20px;
    border: none;
    background: transparent;
    border-radius: 4px;
    cursor: pointer;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.12s;
    flex-shrink: 0;
}
.micro-btn:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.micro-btn.danger:hover {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}
.micro-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* ──────────────────────────────────────────── */
/* TOAST                                          */
/* ──────────────────────────────────────────── */
.toast {
    position: fixed;
    bottom: 32px;
    right: 24px;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    box-shadow: var(--shadow-lg);
    z-index: 2000;
    display: flex;
    align-items: center;
    gap: 8px;
}
.toast.success {
    background: var(--success);
    color: white;
}
.toast.error {
    background: var(--danger);
    color: white;
}
.toast.info {
    background: var(--accent);
    color: white;
}
.toast-enter-active {
    animation: toastIn 0.3s ease;
}
.toast-leave-active {
    animation: toastOut 0.3s ease forwards;
}
@keyframes toastIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}
@keyframes toastOut {
    to {
        opacity: 0;
        transform: translateY(20px);
    }
}

/* Ruler */
.ruler-canvas-h {
    display: block;
    width: 100%;
    height: 20px;
}
.ruler-canvas-v {
    display: block;
    width: 20px;
    height: 100%;
}

/* Scrollbars */
::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}

/* Fullscreen */
.fullscreen {
    position: fixed !important;
    inset: 0 !important;
    z-index: 9999 !important;
}

/* Responsive tweaks */
@media (max-width: 1200px) {
    .left-panel {
        width: 200px;
    }
    .right-panel {
        width: 240px;
    }
}
@media (max-width: 900px) {
    .left-panel {
        width: 180px;
    }
    .right-panel.collapsed {
        width: 0;
    }
    .ribbon-select.font-select {
        width: 90px;
    }
}
@media (max-width: 640px) {
    .left-panel {
        position: absolute;
        z-index: 50;
        height: 100%;
    }
    .left-panel.collapsed {
        width: 0;
        overflow: hidden;
    }
    .right-panel {
        position: absolute;
        right: 0;
        z-index: 50;
        height: 100%;
    }
}
</style>
