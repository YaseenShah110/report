<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

/**
 * User Management Controller
 *
 * Handles all user CRUD operations for administrators.
 * Supports soft deletes, impersonation, bulk operations, and export.
 *
 * Access: Admin and Manager roles
 */
class UserController extends Controller
{
    /**
     * Display paginated list of users with search and filters.
     * Shows active users by default.
     */
  // ═══════════════════════════════════════════════════════════════════


public function index(Request $request)
{
    $users = User::with('roles')
        ->withCount([
            'reports',           // Total reports created
            'tasksAssigned',     // Total tasks assigned to user
        ])
        ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
            ->orWhere('email', 'like', "%{$request->search}%"))
        ->when($request->role, fn ($q) => $q->role($request->role))
        ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
        ->paginate(12)
        ->withQueryString()
        ->through(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name]),
            'reports_count' => $user->reports_count,
            'tasks_count' => $user->tasks_assigned_count,
            'shared_reports_count' => 0,  // Set to 0 or adjust if you have a specific relationship
            'email_verified_at' => $user->email_verified_at,
            'is_premium' => $user->is_premium,
            'created_at' => $user->created_at,
        ]);

    $roles = Role::all();
    
    // Get all stats including role-based counts
    $stats = [
        'total' => User::count(),
        'active' => User::whereNotNull('email_verified_at')->count(),
        'admin' => User::role('admin')->count(),
        'manager' => User::role('manager')->count(),
        'user' => User::role('user')->count(),
        'premium' => User::where('is_premium', true)->count(),
        'new_today' => User::whereDate('created_at', today())->count(),
        'trashed' => User::onlyTrashed()->count(),
    ];
    
    $trashedUsers = User::onlyTrashed()
        ->select('id', 'name', 'email', 'deleted_at')
        ->orderBy('deleted_at', 'desc')
        ->get();

    return Inertia::render('Admin/Users/Index', [
        'users' => $users,
        'roles' => $roles,
        'stats' => $stats,
        'filters' => $request->only(['search', 'role', 'sort', 'direction']),
        'trashedUsers' => $trashedUsers,
    ]);
}
 
// ═══════════════════════════════════════════════════════════════════
// ADD THIS NEW export() METHOD TO: app/Http/Controllers/Admin/UserController.php
// ═══════════════════════════════════════════════════════════════════
 
public function downloadUsers(Request $request)
{
    $format = $request->get('format', 'csv');
    
    // Get all users with roles
    $users = User::with('roles')->get();
    
    if ($format == 'json') {
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->implode(', '),
                'verified' => $user->email_verified_at ? 'Yes' : 'No',
                'premium' => $user->is_premium ? 'Yes' : 'No',
            ];
        }
        
        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="users-' . date('Y-m-d') . '.json"',
            'Content-Type' => 'application/json;charset=UTF-8'
        ]);
    }
    
    // CSV Format
    $filename = 'users-' . date('Y-m-d') . '.csv';
    $handle = fopen('php://memory', 'r+');
    
    // Headers
    fputcsv($handle, ['ID', 'Name', 'Email', 'Roles', 'Verified', 'Premium']);
    
    // Data
    foreach ($users as $user) {
        fputcsv($handle, [
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $user->email_verified_at ? 'Yes' : 'No',
            $user->is_premium ? 'Yes' : 'No',
        ]);
    }
    
    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);
    
    return response($csv, 200, [
        'Content-Type' => 'text/csv;charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ]);
}


    /**
     * Show create user form with available roles.
     */
    public function create()
    {
        $roles = Role::all();

        return Inertia::render('Admin/Users/Create', ['roles' => $roles]);
    }

    /**
     * Store a newly created user.
     * Auto-verifies email for admin-created users.
     * Assigns selected roles.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_premium' => $request->is_premium ?? false,
            'email_verified_at' => now(), // Auto-verify admin created users
        ]);

        // Assign roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // Log the activity
        UserActivity::log(auth()->id(), 'user_created', 'user', $user->id, [
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display user details with statistics.
     */
    public function show(User $user)
    {
        $user->load(['roles']);

        $stats = [
            'total_reports' => $user->reports()->count(),
            'assigned_reports' => $user->assignedReports()->count(),
            'completed_tasks' => $user->tasksAssigned()->where('status', 'completed')->count(),
            'pending_tasks' => $user->tasksAssigned()->whereIn('status', ['pending', 'in_progress'])->count(),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    /**
     * Show edit user form with current roles and statistics.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name');

        $stats = [
            'total_reports' => $user->reports()->count(),
            'assigned_reports' => $user->assignedReports()->count(),
            'completed_tasks' => $user->tasksAssigned()->where('status', 'completed')->count(),
            'pending_tasks' => $user->tasksAssigned()->whereIn('status', ['pending', 'in_progress'])->count(),
        ];

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
            'stats' => $stats,
        ]);
    }

    /**
     * Update user details.
     * Only updates password if provided.
     * Syncs roles if provided.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$user->id,
        ]);

        // Only update password if a new one is provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->is_premium = $request->is_premium ?? false;
        $user->save();

        // Sync roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // Log the activity
        UserActivity::log(auth()->id(), 'user_updated', 'user', $user->id, [
            'user_name' => $user->name,
            'changes' => $request->only(['name', 'email', 'is_premium']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Soft delete a user (move to trash).
     * Prevents self-deletion and deleting the last admin.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        // Prevent deleting the last admin
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last admin user.');
        }

        UserActivity::log(auth()->id(), 'user_deleted', 'user', $user->id, [
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);

        $user->delete(); // Soft delete

        return redirect()->route('admin.users.index')
            ->with('success', 'User moved to trash.');
    }

    /**
     * Display trashed (soft-deleted) users.
     */
    public function trashed(Request $request)
    {
        $users = User::onlyTrashed()
            ->with('roles')
            ->withCount(['reports', 'tasksAssigned'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('deleted_at', 'desc')
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles,
                'reports_count' => $user->reports_count,
                'tasks_count' => $user->tasks_assigned_count,
                'created_at' => $user->created_at,
                'deleted_at' => $user->deleted_at,
            ]);

        return Inertia::render('Admin/Users/Trashed', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        UserActivity::log(auth()->id(), 'user_restored', 'user', $user->id, [
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User restored successfully.');
    }

    /**
     * Permanently delete a user.
     * Also soft-deletes related reports and tasks.
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        UserActivity::log(auth()->id(), 'user_force_deleted', 'user', $user->id, [
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);

        // Soft delete user's reports and tasks
        $user->reports()->delete();
        $user->tasksAssigned()->delete();

        $user->forceDelete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User permanently deleted.');
    }

    /**
     * Impersonate a user (login as them).
     * Only admin users can impersonate.
     */
    public function impersonate(User $user)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only administrators can impersonate users.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        session(['impersonate' => $user->id]);

        return redirect()->route('dashboard')
            ->with('info', "You are now impersonating {$user->name}");
    }

    /**
     * Stop impersonating and return to original account.
     */
    public function stopImpersonate()
    {
        if (! session()->has('impersonate')) {
            return redirect()->route('dashboard');
        }

        $impersonatedId = session()->pull('impersonate');

        return redirect()->route('dashboard')
            ->with('success', 'Stopped impersonating. Welcome back!');
    }

    /**
     * Get activity logs for a specific user.
     */
    public function userActivities(User $user, Request $request)
    {
        $activities = UserActivity::where('user_id', $user->id)
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn ($activity) => [
                'id' => $activity->id,
                'action' => $activity->action,
                'entity_type' => $activity->entity_type,
                'details' => $activity->details,
                'ip_address' => $activity->ip_address,
                'created_at' => $activity->created_at,
            ]);

        return response()->json(['activities' => $activities]);
    }

    /**
     * Bulk soft delete users.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $deletedCount = 0;
        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                UserActivity::log(auth()->id(), 'user_deleted', 'user', $user->id, [
                    'user_name' => $user->name,
                    'bulk_delete' => true,
                ]);
                $user->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'message' => "{$deletedCount} users deleted successfully",
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request)
    {
        $users = User::with('roles')
            ->when($request->role, fn ($q) => $q->role($request->role))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->get();

        $filename = 'users_export_'.now()->format('Y-m-d_His').'.csv';

        $callback = function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Roles', 'Premium', 'Verified', 'Reports', 'Tasks', 'Joined']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->getRoleNames()->implode(', '),
                    $user->is_premium ? 'Yes' : 'No',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->reports()->count(),
                    $user->tasksAssigned()->count(),
                    $user->created_at->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Search users via AJAX for dropdown/search palette.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json(['users' => $users]);
    }
}