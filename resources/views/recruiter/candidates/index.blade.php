<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Candidates</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, or resume text…"
                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm flex-1 min-w-48" />
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Min score</label>
                <x-text-input name="min_score" type="number" min="0" max="100" class="w-20 text-sm"
                              :value="$minScore" placeholder="0" />
            </div>
            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Filter</button>
            @if ($search || $minScore)
                <a href="{{ route('recruiter.candidates.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Candidate</th>
                        <th class="text-left px-6 py-3 font-medium">File</th>
                        <th class="text-left px-6 py-3 font-medium">Uploaded</th>
                        <th class="text-left px-6 py-3 font-medium">Score</th>
                        <th class="text-right px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($resumes as $resume)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <div class="font-medium text-gray-900">{{ $resume->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $resume->user->email }}</div>
                        </td>
                        <td class="px-6 py-3 text-gray-700">
                            {{ $resume->original_filename }}
                            <div class="text-xs text-gray-400">{{ strtoupper($resume->extension) }}</div>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $resume->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-3">
                            @if ($resume->latestAnalysis?->isComplete())
                                <span class="font-semibold
                                    @if ($resume->latestAnalysis->overall_score >= 80) text-emerald-600
                                    @elseif ($resume->latestAnalysis->overall_score >= 60) text-amber-600
                                    @else text-rose-600 @endif">
                                    {{ $resume->latestAnalysis->overall_score }}/100
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right space-x-3">
                            <a href="{{ route('recruiter.candidates.show', $resume) }}" class="text-indigo-600 hover:underline">View</a>
                            <a href="{{ route('recruiter.candidates.download', $resume) }}" class="text-gray-600 hover:underline">Download</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No shared resumes found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $resumes->links() }}
    </div>
</x-app-layout>
