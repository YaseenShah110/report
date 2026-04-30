<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Report;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Report::class => ReportPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super admin gate - allows admin to do everything
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        // Impersonation gate
        Gate::define('impersonate', function (User $user, User $target) {
            return $user->hasRole('admin') && $user->id !== $target->id;
        });

        // Stop impersonation gate
        Gate::define('stopImpersonate', function (User $user) {
            return session()->has('impersonate');
        });

        // Report access gates
        Gate::define('view-report', function (User $user, Report $report) {
            return $user->canAccessReport($report, 'view');
        });

        Gate::define('edit-report', function (User $user, Report $report) {
            return $user->canAccessReport($report, 'edit');
        });

        Gate::define('manage-report', function (User $user, Report $report) {
            return $user->canAccessReport($report, 'manage');
        });
    }
}