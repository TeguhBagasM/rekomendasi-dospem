<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriterias', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 20)->unique(); // Kode kriteria seperti C1, C2
            $table->string('nama', 100); // Nama kriteria maks 100 karakter
            $table->enum('jenis', ['core', 'secondary']);
            $table->decimal('bobot', 5, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('sub_kriterias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kriteria_id')->constrained('kriterias')->cascadeOnDelete();
            $table->string('kode', 20); // Kode sub kriteria seperti C1.1
            $table->string('nama', 100); // Nama sub kriteria maks 100 karakter
            $table->decimal('nilai_min', 5, 2)->nullable();
            $table->decimal('nilai_max', 5, 2)->nullable();
            $table->decimal('nilai', 5, 2)->default(0);
            $table->decimal('bobot', 5, 2)->default(0);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['kriteria_id', 'kode']);
        });

        Schema::create('riwayat_rekomendasis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->enum('status', ['draft', 'exported', 'imported', 'finalized'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('rekomendasi_dospembings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('riwayat_rekomendasi_id')->constrained('riwayat_rekomendasis')->cascadeOnDelete();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_juduls')->cascadeOnDelete();
            $table->foreignId('rekomendasi_dospem1_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('rekomendasi_dospem2_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->decimal('skor_dospem1', 8, 4)->nullable();
            $table->decimal('skor_dospem2', 8, 4)->nullable();
            $table->json('ringkasan')->nullable();
            $table->timestamps();

            $table->unique(['riwayat_rekomendasi_id', 'pengajuan_id'], 'rek_batch_pengajuan_unique');
        });

        Schema::create('hasil_perhitungans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rekomendasi_dospembing_id')->constrained('rekomendasi_dospembings')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->enum('jenis_dospem', ['dospem1', 'dospem2']);
            $table->decimal('topic_similarity', 8, 4)->default(0);
            $table->unsignedTinyInteger('topic_scale')->default(0);
            $table->unsignedTinyInteger('homebase_scale')->nullable();
            $table->unsignedTinyInteger('quota_scale')->default(0);
            $table->unsignedTinyInteger('jabatan_scale')->default(0);
            $table->unsignedTinyInteger('pendidikan_scale')->default(0);
            $table->decimal('cf_score', 8, 4)->default(0);
            $table->decimal('sf_score', 8, 4)->default(0);
            $table->decimal('final_score', 8, 4)->default(0);
            $table->unsignedInteger('ranking')->default(0);
            $table->json('detail')->nullable();
            $table->timestamps();

            $table->unique(['rekomendasi_dospembing_id', 'dosen_id', 'jenis_dospem'], 'hasil_perhitungan_unique');
        });

        Schema::create('penetapan_dospembings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rekomendasi_dospembing_id')->nullable()->constrained('rekomendasi_dospembings')->nullOnDelete();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_juduls')->cascadeOnDelete();
            $table->foreignId('dospem1_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('dospem2_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('ditetapkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditetapkan_at')->nullable();
            $table->enum('sumber', ['import', 'manual'])->default('import');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique('pengajuan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penetapan_dospembings');
        Schema::dropIfExists('hasil_perhitungans');
        Schema::dropIfExists('rekomendasi_dospembings');
        Schema::dropIfExists('riwayat_rekomendasis');
        Schema::dropIfExists('sub_kriterias');
        Schema::dropIfExists('kriterias');
    }
};