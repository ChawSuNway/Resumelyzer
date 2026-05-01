<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="font-bold text-indigo-600 text-lg tracking-tight">
                        Resumelyzer
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        @if (auth()->user()->isCandidate())
                            <x-nav-link :href="route('candidate.dashboard')" :active="request()->routeIs('candidate.dashboard')">{{ __('nav.dashboard') }}</x-nav-link>
                            <x-nav-link :href="route('candidate.resumes.index')" :active="request()->routeIs('candidate.resumes.*')">{{ __('nav.my_resumes') }}</x-nav-link>
                            <x-nav-link :href="route('candidate.resumes.create')" :active="request()->routeIs('candidate.resumes.create')">{{ __('nav.upload') }}</x-nav-link>
                            <x-nav-link :href="route('candidate.interview-questions.index')" :active="request()->routeIs('candidate.interview-questions.*')">{{ __('nav.interview_questions') }}</x-nav-link>
                            <x-nav-link :href="route('candidate.privacy.edit')" :active="request()->routeIs('candidate.privacy.*')">{{ __('nav.privacy') }}</x-nav-link>
                        @elseif (auth()->user()->isRecruiter())
                            <x-nav-link :href="route('recruiter.dashboard')" :active="request()->routeIs('recruiter.dashboard')">{{ __('nav.dashboard') }}</x-nav-link>
                            <x-nav-link :href="route('recruiter.candidates.index')" :active="request()->routeIs('recruiter.candidates.*')">{{ __('nav.candidates') }}</x-nav-link>
                            <x-nav-link :href="route('recruiter.jobs.index')" :active="request()->routeIs('recruiter.jobs.*')">{{ __('nav.job_postings') }}</x-nav-link>
                        @elseif (auth()->user()->isAdmin())
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">{{ __('nav.dashboard') }}</x-nav-link>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">{{ __('nav.users') }}</x-nav-link>
                            <x-nav-link :href="route('admin.settings.edit')" :active="request()->routeIs('admin.settings.*')">{{ __('nav.settings') }}</x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                @php
                    $locales = ['en' => 'EN', 'my' => 'MY', 'jp' => 'JP'];
                    $currentLocale = app()->getLocale();
                @endphp
                <x-dropdown align="right" width="40">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none transition ease-in-out duration-150">
                            <x-flag :code="$currentLocale" />
                            <span>{{ $locales[$currentLocale] ?? 'EN' }}</span>
                            <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        @foreach ($locales as $code => $label)
                            <a href="{{ route('locale.switch', $code) }}"
                               class="flex items-center gap-2 w-full px-4 py-2 text-start text-sm {{ $code === $currentLocale ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <x-flag :code="$code" />
                                <span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </x-slot>
                </x-dropdown>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <span class="ms-2 px-2 py-0.5 rounded text-xs bg-indigo-50 text-indigo-700 capitalize">{{ Auth::user()->role }}</span>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('nav.profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('nav.log_out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @if (auth()->user()->isCandidate())
                    <x-responsive-nav-link :href="route('candidate.dashboard')">{{ __('nav.dashboard') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('candidate.resumes.index')">{{ __('nav.my_resumes') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('candidate.resumes.create')">{{ __('nav.upload') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('candidate.interview-questions.index')">{{ __('nav.interview_questions') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('candidate.privacy.edit')">{{ __('nav.privacy') }}</x-responsive-nav-link>
                @elseif (auth()->user()->isRecruiter())
                    <x-responsive-nav-link :href="route('recruiter.dashboard')">{{ __('nav.dashboard') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('recruiter.candidates.index')">{{ __('nav.candidates') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('recruiter.jobs.index')">{{ __('nav.job_postings') }}</x-responsive-nav-link>
                @elseif (auth()->user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.dashboard')">{{ __('nav.dashboard') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users.index')">{{ __('nav.users') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.settings.edit')">{{ __('nav.settings') }}</x-responsive-nav-link>
                @endif
            @endauth
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('nav.profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('nav.log_out') }}</x-responsive-nav-link>
                </form>
            </div>
        </div>
        <div class="pt-3 pb-2 border-t border-gray-200">
            <div class="px-4 text-xs uppercase tracking-wider text-gray-400 mb-1">{{ __('nav.language') }}</div>
            <div class="flex gap-2 px-4">
                @foreach (['en' => 'EN', 'my' => 'MY', 'jp' => 'JP'] as $code => $label)
                    <a href="{{ route('locale.switch', $code) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded text-xs font-medium {{ $code === app()->getLocale() ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                        <x-flag :code="$code" />
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>
