<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('candidate.interview-questions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ __('nav.interview_questions') }}</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $resume->original_filename }}</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        @php $job = $resume->latestAnalysis?->jobPosting; @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">AI-Powered Interview Questions</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Generate practice interview questions tailored to this resume{{ $job ? ' and the "'.$job->title.'" role' : '' }}.
                    </p>
                </div>
                <form method="POST" action="{{ route('candidate.interview-questions.store', $resume) }}">
                    @csrf
                    @if ($job)
                        <input type="hidden" name="job_posting_id" value="{{ $job->id }}" />
                    @endif
                    <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 whitespace-nowrap">
                        {{ $interviewSet ? 'Regenerate' : 'Generate questions' }}
                    </button>
                </form>
            </div>
        </div>

        @if ($interviewSet)
            <x-interview-questions :set="$interviewSet" />
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <p class="text-sm text-gray-600">No questions generated yet. Click <span class="font-medium">Generate questions</span> to create your first set.</p>
            </div>
        @endif
    </div>
</x-app-layout>
