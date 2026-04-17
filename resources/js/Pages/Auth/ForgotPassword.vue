<template>
    <GuestLayout>
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Forgot password?</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Enter your email and we'll send a reset link</p>
        </div>
        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 rounded-xl">{{ status }}</div>
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="you@example.com"/>
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>
            <button type="submit" :disabled="form.processing" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold rounded-xl transition-colors">
                {{ form.processing ? 'Sending…' : 'Send Reset Link' }}
            </button>
        </form>
        <p class="mt-4 text-center text-sm"><Link :href="route('login')" class="text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-700">← Back to login</Link></p>
    </GuestLayout>
</template>
<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
defineProps({ status: String });
const form = useForm({ email:'' });
const submit = () => form.post(route('password.email'));
</script>