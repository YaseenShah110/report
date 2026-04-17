<template>
    <GuestLayout>
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create your account</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Start building beautiful reports today</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Full Name</label>
                <input v-model="form.name" type="text" required autocomplete="name"
                    class="w-full px-4 py-2.5 text-sm border dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="John Doe" :class="form.errors.name ? 'border-red-400':''"/>
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input v-model="form.email" type="email" required autocomplete="username"
                    class="w-full px-4 py-2.5 text-sm border dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="you@example.com" :class="form.errors.email ? 'border-red-400':''"/>
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                <input v-model="form.password" type="password" required autocomplete="new-password"
                    class="w-full px-4 py-2.5 text-sm border dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Min. 8 characters" :class="form.errors.password ? 'border-red-400':''"/>
                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" required autocomplete="new-password"
                    class="w-full px-4 py-2.5 text-sm border dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Repeat password"/>
            </div>
            <button type="submit" :disabled="form.processing"
                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".75"/></svg>
                {{ form.processing ? 'Creating account…' : 'Create Account' }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            Already have an account?
            <Link :href="route('login')" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:text-indigo-700">Sign in</Link>
        </p>
    </GuestLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({ name:'', email:'', password:'', password_confirmation:'' });
const submit = () => form.post(route('register'), { onFinish: () => form.reset('password','password_confirmation') });
</script>