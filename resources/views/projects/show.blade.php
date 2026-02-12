<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500">Client: <a href="{{ route('clients.show', $project->client) }}"
                        class="underline hover:text-indigo-600">{{ $project->client->name }}</a></p>
            </div>
            <div class="flex space-x-2">
                <span
                    class="px-2 py-1 text-xs rounded-full inline-flex items-center {{ $project->status === 'live' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($project->status) }}
                </span>
                <a href="{{ route('projects.edit', $project) }}" class="text-gray-400 hover:text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'tasks' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($project->is_monitoring_enabled && $project->domain)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Server Status</h3>
                            <div class="mt-2 text-sm text-gray-500">
                                <p>Monitoring <strong>{{ $project->domain }}</strong></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-x-4">
                            @if($project->last_check_status === 'up')
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">UP</span>
                                @if($project->last_check_time)
                                    <span class="text-sm text-gray-500">Last check: {{ $project->last_check_time->diffForHumans() }}</span>
                                @endif
                            @elseif($project->last_check_status === 'down')
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">DOWN</span>
                                @if($project->down_since)
                                    <span class="text-sm text-red-600 font-bold">Down for: {{ $project->down_since->diffInMinutes() }} mins</span>
                                @endif
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">PENDING</span>
                            @endif
                        </div>
                    </div>

                    {{-- Uptime History Visualization --}}
                    <div class="mt-6">
                        <div class="flex justify-between items-end mb-1">
                            <h4 class="text-sm font-medium text-gray-900">Uptime History (Last hour)</h4>
                            <div class="flex gap-4 text-xs text-gray-500">
                                <span>Avg: <span class="font-semibold text-gray-900">{{ $project->uptime_checks_avg_response_time_ms ? round($project->uptime_checks_avg_response_time_ms) . 'ms' : '-' }}</span></span>
                                <span>Min: <span class="font-semibold text-green-600">{{ $project->uptime_checks_min_response_time_ms ? $project->uptime_checks_min_response_time_ms . 'ms' : '-' }}</span></span>
                                <span>Max: <span class="font-semibold text-indigo-600">{{ $project->uptime_checks_max_response_time_ms ? $project->uptime_checks_max_response_time_ms . 'ms' : '-' }}</span></span>
                            </div>
                        </div>
                        <div class="flex gap-px h-12 bg-gray-50 p-1 rounded-md border border-gray-200 overflow-hidden">
                            @foreach($project->uptimeChecks as $check)
                                <div class="flex-1 relative group {{ $check->status === 'up' ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600' }}"
                                     title="{{ $check->created_at->format('H:i') }} | Status: {{ strtoupper($check->status) }} | Response: {{ $check->response_time_ms ?? 'N/A' }}ms">
                                </div>
                            @endforeach
                            @if($project->uptimeChecks->isEmpty())
                                <div class="w-full flex items-center justify-center text-xs text-gray-400">
                                    No uptime data recorded yet.
                                </div>
                            @endif
                        </div>
                        <div class="mt-1 flex justify-between text-xs text-gray-400">
                            <span>60 mins ago</span>
                            <span>Now</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 bg-white rounded-t-lg px-6 pt-4">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'vault'"
                        :class="{ 'border-indigo-500 text-indigo-600': tab === 'vault', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'vault' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Vault (Credentials)
                    </button>
                    <button @click="tab = 'tasks'"
                        :class="{ 'border-indigo-500 text-indigo-600': tab === 'tasks', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'tasks' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Tasks
                    </button>
                    <button @click="tab = 'integrations'"
                        :class="{ 'border-indigo-500 text-indigo-600': tab === 'integrations', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'integrations' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Plugins / Files
                    </button>
                    <button @click="tab = 'time'"
                        :class="{ 'border-indigo-500 text-indigo-600': tab === 'time', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'time' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Time Tracker
                    </button>
                </nav>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div x-show="tab === 'vault'" style="display: none;" x-data="{ 
                              open: false, 
                              mode: 'view', 
                              vault: { fields: [] }, 
                              formAction: '',
                              initModal(v) {
                                 this.vault = v;
                                 // Ensure fields is always an array
                                 if (!this.vault.fields) this.vault.fields = [];
                                 this.formAction = '/vaults/' + v.id;
                                 this.mode = 'view';
                                 this.open = true;
                              }
                          }">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Credentials</h3>
                            <a href="{{ route('vaults.create', ['project_id' => $project->id]) }}"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path
                                        d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                </svg>
                                Add Credential
                            </a>
                        </div>

                        @if($project->vaults->count() > 0)
                            <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($project->vaults as $vault)
                                    <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow ring-1 ring-gray-900/5 hover:ring-indigo-500 hover:shadow-md transition cursor-pointer"
                                        @click="initModal({{ $vault }})">
                                        <div class="flex w-full items-center justify-between space-x-6 p-6">
                                            <div class="flex-1 truncate">
                                                <div class="flex items-center space-x-3">
                                                    <h3 class="truncate text-sm font-medium text-gray-900">{{ $vault->name }}
                                                    </h3>
                                                    <span
                                                        class="inline-flex flex-shrink-0 items-center rounded-full bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{ $vault->type }}</span>
                                                </div>
                                                <p class="mt-1 truncate text-xs text-gray-500">Click to view details</p>
                                                
                                                <div class="mt-4 flex gap-2">
                                                    @if($cmd = $vault->connection_command)
                                                        <button type="button" @click.stop
                                                            @click="navigator.clipboard.writeText({{ json_encode($cmd) }}).then(() => alert('Command copied!'))"
                                                            class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 hover:bg-gray-100">
                                                            <svg class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                                            </svg>
                                                            Copy Command
                                                        </button>
                                                    @endif

                                                    <form action="{{ route('vaults.destroy', $vault->id) }}" method="POST" class="inline-block" @click.stop onsubmit="return confirm('Are you sure you want to delete this credential?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                            class="inline-flex items-center gap-x-1.5 rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-600 ring-1 ring-inset ring-red-500/10 hover:bg-red-100">
                                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div
                                                class="h-10 w-10 flex-shrink-0 rounded-full flex items-center justify-center bg-gray-100 text-gray-500">
                                                @if($vault->type === 'wordpress')
                                                    <span class="text-[#21759b]">
                                                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.042 21.579c3.085 0 5.8-1.465 7.604-3.72l-3.328-9.155a305.694 305.694 0 00-.73-2.196l-.993 2.846-2.553 7.505h.001v.001c-1.127 3.205-1.922 4.719-1.922 4.719H12.04zM2.38 11.967c0-2.316.828-4.441 2.213-6.095l5.228 15.356c-4.469-1.39-7.441-5.342-7.441-9.26zM13.684.821a11.127 11.127 0 00-11.238 9.39h.564c1.234 0 2.404-.32 2.404-.32.557-.156.634-.87.054-.954 0 0-1.07.126-2.257.126s-2.074-.126-2.074-.126c-.58-.084-.503.798.054.954 0 0 1.05.32 2.237.32h.494l7.009 19.336A11.137 11.137 0 0023.21 11.08c0-.361-.017-.719-.051-1.072h-2.146c-1.332 0-2.435.337-2.435.337-.58.156-.634.87-.054.953 0 0 1.033-.126 2.18-.126 1.147 0 1.942.126 1.942.126.58.084.634-.849.054-.954 0 0-1.16-.337-2.493-.337h-1.037L13.684.82zM15.42 16.35l2.23-6.666 1.45-3.882c.28-.737.56-1.492.56-1.492.155-.54-.3-.884-.712-.625 0 0-1.066.862-2.16 1.576l-4.14 12.35 2.772-1.26z"/></svg>
                                                    </span>
                                                @elseif($vault->type === 'ssh')
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                @elseif($vault->type === 'hosting')
                                                    <span class="text-orange-600">
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                    </span>
                                                @elseif($vault->type === 'gmail')
                                                    <span class="text-red-600">
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    </span>
                                                @elseif($vault->type === 'database')
                                                    <span class="text-blue-600">
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 2.21 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                                    </span>
                                                @else
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div
                                class="text-center rounded-lg border-2 border-dashed border-gray-300 p-12 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">No credentials</h3>
                                <p class="mt-1 text-sm text-gray-500">Store passwords securely in the vault.</p>
                                <div class="mt-6">
                                    <a href="{{ route('vaults.create', ['project_id' => $project->id]) }}"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                            aria-hidden="true">
                                            <path
                                                d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                        </svg>
                                        Add Credential
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Vault Modal -->
                        <div x-show="open" class="relative z-50" aria-labelledby="modal-title" role="dialog"
                            aria-modal="true">
                            <div x-show="open" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div
                                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div x-show="open" @click.outside="open = false"
                                        x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">

                                        <!-- Edit Form -->
                                        <form :action="formAction" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div>
                                                <div class="flex justify-between items-center mb-6">
                                                    <h3 class="text-xl font-semibold leading-6 text-gray-900"
                                                        id="modal-title" x-text="vault.name"></h3>
                                                    <div class="flex space-x-2">
                                                        <a :href="'/vaults/' + vault.id + '/edit'"
                                                            class="rounded bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 shadow-sm hover:bg-indigo-100 transition-colors">
                                                            Pencil Edit
                                                        </a>
                                                        <button type="button" @click="open = false"
                                                            class="rounded bg-gray-50 px-3 py-1.5 text-sm font-semibold text-gray-600 shadow-sm hover:bg-gray-100">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="mt-4 space-y-5">
                                                    <!-- Name (Only in Edit) -->
                                                    <div x-show="mode === 'edit'">
                                                        <label
                                                            class="block text-sm font-medium leading-6 text-gray-900">Name</label>
                                                        <div class="mt-2">
                                                            <input type="text" name="name" x-model="vault.name"
                                                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                        </div>
                                                        <input type="hidden" name="type" x-model="vault.type">
                                                    </div>

                                                    <!-- Dynamic Fields View -->
                                                    <div x-show="mode === 'view'" class="space-y-4">
                                                        <template x-for="(field, index) in vault.fields" :key="index">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium leading-6 text-gray-900"
                                                                    x-text="field.name"></label>
                                                                <div
                                                                    class="mt-1 flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 group">
                                                                    <span
                                                                        class="flex select-none items-center pl-3 text-gray-500 sm:text-sm font-mono flex-1 py-1.5 overflow-x-auto whitespace-pre-wrap"
                                                                        :class="{ 'blur-sm group-hover:blur-none transition-all duration-300': field.is_secret }"
                                                                        x-text="field.value"></span>
                                                                    <button type="button"
                                                                        @click="navigator.clipboard.writeText(field.value)"
                                                                        class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            fill="none" viewBox="0 0 24 24"
                                                                            stroke-width="1.5" stroke="currentColor"
                                                                            class="w-4 h-4 text-gray-400">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <!-- Dynamic Fields Edit -->
                                                    <div x-show="mode === 'edit'" class="space-y-4">
                                                        <div class="flex justify-between items-center mb-2">
                                                            <label
                                                                class="block text-sm font-medium leading-6 text-gray-900">Fields</label>
                                                            <button type="button"
                                                                @click="vault.fields.push({ name: '', value: '', is_secret: false })"
                                                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">+
                                                                Add Field</button>
                                                        </div>

                                                        <div class="space-y-3">
                                                            <template x-for="(field, index) in vault.fields"
                                                                :key="index">
                                                                <div class="flex gap-2 items-start">
                                                                    <div class="w-1/3">
                                                                        <input type="text"
                                                                            :name="'payload[' + index + '][name]'"
                                                                            x-model="field.name"
                                                                            class="form-input py-1.5 text-xs"
                                                                            placeholder="Field Name">
                                                                    </div>
                                                                    <div class="w-1/2">
                                                                        <textarea
                                                                            :name="'payload[' + index + '][value]'"
                                                                            x-model="field.value" rows="1"
                                                                            class="form-input py-1.5 text-xs text-mono"
                                                                            placeholder="Value"></textarea>
                                                                    </div>
                                                                    <div
                                                                        class="flex-1 flex items-center justify-between pt-1">
                                                                        <label
                                                                            class="flex items-center space-x-1 cursor-pointer"
                                                                            title="Hide value">
                                                                            <input type="hidden"
                                                                                :name="'payload[' + index + '][is_secret]'"
                                                                                :value="field.is_secret ? '1' : '0'">
                                                                            <input type="checkbox"
                                                                                x-model="field.is_secret"
                                                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 h-4 w-4">
                                                                            <span
                                                                                class="text-xs text-gray-500">Secret</span>
                                                                        </label>
                                                                        <button type="button"
                                                                            @click="vault.fields.splice(index, 1)"
                                                                            class="text-gray-400 hover:text-red-500">
                                                                            <svg class="h-4 w-4" fill="none"
                                                                                viewBox="0 0 24 24"
                                                                                stroke="currentColor">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M6 18L18 6M6 6l12 12" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div
                                                class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                                <button x-show="mode === 'edit'" type="submit"
                                                    class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2">Save
                                                    Changes</button>
                                                <button type="button" @click="open = false"
                                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">Close</button>
                                            </div>
                                        </form>

                                        <!-- Delete Form -->
                                        <form x-show="mode === 'edit'" :action="formAction" method="POST"
                                            class="mt-4 border-t border-gray-100 pt-4 text-center">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-sm text-red-600 hover:text-red-900 font-medium"
                                                onclick="return confirm('Are you sure you want to delete this credential?')">Delete
                                                this credential</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Tasks Tab (Kanban) -->
                    <div x-show="tab === 'tasks'" style="display: none;" class="h-full">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Project Tasks</h3>
                                <p class="text-sm text-gray-500">Manage workflow and track time.</p>
                            </div>
                            <div class="flex space-x-3">
                                @include('projects.partials.invoice-button')
                                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path
                                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                    </svg>
                                    Add Task
                                </a>
                            </div>
                        </div>

                        <!-- Kanban Board -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-full items-start">

                            <!-- Pending Column -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4
                                    class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                                    Pending
                                    <span class="ml-auto bg-gray-200 text-gray-700 py-0.5 px-2 rounded-full text-xs">
                                        {{ $project->tasks->where('status', 'pending')->count() }}
                                    </span>
                                </h4>
                                <div class="space-y-3 min-h-[100px]" x-on:drop="updateTaskStatus($event, 'pending')"
                                    x-on:dragover.prevent>
                                    @forelse($project->tasks->where('status', 'pending') as $task)
                                        @include('projects.partials.task-card', ['task' => $task])
                                    @empty
                                        <div
                                            class="text-center py-6 text-gray-400 text-xs border-2 border-dashed border-gray-200 rounded-lg">
                                            No pending tasks</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- In Progress Column -->
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                <h4
                                    class="text-xs font-semibold text-blue-800 uppercase tracking-wide mb-3 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                                    In Progress
                                    <span class="ml-auto bg-blue-200 text-blue-800 py-0.5 px-2 rounded-full text-xs">
                                        {{ $project->tasks->where('status', 'in_progress')->count() }}
                                    </span>
                                </h4>
                                <div class="space-y-3 min-h-[100px]" x-on:drop="updateTaskStatus($event, 'in_progress')"
                                    x-on:dragover.prevent>
                                    @forelse($project->tasks->where('status', 'in_progress') as $task)
                                        @include('projects.partials.task-card', ['task' => $task])
                                    @empty
                                        <div
                                            class="text-center py-6 text-blue-300 text-xs border-2 border-dashed border-blue-200 rounded-lg">
                                            No active tasks</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Done Column -->
                            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                <h4
                                    class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                    Done
                                    <span class="ml-auto bg-green-200 text-green-800 py-0.5 px-2 rounded-full text-xs">
                                        {{ $project->tasks->where('status', 'done')->count() }}
                                    </span>
                                </h4>
                                <div class="space-y-3 min-h-[100px]" x-on:drop="updateTaskStatus($event, 'done')"
                                    x-on:dragover.prevent>
                                    @forelse($project->tasks->where('status', 'done') as $task)
                                        @include('projects.partials.task-card', ['task' => $task])
                                    @empty
                                        <div
                                            class="text-center py-6 text-green-300 text-xs border-2 border-dashed border-green-200 rounded-lg">
                                            No completed tasks</div>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Integrations Tab -->
                    <div x-show="tab === 'integrations'" style="display: none;" x-data="{ openUpload: false }">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Plugins & Integration Files</h3>
                            <button @click="openUpload = true"
                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Upload
                                Plugin</button>
                        </div>

                        @if($project->integrations && $project->integrations->count() > 0)
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($project->integrations as $integration)
                                    <div class="bg-white border rounded-lg shadow-sm p-4 hover:shadow-md transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-gray-900">{{ $integration->name }}
                                                    </h4>
                                                    @if($integration->documentation_url)
                                                        <a href="{{ $integration->documentation_url }}" target="_blank"
                                                            class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center mt-1">
                                                            <span>View Documentation</span>
                                                            <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                            </svg>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('integrations.download', $integration) }}"
                                                    class="text-gray-400 hover:text-indigo-600" title="Download">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('integrations.destroy', $integration) }}" method="POST"
                                                    onsubmit="return confirm('Delete this file?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-500"
                                                        title="Delete">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        @if($integration->test_cards)
                                            <div class="mt-4 pt-4 border-t border-gray-100">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                                    Test Cards / Notes</p>
                                                <div
                                                    class="bg-gray-50 rounded-md p-2 text-xs text-gray-700 font-mono whitespace-pre-wrap max-h-32 overflow-y-auto border border-gray-200">
                                                    {{ $integration->test_cards }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mt-3 text-xs text-gray-400 text-right">
                                            {{ $integration->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center rounded-lg border-2 border-dashed border-gray-300 p-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">No plugins uploaded</h3>
                                <p class="mt-1 text-sm text-gray-500">Upload plugins and attach documentation.</p>
                                <div class="mt-6">
                                    <button @click="openUpload = true"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                            aria-hidden="true">
                                            <path
                                                d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                        </svg>
                                        Upload Plugin
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Upload Modal -->
                        <div x-show="openUpload" class="relative z-50" aria-labelledby="modal-title" role="dialog"
                            aria-modal="true">
                            <div x-show="openUpload" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div
                                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div x-show="openUpload" @click.outside="openUpload = false"
                                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                        <form action="{{ route('integrations.store') }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                                            <div class="space-y-4">
                                                <h3 class="text-lg font-semibold leading-6 text-gray-900">Upload
                                                    Plugin / File</h3>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium leading-6 text-gray-900">Name</label>
                                                    <div class="mt-2"><input type="text" name="name" required
                                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium leading-6 text-gray-900">File</label>
                                                    <div class="mt-2"><input type="file" name="file" required
                                                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium leading-6 text-gray-900">Documentation
                                                        URL (Optional)</label>
                                                    <div class="mt-2"><input type="url" name="documentation_url"
                                                            placeholder="https://"
                                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium leading-6 text-gray-900">Test
                                                        Cards / Notes (Optional)</label>
                                                    <div class="mt-2"><textarea name="test_cards" rows="3"
                                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                                <button type="submit"
                                                    class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:col-start-2">Upload</button>
                                                <button type="button" @click="openUpload = false"
                                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Tab -->
                    <div x-show="tab === 'time'" style="display: none;"
                        x-data="{
                            editingEntry: null,
                            showModal: false,
                            formAction: '',
                            deleteAction: '',
                            formData: {
                                start_time: '',
                                end_time: '',
                                description: '',
                                duration_minutes: ''
                            },
                            edit(entry) {
                                this.editingEntry = entry;
                                this.formData.start_time = entry.start_time.slice(0, 16);
                                this.formData.end_time = entry.end_time ? entry.end_time.slice(0, 16) : '';
                                this.formData.description = entry.description;
                                this.formData.duration_minutes = entry.duration ? Math.round(entry.duration / 60) : '';
                                
                                this.formAction = '/time-entries/' + entry.id;
                                this.deleteAction = '/time-entries/' + entry.id;
                                this.showModal = true;
                            }
                        }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Time Entries</h3>
                        </div>

                        <div class="overflow-x-auto ring-1 ring-gray-900/5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                            User</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Task /
                                            Description</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            Duration
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($project->timeEntries()->with('user', 'task')->latest()->get() as $entry)
                                        <tr>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ $entry->user->name }}
                                            </td>
                                            <td class="px-3 py-4 text-sm text-gray-500">
                                                @if($entry->task)
                                                    <span class="font-medium text-gray-900">{{ $entry->task->title }}</span><br>
                                                @endif
                                                {{ $entry->description }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $entry->start_time->format('M d, H:i') }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($entry->duration)
                                                    {{ gmdate('H:i:s', $entry->duration) }}
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Running</span>
                                                @endif
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                @if(auth()->id() === $entry->user_id || auth()->user()->isAdmin())
                                                    <button @click="edit({{ $entry }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-10 text-center text-sm text-gray-500">No time
                                                entries
                                                recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3"
                                            class="py-3.5 pl-4 pr-3 text-right text-sm font-semibold text-gray-900 sm:pl-6">
                                            Total Time:</td>
                                        <td class="px-3 py-3.5 text-left text-sm font-bold text-gray-900">
                                            {{ gmdate('H:i:s', $project->timeEntries()->sum('duration')) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Edit Modal -->
                        <div x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div x-show="showModal" @click.outside="showModal = false"
                                        x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                        
                                        <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                                            <button type="button" @click="showModal = false"
                                                class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                <span class="sr-only">Close</span>
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Edit Time Entry</h3>
                                                <div class="mt-2">
                                                    <form :action="formAction" method="POST" id="editFormProject">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="space-y-4">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                                                <input type="text" name="description" x-model="formData.description"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                                                    <input type="datetime-local" name="start_time" x-model="formData.start_time" required
                                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700">End Time</label>
                                                                    <input type="datetime-local" name="end_time" x-model="formData.end_time"
                                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">Duration Override (Minutes)</label>
                                                                <input type="number" name="duration_minutes" x-model="formData.duration_minutes"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                    placeholder="Optional - overrides calculated duration">
                                                                <p class="mt-1 text-xs text-gray-500">Leave empty to calculate from Start/End times.</p>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse justify-between">
                                            <button type="submit" form="editFormProject"
                                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Save</button>
                                            
                                            <form :action="deleteAction" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 sm:mt-0 sm:w-auto">Delete</button>
                                            </form>
                                            
                                            <button type="button" @click="showModal = false"
                                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:mr-auto">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    function updateTaskStatus(event, status) {
        event.preventDefault();
        const taskId = event.dataTransfer.getData('text/plain');

        // Simple AJAX update
        fetch(`/tasks/${taskId}`, {
            method: 'POST', // Method spoofing for PUT
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _method: 'PUT',
                status: status
            })
        })
            .then(response => {
                if (response.ok) {
                    window.location.reload(); // Simplest way to refresh board state for now
                } else {
                    alert('Failed to update task status');
                }
            });
    }
</script>