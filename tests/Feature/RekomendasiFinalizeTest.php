<?php

use App\Models\Dosen;
use App\Models\Gelombang;
use App\Models\KuotaDosenGelombang;
use App\Models\Mahasiswa;
use App\Models\PenetapanDospembing;
use App\Models\PengajuanJudul;
use App\Models\ProgramStudi;
use App\Models\RekomendasiDospembing;
use App\Models\RiwayatRekomendasi;
use App\Models\User;
use Spatie\Permission\Models\Role;

function finalisasiFixtures(): array
{
    Role::findOrCreate('admin');

    $prodi = ProgramStudi::create([
        'program_studi' => 'Teknik Informatika',
        'kode_prodi' => 'TI',
        'fakultas' => 'FTIK',
        'jenjang' => 'S1',
        'kaprodi' => 'Kaprodi',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $gelombang = Gelombang::create([
        'nama' => 'Gelombang 2026 Ganjil',
        'periode_akademik' => '20261',
        'tanggal_mulai' => '2026-01-01',
        'tanggal_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $dosen1 = Dosen::create([
        'user_id' => null,
        'program_studi_id' => $prodi->id,
        'nidn' => '0011010101',
        'nama' => 'Dosen Satu',
        'pendidikan_terakhir' => 'S3',
        'jabatan_fungsional' => 'Lektor',
        'kuota_dospem1' => 5,
        'kuota_dospem2' => 5,
    ]);

    $dosen2 = Dosen::create([
        'user_id' => null,
        'program_studi_id' => $prodi->id,
        'nidn' => '0011010102',
        'nama' => 'Dosen Dua',
        'pendidikan_terakhir' => 'S2',
        'jabatan_fungsional' => 'Asisten Ahli',
        'kuota_dospem1' => 5,
        'kuota_dospem2' => 5,
    ]);

    $dosen3 = Dosen::create([
        'user_id' => null,
        'program_studi_id' => $prodi->id,
        'nidn' => '0011010103',
        'nama' => 'Dosen Tiga',
        'pendidikan_terakhir' => 'S2',
        'jabatan_fungsional' => 'Tenaga Pengajar',
        'kuota_dospem1' => 5,
        'kuota_dospem2' => 5,
    ]);

    foreach ([$dosen1, $dosen2, $dosen3] as $dosen) {
        KuotaDosenGelombang::create([
            'dosen_id' => $dosen->id,
            'gelombang_id' => $gelombang->id,
            'kuota_dospem1' => 5,
            'kuota_dospem2' => 5,
        ]);
    }

    $mahasiswaUser = User::factory()->create();

    $mahasiswa = Mahasiswa::create([
        'user_id' => $mahasiswaUser->id,
        'nim' => '20001001',
        'nama' => 'Mahasiswa Satu',
        'program_studi_id' => $prodi->id,
        'angkatan' => '2020',
    ]);

    $pengajuan = PengajuanJudul::create([
        'mahasiswa_id' => $mahasiswa->id,
        'judul_skripsi' => 'Sistem Rekomendasi Dosen Pembimbing Menggunakan Profile Matching',
        'file_proposal' => null,
        'status' => 'disetujui',
        'gelombang_id' => $gelombang->id,
    ]);

    $riwayat = RiwayatRekomendasi::create([
        'generated_by' => $admin->id,
        'generated_at' => now(),
        'status' => 'draft',
    ]);

    $rekomendasi = RekomendasiDospembing::create([
        'riwayat_rekomendasi_id' => $riwayat->id,
        'pengajuan_id' => $pengajuan->id,
        'rekomendasi_dospem1_id' => $dosen1->id,
        'rekomendasi_dospem2_id' => $dosen2->id,
        'skor_dospem1' => 0.9500,
        'skor_dospem2' => 0.8700,
        'ringkasan' => ['catatan' => 'Catatan admin finalisasi'],
    ]);

    return compact('admin', 'gelombang', 'dosen1', 'dosen2', 'dosen3', 'pengajuan', 'riwayat', 'rekomendasi', 'mahasiswaUser');
}

test('halaman tinjau draft dapat diakses oleh admin', function () {
    $f = finalisasiFixtures();

    $this->actingAs($f['admin'])
        ->get(route('rekomendasi.review', $f['riwayat']->id))
        ->assertOk();
});

test('finalisasi menyinkronkan draft ke penetapan dan memakai kuota resmi', function () {
    $f = finalisasiFixtures();

    $this->actingAs($f['admin'])
        ->from(route('rekomendasi.review', $f['riwayat']->id))
        ->post(route('rekomendasi.finalize', $f['riwayat']->id))
        ->assertRedirect(route('rekomendasi.review', $f['riwayat']->id))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $this->assertDatabaseCount('penetapan_dospembings', 1);
    $this->assertDatabaseHas('penetapan_dospembings', [
        'pengajuan_id' => $f['pengajuan']->id,
        'rekomendasi_dospembing_id' => $f['rekomendasi']->id,
        'dospem1_id' => $f['dosen1']->id,
        'dospem2_id' => $f['dosen2']->id,
        'ditetapkan_oleh' => $f['admin']->id,
        'sumber' => 'manual',
        'catatan' => 'Catatan admin finalisasi',
    ]);

    expect($f['riwayat']->refresh()->status)->toBe('finalized');

    expect($f['dosen1']->refresh()->kuota_terpakai_dospem1)->toBe(1);
    expect($f['dosen1']->refresh()->kuota_terpakai_dospem2)->toBe(0);
    expect($f['dosen2']->refresh()->kuota_terpakai_dospem2)->toBe(1);
    expect($f['dosen2']->refresh()->kuota_terpakai_dospem1)->toBe(0);

    expect(KuotaDosenGelombang::where('dosen_id', $f['dosen1']->id)->where('gelombang_id', $f['gelombang']->id)->first()->kuota_terpakai_dospem1)->toBe(1);
    expect(KuotaDosenGelombang::where('dosen_id', $f['dosen2']->id)->where('gelombang_id', $f['gelombang']->id)->first()->kuota_terpakai_dospem2)->toBe(1);
});

test('finalisasi tidak dapat dijalankan dua kali untuk riwayat yang sama', function () {
    $f = finalisasiFixtures();

    $this->actingAs($f['admin'])
        ->post(route('rekomendasi.finalize', $f['riwayat']->id));

    $this->actingAs($f['admin'])
        ->from(route('rekomendasi.review', $f['riwayat']->id))
        ->post(route('rekomendasi.finalize', $f['riwayat']->id))
        ->assertSessionHasErrors('riwayat');

    $this->assertDatabaseCount('penetapan_dospembings', 1);
    expect($f['dosen1']->refresh()->kuota_terpakai_dospem1)->toBe(1);
});

test('mahasiswa tidak dapat mengakses finalisasi penetapan', function () {
    $f = finalisasiFixtures();

    Role::findOrCreate('mahasiswa');
    $mahasiswa = $f['mahasiswaUser'];
    $mahasiswa->assignRole('mahasiswa');

    $this->actingAs($mahasiswa)
        ->post(route('rekomendasi.finalize', $f['riwayat']->id))
        ->assertForbidden();

    $this->assertDatabaseCount('penetapan_dospembings', 0);
});

test('finalisasi melepas kuota penetapan lama sebelum memakai kuota baru', function () {
    $f = finalisasiFixtures();

    PenetapanDospembing::create([
        'rekomendasi_dospembing_id' => $f['rekomendasi']->id,
        'pengajuan_id' => $f['pengajuan']->id,
        'dospem1_id' => $f['dosen3']->id,
        'dospem2_id' => $f['dosen2']->id,
        'ditetapkan_oleh' => $f['admin']->id,
        'ditetapkan_at' => now(),
        'sumber' => 'import',
    ]);

    $f['dosen3']->increment('kuota_terpakai_dospem1');
    $f['dosen2']->increment('kuota_terpakai_dospem2');
    KuotaDosenGelombang::where('dosen_id', $f['dosen3']->id)->where('gelombang_id', $f['gelombang']->id)->increment('kuota_terpakai_dospem1');
    KuotaDosenGelombang::where('dosen_id', $f['dosen2']->id)->where('gelombang_id', $f['gelombang']->id)->increment('kuota_terpakai_dospem2');

    $this->actingAs($f['admin'])
        ->from(route('rekomendasi.review', $f['riwayat']->id))
        ->post(route('rekomendasi.finalize', $f['riwayat']->id))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('penetapan_dospembings', 1);
    $this->assertDatabaseHas('penetapan_dospembings', [
        'pengajuan_id' => $f['pengajuan']->id,
        'dospem1_id' => $f['dosen1']->id,
        'dospem2_id' => $f['dosen2']->id,
        'sumber' => 'manual',
    ]);

    expect($f['dosen3']->refresh()->kuota_terpakai_dospem1)->toBe(0);
    expect($f['dosen1']->refresh()->kuota_terpakai_dospem1)->toBe(1);
    expect($f['dosen2']->refresh()->kuota_terpakai_dospem2)->toBe(1);
});
