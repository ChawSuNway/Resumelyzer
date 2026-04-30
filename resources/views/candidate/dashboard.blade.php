<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Candidate Dashboard</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:col-span-1 flex flex-col items-center justify-center">
                @if ($latestAnalysis && $latestAnalysis->isComplete())
                    <x-score-circle :score="$latestAnalysis->overall_score" label="Latest Score" />
                    <p class="mt-3 text-xs text-gray-500 text-center">{{ $latestAnalysis->summary }}</p>
                @else
                    <div class="text-center">
                        <p class="text-gray-500 text-sm">No analyses yet.</p>
                        <a href="{{ route('candidate.resumes.create') }}" class="mt-4 inline-block px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Upload your resume</a>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Your Resumes</h3>
                    <a href="{{ route('candidate.resumes.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">+ Upload new</a>
                </div>
                @forelse ($resumes as $resume)
                    <a href="{{ route('candidate.resumes.show', $resume) }}" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-md">
                        <div>
                            <div class="font-medium text-gray-900">{{ $resume->original_filename }}</div>
                            <div class="text-xs text-gray-500">{{ $resume->created_at->diffForHumans() }} · {{ strtoupper($resume->extension) }}</div>
                        </div>
                        @if ($resume->latestAnalysis && $resume->latestAnalysis->isComplete())
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                @if ($resume->latestAnalysis->overall_score >= 80) bg-emerald-100 text-emerald-700
                                @elseif ($resume->latestAnalysis->overall_score >= 60) bg-amber-100 text-amber-700
                                @else bg-rose-100 text-rose-700 @endif">
                                {{ $resume->latestAnalysis->overall_score }}/100
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $resume->latestAnalysis?->status ?? 'pending' }}</span>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-gray-500">You haven't uploaded any resumes yet.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Open Jobs You Can Match Against</h3>
            @forelse ($openJobs as $job)
                <div class="py-3 border-b border-gray-100 last:border-0">
                    <div class="font-medium text-gray-900">{{ $job->title }}</div>
                    <div class="text-xs text-gray-500">{{ $job->company }} · {{ $job->location }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No active job postings yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
