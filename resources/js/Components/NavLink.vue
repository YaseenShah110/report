<template>
    <Link
        :href="href"
        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
        :class="
            active
                ? 'bg-indigo-300 dark:bg-slate-100/30 text-indigo-700 dark:text-white'
                : 'text-slate-600 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-slate-200'
        "
    >
        <slot />
    </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
defineProps({ href: String, active: Boolean });
</script>
