<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recruiter Dashboard</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="grid md:grid-cols-3 gap-6">
            {{-- My job postings --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-900">My Job Postings</h3>
                    <a href="{{ route('recruiter.jobs.create') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">+ New</a>
                </div>
                @forelse ($jobs as $job)
                    <a href="{{ route('recruiter.jobs.edit', $job) }}" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-md">
                        <div>
                            <div class="font-medium text-gray-900 text-sm">{{ $job->title }}</div>
                            <div class="text-xs text-gray-500">{{ $job->company }} · {{ $job->location }}</div>
                        </div>
                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $job->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $job->is_active ? 'Active' : 'Closed' }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">No job postings yet.</p>
                @endforelse
                <div class="mt-4">
                    <a href="{{ route('recruiter.jobs.index') }}" class="text-xs text-indigo-600 hover:underline">View all jobs &rarr;</a>
                </div>
            </div>

            {{-- Recent candidates --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Candidates</h3>
                    <a href="{{ route('recruiter.candidates.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Browse all</a>
                </div>
                @forelse ($candidates as $candidate)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <div class="font-medium text-gray-900 text-sm">{{ $candidate->name }}</div>
                            <div class="text-xs text-gray-500">{{ $candidate->resumes_count }} resume{{ $candidate->resumes_count !== 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No candidates have shared their resumes.</p>
                @endforelse
            </div>

            {{-- Latest shared resumes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Recent Shared Resumes</h3>
                </div>
                @forelse ($sharedResumes as $resume)
                    <a href="{{ route('recruiter.candidates.show', $resume) }}" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-md">
                        <div>
                            <div class="font-medium text-gray-900 text-sm">{{ $resume->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $resume->original_filename }}</div>
                        </div>
                        @if ($resume->latestAnalysis?->isComplete())
                            <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if ($resume->latestAnalysis->overall_score >= 80) bg-emerald-100 text-emerald-700
                                @elseif ($resume->latestAnalysis->overall_score >= 60) bg-amber-100 text-amber-700
                                @else bg-rose-100 text-rose-700 @endif">
                                {{ $resume->latestAnalysis->overall_score }}
                            </span>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-gray-500">No resumes shared with recruiters yet.</p>
                @endforelse
                <div class="mt-4">
                    <a href="{{ route('recruiter.candidates.index') }}" class="text-xs text-indigo-600 hover:underline">Browse candidates &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
