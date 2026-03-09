<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Task Progress') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Period Filter --}}
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <form method="GET" action="{{ route('reports.task-progress') }}" id="period-form">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                            <input type="date" name="date_from" id="date_from"
                                value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"
                                class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                            <input type="date" name="date_to" id="date_to"
                                value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                                class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            Apply
                        </button>
                    </div>

                    {{-- Quick period buttons --}}
                    <div class="flex flex-wrap gap-2 mt-4">
                        @php
                            $quick = [
                                'This Month'   => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
                                'Last Month'   => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
                                'Last 3 Months'=> [now()->subMonths(3)->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
                                'This Year'    => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
                                'All Time'     => ['2000-01-01', now()->format('Y-m-d')],
                            ];
                        @endphp
                        @foreach ($quick as $label => [$from, $to])
                            @php
                                $isActive = request('date_from') === $from && request('date_to') === $to;
                            @endphp
                            <a href="{{ route('reports.task-progress', ['date_from' => $from, 'date_to' => $to]) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition
                                    {{ $isActive
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-indigo-400 hover:text-indigo-600' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @php
                    $cards = [
                        ['label' => 'Projects', 'value' => $summary['projects'], 'color' => 'indigo', 'icon' => 'M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776'],
                        ['label' => 'Total Tasks', 'value' => $summary['total_tasks'], 'color' => 'blue', 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
                        ['label' => 'Done', 'value' => $summary['done'], 'color' => 'green', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'In Progress', 'value' => $summary['in_progress'], 'color' => 'yellow', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Pending', 'value' => $summary['pending'], 'color' => 'gray', 'icon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ];
                @endphp
                @foreach ($cards as $card)
                    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4 border-l-4
                        @if($card['color'] === 'indigo') border-indigo-500
                        @elseif($card['color'] === 'blue') border-blue-500
                        @elseif($card['color'] === 'green') border-green-500
                        @elseif($card['color'] === 'yellow') border-yellow-500
                        @else border-gray-400 @endif">
                        <div class="p-2 rounded-lg
                            @if($card['color'] === 'indigo') bg-indigo-50 text-indigo-600
                            @elseif($card['color'] === 'blue') bg-blue-50 text-blue-600
                            @elseif($card['color'] === 'green') bg-green-50 text-green-600
                            @elseif($card['color'] === 'yellow') bg-yellow-50 text-yellow-600
                            @else bg-gray-100 text-gray-500 @endif">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ $card['label'] }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Projects Table --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-800">Tasks by Project</h3>
                    <span class="text-xs text-gray-400">
                        {{ $dateFrom->format('d M Y') }} — {{ $dateTo->format('d M Y') }}
                    </span>
                </div>

                @if ($projects->isEmpty())
                    <div class="py-16 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        <p class="font-medium">No tasks found for this period</p>
                        <p class="text-sm mt-1">Try selecting a different date range</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Client</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-green-600 uppercase tracking-wider">Done</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-yellow-600 uppercase tracking-wider">In Progress</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" x-data="{ open: null }">
                            @foreach ($projects as $index => $project)
                                @php $pct = $project['total'] > 0 ? round($project['done'] / $project['total'] * 100) : 0; @endphp
                                {{-- Project Row --}}
                                <tr class="hover:bg-gray-50 cursor-pointer transition-colors"
                                    @click="open = (open === {{ $index }}) ? null : {{ $index }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            {{-- Expand chevron --}}
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-200"
                                                :class="open === {{ $index }} ? 'rotate-90' : ''"
                                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('projects.show', $project['id']) }}"
                                                        class="font-semibold text-gray-800 hover:text-indigo-600 transition-colors"
                                                        @click.stop>
                                                        {{ $project['name'] }}
                                                    </a>
                                                    @if ($project['domain'])
                                                        <a href="{{ Str::startsWith($project['domain'], 'http') ? $project['domain'] : 'https://' . $project['domain'] }}"
                                                            target="_blank"
                                                            class="text-gray-400 hover:text-indigo-500 transition-colors"
                                                            title="Open website"
                                                            @click.stop>
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                            </svg>
                                                        </a>
                                                    @endif
                                                </div>
                                                @if ($project['domain'])
                                                    <span class="text-xs text-gray-400">{{ $project['domain'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 hidden sm:table-cell">
                                        <span class="text-sm text-gray-500">{{ $project['client'] ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                            {{ $project['done'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                            {{ $project['in_progress'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                            {{ $project['pending'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                                            {{ $project['total'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2 min-w-[90px]">
                                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full transition-all duration-500
                                                    @if($pct === 100) bg-green-500
                                                    @elseif($pct >= 50) bg-indigo-500
                                                    @else bg-yellow-400 @endif"
                                                    style="width: {{ $pct }}%">
                                                </div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-500 w-8 text-right">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expanded Task List --}}
                                <tr x-show="open === {{ $index }}" x-cloak>
                                    <td colspan="7" class="px-0 py-0 bg-gray-50">
                                        <div class="border-t border-b border-gray-100">
                                            <table class="min-w-full">
                                                <thead>
                                                    <tr class="bg-gray-100/70">
                                                        <th class="pl-16 pr-4 py-2 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Task</th>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Type</th>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Updated</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach ($project['tasks'] as $task)
                                                        <tr class="hover:bg-white transition-colors">
                                                            <td class="pl-16 pr-4 py-3">
                                                                <a href="{{ route('tasks.show', $task->id) }}"
                                                                    class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">
                                                                    {{ $task->title }}
                                                                </a>
                                                                @if ($task->description)
                                                                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $task->description }}</p>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 hidden md:table-cell">
                                                                <span class="text-xs text-gray-400 capitalize">{{ $task->pricing_type ?? '—' }}</span>
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                @if ($task->status === 'done')
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                                        Done
                                                                    </span>
                                                                @elseif ($task->status === 'in_progress')
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                        In Progress
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                                        Pending
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-right hidden sm:table-cell">
                                                                <span class="text-xs text-gray-400">{{ $task->updated_at->format('d M Y') }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
