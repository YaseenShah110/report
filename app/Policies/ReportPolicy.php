<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;
use Illuminate\Auth\Access\Response;

/**
 * Report Policy
 * 
 * Defines authorization rules for Report model.
 * Used by Laravel's authorization system.
 */
class ReportPolicy
{
    /**
     * Determine if user can view any reports.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can view a specific report.
     */
    public function view(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can create reports.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can update a report.
     */
    public function update(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can delete a report.
     */
    public function delete(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can restore a soft-deleted report.
     */
    public function restore(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can permanently delete a report.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can publish a report.
     */
    public function publish(User $user, Report $report): bool
    {
        return $user->id === $report->user_id && $report->status === 'draft';
    }

    /**
     * Determine if user can archive a report.
     */
    public function archive(User $user, Report $report): bool
    {
        return $user->id === $report->user_id && $report->status !== 'archived';
    }

    /**
     * Determine if user can duplicate a report.
     */
    public function duplicate(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can export a report.
     */
    public function export(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can share a report.
     */
    public function share(User $user, Report $report): bool
    {
        return $user->id === $report->user_id && $report->status === 'published';
    }

    /**
     * Determine if user can view report versions.
     */
    public function viewVersions(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can restore a report version.
     */
    public function restoreVersion(User $user, Report $report): bool
    {
        return $user->id === $report->user_id || $user->hasRole('admin');
    }
}