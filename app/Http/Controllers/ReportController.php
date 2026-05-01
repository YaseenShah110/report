<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportVersion;
use App\Models\Template;
use App\Models\UserActivity;
use App\Models\ReportAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  LIST REPORTS
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get reports that user owns OR has been assigned to
        $query = Report::with('template')
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('assignments', function($sq) use ($user) {
                      $sq->where('user_id', $user->id)
                         ->where('is_active', true)
                         ->where(function($exp) {
                             $exp->whereNull('expires_at')->orWhere('expires_at', '>', now());
                         });
                  });
            });

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->get('sort', 'updated_at');
        $query->orderBy($sort === 'title' ? 'title' : $sort, $sort === 'title' ? 'asc' : 'desc');

        $reports = $query->paginate(12)->withQueryString();

        $stats = [
            'total'     => Report::where('user_id', auth()->id())->count(),
            'published' => Report::where('user_id', auth()->id())->where('status', 'published')->count(),
            'draft'     => Report::where('user_id', auth()->id())->where('status', 'draft')->count(),
            'archived'  => Report::where('user_id', auth()->id())->where('status', 'archived')->count(),
        ];

        return Inertia::render('Reports/Index', compact('reports', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────
    //  CREATE REPORT FORM
    // ─────────────────────────────────────────────────────────────
    public function create()
    {
        $templates = Template::where('is_active', true)->get();
        return Inertia::render('Reports/Create', compact('templates'));
    }

    // ─────────────────────────────────────────────────────────────
    //  STORE NEW REPORT
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        $initialSettings = $request->get('initial_settings', []);

        if ($request->filled('template_id')) {
            $template = Template::findOrFail($request->template_id);
            $pages    = $this->buildPagesFromTemplate($template);
            $settings = array_merge($template->settings ?? $this->defaultSettings(), $initialSettings);
        } else {
            $pages    = [['id' => (string) Str::uuid(), 'label' => 'Page 1', 'elements' => []]];
            $settings = array_merge($this->defaultSettings(), $initialSettings);
        }

        $report = Report::create([
            'user_id'     => auth()->id(),
            'template_id' => $request->template_id,
            'title'       => $request->title,
            'slug'        => Str::slug($request->title) . '-' . Str::random(8),
            'content'     => $pages,
            'settings'    => $settings,
            'status'      => 'draft',
        ]);

        // Log activity
        UserActivity::log(auth()->id(), 'report_created', 'report', $report->id, [
            'report_title' => $report->title,
            'template_used' => $request->template_id ? 'yes' : 'no'
        ]);

        // Create initial version
        $this->createVersionSnapshot($report, 'Initial version');

        return redirect()->route('reports.edit', $report->slug)
            ->with('success', 'Report created!');
    }

    // ─────────────────────────────────────────────────────────────
    //  EDIT REPORT
    // ─────────────────────────────────────────────────────────────
    public function edit($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        
        // Check if user has edit permission via assignment
        $user = auth()->user();
        if ($user->id !== $report->user_id && !$user->hasRole('admin')) {
            $assignment = ReportAssignment::where('report_id', $report->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();
            
            if (!$assignment || !in_array($assignment->permission, ['edit', 'manage'])) {
                abort(403, 'You do not have permission to edit this report.');
            }
        }

        return Inertia::render('Reports/Editor', ['report' => $report]);
    }

    // ─────────────────────────────────────────────────────────────
    //  UPDATE REPORT (Auto-save from editor)
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|array',
            'settings' => 'required|array',
        ]);

        $oldContent = $report->content;
        $oldSettings = $report->settings;

        $report->update([
            'title'    => $request->title,
            'content'  => $request->content,
            'settings' => $request->settings,
        ]);

        // Create version snapshot if significant changes
        $contentChanged = md5(json_encode($oldContent)) !== md5(json_encode($request->content));
        $settingsChanged = md5(json_encode($oldSettings)) !== md5(json_encode($request->settings));
        
        if ($contentChanged || $settingsChanged) {
            // Check if last version was > 5 minutes ago
            $lastVersion = ReportVersion::where('report_id', $report->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$lastVersion || now()->diffInMinutes($lastVersion->created_at) >= 5) {
                $this->createVersionSnapshot($report, 'Auto-saved');
            }
        }

        UserActivity::log(auth()->id(), 'report_updated', 'report', $report->id, [
            'report_title' => $report->title,
            'content_changed' => $contentChanged,
            'settings_changed' => $settingsChanged
        ]);

        return response()->json([
            'message'    => 'Saved',
            'updated_at' => $report->fresh()->updated_at,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  PREVIEW REPORT
    // ─────────────────────────────────────────────────────────────
    public function preview($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        
        // Check if user has view permission
        $user = auth()->user();
        if ($user->id !== $report->user_id && !$user->hasRole('admin')) {
            $hasAccess = ReportAssignment::where('report_id', $report->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();
            
            if (!$hasAccess && !$report->is_public) {
                abort(403, 'You do not have permission to view this report.');
            }
        }

        return Inertia::render('Reports/Preview', ['report' => $report]);
    }

    // ─────────────────────────────────────────────────────────────
    //  DOWNLOAD PDF
    // ─────────────────────────────────────────────────────────────
    public function download($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $filename = Str::slug($report->title) . '.pdf';

        // Option A: Browsershot (recommended)
        if (class_exists(\Spatie\Browsershot\Browsershot::class)) {
            $previewUrl = route('reports.preview', $report->slug);
            $sessionCookie = session()->getId();
            $cookieName = config('session.cookie');

            $pdf = \Spatie\Browsershot\Browsersshot::url($previewUrl)
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

            // Log download activity
            UserActivity::log(auth()->id(), 'report_downloaded', 'report', $report->id, [
                'format' => 'pdf',
                'via' => 'browsershot'
            ]);

            return response($pdf, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Option B: DomPDF fallback
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.report', [
                'report'   => $report,
                'content'  => $report->content,
                'settings' => $report->settings ?? [],
            ]);

            $pdf->setPaper(
                $report->settings['page_size']   ?? 'A4',
                $report->settings['orientation'] ?? 'portrait'
            );

            UserActivity::log(auth()->id(), 'report_downloaded', 'report', $report->id, [
                'format' => 'pdf',
                'via' => 'dompdf'
            ]);

            return $pdf->download($filename);
        }

        return response()->json(['error' => 'No PDF driver installed. Run: composer require spatie/browsershot'], 500);
    }

    // ─────────────────────────────────────────────────────────────
    //  EXPORT EXCEL
    // ─────────────────────────────────────────────────────────────
    public function exportExcel($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        // Extract all table elements from all pages
        $tables = [];
        foreach ($report->content ?? [] as $pageIndex => $page) {
            foreach ($page['elements'] ?? [] as $el) {
                if ($el['type'] === 'table' && !empty($el['data'])) {
                    $tables[] = [
                        'page'    => $pageIndex + 1,
                        'columns' => $el['columns'] ?? [],
                        'data'    => $el['data'] ?? [],
                        'title'   => $el['title'] ?? "Table " . (count($tables) + 1),
                    ];
                }
            }
        }

        if (empty($tables)) {
            return response()->json(['error' => 'No table data found in this report'], 422);
        }

        UserActivity::log(auth()->id(), 'report_exported', 'report', $report->id, [
            'format' => 'excel',
            'tables_count' => count($tables)
        ]);

        // If maatwebsite/excel is installed
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ReportTablesExport($tables),
                Str::slug($report->title) . '.xlsx'
            );
        }

        // Fallback CSV export
        return $this->exportCsv($slug);
    }

    // ─────────────────────────────────────────────────────────────
    //  EXPORT CSV
    // ─────────────────────────────────────────────────────────────
    public function exportCsv($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();
        
        $csvRows  = [];
        $hasData  = false;

        foreach ($report->content ?? [] as $pageIndex => $page) {
            foreach ($page['elements'] ?? [] as $el) {
                if ($el['type'] === 'table' && !empty($el['data'])) {
                    $hasData = true;
                    $csvRows[] = ['=== Page ' . ($pageIndex + 1) . ' Table ==='];
                    $csvRows[] = $el['columns'] ?? [];
                    foreach ($el['data'] as $row) {
                        $csvRows[] = array_map(fn($col) => $row[$col] ?? '', $el['columns'] ?? []);
                    }
                    $csvRows[] = []; // blank line between tables
                }

                // Also export chart data
                if (in_array($el['type'], ['bar-chart','line-chart','area-chart','pie-chart','doughnut-chart','radar-chart'])
                    && !empty($el['chartData'])) {
                    $hasData = true;
                    $csvRows[] = ['=== ' . ($el['chartTitle'] ?? ucfirst($el['type'])) . ' ==='];
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

        if (!$hasData) {
            return response()->json(['error' => 'No exportable data found in this report'], 422);
        }

        UserActivity::log(auth()->id(), 'report_exported', 'report', $report->id, [
            'format' => 'csv'
        ]);

        $filename = Str::slug($report->title) . '.csv';

        $callback = function () use ($csvRows) {
            $handle = fopen('php://output', 'w');
            foreach ($csvRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  EXPORT IMAGE (PNG via Browsershot)
    // ─────────────────────────────────────────────────────────────
    public function exportImage($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        if (!class_exists(\Spatie\Browsershot\Browsershot::class)) {
            return response()->json([
                'error' => 'Browsershot not installed. Run: composer require spatie/browsershot',
            ], 500);
        }

        $previewUrl = route('reports.preview', $report->slug);
        $sessionCookie = session()->getId();
        $cookieName = config('session.cookie');

        // Set window size based on page dimensions
        $settings = $report->settings ?? [];
        $sizes = [
            'A4' => ['portrait' => [794, 1123], 'landscape' => [1123, 794]],
            'Letter' => ['portrait' => [816, 1056], 'landscape' => [1056, 816]],
        ];
        $dimensions = $sizes[$settings['page_size'] ?? 'A4'][$settings['orientation'] ?? 'portrait'] ?? [794, 1123];
        
        $image = \Spatie\Browsershot\Browsershot::url($previewUrl)
            ->useCookies([$cookieName => $sessionCookie])
            ->waitUntilNetworkIdle()
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->windowSize($dimensions[0], $dimensions[1])
            ->screenshot();

        UserActivity::log(auth()->id(), 'report_exported', 'report', $report->id, [
            'format' => 'image/png'
        ]);

        return response($image, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($report->title) . '.png"',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  DESTROY REPORT
    // ─────────────────────────────────────────────────────────────
    public function destroy($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        UserActivity::log(auth()->id(), 'report_deleted', 'report', $report->id, [
            'report_title' => $report->title
        ]);

        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Report deleted.');
    }

    // ─────────────────────────────────────────────────────────────
    //  UPDATE STATUS (publish, archive, draft)
    // ─────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $request->validate(['status' => 'required|in:draft,published,archived']);

        $report->update([
            'status'       => $request->status,
            'published_at' => $request->status === 'published' ? now() : $report->published_at,
        ]);

        UserActivity::log(auth()->id(), 'report_status_changed', 'report', $report->id, [
            'old_status' => $report->getOriginal('status'),
            'new_status' => $request->status
        ]);

        return back()->with('success', 'Status updated.');
    }

    // ─────────────────────────────────────────────────────────────
    //  DUPLICATE REPORT
    // ─────────────────────────────────────────────────────────────
    public function duplicate($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $newContent = collect($report->content ?? [])->map(function ($page) {
            $page['id']       = (string) Str::uuid();
            $page['elements'] = collect($page['elements'] ?? [])->map(function ($el) {
                $el['id'] = (string) Str::uuid();
                return $el;
            })->toArray();
            return $page;
        })->toArray();

        $newReport = Report::create([
            'user_id'     => auth()->id(),
            'template_id' => $report->template_id,
            'title'       => $report->title . ' (Copy)',
            'slug'        => Str::slug($report->title) . '-copy-' . Str::random(8),
            'content'     => $newContent,
            'settings'    => $report->settings,
            'status'      => 'draft',
        ]);

        UserActivity::log(auth()->id(), 'report_duplicated', 'report', $newReport->id, [
            'original_report' => $report->title,
            'new_report' => $newReport->title
        ]);

        return redirect()->route('reports.index')->with('success', 'Report duplicated.');
    }

    // ─────────────────────────────────────────────────────────────
    //  VERSION HISTORY
    // ─────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────
    //  RESTORE VERSION
    // ─────────────────────────────────────────────────────────────
    public function restoreVersion(Request $request, $slug, $versionId)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $version = ReportVersion::where('id', $versionId)
            ->where('report_id', $report->id)
            ->firstOrFail();

        $report->update([
            'content'  => $version->content,
            'settings' => $version->settings,
            'title'    => $version->title,
        ]);

        UserActivity::log(auth()->id(), 'report_version_restored', 'report', $report->id, [
            'version_id' => $versionId,
            'version_label' => $version->label
        ]);

        // Create a new version from current state before restore
        $this->createVersionSnapshot($report, 'Restored from version #' . $versionId);

        return response()->json(['message' => 'Version restored successfully']);
    }

    // ─────────────────────────────────────────────────────────────
    //  GENERATE SHARE LINK
    // ─────────────────────────────────────────────────────────────
    public function generateShareLink($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        if (!$report->share_token) {
            $report->update([
                'share_token' => Str::random(32),
                'is_public' => true,
            ]);
        } else {
            $report->update(['is_public' => true]);
        }

        UserActivity::log(auth()->id(), 'report_share_link_generated', 'report', $report->id, [
            'share_token' => $report->share_token
        ]);

        return response()->json([
            'url'   => route('reports.public-preview', $report->share_token),
            'token' => $report->share_token,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  REVOKE SHARE LINK
    // ─────────────────────────────────────────────────────────────
    public function revokeShareLink($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $report->update([
            'share_token' => null,
            'is_public' => false,
        ]);

        UserActivity::log(auth()->id(), 'report_share_link_revoked', 'report', $report->id, []);

        return response()->json(['message' => 'Share link revoked']);
    }

    // ─────────────────────────────────────────────────────────────
    //  PUBLIC PREVIEW (no authentication required)
    // ─────────────────────────────────────────────────────────────
    public function publicPreview($token)
    {
        $report = Report::where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        return Inertia::render('Reports/Preview', [
            'report'   => $report,
            'readOnly' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  PUBLIC DOWNLOAD (no authentication required)
    // ─────────────────────────────────────────────────────────────
    public function publicDownload($token)
    {
        $report = Report::where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        $filename = Str::slug($report->title) . '.pdf';

        if (class_exists(\Spatie\Browsershot\Browsershot::class)) {
            $previewUrl = route('reports.public-preview', $token);
            
            $pdf = \Spatie\Browsershot\Browsershot::url($previewUrl)
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
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Fallback to DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.report', [
            'report' => $report,
            'content' => $report->content,
            'settings' => $report->settings ?? [],
        ]);

        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET SHARED REPORT (with token)
    // ─────────────────────────────────────────────────────────────
    public function showShared($token)
    {
        $report = Report::where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        return Inertia::render('Reports/Preview', [
            'report'   => $report,
            'readOnly' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  ASSIGN REPORT TO USER (Admin/Manager)
    // ─────────────────────────────────────────────────────────────
    public function assignToUser(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:view,edit,manage',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $assignment = ReportAssignment::updateOrCreate(
            [
                'report_id' => $report->id,
                'user_id' => $request->user_id,
            ],
            [
                'assigned_by' => auth()->id(),
                'permission' => $request->permission,
                'expires_at' => $request->expires_at,
                'is_active' => true,
            ]
        );

        UserActivity::log(auth()->id(), 'report_assigned', 'report', $report->id, [
            'assigned_to' => $request->user_id,
            'permission' => $request->permission
        ]);

        return response()->json([
            'message' => 'Report assigned successfully',
            'assignment' => $assignment
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  REMOVE ASSIGNMENT
    // ─────────────────────────────────────────────────────────────
    public function removeAssignment($slug, $assignmentId)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $assignment = ReportAssignment::where('id', $assignmentId)
            ->where('report_id', $report->id)
            ->firstOrFail();

        $assignment->delete();

        UserActivity::log(auth()->id(), 'report_unassigned', 'report', $report->id, [
            'removed_user' => $assignment->user_id
        ]);

        return response()->json(['message' => 'Assignment removed']);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET REPORT ASSIGNMENTS
    // ─────────────────────────────────────────────────────────────
    public function getAssignments($slug)
    {
        $report = Report::where('slug', $slug)->firstOrFail();

        $assignments = ReportAssignment::where('report_id', $report->id)
            ->with(['user', 'assignedBy'])
            ->get()
            ->map(function($a) {
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

    // ─────────────────────────────────────────────────────────────
    //  PRIVATE HELPER METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Build pages array from template structure
     */
    private function buildPagesFromTemplate(Template $template): array
    {
        $pages = $template->structure['pages'] ?? [];

        if (empty($pages)) {
            return [['id' => (string) Str::uuid(), 'label' => 'Page 1', 'elements' => []]];
        }

        return array_map(function (array $page) {
            return [
                'id'       => (string) Str::uuid(),
                'label'    => $page['label'] ?? 'Page',
                'elements' => array_map(function (array $el) {
                    $el['id'] = (string) Str::uuid();
                    return $el;
                }, $page['elements'] ?? []),
            ];
        }, $pages);
    }

    /**
     * Create a version snapshot of the report
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
     * Default report settings
     */
    private function defaultSettings(): array
    {
        return [
            'page_size'         => 'A4',
            'orientation'       => 'portrait',
            'primary_color'     => '#6366f1',
            'accent_color'      => '#8b5cf6',
            'background_color'  => '#ffffff',
            'text_color'        => '#0f172a',
            'font_family'       => "'DM Sans', sans-serif",
            'font_size'         => 14,
            'margin'            => 40,
            'show_page_numbers' => true,
            'show_header'       => false,
            'show_footer'       => false,
            'header_text'       => '',
            'footer_left'       => '',
            'footer_right'      => '',
            'header_color'      => '#1e293b',
            'footer_color'      => '#1e293b',
            'watermark'         => '',
            'rtl'               => false,
            'bg_image'          => '',
            'page_radius'       => 0,
        ];
    }

    /**
     * Get paper width in mm for PDF
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
     * Get paper height in mm for PDF
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
 * Get reports assigned to the authenticated user
 */
public function assignedReports()
{
    $user = auth()->user();
    
    $assignments = ReportAssignment::with(['report', 'assignedBy'])
        ->where('user_id', $user->id)
        ->where('is_active', true)
        ->where(function($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })
        ->get()
        ->map(function($assignment) {
            // Calculate progress based on report completion (customize as needed)
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
        });
    
    return Inertia::render('Reports/AssignedReports', [
        'assignments' => $assignments
    ]);
}
}