<?php

namespace App\Imports;

use App\Services\PenetapanDospembingImportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenetapanDospembingImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private readonly PenetapanDospembingImportService $penetapanDospembingImportService,
    ) {}

    public function collection(Collection $rows): void
    {
        $this->penetapanDospembingImportService->finalize($rows);
    }
}
