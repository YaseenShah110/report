<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Report Assignment Model
 * 
 * Represents a report shared with a user.
 * Tracks permission level, assignment date, expiry, and active status.
 */
class ReportAssignment extends Model
{
    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'report_id',
        'user_id',
        'assigned_by',
        'permission',
        'expires_at',
        'is_active',
    ];

    /**
     * Type casting for model attributes.
     */
    protected $casts = [
        'expires_at'  => 'datetime',
        'assigned_at' => 'datetime',
        'is_active'   => 'boolean',
    ];

    /**
     * Boot method - auto-sets assigned_at timestamp.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($assignment) {
            if (!$assignment->assigned_at) {
                $assignment->assigned_at = now();
            }
            if (!$assignment->is_active) {
                $assignment->is_active = true;
            }
        });
    }

    /**
     * Report this assignment belongs to.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * User this report is assigned to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who created this assignment.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Check if this assignment allows editing.
     */
    public function canEdit(): bool
    {
        return in_array($this->permission, ['manage', 'edit']);
    }

    /**
     * Check if this assignment allows full management.
     */
    public function canManage(): bool
    {
        return $this->permission === 'manage';
    }

    /**
     * Check if this assignment has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if this assignment is currently valid.
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}