<template>
  <aside
    class="w-72 flex flex-col overflow-hidden shrink-0 border-l transition-colors duration-200"
    :class="dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-white border-slate-200'"
  >
    <!-- No selection -->
    <template v-if="!selectedElement">
      <div class="flex-1 flex flex-col items-center justify-center gap-3 px-5 py-8 text-center">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center"
          :class="dark ? 'bg-[#161b22] border border-[#21262d]' : 'bg-slate-50 border border-slate-200'">
          <i class="fa-solid fa-arrow-pointer text-xl" :class="dark ? 'text-slate-700' : 'text-slate-300'"></i>
        </div>
        <div>
          <p class="text-xs font-black" :class="dark ? 'text-slate-500' : 'text-slate-400'">Select an element</p>
          <p class="text-[10px] mt-0.5" :class="dark ? 'text-slate-700' : 'text-slate-400'">Click to edit properties</p>
        </div>
        <!-- Keyboard shortcuts -->
        <div class="w-full mt-2 space-y-1">
          <div v-for="sc in SHORTCUTS.slice(0,8)" :key="sc.key"
            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg"
            :class="dark ? 'bg-[#161b22]/50' : 'bg-slate-50'">
            <span class="text-[10px]" :class="dark ? 'text-slate-500' : 'text-slate-400'">{{ sc.desc }}</span>
            <kbd class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded"
              :class="dark ? 'bg-[#1c2128] text-slate-400 border border-[#30363d]' : 'bg-white text-slate-500 border border-slate-200'">{{ sc.key }}</kbd>
          </div>
        </div>
      </div>
    </template>

    <!-- Element selected -->
    <template v-else>
      <!-- Panel header -->
      <div class="px-3 py-2.5 border-b flex items-center justify-between shrink-0"
        :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
            :class="dark ? 'bg-indigo-900/40' : 'bg-indigo-50'">
            <i :class="getElIcon(selectedElement.type) + ' text-indigo-500'" style="font-size:11px"></i>
          </div>
          <div class="min-w-0">
            <p class="text-[11px] font-black capitalize truncate" :class="dark ? 'text-slate-200' : 'text-slate-700'">
              {{ selectedElement.type.replace(/-/g,' ') }}
            </p>
            <p class="text-[9px] font-mono truncate" :class="dark ? 'text-slate-600' : 'text-slate-400'">
              id: {{ selectedElement.id?.slice(0,8) }}…
            </p>
          </div>
        </div>
        <div class="flex items-center gap-0.5 shrink-0">
          <button @click="toggleHidden" :title="selectedElement.hidden ? 'Show' : 'Hide'" class="prop-icon-btn" :class="selectedElement.hidden ? 'text-amber-500' : ''">
            <i :class="selectedElement.hidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" style="font-size:11px"></i>
          </button>
          <button @click="toggleLocked" :title="selectedElement.locked ? 'Unlock' : 'Lock'" class="prop-icon-btn" :class="selectedElement.locked ? 'text-amber-500' : ''">
            <i :class="selectedElement.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" style="font-size:11px"></i>
          </button>
          <button @click="$emit('delete-selected')" class="prop-icon-btn text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
            <i class="fa-solid fa-trash" style="font-size:11px"></i>
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex border-b shrink-0" :class="dark ? 'border-[#21262d]' : 'border-slate-200'">
        <button v-for="tab in PROP_TABS" :key="tab.id"
          @click="activeTab = tab.id"
          class="flex-1 py-2 text-[9px] font-black uppercase tracking-wider border-b-2 transition-colors"
          :class="activeTab === tab.id
            ? 'border-indigo-500 text-indigo-500'
            : (dark ? 'border-transparent text-slate-600 hover:text-slate-400' : 'border-transparent text-slate-400 hover:text-slate-600')">
          <i :class="tab.icon + ' mr-0.5'" style="font-size:9px"></i> {{ tab.label }}
        </button>
      </div>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto">

        <!-- ═══ STYLE TAB ═══ -->
        <template v-if="activeTab === 'style'">

          <!-- Position & Size -->
          <PSection title="Position & Size" icon="fa-solid fa-up-down-left-right" :dark="dark" :open="true">
            <div class="grid grid-cols-2 gap-2">
              <PField label="X (px)" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.position?.x||0)"
                  @input="selectedElement.position.x = +$event.target.value; ph()" :class="iCls" step="1"/>
              </PField>
              <PField label="Y (px)" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.position?.y||0)"
                  @input="selectedElement.position.y = +$event.target.value; ph()" :class="iCls" step="1"/>
              </PField>
              <PField label="Width" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.styles?.width||200)"
                  @input="selectedElement.styles.width = +$event.target.value; ph()" :class="iCls" step="1" min="10"/>
              </PField>
              <PField label="Height" :dark="dark">
                <input type="number" :value="Math.round(selectedElement.styles?.height||50)"
                  @input="selectedElement.styles.height = +$event.target.value; ph()" :class="iCls" step="1" min="10"/>
              </PField>
            </div>
            <PField label="Rotation (°)" :dark="dark" class="mt-2">
              <div class="flex items-center gap-2">
                <input type="range" :value="selectedElement.styles?.rotate||0" min="-180" max="180"
                  @input="selectedElement.styles.rotate = +$event.target.value; ph()" class="flex-1 h-1.5 accent-indigo-600"/>
                <input type="number" :value="selectedElement.styles?.rotate||0" min="-180" max="180"
                  @input="selectedElement.styles.rotate = +$event.target.value; ph()" class="w-14 text-[11px] text-center rounded-lg border px-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-200' : 'bg-white border-slate-200 text-slate-700'"/>
              </div>
            </PField>
          </PSection>

          <!-- Color & Fill -->
          <PSection title="Color & Fill" icon="fa-solid fa-palette" :dark="dark" :open="true">
            <CRow v-if="isTextType" label="Text Color" icon="fa-solid fa-font" :dark="dark"
              :val="selectedElement.styles?.color||'#1e293b'"
              @upd="v => { selectedElement.styles.color=v; ph() }"/>
            <CRow label="Background" icon="fa-solid fa-fill-drip" :dark="dark"
              :val="selectedElement.styles?.backgroundColor||'transparent'"
              @upd="v => { selectedElement.styles.backgroundColor=v; ph() }"/>
            <!-- Opacity -->
            <div class="mt-2">
              <div class="flex items-center justify-between mb-1">
                <label class="text-[9px] font-black uppercase tracking-wider flex items-center gap-1"
                  :class="dark ? 'text-slate-600' : 'text-slate-400'">
                  <i class="fa-solid fa-circle-half-stroke" style="font-size:9px"></i> Opacity
                </label>
                <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.styles?.opacity??100 }}%</span>
              </div>
              <input type="range" :value="selectedElement.styles?.opacity??100" min="0" max="100"
                @input="selectedElement.styles.opacity=+$event.target.value; ph()" class="w-full h-1.5 accent-indigo-600"/>
            </div>
          </PSection>

          <!-- Border -->
          <PSection title="Border" icon="fa-solid fa-border-all" :dark="dark">
            <div class="grid grid-cols-2 gap-2">
              <PField label="Width (px)" :dark="dark">
                <input type="number" :value="selectedElement.styles?.borderWidth||0"
                  @input="selectedElement.styles.borderWidth=+$event.target.value; ph()" :class="iCls" min="0" max="20"/>
              </PField>
              <PField label="Style" :dark="dark">
                <select :value="selectedElement.styles?.borderStyle||'solid'"
                  @change="selectedElement.styles.borderStyle=$event.target.value; ph()" :class="iCls">
                  <option value="solid">Solid</option>
                  <option value="dashed">Dashed</option>
                  <option value="dotted">Dotted</option>
                  <option value="double">Double</option>
                </select>
              </PField>
            </div>
            <CRow v-if="(selectedElement.styles?.borderWidth||0) > 0" label="Border Color" icon="fa-solid fa-border-style" :dark="dark"
              :val="selectedElement.styles?.borderColor||'#e2e8f0'"
              @upd="v => { selectedElement.styles.borderColor=v; ph() }"/>
            <PField label="Radius (px)" :dark="dark" class="mt-2">
              <div class="flex items-center gap-2">
                <input type="range" :value="selectedElement.styles?.borderRadius||0" min="0" max="64"
                  @input="selectedElement.styles.borderRadius=+$event.target.value; ph()" class="flex-1 h-1.5 accent-indigo-600"/>
                <span class="text-[10px] font-black w-7 text-right" :class="dark ? 'text-indigo-400' : 'text-indigo-600'">
                  {{ selectedElement.styles?.borderRadius||0 }}
                </span>
              </div>
            </PField>
          </PSection>

          <!-- Shadow -->
          <PSection title="Shadow" icon="fa-solid fa-layer-group" :dark="dark">
            <div class="grid grid-cols-3 gap-1.5">
              <button v-for="sh in SHADOWS" :key="sh.v"
                @click="selectedElement.styles.boxShadow=sh.v; ph()"
                class="py-2.5 rounded-xl border flex flex-col items-center gap-1 transition-all hover:scale-105 text-[9px] font-bold"
                :class="selectedElement.styles?.boxShadow===sh.v
                  ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-500/30'
                  : (dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-indigo-500' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-300')">
                <span :style="{ width:'24px',height:'14px',borderRadius:'4px',background:dark?'#334155':'#e2e8f0',boxShadow:sh.preview,display:'block' }"></span>
                {{ sh.l }}
              </button>
            </div>
          </PSection>

          <!-- Spacing -->
          <PSection title="Spacing" icon="fa-solid fa-maximize" :dark="dark">
            <PField label="Padding (px)" :dark="dark">
              <input type="number" :value="selectedElement.styles?.padding||0"
                @input="selectedElement.styles.padding=+$event.target.value; ph()" :class="iCls" min="0" max="80"/>
            </PField>
          </PSection>

          <!-- Typography (text types) -->
          <PSection v-if="isTextType" title="Typography" icon="fa-solid fa-font" :dark="dark" :open="true">
            <PField label="Font Family" :dark="dark">
              <select :value="selectedElement.styles?.fontFamily||rs.font_family"
                @change="selectedElement.styles.fontFamily=$event.target.value; ph()" :class="iCls">
                <option v-for="f in FONTS" :key="f.v" :value="f.v">{{ f.n }}</option>
              </select>
            </PField>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <PField label="Size (px)" :dark="dark">
                <input type="number" :value="selectedElement.styles?.fontSize||14"
                  @input="selectedElement.styles.fontSize=+$event.target.value; ph()" :class="iCls" min="6" max="300"/>
              </PField>
              <PField label="Weight" :dark="dark">
                <select :value="selectedElement.styles?.fontWeight||'400'"
                  @change="selectedElement.styles.fontWeight=$event.target.value; ph()" :class="iCls">
                  <option v-for="fw in FONT_WEIGHTS" :key="fw.v" :value="fw.v">{{ fw.l }}</option>
                </select>
              </PField>
              <PField label="Line Height" :dark="dark">
                <input type="number" :value="selectedElement.styles?.lineHeight||1.5"
                  @input="selectedElement.styles.lineHeight=+$event.target.value; ph()" :class="iCls" min="0.8" max="4" step="0.1"/>
              </PField>
              <PField label="Letter Spacing" :dark="dark">
                <input type="number" :value="selectedElement.styles?.letterSpacing||0"
                  @input="selectedElement.styles.letterSpacing=+$event.target.value; ph()" :class="iCls" min="-5" max="20" step="0.5"/>
              </PField>
            </div>
            <!-- Style toggles -->
            <div class="flex gap-1 mt-2">
              <button v-for="st in TEXT_STYLES" :key="st.key+st.active"
                @click="toggleTS(st)" :title="st.label"
                class="flex-1 py-1.5 rounded-lg border text-xs transition-all"
                :class="isTSActive(st)
                  ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-500/20'
                  : (dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-indigo-500' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-300')">
                <i :class="st.icon" style="font-size:11px"></i>
              </button>
            </div>
            <!-- Alignment -->
            <div class="flex gap-1 mt-2">
              <button v-for="al in TEXT_ALIGNS" :key="al.v"
                @click="selectedElement.styles.textAlign=al.v; ph()" :title="al.label"
                class="flex-1 py-1.5 rounded-lg border text-xs transition-all"
                :class="selectedElement.styles?.textAlign===al.v
                  ? 'bg-indigo-600 border-indigo-600 text-white'
                  : (dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-indigo-500' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-300')">
                <i :class="al.icon" style="font-size:11px"></i>
              </button>
            </div>
            <!-- Transform -->
            <PField label="Text Transform" :dark="dark" class="mt-2">
              <select :value="selectedElement.styles?.textTransform||'none'"
                @change="selectedElement.styles.textTransform=$event.target.value; ph()" :class="iCls">
                <option value="none">None</option>
                <option value="uppercase">UPPERCASE</option>
                <option value="lowercase">lowercase</option>
                <option value="capitalize">Capitalize</option>
              </select>
            </PField>
          </PSection>

        </template>

        <!-- ═══ CONTENT TAB ═══ -->
        <template v-else-if="activeTab === 'content'">

          <!-- Chart data -->
          <PSection v-if="isChart" title="Chart Data" icon="fa-solid fa-chart-bar" :dark="dark" :open="true">
            <PField label="Chart Title" :dark="dark">
              <input type="text" :value="selectedElement.chartTitle||''" @input="selectedElement.chartTitle=$event.target.value; ph()" :class="iCls" placeholder="Chart title…"/>
            </PField>
            <PField label="Labels (comma-separated)" :dark="dark" class="mt-2">
              <textarea :value="chartLabelsInput" rows="2"
                @input="$emit('update:chart-labels-input', $event.target.value)"
                :class="iCls+' resize-none'" placeholder="Q1, Q2, Q3, Q4"/>
            </PField>
            <PField label="Values (comma-separated)" :dark="dark" class="mt-2">
              <textarea :value="chartValuesInput" rows="2"
                @input="$emit('update:chart-values-input', $event.target.value)"
                :class="iCls+' resize-none'" placeholder="42, 68, 55, 81"/>
            </PField>
            <PField label="Dataset Label" :dark="dark" class="mt-2">
              <input type="text" :value="chartDatasetLabel"
                @input="$emit('update:chart-dataset-label', $event.target.value)" :class="iCls" placeholder="Revenue"/>
            </PField>
            <div v-if="!isPie">
              <PField label="Chart Color" :dark="dark" class="mt-2">
                <div class="flex gap-2">
                  <input type="color" :value="chartColor" @input="$emit('update:chart-color', $event.target.value)"
                    class="w-9 h-9 rounded-lg border cursor-pointer p-0.5 flex-shrink-0"
                    :class="dark ? 'border-[#30363d] bg-[#161b22]' : 'border-slate-200'"/>
                  <input type="text" :value="chartColor" @input="$emit('update:chart-color', $event.target.value)" :class="iCls+' font-mono'"/>
                </div>
              </PField>
            </div>
            <PField v-if="isPie" label="Slice Colors (comma-sep hex)" :dark="dark" class="mt-2">
              <input type="text" :value="pieColorsInput" @input="$emit('update:pie-colors-input', $event.target.value)" :class="iCls+' font-mono'" placeholder="#6366f1, #10b981, #f59e0b"/>
            </PField>
            <PField label="Legend Position" :dark="dark" class="mt-2">
              <select :value="selectedElement.legendPosition||'bottom'"
                @change="selectedElement.legendPosition=$event.target.value; ph()" :class="iCls">
                <option value="top">Top</option>
                <option value="bottom">Bottom</option>
                <option value="left">Left</option>
                <option value="right">Right</option>
                <option value="none">None</option>
              </select>
            </PField>
            <button @click="$emit('update-chart-data'); $emit('refresh-chart')"
              class="w-full mt-3 py-2.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-500 transition-all hover:scale-[1.02] active:scale-95 shadow-md shadow-indigo-500/25">
              <i class="fa-solid fa-rotate mr-1.5" style="font-size:11px"></i>Apply & Refresh Chart
            </button>
          </PSection>

          <!-- KPI Metric -->
          <PSection v-if="selectedElement.type==='metric'" title="KPI Card" icon="fa-solid fa-chart-line" :dark="dark" :open="true">
            <PField label="Label" :dark="dark">
              <input type="text" :value="selectedElement.label||''" @input="selectedElement.label=$event.target.value; ph()" :class="iCls" placeholder="Revenue"/>
            </PField>
            <PField label="Value" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.value||''" @input="selectedElement.value=$event.target.value; ph()" :class="iCls" placeholder="$48,293"/>
            </PField>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <PField label="Change" :dark="dark">
                <input type="text" :value="selectedElement.change||''" @input="selectedElement.change=$event.target.value; ph()" :class="iCls" placeholder="12.5%"/>
              </PField>
              <PField label="Direction" :dark="dark">
                <select :value="selectedElement.changeType||'positive'" @change="selectedElement.changeType=$event.target.value; ph()" :class="iCls">
                  <option value="positive">▲ Up</option>
                  <option value="negative">▼ Down</option>
                </select>
              </PField>
            </div>
            <PField label="Period" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.changePeriod||''" @input="selectedElement.changePeriod=$event.target.value; ph()" :class="iCls" placeholder="vs last month"/>
            </PField>
            <CRow label="Accent Color" icon="fa-solid fa-circle" :dark="dark"
              :val="selectedElement.styles?.color||rs.primary_color"
              @upd="v => { selectedElement.styles.color=v; ph() }" class="mt-2"/>
          </PSection>

          <!-- Progress -->
          <PSection v-if="selectedElement.type==='progress'" title="Progress Bar" icon="fa-solid fa-bars-progress" :dark="dark" :open="true">
            <PField label="Label" :dark="dark">
              <input type="text" :value="selectedElement.label||''" @input="selectedElement.label=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <div class="mt-2">
              <div class="flex items-center justify-between mb-1">
                <label class="text-[9px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-600' : 'text-slate-400'">Value</label>
                <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.value??0 }}%</span>
              </div>
              <input type="range" :value="selectedElement.value??0" min="0" max="100"
                @input="selectedElement.value=+$event.target.value; ph()" class="w-full h-1.5 accent-indigo-600"/>
            </div>
            <CRow label="Fill Color" icon="fa-solid fa-fill" :dark="dark"
              :val="selectedElement.styles?.color||rs.primary_color"
              @upd="v => { selectedElement.styles.color=v; ph() }" class="mt-2"/>
            <CRow label="Track Color" icon="fa-regular fa-circle" :dark="dark"
              :val="selectedElement.styles?.trackColor||'#e2e8f0'"
              @upd="v => { selectedElement.styles.trackColor=v; ph() }" class="mt-2"/>
          </PSection>

          <!-- Circular progress -->
          <PSection v-if="selectedElement.type==='circular-progress'" title="Circular Progress" icon="fa-solid fa-circle-notch" :dark="dark" :open="true">
            <PField label="Label" :dark="dark">
              <input type="text" :value="selectedElement.label||''" @input="selectedElement.label=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <div class="mt-2">
              <div class="flex items-center justify-between mb-1">
                <label class="text-[9px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-600' : 'text-slate-400'">Value</label>
                <span class="text-[10px] font-black text-indigo-500">{{ selectedElement.value??0 }}%</span>
              </div>
              <input type="range" :value="selectedElement.value??0" min="0" max="100"
                @input="selectedElement.value=+$event.target.value; ph()" class="w-full h-1.5 accent-indigo-600"/>
            </div>
            <CRow label="Color" icon="fa-solid fa-circle" :dark="dark"
              :val="selectedElement.styles?.color||rs.primary_color"
              @upd="v => { selectedElement.styles.color=v; ph() }" class="mt-2"/>
            <CRow label="Track" icon="fa-regular fa-circle" :dark="dark"
              :val="selectedElement.styles?.trackColor||'#e2e8f0'"
              @upd="v => { selectedElement.styles.trackColor=v; ph() }" class="mt-2"/>
          </PSection>

          <!-- Table -->
          <PSection v-if="selectedElement.type==='table'" title="Table" icon="fa-solid fa-table" :dark="dark" :open="true">
            <CRow label="Header BG" icon="fa-solid fa-fill-drip" :dark="dark"
              :val="selectedElement.styles?.headerBg||rs.primary_color"
              @upd="v => { selectedElement.styles.headerBg=v; ph() }"/>
            <CRow label="Header Text" icon="fa-solid fa-font" :dark="dark"
              :val="selectedElement.styles?.headerColor||'#fff'"
              @upd="v => { selectedElement.styles.headerColor=v; ph() }" class="mt-2"/>
            <CRow label="Even Row BG" icon="fa-solid fa-table" :dark="dark"
              :val="selectedElement.styles?.evenRowBg||'#fff'"
              @upd="v => { selectedElement.styles.evenRowBg=v; ph() }" class="mt-2"/>
            <CRow label="Odd Row BG" icon="fa-solid fa-table" :dark="dark"
              :val="selectedElement.styles?.oddRowBg||'#f8fafc'"
              @upd="v => { selectedElement.styles.oddRowBg=v; ph() }" class="mt-2"/>
            <div class="grid grid-cols-2 gap-1.5 mt-3">
              <button @click="$emit('add-table-row')" class="py-1.5 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
                :class="dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-emerald-500 hover:text-emerald-400' : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-400 hover:text-emerald-600'">
                <i class="fa-solid fa-plus mr-1" style="font-size:9px"></i>Row
              </button>
              <button @click="$emit('add-table-column')" class="py-1.5 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
                :class="dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-emerald-500 hover:text-emerald-400' : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-400 hover:text-emerald-600'">
                <i class="fa-solid fa-plus mr-1" style="font-size:9px"></i>Col
              </button>
              <button @click="$emit('remove-table-row')" class="py-1.5 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
                :class="dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-red-500 hover:text-red-400' : 'border-slate-200 bg-white text-slate-500 hover:border-red-400 hover:text-red-500'">
                <i class="fa-solid fa-minus mr-1" style="font-size:9px"></i>Row
              </button>
              <button @click="$emit('remove-table-column')" class="py-1.5 rounded-lg text-[10px] font-bold border transition-all hover:scale-105"
                :class="dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-red-500 hover:text-red-400' : 'border-slate-200 bg-white text-slate-500 hover:border-red-400 hover:text-red-500'">
                <i class="fa-solid fa-minus mr-1" style="font-size:9px"></i>Col
              </button>
            </div>
          </PSection>

          <!-- Image -->
          <PSection v-if="selectedElement.type==='image'" title="Image" icon="fa-solid fa-image" :dark="dark" :open="true">
            <PField label="Object Fit" :dark="dark">
              <select :value="selectedElement.styles?.objectFit||'cover'"
                @change="selectedElement.styles.objectFit=$event.target.value; ph()" :class="iCls">
                <option value="cover">Cover</option>
                <option value="contain">Contain</option>
                <option value="fill">Fill</option>
                <option value="none">None</option>
              </select>
            </PField>
            <label class="mt-2 flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 border-dashed cursor-pointer transition-all"
              :class="dark ? 'border-[#30363d] text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400 hover:text-indigo-600'">
              <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
              <span class="text-xs font-bold">Upload Image</span>
              <input type="file" accept="image/*" class="hidden" @change="e => $emit('upload-image', e)"/>
            </label>
            <PField label="Image URL" :dark="dark" class="mt-2">
              <input type="url" :value="selectedElement.src||''" @input="selectedElement.src=$event.target.value; ph()" :class="iCls" placeholder="https://…"/>
            </PField>
            <PField label="Alt Text" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.alt||''" @input="selectedElement.alt=$event.target.value; ph()" :class="iCls" placeholder="Image description…"/>
            </PField>
          </PSection>

          <!-- Generic text content -->
          <PSection v-if="['text','heading','subheading','quote','blockquote','highlight'].includes(selectedElement.type)" title="Content" icon="fa-solid fa-paragraph" :dark="dark" :open="true">
            <textarea :value="selectedElement.content||''"
              @input="selectedElement.content=$event.target.value; ph()"
              :class="iCls+' resize-none'" rows="5" placeholder="Edit content…"/>
          </PSection>

          <!-- Code -->
          <PSection v-if="selectedElement.type==='code'" title="Code Block" icon="fa-solid fa-code" :dark="dark" :open="true">
            <PField label="Language" :dark="dark">
              <select :value="selectedElement.language||'JavaScript'" @change="selectedElement.language=$event.target.value; ph()" :class="iCls">
                <option v-for="l in CODE_LANGS" :key="l" :value="l">{{ l }}</option>
              </select>
            </PField>
            <textarea :value="selectedElement.content||''"
              @input="selectedElement.content=$event.target.value; ph()"
              :class="iCls+' resize-none font-mono text-[10px] mt-2'" rows="7" placeholder="Code here…"/>
          </PSection>

          <!-- Badge -->
          <PSection v-if="selectedElement.type==='badge'" title="Badge" icon="fa-solid fa-tag" :dark="dark" :open="true">
            <PField label="Text" :dark="dark">
              <input type="text" :value="selectedElement.content||''" @input="selectedElement.content=$event.target.value; ph()" :class="iCls" placeholder="Label…"/>
            </PField>
          </PSection>

          <!-- Callout -->
          <PSection v-if="selectedElement.type==='callout'" title="Callout" icon="fa-solid fa-circle-info" :dark="dark" :open="true">
            <div class="grid grid-cols-3 gap-1.5 mb-3">
              <button v-for="(s,k) in CALLOUT_PRESETS" :key="k"
                @click="selectedElement.calloutStyle=k; $emit('apply-callout-style', selectedElement)"
                class="py-2 rounded-xl border text-[9px] font-bold flex flex-col items-center gap-1 transition-all hover:scale-105"
                :class="selectedElement.calloutStyle===k
                  ? 'border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20'
                  : (dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-slate-600' : 'border-slate-200 bg-white text-slate-500')">
                <span style="font-size:14px">{{ s.emoji }}</span>
                <span class="capitalize">{{ k }}</span>
              </button>
            </div>
            <PField label="Emoji" :dark="dark">
              <input type="text" :value="selectedElement.emoji||'💡'" @input="selectedElement.emoji=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <textarea :value="selectedElement.content||''"
              @input="selectedElement.content=$event.target.value; ph()"
              :class="iCls+' resize-none mt-2'" rows="3" placeholder="Callout text…"/>
          </PSection>

          <!-- Stat Row -->
          <PSection v-if="selectedElement.type==='stat-row'" title="Stats Row" icon="fa-solid fa-table-columns" :dark="dark" :open="true">
            <div v-for="(stat,i) in selectedElement.stats||[]" :key="i" class="border rounded-xl p-2 space-y-1.5 mb-2"
              :class="dark ? 'border-[#30363d]' : 'border-slate-200'">
              <div class="flex items-center justify-between">
                <span class="text-[9px] font-black" :class="dark ? 'text-slate-400' : 'text-slate-600'">Stat {{ i+1 }}</span>
                <button @click="selectedElement.stats.splice(i,1); ph()" class="text-[9px] text-red-400 hover:text-red-600">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
              <input type="text" v-model="stat.value" :class="iCls" placeholder="Value" @input="ph()"/>
              <input type="text" v-model="stat.label" :class="iCls" placeholder="Label" @input="ph()"/>
            </div>
            <button @click="selectedElement.stats=(selectedElement.stats||[]); selectedElement.stats.push({label:'Stat',value:'0'}); ph()"
              class="w-full py-1.5 rounded-lg text-[10px] font-bold border-2 border-dashed transition-all"
              :class="dark ? 'border-[#30363d] text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400'">
              <i class="fa-solid fa-plus mr-1" style="font-size:9px"></i>Add Stat
            </button>
          </PSection>

          <!-- List -->
          <PSection v-if="selectedElement.type==='list'" title="List" icon="fa-solid fa-list" :dark="dark" :open="true">
            <PField label="Style" :dark="dark">
              <select :value="selectedElement.styles?.listStyle||'bullet'" @change="selectedElement.styles.listStyle=$event.target.value; ph()" :class="iCls">
                <option value="bullet">• Bullet</option>
                <option value="numbered">1. Numbered</option>
                <option value="none">None</option>
              </select>
            </PField>
            <div class="mt-2 space-y-1">
              <div v-for="(item,i) in selectedElement.items||[]" :key="i" class="flex items-center gap-1.5">
                <input type="text" :value="item" @input="selectedElement.items[i]=$event.target.value; ph()" :class="iCls+' flex-1'" :placeholder="'Item '+(i+1)"/>
                <button @click="selectedElement.items.splice(i,1); ph()" class="text-red-400 hover:text-red-600 text-xs">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
              <button @click="(selectedElement.items=selectedElement.items||[]).push('New item'); ph()"
                class="w-full py-1.5 rounded-lg text-[10px] font-bold border-2 border-dashed transition-all mt-1"
                :class="dark ? 'border-[#30363d] text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400'">
                <i class="fa-solid fa-plus mr-1" style="font-size:9px"></i>Add Item
              </button>
            </div>
          </PSection>

          <!-- Timeline -->
          <PSection v-if="selectedElement.type==='timeline'" title="Timeline" icon="fa-solid fa-timeline" :dark="dark" :open="true">
            <div v-for="(item,i) in selectedElement.items||[]" :key="i" class="border rounded-xl p-2 space-y-1.5 mb-2"
              :class="dark ? 'border-[#30363d]' : 'border-slate-200'">
              <div class="flex items-center justify-between">
                <span class="text-[9px] font-black text-indigo-500">Milestone {{ i+1 }}</span>
                <button @click="selectedElement.items.splice(i,1); ph()" class="text-[9px] text-red-400 hover:text-red-600"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <input type="text" v-model="item.label" :class="iCls" placeholder="Title" @input="ph()"/>
              <input type="text" v-model="item.date" :class="iCls" placeholder="Date" @input="ph()"/>
              <input type="text" v-model="item.desc" :class="iCls" placeholder="Description" @input="ph()"/>
            </div>
            <button @click="(selectedElement.items=selectedElement.items||[]).push({label:'Milestone',date:'2025',desc:''}); ph()"
              class="w-full py-1.5 rounded-lg text-[10px] font-bold border-2 border-dashed transition-all"
              :class="dark ? 'border-[#30363d] text-slate-500 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 text-slate-400 hover:border-indigo-400'">
              <i class="fa-solid fa-plus mr-1" style="font-size:9px"></i>Add Milestone
            </button>
          </PSection>

          <!-- Link -->
          <PSection v-if="selectedElement.type==='link'" title="Hyperlink" icon="fa-solid fa-link" :dark="dark" :open="true">
            <PField label="URL" :dark="dark">
              <input type="url" :value="selectedElement.href||''" @input="selectedElement.href=$event.target.value; ph()" :class="iCls" placeholder="https://…"/>
            </PField>
            <PField label="Display Text" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.content||''" @input="selectedElement.content=$event.target.value; ph()" :class="iCls" placeholder="Click here"/>
            </PField>
            <PField label="Target" :dark="dark" class="mt-2">
              <select :value="selectedElement.target||'_blank'" @change="selectedElement.target=$event.target.value; ph()" :class="iCls">
                <option value="_blank">New Tab</option>
                <option value="_self">Same Tab</option>
              </select>
            </PField>
          </PSection>

          <!-- Signature -->
          <PSection v-if="selectedElement.type==='signature'" title="Signature" icon="fa-solid fa-signature" :dark="dark" :open="true">
            <PField label="Name" :dark="dark">
              <input type="text" :value="selectedElement.content||''" @input="selectedElement.content=$event.target.value; ph()" :class="iCls" placeholder="Name…"/>
            </PField>
            <PField label="Label" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.label||'Authorised Signature'" @input="selectedElement.label=$event.target.value; ph()" :class="iCls"/>
            </PField>
          </PSection>

          <!-- Watermark -->
          <PSection v-if="selectedElement.type==='watermark'" title="Watermark" icon="fa-solid fa-droplet" :dark="dark" :open="true">
            <PField label="Text" :dark="dark">
              <input type="text" :value="selectedElement.content||'CONFIDENTIAL'" @input="selectedElement.content=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <PField label="Font Size" :dark="dark">
                <input type="number" :value="selectedElement.styles?.fontSize||48" @input="selectedElement.styles.fontSize=+$event.target.value; ph()" :class="iCls" min="12" max="200"/>
              </PField>
              <PField label="Rotation (°)" :dark="dark">
                <input type="number" :value="selectedElement.styles?.rotate||(-30)" @input="selectedElement.styles.rotate=+$event.target.value; ph()" :class="iCls" min="-180" max="180"/>
              </PField>
            </div>
            <CRow label="Color" icon="fa-solid fa-palette" :dark="dark"
              :val="selectedElement.styles?.color||'#94a3b8'"
              @upd="v => { selectedElement.styles.color=v; ph() }" class="mt-2"/>
          </PSection>

          <!-- Testimonial -->
          <PSection v-if="selectedElement.type==='testimonial'" title="Testimonial" icon="fa-solid fa-comment-dots" :dark="dark" :open="true">
            <textarea :value="selectedElement.content||''" @input="selectedElement.content=$event.target.value; ph()" :class="iCls+' resize-none'" rows="3" placeholder="Quote…"/>
            <PField label="Author" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.author||''" @input="selectedElement.author=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <PField label="Role" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.role||''" @input="selectedElement.role=$event.target.value; ph()" :class="iCls"/>
            </PField>
          </PSection>

          <!-- Price Card -->
          <PSection v-if="selectedElement.type==='price-card'" title="Price Card" icon="fa-solid fa-dollar-sign" :dark="dark" :open="true">
            <PField label="Plan Name" :dark="dark">
              <input type="text" :value="selectedElement.plan||''" @input="selectedElement.plan=$event.target.value; ph()" :class="iCls" placeholder="Pro Plan"/>
            </PField>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <PField label="Price" :dark="dark">
                <input type="text" :value="selectedElement.price||'$0'" @input="selectedElement.price=$event.target.value; ph()" :class="iCls"/>
              </PField>
              <PField label="Period" :dark="dark">
                <input type="text" :value="selectedElement.period||'/month'" @input="selectedElement.period=$event.target.value; ph()" :class="iCls"/>
              </PField>
            </div>
            <PField label="Features (one per line)" :dark="dark" class="mt-2">
              <textarea :value="(selectedElement.features||[]).join('\n')"
                @input="selectedElement.features=$event.target.value.split('\n').filter(Boolean); ph()"
                :class="iCls+' resize-none'" rows="4" placeholder="Feature 1&#10;Feature 2"/>
            </PField>
          </PSection>

          <!-- Kanban -->
          <PSection v-if="selectedElement.type==='kanban'" title="Kanban Card" icon="fa-solid fa-thumbtack" :dark="dark" :open="true">
            <PField label="Task" :dark="dark">
              <input type="text" :value="selectedElement.content||''" @input="selectedElement.content=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <PField label="Status" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.status||''" @input="selectedElement.status=$event.target.value; ph()" :class="iCls" placeholder="In Progress"/>
            </PField>
            <PField label="Due Date" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.due||''" @input="selectedElement.due=$event.target.value; ph()" :class="iCls" placeholder="Dec 31"/>
            </PField>
          </PSection>

          <!-- Social Card -->
          <PSection v-if="selectedElement.type==='social-card'" title="Profile Card" icon="fa-solid fa-id-badge" :dark="dark" :open="true">
            <PField label="Avatar (emoji)" :dark="dark">
              <input type="text" :value="selectedElement.avatar||'👤'" @input="selectedElement.avatar=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <PField label="Name" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.content||''" @input="selectedElement.content=$event.target.value; ph()" :class="iCls"/>
            </PField>
            <PField label="Title" :dark="dark" class="mt-2">
              <input type="text" :value="selectedElement.subtitle||''" @input="selectedElement.subtitle=$event.target.value; ph()" :class="iCls"/>
            </PField>
          </PSection>

          <!-- Icon/Emoji -->
          <PSection v-if="selectedElement.type==='icon'" title="Icon / Emoji" icon="fa-solid fa-star" :dark="dark" :open="true">
            <PField label="Icon / Emoji" :dark="dark">
              <input type="text" :value="selectedElement.content||'★'" @input="selectedElement.content=$event.target.value; ph()" :class="iCls" placeholder="★ or emoji"/>
            </PField>
            <PField label="Size (px)" :dark="dark" class="mt-2">
              <input type="number" :value="selectedElement.styles?.fontSize||40" @input="selectedElement.styles.fontSize=+$event.target.value; ph()" :class="iCls" min="8" max="200"/>
            </PField>
            <CRow label="Color" icon="fa-solid fa-palette" :dark="dark"
              :val="selectedElement.styles?.color||rs.primary_color"
              @upd="v => { selectedElement.styles.color=v; ph() }" class="mt-2"/>
          </PSection>

          <!-- Rating -->
          <PSection v-if="selectedElement.type==='rating'" title="Star Rating" icon="fa-regular fa-star" :dark="dark" :open="true">
            <PField label="Rating (0-5)" :dark="dark">
              <div class="flex items-center gap-2">
                <input type="range" :value="selectedElement.value||4" min="0" max="5" step="0.5"
                  @input="selectedElement.value=+$event.target.value; ph()" class="flex-1 h-1.5 accent-indigo-600"/>
                <span class="text-[10px] font-black text-indigo-500 w-6">{{ selectedElement.value||4 }}</span>
              </div>
            </PField>
            <CRow label="Star Color" icon="fa-solid fa-star" :dark="dark"
              :val="selectedElement.styles?.color||'#f59e0b'"
              @upd="v => { selectedElement.styles.color=v; ph() }" class="mt-2"/>
          </PSection>

          <!-- Date & Page Number -->
          <PSection v-if="selectedElement.type==='date'||selectedElement.type==='pagenum'" :title="selectedElement.type==='date' ? 'Date' : 'Page Number'" icon="fa-solid fa-calendar" :dark="dark" :open="true">
            <PField label="Font Size (px)" :dark="dark">
              <input type="number" :value="selectedElement.styles?.fontSize||12" @input="selectedElement.styles.fontSize=+$event.target.value; ph()" :class="iCls" min="8" max="48"/>
            </PField>
            <CRow label="Color" icon="fa-solid fa-palette" :dark="dark"
              :val="selectedElement.styles?.color||'#94a3b8'"
              @upd="v => { selectedElement.styles.color=v; ph() }" class="mt-2"/>
          </PSection>

        </template>

        <!-- ═══ LAYOUT TAB ═══ -->
        <template v-else-if="activeTab === 'layout'">

          <!-- Alignment -->
          <PSection title="Alignment" icon="fa-solid fa-align-center" :dark="dark" :open="true">
            <div class="grid grid-cols-3 gap-1.5">
              <button v-for="a in ALIGN_BTNS" :key="a.dir"
                @click="$emit('align-element', a.dir)" :title="a.label"
                class="py-2.5 rounded-xl border flex flex-col items-center gap-1.5 transition-all hover:scale-105 text-[9px] font-bold"
                :class="dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-indigo-500 hover:text-indigo-400 hover:bg-indigo-500/8' : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50'">
                <i :class="a.icon + ' text-sm'"></i>
                <span>{{ a.label }}</span>
              </button>
            </div>
          </PSection>

          <!-- Layer order -->
          <PSection title="Layer Order" icon="fa-solid fa-layer-group" :dark="dark" :open="true">
            <PField label="Z-Index" :dark="dark">
              <input type="number" :value="selectedElement.styles?.zIndex||1"
                @input="selectedElement.styles.zIndex=+$event.target.value; ph()" :class="iCls" min="1"/>
            </PField>
            <div class="grid grid-cols-2 gap-1.5 mt-2">
              <button v-for="lb in LAYER_BTNS" :key="lb.action"
                @click="$emit(lb.action)" :title="lb.label"
                class="py-2.5 rounded-xl border flex flex-col items-center gap-1 transition-all hover:scale-105 text-[9px] font-bold"
                :class="dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-indigo-300 hover:text-indigo-600'">
                <i :class="lb.icon + ' text-base'"></i>
                <span>{{ lb.label }}</span>
              </button>
            </div>
          </PSection>

          <!-- Move to page -->
          <PSection v-if="pages.length > 1" title="Move to Page" icon="fa-solid fa-file-export" :dark="dark">
            <div class="flex flex-wrap gap-1.5">
              <button v-for="(p,pi) in pages" :key="pi"
                :disabled="pi === selectedPageIndex"
                @click="$emit('move-element-to-page', selectedElement, selectedPageIndex, pi)"
                class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold border transition-all hover:scale-105 disabled:opacity-40 disabled:cursor-not-allowed"
                :class="pi === selectedPageIndex
                  ? (dark ? 'border-[#21262d] bg-[#161b22] text-slate-600' : 'border-slate-100 bg-slate-50 text-slate-300')
                  : (dark ? 'border-[#30363d] bg-[#161b22] text-slate-400 hover:border-indigo-500 hover:text-indigo-400' : 'border-slate-200 bg-white text-slate-500 hover:border-indigo-300 hover:text-indigo-600')">
                <i class="fa-regular fa-file mr-1" style="font-size:9px"></i>
                {{ pi === selectedPageIndex ? `P${pi+1} (here)` : `Page ${pi+1}` }}
              </button>
            </div>
          </PSection>

          <!-- Visibility & Lock -->
          <PSection title="Visibility & Lock" icon="fa-solid fa-eye" :dark="dark">
            <div class="space-y-2">
              <div class="flex items-center justify-between py-1">
                <label class="text-xs font-semibold flex items-center gap-2" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                  <i class="fa-solid fa-eye text-slate-400" style="font-size:11px"></i> Visible
                </label>
                <button @click="toggleHidden" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                  :class="!selectedElement.hidden ? 'bg-indigo-600' : (dark ? 'bg-slate-700' : 'bg-slate-200')">
                  <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
                    :class="!selectedElement.hidden ? 'translate-x-5' : 'translate-x-1'"></span>
                </button>
              </div>
              <div class="flex items-center justify-between py-1 border-t" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                <label class="text-xs font-semibold flex items-center gap-2" :class="dark ? 'text-slate-400' : 'text-slate-600'">
                  <i class="fa-solid fa-lock-open text-slate-400" style="font-size:11px"></i> Unlocked
                </label>
                <button @click="toggleLocked" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                  :class="!selectedElement.locked ? 'bg-indigo-600' : (dark ? 'bg-slate-700' : 'bg-slate-200')">
                  <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform"
                    :class="!selectedElement.locked ? 'translate-x-5' : 'translate-x-1'"></span>
                </button>
              </div>
            </div>
          </PSection>

        </template>
      </div>

      <!-- Footer -->
      <div class="px-3 py-2 border-t shrink-0 flex items-center justify-between"
        :class="dark ? 'border-[#21262d] bg-[#0d1117]/50' : 'border-slate-100 bg-slate-50'">
        <span class="text-[9px] font-bold capitalize" :class="dark ? 'text-slate-700' : 'text-slate-400'">
          {{ selectedElement.type.replace(/-/g,' ') }}
        </span>
        <span class="text-[9px] font-mono" :class="dark ? 'text-slate-700' : 'text-slate-400'">
          z:{{ selectedElement.styles?.zIndex||1 }}
        </span>
      </div>
    </template>
  </aside>
</template>

<script setup>
import { ref, computed, inject } from 'vue';

const FONTS = inject('FONTS') || [];
const FONT_WEIGHTS = inject('FONT_WEIGHTS') || [];
const CALLOUT_PRESETS = inject('CALLOUT_PRESETS') || {};
const SHORTCUTS = inject('SHORTCUTS') || [];
const CODE_LANGS = inject('CODE_LANGS') || [];

const props = defineProps({
  dark: Boolean, selectedElement: Object, pages: Array,
  selectedPageIndex: Number, rs: Object, inputCls: String,
  chartLabelsInput: String, chartValuesInput: String,
  chartDatasetLabel: String, chartColor: String, pieColorsInput: String,
});

const emit = defineEmits([
  'push-history','delete-selected','bring-to-front','send-to-back',
  'bring-forward','send-backward','align-element','move-element-to-page',
  'update:chart-labels-input','update:chart-values-input',
  'update:chart-dataset-label','update:chart-color','update:pie-colors-input',
  'update-chart-data','refresh-chart','upload-image','apply-callout-style',
  'add-table-row','add-table-column','remove-table-row','remove-table-column',
]);

const activeTab = ref('style');
const PROP_TABS = [
  { id:'style',   label:'Style',   icon:'fa-solid fa-palette' },
  { id:'content', label:'Content', icon:'fa-solid fa-pen' },
  { id:'layout',  label:'Layout',  icon:'fa-solid fa-layer-group' },
];

const TEXT_TYPES = ['heading','subheading','text','quote','blockquote','highlight','callout','alert','badge','link','code','watermark'];
const CHART_TYPES = ['bar-chart','line-chart','pie-chart','doughnut-chart','area-chart','radar-chart'];
const PIE_TYPES = ['pie-chart','doughnut-chart'];

const isTextType = computed(() => TEXT_TYPES.includes(props.selectedElement?.type));
const isChart = computed(() => CHART_TYPES.includes(props.selectedElement?.type));
const isPie = computed(() => PIE_TYPES.includes(props.selectedElement?.type));

const iCls = computed(() =>
  props.dark
    ? 'w-full text-xs bg-[#161b22] border border-[#30363d] rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-200 transition-all'
    : 'w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 transition-all'
);

const ph = () => emit('push-history');
const toggleHidden = () => { if (props.selectedElement) { props.selectedElement.hidden = !props.selectedElement.hidden; ph(); } };
const toggleLocked = () => { if (props.selectedElement) { props.selectedElement.locked = !props.selectedElement.locked; ph(); } };

const TEXT_STYLES = [
  { key:'fontWeight',    active:'700',       normal:'400',    icon:'fa-solid fa-bold',          label:'Bold' },
  { key:'fontStyle',     active:'italic',    normal:'normal', icon:'fa-solid fa-italic',        label:'Italic' },
  { key:'textDecoration',active:'underline', normal:'none',   icon:'fa-solid fa-underline',     label:'Underline' },
  { key:'textDecoration',active:'line-through',normal:'none', icon:'fa-solid fa-strikethrough', label:'Strike' },
];
const TEXT_ALIGNS = [
  { v:'left',    icon:'fa-solid fa-align-left',    label:'Left' },
  { v:'center',  icon:'fa-solid fa-align-center',  label:'Center' },
  { v:'right',   icon:'fa-solid fa-align-right',   label:'Right' },
  { v:'justify', icon:'fa-solid fa-align-justify', label:'Justify' },
];
const isTSActive = (st) => props.selectedElement?.styles?.[st.key] === st.active;
const toggleTS = (st) => {
  if (!props.selectedElement?.styles) return;
  props.selectedElement.styles[st.key] = isTSActive(st) ? st.normal : st.active;
  ph();
};

const ALIGN_BTNS = [
  { dir:'left',      icon:'fa-solid fa-objects-align-left',   label:'Left' },
  { dir:'center-h',  icon:'fa-solid fa-align-center',         label:'H Center' },
  { dir:'right',     icon:'fa-solid fa-objects-align-right',  label:'Right' },
  { dir:'top',       icon:'fa-solid fa-objects-align-top',    label:'Top' },
  { dir:'center-v',  icon:'fa-solid fa-align-justify',        label:'V Center' },
  { dir:'bottom',    icon:'fa-solid fa-objects-align-bottom', label:'Bottom' },
];
const LAYER_BTNS = [
  { action:'bring-to-front', icon:'fa-solid fa-angles-up',   label:'Front' },
  { action:'bring-forward',  icon:'fa-solid fa-angle-up',    label:'Forward' },
  { action:'send-backward',  icon:'fa-solid fa-angle-down',  label:'Backward' },
  { action:'send-to-back',   icon:'fa-solid fa-angles-down', label:'Back' },
];
const SHADOWS = [
  { v:'none', l:'None',   preview:'none' },
  { v:'sm',   l:'Soft',   preview:'0 2px 4px rgba(0,0,0,.15)' },
  { v:'md',   l:'Medium', preview:'0 4px 12px rgba(0,0,0,.15)' },
  { v:'lg',   l:'Large',  preview:'0 8px 24px rgba(0,0,0,.18)' },
  { v:'xl',   l:'XL',     preview:'0 16px 48px rgba(0,0,0,.2)' },
  { v:'2xl',  l:'2XL',    preview:'0 24px 64px rgba(0,0,0,.25)' },
  { v:'glow-indigo', l:'Indigo', preview:'0 0 16px rgba(99,102,241,.5)' },
  { v:'glow-emerald',l:'Green',  preview:'0 0 16px rgba(16,185,129,.5)' },
  { v:'glow-gold',   l:'Gold',   preview:'0 0 16px rgba(245,158,11,.5)' },
];

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
  'double-arrow':'fa-solid fa-arrows-left-right',divider:'fa-solid fa-grip-lines',
  spacer:'fa-solid fa-square-dashed','social-card':'fa-solid fa-id-badge',
  testimonial:'fa-solid fa-comment-dots',kanban:'fa-solid fa-thumbtack',
  'price-card':'fa-solid fa-dollar-sign',pagenum:'fa-solid fa-hashtag',
  date:'fa-solid fa-calendar-days',signature:'fa-solid fa-signature',
};
const getElIcon = (type) => ICON_MAP[type] || 'fa-solid fa-cube';
</script>

<script>
const PSection = {
  name: 'PSection',
  props: { title: String, icon: String, dark: Boolean, open: Boolean },
  data() { return { isOpen: this.open ?? false }; },
  template: `
    <div class="border-b" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
      <button @click="isOpen=!isOpen" class="w-full flex items-center justify-between px-4 py-2.5 transition-colors" :class="dark ? 'hover:bg-white/5' : 'hover:bg-slate-50'">
        <div class="flex items-center gap-2">
          <i :class="icon" :style="isOpen ? 'color:#6366f1;font-size:11px' : (dark ? 'color:#475569;font-size:11px' : 'color:#94a3b8;font-size:11px')"></i>
          <span class="text-[10px] font-black uppercase tracking-wider" :class="dark ? 'text-slate-400' : 'text-slate-600'">{{ title }}</span>
        </div>
        <i class="fa-solid fa-chevron-down transition-transform duration-200" style="font-size:9px" :class="[isOpen ? 'rotate-180' : '', dark ? 'text-slate-700' : 'text-slate-400']"></i>
      </button>
      <div v-show="isOpen" class="px-4 pb-4 pt-1 space-y-2">
        <slot/>
      </div>
    </div>
  `,
};
const PField = {
  name: 'PField',
  props: { label: String, dark: Boolean },
  template: `
    <div>
      <label class="block text-[9px] font-black uppercase tracking-wider mb-1" :class="dark ? 'text-slate-600' : 'text-slate-400'">{{ label }}</label>
      <slot/>
    </div>
  `,
};
const CRow = {
  name: 'CRow',
  props: { label: String, icon: String, dark: Boolean, val: String },
  emits: ['upd'],
  template: `
    <div>
      <label class="block text-[9px] font-black uppercase tracking-wider mb-1" :class="dark ? 'text-slate-600' : 'text-slate-400'">
        <i v-if="icon" :class="icon" style="font-size:9px"></i> {{ label }}
      </label>
      <div class="flex items-center gap-2">
        <input type="color" :value="val==='transparent' ? '#ffffff' : val" @input="$emit('upd', $event.target.value)"
          class="w-9 h-9 rounded-xl border cursor-pointer p-0.5 flex-shrink-0"
          :class="dark ? 'border-[#30363d] bg-[#161b22]' : 'border-slate-200'"/>
        <input type="text" :value="val" @input="$emit('upd', $event.target.value)"
          class="flex-1 text-[10px] font-mono rounded-lg border px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500"
          :class="dark ? 'bg-[#161b22] border-[#30363d] text-slate-300' : 'bg-white border-slate-200 text-slate-700'"/>
        <button @click="$emit('upd','transparent')" title="Transparent"
          class="w-8 h-8 rounded-lg border flex items-center justify-center flex-shrink-0 hover:border-indigo-400 transition-all"
          :class="dark ? 'border-[#30363d] bg-[#161b22]' : 'border-slate-200 bg-white'">
          <span class="text-[10px] font-black" :class="dark ? 'text-slate-500' : 'text-slate-400'">∅</span>
        </button>
      </div>
    </div>
  `,
};
</script>

<style scoped>
.prop-icon-btn {
  width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
  border-radius: 8px; transition: all 0.15s ease; cursor: pointer;
}
.prop-icon-btn:hover { background: rgba(99,102,241,0.1); color: #6366f1; transform: scale(1.1); }
::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 99px; }
</style>