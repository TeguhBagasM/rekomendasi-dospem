<?php

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

function mahasiswaPengajuanFixtures(?string $status = null): array
{
    Role::findOrCreate('mahasiswa');

    $prodi = ProgramStudi::create([
        'program_studi' => 'Teknik Informatika',
        'kode_prodi' => 'TI',
        'fakultas' => 'FTIK',
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

    $user = User::factory()->create();
    $user->assignRole('mahasiswa');

    $mahasiswa = Mahasiswa::create([
        'user_id' => $user->id,
        'nim' => '20001001',
        'nama' => 'Mahasiswa A',
        'program_studi_id' => $prodi->id,
        'angkatan' => '2020',
    ]);

    if ($status !== null) {
        PengajuanJudul::create([
            'mahasiswa_id' => $mahasiswa->id,
            'gelombang_id' => $gelombang->id,
            'judul_skripsi' => 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Metode Profile Matching',
            'file_proposal' => null,
            'status' => $status,
        ]);
    }

    return compact('gelombang', 'mahasiswa', 'user');
}

test('mahasiswa tanpa pengajuan boleh mengajukan judul baru', function () {
    $f = mahasiswaPengajuanFixtures();

    expect($f['mahasiswa']->bolehMengajukanBaru())->toBeTrue();
});

test('mahasiswa dengan pengajuan disetujui tidak boleh mengajukan judul baru', function () {
    $f = mahasiswaPengajuanFixtures('disetujui');

    expect($f['mahasiswa']->bolehMengajukanBaru())->toBeFalse();
});

test('mahasiswa dengan pengajuan pending tidak boleh mengajukan judul baru', function () {
    $f = mahasiswaPengajuanFixtures('pending');

    expect($f['mahasiswa']->bolehMengajukanBaru())->toBeFalse();
});

test('mahasiswa dengan pengajuan ditolak boleh mengajukan judul baru', function () {
    $f = mahasiswaPengajuanFixtures('ditolak');

    expect($f['mahasiswa']->bolehMengajukanBaru())->toBeTrue();
});

test('mahasiswa dengan judul disetujui tidak dapat mengakses halaman pengajuan baru', function () {
    $f = mahasiswaPengajuanFixtures('disetujui');

    $this->actingAs($f['user'])
        ->get(route('pengajuan.create'))
        ->assertRedirect(route('pengajuan.index'))
        ->assertSessionHas('error');
});

test('store sukses tidak menimpa flash success dengan error saat mengajukan ulang setelah ditolak', function () {
    $f = mahasiswaPengajuanFixtures('ditolak');

    $this->actingAs($f['user'])
        ->from(route('pengajuan.create'))
        ->post(route('pengajuan.store'), [
            'judul_skripsi' => 'Sistem Informasi Akademik Berbasis Web',
            'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('pengajuan.index'))
        ->assertSessionHas('success')
        ->assertSessionMissing('error');

    $this->assertDatabaseHas('pengajuan_juduls', [
        'mahasiswa_id' => $f['mahasiswa']->id,
        'judul_skripsi' => 'Sistem Informasi Akademik Berbasis Web',
        'status' => 'pending',
    ]);
});
