<?php

use App\Http\Controllers\Admin\PengajuanJudulManajemenController;
use App\Http\Controllers\BidangKeahlianController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\GelombangController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PengajuanJudulController;
use App\Http\Controllers\PengajuanValidasiController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileDosenController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated (semua role)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Riwayat pengajuan judul milik mahasiswa sendiri.
    // Approve/reject TIDAK ada di sini — eksklusif lewat admin.pengajuan.* (PengajuanValidasiController).
    Route::resource('pengajuan', PengajuanJudulController::class);
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Manajemen Pengajuan Judul — tabel, filter, import/export
    Route::prefix('admin/pengajuan-judul')->name('admin.pengajuan-judul.')->group(function () {
        Route::get('template', [PengajuanJudulManajemenController::class, 'downloadTemplate'])->name('template');
        Route::get('export', [PengajuanJudulManajemenController::class, 'export'])->name('export');
        Route::post('import', [PengajuanJudulManajemenController::class, 'import'])->name('import');
        Route::get('/', [PengajuanJudulManajemenController::class, 'index'])->name('index');
    });

    // Validasi Pengajuan — antrian pending, approve/reject
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('validasi-pengajuan', [PengajuanValidasiController::class, 'index'])->name('validasi-pengajuan.index');
        Route::post('validasi-pengajuan/{pengajuan}/approve', [PengajuanValidasiController::class, 'approve'])->name('validasi-pengajuan.approve');
        Route::post('validasi-pengajuan/{pengajuan}/reject', [PengajuanValidasiController::class, 'reject'])->name('validasi-pengajuan.reject');
        Route::post('validasi-pengajuan/approve-all', [PengajuanValidasiController::class, 'approveAll'])->name('validasi-pengajuan.approveAll');
    });

    // Rekomendasi Dosen Pembimbing
    Route::prefix('rekomendasi')->name('rekomendasi.')->group(function () {
        Route::get('/', [RekomendasiController::class, 'index'])->name('index');
        Route::post('generate', [RekomendasiController::class, 'generate'])->name('generate');
        Route::get('export', [RekomendasiController::class, 'exportExcel'])->name('export');
        Route::post('import', [RekomendasiController::class, 'importExcel'])->name('import');
        Route::get('{riwayat}/review', [RekomendasiController::class, 'review'])->name('review');
        Route::patch('{rekomendasi}/update-draft', [RekomendasiController::class, 'updateDraft'])->name('updateDraft');
        Route::patch('{riwayat}/bulk-update-draft', [RekomendasiController::class, 'bulkUpdateDraft'])->name('bulkUpdateDraft');
        Route::get('{riwayat}/export-draft', [RekomendasiController::class, 'exportDraft'])->name('exportDraft');
        Route::post('{riwayat}/finalize', [RekomendasiController::class, 'finalize'])->name('finalize');
    });

    Route::resource('roles', RoleController::class)->except(['show']);

    Route::get('mahasiswa/template', [MahasiswaController::class, 'downloadTemplate'])->name('mahasiswa.template');
    Route::get('mahasiswa/export', [MahasiswaController::class, 'export'])->name('mahasiswa.export');
    Route::get('mahasiswa/data', [MahasiswaController::class, 'mahasiswaData'])->name('mahasiswa.data');
    Route::post('mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
    Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);

    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
    Route::resource('prodi', ProdiController::class)->except(['show']);
    Route::resource('bidang-keahlian', BidangKeahlianController::class)->except(['show']);

    Route::get('dosen/export', [DosenController::class, 'export'])->name('dosen.export');
    Route::get('dosen/template', [DosenController::class, 'downloadTemplate'])->name('dosen.template');
    Route::post('dosen/import', [DosenController::class, 'import'])->name('dosen.import');
    Route::resource('dosen', DosenController::class);

    Route::resource('gelombang', GelombangController::class);
    Route::patch('gelombang/{gelombang}/toggle-status', [GelombangController::class, 'toggleStatus'])
        ->name('gelombang.toggle-status');
});

/*
|--------------------------------------------------------------------------
| Dosen
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:dosen'])->group(function () {
    Route::get('profile-saya', [ProfileDosenController::class, 'edit'])->name('profile-dosen.edit');
    Route::put('profile-saya', [ProfileDosenController::class, 'update'])->name('profile-dosen.update');
});

require __DIR__.'/auth.php';
