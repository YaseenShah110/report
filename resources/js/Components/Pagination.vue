<!-- resources/js/Components/Pagination.vue -->
<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1">
            <template v-for="(link, index) in links" :key="index">
                <Link v-if="link.url" :href="link.url"
                    class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                    :class="link.active 
                        ? 'bg-indigo-600 text-white' 
                        : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'"
                    v-html="link.label" />
                <span v-else class="px-3 py-1.5 text-sm text-slate-400 rounded-lg" v-html="link.label"></span>
            </template>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: Array
})
</script>