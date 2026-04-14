<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $report->title }}</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }

body {
  font-family: {{ str_replace(["'","\""], '', $settings['font_family'] ?? 'DejaVu Sans') }}, DejaVu Sans, sans-serif;
  background: {{ $settings['background_color'] ?? '#ffffff' }};
  color: #1e293b;
}

@page {
  size: {{ $settings['page_size'] ?? 'A4' }} {{ $settings['orientation'] ?? 'portrait' }};
  margin: {{ ($settings['margin'] ?? 40) }}px;
}

.page {
  position: relative;
  width: {{ $settings['orientation']==='landscape' ? '1123' : '794' }}px;
  height: {{ $settings['orientation']==='landscape' ? '794' : '1123' }}px;
  overflow: hidden;
  page-break-after: always;
  background: {{ $settings['background_color'] ?? '#ffffff' }};
}
.page:last-child { page-break-after: auto; }

.el { position: absolute; overflow: hidden; }

/* ── Text ── */
.el-text  { word-break: break-word; overflow: hidden; }
.el-quote { border-left: 4px solid {{ $settings['primary_color'] ?? '#6366f1' }}; padding-left: 14px; }

/* ── Table ── */
.el-table table { width: 100%; border-collapse: collapse; font-size: 12px; }
.el-table th { padding: 6px 10px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.el-table td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }

/* ── Metric ── */
.el-metric { display: flex; flex-direction: column; justify-content: center; padding: 16px; }
.el-metric .label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin-bottom: 4px; }
.el-metric .value { font-size: 28px; font-weight: 800; line-height: 1; }
.el-metric .change { display: flex; align-items: center; gap: 4px; margin-top: 6px; }
.el-metric .change-val { font-size: 12px; font-weight: 600; }
.el-metric .change-period { font-size: 11px; color: #94a3b8; }

/* ── Progress ── */
.el-progress { display: flex; flex-direction: column; justify-content: center; gap: 4px; padding: 0 4px; }
.el-progress .prog-header { display: flex; justify-content: space-between; font-size: 12px; }
.el-progress .prog-track { height: 8px; border-radius: 4px; overflow: hidden; }
.el-progress .prog-fill { height: 100%; border-radius: 4px; }

/* ── List ── */
.el-list ul { list-style-type: disc; padding-left: 20px; }
.el-list ol { list-style-type: decimal; padding-left: 20px; }
.el-list li { margin-bottom: 4px; }

/* ── Code ── */
.el-code { background: #1e293b; border-radius: 8px; padding: 12px; }
.el-code pre { color: #34d399; font-family: 'Courier New', monospace; font-size: 12px; white-space: pre-wrap; }

/* ── Badge ── */
.el-badge { display: flex; align-items: center; justify-content: center; }
.el-badge span { padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }

/* ── Callout ── */
.el-callout { display: flex; align-items: flex-start; gap: 10px; border-radius: 10px; padding: 12px; }
.el-callout .callout-icon { font-size: 16px; flex-shrink: 0; margin-top: 2px; }

/* ── Signature ── */
.el-signature { display: flex; flex-direction: column; justify-content: space-between; padding: 8px; }
.el-signature .sig-line { flex: 1; border-bottom: 2px solid #cbd5e1; display: flex; align-items: flex-end; padding-bottom: 4px; }
.el-signature .sig-name { font-size: 22px; color: #94a3b8; font-style: italic; font-family: Georgia, serif; }
.el-signature .sig-label { font-size: 11px; color: #94a3b8; margin-top: 4px; }

/* ── Watermark ── */
.el-watermark { display: flex; align-items: center; justify-content: center; }

/* ── Shapes ── */
.el-circle { border-radius: 50%; }

/* ── Page footer ── */
.page-footer {
  position: absolute; bottom: 0; left: 0; right: 0; height: 36px;
  border-top: 1px solid {{ ($settings['primary_color'] ?? '#6366f1') }}30;
  display: flex; align-items: center; padding: 0 32px;
}
.page-footer .fl { flex: 1; font-size: 10px; color: #94a3b8; }
.page-footer .fc { font-size: 11px; color: #94a3b8; }
.page-footer .fr { flex: 1; text-align: right; font-size: 10px; color: #94a3b8; }
</style>
</head>
<body>

@foreach($content as $pageIndex => $page)
<div class="page">
  @foreach(($page['elements'] ?? []) as $el)
  @php
    $s        = $el['styles'] ?? [];
    $pos      = $el['position'] ?? ['x'=>0,'y'=>0];
    $type     = $el['type'] ?? 'text';
    $primary  = $settings['primary_color'] ?? '#6366f1';
    $fontFam  = str_replace(["'","\""], '', $s['fontFamily'] ?? $settings['font_family'] ?? 'DejaVu Sans');

    // Base style for all elements
    $baseStyle  = "position:absolute;";
    $baseStyle .= "left:{$pos['x']}px;top:{$pos['y']}px;";
    $baseStyle .= "width:" . ($s['width'] ?? 200) . "px;";
    $baseStyle .= "height:" . ($s['height'] ?? 50) . "px;";
    $baseStyle .= "z-index:" . ($s['zIndex'] ?? 1) . ";";

    if (!empty($s['opacity']) && $s['opacity'] != 100) {
      $baseStyle .= "opacity:" . ($s['opacity'] / 100) . ";";
    }
    if (!empty($s['rotate'])) {
      $baseStyle .= "transform:rotate({$s['rotate']}deg);";
    }
    if (!empty($s['borderWidth']) && $s['borderWidth'] > 0) {
      $bStyle = $s['borderStyle'] ?? 'solid';
      $bColor = $s['borderColor'] ?? '#000';
      $baseStyle .= "border:{$s['borderWidth']}px $bStyle $bColor;";
    }
    if (!empty($s['borderRadius'])) {
      $baseStyle .= "border-radius:{$s['borderRadius']}px;";
    }
    if (!empty($s['padding'])) {
      $baseStyle .= "padding:{$s['padding']}px;";
    }
    $shadowMap = ['sm'=>'0 1px 3px rgba(0,0,0,.12)','md'=>'0 4px 12px rgba(0,0,0,.1)','lg'=>'0 10px 30px rgba(0,0,0,.15)','xl'=>'0 20px 60px rgba(0,0,0,.2)'];
    if (!empty($s['boxShadow']) && $s['boxShadow'] !== 'none' && isset($shadowMap[$s['boxShadow']])) {
      $baseStyle .= "box-shadow:{$shadowMap[$s['boxShadow']]};";
    }

    // Text style helper
    $txtStyle  = "font-family:{$fontFam},DejaVu Sans,sans-serif;";
    $txtStyle .= "font-size:" . ($s['fontSize'] ?? 14) . "px;";
    $txtStyle .= "color:" . ($s['color'] ?? '#1e293b') . ";";
    $txtStyle .= "font-weight:" . ($s['fontWeight'] ?? (in_array($type,['heading'])? '700':'400')) . ";";
    $txtStyle .= "font-style:" . ($s['fontStyle'] ?? 'normal') . ";";
    $txtStyle .= "text-align:" . ($s['textAlign'] ?? 'left') . ";";
    $txtStyle .= "text-decoration:" . ($s['textDecoration'] ?? 'none') . ";";
    $txtStyle .= "text-transform:" . ($s['textTransform'] ?? 'none') . ";";
    $txtStyle .= "line-height:" . ($s['lineHeight'] ?? 1.5) . ";";
    if (!empty($s['letterSpacing'])) $txtStyle .= "letter-spacing:{$s['letterSpacing']}px;";
    if (!empty($s['backgroundColor']) && $s['backgroundColor'] !== 'transparent') {
      $txtStyle .= "background-color:{$s['backgroundColor']};";
    }
  @endphp

  {{-- TEXT --}}
  @if(in_array($type, ['text','heading','subheading']))
    <div class="el el-text" style="{{ $baseStyle }}{{ $txtStyle }}">{!! $el['content'] ?? '' !!}</div>

  {{-- QUOTE --}}
  @elseif($type === 'quote')
    <div class="el el-text el-quote" style="{{ $baseStyle }}{{ $txtStyle }}">{!! $el['content'] ?? '' !!}</div>

  {{-- LIST --}}
  @elseif($type === 'list')
    <div class="el el-list" style="{{ $baseStyle }}{{ $txtStyle }}">
      @if(($el['styles']['listStyle'] ?? '') === 'numbered')
        <ol>@foreach($el['items'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ol>
      @else
        <ul>@foreach($el['items'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ul>
      @endif
    </div>

  {{-- CALLOUT --}}
  @elseif($type === 'callout')
    @php
      $calloutBg    = $s['backgroundColor'] ?? ($primary . '18');
      $calloutBorder= $s['color'] ?? $primary;
    @endphp
    <div class="el el-callout" style="{{ $baseStyle }}background-color:{{ $calloutBg }};border-left:4px solid {{ $calloutBorder }};">
      <span class="callout-icon">{{ $el['icon'] ?? '💡' }}</span>
      <div style="{{ $txtStyle }}">{!! $el['content'] ?? '' !!}</div>
    </div>

  {{-- CODE --}}
  @elseif($type === 'code')
    <div class="el el-code" style="{{ $baseStyle }}">
      <pre>{{ $el['content'] ?? '' }}</pre>
    </div>

  {{-- BADGE --}}
  @elseif($type === 'badge')
    @php
      $badgeBg  = $s['backgroundColor'] ?? ($primary . '25');
      $badgeClr = $s['color'] ?? $primary;
    @endphp
    <div class="el el-badge" style="{{ $baseStyle }}">
      <span style="background-color:{{ $badgeBg }};color:{{ $badgeClr }}">{{ $el['content'] ?? 'Label' }}</span>
    </div>

  {{-- LINK --}}
  @elseif($type === 'link')
    @php $linkClr = $s['color'] ?? $primary; @endphp
    <a href="{{ $el['href'] ?? '#' }}" class="el" style="{{ $baseStyle }}{{ $txtStyle }}color:{{ $linkClr }};text-decoration:underline;display:flex;align-items:center;">
      {{ $el['content'] ?? ($el['href'] ?? 'Link') }}
    </a>

  {{-- IMAGE --}}
  @elseif($type === 'image')
    @if(!empty($el['src']))
      @php
        $imgSrc  = $el['src'] ?? '';
        $objFit  = $s['objectFit'] ?? 'cover';
        $bradius = ($s['borderRadius'] ?? 0) . 'px';
      @endphp
      <div class="el" style="{{ $baseStyle }}overflow:hidden;">
        <img src="{{ $imgSrc }}" style="width:100%;height:100%;object-fit:{{ $objFit }};border-radius:{{ $bradius }};display:block;" alt=""/>
      </div>
    @endif

  {{-- TABLE --}}
  @elseif($type === 'table')
    @php
      $hBg  = $s['headerBg']    ?? $primary;
      $hClr = $s['headerColor'] ?? '#fff';
      $eRow = $s['evenRowBg']   ?? '#ffffff';
      $oRow = $s['oddRowBg']    ?? '#f8fafc';
    @endphp
    <div class="el el-table" style="{{ $baseStyle }}">
      <table>
        <thead><tr>
          @foreach($el['columns'] ?? [] as $col)
            <th style="background-color:{{ $hBg }};color:{{ $hClr }}">{{ $col }}</th>
          @endforeach
        </tr></thead>
        <tbody>
          @foreach($el['data'] ?? [] as $ri => $row)
            <tr style="background-color:{{ $ri%2===0 ? $eRow : $oRow }}">
              @foreach($el['columns'] ?? [] as $col)
                <td>{{ $row[$col] ?? '' }}</td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  {{-- METRIC --}}
  @elseif($type === 'metric')
    @php
      $mBg  = $s['backgroundColor'] ?? '#f8fafc';
      $mBrd = $s['borderColor']      ?? '#e2e8f0';
      $mClr = $s['color']            ?? $primary;
      $mRad = ($s['borderRadius'] ?? 12) . 'px';
    @endphp
    <div class="el el-metric" style="{{ $baseStyle }}background-color:{{ $mBg }};border:1px solid {{ $mBrd }};border-radius:{{ $mRad }};">
      <div class="label">{{ $el['label'] ?? '' }}</div>
      <div class="value" style="color:{{ $mClr }}">{{ $el['value'] ?? '' }}</div>
      @if(!empty($el['change']))
        <div class="change">
          <span class="change-val" style="color:{{ ($el['changeType']??'positive')==='positive' ? '#059669' : '#dc2626' }}">
            {{ ($el['changeType']??'positive')==='positive' ? '▲' : '▼' }} {{ $el['change'] }}
          </span>
          <span class="change-period">{{ $el['changePeriod'] ?? '' }}</span>
        </div>
      @endif
    </div>

  {{-- PROGRESS --}}
  @elseif($type === 'progress')
    @php
      $pClr   = $s['color']      ?? $primary;
      $pTrack = $s['trackColor'] ?? '#e2e8f0';
      $pVal   = $el['value']     ?? 0;
    @endphp
    <div class="el el-progress" style="{{ $baseStyle }}">
      <div class="prog-header" style="color:{{ $pClr }}">
        <span>{{ $el['label'] ?? '' }}</span><span>{{ $pVal }}%</span>
      </div>
      <div class="prog-track" style="background-color:{{ $pTrack }};width:100%">
        <div class="prog-fill" style="width:{{ $pVal }}%;background-color:{{ $pClr }}"></div>
      </div>
    </div>

  {{-- RECTANGLE --}}
  @elseif($type === 'rectangle')
    @php $rBg = $s['backgroundColor'] ?? $primary; @endphp
    <div class="el" style="{{ $baseStyle }}background-color:{{ $rBg }};"></div>

  {{-- CIRCLE --}}
  @elseif($type === 'circle')
    @php $cBg = $s['backgroundColor'] ?? $primary; @endphp
    <div class="el el-circle" style="{{ $baseStyle }}background-color:{{ $cBg }};"></div>

  {{-- TRIANGLE --}}
  @elseif($type === 'triangle')
    @php $tBg = $s['backgroundColor'] ?? '#f59e0b'; $tw=$s['width']??100; $th=$s['height']??100; @endphp
    <div class="el" style="{{ $baseStyle }}">
      <div style="width:0;height:0;border-left:{{ floor($tw/2) }}px solid transparent;border-right:{{ floor($tw/2) }}px solid transparent;border-bottom:{{ $th }}px solid {{ $tBg }};"></div>
    </div>

  {{-- LINE --}}
  @elseif($type === 'line')
    @php $lClr=$s['color']??'#94a3b8'; $lW=$s['borderWidth']??2; @endphp
    <div class="el" style="{{ $baseStyle }}display:flex;align-items:center;">
      <div style="width:100%;border-top:{{ $lW }}px solid {{ $lClr }};"></div>
    </div>

  {{-- DIVIDER --}}
  @elseif($type === 'divider')
    @php $dClr=$s['color']??'#e2e8f0'; $dW=$s['borderWidth']??1; $dSt=$s['borderStyle']??'solid'; @endphp
    <div class="el" style="{{ $baseStyle }}display:flex;align-items:center;">
      <div style="width:100%;border-top:{{ $dW }}px {{ $dSt }} {{ $dClr }};"></div>
    </div>

  {{-- DATE --}}
  @elseif($type === 'date')
    <div class="el el-text" style="{{ $baseStyle }}{{ $txtStyle }}display:flex;align-items:center;">
      {{ now()->format('F j, Y') }}
    </div>

  {{-- PAGE NUMBER --}}
  @elseif($type === 'pagenum')
    <div class="el el-text" style="{{ $baseStyle }}{{ $txtStyle }}display:flex;align-items:center;justify-content:center;">
      {{ $pageIndex + 1 }}
    </div>

  {{-- SIGNATURE --}}
  @elseif($type === 'signature')
    <div class="el el-signature" style="{{ $baseStyle }}">
      <div class="sig-line">
        <span class="sig-name">{{ $el['content'] ?? '' }}</span>
      </div>
      <p class="sig-label">{{ $el['label'] ?? 'Authorized Signature' }}</p>
    </div>

  {{-- WATERMARK --}}
  @elseif($type === 'watermark')
    @php
      $wmOp  = isset($s['opacity']) ? $s['opacity']/100 : 0.2;
      $wmFsz = $s['fontSize'] ?? 48;
      $wmClr = $s['color'] ?? '#94a3b8';
      $wmRot = $s['rotate'] ?? -30;
    @endphp
    <div class="el el-watermark" style="{{ $baseStyle }}opacity:{{ $wmOp }};">
      <span style="font-size:{{ $wmFsz }}px;font-weight:800;color:{{ $wmClr }};transform:rotate({{ $wmRot }}deg);display:inline-block;white-space:nowrap;">
        {{ $el['content'] ?? 'CONFIDENTIAL' }}
      </span>
    </div>

  @endif
  @endforeach

  {{-- Page footer --}}
  @if(($settings['show_page_numbers'] ?? true) || !empty($settings['footer_left']) || !empty($settings['footer_right']))
    <div class="page-footer">
      <span class="fl">{{ $settings['footer_left'] ?? '' }}</span>
      <span class="fc">
        @if($settings['show_page_numbers'] ?? true)
          {{ $pageIndex + 1 }}@if(count($content) > 1) / {{ count($content) }}@endif
        @endif
      </span>
      <span class="fr">{{ $settings['footer_right'] ?? '' }}</span>
    </div>
  @endif
</div>
@endforeach

</body>
</html>