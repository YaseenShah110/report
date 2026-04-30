<!-- resources/js/Layouts/GuestLayout.vue -->
<template>
  <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-900 dark:to-slate-800 py-12 px-4">
    <div class="mb-8">
      <Link :href="route('login')" class="flex items-center gap-2.5">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
          <i class="fa-solid fa-chart-line text-white text-xl"></i>
        </div>
        <span class="font-bold text-slate-900 dark:text-white text-2xl">ReportGen</span>
      </Link>
    </div>
    <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl p-8 animate-fade-in">
      <slot/>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.5s ease-out forwards;
}
</style>