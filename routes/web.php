<?php

use App\Http\Controllers\PermissionController;
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
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
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
