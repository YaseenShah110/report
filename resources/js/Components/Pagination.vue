<!--
  Pagination.vue - Reusable Pagination Component
  -----------------------------------------------------------
  Displays pagination links with responsive design.
  Used across all list pages (Users, Tasks, Reports, etc.)
  
  Props:
    - links: Array of pagination links from Laravel paginator
    - from: First item number on current page
    - to: Last item number on current page
    - total: Total items across all pages
-->
<template>
  <!-- Only render if there are more than 3 links (prev, pages, next) -->
  <div v-if="links.length > 3" class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
    
    <!-- Results counter - shown below pagination on mobile -->
    <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 order-2 sm:order-1">
      Showing {{ from }} to {{ to }} of {{ total }} results
    </div>
    
    <!-- Pagination buttons - shown above counter on mobile -->
    <div class="flex items-center gap-1 order-1 sm:order-2">
      <!-- Loop through each link (prev, page numbers, next) -->
      <Link
        v-for="(link, index) in links"
        :key="index"
        :href="link.url || '#'"
        :class="[
          'px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg transition-all duration-200 min-w-[36px] sm:min-w-[40px] text-center',
          // Active page - indigo background
          link.active
            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25 font-semibold'
            : // Disabled (no URL) - grayed out
            !link.url
            ? 'text-slate-400 dark:text-slate-600 cursor-not-allowed opacity-50'
            : // Inactive page - hover effect
            'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'
        ]"
        :preserve-scroll="true"
        v-html="link.label"
      />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

// Props definition
defineProps({
  links: {
    type: Array,
    default: () => []
  },
  from: {
    type: Number,
    default: 0
  },
  to: {
    type: Number,
    default: 0
  },
  total: {
    type: Number,
    default: 0
  }
})
</script>