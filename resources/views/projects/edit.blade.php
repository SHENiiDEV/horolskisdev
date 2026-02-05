<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Edit Project: {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    <div class="mb-10 flex justify-between items-center border-b border-gray-100 pb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Project</h3>
                            <p class="text-sm text-gray-500 mt-2">Update project details and settings.</p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                            {{ $project->name }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Client -->
                        <div>
                            <label for="client_id" class="block text-sm font-semibold text-gray-700 mb-2">Assign
                                Client</label>
                            <div class="relative">
                                <select id="client_id" name="client_id" required
                                    class="block w-full rounded-lg border-gray-300 py-3 pl-4 pr-10 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                    <option value="">Select a Client...</option>
                                    @foreach($clients as $id => $name)
                                        <option value="{{ $id }}" {{ (old('client_id', $project->client_id) == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Name -->
                            <div class="col-span-1">
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Project
                                    Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}"
                                    required
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                            </div>

                            <!-- Domain -->
                            <div class="col-span-1">
                                <label for="domain" class="block text-sm font-semibold text-gray-700 mb-2">Domain /
                                    URL</label>
                                <input type="text" name="domain" id="domain"
                                    value="{{ old('domain', $project->domain) }}"
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50"
                                    placeholder="https://example.com">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Project
                                    Status</label>
                                <select id="status" name="status"
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                    <option value="development" {{ old('status', $project->status) == 'development' ? 'selected' : '' }}>Development</option>
                                    <option value="live" {{ old('status', $project->status) == 'live' ? 'selected' : '' }}>Live</option>
                                    <option value="paused" {{ old('status', $project->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                                </select>
                            </div>

                            <!-- Pricing -->
                            <div>
                                <label for="pricing_type" class="block text-sm font-semibold text-gray-700 mb-2">Billing
                                    Model</label>
                                <select id="pricing_type" name="pricing_type"
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                    <option value="fixed" {{ old('pricing_type', $project->pricing_type) == 'fixed' ? 'selected' : '' }}>Fixed Price</option>
                                    <option value="hourly" {{ old('pricing_type', $project->pricing_type) == 'hourly' ? 'selected' : '' }}>Hourly Rate</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-6 flex items-center justify-end gap-x-4 border-t border-gray-50">
                            <a href="{{ route('projects.show', $project) }}"
                                class="text-sm font-semibold leading-6 text-gray-500 hover:text-gray-900 px-4 py-2 transition-colors">Cancel</a>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all transform hover:scale-[1.02]">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>