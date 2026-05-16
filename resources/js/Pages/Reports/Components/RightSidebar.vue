<template>
  <aside class="right-panel" :class="{ collapsed: isCollapsed, dark: isDark }">
    <button class="panel-toggle" @click="$emit('update:is-collapsed', !isCollapsed)" :title="isCollapsed ? 'Expand' : 'Collapse'">
      <i :class="isCollapsed ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'" />
    </button>

    <div v-if="!isCollapsed" class="panel-body">
      <!-- NO SELECTION -->
      <div v-if="!selectedEl" class="no-selection">
        <div class="no-sel-icon"><i class="fa-solid fa-hand-pointer" /></div>
        <h3>No Element Selected</h3>
        <p>Click any element on the canvas to edit its properties</p>
        <div v-if="currentPageElements?.length" class="quick-stats">
          <div class="stat-chip"><i class="fa-solid fa-cubes" /> {{ currentPageElements.length }} elements</div>
          <div v-if="selectedElsCount > 0" class="stat-chip accent"><i class="fa-solid fa-check-square" /> {{ selectedElsCount }} selected</div>
        </div>
      </div>

      <!-- ELEMENT PROPERTIES -->
      <div v-else class="props-wrap">
        <!-- Header -->
        <div class="props-header">
          <div class="el-identity">
            <i :class="getElIcon(selectedEl.type)" class="el-type-icon" />
            <div>
              <span class="el-type-name">{{ selectedEl.type }}</span>
              <span class="el-id">{{ selectedEl.id?.slice(0, 8) }}</span>
            </div>
          </div>
          <div class="header-actions">
            <button @click="$emit('duplicate-el')" title="Duplicate"><i class="fa-solid fa-clone" /></button>
            <button @click="$emit('lock-el')" :class="{ active: selectedEl.locked }" title="Lock">
              <i :class="selectedEl.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'" />
            </button>
            <button @click="$emit('delete-el')" class="danger" title="Delete"><i class="fa-solid fa-trash-can" /></button>
          </div>
        </div>

        <!-- Property Tabs -->
        <div class="prop-tabs">
          <button v-for="t in propTabs" :key="t.id" class="ptab" :class="{ active: activeTab === t.id }" @click="activeTab = t.id">
            <i :class="t.icon" /><span>{{ t.label }}</span>
          </button>
        </div>

        <div class="prop-body">

          <!-- ══ STYLE ══ -->
          <div v-show="activeTab === 'style'">
            <!-- Position & Size -->
            <div class="prop-section">
              <div class="section-hdr" @click="toggle('pos')">
                <i class="fa-solid fa-arrows-up-down-left-right section-icon" />Position & Size
                <i :class="open.pos ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="ml-auto text-xs" />
              </div>
              <div v-if="open.pos" class="section-body">
                <div class="grid-4">
                  <div class="fn"><label>X</label><input type="number" :value="Math.round(selectedEl.position?.x||0)" @input="updatePos('x',+$event.target.value)" class="prop-input center" /></div>
                  <div class="fn"><label>Y</label><input type="number" :value="Math.round(selectedEl.position?.y||0)" @input="updatePos('y',+$event.target.value)" class="prop-input center" /></div>
                  <div class="fn"><label>W</label><input type="number" :value="Math.round(selectedEl.styles?.width||100)" @input="ss('width',+$event.target.value)" class="prop-input center" min="10" /></div>
                  <div class="fn"><label>H</label><input type="number" :value="Math.round(selectedEl.styles?.height||100)" @input="ss('height',+$event.target.value)" class="prop-input center" min="10" /></div>
                </div>
                <div class="prop-row"><span class="prop-lbl">Rotation</span><div class="slider-row"><input type="range" min="-180" max="180" :value="selectedEl.styles?.rotate||0" @input="ss('rotate',+$event.target.value)" class="slider" /><span class="val">{{ Math.round(selectedEl.styles?.rotate||0) }}°</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Z-Index</span><input type="number" :value="selectedEl.styles?.zIndex||1" @input="ss('zIndex',+$event.target.value)" class="prop-input sm" min="0" max="9999" /></div>
                <div class="prop-row"><span class="prop-lbl">Lock Ratio</span><toggle-sw :value="!!selectedEl.styles?.lockAspect" @toggle="ss('lockAspect',!selectedEl.styles?.lockAspect)" /></div>
                <div class="prop-row" v-if="totalPages>1"><span class="prop-lbl">Move to Page</span>
                  <select class="prop-select" @change="$emit('move-element-to-page',{elementIdx:selectedElIdx,fromPage:currentPage,toPage:+$event.target.value})">
                    <option value="">— page —</option>
                    <option v-for="pi in totalPages" :key="pi-1" :value="pi-1" :disabled="pi-1===currentPage">Page {{ pi }}</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Fill -->
            <div class="prop-section">
              <div class="section-hdr" @click="toggle('fill')"><i class="fa-solid fa-palette section-icon" />Fill & Color<i :class="open.fill?'fa-solid fa-chevron-down':'fa-solid fa-chevron-right'" class="ml-auto text-xs" /></div>
              <div v-if="open.fill" class="section-body">
                <div class="prop-row"><span class="prop-lbl">Background</span>
                  <div class="color-row">
                    <input type="color" :value="cleanColor(selectedEl.styles?.backgroundColor)" @input="ss('backgroundColor',$event.target.value)" class="color-input" />
                    <input type="text" :value="selectedEl.styles?.backgroundColor||'transparent'" @input="ss('backgroundColor',$event.target.value)" class="prop-input mono sm" />
                    <button class="mini-btn" @click="ss('backgroundColor','transparent')" title="No fill"><i class="fa-solid fa-ban" /></button>
                  </div>
                </div>
                <div class="prop-row"><span class="prop-lbl">Opacity</span><div class="slider-row"><input type="range" min="0" max="100" :value="selectedEl.styles?.opacity??100" @input="ss('opacity',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.opacity??100 }}%</span></div></div>
                <div class="color-presets">
                  <button v-for="c in colorPresets" :key="c" class="color-dot" :style="{background:c}" @click="ss('backgroundColor',c)" :title="c" />
                </div>
              </div>
            </div>

            <!-- Border -->
            <div class="prop-section">
              <div class="section-hdr" @click="toggle('border')"><i class="fa-solid fa-border-all section-icon" />Border<i :class="open.border?'fa-solid fa-chevron-down':'fa-solid fa-chevron-right'" class="ml-auto text-xs" /></div>
              <div v-if="open.border" class="section-body">
                <div class="grid-2">
                  <div class="fn"><label>Width</label><input type="number" :value="selectedEl.styles?.borderWidth||0" @input="ss('borderWidth',+$event.target.value)" class="prop-input center" min="0" max="20" /></div>
                  <div class="fn"><label>Style</label>
                    <select :value="selectedEl.styles?.borderStyle||'solid'" @change="ss('borderStyle',$event.target.value)" class="prop-select">
                      <option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option><option value="double">Double</option><option value="none">None</option>
                    </select>
                  </div>
                </div>
                <div class="prop-row" v-if="selectedEl.styles?.borderWidth>0"><span class="prop-lbl">Color</span>
                  <div class="color-row"><input type="color" :value="selectedEl.styles?.borderColor||'#000'" @input="ss('borderColor',$event.target.value)" class="color-input" /><input type="text" :value="selectedEl.styles?.borderColor||'#000'" @input="ss('borderColor',$event.target.value)" class="prop-input mono sm" /></div>
                </div>
                <div class="prop-row"><span class="prop-lbl">Radius</span><div class="slider-row"><input type="range" min="0" max="200" :value="selectedEl.styles?.borderRadius||0" @input="ss('borderRadius',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.borderRadius||0 }}px</span></div></div>
              </div>
            </div>

            <!-- Shadow -->
            <div class="prop-section">
              <div class="section-hdr" @click="toggle('shadow')"><i class="fa-solid fa-layer-group section-icon" />Shadow<i :class="open.shadow?'fa-solid fa-chevron-down':'fa-solid fa-chevron-right'" class="ml-auto text-xs" /></div>
              <div v-if="open.shadow" class="section-body">
                <div class="shadow-grid">
                  <button v-for="sp in shadowPresets" :key="sp.name" class="shadow-btn" :class="{active:selectedEl.styles?.boxShadow===sp.value}" @click="ss('boxShadow',sp.value)">
                    <div class="shadow-box" :style="{boxShadow:sp.value}" /><span>{{ sp.name }}</span>
                  </button>
                </div>
                <div class="prop-row"><span class="prop-lbl">Custom</span><input type="text" :value="selectedEl.styles?.boxShadow||''" @input="ss('boxShadow',$event.target.value)" class="prop-input" placeholder="0 4px 20px rgba(0,0,0,0.15)" /></div>
              </div>
            </div>

            <!-- Priority Stripe -->
            <div class="prop-section">
              <div class="section-hdr" @click="toggle('priority')"><i class="fa-solid fa-flag section-icon" />Priority Stripe<i :class="open.priority?'fa-solid fa-chevron-down':'fa-solid fa-chevron-right'" class="ml-auto text-xs" /></div>
              <div v-if="open.priority" class="section-body">
                <div class="priority-row">
                  <button v-for="p in priorities" :key="p.val" class="pri-btn" :class="{active:(selectedEl.styles?.priority||'none')===p.val}" @click="ss('priority',p.val==='none'?undefined:p.val)">
                    <span class="pri-dot" :style="{background:p.color}" />{{ p.label }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- ══ TYPOGRAPHY ══ -->
          <div v-show="activeTab === 'typography'">
            <div v-if="isTextEl(selectedEl.type)">
              <div class="prop-section">
                <div class="section-hdr" @click="toggle('font')"><i class="fa-solid fa-font section-icon" />Font<i :class="open.font?'fa-solid fa-chevron-down':'fa-solid fa-chevron-right'" class="ml-auto text-xs" /></div>
                <div v-if="open.font" class="section-body">
                  <div class="prop-row"><span class="prop-lbl">Family</span>
                    <select :value="selectedEl.styles?.fontFamily||''" @change="ss('fontFamily',$event.target.value)" class="prop-select">
                      <option value="">Inherit</option>
                      <option v-for="f in fontList" :key="f.value" :value="f.value">{{ f.label }}</option>
                    </select>
                  </div>
                  <div class="grid-2">
                    <div class="fn"><label>Size (px)</label><input type="number" :value="selectedEl.styles?.fontSize||14" @input="ss('fontSize',+$event.target.value)" class="prop-input center" min="6" max="300" /></div>
                    <div class="fn"><label>Weight</label>
                      <select :value="selectedEl.styles?.fontWeight||'400'" @change="ss('fontWeight',$event.target.value)" class="prop-select">
                        <option value="300">Light</option><option value="400">Regular</option><option value="500">Medium</option><option value="600">SemiBold</option><option value="700">Bold</option><option value="800">ExtraBold</option><option value="900">Black</option>
                      </select>
                    </div>
                  </div>
                  <div class="prop-row"><span class="prop-lbl">Color</span>
                    <div class="color-row"><input type="color" :value="selectedEl.styles?.color||'#000'" @input="ss('color',$event.target.value)" class="color-input" /><input type="text" :value="selectedEl.styles?.color||'#000'" @input="ss('color',$event.target.value)" class="prop-input mono sm" /></div>
                  </div>
                  <div class="prop-row"><span class="prop-lbl">Style</span>
                    <div class="fmt-btns">
                      <button :class="{active:selectedEl.styles?.fontStyle==='italic'}" @click="toggleFmt('fontStyle','italic','normal')"><i>I</i></button>
                      <button :class="{active:selectedEl.styles?.textDecoration==='underline'}" @click="toggleFmt('textDecoration','underline','none')"><u>U</u></button>
                      <button :class="{active:selectedEl.styles?.textDecoration==='line-through'}" @click="toggleFmt('textDecoration','line-through','none')"><s>S</s></button>
                    </div>
                  </div>
                  <div class="prop-row"><span class="prop-lbl">Align</span>
                    <div class="align-btns">
                      <button v-for="a in ['left','center','right','justify']" :key="a" :class="{active:selectedEl.styles?.textAlign===a}" @click="ss('textAlign',a)"><i :class="`fa-solid fa-align-${a}`" /></button>
                    </div>
                  </div>
                  <div class="prop-row"><span class="prop-lbl">Transform</span>
                    <select :value="selectedEl.styles?.textTransform||'none'" @change="ss('textTransform',$event.target.value)" class="prop-select">
                      <option value="none">None</option><option value="uppercase">UPPERCASE</option><option value="lowercase">lowercase</option><option value="capitalize">Capitalize</option>
                    </select>
                  </div>
                  <div class="prop-row"><span class="prop-lbl">Line Height</span><div class="slider-row"><input type="range" min="1" max="3" step="0.05" :value="selectedEl.styles?.lineHeight||1.6" @input="ss('lineHeight',+$event.target.value)" class="slider" /><span class="val">{{ (+( selectedEl.styles?.lineHeight||1.6)).toFixed(2) }}</span></div></div>
                  <div class="prop-row"><span class="prop-lbl">Letter Spacing</span><div class="slider-row"><input type="range" min="-3" max="20" step="0.5" :value="selectedEl.styles?.letterSpacing||0" @input="ss('letterSpacing',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.letterSpacing||0 }}px</span></div></div>
                  <div class="prop-row"><span class="prop-lbl">Padding</span><input type="number" :value="selectedEl.styles?.padding||0" @input="ss('padding',+$event.target.value)" class="prop-input sm" min="0" max="80" /></div>
                </div>
              </div>
            </div>
            <div v-else class="no-props"><i class="fa-solid fa-font" /><p>Typography applies to text elements only</p></div>
          </div>

          <!-- ══ CONTENT ══ -->
          <div v-show="activeTab === 'content'">
            <!-- Text -->
            <div v-if="isTextEl(selectedEl.type)" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-align-left section-icon" />Text Content</div>
              <div class="section-body"><textarea :value="stripHtml(selectedEl.content||'')" @input="$emit('update:content',$event.target.value)" class="content-ta" rows="6" placeholder="Enter content…" /></div>
            </div>

            <!-- Image -->
            <div v-if="selectedEl.type==='image'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-image section-icon" />Image</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">URL</span><input type="text" :value="selectedEl.src||''" @input="eup('src',$event.target.value)" class="prop-input" placeholder="https://…" /></div>
                <div class="prop-row"><span class="prop-lbl">Alt</span><input type="text" :value="selectedEl.alt||''" @input="eup('alt',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Fit</span>
                  <select :value="selectedEl.styles?.objectFit||'cover'" @change="ss('objectFit',$event.target.value)" class="prop-select">
                    <option value="cover">Cover</option><option value="contain">Contain</option><option value="fill">Fill</option><option value="scale-down">Scale Down</option><option value="none">None</option>
                  </select>
                </div>
                <button class="btn-sec full-w mt-2" @click="$emit('image-replace',selectedEl)"><i class="fa-solid fa-upload" /> Replace Image</button>
              </div>
            </div>

            <!-- Table -->
            <div v-if="selectedEl.type==='table'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-table section-icon" />Table</div>
              <div class="section-body">
                <div class="table-info">{{ selectedEl.columns?.length||0 }} cols × {{ selectedEl.data?.length||0 }} rows</div>
                <div class="grid-2">
                  <button class="btn-sec" @click="$emit('add-table-row')">+ Row</button>
                  <button class="btn-sec" @click="$emit('add-table-col')">+ Col</button>
                  <button class="btn-sec" @click="$emit('remove-table-row')" :disabled="selectedEl.data?.length<=1">− Row</button>
                  <button class="btn-sec" @click="$emit('remove-table-col')" :disabled="selectedEl.columns?.length<=1">− Col</button>
                </div>
                <div class="prop-row"><span class="prop-lbl">Header Bg</span><input type="color" :value="selectedEl.styles?.headerBg||settings?.primary_color||'#6366f1'" @input="ss('headerBg',$event.target.value)" class="color-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Even Row</span><input type="color" :value="selectedEl.styles?.evenRowBg||'#ffffff'" @input="ss('evenRowBg',$event.target.value)" class="color-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Odd Row</span><input type="color" :value="selectedEl.styles?.oddRowBg||'#f8fafc'" @input="ss('oddRowBg',$event.target.value)" class="color-input" /></div>
              </div>
            </div>

            <!-- Chart -->
            <div v-if="isChartEl(selectedEl.type)" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-chart-bar section-icon" />Chart Data</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Title</span><input type="text" :value="selectedEl.chartTitle||''" @input="eup('chartTitle',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Labels</span><input type="text" :value="(selectedEl.chartData?.labels||[]).join(', ')" @input="$emit('set-chart-labels',$event.target.value.split(',').map(s=>s.trim()))" class="prop-input" placeholder="Q1, Q2, Q3, Q4" /></div>
                <div class="prop-row"><span class="prop-lbl">Values</span><input type="text" :value="(selectedEl.chartData?.values||[]).join(', ')" @input="$emit('set-chart-values',$event.target.value.split(',').map(s=>+s.trim()).filter(v=>!isNaN(v)))" class="prop-input" placeholder="25, 40, 35, 55" /></div>
                <div class="prop-row"><span class="prop-lbl">Color</span><input type="color" :value="selectedEl.chartColor||settings?.primary_color||'#6366f1'" @input="eup('chartColor',$event.target.value)" class="color-input" /></div>
              </div>
            </div>

            <!-- Metric -->
            <div v-if="selectedEl.type==='metric'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-chart-simple section-icon" />KPI Card</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Value</span><input type="text" :value="selectedEl.value||''" @input="eup('value',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Label</span><input type="text" :value="selectedEl.label||''" @input="eup('label',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Change</span><input type="text" :value="selectedEl.change||''" @input="eup('change',$event.target.value)" class="prop-input" placeholder="+12.5%" /></div>
                <div class="prop-row"><span class="prop-lbl">Period</span><input type="text" :value="selectedEl.changePeriod||''" @input="eup('changePeriod',$event.target.value)" class="prop-input" placeholder="vs last month" /></div>
                <div class="prop-row"><span class="prop-lbl">Direction</span>
                  <select :value="selectedEl.changeType||'positive'" @change="eup('changeType',$event.target.value)" class="prop-select">
                    <option value="positive">Positive ↑</option><option value="negative">Negative ↓</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Progress -->
            <div v-if="selectedEl.type==='progress'||selectedEl.type==='circular-progress'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-bars-progress section-icon" />Progress</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Label</span><input type="text" :value="selectedEl.label||''" @input="eup('label',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Value %</span><div class="slider-row"><input type="range" min="0" max="100" :value="selectedEl.value||0" @input="eup('value',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.value||0 }}%</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Fill Color</span><input type="color" :value="selectedEl.styles?.color||settings?.primary_color||'#6366f1'" @input="ss('color',$event.target.value)" class="color-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Track</span><input type="color" :value="selectedEl.styles?.trackColor||'#e2e8f0'" @input="ss('trackColor',$event.target.value)" class="color-input" /></div>
              </div>
            </div>

            <!-- Timeline -->
            <div v-if="selectedEl.type==='timeline'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-timeline section-icon" />Timeline</div>
              <div class="section-body">
                <div v-for="(item,ti) in (selectedEl.items||[])" :key="ti" class="tl-edit">
                  <input type="text" :value="item.date" @input="item.date=$event.target.value;$emit('mark-dirty')" placeholder="Date" class="prop-input" />
                  <input type="text" :value="item.label" @input="item.label=$event.target.value;$emit('mark-dirty')" placeholder="Title" class="prop-input" />
                  <input type="text" :value="item.desc" @input="item.desc=$event.target.value;$emit('mark-dirty')" placeholder="Desc" class="prop-input" />
                  <button class="mini-btn danger" @click="$emit('remove-timeline-item',ti)"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-sec full-w" @click="$emit('add-timeline-item')">+ Add Item</button>
              </div>
            </div>

            <!-- Checklist -->
            <div v-if="selectedEl.type==='checklist'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-list-check section-icon" />Checklist</div>
              <div class="section-body">
                <div v-for="(item,ci) in (selectedEl.items||[])" :key="ci" class="list-row">
                  <input type="text" :value="item.text" @input="item.text=$event.target.value;$emit('mark-dirty')" class="prop-input" />
                  <button class="mini-btn danger" @click="$emit('remove-checklist-item',ci)"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-sec full-w" @click="$emit('add-checklist-item')">+ Add Item</button>
              </div>
            </div>

            <!-- Stat Row -->
            <div v-if="selectedEl.type==='stat-row'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-bars-staggered section-icon" />Statistics</div>
              <div class="section-body">
                <div v-for="(stat,si) in (selectedEl.stats||[])" :key="si" class="list-row">
                  <input type="text" :value="stat.value" @input="stat.value=$event.target.value;$emit('mark-dirty')" placeholder="Value" class="prop-input sm" />
                  <input type="text" :value="stat.label" @input="stat.label=$event.target.value;$emit('mark-dirty')" placeholder="Label" class="prop-input sm" />
                  <button class="mini-btn danger" @click="$emit('remove-stat-item',si)"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-sec full-w" @click="$emit('add-stat-item')">+ Add Stat</button>
              </div>
            </div>

            <!-- Rating -->
            <div v-if="selectedEl.type==='rating'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-star section-icon" />Rating</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Stars</span><div class="slider-row"><input type="range" min="0" max="5" step="0.5" :value="selectedEl.value||0" @input="eup('value',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.value||0 }}</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Color</span><input type="color" :value="selectedEl.styles?.color||'#f59e0b'" @input="ss('color',$event.target.value)" class="color-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Size</span><input type="number" :value="selectedEl.styles?.fontSize||22" @input="ss('fontSize',+$event.target.value)" class="prop-input sm" min="12" max="80" /></div>
              </div>
            </div>

            <!-- QR Code -->
            <div v-if="selectedEl.type==='qr-code'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-qrcode section-icon" />QR Code</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">URL/Text</span><input type="text" :value="selectedEl.qrText||''" @input="eup('qrText',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Size (px)</span><input type="number" :value="selectedEl.qrSize||150" @input="eup('qrSize',+$event.target.value)" class="prop-input sm" min="50" max="500" /></div>
                <button class="btn-sec full-w" @click="generateQR">Generate QR</button>
              </div>
            </div>

            <!-- Video -->
            <div v-if="selectedEl.type==='video'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-video section-icon" />Video</div>
              <div class="section-body"><div class="prop-row"><span class="prop-lbl">YouTube URL</span><input type="text" :value="selectedEl.videoUrl||''" @input="eup('videoUrl',$event.target.value)" class="prop-input" placeholder="https://youtube.com/watch?v=…" /></div></div>
            </div>

            <!-- Map -->
            <div v-if="selectedEl.type==='map'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-map section-icon" />Map</div>
              <div class="section-body"><div class="prop-row"><span class="prop-lbl">Address</span><input type="text" :value="selectedEl.mapAddress||''" @input="eup('mapAddress',$event.target.value)" class="prop-input" placeholder="New York, NY" /></div></div>
            </div>

            <!-- Sparkline -->
            <div v-if="selectedEl.type==='sparkline'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-wave-square section-icon" />Sparkline</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Values</span><input type="text" :value="(selectedEl.sparkData||[]).join(', ')" @input="eup('sparkData',$event.target.value.split(',').map(v=>+v.trim()).filter(v=>!isNaN(v)))" class="prop-input" placeholder="3,5,4,8,6" /></div>
                <div class="prop-row"><span class="prop-lbl">Color</span><input type="color" :value="selectedEl.styles?.color||settings?.primary_color||'#6366f1'" @input="ss('color',$event.target.value)" class="color-input" /></div>
              </div>
            </div>

            <!-- List -->
            <div v-if="selectedEl.type==='list'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-list-ul section-icon" />List</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Style</span>
                  <select :value="selectedEl.styles?.listStyle||'bullet'" @change="ss('listStyle',$event.target.value)" class="prop-select">
                    <option value="bullet">Bullet</option><option value="numbered">Numbered</option><option value="none">None</option>
                  </select>
                </div>
                <div v-for="(item,li) in (selectedEl.items||[])" :key="li" class="list-row">
                  <input type="text" :value="item" @input="selectedEl.items[li]=$event.target.value;$emit('mark-dirty')" class="prop-input" />
                  <button class="mini-btn danger" @click="selectedEl.items.splice(li,1);$emit('mark-dirty')"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-sec full-w" @click="selectedEl.items=[...(selectedEl.items||[]),'New item'];$emit('mark-dirty')">+ Add Item</button>
              </div>
            </div>

            <!-- Price Card -->
            <div v-if="selectedEl.type==='price-card'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-tags section-icon" />Price Card</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Plan</span><input type="text" :value="selectedEl.plan||''" @input="eup('plan',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Price</span><input type="text" :value="selectedEl.price||''" @input="eup('price',$event.target.value)" class="prop-input" placeholder="$49" /></div>
                <div class="prop-row"><span class="prop-lbl">Period</span><input type="text" :value="selectedEl.period||''" @input="eup('period',$event.target.value)" class="prop-input" placeholder="/month" /></div>
                <div class="section-lbl">Features</div>
                <div v-for="(f,fi) in (selectedEl.features||[])" :key="fi" class="list-row">
                  <input type="text" :value="f" @input="selectedEl.features[fi]=$event.target.value;$emit('mark-dirty')" class="prop-input" />
                  <button class="mini-btn danger" @click="selectedEl.features.splice(fi,1);$emit('mark-dirty')"><i class="fa-solid fa-xmark" /></button>
                </div>
                <button class="btn-sec full-w" @click="selectedEl.features=[...(selectedEl.features||[]),'Feature'];$emit('mark-dirty')">+ Feature</button>
              </div>
            </div>

            <!-- Kanban -->
            <div v-if="selectedEl.type==='kanban'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-columns section-icon" />Kanban Card</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Status</span><input type="text" :value="selectedEl.status||''" @input="eup('status',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Priority</span>
                  <select :value="selectedEl.priority||'medium'" @change="eup('priority',$event.target.value)" class="prop-select">
                    <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option>
                  </select>
                </div>
                <div class="prop-row"><span class="prop-lbl">Due Date</span><input type="text" :value="selectedEl.due||''" @input="eup('due',$event.target.value)" class="prop-input" placeholder="Dec 31, 2024" /></div>
              </div>
            </div>

            <!-- Social Card -->
            <div v-if="selectedEl.type==='social-card'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-id-card section-icon" />Social Card</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Avatar</span><input type="text" :value="selectedEl.avatar||'👤'" @input="eup('avatar',$event.target.value)" class="prop-input sm" /></div>
                <div class="prop-row"><span class="prop-lbl">Name</span><input type="text" :value="selectedEl.content||''" @input="eup('content',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Subtitle</span><input type="text" :value="selectedEl.subtitle||''" @input="eup('subtitle',$event.target.value)" class="prop-input" /></div>
              </div>
            </div>

            <!-- Callout -->
            <div v-if="selectedEl.type==='callout'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-lightbulb section-icon" />Callout</div>
              <div class="section-body"><div class="prop-row"><span class="prop-lbl">Emoji</span><input type="text" :value="selectedEl.emoji||'💡'" @input="eup('emoji',$event.target.value)" class="prop-input sm" /></div></div>
            </div>

            <!-- Testimonial -->
            <div v-if="selectedEl.type==='testimonial'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-comment-dots section-icon" />Testimonial</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Author</span><input type="text" :value="selectedEl.author||''" @input="eup('author',$event.target.value)" class="prop-input" /></div>
                <div class="prop-row"><span class="prop-lbl">Role</span><input type="text" :value="selectedEl.role||''" @input="eup('role',$event.target.value)" class="prop-input" /></div>
              </div>
            </div>

            <!-- TOC -->
            <div v-if="selectedEl.type==='toc'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-list-ol section-icon" />Table of Contents</div>
              <div class="section-body">
                <button class="btn-sec full-w" @click="$emit('refresh-toc')"><i class="fa-solid fa-rotate" /> Refresh TOC</button>
                <p class="hint-txt">Auto-populates from Heading & Subheading elements</p>
              </div>
            </div>

            <!-- HTML Embed -->
            <div v-if="selectedEl.type==='html-embed'" class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-code section-icon" />HTML Code</div>
              <div class="section-body"><textarea :value="selectedEl.htmlContent||''" @input="eup('htmlContent',$event.target.value)" class="content-ta code-ta" rows="8" placeholder="<div>Your HTML here</div>" /></div>
            </div>
          </div>

          <!-- ══ EFFECTS ══ -->
          <div v-show="activeTab === 'effects'">
            <div class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-wand-magic-sparkles section-icon" />Filters</div>
              <div class="section-body">
                <div class="prop-row"><span class="prop-lbl">Blur</span><div class="slider-row"><input type="range" min="0" max="20" :value="selectedEl.styles?.blur||0" @input="ss('blur',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.blur||0 }}px</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Brightness</span><div class="slider-row"><input type="range" min="0" max="200" :value="selectedEl.styles?.brightness||100" @input="ss('brightness',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.brightness||100 }}%</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Contrast</span><div class="slider-row"><input type="range" min="0" max="200" :value="selectedEl.styles?.contrast||100" @input="ss('contrast',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.contrast||100 }}%</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Grayscale</span><div class="slider-row"><input type="range" min="0" max="100" :value="selectedEl.styles?.grayscale||0" @input="ss('grayscale',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.grayscale||0 }}%</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Saturate</span><div class="slider-row"><input type="range" min="0" max="200" :value="selectedEl.styles?.saturate||100" @input="ss('saturate',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.saturate||100 }}%</span></div></div>
                <div class="prop-row"><span class="prop-lbl">Sepia</span><div class="slider-row"><input type="range" min="0" max="100" :value="selectedEl.styles?.sepia||0" @input="ss('sepia',+$event.target.value)" class="slider" /><span class="val">{{ selectedEl.styles?.sepia||0 }}%</span></div></div>
              </div>
            </div>
            <div class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-blend section-icon" />Blend Mode</div>
              <div class="section-body">
                <select :value="selectedEl.styles?.mixBlendMode||'normal'" @change="ss('mixBlendMode',$event.target.value)" class="prop-select">
                  <option v-for="m in blendModes" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
            </div>
            <div class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-rotate section-icon" />Transform</div>
              <div class="section-body">
                <div class="flip-row">
                  <button :class="{active:selectedEl.styles?.scaleX===-1}" @click="ss('scaleX',selectedEl.styles?.scaleX===-1?1:-1)"><i class="fa-solid fa-arrows-left-right" /> Flip H</button>
                  <button :class="{active:selectedEl.styles?.scaleY===-1}" @click="ss('scaleY',selectedEl.styles?.scaleY===-1?1:-1)"><i class="fa-solid fa-arrows-up-down" /> Flip V</button>
                </div>
              </div>
            </div>
          </div>

          <!-- ══ ARRANGE ══ -->
          <div v-show="activeTab === 'arrange'">
            <div class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-layer-group section-icon" />Layer Order</div>
              <div class="section-body">
                <div class="grid-2">
                  <button class="arrange-btn" @click="$emit('bring-front')"><i class="fa-solid fa-angles-up" /><span>To Front</span></button>
                  <button class="arrange-btn" @click="$emit('send-back')"><i class="fa-solid fa-angles-down" /><span>To Back</span></button>
                </div>
              </div>
            </div>
            <div class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-align-center section-icon" />Align to Page</div>
              <div class="section-body">
                <div class="align-page-grid">
                  <button @click="$emit('align-to-page','left')" title="Left"><i class="fa-solid fa-align-left" /></button>
                  <button @click="$emit('align-to-page','center-h')" title="Center H"><i class="fa-solid fa-align-center" /></button>
                  <button @click="$emit('align-to-page','right')" title="Right"><i class="fa-solid fa-align-right" /></button>
                  <button @click="$emit('align-to-page','top')" title="Top"><i class="fa-solid fa-arrow-up" /></button>
                  <button @click="$emit('align-to-page','center-v')" title="Center V"><i class="fa-solid fa-arrows-up-down" /></button>
                  <button @click="$emit('align-to-page','bottom')" title="Bottom"><i class="fa-solid fa-arrow-down" /></button>
                </div>
              </div>
            </div>
            <div class="prop-section">
              <div class="section-hdr open-always"><i class="fa-solid fa-bolt section-icon" />Quick Actions</div>
              <div class="section-body">
                <div class="actions-grid">
                  <button class="action-btn" @click="$emit('duplicate-el')"><i class="fa-solid fa-clone" /> Duplicate</button>
                  <button class="action-btn" @click="$emit('copy-el')"><i class="fa-solid fa-copy" /> Copy</button>
                  <button class="action-btn" v-if="clipboard" @click="$emit('paste-el')"><i class="fa-solid fa-paste" /> Paste</button>
                  <button class="action-btn" @click="$emit('style-painter-copy')"><i class="fa-solid fa-paintbrush" /> Copy Style</button>
                  <button class="action-btn" @click="$emit('style-painter-activate')"><i class="fa-solid fa-brush" /> Paint</button>
                  <button class="action-btn" v-if="stylePainterClipboard" @click="$emit('style-painter-paste')"><i class="fa-solid fa-fill-drip" /> Paste Style</button>
                  <button class="action-btn" @click="$emit('reset-styles')"><i class="fa-solid fa-rotate-left" /> Reset</button>
                  <button class="action-btn danger" @click="$emit('delete-el')"><i class="fa-solid fa-trash-can" /> Delete</button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, reactive } from 'vue'

const props = defineProps({
  selectedEl: { type: Object, default: null },
  selectedElsCount: { type: Number, default: 0 },
  settings: { type: Object, default: () => ({}) },
  currentPageElements: { type: Array, default: () => [] },
  clipboard: { type: Object, default: null },
  stylePainterClipboard: { type: Object, default: null },
  isCollapsed: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
  currentPage: { type: Number, default: 0 },
  totalPages: { type: Number, default: 1 },
  selectedElIdx: { type: [Number,null], default: null },
})

const emit = defineEmits([
  'update:style','update:content','delete-el','duplicate-el','copy-el','paste-el',
  'lock-el','bring-front','send-back','align-to-page','update:settings',
  'update:active-section','update:is-collapsed',
  'add-table-row','add-table-col','remove-table-row','remove-table-col',
  'set-chart-labels','set-chart-values',
  'add-timeline-item','remove-timeline-item',
  'add-checklist-item','remove-checklist-item',
  'add-stat-item','remove-stat-item',
  'reset-styles','style-painter-copy','style-painter-paste','style-painter-activate',
  'mark-dirty','image-replace','refresh-toc',
  'update-el-prop','update-position','move-element-to-page',
])

const activeTab = ref('style')
const open = reactive({ pos: true, fill: true, border: false, shadow: false, priority: false, font: true })
const colorPresets = ref(['#6366f1','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4','#ec4899','#84cc16','#f97316','#1e293b','#ffffff','#000000'])

const propTabs = [
  { id: 'style', label: 'Style', icon: 'fa-solid fa-paint-brush' },
  { id: 'typography', label: 'Text', icon: 'fa-solid fa-font' },
  { id: 'content', label: 'Content', icon: 'fa-solid fa-align-left' },
  { id: 'effects', label: 'Effects', icon: 'fa-solid fa-wand-magic-sparkles' },
  { id: 'arrange', label: 'Arrange', icon: 'fa-solid fa-layer-group' },
]

const fontList = [
  { value:"'DM Sans', sans-serif", label:'DM Sans' },
  { value:"'Inter', sans-serif", label:'Inter' },
  { value:"'Plus Jakarta Sans', sans-serif", label:'Plus Jakarta Sans' },
  { value:"'Space Grotesk', sans-serif", label:'Space Grotesk' },
  { value:"'Sora', sans-serif", label:'Sora' },
  { value:"Georgia, serif", label:'Georgia' },
  { value:"'Playfair Display', serif", label:'Playfair Display' },
  { value:"'Fira Code', monospace", label:'Fira Code (Mono)' },
]

const shadowPresets = [
  { name:'None', value:'none' },
  { name:'Soft', value:'0 2px 8px rgba(0,0,0,0.08)' },
  { name:'Med', value:'0 4px 20px rgba(0,0,0,0.15)' },
  { name:'Heavy', value:'0 8px 40px rgba(0,0,0,0.25)' },
  { name:'Glow', value:'0 0 20px rgba(99,102,241,0.4)' },
  { name:'Inner', value:'inset 0 2px 8px rgba(0,0,0,0.1)' },
]

const priorities = [
  { val:'none', label:'None', color:'#94a3b8' },
  { val:'low', label:'Low', color:'#3b82f6' },
  { val:'medium', label:'Med', color:'#f59e0b' },
  { val:'high', label:'High', color:'#f97316' },
  { val:'urgent', label:'Urgent', color:'#ef4444' },
]

const blendModes = ['normal','multiply','screen','overlay','darken','lighten','color-dodge','color-burn','difference','exclusion','hue','saturation','color','luminosity']

function toggle(k) { open[k] = !open[k] }
function ss(prop, val) { emit('update:style', prop, val) }
function eup(prop, val) { emit('update-el-prop', { prop, val }) }
function updatePos(axis, value) { emit('update-position', { axis, value }) }
function toggleFmt(prop, onVal, offVal) {
  if (!props.selectedEl?.styles) return
  ss(prop, props.selectedEl.styles[prop] === onVal ? offVal : onVal)
}
function isTextEl(type) { return ['text','heading','subheading','quote','blockquote','highlight','badge','code','link','richtext','toc','callout','watermark'].includes(type) }
function isChartEl(type) { return type?.endsWith('-chart') }
function cleanColor(val) { return (!val || val === 'transparent') ? '#ffffff' : val }
function stripHtml(html) { return (html || '').replace(/<[^>]*>/g, '') }
function getElIcon(type) {
  const m = { text:'fa-solid fa-align-left', heading:'fa-solid fa-heading', image:'fa-solid fa-image', table:'fa-solid fa-table', metric:'fa-solid fa-chart-simple', progress:'fa-solid fa-bars-progress', rectangle:'fa-solid fa-square', circle:'fa-solid fa-circle', divider:'fa-solid fa-minus', timeline:'fa-solid fa-timeline', testimonial:'fa-solid fa-comment-dots', signature:'fa-solid fa-signature', callout:'fa-solid fa-lightbulb', checklist:'fa-solid fa-list-check', rating:'fa-solid fa-star', 'qr-code':'fa-solid fa-qrcode', video:'fa-solid fa-video', map:'fa-solid fa-map', richtext:'fa-solid fa-file-word', sparkline:'fa-solid fa-wave-square', 'price-card':'fa-solid fa-tags', kanban:'fa-solid fa-columns', 'social-card':'fa-solid fa-id-card', 'html-embed':'fa-solid fa-code', toc:'fa-solid fa-list-ol' }
  for (const [k, v] of Object.entries(m)) { if (type?.includes(k)) return v }
  if (type?.endsWith('-chart')) return 'fa-solid fa-chart-bar'
  return 'fa-solid fa-cube'
}
async function generateQR() {
  if (!props.selectedEl) return
  props.selectedEl.qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${props.selectedEl.qrSize||150}x${props.selectedEl.qrSize||150}&data=${encodeURIComponent(props.selectedEl.qrText||'https://example.com')}`
  emit('mark-dirty')
}
</script>

<style scoped>
.right-panel { width: 276px; flex-shrink: 0; background: var(--bg-panel,#fff); border-left: 1px solid var(--border,#e2e8f0); display: flex; flex-direction: column; overflow: hidden; transition: width 0.25s ease; position: relative; }
.right-panel.collapsed { width: 0; border-left: none; }
.panel-toggle { position: absolute; left: -14px; top: 50%; transform: translateY(-50%); width: 28px; height: 28px; border-radius: 50%; background: var(--bg-panel); border: 1px solid var(--border); cursor: pointer; color: var(--text-muted); font-size: 10px; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); z-index: 10; transition: all 0.15s; }
.panel-toggle:hover { color: var(--accent); border-color: var(--accent); }
.panel-body { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.no-selection { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 28px 16px; text-align: center; color: var(--text-muted); }
.no-sel-icon { width: 56px; height: 56px; border-radius: 50%; background: var(--bg-secondary); display: flex; align-items: center; justify-content: center; font-size: 22px; opacity: 0.5; }
.no-selection h3 { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.no-selection p { font-size: 11px; line-height: 1.5; }
.quick-stats { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; margin-top: 8px; }
.stat-chip { display: flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; padding: 4px 10px; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 99px; color: var(--text-secondary); }
.stat-chip.accent { background: var(--accent-light); border-color: var(--accent); color: var(--accent); }
.props-wrap { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.props-header { display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
.el-identity { display: flex; align-items: center; gap: 7px; }
.el-type-icon { font-size: 14px; color: var(--accent); }
.el-type-name { display: block; font-size: 11px; font-weight: 700; text-transform: capitalize; color: var(--text-primary); }
.el-id { display: block; font-size: 9px; color: var(--text-muted); font-family: monospace; }
.header-actions { display: flex; gap: 2px; }
.header-actions button { width: 26px; height: 26px; border: 1px solid var(--border); border-radius: 5px; background: transparent; cursor: pointer; color: var(--text-secondary); font-size: 11px; display: flex; align-items: center; justify-content: center; transition: all 0.12s; }
.header-actions button:hover { background: var(--bg-secondary); color: var(--text-primary); }
.header-actions button.active { background: rgba(245,158,11,.1); color: #f59e0b; }
.header-actions button.danger:hover { background: var(--danger-light); color: var(--danger); }
.prop-tabs { display: flex; border-bottom: 1px solid var(--border); flex-shrink: 0; }
.ptab { flex: 1; padding: 5px 1px; border: none; background: transparent; cursor: pointer; color: var(--text-muted); font-size: 7.5px; font-weight: 700; display: flex; flex-direction: column; align-items: center; gap: 2px; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all 0.15s; font-family: inherit; }
.ptab i { font-size: 11px; }
.ptab:hover { color: var(--text-secondary); background: var(--bg-secondary); }
.ptab.active { color: var(--accent); border-bottom-color: var(--accent); }
.prop-body { flex: 1; overflow-y: auto; padding: 4px; scrollbar-width: thin; }
.prop-section { margin-bottom: 3px; border: 1px solid var(--border-light,#f1f5f9); border-radius: 7px; overflow: hidden; }
.section-hdr { display: flex; align-items: center; gap: 6px; width: 100%; padding: 7px 9px; border: none; background: var(--bg-secondary); cursor: pointer; font-size: 10px; font-weight: 700; color: var(--text-secondary); font-family: inherit; transition: background 0.1s; }
.section-hdr:hover { background: var(--bg-tertiary); }
.open-always { cursor: default; }
.section-icon { font-size: 11px; color: var(--accent); }
.ml-auto { margin-left: auto; }
.text-xs { font-size: 8px; opacity: 0.5; }
.section-body { padding: 8px 9px; display: flex; flex-direction: column; gap: 6px; }
.prop-row { display: flex; align-items: center; gap: 6px; min-height: 26px; }
.prop-lbl { font-size: 9px; font-weight: 600; color: var(--text-secondary); width: 70px; flex-shrink: 0; }
.grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 4px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
.fn { display: flex; flex-direction: column; gap: 2px; }
.fn label { font-size: 8px; font-weight: 700; color: var(--text-muted); text-align: center; }
.center { text-align: center; }
.prop-input { width: 100%; padding: 4px 6px; border: 1px solid var(--border); border-radius: 5px; background: var(--bg-secondary); color: var(--text-primary); font-size: 10px; outline: none; box-sizing: border-box; font-family: inherit; }
.prop-input:focus { border-color: var(--accent); }
.prop-input.sm { width: 62px; flex: none; }
.prop-input.mono { font-family:'Courier New',monospace; }
.prop-select { width: 100%; padding: 4px 5px; border: 1px solid var(--border); border-radius: 5px; background: var(--bg-secondary); color: var(--text-primary); font-size: 10px; outline: none; cursor: pointer; font-family: inherit; }
.slider { flex: 1; accent-color: var(--accent); cursor: pointer; height: 4px; }
.slider-row { display: flex; align-items: center; gap: 6px; flex: 1; }
.val { font-size: 9px; color: var(--text-muted); min-width: 32px; text-align: right; font-weight: 700; }
.color-row { display: flex; align-items: center; gap: 4px; flex: 1; }
.color-input { width: 26px; height: 26px; border: 1px solid var(--border); border-radius: 5px; cursor: pointer; padding: 1px; background: transparent; flex-shrink: 0; }
.color-presets { display: flex; gap: 3px; flex-wrap: wrap; }
.color-dot { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border); cursor: pointer; transition: transform 0.15s; }
.color-dot:hover { transform: scale(1.2); border-color: var(--text-primary); }
.mini-btn { padding: 3px 7px; border: 1px solid var(--border); border-radius: 4px; background: transparent; cursor: pointer; color: var(--text-secondary); font-size: 10px; transition: all 0.12s; font-family: inherit; flex-shrink: 0; }
.mini-btn:hover { background: var(--bg-secondary); }
.mini-btn.danger:hover { color: var(--danger); background: var(--danger-light); }
.shadow-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 4px; margin-bottom: 6px; }
.shadow-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 5px 3px; border: 1px solid var(--border); border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.15s; font-family: inherit; }
.shadow-btn:hover { border-color: var(--accent); }
.shadow-btn.active { border-color: var(--accent); background: var(--accent-light); }
.shadow-box { width: 28px; height: 18px; background: var(--bg-panel); border-radius: 3px; }
.shadow-btn span { font-size: 8px; font-weight: 600; color: var(--text-secondary); }
.priority-row { display: flex; gap: 2px; }
.pri-btn { flex: 1; padding: 4px 2px; border: 1px solid var(--border); border-radius: 5px; background: var(--bg-secondary); cursor: pointer; font-size: 8px; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 2px; transition: all 0.15s; font-family: inherit; }
.pri-btn.active { box-shadow: 0 0 0 2px currentColor; color: var(--text-primary); }
.pri-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.fmt-btns { display: flex; gap: 2px; }
.fmt-btns button { width: 26px; height: 26px; border: 1px solid var(--border); border-radius: 5px; background: transparent; cursor: pointer; color: var(--text-secondary); font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; transition: all 0.12s; }
.fmt-btns button:hover { background: var(--bg-secondary); }
.fmt-btns button.active { background: var(--accent-light); color: var(--accent); border-color: var(--accent); }
.align-btns { display: flex; gap: 2px; }
.align-btns button { width: 26px; height: 26px; border: 1px solid var(--border); border-radius: 5px; background: transparent; cursor: pointer; color: var(--text-secondary); font-size: 11px; display: flex; align-items: center; justify-content: center; transition: all 0.12s; }
.align-btns button:hover { background: var(--bg-secondary); }
.align-btns button.active { background: var(--accent-light); color: var(--accent); border-color: var(--accent); }
.flip-row { display: flex; gap: 4px; }
.flip-row button { flex: 1; padding: 6px; border: 1px solid var(--border); border-radius: 6px; background: transparent; cursor: pointer; font-size: 10px; font-weight: 600; color: var(--text-secondary); display: flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.12s; font-family: inherit; }
.flip-row button:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
.flip-row button.active { background: var(--accent-light); color: var(--accent); border-color: var(--accent); }
.arrange-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: transparent; cursor: pointer; font-size: 10px; font-weight: 600; color: var(--text-secondary); transition: all 0.15s; font-family: inherit; }
.arrange-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
.align-page-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 4px; }
.align-page-grid button { padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: transparent; cursor: pointer; color: var(--text-secondary); font-size: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.15s; font-family: inherit; }
.align-page-grid button:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
.actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
.action-btn { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 7px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-secondary); cursor: pointer; color: var(--text-secondary); font-size: 9px; font-weight: 600; transition: all 0.12s; font-family: inherit; }
.action-btn:hover { background: var(--bg-tertiary); color: var(--text-primary); }
.action-btn.danger:hover { background: var(--danger-light); color: var(--danger); border-color: var(--danger); }
.content-ta { width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-secondary); color: var(--text-primary); font-size: 11px; outline: none; resize: vertical; line-height: 1.5; box-sizing: border-box; font-family: inherit; }
.content-ta:focus { border-color: var(--accent); }
.code-ta { font-family:'Fira Code','Courier New',monospace !important; font-size: 10px; }
.table-info { font-size: 11px; color: var(--text-muted); text-align: center; }
.list-row { display: flex; gap: 4px; align-items: center; }
.tl-edit { border: 1px solid var(--border); border-radius: 6px; padding: 6px; display: flex; flex-direction: column; gap: 4px; }
.btn-sec { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 5px 9px; border: 1px solid var(--border); background: var(--bg-secondary); color: var(--text-primary); border-radius: 6px; cursor: pointer; font-size: 10px; font-weight: 500; transition: all 0.15s; font-family: inherit; }
.btn-sec:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
.btn-sec:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-sec.full-w { width: 100%; }
.mt-2 { margin-top: 4px; }
.section-lbl { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); margin-top: 4px; }
.hint-txt { font-size: 10px; color: var(--text-muted); line-height: 1.5; margin-top: 6px; }
.no-props { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 24px; color: var(--text-muted); text-align: center; }
.no-props i { font-size: 28px; opacity: 0.3; }
.no-props p { font-size: 11px; }
@media (max-width:768px) { .right-panel { position: fixed; right: 0; top: 48px; bottom: 0; z-index: 150; box-shadow: -8px 0 32px rgba(0,0,0,.15); } .right-panel.collapsed { width: 0; box-shadow: none; } }
</style>