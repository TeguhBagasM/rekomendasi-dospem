<x-app-layout>
    <x-page-header
        title="User Management"
        description="Kelola role yang dimiliki setiap user."
        icon="fa-users"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Akses User', 'url' => route('users.index')],
            ['label' => 'User Management'],
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
                                <h3 class="text-lg font-semibold text-gray-900">Daftar User</h3>
                                <p class="text-sm text-gray-500">Total Data: {{ $users->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Roles</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($users as $user)
                                    <tr class="text-gray-700 hover:bg-gray-50 transition">
                                        <td class="px-4 py-4 font-medium">{{ $user->name }}</td>
                                        <td class="px-4 py-4">{{ $user->email }}</td>
                                        <td class="px-4 py-4">
                                            @if ($user->roles->isNotEmpty())
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($user->roles as $role)
                                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $role->name }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">Belum ada role</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end items-center gap-3">
                                                <a href="{{ route('users.edit', $user) }}"
                                                   class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-4 py-8 text-center text-sm text-gray-500">
                                            Belum ada data user.
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
