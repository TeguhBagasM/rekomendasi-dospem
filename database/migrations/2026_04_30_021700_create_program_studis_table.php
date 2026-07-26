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
        Schema::create('program_studis', function (Blueprint $table) {
            $table->id();
            $table->string('program_studi', 100); // Nama program studi maks 100 karakter
            $table->string('kode_prodi', 10)->unique(); // Kode prodi umumnya 5-10 karakter
            $table->string('fakultas', 100); // Nama fakultas maks 100 karakter
            $table->string('jenjang', 10);
            $table->string('kaprodi', 100); // Nama kaprodi maks 100 karakter
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_studis');
    }
};
