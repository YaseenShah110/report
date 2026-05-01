<!-- resources/js/Pages/Reports/Editor.vue -->
<template>
  <Head :title="reportTitle + ' — Editor'" />
  <div class="editor-shell" :class="{ dark: isDark, 'rtl': rs.rtl }">

    <!-- TOP TOOLBAR -->
    <header class="editor-topbar">
      <div class="topbar-left">
        <button @click="goBack" class="btn-icon" title="Back to Reports">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="title-area">
          <input v-model="reportTitle" class="title-input" placeholder="Untitled Report…" @blur="saveReport" />
          <transition name="fade">
            <span v-if="saveStatus === 'saving'" class="save-badge saving">
              <i class="fa-solid fa-spinner fa-spin"></i> Saving…
            </span>
            <span v-else-if="saveStatus === 'saved'" class="save-badge saved">
              <i class="fa-solid fa-check"></i> Saved
            </span>
          </transition>
        </div>
      </div>

      <div class="topbar-center">
        <div class="tool-group">
          <button @click="activeTool = 'select'" :class="{ active: activeTool === 'select' }" class="tool-btn" title="Select (V)">
            <i class="fa-solid fa-arrow-pointer"></i>
          </button>
          <button @click="activeTool = 'pan'" :class="{ active: activeTool === 'pan' }" class="tool-btn" title="Pan (H)">
            <i class="fa-solid fa-hand"></i>
          </button>
        </div>
        
        <div class="tool-sep"></div>
        
        <div class="tool-group">
          <button @click="showGrid = !showGrid" :class="{ active: showGrid }" class="tool-btn" title="Grid (G)">
            <i class="fa-solid fa-border-all"></i>
          </button>
          <button @click="showRulers = !showRulers" :class="{ active: showRulers }" class="tool-btn" title="Rulers (R)">
            <i class="fa-solid fa-ruler-combined"></i>
          </button>
          <button @click="snapToGrid = !snapToGrid" :class="{ active: snapToGrid }" class="tool-btn" title="Snap to Grid">
            <i class="fa-solid fa-magnet"></i>
          </button>
        </div>
        
        <div class="tool-sep"></div>
        
        <div class="tool-group">
          <button @click="undo" :disabled="histIdx <= 0" class="tool-btn" title="Undo (Ctrl+Z)">
            <i class="fa-solid fa-rotate-left"></i>
          </button>
          <button @click="redo" :disabled="histIdx >= history.length - 1" class="tool-btn" title="Redo (Ctrl+Y)">
            <i class="fa-solid fa-rotate-right"></i>
          </button>
        </div>
        
        <div class="tool-sep"></div>
        
        <div class="zoom-controls">
          <button @click="zoomOut" class="tool-btn"><i class="fa-solid fa-minus"></i></button>
          <div class="zoom-display" @click="zoom = 100">{{ zoom }}%</div>
          <button @click="zoomIn" class="tool-btn"><i class="fa-solid fa-plus"></i></button>
          <button @click="fitToScreen" class="tool-btn" title="Fit to Screen"><i class="fa-solid fa-expand"></i></button>
        </div>
      </div>

      <div class="topbar-right">
        <button @click="isDark = !isDark" class="tool-btn" :title="isDark ? 'Light Mode' : 'Dark Mode'">
          <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
        </button>
        <button @click="saveReport" class="btn-save">
          <i class="fa-solid fa-floppy-disk"></i> Save
        </button>
        <button @click="previewReport" class="btn-preview">
          <i class="fa-solid fa-eye"></i> Preview
        </button>
        <div class="export-wrapper" ref="exportRef">
          <button @click="showExportMenu = !showExportMenu" class="btn-export">
            <i class="fa-solid fa-file-export"></i> Export
            <i class="fa-solid fa-chevron-down" style="font-size:9px"></i>
          </button>
          <transition name="dropdown">
            <div v-if="showExportMenu" class="export-dropdown">
              <button v-for="fmt in EXPORT_FORMATS" :key="fmt.key" @click="exportAs(fmt.key)" class="export-item">
                <i :class="fmt.icon" :style="{color: fmt.color}"></i>
                <div>
                  <div class="export-label">{{ fmt.label }}</div>
                  <div class="export-desc">{{ fmt.desc }}</div>
                </div>
              </button>
            </div>
          </transition>
        </div>
      </div>
    </header>

    <!-- MAIN BODY -->
    <div class="editor-body">

      <!-- LEFT SIDEBAR -->
      <aside class="left-sidebar" :class="{ collapsed: leftSidebarCollapsed }">
        <button class="sidebar-collapse-btn" @click="leftSidebarCollapsed = !leftSidebarCollapsed">
          <i :class="leftSidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"></i>
        </button>
        
        <template v-if="!leftSidebarCollapsed">
          <div class="sidebar-tabs">
            <button v-for="tab in LEFT_TABS" :key="tab.id"
              @click="leftTab = tab.id"
              :class="{ active: leftTab === tab.id }"
              class="sidebar-tab" :title="tab.label">
              <i :class="tab.icon"></i>
              <span>{{ tab.label }}</span>
            </button>
          </div>

          <!-- ELEMENTS TAB -->
          <div v-if="leftTab === 'elements'" class="sidebar-content">
            <div class="search-wrap">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input v-model="elemSearch" placeholder="Search 50+ elements…" class="search-input" />
              <button v-if="elemSearch" @click="elemSearch = ''" class="search-clear">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="elements-list">
              <div v-for="cat in filteredCategories" :key="cat.name" class="elem-category">
                <button @click="toggleCat(cat.name)" class="cat-header">
                  <div class="cat-icon" :style="{background: cat.color + '22', color: cat.color}">
                    <i :class="cat.icon"></i>
                  </div>
                  <span class="cat-name">{{ cat.name }}</span>
                  <span class="cat-count">{{ cat.items.length }}</span>
                  <i class="fa-solid fa-chevron-down cat-chevron" :class="{rotated: !collapsedCats.includes(cat.name)}"></i>
                </button>
                <div v-show="!collapsedCats.includes(cat.name)" class="elem-grid">
                  <div v-for="el in cat.items" :key="el.type"
                    class="elem-card"
                    draggable="true"
                    @dragstart="onElemDragStart($event, el.type)"
                    @click="addElement(el.type)"
                    :title="el.name">
                    <div class="elem-icon">
                      <i :class="el.icon"></i>
                    </div>
                    <span class="elem-name">{{ el.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PAGES TAB -->
          <div v-if="leftTab === 'pages'" class="sidebar-content">
            <div class="pages-header">
              <span class="pages-title">Pages ({{ pages.length }})</span>
              <button @click="addPage" class="btn-add-page" title="Add Page">
                <i class="fa-solid fa-plus"></i>
              </button>
            </div>
            <div class="pages-list">
              <div v-for="(page, pi) in pages" :key="page.id"
                class="page-thumb"
                :class="{ active: pi === activePageIdx }"
                @click="selectPage(pi)">
                <div class="page-mini-preview" :style="{background: rs.background_color || '#fff'}">
                  <div v-for="el in (page.elements || []).slice(0,6)" :key="el.id"
                    class="mini-el" :style="getMiniElStyle(el)"></div>
                </div>
                <div class="page-thumb-info">
                  <input class="page-label-input"
                    :value="page.label || `Page ${pi + 1}`"
                    @change="renamePage(pi, $event.target.value)"
                    @click.stop />
                  <span class="page-el-count">{{ (page.elements || []).length }} elements</span>
                </div>
                <div class="page-thumb-actions">
                  <button @click.stop="duplicatePage(pi)" title="Duplicate"><i class="fa-regular fa-clone"></i></button>
                  <button @click.stop="deletePage(pi)" title="Delete" :disabled="pages.length <= 1"><i class="fa-solid fa-xmark"></i></button>
                </div>
              </div>
            </div>
            <button @click="addPage" class="btn-add-page-full">
              <i class="fa-solid fa-plus"></i> Add Page
            </button>
          </div>

          <!-- SETTINGS TAB -->
          <div v-if="leftTab === 'settings'" class="sidebar-content settings-panel">
            <SettingsSection title="Page Setup" icon="fa-solid fa-file" :open="true">
              <SettingRow label="Page Size">
                <select v-model="rs.page_size" @change="pushHistory" class="s-select">
                  <option value="A4">A4 (210 × 297 mm)</option>
                  <option value="Letter">US Letter (8.5 × 11 in)</option>
                  <option value="Legal">Legal (8.5 × 14 in)</option>
                  <option value="A3">A3 (297 × 420 mm)</option>
                  <option value="A5">A5 (148 × 210 mm)</option>
                </select>
              </SettingRow>
              <SettingRow label="Orientation">
                <div class="btn-group">
                  <button v-for="o in ['portrait','landscape']" :key="o"
                    @click="rs.orientation = o; pushHistory()"
                    :class="{ active: rs.orientation === o }" class="btn-group-item">
                    <i :class="o === 'portrait' ? 'fa-solid fa-file' : 'fa-solid fa-file fa-rotate-90'"></i>
                    {{ o }}
                  </button>
                </div>
              </SettingRow>
              <SettingRow label="Page Margin (px)">
                <input type="number" v-model.number="rs.margin" @change="pushHistory" class="s-input" min="0" max="120" />
              </SettingRow>
              <SettingRow label="Page Radius (px)">
                <input type="number" v-model.number="rs.page_radius" @change="pushHistory" class="s-input" min="0" max="40" />
              </SettingRow>
              <SettingRow label="Background Color">
                <ColorPicker :value="rs.background_color" @update="v => { rs.background_color = v; pushHistory() }" />
              </SettingRow>
              <SettingRow label="Background Image">
                <input type="url" v-model="rs.bg_image" @change="pushHistory" class="s-input" placeholder="https://…" />
              </SettingRow>
            </SettingsSection>

            <SettingsSection title="Typography" icon="fa-solid fa-font">
              <SettingRow label="Font Family">
                <select v-model="rs.font_family" @change="pushHistory" class="s-select">
                  <option v-for="f in FONTS" :key="f.value" :value="f.value">{{ f.name }}</option>
                </select>
              </SettingRow>
              <SettingRow label="Base Font Size (px)">
                <input type="number" v-model.number="rs.font_size" @change="pushHistory" class="s-input" min="8" max="32" />
              </SettingRow>
              <SettingRow label="Text Color">
                <ColorPicker :value="rs.text_color" @update="v => { rs.text_color = v; pushHistory() }" />
              </SettingRow>
            </SettingsSection>

            <SettingsSection title="Brand Colors" icon="fa-solid fa-palette">
              <SettingRow label="Primary Color">
                <ColorPicker :value="rs.primary_color" @update="v => { rs.primary_color = v; pushHistory() }" />
              </SettingRow>
              <SettingRow label="Accent Color">
                <ColorPicker :value="rs.accent_color" @update="v => { rs.accent_color = v; pushHistory() }" />
              </SettingRow>
            </SettingsSection>

            <SettingsSection title="Header" icon="fa-solid fa-rectangle-ad">
              <SettingRow label="Show Header">
                <Toggle :value="rs.show_header" @toggle="rs.show_header = !rs.show_header; pushHistory()" />
              </SettingRow>
              <template v-if="rs.show_header">
                <SettingRow label="Header Text">
                  <input v-model="rs.header_text" @change="pushHistory" class="s-input" placeholder="Header text…" />
                </SettingRow>
                <SettingRow label="Header Height (px)">
                  <input type="number" v-model.number="rs.header_height" @change="pushHistory" class="s-input" min="20" max="120" />
                </SettingRow>
              </template>
            </SettingsSection>

            <SettingsSection title="Footer" icon="fa-solid fa-grip-lines">
              <SettingRow label="Show Footer">
                <Toggle :value="rs.show_footer" @toggle="rs.show_footer = !rs.show_footer; pushHistory()" />
              </SettingRow>
              <template v-if="rs.show_footer">
                <SettingRow label="Footer Left Text">
                  <input v-model="rs.footer_left" @change="pushHistory" class="s-input" placeholder="Company Name" />
                </SettingRow>
                <SettingRow label="Footer Right Text">
                  <input v-model="rs.footer_right" @change="pushHistory" class="s-input" placeholder="Page {n}" />
                </SettingRow>
                <SettingRow label="Footer Height (px)">
                  <input type="number" v-model.number="rs.footer_height" @change="pushHistory" class="s-input" min="20" max="80" />
                </SettingRow>
              </template>
            </SettingsSection>

            <SettingsSection title="Grid & Canvas" icon="fa-solid fa-ruler-combined">
              <SettingRow label="Grid Size (px)">
                <input type="number" v-model.number="gridSize" class="s-input" min="5" max="60" step="5" />
              </SettingRow>
              <SettingRow label="Snap to Grid">
                <Toggle :value="snapToGrid" @toggle="snapToGrid = !snapToGrid" />
              </SettingRow>
            </SettingsSection>
          </div>
        </template>
        
        <div v-else class="sidebar-collapsed-icons">
          <button v-for="tab in LEFT_TABS" :key="tab.id"
            @click="leftTab = tab.id; leftSidebarCollapsed = false"
            class="collapsed-icon" :title="tab.label">
            <i :class="tab.icon"></i>
          </button>
        </div>
      </aside>

      <!-- CANVAS AREA -->
      <main class="canvas-area"
        ref="canvasArea"
        @dragover.prevent="onCanvasDragOver"
        @drop.prevent="onCanvasDrop"
        @mousedown.self="deselectAll">

        <!-- Rulers -->
        <template v-if="showRulers">
          <div class="ruler ruler-h" ref="hRuler">
            <canvas ref="hRulerCanvas" width="1000" height="24"></canvas>
          </div>
          <div class="ruler ruler-v" ref="vRuler">
            <canvas ref="vRulerCanvas" width="24" height="1000"></canvas>
          </div>
          <div class="ruler-corner"></div>
        </template>

        <!-- Canvas scroll wrapper -->
        <div class="canvas-scroll" ref="canvasScroll"
          :style="showRulers ? 'padding-top:24px;padding-left:24px;' : ''"
          @wheel.ctrl.prevent="onCtrlWheel">

          <div class="canvas-centering"
            :style="{ minWidth: (canvasDims.width * zf + 160) + 'px', minHeight: (canvasDims.height * zf + 160) + 'px' }">

            <!-- Pages -->
            <div v-for="(page, pi) in pages" :key="page.id"
              class="page-wrapper"
              :class="{ 'page-active': pi === activePageIdx }"
              :style="{ marginBottom: '48px' }"
              @click="selectPage(pi)">

              <!-- Page label -->
              <div class="page-label-bar">
                <span class="page-label-tag" :class="{ current: pi === activePageIdx }">
                  <i class="fa-regular fa-file"></i>
                  {{ page.label || `Page ${pi + 1}` }}
                </span>
              </div>

              <!-- The page canvas -->
              <div class="page-canvas"
                :id="'page-' + page.id"
                :style="pageStyle(pi)"
                @click.self="deselectAll"
                @dragover.prevent="isDragOverPage = true"
                @dragleave="isDragOverPage = false"
                @drop.prevent="e => onPageDrop(e, pi)">

                <!-- Grid overlay -->
                <svg v-if="showGrid"
                  class="grid-overlay"
                  :width="canvasDims.width * zf"
                  :height="canvasDims.height * zf">
                  <defs>
                    <pattern :id="'grid-pattern'" :width="gridSize * zf" :height="gridSize * zf" patternUnits="userSpaceOnUse">
                      <path :d="`M ${gridSize * zf} 0 L 0 0 0 ${gridSize * zf}`" fill="none" stroke="rgba(99,102,241,0.15)" stroke-width="0.5"/>
                    </pattern>
                    <pattern :id="'grid-pattern-lg'" :width="gridSize * zf * 5" :height="gridSize * zf * 5" patternUnits="userSpaceOnUse">
                      <path :d="`M ${gridSize * zf * 5} 0 L 0 0 0 ${gridSize * zf * 5}`" fill="none" stroke="rgba(99,102,241,0.25)" stroke-width="1"/>
                    </pattern>
                  </defs>
                  <rect width="100%" height="100%" fill="url(#grid-pattern)" />
                  <rect width="100%" height="100%" fill="url(#grid-pattern-lg)" />
                </svg>

                <!-- Header bar -->
                <div v-if="rs.show_header" class="page-header-bar"
                  :style="{height: (rs.header_height || 40) * zf + 'px', background: rs.header_bg || '#1e293b'}">
                  <span :style="{color: rs.header_text_color || '#fff', fontSize: (rs.header_font_size || 12) * zf + 'px', textAlign: rs.header_align || 'left'}">
                    {{ rs.header_text || '' }}
                  </span>
                </div>

                <!-- Watermark -->
                <div v-if="rs.watermark" class="watermark-layer"
                  :style="{ opacity: (rs.watermark_opacity || 20) / 100 }">
                  <span :style="{
                    fontSize: 72 * zf + 'px',
                    color: rs.watermark_color || '#94a3b8',
                    transform: `rotate(${rs.watermark_rotate || -30}deg)`
                  }">{{ rs.watermark }}</span>
                </div>

                <!-- ELEMENTS -->
                <div v-for="el in getSortedElements(page.elements)" :key="el.id"
                  class="element-node"
                  :class="{
                    selected: selectedElId === el.id,
                    locked: el.locked,
                    hidden: el.hidden,
                  }"
                  :style="elWrapStyle(el, pi)"
                  @mousedown.stop="onElMousedown($event, el, pi)"
                  @dblclick.stop="onElDblClick($event, el)"
                  @contextmenu.prevent.stop="openCtxMenu($event, el, pi)">

                  <!-- Dynamic element rendering -->
                  <component 
                    :is="getElementComponent(el.type)"
                    :el="el"
                    :pi="pi"
                    :zf="zf"
                    :rs="rs"
                    :dark="isDark"
                    :selected="selectedElId === el.id"
                    @update="handleElementUpdate"
                    @push-history="pushHistory" />

                  <!-- Selection UI -->
                  <template v-if="selectedElId === el.id && !el.locked">
                    <div class="sel-border" />
                    <div v-for="h in HANDLES" :key="h.dir"
                      class="resize-handle"
                      :class="'rh-' + h.dir"
                      :style="h.style"
                      @mousedown.stop.prevent="startResize(h.dir, $event)" />
                    <div class="rot-handle" @mousedown.stop.prevent="startRotation($event, el)" title="Rotate">
                      <i class="fa-solid fa-rotate"></i>
                    </div>
                    <div class="el-quickbar">
                      <button @click.stop="duplicateEl(pi, el)" title="Duplicate (Ctrl+D)"><i class="fa-regular fa-clone"></i></button>
                      <button @click.stop="bringToFront" title="Bring Front"><i class="fa-solid fa-angles-up"></i></button>
                      <button @click.stop="sendToBack" title="Send Back"><i class="fa-solid fa-angles-down"></i></button>
                      <button @click.stop="el.locked = !el.locked; pushHistory()" :title="el.locked ? 'Unlock' : 'Lock'">
                        <i :class="el.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'"></i>
                      </button>
                      <div class="qb-sep"></div>
                      <button @click.stop="deleteEl(pi, el.id)" title="Delete" class="del"><i class="fa-solid fa-trash"></i></button>
                    </div>
                  </template>
                </div>

                <!-- Footer bar -->
                <div v-if="rs.show_footer" class="page-footer-bar"
                  :style="{height: (rs.footer_height || 36) * zf + 'px', background: rs.footer_bg || 'transparent'}">
                  <span>{{ rs.footer_left }}</span>
                  <span>{{ rs.footer_center }}</span>
                  <span>{{ rs.footer_right.replace('{n}', pi + 1) }}</span>
                </div>

                <!-- Drop hint -->
                <div v-if="isDragOverPage" class="drop-hint">
                  <i class="fa-solid fa-plus-circle"></i>
                  <span>Drop to add element</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Status bar -->
        <div class="canvas-statusbar">
          <span><i class="fa-solid fa-cube"></i> {{ currentPageEls.length }} elements</span>
          <span><i class="fa-regular fa-file"></i> {{ rs.page_size }} · {{ rs.orientation }}</span>
          <span v-if="selectedEl"><i class="fa-solid fa-arrow-pointer"></i> {{ selectedEl.type }} ({{ Math.round(selectedEl.position?.x || 0) }}, {{ Math.round(selectedEl.position?.y || 0) }})</span>
        </div>

        <!-- Snap guides -->
        <div v-if="snapGuide.h !== null" class="snap-guide snap-h" :style="{top: snapGuide.h + 'px'}" />
        <div v-if="snapGuide.v !== null" class="snap-guide snap-v" :style="{left: snapGuide.v + 'px'}" />
      </main>

      <!-- RIGHT PROPERTIES PANEL -->
      <aside class="right-panel" :class="{ collapsed: rightPanelCollapsed }">
        <button class="panel-collapse-btn" @click="rightPanelCollapsed = !rightPanelCollapsed">
          <i :class="rightPanelCollapsed ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'"></i>
        </button>
        
        <template v-if="!rightPanelCollapsed">
          <div v-if="!selectedEl" class="no-selection">
            <div class="no-sel-icon"><i class="fa-solid fa-arrow-pointer"></i></div>
            <p class="no-sel-title">Select an element</p>
            <p class="no-sel-sub">Click any element on the canvas to edit its properties</p>
          </div>

          <template v-else>
            <div class="panel-header">
              <div class="panel-el-info">
                <div class="panel-el-icon">
                  <i :class="getElIcon(selectedEl.type)"></i>
                </div>
                <div>
                  <div class="panel-el-type">{{ selectedEl.type.replace(/-/g, ' ') }}</div>
                </div>
              </div>
              <div class="panel-header-actions">
                <button @click="selectedEl.locked = !selectedEl.locked; pushHistory()" :class="{ active: selectedEl.locked }">
                  <i :class="selectedEl.locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open'"></i>
                </button>
                <button @click="deleteSelectedEl" class="del">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>

            <div class="prop-tabs">
              <button v-for="t in PROP_TABS" :key="t.id" @click="propTab = t.id" :class="{ active: propTab === t.id }" class="prop-tab">
                <i :class="t.icon"></i> {{ t.label }}
              </button>
            </div>

            <div class="props-scroll">
              <!-- STYLE TAB -->
              <template v-if="propTab === 'style'">
                <PropSection title="Position & Size" icon="fa-solid fa-up-down-left-right" :open="true">
                  <div class="prop-grid-2">
                    <PropField label="X (px)">
                      <input type="number" :value="Math.round(selectedEl.position?.x || 0)" @input="selectedEl.position.x = +$event.target.value; pushHistory()" class="p-input" />
                    </PropField>
                    <PropField label="Y (px)">
                      <input type="number" :value="Math.round(selectedEl.position?.y || 0)" @input="selectedEl.position.y = +$event.target.value; pushHistory()" class="p-input" />
                    </PropField>
                    <PropField label="Width">
                      <input type="number" :value="Math.round(selectedEl.styles?.width || 200)" @input="selectedEl.styles.width = +$event.target.value; pushHistory()" class="p-input" min="10" />
                    </PropField>
                    <PropField label="Height">
                      <input type="number" :value="Math.round(selectedEl.styles?.height || 50)" @input="selectedEl.styles.height = +$event.target.value; pushHistory()" class="p-input" min="10" />
                    </PropField>
                  </div>
                  <PropField label="Rotation (°)">
                    <input type="range" :value="selectedEl.styles?.rotate || 0" min="-180" max="180" @input="selectedEl.styles.rotate = +$event.target.value; pushHistory()" class="p-range" />
                  </PropField>
                </PropSection>

                <PropSection title="Appearance" icon="fa-solid fa-palette" :open="true">
                  <PropField label="Background Color">
                    <ColorPicker :value="selectedEl.styles?.backgroundColor || 'transparent'" @update="v => { selectedEl.styles.backgroundColor = v; pushHistory() }" />
                  </PropField>
                  <PropField label="Border Radius (px)">
                    <input type="number" :value="selectedEl.styles?.borderRadius || 0" @input="selectedEl.styles.borderRadius = +$event.target.value; pushHistory()" class="p-input" min="0" max="100" />
                  </PropField>
                  <PropField label="Opacity (%)">
                    <input type="range" :value="selectedEl.styles?.opacity || 100" min="0" max="100" @input="selectedEl.styles.opacity = +$event.target.value; pushHistory()" class="p-range" />
                  </PropField>
                </PropSection>

                <PropSection v-if="isTextEl" title="Typography" icon="fa-solid fa-font" :open="true">
                  <PropField label="Font Size (px)">
                    <input type="number" :value="selectedEl.styles?.fontSize || 14" @input="selectedEl.styles.fontSize = +$event.target.value; pushHistory()" class="p-input" min="6" max="200" />
                  </PropField>
                  <PropField label="Text Color">
                    <ColorPicker :value="selectedEl.styles?.color || rs.text_color" @update="v => { selectedEl.styles.color = v; pushHistory() }" />
                  </PropField>
                  <div class="text-style-btns">
                    <button @click="toggleTextStyle('bold')" :class="{ active: selectedEl.styles?.fontWeight === '700' }" class="ts-btn"><i class="fa-solid fa-bold"></i></button>
                    <button @click="toggleTextStyle('italic')" :class="{ active: selectedEl.styles?.fontStyle === 'italic' }" class="ts-btn"><i class="fa-solid fa-italic"></i></button>
                    <button @click="toggleTextStyle('underline')" :class="{ active: selectedEl.styles?.textDecoration === 'underline' }" class="ts-btn"><i class="fa-solid fa-underline"></i></button>
                  </div>
                </PropSection>
              </template>

              <!-- CONTENT TAB -->
              <template v-if="propTab === 'content'">
                <PropSection title="Content" icon="fa-solid fa-paragraph" :open="true">
                  <PropField label="Text">
                    <textarea :value="selectedEl.content || ''" @input="selectedEl.content = $event.target.value; pushHistory()" class="p-textarea" rows="4" placeholder="Enter text here..." />
                  </PropField>
                  <PropField v-if="selectedEl.type === 'link'" label="URL">
                    <input type="url" :value="selectedEl.href || ''" @input="selectedEl.href = $event.target.value; pushHistory()" class="p-input" />
                  </PropField>
                  <PropField v-if="selectedEl.type === 'image'" label="Image URL">
                    <input type="url" :value="selectedEl.src || ''" @input="selectedEl.src = $event.target.value; pushHistory()" class="p-input" placeholder="https://..." />
                  </PropField>
                </PropSection>

                <PropSection v-if="selectedEl.type === 'list'" title="List Items" icon="fa-solid fa-list" :open="true">
                  <div v-for="(item, i) in (selectedEl.items || [])" :key="i" class="list-item-row">
                    <input :value="item" @input="selectedEl.items[i] = $event.target.value; pushHistory()" class="p-input" />
                    <button @click="selectedEl.items.splice(i, 1); pushHistory()" class="btn-del-small"><i class="fa-solid fa-xmark"></i></button>
                  </div>
                  <button @click="selectedEl.items = [...(selectedEl.items || []), 'New item']; pushHistory()" class="btn-add-item">
                    <i class="fa-solid fa-plus"></i> Add Item
                  </button>
                </PropSection>

                <PropSection v-if="['bar-chart', 'line-chart', 'pie-chart'].includes(selectedEl.type)" title="Chart Data" icon="fa-solid fa-chart-bar" :open="true">
                  <PropField label="Chart Title">
                    <input :value="selectedEl.chartTitle || ''" @input="selectedEl.chartTitle = $event.target.value; refreshChart(selectedEl); pushHistory()" class="p-input" />
                  </PropField>
                  <PropField label="Labels (comma separated)">
                    <input :value="(selectedEl.chartData?.labels || []).join(', ')" @input="updateChartLabels($event.target.value); pushHistory()" class="p-input" />
                  </PropField>
                  <PropField label="Values (comma separated)">
                    <input :value="(selectedEl.chartData?.values || []).join(', ')" @input="updateChartValues($event.target.value); pushHistory()" class="p-input" />
                  </PropField>
                </PropSection>
              </template>
            </div>
          </template>
        </template>
        
        <div v-else class="panel-collapsed-icons">
          <div class="collapsed-icon" title="Properties">
            <i class="fa-solid fa-sliders-h"></i>
          </div>
        </div>
      </aside>
    </div>

    <!-- Context Menu -->
    <transition name="dropdown">
      <div v-if="ctxMenu.show" class="context-menu" :style="{left: ctxMenu.x + 'px', top: ctxMenu.y + 'px'}" @click.stop>
        <button v-for="item in ctxItems" :key="item.label" @click="item.action(); ctxMenu.show = false" class="ctx-item" :class="{ danger: item.danger }">
          <i :class="item.icon"></i>
          <span>{{ item.label }}</span>
          <kbd v-if="item.key">{{ item.key }}</kbd>
        </button>
      </div>
    </transition>
    <div v-if="ctxMenu.show" class="ctx-overlay" @click="ctxMenu.show = false" />

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" class="toast" :class="toast.type">
        <i :class="toast.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick, provide } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import axios from 'axios'
import Chart from 'chart.js/auto'

// Utility functions
const uuid = () => {
  return crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).substr(2)
}

// ─── Element Components ───────────────────────────────────────────────────────
// Text Element
const TextElement = {
  props: ['el', 'zf', 'rs', 'selected'],
  emits: ['update'],
  template: `
    <div class="text-element" 
      :contenteditable="selected && !el.locked"
      @blur="$emit('update', { content: $event.target.innerText })"
      :style="{
        fontSize: (el.styles?.fontSize || rs.font_size) * zf + 'px',
        color: el.styles?.color || rs.text_color,
        fontFamily: el.styles?.fontFamily || rs.font_family,
        fontWeight: el.styles?.fontWeight || '400',
        fontStyle: el.styles?.fontStyle || 'normal',
        textAlign: el.styles?.textAlign || 'left',
        textDecoration: el.styles?.textDecoration || 'none',
        lineHeight: (el.styles?.lineHeight || 1.5),
        padding: (el.styles?.padding || 0) * zf + 'px',
        backgroundColor: el.styles?.backgroundColor || 'transparent',
        borderRadius: (el.styles?.borderRadius || 0) * zf + 'px',
        width: '100%',
        height: '100%',
        overflow: 'auto'
      }"
      v-html="el.content || 'Edit your text here...'"
    ></div>
  `
}

// Image Element
const ImageElement = {
  props: ['el', 'zf', 'selected'],
  template: `
    <div class="image-element" style="width:100%;height:100%">
      <img v-if="el.src" :src="el.src" 
        :style="{
          width: '100%',
          height: '100%',
          objectFit: el.styles?.objectFit || 'cover',
          borderRadius: (el.styles?.borderRadius || 0) * zf + 'px'
        }" />
      <div v-else class="image-placeholder">
        <i class="fa-solid fa-image"></i>
        <span>Click to add image</span>
      </div>
    </div>
  `
}

// Chart Element
const ChartElement = {
  props: ['el', 'zf'],
  setup(props) {
    const chartInstance = ref(null)
    const canvasRef = ref(null)
    
    const initChart = () => {
      if (!canvasRef.value) return
      if (chartInstance.value) chartInstance.value.destroy()
      
      const ctx = canvasRef.value.getContext('2d')
      const type = {
        'bar-chart': 'bar',
        'line-chart': 'line',
        'pie-chart': 'pie'
      }[props.el.type] || 'bar'
      
      chartInstance.value = new Chart(ctx, {
        type: type,
        data: {
          labels: props.el.chartData?.labels || [],
          datasets: [{
            label: props.el.chartTitle || 'Data',
            data: props.el.chartData?.values || [],
            backgroundColor: '#6366f1',
            borderColor: '#4f46e5',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      })
    }
    
    watch(() => props.el.chartData, () => initChart(), { deep: true })
    onMounted(() => initChart())
    onBeforeUnmount(() => {
      if (chartInstance.value) chartInstance.value.destroy()
    })
    
    return { canvasRef }
  },
  template: `
    <canvas ref="canvasRef" style="width:100%;height:100%"></canvas>
  `
}

// Map element types to components
const elementComponents = {
  text: TextElement,
  heading: TextElement,
  subheading: TextElement,
  image: ImageElement,
  'bar-chart': ChartElement,
  'line-chart': ChartElement,
  'pie-chart': ChartElement
}

const getElementComponent = (type) => {
  return elementComponents[type] || TextElement
}

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
  report: Object,
  template: Object
})

// ─── State ───────────────────────────────────────────────────────────────────
const isDark = ref(false)
const leftTab = ref('elements')
const propTab = ref('style')
const activeTool = ref('select')
const showGrid = ref(true)
const showRulers = ref(false)
const snapToGrid = ref(true)
const gridSize = ref(20)
const zoom = ref(100)
const saveStatus = ref('idle')
const reportTitle = ref(props.report?.title || 'Untitled Report')
const showExportMenu = ref(false)
const exportRef = ref(null)
const leftSidebarCollapsed = ref(false)
const rightPanelCollapsed = ref(false)

const elemSearch = ref('')
const collapsedCats = ref([])
const isDragOverPage = ref(false)

// Initialize pages from report or template
const initializePages = () => {
  if (props.report?.content && props.report.content.length > 0) {
    return props.report.content
  }
  
  const templatePage = {
    id: uuid(),
    label: 'Page 1',
    elements: props.template?.elements || []
  }
  
  return [templatePage]
}

const pages = ref(initializePages())
const activePageIdx = ref(0)
const selectedElId = ref(null)
const history = ref([JSON.stringify(pages.value)])
const histIdx = ref(0)

const snapGuide = reactive({ h: null, v: null })
const ctxMenu = reactive({ show: false, x: 0, y: 0, el: null, pi: null })
const toast = reactive({ show: false, message: '', type: 'success' })

// Refs
const canvasArea = ref(null)
const canvasScroll = ref(null)
const hRulerCanvas = ref(null)
const vRulerCanvas = ref(null)

// Chart instances
const chartInstances = new Map()

// Report settings with template inheritance
const rs = reactive({
  page_size: props.report?.settings?.page_size || props.template?.page_size || 'A4',
  orientation: props.report?.settings?.orientation || props.template?.orientation || 'portrait',
  primary_color: props.report?.settings?.primary_color || props.template?.primary_color || '#6366f1',
  accent_color: props.report?.settings?.accent_color || props.template?.accent_color || '#8b5cf6',
  background_color: props.report?.settings?.background_color || props.template?.background_color || '#ffffff',
  text_color: props.report?.settings?.text_color || props.template?.text_color || '#0f172a',
  font_family: props.report?.settings?.font_family || props.template?.font_family || "'DM Sans', sans-serif",
  font_size: props.report?.settings?.font_size || props.template?.font_size || 14,
  margin: props.report?.settings?.margin || props.template?.margin || 40,
  page_radius: props.report?.settings?.page_radius || props.template?.page_radius || 0,
  bg_image: props.report?.settings?.bg_image || props.template?.bg_image || '',
  show_header: props.report?.settings?.show_header ?? props.template?.show_header ?? false,
  header_text: props.report?.settings?.header_text || props.template?.header_text || '',
  header_height: props.report?.settings?.header_height || props.template?.header_height || 40,
  header_bg: props.report?.settings?.header_bg || props.template?.header_bg || '#1e293b',
  header_text_color: props.report?.settings?.header_text_color || props.template?.header_text_color || '#ffffff',
  header_font_size: props.report?.settings?.header_font_size || props.template?.header_font_size || 12,
  header_align: props.report?.settings?.header_align || props.template?.header_align || 'left',
  show_footer: props.report?.settings?.show_footer ?? props.template?.show_footer ?? false,
  footer_left: props.report?.settings?.footer_left || props.template?.footer_left || '',
  footer_center: props.report?.settings?.footer_center || props.template?.footer_center || '',
  footer_right: props.report?.settings?.footer_right || props.template?.footer_right || 'Page {n}',
  footer_height: props.report?.settings?.footer_height || props.template?.footer_height || 36,
  footer_bg: props.report?.settings?.footer_bg || props.template?.footer_bg || 'transparent',
  footer_text_color: props.report?.settings?.footer_text_color || props.template?.footer_text_color || '#94a3b8',
  footer_font_size: props.report?.settings?.footer_font_size || props.template?.footer_font_size || 10,
  watermark: props.report?.settings?.watermark || props.template?.watermark || '',
  watermark_color: props.report?.settings?.watermark_color || props.template?.watermark_color || '#94a3b8',
  watermark_opacity: props.report?.settings?.watermark_opacity || props.template?.watermark_opacity || 10,
  watermark_rotate: props.report?.settings?.watermark_rotate || props.template?.watermark_rotate || -30,
  rtl: props.report?.settings?.rtl ?? props.template?.rtl ?? false,
})

// ─── Constants ────────────────────────────────────────────────────────────────
const EXPORT_FORMATS = [
  { key: 'pdf', label: 'PDF Document', desc: 'Print-ready PDF', icon: 'fa-solid fa-file-pdf', color: '#ef4444' },
  { key: 'png', label: 'PNG Image', desc: 'High-res image', icon: 'fa-solid fa-file-image', color: '#3b82f6' },
  { key: 'csv', label: 'CSV Export', desc: 'Raw data export', icon: 'fa-solid fa-file-csv', color: '#f59e0b' },
  { key: 'xlsx', label: 'Excel Sheet', desc: 'Data in .xlsx', icon: 'fa-solid fa-file-excel', color: '#16a34a' }
]

const LEFT_TABS = [
  { id: 'elements', label: 'Elements', icon: 'fa-solid fa-puzzle-piece' },
  { id: 'pages', label: 'Pages', icon: 'fa-regular fa-file' },
  { id: 'settings', label: 'Settings', icon: 'fa-solid fa-gear' }
]

const PROP_TABS = [
  { id: 'style', label: 'Style', icon: 'fa-solid fa-palette' },
  { id: 'content', label: 'Content', icon: 'fa-solid fa-pen' }
]

const FONTS = [
  { name: 'DM Sans', value: "'DM Sans', sans-serif" },
  { name: 'Inter', value: "'Inter', sans-serif" },
  { name: 'Georgia', value: 'Georgia, serif' },
  { name: 'Playfair Display', value: "'Playfair Display', serif" }
]

const HANDLES = [
  { dir: 'n', style: { top: '-4px', left: '50%', transform: 'translateX(-50%)', cursor: 'n-resize' } },
  { dir: 's', style: { bottom: '-4px', left: '50%', transform: 'translateX(-50%)', cursor: 's-resize' } },
  { dir: 'e', style: { right: '-4px', top: '50%', transform: 'translateY(-50%)', cursor: 'e-resize' } },
  { dir: 'w', style: { left: '-4px', top: '50%', transform: 'translateY(-50%)', cursor: 'w-resize' } },
  { dir: 'ne', style: { top: '-4px', right: '-4px', cursor: 'ne-resize' } },
  { dir: 'nw', style: { top: '-4px', left: '-4px', cursor: 'nw-resize' } },
  { dir: 'se', style: { bottom: '-4px', right: '-4px', cursor: 'se-resize' } },
  { dir: 'sw', style: { bottom: '-4px', left: '-4px', cursor: 'sw-resize' } }
]

const SHORTCUTS = [
  { key: 'Ctrl+Z', desc: 'Undo' },
  { key: 'Ctrl+Y', desc: 'Redo' },
  { key: 'Ctrl+S', desc: 'Save' },
  { key: 'Ctrl+D', desc: 'Duplicate' },
  { key: 'Delete', desc: 'Delete' },
  { key: 'G', desc: 'Toggle Grid' },
  { key: 'R', desc: 'Toggle Rulers' },
  { key: 'Arrow Keys', desc: 'Nudge 1px' },
  { key: 'Shift+Arrow', desc: 'Nudge 10px' }
]

// Element categories with 50+ elements
const ALL_CATEGORIES = [
  {
    name: 'Text', icon: 'fa-solid fa-font', color: '#6366f1',
    items: [
      { type: 'heading', name: 'Heading', icon: 'fa-solid fa-heading' },
      { type: 'subheading', name: 'Subheading', icon: 'fa-solid fa-h' },
      { type: 'text', name: 'Paragraph', icon: 'fa-solid fa-paragraph' },
      { type: 'quote', name: 'Quote', icon: 'fa-solid fa-quote-left' },
      { type: 'list', name: 'List', icon: 'fa-solid fa-list-ul' },
      { type: 'checklist', name: 'Checklist', icon: 'fa-solid fa-list-check' },
      { type: 'code', name: 'Code Block', icon: 'fa-solid fa-code' }
    ]
  },
  {
    name: 'Data & Charts', icon: 'fa-solid fa-chart-bar', color: '#10b981',
    items: [
      { type: 'bar-chart', name: 'Bar Chart', icon: 'fa-solid fa-chart-bar' },
      { type: 'line-chart', name: 'Line Chart', icon: 'fa-solid fa-chart-line' },
      { type: 'pie-chart', name: 'Pie Chart', icon: 'fa-solid fa-chart-pie' },
      { type: 'metric', name: 'Metric/KPI', icon: 'fa-solid fa-square-poll-vertical' },
      { type: 'progress', name: 'Progress Bar', icon: 'fa-solid fa-bars-progress' },
      { type: 'table', name: 'Table', icon: 'fa-solid fa-table' }
    ]
  },
  {
    name: 'Media', icon: 'fa-solid fa-photo-film', color: '#ec4899',
    items: [
      { type: 'image', name: 'Image', icon: 'fa-solid fa-image' },
      { type: 'icon', name: 'Icon', icon: 'fa-solid fa-star' }
    ]
  },
  {
    name: 'Shapes', icon: 'fa-solid fa-shapes', color: '#f59e0b',
    items: [
      { type: 'rectangle', name: 'Rectangle', icon: 'fa-regular fa-square' },
      { type: 'circle', name: 'Circle', icon: 'fa-regular fa-circle' },
      { type: 'line', name: 'Line', icon: 'fa-solid fa-minus' },
      { type: 'divider', name: 'Divider', icon: 'fa-solid fa-grip-lines' }
    ]
  }
]

// ─── Computed ─────────────────────────────────────────────────────────────────
const zf = computed(() => zoom.value / 100)

const canvasDims = computed(() => {
  const sizes = {
    A4: { portrait: { w: 794, h: 1123 }, landscape: { w: 1123, h: 794 } },
    Letter: { portrait: { w: 816, h: 1056 }, landscape: { w: 1056, h: 816 } },
    Legal: { portrait: { w: 816, h: 1344 }, landscape: { w: 1344, h: 816 } }
  }
  const size = sizes[rs.page_size]?.[rs.orientation] || sizes.A4.portrait
  return { width: size.w, height: size.h }
})

const selectedEl = computed(() => {
  if (!selectedElId.value) return null
  const page = pages.value[activePageIdx.value]
  return page?.elements?.find(e => e.id === selectedElId.value) || null
})

const currentPageEls = computed(() => pages.value[activePageIdx.value]?.elements || [])
const isTextEl = computed(() => ['text', 'heading', 'subheading', 'quote'].includes(selectedEl.value?.type))

const filteredCategories = computed(() => {
  if (!elemSearch.value) return ALL_CATEGORIES
  const q = elemSearch.value.toLowerCase()
  return ALL_CATEGORIES.map(cat => ({
    ...cat,
    items: cat.items.filter(item => item.name.toLowerCase().includes(q))
  })).filter(cat => cat.items.length > 0)
})

const ctxItems = computed(() => {
  if (!ctxMenu.el) return []
  return [
    { icon: 'fa-regular fa-clone', label: 'Duplicate', action: () => duplicateEl(ctxMenu.pi, ctxMenu.el) },
    { icon: 'fa-solid fa-angles-up', label: 'Bring to Front', action: bringToFront },
    { icon: 'fa-solid fa-angles-down', label: 'Send to Back', action: sendToBack },
    { icon: 'fa-solid fa-trash', label: 'Delete', action: () => deleteEl(ctxMenu.pi, ctxMenu.el.id), danger: true }
  ]
})

// ─── Helper Functions ─────────────────────────────────────────────────────────
const createElement = (type, x = 100, y = 100) => {
  const baseStyles = { width: 200, height: 50, zIndex: 1, opacity: 100 }
  
  const presets = {
    heading: { content: 'Heading', styles: { ...baseStyles, width: 400, height: 60, fontSize: 32, fontWeight: '700' } },
    subheading: { content: 'Subheading', styles: { ...baseStyles, width: 300, height: 40, fontSize: 20, fontWeight: '600' } },
    text: { content: 'Lorem ipsum dolor sit amet.', styles: { ...baseStyles, width: 400, height: 100, fontSize: 14 } },
    quote: { content: 'A great quote here.', styles: { ...baseStyles, width: 400, height: 80, fontSize: 16, fontStyle: 'italic' } },
    list: { items: ['Item 1', 'Item 2', 'Item 3'], styles: { ...baseStyles, width: 300, height: 120, fontSize: 14 } },
    checklist: { items: [{ text: 'Task 1', checked: false }, { text: 'Task 2', checked: true }], styles: { ...baseStyles, width: 300, height: 100 } },
    code: { content: 'console.log("Hello World");', language: 'JavaScript', styles: { ...baseStyles, width: 400, height: 120, backgroundColor: '#1e293b', color: '#34d399' } },
    'bar-chart': { chartData: { labels: ['A', 'B', 'C'], values: [30, 50, 80] }, chartTitle: 'Chart', styles: { ...baseStyles, width: 400, height: 300 } },
    'line-chart': { chartData: { labels: ['Jan', 'Feb', 'Mar'], values: [10, 40, 70] }, chartTitle: 'Trend', styles: { ...baseStyles, width: 400, height: 300 } },
    'pie-chart': { chartData: { labels: ['X', 'Y', 'Z'], values: [30, 40, 30] }, chartTitle: 'Distribution', styles: { ...baseStyles, width: 400, height: 300 } },
    metric: { label: 'Revenue', value: '$48,293', change: '+12%', styles: { ...baseStyles, width: 240, height: 100, backgroundColor: '#f8fafc', borderRadius: 12 } },
    progress: { label: 'Progress', value: 65, styles: { ...baseStyles, width: 300, height: 50 } },
    table: { columns: ['Name', 'Value'], data: [{ Name: 'Item 1', Value: '100' }], styles: { ...baseStyles, width: 400, height: 150 } },
    image: { src: '', styles: { ...baseStyles, width: 300, height: 200, borderRadius: 8 } },
    icon: { content: '★', styles: { ...baseStyles, width: 60, height: 60, fontSize: 40 } },
    rectangle: { styles: { ...baseStyles, width: 200, height: 120, backgroundColor: '#e0e7ff', borderRadius: 8 } },
    circle: { styles: { ...baseStyles, width: 100, height: 100, backgroundColor: '#6366f1', borderRadius: '50%' } },
    line: { styles: { ...baseStyles, width: 200, height: 20, borderWidth: 2, color: '#94a3b8' } },
    divider: { styles: { ...baseStyles, width: 400, height: 20, borderWidth: 1, color: '#e2e8f0' } }
  }
  
  const preset = presets[type] || { content: type, styles: baseStyles }
  return {
    id: uuid(),
    type,
    position: { x, y },
    ...preset
  }
}

const getSortedElements = (elements) => {
  return [...(elements || [])].sort((a, b) => (a.styles?.zIndex || 1) - (b.styles?.zIndex || 1))
}

const elWrapStyle = (el) => {
  const s = el.styles || {}
  const f = zf.value
  return {
    left: (el.position?.x || 0) * f + 'px',
    top: (el.position?.y || 0) * f + 'px',
    width: (s.width || 200) * f + 'px',
    height: (s.height || 50) * f + 'px',
    zIndex: s.zIndex || 1,
    opacity: (s.opacity || 100) / 100,
    transform: s.rotate ? `rotate(${s.rotate}deg)` : 'none',
    position: 'absolute',
    borderRadius: s.borderRadius ? s.borderRadius * f + 'px' : undefined,
    border: s.borderWidth ? `${s.borderWidth * f}px ${s.borderStyle || 'solid'} ${s.borderColor || '#000'}` : undefined,
    padding: s.padding ? s.padding * f + 'px' : undefined,
    boxShadow: s.boxShadow === 'sm' ? '0 1px 3px rgba(0,0,0,.12)' : 
                s.boxShadow === 'md' ? '0 4px 12px rgba(0,0,0,.1)' : 
                s.boxShadow === 'lg' ? '0 10px 30px rgba(0,0,0,.15)' : undefined
  }
}

const pageStyle = () => ({
  width: canvasDims.value.width * zf.value + 'px',
  height: canvasDims.value.height * zf.value + 'px',
  backgroundColor: rs.background_color,
  fontFamily: rs.font_family,
  borderRadius: rs.page_radius + 'px',
  backgroundImage: rs.bg_image ? `url(${rs.bg_image})` : 'none',
  backgroundSize: 'cover',
  position: 'relative',
  overflow: 'hidden',
  boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
  margin: '0 auto'
})

const getMiniElStyle = (el) => {
  const scale = 0.1
  return {
    position: 'absolute',
    left: (el.position?.x || 0) * scale + 'px',
    top: (el.position?.y || 0) * scale + 'px',
    width: (el.styles?.width || 100) * scale + 'px',
    height: (el.styles?.height || 50) * scale + 'px',
    backgroundColor: el.styles?.backgroundColor || '#6366f1',
    borderRadius: '2px',
    opacity: 0.7
  }
}

const getElIcon = (type) => {
  const icons = {
    heading: 'fa-solid fa-heading',
    text: 'fa-solid fa-paragraph',
    image: 'fa-solid fa-image',
    'bar-chart': 'fa-solid fa-chart-bar',
    metric: 'fa-solid fa-square-poll-vertical',
    table: 'fa-solid fa-table',
    list: 'fa-solid fa-list',
    rectangle: 'fa-regular fa-square',
    circle: 'fa-regular fa-circle'
  }
  return icons[type] || 'fa-solid fa-cube'
}

// ─── Element Operations ──────────────────────────────────────────────────────
const addElement = (type) => {
  const x = 100 + (currentPageEls.value.length % 5) * 20
  const y = 100 + Math.floor(currentPageEls.value.length / 5) * 30
  const el = createElement(type, x, y)
  pages.value[activePageIdx.value].elements.push(el)
  selectedElId.value = el.id
  pushHistory()
  showToast(`Added ${type} element`)
}

const deleteEl = (pi, id) => {
  pages.value[pi].elements = pages.value[pi].elements.filter(e => e.id !== id)
  if (selectedElId.value === id) selectedElId.value = null
  pushHistory()
}

const deleteSelectedEl = () => {
  if (selectedEl.value) deleteEl(activePageIdx.value, selectedEl.value.id)
}

const duplicateEl = (pi, el) => {
  const clone = JSON.parse(JSON.stringify(el))
  clone.id = uuid()
  clone.position = { x: el.position.x + 20, y: el.position.y + 20 }
  pages.value[pi].elements.push(clone)
  selectedElId.value = clone.id
  pushHistory()
  showToast('Element duplicated')
}

const handleElementUpdate = (updates) => {
  if (selectedEl.value) {
    Object.assign(selectedEl.value, updates)
    pushHistory()
  }
}

// ─── Page Operations ─────────────────────────────────────────────────────────
const addPage = () => {
  const newPage = {
    id: uuid(),
    label: `Page ${pages.value.length + 1}`,
    elements: []
  }
  pages.value.push(newPage)
  activePageIdx.value = pages.value.length - 1
  pushHistory()
  showToast('Page added')
}

const deletePage = (pi) => {
  if (pages.value.length <= 1) {
    showToast('Cannot delete the last page', 'error')
    return
  }
  pages.value.splice(pi, 1)
  if (activePageIdx.value >= pages.value.length) activePageIdx.value = pages.value.length - 1
  pushHistory()
  showToast('Page deleted')
}

const duplicatePage = (pi) => {
  const clone = JSON.parse(JSON.stringify(pages.value[pi]))
  clone.id = uuid()
  clone.label = `${clone.label} (Copy)`
  pages.value.splice(pi + 1, 0, clone)
  pushHistory()
  showToast('Page duplicated')
}

const renamePage = (pi, label) => {
  pages.value[pi].label = label
  pushHistory()
}

const selectPage = (pi) => {
  activePageIdx.value = pi
  selectedElId.value = null
}

const deselectAll = () => {
  selectedElId.value = null
}

// ─── Layer Operations ────────────────────────────────────────────────────────
const bringToFront = () => {
  if (!selectedEl.value) return
  const maxZ = Math.max(...currentPageEls.value.map(e => e.styles?.zIndex || 1))
  selectedEl.value.styles.zIndex = maxZ + 1
  pushHistory()
}

const sendToBack = () => {
  if (!selectedEl.value) return
  const minZ = Math.min(...currentPageEls.value.map(e => e.styles?.zIndex || 1))
  selectedEl.value.styles.zIndex = Math.max(1, minZ - 1)
  pushHistory()
}

// ─── Drag and Drop ───────────────────────────────────────────────────────────
let dragType = null

const onElemDragStart = (e, type) => {
  dragType = type
  e.dataTransfer.setData('text/plain', type)
  e.dataTransfer.effectAllowed = 'copy'
}

const onCanvasDragOver = (e) => {
  e.preventDefault()
  isDragOverPage.value = true
}

const onCanvasDrop = (e) => {
  e.preventDefault()
  isDragOverPage.value = false
  const type = dragType || e.dataTransfer.getData('text/plain')
  if (!type) return
  
  const rect = e.currentTarget.getBoundingClientRect()
  const x = (e.clientX - rect.left) / zf.value
  const y = (e.clientY - rect.top) / zf.value
  
  addElementAt(type, activePageIdx.value, Math.max(10, x), Math.max(10, y))
  dragType = null
}

const onPageDrop = (e, pi) => {
  e.preventDefault()
  isDragOverPage.value = false
  const type = dragType || e.dataTransfer.getData('text/plain')
  if (!type) return
  
  selectPage(pi)
  const rect = e.currentTarget.getBoundingClientRect()
  const x = (e.clientX - rect.left) / zf.value
  const y = (e.clientY - rect.top) / zf.value
  
  addElementAt(type, pi, Math.max(10, x), Math.max(10, y))
  dragType = null
}

const addElementAt = (type, pi, x, y) => {
  const el = createElement(type, x, y)
  pages.value[pi].elements.push(el)
  selectedElId.value = el.id
  pushHistory()
  if (type.includes('chart')) refreshChart(el)
}

// ─── Element Dragging ────────────────────────────────────────────────────────
let isDraggingEl = false
let dragStartX = 0, dragStartY = 0
let elementStartX = 0, elementStartY = 0

const onElMousedown = (e, el, pi) => {
  if (e.button !== 0) return
  if (pi !== activePageIdx.value) selectPage(pi)
  selectedElId.value = el.id
  
  if (el.locked || activeTool.value === 'select') return
  
  isDraggingEl = true
  dragStartX = e.clientX
  dragStartY = e.clientY
  elementStartX = el.position.x
  elementStartY = el.position.y
  
  window.addEventListener('mousemove', onElDragMove)
  window.addEventListener('mouseup', onElDragUp)
  e.preventDefault()
}

const onElDragMove = (e) => {
  if (!isDraggingEl || !selectedEl.value) return
  
  let dx = (e.clientX - dragStartX) / zf.value
  let dy = (e.clientY - dragStartY) / zf.value
  
  let newX = elementStartX + dx
  let newY = elementStartY + dy
  
  if (snapToGrid.value) {
    newX = Math.round(newX / gridSize.value) * gridSize.value
    newY = Math.round(newY / gridSize.value) * gridSize.value
  }
  
  selectedEl.value.position.x = Math.max(0, newX)
  selectedEl.value.position.y = Math.max(0, newY)
  
  showSnapGuides(newX, newY, selectedEl.value.styles?.width || 200, selectedEl.value.styles?.height || 50)
}

const onElDragUp = () => {
  isDraggingEl = false
  snapGuide.h = null
  snapGuide.v = null
  window.removeEventListener('mousemove', onElDragMove)
  window.removeEventListener('mouseup', onElDragUp)
  pushHistory()
}

const showSnapGuides = (x, y, w, h) => {
  const snapThreshold = 5
  if (Math.abs(x) < snapThreshold) snapGuide.v = x
  else if (Math.abs(x + w - canvasDims.value.width) < snapThreshold) snapGuide.v = x + w
  else snapGuide.v = null
  
  if (Math.abs(y) < snapThreshold) snapGuide.h = y
  else if (Math.abs(y + h - canvasDims.value.height) < snapThreshold) snapGuide.h = y + h
  else snapGuide.h = null
}

// ─── Resize Operations ───────────────────────────────────────────────────────
let isResizing = false
let resizeDir = ''
let resizeStartX = 0, resizeStartY = 0
let resizeStartW = 0, resizeStartH = 0
let resizeStartPosX = 0, resizeStartPosY = 0

const startResize = (dir, e) => {
  if (!selectedEl.value) return
  e.preventDefault()
  isResizing = true
  resizeDir = dir
  resizeStartX = e.clientX
  resizeStartY = e.clientY
  resizeStartW = selectedEl.value.styles.width
  resizeStartH = selectedEl.value.styles.height
  resizeStartPosX = selectedEl.value.position.x
  resizeStartPosY = selectedEl.value.position.y
  
  window.addEventListener('mousemove', onResizeMove)
  window.addEventListener('mouseup', onResizeUp)
}

const onResizeMove = (e) => {
  if (!isResizing || !selectedEl.value) return
  
  const dx = (e.clientX - resizeStartX) / zf.value
  const dy = (e.clientY - resizeStartY) / zf.value
  const minSize = 20
  
  if (resizeDir.includes('e')) {
    selectedEl.value.styles.width = Math.max(minSize, resizeStartW + dx)
  }
  if (resizeDir.includes('s')) {
    selectedEl.value.styles.height = Math.max(minSize, resizeStartH + dy)
  }
  if (resizeDir.includes('w')) {
    const newW = Math.max(minSize, resizeStartW - dx)
    selectedEl.value.position.x = resizeStartPosX + (resizeStartW - newW)
    selectedEl.value.styles.width = newW
  }
  if (resizeDir.includes('n')) {
    const newH = Math.max(minSize, resizeStartH - dy)
    selectedEl.value.position.y = resizeStartPosY + (resizeStartH - newH)
    selectedEl.value.styles.height = newH
  }
}

const onResizeUp = () => {
  isResizing = false
  window.removeEventListener('mousemove', onResizeMove)
  window.removeEventListener('mouseup', onResizeUp)
  pushHistory()
}

// ─── Rotation ────────────────────────────────────────────────────────────────
const startRotation = (e, el) => {
  const rect = e.currentTarget.parentElement.getBoundingClientRect()
  const centerX = rect.left + rect.width / 2
  const centerY = rect.top + rect.height / 2
  
  const onMouseMove = (moveEvent) => {
    const angle = Math.atan2(moveEvent.clientY - centerY, moveEvent.clientX - centerX) * 180 / Math.PI
    el.styles.rotate = Math.round(angle + 90)
  }
  
  const onMouseUp = () => {
    window.removeEventListener('mousemove', onMouseMove)
    window.removeEventListener('mouseup', onMouseUp)
    pushHistory()
  }
  
  window.addEventListener('mousemove', onMouseMove)
  window.addEventListener('mouseup', onMouseUp)
  e.preventDefault()
}

// ─── Text Style Toggles ──────────────────────────────────────────────────────
const toggleTextStyle = (style) => {
  if (!selectedEl.value) return
  
  switch(style) {
    case 'bold':
      selectedEl.value.styles.fontWeight = selectedEl.value.styles?.fontWeight === '700' ? '400' : '700'
      break
    case 'italic':
      selectedEl.value.styles.fontStyle = selectedEl.value.styles?.fontStyle === 'italic' ? 'normal' : 'italic'
      break
    case 'underline':
      selectedEl.value.styles.textDecoration = selectedEl.value.styles?.textDecoration === 'underline' ? 'none' : 'underline'
      break
  }
  pushHistory()
}

// ─── Chart Functions ─────────────────────────────────────────────────────────
const refreshChart = (el) => {
  const canvasId = `chart-${el.id}`
  nextTick(() => {
    const canvas = document.getElementById(canvasId)
    if (!canvas) return
    
    if (chartInstances.has(el.id)) {
      chartInstances.get(el.id).destroy()
      chartInstances.delete(el.id)
    }
    
    const ctx = canvas.getContext('2d')
    const type = {
      'bar-chart': 'bar',
      'line-chart': 'line',
      'pie-chart': 'pie'
    }[el.type] || 'bar'
    
    const chart = new Chart(ctx, {
      type: type,
      data: {
        labels: el.chartData?.labels || [],
        datasets: [{
          label: el.chartTitle || 'Data',
          data: el.chartData?.values || [],
          backgroundColor: '#6366f1',
          borderColor: '#4f46e5',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    })
    
    chartInstances.set(el.id, chart)
  })
}

const updateChartLabels = (value) => {
  if (!selectedEl.value) return
  const labels = value.split(',').map(s => s.trim()).filter(Boolean)
  if (!selectedEl.value.chartData) selectedEl.value.chartData = {}
  selectedEl.value.chartData.labels = labels
  refreshChart(selectedEl.value)
}

const updateChartValues = (value) => {
  if (!selectedEl.value) return
  const values = value.split(',').map(s => parseFloat(s.trim())).filter(n => !isNaN(n))
  if (!selectedEl.value.chartData) selectedEl.value.chartData = {}
  selectedEl.value.chartData.values = values
  refreshChart(selectedEl.value)
}

// ─── History (Undo/Redo) ─────────────────────────────────────────────────────
const pushHistory = () => {
  const snapshot = JSON.stringify(pages.value)
  if (snapshot === history.value[histIdx.value]) return
  
  history.value = history.value.slice(0, histIdx.value + 1)
  history.value.push(snapshot)
  histIdx.value = history.value.length - 1
  
  if (history.value.length > 100) {
    history.value.shift()
    histIdx.value--
  }
  
  scheduleAutoSave()
}

const undo = () => {
  if (histIdx.value <= 0) return
  histIdx.value--
  pages.value = JSON.parse(history.value[histIdx.value])
  nextTick(() => refreshAllCharts())
}

const redo = () => {
  if (histIdx.value >= history.value.length - 1) return
  histIdx.value++
  pages.value = JSON.parse(history.value[histIdx.value])
  nextTick(() => refreshAllCharts())
}

const refreshAllCharts = () => {
  pages.value.forEach(page => {
    page.elements?.forEach(el => {
      if (el.type.includes('chart')) refreshChart(el)
    })
  })
}

// ─── Save & Export ───────────────────────────────────────────────────────────
let saveTimer = null

const scheduleAutoSave = () => {
  clearTimeout(saveTimer)
  saveTimer = setTimeout(saveReport, 3000)
}

const saveReport = async () => {
  saveStatus.value = 'saving'
  try {
    await axios.put(route('reports.update', props.report.slug), {
      title: reportTitle.value,
      content: pages.value,
      settings: {
        page_size: rs.page_size,
        orientation: rs.orientation,
        primary_color: rs.primary_color,
        accent_color: rs.accent_color,
        background_color: rs.background_color,
        text_color: rs.text_color,
        font_family: rs.font_family,
        font_size: rs.font_size,
        margin: rs.margin,
        page_radius: rs.page_radius,
        bg_image: rs.bg_image,
        show_header: rs.show_header,
        header_text: rs.header_text,
        header_height: rs.header_height,
        header_bg: rs.header_bg,
        header_text_color: rs.header_text_color,
        header_font_size: rs.header_font_size,
        header_align: rs.header_align,
        show_footer: rs.show_footer,
        footer_left: rs.footer_left,
        footer_center: rs.footer_center,
        footer_right: rs.footer_right,
        footer_height: rs.footer_height,
        footer_bg: rs.footer_bg,
        footer_text_color: rs.footer_text_color,
        footer_font_size: rs.footer_font_size,
        watermark: rs.watermark,
        watermark_color: rs.watermark_color,
        watermark_opacity: rs.watermark_opacity,
        watermark_rotate: rs.watermark_rotate,
        rtl: rs.rtl
      }
    })
    saveStatus.value = 'saved'
    setTimeout(() => { if (saveStatus.value === 'saved') saveStatus.value = 'idle' }, 2000)
  } catch (error) {
    console.error('Save failed:', error)
    saveStatus.value = 'idle'
    showToast('Failed to save report', 'error')
  }
}

const previewReport = () => {
  window.open(route('reports.preview', props.report.slug), '_blank')
}

const goBack = () => {
  router.get(route('reports.index'))
}

const exportAs = async (format) => {
  showExportMenu.value = false
  showToast(`Exporting as ${format.toUpperCase()}...`, 'success')
  
  if (format === 'pdf') {
    window.open(route('reports.download', props.report.slug), '_blank')
  } else if (format === 'png') {
    const element = document.querySelector('.page-canvas')
    if (element && html2canvas) {
      const canvas = await html2canvas(element, { scale: 2 })
      const link = document.createElement('a')
      link.download = `${reportTitle.value}.png`
      link.href = canvas.toDataURL()
      link.click()
    } else {
      showToast('PNG export requires html2canvas library', 'error')
    }
  } else {
    showToast(`${format.toUpperCase()} export coming soon`, 'info')
  }
}

// ─── Zoom & View ─────────────────────────────────────────────────────────────
const zoomIn = () => {
  zoom.value = Math.min(200, zoom.value + 10)
  updateRulers()
}

const zoomOut = () => {
  zoom.value = Math.max(25, zoom.value - 10)
  updateRulers()
}

const fitToScreen = () => {
  if (!canvasScroll.value) return
  const container = canvasScroll.value
  const availableWidth = container.clientWidth - 80
  const availableHeight = container.clientHeight - 80
  const scaleX = (availableWidth / canvasDims.value.width) * 100
  const scaleY = (availableHeight / canvasDims.value.height) * 100
  zoom.value = Math.min(150, Math.max(25, Math.min(scaleX, scaleY)))
}

const onCtrlWheel = (e) => {
  const delta = e.deltaY > 0 ? -5 : 5
  zoom.value = Math.min(200, Math.max(25, zoom.value + delta))
}

// ─── Rulers ──────────────────────────────────────────────────────────────────
const updateRulers = () => {
  if (!showRulers.value) return
  
  nextTick(() => {
    drawHorizontalRuler()
    drawVerticalRuler()
  })
}

const drawHorizontalRuler = () => {
  const canvas = hRulerCanvas.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const width = canvas.parentElement?.clientWidth || 1000
  canvas.width = width
  canvas.height = 24
  
  ctx.fillStyle = isDark.value ? '#1e293b' : '#f1f5f9'
  ctx.fillRect(0, 0, width, 24)
  ctx.strokeStyle = isDark.value ? '#334155' : '#cbd5e1'
  ctx.fillStyle = isDark.value ? '#94a3b8' : '#64748b'
  ctx.font = '9px monospace'
  
  const step = 50 * zf.value
  for (let x = 0; x < canvasDims.value.width * zf.value; x += step) {
    ctx.beginPath()
    ctx.moveTo(x, 16)
    ctx.lineTo(x, 24)
    ctx.stroke()
    if (x % (step * 2) === 0) {
      ctx.fillText(Math.round(x / zf.value), x + 2, 12)
    }
  }
}

const drawVerticalRuler = () => {
  const canvas = vRulerCanvas.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const height = canvas.parentElement?.clientHeight || 1000
  canvas.width = 24
  canvas.height = height
  
  ctx.fillStyle = isDark.value ? '#1e293b' : '#f1f5f9'
  ctx.fillRect(0, 0, 24, height)
  ctx.strokeStyle = isDark.value ? '#334155' : '#cbd5e1'
  ctx.fillStyle = isDark.value ? '#94a3b8' : '#64748b'
  ctx.font = '9px monospace'
  
  const step = 50 * zf.value
  for (let y = 0; y < canvasDims.value.height * zf.value; y += step) {
    ctx.beginPath()
    ctx.moveTo(16, y)
    ctx.lineTo(24, y)
    ctx.stroke()
    if (y % (step * 2) === 0) {
      ctx.save()
      ctx.translate(12, y + 2)
      ctx.rotate(-Math.PI / 2)
      ctx.fillText(Math.round(y / zf.value), 0, 0)
      ctx.restore()
    }
  }
}

// ─── Context Menu ────────────────────────────────────────────────────────────
const openCtxMenu = (e, el, pi) => {
  ctxMenu.show = true
  ctxMenu.x = e.clientX
  ctxMenu.y = e.clientY
  ctxMenu.el = el
  ctxMenu.pi = pi
  selectedElId.value = el.id
}

// ─── Toast Notifications ─────────────────────────────────────────────────────
const showToast = (message, type = 'success') => {
  toast.message = message
  toast.type = type
  toast.show = true
  setTimeout(() => {
    toast.show = false
  }, 3000)
}

// ─── Keyboard Shortcuts ──────────────────────────────────────────────────────
const onKeyDown = (e) => {
  const target = e.target
  if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable) return
  
  if (e.ctrlKey || e.metaKey) {
    switch(e.key) {
      case 'z':
        e.preventDefault()
        undo()
        break
      case 'y':
        e.preventDefault()
        redo()
        break
      case 's':
        e.preventDefault()
        saveReport()
        break
      case 'd':
        e.preventDefault()
        if (selectedEl.value) duplicateEl(activePageIdx.value, selectedEl.value)
        break
    }
  } else {
    switch(e.key) {
      case 'Delete':
      case 'Backspace':
        if (selectedEl.value) deleteSelectedEl()
        break
      case 'Escape':
        deselectAll()
        ctxMenu.show = false
        break
      case 'g':
      case 'G':
        showGrid.value = !showGrid.value
        break
      case 'r':
      case 'R':
        showRulers.value = !showRulers.value
        if (showRulers.value) updateRulers()
        break
      case 'ArrowUp':
      case 'ArrowDown':
      case 'ArrowLeft':
      case 'ArrowRight':
        if (selectedEl.value && !selectedEl.value.locked) {
          e.preventDefault()
          const step = e.shiftKey ? 10 : 1
          switch(e.key) {
            case 'ArrowUp': selectedEl.value.position.y -= step; break
            case 'ArrowDown': selectedEl.value.position.y += step; break
            case 'ArrowLeft': selectedEl.value.position.x -= step; break
            case 'ArrowRight': selectedEl.value.position.x += step; break
          }
          pushHistory()
        }
        break
    }
  }
}

// ─── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(() => {
  window.addEventListener('keydown', onKeyDown)
  document.addEventListener('click', (e) => {
    if (exportRef.value && !exportRef.value.contains(e.target)) {
      showExportMenu.value = false
    }
    if (ctxMenu.show) ctxMenu.show = false
  })
  
  nextTick(() => {
    fitToScreen()
    if (showRulers.value) updateRulers()
    refreshAllCharts()
  })
  
  const savedDark = localStorage.getItem('rb-dark')
  if (savedDark !== null) isDark.value = savedDark === 'true'
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeyDown)
  chartInstances.forEach(chart => chart.destroy())
  chartInstances.clear()
  clearTimeout(saveTimer)
})

watch(() => isDark.value, (val) => {
  localStorage.setItem('rb-dark', val)
  if (showRulers.value) updateRulers()
})

watch([() => zoom.value, () => showRulers.value], () => {
  if (showRulers.value) updateRulers()
})

watch(() => pages.value, () => {
  nextTick(() => refreshAllCharts())
}, { deep: true })

const toggleCat = (catName) => {
  const index = collapsedCats.value.indexOf(catName)
  if (index > -1) {
    collapsedCats.value.splice(index, 1)
  } else {
    collapsedCats.value.push(catName)
  }
}

// ─── Sub-components ─────────────────────────────────────────────────────────
const SettingsSection = {
  name: 'SettingsSection',
  props: { title: String, icon: String, open: { type: Boolean, default: false } },
  data() { return { isOpen: this.open } },
  template: `
    <div class="settings-section">
      <button @click="isOpen = !isOpen" class="section-toggle">
        <i :class="icon"></i>
        <span>{{ title }}</span>
        <i class="fa-solid fa-chevron-down" :class="{ rotated: isOpen }"></i>
      </button>
      <div v-show="isOpen" class="section-body"><slot/></div>
    </div>
  `
}

const PropSection = {
  name: 'PropSection',
  props: { title: String, icon: String, open: { type: Boolean, default: false } },
  data() { return { isOpen: this.open } },
  template: `
    <div class="prop-section">
      <button @click="isOpen = !isOpen" class="section-toggle">
        <i :class="icon"></i>
        <span>{{ title }}</span>
        <i class="fa-solid fa-chevron-down" :class="{ rotated: isOpen }"></i>
      </button>
      <div v-show="isOpen" class="section-body"><slot/></div>
    </div>
  `
}

const SettingRow = {
  name: 'SettingRow',
  props: { label: String },
  template: `
    <div class="setting-row">
      <label class="setting-label">{{ label }}</label>
      <div class="setting-control"><slot/></div>
    </div>
  `
}

const PropField = {
  name: 'PropField',
  props: { label: String },
  template: `
    <div class="prop-field">
      <label class="prop-label">{{ label }}</label>
      <slot/>
    </div>
  `
}

const Toggle = {
  name: 'Toggle',
  props: { value: Boolean },
  emits: ['toggle'],
  template: `
    <button @click="$emit('toggle')" class="toggle-switch" :class="{ on: value }">
      <span class="toggle-thumb" :class="{ on: value }"></span>
    </button>
  `
}

const ColorPicker = {
  name: 'ColorPicker',
  props: { value: String },
  emits: ['update'],
  template: `
    <div class="color-picker-row">
      <input type="color" :value="value === 'transparent' ? '#ffffff' : value" @input="$emit('update', $event.target.value)" class="color-swatch" />
      <input type="text" :value="value" @input="$emit('update', $event.target.value)" class="color-text" placeholder="#000000" />
      <button @click="$emit('update', 'transparent')" class="transparent-btn" title="Transparent">∅</button>
    </div>
  `
}
</script>

<style scoped>
/* Modern CSS with glassmorphism and smooth animations */
.editor-shell {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  background: linear-gradient(135deg, #f5f7fa 0%, #eef2f6 100%);
  font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  transition: background 0.3s ease;
}

.editor-shell.dark {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

/* Topbar with glass effect */
.editor-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 8px 20px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  z-index: 100;
  flex-shrink: 0;
}

.dark .editor-topbar {
  background: rgba(30, 41, 59, 0.95);
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.title-input {
  font-size: 16px;
  font-weight: 600;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  outline: none;
  padding: 6px 8px;
  color: #1e293b;
  transition: all 0.2s;
}

.dark .title-input {
  color: #f1f5f9;
}

.title-input:focus {
  border-bottom-color: #6366f1;
}

.save-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}

.save-badge.saving {
  background: #fef3c7;
  color: #d97706;
}

.save-badge.saved {
  background: #d1fae5;
  color: #059669;
}

.topbar-center {
  display: flex;
  align-items: center;
  gap: 4px;
  background: rgba(0, 0, 0, 0.04);
  padding: 4px 8px;
  border-radius: 12px;
}

.dark .topbar-center {
  background: rgba(255, 255, 255, 0.04);
}

.tool-group {
  display: flex;
  gap: 2px;
}

.tool-btn {
  width: 34px;
  height: 34px;
  border: none;
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 14px;
  transition: all 0.2s;
}

.dark .tool-btn {
  color: #94a3b8;
}

.tool-btn:hover {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.tool-btn.active {
  background: #6366f1;
  color: white;
}

.tool-sep {
  width: 1px;
  height: 24px;
  background: rgba(0, 0, 0, 0.1);
  margin: 0 6px;
}

.dark .tool-sep {
  background: rgba(255, 255, 255, 0.1);
}

.zoom-controls {
  display: flex;
  align-items: center;
  gap: 2px;
}

.zoom-display {
  min-width: 50px;
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: #6366f1;
  cursor: pointer;
  padding: 4px 8px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  justify-content: flex-end;
}

.btn-save, .btn-preview, .btn-export {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save {
  background: #1e293b;
  color: white;
}

.btn-save:hover {
  background: #0f172a;
  transform: translateY(-1px);
}

.btn-preview {
  background: #6366f1;
  color: white;
}

.btn-preview:hover {
  background: #4f46e5;
  transform: translateY(-1px);
}

.btn-export {
  background: #059669;
  color: white;
}

.btn-export:hover {
  background: #047857;
  transform: translateY(-1px);
}

/* Editor Body */
.editor-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* Left Sidebar */
.left-sidebar {
  width: 280px;
  flex-shrink: 0;
  background: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: relative;
}

.left-sidebar.collapsed {
  width: 50px;
}

.dark .left-sidebar {
  background: #1e293b;
  border-right-color: #334155;
}

.sidebar-collapse-btn {
  position: absolute;
  right: -12px;
  top: 20px;
  width: 24px;
  height: 24px;
  border-radius: 12px;
  background: white;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  color: #64748b;
  transition: all 0.2s;
}

.dark .sidebar-collapse-btn {
  background: #1e293b;
  border-color: #334155;
  color: #94a3b8;
}

.sidebar-collapse-btn:hover {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

.sidebar-tabs {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
  padding: 8px 12px 0;
  gap: 4px;
}

.dark .sidebar-tabs {
  border-bottom-color: #334155;
}

.sidebar-tab {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px 4px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  color: #64748b;
  font-size: 10px;
  font-weight: 600;
  transition: all 0.2s;
}

.dark .sidebar-tab {
  color: #94a3b8;
}

.sidebar-tab i {
  font-size: 16px;
}

.sidebar-tab.active {
  color: #6366f1;
  border-bottom-color: #6366f1;
}

.sidebar-tab:hover:not(.active) {
  color: #1e293b;
}

.dark .sidebar-tab:hover:not(.active) {
  color: #f1f5f9;
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
}

/* Search */
.search-wrap {
  position: relative;
  margin-bottom: 16px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 12px;
}

.search-input {
  width: 100%;
  padding: 8px 12px 8px 32px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  font-size: 13px;
  outline: none;
  transition: all 0.2s;
}

.dark .search-input {
  background: #334155;
  border-color: #475569;
  color: #f1f5f9;
}

.search-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-clear {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
}

/* Elements Grid */
.elements-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.elem-category {
  border-radius: 12px;
  overflow: hidden;
}

.cat-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  width: 100%;
  background: #f8fafc;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
  border-radius: 12px;
}

.dark .cat-header {
  background: #334155;
}

.cat-header:hover {
  background: #f1f5f9;
}

.dark .cat-header:hover {
  background: #475569;
}

.cat-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.cat-name {
  flex: 1;
  text-align: left;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}

.dark .cat-name {
  color: #f1f5f9;
}

.cat-count {
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 12px;
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.cat-chevron {
  font-size: 10px;
  transition: transform 0.2s;
  color: #94a3b8;
}

.cat-chevron.rotated {
  transform: rotate(180deg);
}

.elem-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  padding: 12px;
}

.elem-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 10px 6px;
  border-radius: 10px;
  background: white;
  cursor: grab;
  transition: all 0.2s;
}

.dark .elem-card {
  background: #1e293b;
}

.elem-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.elem-card:active {
  cursor: grabbing;
}

.elem-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 16px;
}

.elem-name {
  font-size: 11px;
  font-weight: 500;
  color: #1e293b;
}

.dark .elem-name {
  color: #cbd5e1;
}

/* Pages Tab */
.pages-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.pages-title {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

.dark .pages-title {
  color: #f1f5f9;
}

.btn-add-page {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #6366f1;
  border: none;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add-page:hover {
  background: #4f46e5;
  transform: scale(1.05);
}

.pages-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
  max-height: calc(100vh - 200px);
  overflow-y: auto;
}

.page-thumb {
  display: flex;
  gap: 12px;
  padding: 10px;
  border: 2px solid transparent;
  border-radius: 12px;
  background: #f8fafc;
  cursor: pointer;
  transition: all 0.2s;
}

.dark .page-thumb {
  background: #334155;
}

.page-thumb:hover {
  background: #f1f5f9;
  transform: translateX(4px);
}

.dark .page-thumb:hover {
  background: #475569;
}

.page-thumb.active {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

.page-mini-preview {
  width: 50px;
  height: 70px;
  border-radius: 6px;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.mini-el {
  position: absolute;
}

.page-thumb-info {
  flex: 1;
  min-width: 0;
}

.page-label-input {
  font-size: 12px;
  font-weight: 600;
  background: transparent;
  border: none;
  outline: none;
  width: 100%;
  color: #1e293b;
}

.dark .page-label-input {
  color: #f1f5f9;
}

.page-el-count {
  font-size: 10px;
  color: #64748b;
}

.dark .page-el-count {
  color: #94a3b8;
}

.page-thumb-actions {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.page-thumb-actions button {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  border: none;
  background: rgba(0, 0, 0, 0.05);
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.page-thumb-actions button:hover {
  background: #ef4444;
  color: white;
}

.btn-add-page-full {
  width: 100%;
  padding: 10px;
  border: 2px dashed #cbd5e1;
  border-radius: 10px;
  background: transparent;
  cursor: pointer;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-add-page-full:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

/* Settings Panel */
.settings-panel {
  padding: 0;
}

.settings-section {
  border-bottom: 1px solid #e2e8f0;
}

.dark .settings-section {
  border-bottom-color: #334155;
}

.section-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  width: 100%;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  transition: background 0.2s;
}

.dark .section-toggle {
  color: #f1f5f9;
}

.section-toggle:hover {
  background: rgba(0, 0, 0, 0.03);
}

.section-toggle i:first-child {
  width: 18px;
  color: #6366f1;
}

.section-toggle .fa-chevron-down {
  margin-left: auto;
  transition: transform 0.2s;
  font-size: 10px;
  color: #94a3b8;
}

.section-toggle .fa-chevron-down.rotated {
  transform: rotate(180deg);
}

.section-body {
  padding: 0 16px 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.setting-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.setting-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
}

.dark .setting-label {
  color: #94a3b8;
}

.setting-control select,
.setting-control input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  font-size: 13px;
  outline: none;
  transition: all 0.2s;
}

.dark .setting-control select,
.dark .setting-control input {
  background: #334155;
  border-color: #475569;
  color: #f1f5f9;
}

.setting-control select:focus,
.setting-control input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.btn-group {
  display: flex;
  gap: 6px;
}

.btn-group-item {
  flex: 1;
  padding: 8px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  color: #64748b;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.dark .btn-group-item {
  background: #334155;
  border-color: #475569;
  color: #94a3b8;
}

.btn-group-item.active {
  background: #6366f1;
  border-color: #6366f1;
  color: white;
}

.color-picker-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.color-swatch {
  width: 40px;
  height: 40px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  padding: 2px;
}

.color-text {
  flex: 1;
  padding: 8px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 13px;
  font-family: monospace;
}

.transparent-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s;
}

.transparent-btn:hover {
  background: #f1f5f9;
  border-color: #6366f1;
}

.toggle-switch {
  width: 44px;
  height: 24px;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  background: #cbd5e1;
  position: relative;
  transition: background 0.2s;
}

.toggle-switch.on {
  background: #6366f1;
}

.toggle-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  border-radius: 10px;
  background: white;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-thumb.on {
  transform: translateX(20px);
}

/* Sidebar Collapsed Icons */
.sidebar-collapsed-icons,
.panel-collapsed-icons {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 16px 0;
  gap: 16px;
}

.collapsed-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  cursor: pointer;
  color: #64748b;
  font-size: 18px;
  transition: all 0.2s;
}

.collapsed-icon:hover {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

/* Canvas Area */
.canvas-area {
  flex: 1;
  position: relative;
  overflow: hidden;
  background: #e2e8f0;
}

.dark .canvas-area {
  background: #0f172a;
}

/* Rulers */
.ruler {
  position: absolute;
  z-index: 20;
  background: #f8fafc;
  overflow: hidden;
}

.dark .ruler {
  background: #1e293b;
}

.ruler-h {
  top: 0;
  left: 24px;
  right: 0;
  height: 24px;
  border-bottom: 1px solid #cbd5e1;
}

.dark .ruler-h {
  border-bottom-color: #334155;
}

.ruler-v {
  top: 24px;
  left: 0;
  bottom: 0;
  width: 24px;
  border-right: 1px solid #cbd5e1;
}

.dark .ruler-v {
  border-right-color: #334155;
}

.ruler-corner {
  position: absolute;
  top: 0;
  left: 0;
  width: 24px;
  height: 24px;
  background: #f8fafc;
  border-right: 1px solid #cbd5e1;
  border-bottom: 1px solid #cbd5e1;
  z-index: 21;
}

.dark .ruler-corner {
  background: #1e293b;
  border-right-color: #334155;
  border-bottom-color: #334155;
}

/* Canvas Scroll */
.canvas-scroll {
  flex: 1;
  overflow: auto;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  position: relative;
}

.canvas-scroll::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.canvas-scroll::-webkit-scrollbar-track {
  background: #e2e8f0;
}

.dark .canvas-scroll::-webkit-scrollbar-track {
  background: #334155;
}

.canvas-scroll::-webkit-scrollbar-thumb {
  background: #6366f1;
  border-radius: 4px;
}

.canvas-centering {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px;
  min-height: 100%;
}

/* Page Wrapper */
.page-wrapper {
  position: relative;
  margin-bottom: 48px;
}

.page-label-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  padding-left: 8px;
}

.page-label-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
}

.dark .page-label-tag {
  background: rgba(255, 255, 255, 0.05);
  color: #94a3b8;
}

.page-label-tag.current {
  background: #6366f1;
  color: white;
}

/* Page Canvas */
.page-canvas {
  position: relative;
  overflow: hidden;
  background: white;
  transition: box-shadow 0.3s ease;
}

.dark .page-canvas {
  background: #1e293b;
}

.page-wrapper.page-active .page-canvas {
  box-shadow: 0 0 0 2px #6366f1, 0 20px 40px rgba(0, 0, 0, 0.2);
}

/* Grid Overlay */
.grid-overlay {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
  z-index: 5;
}

/* Header & Footer */
.page-header-bar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  padding: 0 20px;
  z-index: 10;
}

.page-footer-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  z-index: 10;
  font-size: 11px;
}

/* Watermark */
.watermark-layer {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  z-index: 8;
  overflow: hidden;
}

/* Elements */
.element-node {
  position: absolute;
  cursor: move;
  transition: box-shadow 0.1s ease;
}

.element-node.selected {
  z-index: 1000;
}

.element-node.locked {
  cursor: not-allowed;
  opacity: 0.7;
}

.element-node.hidden {
  display: none;
}

.sel-border {
  position: absolute;
  inset: -2px;
  border: 2px solid #6366f1;
  border-radius: 2px;
  pointer-events: none;
  z-index: 90;
  animation: pulse 1s ease-in-out;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.resize-handle {
  position: absolute;
  width: 10px;
  height: 10px;
  background: white;
  border: 2px solid #6366f1;
  border-radius: 2px;
  z-index: 100;
  transition: transform 0.1s;
}

.resize-handle:hover {
  transform: scale(1.3);
  background: #6366f1;
}

.rot-handle {
  position: absolute;
  top: -32px;
  left: 50%;
  transform: translateX(-50%);
  width: 24px;
  height: 24px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: grab;
  z-index: 100;
  color: white;
  font-size: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s;
}

.rot-handle:hover {
  transform: translateX(-50%) scale(1.1);
}

.el-quickbar {
  position: absolute;
  top: -40px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 200;
  white-space: nowrap;
}

.dark .el-quickbar {
  background: #1e293b;
  border-color: #475569;
}

.el-quickbar button {
  width: 28px;
  height: 28px;
  border-radius: 14px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: #64748b;
  font-size: 12px;
  transition: all 0.2s;
}

.el-quickbar button:hover {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.el-quickbar button.del:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.qb-sep {
  width: 1px;
  height: 20px;
  background: #e2e8f0;
  margin: 0 4px;
}

/* Drop Hint */
.drop-hint {
  position: absolute;
  inset: 0;
  border: 2px dashed #6366f1;
  background: rgba(99, 102, 241, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  z-index: 999;
  pointer-events: none;
}

.drop-hint i {
  font-size: 32px;
  color: #6366f1;
  opacity: 0.7;
}

.drop-hint span {
  font-size: 14px;
  font-weight: 600;
  color: #6366f1;
}

/* Status Bar */
.canvas-statusbar {
  position: absolute;
  bottom: 12px;
  right: 12px;
  display: flex;
  gap: 16px;
  padding: 6px 12px;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  border-radius: 20px;
  font-size: 11px;
  color: white;
  z-index: 50;
  pointer-events: none;
}

.canvas-statusbar span {
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Right Panel */
.right-panel {
  width: 320px;
  flex-shrink: 0;
  background: white;
  border-left: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: relative;
}

.right-panel.collapsed {
  width: 50px;
}

.dark .right-panel {
  background: #1e293b;
  border-left-color: #334155;
}

.panel-collapse-btn {
  position: absolute;
  left: -12px;
  top: 20px;
  width: 24px;
  height: 24px;
  border-radius: 12px;
  background: white;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  color: #64748b;
  transition: all 0.2s;
}

.dark .panel-collapse-btn {
  background: #1e293b;
  border-color: #334155;
  color: #94a3b8;
}

.panel-collapse-btn:hover {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

/* No Selection */
.no-selection {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 20px;
  text-align: center;
}

.no-sel-icon {
  width: 64px;
  height: 64px;
  border-radius: 32px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.no-sel-icon i {
  font-size: 28px;
  color: white;
}

.no-sel-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 8px;
}

.dark .no-sel-title {
  color: #f1f5f9;
}

.no-sel-sub {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 24px;
}

.dark .no-sel-sub {
  color: #94a3b8;
}

.shortcut-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.shortcut-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 12px;
  background: #f8fafc;
  border-radius: 8px;
}

.dark .shortcut-row {
  background: #334155;
}

.shortcut-desc {
  font-size: 12px;
  color: #64748b;
}

.dark .shortcut-desc {
  color: #94a3b8;
}

.shortcut-key {
  font-size: 10px;
  font-family: monospace;
  padding: 2px 6px;
  background: white;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
  color: #6366f1;
}

.dark .shortcut-key {
  background: #1e293b;
  border-color: #475569;
}

/* Panel Header */
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #e2e8f0;
}

.dark .panel-header {
  border-bottom-color: #334155;
}

.panel-el-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.panel-el-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

.panel-el-type {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  text-transform: capitalize;
}

.dark .panel-el-type {
  color: #f1f5f9;
}

.panel-header-actions {
  display: flex;
  gap: 8px;
}

.panel-header-actions button {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.dark .panel-header-actions button {
  background: #334155;
  border-color: #475569;
  color: #94a3b8;
}

.panel-header-actions button:hover {
  background: #ef4444;
  border-color: #ef4444;
  color: white;
}

/* Prop Tabs */
.prop-tabs {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
  padding: 0 16px;
}

.dark .prop-tabs {
  border-bottom-color: #334155;
}

.prop-tab {
  flex: 1;
  padding: 12px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.prop-tab.active {
  color: #6366f1;
  border-bottom-color: #6366f1;
}

/* Props Scroll */
.props-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.props-scroll::-webkit-scrollbar {
  width: 4px;
}

.props-scroll::-webkit-scrollbar-track {
  background: #e2e8f0;
}

.dark .props-scroll::-webkit-scrollbar-track {
  background: #334155;
}

.props-scroll::-webkit-scrollbar-thumb {
  background: #6366f1;
  border-radius: 2px;
}

/* Prop Sections */
.prop-section {
  margin-bottom: 24px;
}

.prop-field {
  margin-bottom: 12px;
}

.prop-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-bottom: 6px;
}

.dark .prop-label {
  color: #94a3b8;
}

.prop-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.p-input,
.p-select {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  font-size: 13px;
  outline: none;
  transition: all 0.2s;
}

.dark .p-input,
.dark .p-select {
  background: #334155;
  border-color: #475569;
  color: #f1f5f9;
}

.p-input:focus,
.p-select:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.p-range {
  width: 100%;
  accent-color: #6366f1;
}

.p-textarea {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  font-size: 13px;
  resize: vertical;
  font-family: inherit;
  outline: none;
}

.dark .p-textarea {
  background: #334155;
  border-color: #475569;
  color: #f1f5f9;
}

.p-textarea:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.text-style-btns {
  display: flex;
  gap: 8px;
}

.ts-btn {
  flex: 1;
  padding: 6px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.dark .ts-btn {
  background: #334155;
  border-color: #475569;
  color: #94a3b8;
}

.ts-btn.active {
  background: #6366f1;
  border-color: #6366f1;
  color: white;
}

.list-item-row {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.btn-del-small {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  color: #ef4444;
  transition: all 0.2s;
}

.btn-del-small:hover {
  background: #ef4444;
  color: white;
  border-color: #ef4444;
}

.btn-add-item {
  width: 100%;
  padding: 8px;
  border: 2px dashed #cbd5e1;
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-add-item:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

/* Context Menu */
.context-menu {
  position: fixed;
  z-index: 10000;
  width: 200px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  padding: 6px;
}

.dark .context-menu {
  background: #1e293b;
  border-color: #334155;
}

.ctx-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  width: 100%;
  background: none;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  color: #1e293b;
  font-size: 13px;
  font-weight: 500;
  transition: background 0.2s;
}

.dark .ctx-item {
  color: #f1f5f9;
}

.ctx-item:hover {
  background: rgba(99, 102, 241, 0.1);
}

.ctx-item.danger {
  color: #ef4444;
}

.ctx-item.danger:hover {
  background: rgba(239, 68, 68, 0.1);
}

.ctx-item kbd {
  margin-left: auto;
  font-size: 10px;
  font-family: monospace;
  padding: 2px 4px;
  background: #f1f5f9;
  border-radius: 4px;
  color: #64748b;
}

.dark .ctx-item kbd {
  background: #334155;
  color: #94a3b8;
}

.ctx-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  background: white;
  border-radius: 40px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  z-index: 100000;
  font-size: 13px;
  font-weight: 500;
  color: #1e293b;
}

.dark .toast {
  background: #1e293b;
  color: #f1f5f9;
}

.toast.success {
  background: #059669;
  color: white;
}

.toast.error {
  background: #dc2626;
  color: white;
}

/* Snap Guides */
.snap-guide {
  position: absolute;
  background: #6366f1;
  pointer-events: none;
  z-index: 1000;
  opacity: 0.7;
}

.snap-h {
  left: 0;
  right: 0;
  height: 2px;
}

.snap-v {
  top: 0;
  bottom: 0;
  width: 2px;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

/* RTL Support */
.rtl {
  direction: rtl;
}

.rtl .topbar-left,
.rtl .topbar-right {
  flex-direction: row-reverse;
}

/* Responsive */
@media (max-width: 768px) {
  .topbar-center {
    display: none;
  }
  
  .left-sidebar,
  .right-panel {
    position: absolute;
    z-index: 100;
    height: 100%;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  }
  
  .left-sidebar:not(.collapsed) {
    width: 280px;
  }
  
  .right-panel:not(.collapsed) {
    width: 280px;
    right: 0;
  }
}

/* Image Placeholder */
.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
  border-radius: 8px;
  cursor: pointer;
}

.dark .image-placeholder {
  background: linear-gradient(135deg, #334155, #475569);
}

.image-placeholder i {
  font-size: 32px;
  color: #94a3b8;
}

.image-placeholder span {
  font-size: 12px;
  color: #64748b;
}

/* Export Dropdown */
.export-wrapper {
  position: relative;
}

.export-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  width: 240px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 1000;
}

.dark .export-dropdown {
  background: #1e293b;
  border-color: #334155;
}

.export-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  width: 100%;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
  transition: background 0.2s;
}

.export-item:hover {
  background: rgba(99, 102, 241, 0.05);
}

.export-item i {
  font-size: 20px;
}

.export-label {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}

.dark .export-label {
  color: #f1f5f9;
}

.export-desc {
  font-size: 10px;
  color: #64748b;
}

.dark .export-desc {
  color: #94a3b8;
}
</style>
<!--  -->
 <!-- For PDF gennpm install chart.js html2canvaseration (recommended) -->
<!-- composer require spatie/browsershot -->
<!-- npm install puppeteer -->

<!-- For Excel export (optional) -->
<!-- composer require maatwebsite/excel -->

<!-- For DomPDF fallback (optional) -->
<!-- composer require barryvdh/laravel-dompdf -->
 <!-- #editor and preview not enhance -->