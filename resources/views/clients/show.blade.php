<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $client->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('clients.kanban', $client) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                    View Kanban Board
                </a>
                <a href="{{ route('clients.edit', $client) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 transition">
                    Edit Client
                </a>
                {{-- Project creation will go here --}}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Client Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Contact & Company Info</h3>
                    <div class="mt-2 text-gray-600 space-y-1">
                        @if($client->email)
                        <p><strong>Email:</strong> {{ $client->email }}</p> @endif
                        @if($client->phone)
                        <p><strong>Phone:</strong> {{ $client->phone }}</p> @endif
                        @if($client->vat_number)
                        <p><strong>VAT:</strong> {{ $client->vat_number }}</p> @endif
                        @if($client->registration_number)
                        <p><strong>Reg No:</strong> {{ $client->registration_number }}</p> @endif
                    </div>

                    <h3 class="mt-4 text-lg font-medium text-gray-900">Address</h3>
                    <div class="mt-2 text-gray-600">
                        <p>{{ $client->address }}</p>
                        <p>{{ $client->city }}, {{ $client->postal_code }}</p>
                        <p>{{ $client->country }}</p>
                    </div>

                    @if($client->notes)
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Notes</h3>
                        <p class="mt-1 text-gray-600 whitespace-pre-line">{{ $client->notes }}</p>
                    @endif
                </div>
            </div>

            <!-- Projects Grid -->
            <div class="flex justify-between items-center mt-8">
                <h3 class="text-xl font-bold text-gray-800">Projects</h3>
                <a href="{{ route('projects.create', ['client_id' => $client->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                    Add Project
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($client->projects as $project)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <h4 class="text-lg font-bold text-indigo-700">
                                    <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                                </h4>
                                <span
                                    class="px-2 py-1 text-xs rounded-full {{ $project->status === 'live' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ $project->domain ?? 'No domain' }}</p>

                            <hr class="my-4">

                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ $project->tasks_count ?? 0 }} Tasks</span>
                                <span>{{ $project->pricing_type }}</span>
                            </div>

                            <div class="mt-4 text-right">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Manage
                                    &rarr;</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full text-center py-10 bg-gray-50 rounded-lg dashed border-2 border-gray-300 text-gray-500">
                        No projects found for this client.
                    </div>
                @endforelse
            </div>

            <!-- Invoices Section -->
            <div class="flex justify-between items-center mt-12">
                <h3 class="text-xl font-bold text-gray-800">Invoices</h3>
                <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                    Create Invoice
                </a>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <ul class="divide-y divide-gray-200">
                    @forelse ($client->invoices as $invoice)
                        <li class="p-6 hover:bg-gray-50 flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">
                                    <a
                                        href="{{ route('invoices.show', $invoice) }}">#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</a>
                                </h4>
                                <p class="text-sm text-gray-500">{{ $invoice->created_at->format('M d, Y') }} —
                                    €{{ number_format($invoice->total_amount, 2) }}</p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </li>
                    @empty
                        <li class="p-6 text-center text-gray-500">No invoices found.</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>