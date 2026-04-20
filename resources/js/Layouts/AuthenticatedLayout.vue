<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
        <!-- Navbar -->
        <nav
            class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 sticky top-0 z-40"
        >
            <div class="max-w-full px-4 sm:px-6">
                <div class="flex items-center justify-between h-14">
                    <!-- Logo + Nav -->
                    <div class="flex items-center gap-6">
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-2.5 shrink-0"
                        >
                            <div
                                class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>
                            <span
                                class="font-bold text-slate-900 dark:text-white text-sm"
                                >ReportGen</span
                            >
                        </Link>

                        <div class="hidden sm:flex items-center gap-1">
                            <NavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                Dashboard
                            </NavLink>
                            <NavLink
                                :href="route('reports.index')"
                                :active="route().current('reports.*')"
                            >
                                Reports
                            </NavLink>
                            <NavLink
                                :href="route('templates.index')"
                                :active="route().current('templates.*')"
                            >
                                Templates
                            </NavLink>
                        </div>
                    </div>

                    <!-- Right: user menu -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="toggleDark"
                            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all"
                        >
                            <svg
                                v-if="!isDark"
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                        </button>
                        <!-- New report shortcut -->
                        <Link
                            :href="route('reports.create')"
                            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors"
                        >
                            <svg
                                class="w-3.5 h-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
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
                                    class="w-7 h-7 rounded-full flex items-center justify-center bg-orange-600"
                                >
                                    <span
                                        class="text-xl font-extrabold text-indigo-600 dark:text-white"
                                    >
                                        {{
                                            $page.props.auth.user?.name?.[0]?.toUpperCase() ??
                                            "U"
                                        }}
                                    </span>
                                </div>
                                <span
                                    class="hidden md:block text-sm text-orange-600 dark:text-orange-500 font-extrabold"
                                >
                                    {{ $page.props.auth.user?.name }}
                                </span>
                                <svg
                                    class="w-3.5 h-3.5 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>

                            <transition name="dropdown">
                                <div
                                    v-if="showUserMenu"
                                    class="absolute right-0 top-full mt-1.5 w-48 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden z-50"
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
                                            :href="route('profile.edit')"
                                            class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        >
                                            <svg
                                                class="w-4 h-4 text-slate-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                />
                                            </svg>
                                            Profile
                                        </Link>
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                                />
                                            </svg>
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
            class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700"
        >
            <div class="max-w-full px-4 sm:px-6 py-3">
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
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
                {{ $page.props.flash.success }}
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="flex items-center gap-2 px-4 py-3 bg-red-600 text-white rounded-xl shadow-lg text-sm font-medium"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
                {{ $page.props.flash.error }}
            </div>
        </div>

        <!-- Main content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";

const $page = usePage();
const showUserMenu = ref(false);
const isDark = ref(false);

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

// Dark mode functions (extracted from template gallery)
const toggleDark = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark", isDark.value);
    localStorage.setItem("theme", isDark.value ? "dark" : "light");
};

onMounted(() => {
    const saved = localStorage.getItem("theme");
    if (
        saved === "dark" ||
        (!saved && window.matchMedia("(prefers-color-scheme: dark)").matches)
    ) {
        isDark.value = true;
        document.documentElement.classList.add("dark");
    }
});
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
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
</style>
