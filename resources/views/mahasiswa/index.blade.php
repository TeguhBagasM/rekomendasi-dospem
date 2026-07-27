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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    {{-- Header & Tombol Aksi --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Mahasiswa</h3>
                            <p class="text-sm text-gray-500">
                                Total Mahasiswa: {{ $mahasiswas->total() }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('mahasiswa.template') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-arrow-down mr-2"></i> Template
                            </a>

                            <a href="{{ route('mahasiswa.export') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-export mr-2"></i> Export Excel
                            </a>

                            <button type="button"
                                    onclick="document.getElementById('modal-import-mahasiswa').classList.remove('hidden')"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-import mr-2"></i> Import Excel
                            </button>

                            <a href="{{ route('mahasiswa.create') }}"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                <i class="fa-solid fa-plus mr-2"></i> Tambah Mahasiswa
                            </a>
                        </div>
                    </div>

                    {{-- Flash Message --}}
                    @if (session('error'))
                        <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Filter --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <form action="{{ route('mahasiswa.index') }}" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                        Cari Nama / NIM
                                    </label>
                                    <input type="text"
                                           id="search"
                                           name="search"
                                           value="{{ request('search') }}"
                                           placeholder="Nama atau NIM..."
                                           class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label for="program_studi_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Program Studi
                                    </label>
                                    <select id="program_studi_id"
                                            name="program_studi_id"
                                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Semua</option>
                                        @foreach ($programStudis as $programStudi)
                                            <option value="{{ $programStudi->id }}"
                                                @selected(request('program_studi_id') == $programStudi->id)>
                                                {{ $programStudi->program_studi }} {{ $programStudi->jenjang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="angkatan" class="block text-sm font-medium text-gray-700 mb-1">
                                        Angkatan
                                    </label>
                                    <select id="angkatan"
                                            name="angkatan"
                                            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Semua</option>
                                        @foreach ($angkatans as $year)
                                            <option value="{{ $year }}"
                                                @selected(request('angkatan') == $year)>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mt-4">
                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Cari
                                </button>
                                <a href="{{ route('mahasiswa.index') }}"
                                   class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    <i class="fa-solid fa-rotate-left mr-2"></i> Reset Filter
                                </a>
                            </div>
                        </form>
                    </div>

                    {{-- Tabel --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">NIM</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Program Studi</th>
                                    <th class="px-4 py-3">Angkatan</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">No HP</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($mahasiswas as $mahasiswa)
                                    <tr class="text-gray-700 hover:bg-gray-50 transition">
                                        {{-- NIM --}}
                                        <td class="px-4 py-4 font-medium">
                                            {{ $mahasiswa->nim }}
                                        </td>

                                        {{-- Nama --}}
                                        <td class="px-4 py-4">
                                            {{ $mahasiswa->nama }}
                                        </td>

                                        {{-- Program Studi --}}
                                        <td class="px-4 py-4">
                                            {{ $mahasiswa->programStudi?->program_studi ?? '-' }} {{ $mahasiswa->programStudi?->jenjang }}
                                        </td>

                                        {{-- Angkatan --}}
                                        <td class="px-4 py-4">
                                            {{ $mahasiswa->angkatan }}
                                        </td>

                                        {{-- Email --}}
                                        <td class="px-4 py-4">
                                            {{ $mahasiswa->user?->email ?? '-' }}
                                        </td>

                                        {{-- No HP --}}
                                        <td class="px-4 py-4">
                                            {{ $mahasiswa->no_hp ?? '-' }}
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end items-center gap-3">
                                                <a href="{{ route('mahasiswa.edit', $mahasiswa) }}"
                                                   class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>

                                                <form id="delete-mahasiswa-{{ $mahasiswa->id }}" action="{{ route('mahasiswa.destroy', $mahasiswa) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <button type="button"
                                                        @click="$dispatch('open-confirm', { name: 'delete', formId: 'delete-mahasiswa-{{ $mahasiswa->id }}', title: 'Hapus Mahasiswa', description: 'Apakah Anda yakin ingin menghapus data mahasiswa {{ addslashes($mahasiswa->nama) }}? Data yang sudah dihapus tidak dapat dikembalikan.' })"
                                                        class="inline-flex items-center font-medium text-red-600 hover:text-red-800">
                                                    <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-4 py-8 text-center text-sm text-gray-500">
                                            Belum ada data mahasiswa.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div>
                        {{ $mahasiswas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal Import --}}
    <div id="modal-import-mahasiswa" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Import Data Mahasiswa</h3>
                <button type="button"
                        onclick="document.getElementById('modal-import-mahasiswa').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-sm text-gray-500">
                Gunakan NIM sebagai kunci unik. Jika NIM sudah ada, data mahasiswa akan diperbarui.
                Unduh <a href="{{ route('mahasiswa.template') }}" class="text-indigo-600 underline">template</a> terlebih dahulu jika belum punya format yang sesuai.
            </p>

            <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-gray-600 border border-gray-300 rounded-md cursor-pointer focus:outline-none">
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="document.getElementById('modal-import-mahasiswa').classList.add('hidden')"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
