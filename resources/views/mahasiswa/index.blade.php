<x-app-layout>
    <x-page-header
        title="Manajemen Mahasiswa"
        description="Kelola Data Mahasiswa, import Excel, dan export Excel."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Data Akademik', 'url' => route('mahasiswa.index')],
            ['label' => 'Manajemen Mahasiswa'],
        ]"
    />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Mahasiswa</h3>
                            <p class="text-sm text-gray-500">Total Mahasiswa: {{ $mahasiswas->count() }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('mahasiswa.export') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                <i class="fa-solid fa-file-export mr-2"></i> Export
                            </a>
                            <a href="{{ route('mahasiswa.create') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                <i class="fa-solid fa-plus mr-2"></i> Tambah
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3 rounded-lg border border-gray-200 p-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Import Excel</label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700" required>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            <i class="fa-solid fa-file-import mr-2"></i> Import
                        </button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="{{ $mahasiswas->isNotEmpty() ? 'js-datatable' : '' }} min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">NIM</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Prodi</th>
                                    <th class="px-4 py-3">Angkatan</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">No HP</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($mahasiswas as $mahasiswa)
                                    <tr class="text-sm text-gray-700">
                                        <td class="px-4 py-4 font-medium">{{ $mahasiswa->nim }}</td>
                                        <td class="px-4 py-4">{{ $mahasiswa->nama }}</td>
                                        <td class="px-4 py-4">{{ $mahasiswa->programStudi?->program_studi ?? $mahasiswa->program_studi ?? '-' }}</td>
                                        <td class="px-4 py-4">{{ $mahasiswa->angkatan }}</td>
                                        <td class="px-4 py-4">{{ $mahasiswa->user?->email ?? '-' }}</td>
                                        <td class="px-4 py-4">{{ $mahasiswa->no_hp ?? '-' }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('mahasiswa.edit', $mahasiswa) }}" class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('mahasiswa.destroy', $mahasiswa) }}" method="POST" onsubmit="return confirm('Hapus data mahasiswa ini?')">
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
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data mahasiswa.</td>
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