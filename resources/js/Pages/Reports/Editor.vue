<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <button @click="router.get(route('reports.index'))"
                        class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Reports
                    </button>
                    <span class="text-slate-300">/</span>
                    <input v-model="form.title"
                        class="text-lg font-semibold bg-transparent border-0 border-b-2 border-transparent focus:border-indigo-500 focus:outline-none px-1 py-0.5 text-slate-800 min-w-0 flex-1 transition-colors"
                        placeholder="Report title…" />
                    <span v-if="saveStatus === 'saving'" class="text-xs text-amber-500 flex items-center gap-1 shrink-0">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Saving…
                    </span>
                    <span v-else-if="saveStatus === 'saved'" class="text-xs text-emerald-500 flex items-center gap-1 shrink-0">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Saved
                    </span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Undo/Redo -->
                    <button @click="undo" :disabled="historyIndex <= 0" title="Undo"
                        class="p-2 rounded-lg hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6"/></svg>
                    </button>
                    <button @click="redo" :disabled="historyIndex >= history.length - 1" title="Redo"
                        class="p-2 rounded-lg hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6M21 10l-6-6"/></svg>
                    </button>
                    <div class="w-px h-6 bg-slate-200 mx-1"></div>
                    <button @click="saveReport"
                        class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Save
                    </button>
                    <button @click="previewReport"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Preview
                    </button>
                    <button @click="downloadPDF"
                        class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="flex h-[calc(100vh-72px)] bg-slate-100 overflow-hidden">

            <!-- ===== LEFT SIDEBAR: Elements Library ===== -->
            <div class="w-64 bg-white border-r border-slate-200 flex flex-col overflow-hidden shrink-0">
                <!-- Search -->
                <div class="p-3 border-b border-slate-100">
                    <div class="relative">
                        <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input v-model="elementSearch" placeholder="Search elements…"
                            class="w-full pl-8 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-4">
                    <!-- Element categories -->
                    <div v-for="category in filteredCategories" :key="category.name">
                        <div class="flex items-center gap-2 mb-2 cursor-pointer" @click="toggleCategory(category.name)">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex-1">{{ category.name }}</span>
                            <svg class="w-3 h-3 text-slate-400 transition-transform" :class="{ 'rotate-180': !collapsedCategories.has(category.name) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div v-show="!collapsedCategories.has(category.name)" class="grid grid-cols-2 gap-1.5">
                            <div v-for="el in category.items" :key="el.type"
                                class="element-tile group flex flex-col items-center gap-1.5 p-2.5 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 hover:bg-indigo-50 cursor-grab active:cursor-grabbing transition-all select-none"
                                draggable="true"
                                @dragstart="handleDragStart($event, el)"
                                @click="addElement(el.type)">
                                <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 group-hover:bg-indigo-100 transition-colors">
                                    <span class="text-base" v-html="el.icon"></span>
                                </div>
                                <span class="text-xs text-slate-600 group-hover:text-indigo-700 font-medium text-center leading-tight">{{ el.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Global Settings tab -->
                <div class="border-t border-slate-200">
                    <button @click="showGlobalSettings = !showGlobalSettings"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                            Page Settings
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': showGlobalSettings }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div v-show="showGlobalSettings" class="px-4 pb-4 space-y-3 bg-slate-50 border-t border-slate-100">
                        <div class="pt-3">
                            <label class="prop-label">Page Size</label>
                            <select v-model="reportSettings.page_size" class="prop-input">
                                <option value="A4">A4</option>
                                <option value="Letter">Letter (US)</option>
                                <option value="Legal">Legal</option>
                                <option value="A3">A3</option>
                            </select>
                        </div>
                        <div>
                            <label class="prop-label">Orientation</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="reportSettings.orientation = 'portrait'"
                                    :class="reportSettings.orientation === 'portrait' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'"
                                    class="flex items-center justify-center gap-1.5 py-2 text-xs font-medium border rounded-lg transition-colors">
                                    <svg class="w-3 h-4" fill="currentColor" viewBox="0 0 12 16"><rect x="1" y="1" width="10" height="14" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                                    Portrait
                                </button>
                                <button @click="reportSettings.orientation = 'landscape'"
                                    :class="reportSettings.orientation === 'landscape' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'"
                                    class="flex items-center justify-center gap-1.5 py-2 text-xs font-medium border rounded-lg transition-colors">
                                    <svg class="w-4 h-3" fill="currentColor" viewBox="0 0 16 12"><rect x="1" y="1" width="14" height="10" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                                    Landscape
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="prop-label">Primary Color</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" v-model="reportSettings.primary_color" class="w-8 h-8 rounded border border-slate-200 cursor-pointer p-0.5 bg-white" />
                                    <input type="text" v-model="reportSettings.primary_color" class="prop-input flex-1 font-mono text-xs" />
                                </div>
                            </div>
                            <div>
                                <label class="prop-label">Background</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" v-model="reportSettings.background_color" class="w-8 h-8 rounded border border-slate-200 cursor-pointer p-0.5 bg-white" />
                                    <input type="text" v-model="reportSettings.background_color" class="prop-input flex-1 font-mono text-xs" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="prop-label">Global Font</label>
                            <select v-model="reportSettings.font_family" class="prop-input">
                                <option value="'DM Sans', sans-serif">DM Sans</option>
                                <option value="'Inter', sans-serif">Inter</option>
                                <option value="Georgia, serif">Georgia</option>
                                <option value="'Playfair Display', serif">Playfair Display</option>
                                <option value="'Courier New', monospace">Courier New</option>
                                <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                            </select>
                        </div>
                        <div>
                            <label class="prop-label">Margin (px)</label>
                            <input type="number" v-model.number="reportSettings.margin" class="prop-input" min="0" max="120" />
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="prop-label mb-0">Show Grid</label>
                            <button @click="showGrid = !showGrid"
                                :class="showGrid ? 'bg-indigo-600' : 'bg-slate-200'"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors">
                                <span :class="showGrid ? 'translate-x-5' : 'translate-x-1'" class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="prop-label mb-0">Snap to Grid</label>
                            <button @click="snapToGrid = !snapToGrid"
                                :class="snapToGrid ? 'bg-indigo-600' : 'bg-slate-200'"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors">
                                <span :class="snapToGrid ? 'translate-x-5' : 'translate-x-1'" class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== CENTER: Canvas Area ===== -->
            <div class="flex-1 overflow-auto bg-slate-100 relative" ref="canvasArea" @click.self="deselectAll">
                <!-- Zoom controls -->
                <div class="sticky top-4 left-0 right-0 z-20 flex justify-center pointer-events-none">
                    <div class="flex items-center gap-1 bg-white/90 backdrop-blur-sm border border-slate-200 rounded-xl px-2 py-1.5 shadow-sm pointer-events-auto">
                        <button @click="zoom = Math.max(50, zoom - 10)" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <span class="text-xs font-medium text-slate-600 w-12 text-center">{{ zoom }}%</span>
                        <button @click="zoom = Math.min(200, zoom + 10)" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                        <div class="w-px h-4 bg-slate-200 mx-1"></div>
                        <button @click="zoom = 100" class="text-xs text-slate-500 hover:text-indigo-600 px-1.5 transition-colors">Reset</button>
                    </div>
                </div>

                <div class="py-8 flex flex-col items-center gap-8" :style="{ paddingTop: '60px' }">
                    <div v-for="(page, pageIndex) in pages" :key="page.id" class="relative">
                        <!-- Page label -->
                        <div class="flex items-center justify-between mb-2 w-full" :style="{ width: canvasDimensions.width * (zoom/100) + 'px' }">
                            <span class="text-xs text-slate-400 font-medium">Page {{ pageIndex + 1 }}</span>
                            <div class="flex items-center gap-2">
                                <button @click="duplicatePage(pageIndex)" class="text-xs text-slate-400 hover:text-indigo-600 flex items-center gap-1 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Duplicate
                                </button>
                                <button @click="addPageAfter(pageIndex)" class="text-xs text-slate-400 hover:text-indigo-600 flex items-center gap-1 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Page
                                </button>
                                <button v-if="pages.length > 1" @click="removePage(pageIndex)" class="text-xs text-red-400 hover:text-red-600 flex items-center gap-1 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Canvas Page -->
                        <div class="canvas-page relative shadow-2xl overflow-hidden"
                            :style="getCanvasStyle()"
                            @dragover.prevent="handleDragOver"
                            @drop="handleDrop($event, pageIndex)"
                            @click.self="deselectAll"
                            @mousedown.self="startMarquee($event, pageIndex)">

                            <!-- Grid overlay -->
                            <div v-if="showGrid" class="absolute inset-0 pointer-events-none" style="background-image: radial-gradient(circle, #94a3b8 1px, transparent 1px); background-size: 20px 20px; opacity: 0.3; z-index: 1;"></div>

                            <!-- Drop zone hint -->
                            <div v-if="isDragOver && dragTargetPage === pageIndex && page.elements.length === 0"
                                class="absolute inset-8 border-2 border-dashed border-indigo-300 rounded-xl flex items-center justify-center z-10 pointer-events-none">
                                <span class="text-indigo-400 text-sm font-medium">Drop element here</span>
                            </div>

                            <!-- Elements -->
                            <div v-for="element in page.elements" :key="element.id"
                                class="element-wrapper absolute group"
                                :class="{
                                    'ring-2 ring-indigo-500 ring-offset-1': selectedElement && selectedElement.id === element.id,
                                    'hover:ring-1 hover:ring-indigo-300': !selectedElement || selectedElement.id !== element.id
                                }"
                                :style="getElementWrapperStyle(element)"
                                @mousedown="startDrag($event, element, pageIndex)"
                                @click.stop="selectElement(element, pageIndex)">

                                <!-- Selection handles -->
                                <template v-if="selectedElement && selectedElement.id === element.id">
                                    <div v-for="handle in resizeHandles" :key="handle.dir"
                                        class="resize-handle absolute w-2.5 h-2.5 bg-white border-2 border-indigo-500 rounded-full z-20 hover:scale-125 transition-transform"
                                        :style="handle.style"
                                        @mousedown.stop="startResize(handle.dir, $event)">
                                    </div>
                                    <!-- Rotation handle -->
                                    <div class="absolute top-[-28px] left-1/2 -translate-x-1/2 w-5 h-5 bg-white border-2 border-indigo-500 rounded-full cursor-crosshair z-20 flex items-center justify-center"
                                        @mousedown.stop="startRotation($event, element)">
                                        <svg class="w-2.5 h-2.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </div>
                                    <!-- Element type badge -->
                                    <div class="absolute -top-7 left-0 bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-md font-medium whitespace-nowrap z-20">
                                        {{ getElementLabel(element.type) }}
                                    </div>
                                    <!-- Delete / duplicate quick actions -->
                                    <div class="absolute -top-7 right-0 flex items-center gap-1 z-20">
                                        <button @click.stop="duplicateElement(pageIndex, element)"
                                            class="bg-white text-slate-600 hover:bg-slate-100 text-xs px-1.5 py-0.5 rounded border border-slate-200 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                        <button @click.stop="deleteElement(pageIndex, element.id)"
                                            class="bg-red-500 text-white hover:bg-red-600 text-xs px-1.5 py-0.5 rounded transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>

                                <!-- RENDER ELEMENT CONTENT -->
                                <component :is="'div'" :style="getElementContentStyle(element)" class="element-content w-full h-full overflow-hidden">

                                    <!-- Text -->
                                    <div v-if="element.type === 'text'"
                                        :contenteditable="selectedElement && selectedElement.id === element.id"
                                        :style="getTextStyle(element)"
                                        class="w-full h-full outline-none"
                                        @blur="onTextBlur(element, $event)"
                                        @dblclick="focusText($event)"
                                        v-html="element.content">
                                    </div>

                                    <!-- Heading -->
                                    <div v-else-if="element.type === 'heading'"
                                        :contenteditable="selectedElement && selectedElement.id === element.id"
                                        :style="getTextStyle(element)"
                                        class="w-full h-full outline-none font-bold"
                                        @blur="onTextBlur(element, $event)"
                                        @dblclick="focusText($event)"
                                        v-html="element.content">
                                    </div>

                                    <!-- Subheading -->
                                    <div v-else-if="element.type === 'subheading'"
                                        :contenteditable="selectedElement && selectedElement.id === element.id"
                                        :style="getTextStyle(element)"
                                        class="w-full h-full outline-none"
                                        @blur="onTextBlur(element, $event)"
                                        @dblclick="focusText($event)"
                                        v-html="element.content">
                                    </div>

                                    <!-- Quote / Callout -->
                                    <div v-else-if="element.type === 'quote'"
                                        :style="getTextStyle(element)"
                                        class="w-full h-full flex items-center pl-5 border-l-4"
                                        :class="element.styles?.borderLeft || 'border-indigo-500'">
                                        <div :contenteditable="selectedElement && selectedElement.id === element.id"
                                            class="w-full outline-none italic"
                                            @blur="onTextBlur(element, $event)"
                                            @dblclick="focusText($event)"
                                            v-html="element.content">
                                        </div>
                                    </div>

                                    <!-- List -->
                                    <div v-else-if="element.type === 'list'" :style="getTextStyle(element)" class="w-full h-full overflow-auto">
                                        <ul class="space-y-1" :class="element.styles?.listStyle === 'numbered' ? 'list-decimal pl-5' : 'list-disc pl-5'">
                                            <li v-for="(item, i) in element.items" :key="i"
                                                :contenteditable="selectedElement && selectedElement.id === element.id"
                                                @blur="onListItemBlur(element, i, $event)"
                                                class="outline-none">{{ item }}</li>
                                        </ul>
                                        <button v-if="selectedElement && selectedElement.id === element.id"
                                            @click.stop="addListItem(element)"
                                            class="mt-2 text-xs text-indigo-500 hover:text-indigo-700">+ Add item</button>
                                    </div>

                                    <!-- Image -->
                                    <div v-else-if="element.type === 'image'" class="w-full h-full relative group/img">
                                        <img v-if="element.src" :src="element.src"
                                            :style="{ width: '100%', height: '100%', objectFit: element.styles?.objectFit || 'cover', borderRadius: (element.styles?.borderRadius || 0) + 'px' }"
                                            class="block" />
                                        <div v-else class="w-full h-full bg-slate-100 flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-xs text-slate-400">Click to upload</span>
                                        </div>
                                        <label class="absolute inset-0 cursor-pointer opacity-0 group/img-hover:opacity-100">
                                            <input type="file" accept="image/*" class="hidden" @change="uploadImage($event, element)" />
                                        </label>
                                    </div>

                                    <!-- Divider -->
                                    <div v-else-if="element.type === 'divider'" class="w-full h-full flex items-center">
                                        <div class="w-full" :style="{
                                            borderTop: `${element.styles?.borderWidth || 1}px ${element.styles?.borderStyle || 'solid'} ${element.styles?.color || '#e2e8f0'}`
                                        }"></div>
                                    </div>

                                    <!-- Metric / KPI Card -->
                                    <div v-else-if="element.type === 'metric'" class="w-full h-full rounded-xl p-4 flex flex-col justify-center"
                                        :style="{ backgroundColor: element.styles?.backgroundColor || '#f8fafc', border: `1px solid ${element.styles?.borderColor || '#e2e8f0'}` }">
                                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ element.label }}</div>
                                        <div class="text-3xl font-bold" :style="{ color: element.styles?.color || reportSettings.primary_color }">{{ element.value }}</div>
                                        <div v-if="element.change" class="flex items-center gap-1 mt-1">
                                            <span :class="element.changeType === 'positive' ? 'text-emerald-600' : 'text-red-500'" class="text-xs font-medium">
                                                {{ element.changeType === 'positive' ? '▲' : '▼' }} {{ element.change }}
                                            </span>
                                            <span class="text-xs text-slate-400">{{ element.changePeriod }}</span>
                                        </div>
                                    </div>

                                    <!-- Table -->
                                    <div v-else-if="element.type === 'table'" class="w-full h-full overflow-auto">
                                        <table class="w-full border-collapse text-sm">
                                            <thead>
                                                <tr>
                                                    <th v-for="col in element.columns" :key="col"
                                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider"
                                                        :style="{ backgroundColor: element.styles?.headerBg || reportSettings.primary_color, color: element.styles?.headerColor || '#fff' }">
                                                        {{ col }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(row, ri) in element.data" :key="ri"
                                                    :style="{ backgroundColor: ri % 2 === 0 ? (element.styles?.evenRowBg || '#fff') : (element.styles?.oddRowBg || '#f8fafc') }">
                                                    <td v-for="(col, ci) in element.columns" :key="ci"
                                                        class="px-3 py-2 border-b border-slate-100"
                                                        :contenteditable="selectedElement && selectedElement.id === element.id"
                                                        @blur="onTableCellBlur(element, ri, ci, $event)">
                                                        {{ row[col] || '' }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div v-if="selectedElement && selectedElement.id === element.id" class="flex gap-2 p-2 bg-slate-50 border-t border-slate-100">
                                            <button @click.stop="addTableRow(element)" class="text-xs bg-indigo-500 text-white px-2 py-1 rounded">+ Row</button>
                                            <button @click.stop="addTableColumn(element)" class="text-xs bg-emerald-500 text-white px-2 py-1 rounded">+ Col</button>
                                            <button @click.stop="removeTableRow(element)" class="text-xs bg-red-400 text-white px-2 py-1 rounded">- Row</button>
                                        </div>
                                    </div>

                                    <!-- Charts -->
                                    <div v-else-if="isChartType(element.type)" class="w-full h-full relative">
                                        <canvas :id="'chart-' + element.id" class="w-full h-full"></canvas>
                                    </div>

                                    <!-- Shape: Rectangle -->
                                    <div v-else-if="element.type === 'rectangle'" class="w-full h-full"
                                        :style="{
                                            backgroundColor: element.styles?.backgroundColor || '#6366f1',
                                            borderRadius: (element.styles?.borderRadius || 0) + 'px',
                                            border: element.styles?.borderWidth ? `${element.styles.borderWidth}px solid ${element.styles?.borderColor || '#000'}` : 'none'
                                        }">
                                    </div>

                                    <!-- Shape: Circle/Ellipse -->
                                    <div v-else-if="element.type === 'circle'" class="w-full h-full rounded-full"
                                        :style="{ backgroundColor: element.styles?.backgroundColor || '#10b981', border: element.styles?.borderWidth ? `${element.styles.borderWidth}px solid ${element.styles?.borderColor || '#000'}` : 'none' }">
                                    </div>

                                    <!-- Shape: Triangle -->
                                    <div v-else-if="element.type === 'triangle'" class="w-full h-full flex items-center justify-center">
                                        <div :style="{
                                            width: 0, height: 0,
                                            borderLeft: `${(element.styles?.width || 100) / 2}px solid transparent`,
                                            borderRight: `${(element.styles?.width || 100) / 2}px solid transparent`,
                                            borderBottom: `${element.styles?.height || 100}px solid ${element.styles?.backgroundColor || '#f59e0b'}`
                                        }"></div>
                                    </div>

                                    <!-- Line -->
                                    <svg v-else-if="element.type === 'line'" class="w-full h-full" preserveAspectRatio="none">
                                        <line x1="0" y1="50%" x2="100%" y2="50%"
                                            :stroke="element.styles?.color || '#64748b'"
                                            :stroke-width="element.styles?.borderWidth || 2"
                                            :stroke-dasharray="element.styles?.dashed ? '8 4' : 'none'" />
                                    </svg>

                                    <!-- Progress Bar -->
                                    <div v-else-if="element.type === 'progress'" class="w-full h-full flex flex-col justify-center gap-1">
                                        <div class="flex justify-between text-xs" :style="{ color: element.styles?.color || '#374151' }">
                                            <span>{{ element.label }}</span>
                                            <span>{{ element.value }}%</span>
                                        </div>
                                        <div class="w-full rounded-full overflow-hidden" :style="{ height: (element.styles?.height || 8) + 'px', backgroundColor: element.styles?.trackColor || '#e2e8f0' }">
                                            <div class="h-full rounded-full transition-all" :style="{ width: element.value + '%', backgroundColor: element.styles?.color || reportSettings.primary_color }"></div>
                                        </div>
                                    </div>

                                    <!-- Page Number -->
                                    <div v-else-if="element.type === 'pagenum'" :style="getTextStyle(element)" class="w-full h-full flex items-center"
                                        :class="{ 'justify-start': element.styles?.textAlign === 'left', 'justify-center': !element.styles?.textAlign || element.styles?.textAlign === 'center', 'justify-end': element.styles?.textAlign === 'right' }">
                                        <span class="text-slate-400 text-xs">[Page Number]</span>
                                    </div>

                                    <!-- Date -->
                                    <div v-else-if="element.type === 'date'" :style="getTextStyle(element)" class="w-full h-full flex items-center">
                                        <span>{{ new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                                    </div>

                                    <!-- Link -->
                                    <div v-else-if="element.type === 'link'" :style="getTextStyle(element)" class="w-full h-full flex items-center">
                                        <a :href="element.href || '#'" :target="element.target || '_blank'"
                                            class="underline"
                                            :style="{ color: element.styles?.color || reportSettings.primary_color }"
                                            @click.prevent>
                                            {{ element.content || element.href || 'Click here' }}
                                        </a>
                                    </div>

                                    <!-- Code block -->
                                    <div v-else-if="element.type === 'code'" class="w-full h-full overflow-auto rounded-lg bg-slate-900 p-4">
                                        <pre class="text-emerald-400 text-xs font-mono whitespace-pre-wrap"
                                            :contenteditable="selectedElement && selectedElement.id === element.id"
                                            @blur="onTextBlur(element, $event)">{{ element.content }}</pre>
                                    </div>

                                    <!-- Icon / Badge -->
                                    <div v-else-if="element.type === 'badge'" class="w-full h-full flex items-center justify-center">
                                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold"
                                            :style="{ backgroundColor: element.styles?.backgroundColor || reportSettings.primary_color + '20', color: element.styles?.color || reportSettings.primary_color }">
                                            {{ element.content || 'Badge Label' }}
                                        </span>
                                    </div>

                                    <!-- Spacer -->
                                    <div v-else-if="element.type === 'spacer'" class="w-full h-full"></div>

                                </component>
                            </div>

                            <!-- Page number in footer (for templates that show it) -->
                            <div v-if="reportSettings.show_page_numbers" class="absolute bottom-4 left-0 right-0 flex justify-center pointer-events-none">
                                <span class="text-xs text-slate-400">{{ pageIndex + 1 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Add page button -->
                    <button @click="addPageAfter(pages.length - 1)"
                        class="flex items-center gap-2 px-6 py-3 border-2 border-dashed border-slate-300 text-slate-400 hover:border-indigo-400 hover:text-indigo-500 rounded-xl transition-all bg-white/50 text-sm font-medium mb-8"
                        :style="{ width: canvasDimensions.width * (zoom/100) + 'px' }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add New Page
                    </button>
                </div>
            </div>

            <!-- ===== RIGHT SIDEBAR: Properties Panel ===== -->
            <div class="w-72 bg-white border-l border-slate-200 flex flex-col overflow-hidden shrink-0">
                <div v-if="!selectedElement" class="flex-1 flex items-center justify-center p-6">
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                        </div>
                        <p class="text-sm text-slate-400">Select an element to edit its properties</p>
                        <p class="text-xs text-slate-300 mt-1">or drag one from the left panel</p>
                    </div>
                </div>

                <template v-else>
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-700">{{ getElementLabel(selectedElement.type) }} Properties</span>
                        <button @click="deselectAll" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <!-- POSITION & SIZE -->
                        <div class="prop-section">
                            <div class="prop-section-header" @click="togglePropSection('layout')">
                                <span>Position & Size</span>
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': !collapsedSections.has('layout') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-show="!collapsedSections.has('layout')" class="prop-section-body">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="prop-label">X</label>
                                        <div class="relative">
                                            <input type="number" v-model.number="selectedElement.position.x" class="prop-input pr-7" @change="pushHistory" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">px</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="prop-label">Y</label>
                                        <div class="relative">
                                            <input type="number" v-model.number="selectedElement.position.y" class="prop-input pr-7" @change="pushHistory" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">px</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="prop-label">Width</label>
                                        <div class="relative">
                                            <input type="number" v-model.number="selectedElement.styles.width" class="prop-input pr-7" @change="pushHistory" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">px</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="prop-label">Height</label>
                                        <div class="relative">
                                            <input type="number" v-model.number="selectedElement.styles.height" class="prop-input pr-7" @change="pushHistory" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">px</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div>
                                        <label class="prop-label">Rotation</label>
                                        <div class="relative">
                                            <input type="number" v-model.number="selectedElement.styles.rotate" min="-360" max="360" class="prop-input pr-6" @change="pushHistory" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">°</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="prop-label">Z-Index</label>
                                        <input type="number" v-model.number="selectedElement.styles.zIndex" class="prop-input" @change="pushHistory" />
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Opacity</label>
                                    <div class="flex items-center gap-2">
                                        <input type="range" min="0" max="100" v-model.number="selectedElement.styles.opacity" class="flex-1 h-1.5 accent-indigo-600" />
                                        <span class="text-xs text-slate-500 w-8 text-right">{{ selectedElement.styles.opacity ?? 100 }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TYPOGRAPHY (text elements) -->
                        <div v-if="isTextLike(selectedElement.type)" class="prop-section">
                            <div class="prop-section-header" @click="togglePropSection('typography')">
                                <span>Typography</span>
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': !collapsedSections.has('typography') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-show="!collapsedSections.has('typography')" class="prop-section-body">
                                <div>
                                    <label class="prop-label">Font Family</label>
                                    <select v-model="selectedElement.styles.fontFamily" class="prop-input" @change="pushHistory">
                                        <option value="'DM Sans', sans-serif">DM Sans</option>
                                        <option value="'Inter', sans-serif">Inter</option>
                                        <option value="Georgia, serif">Georgia</option>
                                        <option value="'Playfair Display', serif">Playfair Display</option>
                                        <option value="'Courier New', monospace">Courier New</option>
                                        <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                                        <option value="'Arial', sans-serif">Arial</option>
                                        <option value="'Times New Roman', serif">Times New Roman</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div>
                                        <label class="prop-label">Size</label>
                                        <div class="relative">
                                            <input type="number" v-model.number="selectedElement.styles.fontSize" class="prop-input pr-7" @change="pushHistory" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">px</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="prop-label">Weight</label>
                                        <select v-model="selectedElement.styles.fontWeight" class="prop-input" @change="pushHistory">
                                            <option value="300">Light</option>
                                            <option value="400">Regular</option>
                                            <option value="500">Medium</option>
                                            <option value="600">Semi Bold</option>
                                            <option value="700">Bold</option>
                                            <option value="800">Extra Bold</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="prop-label">Line Height</label>
                                        <input type="number" step="0.1" v-model.number="selectedElement.styles.lineHeight" class="prop-input" @change="pushHistory" />
                                    </div>
                                    <div>
                                        <label class="prop-label">Letter Spacing</label>
                                        <div class="relative">
                                            <input type="number" step="0.5" v-model.number="selectedElement.styles.letterSpacing" class="prop-input pr-7" @change="pushHistory" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">px</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Text alignment -->
                                <div class="mt-2">
                                    <label class="prop-label">Alignment</label>
                                    <div class="flex gap-1">
                                        <button v-for="align in ['left','center','right','justify']" :key="align"
                                            @click="selectedElement.styles.textAlign = align; pushHistory()"
                                            :class="selectedElement.styles.textAlign === align ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                                            class="flex-1 py-1.5 text-xs border rounded-lg transition-colors font-mono">
                                            {{ { left: '⫷', center: '≡', right: '⫸', justify: '☰' }[align] }}
                                        </button>
                                    </div>
                                </div>
                                <!-- Text decoration -->
                                <div class="mt-2">
                                    <label class="prop-label">Style</label>
                                    <div class="flex gap-1">
                                        <button @click="toggleTextStyle('italic')"
                                            :class="selectedElement.styles.fontStyle === 'italic' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-slate-500 border-slate-200'"
                                            class="flex-1 py-1.5 text-xs border rounded-lg italic">I</button>
                                        <button @click="toggleTextStyle('underline')"
                                            :class="selectedElement.styles.textDecoration === 'underline' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-slate-500 border-slate-200'"
                                            class="flex-1 py-1.5 text-xs border rounded-lg underline">U</button>
                                        <button @click="toggleTextStyle('line-through')"
                                            :class="selectedElement.styles.textDecoration === 'line-through' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-slate-500 border-slate-200'"
                                            class="flex-1 py-1.5 text-xs border rounded-lg line-through">S</button>
                                        <button @click="toggleTextStyle('uppercase')"
                                            :class="selectedElement.styles.textTransform === 'uppercase' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-slate-500 border-slate-200'"
                                            class="flex-1 py-1.5 text-xs border rounded-lg uppercase">AA</button>
                                    </div>
                                </div>
                                <!-- Color -->
                                <div class="mt-2">
                                    <label class="prop-label">Text Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" v-model="selectedElement.styles.color" class="w-9 h-9 rounded border border-slate-200 cursor-pointer p-0.5 bg-white" @change="pushHistory" />
                                        <input type="text" v-model="selectedElement.styles.color" class="prop-input flex-1 font-mono text-xs" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LINK properties -->
                        <div v-if="selectedElement.type === 'link'" class="prop-section">
                            <div class="prop-section-header">Link</div>
                            <div class="prop-section-body">
                                <div>
                                    <label class="prop-label">URL</label>
                                    <input type="url" v-model="selectedElement.href" class="prop-input" placeholder="https://example.com" @change="pushHistory" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Display Text</label>
                                    <input type="text" v-model="selectedElement.content" class="prop-input" @change="pushHistory" />
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <label class="prop-label mb-0">Open in new tab</label>
                                    <button @click="selectedElement.target = selectedElement.target === '_blank' ? '_self' : '_blank'; pushHistory()"
                                        :class="selectedElement.target === '_blank' ? 'bg-indigo-600' : 'bg-slate-200'"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors">
                                        <span :class="selectedElement.target === '_blank' ? 'translate-x-5' : 'translate-x-1'" class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- METRIC properties -->
                        <div v-if="selectedElement.type === 'metric'" class="prop-section">
                            <div class="prop-section-header">KPI / Metric</div>
                            <div class="prop-section-body">
                                <div>
                                    <label class="prop-label">Label</label>
                                    <input type="text" v-model="selectedElement.label" class="prop-input" @change="pushHistory" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Value</label>
                                    <input type="text" v-model="selectedElement.value" class="prop-input" @change="pushHistory" />
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div>
                                        <label class="prop-label">Change</label>
                                        <input type="text" v-model="selectedElement.change" class="prop-input" placeholder="5.2%" @change="pushHistory" />
                                    </div>
                                    <div>
                                        <label class="prop-label">Type</label>
                                        <select v-model="selectedElement.changeType" class="prop-input" @change="pushHistory">
                                            <option value="positive">Positive ▲</option>
                                            <option value="negative">Negative ▼</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Period Label</label>
                                    <input type="text" v-model="selectedElement.changePeriod" class="prop-input" placeholder="vs last month" @change="pushHistory" />
                                </div>
                            </div>
                        </div>

                        <!-- PROGRESS properties -->
                        <div v-if="selectedElement.type === 'progress'" class="prop-section">
                            <div class="prop-section-header">Progress Bar</div>
                            <div class="prop-section-body">
                                <div>
                                    <label class="prop-label">Label</label>
                                    <input type="text" v-model="selectedElement.label" class="prop-input" @change="pushHistory" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Value (0–100)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="range" min="0" max="100" v-model.number="selectedElement.value" class="flex-1 h-1.5 accent-indigo-600" />
                                        <span class="text-xs text-slate-500 w-8 text-right">{{ selectedElement.value }}%</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Bar Height</label>
                                    <input type="number" v-model.number="selectedElement.styles.height" class="prop-input" min="4" max="40" />
                                </div>
                            </div>
                        </div>

                        <!-- CHART properties -->
                        <div v-if="isChartType(selectedElement.type)" class="prop-section">
                            <div class="prop-section-header" @click="togglePropSection('chart')">
                                <span>Chart Data</span>
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': !collapsedSections.has('chart') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-show="!collapsedSections.has('chart')" class="prop-section-body">
                                <div>
                                    <label class="prop-label">Chart Type</label>
                                    <select v-model="selectedElement.type" class="prop-input" @change="onChartTypeChange">
                                        <option value="bar-chart">Bar Chart</option>
                                        <option value="line-chart">Line Chart</option>
                                        <option value="pie-chart">Pie / Donut</option>
                                        <option value="area-chart">Area Chart</option>
                                        <option value="doughnut-chart">Doughnut</option>
                                        <option value="radar-chart">Radar</option>
                                    </select>
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Title</label>
                                    <input type="text" v-model="selectedElement.chartTitle" class="prop-input" @change="refreshChart(selectedElement)" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Labels (comma separated)</label>
                                    <input type="text" v-model="chartLabelsInput" class="prop-input" placeholder="Jan, Feb, Mar…" @blur="updateChartData" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Dataset 1 Values</label>
                                    <input type="text" v-model="chartValuesInput" class="prop-input" placeholder="65, 59, 80…" @blur="updateChartData" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Dataset 1 Label</label>
                                    <input type="text" v-model="chartDatasetLabel" class="prop-input" @blur="updateChartData" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Dataset 1 Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" v-model="chartColor" class="w-9 h-9 rounded border border-slate-200 cursor-pointer p-0.5 bg-white" @change="updateChartData" />
                                        <input type="text" v-model="chartColor" class="prop-input flex-1 font-mono text-xs" />
                                    </div>
                                </div>
                                <div v-if="selectedElement.type === 'pie-chart' || selectedElement.type === 'doughnut-chart'" class="mt-2">
                                    <label class="prop-label">Pie Colors (comma separated)</label>
                                    <input type="text" v-model="pieColorsInput" class="prop-input text-xs" placeholder="#f59e0b,#6366f1,#10b981…" @blur="updateChartData" />
                                </div>
                                <button @click="refreshChart(selectedElement)" class="mt-3 w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    Refresh Chart
                                </button>
                            </div>
                        </div>

                        <!-- IMAGE properties -->
                        <div v-if="selectedElement.type === 'image'" class="prop-section">
                            <div class="prop-section-header">Image</div>
                            <div class="prop-section-body">
                                <label class="w-full flex items-center justify-center gap-2 py-2.5 border-2 border-dashed border-slate-200 rounded-lg text-sm text-slate-500 hover:border-indigo-400 hover:text-indigo-600 cursor-pointer transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Upload Image
                                    <input type="file" accept="image/*" class="hidden" @change="uploadImage($event, selectedElement)" />
                                </label>
                                <div class="mt-2">
                                    <label class="prop-label">Image URL</label>
                                    <input type="url" v-model="selectedElement.src" class="prop-input" placeholder="https://…" @change="pushHistory" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Object Fit</label>
                                    <select v-model="selectedElement.styles.objectFit" class="prop-input" @change="pushHistory">
                                        <option value="cover">Cover</option>
                                        <option value="contain">Contain</option>
                                        <option value="fill">Fill</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- LIST properties -->
                        <div v-if="selectedElement.type === 'list'" class="prop-section">
                            <div class="prop-section-header">List</div>
                            <div class="prop-section-body">
                                <div>
                                    <label class="prop-label">List Style</label>
                                    <select v-model="selectedElement.styles.listStyle" class="prop-input" @change="pushHistory">
                                        <option value="bullet">Bullet</option>
                                        <option value="numbered">Numbered</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- APPEARANCE -->
                        <div class="prop-section">
                            <div class="prop-section-header" @click="togglePropSection('appearance')">
                                <span>Appearance</span>
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': !collapsedSections.has('appearance') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-show="!collapsedSections.has('appearance')" class="prop-section-body">
                                <div>
                                    <label class="prop-label">Background Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" v-model="selectedElement.styles.backgroundColor" class="w-9 h-9 rounded border border-slate-200 cursor-pointer p-0.5 bg-white" @change="pushHistory" />
                                        <input type="text" v-model="selectedElement.styles.backgroundColor" class="prop-input flex-1 font-mono text-xs" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div>
                                        <label class="prop-label">Border Width</label>
                                        <input type="number" v-model.number="selectedElement.styles.borderWidth" min="0" class="prop-input" @change="pushHistory" />
                                    </div>
                                    <div>
                                        <label class="prop-label">Border Radius</label>
                                        <input type="number" v-model.number="selectedElement.styles.borderRadius" min="0" class="prop-input" @change="pushHistory" />
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Border Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" v-model="selectedElement.styles.borderColor" class="w-9 h-9 rounded border border-slate-200 cursor-pointer p-0.5 bg-white" @change="pushHistory" />
                                        <input type="text" v-model="selectedElement.styles.borderColor" class="prop-input flex-1 font-mono text-xs" />
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Border Style</label>
                                    <select v-model="selectedElement.styles.borderStyle" class="prop-input" @change="pushHistory">
                                        <option value="solid">Solid</option>
                                        <option value="dashed">Dashed</option>
                                        <option value="dotted">Dotted</option>
                                        <option value="double">Double</option>
                                    </select>
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Padding</label>
                                    <input type="number" v-model.number="selectedElement.styles.padding" min="0" class="prop-input" @change="pushHistory" />
                                </div>
                                <div class="mt-2">
                                    <label class="prop-label">Box Shadow</label>
                                    <select v-model="selectedElement.styles.boxShadow" class="prop-input" @change="pushHistory">
                                        <option value="none">None</option>
                                        <option value="sm">Small</option>
                                        <option value="md">Medium</option>
                                        <option value="lg">Large</option>
                                        <option value="xl">Extra Large</option>
                                        <option value="inner">Inner</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- LAYER ordering -->
                        <div class="prop-section">
                            <div class="prop-section-header">Layer Order</div>
                            <div class="prop-section-body">
                                <div class="grid grid-cols-2 gap-2">
                                    <button @click="bringForward" class="flex items-center justify-center gap-1 py-2 text-xs border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/></svg>
                                        Bring Forward
                                    </button>
                                    <button @click="sendBackward" class="flex items-center justify-center gap-1 py-2 text-xs border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"/></svg>
                                        Send Backward
                                    </button>
                                    <button @click="bringToFront" class="flex items-center justify-center gap-1 py-2 text-xs border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600">
                                        Bring to Front
                                    </button>
                                    <button @click="sendToBack" class="flex items-center justify-center gap-1 py-2 text-xs border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600">
                                        Send to Back
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Delete element -->
                    <div class="p-4 border-t border-slate-100">
                        <button @click="deleteSelectedElement"
                            class="w-full flex items-center justify-center gap-2 py-2 text-sm text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-200 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete Element
                        </button>
                    </div>
                </template>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { v4 as uuidv4 } from 'uuid';
import axios from 'axios';
import Chart from 'chart.js/auto';

const props = defineProps({ report: Object });

// ===== STATE =====
const pages = ref(props.report.content?.length ? props.report.content : [{ id: uuidv4(), elements: [] }]);
const selectedElement = ref(null);
const selectedPageIndex = ref(null);
const zoom = ref(100);
const showGrid = ref(false);
const snapToGrid = ref(false);
const showGlobalSettings = ref(false);
const saveStatus = ref('idle'); // idle | saving | saved
const isDragOver = ref(false);
const dragTargetPage = ref(null);
const elementSearch = ref('');
const collapsedCategories = ref(new Set());
const collapsedSections = ref(new Set());
const history = ref([JSON.stringify(pages.value)]);
const historyIndex = ref(0);
const chartInstances = new Map();

// Chart inputs (sync'd when element selected)
const chartLabelsInput = ref('');
const chartValuesInput = ref('');
const chartDatasetLabel = ref('');
const chartColor = ref('#6366f1');
const pieColorsInput = ref('');

const reportSettings = reactive({
    page_size: props.report.settings?.page_size || 'A4',
    orientation: props.report.settings?.orientation || 'portrait',
    primary_color: props.report.settings?.primary_color || '#6366f1',
    background_color: props.report.settings?.background_color || '#ffffff',
    font_family: props.report.settings?.font_family || "'DM Sans', sans-serif",
    margin: props.report.settings?.margin || 40,
    show_page_numbers: props.report.settings?.show_page_numbers ?? false,
});

const form = useForm({ title: props.report.title });

// ===== ELEMENT LIBRARY =====
const elementCategories = [
    {
        name: 'Text',
        items: [
            { type: 'heading',    name: 'Heading',     icon: '<strong style="font-size:14px">H1</strong>' },
            { type: 'subheading', name: 'Subheading',  icon: '<strong style="font-size:11px">H2</strong>' },
            { type: 'text',       name: 'Paragraph',   icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>' },
            { type: 'quote',      name: 'Quote',       icon: '<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>' },
            { type: 'list',       name: 'List',        icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>' },
            { type: 'link',       name: 'Link',        icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>' },
            { type: 'badge',      name: 'Badge',       icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>' },
            { type: 'code',       name: 'Code Block',  icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>' },
        ]
    },
    {
        name: 'Data & Charts',
        items: [
            { type: 'table',          name: 'Table',       icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 6v12M3 6h18v12H3z"/></svg>' },
            { type: 'bar-chart',      name: 'Bar Chart',   icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' },
            { type: 'line-chart',     name: 'Line Chart',  icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4v16"/></svg>' },
            { type: 'pie-chart',      name: 'Pie Chart',   icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>' },
            { type: 'doughnut-chart', name: 'Doughnut',    icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>' },
            { type: 'area-chart',     name: 'Area Chart',  icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 20V8l4 4 4-6 4 8 4-4v10H4z"/></svg>' },
            { type: 'radar-chart',    name: 'Radar',       icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="12,2 22,8.5 22,15.5 12,22 2,15.5 2,8.5" fill="none" stroke="currentColor" stroke-width="2"/><polygon points="12,7 17.5,10.25 17.5,16.75 12,20 6.5,16.75 6.5,10.25"/></svg>' },
            { type: 'metric',         name: 'KPI Card',    icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>' },
            { type: 'progress',       name: 'Progress Bar',icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>' },
        ]
    },
    {
        name: 'Media',
        items: [
            { type: 'image',   name: 'Image',   icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' },
        ]
    },
    {
        name: 'Shapes & Layout',
        items: [
            { type: 'rectangle', name: 'Rectangle', icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/></svg>' },
            { type: 'circle',    name: 'Circle',    icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>' },
            { type: 'triangle',  name: 'Triangle',  icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3L22 20H2z"/></svg>' },
            { type: 'line',      name: 'Line',      icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M3 12h18"/></svg>' },
            { type: 'divider',   name: 'Divider',   icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M3 12h18"/><circle cx="12" cy="12" r="2" fill="currentColor"/></svg>' },
            { type: 'spacer',    name: 'Spacer',    icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m-4-4v12m-4 3l4 4 4-4"/></svg>' },
        ]
    },
    {
        name: 'Report',
        items: [
            { type: 'pagenum', name: 'Page Number', icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>' },
            { type: 'date',    name: 'Date',        icon: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>' },
        ]
    }
];

const filteredCategories = computed(() => {
    if (!elementSearch.value.trim()) return elementCategories;
    const q = elementSearch.value.toLowerCase();
    return elementCategories.map(cat => ({
        ...cat,
        items: cat.items.filter(item => item.name.toLowerCase().includes(q) || item.type.includes(q))
    })).filter(cat => cat.items.length > 0);
});

// ===== CANVAS =====
const canvasDimensions = computed(() => {
    const sizes = {
        A4:     { portrait: { width: 794,  height: 1123 }, landscape: { width: 1123, height: 794 }},
        Letter: { portrait: { width: 816,  height: 1056 }, landscape: { width: 1056, height: 816 }},
        Legal:  { portrait: { width: 816,  height: 1344 }, landscape: { width: 1344, height: 816 }},
        A3:     { portrait: { width: 1123, height: 1587 }, landscape: { width: 1587, height: 1123 }},
    };
    return sizes[reportSettings.page_size]?.[reportSettings.orientation] || sizes.A4.portrait;
});

const getCanvasStyle = () => ({
    width: canvasDimensions.value.width * (zoom.value / 100) + 'px',
    height: canvasDimensions.value.height * (zoom.value / 100) + 'px',
    backgroundColor: reportSettings.background_color,
    fontFamily: reportSettings.font_family,
    transform: `scale(1)`,
    transformOrigin: 'top center',
});

const getElementWrapperStyle = (el) => {
    const s = el.styles || {};
    const position = el.position || {};
    
    const zoomFactor = zoom.value / 100;
    console.log('Element styles:', el);
    return {
        left: (position.x * zoomFactor) + 'px',
        top: (position.y * zoomFactor) + 'px',
        width: ((s.width || 200) * zoomFactor) + 'px',
        height: ((s.height || 50) * zoomFactor) + 'px',
        transform: s.rotate ? `rotate(${s.rotate}deg)` : undefined,
        zIndex: s.zIndex || 1,
        opacity: s.opacity !== undefined ? s.opacity / 100 : 1,
        cursor: 'move',
    };
};

const getElementContentStyle = (el) => {
    const s = el.styles || {};
    const shadow = {
        none: 'none', sm: '0 1px 3px rgba(0,0,0,.12)', md: '0 4px 12px rgba(0,0,0,.1)',
        lg: '0 10px 30px rgba(0,0,0,.15)', xl: '0 20px 60px rgba(0,0,0,.2)', inner: 'inset 0 2px 4px rgba(0,0,0,.1)'
    }[s.boxShadow] || 'none';
    return {
        borderRadius: s.borderRadius ? s.borderRadius + 'px' : undefined,
        border: s.borderWidth ? `${s.borderWidth}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : undefined,
        boxShadow: shadow,
        padding: s.padding ? s.padding + 'px' : undefined,
        overflow: 'hidden',
    };
};

const getTextStyle = (el) => {
    const s = el.styles || {};
    const zoomFactor = zoom.value / 100;
    return {
        fontSize: ((s.fontSize || 16) * zoomFactor) + 'px',
        color: s.color || '#1e293b',
        fontFamily: s.fontFamily || reportSettings.font_family,
        fontWeight: s.fontWeight || (el.type === 'heading' ? '700' : '400'),
        fontStyle: s.fontStyle || 'normal',
        textAlign: s.textAlign || 'left',
        textDecoration: s.textDecoration || 'none',
        textTransform: s.textTransform || 'none',
        lineHeight: s.lineHeight || 1.5,
        letterSpacing: s.letterSpacing ? s.letterSpacing + 'px' : undefined,
        backgroundColor: s.backgroundColor !== 'transparent' ? s.backgroundColor : undefined,
    };
};

// ===== DRAG FROM SIDEBAR =====
const handleDragStart = (e, el) => {
    e.dataTransfer.setData('elementType', el.type);
};
const handleDragOver = (e) => {
    isDragOver.value = true;
};
const handleDrop = (e, pageIndex) => {
    isDragOver.value = false;
    const type = e.dataTransfer.getData('elementType');
    if (type) {
        const rect = e.currentTarget.getBoundingClientRect();
        const zoomFactor = zoom.value / 100;
        const x = (e.clientX - rect.left) / zoomFactor;
        const y = (e.clientY - rect.top) / zoomFactor;
        addElementAtPosition(type, pageIndex, Math.max(0, x - 50), Math.max(0, y - 25));
    }
};

// ===== ELEMENT OPERATIONS =====
const addElement = (type) => {
    const pi = selectedPageIndex.value ?? 0;
    addElementAtPosition(type, pi, 60, 60 + (pages.value[pi].elements.length * 30));
};

const addElementAtPosition = (type, pageIndex, x, y) => {
    const el = createDefaultElement(type, x, y);
    pages.value[pageIndex].elements.push(el);
    nextTick(() => {
        if (isChartType(type)) initChart(el);
    });
    selectElement(el, pageIndex);
    pushHistory();
};

const createDefaultElement = (type, x = 60, y = 60) => {
    const base = {
        id: uuidv4(), type,
        position: { x, y },
        styles: { width: 200, height: 50, zIndex: 1, opacity: 100 }
    };
    const defaults = {
        heading:        { content: 'Report Heading', styles: { ...base.styles, width: 400, height: 60, fontSize: 32, fontWeight: '700', color: '#0f172a' } },
        subheading:     { content: 'Section Title', styles: { ...base.styles, width: 300, height: 44, fontSize: 20, fontWeight: '600', color: '#1e293b' } },
        text:           { content: 'Start typing your paragraph text here. This is sample text that can be edited.', styles: { ...base.styles, width: 400, height: 80, fontSize: 14, color: '#334155', lineHeight: 1.7 } },
        quote:          { content: 'An insightful quote or important callout goes here.', styles: { ...base.styles, width: 360, height: 80, fontSize: 15, fontStyle: 'italic', color: '#475569', backgroundColor: '#f8fafc', padding: 16 } },
        list:           { items: ['First item', 'Second item', 'Third item'], styles: { ...base.styles, width: 280, height: 120, fontSize: 14, color: '#334155' } },
        link:           { content: 'Click here', href: 'https://example.com', target: '_blank', styles: { ...base.styles, width: 160, height: 32, fontSize: 14, color: '#6366f1' } },
        badge:          { content: 'New', styles: { ...base.styles, width: 80, height: 36, backgroundColor: '#6366f110', color: '#6366f1' } },
        code:           { content: 'const greeting = "Hello, World!";\nconsole.log(greeting);', styles: { ...base.styles, width: 360, height: 100 } },
        date:           { styles: { ...base.styles, width: 200, height: 30, fontSize: 13, color: '#64748b' } },
        pagenum:        { styles: { ...base.styles, width: 100, height: 24, fontSize: 12, textAlign: 'center', color: '#94a3b8' } },
        image:          { src: '', styles: { ...base.styles, width: 300, height: 200 } },
        table:          { columns: ['Name', 'Value', 'Change'], data: [{ Name: 'Revenue', Value: '$1.2M', Change: '+12%' }, { Name: 'Users', Value: '8,400', Change: '+5%' }, { Name: 'Conversion', Value: '3.2%', Change: '-0.3%' }], styles: { ...base.styles, width: 440, height: 160, headerBg: '#6366f1', headerColor: '#fff' } },
        'bar-chart':    { chartData: { labels: ['Q1','Q2','Q3','Q4'], values: [42, 68, 55, 81] }, chartTitle: 'Quarterly Performance', chartDatasets: [], styles: { ...base.styles, width: 380, height: 260 } },
        'line-chart':   { chartData: { labels: ['Jan','Feb','Mar','Apr','May','Jun'], values: [30, 48, 36, 70, 60, 85] }, chartTitle: 'Trend Analysis', styles: { ...base.styles, width: 380, height: 260 } },
        'pie-chart':    { chartData: { labels: ['Category A','Category B','Category C','Category D'], values: [35, 25, 22, 18] }, chartTitle: 'Distribution', styles: { ...base.styles, width: 300, height: 280 } },
        'doughnut-chart':{ chartData: { labels: ['Segment A','Segment B','Segment C'], values: [40, 35, 25] }, chartTitle: 'Composition', styles: { ...base.styles, width: 300, height: 280 } },
        'area-chart':   { chartData: { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], values: [20, 45, 38, 60, 55, 72, 80] }, chartTitle: 'Weekly Overview', styles: { ...base.styles, width: 380, height: 260 } },
        'radar-chart':  { chartData: { labels: ['Speed','Accuracy','Efficiency','Quality','Innovation','Support'], values: [80, 92, 75, 88, 70, 95] }, chartTitle: 'Performance Radar', styles: { ...base.styles, width: 320, height: 300 } },
        metric:         { label: 'Total Revenue', value: '$48,293', change: '12.5%', changeType: 'positive', changePeriod: 'vs last month', styles: { ...base.styles, width: 220, height: 110, backgroundColor: '#f8fafc', borderColor: '#e2e8f0', borderWidth: 1, borderRadius: 12 } },
        progress:       { label: 'Completion Rate', value: 72, styles: { ...base.styles, width: 320, height: 50, color: '#6366f1', trackColor: '#e2e8f0', height: 8 } },
        rectangle:      { styles: { ...base.styles, width: 200, height: 120, backgroundColor: '#e0e7ff', borderRadius: 8 } },
        circle:         { styles: { ...base.styles, width: 100, height: 100, backgroundColor: '#6366f1' } },
        triangle:       { styles: { ...base.styles, width: 120, height: 120, backgroundColor: '#f59e0b' } },
        line:           { styles: { ...base.styles, width: 200, height: 20, borderWidth: 2, color: '#cbd5e1' } },
        divider:        { styles: { ...base.styles, width: 400, height: 20, borderWidth: 1, borderStyle: 'solid', color: '#e2e8f0' } },
        spacer:         { styles: { ...base.styles, width: 200, height: 40 } },
    };
    const d = defaults[type] || {};
    return { ...base, ...d, styles: { ...base.styles, ...(d.styles || {}) } };
};

const duplicateElement = (pageIndex, element) => {
    const clone = JSON.parse(JSON.stringify(element));
    clone.id = uuidv4();
    clone.position = { x: element.position.x + 20, y: element.position.y + 20 };
    pages.value[pageIndex].elements.push(clone);
    nextTick(() => { if (isChartType(clone.type)) initChart(clone); });
    selectElement(clone, pageIndex);
    pushHistory();
};

const deleteElement = (pageIndex, id) => {
    pages.value[pageIndex].elements = pages.value[pageIndex].elements.filter(e => e.id !== id);
    if (selectedElement.value?.id === id) { selectedElement.value = null; selectedPageIndex.value = null; }
    pushHistory();
};

const deleteSelectedElement = () => {
    if (!selectedElement.value || selectedPageIndex.value === null) return;
    deleteElement(selectedPageIndex.value, selectedElement.value.id);
};

const selectElement = (el, pageIndex) => {
    selectedElement.value = el;
    selectedPageIndex.value = pageIndex;
    if (isChartType(el.type) && el.chartData) {
        chartLabelsInput.value = (el.chartData.labels || []).join(', ');
        chartValuesInput.value = (el.chartData.values || []).join(', ');
        chartDatasetLabel.value = el.chartDatasetLabel || 'Dataset 1';
        chartColor.value = el.chartColor || reportSettings.primary_color;
        pieColorsInput.value = (el.pieColors || []).join(', ');
    }
};

const deselectAll = () => { selectedElement.value = null; selectedPageIndex.value = null; };

// ===== DRAGGING ELEMENTS ON CANVAS =====
let isDraggingEl = false;
let dragStartX = 0, dragStartY = 0, elStartX = 0, elStartY = 0;

const startDrag = (e, element, pageIndex) => {
    if (e.button !== 0) return;
    selectElement(element, pageIndex);
    isDraggingEl = true;
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    elStartX = element.position.x;
    elStartY = element.position.y;
    window.addEventListener('mousemove', onDragMove);
    window.addEventListener('mouseup', stopDrag);
    e.preventDefault();
};
const onDragMove = (e) => {
    if (!isDraggingEl || !selectedElement.value) return;
    const zoomFactor = zoom.value / 100;
    let nx = elStartX + (e.clientX - dragStartX) / zoomFactor;
    let ny = elStartY + (e.clientY - dragStartY) / zoomFactor;
    if (snapToGrid.value) { nx = Math.round(nx / 20) * 20; ny = Math.round(ny / 20) * 20; }
    selectedElement.value.position.x = Math.max(0, nx);
    selectedElement.value.position.y = Math.max(0, ny);
};
const stopDrag = () => { isDraggingEl = false; window.removeEventListener('mousemove', onDragMove); window.removeEventListener('mouseup', stopDrag); pushHistory(); };

// ===== RESIZING =====
const resizeHandles = [
    { dir: 'nw', style: 'top:-5px;left:-5px;cursor:nw-resize' },
    { dir: 'n',  style: 'top:-5px;left:calc(50% - 5px);cursor:n-resize' },
    { dir: 'ne', style: 'top:-5px;right:-5px;cursor:ne-resize' },
    { dir: 'w',  style: 'top:calc(50% - 5px);left:-5px;cursor:w-resize' },
    { dir: 'e',  style: 'top:calc(50% - 5px);right:-5px;cursor:e-resize' },
    { dir: 'sw', style: 'bottom:-5px;left:-5px;cursor:sw-resize' },
    { dir: 's',  style: 'bottom:-5px;left:calc(50% - 5px);cursor:s-resize' },
    { dir: 'se', style: 'bottom:-5px;right:-5px;cursor:se-resize' },
];

let isResizing = false;
let resizeDir = '';
let resizeStartX = 0, resizeStartY = 0;
let resizeStartW = 0, resizeStartH = 0, resizeStartElX = 0, resizeStartElY = 0;

const startResize = (dir, e) => {
    e.preventDefault();
    isResizing = true; resizeDir = dir;
    resizeStartX = e.clientX; resizeStartY = e.clientY;
    resizeStartW = selectedElement.value.styles.width || 200;
    resizeStartH = selectedElement.value.styles.height || 50;
    resizeStartElX = selectedElement.value.position.x;
    resizeStartElY = selectedElement.value.position.y;
    window.addEventListener('mousemove', onResizeMove);
    window.addEventListener('mouseup', stopResize);
};
const onResizeMove = (e) => {
    if (!isResizing || !selectedElement.value) return;
    const zf = zoom.value / 100;
    const dx = (e.clientX - resizeStartX) / zf;
    const dy = (e.clientY - resizeStartY) / zf;
    const MIN = 20;
    const s = selectedElement.value.styles;
    const p = selectedElement.value.position;
    if (resizeDir.includes('e')) s.width = Math.max(MIN, resizeStartW + dx);
    if (resizeDir.includes('s')) s.height = Math.max(MIN, resizeStartH + dy);
    if (resizeDir.includes('w')) { const nw = Math.max(MIN, resizeStartW - dx); p.x = resizeStartElX + (resizeStartW - nw); s.width = nw; }
    if (resizeDir.includes('n')) { const nh = Math.max(MIN, resizeStartH - dy); p.y = resizeStartElY + (resizeStartH - nh); s.height = nh; }
};
const stopResize = () => { isResizing = false; window.removeEventListener('mousemove', onResizeMove); window.removeEventListener('mouseup', stopResize); pushHistory(); };

// ===== ROTATION =====
const startRotation = (e, element) => {
    const rect = e.currentTarget.parentElement.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;
    const onMove = (mv) => {
        const angle = Math.atan2(mv.clientY - cy, mv.clientX - cx) * (180 / Math.PI) + 90;
        element.styles.rotate = Math.round(angle);
    };
    const onUp = () => { window.removeEventListener('mousemove', onMove); window.removeEventListener('mouseup', onUp); pushHistory(); };
    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
    e.preventDefault();
};

// ===== TEXT EDITING =====
const onTextBlur = (element, e) => { element.content = e.target.innerHTML || e.target.innerText; pushHistory(); };
const focusText = (e) => { const el = e.target; el.focus(); const range = document.createRange(); range.selectNodeContents(el); const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range); };
const onListItemBlur = (element, index, e) => { element.items[index] = e.target.innerText; pushHistory(); };
const addListItem = (element) => { element.items.push('New item'); pushHistory(); };
const onTableCellBlur = (element, ri, ci, e) => { const col = element.columns[ci]; element.data[ri][col] = e.target.innerText; pushHistory(); };
const addTableRow = (element) => { const row = {}; element.columns.forEach(c => row[c] = ''); element.data.push(row); pushHistory(); };
const addTableColumn = (element) => { const name = `Col ${element.columns.length + 1}`; element.columns.push(name); element.data.forEach(r => r[name] = ''); pushHistory(); };
const removeTableRow = (element) => { if (element.data.length > 1) { element.data.pop(); pushHistory(); } };

// ===== TYPOGRAPHY HELPERS =====
const toggleTextStyle = (style) => {
    if (!selectedElement.value) return;
    const s = selectedElement.value.styles;
    if (style === 'italic') s.fontStyle = s.fontStyle === 'italic' ? 'normal' : 'italic';
    else if (style === 'underline') s.textDecoration = s.textDecoration === 'underline' ? 'none' : 'underline';
    else if (style === 'line-through') s.textDecoration = s.textDecoration === 'line-through' ? 'none' : 'line-through';
    else if (style === 'uppercase') s.textTransform = s.textTransform === 'uppercase' ? 'none' : 'uppercase';
    pushHistory();
};

// ===== CHARTS =====
const isChartType = (type) => ['bar-chart','line-chart','pie-chart','area-chart','doughnut-chart','radar-chart'].includes(type);
const chartTypeMap = { 'bar-chart': 'bar', 'line-chart': 'line', 'pie-chart': 'pie', 'area-chart': 'line', 'doughnut-chart': 'doughnut', 'radar-chart': 'radar' };

const initChart = (element) => {
    nextTick(() => {
        const canvas = document.getElementById('chart-' + element.id);
        if (!canvas) return;
        if (chartInstances.has(element.id)) { chartInstances.get(element.id).destroy(); chartInstances.delete(element.id); }
        const type = chartTypeMap[element.type] || 'bar';
        const colors = element.pieColors?.length ? element.pieColors : ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316'];
        const isArea = element.type === 'area-chart';
        const chart = new Chart(canvas.getContext('2d'), {
            type,
            data: {
                labels: element.chartData?.labels || [],
                datasets: [{
                    label: element.chartDatasetLabel || element.chartTitle || 'Dataset',
                    data: element.chartData?.values || [],
                    backgroundColor: (type === 'pie' || type === 'doughnut') ? colors : (isArea ? element.chartColor + '30' || '#6366f130' : element.chartColor || reportSettings.primary_color),
                    borderColor: (type === 'pie' || type === 'doughnut') ? colors : element.chartColor || reportSettings.primary_color,
                    borderWidth: 2,
                    fill: isArea,
                    tension: element.type === 'line-chart' || isArea ? 0.4 : 0,
                    pointBackgroundColor: element.chartColor || reportSettings.primary_color,
                    pointRadius: type === 'radar' ? 4 : 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 }, boxWidth: 12 } },
                    title: { display: !!element.chartTitle, text: element.chartTitle, font: { size: 13, weight: '600' } },
                },
                scales: (type === 'pie' || type === 'doughnut' || type === 'radar') ? {} : {
                    x: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
                }
            }
        });
        chartInstances.set(element.id, chart);
    });
};

const refreshChart = (el) => { if (el && isChartType(el.type)) initChart(el); };
const onChartTypeChange = () => { nextTick(() => refreshChart(selectedElement.value)); };
const updateChartData = () => {
    if (!selectedElement.value || !isChartType(selectedElement.value.type)) return;
    selectedElement.value.chartData = {
        labels: chartLabelsInput.value.split(',').map(s => s.trim()).filter(Boolean),
        values: chartValuesInput.value.split(',').map(s => parseFloat(s.trim())).filter(n => !isNaN(n))
    };
    selectedElement.value.chartDatasetLabel = chartDatasetLabel.value;
    selectedElement.value.chartColor = chartColor.value;
    if (pieColorsInput.value) selectedElement.value.pieColors = pieColorsInput.value.split(',').map(s => s.trim());
    refreshChart(selectedElement.value);
    pushHistory();
};

// ===== IMAGE UPLOAD =====
const uploadImage = async (e, element) => {
    const file = e.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('image', file);
    try {
        const res = await axios.post('/api/upload-image', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
        element.src = res.data.url;
        pushHistory();
    } catch { alert('Upload failed. Please try again.'); }
};

// ===== PAGE MANAGEMENT =====
const addPageAfter = (index) => {
    pages.value.splice(index + 1, 0, { id: uuidv4(), elements: [] });
    pushHistory();
};
const removePage = (index) => {
    if (pages.value.length > 1) { pages.value.splice(index, 1); if (selectedPageIndex.value === index) deselectAll(); pushHistory(); }
};
const duplicatePage = (index) => {
    const clone = JSON.parse(JSON.stringify(pages.value[index]));
    clone.id = uuidv4();
    clone.elements = clone.elements.map(el => ({ ...el, id: uuidv4() }));
    pages.value.splice(index + 1, 0, clone);
    nextTick(() => clone.elements.forEach(el => { if (isChartType(el.type)) initChart(el); }));
    pushHistory();
};

// ===== LAYER ORDERING =====
const bringForward = () => {
    if (!selectedElement.value || selectedPageIndex.value === null) return;
    selectedElement.value.styles.zIndex = (selectedElement.value.styles.zIndex || 1) + 1;
    pushHistory();
};
const sendBackward = () => {
    if (!selectedElement.value || selectedPageIndex.value === null) return;
    selectedElement.value.styles.zIndex = Math.max(1, (selectedElement.value.styles.zIndex || 1) - 1);
    pushHistory();
};
const bringToFront = () => {
    if (!selectedElement.value || selectedPageIndex.value === null) return;
    const maxZ = Math.max(...pages.value[selectedPageIndex.value].elements.map(e => e.styles?.zIndex || 1));
    selectedElement.value.styles.zIndex = maxZ + 1;
    pushHistory();
};
const sendToBack = () => {
    if (!selectedElement.value || selectedPageIndex.value === null) return;
    const minZ = Math.min(...pages.value[selectedPageIndex.value].elements.map(e => e.styles?.zIndex || 1));
    selectedElement.value.styles.zIndex = Math.max(1, minZ - 1);
    pushHistory();
};

// ===== HISTORY =====
const pushHistory = () => {
    const snapshot = JSON.stringify(pages.value);
    if (snapshot === history.value[historyIndex.value]) return;
    history.value = history.value.slice(0, historyIndex.value + 1);
    history.value.push(snapshot);
    historyIndex.value = history.value.length - 1;
    autoSave();
};

const undo = () => {
    if (historyIndex.value <= 0) return;
    historyIndex.value--;
    pages.value = JSON.parse(history.value[historyIndex.value]);
    nextTick(() => reinitAllCharts());
};
const redo = () => {
    if (historyIndex.value >= history.value.length - 1) return;
    historyIndex.value++;
    pages.value = JSON.parse(history.value[historyIndex.value]);
    nextTick(() => reinitAllCharts());
};

const reinitAllCharts = () => {
    pages.value.forEach(page => page.elements.forEach(el => { if (isChartType(el.type)) initChart(el); }));
};

// ===== SAVE =====
let saveTimeout = null;
const autoSave = () => {
    if (saveTimeout) clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => saveReport(), 2500);
};

const saveReport = async () => {
    saveStatus.value = 'saving';
    try {
        await axios.put(route('reports.update', props.report.slug), {
            title: form.title,
            content: pages.value,
            settings: reportSettings,
        });
        saveStatus.value = 'saved';
        setTimeout(() => { saveStatus.value = 'idle'; }, 2000);
    } catch { saveStatus.value = 'idle'; alert('Save failed. Please try again.'); }
};

const previewReport = () => window.open(route('reports.preview', props.report.slug), '_blank');
const downloadPDF = () => window.open(route('reports.download', props.report.slug), '_blank');

// ===== UI HELPERS =====
const isTextLike = (type) => ['text','heading','subheading','quote','link','badge','date','pagenum'].includes(type);
const getElementLabel = (type) => {
    const labels = { 'bar-chart': 'Bar Chart', 'line-chart': 'Line Chart', 'pie-chart': 'Pie Chart', 'doughnut-chart': 'Doughnut', 'area-chart': 'Area Chart', 'radar-chart': 'Radar Chart', 'pagenum': 'Page Number' };
    return labels[type] || type.charAt(0).toUpperCase() + type.slice(1);
};
const toggleCategory = (name) => {
    const s = new Set(collapsedCategories.value);
    s.has(name) ? s.delete(name) : s.add(name);
    collapsedCategories.value = s;
};
const togglePropSection = (name) => {
    const s = new Set(collapsedSections.value);
    s.has(name) ? s.delete(name) : s.add(name);
    collapsedSections.value = s;
};

// ===== KEYBOARD SHORTCUTS =====
const handleKeyDown = (e) => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') return;
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveReport(); }
    if (e.key === 'Delete' || e.key === 'Backspace') { if (selectedElement.value) deleteSelectedElement(); }
    if (e.key === 'Escape') deselectAll();
    if (selectedElement.value && ['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key)) {
        e.preventDefault();
        const step = e.shiftKey ? 10 : 1;
        if (e.key === 'ArrowUp') selectedElement.value.position.y -= step;
        if (e.key === 'ArrowDown') selectedElement.value.position.y += step;
        if (e.key === 'ArrowLeft') selectedElement.value.position.x -= step;
        if (e.key === 'ArrowRight') selectedElement.value.position.x += step;
        pushHistory();
    }
};

// ===== LIFECYCLE =====
onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    nextTick(() => reinitAllCharts());
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
    chartInstances.forEach(c => c.destroy());
});

// Marquee (stub – selection box)
const startMarquee = (e) => { /* future: multi-select */ };
</script>

<style scoped>
.prop-label {
    @apply block text-xs font-medium text-slate-500 mb-1;
}
.prop-input {
    @apply w-full text-sm bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors;
}
.prop-section {
    @apply border-b border-slate-100;
}
.prop-section-header {
    @apply flex items-center justify-between px-4 py-2.5 text-xs font-semibold text-slate-600 uppercase tracking-wider bg-slate-50 cursor-pointer hover:bg-slate-100 transition-colors select-none;
}
.prop-section-body {
    @apply px-4 py-3;
}
.element-tile {
    user-select: none;
}
.canvas-page {
    user-select: none;
    position: relative;
}
.element-wrapper {
    user-select: none;
}
</style>