<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->foreignId('program_studi_id')
                ->nullable()
                ->after('nama')
                ->constrained('program_studis')
                ->nullOnDelete();
        });

        DB::unprepared(<<<'SQL'
update mahasiswas m
left join program_studis p on p.program_studi = m.program_studi
set m.program_studi_id = p.id
where m.program_studi_id is null
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_studi_id');
        });
    }
};