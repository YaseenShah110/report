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
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Landing page - Welcome screen with animated UI
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => app()->version(),
        'phpVersion'     => PHP_VERSION,
    ]);
})->name('home');

// Public Share Links - View/download reports without authentication
Route::prefix('share')->name('reports.')->group(function () {
    
    // Public preview - View a shared report in read-only mode
    Route::get('/{token}', [ReportController::class, 'publicPreview'])
        ->name('public-preview');
    
    // Public download - Download a shared report as PDF
    Route::get('/{token}/download', [ReportController::class, 'publicDownload'])
        ->name('public-download');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (Requires Login + Verified Email)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ── Dashboard ──────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ── Profile Management ─────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ── Notifications ──────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [NotificationController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [NotificationController::class, 'forceDelete'])->name('force-delete');
    });

    // ── Reports CRUD ───────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        
        // Basic CRUD Operations
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report:slug}/edit', [ReportController::class, 'edit'])->name('edit');
        Route::put('/{report:slug}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{report:slug}', [ReportController::class, 'destroy'])->name('destroy');

        // View & Preview
        Route::get('/{report:slug}/preview', [ReportController::class, 'preview'])->name('preview');
        
        // Status Management (draft → published → archived)
        Route::patch('/{report:slug}/status', [ReportController::class, 'updateStatus'])->name('status');
        
        // Duplicate Report
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
        
        // Soft Delete Management
        Route::post('/{report:slug}/restore', [ReportController::class, 'restore'])->withTrashed()->name('restore');
        Route::delete('/{report:slug}/force', [ReportController::class, 'forceDelete'])->withTrashed()->name('force-delete');
        Route::get('/trashed', [ReportController::class, 'trashed'])->name('trashed');
        
        // Reports Assigned to Current User
        Route::get('/assigned', [ReportController::class, 'assignedReports'])->name('assigned');
        
        // Report Assignment Management (per report)
        Route::get('/{report:slug}/assignments', [ReportController::class, 'getAssignments'])->name('assignments');
        Route::post('/{report:slug}/assign', [ReportController::class, 'assignToUser'])->name('assign');
        Route::delete('/{report:slug}/assignments/{assignment}', [ReportController::class, 'removeAssignment'])->name('unassign');

        // Element Presets
        Route::get('/presets/list', [ReportController::class, 'getPresets'])->name('presets');
        Route::post('/presets/save', [ReportController::class, 'savePreset'])->name('presets.save');

        // Report Statistics
        Route::get('/{report:slug}/stats', [ReportController::class, 'reportStats'])->name('stats');
    });

    // ── Templates ──────────────────────────────────────────────
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{template:slug}', [TemplateController::class, 'show'])->name('templates.show');
    Route::get('/templates/{template:slug}/use', [TemplateController::class, 'use'])->name('templates.use');

    // ── My Tasks ───────────────────────────────────────────────
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('admin.tasks.my');

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (Requires Admin or Manager Role)
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('admin')->name('admin.')
        ->middleware(['can:admin|manager'])
        ->group(function () {

        // ── User Management ─────────────────────────────────────
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            
            // Impersonate user
            Route::post('/{user}/impersonate', [UserController::class, 'impersonate'])->name('impersonate');
            
            // User activities
            Route::get('/{user}/activities', [UserController::class, 'userActivities'])->name('activities');
            
            // Bulk operations
            Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulk-delete');
            
            // Export
            Route::get('/export', [UserController::class, 'export'])->name('export');
            
            // Soft delete management
            Route::post('/{user}/restore', [UserController::class, 'restore'])->withTrashed()->name('restore');
            Route::delete('/{user}/force', [UserController::class, 'forceDelete'])->withTrashed()->name('force-delete');
            Route::get('/trashed', [UserController::class, 'trashed'])->name('trashed');
        });

        // ── Role & Permission Management (Admin Only) ───────────
        Route::prefix('roles')->name('roles.')
            ->middleware(['can:admin'])
            ->group(function () {
            
            // Role CRUD
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
            
            // Permission CRUD
            Route::get('/permissions', [RoleController::class, 'permissions'])->name('permissions');
            Route::post('/permissions', [RoleController::class, 'storePermission'])->name('permissions.store');
            Route::put('/permissions/{permission}', [RoleController::class, 'updatePermission'])->name('permissions.update');
            Route::delete('/permissions/{permission}', [RoleController::class, 'destroyPermission'])->name('permissions.destroy');
            
            // Role-Permission Assignment
            Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])->name('assign-permissions');
            Route::delete('/{role}/permissions/{permission}', [RoleController::class, 'removePermission'])->name('remove-permission');
            
            // User-Role Assignment
            Route::post('/assign-to-user', [RoleController::class, 'assignToUser'])->name('assign-to-user');
            Route::delete('/remove-from-user', [RoleController::class, 'removeFromUser'])->name('remove-from-user');
            
            // Setup & Statistics
            Route::post('/setup-default', [RoleController::class, 'setupDefaultRoles'])->name('setup-default');
            Route::get('/stats', [RoleController::class, 'getStats'])->name('stats');
        });

        // ── Task Management ────────────────────────────────────
        Route::prefix('tasks')->name('tasks.')->group(function () {
            
            // Basic CRUD
            Route::get('/', [TaskController::class, 'index'])->name('index');
            Route::get('/create', [TaskController::class, 'create'])->name('create');
            Route::post('/', [TaskController::class, 'store'])->name('store');
            Route::get('/{task}', [TaskController::class, 'show'])->name('show');
            Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
            Route::put('/{task}', [TaskController::class, 'update'])->name('update');
            Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
            
            // Quick Status Update
            Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('status');
            
            // Bulk Operations
            Route::post('/bulk-delete', [TaskController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-assign', [TaskController::class, 'bulkAssign'])->name('bulk-assign');
            Route::post('/bulk-status', [TaskController::class, 'bulkStatus'])->name('bulk-status');
            
            // Export
            Route::get('/export', [TaskController::class, 'export'])->name('export');
            
            // Soft Delete Management
            Route::post('/{task}/restore', [TaskController::class, 'restore'])->withTrashed()->name('restore');
            Route::delete('/{task}/force', [TaskController::class, 'forceDelete'])->withTrashed()->name('force-delete');
            Route::get('/trashed', [TaskController::class, 'trashed'])->name('trashed');
        });

        // ── Report Assignments Management ──────────────────────
        Route::prefix('report-assignments')->name('report-assignments.')->group(function () {
            Route::get('/', [ReportAssignmentController::class, 'index'])->name('index');
            Route::post('/', [ReportAssignmentController::class, 'store'])->name('store');
            Route::delete('/{assignment}', [ReportAssignmentController::class, 'destroy'])->name('destroy');
            Route::patch('/{assignment}/toggle', [ReportAssignmentController::class, 'toggleActive'])->name('toggle');
            Route::get('/export', [ReportAssignmentController::class, 'export'])->name('export');
        });

        // ── Template Management (Admin Only) ───────────────────
        Route::prefix('templates')->name('templates.')
            ->middleware(['can:admin'])
            ->group(function () {
            Route::post('/', [TemplateController::class, 'store'])->name('store');
            Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/restore', [TemplateController::class, 'restore'])->withTrashed()->name('restore');
            Route::delete('/{template}/force', [TemplateController::class, 'forceDelete'])->withTrashed()->name('force-delete');
        });

        // ── Activity Logs ──────────────────────────────────────
        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/user/{user}', [ActivityController::class, 'userActivities'])->name('user');
            Route::delete('/clear', [ActivityController::class, 'clear'])->name('clear');
            Route::get('/export', [ActivityController::class, 'export'])->name('export');
        });

        // ── Analytics Dashboard ─────────────────────────────────
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports');
            Route::get('/users', [AnalyticsController::class, 'users'])->name('users');
            Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
            Route::get('/quick-stats', [AnalyticsController::class, 'quickStats'])->name('quick-stats');
        });
    });
});

/*
|--------------------------------------------------------------------------
| IMPERSONATION STOP ROUTE - Outside admin group
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->post('/admin/users/stop-impersonate', [UserController::class, 'stopImpersonate'])
    ->name('admin.users.stop-impersonate');

/*
|--------------------------------------------------------------------------
| API ROUTES (Application-specific, no external API)
|--------------------------------------------------------------------------
*/

// Image Upload
Route::middleware(['auth', 'verified'])->post('/api/upload-image', function (Request $request) {
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
    } catch (\Exception $e) {
        return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
    }
});

// AI Content Generation
Route::middleware(['auth', 'verified'])->post('/api/ai/generate', function (Request $request) {
    $request->validate([
        'prompt' => 'required|string|max:1000',
        'type'   => 'required|in:text,chart_data,headline,summary',
    ]);

    $prompt = strtolower($request->prompt);

    // Smart Text Generation
    if ($request->type === 'text') {
        $templates = [
            'business' => [
                'keywords'  => ['revenue', 'growth', 'profit', 'sales', 'quarter', 'annual', 'business', 'company'],
                'responses' => [
                    'Based on our analysis, the company has demonstrated consistent growth trajectory over the past fiscal year. Key metrics show a {percent}% increase in revenue, driven by strategic initiatives and market expansion.',
                    'The business performance report indicates strong operational efficiency with EBITDA margins improving by {percent}%. Cost optimization strategies have yielded significant results.',
                    'Market analysis reveals emerging opportunities in the {segment} sector. Our competitive positioning remains strong with projected growth of {percent}% in the coming quarters.',
                ]
            ],
            'marketing' => [
                'keywords'  => ['campaign', 'marketing', 'social', 'advertising', 'brand', 'engagement', 'roi'],
                'responses' => [
                    'The marketing campaign exceeded KPI targets with a {percent}% increase in engagement. Social media reach expanded by {percent}%, driving significant brand awareness.',
                    'ROI analysis shows marketing spend efficiency improved by {percent}%. The multi-channel approach generated {number} new leads with a conversion rate of {percent}%.',
                ]
            ],
            'default' => [
                'responses' => [
                    'Based on your request regarding "{prompt}", analysis indicates positive outcomes. Key metrics show improvement across all tracked dimensions with projected growth of {percent}%.',
                    'The data suggests that strategic focus on {prompt} has yielded measurable results. Implementation of recommended actions would further enhance outcomes by an estimated {percent}%.',
                ]
            ]
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
        $amount    = rand(50, 500);
        $number    = rand(5, 50);
        $segment   = ['enterprise', 'SMB', 'consumer', 'international', 'digital'][array_rand(['enterprise', 'SMB', 'consumer', 'international', 'digital'])];

        $result = str_replace(
            ['{percent}', '{amount}', '{number}', '{segment}', '{prompt}'],
            [$percent, $amount, $number, $segment, $request->prompt],
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

    // Smart Headline Generation
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

        $quarter = rand(1, 4);
        $year    = date('Y');
        $percent = rand(10, 75);
        $topic   = ucwords(str_replace(['write', 'generate', 'about', 'for', 'a'], '', $request->prompt));
        if (strlen($topic) < 3) $topic = 'Performance';

        $template = $templates[array_rand($templates)];
        $result   = str_replace(
            ['{quarter}', '{year}', '{topic}', '{percent}'],
            [$quarter, $year, $topic, $percent],
            $template
        );

        return response()->json(['result' => $result]);
    }

    // Smart Summary Generation
    if ($request->type === 'summary') {
        $metrics = [
            'revenue'      => rand(50000, 500000),
            'growth'       => rand(5, 45),
            'customers'    => rand(1000, 50000),
            'satisfaction' => rand(75, 98),
            'efficiency'   => rand(60, 95),
        ];

        $summary  = "Executive Summary: ";
        $summary .= "Based on the analysis of \"{$request->prompt}\", ";
        $summary .= "the organization achieved {$metrics['growth']}% growth with revenue reaching \${$metrics['revenue']}. ";
        $summary .= "Customer satisfaction stands at {$metrics['satisfaction']}% with {$metrics['customers']}+ active users. ";
        $summary .= "Operational efficiency improved by {$metrics['efficiency']}% through strategic initiatives. ";
        $summary .= "Key recommendations include leveraging emerging opportunities and optimizing resource allocation for sustained growth.";

        return response()->json(['result' => $summary]);
    }

    // Smart Chart Data Generation
    if ($request->type === 'chart_data') {
        $chartTypes    = ['bar-chart', 'line-chart', 'area-chart', 'pie-chart'];
        $suggestedType = $chartTypes[array_rand($chartTypes)];

        $periods         = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $selectedPeriods = array_slice($periods, 0, rand(4, 8));

        $baseValue = rand(20000, 100000);
        $trend     = rand(-15, 30);
        $values    = [];
        $current   = $baseValue;

        for ($i = 0; $i < count($selectedPeriods); $i++) {
            $change   = rand(-10, 25);
            $current  = max(1000, $current + ($current * $change / 100));
            $values[] = round($current);
        }

        $isTrending = end($values) > $values[0];
        $title      = $isTrending
            ? "Upward Trend in " . ucwords(str_replace(['generate', 'chart', 'data', 'for'], '', $request->prompt))
            : "Performance Analysis";
        if (strlen($title) < 5) $title = "Key Performance Metrics";

        return response()->json([
            'labels'               => $selectedPeriods,
            'values'               => $values,
            'title'                => $title,
            'suggested_chart_type' => $suggestedType,
            'summary'              => $isTrending ? "Showing {$trend}% overall growth" : "Stable performance with minor fluctuations",
        ]);
    }

    return response()->json(['result' => 'Analysis complete. The data indicates positive momentum with opportunities for further optimization.']);
});

// AI Content Enhancement
Route::middleware(['auth', 'verified'])->post('/api/ai/enhance', function (Request $request) {
    $request->validate([
        'content' => 'required|string|max:5000',
        'style'   => 'required|in:professional,concise,detailed,persuasive',
    ]);

    $content = $request->content;
    $style   = $request->style;

    $enhancements = [
        'professional' => [
            'prefix'       => 'Upon review, ',
            'suffix'       => ' This analysis was conducted using industry-standard methodologies.',
            'replacements' => [
                'good' => 'satisfactory', 'great' => 'excellent', 'bad' => 'suboptimal',
                'think' => 'believe', 'show' => 'demonstrate', 'get' => 'obtain',
            ]
        ],
        'concise' => [
            'prefix'       => '',
            'suffix'       => ' In summary, the key takeaways are clear.',
            'replacements' => [
                'in order to' => 'to', 'due to the fact that' => 'because', 'at this point in time' => 'now',
                'a large number of' => 'many', 'in the event that' => 'if',
            ]
        ],
        'detailed' => [
            'prefix'       => 'A comprehensive examination reveals that ',
            'suffix'       => ' Further analysis indicates additional opportunities for optimization.',
            'replacements' => []
        ],
        'persuasive' => [
            'prefix'       => 'Undoubtedly, ',
            'suffix'       => ' The evidence strongly supports this conclusion.',
            'replacements' => [
                'good' => 'outstanding', 'important' => 'critical', 'help' => 'empower',
                'show' => 'prove', 'think' => 'are confident',
            ]
        ],
    ];

    $config   = $enhancements[$style];
    $enhanced = $config['prefix'] . $content . $config['suffix'];

    foreach ($config['replacements'] as $old => $new) {
        $enhanced = str_ireplace($old, $new, $enhanced);
    }

    return response()->json([
        'original'   => $content,
        'enhanced'   => $enhanced,
        'style'      => $style,
        'word_count' => [
            'original' => str_word_count($content),
            'enhanced' => str_word_count($enhanced),
        ]
    ]);
});

// AI Chart Suggestion
Route::middleware(['auth', 'verified'])->post('/api/ai/suggest-chart', function (Request $request) {
    $request->validate(['data' => 'nullable|array']);

    $hasData = !empty($request->data);

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

    $sampleData = [
        'labels'         => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'values'         => [12500, 18200, 15800, 22400, 19600, 28300],
        'title'          => 'Revenue Trend (Suggested)',
        'suggested_type' => 'line-chart',
        'reason'         => 'Line chart best shows the upward trend in revenue over time',
        'insights'       => 'Showing 126% growth from January to June',
    ];

    return response()->json($sampleData);
});

// Unsplash/Stock Image Search (Free placeholder images)
Route::middleware(['auth', 'verified'])->get('/api/unsplash/search', function (Request $request) {
    $query  = $request->get('q', 'business');
    $page   = $request->get('page', 1);
    $images = collect(range(1, 20))->map(function ($i) use ($query) {
        $seed = $i * 7 + (time() % 100);
        return [
            'id'           => $i,
            'url'          => "https://picsum.photos/800/600?random={$seed}",
            'thumb'        => "https://picsum.photos/200/150?random={$seed}",
            'author'       => 'Free Stock Photo',
            'download_url' => "https://picsum.photos/800/600?random={$seed}",
        ];
    });

    return response()->json([
        'images' => $images,
        'total'  => 20,
        'page'   => $page,
    ]);
});

// QR Code Generation
Route::middleware(['auth', 'verified'])->post('/api/qr/generate', function (Request $request) {
    $text = $request->get('text', 'https://example.com');
    $size = $request->get('size', 200);
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($text);

    return response()->json([
        'qr_url' => $qrUrl,
        'text'   => $text,
    ]);
});

// Available Icons List
Route::middleware(['auth', 'verified'])->get('/api/icons', function () {
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

// Search Endpoints
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/search/users', [UserController::class, 'search']);
    Route::get('/api/search/reports', [ReportController::class, 'search']);
    Route::get('/api/search/tasks', [TaskController::class, 'search']);
});

// Notifications API
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/notifications', [DashboardController::class, 'notifications']);
    Route::post('/api/notifications/read', [DashboardController::class, 'markNotificationsRead']);
});

// Quick Stats API
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/stats/dashboard', [DashboardController::class, 'quickStats']);
    Route::get('/api/stats/reports', [ReportController::class, 'quickStats']);
});

// Task Status API
Route::middleware(['auth', 'verified'])
    ->patch('/api/tasks/{task}/status', [TaskController::class, 'updateStatus']);

// Health Check
Route::get('/api/health', function () {
    return response()->json([
        'status'    => 'healthy',
        'timestamp' => now(),
        'app_name'  => config('app.name'),
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES - Laravel Breeze/Jetstream
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';