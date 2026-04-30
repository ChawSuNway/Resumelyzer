<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ([
                ['label' => 'Total Users',      'value' => $stats['users'],      'color' => 'indigo'],
                ['label' => 'Candidates',        'value' => $stats['candidates'], 'color' => 'emerald'],
                ['label' => 'Recruiters',        'value' => $stats['recruiters'], 'color' => 'amber'],
                ['label' => 'Admins',            'value' => $stats['admins'],     'color' => 'rose'],
                ['label' => 'Resumes',           'value' => $stats['resumes'],    'color' => 'violet'],
                ['label' => 'Analyses',          'value' => $stats['analyses'],   'color' => 'cyan'],
                ['label' => 'Job Postings',      'value' => $stats['jobs'],       'color' => 'orange'],
                ['label' => 'Avg. Score',        'value' => $stats['avg_score'],  'color' => 'teal'],
            ] as $stat)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Upload trend --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Resume uploads (last 14 days)</h3>
                @if ($uploadsByDay->isEmpty())
                    <p class="text-sm text-gray-500">No uploads in this period.</p>
                @else
                    <div class="space-y-2">
                        @php $max = $uploadsByDay->max('count') ?: 1; @endphp
                        @foreach ($uploadsByDay as $day)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-20 text-gray-500 shrink-0">{{ \Carbon\Carbon::parse($day->day)->format('M j') }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-3">
                                <div class="bg-indigo-500 h-3 rounded-full" style="width: {{ round($day->count / $max * 100) }}%"></div>
                            </div>
                            <span class="w-6 text-right text-gray-700 font-medium">{{ $day->count }}</span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent activity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Recent activity</h3>
                <div class="space-y-3">
                    @forelse ($recentActivity as $log)
                    <div class="flex justify-between text-sm border-b border-gray-50 pb-2 last:border-0">
                        <div>
                            <span class="font-medium text-gray-700">{{ $log->user?->name ?? 'System' }}</span>
                            <span class="text-gray-500 ml-1">{{ $log->action }}</span>
                        </div>
                        <span class="text-gray-400 shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                        <p class="text-sm text-gray-500">No activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Manage Users</a>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50">Create User</a>
            <a href="{{ route('admin.settings.edit') }}" class="px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50">Settings</a>
        </div>
    </div>
</x-app-layout>
