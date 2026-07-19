<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-50 via-indigo-50 to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 relative overflow-hidden">

        <div class="pointer-events-none absolute inset-0 opacity-40 dark:opacity-20" style="background-image: radial-gradient(circle at 1px 1px, rgb(99 102 241 / 0.15) 1px, transparent 0); background-size: 24px 24px;"></div>

        <div class="pointer-events-none absolute -top-24 -left-24 w-72 h-72 bg-sky-300/30 dark:bg-sky-600/10 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 w-72 h-72 bg-purple-300/30 dark:bg-purple-600/10 rounded-full blur-3xl"></div>

        <div class="relative w-full max-w-sm">

            <div class="flex flex-col items-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" width="100">
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ __('Selamat Datang') }}
                </h1>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400 text-center">
                    {{ __('Sistem Rekomendasi Dosen Pembimbing') }}
                </p>
            </div>

            <div class="bg-white/80 dark:bg-slate-900/70 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200/70 dark:border-slate-800 px-6 py-6 sm:px-7">

                <x-auth-session-status class="mb-3" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email / NIM')" class="text-sm font-medium text-slate-700 dark:text-slate-300" />
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16v16H4V4z" stroke="none"></path>
                                    <path d="M22 6l-10 7L2 6"></path>
                                    <path d="M2 6h20v12H2V6z"></path>
                                </svg>
                            </div>
                            <x-text-input id="email"
                                class="block w-full pl-9 pr-3 py-2 rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder:text-slate-400 shadow-sm transition duration-200 ease-in-out focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40 hover:border-slate-400 dark:hover:border-slate-600"
                                type="text"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="you@example.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-slate-700 dark:text-slate-300" />
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                </svg>
                            </div>
                            <x-text-input id="password"
                                class="block w-full pl-9 pr-10 py-2 rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder:text-slate-400 shadow-sm transition duration-200 ease-in-out focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40 hover:border-slate-400 dark:hover:border-slate-600"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••" />
                            <button type="button" id="togglePassword" tabindex="-1"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors duration-200 focus:outline-none">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eyeOffIcon" class="hidden w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.62 21.62 0 0 1 5.06-6.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.6 21.6 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <!-- Submit -->
                    <div>
                        <x-primary-button class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg bg-sky-800 hover:bg-sky-700 active:bg-sky-800 text-white font-semibold shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <p class="mt-4 text-center text-xs text-slate-400 dark:text-slate-600">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('All rights reserved.') }}
            </p>
        </div>
    </div>

    <script>
        (function () {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function () {
                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';
                    eyeIcon.classList.toggle('hidden', isHidden);
                    eyeOffIcon.classList.toggle('hidden', !isHidden);
                });
            }
        })();
    </script>
</x-guest-layout>