<?php
// app/Models/Report.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $fillable = [
        'user_id', 'template_id', 'title', 'slug', 'share_token', 'is_public',
        'content', 'settings', 'metadata', 'status', 'published_at'
    ];
    
    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'metadata' => 'array',
        'published_at' => 'datetime',
        'is_public' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($report) {
            if (empty($report->slug)) {
                $report->slug = Str::slug($report->title) . '-' . Str::random(8);
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
        // ✅ ADD THIS METHOD - Missing relationship
    public function assignments()
    {
        return $this->hasMany(ReportAssignment::class);
    }
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    
    public function versions()
    {
        return $this->hasMany(ReportVersion::class)->orderBy('created_at', 'desc');
    }
    
    public function getTotalPagesAttribute()
    {
        return count($this->content ?? []);
    }
    
    public function generateShareToken()
    {
        $this->update([
            'share_token' => Str::random(32),
            'is_public' => true,
        ]);
        
        return $this->share_token;
    }
    
    public function revokeShareToken()
    {
        $this->update([
            'share_token' => null,
            'is_public' => false,
        ]);
    }
}