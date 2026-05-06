<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   EditorCanvas - Canvas, Pages, Elements, Drag-Drop, Resize    ║
  ║   Grid, Rulers, Alignment Guides, Rubber Band Selection        ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <main
    class="canvas-area"
    ref="canvasArea"
    @dragover.prevent="onDragOver"
    @drop.prevent="onDrop"
    @click.self="deselectAll"
    @mousedown.self="startRubberBand"
    @mousemove="onMouseMove"
    @mouseup="endRubberBand"
    @wheel.ctrl.prevent="onZoomWheel"
    @wheel.alt.prevent="onPanWheel"
    @contextmenu.prevent="onCanvasContext"
  >
    <!-- ═══ GRID OVERLAY ═══════════════════════════════════════ -->
    <div
      v-if="showGrid"
      class="grid-overlay"
      :style="gridStyle"
    ></div>

    <!-- ═══ RULERS ═══════════════════════════════════════════ -->
    <div v-if="showRulers" class="ruler ruler-h">
      <canvas ref="rulerHCanvas" class="ruler-canvas-h"></canvas>
    </div>
    <div v-if="showRulers" class="ruler ruler-v">
      <canvas ref="rulerVCanvas" class="ruler-canvas-v"></canvas>
    </div>

    <!-- ═══ ALIGNMENT GUIDES ═══════════════════════════════════ -->
    <AlignmentGuides
      v-if="alignmentGuides.show"
      :guides="alignmentGuides.lines"
      :canvas-rect="canvasRect"
    />

    <!-- ═══ RUBBER BAND ═══════════════════════════════════════ -->
    <div
      v-if="rubberBand.active"
      class="rubber-band"
      :style="rubberBandStyle"
    ></div>

    <!-- ═══ CANVAS CONTAINER ═══════════════════════════════════ -->
    <div
      class="canvas-container"
      :style="{
        transform: `scale(${zoom / 100})`,
        transformOrigin: 'top center',
        transition: isZooming ? 'transform 0.2s ease' : 'none'
      }"
    >
      <!-- ═══ PAGE SHEETS ═══════════════════════════════════════ -->
      <div
        v-for="(page, pi) in report.content"
        :key="page.id"
        class="page-sheet"
        :class="{
          'page-active': currentPage === pi,
          'page-drop-target': dropTargetPage === pi,
          'page-hover': hoveredPage === pi
        }"
        :style="getPageStyle(page, pi)"
        @click.stop="selectPage(pi)"
        @dragover.prevent="dropTargetPage = pi"
        @dragleave="dropTargetPage = null"
        @drop.stop="onPageDrop($event, pi)"
        @dblclick.self="onPageDblClick($event, pi)"
        @contextmenu.prevent.stop="onPageContext($event, pi)"
        @mouseenter="hoveredPage = pi"
        @mouseleave="hoveredPage = null"
      >
        <!-- Page Label -->
        <div class="page-label">
          <span>{{ page.label || `Page ${pi + 1}` }}</span>
        </div>

        <!-- Active Page Glow -->
        <div v-if="currentPage === pi" class="page-glow"></div>

        <!-- Watermark -->
        <div
          v-if="settings.watermark"
          class="watermark"
          :style="{
            color: settings.primary_color || '#6366f1',
            opacity: (settings.watermark_opacity || 5) / 100,
            fontSize: '72px',
            fontWeight: '900',
            transform: 'rotate(-25deg)',
            pointerEvents: 'none',
            userSelect: 'none'
          }"
        >
          {{ settings.watermark }}
        </div>

        <!-- Header -->
        <div
          v-if="settings.show_header"
          class="page-header"
          :style="{
            background: settings.header_color || '#1e293b',
            color: '#ffffff',
            position: 'absolute',
            top: 0,
            left: 0,
            right: 0,
            height: '44px',
            display: 'flex',
            alignItems: 'center',
            padding: '0 30px',
            fontSize: '12px',
            fontWeight: '600',
            zIndex: 10
          }"
        >
          {{ settings.header_text || 'Header' }}
        </div>

        <!-- Elements Container -->
        <div class="elements-container" :style="{ position: 'relative', flex: 1 }">
          <!-- ═══ ELEMENTS ═══════════════════════════════════ -->
          <div
            v-for="(el, ei) in page.elements"
            :key="el.id"
            v-show="el.visible !== false"
            class="canvas-element"
            :class="getElementClasses(el, pi, ei)"
            :style="getElementStyle(el)"
            :data-element-id="el.id"
            :data-element-index="ei"
            :data-page-index="pi"
            @mousedown.stop="onElementMouseDown($event, pi, ei)"
            @dblclick.stop="onElementDblClick($event, pi, ei)"
            @contextmenu.prevent.stop="$emit('context-menu', $event, pi, ei)"
          >
            <!-- Priority Stripe -->
            <div
              v-if="el.styles?.priority && el.styles.priority !== 'none'"
              class="priority-stripe"
              :style="{
                background: getPriorityColor(el.styles.priority),
                height: '3px',
                position: 'absolute',
                top: 0,
                left: 0,
                right: 0,
                zIndex: 20,
                animation: 'priorityGlow 2s ease-in-out infinite'
              }"
            ></div>

            <!-- Selection Handles -->
            <template v-if="isElementSelected(pi, ei) && !el.locked">
              <!-- Resize Handles -->
              <div
                v-for="handle in resizeHandles"
                :key="handle"
                class="resize-handle"
                :class="`handle-${handle}`"
                @mousedown.stop="startResize($event, pi, ei, handle)"
              ></div>
              
              <!-- Rotate Handle -->
              <div
                class="rotate-handle"
                @mousedown.stop="startRotate($event, pi, ei)"
                title="Rotate"
              >
                <i class="fa-solid fa-rotate"></i>
              </div>

              <!-- Element Info Bar -->
              <div class="el-info-bar">
                {{ Math.round(el.position?.x || 0) }}, {{ Math.round(el.position?.y || 0) }} —
                {{ Math.round(el.styles?.width || 100) }} × {{ Math.round(el.styles?.height || 50) }}
                <span v-if="el.styles?.rotate"> | {{ el.styles.rotate }}°</span>
              </div>
            </template>

            <!-- Lock Indicator -->
            <div v-if="el.locked" class="lock-indicator" title="Locked">
              <i class="fa-solid fa-lock"></i>
            </div>

            <!-- Connection Point (for linking) -->
            <div v-if="isElementSelected(pi, ei) && !el.locked" class="connection-points">
              <div v-for="cp in 4" :key="cp" class="conn-point" :class="`cp-${cp}`"></div>
            </div>

            <!-- ═══ ELEMENT CONTENT ═══════════════════════════ -->
            <div class="el-content" :style="getElementContentStyle(el)">
              
              <!-- TEXT ELEMENTS -->
              <div
                v-if="isTextElement(el.type)"
                class="text-content"
                :class="el.type"
                :contenteditable="isEditing(pi, ei) && !el.locked"
                :style="getTextStyle(el)"
                @input="onTextInput(pi, ei, $event)"
                @blur="onTextBlur(pi, ei)"
                @paste="onTextPaste($event)"
                v-html="el.content || getPlaceholder(el.type)"
              ></div>

              <!-- IMAGE -->
              <div v-else-if="el.type === 'image'" class="image-content">
                <img
                  v-if="el.src"
                  :src="el.src"
                  :alt="el.alt || 'Image'"
                  :style="{
                    width: '100%',
                    height: '100%',
                    objectFit: el.styles?.objectFit || 'cover',
                    borderRadius: (el.styles?.borderRadius || 0) + 'px',
                    filter: getImageFilters(el)
                  }"
                  @error="onImageError($event)"
                  loading="lazy"
                />
                <div v-else class="image-placeholder" @click="$emit('image-upload', pi, ei)">
                  <i class="fa-solid fa-image"></i>
                  <span>Click to add image</span>
                  <small>or drop image here</small>
                </div>
                <!-- Image overlay on hover -->
                <div v-if="el.src && isElementSelected(pi, ei)" class="image-overlay">
                  <button @click.stop="$emit('image-replace', { pi, ei })" title="Replace">
                    <i class="fa-solid fa-rotate"></i>
                  </button>
                  <button @click.stop="el.src = ''; markDirty()" title="Remove">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </div>

              <!-- TABLE -->
              <div v-else-if="el.type === 'table'" class="table-content">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th
                        v-for="(col, ci) in el.columns"
                        :key="ci"
                        :style="{ background: settings.primary_color || '#6366f1', color: '#fff', padding: '8px 10px', fontSize: '12px', fontWeight: '600' }"
                        :contenteditable="isEditing(pi, ei)"
                        @blur="el.columns[ci] = $event.target.textContent; markDirty()"
                      >
                        {{ col }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, ri) in el.data" :key="ri">
                      <td
                        v-for="(col, ci) in el.columns"
                        :key="ci"
                        :contenteditable="isEditing(pi, ei)"
                        @blur="el.data[ri][col] = $event.target.textContent; markDirty()"
                        :style="{ padding: '6px 10px', borderBottom: '1px solid var(--border, #e2e8f0)', fontSize: '11px' }"
                      >
                        {{ row[col] || '' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- METRIC / KPI -->
              <div v-else-if="el.type === 'metric'" class="metric-content" :style="metricStyle(el)">
                <div class="metric-label">{{ el.label || 'Metric' }}</div>
                <div class="metric-value" :style="{ color: el.styles?.valueColor || settings.primary_color }">
                  {{ el.value || '0' }}
                </div>
                <div v-if="el.change" class="metric-change" :class="el.changeType || 'positive'">
                  <i :class="el.changeType === 'negative' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'"></i>
                  {{ el.change }}
                </div>
              </div>

              <!-- PROGRESS BAR -->
              <div v-else-if="el.type === 'progress'" class="progress-content">
                <div class="progress-header">
                  <span>{{ el.label || 'Progress' }}</span>
                  <span>{{ el.value || 0 }}%</span>
                </div>
                <div class="progress-track">
                  <div
                    class="progress-fill"
                    :style="{
                      width: (el.value || 0) + '%',
                      background: `linear-gradient(90deg, ${settings.primary_color || '#6366f1'}, ${settings.accent_color || '#8b5cf6'})`,
                      borderRadius: '99px',
                      transition: 'width 0.5s ease',
                      height: '100%'
                    }"
                  ></div>
                </div>
              </div>

              <!-- CHART PLACEHOLDER -->
              <div v-else-if="isChartType(el.type)" class="chart-placeholder">
                <div class="chart-preview">
                  <div v-if="el.type === 'bar-chart'" class="chart-bars">
                    <div
                      v-for="(val, vi) in (el.chartData?.values || [25,40,35,55,50,70])"
                      :key="vi"
                      class="chart-bar"
                      :style="{
                        height: (val / maxChartVal(el)) * 100 + '%',
                        background: getChartGradient(vi)
                      }"
                    ></div>
                  </div>
                  <div v-else-if="el.type === 'pie-chart'" class="chart-pie">
                    <svg viewBox="0 0 100 100">
                      <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="20" />
                      <circle
                        cx="50" cy="50" r="40"
                        fill="none"
                        :stroke="settings.primary_color || '#6366f1'"
                        stroke-width="20"
                        :stroke-dasharray="`${(el.chartData?.values?.[0] || 35) / 100 * 251} 251`"
                        stroke-dashoffset="0"
                        transform="rotate(-90 50 50)"
                      />
                    </svg>
                  </div>
                  <div v-else class="chart-line-preview">
                    <svg viewBox="0 0 200 80" preserveAspectRatio="none">
                      <polyline
                        :points="getLinePoints(el)"
                        fill="none"
                        :stroke="settings.primary_color || '#6366f1'"
                        stroke-width="2"
                      />
                    </svg>
                  </div>
                </div>
                <div class="chart-title">{{ el.chartTitle || 'Chart' }}</div>
              </div>

              <!-- SHAPES -->
              <div v-else-if="el.type === 'rectangle'" class="shape-rect" :style="shapeStyle(el)"></div>
              <div v-else-if="el.type === 'circle'" class="shape-circle" :style="shapeStyle(el)"></div>
              <div v-else-if="el.type === 'triangle'" class="shape-triangle" :style="triangleStyle(el)"></div>
              <div v-else-if="el.type === 'divider'" class="shape-divider" :style="dividerStyle(el)"></div>
              <div v-else-if="el.type === 'arrow'" class="shape-arrow">
                <svg width="100%" height="100%" viewBox="0 0 200 40" preserveAspectRatio="none">
                  <line x1="5" y1="20" x2="185" y2="20" :stroke="el.styles?.color || settings.primary_color" :stroke-width="el.styles?.strokeWidth || 2" />
                  <polygon points="175,8 195,20 175,32" :fill="el.styles?.color || settings.primary_color" />
                </svg>
              </div>

              <!-- CALLOUT -->
              <div v-else-if="el.type === 'callout'" class="callout-content" :style="calloutStyle(el)">
                <span class="callout-emoji">{{ el.emoji || '💡' }}</span>
                <div
                  :contenteditable="isEditing(pi, ei)"
                  @input="el.content = $event.target.innerHTML; markDirty()"
                  v-html="el.content || 'Callout message...'"
                  class="callout-text"
                ></div>
              </div>

              <!-- TIMELINE -->
              <div v-else-if="el.type === 'timeline'" class="timeline-content">
                <div v-for="(item, ti) in (el.items || [])" :key="ti" class="tl-item">
                  <div class="tl-dot" :style="{ background: settings.primary_color }"></div>
                  <div v-if="ti < (el.items || []).length - 1" class="tl-line"></div>
                  <div class="tl-info">
                    <span class="tl-date">{{ item.date }}</span>
                    <strong class="tl-title">{{ item.label }}</strong>
                    <span class="tl-desc">{{ item.desc }}</span>
                  </div>
                </div>
              </div>

              <!-- CHECKLIST -->
              <div v-else-if="el.type === 'checklist'" class="checklist-content">
                <div v-for="(item, ci) in (el.items || [])" :key="ci" class="check-item">
                  <div
                    class="check-box"
                    :class="{ checked: item.checked }"
                    @click="item.checked = !item.checked; markDirty()"
                    :style="{
                      borderColor: settings.primary_color,
                      background: item.checked ? settings.primary_color : 'transparent'
                    }"
                  >
                    <i v-if="item.checked" class="fa-solid fa-check"></i>
                  </div>
                  <span :class="{ 'checked-text': item.checked }">{{ item.text }}</span>
                </div>
              </div>

              <!-- TESTIMONIAL -->
              <div v-else-if="el.type === 'testimonial'" class="testimonial-content">
                <div class="quote-mark">"</div>
                <p class="testimonial-text">{{ el.content || 'Amazing product!' }}</p>
                <div class="testimonial-author">{{ el.author || 'Jane Doe' }}</div>
                <div class="testimonial-role">{{ el.role || 'CEO' }}</div>
              </div>

              <!-- SIGNATURE -->
              <div v-else-if="el.type === 'signature'" class="signature-content">
                <div class="sig-line"></div>
                <div class="sig-name">{{ el.content || 'Signature' }}</div>
                <div class="sig-title">{{ el.label || 'Authorized Signature' }}</div>
              </div>

              <!-- STAT ROW -->
              <div v-else-if="el.type === 'stat-row'" class="stat-row-content">
                <div v-for="(stat, si) in (el.stats || [])" :key="si" class="stat-item">
                  <div class="stat-value" :style="{ color: settings.primary_color }">{{ stat.value }}</div>
                  <div class="stat-label">{{ stat.label }}</div>
                </div>
              </div>

              <!-- ICON -->
              <div v-else-if="el.type === 'icon'" class="icon-content" :style="{ color: el.styles?.color || settings.primary_color, fontSize: (el.styles?.fontSize || 40) + 'px' }">
                {{ el.content || '⭐' }}
              </div>

              <!-- RATING -->
              <div v-else-if="el.type === 'rating'" class="rating-content">
                <span v-for="i in 5" :key="i" class="rating-star" :style="{ color: i <= (el.value || 4) ? (el.styles?.color || '#f59e0b') : '#cbd5e1', fontSize: (el.styles?.fontSize || 20) + 'px' }">★</span>
              </div>

              <!-- QR CODE -->
              <div v-else-if="el.type === 'qr-code'" class="qr-content">
                <img
                  v-if="el.qrUrl"
                  :src="el.qrUrl"
                  :style="{ width: '100%', height: '100%', objectFit: 'contain' }"
                />
                <div v-else class="qr-placeholder" @click="generateQr(el)">
                  <i class="fa-solid fa-qrcode"></i>
                  <span>Click to generate QR</span>
                </div>
              </div>

              <!-- PAGE NUMBER -->
              <div v-else-if="el.type === 'pagenum'" class="pagenum-content">
                {{ pi + 1 }}
              </div>

              <!-- DATE -->
              <div v-else-if="el.type === 'date-el'" class="date-content">
                {{ formattedDate }}
              </div>

              <!-- FALLBACK -->
              <div v-else class="fallback-content">
                <i class="fa-solid fa-cube"></i>
                <span>{{ el.type }}</span>
              </div>
            </div>
          </div>

          <!-- Drop Hint -->
          <div v-if="isDraggingEl && !page.elements.length" class="drop-hint">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Drop element here</span>
          </div>
        </div>

        <!-- Footer -->
        <div
          v-if="settings.show_footer"
          class="page-footer"
          :style="{
            position: 'absolute',
            bottom: 0,
            left: 0,
            right: 0,
            height: '35px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            padding: '0 30px',
            fontSize: '10px',
            color: '#94a3b8',
            borderTop: '1px solid #e2e8f0',
            zIndex: 10
          }"
        >
          <span>{{ settings.footer_left || '' }}</span>
          <span v-if="settings.show_page_numbers">Page {{ pi + 1 }} of {{ report.content.length }}</span>
          <span>{{ settings.footer_right || '' }}</span>
        </div>
      </div>

      <!-- Add Page Button -->
      <button class="add-page-btn" @click="$emit('add-page')">
        <i class="fa-solid fa-plus"></i>
        <span>Add New Page</span>
        <span class="add-page-hint">or press Ctrl+N</span>
      </button>
    </div>

    <!-- ═══ PAGE NAVIGATION ARROWS ═══════════════════════════════ -->
    <div class="page-navigation" v-if="report.content.length > 1">
      <button
        class="nav-arrow nav-prev"
        :disabled="currentPage === 0"
        @click="$emit('go-to-page', currentPage - 1)"
        title="Previous Page (PgUp)"
      >
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <div class="page-indicator">
        <span v-for="(pg, pi) in report.content" :key="pi"
          class="page-dot"
          :class="{ active: pi === currentPage, 'has-content': pg.elements.length > 0 }"
          @click="$emit('go-to-page', pi)"
          :title="`Page ${pi + 1}`"
        ></span>
      </div>
      <button
        class="nav-arrow nav-next"
        :disabled="currentPage >= report.content.length - 1"
        @click="$emit('go-to-page', currentPage + 1)"
        title="Next Page (PgDn)"
      >
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>

    <!-- ═══ ZOOM INDICATOR ═══════════════════════════════════ -->
    <div v-if="zoom !== 100" class="zoom-indicator" @click="$emit('zoom-reset')">
      {{ zoom }}% <small>(click to reset)</small>
    </div>

    <!-- ═══ MINI MAP ═══════════════════════════════════════ -->
    <div class="minimap" v-if="showMinimap" ref="minimapEl">
      <div
        v-for="(page, pi) in report.content"
        :key="'mm-' + page.id"
        class="minimap-page"
        :class="{ active: pi === currentPage }"
        :style="getMinimapPageStyle(page)"
        @click="$emit('go-to-page', pi)"
      >
        <div
          v-for="(el, ei) in page.elements"
          :key="'mme-' + el.id"
          class="minimap-el"
          :style="getMinimapElStyle(el)"
          :class="{ selected: pi === currentPage && (selectedElIdx === ei || selectedEls.includes(ei)) }"
        ></div>
      </div>
    </div>

    <!-- ═══ MINIMAP TOGGLE ═══════════════════════════════════ -->
    <button class="minimap-toggle" @click="showMinimap = !showMinimap" :title="showMinimap ? 'Hide Minimap' : 'Show Minimap'">
      <i class="fa-solid fa-map"></i>
    </button>
  </main>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

// ═══════════════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════════════
const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
  currentPage: { type: Number, default: 0 },
  selectedElIdx: { type: [Number, null], default: null },
  selectedEls: { type: Array, default: () => [] },
  editingElIdx: { type: [Number, null], default: null },
  zoom: { type: Number, default: 100 },
  showGrid: { type: Boolean, default: true },
  snapToGrid: { type: Boolean, default: true },
  showRulers: { type: Boolean, default: false },
  isDraggingEl: { type: Boolean, default: false },
  rubberBand: { type: Object, default: () => ({ active: false, x: 0, y: 0, w: 0, h: 0 }) },
  dropTargetPage: { type: [Number, null], default: null },
  gridSize: { type: Number, default: 20 },
})

// ═══════════════════════════════════════════════════════════════════
// EMITS
// ═══════════════════════════════════════════════════════════════════
const emit = defineEmits([
  'select-element', 'deselect-all', 'add-element', 'add-page',
  'update-element-position', 'start-editing', 'update-text-content',
  'duplicate-element', 'delete-element', 'element-mouse-down',
  'resize-start', 'rotate-start', 'canvas-drop', 'canvas-drag-start',
  'canvas-drag-end', 'rubber-band-start', 'rubber-band-move', 'rubber-band-end',
  'zoom-wheel', 'page-dblclick', 'context-menu', 'image-upload',
  'image-replace', 'go-to-page', 'select-page',
  'mark-dirty', 'zoom-reset',
])

// ═══════════════════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════════════════
const canvasArea = ref(null)
const rulerHCanvas = ref(null)
const rulerVCanvas = ref(null)
const minimapEl = ref(null)
const showMinimap = ref(false)
const hoveredPage = ref(null)
const isZooming = ref(false)
const canvasRect = ref(null)

// Alignment Guides
const alignmentGuides = reactive({
  show: false,
  lines: [],
})

// Drag state
let dragState = null
let resizeState = null
let rotateState = null

// ═══════════════════════════════════════════════════════════════════
// CONSTANTS
// ═══════════════════════════════════════════════════════════════════
const resizeHandles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w']
const formattedDate = computed(() => new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }))

// ═══════════════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════════════
const gridStyle = computed(() => ({
  backgroundImage: `
    linear-gradient(rgba(99,102,241,0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99,102,241,0.08) 1px, transparent 1px)
  `,
  backgroundSize: `${props.gridSize}px ${props.gridSize}px`,
  position: 'fixed',
  inset: 0,
  pointerEvents: 'none',
  zIndex: 0,
}))

const rubberBandStyle = computed(() => ({
  position: 'fixed',
  left: props.rubberBand.x + 'px',
  top: props.rubberBand.y + 'px',
  width: props.rubberBand.w + 'px',
  height: props.rubberBand.h + 'px',
  border: '2px dashed #6366f1',
  backgroundColor: 'rgba(99,102,241,0.06)',
  pointerEvents: 'none',
  zIndex: 1000,
}))

// ═══════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════
function getPageDims() {
  const sizes = {
    A4: { portrait: { w: 794, h: 1123 }, landscape: { w: 1123, h: 794 } },
    Letter: { portrait: { w: 816, h: 1056 }, landscape: { w: 1056, h: 816 } },
    Legal: { portrait: { w: 816, h: 1344 }, landscape: { w: 1344, h: 816 } },
    A3: { portrait: { w: 1123, h: 1587 }, landscape: { w: 1587, h: 1123 } },
    A5: { portrait: { w: 559, h: 794 }, landscape: { w: 794, h: 559 } },
    custom: { portrait: { w: props.settings.custom_w || 794, h: props.settings.custom_h || 1123 }, landscape: { w: props.settings.custom_h || 1123, h: props.settings.custom_w || 794 } }
  }
  return sizes[props.settings.page_size]?.[props.settings.orientation] || sizes.A4.portrait
}

function getPageStyle(page, pi) {
  const dims = getPageDims()
  const isActive = props.currentPage === pi
  return {
    width: dims.w + 'px',
    minHeight: dims.h + 'px',
    backgroundColor: props.settings.background_color || '#ffffff',
    backgroundImage: props.settings.bg_image ? `url(${props.settings.bg_image})` : 'none',
    backgroundSize: 'cover',
    fontFamily: props.settings.font_family || 'Inter',
    fontSize: (props.settings.font_size || 14) + 'px',
    borderRadius: (props.settings.page_radius || 0) + 'px',
    padding: (props.settings.margin || 40) + 'px',
    direction: props.settings.rtl ? 'rtl' : 'ltr',
    color: props.settings.text_color || '#0f172a',
    position: 'relative',
    boxShadow: isActive
      ? '0 0 0 3px rgba(251,191,36,0.6), 0 0 40px rgba(251,191,36,0.2), 0 8px 40px rgba(0,0,0,0.15)'
      : '0 8px 40px rgba(0,0,0,0.15), 0 2px 8px rgba(0,0,0,0.08)',
    transition: 'box-shadow 0.3s ease, border-color 0.3s ease',
    margin: '0 auto',
    flexShrink: 0,
  }
}

function getElementStyle(el) {
  const s = el.styles || {}
  const border = s.borderWidth ? `${s.borderWidth}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : 'none'
  const filters = []
  if (s.blur) filters.push(`blur(${s.blur}px)`)
  if (s.brightness && s.brightness !== 100) filters.push(`brightness(${s.brightness}%)`)
  if (s.contrast && s.contrast !== 100) filters.push(`contrast(${s.contrast}%)`)
  if (s.grayscale) filters.push(`grayscale(${s.grayscale}%)`)

  return {
    position: 'absolute',
    left: (el.position?.x || 0) + 'px',
    top: (el.position?.y || 0) + 'px',
    width: (s.width || 200) + 'px',
    height: (s.height || 100) + 'px',
    zIndex: s.zIndex || 1,
    opacity: el.visible === false ? 0 : ((s.opacity ?? 100) / 100),
    transform: [
      s.rotate ? `rotate(${s.rotate}deg)` : '',
      s.scaleX === -1 ? 'scaleX(-1)' : '',
      s.scaleY === -1 ? 'scaleY(-1)' : '',
    ].filter(Boolean).join(' ') || 'none',
    borderRadius: (s.borderRadius || 0) + 'px',
    border,
    boxShadow: s.boxShadow || 'none',
    filter: filters.length ? filters.join(' ') : 'none',
    mixBlendMode: s.mixBlendMode || 'normal',
    cursor: el.locked ? 'not-allowed' : 'move',
    userSelect: 'none',
    overflow: 'hidden',
    transition: el.locked ? 'none' : 'box-shadow 0.15s',
  }
}

function getElementContentStyle(el) {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    backgroundColor: s.backgroundColor || 'transparent',
    padding: s.padding ? `${s.padding}px` : `${s.paddingTop || 4}px ${s.paddingRight || 4}px ${s.paddingBottom || 4}px ${s.paddingLeft || 4}px`,
    overflow: s.overflow || 'auto',
    fontFamily: s.fontFamily || props.settings.font_family || 'Inter',
  }
}

function getTextStyle(el) {
  const s = el.styles || {}
  return {
    fontFamily: el.type === 'code' ? "'Fira Code', monospace" : (s.fontFamily || props.settings.font_family || 'Inter'),
    fontSize: (s.fontSize || 14) + 'px',
    fontWeight: s.fontWeight || '400',
    fontStyle: s.fontStyle || 'normal',
    textDecoration: s.textDecoration || 'none',
    color: s.color || props.settings.text_color || '#0f172a',
    textAlign: s.textAlign || 'left',
    lineHeight: s.lineHeight || 1.5,
    letterSpacing: (s.letterSpacing || 0) + 'px',
    textTransform: s.textTransform || 'none',
    outline: 'none',
    wordBreak: 'break-word',
    whiteSpace: el.type === 'code' ? 'pre-wrap' : 'normal',
    width: '100%',
    height: '100%',
  }
}

function getElementClasses(el, pi, ei) {
  return {
    'el-selected': isElementSelected(pi, ei),
    'el-multi-selected': props.selectedEls.includes(ei) && props.selectedElIdx !== ei,
    'el-locked': el.locked,
    'el-hidden': el.visible === false,
    'el-editing': isEditing(pi, ei),
    'el-hover-animation': el.styles?.hoverEffect && el.styles.hoverEffect !== 'none',
    [`el-hover-${el.styles?.hoverEffect}`]: el.styles?.hoverEffect && el.styles.hoverEffect !== 'none',
  }
}

function isElementSelected(pi, ei) {
  return props.currentPage === pi && (props.selectedElIdx === ei || props.selectedEls.includes(ei))
}

function isEditing(pi, ei) {
  return props.currentPage === pi && props.editingElIdx === ei
}

function isTextElement(type) {
  return ['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight', 'badge', 'code', 'link', 'callout'].includes(type)
}

function isChartType(type) {
  return type?.endsWith('-chart')
}

function getPlaceholder(type) {
  const map = { heading: 'Click to edit heading', subheading: 'Subheading', text: 'Start typing...', quote: 'Inspiring quote...', code: '// code here', badge: 'Badge', link: 'https://...', callout: 'Callout message...' }
  return map[type] || 'Click to edit'
}

function getPriorityColor(p) {
  return { low: '#3b82f6', medium: '#f59e0b', high: '#f97316', urgent: '#ef4444' }[p] || '#6366f1'
}

function getImageFilters(el) {
  const s = el.styles || {}
  const filters = []
  if (s.blur) filters.push(`blur(${s.blur}px)`)
  if (s.brightness && s.brightness !== 100) filters.push(`brightness(${s.brightness}%)`)
  if (s.contrast && s.contrast !== 100) filters.push(`contrast(${s.contrast}%)`)
  if (s.grayscale) filters.push(`grayscale(${s.grayscale}%)`)
  return filters.join(' ') || 'none'
}

function maxChartVal(el) {
  return Math.max(...(el.chartData?.values || [1]), 1)
}

function getChartGradient(i) {
  const colors = [props.settings.primary_color, props.settings.accent_color, '#8b5cf6', '#6366f1']
  return colors[i % colors.length]
}

function getLinePoints(el) {
  const values = el.chartData?.values || [25, 40, 35, 55, 50, 70]
  const max = maxChartVal(el)
  return values.map((v, i) => `${(i / (values.length - 1)) * 200},${80 - (v / max) * 70}`).join(' ')
}

function shapeStyle(el) {
  const s = el.styles || {}
  return {
    width: '100%', height: '100%',
    backgroundColor: s.backgroundColor || props.settings.primary_color || '#6366f1',
    borderRadius: el.type === 'circle' ? '50%' : (s.borderRadius || 0) + 'px',
  }
}

function triangleStyle(el) {
  const s = el.styles || {}
  return {
    width: 0, height: 0,
    borderLeft: `${((s.width || 100) / 2)}px solid transparent`,
    borderRight: `${((s.width || 100) / 2)}px solid transparent`,
    borderBottom: `${(s.height || 100)}px solid ${s.backgroundColor || props.settings.primary_color || '#6366f1'}`,
    backgroundColor: 'transparent',
  }
}

function dividerStyle(el) {
  const s = el.styles || {}
  return {
    width: '100%',
    height: (s.borderWidth || 2) + 'px',
    backgroundColor: s.color || props.settings.primary_color || '#e2e8f0',
  }
}

function calloutStyle(el) {
  const s = el.styles || {}
  return {
    display: 'flex', gap: '10px', alignItems: 'flex-start',
    padding: '12px', height: '100%',
    backgroundColor: s.backgroundColor || (props.settings.primary_color || '#6366f1') + '12',
    borderLeft: `4px solid ${s.borderColor || props.settings.primary_color || '#6366f1'}`,
    borderRadius: (s.borderRadius || 8) + 'px',
  }
}

function metricStyle(el) {
  const s = el.styles || {}
  return {
    padding: '14px', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'center',
    backgroundColor: s.backgroundColor || '#f8fafc',
    borderRadius: (s.borderRadius || 12) + 'px',
    border: s.borderWidth ? `${s.borderWidth}px solid ${s.borderColor || '#e2e8f0'}` : '1px solid #e2e8f0',
  }
}

function getMinimapPageStyle(page) {
  const dims = getPageDims()
  const scale = 0.04
  return {
    width: dims.w * scale + 'px',
    height: dims.h * scale + 'px',
    backgroundColor: props.settings.background_color || '#fff',
    position: 'relative',
    borderRadius: '2px',
    overflow: 'hidden',
    border: '1px solid #e2e8f0',
  }
}

function getMinimapElStyle(el) {
  const dims = getPageDims()
  const scale = 0.04
  return {
    position: 'absolute',
    left: (el.position?.x || 0) * scale + 'px',
    top: (el.position?.y || 0) * scale + 'px',
    width: ((el.styles?.width || 100) * scale) + 'px',
    height: ((el.styles?.height || 50) * scale) + 'px',
    backgroundColor: el.styles?.backgroundColor || props.settings.primary_color || '#6366f1',
    opacity: 0.8,
    borderRadius: '1px',
  }
}

function markDirty() {
  emit('mark-dirty')
}

// ═══════════════════════════════════════════════════════════════════
// EVENT HANDLERS
// ═══════════════════════════════════════════════════════════════════
function selectPage(pi) {
  emit('select-page', pi)
}

function deselectAll() {
  emit('deselect-all')
}

function onDragOver(e) {
  e.dataTransfer.dropEffect = 'copy'
}

function onDrop(e) {
  const defStr = e.dataTransfer.getData('el-def')
  if (defStr) {
    const def = JSON.parse(defStr)
    const rect = canvasArea.value?.getBoundingClientRect()
    if (rect) {
      const scale = props.zoom / 100
      const x = (e.clientX - rect.left) / scale - (def.w || 100) / 2
      const y = (e.clientY - rect.top + canvasArea.value.scrollTop) / scale - (def.h || 50) / 2
      emit('canvas-drop', { def, x: Math.max(0, x), y: Math.max(0, y) })
    }
  }
  emit('canvas-drag-end')
}

function onPageDrop(e, pi) {
  e.stopPropagation()
  const defStr = e.dataTransfer.getData('el-def')
  if (!defStr) return
  const def = JSON.parse(defStr)
  emit('add-element', { def, pageIndex: pi, x: 100, y: 100 })
}

function onElementMouseDown(e, pi, ei) {
  emit('select-element', ei) 
  emit('element-mouse-down', { event: e, pageIndex: pi, elementIndex: ei })
}

function onElementDblClick(e, pi, ei) {
  emit('start-editing', { pageIndex: pi, elementIndex: ei })
  emit('image-upload', pi, ei)
}

function startResize(e, pi, ei, handle) {
  e.stopPropagation()
  e.preventDefault()
  emit('resize-start', { event: e, pageIndex: pi, elementIndex: ei, handle })
}

function startRotate(e, pi, ei) {
  e.stopPropagation()
  e.preventDefault()
  emit('rotate-start', { event: e, pageIndex: pi, elementIndex: ei })
}

function onTextInput(pi, ei, event) {
  emit('update-text-content', { pageIndex: pi, elementIndex: ei, content: event.target.innerHTML })
}

function onTextBlur(pi, ei) {
  // Handled by parent
}

function onTextPaste(e) {
  e.preventDefault()
  const text = e.clipboardData.getData('text/plain')
  document.execCommand('insertText', false, text)
}

function onImageError(e) {
  e.target.style.display = 'none'
  if (e.target.parentElement) {
    const placeholder = document.createElement('div')
    placeholder.className = 'image-placeholder'
    placeholder.innerHTML = '<i class="fa-solid fa-image"></i><span>Image failed to load</span>'
    e.target.parentElement.appendChild(placeholder)
  }
}

function onPageDblClick(e, pi) {
  if (e.target.closest('.canvas-element')) return
  emit('page-dblclick', { event: e, pageIndex: pi })
}

function onPageContext(e, pi) {
  emit('context-menu', e, pi, null)
}

function onCanvasContext(e) {
  emit('context-menu', e, null, null)
}

function startRubberBand(e) {
  if (e.target.closest('.canvas-element')) return
  if (e.target.closest('.page-navigation')) return
  if (e.target.closest('.add-page-btn')) return
  emit('rubber-band-start', e)
}

function onMouseMove(e) {
  emit('rubber-band-move', e)
}

function endRubberBand() {
  emit('rubber-band-end')
}

function onZoomWheel(e) {
  emit('zoom-wheel', e)
}

function onPanWheel(e) {
  if (canvasArea.value) {
    canvasArea.value.scrollLeft += e.deltaX
    canvasArea.value.scrollTop += e.deltaY
  }
}

async function generateQr(el) {
  const text = el.qrText || 'https://example.com'
  const size = el.qrSize || 150
  el.qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}`
  markDirty()
}

// ═══════════════════════════════════════════════════════════════════
// WATCHERS
// ═══════════════════════════════════════════════════════════════════
watch(() => props.zoom, (newVal, oldVal) => {
  isZooming.value = true
  setTimeout(() => { isZooming.value = false }, 200)
})

// ═══════════════════════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════════════════════
onMounted(() => {
  if (canvasArea.value) {
    canvasRect.value = canvasArea.value.getBoundingClientRect()
  }
  // Update canvas rect on resize
  const observer = new ResizeObserver(() => {
    if (canvasArea.value) {
      canvasRect.value = canvasArea.value.getBoundingClientRect()
    }
  })
  if (canvasArea.value) observer.observe(canvasArea.value)
  
  // Draw rulers if needed
  if (props.showRulers) drawRulers()
})

onBeforeUnmount(() => {
  // Cleanup
})

function drawRulers() {
  // Ruler drawing logic (simplified)
  const hCanvas = rulerHCanvas.value
  const vCanvas = rulerVCanvas.value
  if (!hCanvas || !vCanvas) return
  
  const hCtx = hCanvas.getContext('2d')
  hCtx.canvas.width = 1200
  hCtx.canvas.height = 20
  hCtx.fillStyle = '#f8fafc'
  hCtx.fillRect(0, 0, 1200, 20)
  
  // Draw tick marks
  for (let i = 0; i < 1200; i += 50) {
    hCtx.beginPath()
    hCtx.moveTo(i, 12)
    hCtx.lineTo(i, 20)
    hCtx.strokeStyle = '#94a3b8'
    hCtx.stroke()
  }
}

// Expose
defineExpose({ canvasArea, drawRulers })
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════════════════
   CANVAS STYLES
   ══════════════════════════════════════════════════════════════════ */

.canvas-area {
  flex: 1;
  background: var(--bg-tertiary, #f1f5f9);
  overflow: auto;
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 40px 100px;
  scrollbar-width: thin;
}

.canvas-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 40px;
  min-height: 100%;
  padding-bottom: 60px;
}

/* ── Page Sheet ──────────────────────────────────────────────── */
.page-sheet {
  position: relative;
  flex-shrink: 0;
  overflow: visible;
  transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.page-sheet.page-active {
  z-index: 10;
}

.page-sheet.page-drop-target {
  border: 2px dashed var(--accent, #6366f1) !important;
  background: rgba(99,102,241,0.03) !important;
}

/* ── Page Label ──────────────────────────────────────────────── */
.page-label {
  position: absolute;
  top: -30px;
  left: 0;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted, #94a3b8);
  user-select: none;
  pointer-events: none;
}

/* ── Page Glow ───────────────────────────────────────────────── */
.page-glow {
  position: absolute;
  inset: -3px;
  border-radius: 6px;
  border: 2px solid rgba(251,191,36,0.5);
  pointer-events: none;
  animation: pageGlow 3s ease-in-out infinite;
  z-index: 1;
}

@keyframes pageGlow {
  0%, 100% { box-shadow: 0 0 15px rgba(251,191,36,0.3), inset 0 0 15px rgba(251,191,36,0.05); border-color: rgba(251,191,36,0.4); }
  50% { box-shadow: 0 0 35px rgba(251,191,36,0.5), inset 0 0 35px rgba(251,191,36,0.08); border-color: rgba(251,191,36,0.7); }
}

/* ── Canvas Element ──────────────────────────────────────────── */
.canvas-element {
  transform-origin: center;
  transition: box-shadow 0.1s;
}

.canvas-element:not(.el-locked):hover {
  outline: 1px solid rgba(99,102,241,0.4);
  outline-offset: 1px;
}

.canvas-element.el-selected {
  outline: 2px solid var(--accent, #6366f1) !important;
  outline-offset: 1px;
  box-shadow: 0 0 0 6px rgba(99,102,241,0.1);
  z-index: 50 !important;
}

.canvas-element.el-multi-selected {
  outline: 2px solid rgba(99,102,241,0.5) !important;
  outline-offset: 1px;
}

.canvas-element.el-editing {
  outline: 2px solid var(--accent, #6366f1) !important;
  cursor: text !important;
}

.canvas-element.el-locked {
  outline-color: var(--warning, #f59e0b) !important;
}

/* ── Hover Animations ────────────────────────────────────────── */
.el-hover-lift:hover { transform: translateY(-2px); }
.el-hover-pulse:hover { animation: pulse 1s ease-in-out infinite; }
.el-hover-shake:hover { animation: shake 0.5s ease-in-out; }
.el-hover-bounce:hover { animation: bounce 0.6s ease-in-out; }

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.02); }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-3px); }
  75% { transform: translateX(3px); }
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

@keyframes priorityGlow {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 1; box-shadow: 0 2px 12px currentColor; }
}

/* ── Resize Handles ──────────────────────────────────────────── */
.resize-handle {
  position: absolute;
  width: 10px;
  height: 10px;
  background: #ffffff;
  border: 2px solid var(--accent, #6366f1);
  border-radius: 2px;
  z-index: 100;
  box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}

.handle-nw { top: -5px; left: -5px; cursor: nw-resize; }
.handle-n { top: -5px; left: calc(50% - 5px); cursor: n-resize; }
.handle-ne { top: -5px; right: -5px; cursor: ne-resize; }
.handle-e { top: calc(50% - 5px); right: -5px; cursor: e-resize; }
.handle-se { bottom: -5px; right: -5px; cursor: se-resize; }
.handle-s { bottom: -5px; left: calc(50% - 5px); cursor: s-resize; }
.handle-sw { bottom: -5px; left: -5px; cursor: sw-resize; }
.handle-w { top: calc(50% - 5px); left: -5px; cursor: w-resize; }

/* ── Rotate Handle ───────────────────────────────────────────── */
.rotate-handle {
  position: absolute;
  top: -32px;
  left: calc(50% - 14px);
  width: 28px;
  height: 28px;
  background: var(--accent, #6366f1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: crosshair;
  z-index: 100;
  color: #fff;
  font-size: 12px;
  box-shadow: 0 2px 12px rgba(99,102,241,0.4);
}

.rotate-handle:hover {
  transform: scale(1.1);
}

/* ── Element Info Bar ────────────────────────────────────────── */
.el-info-bar {
  position: absolute;
  bottom: -20px;
  left: 0;
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
  white-space: nowrap;
  pointer-events: none;
  background: var(--bg-panel, #ffffff);
  padding: 2px 6px;
  border-radius: 3px;
  border: 1px solid var(--border, #e2e8f0);
}

/* ── Lock Indicator ──────────────────────────────────────────── */
.lock-indicator {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(245,158,11,0.9);
  color: #fff;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  z-index: 30;
}

/* ── Connection Points ───────────────────────────────────────── */
.connection-points { position: absolute; inset: 0; pointer-events: none; z-index: 90; }
.conn-point {
  position: absolute;
  width: 8px;
  height: 8px;
  background: var(--accent, #6366f1);
  border-radius: 50%;
  border: 2px solid #fff;
}
.cp-1 { top: -4px; left: 50%; transform: translateX(-50%); }
.cp-2 { right: -4px; top: 50%; transform: translateY(-50%); }
.cp-3 { bottom: -4px; left: 50%; transform: translateX(-50%); }
.cp-4 { left: -4px; top: 50%; transform: translateY(-50%); }

/* ── Drop Hint ───────────────────────────────────────────────── */
.drop-hint {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: var(--text-muted, #94a3b8);
  opacity: 0.5;
  pointer-events: none;
  font-size: 14px;
}

.drop-hint i { font-size: 36px; }

/* ── Add Page Button ─────────────────────────────────────────── */
.add-page-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 220px;
  height: 70px;
  border: 2px dashed var(--border, #e2e8f0);
  border-radius: 12px;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s;
  cursor: pointer;
}

.add-page-btn:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.04));
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(99,102,241,0.15);
}

.add-page-btn i { font-size: 18px; }

.add-page-hint {
  font-size: 10px;
  opacity: 0.5;
  font-weight: 400;
}

/* ── Page Navigation ─────────────────────────────────────────── */
.page-navigation {
  position: fixed;
  bottom: 50px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--bg-panel, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 99px;
  padding: 6px 14px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  z-index: 100;
}

.nav-arrow {
  width: 30px;
  height: 30px;
  border: none;
  background: transparent;
  border-radius: 50%;
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.nav-arrow:hover:not(:disabled) {
  background: var(--accent-light, rgba(99,102,241,0.1));
  color: var(--accent, #6366f1);
}

.nav-arrow:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.page-indicator {
  display: flex;
  gap: 6px;
}

.page-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border, #e2e8f0);
  cursor: pointer;
  transition: all 0.2s;
}

.page-dot:hover {
  background: var(--text-muted, #94a3b8);
  transform: scale(1.3);
}

.page-dot.active {
  background: var(--accent, #6366f1);
  box-shadow: 0 0 8px rgba(99,102,241,0.4);
  width: 24px;
  border-radius: 99px;
}

.page-dot.has-content {
  border: 2px solid var(--border, #e2e8f0);
}

/* ── Zoom Indicator ──────────────────────────────────────────── */
.zoom-indicator {
  position: fixed;
  bottom: 100px;
  right: 24px;
  background: var(--bg-panel, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 700;
  color: var(--text-primary, #0f172a);
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  z-index: 50;
  cursor: pointer;
  transition: all 0.15s;
}

.zoom-indicator:hover {
  background: var(--accent, #6366f1);
  color: #fff;
  border-color: var(--accent, #6366f1);
}

.zoom-indicator small {
  font-weight: 400;
  opacity: 0.7;
}

/* ── Minimap ─────────────────────────────────────────────────── */
.minimap {
  position: fixed;
  bottom: 40px;
  right: 24px;
  background: var(--bg-panel, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 10px;
  padding: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.12);
  z-index: 60;
  max-height: 200px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.minimap-page {
  cursor: pointer;
  transition: all 0.15s;
}

.minimap-page:hover {
  outline: 1px solid var(--accent, #6366f1);
}

.minimap-page.active {
  outline: 2px solid #fbbf24;
  box-shadow: 0 0 8px rgba(251,191,36,0.3);
}

.minimap-el {
  position: absolute;
}

.minimap-el.selected {
  outline: 1px solid #fff;
}

.minimap-toggle {
  position: fixed;
  bottom: 40px;
  right: 24px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid var(--border, #e2e8f0);
  background: var(--bg-panel, #ffffff);
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  z-index: 61;
  transition: all 0.15s;
}

.minimap-toggle:hover {
  background: var(--accent, #6366f1);
  color: #fff;
  border-color: var(--accent, #6366f1);
}

/* ── Image Overlay ───────────────────────────────────────────── */
.image-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  opacity: 0;
  transition: opacity 0.2s;
}

.canvas-element:hover .image-overlay {
  opacity: 1;
}

.image-overlay button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.9);
  cursor: pointer;
  color: #475569;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.image-overlay button:hover {
  background: #fff;
  transform: scale(1.1);
}

/* ── Rulers ──────────────────────────────────────────────────── */
.ruler {
  position: sticky;
  background: var(--bg-panel, #ffffff);
  z-index: 40;
}

.ruler-h {
  top: 0;
  height: 20px;
  left: 0;
  right: 0;
}

.ruler-v {
  left: 0;
  width: 20px;
  top: 0;
  bottom: 0;
}

.ruler-canvas-h, .ruler-canvas-v {
  display: block;
}

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 768px) {
  .canvas-area {
    padding: 20px 10px 80px;
  }
  
  .page-navigation {
    bottom: 30px;
    padding: 4px 10px;
  }
  
  .minimap {
    display: none;
  }
  
  .minimap-toggle {
    bottom: 90px;
  }
}
</style>