<template>
  <aside class="w-72 flex flex-col overflow-hidden shrink-0 border-l transition-colors duration-200"
         :class="dark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">

    <!-- No selection state -->
    <template v-if="!selectedElement">
      <div class="flex-1 flex flex-col items-center justify-center gap-4 px-6 py-10 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-2 transition-all"
             :class="dark ? 'bg-slate-800 border border-slate-700' : 'bg-slate-50 border border-slate-200'">
          <i class="fa-solid fa-arrow-pointer text-2xl" :class="dark ? 'text-slate-600' : 'text-slate-300'"></i>
        </div>
        <div>
          <p class="text-xs font-black" :class="dark ? 'text-slate-500' : 'text-slate-400'">No element selected</p>
          <p class="text-[10px] mt-0.5" :class="dark ? 'text-slate-700' : 'text-slate-400'">Click an element to edit its properties</p>
        </div>
        <!-- Shortcuts hint -->
        <div class="mt-2 w-full space-y-1.5">
          <div v-for="sc in SHORTCUTS.slice(0,6)" :key="sc.key"
               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg"
               :class="dark ? 'bg-slate-800/50' : 'bg-slate-50'">
            <span class="text-[10px]" :class="dark ? 'text-slate-500' : 'text-slate-400'">{{ sc.desc }}</span>
            <kbd class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded"
                 :class="dark ? 'bg-slate-700 text-slate-400' : 'bg-white text-slate-500 border border-slate-200'">{{ sc.key }}</kbd>
          </div>
        </div>
      </div>
    </template>

    <!-- Element selected -->
    <template v-else>
      <!-- Panel header -->
      <div class="px-4 py-3 border-b flex items-center justify-between shrink-0"
           :class="dark ? 'border-slate-800 bg-slate-900' : 'border-slate-100 bg-white'">
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
               :class="dark ? 'bg-indigo-900/40' : 'bg-indigo-50'">
            <i class="fa-solid fa-pen-ruler text-xs text-indigo-500"></i>
          </div>
          <div class="min-w-0">
            <p class="text-[11px] font-black capitalize truncate"
               :class="dark ? 'text-slate-200' : 'text-slate-700'">
              {{ selectedElement.type.replace(/-/g, ' ') }}
            </p>
            <p class="text-[9px] font-mono truncate"
               :class="dark ? 'text-slate-600' : 'text-slate-400'">{{ selectedElement.id.slice(0,8) }}…</p>
          </div>
        </div>
        <div class="flex items-center gap-1 shrink-0">
          <!-- Visibility -->
          <button @click="selectedElement.hidden = !selectedElement.hidden; $emit('push-history')"
                  :title="selectedElement.hidden ? 'Show' : 'Hide'"
                  class="p-1.5 rounded-lg transition-all"
                  :class="selectedElement.hidden
                    ? 'bg-amber-500/20 text-amber-500'
                    : dark ? 'text-slate-500 hover:text-slate-300 hover:bg-white/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'">
            <i :class="selectedElement.hidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
          </button>
          <!-- Lock -->
          <button @click="selectedElement.locked = !selectedElement.locked; $emit('push-history')"
                  :title="selectedElement.locked ? 'Unlock' : 'Lock'"
                  class="p-1.5 rounded-lg transition-all"
                  :class="selectedElement.locked
                    ? 'bg-amber-500/20 text-amber-500'
                    : dark ? 'text-slate-500 hover:text-slate-300 hover:bg-white/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'">
            <i :class="selectedElement.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" class="text-xs"></i>
          </button>
          <!-- Delete -->
          <button @click="$emit('delete-selected')"
                  class="p-1.5 rounded-lg transition-all"
                  :class="dark ? 'text-slate-500 hover:text-red-400 hover:bg-red-500/10' : 'text-slate-400 hover:text-red-500 hover:bg-red-50'">
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
        </div>
      </div>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto">

        <!-- POSITION & SIZE -->
        <PanelSection title="Position & Size" icon="fa-solid fa-up-down-left-right" :dark="dark" defaultOpen>
          <div class="grid grid-cols-2 gap-2">
            <PropField label="X" :dark="dark">
              <input type="number" :value="Math.round(selectedElement.position.x)"
                     @input="selectedElement.position.x = +$event.target.value; $emit('push-history')"
                     :class="inputCls" step="1"/>
            </PropField>
            <PropField label="Y" :dark="dark">
              <input type="number" :value="Math.round(selectedElement.position.y)"
                     @input="selectedElement.position.y = +$event.target.value; $emit('push-history')"
                     :class="inputCls" step="1"/>
            </PropField>
            <PropField label="Width" :dark="dark">
              <input type="number" :value="Math.round(selectedElement.styles?.width || 200)"
                     @input="selectedElement.styles.width = +$event.target.value; $emit('push-history')"
                     :class="inputCls" step="1" min="10"/>
            </PropField>
            <PropField label="Height" :dark="dark">
              <input type="number" :value="Math.round(selectedElement.styles?.height || 50)"
                     @input="selectedElement.styles.height = +$event.target.value; $emit('push-history')"
                     :class="inputCls" step="1" min="10"/>
            </PropField>
          </div>
          <!-- Rotation -->
          <div class="mt-2 flex items-center gap-2">
            <PropField label="Rotation" :dark="dark" class="flex-1">
              <div class="flex items-center gap-1.5">
                <input type="range" :value="selectedElement.styles?.rotate || 0" min="-180" max="180" step="1"
                       @input="selectedElement.styles.rotate = +$event.target.value; $emit('push-history')"
                       class="flex-1 accent-indigo-600"/>
                <input type="number" :value="selectedElement.styles?.rotate || 0" min="-180" max="180"
                       @input="selectedElement.styles.rotate = +$event.target.value; $emit('push-history')"
                       class="w-14 text-xs text-center rounded-lg border px-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       :class="dark ? 'bg-slate-800 border-slate-700 text-slate-200' : 'bg-white border-slate-200 text-slate-700'"/>
              </div>
            </PropField>
          </div>
        </PanelSection>

        <!-- ALIGNMENT -->
        <PanelSection title="Alignment" icon="fa-solid fa-align-center" :dark="dark">
          <div class="flex items-center gap-1 flex-wrap">
            <button v-for="a in ALIGN_BTNS" :key="a.dir"
                    @click="$emit('align-element', a.dir)"
                    :title="a.label"
                    class="flex-1 py-2 rounded-lg border flex items-center justify-center transition-all hover:scale-105 active:scale-95"
                    :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50'">
              <i :class="a.icon + ' text-xs'"></i>
            </button>
          </div>
        </PanelSection>

        <!-- LAYER ORDER -->
        <PanelSection title="Layer Order" icon="fa-solid fa-layer-group" :dark="dark">
          <div class="flex items-center gap-1.5">
            <PropField label="Z-Index" :dark="dark" class="flex-1">
              <input type="number" :value="selectedElement.styles?.zIndex || 1"
                     @input="selectedElement.styles.zIndex = +$event.target.value; $emit('push-history')"
                     :class="inputCls" step="1" min="1"/>
            </PropField>
          </div>
          <div class="grid grid-cols-4 gap-1 mt-2">
            <button v-for="lb in LAYER_BTNS" :key="lb.action"
                    @click="$emit(lb.action)"
                    :title="lb.label"
                    class="py-2 rounded-lg border text-[10px] font-bold flex flex-col items-center gap-1 transition-all hover:scale-105"
                    :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-indigo-400 hover:text-indigo-600'">
              <i :class="lb.icon + ' text-xs'"></i>
              <span>{{ lb.label }}</span>
            </button>
          </div>
        </PanelSection>

        <!-- MOVE TO PAGE -->
        <PanelSection v-if="pages.length > 1" title="Move to Page" icon="fa-solid fa-file-export" :dark="dark">
          <div class="flex flex-wrap gap-1">
            <button v-for="(p, pi) in pages" :key="pi"
                    :disabled="pi === selectedPageIndex"
                    @click="$emit('move-element-to-page', selectedElement, selectedPageIndex, pi)"
                    class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-all"
                    :class="pi === selectedPageIndex
                      ? (dark ? 'border-slate-700 bg-slate-800 text-slate-700 cursor-default' : 'border-slate-100 bg-slate-50 text-slate-300 cursor-default')
                      : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-400 hover:text-indigo-600')">
              Page {{ pi + 1 }}{{ pi === selectedPageIndex ? ' (current)' : '' }}
            </button>
          </div>
        </PanelSection>

        <!-- TEXT PROPERTIES -->
        <template v-if="isTextType">
          <PanelSection title="Typography" icon="fa-solid fa-font" :dark="dark" defaultOpen>
            <PropField label="Font Family" :dark="dark">
              <select :value="selectedElement.styles?.fontFamily || rs.font_family"
                      @change="selectedElement.styles.fontFamily = $event.target.value; $emit('push-history')"
                      :class="inputCls">
                <option v-for="f in FONTS" :key="f.value" :value="f.value">{{ f.name }}</option>
              </select>
            </PropField>

            <div class="grid grid-cols-2 gap-2 mt-2">
              <PropField label="Size (px)" :dark="dark">
                <input type="number" :value="selectedElement.styles?.fontSize || 14"
                       @input="selectedElement.styles.fontSize = +$event.target.value; $emit('push-history')"
                       :class="inputCls" min="6" max="200"/>
              </PropField>
              <PropField label="Weight" :dark="dark">
                <select :value="selectedElement.styles?.fontWeight || '400'"
                        @change="selectedElement.styles.fontWeight = $event.target.value; $emit('push-history')"
                        :class="inputCls">
                  <option v-for="fw in FONT_WEIGHTS" :key="fw.value" :value="fw.value">{{ fw.label }}</option>
                </select>
              </PropField>
              <PropField label="Line Height" :dark="dark">
                <input type="number" :value="selectedElement.styles?.lineHeight || 1.5"
                       @input="selectedElement.styles.lineHeight = +$event.target.value; $emit('push-history')"
                       :class="inputCls" min="0.8" max="4" step="0.1"/>
              </PropField>
              <PropField label="Letter Spacing" :dark="dark">
                <input type="number" :value="selectedElement.styles?.letterSpacing || 0"
                       @input="selectedElement.styles.letterSpacing = +$event.target.value; $emit('push-history')"
                       :class="inputCls" min="-5" max="20" step="0.5"/>
              </PropField>
            </div>

            <!-- Style toggles -->
            <div class="flex items-center gap-1 mt-2">
              <button v-for="st in TEXT_STYLES" :key="st.key"
                      @click="toggleTextStyle(st)"
                      :title="st.label"
                      class="flex-1 py-2 rounded-lg border text-xs font-bold transition-all"
                      :class="isTextStyleActive(st)
                        ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-500/30'
                        : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-400')">
                <i :class="st.icon + ' text-xs'"></i>
              </button>
            </div>

            <!-- Alignment -->
            <div class="flex items-center gap-1 mt-2">
              <button v-for="al in TEXT_ALIGNS" :key="al.val"
                      @click="selectedElement.styles.textAlign = al.val; $emit('push-history')"
                      :title="al.label"
                      class="flex-1 py-2 rounded-lg border text-xs font-bold transition-all"
                      :class="selectedElement.styles?.textAlign === al.val
                        ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-500/30'
                        : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-400')">
                <i :class="al.icon + ' text-xs'"></i>
              </button>
            </div>

            <!-- Transform -->
            <PropField label="Text Transform" :dark="dark" class="mt-2">
              <select :value="selectedElement.styles?.textTransform || 'none'"
                      @change="selectedElement.styles.textTransform = $event.target.value; $emit('push-history')"
                      :class="inputCls">
                <option value="none">None</option>
                <option value="uppercase">UPPERCASE</option>
                <option value="lowercase">lowercase</option>
                <option value="capitalize">Capitalize</option>
              </select>
            </PropField>
          </PanelSection>
        </template>

        <!-- COLOR & FILL -->
        <PanelSection title="Color & Fill" icon="fa-solid fa-palette" :dark="dark" defaultOpen>
          <div class="space-y-2">
            <!-- Text color (for text types) -->
            <ColorRow v-if="isTextType" label="Text Color" :dark="dark"
                      :value="selectedElement.styles?.color || '#1e293b'"
                      @update="v => { selectedElement.styles.color = v; $emit('push-history') }"/>

            <!-- Background color -->
            <ColorRow label="Background" :dark="dark"
                      :value="selectedElement.styles?.backgroundColor || 'transparent'"
                      @update="v => { selectedElement.styles.backgroundColor = v; $emit('push-history') }"/>

            <!-- Opacity -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="text-[10px] font-bold uppercase tracking-wider"
                       :class="dark ? 'text-slate-500' : 'text-slate-400'">Opacity</label>
                <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.styles?.opacity ?? 100 }}%</span>
              </div>
              <input type="range" :value="selectedElement.styles?.opacity ?? 100" min="0" max="100" step="1"
                     @input="selectedElement.styles.opacity = +$event.target.value; $emit('push-history')"
                     class="w-full accent-indigo-600"/>
            </div>
          </div>
        </PanelSection>

        <!-- BORDER -->
        <PanelSection title="Border" icon="fa-solid fa-border-all" :dark="dark">
          <div class="space-y-2">
            <div class="grid grid-cols-2 gap-2">
              <PropField label="Width (px)" :dark="dark">
                <input type="number" :value="selectedElement.styles?.borderWidth || 0"
                       @input="selectedElement.styles.borderWidth = +$event.target.value; $emit('push-history')"
                       :class="inputCls" min="0" max="20"/>
              </PropField>
              <PropField label="Style" :dark="dark">
                <select :value="selectedElement.styles?.borderStyle || 'solid'"
                        @change="selectedElement.styles.borderStyle = $event.target.value; $emit('push-history')"
                        :class="inputCls">
                  <option value="solid">Solid</option>
                  <option value="dashed">Dashed</option>
                  <option value="dotted">Dotted</option>
                  <option value="double">Double</option>
                </select>
              </PropField>
            </div>
            <ColorRow v-if="selectedElement.styles?.borderWidth" label="Border Color" :dark="dark"
                      :value="selectedElement.styles?.borderColor || '#e2e8f0'"
                      @update="v => { selectedElement.styles.borderColor = v; $emit('push-history') }"/>
            <PropField label="Border Radius (px)" :dark="dark">
              <div class="flex items-center gap-2">
                <input type="range" :value="selectedElement.styles?.borderRadius || 0" min="0" max="64" step="1"
                       @input="selectedElement.styles.borderRadius = +$event.target.value; $emit('push-history')"
                       class="flex-1 accent-indigo-600"/>
                <span class="text-[10px] font-black w-8 text-right"
                      :class="dark ? 'text-indigo-400' : 'text-indigo-600'">
                  {{ selectedElement.styles?.borderRadius || 0 }}
                </span>
              </div>
            </PropField>
          </div>
        </PanelSection>

        <!-- SHADOW -->
        <PanelSection title="Shadow" icon="fa-solid fa-cloud" :dark="dark">
          <div class="grid grid-cols-3 gap-1.5">
            <button v-for="sh in SHADOW_OPTIONS" :key="sh.value"
                    @click="selectedElement.styles.boxShadow = sh.value; $emit('push-history')"
                    class="py-2.5 rounded-xl border flex flex-col items-center gap-1 transition-all hover:scale-105 text-[9px] font-bold"
                    :class="selectedElement.styles?.boxShadow === sh.value
                      ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-500/30'
                      : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-400')">
              <span :style="{width:'24px',height:'16px',borderRadius:'4px',background: dark ? '#475569' : '#e2e8f0', boxShadow: sh.preview}"></span>
              {{ sh.label }}
            </button>
          </div>
        </PanelSection>

        <!-- PADDING -->
        <PanelSection title="Spacing" icon="fa-solid fa-maximize" :dark="dark">
          <PropField label="Padding (px)" :dark="dark">
            <input type="number" :value="selectedElement.styles?.padding || 0"
                   @input="selectedElement.styles.padding = +$event.target.value; $emit('push-history')"
                   :class="inputCls" min="0" max="80"/>
          </PropField>
        </PanelSection>

        <!-- IMAGE SPECIFIC -->
        <template v-if="selectedElement.type === 'image'">
          <PanelSection title="Image" icon="fa-solid fa-image" :dark="dark" defaultOpen>
            <PropField label="Object Fit" :dark="dark">
              <select :value="selectedElement.styles?.objectFit || 'cover'"
                      @change="selectedElement.styles.objectFit = $event.target.value; $emit('push-history')"
                      :class="inputCls">
                <option value="cover">Cover</option>
                <option value="contain">Contain</option>
                <option value="fill">Fill</option>
                <option value="none">None</option>
              </select>
            </PropField>
            <div class="mt-2">
              <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 border-dashed cursor-pointer transition-all"
                     :class="dark ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400 hover:text-indigo-600'">
                <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                <span class="text-xs font-bold">Upload Image</span>
                <input type="file" accept="image/*" class="hidden" @change="e => $emit('upload-image', e)"/>
              </label>
            </div>
            <PropField label="Image URL" :dark="dark" class="mt-2">
              <input type="url" :value="selectedElement.src || ''"
                     @input="selectedElement.src = $event.target.value; $emit('push-history')"
                     :class="inputCls" placeholder="https://…"/>
            </PropField>
            <PropField label="Alt Text" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.alt || ''"
                     @input="selectedElement.alt = $event.target.value; $emit('push-history')"
                     :class="inputCls" placeholder="Describe the image…"/>
            </PropField>
          </PanelSection>
        </template>

        <!-- METRIC / KPI SPECIFIC -->
        <template v-if="selectedElement.type === 'metric'">
          <PanelSection title="KPI Card" icon="fa-solid fa-chart-line" :dark="dark" defaultOpen>
            <div class="space-y-2">
              <PropField label="Label" :dark="dark">
                <input type="text" :value="selectedElement.label || ''"
                       @input="selectedElement.label = $event.target.value; $emit('push-history')"
                       :class="inputCls" placeholder="e.g. Revenue"/>
              </PropField>
              <PropField label="Value" :dark="dark">
                <input type="text" :value="selectedElement.value || ''"
                       @input="selectedElement.value = $event.target.value; $emit('push-history')"
                       :class="inputCls" placeholder="e.g. $48,200"/>
              </PropField>
              <div class="grid grid-cols-2 gap-2">
                <PropField label="Change" :dark="dark">
                  <input type="text" :value="selectedElement.change || ''"
                         @input="selectedElement.change = $event.target.value; $emit('push-history')"
                         :class="inputCls" placeholder="12.5%"/>
                </PropField>
                <PropField label="Type" :dark="dark">
                  <select :value="selectedElement.changeType || 'positive'"
                          @change="selectedElement.changeType = $event.target.value; $emit('push-history')"
                          :class="inputCls">
                    <option value="positive">▲ Positive</option>
                    <option value="negative">▼ Negative</option>
                  </select>
                </PropField>
              </div>
              <PropField label="Period" :dark="dark">
                <input type="text" :value="selectedElement.changePeriod || ''"
                       @input="selectedElement.changePeriod = $event.target.value; $emit('push-history')"
                       :class="inputCls" placeholder="vs last month"/>
              </PropField>
              <ColorRow label="Accent Color" :dark="dark"
                        :value="selectedElement.styles?.color || rs.primary_color"
                        @update="v => { selectedElement.styles.color = v; $emit('push-history') }"/>
            </div>
          </PanelSection>
        </template>

        <!-- PROGRESS BAR SPECIFIC -->
        <template v-if="selectedElement.type === 'progress'">
          <PanelSection title="Progress Bar" icon="fa-solid fa-bars-progress" :dark="dark" defaultOpen>
            <div class="space-y-2">
              <PropField label="Label" :dark="dark">
                <input type="text" :value="selectedElement.label || ''"
                       @input="selectedElement.label = $event.target.value; $emit('push-history')"
                       :class="inputCls"/>
              </PropField>
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider"
                         :class="dark ? 'text-slate-500' : 'text-slate-400'">Value</label>
                  <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.value ?? 0 }}%</span>
                </div>
                <input type="range" :value="selectedElement.value ?? 0" min="0" max="100" step="1"
                       @input="selectedElement.value = +$event.target.value; $emit('push-history')"
                       class="w-full accent-indigo-600"/>
              </div>
              <ColorRow label="Fill Color" :dark="dark"
                        :value="selectedElement.styles?.color || rs.primary_color"
                        @update="v => { selectedElement.styles.color = v; $emit('push-history') }"/>
              <ColorRow label="Track Color" :dark="dark"
                        :value="selectedElement.styles?.trackColor || '#e2e8f0'"
                        @update="v => { selectedElement.styles.trackColor = v; $emit('push-history') }"/>
            </div>
          </PanelSection>
        </template>

        <!-- TABLE SPECIFIC -->
        <template v-if="selectedElement.type === 'table'">
          <PanelSection title="Table Settings" icon="fa-solid fa-table" :dark="dark" defaultOpen>
            <div class="space-y-2">
              <ColorRow label="Header BG" :dark="dark"
                        :value="selectedElement.styles?.headerBg || rs.primary_color"
                        @update="v => { selectedElement.styles.headerBg = v; $emit('push-history') }"/>
              <ColorRow label="Header Text" :dark="dark"
                        :value="selectedElement.styles?.headerColor || '#ffffff'"
                        @update="v => { selectedElement.styles.headerColor = v; $emit('push-history') }"/>
              <ColorRow label="Even Row BG" :dark="dark"
                        :value="selectedElement.styles?.evenRowBg || '#ffffff'"
                        @update="v => { selectedElement.styles.evenRowBg = v; $emit('push-history') }"/>
              <ColorRow label="Odd Row BG" :dark="dark"
                        :value="selectedElement.styles?.oddRowBg || '#f8fafc'"
                        @update="v => { selectedElement.styles.oddRowBg = v; $emit('push-history') }"/>
            </div>
            <div class="flex gap-1.5 mt-3 flex-wrap">
              <button @click="$emit('add-table-row')" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold border transition-all"
                      :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-emerald-500 hover:text-emerald-400' : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-400 hover:text-emerald-600'">
                <i class="fa-solid fa-plus mr-1"></i>Row
              </button>
              <button @click="$emit('add-table-column')" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold border transition-all"
                      :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-emerald-500 hover:text-emerald-400' : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-400 hover:text-emerald-600'">
                <i class="fa-solid fa-plus mr-1"></i>Col
              </button>
              <button @click="$emit('remove-table-row')" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold border transition-all"
                      :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-red-500 hover:text-red-400' : 'border-slate-200 bg-white text-slate-500 hover:border-red-400 hover:text-red-500'">
                <i class="fa-solid fa-minus mr-1"></i>Row
              </button>
              <button @click="$emit('remove-table-column')" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold border transition-all"
                      :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-red-500 hover:text-red-400' : 'border-slate-200 bg-white text-slate-500 hover:border-red-400 hover:text-red-500'">
                <i class="fa-solid fa-minus mr-1"></i>Col
              </button>
            </div>
          </PanelSection>
        </template>

        <!-- CHART SPECIFIC -->
        <template v-if="isChartEl">
          <PanelSection title="Chart Data" icon="fa-solid fa-chart-bar" :dark="dark" defaultOpen>
            <div class="space-y-2">
              <PropField label="Chart Title" :dark="dark">
                <input type="text" :value="selectedElement.chartTitle || ''"
                       @input="selectedElement.chartTitle = $event.target.value; $emit('push-history')"
                       :class="inputCls" placeholder="Chart title…"/>
              </PropField>
              <PropField label="Labels (comma-separated)" :dark="dark">
                <textarea :value="chartLabelsInput" rows="2"
                          @input="$emit('update:chart-labels-input', $event.target.value)"
                          :class="inputCls + ' resize-none !h-auto'" placeholder="Q1, Q2, Q3, Q4"/>
              </PropField>
              <PropField label="Values (comma-separated)" :dark="dark">
                <textarea :value="chartValuesInput" rows="2"
                          @input="$emit('update:chart-values-input', $event.target.value)"
                          :class="inputCls + ' resize-none !h-auto'" placeholder="42, 68, 55, 81"/>
              </PropField>
              <PropField label="Dataset Label" :dark="dark">
                <input type="text" :value="chartDatasetLabel"
                       @input="$emit('update:chart-dataset-label', $event.target.value)"
                       :class="inputCls" placeholder="Revenue"/>
              </PropField>
              <PropField v-if="!isPieChart" label="Chart Color" :dark="dark">
                <div class="flex gap-2">
                  <input type="color" :value="chartColor"
                         @input="$emit('update:chart-color', $event.target.value)"
                         class="w-9 h-9 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                         :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'"/>
                  <input type="text" :value="chartColor"
                         @input="$emit('update:chart-color', $event.target.value)"
                         :class="inputCls + ' font-mono'"/>
                </div>
              </PropField>
              <PropField v-if="isPieChart" label="Slice Colors (comma-separated hex)" :dark="dark">
                <input type="text" :value="pieColorsInput"
                       @input="$emit('update:pie-colors-input', $event.target.value)"
                       :class="inputCls + ' font-mono'" placeholder="#6366f1, #10b981, #f59e0b"/>
              </PropField>
              <PropField label="Legend Position" :dark="dark">
                <select :value="selectedElement.legendPosition || 'bottom'"
                        @change="selectedElement.legendPosition = $event.target.value; $emit('push-history')"
                        :class="inputCls">
                  <option value="top">Top</option>
                  <option value="bottom">Bottom</option>
                  <option value="left">Left</option>
                  <option value="right">Right</option>
                  <option value="none">None</option>
                </select>
              </PropField>
              <button @click="$emit('update-chart-data'); $emit('refresh-chart')"
                      class="w-full py-2 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-500 transition-all hover:scale-[1.02] active:scale-95 shadow-md shadow-indigo-500/30">
                <i class="fa-solid fa-rotate mr-1.5"></i>Apply & Refresh Chart
              </button>
            </div>
          </PanelSection>
        </template>

        <!-- CALLOUT SPECIFIC -->
        <template v-if="selectedElement.type === 'callout'">
          <PanelSection title="Callout Style" icon="fa-solid fa-circle-info" :dark="dark" defaultOpen>
            <div class="grid grid-cols-3 gap-1.5">
              <button v-for="(style, key) in CALLOUT_PRESETS" :key="key"
                      @click="selectedElement.calloutStyle = key; $emit('apply-callout-style', selectedElement)"
                      class="py-2 rounded-xl border text-[10px] font-bold flex flex-col items-center gap-1 transition-all hover:scale-105"
                      :class="selectedElement.calloutStyle === key
                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600'
                        : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-slate-600' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300')">
                <span>{{ style.emoji }}</span>
                <span class="capitalize">{{ key }}</span>
              </button>
            </div>
            <PropField label="Emoji/Icon" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.emoji || '💡'"
                     @input="selectedElement.emoji = $event.target.value; $emit('push-history')"
                     :class="inputCls"/>
            </PropField>
          </PanelSection>
        </template>

        <!-- BADGE SPECIFIC -->
        <template v-if="selectedElement.type === 'badge'">
          <PanelSection title="Badge" icon="fa-solid fa-tag" :dark="dark" defaultOpen>
            <PropField label="Label" :dark="dark">
              <input type="text" :value="selectedElement.content || ''"
                     @input="selectedElement.content = $event.target.value; $emit('push-history')"
                     :class="inputCls" placeholder="Status label…"/>
            </PropField>
          </PanelSection>
        </template>

        <!-- SIGNATURE -->
        <template v-if="selectedElement.type === 'signature'">
          <PanelSection title="Signature" icon="fa-solid fa-signature" :dark="dark" defaultOpen>
            <PropField label="Name" :dark="dark">
              <input type="text" :value="selectedElement.content || ''"
                     @input="selectedElement.content = $event.target.value; $emit('push-history')"
                     :class="inputCls" placeholder="Name…"/>
            </PropField>
            <PropField label="Label" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.label || 'Authorised Signature'"
                     @input="selectedElement.label = $event.target.value; $emit('push-history')"
                     :class="inputCls"/>
            </PropField>
          </PanelSection>
        </template>

        <!-- LINK SPECIFIC -->
        <template v-if="selectedElement.type === 'link'">
          <PanelSection title="Hyperlink" icon="fa-solid fa-link" :dark="dark" defaultOpen>
            <PropField label="URL" :dark="dark">
              <input type="url" :value="selectedElement.href || ''"
                     @input="selectedElement.href = $event.target.value; $emit('push-history')"
                     :class="inputCls" placeholder="https://…"/>
            </PropField>
            <PropField label="Display Text" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.content || ''"
                     @input="selectedElement.content = $event.target.value; $emit('push-history')"
                     :class="inputCls" placeholder="Click here"/>
            </PropField>
          </PanelSection>
        </template>

        <!-- WATERMARK SPECIFIC -->
        <template v-if="selectedElement.type === 'watermark'">
          <PanelSection title="Watermark" icon="fa-solid fa-droplet" :dark="dark" defaultOpen>
            <PropField label="Text" :dark="dark">
              <input type="text" :value="selectedElement.content || 'CONFIDENTIAL'"
                     @input="selectedElement.content = $event.target.value; $emit('push-history')"
                     :class="inputCls"/>
            </PropField>
          </PanelSection>
        </template>

      </div>

      <!-- Bottom: element type label -->
      <div class="px-4 py-2.5 border-t shrink-0 flex items-center justify-between"
           :class="dark ? 'border-slate-800 bg-slate-900/50' : 'border-slate-100 bg-slate-50'">
        <span class="text-[9px] font-bold uppercase tracking-widest"
              :class="dark ? 'text-slate-700' : 'text-slate-400'">
          ID: {{ selectedElement.id.slice(0,12) }}…
        </span>
        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full"
              :class="dark ? 'bg-slate-800 text-slate-500' : 'bg-slate-200 text-slate-500'">
          {{ selectedElement.type }}
        </span>
      </div>
    </template>
  </aside>
</template>

<script setup>
import { computed, inject, defineAsyncComponent } from 'vue';

const props = defineProps({
  dark: Boolean,
  selectedElement: Object,
  pages: Array,
  selectedPageIndex: Number,
  rs: Object,
  inputCls: String,
  chartLabelsInput: String,
  chartValuesInput: String,
  chartDatasetLabel: String,
  chartColor: String,
  pieColorsInput: String,
});

const emit = defineEmits([
  'deselect-all','push-history','add-element','delete-selected','bring-to-front',
  'send-to-back','bring-forward','send-backward','align-element','move-element-to-page',
  'update:chart-labels-input','update:chart-values-input','update:chart-dataset-label',
  'update:chart-color','update:pie-colors-input','update-chart-data','refresh-chart',
  'upload-image','apply-callout-style',
  'add-table-row','add-table-column','remove-table-row','remove-table-column',
]);

const FONTS            = inject('FONTS');
const FONT_WEIGHTS     = inject('FONT_WEIGHTS');
const CALLOUT_PRESETS  = inject('CALLOUT_PRESETS');
const SHORTCUTS        = inject('SHORTCUTS');

const TEXT_TYPES = ['heading','subheading','text','quote','blockquote','highlight','callout','alert','badge','link','code'];
const CHART_TYPES = ['bar-chart','line-chart','pie-chart','doughnut-chart','area-chart','radar-chart'];
const PIE_TYPES = ['pie-chart','doughnut-chart'];

const isTextType = computed(() => TEXT_TYPES.includes(props.selectedElement?.type));
const isChartEl  = computed(() => CHART_TYPES.includes(props.selectedElement?.type));
const isPieChart = computed(() => PIE_TYPES.includes(props.selectedElement?.type));

const TEXT_STYLES = [
  { key: 'fontWeight',     active: '700',    normal: '400', icon: 'fa-solid fa-bold',      label: 'Bold' },
  { key: 'fontStyle',      active: 'italic', normal: 'normal', icon: 'fa-solid fa-italic', label: 'Italic' },
  { key: 'textDecoration', active: 'underline', normal: 'none', icon: 'fa-solid fa-underline', label: 'Underline' },
  { key: 'textDecoration', active: 'line-through', normal: 'none', icon: 'fa-solid fa-strikethrough', label: 'Strikethrough' },
];

const TEXT_ALIGNS = [
  { val: 'left',    icon: 'fa-solid fa-align-left',    label: 'Left' },
  { val: 'center',  icon: 'fa-solid fa-align-center',  label: 'Center' },
  { val: 'right',   icon: 'fa-solid fa-align-right',   label: 'Right' },
  { val: 'justify', icon: 'fa-solid fa-align-justify',  label: 'Justify' },
];

const ALIGN_BTNS = [
  { dir: 'left',     icon: 'fa-solid fa-objects-align-left',   label: 'Align Left' },
  { dir: 'center-h', icon: 'fa-solid fa-objects-align-center-horizontal', label: 'Center H' },
  { dir: 'right',    icon: 'fa-solid fa-objects-align-right',  label: 'Align Right' },
  { dir: 'top',      icon: 'fa-solid fa-objects-align-top',    label: 'Align Top' },
  { dir: 'center-v', icon: 'fa-solid fa-objects-align-center-vertical', label: 'Center V' },
  { dir: 'bottom',   icon: 'fa-solid fa-objects-align-bottom', label: 'Align Bottom' },
];

const LAYER_BTNS = [
  { action: 'bring-to-front', icon: 'fa-solid fa-angles-up',   label: 'Top' },
  { action: 'bring-forward',  icon: 'fa-solid fa-angle-up',    label: 'Fwd' },
  { action: 'send-backward',  icon: 'fa-solid fa-angle-down',  label: 'Back' },
  { action: 'send-to-back',   icon: 'fa-solid fa-angles-down', label: 'Bottom' },
];

const SHADOW_OPTIONS = [
  { value: 'none', label: 'None',   preview: 'none' },
  { value: 'sm',   label: 'Small',  preview: '0 1px 3px rgba(0,0,0,.15)' },
  { value: 'md',   label: 'Medium', preview: '0 4px 12px rgba(0,0,0,.12)' },
  { value: 'lg',   label: 'Large',  preview: '0 10px 30px rgba(0,0,0,.18)' },
  { value: 'xl',   label: 'XL',     preview: '0 20px 60px rgba(0,0,0,.22)' },
  { value: '2xl',  label: '2XL',    preview: '0 25px 80px rgba(0,0,0,.28)' },
  { value: 'glow-indigo', label: 'Glow', preview: '0 0 20px rgba(99,102,241,.5)' },
  { value: 'glow-emerald', label: 'G.Em', preview: '0 0 20px rgba(16,185,129,.5)' },
  { value: 'glow-gold',   label: 'G.Gold',preview: '0 0 20px rgba(245,158,11,.5)' },
];

const isTextStyleActive = (st) => {
  const val = props.selectedElement?.styles?.[st.key];
  return val === st.active;
};

const toggleTextStyle = (st) => {
  if (!props.selectedElement?.styles) return;
  const current = props.selectedElement.styles[st.key];
  props.selectedElement.styles[st.key] = current === st.active ? st.normal : st.active;
  emit('push-history');
};
</script>

<!-- Sub-components defined inline for single-file simplicity -->
<script>
// PanelSection component
export const PanelSection = {
  name: 'PanelSection',
  props: { title: String, icon: String, dark: Boolean, defaultOpen: Boolean },
  data() { return { open: this.defaultOpen ?? false }; },
  template: `
    <div class="border-b" :class="dark ? 'border-slate-800' : 'border-slate-100'">
      <button @click="open = !open"
              class="w-full flex items-center justify-between px-4 py-2.5 transition-colors"
              :class="dark ? 'hover:bg-white/5' : 'hover:bg-slate-50'">
        <div class="flex items-center gap-2">
          <i :class="icon + ' text-[11px]'" :style="open ? 'color:#6366f1' : (dark ? 'color:#475569' : 'color:#94a3b8')"></i>
          <span class="text-[10px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-400' : 'text-slate-600'">{{ title }}</span>
        </div>
        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
           :class="[open ? 'rotate-180' : '', dark ? 'text-slate-600' : 'text-slate-400']"></i>
      </button>
      <div v-show="open" class="px-4 pb-4 pt-1 space-y-2">
        <slot/>
      </div>
    </div>
  `
};

// PropField component
export const PropField = {
  name: 'PropField',
  props: { label: String, dark: Boolean },
  template: `
    <div>
      <label class="block text-[9px] font-black uppercase tracking-wider mb-1"
             :class="dark ? 'text-slate-600' : 'text-slate-400'">{{ label }}</label>
      <slot/>
    </div>
  `
};

// ColorRow component
export const ColorRow = {
  name: 'ColorRow',
  props: { label: String, dark: Boolean, value: String },
  emits: ['update'],
  template: `
    <div>
      <label class="block text-[9px] font-black uppercase tracking-wider mb-1"
             :class="dark ? 'text-slate-600' : 'text-slate-400'">{{ label }}</label>
      <div class="flex items-center gap-2">
        <input type="color" :value="value"
               @input="$emit('update', $event.target.value)"
               class="w-9 h-9 rounded-xl border cursor-pointer p-0.5 flex-shrink-0"
               :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'"/>
        <input type="text" :value="value"
               @input="$emit('update', $event.target.value)"
               class="flex-1 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
               :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'"/>
        <!-- Transparent option -->
        <button @click="$emit('update', 'transparent')"
                class="w-8 h-8 rounded-lg border flex items-center justify-center flex-shrink-0 transition-all hover:border-indigo-400"
                :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200 bg-white'"
                title="Transparent">
          <span class="text-[10px] font-black" :class="dark ? 'text-slate-500' : 'text-slate-400'">∅</span>
        </button>
      </div>
    </div>
  `
};
</script>

<style scoped>
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #475569; border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: #6366f1; }

/* Smooth section expand/collapse */
[v-show] { transition: all 0.2s ease; }

/* Input focus glow */
input:focus, select:focus, textarea:focus {
  box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}

/* Animate panel entrance */
aside {
  animation: slideInRight 0.2s ease;
}

@keyframes slideInRight {
  from { opacity: 0; transform: translateX(16px); }
  to   { opacity: 1; transform: translateX(0); }
}
</style>