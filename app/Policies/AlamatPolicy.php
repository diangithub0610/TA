<?php

namespace App\Policies;

use App\Models\Pelanggan;
use App\Models\Alamat;

class AlamatPolicy
{
    public function update(Pelanggan $user, Alamat $alamat)
    {
        return $user->id_pelanggan === $alamat->id_pelanggan;
    }

    public function delete(Pelanggan $user, Alamat $alamat)
    {
        return $user->id_pelanggan === $alamat->id_pelanggan;
    }
}
