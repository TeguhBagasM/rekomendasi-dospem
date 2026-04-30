<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Manajemen Dosen') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Dosen</h3>
                            <p class="text-sm text-gray-500">CRUD dosen dengan ringkasan keahlian dan kuota bimbingan.</p>
                        </div>

                        <a href="{{ route('dosen.create') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Dosen
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">NIDN</th>
                                    <th class="px-4 py-3">Kuota</th>
                                    <th class="px-4 py-3">Sisa Kuota</th>
                                    <th class="px-4 py-3">Jumlah Keahlian</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($dosens as $dosen)
                                    <tr class="text-sm text-gray-700">
                                        <td class="px-4 py-4 font-medium">
                                            <div>{{ $dosen->nama }}</div>
                                            <div class="text-xs text-gray-500">{{ $dosen->program_studi ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-4">{{ $dosen->nidn }}</td>
                                        <td class="px-4 py-4">{{ $dosen->kuota_terisi }}/{{ $dosen->kuota_bimbingan }}</td>
                                        <td class="px-4 py-4">{{ $dosen->sisaKuota() }}</td>
                                        <td class="px-4 py-4">{{ $dosen->keahlian->count() }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('dosen.edit', $dosen) }}" class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('dosen.destroy', $dosen) }}" method="POST" onsubmit="return confirm('Hapus data dosen ini?')">
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
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data dosen.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $dosens->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>