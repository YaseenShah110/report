<?php
// app/Http/Controllers/Admin/AnalyticsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Task;
use App\Models\UserActivity;
use App\Models\ReportAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
   

    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        
        // Report statistics
        $reportStats = [
            'total' => Report::count(),
            'published' => Report::where('status', 'published')->count(),
            'draft' => Report::where('status', 'draft')->count(),
            'archived' => Report::where('status', 'archived')->count(),
            'trend' => $this->getReportTrend($startDate),
        ];
        
        // User statistics
        $userStats = [
            'total' => User::count(),
            'new_this_month' => User::whereMonth('created_at', Carbon::now()->month)->count(),
            'active' => User::where('email_verified_at', '!=', null)->count(),
            'premium' => User::where('is_premium', true)->count(),
            'with_reports' => User::has('reports')->count(),
        ];
        
        // Task statistics
        $taskStats = [
            'total' => Task::count(),
            'completed' => Task::where('status', 'completed')->count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'overdue' => Task::where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
            'completion_rate' => $this->calculateCompletionRate(),
        ];
        
        // Activity statistics
        $activityStats = [
            'total' => UserActivity::count(),
            'last_24h' => UserActivity::where('created_at', '>=', Carbon::now()->subDay())->count(),
            'last_7d' => UserActivity::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'most_active_users' => $this->getMostActiveUsers(),
        ];
        
        // Chart data
        $chartData = [
            'reports_created' => $this->getReportsCreatedChart($startDate),
            'user_growth' => $this->getUserGrowthChart($startDate),
            'task_completion' => $this->getTaskCompletionChart($startDate),
            'popular_report_types' => $this->getPopularReportTypes(),
        ];
        
        // Sharing statistics
        $sharingStats = [
            'total_shares' => ReportAssignment::count(),
            'active_shares' => ReportAssignment::where('is_active', true)->count(),
            'expired_shares' => ReportAssignment::where('expires_at', '<', now())->count(),
            'most_shared_reports' => $this->getMostSharedReports(),
        ];
        
        return Inertia::render('Admin/Analytics/Index', [
            'reportStats' => $reportStats,
            'userStats' => $userStats,
            'taskStats' => $taskStats,
            'activityStats' => $activityStats,
            'sharingStats' => $sharingStats,
            'chartData' => $chartData,
            'period' => $period,
        ]);
    }
    
    /**
     * Get detailed reports analytics
     */
    public function reports(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        
        $reports = Report::with('user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(20)
            ->through(fn($report) => [
                'id' => $report->id,
                'title' => $report->title,
                'status' => $report->status,
                'user_name' => $report->user->name,
                'pages' => count($report->content ?? []),
                'shares' => $report->assignments()->count(),
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]);
        
        $summary = [
            'total_pages' => Report::sum(DB::raw('JSON_LENGTH(content)')),
            'avg_pages_per_report' => round(Report::avg(DB::raw('JSON_LENGTH(content)')), 1),
            'total_shares' => ReportAssignment::count(),
            'reports_with_shares' => Report::has('assignments')->count(),
        ];
        
        return Inertia::render('Admin/Analytics/Reports', [
            'reports' => $reports,
            'summary' => $summary,
            'filters' => $request->only(['status', 'search', 'sort', 'direction']),
        ]);
    }
    
    /**
     * Get detailed users analytics
     */
    public function users(Request $request)
    {
        $users = User::withCount(['reports', 'tasksAssigned', 'tasksCreated'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(20)
            ->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'reports_count' => $user->reports_count,
                'tasks_assigned' => $user->tasks_assigned_count,
                'tasks_created' => $user->tasks_created_count,
                'last_activity' => $user->activities()->latest()->first()?->created_at,
                'created_at' => $user->created_at,
            ]);
        
        $summary = [
            'total_users' => User::count(),
            'users_with_reports' => User::has('reports')->count(),
            'users_with_tasks' => User::has('tasksAssigned')->count(),
            'avg_reports_per_user' => round(User::avg(DB::raw('(SELECT COUNT(*) FROM reports WHERE reports.user_id = users.id)')), 1),
        ];
        
        $roles = \Spatie\Permission\Models\Role::all();
        
        return Inertia::render('Admin/Analytics/Users', [
            'users' => $users,
            'summary' => $summary,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role', 'sort', 'direction']),
        ]);
    }
    
    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'reports');
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        
        if ($type === 'reports') {
            return $this->exportReportsData($startDate);
        } elseif ($type === 'users') {
            return $this->exportUsersData($startDate);
        } elseif ($type === 'tasks') {
            return $this->exportTasksData($startDate);
        } elseif ($type === 'activities') {
            return $this->exportActivitiesData($startDate);
        }
        
        return response()->json(['error' => 'Invalid export type'], 422);
    }
    
    /**
     * Get API stats for dashboard widgets
     */
    public function quickStats()
    {
        return response()->json([
            'reports' => [
                'total' => Report::count(),
                'today' => Report::whereDate('created_at', today())->count(),
                'this_week' => Report::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            ],
            'users' => [
                'total' => User::count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'active' => User::whereNotNull('email_verified_at')->count(),
            ],
            'tasks' => [
                'completed' => Task::where('status', 'completed')->whereDate('completed_at', today())->count(),
                'pending' => Task::where('status', 'pending')->count(),
                'overdue' => Task::where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
            ],
        ]);
    }
    
    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPER METHODS
    // ─────────────────────────────────────────────────────────────
    
    private function getReportTrend($startDate)
    {
        $previousPeriod = Carbon::now()->subDays(30);
        $currentCount = Report::where('created_at', '>=', $startDate)->count();
        $previousCount = Report::whereBetween('created_at', [$previousPeriod, $startDate])->count();
        
        if ($previousCount == 0) return 100;
        return round((($currentCount - $previousCount) / $previousCount) * 100, 1);
    }
    
    private function calculateCompletionRate()
    {
        $total = Task::count();
        if ($total == 0) return 0;
        $completed = Task::where('status', 'completed')->count();
        return round(($completed / $total) * 100, 1);
    }
    
    private function getMostActiveUsers()
    {
        return UserActivity::select('user_id', DB::raw('count(*) as activity_count'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($activity) => [
                'user_id' => $activity->user_id,
                'user_name' => $activity->user->name,
                'activity_count' => $activity->activity_count,
            ]);
    }
    
    private function getReportsCreatedChart($startDate)
    {
        $dates = [];
        $counts = [];
        
        for ($i = 0; $i <= 30; $i++) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');
            $counts[] = Report::whereDate('created_at', $date)->count();
        }
        
        return [
            'labels' => array_reverse($dates),
            'values' => array_reverse($counts),
        ];
    }
    
    private function getUserGrowthChart($startDate)
    {
        $dates = [];
        $counts = [];
        
        for ($i = 0; $i <= 30; $i++) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');
            $counts[] = User::whereDate('created_at', $date)->count();
        }
        
        return [
            'labels' => array_reverse($dates),
            'values' => array_reverse($counts),
        ];
    }
    
    private function getTaskCompletionChart($startDate)
    {
        $dates = [];
        $completed = [];
        $created = [];
        
        for ($i = 0; $i <= 30; $i++) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');
            $completed[] = Task::whereDate('completed_at', $date)->count();
            $created[] = Task::whereDate('created_at', $date)->count();
        }
        
        return [
            'labels' => array_reverse($dates),
            'completed' => array_reverse($completed),
            'created' => array_reverse($created),
        ];
    }
    
    private function getPopularReportTypes()
    {
        // This is simplified - you can expand based on your template system
        return [
            'labels' => ['Business', 'Executive', 'Analytics', 'Marketing', 'Financial'],
            'values' => [45, 30, 25, 20, 15],
        ];
    }
    
    private function getMostSharedReports()
    {
        return Report::withCount('assignments')
            ->orderBy('assignments_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($report) => [
                'title' => $report->title,
                'share_count' => $report->assignments_count,
            ]);
    }
    
    private function exportReportsData($startDate)
    {
        $reports = Report::with('user')
            ->where('created_at', '>=', $startDate)
            ->get();
        
        $filename = 'reports_analytics_' . now()->format('Y-m-d') . '.csv';
        $callback = function() use ($reports) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Title', 'Author', 'Status', 'Pages', 'Shares', 'Created At', 'Updated At']);
            
            foreach ($reports as $report) {
                fputcsv($handle, [
                    $report->title,
                    $report->user->name,
                    $report->status,
                    count($report->content ?? []),
                    $report->assignments()->count(),
                    $report->created_at,
                    $report->updated_at,
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    private function exportUsersData($startDate)
    {
        $users = User::withCount(['reports', 'tasksAssigned'])
            ->where('created_at', '>=', $startDate)
            ->get();
        
        $filename = 'users_analytics_' . now()->format('Y-m-d') . '.csv';
        $callback = function() use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Reports Created', 'Tasks Assigned', 'Joined', 'Last Activity']);
            
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->reports_count,
                    $user->tasks_assigned_count,
                    $user->created_at,
                    $user->activities()->latest()->first()?->created_at ?? 'N/A',
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    private function exportTasksData($startDate)
    {
        $tasks = Task::with(['assignedTo', 'assignedBy'])
            ->where('created_at', '>=', $startDate)
            ->get();
        
        $filename = 'tasks_analytics_' . now()->format('Y-m-d') . '.csv';
        $callback = function() use ($tasks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Title', 'Assigned To', 'Assigned By', 'Priority', 'Status', 'Due Date', 'Completed At']);
            
            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->title,
                    $task->assignedTo->name,
                    $task->assignedBy->name,
                    $task->priority,
                    $task->status,
                    $task->due_date ?? 'N/A',
                    $task->completed_at ?? 'N/A',
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    private function exportActivitiesData($startDate)
    {
        $activities = UserActivity::with('user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'activities_analytics_' . now()->format('Y-m-d') . '.csv';
        $callback = function() use ($activities) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['User', 'Action', 'Entity Type', 'Details', 'IP Address', 'Timestamp']);
            
            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->user->name,
                    $activity->action,
                    $activity->entity_type ?? 'N/A',
                    json_encode($activity->details),
                    $activity->ip_address ?? 'N/A',
                    $activity->created_at,
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}