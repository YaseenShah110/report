<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::where('user_id', auth()->id())->with('template');

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sort = $request->get('sort', 'updated_at');
        if ($sort === 'title') {
            $query->orderBy('title');
        } else {
            $query->latest($sort);
        }

        $reports = $query->paginate(12)->withQueryString();

        $stats = [
            'total'     => Report::where('user_id', auth()->id())->count(),
            'published' => Report::where('user_id', auth()->id())->where('status', 'published')->count(),
            'draft'     => Report::where('user_id', auth()->id())->where('status', 'draft')->count(),
            'archived'  => Report::where('user_id', auth()->id())->where('status', 'archived')->count(),
        ];

        return Inertia::render('Reports/Index', compact('reports', 'stats'));
    }

    public function create()
    {
        $templates = Template::where('is_active', true)->get();
        return Inertia::render('Reports/Create', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        // Merge initial_settings if provided (from Create page)
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
            'slug'        => Str::slug($request->title) . '-' . Str::random(6),
            'content'     => $pages,
            'settings'    => $settings,
            'status'      => 'draft',
        ]);

        return redirect()->route('reports.edit', $report->slug)
            ->with('success', 'Report created successfully!');
    }

    public function edit($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Reports/Editor', ['report' => $report]);
    }

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

        return response()->json(['message' => 'Saved', 'updated_at' => $report->fresh()->updated_at]);
    }

    public function preview($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Reports/Preview', ['report' => $report]);
    }

    public function download($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $pdf = Pdf::loadView('pdfs.report', [
            'report'   => $report,
            'content'  => $report->content,
            'settings' => $report->settings ?? [],
        ]);

        $pdf->setPaper(
            $report->settings['page_size']    ?? 'A4',
            $report->settings['orientation']  ?? 'portrait'
        );

        return $pdf->download(Str::slug($report->title) . '.pdf');
    }

    public function destroy($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Report deleted.');
    }

    /**
     * Update report status (draft / published / archived)
     */
    public function updateStatus(Request $request, $slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:draft,published,archived',
        ]);

        $report->update([
            'status'       => $request->status,
            'published_at' => $request->status === 'published' ? now() : $report->published_at,
        ]);

        return back()->with('success', 'Report status updated.');
    }

    /**
     * Duplicate a report
     */
    public function duplicate($slug)
    {
        $report = Report::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Give every element a fresh UUID so edits are independent
        $newContent = collect($report->content ?? [])->map(function ($page) {
            $page['id']       = (string) Str::uuid();
            $page['label']    = $page['label'] ?? 'Page';
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
            'slug'        => Str::slug($report->title) . '-copy-' . Str::random(6),
            'content'     => $newContent,
            'settings'    => $report->settings,
            'status'      => 'draft',
        ]);

        return redirect()->route('reports.index')->with('success', 'Report duplicated.');
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

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
}