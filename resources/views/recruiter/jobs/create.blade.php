<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('recruiter.jobs.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Job Postings</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Job Posting</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 space-y-5">
            @if ($errors->any())
                <div class="rounded-md bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('recruiter.jobs.store') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="title" value="Job Title" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="company" value="Company (optional)" />
                        <x-text-input id="company" name="company" type="text" class="mt-1 block w-full" :value="old('company')" />
                        <x-input-error :messages="$errors->get('company')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="location" value="Location (optional)" />
                        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location')" placeholder="Remote, New York, etc." />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="description" value="Job Description" />
                    <textarea id="description" name="description" rows="8" required
                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                              placeholder="Paste the full job description here…">{{ old('description') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">The AI uses this text to score candidate resumes against this posting.</p>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="keywords_raw" value="Keywords (optional)" />
                    <textarea id="keywords_raw" name="keywords_raw" rows="3"
                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                              placeholder="Python, SQL, AWS, 5+ years experience…">{{ old('keywords_raw') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Comma or newline-separated. Used to boost ATS scoring accuracy.</p>
                    <x-input-error :messages="$errors->get('keywords_raw')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600"
                           {{ old('is_active', true) ? 'checked' : '' }} />
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active (visible to candidates)</label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('recruiter.jobs.index') }}" class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Cancel</a>
                    <x-primary-button>Create Job</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
