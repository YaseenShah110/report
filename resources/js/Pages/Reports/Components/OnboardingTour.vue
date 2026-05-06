<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   OnboardingTour.vue - First-Time User Walkthrough             ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <Teleport to="body">
    <div class="onboarding-overlay">
      <div class="onboarding-card" :style="cardStyle">
        <div class="onboarding-step">
          <span class="step-indicator">{{ currentStep + 1 }} / {{ steps.length }}</span>
          <div class="step-dots">
            <span v-for="(step, idx) in steps" :key="idx" class="step-dot" :class="{ active: idx === currentStep, done: idx < currentStep }"></span>
          </div>
        </div>
        
        <div class="onboarding-content">
          <div class="onboarding-icon"><i :class="steps[currentStep]?.icon || 'fa-solid fa-circle'"></i></div>
          <h3>{{ steps[currentStep]?.title || '' }}</h3>
          <p>{{ steps[currentStep]?.description || '' }}</p>
        </div>
        
        <div class="onboarding-actions">
          <button v-if="currentStep > 0" class="onboarding-btn secondary" @click="prevStep">Back</button>
          <button v-if="currentStep < steps.length - 1" class="onboarding-btn primary" @click="nextStep">Next</button>
          <button v-else class="onboarding-btn primary" @click="$emit('complete')"><i class="fa-solid fa-check"></i> Get Started</button>
          <button class="onboarding-btn skip" @click="$emit('complete')">Skip Tour</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'

defineEmits(['complete'])
const currentStep = ref(0)

const steps = [
  { title: 'Welcome!', description: 'The most advanced drag-and-drop report editor. Create stunning reports in minutes.', icon: 'fa-solid fa-wand-magic-sparkles', position: 'center' },
  { title: 'Left Sidebar', description: 'Browse 45+ elements including text, charts, tables, images, shapes, and more. Drag them onto the canvas.', icon: 'fa-solid fa-shapes', position: 'left' },
  { title: 'Canvas', description: 'Drag elements to position them. Resize using corner handles. Double-click to edit text. Right-click for more options.', icon: 'fa-solid fa-vector-square', position: 'center' },
  { title: 'Right Sidebar', description: 'Customize every aspect: colors, fonts, shadows, borders, effects, content, and more.', icon: 'fa-solid fa-sliders', position: 'right' },
  { title: 'Export & Share', description: 'Export as PDF, PNG, Excel, or CSV. Share with a link or collaborate with your team.', icon: 'fa-solid fa-share-nodes', position: 'center' },
]

const cardStyle = computed(() => {
  const pos = steps[currentStep.value]?.position || 'center'
  if (pos === 'left') return { left: '260px', top: '50%', transform: 'translateY(-50%)' }
  if (pos === 'right') return { right: '300px', top: '50%', transform: 'translateY(-50%)' }
  return { left: '50%', top: '50%', transform: 'translate(-50%, -50%)' }
})

function nextStep() { currentStep.value++ }
function prevStep() { currentStep.value-- }
</script>

<style scoped>
.onboarding-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 10001; backdrop-filter: blur(2px); }
.onboarding-card { position: absolute; width: 380px; max-width: 90vw; background: var(--bg-panel, #ffffff); border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
.onboarding-step { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border, #e2e8f0); }
.step-indicator { font-size: 11px; font-weight: 600; color: var(--text-muted, #94a3b8); }
.step-dots { display: flex; gap: 4px; }
.step-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--border, #e2e8f0); transition: all 0.2s; }
.step-dot.active { background: var(--accent, #6366f1); width: 16px; border-radius: 99px; }
.step-dot.done { background: var(--success, #10b981); }
.onboarding-content { padding: 24px 20px; text-align: center; }
.onboarding-icon { width: 56px; height: 56px; border-radius: 14px; background: var(--accent-light, rgba(99,102,241,0.1)); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--accent, #6366f1); margin: 0 auto 12px; }
.onboarding-content h3 { font-size: 16px; font-weight: 700; color: var(--text-primary, #0f172a); margin-bottom: 8px; }
.onboarding-content p { font-size: 13px; color: var(--text-secondary, #475569); line-height: 1.6; }
.onboarding-actions { display: flex; gap: 8px; padding: 16px 20px; border-top: 1px solid var(--border, #e2e8f0); }
.onboarding-btn { padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s; font-family: inherit; border: 1px solid transparent; }
.onboarding-btn.primary { background: var(--accent, #6366f1); color: #fff; border-color: var(--accent, #6366f1); flex: 1; }
.onboarding-btn.primary:hover { background: var(--accent-hover, #4f46e5); }
.onboarding-btn.secondary { background: transparent; color: var(--text-secondary, #475569); border-color: var(--border, #e2e8f0); }
.onboarding-btn.secondary:hover { background: var(--bg-secondary, #f8fafc); }
.onboarding-btn.skip { background: transparent; color: var(--text-muted, #94a3b8); border: none; font-size: 11px; }
.onboarding-btn.skip:hover { color: var(--text-secondary, #475569); }
</style>