<?php

use App\Http\Controllers\DosenController;
use App\Http\Controllers\KeahlianDosenController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PengajuanJudulController;
use App\Http\Controllers\PengajuanValidasiController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('pengajuan', PengajuanJudulController::class);
    Route::post('pengajuan/{pengajuan}/approve', [PengajuanJudulController::class, 'approve'])->name('pengajuan.approve');
    Route::post('pengajuan/{pengajuan}/reject', [PengajuanJudulController::class, 'reject'])->name('pengajuan.reject');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);
    Route::get('mahasiswa/export', [MahasiswaController::class, 'export'])->name('mahasiswa.export');
    Route::post('mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
    Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
    Route::resource('prodi', ProdiController::class)->except(['show']);
    Route::resource('dosen', DosenController::class);
    Route::post('/dosen/{dosen}/keahlian', [KeahlianDosenController::class, 'store'])->name('keahlian.store');
    Route::put('/keahlian/{id}', [KeahlianDosenController::class, 'update'])->name('keahlian.update');
    Route::delete('/keahlian/{id}', [KeahlianDosenController::class, 'destroy'])->name('keahlian.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('pengajuan', [PengajuanValidasiController::class, 'index'])->name('pengajuan.index');
        Route::post('pengajuan/{pengajuan}/approve', [PengajuanValidasiController::class, 'approve'])->name('pengajuan.approve');
        Route::post('pengajuan/{pengajuan}/reject', [PengajuanValidasiController::class, 'reject'])->name('pengajuan.reject');
    });
});

// Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
//     Route::get('/mahasiswa/judul', [JudulController::class, 'index'])->name('mahasiswa.judul');
//     Route::post('/mahasiswa/judul', [JudulController::class, 'store'])->name('mahasiswa.judul.store');
// });

// Route::middleware(['auth', 'role:admin'])->group(function () {
//     Route::get('/admin/judul', [JudulController::class, 'adminIndex'])->name('admin.judul');
//     Route::post('/admin/judul/{id}/approve', [JudulController::class, 'approve'])->name('admin.judul.approve');
//     Route::post('/admin/judul/{id}/reject', [JudulController::class, 'reject'])->name('admin.judul.reject');
// });

require __DIR__.'/auth.php';
