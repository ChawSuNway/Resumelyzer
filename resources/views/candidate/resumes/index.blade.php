<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Resumes</h2>
            <a href="{{ route('candidate.resumes.create') }}" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">+ Upload</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm mb-4">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">File</th>
                        <th class="text-left px-6 py-3 font-medium">Uploaded</th>
                        <th class="text-left px-6 py-3 font-medium">Score</th>
                        <th class="text-left px-6 py-3 font-medium">Shared</th>
                        <th class="text-right px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($resumes as $resume)
                        <tr>
                            <td class="px-6 py-3">
                                <a href="{{ route('candidate.resumes.show', $resume) }}" class="font-medium text-indigo-600 hover:underline">{{ $resume->original_filename }}</a>
                                <div class="text-xs text-gray-500">{{ strtoupper($resume->extension) }} · {{ number_format($resume->size_bytes / 1024, 1) }} KB</div>
                            </td>
                            <td class="px-6 py-3 text-gray-700">{{ $resume->created_at->format('M j, Y') }}</td>
                            <td class="px-6 py-3">
                                @if ($resume->latestAnalysis?->isComplete())
                                    <span class="font-semibold text-gray-900">{{ $resume->latestAnalysis->overall_score }}/100</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-700">{{ $resume->shared_with_recruiters ? 'Yes' : 'No' }}</td>
                            <td class="px-6 py-3 text-right space-x-2">
                                <a href="{{ route('candidate.resumes.show', $resume) }}" class="text-indigo-600 hover:underline">View</a>
                                <a href="{{ route('candidate.resumes.download', $resume) }}" class="text-gray-700 hover:underline">Download</a>
                                <form method="POST" action="{{ route('candidate.resumes.destroy', $resume) }}" class="inline" onsubmit="return confirm('Permanently delete this resume?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No resumes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
