<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PenggunaSeeder extends Seeder
{
    public function run()
    {
        $penggunas = [
            [
                'id_admin' => 'ADM001',
                'nama_admin' => 'Admin Gudang',
                'no_hp' => '081234567890',
                'email' => 'gudang@gmail.com',
                'username' => 'gudang',
                'role' => 'gudang',
                'foto_profil' => null,
                'kata_sandi' => Hash::make('password')
            ],
            [
                'id_admin' => 'ADM002',
                'nama_admin' => 'Admin Pemesanan',
                'no_hp' => '082345678901',
                'email' => 'pemesanan@gmail.com',
                'username' => 'pemesanan',
                'role' => 'pemesanan',
                'foto_profil' => null,
                'kata_sandi' => Hash::make('password')
            ],
            [
                'id_admin' => 'ADM003',
                'nama_admin' => 'Owner Perusahaan',
                'no_hp' => '083456789012',
                'email' => 'owner@gmail.com',
                'username' => 'owner',
                'role' => 'owner',
                'foto_profil' => null,
                'kata_sandi' => Hash::make('password')
            ]
        ];

        // Insert data into Pengguna table
        DB::table('Pengguna')->insert($penggunas);
    }
}
