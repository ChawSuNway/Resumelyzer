@props(['set'])

@php
    $categoryLabels = [
        'technical'     => 'Technical',
        'behavioral'    => 'Behavioral',
        'role_specific' => 'Role-Specific',
        'situational'   => 'Situational',
    ];
    $difficultyClass = [
        'easy'         => 'bg-emerald-100 text-emerald-700',
        'intermediate' => 'bg-amber-100 text-amber-700',
        'hard'         => 'bg-rose-100 text-rose-700',
    ];

    // Only include categories that actually have questions; pick the first available as default tab.
    $available = [];
    foreach ($categoryLabels as $key => $label) {
        $items = $set->questions[$key] ?? [];
        if (count($items)) {
            $available[$key] = $label;
        }
    }
    $defaultTab = array_key_first($available) ?? 'technical';
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
     x-data="{ tab: '{{ $defaultTab }}' }">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Interview Questions</h3>
            @if (! empty($set->meta['summary']))
                <p class="text-sm text-gray-600 mt-1">{{ $set->meta['summary'] }}</p>
            @endif
            @if (! empty($set->meta['job_title']))
                <p class="text-xs text-gray-500 mt-1">Tailored for: <span class="font-medium">{{ $set->meta['job_title'] }}</span></p>
            @endif
        </div>
        <span class="text-xs text-gray-400 whitespace-nowrap">Generated {{ $set->created_at->diffForHumans() }}</span>
    </div>

    {{-- Tab menu --}}
    <div class="border-b border-gray-200 mb-5">
        <nav class="-mb-px flex flex-wrap gap-x-6 gap-y-2">
            @foreach ($available as $key => $label)
                @php $count = count($set->questions[$key] ?? []); @endphp
                <button type="button"
                        @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                    {{ $label }}
                    <span class="ms-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600"
                          :class="tab === '{{ $key }}' ? '!bg-indigo-100 !text-indigo-700' : ''">
                        {{ $count }}
                    </span>
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab panels --}}
    @foreach ($available as $key => $label)
        @php $items = $set->questions[$key] ?? []; @endphp
        <div x-show="tab === '{{ $key }}'" x-cloak>
            <ol class="space-y-4 list-decimal list-inside">
                @foreach ($items as $q)
                    <li class="text-sm">
                        <div class="inline">
                            <span class="font-medium text-gray-900">{{ is_array($q['question'] ?? null) ? '' : ($q['question'] ?? '') }}</span>
                            @if (! empty($q['difficulty']))
                                <span class="ms-2 inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $difficultyClass[strtolower($q['difficulty'])] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($q['difficulty']) }}
                                </span>
                            @endif
                        </div>
                        @if (! empty($q['based_on']))
                            <div class="mt-1 ms-5 text-xs text-gray-500"><span class="font-semibold">Based on:</span> {{ $q['based_on'] }}</div>
                        @endif
                        @if (! empty($q['ideal_answer_should_cover']))
                            <div class="mt-1 ms-5 text-xs text-gray-600"><span class="font-semibold">Ideal answer covers:</span> {{ $q['ideal_answer_should_cover'] }}</div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @endforeach
</div>
