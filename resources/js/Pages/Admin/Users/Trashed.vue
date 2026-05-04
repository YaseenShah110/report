<!--
  Admin/Users/Trashed.vue - Trashed Users Page
  -----------------------------------------------------------
  Displays soft-deleted users for administrators.
  Admins can restore users or permanently delete them.
  Includes search functionality and responsive design.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Trashed Users</h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Users that have been soft-deleted</p>
        </div>
        <Link :href="route('admin.users.index')" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 border rounded-xl text-xs sm:text-sm">
          <i class="fa-solid fa-arrow-left text-xs"></i> Back to Users
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Search -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border">
        <div class="relative max-w-md">
          <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input v-model="filters.search" type="text" placeholder="Search by name or email..." @keyup.enter="applyFilters"
            class="w-full pl-9 pr-3 py-2 sm:py-2.5 border rounded-xl bg-white dark:bg-slate-900 text-xs sm:text-sm">
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold">User</th>
                <th class="px-3 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-semibold hidden md:table-cell">Deleted At</th>
                <th class="px-3 sm:px-6 py-3 text-center text-[10px] sm:text-xs font-semibold hidden sm:table-cell">Reports</th>
                <th class="px-3 sm:px-6 py-3 text-center text-[10px] sm:text-xs font-semibold hidden sm:table-cell">Tasks</th>
                <th class="px-3 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center text-white font-bold text-xs">
                      {{ user.name?.charAt(0)?.toUpperCase() || '?' }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-xs sm:text-sm truncate">{{ user.name }}</p>
                      <p class="text-[10px] sm:text-xs text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-slate-500 hidden md:table-cell">{{ formatDate(user.deleted_at) }}</td>
                <td class="px-3 sm:px-6 py-3 text-center hidden sm:table-cell text-xs">{{ user.reports_count || 0 }}</td>
                <td class="px-3 sm:px-6 py-3 text-center hidden sm:table-cell text-xs">{{ user.tasks_count || 0 }}</td>
                <td class="px-3 sm:px-6 py-3 text-right">
                  <div class="flex items-center justify-end gap-1 sm:gap-2">
                    <button @click="restoreUser(user)" class="p-1.5 sm:p-2 rounded-lg hover:bg-emerald-100 text-emerald-600" title="Restore"><i class="fa-solid fa-rotate-left text-xs"></i></button>
                    <button @click="confirmForceDelete(user)" class="p-1.5 sm:p-2 rounded-lg hover:bg-red-100 text-red-600" title="Delete Forever"><i class="fa-solid fa-trash-can text-xs"></i></button>
                  </div>
                </td>
              </tr>
              <tr v-if="!users.data?.length">
                <td colspan="5" class="py-12 text-center text-slate-400">No trashed users found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="users.links?.length > 3" class="px-3 sm:px-6 py-3 border-t">
          <Pagination :links="users.links" :from="users.from" :to="users.to" :total="users.total" />
        </div>
      </div>
    </div>

    <ConfirmationModal :show="deleteModal.show" title="Permanently Delete User?" :message="`Are you sure you want to permanently delete ${deleteModal.user?.name}? This cannot be undone.`" @close="deleteModal.show = false" @confirm="forceDeleteUser" />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({ users: Object, filters: Object })
const filters = reactive({ search: props.filters?.search || '' })
const deleteModal = ref({ show: false, user: null })

const formatDate = (date) => new Date(date).toLocaleString()
const applyFilters = () => router.get(route('admin.users.trashed'), filters, { preserveState: true })

const restoreUser = (user) => router.post(route('admin.users.restore', user.id), {}, { onSuccess: () => window.showToast?.('User restored', 'success') })
const confirmForceDelete = (user) => { deleteModal.value = { show: true, user } }
const forceDeleteUser = () => router.delete(route('admin.users.force-delete', deleteModal.value.user.id), { onSuccess: () => { deleteModal.value.show = false; window.showToast?.('User permanently deleted', 'success') } })
</script>