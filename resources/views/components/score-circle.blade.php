@props(['score' => 0, 'label' => 'Overall'])
@php
    $score = (int) ($score ?? 0);
    $color = match (true) {
        $score >= 80 => 'text-emerald-500 stroke-emerald-500',
        $score >= 60 => 'text-amber-500 stroke-amber-500',
        default => 'text-rose-500 stroke-rose-500',
    };
    $circumference = 2 * 3.14159 * 45;
    $offset = $circumference - ($score / 100) * $circumference;
@endphp
<div class="flex flex-col items-center">
    <div class="relative w-32 h-32">
        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
            <circle cx="50" cy="50" r="45" stroke="#e5e7eb" stroke-width="8" fill="none" />
            <circle cx="50" cy="50" r="45" stroke-width="8" fill="none"
                    class="{{ $color }}"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $offset }}"
                    stroke-linecap="round" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-3xl font-bold {{ $color }}">{{ $score }}</span>
            <span class="text-xs text-gray-500">/ 100</span>
        </div>
    </div>
    <span class="mt-2 text-sm font-medium text-gray-700">{{ $label }}</span>
</div>
