<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   ToastContainer.vue - Toast Notifications                     ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
    <Teleport to="body">
        <div class="toast-container">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="toast-item"
                    :class="toast.type"
                >
                    <span class="toast-icon">
                        <i
                            v-if="toast.type === 'success'"
                            class="fa-solid fa-circle-check"
                        ></i>
                        <i
                            v-else-if="toast.type === 'error'"
                            class="fa-solid fa-circle-xmark"
                        ></i>
                        <i
                            v-else-if="toast.type === 'warning'"
                            class="fa-solid fa-triangle-exclamation"
                        ></i>
                        <i v-else class="fa-solid fa-circle-info"></i>
                    </span>
                    <span class="toast-message">{{ toast.message }}</span>
                    <button
                        class="toast-close"
                        @click="$emit('remove', toast.id)"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div
                        class="toast-progress"
                        :style="{ animationDuration: duration + 'ms' }"
                    ></div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
defineProps({
    toasts: { type: Array, default: () => [] },
    duration: { type: Number, default: 3000 },
});
defineEmits(["remove"]);
</script>

<style scoped>
.toast-container {
    position: fixed;
    bottom: 40px;
    right: 24px;
    z-index: 2000;
    display: flex;
    flex-direction: column-reverse;
    gap: 8px;
    pointer-events: none;
}
.toast-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    pointer-events: auto;
    position: relative;
    overflow: hidden;
    min-width: 280px;
    max-width: 420px;
    backdrop-filter: blur(10px);
}
.toast-item.success {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
}
.toast-item.error {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: #fff;
}
.toast-item.warning {
    background: linear-gradient(135deg, #d97706, #f59e0b);
    color: #fff;
}
.toast-item.info {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #fff;
}
.toast-icon {
    font-size: 16px;
    flex-shrink: 0;
}
.toast-message {
    flex: 1;
}
.toast-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
    flex-shrink: 0;
}
.toast-close:hover {
    background: rgba(255, 255, 255, 0.3);
}
.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.4);
    animation: toastProgress linear forwards;
}
@keyframes toastProgress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
.toast-enter-active {
    animation: toastIn 0.3s ease;
}
.toast-leave-active {
    animation: toastOut 0.3s ease forwards;
}
@keyframes toastIn {
    from {
        opacity: 0;
        transform: translateX(40px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}
@keyframes toastOut {
    to {
        opacity: 0;
        transform: translateX(40px) scale(0.9);
    }
}
</style>
