@php
    $summaryCards = $summaryCards ?? [];
    $assignments = $assignments ?? collect();
    $dosenLoaded = $dosenLoaded ?? null;
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ($summaryCards as $card)
            <x-dashboard.metric-card :card="$card" />
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-1">
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-slate-900">Distribusi Mahasiswa</h3>
                <p class="text-xs text-slate-500">Pie chart</p>
            </div>
            <div class="h-72">
                <canvas id="dosenDistributionChart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Daftar Mahasiswa Bimbingan</h3>
                    <p class="text-xs text-slate-500">Data final dari penetapan dospem</p>
                </div>
            </div>

            @if ($assignments->isEmpty())
                <x-dashboard.empty-state title="Belum ada mahasiswa bimbingan." description="Belum ada penetapan dosen pembimbing final untuk akun ini." icon="fa-solid fa-user-graduate" />
            @else
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">NIM</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Judul Skripsi</th>
                                    <th class="px-4 py-3">Status Pengajuan</th>
                                    <th class="px-4 py-3">Peran</th>
                                    <th class="px-4 py-3">Gelombang</th>
                                    <th class="px-4 py-3">Tanggal Penetapan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white text-slate-700">
                                @foreach ($assignments as $assignment)
                                    @php
                                        $isDospem1 = (int) $assignment->dospem1_id === (int) ($dosenLoaded?->id ?? 0);
                                    @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $assignment->pengajuan?->mahasiswa?->nim ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $assignment->pengajuan?->mahasiswa?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $assignment->pengajuan?->judul_skripsi ?? '-' }}</td>
                                        <td class="px-4 py-3"><x-dashboard.status-badge :status="$assignment->pengajuan?->status" /></td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                                {{ $isDospem1 ? 'Dospem 1' : 'Dospem 2' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">{{ $assignment->pengajuan?->gelombang?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ optional($assignment->ditetapkan_at)->format('d M Y') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        window.addEventListener('load', () => {
            const chartData = @json($chart ?? ['labels' => [], 'values' => []]);
            const canvas = document.getElementById('dosenDistributionChart');

            if (!canvas) return;

            new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.values,
                        backgroundColor: ['#3b82f6', '#06b6d4'],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true } },
                    },
                },
            });
        });
    </script>
@endpush