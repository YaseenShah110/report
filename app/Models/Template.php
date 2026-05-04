<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // For trash/recover functionality
use Illuminate\Support\Str;

class Template extends Model
{
    // SoftDeletes: Allows templates to be moved to trash instead of permanently deleted
    use SoftDeletes;
    
    /**
     * Mass-assignable fields
     * These fields can be set via Template::create() or $template->update()
     */
    protected $fillable = [
        'name',            // Template display name (e.g., "Executive Dark")
        'slug',            // URL-friendly unique identifier (auto-generated)
        'description',     // Short description of the template
        'thumbnail',       // Path to thumbnail image
        'badge',           // Optional badge text (e.g., "New", "Popular", "Pro")
        'category',        // Template category for filtering
        'tags',            // Array of tags for search/filtering
        'cover_gradient',  // CSS gradient for cover preview
        'structure',       // JSON structure defining pages and elements
        'settings',        // Default settings (page size, colors, fonts)
        'is_active',       // Whether template is available for use
    ];
    
    /**
     * Type casting for model attributes
     */
    protected $casts = [
        'structure'  => 'array',      // Auto JSON encode/decode
        'settings'   => 'array',      // Auto JSON encode/decode
        'tags'       => 'array',      // Auto JSON encode/decode
        'is_active'  => 'boolean',    // Cast to true/false
        'deleted_at' => 'datetime',   // Soft delete timestamp
    ];
    
    /**
     * Boot method - called when the model is initialized
     * Auto-generates slug when creating a new template
     */
    protected static function boot()
    {
        parent::boot();
        
        // BEFORE CREATING: Auto-generate slug from name if not provided
        static::creating(function ($template) {
            if (empty($template->slug)) {
                // Create URL-friendly slug from name + random string for uniqueness
                $template->slug = Str::slug($template->name) . '-' . Str::random(6);
            }
        });
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    
    /**
     * Reports created using this template
     * One template can be used by many reports
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    
    /**
     * Get default settings merged with template-specific settings
     * If a setting is not defined in the template, use the default value
     */
    public function getDefaultSettingsAttribute()
    {
        return array_merge([
            'page_size'      => 'A4',          // A4, Letter, Legal, A3
            'orientation'    => 'portrait',     // portrait or landscape
            'font_family'    => 'Inter',        // Default font family
            'primary_color'  => '#3b82f6',      // Primary accent color
            'secondary_color'=> '#6b7280',      // Secondary color
            'margin_top'     => 40,             // Top margin in pixels
            'margin_bottom'  => 40,             // Bottom margin in pixels
            'margin_left'    => 40,             // Left margin in pixels
            'margin_right'   => 40,             // Right margin in pixels
        ], $this->settings ?? []);
    }
}