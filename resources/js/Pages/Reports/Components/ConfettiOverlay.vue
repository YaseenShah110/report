<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   ConfettiOverlay.vue - Celebration Animation on Publish       ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
    <Teleport to="body">
        <div class="confetti-container">
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
import { ref, onMounted } from "vue";

const emit = defineEmits(["complete"]);
const pieces = ref([]);
const colors = [
    "#6366f1",
    "#8b5cf6",
    "#ec4899",
    "#f43f5e",
    "#f59e0b",
    "#10b981",
    "#06b6d4",
    "#3b82f6",
    "#84cc16",
    "#f97316",
    "#14b8a6",
    "#a855f7",
];

onMounted(() => {
    for (let i = 0; i < 80; i++) {
        pieces.value.push({
            style: {
                left: Math.random() * 100 + "%",
                width: 6 + Math.random() * 10 + "px",
                height: 6 + Math.random() * 10 + "px",
                backgroundColor:
                    colors[Math.floor(Math.random() * colors.length)],
                animationDelay: Math.random() * 0.5 + "s",
                animationDuration: 2 + Math.random() * 3 + "s",
                borderRadius: Math.random() > 0.5 ? "50%" : "2px",
            },
        });
    }
    setTimeout(() => emit("complete"), 4000);
});
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
