<?php
// app/Http/Middleware/HandleInertiaRequests.php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Task;
use App\Models\ReportAssignment;
use App\Models\Notification;

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
        $notifications = [];
        $unreadNotificationsCount = 0;
        $notificationTypes = [];
        
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
            
            // Get latest notifications for dropdown
            $notifications = Notification::where('user_id', $user->id)
                ->recent()
                ->take(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'icon' => $notification->icon ?? 'fa-solid fa-bell',
                        'color' => $notification->color ?? '#64748b',
                        'action_url' => $notification->action_url,
                        'read_at' => $notification->read_at,
                        'trashed' => $notification->trashed(),
                        'created_at' => $notification->created_at,
                        'time_ago' => $notification->created_at->diffForHumans(),
                    ];
                });
            
            // Get unread notification count
            $unreadNotificationsCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();
            
            // Get notification type counts for filtering
            $notificationTypes = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
            
            // Get specific notification counts for sidebar badges
            $assignedReportsNotifications = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->whereIn('type', ['report_assigned', 'report_shared'])
                ->count();
            
            $pendingTaskNotifications = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->whereIn('type', ['task_created', 'task_updated'])
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
                'is_impersonating' => session()->has('impersonate'),
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
                // New dynamic notification data
                'items' => $notifications,
                'unread_count' => $unreadNotificationsCount,
                'types' => $notificationTypes,
                'assigned_reports_notifications' => $assignedReportsNotifications ?? 0,
                'pending_task_notifications' => $pendingTaskNotifications ?? 0,
            ],
        ];
    }
}