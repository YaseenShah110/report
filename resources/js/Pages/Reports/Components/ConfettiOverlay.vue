<!--
  ConfettiOverlay.vue — Canvas-based confetti burst
  • requestAnimationFrame loop, auto-stops when particles settle
  • Configurable: count, duration, colors, spread, gravity
  • Emits 'done' when animation ends
  • Memory safe: cancels RAF on unmount
  • No external deps — pure canvas
-->
<template>
    <Teleport to="body">
        <canvas ref="canvasRef" class="confetti-canvas" aria-hidden="true" />
    </Teleport>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
    active: { type: Boolean, default: false },
    count: { type: Number, default: 160 },
    duration: { type: Number, default: 3200 },
    colors: {
        type: Array,
        default: () => ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#ef4444', '#3b82f6', '#fff'],
    },
    gravity: { type: Number, default: 0.25 },
    spread: { type: Number, default: 80 },
    origin: { type: Object, default: () => ({ x: 0.5, y: 0.35 }) },
})

const emit = defineEmits(['done'])

const canvasRef = ref(null)
let ctx = null, rafId = null, particles = [], startTime = 0

function resize() {
    if (!canvasRef.value) return
    canvasRef.value.width = window.innerWidth
    canvasRef.value.height = window.innerHeight
}

function randomRange(a, b) { return a + Math.random() * (b - a) }

function makeParticle() {
    const angle = randomRange(-props.spread, props.spread) - 90
    const rad = angle * Math.PI / 180
    const speed = randomRange(4, 14)
    return {
        x: (canvasRef.value?.width || window.innerWidth) * props.origin.x,
        y: (canvasRef.value?.height || window.innerHeight) * props.origin.y,
        vx: Math.cos(rad) * speed,
        vy: Math.sin(rad) * speed,
        color: props.colors[Math.floor(Math.random() * props.colors.length)],
        w: randomRange(7, 14),
        h: randomRange(4, 8),
        rot: randomRange(0, 360),
        rotSpeed: randomRange(-4, 4),
        opacity: 1,
        shape: Math.random() > .5 ? 'rect' : 'circle',
    }
}

function launch() {
    particles = Array.from({ length: props.count }, makeParticle)
    startTime = performance.now()
    loop()
}

function loop(now = 0) {
    if (!ctx || !canvasRef.value) return
    const elapsed = now - startTime
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)

    let alive = 0
    for (const p of particles) {
        p.vy += props.gravity
        p.vx *= 0.99
        p.x += p.vx
        p.y += p.vy
        p.rot += p.rotSpeed

        const fade = Math.max(0, 1 - elapsed / props.duration)
        p.opacity = fade

        if (p.y < (canvasRef.value.height || window.innerHeight) + 40 && p.opacity > 0.01) {
            alive++
            ctx.save()
            ctx.globalAlpha = p.opacity
            ctx.translate(p.x, p.y)
            ctx.rotate(p.rot * Math.PI / 180)
            ctx.fillStyle = p.color

            if (p.shape === 'circle') {
                ctx.beginPath()
                ctx.arc(0, 0, p.w / 2, 0, Math.PI * 2)
                ctx.fill()
            } else {
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h)
            }
            ctx.restore()
        }
    }

    if (alive > 0 && elapsed < props.duration + 1000) {
        rafId = requestAnimationFrame(loop)
    } else {
        cancel()
        emit('done')
    }
}

function cancel() {
    if (rafId) { cancelAnimationFrame(rafId); rafId = null }
    if (ctx && canvasRef.value) ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
    particles = []
}

watch(() => props.active, v => {
    if (v) { resize(); launch() }
    else cancel()
})

onMounted(() => {
    if (!canvasRef.value) return
    ctx = canvasRef.value.getContext('2d')
    resize()
    window.addEventListener('resize', resize)
    if (props.active) launch()
})

onBeforeUnmount(() => {
    cancel()
    window.removeEventListener('resize', resize)
})
</script>

<style scoped>
.confetti-canvas {
    position: fixed;
    inset: 0;
    z-index: 9900;
    pointer-events: none;
    width: 100%;
    height: 100%;
}
</style>