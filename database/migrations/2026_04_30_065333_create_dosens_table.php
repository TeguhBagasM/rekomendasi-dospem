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
         Schema::create('dosens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('program_studi_id')
                ->nullable()
                ->constrained('program_studis')
                ->nullOnDelete();

            $table->string('nidn')->unique();
            $table->string('nama');

            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('jabatan_fungsional', 50)->nullable();

            $table->unsignedInteger('kuota_dospem1')
                ->default(15);

            $table->unsignedInteger('kuota_dospem2')
                ->default(10);

            $table->unsignedInteger('kuota_terpakai_dospem1')
                ->default(0);

            $table->unsignedInteger('kuota_terpakai_dospem2')
                ->default(0);

            $table->string('no_hp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
