<?php
// app/Models/Task.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'title', 'description', 'assigned_by', 'assigned_to', 'report_id',
        'priority', 'status', 'due_date', 'completed_at', 'completion_notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function markAsCompleted($notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $notes
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'medium' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'overdue' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}