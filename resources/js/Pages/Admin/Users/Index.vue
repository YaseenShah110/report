<!--
  Admin/Users/Index.vue - User Management Page
  -----------------------------------------------------------
  Displays all users for administrators/managers.
  Features: CRUD, search, filter by role, impersonation, soft delete.
  Shows stats cards and responsive table.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">User Management</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Manage system users, roles, and permissions</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
          <button @click="exportUsers" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
            <i class="fa-solid fa-download text-xs"></i> Export
          </button>
          <Link :href="route('admin.users.create')" class="inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-500/25">
            <i class="fa-solid fa-plus text-xs"></i> Add User
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-4 sm:mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-2 sm:mb-3">
            <div>
              <p class="text-[10px] sm:text-sm text-slate-500 dark:text-slate-400">Total Users</p>
              <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ stats.total }}</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-users text-indigo-600 text-lg sm:text-xl"></i>
            </div>
          </div>
          <div class="text-[10px] sm:text-xs text-slate-500">{{ stats.new_today }} new today</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-2 sm:mb-3">
            <div><p class="text-[10px] sm:text-sm text-slate-500">Active Users</p><p class="text-2xl sm:text-3xl font-bold text-emerald-600">{{ stats.active }}</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fa-solid fa-circle-check text-emerald-600 text-lg sm:text-xl"></i></div>
          </div>
          <div class="text-[10px] sm:text-xs text-slate-500">Verified accounts</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-2 sm:mb-3">
            <div><p class="text-[10px] sm:text-sm text-slate-500">Premium Users</p><p class="text-2xl sm:text-3xl font-bold text-amber-600">{{ stats.premium }}</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fa-solid fa-crown text-amber-600 text-lg sm:text-xl"></i></div>
          </div>
          <div class="text-[10px] sm:text-xs text-slate-500">Premium members</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-2 sm:mb-3">
            <div><p class="text-[10px] sm:text-sm text-slate-500">Trashed</p><p class="text-2xl sm:text-3xl font-bold text-red-600">{{ stats.trashed || 0 }}</p></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fa-solid fa-trash-can text-red-600 text-lg sm:text-xl"></i></div>
          </div>
          <Link :href="route('admin.users.trashed')" class="text-[10px] sm:text-xs text-red-500 hover:underline">View Trash →</Link>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 sm:p-5 mb-4 sm:mb-6 border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-2 sm:gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
              <input v-model="filters.search" type="text" placeholder="Search by name or email..." @input="debouncedSearch"
                class="w-full pl-9 sm:pl-11 pr-3 sm:pr-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
          <select v-model="filters.role" @change="applyFilters" class="px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-xs sm:text-sm">
            <option value="">All Roles</option>
            <option v-for="role in roles" :key="role.name" :value="role.name">{{ role.name }}</option>
          </select>
          <select v-model="filters.sort" @change="applyFilters" class="px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-xs sm:text-sm">
            <option value="created_at">Date Joined</option>
            <option value="name">Name</option>
            <option value="email">Email</option>
            <option value="reports_count">Most Reports</option>
          </select>
          <button @click="resetFilters" class="px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all text-xs sm:text-sm">
            <i class="fa-solid fa-rotate-right mr-1.5"></i> Reset
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Status</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Reports</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Tasks</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Joined</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-right text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                
                <!-- User Info -->
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-md flex-shrink-0">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-semibold text-slate-900 dark:text-white text-xs sm:text-sm truncate">{{ user.name }}</p>
                      <p class="text-[10px] sm:text-xs text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                  </div>
                </td>
                
                <!-- Roles -->
                <td class="px-3 sm:px-6 py-3 sm:py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="role in user.roles" :key="role.name" 
                      class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 text-[9px] sm:text-xs font-semibold rounded-full"
                      :class="role.name === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : role.name === 'manager' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700'">
                      {{ role.name }}
                    </span>
                    <span v-if="user.is_premium" class="px-1.5 py-0.5 text-[8px] sm:text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">
                      <i class="fa-solid fa-crown text-[7px] mr-0.5"></i>Premium
                    </span>
                  </div>
                </td>
                
                <!-- Status -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">
                  <span :class="user.email_verified_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-full">
                    {{ user.email_verified_at ? 'Active' : 'Pending' }}
                  </span>
                </td>
                
                <!-- Reports Count -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">
                  <span class="text-slate-700 dark:text-slate-300 text-xs sm:text-sm">{{ user.reports_count }}</span>
                </td>
                
                <!-- Tasks Count -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">
                  <span class="text-slate-700 dark:text-slate-300 text-xs sm:text-sm">{{ user.tasks_count }}</span>
                </td>
                
                <!-- Joined Date -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-500 hidden lg:table-cell">{{ formatDate(user.created_at) }}</td>
                
                <!-- Actions -->
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                  <div class="flex items-center justify-end gap-0.5 sm:gap-1">
                    <Link :href="route('admin.users.show', user.id)" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 text-slate-600" title="View"><i class="fa-solid fa-eye text-xs"></i></Link>
                    <Link :href="route('admin.users.edit', user.id)" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 text-slate-600" title="Edit"><i class="fa-solid fa-pen text-xs"></i></Link>
                    <button v-if="$page.props.auth.user.id !== user.id && isAdmin" @click="impersonateUser(user)" class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 text-slate-600" title="Impersonate"><i class="fa-solid fa-mask text-xs"></i></button>
                    <button v-if="$page.props.auth.user.id !== user.id" @click="confirmDelete(user)" class="p-1.5 sm:p-2 rounded-lg hover:bg-red-100 text-red-600" title="Delete"><i class="fa-solid fa-trash text-xs"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-3 sm:px-6 py-3 sm:py-4 border-t border-slate-200 dark:border-slate-700">
          <Pagination :links="users.links" :from="users.from" :to="users.to" :total="users.total" />
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal 
      :show="deleteModal.show" 
      title="Delete User?" 
      :message="`Are you sure you want to delete ${deleteModal.user?.name}? They will be moved to trash.`"
      confirm-text="Delete"
      @close="deleteModal.show = false" 
      @confirm="deleteUser" 
    />
  </AuthenticatedLayout>
</template>

<script setup>
/**
 * Admin Users Index Script
 * Handles: user listing, filtering, search, delete with confirmation, impersonation, export
 */
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const page = usePage()
const props = defineProps({ users: Object, roles: Array, stats: Object, filters: Object })

const deleteModal = ref({ show: false, user: null })
const isAdmin = computed(() => page.props.auth.user?.roles?.includes('admin'))

const filters = reactive({
  search: props.filters?.search || '',
  role: props.filters?.role || '',
  sort: props.filters?.sort || 'created_at'
})

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => applyFilters(), 500)
}

const applyFilters = () => router.get(route('admin.users.index'), filters, { preserveState: true })
const resetFilters = () => { filters.search = ''; filters.role = ''; filters.sort = 'created_at'; applyFilters() }

const formatDate = (date) => date ? new Date(date).toLocaleDateString() : 'N/A'

const confirmDelete = (user) => { deleteModal.value = { show: true, user } }
const deleteUser = () => {
  router.delete(route('admin.users.destroy', deleteModal.value.user.id), {
    onSuccess: () => { deleteModal.value.show = false; window.showToast?.('User moved to trash', 'success') }
  })
}

const impersonateUser = (user) => {
  router.post(route('admin.users.impersonate', user.id), {}, {
    onSuccess: () => window.showToast?.(`Impersonating ${user.name}`, 'info')
  })
}

const exportUsers = () => window.open(route('admin.users.export', filters), '_blank')
</script>