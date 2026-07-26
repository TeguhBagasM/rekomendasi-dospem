<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table): void {
            if (Schema::hasColumn('mahasiswas', 'program_studi_id')) {
                $table->unsignedBigInteger('program_studi_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table): void {
            if (Schema::hasColumn('mahasiswas', 'program_studi_id')) {
                $table->unsignedBigInteger('program_studi_id')->nullable(false)->change();
            }
        });
    }
};
