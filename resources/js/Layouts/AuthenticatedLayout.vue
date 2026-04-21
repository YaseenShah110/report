<template>
    <div
        class="min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-200"
        :style="cssVars"
    >
        <!-- Settings Panel Overlay -->
        <transition name="settings-overlay">
            <div v-if="showSettings" class="fixed inset-0 z-50 flex">
                <div
                    class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                    @click="showSettings = false"
                ></div>
                <div
                    class="relative ml-auto h-full w-80 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 shadow-2xl flex flex-col overflow-hidden"
                >
                    <!-- Settings Header -->
                    <div
                        class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0"
                    >
                        <div class="flex items-center gap-2">
                            <div
                                class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center"
                            >
                                <i
                                    class="fa-solid fa-gear text-xs text-indigo-600 dark:text-indigo-400"
                                ></i>
                            </div>
                            <h2
                                class="text-sm font-bold text-slate-800 dark:text-white"
                            >
                                Appearance
                            </h2>
                        </div>
                        <button
                            @click="showSettings = false"
                            class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5 space-y-5">
                        <!-- Dark Mode -->
                        <div>
                            <label class="settings-label">Theme</label>
                            <div class="grid grid-cols-3 gap-2 mt-1.5">
                                <button
                                    v-for="t in THEMES"
                                    :key="t.id"
                                    @click="setTheme(t.id)"
                                    class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all"
                                    :class="
                                        theme === t.id
                                            ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30'
                                            : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500'
                                    "
                                >
                                    <i
                                        :class="t.icon + ' text-sm'"
                                        :style="
                                            theme === t.id
                                                ? 'color:#6366f1'
                                                : ''
                                        "
                                    ></i>
                                    <span
                                        class="text-[10px] font-bold text-slate-600 dark:text-slate-300"
                                        >{{ t.label }}</span
                                    >
                                </button>
                            </div>
                        </div>

                        <!-- Accent Color -->
                        <div>
                            <label class="settings-label">Accent Color</label>
                            <div class="flex flex-wrap gap-2 mt-1.5">
                                <button
                                    v-for="c in ACCENT_COLORS"
                                    :key="c.value"
                                    @click="accentColor = c.value"
                                    class="w-8 h-8 rounded-full border-2 transition-all hover:scale-110 flex items-center justify-center"
                                    :style="{ backgroundColor: c.value }"
                                    :class="
                                        accentColor === c.value
                                            ? 'border-white dark:border-slate-200 ring-2 ring-offset-1'
                                            : 'border-transparent'
                                    "
                                    :title="c.name"
                                >
                                    <i
                                        v-if="accentColor === c.value"
                                        class="fa-solid fa-check text-white text-[10px]"
                                    ></i>
                                </button>
                            </div>
                        </div>

                        <!-- Font Family -->
                        <div>
                            <label class="settings-label">Font Family</label>
                            <select
                                v-model="fontFamily"
                                class="mt-1.5 w-full px-3 py-2 text-sm bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200"
                            >
                                <option
                                    v-for="f in FONT_OPTIONS"
                                    :key="f.value"
                                    :value="f.value"
                                >
                                    {{ f.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Font Size -->
                        <div>
                            <div class="flex items-center justify-between">
                                <label class="settings-label">Font Size</label>
                                <span
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                    >{{ fontSize }}px</span
                                >
                            </div>
                            <input
                                type="range"
                                v-model.number="fontSize"
                                min="12"
                                max="18"
                                step="1"
                                class="mt-2 w-full accent-indigo-600"
                            />
                            <div
                                class="flex justify-between text-[10px] text-slate-400 mt-1"
                            >
                                <span>12px</span><span>15px</span
                                ><span>18px</span>
                            </div>
                        </div>

                        <!-- Border Radius -->
                        <div>
                            <div class="flex items-center justify-between">
                                <label class="settings-label"
                                    >Corner Radius</label
                                >
                                <span
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                    >{{ borderRadius }}px</span
                                >
                            </div>
                            <input
                                type="range"
                                v-model.number="borderRadius"
                                min="4"
                                max="24"
                                step="2"
                                class="mt-2 w-full accent-indigo-600"
                            />
                            <div
                                class="flex justify-between text-[10px] text-slate-400 mt-1"
                            >
                                <span>Sharp</span><span>Round</span
                                ><span>Pill</span>
                            </div>
                        </div>

                        <!-- Compact Mode -->
                        <div
                            class="flex items-center justify-between py-2 border-t border-slate-100 dark:border-slate-700"
                        >
                            <div>
                                <label
                                    class="text-xs font-bold text-slate-700 dark:text-slate-200"
                                    >Compact Mode</label
                                >
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    Reduce padding & spacing
                                </p>
                            </div>
                            <button
                                @click="compactMode = !compactMode"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                :class="
                                    compactMode
                                        ? 'bg-indigo-600'
                                        : 'bg-slate-200 dark:bg-slate-600'
                                "
                            >
                                <span
                                    class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
                                    :class="
                                        compactMode
                                            ? 'translate-x-5'
                                            : 'translate-x-1'
                                    "
                                ></span>
                            </button>
                        </div>

                        <!-- Sidebar Width -->
                        <div
                            class="border-t border-slate-100 dark:border-slate-700 pt-4"
                        >
                            <label class="settings-label">Sidebar Style</label>
                            <div class="grid grid-cols-2 gap-2 mt-1.5">
                                <button
                                    @click="sidebarCollapsed = false"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl border-2 transition-all text-xs font-semibold"
                                    :class="
                                        !sidebarCollapsed
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30'
                                            : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-slate-300'
                                    "
                                >
                                    <i class="fa-solid fa-sidebar text-sm"></i>
                                    Expanded
                                </button>
                                <button
                                    @click="sidebarCollapsed = true"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl border-2 transition-all text-xs font-semibold"
                                    :class="
                                        sidebarCollapsed
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30'
                                            : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-slate-300'
                                    "
                                >
                                    <i
                                        class="fa-solid fa-sidebar-flip text-sm"
                                    ></i>
                                    Compact
                                </button>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div
                            class="border-t border-slate-100 dark:border-slate-700 pt-4"
                        >
                            <label class="settings-label mb-2 block"
                                >Preview</label
                            >
                            <div
                                class="rounded-xl border border-slate-200 dark:border-slate-600 p-3 bg-slate-50 dark:bg-slate-900/50 space-y-2"
                            >
                                <div
                                    class="h-2 rounded-full bg-indigo-500/20 overflow-hidden"
                                >
                                    <div
                                        class="h-full w-2/3 rounded-full"
                                        :style="{
                                            backgroundColor: accentColor,
                                        }"
                                    ></div>
                                </div>
                                <div class="flex gap-2">
                                    <div
                                        class="h-7 flex-1 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700"
                                        :style="{
                                            borderRadius: borderRadius + 'px',
                                        }"
                                    ></div>
                                    <div
                                        class="h-7 px-3 flex items-center rounded-lg text-white text-[11px] font-bold"
                                        :style="{
                                            backgroundColor: accentColor,
                                            borderRadius: borderRadius + 'px',
                                        }"
                                    >
                                        Save
                                    </div>
                                </div>
                                <div
                                    class="text-xs text-slate-600 dark:text-slate-300"
                                    :style="{
                                        fontFamily,
                                        fontSize: fontSize + 'px',
                                    }"
                                >
                                    Sample text in your chosen font
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reset -->
                    <div
                        class="p-4 border-t border-slate-100 dark:border-slate-700 shrink-0"
                    >
                        <button
                            @click="resetSettings"
                            class="w-full py-2 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors"
                        >
                            Reset to defaults
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Navbar -->
        <nav
            class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-40 transition-colors duration-200"
        >
            <div class="max-w-full px-4 sm:px-6">
                <div
                    class="flex items-center justify-between"
                    :class="compactMode ? 'h-12' : 'h-14'"
                >
                    <!-- Logo + Nav -->
                    <div class="flex items-center gap-6">
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-2.5 shrink-0"
                        >
                            <div
                                class="flex items-center justify-center rounded-lg transition-colors"
                                :style="{
                                    width: '32px',
                                    height: '32px',
                                    backgroundColor: accentColor,
                                    borderRadius: borderRadius + 'px',
                                }"
                            >
                                <i
                                    class="fa-solid fa-chart-line text-white text-sm"
                                ></i>
                            </div>
                            <span
                                v-if="!sidebarCollapsed"
                                class="font-bold text-slate-900 dark:text-white text-sm"
                                >ReportGen</span
                            >
                        </Link>

                        <div class="hidden sm:flex items-center gap-1">
                            <NavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                <i
                                    class="fa-solid fa-gauge-high text-xs mr-1.5"
                                ></i
                                >Dashboard
                            </NavLink>
                            <NavLink
                                :href="route('reports.index')"
                                :active="route().current('reports.*')"
                            >
                                <i
                                    class="fa-solid fa-file-lines text-xs mr-1.5"
                                ></i
                                >Reports
                            </NavLink>
                            <NavLink
                                :href="route('templates.index')"
                                :active="route().current('templates.*')"
                            >
                                <i class="fa-solid fa-grid-2 text-xs mr-1.5"></i
                                >Templates
                            </NavLink>
                        </div>
                    </div>

                    <!-- Right: toolbar -->
                    <div class="flex items-center gap-1.5">
                        <!-- Full Screen -->
                        <button
                            @click="toggleFullscreen"
                            title="Fullscreen (F)"
                            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all"
                            :class="
                                isFullscreen
                                    ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400'
                                    : ''
                            "
                        >
                            <i
                                :class="
                                    isFullscreen
                                        ? 'fa-solid fa-compress'
                                        : 'fa-solid fa-expand'
                                "
                                class="text-sm"
                            ></i>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleDark"
                            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all"
                        >
                            <i
                                :class="
                                    isDark
                                        ? 'fa-solid fa-sun'
                                        : 'fa-solid fa-moon'
                                "
                                class="text-sm"
                            ></i>
                        </button>

                        <!-- Settings -->
                        <button
                            @click="showSettings = !showSettings"
                            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all"
                            :class="
                                showSettings
                                    ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400'
                                    : ''
                            "
                            title="Appearance Settings"
                        >
                            <i class="fa-solid fa-sliders text-sm"></i>
                        </button>

                        <!-- New report -->
                        <Link
                            :href="route('reports.create')"
                            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 text-white text-xs font-semibold rounded-lg transition-colors"
                            :style="{
                                backgroundColor: accentColor,
                                borderRadius: borderRadius + 'px',
                            }"
                        >
                            <i class="fa-solid fa-plus text-xs"></i>
                            New Report
                        </Link>

                        <!-- User dropdown -->
                        <div
                            class="relative"
                            v-click-outside="() => (showUserMenu = false)"
                        >
                            <button
                                @click="showUserMenu = !showUserMenu"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                            >
                                <div
                                    class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs text-white flex-shrink-0"
                                    :style="{
                                        backgroundColor: accentColor,
                                        borderRadius: '50%',
                                    }"
                                >
                                    {{
                                        $page.props.auth.user?.name?.[0]?.toUpperCase() ??
                                        "U"
                                    }}
                                </div>
                                <span
                                    class="hidden md:block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        $page.props.auth.user?.name?.split(
                                            " ",
                                        )[0]
                                    }}
                                </span>
                                <i
                                    class="fa-solid fa-chevron-down text-[10px] text-slate-400"
                                ></i>
                            </button>

                            <transition name="dropdown">
                                <div
                                    v-if="showUserMenu"
                                    class="absolute right-0 top-full mt-1.5 w-52 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden z-50"
                                >
                                    <div
                                        class="px-3 py-2.5 border-b border-slate-100 dark:border-slate-700"
                                    >
                                        <p
                                            class="text-xs font-semibold text-slate-900 dark:text-white truncate"
                                        >
                                            {{ $page.props.auth.user?.name }}
                                        </p>
                                        <p
                                            class="text-[11px] text-slate-400 truncate"
                                        >
                                            {{ $page.props.auth.user?.email }}
                                        </p>
                                    </div>
                                    <div class="py-1">
                                        <Link
                                            :href="route('dashboard')"
                                            class="dropdown-item"
                                        >
                                            <i
                                                class="fa-solid fa-gauge-high w-4 text-center"
                                            ></i>
                                            Dashboard
                                        </Link>
                                        <Link
                                            :href="route('reports.index')"
                                            class="dropdown-item"
                                        >
                                            <i
                                                class="fa-solid fa-file-lines w-4 text-center"
                                            ></i>
                                            My Reports
                                        </Link>
                                        <Link
                                            :href="route('profile.edit')"
                                            class="dropdown-item"
                                        >
                                            <i
                                                class="fa-solid fa-user w-4 text-center"
                                            ></i>
                                            Profile
                                        </Link>
                                        <button
                                            @click="
                                                showSettings = true;
                                                showUserMenu = false;
                                            "
                                            class="dropdown-item w-full text-left"
                                        >
                                            <i
                                                class="fa-solid fa-sliders w-4 text-center"
                                            ></i>
                                            Appearance
                                        </button>
                                        <div
                                            class="my-1 border-t border-slate-100 dark:border-slate-700"
                                        ></div>
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="dropdown-item w-full text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <i
                                                class="fa-solid fa-arrow-right-from-bracket w-4 text-center"
                                            ></i>
                                            Sign Out
                                        </Link>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page header slot -->
        <header
            v-if="$slots.header"
            class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 transition-colors duration-200"
        >
            <div
                class="max-w-full px-4 sm:px-6"
                :class="compactMode ? 'py-2' : 'py-3'"
            >
                <slot name="header" />
            </div>
        </header>

        <!-- Flash messages -->
        <div
            v-if="$page.props.flash?.success || $page.props.flash?.error"
            class="fixed top-4 right-4 z-[100] space-y-2"
        >
            <div
                v-if="$page.props.flash?.success"
                class="flex items-center gap-2 px-4 py-3 bg-emerald-600 text-white rounded-xl shadow-lg text-sm font-medium animate-slide-in"
            >
                <i class="fa-solid fa-circle-check"></i>
                {{ $page.props.flash.success }}
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="flex items-center gap-2 px-4 py-3 bg-red-600 text-white rounded-xl shadow-lg text-sm font-medium"
            >
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $page.props.flash.error }}
            </div>
        </div>

        <!-- Main content -->
        <main :class="compactMode ? 'text-sm' : ''">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";

const $page = usePage();
const showUserMenu = ref(false);
const showSettings = ref(false);
const isDark = ref(false);
const isFullscreen = ref(false);

// Settings state
const theme = ref("system");
const accentColor = ref("#6366f1");
const fontFamily = ref("'DM Sans', sans-serif");
const fontSize = ref(14);
const borderRadius = ref(12);
const compactMode = ref(false);
const sidebarCollapsed = ref(false);

const THEMES = [
    { id: "light", label: "Light", icon: "fa-solid fa-sun" },
    { id: "dark", label: "Dark", icon: "fa-solid fa-moon" },
    { id: "system", label: "System", icon: "fa-solid fa-laptop" },
];

const ACCENT_COLORS = [
    { value: "#6366f1", name: "Indigo" },
    { value: "#8b5cf6", name: "Violet" },
    { value: "#ec4899", name: "Pink" },
    { value: "#10b981", name: "Emerald" },
    { value: "#f59e0b", name: "Amber" },
    { value: "#ef4444", name: "Red" },
    { value: "#0ea5e9", name: "Sky" },
    { value: "#14b8a6", name: "Teal" },
    { value: "#f97316", name: "Orange" },
];

const FONT_OPTIONS = [
    { value: "'DM Sans', sans-serif", label: "DM Sans" },
    { value: "'Inter', sans-serif", label: "Inter" },
    { value: "'Outfit', sans-serif", label: "Outfit" },
    { value: "'Plus Jakarta Sans', sans-serif", label: "Plus Jakarta Sans" },
    { value: "Georgia, serif", label: "Georgia" },
    { value: "'Playfair Display', serif", label: "Playfair Display" },
    { value: "'Merriweather', serif", label: "Merriweather" },
    { value: "'Courier New', monospace", label: "Courier New" },
];

const cssVars = computed(() => ({
    "--accent-color": accentColor.value,
    "--font-family": fontFamily.value,
    "--base-font-size": fontSize.value + "px",
    "--card-radius": borderRadius.value + "px",
}));

const setTheme = (t) => {
    theme.value = t;
    if (t === "dark") {
        isDark.value = true;
        document.documentElement.classList.add("dark");
        localStorage.setItem("theme", "dark");
    } else if (t === "light") {
        isDark.value = false;
        document.documentElement.classList.remove("dark");
        localStorage.setItem("theme", "light");
    } else {
        localStorage.removeItem("theme");
        const prefersDark = window.matchMedia(
            "(prefers-color-scheme: dark)",
        ).matches;
        isDark.value = prefersDark;
        document.documentElement.classList.toggle("dark", prefersDark);
    }
};

const toggleDark = () => {
    setTheme(isDark.value ? "light" : "dark");
    theme.value = isDark.value ? "light" : "dark";
};

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement
            .requestFullscreen?.()
            .then(() => {
                isFullscreen.value = true;
            })
            .catch(() => {});
    } else {
        document.exitFullscreen?.().then(() => {
            isFullscreen.value = false;
        });
    }
};

const resetSettings = () => {
    accentColor.value = "#6366f1";
    fontFamily.value = "'DM Sans', sans-serif";
    fontSize.value = 14;
    borderRadius.value = 12;
    compactMode.value = false;
    sidebarCollapsed.value = false;
    saveSettings();
};

const saveSettings = () => {
    localStorage.setItem(
        "rb-settings",
        JSON.stringify({
            accentColor: accentColor.value,
            fontFamily: fontFamily.value,
            fontSize: fontSize.value,
            borderRadius: borderRadius.value,
            compactMode: compactMode.value,
            sidebarCollapsed: sidebarCollapsed.value,
        }),
    );
};

const loadSettings = () => {
    try {
        const saved = JSON.parse(localStorage.getItem("rb-settings") || "{}");
        if (saved.accentColor) accentColor.value = saved.accentColor;
        if (saved.fontFamily) fontFamily.value = saved.fontFamily;
        if (saved.fontSize) fontSize.value = saved.fontSize;
        if (saved.borderRadius) borderRadius.value = saved.borderRadius;
        if (saved.compactMode !== undefined)
            compactMode.value = saved.compactMode;
        if (saved.sidebarCollapsed !== undefined)
            sidebarCollapsed.value = saved.sidebarCollapsed;
    } catch {}
};

// v-click-outside directive
const vClickOutside = {
    mounted(el, binding) {
        el._clickOutside = (e) => {
            if (!el.contains(e.target)) binding.value(e);
        };
        document.addEventListener("click", el._clickOutside);
    },
    unmounted(el) {
        document.removeEventListener("click", el._clickOutside);
    },
};

const handleFullscreenChange = () => {
    isFullscreen.value = !!document.fullscreenElement;
};

const handleKeyDown = (e) => {
    if (
        e.key === "F" &&
        !e.ctrlKey &&
        !e.metaKey &&
        !["INPUT", "TEXTAREA"].includes(e.target.tagName) &&
        e.target.contentEditable !== "true"
    ) {
        toggleFullscreen();
    }
};

onMounted(() => {
    loadSettings();
    const saved = localStorage.getItem("theme");
    if (saved === "dark") {
        isDark.value = true;
        document.documentElement.classList.add("dark");
        theme.value = "dark";
    } else if (saved === "light") {
        isDark.value = false;
        document.documentElement.classList.remove("dark");
        theme.value = "light";
    } else {
        const prefersDark = window.matchMedia(
            "(prefers-color-scheme: dark)",
        ).matches;
        isDark.value = prefersDark;
        document.documentElement.classList.toggle("dark", prefersDark);
        theme.value = "system";
    }

    document.addEventListener("fullscreenchange", handleFullscreenChange);
    document.addEventListener("keydown", handleKeyDown);

    // Watch settings changes and save
    const watchInterval = setInterval(saveSettings, 2000);
    return () => clearInterval(watchInterval);
});

onBeforeUnmount(() => {
    document.removeEventListener("fullscreenchange", handleFullscreenChange);
    document.removeEventListener("keydown", handleKeyDown);
    saveSettings();
});
</script>

<style scoped>
.settings-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
}
.dark .settings-label {
    color: #64748b;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    transition: background-color 0.15s;
    cursor: pointer;
    text-decoration: none;
    width: 100%;
    text-align: left;
}
.dark .dropdown-item {
    color: #cbd5e1;
}
.dropdown-item:hover {
    background-color: #f8fafc;
}
.dark .dropdown-item:hover {
    background-color: #334155;
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

.settings-overlay-enter-active {
    transition: all 0.25s ease;
}
.settings-overlay-leave-active {
    transition: all 0.2s ease;
}
.settings-overlay-enter-from,
.settings-overlay-leave-to {
    opacity: 0;
}
.settings-overlay-enter-from .relative,
.settings-overlay-leave-to .relative {
    transform: translateX(100%);
}

@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateX(16px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
.animate-slide-in {
    animation: slide-in 0.25s ease;
}

::-webkit-scrollbar {
    width: 4px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 99px;
}
.dark ::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
