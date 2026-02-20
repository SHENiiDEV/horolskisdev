<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Projects') }}
            </h2>

            <div class="flex w-full sm:w-auto flex-col sm:flex-row items-center gap-4">
                <form action="{{ route('projects.index') }}" method="GET"
                    class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <!-- Client Filter (Only visible if the user isn't a restricted client) -->
                    @if(isset($clients) && $clients->isNotEmpty())
                        <div class="relative w-full sm:w-48 shrink-0">
                            <select name="client_id" onchange="this.form.submit()"
                                class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="relative flex-1 sm:w-64 min-w-[200px]">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search projects..."
                            class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- Only show Apply button on mobile visually, on desktop people hit Enter or change select -->
                    <button type="submit"
                        class="sm:hidden w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Apply Filters
                    </button>
                    <!-- Hidden submit button for desktop Enter key behavior on search -->
                    <button type="submit" class="hidden"></button>
                </form>

                <a href="{{ route('projects.create') }}"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 shrink-0 text-center w-full sm:w-auto mt-2 sm:mt-0">
                    Add Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($projects as $project)
            <div
                class="relative bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 rounded-xl hover:-translate-y-1 hover:shadow-lg transition-all duration-200 flex flex-col">
                <div class="px-6 py-5 flex-1">
                    <div class="flex justify-between items-start mb-4 gap-4">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 line-clamp-2">
                            <a href="{{ route('projects.show', $project) }}" class="focus:outline-none">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{ $project->name }}
                            </a>
                        </h3>

                        @php
                            $statusColors = [
                                'live' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'development' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                'paused' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                            ];
                            $colorClass = $statusColors[$project->status] ?? 'bg-gray-50 text-gray-600 ring-gray-500/10';
                        @endphp
                        <span
                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $colorClass }} shrink-0">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="mr-2 h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="truncate" title="{{ $project->client->name }}">{{ $project->client->name }}</span>
                        </div>

                        @if($project->domain)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="mr-2 h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <a href="https://{{ $project->domain }}" target="_blank"
                                    class="truncate text-indigo-600 hover:text-indigo-500 relative z-10"
                                    title="{{ $project->domain }}">
                                    {{ $project->domain }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-between items-center">
                    <div class="text-sm font-medium text-gray-500">
                        {{ $project->tasks_count }} {{ Str::plural('Task', $project->tasks_count) }}
                    </div>
                    <a href="{{ route('projects.show', $project) }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900 relative z-10 block">
                        View details <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 px-6 py-14 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
                    </svg>
                    <h3 class="mt-4 text-sm font-semibold text-gray-900">No projects found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if(request('search'))
                            We couldn't find anything matching "{{ request('search') }}". Please try a different search term.
                        @else
                            Get started by creating a new project.
                        @endif
                    </p>
                    @if(!request('search'))
                        <div class="mt-6">
                            <a href="{{ route('projects.create') }}"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 relative z-10">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                </svg>
                                Add Project
                            </a>
                        </div>
                    @else
                        <div class="mt-6">
                            <a href="{{ route('projects.index') }}"
                                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 relative z-10">
                                Clear search
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endforelse
    </div>
    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</x-app-layout>