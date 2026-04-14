<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return redirect()->route('dashboard');
    return inertia('Welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Report routes
    Route::resource('reports', ReportController::class)->except(['show']);
    Route::get('/reports/{report:slug}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::get('/reports/{report:slug}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::get('/reports/{report:slug}/preview', [ReportController::class, 'preview'])->name('reports.preview'); // Add this line
    
    // Template routes
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
});

require __DIR__.'/auth.php';