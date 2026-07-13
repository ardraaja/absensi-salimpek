<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash; 

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Wali Nagari',
            'email' => 'admin@salimpek.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nik' => '1300000000000001',
            'nip' => '198501012010011001',
            'jabatan' => 'Administrator Sistem',
            'status_kerja' => 'Tetap',
        ]);

        User::create([
            'name' => 'Budi Setiawan',
            'email' => 'budi@salimpek.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
            'nik' => '1300000000000002',
            'nip' => null,
            'jabatan' => 'Staf Administrasi',
            'status_kerja' => 'Kontrak',
        ]);
    }
}