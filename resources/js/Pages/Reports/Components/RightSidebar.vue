<!--
  RightSidebar.vue — FULLY ENHANCED
  • Style tab: Position, Fill, Border, Shadow, Effects, Blend, Transform, Priority
  • Typography tab: Font family, size, weight, color, alignment, spacing
  • Content tab: Full editors for ALL 50+ element types
      – Table: live row/col add/remove, header color, cell editor
      – Charts: live label/value add/remove, type switcher dropdown, color
      – Metric/KPI: all fields editable
      – Progress/Circular: slider, label, color
      – Timeline: add/remove items, date/title/desc editors
      – Checklist: add/remove/reorder items, check all
      – Stat Row: add/remove stats
      – Callout: emoji picker, content
      – Testimonial, Signature, Price Card, Social Card, Kanban, HTML Embed
      – QR Code, Video, Map, Rating, Sparkline data
  • Effects tab: CSS filters, blend modes, flip, hover animation
  • Arrange tab: Z-order, align to page, distribute, group, duplicate, delete
-->
<template>
  <aside class="right-panel" :class="{ collapsed: isCollapsed }">
    <!-- Collapse toggle -->
    <button class="panel-toggle" @click="$emit('update:is-collapsed', !isCollapsed)" :title="isCollapsed ? 'Expand' : 'Collapse'">
      <i :class="isCollapsed ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'" />
    </button>

    <div v-if="!isCollapsed" class="panel-body">

      <!-- ══ NO SELECTION ═════════════════════════════════════════════════ -->
      <div v-if="!el" class="no-sel">
        <div class="no-sel-icon"><i class="fa-solid fa-hand-pointer" /></div>
        <h3>No Element Selected</h3>
        <p>Click any element on the canvas to edit its properties</p>
      </div>

      <!-- ══ ELEMENT PROPERTIES ══════════════════════════════════════════ -->
      <template v-else>

        <!-- Element type header -->
        <div class="el-header">
          <div class="el-type-badge">
            <i :class="typeIcon(el.type)" />
            <span>{{ el.type }}</span>
          </div>
          <div class="el-header-actions">
            <button @click="$emit('duplicate-el')" title="Duplicate"><i class="fa-solid fa-clone" /></button>
            <button @click="$emit('lock-el')" :class="{ active: el.locked }" title="Lock"><i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" /></button>
            <button class="danger-btn" @click="$emit('delete-el')" title="Delete"><i class="fa-solid fa-trash-can" /></button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="prop-tabs">
          <button v-for="t in tabs" :key="t.id" class="prop-tab" :class="{ active: activeTab === t.id }" @click="activeTab = t.id">
            <i :class="t.icon" /><span>{{ t.label }}</span>
          </button>
        </div>

        <div class="prop-body">

          <!-- ══ STYLE TAB ════════════════════════════════════════════════ -->
          <div v-show="activeTab === 'style'">

            <!-- Position & Size -->
            <Sec title="Position & Size" icon="fa-solid fa-arrows-up-down-left-right">
              <div class="grid-4">
                <Field label="X">
                  <NumIn :value="Math.round(el.position?.x||0)" @change="updatePos('x',$event)" />
                </Field>
                <Field label="Y">
                  <NumIn :value="Math.round(el.position?.y||0)" @change="updatePos('y',$event)" />
                </Field>
                <Field label="W">
                  <NumIn :value="Math.round(el.styles?.width||200)" @change="setS('width',$event)" :min="10" />
                </Field>
                <Field label="H">
                  <NumIn :value="Math.round(el.styles?.height||80)" @change="setS('height',$event)" :min="10" />
                </Field>
              </div>
              <Row label="Rotation">
                <div class="slider-row">
                  <input type="range" min="-180" max="180" :value="el.styles?.rotate||0" @input="setS('rotate',+$event.target.value)" class="slider" />
                  <span class="val">{{ Math.round(el.styles?.rotate||0) }}°</span>
                </div>
              </Row>
              <Row label="Z-Index">
                <NumIn :value="el.styles?.zIndex||1" @change="setS('zIndex',$event)" :min="0" :max="9999" class="w-full" />
              </Row>
              <Row label="Opacity">
                <div class="slider-row">
                  <input type="range" min="0" max="100" :value="el.styles?.opacity??100" @input="setS('opacity',+$event.target.value)" class="slider" />
                  <span class="val">{{ el.styles?.opacity??100 }}%</span>
                </div>
              </Row>
            </Sec>

            <!-- Fill -->
            <Sec title="Fill & Color" icon="fa-solid fa-palette">
              <Row label="Background">
                <div class="color-row">
                  <input type="color" :value="el.styles?.backgroundColor||'#ffffff'" @input="setS('backgroundColor',$event.target.value)" class="color-pick" />
                  <input type="text" :value="el.styles?.backgroundColor||'transparent'" @input="setS('backgroundColor',$event.target.value)" class="txt-input mono" />
                  <button class="icon-btn-sm" @click="setS('backgroundColor','transparent')" title="No fill"><i class="fa-solid fa-ban" /></button>
                </div>
              </Row>
              <!-- Color presets -->
              <div class="color-presets">
                <div v-for="c in PRESETS" :key="c" class="color-dot" :style="{ background: c }" @click="setS('backgroundColor',c)" :title="c" />
              </div>
            </Sec>

            <!-- Border -->
            <Sec title="Border" icon="fa-solid fa-border-all">
              <div class="grid-2">
                <Field label="Width">
                  <NumIn :value="el.styles?.borderWidth||0" @change="setS('borderWidth',$event)" :min="0" :max="20" />
                </Field>
                <Field label="Style">
                  <select :value="el.styles?.borderStyle||'solid'" @change="setS('borderStyle',$event.target.value)" class="sel-input">
                    <option value="solid">Solid</option>
                    <option value="dashed">Dashed</option>
                    <option value="dotted">Dotted</option>
                    <option value="double">Double</option>
                    <option value="none">None</option>
                  </select>
                </Field>
              </div>
              <Row label="Color" v-if="el.styles?.borderWidth">
                <div class="color-row">
                  <input type="color" :value="el.styles?.borderColor||'#000000'" @input="setS('borderColor',$event.target.value)" class="color-pick" />
                  <input type="text" :value="el.styles?.borderColor||'#000000'" @input="setS('borderColor',$event.target.value)" class="txt-input mono" />
                </div>
              </Row>
              <Row label="Radius">
                <div class="slider-row">
                  <input type="range" min="0" max="100" :value="el.styles?.borderRadius||0" @input="setS('borderRadius',+$event.target.value)" class="slider" />
                  <span class="val">{{ el.styles?.borderRadius||0 }}px</span>
                </div>
              </Row>
            </Sec>

            <!-- Shadow -->
            <Sec title="Shadow" icon="fa-solid fa-layer-group">
              <div class="shadow-presets">
                <button v-for="sh in SHADOWS" :key="sh.name" class="shadow-btn" :class="{ active: el.styles?.boxShadow === sh.value }" @click="setS('boxShadow',sh.value)" :title="sh.name">
                  <div class="shadow-preview" :style="{ boxShadow: sh.value }" />
                  <span>{{ sh.name }}</span>
                </button>
              </div>
              <Row label="Custom">
                <input type="text" :value="el.styles?.boxShadow||''" @input="setS('boxShadow',$event.target.value)" class="txt-input" placeholder="0 4px 20px rgba(0,0,0,.15)" />
              </Row>
            </Sec>

          </div>

          <!-- ══ TYPOGRAPHY TAB ════════════════════════════════════════════ -->
          <div v-show="activeTab === 'text'">
            <template v-if="hasText">
              <Sec title="Font" icon="fa-solid fa-font">
                <Row label="Family">
                  <select :value="el.styles?.fontFamily||'inherit'" @change="setS('fontFamily',$event.target.value)" class="sel-input">
                    <option v-for="f in FONTS" :key="f.v" :value="f.v">{{ f.l }}</option>
                  </select>
                </Row>
                <div class="grid-2">
                  <Field label="Size">
                    <NumIn :value="el.styles?.fontSize||14" @change="setS('fontSize',$event)" :min="6" :max="200" />
                  </Field>
                  <Field label="Weight">
                    <select :value="el.styles?.fontWeight||'400'" @change="setS('fontWeight',$event.target.value)" class="sel-input">
                      <option value="300">Light</option>
                      <option value="400">Regular</option>
                      <option value="500">Medium</option>
                      <option value="600">SemiBold</option>
                      <option value="700">Bold</option>
                      <option value="800">ExtraBold</option>
                      <option value="900">Black</option>
                    </select>
                  </Field>
                </div>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#000000'" @input="setS('color',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.color||'#000000'" @input="setS('color',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
              </Sec>
              <Sec title="Alignment & Style" icon="fa-solid fa-align-left">
                <Row label="Align">
                  <div class="btn-group">
                    <button v-for="a in ['left','center','right','justify']" :key="a" :class="{ active: el.styles?.textAlign===a }" @click="setS('textAlign',a)">
                      <i :class="`fa-solid fa-align-${a}`" />
                    </button>
                  </div>
                </Row>
                <Row label="Style">
                  <div class="btn-group">
                    <button :class="{ active: el.styles?.fontStyle==='italic' }" @click="toggleStyle('fontStyle','italic','normal')" title="Italic"><i>I</i></button>
                    <button :class="{ active: el.styles?.textDecoration==='underline' }" @click="toggleStyle('textDecoration','underline','none')" title="Underline"><u>U</u></button>
                    <button :class="{ active: el.styles?.textDecoration==='line-through' }" @click="toggleStyle('textDecoration','line-through','none')" title="Strikethrough"><s>S</s></button>
                  </div>
                </Row>
                <Row label="Transform">
                  <select :value="el.styles?.textTransform||'none'" @change="setS('textTransform',$event.target.value)" class="sel-input">
                    <option value="none">None</option>
                    <option value="uppercase">UPPERCASE</option>
                    <option value="lowercase">lowercase</option>
                    <option value="capitalize">Capitalize</option>
                  </select>
                </Row>
                <Row label="Line H">
                  <div class="slider-row">
                    <input type="range" min="1" max="3" step="0.05" :value="el.styles?.lineHeight||1.5" @input="setS('lineHeight',+$event.target.value)" class="slider" />
                    <span class="val">{{ (el.styles?.lineHeight||1.5).toFixed(2) }}</span>
                  </div>
                </Row>
                <Row label="Spacing">
                  <div class="slider-row">
                    <input type="range" min="-2" max="10" step="0.5" :value="el.styles?.letterSpacing||0" @input="setS('letterSpacing',+$event.target.value)" class="slider" />
                    <span class="val">{{ el.styles?.letterSpacing||0 }}px</span>
                  </div>
                </Row>
              </Sec>
            </template>
            <div v-else class="no-props"><i class="fa-solid fa-font" /><p>Typography options apply to text elements</p></div>
          </div>

          <!-- ══ CONTENT TAB ════════════════════════════════════════════════ -->
          <div v-show="activeTab === 'content'">

            <!-- ─── Text content ─────────────────────────────────────────── -->
            <template v-if="hasText || el.type==='richtext'">
              <Sec title="Text Content" icon="fa-solid fa-align-left">
                <textarea :value="(el.content||'').replace(/<[^>]*>/g,'')" @input="el.content=$event.target.value; dirty()" rows="5" class="txt-area" placeholder="Enter text content…" />
              </Sec>
            </template>

            <!-- ─── Image ────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='image'">
              <Sec title="Image" icon="fa-solid fa-image">
                <Row label="URL">
                  <input type="text" :value="el.src||''" @input="el.src=$event.target.value; dirty()" class="txt-input" placeholder="https://…" />
                </Row>
                <Row label="Alt">
                  <input type="text" :value="el.alt||''" @input="el.alt=$event.target.value; dirty()" class="txt-input" placeholder="Alt text" />
                </Row>
                <Row label="Fit">
                  <select :value="el.styles?.objectFit||'cover'" @change="setS('objectFit',$event.target.value)" class="sel-input">
                    <option value="cover">Cover</option><option value="contain">Contain</option>
                    <option value="fill">Fill</option><option value="scale-down">Scale Down</option>
                    <option value="none">None</option>
                  </select>
                </Row>
                <Row label="Filter">
                  <select :value="el.styles?.imageFilter||'none'" @change="setS('imageFilter',$event.target.value)" class="sel-input">
                    <option value="none">None</option><option value="grayscale">Grayscale</option>
                    <option value="sepia">Sepia</option><option value="vintage">Vintage</option>
                    <option value="blur">Blur</option><option value="bright">Brighten</option>
                  </select>
                </Row>
              </Sec>
            </template>

            <!-- ─── TABLE ────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='table'">
              <Sec title="Table Data" icon="fa-solid fa-table">
                <div class="tbl-meta">
                  <span>{{ (el.columns||[]).length }} cols · {{ (el.data||[]).length }} rows</span>
                  <div class="btn-group">
                    <button @click="addRow" title="Add Row">+R</button>
                    <button @click="addCol" title="Add Col">+C</button>
                    <button @click="delRow" :disabled="(el.data||[]).length<=1" title="Remove Row">-R</button>
                    <button @click="delCol" :disabled="(el.columns||[]).length<=1" title="Remove Col">-C</button>
                  </div>
                </div>
                <div class="tbl-col-editor">
                  <div v-for="(col,ci) in (el.columns||[])" :key="ci" class="tbl-col-row">
                    <span class="tbl-col-idx">{{ ci+1 }}</span>
                    <input type="text" :value="col" @input="el.columns[ci]=$event.target.value; dirty()" class="txt-input" placeholder="Column name" />
                  </div>
                </div>
              </Sec>
              <Sec title="Table Styling" icon="fa-solid fa-palette">
                <Row label="Header BG">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.headerBg||'#6366f1'" @input="setS('headerBg',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.headerBg||'#6366f1'" @input="setS('headerBg',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
                <Row label="Even Row">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.evenRowBg||'#ffffff'" @input="setS('evenRowBg',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.evenRowBg||'#ffffff'" @input="setS('evenRowBg',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
                <Row label="Odd Row">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.oddRowBg||'#f8fafc'" @input="setS('oddRowBg',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.oddRowBg||'#f8fafc'" @input="setS('oddRowBg',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── CHARTS ───────────────────────────────────────────────── -->
            <template v-else-if="isChart">
              <Sec title="Chart Type" icon="fa-solid fa-chart-bar">
                <!-- Chart type switcher dropdown -->
                <Row label="Type">
                  <select :value="el.type" @change="$emit('change-chart-type', $event.target.value)" class="sel-input">
                    <option value="bar-chart">Bar Chart</option>
                    <option value="line-chart">Line Chart</option>
                    <option value="area-chart">Area Chart</option>
                    <option value="pie-chart">Pie Chart</option>
                    <option value="doughnut-chart">Doughnut Chart</option>
                    <option value="radar-chart">Radar Chart</option>
                    <option value="polar-chart">Polar Area</option>
                    <option value="scatter-chart">Scatter Plot</option>
                  </select>
                </Row>
                <Row label="Title">
                  <input type="text" :value="el.chartTitle||''" @input="el.chartTitle=$event.target.value; dirty()" class="txt-input" placeholder="Chart title" />
                </Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.chartColor||'#6366f1'" @input="el.chartColor=$event.target.value; dirty()" class="color-pick" />
                    <input type="text" :value="el.chartColor||'#6366f1'" @input="el.chartColor=$event.target.value; dirty()" class="txt-input mono" />
                  </div>
                </Row>
              </Sec>
              <Sec title="Labels & Values" icon="fa-solid fa-list-ol">
                <p class="hint-text">One label/value per line</p>
                <Row label="Labels">
                  <textarea :value="(el.chartData?.labels||[]).join('\n')" @input="setChartLabels($event.target.value)" rows="4" class="txt-area" placeholder="Q1&#10;Q2&#10;Q3&#10;Q4" />
                </Row>
                <Row label="Values">
                  <textarea :value="(el.chartData?.values||[]).join('\n')" @input="setChartValues($event.target.value)" rows="4" class="txt-area" placeholder="25&#10;40&#10;35&#10;55" />
                </Row>
                <!-- Quick data add -->
                <div class="quick-row">
                  <input v-model="newLabel" class="txt-input" placeholder="Label" />
                  <NumIn v-model:value="newValue" :min="-99999" />
                  <button class="btn-accent" @click="addDataPoint">+</button>
                </div>
                <!-- Data points list -->
                <div class="data-points">
                  <div v-for="(lbl,di) in (el.chartData?.labels||[])" :key="di" class="data-pt-row">
                    <input type="text" :value="lbl" @input="updateLabel(di,$event.target.value)" class="txt-input" style="flex:1" />
                    <input type="number" :value="(el.chartData?.values||[])[di]" @input="updateValue(di,+$event.target.value)" class="txt-input" style="width:64px" />
                    <button class="icon-btn-sm danger" @click="removeDataPoint(di)"><i class="fa-solid fa-xmark" /></button>
                  </div>
                </div>
              </Sec>
            </template>

            <!-- ─── METRIC / KPI ──────────────────────────────────────────── -->
            <template v-else-if="el.type==='metric'">
              <Sec title="KPI Card" icon="fa-solid fa-chart-simple">
                <Row label="Value"><input type="text" :value="el.value||''" @input="el.value=$event.target.value; dirty()" class="txt-input" placeholder="$48K" /></Row>
                <Row label="Label"><input type="text" :value="el.label||''" @input="el.label=$event.target.value; dirty()" class="txt-input" placeholder="Revenue" /></Row>
                <Row label="Change"><input type="text" :value="el.change||''" @input="el.change=$event.target.value; dirty()" class="txt-input" placeholder="+12%" /></Row>
                <Row label="Type">
                  <div class="btn-group">
                    <button :class="{ active: el.changeType==='positive' }" @click="el.changeType='positive'; dirty()">▲ Positive</button>
                    <button :class="{ active: el.changeType==='negative' }" @click="el.changeType='negative'; dirty()">▼ Negative</button>
                  </div>
                </Row>
                <Row label="Period"><input type="text" :value="el.changePeriod||''" @input="el.changePeriod=$event.target.value; dirty()" class="txt-input" placeholder="YoY" /></Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── PROGRESS BAR ──────────────────────────────────────────── -->
            <template v-else-if="el.type==='progress'">
              <Sec title="Progress Bar" icon="fa-solid fa-bars-progress">
                <Row label="Label"><input type="text" :value="el.label||''" @input="el.label=$event.target.value; dirty()" class="txt-input" /></Row>
                <Row label="Value">
                  <div class="slider-row">
                    <input type="range" min="0" max="100" :value="el.value||0" @input="el.value=+$event.target.value; dirty()" class="slider" />
                    <span class="val">{{ el.value||0 }}%</span>
                  </div>
                </Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
                <Row label="Track">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.trackColor||'#e2e8f0'" @input="setS('trackColor',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.trackColor||'#e2e8f0'" @input="setS('trackColor',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── CIRCULAR PROGRESS ─────────────────────────────────────── -->
            <template v-else-if="el.type==='circular-progress'">
              <Sec title="Circular Progress" icon="fa-solid fa-circle-notch">
                <Row label="Label"><input type="text" :value="el.label||''" @input="el.label=$event.target.value; dirty()" class="txt-input" /></Row>
                <Row label="Value">
                  <div class="slider-row">
                    <input type="range" min="0" max="100" :value="el.value||0" @input="el.value=+$event.target.value; dirty()" class="slider" />
                    <span class="val">{{ el.value||0 }}%</span>
                  </div>
                </Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── STAT ROW ───────────────────────────────────────────────── -->
            <template v-else-if="el.type==='stat-row'">
              <Sec title="Stats" icon="fa-solid fa-bars-staggered">
                <div v-for="(stat,si) in (el.stats||[])" :key="si" class="stat-edit-row">
                  <input type="text" :value="stat.value" @input="stat.value=$event.target.value; dirty()" class="txt-input" placeholder="Value" style="flex:1" />
                  <input type="text" :value="stat.label" @input="stat.label=$event.target.value; dirty()" class="txt-input" placeholder="Label" style="flex:1" />
                  <button class="icon-btn-sm danger" @click="el.stats.splice(si,1); dirty()"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-full" @click="el.stats=[...(el.stats||[]),{value:'0',label:'Metric'}]; dirty()">+ Add Stat</button>
              </Sec>
            </template>

            <!-- ─── TIMELINE ───────────────────────────────────────────────── -->
            <template v-else-if="el.type==='timeline'">
              <Sec title="Timeline Items" icon="fa-solid fa-timeline">
                <div v-for="(item,ii) in (el.items||[])" :key="ii" class="tl-edit-item">
                  <div class="tl-edit-head">
                    <span class="tl-num">{{ ii+1 }}</span>
                    <button class="icon-btn-sm danger" @click="el.items.splice(ii,1); dirty()"><i class="fa-solid fa-xmark" /></button>
                  </div>
                  <input type="text" :value="item.date" @input="item.date=$event.target.value; dirty()" class="txt-input" placeholder="Date / Period" />
                  <input type="text" :value="item.label" @input="item.label=$event.target.value; dirty()" class="txt-input" placeholder="Title" />
                  <input type="text" :value="item.desc" @input="item.desc=$event.target.value; dirty()" class="txt-input" placeholder="Description" />
                </div>
                <button class="btn-full" @click="el.items=[...(el.items||[]),{date:'',label:'New Item',desc:''}]; dirty()">+ Add Item</button>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── CHECKLIST ──────────────────────────────────────────────── -->
            <template v-else-if="el.type==='checklist'">
              <Sec title="Checklist Items" icon="fa-solid fa-list-check">
                <div class="checklist-actions">
                  <button class="btn-sm-outline" @click="el.items?.forEach(i=>i.checked=true); dirty()">Check All</button>
                  <button class="btn-sm-outline" @click="el.items?.forEach(i=>i.checked=false); dirty()">Uncheck All</button>
                </div>
                <div v-for="(item,ii) in (el.items||[])" :key="ii" class="check-edit-row">
                  <button class="check-tog" :class="{ checked: item.checked }" :style="{ borderColor: el.styles?.color||'#6366f1', background: item.checked ? (el.styles?.color||'#6366f1') : 'transparent' }" @click="item.checked=!item.checked; dirty()">
                    <i v-if="item.checked" class="fa-solid fa-check" style="color:#fff;font-size:8px" />
                  </button>
                  <input type="text" :value="item.text" @input="item.text=$event.target.value; dirty()" class="txt-input" style="flex:1" placeholder="Item text" />
                  <button class="icon-btn-sm danger" @click="el.items.splice(ii,1); dirty()"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-full" @click="el.items=[...(el.items||[]),{text:'New item',checked:false}]; dirty()">+ Add Item</button>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── CALLOUT ────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='callout'">
              <Sec title="Callout Box" icon="fa-solid fa-lightbulb">
                <Row label="Emoji">
                  <input type="text" :value="el.emoji||'💡'" @input="el.emoji=$event.target.value; dirty()" class="txt-input" maxlength="4" style="width:60px" />
                </Row>
                <Row label="Text">
                  <textarea :value="el.content||''" @input="el.content=$event.target.value; dirty()" rows="4" class="txt-area" placeholder="Callout message…" />
                </Row>
                <Row label="BG Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.backgroundColor||'#eff6ff'" @input="setS('backgroundColor',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.backgroundColor||'#eff6ff'" @input="setS('backgroundColor',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
                <Row label="Border">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.borderColor||'#6366f1'" @input="setS('borderColor',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.borderColor||'#6366f1'" @input="setS('borderColor',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── TESTIMONIAL ────────────────────────────────────────────── -->
            <template v-else-if="el.type==='testimonial'">
              <Sec title="Testimonial" icon="fa-solid fa-comment-dots">
                <Row label="Quote"><textarea :value="el.content||''" @input="el.content=$event.target.value; dirty()" rows="4" class="txt-area" /></Row>
                <Row label="Author"><input type="text" :value="el.author||''" @input="el.author=$event.target.value; dirty()" class="txt-input" /></Row>
                <Row label="Role"><input type="text" :value="el.role||''" @input="el.role=$event.target.value; dirty()" class="txt-input" /></Row>
              </Sec>
            </template>

            <!-- ─── SIGNATURE ──────────────────────────────────────────────── -->
            <template v-else-if="el.type==='signature'">
              <Sec title="Signature" icon="fa-solid fa-signature">
                <Row label="Name"><input type="text" :value="el.content||''" @input="el.content=$event.target.value; dirty()" class="txt-input" placeholder="Full Name" /></Row>
                <Row label="Title"><input type="text" :value="el.label||''" @input="el.label=$event.target.value; dirty()" class="txt-input" placeholder="Authorized Signature" /></Row>
              </Sec>
            </template>

            <!-- ─── PRICE CARD ─────────────────────────────────────────────── -->
            <template v-else-if="el.type==='price-card'">
              <Sec title="Price Card" icon="fa-solid fa-tags">
                <Row label="Plan"><input type="text" :value="el.plan||''" @input="el.plan=$event.target.value; dirty()" class="txt-input" placeholder="Basic" /></Row>
                <Row label="Price"><input type="text" :value="el.price||''" @input="el.price=$event.target.value; dirty()" class="txt-input" placeholder="$29" /></Row>
                <Row label="Period"><input type="text" :value="el.period||''" @input="el.period=$event.target.value; dirty()" class="txt-input" placeholder="/month" /></Row>
                <div class="hint-text">Features</div>
                <div v-for="(f,fi) in (el.features||[])" :key="fi" class="feat-row">
                  <input type="text" :value="f" @input="el.features[fi]=$event.target.value; dirty()" class="txt-input" style="flex:1" />
                  <button class="icon-btn-sm danger" @click="el.features.splice(fi,1); dirty()"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-full" @click="el.features=[...(el.features||[]),'New feature']; dirty()">+ Add Feature</button>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── SOCIAL CARD ────────────────────────────────────────────── -->
            <template v-else-if="el.type==='social-card'">
              <Sec title="Social Card" icon="fa-solid fa-id-card">
                <Row label="Avatar"><input type="text" :value="el.avatar||'👤'" @input="el.avatar=$event.target.value; dirty()" class="txt-input" maxlength="4" /></Row>
                <Row label="Name"><input type="text" :value="el.content||''" @input="el.content=$event.target.value; dirty()" class="txt-input" /></Row>
                <Row label="Subtitle"><input type="text" :value="el.subtitle||''" @input="el.subtitle=$event.target.value; dirty()" class="txt-input" /></Row>
              </Sec>
            </template>

            <!-- ─── KANBAN ─────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='kanban'">
              <Sec title="Kanban Card" icon="fa-solid fa-columns">
                <Row label="Status"><input type="text" :value="el.status||''" @input="el.status=$event.target.value; dirty()" class="txt-input" placeholder="In Progress" /></Row>
                <Row label="Title"><input type="text" :value="el.content||''" @input="el.content=$event.target.value; dirty()" class="txt-input" /></Row>
                <Row label="Due Date"><input type="text" :value="el.due||''" @input="el.due=$event.target.value; dirty()" class="txt-input" placeholder="Dec 31, 2024" /></Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── QR CODE ────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='qr-code'">
              <Sec title="QR Code" icon="fa-solid fa-qrcode">
                <Row label="Text/URL"><input type="text" :value="el.qrText||''" @input="el.qrText=$event.target.value; dirty()" class="txt-input" placeholder="https://example.com" /></Row>
              </Sec>
            </template>

            <!-- ─── VIDEO ──────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='video'">
              <Sec title="Video" icon="fa-solid fa-video">
                <Row label="YouTube URL"><input type="text" :value="el.videoUrl||''" @input="el.videoUrl=$event.target.value; dirty()" class="txt-input" placeholder="https://youtube.com/watch?v=…" /></Row>
              </Sec>
            </template>

            <!-- ─── MAP ────────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='map'">
              <Sec title="Map" icon="fa-solid fa-map-location-dot">
                <Row label="Address"><input type="text" :value="el.mapAddress||''" @input="el.mapAddress=$event.target.value; dirty()" class="txt-input" placeholder="New York, NY" /></Row>
              </Sec>
            </template>

            <!-- ─── RATING ─────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='rating'">
              <Sec title="Star Rating" icon="fa-solid fa-star">
                <Row label="Stars (1–5)">
                  <div class="slider-row">
                    <input type="range" min="0" max="5" step="0.5" :value="el.value||4" @input="el.value=+$event.target.value; dirty()" class="slider" />
                    <span class="val">{{ el.value||4 }}</span>
                  </div>
                </Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#f59e0b'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── SPARKLINE ──────────────────────────────────────────────── -->
            <template v-else-if="el.type==='sparkline'">
              <Sec title="Sparkline" icon="fa-solid fa-wave-square">
                <Row label="Data (comma-sep)">
                  <input type="text" :value="(el.sparkData||[]).join(',')" @input="el.sparkData=$event.target.value.split(',').map(v=>+v.trim()).filter(v=>!isNaN(v)); dirty()" class="txt-input" placeholder="3,5,4,8,6,7,5,9" />
                </Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── HTML EMBED ─────────────────────────────────────────────── -->
            <template v-else-if="el.type==='html-embed'">
              <Sec title="HTML Code" icon="fa-solid fa-code">
                <textarea :value="el.content||''" @input="el.content=$event.target.value; dirty()" rows="8" class="txt-area mono" placeholder="<p>Custom HTML…</p>" />
              </Sec>
            </template>

            <!-- ─── ICON ───────────────────────────────────────────────────── -->
            <template v-else-if="el.type==='icon'">
              <Sec title="Emoji / Icon" icon="fa-solid fa-face-smile">
                <Row label="Emoji"><input type="text" :value="el.content||'⭐'" @input="el.content=$event.target.value; dirty()" class="txt-input" maxlength="4" style="width:60px;font-size:20px" /></Row>
                <Row label="Size">
                  <div class="slider-row">
                    <input type="range" min="12" max="120" :value="el.styles?.fontSize||40" @input="setS('fontSize',+$event.target.value)" class="slider" />
                    <span class="val">{{ el.styles?.fontSize||40 }}px</span>
                  </div>
                </Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#6366f1'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── WATERMARK TEXT ─────────────────────────────────────────── -->
            <template v-else-if="el.type==='watermark'">
              <Sec title="Watermark" icon="fa-solid fa-water">
                <Row label="Text"><input type="text" :value="el.content||'CONFIDENTIAL'" @input="el.content=$event.target.value; dirty()" class="txt-input" /></Row>
                <Row label="Opacity">
                  <div class="slider-row">
                    <input type="range" min="1" max="80" :value="el.styles?.opacity||20" @input="setS('opacity',+$event.target.value)" class="slider" />
                    <span class="val">{{ el.styles?.opacity||20 }}%</span>
                  </div>
                </Row>
                <Row label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#94a3b8'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
              </Sec>
            </template>

            <!-- ─── SHAPES ─────────────────────────────────────────────────── -->
            <template v-else-if="isShape">
              <Sec title="Shape" icon="fa-solid fa-shapes">
                <Row label="Fill">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.backgroundColor||'#6366f1'" @input="setS('backgroundColor',$event.target.value)" class="color-pick" />
                    <input type="text" :value="el.styles?.backgroundColor||'#6366f1'" @input="setS('backgroundColor',$event.target.value)" class="txt-input mono" />
                  </div>
                </Row>
                <Row v-if="el.type==='divider'" label="Color">
                  <div class="color-row">
                    <input type="color" :value="el.styles?.color||'#e2e8f0'" @input="setS('color',$event.target.value)" class="color-pick" />
                  </div>
                </Row>
                <Row v-if="el.type==='divider'" label="Thickness">
                  <div class="slider-row">
                    <input type="range" min="1" max="20" :value="el.styles?.borderWidth||2" @input="setS('borderWidth',+$event.target.value)" class="slider" />
                    <span class="val">{{ el.styles?.borderWidth||2 }}px</span>
                  </div>
                </Row>
              </Sec>
            </template>

            <div v-else class="no-props"><i class="fa-solid fa-pen-ruler" /><p>Select an element to edit its content</p></div>

          </div>

          <!-- ══ EFFECTS TAB ═══════════════════════════════════════════════ -->
          <div v-show="activeTab === 'effects'">
            <Sec title="Filters" icon="fa-solid fa-wand-magic-sparkles">
              <Row label="Blur">
                <div class="slider-row">
                  <input type="range" min="0" max="20" :value="el.styles?.blur||0" @input="setS('blur',+$event.target.value)" class="slider" />
                  <span class="val">{{ el.styles?.blur||0 }}px</span>
                </div>
              </Row>
              <Row label="Brightness">
                <div class="slider-row">
                  <input type="range" min="0" max="200" :value="el.styles?.brightness||100" @input="setS('brightness',+$event.target.value)" class="slider" />
                  <span class="val">{{ el.styles?.brightness||100 }}%</span>
                </div>
              </Row>
              <Row label="Contrast">
                <div class="slider-row">
                  <input type="range" min="0" max="200" :value="el.styles?.contrast||100" @input="setS('contrast',+$event.target.value)" class="slider" />
                  <span class="val">{{ el.styles?.contrast||100 }}%</span>
                </div>
              </Row>
              <Row label="Grayscale">
                <div class="slider-row">
                  <input type="range" min="0" max="100" :value="el.styles?.grayscale||0" @input="setS('grayscale',+$event.target.value)" class="slider" />
                  <span class="val">{{ el.styles?.grayscale||0 }}%</span>
                </div>
              </Row>
            </Sec>
            <Sec title="Blend & Transform" icon="fa-solid fa-layer-group">
              <Row label="Blend Mode">
                <select :value="el.styles?.mixBlendMode||'normal'" @change="setS('mixBlendMode',$event.target.value)" class="sel-input">
                  <option v-for="m in BLENDS" :key="m" :value="m">{{ m }}</option>
                </select>
              </Row>
              <Row label="Flip">
                <div class="btn-group">
                  <button :class="{ active: el.styles?.scaleX===-1 }" @click="setS('scaleX', el.styles?.scaleX===-1 ? 1 : -1)">↔ Flip H</button>
                  <button :class="{ active: el.styles?.scaleY===-1 }" @click="setS('scaleY', el.styles?.scaleY===-1 ? 1 : -1)">↕ Flip V</button>
                </div>
              </Row>
            </Sec>
          </div>

          <!-- ══ ARRANGE TAB ═══════════════════════════════════════════════ -->
          <div v-show="activeTab === 'arrange'">
            <Sec title="Layer Order" icon="fa-solid fa-layer-group">
              <div class="arrange-grid">
                <button class="arrange-btn" @click="$emit('bring-front')"><i class="fa-solid fa-angles-up" /><span>To Front</span></button>
                <button class="arrange-btn" @click="$emit('send-back')"><i class="fa-solid fa-angles-down" /><span>To Back</span></button>
              </div>
            </Sec>
            <Sec title="Align to Page" icon="fa-solid fa-align-center">
              <div class="align-grid">
                <button @click="$emit('align','left')" title="Align Left"><i class="fa-solid fa-arrow-left" /></button>
                <button @click="$emit('align','center-h')" title="Center H"><i class="fa-solid fa-arrows-left-right" /></button>
                <button @click="$emit('align','right')" title="Align Right"><i class="fa-solid fa-arrow-right" /></button>
                <button @click="$emit('align','top')" title="Align Top"><i class="fa-solid fa-arrow-up" /></button>
                <button @click="$emit('align','center-v')" title="Center V"><i class="fa-solid fa-arrows-up-down" /></button>
                <button @click="$emit('align','bottom')" title="Align Bottom"><i class="fa-solid fa-arrow-down" /></button>
              </div>
            </Sec>
            <Sec title="Actions" icon="fa-solid fa-bolt">
              <div class="action-grid">
                <button class="action-btn" @click="$emit('duplicate-el')"><i class="fa-solid fa-clone" /> Duplicate</button>
                <button class="action-btn" @click="$emit('copy-el')"><i class="fa-solid fa-copy" /> Copy</button>
                <button class="action-btn" @click="$emit('style-copy')"><i class="fa-solid fa-paintbrush" /> Copy Style</button>
                <button class="action-btn" @click="$emit('style-paste')"><i class="fa-solid fa-brush" /> Paste Style</button>
                <button class="action-btn" @click="$emit('reset-styles')"><i class="fa-solid fa-rotate-left" /> Reset</button>
                <button class="action-btn danger-btn" @click="$emit('delete-el')"><i class="fa-solid fa-trash-can" /> Delete</button>
              </div>
            </Sec>
          </div>

        </div><!-- prop-body -->
      </template>
    </div><!-- panel-body -->
  </aside>
</template>

<script setup>
import { ref, computed, defineComponent, h } from 'vue'

const props = defineProps({
  el:          { type: Object,  default: null },
  settings:    { type: Object,  required: true },
  isCollapsed: { type: Boolean, default: false },
  isDark:      { type: Boolean, default: false },
})

const emit = defineEmits([
  'update:style', 'update:el-prop', 'delete-el', 'duplicate-el', 'copy-el', 'paste-el',
  'lock-el', 'bring-front', 'send-back', 'align', 'reset-styles', 'style-copy', 'style-paste',
  'change-chart-type', 'mark-dirty', 'update:is-collapsed',
])

// ── State ──────────────────────────────────────────────────────────────────────
const activeTab = ref('style')
const newLabel  = ref('')
const newValue  = ref(0)

// ── Inline subcomponents ────────────────────────────────────────────────────────
// Section wrapper
const Sec = defineComponent({
  props: { title: String, icon: String },
  setup(p, { slots }) {
    const open = ref(true)
    return () => h('div', { class: 'prop-sec' }, [
      h('button', { class: 'sec-title', onClick: () => open.value = !open.value }, [
        h('i', { class: p.icon }),
        h('span', p.title),
        h('i', { class: open.value ? 'fa-solid fa-chevron-down sec-chev' : 'fa-solid fa-chevron-right sec-chev' })
      ]),
      open.value ? h('div', { class: 'sec-body' }, slots.default?.()) : null,
    ])
  }
})

// Label + control row
const Row = defineComponent({
  props: { label: String },
  setup(p, { slots }) {
    return () => h('div', { class: 'prop-row' }, [
      p.label ? h('label', { class: 'prop-label' }, p.label) : null,
      h('div', { class: 'prop-control' }, slots.default?.()),
    ])
  }
})

// Field = label + content in grid cell
const Field = defineComponent({
  props: { label: String },
  setup(p, { slots }) {
    return () => h('div', { class: 'prop-field' }, [
      h('label', { class: 'prop-label' }, p.label),
      h('div', {}, slots.default?.()),
    ])
  }
})

// Number input
const NumIn = defineComponent({
  props: { value: Number, min: Number, max: Number },
  emits: ['change'],
  setup(p, { emit: e }) {
    return () => h('input', {
      type: 'number', value: p.value,
      min: p.min, max: p.max,
      class: 'num-input',
      onInput: (ev) => e('change', +ev.target.value),
    })
  }
})

// ── Constants ───────────────────────────────────────────────────────────────────
const PRESETS = ['#6366f1','#8b5cf6','#ec4899','#ef4444','#f59e0b','#10b981','#06b6d4','#3b82f6','#0f172a','#ffffff','#f8fafc','transparent']
const SHADOWS = [
  { name: 'None',   value: 'none' },
  { name: 'Soft',   value: '0 2px 8px rgba(0,0,0,.08)' },
  { name: 'Medium', value: '0 4px 20px rgba(0,0,0,.15)' },
  { name: 'Heavy',  value: '0 8px 40px rgba(0,0,0,.25)' },
  { name: 'Glow',   value: '0 0 20px rgba(99,102,241,.5)' },
  { name: 'Inner',  value: 'inset 0 2px 8px rgba(0,0,0,.1)' },
]
const BLENDS = ['normal','multiply','screen','overlay','darken','lighten','color-dodge','color-burn','difference','exclusion','hue','saturation','color','luminosity']
const FONTS  = [
  { v: "'DM Sans', sans-serif",          l: 'DM Sans' },
  { v: "'Inter', sans-serif",            l: 'Inter' },
  { v: "'Plus Jakarta Sans', sans-serif",l: 'Plus Jakarta Sans' },
  { v: "'Space Grotesk', sans-serif",    l: 'Space Grotesk' },
  { v: "'Sora', sans-serif",             l: 'Sora' },
  { v: "'Outfit', sans-serif",           l: 'Outfit' },
  { v: "'Nunito', sans-serif",           l: 'Nunito' },
  { v: "Georgia, serif",                 l: 'Georgia' },
  { v: "'Playfair Display', serif",      l: 'Playfair Display' },
  { v: "'Times New Roman', serif",       l: 'Times New Roman' },
  { v: "'Fira Code', monospace",         l: 'Fira Code' },
]

const tabs = [
  { id: 'style',   label: 'Style',   icon: 'fa-solid fa-paint-brush' },
  { id: 'text',    label: 'Text',    icon: 'fa-solid fa-font' },
  { id: 'content', label: 'Content', icon: 'fa-solid fa-align-left' },
  { id: 'effects', label: 'Effects', icon: 'fa-solid fa-sparkles' },
  { id: 'arrange', label: 'Arrange', icon: 'fa-solid fa-layer-group' },
]

// ── Computed helpers ─────────────────────────────────────────────────────────
const el      = computed(() => props.el)
const isChart = computed(() => el.value?.type?.endsWith('-chart'))
const isShape = computed(() => ['rectangle','circle','triangle','star','hexagon','divider','arrow'].includes(el.value?.type))
const hasText = computed(() => ['text','heading','subheading','quote','blockquote','highlight','badge','code','link','list','toc','pagenum','date'].includes(el.value?.type))

function typeIcon(type) {
  const m = {
    text:'fa-solid fa-align-left', heading:'fa-solid fa-heading', subheading:'fa-solid fa-h',
    image:'fa-solid fa-image', table:'fa-solid fa-table', metric:'fa-solid fa-chart-simple',
    progress:'fa-solid fa-bars-progress', checklist:'fa-solid fa-list-check', timeline:'fa-solid fa-timeline',
    callout:'fa-solid fa-lightbulb', testimonial:'fa-solid fa-comment-dots', rectangle:'fa-solid fa-square',
    circle:'fa-solid fa-circle', divider:'fa-solid fa-minus', richtext:'fa-solid fa-file-word',
    'bar-chart':'fa-solid fa-chart-bar', 'line-chart':'fa-solid fa-chart-line', 'pie-chart':'fa-solid fa-chart-pie',
    'area-chart':'fa-solid fa-chart-area', 'doughnut-chart':'fa-solid fa-circle-half-stroke',
    'radar-chart':'fa-solid fa-compass', rating:'fa-solid fa-star', qr: 'fa-solid fa-qrcode',
    signature:'fa-solid fa-signature', 'stat-row':'fa-solid fa-bars-staggered',
  }
  return m[type] || 'fa-solid fa-cube'
}

// ── Style mutations ───────────────────────────────────────────────────────────
function setS(prop, val) {
  emit('update:style', prop, val)
}
function updatePos(axis, val) {
  if (el.value?.position) { el.value.position[axis] = val; dirty() }
}
function toggleStyle(prop, on, off) {
  setS(prop, el.value?.styles?.[prop] === on ? off : on)
}
function dirty() { emit('mark-dirty') }

// ── Table helpers ─────────────────────────────────────────────────────────────
function addRow() {
  if (!el.value) return
  const row = {}
  el.value.columns?.forEach(c => { row[c] = '' })
  if (!el.value.data) el.value.data = []
  el.value.data.push(row); dirty()
}
function addCol() {
  if (!el.value) return
  const col = `Col ${(el.value.columns?.length || 0) + 1}`
  if (!el.value.columns) el.value.columns = []
  el.value.columns.push(col)
  el.value.data?.forEach(r => { r[col] = '' }); dirty()
}
function delRow() {
  if (!el.value || (el.value.data?.length || 0) <= 1) return
  el.value.data.pop(); dirty()
}
function delCol() {
  if (!el.value || (el.value.columns?.length || 0) <= 1) return
  const col = el.value.columns.pop()
  el.value.data?.forEach(r => { delete r[col] }); dirty()
}

// ── Chart helpers ─────────────────────────────────────────────────────────────
function setChartLabels(raw) {
  if (!el.value) return
  if (!el.value.chartData) el.value.chartData = { labels: [], values: [] }
  const labs = raw.split('\n').map(s => s.trim()).filter(Boolean)
  el.value.chartData.labels = labs
  // Pad or trim values
  while (el.value.chartData.values.length < labs.length) el.value.chartData.values.push(0)
  el.value.chartData.values = el.value.chartData.values.slice(0, labs.length)
  dirty()
}
function setChartValues(raw) {
  if (!el.value) return
  if (!el.value.chartData) el.value.chartData = { labels: [], values: [] }
  el.value.chartData.values = raw.split('\n').map(s => +s.trim()).filter(v => !isNaN(v))
  dirty()
}
function addDataPoint() {
  if (!el.value || !newLabel.value.trim()) return
  if (!el.value.chartData) el.value.chartData = { labels: [], values: [] }
  el.value.chartData.labels.push(newLabel.value.trim())
  el.value.chartData.values.push(newValue.value)
  newLabel.value = ''; newValue.value = 0; dirty()
}
function removeDataPoint(di) {
  if (!el.value?.chartData) return
  el.value.chartData.labels.splice(di, 1)
  el.value.chartData.values.splice(di, 1); dirty()
}
function updateLabel(di, val) {
  if (!el.value?.chartData) return
  el.value.chartData.labels[di] = val; dirty()
}
function updateValue(di, val) {
  if (!el.value?.chartData) return
  el.value.chartData.values[di] = val; dirty()
}
</script>

<style scoped>
/* ── Panel shell ─────────────────────────────────────────────────────────────── */
.right-panel { width: 280px; flex-shrink: 0; background: var(--bg-panel,#fff); border-left: 1px solid var(--border,#e2e8f0); display: flex; flex-direction: column; overflow: hidden; transition: width .25s ease; position: relative; z-index: 50; }
.right-panel.collapsed { width: 0; border-left: none; }
.panel-toggle { position: absolute; left: -13px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; border-radius: 50%; background: var(--bg-panel,#fff); border: 1px solid var(--border,#e2e8f0); cursor: pointer; color: var(--text-muted,#94a3b8); font-size: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,.08); z-index: 10; transition: all .15s; }
.panel-toggle:hover { color: #6366f1; border-color: #6366f1; }
.panel-body { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* ── No selection ────────────────────────────────────────────────────────────── */
.no-sel { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 24px; text-align: center; color: var(--text-muted,#94a3b8); }
.no-sel-icon { width: 52px; height: 52px; border-radius: 50%; background: var(--bg-secondary,#f8fafc); display: flex; align-items: center; justify-content: center; font-size: 20px; opacity: .5; }
.no-sel h3 { margin: 0; font-size: 12px; font-weight: 700; color: var(--text-primary,#0f172a); }
.no-sel p { margin: 0; font-size: 10px; line-height: 1.5; }

/* ── Element header ──────────────────────────────────────────────────────────── */
.el-header { display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-bottom: 1px solid var(--border,#e2e8f0); flex-shrink: 0; }
.el-type-badge { display: flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 700; color: #6366f1; text-transform: capitalize; }
.el-type-badge i { font-size: 12px; }
.el-header-actions { display: flex; gap: 2px; }
.el-header-actions button { width: 24px; height: 24px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; background: transparent; cursor: pointer; color: var(--text-secondary,#475569); font-size: 11px; display: flex; align-items: center; justify-content: center; transition: all .12s; }
.el-header-actions button:hover { background: var(--bg-secondary,#f8fafc); }
.el-header-actions button.active { background: rgba(245,158,11,.1); color: #f59e0b; }
.el-header-actions button.danger-btn:hover { background: rgba(239,68,68,.08); color: #ef4444; }

/* ── Tabs ────────────────────────────────────────────────────────────────────── */
.prop-tabs { display: flex; border-bottom: 1px solid var(--border,#e2e8f0); flex-shrink: 0; }
.prop-tab { flex: 1; padding: 7px 2px; border: none; background: transparent; cursor: pointer; color: var(--text-muted,#94a3b8); font-size: 8px; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 2px; transition: all .15s; border-bottom: 2px solid transparent; margin-bottom: -1px; font-family: inherit; }
.prop-tab i { font-size: 11px; }
.prop-tab:hover { color: var(--text-secondary,#475569); background: var(--bg-secondary,#f8fafc); }
.prop-tab.active { color: #6366f1; border-bottom-color: #6366f1; }
.prop-body { flex: 1; overflow-y: auto; padding: 4px; scrollbar-width: thin; }

/* ── Sections ────────────────────────────────────────────────────────────────── */
.prop-sec { margin-bottom: 3px; border: 1px solid var(--border-light,#f1f5f9); border-radius: 7px; overflow: hidden; }
.sec-title { display: flex; align-items: center; gap: 5px; width: 100%; padding: 7px 9px; background: var(--bg-secondary,#f8fafc); border: none; cursor: pointer; font-size: 9px; font-weight: 700; color: var(--text-secondary,#475569); text-transform: uppercase; letter-spacing: .06em; transition: background .1s; font-family: inherit; text-align: left; }
.sec-title:hover { background: var(--bg-tertiary,#f1f5f9); }
.sec-title i { font-size: 11px; color: #6366f1; }
.sec-chev { margin-left: auto; font-size: 8px !important; color: var(--text-muted,#94a3b8) !important; }
.sec-body { padding: 8px 9px; display: flex; flex-direction: column; gap: 5px; }

/* ── Rows and fields ─────────────────────────────────────────────────────────── */
.prop-row { display: flex; align-items: center; gap: 6px; }
.prop-label { font-size: 9px; font-weight: 600; color: var(--text-secondary,#475569); width: 56px; flex-shrink: 0; }
.prop-control { flex: 1; min-width: 0; }
.prop-field { display: flex; flex-direction: column; gap: 2px; }
.prop-field .prop-label { width: auto; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
.grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 4px; }

/* ── Input controls ──────────────────────────────────────────────────────────── */
.txt-input { width: 100%; padding: 4px 6px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; background: var(--bg-secondary,#f8fafc); color: var(--text-primary,#0f172a); font-size: 10px; outline: none; box-sizing: border-box; font-family: inherit; transition: border-color .15s; }
.txt-input:focus { border-color: #6366f1; }
.txt-input.mono { font-family: 'Courier New', monospace; }
.num-input { width: 100%; padding: 4px 5px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; background: var(--bg-secondary,#f8fafc); color: var(--text-primary,#0f172a); font-size: 10px; outline: none; text-align: center; font-family: inherit; }
.sel-input { width: 100%; padding: 4px 6px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; background: var(--bg-secondary,#f8fafc); color: var(--text-primary,#0f172a); font-size: 10px; outline: none; cursor: pointer; font-family: inherit; }
.txt-area { width: 100%; padding: 6px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; background: var(--bg-secondary,#f8fafc); color: var(--text-primary,#0f172a); font-size: 10px; outline: none; resize: vertical; font-family: inherit; box-sizing: border-box; line-height: 1.5; }
.txt-area.mono { font-family: 'Fira Code', monospace; }
.txt-area:focus { border-color: #6366f1; }
.slider { width: 100%; accent-color: #6366f1; cursor: pointer; height: 4px; }
.slider-row { display: flex; align-items: center; gap: 6px; flex: 1; }
.val { font-size: 9px; color: var(--text-muted,#94a3b8); font-weight: 600; min-width: 34px; text-align: right; white-space: nowrap; }
.color-pick { width: 26px; height: 26px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; cursor: pointer; padding: 1px; background: transparent; flex-shrink: 0; }
.color-row { display: flex; align-items: center; gap: 4px; flex: 1; }
.w-full { width: 100%; }

/* ── Color presets ───────────────────────────────────────────────────────────── */
.color-presets { display: flex; flex-wrap: wrap; gap: 3px; }
.color-dot { width: 17px; height: 17px; border-radius: 50%; border: 2px solid var(--border,#e2e8f0); cursor: pointer; transition: all .15s; }
.color-dot:hover { transform: scale(1.2); border-color: var(--text-primary,#0f172a); }

/* ── Shadow presets ──────────────────────────────────────────────────────────── */
.shadow-presets { display: grid; grid-template-columns: repeat(3,1fr); gap: 4px; margin-bottom: 6px; }
.shadow-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 3px; border: 1px solid var(--border,#e2e8f0); border-radius: 6px; background: transparent; cursor: pointer; transition: all .15s; font-family: inherit; }
.shadow-btn:hover { border-color: #6366f1; }
.shadow-btn.active { border-color: #6366f1; background: rgba(99,102,241,.06); }
.shadow-preview { width: 28px; height: 18px; background: #fff; border-radius: 3px; }
.shadow-btn span { font-size: 8px; color: var(--text-muted,#94a3b8); }

/* ── Button groups ───────────────────────────────────────────────────────────── */
.btn-group { display: flex; gap: 2px; }
.btn-group button { flex: 1; padding: 4px 6px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; background: var(--bg-secondary,#f8fafc); cursor: pointer; font-size: 10px; font-weight: 500; color: var(--text-secondary,#475569); transition: all .12s; font-family: inherit; }
.btn-group button:hover { border-color: #6366f1; color: #6366f1; }
.btn-group button.active { background: rgba(99,102,241,.1); color: #6366f1; border-color: #6366f1; font-weight: 700; }

/* ── Icon buttons ────────────────────────────────────────────────────────────── */
.icon-btn-sm { width: 22px; height: 22px; border: 1px solid var(--border,#e2e8f0); border-radius: 4px; background: transparent; cursor: pointer; color: var(--text-muted,#94a3b8); font-size: 10px; display: flex; align-items: center; justify-content: center; transition: all .12s; flex-shrink: 0; }
.icon-btn-sm:hover { background: var(--bg-secondary,#f8fafc); color: var(--text-primary,#0f172a); }
.icon-btn-sm.danger:hover { background: rgba(239,68,68,.08); color: #ef4444; }

/* ── Table editor ────────────────────────────────────────────────────────────── */
.tbl-meta { display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: var(--text-muted,#94a3b8); margin-bottom: 5px; }
.tbl-col-editor { display: flex; flex-direction: column; gap: 3px; max-height: 140px; overflow-y: auto; }
.tbl-col-row { display: flex; align-items: center; gap: 4px; }
.tbl-col-idx { width: 16px; font-size: 9px; color: var(--text-muted,#94a3b8); text-align: center; flex-shrink: 0; }

/* ── Chart data editor ───────────────────────────────────────────────────────── */
.hint-text { font-size: 9px; color: var(--text-muted,#94a3b8); margin-bottom: 2px; }
.data-points { display: flex; flex-direction: column; gap: 3px; max-height: 160px; overflow-y: auto; }
.data-pt-row { display: flex; align-items: center; gap: 3px; }
.quick-row { display: flex; align-items: center; gap: 4px; margin-top: 4px; }

/* ── Buttons ─────────────────────────────────────────────────────────────────── */
.btn-full { width: 100%; padding: 6px; border: 1px dashed var(--border,#e2e8f0); border-radius: 5px; background: transparent; cursor: pointer; font-size: 10px; color: var(--text-muted,#94a3b8); transition: all .15s; font-family: inherit; }
.btn-full:hover { border-color: #6366f1; color: #6366f1; background: rgba(99,102,241,.04); }
.btn-accent { padding: 4px 10px; border: none; background: #6366f1; color: #fff; border-radius: 5px; cursor: pointer; font-size: 11px; font-weight: 700; font-family: inherit; transition: all .15s; }
.btn-accent:hover { background: #4f46e5; }
.btn-sm-outline { padding: 3px 8px; border: 1px solid var(--border,#e2e8f0); background: transparent; border-radius: 4px; cursor: pointer; font-size: 9px; font-weight: 600; color: var(--text-secondary,#475569); font-family: inherit; }

/* ── Specific editors ────────────────────────────────────────────────────────── */
.stat-edit-row, .feat-row { display: flex; align-items: center; gap: 3px; }
.checklist-actions { display: flex; gap: 4px; margin-bottom: 6px; }
.check-edit-row { display: flex; align-items: center; gap: 5px; }
.check-tog { width: 16px; height: 16px; border: 2px solid; border-radius: 3px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .15s; }
.tl-edit-item { border: 1px solid var(--border,#e2e8f0); border-radius: 6px; padding: 6px; display: flex; flex-direction: column; gap: 3px; }
.tl-edit-head { display: flex; align-items: center; justify-content: space-between; }
.tl-num { font-size: 9px; font-weight: 700; color: #6366f1; }

/* ── Arrange ─────────────────────────────────────────────────────────────────── */
.arrange-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
.arrange-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 8px; border: 1px solid var(--border,#e2e8f0); border-radius: 6px; background: transparent; cursor: pointer; font-size: 9px; color: var(--text-secondary,#475569); font-family: inherit; transition: all .15s; }
.arrange-btn:hover { border-color: #6366f1; color: #6366f1; background: rgba(99,102,241,.04); }
.arrange-btn i { font-size: 14px; }
.align-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 4px; }
.align-grid button { padding: 8px; border: 1px solid var(--border,#e2e8f0); border-radius: 6px; background: transparent; cursor: pointer; color: var(--text-secondary,#475569); font-size: 13px; transition: all .15s; }
.align-grid button:hover { border-color: #6366f1; color: #6366f1; background: rgba(99,102,241,.04); }
.action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3px; }
.action-btn { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 6px; border: 1px solid var(--border,#e2e8f0); border-radius: 5px; background: transparent; cursor: pointer; font-size: 9px; font-weight: 600; color: var(--text-secondary,#475569); font-family: inherit; transition: all .12s; }
.action-btn:hover { background: var(--bg-secondary,#f8fafc); }
.action-btn.danger-btn { color: #ef4444; }
.action-btn.danger-btn:hover { background: rgba(239,68,68,.06); border-color: rgba(239,68,68,.3); }

/* ── No props placeholder ────────────────────────────────────────────────────── */
.no-props { padding: 24px 12px; text-align: center; color: var(--text-muted,#94a3b8); display: flex; flex-direction: column; align-items: center; gap: 8px; }
.no-props i { font-size: 24px; opacity: .3; }
.no-props p { font-size: 10px; }

/* ── Responsive ──────────────────────────────────────────────────────────────── */
@media (max-width:768px) {
  .right-panel { position: fixed; right: 0; top: 48px; bottom: 0; z-index: 150; box-shadow: -8px 0 32px rgba(0,0,0,.15); }
  .right-panel.collapsed { width: 0; box-shadow: none; }
}
</style>