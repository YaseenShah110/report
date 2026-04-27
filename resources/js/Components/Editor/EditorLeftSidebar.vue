<template>
  <aside
    class="w-64 flex flex-col overflow-hidden shrink-0 border-r transition-colors duration-200"
    :class="dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-white border-slate-200'"
  >
    <!-- Tab bar -->
    <div class="flex border-b shrink-0" :class="dark ? 'border-[#21262d]' : 'border-slate-200'">
      <button v-for="t in TABS" :key="t.id"
        @click="$emit('update:left-tab', t.id)"
        class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[9px] font-black uppercase tracking-wider border-b-2 transition-all"
        :class="leftTab === t.id
          ? 'border-indigo-500 text-indigo-500'
          : (dark ? 'border-transparent text-slate-600 hover:text-slate-400' : 'border-transparent text-slate-400 hover:text-slate-600')">
        <i :class="t.icon + ' text-sm leading-none'"></i>
        <span>{{ t.label }}</span>
      </button>
    </div>

    <!-- ═══ ELEMENTS TAB ═══ -->
    <template v-if="leftTab === 'elements'">
      <!-- Search -->
      <div class="p-2.5 border-b shrink-0" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 pointer-events-none" style="font-size:11px"></i>
          <input
            :value="elementSearch"
            @input="$emit('update:element-search', $event.target.value)"
            placeholder="Search 55+ elements…"
            class="w-full pl-7 pr-7 py-1.5 text-xs rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
            :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-200 placeholder:text-slate-600' : 'bg-slate-50 border-slate-200 text-slate-700 placeholder:text-slate-400'"
          />
          <button v-if="elementSearch" @click="$emit('update:element-search','')"
            class="absolute right-2 top-2 transition-all hover:scale-110"
            :class="dark ? 'text-slate-500 hover:text-slate-300' : 'text-slate-400 hover:text-slate-600'">
            <i class="fa-solid fa-xmark text-[10px]"></i>
          </button>
        </div>
        <!-- Quick add row -->
        <div v-if="!elementSearch" class="flex gap-1 mt-2">
          <button v-for="q in QUICK_ADDS" :key="q.type"
            @click="$emit('add-element', q.type)"
            :title="q.name"
            class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-lg text-[9px] font-bold transition-all hover:scale-105 active:scale-95 border"
            :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-400 hover:border-indigo-500 hover:text-indigo-400 hover:bg-indigo-500/5' : 'bg-slate-50 border-slate-200 text-slate-500 hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600'">
            <i :class="q.icon" style="font-size:12px"></i>
            <span>{{ q.label }}</span>
          </button>
        </div>
      </div>

      <!-- Element categories -->
      <div class="flex-1 overflow-y-auto p-2 space-y-2.5">
        <div v-if="elementSearch" class="flex items-center gap-1.5 px-1 mb-1">
          <span class="text-[10px] font-black" :class="dark ? 'text-slate-500' : 'text-slate-400'">
            <i class="fa-solid fa-filter mr-1" style="font-size:9px"></i>
            {{ filteredCategories.reduce((n,c)=>n+c.items.length,0) }} results
          </span>
        </div>

        <div v-for="cat in filteredCategories" :key="cat.name" class="space-y-1">
          <!-- Category header -->
          <button @click="toggleCat(cat.name)"
            class="w-full flex items-center gap-2 px-1 py-0.5 rounded-lg select-none group/cat transition-all"
            :class="dark ? 'hover:bg-white/5' : 'hover:bg-slate-50'">
            <div class="w-5 h-5 rounded-md flex items-center justify-center flex-shrink-0"
              :style="{ backgroundColor: cat.color + '25' }">
              <i :class="cat.icon" :style="{ color: cat.color, fontSize: '10px' }"></i>
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest flex-1 text-left"
              :class="dark ? 'text-slate-500' : 'text-slate-400'">{{ cat.name }}</span>
            <span class="text-[8px] font-bold px-1 py-0.5 rounded-full"
              :class="dark ? 'bg-[#1c2128] text-slate-600' : 'bg-slate-100 text-slate-400'">{{ cat.items.length }}</span>
            <i class="fa-solid fa-chevron-down transition-transform duration-150"
              :class="[collapsedCats.includes(cat.name) ? '' : 'rotate-180', dark ? 'text-slate-700' : 'text-slate-300']"
              style="font-size:9px"></i>
          </button>

          <!-- Elements grid -->
          <div v-show="!collapsedCats.includes(cat.name)" class="grid grid-cols-3 gap-1">
            <div v-for="el in cat.items" :key="el.type"
              :draggable="true"
              @dragstart="onDragStart($event, el)"
              @click="$emit('add-element', el.type)"
              class="group/el flex flex-col items-center gap-1 p-1.5 rounded-xl border cursor-grab active:cursor-grabbing select-none transition-all duration-150 hover:scale-105 active:scale-95"
              :class="dark
                ? 'border-[#21262d] bg-[#161b22]/60 hover:border-indigo-500/60 hover:bg-indigo-500/8'
                : 'border-slate-200 bg-white hover:border-indigo-400 hover:bg-indigo-50 hover:shadow-sm'">
              <div class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                :class="dark ? 'bg-[#1c2128] group-hover/el:bg-indigo-500/20' : 'bg-slate-100 group-hover/el:bg-indigo-100'">
                <i :class="el.icon + ' leading-none'" :style="{ color: dark ? '#64748b' : '#475569', fontSize: '13px' }"></i>
              </div>
              <span class="text-[8px] font-bold text-center leading-tight transition-colors"
                :class="dark ? 'text-slate-600 group-hover/el:text-indigo-400' : 'text-slate-500 group-hover/el:text-indigo-700'">
                {{ el.name }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="filteredCategories.length === 0" class="flex flex-col items-center py-10 gap-3">
          <i class="fa-regular fa-face-meh text-2xl" :class="dark ? 'text-slate-700' : 'text-slate-300'"></i>
          <p class="text-xs" :class="dark ? 'text-slate-600' : 'text-slate-400'">No elements found</p>
          <button @click="$emit('update:element-search','')" class="text-xs text-indigo-500 hover:text-indigo-400 font-bold">Clear search</button>
        </div>
      </div>
    </template>

    <!-- ═══ LAYERS TAB ═══ -->
    <template v-if="leftTab === 'layers'">
      <!-- Page tabs -->
      <div class="px-2 pt-2 pb-0 border-b shrink-0" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
        <div class="flex gap-1 overflow-x-auto pb-2" style="scrollbar-width:none">
          <button v-for="(page, pi) in pages" :key="page.id"
            @click="$emit('select-page', pi)"
            class="flex-shrink-0 flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black transition-all border whitespace-nowrap"
            :class="selectedPageIndex === pi
              ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-500/30'
              : (dark ? 'bg-[#161b22] text-slate-500 border-[#30363d] hover:border-indigo-500 hover:text-indigo-400' : 'bg-white text-slate-400 border-slate-200 hover:border-indigo-300 hover:text-indigo-600')">
            <i class="fa-regular fa-file" style="font-size:8px"></i>
            P{{ pi + 1 }}
          </button>
        </div>
      </div>

      <!-- Layer list -->
      <div class="flex-1 overflow-y-auto">
        <div class="p-2 pb-0">
          <div class="flex items-center justify-between px-1 mb-2">
            <span class="text-[9px] font-black uppercase tracking-widest" :class="dark ? 'text-slate-600' : 'text-slate-400'">
              {{ pages[selectedPageIndex]?.label || `Page ${selectedPageIndex + 1}` }}
            </span>
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full"
              :class="dark ? 'bg-[#1c2128] text-slate-600' : 'bg-slate-100 text-slate-400'">
              {{ (pages[selectedPageIndex]?.elements || []).length }}
            </span>
          </div>
        </div>

        <div v-if="(pages[selectedPageIndex]?.elements || []).length === 0"
          class="flex flex-col items-center py-10 gap-2">
          <i class="fa-regular fa-square-dashed text-2xl" :class="dark ? 'text-slate-700' : 'text-slate-300'"></i>
          <p class="text-[11px]" :class="dark ? 'text-slate-600' : 'text-slate-400'">No elements on this page</p>
        </div>

        <!-- Elements as layers, in reverse z-order (top element first) -->
        <div class="px-2 pb-2 space-y-1">
          <div
            v-for="el in [...(pages[selectedPageIndex]?.elements || [])].reverse()"
            :key="el.id"
            @click="$emit('select-element', el, selectedPageIndex)"
            class="flex items-center gap-2 px-2.5 py-2 rounded-xl border cursor-pointer transition-all group/layer"
            :class="selectedElement?.id === el.id
              ? (dark ? 'border-indigo-500/60 bg-indigo-500/10' : 'border-indigo-300 bg-indigo-50')
              : (dark ? 'border-[#21262d] bg-[#161b22]/40 hover:border-[#30363d]' : 'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50')">
            <!-- Layer icon -->
            <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0"
              :class="selectedElement?.id === el.id
                ? 'bg-indigo-600'
                : (dark ? 'bg-[#1c2128]' : 'bg-slate-100')">
              <i :class="getElIcon(el.type)"
                :style="{ fontSize:'10px', color: selectedElement?.id === el.id ? 'white' : (dark ? '#64748b' : '#6b7280') }"></i>
            </div>
            <!-- Label -->
            <div class="flex-1 min-w-0">
              <p class="text-[11px] font-bold truncate capitalize"
                :class="dark ? 'text-slate-300' : 'text-slate-700'">
                {{ getElLabel(el) }}
              </p>
              <p class="text-[9px] truncate"
                :class="dark ? 'text-slate-600' : 'text-slate-400'">
                {{ el.type.replace(/-/g,' ') }} · z:{{ el.styles?.zIndex || 1 }}
              </p>
            </div>
            <!-- Layer controls -->
            <div class="flex items-center gap-0.5 opacity-0 group-hover/layer:opacity-100 transition-opacity">
              <button @click.stop="el.hidden = !el.hidden; $emit('push-history')"
                :title="el.hidden ? 'Show' : 'Hide'"
                class="w-5 h-5 flex items-center justify-center rounded transition-all hover:scale-110"
                :class="el.hidden ? 'text-amber-500' : (dark ? 'text-slate-600 hover:text-slate-400' : 'text-slate-400 hover:text-slate-600')">
                <i :class="el.hidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" style="font-size:9px"></i>
              </button>
              <button @click.stop="el.locked = !el.locked; $emit('push-history')"
                :title="el.locked ? 'Unlock' : 'Lock'"
                class="w-5 h-5 flex items-center justify-center rounded transition-all hover:scale-110"
                :class="el.locked ? 'text-amber-500' : (dark ? 'text-slate-600 hover:text-slate-400' : 'text-slate-400 hover:text-slate-600')">
                <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" style="font-size:9px"></i>
              </button>
              <button @click.stop="$emit('delete-element', selectedPageIndex, el.id)"
                class="w-5 h-5 flex items-center justify-center rounded text-red-400 hover:text-red-600 hover:bg-red-50 transition-all hover:scale-110">
                <i class="fa-solid fa-xmark" style="font-size:9px"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Page management bar -->
      <div class="p-2 border-t space-y-1.5 shrink-0" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
        <div class="grid grid-cols-2 gap-1.5">
          <button @click="$emit('add-page-after', pages.length - 1)"
            class="flex items-center justify-center gap-1 py-2 rounded-xl text-[10px] font-bold border transition-all hover:scale-105 active:scale-95"
            :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-400 hover:border-emerald-500 hover:text-emerald-400' : 'bg-white border-slate-200 text-slate-500 hover:border-emerald-400 hover:text-emerald-600'">
            <i class="fa-solid fa-plus text-[9px]"></i> Add Page
          </button>
          <button @click="$emit('duplicate-page', selectedPageIndex)"
            class="flex items-center justify-center gap-1 py-2 rounded-xl text-[10px] font-bold border transition-all hover:scale-105 active:scale-95"
            :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'bg-white border-slate-200 text-slate-500 hover:border-indigo-400 hover:text-indigo-600'">
            <i class="fa-regular fa-clone text-[9px]"></i> Clone
          </button>
        </div>
        <button v-if="pages.length > 1" @click="$emit('remove-page', selectedPageIndex)"
          class="w-full flex items-center justify-center gap-1 py-1.5 rounded-xl text-[10px] font-bold border transition-all hover:scale-105 active:scale-95"
          :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-600 hover:border-red-500 hover:text-red-400' : 'bg-white border-slate-200 text-slate-400 hover:border-red-400 hover:text-red-500'">
          <i class="fa-solid fa-trash text-[9px]"></i> Delete Page {{ selectedPageIndex + 1 }}
        </button>
      </div>
    </template>

    <!-- ═══ PAGES TAB ═══ -->
    <template v-if="leftTab === 'pages'">
      <div class="flex-1 overflow-y-auto p-2 space-y-2">
        <div v-for="(page, pi) in pages" :key="page.id"
          @click="$emit('select-page', pi)"
          class="group/pg relative rounded-2xl border overflow-hidden cursor-pointer transition-all"
          :class="selectedPageIndex === pi
            ? (dark ? 'border-indigo-500 shadow-lg shadow-indigo-500/20' : 'border-indigo-400 shadow-lg shadow-indigo-400/20')
            : (dark ? 'border-[#21262d] hover:border-[#30363d]' : 'border-slate-200 hover:border-slate-300 hover:shadow-sm')">

          <!-- Thumbnail -->
          <div class="h-20 relative overflow-hidden"
            :style="{ backgroundColor: rs.background_color || '#fff' }">
            <!-- Mini elements -->
            <div v-for="el in (page.elements || []).slice(0,12)" :key="el.id"
              class="absolute rounded-sm"
              :style="{
                left: (el.position?.x / (canvasDimensions?.width || 794)) * 100 + '%',
                top: (el.position?.y / (canvasDimensions?.height || 1123)) * 100 + '%',
                width: Math.max(2, ((el.styles?.width || 50) / (canvasDimensions?.width || 794)) * 100) + '%',
                height: Math.max(1, ((el.styles?.height || 20) / (canvasDimensions?.height || 1123)) * 100) + '%',
                backgroundColor: getMiniColor(el),
                opacity: 0.8,
                zIndex: el.styles?.zIndex || 1,
              }"/>
            <!-- Active indicator -->
            <div v-if="selectedPageIndex === pi"
              class="absolute inset-0 bg-indigo-500/8 flex items-center justify-center">
              <span class="text-[9px] font-black text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full">Active</span>
            </div>
          </div>

          <!-- Info -->
          <div class="flex items-center justify-between px-3 py-2"
            :class="dark ? 'bg-[#161b22]' : 'bg-white'">
            <div class="min-w-0">
              <p class="text-[11px] font-bold truncate" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                {{ page.label || `Page ${pi + 1}` }}
              </p>
              <p class="text-[9px]" :class="dark ? 'text-slate-600' : 'text-slate-400'">
                {{ (page.elements || []).length }} elements
              </p>
            </div>
            <!-- Page actions -->
            <div class="flex items-center gap-1 opacity-0 group-hover/pg:opacity-100 transition-opacity">
              <button @click.stop="$emit('duplicate-page', pi)" title="Duplicate"
                class="w-5 h-5 flex items-center justify-center rounded transition-all hover:scale-110"
                :class="dark ? 'text-slate-600 hover:text-indigo-400' : 'text-slate-400 hover:text-indigo-600'">
                <i class="fa-regular fa-clone" style="font-size:9px"></i>
              </button>
              <button v-if="pages.length > 1" @click.stop="$emit('remove-page', pi)" title="Delete"
                class="w-5 h-5 flex items-center justify-center rounded transition-all hover:scale-110 text-red-400 hover:text-red-600">
                <i class="fa-solid fa-trash" style="font-size:9px"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Add page -->
      <div class="p-2.5 border-t shrink-0" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
        <button @click="$emit('add-page-after', pages.length - 1)"
          class="w-full flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold border-2 border-dashed rounded-xl transition-all hover:scale-[1.02] active:scale-95"
          :class="dark ? 'border-[#30363d] text-slate-500 hover:border-indigo-500 hover:text-indigo-400 hover:bg-indigo-500/5' : 'border-slate-200 text-slate-400 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50'">
          <i class="fa-solid fa-plus text-[11px]"></i> Add New Page
        </button>
      </div>
    </template>

    <!-- ═══ SETTINGS TAB ═══ -->
    <template v-if="leftTab === 'settings'">
      <div class="flex-1 overflow-y-auto">

        <SettingsSection title="Page Setup" icon="fa-solid fa-file" :dark="dark" :open="true">
          <SField label="Page Size" :dark="dark">
            <select :value="rs.page_size" @change="upRs('page_size',$event.target.value)" :class="iCls">
              <option v-for="s in PAGE_SIZES" :key="s.v" :value="s.v">{{ s.l }}</option>
            </select>
          </SField>
          <SField label="Orientation" :dark="dark" class="mt-2">
            <div class="grid grid-cols-2 gap-1.5">
              <button v-for="o in ['portrait','landscape']" :key="o"
                @click="upRs('orientation', o)"
                class="flex items-center justify-center gap-1.5 py-1.5 rounded-lg border text-[10px] font-bold capitalize transition-all"
                :class="rs.orientation === o
                  ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-500/20'
                  : (dark ? 'bg-[#161b22] text-slate-400 border-[#30363d] hover:border-indigo-500' : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300')">
                <i :class="o === 'portrait' ? 'fa-solid fa-file' : 'fa-solid fa-file fa-rotate-90'" style="font-size:10px"></i>
                {{ o }}
              </button>
            </div>
          </SField>
          <SField label="Margin (px)" :dark="dark" class="mt-2">
            <input type="number" :value="rs.margin" @change="upRs('margin', +$event.target.value)" :class="iCls" min="0" max="120" step="4"/>
          </SField>
          <SField label="Corner Radius (px)" :dark="dark" class="mt-2">
            <div class="flex items-center gap-2">
              <input type="range" :value="rs.page_radius || 0" @input="upRs('page_radius', +$event.target.value)" min="0" max="32" class="flex-1 accent-indigo-600 h-1.5"/>
              <span class="text-[10px] font-black w-7 text-right" :class="dark ? 'text-indigo-400' : 'text-indigo-600'">{{ rs.page_radius || 0 }}</span>
            </div>
          </SField>
        </SettingsSection>

        <SettingsSection title="Typography" icon="fa-solid fa-font" :dark="dark">
          <SField label="Font Family" :dark="dark">
            <select :value="rs.font_family" @change="upRs('font_family', $event.target.value)" :class="iCls">
              <option v-for="f in FONTS" :key="f.v" :value="f.v">{{ f.n }}</option>
            </select>
          </SField>
          <SField label="Base Size (px)" :dark="dark" class="mt-2">
            <input type="number" :value="rs.font_size || 14" @change="upRs('font_size', +$event.target.value)" :class="iCls" min="8" max="32"/>
          </SField>
        </SettingsSection>

        <SettingsSection title="Brand Colors" icon="fa-solid fa-palette" :dark="dark">
          <div class="space-y-2">
            <div v-for="cp in COLOR_PROPS" :key="cp.k">
              <label class="flex items-center gap-1 text-[9px] font-black uppercase tracking-wider mb-1"
                :class="dark ? 'text-slate-600' : 'text-slate-400'">
                <i :class="cp.icon" style="font-size:9px"></i> {{ cp.l }}
              </label>
              <div class="flex items-center gap-2">
                <input type="color" :value="rs[cp.k]" @input="upRs(cp.k, $event.target.value)"
                  class="w-8 h-8 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                  :class="dark ? 'border-[#30363d] bg-[#161b22]' : 'border-slate-200'"/>
                <input type="text" :value="rs[cp.k]" @input="upRs(cp.k, $event.target.value)"
                  class="flex-1 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-300' : 'bg-white border-slate-200 text-slate-700'"/>
              </div>
            </div>
          </div>
        </SettingsSection>

        <SettingsSection title="Header" icon="fa-solid fa-rectangle-ad" :dark="dark">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">Show Header</span>
            <toggle :val="rs.show_header" @click="upRs('show_header', !rs.show_header)" :dark="dark"/>
          </div>
          <template v-if="rs.show_header">
            <SField label="Header Text" :dark="dark">
              <input type="text" :value="rs.header_text" @input="upRs('header_text', $event.target.value)" :class="iCls" placeholder="Company name…"/>
            </SField>
            <SField label="Header BG" :dark="dark" class="mt-2">
              <div class="flex gap-2">
                <input type="color" :value="rs.header_color||'#1e293b'" @input="upRs('header_color',$event.target.value)"
                  class="w-8 h-8 rounded-lg border cursor-pointer p-0.5" :class="dark ? 'border-[#30363d] bg-[#161b22]' : 'border-slate-200'"/>
                <input type="text" :value="rs.header_color||'#1e293b'" @input="upRs('header_color',$event.target.value)"
                  class="flex-1 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none" :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-300' : 'bg-white border-slate-200 text-slate-700'"/>
              </div>
            </SField>
          </template>
        </SettingsSection>

        <SettingsSection title="Footer" icon="fa-solid fa-rectangle-ad" :dark="dark">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">Show Footer</span>
            <toggle :val="rs.show_footer" @click="upRs('show_footer', !rs.show_footer)" :dark="dark"/>
          </div>
          <template v-if="rs.show_footer">
            <SField label="Left Text" :dark="dark">
              <input type="text" :value="rs.footer_left" @input="upRs('footer_left',$event.target.value)" :class="iCls" placeholder="Company…"/>
            </SField>
            <SField label="Right Text" :dark="dark" class="mt-2">
              <input type="text" :value="rs.footer_right" @input="upRs('footer_right',$event.target.value)" :class="iCls" placeholder="Confidential…"/>
            </SField>
          </template>
        </SettingsSection>

        <SettingsSection title="Page Numbers" icon="fa-solid fa-hashtag" :dark="dark">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">Show Page Numbers</span>
            <toggle :val="rs.show_page_numbers" @click="upRs('show_page_numbers', !rs.show_page_numbers)" :dark="dark"/>
          </div>
        </SettingsSection>

        <SettingsSection title="Canvas" icon="fa-solid fa-ruler-combined" :dark="dark">
          <div class="space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">Grid Overlay</span>
              <toggle :val="showGrid" @click="$emit('update:show-grid', !showGrid)" :dark="dark"/>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">Snap to Grid</span>
              <toggle :val="snapToGrid" @click="$emit('update:snap-to-grid', !snapToGrid)" :dark="dark"/>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">Rulers</span>
              <toggle :val="showRulers" @click="$emit('update:show-rulers', !showRulers)" :dark="dark"/>
            </div>
          </div>
          <SField label="Grid Size (px)" :dark="dark" class="mt-3">
            <input type="number" :value="gridSize" @change="$emit('update:grid-size', +$event.target.value)" :class="iCls" min="5" max="60" step="5"/>
          </SField>
        </SettingsSection>

        <SettingsSection title="Watermark" icon="fa-solid fa-droplet" :dark="dark">
          <SField label="Watermark Text" :dark="dark">
            <input type="text" :value="rs.watermark" @input="upRs('watermark',$event.target.value)" :class="iCls" placeholder="CONFIDENTIAL · DRAFT"/>
          </SField>
        </SettingsSection>

        <SettingsSection title="Background" icon="fa-solid fa-image" :dark="dark">
          <SField label="BG Image URL" :dark="dark">
            <input type="url" :value="rs.bg_image" @input="upRs('bg_image',$event.target.value)" :class="iCls" placeholder="https://…"/>
          </SField>
        </SettingsSection>

        <SettingsSection title="RTL Layout" icon="fa-solid fa-align-right" :dark="dark">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-600'">Right-to-Left Text</span>
            <toggle :val="rs.rtl" @click="upRs('rtl', !rs.rtl)" :dark="dark"/>
          </div>
        </SettingsSection>

      </div>
    </template>

  </aside>
</template>

<script setup>
import { ref, computed, inject } from 'vue';

const FONTS = inject('FONTS') || [];
const PAGE_SIZES = inject('PAGE_SIZES') || [];
const ELEMENT_CATEGORIES = inject('ELEMENT_CATEGORIES') || [];

const props = defineProps({
  dark: Boolean, leftTab: String, pages: Array, selectedPageIndex: Number,
  elementSearch: String, rs: Object, showGrid: Boolean, snapToGrid: Boolean,
  showRulers: Boolean, gridSize: Number, inputCls: String, selectedElement: Object,
  canvasDimensions: Object,
});

const emit = defineEmits([
  'update:left-tab','update:element-search','add-element','select-page','add-page-after',
  'remove-page','duplicate-page','update:rs','update:show-grid','update:snap-to-grid',
  'update:show-rulers','update:grid-size','push-history','delete-element','select-element',
]);

const collapsedCats = ref([]);
const toggleCat = (name) => {
  const i = collapsedCats.value.indexOf(name);
  if (i > -1) collapsedCats.value.splice(i, 1);
  else collapsedCats.value.push(name);
};

const TABS = [
  { id: 'elements', label: 'Add', icon: 'fa-solid fa-plus-circle' },
  { id: 'layers',   label: 'Layers', icon: 'fa-solid fa-layer-group' },
  { id: 'pages',    label: 'Pages', icon: 'fa-regular fa-file' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-gear' },
];

const QUICK_ADDS = [
  { type:'heading',  name:'Heading',  label:'H1',    icon:'fa-solid fa-heading' },
  { type:'text',     name:'Text',     label:'Text',  icon:'fa-solid fa-paragraph' },
  { type:'metric',   name:'KPI',      label:'KPI',   icon:'fa-solid fa-chart-line' },
  { type:'bar-chart',name:'Chart',    label:'Chart', icon:'fa-solid fa-chart-bar' },
  { type:'image',    name:'Image',    label:'Image', icon:'fa-solid fa-image' },
];

const ALL_CATS = [
  { name:'Text & Typography', icon:'fa-solid fa-font', color:'#6366f1', items:[
    {type:'heading',name:'Heading H1',icon:'fa-solid fa-heading'},
    {type:'subheading',name:'Heading H2',icon:'fa-solid fa-h'},
    {type:'text',name:'Paragraph',icon:'fa-solid fa-paragraph'},
    {type:'quote',name:'Quote',icon:'fa-solid fa-quote-left'},
    {type:'blockquote',name:'Blockquote',icon:'fa-solid fa-quote-right'},
    {type:'highlight',name:'Highlight',icon:'fa-solid fa-highlighter'},
    {type:'list',name:'Bullet List',icon:'fa-solid fa-list-ul'},
    {type:'checklist',name:'Checklist',icon:'fa-solid fa-list-check'},
    {type:'code',name:'Code Block',icon:'fa-solid fa-code'},
    {type:'link',name:'Hyperlink',icon:'fa-solid fa-link'},
  ]},
  { name:'Callouts & Labels', icon:'fa-solid fa-tag', color:'#8b5cf6', items:[
    {type:'badge',name:'Badge',icon:'fa-solid fa-tag'},
    {type:'callout',name:'Callout',icon:'fa-solid fa-circle-info'},
    {type:'alert',name:'Alert Box',icon:'fa-solid fa-triangle-exclamation'},
    {type:'icon',name:'Icon/Emoji',icon:'fa-solid fa-star'},
    {type:'rating',name:'Star Rating',icon:'fa-regular fa-star'},
    {type:'watermark',name:'Watermark',icon:'fa-solid fa-droplet'},
  ]},
  { name:'Charts & Graphs', icon:'fa-solid fa-chart-bar', color:'#10b981', items:[
    {type:'bar-chart',name:'Bar Chart',icon:'fa-solid fa-chart-bar'},
    {type:'line-chart',name:'Line Chart',icon:'fa-solid fa-chart-line'},
    {type:'area-chart',name:'Area Chart',icon:'fa-solid fa-chart-area'},
    {type:'pie-chart',name:'Pie Chart',icon:'fa-solid fa-chart-pie'},
    {type:'doughnut-chart',name:'Doughnut',icon:'fa-solid fa-circle-half-stroke'},
    {type:'radar-chart',name:'Radar Chart',icon:'fa-solid fa-circle-nodes'},
  ]},
  { name:'Data & KPIs', icon:'fa-solid fa-database', color:'#f59e0b', items:[
    {type:'metric',name:'KPI Card',icon:'fa-solid fa-square-poll-vertical'},
    {type:'progress',name:'Progress Bar',icon:'fa-solid fa-bars-progress'},
    {type:'circular-progress',name:'Circular %',icon:'fa-solid fa-circle-notch'},
    {type:'stat-row',name:'Stats Row',icon:'fa-solid fa-table-columns'},
    {type:'table',name:'Data Table',icon:'fa-solid fa-table'},
    {type:'timeline',name:'Timeline',icon:'fa-solid fa-timeline'},
    {type:'toc',name:'Contents',icon:'fa-solid fa-list-ol'},
  ]},
  { name:'Media', icon:'fa-solid fa-photo-film', color:'#ec4899', items:[
    {type:'image',name:'Image',icon:'fa-solid fa-image'},
    {type:'video',name:'Video',icon:'fa-solid fa-film'},
  ]},
  { name:'Shapes & Lines', icon:'fa-solid fa-shapes', color:'#0ea5e9', items:[
    {type:'rectangle',name:'Rectangle',icon:'fa-regular fa-square'},
    {type:'circle',name:'Circle',icon:'fa-regular fa-circle'},
    {type:'triangle',name:'Triangle',icon:'fa-solid fa-play fa-rotate-270'},
    {type:'star',name:'Star',icon:'fa-regular fa-star'},
    {type:'line',name:'Line',icon:'fa-solid fa-minus'},
    {type:'arrow',name:'Arrow',icon:'fa-solid fa-arrow-right'},
    {type:'double-arrow',name:'Two-way Arrow',icon:'fa-solid fa-arrows-left-right'},
    {type:'divider',name:'Divider',icon:'fa-solid fa-grip-lines'},
    {type:'spacer',name:'Spacer',icon:'fa-solid fa-square-dashed'},
  ]},
  { name:'Cards & Components', icon:'fa-solid fa-id-card', color:'#f97316', items:[
    {type:'social-card',name:'Profile Card',icon:'fa-solid fa-id-badge'},
    {type:'testimonial',name:'Testimonial',icon:'fa-solid fa-comment-dots'},
    {type:'kanban',name:'Kanban Card',icon:'fa-solid fa-thumbtack'},
    {type:'price-card',name:'Price Card',icon:'fa-solid fa-dollar-sign'},
  ]},
  { name:'Report Elements', icon:'fa-solid fa-file-lines', color:'#64748b', items:[
    {type:'pagenum',name:'Page Number',icon:'fa-solid fa-hashtag'},
    {type:'date',name:'Date',icon:'fa-solid fa-calendar-days'},
    {type:'signature',name:'Signature',icon:'fa-solid fa-signature'},
  ]},
];

const filteredCategories = computed(() => {
  if (!props.elementSearch?.trim()) return ALL_CATS;
  const q = props.elementSearch.toLowerCase();
  return ALL_CATS.map(c => ({
    ...c,
    items: c.items.filter(i => i.name.toLowerCase().includes(q) || i.type.includes(q)),
  })).filter(c => c.items.length > 0);
});

const COLOR_PROPS = [
  { k:'primary_color',    l:'Primary',    icon:'fa-solid fa-circle text-indigo-500' },
  { k:'accent_color',     l:'Accent',     icon:'fa-solid fa-circle text-violet-500' },
  { k:'background_color', l:'Page BG',    icon:'fa-solid fa-fill-drip' },
  { k:'text_color',       l:'Text Color', icon:'fa-solid fa-font' },
];

const iCls = computed(() =>
  props.dark
    ? 'w-full text-xs bg-[#161b22] border border-[#30363d] rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-200 transition-all'
    : 'w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 transition-all'
);

const upRs = (k, v) => emit('update:rs', { ...props.rs, [k]: v });

const onDragStart = (e, el) => {
  e.dataTransfer.setData('elementType', el.type);
  e.dataTransfer.effectAllowed = 'copy';
};

const ICON_MAP = {
  heading:'fa-solid fa-heading',subheading:'fa-solid fa-h',text:'fa-solid fa-paragraph',
  quote:'fa-solid fa-quote-left',blockquote:'fa-solid fa-quote-right',highlight:'fa-solid fa-highlighter',
  list:'fa-solid fa-list',checklist:'fa-solid fa-list-check',link:'fa-solid fa-link',code:'fa-solid fa-code',
  badge:'fa-solid fa-tag',callout:'fa-solid fa-circle-info',alert:'fa-solid fa-triangle-exclamation',
  icon:'fa-solid fa-star',rating:'fa-regular fa-star',watermark:'fa-solid fa-droplet',
  'bar-chart':'fa-solid fa-chart-bar','line-chart':'fa-solid fa-chart-line',
  'area-chart':'fa-solid fa-chart-area','pie-chart':'fa-solid fa-chart-pie',
  'doughnut-chart':'fa-solid fa-circle-half-stroke','radar-chart':'fa-solid fa-circle-nodes',
  metric:'fa-solid fa-square-poll-vertical',progress:'fa-solid fa-bars-progress',
  'circular-progress':'fa-solid fa-circle-notch','stat-row':'fa-solid fa-table-columns',
  table:'fa-solid fa-table',timeline:'fa-solid fa-timeline',toc:'fa-solid fa-list-ol',
  image:'fa-solid fa-image',video:'fa-solid fa-film',
  rectangle:'fa-regular fa-square',circle:'fa-regular fa-circle',triangle:'fa-solid fa-play',
  star:'fa-regular fa-star',line:'fa-solid fa-minus',arrow:'fa-solid fa-arrow-right',
  'double-arrow':'fa-solid fa-arrows-left-right',divider:'fa-solid fa-grip-lines',spacer:'fa-solid fa-square-dashed',
  'social-card':'fa-solid fa-id-badge',testimonial:'fa-solid fa-comment-dots',
  kanban:'fa-solid fa-thumbtack','price-card':'fa-solid fa-dollar-sign',
  pagenum:'fa-solid fa-hashtag',date:'fa-solid fa-calendar-days',signature:'fa-solid fa-signature',
};
const getElIcon = (type) => ICON_MAP[type] || 'fa-solid fa-cube';

const getElLabel = (el) => {
  if (el.content && typeof el.content === 'string') {
    const stripped = el.content.replace(/<[^>]*>/g,'').trim();
    if (stripped) return stripped.slice(0,25) + (stripped.length>25?'…':'');
  }
  if (el.label) return el.label;
  if (el.value) return String(el.value);
  if (el.chartTitle) return el.chartTitle;
  return el.type.replace(/-/g,' ');
};

const getMiniColor = (el) => {
  const type = el.type;
  if (type === 'rectangle' || type === 'circle' || type === 'triangle') return el.styles?.backgroundColor || '#6366f1';
  if (['heading','subheading','text'].includes(type)) return '#94a3b8';
  if (type.includes('chart')) return (el.chartColor || '#6366f1') + '80';
  if (type === 'image') return '#e2e8f0';
  if (type === 'metric') return el.styles?.backgroundColor || '#f8fafc';
  return '#e2e8f0';
};
</script>

<script>
const SettingsSection = {
  name: 'SettingsSection',
  props: { title: String, icon: String, dark: Boolean, open: Boolean },
  data() { return { isOpen: this.open ?? false }; },
  template: `
    <div class="border-b" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
      <button @click="isOpen = !isOpen" class="w-full flex items-center justify-between px-4 py-2.5 transition-colors" :class="dark ? 'hover:bg-white/5' : 'hover:bg-slate-50'">
        <div class="flex items-center gap-2">
          <i :class="icon" :style="isOpen ? 'color:#6366f1;font-size:11px' : (dark ? 'color:#475569;font-size:11px' : 'color:#94a3b8;font-size:11px')"></i>
          <span class="text-[10px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-400' : 'text-slate-600'">{{ title }}</span>
        </div>
        <i class="fa-solid fa-chevron-down transition-transform duration-200" :class="[isOpen ? 'rotate-180' : '', dark ? 'text-slate-700' : 'text-slate-400']" style="font-size:9px"></i>
      </button>
      <div v-show="isOpen" class="px-4 pb-4 pt-1 space-y-2">
        <slot/>
      </div>
    </div>
  `,
};

const SField = {
  name: 'SField',
  props: { label: String, dark: Boolean },
  template: `
    <div>
      <label class="block text-[9px] font-black uppercase tracking-wider mb-1" :class="dark ? 'text-slate-600' : 'text-slate-400'">{{ label }}</label>
      <slot/>
    </div>
  `,
};

const Toggle = {
  name: 'Toggle',
  props: { val: Boolean, dark: Boolean },
  emits: ['click'],
  template: `
    <button @click="$emit('click')"
      class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors flex-shrink-0"
      :class="val ? 'bg-indigo-600' : (dark ? 'bg-slate-700' : 'bg-slate-200')">
      <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
        :class="val ? 'translate-x-5' : 'translate-x-1'"></span>
    </button>
  `,
};
</script>

<style scoped>
::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 99px; }
</style>