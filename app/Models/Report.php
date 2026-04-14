<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $fillable = [
        'user_id', 'template_id', 'title', 'slug', 'content', 
        'settings', 'metadata', 'status', 'published_at'
    ];
    
    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'metadata' => 'array',
        'published_at' => 'datetime',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($report) {
            if (empty($report->slug)) {
                $report->slug = Str::slug($report->title) . '-' . uniqid();
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    
    public function getTotalPagesAttribute()
    {
        return count($this->content ?? []);
    }
}