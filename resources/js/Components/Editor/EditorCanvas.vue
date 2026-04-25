<template>
  <div
    ref="canvasWrapper"
    class="flex-1 flex flex-col overflow-hidden relative canvas-workspace"
    :class="dark ? 'bg-slate-950' : 'bg-[#e8eaf0]'"
    @dragover.prevent
    @drop.prevent="handleDrop"
  >

    <!-- ── RULERS ── -->
    <template v-if="showRulers">
      <!-- Corner -->
      <div class="absolute top-0 left-0 z-30 w-7 h-7 flex items-center justify-center shrink-0"
        :class="dark ? 'bg-slate-900 border-b border-r border-slate-800' : 'bg-slate-200 border-b border-r border-slate-300'">
        <i class="fa-solid fa-crosshairs text-[9px]" :class="dark ? 'text-slate-600' : 'text-slate-400'"></i>
      </div>
      <!-- Horizontal ruler -->
      <div ref="hRuler" class="absolute top-0 left-7 right-0 h-7 z-30 overflow-hidden shrink-0"
        :class="dark ? 'bg-slate-900 border-b border-slate-800' : 'bg-slate-200 border-b border-slate-300'">
        <canvas ref="hRulerCanvas" class="w-full h-full" />
      </div>
      <!-- Vertical ruler -->
      <div ref="vRuler" class="absolute left-0 top-7 bottom-0 w-7 z-30 overflow-hidden"
        :class="dark ? 'bg-slate-900 border-r border-slate-800' : 'bg-slate-200 border-r border-slate-300'">
        <canvas ref="vRulerCanvas" class="w-full h-full" />
      </div>
    </template>

    <!-- ── TOOLBAR BAR ── -->
    <div class="absolute top-2 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1 px-3 py-1.5 rounded-2xl shadow-xl border backdrop-blur-xl transition-all"
      :class="dark ? 'bg-slate-900/90 border-slate-700/60 shadow-black/40' : 'bg-white/90 border-slate-200/80 shadow-slate-300/40'"
      style="animation: slideDown 0.3s cubic-bezier(0.16,1,0.3,1)">
      <!-- Zoom controls -->
      <button @click="decreaseZoom" title="Zoom Out"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
        <i class="fa-solid fa-minus text-[10px]"></i>
      </button>
      <div class="relative group">
        <button @click="showZoomMenu = !showZoomMenu"
          class="min-w-[52px] h-7 px-2 rounded-lg text-[11px] font-black tabular-nums transition-all hover:scale-105 active:scale-95 border"
          :class="dark ? 'bg-slate-800 border-slate-700 text-indigo-400 hover:border-indigo-600' : 'bg-indigo-50 border-indigo-200 text-indigo-600 hover:bg-indigo-100'">
          {{ zoom }}%
        </button>
        <transition name="dropdown">
          <div v-if="showZoomMenu"
            class="absolute top-full mt-1 left-1/2 -translate-x-1/2 w-28 rounded-xl shadow-xl border overflow-hidden z-50"
            :class="dark ? 'bg-slate-800 border-slate-700' : 'bg-white border-slate-200'">
            <button v-for="z in ZOOM_PRESETS" :key="z"
              @click="$emit('update:zoom', z); showZoomMenu = false"
              class="w-full text-center py-1.5 text-xs font-bold transition-colors"
              :class="zoom === z
                ? 'bg-indigo-600 text-white'
                : (dark ? 'text-slate-300 hover:bg-white/10' : 'text-slate-600 hover:bg-slate-50')">
              {{ z }}%
            </button>
          </div>
        </transition>
      </div>
      <button @click="increaseZoom" title="Zoom In"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
        <i class="fa-solid fa-plus text-[10px]"></i>
      </button>
      <div class="w-px h-4 mx-1" :class="dark ? 'bg-slate-700' : 'bg-slate-200'"></div>
      <button @click="fitCanvas" title="Fit to Screen"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
        <i class="fa-solid fa-expand-arrows-alt text-[10px]"></i>
      </button>
      <button @click="resetZoom" title="Reset Zoom (100%)"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
        <i class="fa-solid fa-maximize text-[10px]"></i>
      </button>
      <div class="w-px h-4 mx-1" :class="dark ? 'bg-slate-700' : 'bg-slate-200'"></div>
      <!-- Page indicator -->
      <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-[10px] font-black"
        :class="dark ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-500'">
        <i class="fa-regular fa-file text-[9px]"></i>
        <span>{{ selectedPageIndex + 1 }}/{{ pages.length }}</span>
      </div>
      <div class="w-px h-4 mx-1" :class="dark ? 'bg-slate-700' : 'bg-slate-200'"></div>
      <!-- Add page -->
      <button @click="$emit('add-page-after', selectedPageIndex)" title="Add Page"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95 text-emerald-500 hover:bg-emerald-500/10">
        <i class="fa-solid fa-file-circle-plus text-[11px]"></i>
      </button>
    </div>

    <!-- ── CANVAS SCROLL AREA ── -->
    <div
      ref="scrollArea"
      class="flex-1 overflow-auto canvas-scroll"
      :style="showRulers ? 'padding-top: 28px; padding-left: 28px;' : ''"
      @mousedown="onCanvasMousedown"
      @mousemove="onCanvasMousemove"
      @mouseup="onCanvasMouseup"
    >
      <!-- Inner centering wrapper -->
      <div
        class="flex flex-col items-center gap-8 py-16 px-8 min-h-full"
        :style="{ minWidth: (canvasDimensions.width * zoom / 100 + 120) + 'px' }"
      >

        <!-- Each page -->
        <div
          v-for="(page, pi) in pages"
          :key="page.id"
          class="page-wrapper relative"
          :class="{ 'page-active': pi === selectedPageIndex }"
          @click="$emit('select-page', pi)"
        >
          <!-- Page label -->
          <div class="absolute -top-8 left-0 flex items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg transition-all"
              :class="pi === selectedPageIndex
                ? (dark ? 'bg-indigo-600 text-white' : 'bg-indigo-600 text-white')
                : (dark ? 'bg-slate-800 text-slate-500' : 'bg-slate-300/60 text-slate-500')">
              <i class="fa-regular fa-file mr-1 text-[9px]"></i>
              {{ page.label || `Page ${pi + 1}` }}
            </span>
            <span class="text-[9px]" :class="dark ? 'text-slate-700' : 'text-slate-400'">
              {{ (page.elements || []).length }} element{{ (page.elements || []).length !== 1 ? 's' : '' }}
            </span>
          </div>

          <!-- Page shadow wrapper -->
          <div class="page-shadow-wrapper"
            :style="{
              width: (canvasDimensions.width * zoom / 100) + 'px',
              height: (canvasDimensions.height * zoom / 100) + 'px',
            }">

            <!-- THE ACTUAL PAGE -->
            <div
              :id="'page-' + page.id"
              class="page-canvas relative overflow-hidden select-none transition-all duration-300"
              :style="{
                width: (canvasDimensions.width * zoom / 100) + 'px',
                height: (canvasDimensions.height * zoom / 100) + 'px',
                backgroundColor: rs.background_color || '#ffffff',
                fontFamily: rs.font_family || 'sans-serif',
                borderRadius: (rs.page_radius || 0) + 'px',
                backgroundImage: rs.bg_image ? `url(${rs.bg_image})` : undefined,
                backgroundSize: rs.bg_image ? 'cover' : undefined,
                backgroundPosition: rs.bg_image ? 'center' : undefined,
                cursor: dragMode ? 'grab' : 'default',
              }"
              @click.self="$emit('deselect-all')"
              @contextmenu.prevent="pi === selectedPageIndex ? null : null"
              @dragover.prevent
              @drop.prevent="e => handleDropOnPage(e, pi)"
            >
              <!-- Grid overlay -->
              <svg v-if="showGrid && pi === selectedPageIndex"
                class="absolute inset-0 pointer-events-none z-10 opacity-40"
                :width="canvasDimensions.width * zoom / 100"
                :height="canvasDimensions.height * zoom / 100"
                xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <pattern :id="'grid-' + pi" :width="gridSize * zoom/100" :height="gridSize * zoom/100" patternUnits="userSpaceOnUse">
                    <path :d="`M ${gridSize * zoom/100} 0 L 0 0 0 ${gridSize * zoom/100}`"
                      fill="none" :stroke="dark ? '#6366f130' : '#6366f125'" stroke-width="0.5"/>
                  </pattern>
                </defs>
                <rect width="100%" height="100%" :fill="`url(#grid-${pi})`" />
              </svg>

              <!-- Header bar -->
              <div v-if="rs.show_header"
                class="absolute top-0 left-0 right-0 z-20 flex items-center px-6"
                :style="{
                  height: (40 * zoom/100) + 'px',
                  backgroundColor: rs.header_color || '#1e293b',
                }">
                <span :style="{
                  fontSize: (11 * zoom/100) + 'px',
                  color: '#ffffff',
                  fontFamily: rs.font_family,
                  fontWeight: '600',
                  letterSpacing: '0.05em',
                }">{{ rs.header_text || '' }}</span>
              </div>

              <!-- Watermark global -->
              <div v-if="rs.watermark"
                class="absolute inset-0 flex items-center justify-center pointer-events-none z-40 overflow-hidden">
                <span :style="{
                  fontSize: (72 * zoom/100) + 'px',
                  fontWeight: '800',
                  color: '#94a3b8',
                  opacity: 0.08,
                  transform: 'rotate(-35deg)',
                  whiteSpace: 'nowrap',
                  userSelect: 'none',
                }">{{ rs.watermark }}</span>
              </div>

              <!-- ── ELEMENTS ── -->
              <template v-if="pi === selectedPageIndex || zoom < 50">
                <div
                  v-for="el in (page.elements || [])"
                  :key="el.id"
                  class="absolute element-node"
                  :class="{
                    'element-selected': selectedElement?.id === el.id && pi === selectedPageIndex,
                    'element-hidden': el.hidden,
                    'element-locked': el.locked,
                  }"
                  :style="getElementWrapperStyle(el)"
                  @mousedown.stop="onElementMousedown($event, el, pi)"
                  @dblclick.stop="onElementDblClick($event, el, pi)"
                  @contextmenu.prevent.stop="$emit('open-context-menu', $event, el, pi)"
                >
                  <!-- Element content -->
                  <ElementRenderer
                    :el="el"
                    :pi="pi"
                    :zoom="zoom"
                    :rs="rs"
                    :dark="dark"
                    :selected="selectedElement?.id === el.id && pi === selectedPageIndex"
                    @text-blur="(e) => $emit('text-blur', el, e)"
                    @list-item-blur="(i, e) => $emit('list-item-blur', el, i, e)"
                    @add-list-item="$emit('add-list-item', el)"
                    @checklist-item-blur="(i, e) => $emit('checklist-item-blur', el, i, e)"
                    @add-checklist-item="$emit('add-checklist-item', el)"
                    @table-cell-blur="(ri, ci, e) => $emit('table-cell-blur', el, ri, ci, e)"
                    @table-header-blur="(col, e) => $emit('table-header-blur', el, col, e)"
                    @toc-title-blur="(e) => $emit('toc-title-blur', el, e)"
                    @toc-item-blur="(i, f, e) => $emit('toc-item-blur', el, i, f, e)"
                    @add-toc-item="$emit('add-toc-item', el)"
                    @timeline-blur="(i, f, e) => $emit('timeline-blur', el, i, f, e)"
                    @add-timeline-item="$emit('add-timeline-item', el)"
                    @upload-image="(e) => $emit('upload-image', e, el)"
                    @push-history="$emit('push-history')"
                  />

                  <!-- Selection UI (only for selected element on active page) -->
                  <template v-if="selectedElement?.id === el.id && pi === selectedPageIndex && !el.locked">
                    <!-- Selection border -->
                    <div class="selection-border" />

                    <!-- Resize handles -->
                    <div v-for="handle in RESIZE_HANDLES" :key="handle.dir"
                      class="resize-handle"
                      :class="'handle-' + handle.dir"
                      :style="getHandleStyle(handle)"
                      @mousedown.stop.prevent="$emit('start-resize', handle.dir, $event)" />

                    <!-- Rotation handle -->
                    <div class="rotation-handle"
                      @mousedown.stop.prevent="$emit('start-rotation', $event, el)"
                      title="Rotate">
                      <i class="fa-solid fa-rotate text-white text-[8px]"></i>
                    </div>

                    <!-- Quick action bar -->
                    <div class="element-actions"
                      :class="dark ? 'bg-slate-800/95 border-slate-700' : 'bg-white/95 border-slate-200'"
                      style="animation: fadeInUp 0.15s ease">
                      <button @click.stop="$emit('duplicate-element', pi, el)" title="Duplicate (Ctrl+D)"
                        class="action-btn">
                        <i class="fa-regular fa-clone"></i>
                      </button>
                      <button @click.stop="$emit('bring-to-front')" title="Bring to Front"
                        class="action-btn">
                        <i class="fa-solid fa-angles-up"></i>
                      </button>
                      <button @click.stop="$emit('send-to-back')" title="Send to Back"
                        class="action-btn">
                        <i class="fa-solid fa-angles-down"></i>
                      </button>
                      <button @click.stop="$emit('lock-element', el)" :title="el.locked ? 'Unlock' : 'Lock'"
                        class="action-btn" :class="el.locked ? 'text-amber-500' : ''">
                        <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'"></i>
                      </button>
                      <div class="w-px h-3 bg-slate-200 dark:bg-slate-600 mx-0.5"></div>
                      <button @click.stop="$emit('delete-element', pi, el.id)" title="Delete (Del)"
                        class="action-btn text-red-400 hover:text-red-600 hover:bg-red-50">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>

                    <!-- Element info tooltip -->
                    <div class="element-info-badge"
                      :class="dark ? 'bg-indigo-600 text-white' : 'bg-indigo-600 text-white'">
                      {{ el.type.replace(/-/g, ' ') }}
                      · {{ Math.round(el.styles?.width || 0) }}×{{ Math.round(el.styles?.height || 0) }}
                    </div>
                  </template>

                  <!-- Lock indicator -->
                  <div v-if="el.locked && pi === selectedPageIndex"
                    class="absolute -top-3 -right-3 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center shadow-md z-20">
                    <i class="fa-solid fa-lock text-white text-[7px]"></i>
                  </div>

                  <!-- Hidden indicator -->
                  <div v-if="el.hidden"
                    class="absolute inset-0 bg-slate-900/40 flex items-center justify-center rounded pointer-events-none z-10">
                    <i class="fa-solid fa-eye-slash text-white/60 text-lg"></i>
                  </div>
                </div>
              </template>

              <!-- Inactive page overlay (other pages shown dimly) -->
              <template v-else>
                <div
                  v-for="el in (page.elements || [])"
                  :key="el.id"
                  class="absolute pointer-events-none"
                  :style="getElementWrapperStyle(el)"
                >
                  <ElementRenderer
                    :el="el"
                    :pi="pi"
                    :zoom="zoom"
                    :rs="rs"
                    :dark="dark"
                    :selected="false"
                  />
                </div>
              </template>

              <!-- Footer bar -->
              <div v-if="rs.show_footer || rs.show_page_numbers"
                class="absolute bottom-0 left-0 right-0 z-20 flex items-center justify-between"
                :style="{
                  height: (36 * zoom/100) + 'px',
                  backgroundColor: rs.footer_color || 'transparent',
                  borderTop: `1px solid ${rs.primary_color || '#6366f1'}25`,
                  padding: `0 ${(rs.margin || 40) * zoom/100}px`,
                }">
                <span :style="{ fontSize: (10 * zoom/100) + 'px', color: '#94a3b8', fontFamily: rs.font_family }">
                  {{ rs.footer_left || '' }}
                </span>
                <span v-if="rs.show_page_numbers"
                  :style="{ fontSize: (11 * zoom/100) + 'px', color: '#94a3b8' }">
                  {{ pi + 1 }}{{ pages.length > 1 ? ` / ${pages.length}` : '' }}
                </span>
                <span :style="{ fontSize: (10 * zoom/100) + 'px', color: '#94a3b8', fontFamily: rs.font_family }">
                  {{ rs.footer_right || '' }}
                </span>
              </div>

              <!-- Drop zone hint (active page only) -->
              <div v-if="isDragOverPage && pi === selectedPageIndex"
                class="absolute inset-0 z-50 rounded flex items-center justify-center pointer-events-none"
                style="border: 2px dashed #6366f1; background: rgba(99,102,241,0.05);">
                <div class="flex flex-col items-center gap-2">
                  <i class="fa-solid fa-plus-circle text-indigo-500 text-3xl opacity-60"></i>
                  <span class="text-indigo-500 text-xs font-black opacity-60">Drop to add element</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Page controls below -->
          <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity page-controls">
            <button @click.stop="$emit('add-page-after', pi)" title="Add page after"
              class="px-3 py-1 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
              :class="dark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-emerald-500 hover:text-emerald-400' : 'bg-white border-slate-200 text-slate-500 hover:border-emerald-400 hover:text-emerald-600'">
              <i class="fa-solid fa-plus mr-1"></i>Add After
            </button>
            <button v-if="pages.length > 1" @click.stop="$emit('remove-page', pi)" title="Delete page"
              class="px-3 py-1 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
              :class="dark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-red-500 hover:text-red-400' : 'bg-white border-slate-200 text-slate-500 hover:border-red-400 hover:text-red-500'">
              <i class="fa-solid fa-trash mr-1"></i>Delete
            </button>
            <button @click.stop="$emit('duplicate-page', pi)" title="Duplicate page"
              class="px-3 py-1 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
              :class="dark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'bg-white border-slate-200 text-slate-500 hover:border-indigo-400 hover:text-indigo-600'">
              <i class="fa-regular fa-clone mr-1"></i>Duplicate
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- ── BOTTOM STATUS BAR ── -->
    <div class="absolute bottom-3 right-4 z-20 flex items-center gap-2">
      <!-- Element count badge -->
      <div class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider border backdrop-blur-xl transition-all"
        :class="dark ? 'bg-slate-900/80 border-slate-700/60 text-slate-500' : 'bg-white/80 border-slate-200/80 text-slate-400'">
        <i class="fa-solid fa-cube mr-1"></i>
        {{ (pages[selectedPageIndex]?.elements || []).length }} elements
      </div>
      <!-- Canvas size indicator -->
      <div class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider border backdrop-blur-xl"
        :class="dark ? 'bg-slate-900/80 border-slate-700/60 text-slate-500' : 'bg-white/80 border-slate-200/80 text-slate-400'">
        <i class="fa-solid fa-file mr-1"></i>
        {{ rs.page_size }} · {{ rs.orientation }}
      </div>
    </div>

    <!-- ── SELECTION BOX (multi-select rubber band) ── -->
    <div v-if="selectionBox.active"
      class="absolute pointer-events-none z-40 border-2 border-indigo-500 rounded"
      style="background: rgba(99,102,241,0.08);"
      :style="{
        left: selectionBox.x + 'px',
        top: selectionBox.y + 'px',
        width: selectionBox.width + 'px',
        height: selectionBox.height + 'px',
      }" />

    <!-- ── SNAP GUIDELINES ── -->
    <template v-if="snapGuides.h !== null">
      <div class="absolute left-0 right-0 pointer-events-none z-50"
        :style="{ top: snapGuides.h + 'px', height: '1px', background: '#6366f1', opacity: 0.7 }" />
    </template>
    <template v-if="snapGuides.v !== null">
      <div class="absolute top-0 bottom-0 pointer-events-none z-50"
        :style="{ left: snapGuides.v + 'px', width: '1px', background: '#6366f1', opacity: 0.7 }" />
    </template>

    <!-- ── CLOSE ZOOM MENU OVERLAY ── -->
    <div v-if="showZoomMenu" class="fixed inset-0 z-40" @click="showZoomMenu = false" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick, inject } from 'vue';
import ElementRenderer from '@/Components/Editor/ElementRenderer.vue';

const props = defineProps({
  pages: Array,
  selectedElement: Object,
  selectedPageIndex: { type: Number, default: 0 },
  zoom: { type: Number, default: 100 },
  dragMode: Boolean,
  showGrid: Boolean,
  showRulers: Boolean,
  gridSize: { type: Number, default: 20 },
  rs: Object,
  canvasDimensions: Object,
  historyIndex: Number,
  historyLength: Number,
  dark: { type: Boolean, default: false },
  snapToGrid: Boolean,
});

const emit = defineEmits([
  'update:zoom', 'select-element', 'deselect-all', 'add-element-at', 'delete-element',
  'duplicate-element', 'move-element-to-page', 'lock-element', 'add-page-after',
  'remove-page', 'duplicate-page', 'push-history', 'upload-image', 'open-context-menu',
  'start-drag', 'start-resize', 'start-rotation', 'fit-to-screen', 'text-blur',
  'list-item-blur', 'add-list-item', 'checklist-item-blur', 'add-checklist-item',
  'table-cell-blur', 'table-header-blur', 'add-table-row', 'add-table-column',
  'remove-table-row', 'remove-table-column', 'toc-title-blur', 'toc-item-blur',
  'add-toc-item', 'timeline-blur', 'add-timeline-item', 'bring-to-front', 'send-to-back',
  'bring-forward', 'send-backward', 'align-element', 'select-page',
]);

const canvasWrapper = ref(null);
const scrollArea = ref(null);
const hRulerCanvas = ref(null);
const vRulerCanvas = ref(null);
const showZoomMenu = ref(false);
const isDragOverPage = ref(false);
const selectionBox = ref({ active: false, x: 0, y: 0, width: 0, height: 0, startX: 0, startY: 0 });
const snapGuides = ref({ h: null, v: null });

const ZOOM_PRESETS = [25, 33, 50, 67, 75, 100, 125, 150, 200];

const RESIZE_HANDLES = [
  { dir: 'n',  style: { top: '-4px', left: '50%', transform: 'translateX(-50%)', cursor: 'n-resize' } },
  { dir: 's',  style: { bottom: '-4px', left: '50%', transform: 'translateX(-50%)', cursor: 's-resize' } },
  { dir: 'e',  style: { right: '-4px', top: '50%', transform: 'translateY(-50%)', cursor: 'e-resize' } },
  { dir: 'w',  style: { left: '-4px', top: '50%', transform: 'translateY(-50%)', cursor: 'w-resize' } },
  { dir: 'ne', style: { top: '-4px', right: '-4px', cursor: 'ne-resize' } },
  { dir: 'nw', style: { top: '-4px', left: '-4px', cursor: 'nw-resize' } },
  { dir: 'se', style: { bottom: '-4px', right: '-4px', cursor: 'se-resize' } },
  { dir: 'sw', style: { bottom: '-4px', left: '-4px', cursor: 'sw-resize' } },
];

const getHandleStyle = (handle) => ({
  ...handle.style,
  position: 'absolute',
  width: '8px',
  height: '8px',
  background: 'white',
  border: '2px solid #6366f1',
  borderRadius: '2px',
  zIndex: 100,
  boxShadow: '0 1px 3px rgba(0,0,0,0.3)',
});

const getElementWrapperStyle = (el) => {
  const s = el.styles || {};
  const zf = props.zoom / 100;
  return {
    left: (el.position?.x || 0) * zf + 'px',
    top: (el.position?.y || 0) * zf + 'px',
    width: (s.width || 200) * zf + 'px',
    height: (s.height || 50) * zf + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: s.rotate ? `rotate(${s.rotate}deg)` : undefined,
    borderRadius: s.borderRadius ? s.borderRadius * zf + 'px' : undefined,
    border: (s.borderWidth && s.borderWidth > 0)
      ? `${s.borderWidth * zf}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}`
      : undefined,
    padding: s.padding ? s.padding * zf + 'px' : undefined,
    boxShadow: getShadow(s.boxShadow, zf),
    cursor: props.dragMode ? 'grab' : (el.locked ? 'not-allowed' : 'move'),
    overflow: 'hidden',
    transition: 'box-shadow 0.15s ease',
  };
};

const getShadow = (val, zf) => {
  const shadows = {
    none: undefined,
    sm:   `0 ${1*zf}px ${3*zf}px rgba(0,0,0,.12)`,
    md:   `0 ${4*zf}px ${12*zf}px rgba(0,0,0,.1)`,
    lg:   `0 ${10*zf}px ${30*zf}px rgba(0,0,0,.15)`,
    xl:   `0 ${20*zf}px ${60*zf}px rgba(0,0,0,.2)`,
    '2xl': `0 ${25*zf}px ${80*zf}px rgba(0,0,0,.25)`,
    'glow-indigo': `0 0 ${20*zf}px rgba(99,102,241,.5)`,
    'glow-emerald': `0 0 ${20*zf}px rgba(16,185,129,.5)`,
    'glow-gold': `0 0 ${20*zf}px rgba(245,158,11,.5)`,
  };
  return val ? shadows[val] : undefined;
};

// ── ZOOM ──────────────────────────────────────────────────────────────────────
const increaseZoom = () => {
  const next = ZOOM_PRESETS.find(z => z > props.zoom);
  emit('update:zoom', next || 200);
};
const decreaseZoom = () => {
  const prev = [...ZOOM_PRESETS].reverse().find(z => z < props.zoom);
  emit('update:zoom', prev || 25);
};
const resetZoom = () => emit('update:zoom', 100);
const fitCanvas = () => {
  if (!scrollArea.value) return;
  const availW = scrollArea.value.clientWidth - 120;
  const availH = scrollArea.value.clientHeight - 120;
  const zW = Math.floor(availW / props.canvasDimensions.width * 100);
  const zH = Math.floor(availH / props.canvasDimensions.height * 100);
  emit('update:zoom', Math.min(150, Math.max(25, Math.min(zW, zH))));
  emit('fit-to-screen', scrollArea.value.clientWidth - 120);
};

// Mouse wheel zoom
const onWheelZoom = (e) => {
  if (!e.ctrlKey && !e.metaKey) return;
  e.preventDefault();
  const delta = e.deltaY < 0 ? 10 : -10;
  const newZoom = Math.min(200, Math.max(25, props.zoom + delta));
  emit('update:zoom', newZoom);
};

// ── MOUSE EVENTS ──────────────────────────────────────────────────────────────
const onElementMousedown = (e, el, pi) => {
  if (e.button !== 0) return;
  if (pi !== props.selectedPageIndex) {
    emit('select-page', pi);
    return;
  }
  if (el.locked) return;
  emit('select-element', el, pi);
  emit('start-drag', e, el, pi);
};

const onElementDblClick = (e, el, pi) => {
  // Allow inline editing for text elements
  if (!['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight', 'badge', 'callout', 'code'].includes(el.type)) return;
  const target = e.currentTarget.querySelector('[contenteditable]');
  if (target) {
    target.focus();
    const range = document.createRange();
    range.selectNodeContents(target);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
  }
};

const onCanvasMousedown = (e) => {
  if (e.target === scrollArea.value || e.target.classList.contains('canvas-scroll')) {
    emit('deselect-all');
  }
};
const onCanvasMousemove = () => {};
const onCanvasMouseup = () => {};

// ── DRAG FROM SIDEBAR ─────────────────────────────────────────────────────────
const handleDrop = (e) => {
  isDragOverPage.value = false;
  const type = e.dataTransfer.getData('elementType');
  if (!type) return;
  const rect = scrollArea.value.getBoundingClientRect();
  const zf = props.zoom / 100;
  // Find the active page element bounds
  const pageEl = document.getElementById('page-' + props.pages[props.selectedPageIndex]?.id);
  if (!pageEl) {
    emit('add-element-at', type, props.selectedPageIndex, 60, 60);
    return;
  }
  const pageRect = pageEl.getBoundingClientRect();
  const x = Math.round((e.clientX - pageRect.left) / zf);
  const y = Math.round((e.clientY - pageRect.top) / zf);
  emit('add-element-at', type, props.selectedPageIndex, Math.max(0, x), Math.max(0, y));
};

const handleDropOnPage = (e, pi) => {
  isDragOverPage.value = false;
  const type = e.dataTransfer.getData('elementType');
  if (!type) return;
  const pageEl = document.getElementById('page-' + props.pages[pi]?.id);
  if (!pageEl) return;
  const pageRect = pageEl.getBoundingClientRect();
  const zf = props.zoom / 100;
  const x = Math.round((e.clientX - pageRect.left) / zf);
  const y = Math.round((e.clientY - pageRect.top) / zf);
  emit('select-page', pi);
  nextTick(() => emit('add-element-at', type, pi, Math.max(0, x), Math.max(0, y)));
};

// ── RULERS ────────────────────────────────────────────────────────────────────
const drawRulers = () => {
  if (!props.showRulers) return;
  nextTick(() => {
    const hCanvas = hRulerCanvas.value;
    const vCanvas = vRulerCanvas.value;
    if (!hCanvas || !vCanvas) return;
    const hParent = hCanvas.parentElement;
    const vParent = vCanvas.parentElement;
    if (!hParent || !vParent) return;

    hCanvas.width = hParent.offsetWidth;
    hCanvas.height = 28;
    vCanvas.width = 28;
    vCanvas.height = vParent.offsetHeight;

    const dark = props.dark;
    const bg = dark ? '#0f172a' : '#e2e8f0';
    const text = dark ? '#475569' : '#94a3b8';
    const tick = dark ? '#334155' : '#cbd5e1';
    const zf = props.zoom / 100;
    const step = props.gridSize;

    // Horizontal
    const hCtx = hCanvas.getContext('2d');
    hCtx.fillStyle = bg;
    hCtx.fillRect(0, 0, hCanvas.width, hCanvas.height);
    hCtx.strokeStyle = tick;
    hCtx.fillStyle = text;
    hCtx.font = '9px monospace';
    for (let x = 0; x < props.canvasDimensions.width; x += step) {
      const px = x * zf;
      hCtx.beginPath();
      hCtx.moveTo(px, 18);
      hCtx.lineTo(px, 28);
      hCtx.stroke();
      if (x % (step * 5) === 0) {
        hCtx.fillText(x, px + 2, 14);
      }
    }

    // Vertical
    const vCtx = vCanvas.getContext('2d');
    vCtx.fillStyle = bg;
    vCtx.fillRect(0, 0, 28, vCanvas.height);
    vCtx.strokeStyle = tick;
    vCtx.fillStyle = text;
    vCtx.font = '9px monospace';
    for (let y = 0; y < props.canvasDimensions.height; y += step) {
      const py = y * zf;
      vCtx.beginPath();
      vCtx.moveTo(18, py);
      vCtx.lineTo(28, py);
      vCtx.stroke();
      if (y % (step * 5) === 0) {
        vCtx.save();
        vCtx.translate(14, py + 2);
        vCtx.rotate(-Math.PI / 2);
        vCtx.fillText(y, 0, 0);
        vCtx.restore();
      }
    }
  });
};

watch(() => [props.zoom, props.showRulers, props.dark], drawRulers);

// Keyboard shortcut for zoom
const onKeyDown = (e) => {
  if (e.ctrlKey || e.metaKey) {
    if (e.key === '=' || e.key === '+') { e.preventDefault(); increaseZoom(); }
    if (e.key === '-') { e.preventDefault(); decreaseZoom(); }
    if (e.key === '0') { e.preventDefault(); resetZoom(); }
  }
};

onMounted(() => {
  document.addEventListener('keydown', onKeyDown);
  if (scrollArea.value) {
    scrollArea.value.addEventListener('wheel', onWheelZoom, { passive: false });
  }
  drawRulers();
  // Auto-fit on mount
  nextTick(fitCanvas);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeyDown);
  if (scrollArea.value) {
    scrollArea.value.removeEventListener('wheel', onWheelZoom);
  }
});
</script>

<style scoped>
/* ── Canvas Workspace ── */
.canvas-workspace {
  background-image:
    radial-gradient(circle at 1px 1px, rgba(99,102,241,0.08) 1px, transparent 0);
  background-size: 32px 32px;
}

.canvas-scroll {
  scrollbar-width: thin;
  scrollbar-color: #4f46e5 transparent;
}
.canvas-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.canvas-scroll::-webkit-scrollbar-track { background: transparent; }
.canvas-scroll::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 99px; }
.canvas-scroll::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

/* ── Page Wrapper ── */
.page-wrapper {
  position: relative;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.page-wrapper:hover .page-controls {
  opacity: 1;
}

.page-shadow-wrapper {
  border-radius: 4px;
  box-shadow:
    0 4px 6px rgba(0,0,0,0.05),
    0 10px 25px rgba(0,0,0,0.08),
    0 20px 60px rgba(0,0,0,0.12),
    0 0 0 1px rgba(0,0,0,0.06);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.page-active .page-shadow-wrapper {
  box-shadow:
    0 0 0 2.5px #6366f1,
    0 8px 16px rgba(99,102,241,0.15),
    0 20px 50px rgba(0,0,0,0.15),
    0 40px 80px rgba(0,0,0,0.1);
}

.page-canvas {
  transition: background-color 0.2s ease;
}

/* ── Element Node ── */
.element-node {
  position: absolute;
  transition: filter 0.15s ease;
}

.element-node:hover:not(.element-selected) .element-hover-ring {
  opacity: 1;
}

.element-selected {
  /* Selection handled by border div */
}

.element-hidden {
  opacity: 0.3 !important;
}

.element-locked {
  cursor: not-allowed !important;
}

/* ── Selection Border ── */
.selection-border {
  position: absolute;
  inset: -2px;
  border: 2px solid #6366f1;
  border-radius: 3px;
  pointer-events: none;
  z-index: 90;
  box-shadow:
    0 0 0 1px rgba(255,255,255,0.8),
    0 0 12px rgba(99,102,241,0.4);
  animation: selectionPulse 2s ease-in-out infinite;
}

@keyframes selectionPulse {
  0%, 100% { box-shadow: 0 0 0 1px rgba(255,255,255,0.8), 0 0 12px rgba(99,102,241,0.4); }
  50% { box-shadow: 0 0 0 1px rgba(255,255,255,0.8), 0 0 20px rgba(99,102,241,0.6); }
}

/* ── Resize Handles ── */
.resize-handle {
  position: absolute;
  width: 8px;
  height: 8px;
  background: white;
  border: 2px solid #6366f1;
  border-radius: 2px;
  z-index: 100;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
  transition: transform 0.1s ease, background 0.1s ease;
}
.resize-handle:hover {
  background: #6366f1;
  transform: scale(1.3);
}

/* ── Rotation Handle ── */
.rotation-handle {
  position: absolute;
  top: -28px;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: grab;
  z-index: 100;
  box-shadow: 0 2px 8px rgba(99,102,241,0.5);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.rotation-handle::before {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 1px;
  height: 8px;
  background: #6366f1;
  opacity: 0.5;
}
.rotation-handle:hover {
  transform: translateX(-50%) scale(1.2);
  box-shadow: 0 4px 16px rgba(99,102,241,0.6);
}
.rotation-handle:active { cursor: grabbing; }

/* ── Element Actions Bar ── */
.element-actions {
  position: absolute;
  top: -38px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 1px;
  padding: 3px 6px;
  border-radius: 10px;
  border: 1px solid;
  box-shadow: 0 4px 16px rgba(0,0,0,0.15), 0 1px 4px rgba(0,0,0,0.1);
  z-index: 200;
  white-space: nowrap;
  backdrop-filter: blur(8px);
}

.action-btn {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  font-size: 10px;
  color: #64748b;
  transition: all 0.15s ease;
  cursor: pointer;
  border: none;
  background: transparent;
}
.action-btn:hover {
  background: rgba(99,102,241,0.1);
  color: #6366f1;
  transform: scale(1.1);
}

/* ── Element Info Badge ── */
.element-info-badge {
  position: absolute;
  bottom: -24px;
  left: 0;
  font-size: 9px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  white-space: nowrap;
  z-index: 100;
  text-transform: capitalize;
  letter-spacing: 0.04em;
  pointer-events: none;
}

/* ── Page Controls ── */
.page-controls {
  transition: opacity 0.2s ease;
}

/* ── Dropdown ── */
.dropdown-enter-active { transition: all 0.15s ease-out; }
.dropdown-leave-active { transition: all 0.1s ease-in; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateX(-50%) translateY(-6px) scale(0.95); }
.dropdown-enter-to { transform: translateX(-50%) translateY(0) scale(1); }

/* ── Animations ── */
@keyframes slideDown {
  from { opacity: 0; transform: translateX(-50%) translateY(-12px); }
  to { opacity: 1; transform: translateX(-50%) translateY(0); }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateX(-50%) translateY(4px); }
  to { opacity: 1; transform: translateX(-50%) translateY(0); }
}
</style>