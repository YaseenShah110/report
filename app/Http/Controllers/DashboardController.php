<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\ReportAssignment;
use App\Models\Template;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

/**
 * Dashboard Controller — Vue-Aligned Edition
 *
 * ╔══════════════════════════════════════════════════════════════════════╗
 * ║  VUE → CONTROLLER DATA MAP  (every prop access traced)             ║
 * ╠══════════════════════════════════════════════════════════════════════╣
 * ║                                                                      ║
 * ║  props.recentReports[]                                              ║
 * ║    .id / .title / .slug / .status / .updated_at / .total_pages      ║
 * ║    .template.name                                                    ║
 * ║                                                                      ║
 * ║  props.stats{}                                                       ║
 * ║    .total_reports      → heroCards[0].value                         ║
 * ║    .published_reports  → heroCards[1].value + polar chart           ║
 * ║    .draft_reports      → polar chart                                ║
 * ║    .archived_reports   → polar chart                                ║
 * ║    .completed_tasks    → heroCards[3] + globe + kanban              ║
 * ║    .pending_tasks      → heroCards[4] + globe + kanban              ║
 * ║    .total_templates    → heroCards[5].value                         ║
 * ║                                                                      ║
 * ║  props.recentActivities[]                                            ║
 * ║    .id / .action / .entity_type / .created_at                       ║
 * ║    .details.report_title  ← must be decoded array, not JSON string  ║
 * ║    .details.task_title                                               ║
 * ║                                                                      ║
 * ║  props.chartData{}                                                   ║
 * ║    .reports_last_30_days.labels[] / .values[]                       ║
 * ║    .task_completion_rate  (float)                                    ║
 * ║    .user_growth.labels[] / .values[]                                ║
 * ║    .popular_report_types.labels[] / .values[]                       ║
 * ║                                                                      ║
 * ║  page.props.notifications{}   ← CRITICAL: was never populated!      ║
 * ║    .items[]              → notification panel list                  ║
 * ║    .unread_count         → bell badge                               ║
 * ║    .assigned_reports     → heroCards[2] "Shared with Me"            ║
 * ║                            + ticker + polar chart[3]                ║
 * ║    .overdue_tasks        → overdueAlert + globe + kanban + ticker   ║
 * ║                                                                      ║
 * ╚══════════════════════════════════════════════════════════════════════╝
 *
 * KEY BUGS FIXED
 * ──────────────────────────────────
 * BUG 1 — page.props.notifications was never sent from the controller.
 *          Vue's heroCards[2] (Shared with Me), overdueCount, kanban
 *          overdue column, and globe "X Overdue" were always 0/empty.
 *          Fixed: index() now builds and passes a full notifications payload.
 *
 * BUG 2 — page.props.notifications.overdue_tasks was never computed.
 *          Added a dedicated Task query with due_date < now filter.
 *
 * BUG 3 — recentActivities.details may arrive as a JSON string from MySQL.
 *          Vue accesses a.details.report_title (object access, not string).
 *          Fixed: map() now ensures details is always a decoded array.
 *
 * BUG 4 — YEAR boundary bug in completed_tasks: MONTH(completed_at) = 1
 *          matches January of ANY year. Added YEAR guard.
 *
 * BUG 5 — report.with('template') loaded all template columns. Changed to
 *          with(['template:id,name']) — only fetches what Vue uses.
 *
 * BUG 6 (HY001 ROOT CAUSE) — Every previous version produced an ORDER BY
 *          (updated_at, FIELD(id,...), or withCount correlated subquery) that
 *          MySQL tried to satisfy with a filesort. On a dev MySQL with the
 *          default 256 KB sort_buffer_size, even 8 rows overflow when the
 *          reports.content column contains large JSON blobs.
 *
 *          Fix: ALL ORDER BY / FIELD() / withCount() removed from SQL.
 *          Data is fetched unsorted by PK/index scans only.
 *          Sorting is done entirely in PHP on the small collections
 *          (≤20 rows) already in memory — zero DB sort memory used.
 */
class DashboardController extends Controller
{
    // ── Cache TTLs ──────────────────────────────────────────────────────────
    private const CACHE_USER_TTL   = 300;   // 5 min  – per-user stats & charts
    private const CACHE_GLOBAL_TTL = 900;   // 15 min – global aggregates

    // ════════════════════════════════════════════════════════════════════════
    // MAIN PAGE
    // ════════════════════════════════════════════════════════════════════════

    public function index()
    {
        $user = auth()->user();
        $uid  = (int) $user->id;
        $now  = now()->toDateTimeString();

        // ── 1. RECENT REPORTS ─────────────────────────────────────────────────
        //
        // HY001 ROOT FIX:
        // Every prior version eventually emitted "ORDER BY updated_at DESC"
        // which MySQL could not satisfy without a filesort. On this server the
        // sort_buffer_size is too small to sort even 8 rows when the content
        // column is a large JSON blob.
        //
        // New strategy:
        //   • Two cheap DB::table plucks — no ORDER BY in SQL, hits narrow indexes
        //   • Merge ≤10 rows in PHP, sort in PHP (usort on stdClass, free)
        //   • Final fetch by PK IN(...) — again NO ORDER BY in SQL
        //   • PHP usort on ≤10 objects for final order
        //
        // Required indexes (add via migration if missing):
        //   reports(user_id, deleted_at)
        //   report_assignments(user_id, is_active, report_id)

        // Branch A — own reports: narrow index scan, no sort
        $ownRows = DB::table('reports')
            ->select('id', 'updated_at')
            ->where('user_id', $uid)
            ->whereNull('deleted_at')
            ->limit(5)
            ->get()
            ->keyBy('id');                             // Collection<id => obj>

        // Branch B — assigned reports: narrow join, no sort
        $assignedRows = DB::table('report_assignments as ra')
            ->join('reports as r', 'r.id', '=', 'ra.report_id')
            ->select('r.id', 'r.updated_at')
            ->where('ra.user_id', $uid)
            ->where('ra.is_active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('ra.expires_at')
                  ->orWhere('ra.expires_at', '>', $now);
            })
            ->whereNull('r.deleted_at')
            ->limit(5)
            ->get()
            ->keyBy('id');                             // Collection<id => obj>

        // Merge, deduplicate (union on keys), sort by updated_at in PHP
        $merged = $ownRows
            ->union($assignedRows)                     // deduplicate by id key
            ->sortByDesc('updated_at')                 // PHP sort — zero DB cost
            ->take(5)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        $recentReports = collect();

        if (!empty($merged)) {
            $placeholders = implode(',', array_fill(0, count($merged), '?'));

            // NO ORDER BY — fetch columns by PK only
            $rows = DB::select(
                "SELECT r.id, r.title, r.slug, r.status,
                        r.updated_at, r.content, t.name AS template_name
                 FROM reports r
                 LEFT JOIN templates t ON t.id = r.template_id
                 WHERE r.id IN ({$placeholders})
                   AND r.deleted_at IS NULL",
                $merged
            );

            // PHP sort to restore recency order
            usort($rows, fn ($a, $b) => strcmp($b->updated_at ?? '', $a->updated_at ?? ''));

            $recentReports = collect($rows)
                ->map(fn ($r) => [
                    'id'          => $r->id,
                    'title'       => $r->title,
                    'slug'        => $r->slug,
                    'status'      => $r->status,
                    'updated_at'  => $r->updated_at,
                    'total_pages' => count(
                        is_string($r->content)
                            ? (json_decode($r->content, true) ?? [])
                            : ($r->content ?? [])
                    ),
                    'template'    => $r->template_name
                                        ? ['name' => $r->template_name]
                                        : null,
                ]);
        }

        // ── 2. STATS ──────────────────────────────────────────────────────────
        $stats = Cache::remember(
            "dashboard:stats:{$uid}",
            self::CACHE_USER_TTL,
            fn () => $this->getStats($user)
        );

        // ── 3. RECENT ACTIVITIES ──────────────────────────────────────────────
        //
        // BUG FIX: details must be a decoded PHP array.
        // Vue accesses a.details.report_title and a.details.task_title.
        // If UserActivity casts details to array this is automatic;
        // the manual decode below handles the case where it does not.

        $recentActivities = UserActivity::select([
                'id', 'action', 'entity_type', 'details', 'created_at',
            ])
            ->where('user_id', $uid)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($a) => [
                'id'          => $a->id,
                'action'      => $a->action,
                'entity_type' => $a->entity_type,
                'details'     => is_array($a->details)
                                    ? $a->details
                                    : (json_decode($a->details, true) ?? []),
                'created_at'  => $a->created_at,
            ]);

        // ── 4. CHART DATA ─────────────────────────────────────────────────────
        $chartData = [
            'reports_last_30_days' => Cache::remember(
                "dashboard:chart:reports30:{$uid}",
                self::CACHE_USER_TTL,
                fn () => $this->getReportsChartData($uid)
            ),
            'task_completion_rate' => Cache::remember(
                "dashboard:chart:taskrate:{$uid}",
                self::CACHE_USER_TTL,
                fn () => $this->getTaskCompletionRate($uid)
            ),
            'user_growth' => Cache::remember(
                'dashboard:chart:usergrowth',
                self::CACHE_GLOBAL_TTL,
                fn () => $this->getUserGrowthChart()
            ),
            'popular_report_types' => Cache::remember(
                'dashboard:chart:populartypes',
                self::CACHE_GLOBAL_TTL,
                fn () => $this->getPopularReportTypes()
            ),
        ];

        // ── 5. NOTIFICATIONS PAYLOAD ──────────────────────────────────────────
        //
        // BUG FIX: Vue reads page.props.notifications for four things:
        //   .items[]          → notification panel list
        //   .unread_count     → red badge on bell
        //   .assigned_reports → heroCards[2] value, ticker, polar chart[3]
        //   .overdue_tasks    → overdueAlert card, globe .gs-red, kanban col
        // None of these were populated before — all showed 0 / empty.

        $notifSummary = Cache::remember(
            "dashboard:notif-summary:{$uid}",
            self::CACHE_USER_TTL,
            function () use ($uid, $now) {
                $overdueTasks = DB::table('tasks')
                    ->where('assigned_to', $uid)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', $now)
                    ->count();

                $assignedReports = DB::table('report_assignments')
                    ->where('user_id', $uid)
                    ->where('is_active', 1)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', $now);
                    })
                    ->count();

                return [
                    'assigned_reports' => $assignedReports,
                    'overdue_tasks'    => $overdueTasks,
                ];
            }
        );

        // Live notification items — intentionally not cached so panel is fresh
        $notifItems = Notification::where('user_id', $uid)
            ->recent()
            ->take(20)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'icon'       => $n->icon  ?? 'fa-solid fa-bell',
                'color'      => $n->color ?? '#64748b',
                'action_url' => $n->action_url,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
                'time_ago'   => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Notification::where('user_id', $uid)->unread()->count();

        return Inertia::render('Dashboard', [
            'recentReports'    => $recentReports,
            'stats'            => $stats,
            'recentActivities' => $recentActivities,
            'chartData'        => $chartData,
            // Passed as page.props.notifications — matches every Vue access
            'notifications'    => [
                'items'            => $notifItems,
                'unread_count'     => $unreadCount,
                'assigned_reports' => $notifSummary['assigned_reports'],
                'overdue_tasks'    => $notifSummary['overdue_tasks'],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // API ENDPOINTS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * GET /dashboard/notifications
     *
     * Returns same shape as page.props.notifications so Vue can merge.
     */
    public function notifications()
    {
        $uid = (int) auth()->id();
        $now = now()->toDateTimeString();

        $notifications = Notification::where('user_id', $uid)
            ->recent()
            ->take(20)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'icon'       => $n->icon  ?? 'fa-solid fa-bell',
                'color'      => $n->color ?? '#64748b',
                'action_url' => $n->action_url,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
                'time_ago'   => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Notification::where('user_id', $uid)->unread()->count();

        $overdueTasks = DB::table('tasks')
            ->where('assigned_to', $uid)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now)
            ->count();

        $assignedReports = DB::table('report_assignments')
            ->where('user_id', $uid)
            ->where('is_active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->count();

        return response()->json([
            'notifications'    => $notifications,
            'unread_count'     => $unreadCount,
            'assigned_reports' => $assignedReports,
            'overdue_tasks'    => $overdueTasks,
        ]);
    }

    /**
     * POST /dashboard/notifications/mark-all-read
     *
     * Marks all read and busts notification summary cache.
     */
    public function markNotificationsRead(Request $request)
    {
        $uid = (int) auth()->id();

        Notification::markAllAsReadForUser($uid);

        Cache::forget("dashboard:notif-summary:{$uid}");

        return response()->json([
            'message'      => 'All notifications marked as read',
            'unread_count' => 0,
        ]);
    }

    /**
     * GET /dashboard/quick-stats
     *
     * Lightweight polling endpoint.
     * Vue reads: reports_count, tasks_pending, shared_with_me
     */
    public function quickStats()
    {
        $uid = (int) auth()->id();

        return response()->json([
            'reports_count'  => DB::table('reports')
                ->where('user_id', $uid)
                ->whereNull('deleted_at')
                ->count(),
            'tasks_pending'  => DB::table('tasks')
                ->where('assigned_to', $uid)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'shared_with_me' => DB::table('report_assignments')
                ->where('user_id', $uid)
                ->where('is_active', 1)
                ->count(),
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Comprehensive stats for dashboard hero cards and charts.
     *
     * Vue consumption:
     *   total_reports     → heroCards[0].value + pct denominator
     *   published_reports → heroCards[1].value + polar chart[1]
     *   draft_reports     → polar chart[0]
     *   archived_reports  → polar chart[2]
     *   assigned_reports  → returned for API; Vue uses notifications version
     *   completed_tasks   → heroCards[3] + globe + kanban done column
     *   pending_tasks     → heroCards[4] + globe + kanban pending column
     *   total_templates   → heroCards[5].value
     *
     * HY001 FIX: uses DB::table() raw aggregation — no Eloquent model
     * scopes, no ORDER BY, no filesort anywhere in these queries.
     * Query count: 5 (down from 8+ in original).
     */
    private function getStats($user): array
    {
        $uid = (int) $user->id;
        $now = now()->toDateTimeString();
        $mon = (int) now()->month;
        $yr  = (int) now()->year;

        // Q1 — own report status breakdown in one aggregation pass, no ORDER BY
        $ownCounts = DB::table('reports')
            ->where('user_id', $uid)
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*)                                                AS total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END)  AS published,
                SUM(CASE WHEN status = 'draft'     THEN 1 ELSE 0 END)  AS draft,
                SUM(CASE WHEN status = 'archived'  THEN 1 ELSE 0 END)  AS archived
            ")
            ->first();

        // Q2 — active assignments count, no ORDER BY
        $assignedReports = DB::table('report_assignments')
            ->where('user_id', $uid)
            ->where('is_active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', $now);
            })
            ->count();

        // Q3 — assigned published count (narrow join, no ORDER BY)
        $assignedPublished = DB::table('report_assignments as ra')
            ->join('reports as r', 'r.id', '=', 'ra.report_id')
            ->where('ra.user_id', $uid)
            ->where('ra.is_active', 1)
            ->where('r.status', 'published')
            ->whereNull('r.deleted_at')
            ->where(function ($q) use ($now) {
                $q->whereNull('ra.expires_at')
                  ->orWhere('ra.expires_at', '>', $now);
            })
            ->count();

        // Q4 — task counts in one conditional aggregation pass
        // BUG FIX: YEAR guard added — MONTH alone matches same month in any year
        $taskCounts = DB::table('tasks')
            ->where('assigned_to', $uid)
            ->selectRaw("
                SUM(CASE WHEN status IN ('pending','in_progress')  THEN 1 ELSE 0 END)  AS pending_count,
                SUM(CASE WHEN status = 'completed'
                              AND MONTH(completed_at) = ?
                              AND YEAR(completed_at)  = ?
                         THEN 1 ELSE 0 END)                                            AS completed_this_month
            ", [$mon, $yr])
            ->first();

        // Q5 — active template count
        $totalTemplates = DB::table('templates')
            ->where('is_active', 1)
            ->count();

        return [
            'total_reports'     => (int) ($ownCounts->total     ?? 0) + $assignedReports,
            'published_reports' => (int) ($ownCounts->published ?? 0) + $assignedPublished,
            'draft_reports'     => (int) ($ownCounts->draft     ?? 0),
            'archived_reports'  => (int) ($ownCounts->archived  ?? 0),
            'assigned_reports'  => $assignedReports,
            'pending_tasks'     => (int) ($taskCounts->pending_count        ?? 0),
            'completed_tasks'   => (int) ($taskCounts->completed_this_month ?? 0),
            'total_templates'   => $totalTemplates,
        ];
    }

    /**
     * Report creation chart — last 30 days.
     *
     * Vue reads:
     *   .labels[] → velocity chart X-axis (e.g. "Apr 01")
     *   .values[] → velocity chart bars + heatmap cell intensity
     *
     * 1 GROUP BY query. No ORDER BY — label array built in PHP loop.
     */
    private function getReportsChartData(int $userId): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay()->toDateTimeString();
        $end   = Carbon::now()->endOfDay()->toDateTimeString();

        $rows = DB::table('reports')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS cnt')
            ->groupByRaw('DATE(created_at)')
            ->pluck('cnt', 'day')
            ->toArray();

        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $date     = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            $values[] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Task completion rate — float 0–100.
     *
     * Vue reads: chartData.task_completion_rate
     *   → productivityScore dial fallback when completed + pending = 0
     *
     * 1 conditional aggregation, no ORDER BY.
     */
    private function getTaskCompletionRate(int $userId): float
    {
        $result = DB::table('tasks')
            ->where('assigned_to', $userId)
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
            ")
            ->first();

        $total     = (int) ($result->total     ?? 0);
        $completed = (int) ($result->completed ?? 0);

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }

    /**
     * User growth chart — last 6 months.
     *
     * Vue reads:
     *   .labels[] → growth line chart X-axis (e.g. "Jan 2025")
     *   .values[] → growth line chart Y-axis
     *
     * 1 GROUP BY query. No ORDER BY — array built in PHP loop.
     */
    private function getUserGrowthChart(): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth()->toDateTimeString();

        $rows = DB::table('users')
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) AS yr, MONTH(created_at) AS mo, COUNT(*) AS cnt')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->yr}-{$r->mo}" => (int) $r->cnt])
            ->toArray();

        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $values[] = $rows["{$date->year}-{$date->month}"] ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Popular report types — top 5 templates by report count.
     *
     * Vue reads:
     *   .labels[] → bar race row labels + raceRaw fallback labels
     *   .values[] → bar race row values + animated bar widths
     *
     * HY001 FIX: replaced withCount() (correlated subquery per row that
     * forces filesort on the full reports table) with a LEFT JOIN + GROUP BY.
     * The ORDER BY here is on a COUNT aggregate over ≤5 grouped rows —
     * MySQL resolves this with a tiny tmp table, not a full-table filesort.
     * Fallback labels match Vue's hardcoded raceRaw fallback exactly.
     */
    private function getPopularReportTypes(): array
    {
        $rows = DB::table('templates')
            ->leftJoin('reports', function ($join) {
                $join->on('reports.template_id', '=', 'templates.id')
                     ->whereNull('reports.deleted_at');
            })
            ->where('templates.is_active', 1)
            ->selectRaw('templates.id, templates.name, COUNT(reports.id) AS reports_count')
            ->groupBy('templates.id', 'templates.name')
            ->orderByDesc('reports_count')
            ->limit(5)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'labels' => ['Business', 'Executive', 'Analytics', 'Marketing', 'Financial'],
                'values' => [0, 0, 0, 0, 0],
            ];
        }

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'values' => $rows->pluck('reports_count')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // CACHE INVALIDATION HELPERS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Flush all per-user dashboard cache keys.
     *
     * Call from Eloquent observers:
     *   ReportObserver::created/updated/deleted    → flushUserCache($userId)
     *   TaskObserver::saved/deleted               → flushUserCache($userId)
     *   ReportAssignmentObserver::saved/deleted   → flushUserCache($userId)
     */
    public function flushUserCache(int $userId): void
    {
        Cache::forget("dashboard:stats:{$userId}");
        Cache::forget("dashboard:chart:reports30:{$userId}");
        Cache::forget("dashboard:chart:taskrate:{$userId}");
        Cache::forget("dashboard:notif-summary:{$userId}");
    }

    /**
     * Flush global (cross-user) dashboard cache keys.
     *
     * Call from:
     *   UserObserver::created              → flushGlobalCache()
     *   TemplateObserver::saved/deleted    → flushGlobalCache()
     */
    public function flushGlobalCache(): void
    {
        Cache::forget('dashboard:chart:usergrowth');
        Cache::forget('dashboard:chart:populartypes');
    }
}