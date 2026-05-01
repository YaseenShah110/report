<!-- resources/js/Pages/Admin/Users/Index.vue -->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            User Management
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage system users, roles, and permissions</p>
        </div>
        <div class="flex gap-3">
          <button @click="exportUsers" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
            <i class="fa-solid fa-download"></i>
            <span>Export</span>
          </button>
          <Link :href="route('admin.users.create')" 
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-500/25">
            <i class="fa-solid fa-plus"></i>
            <span>Add User</span>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Total Users</p>
              <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-users text-indigo-600 text-xl"></i>
            </div>
          </div>
          <div class="text-xs text-slate-500">{{ stats.new_today }} new today</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Active Users</p>
              <p class="text-3xl font-bold text-emerald-600">{{ stats.active }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
            </div>
          </div>
          <div class="text-xs text-slate-500">Verified accounts</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Premium Users</p>
              <p class="text-3xl font-bold text-amber-600">{{ stats.premium }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-crown text-amber-600 text-xl"></i>
            </div>
          </div>
          <div class="text-xs text-slate-500">Premium members</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 group">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Pending Invites</p>
              <p class="text-3xl font-bold text-violet-600">{{ stats.pending_invites || 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-envelope text-violet-600 text-xl"></i>
            </div>
          </div>
          <div class="text-xs text-slate-500">Awaiting response</div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 mb-6 border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[250px]">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input 
                v-model="filters.search" 
                type="text" 
                placeholder="Search by name or email..." 
                @input="debouncedSearch"
                class="w-full pl-11 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500"
              >
            </div>
          </div>
          <select v-model="filters.role" @change="applyFilters" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
            <option value="">All Roles</option>
            <option v-for="role in roles" :key="role.name" :value="role.name">{{ role.name | capitalize }}</option>
          </select>
          <select v-model="filters.sort" @change="applyFilters" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
            <option value="created_at">Date Joined</option>
            <option value="name">Name</option>
            <option value="email">Email</option>
            <option value="reports_count">Most Reports</option>
          </select>
          <button @click="resetFilters" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
            <i class="fa-solid fa-rotate-right mr-2"></i>Reset
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Reports</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tasks</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Joined</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-md">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-semibold text-slate-900 dark:text-white">{{ user.name }}</p>
                      <p class="text-xs text-slate-500">{{ user.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="role in user.roles" :key="role.name" 
                      class="px-2.5 py-1 text-xs font-semibold rounded-full"
                      :class="role.name === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 
                             role.name === 'manager' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                             'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'">
                      {{ role.name }}
                    </span>
                    <span v-if="user.is_premium" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">
                      <i class="fa-solid fa-crown text-[8px] mr-0.5"></i>Premium
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="user.email_verified_at ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                    class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ user.email_verified_at ? 'Active' : 'Pending' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-1">
                    <i class="fa-regular fa-file-lines text-slate-400"></i>
                    <span class="text-slate-700 dark:text-slate-300">{{ user.reports_count }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-1">
                    <i class="fa-regular fa-clock text-slate-400"></i>
                    <span class="text-slate-700 dark:text-slate-300">{{ user.tasks_count }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ formatDate(user.created_at) }}</td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Link :href="route('admin.users.edit', user.id)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400" title="Edit">
                      <i class="fa-solid fa-pen"></i>
                    </Link>
                    <button @click="impersonateUser(user)" v-if="$page.props.auth.user.id !== user.id && isAdmin" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400" title="Impersonate">
                      <i class="fa-solid fa-mask"></i>
                    </button>
                    <button @click="confirmDelete(user)" v-if="$page.props.auth.user.id !== user.id && isAdmin" class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400" title="Delete">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
          <Pagination :links="users.links" />
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div v-if="deleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="deleteModal.show = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
              <i class="fa-solid fa-trash text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete User</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">
              Are you sure you want to delete <span class="font-semibold text-slate-900 dark:text-white">{{ deleteModal.user?.name }}</span>? 
              This action cannot be undone.
            </p>
            <div class="flex gap-3">
              <button @click="deleteModal.show = false" class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Cancel
              </button>
              <button @click="deleteUser" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-colors">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const page = usePage()
const props = defineProps({
  users: Object,
  roles: Array,
  stats: Object,
  filters: Object
})

const deleteModal = ref({ show: false, user: null })
const isAdmin = computed(() => page.props.auth.user?.roles?.includes('admin'))

const filters = reactive({
  search: props.filters?.search || '',
  role: props.filters?.role || '',
  sort: props.filters?.sort || 'created_at'
})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => applyFilters(), 500)
}

const applyFilters = () => {
  router.get(route('admin.users.index'), filters, { preserveState: true })
}

const resetFilters = () => {
  filters.search = ''
  filters.role = ''
  filters.sort = 'created_at'
  applyFilters()
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const confirmDelete = (user) => {
  deleteModal.value = { show: true, user }
}

const deleteUser = () => {
  router.delete(route('admin.users.destroy', deleteModal.value.user.id), {
    onSuccess: () => {
      deleteModal.value.show = false
      window.showToast('User deleted successfully', 'success')
    }
  })
}

const impersonateUser = (user) => {
  router.post(route('admin.users.impersonate', user.id), {}, {
    onSuccess: () => {
      window.showToast(`Impersonating ${user.name}`, 'info')
    }
  })
}

const exportUsers = () => {
  window.open(route('admin.users.export', filters), '_blank')
}

// Capitalize filter
// Vue.filter('capitalize', function(value) {
//   if (!value) return ''
//   return value.charAt(0).toUpperCase() + value.slice(1)
// })
</script>