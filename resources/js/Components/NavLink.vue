<!-- resources/js/Components/NavLink.vue -->
<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    href: String,
    active: Boolean
})

const classes = computed(() => {
    return props.active
        ? 'inline-flex items-center px-3 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg transition-colors'
        : 'inline-flex items-center px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors'
})
</script>

<script>
import { computed } from 'vue'
export default { setup() { return { computed } } }
</script>