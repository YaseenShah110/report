<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Template Controller
 * 
 * Handles template CRUD operations.
 * Templates are pre-designed report structures that users can start from.
 * 
 * Access: All authenticated users can view templates.
 *         Only admin users can create/update/delete templates.
 */
class TemplateController extends Controller
{
    /**
     * Display a listing of active templates.
     * Users see this to choose a template for their report.
     */
    public function index()
    {
        $templates = Template::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($template) => [
                'id'             => $template->id,
                'name'           => $template->name,
                'slug'           => $template->slug,
                'description'    => $template->description,
                'thumbnail'      => $template->thumbnail,
                'badge'          => $template->badge,
                'category'       => $template->category,
                'tags'           => $template->tags,
                'cover_gradient' => $template->cover_gradient,
                'settings'       => $template->settings,
                'structure'      => $template->structure,
                'is_active'      => $template->is_active,
                'created_at'     => $template->created_at,
            ]);
        
        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }
    
    /**
     * Show a specific template (API endpoint for preview).
     */
    public function show($slug)
    {
        $template = Template::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
        
        return response()->json([
            'template' => [
                'id'             => $template->id,
                'name'           => $template->name,
                'slug'           => $template->slug,
                'description'    => $template->description,
                'thumbnail'      => $template->thumbnail,
                'badge'          => $template->badge,
                'category'       => $template->category,
                'tags'           => $template->tags,
                'cover_gradient' => $template->cover_gradient,
                'settings'       => $template->settings,
                'structure'      => $template->structure,
            ]
        ]);
    }
    
    /**
     * Store a new template (Admin only).
     */
    public function store(Request $request)
    {
        $this->authorize('manage-templates');
        
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'category'       => 'nullable|string|max:100',
            'badge'          => 'nullable|string|max:50',
            'tags'           => 'nullable|array',
            'cover_gradient' => 'nullable|string',
            'structure'      => 'nullable|array',
            'settings'       => 'nullable|array',
        ]);

$template = Template::create([
    'name'           => $request->name,
    'slug'           => Str::slug($request->name) . '-' . Str::random(6),
    'description'    => $request->description,
    'category'       => $request->category,
    'badge'          => $request->badge,
    'tags'           => $request->tags,
    'cover_gradient' => $request->cover_gradient,
    'structure'      => $request->structure ?? ['pages' => []],
    'settings'       => array_merge($this->defaultSettings(), $request->settings ?? []),
    'is_active'      => $request->is_active ?? true,
]);

        UserActivity::log(auth()->id(), 'template_created', 'template', $template->id, [
            'template_name' => $template->name
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Update an existing template (Admin only).
     */
    public function update(Request $request, Template $template)
    {
        $this->authorize('manage-templates');
        
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:500',
            'category'       => 'nullable|string|max:100',
            'badge'          => 'nullable|string|max:50',
            'tags'           => 'nullable|array',
            'cover_gradient' => 'nullable|string',
            'structure'      => 'nullable|array',
            'settings'       => 'nullable|array',
        ]);

        $template->update($request->only([
            'name', 'description', 'category', 'badge', 'tags',
            'cover_gradient', 'structure', 'settings', 'is_active'
        ]));

        // Update slug if name changed
        if ($request->name !== $template->getOriginal('name')) {
            $template->update(['slug' => Str::slug($request->name) . '-' . Str::random(6)]);
        }

        UserActivity::log(auth()->id(), 'template_updated', 'template', $template->id, [
            'template_name' => $template->name
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Soft delete a template (Admin only).
     */
    public function destroy(Template $template)
    {
        $this->authorize('manage-templates');
        
        UserActivity::log(auth()->id(), 'template_deleted', 'template', $template->id, [
            'template_name' => $template->name
        ]);

        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template moved to trash.');
    }

    /**
     * Restore a soft-deleted template (Admin only).
     */
    public function restore($id)
    {
        $this->authorize('manage-templates');
        
        $template = Template::withTrashed()->findOrFail($id);
        $template->restore();

        UserActivity::log(auth()->id(), 'template_restored', 'template', $template->id, [
            'template_name' => $template->name
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template restored successfully.');
    }

    /**
     * Permanently delete a template (Admin only).
     */
    public function forceDelete($id)
    {
        $this->authorize('manage-templates');
        
        $template = Template::withTrashed()->findOrFail($id);

        UserActivity::log(auth()->id(), 'template_force_deleted', 'template', $template->id, [
            'template_name' => $template->name
        ]);

        $template->forceDelete();

        return redirect()->route('templates.index')
            ->with('success', 'Template permanently deleted.');
    }

    /**
     * Use template to create a report.
     * Redirects to report creation with pre-selected template.
     */
    public function use($slug)
    {
        $template = Template::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
        
        return Inertia::render('Reports/Create', [
            'selectedTemplate' => $template
        ]);
    }
    
    /**
     * Default template settings.
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
}