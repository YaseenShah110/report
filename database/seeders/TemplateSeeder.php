<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Template::truncate();

        $templates = [
            [
                'name'        => 'Executive Dark',
                'slug'        => 'executive-dark',
                'description' => 'Sleek dark-themed executive report with accent gradients, KPI cards, and premium charts.',
                'thumbnail'   => null,
                'is_active'   => true,
                'structure'   => [
                    'pages' => [
                        // ── PAGE 1: Cover ──
                        [
                            'id'       => 'page-exec-1',
                            'label'    => 'Cover Page',
                            'elements' => [
                                // Full-page dark background rectangle
                                ['id'=>'exec-bg','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>1123,'backgroundColor'=>'#0f172a','zIndex'=>0,'borderRadius'=>0,'opacity'=>100]],
                                // Accent gradient bar top
                                ['id'=>'exec-bar-top','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>6,'backgroundColor'=>'#6366f1','zIndex'=>2,'borderRadius'=>0,'opacity'=>100]],
                                // Large circle decoration
                                ['id'=>'exec-circle1','type'=>'circle','position'=>['x'=>550,'y'=>-80],'styles'=>['width'=>360,'height'=>360,'backgroundColor'=>'#6366f1','zIndex'=>1,'opacity'=>8]],
                                ['id'=>'exec-circle2','type'=>'circle','position'=>['x'=>620,'y'=>100],'styles'=>['width'=>200,'height'=>200,'backgroundColor'=>'#8b5cf6','zIndex'=>1,'opacity'=>6]],
                                // Company Logo area
                                ['id'=>'exec-logo-rect','type'=>'rectangle','position'=>['x'=>60,'y'=>60],'styles'=>['width'=>48,'height'=>48,'backgroundColor'=>'#6366f1','zIndex'=>3,'borderRadius'=>12,'opacity'=>100]],
                                ['id'=>'exec-logo-txt','type'=>'text','content'=>'CO','position'=>['x'=>70,'y'=>72],'styles'=>['width'=>30,'height'=>28,'fontSize'=>14,'fontWeight'=>'700','color'=>'#ffffff','textAlign'=>'center','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'exec-company','type'=>'text','content'=>'Company Name','position'=>['x'=>120,'y'=>72],'styles'=>['width'=>200,'height'=>28,'fontSize'=>14,'fontWeight'=>'600','color'=>'#94a3b8','zIndex'=>4,'opacity'=>100]],
                                // Divider line
                                ['id'=>'exec-div1','type'=>'divider','position'=>['x'=>60,'y'=>300],'styles'=>['width'=>300,'height'=>2,'borderWidth'=>1,'borderStyle'=>'solid','color'=>'#6366f1','zIndex'=>3,'opacity'=>60]],
                                // Main title
                                ['id'=>'exec-title','type'=>'heading','content'=>'Annual Report<br>2024','position'=>['x'=>60,'y'=>320],'styles'=>['width'=>500,'height'=>140,'fontSize'=>56,'fontWeight'=>'800','color'=>'#f8fafc','lineHeight'=>1.1,'zIndex'=>3,'fontFamily'=>"'DM Sans', sans-serif",'opacity'=>100]],
                                // Subtitle
                                ['id'=>'exec-subtitle','type'=>'subheading','content'=>'Executive Summary & Performance Review','position'=>['x'=>60,'y'=>475],'styles'=>['width'=>440,'height'=>32,'fontSize'=>16,'fontWeight'=>'400','color'=>'#94a3b8','zIndex'=>3,'opacity'=>100]],
                                // Prepared by
                                ['id'=>'exec-prep','type'=>'text','content'=>'Prepared by the Finance & Strategy Team','position'=>['x'=>60,'y'=>520],'styles'=>['width'=>360,'height'=>24,'fontSize'=>13,'color'=>'#64748b','zIndex'=>3,'opacity'=>100]],
                                // Bottom bar
                                ['id'=>'exec-bar-bot','type'=>'rectangle','position'=>['x'=>0,'y'=>1060],'styles'=>['width'=>794,'height'=>63,'backgroundColor'=>'#1e1b4b','zIndex'=>2,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'exec-bot-txt','type'=>'text','content'=>'CONFIDENTIAL  ·  Q4 2024  ·  Internal Distribution Only','position'=>['x'=>60,'y'=>1078],'styles'=>['width'=>674,'height'=>22,'fontSize'=>11,'color'=>'#6366f1','textAlign'=>'center','letterSpacing'=>2,'zIndex'=>4,'opacity'=>100]],
                                // KPI strip
                                ['id'=>'exec-kpi1','type'=>'metric','label'=>'Revenue','value'=>'$4.8M','change'=>'18.2%','changeType'=>'positive','changePeriod'=>'YoY','position'=>['x'=>60,'y'=>660],'styles'=>['width'=>185,'height'=>110,'backgroundColor'=>'#1e293b','borderColor'=>'#334155','borderWidth'=>1,'borderRadius'=>12,'color'=>'#6366f1','opacity'=>100,'zIndex'=>3]],
                                ['id'=>'exec-kpi2','type'=>'metric','label'=>'Net Profit','value'=>'$1.2M','change'=>'9.4%','changeType'=>'positive','changePeriod'=>'YoY','position'=>['x'=>265,'y'=>660],'styles'=>['width'=>185,'height'=>110,'backgroundColor'=>'#1e293b','borderColor'=>'#334155','borderWidth'=>1,'borderRadius'=>12,'color'=>'#10b981','opacity'=>100,'zIndex'=>3]],
                                ['id'=>'exec-kpi3','type'=>'metric','label'=>'Customers','value'=>'12,400','change'=>'24.1%','changeType'=>'positive','changePeriod'=>'YoY','position'=>['x'=>470,'y'=>660],'styles'=>['width'=>185,'height'=>110,'backgroundColor'=>'#1e293b','borderColor'=>'#334155','borderWidth'=>1,'borderRadius'=>12,'color'=>'#f59e0b','opacity'=>100,'zIndex'=>3]],
                            ],
                        ],
                        // ── PAGE 2: Content ──
                        [
                            'id'       => 'page-exec-2',
                            'label'    => 'Performance',
                            'elements' => [
                                ['id'=>'exec2-bg','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>1123,'backgroundColor'=>'#0f172a','zIndex'=>0,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'exec2-bar','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>6,'height'=>1123,'backgroundColor'=>'#6366f1','zIndex'=>2,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'exec2-h','type'=>'heading','content'=>'Performance Overview','position'=>['x'=>40,'y'=>50],'styles'=>['width'=>500,'height'=>48,'fontSize'=>28,'fontWeight'=>'700','color'=>'#f8fafc','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'exec2-sub','type'=>'text','content'=>'Key metrics and highlights for the fiscal year 2024','position'=>['x'=>40,'y'=>105],'styles'=>['width'=>500,'height'=>24,'fontSize'=>13,'color'=>'#64748b','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'exec2-chart','type'=>'bar-chart','chartTitle'=>'Quarterly Revenue','chartData'=>['labels'=>['Q1','Q2','Q3','Q4'],'values'=>[980000,1150000,1320000,1350000]],'chartDatasetLabel'=>'Revenue ($)','chartColor'=>'#6366f1','position'=>['x'=>40,'y'=>150],'styles'=>['width'=>460,'height'=>280,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'exec2-line','type'=>'line-chart','chartTitle'=>'Monthly Growth %','chartData'=>['labels'=>['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],'values'=>[5,7,6,9,11,10,14,13,16,15,18,20]],'chartColor'=>'#10b981','position'=>['x'=>40,'y'=>460],'styles'=>['width'=>460,'height'=>240,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'exec2-tbl','type'=>'table','columns'=>['Department','Budget','Actual','Variance'],'data'=>[['Department'=>'Engineering','Budget'=>'$1.2M','Actual'=>'$1.1M','Variance'=>'+8.3%'],['Department'=>'Marketing','Budget'=>'$480K','Actual'=>'$510K','Variance'=>'-6.3%'],['Department'=>'Operations','Budget'=>'$620K','Actual'=>'$590K','Variance'=>'+4.8%'],['Department'=>'HR & Admin','Budget'=>'$310K','Actual'=>'$295K','Variance'=>'+4.8%']],'position'=>['x'=>40,'y'=>730],'styles'=>['width'=>714,'height'=>200,'zIndex'=>3,'headerBg'=>'#6366f1','headerColor'=>'#fff','evenRowBg'=>'#1e293b','oddRowBg'=>'#0f172a','opacity'=>100]],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'page_size'        => 'A4',
                    'orientation'      => 'portrait',
                    'primary_color'    => '#6366f1',
                    'background_color' => '#0f172a',
                    'font_family'      => "'DM Sans', sans-serif",
                    'margin'           => 40,
                    'show_page_numbers'=> true,
                    'show_header'      => false,
                    'footer_left'      => 'CONFIDENTIAL',
                    'footer_right'     => '© 2024 Company Name',
                    'dark_mode_preview'=> true,
                ],
            ],

            [
                'name'        => 'Modern Minimal',
                'slug'        => 'modern-minimal',
                'description' => 'Clean typography-first layout with generous whitespace, subtle accents, and structured sections.',
                'thumbnail'   => null,
                'is_active'   => true,
                'structure'   => [
                    'pages' => [
                        [
                            'id'       => 'page-min-1',
                            'label'    => 'Cover',
                            'elements' => [
                                // Top green accent strip
                                ['id'=>'min-strip','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>4,'backgroundColor'=>'#10b981','zIndex'=>2,'borderRadius'=>0,'opacity'=>100]],
                                // Side accent line
                                ['id'=>'min-side','type'=>'rectangle','position'=>['x'=>60,'y'=>200],'styles'=>['width'=>3,'height'=>320,'backgroundColor'=>'#10b981','zIndex'=>2,'borderRadius'=>2,'opacity'=>100]],
                                // Report type label
                                ['id'=>'min-label','type'=>'text','content'=>'BUSINESS REPORT · 2024','position'=>['x'=>80,'y'=>160],'styles'=>['width'=>300,'height'=>24,'fontSize'=>11,'fontWeight'=>'600','color'=>'#10b981','letterSpacing'=>3,'textTransform'=>'uppercase','zIndex'=>3,'opacity'=>100]],
                                // Main title
                                ['id'=>'min-title','type'=>'heading','content'=>'Strategic<br>Growth Plan','position'=>['x'=>80,'y'=>210],'styles'=>['width'=>560,'height'=>200,'fontSize'=>64,'fontWeight'=>'300','color'=>'#0f172a','lineHeight'=>1.05,'fontFamily'=>"'DM Sans', sans-serif",'zIndex'=>3,'opacity'=>100]],
                                // Divider
                                ['id'=>'min-hdiv','type'=>'divider','position'=>['x'=>80,'y'=>430],'styles'=>['width'=>480,'height'=>1,'borderWidth'=>1,'color'=>'#e2e8f0','zIndex'=>3,'opacity'=>100]],
                                // Author / date block
                                ['id'=>'min-author','type'=>'text','content'=>'Prepared for Leadership Review','position'=>['x'=>80,'y'=>450],'styles'=>['width'=>360,'height'=>24,'fontSize'=>14,'color'=>'#475569','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min-date','type'=>'date','position'=>['x'=>80,'y'=>480],'styles'=>['width'=>200,'height'=>22,'fontSize'=>13,'color'=>'#94a3b8','zIndex'=>3,'opacity'=>100]],
                                // Cover image placeholder
                                ['id'=>'min-img','type'=>'image','src'=>'','position'=>['x'=>60,'y'=>560],'styles'=>['width'=>674,'height'=>340,'borderRadius'=>16,'objectFit'=>'cover','zIndex'=>3,'opacity'=>100]],
                                // Bottom dots decoration
                                ['id'=>'min-dot1','type'=>'circle','position'=>['x'=>60,'y'=>930],'styles'=>['width'=>8,'height'=>8,'backgroundColor'=>'#10b981','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min-dot2','type'=>'circle','position'=>['x'=>80,'y'=>930],'styles'=>['width'=>8,'height'=>8,'backgroundColor'=>'#10b981','zIndex'=>3,'opacity'=>40]],
                                ['id'=>'min-dot3','type'=>'circle','position'=>['x'=>100,'y'=>930],'styles'=>['width'=>8,'height'=>8,'backgroundColor'=>'#10b981','zIndex'=>3,'opacity'=>20]],
                                ['id'=>'min-page','type'=>'pagenum','position'=>['x'=>697,'y'=>1080],'styles'=>['width'=>40,'height'=>20,'fontSize'=>11,'color'=>'#94a3b8','textAlign'=>'right','zIndex'=>5,'opacity'=>100]],
                            ],
                        ],
                        [
                            'id'       => 'page-min-2',
                            'label'    => 'Executive Summary',
                            'elements' => [
                                ['id'=>'min2-strip','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>4,'backgroundColor'=>'#10b981','zIndex'=>2,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'min2-h','type'=>'heading','content'=>'Executive Summary','position'=>['x'=>60,'y'=>60],'styles'=>['width'=>500,'height'=>52,'fontSize'=>32,'fontWeight'=>'300','color'=>'#0f172a','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min2-div','type'=>'divider','position'=>['x'=>60,'y'=>120],'styles'=>['width'=>100,'height'=>2,'borderWidth'=>3,'color'=>'#10b981','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min2-body','type'=>'text','content'=>'This report presents a comprehensive overview of our strategic initiatives and performance metrics for the 2024 fiscal year. Our focus on operational excellence and customer-centric growth has yielded strong results across all business units.','position'=>['x'=>60,'y'=>145],'styles'=>['width'=>674,'height'=>80,'fontSize'=>14,'color'=>'#475569','lineHeight'=>1.8,'zIndex'=>3,'opacity'=>100]],
                                // 3 highlight boxes
                                ['id'=>'min2-box1','type'=>'rectangle','position'=>['x'=>60,'y'=>250],'styles'=>['width'=>210,'height'=>110,'backgroundColor'=>'#f0fdf4','borderColor'=>'#10b981','borderWidth'=>1,'borderRadius'=>12,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min2-boxh1','type'=>'subheading','content'=>'Revenue Growth','position'=>['x'=>76,'y'=>268],'styles'=>['width'=>180,'height'=>24,'fontSize'=>11,'fontWeight'=>'600','color'=>'#10b981','letterSpacing'=>1,'textTransform'=>'uppercase','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'min2-boxv1','type'=>'heading','content'=>'+18.2%','position'=>['x'=>76,'y'=>298],'styles'=>['width'=>180,'height'=>44,'fontSize'=>28,'fontWeight'=>'700','color'=>'#059669','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'min2-box2','type'=>'rectangle','position'=>['x'=>292,'y'=>250],'styles'=>['width'=>210,'height'=>110,'backgroundColor'=>'#f8fafc','borderColor'=>'#e2e8f0','borderWidth'=>1,'borderRadius'=>12,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min2-boxh2','type'=>'subheading','content'=>'New Customers','position'=>['x'=>308,'y'=>268],'styles'=>['width'=>180,'height'=>24,'fontSize'=>11,'fontWeight'=>'600','color'=>'#64748b','letterSpacing'=>1,'textTransform'=>'uppercase','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'min2-boxv2','type'=>'heading','content'=>'3,840','position'=>['x'=>308,'y'=>298],'styles'=>['width'=>180,'height'=>44,'fontSize'=>28,'fontWeight'=>'700','color'=>'#0f172a','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'min2-box3','type'=>'rectangle','position'=>['x'=>524,'y'=>250],'styles'=>['width'=>210,'height'=>110,'backgroundColor'=>'#f8fafc','borderColor'=>'#e2e8f0','borderWidth'=>1,'borderRadius'=>12,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min2-boxh3','type'=>'subheading','content'=>'NPS Score','position'=>['x'=>540,'y'=>268],'styles'=>['width'=>180,'height'=>24,'fontSize'=>11,'fontWeight'=>'600','color'=>'#64748b','letterSpacing'=>1,'textTransform'=>'uppercase','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'min2-boxv3','type'=>'heading','content'=>'72','position'=>['x'=>540,'y'=>298],'styles'=>['width'=>180,'height'=>44,'fontSize'=>28,'fontWeight'=>'700','color'=>'#0f172a','zIndex'=>4,'opacity'=>100]],
                                // Chart
                                ['id'=>'min2-chart','type'=>'line-chart','chartTitle'=>'Monthly Revenue Trend','chartData'=>['labels'=>['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],'values'=>[310,340,290,420,390,470,510,490,560,540,610,680]],'chartColor'=>'#10b981','position'=>['x'=>60,'y'=>390],'styles'=>['width'=>674,'height'=>280,'zIndex'=>3,'opacity'=>100]],
                                // Goals list
                                ['id'=>'min2-goals-h','type'=>'subheading','content'=>'Strategic Goals 2025','position'=>['x'=>60,'y'=>700],'styles'=>['width'=>300,'height'=>28,'fontSize'=>16,'fontWeight'=>'600','color'=>'#0f172a','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min2-goals','type'=>'list','items'=>['Expand into 3 new markets by Q3','Launch v2.0 of the core product platform','Achieve $6M ARR milestone','Reduce operational costs by 12%','Grow team to 85 headcount'],'position'=>['x'=>60,'y'=>740],'styles'=>['width'=>360,'height'=>200,'fontSize'=>14,'color'=>'#475569','lineHeight'=>1.8,'listStyle'=>'bullet','zIndex'=>3,'opacity'=>100]],
                                // Pie chart
                                ['id'=>'min2-pie','type'=>'pie-chart','chartTitle'=>'Revenue by Segment','chartData'=>['labels'=>['Enterprise','SMB','Startup','Education'],'values'=>[42,31,18,9]],'pieColors'=>['#10b981','#0ea5e9','#f59e0b','#8b5cf6'],'position'=>['x'=>450,'y'=>700],'styles'=>['width'=>284,'height'=>260,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'min2-page','type'=>'pagenum','position'=>['x'=>697,'y'=>1080],'styles'=>['width'=>40,'height'=>20,'fontSize'=>11,'color'=>'#94a3b8','textAlign'=>'right','zIndex'=>5,'opacity'=>100]],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'page_size'        => 'A4',
                    'orientation'      => 'portrait',
                    'primary_color'    => '#10b981',
                    'background_color' => '#ffffff',
                    'font_family'      => "'DM Sans', sans-serif",
                    'margin'           => 40,
                    'show_page_numbers'=> true,
                    'footer_left'      => 'Strategic Growth Plan 2024',
                    'footer_right'     => 'Confidential',
                ],
            ],

            [
                'name'        => 'Bold Analytics',
                'slug'        => 'bold-analytics',
                'description' => 'High-impact data-forward design. Heavy typography, rich chart layouts, and bold color contrast.',
                'thumbnail'   => null,
                'is_active'   => true,
                'structure'   => [
                    'pages' => [
                        [
                            'id'       => 'page-bold-1',
                            'label'    => 'Cover',
                            'elements' => [
                                // Dark bg
                                ['id'=>'bold-bg','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>1123,'backgroundColor'=>'#1e293b','zIndex'=>0,'borderRadius'=>0,'opacity'=>100]],
                                // Orange circle bg
                                ['id'=>'bold-circ1','type'=>'circle','position'=>['x'=>-100,'y'=>600],'styles'=>['width'=>500,'height'=>500,'backgroundColor'=>'#f59e0b','zIndex'=>1,'opacity'=>10]],
                                ['id'=>'bold-circ2','type'=>'circle','position'=>['x'=>400,'y'=>-80],'styles'=>['width'=>300,'height'=>300,'backgroundColor'=>'#f97316','zIndex'=>1,'opacity'=>8]],
                                // ANALYTICS label
                                ['id'=>'bold-tag','type'=>'text','content'=>'ANALYTICS REPORT','position'=>['x'=>60,'y'=>80],'styles'=>['width'=>300,'height'=>22,'fontSize'=>11,'fontWeight'=>'700','color'=>'#f59e0b','letterSpacing'=>4,'zIndex'=>4,'opacity'=>100]],
                                // Giant title
                                ['id'=>'bold-t1','type'=>'heading','content'=>'DATA','position'=>['x'=>60,'y'=>110],'styles'=>['width'=>600,'height'=>160,'fontSize'=>130,'fontWeight'=>'900','color'=>'#f8fafc','lineHeight'=>0.95,'zIndex'=>3,'fontFamily'=>"'DM Sans', sans-serif",'opacity'=>100]],
                                ['id'=>'bold-t2','type'=>'heading','content'=>'REPORT','position'=>['x'=>60,'y'=>260],'styles'=>['width'=>600,'height'=>160,'fontSize'=>130,'fontWeight'=>'900','color'=>'#f59e0b','lineHeight'=>0.95,'zIndex'=>3,'fontFamily'=>"'DM Sans', sans-serif",'opacity'=>100]],
                                // Mini bar chart decoration
                                ['id'=>'bold-bar1','type'=>'rectangle','position'=>['x'=>60,'y'=>440],'styles'=>['width'=>16,'height'=>40,'backgroundColor'=>'#f59e0b','borderRadius'=>3,'zIndex'=>4,'opacity'=>100]],
                                ['id'=>'bold-bar2','type'=>'rectangle','position'=>['x'=>84,'y'=>420],'styles'=>['width'=>16,'height'=>60,'backgroundColor'=>'#f59e0b','borderRadius'=>3,'zIndex'=>4,'opacity'=>80]],
                                ['id'=>'bold-bar3','type'=>'rectangle','position'=>['x'=>108,'y'=>430],'styles'=>['width'=>16,'height'=>50,'backgroundColor'=>'#f59e0b','borderRadius'=>3,'zIndex'=>4,'opacity'=>60]],
                                ['id'=>'bold-bar4','type'=>'rectangle','position'=>['x'=>132,'y'=>410],'styles'=>['width'=>16,'height'=>70,'backgroundColor'=>'#f59e0b','borderRadius'=>3,'zIndex'=>4,'opacity'=>40]],
                                ['id'=>'bold-bar5','type'=>'rectangle','position'=>['x'=>156,'y'=>400],'styles'=>['width'=>16,'height'=>80,'backgroundColor'=>'#f59e0b','borderRadius'=>3,'zIndex'=>4,'opacity'=>20]],
                                // Description
                                ['id'=>'bold-desc','type'=>'text','content'=>'Comprehensive business intelligence and performance analytics for Q4 2024 strategic decision-making.','position'=>['x'=>60,'y'=>530],'styles'=>['width'=>560,'height'=>60,'fontSize'=>15,'color'=>'#94a3b8','lineHeight'=>1.7,'zIndex'=>4,'opacity'=>100]],
                                // Stat strip
                                ['id'=>'bold-stat-bg','type'=>'rectangle','position'=>['x'=>0,'y'=>640],'styles'=>['width'=>794,'height'=>110,'backgroundColor'=>'#0f172a','zIndex'=>3,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'bold-s1v','type'=>'heading','content'=>'$12.4M','position'=>['x'=>60,'y'=>655],'styles'=>['width'=>160,'height'=>44,'fontSize'=>28,'fontWeight'=>'800','color'=>'#f59e0b','zIndex'=>5,'opacity'=>100]],
                                ['id'=>'bold-s1l','type'=>'text','content'=>'Total ARR','position'=>['x'=>60,'y'=>700],'styles'=>['width'=>160,'height'=>20,'fontSize'=>11,'color'=>'#64748b','zIndex'=>5,'opacity'=>100]],
                                ['id'=>'bold-s2v','type'=>'heading','content'=>'98.4%','position'=>['x'=>240,'y'=>655],'styles'=>['width'=>160,'height'=>44,'fontSize'=>28,'fontWeight'=>'800','color'=>'#10b981','zIndex'=>5,'opacity'=>100]],
                                ['id'=>'bold-s2l','type'=>'text','content'=>'Uptime SLA','position'=>['x'=>240,'y'=>700],'styles'=>['width'=>160,'height'=>20,'fontSize'=>11,'color'=>'#64748b','zIndex'=>5,'opacity'=>100]],
                                ['id'=>'bold-s3v','type'=>'heading','content'=>'24,800','position'=>['x'=>420,'y'=>655],'styles'=>['width'=>160,'height'=>44,'fontSize'=>28,'fontWeight'=>'800','color'=>'#f8fafc','zIndex'=>5,'opacity'=>100]],
                                ['id'=>'bold-s3l','type'=>'text','content'=>'Active Users','position'=>['x'=>420,'y'=>700],'styles'=>['width'=>160,'height'=>20,'fontSize'=>11,'color'=>'#64748b','zIndex'=>5,'opacity'=>100]],
                                ['id'=>'bold-s4v','type'=>'heading','content'=>'+31%','position'=>['x'=>600,'y'=>655],'styles'=>['width'=>140,'height'=>44,'fontSize'=>28,'fontWeight'=>'800','color'=>'#a78bfa','zIndex'=>5,'opacity'=>100]],
                                ['id'=>'bold-s4l','type'=>'text','content'=>'YoY Growth','position'=>['x'=>600,'y'=>700],'styles'=>['width'=>140,'height'=>20,'fontSize'=>11,'color'=>'#64748b','zIndex'=>5,'opacity'=>100]],
                                // Bottom footer bar
                                ['id'=>'bold-foot-bg','type'=>'rectangle','position'=>['x'=>0,'y'=>1083],'styles'=>['width'=>794,'height'=>40,'backgroundColor'=>'#f59e0b','zIndex'=>3,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'bold-foot-txt','type'=>'text','content'=>'CONFIDENTIAL  ·  ANALYTICS DIVISION  ·  Q4 2024','position'=>['x'=>0,'y'=>1093],'styles'=>['width'=>794,'height'=>20,'fontSize'=>11,'fontWeight'=>'700','color'=>'#1e293b','textAlign'=>'center','letterSpacing'=>2,'zIndex'=>5,'opacity'=>100]],
                            ],
                        ],
                        [
                            'id'       => 'page-bold-2',
                            'label'    => 'Analytics Dashboard',
                            'elements' => [
                                ['id'=>'bold2-bg','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>1123,'backgroundColor'=>'#1e293b','zIndex'=>0,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'bold2-top','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>794,'height'=>60,'backgroundColor'=>'#0f172a','zIndex'=>2,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'bold2-h','type'=>'heading','content'=>'Analytics Dashboard','position'=>['x'=>40,'y'=>12],'styles'=>['width'=>400,'height'=>38,'fontSize'=>22,'fontWeight'=>'700','color'=>'#f8fafc','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'bold2-date','type'=>'text','content'=>'Q4 2024  ·  October – December','position'=>['x'=>500,'y'=>20],'styles'=>['width'=>254,'height'=>22,'fontSize'=>12,'color'=>'#f59e0b','textAlign'=>'right','zIndex'=>4,'opacity'=>100]],
                                // KPI row
                                ['id'=>'bold2-k1','type'=>'metric','label'=>'Monthly Recurring','value'=>'$1.03M','change'=>'8.3%','changeType'=>'positive','changePeriod'=>'MoM','position'=>['x'=>40,'y'=>80],'styles'=>['width'=>158,'height'=>100,'backgroundColor'=>'#0f172a','borderColor'=>'#f59e0b','borderWidth'=>1,'borderRadius'=>10,'color'=>'#f59e0b','opacity'=>100,'zIndex'=>3]],
                                ['id'=>'bold2-k2','type'=>'metric','label'=>'Churn Rate','value'=>'2.1%','change'=>'0.4%','changeType'=>'negative','changePeriod'=>'MoM','position'=>['x'=>210,'y'=>80],'styles'=>['width'=>158,'height'=>100,'backgroundColor'=>'#0f172a','borderColor'=>'#334155','borderWidth'=>1,'borderRadius'=>10,'color'=>'#ef4444','opacity'=>100,'zIndex'=>3]],
                                ['id'=>'bold2-k3','type'=>'metric','label'=>'Avg Deal Size','value'=>'$8,420','change'=>'12.0%','changeType'=>'positive','changePeriod'=>'QoQ','position'=>['x'=>380,'y'=>80],'styles'=>['width'=>158,'height'=>100,'backgroundColor'=>'#0f172a','borderColor'=>'#334155','borderWidth'=>1,'borderRadius'=>10,'color'=>'#10b981','opacity'=>100,'zIndex'=>3]],
                                ['id'=>'bold2-k4','type'=>'metric','label'=>'CSAT Score','value'=>'4.7/5','change'=>'0.2','changeType'=>'positive','changePeriod'=>'QoQ','position'=>['x'=>550,'y'=>80],'styles'=>['width'=>205,'height'=>100,'backgroundColor'=>'#0f172a','borderColor'=>'#334155','borderWidth'=>1,'borderRadius'=>10,'color'=>'#a78bfa','opacity'=>100,'zIndex'=>3]],
                                // Charts
                                ['id'=>'bold2-area','type'=>'area-chart','chartTitle'=>'Revenue by Month','chartData'=>['labels'=>['Oct','Nov','Dec'],'values'=>[920000,1010000,1030000]],'chartColor'=>'#f59e0b','position'=>['x'=>40,'y'=>205],'styles'=>['width'=>350,'height'=>240,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'bold2-dnut','type'=>'doughnut-chart','chartTitle'=>'Traffic Sources','chartData'=>['labels'=>['Direct','Organic','Paid','Referral'],'values'=>[38,28,22,12]],'pieColors'=>['#f59e0b','#f97316','#10b981','#6366f1'],'position'=>['x'=>408,'y'=>205],'styles'=>['width'=>347,'height'=>240,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'bold2-radar','type'=>'radar-chart','chartTitle'=>'Team Performance','chartData'=>['labels'=>['Sales','Support','Engineering','Marketing','Finance','Operations'],'values'=>[88,92,78,85,90,76]],'chartColor'=>'#f59e0b','position'=>['x'=>40,'y'=>470],'styles'=>['width'=>350,'height'=>280,'zIndex'=>3,'opacity'=>100]],
                                // Table
                                ['id'=>'bold2-tbl','type'=>'table','columns'=>['Product','Q4 Sales','Growth','Status'],'data'=>[['Product'=>'Enterprise Plan','Q4 Sales'=>'$4.2M','Growth'=>'+22%','Status'=>'Strong'],['Product'=>'Pro Plan','Q4 Sales'=>'$2.8M','Growth'=>'+15%','Status'=>'Good'],['Product'=>'Starter Plan','Q4 Sales'=>'$1.1M','Growth'=>'+8%','Status'=>'Stable'],['Product'=>'Add-ons','Q4 Sales'=>'$0.4M','Growth'=>'+31%','Status'=>'Growing']],'position'=>['x'=>408,'y'=>470],'styles'=>['width'=>346,'height'=>200,'zIndex'=>3,'headerBg'=>'#f59e0b','headerColor'=>'#1e293b','evenRowBg'=>'#0f172a','oddRowBg'=>'#1e293b','opacity'=>100]],
                                // Progress bars
                                ['id'=>'bold2-prog-h','type'=>'subheading','content'=>'Goal Completion','position'=>['x'=>40,'y'=>778],'styles'=>['width'=>300,'height'=>28,'fontSize'=>14,'fontWeight'=>'600','color'=>'#f8fafc','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'bold2-p1','type'=>'progress','label'=>'Revenue Target','value'=>86,'position'=>['x'=>40,'y'=>818],'styles'=>['width'=>714,'height'=>24,'color'=>'#f59e0b','trackColor'=>'#334155','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'bold2-p2','type'=>'progress','label'=>'User Acquisition','value'=>72,'position'=>['x'=>40,'y'=>858],'styles'=>['width'=>714,'height'=>24,'color'=>'#10b981','trackColor'=>'#334155','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'bold2-p3','type'=>'progress','label'=>'Product Roadmap','value'=>91,'position'=>['x'=>40,'y'=>898],'styles'=>['width'=>714,'height'=>24,'color'=>'#a78bfa','trackColor'=>'#334155','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'bold2-foot','type'=>'rectangle','position'=>['x'=>0,'y'=>1083],'styles'=>['width'=>794,'height'=>40,'backgroundColor'=>'#f59e0b','zIndex'=>3,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'bold2-foot-txt','type'=>'text','content'=>'Analytics Dashboard  ·  Q4 2024  ·  Page 2','position'=>['x'=>0,'y'=>1093],'styles'=>['width'=>794,'height'=>20,'fontSize'=>11,'fontWeight'=>'700','color'=>'#1e293b','textAlign'=>'center','zIndex'=>5,'opacity'=>100]],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'page_size'        => 'A4',
                    'orientation'      => 'portrait',
                    'primary_color'    => '#f59e0b',
                    'background_color' => '#1e293b',
                    'font_family'      => "'DM Sans', sans-serif",
                    'margin'           => 40,
                    'show_page_numbers'=> true,
                    'footer_left'      => 'Analytics Division',
                    'footer_right'     => 'Confidential',
                    'dark_mode_preview'=> true,
                ],
            ],

            [
                'name'        => 'Clean Professional',
                'slug'        => 'clean-professional',
                'description' => 'Balanced white-space design with indigo accents, perfect for business proposals and quarterly reviews.',
                'thumbnail'   => null,
                'is_active'   => true,
                'structure'   => [
                    'pages' => [
                        [
                            'id'       => 'page-clean-1',
                            'label'    => 'Cover',
                            'elements' => [
                                // Left sidebar accent
                                ['id'=>'cln-sidebar','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>220,'height'=>1123,'backgroundColor'=>'#6366f1','zIndex'=>1,'borderRadius'=>0,'opacity'=>100]],
                                // Logo box
                                ['id'=>'cln-logo-bg','type'=>'rectangle','position'=>['x'=>30,'y'=>50],'styles'=>['width'=>60,'height'=>60,'backgroundColor'=>'rgba(255,255,255,0.2)','borderRadius'=>14,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'cln-logo-txt','type'=>'heading','content'=>'B','position'=>['x'=>46,'y'=>60],'styles'=>['width'=>30,'height'=>40,'fontSize'=>28,'fontWeight'=>'800','color'=>'#ffffff','textAlign'=>'center','zIndex'=>4,'opacity'=>100]],
                                // Company on sidebar
                                ['id'=>'cln-company','type'=>'text','content'=>'Brand Corp','position'=>['x'=>30,'y'=>125],'styles'=>['width'=>160,'height'=>22,'fontSize'=>13,'fontWeight'=>'600','color'=>'rgba(255,255,255,0.8)','zIndex'=>4,'opacity'=>100]],
                                // Sidebar nav labels
                                ['id'=>'cln-nav1','type'=>'text','content'=>'Overview','position'=>['x'=>30,'y'=>280],'styles'=>['width'=>160,'height'=>22,'fontSize'=>12,'color'=>'rgba(255,255,255,0.9)','fontWeight'=>'600','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-nav2','type'=>'text','content'=>'Financials','position'=>['x'=>30,'y'=>310],'styles'=>['width'=>160,'height'=>22,'fontSize'=>12,'color'=>'rgba(255,255,255,0.5)','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-nav3','type'=>'text','content'=>'Operations','position'=>['x'=>30,'y'=>336],'styles'=>['width'=>160,'height'=>22,'fontSize'=>12,'color'=>'rgba(255,255,255,0.5)','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-nav4','type'=>'text','content'=>'Roadmap','position'=>['x'=>30,'y'=>362],'styles'=>['width'=>160,'height'=>22,'fontSize'=>12,'color'=>'rgba(255,255,255,0.5)','zIndex'=>4,'opacity'=>100]],
                                // KPIs on sidebar
                                ['id'=>'cln-kpi1-v','type'=>'heading','content'=>'$4.8M','position'=>['x'=>30,'y'=>560],'styles'=>['width'=>160,'height'=>40,'fontSize'=>26,'fontWeight'=>'800','color'=>'#ffffff','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-kpi1-l','type'=>'text','content'=>'Revenue','position'=>['x'=>30,'y'=>605],'styles'=>['width'=>160,'height'=>20,'fontSize'=>11,'color'=>'rgba(255,255,255,0.6)','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-divs','type'=>'divider','position'=>['x'=>30,'y'=>638],'styles'=>['width'=>160,'height'=>1,'borderWidth'=>1,'color'=>'rgba(255,255,255,0.2)','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-kpi2-v','type'=>'heading','content'=>'12,400','position'=>['x'=>30,'y'=>655],'styles'=>['width'=>160,'height'=>40,'fontSize'=>26,'fontWeight'=>'800','color'=>'#ffffff','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-kpi2-l','type'=>'text','content'=>'Customers','position'=>['x'=>30,'y'=>700],'styles'=>['width'=>160,'height'=>20,'fontSize'=>11,'color'=>'rgba(255,255,255,0.6)','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-divs2','type'=>'divider','position'=>['x'=>30,'y'=>733],'styles'=>['width'=>160,'height'=>1,'borderWidth'=>1,'color'=>'rgba(255,255,255,0.2)','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-kpi3-v','type'=>'heading','content'=>'+24%','position'=>['x'=>30,'y'=>750],'styles'=>['width'=>160,'height'=>40,'fontSize'=>26,'fontWeight'=>'800','color'=>'#bfdbfe','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-kpi3-l','type'=>'text','content'=>'YoY Growth','position'=>['x'=>30,'y'=>795],'styles'=>['width'=>160,'height'=>20,'fontSize'=>11,'color'=>'rgba(255,255,255,0.6)','zIndex'=>4,'opacity'=>100]],
                                // Main content area
                                ['id'=>'cln-year-tag','type'=>'text','content'=>'QUARTERLY BUSINESS REVIEW','position'=>['x'=>250,'y'=>80],'styles'=>['width'=>500,'height'=>20,'fontSize'=>11,'fontWeight'=>'700','color'=>'#6366f1','letterSpacing'=>3,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'cln-title','type'=>'heading','content'=>'Performance<br>Report Q4','position'=>['x'=>250,'y'=>110],'styles'=>['width'=>520,'height'=>180,'fontSize'=>58,'fontWeight'=>'800','color'=>'#0f172a','lineHeight'=>1.0,'fontFamily'=>"'DM Sans', sans-serif",'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'cln-div-main','type'=>'divider','position'=>['x'=>250,'y'=>310],'styles'=>['width'=>80,'height'=>1,'borderWidth'=>3,'color'=>'#6366f1','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'cln-desc','type'=>'text','content'=>'This report covers the operational and financial performance of BrandCorp across all business units for Q4 2024, with forward-looking commentary for 2025 strategy.','position'=>['x'=>250,'y'=>328],'styles'=>['width'=>520,'height'=>72,'fontSize'=>14,'color'=>'#475569','lineHeight'=>1.8,'zIndex'=>3,'opacity'=>100]],
                                // Cover image
                                ['id'=>'cln-img','type'=>'image','src'=>'','position'=>['x'=>250,'y'=>440],'styles'=>['width'=>524,'height'=>360,'borderRadius'=>16,'objectFit'=>'cover','zIndex'=>3,'opacity'=>100]],
                                // Prepared by section
                                ['id'=>'cln-prep-bg','type'=>'rectangle','position'=>['x'=>250,'y'=>840],'styles'=>['width'=>524,'height'=>80,'backgroundColor'=>'#f8fafc','borderRadius'=>12,'borderColor'=>'#e2e8f0','borderWidth'=>1,'zIndex'=>3,'opacity'=>100]],
                                ['id'=>'cln-prep-l','type'=>'text','content'=>'Prepared by','position'=>['x'=>270,'y'=>858],'styles'=>['width'=>140,'height'=>18,'fontSize'=>11,'color'=>'#94a3b8','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-prep-n','type'=>'text','content'=>'Finance & Strategy Team','position'=>['x'=>270,'y'=>878],'styles'=>['width'=>220,'height'=>22,'fontSize'=>14,'fontWeight'=>'600','color'=>'#1e293b','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln-prep-dt','type'=>'date','position'=>['x'=>490,'y'=>870],'styles'=>['width'=>264,'height'=>20,'fontSize'=>12,'color'=>'#6366f1','textAlign'=>'right','zIndex'=>4,'opacity'=>100]],
                            ],
                        ],
                        [
                            'id'       => 'page-clean-2',
                            'label'    => 'Financial Overview',
                            'elements' => [
                                ['id'=>'cln2-sidebar','type'=>'rectangle','position'=>['x'=>0,'y'=>0],'styles'=>['width'=>220,'height'=>1123,'backgroundColor'=>'#6366f1','zIndex'=>1,'borderRadius'=>0,'opacity'=>100]],
                                ['id'=>'cln2-pg','type'=>'text','content'=>'02','position'=>['x'=>30,'y'=>50],'styles'=>['width'=>160,'height'=>60,'fontSize'=>48,'fontWeight'=>'800','color'=>'rgba(255,255,255,0.15)','zIndex'=>4,'opacity'=>100]],
                                ['id'=>'cln2-sec','type'=>'text','content'=>'FINANCIAL OVERVIEW','position'=>['x'=>30,'y'=>120],'styles'=>['width'=>160,'height'=>40,'fontSize'=>11,'fontWeight'=>'700','color'=>'rgba(255,255,255,0.8)','letterSpacing'=>1,'zIndex'=>4,'opacity'=>100]],
                                // Main heading
                                ['id'=>'cln2-h','type'=>'heading','content'=>'Financial Overview','position'=>['x'=>250,'y'=>50],'styles'=>['width'=>524,'height'=>52,'fontSize'=>30,'fontWeight'=>'700','color'=>'#0f172a','zIndex'=>3,'opacity'=>100]],
                                ['id'=>'cln2-sub','type'=>'text','content'=>'Revenue, expenses and profitability metrics for Q4 2024','position'=>['x'=>250,'y'=>108],'styles'=>['width'=>524,'height'=>22,'fontSize'=>13,'color'=>'#64748b','zIndex'=>3,'opacity'=>100]],
                                // Bar chart
                                ['id'=>'cln2-bar','type'=>'bar-chart','chartTitle'=>'Quarterly Revenue vs Target','chartData'=>['labels'=>['Q1','Q2','Q3','Q4'],'values'=>[980,1150,1320,1480]],'chartColor'=>'#6366f1','chartDatasetLabel'=>'Revenue ($K)','position'=>['x'=>250,'y'=>150],'styles'=>['width'=>524,'height'=>260,'zIndex'=>3,'opacity'=>100]],
                                // Table
                                ['id'=>'cln2-tbl','type'=>'table','columns'=>['Category','Budget','Actual','%'],'data'=>[['Category'=>'Product Revenue','Budget'=>'$3.2M','Actual'=>'$3.8M','%'=>'+18.7%'],['Category'=>'Service Revenue','Budget'=>'$0.9M','Actual'=>'$1.0M','%'=>'+11.1%'],['Category'=>'COGS','Budget'=>'$1.1M','Actual'=>'$0.95M','%'=>'-13.6%'],['Category'=>'Gross Profit','Budget'=>'$3.0M','Actual'=>'$3.85M','%'=>'+28.3%'],['Category'=>'OPEX','Budget'=>'$2.1M','Actual'=>'$2.0M','%'=>'-4.8%'],['Category'=>'Net Income','Budget'=>'$0.9M','Actual'=>'$1.85M','%'=>'+105%']],'position'=>['x'=>250,'y'=>432],'styles'=>['width'=>524,'height'=>220,'zIndex'=>3,'headerBg'=>'#6366f1','headerColor'=>'#fff','evenRowBg'=>'#ffffff','oddRowBg'=>'#f8fafc','opacity'=>100]],
                                // Donut
                                ['id'=>'cln2-dnut','type'=>'doughnut-chart','chartTitle'=>'Cost Breakdown','chartData'=>['labels'=>['COGS','Sales & Mktg','Engineering','G&A'],'values'=>[28,32,25,15]],'pieColors'=>['#6366f1','#8b5cf6','#a78bfa','#c4b5fd'],'position'=>['x'=>250,'y'=>672],'styles'=>['width'=>260,'height'=>240,'zIndex'=>3,'opacity'=>100]],
                                // Quote
                                ['id'=>'cln2-q','type'=>'quote','content'=>'We exceeded every major financial target this quarter, setting a strong foundation for our 2025 expansion plans.','position'=>['x'=>526,'y'=>672],'styles'=>['width'=>248,'height'=>120,'fontSize'=>13,'fontStyle'=>'italic','color'=>'#475569','backgroundColor'=>'#f8fafc','borderColor'=>'#6366f1','borderWidth'=>3,'borderRadius'=>4,'padding'=>16,'zIndex'=>3,'opacity'=>100]],
                                // Badge
                                ['id'=>'cln2-badge','type'=>'badge','content'=>'RECORD QUARTER','position'=>['x'=>526,'y'=>808],'styles'=>['width'=>248,'height'=>36,'backgroundColor'=>'#ede9fe','color'=>'#6d28d9','borderRadius'=>8,'fontSize'=>12,'fontWeight'=>'700','textAlign'=>'center','zIndex'=>3,'opacity'=>100]],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'page_size'        => 'A4',
                    'orientation'      => 'portrait',
                    'primary_color'    => '#6366f1',
                    'background_color' => '#ffffff',
                    'font_family'      => "'DM Sans', sans-serif",
                    'margin'           => 40,
                    'show_page_numbers'=> true,
                    'footer_left'      => 'BrandCorp – Q4 2024',
                    'footer_right'     => 'Confidential',
                ],
            ],
        ];

        foreach ($templates as $data) {
            Template::create([
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'],
                'thumbnail'   => $data['thumbnail'],
                'is_active'   => $data['is_active'],
                'structure'   => $data['structure'],
                'settings'    => $data['settings'],
            ]);
        }
    }
}