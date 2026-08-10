<?php

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Services\TextSimilarityService;

function similarityFixtures(): array
{
    $prodi = ProgramStudi::create([
        'program_studi' => 'Teknik Informatika',
        'kode_prodi' => 'TI',
        'jenjang' => 'S1',
        'kaprodi' => 'Kaprodi',
    ]);

    $gelombang = Gelombang::create([
        'nama' => 'Gelombang 2026 Ganjil',
        'periode_akademik' => '20261',
        'tanggal_mulai' => '2026-01-01',
        'tanggal_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $mahasiswaA = Mahasiswa::create([
        'user_id' => User::factory()->create()->id,
        'nim' => '20001001',
        'nama' => 'Mahasiswa A',
        'program_studi_id' => $prodi->id,
        'angkatan' => '2020',
    ]);

    $mahasiswaB = Mahasiswa::create([
        'user_id' => User::factory()->create()->id,
        'nim' => '20001002',
        'nama' => 'Mahasiswa B',
        'program_studi_id' => $prodi->id,
        'angkatan' => '2020',
    ]);

    return compact('gelombang', 'mahasiswaA', 'mahasiswaB');
}

function buatPengajuan(int $mahasiswaId, int $gelombangId, string $judul, string $status): PengajuanJudul
{
    return PengajuanJudul::create([
        'mahasiswa_id' => $mahasiswaId,
        'gelombang_id' => $gelombangId,
        'judul_skripsi' => $judul,
        'file_proposal' => null,
        'status' => $status,
    ]);
}

test('checkSimilarity memblokir judul yang mirip dengan judul mahasiswa lain yang sudah disetujui', function () {
    $f = similarityFixtures();
    $judul = 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Metode Profile Matching';

    buatPengajuan($f['mahasiswaB']->id, $f['gelombang']->id, $judul, 'disetujui');

    $result = app(TextSimilarityService::class)->checkSimilarity($judul);

    expect($result['is_similar'])->toBeTrue();
});

test('checkSimilarity mengabaikan judul mahasiswa itu sendiri saat pengajuan ulang', function () {
    $f = similarityFixtures();
    $judul = 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Metode Profile Matching';

    buatPengajuan($f['mahasiswaA']->id, $f['gelombang']->id, $judul, 'ditolak');

    $result = app(TextSimilarityService::class)->checkSimilarity(
        $judul,
        excludeMahasiswaId: $f['mahasiswaA']->id
    );

    expect($result['is_similar'])->toBeFalse();
});

test('checkSimilarity tetap memblokir judul mahasiswa lain meski sedang mengajukan ulang', function () {
    $f = similarityFixtures();
    $judul = 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Metode Profile Matching';

    buatPengajuan($f['mahasiswaA']->id, $f['gelombang']->id, $judul, 'ditolak');
    buatPengajuan($f['mahasiswaB']->id, $f['gelombang']->id, $judul, 'disetujui');

    $result = app(TextSimilarityService::class)->checkSimilarity(
        $judul,
        excludeMahasiswaId: $f['mahasiswaA']->id
    );

    expect($result['is_similar'])->toBeTrue();
});

test('checkSimilarity mengabaikan judul berstatus ditolak', function () {
    $f = similarityFixtures();
    $judul = 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Metode Profile Matching';

    buatPengajuan($f['mahasiswaB']->id, $f['gelombang']->id, $judul, 'ditolak');

    $result = app(TextSimilarityService::class)->checkSimilarity($judul);

    expect($result['is_similar'])->toBeFalse();
});

test('checkSimilarity mengabaikan judul berstatus pending', function () {
    $f = similarityFixtures();
    $judul = 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Metode Profile Matching';

    buatPengajuan($f['mahasiswaB']->id, $f['gelombang']->id, $judul, 'pending');

    $result = app(TextSimilarityService::class)->checkSimilarity($judul);

    expect($result['is_similar'])->toBeFalse();
});

test('checkSimilarity mengabaikan pengajuan yang sedang diedit (excludePengajuanId)', function () {
    $f = similarityFixtures();
    $judul = 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Metode Profile Matching';

    $pengajuan = buatPengajuan($f['mahasiswaA']->id, $f['gelombang']->id, $judul, 'pending');

    $result = app(TextSimilarityService::class)->checkSimilarity($judul, $pengajuan->id);

    expect($result['is_similar'])->toBeFalse();
});
