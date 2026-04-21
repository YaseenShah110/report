<template>
    <button @click="$emit('toggle')"
        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 flex-shrink-0"
        :class="value ? 'bg-indigo-600' : dark ? 'bg-slate-700' : 'bg-slate-200'">
        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-200"
            :class="value ? 'translate-x-5' : 'translate-x-1'"></span>
    </button>
</template>

<script setup>
defineProps({ value: Boolean, dark: Boolean });
defineEmits(["toggle"]);
</script>