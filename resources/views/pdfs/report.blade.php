{{-- resources/views/pdfs/report.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->title }}</title>
    <style>
        /* ============================================
           RESET & BASE STYLES
        ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ str_replace(["'", '"'], '', $settings['font_family'] ?? 'DM Sans') }}, 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: {{ $settings['background_color'] ?? '#ffffff' }};
            color: {{ $settings['text_color'] ?? '#1e293b' }};
            line-height: 1.5;
        }

        /* Page Break Handling */
        @page {
            size: {{ $settings['page_size'] ?? 'A4' }} {{ $settings['orientation'] ?? 'portrait' }};
            margin: {{ ($settings['margin'] ?? 40) }}px;
        }

        /* ============================================
           PAGE CONTAINER
        ============================================ */
        .page {
            position: relative;
            width: {{ ($settings['orientation'] === 'landscape' ? 1123 : 794) }}px;
            height: {{ ($settings['orientation'] === 'landscape' ? 794 : 1123) }}px;
            page-break-after: always;
            break-after: page;
            background: {{ $settings['background_color'] ?? '#ffffff' }};
            overflow: hidden;
            position: relative;
        }

        .page:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        /* ============================================
           HEADER & FOOTER
        ============================================ */
        .page-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: {{ ($settings['header_height'] ?? 60) }}px;
            background: {{ $settings['header_bg'] ?? '#1e293b' }};
            color: {{ $settings['header_text_color'] ?? '#ffffff' }};
            display: flex;
            align-items: center;
            padding: 0 {{ ($settings['margin'] ?? 40) }}px;
            z-index: 10;
            font-size: {{ ($settings['header_font_size'] ?? 12) }}px;
            text-align: {{ $settings['header_align'] ?? 'center' }};
        }

        .page-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: {{ ($settings['footer_height'] ?? 40) }}px;
            background: {{ $settings['footer_bg'] ?? 'transparent' }};
            color: {{ $settings['footer_text_color'] ?? '#94a3b8' }};
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 {{ ($settings['margin'] ?? 40) }}px;
            z-index: 10;
            font-size: {{ ($settings['footer_font_size'] ?? 10) }}px;
            border-top: 1px solid {{ $settings['primary_color'] ?? '#6366f1' }}20;
        }

        .footer-left,
        .footer-center,
        .footer-right {
            flex: 1;
        }

        .footer-center {
            text-align: center;
        }

        .footer-right {
            text-align: right;
        }

        /* ============================================
           WATERMARK
        ============================================ */
        .watermark-layer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate({{ $settings['watermark_rotate'] ?? -30 }}deg);
            font-size: 72px;
            font-weight: 800;
            color: {{ $settings['watermark_color'] ?? '#94a3b8' }};
            opacity: {{ ($settings['watermark_opacity'] ?? 10) / 100 }};
            white-space: nowrap;
            pointer-events: none;
            z-index: 5;
            text-transform: uppercase;
        }

        /* ============================================
           ELEMENTS CONTAINER
        ============================================ */
        .elements-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Base Element Style */
        .el {
            position: absolute;
            overflow: hidden;
            word-wrap: break-word;
        }

        /* ============================================
           TEXT ELEMENTS
        ============================================ */
        .el-text,
        .el-heading,
        .el-subheading {
            word-break: break-word;
            overflow: auto;
        }

        .el-quote {
            border-left: 4px solid {{ $settings['primary_color'] ?? '#6366f1' }};
            padding-left: 16px;
            font-style: italic;
        }

        .el-blockquote {
            background: {{ $settings['primary_color'] ?? '#6366f1' }}10;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .el-highlight {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        /* ============================================
           LIST ELEMENTS
        ============================================ */
        .el-list ul,
        .el-list ol {
            margin: 0;
            padding-left: 20px;
        }

        .el-list li {
            margin-bottom: 4px;
        }

        .el-checklist .checklist-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .el-checklist .check-icon {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        /* ============================================
           CODE BLOCK
        ============================================ */
        .el-code {
            background: #1e293b;
            border-radius: 8px;
            overflow: hidden;
        }

        .code-header {
            background: #0f172a;
            padding: 8px 12px;
            border-bottom: 1px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .code-language {
            font-size: 10px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .el-code pre {
            margin: 0;
            padding: 12px;
            overflow: auto;
            background: #1e293b;
        }

        .el-code code {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #34d399;
            white-space: pre-wrap;
        }

        /* ============================================
           LINK
        ============================================ */
        .el-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .el-link:hover {
            text-decoration: underline;
        }

        /* ============================================
           BADGE
        ============================================ */
        .el-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: {{ $settings['primary_color'] ?? '#6366f1' }}20;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
            white-space: nowrap;
        }

        /* ============================================
           CALLOUT
        ============================================ */
        .el-callout {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            border-radius: 12px;
            background: {{ $settings['primary_color'] ?? '#6366f1' }}10;
            border-left: 4px solid {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .callout-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .callout-content {
            flex: 1;
            word-break: break-word;
        }

        /* ============================================
           IMAGE
        ============================================ */
        .el-image {
            width: 100%;
            height: 100%;
        }

        .el-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #f1f5f9;
            color: #94a3b8;
            font-size: 12px;
        }

        /* ============================================
           TABLE
        ============================================ */
        .el-table {
            width: 100%;
            height: 100%;
            overflow: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .data-table th {
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: {{ $settings['primary_color'] ?? '#6366f1' }};
            color: white;
        }

        .data-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        /* ============================================
           METRIC / KPI CARD
        ============================================ */
        .el-metric {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .metric-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .metric-change {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            margin-top: 6px;
        }

        .metric-change.positive {
            color: #10b981;
        }

        .metric-change.negative {
            color: #ef4444;
        }

        /* ============================================
           PROGRESS BAR
        ============================================ */
        .el-progress {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 500;
        }

        .progress-track {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: {{ $settings['primary_color'] ?? '#6366f1' }};
            border-radius: 4px;
            width: 0%;
        }

        /* ============================================
           CIRCULAR PROGRESS
        ============================================ */
        .el-circular-progress {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .circular-svg {
            width: 80%;
            height: 80%;
        }

        .circular-bg {
            stroke: #e2e8f0;
        }

        .circular-fill {
            stroke: {{ $settings['primary_color'] ?? '#6366f1' }};
            transition: stroke-dasharray 0.5s ease;
        }

        .circular-text {
            font-size: 20px;
            font-weight: 700;
            fill: {{ $settings['primary_color'] ?? '#6366f1' }};
            dominant-baseline: middle;
            text-anchor: middle;
        }

        .circular-label {
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
            text-align: center;
        }

        /* ============================================
           STAT ROW
        ============================================ */
        .el-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-around;
            width: 100%;
            height: 100%;
        }

        .stat-item {
            flex: 1;
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-top: 4px;
        }

        /* ============================================
           TIMELINE
        ============================================ */
        .el-timeline {
            width: 100%;
            height: 100%;
            overflow: auto;
            padding: 8px;
        }

        .timeline-item {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .timeline-marker {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 24px;
        }

        .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .timeline-line {
            width: 2px;
            flex: 1;
            background: #e2e8f0;
            margin-top: 4px;
        }

        .timeline-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .timeline-date {
            font-size: 11px;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
            margin-bottom: 4px;
        }

        .timeline-desc {
            font-size: 12px;
            color: #64748b;
        }

        /* ============================================
           SHAPES
        ============================================ */
        .el-rectangle {
            background: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .el-circle {
            border-radius: 50%;
            background: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .el-triangle {
            width: 0;
            height: 0;
            border-left: 50px solid transparent;
            border-right: 50px solid transparent;
            border-bottom: 100px solid #f59e0b;
        }

        .el-star {
            position: relative;
            display: inline-block;
            width: 0;
            height: 0;
            border-right: 0.3em solid transparent;
            border-bottom: 0.7em solid #fc0;
            border-left: 0.3em solid transparent;
            font-size: inherit;
        }
        .el-star:before {
            content: '';
            position: absolute;
            top: 0.03em;
            left: -1.03em;
            width: 0;
            height: 0;
            border-right: 0.3em solid transparent;
            border-bottom: 0.7em solid #fc0;
            border-left: 0.3em solid transparent;
            transform: rotate(35deg);
        }

        .el-line {
            height: 2px;
            background: #cbd5e1;
            margin-top: 50%;
        }

        .el-arrow {
            height: 2px;
            background: {{ $settings['primary_color'] ?? '#6366f1' }};
            position: relative;
            margin-top: 50%;
        }
        .el-arrow::after {
            content: '';
            position: absolute;
            right: -6px;
            top: -4px;
            width: 0;
            height: 0;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
            border-left: 6px solid {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .el-divider {
            height: 1px;
            background: #e2e8f0;
            margin-top: 50%;
        }

        /* ============================================
           ICON
        ============================================ */
        .el-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        /* ============================================
           RATING
        ============================================ */
        .el-rating {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .star-filled {
            color: #f59e0b;
        }

        .star-empty {
            color: #cbd5e1;
        }

        /* ============================================
           SIGNATURE
        ============================================ */
        .el-signature {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            padding: 8px;
        }

        .signature-line {
            flex: 1;
            border-bottom: 2px solid #cbd5e1;
        }

        .signature-name {
            font-size: 16px;
            font-family: 'Georgia', serif;
            font-style: italic;
            color: #94a3b8;
            margin-top: 4px;
        }

        .signature-title {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* ============================================
           SOCIAL CARD
        ============================================ */
        .el-social-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 16px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .social-avatar {
            font-size: 48px;
            margin-bottom: 8px;
        }

        .social-name {
            font-weight: 600;
            font-size: 14px;
        }

        .social-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* ============================================
           TESTIMONIAL
        ============================================ */
        .el-testimonial {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .testimonial-quote {
            font-size: 24px;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
            opacity: 0.5;
        }

        .testimonial-text {
            font-size: 13px;
            line-height: 1.6;
            font-style: italic;
        }

        .testimonial-author {
            font-weight: 600;
            font-size: 12px;
            margin-top: 8px;
        }

        .testimonial-role {
            font-size: 10px;
            color: #64748b;
        }

        /* ============================================
           PRICE CARD
        ============================================ */
        .el-price-card {
            text-align: center;
            padding: 20px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .price-plan {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .price-amount {
            font-size: 32px;
            font-weight: 800;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .price-period {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .price-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .price-features li {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            margin-bottom: 6px;
            justify-content: center;
        }

        .price-features li i {
            color: #10b981;
            font-size: 10px;
        }

        /* ============================================
           KANBAN CARD
        ============================================ */
        .el-kanban {
            padding: 12px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .kanban-title {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .kanban-status {
            font-size: 10px;
            font-weight: 600;
            color: {{ $settings['primary_color'] ?? '#6366f1' }};
        }

        .kanban-due {
            font-size: 10px;
            color: #64748b;
            margin-top: 6px;
        }

        /* ============================================
           WATERMARK ELEMENT
        ============================================ */
        .el-watermark-element {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            white-space: nowrap;
        }

        /* ============================================
           CHARTS
        ============================================ */
        .chart-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* ============================================
           PAGE NUMBER
        ============================================ */
        .page-number {
            position: absolute;
            bottom: 10px;
            right: 20px;
            font-size: 11px;
            color: #94a3b8;
            z-index: 10;
        }

        /* ============================================
           UTILITY CLASSES
        ============================================ */
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }

        .font-normal { font-weight: 400; }
        .font-bold { font-weight: 700; }
        .font-italic { font-style: italic; }

        /* ============================================
           PRINT OPTIMIZATION
        ============================================ */
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            
            .page {
                margin: 0;
                padding: 0;
                page-break-after: always;
                break-inside: avoid;
            }
            
            .watermark-layer {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .el-metric,
            .el-testimonial,
            .el-price-card,
            .el-social-card {
                break-inside: avoid;
            }
            
            .el-table {
                break-inside: auto;
            }
            
            .el-chart {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

@foreach($content as $pageIndex => $page)
<div class="page">
    
    <!-- Header -->
    @if(!empty($settings['show_header']) && !empty($settings['header_text']))
    <div class="page-header">
        {{ $settings['header_text'] }}
    </div>
    @endif
    
    <!-- Watermark -->
    @if(!empty($settings['watermark']))
    <div class="watermark-layer">
        {{ $settings['watermark'] }}
    </div>
    @endif
    
    <!-- Elements Container -->
    <div class="elements-container">
        @foreach($page['elements'] ?? [] as $el)
        @php
            $s = $el['styles'] ?? [];
            $pos = $el['position'] ?? ['x' => 0, 'y' => 0];
            $type = $el['type'] ?? 'text';
            $primaryColor = $settings['primary_color'] ?? '#6366f1';
            
            // Base style for all elements
            $baseStyle = "position:absolute; left:{$pos['x']}px; top:{$pos['y']}px;";
            $baseStyle .= "width:" . ($s['width'] ?? 200) . "px; height:" . ($s['height'] ?? 50) . "px;";
            $baseStyle .= "z-index:" . ($s['zIndex'] ?? 1) . ";";
            
            if (!empty($s['opacity']) && $s['opacity'] != 100) {
                $baseStyle .= "opacity:" . ($s['opacity'] / 100) . ";";
            }
            if (!empty($s['rotate'])) {
                $baseStyle .= "transform:rotate({$s['rotate']}deg);";
            }
            if (!empty($s['borderRadius'])) {
                $baseStyle .= "border-radius:{$s['borderRadius']}px;";
            }
            if (!empty($s['borderWidth']) && $s['borderWidth'] > 0) {
                $borderStyle = $s['borderStyle'] ?? 'solid';
                $borderColor = $s['borderColor'] ?? '#000';
                $baseStyle .= "border:{$s['borderWidth']}px {$borderStyle} {$borderColor};";
            }
            if (!empty($s['padding'])) {
                $baseStyle .= "padding:{$s['padding']}px;";
            }
            
            // Text style helper
            $textStyle = "font-family:" . str_replace(["'", '"'], '', $s['fontFamily'] ?? $settings['font_family'] ?? 'DM Sans') . ", 'DM Sans', sans-serif;";
            $textStyle .= "font-size:" . ($s['fontSize'] ?? 14) . "px;";
            $textStyle .= "color:" . ($s['color'] ?? $settings['text_color'] ?? '#1e293b') . ";";
            $textStyle .= "font-weight:" . ($s['fontWeight'] ?? (in_array($type, ['heading']) ? '700' : '400')) . ";";
            $textStyle .= "font-style:" . ($s['fontStyle'] ?? 'normal') . ";";
            $textStyle .= "text-align:" . ($s['textAlign'] ?? 'left') . ";";
            $textStyle .= "text-decoration:" . ($s['textDecoration'] ?? 'none') . ";";
            $textStyle .= "text-transform:" . ($s['textTransform'] ?? 'none') . ";";
            $textStyle .= "line-height:" . ($s['lineHeight'] ?? 1.5) . ";";
            if (!empty($s['letterSpacing'])) {
                $textStyle .= "letter-spacing:{$s['letterSpacing']}px;";
            }
            if (!empty($s['backgroundColor']) && $s['backgroundColor'] !== 'transparent') {
                $textStyle .= "background-color:{$s['backgroundColor']};";
            }
        @endphp
        
        <!-- TEXT ELEMENTS -->
        @if(in_array($type, ['text', 'heading', 'subheading']))
        <div class="el el-{{ $type }}" style="{{ $baseStyle }}{{ $textStyle }}">
            {!! nl2br(e($el['content'] ?? '')) !!}
        </div>
        
        <!-- QUOTE -->
        @elseif($type === 'quote')
        <div class="el el-quote" style="{{ $baseStyle }}{{ $textStyle }}">
            {!! nl2br(e($el['content'] ?? '')) !!}
        </div>
        
        <!-- BLOCKQUOTE -->
        @elseif($type === 'blockquote')
        <div class="el el-blockquote" style="{{ $baseStyle }}{{ $textStyle }}">
            {!! nl2br(e($el['content'] ?? '')) !!}
        </div>
        
        <!-- HIGHLIGHT -->
        @elseif($type === 'highlight')
        <div class="el el-highlight" style="{{ $baseStyle }}{{ $textStyle }}">
            {{ $el['content'] ?? '' }}
        </div>
        
        <!-- LIST -->
        @elseif($type === 'list')
        <div class="el el-list" style="{{ $baseStyle }}">
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
        
        <!-- CHECKLIST -->
        @elseif($type === 'checklist')
        <div class="el el-checklist" style="{{ $baseStyle }}{{ $textStyle }}">
            @foreach($el['items'] ?? [] as $item)
            <div class="checklist-item">
                <span class="check-icon">
                    @if($item['checked'] ?? false)
                    ✓
                    @else
                    □
                    @endif
                </span>
                <span>{{ $item['text'] ?? '' }}</span>
            </div>
            @endforeach
        </div>
        
        <!-- CODE BLOCK -->
        @elseif($type === 'code')
        <div class="el el-code" style="{{ $baseStyle }}">
            <div class="code-header">
                <span class="code-language">{{ $el['language'] ?? 'Code' }}</span>
            </div>
            <pre><code>{{ $el['content'] ?? '' }}</code></pre>
        </div>
        
        <!-- LINK -->
        @elseif($type === 'link')
        <a href="{{ $el['href'] ?? '#' }}" class="el el-link" target="_blank" style="{{ $baseStyle }}{{ $textStyle }}">
            {{ $el['content'] ?? ($el['href'] ?? 'Link') }}
        </a>
        
        <!-- BADGE -->
        @elseif($type === 'badge')
        <div class="el el-badge" style="{{ $baseStyle }}">
            {{ $el['content'] ?? 'Badge' }}
        </div>
        
        <!-- CALLOUT -->
        @elseif($type === 'callout')
        <div class="el el-callout" style="{{ $baseStyle }}">
            <div class="callout-icon">{{ $el['emoji'] ?? '💡' }}</div>
            <div class="callout-content">{!! nl2br(e($el['content'] ?? '')) !!}</div>
        </div>
        
        <!-- IMAGE -->
        @elseif($type === 'image')
        <div class="el el-image" style="{{ $baseStyle }}">
            @if(!empty($el['src']))
            <img src="{{ $el['src'] }}" alt="{{ $el['alt'] ?? 'Report image' }}" style="object-fit: {{ $s['objectFit'] ?? 'cover' }}; border-radius: {{ $s['borderRadius'] ?? 0 }}px;">
            @else
            <div class="image-placeholder">
                <span>No image selected</span>
            </div>
            @endif
        </div>
        
        <!-- TABLE -->
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
        
        <!-- METRIC / KPI -->
        @elseif($type === 'metric')
        <div class="el el-metric" style="{{ $baseStyle }}">
            <div class="metric-label">{{ $el['label'] ?? 'Metric' }}</div>
            <div class="metric-value">{{ $el['value'] ?? '0' }}</div>
            @if(!empty($el['change']))
            <div class="metric-change {{ ($el['changeType'] ?? 'positive') === 'positive' ? 'positive' : 'negative' }}">
                {{ ($el['changeType'] ?? 'positive') === 'positive' ? '▲' : '▼' }} {{ $el['change'] }}
                <span class="metric-period">{{ $el['changePeriod'] ?? '' }}</span>
            </div>
            @endif
        </div>
        
        <!-- PROGRESS BAR -->
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
        
        <!-- CIRCULAR PROGRESS -->
        @elseif($type === 'circular-progress')
        @php
            $value = $el['value'] ?? 0;
            $circumference = 2 * pi() * 52;
            $dashArray = ($value / 100) * $circumference;
        @endphp
        <div class="el el-circular-progress" style="{{ $baseStyle }}">
            <svg class="circular-svg" viewBox="0 0 120 120">
                <circle class="circular-bg" cx="60" cy="60" r="52" fill="none" stroke-width="8"/>
                <circle class="circular-fill" cx="60" cy="60" r="52" fill="none" stroke-width="8" stroke-linecap="round"
                        stroke-dasharray="{{ $dashArray }} {{ $circumference }}" transform="rotate(-90 60 60)"/>
                <text class="circular-text" x="60" y="60">{{ $value }}%</text>
            </svg>
            @if(!empty($el['label']))
            <div class="circular-label">{{ $el['label'] }}</div>
            @endif
        </div>
        
        <!-- STAT ROW -->
        @elseif($type === 'stat-row')
        <div class="el el-stat-row" style="{{ $baseStyle }}">
            @foreach($el['stats'] ?? [] as $stat)
            <div class="stat-item">
                <div class="stat-value">{{ $stat['value'] ?? '0' }}</div>
                <div class="stat-label">{{ $stat['label'] ?? '' }}</div>
            </div>
            @endforeach
        </div>
        
        <!-- TIMELINE -->
        @elseif($type === 'timeline')
        <div class="el el-timeline" style="{{ $baseStyle }}">
            @foreach($el['items'] ?? [] as $item)
            <div class="timeline-item">
                <div class="timeline-marker">
                    <div class="timeline-dot"></div>
                    @if(!$loop->last)
                    <div class="timeline-line"></div>
                    @endif
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">{{ $item['label'] ?? '' }}</div>
                    <div class="timeline-date">{{ $item['date'] ?? '' }}</div>
                    <div class="timeline-desc">{{ $item['desc'] ?? '' }}</div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- RECTANGLE -->
        @elseif($type === 'rectangle')
        <div class="el el-rectangle" style="{{ $baseStyle }} background-color: {{ $s['backgroundColor'] ?? $primaryColor }};"></div>
        
        <!-- CIRCLE -->
        @elseif($type === 'circle')
        <div class="el el-circle" style="{{ $baseStyle }} background-color: {{ $s['backgroundColor'] ?? $primaryColor }};"></div>
        
        <!-- TRIANGLE -->
        @elseif($type === 'triangle')
        @php
            $bgColor = $s['backgroundColor'] ?? '#f59e0b';
        @endphp
        <div class="el el-triangle" style="{{ $baseStyle }}">
            <div style="width: 0; height: 0; border-left: 50px solid transparent; border-right: 50px solid transparent; border-bottom: 100px solid {{ $bgColor }};"></div>
        </div>
        
        <!-- STAR -->
        @elseif($type === 'star')
        @php
            $bgColor = $s['backgroundColor'] ?? $primaryColor;
        @endphp
        <div class="el el-star" style="{{ $baseStyle }} border-bottom-color: {{ $bgColor }};"></div>
        
        <!-- LINE -->
        @elseif($type === 'line')
        <div class="el el-line" style="{{ $baseStyle }} background-color: {{ $s['color'] ?? '#cbd5e1' }};"></div>
        
        <!-- ARROW -->
        @elseif($type === 'arrow')
        <div class="el el-arrow" style="{{ $baseStyle }} background-color: {{ $s['color'] ?? $primaryColor }};"></div>
        
        <!-- DIVIDER -->
        @elseif($type === 'divider')
        <div class="el el-divider" style="{{ $baseStyle }} background-color: {{ $s['color'] ?? '#e2e8f0' }};"></div>
        
        <!-- ICON -->
        @elseif($type === 'icon')
        <div class="el el-icon" style="{{ $baseStyle }}">
            <span style="font-size: {{ $s['fontSize'] ?? 40 }}px; color: {{ $s['color'] ?? $primaryColor }};">
                {{ $el['content'] ?? '★' }}
            </span>
        </div>
        
        <!-- RATING -->
        @elseif($type === 'rating')
        @php
            $rating = $el['value'] ?? 0;
            $starColor = $s['color'] ?? '#f59e0b';
        @endphp
        <div class="el el-rating" style="{{ $baseStyle }}">
            @for($i = 1; $i <= 5; $i++)
            <span class="{{ $i <= $rating ? 'star-filled' : 'star-empty' }}" style="font-size: {{ $s['fontSize'] ?? 20 }}px; color: {{ $i <= $rating ? $starColor : '#cbd5e1' }};">★</span>
            @endfor
        </div>
        
        <!-- PAGE NUMBER -->
        @elseif($type === 'pagenum')
        <div class="el" style="{{ $baseStyle }}{{ $textStyle }} display: flex; align-items: center; justify-content: center;">
            {{ $pageIndex + 1 }}
        </div>
        
        <!-- DATE -->
        @elseif($type === 'date')
        <div class="el" style="{{ $baseStyle }}{{ $textStyle }}">
            {{ date('F j, Y') }}
        </div>
        
        <!-- SIGNATURE -->
        @elseif($type === 'signature')
        <div class="el el-signature" style="{{ $baseStyle }}">
            <div class="signature-line"></div>
            <div class="signature-name">{{ $el['content'] ?? 'Signature' }}</div>
            <div class="signature-title">{{ $el['label'] ?? 'Authorized Signature' }}</div>
        </div>
        
        <!-- SOCIAL CARD -->
        @elseif($type === 'social-card')
        <div class="el el-social-card" style="{{ $baseStyle }}">
            <div class="social-avatar">{{ $el['avatar'] ?? '👤' }}</div>
            <div class="social-name">{{ $el['content'] ?? 'User Name' }}</div>
            <div class="social-subtitle">{{ $el['subtitle'] ?? 'Title / Position' }}</div>
        </div>
        
        <!-- TESTIMONIAL -->
        @elseif($type === 'testimonial')
        <div class="el el-testimonial" style="{{ $baseStyle }}">
            <div class="testimonial-quote">"</div>
            <div class="testimonial-text">{{ $el['content'] ?? 'Great product! Highly recommended.' }}</div>
            <div class="testimonial-author">{{ $el['author'] ?? 'John Doe' }}</div>
            <div class="testimonial-role">{{ $el['role'] ?? 'CEO' }}</div>
        </div>
        
        <!-- PRICE CARD -->
        @elseif($type === 'price-card')
        <div class="el el-price-card" style="{{ $baseStyle }}">
            <div class="price-plan">{{ $el['plan'] ?? 'Basic Plan' }}</div>
            <div class="price-amount">{{ $el['price'] ?? '$0' }}</div>
            <div class="price-period">{{ $el['period'] ?? '/month' }}</div>
            <ul class="price-features">
                @foreach($el['features'] ?? [] as $feature)
                <li><i>✓</i> {{ $feature }}</li>
                @endforeach
            </ul>
        </div>
        
        <!-- KANBAN CARD -->
        @elseif($type === 'kanban')
        <div class="el el-kanban" style="{{ $baseStyle }}">
            <div class="kanban-title">{{ $el['content'] ?? 'Task Title' }}</div>
            <div class="kanban-status">{{ $el['status'] ?? 'In Progress' }}</div>
            @if(!empty($el['due']))
            <div class="kanban-due">Due: {{ $el['due'] }}</div>
            @endif
        </div>
        
        <!-- WATERMARK ELEMENT -->
        @elseif($type === 'watermark')
        @php
            $wmOpacity = isset($s['opacity']) ? $s['opacity'] / 100 : 0.2;
            $wmFontSize = $s['fontSize'] ?? 48;
            $wmColor = $s['color'] ?? '#94a3b8';
            $wmRotate = $s['rotate'] ?? -30;
        @endphp
        <div class="el el-watermark-element" style="{{ $baseStyle }} opacity: {{ $wmOpacity }};">
            <span style="font-size: {{ $wmFontSize }}px; font-weight: 800; color: {{ $wmColor }}; transform: rotate({{ $wmRotate }}deg); white-space: nowrap;">
                {{ $el['content'] ?? 'CONFIDENTIAL' }}
            </span>
        </div>
        
        <!-- CHARTS -->
        @elseif(in_array($type, ['bar-chart', 'line-chart', 'area-chart', 'pie-chart', 'doughnut-chart', 'radar-chart']))
        @php
            $chartId = 'chart-' . ($el['id'] ?? uniqid());
            $chartLabels = json_encode($el['chartData']['labels'] ?? []);
            $chartValues = json_encode($el['chartData']['values'] ?? []);
            $chartTitle = $el['chartTitle'] ?? '';
            $chartType = $type;
        @endphp
        <div class="el chart-container" style="{{ $baseStyle }}">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('{{ $chartId }}')?.getContext('2d');
                if (ctx) {
                    new Chart(ctx, {
                        type: @json($chartType === 'area-chart' ? 'line' : ($chartType === 'bar-chart' ? 'bar' : ($chartType === 'pie-chart' ? 'pie' : ($chartType === 'doughnut-chart' ? 'doughnut' : ($chartType === 'radar-chart' ? 'radar' : 'line'))))),
                        data: {
                            labels: @json($chartLabels),
                            datasets: [{
                                label: @json($chartTitle),
                                data: @json($chartValues),
                                backgroundColor: @json($chartType === 'bar-chart' || $chartType === 'line-chart' ? $primaryColor : ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']),
                                borderColor: @json($primaryColor),
                                borderWidth: 2,
                                fill: @json($chartType === 'area-chart'),
                                tension: @json($chartType === 'line-chart' || $chartType === 'area-chart' ? 0.4 : 0)
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                                title: { display: @json(!empty($chartTitle)), text: @json($chartTitle), font: { size: 13, weight: '600' } }
                            }
                        }
                    });
                }
            });
        </script>
        
        @endif
        @endforeach
    </div>
    
    <!-- Footer -->
    @if(!empty($settings['show_footer']))
    <div class="page-footer">
        <div class="footer-left">{{ $settings['footer_left'] ?? '' }}</div>
        <div class="footer-center">{{ $settings['footer_center'] ?? '' }}</div>
        <div class="footer-right">{{ str_replace('{n}', $pageIndex + 1, $settings['footer_right'] ?? '') }}</div>
    </div>
    @endif
    
    <!-- Page Number (if not in footer) -->
    @if(!empty($settings['show_page_numbers']) && empty($settings['show_footer']))
    <div class="page-number">
        {{ $pageIndex + 1 }}
    </div>
    @endif
    
</div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</body>
</html>