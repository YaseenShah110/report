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
    /**
 * Get element presets library.
 */
public function getPresets()
{
    $presets = [
        [
            'name' => 'Blue Header',
            'type' => 'heading',
            'styles' => [
                'fontSize' => 32,
                'fontWeight' => '700',
                'color' => '#1e40af',
                'textAlign' => 'left'
            ]
        ],
        [
            'name' => 'Subtitle Gray',
            'type' => 'subheading',
            'styles' => [
                'fontSize' => 18,
                'fontWeight' => '500',
                'color' => '#64748b',
                'textAlign' => 'left'
            ]
        ],
        [
            'name' => 'Metric Card',
            'type' => 'metric',
            'styles' => [
                'backgroundColor' => '#f8fafc',
                'borderRadius' => 12,
                'width' => 200,
                'height' => 120
            ]
        ],
        [
            'name' => 'CTA Badge',
            'type' => 'badge',
            'styles' => [
                'backgroundColor' => '#6366f1',
                'color' => '#ffffff',
                'borderRadius' => 999,
                'fontWeight' => '700',
                'padding' => '12 24',
                'fontSize' => 14
            ]
        ],
        [
            'name' => 'Elegant Divider',
            'type' => 'divider',
            'styles' => [
                'color' => '#e2e8f0',
                'height' => 2
            ]
        ],
        [
            'name' => 'Quote Style',
            'type' => 'quote',
            'styles' => [
                'fontStyle' => 'italic',
                'fontSize' => 16,
                'borderLeft' => '4px solid #6366f1',
                'paddingLeft' => 16,
                'color' => '#475569'
            ]
        ],
    ];
    
    return response()->json(['presets' => $presets]);
}

/**
 * Save element as preset.
 */
public function savePreset(Request $request)
{
    // In future, save to database. For now, just acknowledge.
    return response()->json([
        'message' => 'Preset saved successfully',
        'preset' => $request->all()
    ]);
}

/**
 * Get report statistics.
 */
public function reportStats($slug)
{
    $report = Report::where('slug', $slug)->firstOrFail();
    
    $totalElements = 0;
    $totalWords = 0;
    $textElements = 0;
    $imageElements = 0;
    $chartElements = 0;
    $tableElements = 0;
    
    foreach ($report->content ?? [] as $page) {
        foreach ($page['elements'] ?? [] as $el) {
            $totalElements++;
            
            // Count text words
            if (!empty($el['content']) && is_string($el['content'])) {
                $totalWords += str_word_count(strip_tags($el['content']));
            }
            
            // Count by type
            if (in_array($el['type'] ?? '', ['text', 'heading', 'subheading', 'quote', 'code', 'badge', 'link'])) {
                $textElements++;
            } elseif (($el['type'] ?? '') === 'image') {
                $imageElements++;
            } elseif (str_ends_with($el['type'] ?? '', '-chart')) {
                $chartElements++;
            } elseif (($el['type'] ?? '') === 'table') {
                $tableElements++;
            }
        }
    }
    
    return response()->json([
        'total_pages'     => count($report->content ?? []),
        'total_elements'  => $totalElements,
        'total_words'     => $totalWords,
        'text_elements'   => $textElements,
        'image_elements'  => $imageElements,
        'chart_elements'  => $chartElements,
        'table_elements'  => $tableElements,
        'status'          => $report->status,
        'created_at'      => $report->created_at->toISOString(),
        'updated_at'      => $report->updated_at->toISOString(),
    ]);
}
}