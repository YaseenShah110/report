<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex items-center justify-between gap-3 h-full px-1"
                :class="dark ? 'text-slate-100' : 'text-slate-800'"
            >
                <!-- Left: breadcrumb + title -->
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <button
                        @click="router.get(route('reports.index'))"
                        class="flex items-center gap-1 text-xs font-semibold shrink-0 px-2 py-1.5 rounded-lg transition-all"
                        :class="
                            dark
                                ? 'text-slate-400 hover:text-slate-100 hover:bg-white/10'
                                : 'text-slate-400 hover:text-slate-700 hover:bg-slate-100'
                        "
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
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        Reports
                    </button>
                    <span
                        :class="dark ? 'text-slate-700' : 'text-slate-300'"
                        >/</span
                    >
                    <input
                        v-model="form.title"
                        class="text-sm font-bold bg-transparent border-0 border-b-2 border-transparent focus:border-indigo-500 focus:outline-none px-1 py-0.5 min-w-0 flex-1 transition-colors"
                        :class="
                            dark
                                ? 'text-slate-100 placeholder:text-slate-600'
                                : 'text-slate-800 placeholder:text-slate-300'
                        "
                        placeholder="Untitled Report…"
                    />
                    <transition name="fade">
                        <span
                            v-if="saveStatus === 'saving'"
                            class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-500 shrink-0"
                        >
                            <svg
                                class="w-2.5 h-2.5 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                /></svg
                            >Saving…
                        </span>
                        <span
                            v-else-if="saveStatus === 'saved'"
                            class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-500 shrink-0"
                        >
                            <svg
                                class="w-2.5 h-2.5"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                /></svg
                            >Saved
                        </span>
                    </transition>
                </div>

                <!-- Right: toolbar -->
                <div class="flex items-center gap-1 shrink-0">
                    <!-- Dark Mode -->
                    <button
                        @click="dark = !dark"
                        :title="dark ? 'Light Mode' : 'Dark Mode'"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="
                            dark
                                ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                        "
                    >
                        <svg
                            v-if="dark"
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                            />
                        </svg>
                    </button>

                    <!-- Drag Mode -->
                    <button
                        @click="dragMode = !dragMode"
                        title="Drag Mode (D)"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg border transition-all"
                        :class="
                            dragMode
                                ? 'bg-violet-600 text-white border-violet-600 shadow-md shadow-violet-500/30'
                                : dark
                                  ? 'bg-white/10 text-slate-300 border-white/10 hover:bg-white/15'
                                  : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'
                        "
                    >
                        <svg
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"
                            />
                        </svg>
                        {{ dragMode ? "Drag ON" : "Drag" }}
                    </button>

                    <!-- Grid Toggle -->
                    <button
                        @click="showGrid = !showGrid"
                        :title="showGrid ? 'Hide Grid (G)' : 'Show Grid (G)'"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="
                            showGrid
                                ? 'bg-indigo-100 text-indigo-600'
                                : dark
                                  ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                  : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                        "
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
                                d="M3 3h18v18H3zM9 3v18M15 3v18M3 9h18M3 15h18"
                            />
                        </svg>
                    </button>

                    <!-- Rulers Toggle -->
                    <button
                        @click="showRulers = !showRulers"
                        :title="showRulers ? 'Hide Rulers (R)' : 'Show Rulers (R)'"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="
                            showRulers
                                ? 'bg-indigo-100 text-indigo-600'
                                : dark
                                  ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                  : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                        "
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
                                d="M3 6h18M3 12h4m4 0h4m4 0h2M3 18h18M7 6v3m4 0V6m4 3V6m4 3V6"
                            />
                        </svg>
                    </button>

                    <div
                        class="w-px h-5 mx-0.5"
                        :class="dark ? 'bg-slate-700' : 'bg-slate-200'"
                    ></div>

                    <!-- Undo/Redo -->
                    <button
                        @click="undo"
                        :disabled="historyIndex <= 0"
                        title="Undo (Ctrl+Z)"
                        class="p-1.5 rounded-lg transition-colors disabled:opacity-25"
                        :class="
                            dark
                                ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                        "
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
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6"
                            />
                        </svg>
                    </button>
                    <button
                        @click="redo"
                        :disabled="historyIndex >= history.length - 1"
                        title="Redo (Ctrl+Y)"
                        class="p-1.5 rounded-lg transition-colors disabled:opacity-25"
                        :class="
                            dark
                                ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                        "
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
                                d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6M21 10l-6-6"
                            />
                        </svg>
                    </button>

                    <div
                        class="w-px h-5 mx-0.5"
                        :class="dark ? 'bg-slate-700' : 'bg-slate-200'"
                    ></div>

                    <!-- Fullscreen -->
                    <button
                        @click="toggleFullscreen"
                        title="Fullscreen (F)"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="
                            dark
                                ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                        "
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
                                d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                            />
                        </svg>
                    </button>

                    <div
                        class="w-px h-5 mx-0.5"
                        :class="dark ? 'bg-slate-700' : 'bg-slate-200'"
                    ></div>

                    <!-- Save -->
                    <button
                        @click="saveReport"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
                        :class="
                            dark
                                ? 'bg-slate-600 hover:bg-slate-500 text-white'
                                : 'bg-slate-800 hover:bg-slate-700 text-white'
                        "
                    >
                        <svg
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
                            /></svg
                        >Save
                    </button>

                    <!-- Preview -->
                    <button
                        @click="previewReport"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all"
                    >
                        <svg
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            /></svg
                        >Preview
                    </button>

                    <!-- Export -->
                    <div class="relative" ref="exportMenuRef">
                        <button
                            @click="showExportMenu = !showExportMenu"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition-all"
                        >
                            <svg
                                class="w-3 h-3"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                /></svg
                            >Export
                            <svg
                                class="w-3 h-3"
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
                                v-if="showExportMenu"
                                class="absolute right-0 top-full mt-1 w-48 rounded-xl shadow-xl border overflow-hidden z-50"
                                :class="
                                    dark
                                        ? 'bg-slate-800 border-slate-700'
                                        : 'bg-white border-slate-200'
                                "
                            >
                                <button
                                    v-for="fmt in EXPORT_FORMATS"
                                    :key="fmt.key"
                                    @click="exportAs(fmt.key)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold transition-colors text-left"
                                    :class="
                                        dark
                                            ? 'text-slate-300 hover:bg-white/10'
                                            : 'text-slate-600 hover:bg-slate-50'
                                    "
                                >
                                    <span class="text-lg leading-none">{{
                                        fmt.icon
                                    }}</span>
                                    <div>
                                        <div class="font-bold">{{
                                            fmt.label
                                        }}</div>
                                        <div
                                            class="text-[10px] text-slate-400"
                                        >
                                            {{ fmt.desc }}
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </template>

        <!-- MAIN LAYOUT -->
        <div
            class="flex h-[calc(100vh-64px)] overflow-hidden transition-colors duration-200"
            :class="dark ? 'bg-slate-950' : 'bg-slate-100'"
        >
            <!-- ═══════════ LEFT SIDEBAR ═══════════ -->
            <aside
                class="w-64 flex flex-col overflow-hidden shrink-0 border-r transition-colors"
                :class="
                    dark
                        ? 'bg-slate-900 border-slate-800'
                        : 'bg-white border-slate-200'
                "
            >
                <!-- Tabs -->
                <div
                    class="flex border-b shrink-0"
                    :class="dark ? 'border-slate-800' : 'border-slate-200'"
                >
                    <button
                        v-for="t in LEFT_TABS"
                        :key="t.id"
                        @click="leftTab = t.id"
                        class="flex-1 py-2.5 text-[10px] font-black uppercase tracking-wider transition-colors border-b-2 flex flex-col items-center gap-0.5"
                        :class="
                            leftTab === t.id
                                ? 'border-indigo-500 text-indigo-500'
                                : dark
                                  ? 'border-transparent text-slate-600 hover:text-slate-400'
                                  : 'border-transparent text-slate-400 hover:text-slate-600'
                        "
                    >
                        <span class="text-base leading-none">{{ t.icon }}</span>
                        <span>{{ t.label }}</span>
                    </button>
                </div>

                <!-- ── ELEMENTS TAB ── -->
                <template v-if="leftTab === 'elements'">
                    <div
                        class="p-2.5 border-b shrink-0"
                        :class="dark ? 'border-slate-800' : 'border-slate-100'"
                    >
                        <div class="relative">
                            <svg
                                class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            <input
                                v-model="elementSearch"
                                placeholder="Search elements… (Ctrl+F)"
                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                                :class="
                                    dark
                                        ? 'bg-slate-800 border-slate-700 text-slate-200 placeholder:text-slate-600'
                                        : 'bg-slate-50 border-slate-200 text-slate-700'
                                "
                            />
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2.5 space-y-3">
                        <div v-for="cat in filteredCategories" :key="cat.name">
                            <button
                                @click="toggleCategory(cat.name)"
                                class="w-full flex items-center gap-2 mb-1.5 select-none"
                                :class="
                                    dark
                                        ? 'text-slate-500 hover:text-slate-400'
                                        : 'text-slate-400 hover:text-slate-600'
                                "
                            >
                                <span class="text-sm">{{ cat.icon }}</span>
                                <span
                                    class="text-[9px] font-black uppercase tracking-widest flex-1 text-left"
                                    >{{ cat.name }}</span
                                >
                                <svg
                                    class="w-3 h-3 transition-transform duration-150"
                                    :class="
                                        collapsedCategories.includes(cat.name)
                                            ? ''
                                            : 'rotate-180'
                                    "
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
                            <div
                                v-show="!collapsedCategories.includes(cat.name)"
                                class="grid grid-cols-2 gap-1.5"
                            >
                                <div
                                    v-for="el in cat.items"
                                    :key="el.type"
                                    :draggable="true"
                                    @dragstart="handleDragStart($event, el)"
                                    @click="addElement(el.type)"
                                    class="group flex flex-col items-center gap-1 p-2 rounded-xl border cursor-grab active:cursor-grabbing select-none transition-all"
                                    :class="
                                        dark
                                            ? 'border-slate-800 bg-slate-800/50 hover:border-indigo-500/60 hover:bg-indigo-500/10'
                                            : 'border-slate-200 bg-white hover:border-indigo-400 hover:bg-indigo-50'
                                    "
                                >
                                    <div
                                        class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                                        :class="
                                            dark
                                                ? 'bg-slate-700 group-hover:bg-indigo-500/20'
                                                : 'bg-slate-100 group-hover:bg-indigo-100'
                                        "
                                    >
                                        <span
                                            class="text-sm leading-none"
                                            v-html="el.icon"
                                        ></span>
                                    </div>
                                    <span
                                        class="text-[9px] font-bold text-center leading-tight"
                                        :class="
                                            dark
                                                ? 'text-slate-500 group-hover:text-indigo-400'
                                                : 'text-slate-500 group-hover:text-indigo-700'
                                        "
                                        >{{ el.name }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ── PAGES TAB ── -->
                <template v-if="leftTab === 'pages'">
                    <div class="flex-1 overflow-y-auto p-2.5 space-y-2">
                        <div
                            v-for="(page, pi) in pages"
                            :key="page.id"
                            @click="selectedPageIndex = pi"
                            class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all"
                            :class="
                                selectedPageIndex === pi
                                    ? 'border-indigo-500 bg-indigo-500/10'
                                    : dark
                                      ? 'border-slate-800 bg-slate-800/50 hover:border-slate-700'
                                      : 'border-slate-200 bg-white hover:border-slate-300'
                            "
                        >
                            <div
                                class="w-9 h-12 rounded-lg border flex-shrink-0 overflow-hidden flex flex-col gap-0.5 p-1"
                                :class="
                                    dark
                                        ? 'border-slate-700 bg-slate-800'
                                        : 'border-slate-200 bg-white'
                                "
                            >
                                <div
                                    class="h-1 rounded w-full"
                                    :class="
                                        dark ? 'bg-slate-600' : 'bg-slate-200'
                                    "
                                ></div>
                                <div
                                    class="h-1 rounded w-3/4"
                                    :class="
                                        dark ? 'bg-slate-600' : 'bg-slate-200'
                                    "
                                ></div>
                                <div
                                    class="h-1 rounded w-1/2 bg-indigo-400/60"
                                ></div>
                                <div
                                    class="h-1 rounded w-full mt-0.5"
                                    :class="
                                        dark ? 'bg-slate-700' : 'bg-slate-100'
                                    "
                                ></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div
                                    class="text-[11px] font-bold"
                                    :class="
                                        dark
                                            ? 'text-slate-200'
                                            : 'text-slate-700'
                                    "
                                >
                                    Page {{ pi + 1 }}
                                </div>
                                <div
                                    class="text-[9px] mt-0.5"
                                    :class="
                                        dark
                                            ? 'text-slate-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    {{ page.elements.length }} element{{
                                        page.elements.length !== 1 ? "s" : ""
                                    }}
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <button
                                    @click.stop="duplicatePage(pi)"
                                    class="p-0.5 rounded transition-colors"
                                    :class="
                                        dark
                                            ? 'text-slate-600 hover:text-indigo-400'
                                            : 'text-slate-300 hover:text-indigo-500'
                                    "
                                    title="Duplicate"
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    v-if="pages.length > 1"
                                    @click.stop="removePage(pi)"
                                    class="p-0.5 rounded transition-colors"
                                    :class="
                                        dark
                                            ? 'text-slate-700 hover:text-red-400'
                                            : 'text-slate-300 hover:text-red-500'
                                    "
                                    title="Delete"
                                >
                                    <svg
                                        class="w-3 h-3"
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
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        class="p-2.5 border-t shrink-0"
                        :class="dark ? 'border-slate-800' : 'border-slate-100'"
                    >
                        <button
                            @click="addPageAfter(pages.length - 1)"
                            class="w-full flex items-center justify-center gap-1.5 py-2 text-xs font-bold border-2 border-dashed rounded-xl transition-all"
                            :class="
                                dark
                                    ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400'
                                    : 'border-slate-200 text-slate-400 hover:border-indigo-400 hover:text-indigo-600'
                            "
                        >
                            <svg
                                class="w-3 h-3"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                /></svg
                            >Add Page
                        </button>
                    </div>
                </template>

                <!-- ── LAYERS TAB ── -->
                <template v-if="leftTab === 'layers'">
                    <div class="flex-1 overflow-y-auto p-2.5">
                        <div
                            class="text-[9px] font-black uppercase tracking-widest mb-2"
                            :class="dark ? 'text-slate-600' : 'text-slate-400'"
                        >
                            Page {{ selectedPageIndex + 1 }} — {{ currentPage.elements.length }} Layers
                        </div>
                        <template v-if="currentPage.elements.length">
                            <div
                                v-for="el in [...currentPage.elements].reverse()"
                                :key="el.id"
                                @click="selectElement(el, selectedPageIndex)"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg cursor-pointer transition-all group/layer mb-0.5"
                                :class="
                                    selectedElement?.id === el.id
                                        ? 'bg-indigo-500/20 text-indigo-400'
                                        : dark
                                          ? 'hover:bg-white/5 text-slate-400'
                                          : 'hover:bg-slate-100 text-slate-600'
                                "
                            >
                                <span
                                    class="text-sm flex-shrink-0 w-5 text-center"
                                    >{{ ELEMENT_META[el.type] || "▣" }}</span
                                >
                                <span
                                    class="flex-1 text-[11px] font-medium truncate"
                                    >{{ getElementLabel(el.type) }}</span
                                >
                                <button
                                    @click.stop="el.hidden = !el.hidden"
                                    class="p-0.5 opacity-40 hover:opacity-100 transition-opacity"
                                    :title="el.hidden ? 'Show' : 'Hide'"
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            v-if="!el.hidden"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                        <path
                                            v-else
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click.stop="lockElement(el)"
                                    class="p-0.5 opacity-0 group-hover/layer:opacity-40 hover:!opacity-100 transition-opacity"
                                    :class="
                                        el.locked
                                            ? 'text-amber-500 opacity-100!'
                                            : ''
                                    "
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            v-if="el.locked"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />
                                        <path
                                            v-else
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click.stop="
                                        deleteElement(selectedPageIndex, el.id)
                                    "
                                    class="p-0.5 opacity-0 group-hover/layer:opacity-40 hover:!opacity-100 hover:text-red-500 transition-opacity"
                                >
                                    <svg
                                        class="w-3 h-3"
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
                                </button>
                            </div>
                        </template>
                        <div
                            v-else
                            class="text-center py-8 text-[11px]"
                            :class="dark ? 'text-slate-700' : 'text-slate-400'"
                        >
                            No elements on this page
                        </div>
                    </div>
                </template>

                <!-- ── SETTINGS TAB ── -->
                <template v-if="leftTab === 'settings'">
                    <div class="flex-1 overflow-y-auto p-3 space-y-4">
                        <!-- Page Size -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Page Size
                            </div>
                            <select v-model="rs.page_size" :class="inputCls">
                                <option
                                    v-for="s in PAGE_SIZES"
                                    :key="s.value"
                                    :value="s.value"
                                >
                                    {{ s.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Orientation -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Orientation
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <button
                                    v-for="o in ['portrait', 'landscape']"
                                    :key="o"
                                    @click="rs.orientation = o"
                                    class="flex flex-col items-center justify-center py-3 rounded-xl border text-xs font-bold transition-all capitalize"
                                    :class="
                                        rs.orientation === o
                                            ? 'bg-indigo-600 text-white border-indigo-600'
                                            : dark
                                              ? 'bg-slate-800 text-slate-400 border-slate-700 hover:border-indigo-500'
                                              : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300'
                                    "
                                >
                                    <svg
                                        v-if="o === 'portrait'"
                                        class="w-3 h-4 mx-auto mb-1"
                                        viewBox="0 0 10 14"
                                    >
                                        <rect
                                            x="1"
                                            y="1"
                                            width="8"
                                            height="12"
                                            rx="1"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-4 h-3 mx-auto mb-1"
                                        viewBox="0 0 14 10"
                                    >
                                        <rect
                                            x="1"
                                            y="1"
                                            width="12"
                                            height="8"
                                            rx="1"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        />
                                    </svg>
                                    {{ o }}
                                </button>
                            </div>
                        </div>

                        <!-- Brand Colors -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Brand Colors
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <template
                                    v-for="cp in COLOR_PROPS"
                                    :key="cp.key"
                                >
                                    <div>
                                        <div
                                            class="text-[9px] font-bold mb-1"
                                            :class="
                                                dark
                                                    ? 'text-slate-600'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            {{ cp.label }}
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <input
                                                type="color"
                                                v-model="rs[cp.key]"
                                                class="w-7 h-7 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                                                :class="
                                                    dark
                                                        ? 'border-slate-700 bg-slate-800'
                                                        : 'border-slate-200'
                                                "
                                            />
                                            <input
                                                type="text"
                                                v-model="rs[cp.key]"
                                                class="flex-1 min-w-0 text-[10px] font-mono rounded border px-1.5 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                :class="
                                                    dark
                                                        ? 'bg-slate-800 border-slate-700 text-slate-300'
                                                        : 'bg-white border-slate-200 text-slate-700'
                                                "
                                            />
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Font -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Font Family
                            </div>
                            <select v-model="rs.font_family" :class="inputCls">
                                <option
                                    v-for="f in FONTS"
                                    :key="f.value"
                                    :value="f.value"
                                >
                                    {{ f.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Page Margin -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Page Margin (px)
                            </div>
                            <input
                                type="number"
                                v-model.number="rs.margin"
                                :class="inputCls"
                                min="0"
                                max="120"
                                step="4"
                            />
                        </div>

                        <!-- Page Border Radius -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Canvas Border Radius (px)
                            </div>
                            <input
                                type="number"
                                v-model.number="rs.page_radius"
                                :class="inputCls"
                                min="0"
                                max="32"
                            />
                        </div>

                        <!-- Page Background Image -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Page BG Image URL
                            </div>
                            <input
                                type="url"
                                v-model="rs.bg_image"
                                :class="inputCls"
                                placeholder="https://…"
                            />
                        </div>

                        <!-- Header / Footer -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Header Text
                            </div>
                            <input
                                type="text"
                                v-model="rs.header_text"
                                :class="inputCls"
                                placeholder="Organisation name…"
                            />
                        </div>
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Footer Text
                            </div>
                            <input
                                type="text"
                                v-model="rs.footer_text"
                                :class="inputCls"
                                placeholder="Confidential · GBRSP 2026"
                            />
                        </div>

                        <!-- Watermark -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Watermark Text
                            </div>
                            <input
                                type="text"
                                v-model="rs.watermark"
                                :class="inputCls"
                                placeholder="DRAFT · CONFIDENTIAL"
                            />
                        </div>

                        <!-- Grid Size -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Grid Size (px)
                            </div>
                            <input
                                type="number"
                                v-model.number="gridSize"
                                :class="inputCls"
                                min="5"
                                max="60"
                                step="5"
                            />
                        </div>

                        <!-- Display Options -->
                        <div>
                            <div
                                class="text-[9px] font-black uppercase tracking-widest mb-1.5"
                                :class="
                                    dark ? 'text-slate-500' : 'text-slate-400'
                                "
                            >
                                Display Options
                            </div>
                            <div class="space-y-0">
                                <div
                                    v-for="t in SETTING_TOGGLES"
                                    :key="t.key"
                                    class="flex items-center justify-between py-1.5 border-b last:border-b-0"
                                    :class="
                                        dark
                                            ? 'border-slate-800'
                                            : 'border-slate-100'
                                    "
                                >
                                    <label
                                        class="text-xs font-semibold"
                                        :class="
                                            dark
                                                ? 'text-slate-400'
                                                : 'text-slate-600'
                                        "
                                        >{{ t.label }}</label
                                    >
                                    <button
                                        @click="setToggle(t.key, !getToggle(t.key))"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                        :class="
                                            getToggle(t.key)
                                                ? 'bg-indigo-600'
                                                : dark
                                                  ? 'bg-slate-700'
                                                  : 'bg-slate-200'
                                        "
                                    >
                                        <span
                                            class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform"
                                            :class="
                                                getToggle(t.key)
                                                    ? 'translate-x-5'
                                                    : 'translate-x-1'
                                            "
                                        ></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </aside>

            <!-- ═══════════ CANVAS ═══════════ -->
            <main
                class="flex-1 overflow-auto relative"
                ref="canvasArea"
                @click.self="deselectAll"
                :class="[
                    dark ? 'bg-slate-950' : 'bg-slate-100',
                    dragMode ? 'cursor-crosshair' : '',
                ]"
            >
                <!-- Drag mode banner -->
                <div
                    v-if="dragMode"
                    class="sticky top-0 z-30 flex justify-center py-1.5 pointer-events-none"
                >
                    <div
                        class="flex items-center gap-2 bg-violet-600 text-white text-[11px] font-bold px-4 py-1.5 rounded-full shadow-lg shadow-violet-500/30"
                    >
                        <svg
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"
                            />
                        </svg>
                        Drag Mode Active — click &amp; drag elements freely ·
                        Press
                        <kbd class="bg-white/20 px-1 rounded text-[10px] mx-0.5"
                            >D</kbd
                        >
                        to exit
                    </div>
                </div>

                <!-- Zoom bar -->
                <div
                    class="sticky z-20 flex justify-center pointer-events-none"
                    :class="dragMode ? 'top-10' : 'top-3'"
                >
                    <div
                        class="flex items-center gap-1 backdrop-blur-sm border rounded-xl px-2 py-1 shadow-sm pointer-events-auto"
                        :class="
                            dark
                                ? 'bg-slate-900/90 border-slate-800'
                                : 'bg-white/95 border-slate-200'
                        "
                    >
                        <button
                            @click="zoom = Math.max(25, zoom - 10)"
                            class="p-1.5 rounded-lg transition-colors"
                            :class="
                                dark
                                    ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                    : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                            "
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
                                    d="M20 12H4"
                                />
                            </svg>
                        </button>
                        <span
                            class="text-xs font-black tabular-nums w-12 text-center"
                            :class="dark ? 'text-slate-300' : 'text-slate-600'"
                            >{{ zoom }}%</span
                        >
                        <button
                            @click="zoom = Math.min(200, zoom + 10)"
                            class="p-1.5 rounded-lg transition-colors"
                            :class="
                                dark
                                    ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                    : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                            "
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
                        </button>
                        <div
                            class="w-px h-4 mx-1"
                            :class="dark ? 'bg-slate-700' : 'bg-slate-200'"
                        ></div>
                        <button
                            v-for="z in [50, 75, 100, 125, 150]"
                            :key="z"
                            @click="zoom = z"
                            class="text-[10px] font-bold px-1.5 rounded transition-colors"
                            :class="
                                zoom === z
                                    ? 'text-indigo-500'
                                    : dark
                                      ? 'text-slate-500 hover:text-slate-300'
                                      : 'text-slate-400 hover:text-indigo-600'
                            "
                        >
                            {{ z }}%
                        </button>
                        <div
                            class="w-px h-4 mx-1"
                            :class="dark ? 'bg-slate-700' : 'bg-slate-200'"
                        ></div>
                        <button
                            @click="fitToScreen"
                            class="p-1.5 rounded-lg transition-colors"
                            :class="
                                dark
                                    ? 'text-slate-400 hover:text-slate-200 hover:bg-white/10'
                                    : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                            "
                            title="Fit to screen"
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
                                    d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    class="py-8 flex flex-col items-center gap-8"
                    style="padding-top: 52px"
                >
                    <div
                        v-for="(page, pi) in pages"
                        :key="page.id"
                        class="relative"
                    >
                        <!-- Page toolbar -->
                        <div
                            class="flex items-center justify-between mb-2"
                            :style="{
                                width:
                                    canvasDimensions.width * (zoom / 100) +
                                    'px',
                            }"
                        >
                            <span
                                class="text-[11px] font-bold"
                                :class="
                                    dark ? 'text-slate-600' : 'text-slate-400'
                                "
                                >Page {{ pi + 1 }} / {{ pages.length }}</span
                            >
                            <div class="flex items-center gap-3">
                                <button
                                    @click="duplicatePage(pi)"
                                    class="text-[10px] font-semibold flex items-center gap-1 transition-colors"
                                    :class="
                                        dark
                                            ? 'text-slate-600 hover:text-indigo-400'
                                            : 'text-slate-400 hover:text-indigo-600'
                                    "
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                        /></svg
                                    >Duplicate
                                </button>
                                <button
                                    @click="addPageAfter(pi)"
                                    class="text-[10px] font-semibold flex items-center gap-1 transition-colors"
                                    :class="
                                        dark
                                            ? 'text-slate-600 hover:text-indigo-400'
                                            : 'text-slate-400 hover:text-indigo-600'
                                    "
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        /></svg
                                    >Add After
                                </button>
                                <button
                                    v-if="pages.length > 1"
                                    @click="removePage(pi)"
                                    class="text-[10px] font-semibold flex items-center gap-1 text-red-400 hover:text-red-600 transition-colors"
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        /></svg
                                    >Delete
                                </button>
                            </div>
                        </div>

                        <!-- Canvas -->
                        <div
                            class="canvas-page relative shadow-2xl overflow-hidden select-none transition-all"
                            :class="[
                                isPageActive(pi)
                                    ? 'ring-2 ring-indigo-400 ring-offset-2'
                                    : '',
                                isDragOver && dragTargetPage === pi
                                    ? 'ring-2 ring-violet-400 ring-offset-2'
                                    : '',
                            ]"
                            :style="getCanvasStyle()"
                            @contextmenu.prevent
                            @dragover.prevent="handleDragOver($event, pi)"
                            @dragleave="isDragOver = false"
                            @drop="handleDrop($event, pi)"
                            @click.self="deselectAll"
                            @mousedown.self="() => {}"
                        >
                            <!-- Header band -->
                            <div
                                v-if="rs.show_header && rs.header_text"
                                class="absolute top-0 left-0 right-0 flex items-center px-4 z-10 pointer-events-none"
                                :style="{
                                    height: Math.max(rs.margin / 2, 24) + 'px',
                                    backgroundColor:
                                        rs.header_color || '#1e293b',
                                    opacity: 0.92,
                                }"
                            >
                                <span
                                    class="text-white text-[10px] font-bold tracking-widest uppercase opacity-80"
                                    >{{ rs.header_text }}</span
                                >
                            </div>
                            <!-- Footer band -->
                            <div
                                v-if="rs.show_footer && rs.footer_text"
                                class="absolute bottom-0 left-0 right-0 flex items-center justify-between px-4 z-10 pointer-events-none"
                                :style="{
                                    height: Math.max(rs.margin / 2, 24) + 'px',
                                    backgroundColor:
                                        rs.footer_color || '#1e293b',
                                    opacity: 0.92,
                                }"
                            >
                                <span
                                    class="text-white text-[10px] opacity-60"
                                    >{{ rs.footer_text }}</span
                                >
                                <span
                                    v-if="rs.show_page_numbers"
                                    class="text-white text-[10px] opacity-60"
                                    >{{ pi + 1 }}</span
                                >
                            </div>
                            <!-- Watermark -->
                            <div
                                v-if="rs.watermark"
                                class="absolute inset-0 flex items-center justify-center pointer-events-none z-[2] select-none"
                                style="transform: rotate(-35deg)"
                            >
                                <span
                                    class="font-black opacity-[0.04] tracking-widest"
                                    :style="{
                                        color: rs.text_color || '#000',
                                        fontSize:
                                            canvasDimensions.width *
                                                (zoom / 100) *
                                                0.08 +
                                            'px',
                                    }"
                                    >{{ rs.watermark }}</span
                                >
                            </div>
                            <!-- Grid -->
                            <div
                                v-if="showGrid"
                                class="absolute inset-0 pointer-events-none z-[1]"
                                :style="`background-image:radial-gradient(circle,${dark ? '#ffffff18' : '#94a3b880'} 1px,transparent 1px);background-size:${gridSize}px ${gridSize}px`"
                            ></div>
                            <!-- Rulers -->
                            <div
                                v-if="showRulers"
                                class="absolute top-0 left-0 right-0 h-4 z-20 pointer-events-none overflow-hidden"
                                :style="{
                                    background: dark
                                        ? 'rgba(15,23,42,0.9)'
                                        : 'rgba(248,250,252,0.9)',
                                    borderBottom:
                                        '1px solid ' +
                                        (dark ? '#1e293b' : '#e2e8f0'),
                                }"
                            >
                                <div class="flex h-full">
                                    <div
                                        v-for="n in Math.ceil(
                                            canvasDimensions.width / 50,
                                        )"
                                        :key="n"
                                        class="flex-shrink-0 flex items-end pb-0.5 border-r justify-start"
                                        :style="{
                                            width: (50 * zoom) / 100 + 'px',
                                            borderColor: dark
                                                ? '#1e293b'
                                                : '#e2e8f0',
                                        }"
                                    >
                                        <span
                                            class="text-[7px] pl-0.5"
                                            :class="
                                                dark
                                                    ? 'text-slate-700'
                                                    : 'text-slate-400'
                                            "
                                            >{{ (n - 1) * 50 }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <!-- Drop hint -->
                            <div
                                v-if="
                                    isDragOver &&
                                    dragTargetPage === pi &&
                                    page.elements.length === 0
                                "
                                class="absolute inset-8 border-2 border-dashed border-indigo-400 rounded-2xl flex items-center justify-center z-10 pointer-events-none bg-indigo-500/5"
                            >
                                <div class="text-center">
                                    <svg
                                        class="w-10 h-10 text-indigo-400 mx-auto mb-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                    <span
                                        class="text-indigo-500 text-sm font-bold"
                                        >Drop element here</span
                                    >
                                </div>
                            </div>

                            <!-- ── ELEMENTS ── -->
                            <div
                                v-for="el in page.elements"
                                :key="el.id"
                                class="element-wrapper absolute group"
                                :class="{
                                    'ring-2 ring-indigo-500 ring-offset-1 z-[100]':
                                        selectedElement?.id === el.id,
                                    'hover:ring-1 hover:ring-indigo-300/70':
                                        !selectedElement ||
                                        selectedElement.id !== el.id,
                                    'cursor-move':
                                        dragMode && !el.locked,
                                    'cursor-not-allowed opacity-70': el.locked,
                                    'opacity-40': el.hidden,
                                }"
                                :style="getElementWrapperStyle(el)"
                                @mousedown="startDrag($event, el, pi)"
                                @click.stop="selectElement(el, pi)"
                                @contextmenu.prevent="
                                    openContextMenu($event, el, pi)
                                "
                            >
                                <!-- Resize & Rotate Handles -->
                                <template
                                    v-if="
                                        selectedElement?.id === el.id && !el.locked
                                    "
                                >
                                    <div
                                        v-for="h in RESIZE_HANDLES"
                                        :key="h.dir"
                                        class="resize-handle absolute w-2.5 h-2.5 bg-white border-2 border-indigo-500 rounded-full z-[200] hover:scale-125 transition-transform shadow"
                                        :style="h.style"
                                        @mousedown.stop="startResize(h.dir, $event)"
                                    ></div>
                                    <div
                                        class="absolute top-[-30px] left-1/2 -translate-x-1/2 w-5 h-5 bg-white border-2 border-indigo-500 rounded-full cursor-crosshair z-[200] flex items-center justify-center shadow"
                                        @mousedown.stop="startRotation($event, el)"
                                        title="Rotate"
                                    >
                                        <svg
                                            class="w-2.5 h-2.5 text-indigo-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                            />
                                        </svg>
                                    </div>
                                    <!-- Quick action bar -->
                                    <div
                                        class="absolute -top-7 left-0 flex items-center gap-1 z-[200]"
                                    >
                                        <span
                                            class="bg-indigo-600 text-white text-[9px] px-1.5 py-0.5 rounded font-black"
                                            >{{
                                                getElementLabel(el.type)
                                            }}</span
                                        >
                                        <!-- Move to previous page -->
                                        <button
                                            v-if="pi > 0"
                                            @click.stop="
                                                moveElementToPage(el, pi, pi - 1)
                                            "
                                            class="bg-white text-slate-500 hover:text-indigo-600 text-[9px] px-1 py-0.5 rounded border border-slate-200 transition-colors"
                                            title="Move to previous page"
                                        >
                                            <svg
                                                class="w-2.5 h-2.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 19l-7-7 7-7"
                                                />
                                            </svg>
                                        </button>
                                        <!-- Move to next page -->
                                        <button
                                            v-if="pi < pages.length - 1"
                                            @click.stop="
                                                moveElementToPage(el, pi, pi + 1)
                                            "
                                            class="bg-white text-slate-500 hover:text-indigo-600 text-[9px] px-1 py-0.5 rounded border border-slate-200 transition-colors"
                                            title="Move to next page"
                                        >
                                            <svg
                                                class="w-2.5 h-2.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 5l7 7-7 7"
                                                />
                                            </svg>
                                        </button>
                                        <!-- Duplicate -->
                                        <button
                                            @click.stop="duplicateElement(pi, el)"
                                            class="bg-white text-slate-500 hover:text-indigo-600 text-[9px] px-1 py-0.5 rounded border border-slate-200 transition-colors"
                                            title="Duplicate (Ctrl+D)"
                                        >
                                            <svg
                                                class="w-2.5 h-2.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                />
                                            </svg>
                                        </button>
                                        <!-- Lock -->
                                        <button
                                            @click.stop="lockElement(el)"
                                            class="text-[9px] px-1 py-0.5 rounded border transition-colors"
                                            :class="
                                                el.locked
                                                    ? 'bg-amber-500 text-white border-amber-500'
                                                    : 'bg-white text-slate-500 hover:text-amber-500 border-slate-200'
                                            "
                                            title="Lock/Unlock"
                                        >
                                            <svg
                                                class="w-2.5 h-2.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                />
                                            </svg>
                                        </button>
                                        <!-- Delete -->
                                        <button
                                            @click.stop="deleteElement(pi, el.id)"
                                            class="bg-red-500 text-white text-[9px] px-1 py-0.5 rounded hover:bg-red-600 transition-colors"
                                            title="Delete (Del)"
                                        >
                                            <svg
                                                class="w-2.5 h-2.5"
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
                                        </button>
                                    </div>
                                </template>

                                <!-- Element renderer -->
                                <div
                                    :style="getElementContentStyle(el)"
                                    class="w-full h-full overflow-hidden"
                                    @contextmenu.prevent
                                >
                                    <!-- TEXT / HEADING / SUBHEADING -->
                                    <div
                                        v-if="
                                            [
                                                'text',
                                                'heading',
                                                'subheading',
                                            ].includes(el.type)
                                        "
                                        :contenteditable="
                                            selectedElement?.id === el.id &&
                                            !el.locked
                                        "
                                        :style="getTextStyle(el)"
                                        class="w-full h-full outline-none p-1"
                                        @blur="onTextBlur(el, $event)"
                                        @dblclick="focusText($event)"
                                        v-html="el.content"
                                    ></div>

                                    <!-- QUOTE -->
                                    <div
                                        v-else-if="el.type === 'quote'"
                                        :style="getTextStyle(el)"
                                        class="w-full h-full flex items-center pl-5 border-l-4 border-indigo-500"
                                    >
                                        <div
                                            :contenteditable="
                                                selectedElement?.id === el.id &&
                                                !el.locked
                                            "
                                            class="w-full outline-none italic"
                                            @blur="onTextBlur(el, $event)"
                                            @dblclick="focusText($event)"
                                            v-html="el.content"
                                        ></div>
                                    </div>

                                    <!-- BLOCKQUOTE -->
                                    <div
                                        v-else-if="el.type === 'blockquote'"
                                        class="w-full h-full rounded-xl overflow-hidden"
                                        :style="{
                                            background:
                                                el.styles?.backgroundGradient ||
                                                el.styles?.backgroundColor ||
                                                rs.primary_color + '10',
                                        }"
                                    >
                                        <div
                                            class="h-1"
                                            :style="{
                                                backgroundColor:
                                                    rs.primary_color,
                                            }"
                                        ></div>
                                        <div class="p-4">
                                            <div
                                                :contenteditable="
                                                    selectedElement?.id ===
                                                        el.id && !el.locked
                                                "
                                                class="text-sm italic outline-none font-medium leading-relaxed"
                                                :style="{
                                                    color:
                                                        el.styles?.color ||
                                                        rs.text_color,
                                                }"
                                                @blur="onTextBlur(el, $event)"
                                                v-html="
                                                    el.content ||
                                                    'Block quote text…'
                                                "
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- HIGHLIGHT -->
                                    <div
                                        v-else-if="el.type === 'highlight'"
                                        class="w-full h-full flex items-center"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#fef9c3',
                                            padding: '4px 8px',
                                            borderRadius: '4px',
                                        }"
                                    >
                                        <div
                                            :contenteditable="
                                                selectedElement?.id ===
                                                    el.id && !el.locked
                                            "
                                            class="w-full outline-none font-semibold text-sm"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    '#713f12',
                                            }"
                                            @blur="onTextBlur(el, $event)"
                                            v-html="
                                                el.content || 'Highlighted text'
                                            "
                                        ></div>
                                    </div>

                                    <!-- LIST -->
                                    <div
                                        v-else-if="el.type === 'list'"
                                        :style="getTextStyle(el)"
                                        class="w-full h-full overflow-auto p-2"
                                    >
                                        <ul
                                            :class="
                                                el.styles?.listStyle ===
                                                'numbered'
                                                    ? 'list-decimal pl-5'
                                                    : el.styles?.listStyle ===
                                                        'none'
                                                      ? 'pl-0'
                                                      : 'list-disc pl-5'
                                            "
                                            class="space-y-1"
                                        >
                                            <li
                                                v-for="(item, i) in el.items"
                                                :key="i"
                                                :contenteditable="
                                                    selectedElement?.id ===
                                                        el.id && !el.locked
                                                "
                                                @blur="
                                                    onListItemBlur(
                                                        el,
                                                        i,
                                                        $event,
                                                    )
                                                "
                                                class="outline-none"
                                            >
                                                {{ item }}
                                            </li>
                                        </ul>
                                        <button
                                            v-if="
                                                selectedElement?.id === el.id &&
                                                !el.locked
                                            "
                                            @click.stop="addListItem(el)"
                                            class="mt-1 text-xs text-indigo-500 hover:text-indigo-700"
                                        >
                                            + Add item
                                        </button>
                                    </div>

                                    <!-- CHECKLIST -->
                                    <div
                                        v-else-if="el.type === 'checklist'"
                                        :style="getTextStyle(el)"
                                        class="w-full h-full overflow-auto p-2"
                                    >
                                        <div
                                            v-for="(item, i) in el.items"
                                            :key="i"
                                            class="flex items-start gap-2 py-1"
                                        >
                                            <div
                                                class="w-4 h-4 rounded border-2 flex-shrink-0 mt-0.5 flex items-center justify-center transition-colors cursor-pointer"
                                                :style="{
                                                    borderColor:
                                                        rs.primary_color,
                                                    backgroundColor:
                                                        item.checked
                                                            ? rs.primary_color
                                                            : 'transparent',
                                                }"
                                                @click.stop="
                                                    item.checked = !item.checked
                                                "
                                            >
                                                <svg
                                                    v-if="item.checked"
                                                    class="w-2.5 h-2.5 text-white"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </div>
                                            <span
                                                :contenteditable="
                                                    selectedElement?.id ===
                                                        el.id && !el.locked
                                                "
                                                class="flex-1 outline-none text-xs"
                                                :class="
                                                    item.checked
                                                        ? 'line-through text-slate-400'
                                                        : 'text-slate-700'
                                                "
                                                @blur="
                                                    onChecklistItemBlur(
                                                        el,
                                                        i,
                                                        $event,
                                                    )
                                                "
                                                >{{ item.text }}</span
                                            >
                                        </div>
                                        <button
                                            v-if="
                                                selectedElement?.id === el.id &&
                                                !el.locked
                                            "
                                            @click.stop="addChecklistItem(el)"
                                            class="mt-1 text-xs text-indigo-500"
                                        >
                                            + Add item
                                        </button>
                                    </div>

                                    <!-- IMAGE -->
                                    <div
                                        v-else-if="el.type === 'image'"
                                        class="w-full h-full relative group/img"
                                    >
                                        <img
                                            v-if="el.src"
                                            :src="el.src"
                                            :alt="el.alt || ''"
                                            :style="{
                                                width: '100%',
                                                height: '100%',
                                                objectFit:
                                                    el.styles?.objectFit ||
                                                    'cover',
                                                borderRadius:
                                                    (el.styles?.borderRadius ||
                                                        0) + 'px',
                                                filter:
                                                    el.styles?.filter || 'none',
                                            }"
                                            class="block pointer-events-none"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed"
                                            :class="
                                                dark
                                                    ? 'border-slate-700 bg-slate-800/50'
                                                    : 'border-slate-300 bg-slate-50'
                                            "
                                        >
                                            <svg
                                                class="w-10 h-10 text-slate-300"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            <span
                                                class="text-xs font-semibold"
                                                :class="
                                                    dark
                                                        ? 'text-slate-500'
                                                        : 'text-slate-400'
                                                "
                                                >Click to upload</span
                                            >
                                        </div>
                                        <label
                                            class="absolute inset-0 cursor-pointer"
                                            :class="
                                                el.src
                                                    ? 'opacity-0 hover:opacity-100'
                                                    : ''
                                            "
                                        >
                                            <div
                                                v-if="el.src"
                                                class="w-full h-full flex items-center justify-center bg-black/40 rounded backdrop-blur-sm"
                                            >
                                                <div
                                                    class="flex flex-col items-center gap-1 text-white"
                                                >
                                                    <svg
                                                        class="w-6 h-6"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                                        />
                                                    </svg>
                                                    <span
                                                        class="text-[10px] font-bold"
                                                        >Replace</span
                                                    >
                                                </div>
                                            </div>
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                @change="
                                                    uploadImage($event, el)
                                                "
                                            />
                                        </label>
                                        <div
                                            v-if="el._uploading"
                                            class="absolute inset-0 flex items-center justify-center bg-black/60 rounded"
                                        >
                                            <svg
                                                class="w-8 h-8 animate-spin text-white"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                />
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                                />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- VIDEO -->
                                    <div
                                        v-else-if="el.type === 'video'"
                                        class="w-full h-full relative bg-slate-900 rounded flex items-center justify-center overflow-hidden"
                                    >
                                        <iframe
                                            v-if="el.src"
                                            :src="el.src"
                                            class="w-full h-full"
                                            frameborder="0"
                                            allowfullscreen
                                        ></iframe>
                                        <div
                                            v-else
                                            class="flex flex-col items-center gap-2 text-white/60"
                                        >
                                            <svg
                                                class="w-10 h-10"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M15 10l4.553-2.069A1 1 0 0121 8.867V15.133a1 1 0 01-1.447.902L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                />
                                            </svg>
                                            <span class="text-xs"
                                                >Add video URL in properties</span
                                            >
                                        </div>
                                    </div>

                                    <!-- DIVIDER -->
                                    <div
                                        v-else-if="el.type === 'divider'"
                                        class="w-full h-full flex items-center"
                                    >
                                        <div
                                            class="w-full"
                                            :style="{
                                                borderTop: `${el.styles?.borderWidth || 1}px ${el.styles?.borderStyle || 'solid'} ${el.styles?.color || '#e2e8f0'}`,
                                            }"
                                        ></div>
                                    </div>

                                    <!-- METRIC / KPI -->
                                    <div
                                        v-else-if="el.type === 'metric'"
                                        class="w-full h-full rounded-xl p-4 flex flex-col justify-center"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#f8fafc',
                                            border: `1px solid ${el.styles?.borderColor || '#e2e8f0'}`,
                                        }"
                                    >
                                        <div
                                            class="flex items-center gap-2 mb-1"
                                        >
                                            <span
                                                v-if="el.icon"
                                                class="text-2xl"
                                                >{{ el.icon }}</span
                                            >
                                            <div
                                                class="text-[10px] font-black uppercase tracking-widest"
                                                :style="{
                                                    color:
                                                        el.styles?.labelColor ||
                                                        '#64748b',
                                                }"
                                            >
                                                {{ el.label }}
                                            </div>
                                        </div>
                                        <div
                                            class="text-3xl font-black leading-none"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    rs.primary_color,
                                            }"
                                        >
                                            {{ el.value }}
                                        </div>
                                        <div
                                            v-if="el.change"
                                            class="flex items-center gap-1 mt-1.5"
                                        >
                                            <span
                                                class="text-xs font-black"
                                                :class="
                                                    el.changeType === 'positive'
                                                        ? 'text-emerald-500'
                                                        : 'text-red-500'
                                                "
                                                >{{
                                                    el.changeType === "positive"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                                {{ el.change }}</span
                                            >
                                            <span
                                                class="text-[10px] text-slate-400"
                                                >{{ el.changePeriod }}</span
                                            >
                                        </div>
                                    </div>

                                    <!-- TABLE -->
                                    <div
                                        v-else-if="el.type === 'table'"
                                        class="w-full h-full overflow-auto"
                                    >
                                        <table
                                            class="w-full border-collapse text-xs"
                                        >
                                            <thead>
                                                <tr>
                                                    <th
                                                        v-for="col in el.columns"
                                                        :key="col"
                                                        class="px-3 py-2 text-left text-[10px] font-black uppercase tracking-wider"
                                                        :style="{
                                                            backgroundColor:
                                                                el.styles
                                                                    ?.headerBg ||
                                                                rs.primary_color,
                                                            color:
                                                                el.styles
                                                                    ?.headerColor ||
                                                                '#fff',
                                                        }"
                                                    >
                                                        <span
                                                            :contenteditable="
                                                                selectedElement?.id ===
                                                                    el.id &&
                                                                !el.locked
                                                            "
                                                            @blur="
                                                                onTableHeaderBlur(
                                                                    el,
                                                                    col,
                                                                    $event,
                                                                )
                                                            "
                                                            class="outline-none"
                                                            >{{ col }}</span
                                                        >
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(row, ri) in el.data"
                                                    :key="ri"
                                                    :style="{
                                                        backgroundColor:
                                                            ri % 2 === 0
                                                                ? el.styles
                                                                      ?.evenRowBg ||
                                                                  '#fff'
                                                                : el.styles
                                                                      ?.oddRowBg ||
                                                                  '#f8fafc',
                                                    }"
                                                >
                                                    <td
                                                        v-for="(
                                                            col, ci
                                                        ) in el.columns"
                                                        :key="ci"
                                                        class="px-3 py-2 border-b border-slate-100"
                                                        :contenteditable="
                                                            selectedElement?.id ===
                                                                el.id && !el.locked
                                                        "
                                                        @blur="
                                                            onTableCellBlur(
                                                                el,
                                                                ri,
                                                                ci,
                                                                $event,
                                                            )
                                                        "
                                                    >
                                                        {{ row[col] || "" }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div
                                            v-if="
                                                selectedElement?.id === el.id &&
                                                !el.locked
                                            "
                                            class="flex gap-1.5 p-2 bg-slate-50 border-t border-slate-100"
                                        >
                                            <button
                                                @click.stop="addTableRow(el)"
                                                class="text-[10px] bg-indigo-500 text-white px-2 py-1 rounded-lg font-bold"
                                            >
                                                +Row
                                            </button>
                                            <button
                                                @click.stop="addTableColumn(el)"
                                                class="text-[10px] bg-emerald-500 text-white px-2 py-1 rounded-lg font-bold"
                                            >
                                                +Col
                                            </button>
                                            <button
                                                @click.stop="removeTableRow(el)"
                                                class="text-[10px] bg-red-400 text-white px-2 py-1 rounded-lg font-bold"
                                            >
                                                −Row
                                            </button>
                                            <button
                                                @click.stop="
                                                    removeTableColumn(el)
                                                "
                                                class="text-[10px] bg-orange-400 text-white px-2 py-1 rounded-lg font-bold"
                                            >
                                                −Col
                                            </button>
                                        </div>
                                    </div>

                                    <!-- CHART -->
                                    <div
                                        v-else-if="isChartType(el.type)"
                                        class="w-full h-full relative"
                                    >
                                        <canvas
                                            :id="'chart-' + el.id"
                                            class="w-full h-full"
                                        ></canvas>
                                    </div>

                                    <!-- RECTANGLE -->
                                    <div
                                        v-else-if="el.type === 'rectangle'"
                                        class="w-full h-full"
                                        :style="{
                                            background:
                                                el.styles?.backgroundGradient ||
                                                el.styles?.backgroundColor ||
                                                '#e0e7ff',
                                            borderRadius:
                                                (el.styles?.borderRadius || 0) +
                                                'px',
                                            border: el.styles?.borderWidth
                                                ? `${el.styles.borderWidth}px solid ${el.styles?.borderColor || '#000'}`
                                                : 'none',
                                        }"
                                    ></div>

                                    <!-- CIRCLE -->
                                    <div
                                        v-else-if="el.type === 'circle'"
                                        class="w-full h-full rounded-full"
                                        :style="{
                                            background:
                                                el.styles?.backgroundGradient ||
                                                el.styles?.backgroundColor ||
                                                '#6366f1',
                                            border: el.styles?.borderWidth
                                                ? `${el.styles.borderWidth}px solid ${el.styles?.borderColor || '#000'}`
                                                : 'none',
                                        }"
                                    ></div>

                                    <!-- TRIANGLE -->
                                    <div
                                        v-else-if="el.type === 'triangle'"
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <div
                                            :style="{
                                                width: 0,
                                                height: 0,
                                                borderLeft: `${(el.styles?.width || 100) / 2}px solid transparent`,
                                                borderRight: `${(el.styles?.width || 100) / 2}px solid transparent`,
                                                borderBottom: `${el.styles?.height || 100}px solid ${el.styles?.backgroundColor || '#f59e0b'}`,
                                            }"
                                        ></div>
                                    </div>

                                    <!-- STAR -->
                                    <div
                                        v-else-if="el.type === 'star'"
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <svg
                                            viewBox="0 0 100 100"
                                            class="w-full h-full"
                                        >
                                            <polygon
                                                points="50,5 61,35 95,35 68,57 79,91 50,70 21,91 32,57 5,35 39,35"
                                                :fill="
                                                    el.styles?.backgroundColor ||
                                                    rs.primary_color
                                                "
                                            />
                                        </svg>
                                    </div>

                                    <!-- LINE -->
                                    <svg
                                        v-else-if="el.type === 'line'"
                                        class="w-full h-full"
                                        preserveAspectRatio="none"
                                    >
                                        <line
                                            x1="0"
                                            y1="50%"
                                            x2="100%"
                                            y2="50%"
                                            :stroke="
                                                el.styles?.color || '#64748b'
                                            "
                                            :stroke-width="
                                                el.styles?.borderWidth || 2
                                            "
                                            :stroke-dasharray="
                                                el.styles?.borderStyle ===
                                                'dashed'
                                                    ? '8 4'
                                                    : el.styles?.borderStyle ===
                                                        'dotted'
                                                      ? '2 4'
                                                      : 'none'
                                            "
                                        />
                                    </svg>

                                    <!-- ARROW -->
                                    <svg
                                        v-else-if="el.type === 'arrow'"
                                        class="w-full h-full"
                                        preserveAspectRatio="none"
                                    >
                                        <defs>
                                            <marker
                                                id="ah"
                                                markerWidth="10"
                                                markerHeight="7"
                                                refX="10"
                                                refY="3.5"
                                                orient="auto"
                                            >
                                                <polygon
                                                    points="0 0,10 3.5,0 7"
                                                    :fill="
                                                        el.styles?.color ||
                                                        '#6366f1'
                                                    "
                                                />
                                            </marker>
                                        </defs>
                                        <line
                                            x1="5"
                                            y1="50%"
                                            x2="calc(100% - 15px)"
                                            y2="50%"
                                            :stroke="
                                                el.styles?.color || '#6366f1'
                                            "
                                            :stroke-width="
                                                el.styles?.borderWidth || 2
                                            "
                                            marker-end="url(#ah)"
                                        />
                                    </svg>

                                    <!-- DOUBLE ARROW -->
                                    <svg
                                        v-else-if="el.type === 'double-arrow'"
                                        class="w-full h-full"
                                        preserveAspectRatio="none"
                                    >
                                        <defs>
                                            <marker
                                                id="dah-end"
                                                markerWidth="10"
                                                markerHeight="7"
                                                refX="10"
                                                refY="3.5"
                                                orient="auto"
                                            >
                                                <polygon
                                                    points="0 0,10 3.5,0 7"
                                                    :fill="
                                                        el.styles?.color ||
                                                        '#6366f1'
                                                    "
                                                />
                                            </marker>
                                            <marker
                                                id="dah-start"
                                                markerWidth="10"
                                                markerHeight="7"
                                                refX="0"
                                                refY="3.5"
                                                orient="auto-start-reverse"
                                            >
                                                <polygon
                                                    points="0 0,10 3.5,0 7"
                                                    :fill="
                                                        el.styles?.color ||
                                                        '#6366f1'
                                                    "
                                                />
                                            </marker>
                                        </defs>
                                        <line
                                            x1="15"
                                            y1="50%"
                                            x2="calc(100% - 15px)"
                                            y2="50%"
                                            :stroke="
                                                el.styles?.color || '#6366f1'
                                            "
                                            :stroke-width="
                                                el.styles?.borderWidth || 2
                                            "
                                            marker-end="url(#dah-end)"
                                            marker-start="url(#dah-start)"
                                        />
                                    </svg>

                                    <!-- PROGRESS -->
                                    <div
                                        v-else-if="el.type === 'progress'"
                                        class="w-full h-full flex flex-col justify-center gap-1 px-1"
                                    >
                                        <div
                                            class="flex justify-between text-xs"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    '#374151',
                                            }"
                                        >
                                            <span class="font-semibold">{{
                                                el.label
                                            }}</span>
                                            <span class="font-black"
                                                >{{ el.value }}%</span
                                            >
                                        </div>
                                        <div
                                            class="w-full overflow-hidden"
                                            :style="{
                                                borderRadius:
                                                    el.styles?.progressShape ===
                                                    'square'
                                                        ? '2px'
                                                        : '99px',
                                                height:
                                                    (el.styles?.trackHeight ||
                                                        8) + 'px',
                                                backgroundColor:
                                                    el.styles?.trackColor ||
                                                    '#e2e8f0',
                                            }"
                                        >
                                            <div
                                                class="h-full transition-all"
                                                :style="{
                                                    width: el.value + '%',
                                                    backgroundColor:
                                                        el.styles?.color ||
                                                        rs.primary_color,
                                                    borderRadius:
                                                        el.styles
                                                            ?.progressShape ===
                                                        'square'
                                                            ? '2px'
                                                            : '99px',
                                                }"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- CIRCULAR PROGRESS -->
                                    <div
                                        v-else-if="
                                            el.type === 'circular-progress'
                                        "
                                        class="w-full h-full flex flex-col items-center justify-center gap-1"
                                    >
                                        <svg
                                            class="transform -rotate-90"
                                            :style="{
                                                width: '80%',
                                                height: '80%',
                                            }"
                                            viewBox="0 0 36 36"
                                        >
                                            <circle
                                                cx="18"
                                                cy="18"
                                                r="15.9"
                                                fill="none"
                                                :stroke="
                                                    el.styles?.trackColor ||
                                                    '#e2e8f0'
                                                "
                                                stroke-width="3.2"
                                            />
                                            <circle
                                                cx="18"
                                                cy="18"
                                                r="15.9"
                                                fill="none"
                                                :stroke="
                                                    el.styles?.color ||
                                                    rs.primary_color
                                                "
                                                stroke-width="3.2"
                                                :stroke-dasharray="`${el.value} ${100 - el.value}`"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                        <div class="text-center">
                                            <div
                                                class="font-black text-lg"
                                                :style="{
                                                    color:
                                                        el.styles?.color ||
                                                        rs.primary_color,
                                                }"
                                            >
                                                {{ el.value }}%
                                            </div>
                                            <div
                                                class="text-[10px] text-slate-400 font-semibold"
                                            >
                                                {{ el.label }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- BADGE -->
                                    <div
                                        v-else-if="el.type === 'badge'"
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <span
                                            class="px-3 py-1.5 rounded-full text-xs font-black"
                                            :style="{
                                                backgroundColor:
                                                    el.styles
                                                        ?.backgroundColor ||
                                                    rs.primary_color + '20',
                                                color:
                                                    el.styles?.color ||
                                                    rs.primary_color,
                                            }"
                                            >{{ el.content || "Badge" }}</span
                                        >
                                    </div>

                                    <!-- CALLOUT -->
                                    <div
                                        v-else-if="el.type === 'callout'"
                                        class="w-full h-full rounded-xl p-3 flex items-start gap-2.5"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#eff6ff',
                                            border: `1px solid ${el.styles?.borderColor || '#bfdbfe'}`,
                                        }"
                                    >
                                        <span class="text-xl flex-shrink-0">{{
                                            el.emoji || "ℹ️"
                                        }}</span>
                                        <div
                                            :contenteditable="
                                                selectedElement?.id ===
                                                    el.id && !el.locked
                                            "
                                            class="flex-1 outline-none text-xs leading-relaxed"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    '#1e40af',
                                            }"
                                            @blur="onTextBlur(el, $event)"
                                            v-html="
                                                el.content || 'Callout text…'
                                            "
                                        ></div>
                                    </div>

                                    <!-- ALERT -->
                                    <div
                                        v-else-if="el.type === 'alert'"
                                        class="w-full h-full flex items-center gap-3 px-4 rounded-xl"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#fef3c7',
                                            borderLeft: `4px solid ${el.styles?.borderColor || '#f59e0b'}`,
                                        }"
                                    >
                                        <span class="text-xl flex-shrink-0">{{
                                            el.icon || "⚠️"
                                        }}</span>
                                        <div
                                            :contenteditable="
                                                selectedElement?.id ===
                                                    el.id && !el.locked
                                            "
                                            class="flex-1 outline-none text-sm font-semibold"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    '#92400e',
                                            }"
                                            @blur="onTextBlur(el, $event)"
                                            v-html="
                                                el.content || 'Alert message here'
                                            "
                                        ></div>
                                    </div>

                                    <!-- SIGNATURE -->
                                    <div
                                        v-else-if="el.type === 'signature'"
                                        class="w-full h-full flex flex-col justify-end"
                                        :style="{
                                            borderBottom: `2px solid ${el.styles?.color || '#334155'}`,
                                        }"
                                    >
                                        <span
                                            class="text-xs mb-1"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    '#94a3b8',
                                            }"
                                            >{{ el.label || "Signature" }}</span
                                        >
                                    </div>

                                    <!-- CODE -->
                                    <div
                                        v-else-if="el.type === 'code'"
                                        class="w-full h-full overflow-auto rounded-lg bg-slate-900 p-3"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 mb-2"
                                        >
                                            <div
                                                class="w-2.5 h-2.5 rounded-full bg-red-500"
                                            ></div>
                                            <div
                                                class="w-2.5 h-2.5 rounded-full bg-amber-500"
                                            ></div>
                                            <div
                                                class="w-2.5 h-2.5 rounded-full bg-emerald-500"
                                            ></div>
                                            <span
                                                v-if="el.language"
                                                class="ml-auto text-[9px] text-slate-500 font-mono"
                                                >{{ el.language }}</span
                                            >
                                        </div>
                                        <pre
                                            class="text-emerald-400 text-xs font-mono whitespace-pre-wrap"
                                            :contenteditable="
                                                selectedElement?.id ===
                                                    el.id && !el.locked
                                            "
                                            @blur="onTextBlur(el, $event)"
                                            >{{ el.content }}</pre
                                        >
                                    </div>

                                    <!-- LINK -->
                                    <div
                                        v-else-if="el.type === 'link'"
                                        :style="getTextStyle(el)"
                                        class="w-full h-full flex items-center"
                                    >
                                        <a
                                            :href="el.href || '#'"
                                            class="underline font-semibold"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    rs.primary_color,
                                            }"
                                            @click.prevent
                                            >{{
                                                el.content ||
                                                el.href ||
                                                "Click here"
                                            }}</a
                                        >
                                    </div>

                                    <!-- PAGE NUMBER -->
                                    <div
                                        v-else-if="el.type === 'pagenum'"
                                        :style="getTextStyle(el)"
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <span
                                            class="text-xs font-semibold"
                                            :style="{
                                                color:
                                                    el.styles?.color ||
                                                    '#94a3b8',
                                            }"
                                            >[Page {{ pi + 1 }}]</span
                                        >
                                    </div>

                                    <!-- DATE -->
                                    <div
                                        v-else-if="el.type === 'date'"
                                        :style="getTextStyle(el)"
                                        class="w-full h-full flex items-center"
                                    >
                                        <span>{{ formatDateEl(el) }}</span>
                                    </div>

                                    <!-- ICON -->
                                    <div
                                        v-else-if="el.type === 'icon'"
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <span
                                            :style="{
                                                fontSize:
                                                    (el.styles?.fontSize ||
                                                        40) + 'px',
                                                color:
                                                    el.styles?.color ||
                                                    rs.primary_color,
                                            }"
                                            >{{ el.content || "★" }}</span
                                        >
                                    </div>

                                    <!-- RATING -->
                                    <div
                                        v-else-if="el.type === 'rating'"
                                        class="w-full h-full flex items-center justify-center gap-1"
                                    >
                                        <span
                                            v-for="i in 5"
                                            :key="i"
                                            class="text-2xl leading-none"
                                            :style="{
                                                color:
                                                    i <= el.value
                                                        ? el.styles?.color ||
                                                          '#f59e0b'
                                                        : '#e2e8f0',
                                            }"
                                            >★</span
                                        >
                                    </div>

                                    <!-- SPACER -->
                                    <div
                                        v-else-if="el.type === 'spacer'"
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <div
                                            class="border-t-2 border-dashed w-full opacity-20"
                                            :class="
                                                dark
                                                    ? 'border-slate-500'
                                                    : 'border-slate-400'
                                            "
                                        ></div>
                                    </div>

                                    <!-- TABLE OF CONTENTS -->
                                    <div
                                        v-else-if="el.type === 'toc'"
                                        class="w-full h-full p-3 overflow-auto"
                                        :style="getTextStyle(el)"
                                    >
                                        <div
                                            :contenteditable="
                                                selectedElement?.id ===
                                                    el.id && !el.locked
                                            "
                                            class="text-sm font-black mb-2 pb-1 border-b outline-none"
                                            :style="{
                                                borderColor: rs.primary_color,
                                                color: rs.primary_color,
                                            }"
                                            @blur="onTocTitleBlur(el, $event)"
                                        >
                                            {{ el.title || "Table of Contents" }}
                                        </div>
                                        <div
                                            v-for="(item, i) in el.items || []"
                                            :key="i"
                                            class="flex items-center gap-1 py-1 text-xs"
                                        >
                                            <span
                                                :contenteditable="
                                                    selectedElement?.id ===
                                                        el.id && !el.locked
                                                "
                                                class="outline-none"
                                                @blur="
                                                    onTocItemBlur(
                                                        el,
                                                        i,
                                                        'label',
                                                        $event,
                                                    )
                                                "
                                                >{{ item.label }}</span
                                            >
                                            <span
                                                class="border-b border-dotted flex-1 mx-2"
                                                :class="
                                                    dark
                                                        ? 'border-slate-700'
                                                        : 'border-slate-300'
                                                "
                                            ></span>
                                            <span
                                                :contenteditable="
                                                    selectedElement?.id ===
                                                        el.id && !el.locked
                                                "
                                                class="font-bold outline-none"
                                                :style="{
                                                    color: rs.primary_color,
                                                }"
                                                @blur="
                                                    onTocItemBlur(
                                                        el,
                                                        i,
                                                        'page',
                                                        $event,
                                                    )
                                                "
                                                >{{ item.page }}</span
                                            >
                                        </div>
                                        <button
                                            v-if="
                                                selectedElement?.id === el.id &&
                                                !el.locked
                                            "
                                            @click.stop="addTocItem(el)"
                                            class="mt-1 text-xs text-indigo-500"
                                        >
                                            + Add entry
                                        </button>
                                    </div>

                                    <!-- TIMELINE -->
                                    <div
                                        v-else-if="el.type === 'timeline'"
                                        class="w-full h-full p-3 overflow-auto"
                                    >
                                        <div
                                            v-for="(item, i) in el.items || []"
                                            :key="i"
                                            class="flex gap-3 mb-3"
                                        >
                                            <div
                                                class="flex flex-col items-center"
                                            >
                                                <div
                                                    class="w-3 h-3 rounded-full flex-shrink-0 mt-0.5"
                                                    :style="{
                                                        backgroundColor:
                                                            rs.primary_color,
                                                    }"
                                                ></div>
                                                <div
                                                    v-if="
                                                        i <
                                                        (el.items?.length ||
                                                            1) -
                                                            1
                                                    "
                                                    class="w-0.5 flex-1 mt-1"
                                                    :style="{
                                                        backgroundColor:
                                                            rs.primary_color +
                                                            '40',
                                                    }"
                                                ></div>
                                            </div>
                                            <div class="flex-1 pb-2">
                                                <div
                                                    :contenteditable="
                                                        selectedElement?.id ===
                                                            el.id && !el.locked
                                                    "
                                                    class="text-xs font-black outline-none"
                                                    :style="{
                                                        color: rs.primary_color,
                                                    }"
                                                    @blur="
                                                        onTimelineBlur(
                                                            el,
                                                            i,
                                                            'date',
                                                            $event,
                                                        )
                                                    "
                                                >
                                                    {{ item.date }}
                                                </div>
                                                <div
                                                    :contenteditable="
                                                        selectedElement?.id ===
                                                            el.id && !el.locked
                                                    "
                                                    class="text-xs font-bold outline-none"
                                                    :style="{
                                                        color:
                                                            el.styles?.color ||
                                                            rs.text_color,
                                                    }"
                                                    @blur="
                                                        onTimelineBlur(
                                                            el,
                                                            i,
                                                            'label',
                                                            $event,
                                                        )
                                                    "
                                                >
                                                    {{ item.label }}
                                                </div>
                                                <div
                                                    :contenteditable="
                                                        selectedElement?.id ===
                                                            el.id && !el.locked
                                                    "
                                                    class="text-[10px] mt-0.5 outline-none"
                                                    :class="
                                                        dark
                                                            ? 'text-slate-500'
                                                            : 'text-slate-400'
                                                    "
                                                    @blur="
                                                        onTimelineBlur(
                                                            el,
                                                            i,
                                                            'desc',
                                                            $event,
                                                        )
                                                    "
                                                >
                                                    {{ item.desc }}
                                                </div>
                                            </div>
                                        </div>
                                        <button
                                            v-if="
                                                selectedElement?.id === el.id &&
                                                !el.locked
                                            "
                                            @click.stop="addTimelineItem(el)"
                                            class="text-xs text-indigo-500"
                                        >
                                            + Add milestone
                                        </button>
                                    </div>

                                    <!-- SOCIAL CARD / PROFILE -->
                                    <div
                                        v-else-if="el.type === 'social-card'"
                                        class="w-full h-full rounded-xl overflow-hidden flex flex-col"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#fff',
                                            border: `1px solid ${el.styles?.borderColor || '#e2e8f0'}`,
                                        }"
                                    >
                                        <div
                                            class="h-20 flex-shrink-0"
                                            :style="{
                                                backgroundColor: rs.primary_color,
                                            }"
                                        ></div>
                                        <div class="p-4 flex-1">
                                            <div
                                                class="w-14 h-14 rounded-full border-4 border-white bg-slate-200 -mt-9 mb-2 overflow-hidden flex items-center justify-center"
                                            >
                                                <span class="text-2xl">{{
                                                    el.avatar || "👤"
                                                }}</span>
                                            </div>
                                            <div
                                                :contenteditable="
                                                    selectedElement?.id ===
                                                        el.id && !el.locked
                                                "
                                                class="font-black text-sm text-slate-800 outline-none"
                                                @blur="onTextBlur(el, $event)"
                                                v-html="
                                                    el.content || 'Name Here'
                                                "
                                            ></div>
                                            <div class="text-xs text-slate-400 mt-0.5">
                                                {{ el.subtitle || "Title / Organisation" }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TESTIMONIAL -->
                                    <div
                                        v-else-if="el.type === 'testimonial'"
                                        class="w-full h-full rounded-xl p-4 flex flex-col justify-between"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#f8fafc',
                                            border: `1px solid ${el.styles?.borderColor || '#e2e8f0'}`,
                                        }"
                                    >
                                        <div
                                            class="text-3xl text-slate-300 leading-none mb-2"
                                        >
                                            "
                                        </div>
                                        <div
                                            :contenteditable="
                                                selectedElement?.id ===
                                                    el.id && !el.locked
                                            "
                                            class="flex-1 text-sm italic text-slate-600 outline-none"
                                            @blur="onTextBlur(el, $event)"
                                            v-html="
                                                el.content ||
                                                'Add testimonial text here…'
                                            "
                                        ></div>
                                        <div class="mt-3 flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-sm"
                                            >
                                                {{ el.avatar || "👤" }}
                                            </div>
                                            <div>
                                                <div
                                                    class="text-xs font-bold text-slate-700"
                                                >
                                                    {{ el.author || "Author Name" }}
                                                </div>
                                                <div class="text-[10px] text-slate-400">
                                                    {{ el.role || "Title" }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PRICE CARD -->
                                    <div
                                        v-else-if="el.type === 'price-card'"
                                        class="w-full h-full rounded-xl overflow-hidden flex flex-col"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#fff',
                                            border: `1px solid ${el.styles?.borderColor || '#e2e8f0'}`,
                                        }"
                                    >
                                        <div
                                            class="p-5 text-center"
                                            :style="{
                                                backgroundColor: el.highlighted
                                                    ? rs.primary_color
                                                    : '#f8fafc',
                                            }"
                                        >
                                            <div
                                                class="text-sm font-black"
                                                :style="{
                                                    color: el.highlighted
                                                        ? '#fff'
                                                        : rs.text_color,
                                                }"
                                            >
                                                {{ el.plan || "Pro Plan" }}
                                            </div>
                                            <div
                                                class="text-4xl font-black mt-2"
                                                :style="{
                                                    color: el.highlighted
                                                        ? '#fff'
                                                        : rs.primary_color,
                                                }"
                                            >
                                                {{ el.price || "$0" }}
                                            </div>
                                            <div
                                                class="text-xs mt-1"
                                                :style="{
                                                    color: el.highlighted
                                                        ? 'rgba(255,255,255,0.7)'
                                                        : '#94a3b8',
                                                }"
                                            >
                                                {{ el.period || "per month" }}
                                            </div>
                                        </div>
                                        <div class="p-4 flex-1">
                                            <ul class="space-y-1.5">
                                                <li
                                                    v-for="(f, i) in el.features ||
                                                        ['Feature 1', 'Feature 2']"
                                                    :key="i"
                                                    class="flex items-center gap-2 text-xs text-slate-600"
                                                >
                                                    <svg
                                                        class="w-3.5 h-3.5 flex-shrink-0"
                                                        :style="{
                                                            color: rs.primary_color,
                                                        }"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                    {{ f }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- STAT ROW -->
                                    <div
                                        v-else-if="el.type === 'stat-row'"
                                        class="w-full h-full grid gap-2 p-2"
                                        :style="{
                                            gridTemplateColumns: `repeat(${el.cols || 3}, 1fr)`,
                                        }"
                                    >
                                        <div
                                            v-for="(stat, i) in el.stats || [
                                                { label: 'Stat 1', value: '0' },
                                                { label: 'Stat 2', value: '0' },
                                                { label: 'Stat 3', value: '0' },
                                            ]"
                                            :key="i"
                                            class="flex flex-col items-center justify-center rounded-xl p-3 text-center"
                                            :style="{
                                                backgroundColor:
                                                    rs.primary_color + '10',
                                                border: `1px solid ${rs.primary_color}30`,
                                            }"
                                        >
                                            <div
                                                class="text-2xl font-black"
                                                :style="{ color: rs.primary_color }"
                                            >
                                                {{ stat.value }}
                                            </div>
                                            <div
                                                class="text-[10px] font-semibold text-slate-500 mt-0.5"
                                            >
                                                {{ stat.label }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- KANBAN CARD -->
                                    <div
                                        v-else-if="el.type === 'kanban'"
                                        class="w-full h-full rounded-xl overflow-hidden border"
                                        :style="{
                                            backgroundColor:
                                                el.styles?.backgroundColor ||
                                                '#fff',
                                            borderColor:
                                                el.styles?.borderColor ||
                                                '#e2e8f0',
                                        }"
                                    >
                                        <div
                                            class="h-1"
                                            :style="{
                                                backgroundColor:
                                                    el.styles?.accentColor ||
                                                    rs.primary_color,
                                            }"
                                        ></div>
                                        <div class="p-3">
                                            <div
                                                :contenteditable="
                                                    selectedElement?.id ===
                                                        el.id && !el.locked
                                                "
                                                class="text-xs font-bold text-slate-700 outline-none mb-1.5"
                                                @blur="onTextBlur(el, $event)"
                                                v-html="
                                                    el.content || 'Task Title'
                                                "
                                            ></div>
                                            <div
                                                class="flex items-center gap-2 mt-2"
                                            >
                                                <span
                                                    class="text-[9px] px-2 py-0.5 rounded-full font-bold"
                                                    :style="{
                                                        backgroundColor:
                                                            (el.styles
                                                                ?.accentColor ||
                                                                rs.primary_color) +
                                                            '20',
                                                        color:
                                                            el.styles
                                                                ?.accentColor ||
                                                            rs.primary_color,
                                                    }"
                                                    >{{
                                                        el.status ||
                                                        "In Progress"
                                                    }}</span
                                                >
                                                <span
                                                    class="text-[9px] text-slate-400 ml-auto"
                                                    >{{ el.due || "Due date" }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Page number footer -->
                            <div
                                v-if="rs.show_page_numbers && !rs.show_footer"
                                class="absolute bottom-2 left-0 right-0 flex justify-center pointer-events-none z-[1]"
                            >
                                <span
                                    class="text-[10px] font-semibold"
                                    :class="
                                        dark
                                            ? 'text-slate-700'
                                            : 'text-slate-400'
                                    "
                                    >{{ pi + 1 }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Add page -->
                    <button
                        @click="addPageAfter(pages.length - 1)"
                        class="flex items-center gap-2 px-6 py-3 border-2 border-dashed rounded-xl transition-all text-sm font-bold mb-8"
                        :class="
                            dark
                                ? 'border-slate-800 text-slate-700 hover:border-indigo-500/50 hover:text-indigo-500'
                                : 'border-slate-300 text-slate-400 hover:border-indigo-400 hover:text-indigo-500'
                        "
                        :style="{
                            width: canvasDimensions.width * (zoom / 100) + 'px',
                        }"
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
                                d="M12 4v16m8-8H4"
                            /></svg
                        >Add New Page
                    </button>
                </div>
            </main>

            <!-- ═══════════ RIGHT PROPERTIES PANEL ═══════════ -->
            <aside
                class="w-72 flex flex-col overflow-hidden shrink-0 border-l transition-colors"
                :class="
                    dark
                        ? 'bg-slate-900 border-slate-800'
                        : 'bg-white border-slate-200'
                "
            >
                <!-- Empty state -->
                <div
                    v-if="!selectedElement"
                    class="flex-1 flex flex-col items-center justify-center p-6 gap-4"
                >
                    <div
                        class="w-14 h-14 rounded-2xl flex items-center justify-center"
                        :class="dark ? 'bg-slate-800' : 'bg-slate-100'"
                    >
                        <svg
                            class="w-7 h-7 text-slate-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"
                            />
                        </svg>
                    </div>
                    <div class="text-center">
                        <p
                            class="text-sm font-bold"
                            :class="dark ? 'text-slate-400' : 'text-slate-500'"
                        >
                            No element selected
                        </p>
                        <p
                            class="text-[11px] mt-1"
                            :class="dark ? 'text-slate-600' : 'text-slate-400'"
                        >
                            Click an element or drag from the panel
                        </p>
                    </div>
                    <!-- Keyboard shortcuts reference -->
                    <div
                        class="w-full p-3 rounded-xl"
                        :class="
                            dark
                                ? 'bg-slate-800/50 border border-slate-700'
                                : 'bg-slate-50 border border-slate-100'
                        "
                    >
                        <div
                            class="text-[9px] font-black uppercase tracking-widest mb-2"
                            :class="dark ? 'text-slate-600' : 'text-slate-400'"
                        >
                            Keyboard Shortcuts
                        </div>
                        <div class="space-y-1 text-[10px]">
                            <div
                                v-for="s in SHORTCUTS"
                                :key="s.key"
                                class="flex items-center justify-between"
                                :class="dark ? 'text-slate-500' : 'text-slate-500'"
                            >
                                <span>{{ s.desc }}</span>
                                <kbd
                                    class="px-1.5 py-0.5 rounded text-[9px] font-mono shadow-sm"
                                    :class="
                                        dark
                                            ? 'bg-slate-700 border border-slate-600 text-slate-300'
                                            : 'bg-white border border-slate-200 text-slate-600'
                                    "
                                    >{{ s.key }}</kbd
                                >
                            </div>
                        </div>
                    </div>
                    <!-- Quick add -->
                    <div class="w-full space-y-1 mt-1">
                        <p
                            class="text-[9px] font-black uppercase tracking-widest mb-2"
                            :class="dark ? 'text-slate-700' : 'text-slate-400'"
                        >
                            Quick Add
                        </p>
                        <button
                            v-for="qt in QUICK_ADD"
                            :key="qt.type"
                            @click="addElement(qt.type)"
                            class="w-full flex items-center gap-2 px-3 py-2 text-[11px] font-bold rounded-xl border transition-all"
                            :class="
                                dark
                                    ? 'bg-slate-800/50 border-slate-800 text-slate-400 hover:text-indigo-400 hover:border-indigo-500/40'
                                    : 'bg-slate-50 border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50'
                            "
                        >
                            <span class="text-base">{{ qt.icon }}</span
                            >{{ qt.label }}
                        </button>
                    </div>
                </div>

                <!-- Properties -->
                <template v-else>
                    <div
                        class="px-4 py-3 border-b flex items-center justify-between shrink-0"
                        :class="
                            dark
                                ? 'border-slate-800 bg-slate-800/50'
                                : 'border-slate-100 bg-slate-50'
                        "
                    >
                        <div>
                            <span
                                class="text-xs font-black"
                                :class="
                                    dark ? 'text-slate-200' : 'text-slate-700'
                                "
                                >{{
                                    getElementLabel(selectedElement.type)
                                }}</span
                            >
                            <p
                                class="text-[10px] mt-0.5"
                                :class="
                                    dark ? 'text-slate-600' : 'text-slate-400'
                                "
                            >
                                Element Properties
                            </p>
                        </div>
                        <button
                            @click="deselectAll"
                            class="p-1.5 rounded-lg transition-colors"
                            :class="
                                dark
                                    ? 'text-slate-600 hover:text-slate-400 hover:bg-white/10'
                                    : 'text-slate-400 hover:text-slate-600 hover:bg-slate-200'
                            "
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <div
                        class="flex-1 overflow-y-auto divide-y"
                        :class="dark ? 'divide-slate-800' : 'divide-slate-100'"
                    >
                        <!-- POSITION & SIZE -->
                        <PropSec
                            title="Position & Size"
                            id="layout"
                            v-bind="secBind('layout')"
                        >
                            <div class="grid grid-cols-2 gap-2">
                                <PF label="X (px)" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.position.x
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Y (px)" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.position.y
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Width (px)" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.width
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Height (px)" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.height
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Rotation °" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.rotate
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Z-Index" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.zIndex
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                            </div>
                            <div class="mt-2.5">
                                <label class="prop-label"
                                    >Opacity —
                                    {{
                                        selectedElement.styles.opacity ?? 100
                                    }}%</label
                                >
                                <input
                                    type="range"
                                    min="0"
                                    max="100"
                                    v-model.number="
                                        selectedElement.styles.opacity
                                    "
                                    class="w-full h-1.5 accent-indigo-600 mt-1"
                                />
                            </div>
                            <div
                                class="flex items-center justify-between mt-2 pt-2 border-t"
                                :class="
                                    dark
                                        ? 'border-slate-800'
                                        : 'border-slate-100'
                                "
                            >
                                <label
                                    class="text-[11px] font-semibold"
                                    :class="
                                        dark
                                            ? 'text-slate-400'
                                            : 'text-slate-500'
                                    "
                                    >Lock Aspect Ratio</label
                                >
                                <Tog
                                    v-model="selectedElement.lockAspect"
                                    :dark="dark"
                                />
                            </div>
                            <div
                                class="flex items-center justify-between mt-1.5"
                            >
                                <label
                                    class="text-[11px] font-semibold"
                                    :class="
                                        dark
                                            ? 'text-slate-400'
                                            : 'text-slate-500'
                                    "
                                    >Visible</label
                                >
                                <Tog
                                    :model-value="!selectedElement.hidden"
                                    @update:model-value="
                                        (v) => (selectedElement.hidden = !v)
                                    "
                                    :dark="dark"
                                />
                            </div>
                            <div
                                class="flex items-center justify-between mt-1.5"
                            >
                                <label
                                    class="text-[11px] font-semibold"
                                    :class="
                                        dark
                                            ? 'text-slate-400'
                                            : 'text-slate-500'
                                    "
                                    >Locked</label
                                >
                                <Tog
                                    v-model="selectedElement.locked"
                                    @update:model-value="pushHistory"
                                    :dark="dark"
                                />
                            </div>
                            <!-- Move to Page -->
                            <div
                                v-if="pages.length > 1"
                                class="mt-2 pt-2 border-t"
                                :class="
                                    dark
                                        ? 'border-slate-800'
                                        : 'border-slate-100'
                                "
                            >
                                <label class="prop-label">Move to Page</label>
                                <div class="flex gap-1 mt-1 flex-wrap">
                                    <button
                                        v-for="(page, pi2) in pages"
                                        :key="pi2"
                                        @click="
                                            moveElementToPage(
                                                selectedElement,
                                                selectedPageIndex,
                                                pi2,
                                            )
                                        "
                                        class="text-[10px] px-2 py-1 rounded border font-bold transition-colors"
                                        :class="
                                            pi2 === selectedPageIndex
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : dark
                                                  ? 'bg-slate-800 text-slate-400 border-slate-700 hover:border-indigo-500 hover:text-indigo-400'
                                                  : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-400 hover:text-indigo-600'
                                        "
                                    >
                                        Page {{ pi2 + 1 }}
                                    </button>
                                </div>
                            </div>
                        </PropSec>

                        <!-- TYPOGRAPHY -->
                        <PropSec
                            v-if="isTextLike(selectedElement.type)"
                            title="Typography"
                            id="typography"
                            v-bind="secBind('typography')"
                        >
                            <div class="space-y-2">
                                <PF label="Font Family" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles.fontFamily
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option
                                            v-for="f in FONTS"
                                            :key="f.value"
                                            :value="f.value"
                                        >
                                            {{ f.name }}
                                        </option>
                                    </select>
                                </PF>
                                <div class="grid grid-cols-2 gap-2">
                                    <PF label="Size (px)" :dark="dark"
                                        ><input
                                            type="number"
                                            v-model.number="
                                                selectedElement.styles.fontSize
                                            "
                                            :class="inputCls"
                                            @change="pushHistory"
                                    /></PF>
                                    <PF label="Weight" :dark="dark">
                                        <select
                                            v-model="
                                                selectedElement.styles
                                                    .fontWeight
                                            "
                                            :class="inputCls"
                                            @change="pushHistory"
                                        >
                                            <option
                                                v-for="w in FONT_WEIGHTS"
                                                :key="w.value"
                                                :value="w.value"
                                            >
                                                {{ w.label }}
                                            </option>
                                        </select>
                                    </PF>
                                    <PF label="Line Height" :dark="dark"
                                        ><input
                                            type="number"
                                            step="0.1"
                                            v-model.number="
                                                selectedElement.styles
                                                    .lineHeight
                                            "
                                            :class="inputCls"
                                            @change="pushHistory"
                                    /></PF>
                                    <PF label="Letter Spacing" :dark="dark"
                                        ><input
                                            type="number"
                                            step="0.5"
                                            v-model.number="
                                                selectedElement.styles
                                                    .letterSpacing
                                            "
                                            :class="inputCls"
                                            @change="pushHistory"
                                    /></PF>
                                </div>
                                <!-- Alignment -->
                                <div>
                                    <label class="prop-label">Alignment</label>
                                    <div class="flex gap-1 mt-1">
                                        <button
                                            v-for="a in [
                                                'left',
                                                'center',
                                                'right',
                                                'justify',
                                            ]"
                                            :key="a"
                                            @click="
                                                selectedElement.styles.textAlign =
                                                    a;
                                                pushHistory();
                                            "
                                            class="flex-1 py-1.5 rounded-lg border text-xs transition-colors"
                                            :class="
                                                selectedElement.styles
                                                    .textAlign === a
                                                    ? 'bg-indigo-100 text-indigo-700 border-indigo-300'
                                                    : dark
                                                      ? 'bg-slate-800 text-slate-500 border-slate-700 hover:bg-slate-700'
                                                      : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'
                                            "
                                        >
                                            {{
                                                {
                                                    left: "⬱",
                                                    center: "☰",
                                                    right: "⬰",
                                                    justify: "≡",
                                                }[a]
                                            }}
                                        </button>
                                    </div>
                                </div>
                                <!-- Style -->
                                <div>
                                    <label class="prop-label">Style</label>
                                    <div class="flex gap-1 mt-1">
                                        <button
                                            @click="toggleTextStyle('italic')"
                                            class="flex-1 py-1.5 border rounded-lg text-sm font-bold italic transition-colors"
                                            :class="
                                                selectedElement.styles
                                                    .fontStyle === 'italic'
                                                    ? 'bg-indigo-100 text-indigo-700 border-indigo-300'
                                                    : dark
                                                      ? 'bg-slate-800 text-slate-500 border-slate-700'
                                                      : 'bg-white text-slate-500 border-slate-200'
                                            "
                                        >
                                            I
                                        </button>
                                        <button
                                            @click="
                                                toggleTextStyle('underline')
                                            "
                                            class="flex-1 py-1.5 border rounded-lg text-sm font-bold underline transition-colors"
                                            :class="
                                                selectedElement.styles
                                                    .textDecoration ===
                                                'underline'
                                                    ? 'bg-indigo-100 text-indigo-700 border-indigo-300'
                                                    : dark
                                                      ? 'bg-slate-800 text-slate-500 border-slate-700'
                                                      : 'bg-white text-slate-500 border-slate-200'
                                            "
                                        >
                                            U
                                        </button>
                                        <button
                                            @click="
                                                toggleTextStyle('line-through')
                                            "
                                            class="flex-1 py-1.5 border rounded-lg text-sm font-bold line-through transition-colors"
                                            :class="
                                                selectedElement.styles
                                                    .textDecoration ===
                                                'line-through'
                                                    ? 'bg-indigo-100 text-indigo-700 border-indigo-300'
                                                    : dark
                                                      ? 'bg-slate-800 text-slate-500 border-slate-700'
                                                      : 'bg-white text-slate-500 border-slate-200'
                                            "
                                        >
                                            S
                                        </button>
                                        <button
                                            @click="
                                                toggleTextStyle('uppercase')
                                            "
                                            class="flex-1 py-1.5 border rounded-lg text-[10px] font-black uppercase transition-colors"
                                            :class="
                                                selectedElement.styles
                                                    .textTransform ===
                                                'uppercase'
                                                    ? 'bg-indigo-100 text-indigo-700 border-indigo-300'
                                                    : dark
                                                      ? 'bg-slate-800 text-slate-500 border-slate-700'
                                                      : 'bg-white text-slate-500 border-slate-200'
                                            "
                                        >
                                            AA
                                        </button>
                                    </div>
                                </div>
                                <!-- Text color -->
                                <PF label="Text Color" :dark="dark">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            class="w-8 h-8 rounded-lg border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700 bg-slate-800'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <!-- Text shadow -->
                                <PF label="Text Shadow" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles.textShadow
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="">None</option>
                                        <option
                                            value="1px 1px 2px rgba(0,0,0,0.2)"
                                        >
                                            Subtle
                                        </option>
                                        <option
                                            value="2px 2px 4px rgba(0,0,0,0.3)"
                                        >
                                            Medium
                                        </option>
                                        <option
                                            value="3px 3px 6px rgba(0,0,0,0.4)"
                                        >
                                            Strong
                                        </option>
                                        <option
                                            value="0 0 10px rgba(99,102,241,0.8)"
                                        >
                                            Glow Indigo
                                        </option>
                                        <option
                                            value="0 0 10px rgba(245,158,11,0.8)"
                                        >
                                            Glow Gold
                                        </option>
                                    </select>
                                </PF>
                            </div>
                        </PropSec>

                        <!-- IMAGE -->
                        <PropSec
                            v-if="selectedElement.type === 'image'"
                            title="Image"
                            id="image"
                            v-bind="secBind('image')"
                        >
                            <div class="space-y-2.5">
                                <label
                                    class="w-full flex items-center justify-center gap-2 py-3 border-2 border-dashed rounded-xl text-xs font-bold cursor-pointer transition-colors"
                                    :class="
                                        dark
                                            ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400'
                                            : 'border-slate-200 text-slate-500 hover:border-indigo-400 hover:text-indigo-600'
                                    "
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
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                        />
                                    </svg>
                                    {{
                                        selectedElement.src
                                            ? "Replace Image"
                                            : "Upload Image"
                                    }}
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="
                                            uploadImage($event, selectedElement)
                                        "
                                    />
                                </label>
                                <PF label="Image URL" :dark="dark"
                                    ><input
                                        type="url"
                                        v-model="selectedElement.src"
                                        :class="inputCls"
                                        placeholder="https://…"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Alt Text" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.alt"
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Object Fit" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles.objectFit
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="cover">
                                            Cover (crop to fill)
                                        </option>
                                        <option value="contain">
                                            Contain (letterbox)
                                        </option>
                                        <option value="fill">
                                            Fill (stretch)
                                        </option>
                                        <option value="none">
                                            None (original size)
                                        </option>
                                        <option value="scale-down">
                                            Scale Down
                                        </option>
                                    </select>
                                </PF>
                                <PF label="Image Filter" :dark="dark">
                                    <select
                                        v-model="selectedElement.styles.filter"
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="">None</option>
                                        <option value="grayscale(100%)">
                                            Grayscale
                                        </option>
                                        <option value="sepia(100%)">
                                            Sepia
                                        </option>
                                        <option value="blur(3px)">Blur</option>
                                        <option value="brightness(1.25)">
                                            Brighten
                                        </option>
                                        <option value="brightness(0.75)">
                                            Darken
                                        </option>
                                        <option value="contrast(1.5)">
                                            High Contrast
                                        </option>
                                        <option value="saturate(2)">
                                            Saturate
                                        </option>
                                        <option value="hue-rotate(90deg)">
                                            Hue Shift
                                        </option>
                                        <option value="invert(100%)">
                                            Invert
                                        </option>
                                    </select>
                                </PF>
                                <PF label="Corner Radius" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.borderRadius
                                        "
                                        :class="inputCls"
                                        min="0"
                                        max="200"
                                        @change="pushHistory"
                                /></PF>
                            </div>
                        </PropSec>

                        <!-- VIDEO -->
                        <PropSec
                            v-if="selectedElement.type === 'video'"
                            title="Video"
                            id="video"
                            v-bind="secBind('video')"
                        >
                            <PF label="Embed URL (YouTube/Vimeo)" :dark="dark">
                                <input
                                    type="url"
                                    v-model="selectedElement.src"
                                    :class="inputCls"
                                    placeholder="https://www.youtube.com/embed/…"
                                    @change="pushHistory"
                                />
                            </PF>
                        </PropSec>

                        <!-- METRIC -->
                        <PropSec
                            v-if="selectedElement.type === 'metric'"
                            title="KPI / Metric"
                            id="metric"
                            v-bind="secBind('metric')"
                        >
                            <div class="space-y-2">
                                <PF label="Label" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.label"
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Value" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.value"
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <div class="grid grid-cols-2 gap-2">
                                    <PF label="Change" :dark="dark"
                                        ><input
                                            type="text"
                                            v-model="selectedElement.change"
                                            :class="inputCls"
                                            placeholder="12.5%"
                                            @change="pushHistory"
                                    /></PF>
                                    <PF label="Direction" :dark="dark">
                                        <select
                                            v-model="selectedElement.changeType"
                                            :class="inputCls"
                                            @change="pushHistory"
                                        >
                                            <option value="positive">
                                                ▲ Positive
                                            </option>
                                            <option value="negative">
                                                ▼ Negative
                                            </option>
                                        </select>
                                    </PF>
                                </div>
                                <PF label="Period Label" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.changePeriod"
                                        :class="inputCls"
                                        placeholder="vs last month"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Icon Emoji" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.icon"
                                        :class="inputCls"
                                        placeholder="📈"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Label Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles
                                                    .labelColor
                                            "
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles
                                                    .labelColor
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <PF label="Value Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                            </div>
                        </PropSec>

                        <!-- PROGRESS / CIRCULAR PROGRESS -->
                        <PropSec
                            v-if="
                                selectedElement.type === 'progress' ||
                                selectedElement.type === 'circular-progress'
                            "
                            title="Progress"
                            id="progress"
                            v-bind="secBind('progress')"
                        >
                            <div class="space-y-2">
                                <PF label="Label" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.label"
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <div>
                                    <label class="prop-label"
                                        >Value —
                                        {{ selectedElement.value }}%</label
                                    >
                                    <input
                                        type="range"
                                        min="0"
                                        max="100"
                                        v-model.number="selectedElement.value"
                                        class="w-full h-1.5 accent-indigo-600 mt-1"
                                        @change="pushHistory"
                                    />
                                </div>
                                <PF
                                    v-if="
                                        selectedElement.type === 'progress'
                                    "
                                    label="Track Height (px)"
                                    :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.trackHeight
                                        "
                                        :class="inputCls"
                                        min="2"
                                        max="48"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Bar Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <PF label="Track Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles
                                                    .trackColor
                                            "
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles
                                                    .trackColor
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                            </div>
                        </PropSec>

                        <!-- CHART -->
                        <PropSec
                            v-if="isChartType(selectedElement.type)"
                            title="Chart Data"
                            id="chart"
                            v-bind="secBind('chart')"
                        >
                            <div class="space-y-2.5">
                                <PF label="Chart Type" :dark="dark">
                                    <select
                                        v-model="selectedElement.type"
                                        :class="inputCls"
                                        @change="onChartTypeChange"
                                    >
                                        <option value="bar-chart">
                                            Bar Chart
                                        </option>
                                        <option value="line-chart">
                                            Line Chart
                                        </option>
                                        <option value="pie-chart">
                                            Pie Chart
                                        </option>
                                        <option value="area-chart">
                                            Area Chart
                                        </option>
                                        <option value="doughnut-chart">
                                            Doughnut
                                        </option>
                                        <option value="radar-chart">
                                            Radar
                                        </option>
                                    </select>
                                </PF>
                                <PF label="Chart Title" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.chartTitle"
                                        :class="inputCls"
                                        @change="refreshChart(selectedElement)"
                                /></PF>
                                <PF label="Labels (comma sep.)" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="chartLabelsInput"
                                        :class="inputCls"
                                        placeholder="Jan, Feb, Mar…"
                                        @blur="updateChartData"
                                /></PF>
                                <PF label="Values (comma sep.)" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="chartValuesInput"
                                        :class="inputCls"
                                        placeholder="65, 59, 80…"
                                        @blur="updateChartData"
                                /></PF>
                                <PF label="Dataset Label" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="chartDatasetLabel"
                                        :class="inputCls"
                                        @blur="updateChartData"
                                /></PF>
                                <PF label="Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="chartColor"
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="updateChartData"
                                        />
                                        <input
                                            type="text"
                                            v-model="chartColor"
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <PF
                                    v-if="
                                        [
                                            'pie-chart',
                                            'doughnut-chart',
                                        ].includes(selectedElement.type)
                                    "
                                    label="Segment Colors (hex, comma)"
                                    :dark="dark"
                                >
                                    <input
                                        type="text"
                                        v-model="pieColorsInput"
                                        :class="inputCls"
                                        placeholder="#f59e0b,#6366f1,#10b981"
                                        @blur="updateChartData"
                                    />
                                </PF>
                                <PF label="Legend Position" :dark="dark">
                                    <select
                                        v-model="selectedElement.legendPosition"
                                        :class="inputCls"
                                        @change="refreshChart(selectedElement)"
                                    >
                                        <option value="bottom">Bottom</option>
                                        <option value="top">Top</option>
                                        <option value="left">Left</option>
                                        <option value="right">Right</option>
                                        <option value="none">Hidden</option>
                                    </select>
                                </PF>
                                <button
                                    @click="refreshChart(selectedElement)"
                                    class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black rounded-xl transition-colors"
                                >
                                    ↻ Refresh Chart
                                </button>
                            </div>
                        </PropSec>

                        <!-- LIST -->
                        <PropSec
                            v-if="selectedElement.type === 'list'"
                            title="List Options"
                            id="list"
                            v-bind="secBind('list')"
                        >
                            <PF label="List Style" :dark="dark">
                                <select
                                    v-model="selectedElement.styles.listStyle"
                                    :class="inputCls"
                                    @change="pushHistory"
                                >
                                    <option value="bullet">Bullet (•)</option>
                                    <option value="numbered">
                                        Numbered (1.)
                                    </option>
                                    <option value="check">
                                        Checkmarks (✓)
                                    </option>
                                    <option value="arrow">Arrows (→)</option>
                                    <option value="none">None</option>
                                </select>
                            </PF>
                        </PropSec>

                        <!-- TABLE -->
                        <PropSec
                            v-if="selectedElement.type === 'table'"
                            title="Table Style"
                            id="table"
                            v-bind="secBind('table')"
                        >
                            <div class="space-y-2">
                                <PF label="Header BG" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles.headerBg
                                            "
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles.headerBg
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <PF label="Header Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles
                                                    .headerColor
                                            "
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles
                                                    .headerColor
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="prop-label"
                                            >Even Row</label
                                        >
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles.evenRowBg
                                            "
                                            class="w-full h-8 rounded-lg border cursor-pointer p-0.5 mt-1"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                    </div>
                                    <div>
                                        <label class="prop-label"
                                            >Odd Row</label
                                        >
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles.oddRowBg
                                            "
                                            class="w-full h-8 rounded-lg border cursor-pointer p-0.5 mt-1"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                    </div>
                                </div>
                            </div>
                        </PropSec>

                        <!-- CALLOUT / ALERT -->
                        <PropSec
                            v-if="
                                ['callout', 'alert'].includes(
                                    selectedElement.type,
                                )
                            "
                            title="Callout / Alert"
                            id="callout"
                            v-bind="secBind('callout')"
                        >
                            <div class="space-y-2">
                                <PF label="Emoji Icon" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.emoji"
                                        :class="inputCls"
                                        placeholder="ℹ️"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Style Preset" :dark="dark">
                                    <select
                                        v-model="selectedElement.calloutStyle"
                                        :class="inputCls"
                                        @change="applyCalloutStyle"
                                    >
                                        <option value="info">
                                            ℹ️ Info (blue)
                                        </option>
                                        <option value="warning">
                                            ⚠️ Warning (amber)
                                        </option>
                                        <option value="success">
                                            ✅ Success (green)
                                        </option>
                                        <option value="danger">
                                            🚨 Danger (red)
                                        </option>
                                        <option value="neutral">
                                            📝 Neutral (gray)
                                        </option>
                                        <option value="purple">
                                            💡 Idea (purple)
                                        </option>
                                    </select>
                                </PF>
                            </div>
                        </PropSec>

                        <!-- LINK -->
                        <PropSec
                            v-if="selectedElement.type === 'link'"
                            title="Link"
                            id="link"
                            v-bind="secBind('link')"
                        >
                            <div class="space-y-2">
                                <PF label="URL" :dark="dark"
                                    ><input
                                        type="url"
                                        v-model="selectedElement.href"
                                        :class="inputCls"
                                        placeholder="https://…"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Display Text" :dark="dark"
                                    ><input
                                        type="text"
                                        v-model="selectedElement.content"
                                        :class="inputCls"
                                        @change="pushHistory"
                                /></PF>
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-[11px] font-semibold"
                                        :class="
                                            dark
                                                ? 'text-slate-400'
                                                : 'text-slate-500'
                                        "
                                        >Open in new tab</label
                                    >
                                    <Tog
                                        :model-value="
                                            selectedElement.target === '_blank'
                                        "
                                        @update:model-value="
                                            (v) => {
                                                selectedElement.target = v
                                                    ? '_blank'
                                                    : '_self';
                                                pushHistory();
                                            }
                                        "
                                        :dark="dark"
                                    />
                                </div>
                            </div>
                        </PropSec>

                        <!-- CODE -->
                        <PropSec
                            v-if="selectedElement.type === 'code'"
                            title="Code Block"
                            id="code"
                            v-bind="secBind('code')"
                        >
                            <PF label="Language" :dark="dark">
                                <select
                                    v-model="selectedElement.language"
                                    :class="inputCls"
                                    @change="pushHistory"
                                >
                                    <option
                                        v-for="l in CODE_LANGS"
                                        :key="l"
                                        :value="l"
                                    >
                                        {{ l }}
                                    </option>
                                </select>
                            </PF>
                        </PropSec>

                        <!-- SIGNATURE -->
                        <PropSec
                            v-if="selectedElement.type === 'signature'"
                            title="Signature"
                            id="signature"
                            v-bind="secBind('signature')"
                        >
                            <PF label="Label" :dark="dark"
                                ><input
                                    type="text"
                                    v-model="selectedElement.label"
                                    :class="inputCls"
                                    placeholder="Authorised Signature"
                                    @change="pushHistory"
                            /></PF>
                            <PF label="Line Color" :dark="dark">
                                <div class="flex gap-2 mt-1">
                                    <input
                                        type="color"
                                        v-model="selectedElement.styles.color"
                                        class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                        :class="
                                            dark
                                                ? 'border-slate-700'
                                                : 'border-slate-200'
                                        "
                                        @change="pushHistory"
                                    />
                                    <input
                                        type="text"
                                        v-model="selectedElement.styles.color"
                                        :class="[inputCls, 'flex-1 font-mono']"
                                    />
                                </div>
                            </PF>
                        </PropSec>

                        <!-- RATING -->
                        <PropSec
                            v-if="selectedElement.type === 'rating'"
                            title="Star Rating"
                            id="rating"
                            v-bind="secBind('rating')"
                        >
                            <div>
                                <label class="prop-label"
                                    >Stars (0–5) —
                                    {{ selectedElement.value }}</label
                                >
                                <input
                                    type="range"
                                    min="0"
                                    max="5"
                                    step="0.5"
                                    v-model.number="selectedElement.value"
                                    class="w-full h-1.5 accent-amber-400 mt-1"
                                    @change="pushHistory"
                                />
                            </div>
                            <PF label="Star Color" :dark="dark" class="mt-2">
                                <div class="flex gap-2">
                                    <input
                                        type="color"
                                        v-model="selectedElement.styles.color"
                                        class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                        :class="
                                            dark
                                                ? 'border-slate-700'
                                                : 'border-slate-200'
                                        "
                                        @change="pushHistory"
                                    />
                                    <input
                                        type="text"
                                        v-model="selectedElement.styles.color"
                                        :class="[inputCls, 'flex-1 font-mono']"
                                    />
                                </div>
                            </PF>
                        </PropSec>

                        <!-- ICON -->
                        <PropSec
                            v-if="selectedElement.type === 'icon'"
                            title="Icon"
                            id="icon"
                            v-bind="secBind('icon')"
                        >
                            <PF label="Emoji / Symbol" :dark="dark"
                                ><input
                                    type="text"
                                    v-model="selectedElement.content"
                                    :class="inputCls"
                                    placeholder="★ 🎯 ✓"
                                    @change="pushHistory"
                            /></PF>
                            <PF label="Size (px)" :dark="dark" class="mt-2"
                                ><input
                                    type="number"
                                    v-model.number="
                                        selectedElement.styles.fontSize
                                    "
                                    :class="inputCls"
                                    min="8"
                                    max="200"
                                    @change="pushHistory"
                            /></PF>
                            <PF label="Color" :dark="dark" class="mt-2">
                                <div class="flex gap-2">
                                    <input
                                        type="color"
                                        v-model="selectedElement.styles.color"
                                        class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                        :class="
                                            dark
                                                ? 'border-slate-700'
                                                : 'border-slate-200'
                                        "
                                        @change="pushHistory"
                                    />
                                    <input
                                        type="text"
                                        v-model="selectedElement.styles.color"
                                        :class="[inputCls, 'flex-1 font-mono']"
                                    />
                                </div>
                            </PF>
                        </PropSec>

                        <!-- DATE -->
                        <PropSec
                            v-if="selectedElement.type === 'date'"
                            title="Date"
                            id="date"
                            v-bind="secBind('date')"
                        >
                            <PF label="Format" :dark="dark">
                                <select
                                    v-model="selectedElement.format"
                                    :class="inputCls"
                                    @change="pushHistory"
                                >
                                    <option value="long">
                                        Long (January 1, 2026)
                                    </option>
                                    <option value="short">
                                        Short (Jan 1, 2026)
                                    </option>
                                    <option value="numeric">
                                        Numeric (01/01/2026)
                                    </option>
                                    <option value="iso">
                                        ISO (2026-01-01)
                                    </option>
                                    <option value="custom">Custom</option>
                                </select>
                            </PF>
                            <PF
                                v-if="selectedElement.format === 'custom'"
                                label="Custom Date Text"
                                :dark="dark"
                                class="mt-2"
                            >
                                <input
                                    type="text"
                                    v-model="selectedElement.customDate"
                                    :class="inputCls"
                                    placeholder="e.g. Q1 2026"
                                    @change="pushHistory"
                                />
                            </PF>
                        </PropSec>

                        <!-- TOC -->
                        <PropSec
                            v-if="selectedElement.type === 'toc'"
                            title="Table of Contents"
                            id="toc"
                            v-bind="secBind('toc')"
                        >
                            <PF label="Title" :dark="dark"
                                ><input
                                    type="text"
                                    v-model="selectedElement.title"
                                    :class="inputCls"
                                    placeholder="Table of Contents"
                                    @change="pushHistory"
                            /></PF>
                        </PropSec>

                        <!-- DIVIDER / LINE / ARROW -->
                        <PropSec
                            v-if="
                                ['divider', 'line', 'arrow', 'double-arrow'].includes(
                                    selectedElement.type,
                                )
                            "
                            title="Line Settings"
                            id="line"
                            v-bind="secBind('line')"
                        >
                            <div class="space-y-2">
                                <PF label="Thickness (px)" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.borderWidth
                                        "
                                        :class="inputCls"
                                        min="1"
                                        max="30"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Style" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles.borderStyle
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="solid">Solid</option>
                                        <option value="dashed">Dashed</option>
                                        <option value="dotted">Dotted</option>
                                        <option value="double">Double</option>
                                    </select>
                                </PF>
                                <PF label="Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            class="w-8 h-8 rounded border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles.color
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                            </div>
                        </PropSec>

                        <!-- APPEARANCE -->
                        <PropSec
                            title="Appearance"
                            id="appearance"
                            v-bind="secBind('appearance')"
                        >
                            <div class="space-y-2.5">
                                <PF label="Background Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles
                                                    .backgroundColor
                                            "
                                            class="w-8 h-8 rounded-lg border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700 bg-slate-800'
                                                    : 'border-slate-200 bg-white'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles
                                                    .backgroundColor
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <PF label="Background Gradient" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles
                                                .backgroundGradient
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="">
                                            None (use solid color)
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#6366f1,#8b5cf6)"
                                        >
                                            Indigo → Violet
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#10b981,#06b6d4)"
                                        >
                                            Emerald → Cyan
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#f59e0b,#ef4444)"
                                        >
                                            Amber → Red
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#1e293b,#334155)"
                                        >
                                            Dark Slate
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#fef3c7,#fde68a)"
                                        >
                                            Soft Gold
                                        </option>
                                        <option
                                            value="linear-gradient(180deg,#0f172a,#1e3a5f)"
                                        >
                                            Navy Deep
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#f0fdf4,#dcfce7)"
                                        >
                                            Soft Green
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#fdf2f8,#fce7f3)"
                                        >
                                            Soft Pink
                                        </option>
                                        <option
                                            value="linear-gradient(135deg,#eff6ff,#dbeafe)"
                                        >
                                            Soft Blue
                                        </option>
                                    </select>
                                </PF>
                                <div class="grid grid-cols-2 gap-2">
                                    <PF label="Border Width" :dark="dark"
                                        ><input
                                            type="number"
                                            v-model.number="
                                                selectedElement.styles
                                                    .borderWidth
                                            "
                                            :class="inputCls"
                                            min="0"
                                            max="20"
                                            @change="pushHistory"
                                    /></PF>
                                    <PF label="Radius (px)" :dark="dark"
                                        ><input
                                            type="number"
                                            v-model.number="
                                                selectedElement.styles
                                                    .borderRadius
                                            "
                                            :class="inputCls"
                                            min="0"
                                            max="200"
                                            @change="pushHistory"
                                    /></PF>
                                </div>
                                <PF label="Border Color" :dark="dark">
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="
                                                selectedElement.styles
                                                    .borderColor
                                            "
                                            class="w-8 h-8 rounded-lg border cursor-pointer p-0.5"
                                            :class="
                                                dark
                                                    ? 'border-slate-700'
                                                    : 'border-slate-200'
                                            "
                                            @change="pushHistory"
                                        />
                                        <input
                                            type="text"
                                            v-model="
                                                selectedElement.styles
                                                    .borderColor
                                            "
                                            :class="[
                                                inputCls,
                                                'flex-1 font-mono',
                                            ]"
                                        />
                                    </div>
                                </PF>
                                <PF label="Border Style" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles.borderStyle
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="solid">Solid</option>
                                        <option value="dashed">Dashed</option>
                                        <option value="dotted">Dotted</option>
                                        <option value="double">Double</option>
                                        <option value="groove">Groove</option>
                                        <option value="ridge">Ridge</option>
                                    </select>
                                </PF>
                                <PF label="Padding (px)" :dark="dark"
                                    ><input
                                        type="number"
                                        v-model.number="
                                            selectedElement.styles.padding
                                        "
                                        :class="inputCls"
                                        min="0"
                                        max="80"
                                        @change="pushHistory"
                                /></PF>
                                <PF label="Box Shadow" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles.boxShadow
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="none">None</option>
                                        <option value="sm">Small</option>
                                        <option value="md">Medium</option>
                                        <option value="lg">Large</option>
                                        <option value="xl">Extra Large</option>
                                        <option value="2xl">2XL</option>
                                        <option value="inner">Inner</option>
                                        <option value="glow-indigo">
                                            Glow Indigo
                                        </option>
                                        <option value="glow-emerald">
                                            Glow Emerald
                                        </option>
                                        <option value="glow-gold">
                                            Glow Gold
                                        </option>
                                    </select>
                                </PF>
                                <PF label="Overflow" :dark="dark">
                                    <select
                                        v-model="
                                            selectedElement.styles.overflow
                                        "
                                        :class="inputCls"
                                        @change="pushHistory"
                                    >
                                        <option value="hidden">
                                            Hidden (clip)
                                        </option>
                                        <option value="auto">
                                            Auto (scroll)
                                        </option>
                                        <option value="visible">Visible</option>
                                    </select>
                                </PF>
                            </div>
                        </PropSec>

                        <!-- LAYER & ALIGNMENT -->
                        <PropSec
                            title="Layer & Alignment"
                            id="layers"
                            v-bind="secBind('layers')"
                        >
                            <div class="grid grid-cols-2 gap-1.5 mb-3">
                                <button
                                    @click="bringToFront"
                                    class="layer-btn"
                                    :class="layerBtnCls"
                                >
                                    ↑↑ Front
                                </button>
                                <button
                                    @click="sendToBack"
                                    class="layer-btn"
                                    :class="layerBtnCls"
                                >
                                    ↓↓ Back
                                </button>
                                <button
                                    @click="bringForward"
                                    class="layer-btn"
                                    :class="layerBtnCls"
                                >
                                    ↑ Forward
                                </button>
                                <button
                                    @click="sendBackward"
                                    class="layer-btn"
                                    :class="layerBtnCls"
                                >
                                    ↓ Backward
                                </button>
                            </div>
                            <label class="prop-label mb-2 block"
                                >Align on Page</label
                            >
                            <div class="grid grid-cols-3 gap-1">
                                <button
                                    v-for="a in ALIGNMENTS"
                                    :key="a.dir"
                                    @click="alignElement(a.dir)"
                                    class="py-1.5 text-[10px] font-bold border rounded-xl transition-all"
                                    :class="layerBtnCls"
                                >
                                    {{ a.label }}
                                </button>
                            </div>
                        </PropSec>
                    </div>

                    <!-- Delete -->
                    <div
                        class="p-3 border-t shrink-0"
                        :class="dark ? 'border-slate-800' : 'border-slate-100'"
                    >
                        <button
                            @click="deleteSelectedElement"
                            class="w-full flex items-center justify-center gap-2 py-2 text-xs font-bold border rounded-xl transition-all"
                            :class="
                                dark
                                    ? 'text-red-400 border-red-900/50 hover:bg-red-500/10'
                                    : 'text-red-500 border-red-200 hover:bg-red-50'
                            "
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
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            Delete Element (Del)
                        </button>
                    </div>
                </template>
            </aside>
        </div>

        <!-- Context Menu -->
        <transition name="dropdown">
            <div
                v-if="contextMenu.show"
                class="fixed z-[9999] w-48 rounded-xl shadow-xl border overflow-hidden py-1"
                :class="
                    dark
                        ? 'bg-slate-800 border-slate-700'
                        : 'bg-white border-slate-200'
                "
                :style="{
                    left: contextMenu.x + 'px',
                    top: contextMenu.y + 'px',
                }"
            >
                <button
                    v-for="item in contextMenuItems"
                    :key="item.label"
                    @click="
                        item.action();
                        contextMenu.show = false;
                    "
                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-left transition-colors"
                    :class="
                        item.danger
                            ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10'
                            : dark
                              ? 'text-slate-300 hover:bg-white/10'
                              : 'text-slate-600 hover:bg-slate-50'
                    "
                >
                    <span class="text-base leading-none w-5 text-center">{{
                        item.icon
                    }}</span>
                    <span class="flex-1">{{ item.label }}</span>
                    <kbd
                        v-if="item.key"
                        class="text-[9px] font-mono"
                        :class="
                            dark
                                ? 'text-slate-500 bg-slate-700 px-1 rounded'
                                : 'text-slate-400 bg-slate-100 px-1 rounded'
                        "
                        >{{ item.key }}</kbd
                    >
                </button>
            </div>
        </transition>

        <!-- Toast -->
        <transition name="toast">
            <div
                v-if="toast.show"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[999] flex items-center gap-2.5 px-4 py-2.5 rounded-xl shadow-xl text-sm font-bold"
                :class="
                    toast.type === 'error'
                        ? 'bg-red-600 text-white'
                        : 'bg-emerald-600 text-white'
                "
            >
                <svg
                    v-if="toast.type === 'error'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <svg
                    v-else
                    class="w-4 h-4"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                    />
                </svg>
                {{ toast.message }}
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onBeforeUnmount,
    nextTick,
    defineComponent,
    h,
} from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";
import Chart from "chart.js/auto";

// ─── Inline Micro-Components ─────────────────────────────────────────────────
const Tog = defineComponent({
    props: ["modelValue", "dark"],
    emits: ["update:modelValue"],
    setup(props, { emit }) {
        return () =>
            h(
                "button",
                {
                    onClick: () => emit("update:modelValue", !props.modelValue),
                    class: `relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 flex-shrink-0 ${props.modelValue ? "bg-indigo-600" : props.dark ? "bg-slate-700" : "bg-slate-200"}`,
                },
                [
                    h("span", {
                        class: `inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-200 ${props.modelValue ? "translate-x-5" : "translate-x-1"}`,
                    }),
                ],
            );
    },
});

const PF = defineComponent({
    props: ["label", "dark"],
    setup(props, { slots }) {
        return () =>
            h("div", [
                h(
                    "label",
                    {
                        class:
                            "block text-[10px] font-bold uppercase tracking-wider mb-1 " +
                            (props.dark ? "text-slate-500" : "text-slate-400"),
                    },
                    props.label,
                ),
                slots.default?.(),
            ]);
    },
});

const PropSec = defineComponent({
    props: ["title", "id", "collapsedSections", "dark"],
    emits: ["toggle"],
    setup(props, { slots, emit }) {
        return () =>
            h("div", [
                h(
                    "button",
                    {
                        onClick: () => emit("toggle", props.id),
                        class: `w-full flex items-center justify-between px-4 py-2.5 text-[9px] font-black uppercase tracking-widest transition-colors select-none ${props.dark ? "text-slate-500 bg-slate-800/30 hover:bg-slate-800/60" : "text-slate-500 bg-slate-50 hover:bg-slate-100"}`,
                    },
                    [
                        props.title,
                        h(
                            "svg",
                            {
                                class: `w-3 h-3 transition-transform ${props.collapsedSections.includes(props.id) ? "" : "rotate-180"}`,
                                fill: "none",
                                stroke: "currentColor",
                                viewBox: "0 0 24 24",
                            },
                            [
                                h("path", {
                                    "stroke-linecap": "round",
                                    "stroke-linejoin": "round",
                                    "stroke-width": "2",
                                    d: "M19 9l-7 7-7-7",
                                }),
                            ],
                        ),
                    ],
                ),
                h(
                    "div",
                    {
                        style: {
                            display: props.collapsedSections.includes(props.id)
                                ? "none"
                                : "block",
                        },
                        class: "px-4 py-3",
                    },
                    slots.default?.(),
                ),
            ]);
    },
});

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({ report: Object });

// ─── Constants ────────────────────────────────────────────────────────────────
const LEFT_TABS = [
    { id: "elements", label: "Elements", icon: "🧩" },
    { id: "pages", label: "Pages", icon: "📄" },
    { id: "layers", label: "Layers", icon: "🗂️" },
    { id: "settings", label: "Settings", icon: "⚙️" },
];

const EXPORT_FORMATS = [
    { key: "pdf", label: "PDF Document", desc: "Print-ready PDF", icon: "📄" },
    { key: "xlsx", label: "Excel Sheet", desc: "Data in .xlsx", icon: "📊" },
    { key: "csv", label: "CSV File", desc: "Raw data export", icon: "📋" },
    { key: "png", label: "PNG Image", desc: "High-res image", icon: "🖼️" },
];

const PAGE_SIZES = [
    { value: "A4", label: "A4 (210 × 297 mm)" },
    { value: "Letter", label: "US Letter (8.5 × 11 in)" },
    { value: "Legal", label: "Legal (8.5 × 14 in)" },
    { value: "A3", label: "A3 (297 × 420 mm)" },
    { value: "A5", label: "A5 (148 × 210 mm)" },
];

const FONTS = [
    { name: "DM Sans", value: "'DM Sans', sans-serif" },
    { name: "Inter", value: "'Inter', sans-serif" },
    { name: "Outfit", value: "'Outfit', sans-serif" },
    { name: "Plus Jakarta Sans", value: "'Plus Jakarta Sans', sans-serif" },
    { name: "Georgia", value: "Georgia, serif" },
    { name: "Playfair Display", value: "'Playfair Display', serif" },
    { name: "Merriweather", value: "'Merriweather', serif" },
    { name: "Lato", value: "'Lato', sans-serif" },
    { name: "Nunito", value: "'Nunito', sans-serif" },
    { name: "Courier New", value: "'Courier New', monospace" },
    { name: "Trebuchet MS", value: "'Trebuchet MS', sans-serif" },
    { name: "Times New Roman", value: "'Times New Roman', serif" },
];

const FONT_WEIGHTS = [
    { label: "Thin", value: "100" },
    { label: "Light", value: "300" },
    { label: "Regular", value: "400" },
    { label: "Medium", value: "500" },
    { label: "Semi Bold", value: "600" },
    { label: "Bold", value: "700" },
    { label: "Extra Bold", value: "800" },
    { label: "Black", value: "900" },
];

const COLOR_PROPS = [
    { key: "primary_color", label: "Primary" },
    { key: "accent_color", label: "Accent" },
    { key: "background_color", label: "Page BG" },
    { key: "text_color", label: "Text" },
    { key: "header_color", label: "Header BG" },
    { key: "footer_color", label: "Footer BG" },
];

const SETTING_TOGGLES = [
    { key: "show_page_numbers", label: "Page Numbers" },
    { key: "show_header", label: "Show Header" },
    { key: "show_footer", label: "Show Footer" },
    { key: "showGrid", label: "Grid Overlay" },
    { key: "snapToGrid", label: "Snap to Grid" },
    { key: "showRulers", label: "Rulers" },
    { key: "showGuides", label: "Smart Guides" },
    { key: "rtl", label: "RTL Layout" },
];

const ALIGNMENTS = [
    { dir: "left", label: "⟵ Left" },
    { dir: "center-h", label: "⟺ Center" },
    { dir: "right", label: "Right ⟶" },
    { dir: "top", label: "⟰ Top" },
    { dir: "center-v", label: "⥮ Middle" },
    { dir: "bottom", label: "Bot ⟱" },
];

const CODE_LANGS = [
    "JavaScript",
    "TypeScript",
    "Python",
    "PHP",
    "HTML",
    "CSS",
    "SQL",
    "JSON",
    "Bash",
    "Java",
    "C#",
    "C++",
    "Go",
    "Rust",
    "Ruby",
    "YAML",
    "Markdown",
    "Vue",
];

const QUICK_ADD = [
    { type: "heading", label: "Heading", icon: "🔤" },
    { type: "text", label: "Paragraph", icon: "¶" },
    { type: "metric", label: "KPI Card", icon: "📈" },
    { type: "bar-chart", label: "Bar Chart", icon: "📊" },
    { type: "table", label: "Table", icon: "📋" },
    { type: "image", label: "Image", icon: "🖼️" },
    { type: "checklist", label: "Checklist", icon: "✅" },
    { type: "progress", label: "Progress", icon: "▬" },
];

const SHORTCUTS = [
    { key: "Ctrl+Z", desc: "Undo" },
    { key: "Ctrl+Y", desc: "Redo" },
    { key: "Ctrl+S", desc: "Save" },
    { key: "Ctrl+D", desc: "Duplicate" },
    { key: "Del", desc: "Delete element" },
    { key: "D", desc: "Toggle Drag" },
    { key: "G", desc: "Toggle Grid" },
    { key: "R", desc: "Toggle Rulers" },
    { key: "F", desc: "Fullscreen" },
    { key: "Esc", desc: "Deselect" },
    { key: "←↑→↓", desc: "Nudge 1px" },
    { key: "Shift+←", desc: "Nudge 10px" },
];

const ELEMENT_META = {
    heading: "H1",
    subheading: "H2",
    text: "¶",
    quote: "❝",
    blockquote: "❞",
    highlight: "🖊",
    list: "≡",
    checklist: "✅",
    link: "🔗",
    badge: "🏷",
    code: "<>",
    callout: "ℹ",
    alert: "⚠️",
    date: "📅",
    pagenum: "#",
    signature: "✍",
    icon: "★",
    rating: "⭐",
    toc: "📑",
    timeline: "⏱",
    table: "📋",
    "bar-chart": "📊",
    "line-chart": "📈",
    "pie-chart": "🥧",
    "doughnut-chart": "○",
    "area-chart": "▨",
    "radar-chart": "✦",
    metric: "📈",
    progress: "▬",
    "circular-progress": "◐",
    image: "🖼",
    video: "🎬",
    rectangle: "▭",
    circle: "●",
    triangle: "▲",
    star: "★",
    line: "—",
    arrow: "→",
    "double-arrow": "↔",
    divider: "─",
    spacer: "□",
    "social-card": "👤",
    testimonial: "💬",
    "price-card": "💰",
    "stat-row": "📊",
    kanban: "📌",
};

const RESIZE_HANDLES = [
    { dir: "nw", style: "top:-5px;left:-5px;cursor:nw-resize" },
    { dir: "n", style: "top:-5px;left:calc(50% - 5px);cursor:n-resize" },
    { dir: "ne", style: "top:-5px;right:-5px;cursor:ne-resize" },
    { dir: "w", style: "top:calc(50% - 5px);left:-5px;cursor:w-resize" },
    { dir: "e", style: "top:calc(50% - 5px);right:-5px;cursor:e-resize" },
    { dir: "sw", style: "bottom:-5px;left:-5px;cursor:sw-resize" },
    { dir: "s", style: "bottom:-5px;left:calc(50% - 5px);cursor:s-resize" },
    { dir: "se", style: "bottom:-5px;right:-5px;cursor:se-resize" },
];

const SHADOW_MAP = {
    none: "none",
    sm: "0 1px 3px rgba(0,0,0,.12)",
    md: "0 4px 12px rgba(0,0,0,.1)",
    lg: "0 10px 30px rgba(0,0,0,.15)",
    xl: "0 20px 60px rgba(0,0,0,.2)",
    "2xl": "0 25px 80px rgba(0,0,0,.25)",
    inner: "inset 0 2px 4px rgba(0,0,0,.1)",
    "glow-indigo": "0 0 20px rgba(99,102,241,.5)",
    "glow-emerald": "0 0 20px rgba(16,185,129,.5)",
    "glow-gold": "0 0 20px rgba(245,158,11,.5)",
};

const CALLOUT_PRESETS = {
    info: { bg: "#eff6ff", border: "#bfdbfe", color: "#1e40af", emoji: "ℹ️" },
    warning: {
        bg: "#fffbeb",
        border: "#fde68a",
        color: "#92400e",
        emoji: "⚠️",
    },
    success: {
        bg: "#ecfdf5",
        border: "#a7f3d0",
        color: "#065f46",
        emoji: "✅",
    },
    danger: { bg: "#fef2f2", border: "#fecaca", color: "#991b1b", emoji: "🚨" },
    neutral: {
        bg: "#f8fafc",
        border: "#e2e8f0",
        color: "#334155",
        emoji: "📝",
    },
    purple: { bg: "#faf5ff", border: "#e9d5ff", color: "#6b21a8", emoji: "💡" },
};

// ─── Element Categories ───────────────────────────────────────────────────────
const elementCategories = [
    {
        name: "Text & Content",
        icon: "📝",
        items: [
            {
                type: "heading",
                name: "Heading H1",
                icon: '<strong style="font-size:14px;color:#1e293b">H1</strong>',
            },
            {
                type: "subheading",
                name: "Heading H2",
                icon: '<strong style="font-size:11px;color:#1e293b">H2</strong>',
            },
            { type: "text", name: "Paragraph", icon: "¶" },
            { type: "quote", name: "Quote", icon: "❝" },
            { type: "blockquote", name: "Block Quote", icon: "❞" },
            { type: "highlight", name: "Highlight", icon: "🖊️" },
            { type: "list", name: "List", icon: "≡" },
            { type: "checklist", name: "Checklist", icon: "✅" },
            { type: "link", name: "Hyperlink", icon: "🔗" },
            { type: "badge", name: "Badge", icon: "🏷️" },
            { type: "callout", name: "Callout", icon: "ℹ️" },
            { type: "alert", name: "Alert Banner", icon: "⚠️" },
            {
                type: "code",
                name: "Code Block",
                icon: '<code style="font-size:10px">&lt;/&gt;</code>',
            },
            { type: "icon", name: "Icon / Emoji", icon: "★" },
            { type: "rating", name: "Star Rating", icon: "⭐" },
            { type: "toc", name: "Table of Contents", icon: "📑" },
        ],
    },
    {
        name: "Data & Charts",
        icon: "📊",
        items: [
            { type: "table", name: "Table", icon: "📋" },
            { type: "bar-chart", name: "Bar Chart", icon: "📊" },
            { type: "line-chart", name: "Line Chart", icon: "📈" },
            { type: "pie-chart", name: "Pie Chart", icon: "🥧" },
            { type: "doughnut-chart", name: "Doughnut", icon: "○" },
            { type: "area-chart", name: "Area Chart", icon: "▨" },
            { type: "radar-chart", name: "Radar", icon: "✦" },
            { type: "metric", name: "KPI Card", icon: "💹" },
            { type: "progress", name: "Progress Bar", icon: "▬" },
            { type: "circular-progress", name: "Circular Progress", icon: "◐" },
            { type: "stat-row", name: "Stats Row", icon: "🔢" },
            { type: "timeline", name: "Timeline", icon: "⏱️" },
        ],
    },
    {
        name: "Media",
        icon: "🖼️",
        items: [
            { type: "image", name: "Image", icon: "🖼️" },
            { type: "video", name: "Video Embed", icon: "🎬" },
        ],
    },
    {
        name: "Shapes & Layout",
        icon: "🔷",
        items: [
            { type: "rectangle", name: "Rectangle", icon: "▭" },
            { type: "circle", name: "Circle", icon: "●" },
            { type: "triangle", name: "Triangle", icon: "▲" },
            { type: "star", name: "Star", icon: "★" },
            { type: "line", name: "Line", icon: "—" },
            { type: "arrow", name: "Arrow →", icon: "→" },
            { type: "double-arrow", name: "Double ↔", icon: "↔" },
            { type: "divider", name: "Divider", icon: "─" },
            { type: "spacer", name: "Spacer", icon: "□" },
        ],
    },
    {
        name: "Cards & Components",
        icon: "🃏",
        items: [
            { type: "social-card", name: "Profile Card", icon: "👤" },
            { type: "testimonial", name: "Testimonial", icon: "💬" },
            { type: "kanban", name: "Kanban Card", icon: "📌" },
            { type: "price-card", name: "Price Card", icon: "💰" },
        ],
    },
    {
        name: "Report Elements",
        icon: "📋",
        items: [
            { type: "pagenum", name: "Page Number", icon: "#️⃣" },
            { type: "date", name: "Date", icon: "📅" },
            { type: "signature", name: "Signature", icon: "✍️" },
        ],
    },
];

// ─── State ────────────────────────────────────────────────────────────────────
const pages = ref(
    props.report?.content?.length
        ? props.report.content
        : [{ id: uuidv4(), elements: [] }],
);
const selectedElement = ref(null);
const selectedPageIndex = ref(0);
const zoom = ref(100);
const dark = ref(false);
const showGrid = ref(false);
const snapToGrid = ref(false);
const showRulers = ref(false);
const showGuides = ref(false);
const dragMode = ref(false);
const gridSize = ref(20);
const leftTab = ref("elements");
const saveStatus = ref("idle");
const isDragOver = ref(false);
const dragTargetPage = ref(null);
const elementSearch = ref("");
const collapsedCategories = ref([]);
const collapsedSections = ref([]);
const history = ref([JSON.stringify(pages.value)]);
const historyIndex = ref(0);
const chartInstances = new Map();
const showExportMenu = ref(false);
const exportMenuRef = ref(null);
const canvasArea = ref(null);
const toast = reactive({ show: false, message: "", type: "success" });
const contextMenu = reactive({ show: false, x: 0, y: 0, el: null, pi: null });
const chartLabelsInput = ref("");
const chartValuesInput = ref("");
const chartDatasetLabel = ref("Dataset 1");
const chartColor = ref("#6366f1");
const pieColorsInput = ref("");

const rs = reactive({
    page_size: props.report?.settings?.page_size || "A4",
    orientation: props.report?.settings?.orientation || "portrait",
    primary_color: props.report?.settings?.primary_color || "#6366f1",
    background_color: props.report?.settings?.background_color || "#ffffff",
    accent_color: props.report?.settings?.accent_color || "#8b5cf6",
    text_color: props.report?.settings?.text_color || "#0f172a",
    font_family:
        props.report?.settings?.font_family || "'DM Sans', sans-serif",
    margin: props.report?.settings?.margin || 40,
    show_page_numbers: props.report?.settings?.show_page_numbers ?? false,
    show_header: props.report?.settings?.show_header ?? false,
    show_footer: props.report?.settings?.show_footer ?? false,
    header_text: props.report?.settings?.header_text || "",
    footer_text: props.report?.settings?.footer_text || "",
    header_color: props.report?.settings?.header_color || "#1e293b",
    footer_color: props.report?.settings?.footer_color || "#1e293b",
    watermark: props.report?.settings?.watermark || "",
    rtl: props.report?.settings?.rtl ?? false,
    bg_image: props.report?.settings?.bg_image || "",
    page_radius: props.report?.settings?.page_radius || 0,
});

const form = useForm({ title: props.report?.title || "Untitled Report" });

// ─── Computed ────────────────────────────────────────────────────────────────
const currentPage = computed(
    () => pages.value[selectedPageIndex.value] || { elements: [] },
);

const canvasDimensions = computed(() => {
    const sizes = {
        A4: {
            portrait: { width: 794, height: 1123 },
            landscape: { width: 1123, height: 794 },
        },
        Letter: {
            portrait: { width: 816, height: 1056 },
            landscape: { width: 1056, height: 816 },
        },
        Legal: {
            portrait: { width: 816, height: 1344 },
            landscape: { width: 1344, height: 816 },
        },
        A3: {
            portrait: { width: 1123, height: 1587 },
            landscape: { width: 1587, height: 1123 },
        },
        A5: {
            portrait: { width: 559, height: 794 },
            landscape: { width: 794, height: 559 },
        },
    };
    return sizes[rs.page_size]?.[rs.orientation] || sizes.A4.portrait;
});

const inputCls = computed(() =>
    dark.value
        ? "w-full text-xs bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-200 transition-all"
        : "w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 transition-all",
);

const layerBtnCls = computed(() =>
    dark.value
        ? "border-slate-700 text-slate-500 hover:bg-slate-800 hover:border-slate-600 hover:text-slate-300"
        : "border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300",
);

const secBind = (id) => ({
    id,
    collapsedSections: collapsedSections.value,
    dark: dark.value,
    onToggle: togglePropSection,
});

const filteredCategories = computed(() => {
    if (!elementSearch.value.trim()) return elementCategories;
    const q = elementSearch.value.toLowerCase();
    return elementCategories
        .map((c) => ({
            ...c,
            items: c.items.filter(
                (i) => i.name.toLowerCase().includes(q) || i.type.includes(q),
            ),
        }))
        .filter((c) => c.items.length);
});

// ─── Settings toggle helpers ─────────────────────────────────────────────────
const TOGGLE_MAP = {
    show_page_numbers: () => [rs.show_page_numbers, (v) => (rs.show_page_numbers = v)],
    show_header: () => [rs.show_header, (v) => (rs.show_header = v)],
    show_footer: () => [rs.show_footer, (v) => (rs.show_footer = v)],
    showGrid: () => [showGrid.value, (v) => (showGrid.value = v)],
    snapToGrid: () => [snapToGrid.value, (v) => (snapToGrid.value = v)],
    showRulers: () => [showRulers.value, (v) => (showRulers.value = v)],
    showGuides: () => [showGuides.value, (v) => (showGuides.value = v)],
    rtl: () => [rs.rtl, (v) => (rs.rtl = v)],
};

const getToggle = (key) => TOGGLE_MAP[key]?.()?.[0] ?? false;
const setToggle = (key, v) => TOGGLE_MAP[key]?.()?.[1]?.(v);

// ─── Canvas helpers ───────────────────────────────────────────────────────────
const isPageActive = (pi) => {
    if (!selectedElement.value) return false;
    return selectedPageIndex.value === pi;
};

const getCanvasStyle = () => {
    const style = {
        width: canvasDimensions.value.width * (zoom.value / 100) + "px",
        height: canvasDimensions.value.height * (zoom.value / 100) + "px",
        backgroundColor: rs.background_color,
        fontFamily: rs.font_family,
        direction: rs.rtl ? "rtl" : "ltr",
        borderRadius: (rs.page_radius || 0) + "px",
    };
    if (rs.bg_image) {
        style.backgroundImage = `url('${rs.bg_image}')`;
        style.backgroundSize = "cover";
        style.backgroundPosition = "center";
    }
    return style;
};

const getElementWrapperStyle = (el) => {
    const s = el.styles || {};
    const zf = zoom.value / 100;
    return {
        left: el.position.x * zf + "px",
        top: el.position.y * zf + "px",
        width: (s.width || 200) * zf + "px",
        height: (s.height || 50) * zf + "px",
        transform: s.rotate ? `rotate(${s.rotate}deg)` : undefined,
        zIndex: s.zIndex || 1,
        opacity: s.opacity !== undefined ? s.opacity / 100 : 1,
        display: el.hidden ? "none" : undefined,
        cursor: el.locked ? "not-allowed" : dragMode.value ? "move" : "default",
    };
};

const getElementContentStyle = (el) => {
    const s = el.styles || {};
    return {
        borderRadius: s.borderRadius ? s.borderRadius + "px" : undefined,
        border: s.borderWidth
            ? `${s.borderWidth}px ${s.borderStyle || "solid"} ${s.borderColor || "#000"}`
            : undefined,
        boxShadow: SHADOW_MAP[s.boxShadow] || undefined,
        padding: s.padding ? s.padding + "px" : undefined,
        background: s.backgroundGradient || s.backgroundColor || undefined,
        overflow: s.overflow || "hidden",
        filter: s.filter || undefined,
    };
};

const getTextStyle = (el) => {
    const s = el.styles || {};
    const zf = zoom.value / 100;
    return {
        fontSize: (s.fontSize || 16) * zf + "px",
        color: s.color || rs.text_color || "#1e293b",
        fontFamily: s.fontFamily || rs.font_family,
        fontWeight: s.fontWeight || (el.type === "heading" ? "700" : "400"),
        fontStyle: s.fontStyle || "normal",
        textAlign: s.textAlign || "left",
        textDecoration: s.textDecoration || "none",
        textTransform: s.textTransform || "none",
        lineHeight: s.lineHeight || 1.6,
        letterSpacing: s.letterSpacing ? s.letterSpacing + "px" : undefined,
        wordSpacing: s.wordSpacing ? s.wordSpacing + "px" : undefined,
        textShadow: s.textShadow || undefined,
    };
};

const formatDateEl = (el) => {
    if (el.format === "custom" && el.customDate) return el.customDate;
    const d = new Date();
    if (el.format === "iso") return d.toISOString().split("T")[0];
    const opts = {
        long: { year: "numeric", month: "long", day: "numeric" },
        short: { year: "numeric", month: "short", day: "numeric" },
        numeric: { year: "numeric", month: "2-digit", day: "2-digit" },
    };
    return d.toLocaleDateString("en-US", opts[el.format] || opts.long);
};

// ─── Context Menu ────────────────────────────────────────────────────────────
const openContextMenu = (e, el, pi) => {
    contextMenu.show = true;
    contextMenu.x = Math.min(e.clientX, window.innerWidth - 200);
    contextMenu.y = Math.min(e.clientY, window.innerHeight - 300);
    contextMenu.el = el;
    contextMenu.pi = pi;
    selectElement(el, pi);
};

const contextMenuItems = computed(() => {
    if (!contextMenu.el) return [];
    const el = contextMenu.el;
    const pi = contextMenu.pi;
    return [
        {
            icon: "✏️",
            label: "Edit",
            key: "DblClick",
            action: () => {
                if (
                    !selectedElement.value ||
                    selectedElement.value.id !== el.id
                )
                    selectElement(el, pi);
            },
        },
        {
            icon: "📋",
            label: "Duplicate",
            key: "Ctrl+D",
            action: () => duplicateElement(pi, el),
        },
        {
            icon: "🔝",
            label: "Bring to Front",
            key: null,
            action: () => bringToFront(),
        },
        {
            icon: "🔙",
            label: "Send to Back",
            key: null,
            action: () => sendToBack(),
        },
        {
            icon: "👁",
            label: el.hidden ? "Show Element" : "Hide Element",
            key: null,
            action: () => {
                el.hidden = !el.hidden;
                pushHistory();
            },
        },
        {
            icon: "🔒",
            label: el.locked ? "Unlock" : "Lock",
            key: null,
            action: () => lockElement(el),
        },
        ...(pi > 0
            ? [
                  {
                      icon: "⬆️",
                      label: "Move to Prev Page",
                      key: null,
                      action: () => moveElementToPage(el, pi, pi - 1),
                  },
              ]
            : []),
        ...(pi < pages.value.length - 1
            ? [
                  {
                      icon: "⬇️",
                      label: "Move to Next Page",
                      key: null,
                      action: () => moveElementToPage(el, pi, pi + 1),
                  },
              ]
            : []),
        {
            icon: "🗑",
            label: "Delete",
            key: "Del",
            action: () => deleteElement(pi, el.id),
            danger: true,
        },
    ];
});

// ─── Drag from sidebar ────────────────────────────────────────────────────────
const handleDragStart = (e, el) =>
    e.dataTransfer.setData("elementType", el.type);
const handleDragOver = (e, pi) => {
    isDragOver.value = true;
    dragTargetPage.value = pi;
};
const handleDrop = (e, pi) => {
    isDragOver.value = false;
    const type = e.dataTransfer.getData("elementType");
    if (!type) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const zf = zoom.value / 100;
    addElementAtPosition(
        type,
        pi,
        (e.clientX - rect.left) / zf - 50,
        (e.clientY - rect.top) / zf - 25,
    );
};

// ─── Element operations ───────────────────────────────────────────────────────
const addElement = (type) => {
    const pi = selectedPageIndex.value ?? 0;
    addElementAtPosition(
        type,
        pi,
        60,
        60 + pages.value[pi].elements.length * 30,
    );
};

const addElementAtPosition = (type, pi, x, y) => {
    const el = createDefaultElement(type, x, y);
    pages.value[pi].elements.push(el);
    nextTick(() => {
        if (isChartType(type)) initChart(el);
    });
    selectElement(el, pi);
    pushHistory();
};

const createDefaultElement = (type, x = 60, y = 60) => {
    const base = {
        id: uuidv4(),
        type,
        position: { x, y },
        styles: { width: 200, height: 50, zIndex: 1, opacity: 100 },
    };
    const P = rs.primary_color;
    const T = rs.text_color;
    const presets = {
        heading: {
            content: "Report Heading",
            styles: {
                ...base.styles,
                width: 460,
                height: 64,
                fontSize: 32,
                fontWeight: "700",
                color: T || "#0f172a",
            },
        },
        subheading: {
            content: "Section Title",
            styles: {
                ...base.styles,
                width: 340,
                height: 44,
                fontSize: 20,
                fontWeight: "600",
                color: T || "#1e293b",
            },
        },
        text: {
            content:
                "Add your paragraph text here. Double-click to edit inline.",
            styles: {
                ...base.styles,
                width: 420,
                height: 80,
                fontSize: 14,
                color: "#334155",
                lineHeight: 1.7,
            },
        },
        quote: {
            content: "An insightful quote goes here.",
            styles: {
                ...base.styles,
                width: 380,
                height: 80,
                fontSize: 15,
                fontStyle: "italic",
                color: "#475569",
                backgroundColor: "#f8fafc",
                padding: 16,
            },
        },
        blockquote: {
            content: "Block quote text here.",
            styles: {
                ...base.styles,
                width: 380,
                height: 100,
                fontSize: 14,
                color: "#334155",
                backgroundColor: P + "10",
                padding: 16,
                borderRadius: 12,
            },
        },
        highlight: {
            content: "Highlighted text",
            styles: {
                ...base.styles,
                width: 200,
                height: 36,
                fontSize: 14,
                color: "#713f12",
                backgroundColor: "#fef9c3",
                padding: 6,
                borderRadius: 4,
            },
        },
        list: {
            items: ["First item", "Second item", "Third item"],
            styles: {
                ...base.styles,
                width: 280,
                height: 120,
                fontSize: 14,
                color: "#334155",
            },
        },
        checklist: {
            items: [
                { text: "Task one", checked: false },
                { text: "Task two", checked: true },
                { text: "Task three", checked: false },
            ],
            styles: {
                ...base.styles,
                width: 300,
                height: 140,
                fontSize: 13,
            },
        },
        link: {
            content: "Click here",
            href: "https://example.com",
            target: "_blank",
            styles: {
                ...base.styles,
                width: 160,
                height: 32,
                fontSize: 14,
                color: P,
            },
        },
        badge: {
            content: "Status",
            styles: { ...base.styles, width: 90, height: 36 },
        },
        callout: {
            content: "Add your callout note here.",
            emoji: "ℹ️",
            calloutStyle: "info",
            styles: {
                ...base.styles,
                width: 380,
                height: 90,
                backgroundColor: "#eff6ff",
                borderColor: "#bfdbfe",
                borderWidth: 1,
                borderRadius: 10,
            },
        },
        alert: {
            content: "Important alert message.",
            emoji: "⚠️",
            styles: {
                ...base.styles,
                width: 380,
                height: 70,
                backgroundColor: "#fef3c7",
                borderColor: "#f59e0b",
                color: "#92400e",
            },
        },
        code: {
            content: 'const x = "Hello, World!";\nconsole.log(x);',
            language: "JavaScript",
            styles: { ...base.styles, width: 380, height: 120 },
        },
        icon: {
            content: "★",
            styles: {
                ...base.styles,
                width: 60,
                height: 60,
                fontSize: 40,
                color: P,
            },
        },
        rating: {
            value: 4,
            styles: {
                ...base.styles,
                width: 160,
                height: 48,
                color: "#f59e0b",
            },
        },
        date: {
            format: "long",
            styles: {
                ...base.styles,
                width: 220,
                height: 30,
                fontSize: 13,
                color: "#64748b",
            },
        },
        pagenum: {
            styles: {
                ...base.styles,
                width: 100,
                height: 24,
                fontSize: 12,
                textAlign: "center",
                color: "#94a3b8",
            },
        },
        signature: {
            label: "Authorised Signature",
            styles: {
                ...base.styles,
                width: 260,
                height: 80,
                color: "#334155",
            },
        },
        toc: {
            title: "Table of Contents",
            items: [
                { label: "Section 1", page: 1 },
                { label: "Section 2", page: 3 },
                { label: "Section 3", page: 5 },
            ],
            styles: {
                ...base.styles,
                width: 360,
                height: 160,
                fontSize: 12,
                color: "#334155",
            },
        },
        timeline: {
            items: [
                {
                    label: "Milestone 1",
                    date: "2024 Q1",
                    desc: "Kickoff & planning",
                },
                {
                    label: "Milestone 2",
                    date: "2024 Q3",
                    desc: "Development & testing",
                },
                {
                    label: "Milestone 3",
                    date: "2025 Q1",
                    desc: "Launch & deployment",
                },
            ],
            styles: { ...base.styles, width: 340, height: 200 },
        },
        image: {
            src: "",
            alt: "",
            styles: {
                ...base.styles,
                width: 320,
                height: 220,
                borderRadius: 8,
            },
        },
        video: {
            src: "",
            styles: { ...base.styles, width: 360, height: 220, borderRadius: 8 },
        },
        table: {
            columns: ["Name", "Value", "Change"],
            data: [
                { Name: "Revenue", Value: "$1.2M", Change: "+12%" },
                { Name: "Users", Value: "8,400", Change: "+5%" },
                { Name: "Conversion", Value: "3.2%", Change: "-0.3%" },
            ],
            styles: {
                ...base.styles,
                width: 460,
                height: 170,
                headerBg: P,
                headerColor: "#fff",
                evenRowBg: "#fff",
                oddRowBg: "#f8fafc",
            },
        },
        "bar-chart": {
            chartData: {
                labels: ["Q1", "Q2", "Q3", "Q4"],
                values: [42, 68, 55, 81],
            },
            chartTitle: "Quarterly Performance",
            legendPosition: "bottom",
            styles: { ...base.styles, width: 400, height: 280 },
        },
        "line-chart": {
            chartData: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
                values: [30, 48, 36, 70, 60, 85],
            },
            chartTitle: "Trend Analysis",
            legendPosition: "bottom",
            styles: { ...base.styles, width: 400, height: 280 },
        },
        "pie-chart": {
            chartData: {
                labels: ["Category A", "Category B", "Category C", "Category D"],
                values: [35, 25, 22, 18],
            },
            chartTitle: "Distribution",
            legendPosition: "bottom",
            styles: { ...base.styles, width: 320, height: 300 },
        },
        "doughnut-chart": {
            chartData: {
                labels: ["Segment A", "Segment B", "Segment C"],
                values: [40, 35, 25],
            },
            chartTitle: "Composition",
            legendPosition: "bottom",
            styles: { ...base.styles, width: 320, height: 300 },
        },
        "area-chart": {
            chartData: {
                labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                values: [20, 45, 38, 60, 55, 72, 80],
            },
            chartTitle: "Weekly Overview",
            legendPosition: "bottom",
            styles: { ...base.styles, width: 400, height: 280 },
        },
        "radar-chart": {
            chartData: {
                labels: ["Speed", "Accuracy", "Efficiency", "Quality", "Innovation"],
                values: [80, 92, 75, 88, 70],
            },
            chartTitle: "Performance",
            legendPosition: "bottom",
            styles: { ...base.styles, width: 340, height: 320 },
        },
        metric: {
            label: "Total Revenue",
            value: "$48,293",
            change: "12.5%",
            changeType: "positive",
            changePeriod: "vs last month",
            styles: {
                ...base.styles,
                width: 240,
                height: 120,
                backgroundColor: "#f8fafc",
                borderColor: "#e2e8f0",
                borderWidth: 1,
                borderRadius: 14,
            },
        },
        progress: {
            label: "Completion Rate",
            value: 72,
            styles: {
                ...base.styles,
                width: 340,
                height: 50,
                color: P,
                trackColor: "#e2e8f0",
                trackHeight: 8,
            },
        },
        "circular-progress": {
            label: "Progress",
            value: 72,
            styles: {
                ...base.styles,
                width: 180,
                height: 200,
                color: P,
                trackColor: "#e2e8f0",
            },
        },
        "stat-row": {
            cols: 3,
            stats: [
                { label: "Revenue", value: "$48K" },
                { label: "Users", value: "8.4K" },
                { label: "Growth", value: "+12%" },
            ],
            styles: { ...base.styles, width: 460, height: 100 },
        },
        rectangle: {
            styles: {
                ...base.styles,
                width: 200,
                height: 120,
                backgroundColor: "#e0e7ff",
                borderRadius: 10,
            },
        },
        circle: {
            styles: {
                ...base.styles,
                width: 100,
                height: 100,
                backgroundColor: P,
            },
        },
        triangle: {
            styles: {
                ...base.styles,
                width: 120,
                height: 120,
                backgroundColor: "#f59e0b",
            },
        },
        star: {
            styles: {
                ...base.styles,
                width: 80,
                height: 80,
                backgroundColor: P,
            },
        },
        line: {
            styles: {
                ...base.styles,
                width: 240,
                height: 20,
                borderWidth: 2,
                color: "#cbd5e1",
            },
        },
        arrow: {
            styles: {
                ...base.styles,
                width: 200,
                height: 30,
                borderWidth: 2,
                color: P,
            },
        },
        "double-arrow": {
            styles: {
                ...base.styles,
                width: 200,
                height: 30,
                borderWidth: 2,
                color: P,
            },
        },
        divider: {
            styles: {
                ...base.styles,
                width: 460,
                height: 20,
                borderWidth: 1,
                borderStyle: "solid",
                color: "#e2e8f0",
            },
        },
        spacer: { styles: { ...base.styles, width: 200, height: 40 } },
        "social-card": {
            content: "Name Here",
            subtitle: "Title / Organisation",
            avatar: "👤",
            styles: {
                ...base.styles,
                width: 240,
                height: 200,
                backgroundColor: "#fff",
                borderColor: "#e2e8f0",
                borderWidth: 1,
                borderRadius: 16,
            },
        },
        testimonial: {
            content: "Amazing product! Highly recommend.",
            author: "John Doe",
            role: "CEO, Acme Corp",
            avatar: "👤",
            styles: {
                ...base.styles,
                width: 300,
                height: 180,
                backgroundColor: "#f8fafc",
                borderColor: "#e2e8f0",
                borderWidth: 1,
                borderRadius: 16,
            },
        },
        kanban: {
            content: "Task Title",
            status: "In Progress",
            due: "Dec 31",
            styles: {
                ...base.styles,
                width: 220,
                height: 100,
                backgroundColor: "#fff",
                borderColor: "#e2e8f0",
                accentColor: P,
            },
        },
        "price-card": {
            plan: "Pro Plan",
            price: "$29",
            period: "per month",
            features: ["Feature 1", "Feature 2", "Feature 3"],
            highlighted: false,
            styles: {
                ...base.styles,
                width: 220,
                height: 280,
                backgroundColor: "#fff",
                borderColor: "#e2e8f0",
                borderWidth: 1,
                borderRadius: 16,
            },
        },
    };
    const d = presets[type] || {};
    return { ...base, ...d, styles: { ...base.styles, ...(d.styles || {}) } };
};

const duplicateElement = (pi, el) => {
    const clone = JSON.parse(JSON.stringify(el));
    clone.id = uuidv4();
    clone.position = { x: el.position.x + 20, y: el.position.y + 20 };
    pages.value[pi].elements.push(clone);
    nextTick(() => {
        if (isChartType(clone.type)) initChart(clone);
    });
    selectElement(clone, pi);
    pushHistory();
};

const deleteElement = (pi, id) => {
    pages.value[pi].elements = pages.value[pi].elements.filter(
        (e) => e.id !== id,
    );
    if (selectedElement.value?.id === id) selectedElement.value = null;
    pushHistory();
};

const deleteSelectedElement = () => {
    if (!selectedElement.value) return;
    deleteElement(selectedPageIndex.value, selectedElement.value.id);
};

const selectElement = (el, pi) => {
    selectedElement.value = el;
    selectedPageIndex.value = pi;
    if (isChartType(el.type) && el.chartData) {
        chartLabelsInput.value = (el.chartData.labels || []).join(", ");
        chartValuesInput.value = (el.chartData.values || []).join(", ");
        chartDatasetLabel.value = el.chartDatasetLabel || "Dataset 1";
        chartColor.value = el.chartColor || rs.primary_color;
        pieColorsInput.value = (el.pieColors || []).join(", ");
    }
};

const deselectAll = () => {
    selectedElement.value = null;
};

const lockElement = (el) => {
    el.locked = !el.locked;
    pushHistory();
};

const moveElementToPage = (el, fromPi, toPi) => {
    if (toPi < 0 || toPi >= pages.value.length || toPi === fromPi) return;
    pages.value[fromPi].elements = pages.value[fromPi].elements.filter(
        (e) => e.id !== el.id,
    );
    const clone = { ...el, id: el.id };
    pages.value[toPi].elements.push(clone);
    selectedPageIndex.value = toPi;
    nextTick(() => {
        if (isChartType(el.type)) initChart(el);
    });
    pushHistory();
    showToast(`Moved to Page ${toPi + 1}`);
};

// ─── Drag on canvas ───────────────────────────────────────────────────────────
let isDraggingEl = false,
    dSX = 0,
    dSY = 0,
    eSX = 0,
    eSY = 0;

const startDrag = (e, el, pi) => {
    if (e.button !== 0 || el.locked) return;
    selectElement(el, pi);
    isDraggingEl = true;
    dSX = e.clientX;
    dSY = e.clientY;
    eSX = el.position.x;
    eSY = el.position.y;
    window.addEventListener("mousemove", onDragMove);
    window.addEventListener("mouseup", stopDrag);
    e.preventDefault();
};

const onDragMove = (e) => {
    if (!isDraggingEl || !selectedElement.value) return;
    const zf = zoom.value / 100;
    let nx = eSX + (e.clientX - dSX) / zf;
    let ny = eSY + (e.clientY - dSY) / zf;
    if (snapToGrid.value) {
        nx = Math.round(nx / gridSize.value) * gridSize.value;
        ny = Math.round(ny / gridSize.value) * gridSize.value;
    }
    selectedElement.value.position.x = Math.max(0, nx);
    selectedElement.value.position.y = Math.max(0, ny);
};

const stopDrag = () => {
    isDraggingEl = false;
    window.removeEventListener("mousemove", onDragMove);
    window.removeEventListener("mouseup", stopDrag);
    pushHistory();
};

// ─── Resize ───────────────────────────────────────────────────────────────────
let isResizing = false,
    rDir = "",
    rSX = 0,
    rSY = 0,
    rSW = 0,
    rSH = 0,
    rSEX = 0,
    rSEY = 0;

const startResize = (dir, e) => {
    e.preventDefault();
    isResizing = true;
    rDir = dir;
    rSX = e.clientX;
    rSY = e.clientY;
    rSW = selectedElement.value.styles.width || 200;
    rSH = selectedElement.value.styles.height || 50;
    rSEX = selectedElement.value.position.x;
    rSEY = selectedElement.value.position.y;
    window.addEventListener("mousemove", onResizeMove);
    window.addEventListener("mouseup", stopResize);
};

const onResizeMove = (e) => {
    if (!isResizing || !selectedElement.value) return;
    const zf = zoom.value / 100;
    const dx = (e.clientX - rSX) / zf;
    const dy = (e.clientY - rSY) / zf;
    const MIN = 20,
        s = selectedElement.value.styles,
        p = selectedElement.value.position;
    if (rDir.includes("e")) s.width = Math.max(MIN, rSW + dx);
    if (rDir.includes("s")) s.height = Math.max(MIN, rSH + dy);
    if (rDir.includes("w")) {
        const nw = Math.max(MIN, rSW - dx);
        p.x = rSEX + (rSW - nw);
        s.width = nw;
    }
    if (rDir.includes("n")) {
        const nh = Math.max(MIN, rSH - dy);
        p.y = rSEY + (rSH - nh);
        s.height = nh;
    }
};

const stopResize = () => {
    isResizing = false;
    window.removeEventListener("mousemove", onResizeMove);
    window.removeEventListener("mouseup", stopResize);
    pushHistory();
};

// ─── Rotation ─────────────────────────────────────────────────────────────────
const startRotation = (e, el) => {
    const rect = e.currentTarget.parentElement.getBoundingClientRect();
    const cx = rect.left + rect.width / 2,
        cy = rect.top + rect.height / 2;
    const onMove = (mv) => {
        el.styles.rotate = Math.round(
            Math.atan2(mv.clientY - cy, mv.clientX - cx) * (180 / Math.PI) + 90,
        );
    };
    const onUp = () => {
        window.removeEventListener("mousemove", onMove);
        window.removeEventListener("mouseup", onUp);
        pushHistory();
    };
    window.addEventListener("mousemove", onMove);
    window.addEventListener("mouseup", onUp);
    e.preventDefault();
};

// ─── Text editing ─────────────────────────────────────────────────────────────
const onTextBlur = (el, e) => {
    el.content = e.target.innerHTML || e.target.innerText;
    pushHistory();
};
const focusText = (e) => {
    const el = e.target;
    el.focus();
    const r = document.createRange();
    r.selectNodeContents(el);
    const s = window.getSelection();
    s.removeAllRanges();
    s.addRange(r);
};
const onListItemBlur = (el, i, e) => {
    el.items[i] = e.target.innerText;
    pushHistory();
};
const addListItem = (el) => {
    el.items.push("New item");
    pushHistory();
};
const onChecklistItemBlur = (el, i, e) => {
    el.items[i].text = e.target.innerText;
    pushHistory();
};
const addChecklistItem = (el) => {
    el.items.push({ text: "New task", checked: false });
    pushHistory();
};
const onTableCellBlur = (el, ri, ci, e) => {
    el.data[ri][el.columns[ci]] = e.target.innerText;
    pushHistory();
};
const onTableHeaderBlur = (el, col, e) => {
    const newName = e.target.innerText.trim() || col;
    if (newName !== col) {
        const idx = el.columns.indexOf(col);
        if (idx > -1) {
            el.columns[idx] = newName;
            el.data.forEach((r) => {
                r[newName] = r[col];
                delete r[col];
            });
        }
    }
    pushHistory();
};
const addTableRow = (el) => {
    const r = {};
    el.columns.forEach((c) => (r[c] = ""));
    el.data.push(r);
    pushHistory();
};
const addTableColumn = (el) => {
    const n = `Col ${el.columns.length + 1}`;
    el.columns.push(n);
    el.data.forEach((r) => (r[n] = ""));
    pushHistory();
};
const removeTableRow = (el) => {
    if (el.data.length > 1) {
        el.data.pop();
        pushHistory();
    }
};
const removeTableColumn = (el) => {
    if (el.columns.length > 1) {
        const last = el.columns.pop();
        el.data.forEach((r) => delete r[last]);
        pushHistory();
    }
};
const onTocTitleBlur = (el, e) => {
    el.title = e.target.innerText;
    pushHistory();
};
const onTocItemBlur = (el, i, field, e) => {
    el.items[i][field] = e.target.innerText;
    pushHistory();
};
const addTocItem = (el) => {
    el.items.push({ label: `Section ${el.items.length + 1}`, page: 1 });
    pushHistory();
};
const onTimelineBlur = (el, i, field, e) => {
    el.items[i][field] = e.target.innerText;
    pushHistory();
};
const addTimelineItem = (el) => {
    el.items.push({
        label: "New Milestone",
        date: "2026",
        desc: "Description",
    });
    pushHistory();
};

const toggleTextStyle = (style) => {
    if (!selectedElement.value) return;
    const s = selectedElement.value.styles;
    if (style === "italic")
        s.fontStyle = s.fontStyle === "italic" ? "normal" : "italic";
    if (style === "underline")
        s.textDecoration =
            s.textDecoration === "underline" ? "none" : "underline";
    if (style === "line-through")
        s.textDecoration =
            s.textDecoration === "line-through" ? "none" : "line-through";
    if (style === "uppercase")
        s.textTransform =
            s.textTransform === "uppercase" ? "none" : "uppercase";
    pushHistory();
};

const applyCalloutStyle = () => {
    if (!selectedElement.value) return;
    const s =
        CALLOUT_PRESETS[selectedElement.value.calloutStyle] ||
        CALLOUT_PRESETS.info;
    Object.assign(selectedElement.value.styles, {
        backgroundColor: s.bg,
        borderColor: s.border,
        color: s.color,
    });
    if (!selectedElement.value.emoji) selectedElement.value.emoji = s.emoji;
    pushHistory();
};

// ─── Charts ───────────────────────────────────────────────────────────────────
const isChartType = (t) =>
    [
        "bar-chart",
        "line-chart",
        "pie-chart",
        "area-chart",
        "doughnut-chart",
        "radar-chart",
    ].includes(t);
const chartTypeMap = {
    "bar-chart": "bar",
    "line-chart": "line",
    "pie-chart": "pie",
    "area-chart": "line",
    "doughnut-chart": "doughnut",
    "radar-chart": "radar",
};

const initChart = (el) => {
    nextTick(() => {
        const canvas = document.getElementById("chart-" + el.id);
        if (!canvas) return;
        if (chartInstances.has(el.id)) {
            chartInstances.get(el.id).destroy();
            chartInstances.delete(el.id);
        }
        const type = chartTypeMap[el.type] || "bar";
        const colors = el.pieColors?.length
            ? el.pieColors
            : [
                  "#6366f1",
                  "#10b981",
                  "#f59e0b",
                  "#ef4444",
                  "#8b5cf6",
                  "#06b6d4",
                  "#f97316",
                  "#ec4899",
              ];
        const isArea = el.type === "area-chart";
        new Chart(canvas.getContext("2d"), {
            type,
            data: {
                labels: el.chartData?.labels || [],
                datasets: [
                    {
                        label:
                            el.chartDatasetLabel || el.chartTitle || "Dataset",
                        data: el.chartData?.values || [],
                        backgroundColor:
                            type === "pie" || type === "doughnut"
                                ? colors
                                : isArea
                                  ? (el.chartColor || rs.primary_color) + "30"
                                  : el.chartColor || rs.primary_color,
                        borderColor:
                            type === "pie" || type === "doughnut"
                                ? colors
                                : el.chartColor || rs.primary_color,
                        borderWidth: 2,
                        fill: isArea,
                        tension: el.type === "line-chart" || isArea ? 0.4 : 0,
                        pointBackgroundColor: el.chartColor || rs.primary_color,
                        pointRadius: type === "radar" ? 4 : 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position:
                            el.legendPosition === "none"
                                ? false
                                : el.legendPosition || "bottom",
                        display: el.legendPosition !== "none",
                        labels: {
                            padding: 12,
                            font: { size: 11 },
                            boxWidth: 12,
                        },
                    },
                    title: {
                        display: !!el.chartTitle,
                        text: el.chartTitle,
                        font: { size: 13, weight: "600" },
                    },
                },
                scales:
                    type === "pie" || type === "doughnut" || type === "radar"
                        ? {}
                        : {
                              x: {
                                  grid: { color: "#f1f5f9" },
                                  ticks: { font: { size: 11 } },
                              },
                              y: {
                                  grid: { color: "#f1f5f9" },
                                  ticks: { font: { size: 11 } },
                              },
                          },
            },
        });
        chartInstances.set(el.id, chart);
    });
};

const refreshChart = (el) => {
    if (el && isChartType(el.type)) initChart(el);
};
const onChartTypeChange = () =>
    nextTick(() => refreshChart(selectedElement.value));
const updateChartData = () => {
    if (!selectedElement.value || !isChartType(selectedElement.value.type))
        return;
    selectedElement.value.chartData = {
        labels: chartLabelsInput.value
            .split(",")
            .map((s) => s.trim())
            .filter(Boolean),
        values: chartValuesInput.value
            .split(",")
            .map((s) => parseFloat(s.trim()))
            .filter((n) => !isNaN(n)),
    };
    selectedElement.value.chartDatasetLabel = chartDatasetLabel.value;
    selectedElement.value.chartColor = chartColor.value;
    if (pieColorsInput.value)
        selectedElement.value.pieColors = pieColorsInput.value
            .split(",")
            .map((s) => s.trim());
    refreshChart(selectedElement.value);
    pushHistory();
};

// ─── Image upload ─────────────────────────────────────────────────────────────
const uploadImage = async (e, el) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith("image/")) {
        showToast("Only image files are allowed.", "error");
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        showToast("Image must be under 10 MB.", "error");
        return;
    }
    const localUrl = URL.createObjectURL(file);
    el.src = localUrl;
    el._uploading = true;
    try {
        const fd = new FormData();
        fd.append("image", file);
        const res = await axios.post("/api/upload-image", fd, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        el.src = res.data.url;
        URL.revokeObjectURL(localUrl);
        showToast("Image uploaded!");
        pushHistory();
    } catch {
        showToast("Upload failed — local preview only.", "error");
    } finally {
        el._uploading = false;
        if (e.target) e.target.value = "";
    }
};

// ─── Page management ──────────────────────────────────────────────────────────
const addPageAfter = (i) => {
    pages.value.splice(i + 1, 0, { id: uuidv4(), elements: [] });
    pushHistory();
};
const removePage = (i) => {
    if (pages.value.length <= 1) return;
    pages.value.splice(i, 1);
    if (selectedPageIndex.value >= pages.value.length)
        selectedPageIndex.value = pages.value.length - 1;
    pushHistory();
};
const duplicatePage = (i) => {
    const clone = JSON.parse(JSON.stringify(pages.value[i]));
    clone.id = uuidv4();
    clone.elements = clone.elements.map((el) => ({ ...el, id: uuidv4() }));
    pages.value.splice(i + 1, 0, clone);
    nextTick(() =>
        clone.elements.forEach((el) => {
            if (isChartType(el.type)) initChart(el);
        }),
    );
    pushHistory();
};

// ─── Layer order ──────────────────────────────────────────────────────────────
const bringForward = () => {
    if (!selectedElement.value) return;
    selectedElement.value.styles.zIndex =
        (selectedElement.value.styles.zIndex || 1) + 1;
    pushHistory();
};
const sendBackward = () => {
    if (!selectedElement.value) return;
    selectedElement.value.styles.zIndex = Math.max(
        1,
        (selectedElement.value.styles.zIndex || 1) - 1,
    );
    pushHistory();
};
const bringToFront = () => {
    if (!selectedElement.value) return;
    const maxZ = Math.max(
        ...pages.value[selectedPageIndex.value].elements.map(
            (e) => e.styles?.zIndex || 1,
        ),
    );
    selectedElement.value.styles.zIndex = maxZ + 1;
    pushHistory();
};
const sendToBack = () => {
    if (!selectedElement.value) return;
    const minZ = Math.min(
        ...pages.value[selectedPageIndex.value].elements.map(
            (e) => e.styles?.zIndex || 1,
        ),
    );
    selectedElement.value.styles.zIndex = Math.max(1, minZ - 1);
    pushHistory();
};

// ─── Alignment ────────────────────────────────────────────────────────────────
const alignElement = (dir) => {
    if (!selectedElement.value) return;
    const el = selectedElement.value;
    const pw = canvasDimensions.value.width,
        ph = canvasDimensions.value.height;
    const w = el.styles.width || 200,
        h = el.styles.height || 50;
    if (dir === "left") el.position.x = 0;
    if (dir === "right") el.position.x = pw - w;
    if (dir === "center-h") el.position.x = (pw - w) / 2;
    if (dir === "top") el.position.y = 0;
    if (dir === "bottom") el.position.y = ph - h;
    if (dir === "center-v") el.position.y = (ph - h) / 2;
    pushHistory();
};

// ─── History ──────────────────────────────────────────────────────────────────
const pushHistory = () => {
    const snap = JSON.stringify(pages.value);
    if (snap === history.value[historyIndex.value]) return;
    history.value = history.value.slice(0, historyIndex.value + 1);
    history.value.push(snap);
    historyIndex.value = history.value.length - 1;
    if (history.value.length > 100) {
        history.value.shift();
        historyIndex.value--;
    }
    autoSave();
};

const undo = () => {
    if (historyIndex.value <= 0) return;
    historyIndex.value--;
    pages.value = JSON.parse(history.value[historyIndex.value]);
    nextTick(reinitAllCharts);
};
const redo = () => {
    if (historyIndex.value >= history.value.length - 1) return;
    historyIndex.value++;
    pages.value = JSON.parse(history.value[historyIndex.value]);
    nextTick(reinitAllCharts);
};
const reinitAllCharts = () =>
    pages.value.forEach((p) =>
        p.elements.forEach((el) => {
            if (isChartType(el.type)) initChart(el);
        }),
    );

// ─── Save / Export ────────────────────────────────────────────────────────────
let saveTimeout = null;
const autoSave = () => {
    if (saveTimeout) clearTimeout(saveTimeout);
    saveTimeout = setTimeout(saveReport, 2500);
};

const saveReport = async () => {
    saveStatus.value = "saving";
    try {
        await axios.put(route("reports.update", props.report.slug), {
            title: form.title,
            content: pages.value,
            settings: rs,
        });
        saveStatus.value = "saved";
        setTimeout(() => {
            saveStatus.value = "idle";
        }, 2000);
    } catch {
        saveStatus.value = "idle";
        showToast("Save failed. Please try again.", "error");
    }
};

const previewReport = () =>
    window.open(route("reports.preview", props.report.slug), "_blank");

const exportAs = (fmt) => {
    showExportMenu.value = false;
    const routes = {
        pdf: route("reports.download", props.report.slug),
        xlsx: route("reports.export.excel", props.report.slug),
        csv: route("reports.export.csv", props.report.slug),
        png: route("reports.export.image", props.report.slug),
    };
    if (routes[fmt]) window.open(routes[fmt], "_blank");
};

// ─── UI helpers ───────────────────────────────────────────────────────────────
const isTextLike = (type) =>
    [
        "text",
        "heading",
        "subheading",
        "quote",
        "blockquote",
        "link",
        "badge",
        "date",
        "pagenum",
        "highlight",
    ].includes(type);

const getElementLabel = (type) => {
    const map = {
        "bar-chart": "Bar Chart",
        "line-chart": "Line Chart",
        "pie-chart": "Pie Chart",
        "doughnut-chart": "Doughnut",
        "area-chart": "Area Chart",
        "radar-chart": "Radar",
        pagenum: "Page #",
        blockquote: "Block Quote",
        highlight: "Highlight",
        toc: "Table of Contents",
        timeline: "Timeline",
        "stat-row": "Stats Row",
        "social-card": "Profile Card",
        "price-card": "Price Card",
        "double-arrow": "Double Arrow",
        "circular-progress": "Circular Progress",
    };
    return (
        map[type] ||
        type.charAt(0).toUpperCase() + type.slice(1).replace(/-/g, " ")
    );
};

const toggleCategory = (name) => {
    const i = collapsedCategories.value.indexOf(name);
    i > -1
        ? collapsedCategories.value.splice(i, 1)
        : collapsedCategories.value.push(name);
};
const togglePropSection = (name) => {
    const i = collapsedSections.value.indexOf(name);
    i > -1
        ? collapsedSections.value.splice(i, 1)
        : collapsedSections.value.push(name);
};

const fitToScreen = () => {
    if (!canvasArea.value) return;
    const w = canvasArea.value.clientWidth - 80;
    zoom.value = Math.min(
        150,
        Math.max(25, Math.floor((w / canvasDimensions.value.width) * 100)),
    );
};

const toggleFullscreen = () => {
    if (!document.fullscreenElement)
        document.documentElement.requestFullscreen?.().catch(() => {});
    else document.exitFullscreen?.();
};

const showToast = (message, type = "success") => {
    toast.message = message;
    toast.type = type;
    toast.show = true;
    setTimeout(() => {
        toast.show = false;
    }, 3000);
};

// ─── Keyboard shortcuts ───────────────────────────────────────────────────────
const handleKeyDown = (e) => {
    if (
        ["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName) ||
        e.target.contentEditable === "true"
    )
        return;
    if ((e.ctrlKey || e.metaKey) && e.key === "z") {
        e.preventDefault();
        undo();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === "y") {
        e.preventDefault();
        redo();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === "s") {
        e.preventDefault();
        saveReport();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === "d") {
        e.preventDefault();
        if (selectedElement.value)
            duplicateElement(selectedPageIndex.value, selectedElement.value);
    }
    if (e.key === "D" && !e.ctrlKey && !e.metaKey)
        dragMode.value = !dragMode.value;
    if (e.key === "G" && !e.ctrlKey && !e.metaKey)
        showGrid.value = !showGrid.value;
    if (e.key === "R" && !e.ctrlKey && !e.metaKey)
        showRulers.value = !showRulers.value;
    if (e.key === "F" && !e.ctrlKey && !e.metaKey) toggleFullscreen();
    if (e.key === "Escape") {
        deselectAll();
        contextMenu.show = false;
    }
    if (e.key === "Delete" || e.key === "Backspace") {
        if (selectedElement.value) deleteSelectedElement();
    }
    if (
        selectedElement.value &&
        ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].includes(e.key)
    ) {
        e.preventDefault();
        const step = e.shiftKey ? 10 : 1;
        if (e.key === "ArrowUp") selectedElement.value.position.y -= step;
        if (e.key === "ArrowDown") selectedElement.value.position.y += step;
        if (e.key === "ArrowLeft") selectedElement.value.position.x -= step;
        if (e.key === "ArrowRight") selectedElement.value.position.x += step;
        pushHistory();
    }
    if (e.key === "=" && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        zoom.value = Math.min(200, zoom.value + 10);
    }
    if (e.key === "-" && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        zoom.value = Math.max(25, zoom.value - 10);
    }
    if (e.key === "0" && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        zoom.value = 100;
    }
    if (selectedElement.value) {
        if (e.key === "[" && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            sendBackward();
        }
        if (e.key === "]" && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            bringForward();
        }
    }
};

const handleContextMenu = (e) => {
    const isCanvas = e.target.closest(".canvas-page");
    if (isCanvas) e.preventDefault();
};

const handleOutsideClick = (e) => {
    if (exportMenuRef.value && !exportMenuRef.value.contains(e.target))
        showExportMenu.value = false;
    if (contextMenu.show && !e.target.closest(".context-menu"))
        contextMenu.show = false;
};

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
    document.addEventListener("click", handleOutsideClick);
    document.addEventListener("contextmenu", handleContextMenu);
    nextTick(reinitAllCharts);
    dark.value = localStorage.getItem("rb-dark") === "1";
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeyDown);
    document.removeEventListener("click", handleOutsideClick);
    document.removeEventListener("contextmenu", handleContextMenu);
    chartInstances.forEach((c) => c.destroy());
    if (saveTimeout) clearTimeout(saveTimeout);
});

watch(dark, (v) => localStorage.setItem("rb-dark", v ? "1" : "0"));
</script>

<style scoped>
/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.dropdown-enter-active {
    transition: all 0.15s ease-out;
}
.dropdown-leave-active {
    transition: all 0.1s ease-in;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.97);
}
.toast-enter-active {
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-leave-active {
    transition: all 0.2s ease-in;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(12px);
}

/* Base */
.prop-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
    color: #94a3b8;
}
.canvas-page {
    position: relative;
}
.element-wrapper {
    user-select: none;
}
.layer-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 8px;
    font-size: 10px;
    font-weight: 700;
    border: 1px solid;
    border-radius: 12px;
    transition: all 0.15s;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 99px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>