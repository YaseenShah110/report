<!--
  Preview.vue — Unified Report Preview + PDF Export View
  ═══════════════════════════════════════════════════════════════════
  • Single Inertia page served at /reports/{slug}/preview
  • ?mode=pdf  → hides toolbar, signals window.__PDF_READY__ when
                 charts + images are done (Browsershot waits for this)
  • ?print=1   → auto-triggers window.print() after fonts load
  • Browser    → shows floating toolbar (Back | Print | Export PDF)
  • All 50+ element types rendered read-only with identical styles
    to EditorCanvas — WYSIWYG: "what you see in editor = what prints"
  • Chart.js initialised on mount; PDF_READY deferred until all
    charts have finished their first render animation
  • Images tracked individually; PDF_READY also deferred until every
    <img> has loaded or errored
  • Completely isolated from app/system theme via .preview-shell reset
  ═══════════════════════════════════════════════════════════════════
-->
<template>
  <div class="preview-shell" :class="{ 'pdf-mode': isPdfMode, 'print-mode': isPrintMode }">

    <!-- ══ FLOATING TOOLBAR (browser mode only) ══════════════════════ -->
    <div v-if="!isPdfMode" class="pv-toolbar" aria-label="Preview controls">
      <button class="pv-tb-btn pv-tb-back" @click="goBack" aria-label="Back to editor">
        <i class="fa-solid fa-arrow-left" /> Back
      </button>

      <div class="pv-tb-title">
        <i class="fa-solid fa-eye" />
        <span>{{ report.title }}</span>
        <span class="pv-tb-pages">{{ report.content.length }} page{{ report.content.length !== 1 ? 's' : '' }}</span>
      </div>

      <div class="pv-tb-actions">
        <button class="pv-tb-btn pv-tb-print" @click="doPrint" aria-label="Print report">
          <i class="fa-solid fa-print" /> Print
        </button>
        <button class="pv-tb-btn pv-tb-pdf" @click="doExportPdf" :disabled="exporting" aria-label="Export as PDF">
          <i v-if="exporting" class="fa-solid fa-spinner fa-spin" />
          <i v-else class="fa-solid fa-file-pdf" />
          {{ exporting ? 'Generating…' : 'Export PDF' }}
        </button>
      </div>
    </div>

    <!-- ══ REPORT PAGES ══════════════════════════════════════════════ -->
    <main class="pv-body" :class="{ 'pv-body--pdf': isPdfMode }">
      <div v-for="(page, pi) in report.content" :key="page.id" class="pv-page" :style="getPageStyle()" :data-page="pi"
        aria-label="`Page ${pi + 1}`">
        <!-- Watermark -->
        <div v-if="settings.watermark" class="pv-watermark" :style="watermarkStyle" aria-hidden="true">{{
          settings.watermark }}</div>

        <!-- Header -->
        <div v-if="settings.show_header" class="pv-header" :style="headerStyle" aria-label="Header">
          <span>{{ settings.header_text || '' }}</span>
          <span v-if="settings.page_number_position?.startsWith('header')">{{ formatPageNum(pi) }}</span>
        </div>

        <!-- Elements -->
        <div class="pv-elements-layer">
          <template v-for="(el, ei) in (page.elements || [])" :key="el.id">
            <div v-if="el.visible !== false" class="pv-el" :style="getElStyle(el)" :aria-label="el.type">
              <!-- ── TEXT TYPES ─────────────────────────────────────── -->
              <div v-if="isTextType(el.type)" class="pv-el-text" :class="`pv-type-${el.type}`" :style="getTextStyle(el)"
                v-html="el.content || ''" />

              <!-- ── RICH TEXT ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'richtext'" class="pv-el-text" :style="getTextStyle(el)"
                v-html="el.content || ''" />

              <!-- ── IMAGE ─────────────────────────────────────────── -->
              <img v-else-if="el.type === 'image' && el.src" :src="el.src" :alt="el.alt || ''"
                :style="getImageStyle(el)" :ref="el => trackImg(el)" loading="eager" draggable="false" />
              <div v-else-if="el.type === 'image'" class="pv-img-placeholder">
                <i class="fa-solid fa-image" />
              </div>

              <!-- ── TABLE ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'table'" class="pv-table-wrap">
                <table class="pv-table">
                  <thead>
                    <tr>
                      <th v-for="col in (el.columns || [])" :key="col"
                        :style="{ background: el.styles?.headerBg || settings.primary_color || '#6366f1', color: '#fff', padding: '7px 10px', fontSize: '11px', fontWeight: '600' }">
                        {{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, ri) in (el.data || [])" :key="ri"
                      :style="{ background: ri % 2 === 0 ? (el.styles?.evenRowBg || '#fff') : (el.styles?.oddRowBg || '#f8fafc') }">
                      <td v-for="col in (el.columns || [])" :key="col" class="pv-table-cell">{{ row[col] || '' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- ── CHARTS ─────────────────────────────────────────── -->
              <div v-else-if="isChartType(el.type)" class="pv-chart-wrap"
                :ref="domEl => registerChartRef(domEl, `${pi}-${ei}`, el)">
                <div v-if="el.chartTitle" class="pv-chart-title">{{ el.chartTitle }}</div>
                <div class="pv-chart-canvas-wrap" />
              </div>

              <!-- ── METRIC ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'metric'" class="pv-metric" :style="getMetricWrapStyle(el)">
                <div class="pv-metric-label">{{ el.label || 'Metric' }}</div>
                <div class="pv-metric-value"
                  :style="{ color: el.styles?.color || settings.primary_color || '#6366f1' }">{{ el.value || '—' }}
                </div>
                <div v-if="el.change" class="pv-metric-change" :class="el.changeType || 'positive'">
                  <i :class="el.changeType === 'negative' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'" />
                  {{ el.change }} <small>{{ el.changePeriod }}</small>
                </div>
              </div>

              <!-- ── PROGRESS ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'progress'" class="pv-progress">
                <div class="pv-prog-header"><span>{{ el.label }}</span><span>{{ el.value || 0 }}%</span></div>
                <div class="pv-prog-track" :style="{ background: el.styles?.trackColor || '#e2e8f0' }">
                  <div class="pv-prog-fill"
                    :style="{ width: (el.value || 0) + '%', background: el.styles?.color || settings.primary_color || '#6366f1' }" />
                </div>
              </div>

              <!-- ── CIRCULAR PROGRESS ──────────────────────────────── -->
              <div v-else-if="el.type === 'circular-progress'" class="pv-circular">
                <svg viewBox="0 0 120 120">
                  <circle cx="60" cy="60" r="52" fill="none" :stroke="el.styles?.trackColor || '#e2e8f0'"
                    stroke-width="8" />
                  <circle cx="60" cy="60" r="52" fill="none"
                    :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="8"
                    stroke-linecap="round" :stroke-dasharray="`${(el.value || 0) * 3.27} 327`"
                    transform="rotate(-90 60 60)" />
                  <text x="60" y="66" text-anchor="middle" font-size="20" font-weight="700"
                    :fill="el.styles?.color || settings.primary_color || '#6366f1'">{{ el.value || 0 }}%</text>
                </svg>
                <div v-if="el.label" class="pv-circular-label">{{ el.label }}</div>
              </div>

              <!-- ── SPARKLINE ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'sparkline'" class="pv-sparkline">
                <svg width="100%" height="100%" viewBox="0 0 100 30" preserveAspectRatio="none">
                  <polyline :points="sparkPoints(el)" fill="none"
                    :stroke="el.styles?.color || settings.primary_color || '#6366f1'" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                  <polyline :points="sparkPoints(el) + ' 100,30 0,30'"
                    :fill="(el.styles?.color || settings.primary_color || '#6366f1') + '22'" stroke="none" />
                </svg>
              </div>

              <!-- ── STAT ROW ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'stat-row'" class="pv-stat-row">
                <div v-for="(s, si) in (el.stats || [])" :key="si" class="pv-stat-item">
                  <div class="pv-stat-value" :style="{ color: settings.primary_color || '#6366f1' }">{{ s.value }}</div>
                  <div class="pv-stat-label">{{ s.label }}</div>
                </div>
              </div>

              <!-- ── CHECKLIST ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'checklist'" class="pv-checklist">
                <div v-for="(item, ci) in (el.items || [])" :key="ci" class="pv-check-item">
                  <div class="pv-check-box"
                    :style="{ background: item.checked ? (settings.primary_color || '#6366f1') : 'transparent', borderColor: settings.primary_color || '#6366f1' }">
                    <i v-if="item.checked" class="fa-solid fa-check" style="color:#fff;font-size:8px" />
                  </div>
                  <span
                    :style="{ textDecoration: item.checked ? 'line-through' : 'none', opacity: item.checked ? .5 : 1 }">{{
                    item.text }}</span>
                </div>
              </div>

              <!-- ── TIMELINE ───────────────────────────────────────── -->
              <div v-else-if="el.type === 'timeline'" class="pv-timeline">
                <div v-for="(item, ti) in (el.items || [])" :key="ti" class="pv-tl-item">
                  <div class="pv-tl-marker">
                    <div class="pv-tl-dot" :style="{ background: settings.primary_color || '#6366f1' }" />
                    <div v-if="ti < (el.items || []).length - 1" class="pv-tl-line" />
                  </div>
                  <div class="pv-tl-content">
                    <div class="pv-tl-date" :style="{ color: settings.primary_color || '#6366f1' }">{{ item.date }}
                    </div>
                    <div class="pv-tl-label">{{ item.label }}</div>
                    <div class="pv-tl-desc">{{ item.desc }}</div>
                  </div>
                </div>
              </div>

              <!-- ── STEPS ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'steps'" class="pv-steps">
                <div v-for="(step, si) in (el.items || [])" :key="si" class="pv-step-item">
                  <div class="pv-step-num" :style="{ background: settings.primary_color || '#6366f1' }">{{ si + 1 }}
                  </div>
                  <div class="pv-step-label">{{ step.label }}</div>
                </div>
              </div>

              <!-- ── SHAPES ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'rectangle'" class="pv-shape"
                :style="{ width: '100%', height: '100%', background: el.styles?.backgroundColor || settings.primary_color || '#6366f1', borderRadius: (el.styles?.borderRadius || 0) + 'px' }" />
              <div v-else-if="el.type === 'circle'" class="pv-shape"
                :style="{ width: '100%', height: '100%', background: el.styles?.backgroundColor || settings.primary_color || '#6366f1', borderRadius: '50%' }" />
              <div v-else-if="el.type === 'divider'"
                :style="{ width: '100%', height: (el.styles?.borderWidth || 2) + 'px', background: el.styles?.color || '#e2e8f0' }" />
              <svg v-else-if="el.type === 'arrow'" viewBox="0 0 200 40" class="pv-arrow">
                <line x1="5" y1="20" x2="185" y2="20" :stroke="el.styles?.color || settings.primary_color || '#6366f1'"
                  stroke-width="2" />
                <polygon points="175,8 195,20 175,32" :fill="el.styles?.color || settings.primary_color || '#6366f1'" />
              </svg>

              <!-- ── CALLOUT ────────────────────────────────────────── -->
              <div v-else-if="el.type === 'callout'" class="pv-callout"
                :style="{ borderLeft: '4px solid ' + (el.styles?.borderColor || settings.primary_color || '#6366f1'), background: (el.styles?.borderColor || settings.primary_color || '#6366f1') + '12', borderRadius: '8px', padding: '12px' }">
                <span class="pv-callout-emoji">{{ el.emoji || '💡' }}</span>
                <span class="pv-callout-text" v-html="el.content || ''" />
              </div>

              <!-- ── TESTIMONIAL ────────────────────────────────────── -->
              <div v-else-if="el.type === 'testimonial'" class="pv-testimonial">
                <div class="pv-testi-quote">"</div>
                <p class="pv-testi-text">{{ el.content || '' }}</p>
                <div class="pv-testi-author">{{ el.author }}</div>
                <div class="pv-testi-role">{{ el.role }}</div>
              </div>

              <!-- ── SIGNATURE ──────────────────────────────────────── -->
              <div v-else-if="el.type === 'signature'" class="pv-signature">
                <div class="pv-sig-line" />
                <div class="pv-sig-name">{{ el.content }}</div>
                <div class="pv-sig-title">{{ el.label }}</div>
              </div>

              <!-- ── RATING ────────────────────────────────────────── -->
              <div v-else-if="el.type === 'rating'" class="pv-rating">
                <i v-for="i in 5" :key="i" class="fa-solid fa-star"
                  :style="{ color: i <= (el.value || 4) ? (el.styles?.color || '#f59e0b') : '#e2e8f0', fontSize: (el.styles?.fontSize || 20) + 'px' }" />
              </div>

              <!-- ── QR CODE ────────────────────────────────────────── -->
              <img v-else-if="el.type === 'qr-code' && el.qrUrl" :src="el.qrUrl" :ref="domEl => trackImg(domEl)"
                style="width:100%;height:100%;object-fit:contain" :alt="`QR: ${el.qrText || ''}`" />

              <!-- ── PAGE NUMBER ────────────────────────────────────── -->
              <div v-else-if="el.type === 'pagenum'" :style="getTextStyle(el)">{{ formatPageNum(pi) }}</div>

              <!-- ── DATE ──────────────────────────────────────────── -->
              <div v-else-if="el.type === 'date-el'" :style="getTextStyle(el)">{{ formattedDate }}</div>

              <!-- ── PRICE CARD ─────────────────────────────────────── -->
              <div v-else-if="el.type === 'price-card'" class="pv-price-card">
                <div class="pv-price-plan">{{ el.plan }}</div>
                <div class="pv-price-amount" :style="{ color: settings.primary_color || '#6366f1' }">{{ el.price }}</div>
                <div class="pv-price-period">{{ el.period }}</div>
                <ul class="pv-price-features">
                  <li v-for="f in (el.features || [])" :key="f"><i class="fa-solid fa-check"
                      :style="{ color: settings.primary_color || '#6366f1' }" /> {{ f }}</li>
                </ul>
              </div>

              <!-- ── BADGE ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'badge'" class="pv-badge"
                :style="{ background: (settings.primary_color || '#6366f1') + '18', color: settings.primary_color || '#6366f1', borderRadius: '99px', padding: '4px 14px', fontWeight: '700', fontSize: (el.styles?.fontSize || 13) + 'px' }">
                {{ el.content }}</div>

              <!-- ── CODE BLOCK ─────────────────────────────────────── -->
              <div v-else-if="el.type === 'code'" class="pv-code-block">
                <div class="pv-code-header"><span>{{ el.language || 'Code' }}</span></div>
                <pre class="pv-code-pre"><code>{{ el.content }}</code></pre>
              </div>

              <!-- ── ICON / AVATAR ──────────────────────────────────── -->
              <div v-else-if="el.type === 'icon' || el.type === 'avatar'" class="pv-icon-el"
                :style="{ fontSize: (el.styles?.fontSize || 40) + 'px' }">{{ el.content || '⭐' }}</div>

              <!-- ── SPACER ─────────────────────────────────────────── -->
              <div v-else-if="el.type === 'spacer'" />

              <!-- ── TOC ───────────────────────────────────────────── -->
              <div v-else-if="el.type === 'toc'" class="pv-toc">
                <div class="pv-toc-title">{{ el.content || 'Table of Contents' }}</div>
                <div v-for="(item, ti) in (el.tocItems || [])" :key="ti" class="pv-toc-item"
                  :style="{ paddingLeft: (item.level - 1) * 14 + 'px', fontWeight: item.level === 1 ? '600' : '400', fontSize: item.level === 1 ? '13px' : '11px' }">
                  <span>{{ item.text }}</span>
                  <span class="pv-toc-pg" :style="{ color: settings.primary_color || '#6366f1' }">{{ item.page }}</span>
                </div>
              </div>

              <!-- Fallback -->
              <div v-else class="pv-fallback"><i class="fa-solid fa-cube" /> {{ el.type }}</div>
            </div>
          </template>
        </div><!-- /pv-elements-layer -->

        <!-- Footer -->
        <div v-if="settings.show_footer" class="pv-footer" :style="footerStyle" aria-label="Footer">
          <span>{{ settings.footer_left || '' }}</span>
          <span v-if="settings.show_page_numbers !== false && settings.page_number_position?.includes('footer')">{{
            formatPageNum(pi) }}</span>
          <span>{{ (settings.footer_right || '').replace('{n}', pi + 1).replace('{total}', report.content.length)
            }}</span>
        </div>
      </div><!-- /pv-page -->
    </main>

    <!-- PDF loading indicator -->
    <div v-if="isPdfMode && !pdfReady" class="pv-loading" aria-live="polite">
      <i class="fa-solid fa-spinner fa-spin" /> Preparing document…
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

// ── Props (from Inertia) ────────────────────────────────────────────
const props = defineProps({
  report: { type: Object, required: true },
  settings: { type: Object, required: true },
})

// ── Mode detection ──────────────────────────────────────────────────
const params = new URLSearchParams(window.location.search)
const isPdfMode = params.get('mode') === 'pdf'
const isPrintMode = params.get('print') === '1'

// ── State ───────────────────────────────────────────────────────────
const exporting = ref(false)
const pdfReady = ref(false)

// Image tracking for PDF_READY signal
const imgPending = new Set()
const chartPending = new Set()

// Chart instances — keyed by `pi-ei`
const chartInstances = {}
const chartRefMap = {}

// ── Computed ─────────────────────────────────────────────────────────
const formattedDate = computed(() =>
  new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
)

const PAGE_SIZES = {
  A4: { portrait: [794, 1123], landscape: [1123, 794] },
  A3: { portrait: [1123, 1587], landscape: [1587, 1123] },
  A5: { portrait: [559, 794], landscape: [794, 559] },
  Letter: { portrait: [850, 1100], landscape: [1100, 850] },
  Legal: { portrait: [850, 1400], landscape: [1400, 850] },
}

const pageDims = computed(() => {
  const sz = props.settings.page_size || 'A4'
  const ori = props.settings.orientation || 'portrait'
  return (PAGE_SIZES[sz] || PAGE_SIZES.A4)[ori]
})

const watermarkStyle = computed(() => ({
  position: 'absolute', top: '50%', left: '50%',
  transform: `translate(-50%,-50%) rotate(${props.settings.watermark_rotate || -30}deg)`,
  fontSize: (props.settings.watermark_size || 72) + 'px', fontWeight: '900',
  color: props.settings.watermark_color || '#94a3b8',
  opacity: (props.settings.watermark_opacity || 8) / 100,
  whiteSpace: 'nowrap', pointerEvents: 'none', zIndex: 5, userSelect: 'none',
}))

const headerStyle = computed(() => ({
  position: 'absolute', top: 0, left: 0, right: 0,
  height: (props.settings.header_height || 50) + 'px',
  background: props.settings.header_color || '#1e293b',
  color: props.settings.header_text_color || '#ffffff',
  display: 'flex', alignItems: 'center', justifyContent: 'space-between',
  padding: `0 ${props.settings.margin || 40}px`,
  fontSize: '12px', fontWeight: '600', zIndex: 10,
}))

const footerStyle = computed(() => ({
  position: 'absolute', bottom: 0, left: 0, right: 0,
  height: (props.settings.footer_height || 36) + 'px',
  display: 'flex', alignItems: 'center', justifyContent: 'space-between',
  padding: `0 ${props.settings.margin || 40}px`,
  fontSize: '10px', color: props.settings.footer_color || '#94a3b8',
  borderTop: `1px solid ${props.settings.primary_color || '#6366f1'}20`, zIndex: 10,
}))

// ── Style helpers ────────────────────────────────────────────────────
function getPageStyle() {
  const [w, h] = pageDims.value
  const m = props.settings.margin || 40
  return {
    width: w + 'px', minHeight: h + 'px', position: 'relative',
    background: props.settings.background_color || '#ffffff',
    backgroundImage: props.settings.bg_image ? `url(${props.settings.bg_image})` : 'none',
    backgroundSize: 'cover',
    fontFamily: props.settings.font_family || 'DM Sans, sans-serif',
    fontSize: (props.settings.font_size || 14) + 'px',
    color: props.settings.text_color || '#1e293b',
    padding: m + 'px',
    boxSizing: 'border-box',
    direction: props.settings.rtl ? 'rtl' : 'ltr',
    pageBreakAfter: 'always', breakAfter: 'page',
  }
}

function getElStyle(el) {
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
    left: (el.position?.x || 0) + 'px', top: (el.position?.y || 0) + 'px',
    width: (s.width || 200) + 'px', height: (s.height || 80) + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: transforms.join(' ') || 'none',
    filter: filters.join(' ') || 'none',
    background: bg,
    borderRadius: (s.borderRadius || 0) + 'px',
    border: s.borderWidth ? `${s.borderWidth}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : 'none',
    boxShadow: s.boxShadow || 'none',
    mixBlendMode: s.mixBlendMode || 'normal',
    overflow: 'hidden', boxSizing: 'border-box',
  }
}

function getTextStyle(el) {
  const s = el.styles || {}
  let textBG = 'none', webkitBGClip = 'unset', webkitFillColor = 'unset'
  if (s.textGradient && s.textGradientFrom && s.textGradientTo) {
    textBG = `linear-gradient(135deg, ${s.textGradientFrom}, ${s.textGradientTo})`
    webkitBGClip = 'text'; webkitFillColor = 'transparent'
  }
  return {
    fontFamily: s.fontFamily || props.settings.font_family || 'DM Sans',
    fontSize: (s.fontSize || 14) + 'px', fontWeight: s.fontWeight || '400',
    fontStyle: s.fontStyle || 'normal', textDecoration: s.textDecoration || 'none',
    color: s.color || props.settings.text_color || '#1e293b',
    textAlign: s.textAlign || 'left', lineHeight: s.lineHeight || 1.5,
    letterSpacing: (s.letterSpacing || 0) + 'px',
    textTransform: s.textTransform || 'none',
    columnCount: s.columns || 1, columnGap: '20px',
    background: textBG, WebkitBackgroundClip: webkitBGClip, WebkitTextFillColor: webkitFillColor,
    width: '100%', height: '100%', wordBreak: 'break-word',
  }
}

function getImageStyle(el) {
  return {
    width: '100%', height: '100%',
    objectFit: el.styles?.objectFit || 'cover',
    borderRadius: (el.styles?.borderRadius || 0) + 'px',
    display: 'block',
    filter: ({ grayscale: 'grayscale(100%)', sepia: 'sepia(80%)', vintage: 'sepia(50%) contrast(85%)', blur: 'blur(2px)', bright: 'brightness(130%)' }[el.styles?.imageFilter] || 'none'),
  }
}

function getMetricWrapStyle(el) {
  const s = el.styles || {}
  return { background: s.backgroundColor || '#f8fafc', borderRadius: (s.borderRadius || 12) + 'px', border: '1px solid #e2e8f0', padding: '14px', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'center' }
}

// ── Type checks ──────────────────────────────────────────────────────
const TEXT_TYPES = ['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight', 'badge', 'code', 'link', 'callout', 'richtext', 'list', 'pagenum', 'date-el', 'watermark']
const CHART_TYPES = ['bar-chart', 'line-chart', 'area-chart', 'pie-chart', 'doughnut-chart', 'radar-chart']
const isTextType = type => TEXT_TYPES.includes(type)
const isChartType = type => CHART_TYPES.includes(type)

// ── Page number formatting ───────────────────────────────────────────
function formatPageNum(pi) {
  const n = pi + (props.settings.page_number_start || 1)
  const total = props.report.content.length
  const style = props.settings.page_number_style || 'decimal'
  if (style === 'of') return `Page ${n} of ${total}`
  if (style === 'roman') return toRoman(n)
  if (style === 'alpha') return String.fromCharCode(64 + n)
  return String(n)
}

function toRoman(num) {
  const map = [[1000, 'M'], [900, 'CM'], [500, 'D'], [400, 'CD'], [100, 'C'], [90, 'XC'], [50, 'L'], [40, 'XL'], [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I']]
  return map.reduce((r, [v, s]) => { while (num >= v) { r += s; num -= v } return r }, '')
}

// ── Sparkline ────────────────────────────────────────────────────────
function sparkPoints(el) {
  const data = el.sparkData || [3, 5, 4, 8, 6, 9, 5, 10, 7, 8]
  const max = Math.max(...data)
  return data.map((v, i) => `${(i / (data.length - 1)) * 100},${30 - (v / max) * 25}`).join(' ')
}

// ── Image tracking for PDF_READY ─────────────────────────────────────
function trackImg(imgEl) {
  if (!imgEl || !isPdfMode) return
  if (imgEl.complete) return
  imgPending.add(imgEl)
  imgEl.addEventListener('load', () => { imgPending.delete(imgEl); checkReady() }, { once: true })
  imgEl.addEventListener('error', () => { imgPending.delete(imgEl); checkReady() }, { once: true })
}

// ── Charts ───────────────────────────────────────────────────────────
function registerChartRef(domEl, key, el) {
  if (!domEl) return
  chartRefMap[key] = { domEl, el }
}

function renderAllCharts() {
  Object.entries(chartRefMap).forEach(([key, { domEl, el }]) => {
    const wrap = domEl.querySelector('.pv-chart-canvas-wrap')
    if (!wrap) return
    if (chartInstances[key]) { try { chartInstances[key].destroy() } catch { } }

    let canvas = wrap.querySelector('canvas')
    if (!canvas) {
      canvas = document.createElement('canvas')
      canvas.style.cssText = 'width:100%;height:100%;display:block'
      wrap.appendChild(canvas)
    }

    const typeMap = { 'bar-chart': 'bar', 'line-chart': 'line', 'area-chart': 'line', 'pie-chart': 'pie', 'doughnut-chart': 'doughnut', 'radar-chart': 'radar' }
    const chartType = typeMap[el.type] || 'bar'
    const labels = el.chartData?.labels || ['Q1', 'Q2', 'Q3', 'Q4']
    const values = el.chartData?.values || [25, 40, 35, 55]
    const primary = el.chartColor || props.settings.primary_color || '#6366f1'
    const PIE_COLORS = [primary, '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#84cc16']
    const isMulti = ['pie', 'doughnut'].includes(chartType)

    if (isPdfMode) chartPending.add(key)

    try {
      chartInstances[key] = new Chart(canvas.getContext('2d'), {
        type: chartType,
        data: {
          labels,
          datasets: [{
            label: el.chartTitle || 'Data', data: values,
            backgroundColor: isMulti ? PIE_COLORS : primary + '99',
            borderColor: isMulti ? PIE_COLORS : primary,
            borderWidth: 2, fill: el.type === 'area-chart', tension: 0.35,
          }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          animation: {
            duration: isPdfMode ? 0 : 500,  // no animation in PDF mode
            onComplete: () => { chartPending.delete(key); checkReady() },
          },
          plugins: {
            legend: { display: isMulti, position: 'bottom' },
          },
        },
      })
    } catch (e) {
      chartPending.delete(key)
      checkReady()
    }
  })
}

// ── PDF_READY signal ─────────────────────────────────────────────────
function checkReady() {
  if (!isPdfMode) return
  if (imgPending.size === 0 && chartPending.size === 0) {
    pdfReady.value = true
    window.__PDF_READY__ = true
  }
}

// ── Navigation ───────────────────────────────────────────────────────
function goBack() {
  router.visit(route('reports.edit', props.report.slug))
}

async function doExportPdf() {
  exporting.value = true
  try {
    const res = await window.axios.post(route('reports.export-pdf', props.report.slug), {}, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url; a.download = (props.report.title || 'report') + '.pdf'; a.click()
    URL.revokeObjectURL(url)
  } catch { alert('PDF export failed. Please try again.') }
  finally { exporting.value = false }
}

function doPrint() {
  window.print()
}

// ── Lifecycle ────────────────────────────────────────────────────────
onMounted(async () => {
  // Render charts
  await new Promise(r => setTimeout(r, 80))  // DOM settle
  renderAllCharts()

  // If no images/charts pending, signal immediately
  if (isPdfMode) {
    if (imgPending.size === 0 && chartPending.size === 0) {
      window.__PDF_READY__ = true
      pdfReady.value = true
    }
  }

  // Auto-print mode (when opened with ?print=1)
  if (isPrintMode) {
    await new Promise(r => setTimeout(r, 800))  // wait for fonts
    window.print()
  }
})

onBeforeUnmount(() => {
  Object.values(chartInstances).forEach(c => { try { c.destroy() } catch { } })
})
</script>

<style>
/* ═══ GLOBAL RESET WITHIN PREVIEW SHELL ══════════════════════════════ */
.preview-shell {
  all: unset;
  display: block;
  font-family: 'DM Sans', 'Inter', system-ui, sans-serif;
  background: #e8eef5;
  min-height: 100vh;
  box-sizing: border-box;
}

/* Dark canvas background in browser mode */
.preview-shell:not(.pdf-mode) {
  background: #d1d9e6;
}

/* ═══ TOOLBAR ════════════════════════════════════════════════════════ */
.pv-toolbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 54px;
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  background: rgba(255, 255, 255, .92);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
  gap: 16px;
}

.pv-tb-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  flex: 1;
  justify-content: center;
}

.pv-tb-pages {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 400;
}

.pv-tb-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #fff;
  color: #475569;
  cursor: pointer;
  font-size: 12px;
  font-family: inherit;
  font-weight: 500;
  transition: all .15s;
  white-space: nowrap;
}

.pv-tb-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, .04);
}

.pv-tb-back:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.pv-tb-pdf {
  background: #6366f1;
  color: #fff;
  border-color: #6366f1;
}

.pv-tb-pdf:hover {
  background: #4f46e5;
  border-color: #4f46e5;
  color: #fff;
}

.pv-tb-pdf:disabled {
  opacity: .6;
  cursor: not-allowed;
}

.pv-tb-actions {
  display: flex;
  gap: 8px;
}

/* ═══ BODY ═══════════════════════════════════════════════════════════ */
.pv-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80px 32px 60px;
  gap: 28px;
}

.pv-body--pdf {
  padding: 0;
  gap: 0;
  align-items: flex-start;
  background: #fff;
}

/* ═══ PAGE ═══════════════════════════════════════════════════════════ */
.pv-page {
  flex-shrink: 0;
  box-shadow: 0 6px 32px rgba(0, 0, 0, .15);
}

.pv-body--pdf .pv-page {
  box-shadow: none;
}

/* ═══ ELEMENTS LAYER ═════════════════════════════════════════════════ */
.pv-elements-layer {
  position: absolute;
  inset: 0;
}

/* ═══ ELEMENT STYLES ═════════════════════════════════════════════════ */
.pv-el {
  position: absolute;
  overflow: hidden;
  box-sizing: border-box;
}

.pv-el-text {
  width: 100%;
  height: 100%;
  word-break: break-word;
  overflow: auto;
}

.pv-type-quote {
  border-left: 4px solid #6366f1;
  padding-left: 12px;
  font-style: italic;
}

.pv-type-blockquote {
  border-left: 4px solid #6366f1;
  padding: 12px 16px;
  background: rgba(99, 102, 241, .04);
  border-radius: 0 8px 8px 0;
}

.pv-type-highlight {
  background: #fef3c7;
  color: #92400e;
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
}

.pv-img-placeholder {
  width: 100%;
  height: 100%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 24px;
  border: 2px dashed #e2e8f0;
  border-radius: 4px;
}

.pv-table-wrap {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.pv-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.pv-table-cell {
  padding: 6px 10px;
  border-bottom: 1px solid #e2e8f0;
  font-size: 11px;
}

.pv-chart-wrap {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.pv-chart-title {
  font-size: 11px;
  font-weight: 700;
  text-align: center;
  padding: 4px;
  flex-shrink: 0;
}

.pv-chart-canvas-wrap {
  flex: 1;
  min-height: 0;
  position: relative;
}

.pv-metric {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 14px;
  height: 100%;
}

.pv-metric-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #64748b;
  margin-bottom: 4px;
}

.pv-metric-value {
  font-size: 32px;
  font-weight: 800;
  line-height: 1;
}

.pv-metric-change {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  margin-top: 6px;
}

.pv-metric-change.positive {
  color: #10b981;
}

.pv-metric-change.negative {
  color: #ef4444;
}

.pv-progress {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 7px;
  padding: 8px;
}

.pv-prog-header {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 500;
}

.pv-prog-track {
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}

.pv-prog-fill {
  height: 100%;
  border-radius: 4px;
}

.pv-circular {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.pv-circular svg {
  width: 80%;
  height: 80%;
}

.pv-circular-label {
  font-size: 11px;
  color: #64748b;
  margin-top: 6px;
  text-align: center;
}

.pv-sparkline {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
}

.pv-stat-row {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-around;
}

.pv-stat-item {
  text-align: center;
  flex: 1;
}

.pv-stat-value {
  font-size: 24px;
  font-weight: 800;
  line-height: 1;
}

.pv-stat-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: #64748b;
  margin-top: 4px;
}

.pv-checklist {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 4px;
  height: 100%;
}

.pv-check-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.pv-check-box {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 2px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.pv-timeline {
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 6px;
}

.pv-tl-item {
  display: flex;
  gap: 10px;
  margin-bottom: 16px;
}

.pv-tl-marker {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 14px;
  flex-shrink: 0;
}

.pv-tl-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.pv-tl-line {
  width: 2px;
  flex: 1;
  background: #e2e8f0;
  margin-top: 4px;
}

.pv-tl-date {
  font-size: 10px;
  font-weight: 600;
  margin-bottom: 2px;
}

.pv-tl-label {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 2px;
}

.pv-tl-desc {
  font-size: 11px;
  color: #64748b;
}

.pv-steps {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 100%;
  padding: 8px;
}

.pv-step-item {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.pv-step-num {
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

.pv-step-label {
  font-size: 12px;
  font-weight: 500;
}

.pv-arrow {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.pv-callout {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  height: 100%;
}

.pv-callout-emoji {
  font-size: 18px;
  flex-shrink: 0;
}

.pv-callout-text {
  flex: 1;
  font-size: 13px;
}

.pv-testimonial {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 8px;
}

.pv-testi-quote {
  font-size: 32px;
  opacity: .3;
  line-height: .8;
}

.pv-testi-text {
  font-style: italic;
  font-size: 13px;
  line-height: 1.6;
  flex: 1;
}

.pv-testi-author {
  font-weight: 600;
  font-size: 12px;
}

.pv-testi-role {
  font-size: 10px;
  color: #64748b;
}

.pv-signature {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 8px;
}

.pv-sig-line {
  flex: 1;
  border-bottom: 2px solid #cbd5e1;
}

.pv-sig-name {
  font-family: Georgia, serif;
  font-style: italic;
  font-size: 18px;
  color: #94a3b8;
  margin-top: 4px;
}

.pv-sig-title {
  font-size: 10px;
  color: #94a3b8;
}

.pv-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  height: 100%;
}

.pv-price-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 16px;
  overflow: hidden;
}

.pv-price-plan {
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 6px;
}

.pv-price-amount {
  font-size: 32px;
  font-weight: 800;
}

.pv-price-period {
  font-size: 11px;
  color: #64748b;
  margin-bottom: 10px;
}

.pv-price-features {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
  font-size: 11px;
}

.pv-price-features li {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 5px;
}

.pv-badge {
  display: inline-flex;
  align-items: center;
  height: 100%;
}

.pv-code-block {
  width: 100%;
  height: 100%;
  background: #1e293b;
  border-radius: 6px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.pv-code-header {
  background: #0f172a;
  padding: 5px 12px;
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
}

.pv-code-pre {
  margin: 0;
  padding: 12px;
  font-family: monospace;
  font-size: 12px;
  color: #34d399;
  white-space: pre-wrap;
  flex: 1;
  overflow: auto;
}

.pv-icon-el {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pv-toc {
  height: 100%;
  overflow: auto;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.pv-toc-title {
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 8px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 6px;
}

.pv-toc-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 3px 0;
  border-bottom: 1px dotted #e2e8f0;
}

.pv-toc-pg {
  font-weight: 700;
}

.pv-watermark {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
}

.pv-header {
  position: absolute;
}

.pv-footer {
  position: absolute;
}

.pv-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 11px;
  gap: 5px;
}

.pv-loading {
  position: fixed;
  inset: 0;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #64748b;
  gap: 8px;
  z-index: 9999;
}

/* ═══ PRINT MEDIA ════════════════════════════════════════════════════ */
@media print {
  .pv-toolbar {
    display: none !important;
  }

  .pv-body {
    padding: 0 !important;
    gap: 0 !important;
    background: transparent !important;
  }

  .preview-shell {
    background: transparent !important;
  }

  .pv-page {
    box-shadow: none !important;
    page-break-after: always;
    break-after: page;
  }

  .pv-page:last-child {
    page-break-after: auto;
    break-after: auto;
  }

  @page {
    margin: 0;
    size: A4 portrait;
  }
}
</style>