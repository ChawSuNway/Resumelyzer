@props(['code' => 'en', 'class' => 'w-5 h-3.5'])

@php $clipId = 'flag-en-clip-'.\Illuminate\Support\Str::random(6); @endphp

@switch($code)
    @case('en')
        {{-- Union Jack (English) --}}
        <svg viewBox="0 0 60 30" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $class . ' rounded-sm shadow-inner ring-1 ring-black/10 inline-block align-middle']) }}>
            <clipPath id="{{ $clipId }}"><path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/></clipPath>
            <path d="M0,0 v30 h60 V0 z" fill="#012169"/>
            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
            <path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#{{ $clipId }})" stroke="#C8102E" stroke-width="4"/>
            <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
            <path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/>
        </svg>
        @break

    @case('my')
        {{-- Myanmar --}}
        <svg viewBox="0 0 60 30" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $class . ' rounded-sm shadow-inner ring-1 ring-black/10 inline-block align-middle']) }}>
            <rect width="60" height="10" fill="#FECB00"/>
            <rect y="10" width="60" height="10" fill="#34B233"/>
            <rect y="20" width="60" height="10" fill="#EA2839"/>
            <polygon points="30,7 32.35,14.27 40,14.27 33.82,18.77 36.18,26.04 30,21.55 23.82,26.04 26.18,18.77 20,14.27 27.65,14.27" fill="#fff"/>
        </svg>
        @break

    @case('jp')
        {{-- Japan --}}
        <svg viewBox="0 0 60 30" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $class . ' rounded-sm shadow-inner ring-1 ring-black/10 inline-block align-middle']) }}>
            <rect width="60" height="30" fill="#fff"/>
            <circle cx="30" cy="15" r="9" fill="#BC002D"/>
        </svg>
        @break
@endswitch
