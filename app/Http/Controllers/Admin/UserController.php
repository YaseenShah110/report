<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Report;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
   

    public function index(Request $request)
    {
        
        
        $users = User::with('roles')
            ->withCount(['reports', 'tasksAssigned'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(12)
            ->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles,
                'reports_count' => $user->reports_count,
                'tasks_count' => $user->tasks_assigned_count,
                'email_verified_at' => $user->email_verified_at,
                'is_premium' => $user->is_premium,
                'created_at' => $user->created_at,
            ]);

        $roles = Role::all();
        $stats = [
            'total' => User::count(),
            'active' => User::whereNotNull('email_verified_at')->count(),
            'premium' => User::where('is_premium', true)->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'sort', 'direction'])
        ]);
    }

    public function create()
    {
        
        
        $roles = Role::all();
        return Inertia::render('Admin/Users/Create', ['roles' => $roles]);
    }

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
        ]);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        UserActivity::log(auth()->id(), 'user_created', 'user', $user->id, [
            'user_name' => $user->name,
            'user_email' => $user->email
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

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
            'stats' => $stats
        ]);
    }

    public function update(Request $request, User $user)
    {
        
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$user->id,
        ]);

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

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        UserActivity::log(auth()->id(), 'user_updated', 'user', $user->id, [
            'user_name' => $user->name,
            'changes' => $request->only(['name', 'email', 'is_premium'])
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
       
        
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        UserActivity::log(auth()->id(), 'user_deleted', 'user', $user->id, [
            'user_name' => $user->name,
            'user_email' => $user->email
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function impersonate(User $user)
    {
        
        
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }
        
        session(['impersonate' => $user->id]);
        return redirect()->route('dashboard');
    }

    public function stopImpersonate()
    {
    
        
        session()->forget('impersonate');
        return redirect()->route('dashboard');
    }
}