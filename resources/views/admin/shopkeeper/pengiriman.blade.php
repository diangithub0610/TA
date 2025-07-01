@extends('admin.layouts.app')

@section('title', 'Detail Pengiriman - ' . $pengiriman->kode_transaksi)

@section('content')
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">@yield('title')</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="{{ route('dashboard') }}">
                                    <i class="icon-home"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">@yield('title')</a>
                            </li>
                        </ul>
                    </div>
                        <div class="row">
                            <!-- Info Pengiriman -->
                            <div class="col-lg-8">
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Detail Pengiriman</h5>
                                        <div class="btn-group">
                                            @if(!$pengiriman->nomor_resi)
                                                <a href="{{ route('admin.pengiriman.edit-resi', $pengiriman->id_pengiriman) }}"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Input Resi
                                                </a>
                                            @endif

                                            @if($pengiriman->nomor_resi && !$pengiriman->isSelesai())
                                                <button type="button" class="btn btn-success btn-sm"
                                                    onclick="updateTracking('{{ $pengiriman->id_pengiriman }}')">
                                                    <i class="fas fa-sync-alt me-1"></i>Update Tracking
                                                </button>
                                            @endif

                                            <a href="{{ route('admin-transaksi.show', $pengiriman->kode_transaksi) }}" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-arrow-left me-1"></i>Kembali
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td class="fw-bold">Kode Transaksi:</td>
                                                        <td>{{ $pengiriman->kode_transaksi }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Nomor Resi:</td>
                                                        <td>
                                                            @if($pengiriman->nomor_resi)
                                                                <span class="badge bg-info fs-6">{{ $pengiriman->nomor_resi }}</span>
                                                            @else
                                                                <span class="badge bg-secondary">Belum ada resi</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Ekspedisi:</td>
                                                        <td>
                                                            <strong>{{ strtoupper($pengiriman->ekspedisi) }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $pengiriman->layanan_ekspedisi }}</small>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Status:</td>
                                                        <td>
                                                            @php
    $statusClass = match ($pengiriman->status_pengiriman) {
        'menunggu_pengiriman' => 'bg-secondary',
        'dikemas' => 'bg-info',
        'diserahkan_ke_kurir' => 'bg-primary',
        'dalam_perjalanan' => 'bg-warning',
        'tiba_di_kota_tujuan' => 'bg-warning',
        'sedang_diantar' => 'bg-warning',
        'terkirim' => 'bg-success',
        'gagal_kirim' => 'bg-danger',
        default => 'bg-secondary'
    };
                                                            @endphp
                                                            <span class="badge {{ $statusClass }} fs-6">
                                                                {{ $pengiriman->status_pengiriman_text }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td class="fw-bold">Tanggal Pengiriman:</td>
                                                        <td>
                                                            @if($pengiriman->tanggal_pengiriman)
                                                                {{ $pengiriman->tanggal_pengiriman->format('d M Y H:i') }}
                                                            @else
                                                                <span class="text-muted">Belum dikirim</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Estimasi Tiba:</td>
                                                        <td>
                                                            @if($pengiriman->estimasi_tiba)
                                                                {{ $pengiriman->estimasi_tiba->format('d M Y') }}
                                                                <br>
                                                                <small class="text-muted">
                                                                    @if($pengiriman->estimasi_tiba->isPast() && !$pengiriman->sudah_sampai)
                                                                        <span class="text-danger">Terlambat
                                                                            {{ $pengiriman->estimasi_tiba->diffForHumans() }}</span>
                                                                    @elseif(!$pengiriman->sudah_sampai)
                                                                        {{ $pengiriman->estimasi_tiba->diffForHumans() }}
                                                                    @else
                                                                        <span class="text-success">Sudah sampai</span>
                                                                    @endif
                                                                </small>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Tanggal Terkirim:</td>
                                                        <td>
                                                            @if($pengiriman->tanggal_terkirim)
                                                                {{ $pengiriman->tanggal_terkirim->format('d M Y H:i') }}
                                                            @else
                                                                <span class="text-muted">Belum terkirim</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Update Terakhir:</td>
                                                        <td>
                                                            @if($pengiriman->terakhir_update_tracking)
                                                                {{ $pengiriman->terakhir_update_tracking->format('d M Y H:i') }}
                                                            @else
                                                                <span class="text-muted">Belum pernah update</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        @if($pengiriman->catatan_pengiriman)
                                            <div class="alert alert-info mt-3">
                                                <strong>Catatan:</strong> {{ $pengiriman->catatan_pengiriman }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tracking History -->
                                <div class="card shadow-sm">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-route me-2"></i>Riwayat Tracking</h5>
                                    </div>
    {{-- {{dd($trackingData['tracking_history']);}} --}}

    <div class="card-body">


        @php
            // Fungsi untuk flatten array multi-dimensional dan mengambil data tracking
            function flattenTrackingData($data)
            {
                $result = [];

                if (!is_array($data)) {
                    return $result;
                }

                foreach ($data as $item) {
                    if (is_array($item)) {
                        // Jika item adalah array dengan key numerik (masih ada level lagi)
                        if (isset($item[0]) && is_array($item[0])) {
                            // Rekursif untuk level yang lebih dalam
                            $result = array_merge($result, flattenTrackingData($item));
                        }
                        // Jika item sudah berisi data tracking yang valid
                        elseif (isset($item['manifest_code']) || isset($item['manifest_description'])) {
                            $result[] = $item;
                        }
                        // Jika masih ada level array lagi
                        else {
                            $result = array_merge($result, flattenTrackingData($item));
                        }
                    }
                }

                return $result;
            }

            // Normalisasi data tracking
            $trackingItems = [];

            if (isset($trackingData['tracking_history'])) {
                $rawData = $trackingData['tracking_history'];

                // Flatten data multi-dimensional
                $trackingItems = flattenTrackingData($rawData);
            }

            // Urutkan berdasarkan datetime (terbaru ke terlama)
            if (!empty($trackingItems)) {
                usort($trackingItems, function ($a, $b) {
                    $timeA = isset($a['datetime']) ? strtotime($a['datetime']) : 0;
                    $timeB = isset($b['datetime']) ? strtotime($b['datetime']) : 0;
                    return $timeB - $timeA; // Descending order
                });
            }
        @endphp


        @if(!empty($trackingItems))
            <div class="tracking-timeline">
                @foreach($trackingItems as $index => $tracking)
                    @php
                        // Tentukan status berdasarkan manifest_code atau posisi
                        $status = 'completed';
                        $icon = 'fas fa-check';

                        // Item terbaru (index 0) atau status khusus
                        if ($index === 0) {
                            // Cek apakah paket sudah selesai (diterima)
                            if (isset($tracking['manifest_code']) && $tracking['manifest_code'] == '200') {
                                $status = 'completed';
                                $icon = 'fas fa-check-circle';
                            } else {
                                $status = 'in-progress';
                                $icon = 'fas fa-truck';
                            }
                        }

                        // Sesuaikan icon dan status berdasarkan manifest_code
                        if (isset($tracking['manifest_code'])) {
                            switch ($tracking['manifest_code']) {
                                case '101': // Manifes
                                    $icon = 'fas fa-box';
                                    break;
                                case '200': // Paket diterima
                                    $icon = 'fas fa-check-circle';
                                    $status = 'completed';
                                    break;
                                case '150': // Paket disimpan
                                    $icon = 'fas fa-warehouse';
                                    $status = 'pending';
                                    break;
                                default: // Code 100 dan lainnya
                                    if (isset($tracking['manifest_description'])) {
                                        $desc = strtolower($tracking['manifest_description']);
                                        if (strpos($desc, 'paket telah diterima') !== false && strpos($desc, 'alamat') === false) {
                                            $icon = 'fas fa-check-circle';
                                            $status = 'completed';
                                        } elseif (strpos($desc, 'diterima') !== false || strpos($desc, 'sampai') !== false) {
                                            $icon = 'fas fa-check';
                                        } elseif (strpos($desc, 'dikirim') !== false || strpos($desc, 'akan') !== false) {
                                            $icon = 'fas fa-truck';
                                            if ($index === 0)
                                                $status = 'in-progress';
                                        } elseif (strpos($desc, 'disimpan') !== false) {
                                            $icon = 'fas fa-warehouse';
                                            $status = 'pending';
                                        } else {
                                            $icon = 'fas fa-map-marker-alt';
                                        }
                                    }
                                    break;
                            }
                        }

                        // Format tanggal dengan pengecekan
                        $date = 'Tanggal tidak tersedia';
                        $time = 'Waktu tidak tersedia';
                        $cityName = 'Lokasi tidak tersedia';
                        $manifestCode = 'N/A';
                        $description = 'Deskripsi tidak tersedia';

                        if (isset($tracking['manifest_date'])) {
                            try {
                                $date = \Carbon\Carbon::parse($tracking['manifest_date'])->format('d M Y');
                            } catch (Exception $e) {
                                $date = $tracking['manifest_date'];
                            }
                        }

                        if (isset($tracking['manifest_time'])) {
                            $time = $tracking['manifest_time'];
                        }

                        if (isset($tracking['city_name'])) {
                            $cityName = $tracking['city_name'];
                        }

                        if (isset($tracking['manifest_code'])) {
                            $manifestCode = $tracking['manifest_code'];
                        }

                        if (isset($tracking['manifest_description'])) {
                            $description = $tracking['manifest_description'];
                        }
                    @endphp

                    <div class="tracking-item {{ $status }}">
                        <div class="tracking-icon {{ $status }}">
                            <i class="{{ $icon }}"></i>
                        </div>
                        <div class="tracking-content">
                            <div class="tracking-info">
                                <div class="tracking-title">{{ $description }}</div>
                                <div class="tracking-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $cityName }}
                                </div>
                                <span class="tracking-code">Code: {{ $manifestCode }}</span>
                            </div>
                            <div class="tracking-datetime">
                                <div class="tracking-date">{{ $date }}</div>
                                <div class="tracking-time">{{ $time }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-tracking">
                <i class="fas fa-route"></i>
                <h5>Belum ada riwayat tracking</h5>
                <p>Data tracking pengiriman akan muncul di sini</p>
                @if(isset($pengiriman) && isset($pengiriman->nomor_resi) && $pengiriman->nomor_resi)
                    <button type="button" class="btn btn-primary" onclick="updateTracking('{{ $pengiriman->id_pengiriman }}')">
                        <i class="fas fa-sync-alt me-1"></i>Mulai Tracking
                    </button>
                @endif
            </div>
        @endif
    </div>

                                </div>
                            </div>

                            <!-- Info Transaksi -->
                            <div class="col-lg-4">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Info Transaksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td class="fw-bold">Pelanggan:</td>
                                                <td>{{ $pengiriman->transaksi->pelanggan->nama_pelanggan ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Email:</td>
                                                <td>{{ $pengiriman->transaksi->pelanggan->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">No. HP:</td>
                                                <td>{{ $pengiriman->transaksi->pelanggan->no_hp ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Status Transaksi:</td>
                                                <td>
                                                    @php
    $statusTrans = match ($pengiriman->transaksi->status ?? '') {
        'pending' => 'bg-warning',
        'dibayar' => 'bg-success',
        'diproses' => 'bg-info',
        'dikirim' => 'bg-primary',
        'selesai' => 'bg-success',
        'dibatalkan' => 'bg-danger',
        default => 'bg-secondary'
    };
                                                    @endphp
                                                    <span class="badge {{ $statusTrans }}">
                                                        {{ ucfirst($pengiriman->transaksi->status ?? 'Unknown') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Alamat Pengiriman -->
                                @if($pengiriman->transaksi->alamat)
                                    <div class="card shadow-sm mt-3">
                                        <div class="card-header bg-warning text-dark">
                                            <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Alamat Pengiriman</h5>
                                        </div>
                                        <div class="card-body">
                                            <address class="mb-0">
                                                <strong>{{ $pengiriman->transaksi->alamat->nama_penerima }}</strong><br>
                                                {{ $pengiriman->transaksi->alamat->alamat_lengkap }}<br>
                                                {{ $pengiriman->transaksi->alamat->kecamatan }}, {{ $pengiriman->transaksi->alamat->kota }}<br>
                                                {{ $pengiriman->transaksi->alamat->provinsi }}
                                                {{ $pengiriman->transaksi->alamat->kode_pos }}<br>
                                                <strong>HP:</strong> {{ $pengiriman->transaksi->alamat->no_hp_penerima }}
                                            </address>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Loading Modal -->
                    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-center py-4">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mb-0">Mengupdate tracking...</p>
                                </div>
                            </div>
                        </div>
                    </div>
@endsection

@push('scripts')
    <script>
       document.addEventListener('DOMContentLoaded', function () {
            // Animasi untuk tracking items
            const trackingItems = document.querySelectorAll('.tracking-item');

            trackingItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(30px)';

                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });

        function updateTracking(idPengiriman) {
            if (confirm('Update tracking untuk pengiriman ini?')) {
                $('#loadingModal').modal('show');

                fetch(`{{ url('api/admin/pengiriman') }}/${idPengiriman}/update-tracking`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        $('#loadingModal').modal('hide');
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Gagal update tracking: ' + data.message);
                        }
                    })
                    .catch(error => {
                        $('#loadingModal').modal('hide');
                        alert('Terjadi kesalahan: ' + error.message);
                    });
            }
        }
    </script>
@endpush


@push('styles')
    <style>
        .tracking-timeline {
            position: relative;
            padding-left: 30px;
        }

        .tracking-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #4CAF50, #2196F3, #FF9800);
        }

        .tracking-item {
            position: relative;
            margin-bottom: 30px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .tracking-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .tracking-item.completed {
            border-left-color: #4CAF50;
        }

        .tracking-item.in-progress {
            border-left-color: #2196F3;
        }

        .tracking-item.pending {
            border-left-color: #FF9800;
        }

        .tracking-icon {
            position: absolute;
            left: -37px;
            top: 20px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            z-index: 2;
        }

        .tracking-icon.completed {
            background: #4CAF50;
        }

        .tracking-icon.in-progress {
            background: #2196F3;
        }

        .tracking-icon.pending {
            background: #FF9800;
        }

        .tracking-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 15px;
        }

        .tracking-info {
            flex: 1;
            min-width: 250px;
        }

        .tracking-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .tracking-location {
            display: flex;
            align-items: center;
            color: #666;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        .tracking-location i {
            margin-right: 8px;
            color: #999;
        }

        .tracking-datetime {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
            min-width: 140px;
        }

        .tracking-date {
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .tracking-time {
            color: #666;
            font-size: 0.9rem;
        }

        .tracking-code {
            display: inline-block;
            background: #f8f9fa;
            color: #666;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 5px;
        }

        .no-tracking {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-tracking i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .tracking-timeline {
                padding-left: 25px;
            }

            .tracking-icon {
                left: -32px;
            }

            .tracking-content {
                flex-direction: column;
                align-items: stretch;
            }

            .tracking-datetime {
                align-items: flex-start;
                text-align: left;
                margin-top: 10px;
            }
        }
    </style>
@endpush