@php
    // Normalise any value that Gemini may return as array instead of string
    $str = fn($v): string => is_array($v)
        ? implode(', ', array_map('strval', array_filter($v, fn($i) => !is_array($i))))
        : (string) ($v ?? '');
@endphp

{{-- Scores ------------------------------------------------------------------ --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-base font-semibold text-gray-900 mb-6">Scores</h3>

    <div class="flex flex-col sm:flex-row gap-8 items-start">

        <div class="flex-shrink-0">
            <x-score-circle :score="$analysis->overall_score ?? 0" label="Overall" />
        </div>

        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
            <x-score-bar label="ATS Compatibility"  :score="$analysis->ats_score             ?? 0" />
            <x-score-bar label="Readability"         :score="$analysis->readability_score     ?? 0" />
            <x-score-bar label="Skills Match"        :score="$analysis->skills_score          ?? 0" />
            <x-score-bar label="Professionalism"     :score="$analysis->professionalism_score ?? 0" />
        </div>
    </div>
</div>

{{-- Summary ----------------------------------------------------------------- --}}
@if ($analysis->summary)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-base font-semibold text-gray-900 mb-3">Summary</h3>
    <p class="text-sm text-gray-700 leading-relaxed">{{ $str($analysis->summary) }}</p>
</div>
@endif

{{-- Keyword Match ------------------------------------------------------------ --}}
@php
    $matched = is_array($analysis->keyword_match['matched'] ?? null)
        ? $analysis->keyword_match['matched'] : [];
    $missing = is_array($analysis->keyword_match['missing'] ?? null)
        ? $analysis->keyword_match['missing'] : [];
@endphp
@if (count($matched) || count($missing))
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-base font-semibold text-gray-900 mb-4">Keyword Match</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        <div>
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-2">
                ✓ Matched ({{ count($matched) }})
            </p>
            <div class="flex flex-wrap gap-1.5">
                @forelse ($matched as $kw)
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $str($kw) }}
                    </span>
                @empty
                    <span class="text-xs text-gray-400">None found</span>
                @endforelse
            </div>
        </div>

        <div>
            <p class="text-xs font-semibold text-rose-500 uppercase tracking-wide mb-2">
                ✗ Missing ({{ count($missing) }})
            </p>
            <div class="flex flex-wrap gap-1.5">
                @forelse ($missing as $kw)
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200">
                        {{ $str($kw) }}
                    </span>
                @empty
                    <span class="text-xs text-gray-400">None missing</span>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endif

{{-- Strengths & Weaknesses --------------------------------------------------- --}}
@php
    $strengths  = is_array($analysis->feedback['strengths']  ?? null) ? $analysis->feedback['strengths']  : [];
    $weaknesses = is_array($analysis->feedback['weaknesses'] ?? null) ? $analysis->feedback['weaknesses'] : [];
@endphp
@if (count($strengths) || count($weaknesses))
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        @if (count($strengths))
        <div>
            <h3 class="text-base font-semibold text-gray-900 mb-3">Strengths</h3>
            <ul class="space-y-2">
                @foreach ($strengths as $item)
                    <li class="flex gap-2 text-sm text-gray-700">
                        <span class="mt-0.5 flex-shrink-0 text-emerald-500">✓</span>
                        <span>{{ $str($item) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (count($weaknesses))
        <div>
            <h3 class="text-base font-semibold text-gray-900 mb-3">Areas for Improvement</h3>
            <ul class="space-y-2">
                @foreach ($weaknesses as $item)
                    <li class="flex gap-2 text-sm text-gray-700">
                        <span class="mt-0.5 flex-shrink-0 text-amber-500">⚠</span>
                        <span>{{ $str($item) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

    </div>
</div>
@endif

{{-- Suggestions ------------------------------------------------------------- --}}
@php
    $suggestions = is_array($analysis->feedback['suggestions'] ?? null) ? $analysis->feedback['suggestions'] : [];
@endphp
@if (count($suggestions))
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-base font-semibold text-gray-900 mb-4">Actionable Suggestions</h3>
    <div class="space-y-3">
        @foreach ($suggestions as $s)
            @php
                $area = $str(is_array($s) ? ($s['area'] ?? '') : $s);
                $tip  = $str(is_array($s) ? ($s['tip']  ?? '') : '');
            @endphp
            <div class="flex gap-3 p-3 rounded-lg bg-indigo-50 border border-indigo-100">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center mt-0.5">
                    {{ $loop->iteration }}
                </span>
                <div>
                    @if ($area !== '')
                        <p class="text-xs font-semibold text-indigo-700 mb-0.5">{{ $area }}</p>
                    @endif
                    @if ($tip !== '')
                        <p class="text-sm text-gray-700">{{ $tip }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Flags ------------------------------------------------------------------- --}}
@php
    $flags = is_array($analysis->flags ?? null) ? $analysis->flags : [];

    // Gemini returns flags as an object: { key: bool, section_order_issues: [string] }
    $boolFlagLabels = [
        'missing_dates'              => 'Missing Dates',
        'no_measurable_achievements' => 'No Measurable Achievements',
        'long_paragraphs'            => 'Long Paragraphs',
        'passive_voice_overuse'      => 'Passive Voice Overuse',
    ];
    $activeFlags        = array_filter($boolFlagLabels, fn($label, $key) => !empty($flags[$key]), ARRAY_FILTER_USE_BOTH);
    $sectionOrderIssues = is_array($flags['section_order_issues'] ?? null) ? $flags['section_order_issues'] : [];
    $hasAnyFlag         = count($activeFlags) || count($sectionOrderIssues);
@endphp
@if ($hasAnyFlag)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-base font-semibold text-gray-900 mb-4">Flags &amp; Warnings</h3>

    {{-- Boolean flag badges --}}
    @if (count($activeFlags))
    <div class="flex flex-wrap gap-2 {{ count($sectionOrderIssues) ? 'mb-4' : '' }}">
        @foreach ($activeFlags as $label)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                {{ $label }}
            </span>
        @endforeach
    </div>
    @endif

    {{-- Section order issues --}}
    @if (count($sectionOrderIssues))
    <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Section Order Issues</p>
        <ul class="space-y-1">
            @foreach ($sectionOrderIssues as $issue)
                <li class="flex gap-2 text-sm text-gray-700">
                    <span class="flex-shrink-0 text-amber-500">•</span>
                    <span>{{ $str($issue) }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>
@endif

{{-- Meta -------------------------------------------------------------------- --}}
<p class="text-xs text-gray-400 text-right">
    Analysed {{ $analysis->analyzed_at?->diffForHumans() ?? 'recently' }}
    · {{ $resume->original_filename }}
</p>
