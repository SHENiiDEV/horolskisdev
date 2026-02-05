<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoices') }}
            </h2>
            <div class="flex space-x-4">
                <form method="GET" action="{{ route('invoices.index') }}" class="relative">
                    <input type="text" name="search" placeholder="Search invoices..." value="{{ request('search') }}"
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </form>
                <a href="{{ route('invoices.create') }}"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Create
                    Invoice</a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mt-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('invoices.index') }}"
                    class="{{ !request('status') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Active
                </a>
                <a href="{{ route('invoices.index', ['status' => 'archived']) }}"
                    class="{{ request('status') === 'archived' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Archived
                </a>
            </nav>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl mt-6">
        <ul role="list" class="divide-y divide-gray-100">
            @forelse ($invoices as $invoice)
                <li class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 sm:px-6">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold leading-6 text-gray-900">
                                <a href="{{ route('invoices.show', $invoice) }}">
                                    <span class="absolute inset-x-0 -top-px bottom-0"></span>
                                    {{ $invoice->public_id ?? 'Invoice #' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </p>
                            <p class="mt-1 flex text-xs leading-5 text-gray-500">
                                <span class="truncate">{{ $invoice->client->name }}</span>
                                @if($invoice->project)
                                    <span class="mx-1">·</span> <span class="truncate">{{ $invoice->project->name }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-x-4 z-10">
                        <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900">€{{ number_format($invoice->amount, 2) }}</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</p>
                        </div>
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>

                        @if($invoice->trashed())
                            <form method="POST" action="{{ route('invoices.restore', $invoice->id) }}" class="inline">
                                @csrf
                                <button type="submit"
                                    class="text-green-600 hover:text-green-900 text-xs font-medium">Restore</button>
                            </form>
                            <form method="POST" action="{{ route('invoices.destroy', $invoice->id) }}" class="inline"
                                onsubmit="return confirm('Are you sure you want to permanently delete this invoice?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium ml-2">Delete
                                    Permanently</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('invoices.destroy', $invoice->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-gray-500">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No invoices found</h3>
                        @if(request('status') !== 'archived')
                            <p class="mt-1 text-sm text-gray-500">Create your first invoice.</p>
                            <div class="mt-6">
                                <a href="{{ route('invoices.create') }}"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path
                                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                    </svg>
                                    Create Invoice
                                </a>
                            </div>
                        @endif
                    </div>
                </li>
            @endforelse
        </ul>
    </div>
    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
</x-app-layout>