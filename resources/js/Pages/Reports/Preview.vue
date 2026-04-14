<template>
  <div class="min-h-screen bg-slate-900 flex flex-col" :class="isDark?'dark':''">

    <!-- TOP BAR -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
      <div class="flex items-center justify-between px-4 py-2.5">
        <div class="flex items-center gap-3">
          <button @click="goBack" class="flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Editor
          </button>
          <div class="w-px h-5 bg-slate-700"/>
          <h1 class="text-white font-semibold text-sm truncate max-w-xs">{{ report.title }}</h1>
          <span class="px-2 py-0.5 bg-indigo-500/20 text-indigo-300 text-xs font-medium rounded-full border border-indigo-500/30">Preview</span>
        </div>

        <div class="flex items-center gap-2">
          <!-- Page nav -->
          <div class="flex items-center gap-1.5 bg-slate-800 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-300">
            <button @click="goToPrev" :disabled="activePage===0" class="disabled:opacity-30 hover:text-white transition-colors p-0.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="min-w-[60px] text-center text-xs">{{ activePage+1 }} / {{ pages.length }}</span>
            <button @click="goToNext" :disabled="activePage===pages.length-1" class="disabled:opacity-30 hover:text-white transition-colors p-0.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>

          <!-- Zoom -->
          <div class="flex items-center gap-1 bg-slate-800 border border-slate-700 rounded-xl px-2 py-1 text-xs">
            <button @click="previewZoom=Math.max(50,previewZoom-10)" class="p-1 hover:text-white text-slate-400 transition-colors"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg></button>
            <span class="text-slate-300 w-10 text-center">{{ previewZoom }}%</span>
            <button @click="previewZoom=Math.min(150,previewZoom+10)" class="p-1 hover:text-white text-slate-400 transition-colors"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></button>
          </div>

          <div class="w-px h-5 bg-slate-700"/>

          <button @click="printReport"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-medium rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print
          </button>
          <a :href="downloadUrl"
            class="flex items-center gap-1.5 px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF
          </a>
        </div>
      </div>
    </header>

    <div class="flex flex-1 overflow-hidden">

      <!-- PAGE THUMBNAILS SIDEBAR -->
      <aside class="w-44 bg-slate-800 border-r border-slate-700 overflow-y-auto flex flex-col shrink-0">
        <div class="px-3 py-2.5 border-b border-slate-700">
          <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pages ({{ pages.length }})</p>
        </div>
        <div class="p-2 space-y-2 flex-1">
          <div v-for="(page,pi) in pages" :key="page.id"
            @click="goToPage(pi)"
            :class="activePage===pi ? 'ring-2 ring-indigo-500' : 'ring-1 ring-slate-700 opacity-60 hover:opacity-90 hover:ring-slate-500'"
            class="relative rounded-lg overflow-hidden cursor-pointer transition-all">

            <!-- Mini page preview -->
            <div class="w-full bg-white" :style="{aspectRatio: isLandscape ? '4/3' : '3/4'}">
              <div class="w-full h-full relative overflow-hidden"
                :style="{backgroundColor: settings.background_color||'#fff'}">
                <!-- Scale-down mini render of elements -->
                <div class="absolute inset-0 pointer-events-none"
                  :style="{transform:`scale(${thumbScale})`,transformOrigin:'top left',width:`${canvasW}px`,height:`${canvasH}px`}">
                  <div v-for="el in page.elements" :key="el.id" class="absolute"
                    :style="{left:el.position.x+'px',top:el.position.y+'px',width:(el.styles?.width||200)+'px',height:(el.styles?.height||50)+'px',backgroundColor:getElThumbColor(el),borderRadius:(el.styles?.borderRadius||0)+'px',opacity:(el.styles?.opacity??100)/100,zIndex:el.styles?.zIndex||1}"/>
                </div>
              </div>
            </div>

            <!-- Page number badge -->
            <div class="absolute bottom-1 left-0 right-0 flex justify-center">
              <span class="text-[10px] bg-black/50 text-white px-1.5 py-0.5 rounded-full backdrop-blur-sm">{{ pi+1 }}</span>
            </div>
            <div v-if="activePage===pi" class="absolute inset-0 flex items-center justify-center bg-indigo-600/10">
              <span class="text-[10px] text-indigo-400 font-semibold">Active</span>
            </div>
          </div>
        </div>
      </aside>

      <!-- MAIN PREVIEW AREA -->
      <main class="flex-1 overflow-auto bg-slate-900 py-8 flex flex-col items-center gap-8 print:bg-white print:p-0 print:gap-0"
        ref="mainScrollArea">

        <div v-for="(page,pi) in pages" :key="page.id"
          :ref="el => { if(el) pageRefs[pi]=el; }"
          class="print-page relative shadow-2xl print:shadow-none"
          :class="{ 'opacity-30 scale-95': activePage!==pi && pages.length>1 }"
          :style="pageContainerStyle"
          style="transition: opacity 0.3s, transform 0.3s">

          <!-- The actual page -->
          <div class="relative overflow-hidden"
            :style="pageStyle">

            <!-- All elements -->
            <div v-for="el in page.elements" :key="el.id"
              class="absolute element-preview"
              :style="elPosStyle(el)">

              <!-- Text types -->
              <div v-if="['text','heading','subheading'].includes(el.type)"
                :style="elTxtStyle(el)" v-html="el.content" class="w-full h-full overflow-hidden"/>

              <!-- Quote -->
              <div v-else-if="el.type==='quote'"
                class="w-full h-full overflow-hidden"
                :style="{...elTxtStyle(el),borderLeft:`4px solid ${settings.primary_color||'#6366f1'}`,paddingLeft:'16px'}"
                v-html="el.content"/>

              <!-- Callout -->
              <div v-else-if="el.type==='callout'"
                class="w-full h-full flex items-start gap-3 overflow-hidden"
                :style="{backgroundColor:el.styles?.backgroundColor||settings.primary_color+'18',borderLeft:`4px solid ${el.styles?.color||settings.primary_color}`,padding:'12px',borderRadius:'12px'}">
                <span style="font-size:16px;flex-shrink:0;margin-top:2px">{{ el.icon||'💡' }}</span>
                <div :style="elTxtStyle(el)" v-html="el.content" class="flex-1 overflow-hidden"/>
              </div>

              <!-- List -->
              <div v-else-if="el.type==='list'" :style="elTxtStyle(el)" class="w-full h-full overflow-hidden">
                <ul :style="{listStyleType:el.styles?.listStyle==='numbered'?'decimal':'disc',paddingLeft:'20px'}">
                  <li v-for="item in el.items" :key="item" style="margin-bottom:4px">{{ item }}</li>
                </ul>
              </div>

              <!-- Code -->
              <div v-else-if="el.type==='code'"
                style="background:#1e293b;border-radius:8px;padding:12px;width:100%;height:100%;overflow:hidden;">
                <pre style="color:#34d399;font-size:12px;font-family:'Courier New',monospace;white-space:pre-wrap;margin:0">{{ el.content }}</pre>
              </div>

              <!-- Badge -->
              <div v-else-if="el.type==='badge'" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                <span :style="{backgroundColor:el.styles?.backgroundColor||settings.primary_color+'25',color:el.styles?.color||settings.primary_color,padding:'4px 12px',borderRadius:'999px',fontSize:'12px',fontWeight:'600'}">
                  {{ el.content||'Label' }}
                </span>
              </div>

              <!-- Link -->
              <a v-else-if="el.type==='link'" :href="el.href" :target="el.target"
                :style="{...elTxtStyle(el),color:el.styles?.color||settings.primary_color,textDecoration:'underline'}"
                class="w-full h-full flex items-center">{{ el.content||el.href }}</a>

              <!-- Image -->
              <img v-else-if="el.type==='image' && el.src" :src="el.src"
                :style="{width:'100%',height:'100%',objectFit:el.styles?.objectFit||'cover',borderRadius:(el.styles?.borderRadius||0)+'px',display:'block'}"/>
              <div v-else-if="el.type==='image' && !el.src"
                :style="{width:'100%',height:'100%',background:'#f1f5f9',display:'flex',alignItems:'center',justifyContent:'center',borderRadius:(el.styles?.borderRadius||0)+'px'}">
                <span style="font-size:12px;color:#94a3b8">Image placeholder</span>
              </div>

              <!-- Table -->
              <div v-else-if="el.type==='table'" style="width:100%;height:100%;overflow:hidden">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                  <thead><tr>
                    <th v-for="col in el.columns" :key="col"
                      :style="{backgroundColor:el.styles?.headerBg||settings.primary_color,color:el.styles?.headerColor||'#fff',padding:'6px 10px',textAlign:'left',fontSize:'11px',fontWeight:'600',textTransform:'uppercase',letterSpacing:'0.04em'}">
                      {{ col }}
                    </th>
                  </tr></thead>
                  <tbody>
                    <tr v-for="(row,ri) in el.data" :key="ri" :style="{backgroundColor:ri%2===0?(el.styles?.evenRowBg||'#fff'):(el.styles?.oddRowBg||'#f8fafc')}">
                      <td v-for="col in el.columns" :key="col" style="padding:6px 10px;border-bottom:1px solid #f1f5f9">{{ row[col]||'' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Charts -->
              <canvas v-else-if="isChart(el.type)" :id="'pv-chart-'+el.id" style="width:100%;height:100%"/>

              <!-- Metric -->
              <div v-else-if="el.type==='metric'"
                :style="{width:'100%',height:'100%',borderRadius:(el.styles?.borderRadius||12)+'px',backgroundColor:el.styles?.backgroundColor||'#f8fafc',border:`1px solid ${el.styles?.borderColor||'#e2e8f0'}`,display:'flex',flexDirection:'column',justifyContent:'center',padding:'16px'}">
                <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;margin-bottom:4px">{{ el.label }}</p>
                <p :style="{fontSize:'28px',fontWeight:'800',color:el.styles?.color||settings.primary_color,lineHeight:'1'}">{{ el.value }}</p>
                <div v-if="el.change" style="display:flex;align-items:center;gap:4px;margin-top:6px">
                  <span :style="{color:el.changeType==='positive'?'#059669':'#dc2626',fontSize:'12px',fontWeight:'600'}">
                    {{ el.changeType==='positive'?'▲':'▼' }} {{ el.change }}
                  </span>
                  <span style="font-size:11px;color:#94a3b8">{{ el.changePeriod }}</span>
                </div>
              </div>

              <!-- Progress -->
              <div v-else-if="el.type==='progress'" style="width:100%;height:100%;display:flex;flex-direction:column;justify-content:center;gap:4px;padding:0 4px">
                <div style="display:flex;justify-content:space-between;font-size:12px" :style="{color:el.styles?.color||'#374151'}">
                  <span>{{ el.label }}</span><span>{{ el.value }}%</span>
                </div>
                <div :style="{height:'8px',backgroundColor:el.styles?.trackColor||'#e2e8f0',borderRadius:'4px',overflow:'hidden',width:'100%'}">
                  <div :style="{width:el.value+'%',height:'100%',backgroundColor:el.styles?.color||settings.primary_color,borderRadius:'4px'}"/>
                </div>
              </div>

              <!-- Shapes -->
              <div v-else-if="el.type==='rectangle'" :style="{width:'100%',height:'100%',backgroundColor:el.styles?.backgroundColor,borderRadius:(el.styles?.borderRadius||0)+'px',border:el.styles?.borderWidth?`${el.styles.borderWidth}px solid ${el.styles.borderColor||'#000'}`:undefined}"/>
              <div v-else-if="el.type==='circle'" :style="{width:'100%',height:'100%',backgroundColor:el.styles?.backgroundColor,borderRadius:'50%'}"/>
              <svg v-else-if="el.type==='triangle'" style="width:100%;height:100%">
                <polygon points="50%,0 100%,100% 0,100%" :fill="el.styles?.backgroundColor||'#f59e0b'"/>
              </svg>
              <svg v-else-if="el.type==='line'" style="width:100%;height:100%" preserveAspectRatio="none">
                <line x1="0" y1="50%" x2="100%" y2="50%" :stroke="el.styles?.color||'#94a3b8'" :stroke-width="el.styles?.borderWidth||2"/>
              </svg>
              <div v-else-if="el.type==='divider'" style="width:100%;height:100%;display:flex;align-items:center">
                <div :style="{width:'100%',borderTop:`${el.styles?.borderWidth||1}px ${el.styles?.borderStyle||'solid'} ${el.styles?.color||'#e2e8f0'}`}"/>
              </div>

              <!-- Special -->
              <div v-else-if="el.type==='date'" :style="elTxtStyle(el)" style="width:100%;height:100%;display:flex;align-items:center">
                {{ todayFormatted }}
              </div>
              <div v-else-if="el.type==='pagenum'" :style="{...elTxtStyle(el),width:'100%',height:'100%',display:'flex',alignItems:'center',justifyContent:'center'}">
                {{ pi+1 }}
              </div>
              <div v-else-if="el.type==='signature'" style="width:100%;height:100%;display:flex;flex-direction:column;justify-content:space-between;padding:8px">
                <div :style="{flex:1,borderBottom:`2px solid #cbd5e1`,display:'flex',alignItems:'flex-end',paddingBottom:'4px'}">
                  <span style="font-size:24px;color:#94a3b8;font-style:italic;font-family:Georgia,serif">{{ el.content||'' }}</span>
                </div>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px">{{ el.label||'Authorized Signature' }}</p>
              </div>

              <!-- Watermark -->
              <div v-else-if="el.type==='watermark'"
                style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;pointer-events:none"
                :style="{opacity:(el.styles?.opacity||20)/100}">
                <span :style="{fontSize:(el.styles?.fontSize||48)+'px',fontWeight:'800',color:el.styles?.color||'#94a3b8',transform:`rotate(${el.styles?.rotate||-30}deg)`,whiteSpace:'nowrap'}">
                  {{ el.content||'CONFIDENTIAL' }}
                </span>
              </div>

            </div>

            <!-- Page Footer -->
            <div v-if="settings.show_page_numbers || settings.footer_left || settings.footer_right"
              :style="{position:'absolute',bottom:'0',left:'0',right:'0',height:'36px',borderTop:`1px solid ${settings.primary_color||'#6366f1'}20`,display:'flex',alignItems:'center',padding:'0 32px'}">
              <span style="flex:1;font-size:10px;color:#94a3b8">{{ settings.footer_left||'' }}</span>
              <span v-if="settings.show_page_numbers" style="font-size:11px;color:#94a3b8">{{ pi+1 }}{{ pages.length>1?` / ${pages.length}`:'' }}</span>
              <span style="flex:1;text-align:right;font-size:10px;color:#94a3b8">{{ settings.footer_right||'' }}</span>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';

const props = defineProps({ report: Object });

const activePage   = ref(0);
const previewZoom  = ref(100);
const isDark       = ref(false);
const pageRefs     = ref([]);
const mainScrollArea = ref(null);
const chartInstances = new Map();
const todayFormatted = new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});

const pages   = computed(() => props.report.content || []);
const settings = computed(() => props.report.settings || {});
const downloadUrl = route('reports.download', props.report.slug);

const SIZES = {A4:{portrait:{w:794,h:1123},landscape:{w:1123,h:794}},Letter:{portrait:{w:816,h:1056},landscape:{w:1056,h:816}},Legal:{portrait:{w:816,h:1344},landscape:{w:1344,h:816}},A3:{portrait:{w:1123,h:1587},landscape:{w:1587,h:1123}}};
const dims     = computed(()=>SIZES[settings.value.page_size]?.[settings.value.orientation]??SIZES.A4.portrait);
const canvasW  = computed(()=>dims.value.w);
const canvasH  = computed(()=>dims.value.h);
const isLandscape = computed(()=>settings.value.orientation==='landscape');
const zf       = computed(()=>previewZoom.value/100);
const thumbScale = computed(()=>104/canvasW.value);

const pageContainerStyle = computed(()=>({
  width: (canvasW.value*zf.value)+'px',
}));
const pageStyle = computed(()=>({
  width:  (canvasW.value*zf.value)+'px',
  height: (canvasH.value*zf.value)+'px',
  backgroundColor: settings.value.background_color||'#fff',
  fontFamily: settings.value.font_family||"'DM Sans', sans-serif",
  position: 'relative',
  overflow: 'hidden',
}));

const elPosStyle = el => ({
  left:   (el.position.x*zf.value)+'px',
  top:    (el.position.y*zf.value)+'px',
  width:  ((el.styles?.width||200)*zf.value)+'px',
  height: ((el.styles?.height||50)*zf.value)+'px',
  transform: el.styles?.rotate?`rotate(${el.styles.rotate}deg)`:undefined,
  zIndex:  el.styles?.zIndex||1,
  opacity: (el.styles?.opacity??100)/100,
  boxShadow: {sm:'0 1px 3px rgba(0,0,0,.12)',md:'0 4px 12px rgba(0,0,0,.1)',lg:'0 10px 30px rgba(0,0,0,.15)',xl:'0 20px 60px rgba(0,0,0,.2)'}[el.styles?.boxShadow]||undefined,
  borderRadius: el.styles?.borderRadius?el.styles.borderRadius+'px':undefined,
  border: el.styles?.borderWidth?`${el.styles.borderWidth}px ${el.styles.borderStyle||'solid'} ${el.styles.borderColor||'#000'}`:undefined,
  padding: el.styles?.padding?el.styles.padding+'px':undefined,
  overflow: 'hidden',
});

const elTxtStyle = el => ({
  fontSize: ((el.styles?.fontSize||14)*zf.value)+'px',
  color:    el.styles?.color||'#1e293b',
  fontFamily: el.styles?.fontFamily||settings.value.font_family||"'DM Sans', sans-serif",
  fontWeight: el.styles?.fontWeight||(el.type==='heading'?'700':'400'),
  fontStyle:  el.styles?.fontStyle||'normal',
  textAlign:  el.styles?.textAlign||'left',
  textDecoration: el.styles?.textDecoration||'none',
  textTransform:  el.styles?.textTransform||'none',
  lineHeight: el.styles?.lineHeight||1.5,
  letterSpacing: el.styles?.letterSpacing?el.styles.letterSpacing+'px':undefined,
  backgroundColor: el.styles?.backgroundColor||undefined,
  overflow: 'hidden',
});

const isChart = t => ['bar-chart','line-chart','area-chart','pie-chart','doughnut-chart','radar-chart'].includes(t);
const CMAP    = {'bar-chart':'bar','line-chart':'line','area-chart':'line','pie-chart':'pie','doughnut-chart':'doughnut','radar-chart':'radar'};

const getElThumbColor = el => {
  if (el.type==='rectangle'||el.type==='circle') return el.styles?.backgroundColor||settings.value.primary_color||'#6366f1';
  if (['text','heading','subheading','quote'].includes(el.type)) return '#f1f5f9';
  if (isChart(el.type)) return (settings.value.primary_color||'#6366f1')+'40';
  if (el.type==='image') return '#e2e8f0';
  if (el.type==='metric') return el.styles?.backgroundColor||'#f8fafc';
  return '#f8fafc';
};

const initAllCharts = () => {
  pages.value.forEach(page => {
    page.elements.forEach(el => {
      if (!isChart(el.type)) return;
      nextTick(()=>{
        const canvas=document.getElementById('pv-chart-'+el.id); if(!canvas) return;
        if(chartInstances.has(el.id)){chartInstances.get(el.id).destroy();chartInstances.delete(el.id);}
        const type=CMAP[el.type]||'bar',primary=el.chartColor||settings.value.primary_color||'#6366f1';
        const pColors=el.pieColors?.length?el.pieColors:[primary,'#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
        const isArea=el.type==='area-chart';
        const chart=new Chart(canvas.getContext('2d'),{
          type, data:{labels:el.chartData?.labels||[],datasets:[{
            label:el.chartDatasetLabel||el.chartTitle||'Data',
            data:el.chartData?.values||[],
            backgroundColor:(type==='pie'||type==='doughnut')?pColors:(isArea?primary+'30':primary+'cc'),
            borderColor:(type==='pie'||type==='doughnut')?pColors.map(c=>c+'dd'):primary,
            borderWidth:2,fill:isArea,tension:isArea||type==='line'?0.4:0,
            pointBackgroundColor:primary,pointRadius:4,pointHoverRadius:6,
          }]},
          options:{responsive:true,maintainAspectRatio:false,
            animation:{duration:600,easing:'easeInOutQuart'},
            plugins:{
              legend:{position:'bottom',labels:{padding:14,font:{size:11,family:settings.value.font_family||"'DM Sans',sans-serif"},boxWidth:12,color:'#475569'}},
              title:{display:!!el.chartTitle,text:el.chartTitle||'',font:{size:13,weight:'600',family:settings.value.font_family||"'DM Sans',sans-serif"},color:'#1e293b',padding:{bottom:10}},
              tooltip:{backgroundColor:'rgba(15,23,42,.9)',titleColor:'#f1f5f9',bodyColor:'#cbd5e1',borderColor:primary+'50',borderWidth:1,cornerRadius:8,padding:10},
            },
            scales:(type==='pie'||type==='doughnut')?{}:(type==='radar'?{r:{grid:{color:'#f1f5f9'},ticks:{font:{size:10},color:'#94a3b8',backdropColor:'transparent'},pointLabels:{font:{size:11},color:'#475569'},angleLines:{color:'#f1f5f9'}}}:{
              x:{grid:{color:'#f8fafc',drawBorder:false},ticks:{font:{size:11,family:settings.value.font_family},color:'#94a3b8'},border:{color:'#f1f5f9'}},
              y:{grid:{color:'#f1f5f9',drawBorder:false},ticks:{font:{size:11,family:settings.value.font_family},color:'#94a3b8'},border:{color:'#f1f5f9'}},
            }),
          },
        });
        chartInstances.set(el.id,chart);
      });
    });
  });
};

// Page navigation
const goToPage = (pi) => {
  activePage.value = pi;
  nextTick(()=>{
    const el = pageRefs.value[pi];
    if(el && mainScrollArea.value) {
      el.scrollIntoView({ behavior:'smooth', block:'start' });
    }
  });
};
const goToPrev = () => { if(activePage.value>0) goToPage(activePage.value-1); };
const goToNext = () => { if(activePage.value<pages.value.length-1) goToPage(activePage.value+1); };

const goBack = () => router.get(route('reports.edit', props.report.slug));
const printReport = () => window.print();

onMounted(()=>{
  initAllCharts();
  // Intersection observer to update active page on scroll
  if(mainScrollArea.value && pages.value.length>1){
    const obs = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{ if(e.isIntersecting){ const idx=pageRefs.value.findIndex(r=>r===e.target); if(idx>=0) activePage.value=idx; } });
    },{root:mainScrollArea.value,threshold:0.5});
    nextTick(()=>pageRefs.value.forEach(el=>{ if(el) obs.observe(el); }));
  }
});
</script>

<style>
@media print {
  header, aside { display:none!important; }
  main { background:white!important; padding:0!important; overflow:visible!important; gap:0!important; }
  .print-page { page-break-after:always; break-after:page; opacity:1!important; transform:scale(1)!important; box-shadow:none!important; }
  .print-page:last-child { page-break-after:auto; }
}
</style>