<template>
    <GuestLayout>
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Welcome back</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sign in to your account</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 rounded-xl">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input v-model="form.email" type="email" required autocomplete="username"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="you@example.com"
                    :class="form.errors.email ? 'border-red-400' : ''"/>
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                    <Link :href="route('password.request')" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">Forgot password?</Link>
                </div>
                <input v-model="form.password" type="password" required autocomplete="current-password"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="••••••••"
                    :class="form.errors.password ? 'border-red-400' : ''"/>
                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" v-model="form.remember" class="w-4 h-4 text-indigo-600 rounded border-slate-300"/>
                <label for="remember" class="text-sm text-slate-600 dark:text-slate-400">Remember me</label>
            </div>
            <button type="submit" :disabled="form.processing"
                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".75"/></svg>
                {{ form.processing ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            Don't have an account?
            <Link :href="route('register')" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:text-indigo-700">Create one</Link>
        </p>

        <!-- Demo credentials hint -->
        <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600">
            <p class="text-xs text-slate-500 dark:text-slate-400 text-center font-medium mb-1">Demo credentials</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 text-center">demo@example.com / password</p>
        </div>
    </GuestLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({ status: String, canResetPassword: Boolean });

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>