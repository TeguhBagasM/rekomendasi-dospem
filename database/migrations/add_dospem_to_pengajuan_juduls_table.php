<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_juduls', function (Blueprint $table): void {
            if (! Schema::hasColumn('pengajuan_juduls', 'dospem1_id')) {
                $table->foreignId('dospem1_id')->nullable()->constrained('dosens')->nullOnDelete();
            }

            if (! Schema::hasColumn('pengajuan_juduls', 'dospem2_id')) {
                $table->foreignId('dospem2_id')->nullable()->constrained('dosens')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_juduls', function (Blueprint $table): void {
            if (Schema::hasColumn('pengajuan_juduls', 'dospem2_id')) {
                $table->dropConstrainedForeignId('dospem2_id');
            }

            if (Schema::hasColumn('pengajuan_juduls', 'dospem1_id')) {
                $table->dropConstrainedForeignId('dospem1_id');
            }
        });
    }
};
