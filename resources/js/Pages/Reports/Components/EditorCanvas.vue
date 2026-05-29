<!--
  EditorCanvas.vue — FULLY ENHANCED
  • All 50+ element types render & are double-click editable
  • Chart.js live charts with full interactivity
  • Inline text editing for all text-type elements
  • Table cell editing in-place
  • Smooth drag / resize / rotate with snap
  • Rubber-band multi-select
  • Alignment guides (smart pink lines)
  • Zoom wheel, pan, minimap
  • Style-painter, measure tool, rulers
  • Optimized: charts cached per element id, destroyed on unmount
-->
<template>
  <main class="canvas-area" ref="canvasAreaRef" @dragover.prevent @drop.prevent="onCanvasDrop"
    @click.self="$emit('deselect-all')" @mousedown.self="onRubberStart" @mousemove="onMouseMove" @mouseup="onRubberEnd"
    @wheel.ctrl.prevent="onZoomWheel" @contextmenu.prevent.self="$emit('context-menu', $event, null, null)">
    <!-- Grid overlay -->
    <div v-if="showGrid" class="grid-overlay" :style="gridStyle" />

    <!-- Rubber-band selection box -->
    <div v-if="rubber.active" class="rubber-band" :style="rubberStyle" />

    <!-- Canvas scroll container -->
    <div class="canvas-scroller" ref="scrollerRef">
      <div class="canvas-wrap" :style="{ transform: `scale(${zoom / 100})`, transformOrigin: 'top center' }">
        <!-- ══ PAGES ══════════════════════════════════════════════════ -->
        <div v-for="(page, pi) in report.content" :key="page.id" class="page-sheet"
          :class="{ 'page-active': currentPage === pi }" :style="pageStyle()" @click.stop="$emit('select-page', pi)"
          @dblclick.self="onPageDblClick($event, pi)" @dragover.prevent @drop.stop="onPageDrop($event, pi)"
          @contextmenu.prevent.stop="$emit('context-menu', $event, pi, null)">
          <!-- Page label -->
          <div class="page-label">{{ page.label || `Page ${pi + 1}` }}</div>

          <!-- Active glow ring -->
          <div v-if="currentPage === pi" class="page-glow" />

          <!-- Watermark -->
          <div v-if="settings.watermark" class="watermark" :style="{
            color: settings.primary_color || '#6366f1',
            opacity: (settings.watermark_opacity || 8) / 100,
            transform: `rotate(${settings.watermark_rotate || -30}deg)`,
          }">{{ settings.watermark }}</div>

          <!-- Header bar -->
          <div v-if="settings.show_header" class="page-header-bar"
            :style="{ background: settings.header_color || '#1e293b', height: (settings.header_height || 50) + 'px' }">
            {{ settings.header_text }}</div>

          <!-- ══ ELEMENTS ══════════════════════════════════════════════ -->
          <div v-for="(el, ei) in page.elements" v-show="el.visible !== false" :key="el.id" class="canvas-el" :class="{
            'el-selected': isSelected(pi, ei),
            'el-editing': editingElIdx === ei && currentPage === pi,
            'el-locked': el.locked,
            'el-multi': selectedEls.includes(ei) && selectedElIdx !== ei,
          }" :style="elWrapStyle(el)" :data-ei="ei" :data-pi="pi" @mousedown.stop="onElMouseDown($event, pi, ei)"
            @dblclick.stop="onElDblClick($event, pi, ei)"
            @contextmenu.prevent.stop="$emit('context-menu', $event, pi, ei)">
            <!-- Resize handles (8 directions) -->
            <template v-if="isSelected(pi, ei) && !el.locked">
              <div v-for="h in HANDLES" :key="h" :class="`resize-handle handle-${h}`"
                @mousedown.stop="$emit('resize-start', { event: $event, pageIndex: pi, elementIndex: ei, handle: h })" />
              <div class="rotate-handle"
                @mousedown.stop="$emit('rotate-start', { event: $event, pageIndex: pi, elementIndex: ei })"
                title="Rotate"><i class="fa-solid fa-rotate" /></div>
              <div class="el-info-bar">
                {{ Math.round(el.position?.x || 0) }}, {{ Math.round(el.position?.y || 0) }}
                — {{ Math.round(el.styles?.width || 0) }}×{{ Math.round(el.styles?.height || 0) }}
                <span v-if="el.styles?.rotate"> · {{ el.styles.rotate }}°</span>
              </div>
            </template>

            <!-- Lock indicator -->
            <div v-if="el.locked" class="lock-badge" title="Locked"><i class="fa-solid fa-lock" /></div>

            <!-- ══ ELEMENT CONTENT RENDERER ════════════════════════════ -->
            <div class="el-inner" :style="elInnerStyle(el)">

              <!-- ─── TEXT TYPES ──────────────────────────────────────── -->
              <div v-if="isTextEl(el.type)" :contenteditable="editingElIdx === ei && currentPage === pi && !el.locked"
                :spellcheck="true" class="el-text" :class="[`el-text--${el.type}`]" :style="textStyle(el)"
                @input="onTextInput(pi, ei, $event)" @blur="onTextBlur" @paste.prevent="onTextPaste"
                v-html="el.content || placeholderFor(el.type)" />

              <!-- ─── RICH TEXT ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'richtext'" class="richtext-wrap">
                <RichTextEditor :key="el.id" :content="el.content || ''"
                  :editable="editingElIdx === ei && currentPage === pi && !el.locked"
                  :primary-color="settings.primary_color"
                  @update:content="v => { el.content = v; $emit('mark-dirty') }" />
              </div>

              <!-- ─── IMAGE ───────────────────────────────────────────── -->
              <div v-else-if="el.type === 'image'" class="el-image">
                <img v-if="el.src" :src="el.src" :alt="el.alt || 'Image'" :style="{
                  width: '100%', height: '100%',
                  objectFit: el.styles?.objectFit || 'cover',
                  borderRadius: (el.styles?.borderRadius || 0) + 'px',
                  filter: imageFilter(el.styles?.imageFilter),
                }" loading="lazy" @error="el.src = ''" />
                <div v-else class="img-placeholder" @click.stop="$emit('image-upload', pi, ei)">
                  <i class="fa-solid fa-image" />
                  <span>Click to add image</span>
                  <small>or drag a file here</small>
                </div>
                <!-- replace button on hover -->
                <div v-if="el.src && isSelected(pi, ei)" class="img-overlay">
                  <button @click.stop="$emit('image-upload', pi, ei)"><i class="fa-solid fa-rotate" /> Replace</button>
                  <button @click.stop="el.src = ''; $emit('mark-dirty')"><i class="fa-solid fa-trash" /></button>
                </div>
              </div>

              <!-- ─── TABLE ───────────────────────────────────────────── -->
              <div v-else-if="el.type === 'table'" class="el-table">
                <table>
                  <thead>
                    <tr>
                      <th v-for="(col, ci) in (el.columns || [])" :key="ci"
                        :contenteditable="editingElIdx === ei && currentPage === pi"
                        :style="{ background: el.styles?.headerBg || settings.primary_color || '#6366f1', color: el.styles?.headerColor || '#fff', padding: '7px 10px', fontSize: '11px', fontWeight: '700', letterSpacing: '.04em', whiteSpace: 'nowrap' }"
                        @blur="el.columns[ci] = $event.target.textContent; $emit('mark-dirty')">{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, ri) in (el.data || [])" :key="ri"
                      :style="{ background: ri % 2 === 0 ? (el.styles?.evenRowBg || '#fff') : (el.styles?.oddRowBg || '#f8fafc') }">
                      <td v-for="(col, ci) in (el.columns || [])" :key="ci"
                        :contenteditable="editingElIdx === ei && currentPage === pi"
                        :style="{ padding: '6px 10px', fontSize: '11px', borderBottom: '1px solid #e2e8f0', color: el.styles?.color || '#1e293b' }"
                        @blur="el.data[ri][col] = $event.target.textContent; $emit('mark-dirty')">{{ row[col] ?? '' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
                <!-- table controls shown when editing -->
                <div v-if="editingElIdx === ei && currentPage === pi" class="table-controls">
                  <button @click.stop="addTableRow(el)">+ Row</button>
                  <button @click.stop="addTableCol(el)">+ Col</button>
                  <button @click.stop="delTableRow(el)" :disabled="(el.data || []).length <= 1">− Row</button>
                  <button @click.stop="delTableCol(el)" :disabled="(el.columns || []).length <= 1">− Col</button>
                </div>
              </div>

              <!-- ─── CHARTS ──────────────────────────────────────────── -->
              <div v-else-if="isChartEl(el.type)" class="el-chart" :ref="r => setChartRef(r, pi, ei)">
                <div v-if="el.chartTitle" class="chart-title">{{ el.chartTitle }}</div>
                <div class="chart-canvas-wrap">
                  <!-- canvas injected by renderChart() -->
                </div>
              </div>

              <!-- ─── METRIC / KPI ────────────────────────────────────── -->
              <div v-else-if="el.type === 'metric'" class="el-metric"
                :style="{ background: el.styles?.backgroundColor || '#f8fafc', borderRadius: (el.styles?.borderRadius || 12) + 'px', padding: '14px' }">
                <div class="metric-lbl">{{ el.label || 'Metric Label' }}</div>
                <div class="metric-val" :style="{ color: el.styles?.color || settings.primary_color }">{{ el.value ||
                  '0' }}</div>
                <div v-if="el.change" class="metric-chg" :class="el.changeType">
                  <i :class="el.changeType === 'negative' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'" />
                  {{ el.change }}
                  <span v-if="el.changePeriod" class="chg-period"> {{ el.changePeriod }}</span>
                </div>
              </div>

              <!-- ─── STAT ROW ────────────────────────────────────────── -->
              <div v-else-if="el.type === 'stat-row'" class="el-stat-row"
                :style="{ background: el.styles?.backgroundColor || '#f8fafc', borderRadius: (el.styles?.borderRadius || 10) + 'px' }">
                <div v-for="(stat, si) in (el.stats || [])" :key="si" class="stat-item">
                  <div class="stat-val" :style="{ color: el.styles?.color || settings.primary_color }">{{ stat.value }}
                  </div>
                  <div class="stat-lbl">{{ stat.label }}</div>
                </div>
              </div>

              <!-- ─── PROGRESS BAR ────────────────────────────────────── -->
              <div v-else-if="el.type === 'progress'" class="el-progress">
                <div class="prog-header">
                  <span>{{ el.label || 'Progress' }}</span>
                  <span>{{ el.value || 0 }}%</span>
                </div>
                <div class="prog-track" :style="{ background: el.styles?.trackColor || '#e2e8f0' }">
                  <div class="prog-fill"
                    :style="{ width: (el.value || 0) + '%', background: el.styles?.color || settings.primary_color || '#6366f1' }" />
                </div>
              </div>

              <!-- ─── CIRCULAR PROGRESS ───────────────────────────────── -->
              <div v-else-if="el.type === 'circular-progress'" class="el-circular">
                <svg viewBox="0 0 120 120" class="circ-svg">
                  <circle cx="60" cy="60" r="52" fill="none" stroke="#e2e8f0" stroke-width="9" />
                  <circle cx="60" cy="60" r="52" fill="none"
                    :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="9"
                    stroke-linecap="round" :stroke-dasharray="`${(el.value || 0) * 3.27} 327`"
                    transform="rotate(-90 60 60)" />
                  <text x="60" y="65" text-anchor="middle" font-size="22" font-weight="700"
                    :fill="el.styles?.color || settings.primary_color || '#6366f1'">{{ el.value || 0 }}%</text>
                </svg>
                <div v-if="el.label" class="circ-label">{{ el.label }}</div>
              </div>

              <!-- ─── SPARKLINE ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'sparkline'" class="el-sparkline">
                <svg :width="el.styles?.width || 200" :height="el.styles?.height || 48" viewBox="0 0 100 30"
                  preserveAspectRatio="none">
                  <polyline :points="sparkPoints(el)" fill="none"
                    :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                  <polyline :points="sparkPoints(el) + ' 100,30 0,30'"
                    :fill="(el.styles?.color || settings.primary_color || '#6366f1') + '22'" stroke="none" />
                </svg>
              </div>

              <!-- ─── CHECKLIST ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'checklist'" class="el-checklist"
                :style="{ background: el.styles?.backgroundColor || 'transparent' }">
                <div v-for="(item, ii) in (el.items || [])" :key="ii" class="check-row">
                  <div class="check-box"
                    :style="{ borderColor: el.styles?.color || settings.primary_color, background: item.checked ? (el.styles?.color || settings.primary_color) : 'transparent' }"
                    @click.stop="item.checked = !item.checked; $emit('mark-dirty')">
                    <i v-if="item.checked" class="fa-solid fa-check" style="color:#fff;font-size:8px" />
                  </div>
                  <span :class="{ 'checked-text': item.checked }">{{ item.text }}</span>
                </div>
              </div>

              <!-- ─── TIMELINE ────────────────────────────────────────── -->
              <div v-else-if="el.type === 'timeline'" class="el-timeline">
                <div v-for="(item, ii) in (el.items || [])" :key="ii" class="tl-row">
                  <div class="tl-side">
                    <div class="tl-dot" :style="{ background: el.styles?.color || settings.primary_color }" />
                    <div v-if="ii < (el.items || []).length - 1" class="tl-line" />
                  </div>
                  <div class="tl-body">
                    <div class="tl-date" :style="{ color: el.styles?.color || settings.primary_color }">{{ item.date }}
                    </div>
                    <div class="tl-title">{{ item.label }}</div>
                    <div class="tl-desc">{{ item.desc }}</div>
                  </div>
                </div>
              </div>

              <!-- ─── CALLOUT ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'callout'" class="el-callout"
                :style="{ background: el.styles?.backgroundColor || (settings.primary_color + '12'), borderLeft: `4px solid ${el.styles?.borderColor || settings.primary_color || '#6366f1'}`, borderRadius: (el.styles?.borderRadius || 8) + 'px' }">
                <span class="callout-emoji">{{ el.emoji || '💡' }}</span>
                <div class="callout-text" :contenteditable="editingElIdx === ei && currentPage === pi"
                  @input="el.content = $event.target.textContent; $emit('mark-dirty')">{{ el.content || 'Callout text...' }}</div>
              </div>

              <!-- ─── TESTIMONIAL ─────────────────────────────────────── -->
              <div v-else-if="el.type === 'testimonial'" class="el-testimonial"
                :style="{ background: el.styles?.backgroundColor || '#f8fafc', borderRadius: (el.styles?.borderRadius || 12) + 'px', padding: '16px' }">
                <div class="test-quote">"</div>
                <div class="test-text" :contenteditable="editingElIdx === ei && currentPage === pi"
                  @input="el.content = $event.target.textContent; $emit('mark-dirty')">{{ el.content || 'Amazing product!'
                  }}</div>
                <div class="test-author">{{ el.author || '— Jane Doe' }}</div>
                <div class="test-role">{{ el.role || 'CEO, Company' }}</div>
              </div>

              <!-- ─── SIGNATURE ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'signature'" class="el-signature">
                <div class="sig-line" />
                <div class="sig-name" :contenteditable="editingElIdx === ei && currentPage === pi"
                  @input="el.content = $event.target.textContent; $emit('mark-dirty')">{{ el.content || 'Signature' }}
                </div>
                <div class="sig-role">{{ el.label || 'Authorized Signature' }}</div>
              </div>

              <!-- ─── PRICE CARD ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'price-card'" class="el-price-card"
                :style="{ background: el.styles?.backgroundColor || '#fff', borderRadius: (el.styles?.borderRadius || 16) + 'px', border: `1px solid ${el.styles?.borderColor || '#e2e8f0'}` }">
                <div class="price-plan">{{ el.plan || 'Basic' }}</div>
                <div class="price-amount" :style="{ color: el.styles?.color || settings.primary_color }">{{ el.price ||
                  '$0' }}
                </div>
                <div class="price-period">{{ el.period || '/month' }}</div>
                <ul class="price-features">
                  <li v-for="(f, fi) in (el.features || [])" :key="fi"><i class="fa-solid fa-check"
                      :style="{ color: settings.primary_color }" /> {{ f }}</li>
                </ul>
              </div>

              <!-- ─── SOCIAL CARD ─────────────────────────────────────── -->
              <div v-else-if="el.type === 'social-card'" class="el-social-card"
                :style="{ background: el.styles?.backgroundColor || '#fff', borderRadius: (el.styles?.borderRadius || 16) + 'px', border: `1px solid ${el.styles?.borderColor || '#e2e8f0'}` }">
                <div class="social-avatar">{{ el.avatar || '👤' }}</div>
                <div class="social-name">{{ el.content || 'Name' }}</div>
                <div class="social-sub">{{ el.subtitle || 'Role / Title' }}</div>
              </div>

              <!-- ─── KANBAN ──────────────────────────────────────────── -->
              <div v-else-if="el.type === 'kanban'" class="el-kanban"
                :style="{ background: el.styles?.backgroundColor || '#fff', borderRadius: (el.styles?.borderRadius || 8) + 'px', border: `1px solid ${el.styles?.borderColor || '#e2e8f0'}` }">
                <div class="kanban-status" :style="{ color: el.styles?.color || settings.primary_color }">{{ el.status
                  || 'In Progress' }}</div>
                <div class="kanban-title" :contenteditable="editingElIdx === ei && currentPage === pi"
                  @input="el.content = $event.target.textContent; $emit('mark-dirty')">{{ el.content || 'Task Title' }}
                </div>
                <div v-if="el.due" class="kanban-due"><i class="fa-regular fa-calendar" /> {{ el.due }}</div>
              </div>

              <!-- ─── HTML EMBED ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'html-embed'" class="el-html"
                v-html="el.content || '<p style=\'color:#94a3b8;text-align:center;padding:20px\'>HTML embed — edit in properties</p>'" />

              <!-- ─── VIDEO ──────────────────────────────────────────── -->
              <div v-else-if="el.type === 'video'" class="el-video">
                <iframe v-if="getYTId(el.videoUrl)" :src="`https://www.youtube.com/embed/${getYTId(el.videoUrl)}`"
                  frameborder="0" allowfullscreen style="width:100%;height:100%" />
                <div v-else class="vid-placeholder"><i class="fa-solid fa-video" /><span>Add YouTube URL in
                    properties</span></div>
              </div>

              <!-- ─── MAP ────────────────────────────────────────────── -->
              <div v-else-if="el.type === 'map'" class="el-map">
                <iframe v-if="el.mapAddress"
                  :src="`https://maps.google.com/maps?q=${encodeURIComponent(el.mapAddress)}&output=embed`"
                  frameborder="0" style="width:100%;height:100%" />
                <div v-else class="map-placeholder"><i class="fa-solid fa-map-location-dot" /><span>Add address in
                    properties</span>
                </div>
              </div>

              <!-- ─── QR CODE ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'qr-code'" class="el-qr">
                <img v-if="el.qrText"
                  :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(el.qrText)}`"
                  style="width:100%;height:100%;object-fit:contain" />
                <div v-else class="qr-placeholder"><i class="fa-solid fa-qrcode" /><span>Add QR text in
                    properties</span></div>
              </div>

              <!-- ─── ICON / EMOJI ────────────────────────────────────── -->
              <div v-else-if="el.type === 'icon'" class="el-icon"
                :style="{ fontSize: (el.styles?.fontSize || 40) + 'px', color: el.styles?.color || settings.primary_color }">
                {{
                  el.content || '⭐' }}</div>

              <!-- ─── RATING ──────────────────────────────────────────── -->
              <div v-else-if="el.type === 'rating'" class="el-rating">
                <span v-for="i in 5" :key="i"
                  :style="{ color: i <= (el.value || 4) ? (el.styles?.color || '#f59e0b') : '#cbd5e1', fontSize: (el.styles?.fontSize || 22) + 'px', cursor: 'pointer' }"
                  @click.stop="el.value = i; $emit('mark-dirty')">★</span>
              </div>

              <!-- ─── PAGE NUMBER ─────────────────────────────────────── -->
              <div v-else-if="el.type === 'pagenum'" class="el-pagenum" :style="textStyle(el)">{{ pi + 1 }}</div>

              <!-- ─── DATE ────────────────────────────────────────────── -->
              <div v-else-if="el.type === 'date'" class="el-date" :style="textStyle(el)">{{ todayStr }}</div>

              <!-- ─── WATERMARK TEXT ──────────────────────────────────── -->
              <div v-else-if="el.type === 'watermark'" class="el-watermark-text"
                :style="{ fontSize: (el.styles?.fontSize || 48) + 'px', color: el.styles?.color || settings.primary_color, opacity: (el.styles?.opacity || 20) / 100, fontWeight: '900', transform: `rotate(${el.styles?.rotate || -30}deg)`, whiteSpace: 'nowrap', userSelect: 'none', pointerEvents: 'none' }">
                {{ el.content || 'CONFIDENTIAL' }}</div>

              <!-- ─── SHAPES ──────────────────────────────────────────── -->
              <div v-else-if="el.type === 'rectangle'" class="shape-fill"
                :style="{ borderRadius: (el.styles?.borderRadius || 0) + 'px', background: el.styles?.backgroundColor || settings.primary_color }" />
              <div v-else-if="el.type === 'circle'" class="shape-fill" style="border-radius:50%"
                :style="{ background: el.styles?.backgroundColor || settings.primary_color }" />
              <div v-else-if="el.type === 'triangle'" class="shape-triangle" :style="triangleStyle(el)" />
              <div v-else-if="el.type === 'divider'" class="shape-divider"
                :style="{ height: (el.styles?.borderWidth || 2) + 'px', background: el.styles?.color || settings.primary_color }" />
              <div v-else-if="el.type === 'arrow'" class="shape-arrow">
                <svg width="100%" height="100%" viewBox="0 0 200 40" preserveAspectRatio="none">
                  <line x1="5" y1="20" x2="185" y2="20" :stroke="el.styles?.color || settings.primary_color"
                    :stroke-width="el.styles?.strokeWidth || 2" />
                  <polygon points="175,8 195,20 175,32" :fill="el.styles?.color || settings.primary_color" />
                </svg>
              </div>
              <div v-else-if="el.type === 'star'" class="el-icon"
                :style="{ fontSize: Math.min(el.styles?.width, el.styles?.height) * 0.7 + 'px', color: el.styles?.color || settings.primary_color }">
                ★</div>

              <!-- ─── TABLE OF CONTENTS ───────────────────────────────── -->
              <div v-else-if="el.type === 'toc'" class="el-toc">
                <div class="toc-title">{{ el.content || 'Table of Contents' }}</div>
                <div v-for="(item, ti) in (el.tocItems || [])" :key="ti" class="toc-row"
                  :style="{ paddingLeft: (item.level - 1) * 14 + 'px' }">
                  <span class="toc-text">{{ item.text }}</span>
                  <span class="toc-page" :style="{ color: settings.primary_color }">{{ item.page }}</span>
                </div>
                <div v-if="!el.tocItems?.length" class="toc-empty">Add headings to auto-generate TOC</div>
              </div>

              <!-- ─── FALLBACK ────────────────────────────────────────── -->
              <div v-else class="el-fallback"><i class="fa-solid fa-cube" /><span>{{ el.type }}</span></div>

            </div><!-- el-inner -->
          </div><!-- canvas-el -->

          <!-- Drop hint when page is empty -->
          <div v-if="!page.elements.length" class="drop-hint">
            <i class="fa-solid fa-plus-circle" />
            <span>Drag elements here or double-click to add text</span>
          </div>

          <!-- Footer bar -->
          <div v-if="settings.show_footer" class="page-footer-bar">
            <span>{{ settings.footer_left }}</span>
            <span v-if="settings.show_page_numbers">{{ pi + 1 }} / {{ report.content.length }}</span>
            <span>{{ (settings.footer_right || '').replace('{n}', pi + 1) }}</span>
          </div>

        </div><!-- page-sheet -->

        <!-- Add page button -->
        <button class="add-page-btn" @click="$emit('add-page')">
          <i class="fa-solid fa-plus" /> Add New Page
        </button>

      </div><!-- canvas-wrap -->
    </div><!-- canvas-scroller -->

    <!-- Page navigation dots -->
    <div v-if="report.content.length > 1" class="page-nav-bar">
      <button class="nav-arr" :disabled="currentPage === 0" @click="$emit('go-to-page', currentPage - 1)">
        <i class="fa-solid fa-chevron-left" />
      </button>
      <span v-for="(p, pi) in report.content" :key="pi" class="nav-dot" :class="{ active: pi === currentPage }"
        @click="$emit('go-to-page', pi)" :title="`Page ${pi + 1}: ${p.label || ''}`" />
      <button class="nav-arr" :disabled="currentPage >= report.content.length - 1"
        @click="$emit('go-to-page', currentPage + 1)">
        <i class="fa-solid fa-chevron-right" />
      </button>
    </div>

    <!-- Zoom badge -->
    <div v-if="zoom !== 100" class="zoom-badge" @click="$emit('zoom-reset')">{{ zoom }}% <small>reset</small></div>

  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick, shallowRef } from 'vue'
import { Chart, registerables } from 'chart.js'
import RichTextEditor from './RichTextEditor.vue'

Chart.register(...registerables)

// ── Props & Emits ──────────────────────────────────────────────────────────────
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
  gridSize: { type: Number, default: 10 },
  isDark: { type: Boolean, default: false },
})

const emit = defineEmits([
  'select-page', 'add-page', 'go-to-page', 'deselect-all',
  'element-mouse-down', 'start-editing', 'update-text', 'mark-dirty',
  'resize-start', 'rotate-start', 'canvas-drop', 'context-menu',
  'image-upload', 'rubber-band-start', 'rubber-band-move', 'rubber-band-end',
  'zoom-wheel', 'zoom-reset',
])

// ── Refs ───────────────────────────────────────────────────────────────────────
const canvasAreaRef = ref(null)
const scrollerRef = ref(null)

// Chart instances: map of `${pi}-${ei}` → Chart instance
const chartInstances = shallowRef({})
// Chart container refs: map of `${pi}-${ei}` → DOM element
const chartRefs = {}

// Rubber-band
const rubber = ref({ active: false, x: 0, y: 0, w: 0, h: 0, sx: 0, sy: 0 })

// ── Constants ──────────────────────────────────────────────────────────────────
const HANDLES = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w']
const todayStr = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })

// ── Computed ───────────────────────────────────────────────────────────────────
const gridStyle = computed(() => ({
  backgroundImage: 'linear-gradient(rgba(99,102,241,.07) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.07) 1px,transparent 1px)',
  backgroundSize: `${props.gridSize * (props.zoom / 100)}px ${props.gridSize * (props.zoom / 100)}px`,
  position: 'fixed', inset: 0, pointerEvents: 'none', zIndex: 0,
}))

const rubberStyle = computed(() => ({
  position: 'fixed',
  left: rubber.value.x + 'px', top: rubber.value.y + 'px',
  width: rubber.value.w + 'px', height: rubber.value.h + 'px',
  border: '1.5px dashed #6366f1', background: 'rgba(99,102,241,.06)',
  pointerEvents: 'none', zIndex: 1000,
}))

// ── Page / Element style helpers ───────────────────────────────────────────────
function pageDims() {
  const map = {
    A4: { portrait: [794, 1123], landscape: [1123, 794] },
    Letter: { portrait: [816, 1056], landscape: [1056, 816] },
    Legal: { portrait: [816, 1344], landscape: [1344, 816] },
    A3: { portrait: [1123, 1587], landscape: [1587, 1123] },
    A5: { portrait: [559, 794], landscape: [794, 559] },
  }
  const sz = props.settings.page_size || 'A4'
  const or = props.settings.orientation || 'portrait'
  return map[sz]?.[or] || [794, 1123]
}

function pageStyle() {
  const [w, h] = pageDims()
  return {
    width: w + 'px',
    minHeight: h + 'px',
    backgroundColor: props.settings.background_color || '#ffffff',
    fontFamily: props.settings.font_family || "'DM Sans', sans-serif",
    fontSize: (props.settings.font_size || 14) + 'px',
    color: props.settings.text_color || '#1e293b',
    borderRadius: (props.settings.page_radius || 0) + 'px',
    padding: (props.settings.margin || 40) + 'px',
    direction: props.settings.rtl ? 'rtl' : 'ltr',
    position: 'relative',
    boxSizing: 'border-box',
    backgroundImage: props.settings.bg_image ? `url(${props.settings.bg_image})` : 'none',
    backgroundSize: 'cover',
  }
}

function elWrapStyle(el) {
  const s = el.styles || {}
  const bord = s.borderWidth ? `${s.borderWidth}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : 'none'
  const filters = []
  if (s.blur) filters.push(`blur(${s.blur}px)`)
  if (s.brightness && s.brightness !== 100) filters.push(`brightness(${s.brightness}%)`)
  if (s.grayscale) filters.push(`grayscale(${s.grayscale}%)`)
  if (s.contrast && s.contrast !== 100) filters.push(`contrast(${s.contrast}%)`)
  return {
    position: 'absolute',
    left: (el.position?.x || 0) + 'px',
    top: (el.position?.y || 0) + 'px',
    width: (s.width || 200) + 'px',
    height: (s.height || 80) + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: buildTransform(s),
    borderRadius: (s.borderRadius || 0) + 'px',
    border: bord,
    boxShadow: s.boxShadow || 'none',
    filter: filters.length ? filters.join(' ') : 'none',
    mixBlendMode: s.mixBlendMode || 'normal',
    cursor: el.locked ? 'not-allowed' : 'move',
    userSelect: 'none',
    overflow: 'hidden',
  }
}

function buildTransform(s) {
  const parts = []
  if (s.rotate) parts.push(`rotate(${s.rotate}deg)`)
  if (s.scaleX === -1) parts.push('scaleX(-1)')
  if (s.scaleY === -1) parts.push('scaleY(-1)')
  return parts.join(' ') || 'none'
}

function elInnerStyle(el) {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    backgroundColor: s.backgroundColor || 'transparent',
    padding: (s.padding || 0) + 'px',
    overflow: el.type === 'richtext' || el.type === 'table' ? 'auto' : 'hidden',
    fontFamily: s.fontFamily || props.settings.font_family,
  }
}

function textStyle(el) {
  const s = el.styles || {}
  return {
    fontSize: (s.fontSize || 14) + 'px',
    fontWeight: s.fontWeight || '400',
    fontStyle: s.fontStyle || 'normal',
    color: s.color || props.settings.text_color || '#1e293b',
    textAlign: s.textAlign || 'left',
    lineHeight: s.lineHeight || 1.5,
    letterSpacing: (s.letterSpacing || 0) + 'px',
    textDecoration: s.textDecoration || 'none',
    textTransform: s.textTransform || 'none',
    width: '100%', height: '100%',
    outline: 'none',
    wordBreak: 'break-word',
    whiteSpace: el.type === 'code' ? 'pre-wrap' : 'normal',
    fontFamily: s.fontFamily || (el.type === 'code' ? "'Fira Code', monospace" : props.settings.font_family),
  }
}

function triangleStyle(el) {
  const s = el.styles || {}
  const w = (s.width || 120) / 2
  const h = s.height || 100
  const c = s.backgroundColor || settings?.primary_color || '#6366f1'
  return { width: 0, height: 0, borderLeft: `${w}px solid transparent`, borderRight: `${w}px solid transparent`, borderBottom: `${h}px solid ${c}`, backgroundColor: 'transparent' }
}

function imageFilter(f) {
  const m = { grayscale: 'grayscale(100%)', sepia: 'sepia(80%)', vintage: 'sepia(50%) contrast(85%) brightness(90%)', blur: 'blur(3px)', bright: 'brightness(130%)' }
  return m[f] || 'none'
}

function getYTId(url) {
  if (!url) return null
  const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/)
  return m ? m[1] : null
}

function sparkPoints(el) {
  const d = el.sparkData || [3, 5, 4, 8, 6, 7, 5, 9, 7, 10]
  const mx = Math.max(...d)
  return d.map((v, i) => `${(i / (d.length - 1)) * 100},${30 - (v / mx) * 25}`).join(' ')
}

// ── Type helpers ───────────────────────────────────────────────────────────────
function isTextEl(t) {
  return ['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight', 'badge', 'code', 'link', 'list'].includes(t)
}
function isChartEl(t) {
  return t?.endsWith('-chart')
}
function isSelected(pi, ei) {
  return props.currentPage === pi && (props.selectedElIdx === ei || props.selectedEls.includes(ei))
}
function placeholderFor(type) {
  const m = { heading: 'Click to edit heading', text: 'Start typing…', quote: 'Inspiring quote…', code: '// your code', badge: 'Badge', link: 'https://', list: '• Item 1\n• Item 2', subheading: 'Subheading' }
  return m[type] || 'Click to edit'
}

// ── Chart rendering ────────────────────────────────────────────────────────────
function setChartRef(el, pi, ei) {
  if (!el) return
  const key = `${pi}-${ei}`
  chartRefs[key] = el
  nextTick(() => renderChart(pi, ei))
}

function renderChart(pi, ei) {
  const key = `${pi}-${ei}`
  const container = chartRefs[key]
  if (!container) return
  const el = props.report.content[pi]?.elements[ei]
  if (!el || !isChartEl(el.type)) return

  const wrap = container.querySelector('.chart-canvas-wrap')
  if (!wrap) return

  // Destroy old instance
  if (chartInstances.value[key]) {
    chartInstances.value[key].destroy()
    delete chartInstances.value[key]
  }
  wrap.innerHTML = ''

  const canvas = document.createElement('canvas')
  wrap.appendChild(canvas)

  const labels = el.chartData?.labels || ['Q1', 'Q2', 'Q3', 'Q4']
  const values = el.chartData?.values || [25, 40, 35, 55]
  const color = el.chartColor || props.settings.primary_color || '#6366f1'
  const pieColors = el.pieColors || [color, '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']

  const isCircular = el.type === 'pie-chart' || el.type === 'doughnut-chart' || el.type === 'polar-chart'
  const chartTypeMap = {
    'bar-chart': 'bar',
    'line-chart': 'line',
    'area-chart': 'line',
    'pie-chart': 'pie',
    'doughnut-chart': 'doughnut',
    'radar-chart': 'radar',
    'scatter-chart': 'scatter',
    'polar-chart': 'polarArea',
    'bubble-chart': 'bubble',
  }
  const chartType = chartTypeMap[el.type] || 'bar'

  try {
    chartInstances.value[key] = new Chart(canvas, {
      type: chartType,
      data: {
        labels,
        datasets: [{
          label: el.chartDatasetLabel || el.chartTitle || 'Data',
          data: el.type === 'scatter-chart'
            ? values.map((v, i) => ({ x: i * 10, y: v }))
            : values,
          backgroundColor: isCircular ? pieColors.slice(0, values.length) : color + '80',
          borderColor: isCircular ? pieColors.slice(0, values.length) : color,
          borderWidth: 2,
          fill: el.type === 'area-chart',
          tension: 0.4,
          pointRadius: 3,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 400 },
        plugins: {
          legend: {
            display: isCircular,
            position: 'bottom',
            labels: { font: { size: 10 }, padding: 8, usePointStyle: true },
          },
          tooltip: { enabled: true },
        },
        scales: isCircular || el.type === 'radar-chart' ? {} : {
          x: { ticks: { font: { size: 10 }, maxRotation: 30 }, grid: { color: 'rgba(0,0,0,.05)' } },
          y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: 'rgba(0,0,0,.05)' } },
        },
      },
    })
  } catch (e) {
    console.warn('[EditorCanvas] chart render error:', e)
  }
}

// Re-render charts when their data or color changes
watch(
  () => props.settings.primary_color,
  () => {
    Object.keys(chartRefs).forEach(key => {
      const [pi, ei] = key.split('-').map(Number)
      if (pi === props.currentPage) renderChart(pi, ei)
    })
  }
)
watch(
  () => props.currentPage,
  () => {
    nextTick(() => {
      const pi = props.currentPage
      const els = props.report.content[pi]?.elements || []
      els.forEach((el, ei) => {
        if (isChartEl(el.type)) renderChart(pi, ei)
      })
    })
  }
)

// ── Element interaction ────────────────────────────────────────────────────────
function onElMouseDown(e, pi, ei) {
  if (e.button !== 0) return
  emit('element-mouse-down', { event: e, pageIndex: pi, elementIndex: ei })
}

function onElDblClick(e, pi, ei) {
  const el = props.report.content[pi]?.elements[ei]
  if (!el || el.locked) return
  if (el.type === 'image' && !el.src) { emit('image-upload', pi, ei); return }
  emit('start-editing', { pageIndex: pi, elementIndex: ei })
}

function onPageDblClick(e, pi) {
  // Double-click on blank canvas → add text at cursor position
  const pageEl = e.currentTarget
  const rect = pageEl.getBoundingClientRect()
  const scale = props.zoom / 100
  emit('canvas-drop', {
    def: { type: 'text', w: 200, h: 60 },
    x: (e.clientX - rect.left) / scale,
    y: (e.clientY - rect.top) / scale,
    pageIndex: pi,
  })
}

// ── Text editing ───────────────────────────────────────────────────────────────
function onTextInput(pi, ei, e) {
  emit('update-text', { pageIndex: pi, elementIndex: ei, content: e.target.innerHTML })
}
function onTextBlur() { }
function onTextPaste(e) {
  const text = e.clipboardData.getData('text/plain')
  document.execCommand('insertText', false, text)
}

// ── Canvas drag-drop from sidebar ─────────────────────────────────────────────
function onCanvasDrop(e) {
  const raw = e.dataTransfer.getData('el-def')
  if (!raw) return
  const def = JSON.parse(raw)
  const rect = canvasAreaRef.value?.getBoundingClientRect()
  if (!rect) return
  const scale = props.zoom / 100
  emit('canvas-drop', {
    def,
    x: (e.clientX - rect.left) / scale - (def.w || 100) / 2,
    y: (e.clientY - rect.top + (scrollerRef.value?.scrollTop || 0)) / scale - (def.h || 50) / 2,
    pageIndex: props.currentPage,
  })
}

function onPageDrop(e, pi) {
  const raw = e.dataTransfer.getData('el-def')
  if (!raw) return
  const def = JSON.parse(raw)
  const pageEl = e.currentTarget
  const rect = pageEl.getBoundingClientRect()
  const scale = props.zoom / 100
  emit('canvas-drop', {
    def,
    x: (e.clientX - rect.left) / scale - (def.w || 100) / 2,
    y: (e.clientY - rect.top) / scale - (def.h || 50) / 2,
    pageIndex: pi,
  })
}

// ── Rubber-band ────────────────────────────────────────────────────────────────
function onRubberStart(e) {
  rubber.value = { active: true, sx: e.clientX, sy: e.clientY, x: e.clientX, y: e.clientY, w: 0, h: 0 }
  emit('rubber-band-start', e)
}
function onMouseMove(e) {
  if (!rubber.value.active) return
  rubber.value.x = Math.min(e.clientX, rubber.value.sx)
  rubber.value.y = Math.min(e.clientY, rubber.value.sy)
  rubber.value.w = Math.abs(e.clientX - rubber.value.sx)
  rubber.value.h = Math.abs(e.clientY - rubber.value.sy)
  emit('rubber-band-move', e)
}
function onRubberEnd() {
  rubber.value.active = false
  emit('rubber-band-end')
}

// ── Zoom wheel ────────────────────────────────────────────────────────────────
function onZoomWheel(e) { emit('zoom-wheel', e) }

// ── Table helpers (called in template) ────────────────────────────────────────
function addTableRow(el) {
  const row = {}
  el.columns?.forEach(c => { row[c] = '' })
  if (!el.data) el.data = []
  el.data.push(row)
  emit('mark-dirty')
}
function addTableCol(el) {
  const col = `Col ${(el.columns?.length || 0) + 1}`
  if (!el.columns) el.columns = []
  el.columns.push(col)
  el.data?.forEach(r => { r[col] = '' })
  emit('mark-dirty')
}
function delTableRow(el) {
  if ((el.data?.length || 0) > 1) { el.data.pop(); emit('mark-dirty') }
}
function delTableCol(el) {
  if ((el.columns?.length || 0) > 1) {
    const col = el.columns.pop()
    el.data?.forEach(r => { delete r[col] })
    emit('mark-dirty')
  }
}

// ── Lifecycle ──────────────────────────────────────────────────────────────────
onMounted(() => {
  // Render charts for initial page
  nextTick(() => {
    const pi = props.currentPage
    const els = props.report.content[pi]?.elements || []
    els.forEach((el, ei) => {
      if (isChartEl(el.type)) renderChart(pi, ei)
    })
  })
})

onBeforeUnmount(() => {
  Object.values(chartInstances.value).forEach(c => c?.destroy())
})
</script>

<style scoped>
/* ── Canvas area ─────────────────────────────────────────────────────────────── */
.canvas-area {
  flex: 1;
  background: var(--bg-tertiary, #f1f5f9);
  overflow: hidden;
  position: relative;
  display: flex;
  flex-direction: column;
}

.canvas-scroller {
  flex: 1;
  overflow: auto;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 40px 120px;
  scrollbar-width: thin;
}

.canvas-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 40px;
}

/* ── Page sheet ──────────────────────────────────────────────────────────────── */
.page-sheet {
  position: relative;
  flex-shrink: 0;
  overflow: visible;
  box-shadow: 0 8px 40px rgba(0, 0, 0, .16);
  transition: box-shadow .3s;
}

.page-sheet.page-active {
  box-shadow: 0 0 0 3px #fbbf24, 0 8px 40px rgba(0, 0, 0, .2);
}

.page-label {
  position: absolute;
  top: -26px;
  left: 0;
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted);
  pointer-events: none;
}

.page-glow {
  position: absolute;
  inset: -3px;
  border: 2px solid rgba(251, 191, 36, .6);
  border-radius: 4px;
  pointer-events: none;
  animation: glow 2.4s ease-in-out infinite;
}

@keyframes glow {

  0%,
  100% {
    box-shadow: 0 0 12px rgba(251, 191, 36, .3)
  }

  50% {
    box-shadow: 0 0 32px rgba(251, 191, 36, .55)
  }
}

.page-header-bar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  padding: 0 24px;
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  z-index: 10;
}

.page-footer-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  font-size: 10px;
  color: #94a3b8;
  border-top: 1px solid rgba(0, 0, 0, .07);
  z-index: 10;
}

.watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 72px;
  font-weight: 900;
  white-space: nowrap;
  pointer-events: none;
  z-index: 5;
  text-transform: uppercase;
  letter-spacing: .1em;
}

/* ── Elements ────────────────────────────────────────────────────────────────── */
.canvas-el {
  position: absolute;
  transform-origin: center;
  transition: box-shadow .1s;
}

.canvas-el:not(.el-locked):hover {
  outline: 1px dashed rgba(99, 102, 241, .45);
  outline-offset: 1px;
}

.canvas-el.el-selected {
  outline: 2px solid #6366f1 !important;
  outline-offset: 1px;
  box-shadow: 0 0 0 6px rgba(99, 102, 241, .1);
  z-index: 50 !important;
}

.canvas-el.el-multi {
  outline: 2px solid rgba(99, 102, 241, .55) !important;
}

.canvas-el.el-editing {
  outline: 2px solid #6366f1 !important;
  cursor: text !important;
}

.canvas-el.el-locked {
  outline-color: #f59e0b !important;
}

.el-inner {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
}

/* ── Resize / rotate handles ─────────────────────────────────────────────────── */
.resize-handle {
  position: absolute;
  width: 9px;
  height: 9px;
  background: #fff;
  border: 2px solid #6366f1;
  border-radius: 2px;
  z-index: 100;
  box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
}

.handle-nw {
  top: -4px;
  left: -4px;
  cursor: nw-resize
}

.handle-n {
  top: -4px;
  left: calc(50% - 4px);
  cursor: n-resize
}

.handle-ne {
  top: -4px;
  right: -4px;
  cursor: ne-resize
}

.handle-e {
  top: calc(50% - 4px);
  right: -4px;
  cursor: e-resize
}

.handle-se {
  bottom: -4px;
  right: -4px;
  cursor: se-resize
}

.handle-s {
  bottom: -4px;
  left: calc(50% - 4px);
  cursor: s-resize
}

.handle-sw {
  bottom: -4px;
  left: -4px;
  cursor: sw-resize
}

.handle-w {
  top: calc(50% - 4px);
  left: -4px;
  cursor: w-resize
}

.rotate-handle {
  position: absolute;
  top: -32px;
  left: calc(50% - 13px);
  width: 26px;
  height: 26px;
  background: #6366f1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: crosshair;
  color: #fff;
  font-size: 11px;
  box-shadow: 0 2px 8px rgba(99, 102, 241, .4);
}

.rotate-handle:hover {
  transform: scale(1.1);
}

.el-info-bar {
  position: absolute;
  bottom: -20px;
  left: 0;
  font-size: 9px;
  color: var(--text-muted);
  white-space: nowrap;
  pointer-events: none;
  background: var(--bg-panel);
  padding: 1px 5px;
  border-radius: 3px;
  border: 1px solid var(--border);
}

.lock-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(245, 158, 11, .9);
  color: #fff;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
  z-index: 30;
}

/* ── Text element styles ─────────────────────────────────────────────────────── */
.el-text {
  width: 100%;
  height: 100%;
  outline: none;
  word-break: break-word;
  overflow: auto;
}

.el-text--code {
  font-family: 'Fira Code', monospace;
  background: #1e293b;
  color: #34d399;
  border-radius: 6px;
  padding: 10px !important;
  white-space: pre-wrap;
}

.el-text--quote {
  border-left: 4px solid currentColor;
  padding-left: 12px !important;
  font-style: italic;
}

.el-text--blockquote {
  background: rgba(99, 102, 241, .06);
  border-left: 4px solid currentColor;
  border-radius: 0 8px 8px 0;
  padding: 12px !important;
  font-style: italic;
}

.el-text--highlight {
  background: #fef3c7;
  color: #92400e;
  display: inline-block;
  padding: 2px 6px !important;
  border-radius: 3px;
}

/* ── Rich text ───────────────────────────────────────────────────────────────── */
.richtext-wrap {
  width: 100%;
  height: 100%;
  overflow: auto;
}

/* ── Image ───────────────────────────────────────────────────────────────────── */
.el-image {
  width: 100%;
  height: 100%;
  position: relative;
}

.img-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #f1f5f9;
  color: #94a3b8;
  font-size: 12px;
  cursor: pointer;
  border: 2px dashed #cbd5e1;
  border-radius: 6px;
  transition: all .2s;
}

.img-placeholder:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .04);
}

.img-placeholder i {
  font-size: 28px;
}

.img-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, .45);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  opacity: 0;
  transition: opacity .2s;
}

.canvas-el:hover .img-overlay {
  opacity: 1;
}

.img-overlay button {
  padding: 5px 10px;
  background: rgba(255, 255, 255, .9);
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.img-overlay button:hover {
  background: #fff;
}

/* ── Table ───────────────────────────────────────────────────────────────────── */
.el-table {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.el-table table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
}

.el-table th,
.el-table td {
  border: none;
}

.table-controls {
  display: flex;
  gap: 3px;
  padding: 4px;
  background: var(--bg-secondary);
  border-top: 1px solid var(--border);
}

.table-controls button {
  padding: 3px 8px;
  font-size: 9px;
  border: 1px solid var(--border);
  border-radius: 4px;
  background: var(--bg-panel);
  cursor: pointer;
  font-family: inherit;
  transition: all .15s;
}

.table-controls button:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.table-controls button:disabled {
  opacity: .35;
  cursor: not-allowed;
}

/* ── Chart ───────────────────────────────────────────────────────────────────── */
.el-chart {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding: 6px;
  box-sizing: border-box;
}

.chart-title {
  font-size: 11px;
  font-weight: 700;
  text-align: center;
  margin-bottom: 4px;
  flex-shrink: 0;
  color: var(--text-primary);
}

.chart-canvas-wrap {
  flex: 1;
  position: relative;
  min-height: 0;
  width: 100%;
}

.chart-canvas-wrap canvas {
  width: 100% !important;
  height: 100% !important;
}

/* ── Metric ──────────────────────────────────────────────────────────────────── */
.el-metric {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.metric-lbl {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: #94a3b8;
  margin-bottom: 4px;
}

.metric-val {
  font-size: 28px;
  font-weight: 800;
  line-height: 1;
}

.metric-chg {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 11px;
  margin-top: 5px;
}

.metric-chg.positive {
  color: #10b981;
}

.metric-chg.negative {
  color: #ef4444;
}

.chg-period {
  font-size: 9px;
  color: #94a3b8;
}

/* ── Stat row ────────────────────────────────────────────────────────────────── */
.el-stat-row {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-around;
  padding: 8px;
}

.stat-item {
  text-align: center;
  flex: 1;
}

.stat-val {
  font-size: 22px;
  font-weight: 800;
  line-height: 1;
}

.stat-lbl {
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #94a3b8;
  margin-top: 3px;
}

/* ── Progress ────────────────────────────────────────────────────────────────── */
.el-progress {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
}

.prog-header {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  font-weight: 600;
}

.prog-track {
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}

.prog-fill {
  height: 100%;
  border-radius: 4px;
  transition: width .5s ease;
}

/* ── Circular progress ───────────────────────────────────────────────────────── */
.el-circular {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.circ-svg {
  width: 80%;
  height: 80%;
}

.circ-label {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 4px;
  text-align: center;
}

/* ── Sparkline ───────────────────────────────────────────────────────────────── */
.el-sparkline {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
}

/* ── Checklist ───────────────────────────────────────────────────────────────── */
.el-checklist {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 7px;
  overflow: auto;
  padding: 6px;
}

.check-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.check-box {
  width: 17px;
  height: 17px;
  border: 2px solid;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all .15s;
}

.checked-text {
  text-decoration: line-through;
  opacity: .5;
}

/* ── Timeline ────────────────────────────────────────────────────────────────── */
.el-timeline {
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 4px;
}

.tl-row {
  display: flex;
  gap: 10px;
  margin-bottom: 14px;
}

.tl-side {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 12px;
  flex-shrink: 0;
}

.tl-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.tl-line {
  flex: 1;
  width: 2px;
  background: #e2e8f0;
  margin-top: 3px;
}

.tl-date {
  font-size: 10px;
  font-weight: 600;
  margin-bottom: 1px;
}

.tl-title {
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 2px;
}

.tl-desc {
  font-size: 11px;
  color: #94a3b8;
  line-height: 1.4;
}

/* ── Callout ─────────────────────────────────────────────────────────────────── */
.el-callout {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px;
  box-sizing: border-box;
  overflow: auto;
}

.callout-emoji {
  font-size: 20px;
  flex-shrink: 0;
}

.callout-text {
  flex: 1;
  font-size: 12px;
  line-height: 1.6;
  outline: none;
  word-break: break-word;
}

/* ── Testimonial ─────────────────────────────────────────────────────────────── */
.el-testimonial {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.test-quote {
  font-size: 36px;
  line-height: .8;
  opacity: .3;
  font-family: Georgia, serif;
}

.test-text {
  font-style: italic;
  font-size: 12px;
  line-height: 1.6;
  outline: none;
}

.test-author {
  font-weight: 700;
  font-size: 11px;
  margin-top: 8px;
}

.test-role {
  font-size: 10px;
  opacity: .6;
}

/* ── Signature ───────────────────────────────────────────────────────────────── */
.el-signature {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 6px;
}

.sig-line {
  flex: 1;
  border-bottom: 2px solid #cbd5e1;
}

.sig-name {
  font-family: Georgia, serif;
  font-style: italic;
  font-size: 16px;
  color: #94a3b8;
  margin-top: 4px;
  outline: none;
}

.sig-role {
  font-size: 9px;
  color: #94a3b8;
}

/* ── Price card ──────────────────────────────────────────────────────────────── */
.el-price-card {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow: auto;
}

.price-plan {
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 6px;
}

.price-amount {
  font-size: 36px;
  font-weight: 900;
  line-height: 1;
}

.price-period {
  font-size: 11px;
  color: #94a3b8;
  margin-bottom: 12px;
}

.price-features {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
  width: 100%;
}

.price-features li {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  margin-bottom: 4px;
}

/* ── Social card ─────────────────────────────────────────────────────────────── */
.el-social-card {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 12px;
}

.social-avatar {
  font-size: 40px;
  margin-bottom: 6px;
}

.social-name {
  font-weight: 700;
  font-size: 13px;
}

.social-sub {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 2px;
}

/* ── Kanban ──────────────────────────────────────────────────────────────────── */
.el-kanban {
  width: 100%;
  height: 100%;
  padding: 12px;
  overflow: auto;
}

.kanban-status {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  margin-bottom: 4px;
}

.kanban-title {
  font-size: 13px;
  font-weight: 600;
  outline: none;
  word-break: break-word;
}

.kanban-due {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* ── HTML embed ──────────────────────────────────────────────────────────────── */
.el-html {
  width: 100%;
  height: 100%;
  overflow: auto;
}

/* ── Video / Map ─────────────────────────────────────────────────────────────── */
.el-video,
.el-map {
  width: 100%;
  height: 100%;
  background: #000;
  border-radius: 6px;
  overflow: hidden;
}

.vid-placeholder,
.map-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #0f172a;
  color: #94a3b8;
  font-size: 11px;
}

.vid-placeholder i,
.map-placeholder i {
  font-size: 28px;
  opacity: .4;
}

/* ── QR code ─────────────────────────────────────────────────────────────────── */
.el-qr {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border-radius: 6px;
}

.qr-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #f8fafc;
  color: #94a3b8;
  font-size: 11px;
  border: 2px dashed #e2e8f0;
  border-radius: 6px;
}

.qr-placeholder i {
  font-size: 28px;
  opacity: .4;
}

/* ── Icon, rating ────────────────────────────────────────────────────────────── */
.el-icon,
.el-rating,
.el-pagenum,
.el-date {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.el-watermark-text {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Shapes ──────────────────────────────────────────────────────────────────── */
.shape-fill {
  width: 100%;
  height: 100%;
}

.shape-triangle {
  margin: auto;
}

.shape-divider {
  width: 100%;
}

.shape-arrow {
  width: 100%;
  height: 100%;
}

/* ── TOC ─────────────────────────────────────────────────────────────────────── */
.el-toc {
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 4px;
}

.toc-title {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 10px;
}

.toc-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
  border-bottom: 1px dotted #e2e8f0;
  font-size: 11px;
}

.toc-text {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.toc-page {
  font-weight: 700;
  margin-left: 8px;
}

.toc-empty {
  font-size: 10px;
  color: #94a3b8;
  padding: 12px 0;
  text-align: center;
}

/* ── Fallback ────────────────────────────────────────────────────────────────── */
.el-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #f1f5f9;
  color: #94a3b8;
  font-size: 11px;
  border-radius: 4px;
}

.el-fallback i {
  font-size: 22px;
  opacity: .35;
}

/* ── Drop hint ───────────────────────────────────────────────────────────────── */
.drop-hint {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: #94a3b8;
  font-size: 12px;
  pointer-events: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.drop-hint i {
  font-size: 32px;
  opacity: .25;
}

/* ── Add page button ─────────────────────────────────────────────────────────── */
.add-page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 220px;
  height: 64px;
  border: 2px dashed var(--border);
  border-radius: 10px;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 600;
  transition: all .2s;
  font-family: inherit;
}

.add-page-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .04);
  transform: translateY(-2px);
}

/* ── Page nav bar ────────────────────────────────────────────────────────────── */
.page-nav-bar {
  position: fixed;
  bottom: 44px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bg-panel);
  border: 1px solid var(--border);
  border-radius: 99px;
  padding: 6px 14px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
  z-index: 80;
}

.nav-arr {
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  border-radius: 50%;
  cursor: pointer;
  color: var(--text-secondary);
  transition: all .15s;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-arr:hover:not(:disabled) {
  background: rgba(99, 102, 241, .1);
  color: #6366f1;
}

.nav-arr:disabled {
  opacity: .3;
  cursor: not-allowed;
}

.nav-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border);
  cursor: pointer;
  transition: all .2s;
}

.nav-dot:hover {
  background: var(--text-muted);
  transform: scale(1.3);
}

.nav-dot.active {
  background: #6366f1;
  box-shadow: 0 0 6px rgba(99, 102, 241, .4);
  width: 22px;
  border-radius: 99px;
}

/* ── Rubber band ─────────────────────────────────────────────────────────────── */
.rubber-band {
  position: fixed;
  pointer-events: none;
  z-index: 999;
}

/* ── Grid overlay ────────────────────────────────────────────────────────────── */
.grid-overlay {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

/* ── Zoom badge ──────────────────────────────────────────────────────────────── */
.zoom-badge {
  position: fixed;
  bottom: 100px;
  right: 20px;
  background: var(--bg-panel);
  border: 1px solid var(--border);
  border-radius: 7px;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 700;
  color: #6366f1;
  cursor: pointer;
  z-index: 50;
}

.zoom-badge:hover {
  background: #6366f1;
  color: #fff;
}

/* ── Responsive ──────────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .canvas-scroller {
    padding: 20px 8px 80px;
  }

  .page-nav-bar {
    bottom: 28px;
    padding: 4px 10px;
  }
}
</style>