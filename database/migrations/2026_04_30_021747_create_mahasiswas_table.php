<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nim', 20)->unique(); // NIM umumnya 8-15 karakter
            $table->string('nama', 100); // Nama mahasiswa maks 100 karakter
            $table->foreignId('program_studi_id')->constrained('program_studis')->restrictOnDelete();
            $table->string('angkatan', 4);
            $table->string('no_hp', 20)->nullable(); // Nomor HP maks 15 digit + prefix
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
