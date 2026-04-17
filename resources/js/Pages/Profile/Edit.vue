<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Profile Settings</h2>
        </template>

        <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-full">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 space-y-6">

                <!-- Update Profile -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Profile Information</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Update your name and email address.</p>
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name</label>
                            <input v-model="profileForm.name" type="text" required class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                            <p v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">{{ profileForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                            <input v-model="profileForm.email" type="email" required class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                            <p v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">{{ profileForm.errors.email }}</p>
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" :disabled="profileForm.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                                Save Changes
                            </button>
                            <span v-if="profileForm.recentlySuccessful" class="text-sm text-emerald-600 dark:text-emerald-400">✓ Saved!</span>
                        </div>
                    </form>
                </div>

                <!-- Update Password -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Change Password</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Use a strong, unique password.</p>
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Current Password</label>
                            <input v-model="passwordForm.current_password" type="password" required autocomplete="current-password" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">New Password</label>
                            <input v-model="passwordForm.password" type="password" required autocomplete="new-password" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                            <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirm New Password</label>
                            <input v-model="passwordForm.password_confirmation" type="password" required autocomplete="new-password" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" :disabled="passwordForm.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                                Update Password
                            </button>
                            <span v-if="passwordForm.recentlySuccessful" class="text-sm text-emerald-600 dark:text-emerald-400">✓ Updated!</span>
                        </div>
                    </form>
                </div>

                <!-- Delete Account -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-red-200 dark:border-red-900/50 p-6">
                    <h3 class="font-semibold text-red-600 dark:text-red-400 mb-1">Delete Account</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Once deleted, all data will be permanently removed.</p>
                    <button @click="showDeleteModal = true" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal=false"/>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 w-full max-w-sm z-10">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Delete Account?</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">This action cannot be undone. Enter your password to confirm.</p>
                    <input v-model="deleteForm.password" type="password" placeholder="Your password"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 mb-4"/>
                    <p v-if="deleteForm.errors.password" class="text-red-500 text-xs -mt-3 mb-3">{{ deleteForm.errors.password }}</p>
                    <div class="flex gap-3">
                        <button @click="showDeleteModal=false" class="flex-1 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                        <button @click="deleteAccount" :disabled="deleteForm.processing" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-xl text-sm font-semibold transition-colors">Delete</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const showDeleteModal = ref(false);

const profileForm = useForm({
    name:  page.props.auth.user.name,
    email: page.props.auth.user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({ password: '' });

const updateProfile = () => profileForm.patch(route('profile.update'));
const updatePassword = () => passwordForm.put(route('password.update'), {
    onFinish: () => passwordForm.reset(),
});
const deleteAccount = () => deleteForm.delete(route('profile.destroy'), {
    onFinish: () => { showDeleteModal.value = false; }
});
</script>