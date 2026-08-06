<!--
  EditorCanvas.vue — Production-Ready Canvas (Part 2 enhancements)
  NEW in this version:
  ┌─────────────────────────────────────────────────────────────────┐
  │ • Edge-of-page auto-scroll while dragging elements (Canva-style)│
  │   — detects mouse proximity to canvas-area edges during drag     │
  │   — smooth RAF-based scrolling, stops immediately on mouseup     │
  │ • Per-page action buttons overlay on each page:                  │
  │   Move Up · Move Down · Add Before · Add After · Duplicate · Del │
  │ • Ctrl+wheel zoom uses addEventListener({passive:false}) so      │
  │   preventDefault() actually works — no more browser zoom fights  │
  │ • All existing 50+ element types, resize/rotate/rubber-band,     │
  │   chart.js rendering, context menus etc. preserved intact        │
  └─────────────────────────────────────────────────────────────────┘
-->
<template>
  <main ref="canvasAreaRef" class="canvas-area" :class="{ 'is-dark': isDark, 'measure-mode': measureMode }"
    @dragover.prevent="onDragOver" @drop.prevent="onDrop" @click.self="deselectAll" @mousedown.self="startRubberBand"
    @contextmenu.prevent="onCanvasCtx">
    <!-- Grid overlay -->
    <div v-if="showGrid" class="grid-overlay" aria-hidden="true" />

    <!-- Rubber-band selection -->
    <div v-if="rb.active" class="rubber-band" :style="rbStyle" aria-hidden="true" />

    <!-- Canvas container (scaled) -->
    <div class="canvas-container" :style="{ transform: `scale(${zoom / 100})`, transformOrigin: 'top center' }">

      <!-- ══ PAGES ══════════════════════════════════════════════════════ -->
      <div v-for="(page, pi) in report.content" :key="page.id" class="page-wrapper">
        <!-- Page label above -->
        <div class="page-label-top" aria-label="`Page ${pi + 1}`">
          <span class="page-label-name">{{ page.label || `Page ${pi + 1}` }}</span>
          <span class="page-label-count">{{ (page.elements || []).length }} elements</span>
        </div>

        <!-- ── PER-PAGE ACTION BUTTONS ─────────────────────────────── -->
        <div class="page-actions-bar" @click.stop aria-label="Page actions">
          <!-- Move order -->
          <button class="pab-btn" @click="$emit('move-page-up', pi)" :disabled="pi === 0" title="Move page up"
            aria-label="Move page up"><i class="fa-solid fa-chevron-up" /></button>

          <button class="pab-btn" @click="$emit('move-page-down', pi)" :disabled="pi === report.content.length - 1"
            title="Move page down" aria-label="Move page down"><i class="fa-solid fa-chevron-down" /></button>

          <div class="pab-sep" aria-hidden="true" />

          <!-- Insert -->
          <button class="pab-btn" @click="$emit('add-page-before', pi)" title="Insert page before this one"
            aria-label="Insert page before"><i class="fa-solid fa-arrow-up-from-bracket" /> Before</button>

          <button class="pab-btn" @click="$emit('add-page-after', pi)" title="Insert page after this one"
            aria-label="Insert page after"><i class="fa-solid fa-arrow-down-to-bracket" /> After</button>

          <div class="pab-sep" aria-hidden="true" />

          <!-- Duplicate / Delete -->
          <button class="pab-btn" @click="$emit('duplicate-page', pi)" title="Duplicate this page"
            aria-label="Duplicate page"><i class="fa-solid fa-copy" /></button>

          <button class="pab-btn pab-btn--danger" @click="$emit('delete-page', pi)"
            :disabled="report.content.length <= 1" title="Delete this page" aria-label="Delete page"><i
              class="fa-solid fa-trash" /></button>
        </div>

        <!-- THE PAGE SHEET -->
        <div ref="pageRefs" class="page-sheet" :class="{
          'page-active': currentPage === pi,
          'page-drop-target': dropTarget === pi,
        }" :style="getPageStyle(page, pi)" :data-page-index="pi" @click.self="selectPage(pi)"
          @dblclick.self="onPageDblClick($event, pi)" @dragover.prevent="dropTarget = pi"
          @dragleave.self="dropTarget = null" @drop.stop.prevent="onPageDrop($event, pi)"
          @contextmenu.prevent.stop="showPageCtx($event, pi)">
          <!-- Watermark -->
          <div v-if="settings.watermark" class="page-watermark" :style="watermarkStyle" aria-hidden="true">{{
            settings.watermark }}</div>

          <!-- Header -->
          <div v-if="settings.show_header" class="page-header-bar" :style="headerStyle" aria-label="Page header">{{
            settings.header_text || '' }}</div>

          <!-- Elements -->
          <div class="elements-layer">
            <template v-for="(el, ei) in page.elements" :key="el.id">
              <div v-if="el.visible !== false" class="canvas-el-wrap" :class="getElWrapClass(el, pi, ei)"
                :style="getElWrapStyle(el)" :data-el-id="el.id" :data-page="pi" :data-idx="ei"
                :tabindex="currentPage === pi ? 0 : -1" :aria-label="`${el.type} element`"
                :aria-selected="isSelected(pi, ei)" @mousedown.stop="onElMouseDown($event, pi, ei)"
                @dblclick.stop="onElDblClick($event, pi, ei)" @contextmenu.prevent.stop="showElCtx($event, pi, ei)"
                @keydown="onElKeyDown($event, pi, ei)">
                <!-- Selection handles -->
                <template v-if="isSelected(pi, ei) && !el.locked && currentPage === pi">
                  <div v-for="h in HANDLES" :key="h" :class="`resize-handle handle-${h}`"
                    @mousedown.stop.prevent="startResize($event, pi, ei, h)" :aria-label="`Resize ${h}`" />
                  <div class="rotate-handle" @mousedown.stop.prevent="startRotate($event, pi, ei)" title="Rotate"><i
                      class="fa-solid fa-rotate" /></div>
                  <div class="el-dims-badge" aria-live="polite">
                    {{ Math.round(el.position?.x || 0) }},{{ Math.round(el.position?.y || 0) }}
                    &nbsp;·&nbsp;
                    {{ Math.round(el.styles?.width || 0) }}×{{ Math.round(el.styles?.height || 0) }}
                    <span v-if="el.styles?.rotate"> · {{ el.styles.rotate }}°</span>
                  </div>
                </template>

                <!-- Lock indicator -->
                <div v-if="el.locked" class="lock-badge" aria-label="Locked">
                  <i class="fa-solid fa-lock" />
                </div>

                <!-- Priority stripe -->
                <div v-if="el.styles?.priority && el.styles.priority !== 'none'" class="priority-stripe"
                  :style="{ background: getPriorityColor(el.styles.priority) }" aria-hidden="true" />

                <!-- ══ ELEMENT CONTENT ════════════════════════════════ -->
                <div class="el-content-wrap" :style="getElContentStyle(el)">

                  <!-- TEXT TYPES -->
                  <div v-if="isTextType(el.type)" :contenteditable="isEditing(pi, ei) && !el.locked"
                    :spellcheck="isEditing(pi, ei)" class="el-text-content" :class="`el-type-${el.type}`"
                    :style="getTextStyle(el)" @input="onTextInput(pi, ei, $event)" @blur="stopEditing"
                    @paste="onTextPaste" @keydown.stop v-html="el.content || getPlaceholder(el.type)" />

                  <!-- RICH TEXT -->
                  <div v-else-if="el.type === 'richtext'" :contenteditable="isEditing(pi, ei) && !el.locked"
                    class="el-richtext-content" :style="getTextStyle(el)" @input="onTextInput(pi, ei, $event)"
                    @blur="stopEditing" @paste="onTextPaste" @keydown.stop
                    v-html="el.content || '<p>Click to edit…</p>'" />

                  <!-- IMAGE -->
                  <div v-else-if="el.type === 'image'" class="el-image-wrap">
                    <img v-if="el.src" :src="el.src" :alt="el.alt || ''" :style="getImageStyle(el)"
                      @error="onImgError($event)" loading="lazy" draggable="false" />
                    <div v-else class="el-image-placeholder" @click.stop="$emit('image-upload', pi, ei)">
                      <i class="fa-solid fa-image" />
                      <span>Click to add image</span>
                      <small>or drop image here</small>
                    </div>
                    <div v-if="el.src && isSelected(pi, ei)" class="el-image-overlay">
                      <button @click.stop="$emit('image-replace', { pi, ei })" title="Replace">
                        <i class="fa-solid fa-rotate" />
                      </button>
                      <button @click.stop="clearImage(pi, ei)" title="Remove">
                        <i class="fa-solid fa-trash" />
                      </button>
                    </div>
                  </div>

                  <!-- TABLE -->
                  <div v-else-if="el.type === 'table'" class="el-table-wrap">
                    <table class="el-table">
                      <thead>
                        <tr>
                          <th v-for="(col, ci) in (el.columns || [])" :key="ci" :style="getTableHeaderStyle(el)"
                            :contenteditable="isEditing(pi, ei)"
                            @blur="el.columns[ci] = $event.target.textContent; markDirty()" @keydown.stop>{{ col }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(row, ri) in (el.data || [])" :key="ri"
                          :style="ri % 2 === 0 ? { background: el.styles?.evenRowBg || '#fff' } : { background: el.styles?.oddRowBg || '#f8fafc' }">
                          <td v-for="col in (el.columns || [])" :key="col" :contenteditable="isEditing(pi, ei)"
                            @blur="el.data[ri][col] = $event.target.textContent; markDirty()" @keydown.stop
                            class="el-table-cell">{{
                            row[col] || '' }}</td>
                        </tr>
                      </tbody>
                    </table>
                    <div v-if="isSelected(pi, ei)" class="el-table-controls">
                      <button @click.stop="$emit('add-table-row')">+Row</button>
                      <button @click.stop="$emit('add-table-col')">+Col</button>
                      <button @click.stop="$emit('remove-table-row')"
                        :disabled="(el.data || []).length <= 1">−Row</button>
                      <button @click.stop="$emit('remove-table-col')"
                        :disabled="(el.columns || []).length <= 1">−Col</button>
                    </div>
                  </div>

                  <!-- CHART TYPES -->
                  <div v-else-if="isChartType(el.type)" class="el-chart-wrap"
                    :ref="domEl => registerChartRef(domEl, pi, ei)">
                    <div v-if="el.chartTitle" class="el-chart-title">{{ el.chartTitle }}</div>
                    <div class="el-chart-canvas-wrap"></div>
                  </div>

                  <!-- METRIC / KPI -->
                  <div v-else-if="el.type === 'metric'" class="el-metric" :style="getMetricStyle(el)">
                    <div class="el-metric-label">{{ el.label || 'Metric' }}</div>
                    <div class="el-metric-value"
                      :style="{ color: el.styles?.color || settings.primary_color || '#6366f1' }">{{ el.value
                      || '—' }}</div>
                    <div v-if="el.change" class="el-metric-change" :class="el.changeType || 'positive'">
                      <i :class="el.changeType === 'negative' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'" />
                      {{ el.change }}
                      <span class="el-metric-period">{{ el.changePeriod }}</span>
                    </div>
                  </div>

                  <!-- PROGRESS BAR -->
                  <div v-else-if="el.type === 'progress'" class="el-progress">
                    <div class="el-progress-header">
                      <span>{{ el.label || 'Progress' }}</span>
                      <span>{{ el.value || 0 }}%</span>
                    </div>
                    <div class="el-progress-track" :style="{ background: el.styles?.trackColor || '#e2e8f0' }">
                      <div class="el-progress-fill"
                        :style="{ width: (el.value || 0) + '%', background: el.styles?.color || settings.primary_color || '#6366f1' }" />
                    </div>
                  </div>

                  <!-- CIRCULAR PROGRESS -->
                  <div v-else-if="el.type === 'circular-progress'" class="el-circular">
                    <svg viewBox="0 0 120 120" class="el-circular-svg">
                      <circle cx="60" cy="60" r="52" fill="none" :stroke="el.styles?.trackColor || '#e2e8f0'"
                        stroke-width="8" />
                      <circle cx="60" cy="60" r="52" fill="none"
                        :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="8"
                        stroke-linecap="round" :stroke-dasharray="`${(el.value || 0) * 3.27} 327`"
                        transform="rotate(-90 60 60)" />
                      <text x="60" y="64" text-anchor="middle" font-size="20" font-weight="700"
                        :fill="el.styles?.color || settings.primary_color || '#6366f1'">{{ el.value || 0 }}%</text>
                    </svg>
                    <div v-if="el.label" class="el-circular-label">{{ el.label }}</div>
                  </div>

                  <!-- SPARKLINE -->
                  <div v-else-if="el.type === 'sparkline'" class="el-sparkline">
                    <svg width="100%" height="100%" viewBox="0 0 100 30" preserveAspectRatio="none">
                      <polyline :points="getSparkPoints(el)" fill="none"
                        :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <polyline :points="getSparkPoints(el) + ' 100,30 0,30'"
                        :fill="(el.styles?.color || settings.primary_color || '#6366f1') + '20'" stroke="none" />
                    </svg>
                  </div>

                  <!-- STAT ROW -->
                  <div v-else-if="el.type === 'stat-row'" class="el-stat-row">
                    <div v-for="(stat, si) in (el.stats || [])" :key="si" class="el-stat-item">
                      <div class="el-stat-value" :style="{ color: settings.primary_color || '#6366f1' }">{{ stat.value
                        }}</div>
                      <div class="el-stat-label">{{ stat.label }}</div>
                    </div>
                  </div>

                  <!-- CHECKLIST -->
                  <div v-else-if="el.type === 'checklist'" class="el-checklist">
                    <div v-for="(item, ci) in (el.items || [])" :key="ci" class="el-checklist-item">
                      <div class="el-check-box" :class="{ checked: item.checked }"
                        :style="{ borderColor: settings.primary_color || '#6366f1', background: item.checked ? (settings.primary_color || '#6366f1') : 'transparent' }"
                        @click="item.checked = !item.checked; markDirty()"><i v-if="item.checked"
                          class="fa-solid fa-check" /></div>
                      <span
                        :style="{ textDecoration: item.checked ? 'line-through' : 'none', opacity: item.checked ? .5 : 1 }">{{
                        item.text }}</span>
                    </div>
                  </div>

                  <!-- TIMELINE -->
                  <div v-else-if="el.type === 'timeline'" class="el-timeline">
                    <div v-for="(item, ti) in (el.items || [])" :key="ti" class="el-tl-item">
                      <div class="el-tl-marker">
                        <div class="el-tl-dot" :style="{ background: settings.primary_color || '#6366f1' }" />
                        <div v-if="ti < (el.items || []).length - 1" class="el-tl-line" />
                      </div>
                      <div class="el-tl-content">
                        <div class="el-tl-date" :style="{ color: settings.primary_color || '#6366f1' }">{{ item.date }}
                        </div>
                        <div class="el-tl-title">{{ item.label }}</div>
                        <div class="el-tl-desc">{{ item.desc }}</div>
                      </div>
                    </div>
                  </div>

                  <!-- CALLOUT -->
                  <div v-else-if="el.type === 'callout'" class="el-callout"
                    :style="{ background: (el.styles?.backgroundColor || (settings.primary_color || '#6366f1') + '10'), borderLeft: '4px solid ' + (el.styles?.borderColor || settings.primary_color || '#6366f1'), borderRadius: (el.styles?.borderRadius || 8) + 'px' }">
                    <span class="el-callout-emoji">{{ el.emoji || '💡' }}</span>
                    <div :contenteditable="isEditing(pi, ei)" @input="onTextInput(pi, ei, $event)" @keydown.stop
                      v-html="el.content || 'Callout text…'" class="el-callout-text" />
                  </div>

                  <!-- TESTIMONIAL -->
                  <div v-else-if="el.type === 'testimonial'" class="el-testimonial">
                    <div class="el-testi-quote">"</div>
                    <p class="el-testi-text">{{ el.content || 'Amazing product!' }}</p>
                    <div class="el-testi-author">{{ el.author || 'John Doe' }}</div>
                    <div class="el-testi-role">{{ el.role || 'CEO' }}</div>
                  </div>

                  <!-- SIGNATURE -->
                  <div v-else-if="el.type === 'signature'" class="el-signature">
                    <div class="el-sig-line" />
                    <div class="el-sig-name">{{ el.content || 'Signature' }}</div>
                    <div class="el-sig-title">{{ el.label || 'Authorized Signature' }}</div>
                  </div>

                  <!-- RATING -->
                  <div v-else-if="el.type === 'rating'" class="el-rating">
                    <i v-for="i in 5" :key="i" class="fa-solid fa-star"
                      :style="{ color: i <= (el.value || 4) ? (el.styles?.color || '#f59e0b') : '#e2e8f0', fontSize: (el.styles?.fontSize || 20) + 'px' }" />
                  </div>

                  <!-- QR CODE -->
                  <div v-else-if="el.type === 'qr-code'" class="el-qr">
                    <img v-if="el.qrUrl" :src="el.qrUrl"
                      :style="{ width: '100%', height: '100%', objectFit: 'contain' }" />
                    <div v-else class="el-qr-placeholder" @click.stop="generateQr(el)">
                      <i class="fa-solid fa-qrcode" />
                      <span>Click to generate QR</span>
                    </div>
                  </div>

                  <!-- VIDEO -->
                  <div v-else-if="el.type === 'video'" class="el-video">
                    <iframe v-if="getYoutubeId(el.videoUrl)"
                      :src="`https://www.youtube-nocookie.com/embed/${getYoutubeId(el.videoUrl)}`" frameborder="0"
                      allowfullscreen loading="lazy" title="Embedded video" />
                    <div v-else class="el-video-placeholder">
                      <i class="fa-solid fa-video" />
                      <span>Add YouTube URL in properties</span>
                    </div>
                  </div>

                  <!-- MAP -->
                  <div v-else-if="el.type === 'map'" class="el-map">
                    <iframe v-if="el.mapAddress"
                      :src="`https://maps.google.com/maps?q=${encodeURIComponent(el.mapAddress)}&output=embed`"
                      frameborder="0" loading="lazy" title="Embedded map" />
                    <div v-else class="el-map-placeholder">
                      <i class="fa-solid fa-map-location-dot" />
                      <span>Add address in properties</span>
                    </div>
                  </div>

                  <!-- SHAPES -->
                  <div v-else-if="el.type === 'rectangle'" class="el-shape el-rect" :style="getShapeStyle(el)" />
                  <div v-else-if="el.type === 'circle'" class="el-shape el-circle" :style="getShapeStyle(el)" />
                  <div v-else-if="el.type === 'triangle'" class="el-triangle" :style="getTriangleStyle(el)" />
                  <div v-else-if="el.type === 'divider'" class="el-divider"
                    :style="{ background: el.styles?.color || '#e2e8f0', height: (el.styles?.borderWidth || 2) + 'px' }" />
                  <svg v-else-if="el.type === 'arrow'" class="el-arrow" viewBox="0 0 200 40" preserveAspectRatio="none">
                    <line x1="5" y1="20" x2="185" y2="20"
                      :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="2" />
                    <polygon points="175,8 195,20 175,32"
                      :fill="el.styles?.color || settings.primary_color || '#6366f1'" />
                  </svg>

                  <!-- PRICE CARD -->
                  <div v-else-if="el.type === 'price-card'" class="el-price-card">
                    <div class="el-price-plan">{{ el.plan || 'Basic Plan' }}</div>
                    <div class="el-price-amount" :style="{ color: settings.primary_color || '#6366f1' }">{{ el.price ||
                      '$0' }}</div>
                    <div class="el-price-period">{{ el.period || '/month' }}</div>
                    <ul class="el-price-features">
                      <li v-for="f in (el.features || ['Feature 1', 'Feature 2'])" :key="f">
                        <i class="fa-solid fa-check" :style="{ color: settings.primary_color || '#6366f1' }" /> {{ f }}
                      </li>
                    </ul>
                  </div>

                  <!-- SOCIAL CARD -->
                  <div v-else-if="el.type === 'social-card'" class="el-social-card">
                    <div class="el-social-avatar">{{ el.avatar || '👤' }}</div>
                    <div class="el-social-name">{{ el.content || 'User Name' }}</div>
                    <div class="el-social-sub">{{ el.subtitle || 'Title / Position' }}</div>
                  </div>

                  <!-- KANBAN CARD -->
                  <div v-else-if="el.type === 'kanban'" class="el-kanban">
                    <div class="el-kanban-title">{{ el.content || 'Task title' }}</div>
                    <div class="el-kanban-status" :style="{ color: settings.primary_color || '#6366f1' }">{{ el.status
                      || 'In Progress'
                      }}</div>
                    <div v-if="el.due" class="el-kanban-due"><i class="fa-regular fa-calendar" /> {{ el.due }}</div>
                  </div>

                  <!-- TABLE OF CONTENTS -->
                  <div v-else-if="el.type === 'toc'" class="el-toc">
                    <div class="el-toc-title">{{ el.content || 'Table of Contents' }}</div>
                    <div v-for="(item, ti) in (el.tocItems || [])" :key="ti" class="el-toc-item"
                      :style="{ paddingLeft: (item.level - 1) * 16 + 'px', fontWeight: item.level === 1 ? '600' : '400', fontSize: item.level === 1 ? '13px' : '11px' }">
                      <span>{{ item.text }}</span>
                      <span class="el-toc-page" :style="{ color: settings.primary_color || '#6366f1' }">{{ item.page
                        }}</span>
                    </div>
                    <div v-if="!(el.tocItems || []).length" class="el-toc-empty">
                      <i class="fa-solid fa-list-ol" /> Add headings to auto-generate TOC
                    </div>
                  </div>

                  <!-- PAGE NUMBER -->
                  <div v-else-if="el.type === 'pagenum'" class="el-pagenum" :style="getTextStyle(el)">{{ pi + 1 }}</div>

                  <!-- DATE -->
                  <div v-else-if="el.type === 'date-el'" class="el-date" :style="getTextStyle(el)">{{ formattedDate }}
                  </div>

                  <!-- WATERMARK ELEMENT -->
                  <div v-else-if="el.type === 'watermark'" class="el-watermark"
                    :style="{ color: el.styles?.color || '#94a3b8', opacity: (el.styles?.opacity || 20) / 100, fontSize: (el.styles?.fontSize || 48) + 'px', transform: `rotate(${el.styles?.rotate || -30}deg)` }">
                    {{ el.content || 'DRAFT' }}
                  </div>

                  <!-- ICON (emoji) -->
                  <div v-else-if="el.type === 'icon'" class="el-icon-el"
                    :style="{ fontSize: (el.styles?.fontSize || 40) + 'px', color: el.styles?.color || settings.primary_color || '#6366f1' }">
                    {{ el.content || '⭐' }}
                  </div>

                  <!-- AVATAR -->
                  <div v-else-if="el.type === 'avatar'" class="el-avatar"
                    :style="{ background: settings.primary_color || '#6366f1', borderRadius: '50%', width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '32px', color: '#fff' }">
                    {{ el.content || '👤' }}
                  </div>

                  <!-- SPACER -->
                  <div v-else-if="el.type === 'spacer'" class="el-spacer">
                    <span v-if="isSelected(pi, ei)">Spacer</span>
                  </div>

                  <!-- BADGE -->
                  <div v-else-if="el.type === 'badge'" class="el-badge-el"
                    :style="{ background: el.styles?.backgroundColor || (settings.primary_color || '#6366f1') + '20', color: el.styles?.color || (settings.primary_color || '#6366f1'), borderRadius: (el.styles?.borderRadius || 99) + 'px', fontSize: (el.styles?.fontSize || 13) + 'px' }">
                    {{ el.content || 'Badge' }}
                  </div>

                  <!-- CODE BLOCK -->
                  <div v-else-if="el.type === 'code'" class="el-code-block">
                    <div class="el-code-header">
                      <span class="el-code-lang">{{ el.language || 'Code' }}</span>
                    </div>
                    <pre class="el-code-pre"><code>{{ el.content || '// Enter code here' }}</code></pre>
                  </div>

                  <!-- LIST -->
                  <div v-else-if="el.type === 'list'" class="el-list" :style="getTextStyle(el)">
                    <component :is="el.styles?.listStyle === 'numbered' ? 'ol' : 'ul'">
                      <li v-for="(item, li) in (el.items || ['List item 1', 'List item 2'])" :key="li">{{ item }}</li>
                    </component>
                  </div>

                  <!-- STEPS -->
                  <div v-else-if="el.type === 'steps'" class="el-steps">
                    <div v-for="(step, si) in (el.items || [{ label: 'Step 1' }, { label: 'Step 2' }])" :key="si"
                      class="el-step-item">
                      <div class="el-step-num" :style="{ background: settings.primary_color || '#6366f1' }">{{ si + 1 }}
                      </div>
                      <div class="el-step-label">{{ step.label }}</div>
                    </div>
                  </div>

                  <!-- FALLBACK -->
                  <div v-else class="el-fallback">
                    <i class="fa-solid fa-cube" />
                    <span>{{ el.type }}</span>
                  </div>

                </div><!-- /el-content-wrap -->
              </div><!-- /canvas-el-wrap -->
            </template>

            <!-- Empty page hint -->
            <div v-if="!(page.elements || []).length" class="page-empty-hint" aria-label="Empty page hint">
              <i class="fa-solid fa-plus-circle" />
              <span>Double-click or drag elements here</span>
            </div>
          </div><!-- /elements-layer -->

          <!-- Footer -->
          <div v-if="settings.show_footer" class="page-footer-bar" :style="footerStyle" aria-label="Page footer">
            <span>{{ settings.footer_left || '' }}</span>
            <span v-if="settings.show_page_numbers !== false">{{ pi + 1 }}</span>
            <span>{{ (settings.footer_right || '').replace('{n}', pi + 1).replace('{total}', report.content.length)
              }}</span>
          </div>
        </div><!-- /page-sheet -->

      </div><!-- /page-wrapper -->

      <!-- Add final page button -->
      <button class="add-page-final" @click="$emit('add-page')" aria-label="Add new page">
        <i class="fa-solid fa-plus" />
        <span>Add New Page</span>
      </button>
    </div><!-- /canvas-container -->

    <!-- Page navigation dots -->
    <nav v-if="report.content.length > 1" class="page-nav-dots" aria-label="Page navigation">
      <button class="page-nav-arrow" :disabled="currentPage === 0" @click="$emit('go-to-page', currentPage - 1)">
        <i class="fa-solid fa-chevron-left" />
      </button>
      <button v-for="(p, pi) in report.content" :key="p.id" class="page-nav-dot"
        :class="{ active: pi === currentPage, filled: (p.elements || []).length > 0 }" @click="$emit('go-to-page', pi)"
        :title="`Page ${pi + 1}`" :aria-current="pi === currentPage ? 'page' : undefined" />
      <button class="page-nav-arrow" :disabled="currentPage >= report.content.length - 1"
        @click="$emit('go-to-page', currentPage + 1)">
        <i class="fa-solid fa-chevron-right" />
      </button>
    </nav>

    <!-- Zoom badge -->
    <div v-if="zoom !== 100" class="zoom-badge" @click="$emit('zoom-reset')" title="Click to reset zoom" role="button">
      {{ zoom }}%
    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick, reactive } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

// ── Props ──────────────────────────────────────────────────────────────
const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
  currentPage: { type: Number, default: 0 },
  selectedElIdx: { type: Number, default: null },
  selectedEls: { type: Array, default: () => [] },
  editingElIdx: { type: Number, default: null },
  zoom: { type: Number, default: 100 },
  showGrid: { type: Boolean, default: true },
  snapToGrid: { type: Boolean, default: true },
  showRulers: { type: Boolean, default: false },
  isDraggingEl: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
  measureMode: { type: Boolean, default: false },
})

// ── Emits ──────────────────────────────────────────────────────────────
const emit = defineEmits([
  'select-element', 'deselect-all', 'add-element', 'select-page',
  'add-page', 'add-page-before', 'add-page-after',
  'move-page-up', 'move-page-down',
  'start-editing', 'stop-editing', 'update-text-content',
  'element-mouse-down', 'resize-start', 'rotate-start',
  'canvas-drop', 'rubber-band-start', 'rubber-band-end',
  'zoom-wheel', 'page-dblclick', 'context-menu',
  'image-upload', 'image-replace', 'go-to-page', 'mark-dirty', 'zoom-reset',
  'add-table-row', 'add-table-col', 'remove-table-row', 'remove-table-col',
  'duplicate-page', 'delete-page',
])

// ── Refs ───────────────────────────────────────────────────────────────
const canvasAreaRef = ref(null)
const pageRefs = ref([])
const dropTarget = ref(null)

// Rubber-band
const rb = reactive({ active: false, startX: 0, startY: 0, x: 0, y: 0, w: 0, h: 0 })

// Chart instances — keyed by `pi-ei`
const chartRefs = {}
const chartInstances = {}

// RAF handles
let dragRAF = null
let resizeRAF = null
let rotateRAF = null
let edgeScrollRAF = null

// Tracks the last mouse position during ANY drag for the edge-scroll loop
const lastDragPos = { x: 0, y: 0 }

// ── Constants ──────────────────────────────────────────────────────────
const HANDLES = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w']

const EDGE_ZONE = 80  // px from canvas-area boundary that triggers scroll
const MAX_SCROLL_SPD = 14  // px/frame at the very edge

const PAGE_SIZES = {
  A4: { portrait: [794, 1123], landscape: [1123, 794] },
  A3: { portrait: [1123, 1587], landscape: [1587, 1123] },
  A5: { portrait: [559, 794], landscape: [794, 559] },
  Letter: { portrait: [850, 1100], landscape: [1100, 850] },
  Legal: { portrait: [850, 1400], landscape: [1400, 850] },
}

// ── Computed ───────────────────────────────────────────────────────────
const formattedDate = computed(() =>
  new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
)

const pageDims = computed(() => {
  const sz = props.settings.page_size || 'A4'
  const ori = props.settings.orientation || 'portrait'
  return (PAGE_SIZES[sz] || PAGE_SIZES.A4)[ori]
})

const rbStyle = computed(() => ({
  position: 'fixed',
  left: rb.x + 'px', top: rb.y + 'px',
  width: rb.w + 'px', height: rb.h + 'px',
  border: '1.5px dashed #6366f1',
  background: 'rgba(99,102,241,.05)',
  pointerEvents: 'none', zIndex: 9000,
}))

const watermarkStyle = computed(() => ({
  position: 'absolute', top: '50%', left: '50%',
  transform: `translate(-50%,-50%) rotate(${props.settings.watermark_rotate || -30}deg)`,
  fontSize: '72px', fontWeight: '900',
  color: props.settings.watermark_color || '#94a3b8',
  opacity: (props.settings.watermark_opacity || 8) / 100,
  whiteSpace: 'nowrap', pointerEvents: 'none', zIndex: 5,
}))

const headerStyle = computed(() => ({
  background: props.settings.header_color || '#1e293b',
  color: props.settings.header_text_color || '#ffffff',
  height: (props.settings.header_height || 50) + 'px',
  fontSize: '12px', fontWeight: '600',
  display: 'flex', alignItems: 'center',
  padding: '0 ' + (props.settings.margin || 40) + 'px',
  position: 'absolute', top: 0, left: 0, right: 0, zIndex: 10,
}))

const footerStyle = computed(() => ({
  position: 'absolute', bottom: 0, left: 0, right: 0,
  height: '36px', display: 'flex', alignItems: 'center', justifyContent: 'space-between',
  padding: '0 ' + (props.settings.margin || 40) + 'px',
  fontSize: '10px',
  color: props.settings.footer_color || '#94a3b8',
  borderTop: `1px solid ${props.settings.primary_color || '#6366f1'}20`,
  zIndex: 10,
}))

// ── Edge-scroll helpers ────────────────────────────────────────────────
/**
 * Called every RAF frame while an element is being dragged.
 * Reads `lastDragPos` (updated in every mousemove) and scrolls the
 * canvas area when the mouse is within EDGE_ZONE of its boundary.
 * Scroll speed is proportional to how deep into the zone the cursor is.
 */
function edgeScrollTick() {
  const area = canvasAreaRef.value
  if (!area) { edgeScrollRAF = null; return }

  const rect = area.getBoundingClientRect()
  const { x, y } = lastDragPos

  const dLeft = x - rect.left
  const dRight = rect.right - x
  const dTop = y - rect.top
  const dBottom = rect.bottom - y

  let sx = 0, sy = 0

  if (dLeft < EDGE_ZONE) sx = -MAX_SCROLL_SPD * (1 - dLeft / EDGE_ZONE)
  else if (dRight < EDGE_ZONE) sx = MAX_SCROLL_SPD * (1 - dRight / EDGE_ZONE)

  if (dTop < EDGE_ZONE) sy = -MAX_SCROLL_SPD * (1 - dTop / EDGE_ZONE)
  else if (dBottom < EDGE_ZONE) sy = MAX_SCROLL_SPD * (1 - dBottom / EDGE_ZONE)

  if (sx !== 0 || sy !== 0) area.scrollBy(sx, sy)

  edgeScrollRAF = requestAnimationFrame(edgeScrollTick)
}

function startEdgeScroll() {
  if (!edgeScrollRAF) edgeScrollRAF = requestAnimationFrame(edgeScrollTick)
}

function stopEdgeScroll() {
  if (edgeScrollRAF) { cancelAnimationFrame(edgeScrollRAF); edgeScrollRAF = null }
}

// ── Ctrl+wheel zoom — non-passive ─────────────────────────────────────
/**
 * Vue's @wheel directive registers a passive listener in modern browsers,
 * making e.preventDefault() a no-op which means the browser still zooms.
 * We register our own non-passive listener so we can prevent default.
 */
function handleWheel(e) {
  if (e.ctrlKey || e.altKey) {
    e.preventDefault()
    e.stopPropagation()
    emit('zoom-wheel', e)
  }
}

// ── Style helpers ──────────────────────────────────────────────────────
function getPageStyle(page, pi) {
  const [w, h] = pageDims.value
  const m = props.settings.margin || 40
  const isActive = props.currentPage === pi
  return {
    width: w + 'px', minHeight: h + 'px',
    background: props.settings.background_color || '#ffffff',
    backgroundImage: props.settings.bg_image ? `url(${props.settings.bg_image})` : 'none',
    backgroundSize: 'cover',
    fontFamily: props.settings.font_family || "'DM Sans', sans-serif",
    fontSize: (props.settings.font_size || 14) + 'px',
    color: props.settings.text_color || '#1e293b',
    padding: m + 'px',
    borderRadius: (props.settings.page_radius || 0) + 'px',
    direction: props.settings.rtl ? 'rtl' : 'ltr',
    position: 'relative', boxSizing: 'border-box',
    boxShadow: isActive
      ? '0 0 0 3px rgba(251,191,36,.7), 0 12px 40px rgba(0,0,0,.2)'
      : '0 4px 24px rgba(0,0,0,.12)',
    transition: 'box-shadow .3s',
  }
}

function getElWrapStyle(el) {
  const s = el.styles || {}
  const transforms = []
  if (s.rotate) transforms.push(`rotate(${s.rotate}deg)`)
  if (s.scaleX === -1) transforms.push('scaleX(-1)')
  if (s.scaleY === -1) transforms.push('scaleY(-1)')

  const filters = []
  if (s.blur) filters.push(`blur(${s.blur}px)`)
  if (s.brightness && s.brightness !== 100) filters.push(`brightness(${s.brightness}%)`)
  if (s.contrast && s.contrast !== 100) filters.push(`contrast(${s.contrast}%)`)
  if (s.grayscale) filters.push(`grayscale(${s.grayscale}%)`)
  if (s.sepia) filters.push(`sepia(${s.sepia}%)`)
  if (s.saturate && s.saturate !== 100) filters.push(`saturate(${s.saturate}%)`)
  if (s.hueRotate) filters.push(`hue-rotate(${s.hueRotate}deg)`)
  if (s.invert) filters.push(`invert(${s.invert}%)`)

  let bg = s.backgroundColor || 'transparent'
  if (s.useGradient && s.gradientFrom && s.gradientTo) {
    bg = `linear-gradient(${s.gradientDir || '135deg'}, ${s.gradientFrom}, ${s.gradientTo})`
  }

  return {
    position: 'absolute',
    left: (el.position?.x || 0) + 'px',
    top: (el.position?.y || 0) + 'px',
    width: (s.width || 200) + 'px',
    height: (s.height || 80) + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: transforms.join(' ') || 'none',
    filter: filters.join(' ') || 'none',
    background: bg,
    borderRadius: (s.borderRadius || 0) + 'px',
    border: s.borderWidth ? `${s.borderWidth}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : 'none',
    boxShadow: s.boxShadow || 'none',
    mixBlendMode: s.mixBlendMode || 'normal',
    cursor: el.locked ? 'not-allowed' : 'move',
    userSelect: 'none', overflow: 'hidden', transformOrigin: 'center',
  }
}

function getElContentStyle(el) {
  const s = el.styles || {}
  return { width: '100%', height: '100%', padding: (s.padding || 0) + 'px', overflow: 'hidden', boxSizing: 'border-box' }
}

function getTextStyle(el) {
  const s = el.styles || {}
  let textBG = 'none', webkitBGClip = 'unset', webkitFillColor = 'unset'
  if (s.textGradient && s.textGradientFrom && s.textGradientTo) {
    textBG = `linear-gradient(135deg, ${s.textGradientFrom}, ${s.textGradientTo})`
    webkitBGClip = 'text'
    webkitFillColor = 'transparent'
  }
  return {
    fontFamily: s.fontFamily || props.settings.font_family || 'DM Sans',
    fontSize: (s.fontSize || 14) + 'px',
    fontWeight: s.fontWeight || '400',
    fontStyle: s.fontStyle || 'normal',
    textDecoration: s.textDecoration || 'none',
    color: s.color || props.settings.text_color || '#1e293b',
    textAlign: s.textAlign || 'left',
    lineHeight: s.lineHeight || 1.5,
    letterSpacing: (s.letterSpacing || 0) + 'px',
    textTransform: s.textTransform || 'none',
    columnCount: s.columns || 1,
    columnGap: '20px',
    background: textBG,
    WebkitBackgroundClip: webkitBGClip,
    WebkitTextFillColor: webkitFillColor,
    outline: 'none', width: '100%', height: '100%',
    wordBreak: 'break-word', whiteSpace: el.type === 'code' ? 'pre-wrap' : 'normal',
  }
}

function getImageStyle(el) {
  return {
    width: '100%', height: '100%',
    objectFit: el.styles?.objectFit || 'cover',
    borderRadius: (el.styles?.borderRadius || 0) + 'px',
    display: 'block',
    filter: getImageFilter(el.styles?.imageFilter),
  }
}

function getImageFilter(f) {
  const map = { grayscale: 'grayscale(100%)', sepia: 'sepia(80%)', vintage: 'sepia(50%) contrast(85%) brightness(90%)', blur: 'blur(2px)', bright: 'brightness(130%)' }
  return map[f] || 'none'
}

function getTableHeaderStyle(el) {
  return { background: el.styles?.headerBg || props.settings.primary_color || '#6366f1', color: '#fff', padding: '8px 10px', fontSize: '11px', fontWeight: '600', textAlign: 'left' }
}

function getMetricStyle(el) {
  const s = el.styles || {}
  return { background: s.backgroundColor || '#f8fafc', borderRadius: (s.borderRadius || 12) + 'px', border: s.borderWidth ? `${s.borderWidth}px solid ${s.borderColor || '#e2e8f0'}` : '1px solid #e2e8f0', padding: '14px', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'center' }
}

function getShapeStyle(el) {
  const s = el.styles || {}
  return { width: '100%', height: '100%', background: s.backgroundColor || props.settings.primary_color || '#6366f1', borderRadius: el.type === 'circle' ? '50%' : (s.borderRadius || 0) + 'px' }
}

function getTriangleStyle(el) {
  const s = el.styles || {}
  const w = (s.width || 160) / 2
  const h = s.height || 140
  const c = s.backgroundColor || props.settings.primary_color || '#6366f1'
  return { width: 0, height: 0, borderLeft: `${w}px solid transparent`, borderRight: `${w}px solid transparent`, borderBottom: `${h}px solid ${c}`, background: 'transparent' }
}

function getSparkPoints(el) {
  const data = el.sparkData || [3, 5, 4, 8, 6, 9, 5, 10, 7, 8]
  const max = Math.max(...data)
  return data.map((v, i) => `${(i / (data.length - 1)) * 100},${30 - (v / max) * 25}`).join(' ')
}

function getPriorityColor(p) {
  return { low: '#3b82f6', medium: '#f59e0b', high: '#f97316', urgent: '#ef4444' }[p] || 'transparent'
}

function getPlaceholder(type) {
  const map = {
    heading: 'Click to edit heading', subheading: 'Click to edit subheading',
    text: 'Start typing…', quote: 'Inspiring quote…', blockquote: 'Blockquote text…',
    code: '// Your code here', badge: 'Badge', link: 'https://example.com',
    highlight: 'Highlighted text', list: 'List item…', callout: 'Callout message…',
  }
  return map[type] || 'Click to edit'
}

function getYoutubeId(url) {
  if (!url) return null
  const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/)
  return m ? m[1] : null
}

// ── El class / selection helpers ───────────────────────────────────────
function getElWrapClass(el, pi, ei) {
  return {
    'el-selected': isSelected(pi, ei),
    'el-editing': isEditing(pi, ei),
    'el-locked': el.locked,
    'el-hidden': el.visible === false,
  }
}

function isSelected(pi, ei) {
  return props.currentPage === pi && (props.selectedElIdx === ei || props.selectedEls.includes(ei))
}

function isEditing(pi, ei) {
  return props.currentPage === pi && props.editingElIdx === ei
}

function isTextType(type) {
  return ['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight', 'badge', 'code', 'link', 'callout', 'pagenum', 'date-el', 'watermark'].includes(type)
}

function isChartType(type) { return type?.endsWith('-chart') }

// ── Actions ────────────────────────────────────────────────────────────
function selectPage(pi) { emit('select-page', pi) }
function deselectAll() { emit('deselect-all') }
function markDirty() { emit('mark-dirty') }
function stopEditing() { emit('stop-editing') }

function clearImage(pi, ei) {
  const el = props.report.content[pi].elements[ei]
  if (el) { el.src = ''; markDirty() }
}

async function generateQr(el) {
  const text = el.qrText || 'https://example.com'
  const size = el.qrSize || 160
  el.qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}`
  markDirty()
}

// ── Event handlers ─────────────────────────────────────────────────────
function onElMouseDown(e, pi, ei) {
  if (e.button !== 0) return
  const el = props.report.content[pi].elements[ei]
  if (!el || el.locked) return

  if (e.shiftKey) {
    const already = props.selectedEls.includes(ei)
    emit('select-element', already ? props.selectedEls.filter(i => i !== ei) : [...props.selectedEls, ei])
    return
  }

  emit('select-page', pi)
  emit('select-element', [ei])
  emit('element-mouse-down', { event: e, pageIndex: pi, elementIndex: ei })

  const scale = props.zoom / 100
  const startX = e.clientX, startY = e.clientY
  const origX = el.position?.x || 0, origY = el.position?.y || 0
  const snap = v => props.snapToGrid ? Math.round(v / 10) * 10 : v
  let moved = false

  // Seed edge-scroll tracker
  lastDragPos.x = e.clientX
  lastDragPos.y = e.clientY

  const onMove = (ev) => {
    // Keep edge-scroll tracker fresh
    lastDragPos.x = ev.clientX
    lastDragPos.y = ev.clientY
    startEdgeScroll()

    cancelAnimationFrame(dragRAF)
    dragRAF = requestAnimationFrame(() => {
      const dx = (ev.clientX - startX) / scale
      const dy = (ev.clientY - startY) / scale
      if (!moved && Math.hypot(dx, dy) > 3) moved = true
      if (!moved) return
      if (!el.position) el.position = { x: 0, y: 0 }
      el.position.x = Math.max(0, snap(origX + dx))
      el.position.y = Math.max(0, snap(origY + dy))
    })
  }

  const onUp = () => {
    stopEdgeScroll()
    cancelAnimationFrame(dragRAF)
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)
    if (moved) markDirty()
  }

  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}

function onElDblClick(e, pi, ei) {
  e.stopPropagation()
  const el = props.report.content[pi].elements[ei]
  if (!el || el.locked) return
  emit('select-page', pi)
  emit('start-editing', { pageIndex: pi, elementIndex: ei })
  if (el.type === 'image' && !el.src) emit('image-upload', pi, ei)
}

function onElKeyDown(e, pi, ei) {
  if (['Enter', 'Space', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) e.stopPropagation()
  if (e.key === 'Enter' && !isEditing(pi, ei)) emit('start-editing', { pageIndex: pi, elementIndex: ei })
}

function onTextInput(pi, ei, e) {
  emit('update-text-content', { pageIndex: pi, elementIndex: ei, content: e.target.innerHTML })
}

function onTextPaste(e) {
  e.preventDefault()
  const text = e.clipboardData?.getData('text/plain') || ''
  document.execCommand('insertText', false, text)
}

function onImgError(e) { e.target.style.display = 'none' }

// ── Resize ─────────────────────────────────────────────────────────────
function startResize(e, pi, ei, handle) {
  e.stopPropagation(); e.preventDefault()
  const el = props.report.content[pi].elements[ei]
  if (!el) return
  const scale = props.zoom / 100
  const startX = e.clientX, startY = e.clientY
  const ow = el.styles?.width || 100, oh = el.styles?.height || 60
  const ox = el.position?.x || 0, oy = el.position?.y || 0
  const MIN = 20

  const onMove = (ev) => {
    cancelAnimationFrame(resizeRAF)
    resizeRAF = requestAnimationFrame(() => {
      const dx = (ev.clientX - startX) / scale, dy = (ev.clientY - startY) / scale
      if (!el.styles) el.styles = {}
      if (handle.includes('e')) el.styles.width = Math.max(MIN, ow + dx)
      if (handle.includes('s')) el.styles.height = Math.max(MIN, oh + dy)
      if (handle.includes('w')) { el.styles.width = Math.max(MIN, ow - dx); el.position.x = ox + (ow - el.styles.width) }
      if (handle.includes('n')) { el.styles.height = Math.max(MIN, oh - dy); el.position.y = oy + (oh - el.styles.height) }
    })
  }

  const onUp = () => {
    cancelAnimationFrame(resizeRAF)
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)
    markDirty()
  }

  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}

// ── Rotate ─────────────────────────────────────────────────────────────
function startRotate(e, pi, ei) {
  e.stopPropagation(); e.preventDefault()
  const el = props.report.content[pi].elements[ei]
  if (!el) return
  const scale = props.zoom / 100
  const pageEl = canvasAreaRef.value?.querySelector(`[data-page-index="${pi}"]`)
  const rect = pageEl?.getBoundingClientRect()
  if (!rect) return
  const cx = rect.left + ((el.position?.x || 0) + (el.styles?.width || 100) / 2) * scale
  const cy = rect.top + ((el.position?.y || 0) + (el.styles?.height || 60) / 2) * scale

  const onMove = (ev) => {
    cancelAnimationFrame(rotateRAF)
    rotateRAF = requestAnimationFrame(() => {
      const angle = Math.atan2(ev.clientY - cy, ev.clientX - cx) * 180 / Math.PI + 90
      if (!el.styles) el.styles = {}
      el.styles.rotate = Math.round(angle)
    })
  }

  const onUp = () => {
    cancelAnimationFrame(rotateRAF)
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)
    markDirty()
  }

  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}

// ── Rubber-band selection ──────────────────────────────────────────────
function startRubberBand(e) {
  if (e.target !== canvasAreaRef.value) return
  rb.active = true
  rb.startX = e.clientX; rb.startY = e.clientY
  rb.x = e.clientX; rb.y = e.clientY; rb.w = 0; rb.h = 0

  const onMove = (ev) => {
    rb.x = Math.min(ev.clientX, rb.startX)
    rb.y = Math.min(ev.clientY, rb.startY)
    rb.w = Math.abs(ev.clientX - rb.startX)
    rb.h = Math.abs(ev.clientY - rb.startY)
  }

  const onUp = () => {
    rb.active = false
    document.removeEventListener('mousemove', onMove)
    document.removeEventListener('mouseup', onUp)

    if (rb.w < 6 || rb.h < 6) return
    const pageEl = canvasAreaRef.value?.querySelector('.page-sheet.page-active')
    if (!pageEl) return
    const rect = pageEl.getBoundingClientRect()
    const scale = props.zoom / 100
    const bx = (rb.x - rect.left) / scale, by = (rb.y - rect.top) / scale
    const bw = rb.w / scale, bh = rb.h / scale

    const hits = []
    const els = props.report.content[props.currentPage]?.elements || []
    els.forEach((el, i) => {
      const ex = el.position?.x || 0, ey = el.position?.y || 0
      const ew = el.styles?.width || 0, eh = el.styles?.height || 0
      if (ex < bx + bw && ex + ew > bx && ey < by + bh && ey + eh > by) hits.push(i)
    })
    if (hits.length) emit('select-element', hits)
  }

  document.addEventListener('mousemove', onMove)
  document.addEventListener('mouseup', onUp)
}

// ── Drop (from left sidebar drag) ─────────────────────────────────────
function onDragOver(e) {
  // Update edge-scroll tracker while sidebar element is dragged over canvas
  lastDragPos.x = e.clientX; lastDragPos.y = e.clientY
  e.dataTransfer.dropEffect = 'copy'
}

function onDrop(e) {
  stopEdgeScroll()
  const raw = e.dataTransfer.getData('el-def')
  if (!raw) return
  const def = JSON.parse(raw)
  const area = canvasAreaRef.value?.getBoundingClientRect()
  if (!area) return
  const scale = props.zoom / 100
  const margin = props.settings.margin || 40
  emit('canvas-drop', {
    def,
    x: Math.max(0, (e.clientX - area.left) / scale - margin - (def.w || 100) / 2),
    y: Math.max(0, (e.clientY - area.top + canvasAreaRef.value.scrollTop) / scale - margin - (def.h || 60) / 2),
  })
  dropTarget.value = null
}

function onPageDrop(e, pi) {
  e.stopPropagation()
  stopEdgeScroll()
  const raw = e.dataTransfer.getData('el-def')
  if (!raw) return
  emit('select-page', pi)
  emit('add-element', { def: JSON.parse(raw), pageIndex: pi, x: 80, y: 80 })
  dropTarget.value = null
}

// ── Context menus ──────────────────────────────────────────────────────
function onCanvasCtx(e) { emit('context-menu', e, null, null) }
function showPageCtx(e, pi) { emit('context-menu', e, pi, null) }
function showElCtx(e, pi, ei) { emit('select-page', pi); emit('select-element', [ei]); emit('context-menu', e, pi, ei) }
function onPageDblClick(e, pi) { emit('page-dblclick', { event: e, pageIndex: pi }) }

// ── Chart rendering ────────────────────────────────────────────────────
function registerChartRef(domEl, pi, ei) {
  if (!domEl) return
  const key = `${pi}-${ei}`
  chartRefs[key] = domEl
  nextTick(() => renderChart(pi, ei))
}

function renderChart(pi, ei) {
  const key = `${pi}-${ei}`
  const el = props.report.content[pi]?.elements[ei]
  if (!el || !isChartType(el.type)) return
  const container = chartRefs[key]
  if (!container) return

  const wrap = container.querySelector('.el-chart-canvas-wrap')
  if (!wrap) return

  let canvas = wrap.querySelector('canvas')
  if (!canvas) {
    canvas = document.createElement('canvas')
    canvas.style.cssText = 'width:100%;height:100%;display:block'
    wrap.appendChild(canvas)
  }

  if (chartInstances[key]) {
    try { chartInstances[key].destroy() } catch { }
    delete chartInstances[key]
  }

  const typeMap = {
    'bar-chart': 'bar', 'line-chart': 'line', 'area-chart': 'line',
    'pie-chart': 'pie', 'doughnut-chart': 'doughnut', 'radar-chart': 'radar',
    'scatter-chart': 'scatter', 'polar-chart': 'polarArea',
  }
  const chartType = typeMap[el.type] || 'bar'
  const labels = el.chartData?.labels || ['Q1', 'Q2', 'Q3', 'Q4']
  const values = el.chartData?.values || [25, 40, 35, 55]
  const primary = el.chartColor || props.settings.primary_color || '#6366f1'
  const PIE_COLORS = [primary, '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#84cc16']
  const isMulti = ['pie', 'doughnut', 'polarArea'].includes(chartType)

  try {
    chartInstances[key] = new Chart(canvas.getContext('2d'), {
      type: chartType,
      data: {
        labels,
        datasets: [{
          label: el.chartTitle || 'Data',
          data: values,
          backgroundColor: isMulti ? PIE_COLORS : primary + '99',
          borderColor: isMulti ? PIE_COLORS : primary,
          borderWidth: 2,
          fill: el.type === 'area-chart',
          tension: 0.35,
          pointBackgroundColor: primary,
          pointRadius: 4,
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        animation: { duration: 500 },
        plugins: {
          legend: { display: isMulti, position: 'bottom', labels: { font: { size: 11 }, padding: 12 } },
          tooltip: { backgroundColor: props.isDark ? '#1e293b' : '#fff', titleColor: props.isDark ? '#f1f5f9' : '#0f172a', bodyColor: props.isDark ? '#94a3b8' : '#64748b', borderColor: primary, borderWidth: 1, cornerRadius: 8, padding: 10 },
        },
        scales: isMulti ? {} : {
          x: { grid: { color: props.isDark ? '#263348' : '#f1f5f9' }, ticks: { font: { size: 10 } } },
          y: { beginAtZero: true, grid: { color: props.isDark ? '#263348' : '#f1f5f9' }, ticks: { font: { size: 10 } } },
        },
      },
    })
  } catch (err) {
    console.warn('[EditorCanvas] Chart render failed:', err)
  }
}

watch(
  () => [props.currentPage, props.settings.primary_color, props.settings.accent_color, props.isDark],
  () => {
    nextTick(() => {
      const pi = props.currentPage
      const els = props.report.content[pi]?.elements || []
      els.forEach((el, ei) => { if (isChartType(el.type)) renderChart(pi, ei) })
    })
  },
  { deep: true }
)

// ── Lifecycle ──────────────────────────────────────────────────────────
onMounted(() => {
  // Non-passive wheel — MUST use addEventListener, not Vue @wheel directive
  const area = canvasAreaRef.value
  if (area) area.addEventListener('wheel', handleWheel, { passive: false })
})

onBeforeUnmount(() => {
  // Clean up wheel listener
  const area = canvasAreaRef.value
  if (area) area.removeEventListener('wheel', handleWheel)

  // Clean up RAF loops
  stopEdgeScroll()
  cancelAnimationFrame(dragRAF)
  cancelAnimationFrame(resizeRAF)
  cancelAnimationFrame(rotateRAF)

  // Destroy chart instances (memory leak prevention)
  Object.values(chartInstances).forEach(c => { try { c.destroy() } catch { } })
})
</script>

<style scoped>
/* ═══ CANVAS AREA ════════════════════════════════════════════════════════ */
.canvas-area {
  flex: 1;
  background: var(--canvas-bg, #e8eef5);
  overflow: auto;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 32px 120px;
  scrollbar-width: thin;
}

.canvas-area.is-dark {
  --canvas-bg: #0a0f1a;
}

.canvas-area.measure-mode {
  cursor: crosshair;
}

/* ── Grid ── */
.grid-overlay {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  background-image:
    linear-gradient(rgba(99, 102, 241, .06) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99, 102, 241, .06) 1px, transparent 1px);
  background-size: 20px 20px;
}

/* ── Rubber band ── */
.rubber-band {
  pointer-events: none;
  border-radius: 2px;
}

/* ═══ CANVAS CONTAINER ═══════════════════════════════════════════════════ */
.canvas-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0;
  transform-origin: top center;
  transition: transform .2s ease;
  padding-bottom: 60px;
}

/* ═══ PAGE WRAPPER ═══════════════════════════════════════════════════════ */
.page-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0;
  margin-bottom: 24px;
}

/* ── Page label ── */
.page-label-top {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  user-select: none;
  pointer-events: none;
}

.page-label-name {
  font-weight: 700;
}

.page-label-count {
  font-size: 10px;
  color: #cbd5e1;
  font-weight: 400;
}

/* ═══ PAGE ACTION BUTTONS ════════════════════════════════════════════════ */
.page-actions-bar {
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 5px 8px;
  margin-bottom: 6px;
  background: rgba(255, 255, 255, .92);
  backdrop-filter: blur(8px);
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
  opacity: 0;
  transform: translateY(-4px);
  transition: opacity .2s, transform .2s;
}

/* Show on page-wrapper hover */
.page-wrapper:hover .page-actions-bar {
  opacity: 1;
  transform: translateY(0);
}

.is-dark .page-actions-bar {
  background: rgba(26, 34, 54, .95);
  border-color: #263348;
}

.pab-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  color: #475569;
  font-size: 10px;
  font-weight: 600;
  white-space: nowrap;
  transition: all .14s;
  font-family: inherit;
}

.pab-btn:hover {
  background: #6366f1;
  color: #fff;
  border-color: #6366f1;
}

.pab-btn:disabled {
  opacity: .3;
  cursor: not-allowed;
  pointer-events: none;
}

.pab-btn--danger:hover {
  background: #ef4444;
  border-color: #ef4444;
}

.pab-sep {
  width: 1px;
  height: 18px;
  background: #e2e8f0;
  margin: 0 3px;
  flex-shrink: 0;
}

.is-dark .pab-sep {
  background: #263348;
}

.is-dark .pab-btn {
  border-color: #263348;
  color: #94a3b8;
}

/* ═══ PAGE SHEET ═════════════════════════════════════════════════════════ */
.page-sheet {
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  border-radius: 4px;
  transition: box-shadow .3s;
}

.page-sheet.page-drop-target {
  outline: 2px dashed #6366f1;
  outline-offset: 4px;
  background: rgba(99, 102, 241, .02) !important;
}

.page-watermark {
  position: absolute;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
  z-index: 5;
}

.page-header-bar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
}

.page-footer-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}

/* ═══ ELEMENTS LAYER ═════════════════════════════════════════════════════ */
.elements-layer {
  position: absolute;
  inset: 0;
}

/* ═══ ELEMENT WRAP ═══════════════════════════════════════════════════════ */
.canvas-el-wrap {
  position: absolute;
  transition: box-shadow .1s;
  transform-origin: center;
}

.canvas-el-wrap:not(.el-locked):hover {
  outline: 1.5px solid rgba(99, 102, 241, .4);
  outline-offset: 1px;
}

.canvas-el-wrap.el-selected {
  outline: 2px solid #6366f1 !important;
  outline-offset: 1px;
  box-shadow: 0 0 0 5px rgba(99, 102, 241, .1) !important;
  z-index: 50 !important;
}

.canvas-el-wrap.el-editing {
  outline: 2px solid #6366f1 !important;
  cursor: text !important;
}

.canvas-el-wrap.el-locked {
  outline-color: #f59e0b !important;
}

.canvas-el-wrap:focus-visible {
  outline: 2px solid #6366f1;
  outline-offset: 2px;
}

.el-content-wrap {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-sizing: border-box;
}

.priority-stripe {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  z-index: 20;
  animation: pStripe 2s ease-in-out infinite;
}

@keyframes pStripe {

  0%,
  100% {
    opacity: .8
  }

  50% {
    opacity: 1;
    box-shadow: 0 2px 8px currentColor
  }
}

.lock-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgba(245, 158, 11, .9);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
  z-index: 30;
  pointer-events: none;
}

/* ═══ RESIZE HANDLES ═════════════════════════════════════════════════════ */
.resize-handle {
  position: absolute;
  width: 10px;
  height: 10px;
  background: #fff;
  border: 2px solid #6366f1;
  border-radius: 2px;
  z-index: 100;
  box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
}

.handle-nw {
  top: -5px;
  left: -5px;
  cursor: nw-resize;
}

.handle-n {
  top: -5px;
  left: calc(50% - 5px);
  cursor: n-resize;
}

.handle-ne {
  top: -5px;
  right: -5px;
  cursor: ne-resize;
}

.handle-e {
  top: calc(50% - 5px);
  right: -5px;
  cursor: e-resize;
}

.handle-se {
  bottom: -5px;
  right: -5px;
  cursor: se-resize;
}

.handle-s {
  bottom: -5px;
  left: calc(50% - 5px);
  cursor: s-resize;
}

.handle-sw {
  bottom: -5px;
  left: -5px;
  cursor: sw-resize;
}

.handle-w {
  top: calc(50% - 5px);
  left: -5px;
  cursor: w-resize;
}

.rotate-handle {
  position: absolute;
  top: -34px;
  left: calc(50% - 14px);
  width: 28px;
  height: 28px;
  background: #6366f1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: crosshair;
  z-index: 100;
  color: #fff;
  font-size: 12px;
  box-shadow: 0 2px 12px rgba(99, 102, 241, .5);
  transition: transform .15s;
}

.rotate-handle:hover {
  transform: scale(1.15);
}

.el-dims-badge {
  position: absolute;
  bottom: -22px;
  left: 0;
  font-size: 9px;
  color: #64748b;
  white-space: nowrap;
  background: #fff;
  padding: 2px 7px;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
  pointer-events: none;
  z-index: 90;
  box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
}

.is-dark .el-dims-badge {
  background: #1a2236;
  border-color: #263348;
}

/* ═══ ELEMENT STYLES ═════════════════════════════════════════════════════ */
.el-text-content,
.el-richtext-content {
  outline: none;
  word-break: break-word;
  overflow: auto;
  width: 100%;
  height: 100%;
}

.el-type-quote {
  border-left: 4px solid #6366f1;
  padding-left: 12px;
  font-style: italic;
}

.el-type-blockquote {
  border-left: 4px solid #6366f1;
  padding: 12px 16px;
  background: rgba(99, 102, 241, .04);
  border-radius: 0 8px 8px 0;
}

.el-type-code {
  font-family: 'Fira Code', monospace;
  white-space: pre-wrap;
}

.el-type-highlight {
  background: #fef3c7;
  color: #92400e;
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
}

/* Image */
.el-image-wrap {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: hidden;
}

.el-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #f8fafc;
  border: 2px dashed #e2e8f0;
  border-radius: 4px;
  color: #94a3b8;
  font-size: 12px;
  cursor: pointer;
}

.el-image-placeholder i {
  font-size: 28px;
  opacity: .4;
}

.el-image-placeholder:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.el-image-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, .4);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  opacity: 0;
  transition: opacity .2s;
}

.canvas-el-wrap:hover .el-image-overlay {
  opacity: 1;
}

.el-image-overlay button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255, 255, 255, .9);
  border: none;
  cursor: pointer;
  color: #475569;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
}

.el-image-overlay button:hover {
  background: #fff;
  transform: scale(1.1);
}

/* Table */
.el-table-wrap {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.el-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.el-table-cell {
  padding: 6px 10px;
  border-bottom: 1px solid #e2e8f0;
  font-size: 11px;
}

.el-table-controls {
  display: flex;
  gap: 4px;
  padding: 6px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.el-table-controls button {
  padding: 3px 8px;
  font-size: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  background: #fff;
  cursor: pointer;
  color: #475569;
  font-family: inherit;
}

.el-table-controls button:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.el-table-controls button:disabled {
  opacity: .35;
  cursor: not-allowed;
}

/* Chart */
.el-chart-wrap {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.el-chart-title {
  font-size: 11px;
  font-weight: 700;
  text-align: center;
  padding: 4px;
  flex-shrink: 0;
}

.el-chart-canvas-wrap {
  flex: 1;
  min-height: 0;
  position: relative;
}

/* Metric */
.el-metric {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
}

.el-metric-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #64748b;
  margin-bottom: 4px;
}

.el-metric-value {
  font-size: 32px;
  font-weight: 800;
  line-height: 1;
}

.el-metric-change {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  margin-top: 6px;
}

.el-metric-change.positive {
  color: #10b981;
}

.el-metric-change.negative {
  color: #ef4444;
}

.el-metric-period {
  color: #94a3b8;
  font-size: 10px;
}

/* Progress */
.el-progress {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 7px;
  overflow: hidden;
}

.el-progress-header {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 500;
}

.el-progress-track {
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}

.el-progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width .4s ease;
}

/* Circular */
.el-circular {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.el-circular-svg {
  width: 80%;
  height: 80%;
}

.el-circular-label {
  font-size: 11px;
  color: #64748b;
  margin-top: 6px;
  text-align: center;
}

/* Sparkline */
.el-sparkline {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  overflow: hidden;
}

/* Stat row */
.el-stat-row {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-around;
}

.el-stat-item {
  text-align: center;
  flex: 1;
}

.el-stat-value {
  font-size: 24px;
  font-weight: 800;
  line-height: 1;
}

.el-stat-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: #64748b;
  margin-top: 4px;
}

/* Checklist */
.el-checklist {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 4px;
  overflow: auto;
  height: 100%;
}

.el-checklist-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.el-check-box {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  border: 2px solid #e2e8f0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 9px;
  color: #fff;
  transition: all .15s;
}

/* Timeline */
.el-timeline {
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 6px;
}

.el-tl-item {
  display: flex;
  gap: 10px;
  margin-bottom: 16px;
}

.el-tl-marker {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 14px;
  flex-shrink: 0;
}

.el-tl-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.el-tl-line {
  width: 2px;
  flex: 1;
  background: #e2e8f0;
  margin-top: 4px;
}

.el-tl-date {
  font-size: 10px;
  font-weight: 600;
  margin-bottom: 2px;
}

.el-tl-title {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 2px;
}

.el-tl-desc {
  font-size: 11px;
  color: #64748b;
}

/* Callout */
.el-callout {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  height: 100%;
  overflow: hidden;
  padding: 12px;
}

.el-callout-emoji {
  font-size: 18px;
  flex-shrink: 0;
}

.el-callout-text {
  flex: 1;
  font-size: 13px;
  outline: none;
}

/* Testimonial */
.el-testimonial {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 8px;
  overflow: hidden;
}

.el-testi-quote {
  font-size: 32px;
  opacity: .3;
  line-height: .8;
}

.el-testi-text {
  font-style: italic;
  font-size: 13px;
  line-height: 1.6;
  flex: 1;
}

.el-testi-author {
  font-weight: 600;
  font-size: 12px;
}

.el-testi-role {
  font-size: 10px;
  color: #64748b;
}

/* Signature */
.el-signature {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 8px;
}

.el-sig-line {
  flex: 1;
  border-bottom: 2px solid #cbd5e1;
}

.el-sig-name {
  font-family: Georgia, serif;
  font-style: italic;
  font-size: 18px;
  color: #94a3b8;
  margin-top: 4px;
}

.el-sig-title {
  font-size: 10px;
  color: #94a3b8;
}

/* Rating */
.el-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  height: 100%;
}

/* QR */
.el-qr {
  width: 100%;
  height: 100%;
}

.el-qr-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #f8fafc;
  border: 2px dashed #e2e8f0;
  border-radius: 8px;
  color: #94a3b8;
  font-size: 12px;
  cursor: pointer;
}

.el-qr-placeholder i {
  font-size: 32px;
  opacity: .4;
}

.el-qr-placeholder:hover {
  border-color: #6366f1;
  color: #6366f1;
}

/* Video / Map */
.el-video,
.el-map {
  width: 100%;
  height: 100%;
  overflow: hidden;
  border-radius: 4px;
}

.el-video iframe,
.el-map iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.el-video-placeholder,
.el-map-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #1e293b;
  color: #64748b;
  font-size: 12px;
  border-radius: 4px;
}

.el-video-placeholder i,
.el-map-placeholder i {
  font-size: 28px;
  opacity: .4;
}

/* Shapes */
.el-shape {
  width: 100%;
  height: 100%;
}

.el-arrow {
  width: 100%;
  height: 100%;
  overflow: visible;
}

/* Price card */
.el-price-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 16px;
  overflow: hidden;
}

.el-price-plan {
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 6px;
}

.el-price-amount {
  font-size: 32px;
  font-weight: 800;
}

.el-price-period {
  font-size: 11px;
  color: #64748b;
  margin-bottom: 10px;
}

.el-price-features {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
  font-size: 11px;
}

.el-price-features li {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 5px;
}

/* Social card */
.el-social-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-align: center;
  padding: 12px;
}

.el-social-avatar {
  font-size: 36px;
}

.el-social-name {
  font-weight: 600;
  font-size: 14px;
}

.el-social-sub {
  font-size: 11px;
  color: #64748b;
}

/* Kanban */
.el-kanban {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
  padding: 10px;
}

.el-kanban-title {
  font-weight: 600;
  font-size: 13px;
}

.el-kanban-status {
  font-size: 10px;
  font-weight: 700;
}

.el-kanban-due {
  font-size: 10px;
  color: #64748b;
}

/* TOC */
.el-toc {
  height: 100%;
  overflow: auto;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.el-toc-title {
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 8px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 6px;
}

.el-toc-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 3px 0;
  border-bottom: 1px dotted #e2e8f0;
}

.el-toc-page {
  font-weight: 700;
  min-width: 20px;
  text-align: right;
}

.el-toc-empty {
  text-align: center;
  color: #94a3b8;
  font-size: 11px;
  padding: 20px;
}

/* Misc elements */
.el-icon-el {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.el-spacer {
  width: 100%;
  height: 100%;
  border: 1px dashed #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 10px;
}

.el-badge-el {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.el-code-block {
  width: 100%;
  height: 100%;
  background: #1e293b;
  border-radius: 6px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.el-code-header {
  background: #0f172a;
  padding: 6px 12px;
  border-bottom: 1px solid #334155;
  display: flex;
}

.el-code-lang {
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: .06em;
}

.el-code-pre {
  margin: 0;
  padding: 12px;
  font-family: 'Fira Code', monospace;
  font-size: 12px;
  color: #34d399;
  white-space: pre-wrap;
  flex: 1;
  overflow: auto;
}

.el-list {
  height: 100%;
  overflow: auto;
}

.el-list ul,
.el-list ol {
  padding-left: 20px;
  margin: 0;
}

.el-list li {
  margin-bottom: 4px;
}

.el-steps {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 100%;
  overflow: hidden;
}

.el-step-item {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.el-step-num {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.el-step-label {
  font-size: 12px;
  font-weight: 500;
}

.el-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  border: 1px dashed #e2e8f0;
  border-radius: 4px;
  gap: 4px;
  font-size: 11px;
}

.el-fallback i {
  font-size: 20px;
  opacity: .3;
}

/* Empty page hint */
.page-empty-hint {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: #cbd5e1;
  opacity: .5;
  pointer-events: none;
  text-align: center;
  font-size: 13px;
}

.page-empty-hint i {
  font-size: 36px;
}

/* ═══ ADD PAGE ════════════════════════════════════════════════════════════ */
.add-page-final {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 260px;
  height: 80px;
  border: 2px dashed #cbd5e1;
  background: transparent;
  border-radius: 12px;
  cursor: pointer;
  color: #94a3b8;
  font-size: 13px;
  font-weight: 600;
  transition: all .3s;
  font-family: inherit;
  margin-top: 16px;
}

.add-page-final i {
  font-size: 20px;
}

.add-page-final:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .04);
  transform: translateY(-2px);
}

/* ═══ NAVIGATION ══════════════════════════════════════════════════════════ */
.page-nav-dots {
  position: fixed;
  bottom: 60px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, .9);
  backdrop-filter: blur(12px);
  border: 1px solid #e2e8f0;
  border-radius: 99px;
  padding: 6px 14px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
  z-index: 100;
}

.is-dark .page-nav-dots {
  background: rgba(26, 34, 54, .95);
  border-color: #263348;
}

.page-nav-arrow {
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  border-radius: 50%;
  cursor: pointer;
  color: #64748b;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
}

.page-nav-arrow:hover:not(:disabled) {
  background: #f1f5f9;
  color: #6366f1;
}

.page-nav-arrow:disabled {
  opacity: .3;
  cursor: not-allowed;
}

.page-nav-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #e2e8f0;
  cursor: pointer;
  border: none;
  padding: 0;
  transition: all .2s;
}

.page-nav-dot:hover {
  background: #94a3b8;
  transform: scale(1.3);
}

.page-nav-dot.active {
  background: #6366f1;
  box-shadow: 0 0 8px rgba(99, 102, 241, .4);
  width: 24px;
  border-radius: 99px;
}

.page-nav-dot.filled {
  border: 2px solid #94a3b8;
}

/* ═══ ZOOM BADGE ══════════════════════════════════════════════════════════ */
.zoom-badge {
  position: fixed;
  bottom: 106px;
  right: 20px;
  background: rgba(255, 255, 255, .95);
  border: 1px solid #e2e8f0;
  backdrop-filter: blur(8px);
  border-radius: 8px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 700;
  color: #0f172a;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
  transition: all .15s;
  z-index: 80;
}

.zoom-badge:hover {
  background: #6366f1;
  color: #fff;
  border-color: #6366f1;
}

.is-dark .zoom-badge {
  background: rgba(26, 34, 54, .95);
  color: #e2e8f0;
  border-color: #263348;
}

/* ═══ RESPONSIVE ══════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
  .canvas-area {
    padding: 20px 10px 100px;
  }

  .add-page-final {
    width: 180px;
    height: 60px;
  }

  .el-dims-badge {
    display: none;
  }

  .rotate-handle {
    display: none;
  }
}
</style>