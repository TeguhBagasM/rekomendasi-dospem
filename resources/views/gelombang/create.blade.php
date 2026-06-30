<x-app-layout>
    <x-page-header
        title="Tambah Gelombang"
        description="Tambahkan data gelombang pengajuan judul skripsi."
        icon="fa-circle-plus"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Manajemen Gelombang', 'url' => route('gelombang.index')],
            ['label' => 'Tambah Gelombang'],
        ]"
    />
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                            <i class="fa-solid fa-folder-plus text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Form Data Gelombang</h3>
                            <p class="text-sm text-gray-500">Isi field berikut untuk membuat gelombang baru.</p>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('gelombang.store') }}">
                    @csrf
                    @include('gelombang._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>