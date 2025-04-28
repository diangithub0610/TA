<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlamatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama_penerima' => 'required|string|max:50',
            'no_hp_penerima' => 'required|string|max:15',
            'alamat_lengkap' => 'required|string',
            'rajaongkir_id' => 'required|string',
            'provinsi' => 'required|string|max:50',
            'kota' => 'required|string|max:50',
            'kecamatan' => 'required|string|max:50',
            'kelurahan' => 'string|max:50|nullable',
            'kode_pos' => 'required|string|max:10',
            'jenis' => 'in:rumah,kantor,lainnya',
            'catatan' => 'nullable|string',
            'is_utama' => 'in:1,0'
        ];
    }
}
