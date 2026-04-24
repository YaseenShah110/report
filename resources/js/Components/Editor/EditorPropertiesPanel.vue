<template>
  <aside
    class="w-72 flex flex-col overflow-hidden shrink-0 border-l transition-colors duration-200"
    :class="dark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'"
  >
    <!-- No selection state -->
    <template v-if="!selectedElement">
      <div class="flex-1 flex flex-col items-center justify-center gap-3 px-5 py-8 text-center">
        <div
          class="w-14 h-14 rounded-2xl flex items-center justify-center"
          :class="dark ? 'bg-slate-800 border border-slate-700' : 'bg-slate-50 border border-slate-200'"
        >
          <i class="fa-solid fa-arrow-pointer text-xl" :class="dark ? 'text-slate-600' : 'text-slate-300'"></i>
        </div>
        <div>
          <p class="text-xs font-black" :class="dark ? 'text-slate-500' : 'text-slate-400'">Select an element</p>
          <p class="text-[10px] mt-0.5" :class="dark ? 'text-slate-700' : 'text-slate-400'">Click any element to edit its properties</p>
        </div>
        <div class="w-full mt-1 space-y-1">
          <div
            v-for="sc in SHORTCUTS.slice(0, 8)"
            :key="sc.key"
            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg"
            :class="dark ? 'bg-slate-800/50' : 'bg-slate-50'"
          >
            <span class="text-[10px]" :class="dark ? 'text-slate-500' : 'text-slate-400'">{{ sc.desc }}</span>
            <kbd
              class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded"
              :class="dark ? 'bg-slate-700 text-slate-400' : 'bg-white text-slate-500 border border-slate-200'"
            >{{ sc.key }}</kbd>
          </div>
        </div>
      </div>
    </template>

    <!-- Element selected -->
    <template v-else>
      <!-- Panel header -->
      <div
        class="px-3 py-2.5 border-b flex items-center justify-between shrink-0"
        :class="dark ? 'border-slate-800' : 'border-slate-100'"
      >
        <div class="flex items-center gap-2 min-w-0">
          <div
            class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
            :class="dark ? 'bg-indigo-900/40' : 'bg-indigo-50'"
          >
            <i :class="getElementIcon(selectedElement.type) + ' text-xs text-indigo-500'"></i>
          </div>
          <div class="min-w-0">
            <p class="text-[11px] font-black capitalize truncate" :class="dark ? 'text-slate-200' : 'text-slate-700'">
              {{ selectedElement.type.replace(/-/g, ' ') }}
            </p>
            <p class="text-[9px] font-mono truncate" :class="dark ? 'text-slate-600' : 'text-slate-400'">
              {{ selectedElement.id.slice(0, 10) }}…
            </p>
          </div>
        </div>
        <div class="flex items-center gap-0.5 shrink-0">
          <button
            @click="selectedElement.hidden = !selectedElement.hidden; $emit('push-history')"
            :title="selectedElement.hidden ? 'Show' : 'Hide'"
            class="p-1.5 rounded-lg transition-all"
            :class="selectedElement.hidden
              ? 'bg-amber-500/20 text-amber-500'
              : dark ? 'text-slate-500 hover:text-slate-300 hover:bg-white/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
          >
            <i :class="selectedElement.hidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
          </button>
          <button
            @click="selectedElement.locked = !selectedElement.locked; $emit('push-history')"
            :title="selectedElement.locked ? 'Unlock' : 'Lock'"
            class="p-1.5 rounded-lg transition-all"
            :class="selectedElement.locked
              ? 'bg-amber-500/20 text-amber-500'
              : dark ? 'text-slate-500 hover:text-slate-300 hover:bg-white/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
          >
            <i :class="selectedElement.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" class="text-xs"></i>
          </button>
          <button
            @click="$emit('delete-selected')"
            class="p-1.5 rounded-lg transition-all"
            :class="dark ? 'text-slate-500 hover:text-red-400 hover:bg-red-500/10' : 'text-slate-400 hover:text-red-500 hover:bg-red-50'"
          >
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex border-b shrink-0" :class="dark ? 'border-slate-800' : 'border-slate-200'">
        <button
          v-for="tab in propTabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="flex-1 py-2 text-[10px] font-black uppercase tracking-wider border-b-2 transition-colors"
          :class="activeTab === tab.id
            ? 'border-indigo-500 text-indigo-500'
            : dark ? 'border-transparent text-slate-600 hover:text-slate-400' : 'border-transparent text-slate-400 hover:text-slate-600'"
        >
          <i :class="tab.icon + ' mr-0.5'"></i> {{ tab.label }}
        </button>
      </div>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto">

        <!-- ═══ STYLE TAB ═══ -->
        <template v-if="activeTab === 'style'">

          <!-- POSITION & SIZE -->
          <PanelSection title="Position & Size" icon="fa-solid fa-up-down-left-right" :dark="dark" :defaultOpen="true">
            <div class="grid grid-cols-2 gap-2">
              <PropField label="X (px)" icon="fa-solid fa-arrows-left-right" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.position.x)"
                  @input="selectedElement.position.x = +$event.target.value; $emit('push-history')"
                  :class="inputCls" step="1" />
              </PropField>
              <PropField label="Y (px)" icon="fa-solid fa-arrows-up-down" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.position.y)"
                  @input="selectedElement.position.y = +$event.target.value; $emit('push-history')"
                  :class="inputCls" step="1" />
              </PropField>
              <PropField label="Width" icon="fa-solid fa-left-right" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.styles?.width || 200)"
                  @input="selectedElement.styles.width = +$event.target.value; $emit('push-history')"
                  :class="inputCls" step="1" min="10" />
              </PropField>
              <PropField label="Height" icon="fa-solid fa-up-down" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.styles?.height || 50)"
                  @input="selectedElement.styles.height = +$event.target.value; $emit('push-history')"
                  :class="inputCls" step="1" min="10" />
              </PropField>
            </div>
            <PropField label="Rotation (°)" icon="fa-solid fa-rotate" :dark="dark" class="mt-2">
              <div class="flex items-center gap-2">
                <input type="range" :value="selectedElement.styles?.rotate || 0" min="-180" max="180" step="1"
                  @input="selectedElement.styles.rotate = +$event.target.value; $emit('push-history')"
                  class="flex-1 accent-indigo-600 h-1.5" />
                <input type="number" :value="selectedElement.styles?.rotate || 0" min="-180" max="180"
                  @input="selectedElement.styles.rotate = +$event.target.value; $emit('push-history')"
                  class="w-14 text-[11px] text-center rounded-lg border px-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  :class="dark ? 'bg-slate-800 border-slate-700 text-slate-200' : 'bg-white border-slate-200 text-slate-700'" />
              </div>
            </PropField>
          </PanelSection>

          <!-- COLOR & FILL -->
          <PanelSection title="Color & Fill" icon="fa-solid fa-palette" :dark="dark" :defaultOpen="true">
            <ColorRow v-if="isTextType" label="Text Color" icon="fa-solid fa-a" :dark="dark"
              :value="selectedElement.styles?.color || '#1e293b'"
              @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
            <ColorRow label="Background" icon="fa-solid fa-fill-drip" :dark="dark"
              :value="selectedElement.styles?.backgroundColor || 'transparent'"
              @update="v => { selectedElement.styles.backgroundColor = v; $emit('push-history') }" />
            <div class="mt-2">
              <div class="flex items-center justify-between mb-1">
                <label class="text-[9px] font-black uppercase tracking-wider flex items-center gap-1"
                  :class="dark ? 'text-slate-500' : 'text-slate-400'">
                  <i class="fa-solid fa-droplet-slash"></i> Opacity
                </label>
                <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.styles?.opacity ?? 100 }}%</span>
              </div>
              <input type="range" :value="selectedElement.styles?.opacity ?? 100" min="0" max="100" step="1"
                @input="selectedElement.styles.opacity = +$event.target.value; $emit('push-history')"
                class="w-full accent-indigo-600 h-1.5" />
            </div>
          </PanelSection>

          <!-- BORDER -->
          <PanelSection title="Border" icon="fa-solid fa-border-all" :dark="dark">
            <div class="grid grid-cols-2 gap-2">
              <PropField label="Width (px)" icon="fa-solid fa-minus" :dark="dark">
                <input type="number" :value="selectedElement.styles?.borderWidth || 0"
                  @input="selectedElement.styles.borderWidth = +$event.target.value; $emit('push-history')"
                  :class="inputCls" min="0" max="20" />
              </PropField>
              <PropField label="Style" icon="fa-solid fa-pen-ruler" :dark="dark">
                <select :value="selectedElement.styles?.borderStyle || 'solid'"
                  @change="selectedElement.styles.borderStyle = $event.target.value; $emit('push-history')"
                  :class="inputCls">
                  <option value="solid">Solid</option>
                  <option value="dashed">Dashed</option>
                  <option value="dotted">Dotted</option>
                  <option value="double">Double</option>
                  <option value="groove">Groove</option>
                  <option value="ridge">Ridge</option>
                </select>
              </PropField>
            </div>
            <ColorRow v-if="(selectedElement.styles?.borderWidth || 0) > 0" label="Border Color" icon="fa-solid fa-border-style" :dark="dark"
              :value="selectedElement.styles?.borderColor || '#e2e8f0'"
              @update="v => { selectedElement.styles.borderColor = v; $emit('push-history') }" />
            <PropField label="Border Radius (px)" icon="fa-solid fa-circle-notch" :dark="dark" class="mt-2">
              <div class="flex items-center gap-2">
                <input type="range" :value="selectedElement.styles?.borderRadius || 0" min="0" max="64" step="1"
                  @input="selectedElement.styles.borderRadius = +$event.target.value; $emit('push-history')"
                  class="flex-1 accent-indigo-600 h-1.5" />
                <span class="text-[10px] font-black w-8 text-right" :class="dark ? 'text-indigo-400' : 'text-indigo-600'">
                  {{ selectedElement.styles?.borderRadius || 0 }}
                </span>
              </div>
            </PropField>
          </PanelSection>

          <!-- SHADOW -->
          <PanelSection title="Shadow & Effects" icon="fa-solid fa-layer-group" :dark="dark">
            <div class="grid grid-cols-3 gap-1.5">
              <button v-for="sh in SHADOW_OPTIONS" :key="sh.value"
                @click="selectedElement.styles.boxShadow = sh.value; $emit('push-history')"
                class="py-2.5 rounded-xl border flex flex-col items-center gap-1 transition-all hover:scale-105 text-[9px] font-bold"
                :class="selectedElement.styles?.boxShadow === sh.value
                  ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-500/30'
                  : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-400')">
                <span :style="{ width: '24px', height: '14px', borderRadius: '4px', background: dark ? '#475569' : '#e2e8f0', boxShadow: sh.preview, display: 'block' }"></span>
                {{ sh.label }}
              </button>
            </div>
          </PanelSection>

          <!-- SPACING -->
          <PanelSection title="Spacing" icon="fa-solid fa-maximize" :dark="dark">
            <PropField label="Padding (px)" icon="fa-solid fa-compress-arrows-alt" :dark="dark">
              <input type="number" :value="selectedElement.styles?.padding || 0"
                @input="selectedElement.styles.padding = +$event.target.value; $emit('push-history')"
                :class="inputCls" min="0" max="80" />
            </PropField>
          </PanelSection>

          <!-- TYPOGRAPHY (for text types) -->
          <template v-if="isTextType">
            <PanelSection title="Typography" icon="fa-solid fa-font" :dark="dark" :defaultOpen="true">
              <PropField label="Font Family" icon="fa-solid fa-text-height" :dark="dark">
                <select :value="selectedElement.styles?.fontFamily || rs.font_family"
                  @change="selectedElement.styles.fontFamily = $event.target.value; $emit('push-history')"
                  :class="inputCls">
                  <option v-for="f in FONTS" :key="f.value" :value="f.value">{{ f.name }}</option>
                </select>
              </PropField>
              <div class="grid grid-cols-2 gap-2 mt-2">
                <PropField label="Size (px)" icon="fa-solid fa-text-size" :dark="dark">
                  <input type="number" :value="selectedElement.styles?.fontSize || 14"
                    @input="selectedElement.styles.fontSize = +$event.target.value; $emit('push-history')"
                    :class="inputCls" min="6" max="300" />
                </PropField>
                <PropField label="Weight" icon="fa-solid fa-bold" :dark="dark">
                  <select :value="selectedElement.styles?.fontWeight || '400'"
                    @change="selectedElement.styles.fontWeight = $event.target.value; $emit('push-history')"
                    :class="inputCls">
                    <option v-for="fw in FONT_WEIGHTS" :key="fw.value" :value="fw.value">{{ fw.label }}</option>
                  </select>
                </PropField>
                <PropField label="Line Height" icon="fa-solid fa-text-height" :dark="dark">
                  <input type="number" :value="selectedElement.styles?.lineHeight || 1.5"
                    @input="selectedElement.styles.lineHeight = +$event.target.value; $emit('push-history')"
                    :class="inputCls" min="0.8" max="4" step="0.1" />
                </PropField>
                <PropField label="Letter Spacing" icon="fa-solid fa-arrows-left-right-to-line" :dark="dark">
                  <input type="number" :value="selectedElement.styles?.letterSpacing || 0"
                    @input="selectedElement.styles.letterSpacing = +$event.target.value; $emit('push-history')"
                    :class="inputCls" min="-5" max="20" step="0.5" />
                </PropField>
              </div>
              <!-- Style toggles -->
              <div class="flex items-center gap-1 mt-2">
                <button v-for="st in TEXT_STYLES" :key="st.key + st.active"
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
              <PropField label="Text Transform" icon="fa-solid fa-text-height" :dark="dark" class="mt-2">
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
        </template>

        <!-- ═══ CONTENT TAB ═══ -->
        <template v-else-if="activeTab === 'content'">

          <!-- CHART DATA -->
          <template v-if="isChartEl">
            <PanelSection title="Chart Data" icon="fa-solid fa-chart-bar" :dark="dark" :defaultOpen="true">
              <div class="space-y-2">
                <PropField label="Chart Title" icon="fa-solid fa-heading" :dark="dark">
                  <input type="text" :value="selectedElement.chartTitle || ''"
                    @input="selectedElement.chartTitle = $event.target.value; $emit('push-history')"
                    :class="inputCls" placeholder="Chart title…" />
                </PropField>
                <PropField label="Labels (comma-separated)" icon="fa-solid fa-tags" :dark="dark">
                  <textarea :value="chartLabelsInput" rows="2"
                    @input="$emit('update:chart-labels-input', $event.target.value)"
                    :class="inputCls + ' resize-none'" placeholder="Q1, Q2, Q3, Q4" />
                </PropField>
                <PropField label="Values (comma-separated)" icon="fa-solid fa-hashtag" :dark="dark">
                  <textarea :value="chartValuesInput" rows="2"
                    @input="$emit('update:chart-values-input', $event.target.value)"
                    :class="inputCls + ' resize-none'" placeholder="42, 68, 55, 81" />
                </PropField>
                <PropField label="Dataset Label" icon="fa-solid fa-tag" :dark="dark">
                  <input type="text" :value="chartDatasetLabel"
                    @input="$emit('update:chart-dataset-label', $event.target.value)"
                    :class="inputCls" placeholder="Revenue" />
                </PropField>
                <PropField v-if="!isPieChart" label="Chart Color" icon="fa-solid fa-palette" :dark="dark">
                  <div class="flex gap-2">
                    <input type="color" :value="chartColor"
                      @input="$emit('update:chart-color', $event.target.value)"
                      class="w-9 h-9 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                      :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
                    <input type="text" :value="chartColor"
                      @input="$emit('update:chart-color', $event.target.value)"
                      :class="inputCls + ' font-mono'" />
                  </div>
                </PropField>
                <PropField v-if="isPieChart" label="Slice Colors (hex, comma-sep)" icon="fa-solid fa-circle-half-stroke" :dark="dark">
                  <input type="text" :value="pieColorsInput"
                    @input="$emit('update:pie-colors-input', $event.target.value)"
                    :class="inputCls + ' font-mono'" placeholder="#6366f1, #10b981, #f59e0b" />
                </PropField>
                <PropField label="Legend Position" icon="fa-solid fa-list" :dark="dark">
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
                  class="w-full py-2.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-500 transition-all hover:scale-[1.02] active:scale-95 shadow-md shadow-indigo-500/30">
                  <i class="fa-solid fa-rotate mr-1.5"></i>Apply & Refresh Chart
                </button>
              </div>
            </PanelSection>
          </template>

          <!-- METRIC / KPI -->
          <template v-if="selectedElement.type === 'metric'">
            <PanelSection title="KPI Card Data" icon="fa-solid fa-chart-line" :dark="dark" :defaultOpen="true">
              <div class="space-y-2">
                <PropField label="Label" icon="fa-solid fa-tag" :dark="dark">
                  <input type="text" :value="selectedElement.label || ''"
                    @input="selectedElement.label = $event.target.value; $emit('push-history')"
                    :class="inputCls" placeholder="e.g. Revenue" />
                </PropField>
                <PropField label="Value" icon="fa-solid fa-dollar-sign" :dark="dark">
                  <input type="text" :value="selectedElement.value || ''"
                    @input="selectedElement.value = $event.target.value; $emit('push-history')"
                    :class="inputCls" placeholder="e.g. $48,200" />
                </PropField>
                <div class="grid grid-cols-2 gap-2">
                  <PropField label="Change" icon="fa-solid fa-arrow-trend-up" :dark="dark">
                    <input type="text" :value="selectedElement.change || ''"
                      @input="selectedElement.change = $event.target.value; $emit('push-history')"
                      :class="inputCls" placeholder="12.5%" />
                  </PropField>
                  <PropField label="Direction" icon="fa-solid fa-circle-arrow-up" :dark="dark">
                    <select :value="selectedElement.changeType || 'positive'"
                      @change="selectedElement.changeType = $event.target.value; $emit('push-history')"
                      :class="inputCls">
                      <option value="positive">▲ Up</option>
                      <option value="negative">▼ Down</option>
                    </select>
                  </PropField>
                </div>
                <PropField label="Period Label" icon="fa-solid fa-calendar" :dark="dark">
                  <input type="text" :value="selectedElement.changePeriod || ''"
                    @input="selectedElement.changePeriod = $event.target.value; $emit('push-history')"
                    :class="inputCls" placeholder="vs last month" />
                </PropField>
                <ColorRow label="Accent Color" icon="fa-solid fa-circle" :dark="dark"
                  :value="selectedElement.styles?.color || rs.primary_color"
                  @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
              </div>
            </PanelSection>
          </template>

          <!-- PROGRESS -->
          <template v-if="selectedElement.type === 'progress'">
            <PanelSection title="Progress Bar" icon="fa-solid fa-bars-progress" :dark="dark" :defaultOpen="true">
              <PropField label="Label" icon="fa-solid fa-tag" :dark="dark">
                <input type="text" :value="selectedElement.label || ''"
                  @input="selectedElement.label = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
              <div class="mt-2">
                <div class="flex items-center justify-between mb-1">
                  <label class="text-[9px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                    <i class="fa-solid fa-percent mr-1"></i>Value
                  </label>
                  <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.value ?? 0 }}%</span>
                </div>
                <input type="range" :value="selectedElement.value ?? 0" min="0" max="100" step="1"
                  @input="selectedElement.value = +$event.target.value; $emit('push-history')"
                  class="w-full accent-indigo-600 h-1.5" />
              </div>
              <ColorRow label="Fill Color" icon="fa-solid fa-fill" :dark="dark"
                :value="selectedElement.styles?.color || rs.primary_color"
                @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
              <ColorRow label="Track Color" icon="fa-solid fa-road" :dark="dark"
                :value="selectedElement.styles?.trackColor || '#e2e8f0'"
                @update="v => { selectedElement.styles.trackColor = v; $emit('push-history') }" />
            </PanelSection>
          </template>

          <!-- CIRCULAR PROGRESS -->
          <template v-if="selectedElement.type === 'circular-progress'">
            <PanelSection title="Circular Progress" icon="fa-solid fa-circle-notch" :dark="dark" :defaultOpen="true">
              <PropField label="Label" icon="fa-solid fa-tag" :dark="dark">
                <input type="text" :value="selectedElement.label || ''"
                  @input="selectedElement.label = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
              <div class="mt-2">
                <div class="flex items-center justify-between mb-1">
                  <label class="text-[9px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-500' : 'text-slate-400'">
                    <i class="fa-solid fa-percent mr-1"></i>Value
                  </label>
                  <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.value ?? 0 }}%</span>
                </div>
                <input type="range" :value="selectedElement.value ?? 0" min="0" max="100" step="1"
                  @input="selectedElement.value = +$event.target.value; $emit('push-history')"
                  class="w-full accent-indigo-600 h-1.5" />
              </div>
              <ColorRow label="Progress Color" icon="fa-solid fa-circle" :dark="dark"
                :value="selectedElement.styles?.color || rs.primary_color"
                @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
              <ColorRow label="Track Color" icon="fa-regular fa-circle" :dark="dark"
                :value="selectedElement.styles?.trackColor || '#e2e8f0'"
                @update="v => { selectedElement.styles.trackColor = v; $emit('push-history') }" />
            </PanelSection>
          </template>

          <!-- STAT ROW -->
          <template v-if="selectedElement.type === 'stat-row'">
            <PanelSection title="Stats Row" icon="fa-solid fa-table-columns" :dark="dark" :defaultOpen="true">
              <div v-for="(stat, i) in selectedElement.stats" :key="i" class="border rounded-xl p-2 space-y-1.5 mb-2"
                :class="dark ? 'border-slate-700' : 'border-slate-200'">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-black" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                    <i class="fa-solid fa-chart-simple mr-1"></i>Stat {{ i + 1 }}
                  </span>
                  <button @click="selectedElement.stats.splice(i, 1); $emit('push-history')"
                    class="text-[9px] text-red-400 hover:text-red-600">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
                <input type="text" v-model="stat.label" :class="inputCls" placeholder="Label" />
                <input type="text" v-model="stat.value" :class="inputCls" placeholder="Value" />
              </div>
              <button @click="selectedElement.stats.push({ label: 'New Stat', value: '0' }); $emit('push-history')"
                class="w-full py-1.5 rounded-lg text-[10px] font-bold border-2 border-dashed transition-all"
                :class="dark ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400'">
                <i class="fa-solid fa-plus mr-1"></i>Add Stat
              </button>
            </PanelSection>
          </template>

          <!-- TABLE -->
          <template v-if="selectedElement.type === 'table'">
            <PanelSection title="Table Settings" icon="fa-solid fa-table" :dark="dark" :defaultOpen="true">
              <ColorRow label="Header BG" icon="fa-solid fa-fill-drip" :dark="dark"
                :value="selectedElement.styles?.headerBg || rs.primary_color"
                @update="v => { selectedElement.styles.headerBg = v; $emit('push-history') }" />
              <ColorRow label="Header Text" icon="fa-solid fa-font" :dark="dark"
                :value="selectedElement.styles?.headerColor || '#ffffff'"
                @update="v => { selectedElement.styles.headerColor = v; $emit('push-history') }" />
              <ColorRow label="Even Row BG" icon="fa-solid fa-table" :dark="dark"
                :value="selectedElement.styles?.evenRowBg || '#ffffff'"
                @update="v => { selectedElement.styles.evenRowBg = v; $emit('push-history') }" />
              <ColorRow label="Odd Row BG" icon="fa-solid fa-table" :dark="dark"
                :value="selectedElement.styles?.oddRowBg || '#f8fafc'"
                @update="v => { selectedElement.styles.oddRowBg = v; $emit('push-history') }" />
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

          <!-- IMAGE -->
          <template v-if="selectedElement.type === 'image'">
            <PanelSection title="Image" icon="fa-solid fa-image" :dark="dark" :defaultOpen="true">
              <PropField label="Object Fit" icon="fa-solid fa-expand" :dark="dark">
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
                  <input type="file" accept="image/*" class="hidden" @change="e => $emit('upload-image', e)" />
                </label>
              </div>
              <PropField label="Image URL" icon="fa-solid fa-link" :dark="dark" class="mt-2">
                <input type="url" :value="selectedElement.src || ''"
                  @input="selectedElement.src = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="https://…" />
              </PropField>
              <PropField label="Alt Text" icon="fa-solid fa-image" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.alt || ''"
                  @input="selectedElement.alt = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="Describe the image…" />
              </PropField>
            </PanelSection>
          </template>

          <!-- CALLOUT -->
          <template v-if="selectedElement.type === 'callout'">
            <PanelSection title="Callout Style" icon="fa-solid fa-circle-info" :dark="dark" :defaultOpen="true">
              <div class="grid grid-cols-3 gap-1.5 mb-3">
                <button v-for="(style, key) in CALLOUT_PRESETS" :key="key"
                  @click="selectedElement.calloutStyle = key; $emit('apply-callout-style', selectedElement)"
                  class="py-2 rounded-xl border text-[10px] font-bold flex flex-col items-center gap-1 transition-all hover:scale-105"
                  :class="selectedElement.calloutStyle === key
                    ? 'border-indigo-500 bg-indigo-50 text-indigo-600'
                    : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-slate-600' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300')">
                  <span>{{ style.emoji }}</span>
                  <span class="capitalize">{{ key }}</span>
                </button>
              </div>
              <PropField label="Emoji/Icon" icon="fa-solid fa-face-smile" :dark="dark">
                <input type="text" :value="selectedElement.emoji || '💡'"
                  @input="selectedElement.emoji = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
            </PanelSection>
          </template>

          <!-- BADGE -->
          <template v-if="selectedElement.type === 'badge'">
            <PanelSection title="Badge" icon="fa-solid fa-tag" :dark="dark" :defaultOpen="true">
              <PropField label="Label Text" icon="fa-solid fa-font" :dark="dark">
                <input type="text" :value="selectedElement.content || ''"
                  @input="selectedElement.content = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="Status label…" />
              </PropField>
            </PanelSection>
          </template>

          <!-- SIGNATURE -->
          <template v-if="selectedElement.type === 'signature'">
            <PanelSection title="Signature" icon="fa-solid fa-signature" :dark="dark" :defaultOpen="true">
              <PropField label="Name" icon="fa-solid fa-user" :dark="dark">
                <input type="text" :value="selectedElement.content || ''"
                  @input="selectedElement.content = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="Name…" />
              </PropField>
              <PropField label="Label" icon="fa-solid fa-tag" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.label || 'Authorised Signature'"
                  @input="selectedElement.label = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
            </PanelSection>
          </template>

          <!-- LINK -->
          <template v-if="selectedElement.type === 'link'">
            <PanelSection title="Hyperlink" icon="fa-solid fa-link" :dark="dark" :defaultOpen="true">
              <PropField label="URL" icon="fa-solid fa-globe" :dark="dark">
                <input type="url" :value="selectedElement.href || ''"
                  @input="selectedElement.href = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="https://…" />
              </PropField>
              <PropField label="Display Text" icon="fa-solid fa-font" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.content || ''"
                  @input="selectedElement.content = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="Click here" />
              </PropField>
              <PropField label="Target" icon="fa-solid fa-arrow-up-right-from-square" :dark="dark" class="mt-2">
                <select :value="selectedElement.target || '_blank'"
                  @change="selectedElement.target = $event.target.value; $emit('push-history')"
                  :class="inputCls">
                  <option value="_blank">New Tab</option>
                  <option value="_self">Same Tab</option>
                </select>
              </PropField>
            </PanelSection>
          </template>

          <!-- WATERMARK -->
          <template v-if="selectedElement.type === 'watermark'">
            <PanelSection title="Watermark" icon="fa-solid fa-droplet" :dark="dark" :defaultOpen="true">
              <PropField label="Text" icon="fa-solid fa-font" :dark="dark">
                <input type="text" :value="selectedElement.content || 'CONFIDENTIAL'"
                  @input="selectedElement.content = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
              <PropField label="Font Size (px)" icon="fa-solid fa-text-height" :dark="dark" class="mt-2">
                <input type="number" :value="selectedElement.styles?.fontSize || 48"
                  @input="selectedElement.styles.fontSize = +$event.target.value; $emit('push-history')"
                  :class="inputCls" min="12" max="200" />
              </PropField>
              <PropField label="Rotation (°)" icon="fa-solid fa-rotate" :dark="dark" class="mt-2">
                <input type="number" :value="selectedElement.styles?.rotate || -30"
                  @input="selectedElement.styles.rotate = +$event.target.value; $emit('push-history')"
                  :class="inputCls" min="-180" max="180" />
              </PropField>
            </PanelSection>
          </template>

          <!-- GENERIC TEXT CONTENT -->
          <template v-if="['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight'].includes(selectedElement.type)">
            <PanelSection title="Content" icon="fa-solid fa-paragraph" :dark="dark" :defaultOpen="true">
              <textarea :value="selectedElement.content || ''"
                @input="selectedElement.content = $event.target.value; $emit('push-history')"
                :class="inputCls + ' resize-none'" rows="4"
                placeholder="Edit content…" />
            </PanelSection>
          </template>

          <!-- CODE BLOCK -->
          <template v-if="selectedElement.type === 'code'">
            <PanelSection title="Code Block" icon="fa-solid fa-code" :dark="dark" :defaultOpen="true">
              <PropField label="Language" icon="fa-solid fa-laptop-code" :dark="dark">
                <select :value="selectedElement.language || 'JavaScript'"
                  @change="selectedElement.language = $event.target.value; $emit('push-history')"
                  :class="inputCls">
                  <option v-for="lang in CODE_LANGS" :key="lang" :value="lang">{{ lang }}</option>
                </select>
              </PropField>
              <textarea :value="selectedElement.content || ''"
                @input="selectedElement.content = $event.target.value; $emit('push-history')"
                :class="inputCls + ' resize-none font-mono text-[11px] mt-2'" rows="6"
                placeholder="Code here…" />
            </PanelSection>
          </template>

          <!-- ICON -->
          <template v-if="selectedElement.type === 'icon'">
            <PanelSection title="Icon / Emoji" icon="fa-solid fa-star" :dark="dark" :defaultOpen="true">
              <PropField label="Icon / Emoji" icon="fa-solid fa-face-smile" :dark="dark">
                <input type="text" :value="selectedElement.content || '★'"
                  @input="selectedElement.content = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="★ or emoji" />
              </PropField>
              <PropField label="Size (px)" icon="fa-solid fa-text-height" :dark="dark" class="mt-2">
                <input type="number" :value="selectedElement.styles?.fontSize || 40"
                  @input="selectedElement.styles.fontSize = +$event.target.value; $emit('push-history')"
                  :class="inputCls" min="8" max="200" />
              </PropField>
              <ColorRow label="Color" icon="fa-solid fa-palette" :dark="dark"
                :value="selectedElement.styles?.color || rs.primary_color"
                @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
            </PanelSection>
          </template>

          <!-- RATING -->
          <template v-if="selectedElement.type === 'rating'">
            <PanelSection title="Star Rating" icon="fa-regular fa-star" :dark="dark" :defaultOpen="true">
              <PropField label="Rating (out of 5)" icon="fa-solid fa-star" :dark="dark">
                <input type="number" :value="selectedElement.value || 4"
                  @input="selectedElement.value = +$event.target.value; $emit('push-history')"
                  :class="inputCls" min="0" max="5" step="0.5" />
              </PropField>
              <ColorRow label="Star Color" icon="fa-solid fa-star" :dark="dark"
                :value="selectedElement.styles?.color || '#f59e0b'"
                @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
            </PanelSection>
          </template>

          <!-- SOCIAL CARD -->
          <template v-if="selectedElement.type === 'social-card'">
            <PanelSection title="Profile Card" icon="fa-solid fa-id-badge" :dark="dark" :defaultOpen="true">
              <PropField label="Avatar (emoji)" icon="fa-solid fa-user" :dark="dark">
                <input type="text" :value="selectedElement.avatar || '👤'"
                  @input="selectedElement.avatar = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
              <PropField label="Name" icon="fa-solid fa-user-pen" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.content || 'Name'"
                  @input="selectedElement.content = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
              <PropField label="Title/Subtitle" icon="fa-solid fa-briefcase" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.subtitle || 'Title'"
                  @input="selectedElement.subtitle = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
            </PanelSection>
          </template>

          <!-- TESTIMONIAL -->
          <template v-if="selectedElement.type === 'testimonial'">
            <PanelSection title="Testimonial" icon="fa-solid fa-comment-dots" :dark="dark" :defaultOpen="true">
              <textarea :value="selectedElement.content || ''"
                @input="selectedElement.content = $event.target.value; $emit('push-history')"
                :class="inputCls + ' resize-none'" rows="3" placeholder="Testimonial text…" />
              <PropField label="Author" icon="fa-solid fa-user" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.author || ''"
                  @input="selectedElement.author = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
              <PropField label="Role" icon="fa-solid fa-briefcase" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.role || ''"
                  @input="selectedElement.role = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
            </PanelSection>
          </template>

          <!-- KANBAN -->
          <template v-if="selectedElement.type === 'kanban'">
            <PanelSection title="Kanban Card" icon="fa-solid fa-thumbtack" :dark="dark" :defaultOpen="true">
              <PropField label="Task Title" icon="fa-solid fa-list-check" :dark="dark">
                <input type="text" :value="selectedElement.content || ''"
                  @input="selectedElement.content = $event.target.value; $emit('push-history')"
                  :class="inputCls" />
              </PropField>
              <PropField label="Status" icon="fa-solid fa-circle-dot" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.status || ''"
                  @input="selectedElement.status = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="In Progress" />
              </PropField>
              <PropField label="Due Date" icon="fa-solid fa-calendar-check" :dark="dark" class="mt-2">
                <input type="text" :value="selectedElement.due || ''"
                  @input="selectedElement.due = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="Dec 31" />
              </PropField>
            </PanelSection>
          </template>

          <!-- PRICE CARD -->
          <template v-if="selectedElement.type === 'price-card'">
            <PanelSection title="Price Card" icon="fa-solid fa-dollar-sign" :dark="dark" :defaultOpen="true">
              <PropField label="Plan Name" icon="fa-solid fa-crown" :dark="dark">
                <input type="text" :value="selectedElement.plan || ''"
                  @input="selectedElement.plan = $event.target.value; $emit('push-history')"
                  :class="inputCls" placeholder="Pro Plan" />
              </PropField>
              <div class="grid grid-cols-2 gap-2 mt-2">
                <PropField label="Price" icon="fa-solid fa-dollar-sign" :dark="dark">
                  <input type="text" :value="selectedElement.price || '$0'"
                    @input="selectedElement.price = $event.target.value; $emit('push-history')"
                    :class="inputCls" />
                </PropField>
                <PropField label="Period" icon="fa-solid fa-calendar" :dark="dark">
                  <input type="text" :value="selectedElement.period || '/month'"
                    @input="selectedElement.period = $event.target.value; $emit('push-history')"
                    :class="inputCls" />
                </PropField>
              </div>
              <div class="mt-2">
                <label class="text-[9px] font-black uppercase tracking-wider mb-1 block flex items-center gap-1"
                  :class="dark ? 'text-slate-500' : 'text-slate-400'">
                  <i class="fa-solid fa-list-check"></i> Features (one per line)
                </label>
                <textarea
                  :value="(selectedElement.features || []).join('\n')"
                  @input="selectedElement.features = $event.target.value.split('\n').filter(Boolean); $emit('push-history')"
                  :class="inputCls + ' resize-none'" rows="4" placeholder="Feature 1&#10;Feature 2&#10;Feature 3" />
              </div>
            </PanelSection>
          </template>

          <!-- LIST -->
          <template v-if="selectedElement.type === 'list'">
            <PanelSection title="List Items" icon="fa-solid fa-list" :dark="dark" :defaultOpen="true">
              <PropField label="List Style" icon="fa-solid fa-list-ul" :dark="dark">
                <select :value="selectedElement.styles?.listStyle || 'bullet'"
                  @change="selectedElement.styles.listStyle = $event.target.value; $emit('push-history')"
                  :class="inputCls">
                  <option value="bullet">• Bullet</option>
                  <option value="numbered">1. Numbered</option>
                  <option value="none">None</option>
                </select>
              </PropField>
              <div class="mt-2 space-y-1">
                <div v-for="(item, i) in selectedElement.items" :key="i" class="flex items-center gap-1.5">
                  <input type="text" :value="item"
                    @input="selectedElement.items[i] = $event.target.value; $emit('push-history')"
                    :class="inputCls + ' flex-1'" :placeholder="'Item ' + (i+1)" />
                  <button @click="selectedElement.items.splice(i, 1); $emit('push-history')"
                    class="text-red-400 hover:text-red-600 text-[11px]">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
                <button @click="selectedElement.items.push('New item'); $emit('push-history')"
                  class="w-full py-1.5 rounded-lg text-[10px] font-bold border-2 border-dashed transition-all mt-1"
                  :class="dark ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400'">
                  <i class="fa-solid fa-plus mr-1"></i>Add Item
                </button>
              </div>
            </PanelSection>
          </template>

          <!-- TIMELINE -->
          <template v-if="selectedElement.type === 'timeline'">
            <PanelSection title="Timeline Items" icon="fa-solid fa-timeline" :dark="dark" :defaultOpen="true">
              <div v-for="(item, i) in selectedElement.items" :key="i"
                class="border rounded-xl p-2 space-y-1.5 mb-2"
                :class="dark ? 'border-slate-700' : 'border-slate-200'">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-black" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                    <i class="fa-solid fa-circle-dot mr-1 text-indigo-500"></i>Milestone {{ i + 1 }}
                  </span>
                  <button @click="selectedElement.items.splice(i, 1); $emit('push-history')"
                    class="text-[9px] text-red-400 hover:text-red-600">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
                <input type="text" v-model="item.label" :class="inputCls" placeholder="Title" />
                <input type="text" v-model="item.date" :class="inputCls" placeholder="Date" />
                <input type="text" v-model="item.desc" :class="inputCls" placeholder="Description" />
              </div>
              <button @click="selectedElement.items.push({ label: 'Milestone', date: '2025', desc: '' }); $emit('push-history')"
                class="w-full py-1.5 rounded-lg text-[10px] font-bold border-2 border-dashed transition-all"
                :class="dark ? 'border-slate-700 text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400'">
                <i class="fa-solid fa-plus mr-1"></i>Add Milestone
              </button>
            </PanelSection>
          </template>

          <!-- PAGE NUMBER -->
          <template v-if="selectedElement.type === 'pagenum'">
            <PanelSection title="Page Number" icon="fa-solid fa-hashtag" :dark="dark" :defaultOpen="true">
              <PropField label="Font Size (px)" icon="fa-solid fa-text-height" :dark="dark">
                <input type="number" :value="selectedElement.styles?.fontSize || 12"
                  @input="selectedElement.styles.fontSize = +$event.target.value; $emit('push-history')"
                  :class="inputCls" min="8" max="48" />
              </PropField>
              <ColorRow label="Color" icon="fa-solid fa-palette" :dark="dark"
                :value="selectedElement.styles?.color || '#94a3b8'"
                @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
            </PanelSection>
          </template>

          <!-- DATE -->
          <template v-if="selectedElement.type === 'date'">
            <PanelSection title="Date Element" icon="fa-solid fa-calendar" :dark="dark" :defaultOpen="true">
              <PropField label="Font Size (px)" icon="fa-solid fa-text-height" :dark="dark">
                <input type="number" :value="selectedElement.styles?.fontSize || 13"
                  @input="selectedElement.styles.fontSize = +$event.target.value; $emit('push-history')"
                  :class="inputCls" min="8" max="48" />
              </PropField>
              <ColorRow label="Color" icon="fa-solid fa-palette" :dark="dark"
                :value="selectedElement.styles?.color || '#94a3b8'"
                @update="v => { selectedElement.styles.color = v; $emit('push-history') }" />
            </PanelSection>
          </template>

        </template>

        <!-- ═══ LAYOUT TAB ═══ -->
        <template v-else-if="activeTab === 'layout'">

          <!-- ALIGNMENT -->
          <PanelSection title="Alignment" icon="fa-solid fa-align-center" :dark="dark" :defaultOpen="true">
            <div class="grid grid-cols-3 gap-1.5">
              <button v-for="a in ALIGN_BTNS" :key="a.dir"
                @click="$emit('align-element', a.dir)"
                :title="a.label"
                class="py-2.5 rounded-xl border flex flex-col items-center gap-1.5 transition-all hover:scale-105 text-[10px] font-bold"
                :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500 hover:text-indigo-400 hover:bg-indigo-500/10' : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50'">
                <i :class="a.icon + ' text-sm'"></i>
                <span class="leading-none">{{ a.label }}</span>
              </button>
            </div>
          </PanelSection>

          <!-- LAYER ORDER -->
          <PanelSection title="Layer Order" icon="fa-solid fa-layer-group" :dark="dark" :defaultOpen="true">
            <PropField label="Z-Index" icon="fa-solid fa-sort" :dark="dark">
              <input type="number" :value="selectedElement.styles?.zIndex || 1"
                @input="selectedElement.styles.zIndex = +$event.target.value; $emit('push-history')"
                :class="inputCls" step="1" min="1" />
            </PropField>
            <div class="grid grid-cols-2 gap-2 mt-3">
              <button v-for="lb in LAYER_BTNS" :key="lb.action"
                @click="$emit(lb.action)"
                :title="lb.label"
                class="py-3 rounded-xl border flex flex-col items-center gap-1.5 transition-all hover:scale-105 text-[10px] font-bold"
                :class="dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500 hover:text-indigo-400 hover:bg-indigo-500/10' : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50'">
                <i :class="lb.icon + ' text-base'"></i>
                <span>{{ lb.label }}</span>
              </button>
            </div>
          </PanelSection>

          <!-- MOVE TO PAGE -->
          <PanelSection v-if="pages.length > 1" title="Move to Page" icon="fa-solid fa-file-export" :dark="dark">
            <div class="flex flex-wrap gap-1.5">
              <button v-for="(p, pi) in pages" :key="pi"
                :disabled="pi === selectedPageIndex"
                @click="$emit('move-element-to-page', selectedElement, selectedPageIndex, pi)"
                class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-all"
                :class="pi === selectedPageIndex
                  ? (dark ? 'border-slate-700 bg-slate-800 text-slate-700 cursor-default' : 'border-slate-100 bg-slate-50 text-slate-300 cursor-default')
                  : (dark ? 'border-slate-700 bg-slate-800 text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-400 hover:text-indigo-600')">
                <i class="fa-regular fa-file mr-1"></i>
                {{ pi === selectedPageIndex ? `P${pi + 1} (here)` : `Page ${pi + 1}` }}
              </button>
            </div>
          </PanelSection>

          <!-- VISIBILITY & LOCK -->
          <PanelSection title="Visibility & Lock" icon="fa-solid fa-eye" :dark="dark">
            <div class="space-y-2">
              <div class="flex items-center justify-between py-1.5">
                <label class="text-xs font-semibold flex items-center gap-2" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                  <i class="fa-solid fa-eye text-slate-400"></i> Visible
                </label>
                <button @click="selectedElement.hidden = !selectedElement.hidden; $emit('push-history')"
                  class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                  :class="!selectedElement.hidden ? 'bg-indigo-600' : dark ? 'bg-slate-700' : 'bg-slate-200'">
                  <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
                    :class="!selectedElement.hidden ? 'translate-x-5' : 'translate-x-1'"></span>
                </button>
              </div>
              <div class="flex items-center justify-between py-1.5 border-t" :class="dark ? 'border-slate-800' : 'border-slate-100'">
                <label class="text-xs font-semibold flex items-center gap-2" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                  <i class="fa-solid fa-lock-open text-slate-400"></i> Unlocked
                </label>
                <button @click="selectedElement.locked = !selectedElement.locked; $emit('push-history')"
                  class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                  :class="!selectedElement.locked ? 'bg-indigo-600' : dark ? 'bg-slate-700' : 'bg-slate-200'">
                  <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
                    :class="!selectedElement.locked ? 'translate-x-5' : 'translate-x-1'"></span>
                </button>
              </div>
            </div>
          </PanelSection>

        </template>

      </div>

      <!-- Bottom bar -->
      <div class="px-3 py-2 border-t shrink-0 flex items-center justify-between"
        :class="dark ? 'border-slate-800 bg-slate-900/50' : 'border-slate-100 bg-slate-50'">
        <span class="text-[9px] font-bold uppercase tracking-widest" :class="dark ? 'text-slate-700' : 'text-slate-400'">
          <i class="fa-solid fa-fingerprint mr-1"></i>{{ selectedElement.id.slice(0, 10) }}…
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
import { ref, computed, inject } from 'vue';

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
  'deselect-all', 'push-history', 'add-element', 'delete-selected', 'bring-to-front',
  'send-to-back', 'bring-forward', 'send-backward', 'align-element', 'move-element-to-page',
  'update:chart-labels-input', 'update:chart-values-input', 'update:chart-dataset-label',
  'update:chart-color', 'update:pie-colors-input', 'update-chart-data', 'refresh-chart',
  'upload-image', 'apply-callout-style',
  'add-table-row', 'add-table-column', 'remove-table-row', 'remove-table-column',
]);

const FONTS = inject('FONTS');
const FONT_WEIGHTS = inject('FONT_WEIGHTS');
const CALLOUT_PRESETS = inject('CALLOUT_PRESETS');
const SHORTCUTS = inject('SHORTCUTS');
const CODE_LANGS = inject('CODE_LANGS');

const activeTab = ref('style');

const propTabs = [
  { id: 'style',   label: 'Style',   icon: 'fa-solid fa-palette' },
  { id: 'content', label: 'Content', icon: 'fa-solid fa-pen' },
  { id: 'layout',  label: 'Layout',  icon: 'fa-solid fa-layer-group' },
];

const TEXT_TYPES = ['heading', 'subheading', 'text', 'quote', 'blockquote', 'highlight', 'callout', 'alert', 'badge', 'link', 'code', 'watermark'];
const CHART_TYPES = ['bar-chart', 'line-chart', 'pie-chart', 'doughnut-chart', 'area-chart', 'radar-chart'];
const PIE_TYPES = ['pie-chart', 'doughnut-chart'];

const isTextType = computed(() => TEXT_TYPES.includes(props.selectedElement?.type));
const isChartEl = computed(() => CHART_TYPES.includes(props.selectedElement?.type));
const isPieChart = computed(() => PIE_TYPES.includes(props.selectedElement?.type));

const TEXT_STYLES = [
  { key: 'fontWeight',     active: '700',           normal: '400',    icon: 'fa-solid fa-bold',          label: 'Bold' },
  { key: 'fontStyle',      active: 'italic',         normal: 'normal', icon: 'fa-solid fa-italic',        label: 'Italic' },
  { key: 'textDecoration', active: 'underline',      normal: 'none',   icon: 'fa-solid fa-underline',     label: 'Underline' },
  { key: 'textDecoration', active: 'line-through',   normal: 'none',   icon: 'fa-solid fa-strikethrough', label: 'Strike' },
];

const TEXT_ALIGNS = [
  { val: 'left',    icon: 'fa-solid fa-align-left',    label: 'Left' },
  { val: 'center',  icon: 'fa-solid fa-align-center',  label: 'Center' },
  { val: 'right',   icon: 'fa-solid fa-align-right',   label: 'Right' },
  { val: 'justify', icon: 'fa-solid fa-align-justify', label: 'Justify' },
];

const ALIGN_BTNS = [
  { dir: 'left',     icon: 'fa-solid fa-objects-align-left',              label: 'Left' },
  { dir: 'center-h', icon: 'fa-solid fa-align-center',                    label: 'H Center' },
  { dir: 'right',    icon: 'fa-solid fa-objects-align-right',             label: 'Right' },
  { dir: 'top',      icon: 'fa-solid fa-objects-align-top',               label: 'Top' },
  { dir: 'center-v', icon: 'fa-solid fa-align-center fa-rotate-90',       label: 'V Center' },
  { dir: 'bottom',   icon: 'fa-solid fa-objects-align-bottom',            label: 'Bottom' },
];

const LAYER_BTNS = [
  { action: 'bring-to-front', icon: 'fa-solid fa-angles-up',   label: 'Bring Front' },
  { action: 'bring-forward',  icon: 'fa-solid fa-angle-up',    label: 'Forward' },
  { action: 'send-backward',  icon: 'fa-solid fa-angle-down',  label: 'Backward' },
  { action: 'send-to-back',   icon: 'fa-solid fa-angles-down', label: 'Send Back' },
];

const SHADOW_OPTIONS = [
  { value: 'none', label: 'None',   preview: 'none' },
  { value: 'sm',   label: 'Small',  preview: '0 1px 3px rgba(0,0,0,.15)' },
  { value: 'md',   label: 'Medium', preview: '0 4px 12px rgba(0,0,0,.12)' },
  { value: 'lg',   label: 'Large',  preview: '0 10px 30px rgba(0,0,0,.18)' },
  { value: 'xl',   label: 'XL',     preview: '0 20px 60px rgba(0,0,0,.22)' },
  { value: '2xl',  label: '2XL',    preview: '0 25px 80px rgba(0,0,0,.28)' },
  { value: 'glow-indigo',  label: 'Indigo', preview: '0 0 20px rgba(99,102,241,.5)' },
  { value: 'glow-emerald', label: 'Green',  preview: '0 0 20px rgba(16,185,129,.5)' },
  { value: 'glow-gold',    label: 'Gold',   preview: '0 0 20px rgba(245,158,11,.5)' },
];

const ELEMENT_ICONS = {
  heading: 'fa-solid fa-heading', subheading: 'fa-solid fa-h', text: 'fa-solid fa-paragraph',
  quote: 'fa-solid fa-quote-left', blockquote: 'fa-solid fa-quote-right', highlight: 'fa-solid fa-highlighter',
  list: 'fa-solid fa-list', checklist: 'fa-solid fa-list-check', link: 'fa-solid fa-link',
  badge: 'fa-solid fa-tag', callout: 'fa-solid fa-circle-info', alert: 'fa-solid fa-triangle-exclamation',
  code: 'fa-solid fa-code', icon: 'fa-solid fa-star', rating: 'fa-regular fa-star',
  toc: 'fa-solid fa-list-ol', timeline: 'fa-solid fa-timeline', image: 'fa-solid fa-image',
  video: 'fa-solid fa-film', table: 'fa-solid fa-table', 'bar-chart': 'fa-solid fa-chart-bar',
  'line-chart': 'fa-solid fa-chart-line', 'pie-chart': 'fa-solid fa-chart-pie',
  'doughnut-chart': 'fa-solid fa-circle-half-stroke', 'area-chart': 'fa-solid fa-chart-area',
  'radar-chart': 'fa-solid fa-circle-nodes', metric: 'fa-solid fa-square-poll-vertical',
  progress: 'fa-solid fa-bars-progress', 'circular-progress': 'fa-solid fa-circle-notch',
  'stat-row': 'fa-solid fa-table-columns', rectangle: 'fa-regular fa-square',
  circle: 'fa-regular fa-circle', triangle: 'fa-solid fa-play fa-rotate-270',
  line: 'fa-solid fa-minus', arrow: 'fa-solid fa-arrow-right', divider: 'fa-solid fa-grip-lines',
  spacer: 'fa-solid fa-square-dashed', 'social-card': 'fa-solid fa-id-badge',
  testimonial: 'fa-solid fa-comment-dots', kanban: 'fa-solid fa-thumbtack',
  'price-card': 'fa-solid fa-dollar-sign', pagenum: 'fa-solid fa-hashtag',
  date: 'fa-solid fa-calendar-days', signature: 'fa-solid fa-signature',
  watermark: 'fa-solid fa-droplet',
};

const getElementIcon = (type) => ELEMENT_ICONS[type] || 'fa-solid fa-cube';

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

<!-- Inline sub-components -->
<script>
const PanelSection = {
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
      <div v-show="open" class="px-4 pb-4 pt-1 space-y-2.5">
        <slot />
      </div>
    </div>
  `
};

const PropField = {
  name: 'PropField',
  props: { label: String, icon: String, dark: Boolean },
  template: `
    <div>
      <label class="block text-[9px] font-black uppercase tracking-wider mb-1 flex items-center gap-1"
        :class="dark ? 'text-slate-600' : 'text-slate-400'">
        <i v-if="icon" :class="icon"></i> {{ label }}
      </label>
      <slot />
    </div>
  `
};

const ColorRow = {
  name: 'ColorRow',
  props: { label: String, icon: String, dark: Boolean, value: String },
  emits: ['update'],
  template: `
    <div>
      <label class="block text-[9px] font-black uppercase tracking-wider mb-1 flex items-center gap-1"
        :class="dark ? 'text-slate-600' : 'text-slate-400'">
        <i v-if="icon" :class="icon"></i> {{ label }}
      </label>
      <div class="flex items-center gap-2">
        <input type="color" :value="value === 'transparent' ? '#ffffff' : value"
          @input="$emit('update', $event.target.value)"
          class="w-9 h-9 rounded-xl border cursor-pointer p-0.5 flex-shrink-0"
          :class="dark ? 'border-slate-700 bg-slate-800' : 'border-slate-200'" />
        <input type="text" :value="value"
          @input="$emit('update', $event.target.value)"
          class="flex-1 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
          :class="dark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700'" />
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
input:focus, select:focus, textarea:focus {
  box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
aside { animation: slideInRight 0.2s ease; }
@keyframes slideInRight {
  from { opacity: 0; transform: translateX(16px); }
  to { opacity: 1; transform: translateX(0); }
}
</style>