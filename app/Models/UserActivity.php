<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    /**
     * Mass-assignable fields
     */
    protected $fillable = [
        'user_id',       // User who performed the action
        'action',        // Action type: 'report_created', 'task_updated', etc.
        'entity_type',   // Related model type (e.g., 'report', 'task', 'user')
        'entity_id',     // Related model ID
        'details',       // Additional details in JSON format
        'ip_address',    // IP address of the user
        'user_agent',    // Browser user agent string
    ];

    /**
     * Type casting for model attributes
     */
    protected $casts = [
        'details' => 'array',  // Auto JSON encode/decode the details
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    
    /**
     * User who performed this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC HELPER METHODS
    |--------------------------------------------------------------------------
    */
    
    /**
     * Log a user activity
     * This is the main method used throughout the application to track user actions
     * 
     * @param int $userId - ID of the user performing the action
     * @param string $action - Action type (e.g., 'report_created', 'task_completed')
     * @param string|null $entityType - Type of entity affected (e.g., 'report', 'task')
     * @param int|null $entityId - ID of the entity affected
     * @param array $details - Additional details about the action
     * @return self - The created activity record
     * 
     * Example usage:
     * UserActivity::log(auth()->id(), 'report_created', 'report', $report->id, [
     *     'report_title' => $report->title
     * ]);
     */
    public static function log($userId, $action, $entityType = null, $entityId = null, $details = []): self
    {
        return self::create([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details,
            // Get IP address from request, or use 'CLI' for console commands
            'ip_address'  => request()->ip() ?? 'CLI',
            // Get user agent from request, or use 'CLI' for console commands
            'user_agent'  => request()->userAgent() ?? 'CLI',
        ]);
    }
}