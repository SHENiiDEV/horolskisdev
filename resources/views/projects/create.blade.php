<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            {{ __('Add Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    <div class="mb-10 text-center">
                        <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Create New Project</h3>
                        <p class="text-sm text-gray-500 mt-2">Initialize a new project, assign a client, and set the
                            pricing structure.</p>
                    </div>

                    <form method="POST" action="{{ route('projects.store') }}" class="space-y-8">
                        @csrf

                        <!-- Client -->
                        <div>
                            <label for="client_id" class="block text-sm font-semibold text-gray-700 mb-2">Assign
                                Client</label>
                            <div class="relative">
                                <select id="client_id" name="client_id" required
                                    class="block w-full rounded-lg border-gray-300 py-3 pl-4 pr-10 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                    <option value="">Select a Client...</option>
                                    @foreach($clients as $id => $name)
                                        <option value="{{ $id }}" {{ (request('client_id') == $id) ? 'selected' : '' }}>
                                            {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">The project will be linked to this client entity.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Name -->
                            <div class="col-span-1">
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Project
                                    Name</label>
                                <input type="text" name="name" id="name" required
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50"
                                    placeholder="e.g. E-Commerce Platform">
                            </div>

                            <!-- Domain -->
                            <div class="col-span-1">
                                <label for="domain" class="block text-sm font-semibold text-gray-700 mb-2">Domain /
                                    URL</label>
                                <input type="text" name="domain" id="domain"
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
                                    <option value="development">Development</option>
                                    <option value="live">Live</option>
                                    <option value="paused">Paused</option>
                                </select>
                            </div>

                            <!-- Pricing -->
                            <div>
                                <label for="pricing_type" class="block text-sm font-semibold text-gray-700 mb-2">Billing
                                    Model</label>
                                <select id="pricing_type" name="pricing_type"
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                    <option value="fixed">Fixed Price</option>
                                    <option value="hourly">Hourly Rate</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-6 flex items-center justify-end gap-x-4 border-t border-gray-50">
                            <button type="button" onclick="history.back()"
                                class="text-sm font-semibold leading-6 text-gray-500 hover:text-gray-900 px-4 py-2 transition-colors">Cancel</button>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all transform hover:scale-[1.02]">
                                Create Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>