<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\ReportAssignmentController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AnalyticsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ╔══════════════════════════════════════════════════════════════════════════╗
// ║                         PUBLIC ROUTES                                    ║
// ╚══════════════════════════════════════════════════════════════════════════╝

// ─────────────────────────────────────────────────────────────────────────
// WELCOME / LANDING PAGE
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
Route::prefix('share')->name('reports.')->group(function () {
    Route::get('/{token}', [ReportController::class, 'publicPreview'])->name('public-preview');
    Route::get('/{token}/download', [ReportController::class, 'publicDownload'])->name('public-download');
});

// ─────────────────────────────────────────────────────────────────────────
// HEALTH CHECK
// ─────────────────────────────────────────────────────────────────────────
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'app_name' => config('app.name'),
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
    ]);
})->name('health');


// ╔══════════════════════════════════════════════════════════════════════════╗
// ║                     AUTHENTICATED ROUTES                                 ║
// ╚══════════════════════════════════════════════════════════════════════════╝

Route::middleware(['auth', 'verified'])->group(function () {

    // ─────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─────────────────────────────────────────────────────────────────────
    // PROFILE MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ─────────────────────────────────────────────────────────────────────
    // NOTIFICATIONS (Dynamic Real-time Notifications)
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // View all notifications page
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        
        // API endpoint for latest notifications (used by dropdown)
        Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
        
        // Mark single notification as read
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        
        // Mark all notifications as read
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        
        // Soft delete a notification
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        
        // Restore a soft-deleted notification
        Route::post('/{id}/restore', [NotificationController::class, 'restore'])->name('restore');
        
        // Force delete a notification permanently
        Route::delete('/{id}/force', [NotificationController::class, 'forceDelete'])->name('force-delete');
    });

    // ─────────────────────────────────────────────────────────────────────
    // REPORTS MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        
        // Main CRUD Operations
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report:slug}/edit', [ReportController::class, 'edit'])
            ->middleware('can:edit,report')
            ->name('edit');
        Route::put('/{report:slug}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{report:slug}', [ReportController::class, 'destroy'])->name('destroy');
        
        // View & Preview
        Route::get('/{report:slug}/preview', [ReportController::class, 'preview'])
            ->middleware('can:view,report')
            ->name('preview');
        
        // Status Management
        Route::patch('/{report:slug}/status', [ReportController::class, 'updateStatus'])->name('status');
        
        // Duplicate Report
        Route::post('/{report:slug}/duplicate', [ReportController::class, 'duplicate'])->name('duplicate');
        
        // Version History
        Route::get('/{report:slug}/versions', [ReportController::class, 'versions'])->name('versions');
        Route::post('/{report:slug}/versions/{version}/restore', [ReportController::class, 'restoreVersion'])
            ->name('versions.restore');
        
        // Share Management
        Route::post('/{report:slug}/share', [ReportController::class, 'generateShareLink'])->name('share');
        Route::delete('/{report:slug}/share', [ReportController::class, 'revokeShareLink'])->name('share.revoke');
        
        // Export Functionality
        Route::get('/{report:slug}/download', [ReportController::class, 'download'])->name('download');
        Route::get('/{report:slug}/export/pdf', [ReportController::class, 'download'])->name('export.pdf');
        Route::get('/{report:slug}/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/{report:slug}/export/csv', [ReportController::class, 'exportCsv'])->name('export.csv');
        Route::get('/{report:slug}/export/image', [ReportController::class, 'exportImage'])->name('export.image');
        
        // Restore soft-deleted report
        Route::post('/{report:slug}/restore', [ReportController::class, 'restore'])
            ->withTrashed()
            ->name('restore');
        
        // Force delete report permanently
        Route::delete('/{report:slug}/force', [ReportController::class, 'forceDelete'])
            ->withTrashed()
            ->name('force-delete');
        
        // Assigned reports for users
        Route::get('/assigned', [ReportController::class, 'assignedReports'])->name('assigned');
        
        // Trashed reports
        Route::get('/trashed', [ReportController::class, 'trashed'])->name('trashed');
    });

    // ─────────────────────────────────────────────────────────────────────
    // TEMPLATES MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::get('/{template}', [TemplateController::class, 'show'])->name('show');
        Route::post('/', [TemplateController::class, 'store'])->name('store');
        Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{template}/restore', [TemplateController::class, 'restore'])
            ->withTrashed()
            ->name('restore');
        Route::delete('/{template}/force', [TemplateController::class, 'forceDelete'])
            ->withTrashed()
            ->name('force-delete');
    });

    // ─────────────────────────────────────────────────────────────────────
    // MY TASKS (For all authenticated users)
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('admin.tasks.my');


    // ╔══════════════════════════════════════════════════════════════════════╗
    // ║                      ADMIN PANEL ROUTES                              ║
    // ╚══════════════════════════════════════════════════════════════════════╝

    Route::prefix('admin')->name('admin.')->middleware(['can:manage-users'])->group(function () {
        
        // ─────────────────────────────────────────────────────────────────
        // USER MANAGEMENT
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('users')->name('users.')->group(function () {
            // CRUD Operations
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            
            // User Actions
            Route::post('/{user}/impersonate', [UserController::class, 'impersonate'])->name('impersonate');
            Route::post('/stop-impersonate', [UserController::class, 'stopImpersonate'])->name('stop-impersonate');
            Route::get('/{user}/activities', [UserController::class, 'activities'])->name('activities');
            
            // Bulk Operations
            Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-activate', [UserController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            
            // Export
            Route::get('/export', [UserController::class, 'export'])->name('export');
            Route::get('/export/pdf', [UserController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [UserController::class, 'exportExcel'])->name('export.excel');
            
            // Restore & Force Delete
            Route::post('/{user}/restore', [UserController::class, 'restore'])
                ->withTrashed()
                ->name('restore');
            Route::delete('/{user}/force', [UserController::class, 'forceDelete'])
                ->withTrashed()
                ->name('force-delete');
            
            // Trashed users
            Route::get('/trashed', [UserController::class, 'trashed'])->name('trashed');
        });
        
        // ─────────────────────────────────────────────────────────────────
        // ROLE & PERMISSION MANAGEMENT
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('roles')->name('roles.')->group(function () {
            // CRUD Operations
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
            
            // Permission Management
            Route::get('/permissions', [RoleController::class, 'permissions'])->name('permissions');
            Route::post('/permissions', [RoleController::class, 'storePermission'])->name('permissions.store');
            Route::put('/permissions/{permission}', [RoleController::class, 'updatePermission'])->name('permissions.update');
            Route::delete('/permissions/{permission}', [RoleController::class, 'destroyPermission'])->name('permissions.destroy');
            Route::get('/all/permissions', [RoleController::class, 'getAllPermissions'])->name('all-permissions');
            
            // Role-Permission Assignment
            Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])->name('assign-permissions');
            Route::delete('/{role}/permissions/{permission}', [RoleController::class, 'removePermission'])->name('remove-permission');
            
            // User-Role Assignment
            Route::post('/assign-to-user', [RoleController::class, 'assignToUser'])->name('assign-to-user');
            Route::delete('/remove-from-user', [RoleController::class, 'removeFromUser'])->name('remove-from-user');
            Route::get('/{role}/users', [RoleController::class, 'roleUsers'])->name('users');
            
            // Utilities
            Route::post('/setup-default', [RoleController::class, 'setupDefaultRoles'])->name('setup-default');
            Route::get('/stats', [RoleController::class, 'getStats'])->name('stats');
        });
        
        // ─────────────────────────────────────────────────────────────────
        // TASK MANAGEMENT
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('tasks')->name('tasks.')->group(function () {
            // CRUD Operations
            Route::get('/', [TaskController::class, 'index'])->name('index');
            Route::get('/create', [TaskController::class, 'create'])->name('create');
            Route::post('/', [TaskController::class, 'store'])->name('store');
            Route::get('/{task}', [TaskController::class, 'show'])->name('show');
            Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
            Route::put('/{task}', [TaskController::class, 'update'])->name('update');
            Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
            
            // Status Management
            Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('status');
            
            // Bulk Operations
            Route::post('/bulk-delete', [TaskController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-assign', [TaskController::class, 'bulkAssign'])->name('bulk-assign');
            Route::post('/bulk-status', [TaskController::class, 'bulkStatus'])->name('bulk-status');
            
            // Export
            Route::get('/export', [TaskController::class, 'export'])->name('export');
            Route::get('/export/pdf', [TaskController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [TaskController::class, 'exportExcel'])->name('export.excel');
            
            // Restore & Force Delete
            Route::post('/{task}/restore', [TaskController::class, 'restore'])
                ->withTrashed()
                ->name('restore');
            Route::delete('/{task}/force', [TaskController::class, 'forceDelete'])
                ->withTrashed()
                ->name('force-delete');
            
            // Trashed tasks
            Route::get('/trashed', [TaskController::class, 'trashed'])->name('trashed');
        });
        
        // ─────────────────────────────────────────────────────────────────
        // REPORT ASSIGNMENTS MANAGEMENT
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('report-assignments')->name('report-assignments.')->group(function () {
            // Main Operations
            Route::get('/', [ReportAssignmentController::class, 'index'])->name('index');
            Route::post('/', [ReportAssignmentController::class, 'store'])->name('store');
            Route::delete('/{assignment}', [ReportAssignmentController::class, 'destroy'])->name('destroy');
            
            // Toggle Active Status
            Route::patch('/{assignment}/toggle', [ReportAssignmentController::class, 'toggleActive'])->name('toggle');
            
            // Filtered Views
            Route::get('/report/{report}', [ReportAssignmentController::class, 'forReport'])->name('for-report');
            Route::get('/user/{user}', [ReportAssignmentController::class, 'forUser'])->name('for-user');
            
            // Bulk Operations
            Route::post('/bulk-assign', [ReportAssignmentController::class, 'bulkAssign'])->name('bulk-assign');
            Route::post('/bulk-revoke', [ReportAssignmentController::class, 'bulkRevoke'])->name('bulk-revoke');
            
            // Export
            Route::get('/export', [ReportAssignmentController::class, 'export'])->name('export');
        });
        
        // ─────────────────────────────────────────────────────────────────
        // ACTIVITY LOGS
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/user/{user}', [ActivityController::class, 'userActivities'])->name('user');
            Route::get('/type/{type}', [ActivityController::class, 'byType'])->name('type');
            
            // Activity Actions
            Route::delete('/clear', [ActivityController::class, 'clear'])->name('clear');
            Route::delete('/clear-older-than/{days}', [ActivityController::class, 'clearOlderThan'])->name('clear-old');
            
            // Export
            Route::get('/export', [ActivityController::class, 'export'])->name('export');
            Route::get('/export/pdf', [ActivityController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [ActivityController::class, 'exportExcel'])->name('export.excel');
        });
        
        // ─────────────────────────────────────────────────────────────────
        // ANALYTICS DASHBOARD
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            
            // Specific Analytics
            Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports');
            Route::get('/users', [AnalyticsController::class, 'users'])->name('users');
            Route::get('/tasks', [AnalyticsController::class, 'tasks'])->name('tasks');
            Route::get('/performance', [AnalyticsController::class, 'performance'])->name('performance');
            
            // Date Range Filtered
            Route::get('/range', [AnalyticsController::class, 'byDateRange'])->name('range');
            
            // Export
            Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
            Route::get('/export/pdf', [AnalyticsController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [AnalyticsController::class, 'exportExcel'])->name('export.excel');
            
            // API Endpoints for Charts
            Route::get('/api/report-stats', [AnalyticsController::class, 'reportStats'])->name('api.report-stats');
            Route::get('/api/user-stats', [AnalyticsController::class, 'userStats'])->name('api.user-stats');
            Route::get('/api/task-stats', [AnalyticsController::class, 'taskStats'])->name('api.task-stats');
        });
        
        // ─────────────────────────────────────────────────────────────────
        // SYSTEM SETTINGS (Optional - if you have system settings)
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Admin/Settings/Index');
            })->name('index');
            
            Route::put('/general', function () {
                // Update general settings
            })->name('general.update');
            
            Route::put('/email', function () {
                // Update email settings
            })->name('email.update');
            
            Route::put('/security', function () {
                // Update security settings
            })->name('security.update');
        });
    });
});

// ╔══════════════════════════════════════════════════════════════════════════╗
// ║                     IMPERSONATION ROUTE                                  ║
// ║           (Outside admin group for proper redirect handling)             ║
// ╚══════════════════════════════════════════════════════════════════════════╝

Route::middleware(['auth', 'verified'])->post('/admin/users/stop-impersonate', [UserController::class, 'stopImpersonate'])
    ->name('admin.users.stop-impersonate');


// ╔══════════════════════════════════════════════════════════════════════════╗
// ║                     MAINTENANCE MODE FALLBACK                            ║
// ╚══════════════════════════════════════════════════════════════════════════╝

if (app()->isDownForMaintenance()) {
    Route::get('/{any}', function () {
        return response()->view('errors.maintenance', [], 503);
    })->where('any', '.*');
}


// ╔══════════════════════════════════════════════════════════════════════════╗
// ║                     AUTH ROUTES (Laravel Breeze/Jetstream)               ║
// ╚══════════════════════════════════════════════════════════════════════════╝

require __DIR__.'/auth.php';