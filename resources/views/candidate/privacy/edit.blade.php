<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Privacy Settings</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Privacy preferences --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Data Sharing Preferences</h3>

            <form method="POST" action="{{ route('candidate.privacy.update') }}" class="space-y-5">
                @csrf @method('PATCH')

                <div class="flex items-start gap-4 p-4 rounded-lg border border-gray-100 hover:border-indigo-100 transition">
                    <div class="flex items-center mt-0.5">
                        <input type="checkbox" id="allow_recruiter_access" name="allow_recruiter_access" value="1"
                               class="rounded border-gray-300 text-indigo-600"
                               {{ $user->allow_recruiter_access ? 'checked' : '' }} />
                    </div>
                    <div>
                        <label for="allow_recruiter_access" class="text-sm font-medium text-gray-800 cursor-pointer">Share resumes with recruiters</label>
                        <p class="mt-1 text-sm text-gray-500">When enabled, recruiters can browse and view your resume and analysis results. Turning this off will hide all your resumes from recruiters immediately.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 rounded-lg border border-gray-100 hover:border-indigo-100 transition">
                    <div class="flex items-center mt-0.5">
                        <input type="checkbox" id="store_resumes" name="store_resumes" value="1"
                               class="rounded border-gray-300 text-indigo-600"
                               {{ $user->store_resumes ? 'checked' : '' }} />
                    </div>
                    <div>
                        <label for="store_resumes" class="text-sm font-medium text-gray-800 cursor-pointer">Keep original resume files</label>
                        <p class="mt-1 text-sm text-gray-500">When enabled, your encrypted resume files are retained for downloading. Disabling this preference takes effect on new uploads; existing files are not deleted immediately.</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>Save Preferences</x-primary-button>
                </div>
            </form>
        </div>

        {{-- Data purge --}}
        <div class="bg-white rounded-xl shadow-sm border border-rose-100 p-8">
            <h3 class="text-base font-semibold text-rose-700 mb-2">Delete All My Data</h3>
            <p class="text-sm text-gray-600 mb-4">This will permanently delete all your uploaded resumes and analysis results. This action cannot be undone.</p>

            <form method="POST" action="{{ route('candidate.privacy.purge') }}"
                  onsubmit="return confirm('Are you sure? This will permanently delete all your resume data and cannot be undone.')">
                @csrf @method('DELETE')
                <div class="flex items-center gap-3 mb-4">
                    <input type="checkbox" id="confirm" name="confirm" value="1" required class="rounded border-gray-300 text-rose-600" />
                    <label for="confirm" class="text-sm text-gray-700">I understand this is permanent and cannot be undone</label>
                </div>
                <button type="submit" class="px-4 py-2 rounded-md bg-rose-600 text-white text-sm font-medium hover:bg-rose-700">Delete All My Resumes</button>
            </form>
        </div>
    </div>
</x-app-layout>
