<x-app-layout>
    <x-page-header
        title="Bidang Keahlian"
        description="Kelola daftar bidang keahlian dosen untuk pencocokan topik skripsi."
        icon="fa-tags"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Data Akademik', 'url' => route('bidang-keahlian.index')],
            ['label' => 'Bidang Keahlian'],
        ]"
    />

    <div
        class="py-8"
        x-data="{
            editMode: false,
            editId: null,
            editNama: '',
            baseUpdateUrl: '{{ route('bidang-keahlian.update', ':id') }}',
            storeUrl: '{{ route('bidang-keahlian.store') }}',
            startEdit(id, nama) {
                this.editMode = true;
                this.editId = id;
                this.editNama = nama;
                this.$nextTick(() => this.$refs.namaInput.focus());
                this.$refs.formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            },
            cancelEdit() {
                this.editMode = false;
                this.editId = null;
                this.editNama = '';
            },
        }"
        @if ($errors->any() && old('_form_mode') === 'edit')
            x-init="editMode = true; editId = '{{ old('_form_id') }}'; editNama = '{{ old('nama') }}'"
        @endif
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ===== CARD FORM (Tambah / Edit) ===== --}}
            <div x-ref="formCard" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center gap-3 mb-5">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full ring-1"
                            :class="editMode ? 'bg-amber-50 text-amber-700 ring-amber-100' : 'bg-sky-50 text-sky-700 ring-sky-100'"
                        >
                            <i class="fa-solid" :class="editMode ? 'fa-pen-to-square' : 'fa-plus'"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="editMode ? 'Edit Bidang Keahlian' : 'Tambah Bidang Keahlian'"></h3>
                            <p class="text-sm text-gray-500" x-text="editMode ? 'Perbarui nama bidang keahlian yang dipilih.' : 'Tambahkan bidang keahlian baru ke daftar.'"></p>
                        </div>
                    </div>

                    <form
                        method="POST"
                        :action="editMode ? baseUpdateUrl.replace(':id', editId) : storeUrl"
                    >
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <input type="hidden" name="_form_mode" :value="editMode ? 'edit' : 'create'">
                        <input type="hidden" name="_form_id" :value="editId">

                        <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                            <div class="flex-1">
                                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nama Bidang Keahlian
                                </label>
                                <input
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    x-ref="namaInput"
                                    x-model="editNama"
                                    placeholder="Contoh: Machine Learning"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('nama') border-red-400 @enderror"
                                >
                                @error('nama')
                                    <p class="mt-2 text-sm text-red-600">
                                        <i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="flex gap-2 shrink-0">
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold text-white transition-colors"
                                    :class="editMode ? 'bg-amber-600 hover:bg-amber-500' : 'bg-gray-900 hover:bg-gray-700'"
                                >
                                    <i class="fa-solid mr-2" :class="editMode ? 'fa-floppy-disk' : 'fa-plus'"></i>
                                    <span x-text="editMode ? 'Update' : 'Tambah'"></span>
                                </button>

                                <button
                                    type="button"
                                    x-show="editMode"
                                    x-cloak
                                    @click="cancelEdit()"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    <i class="fa-solid fa-xmark mr-2"></i>
                                    Batal
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ===== CARD TABEL ===== --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                            <i class="fa-solid fa-list text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Bidang Keahlian</h3>
                            <p class="text-sm text-gray-500">Total Data: {{ $bidangKeahlians->total() }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3 w-16">No</th>
                                    <th class="px-4 py-3">Nama Bidang Keahlian</th>
                                    <th class="px-4 py-3">Jumlah Dosen</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($bidangKeahlians as $index => $bidang)
                                    <tr class="text-sm text-gray-700">
                                        <td class="px-4 py-4">
                                            {{ $bidangKeahlians->firstItem() + $index }}
                                        </td>
                                        <td class="px-4 py-4 font-medium">{{ $bidang->nama }}</td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-100">
                                                <i class="fa-solid fa-user-tie mr-1.5"></i>
                                                {{ $bidang->dosens_count }} Dosen
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-3">
                                                <button
                                                    type="button"
                                                    @click="startEdit('{{ $bidang->id }}', '{{ addslashes($bidang->nama) }}')"
                                                    class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800"
                                                >
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </button>
                                                <form id="delete-bidang-{{ $bidang->id }}" action="{{ route('bidang-keahlian.destroy', $bidang) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <button type="button"
                                                        @click="$dispatch('open-confirm', { name: 'delete', formId: 'delete-bidang-{{ $bidang->id }}', title: 'Hapus Bidang Keahlian', description: 'Apakah Anda yakin ingin menghapus bidang keahlian ini? Data yang sudah dihapus tidak dapat dikembalikan.' })"
                                                        class="inline-flex items-center font-medium text-red-600 hover:text-red-800">
                                                    <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <i class="fa-solid fa-tags text-3xl mb-3"></i>
                                                <p class="text-sm font-medium text-gray-500">Belum ada data bidang keahlian.</p>
                                                <p class="text-xs text-gray-400 mt-1">Gunakan form di atas untuk menambahkan data baru.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($bidangKeahlians->hasPages())
                        <div>
                            {{ $bidangKeahlians->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>