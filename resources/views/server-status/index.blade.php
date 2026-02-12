<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Server Status') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($projects as $project)
                    <div
                        class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
                        <!-- Card Header -->
                        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('projects.show', $project) }}" class="focus:outline-none group">
                                    <div class="flex items-center gap-2">
                                        <h3
                                            class="text-base font-semibold leading-6 text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $project->name }}
                                        </h3>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-gray-500">{{ $project->domain }}</p>
                                </a>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                @if($project->last_check_status === 'up')
                                    <span
                                        class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">UP</span>
                                @elseif($project->last_check_status === 'down')
                                    <span
                                        class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">DOWN</span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">PENDING</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body: Uptime Graph -->
                        <div class="px-6 py-5">
                            <div class="mb-2 flex justify-between items-end">
                                <span class="text-xs font-medium text-gray-500">Uptime (Last hour)</span>
                                @if($project->last_check_time)
                                    <span class="text-xs text-gray-400" title="{{ $project->last_check_time }}">
                                        Checked {{ $project->last_check_time->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                            {{-- Graph --}}
                            <div class="flex gap-px h-10 bg-gray-50 p-px rounded-md border border-gray-200 overflow-hidden">
                                @foreach($project->uptimeChecks as $check)
                                    <div class="flex-1 relative group {{ $check->status === 'up' ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600' }}"
                                        title="{{ $check->created_at->format('H:i') }} | Status: {{ strtoupper($check->status) }} | Response: {{ $check->response_time_ms ?? 'N/A' }}ms">
                                    </div>
                                @endforeach
                                @if($project->uptimeChecks->isEmpty())
                                    <div class="w-full flex items-center justify-center text-xs text-gray-400">
                                        No data
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer: Stats -->
                        <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                            <dl class="grid grid-cols-3 gap-x-4 text-center">
                                <div>
                                    <dt class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Avg</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                                        {{ $project->uptime_checks_avg_response_time_ms ? round($project->uptime_checks_avg_response_time_ms) . 'ms' : '-' }}
                                    </dd>
                                </div>
                                <div class="border-l border-gray-200 pl-4 border-r pr-4">
                                    <dt class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Min</dt>
                                    <dd class="mt-1 text-sm font-semibold text-green-600">
                                        {{ $project->uptime_checks_min_response_time_ms ? $project->uptime_checks_min_response_time_ms . 'ms' : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Max</dt>
                                    <dd class="mt-1 text-sm font-semibold text-indigo-600">
                                        {{ $project->uptime_checks_max_response_time_ms ? $project->uptime_checks_max_response_time_ms . 'ms' : '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-12 px-4 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No monitored servers</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by enabling monitoring on your projects.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>