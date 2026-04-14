<template>
    <div class="min-h-screen bg-gray-100 ">
        <nav class="bg-white border-b border-gray-100 dark:bg-slate-900 ">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="text-xl font-bold text-blue-600">
                                GBRSP Report
                            </Link>
                        </div>
                        
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex ">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </NavLink>
                            <NavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                My Reports
                            </NavLink>
                            <NavLink :href="route('templates.index')" :active="route().current('templates.*')">
                                Templates
                            </NavLink>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <button @click="logout" class="text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 hover:text-gray-700">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        
        <header class="bg-white shadow dark:bg-slate-900" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 dark:bg-slate-900">
                <slot name="header" />
            </div>
        </header>
        
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

const logout = () => {
    router.post(route('logout'));
};
</script>