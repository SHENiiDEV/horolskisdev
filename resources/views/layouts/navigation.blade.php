<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="font-bold text-xl text-indigo-600">
                        HOROLSKIS DEV
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                        Dashboard
                    </a>
                    <a href="{{ route('clients.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('clients.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                        Clients
                    </a>
                    <a href="{{ route('projects.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('projects.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                        Projects
                    </a>
                    <a href="{{ route('tasks.kanban') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('tasks.kanban') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                        Kanban
                    </a>
                    <a href="{{ route('time-entries.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('time-entries.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                        Time Tracker
                    </a>
                    <a href="{{ route('plugins.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('plugins.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                        Plugins
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="ml-3 relative">
                    <span class="inline-flex rounded-md">
                        <button type="button"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            {{ Auth::user()->name ?? 'User' }}
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</nav>