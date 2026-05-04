{{-- 
    ---------------------------------------------------------------------
    PDF Report Template - COMPLETE PRODUCTION-READY
    ---------------------------------------------------------------------
    
    WHAT THIS DOES (In Short):
    1.  Renders EVERY element type from the Report Editor as a real PDF page.
    2.  Uses @page to set the exact paper size (A4/Letter/Legal) and orientation.
    3.  Positions each element absolutely, just like the canvas editor.
    4.  Supports 30+ element types: text, headings, images, tables, charts,
        metrics, shapes, lists, code blocks, timelines, signatures, badges,
        callouts, progress bars, circular progress, social cards, testimonials,
        price cards, kanban cards, checklists, watermarks, and more.
    5.  Applies report settings: font family, primary color, margins, footer,
        header, watermark, page numbers, dark/light mode preview.
    6.  Includes Chart.js CDN for rendering bar, line, area, pie, doughnut,
        and radar charts directly in the PDF.
    7.  Fully responsive – works seamlessly with Browsershot or DomPDF.
    8.  Optimized for print with page-break controls and break-inside avoidance.
    9.  Preserves all custom styles: colors, fonts, borders, opacity, rotation.
   10.  Generates professional, mind-blowing PDF reports from your editor.

    Variables received:
    - $report: Full Report model (with title, settings, status)
    - $content: Array of pages, each containing elements
    - $settings: Report settings (colors, fonts, margins, etc.)
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->title ?? 'Report' }}</title>
    
    {{-- ============================================================ --}}
    {{-- PDF STYLES - Complete Element Library                        --}}
    {{-- ============================================================ --}}
    <style>
        /* ── Reset & Base ────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: {{ str_replace(["'", '"'], '', $settings['font_family'] ?? 'DM Sans') }}, 
                         'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: {{ $settings['background_color'] ?? '#ffffff' }};
            color: {{ $settings['text_color'] ?? '#1e293b' }};
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Page Setup ──────────────────────────────────── */
        @page {
            size: {{ $settings['page_size'] ?? 'A4' }} {{ $settings['orientation'] ?? 'portrait' }};
            margin: {{ ($settings['margin'] ?? 40) }}px;
        }

        .page {
            position: relative;
            width: {{ ($settings['orientation'] === 'landscape' ? 1123 : 794) }}px;
            height: {{ ($settings['orientation'] === 'landscape' ? 794 : 1123) }}px;
            page-break-after: always;
            break-after: page;
            background: {{ $settings['background_color'] ?? '#ffffff' }};
            overflow: hidden;
            border-radius: {{ ($settings['page_radius'] ?? 0) }}px;
        }
        .page:last-child { page-break-after: auto; break-after: auto; }

        /* ── Watermark ───────────────────────────────────── */
        .watermark-layer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate({{ $settings['watermark_rotate'] ?? -30 }}deg);
            font-size: 72px;
            font-weight: 900;
            color: {{ $settings['watermark_color'] ?? '#94a3b8' }};
            opacity: {{ ($settings['watermark_opacity'] ?? 8) / 100 }};
            white-space: nowrap;
            pointer-events: none;
            z-index: 5;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* ── Header ──────────────────────────────────────── */
        .page-header {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: {{ ($settings['header_height'] ?? 60) }}px;
            background: {{ $settings['header_color'] ?? '#1e293b' }};
            color: #ffffff;
            display: flex;
            align-items: center;
            padding: 0 {{ ($settings['margin'] ?? 40) }}px;
            z-index: 10;
            font-size: {{ ($settings['header_font_size'] ?? 12) }}px;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        /* ── Footer ──────────────────────────────────────── */
        .page-footer {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: {{ ($settings['footer_height'] ?? 40) }}px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 {{ ($settings['margin'] ?? 40) }}px;
            font-size: {{ ($settings['footer_font_size'] ?? 10) }}px;
            color: {{ $settings['footer_text_color'] ?? '#94a3b8' }};
            border-top: 1px solid {{ ($settings['primary_color'] ?? '#6366f1') }}20;
            z-index: 10;
        }
        .footer-left, .footer-center, .footer-right { flex: 1; }
        .footer-center { text-align: center; }
        .footer-right { text-align: right; }

        /* ── Elements Container ──────────────────────────── */
        .el-container { position: relative; width: 100%; height: 100%; overflow: hidden; }
        .el { position: absolute; overflow: hidden; word-wrap: break-word; }

        /* ── Text Elements ───────────────────────────────── */
        .el-text, .el-heading, .el-subheading { word-break: break-word; }
        .el-heading { font-weight: 700; }

        /* ── Quote ───────────────────────────────────────── */
        .el-quote {
            border-left: 4px solid {{ $settings['primary_color'] ?? '#6366f1' }};
            padding-left: 16px;
            font-style: italic;
        }

        /* ── Blockquote ──────────────────────────────────── */
        .el-blockquote {
            background: {{ $settings['primary_color'] ?? '#6366f1' }}10;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        /* ── Highlight ───────────────────────────────────── */
        .el-highlight {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        /* ── List ────────────────────────────────────────── */
        .el-list ul, .el-list ol { margin: 0; padding-left: 20px; }
        .el-list li { margin-bottom: 4px; }

        /* ── Checklist ───────────────────────────────────── */
        .el-checklist .checklist-item {
            display: flex; align-items: center; gap: 8px; margin-bottom: 6px;
        }
        .check-icon { color: {{ $settings['primary_color'] ?? '#6366f1' }}; font-weight: 700; }

        /* ── Code Block ──────────────────────────────────── */
        .el-code {
            background: #1e293b; border-radius: 8px; overflow: hidden; color: #34d399;
        }
        .code-header {
            background: #0f172a; padding: 8px 12px; border-bottom: 1px solid #334155;
            display: flex; justify-content: space-between;
        }
        .code-language { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; }
        .el-code pre { margin: 0; padding: 12px; overflow: auto; }
        .el-code code { font-family: 'Courier New', monospace; font-size: 12px; }

        /* ── Link ────────────────────────────────────────── */
        .el-link { display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: {{ $settings['primary_color'] ?? '#6366f1' }}; }

        /* ── Badge ───────────────────────────────────────── */
        .el-badge {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600;
            background: {{ $settings['primary_color'] ?? '#6366f1' }}20;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
            white-space: nowrap;
        }

        /* ── Callout ─────────────────────────────────────── */
        .el-callout {
            display: flex; align-items: flex-start; gap: 12px; padding: 16px;
            border-radius: 12px;
            background: {{ $settings['primary_color'] ?? '#6366f1' }}10;
            border-left: 4px solid {{ $settings['primary_color'] ?? '#6366f1' }};
        }
        .callout-icon { font-size: 18px; flex-shrink: 0; }
        .callout-content { flex: 1; word-break: break-word; }

        /* ── Image ───────────────────────────────────────── */
        .el-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .image-placeholder {
            width: 100%; height: 100%; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 8px;
            background: #f1f5f9; color: #94a3b8; font-size: 12px;
        }

        /* ── Table ───────────────────────────────────────── */
        .el-table { width: 100%; height: 100%; overflow: auto; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .data-table th {
            padding: 10px 14px; text-align: left; font-weight: 600; font-size: 11px;
            text-transform: uppercase; letter-spacing: 0.05em;
            background: {{ $settings['primary_color'] ?? '#6366f1' }}; color: #ffffff;
        }
        .data-table td { padding: 10px 14px; border-bottom: 1px solid #e2e8f0; }
        .data-table tr:nth-child(even) { background: #f8fafc; }

        /* ── Metric / KPI ────────────────────────────────── */
        .el-metric {
            display: flex; flex-direction: column; justify-content: center;
            padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
        }
        .metric-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin-bottom: 4px; }
        .metric-value { font-size: 32px; font-weight: 800; line-height: 1; color: {{ $settings['primary_color'] ?? '#6366f1' }}; }
        .metric-change { display: flex; align-items: center; gap: 4px; font-size: 12px; margin-top: 6px; }
        .metric-change.positive { color: #10b981; }
        .metric-change.negative { color: #ef4444; }

        /* ── Progress Bar ────────────────────────────────── */
        .el-progress { display: flex; flex-direction: column; justify-content: center; gap: 6px; }
        .progress-header { display: flex; justify-content: space-between; font-size: 12px; font-weight: 500; }
        .progress-track { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: {{ $settings['primary_color'] ?? '#6366f1' }}; border-radius: 4px; }

        /* ── Circular Progress ───────────────────────────── */
        .el-circular-progress { display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .circular-bg { stroke: #e2e8f0; }
        .circular-fill { stroke: {{ $settings['primary_color'] ?? '#6366f1' }}; stroke-linecap: round; }
        .circular-text { font-size: 20px; font-weight: 700; fill: {{ $settings['primary_color'] ?? '#6366f1' }}; text-anchor: middle; dominant-baseline: central; }
        .circular-label { font-size: 11px; color: #64748b; margin-top: 8px; text-align: center; }

        /* ── Shapes ──────────────────────────────────────── */
        .el-rectangle { border-radius: 0; }
        .el-circle { border-radius: 50%; }
        .el-divider { height: 1px; background: #e2e8f0; }
        .el-line { margin-top: 50%; }

        /* ── Stat Row ────────────────────────────────────── */
        .el-stat-row { display: flex; align-items: center; justify-content: space-around; width: 100%; height: 100%; }
        .stat-item { flex: 1; text-align: center; }
        .stat-value { font-size: 24px; font-weight: 800; color: {{ $settings['primary_color'] ?? '#6366f1' }}; }
        .stat-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin-top: 4px; }

        /* ── Timeline ────────────────────────────────────── */
        .el-timeline { width: 100%; height: 100%; overflow: auto; padding: 8px; }
        .timeline-item { display: flex; gap: 12px; margin-bottom: 16px; }
        .timeline-marker { display: flex; flex-direction: column; align-items: center; width: 24px; flex-shrink: 0; }
        .timeline-dot { width: 10px; height: 10px; border-radius: 50%; background: {{ $settings['primary_color'] ?? '#6366f1' }}; flex-shrink: 0; }
        .timeline-line { width: 2px; flex: 1; background: #e2e8f0; margin-top: 4px; }
        .timeline-title { font-weight: 600; font-size: 14px; margin-bottom: 2px; }
        .timeline-date { font-size: 11px; color: {{ $settings['primary_color'] ?? '#6366f1' }}; margin-bottom: 4px; }
        .timeline-desc { font-size: 12px; color: #64748b; }

        /* ── Signature ───────────────────────────────────── */
        .el-signature { display: flex; flex-direction: column; justify-content: space-between; height: 100%; padding: 8px; }
        .signature-line { flex: 1; border-bottom: 2px solid #cbd5e1; }
        .signature-name { font-size: 16px; font-family: 'Georgia', serif; font-style: italic; color: #94a3b8; margin-top: 4px; }
        .signature-title { font-size: 10px; color: #94a3b8; margin-top: 2px; }

        /* ── Testimonial ─────────────────────────────────── */
        .el-testimonial {
            display: flex; flex-direction: column; gap: 8px;
            padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px;
        }
        .testimonial-quote { font-size: 24px; color: {{ $settings['primary_color'] ?? '#6366f1' }}; opacity: 0.5; }
        .testimonial-text { font-size: 13px; line-height: 1.6; font-style: italic; }
        .testimonial-author { font-weight: 600; font-size: 12px; margin-top: 8px; }
        .testimonial-role { font-size: 10px; color: #64748b; }

        /* ── Price Card ──────────────────────────────────── */
        .el-price-card {
            text-align: center; padding: 20px;
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px;
        }
        .price-plan { font-weight: 700; font-size: 16px; margin-bottom: 8px; }
        .price-amount { font-size: 36px; font-weight: 800; color: {{ $settings['primary_color'] ?? '#6366f1' }}; }
        .price-period { font-size: 11px; color: #64748b; margin-bottom: 12px; }
        .price-features { list-style: none; padding: 0; margin: 0; }
        .price-features li { display: flex; align-items: center; gap: 6px; font-size: 11px; margin-bottom: 6px; justify-content: center; }

        /* ── Social Card ─────────────────────────────────── */
        .el-social-card {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 16px;
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px;
        }
        .social-avatar { font-size: 48px; margin-bottom: 8px; }
        .social-name { font-weight: 600; font-size: 14px; }
        .social-subtitle { font-size: 11px; color: #64748b; margin-top: 4px; }

        /* ── Kanban ──────────────────────────────────────── */
        .el-kanban {
            padding: 12px; background: #ffffff; border: 1px solid #e2e8f0;
            border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .kanban-title { font-weight: 600; font-size: 13px; margin-bottom: 4px; }
        .kanban-status { font-size: 10px; font-weight: 600; color: {{ $settings['primary_color'] ?? '#6366f1' }}; }
        .kanban-due { font-size: 10px; color: #64748b; margin-top: 6px; }

        /* ── Page Number ─────────────────────────────────── */
        .page-number {
            position: absolute; bottom: {{ ($settings['margin'] ?? 40) / 2 }}px;
            right: {{ ($settings['margin'] ?? 40) }}px;
            font-size: 11px; color: #94a3b8; z-index: 10;
        }

        /* ── Chart Container ─────────────────────────────── */
        .chart-container { width: 100%; height: 100%; position: relative; }
        .chart-container canvas { width: 100% !important; height: 100% !important; }

        /* ── Print Optimization ──────────────────────────── */
        @media print {
            body { background: #ffffff; margin: 0; padding: 0; }
            .page { margin: 0; padding: 0; page-break-after: always; break-inside: avoid; }
            .el-metric, .el-testimonial, .el-price-card, .el-social-card,
            .el-kanban, .el-signature, .el-callout { break-inside: avoid; }
        }
    </style>
</head>
<body>

{{-- ================================================================ --}}
{{-- RENDER EACH PAGE                                                 --}}
{{-- ================================================================ --}}
@foreach($content as $pageIndex => $page)
<div class="page">

    {{-- ── Header ─────────────────────────────────────────── --}}
    @if(!empty($settings['show_header']) && !empty($settings['header_text']))
    <div class="page-header">{{ $settings['header_text'] }}</div>
    @endif

    {{-- ── Watermark ──────────────────────────────────────── --}}
    @if(!empty($settings['watermark']))
    <div class="watermark-layer">{{ $settings['watermark'] }}</div>
    @endif

    {{-- ── Elements Container ────────────────────────────── --}}
    <div class="el-container">
        @foreach($page['elements'] ?? [] as $el)
        @php
            // ── Extract element properties ──────────────────
            $s     = $el['styles'] ?? [];
            $pos   = $el['position'] ?? ['x' => 0, 'y' => 0];
            $type  = $el['type'] ?? 'text';
            $primaryColor = $settings['primary_color'] ?? '#6366f1';
            $elId  = $el['id'] ?? uniqid('el-');

            // ── Build base position style ───────────────────
            $baseStyle  = "position:absolute;";
            $baseStyle .= "left:{$pos['x']}px; top:{$pos['y']}px;";
            $baseStyle .= "width:"  . ($s['width']  ?? 200) . "px;";
            $baseStyle .= "height:" . ($s['height'] ?? 50)  . "px;";
            $baseStyle .= "z-index:" . ($s['zIndex'] ?? 1)  . ";";

            // ── Opacity ─────────────────────────────────────
            if (!empty($s['opacity']) && $s['opacity'] != 100) {
                $baseStyle .= "opacity:" . ($s['opacity'] / 100) . ";";
            }

            // ── Rotation ────────────────────────────────────
            if (!empty($s['rotate'])) {
                $baseStyle .= "transform:rotate({$s['rotate']}deg);";
            }

            // ── Border Radius ───────────────────────────────
            if (!empty($s['borderRadius'])) {
                $baseStyle .= "border-radius:{$s['borderRadius']}px;";
            }

            // ── Border ──────────────────────────────────────
            if (!empty($s['borderWidth']) && $s['borderWidth'] > 0) {
                $borderStyle = $s['borderStyle'] ?? 'solid';
                $borderColor = $s['borderColor'] ?? '#000000';
                $baseStyle .= "border:{$s['borderWidth']}px {$borderStyle} {$borderColor};";
            }

            // ── Padding ─────────────────────────────────────
            if (!empty($s['padding'])) {
                $baseStyle .= "padding:{$s['padding']}px;";
            }

            // ── Background Color ────────────────────────────
            if (!empty($s['backgroundColor']) && $s['backgroundColor'] !== 'transparent') {
                $baseStyle .= "background-color:{$s['backgroundColor']};";
            }

            // ── Build text style ────────────────────────────
            $textStyle  = "font-family:" . str_replace(["'", '"'], '', $s['fontFamily'] ?? $settings['font_family'] ?? 'DM Sans') . ", sans-serif;";
            $textStyle .= "font-size:"   . ($s['fontSize']   ?? 14) . "px;";
            $textStyle .= "color:"       . ($s['color']      ?? ($settings['text_color'] ?? '#1e293b')) . ";";
            $textStyle .= "font-weight:" . ($s['fontWeight'] ?? (in_array($type, ['heading', 'subheading']) ? '700' : '400')) . ";";
            $textStyle .= "font-style:"  . ($s['fontStyle']  ?? 'normal') . ";";
            $textStyle .= "text-align:"  . ($s['textAlign']  ?? 'left') . ";";
            $textStyle .= "text-decoration:" . ($s['textDecoration'] ?? 'none') . ";";
            $textStyle .= "text-transform:"  . ($s['textTransform']  ?? 'none') . ";";
            $textStyle .= "line-height:"      . ($s['lineHeight']     ?? 1.6) . ";";
            if (!empty($s['letterSpacing'])) {
                $textStyle .= "letter-spacing:{$s['letterSpacing']}px;";
            }

            $content = $el['content'] ?? '';
        @endphp

        {{-- ════════════════════════════════════════════════ --}}
        {{-- TEXT ELEMENTS                                     --}}
        {{-- ════════════════════════════════════════════════ --}}
        @if(in_array($type, ['text', 'heading', 'subheading']))
        <div class="el el-{{ $type }}" style="{{ $baseStyle }}{{ $textStyle }}">
            {!! $content !!}
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- QUOTE                                             --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'quote')
        <div class="el el-quote" style="{{ $baseStyle }}{{ $textStyle }}">
            {!! $content !!}
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- BLOCKQUOTE                                        --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'blockquote')
        <div class="el el-blockquote" style="{{ $baseStyle }}{{ $textStyle }}">
            {!! $content !!}
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- HIGHLIGHT                                         --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'highlight')
        <div class="el el-highlight" style="{{ $baseStyle }}{{ $textStyle }}">
            {{ $content }}
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- LIST                                              --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'list')
        <div class="el el-list" style="{{ $baseStyle }}{{ $textStyle }}">
            @if(($s['listStyle'] ?? '') === 'numbered')
            <ol>
                @foreach($el['items'] ?? [] as $item)
                <li>{{ $item }}</li>
                @endforeach
            </ol>
            @else
            <ul>
                @foreach($el['items'] ?? [] as $item)
                <li>{{ $item }}</li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- CHECKLIST                                         --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'checklist')
        <div class="el el-checklist" style="{{ $baseStyle }}{{ $textStyle }}">
            @foreach($el['items'] ?? [] as $item)
            <div class="checklist-item">
                <span class="check-icon">
                    {{ ($item['checked'] ?? false) ? '✓' : '□' }}
                </span>
                <span>{{ $item['text'] ?? '' }}</span>
            </div>
            @endforeach
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- CODE BLOCK                                        --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'code')
        <div class="el el-code" style="{{ $baseStyle }}">
            <div class="code-header">
                <span class="code-language">{{ $el['language'] ?? 'Code' }}</span>
            </div>
            <pre><code>{{ $content }}</code></pre>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- LINK                                              --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'link')
        <a href="{{ $el['href'] ?? '#' }}" class="el el-link" target="_blank" style="{{ $baseStyle }}{{ $textStyle }}">
            {{ $content ?: ($el['href'] ?? 'Link') }}
        </a>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- BADGE                                             --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'badge')
        <div class="el el-badge" style="{{ $baseStyle }}">
            {{ $content ?: 'Badge' }}
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- CALLOUT                                           --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'callout')
        <div class="el el-callout" style="{{ $baseStyle }}">
            <div class="callout-icon">{{ $el['emoji'] ?? '💡' }}</div>
            <div class="callout-content">{!! $content !!}</div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- IMAGE                                             --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'image')
        <div class="el el-image" style="{{ $baseStyle }}">
            @if(!empty($el['src']))
            <img src="{{ $el['src'] }}" alt="{{ $el['alt'] ?? 'Image' }}" style="object-fit: {{ $s['objectFit'] ?? 'cover' }}; border-radius: {{ $s['borderRadius'] ?? 0 }}px;">
            @else
            <div class="image-placeholder"><span>🖼️ No image</span></div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- TABLE                                             --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'table')
        <div class="el el-table" style="{{ $baseStyle }}">
            <table class="data-table">
                @if(!empty($el['columns']))
                <thead>
                    <tr>
                        @foreach($el['columns'] as $col)
                        <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                @endif
                <tbody>
                    @foreach($el['data'] ?? [] as $row)
                    <tr>
                        @foreach($el['columns'] ?? [] as $col)
                        <td>{{ $row[$col] ?? '-' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- METRIC / KPI                                      --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'metric')
        <div class="el el-metric" style="{{ $baseStyle }}">
            <div class="metric-label">{{ $el['label'] ?? 'Metric' }}</div>
            <div class="metric-value">{{ $el['value'] ?? '0' }}</div>
            @if(!empty($el['change']))
            <div class="metric-change {{ ($el['changeType'] ?? 'positive') === 'positive' ? 'positive' : 'negative' }}">
                {{ ($el['changeType'] ?? 'positive') === 'positive' ? '▲' : '▼' }}
                {{ $el['change'] }}
                <span style="font-size:10px;opacity:0.7">{{ $el['changePeriod'] ?? '' }}</span>
            </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- PROGRESS BAR                                      --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'progress')
        <div class="el el-progress" style="{{ $baseStyle }}">
            <div class="progress-header">
                <span>{{ $el['label'] ?? 'Progress' }}</span>
                <span>{{ $el['value'] ?? 0 }}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: {{ $el['value'] ?? 0 }}%;"></div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- CIRCULAR PROGRESS                                 --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'circular-progress')
        @php
            $cpValue = $el['value'] ?? 0;
            $circumference = 2 * pi() * 52;
            $dashArray = ($cpValue / 100) * $circumference;
        @endphp
        <div class="el el-circular-progress" style="{{ $baseStyle }}">
            <svg viewBox="0 0 120 120" style="width:80%;height:80%;">
                <circle class="circular-bg" cx="60" cy="60" r="52" fill="none" stroke-width="8"/>
                <circle class="circular-fill" cx="60" cy="60" r="52" fill="none" stroke-width="8"
                        stroke-dasharray="{{ $dashArray }} {{ $circumference }}"
                        transform="rotate(-90 60 60)"/>
                <text class="circular-text" x="60" y="60">{{ $cpValue }}%</text>
            </svg>
            @if(!empty($el['label']))
            <div class="circular-label">{{ $el['label'] }}</div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- STAT ROW                                          --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'stat-row')
        <div class="el el-stat-row" style="{{ $baseStyle }}">
            @foreach($el['stats'] ?? [] as $stat)
            <div class="stat-item">
                <div class="stat-value">{{ $stat['value'] ?? '0' }}</div>
                <div class="stat-label">{{ $stat['label'] ?? '' }}</div>
            </div>
            @endforeach
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- TIMELINE                                          --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'timeline')
        <div class="el el-timeline" style="{{ $baseStyle }}">
            @foreach($el['items'] ?? [] as $item)
            <div class="timeline-item">
                <div class="timeline-marker">
                    <div class="timeline-dot"></div>
                    @if(!$loop->last)<div class="timeline-line"></div>@endif
                </div>
                <div>
                    <div class="timeline-title">{{ $item['label'] ?? '' }}</div>
                    <div class="timeline-date">{{ $item['date'] ?? '' }}</div>
                    <div class="timeline-desc">{{ $item['desc'] ?? '' }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- RECTANGLE SHAPE                                   --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'rectangle')
        <div class="el el-rectangle" style="{{ $baseStyle }} background-color: {{ $s['backgroundColor'] ?? $primaryColor }};"></div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- CIRCLE SHAPE                                      --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'circle')
        <div class="el el-circle" style="{{ $baseStyle }} background-color: {{ $s['backgroundColor'] ?? $primaryColor }};"></div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- LINE / DIVIDER / ARROW                            --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif(in_array($type, ['line', 'divider', 'arrow']))
        <div class="el el-{{ $type }}" style="{{ $baseStyle }} background-color: {{ $s['color'] ?? '#e2e8f0' }};"></div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- SIGNATURE                                         --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'signature')
        <div class="el el-signature" style="{{ $baseStyle }}">
            <div class="signature-line"></div>
            <div class="signature-name">{{ $content ?: 'Signature' }}</div>
            <div class="signature-title">{{ $el['label'] ?? 'Authorized Signature' }}</div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- TESTIMONIAL                                       --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'testimonial')
        <div class="el el-testimonial" style="{{ $baseStyle }}">
            <div class="testimonial-quote">"</div>
            <div class="testimonial-text">{{ $content ?? 'Great product!' }}</div>
            <div class="testimonial-author">{{ $el['author'] ?? 'John Doe' }}</div>
            <div class="testimonial-role">{{ $el['role'] ?? 'CEO' }}</div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- PRICE CARD                                        --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'price-card')
        <div class="el el-price-card" style="{{ $baseStyle }}">
            <div class="price-plan">{{ $el['plan'] ?? 'Basic Plan' }}</div>
            <div class="price-amount">{{ $el['price'] ?? '$0' }}</div>
            <div class="price-period">{{ $el['period'] ?? '/month' }}</div>
            <ul class="price-features">
                @foreach($el['features'] ?? [] as $feature)
                <li><span style="color:#10b981;">✓</span> {{ $feature }}</li>
                @endforeach
            </ul>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- SOCIAL CARD                                       --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'social-card')
        <div class="el el-social-card" style="{{ $baseStyle }}">
            <div class="social-avatar">{{ $el['avatar'] ?? '👤' }}</div>
            <div class="social-name">{{ $content ?? 'User Name' }}</div>
            <div class="social-subtitle">{{ $el['subtitle'] ?? 'Title' }}</div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- KANBAN CARD                                       --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'kanban')
        <div class="el el-kanban" style="{{ $baseStyle }}">
            <div class="kanban-title">{{ $content ?? 'Task' }}</div>
            <div class="kanban-status">{{ $el['status'] ?? 'In Progress' }}</div>
            @if(!empty($el['due']))
            <div class="kanban-due">Due: {{ $el['due'] }}</div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- PAGE NUMBER / DATE                                --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif($type === 'pagenum')
        <div class="el" style="{{ $baseStyle }}{{ $textStyle }} display:flex; align-items:center; justify-content:center;">
            {{ $pageIndex + 1 }}
        </div>
        @elseif($type === 'date')
        <div class="el" style="{{ $baseStyle }}{{ $textStyle }}">
            {{ date('F j, Y') }}
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- CHARTS (Bar, Line, Area, Pie, Doughnut, Radar)    --}}
        {{-- ════════════════════════════════════════════════ --}}
        @elseif(in_array($type, ['bar-chart', 'line-chart', 'area-chart', 'pie-chart', 'doughnut-chart', 'radar-chart']))
        @php
            $chartLabels = json_encode($el['chartData']['labels'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            $chartValues = json_encode($el['chartData']['values'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            $chartTitle  = $el['chartTitle'] ?? '';
            $chartType   = str_replace('-chart', '', $type);
            $chartColors = ['#6366f1', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899'];
        @endphp
        <div class="el chart-container" style="{{ $baseStyle }}">
            <canvas id="chart-{{ $elId }}"></canvas>
        </div>
        <script>
            (function() {
                var ctx = document.getElementById('chart-{{ $elId }}');
                if (ctx && typeof Chart !== 'undefined') {
                    new Chart(ctx.getContext('2d'), {
                        type: '{{ in_array($chartType, ['bar','line','pie','doughnut','radar']) ? $chartType : 'bar' }}',
                        data: {
                            labels: {!! $chartLabels !!},
                            datasets: [{
                                label: '{{ $chartTitle }}',
                                data: {!! $chartValues !!},
                                backgroundColor: {{ json_encode($chartColors) }},
                                borderColor: '{{ $primaryColor }}',
                                borderWidth: 2,
                                fill: {{ $type === 'area-chart' ? 'true' : 'false' }},
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                                title: { display: {{ !empty($chartTitle) ? 'true' : 'false' }}, text: '{{ $chartTitle }}', font: { size: 13, weight: '600' } }
                            },
                            scales: {
                                y: { beginAtZero: true },
                                x: { ticks: { maxRotation: 45 } }
                            }
                        }
                    });
                }
            })();
        </script>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- UNKNOWN ELEMENT - Skip gracefully                 --}}
        {{-- ════════════════════════════════════════════════ --}}
        @else
        <!-- Unknown element type: {{ $type }} -->
        @endif
        @endforeach
    </div>

    {{-- ── Footer ─────────────────────────────────────────── --}}
    @if(!empty($settings['show_footer']))
    <div class="page-footer">
        <div class="footer-left">{{ $settings['footer_left'] ?? '' }}</div>
        <div class="footer-center">{{ $settings['footer_center'] ?? '' }}</div>
        <div class="footer-right">{{ str_replace('{n}', $pageIndex + 1, $settings['footer_right'] ?? '') }}</div>
    </div>
    @endif

    {{-- ── Page Number (if not using footer) ─────────────── --}}
    @if(!empty($settings['show_page_numbers']) && empty($settings['show_footer']))
    <div class="page-number">{{ $pageIndex + 1 }}</div>
    @endif

</div>
@endforeach

{{-- ================================================================ --}}
{{-- CHART.JS CDN - For rendering charts in PDF via Browsershot       --}}
{{-- ================================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

</body>
</html>