<template>
    <Link :href="href"
        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
        :class="active
            ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-slate-200'">
        <slot/>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ href: String, active: Boolean });
</script>