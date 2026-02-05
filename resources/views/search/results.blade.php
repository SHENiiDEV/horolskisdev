<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Search Results') }} for "{{ $query }}"
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Tasks -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tasks</h3>
                    @if($tasks->isEmpty())
                        <p class="text-gray-500">No tasks found.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($tasks as $task)
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('tasks.show', $task) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $task->title }}
                                            </a>
                                            <p class="text-sm text-gray-500">{{ Str::limit($task->description, 100) }}</p>
                                        </div>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $task->status === 'completed' ? 'green' : 'yellow' }}-100 text-{{ $task->status === 'completed' ? 'green' : 'yellow' }}-800">
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Projects -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Projects</h3>
                    @if($projects->isEmpty())
                        <p class="text-gray-500">No projects found.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($projects as $project)
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('projects.show', $project) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $project->name }}
                                            </a>
                                            <p class="text-sm text-gray-500">{{ Str::limit($project->description, 100) }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Clients -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Clients</h3>
                    @if($clients->isEmpty())
                        <p class="text-gray-500">No clients found.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($clients as $client)
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('clients.show', $client) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $client->name }}
                                            </a>
                                            <p class="text-sm text-gray-500">{{ $client->email }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Invoices -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoices</h3>
                    @if($invoices->isEmpty())
                        <p class="text-gray-500">No invoices found.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($invoices as $invoice)
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                Invoice #{{ $invoice->public_id ?? $invoice->id }}
                                            </a>
                                            <p class="text-sm text-gray-500">
                                                {{ $invoice->client->name ?? 'Unknown Client' }} - 
                                                <span class="font-semibold">{{ number_format($invoice->amount, 2) }}</span>
                                            </p>
                                            @if($invoice->notes)
                                                <p class="text-xs text-gray-400 mt-1">{{ Str::limit($invoice->notes, 100) }}</p>
                                            @endif
                                        </div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $invoice->status === 'paid' ? 'green' : ($invoice->status === 'sent' ? 'blue' : 'gray') }}-100 text-{{ $invoice->status === 'paid' ? 'green' : ($invoice->status === 'sent' ? 'blue' : 'gray') }}-800">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>