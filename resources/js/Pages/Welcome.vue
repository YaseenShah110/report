<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

const mounted = ref(false);
onMounted(() => { setTimeout(() => mounted.value = true, 50); });
</script>

<template>
    <Head title="Dynamic Report Generator — GBRSP" />

    <div class="min-h-screen bg-[#06090f] text-white overflow-x-hidden font-sans">

        <!-- Animated background mesh -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-[-20%] left-[-10%] w-[700px] h-[700px] rounded-full opacity-20"
                style="background: radial-gradient(circle, #6366f1 0%, transparent 70%); animation: floatA 18s ease-in-out infinite alternate;"></div>
            <div class="absolute bottom-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full opacity-15"
                style="background: radial-gradient(circle, #0ea5e9 0%, transparent 70%); animation: floatB 22s ease-in-out infinite alternate;"></div>
            <div class="absolute top-[40%] left-[50%] w-[400px] h-[400px] rounded-full opacity-10"
                style="background: radial-gradient(circle, #8b5cf6 0%, transparent 70%); animation: floatC 15s ease-in-out infinite alternate;"></div>
            <!-- Grid overlay -->
            <div class="absolute inset-0 opacity-[0.04]"
                style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 60px 60px;"></div>
        </div>

        <!-- NAVBAR -->
        <nav class="relative z-50 flex items-center justify-between px-8 py-6 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="font-bold text-white text-lg tracking-tight">ReportGen</span>
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-wider">GBRSP</span>
            </div>

            <div v-if="canLogin" class="flex items-center gap-2">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    Go to Dashboard →
                </Link>
                <template v-else>
                    <Link :href="route('login')"
                        class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-xl hover:bg-white/5 transition-all duration-200">
                        Sign in
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="px-4 py-2 text-sm font-medium text-white rounded-xl transition-all duration-200"
                        style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 0 20px rgba(99,102,241,0.4);">
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>

        <!-- HERO SECTION -->
        <section class="relative z-10 max-w-7xl mx-auto px-8 pt-20 pb-32">
            <div class="text-center max-w-4xl mx-auto"
                :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                style="transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);">

                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 mb-8">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-medium text-indigo-300 tracking-wide">Dynamic Report Generator — Powered by Intouch Solutions</span>
                </div>

                <!-- Headline -->
                <h1 class="text-6xl sm:text-7xl font-black leading-[0.95] tracking-tight mb-6">
                    <span class="text-white">Build reports</span><br>
                    <span style="background: linear-gradient(135deg, #818cf8, #c084fc, #67e8f9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        that impress.
                    </span>
                </h1>

                <p class="text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed mb-10">
                    A powerful drag-and-drop report builder for GBRSP — create, design, and export
                    professional reports with live charts, KPI cards, tables and more. No design skills needed.
                </p>

                <div class="flex items-center justify-center gap-4 flex-wrap">
                    <Link v-if="canRegister" :href="route('register')"
                        class="group flex items-center gap-2 px-7 py-3.5 rounded-2xl text-base font-semibold text-white transition-all duration-300"
                        style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 0 40px rgba(99,102,241,0.35);">
                        Start Building Free
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <Link :href="route('login')"
                        class="flex items-center gap-2 px-7 py-3.5 rounded-2xl text-base font-semibold text-slate-300 border border-white/10 hover:border-white/20 hover:text-white bg-white/5 hover:bg-white/10 transition-all duration-300">
                        Sign In
                    </Link>
                </div>
            </div>

            <!-- App preview mockup -->
            <div class="mt-20 relative"
                :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'"
                style="transition: all 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s;">
                <div class="relative mx-auto max-w-5xl">
                    <!-- Glow behind the preview -->
                    <div class="absolute inset-0 blur-3xl opacity-30"
                        style="background: linear-gradient(135deg, #6366f1, #8b5cf6); transform: scale(0.85) translateY(10%);"></div>

                    <!-- Browser chrome -->
                    <div class="relative rounded-2xl border border-white/10 overflow-hidden shadow-2xl"
                        style="background: rgba(15,23,42,0.9); backdrop-filter: blur(20px);">
                        <!-- Browser bar -->
                        <div class="flex items-center gap-3 px-4 py-3 border-b border-white/5"
                            style="background: rgba(255,255,255,0.03);">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/70"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/70"></div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="max-w-xs mx-auto h-6 rounded-lg flex items-center px-3 gap-2"
                                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="text-xs text-slate-500">app.gbrsp.pk/editor</span>
                                </div>
                            </div>
                        </div>

                        <!-- Editor layout preview -->
                        <div class="flex h-[420px]">
                            <!-- Left sidebar preview -->
                            <div class="w-52 border-r border-white/5 p-3 flex flex-col gap-2" style="background: rgba(255,255,255,0.02);">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1 px-1">Elements</div>
                                <div class="relative">
                                    <div class="w-full h-7 rounded-lg" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.07);"></div>
                                </div>
                                <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-1 mt-1">Text</div>
                                <div class="grid grid-cols-2 gap-1">
                                    <div v-for="n in 6" :key="n" class="h-14 rounded-lg flex flex-col items-center justify-center gap-1" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                                        <div class="w-5 h-5 rounded" :style="`background: rgba(${[99,16,245,139,249,6][n-1]},${[102,185,91,92,115,182][n-1]},${[241,231,182,251,234,212][n-1]},0.2)`"></div>
                                        <div class="w-8 h-1.5 rounded-full bg-slate-600/50"></div>
                                    </div>
                                </div>
                                <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-1 mt-1">Charts</div>
                                <div class="grid grid-cols-2 gap-1">
                                    <div v-for="n in 4" :key="n" class="h-14 rounded-lg flex flex-col items-center justify-center gap-1" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                                        <div class="w-5 h-5 rounded bg-indigo-500/20"></div>
                                        <div class="w-8 h-1.5 rounded-full bg-slate-600/50"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Canvas area preview -->
                            <div class="flex-1 flex items-center justify-center p-6" style="background: rgba(0,0,0,0.2);">
                                <div class="w-full max-w-md aspect-[3/4] rounded-xl shadow-2xl flex flex-col gap-3 p-5" style="background: #fff; transform: scale(0.85);">
                                    <!-- Simulated report content -->
                                    <div class="h-8 w-3/4 rounded-lg" style="background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
                                    <div class="h-2 w-full rounded bg-slate-200"></div>
                                    <div class="h-2 w-5/6 rounded bg-slate-200"></div>
                                    <div class="grid grid-cols-3 gap-2 mt-2">
                                        <div class="h-16 rounded-lg bg-indigo-50 border border-indigo-100 flex flex-col items-center justify-center">
                                            <div class="text-indigo-600 font-bold text-sm">$48K</div>
                                            <div class="text-[8px] text-slate-400">Revenue</div>
                                        </div>
                                        <div class="h-16 rounded-lg bg-emerald-50 border border-emerald-100 flex flex-col items-center justify-center">
                                            <div class="text-emerald-600 font-bold text-sm">92%</div>
                                            <div class="text-[8px] text-slate-400">Rate</div>
                                        </div>
                                        <div class="h-16 rounded-lg bg-amber-50 border border-amber-100 flex flex-col items-center justify-center">
                                            <div class="text-amber-600 font-bold text-sm">3.2K</div>
                                            <div class="text-[8px] text-slate-400">Users</div>
                                        </div>
                                    </div>
                                    <div class="flex-1 rounded-lg bg-slate-100 relative overflow-hidden">
                                        <!-- Fake bar chart -->
                                        <div class="absolute bottom-0 left-0 right-0 flex items-end justify-around px-3 pb-2 gap-1" style="height: 80px;">
                                            <div v-for="(h,i) in [60, 80, 45, 95, 70, 55, 85]" :key="i"
                                                class="flex-1 rounded-t-sm"
                                                :style="`height:${h}%; background: linear-gradient(to top, #6366f1, #818cf8); opacity: ${0.5 + i*0.07}`"></div>
                                        </div>
                                    </div>
                                    <div class="h-2 w-2/3 rounded bg-slate-200"></div>
                                </div>
                            </div>

                            <!-- Right sidebar preview -->
                            <div class="w-56 border-l border-white/5 p-3 flex flex-col gap-2" style="background: rgba(255,255,255,0.02);">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Properties</div>
                                <div class="space-y-2">
                                    <div v-for="n in 5" :key="n" class="rounded-lg p-2" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                                        <div class="h-1.5 w-12 rounded bg-indigo-400/30 mb-1.5"></div>
                                        <div class="h-5 w-full rounded bg-slate-700/50"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES SECTION -->
        <section class="relative z-10 max-w-7xl mx-auto px-8 py-24">
            <div class="text-center mb-16">
                <div class="inline-block text-xs font-semibold text-indigo-400 tracking-widest uppercase mb-4 px-3 py-1 rounded-full border border-indigo-500/20 bg-indigo-500/10">Features</div>
                <h2 class="text-4xl font-black text-white mb-4">Everything you need to report</h2>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto">A professional-grade editor with drag & drop, real-time charts, and one-click PDF export.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Feature card template -->
                <div v-for="feature in [
                    { icon: '🎛️', title: 'Drag & Drop Builder', desc: 'Place any element anywhere on the page. Drag from the sidebar, drop on the canvas. Move, resize, and rotate with your mouse.', color: 'indigo' },
                    { icon: '📊', title: 'Live Charts', desc: 'Bar, line, pie, doughnut, area, and radar charts — all interactive. Edit data and watch your chart update instantly.', color: 'violet' },
                    { icon: '📋', title: 'KPI & Metric Cards', desc: 'Highlight key numbers with professional KPI cards showing value, trend direction, and comparison period.', color: 'sky' },
                    { icon: '📄', title: 'Multi-Format Export', desc: 'Download your finished report as a pixel-perfect PDF. Share it directly with stakeholders or upload to GBRSP systems.', color: 'emerald' },
                    { icon: '🔲', title: 'Rich Element Library', desc: 'Text, headings, images, tables, shapes, progress bars, dividers, code blocks, badges, links — 25+ element types.', color: 'amber' },
                    { icon: '🕐', title: 'Auto-Save & History', desc: 'Every change is saved automatically. Full undo/redo history with Ctrl+Z keeps your work safe at all times.', color: 'rose' },
                ]" :key="feature.title"
                    class="group relative p-6 rounded-2xl border border-white/5 hover:border-white/10 transition-all duration-300"
                    style="background: rgba(255,255,255,0.02);"
                    onmouseover="this.style.background='rgba(255,255,255,0.04)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.02)'">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-4"
                        style="background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.2);">
                        {{ feature.icon }}
                    </div>
                    <h3 class="text-white font-bold text-lg mb-2">{{ feature.title }}</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">{{ feature.desc }}</p>
                </div>
            </div>
        </section>

        <!-- ELEMENT SHOWCASE -->
        <section class="relative z-10 max-w-7xl mx-auto px-8 py-20">
            <div class="rounded-3xl p-1 border border-white/10" style="background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.1));">
                <div class="rounded-[22px] px-12 py-14" style="background: rgba(6,9,15,0.8); backdrop-filter: blur(20px);">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <div class="inline-block text-xs font-semibold text-violet-400 tracking-widest uppercase mb-4 px-3 py-1 rounded-full border border-violet-500/20 bg-violet-500/10">25+ Element Types</div>
                            <h2 class="text-4xl font-black text-white mb-5 leading-tight">Every element your report needs</h2>
                            <p class="text-slate-400 leading-relaxed mb-8">From simple text blocks to complex data visualisations — every element is configurable with a dedicated properties panel. Position, style, and animate with precision.</p>

                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="cat in [
                                    { name: 'Text & Typography', count: '8 types', color: '#6366f1' },
                                    { name: 'Charts & Data', count: '9 types', color: '#8b5cf6' },
                                    { name: 'Shapes & Layout', count: '6 types', color: '#0ea5e9' },
                                    { name: 'Media & Report', count: '4 types', color: '#10b981' },
                                ]" :key="cat.name"
                                    class="flex items-center gap-3 p-3 rounded-xl border border-white/5" style="background: rgba(255,255,255,0.03);">
                                    <div class="w-2 h-2 rounded-full flex-shrink-0" :style="`background: ${cat.color}`"></div>
                                    <div>
                                        <div class="text-xs font-semibold text-white">{{ cat.name }}</div>
                                        <div class="text-[10px] text-slate-500">{{ cat.count }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visual element grid -->
                        <div class="grid grid-cols-4 gap-2">
                            <div v-for="el in [
                                { icon: 'H1', label: 'Heading' },
                                { icon: 'H2', label: 'Subheading' },
                                { icon: '¶', label: 'Paragraph' },
                                { icon: '❝', label: 'Quote' },
                                { icon: '≡', label: 'List' },
                                { icon: '⌗', label: 'Table' },
                                { icon: '📊', label: 'Bar Chart' },
                                { icon: '📈', label: 'Line Chart' },
                                { icon: '🥧', label: 'Pie Chart' },
                                { icon: '○', label: 'Doughnut' },
                                { icon: '◈', label: 'Radar' },
                                { icon: '▨', label: 'Area' },
                                { icon: '▣', label: 'KPI Card' },
                                { icon: '▬', label: 'Progress' },
                                { icon: '🖼', label: 'Image' },
                                { icon: '▮', label: 'Rectangle' },
                            ]" :key="el.label"
                                class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl border border-white/5 hover:border-indigo-500/30 transition-colors cursor-default"
                                style="background: rgba(255,255,255,0.03);">
                                <span class="text-lg">{{ el.icon }}</span>
                                <span class="text-[9px] text-slate-500 text-center leading-tight">{{ el.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative z-10 max-w-4xl mx-auto px-8 py-24 text-center">
            <div class="relative rounded-3xl p-12 overflow-hidden border border-indigo-500/20"
                style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.15));">
                <div class="absolute inset-0 opacity-10"
                    style="background: radial-gradient(circle at 50% 50%, #6366f1, transparent 70%);"></div>
                <div class="relative">
                    <h2 class="text-4xl font-black text-white mb-4">Ready to build better reports?</h2>
                    <p class="text-slate-300 text-lg mb-8 max-w-xl mx-auto">Join GBRSP's reporting platform — create, design, and share professional reports in minutes.</p>
                    <div class="flex items-center justify-center gap-4">
                        <Link v-if="canRegister" :href="route('register')"
                            class="group flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-bold text-white transition-all duration-300"
                            style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 0 50px rgba(99,102,241,0.4);">
                            Get Started — It's Free
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                        <Link :href="route('login')"
                            class="px-8 py-4 rounded-2xl text-base font-bold text-slate-300 border border-white/10 hover:text-white hover:border-white/20 bg-white/5 hover:bg-white/10 transition-all duration-300">
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/5 py-8 px-8 text-center">
            <p class="text-slate-600 text-sm">
                Dynamic Report Generator &copy; 2026 GBRSP — Built by Intouch Solutions &nbsp;·&nbsp;
                Laravel v{{ laravelVersion }} &nbsp;·&nbsp; PHP v{{ phpVersion }}
            </p>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
body { font-family: 'Plus Jakarta Sans', sans-serif; }

@keyframes floatA {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(60px, 40px) scale(1.1); }
}
@keyframes floatB {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(-50px, -30px) scale(1.08); }
}
@keyframes floatC {
    from { transform: translate(-50%, -50%) scale(1); }
    to   { transform: translate(-50%, -50%) scale(1.15) rotate(30deg); }
}
</style>