<?php
// app/Policies/ReportPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    /**
     * Determine if the user can view any reports.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their reports list
        return true;
    }

    /**
     * Determine if the user can view the report.
     */
    public function view(User $user, Report $report): bool
    {
        // User can view their own reports OR admin can view all
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can create reports.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create reports
        return true;
    }

    /**
     * Determine if the user can update the report.
     */
    public function update(User $user, Report $report): bool
    {
        // User can update their own reports OR admin can update all
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can delete the report.
     */
    public function delete(User $user, Report $report): bool
    {
        // User can delete their own reports OR admin can delete all
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can restore the report.
     */
    public function restore(User $user, Report $report): bool
    {
        // For soft deletes (if you implement them)
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can permanently delete the report.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        // For permanent deletion
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can publish the report.
     */
    public function publish(User $user, Report $report): bool
    {
        // User can publish their own draft reports
        return $user->id === $report->user_id && $report->status === 'draft';
    }

    /**
     * Determine if the user can archive the report.
     */
    public function archive(User $user, Report $report): bool
    {
        // User can archive their own reports (except archived ones)
        return $user->id === $report->user_id && $report->status !== 'archived';
    }

    /**
     * Determine if the user can duplicate the report.
     */
    public function duplicate(User $user, Report $report): bool
    {
        // User can duplicate their own reports OR admin can duplicate all
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can export the report.
     */
    public function export(User $user, Report $report): bool
    {
        // User can export their own reports OR admin can export all
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can share the report.
     */
    public function share(User $user, Report $report): bool
    {
        // User can share their own published reports
        return $user->id === $report->user_id && $report->status === 'published';
    }

    /**
     * Determine if the user can view report versions.
     */
    public function viewVersions(User $user, Report $report): bool
    {
        // User can view versions of their own reports OR admin can view all
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if the user can restore a version.
     */
    public function restoreVersion(User $user, Report $report): bool
    {
        // User can restore versions of their own reports OR admin can restore all
        return $user->id === $report->user_id || $user->hasRole('admin');
    }
}