<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Spatie\Permission\Models\Role;

class MahasiswaImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $programStudi = ! empty($data['program_studi_id'])
            ? ProgramStudi::find($data['program_studi_id'])
            : null;

        if (empty($data['nim']) || empty($data['nama']) || ! $programStudi || empty($data['angkatan'])) {
            return;
        }

        $email = $data['email'] ?? null;
        if (! $email) {
            $email = $data['nim'].'@stmik-mi.ac.id';
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $data['nama'],
                'email' => $email,
                'password' => Hash::make($data['nim']),
            ]
        );

        $role = Role::firstOrCreate(['name' => 'mahasiswa']);
        $user->assignRole($role);

        Mahasiswa::updateOrCreate(
            ['nim' => $data['nim']],
            [
                'user_id' => $user->id,
                'nama' => $data['nama'],
                'program_studi_id' => $programStudi->id,
                'angkatan' => $data['angkatan'],
                'no_hp' => $data['no_hp'] ?? null,
            ]
        );
    }
}
