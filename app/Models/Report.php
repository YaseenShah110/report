<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Report extends Model
{
    // SoftDeletes: Instead of permanently deleting, sets deleted_at timestamp
    // Records with deleted_at set are excluded from normal queries
    // Can be restored with restore() or permanently deleted with forceDelete()
    use SoftDeletes;
    
    /**
     * Mass-assignable fields
     * Only these fields can be set via Report::create() or $report->update()
     */
    protected $fillable = [
        'user_id',       // Owner of the report
        'template_id',   // Template used to create this report (nullable for blank)
        'title',         // Report title
        'slug',          // URL-friendly unique identifier (auto-generated)
        'share_token',   // Random token for public sharing (null if not shared)
        'is_public',     // Whether report is publicly accessible via share link
        'content',       // JSON structure of pages and elements (the actual report)
        'settings',      // Report settings (page size, colors, fonts, etc.)
        'metadata',      // Additional metadata (can be used for custom data)
        'status',        // Report status: 'draft', 'published', 'archived'
        'published_at',  // When the report was published
    ];
    
    /**
     * Type casting for model attributes
     * JSON fields are automatically encoded/decoded
     * DateTime fields become Carbon instances
     */
    protected $casts = [
        'content'      => 'array',       // Auto JSON encode/decode
        'settings'     => 'array',       // Auto JSON encode/decode
        'metadata'     => 'array',       // Auto JSON encode/decode
        'published_at' => 'datetime',    // Carbon datetime instance
        'is_public'    => 'boolean',     // Cast to true/false
        'deleted_at'   => 'datetime',    // Soft delete timestamp
    ];
    
    /**
     * Boot method - called when the model is initialized
     * Used to register model event handlers
     */
    protected static function boot()
    {
        parent::boot();
        
        // BEFORE CREATING: Auto-generate slug if not provided
        // Slug is used in URLs instead of numeric ID for better SEO and security
        static::creating(function ($report) {
            if (empty($report->slug)) {
                // Create URL-friendly slug from title + random string for uniqueness
                $report->slug = Str::slug($report->title) . '-' . Str::random(8);
            }
        });
        
        // AFTER SOFT DELETING: Also soft-delete related notifications
        // This keeps the notification system in sync with the report
        static::softDeleted(function ($report) {
            // Find all notifications related to this report
            \App\Models\Notification::where('notifiable_type', self::class)
                ->where('notifiable_id', $report->id)
                ->delete(); // Soft delete notifications too
        });
        
        // AFTER RESTORING: Also restore related notifications
        static::restored(function ($report) {
            // Restore notifications that were soft-deleted with the report
            \App\Models\Notification::withTrashed()
                ->where('notifiable_type', self::class)
                ->where('notifiable_id', $report->id)
                ->restore();
        });
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    
    /**
     * Report owner - the user who created this report
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Report assignments - users this report is shared with
     */
    public function assignments()
    {
        return $this->hasMany(ReportAssignment::class);
    }
    
    /**
     * Template used to create this report
     * Can be null for blank reports
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    
    /**
     * Version history for this report
     * Ordered by newest first
     */
    public function versions()
    {
        return $this->hasMany(ReportVersion::class)->orderBy('version_number', 'desc');
    }
    
    /**
     * Tasks associated with this report
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    | Computed properties that can be accessed like regular attributes
    */
    
    /**
     * Get total number of pages in this report
     * Counts the pages array in the content JSON
     */
    public function getTotalPagesAttribute()
    {
        return count($this->content ?? []);
    }
    
    /**
     * Check if report is published
     */
    public function getIsPublishedAttribute()
    {
        return $this->status === 'published';
    }
    
    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */
    
    /**
     * Generate a public share link for this report
     * Creates a random 32-character token and sets is_public to true
     * @return string The share token
     */
    public function generateShareToken()
    {
        $this->update([
            'share_token' => Str::random(32),  // Cryptographically secure random string
            'is_public'   => true,
        ]);
        
        return $this->share_token;
    }
    
    /**
     * Revoke the public share link
     * Removes the token and sets is_public to false
     */
    public function revokeShareToken()
    {
        $this->update([
            'share_token' => null,
            'is_public'   => false,
        ]);
    }
    
    /**
     * Publish this report
     * Changes status to 'published' and records the publish date
     */
    public function publish()
    {
        $this->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);
    }
    
    /**
     * Archive this report
     * Removes from active view but keeps the data
     */
    public function archive()
    {
        $this->update([
            'status' => 'archived',
        ]);
    }
    
    /**
     * Check if a user is the owner of this report
     */
    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }
    
    /**
     * Check if a user can edit this report
     * Admin users, owners, and users with 'edit' or 'manage' assignments can edit
     */
    public function canBeEditedBy(User $user): bool
    {
        // Admin can edit everything
        if ($user->hasRole('admin')) return true;
        
        // Owner can edit their own reports
        if ($this->isOwner($user)) return true;
        
        // Check if user has an active assignment with edit permissions
        return $this->assignments()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereIn('permission', ['edit', 'manage'])
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
    
    /**
     * Check if a user can view this report
     * Admin users, owners, public reports, and assigned users can view
     */
    public function canBeViewedBy(User $user): bool
    {
        // Admin can view everything
        if ($user->hasRole('admin')) return true;
        
        // Owner can view their own reports
        if ($this->isOwner($user)) return true;
        
        // Public reports can be viewed by anyone with the link
        if ($this->is_public) return true;
        
        // Check if user has an active assignment (any permission level)
        return $this->assignments()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}