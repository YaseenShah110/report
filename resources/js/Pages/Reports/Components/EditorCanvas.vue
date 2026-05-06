<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   EditorCanvas.vue - Canvas, Pages, All 46 Element Types       ║
  ║   Drag-Drop · Resize · Rotate · Charts · Grid · Minimap        ║
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
    
    <!-- ═══ GRID OVERLAY ═══════════════════════════════════════════ -->
    <div v-if="showGrid" class="grid-overlay" :style="gridStyle"></div>

    <!-- ═══ RULERS ═══════════════════════════════════════════════ -->
    <div v-if="showRulers" class="ruler ruler-h"><canvas ref="rulerHCanvas" class="ruler-canvas-h"></canvas></div>
    <div v-if="showRulers" class="ruler ruler-v"><canvas ref="rulerVCanvas" class="ruler-canvas-v"></canvas></div>

    <!-- ═══ ALIGNMENT GUIDES ═══════════════════════════════════════ -->
    <AlignmentGuides v-if="alignmentGuides.show" :guides="alignmentGuides.lines" />

    <!-- ═══ RUBBER BAND ═══════════════════════════════════════════ -->
    <div v-if="rubberBand.active" class="rubber-band" :style="rubberBandStyle"></div>

    <!-- ═══ CANVAS CONTAINER ═══════════════════════════════════════ -->
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
        }"
        :style="getPageStyle(page, pi)"
        @click.stop="selectPage(pi)"
        @dragover.prevent="dropTargetPage = pi"
        @dragleave="dropTargetPage = null"
        @drop.stop="onPageDrop($event, pi)"
        @dblclick.self="onPageDblClick($event, pi)"
        @contextmenu.prevent.stop="onPageContext($event, pi)"
      >
        
        <!-- ═══ PAGE LABEL ═══════════════════════════════════════ -->
        <div class="page-label">{{ page.label || 'Page ' + (pi + 1) }}</div>

        <!-- ═══ ACTIVE PAGE GLOW ═══════════════════════════════════ -->
        <div v-if="currentPage === pi" class="page-glow"></div>

        <!-- ═══ WATERMARK ═══════════════════════════════════════ -->
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
        >{{ settings.watermark }}</div>

        <!-- ═══ HEADER ═══════════════════════════════════════════ -->
        <div
          v-if="settings.show_header"
          class="page-header"
          :style="{
            background: settings.header_color || '#1e293b',
            color: '#ffffff',
            position: 'absolute',
            top: 0, left: 0, right: 0,
            height: '44px',
            display: 'flex',
            alignItems: 'center',
            padding: '0 30px',
            fontSize: '12px',
            fontWeight: '600',
            zIndex: 10
          }"
        >{{ settings.header_text || 'Header' }}</div>

        <!-- ═══ ELEMENTS CONTAINER ═══════════════════════════════════ -->
        <div class="elements-container" :style="{ position: 'relative', flex: 1 }">
          
          <!-- ═══ RENDER EACH ELEMENT ═══════════════════════════════ -->
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
            
            <!-- ═══ PRIORITY STRIPE ═══════════════════════════════ -->
            <div
              v-if="el.styles?.priority && el.styles.priority !== 'none'"
              class="priority-stripe"
              :style="{
                background: getPriorityColor(el.styles.priority),
                height: '3px',
                position: 'absolute',
                top: 0, left: 0, right: 0,
                zIndex: 20,
                animation: 'priorityGlow 2s ease-in-out infinite'
              }"
            ></div>

            <!-- ═══ SELECTION HANDLES ═══════════════════════════════ -->
            <template v-if="isSelected(pi, ei) && !el.locked">
              <div
                v-for="h in resizeHandles"
                :key="h"
                class="resize-handle"
                :class="'handle-' + h"
                @mousedown.stop="startResize($event, pi, ei, h)"
              ></div>
              <div class="rotate-handle" @mousedown.stop="startRotate($event, pi, ei)" title="Rotate">
                <i class="fa-solid fa-rotate"></i>
              </div>
              <div class="el-info-bar">
                {{ Math.round(el.position?.x || 0) }}, {{ Math.round(el.position?.y || 0) }} —
                {{ Math.round(el.styles?.width || 100) }} × {{ Math.round(el.styles?.height || 50) }}
                <span v-if="el.styles?.rotate"> | {{ el.styles.rotate }}°</span>
              </div>
              <div class="connection-points">
                <div v-for="cp in 4" :key="cp" class="conn-point" :class="'cp-' + cp"></div>
              </div>
            </template>

            <!-- ═══ LOCK INDICATOR ═══════════════════════════════ -->
            <div v-if="el.locked" class="lock-indicator" title="Locked">
              <i class="fa-solid fa-lock"></i>
            </div>

            <!-- ═══ ELEMENT CONTENT ═══════════════════════════════ -->
            <div class="el-content" :style="getElContentStyle(el)">
              
              <!-- ─── TEXT ELEMENTS ──────────────────────────────── -->
              <div
                v-if="isTextType(el.type)"
                :contenteditable="isEditing(pi, ei) && !el.locked"
                :spellcheck="true"
                class="text-content"
                :class="el.type"
                :style="getTextStyle(el)"
                @input="onTextInput(pi, ei, $event)"
                @blur="onTextBlur"
                @paste="onTextPaste"
                v-html="el.content || getPlaceholder(el.type)"
              ></div>

              <!-- ─── RICH TEXT (TIPTAP) ──────────────────────────── -->
              <TiptapElement
                v-else-if="el.type === 'richtext'"
                :key="el.id"
                :content="el.content || ''"
                :editable="editingElIdx === ei && !el.locked"
                @update:content="(val) => { el.content = val; markDirty() }"
              />

              <!-- ─── IMAGE ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'image'" class="image-content">
                <img
                  v-if="el.src"
                  :src="el.src"
                  :alt="el.alt || 'Image'"
                  :style="{
                    width: '100%', height: '100%',
                    objectFit: el.styles?.objectFit || 'cover',
                    borderRadius: (el.styles?.borderRadius || 0) + 'px',
                    filter: getImageFilter(el.styles?.imageFilter)
                  }"
                  @error="onImageError"
                  loading="lazy"
                />
                <div v-else class="image-placeholder" @click="$emit('image-upload', pi, ei)">
                  <i class="fa-solid fa-image"></i>
                  <span>Click to add image</span>
                  <small>or drop image here</small>
                </div>
                <div v-if="el.src && isSelected(pi, ei)" class="image-overlay">
                  <button @click.stop="$emit('image-replace', { pi, ei })" title="Replace"><i class="fa-solid fa-rotate"></i></button>
                  <button @click.stop="el.src = ''; markDirty()" title="Remove"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>

              <!-- ─── TABLE ──────────────────────────────────────── -->
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
                      >{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, ri) in el.data" :key="ri">
                      <td
                        v-for="(col, ci) in el.columns" :key="ci"
                        :contenteditable="isEditing(pi, ei)"
                        @blur="el.data[ri][col] = $event.target.textContent; markDirty()"
                        :style="{ padding: '6px 10px', borderBottom: '1px solid var(--border, #e2e8f0)', fontSize: '11px' }"
                      >{{ row[col] || '' }}</td>
                    </tr>
                  </tbody>
                </table>
                <div v-if="isSelected(pi, ei)" class="table-controls">
                  <button @click.stop="addRow(pi, ei)">+Row</button>
                  <button @click.stop="addCol(pi, ei)">+Col</button>
                  <button @click.stop="delRow(pi, ei)" :disabled="el.data.length <= 1">−Row</button>
                  <button @click.stop="delCol(pi, ei)" :disabled="el.columns.length <= 1">−Col</button>
                </div>
              </div>

              <!-- ─── LIVE CHARTS ────────────────────────────────── -->
              <div
                v-else-if="isChartType(el.type)"
                :ref="(e) => { if(e) setChartRef(e, pi, ei) }"
                class="chart-container"
                :style="{ width: '100%', height: '100%', position: 'relative', padding: '8px', display: 'flex', flexDirection: 'column' }"
              >
                <div v-if="el.chartTitle" class="chart-title-text">{{ el.chartTitle }}</div>
                <div class="chart-canvas-wrap" :style="{ flex: 1, width: '100%', minHeight: 0 }"></div>
              </div>

              <!-- ─── METRIC / KPI ───────────────────────────────── -->
              <div v-else-if="el.type === 'metric'" class="metric-content" :style="metricStyle(el)">
                <div class="metric-label">{{ el.label || 'Metric' }}</div>
                <div class="metric-value" :style="{ color: el.styles?.valueColor || settings.primary_color }">{{ el.value || '0' }}</div>
                <div v-if="el.change" class="metric-change" :class="el.changeType || 'positive'">
                  <i :class="el.changeType === 'negative' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'"></i> {{ el.change }}
                </div>
              </div>

              <!-- ─── PROGRESS BAR ───────────────────────────────── -->
              <div v-else-if="el.type === 'progress'" class="progress-content">
                <div class="progress-header"><span>{{ el.label || 'Progress' }}</span><span>{{ el.value || 0 }}%</span></div>
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

              <!-- ─── CALLOUT ────────────────────────────────────── -->
              <div v-else-if="el.type === 'callout'" class="callout-content" :style="calloutStyle(el)">
                <span class="callout-emoji">{{ el.emoji || '💡' }}</span>
                <div
                  :contenteditable="isEditing(pi, ei)"
                  @input="el.content = $event.target.innerHTML; markDirty()"
                  v-html="el.content || 'Callout message...'"
                  class="callout-text"
                ></div>
              </div>

              <!-- ─── TIMELINE ───────────────────────────────────── -->
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

              <!-- ─── CHECKLIST ──────────────────────────────────── -->
              <div v-else-if="el.type === 'checklist'" class="checklist-content">
                <div v-for="(item, ci) in (el.items || [])" :key="ci" class="check-item">
                  <div
                    class="check-box" :class="{ checked: item.checked }"
                    @click="item.checked = !item.checked; markDirty()"
                    :style="{ borderColor: settings.primary_color, background: item.checked ? settings.primary_color : 'transparent' }"
                  >
                    <i v-if="item.checked" class="fa-solid fa-check" style="color:#fff;font-size:8px"></i>
                  </div>
                  <span :class="{ 'checked-text': item.checked }">{{ item.text }}</span>
                </div>
              </div>

              <!-- ─── TESTIMONIAL ────────────────────────────────── -->
              <div v-else-if="el.type === 'testimonial'" class="testimonial-content">
                <div class="quote-mark">"</div>
                <p class="testimonial-text">{{ el.content || 'Amazing product!' }}</p>
                <div class="testimonial-author">{{ el.author || 'Jane Doe' }}</div>
                <div class="testimonial-role">{{ el.role || 'CEO' }}</div>
              </div>

              <!-- ─── SIGNATURE ──────────────────────────────────── -->
              <div v-else-if="el.type === 'signature'" class="signature-content">
                <div class="sig-line"></div>
                <div class="sig-name">{{ el.content || 'Signature' }}</div>
                <div class="sig-title">{{ el.label || 'Authorized Signature' }}</div>
              </div>

              <!-- ─── STAT ROW ───────────────────────────────────── -->
              <div v-else-if="el.type === 'stat-row'" class="stat-row-content">
                <div v-for="(stat, si) in (el.stats || [])" :key="si" class="stat-item">
                  <div class="stat-value" :style="{ color: settings.primary_color }">{{ stat.value }}</div>
                  <div class="stat-label">{{ stat.label }}</div>
                </div>
              </div>

              <!-- ─── ICON ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'icon'" class="icon-content" :style="{ color: el.styles?.color || settings.primary_color, fontSize: (el.styles?.fontSize || 40) + 'px' }">{{ el.content || '⭐' }}</div>

              <!-- ─── RATING ─────────────────────────────────────── -->
              <div v-else-if="el.type === 'rating'" class="rating-content">
                <span v-for="i in 5" :key="i" class="rating-star" :style="{ color: i <= (el.value || 4) ? (el.styles?.color || '#f59e0b') : '#cbd5e1', fontSize: (el.styles?.fontSize || 20) + 'px' }">★</span>
              </div>

              <!-- ─── QR CODE ────────────────────────────────────── -->
              <div v-else-if="el.type === 'qr-code'" class="qr-content">
                <img v-if="el.qrUrl" :src="el.qrUrl" :style="{ width: '100%', height: '100%', objectFit: 'contain' }" />
                <div v-else class="qr-placeholder" @click="generateQr(el)">
                  <i class="fa-solid fa-qrcode"></i><span>Click to generate QR</span>
                </div>
              </div>

              <!-- ─── VIDEO ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'video'" class="video-content" :style="{ width: '100%', height: '100%', background: '#000', display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: '8px', overflow: 'hidden' }">
                <iframe v-if="getVideoId(el.videoUrl)" :src="'https://www.youtube.com/embed/' + getVideoId(el.videoUrl)" :style="{ width: '100%', height: '100%' }" frameborder="0" allowfullscreen></iframe>
                <div v-else :style="{ color: '#fff', textAlign: 'center' }"><i class="fa-solid fa-video" style="font-size:32px;opacity:.3;margin-bottom:8px"></i><br><span style="font-size:11px;opacity:.5">Add YouTube URL in properties</span></div>
              </div>

              <!-- ─── MAP ────────────────────────────────────────── -->
              <div v-else-if="el.type === 'map'" class="map-content" :style="{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: '8px', overflow: 'hidden' }">
                <iframe v-if="el.mapAddress" :src="'https://maps.google.com/maps?q=' + encodeURIComponent(el.mapAddress) + '&output=embed'" :style="{ width: '100%', height: '100%' }" frameborder="0"></iframe>
                <div v-else :style="{ textAlign: 'center', color: 'var(--text-muted)' }"><i class="fa-solid fa-map-location-dot" style="font-size:32px;opacity:.3;margin-bottom:8px"></i><br><span style="font-size:11px">Add address in properties</span></div>
              </div>

              <!-- ─── SPARKLINE ──────────────────────────────────── -->
              <div v-else-if="el.type === 'sparkline'" :style="{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', padding: '4px' }">
                <svg :width="el.styles?.width || 200" :height="el.styles?.height || 40" viewBox="0 0 100 30" preserveAspectRatio="none">
                  <polyline :points="getSparkPoints(el)" fill="none" :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <polyline :points="getSparkPoints(el) + ' 100,30 0,30'" :fill="(el.styles?.color || settings.primary_color || '#6366f1') + '20'" stroke="none"/>
                </svg>
              </div>

              <!-- ─── TABLE OF CONTENTS ──────────────────────────── -->
              <div v-else-if="el.type === 'toc'" :style="{ width: '100%', height: '100%', overflow: 'auto', padding: '8px' }">
                <div :style="{ fontSize: '16px', fontWeight: '700', marginBottom: '12px', color: 'var(--text-primary)' }">{{ el.content || 'Table of Contents' }}</div>
                <div v-for="(item, i) in (el.tocItems || [])" :key="i" :style="{ padding: '4px 0', paddingLeft: (item.level - 1) * 16 + 'px', fontSize: item.level === 1 ? '13px' : '11px', fontWeight: item.level === 1 ? '600' : '400', color: 'var(--text-secondary)', borderBottom: '1px dotted var(--border)', display: 'flex', justifyContent: 'space-between' }">
                  <span>{{ item.text }}</span>
                  <span :style="{ color: 'var(--accent)', fontWeight: '600', minWidth: '20px', textAlign: 'right' }">{{ item.page }}</span>
                </div>
                <div v-if="!el.tocItems?.length" :style="{ textAlign: 'center', color: 'var(--text-muted)', padding: '20px', fontSize: '11px' }">Add headings to generate TOC</div>
              </div>

              <!-- ─── SHAPES ─────────────────────────────────────── -->
              <div v-else-if="el.type === 'rectangle'" class="shape-rect" :style="shapeStyle(el)"></div>
              <div v-else-if="el.type === 'circle'" class="shape-circle" :style="shapeStyle(el)" style="border-radius:50%"></div>
              <div v-else-if="el.type === 'triangle'" class="shape-triangle" :style="triangleStyle(el)"></div>
              <div v-else-if="el.type === 'divider'" class="shape-divider" :style="dividerStyle(el)"></div>
              <div v-else-if="el.type === 'arrow'" class="shape-arrow">
                <svg width="100%" height="100%" viewBox="0 0 200 40" preserveAspectRatio="none">
                  <line x1="5" y1="20" x2="185" y2="20" :stroke="el.styles?.color || settings.primary_color" :stroke-width="el.styles?.strokeWidth || 2"/>
                  <polygon points="175,8 195,20 175,32" :fill="el.styles?.color || settings.primary_color"/>
                </svg>
              </div>

              <!-- ─── PAGE NUMBER ────────────────────────────────── -->
              <div v-else-if="el.type === 'pagenum'" class="pagenum-content">{{ pi + 1 }}</div>

              <!-- ─── DATE ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'date-el'" class="date-content">{{ formattedDate }}</div>

              <!-- ─── FALLBACK ───────────────────────────────────── -->
              <div v-else class="fallback-content"><i class="fa-solid fa-cube"></i><span>{{ el.type }}</span></div>

            </div>
          </div>

          <!-- ═══ DROP HINT ═══════════════════════════════════════ -->
          <div v-if="isDraggingEl && !page.elements.length" class="drop-hint">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Drop element here</span>
          </div>
        </div>

        <!-- ═══ FOOTER ═══════════════════════════════════════════ -->
        <div
          v-if="settings.show_footer"
          class="page-footer"
          :style="{
            position: 'absolute', bottom: 0, left: 0, right: 0,
            height: '35px', display: 'flex', alignItems: 'center',
            justifyContent: 'space-between', padding: '0 30px',
            fontSize: '10px', color: '#94a3b8',
            borderTop: '1px solid #e2e8f0', zIndex: 10
          }"
        >
          <span>{{ settings.footer_left || '' }}</span>
          <span v-if="settings.show_page_numbers">Page {{ pi + 1 }} of {{ report.content.length }}</span>
          <span>{{ settings.footer_right || '' }}</span>
        </div>
      </div>

      <!-- ═══ ADD PAGE BUTTON ═══════════════════════════════════════ -->
      <button class="add-page-btn" @click="$emit('add-page')">
        <i class="fa-solid fa-plus"></i>
        <span>Add New Page</span>
      </button>
    </div>

    <!-- ═══ PAGE NAVIGATION ═══════════════════════════════════════ -->
    <div class="page-navigation" v-if="report.content.length > 1">
      <button class="nav-arrow nav-prev" :disabled="currentPage === 0" @click="$emit('go-to-page', currentPage - 1)"><i class="fa-solid fa-chevron-left"></i></button>
      <div class="page-indicator">
        <span
          v-for="(pg, pidx) in report.content" :key="pidx"
          class="page-dot"
          :class="{ active: pidx === currentPage, 'has-content': pg.elements.length > 0 }"
          @click="$emit('go-to-page', pidx)"
          :title="'Page ' + (pidx + 1)"
        ></span>
      </div>
      <button class="nav-arrow nav-next" :disabled="currentPage >= report.content.length - 1" @click="$emit('go-to-page', currentPage + 1)"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

    <!-- ═══ ZOOM INDICATOR ═══════════════════════════════════════ -->
    <div v-if="zoom !== 100" class="zoom-indicator" @click="$emit('zoom-reset')">{{ zoom }}% <small>(click to reset)</small></div>

    <!-- ═══ MINIMAP ═══════════════════════════════════════════ -->
    <div class="minimap" v-if="showMinimap" ref="minimapEl">
      <div
        v-for="(page, pidx) in report.content" :key="'mm-' + page.id"
        class="minimap-page" :class="{ active: pidx === currentPage }"
        :style="getMinimapPageStyle(page)"
        @click="$emit('go-to-page', pidx)"
      >
        <div
          v-for="(el, eidx) in page.elements" :key="'mme-' + el.id"
          class="minimap-el"
          :style="getMinimapElStyle(el)"
          :class="{ selected: pidx === currentPage && (selectedElIdx === eidx || selectedEls.includes(eidx)) }"
        ></div>
      </div>
    </div>

    <!-- ═══ MINIMAP TOGGLE ═══════════════════════════════════════ -->
    <button class="minimap-toggle" @click="showMinimap = !showMinimap" :title="showMinimap ? 'Hide Minimap' : 'Show Minimap'"><i class="fa-solid fa-map"></i></button>

    <!-- ═══ MEASURE LINE ═══════════════════════════════════════ -->
    <div
      v-if="measureMode && measureStart && measureEnd"
      class="measure-line"
      :style="{
        left: measureStart.x + 'px', top: measureStart.y + 'px',
        width: getMeasureDist() + 'px',
        transform: `rotate(${Math.atan2(measureEnd.y - measureStart.y, measureEnd.x - measureStart.x)}rad)`
      }"
    >
      <span class="measure-label">{{ getMeasureDist() }}px</span>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick,reactive  } from 'vue'
import { Chart, registerables } from 'chart.js'
import AlignmentGuides from './AlignmentGuides.vue'
import TiptapElement from './TiptapElement.vue'

Chart.register(...registerables)

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
  measureMode: { type: Boolean, default: false },
  presentationMode: { type: Boolean, default: false },
  presentationPage: { type: Number, default: 0 },
  isDark: { type: Boolean, default: false },
})

const emit = defineEmits([
  'select-element', 'deselect-all', 'add-element', 'select-page', 'add-page',
  'start-editing', 'update-text-content', 'element-mouse-down', 'resize-start',
  'rotate-start', 'canvas-drop', 'canvas-drag-end', 'rubber-band-start',
  'rubber-band-move', 'rubber-band-end', 'zoom-wheel', 'page-dblclick',
  'context-menu', 'image-upload', 'image-replace', 'go-to-page',
  'mark-dirty', 'zoom-reset',
])

// ═══ REFS ═══════════════════════════════════════════════════════
const canvasArea = ref(null)
const rulerHCanvas = ref(null)
const rulerVCanvas = ref(null)
const minimapEl = ref(null)
const showMinimap = ref(false)
const isZooming = ref(false)
const measureStart = ref(null)
const measureEnd = ref(null)
const alignmentGuides = reactive({ show: false, lines: [] })

// ═══ CONSTANTS ═══════════════════════════════════════════════════
const resizeHandles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w']
const formattedDate = computed(() => new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }))

// ═══ CHART REFS ══════════════════════════════════════════════════
const chartRefs = {}
const chartInstances = {}

// ═══ GRID STYLE ══════════════════════════════════════════════════
const gridStyle = computed(() => ({
  backgroundImage: 'linear-gradient(rgba(99,102,241,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.08) 1px,transparent 1px)',
  backgroundSize: '20px 20px',
  position: 'fixed', inset: 0, pointerEvents: 'none', zIndex: 0
}))

const rubberBandStyle = computed(() => ({
  position: 'fixed',
  left: props.rubberBand.x + 'px', top: props.rubberBand.y + 'px',
  width: props.rubberBand.w + 'px', height: props.rubberBand.h + 'px',
  border: '2px dashed #6366f1', backgroundColor: 'rgba(99,102,241,.06)',
  pointerEvents: 'none', zIndex: 1000
}))

// ═══ HELPERS ═════════════════════════════════════════════════════
function getPageDims() {
  const sz = {
    A4: { portrait: { w: 794, h: 1123 }, landscape: { w: 1123, h: 794 } },
    Letter: { portrait: { w: 816, h: 1056 }, landscape: { w: 1056, h: 816 } },
    Legal: { portrait: { w: 816, h: 1344 }, landscape: { w: 1344, h: 816 } },
    A3: { portrait: { w: 1123, h: 1587 }, landscape: { w: 1587, h: 1123 } },
    A5: { portrait: { w: 559, h: 794 }, landscape: { w: 794, h: 559 } },
    custom: { portrait: { w: props.settings.custom_w || 794, h: props.settings.custom_h || 1123 }, landscape: { w: props.settings.custom_h || 1123, h: props.settings.custom_w || 794 } }
  }
  return sz[props.settings.page_size]?.[props.settings.orientation] || sz.A4.portrait
}

function getPageStyle(page, pi) {
  const d = getPageDims()
  const isA = props.currentPage === pi
  return {
    width: d.w + 'px', minHeight: d.h + 'px',
    backgroundColor: props.settings.background_color || '#fff',
    backgroundImage: props.settings.bg_image ? `url(${props.settings.bg_image})` : 'none',
    backgroundSize: 'cover',
    fontFamily: props.settings.font_family || 'Inter',
    fontSize: (props.settings.font_size || 14) + 'px',
    borderRadius: (props.settings.page_radius || 0) + 'px',
    padding: (props.settings.margin || 40) + 'px',
    direction: props.settings.rtl ? 'rtl' : 'ltr',
    color: props.settings.text_color || '#0f172a',
    position: 'relative',
    boxShadow: isA ? '0 0 0 3px rgba(251,191,36,.6),0 0 40px rgba(251,191,36,.2),0 8px 40px rgba(0,0,0,.15)' : '0 8px 40px rgba(0,0,0,.15)',
    transition: 'box-shadow .3s', margin: '0 auto', flexShrink: 0
  }
}

function getElementStyle(el) {
  const s = el.styles || {}
  const b = s.borderWidth ? `${s.borderWidth}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : 'none'
  const f = []
  if (s.blur) f.push(`blur(${s.blur}px)`)
  if (s.brightness && s.brightness !== 100) f.push(`brightness(${s.brightness}%)`)
  if (s.contrast && s.contrast !== 100) f.push(`contrast(${s.contrast}%)`)
  if (s.grayscale) f.push(`grayscale(${s.grayscale}%)`)
  return {
    position: 'absolute',
    left: (el.position?.x || 0) + 'px', top: (el.position?.y || 0) + 'px',
    width: (s.width || 200) + 'px', height: (s.height || 100) + 'px',
    zIndex: s.zIndex || 1,
    opacity: el.visible === false ? 0 : ((s.opacity ?? 100) / 100),
    transform: [s.rotate ? `rotate(${s.rotate}deg)` : '', s.scaleX === -1 ? 'scaleX(-1)' : '', s.scaleY === -1 ? 'scaleY(-1)' : ''].filter(Boolean).join(' ') || 'none',
    borderRadius: (s.borderRadius || 0) + 'px', border: b,
    boxShadow: s.boxShadow || 'none', filter: f.length ? f.join(' ') : 'none',
    mixBlendMode: s.mixBlendMode || 'normal',
    cursor: el.locked ? 'not-allowed' : 'move', userSelect: 'none', overflow: 'hidden'
  }
}

function getElContentStyle(el) {
  const s = el.styles || {}
  return {
    width: '100%', height: '100%',
    backgroundColor: s.backgroundColor || 'transparent',
    padding: (s.padding || 8) + 'px',
    overflow: s.overflow || 'auto',
    fontFamily: s.fontFamily || props.settings.font_family || 'Inter'
  }
}

function getTextStyle(el) {
  const s = el.styles || {}
  return {
    fontFamily: el.type === 'code' ? "'Fira Code',monospace" : (s.fontFamily || props.settings.font_family || 'Inter'),
    fontSize: (s.fontSize || 14) + 'px', fontWeight: s.fontWeight || '400',
    fontStyle: s.fontStyle || 'normal', textDecoration: s.textDecoration || 'none',
    color: s.color || props.settings.text_color || '#0f172a',
    textAlign: s.textAlign || 'left', lineHeight: s.lineHeight || 1.5,
    letterSpacing: (s.letterSpacing || 0) + 'px', textTransform: s.textTransform || 'none',
    background: s.textGradient ? `linear-gradient(135deg,${s.textGradientFrom||'#6366f1'},${s.textGradientTo||'#ec4899'})` : 'none',
    WebkitBackgroundClip: s.textGradient ? 'text' : 'unset',
    WebkitTextFillColor: s.textGradient ? 'transparent' : 'unset',
    outline: 'none', wordBreak: 'break-word',
    whiteSpace: el.type === 'code' ? 'pre-wrap' : 'normal',
    width: '100%', height: '100%',
    columnCount: s.columns || 1, columnGap: '20px'
  }
}

function getElementClasses(el, pi, ei) {
  return {
    'el-selected': isSelected(pi, ei),
    'el-multi-selected': props.selectedEls.includes(ei) && props.selectedElIdx !== ei,
    'el-locked': el.locked,
    'el-editing': isEditing(pi, ei)
  }
}

function isSelected(pi, ei) { return props.currentPage === pi && (props.selectedElIdx === ei || props.selectedEls.includes(ei)) }
function isEditing(pi, ei) { return props.currentPage === pi && props.editingElIdx === ei }
function isTextType(t) { return ['text','heading','subheading','quote','blockquote','highlight','badge','code','link','callout'].includes(t) }
function isChartType(t) { return t?.endsWith('-chart') }
function getPlaceholder(t) { const m = { heading:'Click to edit heading',text:'Start typing...',quote:'Inspiring quote...',code:'// code',badge:'Badge',link:'https://...',callout:'Callout...' }; return m[t]||'Click to edit' }
function getPriorityColor(p) { return { low:'#3b82f6',medium:'#f59e0b',high:'#f97316',urgent:'#ef4444' }[p]||'#6366f1' }
function getImageFilter(f) { switch(f){case'grayscale':return'grayscale(100%)';case'sepia':return'sepia(80%)';case'vintage':return'sepia(50%) contrast(85%) brightness(90%)';case'blur':return'blur(2px)';case'bright':return'brightness(120%)';case'contrast':return'contrast(150%)';default:return'none'} }
function shapeStyle(el){const s=el.styles||{};return{width:'100%',height:'100%',backgroundColor:s.backgroundColor||props.settings.primary_color||'#6366f1',borderRadius:el.type==='circle'?'50%':(s.borderRadius||0)+'px'}}
function triangleStyle(el){const s=el.styles||{};return{width:0,height:0,borderLeft:((s.width||100)/2)+'px solid transparent',borderRight:((s.width||100)/2)+'px solid transparent',borderBottom:(s.height||100)+'px solid '+(s.backgroundColor||props.settings.primary_color||'#6366f1'),backgroundColor:'transparent'}}
function dividerStyle(el){const s=el.styles||{};return{width:'100%',height:(s.borderWidth||2)+'px',backgroundColor:s.color||props.settings.primary_color||'#e2e8f0'}}
function calloutStyle(el){const s=el.styles||{};return{display:'flex',gap:'10px',alignItems:'flex-start',padding:'12px',height:'100%',backgroundColor:s.backgroundColor||(props.settings.primary_color||'#6366f1')+'12',borderLeft:'4px solid '+(s.borderColor||props.settings.primary_color||'#6366f1'),borderRadius:(s.borderRadius||8)+'px'}}
function metricStyle(el){const s=el.styles||{};return{padding:'14px',height:'100%',display:'flex',flexDirection:'column',justifyContent:'center',backgroundColor:s.backgroundColor||'#f8fafc',borderRadius:(s.borderRadius||12)+'px'}}
function getVideoId(u){if(!u)return null;const m=u.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);return m?m[1]:null}
function getSparkPoints(el){const d=el.sparkData||[3,5,4,8,6,7,5,9,7,10];const mx=Math.max(...d);return d.map((v,i)=>`${(i/(d.length-1))*100},${30-(v/mx)*25}`).join(' ')}
function getMinimapPageStyle(p){const d=getPageDims();const s=0.04;return{width:d.w*s+'px',height:d.h*s+'px',backgroundColor:props.settings.background_color||'#fff',position:'relative',borderRadius:'2px',overflow:'hidden',border:'1px solid #e2e8f0'}}
function getMinimapElStyle(el){const d=getPageDims();const s=0.04;return{position:'absolute',left:(el.position?.x||0)*s+'px',top:(el.position?.y||0)*s+'px',width:((el.styles?.width||100)*s)+'px',height:((el.styles?.height||50)*s)+'px',backgroundColor:el.styles?.backgroundColor||props.settings.primary_color||'#6366f1',opacity:.8,borderRadius:'1px'}}
function markDirty(){emit('mark-dirty')}
function getMeasureDist(){if(!measureStart.value||!measureEnd.value)return 0;const dx=measureEnd.value.x-measureStart.value.x;const dy=measureEnd.value.y-measureStart.value.y;return Math.round(Math.sqrt(dx*dx+dy*dy))}
async function generateQr(el){el.qrUrl=`https://api.qrserver.com/v1/create-qr-code/?size=${el.qrSize||150}x${el.qrSize||150}&data=${encodeURIComponent(el.qrText||'https://example.com')}`;markDirty()}

// ═══ CHART RENDERING ═══════════════════════════════════════════
function setChartRef(el, pi, ei) {
  if (!el) return
  const key = `${pi}-${ei}`
  chartRefs[key] = el
  const canvasWrap = el.querySelector('.chart-canvas-wrap')
  if (!canvasWrap) return
  let canvas = canvasWrap.querySelector('canvas')
  if (!canvas) { canvas = document.createElement('canvas'); canvas.style.width = '100%'; canvas.style.height = '100%'; canvasWrap.appendChild(canvas) }
  nextTick(() => renderChart(pi, ei, key))
}

function renderChart(pi, ei, key) {
  const el = props.report.content[pi]?.elements[ei]
  if (!el || !isChartType(el.type)) return
  const container = chartRefs[key]; if (!container) return
  const canvas = container.querySelector('canvas'); if (!canvas) return
  if (chartInstances[key]) { chartInstances[key].destroy(); chartInstances[key] = null }

  const labels = el.chartData?.labels || ['Q1','Q2','Q3','Q4']
  const values = el.chartData?.values || [25,40,35,55]
  const colors = [props.settings.primary_color||'#6366f1',props.settings.accent_color||'#8b5cf6','#10b981','#f59e0b','#ef4444','#8b5cf6']

  let chartType = 'bar'
  if (el.type === 'line-chart') chartType = 'line'
  if (el.type === 'pie-chart') chartType = 'pie'
  if (el.type === 'doughnut-chart') chartType = 'doughnut'
  if (el.type === 'area-chart') chartType = 'line'
  if (el.type === 'radar-chart') chartType = 'radar'

  try {
    chartInstances[key] = new Chart(canvas, {
      type: chartType,
      data: { labels, datasets: [{ label: el.chartTitle||'Data', data: values, backgroundColor: (chartType==='pie'||chartType==='doughnut')?colors.slice(0,values.length):colors[0]+'80', borderColor: colors[0], borderWidth: 2, fill: el.type==='area-chart', tension: 0.3 }] },
      options: { responsive: true, maintainAspectRatio: false, animation: { duration: 500 }, plugins: { legend: { display: false } }, scales: (chartType==='pie'||chartType==='doughnut'||chartType==='radar')?{}:{ y: { beginAtZero: true } } }
    })
  } catch(e) { console.warn('Chart render failed:', e) }
}

// ═══ EVENT HANDLERS ═══════════════════════════════════════════
function selectPage(pi) { emit('select-page', pi) }
function deselectAll() { emit('deselect-all') }
function onDragOver(e) { e.dataTransfer.dropEffect = 'copy' }
function onDrop(e) {
  const d = e.dataTransfer.getData('el-def'); if (!d) return
  const def = JSON.parse(d); const r = canvasArea.value?.getBoundingClientRect()
  if (r) { const s = props.zoom / 100; emit('canvas-drop', { def, x: (e.clientX - r.left) / s - (def.w || 100) / 2, y: (e.clientY - r.top + canvasArea.value.scrollTop) / s - (def.h || 50) / 2 }) }
  emit('canvas-drag-end')
}
function onPageDrop(e, pi) { e.stopPropagation(); const d = e.dataTransfer.getData('el-def'); if (d) { emit('add-element', { def: JSON.parse(d), pageIndex: pi, x: 100, y: 100 }) } }
function onElementMouseDown(e, pi, ei) { emit('element-mouse-down', { event: e, pageIndex: pi, elementIndex: ei }) }
function onElementDblClick(e, pi, ei) { emit('start-editing', { pageIndex: pi, elementIndex: ei }); const el = props.report.content[pi].elements[ei]; if (el?.type === 'image' && !el.src) emit('image-upload', pi, ei) }
function startResize(e, pi, ei, h) { e.stopPropagation(); e.preventDefault(); emit('resize-start', { event: e, pageIndex: pi, elementIndex: ei, handle: h }) }
function startRotate(e, pi, ei) { e.stopPropagation(); e.preventDefault(); emit('rotate-start', { event: e, pageIndex: pi, elementIndex: ei }) }
function onTextInput(pi, ei, ev) { emit('update-text-content', { pageIndex: pi, elementIndex: ei, content: ev.target.innerHTML }) }
function onTextBlur() {}
function onTextPaste(e) { e.preventDefault(); document.execCommand('insertText', false, e.clipboardData.getData('text/plain')) }
function onImageError(e) { e.target.style.display = 'none' }
function onPageDblClick(e, pi) { if (e.target.closest('.canvas-element')) return; emit('page-dblclick', { event: e, pageIndex: pi }) }
function onPageContext(e, pi) { emit('context-menu', e, pi, null) }
function onCanvasContext(e) { emit('context-menu', e, null, null) }
function startRubberBand(e) { if (e.target.closest('.canvas-element') || e.target.closest('.page-navigation') || e.target.closest('.add-page-btn')) return; emit('rubber-band-start', e) }
function onMouseMove(e) { emit('rubber-band-move', e) }
function endRubberBand() { emit('rubber-band-end') }
function onZoomWheel(e) { emit('zoom-wheel', e) }
function onPanWheel(e) { if (canvasArea.value) { canvasArea.value.scrollLeft += e.deltaX; canvasArea.value.scrollTop += e.deltaY } }
function addRow(pi, ei) { const el = props.report.content[pi].elements[ei]; if (el?.type === 'table') { const row = {}; el.columns.forEach(c => row[c] = ''); el.data.push(row); markDirty() } }
function addCol(pi, ei) { const el = props.report.content[pi].elements[ei]; if (el?.type === 'table') { const col = 'Col ' + (el.columns.length + 1); el.columns.push(col); el.data.forEach(r => r[col] = ''); markDirty() } }
function delRow(pi, ei) { const el = props.report.content[pi].elements[ei]; if (el?.type === 'table' && el.data.length > 1) { el.data.pop(); markDirty() } }
function delCol(pi, ei) { const el = props.report.content[pi].elements[ei]; if (el?.type === 'table' && el.columns.length > 1) { const col = el.columns.pop(); el.data.forEach(r => delete r[col]); markDirty() } }

// ═══ WATCHERS ════════════════════════════════════════════════
watch(() => props.zoom, () => { isZooming.value = true; setTimeout(() => isZooming.value = false, 200) })
watch(() => [props.currentPage, props.settings.primary_color, props.settings.accent_color], () => {
  Object.keys(chartRefs).forEach(key => { const [pi, ei] = key.split('-').map(Number); if (pi === props.currentPage) setTimeout(() => renderChart(pi, ei, key), 100) })
}, { deep: true })

// ═══ LIFECYCLE ════════════════════════════════════════════════
onMounted(() => {
  if (canvasArea.value) new ResizeObserver(() => {}).observe(canvasArea.value)
})
</script>

<style scoped>
.canvas-area{flex:1;background:var(--bg-tertiary,#f1f5f9);overflow:auto;position:relative;display:flex;align-items:flex-start;justify-content:center;padding:40px 40px 100px;scrollbar-width:thin}
.canvas-container{display:flex;flex-direction:column;align-items:center;gap:40px;min-height:100%;padding-bottom:60px}
.page-sheet{position:relative;flex-shrink:0;overflow:visible}.page-sheet.page-drop-target{border:2px dashed var(--accent)!important;background:rgba(99,102,241,.03)!important}
.page-label{position:absolute;top:-30px;left:0;font-size:11px;font-weight:700;color:var(--text-muted);user-select:none;pointer-events:none}
.page-glow{position:absolute;inset:-3px;border-radius:6px;border:2px solid rgba(251,191,36,.5);pointer-events:none;animation:pageGlow 3s ease-in-out infinite;z-index:1}
@keyframes pageGlow{0%,100%{box-shadow:0 0 15px rgba(251,191,36,.3);border-color:rgba(251,191,36,.4)}50%{box-shadow:0 0 35px rgba(251,191,36,.5);border-color:rgba(251,191,36,.7)}}
.canvas-element{transform-origin:center;transition:box-shadow .1s}
.canvas-element:not(.el-locked):hover{outline:1px solid rgba(99,102,241,.4);outline-offset:1px}
.canvas-element.el-selected{outline:2px solid var(--accent)!important;outline-offset:1px;box-shadow:0 0 0 6px rgba(99,102,241,.1);z-index:50!important}
.canvas-element.el-multi-selected{outline:2px solid rgba(99,102,241,.5)!important}
.canvas-element.el-editing{outline:2px solid var(--accent)!important;cursor:text!important}
.canvas-element.el-locked{outline-color:var(--warning)!important}
.resize-handle{position:absolute;width:10px;height:10px;background:#fff;border:2px solid var(--accent);border-radius:2px;z-index:100;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.handle-nw{top:-5px;left:-5px;cursor:nw-resize}.handle-n{top:-5px;left:calc(50% - 5px);cursor:n-resize}.handle-ne{top:-5px;right:-5px;cursor:ne-resize}.handle-e{top:calc(50% - 5px);right:-5px;cursor:e-resize}.handle-se{bottom:-5px;right:-5px;cursor:se-resize}.handle-s{bottom:-5px;left:calc(50% - 5px);cursor:s-resize}.handle-sw{bottom:-5px;left:-5px;cursor:sw-resize}.handle-w{top:calc(50% - 5px);left:-5px;cursor:w-resize}
.rotate-handle{position:absolute;top:-32px;left:calc(50% - 14px);width:28px;height:28px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:crosshair;z-index:100;color:#fff;font-size:12px;box-shadow:0 2px 12px rgba(99,102,241,.4)}.rotate-handle:hover{transform:scale(1.1)}
.el-info-bar{position:absolute;bottom:-20px;left:0;font-size:9px;color:var(--text-muted);white-space:nowrap;pointer-events:none;background:var(--bg-panel);padding:2px 6px;border-radius:3px;border:1px solid var(--border)}
.lock-indicator{position:absolute;top:4px;right:4px;background:rgba(245,158,11,.9);color:#fff;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;z-index:30}
.connection-points{position:absolute;inset:0;pointer-events:none;z-index:90}.conn-point{position:absolute;width:8px;height:8px;background:var(--accent);border-radius:50%;border:2px solid #fff}.cp-1{top:-4px;left:50%;transform:translateX(-50%)}.cp-2{right:-4px;top:50%;transform:translateY(-50%)}.cp-3{bottom:-4px;left:50%;transform:translateX(-50%)}.cp-4{left:-4px;top:50%;transform:translateY(-50%)}
@keyframes priorityGlow{0%,100%{opacity:.8}50%{opacity:1;box-shadow:0 2px 12px currentColor}}
.drop-hint{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;gap:8px;color:var(--text-muted);opacity:.5;pointer-events:none;font-size:14px}.drop-hint i{font-size:36px}
.add-page-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;width:220px;height:70px;border:2px dashed var(--border);border-radius:12px;background:transparent;cursor:pointer;color:var(--text-muted);font-size:14px;font-weight:600;transition:all .3s}.add-page-btn:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-light);transform:translateY(-2px);box-shadow:0 8px 30px rgba(99,102,241,.15)}.add-page-btn i{font-size:18px}
.page-navigation{position:fixed;bottom:50px;left:50%;transform:translateX(-50%);display:flex;align-items:center;gap:12px;background:var(--bg-panel);border:1px solid var(--border);border-radius:99px;padding:6px 14px;box-shadow:0 4px 20px rgba(0,0,0,.1);z-index:100}
.nav-arrow{width:30px;height:30px;border:none;background:transparent;border-radius:50%;cursor:pointer;color:var(--text-secondary);font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .15s}.nav-arrow:hover:not(:disabled){background:var(--accent-light);color:var(--accent)}.nav-arrow:disabled{opacity:.3;cursor:not-allowed}
.page-indicator{display:flex;gap:6px}.page-dot{width:8px;height:8px;border-radius:50%;background:var(--border);cursor:pointer;transition:all .2s}.page-dot:hover{background:var(--text-muted);transform:scale(1.3)}.page-dot.active{background:var(--accent);box-shadow:0 0 8px rgba(99,102,241,.4);width:24px;border-radius:99px}.page-dot.has-content{border:2px solid var(--border)}
.zoom-indicator{position:fixed;bottom:100px;right:24px;background:var(--bg-panel);border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;color:var(--text-primary);box-shadow:0 2px 12px rgba(0,0,0,.08);z-index:50;cursor:pointer}.zoom-indicator:hover{background:var(--accent);color:#fff}
.minimap{position:fixed;bottom:40px;right:24px;background:var(--bg-panel);border:1px solid var(--border);border-radius:10px;padding:8px;box-shadow:0 4px 20px rgba(0,0,0,.12);z-index:60;max-height:200px;overflow-y:auto;display:flex;flex-direction:column;gap:6px}.minimap-page{cursor:pointer;transition:all .15s}.minimap-page:hover{outline:1px solid var(--accent)}.minimap-page.active{outline:2px solid #fbbf24;box-shadow:0 0 8px rgba(251,191,36,.3)}.minimap-el{position:absolute}.minimap-el.selected{outline:1px solid #fff}
.minimap-toggle{position:fixed;bottom:40px;right:24px;width:34px;height:34px;border-radius:50%;border:1px solid var(--border);background:var(--bg-panel);cursor:pointer;color:var(--text-secondary);font-size:13px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08);z-index:61;transition:all .15s}.minimap-toggle:hover{background:var(--accent);color:#fff}
.measure-line{position:absolute;height:1px;background:#ef4444;transform-origin:left center;pointer-events:none;z-index:900}.measure-label{position:absolute;top:-16px;left:50%;transform:translateX(-50%);background:#ef4444;color:#fff;font-size:9px;padding:2px 6px;border-radius:3px;white-space:nowrap}
.chart-container{overflow:hidden}.chart-title-text{font-size:11px;font-weight:700;text-align:center;margin-bottom:4px;color:var(--text-primary);flex-shrink:0}.chart-canvas-wrap{position:relative;min-height:0}
.image-placeholder{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;background:var(--bg-secondary);color:var(--text-muted);font-size:12px;cursor:pointer;border:2px dashed var(--border);border-radius:4px}.image-placeholder:hover{border-color:var(--accent);color:var(--accent)}
.image-overlay{position:absolute;inset:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;gap:8px;opacity:0;transition:opacity .2s}.canvas-element:hover .image-overlay{opacity:1}.image-overlay button{width:32px;height:32px;border-radius:50%;border:none;background:rgba(255,255,255,.9);cursor:pointer;color:#475569;font-size:12px;display:flex;align-items:center;justify-content:center}.image-overlay button:hover{background:#fff;transform:scale(1.1)}
.table-controls{display:flex;gap:3px;padding:6px;background:var(--bg-secondary);border-top:1px solid var(--border)}.table-controls button{padding:3px 8px;font-size:10px;border:1px solid var(--border);border-radius:4px;background:var(--bg-panel);cursor:pointer;color:var(--text-secondary)}.table-controls button:hover{background:var(--accent-light);color:var(--accent)}.table-controls button:disabled{opacity:.4}
.metric-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:4px}.metric-value{font-size:28px;font-weight:800;line-height:1}.metric-change{display:flex;align-items:center;gap:3px;font-size:12px;margin-top:6px}.metric-change.positive{color:#10b981}.metric-change.negative{color:#ef4444}
.progress-header{display:flex;justify-content:space-between;font-size:12px;font-weight:500;margin-bottom:8px}.progress-track{height:8px;background:var(--bg-secondary);border-radius:4px;overflow:hidden}
.timeline-content{padding:8px;overflow:auto;height:100%}.tl-item{display:flex;gap:10px;margin-bottom:16px;position:relative}.tl-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px}.tl-line{position:absolute;left:4px;top:14px;bottom:-16px;width:2px;background:var(--border)}
.checklist-content{display:flex;flex-direction:column;gap:8px;padding:4px;overflow:auto;height:100%}.check-item{display:flex;align-items:center;gap:10px;font-size:13px}.check-box{width:18px;height:18px;border:2px solid var(--border);border-radius:4px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s}.check-box.checked{border-color:transparent}.checked-text{text-decoration:line-through;opacity:.5}
.testimonial-content{width:100%;height:100%;overflow:hidden;padding:16px;display:flex;flex-direction:column}.quote-mark{font-size:36px;font-family:Georgia,serif;line-height:.8;opacity:.3}.testimonial-text{font-style:italic;font-size:13px;line-height:1.6;flex:1}.testimonial-author{font-weight:600;font-size:12px;margin-top:8px}.testimonial-role{font-size:10px;opacity:.6}
.signature-content{width:100%;height:100%;display:flex;flex-direction:column;justify-content:flex-end;padding:8px}.sig-line{flex:1;border-bottom:2px solid var(--border)}.sig-name{font-family:Georgia,serif;font-style:italic;font-size:18px;color:var(--text-muted);margin-top:4px}.sig-title{font-size:10px;color:var(--text-muted)}
.stat-row-content{display:flex;align-items:center;justify-content:space-around;width:100%;height:100%;padding:8px}.stat-item{text-align:center;flex:1}.stat-value{font-size:24px;font-weight:800;line-height:1}.stat-label{font-size:10px;text-transform:uppercase;letter-spacing:.06em;opacity:.6;margin-top:4px}
.qr-content{width:100%;height:100%}.qr-placeholder{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;background:var(--bg-secondary);border:2px dashed var(--border);border-radius:8px;cursor:pointer;color:var(--text-muted);font-size:12px}.qr-placeholder:hover{border-color:var(--accent);color:var(--accent)}
.fallback-content{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:11px;color:var(--text-muted);border:1px dashed var(--border);border-radius:4px}.fallback-content i{font-size:24px;opacity:.4;margin-bottom:4px}
@media(max-width:768px){.canvas-area{padding:20px 10px 80px}.page-navigation{bottom:30px;padding:4px 10px}.minimap{display:none}.minimap-toggle{bottom:90px}}
</style>