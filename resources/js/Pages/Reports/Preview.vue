<!-- resources/js/Pages/Reports/Preview.vue -->
<template>
  <div class="preview-shell" :class="{ dark: isDark, 'rtl': settings.rtl }">
    
    <!-- TOP NAVIGATION BAR -->
    <header class="preview-navbar">
      <div class="navbar-left">
        <button @click="goBack" class="nav-btn" title="Back to Editor">
          <i class="fa-solid fa-chevron-left"></i>
          <span class="hidden sm:inline">Back to Editor</span>
        </button>
        <div class="divider"></div>
        <div class="report-info">
          <div class="report-icon">
            <i class="fa-solid fa-file-lines"></i>
          </div>
          <div>
            <h1 class="report-title">{{ report.title }}</h1>
            <p class="report-status">
              <span class="status-badge" :class="report.status">{{ report.status }}</span>
              <span class="dot">•</span>
              <span><i class="fa-regular fa-calendar"></i> {{ formatDate(report.updated_at) }}</span>
            </p>
          </div>
        </div>
      </div>

      <div class="navbar-center">
        <!-- Page Navigation -->
        <div class="page-nav">
          <button @click="goToPrevPage" :disabled="currentPage === 0" class="nav-arrow" title="Previous Page">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <div class="page-indicator">
            <input 
              type="number" 
              v-model.number="pageInput" 
              @change="goToPage" 
              :min="1" 
              :max="totalPages"
              class="page-input"
            />
            <span>/ {{ totalPages }}</span>
          </div>
          <button @click="goToNextPage" :disabled="currentPage === totalPages - 1" class="nav-arrow" title="Next Page">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>

        <!-- Zoom Controls -->
        <div class="zoom-controls">
          <button @click="zoomOut" class="zoom-btn" title="Zoom Out">
            <i class="fa-solid fa-minus"></i>
          </button>
          <div class="zoom-value" @click="resetZoom">{{ zoomPercent }}%</div>
          <button @click="zoomIn" class="zoom-btn" title="Zoom In">
            <i class="fa-solid fa-plus"></i>
          </button>
          <div class="divider-vertical"></div>
          <button @click="fitToScreen" class="zoom-btn" title="Fit to Screen">
            <i class="fa-solid fa-expand"></i>
          </button>
          <button @click="actualSize" class="zoom-btn" title="Actual Size">
            <i class="fa-solid fa-percent"></i>
          </button>
        </div>
      </div>

      <div class="navbar-right">
        <!-- Dark Mode Toggle -->
        <button @click="toggleDarkMode" class="nav-btn" title="Dark Mode">
          <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
        </button>

        <!-- Print Button -->
        <button @click="printReport" class="nav-btn" title="Print">
          <i class="fa-solid fa-print"></i>
          <span class="hidden sm:inline">Print</span>
        </button>

        <!-- Download PDF -->
        <a :href="downloadUrl" class="download-btn" title="Download PDF">
          <i class="fa-solid fa-download"></i>
          <span class="hidden sm:inline">Download PDF</span>
        </a>

        <!-- Share Button (if public) -->
        <button v-if="!readOnly && report.is_public" @click="copyShareLink" class="share-btn" title="Copy Share Link">
          <i class="fa-solid fa-share-alt"></i>
          <span class="hidden sm:inline">Share</span>
        </button>
      </div>
    </header>

    <!-- MAIN PREVIEW AREA -->
    <main class="preview-main" ref="previewMain">
      <div class="preview-container" ref="previewContainer">
        <!-- Loading Indicator -->
        <div v-if="loading" class="loading-overlay">
          <div class="loading-spinner">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <span>Loading preview...</span>
          </div>
        </div>

        <!-- Pages -->
        <div 
          v-for="(page, index) in pages" 
          :key="page.id"
          :ref="el => setPageRef(el, index)"
          class="preview-page-wrapper"
          :class="{ 
            'active': index === currentPage,
            'hidden-page': index !== currentPage && totalPages > 1
          }"
          :style="{
            transform: `scale(${zoom / 100})`,
            transformOrigin: 'top center',
            transition: 'transform 0.3s ease'
          }"
        >
          <!-- Page Label -->
          <div class="page-label">
            <i class="fa-regular fa-file"></i>
            <span>{{ page.label || `Page ${index + 1}` }}</span>
            <span class="page-count">{{ index + 1 }} / {{ totalPages }}</span>
          </div>

          <!-- The Actual Page -->
          <div class="preview-page" :style="getPageStyle(page)">
            <!-- Header -->
            <div v-if="settings.show_header" class="page-header" :style="headerStyle">
              <div class="header-content" :style="{ textAlign: settings.header_align || 'center' }">
                {{ settings.header_text || '' }}
              </div>
            </div>

            <!-- Watermark -->
            <div v-if="settings.watermark" class="page-watermark" :style="watermarkStyle">
              {{ settings.watermark }}
            </div>

            <!-- Elements -->
            <div class="page-elements">
              <div 
                v-for="el in getSortedElements(page.elements)" 
                :key="el.id"
                class="preview-element"
                :style="getElementStyle(el, index)"
                :class="{ 
                  'has-chart': isChartElement(el.type),
                  'has-image': el.type === 'image'
                }"
              >
                <!-- TEXT ELEMENTS -->
                <template v-if="['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight'].includes(el.type)">
                  <div 
                    class="preview-text"
                    :style="getTextStyle(el)"
                    v-html="el.content || 'Empty text element'"
                  ></div>
                </template>

                <!-- HEADING with animation -->
                <template v-else-if="el.type === 'heading'">
                  <h1 class="preview-heading" :style="getTextStyle(el)" v-html="el.content || 'Heading'"></h1>
                </template>

                <!-- SUBHEADING -->
                <template v-else-if="el.type === 'subheading'">
                  <h2 class="preview-subheading" :style="getTextStyle(el)" v-html="el.content || 'Subheading'"></h2>
                </template>

                <!-- QUOTE -->
                <template v-else-if="el.type === 'quote'">
                  <div class="preview-quote" :style="getQuoteStyle(el)">
                    <i class="fa-solid fa-quote-left quote-icon"></i>
                    <div :style="getTextStyle(el)" v-html="el.content || 'Quote text'"></div>
                  </div>
                </template>

                <!-- BLOCKQUOTE -->
                <template v-else-if="el.type === 'blockquote'">
                  <div class="preview-blockquote" :style="getBlockquoteStyle(el)">
                    <div :style="getTextStyle(el)" v-html="el.content || 'Blockquote text'"></div>
                  </div>
                </template>

                <!-- HIGHLIGHT -->
                <template v-else-if="el.type === 'highlight'">
                  <mark class="preview-highlight" :style="getHighlightStyle(el)" v-html="el.content || 'Highlighted text'"></mark>
                </template>

                <!-- LIST -->
                <template v-else-if="el.type === 'list'">
                  <div :class="['preview-list', el.styles?.listStyle === 'numbered' ? 'numbered' : 'bulleted']" :style="getListStyle(el)">
                    <component :is="el.styles?.listStyle === 'numbered' ? 'ol' : 'ul'">
                      <li v-for="(item, i) in (el.items || [])" :key="i" v-html="item"></li>
                    </component>
                  </div>
                </template>

                <!-- CHECKLIST -->
                <template v-else-if="el.type === 'checklist'">
                  <div class="preview-checklist" :style="getListStyle(el)">
                    <div v-for="(item, i) in (el.items || [])" :key="i" class="checklist-item">
                      <i :class="item.checked ? 'fa-regular fa-check-square' : 'fa-regular fa-square'" class="check-icon"></i>
                      <span :style="{ textDecoration: item.checked ? 'line-through' : 'none' }">{{ item.text }}</span>
                    </div>
                  </div>
                </template>

                <!-- CODE BLOCK -->
                <template v-else-if="el.type === 'code'">
                  <div class="preview-code" :style="getCodeStyle(el)">
                    <div class="code-header">
                      <span class="code-language">{{ el.language || 'Code' }}</span>
                      <button class="copy-code" @click="copyCode(el.content)" title="Copy code">
                        <i class="fa-regular fa-copy"></i>
                      </button>
                    </div>
                    <pre><code v-html="escapeHtml(el.content || '// Your code here')"></code></pre>
                  </div>
                </template>

                <!-- LINK -->
                <template v-else-if="el.type === 'link'">
                  <a :href="el.href" target="_blank" class="preview-link" :style="getLinkStyle(el)">
                    <i class="fa-solid fa-link"></i>
                    {{ el.content || el.href || 'Link' }}
                  </a>
                </template>

                <!-- BADGE -->
                <template v-else-if="el.type === 'badge'">
                  <div class="preview-badge" :style="getBadgeStyle(el)">
                    {{ el.content || 'Badge' }}
                  </div>
                </template>

                <!-- CALLOUT -->
                <template v-else-if="el.type === 'callout'">
                  <div class="preview-callout" :style="getCalloutStyle(el)">
                    <div class="callout-icon">{{ el.emoji || '💡' }}</div>
                    <div class="callout-content" v-html="el.content || 'Callout text'"></div>
                  </div>
                </template>

                <!-- IMAGE -->
                <template v-else-if="el.type === 'image'">
                  <div class="preview-image" :style="getImageContainerStyle(el)">
                    <img 
                      v-if="el.src" 
                      :src="el.src" 
                      :alt="el.alt || 'Report image'"
                      :style="getImageStyle(el)"
                      @error="handleImageError(el)"
                    />
                    <div v-else class="image-placeholder">
                      <i class="fa-solid fa-image"></i>
                      <span>No image selected</span>
                    </div>
                  </div>
                </template>

                <!-- TABLE -->
                <template v-else-if="el.type === 'table'">
                  <div class="preview-table-wrapper" :style="getTableWrapperStyle(el)">
                    <table class="preview-table" :style="getTableStyle(el)">
                      <thead v-if="el.columns?.length">
                        <tr>
                          <th v-for="col in el.columns" :key="col" :style="getTableHeaderStyle(el)">
                            {{ col }}
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(row, ri) in (el.data || [])" :key="ri" :style="getTableRowStyle(el, ri)">
                          <td v-for="col in (el.columns || [])" :key="col" :style="getTableCellStyle(el)">
                            {{ row[col] || '-' }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </template>

                <!-- METRIC / KPI CARD -->
                <template v-else-if="el.type === 'metric'">
                  <div class="preview-metric" :style="getMetricStyle(el)">
                    <div class="metric-label">{{ el.label || 'Metric' }}</div>
                    <div class="metric-value" :style="{ color: el.styles?.color || settings.primary_color }">
                      {{ el.value || '0' }}
                    </div>
                    <div v-if="el.change" class="metric-change" :class="el.changeType === 'positive' ? 'positive' : 'negative'">
                      <i :class="el.changeType === 'positive' ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down'"></i>
                      {{ el.change }}
                      <span class="metric-period">{{ el.changePeriod }}</span>
                    </div>
                  </div>
                </template>

                <!-- PROGRESS BAR -->
                <template v-else-if="el.type === 'progress'">
                  <div class="preview-progress" :style="getProgressContainerStyle(el)">
                    <div class="progress-header">
                      <span>{{ el.label || 'Progress' }}</span>
                      <span>{{ el.value || 0 }}%</span>
                    </div>
                    <div class="progress-track" :style="{ backgroundColor: el.styles?.trackColor || '#e2e8f0' }">
                      <div class="progress-fill" :style="{ width: (el.value || 0) + '%', backgroundColor: el.styles?.color || settings.primary_color }"></div>
                    </div>
                  </div>
                </template>

                <!-- CIRCULAR PROGRESS -->
                <template v-else-if="el.type === 'circular-progress'">
                  <div class="preview-circular-progress" :style="getCircularProgressContainerStyle(el)">
                    <svg class="circular-svg" viewBox="0 0 120 120">
                      <circle class="circular-bg" cx="60" cy="60" r="52" fill="none" :stroke="el.styles?.trackColor || '#e2e8f0'" stroke-width="8"/>
                      <circle class="circular-fill" cx="60" cy="60" r="52" fill="none" :stroke="el.styles?.color || settings.primary_color" stroke-width="8" stroke-linecap="round"
                        :stroke-dasharray="`${(el.value || 0) * 3.27} 327`" transform="rotate(-90 60 60)"/>
                      <text x="60" y="60" text-anchor="middle" dominant-baseline="middle" class="circular-text" :fill="el.styles?.color || settings.primary_color">
                        {{ el.value || 0 }}%
                      </text>
                    </svg>
                    <div v-if="el.label" class="circular-label">{{ el.label }}</div>
                  </div>
                </template>

                <!-- STAT ROW -->
                <template v-else-if="el.type === 'stat-row'">
                  <div class="preview-stat-row" :style="getStatRowStyle(el)">
                    <div v-for="(stat, i) in (el.stats || [])" :key="i" class="stat-item">
                      <div class="stat-value" :style="{ color: settings.primary_color }">{{ stat.value }}</div>
                      <div class="stat-label">{{ stat.label }}</div>
                    </div>
                  </div>
                </template>

                <!-- TIMELINE -->
                <template v-else-if="el.type === 'timeline'">
                  <div class="preview-timeline" :style="getTimelineStyle(el)">
                    <div v-for="(item, i) in (el.items || [])" :key="i" class="timeline-item">
                      <div class="timeline-marker">
                        <div class="timeline-dot"></div>
                        <div v-if="i < (el.items || []).length - 1" class="timeline-line"></div>
                      </div>
                      <div class="timeline-content">
                        <div class="timeline-title">{{ item.label }}</div>
                        <div class="timeline-date">{{ item.date }}</div>
                        <div class="timeline-desc">{{ item.desc }}</div>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- SHAPES -->
                <template v-else-if="el.type === 'rectangle'">
                  <div class="preview-shape rectangle" :style="getShapeStyle(el)"></div>
                </template>
                <template v-else-if="el.type === 'circle'">
                  <div class="preview-shape circle" :style="getShapeStyle(el)"></div>
                </template>
                <template v-else-if="el.type === 'triangle'">
                  <div class="preview-shape triangle" :style="getTriangleStyle(el)"></div>
                </template>
                <template v-else-if="el.type === 'star'">
                  <div class="preview-shape star" :style="getStarStyle(el)"></div>
                </template>
                <template v-else-if="el.type === 'line'">
                  <div class="preview-shape line" :style="getLineStyle(el)"></div>
                </template>
                <template v-else-if="el.type === 'arrow'">
                  <div class="preview-shape arrow" :style="getArrowStyle(el)"></div>
                </template>
                <template v-else-if="el.type === 'divider'">
                  <div class="preview-divider" :style="getDividerStyle(el)"></div>
                </template>

                <!-- ICON -->
                <template v-else-if="el.type === 'icon'">
                  <div class="preview-icon" :style="getIconStyle(el)">
                    <span :style="{ fontSize: (el.styles?.fontSize || 40) + 'px', color: el.styles?.color || settings.primary_color }">
                      {{ el.content || '★' }}
                    </span>
                  </div>
                </template>

                <!-- RATING -->
                <template v-else-if="el.type === 'rating'">
                  <div class="preview-rating" :style="getRatingStyle(el)">
                    <i v-for="i in 5" :key="i" 
                       :class="i <= (el.value || 0) ? 'fa-solid fa-star' : 'fa-regular fa-star'"
                       :style="{ color: i <= (el.value || 0) ? (el.styles?.color || '#f59e0b') : '#cbd5e1' }">
                    </i>
                  </div>
                </template>

                <!-- PAGE NUMBER -->
                <template v-else-if="el.type === 'pagenum'">
                  <div class="preview-pagenum" :style="getTextStyle(el)">
                    {{ index + 1 }}
                  </div>
                </template>

                <!-- DATE -->
                <template v-else-if="el.type === 'date'">
                  <div class="preview-date" :style="getTextStyle(el)">
                    {{ currentDate }}
                  </div>
                </template>

                <!-- SIGNATURE -->
                <template v-else-if="el.type === 'signature'">
                  <div class="preview-signature" :style="getSignatureStyle(el)">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ el.content || 'Signature' }}</div>
                    <div class="signature-title">{{ el.label || 'Authorized Signature' }}</div>
                  </div>
                </template>

                <!-- SOCIAL CARD -->
                <template v-else-if="el.type === 'social-card'">
                  <div class="preview-social-card" :style="getSocialCardStyle(el)">
                    <div class="social-avatar">{{ el.avatar || '👤' }}</div>
                    <div class="social-name">{{ el.content || 'User Name' }}</div>
                    <div class="social-subtitle">{{ el.subtitle || 'Title / Position' }}</div>
                  </div>
                </template>

                <!-- TESTIMONIAL -->
                <template v-else-if="el.type === 'testimonial'">
                  <div class="preview-testimonial" :style="getTestimonialStyle(el)">
                    <i class="fa-solid fa-quote-left testimonial-quote"></i>
                    <div class="testimonial-text">{{ el.content || 'Great product! Highly recommended.' }}</div>
                    <div class="testimonial-author">{{ el.author || 'John Doe' }}</div>
                    <div class="testimonial-role">{{ el.role || 'CEO' }}</div>
                  </div>
                </template>

                <!-- PRICE CARD -->
                <template v-else-if="el.type === 'price-card'">
                  <div class="preview-price-card" :style="getPriceCardStyle(el)">
                    <div class="price-plan">{{ el.plan || 'Basic Plan' }}</div>
                    <div class="price-amount">{{ el.price || '$0' }}</div>
                    <div class="price-period">{{ el.period || '/month' }}</div>
                    <ul class="price-features">
                      <li v-for="feature in (el.features || [])" :key="feature">
                        <i class="fa-solid fa-check"></i> {{ feature }}
                      </li>
                    </ul>
                  </div>
                </template>

                <!-- KANBAN CARD -->
                <template v-else-if="el.type === 'kanban'">
                  <div class="preview-kanban" :style="getKanbanStyle(el)">
                    <div class="kanban-title">{{ el.content || 'Task Title' }}</div>
                    <div class="kanban-status" :style="{ color: settings.primary_color }">{{ el.status || 'In Progress' }}</div>
                    <div class="kanban-due" v-if="el.due"><i class="fa-regular fa-calendar"></i> {{ el.due }}</div>
                  </div>
                </template>

                <!-- WATERMARK ELEMENT -->
                <template v-else-if="el.type === 'watermark'">
                  <div class="preview-watermark-element" :style="getWatermarkElementStyle(el)">
                    {{ el.content || 'CONFIDENTIAL' }}
                  </div>
                </template>

                <!-- CHARTS -->
                <template v-else-if="isChartElement(el.type)">
                  <canvas :id="'chart-' + el.id" class="preview-chart" ref="chartCanvasRefs"></canvas>
                </template>

                <!-- FALLBACK -->
                <template v-else>
                  <div class="preview-fallback">
                    <i class="fa-solid fa-cube"></i>
                    <span>{{ el.type }} element</span>
                  </div>
                </template>
              </div>
            </div>

            <!-- Footer -->
            <div v-if="settings.show_footer" class="page-footer" :style="footerStyle">
              <div class="footer-left">{{ settings.footer_left || '' }}</div>
              <div class="footer-center">{{ settings.footer_center || '' }}</div>
              <div class="footer-right">{{ formatFooterText(settings.footer_right, index) }}</div>
            </div>

            <!-- Page Number (if not in footer) -->
            <div v-if="settings.show_page_numbers && !settings.show_footer" class="page-number-bottom" :style="{ color: settings.page_number_color || '#94a3b8' }">
              {{ index + 1 }}
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Page Navigation Thumbnails (Sidebar) -->
    <div class="page-thumbnails" :class="{ collapsed: thumbnailsCollapsed }">
      <button class="thumbnails-toggle" @click="thumbnailsCollapsed = !thumbnailsCollapsed" :title="thumbnailsCollapsed ? 'Show Thumbnails' : 'Hide Thumbnails'">
        <i :class="thumbnailsCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"></i>
      </button>
      
      <div v-if="!thumbnailsCollapsed" class="thumbnails-list">
        <div class="thumbnails-header">
          <i class="fa-regular fa-file"></i>
          <span>Pages</span>
          <span class="thumbnails-count">{{ totalPages }}</span>
        </div>
        <div class="thumbnails-scroll">
          <div 
            v-for="(page, index) in pages" 
            :key="page.id"
            class="thumbnail-item"
            :class="{ active: index === currentPage }"
            @click="goToPageIndex(index)"
          >
            <div class="thumbnail-preview" :style="{ background: settings.background_color || '#fff' }">
              <div class="thumbnail-page">
                <div class="thumbnail-content" :style="{ transform: `scale(${thumbnailScale})`, transformOrigin: 'top left', width: `${thumbnailWidth}px`, height: `${thumbnailHeight}px` }">
                  <!-- Mini element representations -->
                  <div 
                    v-for="el in (page.elements || []).slice(0, 15)" 
                    :key="el.id"
                    class="thumbnail-element"
                    :style="getThumbnailElementStyle(el)"
                  ></div>
                </div>
              </div>
            </div>
            <div class="thumbnail-label">{{ page.label || `Page ${index + 1}` }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">
        <i :class="toast.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'

const props = defineProps({
  report: Object,
  readOnly: { type: Boolean, default: false }
})

// State
const isDark = ref(false)
const loading = ref(true)
const currentPage = ref(0)
const pageInput = ref(1)
const zoom = ref(100)
const thumbnailsCollapsed = ref(false)
const chartInstances = new Map()
const pageRefs = ref([])
const previewContainer = ref(null)
const previewMain = ref(null)
const chartCanvasRefs = ref({})

// Toast
const toast = reactive({ show: false, message: '', type: 'success' })

// Computed
const pages = computed(() => props.report?.content || [])
const settings = computed(() => props.report?.settings || {})
const totalPages = computed(() => pages.value.length)
const downloadUrl = computed(() => route('reports.download', props.report.slug))
const currentDate = computed(() => new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }))
const zoomPercent = computed(() => Math.round(zoom.value))
const thumbnailScale = computed(() => 80 / (settings.value.page_size === 'A4' ? 794 : 816))
const thumbnailWidth = computed(() => settings.value.page_size === 'A4' ? 794 : 816)
const thumbnailHeight = computed(() => settings.value.page_size === 'A4' ? 1123 : 1056)

// Page dimensions
const getPageDimensions = () => {
  const sizes = {
    A4: { portrait: { w: 794, h: 1123 }, landscape: { w: 1123, h: 794 } },
    Letter: { portrait: { w: 816, h: 1056 }, landscape: { w: 1056, h: 816 } },
    Legal: { portrait: { w: 816, h: 1344 }, landscape: { w: 1344, h: 816 } },
    A3: { portrait: { w: 1123, h: 1587 }, landscape: { w: 1587, h: 1123 } },
    A5: { portrait: { w: 559, h: 794 }, landscape: { w: 794, h: 559 } }
  }
  const size = sizes[settings.value.page_size]?.[settings.value.orientation] || sizes.A4.portrait
  return { width: size.w, height: size.h }
}

// Page Style
const getPageStyle = (page) => {
  const dims = getPageDimensions()
  return {
    width: dims.width + 'px',
    height: dims.height + 'px',
    backgroundColor: settings.value.background_color || '#ffffff',
    fontFamily: settings.value.font_family || "'DM Sans', sans-serif",
    borderRadius: (settings.value.page_radius || 0) + 'px',
    backgroundImage: settings.value.bg_image ? `url(${settings.value.bg_image})` : 'none',
    backgroundSize: 'cover',
    position: 'relative',
    overflow: 'hidden',
    boxShadow: '0 8px 32px rgba(0,0,0,0.15)'
  }
}

// Element Style
const getElementStyle = (el, pageIndex) => {
  const s = el.styles || {}
  return {
    position: 'absolute',
    left: (el.position?.x || 0) + 'px',
    top: (el.position?.y || 0) + 'px',
    width: (s.width || 200) + 'px',
    height: (s.height || 50) + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity ?? 100) / 100,
    transform: s.rotate ? `rotate(${s.rotate}deg)` : 'none',
    cursor: 'default'
  }
}

// Text Style
const getTextStyle = (el) => {
  const s = el.styles || {}
  return {
    fontSize: (s.fontSize || 14) + 'px',
    color: s.color || settings.value.text_color || '#1e293b',
    fontFamily: s.fontFamily || settings.value.font_family,
    fontWeight: s.fontWeight || (el.type === 'heading' ? '700' : '400'),
    fontStyle: s.fontStyle || 'normal',
    textAlign: s.textAlign || 'left',
    textDecoration: s.textDecoration || 'none',
    textTransform: s.textTransform || 'none',
    lineHeight: s.lineHeight || 1.5,
    letterSpacing: s.letterSpacing ? s.letterSpacing + 'px' : 'normal',
    backgroundColor: s.backgroundColor !== 'transparent' ? s.backgroundColor : 'transparent',
    padding: s.padding ? s.padding + 'px' : '0',
    borderRadius: s.borderRadius ? s.borderRadius + 'px' : '0',
    width: '100%',
    height: '100%',
    overflow: 'auto',
    wordBreak: 'break-word'
  }
}

// Quote Style
const getQuoteStyle = (el) => {
  return {
    ...getTextStyle(el),
    borderLeft: `4px solid ${settings.value.primary_color || '#6366f1'}`,
    paddingLeft: '16px',
    fontStyle: 'italic'
  }
}

// Blockquote Style
const getBlockquoteStyle = (el) => {
  return {
    ...getTextStyle(el),
    backgroundColor: `${settings.value.primary_color}10`,
    padding: '16px',
    borderRadius: '8px',
    borderLeft: `4px solid ${settings.value.primary_color || '#6366f1'}`
  }
}

// Highlight Style
const getHighlightStyle = (el) => {
  const s = el.styles || {}
  return {
    backgroundColor: s.backgroundColor || '#fef3c7',
    color: s.color || '#92400e',
    padding: '2px 6px',
    borderRadius: '4px',
    fontSize: (s.fontSize || 14) + 'px'
  }
}

// List Style
const getListStyle = (el) => {
  const s = el.styles || {}
  return {
    fontSize: (s.fontSize || 14) + 'px',
    color: s.color || '#1e293b',
    lineHeight: s.lineHeight || 1.6,
    paddingLeft: '20px',
    margin: 0
  }
}

// Code Style
const getCodeStyle = (el) => {
  return {
    backgroundColor: '#1e293b',
    borderRadius: '8px',
    overflow: 'hidden',
    fontSize: '12px',
    fontFamily: 'monospace'
  }
}

// Link Style
const getLinkStyle = (el) => {
  const s = el.styles || {}
  return {
    color: s.color || settings.value.primary_color,
    textDecoration: 'underline',
    cursor: 'pointer',
    fontSize: (s.fontSize || 14) + 'px',
    display: 'inline-flex',
    alignItems: 'center',
    gap: '6px'
  }
}

// Badge Style
const getBadgeStyle = (el) => {
  const s = el.styles || {}
  return {
    display: 'inline-block',
    backgroundColor: s.backgroundColor || `${settings.value.primary_color}20`,
    color: s.color || settings.value.primary_color,
    padding: '4px 12px',
    borderRadius: '999px',
    fontSize: (s.fontSize || 12) + 'px',
    fontWeight: '600'
  }
}

// Callout Style
const getCalloutStyle = (el) => {
  const s = el.styles || {}
  return {
    display: 'flex',
    gap: '12px',
    backgroundColor: s.backgroundColor || `${settings.value.primary_color}10`,
    borderRadius: (s.borderRadius || 12) + 'px',
    padding: '16px',
    borderLeft: `4px solid ${s.borderColor || settings.value.primary_color}`,
    width: '100%',
    height: '100%'
  }
}

// Image Container Style
const getImageContainerStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    backgroundColor: '#f1f5f9',
    borderRadius: (s.borderRadius || 0) + 'px',
    overflow: 'hidden'
  }
}

// Image Style
const getImageStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    objectFit: s.objectFit || 'cover',
    display: 'block'
  }
}

// Table Styles
const getTableWrapperStyle = (el) => {
  return {
    width: '100%',
    height: '100%',
    overflow: 'auto'
  }
}

const getTableStyle = (el) => {
  return {
    width: '100%',
    borderCollapse: 'collapse',
    fontSize: '12px'
  }
}

const getTableHeaderStyle = (el) => {
  const s = el.styles || {}
  return {
    backgroundColor: s.headerBg || settings.value.primary_color,
    color: s.headerColor || '#ffffff',
    padding: '8px 12px',
    textAlign: 'left',
    fontWeight: '600',
    fontSize: '11px',
    textTransform: 'uppercase',
    letterSpacing: '0.05em'
  }
}

const getTableRowStyle = (el, index) => {
  const s = el.styles || {}
  return {
    backgroundColor: index % 2 === 0 ? (s.evenRowBg || '#ffffff') : (s.oddRowBg || '#f8fafc')
  }
}

const getTableCellStyle = (el) => {
  return {
    padding: '8px 12px',
    borderBottom: '1px solid #e2e8f0'
  }
}

// Metric Style
const getMetricStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'center',
    backgroundColor: s.backgroundColor || '#f8fafc',
    borderRadius: (s.borderRadius || 12) + 'px',
    border: `1px solid ${s.borderColor || '#e2e8f0'}`,
    padding: '16px'
  }
}

// Progress Styles
const getProgressContainerStyle = (el) => {
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'center',
    gap: '8px'
  }
}

const getCircularProgressContainerStyle = (el) => {
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center'
  }
}

// Stat Row Style
const getStatRowStyle = (el) => {
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-around',
    gap: '16px'
  }
}

// Timeline Style
const getTimelineStyle = (el) => {
  return {
    width: '100%',
    height: '100%',
    overflow: 'auto',
    padding: '8px'
  }
}

// Shape Styles
const getShapeStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    backgroundColor: s.backgroundColor || settings.value.primary_color,
    borderRadius: s.borderRadius ? s.borderRadius + 'px' : '0'
  }
}

const getTriangleStyle = (el) => {
  const s = el.styles || {}
  return {
    width: 0,
    height: 0,
    borderLeft: '50px solid transparent',
    borderRight: '50px solid transparent',
    borderBottom: `100px solid ${s.backgroundColor || '#f59e0b'}`
  }
}

const getStarStyle = (el) => {
  const s = el.styles || {}
  return {
    position: 'relative',
    display: 'inline-block',
    width: 0,
    height: 0,
    marginLeft: '.9em',
    marginRight: '.9em',
    marginBottom: '1.2em',
    borderRight: '.3em solid transparent',
    borderBottom: '.7em solid ' + (s.backgroundColor || '#fc0'),
    borderLeft: '.3em solid transparent',
    fontSize: 'inherit',
    '&:before': {
      content: '""',
      position: 'absolute',
      top: '.03em',
      left: '-1.03em',
      width: 0,
      height: 0,
      borderRight: '.3em solid transparent',
      borderBottom: '.7em solid ' + (s.backgroundColor || '#fc0'),
      borderLeft: '.3em solid transparent',
      transform: 'rotate(35deg)'
    }
  }
}

const getLineStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '2px',
    backgroundColor: s.color || '#cbd5e1',
    marginTop: '50%'
  }
}

const getArrowStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '2px',
    backgroundColor: s.color || settings.value.primary_color,
    position: 'relative',
    '&:after': {
      content: '""',
      position: 'absolute',
      right: '-6px',
      top: '-4px',
      width: 0,
      height: 0,
      borderTop: '5px solid transparent',
      borderBottom: '5px solid transparent',
      borderLeft: '6px solid ' + (s.color || settings.value.primary_color)
    }
  }
}

const getDividerStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '1px',
    backgroundColor: s.color || '#e2e8f0',
    marginTop: '50%'
  }
}

// Icon Style
const getIconStyle = (el) => {
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  }
}

// Rating Style
const getRatingStyle = (el) => {
  return {
    display: 'flex',
    gap: '4px',
    fontSize: (el.styles?.fontSize || 20) + 'px'
  }
}

// Signature Style
const getSignatureStyle = (el) => {
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-between',
    padding: '8px'
  }
}

// Social Card Style
const getSocialCardStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    gap: '8px',
    backgroundColor: s.backgroundColor || '#ffffff',
    borderRadius: (s.borderRadius || 16) + 'px',
    border: `1px solid ${s.borderColor || '#e2e8f0'}`,
    padding: '16px'
  }
}

// Testimonial Style
const getTestimonialStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    gap: '12px',
    backgroundColor: s.backgroundColor || '#f8fafc',
    borderRadius: (s.borderRadius || 16) + 'px',
    border: `1px solid ${s.borderColor || '#e2e8f0'}`,
    padding: '20px'
  }
}

// Price Card Style
const getPriceCardStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    textAlign: 'center',
    backgroundColor: s.backgroundColor || '#ffffff',
    borderRadius: (s.borderRadius || 16) + 'px',
    border: `1px solid ${s.borderColor || '#e2e8f0'}`,
    padding: '20px'
  }
}

// Kanban Style
const getKanbanStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    backgroundColor: s.backgroundColor || '#ffffff',
    borderRadius: (s.borderRadius || 8) + 'px',
    border: `1px solid ${s.borderColor || '#e2e8f0'}`,
    padding: '12px',
    boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
  }
}

// Watermark Element Style
const getWatermarkElementStyle = (el) => {
  const s = el.styles || {}
  return {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: (s.fontSize || 48) + 'px',
    fontWeight: '800',
    color: s.color || '#94a3b8',
    opacity: (s.opacity || 20) / 100,
    transform: s.rotate ? `rotate(${s.rotate}deg)` : 'none',
    whiteSpace: 'nowrap'
  }
}

// Header & Footer Styles
const headerStyle = computed(() => ({
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  height: (settings.value.header_height || 60) + 'px',
  backgroundColor: settings.value.header_bg || '#1e293b',
  color: settings.value.header_text_color || '#ffffff',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 10
}))

const footerStyle = computed(() => ({
  position: 'absolute',
  bottom: 0,
  left: 0,
  right: 0,
  height: (settings.value.footer_height || 40) + 'px',
  backgroundColor: settings.value.footer_bg || 'transparent',
  color: settings.value.footer_text_color || '#94a3b8',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  padding: '0 20px',
  fontSize: (settings.value.footer_font_size || 10) + 'px',
  borderTop: `1px solid ${settings.value.primary_color}20`,
  zIndex: 10
}))

const watermarkStyle = computed(() => ({
  position: 'absolute',
  top: '50%',
  left: '50%',
  transform: `translate(-50%, -50%) rotate(${settings.value.watermark_rotate || -30}deg)`,
  fontSize: '72px',
  fontWeight: '800',
  color: settings.value.watermark_color || '#94a3b8',
  opacity: (settings.value.watermark_opacity || 10) / 100,
  whiteSpace: 'nowrap',
  pointerEvents: 'none',
  zIndex: 5
}))

// Thumbnail Element Style
const getThumbnailElementStyle = (el) => {
  const scale = thumbnailScale.value
  return {
    position: 'absolute',
    left: (el.position?.x || 0) * scale + 'px',
    top: (el.position?.y || 0) * scale + 'px',
    width: (el.styles?.width || 100) * scale + 'px',
    height: (el.styles?.height || 50) * scale + 'px',
    backgroundColor: el.styles?.backgroundColor || settings.value.primary_color,
    borderRadius: '2px',
    opacity: 0.6
  }
}

// Helper Functions
const getSortedElements = (elements) => {
  return [...(elements || [])].sort((a, b) => (a.styles?.zIndex || 1) - (b.styles?.zIndex || 1))
}

const isChartElement = (type) => {
  return ['bar-chart', 'line-chart', 'area-chart', 'pie-chart', 'doughnut-chart', 'radar-chart'].includes(type)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatFooterText = (text, pageIndex) => {
  if (!text) return ''
  return text.replace('{n}', pageIndex + 1)
}

const escapeHtml = (text) => {
  if (!text) return ''
  return text.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;'
    if (m === '<') return '&lt;'
    if (m === '>') return '&gt;'
    return m
  })
}

const copyCode = (code) => {
  navigator.clipboard.writeText(code || '')
  showToast('Code copied to clipboard!', 'success')
}

const copyShareLink = () => {
  const url = `${window.location.origin}/share/${props.report.share_token}`
  navigator.clipboard.writeText(url)
  showToast('Share link copied!', 'success')
}

const handleImageError = (el) => {
  el.src = null
  showToast('Failed to load image', 'error')
}

// Navigation Functions
const goToPageIndex = (index) => {
  currentPage.value = index
  pageInput.value = index + 1
  scrollToPage(index)
}

const goToPage = () => {
  let page = pageInput.value - 1
  if (page < 0) page = 0
  if (page >= totalPages.value) page = totalPages.value - 1
  goToPageIndex(page)
}

const goToPrevPage = () => {
  if (currentPage.value > 0) goToPageIndex(currentPage.value - 1)
}

const goToNextPage = () => {
  if (currentPage.value < totalPages.value - 1) goToPageIndex(currentPage.value + 1)
}

const scrollToPage = (index) => {
  nextTick(() => {
    const element = pageRefs.value[index]
    if (element && previewMain.value) {
      element.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  })
}

const setPageRef = (el, index) => {
  if (el) pageRefs.value[index] = el
}

// Zoom Functions
const zoomIn = () => {
  zoom.value = Math.min(zoom.value + 10, 200)
}

const zoomOut = () => {
  zoom.value = Math.max(zoom.value - 10, 25)
}

const resetZoom = () => {
  zoom.value = 100
}

const fitToScreen = () => {
  if (!previewContainer.value) return
  const containerWidth = previewMain.value?.clientWidth || 800
  const pageWidth = getPageDimensions().width
  const fitZoom = Math.floor((containerWidth / pageWidth) * 100)
  zoom.value = Math.min(fitZoom, 100)
}

const actualSize = () => {
  zoom.value = 100
}

// Dark Mode
const toggleDarkMode = () => {
  isDark.value = !isDark.value
  localStorage.setItem('preview-dark-mode', isDark.value)
  document.documentElement.classList.toggle('dark', isDark.value)
}

// Print
const printReport = () => {
  window.print()
}

// Go Back
const goBack = () => {
  router.get(route('reports.edit', props.report.slug))
}

// Toast
const showToast = (message, type = 'success') => {
  toast.message = message
  toast.type = type
  toast.show = true
  setTimeout(() => {
    toast.show = false
  }, 3000)
}

// Chart Initialization
const initCharts = () => {
  pages.value.forEach((page, pageIndex) => {
    page.elements?.forEach(el => {
      if (!isChartElement(el.type)) return
      
      nextTick(() => {
        const canvas = document.getElementById(`chart-${el.id}`)
        if (!canvas) return
        
        if (chartInstances.has(el.id)) {
          chartInstances.get(el.id).destroy()
          chartInstances.delete(el.id)
        }
        
        const ctx = canvas.getContext('2d')
        const type = {
          'bar-chart': 'bar',
          'line-chart': 'line',
          'area-chart': 'line',
          'pie-chart': 'pie',
          'doughnut-chart': 'doughnut',
          'radar-chart': 'radar'
        }[el.type] || 'bar'
        
        const isArea = el.type === 'area-chart'
        const colors = el.pieColors?.length ? el.pieColors : ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']
        
        const chart = new Chart(ctx, {
          type: type,
          data: {
            labels: el.chartData?.labels || [],
            datasets: [{
              label: el.chartTitle || 'Data',
              data: el.chartData?.values || [],
              backgroundColor: type === 'pie' || type === 'doughnut' ? colors : (isArea ? `${settings.value.primary_color}30` : settings.value.primary_color),
              borderColor: type === 'pie' || type === 'doughnut' ? colors : settings.value.primary_color,
              borderWidth: 2,
              fill: isArea,
              tension: isArea || type === 'line' ? 0.4 : 0,
              pointBackgroundColor: settings.value.primary_color,
              pointRadius: 4,
              pointHoverRadius: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { font: { size: 11 }, padding: 12, usePointStyle: true }
              },
              title: {
                display: !!el.chartTitle,
                text: el.chartTitle || '',
                font: { size: 13, weight: '600' },
                padding: { bottom: 10 }
              },
              tooltip: {
                backgroundColor: isDark.value ? '#1e293b' : '#ffffff',
                titleColor: isDark.value ? '#f1f5f9' : '#0f172a',
                bodyColor: isDark.value ? '#94a3b8' : '#64748b',
                borderColor: settings.value.primary_color,
                borderWidth: 1,
                cornerRadius: 8,
                padding: 10
              }
            },
            scales: type === 'pie' || type === 'doughnut' ? {} : {
              x: {
                grid: { color: isDark.value ? '#334155' : '#f1f5f9' },
                ticks: { color: isDark.value ? '#94a3b8' : '#64748b', font: { size: 10 } }
              },
              y: {
                grid: { color: isDark.value ? '#334155' : '#f1f5f9' },
                ticks: { color: isDark.value ? '#94a3b8' : '#64748b', font: { size: 10 }, precision: 0 }
              }
            }
          }
        })
        
        chartInstances.set(el.id, chart)
      })
    })
  })
}

// Watch for page changes to scroll
watch(currentPage, () => {
  scrollToPage(currentPage.value)
})

// Watch for zoom changes
watch(zoom, () => {
  localStorage.setItem('preview-zoom', zoom.value)
})

// Keyboard Navigation
const handleKeyDown = (e) => {
  if (e.key === 'ArrowLeft') {
    goToPrevPage()
  } else if (e.key === 'ArrowRight') {
    goToNextPage()
  } else if (e.key === 'Escape') {
    goBack()
  }
}

// Lifecycle
onMounted(() => {
  // Load saved preferences
  const savedDark = localStorage.getItem('preview-dark-mode')
  if (savedDark !== null) isDark.value = savedDark === 'true'
  
  const savedZoom = localStorage.getItem('preview-zoom')
  if (savedZoom !== null) zoom.value = parseInt(savedZoom)
  
  // Initialize
  loading.value = false
  nextTick(() => {
    initCharts()
    fitToScreen()
  })
  
  // Add event listeners
  window.addEventListener('keydown', handleKeyDown)
  window.addEventListener('resize', fitToScreen)
})

onBeforeUnmount(() => {
  chartInstances.forEach(chart => chart.destroy())
  chartInstances.clear()
  window.removeEventListener('keydown', handleKeyDown)
  window.removeEventListener('resize', fitToScreen)
})
</script>

<style scoped>
/* Preview Shell */
.preview-shell {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  background: #e2e8f0;
  font-family: 'DM Sans', sans-serif;
}

.preview-shell.dark {
  background: #0f172a;
}

/* Navbar */
.preview-navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 10px 20px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  z-index: 100;
  flex-shrink: 0;
}

.dark .preview-navbar {
  background: #1e293b;
  border-bottom-color: #334155;
}

.navbar-left,
.navbar-center,
.navbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: transparent;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s;
}

.dark .nav-btn {
  color: #94a3b8;
}

.nav-btn:hover {
  background: #f1f5f9;
  color: #6366f1;
}

.dark .nav-btn:hover {
  background: #334155;
  color: #6366f1;
}

.divider {
  width: 1px;
  height: 30px;
  background: #e2e8f0;
}

.dark .divider {
  background: #334155;
}

.divider-vertical {
  width: 1px;
  height: 24px;
  background: #e2e8f0;
  margin: 0 4px;
}

.dark .divider-vertical {
  background: #334155;
}

/* Report Info */
.report-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.report-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

.report-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.dark .report-title {
  color: #f1f5f9;
}

.report-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #64748b;
  margin-top: 2px;
}

.dark .report-status {
  color: #94a3b8;
}

.status-badge {
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.draft {
  background: #fef3c7;
  color: #d97706;
}

.status-badge.published {
  background: #d1fae5;
  color: #059669;
}

.status-badge.archived {
  background: #f1f5f9;
  color: #64748b;
}

.dot {
  font-size: 4px;
}

/* Page Navigation */
.page-nav {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 12px;
}

.dark .page-nav {
  background: #334155;
}

.nav-arrow {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.nav-arrow:hover:not(:disabled) {
  background: white;
  color: #6366f1;
}

.dark .nav-arrow:hover:not(:disabled) {
  background: #1e293b;
  color: #6366f1;
}

.nav-arrow:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-indicator {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}

.dark .page-indicator {
  color: #f1f5f9;
}

.page-input {
  width: 50px;
  padding: 4px 8px;
  text-align: center;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  font-size: 13px;
  font-weight: 600;
}

.dark .page-input {
  background: #1e293b;
  border-color: #475569;
  color: #f1f5f9;
}

/* Zoom Controls */
.zoom-controls {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 12px;
}

.dark .zoom-controls {
  background: #334155;
}

.zoom-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.zoom-btn:hover {
  background: white;
  color: #6366f1;
}

.dark .zoom-btn:hover {
  background: #1e293b;
  color: #6366f1;
}

.zoom-value {
  min-width: 50px;
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: #6366f1;
  cursor: pointer;
}

/* Download & Share Buttons */
.download-btn, .share-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border: none;
  border-radius: 10px;
  color: white;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
}

.download-btn:hover, .share-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

/* Main Preview Area */
.preview-main {
  flex: 1;
  overflow: auto;
  padding: 20px;
  position: relative;
}

.preview-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 30px;
  min-height: 100%;
}

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.loading-spinner {
  background: white;
  padding: 20px 30px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 16px;
  font-weight: 600;
  color: #6366f1;
}

.dark .loading-spinner {
  background: #1e293b;
  color: #6366f1;
}

/* Preview Page Wrapper */
.preview-page-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.3s ease;
}

.preview-page-wrapper.hidden-page {
  display: none;
}

/* Page Label */
.page-label {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: white;
  border-radius: 20px;
  margin-bottom: 12px;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.dark .page-label {
  background: #1e293b;
  color: #94a3b8;
}

.page-count {
  margin-left: auto;
  font-size: 10px;
  color: #94a3b8;
}

/* Preview Page */
.preview-page {
  position: relative;
  background: white;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  transition: box-shadow 0.3s ease;
  margin: 0 auto;
}

.preview-page-wrapper.active .preview-page {
  box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2), 0 0 0 2px #6366f1;
}

.dark .preview-page {
  background: #1e293b;
  color: #f1f5f9;
}

/* Page Elements */
.page-elements {
  position: relative;
  width: 100%;
  height: 100%;
}

.preview-element {
  position: absolute;
}

/* Text Elements */
.preview-text, .preview-heading, .preview-subheading {
  word-break: break-word;
  overflow: auto;
  width: 100%;
  height: 100%;
}

.preview-quote {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.quote-icon {
  font-size: 20px;
  color: #6366f1;
  margin-right: 8px;
  opacity: 0.5;
}

.preview-blockquote {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.preview-highlight {
  display: inline-block;
}

/* List Elements */
.preview-list ul, .preview-list ol {
  margin: 0;
  padding-left: 20px;
}

.preview-list li {
  margin-bottom: 4px;
}

/* Checklist */
.preview-checklist {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.checklist-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.check-icon {
  color: #6366f1;
  font-size: 14px;
}

/* Code Block */
.preview-code {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.code-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #0f172a;
  border-bottom: 1px solid #334155;
}

.code-language {
  font-size: 10px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.copy-code {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #64748b;
  transition: color 0.2s;
}

.copy-code:hover {
  color: #6366f1;
}

.preview-code pre {
  margin: 0;
  padding: 12px;
  background: #1e293b;
  overflow: auto;
}

.preview-code code {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  color: #34d399;
  white-space: pre-wrap;
}

/* Link */
.preview-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
}

.preview-link:hover {
  text-decoration: underline;
}

/* Badge */
.preview-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
}

/* Callout */
.preview-callout {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.callout-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.callout-content {
  flex: 1;
  word-break: break-word;
}

/* Image */
.preview-image {
  width: 100%;
  height: 100%;
}

.image-placeholder {
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
}

.dark .image-placeholder {
  background: #334155;
}

/* Table */
.preview-table-wrapper {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
}

.preview-table th,
.preview-table td {
  padding: 8px 12px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.dark .preview-table th,
.dark .preview-table td {
  border-bottom-color: #334155;
}

/* Metric / KPI */
.preview-metric {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.metric-label {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #64748b;
  margin-bottom: 4px;
}

.metric-value {
  font-size: 28px;
  font-weight: 800;
  line-height: 1;
}

.metric-change {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  margin-top: 6px;
}

.metric-change.positive {
  color: #10b981;
}

.metric-change.negative {
  color: #ef4444;
}

.metric-period {
  color: #94a3b8;
  font-weight: normal;
}

/* Progress Bar */
.preview-progress {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 500;
}

.progress-track {
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

/* Circular Progress */
.preview-circular-progress {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.circular-svg {
  width: 80%;
  height: 80%;
}

.circular-bg {
  stroke: #e2e8f0;
}

.circular-fill {
  transition: stroke-dasharray 0.5s ease;
}

.circular-text {
  font-size: 20px;
  font-weight: 700;
}

.circular-label {
  font-size: 11px;
  color: #64748b;
  margin-top: 8px;
  text-align: center;
}

/* Stat Row */
.preview-stat-row {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-around;
}

.stat-item {
  flex: 1;
  text-align: center;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
}

.stat-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #64748b;
  margin-top: 4px;
}

/* Timeline */
.preview-timeline {
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 8px;
}

.timeline-item {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}

.timeline-marker {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 24px;
}

.timeline-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #6366f1;
}

.timeline-line {
  width: 2px;
  flex: 1;
  background: #e2e8f0;
  margin-top: 4px;
}

.timeline-content {
  flex: 1;
}

.timeline-title {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 2px;
}

.timeline-date {
  font-size: 11px;
  color: #6366f1;
  margin-bottom: 4px;
}

.timeline-desc {
  font-size: 12px;
  color: #64748b;
}

/* Shapes */
.preview-shape {
  width: 100%;
  height: 100%;
}

.preview-shape.rectangle {
  background-color: #6366f1;
}

.preview-shape.circle {
  border-radius: 50%;
  background-color: #6366f1;
}

.preview-shape.triangle {
  width: 0;
  height: 0;
  border-left: 50px solid transparent;
  border-right: 50px solid transparent;
  border-bottom: 100px solid #f59e0b;
  margin: 0 auto;
}

.preview-shape.line {
  height: 2px;
  background-color: #cbd5e1;
  margin-top: 50%;
}

.preview-shape.arrow {
  height: 2px;
  background-color: #6366f1;
  position: relative;
  margin-top: 50%;
}

.preview-shape.arrow::after {
  content: '';
  position: absolute;
  right: -6px;
  top: -4px;
  width: 0;
  height: 0;
  border-top: 5px solid transparent;
  border-bottom: 5px solid transparent;
  border-left: 6px solid #6366f1;
}

.preview-divider {
  height: 1px;
  background-color: #e2e8f0;
  margin-top: 50%;
}

/* Icon */
.preview-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

/* Rating */
.preview-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  width: 100%;
  height: 100%;
}

/* Signature */
.preview-signature {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.signature-line {
  flex: 1;
  border-bottom: 2px solid #cbd5e1;
}

.signature-name {
  font-size: 16px;
  font-family: 'Georgia', serif;
  font-style: italic;
  color: #94a3b8;
  margin-top: 4px;
}

.signature-title {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 2px;
}

/* Social Card */
.preview-social-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.social-avatar {
  font-size: 48px;
  margin-bottom: 8px;
}

.social-name {
  font-weight: 600;
  font-size: 14px;
}

.social-subtitle {
  font-size: 11px;
  color: #64748b;
}

/* Testimonial */
.preview-testimonial {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.testimonial-quote {
  font-size: 24px;
  color: #6366f1;
  opacity: 0.5;
}

.testimonial-text {
  font-size: 13px;
  line-height: 1.6;
  font-style: italic;
}

.testimonial-author {
  font-weight: 600;
  font-size: 12px;
  margin-top: 8px;
}

.testimonial-role {
  font-size: 10px;
  color: #64748b;
}

/* Price Card */
.preview-price-card {
  text-align: center;
}

.price-plan {
  font-weight: 700;
  font-size: 16px;
  margin-bottom: 8px;
}

.price-amount {
  font-size: 32px;
  font-weight: 800;
  color: #6366f1;
}

.price-period {
  font-size: 11px;
  color: #64748b;
  margin-bottom: 12px;
}

.price-features {
  list-style: none;
  padding: 0;
  margin: 0;
}

.price-features li {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  margin-bottom: 6px;
}

.price-features li i {
  color: #10b981;
  font-size: 10px;
}

/* Kanban */
.preview-kanban {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.kanban-title {
  font-weight: 600;
  font-size: 13px;
}

.kanban-status {
  font-size: 10px;
  font-weight: 600;
}

.kanban-due {
  font-size: 10px;
  color: #64748b;
}

/* Watermark Element */
.preview-watermark-element {
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  white-space: nowrap;
}

/* Chart */
.preview-chart {
  width: 100% !important;
  height: 100% !important;
}

/* Fallback */
.preview-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #f1f5f9;
  border-radius: 8px;
  color: #94a3b8;
  font-size: 11px;
}

.dark .preview-fallback {
  background: #334155;
}

/* Page Header & Footer */
.page-header {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
}

.header-content {
  padding: 0 20px;
  line-height: 1.4;
  font-weight: 500;
}

.page-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 10;
  font-size: 10px;
}

.footer-left, .footer-center, .footer-right {
  flex: 1;
}

.footer-center {
  text-align: center;
}

.footer-right {
  text-align: right;
}

.page-number-bottom {
  position: absolute;
  bottom: 10px;
  right: 20px;
  font-size: 11px;
  z-index: 10;
}

/* Watermark */
.page-watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-30deg);
  font-size: 72px;
  font-weight: 800;
  white-space: nowrap;
  pointer-events: none;
  z-index: 5;
}

/* Thumbnails Sidebar */
.page-thumbnails {
  position: fixed;
  right: 0;
  top: 0;
  height: 100vh;
  background: white;
  border-left: 1px solid #e2e8f0;
  z-index: 200;
  transition: all 0.3s ease;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.05);
}

.dark .page-thumbnails {
  background: #1e293b;
  border-left-color: #334155;
}

.page-thumbnails.collapsed {
  width: 48px;
}

.page-thumbnails:not(.collapsed) {
  width: 200px;
}

.thumbnails-toggle {
  position: absolute;
  left: -12px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  border-radius: 12px;
  background: white;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  color: #64748b;
  transition: all 0.2s;
}

.dark .thumbnails-toggle {
  background: #1e293b;
  border-color: #334155;
  color: #94a3b8;
}

.thumbnails-toggle:hover {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

.thumbnails-list {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.thumbnails-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px;
  border-bottom: 1px solid #e2e8f0;
  font-size: 12px;
  font-weight: 600;
  color: #1e293b;
}

.dark .thumbnails-header {
  border-bottom-color: #334155;
  color: #f1f5f9;
}

.thumbnails-count {
  margin-left: auto;
  background: #f1f5f9;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 10px;
}

.dark .thumbnails-count {
  background: #334155;
}

.thumbnails-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.thumbnail-item {
  cursor: pointer;
  transition: all 0.2s;
}

.thumbnail-item:hover {
  transform: translateX(-4px);
}

.thumbnail-item.active {
  transform: translateX(-4px);
}

.thumbnail-item.active .thumbnail-preview {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px #6366f1;
}

.thumbnail-preview {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  aspect-ratio: 3/4;
  position: relative;
}

.dark .thumbnail-preview {
  background: #1e293b;
  border-color: #334155;
}

.thumbnail-page {
  width: 100%;
  height: 100%;
  overflow: hidden;
  position: relative;
}

.thumbnail-content {
  position: relative;
}

.thumbnail-element {
  position: absolute;
  background: #6366f1;
  border-radius: 2px;
  opacity: 0.5;
}

.thumbnail-label {
  text-align: center;
  font-size: 10px;
  margin-top: 6px;
  color: #64748b;
}

.dark .thumbnail-label {
  color: #94a3b8;
}

.thumbnail-item.active .thumbnail-label {
  color: #6366f1;
  font-weight: 600;
}

/* Toast Notification */
.toast-notification {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 500;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  animation: slideInRight 0.3s ease;
}

.toast-notification.success {
  background: #10b981;
  color: white;
}

.toast-notification.error {
  background: #ef4444;
  color: white;
}

.toast-notification.info {
  background: #3b82f6;
  color: white;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.preview-page {
  animation: fadeIn 0.4s ease-out;
}

/* Responsive */
@media (max-width: 768px) {
  .preview-navbar {
    padding: 8px 12px;
    gap: 8px;
  }
  
  .navbar-center {
    display: none;
  }
  
  .hidden {
    display: none;
  }
  
  .sm\\:inline {
    display: none;
  }
  
  .page-thumbnails:not(.collapsed) {
    width: 160px;
  }
  
  .download-btn, .share-btn {
    padding: 6px 10px;
  }
}

/* Print Styles */
@media print {
  .preview-navbar,
  .page-thumbnails,
  .page-label,
  .toast-notification {
    display: none !important;
  }
  
  .preview-main {
    padding: 0 !important;
    background: white !important;
  }
  
  .preview-page-wrapper {
    page-break-after: always;
    break-after: page;
    margin: 0 !important;
  }
  
  .preview-page-wrapper.hidden-page {
    display: block !important;
  }
  
  .preview-page {
    box-shadow: none !important;
    margin: 0 auto !important;
  }
  
  .preview-page-wrapper.active .preview-page {
    box-shadow: none !important;
    outline: none !important;
  }
}
</style>