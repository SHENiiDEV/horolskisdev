<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="{
                        logs: [],
                        connected: false,
                        controller: null,
                        initLogs() {
                            if (this.connected) return;
                            this.logs = ['Starting connection...'];
                            this.connect();
                        },
                        async connect() {
                            try {
                                this.controller = new AbortController();
                                const response = await fetch('{{ route('logs.stream') }}', {
                                    signal: this.controller.signal
                                });

                                if (!response.ok) {
                                    let errorMessage = 'Connection failed';
                                    try {
                                        const err = await response.json();
                                        errorMessage = err.error || errorMessage;
                                    } catch (e) {
                                        errorMessage = `Server returned ${response.status} ${response.statusText}`;
                                        console.error('Non-JSON response:', await response.text());
                                    }
                                    throw new Error(errorMessage);
                                }

                                this.connected = true;
                                const reader = response.body.getReader();
                                const decoder = new TextDecoder();

                                while (true) {
                                    const { value, done } = await reader.read();
                                    if (done) break;
                                    
                                    const text = decoder.decode(value, { stream: true });
                                    const lines = text.split('\n');
                                    
                                    lines.forEach(line => {
                                        if (line.trim()) this.logs.push(line);
                                    });
                                    
                                    this.$nextTick(() => {
                                        const term = document.getElementById('global-terminal-content');
                                        if (term) term.scrollTop = term.scrollHeight;
                                    });
                                    
                                    if (this.logs.length > 2000) {
                                        this.logs = this.logs.slice(-2000);
                                    }
                                }
                            } catch (error) {
                                if (error.name === 'AbortError') {
                                    this.logs.push('Connection stopped.');
                                } else {
                                    this.logs.push('Error: ' + error.message);
                                }
                                this.connected = false;
                            }
                        },
                        disconnect() {
                            if (this.controller) {
                                this.controller.abort();
                                this.controller = null;
                                this.connected = false;
                            }
                        }
                    }" x-init="initLogs()">

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Local Application Logs</h3>
                            <p class="text-sm text-gray-500">Streaming <code
                                    class="text-xs bg-gray-100 rounded px-1">storage/logs/laravel.log</code></p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="logs = []"
                                class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
                            <div x-show="connected"
                                class="flex items-center gap-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full animate-pulse">
                                <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div> Live
                            </div>
                            <div x-show="!connected"
                                class="flex items-center gap-2 px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full">
                                <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div> Disconnected
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gray-900 rounded-xl shadow-lg border border-gray-800 overflow-hidden font-mono text-xs p-4">
                        <div id="global-terminal-content" class="h-[600px] overflow-y-auto space-y-1">
                            <template x-for="(line, index) in logs" :key="index">
                                <div
                                    class="text-gray-300 whitespace-pre-wrap break-all border-l-2 border-transparent hover:border-gray-700 hover:bg-gray-800/50 pl-2">
                                    <span class="text-gray-500 select-none mr-2"
                                        x-text="(new Date()).toLocaleTimeString()"></span>
                                    <span x-text="line"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>