<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Task Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tasks.edit', $task) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit Task
                </a>
                <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                    onsubmit="return confirm('Are you sure you want to delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Task Info -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Information</h3>
                            <dl class="mt-4 space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $task->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
                                        {{ $task->description ?? 'No description provided.' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Project</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ route('projects.show', $task->project) }}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            {{ $task->project->name }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span
                                            class="inline-flex items-center rounded-md bg-{{ $task->status === 'done' ? 'green' : ($task->status === 'in_progress' ? 'yellow' : 'gray') }}-50 px-2 py-1 text-xs font-medium text-{{ $task->status === 'done' ? 'green' : ($task->status === 'in_progress' ? 'yellow' : 'gray') }}-700 ring-1 ring-inset ring-{{ $task->status === 'done' ? 'green' : ($task->status === 'in_progress' ? 'yellow' : 'gray') }}-600/20">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Financials -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Financials</h3>
                            <dl class="mt-4 space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Pricing Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($task->pricing_type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Rate / Amount</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $task->currency === 'EUR' ? '€' : '$' }}{{ number_format($task->rate, 2) }}
                                        @if($task->pricing_type === 'hourly') / hr @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Estimated Hours</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $task->estimated_hours ?? 0 }} hrs</dd>
                                </div>
                                @if($task->pricing_type === 'hourly')
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Estimated Total</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $task->currency === 'EUR' ? '€' : '$' }}{{ number_format(($task->rate ?? 0) * ($task->estimated_hours ?? 0), 2) }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>