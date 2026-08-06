<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<template>
  <div
    id="rg-app"
    class="rg-app"
    :class="[
      isDark ? 'rg-dark' : 'rg-light',
      compactMode  ? 'rg-compact'   : '',
      !prefs.animations ? 'rg-no-motion' : '',
    ]"
    :style="liveStyles"
  >
    <!-- ░░ AMBIENT MESH ░░ -->
    <div v-if="effects.mesh" class="rg-mesh" aria-hidden="true">
      <div class="mesh-orb mesh-orb--1"></div>
      <div class="mesh-orb mesh-orb--2"></div>
      <div class="mesh-orb mesh-orb--3"></div>
    </div>

    <!-- ══════════════════════════════════════════════════
         KEYBOARD SHORTCUTS
    ══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="rg-modal">
        <div v-if="showShortcuts" class="rg-overlay" @click.self="showShortcuts=false"
          role="dialog" aria-modal="true" aria-label="Keyboard Shortcuts">
          <div class="rg-modal rg-modal--sm" @click.stop>
            <div class="rg-modal__head">
              <div class="rg-modal__icon"><i class="fa-solid fa-keyboard"></i></div>
              <div>
                <p class="rg-modal__title">Keyboard Shortcuts</p>
                <p class="rg-modal__sub">Power up your workflow</p>
              </div>
              <button class="rg-modal__close" @click="showShortcuts=false" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <div class="sc-list">
              <div v-for="sc in shortcutsList" :key="sc.key" class="sc-row">
                <div class="sc-row__desc">
                  <i :class="sc.icon" class="sc-row__icon"></i>
                  <span>{{ sc.description }}</span>
                </div>
                <kbd class="sc-kbd">{{ sc.key }}</kbd>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════════════
         SETTINGS MODAL
    ══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="rg-modal">
        <div v-if="settingsOpen" class="rg-overlay" @click.self="settingsOpen=false"
          role="dialog" aria-modal="true" aria-label="Settings">
          <div class="rg-modal rg-modal--lg" @click.stop>
            <div class="rg-modal__head">
              <div class="rg-modal__icon"><i class="fa-solid fa-sliders"></i></div>
              <div>
                <p class="rg-modal__title">Workspace Settings</p>
                <p class="rg-modal__sub">Every pixel, your way</p>
              </div>
              <button class="rg-modal__close" @click="settingsOpen=false" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="s-body">
              <!-- Tab Rail -->
              <nav class="s-rail" role="tablist">
                <button v-for="tab in settingsTabs" :key="tab.id"
                  class="s-tab" :class="{ 'is-on': activeSettings===tab.id }"
                  @click="activeSettings=tab.id"
                  role="tab" :aria-selected="activeSettings===tab.id">
                  <span class="s-tab__icon"><i :class="tab.icon"></i></span>
                  <span class="s-tab__label">{{ tab.label }}</span>
                  <span v-if="tab.badge" class="s-tab__badge">{{ tab.badge }}</span>
                </button>
              </nav>

              <!-- Panels -->
              <div class="s-panels">

                <!-- ── APPEARANCE ── -->
                <div v-show="activeSettings==='appearance'" class="s-panel">

                  <!-- Theme Mode -->
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-circle-half-stroke"></i> Theme Mode</p>
                    <div class="theme-row">
                      <button v-for="t in themes" :key="t.value"
                        class="theme-card" :class="{ 'is-on': selectedTheme===t.value }"
                        @click="applyTheme(t.value)">
                        <div class="theme-card__mock" :class="`mock--${t.value}`">
                          <div class="mock-sb"></div>
                          <div class="mock-body">
                            <div class="mock-topbar"></div>
                            <div class="mock-content">
                              <div class="mock-block"></div>
                              <div class="mock-block mock-block--sm"></div>
                            </div>
                          </div>
                        </div>
                        <div class="theme-card__label">
                          <i :class="t.icon"></i><span>{{ t.label }}</span>
                        </div>
                        <span v-if="selectedTheme===t.value" class="theme-card__check">
                          <i class="fa-solid fa-check"></i>
                        </span>
                      </button>
                    </div>
                  </div>

                  <!-- Accent Color -->
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-droplet"></i> Accent Color</p>
                    <div class="accent-swatches">
                      <button v-for="c in accentColors" :key="c.key"
                        class="swatch" :class="{ 'is-on': accentKey===c.key }"
                        :style="{ '--sw-col': c.value }"
                        :title="c.name" :aria-label="c.name"
                        @click="setAccent(c.key)">
                        <i v-if="accentKey===c.key" class="fa-solid fa-check"></i>
                      </button>
                    </div>
                    <!-- Live preview -->
                    <div class="accent-preview">
                      <span class="ap-tag">Live Preview</span>
                      <button class="ap-btn" tabindex="-1">Primary Button</button>
                      <span class="ap-link">Accent Link →</span>
                      <span class="ap-badge">Badge</span>
                      <span class="ap-dot"></span>
                    </div>
                  </div>

                  <!-- Typography -->
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-font"></i> Typography</p>
                    <div class="s-2col">
                      <div class="s-field">
                        <label class="s-label">Font Family</label>
                        <select v-model="currentFont" @change="onFontChange" class="s-select">
                          <option value="'Sora', sans-serif">Sora — Modern</option>
                          <option value="'Inter', sans-serif">Inter — Clean</option>
                          <option value="'DM Sans', sans-serif">DM Sans — Neutral</option>
                          <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                          <option value="'Outfit', sans-serif">Outfit — Friendly</option>
                          <option value="'Poppins', sans-serif">Poppins — Round</option>
                          <option value="'IBM Plex Sans', sans-serif">IBM Plex — Technical</option>
                          <option value="Georgia, serif">Georgia — Editorial</option>
                        </select>
                        <!-- live font preview — uses the currently selected font -->
                        <div class="font-preview" :style="{ fontFamily: currentFont }">
                          The quick brown fox jumps over the lazy dog
                        </div>
                      </div>
                      <div class="s-field">
                        <label class="s-label">Font Size <span class="s-val">{{ currentFontSize }}px</span></label>
                        <input type="range" v-model.number="currentFontSize"
                          min="12" max="18" step="1" class="s-range"
                          @input="onFontSizeChange" />
                        <div class="range-ticks">
                          <span v-for="n in [12,13,14,15,16,17,18]" :key="n"
                            :class="{ 'is-active': currentFontSize===n }">{{ n }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Shape & Layout -->
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-vector-square"></i> Shape &amp; Layout</p>
                    <div class="s-2col">
                      <div class="s-field">
                        <label class="s-label">Border Radius <span class="s-val">{{ cardRad }}px</span></label>
                        <input type="range" v-model.number="cardRad"
                          min="0" max="24" step="2" class="s-range"
                          @input="onRadiusChange" />
                        <div class="radius-preview">
                          <div class="rp-box" :style="{ borderRadius: cardRad+'px' }">Rr</div>
                          <div class="rp-box rp-box--sm" :style="{ borderRadius: Math.max(0,cardRad-4)+'px' }">Btn</div>
                          <div class="rp-box rp-box--pill" :style="{ borderRadius: Math.min(99,cardRad*2)+'px' }">Pill</div>
                        </div>
                      </div>
                      <div class="s-field">
                        <label class="s-label">Sidebar Width</label>
                        <div class="sw-btns">
                          <button v-for="sw in sidebarWidths" :key="sw.key"
                            class="sw-btn" :class="{ 'is-on': sidebarWidthKey===sw.key }"
                            @click="setSidebarWidth(sw.key)">
                            <div class="sw-btn__mock">
                              <div class="sw-btn__sb" :style="{ width: sw.prev+'px' }"></div>
                              <div class="sw-btn__body"></div>
                            </div>
                            <span>{{ sw.label }}</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Visual Effects -->
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-wand-magic-sparkles"></i> Visual Effects</p>
                    <div class="fx-row">
                      <button v-for="ef in effectOptions" :key="ef.key"
                        class="fx-btn" :class="{ 'is-on': effects[ef.key] }"
                        @click="effects[ef.key]=!effects[ef.key]; persistEffects()">
                        <i :class="ef.icon"></i>
                        <span>{{ ef.label }}</span>
                        <span class="fx-dot" :class="{ 'is-on': effects[ef.key] }"></span>
                      </button>
                    </div>
                  </div>

                  <button @click="saveAppearance" class="s-save">
                    <i class="fa-solid fa-floppy-disk"></i> Save Appearance
                  </button>
                </div>

                <!-- ── PREFERENCES ── -->
                <div v-show="activeSettings==='preferences'" class="s-panel">
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-gauge-high"></i> Interface</p>
                    <div class="pref-list">
                      <div v-for="p in prefOptions" :key="p.key" class="pref-row">
                        <div class="pref-row__left">
                          <div class="pref-row__icon"><i :class="p.icon"></i></div>
                          <div>
                            <p class="pref-row__title">{{ p.label }}</p>
                            <p class="pref-row__desc">{{ p.desc }}</p>
                          </div>
                        </div>
                        <button class="rg-toggle" :class="{ 'is-on': prefs[p.key] }"
                          @click="prefs[p.key]=!prefs[p.key]"
                          role="switch" :aria-checked="String(prefs[p.key])">
                          <span class="rg-toggle__knob"></span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-chart-bar"></i> Dashboard</p>
                    <div class="pref-list">
                      <div v-for="p in dashPrefOptions" :key="p.key" class="pref-row">
                        <div class="pref-row__left">
                          <div class="pref-row__icon"><i :class="p.icon"></i></div>
                          <div>
                            <p class="pref-row__title">{{ p.label }}</p>
                            <p class="pref-row__desc">{{ p.desc }}</p>
                          </div>
                        </div>
                        <button class="rg-toggle" :class="{ 'is-on': dashPrefs[p.key] }"
                          @click="dashPrefs[p.key]=!dashPrefs[p.key]"
                          role="switch" :aria-checked="String(dashPrefs[p.key])">
                          <span class="rg-toggle__knob"></span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-keyboard"></i> Shortcuts</p>
                    <button @click="showShortcuts=true; settingsOpen=false" class="shortcut-teaser">
                      <div class="pref-row__left">
                        <div class="pref-row__icon"><i class="fa-solid fa-keyboard"></i></div>
                        <div>
                          <p class="pref-row__title">Keyboard Shortcuts</p>
                          <p class="pref-row__desc">{{ shortcutsList.length }} shortcuts available</p>
                        </div>
                      </div>
                      <span class="teaser-cta">View All <i class="fa-solid fa-arrow-right"></i></span>
                    </button>
                  </div>

                  <button @click="savePrefs" class="s-save">
                    <i class="fa-solid fa-floppy-disk"></i> Save Preferences
                  </button>
                </div>

                <!-- ── NOTIFICATIONS ── -->
                <div v-show="activeSettings==='notifications'" class="s-panel">
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-bell"></i> Alert Channels</p>
                    <div class="pref-list">
                      <div v-for="nt in notifTypeOptions" :key="nt.key" class="pref-row">
                        <div class="pref-row__left">
                          <div class="pref-row__icon" :style="{ background:nt.color+'20', color:nt.color }">
                            <i :class="nt.icon"></i>
                          </div>
                          <div>
                            <p class="pref-row__title">{{ nt.label }}</p>
                            <p class="pref-row__desc">{{ nt.desc }}</p>
                          </div>
                        </div>
                        <button class="rg-toggle" :class="{ 'is-on': notifPrefs[nt.key] }"
                          @click="notifPrefs[nt.key]=!notifPrefs[nt.key]"
                          role="switch" :aria-checked="String(notifPrefs[nt.key])">
                          <span class="rg-toggle__knob"></span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-clock"></i> Delivery Timing</p>
                    <div class="s-field">
                      <label class="s-label">Notification Delay</label>
                      <div class="delay-pills">
                        <button v-for="d in delayOptions" :key="d.val"
                          class="delay-pill" :class="{ 'is-on': notifDelay===d.val }"
                          @click="notifDelay=d.val; persistNotifDelay()">{{ d.label }}</button>
                      </div>
                    </div>
                    <div class="s-field" style="margin-top:10px">
                      <label class="s-label">Polling Interval <span class="s-val">{{ pollingInterval }}s</span></label>
                      <input type="range" v-model.number="pollingInterval"
                        min="15" max="120" step="15" class="s-range"
                        @change="onPollingChange" />
                    </div>
                  </div>

                  <div class="notif-tip">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Email preferences are managed in
                      <Link :href="route('profile.edit')" class="notif-tip__link" @click="settingsOpen=false">your profile</Link>.
                    </p>
                  </div>

                  <button @click="saveNotifPrefs" class="s-save">
                    <i class="fa-solid fa-floppy-disk"></i> Save Notification Settings
                  </button>
                </div>

                <!-- ── SECURITY ── -->
                <div v-show="activeSettings==='security'" class="s-panel">
                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-shield-halved"></i> Security Options</p>
                    <div class="pref-list">
                      <div v-for="p in securityOptions" :key="p.key" class="pref-row">
                        <div class="pref-row__left">
                          <div class="pref-row__icon" :style="{ background:p.color+'20', color:p.color }">
                            <i :class="p.icon"></i>
                          </div>
                          <div>
                            <p class="pref-row__title">{{ p.label }}</p>
                            <p class="pref-row__desc">{{ p.desc }}</p>
                          </div>
                        </div>
                        <button class="rg-toggle" :class="{ 'is-on': secPrefs[p.key] }"
                          @click="secPrefs[p.key]=!secPrefs[p.key]"
                          role="switch" :aria-checked="String(secPrefs[p.key])">
                          <span class="rg-toggle__knob"></span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-circle-user"></i> Account</p>
                    <Link :href="route('profile.edit')" class="acct-link" @click="settingsOpen=false">
                      <i class="fa-solid fa-user-pen acct-link__icon"></i>
                      <div>
                        <p class="pref-row__title">Edit Profile</p>
                        <small class="pref-row__desc">Update name, email &amp; password</small>
                      </div>
                      <i class="fa-solid fa-chevron-right acct-link__arrow"></i>
                    </Link>
                  </div>

                  <div class="s-group">
                    <p class="s-group__label"><i class="fa-solid fa-trash-can"></i> Data</p>
                    <button @click="clearLocalStorage" class="danger-btn">
                      <i class="fa-solid fa-rotate-left"></i> Reset All Settings to Default
                    </button>
                  </div>
                </div>

                <!-- ── ABOUT ── -->
                <div v-show="activeSettings==='about'" class="s-panel">
                  <div class="about-wrap">
                    <div class="about-logo"><i class="fa-solid fa-chart-line"></i></div>
                    <h3 class="about-name">ReportGen Enterprise</h3>
                    <p class="about-tagline">The professional reporting platform built for teams.</p>
                    <div class="about-badges">
                      <span>v2.0.0</span><span>Laravel 12</span><span>Vue 3</span><span>Inertia.js</span>
                    </div>
                  </div>
                  <div class="about-grid">
                    <div v-for="ab in aboutCards" :key="ab.label" class="about-card">
                      <i :class="ab.icon" class="about-card__icon"></i>
                      <span class="about-card__label">{{ ab.label }}</span>
                    </div>
                  </div>
                  <p class="about-copy">&copy; {{ currentYear }} ReportGen Enterprise. All rights reserved.</p>
                </div>

              </div><!-- /s-panels -->
            </div><!-- /s-body -->
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════════════
         SEARCH PALETTE
    ══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="rg-pal">
        <div v-if="showSearch" class="rg-overlay rg-overlay--top" @click.self="showSearch=false"
          role="dialog" aria-modal="true" aria-label="Search">
          <div class="pal-box">
            <!-- Input row -->
            <div class="pal-input-row">
              <i class="fa-solid fa-magnifying-glass pal-icon"></i>
              <input ref="searchInputRef" v-model="searchQuery" type="text"
                placeholder="Search reports, tasks, users, pages…"
                class="pal-input" autocomplete="off"
                @keydown.escape="showSearch=false"
                @keydown.down.prevent="searchIdx=Math.min(searchIdx+1,filteredSearch.length-1)"
                @keydown.up.prevent="searchIdx=Math.max(searchIdx-1,0)"
                @keydown.enter="goToSearchResult(filteredSearch[searchIdx])" />
              <kbd class="pal-esc">ESC</kbd>
            </div>
            <!-- Category chips (empty state) -->
            <div v-if="!searchQuery" class="pal-cats">
              <button v-for="cat in searchCategories" :key="cat.label"
                class="pal-cat" @click="searchQuery=cat.query">
                <i :class="cat.icon"></i> {{ cat.label }}
              </button>
            </div>
            <!-- Results -->
            <div v-if="searchQuery" class="pal-results">
              <div v-if="!filteredSearch.length" class="pal-empty">
                <i class="fa-solid fa-face-sad-tear"></i>
                <p>No results for "<strong>{{ searchQuery }}</strong>"</p>
              </div>
              <div v-for="(r,i) in filteredSearch" :key="r.id"
                class="pal-item" :class="{ 'is-on': searchIdx===i }"
                @click="goToSearchResult(r)" @mouseenter="searchIdx=i">
                <div class="pal-item__icon" :style="{ background:r.color+'22', color:r.color }">
                  <i :class="r.icon"></i>
                </div>
                <div class="pal-item__info">
                  <span class="pal-item__title">{{ r.title }}</span>
                  <span class="pal-item__sub">{{ r.subtitle }}</span>
                </div>
                <span class="pal-item__type">{{ r.type }}</span>
                <i v-if="searchIdx===i" class="fa-solid fa-arrow-turn-down-left pal-item__enter"></i>
              </div>
            </div>
            <div class="pal-footer">
              <span><kbd>↑↓</kbd> Navigate</span>
              <span><kbd>↵</kbd> Select</span>
              <span><kbd>ESC</kbd> Dismiss</span>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══════════════════════════════════════════════════
         MOBILE OVERLAY
    ══════════════════════════════════════════════════ -->
    <Transition name="rg-fade">
      <div v-if="mobileMenuOpen" class="rg-mob-veil" @click="mobileMenuOpen=false" aria-hidden="true"></div>
    </Transition>

    <!-- ══════════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════════ -->
    <aside ref="sidebarRef"
      class="rg-sidebar"
      :class="[sidebarCollapsed ? 'is-coll' : '', mobileMenuOpen ? 'is-open' : '']"
      role="navigation" aria-label="Main navigation">

      <div class="sb-glow" aria-hidden="true"></div>

      <!-- Brand -->
      <div class="sb-brand">
        <div class="sb-logo" :title="sidebarCollapsed ? 'ReportGen' : undefined">
          <i class="fa-solid fa-chart-line"></i>
          <span class="sb-logo__pulse" :class="{ 'is-live': hasUnread }" aria-hidden="true"></span>
        </div>
        <Transition name="rg-fade-x">
          <div v-if="!sidebarCollapsed" class="sb-wordmark">
            <span class="sb-wordmark__name">ReportGen</span>
            <span class="sb-wordmark__edition">Enterprise</span>
          </div>
        </Transition>
        <button v-if="!sidebarCollapsed" class="sb-coll-btn"
          @click="toggleSidebar" aria-label="Collapse sidebar">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
      </div>

      <!-- User card -->
      <div class="sb-user">
        <div class="sb-avatar" :title="sidebarCollapsed ? authUser?.name : undefined">
          {{ userInitial }}
          <span class="sb-avatar__online" aria-hidden="true"></span>
        </div>
        <Transition name="rg-fade-x">
          <div v-if="!sidebarCollapsed" class="sb-user__info">
            <span class="sb-user__name">{{ authUser?.name }}</span>
            <span class="sb-user__email">{{ authUser?.email }}</span>
            <div class="sb-badges">
              <span class="sb-badge sb-badge--pro">Pro</span>
              <span v-if="authUser?.is_premium" class="sb-badge sb-badge--gold">
                <i class="fa-solid fa-crown"></i> Premium
              </span>
              <span v-if="isImpersonating" class="sb-badge sb-badge--red">
                <i class="fa-solid fa-mask"></i> Impersonating
              </span>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Nav -->
      <nav class="sb-nav" role="navigation">

        <!-- ══ OVERVIEW ══ -->
        <div class="sb-sect">
          <Transition name="rg-fade-x">
            <p v-if="!sidebarCollapsed" class="sb-sect__lbl">
              <i class="fa-solid fa-house-chimney sb-sect__lbl-ic"></i>Overview
            </p>
          </Transition>
          <NavItem
            :href="route('dashboard')"
            icon="fa-solid fa-gauge-high"
            label="Dashboard"
            :active="isRoute('dashboard')"
            :collapsed="sidebarCollapsed"
          />
        </div>

        <!-- ══ REPORTS ══ -->
        <div class="sb-sect">
          <Transition name="rg-fade-x">
            <p v-if="!sidebarCollapsed" class="sb-sect__lbl">
              <i class="fa-solid fa-file-lines sb-sect__lbl-ic"></i>Reports
            </p>
          </Transition>
          <div v-if="sidebarCollapsed" class="sb-hr" aria-hidden="true"></div>

          <!-- Reports group -->
          <NavDropdown
            icon="fa-solid fa-file-lines"
            label="Reports"
            :open="dropdowns.reports"
            :active="isReportsSection"
            :collapsed="sidebarCollapsed"
            @toggle="toggleDropdown('reports')"
          >
            <template #default>
              <!-- All Reports -->
              <NavSubItem
                :href="route('reports.index')"
                icon="fa-solid fa-list-ul"
                label="My Reports"
                :active="isRoute('reports.index')"
              />
              <!-- Create Report -->
              <NavSubItem
                :href="route('reports.create')"
                icon="fa-solid fa-plus"
                label="Create Report"
                :active="isRoute('reports.create')"
                shortcut="⌘N"
              />
              <!-- Shared with Me -->
              <NavSubItem
                :href="route('reports.assigned')"
                icon="fa-solid fa-share-alt"
                label="Shared with Me"
                :active="isRoute('reports.assigned')"
              >
                <span
                  v-if="pageNotifications?.assigned_reports > 0"
                  class="sub-cnt sub-cnt--pulse"
                >{{ pageNotifications.assigned_reports }}</span>
              </NavSubItem>
              <!-- Trash -->
              <NavSubItem
                :href="route('reports.trashed')"
                icon="fa-solid fa-trash-can"
                label="Trash"
                :active="isRoute('reports.trashed')"
              />
            </template>
          </NavDropdown>

          <!-- Templates -->
          <NavItem
            :href="route('templates.index')"
            icon="fa-solid fa-layer-group"
            label="Templates"
            :active="isRoute('templates.index')"
            :collapsed="sidebarCollapsed"
          />
        </div>

        <!-- ══ TASKS ══ -->
        <div class="sb-sect">
          <Transition name="rg-fade-x">
            <p v-if="!sidebarCollapsed" class="sb-sect__lbl">
              <i class="fa-solid fa-list-check sb-sect__lbl-ic"></i>Tasks
            </p>
          </Transition>
          <div v-if="sidebarCollapsed" class="sb-hr" aria-hidden="true"></div>

          <!-- My Tasks (all authenticated users) -->
          <NavItem
            :href="route('admin.tasks.my')"
            icon="fa-solid fa-list-check"
            label="My Tasks"
            :active="isRoute('admin.tasks.my')"
            :collapsed="sidebarCollapsed"
            :badge="pageNotifications?.pending_tasks > 0 ? pageNotifications.pending_tasks : null"
          />

          <!-- All Tasks (admin | manager) -->
          <template v-if="isAdminOrManager">
            <NavItem
              :href="route('admin.tasks.index')"
              icon="fa-solid fa-table-list"
              label="All Tasks"
              :active="isRoute('admin.tasks.index')"
              :collapsed="sidebarCollapsed"
            />
          </template>
        </div>

        <!-- ══ ADMINISTRATION (admin | manager) ══ -->
        <template v-if="isAdminOrManager">
          <div class="sb-sect">
            <Transition name="rg-fade-x">
              <p v-if="!sidebarCollapsed" class="sb-sect__lbl sb-sect__lbl--admin">
                <i class="fa-solid fa-shield-halved sb-sect__lbl-ic"></i>Administration
              </p>
            </Transition>
            <div v-if="sidebarCollapsed" class="sb-hr" aria-hidden="true"></div>

            <!-- ── People & Work ── -->
            <NavDropdown
              icon="fa-solid fa-users-gear"
              label="People & Work"
              :open="dropdowns.people"
              :active="isPeopleSection"
              :collapsed="sidebarCollapsed"
              @toggle="toggleDropdown('people')"
            >
              <template #default>
                <div v-if="!sidebarCollapsed" class="nav-sub-group-label">Users</div>
                <NavSubItem
                  :href="route('admin.users.index')"
                  icon="fa-solid fa-users"
                  label="User Management"
                  :active="isRoute('admin.users.index')"
                />
                <div v-if="!sidebarCollapsed" class="nav-sub-group-label">Work</div>
                <NavSubItem
                  :href="route('admin.tasks.index')"
                  icon="fa-solid fa-tasks"
                  label="All Tasks"
                  :active="isRoute('admin.tasks.index')"
                />
                <NavSubItem
                  :href="route('admin.report-assignments.index')"
                  icon="fa-solid fa-share-nodes"
                  label="Report Assignments"
                  :active="isRoute('admin.report-assignments.index')"
                />
              </template>
            </NavDropdown>

            <!-- ── Insights ── -->
            <NavDropdown
              icon="fa-solid fa-chart-pie"
              label="Insights"
              :open="dropdowns.insights"
              :active="isInsightsSection"
              :collapsed="sidebarCollapsed"
              @toggle="toggleDropdown('insights')"
            >
              <template #default>
                <div v-if="!sidebarCollapsed" class="nav-sub-group-label">Analytics</div>
                <NavSubItem
                  :href="route('admin.analytics.index')"
                  icon="fa-solid fa-chart-bar"
                  label="Analytics Overview"
                  :active="isRoute('admin.analytics.index')"
                />
                <NavSubItem
                  :href="route('admin.analytics.reports')"
                  icon="fa-solid fa-file-chart-column"
                  label="Report Analytics"
                  :active="isRoute('admin.analytics.reports')"
                />
                <NavSubItem
                  :href="route('admin.analytics.users')"
                  icon="fa-solid fa-user-chart"
                  label="User Analytics"
                  :active="isRoute('admin.analytics.users')"
                />
                <div v-if="!sidebarCollapsed" class="nav-sub-group-label">Logs</div>
                <NavSubItem
                  :href="route('admin.activities.index')"
                  icon="fa-solid fa-clock-rotate-left"
                  label="Activity Logs"
                  :active="isRoute('admin.activities.index')"
                />
              </template>
            </NavDropdown>

            <!-- ── Roles & Access (admin only) ── -->
            <template v-if="isAdmin">
              <NavDropdown
                icon="fa-solid fa-shield-halved"
                label="Roles & Access"
                :open="dropdowns.roles"
                :active="isRolesSection"
                :collapsed="sidebarCollapsed"
                @toggle="toggleDropdown('roles')"
              >
                <template #default>
                  <!-- Role-permission mini-panel (expanded only) -->
                  <template v-if="!sidebarCollapsed">
                    <div class="nav-sub-group-label">Access Control</div>

                    <!-- Inline permission pills strip -->
                    <div class="roles-mini-panel">
                      <div class="roles-mini-panel__row">
                        <div v-for="rp in rolesPreview" :key="rp.name" class="rmp-chip">
                          <span class="rmp-chip__dot" :style="{ background: rp.color }"></span>
                          <span class="rmp-chip__label">{{ rp.name }}</span>
                          <span class="rmp-chip__count">{{ rp.users }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="nav-sub-group-label" style="margin-top:6px">Manage</div>
                  </template>

                  <NavSubItem
                    :href="route('admin.roles.index')"
                    icon="fa-solid fa-shield"
                    label="Roles"
                    :active="isRoute('admin.roles.index')"
                  />
                  <NavSubItem
                    :href="route('admin.roles.permissions')"
                    icon="fa-solid fa-key"
                    label="Permissions"
                    :active="isRoute('admin.roles.permissions')"
                  />

                  <!-- Quick-assign permission prompt (admin only, expanded) -->
                  <template v-if="!sidebarCollapsed">
                    <button class="roles-quick-assign" @click="openSettings('security')">
                      <i class="fa-solid fa-user-plus"></i>
                      <span>Assign Permissions</span>
                      <i class="fa-solid fa-arrow-right roles-quick-assign__arr"></i>
                    </button>
                  </template>
                </template>
              </NavDropdown>
            </template>
          </div>
        </template>

        <!-- ══ ACCOUNT ══ -->
        <div class="sb-sect">
          <Transition name="rg-fade-x">
            <p v-if="!sidebarCollapsed" class="sb-sect__lbl">
              <i class="fa-solid fa-circle-user sb-sect__lbl-ic"></i>Account
            </p>
          </Transition>
          <div v-if="sidebarCollapsed" class="sb-hr" aria-hidden="true"></div>

          <!-- Notifications -->
          <NavItem
            :href="route('notifications.index')"
            icon="fa-solid fa-bell"
            label="Notifications"
            :active="isRoute('notifications.index')"
            :collapsed="sidebarCollapsed"
            :badge="unreadCount > 0 ? unreadCount : null"
          />

          <!-- Settings dropdown -->
          <NavDropdown
            icon="fa-solid fa-gear"
            label="Settings"
            :open="dropdowns.settings"
            :active="isSettingsSection"
            :collapsed="sidebarCollapsed"
            @toggle="toggleDropdown('settings')"
          >
            <template #default>
              <div v-if="!sidebarCollapsed" class="nav-sub-group-label">Appearance</div>
              <button @click="openSettings('appearance')"    class="sb-sub-btn"><i class="fa-solid fa-palette"></i><span>Theme & Colors</span></button>
              <button @click="openSettings('preferences')"   class="sb-sub-btn"><i class="fa-solid fa-sliders"></i><span>Preferences</span></button>
              <div v-if="!sidebarCollapsed" class="nav-sub-group-label">System</div>
              <button @click="openSettings('notifications')" class="sb-sub-btn"><i class="fa-solid fa-bell"></i><span>Notifications</span></button>
              <button @click="openSettings('security')"      class="sb-sub-btn"><i class="fa-solid fa-shield-halved"></i><span>Security</span></button>
              <div class="sb-sub-sep" aria-hidden="true"></div>
              <NavSubItem
                :href="route('profile.edit')"
                icon="fa-solid fa-user-pen"
                label="Edit Profile"
                :active="isRoute('profile.edit')"
              />
            </template>
          </NavDropdown>
        </div>

        <!-- ══ IMPERSONATION BANNER ══ -->
        <Transition name="rg-sub">
          <div v-if="isImpersonating" class="sb-impersonate">
            <div class="sbi-head">
              <span class="sbi-head__dot"></span>
              <i class="fa-solid fa-mask"></i>
              <span>Impersonating User</span>
            </div>
            <p class="sbi-name">{{ authUser?.name }}</p>
            <Link
              :href="route('admin.users.stop-impersonate')"
              method="post"
              as="button"
              class="sbi-stop"
            >
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
              Stop Impersonating
            </Link>
          </div>
        </Transition>

      </nav>

      <!-- Sidebar footer -->
      <div class="sb-foot">
        <button v-if="sidebarCollapsed" class="sb-expand-btn"
          @click="toggleSidebar" aria-label="Expand sidebar">
          <i class="fa-solid fa-chevron-right"></i>
        </button>

        <button class="sb-foot-btn" :class="{ 'is-ctr': sidebarCollapsed }"
          @click="toggleDark"
          :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
          <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="sb-foot-btn__ic"></i>
          <Transition name="rg-fade-x">
            <span v-if="!sidebarCollapsed" class="sb-foot-btn__txt">
              {{ isDark ? 'Light Mode' : 'Dark Mode' }}
            </span>
          </Transition>
        </button>

        <Link :href="route('logout')" method="post" as="button"
          class="sb-foot-btn sb-foot-btn--out"
          :class="{ 'is-ctr': sidebarCollapsed }" title="Sign Out">
          <i class="fa-solid fa-arrow-right-from-bracket sb-foot-btn__ic"></i>
          <Transition name="rg-fade-x">
            <span v-if="!sidebarCollapsed" class="sb-foot-btn__txt">Sign Out</span>
          </Transition>
        </Link>
      </div>
    </aside>

    <!-- ══════════════════════════════════════════════════
         MAIN AREA
    ══════════════════════════════════════════════════ -->
    <div class="rg-main" :class="sidebarCollapsed ? 'is-coll' : ''">

      <!-- ── TOPBAR ── -->
      <header class="rg-topbar" :class="{ 'is-sticky': prefs.stickyHeader }" role="banner">
        <div class="tb-left">
          <button class="tb-ham" @click="mobileMenuOpen=!mobileMenuOpen"
            :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
            :aria-expanded="mobileMenuOpen">
            <i :class="mobileMenuOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'"></i>
          </button>
          <!-- Desktop breadcrumb -->
          <div class="tb-bc">
            <slot name="header">
              <span class="tb-bc__dot" aria-hidden="true"></span>
              <span class="tb-bc__title">{{ pageTitle }}</span>
            </slot>
          </div>
        </div>

        <div class="tb-right">
          <!-- Search -->
          <button class="tb-search" @click="openSearch" aria-label="Search (Ctrl+K)">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span class="tb-search__hint">Search anything…</span>
            <kbd class="tb-search__kbd">⌘K</kbd>
          </button>

          <!-- Notifications -->
          <div class="tb-notif" data-notif-wrapper>
            <button class="tb-icn-btn" @click="toggleNotifications"
              :aria-expanded="showNotifications"
              :aria-label="`Notifications${unreadCount > 0 ? ` (${unreadCount} unread)` : ''}`">
              <i class="fa-solid fa-bell"></i>
              <span v-if="unreadCount > 0" class="tb-dot" aria-hidden="true">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
              </span>
            </button>
            <Transition name="rg-drop">
              <div v-if="showNotifications" class="nd-box" role="region" aria-label="Notifications">
                <div class="nd-head">
                  <div>
                    <p class="nd-head__title">Notifications</p>
                    <p class="nd-head__sub">{{ unreadCount }} unread</p>
                  </div>
                  <div class="nd-head__actions">
                    <button v-if="unreadCount>0" @click="markAllRead" class="nd-mark-all">Mark all read</button>
                    <button @click="fetchNotifications" class="nd-refresh"
                      :class="{ 'is-spin': loadingNotifications }" aria-label="Refresh">
                      <i class="fa-solid fa-rotate"></i>
                    </button>
                  </div>
                </div>
                <div class="nd-list">
                  <div v-if="loadingNotifications" class="nd-state">
                    <i class="fa-solid fa-spinner fa-spin nd-state__ico"></i><p>Loading…</p>
                  </div>
                  <div v-else-if="notifError && !notifList.length" class="nd-state">
                    <i class="fa-solid fa-triangle-exclamation nd-state__ico nd-state__ico--err"></i>
                    <p>{{ notifError }}</p>
                    <button @click="fetchNotifications" class="nd-retry">Retry</button>
                  </div>
                  <div v-else-if="!notifList.length" class="nd-state">
                    <i class="fa-solid fa-bell-slash nd-state__ico"></i>
                    <p>All caught up!</p>
                  </div>
                  <div v-for="n in notifList" :key="n.id"
                    class="nd-item" :class="{ 'is-unread': !n.read_at }"
                    @click="handleNotifClick(n)" role="button" tabindex="0"
                    @keydown.enter="handleNotifClick(n)">
                    <div class="nd-item__ico" :class="{ 'is-unread': !n.read_at }">
                      <i :class="n.icon||'fa-solid fa-bell'" :style="{ color: n.color||'var(--ac)' }"></i>
                    </div>
                    <div class="nd-item__body">
                      <p class="nd-item__title">{{ n.title }}</p>
                      <p class="nd-item__msg">{{ n.message }}</p>
                      <div class="nd-item__meta">
                        <span>{{ n.time_ago || formatTimeAgo(n.created_at) }}</span>
                        <span class="nd-tag" :class="ntClass(n.type)">{{ ntLabel(n.type) }}</span>
                      </div>
                    </div>
                    <div v-if="!n.read_at" class="nd-item__unread-dot" aria-hidden="true"></div>
                  </div>
                </div>
                <div class="nd-foot">
                  <Link :href="route('notifications.index')" class="nd-view-all"
                    @click="showNotifications=false">
                    View all <i class="fa-solid fa-arrow-right"></i>
                  </Link>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Quick Actions -->
          <div class="tb-qa" data-qa-wrapper>
            <button class="tb-icn-btn" @click="showQuickActions=!showQuickActions"
              :aria-expanded="showQuickActions" aria-label="Quick actions">
              <i class="fa-solid fa-bolt"></i>
            </button>
            <Transition name="rg-drop">
              <div v-if="showQuickActions" class="qa-box" role="menu">
                <p class="qa-box__lbl">Quick Actions</p>
                <Link :href="route('reports.create')"       class="qa-item" role="menuitem">
                  <span class="qa-item__ico qa-item__ico--ac"><i class="fa-solid fa-plus"></i></span>
                  <div><b>New Report</b><small>Create from scratch</small></div>
                </Link>
                <Link :href="route('reports.index')"        class="qa-item" role="menuitem">
                  <span class="qa-item__ico" style="background:#dbeafe;color:#2563eb"><i class="fa-solid fa-file-lines"></i></span>
                  <div><b>All Reports</b><small>Browse reports</small></div>
                </Link>
                <Link :href="route('admin.tasks.my')"       class="qa-item" role="menuitem">
                  <span class="qa-item__ico" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-list-check"></i></span>
                  <div><b>My Tasks</b><small>{{ pageNotifications?.pending_tasks ?? 0 }} pending</small></div>
                </Link>
                <Link :href="route('templates.index')"      class="qa-item" role="menuitem">
                  <span class="qa-item__ico" style="background:#ede9fe;color:#7c3aed"><i class="fa-solid fa-layer-group"></i></span>
                  <div><b>Templates</b><small>Start from a template</small></div>
                </Link>
                <Link :href="route('notifications.index')"  class="qa-item" role="menuitem">
                  <span class="qa-item__ico" style="background:#fee2e2;color:#dc2626"><i class="fa-solid fa-bell"></i></span>
                  <div><b>Notifications</b><small>{{ unreadCount }} unread</small></div>
                </Link>
                <div class="qa-sep" aria-hidden="true"></div>
                <button @click="openSettings(); showQuickActions=false" class="qa-item" role="menuitem">
                  <span class="qa-item__ico" style="background:var(--bg3);color:var(--t3)"><i class="fa-solid fa-gear"></i></span>
                  <div><b>Settings</b><small>Appearance & preferences</small></div>
                </button>
              </div>
            </Transition>
          </div>

          <!-- Settings shortcut -->
          <button class="tb-icn-btn" @click="openSettings('appearance')"
            aria-label="Open settings" title="Settings">
            <i class="fa-solid fa-sliders"></i>
          </button>

          <!-- New Report CTA -->
          <Link :href="route('reports.create')" class="tb-cta" aria-label="Create new report">
            <i class="fa-solid fa-plus"></i>
            <span>New Report</span>
          </Link>
        </div>
      </header>

      <!-- ── PAGE CONTENT ── -->
      <main class="rg-page">
        <!-- Mobile page heading -->
        <div class="rg-mob-head">
          <slot name="header">
            <span class="tb-bc__dot" aria-hidden="true"></span>
            <span class="tb-bc__title">{{ pageTitle }}</span>
          </slot>
        </div>
        <slot />
      </main>

      <!-- ── FOOTER ── -->
      <footer class="rg-footer" role="contentinfo">
        <p>&copy; {{ currentYear }} ReportGen Enterprise. All rights reserved.</p>
        <div class="rg-footer__links">
          <Link :href="route('profile.edit')"          class="rg-footer__link">Profile</Link>
          <Link :href="route('notifications.index')"   class="rg-footer__link">Notifications</Link>
          <button @click="openSettings('appearance')"  class="rg-footer__link">Settings</button>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import {
  ref, computed, reactive, onMounted, onUnmounted,
  watch, nextTick
} from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

// ── Page / Auth ────────────────────────────────────────────
const page              = usePage()
const authUser          = computed(() => page.props.auth?.user)
const pageNotifications = computed(() => page.props.notifications)
const isAdmin           = computed(() => authUser.value?.roles?.includes('admin'))

const isAdminOrManager  = computed(() => authUser.value?.roles?.some(r => ['admin','manager'].includes(r)))
const isImpersonating   = computed(() => page.props.auth?.is_impersonating)
const userInitial       = computed(() => authUser.value?.name?.charAt(0)?.toUpperCase() || 'U')
const currentYear       = new Date().getFullYear()

// ─────────────────────────────────────────────────────────
// APPEARANCE STATE — every ref feeds into liveStyles
// ─────────────────────────────────────────────────────────
const isDark          = ref(false)
const selectedTheme   = ref('system')
const accentKey       = ref('indigo')
const currentFont     = ref("'Sora', sans-serif")
const currentFontSize = ref(14)
const cardRad         = ref(12)
const sidebarWidthKey = ref('default')
const notifDelay      = ref('instant')
const pollingInterval = ref(30)

const accentColors = [
  { key:'indigo',  value:'#6366f1', name:'Indigo'  },
  { key:'violet',  value:'#8b5cf6', name:'Violet'  },
  { key:'pink',    value:'#ec4899', name:'Pink'    },
  { key:'emerald', value:'#10b981', name:'Emerald' },
  { key:'amber',   value:'#f59e0b', name:'Amber'   },
  { key:'red',     value:'#ef4444', name:'Red'     },
  { key:'sky',     value:'#0ea5e9', name:'Sky'     },
  { key:'teal',    value:'#14b8a6', name:'Teal'    },
  { key:'rose',    value:'#f43f5e', name:'Rose'    },
  { key:'orange',  value:'#f97316', name:'Orange'  },
  { key:'lime',    value:'#84cc16', name:'Lime'    },
  { key:'cyan',    value:'#06b6d4', name:'Cyan'    },
]

const sidebarWidths = [
  { key:'compact', label:'Compact', w:240, prev:18 },
  { key:'default', label:'Default', w:272, prev:26 },
  { key:'wide',    label:'Wide',    w:304, prev:34 },
]

const currentAccent = computed(() =>
  accentColors.find(c => c.key === accentKey.value)?.value || '#6366f1'
)

function hexToRgb(hex) {
  const r = parseInt(hex.slice(1,3),16)
  const g = parseInt(hex.slice(3,5),16)
  const b = parseInt(hex.slice(5,7),16)
  return `${r},${g},${b}`
}

const sidebarW = computed(() => {
  if (sidebarCollapsed.value) return 72
  return sidebarWidths.find(s => s.key === sidebarWidthKey.value)?.w ?? 272
})

// ─── THE single :style binding on the root — all live ────
// fontFamily & fontSize are set as direct style props so
// every descendant inherits them without any extra watch().
const liveStyles = computed(() => {
  const ac  = currentAccent.value
  const rgb = hexToRgb(ac)
  const rad = cardRad.value
  return {
    // CSS variables — accent system
    '--ac':      ac,
    '--ac-rgb':  rgb,
    '--ac-10':   ac + '1a',
    '--ac-20':   ac + '33',
    '--ac-40':   ac + '66',
    '--ac-grad': `linear-gradient(135deg,${ac},${ac}bb)`,
    // CSS variables — shape
    '--rad':     rad + 'px',
    '--rad-sm':  Math.max(2, rad - 4) + 'px',
    '--rad-lg':  Math.min(32, rad + 6) + 'px',
    // CSS variable — sidebar width
    '--sw':      sidebarW.value + 'px',
    // Direct inline — font (cascades to all children immediately)
    fontFamily:  currentFont.value,
    fontSize:    currentFontSize.value + 'px',
  }
})

// ─── Preferences ─────────────────────────────────────────
const prefs     = reactive({ compact:false, animations:true, autosave:true, stickyHeader:true })
const dashPrefs = reactive({ showWelcome:true, autoRefresh:false, showTips:true })
const notifPrefs = reactive({ report_updates:true, task_reminders:true, team_mentions:true, weekly_digest:false })
const secPrefs  = reactive({ activityLog:true, sessionAlert:false, twoFactor:false })
const effects   = reactive({ blur:true, gradient:true, mesh:true })
const compactMode = computed(() => prefs.compact)

// Roles preview shown inside the sidebar Roles & Access dropdown (admin only)
const rolesPreview = [
  { name:'Admin',   color:'#ef4444', users:2  },
  { name:'Manager', color:'#f59e0b', users:5  },
  { name:'Editor',  color:'#6366f1', users:12 },
  { name:'Viewer',  color:'#10b981', users:34 },
]

// ─── UI State ──────────────────────────────────────────────
const sidebarCollapsed  = ref(false)
const mobileMenuOpen    = ref(false)
const showNotifications = ref(false)
const showQuickActions  = ref(false)
const showSearch        = ref(false)
const showShortcuts     = ref(false)
const settingsOpen      = ref(false)
const activeSettings    = ref('appearance')
const sidebarRef        = ref(null)
const searchInputRef    = ref(null)
const searchQuery       = ref('')
const searchIdx         = ref(0)

const dropdowns = reactive({ reports:false, people:false, insights:false, roles:false, settings:false })

// ─── Notifications ────────────────────────────────────────
const notifList            = ref([])
const loadingNotifications = ref(false)
const notifError           = ref(null)
let   pollingTimer         = null

const unreadCount = computed(() =>
  pageNotifications.value?.unread_count !== undefined
    ? pageNotifications.value.unread_count
    : notifList.value.filter(n => !n.read_at).length
)
const hasUnread = computed(() => unreadCount.value > 0)

// ─── Route helpers ────────────────────────────────────────
const isRoute = (name) =>
  page.component?.toLowerCase().includes(name.replace('.', '/').replace('_','/').toLowerCase()) ||
  (typeof route !== 'undefined' && route().current?.(name))

const isReportsSection  = computed(() => page.url.includes('/reports') || page.url.includes('/templates'))
const isPeopleSection   = computed(() => page.url.includes('/admin/users') || page.url.includes('/admin/tasks') || page.url.includes('/admin/report-assignments'))
const isInsightsSection = computed(() => page.url.includes('/admin/analytics') || page.url.includes('/admin/activities'))
const isRolesSection    = computed(() => page.url.includes('/admin/roles'))
const isSettingsSection = computed(() => page.url.includes('/profile'))

const pageTitle = computed(() => {
  const u = page.url
  if (u.includes('/admin/users'))              return 'User Management'
  if (u.includes('/admin/roles'))              return 'Roles & Permissions'
  if (u.includes('/admin/tasks'))              return 'Task Management'
  if (u.includes('/admin/analytics/reports'))  return 'Report Analytics'
  if (u.includes('/admin/analytics/users'))    return 'User Analytics'
  if (u.includes('/admin/analytics'))          return 'Analytics'
  if (u.includes('/admin/report-assignments')) return 'Report Assignments'
  if (u.includes('/admin/activities'))         return 'Activity Logs'
  if (u.includes('/reports/create'))           return 'Create Report'
  if (u.includes('/reports/assigned'))         return 'Shared with Me'
  if (u.includes('/reports/trashed'))          return 'Trash'
  if (u.includes('/reports'))                  return 'Reports'
  if (u.includes('/templates'))                return 'Templates'
  if (u.includes('/dashboard'))                return 'Dashboard'
  if (u.includes('/profile'))                  return 'Profile Settings'
  if (u.includes('/my-tasks'))                 return 'My Tasks'
  if (u.includes('/notifications'))            return 'Notifications'
  return 'Dashboard'
})

// ─── Static config ────────────────────────────────────────
const themes = [
  { value:'light',  label:'Light',  icon:'fa-solid fa-sun'    },
  { value:'dark',   label:'Dark',   icon:'fa-solid fa-moon'   },
  { value:'system', label:'Auto',   icon:'fa-solid fa-laptop' },
]

const settingsTabs = computed(() => [
  { id:'appearance',    label:'Appearance',    icon:'fa-solid fa-palette',      badge:null },
  { id:'preferences',   label:'Preferences',   icon:'fa-solid fa-sliders',       badge:null },
  { id:'notifications', label:'Alerts',        icon:'fa-solid fa-bell',          badge: unreadCount.value > 0 ? unreadCount.value : null },
  { id:'security',      label:'Security',      icon:'fa-solid fa-shield-halved', badge:null },
  { id:'about',         label:'About',         icon:'fa-solid fa-circle-info',   badge:null },
])

const prefOptions = [
  { key:'compact',      label:'Compact Mode',      desc:'Tighter spacing throughout the UI',         icon:'fa-solid fa-compress' },
  { key:'animations',   label:'Animations',         desc:'Smooth micro-interactions & transitions',   icon:'fa-solid fa-wand-magic-sparkles' },
  { key:'autosave',     label:'Auto-save Reports',  desc:'Automatically save drafts while editing',   icon:'fa-solid fa-floppy-disk' },
  { key:'stickyHeader', label:'Sticky Header',      desc:'Keep topbar visible while scrolling',       icon:'fa-solid fa-thumbtack' },
]
const dashPrefOptions = [
  { key:'showWelcome', label:'Welcome Banner',  desc:'Personalised greeting on dashboard',   icon:'fa-solid fa-hand-wave' },
  { key:'autoRefresh', label:'Auto-refresh',    desc:'Refresh dashboard stats every 5 min',  icon:'fa-solid fa-rotate' },
  { key:'showTips',    label:'Show Tips',       desc:'Helpful hints and feature callouts',   icon:'fa-solid fa-lightbulb' },
]
const notifTypeOptions = [
  { key:'report_updates', label:'Report Updates',  icon:'fa-solid fa-file-pen',           desc:'When reports are updated or shared',       color:'#6366f1' },
  { key:'task_reminders', label:'Task Reminders',  icon:'fa-solid fa-clock',              desc:'Reminders for tasks nearing deadline',     color:'#f59e0b' },
  { key:'team_mentions',  label:'Team Mentions',   icon:'fa-solid fa-at',                 desc:'When someone mentions or assigns you',     color:'#ec4899' },
  { key:'weekly_digest',  label:'Weekly Digest',   icon:'fa-solid fa-envelope-open-text', desc:'Summary email every Monday morning',       color:'#10b981' },
]
const securityOptions = [
  { key:'activityLog',  label:'Activity Logging',   desc:'Log all login and action events',    icon:'fa-solid fa-clock-rotate-left', color:'#6366f1' },
  { key:'sessionAlert', label:'New Session Alerts', desc:'Notify me on new device sign-in',    icon:'fa-solid fa-mobile-screen',     color:'#f59e0b' },
  { key:'twoFactor',    label:'2FA Reminder',       desc:'Remind to enable two-factor auth',   icon:'fa-solid fa-shield-check',      color:'#10b981' },
]
const effectOptions = [
  { key:'blur',     label:'Backdrop Blur',  icon:'fa-solid fa-circle-half-stroke' },
  { key:'gradient', label:'Gradient Tones', icon:'fa-solid fa-swatchbook' },
  { key:'mesh',     label:'Ambient Orbs',   icon:'fa-solid fa-circle-dot' },
]
const delayOptions = [
  { val:'instant', label:'Instant' },
  { val:'30s',     label:'30s' },
  { val:'1min',    label:'1 min' },
  { val:'5min',    label:'5 min' },
]
const aboutCards = [
  { icon:'fa-solid fa-file-lines',    label:'Reports'   },
  { icon:'fa-solid fa-users',         label:'Team'      },
  { icon:'fa-solid fa-shield-halved', label:'Secure'    },
  { icon:'fa-solid fa-bolt',          label:'Fast'      },
]

const shortcutsList = computed(() => {
  const mac = typeof navigator !== 'undefined' && /mac/i.test(navigator.platform)
  const M = mac ? '⌘' : 'Ctrl'
  return [
    { key:`${M}+K`, description:'Open search palette',  icon:'fa-solid fa-magnifying-glass' },
    { key:`${M}+B`, description:'Toggle sidebar',       icon:'fa-solid fa-bars' },
    { key:`${M}+D`, description:'Toggle dark mode',     icon:'fa-solid fa-moon' },
    { key:`${M}+N`, description:'Create new report',    icon:'fa-solid fa-plus' },
    { key:`${M}+/`, description:'Show shortcuts',       icon:'fa-solid fa-keyboard' },
    { key:`${M}+,`, description:'Open settings',        icon:'fa-solid fa-gear' },
    { key:'Esc',    description:'Close any modal',      icon:'fa-solid fa-xmark' },
  ]
})

const searchCategories = [
  { label:'Reports',   icon:'fa-solid fa-file-lines',  query:'reports' },
  { label:'Tasks',     icon:'fa-solid fa-list-check',  query:'tasks' },
  { label:'Analytics', icon:'fa-solid fa-chart-pie',   query:'analytics' },
  { label:'Admin',     icon:'fa-solid fa-users-gear',  query:'admin' },
]

const searchData = [
  { id:'1',  title:'Dashboard',          subtitle:'Overview & stats',        type:'Page',    icon:'fa-solid fa-gauge-high',         color:'#6366f1', link:'/dashboard' },
  { id:'2',  title:'All Reports',        subtitle:'Report management',       type:'Reports', icon:'fa-solid fa-file-lines',         color:'#0ea5e9', link:'/reports' },
  { id:'3',  title:'Create Report',      subtitle:'New blank report',        type:'Reports', icon:'fa-solid fa-plus',               color:'#10b981', link:'/reports/create' },
  { id:'4',  title:'Shared with Me',     subtitle:'Assigned reports',        type:'Reports', icon:'fa-solid fa-share-alt',          color:'#f59e0b', link:'/reports/assigned' },
  { id:'5',  title:'Trash',              subtitle:'Deleted reports',         type:'Reports', icon:'fa-solid fa-trash-can',          color:'#ef4444', link:'/reports/trashed' },
  { id:'6',  title:'Templates',          subtitle:'Report templates',        type:'Page',    icon:'fa-solid fa-layer-group',        color:'#ec4899', link:'/templates' },
  { id:'7',  title:'My Tasks',           subtitle:'Your pending tasks',      type:'Tasks',   icon:'fa-solid fa-list-check',         color:'#f59e0b', link:'/my-tasks' },
  { id:'8',  title:'User Management',    subtitle:'Admin — users',           type:'Admin',   icon:'fa-solid fa-users',              color:'#8b5cf6', link:'/admin/users' },
  { id:'9',  title:'All Tasks',          subtitle:'Admin — all tasks',       type:'Admin',   icon:'fa-solid fa-tasks',              color:'#6366f1', link:'/admin/tasks' },
  { id:'10', title:'Report Assignments', subtitle:'Admin — assignments',     type:'Admin',   icon:'fa-solid fa-share-nodes',        color:'#14b8a6', link:'/admin/report-assignments' },
  { id:'11', title:'Analytics',          subtitle:'Usage insights',          type:'Admin',   icon:'fa-solid fa-chart-pie',          color:'#14b8a6', link:'/admin/analytics' },
  { id:'12', title:'Report Analytics',   subtitle:'Report usage stats',      type:'Admin',   icon:'fa-solid fa-file-chart-column',  color:'#0ea5e9', link:'/admin/analytics/reports' },
  { id:'13', title:'User Analytics',     subtitle:'User activity stats',     type:'Admin',   icon:'fa-solid fa-user-chart',         color:'#8b5cf6', link:'/admin/analytics/users' },
  { id:'14', title:'Activity Logs',      subtitle:'System event log',        type:'Admin',   icon:'fa-solid fa-clock-rotate-left',  color:'#ef4444', link:'/admin/activities' },
  { id:'15', title:'Roles',              subtitle:'Admin — roles',           type:'Admin',   icon:'fa-solid fa-shield',             color:'#f43f5e', link:'/admin/roles' },
  { id:'16', title:'Permissions',        subtitle:'Admin — access control',  type:'Admin',   icon:'fa-solid fa-key',                color:'#f97316', link:'/admin/roles/permissions' },
  { id:'17', title:'Notifications',      subtitle:'All alerts & updates',    type:'Page',    icon:'fa-solid fa-bell',               color:'#0ea5e9', link:'/notifications' },
  { id:'18', title:'Edit Profile',       subtitle:'Account settings',        type:'Page',    icon:'fa-solid fa-user-pen',           color:'#94a3b8', link:'/profile' },
  { id:'19', title:'Appearance Settings',subtitle:'Theme, colors & fonts',   type:'Settings',icon:'fa-solid fa-palette',            color:'#ec4899', link:null, action:() => openSettings('appearance') },
  { id:'20', title:'Preferences',        subtitle:'Interface options',       type:'Settings',icon:'fa-solid fa-sliders',            color:'#6366f1', link:null, action:() => openSettings('preferences') },
]

const filteredSearch = computed(() => {
  if (!searchQuery.value) return []
  const q = searchQuery.value.toLowerCase()
  return searchData.filter(r =>
    r.title.toLowerCase().includes(q) ||
    r.subtitle.toLowerCase().includes(q) ||
    r.type.toLowerCase().includes(q)
  )
})

// ─── Notification helpers ──────────────────────────────────
const ntLabel = (type) => ({
  task_created:'Task', task_completed:'Done', task_updated:'Updated', task_deleted:'Deleted',
  report_assigned:'Assigned', report_shared:'Shared', report_created:'New', report_updated:'Updated',
  user_mentioned:'Mention', system:'System',
}[type] ?? (type?.replace(/_/g,' ') || 'Info'))

const ntClass = (type) => ({
  task_created:'nt--indigo', task_completed:'nt--emerald', task_deleted:'nt--red',
  report_assigned:'nt--violet', report_shared:'nt--sky', user_mentioned:'nt--pink',
}[type] ?? 'nt--slate')

const formatTimeAgo = (date) => {
  if (!date) return ''
  const s = Math.floor((Date.now() - new Date(date)) / 1000)
  if (s < 60)    return 'Just now'
  if (s < 3600)  return `${Math.floor(s/60)}m ago`
  if (s < 86400) return `${Math.floor(s/3600)}h ago`
  return `${Math.floor(s/86400)}d ago`
}

// ─── ACTIONS ──────────────────────────────────────────────
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('rg-sidebar-collapsed', String(sidebarCollapsed.value))
}

const toggleDark = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('rg-theme', isDark.value ? 'dark' : 'light')
}

const applyTheme = (t) => {
  selectedTheme.value = t
  if (t === 'dark')       { isDark.value = true;  document.documentElement.classList.add('dark') }
  else if (t === 'light') { isDark.value = false; document.documentElement.classList.remove('dark') }
  else {
    const sys = window.matchMedia('(prefers-color-scheme: dark)').matches
    isDark.value = sys
    document.documentElement.classList.toggle('dark', sys)
  }
  localStorage.setItem('rg-theme-pref', t)
}

const setAccent = (key) => {
  accentKey.value = key
  localStorage.setItem('rg-accent', key)
  // Stamp onto :root so any CSS outside this component can use --ac too
  const val = accentColors.find(c => c.key === key)?.value || '#6366f1'
  document.documentElement.style.setProperty('--ac', val)
  document.documentElement.style.setProperty('--ac-rgb', hexToRgb(val))
}

// All font/size/radius handlers write to localStorage immediately.
// The actual visual update happens instantly via liveStyles computed.
const onFontChange     = () => localStorage.setItem('rg-font',      currentFont.value)
const onFontSizeChange = () => localStorage.setItem('rg-font-size', String(currentFontSize.value))
const onRadiusChange   = () => localStorage.setItem('rg-radius',    String(cardRad.value))

const setSidebarWidth = (key) => {
  sidebarWidthKey.value = key
  localStorage.setItem('rg-sidebar-width', key)
}

const persistEffects     = () => localStorage.setItem('rg-effects',     JSON.stringify({...effects}))
const persistNotifDelay  = () => localStorage.setItem('rg-notif-delay', notifDelay.value)

const onPollingChange = () => {
  localStorage.setItem('rg-poll-interval', String(pollingInterval.value))
  clearInterval(pollingTimer)
  pollingTimer = setInterval(fetchNotifications, pollingInterval.value * 1000)
}

const applyBodyClasses = () => {
  document.body.classList.toggle('rg-compact',   prefs.compact)
  document.body.classList.toggle('rg-no-motion', !prefs.animations)
}

const saveAppearance = () => {
  onFontChange(); onFontSizeChange(); onRadiusChange()
  settingsOpen.value = false
  toast('Appearance saved ✓', 'success')
}

const savePrefs = () => {
  localStorage.setItem('rg-prefs',      JSON.stringify({...prefs}))
  localStorage.setItem('rg-dash-prefs', JSON.stringify({...dashPrefs}))
  applyBodyClasses()
  settingsOpen.value = false
  toast('Preferences saved ✓', 'success')
}

const saveNotifPrefs = () => {
  localStorage.setItem('rg-notif-prefs', JSON.stringify({...notifPrefs}))
  persistNotifDelay()
  settingsOpen.value = false
  toast('Notification settings saved ✓', 'success')
}

const clearLocalStorage = () => {
  ['rg-theme','rg-theme-pref','rg-accent','rg-font','rg-font-size','rg-radius',
   'rg-sidebar-collapsed','rg-sidebar-width','rg-prefs','rg-dash-prefs',
   'rg-notif-prefs','rg-sec-prefs','rg-effects','rg-poll-interval','rg-notif-delay']
    .forEach(k => localStorage.removeItem(k))
  location.reload()
}

const toggleDropdown = (key) => {
  const cur = dropdowns[key]
  Object.keys(dropdowns).forEach(k => dropdowns[k] = false)
  dropdowns[key] = !cur
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showQuickActions.value  = false
  if (showNotifications.value) fetchNotifications()
}

const openSearch = () => {
  showSearch.value  = true
  searchQuery.value = ''
  searchIdx.value   = 0
  nextTick(() => searchInputRef.value?.focus())
}

const openSettings = (tab = 'appearance') => {
  activeSettings.value    = tab
  settingsOpen.value      = true
  showQuickActions.value  = false
  showNotifications.value = false
}

const goToSearchResult = (r) => {
  if (!r) return
  showSearch.value = false
  if (r.action) { r.action(); return }
  if (r.link)   router.visit(r.link)
}

// ─── Notifications API ────────────────────────────────────
const fetchNotifications = async () => {
  loadingNotifications.value = true
  notifError.value = null
  try {
    const res = await fetch(route('notifications.latest'), {
      headers: { 'X-Requested-With':'XMLHttpRequest', Accept:'application/json' }
    })
    if (!res.ok) throw new Error('Network error')
    const data = await res.json()
    notifList.value = data.notifications ?? []
    if (pageNotifications.value) pageNotifications.value.unread_count = data.unread_count
  } catch {
    if (!notifList.value.length) notifError.value = 'Failed to load notifications'
  } finally {
    loadingNotifications.value = false
  }
}

const markAsRead = async (id) => {
  try {
    await fetch(route('notifications.mark-read', id), {
      method:'PUT',
      headers:{
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        Accept:'application/json'
      }
    })
    const n = notifList.value.find(x => x.id === id)
    if (n) n.read_at = new Date().toISOString()
    if (pageNotifications.value)
      pageNotifications.value.unread_count = Math.max(0,(pageNotifications.value.unread_count||1)-1)
  } catch {}
}

const markAllRead = async () => {
  try {
    await fetch(route('notifications.mark-all-read'), {
      method:'PUT',
      headers:{
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        Accept:'application/json'
      }
    })
    notifList.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    if (pageNotifications.value) { pageNotifications.value.unread_count = 0; pageNotifications.value.pending_tasks = 0 }
    toast('All notifications marked as read', 'success')
  } catch { toast('Failed to mark all as read', 'error') }
}

const handleNotifClick = async (n) => {
  if (!n.read_at) await markAsRead(n.id)
  if (n.action_url) router.visit(n.action_url)
  showNotifications.value = false
}

// ─── Toast ────────────────────────────────────────────────
const toast = (msg, type = 'success') => {
  const el  = document.createElement('div')
  const ico = { success:'circle-check', error:'circle-xmark', info:'circle-info' }[type] || 'circle-info'
  el.className = `rg-toast rg-toast--${type}`
  el.innerHTML = `<i class="fa-solid fa-${ico}"></i><span>${msg}</span>`
  document.body.appendChild(el)
  requestAnimationFrame(() => el.classList.add('rg-toast--in'))
  setTimeout(() => { el.classList.remove('rg-toast--in'); setTimeout(() => el.remove(), 320) }, 3200)
}

// ─── Keyboard shortcuts ────────────────────────────────────
const onKeydown = (e) => {
  const mac = /mac/i.test(navigator.platform)
  const mod = mac ? e.metaKey : e.ctrlKey
  const tag = e.target.tagName
  if (['INPUT','TEXTAREA','SELECT'].includes(tag) || e.target.isContentEditable) {
    if (e.key === 'Escape') { showSearch.value = false; showNotifications.value = false }
    return
  }
  if (e.key === 'Escape') {
    showSearch.value = showNotifications.value = showQuickActions.value =
    showShortcuts.value = settingsOpen.value = mobileMenuOpen.value = false
    return
  }
  if (!mod) return
  if      (e.key==='k') { e.preventDefault(); openSearch() }
  else if (e.key==='b') { e.preventDefault(); toggleSidebar() }
  else if (e.key==='d') { e.preventDefault(); toggleDark() }
  else if (e.key==='n') { e.preventDefault(); router.visit(route('reports.create')) }
  else if (e.key==='/') { e.preventDefault(); showShortcuts.value = !showShortcuts.value }
  else if (e.key===',') { e.preventDefault(); openSettings() }
}

const onClickOutside = (e) => {
  if (!e.target.closest('[data-notif-wrapper]')) showNotifications.value = false
  if (!e.target.closest('[data-qa-wrapper]'))    showQuickActions.value  = false
}

// ─── LOAD ALL SETTINGS FROM LOCALSTORAGE (keys prefixed rg-) ─
const loadSettings = () => {
  const ls = (k, def) => localStorage.getItem(k) ?? def

  const themePref = ls('rg-theme-pref', 'system')
  selectedTheme.value = themePref

  accentKey.value       = ls('rg-accent',       'indigo')
  currentFont.value     = ls('rg-font',          "'Sora', sans-serif")
  currentFontSize.value = parseInt(ls('rg-font-size', '14'))
  cardRad.value         = parseInt(ls('rg-radius',    '12'))
  sidebarCollapsed.value = ls('rg-sidebar-collapsed','false') === 'true'
  sidebarWidthKey.value  = ls('rg-sidebar-width', 'default')
  notifDelay.value       = ls('rg-notif-delay', 'instant')
  pollingInterval.value  = parseInt(ls('rg-poll-interval', '30'))

  try { Object.assign(prefs,      JSON.parse(ls('rg-prefs',      '{}'))) } catch {}
  try { Object.assign(dashPrefs,  JSON.parse(ls('rg-dash-prefs', '{}'))) } catch {}
  try { Object.assign(notifPrefs, JSON.parse(ls('rg-notif-prefs','{}'))) } catch {}
  try { Object.assign(secPrefs,   JSON.parse(ls('rg-sec-prefs',  '{}'))) } catch {}
  try { Object.assign(effects,    JSON.parse(ls('rg-effects',    '{}'))) } catch {}

  // Apply theme (sets isDark + DOM class)
  applyTheme(themePref)

  // Stamp accent onto :root immediately
  const acVal = accentColors.find(c => c.key === accentKey.value)?.value || '#6366f1'
  document.documentElement.style.setProperty('--ac',     acVal)
  document.documentElement.style.setProperty('--ac-rgb', hexToRgb(acVal))

  applyBodyClasses()
}

// ─── Lifecycle ─────────────────────────────────────────────
onMounted(() => {
  loadSettings()
  if (pageNotifications.value?.items?.length) notifList.value = pageNotifications.value.items
  fetchNotifications()
  pollingTimer = setInterval(fetchNotifications, pollingInterval.value * 1000)
  document.addEventListener('keydown', onKeydown)
  document.addEventListener('click',   onClickOutside)
})

onUnmounted(() => {
  clearInterval(pollingTimer)
  document.removeEventListener('keydown', onKeydown)
  document.removeEventListener('click',   onClickOutside)
})

// Watches — only what liveStyles can't cover
watch(() => pageNotifications.value?.items, v => { if (v?.length) notifList.value = v }, { deep:true })
watch(() => prefs.compact,    applyBodyClasses)
watch(() => prefs.animations, applyBodyClasses)
</script>

<!-- ══════════════════════════════════════════════════════
     INLINE SUB-COMPONENTS
══════════════════════════════════════════════════════ -->
<script>
import { defineComponent as dc, h, Transition as T } from 'vue'
import { Link } from '@inertiajs/vue3'

export const NavItem = dc({
  name: 'NavItem',
  props: { href:String, icon:String, label:String, active:Boolean, collapsed:Boolean, badge:[Number,String,null] },
  setup(p) {
    return () => h(Link, {
      href: p.href,
      class: ['nav-item', p.active&&'is-on', p.collapsed&&'is-coll'].filter(Boolean).join(' '),
      title: p.collapsed ? p.label : undefined,
    }, { default: () => [
      h('span',{class:'nav-item__ic','aria-hidden':'true'},[h('i',{class:p.icon})]),
      !p.collapsed && h('span',{class:'nav-item__lbl'},p.label),
      p.badge != null && h('span',{
        class:['nav-item__badge',p.collapsed?'is-float':''].join(' '),
        'aria-label':`${p.badge} unread`
      }, p.badge > 99 ? '99+' : p.badge),
      p.active && !p.collapsed && h('span',{class:'nav-item__bar','aria-hidden':'true'}),
    ].filter(Boolean)})
  }
})

export const NavDropdown = dc({
  name:'NavDropdown',
  props:{ icon:String, label:String, open:Boolean, active:Boolean, collapsed:Boolean },
  emits:['toggle'],
  setup(p,{emit,slots}) {
    return () => h('div',{class:'nav-dd'},[
      h('button',{
        class:['nav-item',(p.open||p.active)&&'is-on',p.collapsed&&'is-coll'].filter(Boolean).join(' '),
        title: p.collapsed ? p.label : undefined,
        onClick: () => emit('toggle'),
        'aria-expanded': String(p.open),
      },[
        h('span',{class:'nav-item__ic','aria-hidden':'true'},[h('i',{class:p.icon})]),
        !p.collapsed && h('span',{class:'nav-item__lbl'},p.label),
        !p.collapsed && h('i',{
          class:`fa-solid ${p.open?'fa-chevron-up':'fa-chevron-down'} nav-item__chev`,
          'aria-hidden':'true'
        }),
      ].filter(Boolean)),
      h(T,{name:'rg-sub'},{
        default:()=> p.open && !p.collapsed && h('div',{class:'nav-sub'},slots.default?.())
      })
    ])
  }
})

export const NavSubItem = dc({
  name:'NavSubItem',
  props:{ href:String, icon:String, label:String, active:Boolean, shortcut:String },
  setup(p,{slots}) {
    return () => h(Link,{
      href: p.href,
      class: ['nav-sub-item', p.active&&'is-on'].filter(Boolean).join(' '),
    },{default:()=>[
      h('i',{class:[p.icon,'nav-sub-item__ic'].join(' '),'aria-hidden':'true'}),
      h('span',{class:'nav-sub-item__lbl'},p.label),
      p.shortcut && h('kbd',{class:'nav-sub-item__sc'},p.shortcut),
      slots.default?.(),
    ].filter(Boolean)})
  }
})
</script>

<style>
/* ════════════════════════════════════════════════════════════
   FONTS
════════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=DM+Sans:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap');

/* ════════════════════════════════════════════════════════════
   ROOT TOKENS  (liveStyles overwrites the root div at runtime)
════════════════════════════════════════════════════════════ */
:root{
  --ac:       #6366f1;
  --ac-rgb:   99,102,241;
  --ac-10:    #6366f11a;
  --ac-20:    #6366f133;
  --ac-40:    #6366f166;
  --ac-grad:  linear-gradient(135deg,#6366f1,#6366f1bb);
  --rad:      12px;
  --rad-sm:   8px;
  --rad-lg:   18px;
  --sw:       272px;
  --tb-h:     60px;
  --ease:     cubic-bezier(.4,0,.2,1);
  --spring:   cubic-bezier(.34,1.3,.64,1);
  --dur:      .2s;
}

*,*::before,*::after{ box-sizing:border-box; margin:0; padding:0; }

/* ════════════════════════════════════════════════════════════
   APP SHELL
════════════════════════════════════════════════════════════ */
.rg-app{
  display:flex; min-height:100dvh; position:relative; overflow-x:hidden;
  /* fontFamily & fontSize are injected live via :style="liveStyles" */
}

/* LIGHT */
.rg-light{
  --bg:   #eff2f8;
  --bg2:  #ffffff;
  --bg3:  #f8fafc;
  --bg4:  #eef1f7;
  --bd:   #e2e8f0;
  --bd2:  #f1f5f9;
  --t1:   #0f172a;
  --t2:   #334155;
  --t3:   #94a3b8;
  --sh:   0 2px 14px rgba(15,23,42,.07);
  --shx:  0 12px 48px rgba(15,23,42,.12);
  --glass:rgba(255,255,255,.82);
  background:linear-gradient(145deg,#e8ecf5 0%,#f2f5fc 60%,#eaeef8 100%);
  color:var(--t1);
}

/* DARK */
.rg-dark{
  --bg:   #07090f;
  --bg2:  #0e1422;
  --bg3:  #141c2c;
  --bg4:  #1a2236;
  --bd:   #202d42;
  --bd2:  #1a2236;
  --t1:   #f0f4fa;
  --t2:   #8fa3bf;
  --t3:   #4a5c72;
  --sh:   0 2px 14px rgba(0,0,0,.35);
  --shx:  0 12px 48px rgba(0,0,0,.55);
  --glass:rgba(14,20,34,.88);
  background:radial-gradient(ellipse at 20% 10%,#0b1427 0%,#07090f 65%);
  color:var(--t1);
}

/* ════════════════════════════════════════════════════════════
   AMBIENT MESH
════════════════════════════════════════════════════════════ */
.rg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden;}
.mesh-orb{position:absolute;border-radius:50%;filter:blur(80px);}
.mesh-orb--1{
  width:clamp(280px,38vw,560px);height:clamp(280px,38vw,560px);
  top:-10%;left:-8%;
  background:radial-gradient(circle,rgba(var(--ac-rgb),.32) 0%,transparent 70%);
  animation:orb1 16s ease-in-out infinite alternate;
}
.mesh-orb--2{
  width:clamp(200px,28vw,420px);height:clamp(200px,28vw,420px);
  bottom:5%;right:5%;
  background:radial-gradient(circle,rgba(var(--ac-rgb),.18) 0%,transparent 70%);
  animation:orb2 12s ease-in-out infinite alternate;
}
.mesh-orb--3{
  width:clamp(120px,18vw,260px);height:clamp(120px,18vw,260px);
  top:45%;left:45%;
  background:radial-gradient(circle,rgba(var(--ac-rgb),.1) 0%,transparent 70%);
  animation:orb3 20s ease-in-out infinite alternate;
}
.rg-dark .mesh-orb{opacity:.55;}
@keyframes orb1{from{transform:translate(0,0) scale(1)}to{transform:translate(4%,7%) scale(1.08)}}
@keyframes orb2{from{transform:translate(0,0) scale(1)}to{transform:translate(-4%,-5%) scale(1.06)}}
@keyframes orb3{from{transform:translate(0,0)}to{transform:translate(3%,-4%)}}

/* ════════════════════════════════════════════════════════════
   SIDEBAR
════════════════════════════════════════════════════════════ */
.rg-sidebar{
  position:fixed;left:0;top:0;z-index:50;
  height:100dvh;width:var(--sw);
  display:flex;flex-direction:column;
  background:var(--glass);
  backdrop-filter:blur(24px) saturate(170%);
  -webkit-backdrop-filter:blur(24px) saturate(170%);
  border-right:1px solid var(--bd);
  box-shadow:var(--shx);
  transition:width var(--dur) var(--ease),transform var(--dur) var(--ease);
  overflow:hidden;
}
.rg-sidebar.is-coll{width:72px;}

.sb-glow{
  position:absolute;top:-70px;left:-70px;
  width:220px;height:220px;border-radius:50%;
  background:radial-gradient(circle,rgba(var(--ac-rgb),.22) 0%,transparent 70%);
  pointer-events:none;animation:orb1 10s ease-in-out infinite alternate;
}

@media(max-width:1023px){
  .rg-sidebar{transform:translateX(-100%);}
  .rg-sidebar.is-open{transform:translateX(0);}
}

/* Brand */
.sb-brand{
  flex-shrink:0;display:flex;align-items:center;gap:10px;
  padding:14px 12px;border-bottom:1px solid var(--bd);min-height:62px;
}
.sb-logo{
  flex-shrink:0;position:relative;
  width:38px;height:38px;border-radius:var(--rad-sm);
  background:var(--ac-grad);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:15px;
  box-shadow:0 4px 18px rgba(var(--ac-rgb),.44);
}
.sb-logo__pulse{
  position:absolute;top:-2px;right:-2px;
  width:9px;height:9px;border-radius:50%;
  background:#10b981;border:2px solid var(--bg2);opacity:0;transition:opacity .3s;
}
.sb-logo__pulse.is-live{opacity:1;animation:pulse-live 2s ease-in-out infinite;}
@keyframes pulse-live{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.5)}50%{box-shadow:0 0 0 4px rgba(16,185,129,0)}}
.sb-wordmark{flex:1;min-width:0;}
.sb-wordmark__name   {display:block;font-weight:800;font-size:14.5px;color:var(--t1);letter-spacing:-.3px;line-height:1;}
.sb-wordmark__edition{display:block;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--ac);margin-top:2px;}
.sb-coll-btn{
  flex-shrink:0;width:26px;height:26px;border-radius:var(--rad-sm);
  border:1px solid var(--bd);background:var(--bg3);color:var(--t3);font-size:10px;
  display:flex;align-items:center;justify-content:center;cursor:pointer;
  transition:all var(--dur);
}
.sb-coll-btn:hover{background:var(--ac-10);color:var(--ac);border-color:var(--ac-20);}

/* User */
.sb-user{
  flex-shrink:0;display:flex;align-items:center;gap:10px;
  padding:10px 12px;border-bottom:1px solid var(--bd);
}
.sb-avatar{
  flex-shrink:0;position:relative;
  width:40px;height:40px;border-radius:var(--rad-sm);
  background:var(--ac-grad);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-weight:800;font-size:15px;cursor:default;
}
.sb-avatar__online{
  position:absolute;bottom:-2px;right:-2px;
  width:10px;height:10px;border-radius:50%;
  background:#10b981;border:2px solid var(--bg2);
}
.sb-user__info{flex:1;min-width:0;}
.sb-user__name {display:block;font-weight:700;font-size:12.5px;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sb-user__email{display:block;font-size:10px;color:var(--t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px;}
.sb-badges{display:flex;flex-wrap:wrap;gap:3px;margin-top:4px;}
.sb-badge{font-size:9px;font-weight:700;padding:1px 5px;border-radius:3px;}
.sb-badge--pro {background:rgba(var(--ac-rgb),.12);color:var(--ac);}
.sb-badge--gold{background:rgba(245,158,11,.12);color:#d97706;}
.sb-badge--red {background:rgba(239,68,68,.12);color:#dc2626;}

/* ══════════════════════════════════════════
   SIDEBAR NAV — COMPLETE STYLE SYSTEM
══════════════════════════════════════════ */

/* Scrollable nav container */
.sb-nav{
  flex:1; overflow-y:auto; overflow-x:hidden;
  padding:4px 6px 12px;
  scrollbar-width:thin; scrollbar-color:var(--bd) transparent;
}
.sb-nav::-webkit-scrollbar{ width:3px; }
.sb-nav::-webkit-scrollbar-thumb{ background:var(--bd); border-radius:99px; }

/* ── Section wrapper ── */
.sb-sect{
  margin-bottom:0;
  padding-bottom:2px;
  position:relative;
}
/* Thin divider line between sections in collapsed mode */
.sb-hr{
  height:1px;
  background:linear-gradient(90deg,transparent,var(--bd),transparent);
  margin:6px 6px;
  opacity:.6;
}

/* ── Section Header Label ── */
.sb-sect__lbl{
  display:flex; align-items:center; gap:6px;
  font-size:9px; font-weight:800;
  text-transform:uppercase; letter-spacing:.16em;
  color:var(--t3); padding:12px 10px 5px;
  line-height:1;
  position:relative;
  /* subtle left accent line */
}
.sb-sect__lbl::after{
  content:'';
  position:absolute; bottom:0; left:10px; right:10px;
  height:1px;
  background:linear-gradient(90deg,var(--bd),transparent);
}
.sb-sect__lbl--admin{
  color:var(--ac);
  opacity:.9;
}
.sb-sect__lbl--admin::after{
  background:linear-gradient(90deg,rgba(var(--ac-rgb),.3),transparent);
}
.sb-sect__lbl-ic{
  font-size:8.5px; opacity:.6; flex-shrink:0;
}

/* ── Nav Item (top-level links + dropdown triggers) ── */
.nav-item{
  display:flex; align-items:center; gap:10px;
  width:100%; padding:9px 10px;
  border-radius:var(--rad-sm);
  color:var(--t2); font-size:12.5px; font-weight:500;
  cursor:pointer; text-decoration:none;
  transition:background var(--dur) var(--ease),
             color var(--dur) var(--ease),
             border-color var(--dur) var(--ease),
             transform .15s var(--spring);
  position:relative;
  border:1px solid transparent;
  background:none;
  margin:1px 0;
}
.nav-item:hover{
  color:var(--t1);
  background:var(--bg3);
  border-color:var(--bd);
  transform:translateX(2px);
}
.nav-item.is-on{
  color:var(--ac);
  background:var(--ac-10);
  border-color:var(--ac-20);
  font-weight:600;
  box-shadow:inset 0 0 0 1px rgba(var(--ac-rgb),.08);
}
.nav-item.is-on:hover{ transform:none; }
/* Collapsed state */
.nav-item.is-coll{
  justify-content:center; padding:11px;
  margin:2px 0;
}
.nav-item.is-coll:hover{ transform:none; }
/* Icon wrapper */
.nav-item__ic{
  width:20px; height:20px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:13px;
  transition:transform .2s var(--spring);
}
.nav-item:hover .nav-item__ic{ transform:scale(1.12); }
.nav-item.is-on  .nav-item__ic{ transform:scale(1.0);  color:var(--ac); }
/* Label */
.nav-item__lbl{
  flex:1; text-align:left;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
/* Active right-edge indicator bar */
.nav-item__bar{
  position:absolute; right:0; top:50%;
  transform:translateY(-50%);
  width:3px; height:60%;
  border-radius:99px;
  background:var(--ac-grad);
  box-shadow:0 0 8px rgba(var(--ac-rgb),.5);
}
/* Badge (notifications count) */
.nav-item__badge{
  min-width:18px; height:18px; padding:0 4px;
  background:#ef4444; color:#fff;
  font-size:9px; font-weight:700; border-radius:99px;
  display:flex; align-items:center; justify-content:center;
  animation:badge-blink 2.5s ease-in-out infinite;
  flex-shrink:0;
  box-shadow:0 2px 6px rgba(239,68,68,.4);
}
.nav-item__badge.is-float{
  position:absolute; top:4px; right:4px;
  min-width:14px; height:14px; font-size:8px;
}
@keyframes badge-blink{
  0%,100%{ opacity:1; transform:scale(1);   }
  50%    { opacity:.7; transform:scale(.92); }
}
/* Chevron on dropdowns */
.nav-item__chev{
  font-size:9px; margin-left:auto; color:var(--t3); flex-shrink:0;
  transition:transform var(--dur) var(--spring), color var(--dur);
}
.nav-item.is-on .nav-item__chev{ color:var(--ac); }

/* ── Dropdown wrapper ── */
.nav-dd{ position:relative; }

/* Sub-menu container — with a subtle left connector line */
.nav-sub{
  padding:3px 0 5px 12px;
  margin-left:10px;
  border-left:1.5px solid var(--bd);
  position:relative;
}
/* Fade the connector line at the bottom */
.nav-sub::after{
  content:'';
  position:absolute; bottom:0; left:-1px;
  width:1.5px; height:12px;
  background:linear-gradient(to bottom, var(--bd), transparent);
}

/* ── Sub-item (inside a dropdown) ── */
.nav-sub-item{
  display:flex; align-items:center; gap:9px;
  width:100%; padding:7px 10px 7px 12px;
  border-radius:var(--rad-sm);
  color:var(--t3); font-size:11.5px; font-weight:500;
  text-decoration:none; cursor:pointer;
  transition:all var(--dur) var(--ease);
  border:1px solid transparent; background:none; text-align:left;
  position:relative;
  margin:1px 0;
}
/* Tiny connector dot on the left edge */
.nav-sub-item::before{
  content:'';
  position:absolute; left:-13px; top:50%;
  transform:translateY(-50%);
  width:5px; height:5px; border-radius:50%;
  background:var(--bd);
  transition:background var(--dur), transform var(--dur) var(--spring);
  flex-shrink:0;
}
.nav-sub-item:hover{
  color:var(--t1); background:var(--bg3);
  border-color:var(--bd);
  transform:translateX(2px);
}
.nav-sub-item:hover::before{
  background:var(--ac);
  transform:translateY(-50%) scale(1.3);
}
.nav-sub-item.is-on{
  color:var(--ac); background:var(--ac-10);
  font-weight:600; border-color:var(--ac-20);
}
.nav-sub-item.is-on::before{
  background:var(--ac);
  box-shadow:0 0 6px rgba(var(--ac-rgb),.5);
}
.nav-sub-item.is-on:hover{ transform:none; }
.nav-sub-item__ic{
  font-size:10px; width:15px; flex-shrink:0;
  transition:transform .15s var(--spring);
}
.nav-sub-item:hover .nav-sub-item__ic{ transform:scale(1.15); }
.nav-sub-item__lbl{ flex:1; }
.nav-sub-item__sc{
  font-size:9px; padding:1px 5px; border-radius:4px;
  background:var(--bg4); border:1px solid var(--bd);
  color:var(--t3); font-family:monospace; opacity:.75;
  white-space:nowrap;
}

/* ── Badge inside sub-item (e.g. assigned_reports) ── */
.sub-cnt{
  min-width:18px; height:18px; padding:0 4px;
  background:#ef4444; color:#fff;
  font-size:9px; font-weight:700; border-radius:99px;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0;
}
.sub-cnt--pulse{
  animation:badge-blink 2.5s ease-in-out infinite;
  box-shadow:0 2px 6px rgba(239,68,68,.35);
}

/* ── Sub-group micro-label (e.g. "Users", "Analytics") ── */
.nav-sub-group-label{
  font-size:8.5px; font-weight:800;
  text-transform:uppercase; letter-spacing:.14em;
  color:var(--t3); padding:9px 12px 3px;
  display:flex; align-items:center; gap:5px;
  opacity:.55;
  line-height:1;
}

/* ── Settings dropdown action buttons ── */
.sb-sub-btn{
  display:flex; align-items:center; gap:9px;
  width:100%; padding:7px 10px 7px 12px;
  border-radius:var(--rad-sm);
  color:var(--t3); font-size:11.5px; font-weight:500;
  cursor:pointer; transition:all var(--dur); border:1px solid transparent;
  background:none; text-align:left; margin:1px 0;
  position:relative;
}
.sb-sub-btn::before{
  content:'';
  position:absolute; left:-13px; top:50%;
  transform:translateY(-50%);
  width:5px; height:5px; border-radius:50%;
  background:var(--bd);
  transition:background var(--dur), transform var(--dur) var(--spring);
}
.sb-sub-btn:hover{
  color:var(--t1); background:var(--bg3); border-color:var(--bd);
  transform:translateX(2px);
}
.sb-sub-btn:hover::before{ background:var(--ac); transform:translateY(-50%) scale(1.3); }
.sb-sub-btn i{ font-size:10px; width:15px; flex-shrink:0; transition:transform .15s var(--spring); }
.sb-sub-btn:hover i{ transform:scale(1.15); }
.sb-sub-sep{
  height:1px;
  background:linear-gradient(90deg,transparent,var(--bd),transparent);
  margin:5px -2px; opacity:.6;
}

/* ── Roles mini-panel (inside Roles & Access) ── */
.roles-mini-panel{
  margin:3px 2px 5px;
  padding:8px 10px;
  border-radius:var(--rad-sm);
  background:linear-gradient(135deg,var(--bg4),var(--bg3));
  border:1px solid var(--bd);
  transition:border-color var(--dur) var(--ease), box-shadow var(--dur);
}
.roles-mini-panel:hover{
  border-color:var(--ac-20);
  box-shadow:0 2px 12px rgba(var(--ac-rgb),.08);
}
.roles-mini-panel__row{ display:flex; flex-wrap:wrap; gap:5px; }

/* Role chip */
.rmp-chip{
  display:inline-flex; align-items:center; gap:4px;
  padding:3px 7px; border-radius:99px;
  background:var(--bg2); border:1px solid var(--bd);
  font-size:9.5px; font-weight:600; color:var(--t2);
  cursor:default;
  transition:all var(--dur) var(--spring);
  animation:chip-pop .25s var(--spring) both;
}
.rmp-chip:nth-child(1){ animation-delay:.04s; }
.rmp-chip:nth-child(2){ animation-delay:.09s; }
.rmp-chip:nth-child(3){ animation-delay:.14s; }
.rmp-chip:nth-child(4){ animation-delay:.19s; }
@keyframes chip-pop{
  from{ opacity:0; transform:scale(.82) translateY(5px); }
  to  { opacity:1; transform:scale(1) translateY(0);     }
}
.rmp-chip:hover{
  border-color:var(--ac-20); background:var(--ac-10); color:var(--ac);
  transform:translateY(-2px) scale(1.04);
  box-shadow:0 3px 12px rgba(var(--ac-rgb),.18);
}
.rmp-chip__dot{
  width:6px; height:6px; border-radius:50%; flex-shrink:0;
  box-shadow:0 0 4px currentColor; opacity:.9;
}
.rmp-chip__label{ line-height:1; }
.rmp-chip__count{
  font-size:8px; font-weight:700; padding:1px 4px; border-radius:99px;
  background:rgba(var(--ac-rgb),.12); color:var(--ac); line-height:1.5;
}

/* ── Quick-assign button ── */
.roles-quick-assign{
  display:flex; align-items:center; gap:8px;
  width:100%; margin:5px 2px 2px;
  padding:8px 10px; border-radius:var(--rad-sm);
  background:linear-gradient(135deg,rgba(var(--ac-rgb),.07),rgba(var(--ac-rgb),.03));
  border:1px dashed rgba(var(--ac-rgb),.3);
  color:var(--ac); font-size:11px; font-weight:600;
  cursor:pointer; transition:all var(--dur) var(--spring);
  text-align:left; font-family:inherit;
  position:relative; overflow:hidden;
}
/* Shimmer sweep on hover */
.roles-quick-assign::after{
  content:'';
  position:absolute; inset:0;
  background:linear-gradient(90deg,transparent 0%,rgba(var(--ac-rgb),.1) 50%,transparent 100%);
  transform:translateX(-100%);
  transition:transform .45s var(--ease);
}
.roles-quick-assign:hover::after{ transform:translateX(100%); }
.roles-quick-assign:hover{
  background:var(--ac-10); border-color:var(--ac); border-style:solid;
  transform:translateX(3px);
  box-shadow:0 3px 14px rgba(var(--ac-rgb),.22);
}
.roles-quick-assign i:first-child{ font-size:11px; flex-shrink:0; }
.roles-quick-assign span{ flex:1; }
.roles-quick-assign__arr{
  font-size:9px; opacity:.5;
  transition:transform var(--dur) var(--spring), opacity var(--dur);
}
.roles-quick-assign:hover .roles-quick-assign__arr{ transform:translateX(4px); opacity:1; }

/* ── Impersonation (enhanced) ── */
.sb-impersonate{
  margin:8px 4px; padding:10px 12px;
  border-radius:var(--rad); border:1px solid rgba(239,68,68,.35);
  background:linear-gradient(135deg,rgba(239,68,68,.07),rgba(239,68,68,.03));
  animation:imp-glow 2.5s ease-in-out infinite;
  position:relative; overflow:hidden;
}
.sb-impersonate::before{
  content:''; position:absolute; top:-20px; right:-20px;
  width:60px; height:60px; border-radius:50%;
  background:radial-gradient(circle,rgba(239,68,68,.15) 0%,transparent 70%);
  pointer-events:none; animation:orb1 8s ease-in-out infinite alternate;
}
@keyframes imp-glow{0%,100%{border-color:rgba(239,68,68,.35)}50%{border-color:rgba(239,68,68,.65)}}
.sbi-head{ display:flex; align-items:center; gap:6px; font-size:11px; font-weight:700; color:#ef4444; margin-bottom:4px; }
.sbi-head__dot{ width:7px; height:7px; border-radius:50%; background:#ef4444; flex-shrink:0; animation:pulse-live 1.5s ease-in-out infinite; }
.sbi-name{ font-size:10px; color:rgba(239,68,68,.7); margin-bottom:8px; padding-left:13px; }
.sbi-stop{
  display:flex; align-items:center; justify-content:center; gap:6px;
  width:100%; padding:7px; background:#ef4444; color:#fff;
  font-size:11px; font-weight:700; border-radius:var(--rad-sm);
  border:none; cursor:pointer; text-decoration:none; transition:all var(--dur);
  box-shadow:0 3px 10px rgba(239,68,68,.3);
}
.sbi-stop:hover{ background:#dc2626; transform:translateY(-1px); box-shadow:0 5px 14px rgba(239,68,68,.4); }

/* Sidebar Footer */
.sb-foot{
  flex-shrink:0;padding:8px;border-top:1px solid var(--bd);
  display:flex;flex-direction:column;gap:2px;
}
.sb-expand-btn{
  width:100%;display:flex;align-items:center;justify-content:center;
  padding:10px;border-radius:var(--rad-sm);background:none;
  border:1px solid transparent;color:var(--t3);cursor:pointer;transition:all var(--dur);
}
.sb-expand-btn:hover{background:var(--bg3);border-color:var(--bd);color:var(--t1);}
.sb-foot-btn{
  display:flex;align-items:center;gap:9px;width:100%;
  padding:9px 10px;border-radius:var(--rad-sm);
  background:none;border:1px solid transparent;
  color:var(--t2);font-size:12.5px;font-weight:500;
  cursor:pointer;transition:all var(--dur);text-decoration:none;text-align:left;
}
.sb-foot-btn:hover{background:var(--bg3);border-color:var(--bd);color:var(--t1);}
.sb-foot-btn.is-ctr{justify-content:center;}
.sb-foot-btn--out{color:#ef4444;}
.sb-foot-btn--out:hover{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2);}
.sb-foot-btn__ic {font-size:14px;flex-shrink:0;width:20px;text-align:center;}
.sb-foot-btn__txt{white-space:nowrap;}

/* ════════════════════════════════════════════════════════════
   MOBILE VEIL
════════════════════════════════════════════════════════════ */
.rg-mob-veil{position:fixed;inset:0;z-index:40;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);}
@media(min-width:1024px){.rg-mob-veil{display:none;}}

/* ════════════════════════════════════════════════════════════
   MAIN AREA
════════════════════════════════════════════════════════════ */
.rg-main{
  flex:1;min-width:0;display:flex;flex-direction:column;min-height:100dvh;
  position:relative;z-index:1;
  transition:margin-left var(--dur) var(--ease);
}
@media(min-width:1024px){
  .rg-main      {margin-left:var(--sw);}
  .rg-main.is-coll{margin-left:72px;}
}

/* ════════════════════════════════════════════════════════════
   TOPBAR
════════════════════════════════════════════════════════════ */
.rg-topbar{
  z-index:40;height:var(--tb-h);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 14px;gap:10px;
  background:var(--glass);
  backdrop-filter:blur(20px) saturate(160%);
  -webkit-backdrop-filter:blur(20px) saturate(160%);
  border-bottom:1px solid var(--bd);
}
.rg-topbar.is-sticky{position:sticky;top:0;}
@media(min-width:640px){.rg-topbar{padding:0 22px;}}

.tb-left{display:flex;align-items:center;gap:10px;flex:1;min-width:0;}
.tb-right{display:flex;align-items:center;gap:4px;flex-shrink:0;}
@media(min-width:640px){.tb-right{gap:6px;}}

.tb-ham{
  width:36px;height:36px;border-radius:var(--rad-sm);
  display:flex;align-items:center;justify-content:center;
  background:var(--bg3);border:1px solid var(--bd);color:var(--t2);
  cursor:pointer;font-size:14px;flex-shrink:0;transition:all var(--dur);
}
.tb-ham:hover{color:var(--ac);border-color:var(--ac-20);background:var(--ac-10);}
@media(min-width:1024px){.tb-ham{display:none;}}

.tb-bc{display:flex;align-items:center;gap:8px;min-width:0;}
.tb-bc__dot{flex-shrink:0;width:7px;height:7px;border-radius:50%;background:var(--ac);box-shadow:0 0 10px rgba(var(--ac-rgb),.6);}
.tb-bc__title{font-size:14px;font-weight:700;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:-.2px;}

/* Search */
.tb-search{
  display:none;align-items:center;gap:8px;
  padding:7px 11px;border-radius:var(--rad-sm);
  background:var(--bg3);border:1px solid var(--bd);color:var(--t3);
  font-size:12px;cursor:pointer;transition:all var(--dur);font-family:inherit;
}
.tb-search:hover{border-color:var(--ac-20);background:var(--ac-10);color:var(--t2);}
@media(min-width:768px){.tb-search{display:flex;}}
.tb-search__hint{min-width:115px;text-align:left;}
.tb-search__kbd{font-size:10px;padding:1px 5px;border-radius:3px;background:var(--bg);border:1px solid var(--bd);color:var(--t3);font-family:monospace;}

/* Icon buttons */
.tb-icn-btn{
  position:relative;width:36px;height:36px;border-radius:var(--rad-sm);
  display:flex;align-items:center;justify-content:center;
  background:var(--bg3);border:1px solid var(--bd);color:var(--t2);
  cursor:pointer;font-size:14px;flex-shrink:0;transition:all var(--dur);
}
.tb-icn-btn:hover{color:var(--ac);background:var(--ac-10);border-color:var(--ac-20);}
.tb-dot{
  position:absolute;top:-3px;right:-3px;
  min-width:16px;height:16px;padding:0 3px;
  background:#ef4444;color:#fff;font-size:9px;font-weight:700;border-radius:99px;
  display:flex;align-items:center;justify-content:center;
  border:2px solid var(--bg2);animation:badge-blink 2.5s ease-in-out infinite;
}

/* CTA */
.tb-cta{
  display:flex;align-items:center;gap:6px;
  padding:8px 14px;border-radius:var(--rad-sm);
  background:var(--ac-grad);color:#fff;
  font-size:12px;font-weight:700;font-family:inherit;
  text-decoration:none;white-space:nowrap;flex-shrink:0;
  transition:all var(--dur);border:none;cursor:pointer;
  box-shadow:0 4px 18px rgba(var(--ac-rgb),.38);
}
.tb-cta:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(var(--ac-rgb),.52);}
.tb-cta:active{transform:translateY(0);}
.tb-cta span{display:none;}
@media(min-width:480px){.tb-cta span{display:inline;}}

/* ════════════════════════════════════════════════════════════
   NOTIFICATIONS DROPDOWN
════════════════════════════════════════════════════════════ */
.tb-notif{position:relative;}
.nd-box{
  position:absolute;right:0;top:calc(100% + 8px);
  width:min(365px,calc(100vw - 14px));
  background:var(--bg2);border:1px solid var(--bd);
  border-radius:var(--rad-lg);box-shadow:var(--shx);overflow:hidden;z-index:60;
}
.nd-head{display:flex;align-items:center;justify-content:space-between;padding:13px 15px;border-bottom:1px solid var(--bd);}
.nd-head__title{font-size:13px;font-weight:700;color:var(--t1);}
.nd-head__sub  {font-size:11px;color:var(--t3);margin-top:1px;}
.nd-head__actions{display:flex;align-items:center;gap:8px;}
.nd-mark-all{font-size:11px;font-weight:600;color:var(--ac);background:none;border:none;cursor:pointer;font-family:inherit;}
.nd-refresh{width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:var(--rad-sm);background:var(--bg3);border:1px solid var(--bd);color:var(--t3);cursor:pointer;font-size:11px;transition:all var(--dur);}
.nd-refresh:hover{color:var(--ac);}
.nd-refresh.is-spin i{animation:spin .8s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

.nd-list{max-height:300px;overflow-y:auto;scrollbar-width:thin;}
.nd-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:36px 16px;gap:8px;color:var(--t3);font-size:12px;text-align:center;}
.nd-state__ico{font-size:22px;}
.nd-state__ico--err{color:#ef4444;}
.nd-retry{font-size:11px;font-weight:600;color:var(--ac);background:none;border:none;cursor:pointer;font-family:inherit;}

.nd-item{display:flex;align-items:flex-start;gap:10px;padding:11px 15px;border-bottom:1px solid var(--bd2);cursor:pointer;transition:background var(--dur);}
.nd-item:last-child{border:none;}
.nd-item:hover{background:var(--bg3);}
.nd-item.is-unread{background:rgba(var(--ac-rgb),.04);}
.nd-item__ico{flex-shrink:0;width:34px;height:34px;border-radius:var(--rad-sm);background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:12px;}
.nd-item__ico.is-unread{background:var(--ac-10);}
.nd-item__body{flex:1;min-width:0;}
.nd-item__title{font-size:12px;font-weight:600;color:var(--t1);line-height:1.4;}
.nd-item__msg  {font-size:11px;color:var(--t2);margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.nd-item__meta {display:flex;align-items:center;gap:6px;margin-top:5px;font-size:10px;color:var(--t3);}
.nd-item__unread-dot{flex-shrink:0;width:7px;height:7px;border-radius:50%;background:var(--ac);margin-top:5px;}

.nd-tag{font-size:9px;font-weight:700;padding:1px 5px;border-radius:3px;}
.nt--indigo {background:rgba(99,102,241,.1);color:#6366f1;}
.nt--emerald{background:rgba(16,185,129,.1);color:#10b981;}
.nt--red    {background:rgba(239,68,68,.1); color:#ef4444;}
.nt--violet {background:rgba(139,92,246,.1);color:#8b5cf6;}
.nt--sky    {background:rgba(14,165,233,.1);color:#0ea5e9;}
.nt--pink   {background:rgba(236,72,153,.1);color:#ec4899;}
.nt--slate  {background:var(--bg3);color:var(--t3);}

.nd-foot{padding:9px 15px;border-top:1px solid var(--bd);background:var(--bg3);}
.nd-view-all{display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;font-weight:600;color:var(--ac);text-decoration:none;transition:opacity var(--dur);}
.nd-view-all:hover{opacity:.75;}

/* ════════════════════════════════════════════════════════════
   QUICK ACTIONS
════════════════════════════════════════════════════════════ */
.tb-qa{position:relative;}
.qa-box{
  position:absolute;right:0;top:calc(100% + 8px);
  width:230px;background:var(--bg2);border:1px solid var(--bd);
  border-radius:var(--rad-lg);box-shadow:var(--shx);overflow:hidden;z-index:60;padding:6px;
}
.qa-box__lbl{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:var(--t3);padding:4px 8px 6px;display:block;}
.qa-item{
  display:flex;align-items:center;gap:10px;width:100%;
  padding:8px 8px;border-radius:var(--rad-sm);
  color:var(--t1);font-size:12.5px;text-decoration:none;
  cursor:pointer;transition:all var(--dur);border:none;background:none;text-align:left;font-family:inherit;
}
.qa-item:hover{background:var(--bg3);}
.qa-item b    {display:block;font-size:12px;font-weight:600;}
.qa-item small{display:block;font-size:10px;color:var(--t3);margin-top:1px;}
.qa-item__ico {flex-shrink:0;width:32px;height:32px;border-radius:var(--rad-sm);display:flex;align-items:center;justify-content:center;font-size:13px;}
.qa-item__ico--ac{background:rgba(var(--ac-rgb),.15);color:var(--ac);}
.qa-sep{height:1px;background:var(--bd);margin:4px 0;}

/* ════════════════════════════════════════════════════════════
   PAGE CONTENT & FOOTER
════════════════════════════════════════════════════════════ */
.rg-page{flex:1;padding:14px;}
@media(min-width:640px){.rg-page{padding:22px;}}
.rg-compact .rg-page{padding:10px 12px;}

.rg-mob-head{display:flex;align-items:center;gap:8px;margin-bottom:14px;}
@media(min-width:1024px){.rg-mob-head{display:none;}}

.rg-footer{
  padding:10px 22px;border-top:1px solid var(--bd);
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;
  font-size:11px;color:var(--t3);
}
.rg-footer__links{display:flex;gap:12px;}
.rg-footer__link{font-size:11px;color:var(--t3);text-decoration:none;background:none;border:none;cursor:pointer;font-family:inherit;transition:color var(--dur);}
.rg-footer__link:hover{color:var(--ac);}

/* ════════════════════════════════════════════════════════════
   MODALS
════════════════════════════════════════════════════════════ */
.rg-overlay{
  position:fixed;inset:0;z-index:70;
  display:flex;align-items:center;justify-content:center;padding:14px;
  background:rgba(0,0,0,.65);backdrop-filter:blur(10px);
}
.rg-overlay--top{align-items:flex-start;padding-top:10vh;}

.rg-modal{
  position:relative;width:100%;background:var(--bg2);
  border:1px solid var(--bd);border-radius:var(--rad-lg);box-shadow:var(--shx);overflow:hidden;
}
.rg-modal--sm{max-width:430px;}
.rg-modal--lg{max-width:680px;max-height:90dvh;display:flex;flex-direction:column;}

.rg-modal__head{
  display:flex;align-items:center;gap:12px;
  padding:16px 18px;border-bottom:1px solid var(--bd);flex-shrink:0;
}
.rg-modal__icon{
  width:38px;height:38px;flex-shrink:0;border-radius:var(--rad-sm);
  background:var(--ac-grad);color:#fff;font-size:14px;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 14px rgba(var(--ac-rgb),.35);
}
.rg-modal__title{font-size:14.5px;font-weight:800;color:var(--t1);letter-spacing:-.2px;}
.rg-modal__sub  {font-size:11px;color:var(--t3);margin-top:1px;}
.rg-modal__close{
  margin-left:auto;flex-shrink:0;
  width:30px;height:30px;border-radius:var(--rad-sm);
  background:var(--bg3);border:1px solid var(--bd);color:var(--t3);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:12px;transition:all var(--dur);
}
.rg-modal__close:hover{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#ef4444;}

/* Shortcuts */
.sc-list{padding:8px;}
.sc-row{display:flex;align-items:center;justify-content:space-between;padding:9px 10px;border-radius:var(--rad-sm);transition:background var(--dur);}
.sc-row:hover{background:var(--bg3);}
.sc-row__desc{display:flex;align-items:center;gap:10px;font-size:12.5px;color:var(--t2);}
.sc-row__icon{font-size:11px;width:14px;color:var(--ac);}
.sc-kbd{font-size:10.5px;padding:3px 8px;border-radius:5px;background:var(--bg3);border:1px solid var(--bd);color:var(--t2);font-family:monospace;white-space:nowrap;}

/* ════════════════════════════════════════════════════════════
   SETTINGS MODAL INTERIOR
════════════════════════════════════════════════════════════ */
.s-body{display:flex;flex:1;min-height:0;}

.s-rail{
  width:128px;flex-shrink:0;padding:10px 8px;
  border-right:1px solid var(--bd);
  display:flex;flex-direction:column;gap:2px;overflow-y:auto;
}
@media(min-width:500px){.s-rail{width:150px;}}

.s-tab{
  display:flex;align-items:center;gap:8px;
  padding:9px 10px;border-radius:var(--rad-sm);
  font-size:12px;font-weight:500;color:var(--t2);
  cursor:pointer;transition:all var(--dur);border:1px solid transparent;background:none;text-align:left;
}
.s-tab:hover{background:var(--bg3);}
.s-tab.is-on{color:var(--ac);background:var(--ac-10);border-color:var(--ac-20);font-weight:600;}
.s-tab__icon {width:16px;text-align:center;font-size:12px;flex-shrink:0;}
.s-tab__label{flex:1;}
.s-tab__badge{min-width:16px;height:16px;padding:0 3px;background:#ef4444;color:#fff;font-size:8px;font-weight:700;border-radius:99px;display:flex;align-items:center;justify-content:center;}

.s-panels{flex:1;overflow-y:auto;padding:16px 18px;scrollbar-width:thin;}
.s-panel{display:flex;flex-direction:column;gap:20px;}
.s-group{display:flex;flex-direction:column;gap:10px;}
.s-group__label{
  font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;
  color:var(--t3);display:flex;align-items:center;gap:6px;
  padding-bottom:4px;border-bottom:1px solid var(--bd);
}

/* Theme cards */
.theme-row{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
.theme-card{
  border-radius:var(--rad);border:2px solid var(--bd);background:var(--bg3);
  cursor:pointer;overflow:hidden;transition:all var(--dur) var(--spring);position:relative;
  display:flex;flex-direction:column;
}
.theme-card:hover{border-color:var(--ac-40);transform:translateY(-2px);box-shadow:var(--sh);}
.theme-card.is-on {border-color:var(--ac);box-shadow:0 0 0 2px rgba(var(--ac-rgb),.2);}
.theme-card__mock{height:52px;display:flex;overflow:hidden;border-radius:3px 3px 0 0;}
.mock--light{background:#eff2f8;}
.mock--dark {background:#07090f;}
.mock--system{background:linear-gradient(90deg,#07090f 50%,#eff2f8 50%);}
.mock-sb    {width:30%;background:rgba(var(--ac-rgb),.2);}
.mock-body  {flex:1;display:flex;flex-direction:column;}
.mock-topbar{height:10px;background:rgba(var(--ac-rgb),.12);}
.mock-content{flex:1;padding:4px;display:flex;flex-direction:column;gap:3px;}
.mock-block  {height:8px;border-radius:2px;background:rgba(var(--ac-rgb),.15);}
.mock-block--sm{width:60%;}
.theme-card__label{display:flex;align-items:center;justify-content:center;gap:5px;padding:6px;font-size:11px;font-weight:600;color:var(--t2);}
.theme-card__check{position:absolute;top:4px;right:4px;width:18px;height:18px;border-radius:50%;background:var(--ac);color:#fff;font-size:9px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(var(--ac-rgb),.4);}

/* Accent */
.accent-swatches{display:flex;flex-wrap:wrap;gap:8px;}
.swatch{
  width:32px;height:32px;border-radius:50%;
  background:var(--sw-col);border:3px solid transparent;
  cursor:pointer;display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:11px;transition:all var(--dur) var(--spring);
  box-shadow:0 2px 8px rgba(0,0,0,.18);
}
.swatch:hover{transform:scale(1.18);}
.swatch.is-on{border-color:#fff;box-shadow:0 0 0 3px var(--sw-col),0 2px 8px rgba(0,0,0,.18);transform:scale(1.1);}

.accent-preview{
  display:flex;align-items:center;flex-wrap:wrap;gap:10px;
  padding:10px 12px;border-radius:var(--rad);border:1px solid var(--bd);background:var(--bg3);
}
.ap-tag  {font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--t3);}
.ap-btn  {padding:5px 12px;background:var(--ac-grad);color:#fff;font-size:11px;font-weight:700;border-radius:var(--rad-sm);border:none;cursor:default;box-shadow:0 3px 10px rgba(var(--ac-rgb),.4);font-family:inherit;}
.ap-link {font-size:12px;font-weight:600;color:var(--ac);cursor:default;}
.ap-badge{padding:2px 8px;background:var(--ac-10);color:var(--ac);border-radius:99px;font-size:10px;font-weight:700;border:1px solid var(--ac-20);}
.ap-dot  {width:22px;height:22px;border-radius:50%;border:2px solid var(--ac);}

/* Typography */
.s-2col{display:grid;grid-template-columns:1fr;gap:14px;}
@media(min-width:480px){.s-2col{grid-template-columns:1fr 1fr;}}
.s-field{display:flex;flex-direction:column;gap:8px;}
.s-label{font-size:11px;font-weight:700;color:var(--t2);display:flex;align-items:center;justify-content:space-between;}
.s-val  {font-size:12px;font-weight:700;color:var(--ac);}
.s-select{
  width:100%;padding:8px 10px;border-radius:var(--rad-sm);
  border:1px solid var(--bd);background:var(--bg3);color:var(--t1);
  font-size:12px;font-family:inherit;outline:none;cursor:pointer;
  transition:border-color var(--dur);
}
.s-select:focus{border-color:var(--ac);}
.font-preview{
  font-size:11px;color:var(--t3);padding:6px 10px;
  background:var(--bg3);border-radius:var(--rad-sm);border:1px solid var(--bd);line-height:1.6;
  /* fontFamily is set via :style directly so it shows the selected font live */
}
.s-range{width:100%;accent-color:var(--ac);cursor:pointer;}
.range-ticks{display:flex;justify-content:space-between;padding:0 2px;}
.range-ticks span{font-size:9px;color:var(--t3);transition:color var(--dur);}
.range-ticks span.is-active{color:var(--ac);font-weight:700;}

/* Radius preview */
.radius-preview{display:flex;gap:8px;margin-top:4px;}
.rp-box{
  width:38px;height:38px;background:var(--ac-10);border:2px solid var(--ac-20);
  display:flex;align-items:center;justify-content:center;
  font-size:10px;font-weight:700;color:var(--ac);transition:border-radius var(--dur);
}
.rp-box--sm  {width:30px;height:26px;font-size:9px;}
.rp-box--pill{padding:0 10px;width:auto;}

/* Sidebar widths */
.sw-btns{display:flex;gap:6px;}
.sw-btn{
  flex:1;border-radius:var(--rad-sm);border:2px solid var(--bd);background:var(--bg3);
  cursor:pointer;padding:8px 6px;
  display:flex;flex-direction:column;align-items:center;gap:5px;
  font-size:10px;font-weight:600;color:var(--t2);transition:all var(--dur);font-family:inherit;
}
.sw-btn:hover{border-color:var(--ac-40);color:var(--ac);}
.sw-btn.is-on {border-color:var(--ac);background:var(--ac-10);color:var(--ac);}
.sw-btn__mock{display:flex;gap:2px;width:48px;height:28px;border-radius:3px;overflow:hidden;background:var(--bg4);}
.sw-btn__sb  {background:rgba(var(--ac-rgb),.28);flex-shrink:0;}
.sw-btn__body{flex:1;background:rgba(var(--ac-rgb),.08);}

/* Effects */
.fx-row{display:flex;flex-wrap:wrap;gap:6px;}
.fx-btn{
  display:flex;align-items:center;gap:7px;padding:7px 11px;
  border-radius:var(--rad-sm);border:1px solid var(--bd);background:var(--bg3);
  font-size:11.5px;font-weight:500;color:var(--t2);cursor:pointer;transition:all var(--dur);font-family:inherit;
}
.fx-btn:hover{border-color:var(--ac-20);color:var(--ac);}
.fx-btn.is-on{border-color:var(--ac);background:var(--ac-10);color:var(--ac);font-weight:600;}
.fx-dot{width:7px;height:7px;border-radius:50%;background:var(--bd);transition:all var(--dur);margin-left:2px;}
.fx-dot.is-on{background:var(--ac);box-shadow:0 0 6px rgba(var(--ac-rgb),.5);}

/* Preferences */
.pref-list{display:flex;flex-direction:column;gap:6px;}
.pref-row{
  display:flex;align-items:center;gap:12px;
  padding:11px 12px;border-radius:var(--rad);border:1px solid var(--bd);background:var(--bg3);
  transition:border-color var(--dur);
}
.pref-row:hover{border-color:var(--ac-20);}
.pref-row__left{display:flex;align-items:center;gap:10px;flex:1;min-width:0;}
.pref-row__icon{flex-shrink:0;width:30px;height:30px;border-radius:var(--rad-sm);background:var(--ac-10);color:var(--ac);display:flex;align-items:center;justify-content:center;font-size:11px;}
.pref-row__title{font-size:12.5px;font-weight:600;color:var(--t1);}
.pref-row__desc {font-size:11px;color:var(--t3);margin-top:1px;}

.shortcut-teaser{
  display:flex;align-items:center;gap:12px;width:100%;
  padding:11px 12px;border-radius:var(--rad);border:1px solid var(--bd);background:var(--bg3);
  cursor:pointer;transition:all var(--dur);text-align:left;font-family:inherit;
}
.shortcut-teaser:hover{border-color:var(--ac-20);}
.teaser-cta{display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--ac);white-space:nowrap;}

/* Toggle */
.rg-toggle{
  flex-shrink:0;width:42px;height:23px;border-radius:99px;
  background:var(--bd);border:2px solid var(--bd);cursor:pointer;
  transition:all .25s var(--ease);position:relative;padding:0;
}
.rg-toggle.is-on{background:var(--ac);border-color:var(--ac);}
.rg-toggle__knob{
  display:block;width:17px;height:17px;border-radius:50%;
  background:var(--t3);position:absolute;top:1px;left:1px;
  transition:transform .25s var(--spring),background .2s;
  box-shadow:0 1px 4px rgba(0,0,0,.2);
}
.rg-toggle.is-on .rg-toggle__knob{transform:translateX(19px);background:#fff;}

/* Notifications settings */
.delay-pills{display:flex;gap:6px;flex-wrap:wrap;}
.delay-pill{
  padding:5px 10px;border-radius:var(--rad-sm);border:1px solid var(--bd);
  background:var(--bg3);font-size:11px;font-weight:600;color:var(--t2);
  cursor:pointer;transition:all var(--dur);font-family:inherit;
}
.delay-pill:hover{border-color:var(--ac-20);color:var(--ac);}
.delay-pill.is-on {border-color:var(--ac);background:var(--ac-10);color:var(--ac);}
.notif-tip{
  display:flex;gap:10px;padding:11px 12px;border-radius:var(--rad);
  border:1px solid rgba(var(--ac-rgb),.2);background:rgba(var(--ac-rgb),.04);
  font-size:12px;color:var(--t2);
}
.notif-tip i{flex-shrink:0;color:var(--ac);margin-top:1px;}
.notif-tip__link{color:var(--ac);font-weight:600;text-decoration:none;}

/* Security */
.acct-link{
  display:flex;align-items:center;gap:12px;padding:12px 14px;
  border-radius:var(--rad);border:1px solid var(--bd);background:var(--bg3);
  color:var(--t1);text-decoration:none;transition:all var(--dur);
}
.acct-link:hover{border-color:var(--ac-20);background:var(--ac-10);}
.acct-link__icon {font-size:16px;width:20px;color:var(--ac);}
.acct-link__arrow{margin-left:auto;font-size:11px;color:var(--t3);}
.danger-btn{
  display:flex;align-items:center;gap:8px;padding:10px 14px;
  border-radius:var(--rad);border:1px solid rgba(239,68,68,.3);
  background:rgba(239,68,68,.05);color:#ef4444;
  font-size:12px;font-weight:600;cursor:pointer;transition:all var(--dur);font-family:inherit;
}
.danger-btn:hover{background:rgba(239,68,68,.12);}

/* About */
.about-wrap{display:flex;flex-direction:column;align-items:center;text-align:center;padding:20px 0;}
.about-logo{width:64px;height:64px;border-radius:var(--rad-lg);background:var(--ac-grad);display:flex;align-items:center;justify-content:center;color:#fff;font-size:28px;box-shadow:0 8px 28px rgba(var(--ac-rgb),.44);margin-bottom:12px;}
.about-name  {font-size:18px;font-weight:800;color:var(--t1);letter-spacing:-.4px;}
.about-tagline{font-size:12px;color:var(--t3);margin-top:4px;}
.about-badges{display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-top:12px;}
.about-badges span{font-size:10px;font-weight:700;padding:3px 8px;border-radius:99px;border:1px solid var(--bd);background:var(--bg3);color:var(--t2);}
.about-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}
.about-card{display:flex;flex-direction:column;align-items:center;gap:5px;padding:12px 8px;border-radius:var(--rad);border:1px solid var(--bd);background:var(--bg3);font-size:11px;color:var(--t2);font-weight:600;}
.about-card__icon{font-size:18px;color:var(--ac);}
.about-copy{text-align:center;font-size:11px;color:var(--t3);}

/* Save button */
.s-save{
  display:flex;align-items:center;justify-content:center;gap:7px;
  width:100%;padding:11px;background:var(--ac-grad);color:#fff;
  font-size:13px;font-weight:700;border-radius:var(--rad);border:none;cursor:pointer;
  transition:all var(--dur);box-shadow:0 4px 18px rgba(var(--ac-rgb),.38);font-family:inherit;
}
.s-save:hover{transform:translateY(-1px);box-shadow:0 6px 22px rgba(var(--ac-rgb),.5);}
.s-save:active{transform:translateY(0);}

/* ════════════════════════════════════════════════════════════
   SEARCH PALETTE
════════════════════════════════════════════════════════════ */
.pal-box{
  width:100%;max-width:540px;
  background:var(--bg2);border:1px solid var(--bd);
  border-radius:var(--rad-lg);box-shadow:var(--shx);overflow:hidden;
}
.pal-input-row{display:flex;align-items:center;gap:10px;padding:14px 15px;border-bottom:1px solid var(--bd);}
.pal-icon {font-size:14px;color:var(--t3);flex-shrink:0;}
.pal-input{flex:1;background:transparent;border:none;outline:none;font-size:14px;color:var(--t1);font-family:inherit;}
.pal-input::placeholder{color:var(--t3);}
.pal-esc  {font-size:10px;padding:2px 6px;border-radius:3px;background:var(--bg3);border:1px solid var(--bd);color:var(--t3);font-family:monospace;}

.pal-cats{display:flex;flex-wrap:wrap;gap:6px;padding:12px 14px;border-bottom:1px solid var(--bd);}
.pal-cat{
  display:flex;align-items:center;gap:6px;padding:5px 10px;
  border-radius:99px;border:1px solid var(--bd);background:var(--bg3);
  font-size:11px;font-weight:600;color:var(--t2);cursor:pointer;
  transition:all var(--dur);font-family:inherit;
}
.pal-cat:hover{border-color:var(--ac-20);color:var(--ac);background:var(--ac-10);}
.pal-cat i{font-size:10px;}

.pal-results{max-height:300px;overflow-y:auto;padding:6px;}
.pal-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:36px 16px;gap:10px;color:var(--t3);font-size:13px;}
.pal-empty i{font-size:28px;}
.pal-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:var(--rad-sm);cursor:pointer;transition:background var(--dur);}
.pal-item:hover,.pal-item.is-on{background:var(--bg3);}
.pal-item__icon{width:34px;height:34px;flex-shrink:0;border-radius:var(--rad-sm);display:flex;align-items:center;justify-content:center;font-size:13px;}
.pal-item__info{flex:1;min-width:0;}
.pal-item__title{display:block;font-size:12.5px;font-weight:600;color:var(--t1);}
.pal-item__sub  {display:block;font-size:11px;color:var(--t3);margin-top:1px;}
.pal-item__type {font-size:10px;font-weight:700;padding:2px 7px;border-radius:3px;background:var(--bg3);border:1px solid var(--bd);color:var(--t3);white-space:nowrap;flex-shrink:0;}
.pal-item__enter{font-size:12px;color:var(--t3);flex-shrink:0;}
.pal-footer{display:flex;gap:16px;justify-content:center;padding:8px;border-top:1px solid var(--bd);background:var(--bg3);font-size:11px;color:var(--t3);}
.pal-footer kbd{font-size:10px;padding:1px 4px;border-radius:3px;background:var(--bg2);border:1px solid var(--bd);margin-right:3px;}

/* ════════════════════════════════════════════════════════════
   TOAST
════════════════════════════════════════════════════════════ */
.rg-toast{
  position:fixed;bottom:20px;right:20px;z-index:100;
  display:flex;align-items:center;gap:8px;
  padding:11px 16px;border-radius:var(--rad);color:#fff;
  font-size:13px;font-weight:600;font-family:inherit;
  box-shadow:0 8px 28px rgba(0,0,0,.25);
  opacity:0;transform:translateY(12px) scale(.96);
  transition:all .28s var(--spring);
}
.rg-toast--in{opacity:1;transform:translateY(0) scale(1);}
.rg-toast--success{background:linear-gradient(135deg,#059669,#10b981);}
.rg-toast--error  {background:linear-gradient(135deg,#dc2626,#ef4444);}
.rg-toast--info   {background:var(--ac-grad);}

/* ════════════════════════════════════════════════════════════
   TRANSITIONS
════════════════════════════════════════════════════════════ */
/* Modal */
.rg-modal-enter-active { transition:opacity .22s var(--ease); }
.rg-modal-leave-active { transition:opacity .15s var(--ease); }
.rg-modal-enter-from,
.rg-modal-leave-to     { opacity:0; }
.rg-modal-enter-active .rg-modal,
.rg-modal-enter-active .pal-box { transition:transform .22s var(--spring); }
.rg-modal-enter-from .rg-modal,
.rg-modal-enter-from .pal-box   { transform:scale(.96) translateY(-10px); }

/* Palette */
.rg-pal-enter-active { transition:opacity .2s var(--ease); }
.rg-pal-leave-active { transition:opacity .15s; }
.rg-pal-enter-from,.rg-pal-leave-to { opacity:0; }
.rg-pal-enter-active .pal-box { transition:transform .2s var(--spring); }
.rg-pal-enter-from .pal-box   { transform:scale(.96) translateY(-8px); }

/* Fade */
.rg-fade-enter-active,.rg-fade-leave-active{transition:opacity .2s;}
.rg-fade-enter-from,.rg-fade-leave-to{opacity:0;}

/* Dropdown */
.rg-drop-enter-active{transition:all .18s var(--spring);}
.rg-drop-enter-from  {opacity:0;transform:translateY(-6px) scale(.97);}
.rg-drop-leave-active{transition:all .12s var(--ease);}
.rg-drop-leave-to    {opacity:0;transform:translateY(-4px);}

/* Fade-X (sidebar labels) */
.rg-fade-x-enter-active{transition:all .18s var(--ease);}
.rg-fade-x-enter-from  {opacity:0;transform:translateX(-8px);}
.rg-fade-x-leave-active{transition:all .14s var(--ease);}
.rg-fade-x-leave-to    {opacity:0;transform:translateX(-8px);max-width:0;overflow:hidden;white-space:nowrap;}

/* Sub-menu */
.rg-sub-enter-active{transition:all .2s var(--ease);max-height:600px;}
.rg-sub-enter-from  {opacity:0;max-height:0;}
.rg-sub-leave-active{transition:all .16s var(--ease);max-height:600px;}
.rg-sub-leave-to    {opacity:0;max-height:0;overflow:hidden;}

/* ════════════════════════════════════════════════════════════
   UTILITY
════════════════════════════════════════════════════════════ */
.rg-no-motion *,.rg-no-motion *::before,.rg-no-motion *::after{
  animation-duration:.01ms !important;transition-duration:.01ms !important;
}
.rg-compact .rg-page      {padding:10px 12px;}
.rg-compact .nav-item     {padding:7px 10px;}
.rg-compact .nav-sub-item {padding:5px 8px;}
.rg-compact .sb-brand     {padding:10px 12px;min-height:54px;}
.rg-compact .sb-user      {padding:8px 12px;}
</style>
<!-- final -->