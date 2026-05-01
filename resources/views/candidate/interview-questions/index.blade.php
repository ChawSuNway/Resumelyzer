<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('nav.interview_questions') }}</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-600">
                Generate AI-powered interview questions tailored to any of your resumes. Pick a resume to view its existing questions or generate a new set.
            </p>
        </div>

        @if ($resumes->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <p class="text-sm text-gray-600">You haven't uploaded any resumes yet.</p>
                <a href="{{ route('candidate.resumes.create') }}"
                   class="inline-block mt-4 px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                    Upload a resume
                </a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Resume</th>
                            <th class="text-left px-6 py-3 font-medium">Status</th>
                            <th class="text-left px-6 py-3 font-medium">Last generated</th>
                            <th class="text-right px-6 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($resumes as $resume)
                            @php $set = $resume->latestInterviewQuestionSet; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $resume->original_filename }}</td>
                                <td class="px-6 py-3">
                                    @if ($set)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                            Generated
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            Not generated yet
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-600">
                                    {{ $set ? $set->created_at->diffForHumans() : '—' }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('candidate.interview-questions.show', $resume) }}"
                                       class="text-indigo-600 hover:underline">
                                        {{ $set ? 'View questions' : 'Generate' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
