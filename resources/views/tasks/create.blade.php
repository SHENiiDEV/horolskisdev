<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Add Task to {{ $project->name }}
        </h2>
    </x-slot>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <form method="POST" action="{{ route('tasks.store') }}" class="space-y-12">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <!-- Section 1: Task Information -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Task Information</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Basic details about the task.</p>
                    </div>

                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-4">
                            <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Task
                                Title</label>
                            <div class="mt-2">
                                <input type="text" name="title" id="title"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    required autofocus>
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label for="description"
                                class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                            <div class="mt-2">
                                <textarea id="description" name="description" rows="3"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Workflow -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Workflow</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Current status and progress.</p>
                    </div>

                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                            <div class="mt-2">
                                <select id="status" name="status"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pricing & Estimation -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3" x-data="{ 
                        type: 'hourly', 
                        defaultRate: {{ $project->client->default_hourly_rate ?? 0 }},
                        currency: '{{ $project->client->currency ?? 'EUR' }}'
                     }">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Pricing & Estimation</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Cost and time estimation.</p>
                    </div>

                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="pricing_type" class="block text-sm font-medium leading-6 text-gray-900">Pricing
                                Type</label>
                            <div class="mt-2">
                                <select id="pricing_type" name="pricing_type" x-model="type"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="hourly">Hourly Rate</option>
                                    <option value="fixed">Fixed Price</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="rate" class="block text-sm font-medium leading-6 text-gray-900"
                                x-text="type === 'hourly' ? 'Hourly Rate' : 'Fixed Amount'"></label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm"
                                        x-text="currency === 'EUR' ? '€' : '$'"></span>
                                </div>
                                <input type="number" name="rate" id="rate" step="0.01"
                                    x-bind:value="type === 'hourly' ? defaultRate : ''"
                                    class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="0.00">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 sm:text-sm" x-text="currency"></span>
                                </div>
                            </div>
                            <input type="hidden" name="currency" x-model="currency">
                        </div>

                        <div class="sm:col-span-3">
                            <label for="estimated_hours"
                                class="block text-sm font-medium leading-6 text-gray-900">Estimated Hours</label>
                            <div class="mt-2">
                                <input type="number" name="estimated_hours" id="estimated_hours" step="0.1"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <button type="button" onclick="history.back()"
                        class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save
                        Task</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>