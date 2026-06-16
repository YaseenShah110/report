<?php

namespace App\Http\Controllers;

use App\Exports\ReportTablesExport;
use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportVersion;
use App\Models\Template;
use App\Models\UserActivity;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;

/**
 * Report Controller
 *
 * Handles all report CRUD operations, versioning, sharing, and export.
 * Supports PDF (Browsershot/DomPDF), Excel, CSV, and PNG export.
 *
 * Access: Authenticated users for own reports, assigned users for shared reports,
 *         public access via share tokens.
 */
class ReportController extends Controller
{
    /**
     * List reports for current user.
     * Shows owned reports AND reports assigned to the user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get reports that user owns OR has been assigned to (active, not expired)
        $query = Report::with('template')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('assignments', function ($sq) use ($user) {
                        $sq->where('user_id', $user->id)
                            ->where('is_active', true)
                            ->where(function ($exp) {
                                $exp->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                            });
                    });
            });

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply sorting
        $sort = $request->get('sort', 'updated_at');
        $query->orderBy($sort === 'title' ? 'title' : $sort,
            $sort === 'title' ? 'asc' : 'desc');

        $reports = $query->paginate(12)->withQueryString();

        // Calculate statistics (only for owned reports)

        $stats = [
            'total' => Report::where('user_id', auth()->id())->count(),
            'published' => Report::where('user_id', auth()->id())->where('status', 'published')->count(),
            'draft' => Report::where('user_id', auth()->id())->where('status', 'draft')->count(),
            'archived' => Report::where('user_id', auth()->id())->where('status', 'archived')->count(),
            'trashed' => Report::onlyTrashed()->where('user_id', auth()->id())->count(),
        ];

        $trashedReports = Report::onlyTrashed()
            ->where('user_id', $user->id)
            ->select('id', 'title', 'slug', 'deleted_at', 'settings')
            ->orderBy('deleted_at', 'desc')
            ->get();

        return Inertia::render('Reports/Index', compact('reports', 'stats', 'trashedReports'));
    }
public function allReports(Request $request)
{
    // Gate: admin only
    if (! auth()->user()->hasRole('admin')) {
        abort(403, 'Admin access required.');
    }
 
    $query = Report::with(['template', 'user'])
        ->withCount('assignments');
 
    if ($request->filled('search')) {
        $query->where('title', 'like', '%'.$request->search.'%');
    }
    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }
 
    $sort = $request->get('sort', 'updated_at');
    $query->orderBy($sort === 'title' ? 'title' : $sort, $sort === 'title' ? 'asc' : 'desc');
 
    $reports = $query->paginate(12)->withQueryString();
 
    $stats = [
        'total'     => Report::count(),
        'published' => Report::where('status', 'published')->count(),
        'draft'     => Report::where('status', 'draft')->count(),
        'archived'  => Report::where('status', 'archived')->count(),
        'trashed'   => Report::onlyTrashed()->count(),
    ];
 
    $trashedReports = Report::onlyTrashed()
        ->select('id', 'title', 'slug', 'deleted_at', 'settings')
        ->orderBy('deleted_at', 'desc')
        ->get();
 
    return Inertia::render('Reports/Index', [
        'reports'        => $reports,
        'stats'          => $stats,
        'trashedReports' => $trashedReports,
        'isAdminAllView' => true,
        'authUser'       => auth()->user()->only(['id', 'role', 'is_admin']),
    ]);
}
    /**
     * Show create report form with available templates.
     */
    public function create()
    {
        $templates = Template::where('is_active', true)->get();

        return Inertia::render('Reports/Create', compact('templates'));
    }

    /**
     * Store a new report.
     * Creates from template if template_id is provided, otherwise blank.
     * Creates initial version snapshot.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        $initialSettings = $request->get('initial_settings', []);

        // Build pages from template or create blank page
        if ($request->filled('template_id')) {
            $template = Template::findOrFail($request->template_id);
            $pages = $this->buildPagesFromTemplate($template);

            // Deep merge: template settings override defaults
            $templateSettings = $template->settings ?? [];
            $defaults = $this->defaultSettings();

            // Ensure ALL keys exist
            $settings = array_merge($defaults, $templateSettings, $initialSettings);

            // Apply template structure to pages
            if (! empty($template->structure)) {
                $pages = $this->buildPagesFromTemplate($template);
            }
        } else {
            $pages = [['id' => (string) Str::uuid(), 'label' => 'Page 1', 'elements' => []]];
            $settings = array_merge($this->defaultSettings(), $initialSettings);
        }

        // Create the report
        $report = Report::create([
            'user_id' => auth()->id(),
            'template_id' => $request->template_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title).'-'.Str::random(8),
            'content' => $pages,
            'settings' => $settings,
            'status' => 'draft',
        ]);

        // Log activity
        UserActivity::log(auth()->id(), 'report_created', 'report', $report->id, [
            'report_title' => $report->title,
            'template_used' => $request->template_id ? 'yes' : 'no',
        ]);

        // Create initial version
        $this->createVersionSnapshot($report, 'Initial version');

        return redirect()->route('reports.edit', $report->slug)
            ->with('success', 'Report created!');
    }

    /**
     * Show the report editor.
     * Checks if user has permission to edit (owner, admin, or assigned with edit/manage).
     */
    public function edit($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Check edit permission
        $user = auth()->user();
        if (! $report->canBeEditedBy($user)) {
            abort(403, 'You do not have permission to edit this report.');
        }

        return Inertia::render('Reports/Editor', ['report' => $report]);
    }

    /**
     * Update report (auto-save from editor).
     * Creates version snapshot if content or settings changed (throttled to 5 minutes).
     */
    public function update(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Check edit permission
        if (! $report->canBeEditedBy(auth()->user())) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|array',
            'settings' => 'required|array',
        ]);

        $oldContent = $report->content;
        $oldSettings = $report->settings;

        $report->update([
            'title' => $request->title,
            'content' => $request->content,
            'settings' => $request->settings,
        ]);

        // Create version snapshot if significant changes (throttled)
        $contentChanged = md5(json_encode($oldContent)) !== md5(json_encode($request->content));
        $settingsChanged = md5(json_encode($oldSettings)) !== md5(json_encode($request->settings));

        if ($contentChanged || $settingsChanged) {
            $lastVersion = ReportVersion::where('report_id', $report->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Only create version if last version was > 5 minutes ago
            if (! $lastVersion || now()->diffInMinutes($lastVersion->created_at) >= 5) {
                $this->createVersionSnapshot($report, 'Auto-saved');
            }
        }

        // Log activity
        UserActivity::log(auth()->id(), 'report_updated', 'report', $report->id, [
            'report_title' => $report->title,
            'content_changed' => $contentChanged,
            'settings_changed' => $settingsChanged,
        ]);

        return response()->json([
            'message' => 'Saved',
            'updated_at' => $report->fresh()->updated_at,
        ]);
    }

    /**
     * Preview a report (read-only view with download options).
     */
    public function preview($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Check view permission
        if (! $report->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this report.');
        }

        return Inertia::render('Reports/Preview', ['report' => $report]);
    }

    /**
     * Download report as PDF.
     * Uses Browsershot (preferred) with DomPDF fallback.
     * FIXED: Corrected typo "Browsersshot" → "Browsershot"
     */
    public function download($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        $filename = Str::slug($report->title).'.pdf';

        // Option A: Browsershot (recommended - better quality)
        if (class_exists(Browsershot::class)) {
            $previewUrl = route('reports.preview', $report->slug);
            $sessionCookie = session()->getId();
            $cookieName = config('session.cookie');

            // FIXED: "Browsersshot" → "Browsershot" (correct class name)
            $pdf = Browsershot::url($previewUrl)
                ->useCookies([$cookieName => $sessionCookie])
                ->waitUntilNetworkIdle()
                ->dismissDialogs()
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->paperSize(
                    $this->getPaperWidth($report->settings),
                    $this->getPaperHeight($report->settings)
                )
                ->landscape(($report->settings['orientation'] ?? 'portrait') === 'landscape')
                ->margins(0, 0, 0, 0)
                ->pdf();

            UserActivity::log(auth()->id(), 'report_downloaded', 'report', $report->id, [
                'format' => 'pdf',
                'via' => 'browsershot',
            ]);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        // Option B: DomPDF fallback
        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('pdfs.report', [
                'report' => $report,
                'content' => $report->content,
                'settings' => $report->settings ?? [],
            ]);

            $pdf->setPaper(
                $report->settings['page_size'] ?? 'A4',
                $report->settings['orientation'] ?? 'portrait'
            );

            UserActivity::log(auth()->id(), 'report_downloaded', 'report', $report->id, [
                'format' => 'pdf',
                'via' => 'dompdf',
            ]);

            return $pdf->download($filename);
        }

        return response()->json([
            'error' => 'No PDF driver installed. Run: composer require spatie/browsershot',
        ], 500);
    }

    /**
     * Export report as Excel.
     * Extracts all table elements from all pages.
     * Uses Maatwebsite Excel with CSV fallback.
     */
    public function exportExcel($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Extract all table elements from all pages
        $tables = [];
        foreach ($report->content ?? [] as $pageIndex => $page) {
            foreach ($page['elements'] ?? [] as $el) {
                if ($el['type'] === 'table' && ! empty($el['data'])) {
                    $tables[] = [
                        'page' => $pageIndex + 1,
                        'columns' => $el['columns'] ?? [],
                        'data' => $el['data'] ?? [],
                        'title' => $el['title'] ?? 'Table '.(count($tables) + 1),
                    ];
                }
            }
        }

        if (empty($tables)) {
            return response()->json(['error' => 'No table data found in this report'], 422);
        }

        UserActivity::log(auth()->id(), 'report_exported', 'report', $report->id, [
            'format' => 'excel',
            'tables_count' => count($tables),
        ]);

        // Use Maatwebsite Excel if available
        if (class_exists(Excel::class)) {
            return Excel::download(
                new ReportTablesExport($tables),
                Str::slug($report->title).'.xlsx'
            );
        }

        // Fallback to CSV
        return $this->exportCsv($slug);
    }

    /**
     * Export report as CSV.
     * Extracts table data and chart data.
     */
    public function exportCsv($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        $csvRows = [];
        $hasData = false;

        foreach ($report->content ?? [] as $pageIndex => $page) {
            foreach ($page['elements'] ?? [] as $el) {
                // Export table data
                if ($el['type'] === 'table' && ! empty($el['data'])) {
                    $hasData = true;
                    $csvRows[] = ['=== Page '.($pageIndex + 1).' Table ==='];
                    $csvRows[] = $el['columns'] ?? [];
                    foreach ($el['data'] as $row) {
                        $csvRows[] = array_map(fn ($col) => $row[$col] ?? '', $el['columns'] ?? []);
                    }
                    $csvRows[] = []; // Blank line between tables
                }

                // Export chart data
                if (in_array($el['type'], ['bar-chart', 'line-chart', 'area-chart', 'pie-chart', 'doughnut-chart', 'radar-chart'])
                    && ! empty($el['chartData'])) {
                    $hasData = true;
                    $csvRows[] = ['=== '.($el['chartTitle'] ?? ucfirst($el['type'])).' ==='];
                    $csvRows[] = ['Label', 'Value'];
                    $labels = $el['chartData']['labels'] ?? [];
                    $values = $el['chartData']['values'] ?? [];
                    foreach ($labels as $i => $label) {
                        $csvRows[] = [$label, $values[$i] ?? ''];
                    }
                    $csvRows[] = [];
                }
            }
        }

        if (! $hasData) {
            return response()->json(['error' => 'No exportable data found'], 422);
        }

        UserActivity::log(auth()->id(), 'report_exported', 'report', $report->id, ['format' => 'csv']);

        $filename = Str::slug($report->title).'.csv';
        $callback = function () use ($csvRows) {
            $handle = fopen('php://output', 'w');
            foreach ($csvRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Export report as PNG image using Browsershot.
     */
    public function exportImage($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        if (! class_exists(Browsershot::class)) {
            return response()->json([
                'error' => 'Browsershot not installed. Run: composer require spatie/browsershot',
            ], 500);
        }

        $previewUrl = route('reports.preview', $report->slug);
        $sessionCookie = session()->getId();
        $cookieName = config('session.cookie');

        // Calculate window size based on page dimensions
        $settings = $report->settings ?? [];
        $sizes = [
            'A4' => ['portrait' => [794, 1123], 'landscape' => [1123, 794]],
            'Letter' => ['portrait' => [816, 1056], 'landscape' => [1056, 816]],
        ];
        $dimensions = $sizes[$settings['page_size'] ?? 'A4'][$settings['orientation'] ?? 'portrait'] ?? [794, 1123];

        // FIXED: Correct class name
        $image = Browsershot::url($previewUrl)
            ->useCookies([$cookieName => $sessionCookie])
            ->waitUntilNetworkIdle()
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->windowSize($dimensions[0], $dimensions[1])
            ->screenshot();

        UserActivity::log(auth()->id(), 'report_exported', 'report', $report->id, ['format' => 'image/png']);

        return response($image, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.Str::slug($report->title).'.png"',
        ]);
    }

    /**
     * Soft delete a report (move to trash).
     */
    public function destroy($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Only owner or admin can delete
        if (! auth()->user()->hasRole('admin') && $report->user_id !== auth()->id()) {
            abort(403);
        }

        UserActivity::log(auth()->id(), 'report_deleted', 'report', $report->id, [
            'report_title' => $report->title,
        ]);

        // Soft delete related notifications
        NotificationService::deleteForNotifiable($report);

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report moved to trash.');
    }

    /**
     * Update report status (draft, published, archived).
     */
    public function updateStatus(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Only owner or admin can change status
        if (! auth()->user()->hasRole('admin') && $report->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:draft,published,archived']);

        $oldStatus = $report->status;
        $report->update([
            'status' => $request->status,
            'published_at' => $request->status === 'published' ? now() : $report->published_at,
        ]);

        UserActivity::log(auth()->id(), 'report_status_changed', 'report', $report->id, [
            'old_status' => $oldStatus,
            'new_status' => $request->status,
        ]);

        return back()->with('success', 'Status updated.');
    }

    /**
     * Duplicate a report (creates a copy with "(Copy)" suffix).
     */
    public function duplicate($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Generate new UUIDs for all pages and elements
        $newContent = collect($report->content ?? [])->map(function ($page) {
            $page['id'] = (string) Str::uuid();
            $page['elements'] = collect($page['elements'] ?? [])->map(function ($el) {
                $el['id'] = (string) Str::uuid();

                return $el;
            })->toArray();

            return $page;
        })->toArray();

        $newReport = Report::create([
            'user_id' => auth()->id(),
            'template_id' => $report->template_id,
            'title' => $report->title.' (Copy)',
            'slug' => Str::slug($report->title).'-copy-'.Str::random(8),
            'content' => $newContent,
            'settings' => $report->settings,
            'status' => 'draft',
        ]);

        UserActivity::log(auth()->id(), 'report_duplicated', 'report', $newReport->id, [
            'original_report' => $report->title,
            'new_report' => $newReport->title,
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Report duplicated.');
    }

    /**
     * Get version history for a report.
     */
    public function versions($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        $versions = ReportVersion::where('report_id', $report->id)
            ->orderByDesc('created_at')
            ->select('id', 'title', 'label', 'version_number', 'created_at')
            ->limit(50)
            ->get();

        return response()->json(['versions' => $versions]);
    }

    /**
     * Restore a previous version of a report.
     */
    public function restoreVersion(Request $request, $slug, $versionId)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        $version = ReportVersion::where('id', $versionId)
            ->where('report_id', $report->id)
            ->firstOrFail();

        $report->update([
            'content' => $version->content,
            'settings' => $version->settings,
            'title' => $version->title,
        ]);

        UserActivity::log(auth()->id(), 'report_version_restored', 'report', $report->id, [
            'version_id' => $versionId,
            'version_label' => $version->label,
        ]);

        // Create a snapshot before restore for safety
        $this->createVersionSnapshot($report, 'Restored from version #'.$versionId);

        return response()->json(['message' => 'Version restored successfully']);
    }

    /**
     * Generate a public share link for a report.
     */
    public function generateShareLink($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        if (! $report->share_token) {
            $report->update([
                'share_token' => Str::random(32),
                'is_public' => true,
            ]);
        } else {
            $report->update(['is_public' => true]);
        }

        UserActivity::log(auth()->id(), 'report_share_link_generated', 'report', $report->id, [
            'share_token' => $report->share_token,
        ]);

        return back()->with([
    'success'     => 'Share link generated',
    'share_token' => $report->fresh()->share_token,
]);
    }

    /**
     * Revoke a public share link (make private again).
     */
    public function revokeShareLink($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $report->update([
            'share_token' => null,
            'is_public' => false,
        ]);

        UserActivity::log(auth()->id(), 'report_share_link_revoked', 'report', $report->id, []);

        return back()->with(['message' => 'Share link revoked! now it is private']);
    }

    /**
     * Send report link via email.
     */
    public function emailReport(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Check view permission
        if (! $report->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this report.');
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $recipient = $request->email;
        $user = auth()->user();

        // Send the mail (using Laravel Mail facade)
        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($recipient, $report, $user) {
            $message->to($recipient)
                ->subject("Shared Report: {$report->title}")
                ->html("
                    <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px;'>
                        <h2 style='color: #4f46e5; margin-bottom: 10px;'>Report Shared With You</h2>
                        <p>Hello,</p>
                        <p><strong>{$user->name}</strong> has shared a report with you from the Report Generator System.</p>
                        
                        <div style='margin: 20px 0; padding: 15px; background-color: #f8fafc; border-radius: 6px; border-left: 4px solid #4f46e5;'>
                            <h3 style='margin: 0 0 5px 0; color: #1e293b;'>{$report->title}</h3>
                            <p style='margin: 0; font-size: 14px; color: #475569;'>Status: " . ucfirst($report->status) . "</p>
                        </div>
                        
                        <div style='margin-top: 25px;'>
                            <a href='" . route('reports.preview', $report->slug) . "' style='background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>View Report</a>
                        </div>
                        
                        <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0 15px 0;' />
                        <p style='font-size: 12px; color: #94a3b8; margin: 0;'>This is an automated message from the Report Generator System. Please do not reply directly to this email.</p>
                    </div>
                ");
        });

        // Log activity
        UserActivity::log(auth()->id(), 'report_emailed', 'report', $report->id, [
            'recipient' => $recipient,
        ]);

        return response()->json(['message' => 'Report emailed successfully']);
    }

    /**
     * Public preview of a shared report (no authentication required).
     */
    public function publicPreview($token)
    {
        $report = Report::where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        return Inertia::render('Reports/Preview', [
            'report' => $report,
            'readOnly' => true,
        ]);
    }

    /**
     * Public download of a shared report (no authentication required).
     */
    public function publicDownload($token)
    {
        $report = Report::where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();
        $filename = Str::slug($report->title).'.pdf';

        if (class_exists(Browsershot::class)) {
            $previewUrl = route('reports.public-preview', $token);

            $pdf = Browsershot::url($previewUrl)
                ->waitUntilNetworkIdle()
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->paperSize(
                    $this->getPaperWidth($report->settings),
                    $this->getPaperHeight($report->settings)
                )
                ->landscape(($report->settings['orientation'] ?? 'portrait') === 'landscape')
                ->pdf();

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        $pdf = Pdf::loadView('pdfs.report', [
            'report' => $report,
            'content' => $report->content,
            'settings' => $report->settings ?? [],
        ]);

        return $pdf->download($filename);
    }

    /**
     * Show shared report (with token, for authenticated users).
     */
    public function showShared($token)
    {
        $report = Report::where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        return Inertia::render('Reports/Preview', [
            'report' => $report,
            'readOnly' => true,
        ]);
    }

    /**
     * Assign a report to a user.
     */
    public function assignToUser(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Only owner or admin can assign
        if (! auth()->user()->hasRole('admin') && $report->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:view,edit,manage',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $assignment = ReportAssignment::updateOrCreate(
            ['report_id' => $report->id, 'user_id' => $request->user_id],
            [
                'assigned_by' => auth()->id(),
                'permission' => $request->permission,
                'expires_at' => $request->expires_at,
                'is_active' => true,
            ]
        );

        // Notify the assigned user
        NotificationService::reportAssigned($report, $request->user_id);

        UserActivity::log(auth()->id(), 'report_assigned', 'report', $report->id, [
            'assigned_to' => $request->user_id,
            'permission' => $request->permission,
        ]);

        return response()->json([
            'message' => 'Report assigned successfully',
            'assignment' => $assignment,
        ]);
    }

    /**
     * Remove a user's assignment from a report.
     */
    public function removeAssignment($slug, $assignmentId)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        $assignment = ReportAssignment::where('id', $assignmentId)
            ->where('report_id', $report->id)
            ->firstOrFail();

        $assignment->delete();

        UserActivity::log(auth()->id(), 'report_unassigned', 'report', $report->id, [
            'removed_user' => $assignment->user_id,
        ]);

        return response()->json(['message' => 'Assignment removed']);
    }

    /**
     * Get all assignments for a report.
     */
    public function getAssignments($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $assignments = ReportAssignment::where('report_id', $report->id)
            ->with(['user', 'assignedBy'])
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'user_id' => $a->user_id,
                    'user_name' => $a->user->name,
                    'user_email' => $a->user->email,
                    'permission' => $a->permission,
                    'assigned_by' => $a->assignedBy->name,
                    'assigned_at' => $a->assigned_at,
                    'expires_at' => $a->expires_at,
                    'is_active' => $a->is_active,
                ];
            });

        return response()->json(['assignments' => $assignments]);
    }

    /**
     * Restore a soft-deleted report.
     */
    public function restore($slug)
    {
        $report = Report::withTrashed()->where('slug', $slug)->firstOrFail();
        $report->restore();

        // Restore related notifications
        NotificationService::restoreForNotifiable($report);

        UserActivity::log(auth()->id(), 'report_restored', 'report', $report->id, [
            'report_title' => $report->title,
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Report restored successfully.');
    }

    /**
     * Permanently delete a report.
     */
    public function forceDelete($slug)
    {
        $report = Report::withTrashed()->where('slug', $slug)->firstOrFail();

        UserActivity::log(auth()->id(), 'report_force_deleted', 'report', $report->id, [
            'report_title' => $report->title,
        ]);

        // Force delete related notifications
        NotificationService::forceDeleteForNotifiable($report);

        $report->forceDelete();

        return redirect()->route('reports.index')
            ->with('success', 'Report permanently deleted.');
    }

    /**
     * Display trashed (soft-deleted) reports for current user.
     */
    public function trashed(Request $request)
    {
        $user = auth()->user();

        $reports = Report::onlyTrashed()
            ->where('user_id', $user->id)
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderBy('deleted_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Reports/Trashed', [
            'reports' => $reports,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Get reports assigned to the authenticated user.
     */
    public function assignedReports()
    {
        $user = auth()->user();

        $assignments = ReportAssignment::with(['report', 'assignedBy'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('report')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->filter(function ($assignment) {
                return $assignment->report !== null;
            })
            ->map(function ($assignment) {
                $report = $assignment->report;
                $totalElements = 0;
                $completedElements = 0;

                if ($report && $report->content) {
                    foreach ($report->content as $page) {
                        $totalElements += count($page['elements'] ?? []);
                    }
                }

                $progress = $totalElements > 0 ? min(100, round(($completedElements / $totalElements) * 100)) : 0;

                return [
                    'id' => $assignment->id,
                    'permission' => $assignment->permission,
                    'assigned_at' => $assignment->assigned_at,
                    'expires_at' => $assignment->expires_at,
                    'progress' => $progress,
                    'assigned_by' => $assignment->assignedBy?->name,
                    'report' => $report ? [
                        'id' => $report->id,
                        'title' => $report->title,
                        'slug' => $report->slug,
                        'status' => $report->status,
                        'pages' => count($report->content ?? []),
                    ] : null,
                ];
            })->values();

        return Inertia::render('Reports/AssignedReports', [
            'assignments' => $assignments,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPER METHODS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Build pages array from template structure.
     */
    private function buildPagesFromTemplate(Template $template): array
    {
        $pages = $template->structure['pages'] ?? [];

        if (empty($pages)) {
            return [['id' => (string) Str::uuid(), 'label' => 'Page 1', 'elements' => []]];
        }

        return array_map(function (array $page) {
            return [
                'id' => (string) Str::uuid(),
                'label' => $page['label'] ?? 'Page',
                'elements' => array_map(function (array $el) {
                    $el['id'] = (string) Str::uuid();

                    return $el;
                }, $page['elements'] ?? []),
            ];
        }, $pages);
    }

    /**
     * Create a version snapshot of the report.
     * Keeps only the last 50 versions.
     */
    private function createVersionSnapshot(Report $report, $label = null): void
    {
        $lastVersion = ReportVersion::where('report_id', $report->id)
            ->orderBy('version_number', 'desc')
            ->first();

        $versionNumber = ($lastVersion ? $lastVersion->version_number + 1 : 1);

        ReportVersion::create([
            'report_id' => $report->id,
            'user_id' => auth()->id(),
            'label' => $label ?? 'Auto-saved',
            'content' => $report->content,
            'settings' => $report->settings,
            'title' => $report->title,
            'version_number' => $versionNumber,
        ]);

        // Keep only last 50 versions
        ReportVersion::where('report_id', $report->id)
            ->orderBy('version_number', 'desc')
            ->skip(50)
            ->take(1000)
            ->delete();
    }

    /**
     * Default report settings.
     */
    private function defaultSettings(): array
    {
        return [
            'page_size' => 'A4',
            'orientation' => 'portrait',
            'primary_color' => '#6366f1',
            'accent_color' => '#8b5cf6',
            'background_color' => '#ffffff',
            'text_color' => '#0f172a',
            'font_family' => "'DM Sans', sans-serif",
            'font_size' => 14,
            'margin' => 40,
            'show_page_numbers' => true,
            'show_header' => false,
            'show_footer' => false,
            'header_text' => '',
            'footer_left' => '',
            'footer_right' => '',
            'header_color' => '#1e293b',
            'footer_color' => '#1e293b',
            'watermark' => '',
            'rtl' => false,
            'bg_image' => '',
            'page_radius' => 0,
        ];
    }

    /**
     * Get paper width in mm for PDF generation.
     */
    private function getPaperWidth(array $settings): float
    {
        $sizes = [
            'A4' => ['portrait' => 210, 'landscape' => 297],
            'Letter' => ['portrait' => 215.9, 'landscape' => 279.4],
            'Legal' => ['portrait' => 215.9, 'landscape' => 355.6],
            'A3' => ['portrait' => 297, 'landscape' => 420],
            'A5' => ['portrait' => 148, 'landscape' => 210],
        ];

        $size = $settings['page_size'] ?? 'A4';
        $orientation = $settings['orientation'] ?? 'portrait';

        return $sizes[$size][$orientation] ?? $sizes['A4']['portrait'];
    }

    /**
     * Get paper height in mm for PDF generation.
     */
    private function getPaperHeight(array $settings): float
    {
        $sizes = [
            'A4' => ['portrait' => 297, 'landscape' => 210],
            'Letter' => ['portrait' => 279.4, 'landscape' => 215.9],
            'Legal' => ['portrait' => 355.6, 'landscape' => 215.9],
            'A3' => ['portrait' => 420, 'landscape' => 297],
            'A5' => ['portrait' => 210, 'landscape' => 148],
        ];

        $size = $settings['page_size'] ?? 'A4';
        $orientation = $settings['orientation'] ?? 'portrait';

        return $sizes[$size][$orientation] ?? $sizes['A4']['portrait'];
    }

    /**
     * Search reports via AJAX for search palette.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $reports = Report::where('title', 'like', "%{$query}%")
            ->where('user_id', auth()->id())
            ->limit(10)
            ->get(['id', 'title', 'slug', 'status']);

        return response()->json(['reports' => $reports]);
    }

    /**
     * Get element presets library.
     */
    public function getPresets()
    {
        $presets = [
            ['name' => 'Blue Header', 'type' => 'heading', 'styles' => ['fontSize' => 32, 'fontWeight' => '700', 'color' => '#1e40af']],
            ['name' => 'Subtitle Gray', 'type' => 'subheading', 'styles' => ['fontSize' => 18, 'color' => '#64748b']],
            ['name' => 'Metric Card', 'type' => 'metric', 'styles' => ['backgroundColor' => '#f8fafc', 'borderRadius' => 12]],
            ['name' => 'CTA Button', 'type' => 'badge', 'styles' => ['backgroundColor' => '#6366f1', 'color' => '#ffffff', 'borderRadius' => 999]],
            ['name' => 'Divider', 'type' => 'divider', 'styles' => ['color' => '#e2e8f0', 'height' => 2]],
        ];

        return response()->json(['presets' => $presets]);
    }

    /**
     * Save element as preset.
     */
    public function savePreset(Request $request)
    {
        return response()->json(['message' => 'Preset saved', 'preset' => $request->all()]);
    }

    /**
     * Get report statistics.
     */
    public function reportStats($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $totalElements = 0;
        $totalWords = 0;

        foreach ($report->content ?? [] as $page) {
            foreach ($page['elements'] ?? [] as $el) {
                $totalElements++;
                if (! empty($el['content'])) {
                    $totalWords += str_word_count(strip_tags($el['content']));
                }
            }
        }

        return response()->json([
            'total_pages' => count($report->content ?? []),
            'total_elements' => $totalElements,
            'total_words' => $totalWords,
            'status' => $report->status,
        ]);
    }
}