<x-app-layout>
    <x-page-header
        title="Edit Gelombang"
        description="Perbarui data gelombang pengajuan judul skripsi."
        icon="fa-pen"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Manajemen Gelombang', 'url' => route('gelombang.index')],
            ['label' => 'Edit Gelombang'],
        ]"
    />
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                            <i class="fa-solid fa-pen text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Form Edit Gelombang</h3>
                            <p class="text-sm text-gray-500">Perbarui data gelombang: {{ $gelombang->nama }}</p>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('gelombang.update', $gelombang) }}">
                    @csrf
                    @method('PUT')
                    @include('gelombang._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>