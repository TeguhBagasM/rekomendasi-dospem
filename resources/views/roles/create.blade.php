<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Role') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <form method="POST" action="{{ route('roles.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="name" value="Nama Role" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <p class="mb-3 text-sm font-medium text-gray-700">Permissions</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(in_array($permission->name, old('permissions', [])))>
                                        <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
                            <x-input-error :messages="$errors->get('permissions.*')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Simpan</x-primary-button>
                            <a href="{{ route('roles.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>