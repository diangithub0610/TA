@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <button class="btn btn-link text-dark p-0 me-3" onclick="history.back()">
            <i class="fas fa-arrow-left fa-lg"></i>
        </button>
        <h4 class="mb-0 fw-bold">Nomor Pesanan #{{ $transaksi->kode_transaksi }}</h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <!-- Customer & Shipping Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Informasi Pelanggan</h6>
                    <div class="mb-2">
                        <strong>Nama:</strong> {{ $transaksi->pelanggan->nama_pelanggan ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Nomor WhatsApp:</strong> {{ $transaksi->pelanggan->no_hp ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong> {{ $transaksi->pelanggan->email ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Informasi Pengiriman</h6>
                    <div class="mb-2">
                        <strong>Alamat:</strong> {{ $transaksi->pelanggan->alamat_pengguna ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Kurir:</strong> {{ strtoupper($transaksi->ekspedisi ?? 'N/A') }}
                    </div>
                    <div class="mb-2">
                        <strong>Service:</strong> {{ strtoupper($transaksi->layanan_ekspedisi ?? 'N/A') }}
                    </div>
                    <div class="mb-2">
                        <strong>Estimasi:</strong> {{ $transaksi->estimasi_waktu ?? 'N/A' }}
                    </div>
                    @if($transaksi->resi)
                    <div class="mb-2">
                        <strong>No. Resi:</strong> {{ $transaksi->pengiriman->resi }}
                    </div>
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <!-- Order Items -->
            <div class="mb-4">
                @foreach($transaksi->detailTransaksi as $detail)
                <div class="row align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-1">{{ $detail->detailBarang->barang->nama_barang ?? 'Produk tidak ditemukan' }}</h6>
                        <p class="text-muted mb-0 small">
                            Ukuran {{ $detail->detailBarang->ukuran ?? 'N/A' }}
                            @if($detail->detailBarang->warna)
                                - {{ $detail->detailBarang->warna->warna }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="fw-bold">Rp{{ number_format($detail->harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="col-md-3 text-end">
                        <span>{{ $detail->kuantitas }} X Rp{{ number_format($detail->harga, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <hr class="my-4">

            <!-- Order Summary -->
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span>Rp{{ number_format($transaksi->detailTransaksi->sum(function($detail) { return $detail->kuantitas * $detail->harga; }), 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Ongkir</span>
                        <span>Rp{{ number_format($transaksi->ongkir, 0, ',', '.') }}</span>
                    </div>
                    @if($transaksi->diskon > 0)
                    <div class="mb-2 d-flex justify-content-between text-success">
                        <span>Diskon</span>
                        <span>-Rp{{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span>Rp{{ number_format($transaksi->detailTransaksi->sum(function($detail) { return $detail->kuantitas * $detail->harga; }) + $transaksi->ongkir - ($transaksi->diskon ?? 0), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Order Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>Tanggal Pesanan:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d M Y H:i') }}
                    </div>
                    <div class="mb-2">
                        <strong>Metode Pembayaran:</strong> {{ ucfirst($transaksi->metode_pembayaran ?? 'Transfer Bank') }}
                    </div>
                    @if($transaksi->catatan)
                    <div class="mb-2">
                        <strong>Catatan:</strong> {{ $transaksi->catatan }}
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>Status:</strong> 
                        @php
                            $statusClass = [
                                'belum_dibayar' => 'warning',
                                'menunggu_konfirmasi' => 'info',
                                'diproses' => 'primary',
                                'dikirim' => 'success',
                                'selesai' => 'success',
                                'dibatalkan' => 'danger',
                            ];
                            $statusLabel = [
                                'belum_dibayar' => 'Belum Dibayar',
                                'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                'diproses' => 'Diproses',
                                'dikirim' => 'Dikirim',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusClass[$transaksi->status] ?? 'secondary' }} px-3 py-2">
                            {{ $statusLabel[$transaksi->status] ?? $transaksi->status }}
                        </span>
                    </div>
                    @if($transaksi->is_dropship)
                    <div class="mb-2">
                        <span class="badge bg-info px-3 py-2">Dropship</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2">
                @if($transaksi->status == 'menunggu_konfirmasi')
                    <button type="button" class="btn btn-dark"
                            onclick="terimaTransaksi('{{ $transaksi->kode_transaksi }}')">
                        Konfirmasi Pesanan
                    </button>
                    <button type="button" class="btn btn-danger"
                            onclick="tolakTransaksi('{{ $transaksi->kode_transaksi }}')">
                        Tolak Pesanan
                    </button>
                @elseif($transaksi->status == 'diproses')
                    <button type="button" class="btn btn-dark"
                            onclick="updateStatus('{{ $transaksi->kode_transaksi }}', 'dikirim')">
                        Kirim Pesanan
                    </button>
                @elseif($transaksi->status == 'dikirim')
                    <button type="button" class="btn btn-dark"
                            onclick="updateStatus('{{ $transaksi->kode_transaksi }}', 'selesai')">
                        Selesaikan Pesanan
                    </button>
                @endif
                
                <button type="button" class="btn btn-outline-secondary"
                        onclick="cetakInvoice('{{ $transaksi->kode_transaksi }}')">
                    Cetak Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Resi -->
<div class="modal fade" id="resiModal" tabindex="-1" aria-labelledby="resiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resiModalLabel">Update Nomor Resi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resiForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="no_resi" class="form-label">Nomor Resi</label>
                        <input type="text" class="form-control" id="no_resi" name="no_resi" required
                               placeholder="Masukkan nomor resi pengiriman...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Resi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function terimaTransaksi(kodeTransaksi) {
    if(confirm('Apakah Anda yakin ingin menerima pesanan ini?')) {
        // Your existing accept transaction function
        // Example AJAX call:
        // $.post('/admin/transaksi/terima/' + kodeTransaksi, {_token: '{{ csrf_token() }}'})
    }
}

function tolakTransaksi(kodeTransaksi) {
    // Show reject modal or your existing reject function
}

function updateStatus(kodeTransaksi, status) {
    if(status === 'dikirim') {
        // Show resi modal first
        $('#resiModal').modal('show');
        $('#resiForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            let noResi = $('#no_resi').val();
            // Update status with resi number
            // Your AJAX call here
        });
    } else {
        if(confirm('Apakah Anda yakin ingin mengubah status pesanan?')) {
            // Your existing update status function
        }
    }
}

function cetakInvoice(kodeTransaksi) {
    window.open('/admin/transaksi/invoice/' + kodeTransaksi, '_blank');
}

</script>

<style>
.btn {
    border-radius: 6px;
    font-weight: 500;
}

.card {
    border-radius: 8px;
}

.badge {
    font-weight: 500;
    border-radius: 6px;
}
</style>
@endsection
