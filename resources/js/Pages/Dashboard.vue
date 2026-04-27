<template>
  <div class="dashboard-shell" :class="{ 'sidebar-collapsed': sidebarCollapsed, 'dark': isDark }">

    <!-- ══════════════════════════════════════════════════════════
         ANIMATED BACKGROUND MESH
    ══════════════════════════════════════════════════════════ -->
    <div class="bg-mesh" aria-hidden="true">
      <div class="mesh-blob mesh-blob-1"></div>
      <div class="mesh-blob mesh-blob-2"></div>
      <div class="mesh-blob mesh-blob-3"></div>
      <div class="mesh-grid"></div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         LEFT SIDEBAR
    ══════════════════════════════════════════════════════════ -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">

      <!-- Logo -->
      <div class="sidebar-logo">
        <div class="logo-mark">
          <i class="fa-solid fa-chart-line"></i>
        </div>
        <transition name="sidebar-text">
          <div v-if="!sidebarCollapsed" class="logo-text">
            <span class="logo-name">ReportGen</span>
            <span class="logo-badge">GBRSP</span>
          </div>
        </transition>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed" :title="sidebarCollapsed ? 'Expand' : 'Collapse'">
          <i class="fa-solid" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
        </button>
      </div>

      <!-- User info -->
      <div class="sidebar-user" v-if="!sidebarCollapsed">
        <div class="user-avatar">{{ userInitial }}</div>
        <div class="user-info">
          <div class="user-name">{{ $page?.props?.auth?.user?.name || 'User' }}</div>
          <div class="user-role">Report Designer</div>
        </div>
        <div class="user-status"></div>
      </div>
      <div class="sidebar-user collapsed-user" v-else>
        <div class="user-avatar sm">{{ userInitial }}</div>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <div class="nav-section-label" v-if="!sidebarCollapsed">WORKSPACE</div>
        <button
          v-for="item in NAV_ITEMS"
          :key="item.id"
          @click="activeView = item.id"
          class="nav-item"
          :class="{ active: activeView === item.id }"
          :title="sidebarCollapsed ? item.label : ''"
        >
          <div class="nav-icon">
            <i :class="item.icon"></i>
            <span v-if="item.badge && !sidebarCollapsed" class="nav-badge">{{ item.badge }}</span>
          </div>
          <transition name="sidebar-text">
            <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
          </transition>
          <transition name="sidebar-text">
            <span v-if="item.badge && sidebarCollapsed" class="nav-badge-dot"></span>
          </transition>
        </button>

        <div class="nav-divider"></div>
        <div class="nav-section-label" v-if="!sidebarCollapsed">REPORTS</div>

        <button
          v-for="item in REPORT_NAV"
          :key="item.id"
          @click="activeView = item.id"
          class="nav-item"
          :class="{ active: activeView === item.id }"
          :title="sidebarCollapsed ? item.label : ''"
        >
          <div class="nav-icon">
            <i :class="item.icon"></i>
          </div>
          <transition name="sidebar-text">
            <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
          </transition>
        </button>

        <div class="nav-divider"></div>
        <div class="nav-section-label" v-if="!sidebarCollapsed">ACCOUNT</div>

        <button
          v-for="item in ACCOUNT_NAV"
          :key="item.id"
          @click="activeView = item.id"
          class="nav-item"
          :class="{ active: activeView === item.id }"
          :title="sidebarCollapsed ? item.label : ''"
        >
          <div class="nav-icon">
            <i :class="item.icon"></i>
          </div>
          <transition name="sidebar-text">
            <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
          </transition>
        </button>
      </nav>

      <!-- Sidebar Footer -->
      <div class="sidebar-footer">
        <button @click="toggleDark" class="theme-toggle" :title="isDark ? 'Light Mode' : 'Dark Mode'">
          <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
          <span v-if="!sidebarCollapsed">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
        </button>
        <Link :href="route('logout')" method="post" as="button" class="logout-btn" :title="sidebarCollapsed ? 'Sign Out' : ''">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          <span v-if="!sidebarCollapsed">Sign Out</span>
        </Link>
      </div>
    </aside>

    <!-- ══════════════════════════════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════════════════════════════ -->
    <main class="main-content">

      <!-- TOP BAR -->
      <header class="topbar">
        <div class="topbar-left">
          <div class="page-breadcrumb">
            <span class="breadcrumb-root">Dashboard</span>
            <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
            <span class="breadcrumb-current">{{ currentViewLabel }}</span>
          </div>
          <div class="page-title-row">
            <h1 class="page-title">{{ currentViewLabel }}</h1>
            <div class="live-indicator">
              <span class="live-dot"></span>
              <span>Live</span>
            </div>
          </div>
        </div>
        <div class="topbar-right">
          <!-- Global Search -->
          <div class="global-search" :class="{ focused: searchFocused }">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input
              v-model="globalSearch"
              placeholder="Search reports, templates…"
              @focus="searchFocused = true"
              @blur="searchFocused = false"
              class="search-input"
            />
            <kbd class="search-kbd">⌘K</kbd>
          </div>
          <!-- Notifications -->
          <div class="notif-btn-wrap" ref="notifRef">
            <button class="icon-btn notif-btn" @click="showNotifs = !showNotifs" :class="{ active: showNotifs }">
              <i class="fa-solid fa-bell"></i>
              <span class="notif-count">{{ notifications.filter(n => !n.read).length }}</span>
            </button>
            <transition name="dropdown">
              <div v-if="showNotifs" class="notif-panel">
                <div class="notif-header">
                  <span>Notifications</span>
                  <button @click="markAllRead" class="mark-read-btn">Mark all read</button>
                </div>
                <div class="notif-list">
                  <div v-for="n in notifications" :key="n.id" class="notif-item" :class="{ unread: !n.read }" @click="n.read = true">
                    <div class="notif-icon" :class="n.type">
                      <i :class="n.icon"></i>
                    </div>
                    <div class="notif-body">
                      <p class="notif-msg">{{ n.msg }}</p>
                      <p class="notif-time">{{ n.time }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </transition>
          </div>
          <!-- New Report -->
          <Link :href="route('reports.create')" class="new-report-btn">
            <i class="fa-solid fa-plus"></i>
            <span>New Report</span>
          </Link>
        </div>
      </header>

      <!-- ─── VIEW CONTENT ─────────────────────────────────────── -->
      <div class="view-content">

        <!-- ══ OVERVIEW ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'overview'" key="overview" class="view-panel">

          <!-- Welcome Banner -->
          <div class="welcome-banner" :style="{ animationDelay: '0ms' }">
            <div class="welcome-left">
              <div class="welcome-greeting">
                <span class="greeting-emoji">{{ timeEmoji }}</span>
                <div>
                  <div class="greeting-text">Good {{ timeOfDay }}, {{ firstName }}!</div>
                  <div class="greeting-sub">{{ todayFormatted }} · {{ currentTime }}</div>
                </div>
              </div>
              <div class="welcome-stats-row">
                <div class="ws-item">
                  <span class="ws-val">{{ stats?.total_reports || 0 }}</span>
                  <span class="ws-label">Total Reports</span>
                </div>
                <div class="ws-divider"></div>
                <div class="ws-item">
                  <span class="ws-val text-emerald-400">{{ stats?.published_reports || 0 }}</span>
                  <span class="ws-label">Published</span>
                </div>
                <div class="ws-divider"></div>
                <div class="ws-item">
                  <span class="ws-val text-amber-400">{{ stats?.draft_reports || 0 }}</span>
                  <span class="ws-label">Drafts</span>
                </div>
              </div>
            </div>
            <div class="welcome-right">
              <div class="welcome-quote">
                <i class="fa-solid fa-quote-left"></i>
                <p>"Data is the new oil. Refined reports are the engine."</p>
              </div>
              <Link :href="route('reports.create')" class="banner-cta">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Create Report
              </Link>
            </div>
          </div>

          <!-- KPI Cards -->
          <div class="kpi-grid">
            <div v-for="(card, i) in kpiCards" :key="card.key" class="kpi-card" :style="{ animationDelay: (i * 80) + 'ms' }">
              <div class="kpi-header">
                <div class="kpi-icon" :class="card.iconClass">
                  <i :class="card.icon"></i>
                </div>
                <div class="kpi-trend" :class="card.trendUp ? 'up' : 'down'">
                  <i :class="card.trendUp ? 'fa-solid fa-arrow-trend-up' : 'fa-solid fa-arrow-trend-down'"></i>
                  {{ card.trend }}
                </div>
              </div>
              <div class="kpi-value">{{ stats?.[card.key] ?? card.fallback }}</div>
              <div class="kpi-label">{{ card.label }}</div>
              <div class="kpi-bar">
                <div class="kpi-bar-fill" :class="card.barClass" :style="{ width: card.progress }"></div>
              </div>
              <div class="kpi-sub">{{ card.sub }}</div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="charts-row">
            <!-- Activity Chart -->
            <div class="chart-card wide">
              <div class="chart-card-header">
                <div class="chart-title-wrap">
                  <div class="chart-icon indigo">
                    <i class="fa-solid fa-chart-column"></i>
                  </div>
                  <div>
                    <div class="chart-title">Report Activity</div>
                    <div class="chart-sub">Creation & publication over time</div>
                  </div>
                </div>
                <div class="period-tabs">
                  <button v-for="p in ['7d','30d','90d']" :key="p" @click="activityPeriod = p; buildCharts()" class="period-tab" :class="{ active: activityPeriod === p }">{{ p }}</button>
                </div>
              </div>
              <div class="chart-area">
                <canvas id="activityChart" ref="activityRef"></canvas>
              </div>
            </div>

            <!-- Donut -->
            <div class="chart-card">
              <div class="chart-card-header">
                <div class="chart-title-wrap">
                  <div class="chart-icon violet">
                    <i class="fa-solid fa-chart-pie"></i>
                  </div>
                  <div>
                    <div class="chart-title">Status Breakdown</div>
                    <div class="chart-sub">Report distribution</div>
                  </div>
                </div>
              </div>
              <div class="chart-area donut-area">
                <canvas id="donutChart" ref="donutRef"></canvas>
                <div class="donut-center">
                  <div class="donut-total">{{ stats?.total_reports || 0 }}</div>
                  <div class="donut-label">Total</div>
                </div>
              </div>
              <div class="donut-legend">
                <div class="legend-item" v-for="l in donutLegend" :key="l.label">
                  <span class="legend-dot" :style="{ background: l.color }"></span>
                  <span class="legend-label">{{ l.label }}</span>
                  <span class="legend-val">{{ l.val }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Second Charts Row -->
          <div class="charts-row">
            <!-- Radar Chart -->
            <div class="chart-card">
              <div class="chart-card-header">
                <div class="chart-title-wrap">
                  <div class="chart-icon emerald">
                    <i class="fa-solid fa-circle-nodes"></i>
                  </div>
                  <div>
                    <div class="chart-title">Productivity Score</div>
                    <div class="chart-sub">Performance across dimensions</div>
                  </div>
                </div>
              </div>
              <div class="chart-area">
                <canvas id="radarChart" ref="radarRef"></canvas>
              </div>
            </div>

            <!-- Line trend -->
            <div class="chart-card wide">
              <div class="chart-card-header">
                <div class="chart-title-wrap">
                  <div class="chart-icon amber">
                    <i class="fa-solid fa-chart-line"></i>
                  </div>
                  <div>
                    <div class="chart-title">Publication Trend</div>
                    <div class="chart-sub">Reports published monthly</div>
                  </div>
                </div>
                <div class="chart-badge emerald">+{{ publishRate }}% rate</div>
              </div>
              <div class="chart-area">
                <canvas id="lineChart" ref="lineRef"></canvas>
              </div>
            </div>
          </div>

          <!-- Recent Reports + Quick Actions Row -->
          <div class="bottom-row">
            <!-- Recent Reports Table -->
            <div class="reports-table-card">
              <div class="card-header">
                <div class="chart-title-wrap">
                  <div class="chart-icon indigo"><i class="fa-solid fa-file-lines"></i></div>
                  <div>
                    <div class="chart-title">Recent Reports</div>
                    <div class="chart-sub">Latest activity</div>
                  </div>
                </div>
                <Link :href="route('reports.index')" class="view-all-link">View All <i class="fa-solid fa-arrow-right"></i></Link>
              </div>
              <div class="reports-table">
                <div class="reports-table-head">
                  <span>Report</span>
                  <span>Status</span>
                  <span>Modified</span>
                  <span>Actions</span>
                </div>
                <div v-for="(r, i) in recentReports.slice(0,6)" :key="r.id" class="reports-table-row" :style="{ animationDelay: (i * 50) + 'ms' }">
                  <div class="report-title-cell">
                    <div class="report-file-icon" :class="r.status">
                      <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                      <div class="report-name">{{ r.title }}</div>
                      <div class="report-meta">{{ (r.content || []).length || 1 }} page{{ ((r.content || []).length || 1) !== 1 ? 's' : '' }}</div>
                    </div>
                  </div>
                  <div>
                    <span class="status-pill" :class="r.status">{{ r.status }}</span>
                  </div>
                  <div class="report-date">{{ formatDate(r.updated_at) }}</div>
                  <div class="report-actions">
                    <Link :href="route('reports.edit', r.slug)" class="row-btn" title="Edit"><i class="fa-solid fa-pen-to-square"></i></Link>
                    <Link :href="route('reports.preview', r.slug)" target="_blank" class="row-btn" title="Preview"><i class="fa-solid fa-eye"></i></Link>
                    <a :href="route('reports.download', r.slug)" class="row-btn" title="Download"><i class="fa-solid fa-download"></i></a>
                    <button @click="confirmDelete(r)" class="row-btn danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </div>
                <div v-if="!recentReports.length" class="empty-table">
                  <i class="fa-solid fa-inbox"></i>
                  <span>No reports yet. <Link :href="route('reports.create')" class="text-indigo-400">Create one →</Link></span>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="right-col">
              <!-- Publish Rate -->
              <div class="publish-rate-card">
                <div class="pr-header">
                  <span class="pr-title">Publish Rate</span>
                  <span class="pr-value" :class="publishRate >= 50 ? 'text-emerald-400' : 'text-amber-400'">{{ publishRate }}%</span>
                </div>
                <div class="pr-track">
                  <div class="pr-fill" :style="{ width: publishRate + '%' }"></div>
                </div>
                <div class="pr-sub">{{ stats?.published_reports || 0 }} of {{ stats?.total_reports || 0 }} published</div>
                <!-- Mini sparkline canvas -->
                <canvas id="sparklineChart" style="height:48px;margin-top:12px"></canvas>
              </div>

              <!-- Quick Actions -->
              <div class="quick-actions-card">
                <div class="qa-title">Quick Actions</div>
                <div class="qa-list">
                  <Link v-for="qa in QUICK_ACTIONS" :key="qa.label" :href="route(qa.route)" class="qa-item">
                    <div class="qa-icon" :class="qa.color"><i :class="qa.icon"></i></div>
                    <div class="qa-text">
                      <div class="qa-label">{{ qa.label }}</div>
                      <div class="qa-desc">{{ qa.desc }}</div>
                    </div>
                    <i class="fa-solid fa-chevron-right qa-arrow"></i>
                  </Link>
                </div>
              </div>
            </div>
          </div>

        </div>
        </transition>

        <!-- ══ ANALYTICS ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'analytics'" key="analytics" class="view-panel">
          <div class="analytics-header">
            <div class="analytics-filters">
              <select v-model="analyticsPeriod" class="filter-select">
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last year</option>
              </select>
              <button class="filter-btn"><i class="fa-solid fa-calendar"></i> Custom Range</button>
              <button class="filter-btn" @click="exportAnalytics"><i class="fa-solid fa-file-export"></i> Export</button>
            </div>
          </div>

          <!-- Analytics KPI Row -->
          <div class="analytics-kpi-row">
            <div v-for="m in analyticsMetrics" :key="m.label" class="analytics-kpi">
              <div class="akpi-icon" :class="m.color"><i :class="m.icon"></i></div>
              <div class="akpi-val">{{ m.value }}</div>
              <div class="akpi-label">{{ m.label }}</div>
              <div class="akpi-change" :class="m.up ? 'up' : 'down'">
                <i :class="m.up ? 'fa-solid fa-caret-up' : 'fa-solid fa-caret-down'"></i>
                {{ m.change }}
              </div>
            </div>
          </div>

          <!-- Full Analytics Charts -->
          <div class="analytics-charts">
            <div class="chart-card full">
              <div class="chart-card-header">
                <div class="chart-title-wrap">
                  <div class="chart-icon indigo"><i class="fa-solid fa-chart-area"></i></div>
                  <div><div class="chart-title">Report Creation Over Time</div><div class="chart-sub">Cumulative growth</div></div>
                </div>
              </div>
              <div class="chart-area tall">
                <canvas id="analyticsAreaChart" ref="analyticsAreaRef"></canvas>
              </div>
            </div>

            <div class="charts-row mt-4">
              <div class="chart-card">
                <div class="chart-card-header">
                  <div class="chart-title-wrap">
                    <div class="chart-icon violet"><i class="fa-solid fa-chart-bar"></i></div>
                    <div><div class="chart-title">Reports by Day</div><div class="chart-sub">Weekly pattern</div></div>
                  </div>
                </div>
                <div class="chart-area">
                  <canvas id="weekdayChart" ref="weekdayRef"></canvas>
                </div>
              </div>
              <div class="chart-card">
                <div class="chart-card-header">
                  <div class="chart-title-wrap">
                    <div class="chart-icon emerald"><i class="fa-solid fa-chart-pie"></i></div>
                    <div><div class="chart-title">Template Usage</div><div class="chart-sub">Which templates are popular</div></div>
                  </div>
                </div>
                <div class="chart-area donut-area">
                  <canvas id="templateUsageChart" ref="templateUsageRef"></canvas>
                </div>
              </div>
            </div>

            <!-- Data Table -->
            <div class="chart-card full mt-4">
              <div class="chart-card-header">
                <div class="chart-title-wrap">
                  <div class="chart-icon amber"><i class="fa-solid fa-table"></i></div>
                  <div><div class="chart-title">Detailed Report Log</div><div class="chart-sub">All report activity</div></div>
                </div>
                <div class="flex gap-2">
                  <input v-model="globalSearch" placeholder="Search…" class="table-search" />
                  <select v-model="analyticsStatusFilter" class="filter-select sm">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                  </select>
                </div>
              </div>
              <div class="analytics-table">
                <div class="at-head">
                  <span>Title</span><span>Status</span><span>Pages</span><span>Created</span><span>Modified</span><span>Actions</span>
                </div>
                <div v-for="r in filteredAnalyticsReports" :key="r.id" class="at-row">
                  <div class="at-title">
                    <div class="report-file-icon sm" :class="r.status"><i class="fa-solid fa-file-lines"></i></div>
                    {{ r.title }}
                  </div>
                  <div><span class="status-pill" :class="r.status">{{ r.status }}</span></div>
                  <div class="text-center">{{ (r.content || []).length || 1 }}</div>
                  <div class="at-date">{{ formatDate(r.created_at) }}</div>
                  <div class="at-date">{{ formatDate(r.updated_at) }}</div>
                  <div class="at-actions">
                    <Link :href="route('reports.edit', r.slug)" class="row-btn" title="Edit"><i class="fa-solid fa-pen-to-square"></i></Link>
                    <a :href="route('reports.download', r.slug)" class="row-btn" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></a>
                    <button @click="changeStatus(r, r.status === 'published' ? 'draft' : 'published')" class="row-btn" :title="r.status === 'published' ? 'Unpublish' : 'Publish'">
                      <i :class="r.status === 'published' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-globe'"></i>
                    </button>
                    <button @click="duplicateReport(r)" class="row-btn" title="Duplicate"><i class="fa-regular fa-clone"></i></button>
                    <button @click="confirmDelete(r)" class="row-btn danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        </transition>

        <!-- ══ MY REPORTS ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'reports'" key="reports" class="view-panel">
          <!-- Toolbar -->
          <div class="reports-toolbar">
            <div class="toolbar-left">
              <div class="search-bar-sm">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input v-model="reportsSearch" placeholder="Search reports…" />
              </div>
              <div class="status-tabs">
                <button v-for="s in ['all','draft','published','archived']" :key="s" @click="reportsFilter = s" class="status-tab" :class="{ active: reportsFilter === s }">
                  {{ s }}
                  <span class="tab-count">{{ s === 'all' ? recentReports.length : recentReports.filter(r => r.status === s).length }}</span>
                </button>
              </div>
            </div>
            <div class="toolbar-right">
              <select v-model="reportsSortBy" class="filter-select sm">
                <option value="updated_at">Last Modified</option>
                <option value="created_at">Date Created</option>
                <option value="title">Title A–Z</option>
              </select>
              <div class="view-toggle">
                <button @click="reportsViewMode = 'grid'" class="vtb" :class="{ active: reportsViewMode === 'grid' }"><i class="fa-solid fa-grip"></i></button>
                <button @click="reportsViewMode = 'list'" class="vtb" :class="{ active: reportsViewMode === 'list' }"><i class="fa-solid fa-list"></i></button>
              </div>
              <Link :href="route('reports.create')" class="new-report-btn sm">
                <i class="fa-solid fa-plus"></i> New
              </Link>
            </div>
          </div>

          <!-- Grid View -->
          <div v-if="reportsViewMode === 'grid'" class="reports-grid">
            <Link :href="route('reports.create')" class="new-report-card">
              <div class="new-card-inner">
                <div class="new-card-icon"><i class="fa-solid fa-plus"></i></div>
                <div class="new-card-label">New Report</div>
                <div class="new-card-sub">Blank canvas or template</div>
              </div>
            </Link>
            <div v-for="r in filteredReports" :key="r.id" class="report-card">
              <div class="rc-thumb" :class="r.status">
                <div class="rc-thumb-bg">
                  <i class="fa-solid fa-file-lines"></i>
                </div>
                <div class="rc-overlay">
                  <Link :href="route('reports.edit', r.slug)" class="rc-action-btn">Edit</Link>
                  <Link :href="route('reports.preview', r.slug)" target="_blank" class="rc-action-btn primary">Preview</Link>
                </div>
                <div class="rc-status-badge" :class="r.status">{{ r.status }}</div>
              </div>
              <div class="rc-body">
                <div class="rc-title">{{ r.title }}</div>
                <div class="rc-meta">
                  <span>{{ formatDate(r.updated_at) }}</span>
                  <span v-if="r.template">· {{ r.template.name }}</span>
                </div>
                <div class="rc-actions">
                  <Link :href="route('reports.edit', r.slug)" class="row-btn" title="Edit"><i class="fa-solid fa-pen-to-square"></i></Link>
                  <Link :href="route('reports.preview', r.slug)" target="_blank" class="row-btn" title="Preview"><i class="fa-solid fa-eye"></i></Link>
                  <a :href="route('reports.download', r.slug)" class="row-btn" title="Download"><i class="fa-solid fa-download"></i></a>
                  <button @click="duplicateReport(r)" class="row-btn" title="Duplicate"><i class="fa-regular fa-clone"></i></button>
                  <button @click="openShareModal(r)" class="row-btn" title="Share"><i class="fa-solid fa-share-nodes"></i></button>
                  <button @click="confirmDelete(r)" class="row-btn danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>
            </div>
          </div>

          <!-- List View -->
          <div v-else class="reports-list-view">
            <div class="rlv-head">
              <span>Report</span><span>Status</span><span>Pages</span><span>Modified</span><span>Actions</span>
            </div>
            <div v-for="r in filteredReports" :key="r.id" class="rlv-row">
              <div class="rlv-title">
                <div class="report-file-icon" :class="r.status"><i class="fa-solid fa-file-lines"></i></div>
                <div>
                  <div class="report-name">{{ r.title }}</div>
                  <div class="report-meta">{{ r.template?.name || 'Custom' }}</div>
                </div>
              </div>
              <div><span class="status-pill" :class="r.status">{{ r.status }}</span></div>
              <div class="text-center" style="color: var(--t-muted)">{{ (r.content || []).length || 1 }}</div>
              <div class="report-date">{{ formatDate(r.updated_at) }}</div>
              <div class="report-actions">
                <Link :href="route('reports.edit', r.slug)" class="row-btn" title="Edit"><i class="fa-solid fa-pen-to-square"></i></Link>
                <Link :href="route('reports.preview', r.slug)" target="_blank" class="row-btn" title="Preview"><i class="fa-solid fa-eye"></i></Link>
                <a :href="route('reports.download', r.slug)" class="row-btn" title="Download"><i class="fa-solid fa-download"></i></a>
                <button @click="duplicateReport(r)" class="row-btn" title="Duplicate"><i class="fa-regular fa-clone"></i></button>
                <button @click="openShareModal(r)" class="row-btn" title="Share"><i class="fa-solid fa-share-nodes"></i></button>
                <div class="relative" :ref="el => statusMenuRefs[r.id] = el">
                  <button @click="openStatusDropdown === r.id ? openStatusDropdown = null : openStatusDropdown = r.id" class="row-btn" title="Change Status">
                    <i class="fa-solid fa-circle-dot"></i>
                  </button>
                  <transition name="dropdown">
                    <div v-if="openStatusDropdown === r.id" class="status-dropdown">
                      <button v-for="s in ['draft','published','archived']" :key="s" @click="changeStatus(r, s); openStatusDropdown = null" class="sd-item" :class="r.status === s ? 'active' : ''">
                        <span class="sd-dot" :class="s"></span> {{ s }}
                      </button>
                    </div>
                  </transition>
                </div>
                <button @click="confirmDelete(r)" class="row-btn danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </div>
            </div>
            <div v-if="!filteredReports.length" class="empty-list">
              <i class="fa-solid fa-folder-open"></i>
              <p>No reports found</p>
              <Link :href="route('reports.create')" class="new-report-btn sm">Create Report</Link>
            </div>
          </div>
        </div>
        </transition>

        <!-- ══ TEMPLATES ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'templates'" key="templates" class="view-panel">
          <div class="section-intro">
            <p class="section-desc">Choose a professional template to jumpstart your report</p>
          </div>
          <div class="templates-grid">
            <Link :href="route('templates.index')" class="see-all-card">
              <i class="fa-solid fa-grid-2 text-2xl mb-2"></i>
              <span>Browse All Templates</span>
              <i class="fa-solid fa-arrow-right"></i>
            </Link>
            <div v-for="(tpl, i) in templatePreviews" :key="tpl.slug" class="tpl-card" :style="{ animationDelay: (i * 60) + 'ms' }">
              <div class="tpl-thumb" :style="{ background: tpl.gradient }">
                <div class="tpl-thumb-inner" v-html="tpl.preview"></div>
                <div class="tpl-overlay">
                  <Link :href="route('templates.index')" class="tpl-use-btn">Use Template</Link>
                </div>
              </div>
              <div class="tpl-info">
                <div class="tpl-name">{{ tpl.name }}</div>
                <div class="tpl-desc">{{ tpl.desc }}</div>
              </div>
            </div>
          </div>
        </div>
        </transition>

        <!-- ══ PROFILE SETTINGS ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'profile'" key="profile" class="view-panel">
          <div class="settings-layout">
            <!-- Profile Info -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-user-circle settings-icon indigo"></i>
                <div>
                  <div class="settings-card-title">Profile Information</div>
                  <div class="settings-card-sub">Update your name and email address</div>
                </div>
              </div>
              <div class="settings-form">
                <div class="form-row">
                  <label class="form-label">Full Name</label>
                  <input v-model="profileForm.name" class="form-input" placeholder="Your full name" />
                </div>
                <div class="form-row">
                  <label class="form-label">Email Address</label>
                  <input v-model="profileForm.email" type="email" class="form-input" placeholder="your@email.com" />
                </div>
                <div class="form-row">
                  <label class="form-label">Job Title</label>
                  <input v-model="profileForm.jobTitle" class="form-input" placeholder="e.g. Data Analyst" />
                </div>
                <div class="form-row">
                  <label class="form-label">Organization</label>
                  <input v-model="profileForm.org" class="form-input" placeholder="Company or department" />
                </div>
                <button @click="saveProfile" class="save-btn">
                  <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
              </div>
            </div>

            <!-- Password -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-shield-halved settings-icon violet"></i>
                <div>
                  <div class="settings-card-title">Change Password</div>
                  <div class="settings-card-sub">Use a strong, unique password</div>
                </div>
              </div>
              <div class="settings-form">
                <div class="form-row">
                  <label class="form-label">Current Password</label>
                  <input v-model="passwordForm.current" type="password" class="form-input" placeholder="••••••••" />
                </div>
                <div class="form-row">
                  <label class="form-label">New Password</label>
                  <input v-model="passwordForm.new" type="password" class="form-input" placeholder="Min. 8 characters" />
                </div>
                <div class="form-row">
                  <label class="form-label">Confirm New Password</label>
                  <input v-model="passwordForm.confirm" type="password" class="form-input" placeholder="Repeat password" />
                </div>
                <button @click="savePassword" class="save-btn violet">
                  <i class="fa-solid fa-lock"></i> Update Password
                </button>
              </div>
            </div>

            <!-- Danger Zone -->
            <div class="settings-card danger-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-triangle-exclamation settings-icon red"></i>
                <div>
                  <div class="settings-card-title">Danger Zone</div>
                  <div class="settings-card-sub">Irreversible account actions</div>
                </div>
              </div>
              <div class="danger-actions">
                <div class="danger-item">
                  <div>
                    <div class="danger-label">Export All Data</div>
                    <div class="danger-desc">Download all your reports and data</div>
                  </div>
                  <button class="danger-btn outline"><i class="fa-solid fa-download"></i> Export</button>
                </div>
                <div class="danger-item">
                  <div>
                    <div class="danger-label">Delete Account</div>
                    <div class="danger-desc">Permanently delete your account and all data</div>
                  </div>
                  <button class="danger-btn red"><i class="fa-solid fa-trash"></i> Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        </transition>

        <!-- ══ APPEARANCE SETTINGS ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'appearance'" key="appearance" class="view-panel">
          <div class="settings-layout">
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-palette settings-icon violet"></i>
                <div>
                  <div class="settings-card-title">Theme & Appearance</div>
                  <div class="settings-card-sub">Customize how ReportGen looks for you</div>
                </div>
              </div>
              <div class="settings-form">
                <div class="form-row">
                  <label class="form-label">Color Theme</label>
                  <div class="theme-grid">
                    <button v-for="t in ['System','Light','Dark']" :key="t" @click="setThemeMode(t)" class="theme-btn" :class="{ active: themeMode === t }">
                      <i :class="t === 'Light' ? 'fa-solid fa-sun' : t === 'Dark' ? 'fa-solid fa-moon' : 'fa-solid fa-laptop'"></i>
                      {{ t }}
                    </button>
                  </div>
                </div>
                <div class="form-row">
                  <label class="form-label">Accent Color</label>
                  <div class="accent-grid">
                    <button v-for="c in ACCENT_COLORS" :key="c.val" @click="accentColor = c.val" class="accent-dot" :class="{ active: accentColor === c.val }" :style="{ background: c.val }" :title="c.name">
                      <i v-if="accentColor === c.val" class="fa-solid fa-check text-white text-xs"></i>
                    </button>
                  </div>
                </div>
                <div class="form-row">
                  <label class="form-label">Font Family</label>
                  <select v-model="appFont" class="form-input">
                    <option value="'DM Sans', sans-serif">DM Sans</option>
                    <option value="'Inter', sans-serif">Inter</option>
                    <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                    <option value="Georgia, serif">Georgia</option>
                  </select>
                </div>
                <div class="form-row">
                  <label class="form-label">Compact Mode</label>
                  <div class="toggle-row">
                    <span class="toggle-desc">Reduce padding and spacing throughout the app</span>
                    <div class="toggle-wrap" @click="compactMode = !compactMode">
                      <div class="toggle-track" :class="{ active: compactMode }">
                        <div class="toggle-thumb"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Live Preview -->
                <div class="preview-box" :style="{ '--preview-accent': accentColor, fontFamily: appFont }">
                  <div class="preview-bar" :style="{ background: accentColor }"></div>
                  <div class="preview-content">
                    <div class="preview-title">Preview</div>
                    <div class="preview-btn" :style="{ background: accentColor }">Save Report</div>
                  </div>
                </div>
                <button @click="saveAppearance" class="save-btn" :style="{ background: accentColor }">
                  <i class="fa-solid fa-check"></i> Apply Settings
                </button>
              </div>
            </div>
          </div>
        </div>
        </transition>

        <!-- ══ NOTIFICATIONS SETTINGS ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'notifications-settings'" key="notif-settings" class="view-panel">
          <div class="settings-layout">
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-bell settings-icon amber"></i>
                <div>
                  <div class="settings-card-title">Notification Preferences</div>
                  <div class="settings-card-sub">Control what alerts you receive</div>
                </div>
              </div>
              <div class="settings-form">
                <div v-for="ns in NOTIF_SETTINGS" :key="ns.key" class="notif-setting-row">
                  <div>
                    <div class="ns-label">{{ ns.label }}</div>
                    <div class="ns-desc">{{ ns.desc }}</div>
                  </div>
                  <div class="toggle-wrap" @click="notifPrefs[ns.key] = !notifPrefs[ns.key]">
                    <div class="toggle-track" :class="{ active: notifPrefs[ns.key] }">
                      <div class="toggle-thumb"></div>
                    </div>
                  </div>
                </div>
                <button @click="saveNotifPrefs" class="save-btn">
                  <i class="fa-solid fa-floppy-disk"></i> Save Preferences
                </button>
              </div>
            </div>
          </div>
        </div>
        </transition>

        <!-- ══ API & INTEGRATIONS ══ -->
        <transition name="view" mode="out-in">
        <div v-if="activeView === 'integrations'" key="integrations" class="view-panel">
          <div class="settings-layout">
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-plug settings-icon emerald"></i>
                <div>
                  <div class="settings-card-title">API Access</div>
                  <div class="settings-card-sub">Manage API keys for programmatic access</div>
                </div>
              </div>
              <div class="settings-form">
                <div class="api-key-display">
                  <div class="api-key-label">Your API Key</div>
                  <div class="api-key-value">
                    <span>{{ apiKeyVisible ? 'rg_live_••••••••••••••••••••••••••••••••' : 'rg_live_sk_••••••••••••••••••' }}</span>
                    <button @click="apiKeyVisible = !apiKeyVisible" class="row-btn"><i :class="apiKeyVisible ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i></button>
                    <button class="row-btn" @click="copyApiKey"><i class="fa-solid fa-copy"></i></button>
                  </div>
                  <button class="save-btn mt-3" style="width:auto">
                    <i class="fa-solid fa-rotate"></i> Regenerate Key
                  </button>
                </div>
              </div>
            </div>
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-link settings-icon indigo"></i>
                <div>
                  <div class="settings-card-title">Integrations</div>
                  <div class="settings-card-sub">Connect third-party services</div>
                </div>
              </div>
              <div class="integrations-list">
                <div v-for="ig in INTEGRATIONS" :key="ig.name" class="integration-item">
                  <div class="ig-icon" :class="ig.color"><i :class="ig.icon"></i></div>
                  <div class="ig-info">
                    <div class="ig-name">{{ ig.name }}</div>
                    <div class="ig-desc">{{ ig.desc }}</div>
                  </div>
                  <div class="ig-status" :class="ig.connected ? 'connected' : ''">
                    <span>{{ ig.connected ? 'Connected' : 'Connect' }}</span>
                    <i :class="ig.connected ? 'fa-solid fa-circle-check' : 'fa-solid fa-plug'"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        </transition>

      </div>
    </main>

    <!-- ══════════════════════════════════════════════════════════
         MODALS
    ══════════════════════════════════════════════════════════ -->

    <!-- Delete Confirm -->
    <Teleport to="body">
      <transition name="modal">
        <div v-if="deleteTarget" class="modal-backdrop" @click.self="deleteTarget = null">
          <div class="modal-box sm-modal">
            <div class="modal-danger-icon"><i class="fa-solid fa-trash"></i></div>
            <div class="modal-title">Delete Report?</div>
            <div class="modal-sub">"{{ deleteTarget?.title }}" will be permanently removed.</div>
            <div class="modal-actions">
              <button @click="deleteTarget = null" class="modal-btn outline">Cancel</button>
              <button @click="deleteReport" class="modal-btn danger">Delete</button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Share Modal -->
    <Teleport to="body">
      <transition name="modal">
        <div v-if="shareModal.show" class="modal-backdrop" @click.self="shareModal.show = false">
          <div class="modal-box">
            <div class="modal-header">
              <div class="modal-header-left">
                <div class="modal-icon indigo"><i class="fa-solid fa-share-nodes"></i></div>
                <div>
                  <div class="modal-title sm">Share Report</div>
                  <div class="modal-sub sm">{{ shareModal.report?.title }}</div>
                </div>
              </div>
              <button @click="shareModal.show = false" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
              <div class="form-row">
                <label class="form-label">Invite by email</label>
                <div class="invite-row">
                  <input v-model="shareModal.email" type="email" placeholder="colleague@company.com" class="form-input flex-1" />
                  <select v-model="shareModal.permission" class="form-input w-auto">
                    <option value="view">Can view</option>
                    <option value="edit">Can edit</option>
                    <option value="admin">Admin</option>
                  </select>
                  <button @click="sendInvite" class="save-btn w-auto">Send</button>
                </div>
              </div>
              <div v-if="shareModal.invites.length" class="invite-list">
                <div v-for="(inv, i) in shareModal.invites" :key="i" class="invite-item">
                  <div class="invite-avatar">{{ inv.email[0].toUpperCase() }}</div>
                  <div class="invite-info">
                    <div class="invite-email">{{ inv.email }}</div>
                    <div class="invite-perm">{{ inv.permission }} · Pending</div>
                  </div>
                  <button @click="shareModal.invites.splice(i,1)" class="row-btn danger"><i class="fa-solid fa-xmark"></i></button>
                </div>
              </div>
              <div class="form-row">
                <label class="form-label">Share Link</label>
                <div class="share-link-row">
                  <input :value="shareModal.link" readonly class="form-input flex-1 font-mono text-xs" />
                  <button @click="copyLink" class="save-btn w-auto" :class="{ 'copied': shareModal.copied }">
                    <i :class="shareModal.copied ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i>
                    {{ shareModal.copied ? 'Copied!' : 'Copy' }}
                  </button>
                </div>
              </div>
              <div class="public-access-row">
                <div>
                  <div class="pa-label">Public Access</div>
                  <div class="pa-desc">Anyone with the link can view</div>
                </div>
                <div class="toggle-wrap" @click="shareModal.publicAccess = !shareModal.publicAccess">
                  <div class="toggle-track" :class="{ active: shareModal.publicAccess }">
                    <div class="toggle-thumb"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <transition name="toast">
        <div v-if="toast.show" class="toast-notification" :class="toast.type">
          <i :class="toast.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
          {{ toast.message }}
        </div>
      </transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const $page = usePage();

const props = defineProps({
  recentReports: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ total_reports: 0, published_reports: 0, draft_reports: 0, archived_reports: 0, total_templates: 0 }) },
  reports: { type: Object, default: () => ({ data: [], total: 0, last_page: 1, from: 0, to: 0, links: [] }) },
});

// ─── State ─────────────────────────────────────────────────────
const activeView = ref('overview');
const sidebarCollapsed = ref(false);
const isDark = ref(false);
const globalSearch = ref('');
const searchFocused = ref(false);
const activityPeriod = ref('30d');
const analyticsPeriod = ref('30');
const analyticsStatusFilter = ref('');
const reportsSearch = ref('');
const reportsFilter = ref('all');
const reportsSortBy = ref('updated_at');
const reportsViewMode = ref('grid');
const openStatusDropdown = ref(null);
const showNotifs = ref(false);
const notifRef = ref(null);
const apiKeyVisible = ref(false);
const accentColor = ref('#6366f1');
const appFont = ref("'DM Sans', sans-serif");
const compactMode = ref(false);
const themeMode = ref('System');
const deleteTarget = ref(null);
const statusMenuRefs = ref({});

let activityChart = null, donutChart = null, radarChart = null, lineChart = null, sparklineChart = null, analyticsAreaChart = null, weekdayChart = null, templateUsageChart = null;

const toast = reactive({ show: false, message: '', type: 'success' });
const shareModal = reactive({ show: false, report: null, email: '', permission: 'view', invites: [], link: '', copied: false, publicAccess: false });
const profileForm = reactive({ name: '', email: '', jobTitle: '', org: '' });
const passwordForm = reactive({ current: '', new: '', confirm: '' });
const notifPrefs = reactive({ report_published: true, team_invite: true, weekly_summary: false, product_updates: true, security: true });

const currentTime = ref('');
let clockTimer = null;

// ─── Constants ─────────────────────────────────────────────────
const NAV_ITEMS = [
  { id: 'overview', label: 'Overview', icon: 'fa-solid fa-gauge-high' },
  { id: 'analytics', label: 'Analytics', icon: 'fa-solid fa-chart-mixed', badge: 'NEW' },
];
const REPORT_NAV = [
  { id: 'reports', label: 'My Reports', icon: 'fa-solid fa-file-lines' },
  { id: 'templates', label: 'Templates', icon: 'fa-solid fa-layer-group' },
];
const ACCOUNT_NAV = [
  { id: 'profile', label: 'Profile', icon: 'fa-solid fa-user' },
  { id: 'appearance', label: 'Appearance', icon: 'fa-solid fa-palette' },
  { id: 'notifications-settings', label: 'Notifications', icon: 'fa-solid fa-bell' },
  { id: 'integrations', label: 'Integrations', icon: 'fa-solid fa-plug' },
];
const QUICK_ACTIONS = [
  { route: 'reports.create', label: 'New Report', desc: 'Start from scratch', icon: 'fa-solid fa-pen-to-square', color: 'indigo' },
  { route: 'templates.index', label: 'Browse Templates', desc: 'Pick a template', icon: 'fa-solid fa-grid-2', color: 'violet' },
  { route: 'reports.index', label: 'All Reports', desc: 'View & manage', icon: 'fa-solid fa-folder-open', color: 'emerald' },
  { route: 'profile.edit', label: 'Profile Settings', desc: 'Update account', icon: 'fa-solid fa-user-gear', color: 'amber' },
];
const ACCENT_COLORS = [
  { val: '#6366f1', name: 'Indigo' }, { val: '#8b5cf6', name: 'Violet' }, { val: '#ec4899', name: 'Pink' },
  { val: '#10b981', name: 'Emerald' }, { val: '#f59e0b', name: 'Amber' }, { val: '#ef4444', name: 'Red' },
  { val: '#0ea5e9', name: 'Sky' }, { val: '#14b8a6', name: 'Teal' },
];
const NOTIF_SETTINGS = [
  { key: 'report_published', label: 'Report Published', desc: 'When a report goes live' },
  { key: 'team_invite', label: 'Team Invitations', desc: 'Collaboration requests' },
  { key: 'weekly_summary', label: 'Weekly Summary', desc: 'Weekly digest of activity' },
  { key: 'product_updates', label: 'Product Updates', desc: 'New features and improvements' },
  { key: 'security', label: 'Security Alerts', desc: 'Login and security events' },
];
const INTEGRATIONS = [
  { name: 'Google Drive', desc: 'Export reports directly to Drive', icon: 'fa-brands fa-google-drive', color: 'emerald', connected: false },
  { name: 'Slack', desc: 'Get notifications in your channels', icon: 'fa-brands fa-slack', color: 'violet', connected: false },
  { name: 'Microsoft Teams', desc: 'Share reports in Teams', icon: 'fa-brands fa-microsoft', color: 'indigo', connected: false },
  { name: 'Zapier', desc: 'Automate workflows', icon: 'fa-solid fa-bolt', color: 'amber', connected: false },
];

const templatePreviews = [
  { slug: 'executive-dark', name: 'Executive Dark', desc: 'Sleek dark executive theme', gradient: 'linear-gradient(135deg,#0f172a,#1e1b4b)', preview: '<div style="color:#818cf8;font-size:9px;font-weight:800;padding:6px">ANNUAL REPORT 2024</div>' },
  { slug: 'modern-minimal', name: 'Modern Minimal', desc: 'Clean typography-first', gradient: 'linear-gradient(135deg,#f8fafc,#e0f2f1)', preview: '<div style="color:#0f172a;font-size:10px;font-weight:300;padding:6px">Strategic<br>Growth</div>' },
  { slug: 'bold-analytics', name: 'Bold Analytics', desc: 'High-impact data design', gradient: 'linear-gradient(135deg,#1e293b,#0f172a)', preview: '<div style="color:#f59e0b;font-size:10px;font-weight:900;padding:6px">DATA<br>REPORT</div>' },
  { slug: 'clean-professional', name: 'Clean Pro', desc: 'Business proposals', gradient: 'linear-gradient(135deg,#ffffff,#f0f4ff)', preview: '<div style="background:#6366f1;width:20px;height:100%;float:left;"></div><div style="padding:6px;font-size:9px;color:#0f172a">Q4 Report</div>' },
];

const notifications = ref([
  { id: 1, type: 'success', icon: 'fa-solid fa-circle-check', msg: 'Report "Q4 Review" published', time: '2 min ago', read: false },
  { id: 2, type: 'info', icon: 'fa-solid fa-user-plus', msg: 'Team member invited', time: '1h ago', read: false },
  { id: 3, type: 'warning', icon: 'fa-solid fa-triangle-exclamation', msg: 'Storage 80% full', time: '3h ago', read: true },
  { id: 4, type: 'info', icon: 'fa-solid fa-wand-magic-sparkles', msg: 'New template: "Bold Analytics"', time: '1d ago', read: true },
]);

// ─── Computed ───────────────────────────────────────────────────
const userInitial = computed(() => ($page?.props?.auth?.user?.name?.[0] || 'U').toUpperCase());
const firstName = computed(() => $page?.props?.auth?.user?.name?.split(' ')[0] || 'there');
const currentViewLabel = computed(() => {
  const all = [...NAV_ITEMS, ...REPORT_NAV, ...ACCOUNT_NAV];
  return all.find(n => n.id === activeView.value)?.label || 'Overview';
});
const timeOfDay = computed(() => { const h = new Date().getHours(); return h < 12 ? 'morning' : h < 17 ? 'afternoon' : 'evening'; });
const timeEmoji = computed(() => { const h = new Date().getHours(); return h < 12 ? '☀️' : h < 17 ? '🌤️' : '🌙'; });
const todayFormatted = computed(() => new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' }));
const publishRate = computed(() => {
  const t = props.stats?.total_reports || 0;
  return t ? Math.round(((props.stats?.published_reports || 0) / t) * 100) : 0;
});
const donutLegend = computed(() => [
  { label: 'Published', color: '#10b981', val: props.stats?.published_reports || 0 },
  { label: 'Drafts', color: '#f59e0b', val: props.stats?.draft_reports || 0 },
  { label: 'Archived', color: '#94a3b8', val: props.stats?.archived_reports || 0 },
]);
const kpiCards = computed(() => [
  { key: 'total_reports', label: 'Total Reports', icon: 'fa-solid fa-file-lines', iconClass: 'indigo', barClass: 'indigo', progress: '70%', trend: '+12%', trendUp: true, fallback: 0, sub: 'All time created' },
  { key: 'published_reports', label: 'Published', icon: 'fa-solid fa-globe', iconClass: 'emerald', barClass: 'emerald', progress: publishRate.value + '%', trend: '+8%', trendUp: true, fallback: 0, sub: `${publishRate.value}% publish rate` },
  { key: 'draft_reports', label: 'In Draft', icon: 'fa-solid fa-pen-clip', iconClass: 'amber', barClass: 'amber', progress: '45%', trend: '-3%', trendUp: false, fallback: 0, sub: 'Ready to publish' },
  { key: 'total_templates', label: 'Templates', icon: 'fa-solid fa-layer-group', iconClass: 'violet', barClass: 'violet', progress: '80%', trend: '+4', trendUp: true, fallback: 4, sub: 'Available templates' },
]);
const analyticsMetrics = computed(() => [
  { label: 'Reports Created', value: props.stats?.total_reports || 0, icon: 'fa-solid fa-file-plus', color: 'indigo', change: '+12%', up: true },
  { label: 'Published', value: props.stats?.published_reports || 0, icon: 'fa-solid fa-globe', color: 'emerald', change: '+8%', up: true },
  { label: 'PDF Downloads', value: Math.floor((props.stats?.total_reports || 0) * 2.4), icon: 'fa-solid fa-download', color: 'violet', change: '+31%', up: true },
  { label: 'Avg Pages/Report', value: '4.2', icon: 'fa-solid fa-file-lines', color: 'amber', change: '+0.8', up: true },
  { label: 'Templates Used', value: props.stats?.total_templates || 4, icon: 'fa-solid fa-layer-group', color: 'sky', change: 'this month', up: true },
]);
const filteredReports = computed(() => {
  let r = props.recentReports || [];
  if (reportsFilter.value !== 'all') r = r.filter(x => x.status === reportsFilter.value);
  if (reportsSearch.value.trim()) {
    const q = reportsSearch.value.toLowerCase();
    r = r.filter(x => x.title?.toLowerCase().includes(q));
  }
  return r;
});
const filteredAnalyticsReports = computed(() => {
  let r = props.recentReports || [];
  if (analyticsStatusFilter.value) r = r.filter(x => x.status === analyticsStatusFilter.value);
  if (globalSearch.value.trim()) {
    const q = globalSearch.value.toLowerCase();
    r = r.filter(x => x.title?.toLowerCase().includes(q));
  }
  return r;
});

// ─── Methods ────────────────────────────────────────────────────
const formatDate = d => {
  if (!d) return '';
  const diff = Math.floor((Date.now() - new Date(d)) / 1000);
  if (diff < 60) return 'just now';
  if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff/86400)}d ago`;
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
const showToast = (message, type = 'success') => {
  toast.message = message; toast.type = type; toast.show = true;
  setTimeout(() => toast.show = false, 3000);
};
const confirmDelete = r => { deleteTarget.value = r; };
const deleteReport = () => {
  if (!deleteTarget.value) return;
  router.delete(route('reports.destroy', deleteTarget.value.slug), {
    onSuccess: () => { deleteTarget.value = null; showToast('Report deleted.'); },
    onError: () => { deleteTarget.value = null; showToast('Delete failed.', 'error'); },
  });
};
const duplicateReport = r => {
  router.post(route('reports.duplicate', r.slug), {}, {
    onSuccess: () => showToast('Report duplicated!'),
    onError: () => showToast('Duplication failed.', 'error'),
  });
};
const changeStatus = (r, status) => {
  router.patch(route('reports.status', r.slug), { status }, {
    preserveState: true, preserveScroll: true,
    onSuccess: () => showToast(`Status updated to ${status}.`),
    onError: () => showToast('Status update failed.', 'error'),
  });
};
const openShareModal = r => {
  Object.assign(shareModal, { show: true, report: r, email: '', invites: [], copied: false, publicAccess: false, link: `${window.location.origin}/reports/${r.slug}/preview` });
};
const sendInvite = () => {
  if (!shareModal.email.trim()) return;
  shareModal.invites.push({ email: shareModal.email, permission: shareModal.permission });
  shareModal.email = '';
  showToast('Invite sent!');
};
const copyLink = () => {
  navigator.clipboard?.writeText(shareModal.link);
  shareModal.copied = true;
  setTimeout(() => shareModal.copied = false, 2000);
};
const copyApiKey = () => { showToast('API key copied!'); };
const markAllRead = () => { notifications.value.forEach(n => n.read = true); };
const toggleDark = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};
const setThemeMode = t => {
  themeMode.value = t;
  if (t === 'Dark') { isDark.value = true; document.documentElement.classList.add('dark'); }
  else if (t === 'Light') { isDark.value = false; document.documentElement.classList.remove('dark'); }
  else { isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches; document.documentElement.classList.toggle('dark', isDark.value); }
};
const saveProfile = () => showToast('Profile saved!');
const savePassword = () => { Object.assign(passwordForm, { current: '', new: '', confirm: '' }); showToast('Password updated!'); };
const saveAppearance = () => { localStorage.setItem('rb-accent', accentColor.value); showToast('Appearance saved!'); };
const saveNotifPrefs = () => showToast('Notification preferences saved!');
const exportAnalytics = () => showToast('Analytics export coming soon.', 'error');

// ─── Charts ─────────────────────────────────────────────────────
const isDarkMode = () => isDark.value;
const chartColors = { indigo: '#6366f1', violet: '#8b5cf6', emerald: '#10b981', amber: '#f59e0b', sky: '#0ea5e9', red: '#ef4444' };
const gridColor = () => isDarkMode() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
const tickColor = () => isDarkMode() ? '#475569' : '#94a3b8';

const buildCharts = () => {
  nextTick(() => {
    if (!window.Chart) return;
    const t = props.stats?.total_reports || 12;
    const dark = isDarkMode();

    // Activity
    const ac = document.getElementById('activityChart');
    if (ac) {
      if (activityChart) activityChart.destroy();
      const days = activityPeriod.value === '7d' ? 7 : activityPeriod.value === '30d' ? 12 : 18;
      const labels = [], pub = [], dra = [];
      for (let i = days - 1; i >= 0; i--) {
        const d = new Date(); d.setDate(d.getDate() - i);
        labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        const base = Math.max(0, Math.round((t / days) * (0.4 + Math.random() * 0.8)));
        pub.push(Math.round(base * 0.6)); dra.push(Math.round(base * 0.4));
      }
      activityChart = new window.Chart(ac, {
        type: 'bar', data: { labels, datasets: [
          { label: 'Published', data: pub, backgroundColor: '#6366f1cc', borderRadius: 5, borderSkipped: false },
          { label: 'Drafts', data: dra, backgroundColor: '#fbbf24cc', borderRadius: 5, borderSkipped: false },
        ]},
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: tickColor(), font: { size: 11 } } }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', titleColor: dark ? '#f1f5f9' : '#0f172a', bodyColor: dark ? '#94a3b8' : '#64748b', borderColor: dark ? '#334155' : '#e2e8f0', borderWidth: 1, cornerRadius: 10, padding: 12 } },
          scales: { x: { stacked: true, grid: { color: gridColor() }, ticks: { color: tickColor(), font: { size: 10 } } }, y: { stacked: true, grid: { color: gridColor() }, ticks: { color: tickColor(), precision: 0, font: { size: 10 } } } },
        },
      });
    }

    // Donut
    const dc = document.getElementById('donutChart');
    if (dc) {
      if (donutChart) donutChart.destroy();
      const pub = props.stats?.published_reports || 0, dra = props.stats?.draft_reports || 0, arc = props.stats?.archived_reports || 0;
      donutChart = new window.Chart(dc, {
        type: 'doughnut',
        data: { labels: ['Published', 'Drafts', 'Archived'], datasets: [{ data: (pub + dra + arc) > 0 ? [pub, dra, arc] : [1,1,1], backgroundColor: ['#10b981','#f59e0b','#94a3b8'], borderWidth: 0, hoverOffset: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', cornerRadius: 10, padding: 12 } } },
      });
    }

    // Radar
    const rc = document.getElementById('radarChart');
    if (rc) {
      if (radarChart) radarChart.destroy();
      radarChart = new window.Chart(rc, {
        type: 'radar',
        data: { labels: ['Reports', 'Published', 'Templates', 'Exports', 'Shares', 'Views'], datasets: [{
          label: 'Score', data: [78, Math.min(100, publishRate.value + 30), 85, 60, 45, 90],
          backgroundColor: '#6366f133', borderColor: '#6366f1', pointBackgroundColor: '#6366f1', borderWidth: 2, pointRadius: 5,
        }]},
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
          scales: { r: { grid: { color: gridColor() }, ticks: { color: tickColor(), font: { size: 9 }, backdropColor: 'transparent' }, pointLabels: { color: tickColor(), font: { size: 11 } }, angleLines: { color: gridColor() } } },
        },
      });
    }

    // Line
    const lc = document.getElementById('lineChart');
    if (lc) {
      if (lineChart) lineChart.destroy();
      const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      lineChart = new window.Chart(lc, {
        type: 'line',
        data: { labels: months, datasets: [{
          label: 'Reports', data: months.map(() => Math.floor(Math.random() * 8 + 2)),
          borderColor: '#10b981', backgroundColor: '#10b98120', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#10b981',
        }]},
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', cornerRadius: 10, padding: 12 } },
          scales: { x: { grid: { color: gridColor() }, ticks: { color: tickColor(), font: { size: 10 } } }, y: { grid: { color: gridColor() }, ticks: { color: tickColor(), precision: 0, font: { size: 10 } } } },
        },
      });
    }

    // Sparkline
    const sc = document.getElementById('sparklineChart');
    if (sc) {
      if (sparklineChart) sparklineChart.destroy();
      sparklineChart = new window.Chart(sc, {
        type: 'line',
        data: { labels: ['','','','','','',''], datasets: [{ data: [3,5,2,7,4,8,6], borderColor: accentColor.value, backgroundColor: accentColor.value + '20', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } },
      });
    }

    // Analytics Area
    const aac = document.getElementById('analyticsAreaChart');
    if (aac) {
      if (analyticsAreaChart) analyticsAreaChart.destroy();
      const aLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      let cum = 0;
      analyticsAreaChart = new window.Chart(aac, {
        type: 'line',
        data: { labels: aLabels, datasets: [
          { label: 'Cumulative Reports', data: aLabels.map(() => { cum += Math.floor(Math.random()*5+1); return cum; }), borderColor: '#6366f1', backgroundColor: '#6366f115', fill: true, tension: 0.4, borderWidth: 2.5 },
          { label: 'Monthly Created', data: aLabels.map(() => Math.floor(Math.random()*8+1)), borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.4, borderWidth: 2, borderDash: [4,4] },
        ]},
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: tickColor(), font: { size: 11 } } }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', cornerRadius: 10, padding: 12 } },
          scales: { x: { grid: { color: gridColor() }, ticks: { color: tickColor() } }, y: { grid: { color: gridColor() }, ticks: { color: tickColor(), precision: 0 } } },
        },
      });
    }

    // Weekday bar
    const wc = document.getElementById('weekdayChart');
    if (wc) {
      if (weekdayChart) weekdayChart.destroy();
      weekdayChart = new window.Chart(wc, {
        type: 'bar',
        data: { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], datasets: [{ label: 'Reports', data: [4,7,5,8,6,2,1], backgroundColor: '#8b5cf6cc', borderRadius: 6, borderSkipped: false }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', cornerRadius: 10, padding: 12 } },
          scales: { x: { grid: { display: false }, ticks: { color: tickColor() } }, y: { grid: { color: gridColor() }, ticks: { color: tickColor(), precision: 0 } } },
        },
      });
    }

    // Template usage
    const tuc = document.getElementById('templateUsageChart');
    if (tuc) {
      if (templateUsageChart) templateUsageChart.destroy();
      templateUsageChart = new window.Chart(tuc, {
        type: 'doughnut',
        data: { labels: ['Executive Dark','Modern Minimal','Bold Analytics','Clean Pro'], datasets: [{ data: [35,28,22,15], backgroundColor: ['#6366f1','#10b981','#f59e0b','#8b5cf6'], borderWidth: 0, hoverOffset: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { color: tickColor(), font: { size: 10 }, boxWidth: 10, padding: 8 } }, tooltip: { backgroundColor: dark ? '#1e293b' : '#fff', cornerRadius: 10, padding: 12 } } },
      });
    }
  });
};

const loadChartJs = () => {
  if (window.Chart) { buildCharts(); return; }
  const s = document.createElement('script');
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js';
  s.onload = buildCharts;
  document.head.appendChild(s);
};

const updateTime = () => { currentTime.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }); };

const handleOutsideClick = (e) => {
  if (notifRef.value && !notifRef.value.contains(e.target)) showNotifs.value = false;
  if (openStatusDropdown.value) {
    const ref = statusMenuRefs.value[openStatusDropdown.value];
    if (ref && !ref.contains(e.target)) openStatusDropdown.value = null;
  }
};

watch(activeView, () => nextTick(buildCharts));
watch(isDark, () => nextTick(buildCharts));

onMounted(() => {
  updateTime();
  clockTimer = setInterval(updateTime, 30000);
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') { isDark.value = true; document.documentElement.classList.add('dark'); }
  profileForm.name = $page?.props?.auth?.user?.name || '';
  profileForm.email = $page?.props?.auth?.user?.email || '';
  const savedAccent = localStorage.getItem('rb-accent');
  if (savedAccent) accentColor.value = savedAccent;
  loadChartJs();
  document.addEventListener('click', handleOutsideClick);
});
onBeforeUnmount(() => {
  if (clockTimer) clearInterval(clockTimer);
  [activityChart, donutChart, radarChart, lineChart, sparklineChart, analyticsAreaChart, weekdayChart, templateUsageChart].forEach(c => c?.destroy());
  document.removeEventListener('click', handleOutsideClick);
});
</script>

<style>
/* ══════════════════════════════════════════
   CSS CUSTOM PROPERTIES
══════════════════════════════════════════ */
.dashboard-shell {
  --bg: #f0f2f8;
  --surface: #ffffff;
  --surface2: #f8faff;
  --border: rgba(99,102,241,0.1);
  --border2: #e2e8f0;
  --t-primary: #0f172a;
  --t-secondary: #475569;
  --t-muted: #94a3b8;
  --sidebar-bg: #ffffff;
  --sidebar-border: rgba(99,102,241,0.08);
  --accent: #6366f1;
  --shadow: 0 4px 24px rgba(99,102,241,0.08);
  --shadow-lg: 0 12px 48px rgba(99,102,241,0.12);
  --indigo: #6366f1;
  --violet: #8b5cf6;
  --emerald: #10b981;
  --amber: #f59e0b;
  --sky: #0ea5e9;
  --red: #ef4444;
  font-family: 'DM Sans', sans-serif;
}
.dashboard-shell.dark {
  --bg: #060b14;
  --surface: #0f1624;
  --surface2: #141e2e;
  --border: rgba(99,102,241,0.15);
  --border2: rgba(255,255,255,0.08);
  --t-primary: #f1f5f9;
  --t-secondary: #94a3b8;
  --t-muted: #475569;
  --sidebar-bg: #0a1020;
  --sidebar-border: rgba(99,102,241,0.1);
  --shadow: 0 4px 24px rgba(0,0,0,0.4);
  --shadow-lg: 0 12px 48px rgba(0,0,0,0.5);
}

/* ── Shell ── */
.dashboard-shell {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: var(--bg);
  position: relative;
}

/* ── Background Mesh ── */
.bg-mesh { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
.mesh-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
  animation-direction: alternate;
}
.mesh-blob-1 { width: 600px; height: 600px; top: -20%; left: -10%; background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%); animation: blobA 20s infinite alternate; }
.mesh-blob-2 { width: 500px; height: 500px; bottom: -15%; right: -5%; background: radial-gradient(circle, rgba(139,92,246,0.06) 0%, transparent 70%); animation: blobB 25s infinite alternate; }
.mesh-blob-3 { width: 350px; height: 350px; top: 40%; left: 40%; background: radial-gradient(circle, rgba(16,185,129,0.05) 0%, transparent 70%); animation: blobC 18s infinite alternate; }
.mesh-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(99,102,241,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,0.03) 1px, transparent 1px); background-size: 48px 48px; }
@keyframes blobA { from { transform: translate(0,0) scale(1); } to { transform: translate(60px,40px) scale(1.15); } }
@keyframes blobB { from { transform: translate(0,0) scale(1); } to { transform: translate(-50px,-30px) scale(1.1); } }
@keyframes blobC { from { transform: translate(-50%,-50%) scale(1); } to { transform: translate(-50%,-50%) scale(1.2) rotate(20deg); } }

/* ══════════════════════════════════════════
   SIDEBAR
══════════════════════════════════════════ */
.sidebar {
  width: 240px;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--sidebar-border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  z-index: 10;
  transition: width 0.3s cubic-bezier(0.16,1,0.3,1);
  box-shadow: 4px 0 24px rgba(0,0,0,0.04);
  overflow: hidden;
  position: relative;
}
.sidebar.collapsed { width: 64px; }

/* Logo */
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 16px 14px;
  border-bottom: 1px solid var(--sidebar-border);
  position: relative;
}
.logo-mark {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 13px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(99,102,241,0.35);
}
.logo-text { display: flex; flex-direction: column; min-width: 0; }
.logo-name { font-size: 14px; font-weight: 800; color: var(--t-primary); letter-spacing: -0.02em; white-space: nowrap; }
.logo-badge { font-size: 9px; font-weight: 700; color: var(--indigo); letter-spacing: 0.08em; text-transform: uppercase; }
.collapse-btn {
  position: absolute;
  right: 10px;
  width: 22px;
  height: 22px;
  border-radius: 6px;
  background: var(--surface2);
  border: 1px solid var(--border2);
  color: var(--t-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
  cursor: pointer;
  transition: all 0.2s;
}
.collapse-btn:hover { background: var(--indigo); color: white; border-color: var(--indigo); }

/* User */
.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-bottom: 1px solid var(--sidebar-border);
}
.collapsed-user { justify-content: center; }
.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  font-size: 13px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.user-avatar.sm { width: 28px; height: 28px; font-size: 11px; }
.user-info { flex: 1; min-width: 0; }
.user-name { font-size: 12px; font-weight: 700; color: var(--t-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: 10px; color: var(--t-muted); white-space: nowrap; }
.user-status { width: 7px; height: 7px; border-radius: 50%; background: #10b981; flex-shrink: 0; box-shadow: 0 0 6px #10b98180; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }

/* Nav */
.sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 8px 8px; display: flex; flex-direction: column; gap: 2px; }
.sidebar-nav::-webkit-scrollbar { width: 3px; }
.sidebar-nav::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 99px; }
.nav-section-label { font-size: 9px; font-weight: 800; color: var(--t-muted); text-transform: uppercase; letter-spacing: 0.1em; padding: 6px 8px 4px; white-space: nowrap; overflow: hidden; }
.nav-divider { height: 1px; background: var(--border2); margin: 6px 0; }
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.18s;
  border: 1px solid transparent;
  text-align: left;
  width: 100%;
  position: relative;
  color: var(--t-secondary);
}
.sidebar.collapsed .nav-item { justify-content: center; padding: 10px; }
.nav-item:hover { background: rgba(99,102,241,0.07); color: var(--indigo); }
.nav-item.active { background: rgba(99,102,241,0.1); border-color: rgba(99,102,241,0.2); color: var(--indigo); }
.nav-item.active .nav-icon { color: var(--indigo); }
.nav-icon { width: 20px; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; position: relative; }
.nav-label { font-size: 12px; font-weight: 600; white-space: nowrap; }
.nav-badge { font-size: 8px; font-weight: 800; background: #10b981; color: white; padding: 1px 5px; border-radius: 4px; text-transform: uppercase; white-space: nowrap; }
.nav-badge-dot { position: absolute; top: -2px; right: -2px; width: 6px; height: 6px; background: #10b981; border-radius: 50%; }

/* Sidebar Footer */
.sidebar-footer { padding: 10px 8px; border-top: 1px solid var(--sidebar-border); display: flex; flex-direction: column; gap: 2px; }
.theme-toggle, .logout-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s;
  color: var(--t-muted);
  width: 100%;
}
.sidebar.collapsed .theme-toggle, .sidebar.collapsed .logout-btn { justify-content: center; }
.theme-toggle:hover { background: rgba(99,102,241,0.07); color: var(--indigo); }
.logout-btn:hover { background: rgba(239,68,68,0.08); color: var(--red); }

/* ══════════════════════════════════════════
   MAIN CONTENT
══════════════════════════════════════════ */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 1;
}

/* TOPBAR */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 28px;
  background: var(--sidebar-bg);
  border-bottom: 1px solid var(--border2);
  gap: 16px;
  flex-shrink: 0;
}
.topbar-left { display: flex; flex-direction: column; gap: 2px; }
.page-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--t-muted); }
.breadcrumb-sep { font-size: 8px; }
.breadcrumb-current { color: var(--indigo); font-weight: 600; }
.page-title-row { display: flex; align-items: center; gap: 10px; }
.page-title { font-size: 20px; font-weight: 800; color: var(--t-primary); letter-spacing: -0.02em; }
.live-indicator { display: flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 700; color: #10b981; background: rgba(16,185,129,0.1); padding: 2px 8px; border-radius: 99px; }
.live-dot { width: 6px; height: 6px; background: #10b981; border-radius: 50%; animation: pulse 1.5s infinite; }
.topbar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* Global search */
.global-search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--surface2);
  border: 1px solid var(--border2);
  border-radius: 12px;
  padding: 7px 14px;
  transition: all 0.2s;
  min-width: 220px;
}
.global-search.focused { border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.search-icon { color: var(--t-muted); font-size: 12px; }
.search-input { border: none; background: transparent; outline: none; font-size: 12px; color: var(--t-primary); flex: 1; }
.search-input::placeholder { color: var(--t-muted); }
.search-kbd { font-size: 9px; background: var(--surface); border: 1px solid var(--border2); border-radius: 4px; padding: 1px 5px; color: var(--t-muted); font-family: monospace; }

/* Notification */
.notif-btn-wrap { position: relative; }
.icon-btn {
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: var(--t-secondary);
  background: var(--surface2);
  border: 1px solid var(--border2);
  font-size: 13px;
  cursor: pointer;
  transition: all 0.18s;
  position: relative;
}
.icon-btn:hover, .icon-btn.active { background: rgba(99,102,241,0.1); color: var(--indigo); border-color: rgba(99,102,241,0.2); }
.notif-count {
  position: absolute; top: -4px; right: -4px;
  width: 16px; height: 16px;
  background: var(--red); color: white;
  border-radius: 50%; font-size: 9px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid var(--sidebar-bg);
}
.notif-panel {
  position: absolute; right: 0; top: calc(100% + 8px);
  width: 320px; background: var(--surface);
  border: 1px solid var(--border2); border-radius: 16px;
  box-shadow: var(--shadow-lg); z-index: 100; overflow: hidden;
}
.notif-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--border2); font-size: 13px; font-weight: 700; color: var(--t-primary); }
.mark-read-btn { font-size: 11px; color: var(--indigo); font-weight: 600; cursor: pointer; }
.notif-list { max-height: 320px; overflow-y: auto; }
.notif-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid var(--border2); }
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--surface2); }
.notif-item.unread { background: rgba(99,102,241,0.04); }
.notif-icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.notif-icon.success { background: rgba(16,185,129,0.15); color: #10b981; }
.notif-icon.info { background: rgba(99,102,241,0.15); color: #6366f1; }
.notif-icon.warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
.notif-msg { font-size: 12px; font-weight: 600; color: var(--t-primary); }
.notif-time { font-size: 10px; color: var(--t-muted); margin-top: 2px; }

/* New Report Btn */
.new-report-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 8px 16px; border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white; font-size: 12px; font-weight: 700;
  text-decoration: none; transition: all 0.2s;
  box-shadow: 0 4px 14px rgba(99,102,241,0.35);
  white-space: nowrap;
}
.new-report-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.45); }
.new-report-btn.sm { padding: 6px 12px; font-size: 11px; }

/* ══════════════════════════════════════════
   VIEW CONTENT
══════════════════════════════════════════ */
.view-content { flex: 1; overflow-y: auto; padding: 24px 28px; scroll-behavior: smooth; }
.view-content::-webkit-scrollbar { width: 5px; }
.view-content::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 99px; }
.view-panel { display: flex; flex-direction: column; gap: 20px; animation: fadeInUp 0.35s ease; }
@keyframes fadeInUp { from { opacity:0; transform: translateY(12px); } to { opacity:1; transform: translateY(0); } }

/* ── Welcome Banner ── */
.welcome-banner {
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e1b4b 100%);
  border-radius: 20px;
  padding: 28px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(99,102,241,0.3);
}
.welcome-banner::before {
  content: '';
  position: absolute;
  top: -60px; right: -60px;
  width: 200px; height: 200px;
  background: radial-gradient(circle, rgba(139,92,246,0.4) 0%, transparent 70%);
  pointer-events: none;
}
.welcome-banner::after {
  content: '';
  position: absolute;
  bottom: -40px; left: 30%;
  width: 150px; height: 150px;
  background: radial-gradient(circle, rgba(99,102,241,0.3) 0%, transparent 70%);
  pointer-events: none;
}
.welcome-left, .welcome-right { position: relative; z-index: 1; }
.welcome-greeting { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
.greeting-emoji { font-size: 28px; }
.greeting-text { font-size: 22px; font-weight: 800; color: white; letter-spacing: -0.02em; }
.greeting-sub { font-size: 12px; color: rgba(255,255,255,0.55); margin-top: 2px; }
.welcome-stats-row { display: flex; align-items: center; gap: 0; }
.ws-item { padding: 0 20px; text-align: center; }
.ws-item:first-child { padding-left: 0; }
.ws-val { display: block; font-size: 26px; font-weight: 900; color: white; line-height: 1; }
.ws-val.text-emerald-400 { color: #34d399; }
.ws-val.text-amber-400 { color: #fbbf24; }
.ws-label { font-size: 10px; color: rgba(255,255,255,0.5); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 3px; }
.ws-divider { width: 1px; height: 36px; background: rgba(255,255,255,0.15); }
.welcome-quote { max-width: 300px; margin-bottom: 16px; }
.welcome-quote i { color: rgba(255,255,255,0.3); font-size: 14px; margin-bottom: 6px; display: block; }
.welcome-quote p { font-size: 13px; font-style: italic; color: rgba(255,255,255,0.7); line-height: 1.6; }
.banner-cta {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 20px; border-radius: 12px;
  background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.2);
  color: white; font-size: 13px; font-weight: 700;
  text-decoration: none; transition: all 0.2s;
}
.banner-cta:hover { background: rgba(255,255,255,0.25); transform: translateY(-1px); }

/* ── KPI Cards ── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}
.kpi-card {
  background: var(--surface);
  border: 1px solid var(--border2);
  border-radius: 16px;
  padding: 20px;
  animation: cardIn 0.4s ease both;
  transition: all 0.2s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
@keyframes cardIn { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
.kpi-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.kpi-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px;
}
.kpi-icon.indigo { background: rgba(99,102,241,0.1); color: var(--indigo); }
.kpi-icon.emerald { background: rgba(16,185,129,0.1); color: var(--emerald); }
.kpi-icon.amber { background: rgba(245,158,11,0.1); color: var(--amber); }
.kpi-icon.violet { background: rgba(139,92,246,0.1); color: var(--violet); }
.kpi-icon.sky { background: rgba(14,165,233,0.1); color: var(--sky); }
.kpi-trend { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 99px; display: flex; align-items: center; gap: 3px; }
.kpi-trend.up { background: rgba(16,185,129,0.1); color: #10b981; }
.kpi-trend.down { background: rgba(239,68,68,0.1); color: #ef4444; }
.kpi-value { font-size: 32px; font-weight: 900; color: var(--t-primary); letter-spacing: -0.03em; line-height: 1; }
.kpi-label { font-size: 12px; color: var(--t-muted); font-weight: 600; margin: 4px 0 12px; }
.kpi-bar { height: 4px; background: var(--border2); border-radius: 99px; overflow: hidden; margin-bottom: 8px; }
.kpi-bar-fill { height: 100%; border-radius: 99px; transition: width 1s ease; }
.kpi-bar-fill.indigo { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
.kpi-bar-fill.emerald { background: linear-gradient(90deg, #10b981, #34d399); }
.kpi-bar-fill.amber { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.kpi-bar-fill.violet { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
.kpi-sub { font-size: 10px; color: var(--t-muted); }

/* ── Charts ── */
.charts-row { display: grid; grid-template-columns: 2fr 1fr; gap: 14px; }
.chart-card {
  background: var(--surface);
  border: 1px solid var(--border2);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  animation: cardIn 0.4s ease both;
}
.chart-card.wide { /* takes 2fr */ }
.chart-card.full { grid-column: 1 / -1; }
.chart-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 8px; }
.chart-title-wrap { display: flex; align-items: center; gap: 10px; }
.chart-icon {
  width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; flex-shrink: 0;
}
.chart-icon.indigo { background: rgba(99,102,241,0.1); color: var(--indigo); }
.chart-icon.violet { background: rgba(139,92,246,0.1); color: var(--violet); }
.chart-icon.emerald { background: rgba(16,185,129,0.1); color: var(--emerald); }
.chart-icon.amber { background: rgba(245,158,11,0.1); color: var(--amber); }
.chart-icon.sky { background: rgba(14,165,233,0.1); color: var(--sky); }
.chart-title { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.chart-sub { font-size: 11px; color: var(--t-muted); }
.chart-area { position: relative; flex: 1; height: 200px; }
.chart-area.donut-area { display: flex; align-items: center; justify-content: center; position: relative; }
.chart-area.tall { height: 280px; }
.donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); text-align: center; pointer-events: none; }
.donut-total { font-size: 26px; font-weight: 900; color: var(--t-primary); }
.donut-label { font-size: 10px; color: var(--t-muted); font-weight: 600; }
.donut-legend { display: flex; flex-direction: column; gap: 6px; margin-top: 14px; }
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 11px; }
.legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
.legend-label { flex: 1; color: var(--t-secondary); font-weight: 500; }
.legend-val { font-weight: 800; color: var(--t-primary); }
.chart-badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; }
.chart-badge.emerald { background: rgba(16,185,129,0.1); color: #10b981; }
.period-tabs { display: flex; background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px; overflow: hidden; }
.period-tab { padding: 4px 10px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.15s; color: var(--t-muted); }
.period-tab.active { background: var(--indigo); color: white; }

/* ── Bottom Row ── */
.bottom-row { display: grid; grid-template-columns: 1.5fr 1fr; gap: 14px; }
.right-col { display: flex; flex-direction: column; gap: 14px; }

/* ── Reports Table Card ── */
.reports-table-card {
  background: var(--surface);
  border: 1px solid var(--border2);
  border-radius: 16px;
  overflow: hidden;
  animation: cardIn 0.4s ease both;
}
.card-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border2); }
.view-all-link { font-size: 11px; font-weight: 700; color: var(--indigo); text-decoration: none; display: flex; align-items: center; gap: 5px; transition: gap 0.15s; }
.view-all-link:hover { gap: 8px; }
.reports-table { }
.reports-table-head {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 100px;
  padding: 8px 20px;
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--t-muted);
  border-bottom: 1px solid var(--border2);
  background: var(--surface2);
}
.reports-table-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 100px;
  padding: 10px 20px;
  align-items: center;
  border-bottom: 1px solid var(--border2);
  transition: background 0.15s;
  animation: rowIn 0.25s ease both;
}
.reports-table-row:last-child { border-bottom: none; }
.reports-table-row:hover { background: var(--surface2); }
@keyframes rowIn { from { opacity:0; transform: translateX(-6px); } to { opacity:1; transform: translateX(0); } }
.report-title-cell { display: flex; align-items: center; gap: 10px; }
.report-file-icon {
  width: 32px; height: 32px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; flex-shrink: 0;
}
.report-file-icon.sm { width: 24px; height: 24px; font-size: 10px; border-radius: 6px; }
.report-file-icon.published { background: rgba(16,185,129,0.1); color: #10b981; }
.report-file-icon.draft { background: rgba(245,158,11,0.1); color: #f59e0b; }
.report-file-icon.archived { background: rgba(148,163,184,0.1); color: #94a3b8; }
.report-name { font-size: 12px; font-weight: 600; color: var(--t-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.report-meta { font-size: 10px; color: var(--t-muted); }
.report-date { font-size: 11px; color: var(--t-muted); }
.report-actions { display: flex; align-items: center; gap: 3px; }
.status-pill {
  display: inline-block;
  font-size: 10px; font-weight: 700;
  padding: 2px 8px; border-radius: 99px;
  text-transform: capitalize;
}
.status-pill.published { background: rgba(16,185,129,0.1); color: #10b981; }
.status-pill.draft { background: rgba(245,158,11,0.1); color: #f59e0b; }
.status-pill.archived { background: rgba(148,163,184,0.1); color: #94a3b8; }
.empty-table { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 32px; color: var(--t-muted); font-size: 13px; }
.empty-table i { font-size: 20px; }

/* Action Buttons */
.row-btn {
  width: 28px; height: 28px;
  border-radius: 7px;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; cursor: pointer;
  transition: all 0.15s;
  color: var(--t-muted);
  text-decoration: none;
}
.row-btn:hover { background: rgba(99,102,241,0.1); color: var(--indigo); }
.row-btn.danger:hover { background: rgba(239,68,68,0.1); color: var(--red); }

/* ── Publish Rate ── */
.publish-rate-card, .quick-actions-card {
  background: var(--surface);
  border: 1px solid var(--border2);
  border-radius: 16px;
  padding: 18px;
}
.pr-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.pr-title { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.pr-value { font-size: 22px; font-weight: 900; }
.text-emerald-400 { color: #34d399; }
.text-amber-400 { color: #fbbf24; }
.pr-track { height: 8px; background: var(--border2); border-radius: 99px; overflow: hidden; }
.pr-fill { height: 100%; background: linear-gradient(90deg, #10b981, #34d399); border-radius: 99px; transition: width 1s ease; }
.pr-sub { font-size: 10px; color: var(--t-muted); margin-top: 6px; }

/* Quick Actions */
.qa-title { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--t-muted); margin-bottom: 12px; }
.qa-list { display: flex; flex-direction: column; gap: 4px; }
.qa-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 10px; border-radius: 10px;
  text-decoration: none; transition: all 0.15s;
  cursor: pointer;
}
.qa-item:hover { background: rgba(99,102,241,0.07); }
.qa-icon {
  width: 30px; height: 30px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; flex-shrink: 0;
}
.qa-icon.indigo { background: rgba(99,102,241,0.1); color: var(--indigo); }
.qa-icon.violet { background: rgba(139,92,246,0.1); color: var(--violet); }
.qa-icon.emerald { background: rgba(16,185,129,0.1); color: var(--emerald); }
.qa-icon.amber { background: rgba(245,158,11,0.1); color: var(--amber); }
.qa-text { flex: 1; min-width: 0; }
.qa-label { font-size: 12px; font-weight: 600; color: var(--t-primary); }
.qa-desc { font-size: 10px; color: var(--t-muted); }
.qa-arrow { font-size: 9px; color: var(--t-muted); }

/* ══ ANALYTICS ══ */
.analytics-header { display: flex; justify-content: flex-end; margin-bottom: 4px; }
.analytics-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.filter-select {
  background: var(--surface); border: 1px solid var(--border2); border-radius: 10px;
  padding: 7px 12px; font-size: 12px; color: var(--t-primary); outline: none; cursor: pointer;
}
.filter-select.sm { padding: 5px 10px; font-size: 11px; }
.filter-btn {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 12px; border-radius: 10px;
  background: var(--surface); border: 1px solid var(--border2);
  font-size: 12px; color: var(--t-secondary); cursor: pointer; transition: all 0.15s;
}
.filter-btn:hover { border-color: var(--indigo); color: var(--indigo); }
.analytics-kpi-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
.analytics-kpi {
  background: var(--surface); border: 1px solid var(--border2); border-radius: 14px; padding: 16px;
  display: flex; flex-direction: column; align-items: flex-start; gap: 4px;
  animation: cardIn 0.35s ease both;
}
.akpi-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; margin-bottom: 4px; }
.akpi-icon.indigo { background: rgba(99,102,241,0.1); color: var(--indigo); }
.akpi-icon.emerald { background: rgba(16,185,129,0.1); color: var(--emerald); }
.akpi-icon.violet { background: rgba(139,92,246,0.1); color: var(--violet); }
.akpi-icon.amber { background: rgba(245,158,11,0.1); color: var(--amber); }
.akpi-icon.sky { background: rgba(14,165,233,0.1); color: var(--sky); }
.akpi-val { font-size: 24px; font-weight: 900; color: var(--t-primary); }
.akpi-label { font-size: 11px; color: var(--t-muted); font-weight: 600; }
.akpi-change { font-size: 10px; font-weight: 700; display: flex; align-items: center; gap: 3px; }
.akpi-change.up { color: #10b981; }
.akpi-change.down { color: #ef4444; }
.analytics-charts { display: flex; flex-direction: column; gap: 14px; }
.mt-4 { margin-top: 0; }
.analytics-table { }
.at-head {
  display: grid; grid-template-columns: 2fr 1fr 80px 1fr 1fr 120px;
  padding: 8px 16px; font-size: 10px; font-weight: 800; text-transform: uppercase;
  letter-spacing: 0.06em; color: var(--t-muted); border-bottom: 1px solid var(--border2); background: var(--surface2);
}
.at-row {
  display: grid; grid-template-columns: 2fr 1fr 80px 1fr 1fr 120px;
  padding: 10px 16px; align-items: center; border-bottom: 1px solid var(--border2);
  transition: background 0.15s; animation: rowIn 0.25s ease both;
}
.at-row:hover { background: var(--surface2); }
.at-title { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: var(--t-primary); }
.at-date { font-size: 11px; color: var(--t-muted); }
.at-actions { display: flex; align-items: center; gap: 3px; }
.table-search {
  background: var(--surface2); border: 1px solid var(--border2); border-radius: 10px;
  padding: 6px 12px; font-size: 12px; color: var(--t-primary); outline: none; width: 180px;
}

/* ══ REPORTS TOOLBAR ══ */
.reports-toolbar {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  background: var(--surface); border: 1px solid var(--border2); border-radius: 14px; padding: 12px 16px;
  flex-wrap: wrap;
}
.toolbar-left, .toolbar-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.search-bar-sm {
  display: flex; align-items: center; gap: 8px;
  background: var(--surface2); border: 1px solid var(--border2); border-radius: 10px; padding: 7px 12px;
}
.search-bar-sm i { font-size: 11px; color: var(--t-muted); }
.search-bar-sm input { border: none; background: transparent; outline: none; font-size: 12px; color: var(--t-primary); width: 180px; }
.status-tabs { display: flex; gap: 4px; }
.status-tab {
  padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700;
  cursor: pointer; transition: all 0.15s; color: var(--t-muted); text-transform: capitalize;
  display: flex; align-items: center; gap: 5px;
}
.status-tab.active { background: var(--indigo); color: white; }
.status-tab:not(.active):hover { background: var(--surface2); color: var(--t-primary); }
.tab-count { font-size: 9px; background: rgba(255,255,255,0.2); padding: 1px 5px; border-radius: 99px; }
.status-tab:not(.active) .tab-count { background: var(--border2); color: var(--t-muted); }
.view-toggle { display: flex; background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px; overflow: hidden; }
.vtb { width: 32px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; transition: all 0.15s; color: var(--t-muted); }
.vtb.active { background: var(--indigo); color: white; }

/* Reports Grid */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 14px;
}
.new-report-card {
  border: 2px dashed var(--border2); border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  text-decoration: none; cursor: pointer; transition: all 0.2s;
  min-height: 240px;
}
.new-report-card:hover { border-color: var(--indigo); background: rgba(99,102,241,0.04); }
.new-card-inner { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.new-card-icon {
  width: 44px; height: 44px; border-radius: 14px;
  background: rgba(99,102,241,0.1); color: var(--indigo);
  display: flex; align-items: center; justify-content: center; font-size: 18px;
  transition: all 0.2s;
}
.new-report-card:hover .new-card-icon { background: var(--indigo); color: white; transform: scale(1.1); }
.new-card-label { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.new-card-sub { font-size: 11px; color: var(--t-muted); }
.report-card {
  background: var(--surface); border: 1px solid var(--border2); border-radius: 16px;
  overflow: hidden; transition: all 0.2s; animation: cardIn 0.35s ease both;
}
.report-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: rgba(99,102,241,0.25); }
.rc-thumb {
  height: 120px; position: relative; overflow: hidden;
  background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.1));
}
.rc-thumb.published { background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(52,211,153,0.06)); }
.rc-thumb.draft { background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(251,191,36,0.06)); }
.rc-thumb-bg {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  font-size: 36px; opacity: 0.2; color: var(--indigo);
}
.rc-overlay {
  position: absolute; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; gap: 8px;
  opacity: 0; transition: opacity 0.2s;
}
.report-card:hover .rc-overlay { opacity: 1; }
.rc-action-btn {
  padding: 6px 12px; border-radius: 8px;
  background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
  color: white; font-size: 11px; font-weight: 700; cursor: pointer;
  text-decoration: none; border: 1px solid rgba(255,255,255,0.25);
  transition: all 0.15s;
}
.rc-action-btn.primary { background: rgba(99,102,241,0.8); border-color: transparent; }
.rc-action-btn:hover { background: rgba(255,255,255,0.25); }
.rc-status-badge {
  position: absolute; top: 8px; right: 8px;
  font-size: 9px; font-weight: 800; padding: 2px 7px; border-radius: 99px; text-transform: capitalize;
}
.rc-status-badge.published { background: rgba(16,185,129,0.8); color: white; }
.rc-status-badge.draft { background: rgba(245,158,11,0.8); color: white; }
.rc-status-badge.archived { background: rgba(148,163,184,0.6); color: white; }
.rc-body { padding: 14px; }
.rc-title { font-size: 13px; font-weight: 700; color: var(--t-primary); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rc-meta { font-size: 10px; color: var(--t-muted); margin-bottom: 12px; }
.rc-actions { display: flex; gap: 2px; }

/* List View */
.reports-list-view { background: var(--surface); border: 1px solid var(--border2); border-radius: 16px; overflow: hidden; }
.rlv-head {
  display: grid; grid-template-columns: 2fr 1fr 80px 1fr 140px;
  padding: 10px 20px; font-size: 10px; font-weight: 800; text-transform: uppercase;
  letter-spacing: 0.06em; color: var(--t-muted); border-bottom: 1px solid var(--border2); background: var(--surface2);
}
.rlv-row {
  display: grid; grid-template-columns: 2fr 1fr 80px 1fr 140px;
  padding: 12px 20px; align-items: center; border-bottom: 1px solid var(--border2); transition: background 0.15s;
}
.rlv-row:last-child { border-bottom: none; }
.rlv-row:hover { background: var(--surface2); }
.rlv-title { display: flex; align-items: center; gap: 10px; }
.empty-list { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px; color: var(--t-muted); }
.empty-list i { font-size: 32px; }
.empty-list p { font-size: 14px; font-weight: 600; }
.status-dropdown {
  position: absolute; right: 0; top: calc(100% + 4px);
  width: 130px; background: var(--surface);
  border: 1px solid var(--border2); border-radius: 12px;
  box-shadow: var(--shadow-lg); z-index: 50; overflow: hidden; padding: 4px;
}
.sd-item {
  display: flex; align-items: center; gap: 8px;
  padding: 7px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;
  color: var(--t-secondary); cursor: pointer; transition: all 0.15s; text-transform: capitalize;
  width: 100%; text-align: left;
}
.sd-item:hover { background: var(--surface2); }
.sd-item.active { color: var(--indigo); }
.sd-dot { width: 7px; height: 7px; border-radius: 50%; }
.sd-dot.published { background: #10b981; }
.sd-dot.draft { background: #f59e0b; }
.sd-dot.archived { background: #94a3b8; }

/* ══ TEMPLATES ══ */
.section-intro { }
.section-desc { font-size: 13px; color: var(--t-muted); }
.templates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
.see-all-card {
  border: 2px dashed var(--border2); border-radius: 16px;
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
  text-decoration: none; padding: 32px; transition: all 0.2s; color: var(--t-muted);
  text-align: center; font-size: 13px; font-weight: 700; min-height: 220px;
}
.see-all-card:hover { border-color: var(--indigo); color: var(--indigo); background: rgba(99,102,241,0.04); }
.tpl-card {
  background: var(--surface); border: 1px solid var(--border2); border-radius: 16px;
  overflow: hidden; transition: all 0.2s; animation: cardIn 0.35s ease both;
}
.tpl-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.tpl-thumb { height: 140px; position: relative; overflow: hidden; }
.tpl-thumb-inner { position: absolute; inset: 0; }
.tpl-overlay {
  position: absolute; inset: 0; background: rgba(0,0,0,0.4);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity 0.2s;
}
.tpl-card:hover .tpl-overlay { opacity: 1; }
.tpl-use-btn {
  padding: 8px 20px; border-radius: 10px;
  background: white; color: #1e293b;
  font-size: 12px; font-weight: 700; text-decoration: none;
}
.tpl-info { padding: 14px; }
.tpl-name { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.tpl-desc { font-size: 11px; color: var(--t-muted); margin-top: 3px; }

/* ══ SETTINGS ══ */
.settings-layout { display: flex; flex-direction: column; gap: 20px; max-width: 700px; }
.settings-card {
  background: var(--surface); border: 1px solid var(--border2); border-radius: 18px;
  overflow: hidden; animation: cardIn 0.35s ease both;
}
.danger-card { border-color: rgba(239,68,68,0.2); }
.settings-card-header {
  display: flex; align-items: center; gap: 14px;
  padding: 20px 24px; border-bottom: 1px solid var(--border2);
}
.settings-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
}
.settings-icon.indigo { background: rgba(99,102,241,0.1); color: var(--indigo); }
.settings-icon.violet { background: rgba(139,92,246,0.1); color: var(--violet); }
.settings-icon.amber { background: rgba(245,158,11,0.1); color: var(--amber); }
.settings-icon.emerald { background: rgba(16,185,129,0.1); color: var(--emerald); }
.settings-icon.red { background: rgba(239,68,68,0.1); color: var(--red); }
.settings-card-title { font-size: 14px; font-weight: 700; color: var(--t-primary); }
.settings-card-sub { font-size: 12px; color: var(--t-muted); margin-top: 2px; }
.settings-form { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; }
.form-row { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--t-muted); }
.form-input {
  background: var(--surface2); border: 1px solid var(--border2); border-radius: 12px;
  padding: 10px 14px; font-size: 13px; color: var(--t-primary); outline: none;
  transition: all 0.2s; width: 100%;
}
.form-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.save-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 20px; border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white; font-size: 13px; font-weight: 700; cursor: pointer;
  transition: all 0.2s; border: none; width: fit-content;
}
.save-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.35); }
.save-btn.violet { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
.save-btn.copied { background: linear-gradient(135deg, #10b981, #34d399); }
.save-btn.mt-3 { margin-top: 12px; }
.theme-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }
.theme-btn {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  padding: 10px; border-radius: 12px; border: 2px solid var(--border2);
  font-size: 12px; font-weight: 700; color: var(--t-secondary); cursor: pointer; transition: all 0.2s;
}
.theme-btn.active { border-color: var(--indigo); color: var(--indigo); background: rgba(99,102,241,0.06); }
.theme-btn:hover:not(.active) { border-color: var(--t-muted); }
.accent-grid { display: flex; gap: 8px; flex-wrap: wrap; }
.accent-dot {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s;
  border: 3px solid transparent;
}
.accent-dot.active { border-color: white; box-shadow: 0 0 0 2px var(--indigo), 0 4px 12px rgba(0,0,0,0.2); transform: scale(1.1); }
.accent-dot:not(.active):hover { transform: scale(1.1); }
.toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.toggle-desc { font-size: 12px; color: var(--t-secondary); }
.toggle-wrap { cursor: pointer; }
.toggle-track {
  width: 44px; height: 24px; border-radius: 99px;
  background: var(--border2); position: relative; transition: background 0.25s;
}
.toggle-track.active { background: var(--indigo); }
.toggle-thumb {
  position: absolute; top: 3px; left: 3px;
  width: 18px; height: 18px; border-radius: 50%;
  background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  transition: transform 0.25s cubic-bezier(0.16,1,0.3,1);
}
.toggle-track.active .toggle-thumb { transform: translateX(20px); }
.preview-box {
  background: var(--surface2); border: 1px solid var(--border2); border-radius: 14px;
  overflow: hidden; margin: 4px 0;
}
.preview-bar { height: 4px; }
.preview-content { padding: 14px; display: flex; align-items: center; justify-content: space-between; }
.preview-title { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.preview-btn { padding: 6px 14px; border-radius: 8px; color: white; font-size: 11px; font-weight: 700; }
.notif-setting-row {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  padding: 12px 0; border-bottom: 1px solid var(--border2);
}
.notif-setting-row:last-of-type { border-bottom: none; }
.ns-label { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.ns-desc { font-size: 11px; color: var(--t-muted); margin-top: 2px; }
.danger-actions { padding: 4px 0; }
.danger-item { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid var(--border2); gap: 16px; }
.danger-item:last-child { border-bottom: none; }
.danger-label { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.danger-desc { font-size: 11px; color: var(--t-muted); margin-top: 2px; }
.danger-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700;
  cursor: pointer; transition: all 0.2s; flex-shrink: 0; border: none;
}
.danger-btn.red { background: rgba(239,68,68,0.1); color: var(--red); border: 1px solid rgba(239,68,68,0.25); }
.danger-btn.red:hover { background: var(--red); color: white; }
.danger-btn.outline { background: var(--surface2); color: var(--t-secondary); border: 1px solid var(--border2); }
.danger-btn.outline:hover { border-color: var(--indigo); color: var(--indigo); }

/* API / Integrations */
.api-key-display { background: var(--surface2); border: 1px solid var(--border2); border-radius: 14px; padding: 16px; }
.api-key-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--t-muted); margin-bottom: 8px; }
.api-key-value { display: flex; align-items: center; gap: 8px; font-family: monospace; font-size: 12px; color: var(--t-primary); }
.api-key-value span { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.integrations-list { display: flex; flex-direction: column; }
.integration-item {
  display: flex; align-items: center; gap: 14px;
  padding: 16px 24px; border-bottom: 1px solid var(--border2); transition: background 0.15s;
}
.integration-item:last-child { border-bottom: none; }
.integration-item:hover { background: var(--surface2); }
.ig-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.ig-icon.emerald { background: rgba(16,185,129,0.1); color: var(--emerald); }
.ig-icon.violet { background: rgba(139,92,246,0.1); color: var(--violet); }
.ig-icon.indigo { background: rgba(99,102,241,0.1); color: var(--indigo); }
.ig-icon.amber { background: rgba(245,158,11,0.1); color: var(--amber); }
.ig-info { flex: 1; }
.ig-name { font-size: 13px; font-weight: 700; color: var(--t-primary); }
.ig-desc { font-size: 11px; color: var(--t-muted); margin-top: 2px; }
.ig-status {
  display: flex; align-items: center; gap: 6px;
  font-size: 11px; font-weight: 700;
  padding: 6px 14px; border-radius: 10px;
  background: var(--surface2); border: 1px solid var(--border2); color: var(--t-muted); cursor: pointer; transition: all 0.2s;
}
.ig-status:hover { border-color: var(--indigo); color: var(--indigo); }
.ig-status.connected { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.25); color: var(--emerald); }

/* ══ MODALS ══ */
.modal-backdrop {
  position: fixed; inset: 0; z-index: 200;
  background: rgba(0,0,0,0.6); backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center; padding: 16px;
}
.modal-box {
  background: var(--surface); border: 1px solid var(--border2); border-radius: 24px;
  box-shadow: 0 24px 80px rgba(0,0,0,0.3);
  width: 100%; max-width: 480px; overflow: hidden;
}
.sm-modal { max-width: 360px; text-align: center; padding: 32px; }
.modal-danger-icon {
  width: 56px; height: 56px; border-radius: 18px;
  background: rgba(239,68,68,0.1); color: var(--red);
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; margin: 0 auto 16px;
}
.modal-title { font-size: 18px; font-weight: 800; color: var(--t-primary); margin-bottom: 8px; }
.modal-title.sm { font-size: 16px; margin-bottom: 2px; }
.modal-sub { font-size: 13px; color: var(--t-muted); margin-bottom: 24px; }
.modal-sub.sm { font-size: 12px; margin-bottom: 0; }
.modal-actions { display: flex; gap: 10px; }
.modal-btn {
  flex: 1; padding: 10px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s;
}
.modal-btn.outline { background: var(--surface2); border: 1px solid var(--border2); color: var(--t-secondary); }
.modal-btn.outline:hover { border-color: var(--indigo); color: var(--indigo); }
.modal-btn.danger { background: var(--red); color: white; border: none; }
.modal-btn.danger:hover { background: #dc2626; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--border2); }
.modal-header-left { display: flex; align-items: center; gap: 12px; }
.modal-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; }
.modal-icon.indigo { background: rgba(99,102,241,0.1); color: var(--indigo); }
.modal-close {
  width: 30px; height: 30px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  color: var(--t-muted); cursor: pointer; transition: all 0.15s;
}
.modal-close:hover { background: var(--surface2); color: var(--t-primary); }
.modal-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; }
.invite-row { display: flex; gap: 8px; }
.invite-row .form-input { min-width: 0; }
.invite-row .save-btn { white-space: nowrap; }
.invite-list { display: flex; flex-direction: column; gap: 6px; }
.invite-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: var(--surface2); border: 1px solid var(--border2); border-radius: 10px; }
.invite-avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--indigo); color: white; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.invite-info { flex: 1; }
.invite-email { font-size: 12px; font-weight: 600; color: var(--t-primary); }
.invite-perm { font-size: 10px; color: var(--t-muted); text-transform: capitalize; }
.share-link-row { display: flex; gap: 8px; }
.public-access-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; background: var(--surface2); border: 1px solid var(--border2); border-radius: 12px; padding: 12px 14px; }
.pa-label { font-size: 12px; font-weight: 700; color: var(--t-primary); }
.pa-desc { font-size: 10px; color: var(--t-muted); margin-top: 2px; }

/* ══ TOAST ══ */
.toast-notification {
  position: fixed; bottom: 24px; right: 24px; z-index: 999;
  display: flex; align-items: center; gap: 10px;
  padding: 14px 20px; border-radius: 14px;
  font-size: 13px; font-weight: 700;
  box-shadow: 0 8px 32px rgba(0,0,0,0.2);
}
.toast-notification.success { background: #10b981; color: white; }
.toast-notification.error { background: var(--red); color: white; }

/* ══ TRANSITIONS ══ */
.sidebar-text-enter-active, .sidebar-text-leave-active { transition: all 0.2s ease; overflow: hidden; white-space: nowrap; }
.sidebar-text-enter-from, .sidebar-text-leave-to { opacity: 0; max-width: 0; }
.sidebar-text-enter-to, .sidebar-text-leave-from { opacity: 1; max-width: 200px; }
.view-enter-active { transition: all 0.3s ease; }
.view-leave-active { transition: all 0.2s ease; }
.view-enter-from { opacity: 0; transform: translateY(10px); }
.view-leave-to { opacity: 0; transform: translateY(-6px); }
.dropdown-enter-active { transition: all 0.15s ease-out; }
.dropdown-leave-active { transition: all 0.1s ease-in; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px) scale(0.97); }
.modal-enter-active { transition: all 0.25s cubic-bezier(0.16,1,0.3,1); }
.modal-leave-active { transition: all 0.15s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-box { transform: scale(0.92) translateY(16px); }
.modal-leave-to .modal-box { transform: scale(0.95); }
.toast-enter-active { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(24px); }

/* ══ DARK MODE ══ */
.dashboard-shell.dark .form-input,
.dashboard-shell.dark .filter-select,
.dashboard-shell.dark .table-search { color-scheme: dark; }

/* ══ SCROLLBAR ══ */
* { scrollbar-width: thin; scrollbar-color: var(--border2) transparent; }
*::-webkit-scrollbar { width: 5px; height: 5px; }
*::-webkit-scrollbar-track { background: transparent; }
*::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 99px; }
*::-webkit-scrollbar-thumb:hover { background: var(--indigo); }

/* ══ RESPONSIVE ══ */
@media (max-width: 1200px) {
  .kpi-grid { grid-template-columns: repeat(2,1fr); }
  .analytics-kpi-row { grid-template-columns: repeat(3,1fr); }
}
@media (max-width: 900px) {
  .charts-row { grid-template-columns: 1fr; }
  .bottom-row { grid-template-columns: 1fr; }
  .welcome-banner { flex-direction: column; gap: 20px; }
  .at-head, .at-row { grid-template-columns: 2fr 1fr 100px; }
  .at-head span:nth-child(3), .at-head span:nth-child(4), .at-row div:nth-child(3), .at-row div:nth-child(4) { display: none; }
}
@media (max-width: 640px) {
  .sidebar { width: 64px; }
  .main-content { }
  .kpi-grid { grid-template-columns: 1fr 1fr; }
  .topbar { padding: 10px 16px; }
  .view-content { padding: 16px; }
  .global-search { min-width: 0; width: 160px; }
  .search-kbd { display: none; }
}
</style>