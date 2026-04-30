<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Job Postings</h2>
            <a href="{{ route('recruiter.jobs.create') }}" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">+ New Job</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm mb-5">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Title</th>
                        <th class="text-left px-6 py-3 font-medium">Company</th>
                        <th class="text-left px-6 py-3 font-medium">Location</th>
                        <th class="text-left px-6 py-3 font-medium">Status</th>
                        <th class="text-left px-6 py-3 font-medium">Created</th>
                        <th class="text-right px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jobs as $job)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $job->title }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $job->company ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $job->location ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $job->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $job->is_active ? 'Active' : 'Closed' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $job->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-3 text-right space-x-3">
                            <a href="{{ route('recruiter.jobs.edit', $job) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('recruiter.jobs.destroy', $job) }}" class="inline"
                                  onsubmit="return confirm('Delete this job posting?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No job postings yet.
                            <a href="{{ route('recruiter.jobs.create') }}" class="text-indigo-600 hover:underline ml-1">Create one now</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $jobs->links() }}
    </div>
</x-app-layout>
