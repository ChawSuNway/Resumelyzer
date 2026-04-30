<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Users</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create User</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
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

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">— Select role —</option>
                        <option value="candidate" {{ old('role') === 'candidate' ? 'selected' : '' }}>Candidate</option>
                        <option value="recruiter" {{ old('role') === 'recruiter' ? 'selected' : '' }}>Recruiter</option>
                        <option value="admin"     {{ old('role') === 'admin'     ? 'selected' : '' }}>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="company" value="Company (optional)" />
                    <x-text-input id="company" name="company" type="text" class="mt-1 block w-full" :value="old('company')" />
                    <x-input-error :messages="$errors->get('company')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600"
                           {{ old('is_active', '1') ? 'checked' : '' }} />
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Cancel</a>
                    <x-primary-button>Create User</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
