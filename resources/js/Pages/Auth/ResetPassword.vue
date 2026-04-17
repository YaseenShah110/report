<template>
    <GuestLayout>
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Reset Password</h1>
        </div>
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">New Password</label>
                <input v-model="form.password" type="password" required autocomplete="new-password" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <button type="submit" :disabled="form.processing" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold rounded-xl transition-colors">
                {{ form.processing ? 'Resetting…' : 'Reset Password' }}
            </button>
        </form>
    </GuestLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
const props = defineProps({ email: String, token: String });
const form = useForm({ token: props.token, email: props.email, password:'', password_confirmation:'' });
const submit = () => form.post(route('password.store'), { onFinish: () => form.reset('password','password_confirmation') });
</script>