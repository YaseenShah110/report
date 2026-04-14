<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Report</h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Report Title</label>
                            <input v-model="form.title" type="text" 
                                   class="w-full border rounded px-3 py-2" required />
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Select Template</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="template in templates" :key="template.id"
                                     @click="form.template_id = template.id"
                                     class="border rounded p-4 cursor-pointer transition"
                                     :class="{ 'border-blue-500 bg-blue-50': form.template_id === template.id }">
                                    <div class="font-semibold">{{ template.name }}</div>
                                    <div class="text-sm text-gray-600">{{ template.description }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" :disabled="form.processing"
                                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                                Create Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    templates: Array
})

const form = useForm({
    title: '',
    template_id: null
})

const submit = () => {
    form.post(route('reports.store'))
}
</script>