<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    protected $primaryKey = 'id_pengiriman';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pengiriman',
        'kode_transaksi',
        'nomor_resi',
        'ekspedisi',
        'layanan_ekspedisi',
        'status_pengiriman',
        'tanggal_pengiriman',
        'tanggal_terkirim',
        'estimasi_tiba',
        'catatan_pengiriman',
        'riwayat_tracking',
        'terakhir_update_tracking',
        'sudah_sampai'
    ];

    protected $casts = [
        'tanggal_pengiriman' => 'datetime',
        'tanggal_terkirim' => 'datetime',
        'estimasi_tiba' => 'datetime',
        'terakhir_update_tracking' => 'datetime',
        'riwayat_tracking' => 'array',
        'sudah_sampai' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_pengiriman)) {
                $model->id_pengiriman = (string) Str::uuid();
            }
        });
    }

    // Relasi dengan Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'kode_transaksi', 'kode_transaksi');
    }

    // Mendapatkan status pengiriman dalam bahasa yang lebih friendly
    public function getStatusPengirimanTextAttribute()
    {
        $statusMap = [
            'menunggu_pengiriman' => 'Menunggu Pengiriman',
            'dikemas' => 'Sedang Dikemas',
            'diserahkan_ke_kurir' => 'Diserahkan ke Kurir',
            'dalam_perjalanan' => 'Dalam Perjalanan',
            'tiba_di_kota_tujuan' => 'Tiba di Kota Tujuan',
            'sedang_diantar' => 'Sedang Diantar',
            'terkirim' => 'Terkirim',
            'gagal_kirim' => 'Gagal Kirim'
        ];

        return $statusMap[$this->status_pengiriman] ?? $this->status_pengiriman;
    }

    // Mendapatkan tracking history terbaru
    public function getTrackingTerbaruAttribute()
    {
        if (empty($this->riwayat_tracking)) {
            return null;
        }

        $tracking = is_array($this->riwayat_tracking) ? $this->riwayat_tracking : json_decode($this->riwayat_tracking, true);

        return !empty($tracking) ? end($tracking) : null;
    }

    // Cek apakah pengiriman sudah selesai
    public function isSelesai()
    {
        return in_array($this->status_pengiriman, ['terkirim']);
    }

    // Cek apakah pengiriman gagal
    public function isGagal()
    {
        return in_array($this->status_pengiriman, ['gagal_kirim']);
    }

    // Update tracking dari API Raja Ongkir
    public function updateTracking($trackingData)
    {
        $riwayatLama = $this->riwayat_tracking ?? [];

        // Tambahkan data tracking baru
        $riwayatBaru = array_merge($riwayatLama, [$trackingData]);

        $this->update([
            'riwayat_tracking' => $riwayatBaru,
            'terakhir_update_tracking' => now(),
            'status_pengiriman' => $this->mapStatusFromAPI($trackingData['status'] ?? ''),
            'sudah_sampai' => $this->checkIfDelivered($trackingData['status'] ?? '')
        ]);

        // Update tanggal terkirim jika sudah sampai
        if ($this->sudah_sampai && !$this->tanggal_terkirim) {
            $this->update(['tanggal_terkirim' => now()]);
        }
    }

    // Mapping status dari API ke status internal
    private function mapStatusFromAPI($apiStatus)
    {
        $statusMap = [
            'SHIPMENT_RECEIVED' => 'dikemas',
            'PICKED_UP' => 'diserahkan_ke_kurir',
            'IN_TRANSIT' => 'dalam_perjalanan',
            'ARRIVED_AT_DESTINATION' => 'tiba_di_kota_tujuan',
            'OUT_FOR_DELIVERY' => 'sedang_diantar',
            'DELIVERED' => 'terkirim',
            'FAILED_DELIVERY' => 'gagal_kirim'
        ];

        return $statusMap[$apiStatus] ?? $this->status_pengiriman;
    }

    // Cek apakah sudah terkirim berdasarkan status API
    private function checkIfDelivered($apiStatus)
    {
        return in_array($apiStatus, ['DELIVERED']);
    }
}
