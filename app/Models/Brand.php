<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brand';
    protected $primaryKey = 'kode_brand';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['kode_brand', 'nama_brand', 'logo'];

    public static function generateKodeBrand($nama_brand)
    {
        // Hilangkan spasi dan ubah ke huruf besar
        $nama = strtoupper(str_replace(' ', '', $nama_brand));

        // Ambil huruf unik dari nama (tanpa pengulangan karakter)
        $hurufUnik = '';
        $used = [];

        for ($i = 0; $i < strlen($nama) && strlen($hurufUnik) < 4; $i++) {
            $char = $nama[$i];
            if (!in_array($char, $used)) {
                $hurufUnik .= $char;
                $used[] = $char;
            }
        }

        // Jika kurang dari 4 karakter unik, tambahkan huruf dari nama
        if (strlen($hurufUnik) < 4) {
            $hurufUnik = str_pad($hurufUnik, 4, 'X');
        }

        // Pastikan kode unik (cek apakah sudah ada kode serupa)
        $existing = self::where('kode_brand', 'LIKE', "$hurufUnik%")->count();
        return $existing ? $hurufUnik . ($existing + 1) : $hurufUnik;
    }
}
