<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Settings</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf @method('PATCH')

            @if ($errors->any())
                <div class="rounded-md bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Scoring weights --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-base font-semibold text-gray-900">Scoring Weights</h3>
                <p class="text-sm text-gray-500">Each weight contributes to the overall resume score. They don't need to sum to 100, but it's recommended.</p>
                <div class="grid grid-cols-2 gap-4">
                    @foreach (['ats' => 'ATS Score', 'readability' => 'Readability', 'skills' => 'Skills Match', 'professionalism' => 'Professionalism'] as $key => $label)
                    <div>
                        <label for="weight_{{ $key }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <div class="mt-1 flex items-center gap-2">
                            <x-text-input id="weight_{{ $key }}" name="weight_{{ $key }}" type="number"
                                          min="0" max="100" step="1" class="block w-24"
                                          :value="old('weight_' . $key, $settings['scoring_weights'][$key] ?? 0)" />
                            <span class="text-sm text-gray-500">%</span>
                        </div>
                        <x-input-error :messages="$errors->get('weight_' . $key)" class="mt-1" />
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Mandatory sections --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-base font-semibold text-gray-900">Mandatory Resume Sections</h3>
                <p class="text-sm text-gray-500">Comma or newline-separated section names. Resumes missing these will lose points.</p>
                <textarea id="mandatory_sections" name="mandatory_sections" rows="4"
                          class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono">{{ old('mandatory_sections', implode("\n", $settings['mandatory_sections'] ?? [])) }}</textarea>
                <x-input-error :messages="$errors->get('mandatory_sections')" class="mt-1" />
            </div>

            {{-- Generic terms blocklist --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-base font-semibold text-gray-900">Generic Terms Blocklist</h3>
                <p class="text-sm text-gray-500">Overused or generic phrases to flag. Comma or newline-separated.</p>
                <textarea id="generic_terms_blocklist" name="generic_terms_blocklist" rows="4"
                          class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono">{{ old('generic_terms_blocklist', implode("\n", $settings['generic_terms_blocklist'] ?? [])) }}</textarea>
                <x-input-error :messages="$errors->get('generic_terms_blocklist')" class="mt-1" />
            </div>

            {{-- ATS keyword library --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-base font-semibold text-gray-900">ATS Keyword Library</h3>
                <p class="text-sm text-gray-500">JSON object mapping categories to arrays of keywords. e.g. <code class="bg-gray-100 px-1 rounded text-xs">{{"{"}} "engineering": ["python", "sql"] {{ "}" }}</code></p>
                <textarea id="ats_keyword_library" name="ats_keyword_library" rows="8"
                          class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono">{{ old('ats_keyword_library', json_encode($settings['ats_keyword_library'] ?? [], JSON_PRETTY_PRINT)) }}</textarea>
                <x-input-error :messages="$errors->get('ats_keyword_library')" class="mt-1" />
            </div>

            {{-- Retention --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-base font-semibold text-gray-900">Data Retention</h3>
                <p class="text-sm text-gray-500">Number of days to retain resume data before automatic deletion.</p>
                <div class="flex items-center gap-2">
                    <x-text-input id="retention_days" name="retention_days" type="number" min="1" max="365"
                                  class="block w-32" :value="old('retention_days', $settings['retention_days'] ?? 30)" />
                    <span class="text-sm text-gray-500">days</span>
                </div>
                <x-input-error :messages="$errors->get('retention_days')" class="mt-1" />
            </div>

            <div class="flex justify-end">
                <x-primary-button>Save Settings</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
