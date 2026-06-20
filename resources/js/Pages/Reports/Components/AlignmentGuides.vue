<!--
  AlignmentGuides.vue — Visual alignment guides overlay
  • Shows pixel-perfect guides when dragging/resizing elements
  • Snap-to-grid functionality
  • Cross-hair guides at element boundaries
-->
<template>
  <div v-if="showGuides" class="alignment-guides">
    <!-- Vertical guides -->
    <div v-for="x in verticalGuides" :key="`v-${x}`" class="guide guide-vertical" :style="{ left: x + 'px' }" />

    <!-- Horizontal guides -->
    <div v-for="y in horizontalGuides" :key="`h-${y}`" class="guide guide-horizontal" :style="{ top: y + 'px' }" />

    <!-- Center guides -->
    <div v-if="showCenterGuide" class="center-guide-v" :style="{ left: centerX + 'px' }" />
    <div v-if="showCenterGuide" class="center-guide-h" :style="{ top: centerY + 'px' }" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  dragElement: { type: Object, default: null },  // { x, y, w, h }
  pageElements: { type: Array, default: () => [] },
  pageDims: { type: Array, default: () => [794, 1123] },
  enableSnap: { type: Boolean, default: true },
})

const emit = defineEmits(['snap-x', 'snap-y'])

const SNAP_DISTANCE = 8  // pixels to snap from

const showGuides = computed(() => !!props.dragElement)

const verticalGuides = computed(() => {
  if (!props.dragElement) return []
  const guides = new Set()

  // Current element edges
  guides.add(props.dragElement.x)
  guides.add(props.dragElement.x + props.dragElement.w)
  guides.add(props.dragElement.x + props.dragElement.w / 2)

  // Page edges
  guides.add(0)
  guides.add(props.pageDims[0])
  guides.add(props.pageDims[0] / 2)

  // Other element edges
  props.pageElements.forEach(el => {
    if (!el || el === props.dragElement) return
    guides.add(el.position?.x || 0)
    guides.add((el.position?.x || 0) + (el.styles?.width || 0))
    guides.add((el.position?.x || 0) + (el.styles?.width || 0) / 2)
  })

  return Array.from(guides).sort((a, b) => a - b)
})

const horizontalGuides = computed(() => {
  if (!props.dragElement) return []
  const guides = new Set()

  guides.add(props.dragElement.y)
  guides.add(props.dragElement.y + props.dragElement.h)
  guides.add(props.dragElement.y + props.dragElement.h / 2)

  guides.add(0)
  guides.add(props.pageDims[1])
  guides.add(props.pageDims[1] / 2)

  props.pageElements.forEach(el => {
    if (!el || el === props.dragElement) return
    guides.add(el.position?.y || 0)
    guides.add((el.position?.y || 0) + (el.styles?.height || 0))
    guides.add((el.position?.y || 0) + (el.styles?.height || 0) / 2)
  })

  return Array.from(guides).sort((a, b) => a - b)
})

const centerX = computed(() => props.pageDims[0] / 2)
const centerY = computed(() => props.pageDims[1] / 2)
const showCenterGuide = computed(() => showGuides.value)
</script>

<style scoped>
.alignment-guides {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 999;
}

.guide {
  position: absolute;
  background: #6366f1;
  opacity: 0.5;
}

.guide-vertical {
  width: 1px;
  height: 100%;
}

.guide-horizontal {
  height: 1px;
  width: 100%;
}

.center-guide-v {
  position: absolute;
  width: 1px;
  height: 100%;
  background: #ec4899;
  opacity: 0.6;
  stroke-dasharray: 4, 4;
  z-index: -1;
}

.center-guide-h {
  position: absolute;
  height: 1px;
  width: 100%;
  background: #ec4899;
  opacity: 0.6;
  stroke-dasharray: 4, 4;
  z-index: -1;
}
</style>