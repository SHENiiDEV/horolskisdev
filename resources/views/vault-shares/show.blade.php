<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Share - {{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans text-gray-900 antialiased bg-gray-100 min-h-screen flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="mx-auto h-12 w-12 text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-12 h-12">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">Secure Credential</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            You have received a secure shared secret.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md" x-data="{ 
        revealed: false, 
        secret: '', 
        loading: false, 
        copied: false,
        error: null,
        secretType: 'text',
        secretFields: [],
        async reveal() {
            this.loading = true;
            this.error = null;
            try {
                const response = await fetch('{{ route('shares.reveal', $share->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                
                if (!response.ok) throw new Error('Failed to retrieve secret. It may have expired or been viewed already.');
                
                const data = await response.json();
                
                try {
                    const parsed = JSON.parse(data.value);
                    if (Array.isArray(parsed)) {
                        this.secretFields = parsed;
                        this.secretType = 'json';
                        this.secret = parsed.map(f => `${f.name}: ${f.value}`).join('\n');
                    } else {
                        throw new Error('Not a fields array');
                    }
                } catch (e) {
                    this.secret = data.value;
                    this.secretType = 'text';
                }
                
                this.revealed = true;
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        }
    }">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">

            <!-- Initial State -->
            <div x-show="!revealed && !error">
                <div class="rounded-md bg-yellow-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>This secret can only be viewed
                                    <strong>{{ $share->allowed_views - $share->views }}</strong> more time(s).
                                </p>
                                @if($share->expires_at)
                                    <p class="mt-1">It will expire automatically {{ $share->expires_at->diffForHumans() }}.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="button" @click="reveal()" :disabled="loading"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Reveal Secret</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Decrypting...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Error State -->
            <div x-show="error" style="display: none;">
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p x-text="error"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revealed State -->
            <div x-show="revealed" style="display: none;">
                <div class="space-y-4">
                    @if($share->name)
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Name</label>
                            <div class="mt-1">
                                <input type="text" value="{{ $share->name }}" readonly
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 bg-gray-50 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                    @endif

                    <div x-show="secretType === 'text'">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Secret Value</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <textarea x-model="secret" readonly rows="4"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-mono"></textarea>
                        </div>
                    </div>

                    <div x-show="secretType === 'json'">
                        <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Credentials</label>
                        <dl class="space-y-4">
                            <template x-for="(field, index) in secretFields" :key="index">
                                <div class="relative">
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-widest"
                                        x-text="field.name"></dt>
                                    <dd class="mt-1 flex items-center shadow-sm rounded-md">
                                        <input type="text" readonly :value="field.value"
                                            class="block w-full rounded-l-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-mono bg-gray-50">
                                        <button type="button"
                                            @click="navigator.clipboard.writeText(field.value); field.copied = true; setTimeout(() => field.copied = false, 2000)"
                                            class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 bg-white">
                                            <svg x-show="!field.copied" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-4 h-4 text-gray-400">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5" />
                                            </svg>
                                            <svg x-show="field.copied" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-4 h-4 text-green-600">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </button>
                                    </dd>
                                </div>
                            </template>
                        </dl>
                    </div>

                    <div class="flex gap-3">
                        <button type="button"
                            @click="navigator.clipboard.writeText(secret); copied = true; setTimeout(() => copied = false, 2000)"
                            class="flex-1 rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 flex justify-center items-center gap-2">
                            <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5" />
                            </svg>
                            <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-green-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span x-text="copied ? 'Copied!' : 'Copy to Clipboard'"></span>
                        </button>
                    </div>

                    <div class="rounded-md bg-blue-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1 md:flex md:justify-between">
                                <p class="text-sm text-blue-700">Make sure to copy this data now. You may not be able to
                                    view it again.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>