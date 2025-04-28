<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PelangganSeeder extends Seeder
{
    public function run()
    {
        $pelanggans = [
            [
                'id_pelanggan' => 'PLG001',
                'nama_pelanggan' => 'Regular Customer',
                'no_hp' => '085678901234',
                'email' => 'pelanggan@gmail.com',
                'alamat' => 'Jalan Pelanggan No. 1',
                'username' => 'reguler',
                'role' => 'pelanggan',
                'foto_profil' => null,
                'kata_sandi' => Hash::make('password')
            ],
            [
                'id_pelanggan' => 'PLG002',
                'nama_pelanggan' => 'Reseller Top',
                'no_hp' => '086789012345',
                'email' => 'reseller@gmail.com',
                'alamat' => 'Jalan Reseller No. 2',
                'username' => 'reseller',
                'role' => 'reseller',
                'foto_profil' => null,
                'kata_sandi' => Hash::make('password')
            ]
        ];

        // Insert data into Pelanggan table
        DB::table('Pelanggan')->insert($pelanggans);
    }
}
