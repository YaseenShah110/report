<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Task;
use App\Models\UserActivity;
use App\Models\ReportAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Recent reports (owned and assigned)
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
                'id' => $report->id,
                'title' => $report->title,
                'slug' => $report->slug,
                'status' => $report->status,
                'updated_at' => $report->updated_at,
                'template' => $report->template ? ['name' => $report->template->name] : null,
            ]);
        
        // Statistics
        $stats = [
            'total_reports' => Report::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('assignments', fn($sq) => $sq->where('user_id', $user->id));
            })->count(),
            
            'published_reports' => Report::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('assignments', fn($sq) => $sq->where('user_id', $user->id));
            })->where('status', 'published')->count(),
            
            'draft_reports' => Report::where('user_id', $user->id)->where('status', 'draft')->count(),
            
            'archived_reports' => Report::where('user_id', $user->id)->where('status', 'archived')->count(),
            
            'assigned_reports' => ReportAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count(),
            
            'pending_tasks' => Task::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            
            'completed_tasks' => Task::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            
            'total_templates' => \App\Models\Template::where('is_active', true)->count(),
        ];
        
        // Recent activities
        $recentActivities = UserActivity::with('user')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($activity) => [
                'action' => $activity->action,
                'entity_type' => $activity->entity_type,
                'details' => $activity->details,
                'created_at' => $activity->created_at,
            ]);
        
        // Chart data for dashboard
        $chartData = [
            'reports_last_30_days' => $this->getReportsChartData($user->id),
            'task_completion_rate' => $this->getTaskCompletionRate($user->id),
        ];
        
        // Notifications
        $notifications = $this->getNotifications($user);
        
        return Inertia::render('Dashboard', [
            'recentReports' => $recentReports,
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'chartData' => $chartData,
            'notifications' => $notifications,
        ]);
    }
    
    public function notifications()
    {
        $user = auth()->user();
        $notifications = $this->getNotifications($user);
        
        return response()->json(['notifications' => $notifications]);
    }
    
    public function markNotificationsRead(Request $request)
    {
        // Implementation for marking notifications as read
        return response()->json(['message' => 'Notifications marked as read']);
    }
    
    public function quickStats()
    {
        $user = auth()->user();
        
        return response()->json([
            'reports_count' => Report::where('user_id', $user->id)->count(),
            'tasks_pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
            'shared_with_me' => ReportAssignment::where('user_id', $user->id)->where('is_active', true)->count(),
        ]);
    }
    
    private function getReportsChartData($userId)
    {
        $dates = [];
        $counts = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');
            $counts[] = Report::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->count();
        }
        
        return ['labels' => $dates, 'values' => $counts];
    }
    
    private function getTaskCompletionRate($userId)
    {
        $total = Task::where('assigned_to', $userId)->count();
        $completed = Task::where('assigned_to', $userId)->where('status', 'completed')->count();
        
        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
    
    private function getNotifications($user)
    {
        $notifications = collect();
        
        // Overdue tasks
        $overdueTasks = Task::where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->count();
        
        if ($overdueTasks > 0) {
            $notifications->push([
                'id' => 'overdue_tasks',
                'type' => 'warning',
                'icon' => 'fa-solid fa-clock',
                'message' => "You have {$overdueTasks} overdue task" . ($overdueTasks > 1 ? 's' : ''),
                'time' => now()->diffForHumans(),
                'read' => false,
            ]);
        }
        
        // New shared reports
        $newShares = ReportAssignment::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        
        if ($newShares > 0) {
            $notifications->push([
                'id' => 'new_shares',
                'type' => 'info',
                'icon' => 'fa-solid fa-share-alt',
                'message' => "{$newShares} new report" . ($newShares > 1 ? 's have' : ' has') . ' been shared with you',
                'time' => 'Today',
                'read' => false,
            ]);
        }
        
        return $notifications;
    }
}