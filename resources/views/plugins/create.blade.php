<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload New Plugin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('plugins.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="version" :value="__('Initial Version')" />
                            <x-text-input id="version" class="block mt-1 w-full" type="text" name="version"
                                :value="old('version')" required placeholder="1.0.0" />
                            <x-input-error :messages="$errors->get('version')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="file" :value="__('Plugin File')" />
                            <input id="file" type="file" name="file"
                                class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                required />
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="changelog" :value="__('Changelog (Optional)')" />
                            <textarea id="changelog" name="changelog"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3">{{ old('changelog') }}</textarea>
                        </div>

                        <div class="border-t border-gray-200 pt-4 mt-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Auto Update Settings') }}</h3>
                            
                            <div class="mb-4">
                                <x-input-label for="folder_name" :value="__('Plugin Folder Name')" />
                                <x-text-input id="folder_name" class="block mt-1 w-full" type="text" name="folder_name"
                                    :value="old('folder_name')" placeholder="e.g. advanced-custom-fields-pro" />
                                <p class="text-xs text-gray-500 mt-1">Exact name of the plugin's folder on WordPress sites. Used for downloading updates publicly.</p>
                                <x-input-error :messages="$errors->get('folder_name')" class="mt-2" />
                            </div>
                            
                            <div class="mb-4">
                                <label for="enable_auto_update" class="inline-flex items-center">
                                    <input id="enable_auto_update" type="checkbox" name="enable_auto_update" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('enable_auto_update') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Enable Auto Update') }}</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">If enabled, uploading a new version will trigger an update request to associated projects.</p>
                            </div>

                            <div class="mb-4">
                                <x-input-label for="update_endpoint" :value="__('Update Endpoint')" />
                                <x-text-input id="update_endpoint" class="block mt-1 w-full" type="text" name="update_endpoint"
                                    :value="old('update_endpoint')" placeholder="e.g. wp-json/my-plugin/v1/update" />
                                <x-input-error :messages="$errors->get('update_endpoint')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="revert_endpoint" :value="__('Revert Endpoint')" />
                                <x-text-input id="revert_endpoint" class="block mt-1 w-full" type="text" name="revert_endpoint"
                                    :value="old('revert_endpoint')" placeholder="e.g. wp-json/my-plugin/v1/revert" />
                                <x-input-error :messages="$errors->get('revert_endpoint')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="update_secret_key" :value="__('Update Secret Key / Token')" />
                                <x-text-input id="update_secret_key" class="block mt-1 w-full" type="text" name="update_secret_key"
                                    :value="old('update_secret_key')" placeholder="e.g. SHA_API_KEY" />
                                <x-input-error :messages="$errors->get('update_secret_key')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Upload Plugin') }}</x-primary-button>
                            <a href="{{ route('plugins.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>