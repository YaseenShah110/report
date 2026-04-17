<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report:slug}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/{report:slug}', [ReportController::class, 'update'])->name('reports.update');
    Route::get('/reports/{report:slug}/preview', [ReportController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/{report:slug}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::delete('/reports/{report:slug}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::patch('/reports/{report:slug}/status', [ReportController::class, 'updateStatus'])->name('reports.status');
    Route::post('/reports/{report:slug}/duplicate', [ReportController::class, 'duplicate'])->name('reports.duplicate');

    // Templates
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');

});

require __DIR__.'/auth.php';
