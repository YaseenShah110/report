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
    public function index()
    {
        $reports = Report::where('user_id', auth()->id())
            ->with('template')
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        $stats = [
            'total'     => Report::where('user_id', auth()->id())->count(),
            'published' => Report::where('user_id', auth()->id())->where('status', 'published')->count(),
            'draft'     => Report::where('user_id', auth()->id())->where('status', 'draft')->count(),
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

        // Build initial pages from template structure or blank page
        if ($request->filled('template_id')) {
            $template = Template::findOrFail($request->template_id);
            $pages    = $this->buildPagesFromTemplate($template);
            $settings = $template->settings ?? $this->defaultSettings();
        } else {
            $pages    = [['id' => (string) Str::uuid(), 'label' => 'Page 1', 'elements' => []]];
            $settings = $this->defaultSettings();
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
            $report->settings['page_size'] ?? 'A4',
            $report->settings['orientation'] ?? 'portrait'
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

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    /**
     * Convert a template's structure.pages array into the report content format,
     * giving every element a fresh UUID so edits don't bleed back to the template.
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
                    $el['id'] = (string) Str::uuid(); // fresh id per element
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
            'background_color'  => '#ffffff',
            'font_family'       => "'DM Sans', sans-serif",
            'margin'            => 40,
            'show_page_numbers' => true,
            'footer_left'       => '',
            'footer_right'      => '',
        ];
    }
}