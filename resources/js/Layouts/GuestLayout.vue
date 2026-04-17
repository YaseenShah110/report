<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-slate-50 dark:bg-slate-900 py-12 px-4">
        <div class="mb-8">
            <Link :href="route('login')" class="flex items-center gap-2.5">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-xl">ReportGen</span>
            </Link>
        </div>
        <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
            <slot/>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>