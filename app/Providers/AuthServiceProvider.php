<?php

namespace App\Providers;

use App\Models\Report;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

/**
 * Authentication Service Provider
 * 
 * Registers policies and defines gates for authorization.
 * Includes super-admin bypass, impersonation gates, and report access gates.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings.
     */
    protected $policies = [
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super admin gate - admin can do everything
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        // Impersonation gate - only admin can impersonate
        Gate::define('impersonate', function (User $user, User $target) {
            return $user->hasRole('admin') && $user->id !== $target->id;
        });

        // Stop impersonation gate
        Gate::define('stopImpersonate', function (User $user) {
            return session()->has('impersonate');
        });

        // Report access gates (view, edit, manage)
        Gate::define('view-report', function (User $user, Report $report) {
            return $user->canAccessReport($report, 'view');
        });

        Gate::define('edit-report', function (User $user, Report $report) {
            return $user->canAccessReport($report, 'edit');
        });

        Gate::define('manage-report', function (User $user, Report $report) {
            return $user->canAccessReport($report, 'manage');
        });

        // Template management gate (admin only)
        Gate::define('manage-templates', function (User $user) {
            return $user->hasRole('admin');
        });
    }
}