<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('recruiter.candidates.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Candidates</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $resume->user->name }}</h2>
            </div>
            <a href="{{ route('recruiter.candidates.download', $resume) }}" class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Download Resume</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="grid md:grid-cols-3 gap-6">
            {{-- Analysis score --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center">
                @if ($resume->latestAnalysis?->isComplete())
                    <x-score-circle :score="$resume->latestAnalysis->overall_score" label="Overall Score" />
                    <p class="mt-3 text-xs text-gray-500 text-center">{{ $resume->latestAnalysis->summary }}</p>
                @else
                    <p class="text-sm text-gray-500">No analysis available.</p>
                @endif
            </div>

            {{-- File info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3 md:col-span-2">
                <h3 class="text-base font-semibold text-gray-900">Resume Details</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500">File</dt>
                        <dd class="font-medium text-gray-900">{{ $resume->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Uploaded</dt>
                        <dd class="font-medium text-gray-900">{{ $resume->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Candidate email</dt>
                        <dd class="font-medium text-gray-900">{{ $resume->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Type</dt>
                        <dd class="font-medium text-gray-900">{{ strtoupper($resume->extension) }}</dd>
                    </div>
                </dl>

                {{-- Compare against a job --}}
                @if ($jobs->isNotEmpty())
                    <form method="POST" action="{{ route('recruiter.candidates.compare', $resume) }}" class="mt-4 flex gap-3 items-center">
                        @csrf
                        <select name="job_posting_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm flex-1">
                            <option value="">— Select a job posting —</option>
                            @foreach ($jobs as $job)
                                <option value="{{ $job->id }}">{{ $job->title }}{{ $job->company ? ' — ' . $job->company : '' }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 shrink-0">Compare</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Score breakdown --}}
        @if ($resume->latestAnalysis?->isComplete())
            @php $analysis = $resume->latestAnalysis; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-base font-semibold text-gray-900">Score Breakdown</h3>
                @foreach ([
                    'ATS Score' => $analysis->ats_score,
                    'Readability' => $analysis->readability_score,
                    'Skills Match' => $analysis->skills_score,
                    'Professionalism' => $analysis->professionalism_score,
                ] as $label => $score)
                    @if (!is_null($score))
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700">{{ $label }}</span>
                                <span class="font-medium text-gray-900">{{ $score }}/100</span>
                            </div>
                            <x-score-bar :score="$score" />
                        </div>
                    @endif
                @endforeach

                @if ($analysis->strengths)
                    <div class="mt-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Strengths</h4>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            @foreach ($analysis->strengths as $s)
                                <li>{{ $s }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($analysis->improvements)
                    <div class="mt-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Areas for Improvement</h4>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            @foreach ($analysis->improvements as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Recruiter notes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">My Notes</h3>

            <form method="POST" action="{{ route('recruiter.candidates.notes', $resume) }}" class="space-y-4 mb-6">
                @csrf
                <div class="flex gap-4 items-center">
                    <label class="text-sm font-medium text-gray-700 shrink-0">Rating (1–5)</label>
                    <select name="rating" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <option value="">No rating</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <textarea name="note" rows="3" placeholder="Add a note about this candidate…"
                          class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"></textarea>
                <div class="flex justify-end">
                    <x-primary-button>Save Note</x-primary-button>
                </div>
            </form>

            @forelse ($notes as $note)
                <div class="py-3 border-t border-gray-100">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-2">
                            @if ($note->rating)
                                <span class="text-amber-500">{{ str_repeat('★', $note->rating) }}{{ str_repeat('☆', 5 - $note->rating) }}</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $note->created_at->format('M j, Y H:i') }}</span>
                        </div>
                    </div>
                    @if ($note->note)
                        <p class="mt-1 text-sm text-gray-700">{{ $note->note }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No notes yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
