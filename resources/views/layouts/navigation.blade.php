@php
    $user = Auth::user();
    $userInitials = $user
        ? collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('')
        : 'U';
@endphp

<nav x-data="{ open: false }" class="bg-sky-800 text-white shadow-lg">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-start justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="shrink-0 rounded-full bg-white p-1 shadow-sm">
                    <a href="{{ route('dashboard') }}" class="block">
                        <x-application-logo class="block h-16 w-16" />
                    </a>
                </div>

                <div class="pt-1">
                    <div class="text-sm font-medium tracking-wide text-white/90">Sistem Rekomendasi Dosen Pembimbing</div>
                    <a href="{{ route('dashboard') }}" class="block text-xl font-bold uppercase tracking-wide text-white sm:text-2xl">
                        STMIK MARDIRA INDONESIA
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-start">
                <x-dropdown align="right" width="44">
                    <x-slot name="trigger">
                        <button class="inline-flex h-11 w-11 items-center justify-center rounded-full border-4 border-white/90 bg-sky-400 text-sm font-bold text-white shadow-sm transition hover:bg-sky-300 focus:outline-none">
                            {{ $userInitials }}
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3">
                            <div class="text-sm font-semibold text-gray-900">{{ $user?->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user?->email }}</div>
                        </div>

                        <div class="border-t border-gray-200"></div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between gap-4 border-t border-white/15 pt-4">
            <div class="hidden sm:flex sm:flex-wrap items-center gap-8 text-sm font-medium text-white">
                <a href="{{ route('dashboard') }}" class="hover:text-white/80 {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">Dashboard</a>

                @if (auth()->check() && auth()->user()->hasRole('mahasiswa'))
                    <a href="{{ route('pengajuan.index') }}" class="hover:text-white/80 {{ request()->routeIs('pengajuan.*') ? 'font-bold' : '' }}">Pengajuan Judul</a>
                @endif
                @if (auth()->check() && auth()->user()->hasRole('dosen'))
                    <a href="{{ route('profile-dosen.edit') }}" class="hover:text-white/80 {{ request()->routeIs('profile-dosen.*') ? 'font-bold' : '' }}">Profile Saya</a>
                @endif

                @if (auth()->check() && auth()->user()->hasRole('admin'))
                    {{-- <x-dropdown align="left" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center text-sm font-medium text-white hover:text-white/80 focus:outline-none">
                                <span>Akses User</span>
                                <svg class="ms-2 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('roles.index')">{{ __('Roles') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('permissions.index')">{{ __('Permissions') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('users.index')">{{ __('Users') }}</x-dropdown-link>
                        </x-slot>
                    </x-dropdown> --}}

                    <x-dropdown align="left" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center text-sm font-medium text-white hover:text-white/80 focus:outline-none">
                                <span>Data Akademik</span>
                                <svg class="ms-2 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('mahasiswa.index')">{{ __('Mahasiswa') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('dosen.index')">{{ __('Dosen') }}</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <x-dropdown align="left" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center text-sm font-medium text-white hover:text-white/80 focus:outline-none">
                                <span>Data Master</span>
                                <svg class="ms-2 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('prodi.index')">{{ __('Program Studi') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('bidang-keahlian.index')">{{ __('Bidang Keahlian') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('gelombang.index')">{{ __('Gelombang') }}</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <a href="{{ route('admin.pengajuan-judul.index') }}" class="hover:text-white/80 {{ request()->routeIs('admin.pengajuan-judul.*') ? 'font-bold' : '' }}">Pengajuan Judul</a>
                    {{-- <a href="{{ route('admin.pengajuan.index') }}" class="hover:text-white/80 {{ request()->routeIs('admin.pengajuan.*') ? 'font-bold' : '' }}">Validasi Pengajuan</a> --}}
                @endif
            </div>

            <div class="sm:hidden">
                <button @click="open = ! open" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/40 bg-white/10 text-white focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-white/15 bg-sky-800/95">
        <div class="px-4 py-4 space-y-2 text-white">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:bg-white/10 hover:text-white">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (auth()->check() && auth()->user()->hasRole('mahasiswa'))
                <x-responsive-nav-link :href="route('pengajuan.index')" :active="request()->routeIs('pengajuan.*')" class="text-white hover:bg-white/10 hover:text-white">
                    {{ __('Pengajuan Judul') }}
                </x-responsive-nav-link>
            @endif

            @if (auth()->check() && auth()->user()->hasRole('admin'))
                {{-- <div x-data="{ open: {{ request()->routeIs('roles.*', 'users.*') ? 'true' : 'false' }} }" class="rounded-lg bg-white/5">
                    <button type="button" @click="open = ! open" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-semibold text-white">
                        <span>Akses User</span>
                        <svg class="h-4 w-4 transition-transform duration-150" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition class="space-y-1 px-2 pb-2" style="display: none;">
                        <x-responsive-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Roles') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('permissions.index')" :active="request()->routeIs('permissions.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Permissions') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Users') }}</x-responsive-nav-link>
                    </div>
                </div> --}}

                <div x-data="{ open: {{ request()->routeIs('mahasiswa.*', 'dosen.*') ? 'true' : 'false' }} }" class="rounded-lg bg-white/5">
                    <button type="button" @click="open = ! open" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-semibold text-white">
                        <span>Data Akademik</span>
                        <svg class="h-4 w-4 transition-transform duration-150" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition class="space-y-1 px-2 pb-2" style="display: none;">
                        <x-responsive-nav-link :href="route('mahasiswa.index')" :active="request()->routeIs('mahasiswa.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Mahasiswa') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('dosen.index')" :active="request()->routeIs('dosen.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Dosen') }}</x-responsive-nav-link>
                    </div>
                </div>
                <div x-data="{ open: {{ request()->routeIs('mahasiswa.*', 'dosen.*') ? 'true' : 'false' }} }" class="rounded-lg bg-white/5">
                    <button type="button" @click="open = ! open" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-semibold text-white">
                        <span>Data Master</span>
                        <svg class="h-4 w-4 transition-transform duration-150" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition class="space-y-1 px-2 pb-2" style="display: none;">
                        <x-responsive-nav-link :href="route('prodi.index')" :active="request()->routeIs('prodi.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Program Studi') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('bidang-keahlian.index')" :active="request()->routeIs('bidang-keahlian.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Bidang Keahlian') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('gelombang.index')" :active="request()->routeIs('gelombang.*')" class="text-white hover:bg-white/10 hover:text-white">{{ __('Gelombang') }}</x-responsive-nav-link>
                    </div>
                </div>

                <x-responsive-nav-link :href="route('admin.pengajuan-judul.index')" :active="request()->routeIs('admin.pengajuan-judul.*')" class="text-white hover:bg-white/10 hover:text-white">
                    {{ __('Pengajuan Judul') }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->check() && auth()->user()->hasRole('dosen'))
                <x-responsive-nav-link :href="route('profile-dosen.edit')" :active="request()->routeIs('profile-dosen.*')" class="text-white hover:bg-white/10 hover:text-white">
                    {{ __('Profile Saya') }}
                </x-responsive-nav-link>
            @endif

            <div class="mt-4 rounded-xl bg-white/10 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-white text-sm font-bold text-white">
                        {{ $userInitials }}
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-white">{{ $user?->name }}</div>
                        <div class="truncate text-xs text-white/80">{{ $user?->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')" class="text-white hover:bg-white/10 hover:text-white">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')" class="text-white hover:bg-white/10 hover:text-white" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
