<?php

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
     * Display paginated list of all activities.
     */
    public function index(Request $request)
    {
        $activities = UserActivity::with('user')
            ->when($request->action,    fn($q) => $q->where('action', $request->action))
            ->when($request->user_id,   fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->search,    fn($q) => $q->whereHas('user', fn($sq) =>
                $sq->where('name', 'like', "%{$request->search}%")
                   ->orWhere('email', 'like', "%{$request->search}%")
            ))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy(in_array($request->sort, ['created_at','action','user_id']) ? $request->sort : 'created_at',
                      $request->direction === 'asc' ? 'asc' : 'desc')
            ->paginate(25)
            ->through(fn($activity) => [
                'id'          => $activity->id,
                'action'      => $activity->action,
                'entity_type' => $activity->entity_type,
                'entity_id'   => $activity->entity_id,
                'details'     => $activity->details,
                'ip_address'  => $activity->ip_address,
                'user_agent'  => $activity->user_agent,
                'created_at'  => $activity->created_at,
                'user' => $activity->user ? [
                    'id'    => $activity->user->id,
                    'name'  => $activity->user->name,
                    'email' => $activity->user->email,
                ] : null,
            ]);

        $users   = User::orderBy('name')->get(['id', 'name', 'email']);
        $actions = UserActivity::distinct()->orderBy('action')->pluck('action');

        $stats = [
            'total'      => UserActivity::count(),
            'today'      => UserActivity::whereDate('created_at', today())->count(),
            'this_week'  => UserActivity::whereBetween('created_at', [
                Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(),
            ])->count(),
            'this_month' => UserActivity::whereMonth('created_at', Carbon::now()->month)
                                        ->whereYear('created_at', Carbon::now()->year)
                                        ->count(),
        ];

        return Inertia::render('Admin/Activities/Index', [
            'activities' => $activities,
            'users'      => $users,
            'actions'    => $actions,
            'stats'      => $stats,
            'filters'    => $request->only([
                'action', 'user_id', 'search', 'date_from', 'date_to', 'sort', 'direction',
            ]),
        ]);
    }

    /**
     * Unified delete endpoint — three modes detected by payload shape.
     *
     * Mode 1 — Bulk by IDs:      { ids: [1,2,3] }
     * Mode 2 — By age (days):    { days: 90 }
     * Mode 3 — Filter-based:     { action?, user_id?, date_from?, date_to?,
     *                               entity_type?, older_than_days? }
     *
     * Route: DELETE /admin/activities/clear  →  admin.activities.clear
     */
    public function clear(Request $request)
    {
        // ── Mode 1: Explicit IDs ──────────────────────────────────────────
        if ($request->filled('ids')) {
            $request->validate([
                'ids'   => 'required|array|min:1|max:500',
                'ids.*' => 'required|integer|exists:user_activities,id',
            ]);

            $deleted = UserActivity::whereIn('id', $request->ids)->delete();

            UserActivity::log(auth()->id(), 'activities_bulk_deleted', 'system', null, [
                'deleted_count' => $deleted,
            ]);

            return back()->with('success', "Deleted {$deleted} selected " . str('record')->plural($deleted) . '.');
        }

        // ── Mode 2: Delete by age in days ────────────────────────────────
        // Detected when 'days' is present AND none of the filter-mode keys exist.
        // Cast to int guards against string '90' failing the >0 check.
        if ($request->filled('days')
            && ! $request->filled('action')
            && ! $request->filled('user_id')
            && ! $request->filled('entity_type')
            && ! $request->filled('date_from')
            && ! $request->filled('date_to')
            && ! $request->filled('older_than_days')
        ) {
            $days = (int) $request->days;

            $request->validate([
                'days' => 'required|integer|min:1|max:365',
            ]);

            $cutoff  = Carbon::now()->subDays($days);
            $deleted = UserActivity::where('created_at', '<', $cutoff)->delete();

            UserActivity::log(auth()->id(), 'activities_cleared', 'system', null, [
                'days'          => $days,
                'cutoff'        => $cutoff->toDateTimeString(),
                'deleted_count' => $deleted,
            ]);

            return back()->with('success', "Deleted {$deleted} " . str('record')->plural($deleted) . " older than {$days} days.");
        }

        // ── Mode 3: Filter-based deletion ────────────────────────────────
        $request->validate([
            'action'          => 'nullable|string|max:100',
            'user_id'         => 'nullable|integer|exists:users,id',
            'date_from'       => 'nullable|date',
            'date_to'         => 'nullable|date|after_or_equal:date_from',
            'entity_type'     => 'nullable|string|max:50',
            'older_than_days' => 'nullable|integer|min:1|max:3650',
        ]);

        $hasAnyFilter = $request->filled('action')
            || $request->filled('user_id')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('entity_type')
            || $request->filled('older_than_days');

        if (! $hasAnyFilter) {
            return back()->withErrors([
                'filter' => 'At least one filter criterion is required.',
            ]);
        }

        $query = UserActivity::query();

        if ($request->filled('action'))          $query->where('action', $request->action);
        if ($request->filled('user_id'))         $query->where('user_id', $request->user_id);
        if ($request->filled('date_from'))       $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))         $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('entity_type'))     $query->where('entity_type', $request->entity_type);
        if ($request->filled('older_than_days')) {
            $query->where('created_at', '<', Carbon::now()->subDays((int) $request->older_than_days));
        }

        $deleted = $query->delete();

        UserActivity::log(auth()->id(), 'activities_filter_deleted', 'system', null, [
            'filters'       => $request->only(['action','user_id','date_from','date_to','entity_type','older_than_days']),
            'deleted_count' => $deleted,
        ]);

        return back()->with('success', "Deleted {$deleted} matching " . str('record')->plural($deleted) . '.');
    }

    /**
     * Export activities to CSV with active filters applied.
     */
    public function export(Request $request)
    {
        $query = UserActivity::with('user');

        if ($request->filled('action'))    $query->where('action', $request->action);
        if ($request->filled('user_id'))   $query->where('user_id', $request->user_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);

        $activities = $query->orderBy('created_at', 'desc')->get();
        $filename   = 'activities_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Pre-format all data with enriched details BEFORE entering stream closure.
        // Extract browser/device from user_agent, categorize actions, format timestamps.
        $rows = $activities->map(function($a, $idx) {
            // Parse user agent to extract browser/device
            $ua = $a->user_agent ?? '';
            $browser = 'Unknown';
            if (stripos($ua, 'Chrome') !== false)     $browser = 'Chrome';
            elseif (stripos($ua, 'Firefox') !== false) $browser = 'Firefox';
            elseif (stripos($ua, 'Safari') !== false)  $browser = 'Safari';
            elseif (stripos($ua, 'Edge') !== false)    $browser = 'Edge';
            elseif (stripos($ua, 'Mobile') !== false)  $browser = 'Mobile';
            
            // Categorize action type
            $actionCategory = 'Other';
            if (stripos($a->action, 'creat') !== false) $actionCategory = 'Created';
            elseif (stripos($a->action, 'updat') !== false) $actionCategory = 'Updated';
            elseif (stripos($a->action, 'delet') !== false) $actionCategory = 'Deleted';
            elseif (stripos($a->action, 'login') !== false) $actionCategory = 'Login';
            elseif (stripos($a->action, 'logout') !== false) $actionCategory = 'Logout';
            elseif (stripos($a->action, 'export') !== false) $actionCategory = 'Export';
            elseif (stripos($a->action, 'assign') !== false) $actionCategory = 'Assigned';
            
            // Parse timestamp
            $ts = $a->created_at ? \Carbon\Carbon::parse($a->created_at) : null;
            dd($a->action ?? '');
            return [
                '#' . ($idx + 1),                                           // Sequence
                $ts ? $ts->format('Y-m-d') : '',                          // Date
                $ts ? $ts->format('H:i:s') : '',                          // Time
                $a->user_id ?? '',                                        // User ID
                $a->user->name ?? 'System',                               // User Name
                $a->user->email ?? '',                                    // Email
                $actionCategory,                                          // Action Category
                $a->action ?? '',                                         // Action (detailed)
                
                $a->entity_type ?? '',                                    // Entity Type
                $a->entity_id ?? '',                                      // Entity ID
                // Extract entity label from details if available
                (is_array($a->details) && isset($a->details['report_title']))
                    ? $a->details['report_title']
                    : ((is_array($a->details) && isset($a->details['task_title']))
                        ? $a->details['task_title']
                        : ((is_array($a->details) && isset($a->details['user_name']))
                            ? $a->details['user_name']
                            : '')),
                $a->ip_address ?? '',                                     // IP Address
                $browser,                                                 // Browser/Device
                // Full details as formatted JSON
                is_array($a->details) ? json_encode($a->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ($a->details ?? ''),
                $ts ? $ts->timezone(\Illuminate\Support\Facades\Auth::user()?->timezone ?? 'UTC')->format('Y-m-d H:i:s') : '', // Full timestamp with timezone
            ];
        })->toArray();

        return response()->stream(function () use ($rows, $filename) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens correctly without garbling special chars
            fwrite($handle, "ï»¿");
            // Enhanced header with all enriched columns — kept all original data PLUS new details
            fputcsv($handle, [
                'Sequence',      // New: Row number
                'Date',          // New: Separated from time
                'Time',          // New: Separated from date  
                'User ID',       // New: User identifier
                'User Name',     // Original: User
                'Email',         // Original: Email
                'Category',      // New: Action type categorized
                'Action',        // Original: Action
                'Entity Type',   // Original: Entity Type
                'Entity ID',     // Original: Entity ID
                'Entity Label',  // New: Name/title of entity
                'IP Address',    // Original: IP Address
                'Browser',       // New: Browser/Device
                'Details JSON',  // Original: Details (pretty-printed)
                'Full Timestamp' // New: Complete ISO timestamp
            ]);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Activities for a specific user (used by user show page).
     */
    public function userActivities(User $user, Request $request)
    {
        $activities = UserActivity::where('user_id', $user->id)
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn($a) => [
                'id'          => $a->id,
                'action'      => $a->action,
                'entity_type' => $a->entity_type,
                'details'     => $a->details,
                'ip_address'  => $a->ip_address,
                'created_at'  => $a->created_at,
            ]);

        return response()->json([
            'activities' => $activities,
            'stats'      => [
                'total'         => UserActivity::where('user_id', $user->id)->count(),
                'last_activity' => UserActivity::where('user_id', $user->id)->latest()->value('created_at'),
                'actions'       => UserActivity::where('user_id', $user->id)
                    ->select('action', DB::raw('count(*) as count'))
                    ->groupBy('action')
                    ->orderByDesc('count')
                    ->get(),
            ],
        ]);
    }

    /**
     * Activity statistics API.
     */
    public function getStats()
    {
        return response()->json([
            'by_action' => UserActivity::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')->orderByDesc('count')->get(),
            'by_hour'   => UserActivity::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
                ->groupBy('hour')->orderBy('hour')->get(),
            'by_day'    => UserActivity::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')->orderBy('date')->get(),
        ]);
    }
}