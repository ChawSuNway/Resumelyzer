<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Upload Resume</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <p class="text-sm text-gray-600 mb-4">Upload a PDF, DOCX, or TXT resume. We'll extract the text, encrypt the original, and run an analysis powered by Google Gemini.</p>
            <p class="text-xs text-gray-500 mb-6">
                Don't have a resume handy?
                <a href="{{ route('sample.resume') }}" class="text-indigo-600 hover:underline" download>Download our sample resume</a>
                to try the analyser.
            </p>

            @if ($errors->any())
                <div class="rounded-md bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm mb-4">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('candidate.resumes.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Resume file</label>
                    <input type="file" name="resume" required
                           accept=".pdf,.doc,.docx,.txt"
                           class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <p class="mt-1 text-xs text-gray-500">PDF, DOC/DOCX, or TXT. Max 10 MB.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Compare against a job posting (optional)</label>
                    <select name="job_posting_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">— No specific job —</option>
                        @foreach ($jobs as $job)
                            <option value="{{ $job->id }}">{{ $job->title }} @if ($job->company) — {{ $job->company }} @endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                    <a href="{{ route('candidate.resumes.index') }}"
                       class="px-5 py-2.5 rounded-md border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 shadow-sm">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-sm">
                        Upload &amp; Analyze
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
