@props(['label' => '', 'score' => 0, 'tip' => null])
@php
    $score = (int) ($score ?? 0);
    $color = match (true) {
        $score >= 80 => 'bg-emerald-500',
        $score >= 60 => 'bg-amber-500',
        default => 'bg-rose-500',
    };
@endphp
<div class="space-y-1" @if($tip) title="{{ $tip }}" @endif>
    <div class="flex justify-between text-sm">
        <span class="font-medium text-gray-700">{{ $label }}</span>
        <span class="font-semibold text-gray-900">{{ $score }}/100</span>
    </div>
    <div class="w-full bg-gray-100 rounded-full h-2">
        <div class="{{ $color }} h-2 rounded-full transition-all" style="width: {{ $score }}%"></div>
    </div>
</div>
