<!-- resources/js/Pages/Reports/AssignedReports.vue -->
<template>
<<<<<<< HEAD
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2
                    class="text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent"
                >
                    Shared Reports
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Reports shared with you by team members
                </p>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Search -->
            <div class="mb-6">
                <div class="relative max-w-md">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                    ></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search shared reports..."
                        @input="filterReports"
                        class="w-full pl-11 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="assignment in filteredAssignments"
                    :key="assignment.id"
                    class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 overflow-hidden hover:-translate-y-1"
                >
                    <!-- Card Header with Gradient -->
                    <div
                        class="relative h-32 bg-gradient-to-r from-indigo-500 to-purple-600"
                    >
                        <div
                            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3"
                        >
                            <Link
                                :href="
                                    route(
                                        'reports.preview',
                                        assignment.report.slug,
                                    )
                                "
                                target="_blank"
                                class="px-3 py-1.5 bg-white text-slate-900 rounded-lg text-xs font-semibold hover:bg-indigo-50 transition-colors"
                            >
                                <i class="fa-solid fa-eye mr-1"></i>Preview
                            </Link>
                            <Link
                                v-if="
                                    assignment.permission === 'edit' ||
                                    assignment.permission === 'manage'
                                "
                                :href="
                                    route(
                                        'reports.edit',
                                        assignment.report.slug,
                                    )
                                "
                                class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition-colors"
                            >
                                <i class="fa-solid fa-pen mr-1"></i>Edit
                            </Link>
                        </div>
                        <div class="absolute top-3 right-3">
                            <span
                                class="px-2 py-0.5 text-xs font-semibold rounded-full capitalize"
                                :class="
                                    assignment.permission === 'manage'
                                        ? 'bg-red-500'
                                        : assignment.permission === 'edit'
                                          ? 'bg-amber-500'
                                          : 'bg-emerald-500'
                                "
                            >
                                {{ assignment.permission }}
                            </span>
                        </div>
                        <div
                            class="absolute bottom-3 left-3 text-white text-sm font-semibold flex items-center gap-1"
                        >
                            <i class="fa-regular fa-file"></i>
                            <span
                                >{{ assignment.report.pages || 1 }} pages</span
                            >
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5">
                        <h3
                            class="font-bold text-lg text-slate-900 dark:text-white mb-1 line-clamp-1"
                        >
                            {{ assignment.report.title }}
                        </h3>
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 text-xs font-bold"
                            >
                                {{ assignment.assigned_by?.charAt(0) || "U" }}
                            </div>
                            <span class="text-xs text-slate-500"
                                >Shared by {{ assignment.assigned_by }}</span
                            >
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-3">
                            <span
                                class="text-xs px-2 py-1 rounded-full"
                                :class="
                                    assignment.report.status === 'published'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-amber-100 text-amber-700'
                                "
                            >
                                <i
                                    :class="
                                        assignment.report.status === 'published'
                                            ? 'fa-solid fa-globe'
                                            : 'fa-solid fa-pen-fancy'
                                    "
                                    class="text-xs mr-1"
                                ></i>
                                {{ assignment.report.status }}
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div
                                class="flex justify-between text-xs text-slate-500 mb-1"
                            >
                                <span>Completion</span>
                                <span>{{ assignment.progress || 0 }}%</span>
                            </div>
                            <div
                                class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-500 bg-indigo-500"
                                    :style="{
                                        width: `${assignment.progress || 0}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700"
                        >
                            <div
                                class="flex items-center gap-1 text-xs text-slate-500"
                            >
                                <i class="fa-regular fa-clock"></i>
                                <span>{{
                                    formatDate(assignment.assigned_at)
                                }}</span>
                            </div>
                            <div
                                v-if="assignment.expires_at"
                                class="text-xs text-amber-600"
                            >
                                <i
                                    class="fa-regular fa-hourglass-half mr-1"
                                ></i>
                                Expires: {{ formatDate(assignment.expires_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!filteredAssignments.length" class="text-center py-16">
                <div
                    class="w-24 h-24 rounded-3xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4"
                >
                    <i
                        class="fa-solid fa-share-alt text-3xl text-slate-400"
                    ></i>
                </div>
                <h3
                    class="text-lg font-semibold text-slate-900 dark:text-white mb-2"
                >
                    No shared reports
                </h3>
                <p class="text-slate-500 dark:text-slate-400">
                    When someone shares a report with you, it will appear here.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({ assignments: Array });
const search = ref("");

const filteredAssignments = computed(() => {
    const validAssignments = (props.assignments || []).filter((a) => a.report);
    if (!search.value) return validAssignments;
    return validAssignments.filter((a) =>
        a.report.title.toLowerCase().includes(search.value.toLowerCase()),
    );
});

const formatDate = (date) => new Date(date).toLocaleDateString();
const filterReports = () => {}; // computed handles it
=======
  <AuthenticatedLayout>
    <template #header>
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
          Shared Reports
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Reports shared with you by team members</p>
      </div>
    </template>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
      
      <!-- Search -->
      <div class="mb-6">
        <div class="relative max-w-md">
          <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input v-model="search" type="text" placeholder="Search shared reports..." @input="filterReports"
            class="w-full pl-11 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <!-- Reports Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="assignment in filteredAssignments" :key="assignment.id" 
             class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 overflow-hidden hover:-translate-y-1">
          
          <!-- Card Header with Gradient -->
          <div class="relative h-32 bg-gradient-to-r from-indigo-500 to-purple-600">
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
              <Link :href="route('reports.preview', assignment.report.slug)" target="_blank" 
                    class="px-3 py-1.5 bg-white text-slate-900 rounded-lg text-xs font-semibold hover:bg-indigo-50 transition-colors">
                <i class="fa-solid fa-eye mr-1"></i>Preview
              </Link>
              <Link v-if="assignment.permission === 'edit' || assignment.permission === 'manage'" 
                    :href="route('reports.edit', assignment.report.slug)" 
                    class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-pen mr-1"></i>Edit
              </Link>
            </div>
            <div class="absolute top-3 right-3">
              <span class="px-2 py-0.5 text-xs font-semibold rounded-full capitalize"
                :class="assignment.permission === 'manage' ? 'bg-red-500' : assignment.permission === 'edit' ? 'bg-amber-500' : 'bg-emerald-500'">
                {{ assignment.permission }}
              </span>
            </div>
            <div class="absolute bottom-3 left-3 text-white text-sm font-semibold flex items-center gap-1">
              <i class="fa-regular fa-file"></i>
              <span>{{ assignment.report.pages || 1 }} pages</span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-5">
            <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-1 line-clamp-1">{{ assignment.report.title }}</h3>
            <div class="flex items-center gap-2 mb-3">
              <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 text-xs font-bold">
                {{ assignment.assigned_by?.charAt(0) || 'U' }}
              </div>
              <span class="text-xs text-slate-500">Shared by {{ assignment.assigned_by }}</span>
            </div>
            
            <!-- Status Badge -->
            <div class="mb-3">
              <span class="text-xs px-2 py-1 rounded-full"
                :class="assignment.report.status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                <i :class="assignment.report.status === 'published' ? 'fa-solid fa-globe' : 'fa-solid fa-pen-fancy'" class="text-xs mr-1"></i>
                {{ assignment.report.status }}
              </span>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
              <div class="flex justify-between text-xs text-slate-500 mb-1">
                <span>Completion</span>
                <span>{{ assignment.progress || 0 }}%</span>
              </div>
              <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 bg-indigo-500" :style="{ width: `${assignment.progress || 0}%` }"></div>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-1 text-xs text-slate-500">
                <i class="fa-regular fa-clock"></i>
                <span>{{ formatDate(assignment.assigned_at) }}</span>
              </div>
              <div v-if="assignment.expires_at" class="text-xs text-amber-600">
                <i class="fa-regular fa-hourglass-half mr-1"></i>
                Expires: {{ formatDate(assignment.expires_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!filteredAssignments.length" class="text-center py-16">
        <div class="w-24 h-24 rounded-3xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
          <i class="fa-solid fa-share-alt text-3xl text-slate-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No shared reports</h3>
        <p class="text-slate-500 dark:text-slate-400">When someone shares a report with you, it will appear here.</p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ assignments: Array })
const search = ref('')

const filteredAssignments = computed(() => {
  if (!search.value) return props.assignments || []
  return (props.assignments || []).filter(a => 
    a.report?.title?.toLowerCase().includes(search.value.toLowerCase())
  )
})

const formatDate = (date) => new Date(date).toLocaleDateString()
const filterReports = () => {} // computed handles it
>>>>>>> 59ec3734d4926d87f116c2407e482f7b7a72d747
</script>
