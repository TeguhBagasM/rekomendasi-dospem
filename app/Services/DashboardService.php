<?php

namespace App\Services;

use App\Models\BidangKeahlian;
use App\Models\Dosen;
use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\PenetapanDospembing;
use App\Models\RiwayatRekomendasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function forAdmin(): array
    {
        $totalMahasiswa = Mahasiswa::count();
        $totalDosen = Dosen::count();
        $totalPengajuan = PengajuanJudul::count();
        $statusCounts = $this->pengajuanStatusCounts();
        $totalBidangKeahlian = BidangKeahlian::count();
        $gelombangAktif = Gelombang::query()->where('is_active', true)->count();
        $totalPenetapan = PenetapanDospembing::count();
        $totalFinalized = RiwayatRekomendasi::query()->where('status', 'finalized')->count();

        $recentPengajuans = PengajuanJudul::query()
            ->select(['id', 'mahasiswa_id', 'judul_skripsi', 'status', 'gelombang_id', 'created_at'])
            ->with([
                'mahasiswa:id,nama,nim',
                'gelombang:id,nama',
            ])
            ->latest()
            ->limit(5)
            ->get();

        $activities = $this->adminActivities();

        $pengajuanPerStatusChart = [
            'labels' => ['Pending', 'Disetujui', 'Ditolak'],
            'values' => [
                (int) ($statusCounts['pending'] ?? 0),
                (int) ($statusCounts['disetujui'] ?? 0),
                (int) ($statusCounts['ditolak'] ?? 0),
            ],
        ];

        $jabatanChart = $this->jabatanFungsionalChart();
        $gelombangChart = $this->pengajuanPerGelombangChart();
        $topDosenChart = $this->topDosenChart();

        return [
            'summaryCards' => [
                $this->metricCard('Total Mahasiswa', $totalMahasiswa, 'Semua mahasiswa terdaftar', 'fa-solid fa-user-graduate'),
                $this->metricCard('Total Dosen', $totalDosen, 'Semua dosen aktif di sistem', 'fa-solid fa-chalkboard-user'),
                $this->metricCard('Total Pengajuan', $totalPengajuan, 'Seluruh pengajuan skripsi', 'fa-solid fa-file-circle-plus'),
                $this->metricCard('Pengajuan Pending', (int) ($statusCounts['pending'] ?? 0), 'Menunggu validasi', 'fa-solid fa-clock', 'bg-amber-50', 'text-amber-700'),
                $this->metricCard('Pengajuan Disetujui', (int) ($statusCounts['disetujui'] ?? 0), 'Siap diproses ke rekomendasi', 'fa-solid fa-circle-check', 'bg-emerald-50', 'text-emerald-700'),
                $this->metricCard('Pengajuan Ditolak', (int) ($statusCounts['ditolak'] ?? 0), 'Tidak lolos validasi', 'fa-solid fa-circle-xmark', 'bg-rose-50', 'text-rose-700'),
                $this->metricCard('Total Bidang Keahlian', $totalBidangKeahlian, 'Bidang keahlian dosen', 'fa-solid fa-tags', 'bg-indigo-50', 'text-indigo-700'),
                $this->metricCard('Jumlah Penetapan Dospem', $totalPenetapan, 'Penetapan yang sudah tersimpan', 'fa-solid fa-id-card-clip', 'bg-cyan-50', 'text-cyan-700'),
            ],
            'pengajuanPerStatusChart' => $pengajuanPerStatusChart,
            'jabatanFungsionalChart' => $jabatanChart,
            'pengajuanPerGelombangChart' => $gelombangChart,
            'topDosenChart' => $topDosenChart,
            'recentPengajuans' => $recentPengajuans,
            'activities' => $activities,
        ];
    }

    public function forDosen(User $user): array
    {
        $dosen = Dosen::query()
            ->select([
                'id',
                'user_id',
                'program_studi_id',
                'nidn',
                'nama',
                'jabatan_fungsional',
                'kuota_dospem1',
                'kuota_dospem2',
                'kuota_terpakai_dospem1',
                'kuota_terpakai_dospem2',
                'no_hp',
            ])
            ->with([
                'programStudi:id,program_studi',
                'bidangKeahlians:id,nama',
            ])
            ->where('user_id', $user->id)
            ->first();

        $assignments = collect();

        if ($dosen) {
            $assignments = PenetapanDospembing::query()
                ->select([
                    'id',
                    'pengajuan_id',
                    'dospem1_id',
                    'dospem2_id',
                    'ditetapkan_at',
                ])
                ->with([
                    'pengajuan:id,mahasiswa_id,judul_skripsi,status,gelombang_id,created_at',
                    'pengajuan.mahasiswa:id,nim,nama,program_studi_id',
                    'pengajuan.mahasiswa.programStudi:id,program_studi',
                    'pengajuan.gelombang:id,nama',
                    'dospem1:id,nama',
                    'dospem2:id,nama',
                ])
                ->where(function (Builder $query) use ($dosen): void {
                    $query->where('dospem1_id', $dosen->id)
                        ->orWhere('dospem2_id', $dosen->id);
                })
                ->whereHas('rekomendasiDospembing.riwayatRekomendasi', function (Builder $query): void {
                    $query->where('status', 'finalized');
                })
                ->orderByDesc('ditetapkan_at')
                ->orderByDesc('id')
                ->get();
        }

        $dospem1Count = $assignments->filter(fn (PenetapanDospembing $item) => (int) $item->dospem1_id === (int) ($dosen?->id ?? 0))->count();
        $dospem2Count = $assignments->filter(fn (PenetapanDospembing $item) => (int) $item->dospem2_id === (int) ($dosen?->id ?? 0))->count();
        $totalMahasiswa = $assignments->pluck('pengajuan_id')->unique()->count();
        $sisaKuotaDospem1 = $dosen ? max(((int) $dosen->kuota_dospem1) - ((int) $dosen->kuota_terpakai_dospem1), 0) : 0;
        $sisaKuotaDospem2 = $dosen ? max(((int) $dosen->kuota_dospem2) - ((int) $dosen->kuota_terpakai_dospem2), 0) : 0;

        $chart = [
            'labels' => ['Dospem 1', 'Dospem 2'],
            'values' => [$dospem1Count, $dospem2Count],
        ];

        return [
            'dosen' => $dosen,
            'summaryCards' => [
                $this->metricCard('Jumlah Mahasiswa Bimbingan Dospem 1', $dospem1Count, 'Mahasiswa yang ditangani sebagai pembimbing utama', 'fa-solid fa-user-pen', 'bg-sky-50', 'text-sky-700'),
                $this->metricCard('Jumlah Mahasiswa Bimbingan Dospem 2', $dospem2Count, 'Mahasiswa yang ditangani sebagai pembimbing pendamping', 'fa-solid fa-user-check', 'bg-cyan-50', 'text-cyan-700'),
                $this->metricCard('Total Mahasiswa Bimbingan', $totalMahasiswa, 'Akumulasi seluruh mahasiswa final', 'fa-solid fa-users', 'bg-indigo-50', 'text-indigo-700'),
                $this->metricCard('Sisa Kuota Dospem 1', $sisaKuotaDospem1, 'Kuota pembimbing utama tersisa', 'fa-solid fa-chart-simple', 'bg-emerald-50', 'text-emerald-700'),
                $this->metricCard('Sisa Kuota Dospem 2', $sisaKuotaDospem2, 'Kuota pembimbing pendamping tersisa', 'fa-solid fa-chart-pie', 'bg-amber-50', 'text-amber-700'),
            ],
            'chart' => $chart,
            'assignments' => $assignments,
            'hasData' => $assignments->isNotEmpty(),
            'dosenLoaded' => $dosen,
        ];
    }

    public function forMahasiswa(User $user): array
    {
        $mahasiswa = Mahasiswa::query()
            ->select(['id', 'user_id', 'nim', 'nama', 'program_studi_id', 'angkatan', 'no_hp'])
            ->with([
                'programStudi:id,program_studi',
            ])
            ->where('user_id', $user->id)
            ->first();

        $currentPengajuan = null;
        $timeline = collect();
        $dospem1 = null;
        $dospem2 = null;

        if ($mahasiswa) {
            $currentPengajuan = PengajuanJudul::query()
                ->select([
                    'id',
                    'mahasiswa_id',
                    'judul_skripsi',
                    'file_proposal',
                    'status',
                    'alasan_penolakan',
                    'gelombang_id',
                    'divalidasi_oleh',
                    'validated_at',
                    'created_at',
                ])
                ->with([
                    'gelombang:id,nama',
                    'rekomendasiDospembing:id,pengajuan_id,riwayat_rekomendasi_id,rekomendasi_dospem1_id,rekomendasi_dospem2_id',
                    'rekomendasiDospembing.riwayatRekomendasi:id,status,generated_at,generated_by',
                    'penetapanDospembing:id,pengajuan_id,dospem1_id,dospem2_id,ditetapkan_at,sumber',
                    'penetapanDospembing.dospem1:id,nama,nidn,jabatan_fungsional,program_studi_id,no_hp,user_id',
                    'penetapanDospembing.dospem1.user:id,email',
                    'penetapanDospembing.dospem1.programStudi:id,program_studi',
                    'penetapanDospembing.dospem1.bidangKeahlians:id,nama',
                    'penetapanDospembing.dospem2:id,nama,nidn,jabatan_fungsional,program_studi_id,no_hp,user_id',
                    'penetapanDospembing.dospem2.user:id,email',
                    'penetapanDospembing.dospem2.programStudi:id,program_studi',
                    'penetapanDospembing.dospem2.bidangKeahlians:id,nama',
                ])
                ->where('mahasiswa_id', $mahasiswa->id)
                ->latest()
                ->first();

            $timeline = collect([
                [
                    'label' => 'Pengajuan dibuat',
                    'description' => $currentPengajuan
                        ? 'Pengajuan skripsi telah masuk ke sistem.'
                        : 'Belum ada pengajuan yang dibuat.',
                    'done' => (bool) $currentPengajuan,
                ],
                [
                    'label' => 'Sedang divalidasi',
                    'description' => $currentPengajuan
                        ? 'Status validasi saat ini: ' . ucfirst($currentPengajuan->status)
                        : 'Menunggu pengajuan dibuat.',
                    'done' => $currentPengajuan?->status === 'pending' || $currentPengajuan?->status === 'disetujui' || $currentPengajuan?->status === 'ditolak',
                ],
                [
                    'label' => 'Rekomendasi dihasilkan',
                    'description' => $currentPengajuan?->rekomendasiDospembing
                        ? 'Rekomendasi dosen pembimbing sudah tersedia.'
                        : 'Belum ada rekomendasi yang dihasilkan.',
                    'done' => (bool) $currentPengajuan?->rekomendasiDospembing,
                ],
                [
                    'label' => 'Penetapan Dosen',
                    'description' => $currentPengajuan?->penetapanDospembing
                        ? 'Dosen pembimbing sudah ditetapkan.'
                        : 'Belum ada dosen pembimbing yang ditetapkan.',
                    'done' => (bool) $currentPengajuan?->penetapanDospembing,
                ],
            ]);

            $dospem1 = $currentPengajuan?->penetapanDospembing?->dospem1;
            $dospem2 = $currentPengajuan?->penetapanDospembing?->dospem2;
        }

        return [
            'mahasiswa' => $mahasiswa,
            'pengajuan' => $currentPengajuan,
            'summaryCards' => [
                $this->metricCard(
                    'Status Pengajuan',
                    $currentPengajuan ? $this->labelStatus($currentPengajuan->status) : 'Belum ada pengajuan',
                    $currentPengajuan ? 'Status proses skripsi terbaru' : 'Mahasiswa belum mengajukan judul',
                    'fa-solid fa-clipboard-check',
                    'bg-sky-50',
                    'text-sky-700'
                ),
                $this->metricCard(
                    'Gelombang',
                    $currentPengajuan?->gelombang?->nama ?? 'Belum ada gelombang',
                    'Gelombang pengajuan skripsi',
                    'fa-solid fa-layer-group',
                    'bg-indigo-50',
                    'text-indigo-700'
                ),
                $this->metricCard(
                    'Tanggal Pengajuan',
                    $currentPengajuan?->created_at?->format('d M Y') ?? 'Belum ada data',
                    'Waktu pengajuan terakhir',
                    'fa-solid fa-calendar-day',
                    'bg-cyan-50',
                    'text-cyan-700'
                ),
            ],
            'timeline' => $timeline,
            'dospem1' => $dospem1,
            'dospem2' => $dospem2,
            'hasPenetapan' => (bool) $currentPengajuan?->penetapanDospembing,
            'hasPengajuan' => (bool) $currentPengajuan,
        ];
    }

    private function pengajuanStatusCounts(): array
    {
        return PengajuanJudul::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    private function jabatanFungsionalChart(): array
    {
        $counts = Dosen::query()
            ->select(['id', 'jabatan_fungsional'])
            ->get()
            ->map(function (Dosen $dosen): string {
                $jabatan = trim((string) $dosen->jabatan_fungsional);

                return match (true) {
                    str_contains(mb_strtolower($jabatan), 'lektor') => 'Lektor',
                    str_contains(mb_strtolower($jabatan), 'asisten ahli') => 'Asisten Ahli',
                    str_contains(mb_strtolower($jabatan), 'tenaga pengajar') => 'Tenaga Pengajar',
                    default => 'Lainnya',
                };
            })
            ->countBy()
            ->all();

        $labels = ['Lektor', 'Asisten Ahli', 'Tenaga Pengajar', 'Lainnya'];

        return [
            'labels' => $labels,
            'values' => collect($labels)->map(fn (string $label) => (int) ($counts[$label] ?? 0))->all(),
        ];
    }

    private function pengajuanPerGelombangChart(): array
    {
        $gelombangs = Gelombang::query()
            ->select(['id', 'nama'])
            ->withCount('pengajuanJuduls')
            ->orderBy('id')
            ->get();

        return [
            'labels' => $gelombangs->map(fn (Gelombang $gelombang) => $gelombang->nama ?? 'Gelombang ' . $gelombang->id)->all(),
            'values' => $gelombangs->map(fn (Gelombang $gelombang) => (int) $gelombang->pengajuan_juduls_count)->all(),
        ];
    }

    private function topDosenChart(): array
    {
        $dospem1Counts = PenetapanDospembing::query()
            ->selectRaw('dospem1_id as dosen_id, COUNT(*) as total')
            ->whereNotNull('dospem1_id')
            ->groupBy('dospem1_id')
            ->pluck('total', 'dosen_id');

        $dospem2Counts = PenetapanDospembing::query()
            ->selectRaw('dospem2_id as dosen_id, COUNT(*) as total')
            ->whereNotNull('dospem2_id')
            ->groupBy('dospem2_id')
            ->pluck('total', 'dosen_id');

        $merged = collect();

        foreach ($dospem1Counts as $dosenId => $total) {
            $merged[$dosenId] = (int) $total;
        }

        foreach ($dospem2Counts as $dosenId => $total) {
            $merged[$dosenId] = ($merged[$dosenId] ?? 0) + (int) $total;
        }

        $topIds = $merged
            ->sortDesc()
            ->take(10)
            ->keys()
            ->values();

        if ($topIds->isEmpty()) {
            return [
                'labels' => [],
                'values' => [],
            ];
        }

        $dosens = Dosen::query()
            ->select(['id', 'nama'])
            ->whereIn('id', $topIds)
            ->get()
            ->keyBy('id');

        $labels = [];
        $values = [];

        foreach ($topIds as $dosenId) {
            $labels[] = $dosens[$dosenId]->nama ?? ('Dosen #' . $dosenId);
            $values[] = (int) ($merged[$dosenId] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function adminActivities(): Collection
    {
        $riwayatActivities = RiwayatRekomendasi::query()
            ->select(['id', 'generated_by', 'generated_at', 'status', 'keterangan'])
            ->with(['generatedBy:id,name'])
            ->latest('generated_at')
            ->limit(5)
            ->get()
            ->map(function (RiwayatRekomendasi $riwayat): array {
                return [
                    'title' => match ($riwayat->status) {
                        'exported' => 'Export Excel',
                        'imported' => 'Import Penetapan',
                        'finalized' => 'Finalisasi rekomendasi',
                        default => 'Generate rekomendasi',
                    },
                    'detail' => $riwayat->keterangan
                        ?? ('Oleh ' . ($riwayat->generatedBy?->name ?? 'Sistem')),
                    'date' => $riwayat->generated_at,
                    'icon' => match ($riwayat->status) {
                        'exported' => 'fa-solid fa-file-excel',
                        'imported' => 'fa-solid fa-file-arrow-up',
                        'finalized' => 'fa-solid fa-circle-check',
                        default => 'fa-solid fa-wand-magic-sparkles',
                    },
                    'tone' => match ($riwayat->status) {
                        'exported' => 'bg-emerald-50 text-emerald-700',
                        'imported' => 'bg-cyan-50 text-cyan-700',
                        'finalized' => 'bg-blue-50 text-blue-700',
                        default => 'bg-sky-50 text-sky-700',
                    },
                ];
            });

        $penetapanActivities = PenetapanDospembing::query()
            ->select(['id', 'pengajuan_id', 'ditetapkan_at', 'ditetapkan_oleh', 'dospem1_id', 'dospem2_id'])
            ->with([
                'pengajuan.mahasiswa:id,nama',
                'ditetapkanOleh:id,name',
                'dospem1:id,nama',
                'dospem2:id,nama',
            ])
            ->latest('ditetapkan_at')
            ->limit(5)
            ->get()
            ->map(function (PenetapanDospembing $penetapan): array {
                return [
                    'title' => 'Penetapan dosen',
                    'detail' => trim(
                        ($penetapan->pengajuan?->mahasiswa?->nama ?? 'Mahasiswa') .
                        ' • ' .
                        ($penetapan->dospem1?->nama ?? '-') .
                        ' / ' .
                        ($penetapan->dospem2?->nama ?? '-')
                    ),
                    'date' => $penetapan->ditetapkan_at,
                    'icon' => 'fa-solid fa-user-check',
                    'tone' => 'bg-amber-50 text-amber-700',
                ];
            });

        return $riwayatActivities
            ->merge($penetapanActivities)
            ->sortByDesc(fn (array $activity) => $activity['date'] instanceof Carbon ? $activity['date']->getTimestamp() : 0)
            ->values()
            ->take(5);
    }

    private function metricCard(string $title, string|int $value, string $description, string $icon, string $iconBg = 'bg-sky-50', string $iconText = 'text-sky-700'): array
    {
        return [
            'title' => $title,
            'value' => $value,
            'description' => $description,
            'icon' => $icon,
            'iconBg' => $iconBg,
            'iconText' => $iconText,
        ];
    }

    private function labelStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst($status),
        };
    }
}