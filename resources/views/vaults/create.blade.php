<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Add Credential to {{ $project->name }}
            </h2>
            <a href="{{ route('projects.show', $project->id) }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Back onto project</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="credentialForm()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('vaults.store') }}" class="space-y-8">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="type" x-model="type">

                <!-- Type Selection -->
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">1. Choose Credential Type</h3>
                        <p class="mt-1 text-sm text-gray-500">Select the type of service you are adding credentials for.
                        </p>
                    </div>

                    <div class="p-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <!-- WordPress -->
                        <button type="button" @click="applyPreset('wordpress')"
                            class="relative flex flex-col items-center p-4 rounded-xl border-2 transition-all duration-200 hover:bg-gray-50 bg-white"
                            :class="type === 'wordpress' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <span
                                class="h-10 w-10 flex items-center justify-center rounded-lg bg-[#21759b]/10 text-[#21759b]">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.042 21.579c3.085 0 5.8-1.465 7.604-3.72l-3.328-9.155a305.694 305.694 0 00-.73-2.196l-.993 2.846-2.553 7.505h.001v.001c-1.127 3.205-1.922 4.719-1.922 4.719H12.04zM2.38 11.967c0-2.316.828-4.441 2.213-6.095l5.228 15.356c-4.469-1.39-7.441-5.342-7.441-9.26zM13.684.821a11.127 11.127 0 00-11.238 9.39h.564c1.234 0 2.404-.32 2.404-.32.557-.156.634-.87.054-.954 0 0-1.07.126-2.257.126s-2.074-.126-2.074-.126c-.58-.084-.503.798.054.954 0 0 1.05.32 2.237.32h.494l7.009 19.336A11.137 11.137 0 0023.21 11.08c0-.361-.017-.719-.051-1.072h-2.146c-1.332 0-2.435.337-2.435.337-.58.156-.634.87-.054.953 0 0 1.033-.126 2.18-.126 1.147 0 1.942.126 1.942.126.58.084.634-.849.054-.954 0 0-1.16-.337-2.493-.337h-1.037L13.684.82zM15.42 16.35l2.23-6.666 1.45-3.882c.28-.737.56-1.492.56-1.492.155-.54-.3-.884-.712-.625 0 0-1.066.862-2.16 1.576l-4.14 12.35 2.772-1.26z" />
                                </svg>
                            </span>
                            <span class="mt-3 text-sm font-medium text-gray-900">WordPress</span>
                            <div x-show="type === 'wordpress'" class="absolute top-2 right-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <!-- SSH -->
                        <button type="button" @click="applyPreset('ssh')"
                            class="relative flex flex-col items-center p-4 rounded-xl border-2 transition-all duration-200 hover:bg-gray-50 bg-white"
                            :class="type === 'ssh' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <span
                                class="h-10 w-10 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span class="mt-3 text-sm font-medium text-gray-900">SSH / Server</span>
                            <div x-show="type === 'ssh'" class="absolute top-2 right-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <!-- Hosting -->
                        <button type="button" @click="applyPreset('hosting')"
                            class="relative flex flex-col items-center p-4 rounded-xl border-2 transition-all duration-200 hover:bg-gray-50 bg-white"
                            :class="type === 'hosting' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <span
                                class="h-10 w-10 flex items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </span>
                            <span class="mt-3 text-sm font-medium text-gray-900">Hosting</span>
                            <div x-show="type === 'hosting'" class="absolute top-2 right-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <!-- Gmail -->
                        <button type="button" @click="applyPreset('gmail')"
                            class="relative flex flex-col items-center p-4 rounded-xl border-2 transition-all duration-200 hover:bg-gray-50 bg-white"
                            :class="type === 'gmail' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <span class="h-10 w-10 flex items-center justify-center rounded-lg bg-red-100 text-red-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span class="mt-3 text-sm font-medium text-gray-900">Gmail</span>
                            <div x-show="type === 'gmail'" class="absolute top-2 right-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <!-- Database -->
                        <button type="button" @click="applyPreset('database')"
                            class="relative flex flex-col items-center p-4 rounded-xl border-2 transition-all duration-200 hover:bg-gray-50 bg-white"
                            :class="type === 'database' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <span
                                class="h-10 w-10 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 2.21 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                            </span>
                            <span class="mt-3 text-sm font-medium text-gray-900">Database</span>
                            <div x-show="type === 'database'" class="absolute top-2 right-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <!-- Custom -->
                        <button type="button" @click="applyPreset('custom')"
                            class="relative flex flex-col items-center p-4 rounded-xl border-2 transition-all duration-200 hover:bg-gray-50 bg-white"
                            :class="type === 'custom' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <span
                                class="h-10 w-10 flex items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                            </span>
                            <span class="mt-3 text-sm font-medium text-gray-900">Custom</span>
                            <div x-show="type === 'custom'" class="absolute top-2 right-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Fields Section -->
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5">
                    <div
                        class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between ga-4">
                        <div>
                            <h3 class="text-base font-semibold leading-6 text-gray-900">2. Configure Details</h3>
                            <p class="mt-1 text-sm text-gray-500">Fill in the necessary credentials. Values marked as
                                "Secret" will be blurred.</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Credential Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Credential
                                Name</label>
                            <div class="mt-2">
                                <input type="text" name="name" id="name" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="e.g. Production DB, Main Server, Official Gmail">
                            </div>
                        </div>

                        <!-- Dynamic Fields -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Fields</label>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(field, index) in fields" :key="index">
                                    <div
                                        class="flex gap-4 items-start bg-gray-50 p-3 rounded-lg border border-gray-100 group hover:border-indigo-200 transition-colors">
                                        <div class="w-1/3">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
                                            <input type="text" :name="'payload[' + index + '][name]'"
                                                x-model="field.name"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-9"
                                                placeholder="Key">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Value</label>
                                            <div class="relative">
                                                <input
                                                    :type="field.is_secret && !field.show_secret ? 'password' : 'text'"
                                                    :name="'payload[' + index + '][value]'" x-model="field.value"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono h-9 pr-10"
                                                    placeholder="Value">

                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <!-- Toggle Visibility (Optional purely for UI while editing) -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pt-6 flex items-center gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer select-none"
                                                title="Mark as secret to blur by default">
                                                <input type="hidden" :name="'payload[' + index + '][is_secret]'"
                                                    :value="field.is_secret ? '1' : '0'">
                                                <div class="relative inline-flex items-center cursor-pointer"
                                                    @click="field.is_secret = !field.is_secret">
                                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600"
                                                        :class="field.is_secret ? 'bg-indigo-600 after:translate-x-full' : 'bg-gray-200'">
                                                    </div>
                                                </div>
                                                <span class="text-xs font-medium"
                                                    :class="field.is_secret ? 'text-indigo-600' : 'text-gray-500'">Secret</span>
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
                            </div>

                            <button type="button" @click="addField()"
                                class="mt-2 w-full border-2 border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-indigo-500 hover:text-indigo-600 transition-colors text-sm font-medium text-gray-500">
                                + Add Another Field
                            </button>
                        </div>
                    </div>

                    <div
                        class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-x-6 rounded-b-2xl">
                        <button type="button" onclick="history.back()"
                            class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Save Credential
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function credentialForm() {
            return {
                type: 'wordpress',
                fields: [],
                init() {
                    this.applyPreset('wordpress');
                },
                applyPreset(type) {
                    this.type = type;
                    switch (type) {
                        case 'wordpress':
                            this.fields = [
                                { name: 'URL', value: '', is_secret: false },
                                { name: 'Username', value: '', is_secret: false },
                                { name: 'Password', value: '', is_secret: true },
                                { name: 'Admin URL', value: '/wp-admin', is_secret: false }
                            ];
                            break;
                        case 'ssh':
                            this.fields = [
                                { name: 'Host/IP', value: '', is_secret: false },
                                { name: 'Port', value: '22', is_secret: false },
                                { name: 'User', value: 'root', is_secret: false },
                                { name: 'Password', value: '', is_secret: true }
                            ];
                            break;
                        case 'hosting':
                            this.fields = [
                                { name: 'Control Panel URL', value: '', is_secret: false },
                                { name: 'Username', value: '', is_secret: false },
                                { name: 'Password', value: '', is_secret: true }
                            ];
                            break;
                        case 'gmail':
                            this.fields = [
                                { name: 'Email Address', value: '', is_secret: false },
                                { name: 'Password', value: '', is_secret: true },
                                { name: 'Recovery Email', value: '', is_secret: false }
                            ];
                            break;
                        case 'database':
                            this.fields = [
                                { name: 'Host', value: '127.0.0.1', is_secret: false },
                                { name: 'Database Name', value: '', is_secret: false },
                                { name: 'Username', value: '', is_secret: false },
                                { name: 'Password', value: '', is_secret: true },
                                { name: 'Port', value: '3306', is_secret: false }
                            ];
                            break;
                        default:
                            this.fields = [{ name: '', value: '', is_secret: false }];
                    }
                },
                addField() {
                    this.fields.push({ name: '', value: '', is_secret: false });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                }
            }
        }
    </script>
</x-app-layout>