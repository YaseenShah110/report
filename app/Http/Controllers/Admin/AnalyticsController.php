<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Task;
use App\Models\UserActivity;
use App\Models\ReportAssignment;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Analytics Controller
 * 
 * Provides system-wide analytics and statistics.
 * Includes report stats, user stats, task stats, activity stats,
 * sharing stats, and chart data for the analytics dashboard.
 * 
 * Access: Admin and Manager roles
 */
class AnalyticsController extends Controller
{
    /**
     * Display the main analytics dashboard with all statistics.
     * Supports period filtering (7, 30, 90, 365 days).
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays((int)$period);
        
        // Report Statistics
        $reportStats = [
            'total'     => Report::count(),
            'published' => Report::where('status', 'published')->count(),
            'draft'     => Report::where('status', 'draft')->count(),
            'archived'  => Report::where('status', 'archived')->count(),
            'trashed'   => Report::onlyTrashed()->count(),
            'trend'     => $this->getReportTrend($startDate),
        ];
        
        // User Statistics
        $userStats = [
            'total'          => User::count(),
            'new_this_month' => User::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)->count(),
            'active'         => User::whereNotNull('email_verified_at')->count(),
            'premium'        => User::where('is_premium', true)->count(),
            'with_reports'   => User::has('reports')->count(),
            'trashed'        => User::onlyTrashed()->count(),
        ];
        
        // Task Statistics
        $taskStats = [
            'total'           => Task::count(),
            'completed'       => Task::where('status', 'completed')->count(),
            'pending'         => Task::where('status', 'pending')->count(),
            'in_progress'     => Task::where('status', 'in_progress')->count(),
            'overdue'         => Task::where('status', '!=', 'completed')
                ->where('due_date', '<', now())->count(),
            'trashed'         => Task::onlyTrashed()->count(),
            'completion_rate' => $this->calculateCompletionRate(),
        ];
        
        // Activity Statistics
        $activityStats = [
            'total'             => UserActivity::count(),
            'last_24h'          => UserActivity::where('created_at', '>=', Carbon::now()->subDay())->count(),
            'last_7d'           => UserActivity::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'most_active_users' => $this->getMostActiveUsers(),
        ];
        
        // Chart Data for JavaScript charts
        $chartData = [
            'reports_created'       => $this->getReportsCreatedChart($startDate),
            'user_growth'           => $this->getUserGrowthChart($startDate),
            'task_completion'       => $this->getTaskCompletionChart($startDate),
            'popular_report_types'  => $this->getPopularReportTypes(),
        ];
        
        // Sharing Statistics
        $sharingStats = [
            'total_shares'          => ReportAssignment::count(),
            'active_shares'         => ReportAssignment::where('is_active', true)->count(),
            'expired_shares'        => ReportAssignment::where('expires_at', '<', now())->count(),
            'most_shared_reports'   => $this->getMostSharedReports(),
        ];
        
        return Inertia::render('Admin/Analytics/Index', [
            'reportStats'   => $reportStats,
            'userStats'     => $userStats,
            'taskStats'     => $taskStats,
            'activityStats' => $activityStats,
            'sharingStats'  => $sharingStats,
            'chartData'     => $chartData,
            'period'        => (int)$period,
        ]);
    }
    
    /**
     * Display detailed reports analytics with pagination and search.
     */
    public function reports(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays((int)$period);
        
        $reports = Report::with('user')
            ->withCount('assignments')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($report) => [
                'id'         => $report->id,
                'title'      => $report->title,
                'slug'       => $report->slug,
                'status'     => $report->status,
                'user_name'  => $report->user->name ?? 'Unknown',
                'pages'      => count($report->content ?? []),
                'shares'     => $report->assignments_count,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]);
        
        // Summary statistics
        $summary = [
            'total_pages'          => Report::sum(DB::raw('JSON_LENGTH(content)')),
            'avg_pages_per_report'  => round(Report::avg(DB::raw('JSON_LENGTH(content)')) ?? 0, 1),
            'total_shares'          => ReportAssignment::count(),
            'reports_with_shares'   => Report::has('assignments')->count(),
        ];
        
        return Inertia::render('Admin/Analytics/Reports', [
            'reports'  => $reports,
            'summary'  => $summary,
            'filters'  => $request->only(['status', 'search', 'sort', 'direction']),
        ]);
    }
    
    /**
     * Display detailed users analytics with pagination and search.
     */
    public function users(Request $request)
    {
        $users = User::withCount(['reports', 'tasksAssigned'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($user) => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'reports_count'   => $user->reports_count,
                'tasks_assigned'  => $user->tasks_assigned_count,
                'last_activity'   => $user->activities()->latest()->first()?->created_at,
                'created_at'      => $user->created_at,
            ]);
        
        $summary = [
            'total_users'           => User::count(),
            'users_with_reports'    => User::has('reports')->count(),
            'users_with_tasks'      => User::has('tasksAssigned')->count(),
            'avg_reports_per_user'  => round(User::has('reports')->count() > 0 
                ? Report::count() / max(User::has('reports')->count(), 1) : 0, 1),
        ];
        
        $roles = \Spatie\Permission\Models\Role::all();
        
        return Inertia::render('Admin/Analytics/Users', [
            'users'    => $users,
            'summary'  => $summary,
            'roles'    => $roles,
            'filters'  => $request->only(['search', 'role', 'sort', 'direction']),
        ]);
    }
    
    /**
     * Export analytics data as CSV.
     * Supports types: reports, users, tasks, activities
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'reports');
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays((int)$period);
        
        return match($type) {
            'reports'    => $this->exportReportsData($startDate),
            'users'      => $this->exportUsersData($startDate),
            'tasks'      => $this->exportTasksData($startDate),
            'activities' => $this->exportActivitiesData($startDate),
            default      => response()->json(['error' => 'Invalid export type'], 422),
        };
    }
    
    /**
     * Quick stats API endpoint for dashboard widgets.
     */
    public function quickStats()
    {
        return response()->json([
            'reports' => [
                'total'     => Report::count(),
                'today'     => Report::whereDate('created_at', today())->count(),
                'this_week' => Report::whereBetween('created_at', 
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            ],
            'users' => [
                'total'     => User::count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'active'    => User::whereNotNull('email_verified_at')->count(),
            ],
            'tasks' => [
                'completed' => Task::where('status', 'completed')
                    ->whereDate('completed_at', today())->count(),
                'pending'   => Task::where('status', 'pending')->count(),
                'overdue'   => Task::where('status', '!=', 'completed')
                    ->where('due_date', '<', now())->count(),
            ],
        ]);
    }
    
    /**
     * Get task statistics API endpoint.
     */
    public function taskStats()
    {
        return response()->json([
            'total'       => Task::count(),
            'completed'   => Task::where('status', 'completed')->count(),
            'pending'     => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'overdue'     => Task::where('status', '!=', 'completed')
                ->where('due_date', '<', now())->count(),
        ]);
    }
    
    /**
     * Get report statistics API endpoint.
     */
    public function reportStats()
    {
        return response()->json([
            'total'     => Report::count(),
            'published' => Report::where('status', 'published')->count(),
            'draft'     => Report::where('status', 'draft')->count(),
            'archived'  => Report::where('status', 'archived')->count(),
            'trashed'   => Report::onlyTrashed()->count(),
        ]);
    }
    
    /**
     * Get user statistics API endpoint.
     */
    public function userStats()
    {
        return response()->json([
            'total'     => User::count(),
            'active'    => User::whereNotNull('email_verified_at')->count(),
            'premium'   => User::where('is_premium', true)->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
        ]);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPER METHODS
    // ═══════════════════════════════════════════════════════════════
    
    /**
     * Calculate report creation trend percentage.
     */
    private function getReportTrend($startDate): float
    {
        $previousPeriod = Carbon::now()->subDays(60);
        $currentCount = Report::where('created_at', '>=', $startDate)->count();
        $previousCount = Report::whereBetween('created_at', [$previousPeriod, $startDate])->count();
        
        if ($previousCount == 0) return 100;
        return round((($currentCount - $previousCount) / $previousCount) * 100, 1);
    }
    
    /**
     * Calculate overall task completion rate.
     */
    private function calculateCompletionRate(): float
    {
        $total = Task::count();
        if ($total == 0) return 0;
        return round((Task::where('status', 'completed')->count() / $total) * 100, 1);
    }
    
    /**
     * Get most active users by activity count.
     */
    private function getMostActiveUsers(): array
    {
        return UserActivity::select('user_id', DB::raw('count(*) as activity_count'))
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($activity) => [
                'user_id'        => $activity->user_id,
                'user_name'      => $activity->user->name ?? 'Unknown',
                'activity_count' => $activity->activity_count,
            ])
            ->toArray();
    }
    
    /**
     * Generate reports created chart data.
     */
    private function getReportsCreatedChart($startDate): array
    {
        $dates = [];
        $counts = [];
        
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');
            $counts[] = Report::whereDate('created_at', $date)->count();
        }
        
        return [
            'labels' => $dates,
            'values' => $counts,
        ];
    }
    
    /**
     * Generate user growth chart data.
     */
    private function getUserGrowthChart($startDate): array
    {
        $dates = [];
        $counts = [];
        
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');
            $counts[] = User::whereDate('created_at', $date)->count();
        }
        
        return [
            'labels' => $dates,
            'values' => $counts,
        ];
    }
    
    /**
     * Generate task completion chart data.
     */
    private function getTaskCompletionChart($startDate): array
    {
        $dates = [];
        $created = [];
        $completed = [];
        
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');
            $created[] = Task::whereDate('created_at', $date)->count();
            $completed[] = Task::whereDate('completed_at', $date)->count();
        }
        
        return [
            'labels'    => $dates,
            'created'   => $created,
            'completed' => $completed,
        ];
    }
    
    /**
     * Get popular report types (template usage).
     */
    private function getPopularReportTypes(): array
    {
        $templateData = Template::withCount('reports')
            ->orderBy('reports_count', 'desc')
            ->take(6)
            ->get();
        
        if ($templateData->isEmpty()) {
            return [
                'labels' => ['Business', 'Executive', 'Analytics', 'Marketing', 'Financial', 'Sales'],
                'values' => [0, 0, 0, 0, 0, 0],
            ];
        }
        
        return [
            'labels' => $templateData->pluck('name')->toArray(),
            'values' => $templateData->pluck('reports_count')->toArray(),
        ];
    }
    
    /**
     * Get most shared reports.
     */
    private function getMostSharedReports(): array
    {
        return Report::withCount('assignments')
            ->orderBy('assignments_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($report) => [
                'title'       => $report->title,
                'share_count' => $report->assignments_count,
            ])
            ->toArray();
    }
    
    /**
     * Export reports analytics as CSV.
     */
    private function exportReportsData($startDate)
    {
        $reports = Report::with('user')
            ->where('created_at', '>=', $startDate)
            ->get();
        
        $filename = 'reports_analytics_' . now()->format('Y-m-d') . '.csv';
        
        $callback = function() use ($reports) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Title', 'Author', 'Status', 'Pages', 'Shares', 'Created At']);
            
            foreach ($reports as $report) {
                fputcsv($handle, [
                    $report->title,
                    $report->user->name ?? 'N/A',
                    $report->status,
                    count($report->content ?? []),
                    $report->assignments()->count(),
                    $report->created_at->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Export users analytics as CSV.
     */
    private function exportUsersData($startDate)
    {
        $users = User::withCount(['reports', 'tasksAssigned'])
            ->where('created_at', '>=', $startDate)
            ->get();
        
        $filename = 'users_analytics_' . now()->format('Y-m-d') . '.csv';
        
        $callback = function() use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Reports', 'Tasks', 'Joined']);
            
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->reports_count,
                    $user->tasks_assigned_count,
                    $user->created_at->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Export tasks analytics as CSV.
     */
    private function exportTasksData($startDate)
    {
        $tasks = Task::with(['assignedTo', 'assignedBy'])
            ->where('created_at', '>=', $startDate)
            ->get();
        
        $filename = 'tasks_analytics_' . now()->format('Y-m-d') . '.csv';
        
        $callback = function() use ($tasks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Title', 'Assigned To', 'Priority', 'Status', 'Due Date', 'Completed']);
            
            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->title,
                    $task->assignedTo->name ?? 'N/A',
                    $task->priority,
                    $task->status,
                    $task->due_date?->format('Y-m-d') ?? 'N/A',
                    $task->completed_at?->format('Y-m-d') ?? 'N/A',
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Export activities analytics as CSV.
     */
    private function exportActivitiesData($startDate)
    {
        $activities = UserActivity::with('user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'activities_analytics_' . now()->format('Y-m-d') . '.csv';
        
        $callback = function() use ($activities) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['User', 'Action', 'Entity', 'Details', 'Timestamp']);
            
            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->user->name ?? 'System',
                    $activity->action,
                    $activity->entity_type ?? 'N/A',
                    json_encode($activity->details),
                    $activity->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}