<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Template extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'thumbnail', 
        'structure', 'settings', 'is_active'
    ];
    
    protected $casts = [
        'structure' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }
    
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    
    public function getDefaultSettingsAttribute()
    {
        return array_merge([
            'page_size' => 'A4',
            'orientation' => 'portrait',
            'font_family' => 'Inter',
            'primary_color' => '#3b82f6',
            'secondary_color' => '#6b7280',
            'margin_top' => 40,
            'margin_bottom' => 40,
            'margin_left' => 40,
            'margin_right' => 40,
        ], $this->settings ?? []);
    }
}