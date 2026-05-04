<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Report Version Model
 * 
 * Stores version history for reports.
 * Each version captures the content, settings, and title at a point in time.
 * Only keeps the last 50 versions per report.
 */
class ReportVersion extends Model
{
    /**
     * Disable automatic timestamps.
     * We use created_at only (no updated_at needed for versions).
     */
    public $timestamps = false;

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'report_id',
        'user_id',
        'label',
        'content',
        'settings',
        'title',
        'version_number',
    ];

    /**
     * Type casting for model attributes.
     */
    protected $casts = [
        'content'    => 'array',
        'settings'   => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot method - auto-sets created_at timestamp.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($version) {
            if (!$version->created_at) {
                $version->created_at = now();
            }
        });
    }

    /**
     * Report this version belongs to.
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * User who created this version snapshot.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}