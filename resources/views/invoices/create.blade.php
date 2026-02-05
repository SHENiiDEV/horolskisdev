<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Create Invoice
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    <div class="mb-10 flex justify-between items-center border-b border-gray-100 pb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Create Invoice</h3>
                            <p class="text-sm text-gray-500 mt-2">Create a new manual invoice with custom line items.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('invoices.store') }}" x-data="{
                              items: [{ description: '', quantity: 1, rate: 0, amount: 0 }],
                              tasks: {{ isset($tasks) ? $tasks->toJson() : '[]' }},
                              selectedTaskIds: [],
                              
                              calculateTotal() {
                                  let customTotal = this.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
                                  
                                  let tasksTotal = 0;
                                  this.tasks.forEach(task => {
                                      // Use loose comparison or String conversion to handle type mismatches
                                      if (this.selectedTaskIds.some(id => id == task.id)) {
                                          tasksTotal += parseFloat(task.calculated_cost);
                                      }
                                  });
                                  
                                  return (customTotal + tasksTotal).toFixed(2);
                              },
                              updateItemTotal(index) {
                                  let item = this.items[index];
                                  item.amount = (parseFloat(item.quantity) * parseFloat(item.rate)).toFixed(2);
                              },
                              addItem() {
                                  this.items.push({ description: '', quantity: 1, rate: 0, amount: 0 });
                              },
                              removeItem(index) {
                                  this.items.splice(index, 1);
                              }
                          }">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <!-- Client -->
                            <div class="col-span-1">
                                <label for="client_id"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Client</label>
                                <select id="client_id" name="client_id" required
                                    onchange="window.location.href='?client_id=' + this.value"
                                    class="block w-full rounded-lg border-gray-300 py-3 pl-4 pr-10 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                    <option value="">Select Client...</option>
                                    @foreach($clients as $id => $name)
                                        <option value="{{ $id }}" {{ (isset($client) && $client->id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-span-1">
                                <label for="status"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                <select id="status" name="status"
                                    class="block w-full rounded-lg border-gray-300 py-3 pl-4 pr-10 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <!-- Period -->
                            <div class="col-span-1">
                                <label for="period_start" class="block text-sm font-semibold text-gray-700 mb-2">Period
                                    Start</label>
                                <input type="date" name="period_start" id="period_start" required
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                            </div>
                            <div class="col-span-1">
                                <label for="period_end" class="block text-sm font-semibold text-gray-700 mb-2">Period
                                    End</label>
                                <input type="date" name="period_end" id="period_end" required
                                    class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                            </div>
                        </div>

                        <!-- Completed Tasks Selection -->
                        @if(isset($tasks) && $tasks->count() > 0)
                            <div class="mb-10">
                                <label class="block text-sm font-semibold text-gray-700 mb-4">Completed Tasks
                                    (Uninvoiced)</label>
                                <div
                                    class="bg-gray-50 rounded-xl border border-gray-100 divide-y divide-gray-100 overflow-hidden">
                                    @foreach($tasks as $task)
                                        <div class="p-4 flex items-start hover:bg-white transition-colors">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="task_ids[]" value="{{ $task['id'] }}"
                                                    x-model="selectedTaskIds" :value="{{ $task['id'] }}"
                                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                            </div>
                                            <div class="ml-3 text-sm flex-1">
                                                <label for="task_{{ $task['id'] }}" class="font-medium text-gray-900 block">
                                                    {{ $task['title'] }}
                                                    <span
                                                        class="text-gray-500 font-normal">({{ $task['project']['name'] }})</span>
                                                </label>
                                                <p class="text-gray-500">{{ Str::limit($task['description'], 80) }}</p>
                                            </div>
                                            <div class="ml-3 text-sm text-right">
                                                <p class="font-semibold text-gray-900">
                                                    €{{ number_format((float) $task['calculated_cost'], 2) }}</p>
                                                <p class="text-xs text-gray-500">{{ $task['total_hours'] }} hrs @
                                                    €{{ $task['rate'] }}/hr</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(isset($client))
                            <div class="mb-10 p-4 bg-yellow-50 text-yellow-700 rounded-lg text-sm">
                                No uninvoiced copmpleted tasks found for this client.
                            </div>
                        @endif

                        <!-- Dynamic Items -->
                        <div class="mb-10">
                            <label class="block text-sm font-semibold text-gray-700 mb-4">Additional Items</label>

                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-4 mb-4 items-end">
                                        <div class="col-span-5">
                                            <label x-show="index === 0"
                                                class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                            <input type="text" :name="`items[${index}][description]`"
                                                x-model="item.description" placeholder="Item description" required
                                                class="block w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div class="col-span-2">
                                            <label x-show="index === 0"
                                                class="block text-xs font-medium text-gray-500 mb-1">Qty</label>
                                            <input type="number" :name="`items[${index}][quantity]`"
                                                x-model="item.quantity" @input="updateItemTotal(index)" step="0.1"
                                                min="0" required
                                                class="block w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div class="col-span-2">
                                            <label x-show="index === 0"
                                                class="block text-xs font-medium text-gray-500 mb-1">Rate (€)</label>
                                            <input type="number" :name="`items[${index}][rate]`" x-model="item.rate"
                                                @input="updateItemTotal(index)" step="0.01" min="0" required
                                                class="block w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div class="col-span-2">
                                            <label x-show="index === 0"
                                                class="block text-xs font-medium text-gray-500 mb-1">Total</label>
                                            <input type="number" :name="`items[${index}][amount]`" x-model="item.amount"
                                                readonly step="0.01"
                                                class="block w-full rounded-md border-gray-300 py-2 text-sm bg-gray-100 text-gray-700 cursor-not-allowed">
                                        </div>
                                        <div class="col-span-1 text-right pb-2">
                                            <button type="button" @click="removeItem(index)"
                                                class="text-red-400 hover:text-red-600 transition-colors"
                                                title="Remove Item">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <button type="button" @click="addItem()"
                                    class="mt-2 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add Line Item
                                </button>
                            </div>

                            <div class="flex justify-end mt-4">
                                <div class="text-right">
                                    <span class="text-sm text-gray-500 mr-2">Total Amount:</span>
                                    <span class="text-2xl font-bold text-gray-900">€<span
                                            x-text="calculateTotal()"></span></span>
                                    <input type="hidden" name="amount" :value="calculateTotal()">
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-8">
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Notes / Payment
                                Details</label>
                            <textarea id="notes" name="notes" rows="4"
                                class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50"
                                placeholder="Additional notes to appear on the invoice..."></textarea>
                        </div>

                        <div class="pt-6 flex items-center justify-end gap-x-4 border-t border-gray-50">
                            <button type="button" onclick="history.back()"
                                class="text-sm font-semibold leading-6 text-gray-500 hover:text-gray-900 px-4 py-2 transition-colors">Cancel</button>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all transform hover:scale-[1.02]">
                                Create Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>