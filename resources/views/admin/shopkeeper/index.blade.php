@extends('admin.layouts.app')

@section('title', 'Daftar Pesanan')
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
                    <a href="{{ route('admin-transaksi.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <!-- Tab Navigation -->
        <div class="d-flex gap-2 mb-4">
            <button class="btn {{ !request('status') || request('status') == '' ? 'btn-dark' : 'btn-light' }} btn-md"
                onclick="filterByStatus('')">
                Pesanan Baru
            </button>
            <button class="btn {{ request('status') == 'diproses' ? 'btn-dark' : 'btn-light' }} btn-md"
                onclick="filterByStatus('diproses')">
                Dalam Proses
            </button>
            <button class="btn {{ request('status') == 'dikirim' ? 'btn-dark' : 'btn-light' }} btn-md"
                onclick="filterByStatus('dikirim')">
                Dikirim
            </button>
            <button class="btn {{ request('status') == 'selesai' ? 'btn-primary' : 'btn-primary' }} btn-md"
                onclick="filterByStatus('selesai')">
                Selesai
            </button>
            <button class="btn {{ request('status') == 'dibatalkan' ? 'btn-dark' : 'btn-light' }} btn-md"
                onclick="filterByStatus('dibatalkan')">
                Dibatalkan
            </button>
        </div>

        <!-- Filter Form (Hidden) -->
        <form method="GET" id="filterForm" style="display: none;">
            <input type="hidden" name="status"  id="statusFilter" value="{{ request('status') }}">
        </form>
{{-- {{ dd('asa') }} --}}
        <!-- Orders List -->
        <div class="row g-3">
            @forelse($transaksi as $item)
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- Header Row -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ $item->pelanggan->nama_pelanggan ?? 'N/A' }}</h6>
                                    <small
                                        class="text-muted">{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d M Y') }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $statusBadge = [
                                            'belum_dibayar' => ['class' => 'warning', 'text' => 'Pesanan Baru'],
                                            'menunggu_konfirmasi' => ['class' => 'warning', 'text' => 'Pesanan Baru'],
                                            'diproses' => ['class' => 'primary', 'text' => 'Dalam Proses'],
                                            'dikirim' => ['class' => 'info', 'text' => 'Dikirim'],
                                            'selesai' => ['class' => 'primary', 'text' => 'Selesai'],
                                            'dibatalkan' => ['class' => 'danger', 'text' => 'Dibatalkan'],
                                        ];
                                        $currentStatus = $statusBadge[$item->status] ?? [
                                            'class' => 'primary',
                                            'text' => ucfirst($item->status),
                                        ];
                                    @endphp
                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                        {{ $currentStatus['text'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Product Row -->
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    @if ($item->detailTransaksi->isNotEmpty())
                                        @php 
                                        $firstDetail = $item->detailTransaksi->first();
                                         @endphp
                                        @if ($firstDetail->detailBarang && $firstDetail->detailBarang->barang && $firstDetail->detailBarang->barang->gambar)
                                            <img src="{{ asset('storage/' . $firstDetail->detailBarang->barang->gambar) }}"
                                                alt="Product Image" class="rounded"
                                                style="width: 80px; height: 80px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                style="width: 80px; height: 80px;">
                                                <i class="fas fa-image text-primary"></i>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <div class="col">
                                    @if ($item->detailTransaksi->isNotEmpty())
                                        @php $firstDetail = $item->detailTransaksi->first(); @endphp
                                        <h6 class="mb-1 fw-bold">
                                            {{ $firstDetail->detailBarang->barang->nama_barang ?? 'Product Not Found' }}
                                        </h6>
                                        <p class="text-muted mb-1 small">Ukuran
                                            {{ $firstDetail->detailBarang->ukuran ?? 'N/A' }}</p>
                                        <p class="text-muted mb-0 small">
                                            {{ $firstDetail->kuantitas }}
                                            {{ $item->detailTransaksi->count() > 1 ? 'Barang Lainnya' : 'Barang' }}
                                        </p>
                                    @endif
                                </div>

                                <div class="col-auto text-end">
                                    <h5 class="mb-0 fw-bold">
                                        RP{{ number_format(
                                            $item->detailTransaksi->sum(function ($detail) {
                                                return $detail->kuantitas * $detail->harga;
                                            }) + 0,
                                            0,
                                            ',',
                                            '.',
                                        ) }}
                                    </h5>
                                </div>
                            </div>

                            <!-- Total Pesanan Row -->
                            <div class="row mt-3 pt-3 border-top">
                                <div class="col">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Total Pesanan</span>
                                        <span class="fw-bold fs-5">
                                            RP{{ number_format(
                                                $item->detailTransaksi->sum(function ($detail) {
                                                    return $detail->kuantitas * $detail->harga;
                                                }) + $item->ongkir,
                                                0,
                                                ',',
                                                '.',
                                            ) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-primary btn-sm"
                                    onclick="lihatDetail('{{ $item->kode_transaksi }}')">
                                    <a href="{{ route('admin-admin-transaksi.show', $item->kode_transaksi) }}"
                                        class="btn btn-primary btn-sm">
                                        Detail
                                    </a>

                                </button>

                                @if ($item->status == 'menunggu_konfirmasi')
                                    <button type="button" class="btn btn-success btn-sm"
                                        onclick="terimaTransaksi('{{ $item->kode_transaksi }}')">
                                        Konfirmasi
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h5>Tidak ada pesanan yang ditemukan</h5>
                            <p>Belum ada pesanan untuk status ini</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $transaksi->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Modal Tolak -->
    <div class="modal fade" id="tolakModal" tabindex="-1" aria-labelledby="tolakModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tolakModalLabel">Tolak Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tolakForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="alasan" class="form-label">Alasan Penolakan</label>
                            <textarea class="form-control" id="alasan" name="alasan" rows="3" required
                                placeholder="Masukkan alasan penolakan pesanan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Pesanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .btn {
            border-radius: 6px;
            font-weight: 500;
        }

        .card {
            border-radius: 8px;
            transition: box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentTransaksiId = null;

        function filterByStatus(status) {
            document.getElementById('statusFilter').value = status;
            document.getElementById('filterForm').submit();
        }

        // function lihatDetail(kodeTransaksi) {
        //     // Implementasi untuk melihat detail nanti
        //     alert('Fitur lihat detail akan diimplementasikan nanti untuk pesanan: ' + kodeTransaksi);
        // }

        function terimaTransaksi(kodeTransaksi) {
            if (confirm('Apakah Anda yakin ingin menerima pesanan ini?')) {
                fetch(`/admin/transaksi/${kodeTransaksi}/terima`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memproses permintaan');
                    });
            }
        }

        function tolakTransaksi(kodeTransaksi) {
            currentTransaksiId = kodeTransaksi;
            const modal = new bootstrap.Modal(document.getElementById('tolakModal'));
            modal.show();
        }

        document.getElementById('tolakForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const alasan = document.getElementById('alasan').value;

            fetch(`/admin/transaksi/${currentTransaksiId}/tolak`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        alasan: alasan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses permintaan');
                });
        });

        function updateStatus(kodeTransaksi, status) {
            let confirmMessage = '';
            switch (status) {
                case 'dikirim':
                    confirmMessage = 'Apakah Anda yakin ingin mengirim pesanan ini?';
                    break;
                case 'selesai':
                    confirmMessage = 'Apakah Anda yakin pesanan ini sudah selesai?';
                    break;
                default:
                    confirmMessage = 'Apakah Anda yakin ingin mengubah status pesanan ini?';
            }

            if (confirm(confirmMessage)) {
                fetch(`/admin/transaksi/${kodeTransaksi}/update-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memproses permintaan');
                    });
            }
        }

        function filterByStatus(status) {
            document.getElementById('statusFilter').value = status;
            document.getElementById('filterForm').submit();
        }

        // // Maintain other JavaScript functions from original code
        // function lihatDetail(kodeTransaksi) {
        //     // Your existing detail view function
        // }

        // function terimaTransaksi(kodeTransaksi) {
        //     // Your existing accept transaction function
        // }

        // function tolakTransaksi(kodeTransaksi) {
        //     // Your existing reject transaction function
        // }

        // function updateStatus(kodeTransaksi, status) {
        //     // Your existing update status function
        // }
    </script>
@endpush
