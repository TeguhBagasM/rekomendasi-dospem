<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuota_dosen_gelombangs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->foreignId('gelombang_id')->constrained('gelombangs')->cascadeOnDelete();
            $table->unsignedSmallInteger('kuota_dospem1')->default(0);
            $table->unsignedSmallInteger('kuota_dospem2')->default(0);
            $table->unsignedSmallInteger('kuota_terpakai_dospem1')->default(0);
            $table->unsignedSmallInteger('kuota_terpakai_dospem2')->default(0);
            $table->timestamps();

            $table->unique(['dosen_id', 'gelombang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuota_dosen_gelombangs');
    }
};
