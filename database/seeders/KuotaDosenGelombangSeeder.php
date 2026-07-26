<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Gelombang;
use App\Models\KuotaDosenGelombang;
use Illuminate\Database\Seeder;

class KuotaDosenGelombangSeeder extends Seeder
{
    public function run(): void
    {
        $gelombangs = Gelombang::all();
        $dosens = Dosen::all();

        if ($gelombangs->isEmpty() || $dosens->isEmpty()) {
            return;
        }

        $jumlahGelombang = $gelombangs->count();

        foreach ($dosens as $dosen) {
            $kuotaPerGelombang1 = max((int) floor($dosen->kuota_dospem1 / $jumlahGelombang), 0);
            $kuotaPerGelombang2 = max((int) floor($dosen->kuota_dospem2 / $jumlahGelombang), 0);

            foreach ($gelombangs as $gelombang) {
                KuotaDosenGelombang::create([
                    'dosen_id' => $dosen->id,
                    'gelombang_id' => $gelombang->id,
                    'kuota_dospem1' => $kuotaPerGelombang1,
                    'kuota_dospem2' => $kuotaPerGelombang2,
                ]);
            }
        }
    }
}
