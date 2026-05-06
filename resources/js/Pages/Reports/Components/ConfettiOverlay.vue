<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   ConfettiOverlay - Celebration Animation                       ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <Teleport to="body">
    <div class="confetti-container" ref="container">
      <div
        v-for="(piece, i) in pieces"
        :key="i"
        class="confetti-piece"
        :style="piece.style"
      ></div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const emit = defineEmits(['complete'])
const container = ref(null)
const pieces = ref([])

const colors = [
  '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e',
  '#f59e0b', '#10b981', '#06b6d4', '#3b82f6',
  '#84cc16', '#f97316', '#14b8a6', '#a855f7'
]

const shapes = ['square', 'circle', 'triangle']

onMounted(() => {
  const count = 80
  for (let i = 0; i < count; i++) {
    const left = Math.random() * 100
    const delay = Math.random() * 0.5
    const duration = 2 + Math.random() * 3
    const size = 6 + Math.random() * 10
    const color = colors[Math.floor(Math.random() * colors.length)]
    const rotation = Math.random() * 360
    const shape = shapes[Math.floor(Math.random() * shapes.length)]
    
    pieces.value.push({
      style: {
        left: left + '%',
        width: size + 'px',
        height: size + 'px',
        backgroundColor: color,
        animationDelay: delay + 's',
        animationDuration: duration + 's',
        transform: `rotate(${rotation}deg)`,
        borderRadius: shape === 'circle' ? '50%' : shape === 'triangle' ? '0' : '2px',
        clipPath: shape === 'triangle' ? 'polygon(50% 0%, 0% 100%, 100% 100%)' : 'none',
      }
    })
  }
  
  setTimeout(() => emit('complete'), 4000)
})
</script>

<style scoped>
.confetti-container {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 9999;
  overflow: hidden;
}

.confetti-piece {
  position: absolute;
  top: -20px;
  animation: confettiFall linear forwards;
}

@keyframes confettiFall {
  0% {
    top: -5%;
    opacity: 1;
    transform: rotate(0deg) translateX(0);
  }
  25% {
    opacity: 1;
  }
  100% {
    top: 105%;
    opacity: 0;
    transform: rotate(720deg) translateX(100px);
  }
}
</style>