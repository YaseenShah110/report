<?php

/**
 * ================================================================
 * LARAVEL 12 WEB ROUTES - Report Generator Application
 * ================================================================
 *
 * Framework Stack:
 * - Laravel 12 + Inertia.js + Vue 3 + MySQL + Tailwind CSS
 * - Spatie Permissions for Role-Based Access Control
 *
 * Route Organization:
 * 1. PUBLIC ROUTES (No Authentication)
 * 2. AUTHENTICATED ROUTES (Login + Email Verified)
 * 3. ADMIN ROUTES (Role/Permission-Based)
 * 4. API ROUTES (Helper Endpoints)
 * 5. AUTH ROUTES (Breeze/Jetstream)
 *
 * Permission System:
 * - Permissions are checked dynamically via middleware
 * - Admins can grant/revoke permissions during user creation/editing
 * - Each route uses 'can:permission' middleware for granular control
 * - Role-based fallback: 'can:admin|manager' for multi-role routes
 *
 * Soft Deletes:
 * - All major resources (users, reports, tasks, templates) support soft deletes
 * - Uses withTrashed() middleware for restore/force-delete routes
 * - Trash/trashed routes for viewing deleted items
 *
 * Route Model Binding:
 * - Reports use slug binding for SEO & security
 * - Other resources use ID binding
 *
 * ================================================================
 */

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ReportAssignmentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES - No Authentication Required
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'      => Route::has('login'),
        'canRegister'   => Route::has('register'),
        'laravelVersion'=> app()->version(),
        'phpVersion'    => PHP_VERSION,
    ]);
})->name('home');

// Public Share Links - View/download reports without authentication
Route::prefix('share')->name('reports.')->group(function () {
    Route::get('/{token}', [ReportController::class, 'publicPreview'])
        ->name('public-preview');
    Route::get('/{token}/download', [ReportController::class, 'publicDownload'])
        ->name('public-download');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES - Requires Login + Verified Email
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |======================================================================
    | DASHBOARD
    |======================================================================
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |======================================================================
    | PROFILE MANAGEMENT - User's Own Profile
    |======================================================================
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])
            ->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])
            ->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('destroy');
    });

    /*
    |======================================================================
    | NOTIFICATIONS - Real-time Notification System
    |======================================================================
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])
            ->name('index');
        Route::get('/latest', [NotificationController::class, 'latest'])
            ->name('latest');
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('mark-read');
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])
            ->name('destroy');
        Route::post('/{id}/restore', [NotificationController::class, 'restore'])
            ->name('restore');
        Route::delete('/{id}/force', [NotificationController::class, 'forceDelete'])
            ->name('force-delete');
    });

    /*
    |======================================================================
    | REPORTS - Core Report Management (CRUD + Export + Share + Versions)
    |======================================================================
    */
    Route::prefix('reports')->name('reports.')->group(function () {

        // ── Listing & Reading ───────────────────────────────────────
        Route::get('/', [ReportController::class, 'index'])
            ->name('index');
        Route::get('/all', [ReportController::class, 'allReports'])
            ->name('reports.all')
            ->middleware('can:view-reports');

        Route::get('/{report:slug}/preview', [ReportController::class, 'preview'])
            ->middleware('can:view-reports')
            ->name('preview');
        Route::get('/trashed', [ReportController::class, 'trashed'])
            ->middleware('can:delete-reports')
            ->name('trashed');
        Route::get('/assigned', [ReportController::class, 'assignedReports'])
            ->name('assigned');

        // ── Create ──────────────────────────────────────────────────
        Route::get('/create', [ReportController::class, 'create'])
            ->middleware('can:create-reports')
            ->name('create');
        Route::post('/', [ReportController::class, 'store'])
            ->middleware('can:create-reports')
            ->name('store');

        // ── Edit & Update ───────────────────────────────────────────
        Route::get('/{report:slug}/edit', [ReportController::class, 'edit'])
            ->middleware('can:edit-reports')
            ->name('edit');
        Route::put('/{report:slug}', [ReportController::class, 'update'])
            ->middleware('can:edit-reports')
            ->name('update');

        // ── Delete ──────────────────────────────────────────────────
        Route::delete('/{report:slug}', [ReportController::class, 'destroy'])
            ->middleware('can:delete-reports')
            ->name('destroy');
        Route::post('/{report:slug}/restore', [ReportController::class, 'restore'])
            ->middleware('can:delete-reports')
            ->withTrashed()
            ->name('restore');
        Route::delete('/{report:slug}/force', [ReportController::class, 'forceDelete'])
            ->middleware('can:delete-reports')
            ->withTrashed()
            ->name('force-delete');

        // ── Status Management ───────────────────────────────────────
        Route::patch('/{report:slug}/status', [ReportController::class, 'updateStatus'])
            ->middleware('can:edit-reports')
            ->name('status');

        // ── Duplicate ───────────────────────────────────────────────
        Route::post('/{report:slug}/duplicate', [ReportController::class, 'duplicate'])
            ->middleware('can:create-reports')
            ->name('duplicate');

        // ── Version History ────────────────────────────────────────
        Route::get('/{report:slug}/versions', [ReportController::class, 'versions'])
            ->middleware('can:view-reports')
            ->name('versions');
        
        // Store a manual version snapshot (editor calls this every 5 min)
        Route::post('/{report:slug}/versions', [ReportController::class, 'storeVersion'])
            ->middleware('can:edit-reports')
            ->name('versions.store');
        
        Route::post('/{report:slug}/versions/{version}/restore', [ReportController::class, 'restoreVersion'])
            ->middleware('can:edit-reports')
            ->name('versions.restore');

        // ── Share Management ───────────────────────────────────────
        Route::post('/{report:slug}/share', [ReportController::class, 'generateShareLink'])
            ->middleware('can:manage-reports')
            ->name('share');
        Route::delete('/{report:slug}/share', [ReportController::class, 'revokeShareLink'])
            ->middleware('can:manage-reports')
            ->name('share.revoke');

        // ── Export Functionality ────────────────────────────────────
        Route::get('/{report:slug}/download', [ReportController::class, 'download'])
            ->middleware('can:view-reports')
            ->name('download');
        
        // Generates PDF via Browsershot — streams bytes back as download
        Route::post('/{report:slug}/export-pdf', [ReportController::class, 'exportPdf'])
            ->middleware('can:view-reports')
            ->name('export-pdf');
        
        Route::get('/{report:slug}/export/pdf', [ReportController::class, 'download'])
            ->middleware('can:view-reports')
            ->name('export.pdf');
        Route::get('/{report:slug}/export/excel', [ReportController::class, 'exportExcel'])
            ->middleware('can:view-reports')
            ->name('export.excel');
        Route::get('/{report:slug}/export/csv', [ReportController::class, 'exportCsv'])
            ->middleware('can:view-reports')
            ->name('export.csv');
        Route::get('/{report:slug}/export/image', [ReportController::class, 'exportImage'])
            ->middleware('can:view-reports')
            ->name('export.image');

        // ── Report Assignment Management ────────────────────────────
        Route::get('/{report:slug}/assignments', [ReportController::class, 'getAssignments'])
            ->middleware('can:assign-reports')
            ->name('assignments');
        Route::post('/{report:slug}/assign', [ReportController::class, 'assignToUser'])
            ->middleware('can:assign-reports')
            ->name('assign');
        Route::delete('/{report:slug}/assignments/{assignment}', [ReportController::class, 'removeAssignment'])
            ->middleware('can:assign-reports')
            ->name('unassign');

        // ── Element Presets ────────────────────────────────────────
        Route::get('/presets/list', [ReportController::class, 'getPresets'])
            ->middleware('can:view-reports')
            ->name('presets');
        Route::post('/presets/save', [ReportController::class, 'savePreset'])
            ->middleware('can:create-reports')
            ->name('presets.save');

        // ── Report Statistics ──────────────────────────────────────
        Route::get('/{report:slug}/stats', [ReportController::class, 'reportStats'])
            ->middleware('can:view-reports')
            ->name('stats');

        // ── Email Report ───────────────────────────────────────────
        Route::post('/{report:slug}/email', [ReportController::class, 'emailReport'])
            ->middleware('can:manage-reports')
            ->name('email');

        // ── PRESENCE — live "who's editing" badge ──────────────────
        // Heartbeat: POSTed every ~8s by usePresence.js composable
        Route::post('/{report:slug}/presence/heartbeat', [PresenceController::class, 'heartbeat'])
            ->name('presence.heartbeat');

        // Leave: fired via navigator.sendBeacon on tab-close / navigation
        Route::post('/{report:slug}/presence/leave', [PresenceController::class, 'leave'])
            ->name('presence.leave');
    });

    /*
    |======================================================================
    | TEMPLATES - Browse & Use Report Templates
    |======================================================================
    */
    Route::get('/templates', [TemplateController::class, 'index'])
        ->middleware('can:view-templates')
        ->name('templates.index');
    Route::get('/templates/{template:slug}', [TemplateController::class, 'show'])
        ->middleware('can:view-templates')
        ->name('templates.show');
    Route::get('/templates/{template:slug}/use', [TemplateController::class, 'use'])
        ->middleware('can:create-reports')
        ->name('templates.use');

    /*
    |======================================================================
    | MEDIA — image upload + free keyword image search proxy
    |======================================================================
    */
    Route::prefix('media')->name('media.')->group(function () {
        // Upload a user image - stores in storage/public/report-media/{report_id}/
        Route::post('/upload', [MediaController::class, 'upload'])
            ->name('upload');

        // Free image search proxy — API key stays server-side
        Route::get('/search', [MediaController::class, 'search'])
            ->name('search');
    });

    /*
    |======================================================================
    | MY TASKS - Tasks Assigned to Current User
    |======================================================================
    */
    Route::prefix('my-tasks')->name('my-tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'myTasks'])
            ->middleware('can:view-tasks')
            ->name('index');

        // ✅ Dedicated My Tasks export route — scoped to current user's tasks only
        Route::get('/export', [TaskController::class, 'exportMyTasks'])
            ->middleware('can:view-tasks')
            ->name('export');
    });

    // Keep backward-compatible named route alias used elsewhere in the app
    Route::get('/my-tasks-legacy', [TaskController::class, 'myTasks'])
        ->middleware('can:view-tasks')
        ->name('admin.tasks.my');

    /*
    |======================================================================
    | ADMIN & MANAGER ROUTES - Role-Based Access Control
    |======================================================================
    */

    Route::prefix('admin')->name('admin.')
        ->middleware(['auth', 'verified'])
        ->group(function () {

            /*
            |==================================================================
            | USER MANAGEMENT - Create, Update, Delete Users & Assign Roles
            |==================================================================
            */
            Route::prefix('users')->name('users.')->group(function () {

                // ── Listing & Reading ───────────────────────────────────
                Route::get('/', [UserController::class, 'index'])
                    ->middleware('can:view-users')
                    ->name('index');
                Route::get('/{user}', [UserController::class, 'show'])
                    ->middleware('can:view-users')
                    ->name('show');
                Route::get('/{user}/activities', [UserController::class, 'userActivities'])
                    ->middleware('can:view-users')
                    ->name('activities');
                Route::get('/trashed', [UserController::class, 'trashed'])
                    ->middleware('can:delete-users')
                    ->name('trashed');

                // ── Create ──────────────────────────────────────────────
                Route::get('/create', [UserController::class, 'create'])
                    ->middleware('can:create-users')
                    ->name('create');
                Route::post('/', [UserController::class, 'store'])
                    ->middleware('can:create-users')
                    ->name('store');

                // ── Edit & Update ───────────────────────────────────────
                Route::get('/{user}/edit', [UserController::class, 'edit'])
                    ->middleware('can:edit-users')
                    ->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])
                    ->middleware('can:edit-users')
                    ->name('update');

                // ── Delete ──────────────────────────────────────────────
                Route::delete('/{user}', [UserController::class, 'destroy'])
                    ->middleware('can:delete-users')
                    ->name('destroy');
                Route::post('/{user}/restore', [UserController::class, 'restore'])
                    ->middleware('can:delete-users')
                    ->withTrashed()
                    ->name('restore');
                Route::delete('/{user}/force', [UserController::class, 'forceDelete'])
                    ->middleware('can:delete-users')
                    ->withTrashed()
                    ->name('force-delete');

                // ── Bulk Operations ────────────────────────────────────
                Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])
                    ->middleware('can:delete-users')
                    ->name('bulk-delete');

                // ── Impersonate ────────────────────────────────────────
                Route::post('/{user}/impersonate', [UserController::class, 'impersonate'])
                    ->middleware('can:manage-users')
                    ->name('impersonate');

                // ── Export ─────────────────────────────────────────────
                Route::get('/export', [UserController::class, 'export'])
                    ->middleware('can:view-users')
                    ->name('export');
                Route::get('/download', [UserController::class, 'downloadUsers'])
                    ->middleware('can:view-users')
                    ->name('admin.users.download');
            });

            /*
            |==================================================================
            | ROLE & PERMISSION MANAGEMENT - Admin Only
            |==================================================================
            */
            Route::prefix('roles')->name('roles.')
                ->middleware('can:manage-roles')
                ->group(function () {

                    // ── Role CRUD ───────────────────────────────────────────
                    Route::get('/', [RoleController::class, 'index'])
                        ->name('index');
                    Route::get('/create', [RoleController::class, 'create'])
                        ->name('create');
                    Route::post('/', [RoleController::class, 'store'])
                        ->name('store');
                    Route::get('/{role}', [RoleController::class, 'show'])
                        ->name('show');
                    Route::get('/{role}/edit', [RoleController::class, 'edit'])
                        ->name('edit');
                    Route::put('/{role}', [RoleController::class, 'update'])
                        ->name('update');
                    Route::delete('/{role}', [RoleController::class, 'destroy'])
                        ->name('destroy');

                    // ── Permission CRUD ─────────────────────────────────────
                    Route::get('/permissions', [RoleController::class, 'permissions'])
                        ->name('permissions');
                    Route::post('/permissions', [RoleController::class, 'storePermission'])
                        ->name('permissions.store');
                    Route::put('/permissions/{permission}', [RoleController::class, 'updatePermission'])
                        ->name('permissions.update');
                    Route::delete('/permissions/{permission}', [RoleController::class, 'destroyPermission'])
                        ->name('permissions.destroy');

                    // ── Role-Permission Assignment ──────────────────────────
                    Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])
                        ->name('assign-permissions');
                    Route::delete('/{role}/permissions/{permission}', [RoleController::class, 'removePermission'])
                        ->name('remove-permission');

                    // ── User-Role Assignment ────────────────────────────────
                    Route::post('/assign-to-user', [RoleController::class, 'assignToUser'])
                        ->name('assign-to-user');
                    Route::delete('/remove-from-user', [RoleController::class, 'removeFromUser'])
                        ->name('remove-from-user');

                    // ── Setup & Statistics ──────────────────────────────────
                    Route::post('/setup-default', [RoleController::class, 'setupDefaultRoles'])
                        ->name('setup-default');
                    Route::get('/stats', [RoleController::class, 'getStats'])
                        ->name('stats');
                });

            /*
            |==================================================================
            | TASK MANAGEMENT - Admin & Manager Access
            |==================================================================
            */
            Route::prefix('tasks')->name('tasks.')->group(function () {

                // ── Static / non-parameterized routes MUST come first ───────
                // CRITICAL: Define all static routes before /{task} to prevent
                // Laravel from matching 'export', 'create', 'trashed', etc. as task IDs.

                // ── Listing ─────────────────────────────────────────────
                Route::get('/', [TaskController::class, 'index'])
                    ->middleware('can:view-tasks')
                    ->name('index');

                // ── Export (MUST be before /{task}) ─────────────────────
                Route::get('/export', [TaskController::class, 'export'])
                    ->middleware('can:view-tasks')
                    ->name('export');

                // ── Create (MUST be before /{task}) ─────────────────────
                Route::get('/create', [TaskController::class, 'create'])
                    ->middleware('can:create-tasks')
                    ->name('create');

                // ── Trashed (MUST be before /{task}) ────────────────────
                Route::get('/trashed', [TaskController::class, 'trashed'])
                    ->middleware('can:delete-tasks')
                    ->name('trashed');

                // ── Bulk Operations (MUST be before /{task}) ────────────
                Route::post('/bulk-delete', [TaskController::class, 'bulkDelete'])
                    ->middleware('can:delete-tasks')
                    ->name('bulk-delete');
                Route::post('/bulk-assign', [TaskController::class, 'bulkAssign'])
                    ->middleware('can:manage-tasks')
                    ->name('bulk-assign');
                Route::post('/bulk-status', [TaskController::class, 'bulkStatus'])
                    ->middleware('can:edit-tasks')
                    ->name('bulk-status');

                // ── Store ────────────────────────────────────────────────
                Route::post('/', [TaskController::class, 'store'])
                    ->middleware('can:create-tasks')
                    ->name('store');

                // ── Parameterized routes (/{task}) MUST come after statics ─
                Route::get('/{task}', [TaskController::class, 'show'])
                    ->middleware('can:view-tasks')
                    ->name('show');

                Route::get('/{task}/edit', [TaskController::class, 'edit'])
                    ->middleware('can:edit-tasks')
                    ->name('edit');

                Route::put('/{task}', [TaskController::class, 'update'])
                    ->middleware('can:edit-tasks')
                    ->name('update');

                Route::delete('/{task}', [TaskController::class, 'destroy'])
                    ->middleware('can:delete-tasks')
                    ->name('destroy');

                Route::post('/{task}/restore', [TaskController::class, 'restore'])
                    ->middleware('can:delete-tasks')
                    ->withTrashed()
                    ->name('restore');

                Route::delete('/{task}/force', [TaskController::class, 'forceDelete'])
                    ->middleware('can:delete-tasks')
                    ->withTrashed()
                    ->name('force-delete');

                // ── Status Management ────────────────────────────────────
                Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])
                    ->middleware('can:edit-tasks')
                    ->name('status');
            });

            /*
            |==================================================================
            | REPORT ASSIGNMENTS - Manage Report Sharing & Access
            |==================================================================
            */
            Route::prefix('report-assignments')->name('report-assignments.')->group(function () {
                Route::get('/', [ReportAssignmentController::class, 'index'])
                    ->middleware('can:manage-reports')
                    ->name('index');
                Route::post('/', [ReportAssignmentController::class, 'store'])
                    ->middleware('can:manage-reports')
                    ->name('store');
                Route::delete('/{assignment}', [ReportAssignmentController::class, 'destroy'])
                    ->middleware('can:manage-reports')
                    ->name('destroy');
                Route::patch('/{assignment}/toggle', [ReportAssignmentController::class, 'toggleActive'])
                    ->middleware('can:manage-reports')
                    ->name('toggle');
                Route::get('/export', [ReportAssignmentController::class, 'export'])
                    ->middleware('can:manage-reports')
                    ->name('export');
            });

            /*
            |==================================================================
            | TEMPLATE MANAGEMENT - Admin Only (Full CRUD)
            |==================================================================
            */
            Route::prefix('templates')->name('templates.')
                ->middleware('can:manage-templates')
                ->group(function () {
                    // ── Listing ─────────────────────────────────────────────
                    Route::get('/', [TemplateController::class, 'adminIndex'])
                        ->name('index');
                    
                    // ── Create ──────────────────────────────────────────────
                    Route::get('/create', [TemplateController::class, 'create'])
                        ->name('create');
                    Route::post('/', [TemplateController::class, 'store'])
                        ->name('store');
                    
                    // ── Edit & Update ───────────────────────────────────────
                    Route::get('/{template}/edit', [TemplateController::class, 'edit'])
                        ->name('edit');
                    Route::put('/{template}', [TemplateController::class, 'update'])
                        ->name('update');
                    
                    // ── Delete ──────────────────────────────────────────────
                    Route::delete('/{template}', [TemplateController::class, 'destroy'])
                        ->name('destroy');
                    Route::post('/{template}/restore', [TemplateController::class, 'restore'])
                        ->withTrashed()
                        ->name('restore');
                    Route::delete('/{template}/force', [TemplateController::class, 'forceDelete'])
                        ->withTrashed()
                        ->name('force-delete');
                });

            /*
            |==================================================================
            | ACTIVITY LOGS - View User & System Activities
            |==================================================================
            */
            Route::prefix('activities')->name('activities.')->group(function () {
                Route::get('/', [ActivityController::class, 'index'])
                    ->middleware('can:view-activities')
                    ->name('index');
                Route::get('/user/{user}', [ActivityController::class, 'userActivities'])
                    ->middleware('can:view-activities')
                    ->name('user');
                Route::delete('/clear', [ActivityController::class, 'clear'])
                    ->middleware('can:manage-settings')
                    ->name('clear');
                Route::get('/export', [ActivityController::class, 'export'])
                    ->middleware('can:view-activities')
                    ->name('export');
            });

            /*
            |==================================================================
            | ANALYTICS DASHBOARD - View & Export Analytics
            |==================================================================
            */
            Route::prefix('analytics')->name('analytics.')->group(function () {
                Route::get('/', [AnalyticsController::class, 'index'])
                    ->middleware('can:view-analytics')
                    ->name('index');
                Route::get('/reports', [AnalyticsController::class, 'reports'])
                    ->middleware('can:view-analytics')
                    ->name('reports');
                Route::get('/users', [AnalyticsController::class, 'users'])
                    ->middleware('can:view-analytics')
                    ->name('users');
                Route::get('/export', [AnalyticsController::class, 'export'])
                    ->middleware('can:view-analytics')
                    ->name('export');
                Route::get('/quick-stats', [AnalyticsController::class, 'quickStats'])
                    ->middleware('can:view-analytics')
                    ->name('quick-stats');
            });
        });
});

/*
|--------------------------------------------------------------------------
| IMPERSONATION STOP - Outside admin group (allows impersonated users to stop)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->post('/admin/users/stop-impersonate', [UserController::class, 'stopImpersonate'])
    ->name('admin.users.stop-impersonate');

/*
|--------------------------------------------------------------------------
| API ROUTES - Image Upload, AI Generation, Chart Suggestions, etc.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |======================================================================
    | IMAGE UPLOAD
    |======================================================================
    */
    Route::post('/api/upload-image', function (Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
        ]);
        try {
            $path = $request->file('image')->store('report-images', 'public');

            return response()->json([
                'url'     => Storage::url($path),
                'path'    => $path,
                'message' => 'Image uploaded successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    });

    /*
    |======================================================================
    | AI CONTENT GENERATION
    |======================================================================
    */
    Route::post('/api/ai/generate', function (Request $request) {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'type'   => 'required|in:text,chart_data,headline,summary',
        ]);

        $prompt = strtolower($request->prompt);

        if ($request->type === 'text') {
            $templates = [
                'business' => [
                    'keywords'  => ['revenue', 'growth', 'profit', 'sales', 'quarter', 'annual', 'business', 'company'],
                    'responses' => [
                        'Based on our analysis, the company has demonstrated consistent growth trajectory over the past fiscal year. Key metrics show a {percent}% increase in revenue, driven by strategic initiatives and market expansion.',
                        'The business performance report indicates strong operational efficiency with EBITDA margins improving by {percent}%. Cost optimization strategies have yielded significant results.',
                    ],
                ],
                'marketing' => [
                    'keywords'  => ['campaign', 'marketing', 'social', 'advertising', 'brand', 'engagement', 'roi'],
                    'responses' => [
                        'The marketing campaign exceeded KPI targets with a {percent}% increase in engagement. Social media reach expanded by {percent}%, driving significant brand awareness.',
                        'ROI analysis shows marketing spend efficiency improved by {percent}%. The multi-channel approach generated {number} new leads with a conversion rate of {percent}%.',
                    ],
                ],
                'default' => [
                    'responses' => [
                        'Based on your request regarding "{prompt}", analysis indicates positive outcomes. Key metrics show improvement across all tracked dimensions with projected growth of {percent}%.',
                        'The data suggests that strategic focus on {prompt} has yielded measurable results. Implementation of recommended actions would further enhance outcomes by an estimated {percent}%.',
                    ],
                ],
            ];

            $category = 'default';
            foreach ($templates as $cat => $config) {
                if ($cat !== 'default' && isset($config['keywords'])) {
                    foreach ($config['keywords'] as $keyword) {
                        if (strpos($prompt, $keyword) !== false) {
                            $category = $cat;
                            break 2;
                        }
                    }
                }
            }

            $responses = $templates[$category]['responses'] ?? $templates['default']['responses'];
            $template  = $responses[array_rand($responses)];
            $percent   = rand(8, 45);
            $number    = rand(5, 50);

            $result = str_replace(
                ['{percent}', '{number}', '{prompt}'],
                [$percent, $number, $request->prompt],
                $template
            );

            $result .= ' ' . [
                'Further analysis is available upon request.',
                'Detailed breakdown by region available.',
                'Comparative data shows consistent improvement.',
                'Recommendations for next quarter have been prepared.',
                'Full report with visualizations is attached.',
            ][array_rand([0, 1, 2, 3, 4])];

            return response()->json(['result' => $result]);
        }

        if ($request->type === 'headline') {
            $templates = [
                'Q{quarter} {year} {topic} Report: Key Insights & Analysis',
                'Breaking Down {topic}: {percent}% Growth Achieved',
                'The State of {topic}: {year} Edition',
                '{topic} Trends: What You Need to Know',
                '{topic} Performance Review: {percent}% Increase',
                'Strategic Analysis: {topic} Market Outlook',
                '{topic} Report: From Data to Decisions',
            ];

            $quarter  = rand(1, 4);
            $year     = date('Y');
            $percent  = rand(10, 75);
            $topic    = ucwords(str_replace(['write', 'generate', 'about', 'for', 'a'], '', $request->prompt));
            if (strlen($topic) < 3) {
                $topic = 'Performance';
            }

            $template = $templates[array_rand($templates)];
            $result   = str_replace(
                ['{quarter}', '{year}', '{topic}', '{percent}'],
                [$quarter, $year, $topic, $percent],
                $template
            );

            return response()->json(['result' => $result]);
        }

        if ($request->type === 'summary') {
            $metrics = [
                'revenue'      => rand(50000, 500000),
                'growth'       => rand(5, 45),
                'customers'    => rand(1000, 50000),
                'satisfaction' => rand(75, 98),
                'efficiency'   => rand(60, 95),
            ];

            $summary  = 'Executive Summary: ';
            $summary .= "Based on the analysis of \"{$request->prompt}\", ";
            $summary .= "the organization achieved {$metrics['growth']}% growth with revenue reaching \${$metrics['revenue']}. ";
            $summary .= "Customer satisfaction stands at {$metrics['satisfaction']}% with {$metrics['customers']}+ active users. ";
            $summary .= "Operational efficiency improved by {$metrics['efficiency']}% through strategic initiatives. ";
            $summary .= 'Key recommendations include leveraging emerging opportunities and optimizing resource allocation for sustained growth.';

            return response()->json(['result' => $summary]);
        }

        if ($request->type === 'chart_data') {
            $chartTypes   = ['bar-chart', 'line-chart', 'area-chart', 'pie-chart'];
            $suggestedType = $chartTypes[array_rand($chartTypes)];

            $periods         = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $selectedPeriods = array_slice($periods, 0, rand(4, 8));

            $baseValue = rand(20000, 100000);
            $values    = [];
            $current   = $baseValue;

            for ($i = 0; $i < count($selectedPeriods); $i++) {
                $change  = rand(-10, 25);
                $current = max(1000, $current + ($current * $change / 100));
                $values[] = round($current);
            }

            $isTrending = end($values) > $values[0];
            $title      = $isTrending
                ? 'Upward Trend in ' . ucwords(str_replace(['generate', 'chart', 'data', 'for'], '', $request->prompt))
                : 'Performance Analysis';
            if (strlen($title) < 5) {
                $title = 'Key Performance Metrics';
            }

            return response()->json([
                'labels'              => $selectedPeriods,
                'values'              => $values,
                'title'               => $title,
                'suggested_chart_type'=> $suggestedType,
                'summary'             => $isTrending ? 'Showing overall growth trend' : 'Stable performance with minor fluctuations',
            ]);
        }

        return response()->json(['result' => 'Analysis complete. The data indicates positive momentum with opportunities for further optimization.']);
    });

    /*
    |======================================================================
    | AI CONTENT ENHANCEMENT
    |======================================================================
    */
    Route::post('/api/ai/enhance', function (Request $request) {
        $request->validate([
            'content' => 'required|string|max:5000',
            'style'   => 'required|in:professional,concise,detailed,persuasive',
        ]);

        $content      = $request->content;
        $style        = $request->style;
        $enhancements = [
            'professional' => [
                'prefix'       => 'Upon review, ',
                'suffix'       => ' This analysis was conducted using industry-standard methodologies.',
                'replacements' => ['good' => 'satisfactory', 'great' => 'excellent', 'bad' => 'suboptimal', 'think' => 'believe', 'show' => 'demonstrate', 'get' => 'obtain'],
            ],
            'concise' => [
                'prefix'       => '',
                'suffix'       => ' In summary, the key takeaways are clear.',
                'replacements' => ['in order to' => 'to', 'due to the fact that' => 'because', 'at this point in time' => 'now', 'a large number of' => 'many', 'in the event that' => 'if'],
            ],
            'detailed' => [
                'prefix'       => 'A comprehensive examination reveals that ',
                'suffix'       => ' Further analysis indicates additional opportunities for optimization.',
                'replacements' => [],
            ],
            'persuasive' => [
                'prefix'       => 'Undoubtedly, ',
                'suffix'       => ' The evidence strongly supports this conclusion.',
                'replacements' => ['good' => 'outstanding', 'important' => 'critical', 'help' => 'empower', 'show' => 'prove', 'think' => 'are confident'],
            ],
        ];

        $config   = $enhancements[$style];
        $enhanced = $config['prefix'] . $content . $config['suffix'];

        foreach ($config['replacements'] as $old => $new) {
            $enhanced = str_ireplace($old, $new, $enhanced);
        }

        return response()->json([
            'original' => $content,
            'enhanced' => $enhanced,
            'style'    => $style,
            'word_count' => [
                'original' => str_word_count($content),
                'enhanced' => str_word_count($enhanced),
            ],
        ]);
    });

    /*
    |======================================================================
    | AI CHART SUGGESTION
    |======================================================================
    */
    Route::post('/api/ai/suggest-chart', function (Request $request) {
        $request->validate(['data' => 'nullable|array']);

        $hasData = ! empty($request->data);

        if ($hasData && count($request->data) > 0) {
            $dataValues = array_values($request->data);
            $avg        = array_sum($dataValues) / count($dataValues);
            $max        = max($dataValues);
            $min        = min($dataValues);
            $range      = $max - $min;

            if ($range / $max < 0.1) {
                $chartType = 'bar-chart';
                $reason    = 'Values are similar, bar chart shows comparison effectively';
            } elseif ($range / $max > 0.5) {
                $chartType = 'line-chart';
                $reason    = 'High variance detected, line chart shows trend clearly';
            } else {
                $chartType = 'area-chart';
                $reason    = 'Moderate variance, area chart emphasizes magnitude';
            }

            $labels        = [];
            $defaultLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($i = 0; $i < count($dataValues); $i++) {
                $labels[] = $defaultLabels[$i % 12] . ' ' . (floor($i / 12) + 1);
            }

            return response()->json([
                'suggested_type' => $chartType,
                'reason'         => $reason,
                'labels'         => $labels,
                'values'         => $dataValues,
                'title'          => 'Data Visualization',
                'insights'       => "Values range from {$min} to {$max} with an average of " . round($avg, 2),
            ]);
        }

        return response()->json([
            'labels'         => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'values'         => [12500, 18200, 15800, 22400, 19600, 28300],
            'title'          => 'Revenue Trend (Suggested)',
            'suggested_type' => 'line-chart',
            'reason'         => 'Line chart best shows the upward trend in revenue over time',
            'insights'       => 'Showing 126% growth from January to June',
        ]);
    });

    /*
    |======================================================================
    | STOCK IMAGE SEARCH
    |======================================================================
    */
    Route::get('/api/unsplash/search', function (Request $request) {
        $query  = $request->get('q', 'business');
        $page   = $request->get('page', 1);
        $images = collect(range(1, 20))->map(function ($i) {
            $seed = $i * 7 + (time() % 100);
            return [
                'id'           => $i,
                'url'          => "https://picsum.photos/800/600?random={$seed}",
                'thumb'        => "https://picsum.photos/200/150?random={$seed}",
                'author'       => 'Free Stock Photo',
                'download_url' => "https://picsum.photos/800/600?random={$seed}",
            ];
        });

        return response()->json(['images' => $images, 'total' => 20, 'page' => $page]);
    });

    /*
    |======================================================================
    | QR CODE GENERATION
    |======================================================================
    */
    Route::post('/api/qr/generate', function (Request $request) {
        $text  = $request->get('text', 'https://example.com');
        $size  = $request->get('size', 200);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($text);
        return response()->json(['qr_url' => $qrUrl, 'text' => $text]);
    });

    /*
    |======================================================================
    | AVAILABLE ICONS LIST
    |======================================================================
    */
    Route::get('/api/icons', function () {
        $icons = [
            'fa-solid fa-star', 'fa-solid fa-heart', 'fa-solid fa-check', 'fa-solid fa-xmark',
            'fa-solid fa-arrow-right', 'fa-solid fa-arrow-left', 'fa-solid fa-arrow-up', 'fa-solid fa-arrow-down',
            'fa-solid fa-phone', 'fa-solid fa-envelope', 'fa-solid fa-location-dot', 'fa-solid fa-globe',
            'fa-solid fa-user', 'fa-solid fa-users', 'fa-solid fa-building', 'fa-solid fa-house',
            'fa-solid fa-gear', 'fa-solid fa-wrench', 'fa-solid fa-magnifying-glass', 'fa-solid fa-filter',
            'fa-solid fa-cloud', 'fa-solid fa-sun', 'fa-solid fa-moon', 'fa-solid fa-bolt',
            'fa-solid fa-fire', 'fa-solid fa-shield', 'fa-solid fa-lock', 'fa-solid fa-key',
            'fa-solid fa-trophy', 'fa-solid fa-gift', 'fa-solid fa-rocket', 'fa-solid fa-lightbulb',
            'fa-solid fa-chart-line', 'fa-solid fa-chart-bar', 'fa-solid fa-chart-pie', 'fa-solid fa-table',
            'fa-solid fa-file-pdf', 'fa-solid fa-file-image', 'fa-solid fa-file-excel', 'fa-solid fa-file-csv',
            'fa-solid fa-download', 'fa-solid fa-upload', 'fa-solid fa-share', 'fa-solid fa-link',
            'fa-solid fa-clock', 'fa-solid fa-calendar', 'fa-solid fa-tag', 'fa-solid fa-hashtag',
            'fa-solid fa-camera', 'fa-solid fa-video', 'fa-solid fa-music', 'fa-solid fa-comment',
            'fa-solid fa-bell', 'fa-solid fa-bookmark', 'fa-solid fa-flag', 'fa-solid fa-thumbs-up',
            'fa-solid fa-circle-check', 'fa-solid fa-circle-xmark', 'fa-solid fa-circle-exclamation',
            'fa-solid fa-circle-info', 'fa-solid fa-circle-question',
        ];
        return response()->json(['icons' => $icons]);
    });

    /*
    |======================================================================
    | SEARCH ENDPOINTS
    |======================================================================
    */
    Route::get('/api/search/users',   [UserController::class, 'search']);
    Route::get('/api/search/reports', [ReportController::class, 'search']);
    Route::get('/api/search/tasks',   [TaskController::class, 'search']);

    /*
    |======================================================================
    | NOTIFICATIONS API
    |======================================================================
    */
    Route::get('/api/notifications',        [DashboardController::class, 'notifications']);
    Route::post('/api/notifications/read',  [DashboardController::class, 'markNotificationsRead']);

    /*
    |======================================================================
    | QUICK STATS API
    |======================================================================
    */
    Route::get('/api/stats/dashboard', [DashboardController::class, 'quickStats']);
    Route::get('/api/stats/reports',   [ReportController::class, 'quickStats']);

    /*
    |======================================================================
    | TASK STATUS API
    |======================================================================
    */
    Route::patch('/api/tasks/{task}/status', [TaskController::class, 'updateStatus']);
});

/*
|--------------------------------------------------------------------------
| HEALTH CHECK
|--------------------------------------------------------------------------
*/
Route::get('/api/health', function () {
    return response()->json([
        'status'    => 'healthy',
        'timestamp' => now(),
        'app_name'  => config('app.name'),
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';