<!--
  RightSidebar.vue — Element Properties Panel (Part 4)
  ═══════════════════════════════════════════════════════════════════
  5 tabs, fully wired to every element type:

  1. Style    — background (solid/gradient), border (width/style/color/radius),
                box-shadow presets + custom, opacity
  2. Typography — font family/size/weight/style/decoration/align/color/
                  line-height/letter-spacing/text-transform/columns/
                  text-gradient (only shown for text-type elements)
  3. Content  — element-specific editors:
                • Metric: label, value, change, period, changeType
                • Progress / Circular: label, value, track colour
                • Chart: title, labels[], values[], colour, type toggle
                • Image: URL, alt, object-fit, image filter
                • Table: header colour, even/odd row colours, cell padding
                • Checklist: add/remove/check items
                • Timeline: add/remove items (date, label, desc)
                • Steps: add/remove step labels
                • Callout: emoji + body text
                • Testimonial: quote, author, role
                • Signature: name, title
                • Rating: value (1-5)
                • Stat-Row: add/remove stat cells
                • QR Code: text to encode, size
                • Video: YouTube URL
                • Map: address
                • Price Card: plan, price, period, features
                • TOC: items editor
                • All text elements: inline content textarea
  4. Effects  — CSS filters (blur/brightness/contrast/grayscale/sepia/
                saturate/hue-rotate/invert), mix-blend-mode, scaleX/Y flip
  5. Arrange  — X, Y, W, H (number inputs), rotation, z-index,
                lock, flip H/V, duplicate, delete,
                align to page (left/center/right/top/middle/bottom),
                distribute (horizontal/vertical)
  ═══════════════════════════════════════════════════════════════════
-->
<template>
  <aside class="rs-root" :class="{ 'rs-dark': isDark }" aria-label="Element properties">

    <!-- No element selected -->
    <div v-if="!el" class="rs-empty">
      <i class="fa-solid fa-arrow-pointer" />
      <span>Select an element to edit its properties</span>
    </div>

    <template v-else>
      <!-- Element header -->
      <div class="rs-el-header">
        <div class="rs-el-type-icon">
          <i :class="elIcon" />
        </div>
        <div class="rs-el-info">
          <span class="rs-el-type">{{ elTypeLabel }}</span>
          <span class="rs-el-id">{{ el.id?.slice(-6) }}</span>
        </div>
        <button class="rs-icon-btn rs-danger-btn" @click="$emit('delete-el')" title="Delete element [Del]"
          aria-label="Delete element">
          <i class="fa-solid fa-trash-can" />
        </button>
        <button class="rs-icon-btn" @click="$emit('duplicate-el')" title="Duplicate [Ctrl+Alt+Q]"
          aria-label="Duplicate element">
          <i class="fa-solid fa-clone" />
        </button>
        <button class="rs-icon-btn" :class="{ active: el.locked }" @click="$emit('lock-el')"
          :title="el.locked ? 'Unlock' : 'Lock'" :aria-pressed="el.locked">
          <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
        </button>
      </div>

      <!-- Tabs -->
      <nav class="rs-tabs" role="tablist">
        <button v-for="t in TABS" :key="t.id" class="rs-tab" :class="{ active: activeTab === t.id }"
          @click="activeTab = t.id" role="tab" :aria-selected="activeTab === t.id" :title="t.label">
          <i :class="t.icon" />
          <span>{{ t.label }}</span>
        </button>
      </nav>

      <!-- Panel body -->
      <div class="rs-body">

        <!-- ══ 1. STYLE ════════════════════════════════════════════════ -->
        <div v-show="activeTab === 'style'" class="rs-panel">

          <!-- Background -->
          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-fill" /> Background</div>

            <div class="rs-row">
              <label class="rs-label">Type</label>
              <div class="rs-btn-group">
                <button :class="{ active: !s.useGradient }" @click="setProp('styles.useGradient', false)">Solid</button>
                <button :class="{ active: s.useGradient }"
                  @click="setProp('styles.useGradient', true)">Gradient</button>
              </div>
            </div>

            <template v-if="!s.useGradient">
              <div class="rs-row">
                <label class="rs-label">Color</label>
                <div class="rs-color-row">
                  <input type="color" :value="s.backgroundColor || '#ffffff'"
                    @input="setProp('styles.backgroundColor', $event.target.value)" class="rs-color" />
                  <input type="text" :value="s.backgroundColor || '#ffffff'"
                    @input="setProp('styles.backgroundColor', $event.target.value)" class="rs-hex" maxlength="9"
                    placeholder="#ffffff" />
                  <button class="rs-clear-btn" @click="setProp('styles.backgroundColor', 'transparent')"
                    title="Transparent">
                    <i class="fa-solid fa-droplet-slash" />
                  </button>
                </div>
              </div>
            </template>

            <template v-else>
              <div class="rs-row">
                <label class="rs-label">From</label>
                <input type="color" :value="s.gradientFrom || '#6366f1'"
                  @input="setProp('styles.gradientFrom', $event.target.value)" class="rs-color" />
              </div>
              <div class="rs-row">
                <label class="rs-label">To</label>
                <input type="color" :value="s.gradientTo || '#8b5cf6'"
                  @input="setProp('styles.gradientTo', $event.target.value)" class="rs-color" />
              </div>
              <div class="rs-row">
                <label class="rs-label">Direction</label>
                <select class="rs-select" :value="s.gradientDir || '135deg'"
                  @change="setProp('styles.gradientDir', $event.target.value)">
                  <option value="to right">→ Left to Right</option>
                  <option value="to left">← Right to Left</option>
                  <option value="to bottom">↓ Top to Bottom</option>
                  <option value="to top">↑ Bottom to Top</option>
                  <option value="135deg">↘ Diagonal</option>
                  <option value="45deg">↗ Diagonal Rev</option>
                </select>
              </div>
            </template>

            <!-- Opacity -->
            <div class="rs-row">
              <label class="rs-label">Opacity <span class="rs-val">{{ s.opacity ?? 100 }}%</span></label>
              <input type="range" min="0" max="100" step="1" :value="s.opacity ?? 100"
                @input="setProp('styles.opacity', +$event.target.value)" class="rs-range" />
            </div>
          </div>

          <!-- Border -->
          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-border-all" /> Border</div>
            <div class="rs-row">
              <label class="rs-label">Width <span class="rs-val">{{ s.borderWidth || 0 }}px</span></label>
              <input type="range" min="0" max="20" step="1" :value="s.borderWidth || 0"
                @input="setProp('styles.borderWidth', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Style</label>
              <select class="rs-select" :value="s.borderStyle || 'solid'"
                @change="setProp('styles.borderStyle', $event.target.value)">
                <option>solid</option>
                <option>dashed</option>
                <option>dotted</option>
                <option>double</option>
                <option>groove</option>
                <option>ridge</option>
              </select>
            </div>
            <div class="rs-row">
              <label class="rs-label">Color</label>
              <div class="rs-color-row">
                <input type="color" :value="s.borderColor || '#e2e8f0'"
                  @input="setProp('styles.borderColor', $event.target.value)" class="rs-color" />
                <input type="text" :value="s.borderColor || '#e2e8f0'"
                  @input="setProp('styles.borderColor', $event.target.value)" class="rs-hex" maxlength="9" />
              </div>
            </div>
            <div class="rs-row">
              <label class="rs-label">Radius <span class="rs-val">{{ s.borderRadius || 0 }}px</span></label>
              <input type="range" min="0" max="200" step="2" :value="s.borderRadius || 0"
                @input="setProp('styles.borderRadius', +$event.target.value)" class="rs-range" />
            </div>
          </div>

          <!-- Shadow -->
          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-droplet" /> Shadow</div>
            <div class="rs-shadow-presets">
              <button v-for="sh in SHADOW_PRESETS" :key="sh.label" class="rs-shadow-btn"
                :class="{ active: s.boxShadow === sh.value }"
                @click="setProp('styles.boxShadow', s.boxShadow === sh.value ? 'none' : sh.value)" :title="sh.label">
                <div class="rs-shadow-demo" :style="{ boxShadow: sh.value }" />
              </button>
            </div>
            <div class="rs-row">
              <label class="rs-label">Custom</label>
              <input type="text" class="rs-input" :value="s.boxShadow || ''"
                @input="setProp('styles.boxShadow', $event.target.value)"
                placeholder="e.g. 0 4px 12px rgba(0,0,0,.15)" />
            </div>
          </div>

        </div>

        <!-- ══ 2. TYPOGRAPHY ══════════════════════════════════════════ -->
        <div v-show="activeTab === 'typography'" class="rs-panel">

          <div v-if="!isTextEl" class="rs-empty-tab">
            <i class="fa-solid fa-font" />
            <span>No typography options for {{ elTypeLabel }} elements</span>
          </div>

          <template v-else>
            <!-- Font family -->
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-font" /> Font</div>
              <div class="rs-row">
                <label class="rs-label">Family</label>
                <select class="rs-select" :value="s.fontFamily || settings.font_family || 'DM Sans'"
                  @change="setProp('styles.fontFamily', $event.target.value)">
                  <option v-for="f in FONTS" :key="f" :value="f" :style="{ fontFamily: f }">{{ f }}</option>
                </select>
              </div>
              <div class="rs-row">
                <label class="rs-label">Size <span class="rs-val">{{ s.fontSize || 14 }}px</span></label>
                <div class="rs-slider-number">
                  <input type="range" min="6" max="200" step="1" :value="s.fontSize || 14"
                    @input="setProp('styles.fontSize', +$event.target.value)" class="rs-range" />
                  <input type="number" min="6" max="200" :value="s.fontSize || 14"
                    @input="setProp('styles.fontSize', +$event.target.value)" class="rs-num-input" />
                </div>
              </div>
              <div class="rs-row">
                <label class="rs-label">Weight</label>
                <select class="rs-select" :value="s.fontWeight || '400'"
                  @change="setProp('styles.fontWeight', $event.target.value)">
                  <option value="100">Thin 100</option>
                  <option value="200">ExtraLight 200</option>
                  <option value="300">Light 300</option>
                  <option value="400">Regular 400</option>
                  <option value="500">Medium 500</option>
                  <option value="600">SemiBold 600</option>
                  <option value="700">Bold 700</option>
                  <option value="800">ExtraBold 800</option>
                  <option value="900">Black 900</option>
                </select>
              </div>
            </div>

            <!-- Style toggles -->
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-italic" /> Style</div>
              <div class="rs-toggle-row">
                <button class="rs-fmt-btn" :class="{ active: s.fontStyle === 'italic' }"
                  @click="toggle('styles.fontStyle', 'italic', 'normal')"><em>I</em> Italic</button>
                <button class="rs-fmt-btn" :class="{ active: s.textDecoration === 'underline' }"
                  @click="toggle('styles.textDecoration', 'underline', 'none')"><u>U</u> Underline</button>
                <button class="rs-fmt-btn" :class="{ active: s.textDecoration === 'line-through' }"
                  @click="toggle('styles.textDecoration', 'line-through', 'none')"><s>S</s> Strike</button>
              </div>
              <div class="rs-row">
                <label class="rs-label">Transform</label>
                <select class="rs-select" :value="s.textTransform || 'none'"
                  @change="setProp('styles.textTransform', $event.target.value)">
                  <option value="none">None</option>
                  <option value="uppercase">UPPERCASE</option>
                  <option value="lowercase">lowercase</option>
                  <option value="capitalize">Capitalize</option>
                </select>
              </div>
            </div>

            <!-- Colour -->
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-palette" /> Colour</div>
              <div class="rs-row">
                <label class="rs-label">Text Color</label>
                <div class="rs-color-row">
                  <input type="color" :value="s.color || '#0f172a'"
                    @input="setProp('styles.color', $event.target.value)" class="rs-color" />
                  <input type="text" :value="s.color || '#0f172a'" @input="setProp('styles.color', $event.target.value)"
                    class="rs-hex" maxlength="9" />
                </div>
              </div>
              <div class="rs-row">
                <label class="rs-label">Gradient Text</label>
                <label class="rs-toggle-sw">
                  <input type="checkbox" :checked="s.textGradient"
                    @change="setProp('styles.textGradient', $event.target.checked)" />
                  <span class="rs-sw-track" /><span class="rs-sw-label">{{ s.textGradient ? 'On' : 'Off' }}</span>
                </label>
              </div>
              <template v-if="s.textGradient">
                <div class="rs-row">
                  <label class="rs-label">From</label>
                  <input type="color" :value="s.textGradientFrom || '#6366f1'"
                    @input="setProp('styles.textGradientFrom', $event.target.value)" class="rs-color" />
                </div>
                <div class="rs-row">
                  <label class="rs-label">To</label>
                  <input type="color" :value="s.textGradientTo || '#8b5cf6'"
                    @input="setProp('styles.textGradientTo', $event.target.value)" class="rs-color" />
                </div>
              </template>
            </div>

            <!-- Spacing -->
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-text-height" /> Spacing</div>
              <div class="rs-row">
                <label class="rs-label">Alignment</label>
                <div class="rs-align-btns">
                  <button v-for="a in ['left', 'center', 'right', 'justify']" :key="a"
                    :class="{ active: s.textAlign === a }" @click="setProp('styles.textAlign', a)"
                    :title="`Align ${a}`">
                    <i :class="`fa-solid fa-align-${a}`" />
                  </button>
                </div>
              </div>
              <div class="rs-row">
                <label class="rs-label">Line Height <span class="rs-val">{{ s.lineHeight || 1.5 }}</span></label>
                <input type="range" min="0.8" max="4" step="0.05" :value="s.lineHeight || 1.5"
                  @input="setProp('styles.lineHeight', +$event.target.value)" class="rs-range" />
              </div>
              <div class="rs-row">
                <label class="rs-label">Letter Spacing <span class="rs-val">{{ s.letterSpacing || 0 }}px</span></label>
                <input type="range" min="-5" max="20" step="0.5" :value="s.letterSpacing || 0"
                  @input="setProp('styles.letterSpacing', +$event.target.value)" class="rs-range" />
              </div>
              <div class="rs-row">
                <label class="rs-label">Columns</label>
                <select class="rs-select" :value="s.columns || 1"
                  @change="setProp('styles.columns', +$event.target.value)">
                  <option :value="1">1 column</option>
                  <option :value="2">2 columns</option>
                  <option :value="3">3 columns</option>
                  <option :value="4">4 columns</option>
                </select>
              </div>
              <div class="rs-row">
                <label class="rs-label">Padding <span class="rs-val">{{ s.padding || 0 }}px</span></label>
                <input type="range" min="0" max="60" step="2" :value="s.padding || 0"
                  @input="setProp('styles.padding', +$event.target.value)" class="rs-range" />
              </div>
            </div>
          </template>
        </div>

        <!-- ══ 3. CONTENT ═════════════════════════════════════════════ -->
        <div v-show="activeTab === 'content'" class="rs-panel">

          <!-- Generic text content -->
          <div v-if="isTextEl" class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-pen-to-square" /> Text Content</div>
            <textarea class="rs-textarea" :value="el.content || ''" @input="setElProp('content', $event.target.value)"
              rows="5" placeholder="Enter text content…" />
          </div>

          <!-- Metric / KPI -->
          <template v-if="el.type === 'metric'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-arrow-trend-up" /> KPI Metric</div>
              <div class="rs-row"><label class="rs-label">Label</label><input class="rs-input" :value="el.label"
                  @input="setElProp('label', $event.target.value)" /></div>
              <div class="rs-row"><label class="rs-label">Value</label><input class="rs-input" :value="el.value"
                  @input="setElProp('value', $event.target.value)" placeholder="$1.2M" /></div>
              <div class="rs-row"><label class="rs-label">Change</label><input class="rs-input" :value="el.change"
                  @input="setElProp('change', $event.target.value)" placeholder="+12%" /></div>
              <div class="rs-row"><label class="rs-label">Period</label><input class="rs-input" :value="el.changePeriod"
                  @input="setElProp('changePeriod', $event.target.value)" placeholder="vs last month" /></div>
              <div class="rs-row">
                <label class="rs-label">Trend</label>
                <div class="rs-btn-group">
                  <button :class="{ active: el.changeType === 'positive' }"
                    @click="setElProp('changeType', 'positive')"><i class="fa-solid fa-arrow-up" /> Up</button>
                  <button :class="{ active: el.changeType === 'negative' }"
                    @click="setElProp('changeType', 'negative')"><i class="fa-solid fa-arrow-down" /> Down</button>
                </div>
              </div>
              <div class="rs-row"><label class="rs-label">Accent Color</label><input type="color"
                  :value="s.color || '#6366f1'" @input="setProp('styles.color', $event.target.value)"
                  class="rs-color" /></div>
            </div>
          </template>

          <!-- Progress bar / Circular progress -->
          <template v-if="el.type === 'progress' || el.type === 'circular-progress'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-bars-progress" /> Progress</div>
              <div class="rs-row"><label class="rs-label">Label</label><input class="rs-input" :value="el.label"
                  @input="setElProp('label', $event.target.value)" /></div>
              <div class="rs-row">
                <label class="rs-label">Value <span class="rs-val">{{ el.value || 0 }}%</span></label>
                <input type="range" min="0" max="100" step="1" :value="el.value || 0"
                  @input="setElProp('value', +$event.target.value)" class="rs-range" />
              </div>
              <div class="rs-row"><label class="rs-label">Fill Color</label><input type="color"
                  :value="s.color || '#6366f1'" @input="setProp('styles.color', $event.target.value)"
                  class="rs-color" /></div>
              <div class="rs-row"><label class="rs-label">Track Color</label><input type="color"
                  :value="s.trackColor || '#e2e8f0'" @input="setProp('styles.trackColor', $event.target.value)"
                  class="rs-color" /></div>
            </div>
          </template>

          <!-- Charts -->
          <template v-if="isChartEl">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-chart-bar" /> Chart Data</div>
              <div class="rs-row"><label class="rs-label">Title</label><input class="rs-input"
                  :value="el.chartTitle || ''" @input="setElProp('chartTitle', $event.target.value)" /></div>
              <div class="rs-row"><label class="rs-label">Color</label><input type="color"
                  :value="el.chartColor || '#6366f1'" @input="setElProp('chartColor', $event.target.value)"
                  class="rs-color" /></div>
              <div class="rs-row">
                <label class="rs-label">Labels</label>
                <input class="rs-input" :value="chartLabels" @input="setChartLabels($event.target.value)"
                  placeholder="Q1,Q2,Q3,Q4" />
              </div>
              <div class="rs-row">
                <label class="rs-label">Values</label>
                <input class="rs-input" :value="chartValues" @input="setChartValues($event.target.value)"
                  placeholder="25,40,35,55" />
              </div>
            </div>
          </template>

          <!-- Image -->
          <template v-if="el.type === 'image'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-image" /> Image</div>
              <div class="rs-row"><label class="rs-label">URL</label><input class="rs-input" :value="el.src"
                  @input="setElProp('src', $event.target.value)" placeholder="https://…" /></div>
              <div class="rs-row"><label class="rs-label">Alt Text</label><input class="rs-input" :value="el.alt"
                  @input="setElProp('alt', $event.target.value)" /></div>
              <div class="rs-row">
                <label class="rs-label">Object Fit</label>
                <select class="rs-select" :value="s.objectFit || 'cover'"
                  @change="setProp('styles.objectFit', $event.target.value)">
                  <option>cover</option>
                  <option>contain</option>
                  <option>fill</option>
                  <option>scale-down</option>
                  <option>none</option>
                </select>
              </div>
              <div class="rs-row">
                <label class="rs-label">Filter</label>
                <select class="rs-select" :value="s.imageFilter || 'none'"
                  @change="setProp('styles.imageFilter', $event.target.value)">
                  <option value="none">None</option>
                  <option value="grayscale">Grayscale</option>
                  <option value="sepia">Sepia</option>
                  <option value="vintage">Vintage</option>
                  <option value="blur">Blur</option>
                  <option value="bright">Brighten</option>
                </select>
              </div>
            </div>
          </template>

          <!-- Table -->
          <template v-if="el.type === 'table'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-table" /> Table Style</div>
              <div class="rs-row"><label class="rs-label">Header BG</label><input type="color"
                  :value="s.headerBg || '#6366f1'" @input="setProp('styles.headerBg', $event.target.value)"
                  class="rs-color" /></div>
              <div class="rs-row"><label class="rs-label">Even Row</label><input type="color"
                  :value="s.evenRowBg || '#ffffff'" @input="setProp('styles.evenRowBg', $event.target.value)"
                  class="rs-color" /></div>
              <div class="rs-row"><label class="rs-label">Odd Row</label><input type="color"
                  :value="s.oddRowBg || '#f8fafc'" @input="setProp('styles.oddRowBg', $event.target.value)"
                  class="rs-color" /></div>
              <div class="rs-row">
                <label class="rs-label">Cell Padding <span class="rs-val">{{ s.cellPadding || 8 }}px</span></label>
                <input type="range" min="2" max="30" step="1" :value="s.cellPadding || 8"
                  @input="setProp('styles.cellPadding', +$event.target.value)" class="rs-range" />
              </div>
              <div class="rs-row rs-row--stacked">
                <div class="rs-table-actions">
                  <button class="rs-action-btn" @click="$emit('add-table-row')"><i class="fa-solid fa-plus" />
                    Row</button>
                  <button class="rs-action-btn" @click="$emit('add-table-col')"><i class="fa-solid fa-plus" />
                    Column</button>
                  <button class="rs-action-btn rs-danger-sm" @click="$emit('remove-table-row')"><i
                      class="fa-solid fa-minus" /> Row</button>
                  <button class="rs-action-btn rs-danger-sm" @click="$emit('remove-table-col')"><i
                      class="fa-solid fa-minus" /> Column</button>
                </div>
              </div>
            </div>
          </template>

          <!-- Checklist -->
          <template v-if="el.type === 'checklist'">
            <div class="rs-section">
              <div class="rs-section-title">
                <i class="fa-solid fa-square-check" /> Checklist
                <button class="rs-section-add" @click="addChecklistItem"><i class="fa-solid fa-plus" /></button>
              </div>
              <div v-for="(item, i) in (el.items || [])" :key="i" class="rs-list-item">
                <input type="checkbox" :checked="item.checked"
                  @change="item.checked = $event.target.checked; emit('mark-dirty')" class="rs-checkbox" />
                <input class="rs-input rs-list-input" :value="item.text"
                  @input="item.text = $event.target.value; emit('mark-dirty')" />
                <button class="rs-list-del" @click="removeItem('items', i)"><i class="fa-solid fa-xmark" /></button>
              </div>
            </div>
          </template>

          <!-- Timeline -->
          <template v-if="el.type === 'timeline'">
            <div class="rs-section">
              <div class="rs-section-title">
                <i class="fa-solid fa-timeline" /> Timeline
                <button class="rs-section-add" @click="addTimelineItem"><i class="fa-solid fa-plus" /></button>
              </div>
              <div v-for="(item, i) in (el.items || [])" :key="i" class="rs-timeline-item-editor">
                <div class="rs-tl-num">{{ i + 1 }}</div>
                <div class="rs-tl-fields">
                  <input class="rs-input rs-mb4" :value="item.date"
                    @input="item.date = $event.target.value; emit('mark-dirty')" placeholder="Date / Period" />
                  <input class="rs-input rs-mb4" :value="item.label"
                    @input="item.label = $event.target.value; emit('mark-dirty')" placeholder="Milestone title" />
                  <input class="rs-input" :value="item.desc"
                    @input="item.desc = $event.target.value; emit('mark-dirty')" placeholder="Description" />
                </div>
                <button class="rs-list-del" @click="removeItem('items', i)"><i class="fa-solid fa-xmark" /></button>
              </div>
            </div>
          </template>

          <!-- Steps -->
          <template v-if="el.type === 'steps'">
            <div class="rs-section">
              <div class="rs-section-title">
                <i class="fa-solid fa-stairs" /> Steps
                <button class="rs-section-add" @click="addStepItem"><i class="fa-solid fa-plus" /></button>
              </div>
              <div v-for="(item, i) in (el.items || [])" :key="i" class="rs-list-item">
                <div class="rs-step-num">{{ i + 1 }}</div>
                <input class="rs-input rs-list-input" :value="item.label"
                  @input="item.label = $event.target.value; emit('mark-dirty')" placeholder="Step label" />
                <button class="rs-list-del" @click="removeItem('items', i)"><i class="fa-solid fa-xmark" /></button>
              </div>
            </div>
          </template>

          <!-- Rating -->
          <template v-if="el.type === 'rating'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-star" /> Rating</div>
              <div class="rs-row">
                <label class="rs-label">Stars <span class="rs-val">{{ el.value || 4 }} / 5</span></label>
                <input type="range" min="1" max="5" step="1" :value="el.value || 4"
                  @input="setElProp('value', +$event.target.value)" class="rs-range" />
              </div>
              <div class="rs-row"><label class="rs-label">Color</label><input type="color" :value="s.color || '#f59e0b'"
                  @input="setProp('styles.color', $event.target.value)" class="rs-color" /></div>
              <div class="rs-row">
                <label class="rs-label">Size <span class="rs-val">{{ s.fontSize || 20 }}px</span></label>
                <input type="range" min="12" max="64" step="2" :value="s.fontSize || 20"
                  @input="setProp('styles.fontSize', +$event.target.value)" class="rs-range" />
              </div>
            </div>
          </template>

          <!-- Callout -->
          <template v-if="el.type === 'callout'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-bullhorn" /> Callout</div>
              <div class="rs-row"><label class="rs-label">Emoji</label><input class="rs-input rs-emoji-input"
                  :value="el.emoji" @input="setElProp('emoji', $event.target.value)" placeholder="💡" /></div>
              <div class="rs-row"><label class="rs-label">Border Color</label><input type="color"
                  :value="s.borderColor || '#6366f1'" @input="setProp('styles.borderColor', $event.target.value)"
                  class="rs-color" /></div>
            </div>
          </template>

          <!-- Testimonial -->
          <template v-if="el.type === 'testimonial'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-comment-quote" /> Testimonial</div>
              <div class="rs-row rs-row--stacked">
                <label class="rs-label-block">Quote</label>
                <textarea class="rs-textarea rs-textarea--sm" :value="el.content"
                  @input="setElProp('content', $event.target.value)" rows="3" />
              </div>
              <div class="rs-row"><label class="rs-label">Author</label><input class="rs-input" :value="el.author"
                  @input="setElProp('author', $event.target.value)" /></div>
              <div class="rs-row"><label class="rs-label">Role</label><input class="rs-input" :value="el.role"
                  @input="setElProp('role', $event.target.value)" /></div>
            </div>
          </template>

          <!-- Signature -->
          <template v-if="el.type === 'signature'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-signature" /> Signature</div>
              <div class="rs-row"><label class="rs-label">Name</label><input class="rs-input" :value="el.content"
                  @input="setElProp('content', $event.target.value)" /></div>
              <div class="rs-row"><label class="rs-label">Title</label><input class="rs-input" :value="el.label"
                  @input="setElProp('label', $event.target.value)" /></div>
            </div>
          </template>

          <!-- QR Code -->
          <template v-if="el.type === 'qr-code'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-qrcode" /> QR Code</div>
              <div class="rs-row"><label class="rs-label">URL / Text</label><input class="rs-input" :value="el.qrText"
                  @input="setElProp('qrText', $event.target.value)" placeholder="https://example.com" /></div>
              <div class="rs-row">
                <label class="rs-label">Size <span class="rs-val">{{ el.qrSize || 160 }}px</span></label>
                <input type="range" min="80" max="400" step="10" :value="el.qrSize || 160"
                  @input="setElProp('qrSize', +$event.target.value)" class="rs-range" />
              </div>
              <div class="rs-row"><button class="rs-action-btn rs-full-btn" @click="regenerateQr"><i
                    class="fa-solid fa-rotate" /> Generate QR Code</button></div>
            </div>
          </template>

          <!-- Video -->
          <template v-if="el.type === 'video'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-video" /> Video</div>
              <div class="rs-row"><label class="rs-label">YouTube URL</label><input class="rs-input"
                  :value="el.videoUrl" @input="setElProp('videoUrl', $event.target.value)"
                  placeholder="https://youtube.com/watch?v=…" /></div>
            </div>
          </template>

          <!-- Map -->
          <template v-if="el.type === 'map'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-map-location-dot" /> Map</div>
              <div class="rs-row"><label class="rs-label">Address</label><input class="rs-input" :value="el.mapAddress"
                  @input="setElProp('mapAddress', $event.target.value)"
                  placeholder="1600 Amphitheatre Pkwy, Mountain View, CA" /></div>
            </div>
          </template>

          <!-- Price card -->
          <template v-if="el.type === 'price-card'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-tag" /> Pricing Card</div>
              <div class="rs-row"><label class="rs-label">Plan Name</label><input class="rs-input" :value="el.plan"
                  @input="setElProp('plan', $event.target.value)" /></div>
              <div class="rs-row"><label class="rs-label">Price</label><input class="rs-input" :value="el.price"
                  @input="setElProp('price', $event.target.value)" placeholder="$99" /></div>
              <div class="rs-row"><label class="rs-label">Period</label><input class="rs-input" :value="el.period"
                  @input="setElProp('period', $event.target.value)" placeholder="/month" /></div>
              <div class="rs-section-title" style="margin-top:8px">
                Features
                <button class="rs-section-add" @click="addFeature"><i class="fa-solid fa-plus" /></button>
              </div>
              <div v-for="(f, i) in (el.features || [])" :key="i" class="rs-list-item">
                <input class="rs-input rs-list-input" :value="f"
                  @input="el.features[i] = $event.target.value; emit('mark-dirty')" />
                <button class="rs-list-del" @click="el.features.splice(i, 1); emit('mark-dirty')"><i
                    class="fa-solid fa-xmark" /></button>
              </div>
            </div>
          </template>

          <!-- Stat row -->
          <template v-if="el.type === 'stat-row'">
            <div class="rs-section">
              <div class="rs-section-title">
                <i class="fa-solid fa-table-columns" /> Stats
                <button class="rs-section-add" @click="addStat"><i class="fa-solid fa-plus" /></button>
              </div>
              <div v-for="(stat, i) in (el.stats || [])" :key="i" class="rs-stat-editor">
                <input class="rs-input rs-mb4" :value="stat.value"
                  @input="stat.value = $event.target.value; emit('mark-dirty')" placeholder="Value" />
                <input class="rs-input" :value="stat.label"
                  @input="stat.label = $event.target.value; emit('mark-dirty')" placeholder="Label" />
                <button class="rs-list-del" @click="el.stats.splice(i, 1); emit('mark-dirty')"><i
                    class="fa-solid fa-xmark" /></button>
              </div>
            </div>
          </template>

          <!-- Icon / Avatar -->
          <template v-if="el.type === 'icon' || el.type === 'avatar'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-icons" /> Icon / Avatar</div>
              <div class="rs-row"><label class="rs-label">Emoji</label><input class="rs-input rs-emoji-input"
                  :value="el.content" @input="setElProp('content', $event.target.value)" /></div>
              <div class="rs-row">
                <label class="rs-label">Size <span class="rs-val">{{ s.fontSize || 40 }}px</span></label>
                <input type="range" min="16" max="200" step="4" :value="s.fontSize || 40"
                  @input="setProp('styles.fontSize', +$event.target.value)" class="rs-range" />
              </div>
            </div>
          </template>

          <!-- Watermark element -->
          <template v-if="el.type === 'watermark'">
            <div class="rs-section">
              <div class="rs-section-title"><i class="fa-solid fa-droplet" /> Watermark</div>
              <div class="rs-row"><label class="rs-label">Text</label><input class="rs-input" :value="el.content"
                  @input="setElProp('content', $event.target.value)" /></div>
              <div class="rs-row"><label class="rs-label">Color</label><input type="color" :value="s.color || '#94a3b8'"
                  @input="setProp('styles.color', $event.target.value)" class="rs-color" /></div>
              <div class="rs-row">
                <label class="rs-label">Opacity <span class="rs-val">{{ s.opacity || 20 }}%</span></label>
                <input type="range" min="1" max="100" step="1" :value="s.opacity || 20"
                  @input="setProp('styles.opacity', +$event.target.value)" class="rs-range" />
              </div>
              <div class="rs-row">
                <label class="rs-label">Size <span class="rs-val">{{ s.fontSize || 48 }}px</span></label>
                <input type="range" min="12" max="200" step="4" :value="s.fontSize || 48"
                  @input="setProp('styles.fontSize', +$event.target.value)" class="rs-range" />
              </div>
            </div>
          </template>

        </div>

        <!-- ══ 4. EFFECTS ═════════════════════════════════════════════ -->
        <div v-show="activeTab === 'effects'" class="rs-panel">

          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-wand-magic-sparkles" /> CSS Filters</div>
            <div class="rs-row">
              <label class="rs-label">Blur <span class="rs-val">{{ s.blur || 0 }}px</span></label>
              <input type="range" min="0" max="40" step="0.5" :value="s.blur || 0"
                @input="setProp('styles.blur', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Brightness <span class="rs-val">{{ s.brightness ?? 100 }}%</span></label>
              <input type="range" min="0" max="300" step="5" :value="s.brightness ?? 100"
                @input="setProp('styles.brightness', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Contrast <span class="rs-val">{{ s.contrast ?? 100 }}%</span></label>
              <input type="range" min="0" max="300" step="5" :value="s.contrast ?? 100"
                @input="setProp('styles.contrast', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Saturate <span class="rs-val">{{ s.saturate ?? 100 }}%</span></label>
              <input type="range" min="0" max="400" step="5" :value="s.saturate ?? 100"
                @input="setProp('styles.saturate', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Grayscale <span class="rs-val">{{ s.grayscale || 0 }}%</span></label>
              <input type="range" min="0" max="100" step="5" :value="s.grayscale || 0"
                @input="setProp('styles.grayscale', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Sepia <span class="rs-val">{{ s.sepia || 0 }}%</span></label>
              <input type="range" min="0" max="100" step="5" :value="s.sepia || 0"
                @input="setProp('styles.sepia', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Hue Rotate <span class="rs-val">{{ s.hueRotate || 0 }}°</span></label>
              <input type="range" min="0" max="360" step="5" :value="s.hueRotate || 0"
                @input="setProp('styles.hueRotate', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <label class="rs-label">Invert <span class="rs-val">{{ s.invert || 0 }}%</span></label>
              <input type="range" min="0" max="100" step="5" :value="s.invert || 0"
                @input="setProp('styles.invert', +$event.target.value)" class="rs-range" />
            </div>
            <div class="rs-row">
              <button class="rs-action-btn" @click="resetFilters">
                <i class="fa-solid fa-rotate-left" /> Reset Filters
              </button>
            </div>
          </div>

          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-layer-group" /> Blend Mode</div>
            <div class="rs-row">
              <label class="rs-label">Mode</label>
              <select class="rs-select" :value="s.mixBlendMode || 'normal'"
                @change="setProp('styles.mixBlendMode', $event.target.value)">
                <option>normal</option>
                <option>multiply</option>
                <option>screen</option>
                <option>overlay</option>
                <option>darken</option>
                <option>lighten</option>
                <option>color-dodge</option>
                <option>color-burn</option>
                <option>hard-light</option>
                <option>soft-light</option>
                <option>difference</option>
                <option>exclusion</option>
                <option>hue</option>
                <option>saturation</option>
                <option>color</option>
                <option>luminosity</option>
              </select>
            </div>
          </div>

          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-arrows-left-right" /> Flip</div>
            <div class="rs-toggle-row">
              <button class="rs-fmt-btn" :class="{ active: s.scaleX === -1 }"
                @click="setProp('styles.scaleX', s.scaleX === -1 ? 1 : -1)">
                <i class="fa-solid fa-flip-horizontal" /> Flip H
              </button>
              <button class="rs-fmt-btn" :class="{ active: s.scaleY === -1 }"
                @click="setProp('styles.scaleY', s.scaleY === -1 ? 1 : -1)">
                <i class="fa-solid fa-flip-vertical" /> Flip V
              </button>
            </div>
          </div>
        </div>

        <!-- ══ 5. ARRANGE ═════════════════════════════════════════════ -->
        <div v-show="activeTab === 'arrange'" class="rs-panel">

          <!-- Position + Size -->
          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-up-down-left-right" /> Position & Size</div>
            <div class="rs-pos-grid">
              <div class="rs-pos-field">
                <label>X</label>
                <input type="number" :value="Math.round(el.position?.x || 0)" @input="setPos('x', +$event.target.value)"
                  class="rs-pos-input" />
              </div>
              <div class="rs-pos-field">
                <label>Y</label>
                <input type="number" :value="Math.round(el.position?.y || 0)" @input="setPos('y', +$event.target.value)"
                  class="rs-pos-input" />
              </div>
              <div class="rs-pos-field">
                <label>W</label>
                <input type="number" min="1" :value="Math.round(s.width || 100)"
                  @input="setProp('styles.width', +$event.target.value)" class="rs-pos-input" />
              </div>
              <div class="rs-pos-field">
                <label>H</label>
                <input type="number" min="1" :value="Math.round(s.height || 60)"
                  @input="setProp('styles.height', +$event.target.value)" class="rs-pos-input" />
              </div>
            </div>
            <div class="rs-row">
              <label class="rs-label">Rotation <span class="rs-val">{{ s.rotate || 0 }}°</span></label>
              <div class="rs-slider-number">
                <input type="range" min="-180" max="180" step="1" :value="s.rotate || 0"
                  @input="setProp('styles.rotate', +$event.target.value)" class="rs-range" />
                <input type="number" min="-180" max="180" :value="s.rotate || 0"
                  @input="setProp('styles.rotate', +$event.target.value)" class="rs-num-input" />
              </div>
            </div>
            <div class="rs-row">
              <label class="rs-label">Z-Index</label>
              <input type="number" min="1" max="999" :value="s.zIndex || 1"
                @input="setProp('styles.zIndex', +$event.target.value)" class="rs-pos-input" />
            </div>
          </div>

          <!-- Layer order -->
          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-layer-group" /> Layer Order</div>
            <div class="rs-toggle-row">
              <button class="rs-fmt-btn" @click="$emit('bring-front')"><i class="fa-solid fa-angles-up" />
                Front</button>
              <button class="rs-fmt-btn" @click="$emit('send-back')"><i class="fa-solid fa-angles-down" /> Back</button>
            </div>
          </div>

          <!-- Align to page -->
          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-align-center" /> Align to Page</div>
            <div class="rs-align-grid">
              <button class="rs-align-btn" @click="alignEl('left')" title="Align left"><i
                  class="fa-solid fa-align-left" /></button>
              <button class="rs-align-btn" @click="alignEl('center')" title="Center H"><i
                  class="fa-solid fa-align-center" /></button>
              <button class="rs-align-btn" @click="alignEl('right')" title="Align right"><i
                  class="fa-solid fa-align-right" /></button>
              <button class="rs-align-btn" @click="alignEl('top')" title="Align top"><i
                  class="fa-solid fa-arrow-up-to-line" /></button>
              <button class="rs-align-btn" @click="alignEl('middle')" title="Center V"><i
                  class="fa-solid fa-arrows-up-down" /></button>
              <button class="rs-align-btn" @click="alignEl('bottom')" title="Align bottom"><i
                  class="fa-solid fa-arrow-down-to-line" /></button>
            </div>
          </div>

          <!-- Actions -->
          <div class="rs-section">
            <div class="rs-section-title"><i class="fa-solid fa-toolbox" /> Actions</div>
            <div class="rs-toggle-row">
              <button class="rs-fmt-btn" :class="{ active: el.locked }" @click="$emit('lock-el')">
                <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" /> {{ el.locked ? 'Locked' : 'Lock'
                }}
              </button>
              <button class="rs-fmt-btn" @click="$emit('duplicate-el')"><i class="fa-solid fa-clone" />
                Duplicate</button>
            </div>
            <button class="rs-action-btn rs-full-btn rs-danger-full" @click="$emit('delete-el')">
              <i class="fa-solid fa-trash-can" /> Delete Element
            </button>
          </div>

        </div>
      </div>
    </template>
  </aside>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// ── Props / Emits ───────────────────────────────────────────────────
const props = defineProps({
  el: { type: Object, default: null },
  settings: { type: Object, required: true },
  isDark: { type: Boolean, default: false },
  pageIndex: { type: Number, default: 0 },
  elIndex: { type: Number, default: null },
  pageDims: { type: Array, default: () => [794, 1123] }, // [w, h]
})

const emit = defineEmits([
  'update:el-prop', 'apply-style', 'delete-el', 'duplicate-el', 'lock-el',
  'bring-front', 'send-back',
  'add-table-row', 'add-table-col', 'remove-table-row', 'remove-table-col',
  'mark-dirty',
])

// ── State ───────────────────────────────────────────────────────────
const activeTab = ref('style')

// Switch to content tab when a new element is selected
watch(() => props.el?.id, () => { activeTab.value = 'style' })

// ── Shorthand ───────────────────────────────────────────────────────
const s = computed(() => props.el?.styles || {})

// ── Helpers ─────────────────────────────────────────────────────────
function setProp(path, value) {
  emit('update:el-prop', path, value)
}

function setElProp(key, value) {
  emit('update:el-prop', key, value)
}

function setPos(axis, value) {
  if (!props.el) return
  const pos = { ...(props.el.position || { x: 0, y: 0 }) }
  pos[axis] = Math.max(0, value)
  emit('update:el-prop', 'position', pos)
}

function toggle(path, onVal, offVal) {
  const current = s.value[path.split('.')[1]]
  setProp(path, current === onVal ? offVal : onVal)
}

// ── Element type helpers ─────────────────────────────────────────────
const TEXT_TYPES = ['text', 'heading', 'subheading', 'quote', 'blockquote', 'highlight', 'badge', 'code', 'link', 'callout', 'richtext', 'list', 'pagenum', 'date-el', 'watermark']
const CHART_TYPES = ['bar-chart', 'line-chart', 'area-chart', 'pie-chart', 'doughnut-chart', 'radar-chart', 'scatter-chart']

const isTextEl = computed(() => TEXT_TYPES.includes(props.el?.type))
const isChartEl = computed(() => CHART_TYPES.includes(props.el?.type))

const EL_ICONS = {
  heading: 'fa-solid fa-heading', subheading: 'fa-solid fa-text-height', text: 'fa-solid fa-align-left',
  richtext: 'fa-solid fa-file-pen', image: 'fa-solid fa-image', video: 'fa-solid fa-video',
  table: 'fa-solid fa-table', 'bar-chart': 'fa-solid fa-chart-column', 'line-chart': 'fa-solid fa-chart-line',
  'pie-chart': 'fa-solid fa-chart-pie', 'doughnut-chart': 'fa-solid fa-circle-half-stroke',
  'area-chart': 'fa-solid fa-chart-area', metric: 'fa-solid fa-arrow-trend-up',
  progress: 'fa-solid fa-bars-progress', checklist: 'fa-solid fa-square-check',
  timeline: 'fa-solid fa-timeline', rectangle: 'fa-regular fa-square', circle: 'fa-regular fa-circle',
  rating: 'fa-solid fa-star', callout: 'fa-solid fa-bullhorn', signature: 'fa-solid fa-signature',
  toc: 'fa-solid fa-list-ol', 'qr-code': 'fa-solid fa-qrcode', map: 'fa-solid fa-map-location-dot',
}

const elIcon = computed(() => EL_ICONS[props.el?.type] || 'fa-solid fa-cube')
const elTypeLabel = computed(() => (props.el?.type || '').replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()))

// ── Chart helpers ────────────────────────────────────────────────────
const chartLabels = computed(() => (props.el?.chartData?.labels || []).join(','))
const chartValues = computed(() => (props.el?.chartData?.values || []).join(','))

function setChartLabels(v) {
  setElProp('chartData', { ...props.el?.chartData, labels: v.split(',').map(s => s.trim()) })
}
function setChartValues(v) {
  setElProp('chartData', { ...props.el?.chartData, values: v.split(',').map(s => +s.trim() || 0) })
}

// ── QR Code ───────────────────────────────────────────────────────────
function regenerateQr() {
  const text = props.el?.qrText || 'https://example.com'
  const size = props.el?.qrSize || 160
  setElProp('qrUrl', `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}`)
}

// ── List item helpers ─────────────────────────────────────────────────
function addChecklistItem() { const el = props.el; (el.items = el.items || []).push({ text: 'New item', checked: false }); emit('mark-dirty') }
function addTimelineItem() { const el = props.el; (el.items = el.items || []).push({ date: 'Date', label: 'Event', desc: '' }); emit('mark-dirty') }
function addStepItem() { const el = props.el; (el.items = el.items || []).push({ label: 'New step' }); emit('mark-dirty') }
function addFeature() { const el = props.el; (el.features = el.features || []).push('New feature'); emit('mark-dirty') }
function addStat() { const el = props.el; (el.stats = el.stats || []).push({ value: '0', label: 'Label' }); emit('mark-dirty') }

function removeItem(key, idx) {
  const el = props.el
  if (el && el[key]) { el[key].splice(idx, 1); emit('mark-dirty') }
}

// ── Align to page ─────────────────────────────────────────────────────
function alignEl(dir) {
  const el = props.el
  if (!el) return
  const [pw, ph] = props.pageDims
  const w = s.value.width || 100
  const h = s.value.height || 60
  const pos = { ...(el.position || { x: 0, y: 0 }) }

  if (dir === 'left') pos.x = 0
  if (dir === 'center') pos.x = (pw - w) / 2
  if (dir === 'right') pos.x = pw - w
  if (dir === 'top') pos.y = 0
  if (dir === 'middle') pos.y = (ph - h) / 2
  if (dir === 'bottom') pos.y = ph - h

  emit('update:el-prop', 'position', pos)
}

// ── Filter reset ─────────────────────────────────────────────────────
function resetFilters() {
  ['blur', 'brightness', 'contrast', 'saturate', 'grayscale', 'sepia', 'hueRotate', 'invert'].forEach(k => {
    const def = ['blur', 'grayscale', 'sepia', 'hueRotate', 'invert'].includes(k) ? 0 : 100
    setProp(`styles.${k}`, def)
  })
  setProp('styles.mixBlendMode', 'normal')
}

// ── Constants ─────────────────────────────────────────────────────────
const TABS = [
  { id: 'style', label: 'Style', icon: 'fa-solid fa-fill' },
  { id: 'typography', label: 'Type', icon: 'fa-solid fa-font' },
  { id: 'content', label: 'Content', icon: 'fa-solid fa-pen-to-square' },
  { id: 'effects', label: 'Effects', icon: 'fa-solid fa-wand-magic-sparkles' },
  { id: 'arrange', label: 'Arrange', icon: 'fa-solid fa-up-down-left-right' },
]

const FONTS = [
  'DM Sans', 'Inter', 'Plus Jakarta Sans', 'Space Grotesk', 'Sora', 'Nunito', 'Outfit', 'Poppins',
  'Figtree', 'Georgia', 'Playfair Display', 'Merriweather', 'Lora', 'Fira Code', 'Courier New', 'Times New Roman',
]

const SHADOW_PRESETS = [
  { label: 'None', value: 'none' },
  { label: 'XS', value: '0 1px 3px rgba(0,0,0,.12)' },
  { label: 'SM', value: '0 4px 12px rgba(0,0,0,.12)' },
  { label: 'MD', value: '0 8px 24px rgba(0,0,0,.15)' },
  { label: 'LG', value: '0 16px 48px rgba(0,0,0,.18)' },
  { label: 'Glow', value: '0 0 20px rgba(99,102,241,.45)' },
  { label: 'Warm', value: '0 8px 24px rgba(201,168,76,.35)' },
  { label: 'Inner', value: 'inset 0 2px 8px rgba(0,0,0,.15)' },
]
</script>

<style scoped>
/* ═══ ROOT ═══════════════════════════════════════════════════════════ */
.rs-root {
  --rs-bg: #ffffff;
  --rs-bg2: #f8fafc;
  --rs-bg3: #f1f5f9;
  --rs-border: #e2e8f0;
  --rs-text: #0f172a;
  --rs-text2: #475569;
  --rs-text3: #94a3b8;
  --rs-accent: #6366f1;
  --rs-accent-l: rgba(99, 102, 241, .08);
  --rs-danger: #ef4444;

  width: 280px;
  min-width: 260px;
  max-width: 300px;
  height: 100%;
  background: var(--rs-bg);
  border-left: 1px solid var(--rs-border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  font-size: 12px;
}

.rs-root.rs-dark {
  --rs-bg: #111827;
  --rs-bg2: #1a2236;
  --rs-bg3: #0f172a;
  --rs-border: #1e2d45;
  --rs-text: #e2e8f0;
  --rs-text2: #94a3b8;
  --rs-text3: #475569;
}

/* ═══ EMPTY ══════════════════════════════════════════════════════════ */
.rs-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: var(--rs-text3);
  text-align: center;
  padding: 32px 20px;
  font-size: 12px;
}

.rs-empty i {
  font-size: 28px;
  opacity: .3;
}

/* ═══ ELEMENT HEADER ═════════════════════════════════════════════════ */
.rs-el-header {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 10px 12px;
  border-bottom: 1px solid var(--rs-border);
  flex-shrink: 0;
  background: var(--rs-bg2);
}

.rs-el-type-icon {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: var(--rs-accent-l);
  color: var(--rs-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}

.rs-el-info {
  flex: 1;
  min-width: 0;
}

.rs-el-type {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: var(--rs-text);
  text-transform: capitalize;
}

.rs-el-id {
  display: block;
  font-size: 9px;
  color: var(--rs-text3);
  font-family: monospace;
}

.rs-icon-btn {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  border: 1px solid var(--rs-border);
  background: transparent;
  cursor: pointer;
  color: var(--rs-text2);
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
  flex-shrink: 0;
}

.rs-icon-btn:hover {
  background: var(--rs-bg3);
  color: var(--rs-text);
}

.rs-icon-btn.active {
  background: var(--rs-accent-l);
  color: var(--rs-accent);
  border-color: var(--rs-accent);
}

.rs-danger-btn:hover {
  background: rgba(239, 68, 68, .08);
  color: var(--rs-danger);
  border-color: var(--rs-danger);
}

/* ═══ TABS ═══════════════════════════════════════════════════════════ */
.rs-tabs {
  display: flex;
  border-bottom: 1px solid var(--rs-border);
  flex-shrink: 0;
  overflow-x: auto;
  scrollbar-width: none;
}

.rs-tabs::-webkit-scrollbar {
  display: none;
}

.rs-tab {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  padding: 8px 4px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--rs-text3);
  font-size: 9px;
  font-weight: 600;
  font-family: inherit;
  border-bottom: 2px solid transparent;
  transition: all .14s;
  white-space: nowrap;
  text-transform: uppercase;
  letter-spacing: .04em;
}

.rs-tab i {
  font-size: 12px;
}

.rs-tab:hover {
  color: var(--rs-text);
}

.rs-tab.active {
  color: var(--rs-accent);
  border-bottom-color: var(--rs-accent);
}

/* ═══ BODY / PANEL ═══════════════════════════════════════════════════ */
.rs-body {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.rs-panel {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  scrollbar-width: thin;
  scrollbar-color: var(--rs-border) transparent;
}

/* ═══ SECTION ════════════════════════════════════════════════════════ */
.rs-section {
  border: 1px solid var(--rs-border);
  border-radius: 10px;
  overflow: hidden;
  background: var(--rs-bg);
}

.rs-section-title {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 8px 11px;
  background: var(--rs-bg2);
  font-size: 10px;
  font-weight: 800;
  color: var(--rs-text2);
  text-transform: uppercase;
  letter-spacing: .06em;
  border-bottom: 1px solid var(--rs-border);
}

.rs-section-add {
  margin-left: auto;
  width: 22px;
  height: 22px;
  border-radius: 6px;
  border: 1px solid var(--rs-border);
  background: var(--rs-bg);
  cursor: pointer;
  color: var(--rs-text2);
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .12s;
}

.rs-section-add:hover {
  background: var(--rs-accent);
  color: #fff;
  border-color: var(--rs-accent);
}

/* ═══ ROWS ═══════════════════════════════════════════════════════════ */
.rs-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 11px;
  border-bottom: 1px solid var(--rs-border);
}

.rs-row:last-child {
  border-bottom: none;
}

.rs-row--stacked {
  flex-direction: column;
  align-items: stretch;
  gap: 5px;
}

.rs-label {
  font-size: 11px;
  color: var(--rs-text2);
  flex: 1;
  min-width: 60px;
  white-space: nowrap;
  font-weight: 500;
}

.rs-label-block {
  font-size: 11px;
  color: var(--rs-text2);
  font-weight: 500;
}

.rs-val {
  font-size: 10px;
  color: var(--rs-accent);
  font-weight: 600;
}

/* ═══ INPUTS ══════════════════════════════════════════════════════════ */
.rs-input {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  background: var(--rs-bg2);
  color: var(--rs-text);
  font-size: 11px;
  outline: none;
  transition: border-color .14s;
  font-family: inherit;
}

.rs-input:focus {
  border-color: var(--rs-accent);
}

.rs-select {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  background: var(--rs-bg2);
  color: var(--rs-text);
  font-size: 11px;
  outline: none;
  cursor: pointer;
  font-family: inherit;
  transition: border-color .14s;
}

.rs-select:focus {
  border-color: var(--rs-accent);
}

.rs-color {
  width: 32px;
  height: 26px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  cursor: pointer;
  padding: 2px;
  background: transparent;
}

.rs-hex {
  width: 76px;
  padding: 5px 7px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  background: var(--rs-bg2);
  color: var(--rs-text);
  font-size: 10px;
  font-family: monospace;
  outline: none;
}

.rs-hex:focus {
  border-color: var(--rs-accent);
}

.rs-color-row {
  display: flex;
  align-items: center;
  gap: 5px;
}

.rs-clear-btn {
  width: 26px;
  height: 26px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  background: var(--rs-bg2);
  cursor: pointer;
  color: var(--rs-text3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  transition: all .12s;
}

.rs-clear-btn:hover {
  border-color: var(--rs-accent);
  color: var(--rs-accent);
}

.rs-range {
  flex: 1;
  accent-color: var(--rs-accent);
}

.rs-num-input {
  width: 52px;
  padding: 4px 6px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  background: var(--rs-bg2);
  color: var(--rs-text);
  font-size: 11px;
  outline: none;
  text-align: center;
  font-family: inherit;
  flex-shrink: 0;
}

.rs-num-input:focus {
  border-color: var(--rs-accent);
}

.rs-slider-number {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.rs-textarea {
  width: 100%;
  padding: 7px 10px;
  border: 1px solid var(--rs-border);
  border-radius: 7px;
  background: var(--rs-bg2);
  color: var(--rs-text);
  font-size: 11px;
  font-family: inherit;
  resize: vertical;
  outline: none;
  min-height: 80px;
}

.rs-textarea:focus {
  border-color: var(--rs-accent);
}

.rs-textarea--sm {
  min-height: 60px;
}

.rs-emoji-input {
  max-width: 80px;
  font-size: 18px;
  text-align: center;
}

/* ═══ BUTTON GROUPS ══════════════════════════════════════════════════ */
.rs-btn-group {
  display: flex;
  gap: 4px;
  flex: 1;
}

.rs-btn-group button {
  flex: 1;
  padding: 5px 8px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  background: var(--rs-bg2);
  color: var(--rs-text2);
  cursor: pointer;
  font-size: 10px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all .12s;
  font-family: inherit;
}

.rs-btn-group button:hover {
  border-color: var(--rs-accent);
  color: var(--rs-accent);
}

.rs-btn-group button.active {
  background: var(--rs-accent);
  color: #fff;
  border-color: var(--rs-accent);
}

.rs-toggle-row {
  display: flex;
  gap: 5px;
  padding: 7px 11px;
  flex-wrap: wrap;
}

.rs-fmt-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  border: 1px solid var(--rs-border);
  border-radius: 7px;
  cursor: pointer;
  color: var(--rs-text2);
  background: var(--rs-bg2);
  font-size: 11px;
  font-family: inherit;
  font-weight: 500;
  transition: all .12s;
}

.rs-fmt-btn:hover {
  border-color: var(--rs-accent);
  color: var(--rs-accent);
}

.rs-fmt-btn.active {
  background: var(--rs-accent-l);
  color: var(--rs-accent);
  border-color: var(--rs-accent);
  font-weight: 700;
}

.rs-align-btns {
  display: flex;
  gap: 3px;
}

.rs-align-btns button {
  width: 26px;
  height: 26px;
  border: 1px solid var(--rs-border);
  border-radius: 5px;
  background: var(--rs-bg2);
  cursor: pointer;
  color: var(--rs-text2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  transition: all .12s;
}

.rs-align-btns button:hover,
.rs-align-btns button.active {
  background: var(--rs-accent);
  color: #fff;
  border-color: var(--rs-accent);
}

/* ═══ SHADOWS ════════════════════════════════════════════════════════ */
.rs-shadow-presets {
  display: flex;
  gap: 6px;
  padding: 8px 11px;
  flex-wrap: wrap;
  border-bottom: 1px solid var(--rs-border);
}

.rs-shadow-btn {
  width: 36px;
  height: 30px;
  border: 2px solid var(--rs-border);
  border-radius: 7px;
  background: var(--rs-bg);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .14s;
  padding: 3px;
}

.rs-shadow-btn:hover {
  border-color: var(--rs-accent);
}

.rs-shadow-btn.active {
  border-color: var(--rs-accent);
  background: var(--rs-accent-l);
}

.rs-shadow-demo {
  width: 18px;
  height: 12px;
  background: var(--rs-bg2);
  border: 1px solid var(--rs-border);
  border-radius: 3px;
}

/* ═══ TOGGLE SWITCH ══════════════════════════════════════════════════ */
.rs-toggle-sw {
  display: flex;
  align-items: center;
  gap: 7px;
  cursor: pointer;
}

.rs-toggle-sw input {
  display: none;
}

.rs-sw-track {
  width: 32px;
  height: 18px;
  border-radius: 9px;
  background: var(--rs-border);
  position: relative;
  transition: background .2s;
  flex-shrink: 0;
}

.rs-toggle-sw input:checked~.rs-sw-track {
  background: var(--rs-accent);
}

.rs-sw-track::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #fff;
  transition: transform .2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
}

.rs-toggle-sw input:checked~.rs-sw-track::after {
  transform: translateX(14px);
}

.rs-sw-label {
  font-size: 10px;
  font-weight: 600;
  color: var(--rs-text3);
}

/* ═══ ARRANGE / POSITION ═════════════════════════════════════════════ */
.rs-pos-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 7px;
  padding: 10px 11px;
  border-bottom: 1px solid var(--rs-border);
}

.rs-pos-field {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.rs-pos-field label {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--rs-text3);
  letter-spacing: .06em;
}

.rs-pos-input {
  width: 100%;
  padding: 5px 7px;
  border: 1px solid var(--rs-border);
  border-radius: 6px;
  background: var(--rs-bg2);
  color: var(--rs-text);
  font-size: 11px;
  outline: none;
  font-family: inherit;
  text-align: center;
}

.rs-pos-input:focus {
  border-color: var(--rs-accent);
}

.rs-align-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 5px;
  padding: 10px 11px;
}

.rs-align-btn {
  padding: 8px;
  border: 1px solid var(--rs-border);
  border-radius: 7px;
  background: var(--rs-bg2);
  cursor: pointer;
  color: var(--rs-text2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: all .12s;
}

.rs-align-btn:hover {
  background: var(--rs-accent);
  color: #fff;
  border-color: var(--rs-accent);
}

/* ═══ LIST / COMPLEX ITEM EDITORS ════════════════════════════════════ */
.rs-list-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 11px;
  border-bottom: 1px solid var(--rs-border);
}

.rs-list-item:last-child {
  border-bottom: none;
}

.rs-list-input {
  flex: 1;
}

.rs-checkbox {
  cursor: pointer;
  accent-color: var(--rs-accent);
  flex-shrink: 0;
}

.rs-list-del {
  width: 22px;
  height: 22px;
  border-radius: 5px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--rs-text3);
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .12s;
  flex-shrink: 0;
}

.rs-list-del:hover {
  background: rgba(239, 68, 68, .1);
  color: var(--rs-danger);
}

.rs-step-num {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--rs-accent);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.rs-timeline-item-editor {
  display: flex;
  gap: 7px;
  padding: 8px 11px;
  border-bottom: 1px solid var(--rs-border);
  align-items: flex-start;
}

.rs-timeline-item-editor:last-child {
  border-bottom: none;
}

.rs-tl-num {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--rs-accent);
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 3px;
}

.rs-tl-fields {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rs-mb4 {
  margin-bottom: 0;
}

.rs-stat-editor {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 8px 11px;
  border-bottom: 1px solid var(--rs-border);
  position: relative;
}

.rs-stat-editor:last-child {
  border-bottom: none;
}

.rs-stat-editor .rs-list-del {
  position: absolute;
  top: 8px;
  right: 8px;
}

/* ═══ ACTIONS ════════════════════════════════════════════════════════ */
.rs-action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 6px 12px;
  border: 1px solid var(--rs-border);
  border-radius: 7px;
  background: var(--rs-bg2);
  color: var(--rs-text2);
  cursor: pointer;
  font-size: 11px;
  font-family: inherit;
  transition: all .14s;
  font-weight: 500;
}

.rs-action-btn:hover {
  border-color: var(--rs-accent);
  color: var(--rs-accent);
  background: var(--rs-accent-l);
}

.rs-table-actions {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
}

.rs-full-btn {
  width: 100%;
  justify-content: center;
}

.rs-danger-sm:hover {
  border-color: var(--rs-danger);
  color: var(--rs-danger);
  background: rgba(239, 68, 68, .05);
}

.rs-danger-full {
  border-color: rgba(239, 68, 68, .3);
  color: var(--rs-danger);
  background: rgba(239, 68, 68, .05);
}

.rs-danger-full:hover {
  background: var(--rs-danger);
  color: #fff;
  border-color: var(--rs-danger);
}

/* ═══ EMPTY TAB ══════════════════════════════════════════════════════ */
.rs-empty-tab {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: var(--rs-text3);
  text-align: center;
  padding: 32px 20px;
  font-size: 12px;
}

.rs-empty-tab i {
  font-size: 24px;
  opacity: .3;
}

/* ═══ RESPONSIVE ═════════════════════════════════════════════════════ */
@media (max-width: 1200px) {
  .rs-root {
    width: 260px;
    min-width: 240px;
  }
}
</style>