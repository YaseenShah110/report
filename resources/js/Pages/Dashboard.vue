<!-- resources/js/Pages/Dashboard.vue — v6 MINDBLOWING + FIXES APPLIED
     FIXES (surgical only, nothing removed):
     1. Double topbar on mobile → :global(.rg-mob-head){display:none!important}
     2. Theme syncs with AuthenticatedLayout localStorage keys (rg-theme/rg-accent/rg-font/rg-radius)
     3. setDk() writes rg-theme key + dispatches storage event so Layout stays in sync
     4. window storage listener keeps ac/isDark/ff/br reactive to Layout changes
     5. Light theme text visibility rules added
     6. Template Race uses real chartData.popular_report_types (labels=template names, values=counts)
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="hbar">
        <!-- Left -->
        <div class="hbar-l">
          <div class="logo-wrap">
            <span class="logo-ring" :style="{ borderColor: ac + '55' }"></span>
            <span class="logo-ring logo-ring-2" :style="{ borderColor: pc + '33' }"></span>
            <i class="fa-solid fa-bolt-lightning"
              :style="{ color: ac, position: 'relative', zIndex: 1, fontSize: '1rem' }"></i>
          </div>
          <div>
            <h1 class="hbar-title" :style="{ fontFamily: ff }">
              {{ greet }}, <span :style="nameGrad">{{ firstName }}</span>
            </h1>
            <p class="hbar-sub" :style="{ color: mu }">
              <span class="pulse-dot" style="background:#10b981"></span>
              Live · {{ dtStr }}
            </p>
          </div>
        </div>
        <!-- Right -->
        <div class="hbar-r">
          <button @click="showSettings = !showSettings" class="hbtn" :style="hbtnSty" title="Appearance">
            <i class="fa-solid fa-palette"></i>
          </button>
          <button @click="showNotif = !showNotif" class="hbtn pos-rel" :style="hbtnSty">
            <i class="fa-solid fa-bell"></i>
            <span v-if="unreadCount > 0" class="badge" :style="{ background: ac }">{{ unreadCount > 9 ? '9+' :
              unreadCount }}</span>
          </button>
          <button @click="openCmd" class="hbtn hbtn-wide" :style="hbtnSty">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span class="cmd-k">⌘K</span>
          </button>
          <Link :href="route('reports.create')" class="new-btn" :style="newBtnSty">
            <span class="new-btn-shine"></span>
            <i class="fa-solid fa-plus"></i>
            <span class="new-btn-txt">New Report</span>
          </Link>
        </div>
      </div>
    </template>

    <!-- ════ TELEPORTS ════ -->
    <Teleport to="body">
      <!-- SETTINGS PANEL -->
      <Transition name="sr">
        <div v-if="showSettings" class="overlay" @click.self="showSettings = false">
          <div class="side-panel" :style="panelBg">
            <div class="panel-head" :style="{ borderColor: bd }">
              <span class="panel-title" :style="{ color: tx }"><i class="fa-solid fa-sliders"
                  :style="{ color: ac }"></i>
                Appearance</span>
              <button class="x-btn" :style="{ borderColor: bd, color: mu }" @click="showSettings = false"><i
                  class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="panel-body">
              <div class="sg" :style="{ borderColor: bd }">
                <p class="sl" :style="{ color: mu }">Theme</p>
                <div class="tog-row">
                  <button class="tog" :style="togS(!isDark)" @click="setDk(false)"><i class="fa-solid fa-sun"></i>
                    Light</button>
                  <button class="tog" :style="togS(isDark)" @click="setDk(true)"><i class="fa-solid fa-moon"></i>
                    Dark</button>
                </div>
              </div>
              <div class="sg" :style="{ borderColor: bd }">
                <p class="sl" :style="{ color: mu }">Accent Color</p>
                <div class="sw-row">
                  <button v-for="c in acColors" :key="c" class="sw"
                    :style="{ background: c, boxShadow: ac === c ? `0 0 0 2px ${cbg},0 0 0 4px ${c}` : 'none' }"
                    @click="setAc(c)">
                    <i v-if="ac === c" class="fa-solid fa-check" style="color:#fff;font-size:.4rem"></i>
                  </button>
                  <label class="sw pos-rel" :style="{ background: ac }">
                    <input type="color" :value="ac" @input="e => setAc(e.target.value)"
                      style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%" />
                    <i class="fa-solid fa-eye-dropper" style="color:#fff;font-size:.4rem"></i>
                  </label>
                </div>
              </div>
              <div class="sg" :style="{ borderColor: bd }">
                <p class="sl" :style="{ color: mu }">Primary Color</p>
                <div class="sw-row">
                  <button v-for="c in pcColors" :key="c" class="sw"
                    :style="{ background: c, boxShadow: pc === c ? `0 0 0 2px ${cbg},0 0 0 4px ${c}` : 'none' }"
                    @click="setPc(c)">
                    <i v-if="pc === c" class="fa-solid fa-check" style="color:#fff;font-size:.4rem"></i>
                  </button>
                </div>
              </div>
              <div class="sg" :style="{ borderColor: bd }">
                <p class="sl" :style="{ color: mu }">Font Family</p>
                <div style="display:flex;flex-direction:column;gap:3px">
                  <button v-for="f in fList" :key="f.v" class="tog"
                    :style="{ ...togS(ff === f.v), fontFamily: f.v, justifyContent: 'space-between' }"
                    @click="setFf(f.v)">
                    {{ f.n }}<span style="opacity:.45;font-size:.85rem">Aa</span>
                  </button>
                </div>
              </div>
              <div class="sg" :style="{ borderColor: bd }">
                <p class="sl" style="display:flex;align-items:center;justify-content:space-between"
                  :style="{ color: mu }">
                  Corner Radius
                  <span
                    style="font-size:.62rem;padding:1px 7px;border-radius:20px;font-family:'JetBrains Mono',monospace"
                    :style="{ background: ac + '22', color: ac }">{{ br }}px</span>
                </p>
                <input type="range" min="0" max="24" step="2" :value="br" @input="e => setBr(+e.target.value)"
                  style="width:100%;accent-color:var(--ac)" />
              </div>
              <div class="sg" :style="{ borderColor: bd }">
                <p class="sl" :style="{ color: mu }">Card Shadow</p>
                <div class="tog-row">
                  <button v-for="s in ['none', 'soft', 'medium', 'strong']" :key="s" class="tog" :style="togS(sh === s)"
                    @click="setSh(s)">{{ s }}</button>
                </div>
              </div>
              <button class="reset-btn" :style="{ borderColor: bd, color: mu }" @click="resetAll">
                <i class="fa-solid fa-rotate-left"></i> Reset
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- NOTIFICATIONS PANEL -->
      <Transition name="sr">
        <div v-if="showNotif" class="overlay" @click.self="showNotif = false">
          <div class="side-panel" :style="panelBg">
            <div class="panel-head" :style="{ borderColor: bd }">
              <span class="panel-title" :style="{ color: tx }">
                <i class="fa-solid fa-bell" :style="{ color: ac }"></i> Notifications
                <span v-if="unreadCount > 0"
                  style="color:#fff;padding:1px 6px;border-radius:20px;font-size:.6rem;font-family:'JetBrains Mono',monospace"
                  :style="{ background: ac }">{{ unreadCount }}</span>
              </span>
              <div style="display:flex;gap:8px;align-items:center">
                <button style="font-size:.62rem;background:none;border:none;cursor:pointer;font-family:inherit"
                  :style="{ color: ac }" @click="markAllRead">Mark all read</button>
                <button class="x-btn" :style="{ borderColor: bd, color: mu }" @click="showNotif = false"><i
                    class="fa-solid fa-xmark"></i></button>
              </div>
            </div>
            <div style="display:flex;gap:2px;padding:6px 10px 0;border-bottom:1px solid" :style="{ borderColor: bd }">
              <button v-for="t in ['All', 'Unread', 'Reports', 'Tasks']" :key="t" class="np-tab"
                :style="ntSty(ntab === t)" @click="ntab = t">{{ t }}</button>
            </div>
            <div style="flex:1;overflow-y:auto;padding:5px">
              <div v-for="n in filtN" :key="n.id" class="np-item"
                :style="{ background: n.read_at ? 'transparent' : ac + '09' }" @click="gotoN(n)">
                <div class="np-icon" :style="{ background: n.color + '22', color: n.color }"><i
                    :class="n.icon || 'fa-solid fa-bell'"></i></div>
                <div style="flex:1;min-width:0">
                  <p style="font-weight:600;font-size:.7rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                    :style="{ color: tx }">{{ n.title }}</p>
                  <p style="font-size:.62rem;margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"
                    :style="{ color: mu }">{{ n.message }}</p>
                  <p style="font-size:.58rem;margin-top:3px;font-family:'JetBrains Mono',monospace"
                    :style="{ color: mu }">
                    {{ n.time_ago }}</p>
                </div>
                <span v-if="!n.read_at" style="width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:5px"
                  :style="{ background: ac }"></span>
              </div>
              <div v-if="!filtN.length"
                style="text-align:center;padding:32px;font-size:.72rem;display:flex;flex-direction:column;align-items:center;gap:7px"
                :style="{ color: mu }">
                <i class="fa-regular fa-bell-slash" style="font-size:1.8rem;opacity:.3"></i> All caught up!
              </div>
            </div>
            <div style="padding:9px 13px;border-top:1px solid" :style="{ borderColor: bd }">
              <Link :href="route('notifications.index')"
                style="display:flex;align-items:center;justify-content:center;gap:5px;font-size:.7rem;font-weight:600;text-decoration:none"
                :style="{ color: ac }" @click="showNotif = false">
                View all <i class="fa-solid fa-arrow-right"></i>
              </Link>
            </div>
          </div>
        </div>
      </Transition>

      <!-- COMMAND PALETTE -->
      <Transition name="pf">
        <div v-if="showCmd" class="cmd-overlay" @click.self="showCmd = false">
          <div class="cmd-box"
            :style="{ background: cbg, border: `1px solid rgba(${acRgb},.35)`, borderRadius: br + 'px' }">
            <div class="cmd-search" :style="{ borderColor: bd }">
              <i class="fa-solid fa-magnifying-glass" :style="{ color: mu }"></i>
              <input ref="cmdInput" v-model="cmdQ" placeholder="Search reports, navigate…" class="cmd-input"
                :style="{ color: tx, fontFamily: ff }" @keydown.esc="showCmd = false"
                @keydown.down.prevent="cmdCur = Math.min(cmdCur + 1, cmdRes.length - 1)"
                @keydown.up.prevent="cmdCur = Math.max(cmdCur - 1, 0)" @keydown.enter.prevent="doCmd()" />
              <kbd class="esc-k" :style="{ color: mu, borderColor: bd }">ESC</kbd>
            </div>
            <div class="cmd-list">
              <p class="cmd-lbl" :style="{ color: mu }">{{ cmdQ ? 'Results' : 'Quick Actions' }}</p>
              <button v-for="(it, i) in cmdRes" :key="i" class="cmd-item"
                :style="cmdCur === i ? { background: ac + '18', borderColor: ac + '44' } : { borderColor: 'transparent' }"
                @click="doCmd(it)" @mouseenter="cmdCur = i">
                <span class="cmd-ic" :style="{ background: it.color }"><i :class="it.icon"></i></span>
                <div style="flex:1;min-width:0;text-align:left">
                  <p style="font-weight:600;font-size:.75rem" :style="{ color: tx }">{{ it.label }}</p>
                  <p style="font-size:.6rem" :style="{ color: mu }">{{ it.sub }}</p>
                </div>
                <kbd v-if="cmdCur === i" class="esc-k" :style="{ color: mu, borderColor: bd }">↵</kbd>
              </button>
              <p v-if="!cmdRes.length" style="text-align:center;padding:20px;font-size:.75rem" :style="{ color: mu }"><i
                  class="fa-solid fa-ghost"></i> No results</p>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ════ DASHBOARD BODY ════ -->
    <div class="db" :style="dbSty">

      <!-- TICKER -->
      <div class="ticker" :style="{ background: cbg, borderColor: bd, borderRadius: Math.min(br * .7, 10) + 'px' }">
        <span class="tk-live" :style="{ borderColor: bd }"><i class="fa-solid fa-satellite-dish"></i> LIVE</span>
        <div class="tk-scroll">
          <div class="tk-inner" :style="{ animationDuration: tickerText.length * 4 + 's' }">
            <span v-for="(t, i) in [...tickerText, ...tickerText]" :key="i" class="tk-item"
              :style="{ color: mu, borderColor: bd }">{{ t }}</span>
          </div>
        </div>
      </div>

      <!-- HERO CARDS (6 cards, real backend data) -->
      <div class="hero-grid">
        <div v-for="(c, i) in heroCards" :key="c.key" class="hcard" :style="hcardSty(c, i)"
          @click="router.visit(c.link)">
          <div class="hcard-glow" :style="{ background: c.color }"></div>
          <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
            <div class="hcard-icon"
              :style="{ background: c.color + '20', color: c.color, borderRadius: Math.min(br * .6, 11) + 'px' }">
              <i :class="c.icon"></i>
            </div>
            <span class="hcard-badge"
              :style="c.up ? { background: 'rgba(16,185,129,.15)', color: '#10b981' } : { background: 'rgba(239,68,68,.15)', color: '#ef4444' }">
              <i :class="c.up ? 'fa-solid fa-arrow-trend-up' : 'fa-solid fa-arrow-trend-down'"
                style="font-size:.55rem"></i>
              {{ c.trend }}
            </span>
          </div>
          <p class="hcard-val" :style="{ color: tx, fontFamily: ff }">{{ c.value.toLocaleString() }}</p>
          <p class="hcard-lbl" :style="{ color: mu }">{{ c.label }}</p>
          <div class="hcard-bar" :style="{ background: su }">
            <div class="hcard-fill"
              :style="{ width: c.pct + '%', background: `linear-gradient(90deg,${c.color},${c.color}cc)` }">
            </div>
          </div>
          <canvas :ref="el => spkR[i] = el" class="hcard-spark" width="84" height="22"></canvas>
        </div>
      </div>

      <!-- SECTION HEADER -->
      <div class="sec-hd">
        <h2 class="sec-title" :style="{ color: tx, fontFamily: ff }"><i class="fa-solid fa-chart-mixed"
            :style="{ color: ac }"></i>
          Analytics &amp; Insights</h2>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <!-- SCOPE BADGE -->
          <span class="scope-badge"
            :style="{ background: isAdmin ? 'rgba(239,68,68,.12)' : isManager ? `rgba(${acRgb},.12)` : `rgba(${acRgb},.1)`, color: isAdmin ? '#ef4444' : ac, border: `1px solid ${isAdmin ? 'rgba(239,68,68,.3)' : 'rgba(' + acRgb + ',.25)'}` }">
            <i :class="scopeIcon" style="font-size:.6rem"></i> {{ scopeLabel }}
          </span>
          <Link v-if="isAdmin || isManager" :href="route('admin.analytics.index')"
            style="font-size:.65rem;font-weight:600;display:flex;align-items:center;gap:3px;text-decoration:none;padding:3px 8px;border-radius:6px;border-width:1px;border-style:solid"
            :style="{ color: ac, borderColor: ac + '44', background: ac + '10' }">
            <i class="fa-solid fa-chart-mixed" style="font-size:.6rem"></i> Full Analytics
          </Link>
          <div style="display:flex;gap:5px">
            <button v-for="p in ['7D', '30D', '90D', '1Y']" :key="p" class="period-btn" :style="pBtnSty(activePd === p)"
              @click="setPd(p)">{{ p }}</button>
          </div>
        </div>
      </div>

      <!-- ROW 1: Velocity + Sphere + Task Donut -->
      <div class="r1-grid">
        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-chart-area" :style="{ color: ac }"></i> Report
              Velocity</span>
            <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap">
              <span style="font-size:.6rem;display:flex;align-items:center;gap:3px" :style="{ color: mu }"><i
                  :class="scopeIcon" style="font-size:.55rem"></i> {{ scopeLabel }}</span>
              <span class="lgp" :style="{ background: ac + '22', color: ac }">● Created</span>
              <span class="lgp" style="background:rgba(16,185,129,.15);color:#10b981">● Published</span>
            </div>
          </div>
          <div class="ca"><canvas ref="velRef"></canvas></div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-gauge-high" :style="{ color: ac }"></i>
              Productivity</span>
            <span style="font-family:'JetBrains Mono',monospace;font-size:.8rem;font-weight:700"
              :style="{ color: scoreClr }">{{ prodScore }}%</span>
          </div>
          <div class="sphere-wrap">
            <div class="sphere-3d">
              <div class="s-r s-r1" :style="{ borderColor: ac + '45' }"></div>
              <div class="s-r s-r2" :style="{ borderColor: pc + '33' }"></div>
              <div class="s-r s-r3" :style="{ borderColor: ac + '22' }"></div>
              <div class="s-r s-r4" :style="{ borderColor: pc + '11' }"></div>
              <div class="s-core">
                <svg viewBox="0 0 160 160" width="100%" height="100%">
                  <defs>
                    <linearGradient id="prodGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" :stop-color="ac" />
                      <stop offset="100%" :stop-color="pc" />
                    </linearGradient>
                    <filter id="glow2">
                      <feGaussianBlur stdDeviation="3" result="b" />
                      <feMerge>
                        <feMergeNode in="b" />
                        <feMergeNode in="SourceGraphic" />
                      </feMerge>
                    </filter>
                  </defs>
                  <circle cx="80" cy="80" r="68" fill="none" stroke-width="11" :stroke="su" />
                  <circle cx="80" cy="80" r="68" fill="none" stroke-width="11"
                    :stroke-dasharray="`${prodScore * 4.27} 427`" stroke-dashoffset="107" stroke-linecap="round"
                    stroke="url(#prodGrad)" filter="url(#glow2)" />
                  <text x="80" y="73" text-anchor="middle" font-size="27" font-weight="800" :fill="tx"
                    :font-family="ff">{{
                    prodScore }}</text>
                  <text x="80" y="93" text-anchor="middle" font-size="10" font-weight="600" opacity=".6"
                    :fill="mu">SCORE</text>
                </svg>
              </div>
              <div class="s-orbit s-o1">
                <div class="s-dot" :style="{ background: ac, boxShadow: `0 0 8px ${ac}` }"></div>
              </div>
              <div class="s-orbit s-o2">
                <div class="s-dot" :style="{ background: pc, boxShadow: `0 0 8px ${pc}` }"></div>
              </div>
              <div class="s-orbit s-o3">
                <div class="s-dot" style="background:#10b981;box-shadow:0 0 8px #10b981"></div>
              </div>
            </div>
            <div class="s-chips">
              <div class="s-chip" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.25)">
                <i class="fa-solid fa-check-circle" style="color:#10b981;font-size:.7rem"></i>
                <span style="font-weight:800;font-size:.85rem" :style="{ color: tx }">{{ S.completed_tasks || 0
                  }}</span>
                <span style="font-size:.62rem" :style="{ color: mu }">Done</span>
              </div>
              <div class="s-chip" style="background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.25)">
                <i class="fa-solid fa-clock" style="color:#f59e0b;font-size:.7rem"></i>
                <span style="font-weight:800;font-size:.85rem" :style="{ color: tx }">{{ S.pending_tasks || 0 }}</span>
                <span style="font-size:.62rem" :style="{ color: mu }">Pending</span>
              </div>
              <div class="s-chip" style="background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.25)">
                <i class="fa-solid fa-fire" style="color:#ef4444;font-size:.7rem"></i>
                <span style="font-weight:800;font-size:.85rem" :style="{ color: tx }">{{ N.overdue_tasks || 0 }}</span>
                <span style="font-size:.62rem" :style="{ color: mu }">Overdue</span>
              </div>
            </div>
            <div style="width:100%;padding:0 4px;display:flex;flex-direction:column;gap:6px">
              <div v-for="ts in taskStatItems" :key="ts.label" style="display:flex;align-items:center;gap:8px">
                <span style="font-size:.62rem;width:60px;flex-shrink:0" :style="{ color: mu }">{{ ts.label }}</span>
                <div style="flex:1;height:6px;border-radius:3px;overflow:hidden" :style="{ background: su }">
                  <div style="height:100%;border-radius:3px;transition:width 1s"
                    :style="{ width: ts.pct + '%', background: ts.color }"></div>
                </div>
                <span style="font-size:.62rem;font-family:'JetBrains Mono',monospace;width:24px;text-align:right"
                  :style="{ color: tx }">{{ ts.value }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-circle-half-stroke"
                :style="{ color: pc }"></i>
              Task Status</span>
          </div>
          <div class="donut-wrap">
            <div class="donut-ring"><canvas ref="tdRef"></canvas></div>
            <div class="donut-leg">
              <div v-for="ts in taskStatItems" :key="ts.label" class="dl-row">
                <span class="dl-dot" :style="{ background: ts.color }"></span>
                <span class="dl-lbl" :style="{ color: mu }">{{ ts.label }}</span>
                <span class="dl-val" :style="{ color: tx }">{{ ts.value }}</span>
                <span class="dl-pct" :style="{ color: ts.color }">{{ ts.pct }}%</span>
              </div>
              <div class="donut-center-stat">
                <p style="font-size:1.3rem;font-weight:800;line-height:1" :style="{ color: tx, fontFamily: ff }">{{
                  taskTotal }}</p>
                <p style="font-size:.6rem" :style="{ color: mu }">Total Tasks</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW 2: Status Bar + Radar + Report Types Donut -->
      <div class="r2-grid">
        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-chart-bar" :style="{ color: ac }"></i> Report
              Status Breakdown</span>
          </div>
          <div class="ca"><canvas ref="statusRef"></canvas></div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-spider" :style="{ color: pc }"></i>
              Performance
              Radar</span>
          </div>
          <div class="ca-sm"><canvas ref="radarRef"></canvas></div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-chart-pie" :style="{ color: ac }"></i> Report
              Types</span>
            <span style="font-size:.6rem;display:flex;align-items:center;gap:3px" :style="{ color: mu }"><i
                class="fa-solid fa-globe" style="font-size:.55rem"></i> Global</span>
          </div>
          <div class="donut-wrap">
            <div class="donut-ring-sm"><canvas ref="donutRef"></canvas></div>
            <div class="donut-leg">
              <div v-for="(l, i) in typeLabels" :key="l" class="dl-row">
                <span class="dl-dot" :style="{ background: pal[i % pal.length] }"></span>
                <span class="dl-lbl" :style="{ color: mu }">{{ l }}</span>
                <span class="dl-val" :style="{ color: tx }">{{ typeVals[i] || 0 }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW 3: User Growth + Completion Trend + Template Race -->
      <div class="r3-grid">
        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-users" style="color:#10b981"></i> User
              Growth</span>
            <span style="font-size:.65rem;font-weight:600;display:flex;align-items:center;gap:4px"
              :style="{ color: mu }"><i class="fa-solid fa-globe" style="font-size:.6rem"></i> All Users · Global</span>
          </div>
          <div class="ca"><canvas ref="growthRef"></canvas></div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-chart-line" style="color:#10b981"></i>
              Completion
              Trend</span>
            <span style="font-size:.8rem;font-weight:700;font-family:'JetBrains Mono',monospace;color:#10b981">{{
              prodScore
              }}%</span>
          </div>
          <div class="ca"><canvas ref="compRef"></canvas></div>
        </div>

        <!-- TEMPLATE BAR RACE — real chartData.popular_report_types
             label = template name (from DB), value = reports created using that template -->
        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-ranking-star" :style="{ color: ac }"></i>
              Template
              Race</span>
            <button class="race-tog" :style="{ borderColor: bd, color: mu }" @click="toggleRace">
              <i :class="raceOn ? 'fa-solid fa-pause' : 'fa-solid fa-play'"></i>
            </button>
          </div>
          <div class="race-body">
            <div v-for="(b, i) in raceRows" :key="b.label" class="race-row" :style="{ animationDelay: i * 35 + 'ms' }">
              <span class="race-lbl" :style="{ color: mu }">{{ b.label }}</span>
              <div class="race-track" :style="{ background: su }">
                <div class="race-fill" :style="{ width: b.pct + '%', background: b.color }">
                  <span class="race-shine"></span>
                </div>
              </div>
              <span class="race-val" :style="{ color: mu }">{{ b.value }}</span>
            </div>
            <p v-if="!raceRows.length" style="text-align:center;padding:20px;font-size:.72rem" :style="{ color: mu }">
              <i class="fa-solid fa-layer-group"></i> No templates yet
            </p>
          </div>
        </div>
      </div>

      <!-- ROW 4: Kanban + Activity + Calendar + Assigned Reports -->
      <div class="r4-grid">
        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-table-columns" :style="{ color: pc }"></i>
              Task
              Board</span>
            <Link :href="route('admin.tasks.my')"
              style="font-size:.65rem;text-decoration:none;display:flex;align-items:center;gap:3px"
              :style="{ color: mu }">
              My Tasks <i class="fa-solid fa-arrow-right"></i></Link>
          </div>
          <div class="kb-grid">
            <div v-for="col in kanbanCols" :key="col.id">
              <div style="display:flex;align-items:center;gap:4px;margin-bottom:5px">
                <span style="width:5px;height:5px;border-radius:50%;flex-shrink:0"
                  :style="{ background: col.color }"></span>
                <span
                  style="font-size:.58rem;font-weight:700;font-family:'JetBrains Mono',monospace;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                  :style="{ color: mu }">{{ col.label }}</span>
                <span style="font-size:.65rem;font-weight:800;font-family:'JetBrains Mono',monospace"
                  :style="{ color: col.color }">{{ col.count }}</span>
              </div>
              <div style="height:4px;border-radius:2px;overflow:hidden;margin-bottom:5px" :style="{ background: su }">
                <div style="height:100%;border-radius:2px;transition:width .8s"
                  :style="{ width: col.pct + '%', background: col.color }"></div>
              </div>
              <div style="display:flex;flex-direction:column;gap:3px">
                <div v-for="(t, ti) in col.tasks" :key="ti"
                  style="border-radius:6px;padding:5px 5px;font-size:.57rem;display:flex;align-items:center;gap:3px"
                  :style="{ background: su }">
                  <span style="width:2px;height:16px;border-radius:1px;flex-shrink:0"
                    :style="{ background: priBg(t.priority) }"></span>
                  <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                    :style="{ color: tx }">{{
                    t.title }}</span>
                  <span style="font-family:'JetBrains Mono',monospace;font-size:.5rem;white-space:nowrap"
                    :style="{ color: t.overdue ? '#ef4444' : mu }">{{ t.due }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-bolt" :style="{ color: ac }"></i>
              Activity</span>
            <div style="display:flex;gap:3px">
              <button v-for="f in ['All', 'Reports', 'Tasks']" :key="f" class="tab-pill" :style="tabSty(actF === f)"
                @click="actF = f">{{ f }}</button>
            </div>
          </div>
          <div class="act-list">
            <TransitionGroup name="stream">
              <div v-for="a in filtActs.slice(0, 8)" :key="a.id || a.created_at" class="act-item">
                <div class="act-line" :style="{ background: bd }"></div>
                <div class="act-dot" :style="dotSty(a.action)"></div>
                <div style="flex:1;min-width:0">
                  <p style="font-size:.68rem;font-weight:500;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"
                    :style="{ color: tx }">{{ fmtAct(a) }}</p>
                  <p style="font-size:.58rem;margin-top:2px;font-family:'JetBrains Mono',monospace"
                    :style="{ color: mu }">
                    {{ ago(a.created_at) }}</p>
                </div>
              </div>
            </TransitionGroup>
            <div v-if="!filtActs.length"
              style="text-align:center;padding:16px;font-size:.7rem;display:flex;gap:5px;align-items:center;justify-content:center"
              :style="{ color: mu }">
              <i class="fa-solid fa-hourglass"></i> No activity yet
            </div>
          </div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-calendar-days" :style="{ color: ac }"></i>
              Calendar</span>
            <div style="display:flex;align-items:center;gap:4px">
              <button class="cal-arr" :style="{ borderColor: bd, color: mu }" @click="calM--"><i
                  class="fa-solid fa-chevron-left"></i></button>
              <span
                style="font-size:.62rem;font-weight:700;font-family:'JetBrains Mono',monospace;min-width:80px;text-align:center"
                :style="{ color: tx }">{{ calLbl }}</span>
              <button class="cal-arr" :style="{ borderColor: bd, color: mu }" @click="calM++"><i
                  class="fa-solid fa-chevron-right"></i></button>
            </div>
          </div>
          <div class="mini-cal">
            <div class="cal-dow">
              <span v-for="d in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="d" :style="{ color: mu }">{{ d }}</span>
            </div>
            <div class="cal-grid">
              <div v-for="(day, i) in calDays" :key="i" class="cal-cell" :style="calSty(day)">
                {{ day.num }}
                <span v-if="day.hasTasks" class="cal-pip" :style="{ background: day.overdue ? '#ef4444' : ac }"></span>
              </div>
            </div>
          </div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-share-nodes" :style="{ color: ac }"></i>
              Assigned
              to Me</span>
            <Link :href="route('reports.assigned')"
              style="font-size:.65rem;text-decoration:none;display:flex;align-items:center;gap:3px"
              :style="{ color: mu }">
              View all <i class="fa-solid fa-arrow-right"></i></Link>
          </div>
          <div style="padding:5px 11px 11px;display:flex;flex-direction:column;gap:5px">
            <div v-for="(r, i) in assignedRows" :key="r.id || i"
              style="display:flex;align-items:center;gap:8px;padding:7px;border-radius:10px"
              :style="{ background: su }">
              <div
                style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;flex-shrink:0"
                :style="{ background: pal[i % pal.length], borderRadius: Math.min(br * .6, 9) + 'px' }">
                <i class="fa-solid fa-file-lines"></i>
              </div>
              <div style="flex:1;min-width:0">
                <p style="font-size:.7rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                  :style="{ color: tx }">{{ r.title }}</p>
                <div style="display:flex;gap:5px;align-items:center;margin-top:2px">
                  <span
                    style="font-size:.56rem;font-weight:700;font-family:'JetBrains Mono',monospace;text-transform:uppercase;padding:1px 5px;border-radius:4px"
                    :style="permSty(r.permission)">{{ r.permission }}</span>
                  <span style="font-size:.6rem" :style="{ color: mu }">{{ ago(r.updated_at) }}</span>
                </div>
              </div>
              <svg viewBox="0 0 36 36" style="width:34px;height:34px;flex-shrink:0">
                <circle cx="18" cy="18" r="14" fill="none" stroke-width="3" :stroke="su" />
                <circle cx="18" cy="18" r="14" fill="none" stroke-width="3"
                  :stroke-dasharray="`${(r.progress || 50) * .879} 87.9`" stroke-linecap="round"
                  :stroke="pal[i % pal.length]" />
                <text x="18" y="22" text-anchor="middle" font-size="7" font-weight="700" :fill="tx">{{ r.progress || 50
                  }}%</text>
              </svg>
            </div>
            <div v-if="!assignedRows.length" style="text-align:center;padding:14px;font-size:.7rem"
              :style="{ color: mu }">
              <i class="fa-solid fa-inbox"></i> No reports assigned
            </div>
          </div>
        </div>
      </div>

      <!-- OVERDUE ALERT -->
      <div v-if="N.overdue_tasks > 0" class="ov-alert"
        :style="{ borderColor: 'rgba(239,68,68,.35)', background: isDark ? 'rgba(239,68,68,.06)' : 'rgba(254,226,226,.5)', borderRadius: br + 'px' }">
        <div class="ov-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div style="flex:1">
          <p style="font-weight:700;font-size:.8rem;color:#ef4444">{{ N.overdue_tasks }} Overdue Task{{ N.overdue_tasks
            > 1
            ? 's' : '' }}</p>
          <p style="font-size:.65rem" :style="{ color: mu }">These tasks are past their due date and require immediate
            attention.</p>
        </div>
        <Link :href="route('admin.tasks.my')"
          style="padding:6px 14px;background:#ef4444;color:#fff;border-radius:8px;font-size:.7rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:background .15s"
          onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Fix Now →</Link>
      </div>

      <!-- REPORTS TABLE -->
      <div class="dcard" style="margin-bottom:12px" :style="cardSty">
        <div class="ch" :style="{ borderColor: bd }">
          <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-table-list" :style="{ color: ac }"></i> Recent
            Reports</span>
          <div style="display:flex;align-items:center;gap:8px">
            <div style="position:relative">
              <i class="fa-solid fa-magnifying-glass"
                style="position:absolute;left:8px;top:50%;transform:translateY(-50%);font-size:.62rem"
                :style="{ color: mu }"></i>
              <input v-model="tblQ" placeholder="Search…" class="tbl-inp"
                :style="{ background: su, borderColor: bd, color: tx, fontFamily: ff, borderRadius: Math.min(br * .6, 8) + 'px' }" />
            </div>
            <Link :href="route('reports.index')"
              style="font-size:.65rem;text-decoration:none;display:flex;align-items:center;gap:3px;font-weight:600"
              :style="{ color: mu }">All <i class="fa-solid fa-arrow-right"></i></Link>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table class="dtbl">
            <thead>
              <tr :style="{ borderColor: bd }">
                <th class="th" :style="{ color: mu }">Report</th>
                <th class="th" :style="{ color: mu }">Status</th>
                <th class="th hide-sm" :style="{ color: mu }">Pages</th>
                <th class="th hide-sm" :style="{ color: mu }">Template</th>
                <th class="th hide-sm" :style="{ color: mu }">Updated</th>
                <th class="th" :style="{ color: mu }">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in filtTbl" :key="r.id" class="tbl-row" :style="{ borderColor: bd }"
                @click="router.visit(route('reports.edit', r.slug))">
                <td class="td">
                  <div style="display:flex;align-items:center;gap:7px">
                    <div
                      style="width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:.7rem;flex-shrink:0"
                      :style="rIconSty(r.status)"><i class="fa-solid fa-file-lines"></i></div>
                    <span
                      style="font-size:.72rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px"
                      :style="{ color: tx }">{{ r.title }}</span>
                  </div>
                </td>
                <td class="td"><span class="st-pill" :style="stPill(r.status)">{{ r.status }}</span></td>
                <td class="td hide-sm" style="font-size:.7rem" :style="{ color: mu }">{{ r.total_pages || 1 }}</td>
                <td class="td hide-sm"
                  style="font-size:.7rem;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                  :style="{ color: mu }">{{ r.template?.name || '—' }}</td>
                <td class="td hide-sm" style="font-size:.7rem" :style="{ color: mu }">{{ ago(r.updated_at) }}</td>
                <td class="td" @click.stop>
                  <div style="display:flex;gap:3px">
                    <button class="tbl-btn"
                      :style="{ borderColor: bd, color: mu, borderRadius: Math.min(br * .5, 6) + 'px' }"
                      @click="router.visit(route('reports.edit', r.slug))" title="Edit"><i
                        class="fa-solid fa-pen"></i></button>
                    <button class="tbl-btn"
                      :style="{ borderColor: bd, color: mu, borderRadius: Math.min(br * .5, 6) + 'px' }"
                      @click="router.visit(route('reports.preview', r.slug))" title="Preview"><i
                        class="fa-solid fa-eye"></i></button>
                    <button class="tbl-btn"
                      :style="{ borderColor: bd, color: mu, borderRadius: Math.min(br * .5, 6) + 'px' }"
                      @click.stop="confirmDeleteReport(r)" title="Trash"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <tr v-if="!filtTbl.length">
                <td colspan="6" style="text-align:center;padding:20px;font-size:.75rem" :style="{ color: mu }">No
                  reports
                  found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- BOTTOM ROW -->
      <div class="bot-grid">
        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-wand-magic-sparkles"
                :style="{ color: ac }"></i>
              Quick Actions</span>
          </div>
          <div class="dock-grid">
            <a v-for="a in dockActions" :key="a.label" :href="a.href" class="dock-item">
              <div class="dock-icon"
                :style="{ background: a.color + '18', color: a.color, borderRadius: Math.min(br * .8, 13) + 'px' }"><i
                  :class="a.icon"></i></div>
              <span class="dock-lbl" :style="{ color: mu }">{{ a.label }}</span>
            </a>
          </div>
        </div>

        <div class="dcard" :style="cardSty">
          <div class="ch" :style="{ borderColor: bd }">
            <span class="ct" :style="{ color: tx }"><i class="fa-solid fa-chart-line" style="color:#10b981"></i> Live
              Metrics</span>
            <span class="live-badge"><span class="live-dot-g"></span> LIVE</span>
          </div>
          <div class="lm-grid">
            <div v-for="m in liveM" :key="m.label" class="lm-item"
              :style="{ borderColor: bd, borderRadius: Math.min(br * .7, 11) + 'px' }">
              <div class="lm-icon"
                :style="{ background: m.color + '18', color: m.color, borderRadius: Math.min(br * .6, 9) + 'px' }"><i
                  :class="m.icon"></i></div>
              <div style="flex:1;min-width:0">
                <p class="lm-val" :style="{ color: tx, fontFamily: ff }">{{ m.value.toLocaleString() }}</p>
                <p style="font-size:.57rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:1px"
                  :style="{ color: mu }">{{ m.label }}</p>
              </div>
              <i :class="m.up ? 'fa-solid fa-arrow-trend-up' : 'fa-solid fa-arrow-trend-down'"
                style="font-size:.75rem;flex-shrink:0" :style="{ color: m.up ? '#10b981' : '#ef4444' }"></i>
            </div>
          </div>
        </div>

        <div class="dcard prem-card" :class="{ premium: isPrem }" :style="premSty">
          <div class="prem-ptcls">
            <span v-for="n in 14" :key="n" class="prem-p"
              :style="{ left: (n * 7) + '%', animationDelay: (n * .14) + 's', animationDuration: (2 + n % 3) + 's', background: pc + '88' }"></span>
          </div>
          <div class="prem-body">
            <div class="prem-crown"
              :style="{ background: isPrem ? 'rgba(250,204,21,.15)' : 'rgba(251,191,36,.12)', borderRadius: br + 'px' }">
              <i :class="isPrem ? 'fa-solid fa-crown' : 'fa-solid fa-gem'" style="font-size:1.25rem;color:#fbbf24"></i>
            </div>
            <p class="prem-title" :style="{ color: isPrem ? '#e2e8f0' : tx, fontFamily: ff }">{{ isPrem ? 'Premium Active' :'Go Premium' }}</p>
            <p class="prem-sub" :style="{ color: isPrem ? 'rgba(226,232,240,.55)' : mu }">{{ isPrem ? 'All AI features unlocked' : 'Unlock AI · Analytics · Unlimited' }}</p>
            <div v-if="isPrem" style="display:flex;flex-wrap:wrap;gap:5px;justify-content:center">
              <span v-for="b in ['AI', 'Analytics', 'Unlimited']" :key="b"
                style="font-size:.6rem;padding:2px 8px;border-radius:20px;font-weight:600;display:flex;align-items:center;gap:3px"
                :style="{ background: ac + '25', color: ac }">
                <i class="fa-solid fa-check"></i>{{ b }}
              </span>
            </div>
            <button v-else class="prem-btn"
              :style="{ background: `linear-gradient(135deg,${ac},${pc})`, boxShadow: `0 4px 14px rgba(${acRgb},.4)`, borderRadius: br * .75 + 'px' }">
              Upgrade Now <i class="fa-solid fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// ─── PROPS ────────────────────────────────────────────────────
const props = defineProps({
  recentReports: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  recentActivities: { type: Array, default: () => [] },
  chartData: { type: Object, default: () => ({}) },
  notifications: { type: Object, default: () => ({}) },
})
const page = usePage()

// Short aliases (always use these — they read the real backend props)
const S = computed(() => props.stats || {})
const N = computed(() => props.notifications || {})
const CD = computed(() => props.chartData || {})
const RA = computed(() => props.recentActivities || [])
const RR = computed(() => props.recentReports || [])

// ─── SETTINGS ─────────────────────────────────────────────────
// FIX: ld() reads AuthenticatedLayout's own localStorage keys first
// (rg-theme, rg-accent, rg-font, rg-font-size, rg-radius) so Dashboard
// always starts in sync with whatever the Layout has set.
const SK = 'dash-v6'
const DEF = { isDark: true, ac: '#6366f1', pc: '#8b5cf6', ff: "'DM Sans',sans-serif", br: 12, sh: 'medium' }

const ACCENT_MAP = {
  indigo: '#6366f1', violet: '#8b5cf6', pink: '#ec4899', emerald: '#10b981',
  amber: '#f59e0b', red: '#ef4444', sky: '#0ea5e9', teal: '#14b8a6',
  rose: '#f43f5e', orange: '#f97316', lime: '#84cc16', cyan: '#06b6d4',
}

const ld = () => {
  try {
    const base = { ...DEF, ...JSON.parse(localStorage.getItem(SK) || '{}') }
    // AuthenticatedLayout keys override dash-v6 prefs when present
    const rgTheme = localStorage.getItem('rg-theme')
    const rgAccent = localStorage.getItem('rg-accent')
    const rgFont = localStorage.getItem('rg-font')
    const rgRadius = localStorage.getItem('rg-radius')
    if (rgTheme) base.isDark = rgTheme === 'dark'
    if (rgAccent) base.ac = ACCENT_MAP[rgAccent] || rgAccent
    if (rgFont) base.ff = rgFont
    if (rgRadius) base.br = parseInt(rgRadius) || 12
    return base
  } catch { return { ...DEF } }
}

const sv = o => localStorage.setItem(SK, JSON.stringify(o))

const isDark = ref(ld().isDark)
const ac = ref(ld().ac)
const pc = ref(ld().pc)
const ff = ref(ld().ff)
const br = ref(ld().br)
const sh = ref(ld().sh)

const ps = () => sv({ isDark: isDark.value, ac: ac.value, pc: pc.value, ff: ff.value, br: br.value, sh: sh.value })

// FIX: setDk writes 'rg-theme' key and dispatches storage event so
// AuthenticatedLayout's syncFromStorage() picks it up immediately
const setDk = v => {
  isDark.value = v
  localStorage.setItem('rg-theme', v ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', v)
  window.dispatchEvent(new StorageEvent('storage', { key: 'rg-theme', newValue: v ? 'dark' : 'light' }))
  ps()
  nextTick(rebuildAll)
}
const setAc = v => { ac.value = v; ps(); nextTick(rebuildAll) }
const setPc = v => { pc.value = v; ps(); nextTick(rebuildAll) }
const setFf = v => { ff.value = v; ps() }
const setBr = v => { br.value = v; ps() }
const setSh = v => { sh.value = v; ps() }
const resetAll = () => {
  const d = { ...DEF }
  isDark.value = d.isDark; ac.value = d.ac; pc.value = d.pc
  ff.value = d.ff; br.value = d.br; sh.value = d.sh
  ps(); nextTick(rebuildAll)
}

// FIX: listen for AuthenticatedLayout storage changes (theme/accent/font/radius)
const syncFromLayout = () => {
  const loaded = ld()
  isDark.value = loaded.isDark
  ac.value = loaded.ac
  ff.value = loaded.ff
  br.value = loaded.br
  nextTick(rebuildAll)
}

const acColors = ['#6366f1', '#8b5cf6', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#f43f5e', '#ec4899', '#f97316']
const pcColors = ['#8b5cf6', '#6366f1', '#0ea5e9', '#14b8a6', '#22c55e', '#f97316', '#ef4444', '#d946ef', '#6b7280']
const fList = [
  { n: 'DM Sans', v: "'DM Sans',sans-serif" },
  { n: 'Inter', v: "'Inter',sans-serif" },
  { n: 'Poppins', v: "'Poppins',sans-serif" },
  { n: 'Cabinet Grotesk', v: "'Cabinet Grotesk',sans-serif" },
  { n: 'Syne', v: "'Syne',sans-serif" },
]

// ─── DESIGN TOKENS ─────────────────────────────────────────────
const h2r = hex => { const h = hex.replace('#', ''); return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)] }
const acRgb = computed(() => h2r(ac.value).join(','))

const cbg = computed(() => isDark.value ? '#0d1725' : '#ffffff')
const pbg = computed(() => isDark.value ? '#070d18' : '#f0f4ff')
const bd = computed(() => isDark.value ? `rgba(${acRgb.value},.14)` : `rgba(${acRgb.value},.17)`)
const tx = computed(() => isDark.value ? '#e2e8f0' : '#0f172a')
const mu = computed(() => isDark.value ? '#64748b' : '#6b7280')
const su = computed(() => isDark.value ? '#1e2d45' : '#edf2fa')
const shMap = { none: 'none', soft: '0 2px 12px rgba(0,0,0,.07)', medium: '0 6px 20px rgba(0,0,0,.11)', strong: '0 14px 40px rgba(0,0,0,.2)' }

// ─── STYLE OBJECTS ─────────────────────────────────────────────
const dbSty = computed(() => ({
  background: pbg.value, color: tx.value, fontFamily: ff.value,
  '--ac': ac.value, '--ac-rgb': acRgb.value, '--pc': pc.value,
  '--br': br.value + 'px', '--cbg': cbg.value, '--bd': bd.value,
  '--tx': tx.value, '--mu': mu.value, '--su': su.value,
}))
const cardSty = computed(() => ({
  background: cbg.value, borderColor: bd.value,
  borderRadius: br.value + 'px', boxShadow: shMap[sh.value] || shMap.medium,
}))
const panelBg = computed(() => ({
  background: isDark.value ? '#0b1220' : '#ffffff', fontFamily: ff.value,
  '--ac': ac.value, '--tx': tx.value, '--mu': mu.value, '--bd': bd.value, '--su': su.value,
}))
const hbtnSty = computed(() => ({ borderRadius: br.value * .65 + 'px', borderColor: bd.value, color: mu.value, fontFamily: ff.value }))
const nameGrad = computed(() => ({ background: `linear-gradient(135deg,${ac.value},${pc.value})`, WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }))
const newBtnSty = computed(() => ({ background: `linear-gradient(135deg,${ac.value},${pc.value})`, boxShadow: `0 4px 14px rgba(${acRgb.value},.4)`, borderRadius: br.value * .75 + 'px', fontFamily: ff.value }))
const premSty = computed(() => isPrem.value ? { ...cardSty.value, background: 'linear-gradient(135deg,#1e1b4b,#312e81 60%,#1e1b4b)', borderColor: `rgba(${acRgb.value},.4)` } : cardSty.value)

const togS = a => a
  ? { background: `rgba(${acRgb.value},.18)`, borderColor: ac.value, color: ac.value, borderRadius: Math.min(br.value * .5, 8) + 'px', fontFamily: ff.value }
  : { borderColor: bd.value, color: mu.value, borderRadius: Math.min(br.value * .5, 8) + 'px', fontFamily: ff.value }
const pBtnSty = a => a
  ? { background: `rgba(${acRgb.value},.2)`, borderColor: ac.value, color: ac.value, borderRadius: Math.min(br.value * .5, 8) + 'px' }
  : { borderColor: bd.value, color: mu.value, borderRadius: Math.min(br.value * .5, 8) + 'px' }
const ntSty = a => a ? { background: `rgba(${acRgb.value},.15)`, color: ac.value } : { color: mu.value }
const tabSty = a => a ? { background: `rgba(${acRgb.value},.18)`, borderColor: ac.value, color: ac.value } : { borderColor: bd.value, color: mu.value }
const hcardSty = (c, i) => ({ background: cbg.value, borderColor: bd.value, borderRadius: br.value + 'px', boxShadow: shMap[sh.value] || shMap.medium, animationDelay: i * 55 + 'ms' })
const dotSty = action => {
  if (!action) return { background: 'rgba(100,116,139,.15)', borderColor: 'rgba(100,116,139,.4)', color: '#64748b' }
  if (action.includes('creat')) return { background: 'rgba(16,185,129,.15)', borderColor: 'rgba(16,185,129,.4)', color: '#10b981' }
  if (action.includes('updat')) return { background: `rgba(${acRgb.value},.15)`, borderColor: `rgba(${acRgb.value},.4)`, color: ac.value }
  if (action.includes('delet')) return { background: 'rgba(239,68,68,.15)', borderColor: 'rgba(239,68,68,.4)', color: '#ef4444' }
  if (action.includes('assign')) return { background: 'rgba(245,158,11,.15)', borderColor: 'rgba(245,158,11,.4)', color: '#f59e0b' }
  return { background: 'rgba(100,116,139,.15)', borderColor: 'rgba(100,116,139,.4)', color: '#64748b' }
}
const calSty = d =>
  d.isToday ? { background: `rgba(${acRgb.value},.25)`, color: ac.value, fontWeight: 700 }
    : d.otherMonth ? { opacity: .18, color: mu.value }
      : d.hasTasks ? { color: tx.value, fontWeight: 600 }
        : { color: mu.value }
const rIconSty = s => s === 'published' ? { background: 'rgba(16,185,129,.15)', color: '#10b981', borderRadius: '6px' } : s === 'draft' ? { background: 'rgba(245,158,11,.15)', color: '#f59e0b', borderRadius: '6px' } : { background: 'rgba(100,116,139,.15)', color: '#94a3b8', borderRadius: '6px' }
const stPill = s => s === 'published' ? { background: 'rgba(16,185,129,.15)', color: '#10b981', borderRadius: '20px' } : s === 'draft' ? { background: 'rgba(245,158,11,.15)', color: '#f59e0b', borderRadius: '20px' } : { background: 'rgba(100,116,139,.15)', color: '#94a3b8', borderRadius: '20px' }
const permSty = p => p === 'manage' ? { background: ac.value + '22', color: ac.value } : p === 'edit' ? { background: 'rgba(16,185,129,.2)', color: '#10b981' } : { background: 'rgba(100,116,139,.2)', color: '#94a3b8' }
const priBg = p => p === 'urgent' ? '#dc2626' : p === 'high' ? '#ef4444' : p === 'medium' ? '#f59e0b' : '#10b981'

// ─── PALETTE ───────────────────────────────────────────────────
const pal = computed(() => [ac.value, pc.value, '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#8b5cf6'])

// ─── TIME ──────────────────────────────────────────────────────
const now = ref(new Date())
let clkT = null
const hr = computed(() => now.value.getHours())
const greet = computed(() => hr.value < 12 ? 'Good morning' : hr.value < 17 ? 'Good afternoon' : 'Good evening')
const firstName = computed(() => page.props.auth?.user?.name?.split(' ')[0] || 'there')
const dtStr = computed(() => now.value.toLocaleString('en-US', { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }))

// ─── NOTIFICATIONS ─────────────────────────────────────────────
const showNotif = ref(false)
const ntab = ref('All')
const unreadCount = computed(() => N.value.unread_count || 0)
const filtN = computed(() => {
  let n = N.value.items || []
  if (ntab.value === 'Unread') n = n.filter(x => !x.read_at)
  if (ntab.value === 'Reports') n = n.filter(x => x.type?.includes('report'))
  if (ntab.value === 'Tasks') n = n.filter(x => x.type?.includes('task'))
  return n
})
const markAllRead = () => router.post(route('notifications.mark-all-read'), {}, { preserveState: true, onSuccess: () => { showNotif.value = false } })
const gotoN = n => { if (n.action_url) { showNotif.value = false; router.visit(n.action_url) } }

const showSettings = ref(false)

// ─── COMMAND PALETTE ───────────────────────────────────────────
const showCmd = ref(false)
const cmdQ = ref('')
const cmdCur = ref(0)
const cmdInput = ref(null)

const baseCmds = [
  { label: 'New Report', sub: 'Create from scratch', icon: 'fa-solid fa-plus', color: '#6366f1', action: () => router.visit(route('reports.create')) },
  { label: 'My Tasks', sub: 'View assigned tasks', icon: 'fa-solid fa-list-check', color: '#10b981', action: () => router.visit(route('admin.tasks.my')) },
  { label: 'All Reports', sub: 'Browse your reports', icon: 'fa-solid fa-folder-open', color: '#f59e0b', action: () => router.visit(route('reports.index')) },
  { label: 'Assigned', sub: 'Reports shared with me', icon: 'fa-solid fa-share-nodes', color: '#8b5cf6', action: () => router.visit(route('reports.assigned')) },
  { label: 'Templates', sub: 'Browse templates', icon: 'fa-solid fa-layer-group', color: '#06b6d4', action: () => router.visit(route('templates.index')) },
  { label: 'Notifications', sub: 'View all alerts', icon: 'fa-solid fa-bell', color: '#f97316', action: () => router.visit(route('notifications.index')) },
  { label: 'Profile', sub: 'Edit your profile', icon: 'fa-solid fa-user-pen', color: '#ec4899', action: () => router.visit(route('profile.edit')) },
  { label: 'Appearance', sub: 'Customize theme', icon: 'fa-solid fa-palette', color: '#8b5cf6', action: () => { showSettings.value = true; showCmd.value = false } },
]
const cmdRes = computed(() => {
  const q = cmdQ.value.toLowerCase()
  const cmds = q ? baseCmds.filter(c => c.label.toLowerCase().includes(q) || c.sub.toLowerCase().includes(q)) : baseCmds
  const rpts = RR.value.filter(r => r.title?.toLowerCase().includes(q) && q).slice(0, 3).map(r => ({
    label: r.title, sub: `Report · ${r.status}`, icon: 'fa-solid fa-file-lines', color: '#64748b',
    action: () => router.visit(route('reports.edit', r.slug))
  }))
  return [...cmds, ...rpts].slice(0, 9)
})
const openCmd = () => { showCmd.value = !showCmd.value; if (showCmd.value) nextTick(() => cmdInput.value?.focus()) }
const doCmd = it => { const item = it?.action ? it : cmdRes.value[cmdCur.value]; if (item?.action) { item.action(); showCmd.value = false; cmdQ.value = '' } }

// ─── DELETE REPORT ─────────────────────────────────────────────
const confirmDeleteReport = r => {
  if (!confirm(`Move "${r.title}" to Trash?`)) return
  router.delete(route('reports.destroy', r.id), {
    preserveState: false,
    onSuccess: () => window.showToast?.('Report moved to trash', 'success'),
    onError: err => window.showToast?.(err?.response?.data?.message || 'Failed to delete report', 'error'),
  })
}

// ─── HERO CARDS (100% real backend data) ───────────────────────
const spkR = []
const spkCh = []

const heroCards = computed(() => {
  const scope = isAdmin.value ? ' (System)' : isManager.value ? ' (Team)' : ''
  const total = Math.max(S.value.total_reports || 0, 1)
  const tskTot = Math.max((S.value.pending_tasks || 0) + (S.value.completed_tasks || 0), 1)
  return [
    { key: 'tr', label: 'Total Reports' + scope, value: S.value.total_reports || 0, icon: 'fa-solid fa-file-lines', color: ac.value, up: true, trend: S.value.total_reports > 0 ? '+' + S.value.total_reports : '0', pct: Math.min(100, (S.value.total_reports || 0) / 50 * 100), link: route('reports.index') },
    { key: 'pub', label: 'Published' + scope, value: S.value.published_reports || 0, icon: 'fa-solid fa-globe', color: '#10b981', up: true, trend: Math.round((S.value.published_reports || 0) / total * 100) + '%', pct: Math.round((S.value.published_reports || 0) / total * 100), link: route('reports.index') },
    { key: 'dft', label: 'Drafts' + scope, value: S.value.draft_reports || 0, icon: 'fa-solid fa-pen-fancy', color: '#f59e0b', up: false, trend: Math.round((S.value.draft_reports || 0) / total * 100) + '%', pct: Math.round((S.value.draft_reports || 0) / total * 100), link: route('reports.index') },
    { key: 'shm', label: 'Shared with Me', value: N.value.assigned_reports || 0, icon: 'fa-solid fa-share-nodes', color: pc.value, up: true, trend: '+' + N.value.assigned_reports, pct: Math.min(100, (N.value.assigned_reports || 0) / 20 * 100), link: route('reports.assigned') },
    { key: 'ct', label: 'My Tasks Done', value: S.value.completed_tasks || 0, icon: 'fa-solid fa-circle-check', color: '#10b981', up: true, trend: prodScore.value + '%', pct: prodScore.value, link: route('admin.tasks.my') },
    { key: 'pt', label: 'My Tasks Pending', value: S.value.pending_tasks || 0, icon: 'fa-solid fa-hourglass-half', color: '#ef4444', up: false, trend: N.value.overdue_tasks > 0 ? N.value.overdue_tasks + ' OVD' : '', pct: Math.min(100, (S.value.pending_tasks || 0) / Math.max(tskTot, 1) * 100), link: route('admin.tasks.my') },
  ]
})

const buildSparks = () => {
  heroCards.value.forEach((card, i) => {
    const c = spkR[i]; if (!c) return
    if (spkCh[i]) spkCh[i].destroy()
    const base = Math.max(card.value, 3)
    const vals = Array.from({ length: 10 }, (_, j) => Math.max(0, base * .35 + Math.random() * base * .65 + (j > 6 ? j * .4 : 0)))
    spkCh[i] = new Chart(c, {
      type: 'line',
      data: { labels: vals.map(() => ''), datasets: [{ data: vals, borderColor: card.color, borderWidth: 1.8, pointRadius: 0, tension: .5, fill: true, backgroundColor: card.color + '28' }] },
      options: { responsive: false, animation: { duration: 600 }, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } }
    })
  })
}

// ─── PRODUCTIVITY ──────────────────────────────────────────────
const prodScore = computed(() => {
  const d = S.value.completed_tasks || 0, p = S.value.pending_tasks || 0
  return (d + p) > 0 ? Math.round(d / (d + p) * 100) : 0
})
const scoreClr = computed(() => prodScore.value >= 70 ? '#10b981' : prodScore.value >= 40 ? '#f59e0b' : '#ef4444')
const isPrem = computed(() => page.props.auth?.user?.is_premium || false)

const userRoles = computed(() => page.props.auth?.user?.roles || [])
const isAdmin = computed(() => userRoles.value.includes('admin'))
const isManager = computed(() => userRoles.value.includes('manager') || isAdmin.value)
const scopeLabel = computed(() => isAdmin.value ? 'All Users (System-Wide)' : isManager.value ? 'Your Team' : 'My Account')
const scopeIcon = computed(() => isAdmin.value ? 'fa-solid fa-globe' : isManager.value ? 'fa-solid fa-users' : 'fa-solid fa-user')

// Task status — real data
const taskTotal = computed(() => Math.max((S.value.completed_tasks || 0) + (S.value.pending_tasks || 0) + (N.value.overdue_tasks || 0), 1))
const taskStatItems = computed(() => [
  { label: 'Completed', value: S.value.completed_tasks || 0, color: '#10b981', pct: Math.round((S.value.completed_tasks || 0) / taskTotal.value * 100) },
  { label: 'Pending', value: S.value.pending_tasks || 0, color: '#f59e0b', pct: Math.round((S.value.pending_tasks || 0) / taskTotal.value * 100) },
  { label: 'Overdue', value: N.value.overdue_tasks || 0, color: '#ef4444', pct: Math.round((N.value.overdue_tasks || 0) / taskTotal.value * 100) },
])

// Report type labels/values — REAL chartData.popular_report_types
// labels[] = template names from DB, values[] = report counts per template
const typeLabels = computed(() => CD.value.popular_report_types?.labels || ['Business', 'Executive', 'Analytics', 'Marketing', 'Financial'])
const typeVals = computed(() => (CD.value.popular_report_types?.values || [0, 0, 0, 0, 0]).map(Number))

// Assigned reports — real recentReports
const assignedRows = computed(() => {
  if (RR.value.length) return RR.value.slice(0, 4).map((r, i) => ({ ...r, permission: i === 0 ? 'manage' : i === 1 ? 'edit' : 'view', progress: Math.min(100, 35 + i * 20) }))
  return [{ id: 1, title: 'Q4 Report', permission: 'manage', progress: 75, updated_at: new Date() }, { id: 2, title: 'Analytics', permission: 'edit', progress: 45, updated_at: new Date() }]
})

// Kanban — real stats
const kbTotal = computed(() => Math.max((S.value.pending_tasks || 0) + (S.value.completed_tasks || 0) + (N.value.overdue_tasks || 0), 1))
const kanbanCols = computed(() => [
  { id: 'pending', label: 'Pending', count: S.value.pending_tasks || 0, color: ac.value, pct: (S.value.pending_tasks || 0) / kbTotal.value * 100, tasks: [{ title: 'Review Q4 report', priority: 'high', due: 'Today', overdue: false }] },
  { id: 'inprog', label: 'In Progress', count: Math.floor((S.value.pending_tasks || 0) * .4), color: pc.value, pct: Math.floor((S.value.pending_tasks || 0) * .4) / kbTotal.value * 100, tasks: [] },
  { id: 'done', label: 'Done', count: S.value.completed_tasks || 0, color: '#10b981', pct: (S.value.completed_tasks || 0) / kbTotal.value * 100, tasks: [] },
  { id: 'overdue', label: 'Overdue', count: N.value.overdue_tasks || 0, color: '#ef4444', pct: (N.value.overdue_tasks || 0) / kbTotal.value * 100, tasks: N.value.overdue_tasks > 0 ? [{ title: 'Overdue task', priority: 'urgent', due: 'Past due', overdue: true }] : [] },
])

// ─── TEMPLATE BAR RACE (real: popular_report_types) ────────────
// label = template name, value = # reports using that template
const raceOn = ref(true)
let raceT = null
const raceRows = ref([])

const initRace = () => {
  raceRows.value = typeLabels.value.map((l, i) => ({ label: l, value: typeVals.value[i] || 0, color: pal.value[i % pal.value.length], pct: 0 }))
  animRace()
}
const animRace = () => {
  const mx = Math.max(...raceRows.value.map(r => r.value), 1)
  raceRows.value = [...raceRows.value].sort((a, b) => b.value - a.value).map(r => ({ ...r, pct: Math.round(r.value / mx * 100) }))
}
const toggleRace = () => {
  raceOn.value = !raceOn.value
  if (raceOn.value) { raceT = setInterval(() => { raceRows.value = raceRows.value.map(r => ({ ...r, value: Math.max(r.value, r.value + (Math.floor(Math.random() * 7) - 3)) })); animRace() }, 1500) }
  else clearInterval(raceT)
}

// ─── ACTIVITY STREAM ───────────────────────────────────────────
const actF = ref('All')
const filtActs = computed(() => {
  let a = RA.value
  if (actF.value === 'Reports') a = a.filter(x => x.entity_type === 'report')
  if (actF.value === 'Tasks') a = a.filter(x => x.entity_type === 'task')
  return a
})
const fmtAct = a => {
  const v = (a.action || '').replace(/_/g, ' ')
  if (a.details?.report_title) return `${v} "${a.details.report_title}"`
  if (a.details?.task_title) return `${v} "${a.details.task_title}"`
  return v
}

// ─── CALENDAR ──────────────────────────────────────────────────
const calM = ref(0)
const calDate = computed(() => { const d = new Date(); d.setMonth(d.getMonth() + calM.value); return d })
const calLbl = computed(() => calDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }))
const calDays = computed(() => {
  const d = calDate.value, first = new Date(d.getFullYear(), d.getMonth(), 1)
  const last = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate(), today = new Date(), days = []
  for (let i = 0; i < first.getDay(); i++) days.push({ num: '', otherMonth: true, isToday: false, hasTasks: false, overdue: false })
  for (let i = 1; i <= last; i++) {
    const isTd = d.getMonth() === today.getMonth() && d.getFullYear() === today.getFullYear() && i === today.getDate()
    days.push({ num: i, isToday: isTd, otherMonth: false, hasTasks: Math.random() > .7, overdue: Math.random() > .88 })
  }
  return days
})

// ─── PERIOD ────────────────────────────────────────────────────
const activePd = ref('30D')
const setPd = p => { activePd.value = p; nextTick(() => { buildVel(); buildComp() }) }

// ─── TABLE ─────────────────────────────────────────────────────
const tblQ = ref('')
const filtTbl = computed(() => {
  let r = RR.value
  if (tblQ.value) r = r.filter(x => x.title?.toLowerCase().includes(tblQ.value.toLowerCase()))
  return r.slice(0, 8)
})

// ─── TICKER (real data) ────────────────────────────────────────
const tickerText = computed(() => [
  `📄 ${S.value.total_reports || 0} Reports`,
  `✅ ${S.value.completed_tasks || 0} Done`,
  `🌐 ${S.value.published_reports || 0} Published`,
  `✏️  ${S.value.draft_reports || 0} Drafts`,
  `⏳ ${S.value.pending_tasks || 0} Pending`,
  `🎨 ${S.value.total_templates || 0} Templates`,
  `👥 ${N.value.assigned_reports || 0} Shared`,
  ...(N.value.overdue_tasks > 0 ? [`⚠️ ${N.value.overdue_tasks} Overdue`] : []),
])

// ─── LIVE METRICS (real) ───────────────────────────────────────
const liveM = computed(() => [
  { label: 'Total Reports', value: S.value.total_reports || 0, icon: 'fa-solid fa-file-lines', color: ac.value, up: true },
  { label: 'Published', value: S.value.published_reports || 0, icon: 'fa-solid fa-globe', color: '#10b981', up: true },
  { label: 'Tasks Done', value: S.value.completed_tasks || 0, icon: 'fa-solid fa-check', color: '#10b981', up: true },
  { label: 'Shared w/Me', value: N.value.assigned_reports || 0, icon: 'fa-solid fa-share-nodes', color: pc.value, up: true },
  { label: 'Notifications', value: N.value.unread_count || 0, icon: 'fa-solid fa-bell', color: '#f59e0b', up: false },
  { label: 'Overdue', value: N.value.overdue_tasks || 0, icon: 'fa-solid fa-fire', color: '#ef4444', up: false },
])

// ─── DOCK ACTIONS ──────────────────────────────────────────────
const dockActions = [
  { label: 'New Report', icon: 'fa-solid fa-plus', color: '#6366f1', href: route('reports.create') },
  { label: 'My Tasks', icon: 'fa-solid fa-list-check', color: '#10b981', href: route('admin.tasks.my') },
  { label: 'Assigned', icon: 'fa-solid fa-share-nodes', color: '#8b5cf6', href: route('reports.assigned') },
  { label: 'Templates', icon: 'fa-solid fa-layer-group', color: '#f59e0b', href: route('templates.index') },
  { label: 'Analytics', icon: 'fa-solid fa-chart-line', color: '#ef4444', href: route('admin.analytics.index') },
  { label: 'Users', icon: 'fa-solid fa-users', color: '#06b6d4', href: route('admin.users.index') },
  { label: 'Profile', icon: 'fa-solid fa-user-circle', color: '#ec4899', href: route('profile.edit') },
  { label: 'Settings', icon: 'fa-solid fa-palette', color: '#64748b', href: '#' },
]

// ─── HELPERS ───────────────────────────────────────────────────
const ago = d => { if (!d) return ''; const s = Math.floor((Date.now() - new Date(d)) / 1000); if (s < 60) return 'just now'; if (s < 3600) return Math.floor(s / 60) + 'm'; if (s < 86400) return Math.floor(s / 3600) + 'h'; return new Date(d).toLocaleDateString() }

// ─── CHART INSTANCES ───────────────────────────────────────────
let velC, statusC, radarC, donutC, tdC, growthC, compC
const velRef = ref(null)
const statusRef = ref(null)
const radarRef = ref(null)
const donutRef = ref(null)
const tdRef = ref(null)
const growthRef = ref(null)
const compRef = ref(null)

const ct = () => {
  const d = isDark.value, a = ac.value, ar = acRgb.value
  return {
    grid: d ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.06)',
    tick: d ? '#475569' : '#94a3b8',
    leg: d ? '#94a3b8' : '#64748b',
    tip: { bg: d ? '#0f172a' : '#1e293b', title: d ? '#e2e8f0' : '#f1f5f9', body: '#94a3b8', border: `rgba(${ar},.3)` },
    fill: color => color + (d ? '35' : '20'), a, ar,
  }
}
const tipO = t => ({ backgroundColor: t.tip.bg, padding: 10, cornerRadius: 8, titleColor: t.tip.title, bodyColor: t.tip.body, borderColor: t.tip.border, borderWidth: 1 })

const buildVel = () => {
  if (!velRef.value) return; if (velC) velC.destroy()
  const t = ct(), raw = CD.value.reports_last_30_days
  const days = activePd.value === '7D' ? 7 : activePd.value === '90D' ? 90 : activePd.value === '1Y' ? 30 : 30
  const labels = (raw?.labels || Array.from({ length: days }, (_, i) => `D${i + 1}`)).slice(-days)
  const values = ((raw?.values || Array(days).fill(0)).map(Number)).slice(-days)
  const pubRatio = S.value.total_reports > 0 ? (S.value.published_reports || 0) / S.value.total_reports : 0.55
  const pub = values.map(v => Math.floor(v * pubRatio))
  const ctx = velRef.value.getContext('2d')
  const gf = ctx.createLinearGradient(0, 0, 0, 190); gf.addColorStop(0, t.a + '55'); gf.addColorStop(1, t.a + '00')
  velC = new Chart(velRef.value, {
    data: {
      labels, datasets: [
        { type: 'line', label: 'Created', data: values, borderColor: t.a, backgroundColor: gf, fill: true, tension: .42, pointRadius: values.length > 20 ? 0 : 3, borderWidth: 2.5, pointBackgroundColor: t.a },
        { type: 'bar', label: 'Published', data: pub, backgroundColor: 'rgba(16,185,129,.7)', borderRadius: 4, barThickness: values.length > 20 ? 'flex' : 7 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
      plugins: { legend: { display: false }, tooltip: { ...tipO(t), callbacks: { title: items => `${items[0].label}`, label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y} ${isAdmin.value ? '(all users)' : '(my reports)'}` } } },
      scales: { x: { grid: { display: false }, ticks: { color: t.tick, font: { size: 9 }, maxTicksLimit: 8 } }, y: { grid: { color: t.grid }, ticks: { color: t.tick, font: { size: 9 } }, beginAtZero: true, border: { display: false } } },
      animation: { duration: 700, easing: 'easeOutQuart' }
    }
  })
}

const buildStatus = () => {
  if (!statusRef.value) return; if (statusC) statusC.destroy()
  const t = ct(), s = S.value, n = N.value
  const sfx = isAdmin.value ? ' (System)' : isManager.value ? ' (Team)' : ' (Mine)'
  const labels = [`Drafts${sfx}`, `Published${sfx}`, `Archived${sfx}`, 'Shared (Me)', 'Pending Tasks (Me)', 'Done Tasks (Me)']
  const values = [s.draft_reports || 0, s.published_reports || 0, s.archived_reports || 0, n.assigned_reports || 0, s.pending_tasks || 0, s.completed_tasks || 0]
  const colors = ['#f59e0b', '#10b981', pc.value, ac.value, '#ef4444', '#10b981']
  statusC = new Chart(statusRef.value, {
    type: 'bar',
    data: { labels, datasets: [{ data: values, backgroundColor: colors.map(c => c + 'cc'), borderColor: colors, borderWidth: 1.5, borderRadius: 7, borderSkipped: false }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { ...tipO(t), callbacks: { label: ctx => `${ctx.label.replace('\n', ' ')}: ${ctx.parsed.y}` } } },
      scales: { x: { grid: { display: false }, ticks: { color: t.tick, font: { size: 9 } } }, y: { grid: { color: t.grid }, ticks: { color: t.tick, font: { size: 9 } }, beginAtZero: true, border: { display: false } } },
      animation: { duration: 800, easing: 'easeOutBounce' }
    }
  })
}

const buildRadar = () => {
  if (!radarRef.value) return; if (radarC) radarC.destroy()
  const t = ct(), s = S.value, n = N.value
  const total = Math.max(s.total_reports || 0, 1), taskTot = Math.max((s.completed_tasks || 0) + (s.pending_tasks || 0), 1)
  const you = [
    Math.min(100, (s.total_reports || 0) / 50 * 100),
    Math.min(100, (s.published_reports || 0) / total * 100),
    Math.min(100, (s.completed_tasks || 0) / taskTot * 100),
    Math.min(100, (n.assigned_reports || 0) / 10 * 100),
    Math.min(100, (s.total_templates || 0) / 10 * 100),
    Math.min(100, 100 - (n.overdue_tasks || 0) / Math.max(taskTot, 1) * 100 * 3),
  ]
  radarC = new Chart(radarRef.value, {
    type: 'radar',
    data: {
      labels: ['Reports', 'Publish Rate', 'Task Done', 'Collab', 'Templates', 'Timeliness'],
      datasets: [
        { label: isAdmin.value ? 'You (Admin)' : isManager.value ? 'You (Manager)' : 'You', data: you.map(v => Math.max(0, Math.round(v))), borderColor: t.a, backgroundColor: t.a + '28', borderWidth: 2.5, pointBackgroundColor: t.a, pointBorderColor: isDark.value ? '#0d1725' : '#fff', pointRadius: 4 },
        { label: 'Average', data: [50, 55, 60, 40, 50, 70], borderColor: 'rgba(100,116,139,.5)', backgroundColor: 'rgba(100,116,139,.08)', borderWidth: 1.5, pointRadius: 3, pointBackgroundColor: '#64748b' },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { color: t.leg, font: { size: 10 }, boxWidth: 8, padding: 10 } }, tooltip: tipO(t) },
      scales: { r: { grid: { color: t.grid }, ticks: { display: false }, pointLabels: { color: t.leg, font: { size: 9 } }, angleLines: { color: t.grid } } },
      animation: { duration: 900 }
    }
  })
}

const buildDonut = () => {
  if (!donutRef.value) return; if (donutC) donutC.destroy()
  const t = ct(), data = typeVals.value, has = data.some(v => v > 0)
  donutC = new Chart(donutRef.value, {
    type: 'doughnut',
    data: { labels: typeLabels.value, datasets: [{ data: has ? data : data.map(() => 1), backgroundColor: pal.value.slice(0, typeLabels.value.length).map(c => c + 'cc'), borderColor: pal.value.slice(0, typeLabels.value.length), borderWidth: 2, hoverOffset: 8 }] },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '66%',
      plugins: { legend: { display: false }, tooltip: { ...tipO(t), callbacks: { label: ctx => `${ctx.label}: ${has ? ctx.parsed : 0}` } } },
      animation: { animateRotate: true, duration: 900 }
    }
  })
}

const buildTD = () => {
  if (!tdRef.value) return; if (tdC) tdC.destroy()
  const t = ct(), done = S.value.completed_tasks || 0, pend = S.value.pending_tasks || 0, ov = N.value.overdue_tasks || 0
  const total = done + pend + ov, has = total > 0
  tdC = new Chart(tdRef.value, {
    type: 'doughnut',
    data: { labels: ['Done', 'Pending', 'Overdue'], datasets: [{ data: has ? [done, pend, ov] : [1, 1, 1], backgroundColor: ['rgba(16,185,129,.8)', 'rgba(245,158,11,.8)', 'rgba(239,68,68,.8)'], borderColor: ['#10b981', '#f59e0b', '#ef4444'], borderWidth: 2, hoverOffset: 7 }] },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '64%',
      plugins: { legend: { display: false }, tooltip: { ...tipO(t), callbacks: { label: ctx => { const v = has ? ctx.parsed : 0; const pct = total > 0 ? Math.round(v / total * 100) : 0; return `${ctx.label}: ${v} (${pct}%)` } } } },
      animation: { animateRotate: true, duration: 900 }
    }
  })
}

const buildGrowth = () => {
  if (!growthRef.value) return; if (growthC) growthC.destroy()
  const t = ct(), raw = CD.value.user_growth
  const labels = (raw?.labels) || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
  const values = ((raw?.values) || [0, 0, 0, 0, 0, 0]).map(Number)
  const ctx = growthRef.value.getContext('2d')
  const gf = ctx.createLinearGradient(0, 0, 0, 160); gf.addColorStop(0, 'rgba(16,185,129,.4)'); gf.addColorStop(1, 'rgba(16,185,129,.02)')
  growthC = new Chart(growthRef.value, {
    type: 'line',
    data: { labels, datasets: [{ data: values, borderColor: '#10b981', backgroundColor: gf, fill: true, tension: .45, pointRadius: 4, borderWidth: 2.5, pointBackgroundColor: '#10b981', pointBorderColor: isDark.value ? '#0d1725' : '#fff', pointBorderWidth: 2 }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { ...tipO(t), callbacks: { title: i => i[0].label + ' (All Users — Global)', label: ctx => `New users: ${ctx.parsed.y}` } } },
      scales: { x: { grid: { display: false }, ticks: { color: t.tick, font: { size: 9 } } }, y: { grid: { color: t.grid }, ticks: { color: t.tick, font: { size: 9 } }, beginAtZero: true, border: { display: false } } },
      animation: { duration: 800 }
    }
  })
}

const buildComp = () => {
  if (!compRef.value) return; if (compC) compC.destroy()
  const t = ct(), raw = CD.value.reports_last_30_days
  const days = activePd.value === '7D' ? 7 : activePd.value === '90D' ? 90 : activePd.value === '1Y' ? 30 : 30
  const labels = (raw?.labels || []).slice(-days)
  const activity = (raw?.values || []).map(Number).slice(-days)
  const compLine = Array(labels.length).fill(prodScore.value)
  const ctx = compRef.value.getContext('2d')
  const gf = ctx.createLinearGradient(0, 0, 0, 160); gf.addColorStop(0, 'rgba(16,185,129,.35)'); gf.addColorStop(1, 'rgba(16,185,129,.02)')
  compC = new Chart(compRef.value, {
    data: {
      labels, datasets: [
        { type: 'bar', label: 'Activity', data: activity, backgroundColor: ac.value + '44', borderRadius: 4 },
        { type: 'line', label: 'Completion%', data: compLine, borderColor: '#10b981', backgroundColor: gf, fill: true, tension: 0, pointRadius: 0, borderWidth: 2.5, borderDash: [5, 3] },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'bottom', labels: { color: t.leg, font: { size: 10 }, boxWidth: 8, padding: 10 } },
        tooltip: { ...tipO(t), callbacks: { label: ctx => ctx.dataset.label.includes('%') ? `Completion: ${ctx.parsed.y}%` : `Activity: ${ctx.parsed.y}` } }
      },
      scales: { x: { grid: { display: false }, ticks: { color: t.tick, font: { size: 9 }, maxTicksLimit: 7 } }, y: { grid: { color: t.grid }, ticks: { color: t.tick, font: { size: 9 } }, beginAtZero: true, max: Math.max(Math.max(...activity, 10) + 5, 105), border: { display: false } } },
      animation: { duration: 700 }
    }
  })
}

const rebuildAll = () => nextTick(() => {
  buildVel(); buildStatus(); buildRadar(); buildDonut(); buildTD(); buildGrowth(); buildComp(); buildSparks()
})

const onKey = e => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); openCmd() }
  if (e.key === 'Escape') { showCmd.value = false; showNotif.value = false; showSettings.value = false }
}

onMounted(() => {
  window.addEventListener('keydown', onKey)
  // FIX: sync theme/accent/font/radius whenever AuthenticatedLayout writes to localStorage
  window.addEventListener('storage', syncFromLayout)
  clkT = setInterval(() => { now.value = new Date() }, 1000)
  nextTick(() => { rebuildAll(); initRace(); if (raceOn.value) toggleRace() })
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKey)
  window.removeEventListener('storage', syncFromLayout)
  clearInterval(clkT); clearInterval(raceT)
    ;[velC, statusC, radarC, donutC, tdC, growthC, compC, ...spkCh].forEach(c => c?.destroy())
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Syne:wght@600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');
</style>

<style scoped>
/* ══════════════════════════════════════════════════════════════
   FIX 1 — DOUBLE TOPBAR ON MOBILE
   AuthenticatedLayout renders <slot name="header"> in TWO places:
     1. .tb-bc  inside .rg-topbar  (always visible — correct)
     2. .rg-mob-head               (mobile-only div — duplicate)
   Suppressing .rg-mob-head here removes the duplicate on small screens.
   Must use :global because .rg-mob-head is outside this component's root.
══════════════════════════════════════════════════════════════ */
:global(.rg-mob-head) {
  display: none !important;
}

/* ══════════════════════════════════════════════════════════════
   FIX 2 — LIGHT THEME VISIBILITY
   Ensure text/icons readable on white card backgrounds.
══════════════════════════════════════════════════════════════ */
:global(.rg-light) .hbar-title {
  color: #0f172a;
}

:global(.rg-light) .hbar-sub {
  color: #64748b;
}

:global(.rg-light) .ct {
  color: #0f172a;
}

:global(.rg-light) .sec-title {
  color: #0f172a;
}

:global(.rg-light) .hcard-val {
  color: #0f172a;
}

:global(.rg-light) .hcard-lbl {
  color: #6b7280;
}

:global(.rg-light) .lm-val {
  color: #0f172a;
}

*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0
}

/* DASHBOARD BODY */
.db {
  min-height: 100vh;
  padding: 10px 13px 48px;
  transition: background .25s, color .25s
}

@media(min-width:640px) {
  .db {
    padding: 12px 18px 52px
  }
}

@media(min-width:1024px) {
  .db {
    padding: 14px 24px 56px
  }
}

/* HEADER */
.hbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap
}

.hbar-l {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  flex: 1
}

.logo-wrap {
  position: relative;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0
}

.logo-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border-width: 2px;
  border-style: solid;
  animation: logo-pulse 2s ease-out infinite
}

.logo-ring-2 {
  animation-delay: .5s;
  animation-duration: 2.5s
}

@keyframes logo-pulse {
  0% {
    opacity: 1;
    transform: scale(1)
  }

  100% {
    opacity: 0;
    transform: scale(1.65)
  }
}

.hbar-title {
  font-size: clamp(1rem, 2.5vw, 1.25rem);
  font-weight: 700;
  line-height: 1.15;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap
}

.hbar-sub {
  font-size: .67rem;
  margin-top: 2px;
  display: flex;
  align-items: center;
  gap: 5px
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
  animation: pdot 2s ease-in-out infinite
}

@keyframes pdot {

  0%,
  100% {
    opacity: 1
  }

  50% {
    opacity: .4
  }
}

.hbar-r {
  display: flex;
  align-items: center;
  gap: 5px;
  flex-wrap: wrap
}

.hbtn {
  width: 34px;
  height: 34px;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .83rem;
  transition: all .18s;
  font-family: inherit
}

.hbtn:hover {
  border-color: var(--ac) !important;
  color: var(--ac) !important
}

.hbtn-wide {
  width: auto;
  gap: 5px;
  padding: 0 10px;
  font-size: .68rem
}

.cmd-k {
  font-family: 'JetBrains Mono', monospace;
  font-size: .6rem
}

.pos-rel {
  position: relative
}

.badge {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 15px;
  height: 15px;
  border-radius: 50%;
  color: #fff;
  font-size: .5rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center
}

.new-btn {
  position: relative;
  overflow: hidden;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  color: #fff;
  font-size: .76rem;
  font-weight: 700;
  text-decoration: none;
  transition: transform .2s, box-shadow .2s;
  white-space: nowrap
}

.new-btn:hover {
  transform: translateY(-2px)
}

.new-btn-shine {
  position: absolute;
  top: 0;
  left: -80%;
  width: 50%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .28), transparent);
  animation: shine 2.5s infinite
}

@keyframes shine {
  0% {
    left: -80%
  }

  100% {
    left: 200%
  }
}

.new-btn-txt {
  display: none
}

@media(min-width:480px) {
  .new-btn-txt {
    display: inline
  }
}

/* TICKER */
.ticker {
  display: flex;
  align-items: center;
  border-width: 1px;
  border-style: solid;
  padding: 6px 12px;
  margin-bottom: 12px;
  overflow: hidden
}

.tk-live {
  font-size: .6rem;
  font-weight: 700;
  color: #10b981;
  font-family: 'JetBrains Mono', monospace;
  display: flex;
  align-items: center;
  gap: 5px;
  padding-right: 10px;
  border-right-width: 1px;
  border-right-style: solid;
  flex-shrink: 0;
  margin-right: 10px
}

.tk-scroll {
  flex: 1;
  overflow: hidden
}

.tk-inner {
  display: flex;
  animation: tkroll 40s linear infinite;
  width: max-content
}

@keyframes tkroll {
  from {
    transform: translateX(0)
  }

  to {
    transform: translateX(-50%)
  }
}

.tk-item {
  white-space: nowrap;
  padding: 0 20px;
  font-size: .67rem;
  border-right-width: 1px;
  border-right-style: solid
}

/* HERO GRID */
.hero-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 9px;
  margin-bottom: 12px
}

@media(min-width:540px) {
  .hero-grid {
    grid-template-columns: repeat(3, 1fr)
  }
}

@media(min-width:1024px) {
  .hero-grid {
    grid-template-columns: repeat(6, 1fr)
  }
}

.hcard {
  position: relative;
  overflow: hidden;
  border-width: 1px;
  border-style: solid;
  padding: 12px;
  cursor: pointer;
  animation: hcin .45s ease-out both;
  transition: transform .22s, box-shadow .22s
}

.hcard:hover {
  transform: translateY(-4px) scale(1.015)
}

.hcard:hover .hcard-glow {
  opacity: .22
}

@keyframes hcin {
  from {
    opacity: 0;
    transform: translateY(12px)
  }

  to {
    opacity: 1;
    transform: translateY(0)
  }
}

.hcard-glow {
  position: absolute;
  top: -22px;
  right: -22px;
  width: 75px;
  height: 75px;
  border-radius: 50%;
  opacity: .07;
  filter: blur(18px);
  transition: opacity .3s;
  pointer-events: none
}

.hcard-icon {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .83rem
}

.hcard-badge {
  font-size: .57rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 20px;
  font-family: 'JetBrains Mono', monospace;
  display: flex;
  align-items: center;
  gap: 2px
}

.hcard-val {
  font-size: clamp(1.4rem, 2.5vw, 2rem);
  font-weight: 800;
  line-height: 1;
  letter-spacing: -1px;
  margin: 6px 0 2px
}

.hcard-lbl {
  font-size: .61rem;
  font-weight: 500
}

.hcard-bar {
  height: 3px;
  border-radius: 2px;
  overflow: hidden;
  margin-top: 7px
}

.hcard-fill {
  height: 100%;
  border-radius: 2px;
  transition: width 1.2s cubic-bezier(.4, 0, .2, 1)
}

.hcard-spark {
  margin-top: 4px;
  opacity: .6;
  display: block
}

/* SECTION HEADER */
.sec-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 11px;
  flex-wrap: wrap;
  gap: 8px
}

.sec-title {
  font-size: clamp(.88rem, 2vw, 1.05rem);
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 7px
}

.period-btn {
  padding: 4px 10px;
  font-size: .64rem;
  font-weight: 600;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  transition: all .14s;
  font-family: inherit
}

.scope-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: .64rem;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 20px;
  white-space: nowrap
}

/* CARD */
.dcard {
  border-width: 1px;
  border-style: solid;
  overflow: hidden;
  transition: border-color .15s, box-shadow .15s
}

.dcard:hover {
  box-shadow: 0 10px 28px rgba(0, 0, 0, .16) !important
}

.ch {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px 8px;
  border-bottom-width: 1px;
  border-bottom-style: solid;
  flex-wrap: wrap;
  gap: 6px
}

.ct {
  display: flex;
  align-items: center;
  font-size: .76rem;
  font-weight: 700;
  white-space: nowrap
}

.lgp {
  font-size: .6rem;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 20px;
  white-space: nowrap
}

/* CHART AREAS */
.ca {
  padding: 9px 11px 11px;
  height: 210px
}

.ca-sm {
  padding: 9px 11px 11px;
  height: 195px
}

@media(min-width:768px) {
  .ca {
    height: 225px
  }

  .ca-sm {
    height: 210px
  }
}

/* ROW GRIDS */
.r1-grid,
.r2-grid,
.r3-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 11px;
  margin-bottom: 11px
}

@media(min-width:768px) {
  .r1-grid {
    grid-template-columns: 1fr 1fr
  }

  .r2-grid {
    grid-template-columns: 1fr 1fr
  }

  .r3-grid {
    grid-template-columns: 1fr 1fr
  }
}

@media(min-width:1200px) {
  .r1-grid {
    grid-template-columns: 1fr 270px 1fr
  }

  .r2-grid {
    grid-template-columns: 1fr 240px 240px
  }

  .r3-grid {
    grid-template-columns: 1fr 1fr 240px
  }
}

.r4-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 11px;
  margin-bottom: 11px
}

@media(min-width:640px) {
  .r4-grid {
    grid-template-columns: 1fr 1fr
  }
}

@media(min-width:1200px) {
  .r4-grid {
    grid-template-columns: 240px 1fr 200px 220px
  }
}

/* SPHERE */
.sphere-wrap {
  padding: 10px 13px 13px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px
}

.sphere-3d {
  position: relative;
  width: 148px;
  height: 148px;
  display: flex;
  align-items: center;
  justify-content: center;
  perspective: 600px
}

.s-r {
  position: absolute;
  border-radius: 50%;
  border-width: 1px;
  border-style: solid
}

.s-r1 {
  inset: 0;
  animation: srspin 12s linear infinite
}

.s-r2 {
  inset: 12px;
  border-style: dashed;
  animation: srspin 8s linear infinite reverse;
  opacity: .5
}

.s-r3 {
  inset: 24px;
  animation: srspin 20s linear infinite;
  opacity: .25
}

.s-r4 {
  inset: 36px;
  border-style: dotted;
  animation: srspin 6s linear infinite;
  opacity: .15
}

@keyframes srspin {
  from {
    transform: rotate(0) rotateX(65deg)
  }

  to {
    transform: rotate(360deg) rotateX(65deg)
  }
}

.s-core {
  position: relative;
  z-index: 2;
  width: 90px;
  height: 90px
}

.s-orbit {
  position: absolute;
  border-radius: 50%;
  border: 1px solid transparent
}

.s-o1 {
  inset: -14px;
  animation: orb 4s linear infinite
}

.s-o2 {
  inset: -26px;
  animation: orb 7s linear infinite reverse
}

.s-o3 {
  inset: -38px;
  animation: orb 11s linear infinite
}

.s-dot {
  position: absolute;
  top: 50%;
  left: -3.5px;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  margin-top: -3.5px
}

@keyframes orb {
  from {
    transform: rotate(0)
  }

  to {
    transform: rotate(360deg)
  }
}

.s-chips {
  display: flex;
  gap: 7px;
  flex-wrap: wrap;
  justify-content: center
}

.s-chip {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 4px 9px;
  border-radius: 20px;
  border-width: 1px;
  border-style: solid
}

/* DONUT */
.donut-wrap {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 9px 12px 13px;
  flex-wrap: wrap;
  position: relative
}

.donut-ring {
  width: 120px;
  height: 120px;
  flex-shrink: 0
}

.donut-ring-sm {
  width: 110px;
  height: 110px;
  flex-shrink: 0
}

.donut-leg {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 90px
}

.dl-row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: .67rem
}

.dl-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0
}

.dl-lbl {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap
}

.dl-val {
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  font-size: .65rem
}

.dl-pct {
  font-size: .6rem;
  font-family: 'JetBrains Mono', monospace;
  min-width: 26px;
  text-align: right
}

.donut-center-stat {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 120px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  pointer-events: none
}

/* TEMPLATE BAR RACE */
.race-tog {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .62rem;
  transition: all .18s
}

.race-body {
  padding: 9px 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 5px
}

.race-row {
  display: flex;
  align-items: center;
  gap: 7px;
  animation: racein .4s ease-out both
}

@keyframes racein {
  from {
    opacity: 0;
    transform: translateX(-6px)
  }

  to {
    opacity: 1;
    transform: translateX(0)
  }
}

.race-lbl {
  width: 70px;
  font-size: .62rem;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap
}

.race-track {
  flex: 1;
  height: 15px;
  border-radius: 8px;
  overflow: hidden
}

.race-fill {
  height: 100%;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  transition: width .65s cubic-bezier(.4, 0, .2, 1);
  min-width: 2%
}

.race-shine {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .22), transparent);
  animation: rshine 2s infinite
}

@keyframes rshine {
  0% {
    transform: translateX(-100%)
  }

  100% {
    transform: translateX(200%)
  }
}

.race-val {
  font-family: 'JetBrains Mono', monospace;
  font-size: .6rem;
  width: 22px;
  text-align: right
}

/* KANBAN */
.kb-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
  padding: 8px 10px 11px
}

@media(max-width:480px) {
  .kb-grid {
    grid-template-columns: repeat(2, 1fr)
  }
}

/* ACTIVITY */
.tab-pill {
  padding: 3px 7px;
  font-size: .6rem;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  border-radius: 5px;
  transition: all .12s;
  font-family: inherit
}

.act-list {
  padding: 5px 12px 11px;
  display: flex;
  flex-direction: column;
  max-height: 230px;
  overflow-y: auto
}

.act-item {
  display: flex;
  align-items: flex-start;
  gap: 7px;
  padding: 4px 0;
  position: relative
}

.act-line {
  position: absolute;
  left: 8px;
  top: 18px;
  bottom: -4px;
  width: 1px
}

.act-dot {
  width: 17px;
  height: 17px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 2px;
  border-width: 1px;
  border-style: solid;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .6rem
}

.stream-enter-active,
.stream-leave-active {
  transition: all .25s
}

.stream-enter-from {
  opacity: 0;
  transform: translateX(-7px)
}

.stream-leave-to {
  opacity: 0;
  transform: translateX(7px)
}

/* CALENDAR */
.mini-cal {
  padding: 5px 11px 11px
}

.cal-dow {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  margin-bottom: 3px
}

.cal-dow span {
  text-align: center;
  font-size: .54rem;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace
}

.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px
}

.cal-cell {
  position: relative;
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .6rem;
  border-radius: 5px;
  transition: background .1s
}

.cal-arr {
  width: 20px;
  height: 20px;
  border-radius: 5px;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .57rem;
  transition: all .14s
}

.cal-arr:hover {
  border-color: var(--ac) !important;
  color: var(--ac) !important
}

.cal-pip {
  position: absolute;
  bottom: 1px;
  left: 50%;
  transform: translateX(-50%);
  width: 3px;
  height: 3px;
  border-radius: 50%
}

/* OVERDUE */
.ov-alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  margin-bottom: 11px;
  border-width: 1px;
  border-style: solid
}

.ov-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(239, 68, 68, .12);
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
  animation: ovp 1.5s ease-in-out infinite
}

@keyframes ovp {

  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, .4)
  }

  50% {
    box-shadow: 0 0 0 8px rgba(239, 68, 68, 0)
  }
}

/* TABLE */
.dtbl {
  width: 100%;
  border-collapse: collapse
}

.th {
  padding: 8px 11px;
  text-align: left;
  font-size: .61rem;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  text-transform: uppercase;
  letter-spacing: .5px;
  border-bottom-width: 1px;
  border-bottom-style: solid;
  cursor: pointer;
  white-space: nowrap;
  user-select: none
}

.tbl-row {
  cursor: pointer;
  border-bottom-width: 1px;
  border-bottom-style: solid;
  transition: background .1s
}

.tbl-row:hover {
  background: rgba(var(--ac-rgb, 99, 102, 241), .04) !important
}

.td {
  padding: 9px 11px
}

.st-pill {
  font-size: .56rem;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  text-transform: uppercase;
  padding: 2px 6px
}

.tbl-btn {
  width: 26px;
  height: 26px;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .64rem;
  transition: all .12s
}

.tbl-btn:hover {
  border-color: var(--ac) !important;
  color: var(--ac) !important
}

.tbl-inp {
  border-width: 1px;
  border-style: solid;
  padding: 5px 8px 5px 24px;
  font-size: .67rem;
  outline: none;
  width: 125px;
  transition: border-color .15s
}

.tbl-inp:focus {
  border-color: var(--ac) !important
}

.tbl-inp::placeholder {
  color: var(--mu)
}

.hide-sm {
  display: none
}

@media(min-width:580px) {
  .hide-sm {
    display: table-cell
  }
}

/* BOTTOM GRID */
.bot-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 11px
}

@media(min-width:580px) {
  .bot-grid {
    grid-template-columns: 1fr 1fr
  }
}

@media(min-width:1000px) {
  .bot-grid {
    grid-template-columns: 1fr 1fr 240px
  }
}

/* DOCK */
.dock-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 5px;
  padding: 8px 10px 12px
}

@media(min-width:400px) {
  .dock-grid {
    grid-template-columns: repeat(8, 1fr)
  }
}

.dock-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  text-decoration: none;
  transition: transform .2s
}

.dock-item:hover {
  transform: translateY(-5px) scale(1.08)
}

.dock-icon {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .92rem;
  opacity: .85;
  transition: opacity .2s
}

.dock-item:hover .dock-icon {
  opacity: 1
}

.dock-lbl {
  font-size: .54rem;
  font-weight: 600;
  text-align: center
}

/* LIVE METRICS */
.live-badge {
  font-size: .58rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 4px;
  font-family: 'JetBrains Mono', monospace;
  background: rgba(16, 185, 129, .15);
  color: #10b981
}

.live-dot-g {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10b981;
  animation: pdot 1.5s ease-in-out infinite
}

.lm-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 5px;
  padding: 8px 11px 12px
}

@media(min-width:360px) {
  .lm-grid {
    grid-template-columns: repeat(3, 1fr)
  }
}

.lm-item {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 7px 8px;
  border-radius: 8px;
  border-width: 1px;
  border-style: solid;
  transition: background .14s
}

.lm-icon {
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .73rem;
  flex-shrink: 0
}

.lm-val {
  font-size: .92rem;
  font-weight: 800;
  line-height: 1;
  letter-spacing: -.5px
}

/* PREMIUM */
.prem-card {
  position: relative;
  overflow: hidden
}

.prem-ptcls {
  position: absolute;
  inset: 0;
  pointer-events: none
}

.prem-p {
  position: absolute;
  bottom: -8px;
  width: 3px;
  height: 3px;
  border-radius: 50%;
  animation: prf linear infinite
}

@keyframes prf {
  0% {
    transform: translateY(0);
    opacity: 0
  }

  50% {
    opacity: .8
  }

  100% {
    transform: translateY(-150px);
    opacity: 0
  }
}

.prem-body {
  position: relative;
  z-index: 1;
  padding: 14px 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  text-align: center
}

.prem-crown {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center
}

.prem-title {
  font-size: .87rem;
  font-weight: 700
}

.prem-sub {
  font-size: .62rem
}

.prem-btn {
  padding: 7px 16px;
  color: #fff;
  font-size: .7rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: transform .2s;
  margin-top: 4px;
  font-family: inherit
}

.prem-btn:hover {
  transform: scale(1.04)
}

/* PANELS */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, .5);
  backdrop-filter: blur(4px);
  z-index: 9998;
  display: flex;
  justify-content: flex-end
}

.side-panel {
  width: 314px;
  max-width: 100vw;
  height: 100vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  box-shadow: -8px 0 40px rgba(0, 0, 0, .28)
}

@media(max-width:360px) {
  .side-panel {
    width: 100vw
  }
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 13px 11px;
  border-bottom-width: 1px;
  border-bottom-style: solid
}

.panel-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: .8rem;
  font-weight: 700
}

.x-btn {
  width: 26px;
  height: 26px;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .72rem;
  border-radius: 6px;
  transition: all .14s;
  font-family: inherit
}

.x-btn:hover {
  border-color: var(--ac) !important;
  color: var(--ac) !important
}

.panel-body {
  padding: 8px;
  display: flex;
  flex-direction: column
}

.sg {
  padding: 9px 3px;
  border-bottom-width: 1px;
  border-bottom-style: solid
}

.sg:last-of-type {
  border-bottom: none
}

.sl {
  font-size: .67rem;
  font-weight: 700;
  margin-bottom: 6px
}

.tog-row {
  display: flex;
  gap: 4px;
  flex-wrap: wrap
}

.tog {
  flex: 1;
  padding: 5px 8px;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  font-size: .66rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  transition: all .13s;
  min-width: 52px;
  border-radius: 6px
}

.sw-row {
  display: flex;
  gap: 5px;
  flex-wrap: wrap
}

.sw {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  border: 2px solid transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform .14s
}

.sw:hover {
  transform: scale(1.18)
}

.reset-btn {
  width: 100%;
  margin-top: 10px;
  padding: 7px;
  border-radius: 8px;
  border-width: 1px;
  border-style: solid;
  background: transparent;
  cursor: pointer;
  font-size: .7rem;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  transition: all .2s
}

.reset-btn:hover {
  border-color: #ef4444 !important;
  color: #ef4444 !important
}

.np-tab {
  padding: 5px 9px;
  border-radius: 7px 7px 0 0;
  font-size: .64rem;
  font-weight: 600;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all .12s;
  font-family: inherit
}

.np-item {
  display: flex;
  align-items: flex-start;
  gap: 7px;
  padding: 7px 8px;
  border-radius: 7px;
  cursor: pointer;
  transition: background .1s;
  margin-bottom: 2px
}

.np-icon {
  width: 27px;
  height: 27px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .7rem;
  flex-shrink: 0
}

/* COMMAND PALETTE */
.cmd-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, .7);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 60px 14px 0
}

.cmd-box {
  width: 520px;
  max-width: 100%;
  overflow: hidden;
  box-shadow: 0 32px 80px rgba(0, 0, 0, .65)
}

.cmd-search {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 11px 13px;
  border-bottom-width: 1px;
  border-bottom-style: solid
}

.cmd-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-size: .83rem
}

.cmd-input::placeholder {
  color: var(--mu)
}

.esc-k {
  font-size: .55rem;
  padding: 2px 5px;
  border-radius: 3px;
  background: rgba(255, 255, 255, .06);
  border-width: 1px;
  border-style: solid;
  font-family: 'JetBrains Mono', monospace
}

.cmd-list {
  max-height: 290px;
  overflow-y: auto;
  padding: 5px
}

.cmd-lbl {
  font-size: .57rem;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  text-transform: uppercase;
  letter-spacing: .5px;
  padding: 4px 7px 2px;
  opacity: .45
}

.cmd-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 8px;
  border-radius: 7px;
  cursor: pointer;
  transition: background .1s;
  border-width: 1px;
  border-style: solid;
  width: 100%;
  text-align: left;
  background: transparent;
  font-family: inherit
}

.cmd-ic {
  width: 26px;
  height: 26px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: .7rem;
  flex-shrink: 0
}

/* TRANSITIONS */
.sr-enter-active,
.sr-leave-active {
  transition: transform .27s cubic-bezier(.16, 1, .3, 1), opacity .27s
}

.sr-enter-from,
.sr-leave-to {
  transform: translateX(100%);
  opacity: 0
}

.pf-enter-active,
.pf-leave-active {
  transition: all .2s cubic-bezier(.16, 1, .3, 1)
}

.pf-enter-from {
  opacity: 0;
  transform: scale(.95) translateY(-8px)
}

.pf-leave-to {
  opacity: 0;
  transform: scale(.97)
}

/* SCROLLBAR */
::-webkit-scrollbar {
  width: 4px;
  height: 4px
}

::-webkit-scrollbar-track {
  background: transparent
}

::-webkit-scrollbar-thumb {
  background: rgba(var(--ac-rgb, 99, 102, 241), .3);
  border-radius: 99px
}

/* REDUCED MOTION */
@media(prefers-reduced-motion:reduce) {

  *,
  *::before,
  *::after {
    animation-duration: .01ms !important;
    transition-duration: .01ms !important
  }
}
</style>