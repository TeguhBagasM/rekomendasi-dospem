<x-app-layout>
    <x-page-header
        title="Permission Management"
        description="Kelola permission yang akan digunakan di role."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Akses User', 'url' => route('users.index')],
            ['label' => 'Permission Management'],
        ]"
    />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Permission</h3>
                            <p class="text-sm text-gray-500">Kelola permission yang akan digunakan di role.</p>
                        </div>

                        <a href="{{ route('permissions.create') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Permission
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="{{ $permissions->isNotEmpty() ? 'js-datatable' : '' }} min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($permissions as $permission)
                                    <tr class="text-sm text-gray-700">
                                        <td class="px-4 py-4 font-medium">{{ $permission->name }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('permissions.edit', $permission) }}" class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Hapus permission ini?')">
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
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada permission.</td>
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