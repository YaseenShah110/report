<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\ReportAssignmentController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AnalyticsController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// ─────────────────────────────────────────────────────────────────────────
// PUBLIC LANDING PAGE
// ─────────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => app()->version(),
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// ─────────────────────────────────────────────────────────────────────────
// PUBLIC SHARE PREVIEW (No authentication required)
// ─────────────────────────────────────────────────────────────────────────
Route::get('/share/{token}', [ReportController::class, 'publicPreview'])->name('reports.public-preview');
Route::get('/share/{token}/download', [ReportController::class, 'publicDownload'])->name('reports.public-download');

// ─────────────────────────────────────────────────────────────────────────
// HEALTH CHECK
// ─────────────────────────────────────────────────────────────────────────
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'app_name' => config('app.name'),
    ]);
})->name('health');

// ─────────────────────────────────────────────────────────────────────────
// AUTHENTICATED ROUTES
// ─────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // ─────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─────────────────────────────────────────────────────────────────────
    // PROFILE
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─────────────────────────────────────────────────────────────────────
    // REPORTS MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        
        // Main CRUD
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report:slug}/edit', [ReportController::class, 'edit'])->middleware('can:edit,report')->name('edit');
        Route::put('/{report:slug}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{report:slug}', [ReportController::class, 'destroy'])->name('destroy');
        
        // View & Preview
        Route::get('/{report:slug}/preview', [ReportController::class, 'preview'])->middleware('can:view,report')->name('preview');
        
        // Status Management
        Route::patch('/{report:slug}/status', [ReportController::class, 'updateStatus'])->name('status');
        
        // Duplicate
        Route::post('/{report:slug}/duplicate', [ReportController::class, 'duplicate'])->name('duplicate');
        
        // Version History
        Route::get('/{report:slug}/versions', [ReportController::class, 'versions'])->name('versions');
        Route::post('/{report:slug}/versions/{version}/restore', [ReportController::class, 'restoreVersion'])->name('versions.restore');
        
        // Share Management
        Route::post('/{report:slug}/share', [ReportController::class, 'generateShareLink'])->name('share');
        Route::delete('/{report:slug}/share', [ReportController::class, 'revokeShareLink'])->name('share.revoke');
        
        // Export Functionality
        Route::get('/{report:slug}/download', [ReportController::class, 'download'])->name('download');
        Route::get('/{report:slug}/export/pdf', [ReportController::class, 'download'])->name('export.pdf');
        Route::get('/{report:slug}/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/{report:slug}/export/csv', [ReportController::class, 'exportCsv'])->name('export.csv');
        Route::get('/{report:slug}/export/image', [ReportController::class, 'exportImage'])->name('export.image');
    });

    // ─────────────────────────────────────────────────────────────────────
    // TEMPLATES MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::get('/{template}', [TemplateController::class, 'show'])->name('show');
    });

    // ─────────────────────────────────────────────────────────────────────
    // ADMIN PANEL
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware(['can:manage-users'])->group(function () {
        
        // ── User Management ────────────────────────────────────────────────
        Route::resource('users', UserController::class);
        Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
        Route::post('users/stop-impersonate', [UserController::class, 'stopImpersonate'])->name('users.stop-impersonate');
        Route::get('users/{user}/activities', [UserController::class, 'activities'])->name('users.activities');
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        
        // ── Role Management ─────────────────────────────────────────────────
        Route::resource('roles', RoleController::class);
        Route::get('roles/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('roles/permissions', [RoleController::class, 'storePermission'])->name('roles.permissions.store');
        Route::put('roles/permissions/{permission}', [RoleController::class, 'updatePermission'])->name('roles.permissions.update');
        Route::delete('roles/permissions/{permission}', [RoleController::class, 'destroyPermission'])->name('roles.permissions.destroy');
        Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');
        Route::delete('roles/{role}/permissions/{permission}', [RoleController::class, 'removePermission'])->name('roles.remove-permission');
        Route::post('roles/assign-to-user', [RoleController::class, 'assignToUser'])->name('roles.assign-to-user');
        Route::delete('roles/remove-from-user', [RoleController::class, 'removeFromUser'])->name('roles.remove-from-user');
        Route::get('roles/{role}/users', [RoleController::class, 'roleUsers'])->name('roles.users');
        Route::post('roles/setup-default', [RoleController::class, 'setupDefaultRoles'])->name('roles.setup-default');
        Route::get('roles/stats', [RoleController::class, 'getStats'])->name('roles.stats');
        Route::get('roles/all/permissions', [RoleController::class, 'getAllPermissions'])->name('roles.all-permissions');
        
        // ── Task Management ─────────────────────────────────────────────────
        Route::resource('tasks', TaskController::class);
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::get('tasks/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my');
        Route::post('tasks/bulk-delete', [TaskController::class, 'bulkDelete'])->name('tasks.bulk-delete');
        
        // ── Report Assignments ──────────────────────────────────────────────
        Route::prefix('report-assignments')->name('report-assignments.')->group(function () {
            Route::get('/', [ReportAssignmentController::class, 'index'])->name('index');
            Route::post('/', [ReportAssignmentController::class, 'store'])->name('store');
            Route::delete('/{assignment}', [ReportAssignmentController::class, 'destroy'])->name('destroy');
            Route::patch('/{assignment}/toggle', [ReportAssignmentController::class, 'toggleActive'])->name('toggle');
            Route::get('/report/{report}', [ReportAssignmentController::class, 'forReport'])->name('for-report');
            Route::get('/user/{user}', [ReportAssignmentController::class, 'forUser'])->name('for-user');
        });
        
        // ── Activity Logs ───────────────────────────────────────────────────
        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/user/{user}', [ActivityController::class, 'userActivities'])->name('user');
            Route::delete('/clear', [ActivityController::class, 'clear'])->name('clear');
            Route::get('/export', [ActivityController::class, 'export'])->name('export');
        });
        
        // ── Analytics Dashboard ─────────────────────────────────────────────
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports');
            Route::get('/users', [AnalyticsController::class, 'users'])->name('users');
            Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
        });
    });
});

// ─────────────────────────────────────────────────────────────────────────
// IMPERSONATION ROUTE (Outside admin group for proper redirect)
// ─────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->post('/admin/users/stop-impersonate', [UserController::class, 'stopImpersonate'])
    ->name('admin.users.stop-impersonate');

// ─────────────────────────────────────────────────────────────────────────
// MAINTENANCE MODE FALLBACK
// ─────────────────────────────────────────────────────────────────────────
if (app()->isDownForMaintenance()) {
    Route::get('/{any}', function () {
        return response()->view('errors.maintenance', [], 503);
    })->where('any', '.*');
}

// ─────────────────────────────────────────────────────────────────────────
// LOAD AUTH ROUTES
// ─────────────────────────────────────────────────────────────────────────
require __DIR__.'/auth.php';