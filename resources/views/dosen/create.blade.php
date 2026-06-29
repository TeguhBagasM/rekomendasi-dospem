<x-app-layout>
    <x-page-header
        title="Tambah Dosen"
        description="Tambahkan data dosen baru lengkap dengan keahlian dan profil penelitian."
        :breadcrumbs="[
            ['label' => 'Home',           'url' => route('dashboard')],
            ['label' => 'Manajemen Dosen','url' => route('dosen.index')],
            ['label' => 'Tambah Dosen'],
        ]"
    />

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @include('dosen._form', [
                    'action'          => route('dosen.store'),
                    'method'          => 'POST',
                    'programStudis'   => $programStudis,
                    'bidangKeahlians' => $bidangKeahlians,
                ])
            </div>
        </div>
    </div>
</x-app-layout>