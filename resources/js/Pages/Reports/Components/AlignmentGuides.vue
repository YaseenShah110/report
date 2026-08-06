<!--
  AlignmentGuides.vue — snap-to guide lines overlay
  ═══════════════════════════════════════════════════════════════════
  Renders thin guide lines over the canvas while an element is being
  dragged. EditorCanvas computes the guide data (horizontal/vertical
  proximity to other elements' edges/centers) and passes it as props.

  This component is purely presentational — no logic, no state.
  It uses SVG positioned absolutely over the active page.
  ═══════════════════════════════════════════════════════════════════
-->
<template>
  <svg v-if="guides.length" class="alignment-guides" :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`"
    aria-hidden="true" style="pointer-events:none">
    <line v-for="(g, i) in guides" :key="i" :x1="g.type === 'v' ? g.pos : 0" :y1="g.type === 'h' ? g.pos : 0"
      :x2="g.type === 'v' ? g.pos : width" :y2="g.type === 'h' ? g.pos : height" stroke="#f43f5e" stroke-width="1"
      stroke-dasharray="4 3" opacity="0.85" />
    <!-- Intersection dots -->
    <circle v-for="(g, i) in intersections" :key="`dot-${i}`" :cx="g.x" :cy="g.y" r="3" fill="#f43f5e" opacity="0.9" />
  </svg>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /**
   * guides: Array of { type: 'h'|'v', pos: number }
   * h = horizontal line at y=pos, v = vertical line at x=pos
   */
  guides: { type: Array, default: () => [] },
  width: { type: Number, default: 794 },
  height: { type: Number, default: 1123 },
})

// Find intersections between all h and v guides (dots at crossings)
const intersections = computed(() => {
  const hGuides = props.guides.filter(g => g.type === 'h')
  const vGuides = props.guides.filter(g => g.type === 'v')
  const dots = []
  hGuides.forEach(h => vGuides.forEach(v => dots.push({ x: v.pos, y: h.pos })))
  return dots
})
</script>

<style scoped>
.alignment-guides {
  position: absolute;
  top: 0;
  left: 0;
  overflow: visible;
  z-index: 200;
}
</style>