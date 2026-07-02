@php
    $summaryCards = $summaryCards ?? [];
    $recentPengajuans = $recentPengajuans ?? collect();
    $activities = $activities ?? collect();
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ($summaryCards as $card)
            <x-dashboard.metric-card :card="$card" />
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Pengajuan Berdasarkan Status</h3>
                    <p class="text-xs text-slate-500">Bar chart</p>
                </div>
            </div>
            <div class="h-80">
                <canvas id="adminPengajuanStatusChart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Distribusi Jabatan Fungsional</h3>
                    <p class="text-xs text-slate-500">Pie chart</p>
                </div>
            </div>
            <div class="h-80">
                <canvas id="adminJabatanChart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Jumlah Pengajuan per Gelombang</h3>
                    <p class="text-xs text-slate-500">Line chart</p>
                </div>
            </div>
            <div class="h-80">
                <canvas id="adminGelombangChart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Top 10 Dosen dengan Mahasiswa Bimbingan Terbanyak</h3>
                    <p class="text-xs text-slate-500">Horizontal bar chart</p>
                </div>
            </div>
            <div class="h-[28rem]">
                <canvas id="adminTopDosenChart" class="h-full w-full"></canvas>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">5 Pengajuan Terbaru</h3>
                    <p class="text-xs text-slate-500">Data terakhir yang masuk</p>
                </div>
            </div>

            @if ($recentPengajuans->isEmpty())
                <x-dashboard.empty-state title="Belum ada pengajuan." description="Data pengajuan terbaru belum tersedia." icon="fa-solid fa-file-circle-xmark" />
            @else
                <div class="space-y-3">
                    @foreach ($recentPengajuans as $pengajuan)
                        <div class="rounded-xl border border-gray-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-white">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-900">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $pengajuan->mahasiswa?->nim ?? '-' }} • {{ $pengajuan->gelombang?->nama ?? '-' }}</div>
                                    <div class="mt-2 text-sm text-slate-600">{{ $pengajuan->judul_skripsi }}</div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <x-dashboard.status-badge :status="$pengajuan->status" />
                                    <div class="mt-2 text-xs text-slate-500">{{ optional($pengajuan->created_at)->format('d M Y') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">5 Aktivitas Terakhir</h3>
                    <p class="text-xs text-slate-500">Generate, export, import, dan penetapan</p>
                </div>
            </div>

            @if ($activities->isEmpty())
                <x-dashboard.empty-state title="Belum ada aktivitas." description="Riwayat aktivitas dashboard belum tersedia." icon="fa-solid fa-clock-rotate-left" />
            @else
                <div class="space-y-3">
                    @foreach ($activities as $activity)
                        <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-slate-50 p-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $activity['tone'] }}">
                                <i class="{{ $activity['icon'] }} text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-slate-900">{{ $activity['title'] }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $activity['detail'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ optional($activity['date'])->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        window.addEventListener('load', () => {
            const pengajuanStatus = @json($pengajuanPerStatusChart ?? ['labels' => [], 'values' => []]);
            const jabatanChart = @json($jabatanFungsionalChart ?? ['labels' => [], 'values' => []]);
            const gelombangChart = @json($pengajuanPerGelombangChart ?? ['labels' => [], 'values' => []]);
            const topDosenChart = @json($topDosenChart ?? ['labels' => [], 'values' => []]);

            const palette = {
                blue: '#3b82f6',
                slate: '#64748b',
                cyan: '#06b6d4',
                emerald: '#10b981',
                amber: '#f59e0b',
                rose: '#f43f5e',
                border: '#dbe4ee',
            };

            const createChart = (ctx, config) => new Chart(ctx, config);

            const pengajuanStatusCtx = document.getElementById('adminPengajuanStatusChart');
            if (pengajuanStatusCtx) {
                createChart(pengajuanStatusCtx, {
                    type: 'bar',
                    data: {
                        labels: pengajuanStatus.labels,
                        datasets: [{
                            label: 'Jumlah Pengajuan',
                            data: pengajuanStatus.values,
                            backgroundColor: [palette.slate, palette.blue, palette.rose],
                            borderRadius: 10,
                            borderSkipped: false,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: palette.border } },
                            x: { grid: { display: false } },
                        },
                    },
                });
            }

            const jabatanCtx = document.getElementById('adminJabatanChart');
            if (jabatanCtx) {
                createChart(jabatanCtx, {
                    type: 'pie',
                    data: {
                        labels: jabatanChart.labels,
                        datasets: [{
                            data: jabatanChart.values,
                            backgroundColor: [palette.blue, palette.cyan, palette.emerald, palette.amber],
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
            }

            const gelombangCtx = document.getElementById('adminGelombangChart');
            if (gelombangCtx) {
                createChart(gelombangCtx, {
                    type: 'line',
                    data: {
                        labels: gelombangChart.labels,
                        datasets: [{
                            label: 'Jumlah Pengajuan',
                            data: gelombangChart.values,
                            borderColor: palette.blue,
                            backgroundColor: 'rgba(59, 130, 246, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: palette.blue,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: palette.border } },
                            x: { grid: { display: false } },
                        },
                    },
                });
            }

            const topDosenCtx = document.getElementById('adminTopDosenChart');
            if (topDosenCtx) {
                createChart(topDosenCtx, {
                    type: 'bar',
                    data: {
                        labels: topDosenChart.labels,
                        datasets: [{
                            label: 'Jumlah Mahasiswa',
                            data: topDosenChart.values,
                            backgroundColor: palette.cyan,
                            borderRadius: 10,
                            borderSkipped: false,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: palette.border } },
                            y: { grid: { display: false } },
                        },
                    },
                });
            }
        });
    </script>
@endpush