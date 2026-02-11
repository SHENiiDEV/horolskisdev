<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $plugin->name }}
        </h2>
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

                <!-- Associated Projects -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Associated Projects</h3>

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
        </div>
    </div>
</x-app-layout>