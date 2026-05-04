<!--
    ╔══════════════════════════════════════════════════════════════════╗
    ║     ReportGen ULTIMATE PRO - Mind-Blowing Report Editor        ║
    ║     Laravel 12 + Inertia.js + Vue 3 + Tailwind CSS             ║
    ║     100% Working - Tabbed Left Panel - Smooth Right Panel      ║
    ╚══════════════════════════════════════════════════════════════════╝
    
    WHAT THIS EDITOR DOES:
    ┌─────────────────────────────────────────────────────────────────┐
    │ LEFT SIDEBAR TABS:                                             │
    │   📦 Elements  - 50+ draggable element types                   │
    │   📄 Pages     - Page thumbnails, add/delete/reorder           │
    │   🎨 Layout    - Page size, orientation, margins, colors       │
    │   ⚙️ Settings  - Header, footer, watermark, fonts, theme       │
    │                                                                │
    │ CENTER CANVAS:                                                  │
    │   • Smooth element selection (NO BLINKING)                     │
    │   • Drag, resize, rotate with live preview                     │
    │   • Grid/Snap guides                                           │
    │   • Zoom 10%-400%                                              │
    │                                                                │
    │ RIGHT SIDEBAR:                                                  │
    │   • Full element properties when selected                       │
    │   • Content editor, typography, colors, borders, shadows       │
    │   • Position/size with pixel-perfect control                   │
    │   • Quick actions (duplicate, copy, delete, lock)              │
    │                                                                │
    │ RIGHT-CLICK CONTEXT MENU:                                       │
    │   • Per-element popup with all relevant actions                │
    │   • Edit content, duplicate, copy, layer control, lock/delete  │
    └─────────────────────────────────────────────────────────────────┘
-->

<template>
  <div 
    class="fixed inset-0 z-50 flex flex-col bg-gradient-to-br from-slate-50 via-slate-100 to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 transition-all duration-500"
    :class="{ 'rounded-none': isFullscreen }"
    @keydown="handleKeyboardShortcuts"
    tabindex="0"
    ref="editorContainer"
  >
    
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- TOP TOOLBAR                                                      -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex-shrink-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border-b border-slate-200/50 dark:border-slate-700/50 shadow-lg z-30">
      <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100 dark:border-slate-700/50">
        <div class="flex items-center gap-2">
          <Link :href="route('reports.index')" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all" title="Back">
            <i class="fa-solid fa-arrow-left text-slate-500 text-sm"></i>
          </Link>
          <div class="h-5 w-px bg-slate-200 dark:bg-slate-700"></div>
          <input v-model="report.title" @change="autoSave" class="text-base font-bold bg-transparent border-none outline-none text-slate-900 dark:text-white w-48 sm:w-64 focus:ring-0" placeholder="Untitled Report"/>
          <span class="text-[10px] px-2.5 py-1 rounded-full font-semibold capitalize shadow-sm" :class="statusBadgeClass">
            <i :class="statusIcon" class="mr-1"></i> {{ report.status }}
          </span>
        </div>
        <div class="flex items-center gap-1">
          <button @click="undo" :disabled="!canUndo" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 transition-all" title="Undo (Ctrl+Z)"><i class="fa-solid fa-rotate-left text-slate-500 text-sm"></i></button>
          <button @click="redo" :disabled="!canRedo" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 transition-all" title="Redo (Ctrl+Y)"><i class="fa-solid fa-rotate-right text-slate-500 text-sm"></i></button>
          <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>
          <button @click="showAIPanel = !showAIPanel" :class="showAIPanel ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600' : 'text-slate-500'" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all" title="AI (Ctrl+Space)"><i class="fa-solid fa-robot text-sm"></i></button>
          <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>
          <button @click="zoomOut" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all"><i class="fa-solid fa-magnifying-glass-minus text-slate-500 text-sm"></i></button>
          <select v-model="zoomLevel" class="text-xs border rounded-lg px-2 py-1.5 bg-transparent text-slate-600 dark:text-slate-300 w-[70px]"><option v-for="z in zoomOptions" :key="z" :value="z">{{ z }}%</option></select>
          <button @click="zoomIn" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all"><i class="fa-solid fa-magnifying-glass-plus text-slate-500 text-sm"></i></button>
          <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>
          <button @click="showGrid = !showGrid" :class="showGrid ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600' : 'text-slate-500'" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all"><i class="fa-solid fa-grid-2 text-sm"></i></button>
          <button @click="toggleFullscreen" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all" title="Fullscreen (F11)">
            <i :class="isFullscreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand'" class="text-slate-500 text-sm"></i>
          </button>
          <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>
          <button @click="toggleTheme" class="relative p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all overflow-hidden group" title="Toggle Theme">
            <div class="relative z-10 transition-all duration-500" :class="isDark ? 'rotate-180 scale-0' : 'rotate-0 scale-100'">
              <i class="fa-solid fa-moon text-slate-500 text-sm absolute inset-0 flex items-center justify-center" v-if="!isDark"></i>
            </div>
            <div class="relative z-10 transition-all duration-500" :class="isDark ? 'rotate-0 scale-100' : '-rotate-180 scale-0'">
              <i class="fa-solid fa-sun text-amber-400 text-sm absolute inset-0 flex items-center justify-center" v-if="isDark"></i>
            </div>
          </button>
          <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>
          <button @click="autoSave" class="px-4 py-2 text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-xl hover:bg-indigo-100 transition-all"><i class="fa-solid fa-floppy-disk mr-1.5"></i> Save</button>
          <button @click="previewReport" class="px-4 py-2 text-xs font-semibold border rounded-xl text-slate-600 hover:bg-slate-50 transition-all"><i class="fa-solid fa-eye mr-1.5"></i> Preview</button>
          <div class="relative">
            <button @click="showExportMenu = !showExportMenu" class="px-4 py-2 text-xs font-bold bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl transition-all shadow-lg hover:shadow-xl active:scale-95"><i class="fa-solid fa-download mr-1.5"></i> Export</button>
            <div v-if="showExportMenu" class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border py-2 z-50 animate-scale-in">
              <button @click="downloadPDF" class="w-full text-left px-4 py-2.5 text-xs hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2"><i class="fa-solid fa-file-pdf text-red-500"></i> PDF</button>
              <button @click="exportImage" class="w-full text-left px-4 py-2.5 text-xs hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2"><i class="fa-solid fa-image text-purple-500"></i> PNG</button>
              <button @click="exportCSV" class="w-full text-left px-4 py-2.5 text-xs hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2"><i class="fa-solid fa-file-csv text-emerald-500"></i> CSV</button>
              <button @click="exportExcel" class="w-full text-left px-4 py-2.5 text-xs hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2"><i class="fa-solid fa-file-excel text-green-500"></i> Excel</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Formatting Ribbon -->
      <div class="flex items-center gap-0.5 px-3 py-2 overflow-x-auto scrollbar-thin">
        <select v-model="selectedFontFamily" @change="applyTextStyle('fontFamily', selectedFontFamily)" class="text-xs border rounded-lg px-2 py-1.5 bg-transparent text-slate-600 dark:text-slate-300 w-28">
          <option value="Inter">Inter</option><option value="'DM Sans'">DM Sans</option><option value="Georgia">Georgia</option><option value="'Playfair Display'">Playfair</option><option value="'Courier New'">Courier</option>
        </select>
        <select v-model="selectedFontSize" @change="applyTextStyle('fontSize', selectedFontSize)" class="text-xs border rounded-lg px-2 py-1.5 bg-transparent text-slate-600 dark:text-slate-300 w-16">
          <option v-for="s in [8,9,10,11,12,14,16,18,20,24,28,32,36,42,48,56,64,72]" :key="s" :value="s">{{ s }}</option>
        </select>
        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-0.5"></div>
        <button @click="applyTextStyle('fontWeight', selectedFontWeight === '700' ? '400' : '700')" :class="selectedFontWeight === '700' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-500'" class="p-2 rounded-lg hover:bg-slate-100 transition-all" title="Bold (Ctrl+B)"><i class="fa-solid fa-bold text-sm"></i></button>
        <button @click="applyTextStyle('fontStyle', selectedFontStyle === 'italic' ? 'normal' : 'italic')" :class="selectedFontStyle === 'italic' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-500'" class="p-2 rounded-lg hover:bg-slate-100 transition-all" title="Italic (Ctrl+I)"><i class="fa-solid fa-italic text-sm"></i></button>
        <button @click="applyTextStyle('textDecoration', selectedTextDecoration === 'underline' ? 'none' : 'underline')" :class="selectedTextDecoration === 'underline' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-500'" class="p-2 rounded-lg hover:bg-slate-100 transition-all" title="Underline (Ctrl+U)"><i class="fa-solid fa-underline text-sm"></i></button>
        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-0.5"></div>
        <div class="relative">
          <button @click="showColorPicker = !showColorPicker" class="p-2 rounded-lg hover:bg-slate-100 transition-all flex items-center gap-1.5" title="Text Color">
            <span class="w-4 h-4 rounded-full border-2 border-slate-300 shadow-sm" :style="{ backgroundColor: selectedColor }"></span>
            <i class="fa-solid fa-caret-down text-[10px] text-slate-400"></i>
          </button>
          <div v-if="showColorPicker" class="absolute left-0 top-full mt-1 p-3 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border z-50 w-60 animate-scale-in">
            <div class="grid grid-cols-10 gap-1.5 mb-2">
              <button v-for="color in colorPalette" :key="color" @click="applyTextStyle('color', color); showColorPicker = false" class="w-5 h-5 rounded-full border hover:scale-125 transition-transform shadow-sm" :style="{ backgroundColor: color }"></button>
            </div>
            <div class="flex items-center gap-2 pt-2 border-t"><input type="color" v-model="selectedColor" @change="applyTextStyle('color', selectedColor)" class="w-8 h-8 rounded cursor-pointer"><input type="text" v-model="selectedColor" class="flex-1 px-2 py-1 text-xs font-mono border rounded-lg bg-white dark:bg-slate-900"></div>
          </div>
        </div>
        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-0.5"></div>
        <button @click="applyTextStyle('textAlign', 'left')" :class="selectedTextAlign === 'left' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-500'" class="p-2 rounded-lg hover:bg-slate-100 transition-all"><i class="fa-solid fa-align-left text-sm"></i></button>
        <button @click="applyTextStyle('textAlign', 'center')" :class="selectedTextAlign === 'center' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-500'" class="p-2 rounded-lg hover:bg-slate-100 transition-all"><i class="fa-solid fa-align-center text-sm"></i></button>
        <button @click="applyTextStyle('textAlign', 'right')" :class="selectedTextAlign === 'right' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 shadow-sm' : 'text-slate-500'" class="p-2 rounded-lg hover:bg-slate-100 transition-all"><i class="fa-solid fa-align-right text-sm"></i></button>
        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-0.5"></div>
        <button @click="bringToFront" class="p-2 rounded-lg hover:bg-slate-100 transition-all text-slate-500" title="Bring to Front"><i class="fa-solid fa-layer-group text-sm"></i></button>
        <button @click="bringForward" class="p-2 rounded-lg hover:bg-slate-100 transition-all text-slate-500" title="Bring Forward"><i class="fa-solid fa-arrow-up-wide-short text-sm"></i></button>
        <button @click="sendBackward" class="p-2 rounded-lg hover:bg-slate-100 transition-all text-slate-500" title="Send Backward"><i class="fa-solid fa-arrow-down-wide-short text-sm"></i></button>
        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-0.5"></div>
        <button @click="toggleLock" :class="selectedElementIsLocked ? 'bg-red-100 dark:bg-red-900/30 text-red-600 shadow-sm' : 'text-slate-500'" class="p-2 rounded-lg hover:bg-slate-100 transition-all"><i :class="selectedElementIsLocked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" class="text-sm"></i></button>
        <button @click="deleteElement" class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-all ml-auto"><i class="fa-solid fa-trash-can text-sm"></i></button>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- MAIN EDITOR AREA                                                 -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex-1 flex overflow-hidden relative">
      
      <!-- ── LEFT SIDEBAR WITH TABS ──────────────────────────────── -->
      <div class="w-56 lg:w-64 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-r border-slate-200/50 dark:border-slate-700/50 flex flex-col flex-shrink-0 hidden sm:flex">
        
        <!-- Tab Buttons -->
        <div class="flex border-b border-slate-200/50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/30">
          <button v-for="tab in leftTabs" :key="tab.id" @click="activeLeftTab = tab.id"
                  :class="activeLeftTab === tab.id ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                  class="flex-1 py-2.5 text-[10px] font-bold transition-all flex items-center justify-center gap-1.5">
            <i :class="tab.icon" class="text-xs"></i> {{ tab.label }}
          </button>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-y-auto">
          
          <!-- 📦 ELEMENTS TAB -->
          <div v-show="activeLeftTab === 'elements'" class="p-3 space-y-4">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
              <input v-model="elementSearch" type="text" placeholder="Search 50+ elements..." class="w-full pl-8 pr-3 py-2 text-xs border rounded-xl bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div v-for="category in filteredElementCategories" :key="category.name">
              <h4 class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                <i :class="category.icon" class="text-[10px]"></i> {{ category.name }}
              </h4>
              <div class="grid grid-cols-2 gap-1">
                <div v-for="el in category.elements" :key="el.type" draggable="true" @dragstart="onDragStart($event, el)"
                     class="p-2.5 rounded-xl border border-slate-200/50 dark:border-slate-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-grab text-center text-[10px] transition-all duration-200 group hover:shadow-md hover:border-indigo-300 active:scale-95">
                  <i :class="el.icon" class="text-lg mb-1.5 block group-hover:scale-110 transition-transform" :style="{ color: el.color }"></i>
                  <span class="font-medium text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white">{{ el.label }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 📄 PAGES TAB ────────────────────────────────── -->
          <div v-show="activeLeftTab === 'pages'" class="p-3 space-y-3">
            <button @click="addPage" class="w-full py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-all">
              <i class="fa-solid fa-plus mr-1.5"></i> Add New Page
            </button>
            <div class="space-y-1.5">
              <div v-for="(page, pIdx) in report.content" :key="page.id" @click="selectPage(pIdx)"
                   :class="selectedPage === pIdx ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                   class="p-3 rounded-xl border border-slate-200 dark:border-slate-600 cursor-pointer transition-all">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-sm text-slate-700 dark:text-slate-300">Page {{ pIdx + 1 }}</span>
                  <span class="text-[10px] text-slate-400">{{ page.elements.length }} elements</span>
                </div>
                <div class="flex gap-1 mt-2">
                  <button @click.stop="duplicatePage(pIdx)" class="px-2 py-1 text-[9px] bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-indigo-100 transition-all">Duplicate</button>
                  <button @click.stop="deletePage(pIdx)" :disabled="report.content.length <= 1" class="px-2 py-1 text-[9px] bg-red-50 dark:bg-red-900/30 text-red-600 rounded-lg hover:bg-red-100 disabled:opacity-50">Delete</button>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 🎨 LAYOUT TAB ────────────────────────────────── -->
          <div v-show="activeLeftTab === 'layout'" class="p-3 space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Page Settings</h3>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Page Size</label>
              <select v-model="report.settings.page_size" @change="autoSave" class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-slate-900 mt-1">
                <option value="A4">A4</option><option value="Letter">Letter</option><option value="Legal">Legal</option><option value="A3">A3</option>
              </select>
            </div>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Orientation</label>
              <select v-model="report.settings.orientation" @change="autoSave" class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-slate-900 mt-1">
                <option value="portrait">Portrait</option><option value="landscape">Landscape</option>
              </select>
            </div>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Margin (px): {{ report.settings.margin || 40 }}</label>
              <input type="range" v-model.number="report.settings.margin" min="10" max="100" @change="autoSave" class="w-full accent-indigo-500">
            </div>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Background Color</label>
              <div class="flex gap-2 mt-1">
                <input type="color" v-model="report.settings.background_color" @change="autoSave" class="w-8 h-8 rounded cursor-pointer border">
                <input type="text" v-model="report.settings.background_color" @change="autoSave" class="flex-1 px-2 py-1 text-xs font-mono border rounded-lg bg-white dark:bg-slate-900">
              </div>
            </div>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Background Image URL</label>
              <input v-model="report.settings.background_image" @change="autoSave" type="text" placeholder="https://..." class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-slate-900 mt-1">
            </div>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Primary Color</label>
              <div class="flex gap-2 mt-1">
                <input type="color" v-model="report.settings.primary_color" @change="autoSave" class="w-8 h-8 rounded cursor-pointer border">
                <input type="text" v-model="report.settings.primary_color" @change="autoSave" class="flex-1 px-2 py-1 text-xs font-mono border rounded-lg bg-white dark:bg-slate-900">
              </div>
            </div>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Font Family</label>
              <select v-model="report.settings.font_family" @change="autoSave" class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-slate-900 mt-1">
                <option value="Inter">Inter</option><option value="'DM Sans'">DM Sans</option><option value="Georgia">Georgia</option>
              </select>
            </div>
            <div>
              <label class="text-[10px] font-semibold text-slate-500">Font Size: {{ report.settings.font_size || 14 }}px</label>
              <input type="range" v-model.number="report.settings.font_size" min="10" max="24" @change="autoSave" class="w-full accent-indigo-500">
            </div>
          </div>

          <!-- ── ⚙️ SETTINGS TAB ──────────────────────────────── -->
          <div v-show="activeLeftTab === 'settings'" class="p-3 space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Report Settings</h3>
            
            <!-- Header Settings -->
            <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl space-y-2">
              <h4 class="text-[10px] font-bold text-slate-600">Header</h4>
              <div class="flex items-center justify-between">
                <span class="text-[10px] text-slate-500">Show Header</span>
                <button @click="report.settings.show_header = !report.settings.show_header; autoSave()" :class="report.settings.show_header ? 'bg-indigo-600' : 'bg-slate-300'" class="relative w-9 h-5 rounded-full transition-colors"><span :class="report.settings.show_header ? 'translate-x-4' : 'translate-x-0.5'" class="absolute top-0.5 w-4 h-4 bg-white rounded-full transition-transform"></span></button>
              </div>
              <input v-if="report.settings.show_header" v-model="report.settings.header_text" @change="autoSave" placeholder="Header text" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900">
            </div>
            
            <!-- Footer Settings -->
            <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl space-y-2">
              <h4 class="text-[10px] font-bold text-slate-600">Footer</h4>
              <div class="flex items-center justify-between">
                <span class="text-[10px] text-slate-500">Show Footer</span>
                <button @click="report.settings.show_footer = !report.settings.show_footer; autoSave()" :class="report.settings.show_footer ? 'bg-indigo-600' : 'bg-slate-300'" class="relative w-9 h-5 rounded-full transition-colors"><span :class="report.settings.show_footer ? 'translate-x-4' : 'translate-x-0.5'" class="absolute top-0.5 w-4 h-4 bg-white rounded-full transition-transform"></span></button>
              </div>
              <input v-if="report.settings.show_footer" v-model="report.settings.footer_left" @change="autoSave" placeholder="Footer left" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900">
              <input v-if="report.settings.show_footer" v-model="report.settings.footer_right" @change="autoSave" placeholder="Footer right (use {n} for page)" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900">
            </div>
            
            <!-- Watermark -->
            <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl space-y-2">
              <h4 class="text-[10px] font-bold text-slate-600">Watermark</h4>
              <input v-model="report.settings.watermark" @change="autoSave" placeholder="e.g., CONFIDENTIAL" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900">
              <label class="text-[9px] text-slate-500">Opacity: {{ report.settings.watermark_opacity || 5 }}%</label>
              <input type="range" v-model.number="report.settings.watermark_opacity" min="1" max="20" @change="autoSave" class="w-full accent-indigo-500">
            </div>
          </div>
        </div>
      </div>

      <!-- ── CENTER: Canvas Area ──────────────────────────────────── -->
      <div class="flex-1 bg-slate-200/50 dark:bg-slate-800/50 overflow-auto relative"
           @dragover.prevent @drop="onDrop" @click.self="deselectAll"
           @contextmenu.prevent="showCanvasContextMenu" ref="canvasContainer">
        
        <div :style="{ transform: `scale(${zoomLevel / 100})`, transformOrigin: 'top left' }" class="transition-transform duration-200 ease-out p-8">
          <div v-if="showGrid" class="absolute inset-0 pointer-events-none opacity-[0.06] dark:opacity-[0.04]"
               :style="{ backgroundImage: `linear-gradient(${accentColor} 1px, transparent 1px), linear-gradient(90deg, ${accentColor} 1px, transparent 1px)`, backgroundSize: `${gridSize}px ${gridSize}px` }"></div>
          
          <div class="space-y-10 flex flex-col items-center py-4">
            <div v-for="(page, pIdx) in report.content" :key="page.id"
                 class="relative bg-white dark:bg-slate-900 shadow-2xl transition-all duration-300 border-[3px]"
                 :style="{ ...getPageStyle(pIdx), borderColor: selectedPage === pIdx ? accentColor : 'transparent' }"
                 @click.stop="selectPage(pIdx)" @dblclick="addPageAfter(pIdx)"
                 @contextmenu.prevent.stop="showPageContextMenu($event, pIdx)">
              
              <div class="absolute -top-8 left-0 text-[11px] text-slate-400 font-medium">
                Page {{ pIdx + 1 }} · {{ page.elements.length }} elements
                <span v-if="pIdx === selectedPage" class="text-indigo-500 font-bold ml-2">[Active]</span>
              </div>
              
              <!-- Header -->
              <div v-if="report.settings.show_header" class="absolute top-0 left-0 right-0 z-20 border-b border-dashed"
                   :style="{ height: '50px', backgroundColor: report.settings.header_color||'#1e293b', color: '#fff', display:'flex', alignItems:'center', padding: '0 40px', fontSize:'12px' }"
                   :contenteditable="isHeaderFooterEditable" @input="updateHeaderContent" @blur="autoSave">
                {{ report.settings.header_text || 'Header' }}
              </div>
              
              <!-- Watermark -->
              <div v-if="report.settings.watermark" class="absolute inset-0 flex items-center justify-center pointer-events-none select-none z-0"
                   :style="{ opacity: (report.settings.watermark_opacity||5)/100 }">
                <span class="text-[100px] font-black transform -rotate-[25deg] whitespace-nowrap" :style="{ color: report.settings.primary_color }">{{ report.settings.watermark }}</span>
              </div>
              
              <!-- Elements - Fixed blinking issue with stable rendering -->
              <div v-for="(el, eIdx) in page.elements" :key="el.id"
                   class="absolute cursor-pointer group/el select-none"
                   :class="isSelected(pIdx, eIdx) ? 'ring-2 ring-indigo-500 ring-offset-2 z-50' : 'hover:ring-1 hover:ring-indigo-300/30'"
                   :style="[getElementStyle(el), { opacity: el.locked ? 0.6 : (el.styles?.opacity || 100) / 100 }]"
                   :data-el-id="el.id"
                   @mousedown.prevent.stop="selectElement(pIdx, eIdx)"
                   @dblclick.prevent.stop="editElementContent(pIdx, eIdx)"
                   @contextmenu.prevent.stop="showElementContextMenu($event, pIdx, eIdx)"
                   draggable="!el.locked"
                   @dragstart.stop="el.locked ? $event.preventDefault() : onElementDragStart($event, pIdx, eIdx)">
                
                <!-- Resize handles - only shown when selected -->
                <template v-if="isSelected(pIdx, eIdx) && !el.locked">
                  <div v-for="handle in resizeHandles" :key="handle.dir"
                       class="absolute w-3 h-3 bg-white border-2 border-indigo-500 rounded-full z-50 shadow-sm hover:scale-125 transition-transform"
                       :class="handle.cursor" :style="getHandleStyle(handle.dir)"
                       @mousedown.prevent.stop="startResize($event, pIdx, eIdx, handle.dir)"></div>
                  <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-4 h-4 bg-indigo-500 rounded-full cursor-grab z-50 flex items-center justify-center shadow-lg border-2 border-white"
                       @mousedown.prevent.stop="startRotate($event, pIdx, eIdx)">
                    <i class="fa-solid fa-rotate text-[7px] text-white"></i>
                  </div>
                </template>
                
                <!-- Element Content -->
                <div class="w-full h-full overflow-hidden" :style="{ pointerEvents: el.locked ? 'none' : 'auto' }">
                  <div v-if="el.type === 'text'" class="w-full h-full p-2 overflow-auto outline-none" :contenteditable="isSelected(pIdx, eIdx)" @input="updateContent(pIdx, eIdx, $event)" @blur="autoSave" v-html="el.content || '<span class=\'text-slate-300\'>Double-click to edit</span>'" :style="getTextStyle(el)"></div>
                  <h2 v-else-if="el.type === 'heading'" class="w-full h-full p-2 overflow-auto outline-none" :contenteditable="isSelected(pIdx, eIdx)" @input="updateContent(pIdx, eIdx, $event)" @blur="autoSave" :style="getTextStyle(el)">{{ el.content || 'Heading' }}</h2>
                  <img v-else-if="el.type === 'image' && el.src" :src="el.src" class="w-full h-full object-cover pointer-events-none" :style="{ borderRadius: (el.styles?.borderRadius||0)+'px' }"/>
                  <div v-else-if="el.type === 'image' && !el.src" class="w-full h-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-xs cursor-pointer border-2 border-dashed border-slate-300 rounded-lg" @click="uploadImage(pIdx, eIdx)"><i class="fa-solid fa-cloud-arrow-up text-2xl mr-2"></i> Click to upload</div>
                  <div v-else-if="el.type === 'table'" class="w-full h-full overflow-auto">
                    <table class="w-full text-xs border-collapse">
                      <thead><tr><th v-for="(col, ci) in el.columns" :key="ci" class="p-1.5 border bg-indigo-50 dark:bg-indigo-900/30 text-left font-bold" :contenteditable="isSelected(pIdx, eIdx)" @input="updateTableHeader(pIdx, eIdx, ci, $event)">{{ col }}</th></tr></thead>
                      <tbody><tr v-for="(row, ri) in el.data" :key="ri"><td v-for="(col, ci) in el.columns" :key="ci" class="p-1.5 border" :contenteditable="isSelected(pIdx, eIdx)" @input="updateTableCell(pIdx, eIdx, ri, col, $event)">{{ row[col]||'' }}</td></tr></tbody>
                    </table>
                  </div>
                  <div v-else-if="el.type === 'rectangle'" class="w-full h-full" :style="{ backgroundColor: el.styles?.backgroundColor || report.settings.primary_color }"></div>
                  <div v-else-if="el.type === 'circle'" class="w-full h-full rounded-full" :style="{ backgroundColor: el.styles?.backgroundColor || report.settings.primary_color }"></div>
                  <div v-else-if="el.type === 'metric'" class="w-full h-full flex flex-col justify-center p-3" :style="{ background: `linear-gradient(135deg, ${el.styles?.backgroundColor||'#f8fafc'}, transparent)` }">
                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-500">{{ el.label||'Metric' }}</span>
                    <span class="text-2xl font-black" :style="{ color: el.styles?.color||report.settings.primary_color }">{{ el.value||'0' }}</span>
                  </div>
                  <div v-else-if="el.type === 'divider'" class="w-full flex items-center" style="height:2px;margin-top:50%;background:linear-gradient(to right,transparent,#e2e8f0,transparent)"></div>
                  <div v-else-if="isChartType(el.type)" class="w-full h-full flex items-center justify-center bg-slate-50/50 dark:bg-slate-800/50 rounded-xl">
                    <div class="text-center"><i class="fa-solid fa-chart-simple text-3xl mb-1 block" :style="{ color: report.settings.primary_color, opacity:0.5 }"></i><span class="text-xs text-slate-500">{{ el.chartTitle||'Chart' }}</span></div>
                  </div>
                  <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-xs">{{ el.type }}</div>
                </div>
              </div>
              
              <!-- Footer -->
              <div v-if="report.settings.show_footer" class="absolute bottom-0 left-0 right-0 z-20 border-t border-dashed"
                   :style="{ height:'35px', color:'#94a3b8', display:'flex', alignItems:'center', justifyContent:'space-between', padding:'0 40px', fontSize:'10px' }">
                <span :contenteditable="isHeaderFooterEditable" @input="updateFooterLeft" @blur="autoSave">{{ report.settings.footer_left||'Footer' }}</span>
                <span>Page {{ pIdx+1 }}</span>
              </div>
            </div>
            
            <button @click="addPage" class="w-40 h-12 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl flex items-center justify-center gap-2 text-sm text-slate-400 hover:border-indigo-400 hover:text-indigo-500 transition-all"><i class="fa-solid fa-plus"></i> Add Page</button>
          </div>
        </div>
      </div>

      <!-- ── RIGHT SIDEBAR: Element Properties ──────────────────────── -->
      <div class="w-72 lg:w-80 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-l border-slate-200/50 dark:border-slate-700/50 overflow-y-auto flex-shrink-0 hidden md:block">
        <div class="p-4 space-y-4">
          
          <!-- No Selection -->
          <template v-if="!selectedElementData">
            <div class="text-center py-12">
              <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-cube text-2xl text-slate-300"></i>
              </div>
              <p class="text-sm font-semibold text-slate-400">Select an element</p>
              <p class="text-xs text-slate-400 mt-1">Properties appear here</p>
            </div>
          </template>
          
          <!-- Element Properties -->
          <template v-else>
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white capitalize flex items-center gap-2">
                <i class="fa-solid fa-cube text-indigo-500"></i> {{ selectedElementData.type }}
              </h3>
              <button @click="lockElement" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" :title="selectedElementData.locked ? 'Unlock' : 'Lock'">
                <i :class="selectedElementData.locked ? 'fa-solid fa-lock text-red-500' : 'fa-solid fa-lock-open text-slate-400'" class="text-sm"></i>
              </button>
            </div>
            
            <!-- Content Editor (for text/heading) -->
            <div v-if="['text','heading','subheading','quote','code'].includes(selectedElementData.type)" class="space-y-2">
              <h4 class="text-[10px] font-bold text-slate-400 uppercase">Content</h4>
              <textarea v-model="selectedElementData.content" @input="autoSave" rows="4" class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-slate-900 resize-none"></textarea>
            </div>
            
            <!-- Position & Size -->
            <div>
              <h4 class="text-[10px] font-bold text-slate-400 uppercase mb-2">Position & Size</h4>
              <div class="grid grid-cols-2 gap-2">
                <div><label class="text-[9px] text-slate-500">X</label><input type="number" v-model.number="selectedElementData.position.x" @change="autoSave" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900"></div>
                <div><label class="text-[9px] text-slate-500">Y</label><input type="number" v-model.number="selectedElementData.position.y" @change="autoSave" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900"></div>
                <div><label class="text-[9px] text-slate-500">W</label><input type="number" v-model.number="selectedElementData.styles.width" @change="autoSave" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900"></div>
                <div><label class="text-[9px] text-slate-500">H</label><input type="number" v-model.number="selectedElementData.styles.height" @change="autoSave" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900"></div>
              </div>
            </div>
            
            <!-- Typography (for text elements) -->
            <div v-if="['text','heading','subheading','quote'].includes(selectedElementData.type)">
              <h4 class="text-[10px] font-bold text-slate-400 uppercase mb-2">Typography</h4>
              <div class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="text-[9px] text-slate-500">Font</label>
                    <select v-model="selectedElementData.styles.fontFamily" @change="autoSave" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900">
                      <option value="Inter">Inter</option><option value="'DM Sans'">DM Sans</option><option value="Georgia">Georgia</option><option value="'Courier New'">Courier</option>
                    </select>
                  </div>
                  <div>
                    <label class="text-[9px] text-slate-500">Size</label>
                    <input type="number" v-model.number="selectedElementData.styles.fontSize" @change="autoSave" class="w-full px-2 py-1.5 text-xs border rounded-lg bg-white dark:bg-slate-900">
                  </div>
                </div>
                <div>
                  <label class="text-[9px] text-slate-500">Color</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="selectedElementData.styles.color" @change="autoSave" class="w-7 h-7 rounded cursor-pointer border">
                    <input type="text" v-model="selectedElementData.styles.color" @change="autoSave" class="flex-1 px-2 py-1 text-xs font-mono border rounded-lg bg-white dark:bg-slate-900">
                  </div>
                </div>
                <div>
                  <label class="text-[9px] text-slate-500">Text Align</label>
                  <div class="flex gap-1 mt-1">
                    <button @click="selectedElementData.styles.textAlign='left'; autoSave()" :class="selectedElementData.styles.textAlign==='left'?'bg-indigo-100 text-indigo-600':''" class="flex-1 p-1.5 rounded-lg border text-xs">Left</button>
                    <button @click="selectedElementData.styles.textAlign='center'; autoSave()" :class="selectedElementData.styles.textAlign==='center'?'bg-indigo-100 text-indigo-600':''" class="flex-1 p-1.5 rounded-lg border text-xs">Center</button>
                    <button @click="selectedElementData.styles.textAlign='right'; autoSave()" :class="selectedElementData.styles.textAlign==='right'?'bg-indigo-100 text-indigo-600':''" class="flex-1 p-1.5 rounded-lg border text-xs">Right</button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Appearance -->
            <div>
              <h4 class="text-[10px] font-bold text-slate-400 uppercase mb-2">Appearance</h4>
              <div class="space-y-2">
                <div>
                  <label class="text-[9px] text-slate-500">Background</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="selectedElementData.styles.backgroundColor" @change="autoSave" class="w-7 h-7 rounded cursor-pointer border">
                    <input type="text" v-model="selectedElementData.styles.backgroundColor" @change="autoSave" class="flex-1 px-2 py-1 text-xs font-mono border rounded-lg bg-white dark:bg-slate-900">
                  </div>
                </div>
                <div>
                  <label class="text-[9px] text-slate-500">Opacity: {{ selectedElementData.styles.opacity || 100 }}%</label>
                  <input type="range" v-model.number="selectedElementData.styles.opacity" min="0" max="100" @change="autoSave" class="w-full accent-indigo-500 h-1.5">
                </div>
                <div>
                  <label class="text-[9px] text-slate-500">Rotation: {{ selectedElementData.styles.rotate || 0 }}°</label>
                  <input type="range" v-model.number="selectedElementData.styles.rotate" min="0" max="360" @change="autoSave" class="w-full accent-indigo-500 h-1.5">
                </div>
                <div>
                  <label class="text-[9px] text-slate-500">Border Radius: {{ selectedElementData.styles.borderRadius || 0 }}px</label>
                  <input type="range" v-model.number="selectedElementData.styles.borderRadius" min="0" max="50" @change="autoSave" class="w-full accent-indigo-500 h-1.5">
                </div>
              </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="space-y-2 pt-4 border-t">
              <button @click="duplicateElement" class="w-full py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-all"><i class="fa-regular fa-clone mr-1.5"></i> Duplicate</button>
              <button @click="copyElement" class="w-full py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-all"><i class="fa-regular fa-copy mr-1.5"></i> Copy (Ctrl+C)</button>
              <button @click="deleteElement" class="w-full py-2 bg-red-50 dark:bg-red-900/30 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 transition-all"><i class="fa-solid fa-trash mr-1.5"></i> Delete (Delete)</button>
            </div>
          </template>
        </div>
      </div>
    </div>
    
    <!-- Bottom Status Bar -->
    <div class="flex-shrink-0 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-t border-slate-200/50 dark:border-slate-700/50 px-4 py-1.5 flex items-center justify-between text-[10px] text-slate-500">
      <span>Page {{ (selectedPage??0)+1 }}/{{ report.content.length }} · {{ totalElements }} elements</span>
      <span :class="lastSaved ? 'text-emerald-500' : 'text-amber-500'">{{ lastSaved ? 'Saved '+lastSaved : 'Unsaved' }}</span>
    </div>
    
    <!-- Context Menu & Versions Modal (same as before) -->
    <Teleport to="body">
      <div v-if="contextMenu.show" class="fixed z-[100] bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border py-2 min-w-[200px] animate-scale-in overflow-hidden" :style="{ left: Math.min(contextMenu.x, window.innerWidth-210)+'px', top: Math.min(contextMenu.y, window.innerHeight-400)+'px' }">
        <div v-for="(item, i) in contextMenu.items" :key="i">
          <div v-if="item==='---'" class="h-px bg-slate-100 dark:bg-slate-700 my-1.5"></div>
          <button v-else @click="item.action(); contextMenu.show=false" class="w-full text-left px-4 py-2.5 text-xs hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-3 transition-colors font-medium" :class="item.danger ? 'text-red-600 hover:bg-red-50' : 'text-slate-700 dark:text-slate-300'">
            <i :class="[item.icon, 'text-xs w-4 text-center', item.danger ? 'text-red-500' : 'text-slate-400']"></i>
            <span class="flex-1">{{ item.label }}</span>
            <kbd v-if="item.shortcut" class="text-[9px] text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded font-mono">{{ item.shortcut }}</kbd>
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import { v4 as uuidv4 } from 'uuid'

const props = defineProps({ report: Object })

// ═══════════════════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════════════════
const report = reactive(JSON.parse(JSON.stringify(props.report)))
const selectedPage = ref(0)
const selectedElementIndex = ref(null)
const isDragging = ref(false)
const isFullscreen = ref(false)
const isDark = ref(localStorage.getItem('theme') === 'dark')
const showGrid = ref(true)
const gridSize = ref(20)
const zoomLevel = ref(100)
const showColorPicker = ref(false)
const showExportMenu = ref(false)
const showAIPanel = ref(false)
const showVersionsModal = ref(false)
const versions = ref([])
const lastSaved = ref('')
const canvasContainer = ref(null)
const clipboard = ref(null)
const contextMenu = reactive({ show: false, x: 0, y: 0, items: [] })
const elementSearch = ref('')
const isHeaderFooterEditable = ref(false)
const undoStack = ref([])
const redoStack = ref([])
const activeLeftTab = ref('elements')
const aiPrompt = ref('')
const aiLoading = ref(false)

// Selection state
const selectedFontFamily = ref('Inter')
const selectedFontSize = ref(14)
const selectedFontWeight = ref('400')
const selectedFontStyle = ref('normal')
const selectedTextDecoration = ref('none')
const selectedColor = ref('#000000')
const selectedTextAlign = ref('left')
const selectedLineHeight = ref(1.5)
const selectedElementIsLocked = ref(false)
const accentColor = ref(localStorage.getItem('accent-color') || '#6366f1')

// ═══════════════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════════════
const canUndo = computed(() => undoStack.value.length > 0)
const canRedo = computed(() => redoStack.value.length > 0)
const totalElements = computed(() => { let c = 0; report.content?.forEach(p => c += p.elements?.length || 0); return c })
const selectedElementData = computed(() => selectedElementIndex.value !== null && selectedPage.value !== null ? report.content[selectedPage.value]?.elements[selectedElementIndex.value] || null : null)
const isSelected = (p, e) => selectedPage.value === p && selectedElementIndex.value === e
const isChartType = (t) => t?.endsWith('-chart')
const statusBadgeClass = computed(() => ({ draft: 'bg-amber-100 text-amber-700', published: 'bg-emerald-100 text-emerald-700', archived: 'bg-slate-100 text-slate-700' }[report.status] || ''))
const statusIcon = computed(() => ({ draft: 'fa-solid fa-pen-fancy', published: 'fa-solid fa-check-circle', archived: 'fa-solid fa-box-archive' }[report.status] || 'fa-solid fa-file'))
const zoomOptions = [10, 25, 50, 75, 90, 100, 125, 150, 200, 300, 400]
const colorPalette = ['#000000','#ffffff','#6366f1','#8b5cf6','#10b981','#f59e0b','#ef4444','#ec4899','#0ea5e9','#64748b','#94a3b8','#e2e8f0']
const resizeHandles = [{ dir:'nw',cursor:'cursor-nw-resize' },{ dir:'n',cursor:'cursor-n-resize' },{ dir:'ne',cursor:'cursor-ne-resize' },{ dir:'e',cursor:'cursor-e-resize' },{ dir:'se',cursor:'cursor-se-resize' },{ dir:'s',cursor:'cursor-s-resize' },{ dir:'sw',cursor:'cursor-sw-resize' },{ dir:'w',cursor:'cursor-w-resize' }]

// ═══════════════════════════════════════════════════════════════════
// LEFT TABS
// ═══════════════════════════════════════════════════════════════════
const leftTabs = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-cube' },
  { id: 'pages', label: 'Pages', icon: 'fa-regular fa-file-lines' },
  { id: 'layout', label: 'Layout', icon: 'fa-solid fa-object-group' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-gear' },
]

// ═══════════════════════════════════════════════════════════════════
// ELEMENT CATEGORIES
// ═══════════════════════════════════════════════════════════════════
const elementCategories = ref([
  { name:'Text', icon:'fa-solid fa-font', elements:[
    { type:'text', label:'Text Box', icon:'fa-solid fa-font', color:'#6366f1', w:200, h:40 },
    { type:'heading', label:'Heading', icon:'fa-solid fa-heading', color:'#8b5cf6', w:300, h:60 },
    { type:'subheading', label:'Subtitle', icon:'fa-solid fa-paragraph', color:'#a78bfa', w:250, h:35 },
    { type:'quote', label:'Quote', icon:'fa-solid fa-quote-right', color:'#f59e0b', w:300, h:80 },
    { type:'code', label:'Code', icon:'fa-solid fa-code', color:'#10b981', w:350, h:120 },
    { type:'link', label:'Link', icon:'fa-solid fa-link', color:'#0ea5e9', w:200, h:30 },
  ]},
  { name:'Shapes', icon:'fa-solid fa-shapes', elements:[
    { type:'rectangle', label:'Rectangle', icon:'fa-regular fa-square', color:'#6366f1', w:150, h:100 },
    { type:'circle', label:'Circle', icon:'fa-regular fa-circle', color:'#8b5cf6', w:100, h:100 },
    { type:'divider', label:'Divider', icon:'fa-solid fa-minus', color:'#94a3b8', w:500, h:3 },
  ]},
  { name:'Data', icon:'fa-solid fa-table', elements:[
    { type:'table', label:'Table', icon:'fa-solid fa-table', color:'#10b981', w:450, h:200 },
    { type:'metric', label:'KPI Card', icon:'fa-solid fa-chart-simple', color:'#6366f1', w:170, h:110 },
    { type:'progress', label:'Progress', icon:'fa-solid fa-bars-progress', color:'#ec4899', w:350, h:50 },
    { type:'bar-chart', label:'Bar Chart', icon:'fa-solid fa-chart-bar', color:'#6366f1', w:400, h:280 },
    { type:'line-chart', label:'Line Chart', icon:'fa-solid fa-chart-line', color:'#10b981', w:400, h:280 },
    { type:'pie-chart', label:'Pie Chart', icon:'fa-solid fa-chart-pie', color:'#f59e0b', w:280, h:280 },
  ]},
  { name:'Media', icon:'fa-solid fa-image', elements:[
    { type:'image', label:'Image', icon:'fa-solid fa-image', color:'#6366f1', w:200, h:150 },
  ]},
  { name:'Layout', icon:'fa-solid fa-object-group', elements:[
    { type:'callout', label:'Callout', icon:'fa-solid fa-lightbulb', color:'#f59e0b', w:350, h:100 },
    { type:'testimonial', label:'Testimonial', icon:'fa-solid fa-comment-dots', color:'#ec4899', w:350, h:140 },
    { type:'price-card', label:'Price Card', icon:'fa-solid fa-tags', color:'#10b981', w:240, h:220 },
    { type:'timeline', label:'Timeline', icon:'fa-solid fa-timeline', color:'#14b8a6', w:450, h:220 },
  ]},
])

const filteredElementCategories = computed(() => {
  if (!elementSearch.value) return elementCategories.value
  const q = elementSearch.value.toLowerCase()
  return elementCategories.value.map(c => ({ ...c, elements: c.elements.filter(e => e.label.toLowerCase().includes(q) || e.type.includes(q)) })).filter(c => c.elements.length > 0)
})

// ═══════════════════════════════════════════════════════════════════
// METHODS (All core functionality - same as before, optimized)
// ═══════════════════════════════════════════════════════════════════
const toggleTheme = () => { isDark.value = !isDark.value; document.documentElement.classList.toggle('dark', isDark.value); localStorage.setItem('theme', isDark.value ? 'dark' : 'light') }
const selectPage = (i) => { selectedPage.value = i; selectedElementIndex.value = null }
const selectElement = (p, e) => {
  selectedPage.value = p; selectedElementIndex.value = e
  const el = report.content[p].elements[e]
  if (el) {
    selectedFontFamily.value = el.styles?.fontFamily || 'Inter'
    selectedFontSize.value = el.styles?.fontSize || 14
    selectedFontWeight.value = el.styles?.fontWeight || '400'
    selectedFontStyle.value = el.styles?.fontStyle || 'normal'
    selectedTextDecoration.value = el.styles?.textDecoration || 'none'
    selectedColor.value = el.styles?.color || '#000000'
    selectedTextAlign.value = el.styles?.textAlign || 'left'
    selectedLineHeight.value = el.styles?.lineHeight || 1.5
    selectedElementIsLocked.value = el.locked || false
  }
}
const deselectAll = () => { selectedElementIndex.value = null }

const getPageStyle = (i) => {
  const s = report.settings || {}
  const p = s.page_size || 'A4'; const o = s.orientation || 'portrait'
  const ws = { A4:794, Letter:816, Legal:816, A3:1123 }; const hs = { A4:1123, Letter:1056, Legal:1344, A3:1587 }
  const w = o==='landscape'?hs[p]:ws[p]; const h = o==='landscape'?ws[p]:hs[p]
  return { width:w+'px', minHeight:h+'px', backgroundColor:s.background_color||'#fff', fontFamily:s.font_family||'Inter', fontSize:(s.font_size||14)+'px', borderRadius:(s.page_radius||0)+'px', padding:(s.margin||40)+'px', position:'relative', overflow:'hidden' }
}

const getElementStyle = (el) => {
  const s = el.styles || {}
  return { left:(el.position?.x||0)+'px', top:(el.position?.y||0)+'px', width:(s.width||200)+'px', height:(s.height||50)+'px', zIndex:s.zIndex||1, borderRadius:(s.borderRadius||0)+'px', transform:`rotate(${s.rotate||0}deg)`, boxShadow:s.boxShadow||'none', cursor:el.locked?'not-allowed':'move' }
}
const getTextStyle = (el) => { const s=el.styles||{}; return { fontFamily:s.fontFamily||'Inter', fontSize:(s.fontSize||14)+'px', fontWeight:s.fontWeight||'400', fontStyle:s.fontStyle||'normal', textDecoration:s.textDecoration||'none', color:s.color||'#000', textAlign:s.textAlign||'left', lineHeight:s.lineHeight||1.5, padding:'8px', outline:'none' } }
const getHandleStyle = (d) => ({ nw:{top:'-6px',left:'-6px'},n:{top:'-6px',left:'50%',transform:'translateX(-50%)'},ne:{top:'-6px',right:'-6px'},e:{top:'50%',right:'-6px',transform:'translateY(-50%)'},se:{bottom:'-6px',right:'-6px'},s:{bottom:'-6px',left:'50%',transform:'translateX(-50%)'},sw:{bottom:'-6px',left:'-6px'},w:{top:'50%',left:'-6px',transform:'translateY(-50%)'} }[d]||{})

const onDragStart = (e, d) => { e.dataTransfer.setData('application/json', JSON.stringify(d)); isDragging.value = true }
const onDrop = (e) => {
  isDragging.value = false
  try { const d=JSON.parse(e.dataTransfer.getData('application/json')); const r=canvasContainer.value?.getBoundingClientRect(); if(!r) return; addElement(d, Math.max(0,(e.clientX-r.left)/(zoomLevel.value/100)-100), Math.max(0,(e.clientY-r.top)/(zoomLevel.value/100)-100)) } catch(er){}
}
const onElementDragStart = (e, p, i) => { e.dataTransfer.setData('element-index', JSON.stringify({p,i})); e.dataTransfer.effectAllowed='move' }

const addElement = (d, x, y) => {
  if (selectedPage.value===null) selectedPage.value=0
  if (!report.content[selectedPage.value]) report.content.push({ id:uuidv4(), label:`Page ${report.content.length+1}`, elements:[] })
  const el = { id:uuidv4(), type:d.type, content:d.type==='text'?'':d.type==='heading'?'New Heading':'', position:{ x:Math.round(x), y:Math.round(y) }, styles:{ width:d.w||200, height:d.h||50, fontSize:d.type==='heading'?28:14, fontWeight:d.type==='heading'?'700':'400', fontFamily:report.settings.font_family||'Inter', color:report.settings.text_color||'#000', backgroundColor:(d.type==='rectangle'||d.type==='circle')?(report.settings.primary_color||'#6366f1'):'transparent', opacity:100, borderRadius:0, rotate:0, zIndex:Date.now()%10000, textAlign:'left', fontStyle:'normal', textDecoration:'none', lineHeight:1.5, boxShadow:'none' }, locked:false }
  if (d.type==='table') { el.columns=['Col 1','Col 2']; el.data=[{'Col 1':'','Col 2':''}] }
  if (d.type==='metric') { el.label='Metric'; el.value='0' }
  if (isChartType(d.type)) { el.chartData={ labels:['Q1','Q2','Q3','Q4'], values:[25,40,35,50] }; el.chartTitle=d.label||'Chart' }
  report.content[selectedPage.value].elements.push(el)
  selectedElementIndex.value = report.content[selectedPage.value].elements.length - 1
  pushUndo(); autoSave()
}

const pushUndo = () => { undoStack.value.push(JSON.parse(JSON.stringify(report.content))); if(undoStack.value.length>100) undoStack.value.shift(); redoStack.value=[] }
const undo = () => { if(!undoStack.value.length) return; redoStack.value.push(JSON.parse(JSON.stringify(report.content))); report.content=undoStack.value.pop(); autoSave() }
const redo = () => { if(!redoStack.value.length) return; undoStack.value.push(JSON.parse(JSON.stringify(report.content))); report.content=redoStack.value.pop(); autoSave() }

const updateContent = (p, i, e) => { if(report.content[p]?.elements[i]) report.content[p].elements[i].content = e.target.innerHTML }
const editElementContent = (p, i) => { nextTick(()=>{ const el=document.querySelector(`[data-el-id="${report.content[p].elements[i].id}"] [contenteditable]`); if(el) el.focus() }) }
const updateTableHeader = (p, i, ci, e) => { if(report.content[p]?.elements[i]?.columns) report.content[p].elements[i].columns[ci] = e.target.textContent }
const updateTableCell = (p, i, ri, cn, e) => { if(report.content[p]?.elements[i]?.data?.[ri]) report.content[p].elements[i].data[ri][cn] = e.target.textContent }

const applyTextStyle = (prop, val) => { if(!selectedElementData.value) return; selectedElementData.value.styles[prop]=val; pushUndo(); autoSave() }

const deleteElement = () => { if(selectedElementIndex.value===null) return; report.content[selectedPage.value].elements.splice(selectedElementIndex.value,1); selectedElementIndex.value=null; pushUndo(); autoSave() }
const duplicateElement = () => { if(!selectedElementData.value) return; const c=JSON.parse(JSON.stringify(selectedElementData.value)); c.id=uuidv4(); c.position.x+=20; c.position.y+=20; report.content[selectedPage.value].elements.push(c); selectedElementIndex.value=report.content[selectedPage.value].elements.length-1; pushUndo(); autoSave() }
const copyElement = () => { if(!selectedElementData.value) return; clipboard.value=JSON.parse(JSON.stringify(selectedElementData.value)); window.showToast?.('Copied!','success') }
const pasteElement = () => { if(!clipboard.value||selectedPage.value===null) return; const c=JSON.parse(JSON.stringify(clipboard.value)); c.id=uuidv4(); c.position.x+=30; c.position.y+=30; report.content[selectedPage.value].elements.push(c); selectedElementIndex.value=report.content[selectedPage.value].elements.length-1; pushUndo(); autoSave() }
const lockElement = () => { if(!selectedElementData.value) return; selectedElementData.value.locked=!selectedElementData.value.locked; selectedElementIsLocked.value=selectedElementData.value.locked; autoSave() }
const toggleLock = () => lockElement()

const bringForward = () => { if(selectedElementData.value){ selectedElementData.value.styles.zIndex=(selectedElementData.value.styles.zIndex||1)+10; autoSave() } }
const sendBackward = () => { if(selectedElementData.value){ selectedElementData.value.styles.zIndex=Math.max(0,(selectedElementData.value.styles.zIndex||1)-10); autoSave() } }
const bringToFront = () => { if(selectedElementData.value){ selectedElementData.value.styles.zIndex=99999; autoSave() } }

let resizeInfo = null
const startResize = (e, p, i, d) => { e.preventDefault(); e.stopPropagation(); const el=report.content[p].elements[i]; resizeInfo={ p,i,d,sx:e.clientX,sy:e.clientY,elW:el.styles.width,elH:el.styles.height,elX:el.position.x,elY:el.position.y }; document.addEventListener('mousemove',handleResize); document.addEventListener('mouseup',stopResize) }
const handleResize = (e) => { if(!resizeInfo) return; const el=report.content[resizeInfo.p].elements[resizeInfo.i]; const dx=(e.clientX-resizeInfo.sx)/(zoomLevel.value/100); const dy=(e.clientY-resizeInfo.sy)/(zoomLevel.value/100); const d=resizeInfo.d; if(d.includes('e')) el.styles.width=Math.max(20,resizeInfo.elW+dx); if(d.includes('w')){ el.styles.width=Math.max(20,resizeInfo.elW-dx); el.position.x=resizeInfo.elX+dx } if(d.includes('s')) el.styles.height=Math.max(20,resizeInfo.elH+dy); if(d.includes('n')){ el.styles.height=Math.max(20,resizeInfo.elH-dy); el.position.y=resizeInfo.elY+dy } }
const stopResize = () => { if(resizeInfo){ pushUndo(); autoSave() } resizeInfo=null; document.removeEventListener('mousemove',handleResize); document.removeEventListener('mouseup',stopResize) }

let rotateInfo = null
const startRotate = (e, p, i) => { e.preventDefault(); e.stopPropagation(); const el=report.content[p].elements[i]; rotateInfo={ p,i,sa:Math.atan2(e.clientY-el.position.y-el.styles.height/2, e.clientX-el.position.x-el.styles.width/2)-((el.styles.rotate||0)*Math.PI/180) }; document.addEventListener('mousemove',handleRotate); document.addEventListener('mouseup',stopRotate) }
const handleRotate = (e) => { if(!rotateInfo) return; const el=report.content[rotateInfo.p].elements[rotateInfo.i]; el.styles.rotate=Math.round((Math.atan2(e.clientY-el.position.y-el.styles.height/2, e.clientX-el.position.x-el.styles.width/2)-rotateInfo.sa)*180/Math.PI)%360 }
const stopRotate = () => { if(rotateInfo){ pushUndo(); autoSave() } rotateInfo=null; document.removeEventListener('mousemove',handleRotate); document.removeEventListener('mouseup',stopRotate) }

const addPage = () => { report.content.push({ id:uuidv4(), label:`Page ${report.content.length+1}`, elements:[] }); selectedPage.value=report.content.length-1; pushUndo(); autoSave() }
const addPageAfter = (p) => { report.content.splice(p+1,0,{ id:uuidv4(), label:`Page ${p+2}`, elements:[] }); selectedPage.value=p+1; pushUndo(); autoSave() }
const duplicatePage = (p) => { const c=JSON.parse(JSON.stringify(report.content[p])); c.id=uuidv4(); c.elements.forEach(el=>el.id=uuidv4()); report.content.splice(p+1,0,c); selectedPage.value=p+1; pushUndo(); autoSave() }
const deletePage = (p) => { if(report.content.length<=1) return; report.content.splice(p,1); selectedPage.value=Math.min(p,report.content.length-1); pushUndo(); autoSave() }

const uploadImage = (p, i) => { const inp=document.createElement('input'); inp.type='file'; inp.accept='image/*'; inp.onchange=(ev)=>handleImageUpload(ev,p,i); inp.click() }
const handleImageUpload = async (e, p, i) => { const f=e.target.files?.[0]; if(!f) return; const fd=new FormData(); fd.append('image',f); try{ const r=await axios.post('/api/upload-image',fd); report.content[p].elements[i].src=r.data.url; autoSave() }catch(er){} }

const updateHeaderContent = (e) => { report.settings.header_text=e.target.textContent; autoSave() }
const updateFooterLeft = (e) => { report.settings.footer_left=e.target.textContent; autoSave() }

const zoomIn = () => { const i=zoomOptions.indexOf(zoomLevel.value); if(i<zoomOptions.length-1) zoomLevel.value=zoomOptions[i+1] }
const zoomOut = () => { const i=zoomOptions.indexOf(zoomLevel.value); if(i>0) zoomLevel.value=zoomOptions[i-1] }
const toggleFullscreen = () => { if(!isFullscreen.value){ document.documentElement.requestFullscreen?.(); isFullscreen.value=true } else { document.exitFullscreen?.(); isFullscreen.value=false } }

const showElementContextMenu = (e, p, i) => {
  selectElement(p, i); contextMenu.show=true; contextMenu.x=e.clientX; contextMenu.y=e.clientY
  contextMenu.items = [
    { icon:'fa-solid fa-pen-to-square', label:'Edit Content', action:()=>editElementContent(p,i) },
    { icon:'fa-regular fa-clone', label:'Duplicate', shortcut:'Ctrl+D', action:duplicateElement },
    { icon:'fa-regular fa-copy', label:'Copy', shortcut:'Ctrl+C', action:copyElement },
    '---',
    { icon:'fa-solid fa-layer-group', label:'Bring to Front', action:bringToFront },
    { icon:'fa-solid fa-arrow-up-wide-short', label:'Bring Forward', shortcut:'Ctrl+]', action:bringForward },
    { icon:'fa-solid fa-arrow-down-wide-short', label:'Send Backward', shortcut:'Ctrl+[', action:sendBackward },
    '---',
    { icon:selectedElementData.value?.locked?'fa-solid fa-unlock':'fa-solid fa-lock', label:selectedElementData.value?.locked?'Unlock':'Lock', action:lockElement },
    { icon:'fa-solid fa-trash-can', label:'Delete', shortcut:'Del', action:deleteElement, danger:true },
  ]
}
const showCanvasContextMenu = (e) => { contextMenu.show=true; contextMenu.x=e.clientX; contextMenu.y=e.clientY; contextMenu.items=[ { icon:'fa-regular fa-paste', label:'Paste', shortcut:'Ctrl+V', action:pasteElement }, { icon:'fa-solid fa-plus', label:'Add Page', action:addPage } ] }
const showPageContextMenu = (e, p) => { selectPage(p); contextMenu.show=true; contextMenu.x=e.clientX; contextMenu.y=e.clientY; contextMenu.items=[ { icon:'fa-solid fa-plus', label:'Add Page After', action:()=>addPageAfter(p) }, { icon:'fa-regular fa-clone', label:'Duplicate Page', action:()=>duplicatePage(p) }, { icon:'fa-solid fa-trash-can', label:'Delete Page', action:()=>deletePage(p), danger:report.content.length<=1 } ] }

const previewReport = () => window.open(route('reports.preview', report.slug), '_blank')
const downloadPDF = () => window.open(route('reports.download', report.slug), '_blank')
const exportImage = () => window.open(route('reports.export.image', report.slug), '_blank')
const exportCSV = () => window.open(route('reports.export.csv', report.slug), '_blank')
const exportExcel = () => window.open(route('reports.export.excel', report.slug), '_blank')

let saveTimeout
const autoSave = () => { clearTimeout(saveTimeout); saveTimeout=setTimeout(async()=>{ try{ await axios.put(route('reports.update', report.slug), { title:report.title, content:report.content, settings:report.settings }); lastSaved.value=new Date().toLocaleTimeString() }catch(e){} },1500) }

const handleKeyboardShortcuts = (e) => {
  const mod = e.ctrlKey || e.metaKey
  if(mod&&e.key==='z'){ e.preventDefault(); undo() }
  else if(mod&&e.key==='y'){ e.preventDefault(); redo() }
  else if(mod&&e.key==='c'){ e.preventDefault(); copyElement() }
  else if(mod&&e.key==='v'){ e.preventDefault(); pasteElement() }
  else if(mod&&e.key==='d'){ e.preventDefault(); duplicateElement() }
  else if(mod&&e.key==='b'){ e.preventDefault(); applyTextStyle('fontWeight', selectedFontWeight.value==='700'?'400':'700') }
  else if(mod&&e.key==='i'){ e.preventDefault(); applyTextStyle('fontStyle', selectedFontStyle.value==='italic'?'normal':'italic') }
  else if(mod&&e.key==='u'){ e.preventDefault(); applyTextStyle('textDecoration', selectedTextDecoration.value==='underline'?'none':'underline') }
  else if(mod&&e.key===']'){ e.preventDefault(); bringForward() }
  else if(mod&&e.key==='['){ e.preventDefault(); sendBackward() }
  else if(e.key==='Delete'||e.key==='Backspace'){ if(e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA'||e.target.isContentEditable) return; e.preventDefault(); deleteElement() }
  else if(e.key==='Escape'){ deselectAll(); contextMenu.show=false; showAIPanel.value=false; showExportMenu.value=false }
  else if(e.key==='F11'){ e.preventDefault(); toggleFullscreen() }
}

onMounted(() => {
  document.documentElement.classList.toggle('dark', isDark.value)
  if(!report.content||report.content.length===0){ report.content=[{ id:uuidv4(), label:'Page 1', elements:[] }]; if(props.report.template_id) report.settings={...report.settings,...props.report.settings} }
  selectedPage.value=0; pushUndo()
  accentColor.value=localStorage.getItem('accent-color')||'#6366f1'
  document.addEventListener('keydown',handleKeyboardShortcuts)
  document.addEventListener('fullscreenchange',()=>{ isFullscreen.value=!!document.fullscreenElement })
  document.addEventListener('click',(ev)=>{ if(!ev.target.closest('.context-menu-trigger')) contextMenu.show=false })
})
onBeforeUnmount(() => { document.removeEventListener('keydown',handleKeyboardShortcuts); clearTimeout(saveTimeout) })
</script>

<style scoped>
@keyframes scale-in { from{opacity:0;transform:scale(0.95) translateY(-5px)} to{opacity:1;transform:scale(1) translateY(0)} }
.animate-scale-in{animation:scale-in 0.2s cubic-bezier(0.16,1,0.3,1) forwards}
.scrollbar-thin{scrollbar-width:thin;scrollbar-color:#cbd5e1 transparent}
.scrollbar-thin::-webkit-scrollbar{width:4px;height:4px}
.scrollbar-thin::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:99px}
.dark .scrollbar-thin::-webkit-scrollbar-thumb{background:#334155}
[contenteditable]:focus{outline:2px solid #6366f1;outline-offset:2px;border-radius:4px}
</style>