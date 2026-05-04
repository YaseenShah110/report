<!--
  Admin/Roles/Index.vue - Role Management Page
  -----------------------------------------------------------
  Displays all roles with permissions count and user count.
  Admin only access. Includes create, edit, delete, manage permissions links.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Role Management</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Manage roles and permissions</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('admin.roles.create')" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Create Role
          </Link>
          <Link :href="route('admin.roles.permissions')" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-key text-xs"></i> Permissions
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border"><p class="text-xs text-slate-500">Total Roles</p><p class="text-2xl font-bold">{{ stats.total_roles }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border"><p class="text-xs text-slate-500">Total Permissions</p><p class="text-2xl font-bold">{{ stats.total_permissions }}</p></div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border"><p class="text-xs text-slate-500">Users with Roles</p><p class="text-2xl font-bold">{{ stats.total_users_with_roles }}</p></div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-4 mb-4 sm:mb-6 border">
        <div class="flex flex-wrap gap-2 sm:gap-3">
          <div class="flex-1 min-w-[200px]"><div class="relative"><i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i><input v-model="filters.search" type="text" placeholder="Search roles..." class="w-full pl-9 pr-3 py-2 border rounded-xl bg-white dark:bg-slate-900 text-sm"></div></div>
          <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold">Apply</button>
          <button @click="resetFilters" class="px-4 py-2 border rounded-xl text-sm">Reset</button>
        </div>
      </div>

      <!-- Roles Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold uppercase">Role</th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold uppercase hidden sm:table-cell">Permissions</th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold uppercase hidden md:table-cell">Users</th>
                <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="role in roles.data" :key="role.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-shield-alt text-white text-xs"></i></div>
                    <span class="font-medium capitalize text-sm">{{ role.name }}</span>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell"><span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 text-xs rounded-full">{{ role.permissions_count }} permissions</span></td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell text-sm">{{ role.users_count }} users</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                  <div class="flex items-center justify-end gap-1 sm:gap-2">
                    <Link :href="route('admin.roles.show', role.id)" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-eye text-xs"></i></Link>
                    <Link :href="route('admin.roles.edit', role.id)" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-pen text-xs"></i></Link>
                    <button v-if="role.name !== 'admin'" @click="confirmDelete(role)" class="p-1.5 sm:p-2 rounded-lg hover:bg-red-100 text-red-600"><i class="fa-solid fa-trash text-xs"></i></button>
                  </div>
                </td>
              </tr>
              <tr v-if="!roles.data?.length"><td colspan="4" class="py-12 text-center text-slate-400">No roles found.</td></tr>
            </tbody>
          </table>
        </div>
        <div v-if="roles.links?.length > 3" class="px-3 sm:px-6 py-3 border-t"><Pagination :links="roles.links" /></div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal :show="deleteModal.show" title="Delete Role?" :message="`Are you sure you want to delete role &quot;${deleteModal.role?.name}&quot;?`" @close="deleteModal.show = false" @confirm="deleteRole" />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({ roles: Object, stats: Object, filters: Object })
const deleteModal = ref({ show: false, role: null })
const filters = reactive({ search: props.filters?.search || '' })

const applyFilters = () => router.get(route('admin.roles.index'), filters, { preserveState: true })
const resetFilters = () => { filters.search = ''; applyFilters() }
const confirmDelete = (role) => { deleteModal.value = { show: true, role } }
const deleteRole = () => router.delete(route('admin.roles.destroy', deleteModal.value.role.id), { onSuccess: () => { deleteModal.value.show = false } })
</script>