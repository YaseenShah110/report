<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
/**
 * TemplateSeeder — 6 World-Class Professional Report Templates
 *
 * Each template is:
 * - 100% dynamic (every element editable in EditorCanvas)
 * - Professionally designed with real-world layouts
 * - Isolated from global app styles (self-contained CSS vars)
 * - Organized across 2 pages with all supported element types
 *
 * Templates included:
 * 1. Annual Business Report (Corporate Dark Indigo)
 * 2. Financial Dashboard (Green Emerald Finance)
 * 3. Marketing Campaign Report (Bold Orange Creative)
 * 4. Executive Proposal (Clean Navy Professional)
 * 5. Analytics Intelligence Report (Dark Violet Data)
 * 6. Product Launch Report (Vibrant Gradient Modern)
 */
class TemplateSeeder extends Seeder
{
    /* ─────────────────────────────────────────────────────────────────
     | PAGE DIMENSIONS (A4 portrait: 794 × 1123 px @ 96dpi)
     | margin: 48px  →  safe inner area: 698 × 1027 px
     ──────────────────────────────────────────────────────────────── */

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    Template::truncate(); // Now this works
    
    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
        

        foreach ($this->templates() as $tpl) {
            Template::create([
                'name'           => $tpl['name'],
                'slug'           => Str::slug($tpl['name']) . '-' . Str::random(4),
                'description'    => $tpl['description'],
                'thumbnail'      => null,
                'badge'          => $tpl['badge'],
                'category'       => $tpl['category'],
                'tags'           => $tpl['tags'],
                'cover_gradient' => $tpl['cover_gradient'],
                'structure'      => ['pages' => $tpl['pages']],
                'settings'       => $tpl['settings'],
                'is_active'      => true,
            ]);
        }

        $this->command->info('✅  6 world-class templates seeded.');
    }

    /* ═══════════════════════════════════════════════════════════════
     | TEMPLATE DEFINITIONS
     ═══════════════════════════════════════════════════════════════ */

    private function templates(): array
    {
        return [
            $this->annualBusinessReport(),
            $this->financialDashboard(),
            $this->marketingCampaignReport(),
            $this->executiveProposal(),
            $this->analyticsIntelligenceReport(),
            $this->productLaunchReport(),
        ];
    }

    /* ───────────────────────────────────────────────────────────────
     | 1. ANNUAL BUSINESS REPORT — Corporate Dark Indigo
     ─────────────────────────────────────────────────────────────── */
    private function annualBusinessReport(): array
    {
        $primary = '#6366f1';
        $dark    = '#0f172a';
        $accent  = '#8b5cf6';
        $light   = '#f8fafc';

        return [
            'name'           => 'Annual Business Report',
            'description'    => 'Premium dark-themed corporate annual report with KPI cards, revenue charts, department breakdown table and executive summary.',
            'badge'          => 'Popular',
            'category'       => 'Business',
            'tags'           => ['annual', 'corporate', 'dark', 'executive', 'kpi'],
            'cover_gradient' => "linear-gradient(135deg, {$dark} 0%, #1e293b 60%, {$primary} 100%)",
            'settings'       => [
                'page_size'        => 'A4',
                'orientation'      => 'portrait',
                'primary_color'    => $primary,
                'accent_color'     => $accent,
                'background_color' => $dark,
                'text_color'       => '#e2e8f0',
                'font_family'      => "'DM Sans', sans-serif",
                'font_size'        => 14,
                'margin'           => 48,
                'page_radius'      => 0,
                'show_header'      => false,
                'show_footer'      => true,
                'footer_left'      => 'Annual Business Report 2024',
                'footer_right'     => 'Confidential',
                'show_page_numbers'=> true,
                'watermark'        => '',
                'rtl'              => false,
            ],
            'pages' => [
                // ── Page 1: Cover ────────────────────────────────
                [
                    'id'       => 'p1',
                    'label'    => 'Cover',
                    'elements' => [

                        // Full-page background
                        $this->rect('bg-full', 0, 0, 794, 1123, $dark, 0),

                        // Left accent bar
                        $this->rect('accent-bar', 0, 0, 6, 1123, $primary, 2),

                        // Top-right decorative circle (large, faded)
                        $this->circle('deco-circle-1', 560, -120, 340, 340, $primary, 1, 7),

                        // Secondary circle
                        $this->circle('deco-circle-2', 640, 80, 180, 180, $accent, 1, 5),

                        // Company logo area
                        $this->rect('logo-bg', 48, 52, 52, 52, $primary, 3, 10),
                        $this->text('logo-text', 'CO', 60, 65, 30, 30, '#ffffff', 16, '800', 'center', 4),
                        $this->text('company-name', 'Company Name', 112, 68, 200, 26, '#94a3b8', 13, '600', 'left', 4),

                        // Decorative divider line
                        $this->rect('cover-divider', 48, 260, 120, 3, $primary, 3),

                        // Report type label
                        $this->text('report-type', 'ANNUAL REPORT · 2024', 48, 230, 400, 22, $primary, 11, '700', 'left', 3, 3),

                        // Main headline
                        $this->heading('main-title', "Business\nPerformance\nReport", 48, 274, 600, 220, '#f1f5f9', 58, '800', 'left', 3, 1.1),

                        // Subtitle
                        $this->text('subtitle', 'Strategic Overview & Performance Metrics — Fiscal Year 2024', 48, 504, 580, 28, '#94a3b8', 14, '400', 'left', 3),

                        // KPI strip — 3 cards
                        $this->kpiCard('kpi-1', 48,  580, 200, 110, '$4.8M',  'Total Revenue',  '+18.2%', 'positive', '#6366f1', '#1e293b', 12),
                        $this->kpiCard('kpi-2', 264, 580, 200, 110, '12,400', 'Active Customers','+24.1%', 'positive', '#10b981', '#1e293b', 12),
                        $this->kpiCard('kpi-3', 480, 580, 200, 110, '94%',   'NPS Score',      '+6pts',  'positive', '#f59e0b', '#1e293b', 12),

                        // Cover image placeholder
                        $this->image('cover-img', 48, 720, 698, 280),

                        // Bottom bar
                        $this->rect('bottom-bar', 0, 1060, 794, 63, '#1e1b4b', 3),
                        $this->text('bottom-txt', 'CONFIDENTIAL  ·  INTERNAL DISTRIBUTION ONLY  ·  DO NOT SHARE', 0, 1080, 794, 20, $primary, 10, '700', 'center', 4, 2),
                    ],
                ],

                // ── Page 2: Performance ──────────────────────────
                [
                    'id'       => 'p2',
                    'label'    => 'Performance Overview',
                    'elements' => [
                        $this->rect('p2-bg', 0, 0, 794, 1123, $dark, 0),
                        $this->rect('p2-accent', 0, 0, 6, 1123, $primary, 2),

                        // Section heading
                        $this->heading('p2-title', 'Performance Overview', 48, 48, 600, 48, '#f1f5f9', 26, '700', 'left', 3),
                        $this->text('p2-sub', 'Key metrics and financial highlights for fiscal year 2024', 48, 100, 550, 22, '#64748b', 12, '400', 'left', 3),

                        // Divider
                        $this->rect('p2-div', 48, 130, 698, 1, '#334155', 3),

                        // Bar chart — Quarterly Revenue
                        $this->barChart('rev-chart', 48, 150, 460, 280, 'Quarterly Revenue', ['Q1', 'Q2', 'Q3', 'Q4'], [980000, 1150000, 1320000, 1350000], $primary, 'Revenue ($)', 3),

                        // Line chart — Monthly Growth
                        $this->lineChart('growth-chart', 48, 460, 460, 220, 'Monthly Growth %', ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], [5,7,6,9,11,10,14,13,16,15,18,20], '#10b981', 3),

                        // Stat row
                        $this->statRow('stats', 530, 150, 220, 90, [
                            ['value' => '$4.8M',   'label' => 'Revenue'],
                            ['value' => '32%',     'label' => 'Margin'],
                            ['value' => '12,400',  'label' => 'Customers'],
                        ], '#1e293b', $primary, 3),

                        // Pie chart — Revenue by Segment
                        $this->pieChart('seg-pie', 530, 260, 220, 220, 'Revenue Split', ['Enterprise','SMB','Startup'], [55, 30, 15], ['#6366f1','#10b981','#f59e0b'], 3),

                        // Department table
                        $this->table(
                            'dept-table', 48, 710, 698, 200,
                            ['Department', 'Budget', 'Actual', 'Variance', 'Status'],
                            [
                                ['Department' => 'Engineering',  'Budget' => '$1.20M', 'Actual' => '$1.10M', 'Variance' => '+8.3%',  'Status' => '✅ Under'],
                                ['Department' => 'Marketing',    'Budget' => '$480K',  'Actual' => '$510K',  'Variance' => '-6.3%',  'Status' => '⚠️ Over'],
                                ['Department' => 'Operations',   'Budget' => '$620K',  'Actual' => '$590K',  'Variance' => '+4.8%',  'Status' => '✅ Under'],
                                ['Department' => 'HR & Admin',   'Budget' => '$310K',  'Actual' => '$295K',  'Variance' => '+4.8%',  'Status' => '✅ Under'],
                            ],
                            $primary, '#0f172a', '#1e293b', 3
                        ),
                    ],
                ],
            ],
        ];
    }

    /* ───────────────────────────────────────────────────────────────
     | 2. FINANCIAL DASHBOARD — Emerald Green Finance
     ─────────────────────────────────────────────────────────────── */
    private function financialDashboard(): array
    {
        $primary = '#10b981';
        $dark    = '#064e3b';
        $accent  = '#059669';
        $bg      = '#ffffff';

        return [
            'name'           => 'Financial Dashboard',
            'description'    => 'Clean emerald financial dashboard with profit & loss, cash flow chart, expense breakdown doughnut and financial summary table.',
            'badge'          => 'Finance',
            'category'       => 'Finance',
            'tags'           => ['financial', 'dashboard', 'green', 'p&l', 'cashflow'],
            'cover_gradient' => "linear-gradient(135deg, {$dark} 0%, {$primary} 100%)",
            'settings'       => [
                'page_size'        => 'A4',
                'orientation'      => 'portrait',
                'primary_color'    => $primary,
                'accent_color'     => $accent,
                'background_color' => $bg,
                'text_color'       => '#0f172a',
                'font_family'      => "'Inter', sans-serif",
                'font_size'        => 13,
                'margin'           => 48,
                'page_radius'      => 0,
                'show_header'      => true,
                'header_text'      => 'Financial Performance Report — Q4 2024',
                'header_color'     => $dark,
                'show_footer'      => true,
                'footer_left'      => 'Finance Division',
                'footer_right'     => 'Page {n}',
                'show_page_numbers'=> true,
                'watermark'        => '',
            ],
            'pages' => [
                [
                    'id'       => 'fp1',
                    'label'    => 'P&L Summary',
                    'elements' => [
                        // Header stripe
                        $this->rect('fh-stripe', 0, 0, 794, 8, $primary, 2),

                        // Title block
                        $this->heading('fh-title', 'Financial Performance', 48, 68, 500, 44, '#0f172a', 28, '700', 'left', 3),
                        $this->text('fh-period', 'Q4 2024 · October — December', 48, 116, 400, 22, '#64748b', 12, '500', 'left', 3),
                        $this->rect('fh-divider', 48, 144, 80, 4, $primary, 3, 0, 99),

                        // 4 KPI cards across top
                        $this->kpiCard('fkpi-1', 48,  164, 153, 100, '$12.4M', 'Total Revenue',  '+22%',   'positive', $primary, '#f0fdf4', 10),
                        $this->kpiCard('fkpi-2', 213, 164, 153, 100, '$4.1M',  'Net Profit',     '+15%',   'positive', '#059669', '#f0fdf4', 10),
                        $this->kpiCard('fkpi-3', 378, 164, 153, 100, '$8.3M',  'Total Expenses', '-3%',    'negative', '#ef4444', '#fff1f2', 10),
                        $this->kpiCard('fkpi-4', 543, 164, 153, 100, '33%',    'Profit Margin',  '+4pts',  'positive', '#3b82f6', '#eff6ff', 10),

                        // Area chart — Cash Flow
                        $this->areaChart('cf-chart', 48, 290, 460, 240, 'Monthly Cash Flow', ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], [210,195,280,310,260,340,390,370,420,400,480,510], $primary, 3),

                        // Doughnut — Expense Breakdown
                        $this->doughnutChart('exp-doughnut', 530, 290, 216, 240, 'Expense Breakdown', ['COGS','R&D','Sales','G&A','Other'], [30,22,28,13,7], ['#10b981','#059669','#34d399','#6ee7b7','#a7f3d0'], 3),

                        // Progress bars — Budget Utilization
                        $this->heading('budget-head', 'Budget Utilization', 48, 556, 400, 28, '#0f172a', 14, '700', 'left', 3),
                        $this->progress('prog-rev',  48,  590, 698, 28, 'Revenue Target',     88, $primary, '#e2e8f0', 3),
                        $this->progress('prog-exp',  48,  628, 698, 28, 'Expense Control',    72, '#059669', '#e2e8f0', 3),
                        $this->progress('prog-prof', 48,  666, 698, 28, 'Profit Target',      95, '#3b82f6', '#e2e8f0', 3),
                        $this->progress('prog-cust', 48,  704, 698, 28, 'Customer Acquisition',64, '#f59e0b', '#e2e8f0', 3),

                        // P&L Summary Table
                        $this->heading('pl-head', 'P&L Summary', 48, 756, 400, 28, '#0f172a', 14, '700', 'left', 3),
                        $this->table(
                            'pl-table', 48, 786, 698, 220,
                            ['Line Item', 'Q3 2024', 'Q4 2024', 'YoY Change', 'Target'],
                            [
                                ['Line Item' => 'Total Revenue',    'Q3 2024' => '$11.2M', 'Q4 2024' => '$12.4M', 'YoY Change' => '+22.0%', 'Target' => '$12.0M'],
                                ['Line Item' => 'Cost of Goods',    'Q3 2024' => '$3.9M',  'Q4 2024' => '$3.7M',  'YoY Change' => '-5.1%',  'Target' => '$3.8M'],
                                ['Line Item' => 'Gross Profit',     'Q3 2024' => '$7.3M',  'Q4 2024' => '$8.7M',  'YoY Change' => '+19.2%', 'Target' => '$8.2M'],
                                ['Line Item' => 'Operating Expenses','Q3 2024'=> '$4.0M',  'Q4 2024' => '$4.6M',  'YoY Change' => '+15.0%', 'Target' => '$4.5M'],
                                ['Line Item' => 'Net Income',       'Q3 2024' => '$3.3M',  'Q4 2024' => '$4.1M',  'YoY Change' => '+24.2%', 'Target' => '$3.7M'],
                            ],
                            $dark, '#f0fdf4', '#ecfdf5', 3
                        ),
                    ],
                ],

                [
                    'id'       => 'fp2',
                    'label'    => 'Investment & Forecast',
                    'elements' => [
                        $this->rect('fp2-stripe', 0, 0, 794, 8, $primary, 2),
                        $this->heading('fp2-title', 'Investment & 2025 Forecast', 48, 68, 600, 40, '#0f172a', 24, '700', 'left', 3),
                        $this->text('fp2-sub', 'Capital allocation and forward-looking performance projections', 48, 112, 500, 22, '#64748b', 12, '400', 'left', 3),
                        $this->rect('fp2-div', 48, 140, 80, 4, $primary, 3, 0, 99),

                        $this->barChart('inv-chart', 48, 160, 698, 280, 'Revenue Forecast 2025 (USD Million)', ['Q1','Q2','Q3','Q4'], [13.2, 14.8, 15.6, 17.1], $primary, 'Projected Revenue', 3),

                        $this->statRow('inv-stats', 48, 468, 698, 80, [
                            ['value' => '$17.1M', 'label' => 'Q4 Target'],
                            ['value' => '38%',    'label' => 'YoY Growth'],
                            ['value' => '$2.4M',  'label' => 'R&D Budget'],
                            ['value' => '4.2x',   'label' => 'ROI Multiple'],
                        ], '#f0fdf4', $primary, 3),

                        $this->callout('inv-note', 48, 570, 698, 70, '💡', 'Strong Q4 performance positions the company well for 2025 expansion. Board has approved $2.4M additional R&D investment to accelerate product roadmap.', '#f0fdf4', $primary, '#059669', 3),

                        $this->lineChart('fcst-line', 48, 660, 698, 240, '3-Year Revenue Projection', ['2022','2023','2024','2025E','2026E','2027E'], [6.2, 8.8, 12.4, 17.1, 23.5, 31.0], $primary, 3),

                        $this->text('fcst-note', 'Projections based on 35% CAGR assumption. Market conditions and execution risks apply.', 48, 918, 698, 18, '#94a3b8', 10, '400', 'center', 3),
                    ],
                ],
            ],
        ];
    }

    /* ───────────────────────────────────────────────────────────────
     | 3. MARKETING CAMPAIGN REPORT — Bold Orange Creative
     ─────────────────────────────────────────────────────────────── */
    private function marketingCampaignReport(): array
    {
        $primary = '#f59e0b';
        $dark    = '#1e293b';
        $accent  = '#f97316';
        $bg      = '#0f172a';

        return [
            'name'           => 'Marketing Campaign Report',
            'description'    => 'Bold creative marketing report with campaign ROI metrics, channel performance radar, audience breakdown and social media analytics.',
            'badge'          => 'Creative',
            'category'       => 'Marketing',
            'tags'           => ['marketing', 'campaign', 'roi', 'social', 'creative', 'bold'],
            'cover_gradient' => "linear-gradient(135deg, {$bg} 0%, {$dark} 50%, {$accent} 100%)",
            'settings'       => [
                'page_size'        => 'A4',
                'orientation'      => 'portrait',
                'primary_color'    => $primary,
                'accent_color'     => $accent,
                'background_color' => $bg,
                'text_color'       => '#f8fafc',
                'font_family'      => "'DM Sans', sans-serif",
                'font_size'        => 14,
                'margin'           => 48,
                'show_header'      => false,
                'show_footer'      => true,
                'footer_left'      => 'Marketing Division · Campaign Report 2024',
                'footer_right'     => 'Confidential',
                'show_page_numbers'=> true,
            ],
            'pages' => [
                [
                    'id'       => 'mp1',
                    'label'    => 'Campaign Overview',
                    'elements' => [
                        $this->rect('m-bg', 0, 0, 794, 1123, $bg, 0),

                        // Bold top section
                        $this->rect('m-hero', 0, 0, 794, 380, $dark, 1),
                        $this->rect('m-hero-accent', 0, 0, 794, 6, $primary, 2),

                        // Decorative circles
                        $this->circle('m-c1', -60,  -60,  260, 260, $primary, 1, 6),
                        $this->circle('m-c2', 600,  200,  180, 180, $accent,  1, 5),

                        // Campaign tag
                        $this->text('m-tag', 'Q4 2024 CAMPAIGN REPORT', 48, 60, 400, 20, $primary, 10, '800', 'left', 3, 3),

                        // Giant hero title
                        $this->heading('m-title', "Campaign\nPerformance", 48, 86, 600, 180, '#f8fafc', 56, '900', 'left', 3, 1.0),

                        // Campaign summary
                        $this->text('m-summary', 'A comprehensive analysis of all marketing initiatives, ROI performance, and channel effectiveness across digital and offline channels.', 48, 270, 560, 52, '#94a3b8', 13, '400', 'left', 3, 1.6),

                        // 4 KPI cards in hero area
                        $this->kpiCard('mkpi-1', 48,  340, 155, 108, '340%',  'Campaign ROI',    '+85pts', 'positive', $primary, '#1e293b', 11),
                        $this->kpiCard('mkpi-2', 215, 340, 155, 108, '2.4M',  'Total Reach',     '+38%',   'positive', '#10b981', '#1e293b', 11),
                        $this->kpiCard('mkpi-3', 382, 340, 155, 108, '8.2%',  'Conv. Rate',      '+2.1%',  'positive', '#3b82f6', '#1e293b', 11),
                        $this->kpiCard('mkpi-4', 549, 340, 155, 108, '$480K', 'Ad Spend',        'On Budget','positive','#f97316','#1e293b', 11),

                        // Channel Performance Heading
                        $this->heading('m-ch-head', 'Channel Performance', 48, 480, 500, 32, '#f8fafc', 16, '700', 'left', 3),

                        // Bar chart — Channel Revenue
                        $this->barChart('m-bar', 48, 520, 390, 240, 'Revenue by Channel', ['Email','Social','PPC','SEO','Content','Referral'], [124000,98000,145000,87000,62000,44000], $primary, 'Revenue ($)', 3),

                        // Radar chart — Channel Effectiveness
                        $this->radarChart('m-radar', 460, 520, 286, 240, 'Channel Effectiveness', ['Reach','Engagement','Conv.','Retention','ROI','Brand'], [88, 72, 65, 80, 90, 76], $primary, 3),

                        // Timeline — Campaign milestones
                        $this->timeline('m-timeline', 48, 790, 698, 200,
                            [
                                ['date' => 'Oct 1',  'label' => 'Campaign Launch',         'desc' => 'Multi-channel campaign rolled out across all platforms'],
                                ['date' => 'Oct 15', 'label' => 'Mid-Campaign Optimization','desc' => 'Reallocated budget to top-performing channels +22% lift'],
                                ['date' => 'Nov 1',  'label' => 'Black Friday Push',        'desc' => 'Highest single-day conversion rate recorded: 12.4%'],
                                ['date' => 'Dec 31', 'label' => 'Campaign Closed',          'desc' => 'Final ROI: 340% · Total revenue attributed: $1.84M'],
                            ],
                            $primary, 3
                        ),
                    ],
                ],

                [
                    'id'       => 'mp2',
                    'label'    => 'Audience & Social',
                    'elements' => [
                        $this->rect('mp2-bg', 0, 0, 794, 1123, $bg, 0),
                        $this->rect('mp2-stripe', 0, 0, 6, 1123, $primary, 2),

                        $this->heading('mp2-title', 'Audience & Social Media', 48, 48, 500, 40, '#f8fafc', 22, '700', 'left', 3),
                        $this->text('mp2-sub', 'Audience demographics, engagement metrics and platform breakdown', 48, 92, 500, 20, '#64748b', 12, '400', 'left', 3),
                        $this->rect('mp2-div', 48, 118, 698, 1, '#334155', 3),

                        // Pie chart — audience demographics
                        $this->pieChart('mp2-pie', 48, 138, 240, 240, 'Age Demographics', ['18-24','25-34','35-44','45+'], [28,38,22,12], ['#f59e0b','#f97316','#ef4444','#8b5cf6'], 3),

                        // Social media stats
                        $this->statRow('soc-stats', 310, 138, 440, 80, [
                            ['value' => '2.4M', 'label' => 'Total Impressions'],
                            ['value' => '8.6%', 'label' => 'Avg Engagement'],
                            ['value' => '48K',  'label' => 'New Followers'],
                        ], $dark, $primary, 3),

                        $this->barChart('soc-bar', 310, 230, 440, 148, 'Followers by Platform', ['Instagram','LinkedIn','Twitter','Facebook','YouTube'], [24000,18000,8600,5400,3200], $accent, 'Followers', 3),

                        // Engagement line chart
                        $this->lineChart('eng-line', 48, 400, 698, 200, 'Weekly Engagement Rate (%)', ['Wk1','Wk2','Wk3','Wk4','Wk5','Wk6','Wk7','Wk8','Wk9','Wk10','Wk11','Wk12'], [5.2,5.8,6.1,7.4,8.9,8.2,9.6,10.1,9.4,11.2,10.8,12.3], $primary, 3),

                        // Checklist — campaign goals
                        $this->heading('goals-head', 'Campaign Goals Achieved', 48, 628, 400, 28, '#f8fafc', 14, '700', 'left', 3),
                        $this->checklist('goals-list', 48, 662, 320, 180, [
                            ['text' => 'Reach 2M+ unique impressions', 'checked' => true],
                            ['text' => 'Achieve 300%+ ROI',            'checked' => true],
                            ['text' => 'Grow follower base by 40K',     'checked' => true],
                            ['text' => 'Conversion rate above 8%',      'checked' => true],
                            ['text' => 'Stay within $500K budget',      'checked' => true],
                            ['text' => 'Launch influencer partnerships', 'checked' => false],
                        ], '#f8fafc', $primary, 3),

                        // Testimonial / Quote
                        $this->testimonial('quote-1', 390, 662, 360, 160, '"The Q4 campaign was our best-performing initiative. 340% ROI exceeded all expectations."', 'Sarah Chen', 'VP Marketing', $dark, '#f8fafc', $primary, 3),

                        // Summary table
                        $this->table(
                            'soc-table', 48, 868, 698, 160,
                            ['Platform', 'Impressions', 'Engagement', 'Clicks', 'Conversions'],
                            [
                                ['Platform' => 'Instagram', 'Impressions' => '890K', 'Engagement' => '9.2%', 'Clicks' => '82K',  'Conversions' => '6,200'],
                                ['Platform' => 'LinkedIn',  'Impressions' => '640K', 'Engagement' => '7.8%', 'Clicks' => '50K',  'Conversions' => '4,800'],
                                ['Platform' => 'Google Ads','Impressions' => '510K', 'Engagement' => '6.4%', 'Clicks' => '33K',  'Conversions' => '3,900'],
                                ['Platform' => 'Facebook',  'Impressions' => '360K', 'Engagement' => '5.1%', 'Clicks' => '18K',  'Conversions' => '1,600'],
                            ],
                            $dark, '#1e293b', '#0f172a', 3
                        ),
                    ],
                ],
            ],
        ];
    }

    /* ───────────────────────────────────────────────────────────────
     | 4. EXECUTIVE PROPOSAL — Clean Navy Professional
     ─────────────────────────────────────────────────────────────── */
    private function executiveProposal(): array
    {
        $primary = '#1d4ed8';
        $accent  = '#3b82f6';
        $bg      = '#ffffff';

        return [
            'name'           => 'Executive Proposal',
            'description'    => 'Clean navy-blue executive business proposal with sidebar layout, investment overview, timeline and strategic goals checklist.',
            'badge'          => 'Professional',
            'category'       => 'Proposal',
            'tags'           => ['proposal', 'executive', 'navy', 'strategy', 'professional'],
            'cover_gradient' => "linear-gradient(135deg, {$primary} 0%, {$accent} 100%)",
            'settings'       => [
                'page_size'        => 'A4',
                'orientation'      => 'portrait',
                'primary_color'    => $primary,
                'accent_color'     => $accent,
                'background_color' => $bg,
                'text_color'       => '#0f172a',
                'font_family'      => "'Plus Jakarta Sans', sans-serif",
                'font_size'        => 13,
                'margin'           => 0,
                'show_header'      => false,
                'show_footer'      => false,
            ],
            'pages' => [
                [
                    'id'       => 'ep1',
                    'label'    => 'Cover',
                    'elements' => [
                        // Left navy sidebar
                        $this->rect('ep-sidebar', 0, 0, 230, 1123, $primary, 1),

                        // Sidebar logo box
                        $this->rect('ep-logo-bg', 24, 48, 56, 56, 'rgba(255,255,255,0.15)', 3, 12),
                        $this->text('ep-logo-t', 'EP', 36, 62, 34, 30, '#ffffff', 16, '800', 'center', 4),

                        // Sidebar company
                        $this->text('ep-company', 'Enterprise\nPartners', 24, 118, 182, 40, 'rgba(255,255,255,0.8)', 13, '600', 'left', 4, 1.3),

                        // Sidebar nav labels
                        $this->rect('ep-nav-div1', 24, 250, 182, 1, 'rgba(255,255,255,0.15)', 4),
                        $this->text('ep-nav-1', 'Executive Summary', 24, 268, 182, 22, 'rgba(255,255,255,0.9)', 11, '600', 'left', 4),
                        $this->text('ep-nav-2', 'Investment Overview', 24, 298, 182, 22, 'rgba(255,255,255,0.5)', 11, '400', 'left', 4),
                        $this->text('ep-nav-3', 'Strategic Goals',     24, 324, 182, 22, 'rgba(255,255,255,0.5)', 11, '400', 'left', 4),
                        $this->text('ep-nav-4', 'Implementation Plan', 24, 350, 182, 22, 'rgba(255,255,255,0.5)', 11, '400', 'left', 4),
                        $this->text('ep-nav-5', 'Financial Model',     24, 376, 182, 22, 'rgba(255,255,255,0.5)', 11, '400', 'left', 4),

                        // Sidebar KPIs
                        $this->rect('ep-k-div', 24, 480, 182, 1, 'rgba(255,255,255,0.15)', 4),
                        $this->text('ep-k1v', '$24M',      24, 500, 182, 34, '#ffffff', 24, '800', 'left', 4),
                        $this->text('ep-k1l', 'Proposed Budget', 24, 536, 182, 18, 'rgba(255,255,255,0.6)', 10, '500', 'left', 4),
                        $this->rect('ep-k-div2', 24, 562, 182, 1, 'rgba(255,255,255,0.15)', 4),
                        $this->text('ep-k2v', '36 Months',  24, 578, 182, 34, '#ffffff', 24, '800', 'left', 4),
                        $this->text('ep-k2l', 'Project Duration', 24, 614, 182, 18, 'rgba(255,255,255,0.6)', 10, '500', 'left', 4),
                        $this->rect('ep-k-div3', 24, 640, 182, 1, 'rgba(255,255,255,0.15)', 4),
                        $this->text('ep-k3v', '4.2x ROI',   24, 656, 182, 34, '#93c5fd', 24, '800', 'left', 4),
                        $this->text('ep-k3l', 'Expected Return', 24, 692, 182, 18, 'rgba(255,255,255,0.6)', 10, '500', 'left', 4),

                        // Main content area
                        $this->text('ep-type', 'EXECUTIVE PROPOSAL · 2024', 258, 72, 500, 18, $accent, 10, '800', 'left', 3, 3),
                        $this->heading('ep-title', "Strategic\nGrowth\nProposal", 258, 96, 500, 210, '#0f172a', 52, '800', 'left', 3, 1.05),
                        $this->rect('ep-title-div', 258, 314, 100, 4, $primary, 3, 0, 99),
                        $this->text('ep-desc', 'Prepared for the Board of Directors and Executive Leadership Team. This proposal outlines a comprehensive strategic framework for sustainable growth and market expansion.', 258, 330, 500, 72, '#475569', 13, '400', 'left', 3, 1.7),

                        // Cover image
                        $this->image('ep-cover-img', 258, 430, 500, 320),

                        // Prepared by section
                        $this->rect('ep-prep-bg', 258, 776, 500, 80, '#f8fafc', 3, 10),
                        $this->rect('ep-prep-border', 258, 776, 4, 80, $primary, 3, 0, 0, 10),
                        $this->text('ep-prep-l', 'Prepared By',        270, 794, 200, 16, '#94a3b8', 10, '600', 'left', 4),
                        $this->text('ep-prep-n', 'Strategy & Growth Team', 270, 812, 280, 22, '#0f172a', 14, '700', 'left', 4),
                        $this->text('ep-prep-dt', 'December 2024', 470, 812, 280, 22, $primary, 12, '600', 'right', 4),
                    ],
                ],

                [
                    'id'       => 'ep2',
                    'label'    => 'Investment & Timeline',
                    'elements' => [
                        $this->rect('ep2-sidebar', 0, 0, 230, 1123, $primary, 1),
                        $this->text('ep2-pg', '02', 24, 48, 182, 60, 'rgba(255,255,255,0.1)', 48, '800', 'left', 4),
                        $this->text('ep2-sect', 'INVESTMENT\n& TIMELINE', 24, 118, 182, 48, 'rgba(255,255,255,0.8)', 11, '700', 'left', 4, 1.4, 2),

                        // Investment breakdown bar chart
                        $this->heading('ep2-inv-h', 'Investment Overview', 258, 48, 500, 36, '#0f172a', 20, '700', 'left', 3),
                        $this->text('ep2-inv-s', 'Proposed capital allocation across all strategic initiatives', 258, 88, 500, 20, '#64748b', 11, '400', 'left', 3),
                        $this->rect('ep2-inv-div', 258, 112, 80, 3, $primary, 3, 0, 99),

                        $this->barChart('ep2-inv-bar', 258, 128, 500, 240, 'Investment Allocation by Initiative', ['Technology','HR','Marketing','R&D','Infrastructure','Operations'], [8.4, 3.2, 4.8, 5.6, 1.2, 0.8], $primary, 'USD Million', 3),

                        // ROI line chart
                        $this->lineChart('ep2-roi', 258, 390, 500, 200, 'Projected ROI Timeline (Cumulative)', ['Q1','Q2','Q3','Q4','Q5','Q6','Q7','Q8','Q9','Q10','Q11','Q12'], [-24,-21,-15,-5,8,22,38,57,79,104,132,164], $accent, 3),

                        // Implementation timeline
                        $this->heading('ep2-tl-h', 'Implementation Milestones', 258, 614, 500, 28, '#0f172a', 14, '700', 'left', 3),
                        $this->timeline('ep2-tl', 258, 648, 500, 200,
                            [
                                ['date' => 'Q1 2025', 'label' => 'Foundation Phase',    'desc' => 'Team onboarding, platform setup, process design'],
                                ['date' => 'Q2 2025', 'label' => 'Development Phase',   'desc' => 'Core product build, early beta testing with selected customers'],
                                ['date' => 'Q3 2025', 'label' => 'Launch Phase',        'desc' => 'Market rollout, sales acceleration, feedback loops'],
                                ['date' => 'Q4 2025', 'label' => 'Scale Phase',         'desc' => 'International expansion, partnership activation'],
                            ],
                            $primary, 3
                        ),

                        // Strategic goals checklist
                        $this->heading('ep2-gl-h', 'Strategic Goals', 258, 872, 500, 28, '#0f172a', 14, '700', 'left', 3),
                        $this->checklist('ep2-goals', 258, 904, 500, 180, [
                            ['text' => 'Achieve $50M ARR by end of Year 2',   'checked' => false],
                            ['text' => 'Expand to 3 new international markets', 'checked' => false],
                            ['text' => 'Reach 50,000 active users',             'checked' => false],
                            ['text' => 'Maintain 35%+ gross margins',           'checked' => false],
                        ], '#f8fafc', $primary, 3),
                    ],
                ],
            ],
        ];
    }

    /* ───────────────────────────────────────────────────────────────
     | 5. ANALYTICS INTELLIGENCE REPORT — Dark Violet Data
     ─────────────────────────────────────────────────────────────── */
    private function analyticsIntelligenceReport(): array
    {
        $primary = '#8b5cf6';
        $bg      = '#0f0f1a';
        $dark    = '#1a1a2e';
        $accent  = '#6366f1';

        return [
            'name'           => 'Analytics Intelligence Report',
            'description'    => 'Advanced dark analytics report with radar charts, multi-metric KPI grid, cohort analysis table and performance heatmap visualization.',
            'badge'          => 'Data',
            'category'       => 'Analytics',
            'tags'           => ['analytics', 'data', 'dark', 'intelligence', 'radar', 'metrics'],
            'cover_gradient' => "linear-gradient(135deg, {$bg} 0%, #2d1b69 60%, {$primary} 100%)",
            'settings'       => [
                'page_size'        => 'A4',
                'orientation'      => 'portrait',
                'primary_color'    => $primary,
                'accent_color'     => $accent,
                'background_color' => $bg,
                'text_color'       => '#e2e8f0',
                'font_family'      => "'Space Grotesk', 'DM Sans', sans-serif",
                'font_size'        => 13,
                'margin'           => 48,
                'show_header'      => false,
                'show_footer'      => true,
                'footer_left'      => 'Analytics Intelligence · Q4 2024',
                'footer_right'     => 'Classified',
                'show_page_numbers'=> true,
            ],
            'pages' => [
                [
                    'id'       => 'ap1',
                    'label'    => 'Intelligence Overview',
                    'elements' => [
                        $this->rect('a-bg', 0, 0, 794, 1123, $bg, 0),

                        // Top header
                        $this->rect('a-top-bar', 0, 0, 794, 64, $dark, 2),
                        $this->rect('a-top-accent', 0, 0, 794, 3, $primary, 3),
                        $this->text('a-top-title', 'ANALYTICS INTELLIGENCE REPORT', 48, 22, 500, 22, '#e2e8f0', 12, '700', 'left', 4, 2),
                        $this->text('a-top-date', 'Q4 2024', 600, 22, 148, 22, $primary, 12, '700', 'right', 4),

                        // Hero section
                        $this->heading('a-hero', 'Data\nIntelligence', 48, 86, 520, 140, '#f8fafc', 48, '800', 'left', 3, 1.0),
                        $this->rect('a-hero-div', 48, 232, 698, 1, '#2d2d4e', 3),

                        // 6 compact KPI cards in 2 rows × 3
                        $this->kpiCard('akpi-1', 48,  252, 210, 96, '$12.4M',  'Total ARR',         '+31%',   'positive', $primary, $dark, 10),
                        $this->kpiCard('akpi-2', 270, 252, 210, 96, '98.4%',   'System Uptime',     '+0.2%',  'positive', '#10b981', $dark, 10),
                        $this->kpiCard('akpi-3', 492, 252, 210, 96, '24,800',  'Active Users',       '+18%',   'positive', '#06b6d4', $dark, 10),
                        $this->kpiCard('akpi-4', 48,  360, 210, 96, '4.7/5',   'Satisfaction',      '+0.3',   'positive', '#f59e0b', $dark, 10),
                        $this->kpiCard('akpi-5', 270, 360, 210, 96, '2.1%',    'Churn Rate',        '-0.4%',  'positive', '#a78bfa', $dark, 10),
                        $this->kpiCard('akpi-6', 492, 360, 210, 96, '48min',   'Avg Session',       '+12min', 'positive', '#34d399', $dark, 10),

                        // Radar chart — Performance dimensions
                        $this->radarChart('a-radar', 48, 476, 310, 280, 'Performance Dimensions', ['Acquisition','Retention','Revenue','Engagement','Support','Product'], [88, 92, 78, 85, 90, 76], $primary, 3),

                        // Area chart — User Growth
                        $this->areaChart('a-area', 374, 476, 372, 280, 'Monthly Active Users', ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], [14200,15800,16400,18200,19600,21000,22400,23100,22800,24200,24600,24800], $primary, 3),

                        // Cohort analysis table
                        $this->heading('a-cohort-h', 'Cohort Retention Analysis', 48, 780, 500, 28, '#e2e8f0', 14, '700', 'left', 3),
                        $this->table(
                            'a-cohort', 48, 812, 698, 200,
                            ['Cohort', 'Month 0', 'Month 1', 'Month 3', 'Month 6', 'Month 12'],
                            [
                                ['Cohort' => 'Jan 2024', 'Month 0' => '100%', 'Month 1' => '68%', 'Month 3' => '52%', 'Month 6' => '41%', 'Month 12' => '32%'],
                                ['Cohort' => 'Apr 2024', 'Month 0' => '100%', 'Month 1' => '72%', 'Month 3' => '58%', 'Month 6' => '46%', 'Month 12' => '—'],
                                ['Cohort' => 'Jul 2024', 'Month 0' => '100%', 'Month 1' => '76%', 'Month 3' => '62%', 'Month 6' => '—',   'Month 12' => '—'],
                                ['Cohort' => 'Oct 2024', 'Month 0' => '100%', 'Month 1' => '79%', 'Month 3' => '—',   'Month 6' => '—',   'Month 12' => '—'],
                            ],
                            '#4c1d95', $dark, '#1a1a2e', 3
                        ),
                    ],
                ],

                [
                    'id'       => 'ap2',
                    'label'    => 'Funnel & Attribution',
                    'elements' => [
                        $this->rect('ap2-bg', 0, 0, 794, 1123, $bg, 0),
                        $this->rect('ap2-top', 0, 0, 794, 64, $dark, 2),
                        $this->rect('ap2-acc', 0, 0, 794, 3, $primary, 3),
                        $this->text('ap2-ttl', 'FUNNEL & ATTRIBUTION', 48, 22, 500, 22, '#e2e8f0', 12, '700', 'left', 4, 2),
                        $this->text('ap2-pg', 'PAGE 02', 600, 22, 148, 22, $primary, 10, '700', 'right', 4, 2),

                        $this->heading('ap2-h', 'Conversion Funnel', 48, 86, 500, 36, '#f8fafc', 20, '700', 'left', 3),
                        $this->text('ap2-s', 'Full-funnel analysis from awareness to revenue attribution', 48, 126, 500, 20, '#64748b', 11, '400', 'left', 3),

                        // Progress bars as funnel
                        $this->progress('fn-vis',  48, 158, 698, 36, 'Visitors (100,000)',     100, $primary, '#2d2d4e', 3),
                        $this->progress('fn-leads', 48, 204, 698, 36, 'Leads (34,200 · 34.2%)', 34, '#6366f1', '#2d2d4e', 3),
                        $this->progress('fn-mqls',  48, 250, 698, 36, 'MQLs (12,600 · 12.6%)',  13, '#8b5cf6', '#2d2d4e', 3),
                        $this->progress('fn-sqls',  48, 296, 698, 36, 'SQLs (4,800 · 4.8%)',    5,  '#a78bfa', '#2d2d4e', 3),
                        $this->progress('fn-opp',   48, 342, 698, 36, 'Opportunities (1,920 · 1.9%)', 2, '#c4b5fd', '#2d2d4e', 3),
                        $this->progress('fn-rev',   48, 388, 698, 36, 'Won Deals (480 · 0.48%)',  1, '#10b981', '#2d2d4e', 3),

                        // Attribution doughnut
                        $this->doughnutChart('attr-dough', 48, 448, 310, 240, 'Revenue Attribution', ['Organic Search','Paid Social','Referral','Direct','Email','PPC'], [28,22,18,14,11,7], ['#8b5cf6','#6366f1','#10b981','#f59e0b','#06b6d4','#f97316'], 3),

                        // Bar chart — conversion by source
                        $this->barChart('conv-bar', 374, 448, 372, 240, 'Conv. Rate by Source (%)', ['Organic','Email','Referral','Social','Direct','PPC'], [4.8, 6.2, 5.4, 2.1, 3.8, 3.2], $accent, 'Conversion %', 3),

                        // Sparkline stat cards
                        $this->statRow('ap2-stats', 48, 714, 698, 80, [
                            ['value' => '480', 'label' => 'Deals Won'],
                            ['value' => '35%', 'label' => 'Win Rate'],
                            ['value' => '$25.8K','label' => 'Avg Deal Size'],
                            ['value' => '42d',  'label' => 'Sales Cycle'],
                        ], $dark, $primary, 3),

                        // Table — top acquisition channels
                        $this->heading('ap2-tbl-h', 'Top Acquisition Channels', 48, 820, 500, 28, '#e2e8f0', 14, '700', 'left', 3),
                        $this->table(
                            'ap2-tbl', 48, 852, 698, 200,
                            ['Channel', 'Sessions', 'Leads', 'Conv. Rate', 'CAC', 'LTV'],
                            [
                                ['Channel' => 'Organic Search', 'Sessions' => '42,000', 'Leads' => '2,016', 'Conv. Rate' => '4.8%', 'CAC' => '$48',  'LTV' => '$1,240'],
                                ['Channel' => 'Email Campaigns','Sessions' => '18,400', 'Leads' => '1,141', 'Conv. Rate' => '6.2%', 'CAC' => '$12',  'LTV' => '$1,680'],
                                ['Channel' => 'Paid Social',    'Sessions' => '28,600', 'Leads' => '944',   'Conv. Rate' => '3.3%', 'CAC' => '$124', 'LTV' => '$920'],
                                ['Channel' => 'Referral',       'Sessions' => '11,200', 'Leads' => '605',   'Conv. Rate' => '5.4%', 'CAC' => '$36',  'LTV' => '$2,100'],
                            ],
                            '#4c1d95', $dark, '#1a1a2e', 3
                        ),
                    ],
                ],
            ],
        ];
    }

    /* ───────────────────────────────────────────────────────────────
     | 6. PRODUCT LAUNCH REPORT — Vibrant Gradient Modern
     ─────────────────────────────────────────────────────────────── */
    private function productLaunchReport(): array
    {
        $primary = '#ec4899';
        $accent  = '#f97316';
        $blue    = '#3b82f6';
        $bg      = '#ffffff';

        return [
            'name'           => 'Product Launch Report',
            'description'    => 'Vibrant gradient product launch report with feature grid, user adoption funnel, market comparison table and milestone tracker.',
            'badge'          => 'New',
            'category'       => 'Product',
            'tags'           => ['product', 'launch', 'gradient', 'modern', 'vibrant', 'feature'],
            'cover_gradient' => "linear-gradient(135deg, {$primary} 0%, {$accent} 60%, #fbbf24 100%)",
            'settings'       => [
                'page_size'        => 'A4',
                'orientation'      => 'portrait',
                'primary_color'    => $primary,
                'accent_color'     => $accent,
                'background_color' => $bg,
                'text_color'       => '#0f172a',
                'font_family'      => "'Sora', 'DM Sans', sans-serif",
                'font_size'        => 13,
                'margin'           => 48,
                'show_header'      => false,
                'show_footer'      => true,
                'footer_left'      => 'Product Launch Report · v2.0',
                'footer_right'     => 'Q4 2024',
                'show_page_numbers'=> true,
            ],
            'pages' => [
                [
                    'id'       => 'pp1',
                    'label'    => 'Launch Overview',
                    'elements' => [
                        // Gradient hero band
                        $this->rect('pp-hero', 0, 0, 794, 320, 'transparent', 1),
                        // Fake gradient using overlapping rects with border-radius
                        $this->rect('pp-hero-pink',   0,   0, 794, 320, $primary, 1),
                        $this->circle('pp-hero-c1', 500, -80, 320, 320, $accent, 1, 60),
                        $this->circle('pp-hero-c2', 600, 100, 200, 200, '#fbbf24', 1, 40),

                        // Hero text
                        $this->text('pp-badge', 'PRODUCT LAUNCH REPORT · 2024', 48, 56, 500, 20, 'rgba(255,255,255,0.8)', 10, '800', 'left', 3, 2),
                        $this->heading('pp-title', 'Product v2.0\nLaunch Results', 48, 80, 600, 140, '#ffffff', 44, '800', 'left', 3, 1.1),
                        $this->text('pp-desc', 'Comprehensive post-launch analysis covering adoption metrics, feature performance, user feedback and market positioning.', 48, 224, 560, 52, 'rgba(255,255,255,0.85)', 12, '400', 'left', 3, 1.6),

                        // KPIs below hero
                        $this->kpiCard('pkpi-1', 48,  290, 150, 104, '48K',  'Users Day 1',    'Record',   'positive', $primary,  '#fff5f7', 10),
                        $this->kpiCard('pkpi-2', 210, 290, 150, 104, '4.8★', 'App Store',      '+0.4',     'positive', '#f97316', '#fff7ed', 10),
                        $this->kpiCard('pkpi-3', 372, 290, 150, 104, '94%',  'Uptime',         'SLA Met',  'positive', '#10b981', '#f0fdf4', 10),
                        $this->kpiCard('pkpi-4', 534, 290, 150, 104, '3.2s', 'Avg Load',       '-1.8s',    'positive', $blue,     '#eff6ff', 10),

                        // User adoption line chart
                        $this->heading('pp-adopt-h', 'User Adoption Curve', 48, 424, 500, 28, '#0f172a', 14, '700', 'left', 3),
                        $this->lineChart('pp-adopt', 48, 456, 460, 220, 'Daily Active Users (Post Launch)', ['Day 1','Day 3','Day 5','Day 7','Day 10','Day 14','Day 21','Day 30'], [48000,62000,71000,68000,74000,82000,91000,98000], $primary, 3),

                        // Feature adoption doughnut
                        $this->doughnutChart('pp-feat', 526, 456, 220, 220, 'Feature Adoption', ['Core Features','AI Tools','Integrations','Analytics','Templates'], [42,24,16,11,7], ['#ec4899','#f97316','#3b82f6','#10b981','#f59e0b'], 3),

                        // Callout — Highlight
                        $this->callout('pp-callout', 48, 700, 698, 68, '🚀', 'Product v2.0 achieved the fastest adoption in company history. Day-1 signups exceeded projections by 340% and app store rating improved from 4.4 to 4.8 stars.', '#fff5f7', $primary, '#be185d', 3),

                        // Feature grid — stat row
                        $this->statRow('pp-stats', 48, 796, 698, 80, [
                            ['value' => '28',    'label' => 'New Features'],
                            ['value' => '98K',   'label' => 'Monthly Active'],
                            ['value' => '340%',  'label' => 'Above Target'],
                            ['value' => '12min', 'label' => 'Avg Session'],
                        ], '#f8fafc', $primary, 3),

                        // Launch timeline
                        $this->timeline('pp-tl', 48, 904, 698, 180,
                            [
                                ['date' => 'Nov 1',  'label' => 'Beta Launch',       'desc' => '500 beta users · 94% satisfaction · 48 bugs fixed'],
                                ['date' => 'Dec 1',  'label' => 'Public Launch',      'desc' => '48,000 day-1 signups · #1 on ProductHunt'],
                                ['date' => 'Dec 15', 'label' => 'App Store Feature',  'desc' => 'Featured by Apple & Google · 4.8★ rating'],
                                ['date' => 'Dec 31', 'label' => '30-Day Milestone',   'desc' => '98K MAU · $240K MRR · NPS score 72'],
                            ],
                            $primary, 3
                        ),
                    ],
                ],

                [
                    'id'       => 'pp2',
                    'label'    => 'Market Analysis',
                    'elements' => [
                        $this->rect('pp2-stripe', 0, 0, 794, 6, $primary, 2),

                        $this->heading('pp2-h', 'Market Analysis', 48, 42, 500, 36, '#0f172a', 22, '700', 'left', 3),
                        $this->text('pp2-s', 'Competitive positioning, user feedback and 2025 product roadmap', 48, 82, 500, 20, '#64748b', 11, '400', 'left', 3),
                        $this->rect('pp2-d', 48, 108, 80, 4, $primary, 3, 0, 99),

                        // Competitive comparison bar chart
                        $this->barChart('pp2-comp', 48, 128, 460, 220, 'Feature Score vs Competitors (out of 100)', ['Our Product','Competitor A','Competitor B','Competitor C','Competitor D'], [94, 78, 82, 71, 65], $primary, 'Score', 3),

                        // NPS radar
                        $this->radarChart('pp2-nps', 526, 128, 220, 220, 'User Satisfaction', ['Ease of Use','Performance','Features','Support','Value','Design'], [92,88,86,94,80,96], $primary, 3),

                        // Competitive table
                        $this->heading('pp2-ct-h', 'Competitive Feature Matrix', 48, 376, 500, 28, '#0f172a', 14, '700', 'left', 3),
                        $this->table(
                            'pp2-ct', 48, 408, 698, 200,
                            ['Feature', 'Our Product', 'Competitor A', 'Competitor B', 'Competitor C'],
                            [
                                ['Feature' => 'AI Integration',    'Our Product' => '✅ Full',      'Competitor A' => '⚠️ Partial', 'Competitor B' => '❌ None',   'Competitor C' => '⚠️ Partial'],
                                ['Feature' => 'Real-time Sync',    'Our Product' => '✅ Full',      'Competitor A' => '✅ Full',     'Competitor B' => '⚠️ Partial','Competitor C' => '❌ None'],
                                ['Feature' => 'Analytics',         'Our Product' => '✅ Advanced',  'Competitor A' => '⚠️ Basic',   'Competitor B' => '⚠️ Basic',  'Competitor C' => '✅ Advanced'],
                                ['Feature' => 'Pricing',           'Our Product' => '$29/mo',       'Competitor A' => '$49/mo',    'Competitor B' => '$39/mo',   'Competitor C' => '$35/mo'],
                            ],
                            $primary, '#fff5f7', '#fdf2f8', 3
                        ),

                        // User feedback checklist
                        $this->heading('pp2-fb-h', 'Top User Requests (2025 Roadmap)', 48, 632, 350, 28, '#0f172a', 14, '700', 'left', 3),
                        $this->checklist('pp2-fb', 48, 666, 320, 200, [
                            ['text' => 'Mobile app (iOS & Android)',       'checked' => true],
                            ['text' => 'API & webhook integrations',       'checked' => true],
                            ['text' => 'Team collaboration features',      'checked' => false],
                            ['text' => 'White-label / custom branding',    'checked' => false],
                            ['text' => 'Advanced export options',          'checked' => false],
                            ['text' => 'SSO & enterprise security',        'checked' => false],
                        ], '#fff5f7', $primary, 3),

                        // 2025 revenue projection
                        $this->lineChart('pp2-rev', 386, 632, 380, 200, '2025 MRR Projection ($K)', ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], [260,310,380,440,520,600,690,790,900,1020,1160,1320], $accent, 3),

                        // Summary quote
                        $this->callout('pp2-quote', 48, 888, 698, 72, '🎯', "2025 target: $15M ARR · 500K users · 4.9★ App Store rating. The product is positioned to capture 12% market share within 18 months based on current adoption velocity and competitive advantages.", '#fff7ed', $accent, '#c2410c', 3),
                    ],
                ],
            ],
        ];
    }

    /* ═══════════════════════════════════════════════════════════════
     | ELEMENT BUILDER HELPERS
     | Every helper returns an array matching EditorCanvas el schema
     ═══════════════════════════════════════════════════════════════ */

    private function el(string $id, string $type, float $x, float $y, array $styles, array $extra = []): array
    {
        return array_merge([
            'id'      => $id,
            'type'    => $type,
            'visible' => true,
            'locked'  => false,
            'position'=> ['x' => $x, 'y' => $y],
            'styles'  => array_merge(['zIndex' => 1, 'opacity' => 100], $styles),
        ], $extra);
    }

    private function rect(string $id, float $x, float $y, float $w, float $h, string $color, int $z = 1, float $radius = 0, int $opacity = 100, float $border = 0, string $borderColor = 'transparent'): array
    {
        return $this->el($id, 'rectangle', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => $color,
            'borderRadius'    => $radius,
            'opacity'         => $opacity,
            'borderWidth'     => $border,
            'borderColor'     => $borderColor,
        ]);
    }

    private function circle(string $id, float $x, float $y, float $w, float $h, string $color, int $z = 1, int $opacity = 100): array
    {
        return $this->el($id, 'circle', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => $color,
            'borderRadius'    => 9999,
            'opacity'         => $opacity,
        ]);
    }

    private function text(string $id, string $content, float $x, float $y, float $w, float $h, string $color, int $fontSize = 14, string $weight = '400', string $align = 'left', int $z = 3, float $spacing = 0): array
    {
        return $this->el($id, 'text', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'fontSize'       => $fontSize,
            'fontWeight'     => $weight,
            'color'          => $color,
            'textAlign'      => $align,
            'letterSpacing'  => $spacing,
            'backgroundColor'=> 'transparent',
        ], ['content' => $content]);
    }

    private function heading(string $id, string $content, float $x, float $y, float $w, float $h, string $color, int $fontSize = 32, string $weight = '700', string $align = 'left', int $z = 3, float $lineHeight = 1.2): array
    {
        return $this->el($id, 'heading', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'fontSize'       => $fontSize,
            'fontWeight'     => $weight,
            'color'          => $color,
            'textAlign'      => $align,
            'lineHeight'     => $lineHeight,
            'backgroundColor'=> 'transparent',
        ], ['content' => $content]);
    }

    private function image(string $id, float $x, float $y, float $w, float $h, int $z = 3): array
    {
        return $this->el($id, 'image', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'borderRadius' => 8,
            'objectFit'    => 'cover',
        ], ['src' => '', 'alt' => 'Report Image']);
    }

    private function kpiCard(string $id, float $x, float $y, float $w, float $h, string $value, string $label, string $change, string $changeType, string $color, string $bg, int $z = 3): array
    {
        return $this->el($id, 'metric', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => $bg,
            'borderRadius'    => 12,
            'borderWidth'     => 1,
            'borderColor'     => "{$color}30",
            'color'           => $color,
            'valueColor'      => $color,
            'padding'         => 14,
        ], [
            'value'         => $value,
            'label'         => $label,
            'change'        => $change,
            'changeType'    => $changeType,
            'changePeriod'  => '',
        ]);
    }

    private function barChart(string $id, float $x, float $y, float $w, float $h, string $title, array $labels, array $values, string $color, string $datasetLabel = 'Value', int $z = 3): array
    {
        return $this->el($id, 'bar-chart', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => 'transparent',
            'borderRadius'    => 8,
        ], [
            'chartTitle'        => $title,
            'chartDatasetLabel' => $datasetLabel,
            'chartData'         => ['labels' => $labels, 'values' => $values],
            'chartColor'        => $color,
        ]);
    }

    private function lineChart(string $id, float $x, float $y, float $w, float $h, string $title, array $labels, array $values, string $color, int $z = 3): array
    {
        return $this->el($id, 'line-chart', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => 'transparent',
            'borderRadius'    => 8,
        ], [
            'chartTitle' => $title,
            'chartData'  => ['labels' => $labels, 'values' => $values],
            'chartColor' => $color,
        ]);
    }

    private function areaChart(string $id, float $x, float $y, float $w, float $h, string $title, array $labels, array $values, string $color, int $z = 3): array
    {
        return $this->el($id, 'area-chart', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => 'transparent',
            'borderRadius'    => 8,
        ], [
            'chartTitle' => $title,
            'chartData'  => ['labels' => $labels, 'values' => $values],
            'chartColor' => $color,
        ]);
    }

    private function pieChart(string $id, float $x, float $y, float $w, float $h, string $title, array $labels, array $values, array $colors, int $z = 3): array
    {
        return $this->el($id, 'pie-chart', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => 'transparent',
        ], [
            'chartTitle' => $title,
            'chartData'  => ['labels' => $labels, 'values' => $values],
            'pieColors'  => $colors,
        ]);
    }

    private function doughnutChart(string $id, float $x, float $y, float $w, float $h, string $title, array $labels, array $values, array $colors, int $z = 3): array
    {
        return $this->el($id, 'doughnut-chart', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => 'transparent',
        ], [
            'chartTitle' => $title,
            'chartData'  => ['labels' => $labels, 'values' => $values],
            'pieColors'  => $colors,
        ]);
    }

    private function radarChart(string $id, float $x, float $y, float $w, float $h, string $title, array $labels, array $values, string $color, int $z = 3): array
    {
        return $this->el($id, 'radar-chart', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => 'transparent',
        ], [
            'chartTitle' => $title,
            'chartData'  => ['labels' => $labels, 'values' => $values],
            'chartColor' => $color,
        ]);
    }

    private function table(string $id, float $x, float $y, float $w, float $h, array $columns, array $data, string $headerBg, string $evenBg, string $oddBg, int $z = 3): array
    {
        return $this->el($id, 'table', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => 'transparent',
            'headerBg'        => $headerBg,
            'headerColor'     => '#ffffff',
            'evenRowBg'       => $evenBg,
            'oddRowBg'        => $oddBg,
        ], ['columns' => $columns, 'data' => $data]);
    }

    private function progress(string $id, float $x, float $y, float $w, float $h, string $label, int $value, string $color, string $trackColor, int $z = 3): array
    {
        return $this->el($id, 'progress', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'color'       => $color,
            'trackColor'  => $trackColor,
        ], ['label' => $label, 'value' => $value]);
    }

    private function statRow(string $id, float $x, float $y, float $w, float $h, array $stats, string $bg, string $color, int $z = 3): array
    {
        return $this->el($id, 'stat-row', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => $bg,
            'borderRadius'    => 10,
            'color'           => $color,
        ], ['stats' => $stats]);
    }

    private function timeline(string $id, float $x, float $y, float $w, float $h, array $items, string $color, int $z = 3): array
    {
        return $this->el($id, 'timeline', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'color'           => $color,
            'backgroundColor' => 'transparent',
        ], ['items' => $items]);
    }

    private function checklist(string $id, float $x, float $y, float $w, float $h, array $items, string $bg, string $color, int $z = 3): array
    {
        return $this->el($id, 'checklist', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => $bg,
            'borderRadius'    => 8,
            'color'           => $color,
        ], ['items' => $items]);
    }

    private function callout(string $id, float $x, float $y, float $w, float $h, string $emoji, string $content, string $bg, string $borderColor, string $textColor, int $z = 3): array
    {
        return $this->el($id, 'callout', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => $bg,
            'borderColor'     => $borderColor,
            'borderRadius'    => 10,
            'color'           => $textColor,
        ], ['emoji' => $emoji, 'content' => $content]);
    }

    private function testimonial(string $id, float $x, float $y, float $w, float $h, string $content, string $author, string $role, string $bg, string $textColor, string $accentColor, int $z = 3): array
    {
        return $this->el($id, 'testimonial', $x, $y, [
            'width' => $w, 'height' => $h, 'zIndex' => $z,
            'backgroundColor' => $bg,
            'borderRadius'    => 12,
            'color'           => $textColor,
            'borderColor'     => $accentColor,
            'borderWidth'     => 1,
        ], ['content' => $content, 'author' => $author, 'role' => $role]);
    }
}