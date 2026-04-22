<template>
    <aside class="w-64 flex flex-col overflow-hidden shrink-0 border-r transition-colors"
        :class="dark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">

        <!-- Tabs -->
        <div class="flex border-b shrink-0" :class="dark ? 'border-slate-800' : 'border-slate-200'">
            <button v-for="t in LEFT_TABS" :key="t.id" @click="$emit('update:left-tab', t.id)"
                class="flex-1 py-2.5 text-[10px] font-black uppercase tracking-wider transition-colors border-b-2 flex flex-col items-center gap-0.5"
                :class="leftTab === t.id ? 'border-indigo-500 text-indigo-500' : dark ? 'border-transparent text-slate-600 hover:text-slate-400' : 'border-transparent text-slate-400 hover:text-slate-600'">
                <i :class="t.icon + ' text-sm leading-none'"></i>
                <span>{{ t.label }}</span>
            </button>
        </div>

        <!-- ELEMENTS TAB -->
        <template v-if="leftTab === 'elements'">
            <div class="p-2.5 border-b shrink-0" :class="dark ? 'border-slate-800' : 'border-slate-100'">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    <input :value="elementSearch" @input="$emit('update:element-search', $event.target.value)"
                        placeholder="Search elements… (Ctrl+F)"
                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                        :class="dark ? 'bg-slate-800 border-slate-700 text-slate-200 placeholder:text-slate-600' : 'bg-slate-50 border-slate-200 text-slate-700'" />
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-2.5 space-y-3">
                <div v-for="cat in filteredCategories" :key="cat.name">
                    <button @click="$emit('toggle-category', cat.name)"
                        class="w-full flex items-center gap-2 mb-1.5 select-none"
                        :class="dark ? 'text-slate-500 hover:text-slate-400' : 'text-slate-400 hover:text-slate-600'">
                        <i :class="cat.icon + ' text-sm'"></i>
                        <span class="text-[9px] font-black uppercase tracking-widest flex-1 text-left">{{ cat.name }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-150"
                            :class="collapsedCategories.includes(cat.name) ? '' : 'rotate-180'"></i>
                    </button>
                    <div v-show="!collapsedCategories.includes(cat.name)" class="grid grid-cols-2 gap-1.5">
                        <div v-for="el in cat.items" :key="el.type"
                            :draggable="true"
                            @dragstart="handleDragStart($event, el)"
                            @click="$emit('add-element', el.type)"
                            class="group flex flex-col items-center gap-1 p-2 rounded-xl border cursor-grab active:cursor-grabbing select-none transition-all"
                            :class="dark ? 'border-slate-800 bg-slate-800/50 hover:border-indigo-500/60 hover:bg-indigo-500/10' : 'border-slate-200 bg-white hover:border-indigo-400 hover:bg-indigo-50'">
                            <div class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                                :class="dark ? 'bg-slate-700 group-hover:bg-indigo-500/20' : 'bg-slate-100 group-hover:bg-indigo-100'">
                                <i :class="el.icon + ' text-sm leading-none'"></i>
                            </div>
                            <span class="text-[9px] font-bold text-center leading-tight"
                                :class="dark ? 'text-slate-500 group-hover:text-indigo-400' : 'text-slate-500 group-hover:text-indigo-700'">
                                {{ el.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- PAGES TAB -->
        <template v-if="leftTab === 'pages'">
            <div class="flex-1 overflow-y-auto p-2.5 space-y-2">
                <div v-for="(page, pi) in pages" :key="page.id"
                    @click="$emit('select-page', pi)"
                    class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all"
                    :class="selectedPageIndex === pi ? 'border-indigo-500 bg-indigo-500/10' : dark ? 'border-slate-800 bg-slate-800/50 hover:border-slate-700' : 'border-slate-200 bg-white hover:border-slate-300'">
                    <!-- Mini thumbnail -->
                    <div class="w-9 h-12 rounded-lg border flex-shrink-0 overflow-hidden flex flex-col gap-0.5 p-1"
                        :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200 bg-white'">
                        <div class="h-1 rounded w-full" :class="dark ? 'bg-slate-600' : 'bg-slate-200'"></div>
                        <div class="h-1 rounded w-3/4" :class="dark ? 'bg-slate-600' : 'bg-slate-200'"></div>
                        <div class="h-1 rounded w-1/2 bg-indigo-400/60"></div>
                        <div class="h-1 rounded w-full mt-0.5" :class="dark ? 'bg-slate-700' : 'bg-slate-100'"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                            Page {{ pi + 1 }}
                        </div>
                        <div class="text-[9px] mt-0.5" :class="dark ? 'text-slate-600' : 'text-slate-400'">
                            {{ page.elements.length }} element{{ page.elements.length !== 1 ? "s" : "" }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <button @click.stop="$emit('duplicate-page', pi)"
                            class="p-0.5 rounded transition-colors" title="Duplicate"
                            :class="dark ? 'text-slate-600 hover:text-indigo-400' : 'text-slate-300 hover:text-indigo-500'">
                            <i class="fa-regular fa-clone text-xs"></i>
                        </button>
                        <button v-if="pages.length > 1" @click.stop="$emit('remove-page', pi)"
                            class="p-0.5 rounded transition-colors" title="Delete"
                            :class="dark ? 'text-slate-700 hover:text-red-400' : 'text-slate-300 hover:text-red-500'">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-2.5 border-t shrink-0" :class="dark ? 'border-slate-800' : 'border-slate-100'">
                <button @click="$emit('add-page-after', pages.length - 1)"
                    class="w-full flex items-center justify-center gap-1.5 py-2 text-xs font-bold border-2 border-dashed rounded-xl transition-all"
                    :class="dark ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400 hover:text-indigo-600'">
                    <i class="fa-solid fa-plus text-xs"></i> Add Page
                </button>
            </div>
        </template>

        <!-- SETTINGS TAB -->
        <template v-if="leftTab === 'settings'">
            <div class="flex-1 overflow-y-auto p-3 space-y-4">

                <!-- Page Size -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-file mr-1"></i>Page Size
                    </label>
                    <select :value="rs.page_size" @change="emitRs('page_size', $event.target.value)" :class="inputCls">
                        <option v-for="s in PAGE_SIZES" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>

                <!-- Orientation -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-rotate mr-1"></i>Orientation
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <button v-for="o in ['portrait', 'landscape']" :key="o" @click="emitRs('orientation', o)"
                            class="flex flex-col items-center justify-center py-3 rounded-xl border text-xs font-bold transition-all capitalize"
                            :class="rs.orientation === o ? 'bg-indigo-600 text-white border-indigo-600' : dark ? 'bg-slate-800 text-slate-400 border-slate-700 hover:border-indigo-500' : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300'">
                            <i :class="o === 'portrait' ? 'fa-solid fa-file text-base mb-1' : 'fa-solid fa-file fa-rotate-90 text-base mb-1'"></i>
                            {{ o }}
                        </button>
                    </div>
                </div>

                <!-- Font Family -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-font mr-1"></i>Font Family
                    </label>
                    <select :value="rs.font_family" @change="emitRs('font_family', $event.target.value)" :class="inputCls">
                        <option v-for="f in FONTS" :key="f.value" :value="f.value">{{ f.name }}</option>
                    </select>
                </div>

                <!-- Font Size -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-text-height mr-1"></i>Base Font Size (px)
                    </label>
                    <input type="number" :value="rs.font_size" @change="emitRs('font_size', +$event.target.value)"
                        :class="inputCls" min="8" max="32" step="1" />
                </div>

                <!-- Brand Colors -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-palette mr-1"></i>Brand Colors
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <template v-for="cp in COLOR_PROPS" :key="cp.key">
                            <div>
                                <div class="text-[9px] font-bold mb-1" :class="dark ? 'text-slate-600' : 'text-slate-400'">{{ cp.label }}</div>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" :value="rs[cp.key]" @input="emitRs(cp.key, $event.target.value)"
                                        class="w-7 h-7 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                                        :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
                                    <input type="text" :value="rs[cp.key]" @input="emitRs(cp.key, $event.target.value)"
                                        class="flex-1 min-w-0 text-[10px] font-mono rounded border px-1.5 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'" />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Page Margin -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-expand mr-1"></i>Page Margin (px)
                    </label>
                    <input type="number" :value="rs.margin" @change="emitRs('margin', +$event.target.value)"
                        :class="inputCls" min="0" max="120" step="4" />
                </div>

                <!-- Page Border Radius -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-vector-square mr-1"></i>Canvas Border Radius (px)
                    </label>
                    <input type="number" :value="rs.page_radius" @change="emitRs('page_radius', +$event.target.value)"
                        :class="inputCls" min="0" max="32" />
                </div>

                <!-- Header Settings -->
                <div class="border rounded-xl p-3 space-y-2" :class="dark ? 'border-slate-700' : 'border-slate-200'">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                            <i class="fa-solid fa-rectangle-ad mr-1 text-slate-400"></i>Header
                        </label>
                        <ToggleSwitch :value="rs.show_header" @toggle="emitRs('show_header', !rs.show_header)" />
                    </div>
                    <div v-if="rs.show_header" class="space-y-2">
                        <input type="text" :value="rs.header_text" @input="emitRs('header_text', $event.target.value)"
                            :class="inputCls" placeholder="Header text…" />
                        <div class="flex items-center gap-2">
                            <input type="color" :value="rs.header_color" @input="emitRs('header_color', $event.target.value)"
                                class="w-7 h-7 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                                :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
                            <input type="text" :value="rs.header_color" @input="emitRs('header_color', $event.target.value)"
                                class="flex-1 text-[10px] font-mono rounded border px-1.5 py-1 focus:outline-none"
                                :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'" />
                        </div>
                    </div>
                </div>

                <!-- Footer Settings -->
                <div class="border rounded-xl p-3 space-y-2" :class="dark ? 'border-slate-700' : 'border-slate-200'">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                            <i class="fa-solid fa-rectangle-ad fa-flip-vertical mr-1 text-slate-400"></i>Footer
                        </label>
                        <ToggleSwitch :value="rs.show_footer" @toggle="emitRs('show_footer', !rs.show_footer)" />
                    </div>
                    <div v-if="rs.show_footer" class="space-y-2">
                        <input type="text" :value="rs.footer_left" @input="emitRs('footer_left', $event.target.value)"
                            :class="inputCls" placeholder="Footer left…" />
                        <input type="text" :value="rs.footer_right" @input="emitRs('footer_right', $event.target.value)"
                            :class="inputCls" placeholder="Footer right…" />
                        <div class="flex items-center gap-2">
                            <input type="color" :value="rs.footer_color" @input="emitRs('footer_color', $event.target.value)"
                                class="w-7 h-7 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                                :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
                            <input type="text" :value="rs.footer_color" @input="emitRs('footer_color', $event.target.value)"
                                class="flex-1 text-[10px] font-mono rounded border px-1.5 py-1 focus:outline-none"
                                :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'" />
                        </div>
                    </div>
                </div>

                <!-- Page Numbers -->
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                        <i class="fa-solid fa-hashtag mr-1 text-slate-400"></i>Page Numbers
                    </label>
                    <ToggleSwitch :value="rs.show_page_numbers" @toggle="emitRs('show_page_numbers', !rs.show_page_numbers)" />
                </div>

                <!-- Watermark -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-droplet mr-1"></i>Watermark Text
                    </label>
                    <input type="text" :value="rs.watermark" @input="emitRs('watermark', $event.target.value)"
                        :class="inputCls" placeholder="DRAFT · CONFIDENTIAL" />
                </div>

                <!-- BG Image -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-image mr-1"></i>Page BG Image URL
                    </label>
                    <input type="url" :value="rs.bg_image" @input="emitRs('bg_image', $event.target.value)"
                        :class="inputCls" placeholder="https://…" />
                </div>

                <!-- Grid Size -->
                <div>
                    <label class="prop-label" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-border-all mr-1"></i>Grid Size (px)
                    </label>
                    <input type="number" :value="gridSize" @change="$emit('update:grid-size', +$event.target.value)"
                        :class="inputCls" min="5" max="60" step="5" />
                </div>

                <!-- Display Toggles -->
                <div>
                    <label class="prop-label mb-1.5 block" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                        <i class="fa-solid fa-toggle-on mr-1"></i>Display Options
                    </label>
                    <div class="space-y-0">
                        <div v-for="t in SETTING_TOGGLES" :key="t.key"
                            class="flex items-center justify-between py-1.5 border-b last:border-b-0"
                            :class="dark ? 'border-slate-800' : 'border-slate-100'">
                            <label class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">{{ t.label }}</label>
                            <ToggleSwitch :value="getToggle(t.key)" @toggle="setToggle(t.key)" />
                        </div>
                    </div>
                </div>

                <!-- RTL -->
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                        <i class="fa-solid fa-align-right mr-1 text-slate-400"></i>RTL Layout
                    </label>
                    <ToggleSwitch :value="rs.rtl" @toggle="emitRs('rtl', !rs.rtl)" />
                </div>
            </div>
        </template>
    </aside>
</template>

<script setup>
import { computed, inject } from "vue";
import ToggleSwitch from "@/Components/Editor/ToggleSwitch.vue";

const ELEMENT_CATEGORIES = inject("ELEMENT_CATEGORIES");
const FONTS = inject("FONTS");
const PAGE_SIZES = inject("PAGE_SIZES");

const props = defineProps({
    dark: Boolean,
    leftTab: String,
    pages: Array,
    selectedPageIndex: Number,
    elementSearch: String,
    collapsedCategories: Array,
    rs: Object,
    showGrid: Boolean,
    snapToGrid: Boolean,
    showRulers: Boolean,
    gridSize: Number,
    inputCls: String,
});

const emit = defineEmits([
    "update:left-tab","update:element-search","add-element","select-page",
    "add-page-after","remove-page","duplicate-page","toggle-category",
    "update:rs","update:show-grid","update:snap-to-grid","update:show-rulers","update:grid-size",
]);

const LEFT_TABS = [
    { id: "elements", label: "Elements", icon: "fa-solid fa-puzzle-piece" },
    { id: "pages",    label: "Pages",    icon: "fa-regular fa-file" },
    { id: "settings", label: "Settings", icon: "fa-solid fa-gear" },
];

const COLOR_PROPS = [
    { key: "primary_color",    label: "Primary" },
    { key: "accent_color",     label: "Accent" },
    { key: "background_color", label: "Page BG" },
    { key: "text_color",       label: "Text" },
    { key: "header_color",     label: "Header BG" },
    { key: "footer_color",     label: "Footer BG" },
];

const SETTING_TOGGLES = [
    { key: "showGrid",     label: "Grid Overlay" },
    { key: "snapToGrid",   label: "Snap to Grid" },
    { key: "showRulers",   label: "Rulers" },
];

const filteredCategories = computed(() => {
    if (!props.elementSearch.trim()) return ELEMENT_CATEGORIES;
    const q = props.elementSearch.toLowerCase();
    return ELEMENT_CATEGORIES.map(c => ({
        ...c,
        items: c.items.filter(i => i.name.toLowerCase().includes(q) || i.type.includes(q)),
    })).filter(c => c.items.length);
});

const emitRs = (key, value) => emit("update:rs", { ...props.rs, [key]: value });

const getToggle = (key) => {
    if (key === "showGrid")   return props.showGrid;
    if (key === "snapToGrid") return props.snapToGrid;
    if (key === "showRulers") return props.showRulers;
    return false;
};
const setToggle = (key) => {
    if (key === "showGrid")   emit("update:show-grid",   !props.showGrid);
    if (key === "snapToGrid") emit("update:snap-to-grid", !props.snapToGrid);
    if (key === "showRulers") emit("update:show-rulers",  !props.showRulers);
};

const handleDragStart = (e, el) => e.dataTransfer.setData("elementType", el.type);
</script>

<style scoped>
.prop-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
</style>