<?php
// app/Models/ReportAssignment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAssignment extends Model
{
    protected $fillable = [
        'report_id', 'user_id', 'assigned_by', 'permission', 'expires_at', 'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function canEdit(): bool
    {
        return in_array($this->permission, ['manage', 'edit']);
    }

    public function canManage(): bool
    {
        return $this->permission === 'manage';
    }
}