<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2"
                x-data="{ period: '{{ $period }}' }">
                <select name="period" x-model="period" onchange="if(this.value !== 'custom') this.form.submit()"
                    class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="all_time">All Time</option>
                    <option value="custom">Custom</option>
                </select>

                <div x-show="period === 'custom'" style="display: none;" class="flex space-x-2">
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit"
                        class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Filter</button>
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Grid -->
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @if(!Auth::user()->isClient())
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                        <dt class="truncate text-sm font-medium text-gray-500">Active Clients</dt>
                        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['clients'] }}</dd>
                    </div>
                @endif
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Live Projects</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['projects'] }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Unpaid Invoices</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-red-600">{{ $stats['unpaid_invoices'] }}
                    </dd>
                    <p class="text-xs text-red-500">€{{ number_format($stats['unpaid_amount'], 2) }} outstanding</p>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Paid Tasks</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-600">
                        €{{ number_format($stats['paid_tasks_value'], 2) }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Completed Tasks (Unpaid)</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-yellow-600">
                        €{{ number_format($stats['completed_unpaid_tasks_value'], 2) }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Potential Earnings</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">
                        €{{ number_format($stats['potential_earnings'], 2) }}</dd>
                </div>
                @if(!Auth::user()->isClient())
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                        <dt class="truncate text-sm font-medium text-gray-500">Active Timer</dt>
                        @if($stats['active_timer'])
                            <dd class="mt-1 text-lg font-semibold tracking-tight text-indigo-600 truncate">
                                {{ $stats['active_timer']->task->title ?? 'Task' }}
                            </dd>
                            <p class="text-xs text-gray-500">{{ $stats['active_timer']->task->project->name ?? '' }}</p>
                        @else
                            <dd class="mt-1 text-lg font-medium tracking-tight text-gray-400">Idle</dd>
                        @endif
                    </div>
                @endif
            </dl>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Recent Projects -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Recent Projects</h3>
                    </div>
                    <ul role="list" class="divide-y divide-gray-100">
                        @forelse($recent_projects as $project)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                <a href="{{ route('projects.show', $project) }}" class="block">
                                    <div class="flex items-center justify-between">
                                        <p class="truncate text-sm font-medium text-indigo-600">{{ $project->name }}</p>
                                        <div class="flex flex-shrink-0">
                                            <p
                                                class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                                                {{ ucfirst($project->status) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-2 sm:flex sm:justify-between">
                                        <div class="sm:flex">
                                            <p class="flex items-center text-sm text-gray-500">
                                                {{ $project->client->name }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="px-4 py-4 text-sm text-gray-500">No projects yet.</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Recent Invoices -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Recent Invoices</h3>
                    </div>
                    <ul role="list" class="divide-y divide-gray-100">
                        @forelse($recent_invoices as $invoice)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                <a href="{{ route('invoices.show', $invoice) }}" class="block">
                                    <div class="flex items-center justify-between">
                                        <p class="truncate text-sm font-medium text-indigo-600">
                                            #{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <p class="text-sm text-gray-500">€{{ number_format($invoice->amount, 2) }}</p>
                                    </div>
                                    <div class="mt-2 flex justify-between">
                                        <p class="text-sm text-gray-500">{{ $invoice->client->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $invoice->created_at->format('M d') }}</p>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="px-4 py-4 text-sm text-gray-500">No invoices yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>