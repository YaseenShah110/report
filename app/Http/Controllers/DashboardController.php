<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\ReportAssignment;
use App\Models\Template;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

/**
 * Dashboard Controller
 * 
 * Displays the main dashboard with:
 * - Recent reports and activities
 * - Statistics (reports, tasks, templates)
 * - Chart data (report activity, user growth, task completion, popular types)
 * 
 * Access: All authenticated users
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard with all widgets and charts.
     */
    public function index()
    {
        $user = auth()->user();
        
        // ── Recent Reports (owned + assigned) ────────────────────
        $recentReports = Report::with('template')
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('assignments', function($sq) use ($user) {
                      $sq->where('user_id', $user->id)->where('is_active', true);
                  });
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($report) => [
                'id'          => $report->id,
                'title'       => $report->title,
                'slug'        => $report->slug,
                'status'      => $report->status,
                'updated_at'  => $report->updated_at,
                'total_pages' => count($report->content ?? []),
                'template'    => $report->template ? ['name' => $report->template->name] : null,
            ]);
        
        // ── Statistics ───────────────────────────────────────────
        $stats = $this->getStats($user);
        
        // ── Recent Activities ────────────────────────────────────
        $recentActivities = UserActivity::with('user')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($activity) => [
                'id'          => $activity->id,
                'action'      => $activity->action,
                'entity_type' => $activity->entity_type,
                'details'     => $activity->details,
                'created_at'  => $activity->created_at,
            ]);
        
        // ── Chart Data ───────────────────────────────────────────
        $chartData = [
            'reports_last_30_days'  => $this->getReportsChartData($user->id),
            'task_completion_rate'  => $this->getTaskCompletionRate($user->id),
            'user_growth'           => $this->getUserGrowthChart(),
            'popular_report_types'  => $this->getPopularReportTypes(),
        ];
        
        return Inertia::render('Dashboard', [
            'recentReports'    => $recentReports,
            'stats'            => $stats,
            'recentActivities' => $recentActivities,
            'chartData'        => $chartData,
        ]);
    }
    
    /**
     * Get latest notifications for the dropdown (API endpoint).
     */
    public function notifications()
    {
        $user = auth()->user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->recent()
            ->take(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'type'       => $notification->type,
                    'title'      => $notification->title,
                    'message'    => $notification->message,
                    'icon'       => $notification->icon ?? 'fa-solid fa-bell',
                    'color'      => $notification->color ?? '#64748b',
                    'action_url' => $notification->action_url,
                    'read_at'    => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'time_ago'   => $notification->created_at->diffForHumans(),
                ];
            });
        
        $unreadCount = Notification::where('user_id', $user->id)
            ->unread()
            ->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }
    
    /**
     * Mark all notifications as read for current user.
     */
    public function markNotificationsRead(Request $request)
    {
        Notification::markAllAsReadForUser(auth()->id());
        
        return response()->json([
            'message'      => 'All notifications marked as read',
            'unread_count' => 0,
        ]);
    }
    
    /**
     * Quick stats for dashboard widgets (API endpoint).
     */
    public function quickStats()
    {
        $user = auth()->user();
        
        return response()->json([
            'reports_count'  => Report::where('user_id', $user->id)->count(),
            'tasks_pending'  => Task::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'shared_with_me' => ReportAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->count(),
        ]);
    }
    
    /**
     * Calculate comprehensive statistics for the current user.
     */
    private function getStats($user): array
    {
        $reportQuery = Report::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('assignments', fn($sq) => $sq->where('user_id', $user->id)
                  ->where('is_active', true)
                  ->where(fn($eq) => $eq->whereNull('expires_at')->orWhere('expires_at', '>', now())));
        });
        
        return [
            'total_reports'     => (clone $reportQuery)->count(),
            'published_reports' => (clone $reportQuery)->where('status', 'published')->count(),
            'draft_reports'     => Report::where('user_id', $user->id)->where('status', 'draft')->count(),
            'archived_reports'  => Report::where('user_id', $user->id)->where('status', 'archived')->count(),
            'assigned_reports'  => ReportAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count(),
            'pending_tasks'     => Task::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'completed_tasks'   => Task::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'total_templates'   => Template::where('is_active', true)->count(),
        ];
    }
    
    /**
     * Generate report creation chart data for last 30 days.
     */
    private function getReportsChartData($userId): array
    {
        $labels = [];
        $values = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date    = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            $values[] = Report::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->count();
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    /**
     * Calculate task completion rate for current user.
     */
    private function getTaskCompletionRate($userId): float
    {
        $total     = Task::where('assigned_to', $userId)->count();
        $completed = Task::where('assigned_to', $userId)->where('status', 'completed')->count();
        
        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
    
    /**
     * Generate user growth chart data for last 6 months.
     */
    private function getUserGrowthChart(): array
    {
        $labels = [];
        $values = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date    = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $values[] = User::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    /**
     * Get popular report types (template usage counts).
     */
    private function getPopularReportTypes(): array
    {
        $templateCounts = Template::withCount('reports')
            ->orderBy('reports_count', 'desc')
            ->take(5)
            ->get();
        
        if ($templateCounts->isEmpty()) {
            return [
                'labels' => ['Business', 'Executive', 'Analytics', 'Marketing', 'Financial'],
                'values' => [0, 0, 0, 0, 0],
            ];
        }
        
        return [
            'labels' => $templateCounts->pluck('name')->toArray(),
            'values' => $templateCounts->pluck('reports_count')->toArray(),
        ];
    }
}