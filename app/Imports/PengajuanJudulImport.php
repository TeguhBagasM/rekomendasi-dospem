<?php

namespace App\Imports;

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PengajuanJudulImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    use Importable;

    public int $mahasiswaCreated = 0;

    public int $mahasiswaUpdated = 0;

    public int $submissionsCreated = 0;

    public int $submissionsUpdated = 0;

    public int $submissionsResetPending = 0;

    public int $submissionsSkippedApproved = 0;

    public array $errors = [];

    private Collection $existingPengajuans;

    public function __construct(private readonly Gelombang $gelombang) {}

    /**
     * Heading row expected: nim | nama_mahasiswa | program_studi | judul_skripsi
     */
    public function collection(Collection $rows): void
    {
        $nims = $rows->pluck('nim')
            ->filter()
            ->map(fn ($nim) => trim((string) $nim))
            ->unique()
            ->values();

        // Preload untuk menghindari N+1 di dalam loop.
        $existingMahasiswas = Mahasiswa::whereIn('nim', $nims)->get()->keyBy('nim');

        $prodiMap = ProgramStudi::query()
            ->get(['id', 'program_studi'])
            ->mapWithKeys(fn ($prodi) => [Str::lower(trim($prodi->program_studi)) => $prodi->id]);

        // Preload pengajuan yang sudah ada pada gelombang aktif ini,
        // di-key per mahasiswa_id agar lookup di dalam loop O(1) tanpa query berulang.
        $this->existingPengajuans = PengajuanJudul::query()
            ->where('gelombang_id', $this->gelombang->id)
            ->whereIn('mahasiswa_id', $existingMahasiswas->pluck('id'))
            ->get()
            ->keyBy('mahasiswa_id');

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 heading row, +1 index dimulai dari 0

            $nim = trim((string) ($row['nim'] ?? ''));
            $nama = trim((string) ($row['nama_mahasiswa'] ?? ''));
            $namaProdi = trim((string) ($row['program_studi'] ?? ''));
            $judul = trim((string) ($row['judul_skripsi'] ?? ''));

            if ($nim === '' || $nama === '' || $namaProdi === '' || $judul === '') {
                $this->errors[] = "Baris {$rowNumber}: NIM/Nama/Program Studi/Judul ada yang kosong, dilewati.";

                continue;
            }

            $programStudiId = $prodiMap->get(Str::lower($namaProdi));

            if (! $programStudiId) {
                $this->errors[] = "Baris {$rowNumber}: Program Studi '{$namaProdi}' tidak ditemukan, dilewati.";

                continue;
            }

            $angkatan = '20'.substr($nim, 0, 2);
            $mahasiswa = $existingMahasiswas->get($nim);

            if ($mahasiswa) {
                $mahasiswa->update([
                    'nama' => $nama,
                    'program_studi_id' => $programStudiId,
                ]);
                $this->mahasiswaUpdated++;
            } else {
                $user = User::create([
                    'name' => $nama,
                    'email' => $nim.'@stmik-mi.ac.id',
                    'password' => $nim, // auto-hashed via cast 'password' => 'hashed' di model User
                ]);
                $user->assignRole('mahasiswa');

                $mahasiswa = Mahasiswa::create([
                    'user_id' => $user->id,
                    'nim' => $nim,
                    'nama' => $nama,
                    'program_studi_id' => $programStudiId,
                    'angkatan' => $angkatan,
                ]);
                $existingMahasiswas->put($nim, $mahasiswa);
                $this->mahasiswaCreated++;
            }

            $this->processPengajuan($mahasiswa, $judul);
        }
    }

    /**
     * Terapkan business rule: satu mahasiswa hanya boleh punya
     * satu pengajuan per gelombang.
     */
    private function processPengajuan(Mahasiswa $mahasiswa, string $judul): void
    {
        $pengajuan = $this->existingPengajuans->get($mahasiswa->id);

        // Case 1: belum ada pengajuan pada gelombang ini -> create baru.
        if (! $pengajuan) {
            $pengajuan = PengajuanJudul::create([
                'mahasiswa_id' => $mahasiswa->id,
                'gelombang_id' => $this->gelombang->id,
                'judul_skripsi' => $judul,
                'status' => 'pending',
                'file_proposal' => null,
                'alasan_penolakan' => null,
                'divalidasi_oleh' => null,
                'validated_at' => null,
            ]);
            $this->existingPengajuans->put($mahasiswa->id, $pengajuan);
            $this->submissionsCreated++;

            return;
        }

        // Case 4: sudah disetujui -> skip total, tidak boleh diubah lewat import.
        if ($pengajuan->isDisetujui()) {
            $this->submissionsSkippedApproved++;

            return;
        }

        // Case 3: ditolak -> update judul & reset status ke pending.
        if ($pengajuan->isDitolak()) {
            $pengajuan->update([
                'judul_skripsi' => $judul,
                'status' => 'pending',
                'alasan_penolakan' => null,
                'divalidasi_oleh' => null,
                'validated_at' => null,
            ]);
            $this->submissionsResetPending++;

            return;
        }

        // Case 2: masih pending -> update judul, status tetap pending.
        $pengajuan->update([
            'judul_skripsi' => $judul,
        ]);
        $this->submissionsUpdated++;
    }
}
