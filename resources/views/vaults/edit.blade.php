<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Edit Credential: {{ $vault->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-2xl border border-gray-100 p-8">
                <form method="POST" action="{{ route('vaults.update', $vault->id) }}" x-data="{
                        type: '{{ $vault->type }}',
                        fields: {{ json_encode($vault->fields) }},
                        addField() {
                            this.fields.push({ name: '', value: '', is_secret: false });
                        },
                        removeField(index) {
                            this.fields.splice(index, 1);
                        }
                    }">
                    @csrf
                    @method('PUT')

                    <div class="space-y-8">
                        <!-- Credential Info -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6 border-b border-gray-100 pb-8">
                            <div class="sm:col-span-4">
                                <label for="name" class="block text-sm font-semibold text-gray-900">Credential
                                    Name</label>
                                <div class="mt-2">
                                    <input type="text" name="name" id="name" value="{{ old('name', $vault->name) }}"
                                        class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50"
                                        rules="required">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="type" class="block text-sm font-semibold text-gray-900">Type</label>
                                <div class="mt-2">
                                    <select id="type" name="type" x-model="type"
                                        class="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50/50">
                                        <option value="wordpress">WordPress</option>
                                        <option value="ssh">SSH / Server</option>
                                        <option value="hosting">Hosting</option>
                                        <option value="gmail">Gmail</option>
                                        <option value="database">Database</option>
                                        <option value="ftp">FTP</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fields -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Fields</h3>
                                <button type="button" @click="addField()"
                                    class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Field
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(field, index) in fields" :key="index">
                                    <div
                                        class="flex gap-4 items-start bg-gray-50 p-4 rounded-xl border border-gray-100 group hover:border-indigo-100 transition-colors">
                                        <div class="w-1/3">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Field
                                                Name</label>
                                            <input type="text" :name="`payload[${index}][name]`" x-model="field.name"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="e.g. Username">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Value</label>
                                            <textarea :name="`payload[${index}][value]`" x-model="field.value" rows="1"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                                                placeholder="Secret value..."></textarea>
                                        </div>
                                        <div class="pt-6 flex items-center space-x-4">
                                            <label class="flex items-center space-x-2 cursor-pointer select-none">
                                                <input type="hidden" :name="`payload[${index}][is_secret]`"
                                                    :value="field.is_secret ? '1' : '0'">
                                                <input type="checkbox" x-model="field.is_secret"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 h-4 w-4">
                                                <span class="text-sm text-gray-600">Secure</span>
                                            </label>
                                            <button type="button" @click="removeField(index)"
                                                class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="fields.length === 0"
                                    class="text-center py-8 text-gray-500 bg-gray-50 rounded-xl border-dashed border-2 border-gray-200">
                                    No fields added yet.
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 flex items-center justify-between border-t border-gray-100">
                            <button type="button"
                                onclick="if(confirm('Are you sure you want to delete this credential?')) document.getElementById('delete-form').submit()"
                                class="text-sm font-semibold text-red-600 hover:text-red-800">
                                Delete Credential
                            </button>
                            <div class="flex items-center gap-x-4">
                                <a href="{{ route('projects.show', ['project' => $vault->project_id]) }}"
                                    class="text-sm font-semibold leading-6 text-gray-500 hover:text-gray-900">Cancel</a>
                                <button type="submit"
                                    class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all transform hover:scale-[1.02]">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <form id="delete-form" action="{{ route('vaults.destroy', $vault->id) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>