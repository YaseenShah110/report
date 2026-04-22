<template>
  <div class="flex-1 flex flex-col overflow-hidden relative select-none"
       :class="dark ? 'bg-slate-950' : 'bg-slate-200/80'">

    <!-- Rulers -->
    <div v-if="showRulers" class="absolute top-0 left-0 right-0 z-30 pointer-events-none">
      <!-- Top ruler -->
      <div class="absolute top-0 left-8 right-0 h-8 flex items-end overflow-hidden"
           :class="dark ? 'bg-slate-900 border-b border-slate-800' : 'bg-white border-b border-slate-300'">
        <svg width="100%" height="32" class="w-full">
          <template v-for="i in 200" :key="i">
            <line :x1="((i * gridSize) * (zoom/100))" y1="20" :x2="((i * gridSize) * (zoom/100))" y2="32"
                  :stroke="dark ? '#334155' : '#cbd5e1'" stroke-width="1"/>
            <text v-if="i % 5 === 0" :x="((i * gridSize) * (zoom/100)) + 2" y="14"
                  font-size="8" :fill="dark ? '#475569' : '#94a3b8'" font-family="monospace">
              {{ i * gridSize }}
            </text>
          </template>
        </svg>
      </div>
      <!-- Left ruler -->
      <div class="absolute top-8 left-0 w-8 bottom-0 overflow-hidden"
           :class="dark ? 'bg-slate-900 border-r border-slate-800' : 'bg-white border-r border-slate-300'">
        <svg height="2000" width="32">
          <template v-for="i in 300" :key="i">
            <line x1="20" :y1="((i * gridSize) * (zoom/100))" x2="32" :y2="((i * gridSize) * (zoom/100))"
                  :stroke="dark ? '#334155' : '#cbd5e1'" stroke-width="1"/>
            <text v-if="i % 5 === 0" x="2" :y="((i * gridSize) * (zoom/100)) + 4"
                  font-size="8" :fill="dark ? '#475569' : '#94a3b8'" font-family="monospace"
                  writing-mode="vertical-rl" transform="rotate(180)">
            </text>
          </template>
        </svg>
      </div>
      <!-- Corner square -->
      <div class="absolute top-0 left-0 w-8 h-8 z-10"
           :class="dark ? 'bg-slate-900 border-b border-r border-slate-800' : 'bg-white border-b border-r border-slate-300'">
      </div>
    </div>

    <!-- Zoom + Page nav bar -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-30 flex items-center gap-1 px-3 py-1.5 rounded-2xl shadow-xl border backdrop-blur-sm transition-all"
         :class="dark ? 'bg-slate-800/90 border-slate-700 text-slate-300' : 'bg-white/90 border-slate-200 text-slate-600'">
      <!-- Page nav -->
      <div class="flex items-center gap-1 pr-2 border-r" :class="dark ? 'border-slate-700' : 'border-slate-200'">
        <button @click="$emit('select-page', Math.max(0, selectedPageIndex - 1))"
                :disabled="selectedPageIndex === 0"
                class="p-1 rounded-lg transition-all disabled:opacity-30 hover:bg-indigo-100 dark:hover:bg-indigo-900/30">
          <i class="fa-solid fa-chevron-left text-[10px]"></i>
        </button>
        <span class="text-[11px] font-bold tabular-nums px-1.5 py-0.5 rounded-lg"
              :class="dark ? 'text-slate-300' : 'text-slate-700'">
          {{ selectedPageIndex + 1 }} / {{ pages.length }}
        </span>
        <button @click="$emit('select-page', Math.min(pages.length - 1, selectedPageIndex + 1))"
                :disabled="selectedPageIndex === pages.length - 1"
                class="p-1 rounded-lg transition-all disabled:opacity-30 hover:bg-indigo-100 dark:hover:bg-indigo-900/30">
          <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      </div>
      <!-- Zoom -->
      <button @click="$emit('update:zoom', Math.max(25, zoom - 10))"
              class="p-1 rounded-lg transition-all hover:bg-slate-100 dark:hover:bg-slate-700">
        <i class="fa-solid fa-minus text-[10px]"></i>
      </button>
      <button @click="$emit('update:zoom', 100)"
              class="text-[11px] font-black tabular-nums min-w-[40px] text-center px-1.5 py-0.5 rounded-lg transition-all hover:bg-slate-100 dark:hover:bg-slate-700">
        {{ zoom }}%
      </button>
      <button @click="$emit('update:zoom', Math.min(200, zoom + 10))"
              class="p-1 rounded-lg transition-all hover:bg-slate-100 dark:hover:bg-slate-700">
        <i class="fa-solid fa-plus text-[10px]"></i>
      </button>
      <div class="w-px h-4 mx-0.5" :class="dark ? 'bg-slate-700' : 'bg-slate-200'"></div>
      <button @click="$emit('fit-to-screen', containerWidth)"
              class="text-[10px] font-bold px-2 py-1 rounded-lg transition-all hover:bg-slate-100 dark:hover:bg-slate-700">
        Fit
      </button>
    </div>

    <!-- Element count badge -->
    <div class="absolute top-4 right-4 z-20 flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border backdrop-blur-sm transition-all"
         :class="dark ? 'bg-slate-800/80 border-slate-700 text-slate-400' : 'bg-white/80 border-slate-200 text-slate-500'">
      <i class="fa-solid fa-layer-group text-[9px]"></i>
      {{ currentPage?.elements?.length ?? 0 }} elements
    </div>

    <!-- Quick add floating strip -->
    <div class="absolute top-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1 px-2 py-1.5 rounded-2xl shadow-lg border backdrop-blur-sm transition-all"
         :class="dark ? 'bg-slate-800/90 border-slate-700' : 'bg-white/90 border-slate-200'">
      <span class="text-[9px] font-black uppercase tracking-widest mr-1 pr-1.5 border-r"
            :class="dark ? 'text-slate-600 border-slate-700' : 'text-slate-400 border-slate-200'">Quick Add</span>
      <button v-for="qa in QUICK_ADD" :key="qa.type"
              @click="$emit('add-element-at', qa.type, selectedPageIndex, 60, 60)"
              :title="qa.label"
              class="p-1.5 rounded-xl transition-all hover:scale-110 active:scale-95 group relative"
              :class="dark ? 'hover:bg-indigo-500/20 text-slate-400 hover:text-indigo-400' : 'hover:bg-indigo-50 text-slate-500 hover:text-indigo-600'">
        <i :class="qa.icon + ' text-xs'"></i>
        <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-[9px] font-bold px-1.5 py-0.5 rounded-md shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all pointer-events-none"
              :class="dark ? 'bg-slate-700 text-slate-200' : 'bg-slate-800 text-white'">{{ qa.label }}</span>
      </button>
    </div>

    <!-- Main scroll area -->
    <div ref="scrollAreaRef" class="flex-1 overflow-auto"
         :class="[showRulers ? 'pl-8 pt-8' : '', dark ? 'bg-slate-950' : 'bg-slate-200/80']"
         @drop.prevent="handleDrop"
         @dragover.prevent
         @click.self="$emit('deselect-all')">

      <!-- Canvas background pattern -->
      <div class="absolute inset-0 pointer-events-none" :class="showRulers ? 'left-8 top-8' : 'left-0 top-0'">
        <svg width="100%" height="100%" class="opacity-40">
          <defs>
            <pattern id="dots" width="24" height="24" patternUnits="userSpaceOnUse">
              <circle cx="1" cy="1" r="1" :fill="dark ? '#334155' : '#cbd5e1'"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#dots)"/>
        </svg>
      </div>

      <!-- Pages container -->
      <div class="flex flex-col items-center py-10 gap-10 relative z-10 min-h-full">
        <div v-for="(page, pi) in pages" :key="page.id"
             class="relative flex-shrink-0 transition-all duration-300"
             :class="selectedPageIndex !== pi ? 'opacity-40 scale-[0.98]' : 'opacity-100 scale-100'"
             @click.self="$emit('deselect-all')">

          <!-- Page label -->
          <div class="absolute -top-7 left-0 flex items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-widest"
                  :class="dark ? 'text-slate-600' : 'text-slate-400'">
              {{ page.label || `Page ${pi + 1}` }}
            </span>
            <button v-if="pi > 0 || pages.length > 1"
                    @click="$emit('select-page', pi)"
                    class="text-[9px] px-2 py-0.5 rounded-full border transition-all"
                    :class="selectedPageIndex === pi
                      ? 'border-indigo-500 text-indigo-500 bg-indigo-500/10'
                      : dark ? 'border-slate-700 text-slate-600 hover:border-slate-600' : 'border-slate-300 text-slate-400 hover:border-slate-400'">
              {{ selectedPageIndex === pi ? 'Active' : 'Click to edit' }}
            </button>
          </div>

          <!-- The actual page canvas -->
          <div :id="'page-canvas-' + pi"
               class="relative overflow-hidden shadow-2xl transition-all duration-200"
               :style="pageStyle(pi)"
               @click="selectedPageIndex = pi; $emit('deselect-all')"
               @drop.prevent="e => handleDropOnPage(e, pi)"
               @dragover.prevent>

            <!-- Background image -->
            <div v-if="rs.bg_image"
                 class="absolute inset-0 pointer-events-none"
                 :style="{backgroundImage:`url(${rs.bg_image})`,backgroundSize:'cover',backgroundPosition:'center',opacity:0.15,zIndex:0}"/>

            <!-- Grid overlay -->
            <svg v-if="showGrid" class="absolute inset-0 pointer-events-none" width="100%" height="100%" :style="{zIndex:998}">
              <defs>
                <pattern :id="'grid-'+pi" :width="gridSize*(zoom/100)" :height="gridSize*(zoom/100)" patternUnits="userSpaceOnUse">
                  <path :d="`M ${gridSize*(zoom/100)} 0 L 0 0 0 ${gridSize*(zoom/100)}`"
                        fill="none" :stroke="dark ? 'rgba(99,102,241,0.12)' : 'rgba(99,102,241,0.1)'" stroke-width="0.5"/>
                </pattern>
              </defs>
              <rect width="100%" height="100%" :fill="`url(#grid-${pi})`"/>
            </svg>

            <!-- Watermark -->
            <div v-if="rs.watermark"
                 class="absolute inset-0 flex items-center justify-center pointer-events-none"
                 :style="{zIndex:997,opacity:0.07}">
              <span class="font-black text-slate-500 whitespace-nowrap select-none"
                    :style="{fontSize:(64*(zoom/100))+'px',transform:'rotate(-30deg)'}">
                {{ rs.watermark }}
              </span>
            </div>

            <!-- Page header -->
            <div v-if="rs.show_header"
                 class="absolute top-0 left-0 right-0 flex items-center px-4"
                 :style="{height:(32*(zoom/100))+'px',backgroundColor:rs.header_color||'#1e293b',zIndex:999}">
              <span :style="{fontSize:(11*(zoom/100))+'px',color:'rgba(255,255,255,0.7)',fontFamily:rs.font_family}">
                {{ rs.header_text }}
              </span>
            </div>

            <!-- Elements -->
            <div v-for="el in page.elements" :key="el.id"
                 v-show="!el.hidden"
                 class="absolute group/el"
                 :style="elWrapperStyle(el)"
                 @mousedown.stop="e => handleElementMousedown(e, el, pi)"
                 @contextmenu.prevent.stop="e => $emit('open-context-menu', e, el, pi)"
                 @click.stop="e => { if(!dragMode) $emit('select-element', el, pi) }">

              <!-- Element content -->
              <div class="w-full h-full overflow-hidden" :style="{borderRadius: (el.styles?.borderRadius||0)+'px'}">
                <ElementRenderer
                  :el="el" :pi="pi" :zoom="zoom" :rs="rs" :dark="dark"
                  :selected="selectedElement?.id === el.id"
                  @text-blur="(e) => $emit('text-blur', el, e)"
                  @list-item-blur="(i, e) => $emit('list-item-blur', el, i, e)"
                  @add-list-item="() => $emit('add-list-item', el)"
                  @checklist-item-blur="(i, e) => $emit('checklist-item-blur', el, i, e)"
                  @add-checklist-item="() => $emit('add-checklist-item', el)"
                  @table-cell-blur="(ri, ci, e) => $emit('table-cell-blur', el, ri, ci, e)"
                  @table-header-blur="(col, e) => $emit('table-header-blur', el, col, e)"
                  @add-table-row="() => $emit('add-table-row', el)"
                  @add-table-column="() => $emit('add-table-column', el)"
                  @remove-table-row="() => $emit('remove-table-row', el)"
                  @remove-table-column="() => $emit('remove-table-column', el)"
                  @toc-title-blur="e => $emit('toc-title-blur', el, e)"
                  @toc-item-blur="(i, f, e) => $emit('toc-item-blur', el, i, f, e)"
                  @add-toc-item="() => $emit('add-toc-item', el)"
                  @timeline-blur="(i, f, e) => $emit('timeline-blur', el, i, f, e)"
                  @add-timeline-item="() => $emit('add-timeline-item', el)"
                  @upload-image="e => $emit('upload-image', e, el)"
                />
              </div>

              <!-- Selection ring + handles (only when selected & not locked) -->
              <template v-if="selectedElement?.id === el.id && !el.locked">
                <!-- Selection border -->
                <div class="absolute inset-0 pointer-events-none ring-2 ring-indigo-500 ring-offset-0 transition-all"
                     :style="{borderRadius:(el.styles?.borderRadius||0)+'px', zIndex:1001}"/>

                <!-- Resize handles -->
                <template v-for="dir in RESIZE_DIRS" :key="dir.d">
                  <div class="absolute z-[1002] cursor-pointer transition-transform hover:scale-150"
                       :style="dir.style"
                       @mousedown.stop.prevent="e => $emit('start-resize', dir.d, e)">
                    <div class="w-2.5 h-2.5 rounded-sm bg-white border-2 border-indigo-500 shadow-md"/>
                  </div>
                </template>

                <!-- Rotation handle -->
                <div class="absolute z-[1002] flex flex-col items-center gap-0.5 cursor-grab"
                     :style="{top:'-28px',left:'50%',transform:'translateX(-50%)'}"
                     @mousedown.stop.prevent="e => $emit('start-rotation', e, el)">
                  <div class="w-px h-2.5 bg-indigo-400"></div>
                  <div class="w-4 h-4 rounded-full bg-white border-2 border-indigo-500 shadow-md flex items-center justify-center">
                    <i class="fa-solid fa-rotate text-[8px] text-indigo-500"></i>
                  </div>
                </div>

                <!-- Info tooltip -->
                <div class="absolute -top-7 left-0 flex items-center gap-1.5 pointer-events-none"
                     :style="{zIndex:1003}">
                  <span class="text-[9px] font-bold px-2 py-0.5 rounded-full shadow-md"
                        :class="dark ? 'bg-indigo-600 text-white' : 'bg-indigo-600 text-white'">
                    {{ Math.round(el.styles?.width || 0) }} × {{ Math.round(el.styles?.height || 0) }}
                  </span>
                  <span class="text-[9px] font-bold px-2 py-0.5 rounded-full shadow-md"
                        :class="dark ? 'bg-slate-700 text-slate-300' : 'bg-white text-slate-600 border border-slate-200'">
                    {{ Math.round(el.position.x) }}, {{ Math.round(el.position.y) }}
                  </span>
                </div>

                <!-- Quick action bar -->
                <div class="absolute flex items-center gap-0.5 pointer-events-auto"
                     :style="{bottom:'-32px', left:'0', zIndex:1003}">
                  <button @mousedown.stop @click.stop="$emit('duplicate-element', pi, el)"
                          class="h-6 px-2 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all shadow-sm"
                          :class="dark ? 'bg-slate-700 text-slate-300 hover:bg-indigo-600 hover:text-white' : 'bg-white text-slate-600 hover:bg-indigo-600 hover:text-white border border-slate-200'">
                    <i class="fa-solid fa-copy text-[9px]"></i> Copy
                  </button>
                  <button @mousedown.stop @click.stop="$emit('bring-to-front')"
                          class="h-6 px-2 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all shadow-sm"
                          :class="dark ? 'bg-slate-700 text-slate-300 hover:bg-slate-600' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'">
                    <i class="fa-solid fa-arrow-up text-[9px]"></i>
                  </button>
                  <button @mousedown.stop @click.stop="$emit('send-to-back')"
                          class="h-6 px-2 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all shadow-sm"
                          :class="dark ? 'bg-slate-700 text-slate-300 hover:bg-slate-600' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'">
                    <i class="fa-solid fa-arrow-down text-[9px]"></i>
                  </button>
                  <button @mousedown.stop @click.stop="$emit('delete-element', pi, el.id)"
                          class="h-6 px-2 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all shadow-sm"
                          :class="dark ? 'bg-slate-700 text-red-400 hover:bg-red-600 hover:text-white' : 'bg-white text-red-500 hover:bg-red-500 hover:text-white border border-slate-200'">
                    <i class="fa-solid fa-trash text-[9px]"></i>
                  </button>
                </div>
              </template>

              <!-- Locked indicator -->
              <div v-if="el.locked"
                   class="absolute inset-0 pointer-events-none flex items-center justify-center"
                   :style="{zIndex:1001}">
                <div class="absolute inset-0 ring-2 ring-amber-400/60 rounded"
                     :style="{borderRadius:(el.styles?.borderRadius||0)+'px'}"/>
                <i class="fa-solid fa-lock text-amber-400 text-xs opacity-60"></i>
              </div>

              <!-- Hidden indicator (show in editor even if hidden) -->
              <div v-if="el.hidden && selectedElement?.id === el.id"
                   class="absolute inset-0 flex items-center justify-center pointer-events-none"
                   :style="{zIndex:1001,background:'rgba(0,0,0,0.35)',borderRadius:(el.styles?.borderRadius||0)+'px'}">
                <i class="fa-solid fa-eye-slash text-white text-sm"></i>
              </div>
            </div>

            <!-- Drop zone hint (when dragging from sidebar) -->
            <div v-if="isDroppingOnPage === pi"
                 class="absolute inset-0 z-[990] flex items-center justify-center pointer-events-none transition-all"
                 :class="dark ? 'bg-indigo-500/10' : 'bg-indigo-500/5'">
              <div class="px-6 py-4 rounded-2xl border-2 border-dashed border-indigo-500/60 text-indigo-500 text-sm font-bold">
                <i class="fa-solid fa-plus-circle mr-2"></i>Drop to add element
              </div>
            </div>

            <!-- Page footer -->
            <div v-if="rs.show_page_numbers || rs.footer_left || rs.footer_right"
                 class="absolute bottom-0 left-0 right-0 flex items-center px-4"
                 :style="{
                   height:(36*(zoom/100))+'px',
                   borderTop:`1px solid ${rs.primary_color||'#6366f1'}20`,
                   backgroundColor: rs.show_footer ? (rs.footer_color||'#1e293b') : 'transparent',
                   zIndex:999
                 }">
              <span class="flex-1" :style="{fontSize:(10*(zoom/100))+'px',color:'#94a3b8',fontFamily:rs.font_family}">
                {{ rs.footer_left }}
              </span>
              <span v-if="rs.show_page_numbers" :style="{fontSize:(11*(zoom/100))+'px',color:'#94a3b8'}">
                {{ pi + 1 }}{{ pages.length > 1 ? ` / ${pages.length}` : '' }}
              </span>
              <span class="flex-1 text-right" :style="{fontSize:(10*(zoom/100))+'px',color:'#94a3b8',fontFamily:rs.font_family}">
                {{ rs.footer_right }}
              </span>
            </div>
          </div>

          <!-- Page action buttons below -->
          <div class="absolute -bottom-9 left-0 right-0 flex items-center justify-center gap-2 opacity-0 hover:opacity-100 transition-all duration-200">
            <button @click="$emit('add-page-after', pi)"
                    class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border shadow-sm transition-all hover:scale-105"
                    :class="dark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'bg-white border-slate-200 text-slate-500 hover:border-indigo-400 hover:text-indigo-600'">
              <i class="fa-solid fa-plus"></i> Add Page After
            </button>
            <button v-if="pages.length > 1"
                    @click="$emit('remove-page', pi)"
                    class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border shadow-sm transition-all hover:scale-105"
                    :class="dark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-red-500 hover:text-red-400' : 'bg-white border-slate-200 text-slate-500 hover:border-red-400 hover:text-red-500'">
              <i class="fa-solid fa-trash"></i> Delete Page
            </button>
            <button @click="$emit('duplicate-page', pi)"
                    class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border shadow-sm transition-all hover:scale-105"
                    :class="dark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-500' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-400'">
              <i class="fa-regular fa-clone"></i> Duplicate
            </button>
          </div>
        </div>

        <!-- Add first page button when empty -->
        <div v-if="!pages.length"
             class="flex flex-col items-center justify-center gap-4 py-20">
          <div class="w-20 h-20 rounded-2xl flex items-center justify-center"
               :class="dark ? 'bg-slate-800 border border-slate-700' : 'bg-white border border-slate-200'">
            <i class="fa-solid fa-file-circle-plus text-3xl text-slate-400"></i>
          </div>
          <p class="text-sm font-bold" :class="dark ? 'text-slate-500' : 'text-slate-400'">No pages yet</p>
          <button @click="$emit('add-page-after', -1)"
                  class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold transition-all hover:scale-105">
            Add Page
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted, onBeforeUnmount } from 'vue';
import ElementRenderer from '@/Components/Editor/ElementRenderer.vue';

const props = defineProps({
  dark: Boolean,
  pages: Array,
  selectedElement: Object,
  selectedPageIndex: Number,
  zoom: Number,
  dragMode: Boolean,
  showGrid: Boolean,
  showRulers: Boolean,
  gridSize: Number,
  rs: Object,
  canvasDimensions: Object,
  historyIndex: Number,
  historyLength: Number,
});

const emit = defineEmits([
  'update:zoom','select-element','deselect-all','add-element-at','delete-element',
  'duplicate-element','move-element-to-page','lock-element','add-page-after','remove-page',
  'duplicate-page','push-history','upload-image','open-context-menu','start-drag',
  'start-resize','start-rotation','fit-to-screen','text-blur','list-item-blur',
  'add-list-item','checklist-item-blur','add-checklist-item','table-cell-blur',
  'table-header-blur','add-table-row','add-table-column','remove-table-row',
  'remove-table-column','toc-title-blur','toc-item-blur','add-toc-item','timeline-blur',
  'add-timeline-item','bring-to-front','send-to-back','bring-forward','send-backward',
  'align-element','select-page',
]);

const QUICK_ADD = inject('QUICK_ADD');
const scrollAreaRef = ref(null);
const containerWidth = ref(800);
const isDroppingOnPage = ref(null);

const RESIZE_DIRS = [
  { d: 'nw', style: 'top:-5px;left:-5px;cursor:nw-resize' },
  { d: 'n',  style: 'top:-5px;left:50%;transform:translateX(-50%);cursor:n-resize' },
  { d: 'ne', style: 'top:-5px;right:-5px;cursor:ne-resize' },
  { d: 'e',  style: 'top:50%;right:-5px;transform:translateY(-50%);cursor:e-resize' },
  { d: 'se', style: 'bottom:-5px;right:-5px;cursor:se-resize' },
  { d: 's',  style: 'bottom:-5px;left:50%;transform:translateX(-50%);cursor:s-resize' },
  { d: 'sw', style: 'bottom:-5px;left:-5px;cursor:sw-resize' },
  { d: 'w',  style: 'top:50%;left:-5px;transform:translateY(-50%);cursor:w-resize' },
];

const currentPage = computed(() => props.pages[props.selectedPageIndex]);
const zf = computed(() => props.zoom / 100);

const pageStyle = (pi) => {
  const w = props.canvasDimensions.width * zf.value;
  const h = props.canvasDimensions.height * zf.value;
  const isSelected = props.selectedPageIndex === pi;
  return {
    width: w + 'px',
    height: h + 'px',
    backgroundColor: props.rs.background_color || '#ffffff',
    fontFamily: props.rs.font_family || "'DM Sans', sans-serif",
    borderRadius: (props.rs.page_radius || 0) + 'px',
    boxShadow: isSelected
      ? (props.dark ? '0 0 0 2px #6366f1, 0 25px 80px rgba(0,0,0,0.5)' : '0 0 0 2px #6366f1, 0 25px 80px rgba(0,0,0,0.2)')
      : (props.dark ? '0 20px 60px rgba(0,0,0,0.5)' : '0 20px 60px rgba(0,0,0,0.15)'),
    cursor: props.dragMode ? 'default' : 'crosshair',
    transition: 'box-shadow 0.2s ease',
    position: 'relative',
    overflow: 'hidden',
  };
};

const elWrapperStyle = (el) => {
  const zf_ = props.zoom / 100;
  const s = el.styles || {};
  const shadows = {
    none: 'none', sm: '0 1px 3px rgba(0,0,0,.12)', md: '0 4px 12px rgba(0,0,0,.1)',
    lg: '0 10px 30px rgba(0,0,0,.15)', xl: '0 20px 60px rgba(0,0,0,.2)',
    '2xl': '0 25px 80px rgba(0,0,0,.25)', inner: 'inset 0 2px 4px rgba(0,0,0,.1)',
    'glow-indigo': '0 0 20px rgba(99,102,241,.5)',
    'glow-emerald': '0 0 20px rgba(16,185,129,.5)',
    'glow-gold': '0 0 20px rgba(245,158,11,.5)',
  };
  return {
    left: (el.position.x * zf_) + 'px',
    top: (el.position.y * zf_) + 'px',
    width: ((s.width || 200) * zf_) + 'px',
    height: ((s.height || 50) * zf_) + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: s.rotate ? `rotate(${s.rotate}deg)` : undefined,
    boxShadow: shadows[s.boxShadow] || undefined,
    border: s.borderWidth ? `${s.borderWidth * zf_}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : undefined,
    borderRadius: (s.borderRadius || 0) + 'px',
    padding: s.padding ? (s.padding * zf_) + 'px' : undefined,
    cursor: el.locked ? 'not-allowed' : (props.dragMode ? 'grab' : 'move'),
    transition: 'opacity 0.15s ease, box-shadow 0.15s ease',
  };
};

const handleElementMousedown = (e, el, pi) => {
  if (e.button !== 0) return;
  emit('select-element', el, pi);
  if (!el.locked && props.dragMode) {
    emit('start-drag', e, el, pi);
  } else if (!el.locked) {
    emit('start-drag', e, el, pi);
  }
};

const handleDrop = (e) => {
  const type = e.dataTransfer.getData('elementType');
  if (!type) return;
  isDroppingOnPage.value = null;
};

const handleDropOnPage = (e, pi) => {
  const type = e.dataTransfer.getData('elementType');
  if (!type) return;
  const rect = e.currentTarget.getBoundingClientRect();
  const x = Math.max(0, (e.clientX - rect.left) / (props.zoom / 100));
  const y = Math.max(0, (e.clientY - rect.top) / (props.zoom / 100));
  emit('add-element-at', type, pi, Math.round(x), Math.round(y));
  isDroppingOnPage.value = null;
};

const resizeObs = typeof ResizeObserver !== 'undefined'
  ? new ResizeObserver(entries => {
      for (const e of entries) containerWidth.value = e.contentRect.width;
    })
  : null;

onMounted(() => {
  if (scrollAreaRef.value && resizeObs) resizeObs.observe(scrollAreaRef.value);
});
onBeforeUnmount(() => {
  if (resizeObs) resizeObs.disconnect();
});
</script>

<style scoped>
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #475569; border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: #6366f1; }
</style>