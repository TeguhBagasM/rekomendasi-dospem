<x-app-layout>
    <x-page-header
        title="Data Program Studi"
        description="Kelola daftar program studi, fakultas, jenjang, dan kaprodi."
        icon="fa-graduation-cap"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Data Akademik', 'url' => route('prodi.index')],
            ['label' => 'Data Program Studi'],
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
                                <h3 class="text-lg font-semibold text-gray-900">Daftar Program Studi</h3>
                                <p class="text-sm text-gray-500">Total Data: {{ $prodis->count() }}</p>
                            </div>
                        </div>

                        <a href="{{ route('prodi.create') }}"
                           class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Data
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">Program Studi</th>
                                    <th class="px-4 py-3">Kode Prodi</th>
                                    <th class="px-4 py-3">Fakultas</th>
                                    <th class="px-4 py-3">Jenjang</th>
                                    <th class="px-4 py-3">Kaprodi</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($prodis as $prodi)
                                    <tr class="text-gray-700 hover:bg-gray-50 transition">
                                        <td class="px-4 py-4 font-medium">{{ $prodi->program_studi }}</td>
                                        <td class="px-4 py-4">{{ $prodi->kode_prodi }}</td>
                                        <td class="px-4 py-4">{{ $prodi->fakultas }}</td>
                                        <td class="px-4 py-4">{{ $prodi->jenjang }}</td>
                                        <td class="px-4 py-4">{{ $prodi->kaprodi }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end items-center gap-3">
                                                <a href="{{ route('prodi.edit', $prodi) }}"
                                                   class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('prodi.destroy', $prodi) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Hapus data program studi ini?')">
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
                                        <td colspan="6"
                                            class="px-4 py-8 text-center text-sm text-gray-500">
                                            Belum ada data program studi.
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
