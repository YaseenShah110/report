<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Presence Controller
 * ---------------------------------------------------------------
 * Lightweight, scalable "who's editing this report right now" tracker.
 *
 * Deliberately uses the cache layer (whatever CACHE_STORE is configured
 * to — Redis in production, database/file elsewhere) instead of a
 * database table. This means:
 *   - Heartbeats from many concurrent editors never write to the
 *     reports/users tables.
 *   - There is no growing log table that needs a cleanup job — entries
 *     simply expire (TTL) and stale editors are pruned on read.
 *   - It scales horizontally the same way the rest of the app's cache
 *     does, so dozens of people on the same report (or thousands of
 *     people across different reports) never "stick" the app.
 *
 * Each open editor tab sends a heartbeat every ~8s (see usePresence.js).
 * The heartbeat endpoint both records the caller's presence AND returns
 * the current full list in the same round trip, so the frontend never
 * needs two separate polling loops.
 */
class PresenceController extends Controller
{
    /** How long a cache entry is allowed to live before it's GC'd outright. */
    private const TTL_SECONDS = 30;

    /** How old a heartbeat can be and still count as "actively editing". */
    private const STALE_SECONDS = 15;

    /**
     * Record a heartbeat for the current user and return the
     * current list of active editors (including the caller).
     */
    public function heartbeat(Request $request, string $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $key = $this->cacheKey($report->id);
        $editors = Cache::get($key, []);
        $now = now()->timestamp;

        // Prune anyone who hasn't pinged recently, then upsert the caller.
        $editors = collect($editors)
            ->filter(fn ($e) => ($now - ($e['last_seen'] ?? 0)) < self::STALE_SECONDS)
            ->keyBy('id')
            ->toArray();

        $editors[$user->id] = [
            'id' => $user->id,
            'name' => $user->name,
            'initials' => $this->initials($user->name),
            'color' => $this->colorFor($user->id),
            'last_seen' => $now,
        ];

        Cache::put($key, $editors, self::TTL_SECONDS);

        return response()->json([
            'editors' => array_values($editors),
            'count' => count($editors),
        ]);
    }

    /**
     * Explicitly remove the current user from the active-editors list.
     * Called via navigator.sendBeacon when the tab closes or the user
     * navigates away, so the badge updates immediately instead of
     * waiting out the stale-timeout.
     */
    public function leave(Request $request, string $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $key = $this->cacheKey($report->id);
        $editors = Cache::get($key, []);
        unset($editors[$user->id]);
        Cache::put($key, $editors, self::TTL_SECONDS);

        return response()->json(['ok' => true]);
    }

    private function cacheKey(int $reportId): string
    {
        return "report-presence:{$reportId}";
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $initials = strtoupper(($parts[0][0] ?? '').($parts[1][0] ?? ($parts[0][1] ?? '')));

        return $initials !== '' ? $initials : '?';
    }

    private function colorFor(int $userId): string
    {
        $palette = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#8b5cf6', '#f97316'];

        return $palette[$userId % count($palette)];
    }
}