<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

/**
 * Role & Permission Controller
 * 
 * Manages roles and permissions using Spatie package.
 * Supports CRUD for roles, CRUD for permissions, and assignment.
 * 
 * Access: Admin only
 */
class RoleController extends Controller
{
    /**
     * Display list of roles with permissions count and users count.
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->when($request->search, fn($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($request->sort ?? 'name', $request->direction ?? 'asc')
            ->paginate(10)
            ->withQueryString()
            ->through(function($role) {
                return [
                    'id'               => $role->id,
                    'name'             => $role->name,
                    'guard_name'       => $role->guard_name,
                    'permissions_count' => $role->permissions->count(),
                    'users_count'      => $role->users()->count(),
                    'created_at'       => $role->created_at,
                    'updated_at'       => $role->updated_at,
                ];
            });

        // Get all permissions grouped by category for the create/edit forms
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[1] ?? $parts[0] ?? 'general');
        });

        $stats = [
            'total_roles'            => Role::count(),
            'total_permissions'      => Permission::count(),
            'total_users_with_roles' => User::role(Role::all()->pluck('name')->toArray())->count(),
        ];

        return Inertia::render('Admin/Roles/Index', [
            'roles'       => $roles,
            'permissions' => $permissions,
            'stats'       => $stats,
            'filters'     => $request->only(['search', 'sort', 'direction'])
        ]);
    }

    /**
     * Show create role form with all permissions grouped.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[1] ?? $parts[0] ?? 'general');
        });

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a new role with selected permissions.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'web',
        ]);

        // Assign selected permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        // Log activity
        UserActivity::log(auth()->id(), 'role_created', 'role', $role->id, [
            'role_name'         => $role->name,
            'permissions_count' => count($request->permissions ?? [])
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display role details with permissions and users.
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[1] ?? $parts[0] ?? 'general');
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return Inertia::render('Admin/Roles/Show', [
            'role' => [
                'id'             => $role->id,
                'name'           => $role->name,
                'guard_name'     => $role->guard_name,
                'created_at'     => $role->created_at,
                'updated_at'     => $role->updated_at,
                'permissions'    => $rolePermissions,
                'users_count'    => $role->users()->count(),
            ],
            'permissions' => $permissions,
            'users'       => $role->users()->take(10)->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Show edit role form with current permissions.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[1] ?? $parts[0] ?? 'general');
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id'   => $role->id,
                'name' => $role->name,
            ],
            'permissions'     => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update role name and permissions.
     * Prevents renaming the admin role.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles')->ignore($role->id),
            ],
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Prevent renaming admin role
        if ($role->name === 'admin' && $request->name !== 'admin') {
            return back()->with('error', 'Cannot rename the admin role.');
        }

        $oldName = $role->name;
        $role->update(['name' => $request->name]);

        // Sync permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        UserActivity::log(auth()->id(), 'role_updated', 'role', $role->id, [
            'old_name'         => $oldName,
            'new_name'         => $role->name,
            'permissions_count' => count($request->permissions ?? [])
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Delete a role.
     * Prevents deletion of admin role and roles with users.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Cannot delete the admin role.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users. Remove users first.');
        }

        $roleName = $role->name;
        $role->delete();

        UserActivity::log(auth()->id(), 'role_deleted', 'role', $role->id, [
            'role_name' => $roleName
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Display all permissions grouped by category.
     */
    public function permissions(Request $request)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[1] ?? $parts[0] ?? 'general');
        });

        $stats = [
            'total_permissions'  => Permission::count(),
            'total_roles'        => Role::count(),
        ];

        return Inertia::render('Admin/Roles/Permissions', [
            'permissions' => $permissions,
            'stats'       => $stats,
        ]);
    }

    /**
     * Create a new permission.
     */
    public function storePermission(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:permissions,name',
            'group' => 'nullable|string|max:100',
        ]);

        $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'web',
        ]);

        UserActivity::log(auth()->id(), 'permission_created', 'permission', $permission->id, [
            'permission_name' => $permission->name,
            'group'           => $request->group
        ]);

        return redirect()->route('admin.roles.permissions')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Update a permission name.
     */
    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($permission->id)],
        ]);

        $oldName = $permission->name;
        $permission->update(['name' => $request->name]);

        UserActivity::log(auth()->id(), 'permission_updated', 'permission', $permission->id, [
            'old_name' => $oldName,
            'new_name' => $permission->name
        ]);

        return redirect()->route('admin.roles.permissions')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Delete a permission.
     * Prevents deletion if assigned to any role.
     */
    public function destroyPermission(Permission $permission)
    {
        $rolesWithPermission = Role::whereHas('permissions', fn($query) => 
            $query->where('permission_id', $permission->id)
        )->count();

        if ($rolesWithPermission > 0) {
            return back()->with('error', 'Cannot delete permission assigned to roles.');
        }

        $permissionName = $permission->name;
        $permission->delete();

        UserActivity::log(auth()->id(), 'permission_deleted', 'permission', $permission->id, [
            'permission_name' => $permissionName
        ]);

        return redirect()->route('admin.roles.permissions')
            ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Bulk assign permissions to a role.
     */
    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions);

        UserActivity::log(auth()->id(), 'permissions_assigned', 'role', $role->id, [
            'role_name'         => $role->name,
            'permissions_count' => count($request->permissions)
        ]);

        return response()->json([
            'message'           => 'Permissions assigned successfully.',
            'permissions_count' => $role->permissions()->count()
        ]);
    }

    /**
     * Remove a specific permission from a role.
     */
    public function removePermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        UserActivity::log(auth()->id(), 'permission_removed', 'role', $role->id, [
            'role_name'       => $role->name,
            'permission_name' => $permission->name
        ]);

        return response()->json(['message' => 'Permission removed successfully.']);
    }

    /**
     * Assign a role to a user.
     */
    public function assignToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Only admins can assign admin role
        if ($role->name === 'admin' && !auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Only administrators can assign admin role.'], 403);
        }

        $user->assignRole($role);

        UserActivity::log(auth()->id(), 'role_assigned_to_user', 'user', $user->id, [
            'user_name' => $user->name,
            'role_name' => $role->name
        ]);

        return response()->json(['message' => 'Role assigned successfully.']);
    }

    /**
     * Remove a role from a user.
     */
    public function removeFromUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Prevent removing own admin role
        if ($user->id === auth()->id() && $role->name === 'admin') {
            return response()->json(['error' => 'Cannot remove your own admin role.'], 403);
        }

        $user->removeRole($role);

        UserActivity::log(auth()->id(), 'role_removed_from_user', 'user', $user->id, [
            'user_name' => $user->name,
            'role_name' => $role->name
        ]);

        return response()->json(['message' => 'Role removed successfully.']);
    }

    /**
     * Get role statistics for API.
     */
    public function getStats()
    {
        return response()->json([
            'total_roles'            => Role::count(),
            'total_permissions'      => Permission::count(),
            'total_users_with_roles' => User::role(Role::all()->pluck('name')->toArray())->count(),
            'roles_distribution'     => Role::withCount('users')->get()->map(fn($role) => [
                'name'        => $role->name,
                'users_count' => $role->users_count,
            ]),
        ]);
    }

    /**
     * Setup default roles and permissions (development only).
     */
    public function setupDefaultRoles()
    {
        if (!app()->environment('local') && !auth()->user()?->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Re-run the seeder logic
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-users', 'create-users', 'edit-users', 'delete-users', 'manage-users',
            'view-reports', 'create-reports', 'edit-reports', 'delete-reports', 'manage-reports', 'assign-reports',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'manage-tasks',
            'view-templates', 'create-templates', 'edit-templates', 'delete-templates', 'manage-templates',
            'manage-settings', 'view-analytics', 'manage-roles', 'view-activities',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        $adminRole->givePermissionTo(Permission::all());
        
        $managerRole->givePermissionTo([
            'view-users', 'view-reports', 'create-reports', 'edit-reports', 'assign-reports',
            'view-tasks', 'create-tasks', 'edit-tasks', 'manage-tasks',
            'view-templates', 'view-analytics', 'view-activities',
        ]);
        
        $userRole->givePermissionTo([
            'view-reports', 'create-reports', 'edit-reports', 'view-tasks', 'view-templates',
        ]);

        return response()->json(['message' => 'Default roles and permissions created.']);
    }
}