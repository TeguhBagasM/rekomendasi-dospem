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
        Schema::create('dosen_bidang_keahlian', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dosen_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('bidang_keahlian_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique([
                'dosen_id',
                'bidang_keahlian_id'
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_bidang_keahlian');
    }
};
