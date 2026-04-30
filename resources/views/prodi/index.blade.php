<x-app-layout>
    <div class="border-b bg-white">
        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                        <i class="fa-solid fa-graduation-cap text-lg"></i>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Data Program Studi</h1>
                        <p class="mt-1 text-sm text-gray-500">Kelola daftar program studi, fakultas, jenjang, dan kaprodi.</p>
                    </div>
                </div>

                <a href="{{ route('prodi.create') }}" class="inline-flex items-center rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Tambah Data
                </a>
            </div>
        </div>
    </div>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                            <i class="fa-solid fa-list text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Daftar Program Studi</h2>
                        <p class="mt-1 text-sm text-sky-700">Menampilkan {{ $prodis->total() }} data program studi.</p>
                        </div>
                    </div>

                    <div class="rounded-full bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 shadow-sm">
                        Total Data: {{ $prodis->total() }}
                    </div>
                </div>

                <div class="overflow-x-auto p-6 pt-0">
                    <table class="min-w-full divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200">
                        <thead class="bg-slate-950 text-white">
                            <tr class="text-left text-sm font-semibold">
                                <th class="px-5 py-4">Program Studi</th>
                                <th class="px-5 py-4">Kode Prodi</th>
                                <th class="px-5 py-4">Fakultas</th>
                                <th class="px-5 py-4">Jenjang</th>
                                <th class="px-5 py-4">Kaprodi</th>
                                <th class="px-5 py-4 text-center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($prodis as $prodi)
                                <tr class="text-sm text-gray-800">
                                    <td class="px-5 py-4 font-medium">{{ $prodi->program_studi }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-700">{{ $prodi->kode_prodi }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">{{ $prodi->fakultas }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">{{ $prodi->jenjang }}</span>
                                    </td>
                                    <td class="px-5 py-4">{{ $prodi->kaprodi }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route('prodi.edit', $prodi) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-sky-600 transition hover:bg-sky-50 hover:text-sky-700" title="Edit">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>

                                            <form action="{{ route('prodi.destroy', $prodi) }}" method="POST" onsubmit="return confirm('Hapus data program studi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-red-500 transition hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada data program studi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 pb-6">
                    {{ $prodis->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>