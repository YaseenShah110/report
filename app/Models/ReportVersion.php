<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'report_id', 'user_id', 'label', 'content', 'settings', 'title', 'version_number'
    ];

    protected $casts = [
        'content'    => 'array',
        'settings'   => 'array',
        'created_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}