<?php
// app/Http/Controllers/Admin/ActivityController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityController extends Controller
{
  

    /**
     * Display all activities
     */
    public function index(Request $request)
    {
        $activities = UserActivity::with('user')
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->search, fn($q) => $q->whereHas('user', fn($sq) => 
                $sq->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
            ))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(25)
            ->through(fn($activity) => [
                'id' => $activity->id,
                'action' => $activity->action,
                'entity_type' => $activity->entity_type,
                'entity_id' => $activity->entity_id,
                'details' => $activity->details,
                'ip_address' => $activity->ip_address,
                'user_agent' => $activity->user_agent,
                'created_at' => $activity->created_at,
                'user' => $activity->user ? [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'email' => $activity->user->email,
                ] : null,
            ]);

        $users = User::all(['id', 'name', 'email']);
        
        $actions = UserActivity::distinct()->pluck('action');
        
        $stats = [
            'total' => UserActivity::count(),
            'today' => UserActivity::whereDate('created_at', today())->count(),
            'this_week' => UserActivity::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'this_month' => UserActivity::whereMonth('created_at', Carbon::now()->month)->count(),
        ];

        return Inertia::render('Admin/Activities/Index', [
            'activities' => $activities,
            'users' => $users,
            'actions' => $actions,
            'stats' => $stats,
            'filters' => $request->only(['action', 'user_id', 'search', 'date_from', 'date_to', 'sort', 'direction']),
        ]);
    }

    /**
     * Get activities for a specific user
     */
    public function userActivities(User $user, Request $request)
    {
        $activities = UserActivity::where('user_id', $user->id)
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(20)
            ->through(fn($activity) => [
                'id' => $activity->id,
                'action' => $activity->action,
                'entity_type' => $activity->entity_type,
                'details' => $activity->details,
                'ip_address' => $activity->ip_address,
                'created_at' => $activity->created_at,
            ]);

        $stats = [
            'total' => UserActivity::where('user_id', $user->id)->count(),
            'last_activity' => UserActivity::where('user_id', $user->id)->latest()->first()?->created_at,
            'actions' => UserActivity::where('user_id', $user->id)
                ->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->get(),
        ];

        return response()->json([
            'activities' => $activities,
            'stats' => $stats,
        ]);
    }

    /**
     * Clear old activities
     */
    public function clear(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $deleted = UserActivity::where('created_at', '<', Carbon::now()->subDays($request->days))->delete();

        // Log this action
        UserActivity::log(auth()->id(), 'activities_cleared', 'system', null, [
            'days' => $request->days,
            'deleted_count' => $deleted,
        ]);

        return back()->with('success', "Deleted {$deleted} activity records older than {$request->days} days.");
    }

    /**
     * Export activities
     */
    public function export(Request $request)
    {
        $query = UserActivity::with('user');
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->action) {
            $query->where('action', $request->action);
        }
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        $filename = 'activities_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $callback = function() use ($activities) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['User', 'Action', 'Entity Type', 'Details', 'IP Address', 'User Agent', 'Timestamp']);
            
            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->user->name ?? 'System',
                    $activity->action,
                    $activity->entity_type ?? 'N/A',
                    json_encode($activity->details),
                    $activity->ip_address ?? 'N/A',
                    $activity->user_agent ?? 'N/A',
                    $activity->created_at,
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get activity statistics
     */
    public function getStats()
    {
        $stats = [
            'by_action' => UserActivity::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'by_hour' => UserActivity::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
            'by_day' => UserActivity::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($stats);
    }
}