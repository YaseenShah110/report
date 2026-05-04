<?php

/**
 * ================================================================
 * WEB ROUTES - Report Generator Application
 * ================================================================
 * Laravel 12 + Inertia.js + Vue 3 + MySQL
 * 
 * Route Organization:
 * 1. PUBLIC ROUTES - No authentication required
 * 2. AUTHENTICATED ROUTES - Requires login + verified email
 * 3. ADMIN ROUTES - Requires admin or manager role
 * 
 * Soft Delete Support: All major resources support soft deletes
 * - Trash listing, restore, and force delete routes available
 * 
 * Route Model Binding: Reports use slug instead of ID
 * - Better SEO and user-friendly URLs
 * - Prevents ID enumeration attacks
 * 
 * Middleware:
 * - auth: User must be logged in
 * - verified: Email must be verified
 * - role:admin|manager: Admin or Manager role required
 * - role:admin: Only Admin role
 * - can:permission: Specific Spatie permission check
 * ================================================================
 */

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
| PUBLIC ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
| These routes are accessible by anyone, including guests.
| Used for landing page, public report sharing, and health checks.
*/

// Landing page - Welcome screen with animated UI
// Passes boolean flags for conditional login/register links
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),    // Check if login route exists
        'canRegister'    => Route::has('register'),  // Check if register route exists
        'laravelVersion' => app()->version(),        // Show Laravel version in UI
        'phpVersion'     => PHP_VERSION,             // Show PHP version in UI
    ]);
})->name('home');

// Public Share Links - View/download reports without authentication
// Uses a random 32-character token for security (unguessable)
// Token is revoked from admin panel or when report is unshared
Route::prefix('share')->name('reports.')->group(function () {
    
    // Public preview - View a shared report in read-only mode
    // Checks: share_token exists AND is_public is true
    Route::get('/{token}', [ReportController::class, 'publicPreview'])
        ->name('public-preview');
    
    // Public download - Download a shared report as PDF
    // Uses Browsershot for PDF generation (with DomPDF fallback)
    Route::get('/{token}/download', [ReportController::class, 'publicDownload'])
        ->name('public-download');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (Requires Login + Verified Email)
|--------------------------------------------------------------------------
| All routes in this group require:
| - User must be logged in (auth middleware)
| - Email must be verified (verified middleware)
| 
| These routes are available to ALL authenticated users regardless of role.
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ─────────────────────────────────────────────────────────────
    // DASHBOARD - Main landing page after login
    // Shows: reports overview, tasks, charts, recent activities
    // ─────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ─────────────────────────────────────────────────────────────
    // PROFILE MANAGEMENT - User's own profile settings
    // Uses Laravel Breeze conventions
    // ─────────────────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        
        // Show profile edit form (name, email, password change)
        Route::get('/', [ProfileController::class, 'edit'])
            ->name('edit');
        
        // Update profile information
        Route::patch('/', [ProfileController::class, 'update'])
            ->name('update');
        
        // Delete user account (requires password confirmation)
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('destroy');
    });

    // ─────────────────────────────────────────────────────────────
    // NOTIFICATIONS - Real-time notification system
    // Features: List, latest (for dropdown), mark read, mark all read
    //           Soft delete (trash), restore, permanent delete
    // Polling: Frontend polls /notifications/latest every 30 seconds
    // ─────────────────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        
        // View all notifications (paginated with filters)
        Route::get('/', [NotificationController::class, 'index'])
            ->name('index');
        
        // Get latest 10 notifications for dropdown (API endpoint)
        Route::get('/latest', [NotificationController::class, 'latest'])
            ->name('latest');
        
        // Mark single notification as read
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('mark-read');
        
        // Mark ALL notifications as read for current user
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('mark-all-read');
        
        // Soft delete a notification (move to trash)
        Route::delete('/{id}', [NotificationController::class, 'destroy'])
            ->name('destroy');
        
        // Restore a soft-deleted notification from trash
        Route::post('/{id}/restore', [NotificationController::class, 'restore'])
            ->name('restore');
        
        // Permanently delete a notification (cannot be recovered)
        Route::delete('/{id}/force', [NotificationController::class, 'forceDelete'])
            ->name('force-delete');
    });

    // ─────────────────────────────────────────────────────────────
    // REPORTS - Core report management functionality
    // Includes: CRUD, status management, versioning, sharing, export
    // Uses slug for route model binding (instead of numeric ID)
    // Slug is auto-generated from title + random string for uniqueness
    // ─────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        
        // ── Basic CRUD Operations ────────────────────────────────
        
        // List all reports (owned by user + assigned to user)
        Route::get('/', [ReportController::class, 'index'])
            ->name('index');
        
        // Show create report form (template selection + settings)
        Route::get('/create', [ReportController::class, 'create'])
            ->name('create');
        
        // Store new report (from template or blank)
        // Creates initial version snapshot automatically
        Route::post('/', [ReportController::class, 'store'])
            ->name('store');
        
        // Open report in editor (drag-and-drop canvas)
        // Checks: User owns report OR has edit/manage assignment
        Route::get('/{report:slug}/edit', [ReportController::class, 'edit'])
            ->name('edit');
        
        // Update report (auto-save from editor)
        // Throttled: Creates version snapshot only if 5+ minutes since last
        Route::put('/{report:slug}', [ReportController::class, 'update'])
            ->name('update');
        
        // Soft delete report (move to trash)
        // Also soft-deletes related notifications
        Route::delete('/{report:slug}', [ReportController::class, 'destroy'])
            ->name('destroy');

        // ── View & Preview ───────────────────────────────────────
        
        // Preview report (read-only view with download options)
        // Checks: User owns OR has assignment OR report is public
        Route::get('/{report:slug}/preview', [ReportController::class, 'preview'])
            ->name('preview');
        
        // ── Status Management ────────────────────────────────────
        
        // Update report status (draft → published → archived)
        // Only owner or admin can change status
        Route::patch('/{report:slug}/status', [ReportController::class, 'updateStatus'])
            ->name('status');
        
        // ── Duplicate Report ────────────────────────────────────
        
        // Create a copy of an existing report with "(Copy)" suffix
        // Generates new UUIDs for all elements to avoid conflicts
        Route::post('/{report:slug}/duplicate', [ReportController::class, 'duplicate'])
            ->name('duplicate');
        
        // ── Version History ──────────────────────────────────────
        
        // Get version history for a report (last 50 versions)
        Route::get('/{report:slug}/versions', [ReportController::class, 'versions'])
            ->name('versions');
        
        // Restore a previous version (creates new version before restore)
        Route::post('/{report:slug}/versions/{version}/restore', 
            [ReportController::class, 'restoreVersion'])
            ->name('versions.restore');
        
        // ── Share Management ─────────────────────────────────────
        
        // Generate public share link (32-char random token)
        // Anyone with the link can view the report (no login required)
        Route::post('/{report:slug}/share', [ReportController::class, 'generateShareLink'])
            ->name('share');
        
        // Revoke public share link (makes report private again)
        Route::delete('/{report:slug}/share', [ReportController::class, 'revokeShareLink'])
            ->name('share.revoke');
        
        // ── Export Functionality ─────────────────────────────────
        
        // Download as PDF (uses Browsershot primary, DomPDF fallback)
        Route::get('/{report:slug}/download', [ReportController::class, 'download'])
            ->name('download');
        
        // Export as PDF (same as download, different route name)
        Route::get('/{report:slug}/export/pdf', [ReportController::class, 'download'])
            ->name('export.pdf');
        
        // Export as Excel (uses Maatwebsite Excel, CSV fallback)
        Route::get('/{report:slug}/export/excel', [ReportController::class, 'exportExcel'])
            ->name('export.excel');
        
        // Export as CSV (extracts all table and chart data)
        Route::get('/{report:slug}/export/csv', [ReportController::class, 'exportCsv'])
            ->name('export.csv');
        
        // Export as PNG image (uses Browsershot screenshot)
        Route::get('/{report:slug}/export/image', [ReportController::class, 'exportImage'])
            ->name('export.image');
        
        // ── Soft Delete Management ───────────────────────────────
        
        // Restore a soft-deleted report from trash
        // Also restores related notifications
        Route::post('/{report:slug}/restore', [ReportController::class, 'restore'])
            ->withTrashed()  // Allows finding soft-deleted records
            ->name('restore');
        
        // Permanently delete a report (cannot be recovered)
        // Also permanently deletes related notifications
        Route::delete('/{report:slug}/force', [ReportController::class, 'forceDelete'])
            ->withTrashed()
            ->name('force-delete');
        
        // View all trashed (soft-deleted) reports for current user
        Route::get('/trashed', [ReportController::class, 'trashed'])
            ->name('trashed');
        
        // ── Reports Assigned to Current User ─────────────────────
        
        // View reports that other users have shared with you
        // Shows permission level (view/edit/manage) and expiry
        Route::get('/assigned', [ReportController::class, 'assignedReports'])
            ->name('assigned');
        
        // ── Report Assignment Management (per report) ────────────
        
        // Get all assignments for a specific report
        Route::get('/{report:slug}/assignments', [ReportController::class, 'getAssignments'])
            ->name('assignments');
        
        // Assign a report to another user (view/edit/manage permissions)
        // Sends notification to the assigned user
        Route::post('/{report:slug}/assign', [ReportController::class, 'assignToUser'])
            ->name('assign');
        
        // Remove a user's assignment from a report
        Route::delete('/{report:slug}/assignments/{assignment}', 
            [ReportController::class, 'removeAssignment'])
            ->name('unassign');
    });

    // ─────────────────────────────────────────────────────────────
    // TEMPLATES - Report template management
    // Users can view and use templates to create reports
    // Admin can create/edit/delete templates (see admin routes)
    // ─────────────────────────────────────────────────────────────
    
    // View all available templates (gallery view)
    Route::get('/templates', [TemplateController::class, 'index'])
        ->name('templates.index');
    
    // View single template details (API endpoint)
    Route::get('/templates/{template:slug}', [TemplateController::class, 'show'])
        ->name('templates.show');
    
    // Use a template to create a new report (redirects to create page)
    Route::get('/templates/{template:slug}/use', [TemplateController::class, 'use'])
        ->name('templates.use');

    // ─────────────────────────────────────────────────────────────
    // MY TASKS - Tasks assigned to the current user
    // Different from admin tasks (which shows ALL tasks)
    // Orders: Overdue first, then Pending, In Progress, Completed
    // ─────────────────────────────────────────────────────────────
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])
        ->name('admin.tasks.my');

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (Requires Admin or Manager Role)
    |--------------------------------------------------------------------------
    | These routes are protected by role middleware.
    | - Admin: Full access to all admin features including roles/permissions
    | - Manager: Access to users, tasks, reports, analytics (NOT roles)
    | - Regular users: Cannot access any of these routes
    |
    | All routes in this group are prefixed with /admin
    | Route names are prefixed with admin. (e.g., admin.users.index)
    */
    
    Route::prefix('admin')->name('admin.')
        ->middleware(['can:admin|manager'])  // Both admin and manager can access
        ->group(function () {

        // ─────────────────────────────────────────────────────────
        // USER MANAGEMENT (Admin & Manager)
        // Full CRUD + impersonation + bulk operations + export
        // All actions are logged in UserActivity
        // ─────────────────────────────────────────────────────────
        Route::prefix('users')->name('users.')->group(function () {
            
            // List all users (paginated with search/filter/sort)
            Route::get('/', [UserController::class, 'index'])
                ->name('index');
            
            // Show create user form (name, email, password, roles, premium)
            Route::get('/create', [UserController::class, 'create'])
                ->name('create');
            
            // Store new user (auto-verifies email for admin-created users)
            Route::post('/', [UserController::class, 'store'])
                ->name('store');
            
            // Show user details (profile, stats, roles)
            Route::get('/{user}', [UserController::class, 'show'])
                ->name('show');
            
            // Show edit user form (with current roles/stats)
            Route::get('/{user}/edit', [UserController::class, 'edit'])
                ->name('edit');
            
            // Update user details (name, email, roles, premium)
            // Password only updated if provided (optional)
            Route::put('/{user}', [UserController::class, 'update'])
                ->name('update');
            
            // Soft delete user (move to trash)
            // Prevents self-deletion and deleting last admin
            Route::delete('/{user}', [UserController::class, 'destroy'])
                ->name('destroy');
            
            // ── User Actions ────────────────────────────────────
            
            // Impersonate a user (admin logs in as that user)
            // Session stores original user ID for stop-impersonate
            Route::post('/{user}/impersonate', [UserController::class, 'impersonate'])
                ->name('impersonate');
            
            // Get user activity logs (API endpoint)
            Route::get('/{user}/activities', [UserController::class, 'userActivities'])
                ->name('activities');
            
            // ── Bulk Operations ──────────────────────────────────
            
            // Bulk soft delete users (accepts array of user IDs)
            Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])
                ->name('bulk-delete');
            
            // ── Export ───────────────────────────────────────────
            
            // Export users to CSV (with filters)
            Route::get('/export', [UserController::class, 'export'])
                ->name('export');
            
            // ── Soft Delete Management ───────────────────────────
            
            // Restore a soft-deleted user from trash
            Route::post('/{user}/restore', [UserController::class, 'restore'])
                ->withTrashed()  // Allows finding soft-deleted users
                ->name('restore');
            
            // Permanently delete a user (cannot be recovered)
            // Also soft-deletes related reports and tasks
            Route::delete('/{user}/force', [UserController::class, 'forceDelete'])
                ->withTrashed()
                ->name('force-delete');
            
            // View all trashed (soft-deleted) users
            Route::get('/trashed', [UserController::class, 'trashed'])
                ->name('trashed');
        });

        // ─────────────────────────────────────────────────────────
        // ROLE & PERMISSION MANAGEMENT (Admin Only)
        // Uses Spatie Laravel Permission package
        // Manager role CANNOT access these routes
        // ─────────────────────────────────────────────────────────
        Route::prefix('roles')->name('roles.')
            ->middleware(['can:admin'])  // Only admin can manage roles
            ->group(function () {
            
            // ── Role CRUD ────────────────────────────────────────
            
            // List all roles with permissions count
            Route::get('/', [RoleController::class, 'index'])
                ->name('index');
            
            // Show create role form with permission checkboxes
            Route::get('/create', [RoleController::class, 'create'])
                ->name('create');
            
            // Store new role with selected permissions
            Route::post('/', [RoleController::class, 'store'])
                ->name('store');
            
            // Show role details (permissions list, users with this role)
            Route::get('/{role}', [RoleController::class, 'show'])
                ->name('show');
            
            // Show edit role form (current permissions pre-checked)
            Route::get('/{role}/edit', [RoleController::class, 'edit'])
                ->name('edit');
            
            // Update role name and permissions
            // Prevents renaming the admin role
            Route::put('/{role}', [RoleController::class, 'update'])
                ->name('update');
            
            // Delete role (prevents deletion if users are assigned)
            // Cannot delete admin role
            Route::delete('/{role}', [RoleController::class, 'destroy'])
                ->name('destroy');
            
            // ── Permission CRUD ──────────────────────────────────
            
            // View all permissions grouped by category
            Route::get('/permissions', [RoleController::class, 'permissions'])
                ->name('permissions');
            
            // Create new permission (e.g., "edit-reports")
            Route::post('/permissions', [RoleController::class, 'storePermission'])
                ->name('permissions.store');
            
            // Update permission name
            Route::put('/permissions/{permission}', [RoleController::class, 'updatePermission'])
                ->name('permissions.update');
            
            // Delete permission (prevents if assigned to any role)
            Route::delete('/permissions/{permission}', [RoleController::class, 'destroyPermission'])
                ->name('permissions.destroy');
            
            // ── Role-Permission Assignment ───────────────────────
            
            // Bulk assign multiple permissions to a role
            Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])
                ->name('assign-permissions');
            
            // Remove a single permission from a role
            Route::delete('/{role}/permissions/{permission}', [RoleController::class, 'removePermission'])
                ->name('remove-permission');
            
            // ── User-Role Assignment ─────────────────────────────
            
            // Assign a role to a user
            Route::post('/assign-to-user', [RoleController::class, 'assignToUser'])
                ->name('assign-to-user');
            
            // Remove a role from a user
            Route::delete('/remove-from-user', [RoleController::class, 'removeFromUser'])
                ->name('remove-from-user');
            
            // ── Setup & Statistics ───────────────────────────────
            
            // Create default roles (admin, manager, user) with permissions
            // Only available in local environment
            Route::post('/setup-default', [RoleController::class, 'setupDefaultRoles'])
                ->name('setup-default');
            
            // Get role statistics (API endpoint)
            Route::get('/stats', [RoleController::class, 'getStats'])
                ->name('stats');
        });

        // ─────────────────────────────────────────────────────────
        // TASK MANAGEMENT - Admin/Manager view of ALL tasks
        // Includes: CRUD, status updates, bulk operations, export
        // Uses soft deletes with trash/restore/force-delete
        // ─────────────────────────────────────────────────────────
        Route::prefix('tasks')->name('tasks.')->group(function () {
            
            // ── Basic CRUD ───────────────────────────────────────
            
            // List ALL tasks (paginated with filters)
            Route::get('/', [TaskController::class, 'index'])
                ->name('index');
            
            // Show create task form (user selection, report linking)
            Route::get('/create', [TaskController::class, 'create'])
                ->name('create');
            
            // Store new task (sends notification to assigned user)
            Route::post('/', [TaskController::class, 'store'])
                ->name('store');
            
            // Show task details (activity log, related tasks)
            Route::get('/{task}', [TaskController::class, 'show'])
                ->name('show');
            
            // Show edit task form
            Route::get('/{task}/edit', [TaskController::class, 'edit'])
                ->name('edit');
            
            // Update task details (notifies on reassignment/completion)
            Route::put('/{task}', [TaskController::class, 'update'])
                ->name('update');
            
            // Soft delete task (move to trash)
            // Notifies assigned user
            Route::delete('/{task}', [TaskController::class, 'destroy'])
                ->name('destroy');
            
            // ── Quick Status Update (AJAX) ───────────────────────
            
            // Update task status via AJAX (from dropdown)
            // Supports completion notes when marking as completed
            Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])
                ->name('status');
            
            // ── Bulk Operations ──────────────────────────────────
            
            // Bulk soft delete tasks
            Route::post('/bulk-delete', [TaskController::class, 'bulkDelete'])
                ->name('bulk-delete');
            
            // Bulk assign tasks to a user
            Route::post('/bulk-assign', [TaskController::class, 'bulkAssign'])
                ->name('bulk-assign');
            
            // Bulk update task status
            Route::post('/bulk-status', [TaskController::class, 'bulkStatus'])
                ->name('bulk-status');
            
            // ── Export ───────────────────────────────────────────
            
            // Export tasks to CSV (with filters)
            Route::get('/export', [TaskController::class, 'export'])
                ->name('export');
            
            // ── Soft Delete Management ───────────────────────────
            
            // Restore a soft-deleted task from trash
            Route::post('/{task}/restore', [TaskController::class, 'restore'])
                ->withTrashed()
                ->name('restore');
            
            // Permanently delete a task (cannot be recovered)
            Route::delete('/{task}/force', [TaskController::class, 'forceDelete'])
                ->withTrashed()
                ->name('force-delete');
            
            // View all trashed (soft-deleted) tasks
            Route::get('/trashed', [TaskController::class, 'trashed'])
                ->name('trashed');
        });

        // ─────────────────────────────────────────────────────────
        // REPORT ASSIGNMENTS MANAGEMENT (Admin & Manager)
        // Central management of all report sharing/assignments
        // ─────────────────────────────────────────────────────────
        Route::prefix('report-assignments')->name('report-assignments.')->group(function () {
            
            // List all assignments (with filters by report/user)
            Route::get('/', [ReportAssignmentController::class, 'index'])
                ->name('index');
            
            // Create new assignment (or update existing)
            Route::post('/', [ReportAssignmentController::class, 'store'])
                ->name('store');
            
            // Delete an assignment (removes user's access)
            Route::delete('/{assignment}', [ReportAssignmentController::class, 'destroy'])
                ->name('destroy');
            
            // Toggle assignment active/inactive status
            Route::patch('/{assignment}/toggle', [ReportAssignmentController::class, 'toggleActive'])
                ->name('toggle');
            
            // Export assignments to CSV
            Route::get('/export', [ReportAssignmentController::class, 'export'])
                ->name('export');
        });

        // ─────────────────────────────────────────────────────────
        // TEMPLATE MANAGEMENT (Admin Only)
        // CRUD for report templates (structure, settings, etc.)
        // ─────────────────────────────────────────────────────────
        Route::prefix('templates')->name('templates.')
            ->middleware(['can:admin'])  // Only admin can manage templates
            ->group(function () {
            
            // Create new template
            Route::post('/', [TemplateController::class, 'store'])
                ->name('store');
            
            // Update existing template
            Route::put('/{template}', [TemplateController::class, 'update'])
                ->name('update');
            
            // Soft delete template (move to trash)
            Route::delete('/{template}', [TemplateController::class, 'destroy'])
                ->name('destroy');
            
            // Restore soft-deleted template
            Route::post('/{template}/restore', [TemplateController::class, 'restore'])
                ->withTrashed()
                ->name('restore');
            
            // Permanently delete template
            Route::delete('/{template}/force', [TemplateController::class, 'forceDelete'])
                ->withTrashed()
                ->name('force-delete');
        });

        // ─────────────────────────────────────────────────────────
        // ACTIVITY LOGS (Admin & Manager)
        // Track all user actions across the system
        // ─────────────────────────────────────────────────────────
        Route::prefix('activities')->name('activities.')->group(function () {
            
            // List all activities (with filters by user, action, date)
            Route::get('/', [ActivityController::class, 'index'])
                ->name('index');
            
            // Get activities for a specific user
            Route::get('/user/{user}', [ActivityController::class, 'userActivities'])
                ->name('user');
            
            // Clear old activities (based on days parameter)
            Route::delete('/clear', [ActivityController::class, 'clear'])
                ->name('clear');
            
            // Export activities to CSV
            Route::get('/export', [ActivityController::class, 'export'])
                ->name('export');
        });

        // ─────────────────────────────────────────────────────────
        // ANALYTICS DASHBOARD (Admin & Manager)
        // System-wide statistics, charts, and reports
        // ─────────────────────────────────────────────────────────
        Route::prefix('analytics')->name('analytics.')->group(function () {
            
            // Main analytics dashboard (overview with all stats)
            Route::get('/', [AnalyticsController::class, 'index'])
                ->name('index');
            
            // Detailed reports analytics (paginated list)
            Route::get('/reports', [AnalyticsController::class, 'reports'])
                ->name('reports');
            
            // Detailed users analytics (paginated list)
            Route::get('/users', [AnalyticsController::class, 'users'])
                ->name('users');
            
            // Export analytics data to CSV
            Route::get('/export', [AnalyticsController::class, 'export'])
                ->name('export');
            
            // Quick stats API for dashboard widgets
            Route::get('/quick-stats', [AnalyticsController::class, 'quickStats'])
                ->name('quick-stats');
        });
    });
});

/*
|--------------------------------------------------------------------------
| IMPERSONATION STOP ROUTE - Outside admin group
|--------------------------------------------------------------------------
| This route is defined OUTSIDE the admin middleware group.
| Reason: When an admin is impersonating a regular user, the impersonated
| user may NOT have admin role. If this route was inside the admin group,
| the impersonated user wouldn't be able to stop the impersonation.
| 
| Session key 'impersonate' stores the original admin's user ID.
| stopImpersonate() removes this key and redirects to dashboard.
*/
Route::middleware(['auth', 'verified'])
    ->post('/admin/users/stop-impersonate', [UserController::class, 'stopImpersonate'])
    ->name('admin.users.stop-impersonate');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES - Laravel Breeze/Jetstream
|--------------------------------------------------------------------------
| These routes handle authentication:
| - Login / Logout
| - Register
| - Password Reset / Forgot Password
| - Email Verification
| - Password Confirmation
| 
| Defined in a separate file for better organization.
*/
require __DIR__.'/auth.php';