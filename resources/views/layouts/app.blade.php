<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HOROLSKIS DEV') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Premium Form Styles */
        .form-input {
            @apply block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6;
        }

        .form-label {
            @apply block text-sm font-medium leading-6 text-gray-900 mb-2;
        }
    </style>
</head>

<body class="h-full">
    <div x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">

        <!-- Off-canvas menu for mobile -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                    x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in-out duration-300 transform"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                    class="relative mr-16 flex w-full max-w-xs flex-1">

                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" @click="sidebarOpen = false" class="-m-2.5 p-2.5">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Sidebar Component -->
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4 ring-1 ring-white/10">
                        <div class="flex h-16 shrink-0 items-center">
                            <span class="text-xl font-bold text-white">HOROLSKIS DEV</span>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        @include('layouts.sidebar-links')
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center">
                    <span class="text-xl font-bold text-white tracking-wider">HOROLSKIS DEV</span>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                @include('layouts.sidebar-links')
                            </ul>
                        </li>
                        <li class="mt-auto">
                            <div class="flex items-center gap-x-4 px-6 py-3 text-sm font-semibold leading-6 text-white">
                                <div
                                    class="h-8 w-8 rounded-full bg-gray-800 flex items-center justify-center border border-gray-700">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="sr-only">Your profile</span>
                                <span aria-hidden="true">{{ auth()->user()->name ?? 'User' }}</span>
                            </div>
                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}" class="px-6 py-2 pb-4">
                                @csrf
                                <button type="submit" class="text-xs text-gray-400 hover:text-white transition">Log
                                    out</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="lg:pl-72">
            <!-- Top Header (Mobile Toggle + Active Timer) -->
            <div
                class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" @click="sidebarOpen = true" class="-m-2.5 p-2.5 text-gray-700 lg:hidden">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Global Timer Widget (Alpine) -->
                @php
                    $activeTimer = \App\Models\TimeEntry::where('user_id', auth()->id())->whereNull('end_time')->first();
                    $initialSeconds = $activeTimer ? now()->diffInSeconds($activeTimer->start_time) : 0;
                    $isRunning = $activeTimer ? 'true' : 'false';
                    $desc = $activeTimer ? $activeTimer->description : '';
                @endphp
                <div class="flex flex-1 justify-center" x-data
                    x-init="$store.timer.init({{ $initialSeconds }}, {{ $isRunning }}, '{{ addslashes($desc) }}')">

                    <template x-if="$store.timer.running">
                        <div
                            class="bg-yellow-50 border border-yellow-200 rounded-full px-4 py-1.5 text-sm text-yellow-800 flex items-center shadow-sm transition-all duration-300">
                            <span class="animate-pulse mr-2 text-red-500">●</span>
                            <span class="font-medium mr-2 max-w-[150px] truncate"
                                x-text="$store.timer.description"></span>
                            <span class="text-yellow-600 mr-4 font-mono font-bold"
                                x-text="$store.timer.formattedTime"></span>
                            <form action="{{ route('timer.stop') }}" method="POST" class="flex"
                                @submit="$store.timer.stop()">
                                @csrf
                                <button type="submit"
                                    class="text-xs bg-white border border-yellow-300 text-yellow-700 hover:bg-yellow-100 rounded px-2 py-0.5 transition font-semibold">Stop</button>
                            </form>
                        </div>
                    </template>

                    <template x-if="!$store.timer.running">
                        <div class="flex items-center text-gray-400 text-sm italic">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            No active timer
                        </div>
                    </template>
                </div>
            </div>

            <main class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    @if (isset($header))
                        <div class="md:flex md:items-center md:justify-between mb-8">
                            <div class="min-w-0 flex-1">
                                {{ $header }}
                            </div>
                        </div>
                    @endif

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>

</html>