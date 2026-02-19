<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd"
                            d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center gap-3">
                        {{ $project->name }}
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $project->status === 'live' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Client: <a href="{{ route('clients.show', $project->client) }}"
                            class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">{{ $project->client->name }}</a>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.edit', $project) }}"
                    class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all">
                    <svg class="-ml-0.5 mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                    </svg>
                    Edit Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ 
        tab: 'vault', 
        toast: { show: false, message: '', type: 'success' },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        }
    }">
        <!-- Toast Notification -->
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-4 right-4 z-[100] flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-3 text-white shadow-lg"
            style="display: none;">
            <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor" class="h-5 w-5 text-green-400">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                    clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium" x-text="toast.message"></span>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Server Status Card --> <!-- ... (omitted, no change needed) ... -->
            <!-- We need to replace the x-data line, so I am including the specific lines to target -->


            <!-- Server Status Card -->
            @if($project->is_monitoring_enabled && $project->domain)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-50 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-6 h-6 text-indigo-600">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>

                            </div>
                            <div>
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Server Health</h3>
                                <p class="text-sm text-gray-500">Monitoring <a href="https://{{ $project->domain }}"
                                        target="_blank" class="text-indigo-600 hover:underline">{{ $project->domain }}</a>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-x-4">
                            @if($project->last_check_status === 'up')
                                <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 border border-green-100">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                    </span>
                                    <span class="text-xs font-semibold text-green-700">Operational</span>
                                </div>
                            @elseif($project->last_check_status === 'down')
                                <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-100">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                    </span>
                                    <span class="text-xs font-semibold text-red-700">Down</span>
                                </div>
                            @else
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">PENDING</span>
                            @endif
                        </div>
                    </div>

                    <!-- Uptime History Visualization (Reused Logic) -->
                    <div class="mt-6 border-t border-gray-100 pt-6">
                        <div class="flex justify-between items-end mb-2">
                            <div class="flex gap-4 text-xs text-gray-500">
                                <span>Avg: <span
                                        class="font-semibold text-gray-900">{{ $project->uptime_checks_avg_response_time_ms ? round($project->uptime_checks_avg_response_time_ms) . 'ms' : '-' }}</span></span>
                            </div>
                            <div class="text-xs text-gray-400">Last 60 Minutes</div>
                        </div>
                        <!-- ... (Uptime Bars - simpler version for brevity) ... -->
                        <div
                            class="flex gap-px h-10 bg-gray-50 rounded-md border border-gray-200 overflow-visible relative">
                            @foreach($project->uptimeChecks as $check)
                                <div
                                    class="flex-1 relative group cursor-pointer {{ $check->status === 'up' ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600' }} h-full first:rounded-l-sm last:rounded-r-sm transition-colors">
                                    {{-- Custom Tooltip --}}
                                    <div
                                        class="absolute bottom-full left-1/2 z-50 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-gray-900 px-2 py-1 text-xs text-white shadow-lg group-hover:block transition-all">
                                        <div class="font-bold flex items-center gap-2">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full {{ $check->status === 'up' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                            {{ strtoupper($check->status) }}
                                        </div>
                                        <div class="text-gray-300 text-[10px] mt-0.5">{{ $check->created_at->format('H:i:s') }}
                                            &bull; {{ $check->response_time_ms }}ms</div>
                                        {{-- Arrow --}}
                                        <div
                                            class="absolute -bottom-1 left-1/2 -ml-1 h-2 w-2 -translate-y-1/2 rotate-45 bg-gray-900">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($project->uptimeChecks->isEmpty())
                                <div class="w-full flex items-center justify-center text-xs text-gray-400 h-full">No monitoring
                                    data available</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabs -->
            <div class="">
                <nav class="flex space-x-1 rounded-xl bg-gray-900/5 p-1" aria-label="Tabs">
                    <button @click="tab = 'vault'"
                        :class="tab === 'vault' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-900'"
                        class="flex w-full items-center justify-center rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 select-none transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Vault
                    </button>
                    <button @click="tab = 'tasks'"
                        :class="tab === 'tasks' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-900'"
                        class="flex w-full items-center justify-center rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 select-none transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        Tasks
                    </button>
                    <button @click="tab = 'integrations'"
                        :class="tab === 'integrations' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-900'"
                        class="flex w-full items-center justify-center rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 select-none transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                        </svg>
                        Plugins & Files
                    </button>
                    <button @click="tab = 'time'"
                        :class="tab === 'time' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-900'"
                        class="flex w-full items-center justify-center rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 select-none transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Time Tracker
                    </button>
                    <button @click="tab = 'logs'"
                        :class="tab === 'logs' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-900'"
                        class="flex w-full items-center justify-center rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 select-none transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        Logs
                    </button>
                    <button @click="tab = 'terminal'"
                        :class="tab === 'terminal' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-900'"
                        class="flex w-full items-center justify-center rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 select-none transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Terminal
                    </button>

                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-4">
                <div x-show="tab === 'vault'" style="display: none;" x-data="{ 
                        open: false, 
                        mode: 'view', 
                        vault: { fields: [] }, 
                        formAction: '',
                        shareModalOpen: false,
                        shareForm: { expires_in: '60', allowed_views: 1, value: '', name: '' },
                        generatedLink: null,
                        shareResult: {},
                        copied: false,
                        openImportModal: false,
                        initModal(v) {
                             this.vault = v;
                             if (!this.vault.fields) this.vault.fields = [];
                             this.formAction = '/vaults/' + v.id;
                             this.mode = 'view';
                             this.open = true;
                        },
                        openShareModal(field) {
                              if (field === 'vault') {
                                  this.shareForm.value = JSON.stringify(this.vault.fields);
                                  this.shareForm.name = this.vault.name;
                              } else {
                                  this.shareForm.value = field.value;
                                  this.shareForm.name = field.name;
                              }
                              this.shareForm.expires_in = '60';
                              this.shareForm.allowed_views = 1;
                              this.generatedLink = null;
                              this.shareModalOpen = true;
                        },
                        async generateShareLink() {
                              try {
                                  const response = await fetch('{{ route('vault-shares.store') }}', {
                                      method: 'POST',
                                      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                      body: JSON.stringify({
                                          project_id: {{ $project->id }},
                                          value: this.shareForm.value,
                                          name: this.shareForm.name,
                                          expires_in: this.shareForm.expires_in,
                                          allowed_views: this.shareForm.allowed_views
                                      })
                                  });
                                  if (!response.ok) throw new Error('Failed to generate link');
                                  const data = await response.json();
                                  this.generatedLink = data.link;
                                  this.shareResult = data;
                              } catch (error) { console.error(error); alert('Error generating link.'); }
                        }
                    }">

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Credentials</h3>
                            <p class="text-sm text-gray-500">Manage secure access keys and passwords.</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openImportModal = true"
                                class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Import from Sheet
                            </button>
                            <a href="{{ route('vaults.create', ['project_id' => $project->id]) }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Add Credential
                            </a>
                        </div>
                    </div>

                    @if($project->vaults->count() > 0)
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($project->vaults as $vault)
                                <div class="group relative flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-indigo-300 transition-all cursor-pointer overflow-hidden"
                                    @click="initModal({{ $vault }})">
                                    <div class="p-6 flex-1">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="p-2.5 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                                    @if($vault->type === 'database')
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 2.21 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                                        </svg>
                                                    @elseif($vault->type === 'ssh')
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    @else
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h3
                                                        class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                        {{ $vault->name }}
                                                    </h3>
                                                    <p
                                                        class="text-xs text-gray-500 uppercase tracking-wider font-medium mt-0.5">
                                                        {{ $vault->type }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-500 line-clamp-2">
                                            Click to view details and reveal credentials.
                                        </div>
                                    </div>
                                    <div
                                        class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between group-hover:bg-indigo-50/30 transition-colors">
                                        <span
                                            class="text-xs font-medium text-gray-400 group-hover:text-indigo-500 transition-colors">View
                                            Details &rarr;</span>
                                        @if($cmd = $vault->connection_command)
                                            <button
                                                @click.stop="navigator.clipboard.writeText({{ json_encode($cmd) }}).then(() => showToast('Command copied!'))"
                                                class="text-gray-400 hover:text-gray-600" title="Copy Connection Command">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center rounded-xl border-2 border-dashed border-gray-300 p-12 bg-gray-50/50">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No credentials stored</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new vault item.</p>
                            <div class="mt-6">
                                <a href="{{ route('vaults.create', ['project_id' => $project->id]) }}"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Add Credential
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Include Modals (VaultModal and ShareModal) - Using include or inline -->
                    <!-- I'll put them inline here to avoid new files for now -->
                    <!-- Modal Code Omitted for Brevity in this step, will append next? No, must include now. -->

                    <!-- View/Edit Modal -->
                    <div x-show="open" class="relative z-50 transition-opacity" style="display: none;">
                        <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm" @click="open = false"></div>
                        <div class="fixed inset-0 z-10 w-screen overflow-y-auto" @click="open = false">
                            <div
                                class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                                    @click.stop>
                                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                        <div class="flex justify-between items-start mb-4">
                                            <h3 class="text-lg font-semibold leading-6 text-gray-900"
                                                x-text="vault.name"></h3>
                                            <button @click="open = false" class="text-gray-400 hover:text-gray-500"><svg
                                                    class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg></button>
                                        </div>

                                        <!-- Fields View -->
                                        <div class="space-y-4">
                                            <template x-for="(field, index) in vault.fields" :key="index">
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-500 uppercase tracking-wide"
                                                        x-text="field.name"></label>
                                                    <div
                                                        class="mt-1 flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300">
                                                        <span
                                                            class="flex-1 block w-full rounded-l-md border-0 py-1.5 pl-3 text-gray-900 sm:text-sm sm:leading-6 bg-gray-50 font-mono select-all"
                                                            x-text="field.value"
                                                            :class="{ 'blur-sm hover:blur-none transition-all': field.is_secret }"></span>
                                                        <button type="button"
                                                            @click="navigator.clipboard.writeText(field.value).then(() => showToast('Copied to clipboard!'))"
                                                            class="relative -ml-px inline-flex items-center gap-x-1.5 px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 text-gray-400" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div
                                            class="mt-6 flex justify-between items-center pt-4 border-t border-gray-100">
                                            <div class="flex gap-2">
                                                <a :href="'/vaults/' + vault.id + '/edit'"
                                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                                <form :action="'/vaults/' + vault.id" method="POST" class="inline"
                                                    onsubmit="return confirm('Delete?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="text-sm font-medium text-red-600 hover:text-red-500 ml-4">Delete</button>
                                                </form>
                                            </div>
                                            <button type="button" @click="openShareModal('vault')"
                                                class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                                Share Vault
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Share Modal (simplified) -->
                    <div x-show="shareModalOpen" class="relative z-[60]" style="display: none;">
                        <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm" @click="shareModalOpen = false">
                        </div>
                        <div class="fixed inset-0 z-10 w-screen overflow-y-auto" @click="shareModalOpen = false">
                            <div class="flex min-h-full items-center justify-center p-4">
                                <div @click.stop
                                    class="relative transform overflow-hidden rounded-lg bg-white p-6 shadow-xl transition-all sm:w-full sm:max-w-md">
                                    <h3 class="text-lg font-semibold" x-show="!generatedLink">Share Credential</h3>
                                    <h3 class="text-lg font-semibold text-green-600" x-show="generatedLink">Link
                                        Generated!</h3>

                                    <div x-show="!generatedLink" class="mt-4 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Expires In</label>
                                            <select x-model="shareForm.expires_in"
                                                class="mt-1 block w-full rounded-md border-gray-300 py-1.5 text-sm">
                                                <option value="60">1 Hour</option>
                                                <option value="1440">1 Day</option>
                                                <option value="10080">7 Days</option>
                                            </select>
                                        </div>
                                        <button @click="generateShareLink()"
                                            class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Generate
                                            Link</button>
                                    </div>

                                    <div x-show="generatedLink" class="mt-4">
                                        <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300">
                                            <input type="text" x-model="generatedLink" readonly
                                                class="block w-full border-0 bg-transparent py-1.5 pl-2 text-sm">
                                            <button
                                                @click="navigator.clipboard.writeText(generatedLink); showToast('Link copied!'); copied=true; setTimeout(()=>copied=false, 2000)"
                                                class="px-3 py-2 text-sm font-semibold text-gray-900 bg-gray-50 hover:bg-gray-100 border-l border-gray-300">
                                                <span x-show="!copied">Copy</span><span x-show="copied"
                                                    class="text-green-600">Copied</span>
                                            </button>
                                        </div>
                                        <button @click="shareModalOpen = false; generatedLink = null"
                                            class="mt-4 w-full rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-200">Done</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Import Modal -->
                    <div x-show="openImportModal" class="relative z-[60]" style="display: none;">
                        <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm" @click="openImportModal = false">
                        </div>
                        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div class="flex min-h-full items-center justify-center p-4">
                                <div @click.stop
                                    class="relative transform overflow-hidden rounded-lg bg-white p-6 shadow-xl transition-all sm:w-full sm:max-w-md">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Import from Google Sheet</h3>
                                    <form action="{{ route('projects.vaults.import', $project) }}" method="POST">
                                        @csrf
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Google Sheet
                                                    URL</label>
                                                <input type="url" name="sheet_url" required
                                                    placeholder="https://docs.google.com/spreadsheets/d/..."
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <p class="mt-2 text-xs text-gray-500">
                                                    Ensure the sheet is accessible (public or shared). The system will
                                                    export it as CSV.
                                                </p>
                                            </div>
                                            <div class="flex justify-end gap-3 pt-2">
                                                <button type="button" @click="openImportModal = false"
                                                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                                                <button type="submit"
                                                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Import</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tasks Tab (Kanban) -->
                <div x-show="tab === 'tasks'" style="display: none;"
                    class="h-full animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Project Tasks</h3>
                            <p class="text-sm text-gray-500">Track progress, manage workflow and log time.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @include('projects.partials.invoice-button')
                            <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Add Task
                            </a>
                        </div>
                    </div>

                    <!-- Kanban Board -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start h-full">

                        <!-- Pending Column -->
                        <div class="flex flex-col bg-gray-50/50 rounded-xl border border-gray-200/60 p-4 h-full">
                            <h4
                                class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    Pending
                                </span>
                                <span
                                    class="bg-white border border-gray-200 text-gray-600 px-2 py-0.5 rounded-md text-xs font-mono">
                                    {{ $project->tasks->where('status', 'pending')->count() }}
                                </span>
                            </h4>
                            <div class="space-y-3 flex-1 min-h-[150px]" x-on:drop="updateTaskStatus($event, 'pending')"
                                x-on:dragover.prevent>
                                @forelse($project->tasks->where('status', 'pending') as $task)
                                    @include('projects.partials.task-card', ['task' => $task])
                                @empty
                                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-6 text-center">
                                        <p class="text-xs text-gray-400">No pending tasks</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- In Progress Column -->
                        <div class="flex flex-col bg-indigo-50/30 rounded-xl border border-indigo-100/60 p-4 h-full">
                            <h4
                                class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                    </span>
                                    In Progress
                                </span>
                                <span
                                    class="bg-indigo-100/50 border border-indigo-200 text-indigo-700 px-2 py-0.5 rounded-md text-xs font-mono">
                                    {{ $project->tasks->where('status', 'in_progress')->count() }}
                                </span>
                            </h4>
                            <div class="space-y-3 flex-1 min-h-[150px]"
                                x-on:drop="updateTaskStatus($event, 'in_progress')" x-on:dragover.prevent>
                                @forelse($project->tasks->where('status', 'in_progress') as $task)
                                    @include('projects.partials.task-card', ['task' => $task])
                                @empty
                                    <div class="border-2 border-dashed border-indigo-100 rounded-lg p-6 text-center">
                                        <p class="text-xs text-indigo-300">No active tasks</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Done Column -->
                        <div class="flex flex-col bg-green-50/30 rounded-xl border border-green-100/60 p-4 h-full">
                            <h4
                                class="text-xs font-bold text-green-900 uppercase tracking-wider mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Done
                                </span>
                                <span
                                    class="bg-green-100/50 border border-green-200 text-green-700 px-2 py-0.5 rounded-md text-xs font-mono">
                                    {{ $project->tasks->where('status', 'done')->count() }}
                                </span>
                            </h4>
                            <div class="space-y-3 flex-1 min-h-[150px]" x-on:drop="updateTaskStatus($event, 'done')"
                                x-on:dragover.prevent>
                                @forelse($project->tasks->where('status', 'done') as $task)
                                    @include('projects.partials.task-card', ['task' => $task])
                                @empty
                                    <div class="border-2 border-dashed border-green-100 rounded-lg p-6 text-center">
                                        <p class="text-xs text-green-300">No completed tasks</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Integrations Tab -->
                <div x-show="tab === 'integrations'" style="display: none;" x-data="{ openUpload: false }"
                    class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Plugins & Resources</h3>
                            <p class="text-sm text-gray-500">Store API documentation, plugins, and integration files.
                            </p>
                        </div>
                        <button @click="openUpload = true"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Upload File
                        </button>
                    </div>

                    @if($project->integrations && $project->integrations->count() > 0)
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($project->integrations as $integration)
                                <div
                                    class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-all group relative">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="p-3 bg-indigo-50 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-base font-semibold text-gray-900 leading-tight">
                                                    {{ $integration->name }}
                                                </h4>
                                                @if($integration->documentation_url)
                                                    <a href="{{ $integration->documentation_url }}" target="_blank"
                                                        class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center mt-1 font-medium">
                                                        <span>Documentation</span>
                                                        <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex space-x-1">
                                            <a href="{{ route('integrations.download', $integration) }}"
                                                class="text-gray-400 hover:text-indigo-600 p-1.5 hover:bg-indigo-50 rounded-md transition-colors"
                                                title="Download">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            <a href="#"
                                                class="group inline-flex items-center border-b-2 py-4 px-1 text-sm font-medium"
                                                :class="tab === 'terminal' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                                @click.prevent="tab = 'terminal'">
                                                <svg class="-ml-0.5 mr-2 h-5 w-5"
                                                    :class="tab === 'terminal' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Terminal
                                            </a>
                                            <a href="#"
                                                class="group inline-flex items-center border-b-2 py-4 px-1 text-sm font-medium"
                                                :class="tab === 'files' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                                @click.prevent="tab = 'files'">
                                                <svg class="-ml-0.5 mr-2 h-5 w-5"
                                                    :class="tab === 'files' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Files
                                            </a>
                                            <form action="{{ route('integrations.destroy', $integration) }}" method="POST"
                                                onsubmit="return confirm('Delete this file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-gray-400 hover:text-red-500 p-1.5 hover:bg-red-50 rounded-md transition-colors"
                                                    title="Delete">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    @if($integration->test_cards)
                                        <div class="mt-4 pt-4 border-t border-gray-100">
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                                                Notes / Test Cards</p>
                                            <div
                                                class="bg-gray-50 rounded-md p-3 text-xs text-gray-600 font-mono whitespace-pre-wrap max-h-32 overflow-y-auto border border-gray-100">
                                                {{ $integration->test_cards }}
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-4 text-xs text-gray-400 text-right font-medium">
                                        Uploaded {{ $integration->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center rounded-xl border-2 border-dashed border-gray-300 p-12 bg-gray-50/50">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No plugins uploaded</h3>
                            <p class="mt-1 text-sm text-gray-500">Upload plugins or documentation to share with the team.
                            </p>
                            <div class="mt-6">
                                <button @click="openUpload = true"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Upload File
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Upload Modal -->
                    <div x-show="openUpload" class="relative z-[60]" style="display: none;">
                        <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm" @click="openUpload = false"></div>
                        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div
                                class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div
                                    class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                    <form action="{{ route('integrations.store') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                                        <div class="space-y-4">
                                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Upload Resource
                                            </h3>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium leading-6 text-gray-900">Name</label>
                                                <input type="text" name="name" required
                                                    class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium leading-6 text-gray-900">File</label>
                                                <input type="file" name="file" required
                                                    class="mt-2 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium leading-6 text-gray-900">Documentation
                                                    URL (Optional)</label>
                                                <input type="url" name="documentation_url" placeholder="https://"
                                                    class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium leading-6 text-gray-900">Notes /
                                                    Test Cards (Optional)</label>
                                                <textarea name="test_cards" rows="3"
                                                    class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
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

                <!-- Logs Tab -->
                <div x-show="tab === 'logs'" style="display: none;"
                    class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Access Logs</h3>
                            <p class="text-sm text-gray-500">Audit history of shared credential access.</p>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Time</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Resource</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        IP Address</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User Agent</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($accessLogs as $log)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->created_at->format('M d, Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                            Accessed Secret
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->vaultShare->name ?? 'Unknown Share' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                            {{ $log->ip_address }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 truncate max-w-xs"
                                            title="{{ $log->user_agent }}">
                                            {{ $log->user_agent }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                            No access logs recorded yet. <br>
                                            <span class="text-xs text-gray-400">Values accessed via Shared Links will appear
                                                here.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Terminal Tab ->
                <!-- Terminal Tab -->
                <div x-show="tab === 'terminal'" style="display: none;"
                    class="animate-in fade-in slide-in-from-bottom-4 duration-500" x-data="{
                        history: [],
                        command: '',
                        cwd: '~',
                        loading: false,
                        historyIndex: -1,
                        commandHistory: [],
                        
                        async execute() {
                            if (!this.command.trim()) return;
                            
                            const cmd = this.command;
                            this.history.push({ type: 'input', content: cmd, cwd: this.cwd });
                            this.command = '';
                            this.loading = true;
                            
                            // Add to history for up/down arrows
                            this.commandHistory.push(cmd);
                            this.historyIndex = this.commandHistory.length;
                            
                            this.scrollToBottom();

                            try {
                                const response = await fetch('{{ route('projects.terminal.execute', $project) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ command: cmd, cwd: this.cwd })
                                });

                                if (!response.ok) {
                                    const err = await response.json();
                                    throw new Error(err.error || 'Execution failed');
                                }

                                const data = await response.json();
                                this.history.push({ type: 'output', content: data.output });
                                this.cwd = data.cwd;
                            } catch (e) {
                                this.history.push({ type: 'error', content: e.message });
                            } finally {
                                this.loading = false;
                                this.scrollToBottom();
                                // Focus input
                                $nextTick(() => { $refs.cmdInput.focus(); });
                            }
                        },
                        
                        scrollToBottom() {
                            $nextTick(() => {
                                const term = this.$refs.terminal;
                                term.scrollTop = term.scrollHeight;
                            });
                        },
                        
                        handleKeydown(e) {
                            if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                if (this.historyIndex > 0) {
                                    this.historyIndex--;
                                    this.command = this.commandHistory[this.historyIndex];
                                }
                            } else if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                if (this.historyIndex < this.commandHistory.length - 1) {
                                    this.historyIndex++;
                                    this.command = this.commandHistory[this.historyIndex];
                                } else {
                                    this.historyIndex = this.commandHistory.length;
                                    this.command = '';
                                }
                            }
                        }
                    }">

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Remote Terminal</h3>
                            <p class="text-sm text-gray-500">Execute commands on remote server via SSH. <span
                                    class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">Stateless</span>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="history = []"
                                class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
                        </div>
                    </div>

                    <div class="bg-gray-900 rounded-xl shadow-lg border border-gray-800 overflow-hidden font-mono text-xs p-4"
                        @click="$refs.cmdInput.focus()">
                        <div x-ref="terminal" class="h-[500px] overflow-y-auto space-y-1 mb-2">
                            <template x-for="(entry, index) in history" :key="index">
                                <div>
                                    <template x-if="entry.type === 'input'">
                                        <div class="text-green-400 font-bold mt-2">
                                            <span class="text-blue-400" x-text="entry.cwd"></span> $ <span
                                                x-text="entry.content"></span>
                                        </div>
                                    </template>
                                    <template x-if="entry.type === 'output'">
                                        <div class="text-gray-300 whitespace-pre-wrap leading-relaxed pl-2"
                                            x-text="entry.content"></div>
                                    </template>
                                    <template x-if="entry.type === 'error'">
                                        <div class="text-red-400 pl-2" x-text="entry.content"></div>
                                    </template>
                                </div>
                            </template>

                            <div x-show="loading" class="text-gray-500 animate-pulse pl-2">Executing...</div>

                            <div class="flex items-center text-green-400 font-bold mt-2">
                                <span class="text-blue-400 mr-2" x-text="cwd"></span> $
                                <input type="text" x-ref="cmdInput" x-model="command" @keydown="handleKeydown"
                                    @keydown.enter="execute()"
                                    class="bg-transparent border-none outline-none text-gray-100 w-full focus:ring-0 p-0 ml-1"
                                    autofocus autocomplete="off" spellcheck="false">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Tab -->
                <div x-show="tab === 'time'" style="display: none;"
                    class="animate-in fade-in slide-in-from-bottom-4 duration-500" x-data="{
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

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Time Tracking</h3>
                            <p class="text-sm text-gray-500">Monitor time spent on tasks and general activities.</p>
                        </div>
                        <div class="text-sm text-gray-500 font-medium">
                            Total: <span
                                class="text-indigo-600 font-bold">{{ gmdate('H:i:s', $project->timeEntries()->sum('duration')) }}</span>
                        </div>
                    </div>



                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        User</th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Task / Description</th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Date</th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Duration</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span
                                            class="sr-only">Edit</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($project->timeEntries()->with('user', 'task')->latest()->get() as $entry)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td
                                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 flex items-center gap-2">
                                            <div
                                                class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700">
                                                {{ substr($entry->user->name, 0, 1) }}
                                            </div>
                                            {{ $entry->user->name }}
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            @if($entry->task)
                                                <div class="font-medium text-gray-900 mb-0.5">{{ $entry->task->title }}</div>
                                            @endif
                                            <span class="text-gray-500">{{ $entry->description }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $entry->start_time->format('M d, H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-mono text-gray-600">
                                            @if($entry->duration)
                                                {{ gmdate('H:i:s', $entry->duration) }}
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20 animate-pulse">Running</span>
                                            @endif
                                        </td>
                                        <td
                                            class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            @if(auth()->id() === $entry->user_id || auth()->user()->isAdmin())
                                                <button @click="edit({{ $entry }})"
                                                    class="text-indigo-600 hover:text-indigo-900 font-semibold">Edit</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-sm text-gray-400 italic">No time
                                            entries recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Edit Modal -->
                    <div x-show="showModal" class="relative z-[60]" style="display: none;">
                        <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm" @click="showModal = false"></div>
                        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div
                                class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div
                                    class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                    <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                                        <button type="button" @click="showModal = false"
                                            class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                                            <span class="sr-only">Close</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                            <h3 class="text-base font-semibold leading-6 text-gray-900">Edit Time Entry
                                            </h3>
                                            <div class="mt-4">
                                                <form :action="formAction" method="POST" id="editFormProject">
                                                    @csrf @method('PUT')
                                                    <div class="space-y-4">
                                                        <div>
                                                            <label
                                                                class="block text-sm font-medium text-gray-700">Description</label>
                                                            <input type="text" name="description"
                                                                x-model="formData.description"
                                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-4">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">Start
                                                                    Time</label>
                                                                <input type="datetime-local" name="start_time"
                                                                    x-model="formData.start_time" required
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700">End
                                                                    Time</label>
                                                                <input type="datetime-local" name="end_time"
                                                                    x-model="formData.end_time"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-sm font-medium text-gray-700">Duration
                                                                Override (Minutes)</label>
                                                            <input type="number" name="duration_minutes"
                                                                x-model="formData.duration_minutes"
                                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                placeholder="Optional">
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse justify-between pt-4 border-t border-gray-100">
                                        <button type="submit" form="editFormProject"
                                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Save</button>
                                        <form :action="deleteAction" method="POST"
                                            onsubmit="return confirm('Are you sure?');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 sm:mt-0 sm:w-auto">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- File Manager Tab -->
        <div x-show="tab === 'files'" class="mt-6">
            <div class="bg-white shadow sm:rounded-lg" x-data="{
            path: '.',
            files: [],
            loading: false,
            currentFile: null,
            fileContent: '',
            editorLoading: false,
            saving: false,
            init() {
                this.loadFiles();
            },
            async loadFiles() {
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('projects.files.index', $project) }}?path=${encodeURIComponent(this.path)}`);
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);
                    this.path = data.path;
                    this.files = data.files;
                } catch (e) {
                    alert(e.message);
                } finally {
                    this.loading = false;
                }
            },
            async openDirectory(dirName) {
                if (dirName === '..') {
                    // Logic to go up is handled by backend returning .. but visual nicety
                    // Actually rawlist returns .. so we just click it
                }
                const newPath = this.path === '/' ? '/' + dirName : this.path + '/' + dirName;
                this.path = newPath;
                this.loadFiles();
            },
            async goUp() {
                // Determine parent
                const parts = this.path.split('/').filter(p => p);
                parts.pop();
                this.path = parts.length === 0 ? '/' : '/' + parts.join('/');
                this.loadFiles();
            },
            async editFile(file) {
                this.currentFile = file;
                this.editorLoading = true;
                try {
                    const response = await fetch(`{{ route('projects.files.show', $project) }}?path=${encodeURIComponent(file.path)}`);
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);
                    this.fileContent = data.content;
                } catch (e) {
                    alert(e.message);
                    this.currentFile = null;
                } finally {
                    this.editorLoading = false;
                }
            },
            async saveFile() {
                if (!this.currentFile) return;
                this.saving = true;
                try {
                    const response = await fetch(`{{ route('projects.files.update', $project) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            path: this.currentFile.path,
                            content: this.fileContent
                        })
                    });
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);
                    alert('File saved successfully');
                } catch (e) {
                    alert('Error saving file: ' + e.message);
                } finally {
                    this.saving = false;
                }
            },
            formatSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }">
                <!-- Breadcrumb / Toolbar -->
                <div
                    class="border-b border-gray-200 px-4 py-4 sm:px-6 flex justify-between items-center bg-gray-50 rounded-t-lg">
                    <div class="flex items-center space-x-2 overflow-hidden">
                        <button @click="loadFiles" class="p-1 hover:bg-gray-200 rounded">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                        </button>
                        <button @click="goUp" class="p-1 hover:bg-gray-200 rounded"
                            :disabled="path === '/' || path === '.'">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                        </button>
                        <span class="font-mono text-sm text-gray-700 truncate" x-text="path"></span>
                    </div>
                </div>

                <div class="flex h-[600px]">
                    <!-- File List -->
                    <div class="w-1/3 border-r border-gray-200 overflow-y-auto bg-white">
                        <template x-if="loading">
                            <div class="p-4 text-center text-gray-500">Loading...</div>
                        </template>
                        <ul role="list" class="divide-y divide-gray-100">
                            <template x-for="file in files" :key="file.name">
                                <li class="relative flex justify-between gap-x-6 px-4 py-3 hover:bg-gray-50 cursor-pointer"
                                    @click="file.type === 'directory' ? openDirectory(file.name) : editFile(file)"
                                    :class="currentFile && currentFile.name === file.name ? 'bg-indigo-50' : ''">
                                    <div class="flex min-w-0 gap-x-4">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold leading-6 text-gray-900 flex items-center">
                                                <svg x-show="file.type === 'directory'"
                                                    class="w-4 h-4 mr-2 text-yellow-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z">
                                                    </path>
                                                </svg>
                                                <svg x-show="file.type === 'file'" class="w-4 h-4 mr-2 text-gray-400"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span x-text="file.name"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                        <p class="text-xs leading-5 text-gray-500"
                                            x-text="file.type === 'file' ? formatSize(file.size) : ''"></p>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <!-- Editor -->
                    <div class="w-2/3 flex flex-col bg-gray-50">
                        <template x-if="!currentFile">
                            <div class="flex-1 flex items-center justify-center text-gray-400">
                                Select a file to view/edit
                            </div>
                        </template>
                        <template x-if="currentFile">
                            <div class="flex-1 flex flex-col h-full">
                                <div
                                    class="bg-gray-100 border-b border-gray-200 px-4 py-2 flex justify-between items-center">
                                    <span class="font-medium text-gray-700" x-text="currentFile.name"></span>
                                    <button @click="saveFile"
                                        class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-500 disabled:opacity-50"
                                        :disabled="saving">
                                        <span x-show="!saving">Save</span>
                                        <span x-show="saving">Saving...</span>
                                    </button>
                                </div>
                                <div class="flex-1 relative">
                                    <template x-if="editorLoading">
                                        <div class="absolute inset-0 bg-white/50 flex items-center justify-center z-10">
                                            Loading content...</div>
                                    </template>
                                    <textarea x-model="fileContent"
                                        class="w-full h-full p-4 font-mono text-sm bg-white focus:outline-none resize-none"
                                        spellcheck="false"></textarea>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <!-- Terminal Tab -->
        <div x-show="tab === 'terminal'" class="mt-6">
            <div class="bg-white shadow sm:rounded-lg" x-data="{
            command: '',
            output: '',
            history: [],
            historyIndex: -1,
            loading: false,
            cwd: '/', // Current working directory
            startTerminal() {
                this.output = 'Welcome to the terminal. Type `help` for a list of commands.\n';
                this.executeCommand('pwd'); // Get initial CWD
            },
            async executeCommand(cmd = this.command) {
                if (!cmd.trim()) return;

                this.output += `\\n$ ${cmd}\\n`;
                this.loading = true;

                if (this.history[this.history.length - 1] !== cmd) {
                    this.history.push(cmd);
                }
                this.historyIndex = this.history.length; // Reset history index

                try {
                    const response = await fetch(`{{ route('projects.terminal.execute', $project) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            command: cmd,
                            cwd: this.cwd
                        })
                    });
                    const data = await response.json();

                    if (data.error) {
                        this.output += `Error: ${data.error}\\n`;
                    } else {
                        this.output += data.output;
                        if (data.newCwd) {
                            this.cwd = data.newCwd;
                        }
                    }
                } catch (e) {
                    this.output += `Client-side error: ${e.message}\\n`;
                } finally {
                    this.command = '';
                    this.loading = false;
                    this.$nextTick(() => {
                        this.$refs.terminalOutput.scrollTop = this.$refs.terminalOutput.scrollHeight;
                    });
                }
            },
            historyUp() {
                if (this.history.length === 0) return;
                if (this.historyIndex > 0) {
                    this.historyIndex--;
                    this.command = this.history[this.historyIndex];
                }
            },
            historyDown() {
                if (this.history.length === 0) return;
                if (this.historyIndex < this.history.length - 1) {
                    this.historyIndex++;
                    this.command = this.history[this.historyIndex];
                } else if (this.historyIndex === this.history.length - 1) {
                    this.historyIndex++;
                    this.command = '';
                }
            },
            autocomplete() {
                if (!this.command) return;
                
                fetch('{{ route('projects.terminal.autocomplete', $project) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        input: this.command,
                        cwd: this.cwd
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.length === 1) {
                        const match = data[0];
                        const parts = this.command.split(' ');
                        const lastPart = parts.pop();
                        
                        // If match completes lastPart
                        if (match.startsWith(lastPart)) {
                             parts.push(match);
                             this.command = parts.join(' ');
                        } else {
                            parts.push(match);
                            this.command = parts.join(' ');
                        }
                    } else if (data.length > 1) {
                         this.output += `\n${data.join('  ')}\n`;
                    }
                });
            }
        }" x-init="$watch('tab', value => {
            if (value === 'terminal' && output === '') {
                 startTerminal();
            }
        })">
                <!-- Terminal Display -->
                <div class="h-96 w-full bg-black text-green-400 font-mono p-4 rounded-t-lg overflow-y-auto whitespace-pre-wrap text-sm border-b border-gray-700 shadow-inner"
                    x-ref="terminalOutput" x-html="output"></div>

                <!-- Command Input -->
                <div class="flex items-center bg-gray-900 rounded-b-lg p-2 border-t border-gray-700">
                    <span class="text-green-500 font-mono mr-2 ml-2">$</span>
                    <input type="text" x-model="command" @keydown.enter="executeCommand" @keydown.up.prevent="historyUp"
                        @keydown.down.prevent="historyDown" @keydown.tab.prevent="autocomplete"
                        class="flex-1 bg-transparent border-none text-gray-200 focus:ring-0 font-mono text-sm placeholder-gray-600"
                        placeholder="Type command..." autofocus>
                    <div x-show="loading" class="animate-spin h-4 w-4 text-green-500 mr-2">
                        <svg class="h-full w-full" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>

<!-- Scripts for D&D -->
<script>
    function updateTaskStatus(event, status) {
        event.preventDefault();
        const taskId = event.dataTransfer.getData(' text/plain'); fetch(`/tasks/${taskId}`, {
            method: 'POST', headers:
            {
                'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept'
                    : 'application/json'
            }, body: JSON.stringify({ _method: 'PUT', status: status })
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Failed to update task status');
            }
        });
    }
</script>