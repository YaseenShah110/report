<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// ─────────────────────────────────────────────────────────────────────────
// PUBLIC API ROUTES (No authentication required)
// ─────────────────────────────────────────────────────────────────────────

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'app_name' => config('app.name'),
    ]);
});

// Public share endpoints
Route::get('/share/{token}', [App\Http\Controllers\ReportController::class, 'publicPreview']);
Route::get('/share/{token}/download', [App\Http\Controllers\ReportController::class, 'publicDownload']);

// ─────────────────────────────────────────────────────────────────────────
// AUTHENTICATED API ROUTES
// ─────────────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // ─────────────────────────────────────────────────────────────────────
    // IMAGE UPLOADS
    // ─────────────────────────────────────────────────────────────────────
    Route::post('/upload-image', function (Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
        ]);

        try {
            $path = $request->file('image')->store('report-images', 'public');
            return response()->json([
                'url' => Storage::url($path),
                'path' => $path,
                'message' => 'Image uploaded successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    });

    // ─────────────────────────────────────────────────────────────────────
    // FREE AI GENERATION - Enhanced with Smart Templates
    // ─────────────────────────────────────────────────────────────────────
    Route::post('/ai/generate', function (Request $request) {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'type'   => 'required|in:text,chart_data,headline,summary',
        ]);

        $prompt = strtolower($request->prompt);
        
        // ─────────────────────────────────────────────────────────────────
        // SMART TEXT GENERATION (Template-based, feels like AI)
        // ─────────────────────────────────────────────────────────────────
        if ($request->type === 'text') {
            $templates = [
                'business' => [
                    'keywords' => ['revenue', 'growth', 'profit', 'sales', 'quarter', 'annual', 'business', 'company'],
                    'responses' => [
                        'Based on our analysis, the company has demonstrated consistent growth trajectory over the past fiscal year. Key metrics show a {percent}% increase in revenue, driven by strategic initiatives and market expansion.',
                        'The business performance report indicates strong operational efficiency with EBITDA margins improving by {percent}%. Cost optimization strategies have yielded significant results.',
                        'Market analysis reveals emerging opportunities in the {segment} sector. Our competitive positioning remains strong with projected growth of {percent}% in the coming quarters.',
                    ]
                ],
                'marketing' => [
                    'keywords' => ['campaign', 'marketing', 'social', 'advertising', 'brand', 'engagement', 'roi'],
                    'responses' => [
                        'The marketing campaign exceeded KPI targets with a {percent}% increase in engagement. Social media reach expanded by {percent}%, driving significant brand awareness.',
                        'ROI analysis shows marketing spend efficiency improved by {percent}%. The multi-channel approach generated {number} new leads with a conversion rate of {percent}%.',
                        'Brand sentiment analysis indicates positive reception with a {percent}% improvement in customer satisfaction scores.',
                    ]
                ],
                'sales' => [
                    'keywords' => ['sales', 'deal', 'pipeline', 'conversion', 'quota', 'customer', 'acquisition'],
                    'responses' => [
                        'Sales pipeline shows strong momentum with {number} qualified opportunities worth ${amount}K. Conversion rates improved by {percent}% this quarter.',
                        'Customer acquisition cost (CAC) decreased by ${amount} while LTV increased by {percent}%, representing excellent unit economics.',
                        'Q4 sales performance exceeded quota by {percent}%. Top performers achieved {percent}% of their annual targets ahead of schedule.',
                    ]
                ],
                'technology' => [
                    'keywords' => ['tech', 'software', 'development', 'platform', 'digital', 'innovation', 'agile'],
                    'responses' => [
                        'The development team delivered {number} features this sprint with {percent}% code coverage. Agile velocity increased by {percent}% month-over-month.',
                        'Platform performance metrics show {percent}% improvement in response times. System uptime maintained at 99.{percent}% with zero critical incidents.',
                        'Innovation pipeline includes {number} initiatives in active development. Technical debt reduced by {percent}% through strategic refactoring.',
                    ]
                ],
                'default' => [
                    'Based on your request regarding "{prompt}", analysis indicates positive outcomes. Key metrics show improvement across all tracked dimensions with projected growth of {percent}%.',
                    'The data suggests that strategic focus on {prompt} has yielded measurable results. Implementation of recommended actions would further enhance outcomes by an estimated {percent}%.',
                ]
            ];
            
            // Detect category based on prompt keywords
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
            
            // Select random response template
            $responses = $templates[$category]['responses'] ?? $templates['default']['responses'];
            $template = $responses[array_rand($responses)];
            
            // Generate dynamic values that feel realistic
            $percent = rand(8, 45);
            $amount = rand(50, 500);
            $number = rand(5, 50);
            $segment = ['enterprise', 'SMB', 'consumer', 'international', 'digital'][array_rand(['enterprise', 'SMB', 'consumer', 'international', 'digital'])];
            
            // Replace placeholders
            $result = str_replace(
                ['{percent}', '{amount}', '{number}', '{segment}', '{prompt}'],
                [$percent, $amount, $number, $segment, $request->prompt],
                $template
            );
            
            // Add a second sentence for completeness
            $result .= ' ' . [
                'Further analysis is available upon request.',
                'Detailed breakdown by region available.',
                'Comparative data shows consistent improvement.',
                'Recommendations for next quarter have been prepared.',
                'Full report with visualizations is attached.',
            ][array_rand([
                'Further analysis is available upon request.',
                'Detailed breakdown by region available.',
                'Comparative data shows consistent improvement.',
                'Recommendations for next quarter have been prepared.',
                'Full report with visualizations is attached.',
            ])];
            
            return response()->json(['result' => $result]);
        }
        
        // ─────────────────────────────────────────────────────────────────
        // SMART HEADLINE GENERATION
        // ─────────────────────────────────────────────────────────────────
        if ($request->type === 'headline') {
            $templates = [
                'Q{quarter} {year} {topic} Report: Key Insights & Analysis',
                'Breaking Down {topic}: {percent}% Growth Achieved',
                'The State of {topic}: 202{year} Edition',
                '{topic} Trends: What You Need to Know',
                '{topic} Performance Review: {percent}% Increase',
                'Strategic Analysis: {topic} Market Outlook',
                '{topic} Report: From Data to Decisions',
            ];
            
            $quarter = rand(1, 4);
            $year = date('Y');
            $percent = rand(10, 75);
            $topic = ucwords(str_replace(['write', 'generate', 'about', 'for', 'a'], '', $request->prompt));
            if (strlen($topic) < 3) $topic = 'Performance';
            
            $template = $templates[array_rand($templates)];
            $result = str_replace(
                ['{quarter}', '{year}', '{topic}', '{percent}'],
                [$quarter, $year, $topic, $percent],
                $template
            );
            
            return response()->json(['result' => $result]);
        }
        
        // ─────────────────────────────────────────────────────────────────
        // SMART SUMMARY GENERATION
        // ─────────────────────────────────────────────────────────────────
        if ($request->type === 'summary') {
            $metrics = [
                'revenue' => rand(50000, 500000),
                'growth' => rand(5, 45),
                'customers' => rand(1000, 50000),
                'satisfaction' => rand(75, 98),
                'efficiency' => rand(60, 95),
            ];
            
            $summary = "Executive Summary: ";
            $summary .= "Based on the analysis of \"{$request->prompt}\", ";
            $summary .= "the organization achieved {$metrics['growth']}% growth with revenue reaching \${$metrics['revenue']}. ";
            $summary .= "Customer satisfaction stands at {$metrics['satisfaction']}% with {$metrics['customers']}+ active users. ";
            $summary .= "Operational efficiency improved by {$metrics['efficiency']}% through strategic initiatives. ";
            $summary .= "Key recommendations include leveraging emerging opportunities and optimizing resource allocation for sustained growth.";
            
            return response()->json(['result' => $summary]);
        }
        
        // ─────────────────────────────────────────────────────────────────
        // SMART CHART DATA GENERATION
        // ─────────────────────────────────────────────────────────────────
        if ($request->type === 'chart_data') {
            $chartTypes = ['bar-chart', 'line-chart', 'area-chart', 'pie-chart'];
            $suggestedType = $chartTypes[array_rand($chartTypes)];
            
            // Generate realistic time-series data
            $periods = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $selectedPeriods = array_slice($periods, 0, rand(4, 8));
            
            $baseValue = rand(20000, 100000);
            $trend = rand(-15, 30);
            $values = [];
            $current = $baseValue;
            
            for ($i = 0; $i < count($selectedPeriods); $i++) {
                $change = rand(-10, 25);
                $current = max(1000, $current + ($current * $change / 100));
                $values[] = round($current);
            }
            
            // Determine if data is seasonal or trending
            $isTrending = end($values) > $values[0];
            $title = $isTrending ? "Upward Trend in " . ucwords(str_replace(['generate', 'chart', 'data', 'for'], '', $request->prompt)) : "Performance Analysis";
            if (strlen($title) < 5) $title = "Key Performance Metrics";
            
            return response()->json([
                'labels' => $selectedPeriods,
                'values' => $values,
                'title' => $title,
                'suggested_chart_type' => $suggestedType,
                'summary' => $isTrending ? "Showing {$trend}% overall growth" : "Stable performance with minor fluctuations",
            ]);
        }
        
        // Fallback
        return response()->json(['result' => 'Analysis complete. The data indicates positive momentum with opportunities for further optimization.']);
    });

    // ─────────────────────────────────────────────────────────────────
    // SMART CHART SUGGESTION
    // ─────────────────────────────────────────────────────────────────
    Route::post('/ai/suggest-chart', function (Request $request) {
        $request->validate([
            'data' => 'nullable|array',
        ]);
        
        $hasData = !empty($request->data);
        
        // Different responses based on whether data is provided
        if ($hasData && count($request->data) > 0) {
            $dataValues = array_values($request->data);
            $avg = array_sum($dataValues) / count($dataValues);
            $max = max($dataValues);
            $min = min($dataValues);
            $range = $max - $min;
            
            if ($range / $max < 0.1) {
                $chartType = 'bar-chart';
                $reason = 'Values are similar, bar chart shows comparison effectively';
            } elseif ($range / $max > 0.5) {
                $chartType = 'line-chart';
                $reason = 'High variance detected, line chart shows trend clearly';
            } else {
                $chartType = 'area-chart';
                $reason = 'Moderate variance, area chart emphasizes magnitude';
            }
            
            // Generate realistic labels based on data count
            $labels = [];
            $defaultLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($i = 0; $i < count($dataValues); $i++) {
                $labels[] = $defaultLabels[$i % 12] . ' ' . (floor($i / 12) + 1);
            }
            
            return response()->json([
                'suggested_type' => $chartType,
                'reason' => $reason,
                'labels' => $labels,
                'values' => $dataValues,
                'title' => 'Data Visualization',
                'insights' => "Values range from {$min} to {$max} with an average of " . round($avg, 2),
            ]);
        }
        
        // Fallback for no data - provide sample
        $sampleData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'values' => [12500, 18200, 15800, 22400, 19600, 28300],
            'title' => 'Revenue Trend (Suggested)',
            'suggested_type' => 'line-chart',
            'reason' => 'Line chart best shows the upward trend in revenue over time',
            'insights' => 'Showing 126% growth from January to June',
        ];
        
        return response()->json($sampleData);
    });

    // ─────────────────────────────────────────────────────────────────────
    // SEARCH ENDPOINTS
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/search/users', [App\Http\Controllers\Admin\UserController::class, 'search']);
    Route::get('/search/reports', [App\Http\Controllers\ReportController::class, 'search']);
    Route::get('/search/tasks', [App\Http\Controllers\Admin\TaskController::class, 'search']);

    // ─────────────────────────────────────────────────────────────────────
    // NOTIFICATIONS
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/notifications', [App\Http\Controllers\DashboardController::class, 'notifications']);
    Route::post('/notifications/read', [App\Http\Controllers\DashboardController::class, 'markNotificationsRead']);

    // ─────────────────────────────────────────────────────────────────────
    // QUICK STATS
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/stats/dashboard', [App\Http\Controllers\DashboardController::class, 'quickStats']);
    Route::get('/stats/reports', [App\Http\Controllers\ReportController::class, 'quickStats']);

    // ─────────────────────────────────────────────────────────────────────
    // REPORT ASSIGNMENTS API
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/reports/{report:slug}/assignments', [App\Http\Controllers\ReportController::class, 'getAssignments']);
    Route::post('/reports/{report:slug}/assign', [App\Http\Controllers\ReportController::class, 'assignToUser']);
    Route::delete('/reports/{report:slug}/assignments/{assignment}', [App\Http\Controllers\ReportController::class, 'removeAssignment']);

    // ─────────────────────────────────────────────────────────────────────
    // REPORT VERSIONS API
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/reports/{report:slug}/versions', [App\Http\Controllers\ReportController::class, 'versions']);
    Route::post('/reports/{report:slug}/versions/{version}/restore', [App\Http\Controllers\ReportController::class, 'restoreVersion']);

    // ─────────────────────────────────────────────────────────────────────
    // TASK STATUS API
    // ─────────────────────────────────────────────────────────────────────
    Route::patch('/tasks/{task}/status', [App\Http\Controllers\Admin\TaskController::class, 'updateStatus']);

    // ─────────────────────────────────────────────────────────────────────
    // USER ACTIVITIES API (Admin only)
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/users/{user}/activities', [App\Http\Controllers\Admin\UserController::class, 'activities'])
        ->middleware(['can:manage-users']);

    // ─────────────────────────────────────────────────────────────────────
    // REPORT ASSIGNMENTS TOGGLE (Admin only)
    // ─────────────────────────────────────────────────────────────────────
    Route::patch('/report-assignments/{assignment}/toggle', [App\Http\Controllers\Admin\ReportAssignmentController::class, 'toggleActive'])
        ->middleware(['can:manage-reports']);
    
    // ─────────────────────────────────────────────────────────────────────
    // CONTENT ENHANCEMENT (Grammar, Tone, Length)
    // ─────────────────────────────────────────────────────────────────────
    Route::post('/ai/enhance', function (Request $request) {
        $request->validate([
            'content' => 'required|string|max:5000',
            'style' => 'required|in:professional,concise,detailed,persuasive',
        ]);
        
        $content = $request->content;
        $style = $request->style;
        
        $enhancements = [
            'professional' => [
                'prefix' => 'Upon review, ',
                'suffix' => ' This analysis was conducted using industry-standard methodologies.',
                'replacements' => [
                    'good' => 'satisfactory', 'great' => 'excellent', 'bad' => 'suboptimal',
                    'think' => 'believe', 'show' => 'demonstrate', 'get' => 'obtain',
                ]
            ],
            'concise' => [
                'prefix' => '',
                'suffix' => ' In summary, the key takeaways are clear.',
                'replacements' => [
                    'in order to' => 'to', 'due to the fact that' => 'because', 'at this point in time' => 'now',
                    'a large number of' => 'many', 'in the event that' => 'if',
                ]
            ],
            'detailed' => [
                'prefix' => 'A comprehensive examination reveals that ',
                'suffix' => ' Further analysis indicates additional opportunities for optimization.',
                'replacements' => []
            ],
            'persuasive' => [
                'prefix' => 'Undoubtedly, ',
                'suffix' => ' The evidence strongly supports this conclusion.',
                'replacements' => [
                    'good' => 'outstanding', 'important' => 'critical', 'help' => 'empower',
                    'show' => 'prove', 'think' => 'are confident',
                ]
            ],
        ];
        
        $config = $enhancements[$style];
        $enhanced = $config['prefix'] . $content . $config['suffix'];
        
        foreach ($config['replacements'] as $old => $new) {
            $enhanced = str_ireplace($old, $new, $enhanced);
        }
        
        return response()->json([
            'original' => $content,
            'enhanced' => $enhanced,
            'style' => $style,
            'word_count' => [
                'original' => str_word_count($content),
                'enhanced' => str_word_count($enhanced),
            ]
        ]);
    });
});