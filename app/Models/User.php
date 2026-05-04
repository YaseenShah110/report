<?php

namespace App\Models;

// Import required traits and classes
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;           // For trash/recover functionality
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;                   // Role-based access control
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    // Use multiple traits to add functionality:
    // - HasFactory: For generating test users in factories
    // - Notifiable: For sending notifications (email, database, etc.)
    // - HasRoles: Spatie permission package for RBAC
    // - SoftDeletes: Instead of permanently deleting, mark as deleted_at
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * Fields that can be mass-assigned via User::create() or $user->update()
     * Protects against mass-assignment vulnerabilities by only allowing these fields
     */
    protected $fillable = [
        'name',              // User's full name
        'email',             // User's email (used for login)
        'password',          // Hashed password (never stored in plain text)
        'is_admin',          // Legacy admin flag (use roles instead in most cases)
        'is_premium',        // Premium user flag for premium features
        'email_verified_at', // When the user verified their email
    ];

    /**
     * Fields that should be hidden from array/JSON output
     * Never expose passwords or remember tokens in API responses
     */
    protected $hidden = [
        'password',          // Hashed password - NEVER expose
        'remember_token',    // Token for "remember me" functionality
    ];

    /**
     * Type casting for model attributes
     * Ensures proper data types when accessing these fields
     * Laravel 11 uses this method instead of $casts property
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',  // Cast to Carbon datetime object
            'password'          => 'hashed',    // Automatically hash when setting
            'is_admin'          => 'boolean',   // Cast 0/1 to true/false
            'is_premium'        => 'boolean',   // Cast 0/1 to true/false
            'deleted_at'        => 'datetime',  // Soft delete timestamp
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    | Defines how User relates to other models in the database
    */

    /**
     * Reports owned/created by this user
     * One user can have many reports
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Reports assigned to this user (for collaboration)
     * Many-to-many relationship through report_assignments table
     * Includes pivot data: permission level, who assigned it, expiry
     */
    public function assignedReports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'report_assignments')
                    ->withPivot('permission', 'assigned_by', 'expires_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Tasks assigned TO this user
     * These are tasks the user needs to complete
     */
    public function tasksAssigned(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Tasks created BY this user (assigned to others)
     * These are tasks the user has delegated
     */
    public function tasksCreated(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    /**
     * Activity logs for this user
     * Tracks all actions performed by the user
     */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Notifications for this user
     * Used by the notification system
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /*
    |--------------------------------------------------------------------------
    | PERMISSION CHECK METHODS
    |--------------------------------------------------------------------------
    | Helper methods to check if a user can access a specific report
    */

    /**
     * Check if user can access a report with specific permission level
     * 
     * @param Report $report - The report to check access for
     * @param string $permission - Required permission: 'view', 'edit', 'manage'
     * @return bool - True if user has the required access
     * 
     * Access hierarchy:
     * 1. Admin users can access everything
     * 2. Report owner has full access
     * 3. Assigned users have access based on their permission level
     */
    public function canAccessReport(Report $report, $permission = 'view'): bool
    {
        // Admin users bypass all permission checks
        if ($this->hasRole('admin')) {
            return true;
        }

        // Report owner has full access
        if ($this->id === $report->user_id) {
            return true;
        }

        // Check if user has an active assignment for this report
        $assignment = $this->assignedReports()
            ->where('report_id', $report->id)
            ->where('is_active', true)
            ->where(function($q) {
                // Assignment must not be expired
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        // No assignment found
        if (!$assignment) {
            return false;
        }

        // Check permission level
        // 'view' - any assignment grants view access
        // 'edit' - only 'edit' or 'manage' assignments
        // 'manage' - only 'manage' assignments
        if ($permission === 'view') return true;
        if ($permission === 'edit') return in_array($assignment->pivot->permission, ['manage', 'edit']);
        if ($permission === 'manage') return $assignment->pivot->permission === 'manage';

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD HELPER METHODS
    |--------------------------------------------------------------------------
    | Used by the dashboard to show task counts
    */

    /**
     * Get count of pending tasks for this user
     * Pending tasks are those with status 'pending' or 'in_progress'
     * and either no due date or due date in the future
     */
    public function getPendingTasksCount(): int
    {
        return $this->tasksAssigned()
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function($q) {
                $q->whereNull('due_date')->orWhere('due_date', '>=', now());
            })
            ->count();
    }

    /**
     * Get count of overdue tasks for this user
     * Overdue tasks are those not completed with due date in the past
     */
    public function getOverdueTasksCount(): int
    {
        return $this->tasksAssigned()
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->count();
    }
}