<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $plugin->name }}
            </h2>
            <form action="{{ route('plugins.processQueue') }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm rounded-md shadow-sm font-medium">
                    Run Worker (Process Queue)
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Plugin Details -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Plugin Details</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $plugin->description }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        Latest:
                        @if($plugin->latestVersion)
                            <span class="font-semibold">{{ $plugin->latestVersion->version }}</span>
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Version History -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Version History</h3>
                    <ul class="divide-y divide-gray-200">
                        @foreach($plugin->versions as $version)
                            <li class="py-4">
                                <div class="flex justify-between items-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $version->version }}</div>
                                    <div class="flex items-center space-x-4">
                                        <div class="text-sm text-gray-500">{{ $version->created_at->format('M d, Y H:i') }}
                                        </div>
                                        <a href="{{ route('plugin-versions.download', $version) }}"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm">Download<span
                                                class="sr-only">, version {{ $version->version }}</span></a>
                                    </div>
                                </div>
                                @if($version->changelog)
                                    <p class="mt-1 text-sm text-gray-600">{{ $version->changelog }}</p>
                                @endif
                                <div class="mt-1 text-xs text-gray-400 break-all">{{ $version->file_path }}</div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Upload New Version</h4>
                        <form action="{{ route('plugins.uploadVersion', $plugin) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <x-text-input type="text" name="version" class="block w-full text-sm"
                                        placeholder="Version (e.g. 1.0.2)" required />
                                </div>
                                <div>
                                    <input type="file" name="file"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        required />
                                </div>
                                <div>
                                    <textarea name="changelog" class="block w-full text-sm border-gray-300 rounded-md"
                                        placeholder="Changelog" rows="2"></textarea>
                                </div>
                                <x-primary-button class="w-full justify-center">Upload Version</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column Stack -->
                <div class="space-y-6">
                    <!-- Associated Projects -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Associated Projects</h3>
                            <div class="flex items-center gap-2">
                                @if($plugin->enable_auto_update && $plugin->update_endpoint)
                                <button type="button" onclick="openLiveUpdateModal('update')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 text-xs rounded-md shadow-sm">
                                    Update All Projects
                                </button>
                                @endif

                                @if($plugin->revert_endpoint)
                                <button type="button" onclick="openLiveUpdateModal('revert')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 text-xs rounded-md shadow-sm">
                                    Revert All Projects
                                </button>
                                @endif
                            </div>
                        </div>

                    <ul class="divide-y divide-gray-200 mb-6">
                        @foreach($plugin->projects as $project)
                            @php
                                $installedVersionId = $project->pivot->plugin_version_id;
                                $installedVersion = \App\Models\PluginVersion::find($installedVersionId);
                            @endphp
                            <li class="py-4 flex justify-between items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Installed: {{ $installedVersion->version ?? 'Unknown' }}
                                        @if($plugin->latestVersion && $installedVersionId !== $plugin->latestVersion->id)
                                            <span class="text-red-500">(Outdated)</span>
                                        @else
                                            <span class="text-green-500">(Latest)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($plugin->latestVersion && $installedVersionId !== $plugin->latestVersion->id)
                                        <form action="{{ route('plugins.upgrade', [$project, $plugin]) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded hover:bg-indigo-200">
                                                Upgrade to {{ $plugin->latestVersion->version }}
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('plugins.remove', [$project, $plugin]) }}" method="POST"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-xs text-red-600 hover:text-red-900">Remove</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Assign to Project</h4>
                        <form action="{{ route('plugins.assign', $plugin) }}" method="POST">
                            @csrf
                            <div class="flex gap-2">
                                <select name="project_id" class="block w-full text-sm border-gray-300 rounded-md">
                                    @foreach(\App\Models\Project::whereDoesntHave('plugins', function ($q) use ($plugin) {
                                            $q->where('plugin_id', $plugin->id);
                                        })->get() as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <x-primary-button>Assign</x-primary-button>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>

                <!-- Plugin Settings -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Plugin Settings</h3>
                    <form action="{{ route('plugins.update', $plugin) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" class="block mt-1 w-full text-sm" type="text" name="name" :value="old('name', $plugin->name)" required />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm" rows="2">{{ old('description', $plugin->description) }}</textarea>
                            </div>

                            <div class="border-t border-gray-100 pt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('Auto Update Configuration') }}</h4>
                                
                                <div class="mb-3">
                                    <x-input-label for="folder_name" :value="__('Plugin Folder Name')" />
                                    <x-text-input id="folder_name" class="block mt-1 w-full text-sm" type="text" name="folder_name" :value="old('folder_name', $plugin->folder_name)" placeholder="e.g. advanced-custom-fields-pro" />
                                    <p class="text-xs text-gray-500 mt-1">Exact name of the plugin's folder on WordPress sites. Used for downloading updates publicly.</p>
                                </div>

                                <div class="mb-3">
                                    <label for="enable_auto_update" class="inline-flex items-center">
                                        <input id="enable_auto_update" type="checkbox" name="enable_auto_update" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('enable_auto_update', $plugin->enable_auto_update) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Enable Auto Update') }}</span>
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">If enabled, uploading a new version will push an update request to associated projects.</p>
                                </div>

                                <div class="mb-3">
                                    <x-input-label for="update_endpoint" :value="__('Update Endpoint')" />
                                    <x-text-input id="update_endpoint" class="block mt-1 w-full text-sm" type="text" name="update_endpoint" :value="old('update_endpoint', $plugin->update_endpoint)" placeholder="e.g. wp-json/my-plugin/v1/update" />
                                </div>

                                <div class="mb-3">
                                    <x-input-label for="revert_endpoint" :value="__('Revert Endpoint')" />
                                    <x-text-input id="revert_endpoint" class="block mt-1 w-full text-sm" type="text" name="revert_endpoint" :value="old('revert_endpoint', $plugin->revert_endpoint)" placeholder="e.g. wp-json/my-plugin/v1/revert" />
                                </div>

                                <div class="mb-3">
                                    <x-input-label for="update_secret_key" :value="__('Update Secret Key / Token')" />
                                    <x-text-input id="update_secret_key" class="block mt-1 w-full text-sm" type="text" name="update_secret_key" :value="old('update_secret_key', $plugin->update_secret_key)" placeholder="e.g. SHA_API_KEY" />
                                </div>
                            </div>

                            <div class="pt-2">
                                <x-primary-button class="w-full justify-center">{{ __('Save Settings') }}</x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Live Update Modal -->
<div id="live-update-modal" class="fixed inset-0 z-50 hidden bg-gray-500 bg-opacity-75 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[80vh] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900" id="live-update-title">Processing...</h3>
            <button type="button" onclick="closeLiveUpdateModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="px-6 py-4 overflow-y-auto flex-1">
            <ul id="live-update-list" class="divide-y divide-gray-200">
                @foreach($plugin->projects as $project)
                <li class="py-3 flex justify-between items-center" data-project-id="{{ $project->id }}">
                    <span class="text-sm font-medium text-gray-900">{{ $project->name }}</span>
                    <span class="text-sm text-gray-500 project-status">Waiting...</span>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" id="start-process-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Start Process
            </button>
        </div>
    </div>
</div>

<script>
    let currentAction = 'update';
    const pluginId = {{ $plugin->id }};
    const csrfToken = '{{ csrf_token() }}';

    function openLiveUpdateModal(action) {
        currentAction = action;
        document.getElementById('live-update-modal').classList.remove('hidden');
        document.getElementById('live-update-title').innerText = action === 'update' ? 'Live Update Progress' : 'Live Revert Progress';
        
        // Reset statuses
        document.querySelectorAll('#live-update-list li').forEach(li => {
            const statusSpan = li.querySelector('.project-status');
            statusSpan.innerHTML = 'Waiting...';
            statusSpan.className = 'text-sm text-gray-500 project-status';
        });

        const startBtn = document.getElementById('start-process-btn');
        startBtn.innerText = 'Start ' + (action === 'update' ? 'Update' : 'Revert');
        startBtn.disabled = false;
        startBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        startBtn.onclick = () => startProcess();
    }

    function closeLiveUpdateModal() {
        document.getElementById('live-update-modal').classList.add('hidden');
    }

    async function startProcess() {
        const startBtn = document.getElementById('start-process-btn');
        startBtn.innerText = 'Processing...';
        startBtn.disabled = true;
        startBtn.classList.add('opacity-50', 'cursor-not-allowed');

        const listItems = document.querySelectorAll('#live-update-list li');
        
        for (let i = 0; i < listItems.length; i++) {
            const li = listItems[i];
            const projectId = li.getAttribute('data-project-id');
            const statusSpan = li.querySelector('.project-status');
            
            // Set Loading State
            statusSpan.innerHTML = '<span class="flex items-center gap-1"><svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...</span>';
            statusSpan.className = 'text-sm text-blue-600 project-status font-medium';

            // Scroll item into view smoothly if list is long
            li.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            try {
                const response = await fetch(`/plugins/${pluginId}/trigger-project/${projectId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: currentAction })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    statusSpan.innerHTML = '✅ OK';
                    statusSpan.className = 'text-sm text-green-600 project-status font-medium';
                } else {
                    statusSpan.innerHTML = '❌ ' + (data.message || 'Failed');
                    statusSpan.className = 'text-sm text-red-600 project-status font-medium';
                }
            } catch (error) {
                statusSpan.innerHTML = '❌ Network Error';
                statusSpan.className = 'text-sm text-red-600 project-status font-medium';
            }
        }

        startBtn.innerText = 'Finished';
    }
</script>