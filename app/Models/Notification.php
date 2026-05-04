<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable fields
     */
    protected $fillable = [
        'user_id',           // The user who receives this notification
        'type',              // Notification type: 'task_created', 'report_shared', etc.
        'title',             // Short title for the notification
        'message',           // Detailed message body
        'notifiable_type',   // Related model type (e.g., 'App\Models\Report')
        'notifiable_id',     // Related model ID
        'data',              // Additional JSON data
        'icon',              // Font Awesome icon class
        'color',             // Accent color for the notification
        'action_url',        // URL to navigate when clicked
        'read_at',           // Timestamp when notification was read (null = unread)
    ];

    /**
     * Type casting for model attributes
     */
    protected $casts = [
        'data'      => 'array',       // Auto JSON encode/decode
        'read_at'   => 'datetime',    // Carbon datetime instance
        'deleted_at'=> 'datetime',    // Soft delete timestamp
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    
    /**
     * User who owns this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relationship to the related model
     * Can be a Report, Task, or any other notifiable model
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    | Scopes allow you to easily add constraints to queries
    */
    
    /**
     * Scope: Only unread notifications
     * Usage: Notification::unread()->get()
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Only read notifications
     * Usage: Notification::read()->get()
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: Notifications for a specific user
     * Usage: Notification::forUser($userId)->get()
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Order by most recent first
     * Usage: Notification::recent()->get()
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter by notification type
     * Usage: Notification::ofType('task_created')->get()
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */
    
    /**
     * Mark this notification as read
     * Only updates if not already read
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    /**
     * Mark this notification as unread
     */
    public function markAsUnread(): void
    {
        if (!is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Check if notification has been read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Mark ALL notifications as read for a specific user
     * Returns the number of notifications marked as read
     */
    public static function markAllAsReadForUser($userId): int
    {
        return static::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get count of unread notifications for a user
     */
    public static function unreadCountForUser($userId): int
    {
        return static::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}