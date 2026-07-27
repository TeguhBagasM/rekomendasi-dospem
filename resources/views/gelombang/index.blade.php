@php
    $search = request('search', '');
    $filteredGelombangs = $gelombangs->when($search, function ($query) use ($search) {
        $query->filter(fn ($g) =>
            str_contains(strtolower($g->nama), strtolower($search))
            || str_contains($g->periode_akademik, $search)
            || str_contains(strtolower($g->periode_akademik_label), strtolower($search))
        );
    });
@endphp

<x-app-layout>
    <x-page-header
        title="Manajemen Gelombang"
        description="Kelola gelombang pengajuan judul skripsi mahasiswa."
        icon="fa-layer-group"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Manajemen Gelombang'],
        ]"
    />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                                <i class="fa-solid fa-list text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Daftar Gelombang</h3>
                                <p class="text-sm text-gray-500">Total Data: {{ $gelombangs->count() }}</p>
                            </div>
                        </div>

                        <a href="{{ route('gelombang.create') }}"
                           class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Data
                        </a>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <form action="{{ route('gelombang.index') }}" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                        Cari Nama / Periode Akademik
                                    </label>
                                    <input type="text"
                                           id="search"
                                           name="search"
                                           value="{{ $search }}"
                                           placeholder="Nama gelombang atau periode akademik..."
                                           class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mt-4">
                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Cari
                                </button>
                                @if ($search)
                                    <a href="{{ route('gelombang.index') }}"
                                       class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-rotate-left mr-2"></i> Reset Filter
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Periode Akademik</th>
                                    <th class="px-4 py-3">Nama Gelombang</th>
                                    <th class="px-4 py-3">Tanggal Mulai</th>
                                    <th class="px-4 py-3">Tanggal Selesai</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Jumlah Pengajuan</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($filteredGelombangs as $index => $gelombang)
                                    <tr class="text-gray-700 hover:bg-gray-50 transition">
                                        <td class="px-4 py-4">{{ $index + 1 }}</td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                                {{ $gelombang->periode_akademik_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 font-medium">{{ $gelombang->nama }}</td>
                                        <td class="px-4 py-4">{{ $gelombang->tanggal_mulai->format('d M Y') }}</td>
                                        <td class="px-4 py-4">{{ $gelombang->tanggal_selesai->format('d M Y') }}</td>
                                        <td class="px-4 py-4">
                                            <div class="inline-flex flex-col items-start">
                                                <form id="toggle-gelombang-{{ $gelombang->id }}" action="{{ route('gelombang.toggle-status', $gelombang) }}" method="POST" class="hidden">
                                                    @csrf
                                                    @method('PATCH')
                                                </form>
                                                <button
                                                    type="button"
                                                    @click="$dispatch('open-confirm', { name: 'warning', formId: 'toggle-gelombang-{{ $gelombang->id }}', title: '{{ $gelombang->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Gelombang', description: 'Apakah Anda yakin ingin {{ strtolower($gelombang->is_active ? 'Menonaktifkan' : 'Mengaktifkan') }} gelombang {{ addslashes($gelombang->nama) }}?' })"
                                                    class="relative inline-flex shrink-0 items-center rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none {{ $gelombang->is_active ? 'bg-green-500' : 'bg-gray-300' }}"
                                                    style="width: 40px; height: 24px; padding: 0;"
                                                    title="{{ $gelombang->is_active ? 'Aktif' : 'Tidak Aktif' }}"
                                                >
                                                    <span
                                                        class="inline-block transform rounded-full bg-white shadow ring-0 transition-transform duration-200 {{ $gelombang->is_active ? 'translate-x-2' : 'translate-x-0' }}"
                                                        style="width: 20px; height: 20px;"
                                                    ></span>
                                                </button>
                                                <span class="mt-1 text-xs font-semibold {{ $gelombang->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ $gelombang->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                                {{ $gelombang->pengajuan_juduls_count }} Mahasiswa
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end items-center gap-3">
                                                <a href="{{ route('gelombang.edit', $gelombang) }}"
                                                   class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>
                                                <form id="delete-gelombang-{{ $gelombang->id }}" action="{{ route('gelombang.destroy', $gelombang) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <button type="button"
                                                        @click="$dispatch('open-confirm', { name: 'delete', formId: 'delete-gelombang-{{ $gelombang->id }}', title: 'Hapus Gelombang', description: 'Apakah Anda yakin ingin menghapus data gelombang ini? Data yang sudah dihapus tidak dapat dikembalikan.' })"
                                                        class="inline-flex items-center font-medium text-red-600 hover:text-red-800">
                                                    <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-4 py-8 text-center text-sm text-gray-500">
                                            Belum ada data gelombang.
                                        </td>
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
