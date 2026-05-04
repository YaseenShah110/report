<!--
  ConfirmationModal.vue - Reusable Confirmation Dialog
  -----------------------------------------------------------
  A reusable modal component for confirming destructive actions.
  Used across the application for delete confirmations.
  
  Props:
    - show: Boolean - controls visibility
    - title: String - modal title (default: "Are you sure?")
    - message: String - confirmation message
    - icon: String - Font Awesome icon class
    - confirmText: String - text for confirm button (default: "Confirm")
    - cancelText: String - text for cancel button (default: "Cancel")
    - loading: Boolean - shows spinner on confirm button
  
  Events:
    - close: emitted when modal is dismissed
    - confirm: emitted when user confirms the action
-->
<template>
  <!-- Teleport to body to avoid z-index and overflow issues -->
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
      
      <!-- Backdrop - click to close -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in overflow-hidden">
        
        <!-- Modal Body -->
        <div class="p-4 sm:p-6 text-center">
          
          <!-- Icon Container -->
          <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-3 sm:mb-4">
            <i :class="icon || 'fa-solid fa-triangle-exclamation'" class="text-red-600 dark:text-red-400 text-xl sm:text-2xl"></i>
          </div>
          
          <!-- Title -->
          <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white mb-1 sm:mb-2">
            {{ title || 'Are you sure?' }}
          </h3>
          
          <!-- Message -->
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-5 sm:mb-6">
            {{ message || 'This action cannot be undone.' }}
          </p>
          
          <!-- Action Buttons -->
          <div class="flex gap-2 sm:gap-3">
            <!-- Cancel Button -->
            <button 
              @click="$emit('close')" 
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-xs sm:text-sm font-medium"
            >
              {{ cancelText || 'Cancel' }}
            </button>
            
            <!-- Confirm Button -->
            <button 
              @click="$emit('confirm')" 
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5"
              :disabled="loading"
            >
              <!-- Loading spinner -->
              <i v-if="loading" class="fa-solid fa-spinner fa-spin text-xs"></i>
              {{ confirmText || 'Confirm' }}
            </button>
          </div>
          
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
/**
 * ConfirmationModal Script
 * 
 * This component is purely presentational - it emits events
 * and the parent component handles the actual logic.
 */

// Props definition with defaults
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Are you sure?'
  },
  message: {
    type: String,
    default: 'This action cannot be undone.'
  },
  icon: {
    type: String,
    default: 'fa-solid fa-triangle-exclamation'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// Events emitted by this component
defineEmits(['close', 'confirm'])
</script>

<style scoped>
/* Animation for modal entrance */
@keyframes scale-in {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
.animate-scale-in {
  animation: scale-in 0.2s ease-out forwards;
}
</style>