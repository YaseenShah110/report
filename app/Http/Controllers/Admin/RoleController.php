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

class RoleController extends Controller
{
   
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy($request->sort ?? 'name', $request->direction ?? 'asc')
            ->paginate(10)
            ->through(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => $role->users()->count(),
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ];
            });

        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[0] ?? 'general');
        });

        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_users_with_roles' => User::role(Role::all())->count(),
        ];

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'stats' => $stats,
            'filters' => $request->only(['search', 'sort', 'direction'])
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[0] ?? 'general');
        });

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        UserActivity::log(auth()->id(), 'role_created', 'role', $role->id, [
            'role_name' => $role->name,
            'permissions_count' => count($request->permissions ?? [])
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[0] ?? 'general');
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return Inertia::render('Admin/Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
                'permissions' => $rolePermissions,
                'users_count' => $role->users()->count(),
            ],
            'permissions' => $permissions,
            'users' => $role->users()->take(10)->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[0] ?? 'general');
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ],
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id),
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Prevent changing admin role name
        if ($role->name === 'admin' && $request->name !== 'admin') {
            return back()->with('error', 'Cannot rename the admin role.');
        }

        $oldName = $role->name;
        $role->update([
            'name' => $request->name,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        UserActivity::log(auth()->id(), 'role_updated', 'role', $role->id, [
            'old_name' => $oldName,
            'new_name' => $role->name,
            'permissions_count' => count($request->permissions ?? [])
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of admin role
        if ($role->name === 'admin') {
            return back()->with('error', 'Cannot delete the admin role.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users. Remove users from this role first.');
        }

        $roleName = $role->name;
        
        UserActivity::log(auth()->id(), 'role_deleted', 'role', $role->id, [
            'role_name' => $roleName
        ]);

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Display permissions management page.
     */
    public function permissions(Request $request)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[0] ?? 'general');
        });

        $stats = [
            'total_permissions' => Permission::count(),
            'total_roles' => Role::count(),
            'permissions_by_group' => $permissions->map(fn($group) => $group->count()),
        ];

        return Inertia::render('Admin/Roles/Permissions', [
            'permissions' => $permissions,
            'stats' => $stats,
        ]);
    }

    /**
     * Create a new permission.
     */
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'group' => 'nullable|string|max:100',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        UserActivity::log(auth()->id(), 'permission_created', 'permission', $permission->id, [
            'permission_name' => $permission->name,
            'group' => $request->group
        ]);

        return redirect()->route('admin.roles.permissions')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Update a permission.
     */
    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->ignore($permission->id),
            ],
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
     */
    public function destroyPermission(Permission $permission)
    {
        // Check if permission is assigned to any role
        $rolesWithPermission = Role::whereHas('permissions', function($query) use ($permission) {
            $query->where('permission_id', $permission->id);
        })->count();

        if ($rolesWithPermission > 0) {
            return back()->with('error', 'Cannot delete permission that is assigned to roles.');
        }

        $permissionName = $permission->name;
        
        UserActivity::log(auth()->id(), 'permission_deleted', 'permission', $permission->id, [
            'permission_name' => $permissionName
        ]);

        $permission->delete();

        return redirect()->route('admin.roles.permissions')
            ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Assign permissions to role in bulk.
     */
    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions);

        UserActivity::log(auth()->id(), 'permissions_assigned', 'role', $role->id, [
            'role_name' => $role->name,
            'permissions_count' => count($request->permissions)
        ]);

        return response()->json([
            'message' => 'Permissions assigned successfully.',
            'permissions_count' => $role->permissions()->count()
        ]);
    }

    /**
     * Remove a specific permission from role.
     */
    public function removePermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        UserActivity::log(auth()->id(), 'permission_removed', 'role', $role->id, [
            'role_name' => $role->name,
            'permission_name' => $permission->name
        ]);

        return response()->json(['message' => 'Permission removed successfully.']);
    }

    /**
     * Get users with specific role.
     */
    public function roleUsers(Role $role)
    {
        $users = $role->users()->paginate(15);

        return response()->json([
            'users' => $users,
            'total' => $users->total(),
        ]);
    }

    /**
     * Assign role to user.
     */
    public function assignToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Prevent assigning admin role to non-admin users? (Optional)
        if ($role->name === 'admin' && !auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Only administrators can assign the admin role.'], 403);
        }

        $user->assignRole($role);

        UserActivity::log(auth()->id(), 'role_assigned_to_user', 'user', $user->id, [
            'user_name' => $user->name,
            'role_name' => $role->name
        ]);

        return response()->json(['message' => 'Role assigned successfully.']);
    }

    /**
     * Remove role from user.
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
            return response()->json(['error' => 'You cannot remove your own admin role.'], 403);
        }

        $user->removeRole($role);

        UserActivity::log(auth()->id(), 'role_removed_from_user', 'user', $user->id, [
            'user_name' => $user->name,
            'role_name' => $role->name
        ]);

        return response()->json(['message' => 'Role removed successfully.']);
    }

    /**
     * Get all available permissions grouped by category.
     */
    public function getAllPermissions()
    {
        $permissions = Permission::all()->map(function($permission) {
            $category = explode('-', $permission->name)[0];
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'category' => ucfirst($category),
            ];
        })->groupBy('category');

        return response()->json($permissions);
    }

    /**
     * Create default roles and permissions (for setup).
     */
    public function setupDefaultRoles()
    {
        // Only allow in development or by super admin
        if (!app()->environment('local') && !auth()->user()->hasRole('super-admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permissions = [
            // User management
            'view-users', 'create-users', 'edit-users', 'delete-users', 'manage-users',
            
            // Report management
            'view-reports', 'create-reports', 'edit-reports', 'delete-reports', 
            'manage-reports', 'assign-reports',
            
            // Task management
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'manage-tasks',
            
            // Template management
            'view-templates', 'create-templates', 'edit-templates', 'delete-templates',
            
            // Settings
            'manage-settings', 'view-analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles
        $adminRole = Role::findOrCreate('admin');
        $managerRole = Role::findOrCreate('manager');
        $userRole = Role::findOrCreate('user');

        // Assign permissions
        $adminRole->givePermissionTo(Permission::all());
        
        $managerRole->givePermissionTo([
            'view-users', 'view-reports', 'create-reports', 'edit-reports',
            'assign-reports', 'view-tasks', 'create-tasks', 'edit-tasks',
            'manage-tasks', 'view-templates', 'view-analytics',
        ]);
        
        $userRole->givePermissionTo([
            'view-reports', 'create-reports', 'edit-reports', 'view-tasks', 'view-templates',
        ]);

        UserActivity::log(auth()->id(), 'default_roles_setup', 'system', null, [
            'roles_created' => ['admin', 'manager', 'user'],
            'permissions_created' => count($permissions)
        ]);

        return response()->json([
            'message' => 'Default roles and permissions created successfully.',
            'roles' => ['admin', 'manager', 'user'],
            'permissions_count' => count($permissions)
        ]);
    }

    /**
     * Get role statistics.
     */
    public function getStats()
    {
        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_users_with_roles' => User::role(Role::all())->count(),
            'roles_distribution' => Role::withCount('users')->get()->map(function($role) {
                return [
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                ];
            }),
            'permissions_distribution' => Permission::withCount('roles')->get()->map(function($permission) {
                return [
                    'name' => $permission->name,
                    'roles_count' => $permission->roles_count,
                ];
            })->groupBy(function($item) {
                return explode('-', $item['name'])[0];
            }),
        ];

        return response()->json($stats);
    }
}