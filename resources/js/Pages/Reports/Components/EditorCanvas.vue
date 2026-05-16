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
    @contextmenu.prevent="onCanvasContext"
  >
    <!-- GRID -->
    <div v-if="showGrid" class="grid-overlay" :style="gridStyle" />

    <!-- RULERS -->
    <div v-if="showRulers" class="ruler ruler-h">
      <canvas ref="rulerH" class="ruler-canvas" />
    </div>
    <div v-if="showRulers" class="ruler ruler-v">
      <canvas ref="rulerV" class="ruler-canvas-v" />
    </div>

    <!-- RUBBER BAND -->
    <div v-if="rubberBand.active && rubberBand.w > 2" class="rubber-band" :style="rubberBandStyle" />

    <!-- ALIGNMENT GUIDES -->
    <template v-if="guides.length">
      <div v-for="(g, i) in guides" :key="i" class="align-guide" :class="g.type" :style="g.style">
        <span v-if="g.label" class="guide-label">{{ g.label }}</span>
      </div>
    </template>

    <!-- STYLE PAINTER CURSOR -->
    <div v-if="stylePainterActive" class="painter-cursor">
      <i class="fa-solid fa-paintbrush"></i> Click element to apply style
    </div>

    <!-- CANVAS CONTAINER -->
    <div class="canvas-container" ref="canvasContainer" :style="containerStyle">

      <!-- PAGE SHEETS -->
      <div
        v-for="(page, pi) in report.content"
        :key="page.id"
        class="page-wrapper"
        :class="{ 'page-active': currentPage === pi }"
      >
        <!-- Page Label -->
        <div class="page-label-bar">
          <span class="page-label-text">
            <i class="fa-regular fa-file"></i>
            {{ page.label || `Page ${pi + 1}` }}
          </span>
          <div class="page-label-actions">
            <button class="plabel-btn" @click.stop="$emit('duplicate-page', pi)" title="Duplicate"><i class="fa-solid fa-copy"></i></button>
            <button class="plabel-btn danger" @click.stop="$emit('delete-page', pi)" title="Delete" :disabled="report.content.length <= 1"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>

        <!-- THE PAGE -->
        <div
          class="page-sheet"
          :class="{
            'page-drop-target': dropTargetPage === pi,
            'page-selected': currentPage === pi,
          }"
          :data-page-index="pi"
          :style="getPageStyle(page)"
          @click.stop="selectPage(pi)"
          @dblclick.self="onPageDblClick($event, pi)"
          @dragover.prevent="onPageDragOver($event, pi)"
          @dragleave="dropTargetPage = null"
          @drop.stop="onPageDrop($event, pi)"
          @contextmenu.prevent.stop="$emit('context-menu', $event, pi, null)"
        >
          <!-- Watermark -->
          <div v-if="settings.watermark" class="page-watermark" :style="watermarkStyle">
            {{ settings.watermark }}
          </div>

          <!-- Header -->
          <div v-if="settings.show_header" class="page-header-bar" :style="headerStyle">
            {{ settings.header_text }}
          </div>

          <!-- ELEMENTS -->
          <div
            v-for="(el, ei) in page.elements"
            :key="el.id"
            v-show="el.visible !== false"
            class="canvas-el"
            :class="getElClasses(el, pi, ei)"
            :style="getElStyle(el)"
            :data-el-id="el.id"
            :data-page-index="pi"
            :data-el-index="ei"
            @mousedown.stop="onElMouseDown($event, pi, ei)"
            @dblclick.stop="onElDblClick($event, pi, ei)"
            @contextmenu.prevent.stop="$emit('context-menu', $event, pi, ei)"
            @click.stop="onElClick($event, pi, ei)"
          >
            <!-- Resize handles -->
            <template v-if="isSelected(pi, ei) && !el.locked && !isEditing(pi, ei)">
              <div v-for="h in handles" :key="h" class="resize-handle" :class="`h-${h}`" @mousedown.stop.prevent="$emit('resize-start', { event: $event, pageIndex: pi, elementIndex: ei, handle: h })" />
              <div class="rotate-handle" @mousedown.stop.prevent="$emit('rotate-start', { event: $event, pageIndex: pi, elementIndex: ei })" title="Rotate">
                <i class="fa-solid fa-rotate"></i>
              </div>
            </template>

            <!-- Lock indicator -->
            <div v-if="el.locked" class="lock-badge"><i class="fa-solid fa-lock"></i></div>

            <!-- Group indicator -->
            <div v-if="el.groupId && isSelected(pi, ei)" class="group-badge"><i class="fa-solid fa-object-group"></i></div>

            <!-- Style Painter overlay -->
            <div v-if="stylePainterActive && !el.locked" class="painter-overlay" @click.stop="$emit('style-painter-apply', ei)" />

            <!-- ELEMENT CONTENT -->
            <div class="el-inner" :style="getElInnerStyle(el)">
              <!-- ── TEXT TYPES ── -->
              <div
                v-if="isTextType(el.type)"
                :contenteditable="isEditing(pi, ei) && !el.locked"
                class="text-el"
                :class="[`type-${el.type}`, { rtl: settings.rtl }]"
                :style="getTextStyle(el)"
                @input="onTextInput(pi, ei, $event)"
                @blur="onTextBlur"
                @paste="onPaste"
                @mousedown.stop="isEditing(pi, ei) ? null : onElMouseDown($event, pi, ei)"
                v-html="el.content || placeholder(el.type)"
              />

              <!-- ── RICH TEXT ── -->
              <div
                v-else-if="el.type === 'richtext'"
                :contenteditable="isEditing(pi, ei) && !el.locked"
                class="richtext-el"
                :style="getTextStyle(el)"
                @input="onTextInput(pi, ei, $event)"
                @blur="onTextBlur"
                @paste="onPaste"
                @mousedown.stop="isEditing(pi, ei) ? null : onElMouseDown($event, pi, ei)"
                v-html="el.content || '<p>Start typing...</p>'"
              />

              <!-- ── IMAGE ── -->
              <div v-else-if="el.type === 'image'" class="img-el">
                <img v-if="el.src" :src="el.src" :alt="el.alt || ''" :style="getImgStyle(el)" @error="$event.target.style.display='none'" loading="lazy" />
                <div v-else class="img-placeholder" @click="$emit('image-upload', pi, ei)">
                  <i class="fa-solid fa-image"></i>
                  <span>Click to upload image</span>
                  <small>or drag & drop</small>
                </div>
                <div v-if="el.src && isSelected(pi, ei)" class="img-actions">
                  <button @click.stop="$emit('image-replace', { pi, ei, el })" title="Replace"><i class="fa-solid fa-rotate"></i></button>
                  <button @click.stop="el.src = ''; $emit('mark-dirty')" title="Remove"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>

              <!-- ── TABLE ── -->
              <div v-else-if="el.type === 'table'" class="table-el">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th v-for="(col, ci) in el.columns" :key="ci"
                        :style="{ background: settings.primary_color || '#6366f1', color: '#fff', padding: '8px 10px', fontSize: '11px', fontWeight: '600' }"
                        :contenteditable="isEditing(pi, ei)"
                        @blur="el.columns[ci] = $event.target.textContent; $emit('mark-dirty')">{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, ri) in el.data" :key="ri" :style="{ background: ri % 2 === 0 ? (el.styles?.evenRowBg || '#fff') : (el.styles?.oddRowBg || '#f8fafc') }">
                      <td v-for="col in el.columns" :key="col"
                        :contenteditable="isEditing(pi, ei)"
                        @blur="el.data[ri][col] = $event.target.textContent; $emit('mark-dirty')"
                        style="padding:7px 10px;border-bottom:1px solid #e2e8f0;font-size:11px">{{ row[col] || '' }}</td>
                    </tr>
                  </tbody>
                </table>
                <div v-if="isSelected(pi, ei)" class="table-controls">
                  <button @click.stop="$emit('add-table-row')">+Row</button>
                  <button @click.stop="$emit('add-table-col')">+Col</button>
                  <button @click.stop="$emit('remove-table-row')" :disabled="el.data?.length <= 1">-Row</button>
                  <button @click.stop="$emit('remove-table-col')" :disabled="el.columns?.length <= 1">-Col</button>
                </div>
              </div>

              <!-- ── CHARTS ── -->
              <div v-else-if="isChartType(el.type)" class="chart-el" :ref="(r) => r && setChartRef(r, pi, ei)">
                <div class="chart-title-txt" v-if="el.chartTitle">{{ el.chartTitle }}</div>
                <div class="chart-canvas-wrap" style="flex:1;width:100%;min-height:0;position:relative"></div>
              </div>

              <!-- ── METRIC ── -->
              <div v-else-if="el.type === 'metric'" class="metric-el" :style="metricContainerStyle(el)">
                <div class="m-label">{{ el.label || 'Metric' }}</div>
                <div class="m-value" :style="{ color: el.styles?.valueColor || settings.primary_color }">{{ el.value || '—' }}</div>
                <div v-if="el.change" class="m-change" :class="el.changeType">
                  <i :class="el.changeType === 'negative' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'"></i>
                  {{ el.change }}
                  <span class="m-period">{{ el.changePeriod || '' }}</span>
                </div>
              </div>

              <!-- ── PROGRESS ── -->
              <div v-else-if="el.type === 'progress'" class="progress-el">
                <div class="prog-header"><span>{{ el.label || 'Progress' }}</span><span>{{ el.value || 0 }}%</span></div>
                <div class="prog-track" :style="{ background: el.styles?.trackColor || '#e2e8f0' }">
                  <div class="prog-fill" :style="{ width: (el.value || 0) + '%', background: `linear-gradient(90deg, ${settings.primary_color || '#6366f1'}, ${settings.accent_color || '#8b5cf6'})` }" />
                </div>
              </div>

              <!-- ── CIRCULAR PROGRESS ── -->
              <div v-else-if="el.type === 'circular-progress'" class="circ-el">
                <svg viewBox="0 0 120 120">
                  <circle cx="60" cy="60" r="52" fill="none" :stroke="el.styles?.trackColor || '#e2e8f0'" stroke-width="10" />
                  <circle cx="60" cy="60" r="52" fill="none"
                    :stroke="el.styles?.color || settings.primary_color || '#6366f1'"
                    stroke-width="10" stroke-linecap="round"
                    :stroke-dasharray="`${((el.value || 0) / 100) * 327} 327`"
                    transform="rotate(-90 60 60)" />
                  <text x="60" y="60" text-anchor="middle" dominant-baseline="central" :fill="el.styles?.color || settings.primary_color || '#6366f1'" font-size="20" font-weight="800">{{ el.value || 0 }}%</text>
                </svg>
                <div v-if="el.label" class="circ-label">{{ el.label }}</div>
              </div>

              <!-- ── CHECKLIST ── -->
              <div v-else-if="el.type === 'checklist'" class="checklist-el">
                <div v-for="(item, ci) in (el.items || [])" :key="ci" class="check-item">
                  <div class="check-box" :class="{ checked: item.checked }" :style="{ borderColor: settings.primary_color, background: item.checked ? settings.primary_color : 'transparent' }" @click.stop="item.checked = !item.checked; $emit('mark-dirty')">
                    <i v-if="item.checked" class="fa-solid fa-check" style="color:#fff;font-size:9px" />
                  </div>
                  <span :class="{ 'line-through': item.checked }">{{ item.text }}</span>
                </div>
              </div>

              <!-- ── TIMELINE ── -->
              <div v-else-if="el.type === 'timeline'" class="timeline-el">
                <div v-for="(item, ti) in (el.items || [])" :key="ti" class="tl-item">
                  <div class="tl-marker">
                    <div class="tl-dot" :style="{ background: settings.primary_color || '#6366f1' }" />
                    <div v-if="ti < el.items.length - 1" class="tl-line" />
                  </div>
                  <div class="tl-body">
                    <div class="tl-date">{{ item.date }}</div>
                    <div class="tl-title">{{ item.label }}</div>
                    <div class="tl-desc">{{ item.desc }}</div>
                  </div>
                </div>
              </div>

              <!-- ── STAT ROW ── -->
              <div v-else-if="el.type === 'stat-row'" class="stat-row-el">
                <div v-for="(stat, si) in (el.stats || [])" :key="si" class="stat-item">
                  <div class="stat-val" :style="{ color: settings.primary_color }">{{ stat.value }}</div>
                  <div class="stat-lbl">{{ stat.label }}</div>
                </div>
              </div>

              <!-- ── TESTIMONIAL ── -->
              <div v-else-if="el.type === 'testimonial'" class="testimonial-el">
                <div class="testim-quote">"</div>
                <p class="testim-text">{{ el.content || 'Amazing product!' }}</p>
                <div class="testim-author">{{ el.author || 'Author Name' }}</div>
                <div class="testim-role">{{ el.role || 'Title' }}</div>
              </div>

              <!-- ── CALLOUT ── -->
              <div v-else-if="el.type === 'callout'" class="callout-el" :style="calloutStyle(el)">
                <span class="callout-emoji">{{ el.emoji || '💡' }}</span>
                <div :contenteditable="isEditing(pi, ei)" @input="el.content = $event.target.innerHTML; $emit('mark-dirty')" class="callout-body" v-html="el.content || 'Callout text'" />
              </div>

              <!-- ── SIGNATURE ── -->
              <div v-else-if="el.type === 'signature'" class="sig-el">
                <div class="sig-line" :style="{ borderColor: settings.primary_color || '#6366f1' }" />
                <div class="sig-name">{{ el.content || 'Signature' }}</div>
                <div class="sig-title">{{ el.label || 'Authorized Signature' }}</div>
              </div>

              <!-- ── RATING ── -->
              <div v-else-if="el.type === 'rating'" class="rating-el">
                <span v-for="i in 5" :key="i" class="rating-star" :style="{ color: i <= (el.value || 4) ? (el.styles?.color || '#f59e0b') : '#cbd5e1', fontSize: (el.styles?.fontSize || 22) + 'px' }" @click.stop="el.value = i; $emit('mark-dirty')">★</span>
              </div>

              <!-- ── QR CODE ── -->
              <div v-else-if="el.type === 'qr-code'" class="qr-el">
                <img v-if="el.qrUrl" :src="el.qrUrl" style="width:100%;height:100%;object-fit:contain" />
                <div v-else class="qr-placeholder" @click.stop="generateQR(el)">
                  <i class="fa-solid fa-qrcode"></i>
                  <span>Click to generate QR</span>
                  <small>{{ el.qrText || 'https://example.com' }}</small>
                </div>
              </div>

              <!-- ── VIDEO ── -->
              <div v-else-if="el.type === 'video'" class="video-el">
                <iframe v-if="getYtId(el.videoUrl)" :src="`https://www.youtube.com/embed/${getYtId(el.videoUrl)}`" style="width:100%;height:100%;border:none;border-radius:inherit" allowfullscreen />
                <div v-else class="video-placeholder"><i class="fa-solid fa-video"></i><span>Add YouTube URL in properties</span></div>
              </div>

              <!-- ── MAP ── -->
              <div v-else-if="el.type === 'map'" class="map-el">
                <iframe v-if="el.mapAddress" :src="`https://maps.google.com/maps?q=${encodeURIComponent(el.mapAddress)}&output=embed`" style="width:100%;height:100%;border:none;border-radius:inherit" />
                <div v-else class="map-placeholder"><i class="fa-solid fa-map-location-dot"></i><span>Add address in properties</span></div>
              </div>

              <!-- ── SPARKLINE ── -->
              <div v-else-if="el.type === 'sparkline'" class="spark-el">
                <svg width="100%" height="100%" :viewBox="`0 0 100 40`" preserveAspectRatio="none">
                  <defs>
                    <linearGradient :id="`sg-${el.id}`" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" :stop-color="el.styles?.color || settings.primary_color || '#6366f1'" stop-opacity="0.3" />
                      <stop offset="100%" :stop-color="el.styles?.color || settings.primary_color || '#6366f1'" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  <polygon :points="getSparkFill(el)" :fill="`url(#sg-${el.id})`" />
                  <polyline :points="getSparkPoints(el)" fill="none" :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>

              <!-- ── LIST ── -->
              <div v-else-if="el.type === 'list'" class="list-el" :style="getTextStyle(el)">
                <component :is="el.styles?.listStyle === 'numbered' ? 'ol' : 'ul'" :style="{ paddingLeft: '20px' }">
                  <li v-for="(item, li) in (el.items || [])" :key="li">{{ item }}</li>
                </component>
              </div>

              <!-- ── ICON ── -->
              <div v-else-if="el.type === 'icon'" class="icon-el" :style="{ color: el.styles?.color || settings.primary_color, fontSize: (el.styles?.fontSize || 40) + 'px' }">{{ el.content || '⭐' }}</div>

              <!-- ── BADGE ── -->
              <div v-else-if="el.type === 'badge'" class="badge-el" :style="badgeStyle(el)">{{ el.content || 'Badge' }}</div>

              <!-- ── PAGE NUMBER ── -->
              <div v-else-if="el.type === 'pagenum'" class="pagenum-el" :style="getTextStyle(el)">{{ pi + 1 }}</div>

              <!-- ── DATE ── -->
              <div v-else-if="el.type === 'date'" class="date-el" :style="getTextStyle(el)">{{ todayFormatted }}</div>

              <!-- ── RECTANGLE ── -->
              <div v-else-if="el.type === 'rectangle'" class="shape-rect" :style="shapeStyle(el)" />

              <!-- ── CIRCLE ── -->
              <div v-else-if="el.type === 'circle'" class="shape-circle" :style="shapeStyle(el)" style="border-radius:50%" />

              <!-- ── TRIANGLE ── -->
              <div v-else-if="el.type === 'triangle'" class="shape-tri" :style="triStyle(el)" />

              <!-- ── STAR ── -->
              <div v-else-if="el.type === 'star'" class="shape-star" :style="{ color: el.styles?.backgroundColor || settings.primary_color, fontSize: Math.min(el.styles?.width || 80, el.styles?.height || 80) + 'px', lineHeight: 1 }">★</div>

              <!-- ── HEXAGON ── -->
              <svg v-else-if="el.type === 'hexagon'" viewBox="0 0 100 86.6" width="100%" height="100%">
                <polygon points="50,0 100,25 100,75 50,100 0,75 0,25" :fill="el.styles?.backgroundColor || settings.primary_color" />
              </svg>

              <!-- ── DIVIDER ── -->
              <div v-else-if="el.type === 'divider'" class="divider-el" :style="dividerStyle(el)" />

              <!-- ── ARROW ── -->
              <div v-else-if="el.type === 'arrow'" class="arrow-el">
                <svg width="100%" height="100%" viewBox="0 0 200 40" preserveAspectRatio="none">
                  <line x1="4" y1="20" x2="188" y2="20" :stroke="el.styles?.color || settings.primary_color" :stroke-width="el.styles?.borderWidth || 2" />
                  <polygon points="176,8 196,20 176,32" :fill="el.styles?.color || settings.primary_color" />
                </svg>
              </div>

              <!-- ── PRICE CARD ── -->
              <div v-else-if="el.type === 'price-card'" class="price-card-el">
                <div class="pc-plan">{{ el.plan || 'Plan' }}</div>
                <div class="pc-amount" :style="{ color: settings.primary_color }">{{ el.price || '$0' }}</div>
                <div class="pc-period">{{ el.period || '/month' }}</div>
                <ul class="pc-features">
                  <li v-for="f in (el.features || [])" :key="f"><i class="fa-solid fa-check" :style="{ color: settings.primary_color }" /> {{ f }}</li>
                </ul>
              </div>

              <!-- ── SOCIAL CARD ── -->
              <div v-else-if="el.type === 'social-card'" class="social-card-el">
                <div class="sc-avatar">{{ el.avatar || '👤' }}</div>
                <div class="sc-name">{{ el.content || 'Name' }}</div>
                <div class="sc-sub">{{ el.subtitle || 'Role' }}</div>
              </div>

              <!-- ── KANBAN ── -->
              <div v-else-if="el.type === 'kanban'" class="kanban-el">
                <div class="kn-priority" :style="{ background: getPriorityColor(el.priority) }" />
                <div class="kn-title">{{ el.content || 'Task' }}</div>
                <div class="kn-status" :style="{ color: settings.primary_color }">{{ el.status || 'In Progress' }}</div>
                <div v-if="el.due" class="kn-due"><i class="fa-regular fa-calendar" /> {{ el.due }}</div>
              </div>

              <!-- ── HTML EMBED ── -->
              <div v-else-if="el.type === 'html-embed'" class="html-el" v-html="el.htmlContent || '<div style=\'padding:12px;text-align:center\'>HTML Embed</div>'" />

              <!-- ── WATERMARK TEXT ── -->
              <div v-else-if="el.type === 'watermark'" class="watermark-el" :style="{ fontSize: (el.styles?.fontSize || 48) + 'px', fontWeight: '900', color: el.styles?.color || '#94a3b8', opacity: (el.styles?.opacity || 10) / 100 }">{{ el.content || 'CONFIDENTIAL' }}</div>

              <!-- ── TOC ── -->
              <div v-else-if="el.type === 'toc'" class="toc-el">
                <div class="toc-header">{{ el.content || 'Table of Contents' }}</div>
                <div v-for="(item, ti) in (el.tocItems || [])" :key="ti" class="toc-item" :style="{ paddingLeft: (item.level - 1) * 16 + 'px', fontSize: item.level === 1 ? '13px' : '11px', fontWeight: item.level === 1 ? '600' : '400' }">
                  <span>{{ item.text }}</span>
                  <span class="toc-page" :style="{ color: settings.primary_color }">{{ item.page }}</span>
                </div>
                <div v-if="!el.tocItems?.length" class="toc-empty">Click "Refresh TOC" in properties</div>
              </div>

              <!-- ── FALLBACK ── -->
              <div v-else class="el-fallback"><i class="fa-solid fa-cube" /><span>{{ el.type }}</span></div>

            </div>

            <!-- INFO BAR (position/size) when selected -->
            <div v-if="isSelected(pi, ei)" class="el-infobar">
              {{ Math.round(el.position.x) }}, {{ Math.round(el.position.y) }} — {{ Math.round(el.styles.width) }} × {{ Math.round(el.styles.height) }}<span v-if="el.styles?.rotate"> · {{ el.styles.rotate }}°</span>
            </div>
          </div>

          <!-- Drop hint -->
          <div v-if="isDraggingEl && dropTargetPage === pi && !page.elements.length" class="drop-hint">
            <i class="fa-solid fa-plus-circle" /><span>Drop here</span>
          </div>

          <!-- Footer -->
          <div v-if="settings.show_footer" class="page-footer-bar" :style="footerStyle">
            <span>{{ settings.footer_left }}</span>
            <span>{{ settings.footer_center }}</span>
            <span>{{ (settings.footer_right || '').replace('{n}', pi + 1) }}</span>
          </div>
        </div>
      </div>

      <!-- ADD PAGE BUTTON -->
      <button class="add-page-btn" @click="$emit('add-page')">
        <i class="fa-solid fa-plus" /> Add Page
      </button>
    </div>

    <!-- PAGE NAVIGATION DOTS -->
    <div class="page-nav-bar" v-if="report.content.length > 1">
      <button class="nav-arr" :disabled="currentPage === 0" @click="$emit('go-to-page', currentPage - 1)"><i class="fa-solid fa-chevron-left" /></button>
      <div class="nav-dots">
        <span v-for="(pg, pi) in report.content" :key="pi" class="nav-dot" :class="{ active: pi === currentPage, 'has-content': pg.elements?.length > 0 }" @click="$emit('go-to-page', pi)" :title="pg.label || `Page ${pi + 1}`" />
      </div>
      <button class="nav-arr" :disabled="currentPage >= report.content.length - 1" @click="$emit('go-to-page', currentPage + 1)"><i class="fa-solid fa-chevron-right" /></button>
      <span class="nav-info">{{ currentPage + 1 }} / {{ report.content.length }}</span>
    </div>

    <!-- ZOOM BADGE -->
    <div v-if="zoom !== 100" class="zoom-badge" @click="$emit('zoom-reset')">{{ zoom }}% <small>↩ reset</small></div>
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
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
  gridSize: { type: Number, default: 10 },
  isDraggingEl: { type: Boolean, default: false },
  rubberBand: { type: Object, default: () => ({}) },
  dropTargetPage: { type: [Number, null], default: null },
  measureMode: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
  stylePainterActive: { type: Boolean, default: false },
})

const emit = defineEmits([
  'select-element','deselect-all','add-element','select-page','add-page',
  'start-editing','stop-editing','update-text-content','element-mouse-down',
  'resize-start','rotate-start','canvas-drop','canvas-drag-end',
  'rubber-band-start','rubber-band-move','rubber-band-end','zoom-wheel',
  'page-dblclick','context-menu','image-upload','image-replace',
  'go-to-page','mark-dirty','zoom-reset','element-cross-page','scroll-to-page',
  'style-painter-apply','duplicate-page','delete-page',
  'add-table-row','add-table-col','remove-table-row','remove-table-col',
])

const canvasArea = ref(null)
const canvasContainer = ref(null)
const handles = ['nw','n','ne','e','se','s','sw','w']
const chartRefs = {}
const chartInstances = {}
const guides = ref([])
let guideTimer = null

const todayFormatted = computed(() => new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }))

// ── Page/element geometry ───────────────────────────────────────────────────
function getPageDims() {
  const sz = {
    A4: { portrait: { w: 794, h: 1123 }, landscape: { w: 1123, h: 794 } },
    Letter: { portrait: { w: 816, h: 1056 }, landscape: { w: 1056, h: 816 } },
    Legal: { portrait: { w: 816, h: 1344 }, landscape: { w: 1344, h: 816 } },
    A3: { portrait: { w: 1123, h: 1587 }, landscape: { w: 1587, h: 1123 } },
    A5: { portrait: { w: 559, h: 794 }, landscape: { w: 794, h: 559 } },
    custom: { portrait: { w: props.settings.custom_w || 794, h: props.settings.custom_h || 1123 }, landscape: { w: props.settings.custom_h || 1123, h: props.settings.custom_w || 794 } },
  }
  return sz[props.settings.page_size]?.[props.settings.orientation] || sz.A4.portrait
}

const containerStyle = computed(() => ({
  transform: `scale(${props.zoom / 100})`,
  transformOrigin: 'top center',
  display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '48px',
  padding: '40px 40px 80px', minWidth: 'max-content',
}))

const gridStyle = computed(() => {
  const g = props.gridSize || 10
  return {
    position: 'fixed', inset: 0, pointerEvents: 'none', zIndex: 0,
    backgroundImage: `linear-gradient(var(--grid-color, rgba(99,102,241,.07)) 1px, transparent 1px), linear-gradient(90deg, var(--grid-color, rgba(99,102,241,.07)) 1px, transparent 1px)`,
    backgroundSize: `${g * (props.zoom / 100)}px ${g * (props.zoom / 100)}px`,
  }
})

const rubberBandStyle = computed(() => ({
  position: 'fixed', left: props.rubberBand.x + 'px', top: props.rubberBand.y + 'px',
  width: props.rubberBand.w + 'px', height: props.rubberBand.h + 'px',
  border: '2px dashed var(--accent, #6366f1)', background: 'rgba(99,102,241,.05)',
  pointerEvents: 'none', zIndex: 999,
}))

const watermarkStyle = computed(() => ({
  position: 'absolute', top: '50%', left: '50%',
  transform: `translate(-50%,-50%) rotate(${props.settings.watermark_rotate || -30}deg)`,
  fontSize: '72px', fontWeight: '900',
  color: props.settings.primary_color || '#94a3b8',
  opacity: (props.settings.watermark_opacity || 8) / 100,
  whiteSpace: 'nowrap', pointerEvents: 'none', zIndex: 5,
  textTransform: 'uppercase', letterSpacing: '0.1em',
}))

const headerStyle = computed(() => ({
  position: 'absolute', top: 0, left: 0, right: 0,
  height: (props.settings.header_height || 50) + 'px',
  background: props.settings.header_color || '#1e293b',
  color: '#fff', display: 'flex', alignItems: 'center',
  padding: '0 24px', zIndex: 10, fontSize: '12px', fontWeight: '600',
}))

const footerStyle = computed(() => ({
  position: 'absolute', bottom: 0, left: 0, right: 0, height: '36px',
  display: 'flex', alignItems: 'center', justifyContent: 'space-between',
  padding: '0 24px', fontSize: '10px', color: '#94a3b8',
  borderTop: '1px solid rgba(0,0,0,.08)', zIndex: 10,
}))

function getPageStyle(page) {
  const d = getPageDims()
  return {
    width: d.w + 'px', minHeight: d.h + 'px',
    background: page.background || props.settings.background_color || '#fff',
    backgroundImage: props.settings.bg_image ? `url(${props.settings.bg_image})` : 'none',
    backgroundSize: 'cover', backgroundPosition: 'center',
    fontFamily: props.settings.font_family || "'DM Sans', sans-serif",
    fontSize: (props.settings.font_size || 14) + 'px',
    color: props.settings.text_color || '#0f172a',
    position: 'relative', overflow: 'hidden',
    borderRadius: (props.settings.page_radius || 0) + 'px',
    direction: props.settings.rtl ? 'rtl' : 'ltr',
  }
}

function getElStyle(el) {
  const s = el.styles || {}
  return {
    position: 'absolute',
    left: (el.position?.x || 0) + 'px', top: (el.position?.y || 0) + 'px',
    width: (s.width || 200) + 'px', height: (s.height || 80) + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: [
      s.rotate ? `rotate(${s.rotate}deg)` : '',
      s.scaleX === -1 ? 'scaleX(-1)' : '',
      s.scaleY === -1 ? 'scaleY(-1)' : '',
    ].filter(Boolean).join(' ') || 'none',
    transformOrigin: 'center center',
    borderRadius: (s.borderRadius || 0) + 'px',
    border: s.borderWidth > 0 ? `${s.borderWidth}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : 'none',
    boxShadow: s.boxShadow || 'none',
    filter: buildFilter(s),
    mixBlendMode: s.mixBlendMode || 'normal',
    cursor: el.locked ? 'not-allowed' : 'move',
    userSelect: 'none', overflow: 'visible',
    pointerEvents: el.visible === false ? 'none' : 'all',
  }
}

function buildFilter(s) {
  const f = []
  if (s.blur > 0) f.push(`blur(${s.blur}px)`)
  if (s.brightness && s.brightness !== 100) f.push(`brightness(${s.brightness}%)`)
  if (s.contrast && s.contrast !== 100) f.push(`contrast(${s.contrast}%)`)
  if (s.grayscale > 0) f.push(`grayscale(${s.grayscale}%)`)
  if (s.saturate && s.saturate !== 100) f.push(`saturate(${s.saturate}%)`)
  if (s.sepia > 0) f.push(`sepia(${s.sepia}%)`)
  return f.length ? f.join(' ') : 'none'
}

function getElInnerStyle(el) {
  const s = el.styles || {}
  return {
    width: '100%', height: '100%',
    background: ['rectangle','circle','triangle','star','hexagon','divider','arrow','watermark'].includes(el.type) ? 'transparent' : (s.backgroundColor || 'transparent'),
    padding: (s.padding || 0) + 'px', overflow: s.overflow || 'hidden',
    fontFamily: s.fontFamily || props.settings.font_family,
    borderRadius: 'inherit',
  }
}

function getTextStyle(el) {
  const s = el.styles || {}
  return {
    fontSize: (s.fontSize || 14) + 'px',
    fontWeight: s.fontWeight || '400',
    fontStyle: s.fontStyle || 'normal',
    textDecoration: s.textDecoration || 'none',
    color: s.color || props.settings.text_color || '#0f172a',
    textAlign: s.textAlign || 'left',
    lineHeight: s.lineHeight || 1.6,
    letterSpacing: (s.letterSpacing || 0) + 'px',
    textTransform: s.textTransform || 'none',
    fontFamily: s.fontFamily || props.settings.font_family,
    width: '100%', height: '100%', overflow: 'auto', wordBreak: 'break-word',
    outline: 'none', whiteSpace: el.type === 'code' ? 'pre-wrap' : 'normal',
    columnCount: s.columns || 1,
  }
}

function getImgStyle(el) {
  const s = el.styles || {}
  return { width: '100%', height: '100%', objectFit: s.objectFit || 'cover', borderRadius: 'inherit', display: 'block' }
}

function shapeStyle(el) {
  const s = el.styles || {}
  return { width: '100%', height: '100%', background: s.backgroundColor || props.settings.primary_color || '#6366f1' }
}

function triStyle(el) {
  const s = el.styles || {}
  const w = s.width || 120, h = s.height || 100
  return { width: 0, height: 0, borderLeft: `${w / 2}px solid transparent`, borderRight: `${w / 2}px solid transparent`, borderBottom: `${h}px solid ${s.backgroundColor || props.settings.primary_color}`, background: 'transparent' }
}

function dividerStyle(el) {
  const s = el.styles || {}
  return { width: '100%', height: (s.borderWidth || 2) + 'px', background: s.color || s.backgroundColor || '#e2e8f0', borderRadius: '99px' }
}

function metricContainerStyle(el) {
  const s = el.styles || {}
  return { background: s.backgroundColor || '#f8fafc', borderRadius: (s.borderRadius || 12) + 'px', padding: '16px', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'center', border: s.borderWidth > 0 ? `${s.borderWidth}px solid ${s.borderColor || '#e2e8f0'}` : 'none' }
}

function calloutStyle(el) {
  const s = el.styles || {}
  const pc = props.settings.primary_color || '#6366f1'
  return { display: 'flex', gap: '10px', alignItems: 'flex-start', background: s.backgroundColor || (pc + '12'), borderLeft: `4px solid ${s.borderColor || pc}`, borderRadius: (s.borderRadius || 8) + 'px', padding: '12px', height: '100%' }
}

function badgeStyle(el) {
  const s = el.styles || {}
  const pc = props.settings.primary_color || '#6366f1'
  return { display: 'inline-flex', alignItems: 'center', justifyContent: 'center', background: s.backgroundColor || (pc + '20'), color: s.color || pc, borderRadius: (s.borderRadius || 999) + 'px', padding: '4px 12px', fontSize: (s.fontSize || 12) + 'px', fontWeight: '600', width: '100%', height: '100%' }
}

function getElClasses(el, pi, ei) {
  return {
    'el-selected': isSelected(pi, ei),
    'el-multi': props.selectedEls.includes(ei) && props.selectedElIdx !== ei,
    'el-locked': el.locked, 'el-hidden': el.visible === false,
    'el-editing': isEditing(pi, ei), 'el-grouped': !!el.groupId,
  }
}

function isSelected(pi, ei) { return props.currentPage === pi && (props.selectedElIdx === ei || props.selectedEls.includes(ei)) }
function isEditing(pi, ei) { return props.currentPage === pi && props.editingElIdx === ei }

function isTextType(t) {
  return ['text','heading','subheading','quote','blockquote','highlight','badge','code','link','toc'].includes(t)
}
function isChartType(t) {
  return ['bar-chart','line-chart','area-chart','pie-chart','doughnut-chart','radar-chart','scatter-chart','bubble-chart','polar-chart','funnel-chart'].includes(t)
}

function placeholder(t) {
  const p = { text: 'Click to edit', heading: 'Heading', subheading: 'Subheading', quote: '"Inspiring quote"', code: '// code', blockquote: 'Blockquote text', highlight: 'Highlighted text', link: 'https://...', badge: 'Badge' }
  return p[t] || 'Click to edit'
}

// ── Chart rendering ─────────────────────────────────────────────────────────
function setChartRef(el, pi, ei) {
  const key = `${pi}-${ei}`
  chartRefs[key] = el
  nextTick(() => renderChart(pi, ei, key))
}

function renderChart(pi, ei, key) {
  const el = props.report.content[pi]?.elements[ei]
  if (!el || !isChartType(el.type)) return
  const container = chartRefs[key]; if (!container) return
  let wrap = container.querySelector('.chart-canvas-wrap'); if (!wrap) return
  let canvas = wrap.querySelector('canvas')
  if (!canvas) { canvas = document.createElement('canvas'); wrap.appendChild(canvas) }
  if (chartInstances[key]) { try { chartInstances[key].destroy() } catch(_){} delete chartInstances[key] }

  const labels = el.chartData?.labels || ['Jan','Feb','Mar','Apr']
  const values = el.chartData?.values || [25, 40, 35, 55]
  const pc = el.chartColor || props.settings.primary_color || '#6366f1'
  const colors = [pc, '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#84cc16']
  const isPolar = ['pie-chart','doughnut-chart','polar-chart'].includes(el.type)
  const isArea = el.type === 'area-chart'

  const typeMap = {
    'bar-chart': 'bar', 'line-chart': 'line', 'area-chart': 'line',
    'pie-chart': 'pie', 'doughnut-chart': 'doughnut', 'radar-chart': 'radar',
    'scatter-chart': 'scatter', 'bubble-chart': 'bubble', 'polar-chart': 'polarArea',
  }

  try {
    chartInstances[key] = new Chart(canvas, {
      type: typeMap[el.type] || 'bar',
      data: {
        labels,
        datasets: [{
          label: el.chartDatasetLabel || el.chartTitle || 'Dataset',
          data: values,
          backgroundColor: isPolar ? colors.slice(0, values.length).map(c => c + 'cc') : (isArea ? pc + '30' : pc + 'cc'),
          borderColor: isPolar ? colors.slice(0, values.length) : pc,
          borderWidth: 2, fill: isArea, tension: 0.35,
          pointBackgroundColor: pc, pointRadius: 4, pointHoverRadius: 6,
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: false, animation: { duration: 400 },
        plugins: {
          legend: { display: isPolar, position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
          title: { display: false },
          tooltip: { callbacks: { label: (ctx) => ` ${ctx.formattedValue}` } },
        },
        scales: isPolar || el.type === 'radar-chart' ? {} : {
          x: { grid: { color: 'rgba(0,0,0,.06)' }, ticks: { font: { size: 10 } } },
          y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.06)' }, ticks: { font: { size: 10 } } },
        },
      },
    })
  } catch (e) { console.warn('Chart render error:', e) }
}

// Re-render charts when settings or data changes
watch([() => props.settings.primary_color, () => props.currentPage], () => {
  nextTick(() => {
    Object.keys(chartRefs).forEach(key => {
      const [pi, ei] = key.split('-').map(Number)
      if (pi === props.currentPage) renderChart(pi, ei, key)
    })
  })
}, { deep: false })

// ── QR Code ─────────────────────────────────────────────────────────────────
async function generateQR(el) {
  const text = el.qrText || 'https://example.com'
  const size = el.qrSize || 150
  el.qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}`
  emit('mark-dirty')
}

// ── Sparkline helpers ────────────────────────────────────────────────────────
function getSparkPoints(el) {
  const d = el.sparkData || [3,5,4,8,6,7,5,9,7,10]
  const mx = Math.max(...d), mn = Math.min(...d)
  const range = mx - mn || 1
  return d.map((v, i) => `${(i / (d.length - 1)) * 100},${35 - ((v - mn) / range) * 30}`).join(' ')
}
function getSparkFill(el) {
  const pts = getSparkPoints(el)
  const d = el.sparkData || [3,5,4,8,6,7,5,9,7,10]
  return `${pts} ${100},40 0,40`
}

// ── Misc helpers ─────────────────────────────────────────────────────────────
function getYtId(url) {
  if (!url) return null
  const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/)
  return m ? m[1] : null
}
function getPriorityColor(p) {
  return { low: '#3b82f6', medium: '#f59e0b', high: '#f97316', urgent: '#ef4444' }[p] || '#94a3b8'
}

// ── Event handlers ───────────────────────────────────────────────────────────
function selectPage(pi) { emit('select-page', pi) }
function deselectAll() { emit('deselect-all') }

function onElMouseDown(e, pi, ei) {
  if (e.button !== 0) return
  emit('element-mouse-down', { event: e, pageIndex: pi, elementIndex: ei })
}

function onElDblClick(e, pi, ei) {
  emit('start-editing', { pageIndex: pi, elementIndex: ei })
  const el = props.report.content[pi]?.elements[ei]
  if (el?.type === 'image' && !el.src) emit('image-upload', pi, ei)
}

function onElClick(e, pi, ei) {
  if (props.stylePainterActive) { emit('style-painter-apply', ei); return }
}

function onTextInput(pi, ei, ev) {
  emit('update-text-content', { pageIndex: pi, elementIndex: ei, content: ev.target.innerHTML })
}
function onTextBlur() {}
function onPaste(e) {
  e.preventDefault()
  const text = e.clipboardData.getData('text/plain')
  document.execCommand('insertText', false, text)
}

function onDragOver(e) { e.dataTransfer.dropEffect = 'copy' }
function onPageDragOver(e, pi) { dropTargetPage.value = pi; e.dataTransfer.dropEffect = 'copy' }

function onDrop(e) {
  const data = e.dataTransfer.getData('el-def'); if (!data) return
  const def = JSON.parse(data)
  const rect = canvasArea.value?.getBoundingClientRect()
  if (!rect) return
  const scale = props.zoom / 100
  const scrollEl = canvasArea.value
  emit('canvas-drop', {
    def,
    x: (e.clientX - rect.left + scrollEl.scrollLeft) / scale - (def.w || 200) / 2,
    y: (e.clientY - rect.top + scrollEl.scrollTop) / scale - (def.h || 80) / 2,
    pageIdx: props.currentPage,
  })
  emit('canvas-drag-end')
}

function onPageDrop(e, pi) {
  const data = e.dataTransfer.getData('el-def')
  if (!data) return
  const def = JSON.parse(data)
  const pageEl = e.currentTarget
  const rect = pageEl.getBoundingClientRect()
  const scale = props.zoom / 100
  emit('canvas-drop', {
    def,
    x: (e.clientX - rect.left) / scale - (def.w || 200) / 2,
    y: (e.clientY - rect.top) / scale - (def.h || 80) / 2,
    pageIdx: pi,
  })
  dropTargetPage.value = null
  emit('canvas-drag-end')
}

function onPageDblClick(e, pi) {
  if (e.target.closest('.canvas-el')) return
  emit('page-dblclick', { event: e, pageIndex: pi })
}

function onCanvasContext(e) { emit('context-menu', e, null, null) }

function startRubberBand(e) {
  if (e.target.closest('.canvas-el') || e.target.closest('.page-nav-bar') || e.target.closest('.add-page-btn') || e.target.closest('.page-label-bar')) return
  emit('rubber-band-start', e)
}
function onMouseMove(e) { emit('rubber-band-move', e) }
function endRubberBand() { emit('rubber-band-end') }
function onZoomWheel(e) { emit('zoom-wheel', e) }

// ── Cleanup ─────────────────────────────────────────────────────────────────
onBeforeUnmount(() => {
  Object.values(chartInstances).forEach(c => { try { c.destroy() } catch(_){} })
})
</script>

<style scoped>
.canvas-area {
  flex: 1; overflow: auto; background: var(--bg-tertiary, #f1f5f9);
  position: relative; display: flex; flex-direction: column;
  align-items: center; scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}

.canvas-container { will-change: transform; }

/* Page wrapper */
.page-wrapper { display: flex; flex-direction: column; align-items: center; }

.page-label-bar {
  display: flex; align-items: center; justify-content: space-between;
  width: 100%; padding: 4px 8px; margin-bottom: 8px;
  font-size: 10px; font-weight: 700; color: var(--text-muted, #94a3b8);
  text-transform: uppercase; letter-spacing: 0.06em;
}
.page-label-text { display: flex; align-items: center; gap: 5px; }
.page-label-actions { display: flex; gap: 3px; }
.plabel-btn {
  width: 22px; height: 22px; border: none; background: transparent; cursor: pointer;
  color: var(--text-muted); border-radius: 4px; font-size: 10px; display: flex; align-items: center; justify-content: center;
}
.plabel-btn:hover { background: var(--bg-secondary); color: var(--text-primary); }
.plabel-btn.danger:hover { color: var(--danger, #ef4444); background: var(--danger-light); }
.plabel-btn:disabled { opacity: 0.3; cursor: not-allowed; }

/* Page sheet */
.page-sheet {
  flex-shrink: 0; position: relative; overflow: hidden;
  box-shadow: 0 8px 40px rgba(0,0,0,.14), 0 2px 8px rgba(0,0,0,.08);
  transition: box-shadow 0.2s;
}
.page-selected { box-shadow: 0 0 0 3px var(--accent, #6366f1), 0 8px 40px rgba(99,102,241,.18), 0 2px 8px rgba(0,0,0,.08) !important; }
.page-drop-target { outline: 3px dashed var(--accent, #6366f1) !important; background: rgba(99,102,241,.02) !important; }

/* Canvas elements */
.canvas-el {
  position: absolute; transform-origin: center center;
  transition: outline 0.1s, box-shadow 0.1s;
}
.canvas-el:not(.el-locked):hover { outline: 1px solid rgba(99,102,241,.35); outline-offset: 1px; }
.canvas-el.el-selected { outline: 2px solid var(--accent, #6366f1) !important; outline-offset: 1px; box-shadow: 0 0 0 5px rgba(99,102,241,.1); z-index: 100 !important; }
.canvas-el.el-multi { outline: 2px solid rgba(99,102,241,.5) !important; }
.canvas-el.el-editing { outline: 2px solid var(--accent, #6366f1) !important; cursor: text !important; }
.canvas-el.el-locked { cursor: not-allowed !important; }
.canvas-el.el-hidden { opacity: 0.25; }
.canvas-el.el-grouped { outline-color: #10b981 !important; }

.el-inner { width: 100%; height: 100%; position: relative; }

/* Resize handles */
.resize-handle {
  position: absolute; width: 9px; height: 9px;
  background: #fff; border: 2px solid var(--accent, #6366f1);
  border-radius: 2px; z-index: 200; box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.h-nw { top: -5px; left: -5px; cursor: nw-resize; }
.h-n  { top: -5px; left: calc(50% - 4px); cursor: n-resize; }
.h-ne { top: -5px; right: -5px; cursor: ne-resize; }
.h-e  { top: calc(50% - 4px); right: -5px; cursor: e-resize; }
.h-se { bottom: -5px; right: -5px; cursor: se-resize; }
.h-s  { bottom: -5px; left: calc(50% - 4px); cursor: s-resize; }
.h-sw { bottom: -5px; left: -5px; cursor: sw-resize; }
.h-w  { top: calc(50% - 4px); left: -5px; cursor: w-resize; }
.rotate-handle {
  position: absolute; top: -36px; left: calc(50% - 14px);
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--accent, #6366f1); color: #fff; font-size: 12px;
  display: flex; align-items: center; justify-content: center;
  cursor: crosshair; z-index: 200; box-shadow: 0 2px 8px rgba(99,102,241,.35);
  transition: transform 0.15s;
}
.rotate-handle:hover { transform: scale(1.1); }

/* Info bar */
.el-infobar {
  position: absolute; bottom: -22px; left: 0; white-space: nowrap;
  font-size: 9px; color: var(--text-muted, #94a3b8);
  background: var(--bg-panel, #fff); border: 1px solid var(--border, #e2e8f0);
  padding: 2px 6px; border-radius: 4px; pointer-events: none; z-index: 200;
}

/* Badges */
.lock-badge, .group-badge {
  position: absolute; top: 4px; right: 4px; width: 18px; height: 18px;
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 9px; z-index: 30; pointer-events: none;
}
.lock-badge { background: rgba(245,158,11,.9); color: #fff; }
.group-badge { background: rgba(16,185,129,.9); color: #fff; right: 26px; }

/* Painter overlay */
.painter-overlay {
  position: absolute; inset: 0; z-index: 50; cursor: copy;
  background: rgba(99,102,241,.08); border: 2px dashed var(--accent, #6366f1);
  border-radius: inherit;
}

/* Alignment guides */
.align-guide { position: fixed; pointer-events: none; z-index: 900; background: #ec4899; opacity: 0.8; }
.align-guide.v { width: 1px; top: 0; bottom: 0; box-shadow: 0 0 6px rgba(236,72,153,.4); }
.align-guide.h { height: 1px; left: 0; right: 0; box-shadow: 0 0 6px rgba(236,72,153,.4); }
.guide-label {
  position: absolute; top: -16px; left: 4px; font-size: 9px; font-weight: 700;
  color: #fff; background: #ec4899; padding: 1px 5px; border-radius: 3px; white-space: nowrap;
}

/* Rubber band */
.rubber-band { position: fixed; border: 2px dashed var(--accent, #6366f1); background: rgba(99,102,241,.05); pointer-events: none; z-index: 999; }

/* Grid overlay */
.grid-overlay { position: fixed; inset: 0; pointer-events: none; z-index: 0; }

/* Rulers */
.ruler { position: fixed; background: var(--bg-panel, #fff); z-index: 50; }
.ruler-h { top: 0; left: 0; right: 0; height: 20px; }
.ruler-v { top: 0; left: 0; bottom: 0; width: 20px; }

/* Painter cursor hint */
.painter-cursor {
  position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
  background: var(--accent, #6366f1); color: #fff; padding: 8px 16px;
  border-radius: 99px; font-size: 11px; font-weight: 600; z-index: 300;
  display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 20px rgba(99,102,241,.4);
}

/* Element types */
.text-el, .richtext-el { width: 100%; height: 100%; outline: none; word-break: break-word; overflow: auto; }
.type-code { font-family: 'Fira Code', 'Courier New', monospace !important; background: #1e293b; color: #34d399; padding: 12px; border-radius: 8px; white-space: pre-wrap; }
.type-quote { border-left: 4px solid var(--accent, #6366f1); padding-left: 14px; font-style: italic; }
.type-blockquote { background: rgba(99,102,241,.06); padding: 12px; border-radius: 6px; border-left: 4px solid var(--accent, #6366f1); }
.type-highlight { background: #fef3c7; padding: 2px 6px; border-radius: 3px; }
.type-link { color: var(--accent, #6366f1); text-decoration: underline; }

.img-el { width: 100%; height: 100%; position: relative; overflow: hidden; border-radius: inherit; }
.img-placeholder {
  width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 8px; background: var(--bg-secondary, #f8fafc); color: var(--text-muted, #94a3b8);
  border: 2px dashed var(--border, #e2e8f0); border-radius: inherit; cursor: pointer; font-size: 12px;
}
.img-placeholder:hover { border-color: var(--accent, #6366f1); color: var(--accent, #6366f1); }
.img-placeholder i { font-size: 28px; opacity: 0.5; }
.img-actions {
  position: absolute; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; gap: 8px;
  opacity: 0; transition: opacity 0.2s; border-radius: inherit;
}
.img-el:hover .img-actions { opacity: 1; }
.img-actions button { width: 32px; height: 32px; border-radius: 50%; border: none; background: rgba(255,255,255,.9); cursor: pointer; color: #475569; font-size: 13px; display: flex; align-items: center; justify-content: center; }
.img-actions button:hover { background: #fff; transform: scale(1.1); }

.table-el { width: 100%; height: 100%; overflow: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.table-controls { display: flex; gap: 4px; padding: 6px; background: var(--bg-secondary); border-top: 1px solid var(--border); flex-shrink: 0; }
.table-controls button { padding: 3px 8px; font-size: 10px; border: 1px solid var(--border); border-radius: 4px; background: var(--bg-panel); cursor: pointer; color: var(--text-secondary); font-family: inherit; }
.table-controls button:hover { background: var(--accent-light); color: var(--accent); }
.table-controls button:disabled { opacity: 0.4; cursor: not-allowed; }

.chart-el { width: 100%; height: 100%; display: flex; flex-direction: column; overflow: hidden; }
.chart-title-txt { font-size: 11px; font-weight: 700; text-align: center; margin-bottom: 4px; color: var(--text-primary); flex-shrink: 0; }
.chart-canvas-wrap { flex: 1; position: relative; min-height: 0; }

.metric-el { width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; }
.m-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); margin-bottom: 4px; }
.m-value { font-size: 28px; font-weight: 800; line-height: 1; }
.m-change { display: flex; align-items: center; gap: 4px; font-size: 11px; margin-top: 6px; font-weight: 600; }
.m-change.positive { color: #10b981; }
.m-change.negative { color: #ef4444; }
.m-period { font-size: 10px; font-weight: 400; color: var(--text-muted); margin-left: 2px; }

.progress-el { width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; gap: 6px; }
.prog-header { display: flex; justify-content: space-between; font-size: 12px; font-weight: 500; }
.prog-track { height: 8px; border-radius: 99px; overflow: hidden; }
.prog-fill { height: 100%; border-radius: 99px; transition: width 0.4s ease; }

.circ-el { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.circ-el svg { max-width: 100%; max-height: 85%; }
.circ-label { font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 4px; }

.checklist-el { display: flex; flex-direction: column; gap: 8px; overflow: auto; height: 100%; }
.check-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
.check-box { width: 18px; height: 18px; border: 2px solid var(--border); border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
.line-through { text-decoration: line-through; opacity: 0.5; }

.timeline-el { display: flex; flex-direction: column; gap: 16px; overflow: auto; height: 100%; padding: 4px; }
.tl-item { display: flex; gap: 10px; }
.tl-marker { display: flex; flex-direction: column; align-items: center; width: 12px; flex-shrink: 0; }
.tl-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.tl-line { width: 2px; flex: 1; background: var(--border); margin-top: 4px; }
.tl-body { flex: 1; }
.tl-date { font-size: 10px; color: var(--accent); font-weight: 600; }
.tl-title { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.tl-desc { font-size: 11px; color: var(--text-muted); }

.stat-row-el { display: flex; align-items: center; justify-content: space-around; width: 100%; height: 100%; }
.stat-item { text-align: center; flex: 1; }
.stat-val { font-size: 24px; font-weight: 800; }
.stat-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); margin-top: 4px; }

.testimonial-el { width: 100%; height: 100%; display: flex; flex-direction: column; overflow: hidden; }
.testim-quote { font-size: 40px; line-height: 0.8; opacity: 0.3; font-family: Georgia, serif; }
.testim-text { font-style: italic; font-size: 13px; line-height: 1.6; flex: 1; }
.testim-author { font-weight: 700; font-size: 12px; margin-top: 8px; }
.testim-role { font-size: 10px; color: var(--text-muted); }

.callout-el { width: 100%; height: 100%; }
.callout-emoji { font-size: 18px; flex-shrink: 0; }
.callout-body { flex: 1; outline: none; font-size: 13px; }

.sig-el { width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; gap: 4px; }
.sig-line { border-bottom: 2px solid; flex: 1; }
.sig-name { font-size: 16px; font-family: Georgia, serif; font-style: italic; color: var(--text-muted); }
.sig-title { font-size: 10px; color: var(--text-muted); }

.rating-el { display: flex; align-items: center; gap: 4px; width: 100%; height: 100%; }
.rating-star { cursor: pointer; transition: transform 0.1s; }
.rating-star:hover { transform: scale(1.2); }

.qr-el { width: 100%; height: 100%; }
.qr-placeholder { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; background: var(--bg-secondary); border: 2px dashed var(--border); border-radius: 8px; cursor: pointer; color: var(--text-muted); font-size: 12px; }
.qr-placeholder:hover { border-color: var(--accent); color: var(--accent); }
.qr-placeholder i { font-size: 28px; }

.video-el, .map-el { width: 100%; height: 100%; border-radius: inherit; overflow: hidden; }
.video-placeholder, .map-placeholder { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: #1e293b; color: rgba(255,255,255,.5); font-size: 12px; border-radius: inherit; }
.video-placeholder i { font-size: 32px; opacity: 0.4; }
.map-placeholder { background: var(--bg-secondary); color: var(--text-muted); }
.map-placeholder i { font-size: 32px; opacity: 0.4; }

.spark-el { width: 100%; height: 100%; }

.list-el { width: 100%; height: 100%; overflow: auto; }
.icon-el { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }

.pagenum-el, .date-el { width: 100%; height: 100%; display: flex; align-items: center; }

.shape-rect, .shape-circle { width: 100%; height: 100%; }
.shape-tri { width: 0; height: 0; }
.shape-star { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }

.divider-el { width: 100%; }
.arrow-el { width: 100%; height: 100%; }

.price-card-el { width: 100%; height: 100%; display: flex; flex-direction: column; overflow: auto; }
.pc-plan { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
.pc-amount { font-size: 32px; font-weight: 800; line-height: 1; }
.pc-period { font-size: 11px; color: var(--text-muted); margin-bottom: 12px; }
.pc-features { list-style: none; display: flex; flex-direction: column; gap: 5px; }
.pc-features li { display: flex; align-items: center; gap: 6px; font-size: 11px; }

.social-card-el { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; overflow: hidden; }
.sc-avatar { font-size: 40px; }
.sc-name { font-weight: 600; font-size: 14px; }
.sc-sub { font-size: 11px; color: var(--text-muted); }

.kanban-el { width: 100%; height: 100%; display: flex; flex-direction: column; gap: 5px; overflow: hidden; position: relative; }
.kn-priority { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 99px 99px 0 0; }
.kn-title { font-weight: 600; font-size: 13px; padding-top: 6px; }
.kn-status { font-size: 10px; font-weight: 600; }
.kn-due { font-size: 10px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; margin-top: auto; }

.html-el { width: 100%; height: 100%; overflow: auto; }
.watermark-el { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; user-select: none; }
.toc-el { width: 100%; height: 100%; overflow: auto; display: flex; flex-direction: column; gap: 4px; }
.toc-header { font-size: 16px; font-weight: 700; margin-bottom: 8px; color: var(--text-primary); }
.toc-item { display: flex; justify-content: space-between; align-items: baseline; padding: 3px 0; border-bottom: 1px dotted var(--border); color: var(--text-secondary); }
.toc-page { font-weight: 700; min-width: 20px; text-align: right; }
.toc-empty { color: var(--text-muted); font-size: 11px; font-style: italic; }

.el-fallback { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px dashed var(--border); border-radius: 6px; color: var(--text-muted); font-size: 11px; gap: 4px; }
.el-fallback i { font-size: 20px; opacity: 0.4; }

/* Drop hint */
.drop-hint { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 8px; color: var(--text-muted); pointer-events: none; }
.drop-hint i { font-size: 36px; opacity: 0.3; }

/* Watermark overlay */
.page-watermark { position: absolute; pointer-events: none; z-index: 5; user-select: none; }

/* Header/Footer bars */
.page-header-bar { position: absolute; top: 0; left: 0; right: 0; z-index: 10; }
.page-footer-bar { position: absolute; bottom: 0; left: 0; right: 0; z-index: 10; }

/* Add page */
.add-page-btn {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 220px; height: 68px; border: 2px dashed var(--border); border-radius: 12px;
  background: transparent; cursor: pointer; color: var(--text-muted); font-size: 13px; font-weight: 600;
  transition: all 0.25s; font-family: inherit;
}
.add-page-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,.15); }

/* Page nav */
.page-nav-bar {
  position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%);
  display: flex; align-items: center; gap: 10px; z-index: 100;
  background: var(--bg-panel); border: 1px solid var(--border);
  border-radius: 99px; padding: 5px 14px; box-shadow: var(--shadow-lg);
}
.nav-arr {
  width: 28px; height: 28px; border: none; background: transparent; border-radius: 50%;
  cursor: pointer; color: var(--text-secondary); font-size: 12px;
  display: flex; align-items: center; justify-content: center; transition: all 0.15s;
}
.nav-arr:hover:not(:disabled) { background: var(--accent-light); color: var(--accent); }
.nav-arr:disabled { opacity: 0.3; cursor: not-allowed; }
.nav-dots { display: flex; gap: 5px; }
.nav-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--border); cursor: pointer; transition: all 0.2s; }
.nav-dot:hover { background: var(--text-muted); transform: scale(1.3); }
.nav-dot.active { background: var(--accent); box-shadow: 0 0 6px rgba(99,102,241,.4); width: 20px; border-radius: 99px; }
.nav-dot.has-content { border: 2px solid var(--border-hover); }
.nav-info { font-size: 10px; font-weight: 700; color: var(--text-muted); white-space: nowrap; }

/* Zoom badge */
.zoom-badge {
  position: fixed; bottom: 80px; right: 24px;
  background: var(--bg-panel); border: 1px solid var(--border); border-radius: 8px;
  padding: 5px 10px; font-size: 12px; font-weight: 700; color: var(--text-primary);
  box-shadow: var(--shadow-sm); z-index: 50; cursor: pointer;
}
.zoom-badge:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
.zoom-badge small { font-weight: 400; font-size: 9px; opacity: 0.8; margin-left: 3px; }

@media (max-width: 768px) {
  .canvas-area { padding: 20px 10px 80px; }
  .page-nav-bar { bottom: 20px; }
}
</style>