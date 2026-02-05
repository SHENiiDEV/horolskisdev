<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Telegram Inbox') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if($messages->isEmpty())
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No messages yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Messages sent to your bot will appear here.</p>
                            <p class="mt-4 text-xs text-gray-400">Webhook URL: {{ route('telegram.index') }}/webhook</p>
                        </div>
                    @else
                        <ul role="list" class="divide-y divide-gray-100">
                            @foreach($messages as $message)
                                <li class="flex gap-x-4 py-5 hover:bg-gray-50 px-4 rounded-lg transition">
                                    <div class="flex-none">
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-indigo-500 text-white font-semibold">
                                            {{ strtoupper(substr($message->sender_name ?? 'U', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-auto">
                                        <p class="text-sm font-semibold leading-6 text-gray-900">
                                            {{ $message->sender_name ?? 'Unknown User' }} 
                                            <span class="font-normal text-gray-500 text-xs ml-2">ID: {{ $message->chat_id }}</span>
                                        </p>
                                        <p class="mt-1 text-sm leading-5 text-gray-600 break-words">{{ $message->message_text }}</p>
                                    </div>
                                    <div class="hidden sm:flex sm:flex-col sm:items-end">
                                        <p class="text-xs leading-5 text-gray-500">{{ $message->created_at->diffForHumans() }}</p>
                                        <p class="text-xs leading-5 text-gray-400">{{ $message->created_at->format('M d, H:i') }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-4">
                            {{ $messages->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
