<template>
    <GuestLayout>
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Verify Email</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Check your inbox for a verification link.</p>
        </div>
        <div v-if="status === 'verification-link-sent'" class="mb-4 text-sm font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 rounded-xl text-center">
            A new verification link has been sent.
        </div>
        <div class="flex flex-col gap-3">
            <form @submit.prevent="submit">
                <button type="submit" :disabled="form.processing" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold rounded-xl transition-colors">
                    {{ form.processing ? 'Sending…' : 'Resend Verification Email' }}
                </button>
            </form>
            <Link :href="route('logout')" method="post" as="button" class="w-full py-2.5 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-center">
                Log Out
            </Link>
        </div>
    </GuestLayout>
</template>
<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
defineProps({ status: String });
const form = useForm({});
const submit = () => form.post(route('verification.send'));
</script>