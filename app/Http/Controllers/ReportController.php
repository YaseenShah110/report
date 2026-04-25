<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  LIST
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Report::where('user_id', auth()->id())->with('template');

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
    //  CREATE
    // ─────────────────────────────────────────────────────────────
    public function create()
    {
        $templates = Template::where('is_active', true)->get();
        return Inertia::render('Reports/Create', compact('templates'));
    }

    // ─────────────────────────────────────────────────────────────
    //  STORE
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
            // FIX: use Str::random(8) instead of uniqid() to avoid collisions
            'slug'        => Str::slug($request->title) . '-' . Str::random(8),
            'content'     => $pages,
            'settings'    => $settings,
            'status'      => 'draft',
        ]);

        return redirect()->route('reports.edit', $report->slug)
            ->with('success', 'Report created!');
    }

    // ─────────────────────────────────────────────────────────────
    //  EDIT
    // ─────────────────────────────────────────────────────────────
    public function edit($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Reports/Editor', ['report' => $report]);
    }

    // ─────────────────────────────────────────────────────────────
    //  UPDATE (auto-save)
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|array',
            'settings' => 'required|array',
        ]);

        $report->update([
            'title'    => $request->title,
            'content'  => $request->content,
            'settings' => $request->settings,
        ]);

        // Auto-save a version snapshot every 5 minutes
        // (only if report_versions table exists — see migration fix)
        if (\Schema::hasTable('report_versions')) {
            $lastVersion = \DB::table('report_versions')
                ->where('report_id', $report->id)
                ->orderByDesc('created_at')
                ->first();

            $shouldSave = !$lastVersion
                || now()->diffInMinutes($lastVersion->created_at) >= 5;

            if ($shouldSave) {
                \DB::table('report_versions')->insert([
                    'report_id'  => $report->id,
                    'content'    => json_encode($request->content),
                    'settings'   => json_encode($request->settings),
                    'title'      => $request->title,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Keep only last 20 versions per report
                $versionIds = \DB::table('report_versions')
                    ->where('report_id', $report->id)
                    ->orderByDesc('created_at')
                    ->skip(20)
                    ->take(9999)
                    ->pluck('id');

                if ($versionIds->count()) {
                    \DB::table('report_versions')->whereIn('id', $versionIds)->delete();
                }
            }
        }

        return response()->json([
            'message'    => 'Saved',
            'updated_at' => $report->fresh()->updated_at,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  PREVIEW
    // ─────────────────────────────────────────────────────────────
    public function preview($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Reports/Preview', ['report' => $report]);
    }

    // ─────────────────────────────────────────────────────────────
    //  DOWNLOAD PDF
    //  FIX: Use Browsershot if available, fallback to DomPDF.
    //  Install: composer require spatie/browsershot
    //  Requires: Node.js + Puppeteer on server
    //  Install Puppeteer: npm install puppeteer (in project root)
    // ─────────────────────────────────────────────────────────────
    public function download($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $filename = Str::slug($report->title) . '.pdf';

        // ── Option A: Browsershot (RECOMMENDED — renders charts + fonts perfectly) ──
        if (class_exists(\Spatie\Browsershot\Browsershot::class)) {
            // Build the full URL to the preview page (Browsershot needs a real URL)
            $previewUrl = route('reports.preview', $report->slug);

            // Add auth cookie so Browsershot can access the authenticated page
            $sessionCookie = session()->getId();
            $cookieName    = config('session.cookie');

            $pdf = \Spatie\Browsershot\Browsershot::url($previewUrl)
                ->useCookies([
                    $cookieName => $sessionCookie,
                ])
                ->waitUntilNetworkIdle()        // wait for all Chart.js to render
                ->dismissDialogs()
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->paperSize(
                    $this->getPaperWidth($report->settings),
                    $this->getPaperHeight($report->settings)
                )
                ->landscape($report->settings['orientation'] === 'landscape')
                ->margins(0, 0, 0, 0)
                ->pdf();

            return response($pdf, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // ── Option B: DomPDF fallback (charts won't render, but text/shapes will) ──
        // Install: composer require barryvdh/laravel-dompdf
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

            return $pdf->download($filename);
        }

        return response()->json(['error' => 'No PDF driver installed. Run: composer require spatie/browsershot'], 500);
    }

    // ─────────────────────────────────────────────────────────────
    //  EXPORT EXCEL
    //  FIX: This method was missing — caused 500 error
    //  Install: composer require maatwebsite/excel
    // ─────────────────────────────────────────────────────────────
    public function exportExcel($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Extract all table elements from all pages
        $tables = [];
        foreach ($report->content ?? [] as $pageIndex => $page) {
            foreach ($page['elements'] ?? [] as $el) {
                if ($el['type'] === 'table' && !empty($el['data'])) {
                    $tables[] = [
                        'page'    => $pageIndex + 1,
                        'columns' => $el['columns'] ?? [],
                        'data'    => $el['data'] ?? [],
                    ];
                }
            }
        }

        if (empty($tables)) {
            return response()->json(['error' => 'No table data found in this report'], 422);
        }

        // If maatwebsite/excel is installed
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ReportTablesExport($tables),
                Str::slug($report->title) . '.xlsx'
            );
        }

        // Fallback: generate a simple CSV-in-xlsx manually
        return $this->generateSimpleExcel($report, $tables);
    }

    // ─────────────────────────────────────────────────────────────
    //  EXPORT CSV
    //  FIX: This method was missing — caused 500 error
    // ─────────────────────────────────────────────────────────────
    public function exportCsv($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $csvRows  = [];
        $hasData  = false;

        foreach ($report->content ?? [] as $pageIndex => $page) {
            foreach ($page['elements'] ?? [] as $el) {
                if ($el['type'] === 'table' && !empty($el['data'])) {
                    $hasData = true;
                    // Section header
                    $csvRows[] = ['=== Page ' . ($pageIndex + 1) . ' Table ==='];
                    // Column headers
                    $csvRows[] = $el['columns'] ?? [];
                    // Data rows
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
    //  FIX: This method was missing — caused 500 error
    // ─────────────────────────────────────────────────────────────
    public function exportImage($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!class_exists(\Spatie\Browsershot\Browsershot::class)) {
            return response()->json([
                'error' => 'Browsershot not installed. Run: composer require spatie/browsershot',
            ], 500);
        }

        $previewUrl  = route('reports.preview', $report->slug);
        $sessionCookie = session()->getId();
        $cookieName    = config('session.cookie');

        $image = \Spatie\Browsershot\Browsershot::url($previewUrl)
            ->useCookies([$cookieName => $sessionCookie])
            ->waitUntilNetworkIdle()
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->windowSize(1200, 1600)
            ->screenshot();

        return response($image, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($report->title) . '.png"',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  DESTROY
    // ─────────────────────────────────────────────────────────────
    public function destroy($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Report deleted.');
    }

    // ─────────────────────────────────────────────────────────────
    //  STATUS UPDATE
    // ─────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate(['status' => 'required|in:draft,published,archived']);

        $report->update([
            'status'       => $request->status,
            'published_at' => $request->status === 'published' ? now() : $report->published_at,
        ]);

        return back()->with('success', 'Status updated.');
    }

    // ─────────────────────────────────────────────────────────────
    //  DUPLICATE
    // ─────────────────────────────────────────────────────────────
    public function duplicate($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $newContent = collect($report->content ?? [])->map(function ($page) {
            $page['id']       = (string) Str::uuid();
            $page['elements'] = collect($page['elements'] ?? [])->map(function ($el) {
                $el['id'] = (string) Str::uuid();
                return $el;
            })->toArray();
            return $page;
        })->toArray();

        Report::create([
            'user_id'     => auth()->id(),
            'template_id' => $report->template_id,
            'title'       => $report->title . ' (Copy)',
            'slug'        => Str::slug($report->title) . '-copy-' . Str::random(8),
            'content'     => $newContent,
            'settings'    => $report->settings,
            'status'      => 'draft',
        ]);

        return redirect()->route('reports.index')->with('success', 'Report duplicated.');
    }

    // ─────────────────────────────────────────────────────────────
    //  VERSION HISTORY  (NEW — requires report_versions table)
    // ─────────────────────────────────────────────────────────────
    public function versions($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!\Schema::hasTable('report_versions')) {
            return response()->json(['versions' => []]);
        }

        $versions = \DB::table('report_versions')
            ->where('report_id', $report->id)
            ->orderByDesc('created_at')
            ->select('id', 'title', 'created_at')
            ->limit(20)
            ->get();

        return response()->json(['versions' => $versions]);
    }

    public function restoreVersion($slug, $versionId)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $version = \DB::table('report_versions')
            ->where('id', $versionId)
            ->where('report_id', $report->id)
            ->firstOrFail();

        $report->update([
            'content'  => json_decode($version->content, true),
            'settings' => json_decode($version->settings, true),
            'title'    => $version->title,
        ]);

        return response()->json(['message' => 'Version restored']);
    }

    // ─────────────────────────────────────────────────────────────
    //  PUBLIC SHARE  (NEW — requires share_token column)
    // ─────────────────────────────────────────────────────────────
    public function generateShareLink($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$report->share_token) {
            $report->update(['share_token' => Str::random(32)]);
        }

        return response()->json([
            'url'   => route('reports.shared', $report->share_token),
            'token' => $report->share_token,
        ]);
    }

    public function revokeShareLink($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $report->update(['share_token' => null]);

        return response()->json(['message' => 'Share link revoked']);
    }

    public function showShared($token)
    {
        $report = Report::where('share_token', $token)->firstOrFail();

        return Inertia::render('Reports/Preview', [
            'report'   => $report,
            'readOnly' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────
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

    // Paper sizes in mm for Browsershot
    private function getPaperWidth(array $settings): float
    {
        $sizes = [
            'A4'     => 210,
            'A3'     => 297,
            'A5'     => 148,
            'Letter' => 215.9,
            'Legal'  => 215.9,
        ];
        $size = $settings['page_size'] ?? 'A4';
        $w    = $sizes[$size] ?? 210;
        return ($settings['orientation'] ?? 'portrait') === 'landscape' ? ($sizes[$size . '_h'] ?? $w) : $w;
    }

    private function getPaperHeight(array $settings): float
    {
        $sizes = [
            'A4'     => 297,
            'A3'     => 420,
            'A5'     => 210,
            'Letter' => 279.4,
            'Legal'  => 355.6,
        ];
        $size = $settings['page_size'] ?? 'A4';
        $h    = $sizes[$size] ?? 297;
        return ($settings['orientation'] ?? 'portrait') === 'landscape' ? ($sizes[$size . '_w'] ?? $h) : $h;
    }

    private function generateSimpleExcel(Report $report, array $tables): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Simple CSV fallback when maatwebsite/excel not installed
        return $this->exportCsv($report->slug);
    }
}