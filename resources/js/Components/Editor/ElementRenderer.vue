<template>
  <div class="w-full h-full" :style="containerStyle">

    <!-- TEXT TYPES -->
    <div v-if="['text','heading','subheading','quote','blockquote','highlight'].includes(el.type)"
         class="w-full h-full overflow-hidden"
         :style="textStyle"
         :contenteditable="selected && !el.locked"
         @blur="e => $emit('text-blur', e)"
         v-html="el.content || ''"/>

    <!-- CALLOUT -->
    <div v-else-if="el.type === 'callout'"
         class="w-full h-full flex items-start overflow-hidden"
         :style="{
           padding: (12*zf)+'px',
           gap: (10*zf)+'px',
           backgroundColor: el.styles?.backgroundColor || '#eff6ff',
           borderLeft: `${4*zf}px solid ${el.styles?.borderColor || rs.primary_color}`,
           borderRadius: (el.styles?.borderRadius||10)*zf+'px',
         }">
      <span :style="{fontSize:(16*zf)+'px',flexShrink:0,marginTop:(2*zf)+'px'}">{{ el.emoji || '💡' }}</span>
      <div class="flex-1 overflow-hidden"
           :style="textStyle"
           :contenteditable="selected && !el.locked"
           @blur="e => $emit('text-blur', e)"
           v-html="el.content || 'Callout text…'"/>
    </div>

    <!-- ALERT -->
    <div v-else-if="el.type === 'alert'"
         class="w-full h-full flex items-center overflow-hidden"
         :style="{
           padding: (10*zf)+'px',
           gap: (8*zf)+'px',
           backgroundColor: el.styles?.backgroundColor || '#fef3c7',
           borderLeft: `${4*zf}px solid ${el.styles?.borderColor || '#f59e0b'}`,
         }">
      <span :style="{fontSize:(16*zf)+'px'}">{{ el.emoji || '⚠️' }}</span>
      <div :style="textStyle" v-html="el.content || 'Alert message'"/>
    </div>

    <!-- BADGE -->
    <div v-else-if="el.type === 'badge'"
         class="w-full h-full flex items-center justify-center">
      <span :style="{
        backgroundColor: el.styles?.backgroundColor || rs.primary_color+'25',
        color: el.styles?.color || rs.primary_color,
        padding: `${4*zf}px ${12*zf}px`,
        borderRadius: '999px',
        fontSize: (12*zf)+'px',
        fontWeight: '600',
        fontFamily: rs.font_family,
        whiteSpace: 'nowrap',
      }">{{ el.content || 'Label' }}</span>
    </div>

    <!-- HIGHLIGHT -->
    <div v-else-if="el.type === 'highlight'"
         :style="{...textStyle, backgroundColor: el.styles?.backgroundColor || '#fef9c3', padding: (6*zf)+'px', borderRadius: (4*zf)+'px'}"
         :contenteditable="selected && !el.locked"
         @blur="e => $emit('text-blur', e)"
         v-html="el.content || 'Highlighted text'"/>

    <!-- LINK -->
    <a v-else-if="el.type === 'link'"
       :href="el.href" :target="el.target || '_blank'"
       class="w-full h-full flex items-center overflow-hidden"
       :style="{...textStyle, color: el.styles?.color || rs.primary_color, textDecoration: 'underline'}">
      {{ el.content || el.href || 'Link' }}
    </a>

    <!-- CODE BLOCK -->
    <div v-else-if="el.type === 'code'"
         class="w-full h-full overflow-hidden"
         :style="{background:'#1e293b',borderRadius:(8*zf)+'px',padding:(12*zf)+'px'}">
      <div v-if="el.language"
           :style="{fontSize:(9*zf)+'px',color:'#64748b',fontFamily:'monospace',marginBottom:(6*zf)+'px',textTransform:'uppercase',letterSpacing:'0.08em'}">
        {{ el.language }}
      </div>
      <pre :style="{color:'#34d399',fontSize:(12*zf)+'px',fontFamily:'Courier New,monospace',whiteSpace:'pre-wrap',margin:0}"
           :contenteditable="selected && !el.locked"
           @blur="e => $emit('text-blur', e)">{{ el.content }}</pre>
    </div>

    <!-- LIST -->
    <div v-else-if="el.type === 'list'"
         class="w-full h-full overflow-hidden"
         :style="textStyle">
      <component :is="el.styles?.listStyle === 'numbered' ? 'ol' : 'ul'"
                 :style="{listStyleType: el.styles?.listStyle === 'numbered' ? 'decimal' : 'disc', paddingLeft: (20*zf)+'px'}">
        <li v-for="(item, i) in el.items" :key="i"
            :style="{marginBottom:(4*zf)+'px',fontSize:(14*zf)+'px'}"
            :contenteditable="selected && !el.locked"
            @blur="e => $emit('list-item-blur', i, e)">{{ item }}</li>
      </component>
      <button v-if="selected && !el.locked" @mousedown.stop @click.stop="$emit('add-list-item')"
              class="mt-1 text-[10px] text-indigo-500 hover:text-indigo-400 font-bold px-1">
        + Add item
      </button>
    </div>

    <!-- CHECKLIST -->
    <div v-else-if="el.type === 'checklist'"
         class="w-full h-full overflow-y-auto"
         :style="textStyle">
      <div v-for="(item, i) in el.items" :key="i"
           class="flex items-center gap-2 mb-1"
           :style="{marginBottom:(4*zf)+'px'}">
        <input type="checkbox" :checked="item.checked"
               @change="item.checked = $event.target.checked; $emit('push-history')"
               :style="{width:(14*zf)+'px',height:(14*zf)+'px',accentColor:rs.primary_color,flexShrink:0}"/>
        <span :contenteditable="selected && !el.locked"
              :style="{fontSize:(13*zf)+'px',textDecoration:item.checked?'line-through':'none',color:item.checked?'#94a3b8':''}"
              @blur="e => $emit('checklist-item-blur', i, e)">{{ item.text }}</span>
      </div>
      <button v-if="selected && !el.locked" @mousedown.stop @click.stop="$emit('add-checklist-item')"
              class="text-[10px] text-indigo-500 hover:text-indigo-400 font-bold px-1">
        + Add task
      </button>
    </div>

    <!-- ICON / EMOJI -->
    <div v-else-if="el.type === 'icon'"
         class="w-full h-full flex items-center justify-center">
      <span :style="{fontSize:(el.styles?.fontSize||40)*zf+'px',color:el.styles?.color||rs.primary_color}">
        {{ el.content || '★' }}
      </span>
    </div>

    <!-- RATING -->
    <div v-else-if="el.type === 'rating'"
         class="w-full h-full flex items-center gap-1">
      <span v-for="i in 5" :key="i"
            @click="el.value = i; $emit('push-history')"
            :style="{fontSize:(24*zf)+'px',cursor:'pointer',color:i <= (el.value||0) ? (el.styles?.color||'#f59e0b') : '#e2e8f0'}">★</span>
    </div>

    <!-- IMAGE -->
    <div v-else-if="el.type === 'image'" class="w-full h-full relative">
      <img v-if="el.src" :src="el.src"
           class="w-full h-full"
           :style="{objectFit:el.styles?.objectFit||'cover',display:'block'}"/>
      <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2"
           :style="{backgroundColor: dark ? '#1e293b' : '#f8fafc',borderRadius:(el.styles?.borderRadius||0)*zf+'px'}">
        <i class="fa-solid fa-image" :style="{fontSize:(32*zf)+'px',color: dark ? '#334155' : '#e2e8f0'}"></i>
        <span :style="{fontSize:(11*zf)+'px',color:'#94a3b8',fontFamily:'sans-serif'}">Click to add image</span>
        <label class="cursor-pointer">
          <input type="file" accept="image/*" class="hidden" @change="e => $emit('upload-image', e)"/>
          <span :style="{fontSize:(10*zf)+'px',color:rs.primary_color,fontWeight:'600',fontFamily:'sans-serif'}">
            Browse files
          </span>
        </label>
      </div>
      <div v-if="el._uploading" class="absolute inset-0 flex items-center justify-center bg-black/40 rounded">
        <i class="fa-solid fa-spinner fa-spin text-white"></i>
      </div>
    </div>

    <!-- TABLE -->
    <div v-else-if="el.type === 'table'" class="w-full h-full overflow-auto">
      <table style="width:100%;border-collapse:collapse" :style="{fontSize:(12*zf)+'px'}">
        <thead>
          <tr>
            <th v-for="(col, ci) in el.columns" :key="ci"
                :style="{
                  backgroundColor: el.styles?.headerBg || rs.primary_color,
                  color: el.styles?.headerColor || '#fff',
                  padding: `${6*zf}px ${10*zf}px`,
                  textAlign: 'left',
                  fontSize: (11*zf)+'px',
                  fontWeight: '600',
                  textTransform: 'uppercase',
                  letterSpacing: '0.04em',
                  fontFamily: rs.font_family,
                }"
                :contenteditable="selected && !el.locked"
                @blur="e => $emit('table-header-blur', col, e)">
              {{ col }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, ri) in el.data" :key="ri"
              :style="{backgroundColor: ri%2===0 ? (el.styles?.evenRowBg||'#fff') : (el.styles?.oddRowBg||'#f8fafc')}">
            <td v-for="(col, ci) in el.columns" :key="ci"
                :style="{
                  padding: `${6*zf}px ${10*zf}px`,
                  borderBottom: '1px solid #f1f5f9',
                  fontSize: (12*zf)+'px',
                  fontFamily: rs.font_family,
                }"
                :contenteditable="selected && !el.locked"
                @blur="e => $emit('table-cell-blur', ri, ci, e)">
              {{ row[col] || '' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- METRIC / KPI -->
    <div v-else-if="el.type === 'metric'"
         class="w-full h-full flex flex-col justify-center overflow-hidden"
         :style="{
           padding: (16*zf)+'px',
           backgroundColor: el.styles?.backgroundColor || '#f8fafc',
           border: `1px solid ${el.styles?.borderColor||'#e2e8f0'}`,
           borderRadius: (el.styles?.borderRadius||12)*zf+'px',
         }">
      <p :style="{fontSize:(10*zf)+'px',fontWeight:'600',textTransform:'uppercase',letterSpacing:'0.06em',color:'#64748b',marginBottom:(4*zf)+'px',fontFamily:rs.font_family}">
        {{ el.label }}
      </p>
      <p :style="{fontSize:(28*zf)+'px',fontWeight:'800',color:el.styles?.color||rs.primary_color,lineHeight:'1',fontFamily:rs.font_family}">
        {{ el.value }}
      </p>
      <div v-if="el.change" :style="{display:'flex',alignItems:'center',gap:(4*zf)+'px',marginTop:(6*zf)+'px'}">
        <span :style="{color:el.changeType==='positive'?'#059669':'#dc2626',fontSize:(12*zf)+'px',fontWeight:'600'}">
          {{ el.changeType==='positive' ? '▲' : '▼' }} {{ el.change }}
        </span>
        <span :style="{fontSize:(11*zf)+'px',color:'#94a3b8'}">{{ el.changePeriod }}</span>
      </div>
    </div>

    <!-- PROGRESS -->
    <div v-else-if="el.type === 'progress'"
         class="w-full h-full flex flex-col justify-center overflow-hidden"
         :style="{padding:`0 ${4*zf}px`}">
      <div :style="{display:'flex',justifyContent:'space-between',fontSize:(12*zf)+'px',marginBottom:(6*zf)+'px',color:el.styles?.color||rs.primary_color}">
        <span>{{ el.label }}</span><span>{{ el.value }}%</span>
      </div>
      <div :style="{height:(el.styles?.trackHeight||8)*zf+'px',backgroundColor:el.styles?.trackColor||'#e2e8f0',borderRadius:'999px',overflow:'hidden'}">
        <div :style="{width:el.value+'%',height:'100%',backgroundColor:el.styles?.color||rs.primary_color,borderRadius:'999px',transition:'width 0.5s ease'}"/>
      </div>
    </div>

    <!-- CIRCULAR PROGRESS -->
    <div v-else-if="el.type === 'circular-progress'"
         class="w-full h-full flex flex-col items-center justify-center">
      <svg :width="Math.min((el.styles?.width||180)*zf, (el.styles?.height||180)*zf - 40*zf)"
           :height="Math.min((el.styles?.width||180)*zf, (el.styles?.height||180)*zf - 40*zf)"
           viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="45" fill="none"
                :stroke="el.styles?.trackColor||'#e2e8f0'" :stroke-width="8"/>
        <circle cx="50" cy="50" r="45" fill="none"
                :stroke="el.styles?.color||rs.primary_color" :stroke-width="8"
                stroke-linecap="round"
                :stroke-dasharray="`${(el.value||0)*2.827} 282.7`"
                transform="rotate(-90 50 50)"
                style="transition: stroke-dasharray 0.5s ease"/>
        <text x="50" y="54" text-anchor="middle"
              :font-size="20" :fill="el.styles?.color||rs.primary_color" font-weight="800" font-family="sans-serif">
          {{ el.value }}%
        </text>
      </svg>
      <p v-if="el.label" :style="{fontSize:(12*zf)+'px',color:'#64748b',marginTop:(4*zf)+'px',fontFamily:rs.font_family}">{{ el.label }}</p>
    </div>

    <!-- STAT ROW -->
    <div v-else-if="el.type === 'stat-row'"
         class="w-full h-full flex items-center divide-x overflow-hidden"
         :style="{divideColor:'#e2e8f0'}">
      <div v-for="(stat, i) in el.stats" :key="i"
           class="flex-1 flex flex-col items-center justify-center"
           :style="{borderRight: i < el.stats.length-1 ? '1px solid #e2e8f0' : 'none', padding:`${8*zf}px`}">
        <div :style="{fontSize:(24*zf)+'px',fontWeight:'800',color:rs.primary_color,fontFamily:rs.font_family,lineHeight:'1'}">{{ stat.value }}</div>
        <div :style="{fontSize:(10*zf)+'px',color:'#94a3b8',fontFamily:rs.font_family,marginTop:(4*zf)+'px',fontWeight:'600',textTransform:'uppercase',letterSpacing:'0.04em'}">{{ stat.label }}</div>
      </div>
    </div>

    <!-- TABLE OF CONTENTS -->
    <div v-else-if="el.type === 'toc'" class="w-full h-full overflow-hidden p-2">
      <div :style="{fontSize:(15*zf)+'px',fontWeight:'700',marginBottom:(10*zf)+'px',color:rs.primary_color,fontFamily:rs.font_family}"
           :contenteditable="selected && !el.locked"
           @blur="e => $emit('toc-title-blur', e)">
        {{ el.title || 'Table of Contents' }}
      </div>
      <div v-for="(item, i) in el.items" :key="i"
           class="flex items-center gap-1"
           :style="{marginBottom:(6*zf)+'px'}">
        <span :style="{fontSize:(13*zf)+'px',color:'#334155',fontFamily:rs.font_family,flex:1}"
              :contenteditable="selected && !el.locked"
              @blur="e => $emit('toc-item-blur', i, 'label', e)">{{ item.label }}</span>
        <span :style="{flex:1,borderBottom:`1px dotted #cbd5e1`,margin:`0 ${6*zf}px`,minWidth:(20*zf)+'px'}"/>
        <span :style="{fontSize:(11*zf)+'px',color:'#94a3b8',fontFamily:rs.font_family}"
              :contenteditable="selected && !el.locked"
              @blur="e => $emit('toc-item-blur', i, 'page', e)">{{ item.page }}</span>
      </div>
      <button v-if="selected && !el.locked" @mousedown.stop @click.stop="$emit('add-toc-item')"
              class="text-[10px] text-indigo-500 font-bold mt-1">+ Add item</button>
    </div>

    <!-- TIMELINE -->
    <div v-else-if="el.type === 'timeline'" class="w-full h-full overflow-hidden py-2 px-3">
      <div v-for="(item, i) in el.items" :key="i"
           class="flex items-start gap-3"
           :style="{marginBottom:(16*zf)+'px'}">
        <div class="flex flex-col items-center" :style="{gap:0}">
          <div :style="{width:(12*zf)+'px',height:(12*zf)+'px',borderRadius:'50%',backgroundColor:rs.primary_color,flexShrink:0}"/>
          <div v-if="i < el.items.length-1" :style="{width:'2px',height:(20*zf)+'px',backgroundColor:'#e2e8f0',marginTop:(4*zf)+'px'}"/>
        </div>
        <div>
          <div :style="{fontSize:(12*zf)+'px',fontWeight:'700',color:'#0f172a',fontFamily:rs.font_family}"
               :contenteditable="selected && !el.locked"
               @blur="e => $emit('timeline-blur', i, 'label', e)">{{ item.label }}</div>
          <div :style="{fontSize:(10*zf)+'px',color:rs.primary_color,marginBottom:(2*zf)+'px'}"
               :contenteditable="selected && !el.locked"
               @blur="e => $emit('timeline-blur', i, 'date', e)">{{ item.date }}</div>
          <div :style="{fontSize:(11*zf)+'px',color:'#64748b',fontFamily:rs.font_family}"
               :contenteditable="selected && !el.locked"
               @blur="e => $emit('timeline-blur', i, 'desc', e)">{{ item.desc }}</div>
        </div>
      </div>
      <button v-if="selected && !el.locked" @mousedown.stop @click.stop="$emit('add-timeline-item')"
              class="text-[10px] text-indigo-500 font-bold">+ Add milestone</button>
    </div>

    <!-- SHAPES -->
    <div v-else-if="el.type === 'rectangle'"
         class="w-full h-full"
         :style="{backgroundColor:el.styles?.backgroundColor||rs.primary_color}"/>

    <div v-else-if="el.type === 'circle'"
         class="w-full h-full"
         :style="{backgroundColor:el.styles?.backgroundColor||rs.primary_color,borderRadius:'50%'}"/>

    <svg v-else-if="el.type === 'triangle'" class="w-full h-full" preserveAspectRatio="none">
      <polygon points="50%,0 100%,100% 0,100%" :fill="el.styles?.backgroundColor||'#f59e0b'"/>
    </svg>

    <svg v-else-if="el.type === 'star'" class="w-full h-full" viewBox="0 0 100 100">
      <polygon points="50,5 61,35 95,35 68,57 79,91 50,70 21,91 32,57 5,35 39,35"
               :fill="el.styles?.backgroundColor||rs.primary_color"/>
    </svg>

    <div v-else-if="el.type === 'line'" class="w-full h-full flex items-center">
      <div :style="{width:'100%',borderTop:`${(el.styles?.borderWidth||2)*zf}px ${el.styles?.borderStyle||'solid'} ${el.styles?.color||'#94a3b8'}`}"/>
    </div>

    <div v-else-if="el.type === 'arrow'" class="w-full h-full flex items-center relative">
      <div :style="{flex:1,borderTop:`${(el.styles?.borderWidth||2)*zf}px ${el.styles?.borderStyle||'solid'} ${el.styles?.color||rs.primary_color}`}"/>
      <div :style="{
        borderTop: `${6*zf}px solid transparent`,
        borderBottom: `${6*zf}px solid transparent`,
        borderLeft: `${12*zf}px solid ${el.styles?.color||rs.primary_color}`,
        flexShrink:0,
      }"/>
    </div>

    <div v-else-if="el.type === 'divider'" class="w-full h-full flex items-center">
      <div :style="{width:'100%',borderTop:`${(el.styles?.borderWidth||1)*zf}px ${el.styles?.borderStyle||'solid'} ${el.styles?.color||'#e2e8f0'}`}"/>
    </div>

    <div v-else-if="el.type === 'spacer'" class="w-full h-full"
         :style="{background: selected ? 'repeating-linear-gradient(45deg,#f1f5f9,#f1f5f9 4px,transparent 4px,transparent 16px)' : 'transparent'}"/>

    <!-- DATE -->
    <div v-else-if="el.type === 'date'" class="w-full h-full flex items-center" :style="textStyle">
      {{ formattedDate }}
    </div>

    <!-- PAGE NUMBER -->
    <div v-else-if="el.type === 'pagenum'"
         class="w-full h-full flex items-center justify-center"
         :style="textStyle">
      {{ pi + 1 }}
    </div>

    <!-- SIGNATURE -->
    <div v-else-if="el.type === 'signature'" class="w-full h-full flex flex-col justify-between" :style="{padding:(8*zf)+'px'}">
      <div :style="{flex:1,borderBottom:`2px solid #cbd5e1`,display:'flex',alignItems:'flex-end',paddingBottom:(4*zf)+'px'}">
        <span :style="{fontSize:(22*zf)+'px',color:'#94a3b8',fontStyle:'italic',fontFamily:'Georgia,serif'}">{{ el.content || '' }}</span>
      </div>
      <p :style="{fontSize:(11*zf)+'px',color:'#94a3b8',marginTop:(4*zf)+'px',fontFamily:rs.font_family}">{{ el.label || 'Authorised Signature' }}</p>
    </div>

    <!-- WATERMARK -->
    <div v-else-if="el.type === 'watermark'"
         class="w-full h-full flex items-center justify-center pointer-events-none"
         :style="{opacity:(el.styles?.opacity||20)/100}">
      <span :style="{
        fontSize:(el.styles?.fontSize||48)*zf+'px',
        fontWeight:'800',
        color:el.styles?.color||'#94a3b8',
        transform:`rotate(${el.styles?.rotate||-30}deg)`,
        whiteSpace:'nowrap',
        fontFamily:'sans-serif',
      }">{{ el.content || 'CONFIDENTIAL' }}</span>
    </div>

    <!-- CHART CANVAS -->
    <canvas v-else-if="isChartType(el.type)"
            :id="'chart-' + el.id"
            class="w-full h-full"
            :style="{display:'block'}"/>

    <!-- SOCIAL CARD -->
    <div v-else-if="el.type === 'social-card'"
         class="w-full h-full flex flex-col items-center justify-center gap-2 overflow-hidden"
         :style="{backgroundColor:el.styles?.backgroundColor||'#fff',borderRadius:(el.styles?.borderRadius||16)*zf+'px',border:`1px solid ${el.styles?.borderColor||'#e2e8f0'}`,padding:(16*zf)+'px'}">
      <span :style="{fontSize:(40*zf)+'px'}">{{ el.avatar || '👤' }}</span>
      <div class="text-center">
        <div :style="{fontSize:(16*zf)+'px',fontWeight:'700',color:'#0f172a',fontFamily:rs.font_family}">{{ el.content || 'Name' }}</div>
        <div :style="{fontSize:(12*zf)+'px',color:'#64748b',fontFamily:rs.font_family}">{{ el.subtitle || 'Title' }}</div>
      </div>
    </div>

    <!-- TESTIMONIAL -->
    <div v-else-if="el.type === 'testimonial'"
         class="w-full h-full overflow-hidden flex flex-col justify-between"
         :style="{backgroundColor:el.styles?.backgroundColor||'#f8fafc',borderRadius:(el.styles?.borderRadius||16)*zf+'px',border:`1px solid ${el.styles?.borderColor||'#e2e8f0'}`,padding:(16*zf)+'px'}">
      <div :style="{fontSize:(24*zf)+'px',color:rs.primary_color,marginBottom:(8*zf)+'px'}">❝</div>
      <div :style="{fontSize:(13*zf)+'px',color:'#334155',fontStyle:'italic',flex:1,fontFamily:rs.font_family}">{{ el.content || 'Great product!' }}</div>
      <div :style="{marginTop:(12*zf)+'px'}">
        <div :style="{fontSize:(13*zf)+'px',fontWeight:'700',color:'#0f172a',fontFamily:rs.font_family}">{{ el.author || 'Author' }}</div>
        <div :style="{fontSize:(11*zf)+'px',color:'#94a3b8',fontFamily:rs.font_family}">{{ el.role || 'Role' }}</div>
      </div>
    </div>

    <!-- PRICE CARD -->
    <div v-else-if="el.type === 'price-card'"
         class="w-full h-full flex flex-col overflow-hidden"
         :style="{
           backgroundColor:el.styles?.backgroundColor||'#fff',
           borderRadius:(el.styles?.borderRadius||16)*zf+'px',
           border:`1px solid ${el.styles?.borderColor||'#e2e8f0'}`,
           padding:(16*zf)+'px',
         }">
      <div :style="{fontSize:(14*zf)+'px',fontWeight:'700',color:'#0f172a',fontFamily:rs.font_family,marginBottom:(8*zf)+'px'}">{{ el.plan || 'Plan' }}</div>
      <div :style="{fontSize:(32*zf)+'px',fontWeight:'900',color:rs.primary_color,fontFamily:rs.font_family,lineHeight:'1'}">{{ el.price || '$0' }}</div>
      <div :style="{fontSize:(11*zf)+'px',color:'#94a3b8',marginBottom:(12*zf)+'px'}">{{ el.period || '/month' }}</div>
      <ul :style="{listStyle:'none',padding:0,space:'4px',flex:1}">
        <li v-for="f in el.features" :key="f" :style="{fontSize:(12*zf)+'px',color:'#334155',marginBottom:(4*zf)+'px',display:'flex',alignItems:'center',gap:(6*zf)+'px'}">
          <span :style="{color:'#10b981',fontWeight:'700'}">✓</span> {{ f }}
        </li>
      </ul>
    </div>

    <!-- KANBAN CARD -->
    <div v-else-if="el.type === 'kanban'"
         class="w-full h-full overflow-hidden"
         :style="{
           backgroundColor:el.styles?.backgroundColor||'#fff',
           borderLeft:`3px solid ${el.styles?.accentColor||rs.primary_color}`,
           borderRadius:(4)*zf+'px',
           padding:(12*zf)+'px',
           boxShadow:'0 2px 8px rgba(0,0,0,.08)',
         }">
      <div :style="{fontSize:(12*zf)+'px',fontWeight:'700',color:'#0f172a',fontFamily:rs.font_family,marginBottom:(4*zf)+'px'}">{{ el.content || 'Task' }}</div>
      <div v-if="el.status" :style="{fontSize:(10*zf)+'px',color:el.styles?.accentColor||rs.primary_color,fontWeight:'600'}">{{ el.status }}</div>
      <div v-if="el.due" :style="{fontSize:(10*zf)+'px',color:'#94a3b8',marginTop:(6*zf)+'px'}">Due: {{ el.due }}</div>
    </div>

    <!-- FALLBACK -->
    <div v-else class="w-full h-full flex items-center justify-center"
         :style="{backgroundColor:dark?'#1e293b':'#f8fafc',borderRadius:'8px'}">
      <span :style="{fontSize:(11*zf)+'px',color:'#94a3b8',fontFamily:'sans-serif'}">{{ el.type }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue';

const props = defineProps({
  el: Object,
  pi: Number,
  zoom: Number,
  rs: Object,
  dark: Boolean,
  selected: Boolean,
});

const emit = defineEmits([
  'text-blur','list-item-blur','add-list-item','checklist-item-blur','add-checklist-item',
  'table-cell-blur','table-header-blur','add-table-row','add-table-column',
  'remove-table-row','remove-table-column','toc-title-blur','toc-item-blur','add-toc-item',
  'timeline-blur','add-timeline-item','upload-image','push-history',
]);

const isChartType = inject('isChartType');
const zf = computed(() => props.zoom / 100);

const textStyle = computed(() => {
  const s = props.el.styles || {};
  return {
    fontSize: ((s.fontSize || 14) * zf.value) + 'px',
    color: s.color || '#1e293b',
    fontFamily: s.fontFamily || props.rs.font_family || "'DM Sans', sans-serif",
    fontWeight: s.fontWeight || (props.el.type === 'heading' ? '700' : '400'),
    fontStyle: s.fontStyle || 'normal',
    textAlign: s.textAlign || 'left',
    textDecoration: s.textDecoration || 'none',
    textTransform: s.textTransform || 'none',
    lineHeight: s.lineHeight || 1.5,
    letterSpacing: s.letterSpacing ? s.letterSpacing + 'px' : undefined,
    backgroundColor: (s.backgroundColor && s.backgroundColor !== 'transparent') ? s.backgroundColor : undefined,
    overflow: 'hidden',
    outline: 'none',
  };
});

const containerStyle = computed(() => {
  const s = props.el.styles || {};
  const style = {};
  if (s.backgroundColor && !isShapeOrSpecial.value) {
    style.backgroundColor = s.backgroundColor;
  }
  return style;
});

const isShapeOrSpecial = computed(() => {
  return ['rectangle','circle','triangle','star','divider','line','arrow','spacer',
          'image','metric','progress','circular-progress','stat-row','kanban',
          'price-card','social-card','testimonial','table','checklist','toc',
          'timeline','code','badge','callout','alert','watermark'].includes(props.el.type);
});

const formattedDate = computed(() =>
  new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
);
</script>