<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   RightSidebar - Properties, Style, Text, Content, Effects,    ║
  ║   Arrange, Page Settings                                        ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <aside class="right-panel" :class="{ collapsed: isCollapsed }">
    <!-- Collapse Toggle -->
    <button class="panel-toggle" @click="isCollapsed = !isCollapsed" :title="isCollapsed ? 'Expand' : 'Collapse'">
      <i :class="isCollapsed ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'"></i>
    </button>

    <div v-if="!isCollapsed" class="panel-content">
      
      <!-- ═══ NO SELECTION STATE ═══════════════════════════════ -->
      <div v-if="!selectedEl" class="no-selection">
        <div class="no-sel-icon">
          <i class="fa-solid fa-hand-pointer"></i>
        </div>
        <h3>No Element Selected</h3>
        <p>Click on any element on the canvas to edit its properties</p>
        <div class="quick-stats" v-if="(currentPageElements || []).length">
          <div class="stat-chip">
            <i class="fa-solid fa-cubes"></i>
            {{ (props.currentPageElements || []).length }} elements
          </div>
          <div class="stat-chip" @click="$emit('deselect-all')" v-if="(selectedEls || []).length">
            <i class="fa-solid fa-layer-group"></i>
            {{ selectedEls?.length || 0 }} selected
          </div>
        </div>
      </div>

      <!-- ═══ ELEMENT PROPERTIES ═══════════════════════════════ -->
      <div v-else class="props-container">
        
        <!-- Element Header -->
        <div class="props-header">
          <div class="el-type-info">
            <i :class="getElIcon(selectedEl.type)" class="type-icon"></i>
            <div>
              <span class="type-name">{{ selectedEl.type }}</span>
              <span class="type-id" title="Element ID">{{ selectedEl.id?.substring(0, 8) }}</span>
            </div>
          </div>
          <div class="props-actions">
            <button @click="$emit('duplicate-el')" title="Duplicate (Ctrl+D)">
              <i class="fa-solid fa-clone"></i>
            </button>
            <button @click="$emit('lock-el')" :class="{ active: selectedEl.locked }" title="Lock/Unlock">
              <i :class="selectedEl.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'"></i>
            </button>
            <button @click="$emit('delete-el')" class="danger" title="Delete (Del)">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        </div>

        <!-- Properties Tabs -->
        <div class="props-tabs">
          <button
            v-for="tab in propTabs"
            :key="tab.id"
            class="props-tab"
            :class="{ active: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            <i :class="tab.icon"></i>
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- Tab Content -->
        <div class="props-body">
          
          <!-- ═══ STYLE TAB ═══════════════════════════════════ -->
          <div v-show="activeTab === 'style'" class="tab-content">
            
            <!-- Position & Size -->
            <div class="prop-section">
              <div class="section-title" @click="toggleSection('position')">
                <i class="fa-solid fa-arrows-up-down-left-right"></i>
                Position & Size
                <i :class="collapsedSections.includes('position') ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'" class="collapse-icon"></i>
              </div>
              
              <div v-if="!collapsedSections.includes('position')" class="section-body">
                <div class="prop-grid-4">
                  <div class="prop-field">
                    <label>X</label>
                    <input
                      type="number"
                      :value="Math.round(selectedEl.position?.x || 0)"
                      @input="updatePosition('x', +$event.target.value)"
                      class="prop-input"
                    />
                  </div>
                  <div class="prop-field">
                    <label>Y</label>
                    <input
                      type="number"
                      :value="Math.round(selectedEl.position?.y || 0)"
                      @input="updatePosition('y', +$event.target.value)"
                      class="prop-input"
                    />
                  </div>
                  <div class="prop-field">
                    <label>W</label>
                    <input
                      type="number"
                      :value="Math.round(selectedEl.styles?.width || 100)"
                      @input="updateStyle('width', +$event.target.value)"
                      class="prop-input"
                      min="10"
                    />
                  </div>
                  <div class="prop-field">
                    <label>H</label>
                    <input
                      type="number"
                      :value="Math.round(selectedEl.styles?.height || 100)"
                      @input="updateStyle('height', +$event.target.value)"
                      class="prop-input"
                      min="10"
                    />
                  </div>
                </div>

                <div class="prop-row">
                  <label>Rotation</label>
                  <div class="slider-row">
                    <input
                      type="range"
                      min="-180"
                      max="180"
                      :value="selectedEl.styles?.rotate || 0"
                      @input="updateStyle('rotate', +$event.target.value)"
                    />
                    <span class="val">{{ Math.round(selectedEl.styles?.rotate || 0) }}°</span>
                  </div>
                </div>

                <div class="prop-row">
                  <label>Z-Index</label>
                  <input
                    type="number"
                    :value="selectedEl.styles?.zIndex || 1"
                    @input="updateStyle('zIndex', +$event.target.value)"
                    class="prop-input sm"
                    min="0"
                    max="9999"
                  />
                </div>

                <div class="prop-row">
                  <label>Lock Aspect</label>
                  <button
                    class="toggle-btn"
                    :class="{ active: selectedEl.styles?.lockAspect }"
                    @click="updateStyle('lockAspect', !selectedEl.styles?.lockAspect)"
                  >
                    {{ selectedEl.styles?.lockAspect ? 'ON' : 'OFF' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Fill & Colors -->
            <div class="prop-section">
              <div class="section-title" @click="toggleSection('fill')">
                <i class="fa-solid fa-palette"></i>
                Fill & Color
                <i :class="collapsedSections.includes('fill') ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'" class="collapse-icon"></i>
              </div>
              
              <div v-if="!collapsedSections.includes('fill')" class="section-body">
                <div class="prop-row">
                  <label>Background</label>
                  <div class="color-row">
                    <input
                      type="color"
                      :value="selectedEl.styles?.backgroundColor === 'transparent' ? '#ffffff' : (selectedEl.styles?.backgroundColor || '#ffffff')"
                      @input="updateStyle('backgroundColor', $event.target.value)"
                      class="color-input"
                    />
                    <input
                      type="text"
                      :value="selectedEl.styles?.backgroundColor || 'transparent'"
                      @input="updateStyle('backgroundColor', $event.target.value)"
                      class="prop-input mono sm"
                    />
                    <button
                      class="mini-btn"
                      @click="updateStyle('backgroundColor', 'transparent')"
                      title="No fill"
                    >
                      <i class="fa-solid fa-ban"></i>
                    </button>
                  </div>
                </div>

                <div class="prop-row">
                  <label>Gradient</label>
                  <div class="color-row">
                    <input
                      type="color"
                      :value="selectedEl.styles?.gradientFrom || '#6366f1'"
                      @input="updateStyle('gradientFrom', $event.target.value)"
                      class="color-input"
                      title="Gradient From"
                    />
                    <i class="fa-solid fa-arrow-right"></i>
                    <input
                      type="color"
                      :value="selectedEl.styles?.gradientTo || '#8b5cf6'"
                      @input="updateStyle('gradientTo', $event.target.value)"
                      class="color-input"
                      title="Gradient To"
                    />
                    <select
                      :value="selectedEl.styles?.gradientDirection || 'to right'"
                      @change="updateStyle('gradientDirection', $event.target.value)"
                      class="prop-select sm"
                    >
                      <option value="to right">→</option>
                      <option value="to bottom">↓</option>
                      <option value="to bottom right">↘</option>
                      <option value="to top">↑</option>
                    </select>
                  </div>
                </div>

                <div class="prop-row">
                  <label>Opacity</label>
                  <div class="slider-row">
                    <input
                      type="range"
                      min="0"
                      max="100"
                      :value="selectedEl.styles?.opacity ?? 100"
                      @input="updateStyle('opacity', +$event.target.value)"
                    />
                    <span class="val">{{ selectedEl.styles?.opacity ?? 100 }}%</span>
                  </div>
                </div>

                <div class="color-presets">
                  <button
                    v-for="color in recentColors"
                    :key="color"
                    class="color-preset-dot"
                    :style="{ background: color }"
                    @click="updateStyle('backgroundColor', color)"
                    :title="color"
                  ></button>
                  <button class="color-preset-dot add-color" @click="addCurrentColor" title="Add current color">
                    <i class="fa-solid fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Border -->
            <div class="prop-section">
              <div class="section-title" @click="toggleSection('border')">
                <i class="fa-solid fa-border-all"></i>
                Border
                <i :class="collapsedSections.includes('border') ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'" class="collapse-icon"></i>
              </div>
              
              <div v-if="!collapsedSections.includes('border')" class="section-body">
                <div class="prop-grid-2">
                  <div class="prop-field">
                    <label>Width</label>
                    <input
                      type="number"
                      min="0"
                      max="20"
                      :value="selectedEl.styles?.borderWidth || 0"
                      @input="updateStyle('borderWidth', +$event.target.value)"
                      class="prop-input"
                    />
                  </div>
                  <div class="prop-field">
                    <label>Style</label>
                    <select
                      :value="selectedEl.styles?.borderStyle || 'solid'"
                      @change="updateStyle('borderStyle', $event.target.value)"
                      class="prop-select"
                    >
                      <option value="solid">Solid</option>
                      <option value="dashed">Dashed</option>
                      <option value="dotted">Dotted</option>
                      <option value="double">Double</option>
                      <option value="none">None</option>
                    </select>
                  </div>
                </div>

                <div class="prop-row" v-if="selectedEl.styles?.borderWidth">
                  <label>Color</label>
                  <div class="color-row">
                    <input
                      type="color"
                      :value="selectedEl.styles?.borderColor || '#000000'"
                      @input="updateStyle('borderColor', $event.target.value)"
                      class="color-input"
                    />
                    <input
                      type="text"
                      :value="selectedEl.styles?.borderColor || '#000000'"
                      @input="updateStyle('borderColor', $event.target.value)"
                      class="prop-input mono sm"
                    />
                  </div>
                </div>

                <div class="prop-row">
                  <label>Radius</label>
                  <div class="slider-row">
                    <input
                      type="range"
                      min="0"
                      max="100"
                      :value="selectedEl.styles?.borderRadius || 0"
                      @input="updateStyle('borderRadius', +$event.target.value)"
                    />
                    <span class="val">{{ selectedEl.styles?.borderRadius || 0 }}px</span>
                  </div>
                </div>

                <!-- Individual corners -->
                <div class="prop-row">
                  <label>Lock Corners</label>
                  <button
                    class="toggle-btn"
                    :class="{ active: !selectedEl.styles?.individualCorners }"
                    @click="updateStyle('individualCorners', !selectedEl.styles?.individualCorners)"
                  >
                    <i :class="selectedEl.styles?.individualCorners ? 'fa-solid fa-lock-open' : 'fa-solid fa-lock'"></i>
                  </button>
                </div>

                <div v-if="selectedEl.styles?.individualCorners" class="prop-grid-2">
                  <div class="prop-field" v-for="corner in ['TopLeft', 'TopRight', 'BottomRight', 'BottomLeft']" :key="corner">
                    <label>{{ corner.replace(/([A-Z])/g, ' $1').trim() }}</label>
                    <input
                      type="number"
                      min="0"
                      max="100"
                      :value="selectedEl.styles?.['border' + corner + 'Radius'] || selectedEl.styles?.borderRadius || 0"
                      @input="updateStyle('border' + corner + 'Radius', +$event.target.value)"
                      class="prop-input"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Shadow -->
            <div class="prop-section">
              <div class="section-title" @click="toggleSection('shadow')">
                <i class="fa-solid fa-layer-group"></i>
                Shadow
                <i :class="collapsedSections.includes('shadow') ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'" class="collapse-icon"></i>
              </div>
              
              <div v-if="!collapsedSections.includes('shadow')" class="section-body">
                <div class="shadow-presets">
                  <button
                    v-for="preset in shadowPresets"
                    :key="preset.name"
                    class="shadow-preset"
                    :class="{ active: selectedEl.styles?.boxShadow === preset.value }"
                    @click="updateStyle('boxShadow', preset.value)"
                    :title="preset.name"
                  >
                    <div class="shadow-preview" :style="{ boxShadow: preset.value }"></div>
                    <span>{{ preset.name }}</span>
                  </button>
                </div>
                <div class="prop-row">
                  <label>Custom</label>
                  <input
                    type="text"
                    :value="selectedEl.styles?.boxShadow || ''"
                    @input="updateStyle('boxShadow', $event.target.value)"
                    class="prop-input"
                    placeholder="0 4px 20px rgba(0,0,0,0.15)"
                  />
                </div>
              </div>
            </div>

            <!-- Priority Stripe -->
            <div class="prop-section">
              <div class="section-title" @click="toggleSection('priority')">
                <i class="fa-solid fa-flag"></i>
                Priority Stripe
                <i :class="collapsedSections.includes('priority') ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-down'" class="collapse-icon"></i>
              </div>
              
              <div v-if="!collapsedSections.includes('priority')" class="section-body">
                <div class="priority-options">
                  <button
                    v-for="p in ['none', 'low', 'medium', 'high', 'urgent']"
                    :key="p"
                    class="priority-btn"
                    :class="[p, { active: (selectedEl.styles?.priority || 'none') === p }]"
                    @click="updateStyle('priority', p === 'none' ? undefined : p)"
                  >
                    <span class="priority-dot" :style="{ background: getPriorityColor(p) }"></span>
                    {{ p }}
                  </button>
                </div>
              </div>
            </div>

          </div>

          <!-- ═══ TYPOGRAPHY TAB ═══════════════════════════════ -->
          <div v-show="activeTab === 'typography'" class="tab-content">
            <div v-if="isTextElement(selectedEl.type)" class="prop-section">
              <div class="section-title">Font Family</div>
              <div class="section-body">
                <select
                  :value="selectedEl.styles?.fontFamily || 'Inter'"
                  @change="updateStyle('fontFamily', $event.target.value)"
                  class="prop-select"
                >
                  <option v-for="f in fontList" :key="f" :value="f">{{ f }}</option>
                </select>
              </div>
            </div>

            <div v-if="isTextElement(selectedEl.type)" class="prop-section">
              <div class="section-title">Size & Weight</div>
              <div class="section-body">
                <div class="prop-grid-2">
                  <div class="prop-field">
                    <label>Size</label>
                    <input
                      type="number"
                      :value="selectedEl.styles?.fontSize || 14"
                      @input="updateStyle('fontSize', +$event.target.value)"
                      class="prop-input"
                      min="8"
                      max="200"
                    />
                  </div>
                  <div class="prop-field">
                    <label>Weight</label>
                    <select
                      :value="selectedEl.styles?.fontWeight || '400'"
                      @change="updateStyle('fontWeight', $event.target.value)"
                      class="prop-select"
                    >
                      <option value="300">Light</option>
                      <option value="400">Regular</option>
                      <option value="500">Medium</option>
                      <option value="600">Semi Bold</option>
                      <option value="700">Bold</option>
                      <option value="800">Extra Bold</option>
                      <option value="900">Black</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="isTextElement(selectedEl.type)" class="prop-section">
              <div class="section-title">Text Color</div>
              <div class="section-body">
                <div class="color-row">
                  <input
                    type="color"
                    :value="selectedEl.styles?.color || '#000000'"
                    @input="updateStyle('color', $event.target.value)"
                    class="color-input"
                  />
                  <input
                    type="text"
                    :value="selectedEl.styles?.color || '#000000'"
                    @input="updateStyle('color', $event.target.value)"
                    class="prop-input mono sm"
                  />
                </div>
              </div>
            </div>

            <div v-if="isTextElement(selectedEl.type)" class="prop-section">
              <div class="section-title">Alignment</div>
              <div class="section-body">
                <div class="btn-group-4">
                  <button v-for="a in ['left', 'center', 'right', 'justify']" :key="a"
                    class="btn-group-btn"
                    :class="{ active: selectedEl.styles?.textAlign === a }"
                    @click="updateStyle('textAlign', a)"
                  >
                    <i :class="`fa-solid fa-align-${a}`"></i>
                  </button>
                </div>
              </div>
            </div>

            <div v-if="isTextElement(selectedEl.type)" class="prop-section">
              <div class="section-title">Spacing</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Line Height</label>
                  <div class="slider-row">
                    <input
                      type="range"
                      min="1"
                      max="3"
                      step="0.1"
                      :value="selectedEl.styles?.lineHeight || 1.5"
                      @input="updateStyle('lineHeight', +$event.target.value)"
                    />
                    <span class="val">{{ (selectedEl.styles?.lineHeight || 1.5).toFixed(1) }}</span>
                  </div>
                </div>
                <div class="prop-row">
                  <label>Letter Spacing</label>
                  <div class="slider-row">
                    <input
                      type="range"
                      min="-2"
                      max="10"
                      step="0.5"
                      :value="selectedEl.styles?.letterSpacing || 0"
                      @input="updateStyle('letterSpacing', +$event.target.value)"
                    />
                    <span class="val">{{ selectedEl.styles?.letterSpacing || 0 }}px</span>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="isTextElement(selectedEl.type)" class="prop-section">
              <div class="section-title">Transform & Style</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Transform</label>
                  <select
                    :value="selectedEl.styles?.textTransform || 'none'"
                    @change="updateStyle('textTransform', $event.target.value)"
                    class="prop-select"
                  >
                    <option value="none">None</option>
                    <option value="uppercase">UPPERCASE</option>
                    <option value="lowercase">lowercase</option>
                    <option value="capitalize">Capitalize</option>
                  </select>
                </div>
                <div class="prop-row">
                  <label>Padding</label>
                  <input
                    type="number"
                    min="0"
                    max="80"
                    :value="selectedEl.styles?.padding || 0"
                    @input="updateStyle('padding', +$event.target.value)"
                    class="prop-input sm"
                  />
                </div>
              </div>
            </div>

            <div v-if="!isTextElement(selectedEl.type)" class="no-props">
              <i class="fa-solid fa-font"></i>
              <p>Typography options are available for text elements only</p>
            </div>
          </div>

          <!-- ═══ CONTENT TAB ═══════════════════════════════ -->
          <div v-show="activeTab === 'content'" class="tab-content">
            
            <!-- Text Content -->
            <div v-if="isTextElement(selectedEl.type)" class="prop-section">
              <div class="section-title">Text Content</div>
              <div class="section-body">
                <textarea
                  :value="selectedEl.content || ''"
                  @input="$emit('update:content', $event.target.value)"
                  class="content-textarea"
                  rows="6"
                  placeholder="Enter content..."
                ></textarea>
              </div>
            </div>

            <!-- Image Content -->
            <div v-if="selectedEl.type === 'image'" class="prop-section">
              <div class="section-title">Image Settings</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>URL</label>
                  <input
                    type="text"
                    :value="selectedEl.src || ''"
                    @input="updateElProp('src', $event.target.value)"
                    class="prop-input"
                    placeholder="https://..."
                  />
                </div>
                <div class="prop-row">
                  <label>Alt Text</label>
                  <input
                    type="text"
                    :value="selectedEl.alt || ''"
                    @input="updateElProp('alt', $event.target.value)"
                    class="prop-input"
                  />
                </div>
                <div class="prop-row">
                  <label>Object Fit</label>
                  <select
                    :value="selectedEl.styles?.objectFit || 'cover'"
                    @change="updateStyle('objectFit', $event.target.value)"
                    class="prop-select"
                  >
                    <option value="cover">Cover</option>
                    <option value="contain">Contain</option>
                    <option value="fill">Fill</option>
                    <option value="none">None</option>
                    <option value="scale-down">Scale Down</option>
                  </select>
                </div>
                <button class="prop-btn" @click="$emit('image-replace', selectedEl)">
                  <i class="fa-solid fa-upload"></i> Replace Image
                </button>
              </div>
            </div>

            <!-- Table Content -->
            <div v-if="selectedEl.type === 'table'" class="prop-section">
              <div class="section-title">Table</div>
              <div class="section-body">
                <div class="prop-row-between">
                  <span>{{ selectedEl.data?.length || 0 }} rows</span>
                  <button class="mini-btn" @click="$emit('add-table-row')">+ Row</button>
                </div>
                <div class="prop-row-between">
                  <span>{{ selectedEl.columns?.length || 0 }} columns</span>
                  <button class="mini-btn" @click="$emit('add-table-col')">+ Col</button>
                </div>
                <div class="prop-row-between" v-if="selectedEl.data?.length > 1">
                  <button class="mini-btn danger" @click="$emit('remove-table-row')">− Last Row</button>
                </div>
                <div class="prop-row-between" v-if="selectedEl.columns?.length > 1">
                  <button class="mini-btn danger" @click="$emit('remove-table-col')">− Last Col</button>
                </div>
              </div>
            </div>

            <!-- Chart Content -->
            <div v-if="isChartType(selectedEl.type)" class="prop-section">
              <div class="section-title">Chart Data</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Title</label>
                  <input
                    type="text"
                    :value="selectedEl.chartTitle || ''"
                    @input="updateElProp('chartTitle', $event.target.value)"
                    class="prop-input"
                  />
                </div>
                <div class="prop-row">
                  <label>Labels</label>
                  <input
                    type="text"
                    :value="(selectedEl.chartData?.labels || []).join(',')"
                    @input="$emit('set-chart-labels', $event.target.value.split(',').map(s => s.trim()))"
                    class="prop-input"
                    placeholder="Q1, Q2, Q3, Q4"
                  />
                </div>
                <div class="prop-row">
                  <label>Values</label>
                  <input
                    type="text"
                    :value="(selectedEl.chartData?.values || []).join(',')"
                    @input="$emit('set-chart-values', $event.target.value.split(',').map(s => +s.trim()).filter(v => !isNaN(v)))"
                    class="prop-input"
                    placeholder="25, 40, 35, 55"
                  />
                </div>
              </div>
            </div>

            <!-- Metric Content -->
            <div v-if="selectedEl.type === 'metric'" class="prop-section">
              <div class="section-title">KPI Card</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Value</label>
                  <input type="text" :value="selectedEl.value || ''" @input="updateElProp('value', $event.target.value)" class="prop-input" />
                </div>
                <div class="prop-row">
                  <label>Label</label>
                  <input type="text" :value="selectedEl.label || ''" @input="updateElProp('label', $event.target.value)" class="prop-input" />
                </div>
                <div class="prop-row">
                  <label>Change</label>
                  <input type="text" :value="selectedEl.change || ''" @input="updateElProp('change', $event.target.value)" class="prop-input" placeholder="+12%" />
                </div>
                <div class="prop-row">
                  <label>Type</label>
                  <select :value="selectedEl.changeType || 'positive'" @change="updateElProp('changeType', $event.target.value)" class="prop-select">
                    <option value="positive">Positive</option>
                    <option value="negative">Negative</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Progress Content -->
            <div v-if="selectedEl.type === 'progress'" class="prop-section">
              <div class="section-title">Progress Bar</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Label</label>
                  <input type="text" :value="selectedEl.label || ''" @input="updateElProp('label', $event.target.value)" class="prop-input" />
                </div>
                <div class="prop-row">
                  <label>Value</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="100" :value="selectedEl.value || 0" @input="updateElProp('value', +$event.target.value)" />
                    <span class="val">{{ selectedEl.value || 0 }}%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Timeline Content -->
            <div v-if="selectedEl.type === 'timeline'" class="prop-section">
              <div class="section-title">Timeline Items</div>
              <div class="section-body">
                <div v-for="(item, ti) in (selectedEl.items || [])" :key="ti" class="timeline-edit-item">
                  <input type="text" :value="item.date" @input="item.date = $event.target.value; markDirty()" placeholder="Date" class="prop-input sm" />
                  <input type="text" :value="item.label" @input="item.label = $event.target.value; markDirty()" placeholder="Title" class="prop-input sm" />
                  <input type="text" :value="item.desc" @input="item.desc = $event.target.value; markDirty()" placeholder="Description" class="prop-input sm" />
                  <button class="mini-btn danger" @click="$emit('remove-timeline-item', ti)">✕</button>
                </div>
                <button class="prop-btn" @click="$emit('add-timeline-item')">+ Add Item</button>
              </div>
            </div>

            <!-- Checklist Content -->
            <div v-if="selectedEl.type === 'checklist'" class="prop-section">
              <div class="section-title">Checklist Items</div>
              <div class="section-body">
                <div v-for="(item, ci) in (selectedEl.items || [])" :key="ci" class="list-edit-row">
                  <input type="text" :value="item.text" @input="item.text = $event.target.value; markDirty()" class="prop-input" />
                  <button class="mini-btn danger" @click="$emit('remove-checklist-item', ci)">✕</button>
                </div>
                <button class="prop-btn" @click="$emit('add-checklist-item')">+ Add Item</button>
              </div>
            </div>

            <!-- Testimonial Content -->
            <div v-if="selectedEl.type === 'testimonial'" class="prop-section">
              <div class="section-title">Testimonial</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Author</label>
                  <input type="text" :value="selectedEl.author || ''" @input="updateElProp('author', $event.target.value)" class="prop-input" />
                </div>
                <div class="prop-row">
                  <label>Role</label>
                  <input type="text" :value="selectedEl.role || ''" @input="updateElProp('role', $event.target.value)" class="prop-input" />
                </div>
              </div>
            </div>

            <!-- Stat Row Content -->
            <div v-if="selectedEl.type === 'stat-row'" class="prop-section">
              <div class="section-title">Stats</div>
              <div class="section-body">
                <div v-for="(stat, si) in (selectedEl.stats || [])" :key="si" class="list-edit-row">
                  <input type="text" :value="stat.value" @input="stat.value = $event.target.value; markDirty()" placeholder="Value" class="prop-input sm" />
                  <input type="text" :value="stat.label" @input="stat.label = $event.target.value; markDirty()" placeholder="Label" class="prop-input sm" />
                  <button class="mini-btn danger" @click="$emit('remove-stat-item', si)">✕</button>
                </div>
                <button class="prop-btn" @click="$emit('add-stat-item')">+ Add Stat</button>
              </div>
            </div>

            <!-- QR Code Content -->
            <div v-if="selectedEl.type === 'qr-code'" class="prop-section">
              <div class="section-title">QR Code</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Text/URL</label>
                  <input type="text" :value="selectedEl.qrText || 'https://example.com'" @input="updateElProp('qrText', $event.target.value)" class="prop-input" />
                </div>
                <div class="prop-row">
                  <label>Size</label>
                  <input type="number" :value="selectedEl.qrSize || 150" @input="updateElProp('qrSize', +$event.target.value)" class="prop-input sm" min="50" max="500" />
                </div>
              </div>
            </div>

            <!-- Rating Content -->
            <div v-if="selectedEl.type === 'rating'" class="prop-section">
              <div class="section-title">Rating</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Value</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="5" step="0.5" :value="selectedEl.value || 0" @input="updateElProp('value', +$event.target.value)" />
                    <span class="val">{{ selectedEl.value || 0 }}</span>
                  </div>
                </div>
                <div class="prop-row">
                  <label>Star Color</label>
                  <input type="color" :value="selectedEl.styles?.color || '#f59e0b'" @input="updateStyle('color', $event.target.value)" class="color-input" />
                </div>
              </div>
            </div>

          </div>

          <!-- ═══ EFFECTS TAB ═══════════════════════════════ -->
          <div v-show="activeTab === 'effects'" class="tab-content">
            
            <!-- Filters -->
            <div class="prop-section">
              <div class="section-title">Filters</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Blur</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="20" :value="selectedEl.styles?.blur || 0" @input="updateStyle('blur', +$event.target.value)" />
                    <span class="val">{{ selectedEl.styles?.blur || 0 }}px</span>
                  </div>
                </div>
                <div class="prop-row">
                  <label>Brightness</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="200" :value="selectedEl.styles?.brightness || 100" @input="updateStyle('brightness', +$event.target.value)" />
                    <span class="val">{{ selectedEl.styles?.brightness || 100 }}%</span>
                  </div>
                </div>
                <div class="prop-row">
                  <label>Contrast</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="200" :value="selectedEl.styles?.contrast || 100" @input="updateStyle('contrast', +$event.target.value)" />
                    <span class="val">{{ selectedEl.styles?.contrast || 100 }}%</span>
                  </div>
                </div>
                <div class="prop-row">
                  <label>Grayscale</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="100" :value="selectedEl.styles?.grayscale || 0" @input="updateStyle('grayscale', +$event.target.value)" />
                    <span class="val">{{ selectedEl.styles?.grayscale || 0 }}%</span>
                  </div>
                </div>
                <div class="prop-row">
                  <label>Saturate</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="200" :value="selectedEl.styles?.saturate || 100" @input="updateStyle('saturate', +$event.target.value)" />
                    <span class="val">{{ selectedEl.styles?.saturate || 100 }}%</span>
                  </div>
                </div>
                <div class="prop-row">
                  <label>Sepia</label>
                  <div class="slider-row">
                    <input type="range" min="0" max="100" :value="selectedEl.styles?.sepia || 0" @input="updateStyle('sepia', +$event.target.value)" />
                    <span class="val">{{ selectedEl.styles?.sepia || 0 }}%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Blend Mode -->
            <div class="prop-section">
              <div class="section-title">Blend Mode</div>
              <div class="section-body">
                <select
                  :value="selectedEl.styles?.mixBlendMode || 'normal'"
                  @change="updateStyle('mixBlendMode', $event.target.value)"
                  class="prop-select"
                >
                  <option value="normal">Normal</option>
                  <option value="multiply">Multiply</option>
                  <option value="screen">Screen</option>
                  <option value="overlay">Overlay</option>
                  <option value="darken">Darken</option>
                  <option value="lighten">Lighten</option>
                  <option value="color-dodge">Color Dodge</option>
                  <option value="color-burn">Color Burn</option>
                  <option value="difference">Difference</option>
                  <option value="exclusion">Exclusion</option>
                </select>
              </div>
            </div>

            <!-- Hover Animation -->
            <div class="prop-section">
              <div class="section-title">Hover Animation</div>
              <div class="section-body">
                <div class="hover-animation-grid">
                  <button
                    v-for="anim in ['none', 'lift', 'pulse', 'shake', 'bounce', 'glow']"
                    :key="anim"
                    class="hover-anim-btn"
                    :class="{ active: (selectedEl.styles?.hoverEffect || 'none') === anim }"
                    @click="updateStyle('hoverEffect', anim === 'none' ? undefined : anim)"
                  >
                    <span class="anim-preview" :class="anim"></span>
                    <span>{{ anim }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Transform -->
            <div class="prop-section">
              <div class="section-title">Transform</div>
              <div class="section-body">
                <div class="prop-row">
                  <label>Flip H</label>
                  <button
                    class="toggle-btn"
                    :class="{ active: selectedEl.styles?.scaleX === -1 }"
                    @click="updateStyle('scaleX', selectedEl.styles?.scaleX === -1 ? 1 : -1)"
                  >
                    <i class="fa-solid fa-arrows-left-right"></i> Flip
                  </button>
                </div>
                <div class="prop-row">
                  <label>Flip V</label>
                  <button
                    class="toggle-btn"
                    :class="{ active: selectedEl.styles?.scaleY === -1 }"
                    @click="updateStyle('scaleY', selectedEl.styles?.scaleY === -1 ? 1 : -1)"
                  >
                    <i class="fa-solid fa-arrows-up-down"></i> Flip
                  </button>
                </div>
              </div>
            </div>

          </div>

          <!-- ═══ ARRANGE TAB ═══════════════════════════════ -->
          <div v-show="activeTab === 'arrange'" class="tab-content">
            
            <!-- Layer Order -->
            <div class="prop-section">
              <div class="section-title">Layer Order</div>
              <div class="section-body">
                <div class="arrange-grid">
                  <button class="arrange-btn" @click="$emit('bring-front')">
                    <i class="fa-solid fa-angles-up"></i>
                    <span>To Front</span>
                  </button>
                  <button class="arrange-btn" @click="$emit('send-back')">
                    <i class="fa-solid fa-angles-down"></i>
                    <span>To Back</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Align to Page -->
            <div class="prop-section">
              <div class="section-title">Align to Page</div>
              <div class="section-body">
                <div class="align-grid">
                  <button @click="$emit('align-to-page', 'left')" title="Align Left">
                    <i class="fa-solid fa-align-left"></i>
                  </button>
                  <button @click="$emit('align-to-page', 'center-h')" title="Center Horizontally">
                    <i class="fa-solid fa-align-center"></i>
                  </button>
                  <button @click="$emit('align-to-page', 'right')" title="Align Right">
                    <i class="fa-solid fa-align-right"></i>
                  </button>
                  <button @click="$emit('align-to-page', 'top')" title="Align Top">
                    <i class="fa-solid fa-arrow-up"></i>
                  </button>
                  <button @click="$emit('align-to-page', 'center-v')" title="Center Vertically">
                    <i class="fa-solid fa-arrow-down-up-across-line"></i>
                  </button>
                  <button @click="$emit('align-to-page', 'bottom')" title="Align Bottom">
                    <i class="fa-solid fa-arrow-down"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="prop-section">
              <div class="section-title">Quick Actions</div>
              <div class="section-body">
                <div class="actions-grid">
                  <button class="action-btn" @click="$emit('duplicate-el')">
                    <i class="fa-solid fa-clone"></i> Duplicate
                  </button>
                  <button class="action-btn" @click="$emit('copy-el')">
                    <i class="fa-solid fa-copy"></i> Copy
                  </button>
                  <button class="action-btn" v-if="clipboard" @click="$emit('paste-el')">
                    <i class="fa-solid fa-paste"></i> Paste
                  </button>
                  <button class="action-btn" @click="$emit('style-painter-copy')">
                    <i class="fa-solid fa-paintbrush"></i> Copy Style
                  </button>
                  <button class="action-btn" v-if="stylePainterClipboard" @click="$emit('style-painter-paste')">
                    <i class="fa-solid fa-brush"></i> Paste Style
                  </button>
                  <button class="action-btn" @click="$emit('reset-styles')">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                  </button>
                  <button class="action-btn danger" @click="$emit('delete-el')">
                    <i class="fa-solid fa-trash-can"></i> Delete
                  </button>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
          <button
            :class="{ active: activeSection === 'props' }"
            @click="$emit('update:active-section', 'props')"
          >
            <i class="fa-solid fa-sliders"></i> Properties
          </button>
          <button
            :class="{ active: activeSection === 'settings' }"
            @click="$emit('update:active-section', 'settings')"
          >
            <i class="fa-solid fa-gear"></i> Page
          </button>
        </div>

      </div>

      <!-- ═══ PAGE SETTINGS TAB ═══════════════════════════════ -->
      <div v-if="activeSection === 'settings' && selectedEl" class="page-settings-panel">
        <!-- Same settings as LeftSidebar settings tab -->
        <div class="prop-section">
          <div class="section-title">Page Setup</div>
          <div class="section-body">
            <div class="prop-row">
              <label>Page Size</label>
              <select v-model="localSettings.page_size" @change="emitSettings" class="prop-select">
                <option value="A4">A4</option>
                <option value="Letter">Letter</option>
                <option value="Legal">Legal</option>
                <option value="A3">A3</option>
                <option value="A5">A5</option>
              </select>
            </div>
            <div class="prop-row">
              <label>Orientation</label>
              <select v-model="localSettings.orientation" @change="emitSettings" class="prop-select">
                <option value="portrait">Portrait</option>
                <option value="landscape">Landscape</option>
              </select>
            </div>
            <div class="prop-row">
              <label>Margin</label>
              <div class="slider-row">
                <input type="range" min="0" max="120" v-model.number="localSettings.margin" @input="emitSettings" />
                <span class="val">{{ localSettings.margin || 40 }}px</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>


  </aside>
</template>

<script setup>
import { ref, reactive, computed, watch, inject } from 'vue'

// ═══════════════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════════════
const props = defineProps({
  selectedEl: { type: Object, default: null },
  settings: { type: Object, required: true },
  activeSection: { type: String, default: 'props' },
    currentPageElements: { 
    type: Array, 
    default: () => []
  },
  clipboard: { type: Object, default: null },
  stylePainterClipboard: { type: Object, default: null },
})

// ═══════════════════════════════════════════════════════════════════
// EMITS
// ═══════════════════════════════════════════════════════════════════
const emit = defineEmits([
  'update:style', 'update:content', 'delete-el', 'duplicate-el',
  'copy-el', 'paste-el', 'lock-el', 'bring-front', 'send-back',
  'align-to-page', 'update:settings', 'update:active-section',
  'add-table-row', 'add-table-col', 'remove-table-row', 'remove-table-col',
  'set-chart-labels', 'set-chart-values',
  'add-timeline-item', 'remove-timeline-item',
  'add-checklist-item', 'remove-checklist-item',
  'add-stat-item', 'remove-stat-item',
  'reset-styles', 'style-painter-copy', 'style-painter-paste',
  'image-replace', 'mark-dirty',
])

// ═══════════════════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════════════════
const isCollapsed = ref(false)
const activeTab = ref('style')
const collapsedSections = ref([])
const localSettings = reactive({ ...props.settings })
const recentColors = ref(['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'])

// ═══════════════════════════════════════════════════════════════════
// CONSTANTS
// ═══════════════════════════════════════════════════════════════════
const propTabs = [
  { id: 'style', label: 'Style', icon: 'fa-solid fa-paint-brush' },
  { id: 'typography', label: 'Text', icon: 'fa-solid fa-font' },
  { id: 'content', label: 'Content', icon: 'fa-solid fa-align-left' },
  { id: 'effects', label: 'Effects', icon: 'fa-solid fa-wand-magic-sparkles' },
  { id: 'arrange', label: 'Arrange', icon: 'fa-solid fa-layer-group' },
]

const fontList = ['Inter', 'DM Sans', 'Plus Jakarta Sans', 'Space Grotesk', 'Sora', 'Outfit', 'Nunito', 'Georgia', 'Playfair Display', 'Times New Roman', 'Courier New', 'Fira Code']

const shadowPresets = [
  { name: 'None', value: 'none' },
  { name: 'Soft', value: '0 2px 8px rgba(0,0,0,0.08)' },
  { name: 'Medium', value: '0 4px 20px rgba(0,0,0,0.15)' },
  { name: 'Heavy', value: '0 8px 40px rgba(0,0,0,0.25)' },
  { name: 'Glow', value: '0 0 20px rgba(99,102,241,0.4)' },
  { name: 'Inner', value: 'inset 0 2px 8px rgba(0,0,0,0.1)' },
]

// ═══════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════
function isTextElement(type) {
  return ['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight', 'badge', 'code', 'link', 'callout'].includes(type)
}

function isChartType(type) {
  return type?.endsWith('-chart')
}

function getElIcon(type) {
  const icons = {
    text: 'fa-solid fa-align-left', heading: 'fa-solid fa-heading', image: 'fa-solid fa-image',
    table: 'fa-solid fa-table', metric: 'fa-solid fa-chart-simple', progress: 'fa-solid fa-bars-progress',
    rectangle: 'fa-solid fa-square', circle: 'fa-solid fa-circle', divider: 'fa-solid fa-minus',
    timeline: 'fa-solid fa-timeline', testimonial: 'fa-solid fa-comment-dots', signature: 'fa-solid fa-signature',
    callout: 'fa-solid fa-lightbulb', checklist: 'fa-solid fa-list-check', rating: 'fa-solid fa-star-half-stroke',
    icon: 'fa-solid fa-star', 'qr-code': 'fa-solid fa-qrcode',
  }
  for (const [key, icon] of Object.entries(icons)) {
    if (type?.includes(key)) return icon
  }
  return 'fa-solid fa-cube'
}

function getPriorityColor(p) {
  return { low: '#3b82f6', medium: '#f59e0b', high: '#f97316', urgent: '#ef4444' }[p] || '#94a3b8'
}

// ═══════════════════════════════════════════════════════════════════
// METHODS
// ═══════════════════════════════════════════════════════════════════
function updateStyle(prop, value) {
  emit('update:style', prop, value)
}

function updatePosition(axis, value) {
  if (!props.selectedEl?.position) return
  props.selectedEl.position[axis] = value
  emit('mark-dirty')
}

function updateElProp(prop, value) {
  if (!props.selectedEl) return
  props.selectedEl[prop] = value
  emit('mark-dirty')
}

function markDirty() {
  emit('mark-dirty')
}

function toggleSection(name) {
  const idx = collapsedSections.value.indexOf(name)
  if (idx >= 0) collapsedSections.value.splice(idx, 1)
  else collapsedSections.value.push(name)
}

function addCurrentColor() {
  const color = props.selectedEl?.styles?.backgroundColor
  if (color && color !== 'transparent' && !recentColors.value.includes(color)) {
    recentColors.value.unshift(color)
    if (recentColors.value.length > 12) recentColors.value.pop()
  }
}

function emitSettings() {
  emit('update:settings', { ...localSettings })
}

// Sync settings
watch(() => props.settings, (newVal) => {
  Object.assign(localSettings, newVal)
}, { deep: true })
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════════════════
   RIGHT PANEL STYLES
   ══════════════════════════════════════════════════════════════════ */

.right-panel {
  width: 290px;
  flex-shrink: 0;
  background: var(--bg-panel, #ffffff);
  border-left: 1px solid var(--border, #e2e8f0);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: width 0.25s ease;
  position: relative;
}

.right-panel.collapsed {
  width: 0;
  border-left: none;
}

/* ── Toggle ──────────────────────────────────────────────────── */
.panel-toggle {
  position: absolute;
  left: -14px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--bg-panel, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  color: var(--text-muted, #94a3b8);
  font-size: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  transition: all 0.15s;
}

.panel-toggle:hover {
  color: var(--accent, #6366f1);
  border-color: var(--accent, #6366f1);
}

/* ── Panel Content ───────────────────────────────────────────── */
.panel-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ── No Selection ────────────────────────────────────────────── */
.no-selection {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 30px;
  text-align: center;
  color: var(--text-muted, #94a3b8);
}

.no-sel-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--bg-secondary, #f8fafc);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  opacity: 0.5;
  margin-bottom: 8px;
}

.no-selection h3 {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-primary, #0f172a);
}

.no-selection p {
  font-size: 11px;
  line-height: 1.5;
}

.quick-stats {
  display: flex;
  gap: 6px;
  margin-top: 12px;
}

.stat-chip {
  padding: 4px 10px;
  border-radius: 99px;
  background: var(--bg-secondary, #f8fafc);
  border: 1px solid var(--border, #e2e8f0);
  font-size: 10px;
  font-weight: 600;
  color: var(--text-secondary, #475569);
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: default;
}

/* ── Props Container ─────────────────────────────────────────── */
.props-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ── Props Header ────────────────────────────────────────────── */
.props-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border-bottom: 1px solid var(--border, #e2e8f0);
  flex-shrink: 0;
}

.el-type-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.type-icon {
  font-size: 14px;
  color: var(--accent, #6366f1);
}

.type-name {
  font-size: 12px;
  font-weight: 700;
  text-transform: capitalize;
  color: var(--text-primary, #0f172a);
}

.type-id {
  display: block;
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
  font-family: monospace;
}

.props-actions {
  display: flex;
  gap: 3px;
}

.props-actions button {
  width: 26px;
  height: 26px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: transparent;
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.12s;
}

.props-actions button:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

.props-actions button.active {
  background: rgba(245,158,11,0.1);
  color: #f59e0b;
  border-color: rgba(245,158,11,0.3);
}

.props-actions button.danger:hover {
  background: rgba(239,68,68,0.08);
  color: #ef4444;
  border-color: rgba(239,68,68,0.3);
}

/* ── Props Tabs ──────────────────────────────────────────────── */
.props-tabs {
  display: flex;
  border-bottom: 1px solid var(--border, #e2e8f0);
  flex-shrink: 0;
}

.props-tab {
  flex: 1;
  padding: 7px 3px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 9px;
  font-weight: 600;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  transition: all 0.15s;
  border-bottom: 2px solid transparent;
}

.props-tab:hover {
  color: var(--text-secondary, #475569);
  background: var(--bg-secondary, #f8fafc);
}

.props-tab.active {
  color: var(--accent, #6366f1);
  border-bottom-color: var(--accent, #6366f1);
}

.props-tab i { font-size: 11px; }

/* ── Props Body ──────────────────────────────────────────────── */
.props-body {
  flex: 1;
  overflow-y: auto;
  padding: 4px;
}

.tab-content {
  padding: 4px;
}

/* ── Sections ────────────────────────────────────────────────── */
.prop-section {
  margin-bottom: 4px;
  border: 1px solid var(--border-light, #f1f5f9);
  border-radius: 8px;
  overflow: hidden;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 10px;
  font-size: 10px;
  font-weight: 700;
  color: var(--text-secondary, #475569);
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  user-select: none;
  transition: background 0.1s;
}

.section-title:hover {
  background: var(--bg-tertiary, #f1f5f9);
}

.section-title i:first-child { font-size: 11px; color: var(--accent, #6366f1); }
.collapse-icon { margin-left: auto; font-size: 8px; opacity: 0.5; }

.section-body {
  padding: 8px 10px;
}

/* ── Prop Rows ───────────────────────────────────────────────── */
.prop-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.prop-row label {
  font-size: 10px;
  font-weight: 500;
  color: var(--text-secondary, #475569);
  width: 60px;
  flex-shrink: 0;
}

.prop-row-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
  font-size: 10px;
  color: var(--text-secondary, #475569);
}

.prop-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 6px; }
.prop-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-bottom: 6px; }

.prop-field label {
  display: block;
  font-size: 9px;
  font-weight: 600;
  color: var(--text-muted, #94a3b8);
  margin-bottom: 2px;
  text-align: center;
}

/* ── Inputs ──────────────────────────────────────────────────── */
.prop-input {
  width: 100%;
  padding: 4px 6px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  font-size: 10px;
  outline: none;
  transition: border-color 0.15s;
  box-sizing: border-box;
  font-family: inherit;
}

.prop-input:focus { border-color: var(--accent, #6366f1); }
.prop-input.sm { width: 60px; flex: none; }
.prop-input.mono { font-family: 'Courier New', monospace; font-size: 10px; }

.prop-select {
  width: 100%;
  padding: 4px 6px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  font-size: 10px;
  outline: none;
  cursor: pointer;
  font-family: inherit;
}

.prop-select.sm { width: auto; flex: none; }

.slider-row {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.slider-row input[type="range"] {
  flex: 1;
  accent-color: var(--accent, #6366f1);
  height: 4px;
}

.val {
  font-size: 10px;
  color: var(--text-muted, #94a3b8);
  min-width: 36px;
  text-align: right;
  font-weight: 600;
}

.color-row {
  display: flex;
  align-items: center;
  gap: 4px;
  flex: 1;
}

.color-input {
  width: 26px;
  height: 26px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  cursor: pointer;
  padding: 1px;
  background: transparent;
}

/* ── Buttons ─────────────────────────────────────────────────── */
.mini-btn {
  padding: 2px 8px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 4px;
  background: transparent;
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 10px;
  transition: all 0.12s;
  font-family: inherit;
}

.mini-btn:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

.mini-btn.danger { color: #ef4444; }
.mini-btn.danger:hover { background: rgba(239,68,68,0.08); }

.toggle-btn {
  padding: 4px 10px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 10px;
  font-weight: 600;
  transition: all 0.12s;
  font-family: inherit;
}

.toggle-btn:hover { background: var(--bg-tertiary, #f1f5f9); }
.toggle-btn.active {
  background: var(--accent-light, rgba(99,102,241,0.1));
  color: var(--accent, #6366f1);
  border-color: var(--accent, #6366f1);
}

.prop-btn {
  width: 100%;
  padding: 6px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 10px;
  font-weight: 500;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  font-family: inherit;
}

.prop-btn:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
}

/* ── Color Presets ───────────────────────────────────────────── */
.color-presets {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
  margin-top: 4px;
}

.color-preset-dot {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid var(--border, #e2e8f0);
  cursor: pointer;
  transition: all 0.15s;
  flex-shrink: 0;
}

.color-preset-dot:hover {
  transform: scale(1.2);
  border-color: var(--text-primary, #0f172a);
}

.color-preset-dot.add-color {
  background: var(--bg-secondary, #f8fafc) !important;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 7px;
  color: var(--text-muted, #94a3b8);
}

/* ── Shadow Presets ──────────────────────────────────────────── */
.shadow-presets {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
  margin-bottom: 6px;
}

.shadow-preset {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  padding: 6px 3px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}

.shadow-preset:hover { border-color: var(--accent, #6366f1); }
.shadow-preset.active {
  border-color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
}

.shadow-preview {
  width: 30px;
  height: 20px;
  border-radius: 3px;
  background: #fff;
}

.shadow-preset span {
  font-size: 9px;
  font-weight: 500;
  color: var(--text-secondary, #475569);
}

/* ── Priority Options ────────────────────────────────────────── */
.priority-options {
  display: flex;
  gap: 3px;
}

.priority-btn {
  flex: 1;
  padding: 5px 4px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 5px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  font-size: 8px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted, #94a3b8);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
  transition: all 0.15s;
  font-family: inherit;
}

.priority-btn:hover { background: var(--bg-tertiary, #f1f5f9); }
.priority-btn.active {
  color: var(--text-primary, #0f172a);
  box-shadow: 0 0 0 2px currentColor;
}

.priority-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

/* ── Hover Animation Grid ────────────────────────────────────── */
.hover-animation-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

.hover-anim-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  padding: 6px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}

.hover-anim-btn:hover { border-color: var(--accent, #6366f1); }
.hover-anim-btn.active {
  border-color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
}

.hover-anim-btn span:last-child {
  font-size: 8px;
  font-weight: 600;
  color: var(--text-muted, #94a3b8);
  text-transform: capitalize;
}

/* ── Arrange/Align Grids ─────────────────────────────────────── */
.arrange-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
.arrange-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  padding: 8px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
  color: var(--text-secondary, #475569);
  font-size: 10px;
}

.arrange-btn:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.04));
}

.align-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

.align-grid button {
  padding: 8px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 13px;
  transition: all 0.15s;
  font-family: inherit;
}

.align-grid button:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.04));
}

/* ── Actions Grid ────────────────────────────────────────────── */
.actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 7px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  color: var(--text-secondary, #475569);
  font-size: 9px;
  font-weight: 600;
  transition: all 0.12s;
  font-family: inherit;
}

.action-btn:hover {
  background: var(--bg-tertiary, #f1f5f9);
  color: var(--text-primary, #0f172a);
}

.action-btn.danger:hover {
  background: rgba(239,68,68,0.08);
  color: #ef4444;
  border-color: rgba(239,68,68,0.3);
}

/* ── Content Textarea ────────────────────────────────────────── */
.content-textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  font-size: 11px;
  outline: none;
  resize: vertical;
  line-height: 1.5;
  font-family: inherit;
  box-sizing: border-box;
}

.content-textarea:focus { border-color: var(--accent, #6366f1); }

/* ── List Edit Row ───────────────────────────────────────────── */
.list-edit-row {
  display: flex;
  gap: 4px;
  align-items: center;
  margin-bottom: 3px;
}

.timeline-edit-item {
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  padding: 6px;
  margin-bottom: 4px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.timeline-edit-item input {
  width: 100%;
  padding: 3px 6px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 4px;
  background: var(--bg-secondary, #f8fafc);
  font-size: 10px;
  outline: none;
  box-sizing: border-box;
  font-family: inherit;
}

.timeline-edit-item button {
  align-self: flex-end;
}

/* ── Bottom Nav ──────────────────────────────────────────────── */
.bottom-nav {
  display: flex;
  border-top: 1px solid var(--border, #e2e8f0);
  flex-shrink: 0;
}

.bottom-nav button {
  flex: 1;
  padding: 8px 4px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 9px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all 0.15s;
  font-family: inherit;
}

.bottom-nav button:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

.bottom-nav button.active {
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
}

/* ── No Props ────────────────────────────────────────────────── */
.no-props {
  padding: 30px 20px;
  text-align: center;
  color: var(--text-muted, #94a3b8);
  font-size: 11px;
}

.no-props i { font-size: 24px; opacity: 0.3; margin-bottom: 8px; display: block; }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 768px) {
  .right-panel {
    position: fixed;
    right: 0;
    top: 48px;
    bottom: 0;
    z-index: 150;
    box-shadow: -8px 0 40px rgba(0,0,0,0.15);
  }
  
  .right-panel.collapsed {
    width: 0;
    box-shadow: none;
  }
}
</style>