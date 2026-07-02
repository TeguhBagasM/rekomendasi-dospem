<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <div class="pointer-events-none fixed right-4 top-4 z-50 space-y-3 sm:right-6 sm:top-6">
                @if (session('success'))
                    <x-toast type="success" title="Berhasil" :message="session('success')" />
                @endif

                @if (session('error'))
                    <x-toast type="error" title="Gagal" :message="session('error')" />
                @endif
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <script>
            window.addEventListener('load', () => {
                if (!window.jQuery || !$.fn || !$.fn.DataTable) {
                    console.warn('jQuery/DataTables CDN failed to load.');
                    return;
                }

                document.querySelectorAll('.js-datatable').forEach((table) => {
                    if (table.dataset.datatableInitialized === '1') {
                        return;
                    }

                    table.dataset.datatableInitialized = '1';

                    $(table).DataTable({
                        pageLength: 25,
                        lengthMenu: [25, 50, 100],
                        language: {
                            search: 'Cari:',
                            lengthMenu: 'Tampilkan _MENU_ data',
                            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                            zeroRecords: 'Data tidak ditemukan',
                            paginate: {
                                first: 'Pertama',
                                last: 'Terakhir',
                                next: 'Berikutnya',
                                previous: 'Sebelumnya',
                            },
                        },
                    });
                });
            });
        </script>

        @stack('scripts')
    </body>
</html>
