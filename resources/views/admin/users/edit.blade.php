<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Users</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit {{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 space-y-5">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf @method('PATCH')

                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                  :value="old('name', $user->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                  :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="candidate" {{ old('role', $user->role) === 'candidate' ? 'selected' : '' }}>Candidate</option>
                        <option value="recruiter" {{ old('role', $user->role) === 'recruiter' ? 'selected' : '' }}>Recruiter</option>
                        <option value="admin"     {{ old('role', $user->role) === 'admin'     ? 'selected' : '' }}>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="company" value="Company (optional)" />
                    <x-text-input id="company" name="company" type="text" class="mt-1 block w-full"
                                  :value="old('company', $user->company)" />
                    <x-input-error :messages="$errors->get('company')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600"
                           {{ old('is_active', $user->is_active) ? 'checked' : '' }} />
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Cancel</a>
                    <x-primary-button>Save Changes</x-primary-button>
                </div>
            </form>
        </div>

        {{-- Quick toggle --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Account Status</h3>
            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                @csrf @method('PATCH')
                <button type="submit" class="px-4 py-2 rounded-md text-sm font-medium
                    {{ $user->is_active ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                    {{ $user->is_active ? 'Deactivate this user' : 'Reactivate this user' }}
                </button>
            </form>
        </div>

        {{-- Reset Password --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Reset Password</h3>
            <p class="text-xs text-gray-500 mb-4">Set a new password for this user. They will need to use it on their next login.</p>

            @if ($errors->hasBag('resetPassword'))
                <div class="rounded-md bg-rose-50 border border-rose-200 p-3 text-rose-800 text-sm mb-4">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->getBag('resetPassword')->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.password', $user) }}" class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <x-input-label for="password" value="New Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                  placeholder="Min 8 characters" autocomplete="new-password" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                  class="mt-1 block w-full" placeholder="Repeat new password" autocomplete="new-password" />
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 rounded-md bg-rose-600 text-white text-sm font-medium hover:bg-rose-700">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
