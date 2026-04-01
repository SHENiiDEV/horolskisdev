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
                                <form action="{{ route('plugins.updateAllProjects', $plugin) }}" method="POST" onsubmit="return confirm('Are you sure you want to force an update on all associated projects?');">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 text-xs rounded-md shadow-sm">
                                        Update All Projects
                                    </button>
                                </form>
                                @endif

                                @if($plugin->revert_endpoint)
                                <form action="{{ route('plugins.revertAll', $plugin) }}" method="POST" onsubmit="return confirm('Are you sure you want to trigger a revert on all associated projects?');">
                                    @csrf
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 text-xs rounded-md shadow-sm">
                                        Revert All Projects
                                    </button>
                                </form>
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