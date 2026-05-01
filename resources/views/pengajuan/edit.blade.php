<x-app-layout>
    <x-page-header
        title="Edit Pengajuan Judul"
        description="Perbarui judul atau proposal sebelum divalidasi."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Pengajuan Judul', 'url' => route('pengajuan.index')],
            ['label' => 'Edit Pengajuan'],
        ]"
    />

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('pengajuan.update', $pengajuan) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="judul_skripsi" value="Judul Skripsi" />
                            <textarea
                                id="judul_skripsi"
                                name="judul_skripsi"
                                rows="1"
                                x-data
                                x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                class="mt-1 block w-full resize-none overflow-hidden rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                                autofocus
                            >{{ old('judul_skripsi', $pengajuan->judul_skripsi) }}</textarea>
                            <x-input-error :messages="$errors->get('judul_skripsi')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="file_proposal" value="File Proposal PDF (opsional)" />
                            <input id="file_proposal" name="file_proposal" type="file" accept="application/pdf" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <x-input-error :messages="$errors->get('file_proposal')" class="mt-2" />
                            @if ($pengajuan->file_proposal)
                                <div class="mt-2 text-sm text-gray-600">
                                    File saat ini:
                                    <a href="{{ asset('storage/' . $pengajuan->file_proposal) }}" target="_blank" rel="noopener" class="font-medium text-indigo-600 hover:text-indigo-800">Lihat proposal</a>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Update</x-primary-button>
                            <a href="{{ route('pengajuan.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>