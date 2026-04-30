<?php
// app/Http/Middleware/HandleInertiaRequests.php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Task;
use App\Models\ReportAssignment;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Get user-specific data (only if authenticated)
        $pendingTasks = null;
        $overdueTasks = null;
        $assignedReportsCount = null;
        
        if ($user) {
            // Count pending tasks for notification badge
            $pendingTasks = Task::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
            
            // Count overdue tasks
            $overdueTasks = Task::where('assigned_to', $user->id)
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count();
            
            // Count assigned reports
            $assignedReportsCount = ReportAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count();
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_premium' => $user->is_premium,
                    'roles' => $user->getRoleNames(),
                ] : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'warning' => session('warning'),
                'info' => session('info'),
            ],
            'app' => [
                'name' => config('app.name'),
                'environment' => config('app.env'),
                'url' => config('app.url'),
                'version' => '1.0.0',
            ],
            'notifications' => [
                'pending_tasks' => $pendingTasks,
                'overdue_tasks' => $overdueTasks,
                'assigned_reports' => $assignedReportsCount,
            ],
        ];
    }
}