<!--
  GuestLayout.vue - Guest/Landing Layout
  -----------------------------------------------------------
  Layout wrapper for non-authenticated pages (Login, Register, etc.)
  Features centered card with gradient background.
  Shows app logo and name above the content slot.
-->
<template>
  <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-900 dark:to-slate-800 py-8 sm:py-12 px-4">
    
    <!-- App Logo & Name -->
    <div class="mb-6 sm:mb-8">
      <Link :href="route('login')" class="flex items-center gap-2 sm:gap-2.5">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
          <i class="fa-solid fa-chart-line text-white text-lg sm:text-xl"></i>
        </div>
        <span class="font-bold text-slate-900 dark:text-white text-xl sm:text-2xl">ReportGen</span>
      </Link>
    </div>
    
    <!-- Content Card -->
    <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl p-6 sm:p-8 animate-fade-in">
      <!-- Page content injected here (login form, register form, etc.) -->
      <slot />
    </div>
    
    <!-- Optional footer links -->
    <div class="mt-4 sm:mt-6 text-center">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
/* Fade in animation for the card */
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.5s ease-out forwards;
}
</style>