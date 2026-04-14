<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Reports</h2>
                <Link :href="route('reports.create')" 
                      class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + New Report
                </Link>
            </div>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white p-4 rounded shadow">
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                        <div class="text-gray-600">Total</div>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <div class="text-2xl font-bold text-green-600">{{ stats.published }}</div>
                        <div class="text-gray-600">Published</div>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.draft }}</div>
                        <div class="text-gray-600">Drafts</div>
                    </div>
                </div>
                
                <!-- Reports List -->
                <div class="bg-white rounded shadow overflow-hidden">
                    <div class="divide-y">
                        <div v-for="report in reports.data" :key="report.id" 
                             class="p-6 hover:bg-gray-50 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold">{{ report.title }}</h3>
                                <div class="text-sm text-gray-500 mt-1">
                                    Template: {{ report.template?.name || 'Custom' }} | 
                                    Status: {{ report.status }} |
                                    Last edited: {{ new Date(report.updated_at).toLocaleDateString() }}
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('reports.edit', report.slug)" 
                                      class="text-blue-600 hover:text-blue-800">Edit</Link>
                                <Link :href="route('reports.download', report.slug)" 
                                      class="text-green-600 hover:text-green-800">PDF</Link>
                                <button @click="deleteReport(report)" 
                                        class="text-red-600 hover:text-red-800">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    reports: Object,
    stats: Object
})

const deleteReport = (report) => {
    if (confirm('Are you sure you want to delete this report?')) {
        router.delete(route('reports.destroy', report.slug))
    }
}
</script>