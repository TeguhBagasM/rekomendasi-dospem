<?php

namespace App\Http\Controllers;

use App\Exports\RekomendasiExport;
use App\Services\ProfileMatchingService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RekomendasiController extends Controller
{
    public function __construct(
        private readonly ProfileMatchingService $profileMatchingService
    ) {}

    public function export()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isKaprodi()), 403);

        $filename = 'rekomendasi-dospem-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new RekomendasiExport($this->profileMatchingService),
            $filename
        );
    }
}