<!--
  Admin/Roles/Permissions.vue - Permission Management Page
  -----------------------------------------------------------
  Displays all permissions grouped by category.
  Admins can create, edit, and delete permissions.
  Shows stats cards and modal forms for CRUD operations.
  Admin only access.
-->
<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Permission Management</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Manage system permissions</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('admin.roles.index')" 
                class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to Roles
          </Link>
          <button @click="showCreateModal = true" 
                  class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Create Permission
          </button>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-8 px-3 sm:px-4 lg:px-6 max-w-7xl mx-auto">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500">Total Permissions</p>
              <p class="text-2xl font-bold">{{ stats.total_permissions }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
              <i class="fa-solid fa-key text-indigo-600"></i>
            </div>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border">
          <div class="flex items-center justify-between">
            <div><p class="text-xs text-slate-500">Total Roles</p><p class="text-2xl font-bold">{{ stats.total_roles }}</p></div>
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center"><i class="fa-solid fa-users text-purple-600"></i></div>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border">
          <div class="flex items-center justify-between">
            <div><p class="text-xs text-slate-500">Permission Groups</p><p class="text-2xl font-bold">{{ Object.keys(permissions).length }}</p></div>
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center"><i class="fa-solid fa-layer-group text-emerald-600"></i></div>
          </div>
        </div>
      </div>

      <!-- Permissions by Category -->
      <div class="space-y-4 sm:space-y-6">
        <div v-for="(perms, category) in permissions" :key="category" 
             class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
          
          <!-- Category Header -->
          <div class="px-4 sm:px-6 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
              <h3 class="text-sm sm:text-lg font-semibold text-slate-900 dark:text-white capitalize">{{ category }}</h3>
              <p class="text-[10px] sm:text-xs text-slate-500">{{ perms.length }} permissions</p>
            </div>
          </div>
          
          <!-- Permissions Grid -->
          <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
              <div v-for="permission in perms" :key="permission.name" 
                   class="flex items-center justify-between p-2 sm:p-3 bg-slate-50 dark:bg-slate-900/30 rounded-xl group hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                <div class="flex items-center gap-2 min-w-0">
                  <i class="fa-solid fa-circle-check text-indigo-500 text-[10px] sm:text-xs flex-shrink-0"></i>
                  <span class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-mono truncate">{{ permission.name }}</span>
                </div>
                
                <!-- Hover Actions -->
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                  <button @click="editPermission(permission)" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Edit">
                    <i class="fa-solid fa-pen text-[10px] sm:text-xs text-slate-500"></i>
                  </button>
                  <button @click="confirmDeletePermission(permission)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                    <i class="fa-solid fa-trash text-[10px] sm:text-xs text-red-500"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Permission Modal -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showCreateModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <form @submit.prevent="createPermission">
            <div class="p-4 sm:p-6">
              <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-4">Create Permission</h3>
              <div class="space-y-3 sm:space-y-4">
                <div>
                  <label class="block text-xs sm:text-sm font-semibold mb-1.5">Permission Name</label>
                  <input type="text" v-model="newPermission.name" required
                    class="w-full px-3 sm:px-4 py-2 border rounded-xl bg-white dark:bg-slate-900 text-sm"
                    placeholder="e.g., edit-reports">
                  <p class="text-[10px] sm:text-xs text-slate-500 mt-1">Format: action-resource (e.g., view-users, edit-reports)</p>
                </div>
                <div>
                  <label class="block text-xs sm:text-sm font-semibold mb-1.5">Group (Optional)</label>
                  <input type="text" v-model="newPermission.group"
                    class="w-full px-3 sm:px-4 py-2 border rounded-xl bg-white dark:bg-slate-900 text-sm"
                    placeholder="e.g., Reports, Users, Tasks">
                </div>
              </div>
            </div>
            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-t flex gap-3">
              <button type="button" @click="showCreateModal = false" class="flex-1 px-4 py-2 border rounded-xl text-sm">Cancel</button>
              <button type="submit" :disabled="creating" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold">
                {{ creating ? 'Creating...' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Edit Permission Modal -->
    <Teleport to="body">
      <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
          <form @submit.prevent="updatePermission">
            <div class="p-4 sm:p-6">
              <h3 class="text-base sm:text-lg font-bold mb-4">Edit Permission</h3>
              <div>
                <label class="block text-xs sm:text-sm font-semibold mb-1.5">Permission Name</label>
                <input type="text" v-model="editingPermission.name" required
                  class="w-full px-3 sm:px-4 py-2 border rounded-xl bg-white dark:bg-slate-900 text-sm">
              </div>
            </div>
            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-slate-50 dark:bg-slate-900/50 border-t flex gap-3">
              <button type="button" @click="showEditModal = false" class="flex-1 px-4 py-2 border rounded-xl text-sm">Cancel</button>
              <button type="submit" :disabled="updating" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold">
                {{ updating ? 'Updating...' : 'Update' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal 
      :show="showDeleteModal" 
      title="Delete Permission?" 
      :message="`Are you sure you want to delete permission &quot;${deleteTarget?.name}&quot;?`"
      @close="showDeleteModal = false" 
      @confirm="deletePermission" 
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({ permissions: Object, stats: Object })

// Modal visibility
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

// Loading states
const creating = ref(false)
const updating = ref(false)

// Form data
const newPermission = reactive({ name: '', group: '' })
const editingPermission = reactive({ id: null, name: '' })
const deleteTarget = ref(null)

/**
 * Create a new permission
 */
const createPermission = () => {
  creating.value = true
  router.post(route('admin.roles.permissions.store'), newPermission, {
    onSuccess: () => {
      showCreateModal.value = false
      newPermission.name = ''
      newPermission.group = ''
      creating.value = false
    },
    onError: () => { creating.value = false }
  })
}

/**
 * Open edit modal with permission data
 */
const editPermission = (permission) => {
  editingPermission.id = permission.id
  editingPermission.name = permission.name
  showEditModal.value = true
}

/**
 * Update an existing permission
 */
const updatePermission = () => {
  updating.value = true
  router.put(route('admin.roles.permissions.update', editingPermission.id), {
    name: editingPermission.name
  }, {
    onSuccess: () => { showEditModal.value = false; updating.value = false },
    onError: () => { updating.value = false }
  })
}

/**
 * Show delete confirmation
 */
const confirmDeletePermission = (permission) => {
  deleteTarget.value = permission
  showDeleteModal.value = true
}

/**
 * Delete a permission
 */
const deletePermission = () => {
  router.delete(route('admin.roles.permissions.destroy', deleteTarget.value.id), {
    onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null }
  })
}
</script>

<style scoped>
@keyframes scale-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.animate-scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>