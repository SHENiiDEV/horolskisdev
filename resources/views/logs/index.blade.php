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
                        search: '',
                        sortDesc: false,
                        autoScroll: true,
                        connected: false,
                        controller: null,
                        
                        get filteredLogs() {
                            let result = this.logs;
                            if (this.search.trim()) {
                                const q = this.search.toLowerCase();
                                result = result.filter(l => l.raw.toLowerCase().includes(q));
                            }
                            if (this.sortDesc) {
                                return [...result].reverse();
                            }
                            return result;
                        },

                        parseLine(line) {
                            const regex = /^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.([A-Z]+): (.*)/;
                            const match = line.match(regex);
                            if (match) {
                                return {
                                    raw: line,
                                    datetime: match[1],
                                    env: match[2],
                                    level: match[3],
                                    message: match[4],
                                    isParsed: true
                                };
                            }
                            return {
                                raw: line,
                                isParsed: false
                            };
                        },

                        initLogs() {
                            if (this.connected) return;
                            this.logs = [{raw: 'Starting connection...', isParsed: false}];
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
                                        if (!line.trim()) return;
                                        
                                        const parsed = this.parseLine(line);
                                        
                                        // If not a new log format entirely, stick it to the last log item (useful for stack traces)
                                        if (!parsed.isParsed && this.logs.length > 0) {
                                            const last = this.logs[this.logs.length - 1];
                                            last.raw += '\n' + line;
                                            if (last.isParsed) last.message += '\n' + line;
                                        } else {
                                            this.logs.push(parsed);
                                        }
                                    });
                                    
                                    this.$nextTick(() => {
                                        if (!this.sortDesc && this.autoScroll) {
                                            const term = document.getElementById('global-terminal-content');
                                            if (term) term.scrollTop = term.scrollHeight;
                                        }
                                    });
                                    
                                    if (this.logs.length > 2000) {
                                        this.logs = this.logs.slice(-2000);
                                    }
                                }
                            } catch (error) {
                                if (error.name === 'AbortError') {
                                    this.logs.push({raw: 'Connection stopped.', isParsed: false});
                                } else {
                                    this.logs.push({raw: 'Error: ' + error.message, isParsed: false});
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

                    <div class="flex items-center gap-4 mb-4">
                        <div class="relative flex-1 max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" x-model="search" placeholder="Search logs by keyword..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                                autocomplete="off">
                        </div>

                        <button @click="sortDesc = !sortDesc"
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-all">
                            <svg x-show="!sortDesc" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            <svg x-show="sortDesc" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                            </svg>
                            <span x-text="sortDesc ? 'Newest First' : 'Oldest First'"></span>
                        </button>

                        <button @click="autoScroll = !autoScroll"
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-all">
                            <div class="w-2 h-2 rounded-full" :class="autoScroll ? 'bg-green-500' : 'bg-gray-300'">
                            </div>
                            Auto-Scroll
                        </button>
                    </div>

                    <div
                        class="bg-gray-900 rounded-xl shadow-lg border border-gray-800 overflow-hidden font-mono text-xs">
                        <div id="global-terminal-content" class="h-[600px] overflow-y-auto divide-y divide-gray-800">
                            <template x-for="(log, index) in filteredLogs" :key="index">
                                <div class="px-4 py-2 hover:bg-gray-800/50 transition-colors">
                                    <template x-if="log.isParsed">
                                        <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-3">
                                            <span class="text-gray-500 shrink-0 select-none font-medium"
                                                x-text="log.datetime"></span>

                                            <span class="shrink-0 font-bold tracking-wide" :class="{
                                                    'text-red-400': ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'].includes(log.level),
                                                    'text-yellow-400': log.level === 'WARNING',
                                                    'text-blue-400': log.level === 'INFO',
                                                    'text-gray-400': log.level === 'DEBUG',
                                                    'text-purple-400': log.level === 'NOTICE'
                                                }" x-text="'[' + log.level + ']'"></span>

                                            <span class="text-gray-300 whitespace-pre-wrap break-all"
                                                x-text="log.message"></span>
                                        </div>
                                    </template>
                                    <template x-if="!log.isParsed">
                                        <div class="text-gray-400 whitespace-pre-wrap break-all pl-2 sm:pl-[200px]"
                                            x-text="log.raw"></div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="filteredLogs.length === 0">
                                <div class="px-4 py-12 text-center text-gray-500 italic">
                                    No logs matching the current criteria.
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>