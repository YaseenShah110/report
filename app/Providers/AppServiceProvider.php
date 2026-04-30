<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Models\Report;
use App\Models\User;
use App\Models\Task;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 for pagination styling
        Paginator::useBootstrapFive();
        
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // ─────────────────────────────────────────────────────────────
        //  BLADE DIRECTIVES FOR PERMISSION CHECKS
        // ─────────────────────────────────────────────────────────────

        // Check if user can update a report
        Blade::if('canUpdate', function ($report) {
            if (!auth()->check()) return false;
            
            $user = auth()->user();
            
            // Admin can update anything
            if ($user->hasRole('admin')) return true;
            
            // Owner can update
            if ($user->id === $report->user_id) return true;
            
            // Check assignment permissions
            $assignment = \App\Models\ReportAssignment::where('report_id', $report->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();
                
            return $assignment && in_array($assignment->permission, ['edit', 'manage']);
        });

        // Check if user can delete a report
        Blade::if('canDelete', function ($report) {
            if (!auth()->check()) return false;
            
            $user = auth()->user();
            
            // Admin can delete anything
            if ($user->hasRole('admin')) return true;
            
            // Only owner can delete
            return $user->id === $report->user_id && $report->status !== 'published';
        });

        // Check if user can share a report
        Blade::if('canShare', function ($report) {
            if (!auth()->check()) return false;
            
            $user = auth()->user();
            
            // Admin can share anything
            if ($user->hasRole('admin')) return true;
            
            // Owner can share published reports
            return $user->id === $report->user_id && $report->status === 'published';
        });

        // Check if user can view a report
        Blade::if('canView', function ($report) {
            if (!auth()->check()) return false;
            
            $user = auth()->user();
            
            // Admin can view anything
            if ($user->hasRole('admin')) return true;
            
            // Owner can view
            if ($user->id === $report->user_id) return true;
            
            // Check if user has assignment
            $hasAccess = \App\Models\ReportAssignment::where('report_id', $report->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();
                
            // Check if report is publicly shared
            return $hasAccess || $report->is_public;
        });

        // Check if user can manage reports (assign to others)
        Blade::if('canManageReports', function () {
            if (!auth()->check()) return false;
            
            $user = auth()->user();
            return $user->hasRole('admin') || $user->hasRole('manager');
        });

        // Check if user can manage tasks
        Blade::if('canManageTasks', function () {
            if (!auth()->check()) return false;
            
            $user = auth()->user();
            return $user->hasRole('admin') || $user->hasRole('manager');
        });

        // Check if user can manage users
        Blade::if('canManageUsers', function () {
            if (!auth()->check()) return false;
            
            $user = auth()->user();
            return $user->hasRole('admin');
        });

        // Check if user is admin
        Blade::if('isAdmin', function () {
            if (!auth()->check()) return false;
            return auth()->user()->hasRole('admin');
        });

        // Check if user is manager
        Blade::if('isManager', function () {
            if (!auth()->check()) return false;
            return auth()->user()->hasRole('manager');
        });

        // Check if user is premium
        Blade::if('isPremium', function () {
            if (!auth()->check()) return false;
            return auth()->user()->is_premium;
        });

        // ─────────────────────────────────────────────────────────────
        //  VIEW COMPOSERS (Share data with all views)
        // ─────────────────────────────────────────────────────────────
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Share task counts
                $view->with('pendingTasksCount', $user->getPendingTasksCount());
                $view->with('overdueTasksCount', $user->getOverdueTasksCount());
                
                // Share assigned reports count
                $assignedReports = \App\Models\ReportAssignment::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->count();
                $view->with('assignedReportsCount', $assignedReports);
            }
        });
    }
}