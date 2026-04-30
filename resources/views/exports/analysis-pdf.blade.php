<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Resume Analysis Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: #fff; }

        /* ── Header ── */
        .header { background: #4f46e5; color: #fff; padding: 28px 36px; }
        .header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .header p  { font-size: 11px; opacity: .85; }

        /* ── Layout ── */
        .page { padding: 28px 36px; }
        .section { margin-bottom: 24px; }
        .section-title {
            font-size: 13px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: #4f46e5;
            border-bottom: 2px solid #e0e7ff; padding-bottom: 5px; margin-bottom: 12px;
        }

        /* ── Score grid ── */
        .score-grid { width: 100%; border-collapse: collapse; }
        .score-grid td { padding: 6px 8px; vertical-align: middle; }
        .score-overall { text-align: center; padding: 16px 0 20px; }
        .score-overall .big { font-size: 52px; font-weight: 700; color: #4f46e5; line-height: 1; }
        .score-overall .sub { font-size: 11px; color: #6b7280; margin-top: 2px; }

        .bar-label { width: 160px; font-size: 11px; color: #374151; }
        .bar-wrap { background: #e5e7eb; border-radius: 99px; height: 10px; width: 100%; }
        .bar-fill  { height: 10px; border-radius: 99px; }
        .bar-val   { width: 36px; text-align: right; font-size: 11px; font-weight: 600; color: #374151; }

        .green  { background: #10b981; }
        .yellow { background: #f59e0b; }
        .red    { background: #ef4444; }

        /* ── Summary ── */
        .summary-box { background: #f8fafc; border-left: 4px solid #4f46e5; padding: 12px 16px; font-size: 11.5px; line-height: 1.65; color: #374151; }

        /* ── Keywords ── */
        .kw-grid { width: 100%; border-collapse: collapse; }
        .kw-grid td { vertical-align: top; width: 50%; padding-right: 12px; }
        .kw-grid td:last-child { padding-right: 0; }
        .kw-title { font-size: 11px; font-weight: 700; margin-bottom: 6px; }
        .kw-title.matched { color: #059669; }
        .kw-title.missing { color: #dc2626; }
        .tag {
            display: inline-block; padding: 2px 8px; border-radius: 99px;
            font-size: 10px; margin: 2px 2px 2px 0;
        }
        .tag-green { background: #d1fae5; color: #065f46; }
        .tag-red   { background: #fee2e2; color: #991b1b; }

        /* ── Feedback lists ── */
        .fb-list { padding-left: 16px; }
        .fb-list li { font-size: 11.5px; line-height: 1.6; color: #374151; margin-bottom: 3px; }
        .fb-list li::marker { color: #4f46e5; }

        /* ── Suggestions table ── */
        .sug-table { width: 100%; border-collapse: collapse; }
        .sug-table th { background: #eef2ff; color: #3730a3; font-size: 11px; text-align: left; padding: 7px 10px; }
        .sug-table td { font-size: 11px; padding: 7px 10px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .sug-table tr:last-child td { border-bottom: none; }
        .sug-table td:first-child { font-weight: 600; color: #4f46e5; white-space: nowrap; width: 130px; }

        /* ── Flags ── */
        .flag-item { display: inline-block; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; border-radius: 6px; padding: 4px 10px; font-size: 10.5px; margin: 0 4px 4px 0; }

        /* ── Footer ── */
        .footer { margin-top: 32px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; font-size: 9.5px; color: #9ca3af; }
    </style>
</head>
<body>

{{-- ── Header ── --}}
<div class="header">
    <h1>Resume Analysis Report</h1>
    <p>
        {{ $resume->original_filename }}
        &nbsp;·&nbsp;
        Analysed: {{ $analysis->analyzed_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}
    </p>
</div>

<div class="page">

    {{-- ── Overall Score ── --}}
    <div class="section">
        <div class="section-title">Overall Score</div>
        <div class="score-overall">
            @php
                $oc = match(true) {
                    ($analysis->overall_score ?? 0) >= 80 => '#10b981',
                    ($analysis->overall_score ?? 0) >= 60 => '#f59e0b',
                    default => '#ef4444',
                };
            @endphp
            <div class="big" style="color: {{ $oc }};">{{ $analysis->overall_score ?? '—' }}</div>
            <div class="sub">out of 100</div>
        </div>

        @php
            $subScores = [
                'ATS Compatibility'   => $analysis->ats_score,
                'Readability'         => $analysis->readability_score,
                'Skills Match'        => $analysis->skills_score,
                'Professionalism'     => $analysis->professionalism_score,
            ];
        @endphp
        <table class="score-grid">
            @foreach ($subScores as $label => $score)
                @php
                    $score = (int)($score ?? 0);
                    $cls   = $score >= 80 ? 'green' : ($score >= 60 ? 'yellow' : 'red');
                @endphp
                <tr>
                    <td class="bar-label">{{ $label }}</td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar-fill {{ $cls }}" style="width: {{ $score }}%;"></div>
                        </div>
                    </td>
                    <td class="bar-val">{{ $score }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- ── Summary ── --}}
    @if ($analysis->summary)
    <div class="section">
        <div class="section-title">Summary</div>
        <div class="summary-box">{{ $analysis->summary }}</div>
    </div>
    @endif

    {{-- ── Keyword Match ── --}}
    @php
        $matched = $analysis->keyword_match['matched'] ?? [];
        $missing = $analysis->keyword_match['missing'] ?? [];
    @endphp
    @if (count($matched) || count($missing))
    <div class="section">
        <div class="section-title">Keyword Match</div>
        <table class="kw-grid">
            <tr>
                <td>
                    <div class="kw-title matched">&#10003; Matched ({{ count($matched) }})</div>
                    @foreach ($matched as $kw)
                        <span class="tag tag-green">{{ $kw }}</span>
                    @endforeach
                </td>
                <td>
                    <div class="kw-title missing">&#10005; Missing ({{ count($missing) }})</div>
                    @foreach ($missing as $kw)
                        <span class="tag tag-red">{{ $kw }}</span>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- ── Strengths ── --}}
    @php $strengths = $analysis->feedback['strengths'] ?? []; @endphp
    @if (count($strengths))
    <div class="section">
        <div class="section-title">Strengths</div>
        <ul class="fb-list">
            @foreach ($strengths as $s)
                <li>{{ $s }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Weaknesses ── --}}
    @php $weaknesses = $analysis->feedback['weaknesses'] ?? []; @endphp
    @if (count($weaknesses))
    <div class="section">
        <div class="section-title">Areas for Improvement</div>
        <ul class="fb-list">
            @foreach ($weaknesses as $w)
                <li>{{ $w }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Suggestions ── --}}
    @php $suggestions = $analysis->feedback['suggestions'] ?? []; @endphp
    @if (count($suggestions))
    <div class="section">
        <div class="section-title">Actionable Suggestions</div>
        <table class="sug-table">
            <tr>
                <th>Area</th>
                <th>Tip</th>
            </tr>
            @foreach ($suggestions as $s)
                <tr>
                    <td>{{ $s['area'] ?? '—' }}</td>
                    <td>{{ $s['tip']  ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- ── Flags ── --}}
    @php $flags = $analysis->flags ?? []; @endphp
    @if (count($flags))
    <div class="section">
        <div class="section-title">Flags &amp; Warnings</div>
        @foreach ($flags as $flag)
            <span class="flag-item">&#9888; {{ $flag }}</span>
        @endforeach
    </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="footer">
        Generated by Resumelyzer &nbsp;·&nbsp; {{ now()->format('d M Y') }}
    </div>

</div>
</body>
</html>
