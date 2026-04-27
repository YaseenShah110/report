<template>
  <div
    ref="canvasWrapper"
    class="flex-1 flex flex-col overflow-hidden relative"
    :class="dark ? 'bg-[#0d1117]' : 'bg-[#e8eaed]'"
    style="background-image: radial-gradient(circle, rgba(99,102,241,0.06) 1px, transparent 1px); background-size: 28px 28px;"
    @dragover.prevent="isDragOverPage = true"
    @dragleave="isDragOverPage = false"
    @drop.prevent="handleDrop"
  >
    <!-- RULERS -->
    <template v-if="showRulers">
      <div class="absolute top-0 left-0 z-30 w-6 h-6 flex-shrink-0"
        :class="dark ? 'bg-[#161b22] border-b border-r border-[#30363d]' : 'bg-slate-200 border-b border-r border-slate-300'" style="z-index:31"/>
      <div class="absolute top-0 z-30 overflow-hidden"
        :class="dark ? 'bg-[#161b22] border-b border-[#30363d]' : 'bg-slate-200 border-b border-slate-300'"
        style="left:24px;right:0;height:24px;">
        <canvas ref="hRulerCanvas" style="width:100%;height:24px;display:block"/>
      </div>
      <div class="absolute left-0 z-30 overflow-hidden"
        :class="dark ? 'bg-[#161b22] border-r border-[#30363d]' : 'bg-slate-200 border-r border-slate-300'"
        style="top:24px;bottom:0;width:24px;">
        <canvas ref="vRulerCanvas" style="width:24px;height:100%;display:block"/>
      </div>
    </template>

    <!-- TOP FLOATING TOOLBAR -->
    <div class="absolute top-3 left-1/2 z-20 flex items-center gap-1 px-2.5 py-1.5 rounded-2xl border backdrop-blur-xl shadow-2xl"
      :class="dark ? 'bg-[#161b22]/95 border-[#30363d] shadow-black/50' : 'bg-white/95 border-slate-200/80 shadow-slate-400/20'"
      style="transform:translateX(-50%);animation:slideDown 0.3s cubic-bezier(0.16,1,0.3,1)">

      <!-- Zoom controls -->
      <button @click="decreaseZoom" title="Zoom Out (Ctrl+-)"
        class="w-7 h-7 flex items-center justify-center rounded-lg text-xs transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100'">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 5h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
      </button>
      <div class="relative">
        <button @click="showZoomMenu = !showZoomMenu"
          class="h-7 px-2.5 rounded-lg text-[11px] font-black tabular-nums border transition-all hover:scale-105 min-w-[52px]"
          :class="dark ? 'bg-slate-800/80 border-slate-700 text-indigo-400' : 'bg-indigo-50 border-indigo-200 text-indigo-600'">
          {{ zoom }}%
        </button>
        <transition name="dd">
          <div v-if="showZoomMenu" class="absolute top-full mt-1 left-1/2 -translate-x-1/2 w-24 rounded-xl border overflow-hidden z-50 shadow-xl"
            :class="dark ? 'bg-[#1c2128] border-[#30363d]' : 'bg-white border-slate-200'">
            <button v-for="z in [25,50,75,100,125,150,200]" :key="z"
              @click="$emit('update:zoom', z); showZoomMenu=false"
              class="w-full py-1.5 text-xs font-bold text-center transition-colors"
              :class="zoom===z ? 'bg-indigo-600 text-white' : (dark ? 'text-slate-300 hover:bg-white/5' : 'text-slate-600 hover:bg-slate-50')">
              {{ z }}%
            </button>
          </div>
        </transition>
      </div>
      <button @click="increaseZoom" title="Zoom In (Ctrl++)"
        class="w-7 h-7 flex items-center justify-center rounded-lg text-xs transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100'">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M5 2v6M2 5h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
      </button>

      <div class="w-px h-4" :class="dark ? 'bg-[#30363d]' : 'bg-slate-200'"/>

      <button @click="fitCanvas" title="Fit to Screen (Ctrl+0)"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100'">
        <i class="fa-solid fa-expand text-[10px]"></i>
      </button>
      <button @click="resetZoom" title="Reset to 100%"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-slate-200' : 'text-slate-500 hover:bg-slate-100'">
        <i class="fa-solid fa-compress text-[10px]"></i>
      </button>

      <div class="w-px h-4" :class="dark ? 'bg-[#30363d]' : 'bg-slate-200'"/>

      <!-- Page indicator -->
      <div class="flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black"
        :class="dark ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-500'">
        <i class="fa-regular fa-file text-[9px]"></i>
        <span>{{ selectedPageIndex + 1 }}/{{ pages.length }}</span>
      </div>

      <!-- Page nav buttons -->
      <button @click="$emit('select-page', Math.max(0, selectedPageIndex-1))"
        :disabled="selectedPageIndex === 0"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 disabled:opacity-30"
        :class="dark ? 'text-slate-400 hover:bg-white/10' : 'text-slate-500 hover:bg-slate-100'">
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
      </button>
      <button @click="$emit('select-page', Math.min(pages.length-1, selectedPageIndex+1))"
        :disabled="selectedPageIndex === pages.length - 1"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 disabled:opacity-30"
        :class="dark ? 'text-slate-400 hover:bg-white/10' : 'text-slate-500 hover:bg-slate-100'">
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
      </button>

      <div class="w-px h-4" :class="dark ? 'bg-[#30363d]' : 'bg-slate-200'"/>

      <!-- Add page -->
      <button @click="$emit('add-page-after', selectedPageIndex)" title="Add Page After"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95 text-emerald-500 hover:bg-emerald-500/10">
        <i class="fa-solid fa-file-circle-plus text-[11px]"></i>
      </button>

      <!-- Duplicate page -->
      <button @click="$emit('duplicate-page', selectedPageIndex)" title="Duplicate Page"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95"
        :class="dark ? 'text-slate-400 hover:bg-white/10 hover:text-indigo-400' : 'text-slate-500 hover:bg-indigo-50 hover:text-indigo-600'">
        <i class="fa-regular fa-clone text-[10px]"></i>
      </button>

      <!-- Delete page -->
      <button v-if="pages.length > 1" @click="$emit('remove-page', selectedPageIndex)" title="Delete Page"
        class="w-7 h-7 flex items-center justify-center rounded-lg transition-all hover:scale-110 active:scale-95 text-red-400 hover:bg-red-500/10">
        <i class="fa-solid fa-trash text-[10px]"></i>
      </button>
    </div>

    <!-- SCROLL AREA -->
    <div
      ref="scrollArea"
      class="flex-1 overflow-auto"
      :style="showRulers ? 'padding-top:24px;padding-left:24px;' : ''"
      style="scrollbar-width:thin"
      @mousedown="onScrollAreaMousedown"
      @click.self="$emit('deselect-all')"
    >
      <div class="flex flex-col items-center gap-10 py-16 px-8 min-h-full"
        :style="{ minWidth: (canvasDimensions.width * zoom / 100 + 160) + 'px' }">

        <!-- Each Page -->
        <div
          v-for="(page, pi) in pages"
          :key="page.id"
          class="relative group/page"
        >
          <!-- Page label above -->
          <div class="absolute -top-7 left-0 flex items-center gap-2">
            <button
              @click="$emit('select-page', pi)"
              class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all"
              :class="pi === selectedPageIndex
                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30'
                : (dark ? 'bg-[#1c2128] text-slate-500 hover:bg-[#262d36] hover:text-slate-300' : 'bg-white/70 text-slate-400 hover:bg-white hover:text-slate-600 border border-slate-200/60')">
              <i class="fa-regular fa-file text-[9px]"></i>
              {{ page.label || `Page ${pi + 1}` }}
            </button>
            <span class="text-[9px] font-bold" :class="dark ? 'text-slate-600' : 'text-slate-400'">
              {{ (page.elements || []).filter(e => !e.hidden).length }} elements
            </span>
          </div>

          <!-- Page shadow ring -->
          <div
            class="page-ring transition-all duration-300"
            :class="pi === selectedPageIndex ? 'page-ring-active' : 'page-ring-inactive'"
            :style="{
              width: (canvasDimensions.width * zoom / 100) + 'px',
              height: (canvasDimensions.height * zoom / 100) + 'px',
              borderRadius: Math.max(2, (rs.page_radius || 0) * zoom / 100) + 'px',
            }">

            <!-- THE ACTUAL PAGE CANVAS -->
            <div
              :id="'page-' + page.id"
              class="relative overflow-hidden select-none"
              :style="{
                width: (canvasDimensions.width * zoom / 100) + 'px',
                height: (canvasDimensions.height * zoom / 100) + 'px',
                backgroundColor: rs.background_color || '#ffffff',
                fontFamily: rs.font_family || 'sans-serif',
                borderRadius: Math.max(2, (rs.page_radius || 0) * zoom / 100) + 'px',
                backgroundImage: rs.bg_image ? `url(${rs.bg_image})` : undefined,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                cursor: dragMode ? 'crosshair' : 'default',
              }"
              @click.self="$emit('deselect-all')"
              @dragover.prevent="dragOverPageId = page.id"
              @dragleave="dragOverPageId = null"
              @drop.prevent="e => handleDropOnPage(e, pi)"
            >
              <!-- Grid overlay -->
              <svg v-if="showGrid && pi === selectedPageIndex"
                class="absolute inset-0 pointer-events-none"
                :width="canvasDimensions.width * zoom / 100"
                :height="canvasDimensions.height * zoom / 100"
                style="z-index:8;opacity:0.5">
                <defs>
                  <pattern :id="`grid-${pi}`" :width="gridSize * zoom/100" :height="gridSize * zoom/100" patternUnits="userSpaceOnUse">
                    <path :d="`M ${gridSize * zoom/100} 0 L 0 0 0 ${gridSize * zoom/100}`"
                      fill="none" :stroke="dark ? '#6366f1' : '#6366f1'" stroke-width="0.4" stroke-dasharray="1,2"/>
                  </pattern>
                  <pattern :id="`grid-major-${pi}`" :width="gridSize * zoom/100 * 5" :height="gridSize * zoom/100 * 5" patternUnits="userSpaceOnUse">
                    <path :d="`M ${gridSize * zoom/100 * 5} 0 L 0 0 0 ${gridSize * zoom/100 * 5}`"
                      fill="none" :stroke="dark ? '#6366f1' : '#6366f1'" stroke-width="0.7"/>
                  </pattern>
                </defs>
                <rect width="100%" height="100%" :fill="`url(#grid-${pi})`"/>
                <rect width="100%" height="100%" :fill="`url(#grid-major-${pi})`"/>
              </svg>

              <!-- Header -->
              <div v-if="rs.show_header" class="absolute top-0 left-0 right-0 flex items-center justify-between"
                :style="{
                  height: (40 * zoom/100) + 'px',
                  backgroundColor: rs.header_color || '#1e293b',
                  padding: `0 ${(rs.margin || 40) * zoom/100}px`,
                  zIndex: 5,
                }">
                <span :style="{ fontSize:(11*zoom/100)+'px', color:'#fff', fontFamily:rs.font_family, fontWeight:'600' }">
                  {{ rs.header_text || '' }}
                </span>
                <span :style="{ fontSize:(10*zoom/100)+'px', color:'rgba(255,255,255,0.5)' }">
                  {{ page.label || `Page ${pi + 1}` }}
                </span>
              </div>

              <!-- Watermark -->
              <div v-if="rs.watermark" class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden" style="z-index:3">
                <span :style="{
                  fontSize: (80 * zoom/100) + 'px',
                  fontWeight: '900',
                  color: dark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)',
                  transform: 'rotate(-30deg)',
                  whiteSpace: 'nowrap',
                  userSelect: 'none',
                  letterSpacing: '0.1em',
                }">{{ rs.watermark }}</span>
              </div>

              <!-- ELEMENTS -->
              <div
                v-for="el in (page.elements || [])"
                :key="el.id"
                class="absolute element-node"
                :class="{
                  'ring-[1.5px] ring-indigo-500': selectedElement?.id === el.id && pi === selectedPageIndex,
                  'opacity-30': el.hidden,
                  'pointer-events-none': pi !== selectedPageIndex && pi !== selectedPageIndex,
                }"
                :style="getElementWrapperStyle(el)"
                @mousedown.stop="onElementMousedown($event, el, pi)"
                @dblclick.stop="onElementDblClick($event, el, pi)"
                @contextmenu.prevent.stop="$emit('open-context-menu', $event, el, pi)"
              >
                <!-- Element Content -->
                <ElementRenderer
                  :el="el"
                  :pi="pi"
                  :zoom="zoom"
                  :rs="rs"
                  :dark="dark"
                  :selected="selectedElement?.id === el.id && pi === selectedPageIndex"
                  @text-blur="e => $emit('text-blur', el, e)"
                  @list-item-blur="(i,e) => $emit('list-item-blur', el, i, e)"
                  @add-list-item="$emit('add-list-item', el)"
                  @checklist-item-blur="(i,e) => $emit('checklist-item-blur', el, i, e)"
                  @add-checklist-item="$emit('add-checklist-item', el)"
                  @table-cell-blur="(ri,ci,e) => $emit('table-cell-blur', el, ri, ci, e)"
                  @table-header-blur="(col,e) => $emit('table-header-blur', el, col, e)"
                  @toc-title-blur="e => $emit('toc-title-blur', el, e)"
                  @toc-item-blur="(i,f,e) => $emit('toc-item-blur', el, i, f, e)"
                  @add-toc-item="$emit('add-toc-item', el)"
                  @timeline-blur="(i,f,e) => $emit('timeline-blur', el, i, f, e)"
                  @add-timeline-item="$emit('add-timeline-item', el)"
                  @upload-image="e => $emit('upload-image', e, el)"
                  @push-history="$emit('push-history')"
                />

                <!-- Selection UI -->
                <template v-if="selectedElement?.id === el.id && pi === selectedPageIndex && !el.locked">
                  <!-- Glow border -->
                  <div class="absolute pointer-events-none"
                    style="inset:-2px;border:2px solid #6366f1;border-radius:3px;z-index:90;box-shadow:0 0 0 1px rgba(255,255,255,0.6),0 0 16px rgba(99,102,241,0.35);animation:selBlink 2.5s ease-in-out infinite"/>

                  <!-- Resize handles -->
                  <div v-for="h in RESIZE_HANDLES" :key="h.dir"
                    class="absolute resize-handle"
                    :style="getHandleStyle(h)"
                    @mousedown.stop.prevent="$emit('start-resize', h.dir, $event)"/>

                  <!-- Rotation handle -->
                  <div class="rotation-handle absolute"
                    style="top:-28px;left:50%;transform:translateX(-50%);width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;cursor:grab;z-index:100;box-shadow:0 2px 8px rgba(99,102,241,0.5)"
                    @mousedown.stop.prevent="$emit('start-rotation', $event, el)"
                    title="Rotate">
                    <i class="fa-solid fa-rotate text-white" style="font-size:8px"></i>
                  </div>
                  <!-- Rotation line -->
                  <div class="absolute pointer-events-none" style="top:-10px;left:50%;transform:translateX(-50%);width:1px;height:10px;background:#6366f1;opacity:0.5;z-index:99"/>

                  <!-- Quick action toolbar -->
                  <div class="absolute flex items-center gap-0.5 px-1.5 py-1 rounded-xl border shadow-xl backdrop-blur-xl"
                    :class="dark ? 'bg-[#1c2128]/95 border-[#30363d]' : 'bg-white/95 border-slate-200'"
                    style="top:-38px;left:50%;transform:translateX(-50%);z-index:200;white-space:nowrap;animation:fadeUp 0.15s ease">
                    <button @click.stop="$emit('duplicate-element', pi, el)" title="Duplicate (Ctrl+D)" class="action-btn-sm">
                      <i class="fa-regular fa-clone text-[10px]"></i>
                    </button>
                    <button @click.stop="$emit('bring-to-front')" title="Bring to Front" class="action-btn-sm">
                      <i class="fa-solid fa-angles-up text-[10px]"></i>
                    </button>
                    <button @click.stop="$emit('send-to-back')" title="Send to Back" class="action-btn-sm">
                      <i class="fa-solid fa-angles-down text-[10px]"></i>
                    </button>
                    <button @click.stop="el.hidden = !el.hidden; $emit('push-history')" :title="el.hidden ? 'Show' : 'Hide'" class="action-btn-sm" :class="el.hidden ? 'text-amber-500' : ''">
                      <i :class="el.hidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" style="font-size:10px"></i>
                    </button>
                    <button @click.stop="$emit('lock-element', el)" :title="el.locked ? 'Unlock' : 'Lock'" class="action-btn-sm" :class="el.locked ? 'text-amber-500' : ''">
                      <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" style="font-size:10px"></i>
                    </button>
                    <div class="w-px h-3 mx-0.5" :class="dark ? 'bg-[#30363d]' : 'bg-slate-200'"/>
                    <button @click.stop="$emit('delete-element', pi, el.id)" title="Delete (Del)" class="action-btn-sm text-red-400 hover:text-red-600 hover:bg-red-50">
                      <i class="fa-solid fa-trash text-[10px]"></i>
                    </button>
                  </div>

                  <!-- Element type badge -->
                  <div class="absolute pointer-events-none rounded px-1.5 py-0.5 text-white font-bold capitalize"
                    style="bottom:-22px;left:0;font-size:9px;background:#6366f1;white-space:nowrap;z-index:100;letter-spacing:0.04em">
                    {{ el.type.replace(/-/g,' ') }} · {{ Math.round(el.styles?.width||0) }}×{{ Math.round(el.styles?.height||0) }}
                  </div>
                </template>

                <!-- Lock badge -->
                <div v-if="el.locked && pi === selectedPageIndex"
                  class="absolute -top-2.5 -right-2.5 w-5 h-5 rounded-full flex items-center justify-center shadow-md"
                  style="background:#f59e0b;z-index:20">
                  <i class="fa-solid fa-lock text-white" style="font-size:7px"></i>
                </div>

                <!-- Hidden overlay -->
                <div v-if="el.hidden" class="absolute inset-0 flex items-center justify-center rounded pointer-events-none" style="background:rgba(15,23,42,0.45);z-index:15">
                  <i class="fa-solid fa-eye-slash text-white" style="font-size:18px;opacity:0.5"></i>
                </div>
              </div>

              <!-- Drop zone highlight -->
              <div v-if="dragOverPageId === page.id"
                class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
                style="border:2px dashed #6366f1;background:rgba(99,102,241,0.04);z-index:50;border-radius:inherit">
                <i class="fa-solid fa-plus-circle text-indigo-500" style="font-size:28px;opacity:0.5"></i>
                <span class="text-indigo-500 font-black text-xs mt-1" style="opacity:0.6">Drop to add</span>
              </div>

              <!-- Footer -->
              <div v-if="rs.show_footer || rs.show_page_numbers"
                class="absolute bottom-0 left-0 right-0 flex items-center justify-between"
                :style="{
                  height: (36 * zoom/100) + 'px',
                  backgroundColor: rs.footer_color && rs.show_footer ? rs.footer_color : 'transparent',
                  borderTop: `1px solid ${rs.primary_color || '#6366f1'}22`,
                  padding: `0 ${(rs.margin || 40) * zoom/100}px`,
                  zIndex: 5,
                }">
                <span :style="{ fontSize:(10*zoom/100)+'px', color:'#94a3b8', fontFamily:rs.font_family }">{{ rs.footer_left||'' }}</span>
                <span v-if="rs.show_page_numbers" :style="{ fontSize:(11*zoom/100)+'px', color:'#94a3b8' }">
                  {{ pi+1 }}{{ pages.length > 1 ? ` / ${pages.length}` : '' }}
                </span>
                <span :style="{ fontSize:(10*zoom/100)+'px', color:'#94a3b8', fontFamily:rs.font_family }">{{ rs.footer_right||'' }}</span>
              </div>
            </div>
          </div>

          <!-- Page controls row below page -->
          <div class="absolute -bottom-9 left-1/2 -translate-x-1/2 flex items-center gap-1 opacity-0 group-hover/page:opacity-100 transition-all duration-200">
            <button @click.stop="$emit('add-page-after', pi)"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
              :class="dark ? 'bg-[#1c2128] border-[#30363d] text-slate-400 hover:border-emerald-500 hover:text-emerald-400' : 'bg-white border-slate-200 text-slate-500 hover:border-emerald-400 hover:text-emerald-600'">
              <i class="fa-solid fa-plus text-[9px]"></i> After
            </button>
            <button @click.stop="$emit('duplicate-page', pi)"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
              :class="dark ? 'bg-[#1c2128] border-[#30363d] text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'bg-white border-slate-200 text-slate-500 hover:border-indigo-400 hover:text-indigo-600'">
              <i class="fa-regular fa-clone text-[9px]"></i> Clone
            </button>
            <button v-if="pages.length > 1" @click.stop="$emit('remove-page', pi)"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
              :class="dark ? 'bg-[#1c2128] border-[#30363d] text-slate-400 hover:border-red-500 hover:text-red-400' : 'bg-white border-slate-200 text-slate-500 hover:border-red-400 hover:text-red-500'">
              <i class="fa-solid fa-trash text-[9px]"></i> Delete
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- BOTTOM STATUS BAR -->
    <div class="absolute bottom-3 right-4 z-20 flex items-center gap-2">
      <div v-if="selectedElement" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-wider backdrop-blur-xl transition-all"
        :class="dark ? 'bg-[#1c2128]/90 border-[#30363d] text-indigo-400' : 'bg-white/90 border-slate-200 text-indigo-600'"
        style="animation:slideLeft 0.2s ease">
        <i class="fa-solid fa-vector-square" style="font-size:9px"></i>
        X:{{ Math.round(selectedElement.position?.x||0) }} Y:{{ Math.round(selectedElement.position?.y||0) }}
        · {{ Math.round(selectedElement.styles?.width||0) }}×{{ Math.round(selectedElement.styles?.height||0) }}
      </div>
      <div class="px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-wider backdrop-blur-xl"
        :class="dark ? 'bg-[#1c2128]/90 border-[#30363d] text-slate-500' : 'bg-white/90 border-slate-200 text-slate-400'">
        <i class="fa-solid fa-layer-group mr-1" style="font-size:9px"></i>
        {{ (pages[selectedPageIndex]?.elements || []).length }} els
      </div>
      <div class="px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-wider backdrop-blur-xl"
        :class="dark ? 'bg-[#1c2128]/90 border-[#30363d] text-slate-500' : 'bg-white/90 border-slate-200 text-slate-400'">
        {{ rs.page_size }} · {{ rs.orientation }}
      </div>
    </div>

    <!-- SNAP LINES -->
    <div v-if="snapH !== null" class="absolute left-0 right-0 pointer-events-none" style="z-index:50;height:1px;background:#6366f1;opacity:0.7" :style="{ top: snapH + 'px' }"/>
    <div v-if="snapV !== null" class="absolute top-0 bottom-0 pointer-events-none" style="z-index:50;width:1px;background:#6366f1;opacity:0.7" :style="{ left: snapV + 'px' }"/>

    <!-- Overlay to close zoom menu -->
    <div v-if="showZoomMenu" class="fixed inset-0 z-40" @click="showZoomMenu=false"/>
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
  dark: { type: Boolean, default: false },
  snapToGrid: Boolean,
});

const emit = defineEmits([
  'update:zoom','select-element','deselect-all','add-element-at','delete-element',
  'duplicate-element','move-element-to-page','lock-element','add-page-after',
  'remove-page','duplicate-page','push-history','upload-image','open-context-menu',
  'start-drag','start-resize','start-rotation','fit-to-screen','text-blur',
  'list-item-blur','add-list-item','checklist-item-blur','add-checklist-item',
  'table-cell-blur','table-header-blur','add-table-row','add-table-column',
  'remove-table-row','remove-table-column','toc-title-blur','toc-item-blur',
  'add-toc-item','timeline-blur','add-timeline-item','bring-to-front','send-to-back',
  'bring-forward','send-backward','align-element','select-page',
]);

const canvasWrapper = ref(null);
const scrollArea = ref(null);
const hRulerCanvas = ref(null);
const vRulerCanvas = ref(null);
const showZoomMenu = ref(false);
const isDragOverPage = ref(false);
const dragOverPageId = ref(null);
const snapH = ref(null);
const snapV = ref(null);

const RESIZE_HANDLES = [
  { dir: 'n',  s: { top: '-4px', left: '50%', transform: 'translateX(-50%)', cursor: 'n-resize' } },
  { dir: 's',  s: { bottom: '-4px', left: '50%', transform: 'translateX(-50%)', cursor: 's-resize' } },
  { dir: 'e',  s: { right: '-4px', top: '50%', transform: 'translateY(-50%)', cursor: 'e-resize' } },
  { dir: 'w',  s: { left: '-4px', top: '50%', transform: 'translateY(-50%)', cursor: 'w-resize' } },
  { dir: 'ne', s: { top: '-4px', right: '-4px', cursor: 'ne-resize' } },
  { dir: 'nw', s: { top: '-4px', left: '-4px', cursor: 'nw-resize' } },
  { dir: 'se', s: { bottom: '-4px', right: '-4px', cursor: 'se-resize' } },
  { dir: 'sw', s: { bottom: '-4px', left: '-4px', cursor: 'sw-resize' } },
];

const getHandleStyle = (h) => ({
  ...h.s,
  position: 'absolute',
  width: '8px',
  height: '8px',
  background: '#ffffff',
  border: '2px solid #6366f1',
  borderRadius: '2px',
  zIndex: 100,
  boxShadow: '0 1px 3px rgba(0,0,0,0.3)',
  transition: 'transform 0.1s ease',
});

const getShadow = (val, zf) => {
  const m = {
    none: undefined,
    sm: `0 ${1*zf}px ${3*zf}px rgba(0,0,0,.12)`,
    md: `0 ${4*zf}px ${12*zf}px rgba(0,0,0,.1)`,
    lg: `0 ${10*zf}px ${30*zf}px rgba(0,0,0,.15)`,
    xl: `0 ${20*zf}px ${60*zf}px rgba(0,0,0,.2)`,
    '2xl': `0 ${25*zf}px ${80*zf}px rgba(0,0,0,.25)`,
    'glow-indigo': `0 0 ${20*zf}px rgba(99,102,241,.5)`,
    'glow-emerald': `0 0 ${20*zf}px rgba(16,185,129,.5)`,
    'glow-gold': `0 0 ${20*zf}px rgba(245,158,11,.5)`,
  };
  return val ? m[val] : undefined;
};

const getElementWrapperStyle = (el) => {
  const s = el.styles || {};
  const zf = props.zoom / 100;
  return {
    left: ((el.position?.x || 0) * zf) + 'px',
    top: ((el.position?.y || 0) * zf) + 'px',
    width: ((s.width || 200) * zf) + 'px',
    height: ((s.height || 50) * zf) + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: s.rotate ? `rotate(${s.rotate}deg)` : undefined,
    borderRadius: s.borderRadius ? (s.borderRadius * zf) + 'px' : undefined,
    border: (s.borderWidth > 0) ? `${s.borderWidth * zf}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : undefined,
    padding: s.padding ? (s.padding * zf) + 'px' : undefined,
    boxShadow: getShadow(s.boxShadow, zf),
    cursor: props.dragMode ? 'crosshair' : (el.locked ? 'not-allowed' : 'move'),
    overflow: 'hidden',
    outline: 'none',
    userSelect: 'none',
  };
};

// ZOOM
const increaseZoom = () => {
  const p = [25,50,75,100,125,150,175,200];
  emit('update:zoom', p.find(z => z > props.zoom) || 200);
};
const decreaseZoom = () => {
  const p = [25,50,75,100,125,150,175,200];
  emit('update:zoom', [...p].reverse().find(z => z < props.zoom) || 25);
};
const resetZoom = () => emit('update:zoom', 100);
const fitCanvas = () => {
  if (!scrollArea.value) return;
  const w = scrollArea.value.clientWidth - 120;
  const h = scrollArea.value.clientHeight - 120;
  const zW = Math.floor(w / props.canvasDimensions.width * 100);
  const zH = Math.floor(h / props.canvasDimensions.height * 100);
  const z = Math.min(150, Math.max(25, Math.min(zW, zH)));
  emit('update:zoom', z);
  emit('fit-to-screen', w);
};

// MOUSE
const onElementMousedown = (e, el, pi) => {
  if (e.button !== 0) return;
  emit('select-element', el, pi);
  if (!el.locked) emit('start-drag', e, el, pi);
};

const onElementDblClick = (e, el, pi) => {
  const textTypes = ['text','heading','subheading','quote','blockquote','highlight','callout','alert','code','badge'];
  if (!textTypes.includes(el.type)) return;
  nextTick(() => {
    const ed = e.currentTarget.querySelector('[contenteditable]');
    if (ed) { ed.focus(); document.execCommand('selectAll', false, null); }
  });
};

const onScrollAreaMousedown = (e) => {
  if (e.target === scrollArea.value || e.target.classList.contains('flex')) {
    emit('deselect-all');
  }
};

// DRAG FROM SIDEBAR
const handleDrop = (e) => {
  isDragOverPage.value = false;
  dragOverPageId.value = null;
  const type = e.dataTransfer.getData('elementType');
  if (!type) return;
  const pageEl = document.getElementById('page-' + props.pages[props.selectedPageIndex]?.id);
  if (!pageEl) { emit('add-element-at', type, props.selectedPageIndex, 100, 100); return; }
  const r = pageEl.getBoundingClientRect();
  const zf = props.zoom / 100;
  const x = Math.round((e.clientX - r.left) / zf);
  const y = Math.round((e.clientY - r.top) / zf);
  emit('add-element-at', type, props.selectedPageIndex, Math.max(10, x), Math.max(10, y));
};

const handleDropOnPage = (e, pi) => {
  dragOverPageId.value = null;
  const type = e.dataTransfer.getData('elementType');
  if (!type) return;
  const pageEl = document.getElementById('page-' + props.pages[pi]?.id);
  if (!pageEl) return;
  const r = pageEl.getBoundingClientRect();
  const zf = props.zoom / 100;
  const x = Math.round((e.clientX - r.left) / zf);
  const y = Math.round((e.clientY - r.top) / zf);
  if (pi !== props.selectedPageIndex) emit('select-page', pi);
  nextTick(() => emit('add-element-at', type, pi, Math.max(10, x), Math.max(10, y)));
};

// WHEEL ZOOM
const onWheel = (e) => {
  if (!e.ctrlKey && !e.metaKey) return;
  e.preventDefault();
  const d = e.deltaY < 0 ? 10 : -10;
  emit('update:zoom', Math.min(200, Math.max(25, props.zoom + d)));
};

// RULERS
const drawRulers = () => {
  if (!props.showRulers) return;
  nextTick(() => {
    const hC = hRulerCanvas.value;
    const vC = vRulerCanvas.value;
    if (!hC || !vC) return;
    const hP = hC.parentElement;
    const vP = vC.parentElement;
    if (!hP || !vP) return;
    hC.width = hP.clientWidth * 2;
    hC.height = 48;
    vC.width = 48;
    vC.height = vP.clientHeight * 2;
    hC.style.transform = 'scale(0.5) translate(-50%, -50%)';
    vC.style.transform = 'scale(0.5) translate(-50%, -50%)';

    const bg = props.dark ? '#161b22' : '#e2e8f0';
    const tc = props.dark ? '#475569' : '#94a3b8';
    const lc = props.dark ? '#334155' : '#cbd5e1';
    const zf = props.zoom / 100;
    const step = props.gridSize;

    const hCtx = hC.getContext('2d');
    hCtx.fillStyle = bg; hCtx.fillRect(0, 0, hC.width, hC.height);
    hCtx.strokeStyle = lc; hCtx.fillStyle = tc; hCtx.font = '18px monospace';
    for (let x = 0; x < props.canvasDimensions.width + 200; x += step) {
      const px = x * zf * 2;
      hCtx.beginPath(); hCtx.moveTo(px, 30); hCtx.lineTo(px, 48); hCtx.stroke();
      if (x % (step * 5) === 0) hCtx.fillText(x, px + 2, 22);
    }

    const vCtx = vC.getContext('2d');
    vCtx.fillStyle = bg; vCtx.fillRect(0, 0, vC.width, vC.height);
    vCtx.strokeStyle = lc; vCtx.fillStyle = tc; vCtx.font = '18px monospace';
    for (let y = 0; y < props.canvasDimensions.height + 200; y += step) {
      const py = y * zf * 2;
      vCtx.beginPath(); vCtx.moveTo(30, py); vCtx.lineTo(48, py); vCtx.stroke();
      if (y % (step * 5) === 0) {
        vCtx.save(); vCtx.translate(22, py + 2); vCtx.rotate(-Math.PI / 2);
        vCtx.fillText(y, 0, 0); vCtx.restore();
      }
    }
  });
};

watch(() => [props.zoom, props.showRulers, props.dark, props.gridSize], drawRulers);

const onKeyDown = (e) => {
  if (['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName) || e.target.contentEditable === 'true') return;
  const ctrl = e.ctrlKey || e.metaKey;
  if (ctrl && (e.key === '=' || e.key === '+')) { e.preventDefault(); increaseZoom(); }
  if (ctrl && e.key === '-') { e.preventDefault(); decreaseZoom(); }
  if (ctrl && e.key === '0') { e.preventDefault(); resetZoom(); }
};

onMounted(() => {
  document.addEventListener('keydown', onKeyDown);
  if (scrollArea.value) scrollArea.value.addEventListener('wheel', onWheel, { passive: false });
  drawRulers();
  nextTick(fitCanvas);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeyDown);
  if (scrollArea.value) scrollArea.value.removeEventListener('wheel', onWheel);
});
</script>

<style scoped>
@keyframes selBlink {
  0%,100% { box-shadow: 0 0 0 1px rgba(255,255,255,0.6), 0 0 16px rgba(99,102,241,0.35); }
  50% { box-shadow: 0 0 0 1px rgba(255,255,255,0.6), 0 0 24px rgba(99,102,241,0.55); }
}
@keyframes slideDown {
  from { opacity:0; transform:translateX(-50%) translateY(-10px); }
  to { opacity:1; transform:translateX(-50%) translateY(0); }
}
@keyframes fadeUp {
  from { opacity:0; transform:translateX(-50%) translateY(4px); }
  to { opacity:1; transform:translateX(-50%) translateY(0); }
}
@keyframes slideLeft {
  from { opacity:0; transform:translateX(8px); }
  to { opacity:1; transform:translateX(0); }
}
.dd-enter-active { transition:all 0.12s ease-out; }
.dd-leave-active { transition:all 0.08s ease-in; }
.dd-enter-from,.dd-leave-to { opacity:0; transform:translateX(-50%) translateY(-4px) scale(0.96); }
.dd-enter-to { transform:translateX(-50%) translateY(0) scale(1); }

.page-ring { position:relative; }
.page-ring-inactive { box-shadow: 0 4px 8px rgba(0,0,0,0.06), 0 12px 30px rgba(0,0,0,0.1), 0 20px 60px rgba(0,0,0,0.08); }
.page-ring-active { box-shadow: 0 0 0 2.5px #6366f1, 0 4px 16px rgba(99,102,241,0.2), 0 16px 48px rgba(0,0,0,0.12), 0 32px 80px rgba(0,0,0,0.08); }

.resize-handle:hover { transform: scale(1.4) !important; background: #6366f1 !important; }
.rotation-handle:hover { transform: translateX(-50%) scale(1.2) !important; }

.action-btn-sm {
  width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;
  border-radius: 6px; color: #64748b; transition: all 0.12s ease;
  cursor: pointer; border: none; background: transparent;
}
.action-btn-sm:hover { background: rgba(99,102,241,0.1); color: #6366f1; transform: scale(1.1); }

.element-node { transition: filter 0.1s ease; }
.element-node:hover:not([style*='cursor: not-allowed']) { filter: drop-shadow(0 0 4px rgba(99,102,241,0.3)); }
</style>