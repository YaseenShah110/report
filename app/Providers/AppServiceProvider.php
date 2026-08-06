<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Models\Report;
use App\Models\User;

/**
 * Application Service Provider
 * 
 * Bootstraps application services:
 * - Bootstrap 5 pagination
 * - Force HTTPS in production
 * - Blade directives for permission checks
 * - View composers for shared data
 */
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
        // BLADE DIRECTIVES FOR PERMISSION CHECKS
        // ─────────────────────────────────────────────────────────────

        // Check if user can update a report
        Blade::if('canUpdate', function ($report) {
            if (!auth()->check()) return false;
            $user = auth()->user();
            if ($user->hasRole('admin')) return true;
            if ($user->id === $report->user_id) return true;
            
            $assignment = \App\Models\ReportAssignment::where('report_id', $report->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->first();
                
            return $assignment && in_array($assignment->permission, ['edit', 'manage']);
        });

        // Check if user can delete a report
        Blade::if('canDelete', function ($report) {
            if (!auth()->check()) return false;
            $user = auth()->user();
            if ($user->hasRole('admin')) return true;
            return $user->id === $report->user_id && $report->status !== 'published';
        });

        // Check if user can share a report
        Blade::if('canShare', function ($report) {
            if (!auth()->check()) return false;
            $user = auth()->user();
            if ($user->hasRole('admin')) return true;
            return $user->id === $report->user_id && $report->status === 'published';
        });

        // Check if user can view a report
        Blade::if('canView', function ($report) {
            if (!auth()->check()) return false;
            $user = auth()->user();
            if ($user->hasRole('admin')) return true;
            if ($user->id === $report->user_id) return true;
            
            $hasAccess = \App\Models\ReportAssignment::where('report_id', $report->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->exists();
                
            return $hasAccess || $report->is_public;
        });

        // Admin role checks
        Blade::if('isAdmin', fn() => auth()->check() && auth()->user()->hasRole('admin'));
        Blade::if('isManager', fn() => auth()->check() && auth()->user()->hasRole('manager'));
        Blade::if('canManageReports', fn() => auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager')));
        Blade::if('canManageTasks', fn() => auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager')));
        Blade::if('canManageUsers', fn() => auth()->check() && auth()->user()->hasRole('admin'));
        Blade::if('isPremium', fn() => auth()->check() && auth()->user()->is_premium);

        // ─────────────────────────────────────────────────────────────
        // VIEW COMPOSERS - Share data with all views
        // ─────────────────────────────────────────────────────────────
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Share task counts for sidebar badges
                $view->with('pendingTasksCount', $user->getPendingTasksCount());
                $view->with('overdueTasksCount', $user->getOverdueTasksCount());
                
                // Share assigned reports count
                $assignedReports = \App\Models\ReportAssignment::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })->count();
                $view->with('assignedReportsCount', $assignedReports);
            }
        });

        if (env('BROWSERSHOT_CHROME_PATH')) {
            \Spatie\Browsershot\Browsershot::setChromePath(
                env('BROWSERSHOT_CHROME_PATH')
            );
        }
    }
}