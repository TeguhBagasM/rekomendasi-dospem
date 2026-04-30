<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Role Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Role</h3>
                            <p class="text-sm text-gray-500">Kelola role dan permission yang terpasang.</p>
                        </div>

                        <a href="{{ route('roles.create') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Role
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="js-datatable min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Permissions</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($roles as $role)
                                    <tr class="text-sm text-gray-700">
                                        <td class="px-4 py-4 font-medium">{{ $role->name }}</td>
                                        <td class="px-4 py-4">
                                            @if ($role->permissions->isNotEmpty())
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($role->permissions as $permission)
                                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $permission->name }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">Belum ada permission</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('roles.edit', $role) }}" class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Hapus role ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center font-medium text-red-600 hover:text-red-800">
                                                        <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada role.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>