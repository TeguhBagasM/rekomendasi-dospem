<x-app-layout>
    <x-page-header
        title="Manajemen Dosen"
        description="Mengelola data dosen, keahlian, dan kuota bimbingan."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Data Akademik', 'url' => route('dosen.index')],
            ['label' => 'Manajemen Dosen'],
        ]"
    />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    {{-- Header & Tombol Tambah --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Dosen</h3>
                            <p class="text-sm text-gray-500">
                                Total Dosen: {{ $dosens->total() }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('dosen.template') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-arrow-down mr-2"></i> Template
                            </a>

                            <a href="{{ route('dosen.export') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-export mr-2"></i> Export Excel
                            </a>

                            <button type="button"
                                    onclick="document.getElementById('modal-import-dosen').classList.remove('hidden')"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-import mr-2"></i> Import Excel
                            </button>

                            <a href="{{ route('dosen.create') }}"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                <i class="fa-solid fa-plus mr-2"></i> Tambah Dosen
                            </a>
                        </div>
                    </div>

                    {{-- Flash Message --}}
                    @if (session('error'))
                        <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Tabel --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">NIDN</th>
                                    <th class="px-4 py-3">Program Studi</th>
                                    <th class="px-4 py-3">Jabatan Fungsional</th>
                                    <th class="px-4 py-3">Pendidikan</th>
                                    <th class="px-4 py-3 text-center">Kuota Dospem 1</th>
                                    <th class="px-4 py-3 text-center">Kuota Dospem 2</th>
                                    <th class="px-4 py-3 text-center">Keahlian</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($dosens as $dosen)
                                    <tr class="text-gray-700 hover:bg-gray-50 transition">
                                        {{-- Nama --}}
                                        <td class="px-4 py-4 font-medium">
                                            {{ $dosen->nama }}
                                        </td>

                                        {{-- NIDN --}}
                                        <td class="px-4 py-4 text-gray-600">
                                            {{ $dosen->nidn }}
                                        </td>

                                        {{-- Program Studi --}}
                                        <td class="px-4 py-4">
                                            {{ $dosen->programStudi?->program_studi ?? '-' }} {{ $dosen->programStudi?->jenjang }}
                                        </td>

                                        {{-- Jabatan Fungsional --}}
                                        <td class="px-4 py-4">
                                            {{ $dosen->jabatan_fungsional ?? '-' }}
                                        </td>

                                        {{-- Pendidikan Terakhir --}}
                                        <td class="px-4 py-4">
                                            {{ $dosen->pendidikan_terakhir ?? '-' }}
                                        </td>

                                        {{-- Kuota Dospem 1 --}}
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="font-medium text-gray-900">
                                                    {{ $dosen->kuota_terpakai_dospem1 }}
                                                </span>
                                                <span class="text-gray-400">/</span>
                                                <span>{{ $dosen->kuota_dospem1 }}</span>
                                            </span>
                                        </td>

                                        {{-- Kuota Dospem 2 --}}
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="font-medium text-gray-900">
                                                    {{ $dosen->kuota_terpakai_dospem2 }}
                                                </span>
                                                <span class="text-gray-400">/</span>
                                                <span>{{ $dosen->kuota_dospem2 }}</span>
                                            </span>
                                        </td>

                                        {{-- Jumlah Bidang Keahlian --}}
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center justify-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                                {{ $dosen->bidang_keahlians_count }}
                                            </span>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end items-center gap-3">
                                                <a href="{{ route('dosen.edit', $dosen) }}"
                                                   class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>

                                                <form action="{{ route('dosen.destroy', $dosen) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Hapus data dosen {{ addslashes($dosen->nama) }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center font-medium text-red-600 hover:text-red-800">
                                                        <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9"
                                            class="px-4 py-8 text-center text-sm text-gray-500">
                                            Belum ada data dosen.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div>
                        {{ $dosens->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div id="modal-import-dosen" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Import Data Dosen</h3>
            <button type="button"
                    onclick="document.getElementById('modal-import-dosen').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p class="text-sm text-gray-500">
            Gunakan NIDN sebagai kunci unik. Jika NIDN sudah ada, data dosen akan diperbarui.
            Unduh <a href="{{ route('dosen.template') }}" class="text-indigo-600 underline">template</a> terlebih dahulu jika belum punya format yang sesuai.
        </p>

        <form action="{{ route('dosen.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                        onclick="document.getElementById('modal-import-dosen').classList.add('hidden')"
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