<div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition group" draggable="true"
    x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $task->id }}'); $event.dataTransfer.effectAllowed = 'move';">

    <div class="flex justify-between items-start mb-2">
        <h5 class="text-sm font-semibold text-gray-900 leading-tight">
            <a href="{{ route('tasks.edit', $task) }}" class="hover:text-indigo-600">{{ $task->title }}</a>
        </h5>
        <div class="flex space-x-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition">
            {{-- Edit --}}
            <a href="{{ route('tasks.edit', $task) }}" class="text-gray-400 hover:text-indigo-500 p-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </a>

            {{-- Delete --}}
            <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                onsubmit="return confirm('Delete this task?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-gray-400 hover:text-red-500 p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    @if($task->description)
        <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $task->description }}</p>
    @endif

    <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-50">
        {{-- Timer / Action Check --}}
        <div>
            @php
                // Check if this specific task is the one currently running
                $isActive = isset($activeTimer) && $activeTimer->task_id === $task->id;
            @endphp

            @if($isActive)
                <form action="{{ route('timer.stop') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center text-xs font-medium text-yellow-600 hover:text-yellow-700 bg-yellow-50 px-2 py-1 rounded-md border border-yellow-200">
                        <span class="relative flex h-2 w-2 mr-1.5">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                        </span>
                        Stop Timer
                    </button>
                </form>
            @elseif($task->status === 'done')
                <span
                    class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Completed</span>
            @else
                <form action="{{ route('tasks.start', $task) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center text-xs font-medium text-gray-500 hover:text-indigo-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $task->status === 'in_progress' ? 'Resume Timer' : 'Start Timer' }}
                    </button>
                </form>
            @endif
        </div>

        {{-- Rate / Cost --}}
        <div class="text-xs text-gray-400 font-mono">
            @if($task->pricing_type === 'fixed')
                {{ $task->currency }}{{ $task->rate }}
            @else
                {{ $task->currency }}{{ $task->rate }}/hr
            @endif
        </div>
    </div>
</div>