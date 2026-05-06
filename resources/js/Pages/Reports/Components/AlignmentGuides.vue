<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   AlignmentGuides - Smart Alignment Lines                       ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <div class="alignment-guides" v-if="guides.length">
    <div
      v-for="(guide, idx) in guides"
      :key="idx"
      class="guide-line"
      :class="guide.type"
      :style="guide.style"
    >
      <span v-if="guide.label" class="guide-label">{{ guide.label }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  guides: { type: Array, default: () => [] },
  canvasRect: { type: Object, default: null },
})
</script>

<style scoped>
.alignment-guides {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 900;
}

.guide-line {
  position: absolute;
  background: #ec4899;
  opacity: 0.8;
}

.guide-line.vertical {
  width: 1px;
  top: 0;
  bottom: 0;
  box-shadow: 0 0 6px rgba(236,72,153,0.4);
}

.guide-line.horizontal {
  height: 1px;
  left: 0;
  right: 0;
  box-shadow: 0 0 6px rgba(236,72,153,0.4);
}

.guide-label {
  position: absolute;
  font-size: 9px;
  font-weight: 600;
  color: #fff;
  background: #ec4899;
  padding: 1px 5px;
  border-radius: 3px;
  white-space: nowrap;
}

.guide-line.vertical .guide-label {
  top: -16px;
  left: 4px;
}

.guide-line.horizontal .guide-label {
  left: 4px;
  top: 4px;
}
</style>