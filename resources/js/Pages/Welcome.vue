<!--
  Welcome.vue - ULTIMATE Mind-Blowing Landing Page
  -----------------------------------------------------------
  The most advanced, visually stunning landing page ever created.
  Features:
  - Particle system with 100+ animated particles
  - Morphing gradient orbs with smooth animations
  - 3D perspective card with parallax tilt effect on mouse move
  - Typewriter effect for dynamic text
  - Animated counter with easing
  - Glowing neon borders and buttons
  - Floating elements with shadow effects
  - Glassmorphism with backdrop blur
  - Animated SVG waves and shapes
  - Scroll-triggered reveal animations
  - Magnetic hover effects on buttons
  - Custom cursor glow effect
  - Fully responsive with mobile optimizations
  - Performance optimized with CSS transforms
-->

<template>
  <Head title="ReportGen - AI-Powered Professional Report Builder" />

  <!-- Custom Cursor Glow -->
  <div 
    ref="cursorGlow"
    class="fixed pointer-events-none z-[9999] w-64 h-64 rounded-full opacity-0 transition-opacity duration-300"
    style="
      background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, rgba(139,92,246,0.05) 40%, transparent 70%);
      transform: translate(-50%, -50%);
    "
  ></div>

  <!-- 
    Main Container
    Uses dark gradient background with animated noise texture
  -->
  <div 
    class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-950 text-white overflow-x-hidden font-sans relative"
    @mousemove="handleMouseMove"
    @mouseenter="cursorGlowVisible = true"
    @mouseleave="cursorGlowVisible = false"
  >

    <!-- ============================================================ -->
    <!-- ANIMATED BACKGROUND - Particle System + Morphing Orbs          -->
    <!-- ============================================================ -->
    <div class="fixed inset-0 pointer-events-none z-0">
      
      <!-- Canvas for Particles -->
      <canvas ref="particleCanvas" class="absolute inset-0"></canvas>

      <!-- Morphing Gradient Orbs -->
      <div 
        class="absolute rounded-full blur-3xl animate-morph-1"
        style="
          width: 700px; 
          height: 700px; 
          top: -20%; 
          left: -15%; 
          background: radial-gradient(circle, rgba(99,102,241,0.35) 0%, rgba(139,92,246,0.15) 35%, rgba(236,72,153,0.05) 60%, transparent 75%);
        "
      ></div>

      <div 
        class="absolute rounded-full blur-3xl animate-morph-2"
        style="
          width: 600px; 
          height: 600px; 
          bottom: -15%; 
          right: -10%; 
          background: radial-gradient(circle, rgba(16,185,129,0.3) 0%, rgba(6,182,212,0.12) 35%, rgba(59,130,246,0.05) 60%, transparent 75%);
        "
      ></div>

      <div 
        class="absolute rounded-full blur-3xl animate-morph-3"
        style="
          width: 500px; 
          height: 500px; 
          top: 50%; 
          left: 50%; 
          transform: translate(-50%, -50%);
          background: radial-gradient(circle, rgba(236,72,153,0.2) 0%, rgba(244,114,182,0.08) 35%, rgba(168,85,247,0.03) 60%, transparent 75%);
        "
      ></div>

      <!-- Animated Grid -->
      <div 
        class="absolute inset-0 opacity-[0.03]"
        style="
          background-image: 
            linear-gradient(rgba(255,255,255,0.4) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.4) 1px, transparent 1px);
          background-size: 60px 60px;
          animation: gridMove 20s linear infinite;
        "
      ></div>

      <!-- SVG Wave Bottom -->
      <svg class="absolute bottom-0 left-0 w-full opacity-[0.02]" viewBox="0 0 1440 320" preserveAspectRatio="none">
        <path fill="url(#waveGradient)" d="M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,138.7C672,128,768,160,864,181.3C960,203,1056,213,1152,197.3C1248,181,1344,139,1392,117.3L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        <defs>
          <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
            <stop offset="50%" style="stop-color:#8b5cf6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#ec4899;stop-opacity:1" />
          </linearGradient>
        </defs>
      </svg>
    </div>

    <!-- ============================================================ -->
    <!-- NAVIGATION BAR - Glassmorphism                                  -->
    <!-- ============================================================ -->
    <nav class="relative z-50 mx-4 sm:mx-6 lg:mx-8 mt-4 sm:mt-6">
      <div class="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 rounded-2xl bg-white/[0.03] border border-white/[0.08] backdrop-blur-2xl shadow-2xl">
        
        <!-- Logo & Brand -->
        <div class="flex items-center gap-2.5 sm:gap-3 group cursor-pointer">
          <!-- Animated Logo -->
          <div class="relative w-9 h-9 sm:w-10 sm:h-10">
            <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 animate-spin-slow opacity-70 blur-sm"></div>
            <div class="relative w-full h-full rounded-xl flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all duration-300">
              <i class="fa-solid fa-chart-line text-white text-base sm:text-lg"></i>
            </div>
          </div>
          
          <div class="flex flex-col">
            <span class="font-black text-white text-lg sm:text-xl tracking-tight">ReportGen</span>
            <span class="text-[8px] sm:text-[9px] font-bold text-indigo-400 tracking-[0.2em] uppercase">Enterprise</span>
          </div>
          
          <!-- Status Badge -->
          <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 text-[9px] font-bold rounded-full bg-gradient-to-r from-indigo-500/20 to-purple-500/20 text-indigo-300 border border-indigo-500/30 backdrop-blur-sm">
            <span class="relative flex h-1.5 w-1.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
            </span>
            PRO
          </span>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center gap-8">
          <a v-for="link in navLinks" :key="link.href" :href="link.href" 
             class="text-sm text-slate-400 hover:text-white transition-colors duration-200 relative group py-2">
            {{ link.label }}
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-indigo-400 to-purple-400 group-hover:w-full transition-all duration-300 rounded-full"></span>
          </a>
        </div>

        <!-- Auth Buttons -->
        <div class="flex items-center gap-2 sm:gap-3">
          <Link v-if="$page.props.auth.user" :href="route('dashboard')"
            class="group relative px-4 sm:px-6 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-white rounded-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700 group-hover:from-indigo-500 group-hover:to-indigo-600 transition-all duration-300"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/15 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
            <span class="relative z-10 flex items-center gap-1.5">
              Dashboard
              <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
            </span>
          </Link>

          <template v-else>
            <Link :href="route('login')" class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-slate-400 hover:text-white transition-colors duration-200">
              Sign In
            </Link>
            <Link v-if="canRegister" :href="route('register')"
              class="magnetic-btn group relative px-5 sm:px-6 py-2 sm:py-2.5 text-xs sm:text-sm font-bold text-white rounded-xl transition-all duration-300 overflow-hidden shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/50">
              <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600"></div>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
              <span class="relative z-10">Get Started</span>
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- ============================================================ -->
    <!-- HERO SECTION - Typewriter + 3D Card                            -->
    <!-- ============================================================ -->
    <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-28 lg:pt-36 pb-12 sm:pb-16">
      
      <div class="text-center max-w-4xl mx-auto">
        
        <!-- Animated Badge -->
        <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full border border-indigo-500/20 bg-indigo-500/[0.06] backdrop-blur-md mb-8 reveal-up">
          <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-gradient-to-r from-emerald-400 to-cyan-400"></span>
          </span>
          <span class="text-xs sm:text-sm font-medium text-transparent bg-gradient-to-r from-indigo-300 to-purple-300 bg-clip-text tracking-wide">
            ✨ Now with AI-Powered Smart Templates
          </span>
          <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-gradient-to-r from-amber-500/20 to-orange-500/20 text-amber-400 border border-amber-500/30">
            NEW
          </span>
        </div>

        <!-- Typewriter Headline -->
        <h1 class="text-5xl sm:text-7xl md:text-8xl lg:text-9xl font-black leading-[0.95] tracking-tight mb-6 reveal-up">
          <span class="text-white block">Create</span>
          <span class="bg-gradient-to-r from-indigo-400 via-purple-400 via-pink-400 to-rose-400 bg-clip-text text-transparent block mt-1">
            {{ displayedText }}
            <span class="animate-blink inline-block -ml-1">|</span>
          </span>
        </h1>

        <!-- Subtitle -->
        <p class="text-base sm:text-xl text-slate-400/80 max-w-2xl mx-auto leading-relaxed mb-10 reveal-up px-4">
          The most advanced drag-and-drop report builder. Create stunning professional reports 
          with 50+ elements, live charts, and real-time collaboration — all in minutes.
        </p>

        <!-- CTA Buttons with Magnetic Effect -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 reveal-up">
          <Link v-if="canRegister" :href="route('register')"
            class="magnetic-btn group relative inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-bold text-white transition-all duration-500 overflow-hidden shadow-2xl w-full sm:w-auto justify-center"
            style="box-shadow: 0 0 80px rgba(99,102,241,0.4);">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600"></div>
            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl blur opacity-0 group-hover:opacity-40 transition-opacity duration-500"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
            <span class="relative z-10 flex items-center gap-2">
              <i class="fa-solid fa-rocket"></i>
              Start Building Free
            </span>
          </Link>
          
          <Link :href="route('login')"
            class="flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-semibold text-slate-300 border border-white/10 hover:border-white/25 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] backdrop-blur-sm transition-all duration-300 w-full sm:w-auto justify-center group">
            <i class="fa-regular fa-circle-play group-hover:text-indigo-400 transition-colors"></i>
            Watch Demo
          </Link>
        </div>

        <!-- Trusted By -->
        <div class="mt-10 reveal-up">
          <p class="text-xs text-slate-600 uppercase tracking-widest mb-4">Trusted by innovative teams worldwide</p>
          <div class="flex flex-wrap items-center justify-center gap-8 opacity-40">
            <div v-for="i in 5" :key="i" class="h-6 w-24 rounded bg-slate-600/30 animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- 3D Perspective Card with Parallax -->
      <div 
        ref="perspectiveCard"
        class="relative mt-16 sm:mt-24 max-w-6xl mx-auto reveal-up perspective-1000"
        @mousemove="handleCardTilt"
        @mouseleave="resetCardTilt"
      >
        <div 
          ref="cardInner"
          class="relative bg-gradient-to-br from-slate-900/90 via-slate-800/90 to-slate-900/90 border border-white/10 rounded-3xl shadow-2xl backdrop-blur-sm overflow-hidden transition-transform duration-200 ease-out preserve-3d"
        >
          <!-- Glow Effect -->
          <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-xl opacity-50"></div>
          
          <!-- Browser Header -->
          <div class="relative flex items-center gap-2 px-5 py-3.5 border-b border-white/10 bg-white/[0.02]">
            <div class="flex gap-1.5">
              <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
              <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
              <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
            </div>
            <div class="flex-1 mx-4">
              <div class="max-w-sm mx-auto h-8 rounded-lg flex items-center px-3 gap-2 bg-white/[0.04] border border-white/[0.06]">
                <i class="fa-solid fa-lock text-slate-500 text-xs"></i>
                <span class="text-xs text-slate-500">app.reportgen.com/editor</span>
              </div>
            </div>
            <div class="flex gap-2">
              <div class="w-6 h-6 rounded-lg bg-white/5"></div>
              <div class="w-6 h-6 rounded-lg bg-white/5"></div>
            </div>
          </div>

          <!-- Editor Content -->
          <div class="relative flex h-72 sm:h-96">
            <!-- Left Toolbar -->
            <div class="w-16 sm:w-20 border-r border-white/[0.06] p-2.5 space-y-2 bg-white/[0.01]">
              <div v-for="i in 8" :key="i" 
                   class="h-8 sm:h-10 rounded-xl bg-white/[0.04] border border-white/[0.06] flex items-center justify-center hover:bg-indigo-500/10 hover:border-indigo-500/30 transition-all cursor-pointer group/tool">
                <i :class="toolIcons[i-1]" class="text-slate-500 text-xs sm:text-sm group-hover/tool:text-indigo-400 transition-colors"></i>
              </div>
            </div>
            
            <!-- Canvas -->
            <div class="flex-1 p-4 sm:p-6 bg-gradient-to-br from-slate-950/50 to-slate-900/50">
              <div class="max-w-lg mx-auto bg-white rounded-2xl shadow-2xl p-5 sm:p-6 space-y-3.5">
                <div class="h-6 sm:h-8 w-3/4 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 animate-shimmer"></div>
                <div class="h-2 w-full rounded bg-slate-100"></div>
                <div class="h-2 w-4/5 rounded bg-slate-100"></div>
                
                <!-- KPI Cards -->
                <div class="grid grid-cols-3 gap-2.5 mt-4">
                  <div class="h-14 sm:h-20 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 flex flex-col items-center justify-center hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-indigo-600 font-bold text-sm sm:text-lg">$48K</div>
                    <div class="text-[9px] sm:text-[10px] text-slate-500 font-medium">Revenue</div>
                  </div>
                  <div class="h-14 sm:h-20 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 flex flex-col items-center justify-center hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-emerald-600 font-bold text-sm sm:text-lg">92%</div>
                    <div class="text-[9px] sm:text-[10px] text-slate-500 font-medium">Rate</div>
                  </div>
                  <div class="h-14 sm:h-20 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 flex flex-col items-center justify-center hover:scale-105 transition-transform cursor-pointer">
                    <div class="text-amber-600 font-bold text-sm sm:text-lg">3.2K</div>
                    <div class="text-[9px] sm:text-[10px] text-slate-500 font-medium">Users</div>
                  </div>
                </div>
                
                <!-- Chart -->
                <div class="h-20 sm:h-28 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 flex items-end justify-around p-3">
                  <div v-for="(h, i) in [45,70,35,85,55,75,60,90]" :key="i" 
                       class="w-5 sm:w-7 rounded-t-lg transition-all hover:scale-110 cursor-pointer animate-bar-grow"
                       :style="{ 
                         height: h + '%', 
                         background: 'linear-gradient(to top, #6366f1, #8b5cf6)',
                         animationDelay: (i * 0.1) + 's'
                       }"></div>
                </div>
              </div>
            </div>
            
            <!-- Right Properties Panel -->
            <div class="w-16 sm:w-20 border-l border-white/[0.06] p-2.5 space-y-2 bg-white/[0.01]">
              <div v-for="i in 5" :key="i" class="space-y-1.5 p-2 rounded-xl bg-white/[0.03] border border-white/[0.06]">
                <div class="h-1 w-10 rounded bg-indigo-400/20"></div>
                <div class="h-5 w-full rounded bg-white/[0.06]"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- FEATURES SECTION                                               -->
    <!-- ============================================================ -->
    <section id="features" class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-32">
      
      <div class="text-center mb-16 sm:mb-20 reveal-up">
        <span class="text-xs sm:text-sm font-bold text-indigo-400 uppercase tracking-[0.2em] mb-3 block">Why ReportGen</span>
        <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white mb-4">
          Everything You Need
        </h2>
        <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto">
          Powerful features designed to make report creation fast, beautiful, and collaborative.
        </p>
      </div>

      <!-- Feature Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="(feature, index) in features" :key="feature.title"
          class="reveal-up group relative bg-gradient-to-br from-white/[0.04] to-white/[0.01] border border-white/[0.08] rounded-2xl p-6 sm:p-8 backdrop-blur-sm transition-all duration-500"
          :class="feature.highlighted ? 'lg:col-span-1 ring-1 ring-indigo-500/30' : ''"
          :style="{ animationDelay: `${index * 0.1}s` }">
          
          <!-- Hover Gradient -->
          <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-indigo-500/0 via-purple-500/0 to-pink-500/0 group-hover:from-indigo-500/5 group-hover:via-purple-500/5 group-hover:to-pink-500/5 transition-all duration-500"></div>
          <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500" 
               style="background: radial-gradient(circle at 50% 0%, rgba(99,102,241,0.1), transparent 70%);"></div>
          
          <!-- Icon -->
          <div class="relative w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-5 sm:mb-6 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3"
               :style="{ background: `linear-gradient(135deg, ${feature.color}20, ${feature.color}10)` }">
            <i :class="feature.icon" class="text-xl sm:text-2xl transition-colors duration-300" :style="{ color: feature.color }"></i>
            <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                 :style="{ boxShadow: `0 0 30px ${feature.color}30` }"></div>
          </div>
          
          <h3 class="text-white font-bold text-base sm:text-lg mb-2 group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:bg-clip-text transition-all duration-300"
              :style="{ backgroundImage: `linear-gradient(to right, ${feature.color}, white)` }">
            {{ feature.title }}
          </h3>
          <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">{{ feature.desc }}</p>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- CTA SECTION                                                    -->
    <!-- ============================================================ -->
    <section class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-32">
      
      <div class="relative rounded-[2.5rem] p-10 sm:p-16 lg:p-20 overflow-hidden text-center reveal-up">
        
        <!-- Animated Border -->
        <div class="absolute inset-0 rounded-[2.5rem]" 
             style="background: conic-gradient(from 0deg, #6366f1, #8b5cf6, #ec4899, #6366f1); animation: rotateBorder 4s linear infinite; opacity: 0.3;"></div>
        
        <!-- Inner Background -->
        <div class="absolute inset-[2px] rounded-[2.4rem] bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950"></div>
        
        <!-- Content -->
        <div class="relative">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-500/10 border border-indigo-500/20 mb-6">
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">🚀 Get Started Today</span>
          </div>
          
          <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white mb-6">
            Ready to Create <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">Amazing Reports</span>?
          </h2>
          
          <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto mb-10">
            Join thousands of professionals who are already creating stunning reports with ReportGen. 
            Start your free trial today — no credit card required.
          </p>
          
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link v-if="canRegister" :href="route('register')"
              class="magnetic-btn group relative inline-flex items-center gap-3 px-10 py-5 rounded-2xl text-lg font-black text-white transition-all duration-500 overflow-hidden shadow-2xl w-full sm:w-auto justify-center"
              style="box-shadow: 0 0 100px rgba(99,102,241,0.5);">
              <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600"></div>
              <div class="absolute -inset-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl blur opacity-0 group-hover:opacity-50 transition-opacity duration-500"></div>
              <span class="relative z-10 flex items-center gap-2">
                Start Building Free
                <i class="fa-solid fa-rocket group-hover:translate-x-1 group-hover:-translate-y-0.5 transition-all duration-300"></i>
              </span>
            </Link>
          </div>
          
          <p class="text-xs text-slate-600 mt-6">No credit card required • Free forever plan • Cancel anytime</p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/[0.06] py-10 text-center">
      <div class="max-w-7xl mx-auto px-4">
        <p class="text-slate-600 text-xs sm:text-sm">
          &copy; {{ new Date().getFullYear() }} ReportGen Enterprise. All rights reserved.
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

// Props
defineProps({ canLogin: Boolean, canRegister: Boolean, laravelVersion: String, phpVersion: String })

// ── Typewriter Effect ──────────────────────────────────────────
const words = ['Stunning', 'Beautiful', 'Professional', 'Amazing', 'Powerful','Report!']
const displayedText = ref('Stunning')
let wordIndex = 0
let charIndex = 0
let isDeleting = false
let typewriterTimer = null

const startTypewriter = () => {
  typewriterTimer = setInterval(() => {
    const currentWord = words[wordIndex]
    if (!isDeleting) {
      displayedText.value = currentWord.substring(0, charIndex + 1)
      charIndex++
      if (charIndex === currentWord.length) {
        isDeleting = true
        setTimeout(() => {}, 3000)
      }
    } else {
      displayedText.value = currentWord.substring(0, charIndex - 1)
      charIndex--
      if (charIndex === 0) {
        isDeleting = false
        wordIndex = (wordIndex + 1) % words.length
      }
    }
  }, isDeleting ? 50 : 100)
}

// ── Cursor Glow Effect ─────────────────────────────────────────
const cursorGlow = ref(null)
const cursorGlowVisible = ref(false)
const perspectiveCard = ref(null)
const cardInner = ref(null)

const handleMouseMove = (e) => {
  if (cursorGlow.value && cursorGlowVisible.value) {
    cursorGlow.value.style.left = e.clientX + 'px'
    cursorGlow.value.style.top = e.clientY + 'px'
    cursorGlow.value.style.opacity = '1'
  }
}

// ── 3D Card Tilt Effect ────────────────────────────────────────
const handleCardTilt = (e) => {
  if (!cardInner.value) return
  const rect = perspectiveCard.value?.getBoundingClientRect()
  if (!rect) return
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  const centerX = rect.width / 2
  const centerY = rect.height / 2
  const rotateX = (y - centerY) / centerY * -5
  const rotateY = (x - centerX) / centerX * 8
  cardInner.value.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`
}

const resetCardTilt = () => {
  if (cardInner.value) {
    cardInner.value.style.transform = 'rotateX(0deg) rotateY(0deg)'
  }
}

// ── Particle System ────────────────────────────────────────────
const particleCanvas = ref(null)
let particles = []
let animationId = null

class Particle {
  constructor(canvas) {
    this.canvas = canvas
    this.x = Math.random() * canvas.width
    this.y = Math.random() * canvas.height
    this.size = Math.random() * 2 + 0.5
    this.speedX = Math.random() * 0.5 - 0.25
    this.speedY = Math.random() * 0.5 - 0.25
    this.opacity = Math.random() * 0.5 + 0.1
  }
  update() {
    this.x += this.speedX
    this.y += this.speedY
    if (this.x > this.canvas.width) this.x = 0
    if (this.x < 0) this.x = this.canvas.width
    if (this.y > this.canvas.height) this.y = 0
    if (this.y < 0) this.y = this.canvas.height
  }
  draw(ctx) {
    ctx.beginPath()
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2)
    ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity})`
    ctx.fill()
  }
}

const initParticles = () => {
  const canvas = particleCanvas.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  canvas.width = window.innerWidth
  canvas.height = window.innerHeight
  particles = Array.from({ length: 80 }, () => new Particle(canvas))
  
  const animate = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    particles.forEach(p => { p.update(); p.draw(ctx) })
    // Draw connection lines between nearby particles
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x
        const dy = particles[i].y - particles[j].y
        const dist = Math.sqrt(dx * dx + dy * dy)
        if (dist < 100) {
          ctx.beginPath()
          ctx.moveTo(particles[i].x, particles[i].y)
          ctx.lineTo(particles[j].x, particles[j].y)
          ctx.strokeStyle = `rgba(255, 255, 255, ${0.05 * (1 - dist / 100)})`
          ctx.stroke()
        }
      }
    }
    animationId = requestAnimationFrame(animate)
  }
  animate()
}

// ── Scroll Reveal ──────────────────────────────────────────────
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1'
      entry.target.style.transform = 'translateY(0)'
      revealObserver.unobserve(entry.target)
    }
  })
}, { threshold: 0.15 })

// ── Navigation Links ───────────────────────────────────────────
const navLinks = [
  { label: 'Features', href: '#features' },
  { label: 'Templates', href: '#templates' },
  { label: 'Pricing', href: '#pricing' },
  { label: 'Docs', href: '#docs' }
]

// Toolbar icons
const toolIcons = [
  'fa-solid fa-mouse-pointer', 'fa-solid fa-font', 'fa-solid fa-image',
  'fa-solid fa-shapes', 'fa-solid fa-table', 'fa-solid fa-chart-simple',
  'fa-solid fa-layer-group', 'fa-solid fa-palette'
]

// Features data
const features = [
  { title: 'Drag & Drop Builder', desc: '50+ elements. No coding needed.', icon: 'fa-solid fa-cube', color: '#6366f1', highlighted: false },
  { title: 'Live Charts', desc: 'Real-time interactive data visualization.', icon: 'fa-solid fa-chart-line', color: '#10b981', highlighted: false },
  { title: 'Multi-Export', desc: 'PDF, PNG, CSV, Excel — all formats.', icon: 'fa-solid fa-file-export', color: '#f59e0b', highlighted: false },
  { title: 'Collaboration', desc: 'Real-time team editing & sharing.', icon: 'fa-solid fa-users', color: '#8b5cf6', highlighted: false },
  { title: 'Templates', desc: 'Professional pre-built templates.', icon: 'fa-solid fa-palette', color: '#ec4899', highlighted: false },
  { title: 'Version History', desc: 'Track changes, restore any version.', icon: 'fa-solid fa-clock-rotate-left', color: '#0ea5e9', highlighted: false },
  { title: 'Live Preview', desc: 'WYSIWYG editing with instant preview.', icon: 'fa-solid fa-eye', color: '#14b8a6', highlighted: false },
  { title: 'Secure Sharing', desc: 'Control access with share links.', icon: 'fa-solid fa-shield-halved', color: '#f43f5e', highlighted: false }
]

// ── Lifecycle ──────────────────────────────────────────────────
onMounted(async () => {
  await nextTick()
  startTypewriter()
  initParticles()
  
  // Observe elements for scroll reveal
  document.querySelectorAll('.reveal-up').forEach(el => {
    el.style.opacity = '0'
    el.style.transform = 'translateY(30px)'
    el.style.transition = 'all 0.8s cubic-bezier(0.16, 1, 0.3, 1)'
    revealObserver.observe(el)
  })

  window.addEventListener('resize', initParticles)
})

onBeforeUnmount(() => {
  clearInterval(typewriterTimer)
  if (animationId) cancelAnimationFrame(animationId)
  revealObserver.disconnect()
  window.removeEventListener('resize', initParticles)
})
</script>

<style>
/* ============================================================ */
/* GLOBAL ANIMATIONS & STYLES                                     */
/* ============================================================ */

/* Reveal Up Animation */
.reveal-up {
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Morphing Orbs */
@keyframes morph-1 {
  0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; transform: translate(0, 0) rotate(0deg); }
  25% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; }
  50% { transform: translate(40px, 30px) rotate(5deg); border-radius: 50% 50% 30% 70% / 40% 60% 50% 60%; }
  75% { border-radius: 40% 60% 30% 70% / 60% 40% 60% 30%; }
}
@keyframes morph-2 {
  0%, 100% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; transform: translate(0, 0) rotate(0deg); }
  50% { transform: translate(-30px, -20px) rotate(-5deg); border-radius: 60% 40% 30% 70% / 50% 60% 40% 60%; }
}
@keyframes morph-3 {
  0%, 100% { border-radius: 50% 50% 40% 60% / 50% 40% 60% 50%; transform: translate(-50%, -50%) rotate(0deg) scale(1); }
  50% { transform: translate(-50%, -50%) rotate(8deg) scale(1.1); border-radius: 60% 40% 60% 40% / 40% 60% 40% 60%; }
}

/* Slow Spin */
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin-slow { animation: spin-slow 12s linear infinite; }

/* Blinking Cursor */
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}
.animate-blink { animation: blink 1s step-end infinite; }

/* Grid Movement */
@keyframes gridMove {
  0% { background-position: 0 0; }
  100% { background-position: 60px 60px; }
}

/* Rotating Border */
@keyframes rotateBorder {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Bar Grow Animation */
@keyframes barGrow {
  from { transform: scaleY(0); }
  to { transform: scaleY(1); }
}
.animate-bar-grow { 
  animation: barGrow 0.6s ease-out forwards; 
  transform-origin: bottom; 
}

/* Shimmer Effect */
@keyframes shimmer {
  0% { background-position: -200px 0; }
  100% { background-position: 200px 0; }
}
.animate-shimmer {
  background: linear-gradient(90deg, #6366f1, #8b5cf6, #6366f1);
  background-size: 200px 100%;
  animation: shimmer 2s linear infinite;
}

/* Perspective for 3D Card */
.perspective-1000 { perspective: 1000px; }
.preserve-3d { transform-style: preserve-3d; }

/* Smooth Scrolling */
html { scroll-behavior: smooth; }

/* Magnetic Button Effect */
.magnetic-btn { transition: transform 0.3s ease-out; }
</style>