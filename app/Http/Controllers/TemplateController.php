<?php
// app/Http/Controllers/TemplateController.php

namespace App\Http\Controllers;

use App\Models\Template;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TemplateController extends Controller
{
     use HasFactory, SoftDeletes; 
     protected $dates = ['deleted_at'];
    public function index()
    {
        $templates = Template::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($template) => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'thumbnail' => $template->thumbnail,
                'badge' => $template->badge,
                'category' => $template->category,
                'tags' => $template->tags,
                'cover_gradient' => $template->cover_gradient,
                'settings' => $template->settings,
                'structure' => $template->structure,
            ]);
        
        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }
    
    public function show($slug)
    {
        $template = Template::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        return response()->json([
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'settings' => $template->settings,
                'structure' => $template->structure,
            ]
        ]);
    }
    
    public function use($slug)
    {
        $template = Template::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        return Inertia::render('Reports/Create', [
            'selectedTemplate' => $template
        ]);
    }
}