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
        Schema::create('dosen_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dosen_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->text('topik_metode')->nullable();

            $table->longText('judul_penelitian')
                ->nullable();

            $table->longText('dokumen_tfidf')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_profiles');
    }
};
