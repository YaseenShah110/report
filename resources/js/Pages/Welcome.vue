<!-- resources/js/Pages/Welcome.vue -->
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
onMounted(() => { setTimeout(() => mounted.value = true, 100); });
</script>

<template>
    <Head title="ReportGen - Professional Report Builder" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white overflow-x-hidden font-sans">

        <!-- Animated Background -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-[-20%] left-[-10%] w-[800px] h-[800px] rounded-full opacity-20"
                style="background: radial-gradient(circle, #6366f1 0%, transparent 70%); animation: floatA 20s ease-in-out infinite alternate;"></div>
            <div class="absolute bottom-[-20%] right-[-10%] w-[700px] h-[700px] rounded-full opacity-15"
                style="background: radial-gradient(circle, #8b5cf6 0%, transparent 70%); animation: floatB 25s ease-in-out infinite alternate;"></div>
            <div class="absolute top-[40%] left-[50%] w-[500px] h-[500px] rounded-full opacity-10"
                style="background: radial-gradient(circle, #06b6d4 0%, transparent 70%); animation: floatC 18s ease-in-out infinite alternate;"></div>
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 60px 60px;"></div>
        </div>

        <!-- Navbar -->
        <nav class="relative z-50 flex items-center justify-between px-6 sm:px-8 py-5 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                    <i class="fa-solid fa-chart-line text-white text-lg"></i>
                </div>
                <span class="font-bold text-white text-xl tracking-tight">ReportGen</span>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-wider">PRO</span>
            </div>

            <div v-if="canLogin" class="flex items-center gap-3">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30">
                    Dashboard <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                </Link>
                <template v-else>
                    <Link :href="route('login')"
                        class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-xl hover:bg-white/10 transition-all duration-200">
                        Sign In
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/30">
                        Get Started Free
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative z-10 max-w-7xl mx-auto px-6 sm:px-8 pt-20 pb-32">
            <div class="text-center max-w-4xl mx-auto"
                :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
                style="transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);">

                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 backdrop-blur-sm mb-8 animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-medium text-indigo-300 tracking-wide">✨ AI-Powered Report Builder</span>
                </div>

                <h1 class="text-5xl sm:text-6xl md:text-7xl font-black leading-[1.1] tracking-tight mb-6">
                    <span class="text-white">Create Stunning</span><br>
                    <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                        Reports Effortlessly
                    </span>
                </h1>

                <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed mb-10">
                    Drag-and-drop editor with 50+ elements, real-time charts, multi-format export, and team collaboration.
                    Build professional reports in minutes, not hours.
                </p>

                <div class="flex items-center justify-center gap-4 flex-wrap">
                    <Link v-if="canRegister" :href="route('register')"
                        class="group relative inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-bold text-white transition-all duration-300 overflow-hidden"
                        style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 0 40px rgba(99,102,241,0.4);">
                        <span class="relative z-10">Start Building Free</span>
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform relative z-10"></i>
                        <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </Link>
                    <Link :href="route('login')"
                        class="flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-semibold text-slate-300 border border-white/10 hover:border-white/20 hover:text-white bg-white/5 hover:bg-white/10 transition-all duration-300">
                        Sign In
                    </Link>
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap items-center justify-center gap-8 mt-12 pt-8 border-t border-white/10">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">50+</p>
                        <p class="text-xs text-slate-400">Drag & Drop Elements</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">6+</p>
                        <p class="text-xs text-slate-400">Chart Types</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">4</p>
                        <p class="text-xs text-slate-400">Export Formats</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">Real-time</p>
                        <p class="text-xs text-slate-400">Collaboration</p>
                    </div>
                </div>
            </div>

            <!-- Editor Preview -->
            <div class="mt-20 relative"
                :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'"
                style="transition: all 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s;">
                <div class="relative mx-auto max-w-6xl">
                    <div class="absolute inset-0 blur-3xl opacity-30"
                        style="background: linear-gradient(135deg, #6366f1, #8b5cf6); transform: scale(0.9) translateY(5%);"></div>

                    <div class="relative rounded-2xl border border-white/10 overflow-hidden shadow-2xl bg-slate-900/50 backdrop-blur-xl">
                        <div class="flex items-center gap-3 px-5 py-3 border-b border-white/10 bg-white/5">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/70"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/70"></div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="max-w-xs mx-auto h-7 rounded-lg flex items-center px-3 gap-2 bg-white/5 border border-white/10">
                                    <i class="fa-solid fa-lock text-slate-500 text-xs"></i>
                                    <span class="text-xs text-slate-400">app.reportgen.com/editor</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-chart-line text-slate-500 text-sm"></i>
                        </div>

                        <div class="flex h-[500px]">
                            <div class="w-56 border-r border-white/10 p-3 space-y-2 bg-white/5">
                                <div class="text-[10px] font-semibold text-indigo-400 uppercase">Elements</div>
                                <div class="grid grid-cols-2 gap-1">
                                    <div v-for="i in 8" :key="i" class="h-12 rounded-lg bg-white/5 border border-white/10 flex flex-col items-center justify-center gap-1 hover:bg-white/10 transition-colors">
                                        <i class="fa-solid fa-font text-indigo-400 text-xs"></i>
                                        <span class="text-[8px] text-slate-400">Element</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 p-6 bg-gradient-to-br from-slate-800/50 to-slate-900/50">
                                <div class="max-w-md mx-auto bg-white rounded-xl shadow-2xl p-5 space-y-3 transform transition-transform hover:scale-[1.02]">
                                    <div class="h-8 w-3/4 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                                    <div class="h-2 w-full rounded bg-slate-200"></div>
                                    <div class="h-2 w-5/6 rounded bg-slate-200"></div>
                                    <div class="grid grid-cols-3 gap-2 mt-3">
                                        <div class="h-16 rounded-lg bg-indigo-50 border border-indigo-100 flex flex-col items-center justify-center">
                                            <div class="text-indigo-600 font-bold">$48K</div>
                                            <div class="text-[10px] text-slate-400">Revenue</div>
                                        </div>
                                        <div class="h-16 rounded-lg bg-emerald-50 border border-emerald-100 flex flex-col items-center justify-center">
                                            <div class="text-emerald-600 font-bold">92%</div>
                                            <div class="text-[10px] text-slate-400">Rate</div>
                                        </div>
                                        <div class="h-16 rounded-lg bg-amber-50 border border-amber-100 flex flex-col items-center justify-center">
                                            <div class="text-amber-600 font-bold">3.2K</div>
                                            <div class="text-[10px] text-slate-400">Users</div>
                                        </div>
                                    </div>
                                    <div class="h-24 rounded-lg bg-slate-100 flex items-end justify-around p-2">
                                        <div v-for="h in [60,80,45,95,70]" :key="h" class="w-8 rounded-t-lg transition-all hover:scale-110" :style="{height: h+'px', background: 'linear-gradient(to top, #6366f1, #8b5cf6)'}"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="w-56 border-l border-white/10 p-3 space-y-2 bg-white/5">
                                <div class="text-[10px] font-semibold text-indigo-400 uppercase">Properties</div>
                                <div v-for="i in 4" :key="i" class="space-y-1 p-2 rounded-lg bg-white/5 border border-white/10">
                                    <div class="h-1.5 w-12 rounded bg-indigo-400/30"></div>
                                    <div class="h-6 w-full rounded bg-white/10"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="relative z-10 max-w-7xl mx-auto px-6 sm:px-8 py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div v-for="(feature, i) in features" :key="feature.title"
                    class="group p-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm hover:bg-white/10 hover:border-indigo-500/30 transition-all duration-300 hover:-translate-y-1"
                    :style="{ animationDelay: `${i * 100}ms` }">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 group-hover:scale-110 transition-transform">
                        <i :class="feature.icon" class="text-indigo-400 text-xl"></i>
                    </div>
                    <h3 class="text-white font-bold text-base mb-2">{{ feature.title }}</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">{{ feature.desc }}</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative z-10 max-w-4xl mx-auto px-6 sm:px-8 py-20 text-center">
            <div class="relative rounded-3xl p-12 overflow-hidden bg-gradient-to-br from-indigo-600/20 to-purple-600/20 border border-indigo-500/30">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10"></div>
                <div class="relative">
                    <h2 class="text-4xl font-bold text-white mb-4">Ready to transform your reports?</h2>
                    <p class="text-slate-300 text-lg mb-8 max-w-xl mx-auto">Join thousands of professionals using ReportGen to create stunning reports.</p>
                    <div class="flex items-center justify-center gap-4">
                        <Link v-if="canRegister" :href="route('register')"
                            class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold rounded-2xl transition-all duration-300 shadow-lg shadow-indigo-500/30">
                            Start Creating Free
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <footer class="relative z-10 border-t border-white/10 py-8 text-center">
            <p class="text-slate-500 text-sm">ReportGen &copy; 2024 - Professional Report Builder</p>
        </footer>
    </div>
</template>

<script>
const features = [
    { title: 'Drag & Drop Builder', desc: '50+ elements at your fingertips', icon: 'fa-solid fa-arrows-up-down-left-right' },
    { title: 'Live Charts', desc: 'Interactive charts that update in real-time', icon: 'fa-solid fa-chart-line' },
    { title: 'Multi-Format Export', desc: 'PDF, PNG, CSV, Excel exports', icon: 'fa-solid fa-file-export' },
    { title: 'Team Collaboration', desc: 'Share and assign reports to team members', icon: 'fa-solid fa-users' }
]
</script>

<style>
@keyframes floatA {
    from { transform: translate(0,0) scale(1); }
    to { transform: translate(60px, 40px) scale(1.1); }
}
@keyframes floatB {
    from { transform: translate(0,0) scale(1); }
    to { transform: translate(-50px, -30px) scale(1.08); }
}
@keyframes floatC {
    from { transform: translate(-50%, -50%) scale(1); }
    to { transform: translate(-50%, -50%) scale(1.15) rotate(30deg); }
}
</style>