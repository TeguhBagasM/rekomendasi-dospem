<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionToRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::findByName('admin');
        $mahasiswa = Role::findByName('mahasiswa');

        $admin->givePermissionTo([
            'view_all_judul',
            'approve_judul',
            'reject_judul',
        ]);

        $mahasiswa->givePermissionTo([
            'create_judul',
            'view_judul',
        ]);
    }
}
