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
        Schema::create('keahlian_dosens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
 
            // Bidang keahlian utama, misal: "Machine Learning", "Deep Learning"
            $table->string('bidang_keahlian');
 
            // Deskripsi lengkap yang akan di-TF-IDF-kan dan dibandingkan dengan judul mhs
            $table->text('deskripsi_keahlian');
 
            // Bobot kepentingan keahlian ini (core factor vs secondary factor untuk Profile Matching)
            // 1 = core factor, 2 = secondary factor
            $table->unsignedTinyInteger('tipe_faktor')->default(1)->comment('1=core, 2=secondary');
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keahlian_dosens');
    }
};
