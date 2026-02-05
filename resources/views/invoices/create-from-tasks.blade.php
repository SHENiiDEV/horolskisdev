<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Generate Invoice for {{ $project->name }}
        </h2>
    </x-slot>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <form method="POST" action="{{ route('invoices.store') }}" class="space-y-12" x-data="{
                    tasks: [
                        @foreach($tasks as $task)
                            {
                                id: {{ $task->id }},
                                cost: {{ $task->calculated_cost }},
                                selected: false
                            },
                        @endforeach
                    ],
                    get total() {
                        return this.tasks.filter(t => t.selected).reduce((sum, t) => sum + t.cost, 0).toFixed(2);
                    }
                }">
                @csrf
                <input type="hidden" name="client_id" value="{{ $project->client_id }}">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="status" value="draft">

                <!-- Display Validation Errors -->
                @if ($errors->any())
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">There were problems with your submission</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul role="list" class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section 1: Invoice Details -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Invoice Details</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Period and description.</p>
                    </div>

                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="period_start" class="block text-sm font-medium leading-6 text-gray-900">Period
                                Start</label>
                            <div class="mt-2">
                                <input type="date" name="period_start"
                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="period_end" class="block text-sm font-medium leading-6 text-gray-900">Period
                                End</label>
                            <div class="mt-2">
                                <input type="date" name="period_end" value="{{ now()->format('Y-m-d') }}"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    required>
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Description /
                                Notes</label>
                            <div class="mt-2">
                                <textarea id="notes" name="notes" rows="3"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">Implementation services for {{ $project->name }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Task Selection -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Billable Tasks</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Select tasks to include in this invoice.</p>
                    </div>

                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="col-span-full">
                            @if($tasks->count() > 0)
                                <div class="space-y-4">
                                    @foreach($tasks as $index => $task)
                                        <div class="relative flex items-start p-4 border rounded-lg hover:bg-gray-50 transition cursor-pointer"
                                             @click="tasks[{{ $index }}].selected = !tasks[{{ $index }}].selected">
                                            <div class="flex h-6 items-center">
                                                <input id="task-{{ $task->id }}" name="task_ids[]" value="{{ $task->id }}" type="checkbox"
                                                       x-model="tasks[{{ $index }}].selected"
                                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 pointer-events-none">
                                            </div>
                                            <div class="ml-3 text-sm leading-6 flex-1">
                                                <label for="task-{{ $task->id }}" class="font-medium text-gray-900 pointer-events-none">{{ $task->title }}</label>
                                                <div class="text-gray-500 pointer-events-none">
                                                    @if($task->pricing_type === 'fixed')
                                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Fixed Price</span>
                                                        {{ $task->currency }} {{ number_format($task->rate, 2) }}
                                                    @else
                                                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">Hourly</span>
                                                        {{ $task->total_hours }} hrs @ {{ $task->currency }} {{ number_format($task->rate, 2) }}/hr = {{ $task->currency }} {{ $task->calculated_cost }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-md bg-yellow-50 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800">No billable tasks found</h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <p>There are no completed tasks that haven't been invoiced yet.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Section 3: Summary -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Summary</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Review total amount.</p>
                    </div>

                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Total
                                Amount</label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span
                                        class="text-gray-500 sm:text-sm">{{ $project->client->currency ?? '€' }}</span>
                                </div>
                                <input type="number" name="amount" id="amount" step="0.01" x-model="total"
                                    class="block w-full rounded-md border-0 py-1.5 pl-12 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-bold text-lg"
                                    readonly>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Calculated automatically based on selected tasks.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <button type="button" onclick="history.back()"
                        class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        :disabled="total <= 0" :class="{'opacity-50 cursor-not-allowed': total <= 0}">
                        Generate Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>