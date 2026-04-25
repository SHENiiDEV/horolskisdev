<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }} <span class="text-gray-400">/</span> {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            <div class="px-4 py-6 sm:p-8">
                <form method="POST" action="{{ route('users.update', $user->id) }}" class="space-y-12">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base font-semibold leading-7 text-gray-900">User Profile</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Basic account information.</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="sm:col-span-4">
                                <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Full
                                    Name</label>
                                <div class="mt-2">
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        required>
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email
                                    Address</label>
                                <div class="mt-2">
                                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        required>
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base font-semibold leading-7 text-gray-900">Security</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Password and access control.</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="col-span-full" x-data="{ 
                            password: '',
                            generate() {
                                const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
                                let pass = '';
                                for (let i = 0; i < 16; i++) {
                                    pass += chars.charAt(Math.floor(Math.random() * chars.length));
                                }
                                this.password = pass;
                            }
                        }">
                                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">New
                                    Password</label>
                                <div class="mt-2 flex gap-x-4">
                                    <input type="text" name="password" id="password" x-model="password"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="Leave blank to keep current password">
                                    <button type="button" @click="generate()"
                                        class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        Generate
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Only fill this if you want to change the password.
                                </p>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base font-semibold leading-7 text-gray-900">Role & Access</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Define user permissions.</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2"
                            x-data="{ role: '{{ old('role', $user->role) }}' }">
                            <div class="sm:col-span-3">
                                <label for="role" class="block text-sm font-medium leading-6 text-gray-900">Account
                                    Type</label>
                                <div class="mt-2">
                                    <select id="role" name="role" x-model="role"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6">
                                        <option value="admin">Admin</option>
                                        <option value="manager">Manager</option>
                                        <option value="client">Client</option>
                                    </select>
                                </div>
                            </div>

                            <div class="sm:col-span-3" x-show="role === 'client'"
                                style="{{ old('role', $user->role) === 'client' ? '' : 'display: none;' }}">
                                <label for="client_id" class="block text-sm font-medium leading-6 text-gray-900">Client
                                    Company</label>
                                <div class="mt-2">
                                    <select id="client_id" name="client_id"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6">
                                        <option value="">Select Client...</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id', $user->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>

                            <div class="col-span-full">
                                <label class="block text-sm font-medium leading-6 text-gray-900 mb-4">Granular Permissions</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($permissions as $permission)
                                        <div class="relative flex items-start">
                                            <div class="flex h-6 items-center">
                                                <input id="permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}" type="checkbox"
                                                    {{ $user->hasDirectPermission($permission->name) ? 'checked' : '' }}
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                            </div>
                                            <div class="ml-3 text-sm leading-6">
                                                <label for="permission_{{ $permission->id }}" class="font-medium text-gray-900">{{ str_replace('-', ' ', ucfirst($permission->name)) }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-4 text-xs text-gray-500 italic">Note: Permissions assigned directly to the user will override or supplement their Role based permissions.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-x-6">
                        <div class="flex items-center gap-x-6">
                            @if(auth()->id() !== $user->id)
                                <button type="submit" form="delete-form"
                                    class="text-sm font-semibold text-red-600 hover:text-red-500">Delete User</button>
                            @endif
                        </div>
                        <div class="flex items-center gap-x-6">
                            <a href="{{ route('users.index') }}"
                                class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Update User
                            </button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('users.destroy', $user->id) }}" id="delete-form"
                    onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>