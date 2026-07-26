<?php

namespace App\Services;

use App\Imports\PengajuanJudulImport;
use App\Models\Gelombang;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PengajuanJudulImportService
{
    public function import(UploadedFile $file): PengajuanJudulImport
    {
        $gelombang = Gelombang::active()->first();

        if (! $gelombang) {
            throw ValidationException::withMessages([
                'file' => 'Import dibatalkan: tidak ada Gelombang yang sedang aktif.',
            ]);
        }

        $import = new PengajuanJudulImport($gelombang);

        DB::transaction(function () use ($import, $file) {
            Excel::import($import, $file);
        });

        return $import;
    }
}
