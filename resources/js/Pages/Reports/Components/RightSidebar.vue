<!--
  RightSidebar.vue — Production-Ready Properties Panel
  5 Tabs: Style | Typography | Content | Effects | Arrange
  • Every element type has dedicated content editor
  • Position, size, rotation, z-index controls
  • Complete border, shadow, opacity, blend mode
  • Full typography controls with Google Fonts list
  • CSS filter effects (blur, brightness, contrast, grayscale, sepia)
  • Flip H/V, transform controls
  • Align to page (6 directions), distribute
  • Copy/paste style between elements
  • Shadow presets
  • Priority stripe toggle
  • Memory safe — no leaked listeners
-->
<template>
  <aside class="right-panel" :class="{ collapsed: isCollapsed, 'is-dark': isDark }" role="complementary"
    aria-label="Properties panel">
    <!-- Collapse toggle -->
    <button class="panel-toggle" @click="$emit('update:is-collapsed', !isCollapsed)"
      :title="isCollapsed ? 'Expand properties' : 'Collapse properties'">
      <i :class="isCollapsed ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'" />
    </button>

    <div class="panel-inner" v-show="!isCollapsed">

      <!-- ══ NO SELECTION ═══════════════════════════════════════════════ -->
      <div v-if="!selectedEl" class="no-selection">
        <div class="no-sel-icon"><i class="fa-solid fa-hand-pointer" /></div>
        <h3>No Element Selected</h3>
        <p>Click any element on the canvas to edit its properties</p>
        <div v-if="currentPageElements.length" class="page-stats">
          <div class="stat-pill"><i class="fa-solid fa-cubes" /> {{ currentPageElements.length }} elements</div>
          <div v-if="selectedEls.length > 1" class="stat-pill accent"><i class="fa-solid fa-check-square" /> {{
            selectedEls.length }} selected</div>
        </div>
      </div>

      <!-- ══ MULTI-SELECTION ════════════════════════════════════════════ -->
      <div v-else-if="selectedEls.length > 1" class="multi-select">
        <div class="multi-header">
          <i class="fa-solid fa-object-group" />
          <span>{{ selectedEls.length }} elements selected</span>
        </div>
        <div class="multi-actions">
          <button class="multi-btn" @click="$emit('group-elements')" title="Group elements">
            <i class="fa-solid fa-object-group" /> Group
          </button>
          <button class="multi-btn" @click="$emit('align-elements', 'left')" title="Align left">
            <i class="fa-solid fa-align-left" /> Align Left
          </button>
          <button class="multi-btn" @click="$emit('align-elements', 'center-h')" title="Center horizontally">
            <i class="fa-solid fa-align-center" /> Center H
          </button>
          <button class="multi-btn" @click="$emit('align-elements', 'right')" title="Align right">
            <i class="fa-solid fa-align-right" /> Align Right
          </button>
          <button class="multi-btn" @click="$emit('align-elements', 'top')" title="Align top">
            <i class="fa-solid fa-arrow-up" /> Align Top
          </button>
          <button class="multi-btn" @click="$emit('align-elements', 'center-v')" title="Center vertically">
            <i class="fa-solid fa-arrows-up-down" /> Center V
          </button>
          <button class="multi-btn" @click="$emit('align-elements', 'bottom')" title="Align bottom">
            <i class="fa-solid fa-arrow-down" /> Align Bottom
          </button>
          <button class="multi-btn" @click="$emit('distribute-h')" title="Distribute horizontally">
            <i class="fa-solid fa-distribute-spacing-horizontal" /> Distribute H
          </button>
          <button class="multi-btn" @click="$emit('distribute-v')" title="Distribute vertically">
            <i class="fa-solid fa-distribute-spacing-vertical" /> Distribute V
          </button>
          <button class="multi-btn danger" @click="$emit('delete-el')">
            <i class="fa-solid fa-trash" /> Delete All
          </button>
        </div>
      </div>

      <!-- ══ SINGLE ELEMENT PROPERTIES ═════════════════════════════════ -->
      <template v-else-if="selectedEl">
        <!-- Element type header -->
        <div class="el-header">
          <div class="el-type-badge" :style="{ color: getTypeColor(selectedEl.type) }">
            <i :class="getElIcon(selectedEl.type)" />
            <span>{{ selectedEl.type }}</span>
          </div>
          <div class="el-header-actions">
            <button @click="$emit('duplicate-el')" title="Duplicate [Ctrl+Alt+D]" class="h-btn">
              <i class="fa-solid fa-clone" />
            </button>
            <button @click="$emit('lock-el')" :class="['h-btn', { 'h-btn--active': selectedEl.locked }]"
              :title="selectedEl.locked ? 'Unlock element' : 'Lock element'">
              <i :class="selectedEl.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
            </button>
            <button @click="$emit('delete-el')" title="Delete [Del]" class="h-btn h-btn--danger">
              <i class="fa-solid fa-trash-can" />
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="props-tabs">
          <button v-for="t in PROP_TABS" :key="t.id" class="props-tab" :class="{ active: propsTab === t.id }"
            @click="propsTab = t.id" :title="t.label">
            <i :class="t.icon" />
            <span>{{ t.label }}</span>
          </button>
        </div>

        <!-- TAB: STYLE ─────────────────────────────────────────────── -->
        <div v-show="propsTab === 'style'" class="props-body">

          <!-- Position & Size -->
          <PropSection title="Position & Size" icon="fa-solid fa-arrows-up-down-left-right" defaultOpen>
            <div class="grid-4">
              <PropField label="X">
                <input type="number" :value="Math.round(selectedEl.position?.x || 0)"
                  @input="updatePos('x', +$event.target.value)" class="num-input" />
              </PropField>
              <PropField label="Y">
                <input type="number" :value="Math.round(selectedEl.position?.y || 0)"
                  @input="updatePos('y', +$event.target.value)" class="num-input" />
              </PropField>
              <PropField label="W">
                <input type="number" :value="Math.round(selectedEl.styles?.width || 100)"
                  @input="updateStyle('width', +$event.target.value)" class="num-input" min="4" />
              </PropField>
              <PropField label="H">
                <input type="number" :value="Math.round(selectedEl.styles?.height || 50)"
                  @input="updateStyle('height', +$event.target.value)" class="num-input" min="4" />
              </PropField>
            </div>

            <div class="prop-row">
              <label>Rotation</label>
              <div class="slider-row">
                <input type="range" min="-180" max="180" :value="selectedEl.styles?.rotate || 0"
                  @input="updateStyle('rotate', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ Math.round(selectedEl.styles?.rotate || 0) }}°</span>
              </div>
            </div>

            <div class="prop-row">
              <label>Z-Index</label>
              <input type="number" :value="selectedEl.styles?.zIndex || 1"
                @input="updateStyle('zIndex', +$event.target.value)" class="num-input sm" min="0" max="9999" />
            </div>

            <div class="prop-row">
              <label>Lock Aspect</label>
              <ToggleSwitch :value="selectedEl.styles?.lockAspect" @update="updateStyle('lockAspect', $event)" />
            </div>
          </PropSection>

          <!-- Fill & Color -->
          <PropSection title="Fill & Color" icon="fa-solid fa-palette" defaultOpen>
            <div class="prop-row">
              <label>Background</label>
              <div class="color-row">
                <input type="color"
                  :value="(selectedEl.styles?.backgroundColor === 'transparent' ? '#ffffff' : selectedEl.styles?.backgroundColor) || '#ffffff'"
                  @input="updateStyle('backgroundColor', $event.target.value)" class="color-input" />
                <input type="text" :value="selectedEl.styles?.backgroundColor || 'transparent'"
                  @input="updateStyle('backgroundColor', $event.target.value)" class="color-text-input" />
                <button class="clear-color-btn" @click="updateStyle('backgroundColor', 'transparent')" title="No fill">
                  <i class="fa-solid fa-ban" />
                </button>
              </div>
            </div>

            <div class="prop-row">
              <label>Opacity</label>
              <div class="slider-row">
                <input type="range" min="0" max="100" :value="selectedEl.styles?.opacity ?? 100"
                  @input="updateStyle('opacity', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.opacity ?? 100 }}%</span>
              </div>
            </div>

            <!-- Gradient -->
            <div class="prop-row">
              <label>Gradient</label>
              <ToggleSwitch :value="selectedEl.styles?.useGradient" @update="updateStyle('useGradient', $event)" />
            </div>
            <template v-if="selectedEl.styles?.useGradient">
              <div class="prop-row">
                <label>From</label>
                <input type="color" :value="selectedEl.styles?.gradientFrom || '#6366f1'"
                  @input="updateStyle('gradientFrom', $event.target.value)" class="color-input" />
              </div>
              <div class="prop-row">
                <label>To</label>
                <input type="color" :value="selectedEl.styles?.gradientTo || '#8b5cf6'"
                  @input="updateStyle('gradientTo', $event.target.value)" class="color-input" />
              </div>
              <div class="prop-row">
                <label>Direction</label>
                <select :value="selectedEl.styles?.gradientDir || '135deg'"
                  @change="updateStyle('gradientDir', $event.target.value)" class="prop-select">
                  <option value="90deg">→ Horizontal</option>
                  <option value="180deg">↓ Vertical</option>
                  <option value="135deg">↘ Diagonal</option>
                  <option value="45deg">↗ Diagonal</option>
                  <option value="0deg">↑ Bottom-Up</option>
                </select>
              </div>
            </template>

            <!-- Recent colors -->
            <div class="color-presets">
              <button v-for="c in recentColors" :key="c" class="color-preset" :style="{ background: c }"
                @click="updateStyle('backgroundColor', c)" :title="c" />
              <button class="color-preset color-preset--add" @click="addRecentColor" title="Save current color">
                <i class="fa-solid fa-plus" />
              </button>
            </div>
          </PropSection>

          <!-- Border -->
          <PropSection title="Border" icon="fa-solid fa-border-all">
            <div class="grid-2">
              <PropField label="Width">
                <input type="number" min="0" max="20" :value="selectedEl.styles?.borderWidth || 0"
                  @input="updateStyle('borderWidth', +$event.target.value)" class="num-input" />
              </PropField>
              <PropField label="Style">
                <select :value="selectedEl.styles?.borderStyle || 'solid'"
                  @change="updateStyle('borderStyle', $event.target.value)" class="prop-select">
                  <option value="solid">Solid</option>
                  <option value="dashed">Dashed</option>
                  <option value="dotted">Dotted</option>
                  <option value="double">Double</option>
                  <option value="none">None</option>
                </select>
              </PropField>
            </div>
            <div class="prop-row" v-if="selectedEl.styles?.borderWidth">
              <label>Color</label>
              <div class="color-row">
                <input type="color" :value="selectedEl.styles?.borderColor || '#000000'"
                  @input="updateStyle('borderColor', $event.target.value)" class="color-input" />
                <input type="text" :value="selectedEl.styles?.borderColor || '#000000'"
                  @input="updateStyle('borderColor', $event.target.value)" class="color-text-input" />
              </div>
            </div>
            <div class="prop-row">
              <label>Radius</label>
              <div class="slider-row">
                <input type="range" min="0" max="200" :value="selectedEl.styles?.borderRadius || 0"
                  @input="updateStyle('borderRadius', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.borderRadius || 0 }}px</span>
              </div>
            </div>
          </PropSection>

          <!-- Shadow -->
          <PropSection title="Shadow" icon="fa-solid fa-layer-group">
            <div class="shadow-presets">
              <button v-for="s in SHADOW_PRESETS" :key="s.name" class="shadow-preset"
                :class="{ active: selectedEl.styles?.boxShadow === s.value }" @click="updateStyle('boxShadow', s.value)"
                :title="s.name">
                <div class="shadow-demo" :style="{ boxShadow: s.value }" />
                <span>{{ s.name }}</span>
              </button>
            </div>
            <div class="prop-row">
              <label>Custom</label>
              <input type="text" :value="selectedEl.styles?.boxShadow || ''"
                @input="updateStyle('boxShadow', $event.target.value)" class="prop-input"
                placeholder="0 4px 12px rgba(0,0,0,.15)" />
            </div>
          </PropSection>

          <!-- Priority stripe -->
          <PropSection title="Priority Stripe" icon="fa-solid fa-flag">
            <div class="priority-btns">
              <button v-for="p in ['none', 'low', 'medium', 'high', 'urgent']" :key="p" class="priority-btn"
                :class="['prio-' + p, { active: (selectedEl.styles?.priority || 'none') === p }]"
                @click="updateStyle('priority', p === 'none' ? undefined : p)">
                <span class="prio-dot" :style="{ background: getPriorityColor(p) }" />
                {{ p }}
              </button>
            </div>
          </PropSection>
        </div>

        <!-- TAB: TYPOGRAPHY ─────────────────────────────────────────── -->
        <div v-show="propsTab === 'typography'" class="props-body">
          <template v-if="isTextType(selectedEl.type)">

            <PropSection title="Font" icon="fa-solid fa-font" defaultOpen>
              <div class="prop-row">
                <label>Family</label>
                <select :value="selectedEl.styles?.fontFamily || settings.font_family || 'DM Sans'"
                  @change="updateStyle('fontFamily', $event.target.value)" class="prop-select">
                  <option v-for="f in FONT_LIST" :key="f" :value="f">{{ f }}</option>
                </select>
              </div>
              <div class="grid-2">
                <PropField label="Size">
                  <input type="number" min="6" max="300" :value="selectedEl.styles?.fontSize || 14"
                    @input="updateStyle('fontSize', +$event.target.value)" class="num-input" />
                </PropField>
                <PropField label="Weight">
                  <select :value="selectedEl.styles?.fontWeight || '400'"
                    @change="updateStyle('fontWeight', $event.target.value)" class="prop-select">
                    <option value="300">Light</option>
                    <option value="400">Regular</option>
                    <option value="500">Medium</option>
                    <option value="600">Semi Bold</option>
                    <option value="700">Bold</option>
                    <option value="800">Extra Bold</option>
                    <option value="900">Black</option>
                  </select>
                </PropField>
              </div>
            </PropSection>

            <PropSection title="Color" icon="fa-solid fa-eye-dropper" defaultOpen>
              <div class="prop-row">
                <label>Text</label>
                <div class="color-row">
                  <input type="color" :value="selectedEl.styles?.color || '#000000'"
                    @input="updateStyle('color', $event.target.value)" class="color-input" />
                  <input type="text" :value="selectedEl.styles?.color || '#000000'"
                    @input="updateStyle('color', $event.target.value)" class="color-text-input" />
                </div>
              </div>
              <div class="prop-row">
                <label>Gradient</label>
                <ToggleSwitch :value="selectedEl.styles?.textGradient" @update="updateStyle('textGradient', $event)" />
              </div>
              <template v-if="selectedEl.styles?.textGradient">
                <div class="prop-row">
                  <label>From</label>
                  <input type="color" :value="selectedEl.styles?.textGradientFrom || '#6366f1'"
                    @input="updateStyle('textGradientFrom', $event.target.value)" class="color-input" />
                </div>
                <div class="prop-row">
                  <label>To</label>
                  <input type="color" :value="selectedEl.styles?.textGradientTo || '#ec4899'"
                    @input="updateStyle('textGradientTo', $event.target.value)" class="color-input" />
                </div>
              </template>
            </PropSection>

            <PropSection title="Alignment" icon="fa-solid fa-align-left">
              <div class="align-btns">
                <button v-for="a in ['left', 'center', 'right', 'justify']" :key="a" class="align-btn"
                  :class="{ active: selectedEl.styles?.textAlign === a }" @click="updateStyle('textAlign', a)"
                  :title="`Align ${a}`">
                  <i :class="`fa-solid fa-align-${a}`" />
                </button>
              </div>
            </PropSection>

            <PropSection title="Spacing" icon="fa-solid fa-arrows-left-right-to-line">
              <div class="prop-row">
                <label>Line Height</label>
                <div class="slider-row">
                  <input type="range" min="1" max="4" step="0.1" :value="selectedEl.styles?.lineHeight || 1.5"
                    @input="updateStyle('lineHeight', +$event.target.value)" class="prop-range" />
                  <span class="prop-val">{{ Number(selectedEl.styles?.lineHeight || 1.5).toFixed(1) }}</span>
                </div>
              </div>
              <div class="prop-row">
                <label>Letter Spacing</label>
                <div class="slider-row">
                  <input type="range" min="-4" max="20" step="0.5" :value="selectedEl.styles?.letterSpacing || 0"
                    @input="updateStyle('letterSpacing', +$event.target.value)" class="prop-range" />
                  <span class="prop-val">{{ selectedEl.styles?.letterSpacing || 0 }}px</span>
                </div>
              </div>
              <div class="prop-row">
                <label>Padding</label>
                <input type="number" min="0" max="80" :value="selectedEl.styles?.padding || 0"
                  @input="updateStyle('padding', +$event.target.value)" class="num-input sm" />
              </div>
            </PropSection>

            <PropSection title="Decoration" icon="fa-solid fa-wand-magic-sparkles">
              <div class="prop-row">
                <label>Style</label>
                <div class="btn-group">
                  <button :class="{ active: selectedEl.styles?.fontStyle === 'italic' }"
                    @click="updateStyle('fontStyle', selectedEl.styles?.fontStyle === 'italic' ? 'normal' : 'italic')"
                    title="Italic">
                    <i>I</i>
                  </button>
                  <button :class="{ active: selectedEl.styles?.textDecoration === 'underline' }"
                    @click="updateStyle('textDecoration', selectedEl.styles?.textDecoration === 'underline' ? 'none' : 'underline')"
                    title="Underline">
                    <u>U</u>
                  </button>
                  <button :class="{ active: selectedEl.styles?.textDecoration === 'line-through' }"
                    @click="updateStyle('textDecoration', selectedEl.styles?.textDecoration === 'line-through' ? 'none' : 'line-through')"
                    title="Strikethrough">
                    <s>S</s>
                  </button>
                </div>
              </div>
              <div class="prop-row">
                <label>Transform</label>
                <select :value="selectedEl.styles?.textTransform || 'none'"
                  @change="updateStyle('textTransform', $event.target.value)" class="prop-select">
                  <option value="none">None</option>
                  <option value="uppercase">UPPERCASE</option>
                  <option value="lowercase">lowercase</option>
                  <option value="capitalize">Capitalize</option>
                </select>
              </div>
              <div class="prop-row">
                <label>Columns</label>
                <input type="number" min="1" max="5" :value="selectedEl.styles?.columns || 1"
                  @input="updateStyle('columns', +$event.target.value)" class="num-input sm" />
              </div>
            </PropSection>
          </template>

          <div v-else class="no-props">
            <i class="fa-solid fa-font" />
            <p>Typography options are only available for text elements</p>
          </div>
        </div>

        <!-- TAB: CONTENT ─────────────────────────────────────────────── -->
        <div v-show="propsTab === 'content'" class="props-body">

          <!-- Text content -->
          <PropSection v-if="isTextType(selectedEl.type)" title="Text Content" icon="fa-solid fa-align-left"
            defaultOpen>
            <textarea :value="selectedEl.content || ''" @input="$emit('update:el-prop', 'content', $event.target.value)"
              class="content-textarea" rows="6" placeholder="Enter text content…" />
          </PropSection>

          <!-- Image -->
          <PropSection v-if="selectedEl.type === 'image'" title="Image" icon="fa-solid fa-image" defaultOpen>
            <div class="prop-row">
              <label>URL</label>
              <input type="text" :value="selectedEl.src || ''"
                @input="$emit('update:el-prop', 'src', $event.target.value)" class="prop-input"
                placeholder="https://…" />
            </div>
            <div class="prop-row">
              <label>Alt Text</label>
              <input type="text" :value="selectedEl.alt || ''"
                @input="$emit('update:el-prop', 'alt', $event.target.value)" class="prop-input"
                placeholder="Describe the image…" />
            </div>
            <div class="prop-row">
              <label>Object Fit</label>
              <select :value="selectedEl.styles?.objectFit || 'cover'"
                @change="updateStyle('objectFit', $event.target.value)" class="prop-select">
                <option value="cover">Cover</option>
                <option value="contain">Contain</option>
                <option value="fill">Fill</option>
                <option value="none">None</option>
                <option value="scale-down">Scale Down</option>
              </select>
            </div>
            <div class="prop-row">
              <label>Filter</label>
              <select :value="selectedEl.styles?.imageFilter || 'none'"
                @change="updateStyle('imageFilter', $event.target.value)" class="prop-select">
                <option value="none">None</option>
                <option value="grayscale">Grayscale</option>
                <option value="sepia">Sepia</option>
                <option value="vintage">Vintage</option>
                <option value="blur">Blur</option>
                <option value="bright">Bright</option>
              </select>
            </div>
            <button class="action-btn-full" @click="$emit('image-replace', selectedEl)">
              <i class="fa-solid fa-upload" /> Replace Image
            </button>
          </PropSection>

          <!-- Table -->
          <PropSection v-if="selectedEl.type === 'table'" title="Table Data" icon="fa-solid fa-table" defaultOpen>
            <div class="table-stats">
              <span>{{ (selectedEl.columns || []).length }} columns</span>
              <span>{{ (selectedEl.data || []).length }} rows</span>
            </div>
            <div class="table-actions">
              <button class="tbl-btn" @click="$emit('add-table-row')"><i class="fa-solid fa-plus" /> Row</button>
              <button class="tbl-btn" @click="$emit('add-table-col')"><i class="fa-solid fa-plus" /> Column</button>
              <button class="tbl-btn danger" @click="$emit('remove-table-row')"
                :disabled="(selectedEl.data || []).length <= 1">
                <i class="fa-solid fa-minus" /> Row
              </button>
              <button class="tbl-btn danger" @click="$emit('remove-table-col')"
                :disabled="(selectedEl.columns || []).length <= 1">
                <i class="fa-solid fa-minus" /> Col
              </button>
            </div>
            <div class="prop-row">
              <label>Header BG</label>
              <input type="color" :value="selectedEl.styles?.headerBg || settings.primary_color || '#6366f1'"
                @input="updateStyle('headerBg', $event.target.value)" class="color-input" />
            </div>
            <div class="prop-row">
              <label>Even Row</label>
              <input type="color" :value="selectedEl.styles?.evenRowBg || '#ffffff'"
                @input="updateStyle('evenRowBg', $event.target.value)" class="color-input" />
            </div>
            <div class="prop-row">
              <label>Odd Row</label>
              <input type="color" :value="selectedEl.styles?.oddRowBg || '#f8fafc'"
                @input="updateStyle('oddRowBg', $event.target.value)" class="color-input" />
            </div>
          </PropSection>

          <!-- Charts -->
          <PropSection v-if="isChartType(selectedEl.type)" title="Chart Data" icon="fa-solid fa-chart-bar" defaultOpen>
            <div class="prop-row">
              <label>Title</label>
              <input type="text" :value="selectedEl.chartTitle || ''"
                @input="$emit('update:el-prop', 'chartTitle', $event.target.value)" class="prop-input"
                placeholder="Chart title…" />
            </div>
            <div class="prop-row">
              <label>Type</label>
              <select :value="selectedEl.type" @change="$emit('change-chart-type', $event.target.value)"
                class="prop-select">
                <option value="bar-chart">Bar</option>
                <option value="line-chart">Line</option>
                <option value="area-chart">Area</option>
                <option value="pie-chart">Pie</option>
                <option value="doughnut-chart">Doughnut</option>
                <option value="radar-chart">Radar</option>
                <option value="scatter-chart">Scatter</option>
                <option value="polar-chart">Polar Area</option>
              </select>
            </div>
            <div class="prop-row">
              <label>Labels</label>
              <input type="text" :value="(selectedEl.chartData?.labels || []).join(', ')"
                @input="$emit('set-chart-labels', $event.target.value.split(',').map(s => s.trim()))" class="prop-input"
                placeholder="Q1, Q2, Q3, Q4" />
            </div>
            <div class="prop-row">
              <label>Values</label>
              <input type="text" :value="(selectedEl.chartData?.values || []).join(', ')"
                @input="$emit('set-chart-values', $event.target.value.split(',').map(s => +s.trim()).filter(v => !isNaN(v)))"
                class="prop-input" placeholder="25, 40, 35, 55" />
            </div>
            <div class="prop-row">
              <label>Color</label>
              <input type="color" :value="selectedEl.chartColor || settings.primary_color || '#6366f1'"
                @input="$emit('update:el-prop', 'chartColor', $event.target.value)" class="color-input" />
            </div>
          </PropSection>

          <!-- Metric/KPI -->
          <PropSection v-if="selectedEl.type === 'metric'" title="KPI Card" icon="fa-solid fa-gauge-high" defaultOpen>
            <div class="prop-row"><label>Value</label>
              <input type="text" :value="selectedEl.value || ''"
                @input="$emit('update:el-prop', 'value', $event.target.value)" class="prop-input" placeholder="$48K" />
            </div>
            <div class="prop-row"><label>Label</label>
              <input type="text" :value="selectedEl.label || ''"
                @input="$emit('update:el-prop', 'label', $event.target.value)" class="prop-input"
                placeholder="Revenue" />
            </div>
            <div class="prop-row"><label>Change</label>
              <input type="text" :value="selectedEl.change || ''"
                @input="$emit('update:el-prop', 'change', $event.target.value)" class="prop-input" placeholder="+12%" />
            </div>
            <div class="prop-row"><label>Trend</label>
              <div class="btn-group">
                <button :class="{ active: selectedEl.changeType === 'positive' }"
                  @click="$emit('update:el-prop', 'changeType', 'positive')">▲ Positive</button>
                <button :class="{ active: selectedEl.changeType === 'negative' }"
                  @click="$emit('update:el-prop', 'changeType', 'negative')">▼ Negative</button>
              </div>
            </div>
            <div class="prop-row"><label>Period</label>
              <input type="text" :value="selectedEl.changePeriod || ''"
                @input="$emit('update:el-prop', 'changePeriod', $event.target.value)" class="prop-input"
                placeholder="vs last month" />
            </div>
          </PropSection>

          <!-- Progress -->
          <PropSection v-if="selectedEl.type === 'progress'" title="Progress Bar" icon="fa-solid fa-bars-progress"
            defaultOpen>
            <div class="prop-row"><label>Label</label>
              <input type="text" :value="selectedEl.label || ''"
                @input="$emit('update:el-prop', 'label', $event.target.value)" class="prop-input" />
            </div>
            <div class="prop-row">
              <label>Value</label>
              <div class="slider-row">
                <input type="range" min="0" max="100" :value="selectedEl.value || 0"
                  @input="$emit('update:el-prop', 'value', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.value || 0 }}%</span>
              </div>
            </div>
          </PropSection>

          <!-- Timeline -->
          <PropSection v-if="selectedEl.type === 'timeline'" title="Timeline Items" icon="fa-solid fa-timeline"
            defaultOpen>
            <div v-for="(item, ti) in (selectedEl.items || [])" :key="ti" class="timeline-edit-item">
              <input type="text" :value="item.date" @input="updateTimelineItem(ti, 'date', $event.target.value)"
                class="prop-input" placeholder="Date" />
              <input type="text" :value="item.label" @input="updateTimelineItem(ti, 'label', $event.target.value)"
                class="prop-input" placeholder="Title" />
              <input type="text" :value="item.desc" @input="updateTimelineItem(ti, 'desc', $event.target.value)"
                class="prop-input" placeholder="Description" />
              <button class="tbl-btn danger" @click="$emit('remove-timeline-item', ti)"><i
                  class="fa-solid fa-xmark" /></button>
            </div>
            <button class="action-btn-full" @click="$emit('add-timeline-item')"><i class="fa-solid fa-plus" /> Add
              Item</button>
          </PropSection>

          <!-- Checklist -->
          <PropSection v-if="selectedEl.type === 'checklist'" title="Checklist Items" icon="fa-solid fa-list-check"
            defaultOpen>
            <div v-for="(item, ci) in (selectedEl.items || [])" :key="ci" class="list-edit-row">
              <input type="checkbox" :checked="item.checked"
                @change="updateChecklistItem(ci, 'checked', $event.target.checked)" />
              <input type="text" :value="item.text" @input="updateChecklistItem(ci, 'text', $event.target.value)"
                class="prop-input" />
              <button class="icon-remove-btn" @click="$emit('remove-checklist-item', ci)"><i
                  class="fa-solid fa-xmark" /></button>
            </div>
            <button class="action-btn-full" @click="$emit('add-checklist-item')"><i class="fa-solid fa-plus" /> Add
              Item</button>
          </PropSection>

          <!-- Video -->
          <PropSection v-if="selectedEl.type === 'video'" title="Video" icon="fa-solid fa-video" defaultOpen>
            <div class="prop-row"><label>YouTube URL</label>
              <input type="text" :value="selectedEl.videoUrl || ''"
                @input="$emit('update:el-prop', 'videoUrl', $event.target.value)" class="prop-input"
                placeholder="https://youtube.com/watch?v=…" />
            </div>
          </PropSection>

          <!-- Map -->
          <PropSection v-if="selectedEl.type === 'map'" title="Map" icon="fa-solid fa-map-location-dot" defaultOpen>
            <div class="prop-row"><label>Address</label>
              <input type="text" :value="selectedEl.mapAddress || ''"
                @input="$emit('update:el-prop', 'mapAddress', $event.target.value)" class="prop-input"
                placeholder="New York, USA" />
            </div>
          </PropSection>

          <!-- QR Code -->
          <PropSection v-if="selectedEl.type === 'qr-code'" title="QR Code" icon="fa-solid fa-qrcode" defaultOpen>
            <div class="prop-row"><label>URL/Text</label>
              <input type="text" :value="selectedEl.qrText || ''"
                @input="$emit('update:el-prop', 'qrText', $event.target.value)" class="prop-input"
                placeholder="https://example.com" />
            </div>
            <div class="prop-row">
              <label>Size</label>
              <input type="number" :value="selectedEl.qrSize || 160" min="80" max="500"
                @input="$emit('update:el-prop', 'qrSize', +$event.target.value)" class="num-input sm" />
            </div>
          </PropSection>

          <!-- Rating -->
          <PropSection v-if="selectedEl.type === 'rating'" title="Rating" icon="fa-solid fa-star" defaultOpen>
            <div class="prop-row">
              <label>Score</label>
              <div class="slider-row">
                <input type="range" min="0" max="5" step="0.5" :value="selectedEl.value || 0"
                  @input="$emit('update:el-prop', 'value', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.value || 0 }}</span>
              </div>
            </div>
          </PropSection>

          <!-- Testimonial -->
          <PropSection v-if="selectedEl.type === 'testimonial'" title="Testimonial" icon="fa-solid fa-comment-dots"
            defaultOpen>
            <div class="prop-row"><label>Author</label>
              <input type="text" :value="selectedEl.author || ''"
                @input="$emit('update:el-prop', 'author', $event.target.value)" class="prop-input" />
            </div>
            <div class="prop-row"><label>Role</label>
              <input type="text" :value="selectedEl.role || ''"
                @input="$emit('update:el-prop', 'role', $event.target.value)" class="prop-input" />
            </div>
            <textarea :value="selectedEl.content || ''" @input="$emit('update:el-prop', 'content', $event.target.value)"
              class="content-textarea" rows="3" placeholder="Quote text…" />
          </PropSection>

          <!-- Stat Row -->
          <PropSection v-if="selectedEl.type === 'stat-row'" title="Stats" icon="fa-solid fa-bars-staggered"
            defaultOpen>
            <div v-for="(stat, si) in (selectedEl.stats || [])" :key="si" class="list-edit-row">
              <input type="text" :value="stat.value" @input="updateStatItem(si, 'value', $event.target.value)"
                class="prop-input" placeholder="42K" />
              <input type="text" :value="stat.label" @input="updateStatItem(si, 'label', $event.target.value)"
                class="prop-input" placeholder="Users" />
              <button class="icon-remove-btn" @click="$emit('remove-stat-item', si)"><i
                  class="fa-solid fa-xmark" /></button>
            </div>
            <button class="action-btn-full" @click="$emit('add-stat-item')"><i class="fa-solid fa-plus" /> Add
              Stat</button>
          </PropSection>

        </div>

        <!-- TAB: EFFECTS ─────────────────────────────────────────────── -->
        <div v-show="propsTab === 'effects'" class="props-body">

          <PropSection title="CSS Filters" icon="fa-solid fa-sliders" defaultOpen>
            <div class="prop-row">
              <label>Blur</label>
              <div class="slider-row">
                <input type="range" min="0" max="20" :value="selectedEl.styles?.blur || 0"
                  @input="updateStyle('blur', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.blur || 0 }}px</span>
              </div>
            </div>
            <div class="prop-row">
              <label>Brightness</label>
              <div class="slider-row">
                <input type="range" min="0" max="300" :value="selectedEl.styles?.brightness ?? 100"
                  @input="updateStyle('brightness', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.brightness ?? 100 }}%</span>
              </div>
            </div>
            <div class="prop-row">
              <label>Contrast</label>
              <div class="slider-row">
                <input type="range" min="0" max="300" :value="selectedEl.styles?.contrast ?? 100"
                  @input="updateStyle('contrast', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.contrast ?? 100 }}%</span>
              </div>
            </div>
            <div class="prop-row">
              <label>Grayscale</label>
              <div class="slider-row">
                <input type="range" min="0" max="100" :value="selectedEl.styles?.grayscale || 0"
                  @input="updateStyle('grayscale', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.grayscale || 0 }}%</span>
              </div>
            </div>
            <div class="prop-row">
              <label>Sepia</label>
              <div class="slider-row">
                <input type="range" min="0" max="100" :value="selectedEl.styles?.sepia || 0"
                  @input="updateStyle('sepia', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.sepia || 0 }}%</span>
              </div>
            </div>
            <div class="prop-row">
              <label>Saturate</label>
              <div class="slider-row">
                <input type="range" min="0" max="300" :value="selectedEl.styles?.saturate ?? 100"
                  @input="updateStyle('saturate', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.saturate ?? 100 }}%</span>
              </div>
            </div>
            <div class="prop-row">
              <label>Hue Rotate</label>
              <div class="slider-row">
                <input type="range" min="0" max="360" :value="selectedEl.styles?.hueRotate || 0"
                  @input="updateStyle('hueRotate', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.hueRotate || 0 }}°</span>
              </div>
            </div>
            <div class="prop-row">
              <label>Invert</label>
              <div class="slider-row">
                <input type="range" min="0" max="100" :value="selectedEl.styles?.invert || 0"
                  @input="updateStyle('invert', +$event.target.value)" class="prop-range" />
                <span class="prop-val">{{ selectedEl.styles?.invert || 0 }}%</span>
              </div>
            </div>
            <button class="action-btn-full" style="margin-top:4px" @click="resetFilters">
              <i class="fa-solid fa-rotate-left" /> Reset Filters
            </button>
          </PropSection>

          <PropSection title="Blend Mode" icon="fa-solid fa-circle-half-stroke">
            <div class="prop-row">
              <label>Mode</label>
              <select :value="selectedEl.styles?.mixBlendMode || 'normal'"
                @change="updateStyle('mixBlendMode', $event.target.value)" class="prop-select">
                <option v-for="mode in BLEND_MODES" :key="mode" :value="mode">{{ mode }}</option>
              </select>
            </div>
          </PropSection>

          <PropSection title="Transform" icon="fa-solid fa-rotate">
            <div class="prop-row">
              <label>Flip H</label>
              <ToggleSwitch :value="selectedEl.styles?.scaleX === -1"
                @update="updateStyle('scaleX', $event ? -1 : 1)" />
            </div>
            <div class="prop-row">
              <label>Flip V</label>
              <ToggleSwitch :value="selectedEl.styles?.scaleY === -1"
                @update="updateStyle('scaleY', $event ? -1 : 1)" />
            </div>
          </PropSection>

        </div>

        <!-- TAB: ARRANGE ─────────────────────────────────────────────── -->
        <div v-show="propsTab === 'arrange'" class="props-body">

          <PropSection title="Layer Order" icon="fa-solid fa-layer-group" defaultOpen>
            <div class="arrange-grid">
              <button class="arrange-btn" @click="$emit('bring-front')" title="Bring to Front">
                <i class="fa-solid fa-angles-up" /> To Front
              </button>
              <button class="arrange-btn" @click="$emit('send-back')" title="Send to Back">
                <i class="fa-solid fa-angles-down" /> To Back
              </button>
              <button class="arrange-btn" @click="$emit('bring-forward')" title="Bring Forward">
                <i class="fa-solid fa-angle-up" /> Forward
              </button>
              <button class="arrange-btn" @click="$emit('send-backward')" title="Send Backward">
                <i class="fa-solid fa-angle-down" /> Backward
              </button>
            </div>
          </PropSection>

          <PropSection title="Align to Page" icon="fa-solid fa-arrows-to-dot" defaultOpen>
            <div class="align-grid">
              <button @click="$emit('align-to-page', 'left')" title="Align Left"><i
                  class="fa-solid fa-align-left" /></button>
              <button @click="$emit('align-to-page', 'center-h')" title="Center Horizontal"><i
                  class="fa-solid fa-align-center" /></button>
              <button @click="$emit('align-to-page', 'right')" title="Align Right"><i
                  class="fa-solid fa-align-right" /></button>
              <button @click="$emit('align-to-page', 'top')" title="Align Top"><i
                  class="fa-solid fa-arrow-up" /></button>
              <button @click="$emit('align-to-page', 'center-v')" title="Center Vertical"><i
                  class="fa-solid fa-arrows-up-down" /></button>
              <button @click="$emit('align-to-page', 'bottom')" title="Align Bottom"><i
                  class="fa-solid fa-arrow-down" /></button>
            </div>
          </PropSection>

          <PropSection title="Quick Actions" icon="fa-solid fa-bolt" defaultOpen>
            <div class="quick-actions-grid">
              <button class="qa-btn" @click="$emit('duplicate-el')"><i class="fa-solid fa-clone" /> Duplicate</button>
              <button class="qa-btn" @click="$emit('copy-el')"><i class="fa-solid fa-copy" /> Copy</button>
              <button class="qa-btn" @click="$emit('paste-el')" :disabled="!clipboard"><i class="fa-solid fa-paste" />
                Paste</button>
              <button class="qa-btn" @click="stylePainterCopy"><i class="fa-solid fa-paintbrush" /> Copy Style</button>
              <button class="qa-btn" @click="$emit('style-painter-paste')" :disabled="!stylePainterClipboard"><i
                  class="fa-solid fa-brush" /> Paste Style</button>
              <button class="qa-btn" @click="$emit('reset-styles')"><i class="fa-solid fa-rotate-left" /> Reset
                Styles</button>
              <button class="qa-btn danger" @click="$emit('delete-el')"><i class="fa-solid fa-trash-can" />
                Delete</button>
            </div>
          </PropSection>

        </div>
      </template>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'

// ── Sub-components defined inline (no separate files needed) ──────────
const PropSection = {
  name: 'PropSection',
  props: { title: String, icon: String, defaultOpen: Boolean },
  setup(props) {
    const open = ref(props.defaultOpen !== false)
    return { open }
  },
  template: `
    <div class="prop-section">
      <button class="prop-section-header" @click="open = !open">
        <i :class="icon" class="prop-section-icon" />
        <span>{{ title }}</span>
        <i :class="open ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="prop-section-chevron" />
      </button>
      <div v-if="open" class="prop-section-body">
        <slot />
      </div>
    </div>
  `,
}

const PropField = {
  name: 'PropField',
  props: { label: String },
  template: `
    <div class="prop-field">
      <label class="prop-field-label">{{ label }}</label>
      <slot />
    </div>
  `,
}

const ToggleSwitch = {
  name: 'ToggleSwitch',
  props: { value: Boolean },
  emits: ['update'],
  template: `
    <label class="toggle-sw">
      <input type="checkbox" :checked="value" @change="$emit('update', $event.target.checked)" class="toggle-sw-input" />
      <span class="toggle-sw-track"><span class="toggle-sw-thumb" /></span>
    </label>
  `,
}

// ── Props ──────────────────────────────────────────────────────────────
const props = defineProps({
  selectedEl: { type: Object, default: null },
  selectedEls: { type: Array, default: () => [] },
  settings: { type: Object, required: true },
  currentPageElements: { type: Array, default: () => [] },
  clipboard: { type: Object, default: null },
  stylePainterClipboard: { type: Object, default: null },
  isCollapsed: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
})

// ── Emits ──────────────────────────────────────────────────────────────
const emit = defineEmits([
  'update:style', 'update:el-prop', 'delete-el', 'duplicate-el', 'copy-el',
  'paste-el', 'lock-el', 'bring-front', 'send-back', 'bring-forward', 'send-backward',
  'align-to-page', 'align-elements', 'distribute-h', 'distribute-v',
  'group-elements', 'ungroup-elements', 'reset-styles', 'style-painter-copy', 'style-painter-paste',
  'change-chart-type', 'set-chart-labels', 'set-chart-values',
  'add-table-row', 'add-table-col', 'remove-table-row', 'remove-table-col',
  'add-timeline-item', 'remove-timeline-item',
  'add-checklist-item', 'remove-checklist-item',
  'add-stat-item', 'remove-stat-item',
  'image-replace', 'mark-dirty', 'update:is-collapsed',
])

// ── State ──────────────────────────────────────────────────────────────
const propsTab = ref('style')
const recentColors = ref(['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'])

// ── Constants ──────────────────────────────────────────────────────────
const PROP_TABS = [
  { id: 'style', label: 'Style', icon: 'fa-solid fa-paint-brush' },
  { id: 'typography', label: 'Text', icon: 'fa-solid fa-font' },
  { id: 'content', label: 'Content', icon: 'fa-solid fa-align-left' },
  { id: 'effects', label: 'Effects', icon: 'fa-solid fa-sliders' },
  { id: 'arrange', label: 'Arrange', icon: 'fa-solid fa-layer-group' },
]

const FONT_LIST = [
  'DM Sans', 'Inter', 'Plus Jakarta Sans', 'Space Grotesk', 'Sora', 'Nunito',
  'Outfit', 'Poppins', 'Geist', 'Figtree', 'Georgia', 'Playfair Display',
  'Merriweather', 'Lora', 'Fira Code', 'JetBrains Mono', 'Courier New',
]

const SHADOW_PRESETS = [
  { name: 'None', value: 'none' },
  { name: 'Soft', value: '0 2px 8px rgba(0,0,0,.08)' },
  { name: 'Medium', value: '0 4px 20px rgba(0,0,0,.15)' },
  { name: 'Heavy', value: '0 8px 40px rgba(0,0,0,.25)' },
  { name: 'Glow', value: '0 0 24px rgba(99,102,241,.5)' },
  { name: 'Inset', value: 'inset 0 2px 8px rgba(0,0,0,.1)' },
]

const BLEND_MODES = [
  'normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten',
  'color-dodge', 'color-burn', 'hard-light', 'soft-light',
  'difference', 'exclusion', 'hue', 'saturation', 'color', 'luminosity',
]

// ── Helpers ────────────────────────────────────────────────────────────
function isTextType(type) {
  return ['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight',
    'badge', 'code', 'link', 'callout', 'richtext', 'list'].includes(type)
}

function isChartType(type) {
  return type?.endsWith('-chart')
}

function getElIcon(type) {
  const map = {
    text: 'fa-solid fa-t', heading: 'fa-solid fa-heading', subheading: 'fa-solid fa-text-height',
    richtext: 'fa-solid fa-file-word', quote: 'fa-solid fa-quote-right',
    image: 'fa-solid fa-image', table: 'fa-solid fa-table', metric: 'fa-solid fa-gauge-high',
    progress: 'fa-solid fa-bars-progress', timeline: 'fa-solid fa-timeline',
    checklist: 'fa-solid fa-list-check', testimonial: 'fa-solid fa-comment-dots',
    rectangle: 'fa-solid fa-square', circle: 'fa-solid fa-circle',
    divider: 'fa-solid fa-minus', video: 'fa-solid fa-video',
    map: 'fa-solid fa-map-location-dot', 'qr-code': 'fa-solid fa-qrcode',
    rating: 'fa-solid fa-star', 'stat-row': 'fa-solid fa-bars-staggered',
    sparkline: 'fa-solid fa-wave-square', callout: 'fa-solid fa-lightbulb',
    signature: 'fa-solid fa-signature', badge: 'fa-solid fa-tag',
    'price-card': 'fa-solid fa-credit-card', 'social-card': 'fa-solid fa-id-card',
    'bar-chart': 'fa-solid fa-chart-bar', 'line-chart': 'fa-solid fa-chart-line',
    'pie-chart': 'fa-solid fa-chart-pie', 'doughnut-chart': 'fa-solid fa-circle-half-stroke',
  }
  return map[type] || 'fa-solid fa-cube'
}

function getTypeColor(type) {
  if (isChartType(type) || type === 'table') return '#06b6d4'
  if (isTextType(type)) return '#6366f1'
  if (['image', 'video', 'map'].includes(type)) return '#ec4899'
  if (['metric', 'stat-row', 'progress'].includes(type)) return '#f59e0b'
  if (['rectangle', 'circle', 'triangle', 'divider'].includes(type)) return '#10b981'
  return '#94a3b8'
}

function getPriorityColor(p) {
  return { low: '#3b82f6', medium: '#f59e0b', high: '#f97316', urgent: '#ef4444' }[p] || 'transparent'
}

// ── Update helpers ─────────────────────────────────────────────────────
function updateStyle(prop, value) {
  emit('update:style', prop, value)
}

function updatePos(axis, value) {
  if (!props.selectedEl?.position) return
  props.selectedEl.position[axis] = value
  emit('mark-dirty')
}

function updateTimelineItem(idx, field, value) {
  if (!props.selectedEl?.items) return
  props.selectedEl.items[idx][field] = value
  emit('mark-dirty')
}

function updateChecklistItem(idx, field, value) {
  if (!props.selectedEl?.items) return
  props.selectedEl.items[idx][field] = value
  emit('mark-dirty')
}

function updateStatItem(idx, field, value) {
  if (!props.selectedEl?.stats) return
  props.selectedEl.stats[idx][field] = value
  emit('mark-dirty')
}

function addRecentColor() {
  const c = props.selectedEl?.styles?.backgroundColor
  if (c && c !== 'transparent' && !recentColors.value.includes(c)) {
    recentColors.value.unshift(c)
    if (recentColors.value.length > 16) recentColors.value.pop()
  }
}

function resetFilters() {
  ;['blur', 'brightness', 'contrast', 'grayscale', 'sepia', 'saturate', 'hueRotate', 'invert'].forEach(k => {
    const defaults = { brightness: 100, contrast: 100, saturate: 100 }
    updateStyle(k, defaults[k] ?? 0)
  })
}

function stylePainterCopy() {
  emit('style-painter-copy')
}
</script>

<style scoped>
/* ═══ VARIABLES ════════════════════════════════════════════════════════ */
.right-panel {
  --rp-bg: #ffffff;
  --rp-bg2: #f8fafc;
  --rp-bg3: #f1f5f9;
  --rp-border: #e2e8f0;
  --rp-text: #0f172a;
  --rp-text2: #475569;
  --rp-text3: #94a3b8;
  --rp-accent: #6366f1;
  --rp-accent-l: rgba(99, 102, 241, .08);

  width: 288px;
  flex-shrink: 0;
  background: var(--rp-bg);
  border-left: 1px solid var(--rp-border);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: width .25s ease;
  position: relative;
}

.right-panel.collapsed {
  width: 0;
  border-left: none;
}

.right-panel.is-dark {
  --rp-bg: #1a2236;
  --rp-bg2: #111827;
  --rp-bg3: #0d1424;
  --rp-border: #263348;
  --rp-text: #e2e8f0;
  --rp-text2: #94a3b8;
  --rp-text3: #475569;
  --rp-accent: #818cf8;
  --rp-accent-l: rgba(129, 140, 248, .1);
}

.panel-toggle {
  position: absolute;
  left: -14px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--rp-bg);
  border: 1px solid var(--rp-border);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  color: var(--rp-text3);
  font-size: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
  transition: all .15s;
}

.panel-toggle:hover {
  color: var(--rp-accent);
  border-color: var(--rp-accent);
}

.panel-inner {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  scrollbar-width: thin;
  scrollbar-color: var(--rp-border) transparent;
}

.panel-inner::-webkit-scrollbar {
  width: 4px;
}

.panel-inner::-webkit-scrollbar-thumb {
  background: var(--rp-border);
  border-radius: 99px;
}

/* ═══ NO SELECTION ══════════════════════════════════════════════════════ */
.no-selection {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 40px 24px;
  gap: 8px;
  color: var(--rp-text3);
}

.no-sel-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: var(--rp-bg2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  opacity: .4;
  margin-bottom: 8px;
}

.no-selection h3 {
  font-size: 14px;
  font-weight: 600;
  color: var(--rp-text2);
  margin: 0;
}

.no-selection p {
  font-size: 12px;
  margin: 0;
  line-height: 1.5;
}

.page-stats {
  display: flex;
  gap: 6px;
  margin-top: 8px;
  flex-wrap: wrap;
  justify-content: center;
}

.stat-pill {
  font-size: 11px;
  padding: 4px 10px;
  border-radius: 99px;
  background: var(--rp-bg2);
  border: 1px solid var(--rp-border);
  color: var(--rp-text2);
  display: flex;
  align-items: center;
  gap: 4px;
}

.stat-pill.accent {
  background: var(--rp-accent-l);
  border-color: var(--rp-accent);
  color: var(--rp-accent);
}

/* ═══ MULTI-SELECT ═══════════════════════════════════════════════════════ */
.multi-select {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.multi-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
  color: var(--rp-text);
}

.multi-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
}

.multi-btn {
  padding: 7px 8px;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
  background: var(--rp-bg2);
  color: var(--rp-text2);
  cursor: pointer;
  font-size: 11px;
  font-weight: 500;
  transition: all .14s;
  font-family: inherit;
  display: flex;
  align-items: center;
  gap: 5px;
}

.multi-btn:hover {
  border-color: var(--rp-accent);
  color: var(--rp-accent);
}

.multi-btn.danger:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: rgba(239, 68, 68, .06);
}

/* ═══ ELEMENT HEADER ════════════════════════════════════════════════════ */
.el-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px;
  border-bottom: 1px solid var(--rp-border);
  flex-shrink: 0;
}

.el-type-badge {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 12px;
  font-weight: 600;
  color: var(--rp-text2);
  text-transform: capitalize;
}

.el-header-actions {
  display: flex;
  gap: 3px;
}

.h-btn {
  width: 28px;
  height: 28px;
  border: 1px solid var(--rp-border);
  border-radius: 5px;
  background: transparent;
  cursor: pointer;
  color: var(--rp-text3);
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
}

.h-btn:hover {
  background: var(--rp-bg2);
  color: var(--rp-text);
}

.h-btn--active {
  color: #f59e0b;
  border-color: rgba(245, 158, 11, .4);
  background: rgba(245, 158, 11, .06);
}

.h-btn--danger:hover {
  color: #ef4444;
  border-color: rgba(239, 68, 68, .4);
  background: rgba(239, 68, 68, .06);
}

/* ═══ TABS ═══════════════════════════════════════════════════════════════ */
.props-tabs {
  display: flex;
  border-bottom: 1px solid var(--rp-border);
  flex-shrink: 0;
}

.props-tab {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  padding: 8px 2px 6px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--rp-text3);
  font-size: 9px;
  font-weight: 600;
  letter-spacing: .03em;
  text-transform: uppercase;
  transition: all .14s;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  font-family: inherit;
}

.props-tab i {
  font-size: 12px;
}

.props-tab:hover {
  color: var(--rp-text2);
  background: var(--rp-bg2);
}

.props-tab.active {
  color: var(--rp-accent);
  border-bottom-color: var(--rp-accent);
}

/* ═══ PROPS BODY ════════════════════════════════════════════════════════ */
.props-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* ── Section (using :deep for sub-component styles) ── */
:deep(.prop-section) {
  border-bottom: 1px solid var(--rp-border);
}

:deep(.prop-section-header) {
  display: flex;
  align-items: center;
  gap: 7px;
  width: 100%;
  padding: 10px 14px;
  background: var(--rp-bg2);
  border: none;
  cursor: pointer;
  color: var(--rp-text2);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .05em;
  transition: background .14s;
  font-family: inherit;
}

:deep(.prop-section-header:hover) {
  background: var(--rp-bg3);
}

:deep(.prop-section-icon) {
  color: var(--rp-accent);
  font-size: 12px;
}

:deep(.prop-section-header span) {
  flex: 1;
  text-align: left;
}

:deep(.prop-section-chevron) {
  font-size: 9px;
  opacity: .5;
}

:deep(.prop-section-body) {
  padding: 10px 14px;
  display: flex;
  flex-direction: column;
  gap: 9px;
}

:deep(.prop-field) {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

:deep(.prop-field-label) {
  font-size: 9px;
  font-weight: 700;
  color: var(--rp-text3);
  text-transform: uppercase;
  letter-spacing: .05em;
}

:deep(.toggle-sw) {
  display: inline-flex;
  align-items: center;
  cursor: pointer;
  flex-shrink: 0;
}

:deep(.toggle-sw-input) {
  display: none;
}

:deep(.toggle-sw-track) {
  width: 32px;
  height: 17px;
  background: var(--rp-border);
  border-radius: 99px;
  position: relative;
  transition: background .2s;
}

:deep(.toggle-sw input:checked + .toggle-sw-track) {
  background: var(--rp-accent);
}

:deep(.toggle-sw-thumb) {
  position: absolute;
  width: 11px;
  height: 11px;
  background: #fff;
  border-radius: 50%;
  top: 3px;
  left: 3px;
  transition: transform .2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
}

:deep(.toggle-sw input:checked + .toggle-sw-track .toggle-sw-thumb) {
  transform: translateX(15px);
}

/* ── Shared row / field styles ── */
.prop-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.prop-row label {
  font-size: 11px;
  font-weight: 500;
  color: var(--rp-text2);
  min-width: 64px;
  flex-shrink: 0;
}

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}

.num-input {
  width: 100%;
  padding: 5px 6px;
  border: 1px solid var(--rp-border);
  border-radius: 5px;
  background: var(--rp-bg2);
  color: var(--rp-text);
  font-size: 11px;
  text-align: center;
  outline: none;
  font-family: inherit;
}

.num-input:focus {
  border-color: var(--rp-accent);
}

.num-input.sm {
  width: 60px;
}

.prop-input {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--rp-border);
  border-radius: 5px;
  background: var(--rp-bg2);
  color: var(--rp-text);
  font-size: 11px;
  outline: none;
  font-family: inherit;
}

.prop-input:focus {
  border-color: var(--rp-accent);
}

.prop-select {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--rp-border);
  border-radius: 5px;
  background: var(--rp-bg2);
  color: var(--rp-text);
  font-size: 11px;
  cursor: pointer;
  outline: none;
  font-family: inherit;
}

.prop-select:focus {
  border-color: var(--rp-accent);
}

.prop-range {
  flex: 1;
  accent-color: var(--rp-accent);
  cursor: pointer;
}

.prop-val {
  font-size: 11px;
  font-weight: 600;
  color: var(--rp-text3);
  min-width: 36px;
  text-align: right;
}

.slider-row {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.color-row {
  display: flex;
  align-items: center;
  gap: 5px;
  flex: 1;
}

.color-input {
  width: 28px;
  height: 28px;
  border: 1px solid var(--rp-border);
  border-radius: 5px;
  cursor: pointer;
  padding: 1px;
  background: transparent;
  flex-shrink: 0;
}

.color-text-input {
  flex: 1;
  padding: 4px 7px;
  border: 1px solid var(--rp-border);
  border-radius: 5px;
  background: var(--rp-bg2);
  color: var(--rp-text);
  font-size: 11px;
  font-family: monospace;
  outline: none;
}

.color-text-input:focus {
  border-color: var(--rp-accent);
}

.clear-color-btn {
  width: 22px;
  height: 22px;
  border: 1px solid var(--rp-border);
  border-radius: 4px;
  background: transparent;
  cursor: pointer;
  color: var(--rp-text3);
  font-size: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clear-color-btn:hover {
  color: #ef4444;
  border-color: #ef4444;
}

.color-presets {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
  margin-top: 2px;
}

.color-preset {
  width: 20px;
  height: 20px;
  border-radius: 4px;
  border: 1.5px solid transparent;
  cursor: pointer;
  transition: all .14s;
}

.color-preset:hover {
  transform: scale(1.2);
  border-color: #fff;
  box-shadow: 0 0 0 2px var(--rp-accent);
}

.color-preset--add {
  background: var(--rp-bg2) !important;
  border-color: var(--rp-border) !important;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 8px;
  color: var(--rp-text3);
}

/* ── Shadow presets ── */
.shadow-presets {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
  margin-bottom: 6px;
}

.shadow-preset {
  border: 1.5px solid var(--rp-border);
  border-radius: 7px;
  padding: 8px 4px 5px;
  background: transparent;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  transition: all .14s;
  font-family: inherit;
}

.shadow-preset:hover {
  border-color: var(--rp-accent);
}

.shadow-preset.active {
  border-color: var(--rp-accent);
  background: var(--rp-accent-l);
}

.shadow-demo {
  width: 30px;
  height: 18px;
  border-radius: 4px;
  background: #fff;
  border: 1px solid var(--rp-border);
}

.shadow-preset span {
  font-size: 9px;
  color: var(--rp-text2);
  font-weight: 500;
  text-align: center;
}

/* ── Priority ── */
.priority-btns {
  display: flex;
  gap: 3px;
  flex-wrap: wrap;
}

.priority-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border: 1.5px solid var(--rp-border);
  border-radius: 99px;
  background: transparent;
  cursor: pointer;
  font-size: 10px;
  font-weight: 600;
  text-transform: capitalize;
  color: var(--rp-text2);
  transition: all .14s;
  font-family: inherit;
}

.priority-btn:hover {
  background: var(--rp-bg2);
}

.priority-btn.active {
  border-color: currentColor;
}

.prio-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
}

.prio-low.active {
  color: #3b82f6;
}

.prio-medium.active {
  color: #f59e0b;
}

.prio-high.active {
  color: #f97316;
}

.prio-urgent.active {
  color: #ef4444;
}

/* ── Content editing ── */
.content-textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
  background: var(--rp-bg2);
  color: var(--rp-text);
  font-size: 12px;
  font-family: inherit;
  outline: none;
  resize: vertical;
  line-height: 1.5;
}

.content-textarea:focus {
  border-color: var(--rp-accent);
}

.table-stats {
  display: flex;
  gap: 10px;
  font-size: 11px;
  color: var(--rp-text2);
  font-weight: 500;
}

.table-actions {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 4px;
}

.tbl-btn {
  padding: 5px 4px;
  border: 1px solid var(--rp-border);
  border-radius: 5px;
  background: transparent;
  cursor: pointer;
  font-size: 11px;
  color: var(--rp-text2);
  transition: all .14s;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
}

.tbl-btn:hover {
  border-color: var(--rp-accent);
  color: var(--rp-accent);
}

.tbl-btn.danger:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: rgba(239, 68, 68, .06);
}

.tbl-btn:disabled {
  opacity: .3;
  cursor: not-allowed;
}

.action-btn-full {
  width: 100%;
  padding: 8px;
  border: 1px dashed var(--rp-border);
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  color: var(--rp-text2);
  font-size: 12px;
  font-weight: 500;
  transition: all .14s;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.action-btn-full:hover {
  border-color: var(--rp-accent);
  color: var(--rp-accent);
  background: var(--rp-accent-l);
}

.timeline-edit-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 8px;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
}

.list-edit-row {
  display: flex;
  align-items: center;
  gap: 5px;
}

.icon-remove-btn {
  width: 22px;
  height: 22px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--rp-text3);
  font-size: 10px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all .14s;
}

.icon-remove-btn:hover {
  color: #ef4444;
  background: rgba(239, 68, 68, .08);
}

/* ── Align buttons ── */
.align-btns {
  display: flex;
  gap: 3px;
}

.align-btn {
  width: 32px;
  height: 32px;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  color: var(--rp-text2);
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
}

.align-btn:hover {
  border-color: var(--rp-accent);
  color: var(--rp-accent);
}

.align-btn.active {
  background: var(--rp-accent-l);
  border-color: var(--rp-accent);
  color: var(--rp-accent);
}

.btn-group {
  display: flex;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
  overflow: hidden;
  flex: 1;
}

.btn-group button {
  flex: 1;
  padding: 5px 6px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--rp-text2);
  font-size: 12px;
  font-weight: 500;
  transition: all .14s;
  font-family: inherit;
}

.btn-group button:hover {
  background: var(--rp-bg2);
}

.btn-group button.active {
  background: var(--rp-accent);
  color: #fff;
}

.btn-group button+button {
  border-left: 1px solid var(--rp-border);
}

/* ── Arrange ── */
.arrange-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
}

.arrange-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 10px;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
  background: var(--rp-bg2);
  cursor: pointer;
  color: var(--rp-text2);
  font-size: 11px;
  font-weight: 500;
  transition: all .14s;
  font-family: inherit;
}

.arrange-btn:hover {
  border-color: var(--rp-accent);
  color: var(--rp-accent);
}

.align-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

.align-grid button {
  padding: 10px;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
  background: var(--rp-bg2);
  cursor: pointer;
  color: var(--rp-text2);
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
}

.align-grid button:hover {
  border-color: var(--rp-accent);
  color: var(--rp-accent);
  background: var(--rp-accent-l);
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
}

.qa-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 7px 8px;
  border: 1px solid var(--rp-border);
  border-radius: 6px;
  background: var(--rp-bg2);
  cursor: pointer;
  color: var(--rp-text2);
  font-size: 11px;
  font-weight: 500;
  transition: all .14s;
  font-family: inherit;
}

.qa-btn:hover {
  border-color: var(--rp-accent);
  color: var(--rp-accent);
  background: var(--rp-accent-l);
}

.qa-btn.danger:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: rgba(239, 68, 68, .06);
}

.qa-btn:disabled {
  opacity: .35;
  cursor: not-allowed;
  pointer-events: none;
}

.no-props {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 32px 20px;
  color: var(--rp-text3);
  text-align: center;
}

.no-props i {
  font-size: 28px;
  opacity: .3;
}

.no-props p {
  font-size: 12px;
}

/* ═══ RESPONSIVE ════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .right-panel {
    position: fixed;
    right: 0;
    top: 0;
    bottom: 0;
    height: 100vh;
    z-index: 150;
    box-shadow: -4px 0 24px rgba(0, 0, 0, .15);
  }
}
</style>