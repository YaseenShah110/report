<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('reports.index')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create New Report</h2>
            </div>
        </template>

        <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-full">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Step 1: Pick template -->
                <div v-if="step === 1">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Choose a Template</h3>
                        <p class="text-slate-500 dark:text-slate-400">Start with a professional template or build from scratch</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <!-- Blank -->
                        <div @click="selectTemplate(null)"
                            :class="!selectedTemplate && templateSelected ? 'ring-2 ring-indigo-500 border-indigo-300 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-indigo-300 dark:hover:border-indigo-600'"
                            class="cursor-pointer rounded-2xl border-2 flex flex-col items-center justify-center min-h-[200px] gap-3 transition-all hover:shadow-md dark:hover:shadow-slate-900">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div class="text-center">
                                <p class="font-semibold text-slate-700 dark:text-slate-300">Blank Canvas</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Start from scratch</p>
                            </div>
                        </div>

                        <!-- Templates from DB -->
                        <div v-for="tpl in templates" :key="tpl.id"
                            @click="selectTemplate(tpl)"
                            :class="selectedTemplate?.id === tpl.id ? 'ring-2 ring-indigo-500 border-indigo-300' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600'"
                            class="cursor-pointer rounded-2xl border-2 bg-white dark:bg-slate-800 overflow-hidden transition-all hover:shadow-md dark:hover:shadow-slate-900">
                            <div class="h-32 relative" :style="{ background: tpl.cover_gradient || 'linear-gradient(135deg,#6366f1,#8b5cf6)' }">
                                <div v-if="tpl.badge" class="absolute top-2.5 right-2.5">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full" :class="tpl.badge==='Popular'?'bg-amber-400 text-amber-900':'bg-indigo-500 text-white'">{{ tpl.badge }}</span>
                                </div>
                            </div>
                            <div class="p-3.5">
                                <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ tpl.name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ tpl.description }}</p>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <span v-for="tag in (tpl.tags||[]).slice(0,3)" :key="tag"
                                        class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded text-[10px]">{{ tag }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button @click="step=2; templateSelected=true"
                            class="flex items-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                            Continue
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Configure -->
                <div v-else class="max-w-lg mx-auto">
                    <div class="text-center mb-8">
                        <div v-if="selectedTemplate" class="w-16 h-16 rounded-2xl mx-auto mb-3 overflow-hidden" :style="{ background: selectedTemplate.cover_gradient }"/>
                        <div v-else class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ selectedTemplate?.name ?? 'Blank Canvas' }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure your report settings</p>
                    </div>

                    <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Report Title <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" ref="titleRef" required
                                class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="e.g. Q4 2024 Annual Report…"
                                :class="form.errors.title ? 'border-red-400' : ''"/>
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Page Size</label>
                                <select v-model="form.initial_settings.page_size" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="A4">A4</option><option value="Letter">Letter</option><option value="Legal">Legal</option><option value="A3">A3</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Orientation</label>
                                <select v-model="form.initial_settings.orientation" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="portrait">Portrait</option><option value="landscape">Landscape</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Primary Color</label>
                                <div class="flex gap-2">
                                    <input type="color" v-model="form.initial_settings.primary_color" class="w-10 h-9 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer p-0.5 bg-white dark:bg-slate-700"/>
                                    <input type="text" v-model="form.initial_settings.primary_color" class="flex-1 px-2 py-1.5 text-xs font-mono border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-400"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Font Family</label>
                                <select v-model="form.initial_settings.font_family" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="'DM Sans', sans-serif">DM Sans</option>
                                    <option value="'Inter', sans-serif">Inter</option>
                                    <option value="Georgia, serif">Georgia</option>
                                    <option value="'Playfair Display', serif">Playfair Display</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="step=1" class="flex-1 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                ← Back
                            </button>
                            <button type="submit" :disabled="form.processing || !form.title.trim()"
                                class="flex-[2] py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".75"/></svg>
                                {{ form.processing ? 'Creating…' : 'Create & Open Editor' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ templates: { type: Array, default: () => [] } });

const step             = ref(1);
const selectedTemplate = ref(null);
const templateSelected = ref(false);
const titleRef         = ref(null);

const form = useForm({
    title: '',
    template_id: null,
    initial_settings: {
        page_size: 'A4',
        orientation: 'portrait',
        primary_color: '#6366f1',
        font_family: "'DM Sans', sans-serif",
    }
});

const selectTemplate = (tpl) => {
    selectedTemplate.value = tpl;
    form.template_id = tpl?.id ?? null;
    // Pre-fill settings from template
    if (tpl?.settings) {
        form.initial_settings = {
            page_size:    tpl.settings.page_size    ?? 'A4',
            orientation:  tpl.settings.orientation  ?? 'portrait',
            primary_color:tpl.settings.primary_color?? '#6366f1',
            font_family:  tpl.settings.font_family  ?? "'DM Sans', sans-serif",
        };
    }
};

const submit = () => {
    form.post(route('reports.store'), {
        onSuccess: () => { /* redirect happens server-side */ }
    });
};
</script>