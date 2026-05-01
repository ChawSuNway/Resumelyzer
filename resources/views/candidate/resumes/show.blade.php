<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $resume->original_filename }}</h2>
            <div class="space-x-2 text-sm">
                <a href="{{ route('candidate.resumes.download', $resume) }}" class="text-indigo-600 hover:underline">Download original</a>
                @if ($analysis?->isComplete())
                    <a href="{{ route('candidate.resumes.export.pdf', $resume) }}" class="text-indigo-600 hover:underline">Export PDF</a>
                    <a href="{{ route('candidate.resumes.export.csv', $resume) }}" class="text-indigo-600 hover:underline">Export CSV</a>
                    {{-- <a href="{{ route('candidate.resumes.export.draft', $resume) }}" class="text-indigo-600 hover:underline">Improved draft</a> --}}
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        @if (! $analysis || ! $analysis->isComplete())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-700">
                    @if (! $analysis)
                        Analysis not yet generated.
                    @elseif ($analysis->status === 'failed')
                        Analysis failed: <span class="text-rose-600">{{ $analysis->error }}</span>
                    @else
                        Analysis is {{ $analysis->status }}.
                    @endif
                </p>
                <form method="POST" action="{{ route('candidate.resumes.reanalyze', $resume) }}" class="mt-4">
                    @csrf
                    <button class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Run analysis</button>
                </form>
            </div>
        @else
            @include('candidate.resumes.analysis', ['analysis' => $analysis, 'resume' => $resume])
        @endif
    </div>
</x-app-layout>
