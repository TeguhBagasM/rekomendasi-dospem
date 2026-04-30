<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Daftar User</h3>
                        <p class="text-sm text-gray-500">Kelola role yang dimiliki setiap user.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="js-datatable min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Roles</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($users as $user)
                                    <tr class="text-sm text-gray-700">
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
                                        <td class="px-4 py-4 text-right">
                                            <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada user.</td>
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