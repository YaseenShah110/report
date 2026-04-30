<?php
// app/Models/User.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'is_premium'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_premium' => 'boolean',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function assignedReports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'report_assignments')
                    ->withPivot('permission', 'assigned_by', 'expires_at', 'is_active')
                    ->withTimestamps();
    }

    public function tasksAssigned(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function tasksCreated(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    public function canAccessReport(Report $report, $permission = 'view'): bool
    {
        if ($this->id === $report->user_id || $this->hasRole('admin')) {
            return true;
        }

        $assignment = $this->assignedReports()
            ->where('report_id', $report->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$assignment) {
            return false;
        }

        if ($permission === 'view') return true;
        if ($permission === 'edit') return in_array($assignment->pivot->permission, ['manage', 'edit']);
        if ($permission === 'manage') return $assignment->pivot->permission === 'manage';

        return false;
    }

    public function getPendingTasksCount(): int
    {
        return $this->tasksAssigned()
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function($q) {
                $q->whereNull('due_date')->orWhere('due_date', '>=', now());
            })
            ->count();
    }

    public function getOverdueTasksCount(): int
    {
        return $this->tasksAssigned()
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->count();
    }
}