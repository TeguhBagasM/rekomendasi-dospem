<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'dosen']);
        Role::firstOrCreate(['name' => 'mahasiswa']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'], 
            [
                'name' => 'Admin',
                'password' => Hash::make('111'),
            ]
        );

        $admin->assignRole('admin');

        $this->call([
            ProdiSeeder::class,
            DosenSeeder::class,
            KeahlianDosenSeeder::class,
            MahasiswaSeeder::class,
            PengajuanSeeder::class,
        ]);
    }
}
