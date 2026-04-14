<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $recentReports = Report::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
            
        $stats = [
            'total_reports' => Report::where('user_id', auth()->id())->count(),
            'published_reports' => Report::where('user_id', auth()->id())->where('status', 'published')->count(),
            'draft_reports' => Report::where('user_id', auth()->id())->where('status', 'draft')->count(),
        ];
        
        return Inertia::render('Dashboard', [
            'recentReports' => $recentReports,
            'stats' => $stats
        ]);
    }
}