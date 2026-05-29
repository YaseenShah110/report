<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{ 
    // HasFactory: For generating test tasks in factories
    // SoftDeletes: Instead of permanently deleting, marks as deleted_at
    use HasFactory, SoftDeletes;
    
    /**
     * Mass-assignable fields
     */
    protected $fillable = [
        'title',             // Task title (required)
        'description',       // Task description/details (optional)
        'assigned_by',       // User ID who created/assigned the task
        'assigned_to',       // User ID who the task is assigned to
        'report_id',         // Optional: linked report this task relates to
        'priority',          // Priority level: 'low', 'medium', 'high', 'urgent'
        'status',            // Current status: 'pending', 'in_progress', 'completed'
        'due_date',          // Optional deadline for the task
        'completed_at',      // When the task was marked as completed
        'completion_notes',  // Notes added when completing the task
    ];

    /**
     * Type casting for model attributes
     */
    protected $casts = [
        'due_date'      => 'datetime',        // Cast to Carbon date object
        'completed_at'  => 'datetime',    // Cast to Carbon datetime object
        'deleted_at'    => 'datetime',    // Soft delete timestamp
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    
    /**
     * User who assigned/created this task
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * User this task is assigned to
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Report this task is related to (optional)
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */
    
    /**
     * Mark task as completed
     * Sets status to 'completed', records completion time, and saves notes
     * 
     * @param string|null $notes - Optional notes about task completion
     */
    public function markAsCompleted($notes = null): void
    {
        $this->update([
            'status'           => 'completed',
            'completed_at'     => now(),
            'completion_notes' => $notes
        ]);
    }

    /**
     * Check if task is overdue
     * A task is overdue if:
     * - It has a due date
     * - The due date has passed
     * - The task is not completed
     * 
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->due_date 
            && $this->due_date->isPast() 
            && $this->status !== 'completed';
    }

    /**
     * Get CSS color classes for priority badge
     * Returns Tailwind CSS classes for different priority levels
     * 
     * @return string - Tailwind CSS classes
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'low'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'medium' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'high'   => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default  => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Get CSS color classes for status badge
     * Returns Tailwind CSS classes for different status levels
     * 
     * @return string - Tailwind CSS classes
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending'     => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'completed'   => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'overdue'     => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default       => 'bg-gray-100 text-gray-700',
        };
    }
}