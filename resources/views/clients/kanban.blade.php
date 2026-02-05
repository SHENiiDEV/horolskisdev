<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $client->name }} Kanban Board
            </h2>
            <div class="flex space-x-2">
                 <a href="{{ route('clients.show', $client) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 transition">
                    &larr; Back to Client
                </a>
                <a href="{{ route('tasks.create') }}?client_id={{ $client->id }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Create Task
                </a>
            </div>
        </div>
    </x-slot>

    <div class="h-[calc(100vh-12rem)] overflow-x-auto overflow-y-hidden" x-data="{
        updateTaskStatus(taskId, status) {
            fetch(`/tasks/${taskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Task updated:', data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update task status');
            });
        }
    }">
        <div class="flex h-full gap-6 pb-4 min-w-[1024px]">
            @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'done' => 'Done', 'paid' => 'Paid'] as $statusKey => $statusLabel)
                <div class="flex-1 flex flex-col bg-gray-50 rounded-lg border border-gray-200 h-full" 
                    @if($statusKey !== 'paid')
                    x-on:dragover.prevent
                    x-on:drop="
                            let taskId = $event.dataTransfer.getData('text/plain');
                            let card = document.getElementById('task-' + taskId);
                            if(card) {
                                $el.querySelector('.task-container').appendChild(card);
                                updateTaskStatus(taskId, '{{ $statusKey }}');
                            }
                         "
                    @endif
                    >
                    <!-- Column Header -->
                    <div class="p-3 border-b border-gray-200 bg-gray-100/50 flex justify-between items-center rounded-t-lg">
                        <h3 class="font-semibold text-sm text-gray-700 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full 
                                    {{ $statusKey === 'pending' ? 'bg-yellow-400' : '' }}
                                    {{ $statusKey === 'in_progress' ? 'bg-blue-500' : '' }}
                                    {{ $statusKey === 'done' ? 'bg-green-500' : '' }}
                                    {{ $statusKey === 'paid' ? 'bg-purple-500' : '' }}
                                "></span>
                            {{ $statusLabel }}
                        </h3>
                        <span class="bg-gray-200 text-gray-600 py-0.5 px-2 rounded-full text-xs font-medium">
                            @if($statusKey === 'paid')
                                {{ $tasks->filter(fn($t) => $t->invoice && $t->invoice->status === 'paid')->count() }}
                            @elseif($statusKey === 'done')
                                {{ $tasks->filter(fn($t) => $t->status === 'done' && (!$t->invoice || $t->invoice->status !== 'paid'))->count() }}
                            @else
                                {{ $tasks->where('status', $statusKey)->count() }}
                            @endif
                        </span>
                    </div>

                    <!-- Tasks Container -->
                    <div class="task-container flex-1 overflow-y-auto p-3 space-y-3 min-h-0">
                        @php
                            $filteredTasks = collect();
                            if ($statusKey === 'paid') {
                                $filteredTasks = $tasks->filter(fn($t) => $t->invoice && $t->invoice->status === 'paid');
                            } elseif ($statusKey === 'done') {
                                $filteredTasks = $tasks->filter(fn($t) => $t->status === 'done' && (!$t->invoice || $t->invoice->status !== 'paid'));
                            } else {
                                $filteredTasks = $tasks->where('status', $statusKey);
                            }
                        @endphp

                        @foreach($filteredTasks as $task)
                            <div id="task-{{ $task->id }}" 
                                @if($statusKey !== 'paid') draggable="true" class="cursor-move group"
                                x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $task->id }}'); $event.dataTransfer.effectAllowed = 'move';"
                                @else class="opacity-75" @endif
                                >
                                @include('projects.partials.task-card', ['task' => $task, 'showProject' => true])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>