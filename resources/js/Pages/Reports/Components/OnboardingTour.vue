<!--
  OnboardingTour.vue — First-run guided tour
  • Step-by-step spotlight tour with tooltips
  • Highlights target element, dims rest
  • Progress dots + prev/next/skip
  • Persists completion in localStorage
  • Dark mode aware
  • Keyboard: arrow keys, Esc to skip
  • Memory safe
-->
<template>
  <Teleport to="body">
    <Transition name="ot-fade">
      <div v-if="visible && !done" class="ot-root" role="dialog" aria-modal="true" aria-label="Getting started tour"
        @keydown="onKey">

        <!-- Spotlight mask (SVG clip) -->
        <svg class="ot-mask" aria-hidden="true">
          <defs>
            <mask id="ot-spotlight">
              <rect width="100%" height="100%" fill="white" />
              <rect :x="spot.x - 8" :y="spot.y - 8" :width="spot.w + 16" :height="spot.h + 16" :rx="spot.r + 8"
                fill="black" />
            </mask>
          </defs>
          <rect width="100%" height="100%" fill="rgba(0,0,0,.65)" mask="url(#ot-spotlight)" />
        </svg>

        <!-- Spotlight ring -->
        <div class="ot-ring" :style="{
          left: spot.x - 10 + 'px',
          top: spot.y - 10 + 'px',
          width: spot.w + 20 + 'px',
          height: spot.h + 20 + 'px',
          borderRadius: spot.r + 10 + 'px',
        }" aria-hidden="true" />

        <!-- Tooltip card -->
        <Transition name="ot-card">
          <div class="ot-card" :class="[isDark && 'ot-dark', `ot-pos--${tipPos}`]" :style="cardStyle" :key="current">
            <!-- Step badge -->
            <div class="ot-step-badge" aria-label="Step {{ current + 1 }} of {{ STEPS.length }}">
              <span class="ot-step-icon"
                :style="{ background: STEPS[current].color + '20', color: STEPS[current].color }">
                <i :class="STEPS[current].icon" aria-hidden="true" />
              </span>
              <span class="ot-step-num">{{ current + 1 }} / {{ STEPS.length }}</span>
            </div>

            <h3 class="ot-title">{{ STEPS[current].title }}</h3>
            <p class="ot-body">{{ STEPS[current].body }}</p>

            <!-- Tip callout -->
            <div v-if="STEPS[current].tip" class="ot-tip" aria-label="Tip">
              <i class="fa-solid fa-lightbulb ot-tip-icon" aria-hidden="true" />
              <span>{{ STEPS[current].tip }}</span>
            </div>

            <!-- Progress dots -->
            <div class="ot-dots" role="tablist" aria-label="Tour progress">
              <button v-for="(_, i) in STEPS" :key="i" class="ot-dot" :class="i === current && 'ot-dot--active'"
                @click="goTo(i)" :aria-label="`Go to step ${i + 1}`" role="tab" :aria-selected="i === current" />
            </div>

            <!-- Buttons -->
            <div class="ot-buttons">
              <button class="ot-btn ot-btn--ghost" @click="skip" aria-label="Skip tour">
                Skip tour
              </button>
              <div class="ot-btn-group">
                <button v-if="current > 0" class="ot-btn ot-btn--outline" @click="prev" aria-label="Previous step">
                  <i class="fa-solid fa-arrow-left" /> Prev
                </button>
                <button class="ot-btn ot-btn--primary" @click="next"
                  :aria-label="current === STEPS.length - 1 ? 'Finish tour' : 'Next step'">
                  <template v-if="current === STEPS.length - 1">
                    <i class="fa-solid fa-check" /> Done!
                  </template>
                  <template v-else>
                    Next <i class="fa-solid fa-arrow-right" />
                  </template>
                </button>
              </div>
            </div>

            <!-- Arrow pointer -->
            <div class="ot-arrow" :class="`ot-arrow--${tipPos}`" aria-hidden="true" />
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  visible: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'complete'])

// ── Tour steps ─────────────────────────────────────────────────────────
const STEPS = [
  {
    target: '.left-sidebar',
    title: 'Element Library',
    body: 'Browse 50+ elements — headings, charts, KPI cards, tables, images, and more. Drag onto the canvas or double-click to add instantly.',
    tip: 'Use the search bar at the top to find any element quickly.',
    icon: 'fa-solid fa-shapes',
    color: '#6366f1',
  },
  {
    target: '.editor-canvas',
    title: 'The Canvas',
    body: 'This is your report. Click to select elements, drag to move, and use the handles to resize or rotate. Snap-to-grid keeps everything aligned.',
    tip: 'Hold Shift while resizing to maintain proportions.',
    icon: 'fa-solid fa-vector-square',
    color: '#10b981',
  },
  {
    target: '.right-sidebar',
    title: 'Properties Panel',
    body: 'Style, position, typography, effects, and content editors live here. Every property of the selected element is one click away.',
    tip: 'Use the "Style" tab to apply gradients and shadows.',
    icon: 'fa-solid fa-sliders',
    color: '#f59e0b',
  },
  {
    target: '.top-toolbar',
    title: 'Top Toolbar',
    body: 'Save, export, zoom, toggle grid/snap/rulers, open the AI assistant, and switch themes — all from the toolbar ribbon.',
    tip: 'Press Ctrl+K anytime to open the Command Palette.',
    icon: 'fa-solid fa-bars',
    color: '#06b6d4',
  },
  {
    target: '.ai-toggle-btn',
    title: 'AI Assistant',
    body: 'Generate content, enhance writing, summarize text, and create chart data using AI. The panel is draggable and can be minimized.',
    tip: 'Press Ctrl+Alt+A to toggle the AI panel.',
    icon: 'fa-solid fa-wand-magic-sparkles',
    color: '#8b5cf6',
  },
  {
    target: '.export-btn',
    title: 'Export & Share',
    body: 'Export your report as PDF, PNG, Excel, or CSV. Copy a share link or email directly from the export menu.',
    tip: 'PDF export preserves all fonts and chart quality.',
    icon: 'fa-solid fa-file-export',
    color: '#ec4899',
  },
]

// ── State ──────────────────────────────────────────────────────────────
const current = ref(0)
const done = ref(localStorage.getItem('rg_tour_done') === '1')
const spot = ref({ x: 0, y: 0, w: 0, h: 0, r: 12 })

// ── Spotlight calculation ──────────────────────────────────────────────
function calcSpot(selector) {
  const el = document.querySelector(selector)
  if (!el) {
    spot.value = { x: window.innerWidth / 2 - 100, y: window.innerHeight / 2 - 60, w: 200, h: 120, r: 12 }
    return
  }
  const r = el.getBoundingClientRect()
  spot.value = { x: r.left, y: r.top, w: r.width, h: r.height, r: 12 }
}

// ── Tooltip positioning ────────────────────────────────────────────────
const TIP_OFFSETS = { right: 20, left: 20, top: -12, bottom: 20 }
const CARD_W = 340

const tipPos = computed(() => {
  const s = spot.value
  const margin = 24
  if (s.x + s.w + CARD_W + margin < window.innerWidth) return 'right'
  if (s.x - CARD_W - margin > 0) return 'left'
  if (s.y + s.h + 240 < window.innerHeight) return 'bottom'
  return 'top'
})

const cardStyle = computed(() => {
  const s = spot.value
  const pos = tipPos.value
  const style = {}
  const margin = 20

  if (pos === 'right') {
    style.left = s.x + s.w + margin + 'px'
    style.top = Math.max(margin, s.y + s.h / 2 - 140) + 'px'
  } else if (pos === 'left') {
    style.left = s.x - CARD_W - margin + 'px'
    style.top = Math.max(margin, s.y + s.h / 2 - 140) + 'px'
  } else if (pos === 'bottom') {
    style.left = Math.max(margin, Math.min(s.x + s.w / 2 - CARD_W / 2, window.innerWidth - CARD_W - margin)) + 'px'
    style.top = s.y + s.h + margin + 'px'
  } else {
    style.left = Math.max(margin, Math.min(s.x + s.w / 2 - CARD_W / 2, window.innerWidth - CARD_W - margin)) + 'px'
    style.top = s.y - 280 + 'px'
  }
  return style
})

// ── Navigation ─────────────────────────────────────────────────────────
function goTo(i) {
  current.value = i
  nextTick(() => calcSpot(STEPS[i].target))
}
function next() {
  if (current.value < STEPS.length - 1) goTo(current.value + 1)
  else complete()
}
function prev() { if (current.value > 0) goTo(current.value - 1) }
function skip() { complete() }
function complete() {
  localStorage.setItem('rg_tour_done', '1')
  done.value = true
  emit('complete')
  emit('close')
}

function onKey(e) {
  if (e.key === 'ArrowRight') next()
  if (e.key === 'ArrowLeft') prev()
  if (e.key === 'Escape') skip()
}

// ── Init ───────────────────────────────────────────────────────────────
watch(() => props.visible, v => {
  if (v && !done.value) {
    current.value = 0
    nextTick(() => calcSpot(STEPS[0].target))
  }
})

const resizeObs = typeof ResizeObserver !== 'undefined'
  ? new ResizeObserver(() => calcSpot(STEPS[current.value]?.target))
  : null

onMounted(() => {
  if (props.visible && !done.value) nextTick(() => calcSpot(STEPS[0].target))
  resizeObs?.observe(document.body)
})
onBeforeUnmount(() => resizeObs?.disconnect())
</script>

<style scoped>
.ot-root {
  position: fixed;
  inset: 0;
  z-index: 9800;
  pointer-events: all;
  font-family: 'DM Sans', system-ui, sans-serif;
}

/* Mask */
.ot-mask {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}

/* Ring */
.ot-ring {
  position: absolute;
  pointer-events: none;
  border: 2px solid rgba(99, 102, 241, .8);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, .15), 0 0 40px rgba(99, 102, 241, .3);
  animation: otRingPulse 2s ease-in-out infinite;
  transition: all .4s cubic-bezier(.16, 1, .3, 1);
}

@keyframes otRingPulse {

  0%,
  100% {
    box-shadow: 0 0 0 4px rgba(99, 102, 241, .15), 0 0 40px rgba(99, 102, 241, .3);
  }

  50% {
    box-shadow: 0 0 0 8px rgba(99, 102, 241, .08), 0 0 60px rgba(99, 102, 241, .2);
  }
}

/* Card */
.ot-card {
  position: absolute;
  z-index: 1;
  width: 340px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  box-shadow: 0 24px 80px rgba(0, 0, 0, .2), 0 4px 20px rgba(0, 0, 0, .1);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: left .4s cubic-bezier(.16, 1, .3, 1), top .4s cubic-bezier(.16, 1, .3, 1);
}

.ot-dark {
  background: #1a2236;
  border-color: #263348;
  color: #e2e8f0;
}

/* top gradient line */
.ot-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #6366f1, #8b5cf6);
  border-radius: 18px 18px 0 0;
}

/* Step badge */
.ot-step-badge {
  display: flex;
  align-items: center;
  gap: 8px;
}

.ot-step-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}

.ot-step-num {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: #94a3b8;
}

.ot-title {
  font-size: 15px;
  font-weight: 800;
  color: inherit;
  margin: 0;
}

.ot-body {
  font-size: 12px;
  line-height: 1.65;
  color: #475569;
  margin: 0;
}

.ot-dark .ot-body {
  color: #94a3b8;
}

/* Tip */
.ot-tip {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  padding: 8px 12px;
  background: rgba(99, 102, 241, .07);
  border-radius: 8px;
  border: 1px solid rgba(99, 102, 241, .15);
}

.ot-tip-icon {
  color: #6366f1;
  font-size: 12px;
  margin-top: 2px;
  flex-shrink: 0;
}

.ot-tip span {
  font-size: 11px;
  color: #6366f1;
  line-height: 1.5;
  font-weight: 500;
}

/* Dots */
.ot-dots {
  display: flex;
  gap: 5px;
  justify-content: center;
}

.ot-dot {
  width: 6px;
  height: 6px;
  border-radius: 99px;
  background: #e2e8f0;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: all .2s;
}

.ot-dot--active {
  width: 20px;
  background: #6366f1;
}

.ot-dark .ot-dot {
  background: #334155;
}

/* Buttons */
.ot-buttons {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-top: 2px;
}

.ot-btn-group {
  display: flex;
  gap: 6px;
}

.ot-btn {
  padding: 7px 14px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all .15s;
  font-family: inherit;
  border: none;
}

.ot-btn--ghost {
  background: transparent;
  color: #94a3b8;
}

.ot-btn--ghost:hover {
  color: #64748b;
  background: #f1f5f9;
}

.ot-btn--outline {
  background: transparent;
  border: 1px solid #e2e8f0;
  color: #475569;
}

.ot-btn--outline:hover {
  background: #f8fafc;
}

.ot-btn--primary {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  box-shadow: 0 4px 14px rgba(99, 102, 241, .35);
}

.ot-btn--primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, .45);
}

.ot-dark .ot-btn--outline {
  border-color: #334155;
  color: #94a3b8;
}

/* Arrow */
.ot-arrow {
  position: absolute;
  width: 12px;
  height: 12px;
  background: #fff;
  border: 1px solid #e2e8f0;
  transform: rotate(45deg);
}

.ot-dark .ot-arrow {
  background: #1a2236;
  border-color: #263348;
}

.ot-arrow--right {
  left: -7px;
  top: 50%;
  margin-top: -6px;
  border-right: none;
  border-top: none;
}

.ot-arrow--left {
  right: -7px;
  top: 50%;
  margin-top: -6px;
  border-left: none;
  border-bottom: none;
}

.ot-arrow--bottom {
  top: -7px;
  left: 50%;
  margin-left: -6px;
  border-right: none;
  border-bottom: none;
}

.ot-arrow--top {
  bottom: -7px;
  left: 50%;
  margin-left: -6px;
  border-left: none;
  border-top: none;
}

/* Transitions */
.ot-fade-enter-active {
  animation: otFadeIn .3s ease;
}

.ot-fade-leave-active {
  animation: otFadeIn .2s ease reverse;
}

@keyframes otFadeIn {
  from {
    opacity: 0
  }

  to {
    opacity: 1
  }
}

.ot-card-enter-active {
  animation: otCardIn .3s cubic-bezier(.16, 1, .3, 1);
}

.ot-card-leave-active {
  animation: otCardIn .18s ease reverse;
}

@keyframes otCardIn {
  from {
    opacity: 0;
    transform: scale(.94) translateY(8px)
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0)
  }
}
</style>