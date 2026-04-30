<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">+ New User</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search name or email…"
                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
            <select name="role" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                <option value="">All roles</option>
                <option value="admin"     {{ $role === 'admin'     ? 'selected' : '' }}>Admin</option>
                <option value="recruiter" {{ $role === 'recruiter' ? 'selected' : '' }}>Recruiter</option>
                <option value="candidate" {{ $role === 'candidate' ? 'selected' : '' }}>Candidate</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-md bg-gray-800 text-white text-sm font-medium hover:bg-gray-900">Filter</button>
            @if ($search || $role)
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Name</th>
                        <th class="text-left px-6 py-3 font-medium">Email</th>
                        <th class="text-left px-6 py-3 font-medium">Role</th>
                        <th class="text-left px-6 py-3 font-medium">Company</th>
                        <th class="text-left px-6 py-3 font-medium">Status</th>
                        <th class="text-left px-6 py-3 font-medium">Joined</th>
                        <th class="text-right px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($user->role === 'admin') bg-rose-100 text-rose-700
                                @elseif ($user->role === 'recruiter') bg-violet-100 text-violet-700
                                @else bg-sky-100 text-sky-700 @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $user->company ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $user->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-3 text-right space-x-3">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="{{ $user->is_active ? 'text-amber-600' : 'text-emerald-600' }} hover:underline">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</x-app-layout>
