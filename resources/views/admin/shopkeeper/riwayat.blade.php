@extends('admin.layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h3 mb-5 mt-5">Riwayat Transaksi</h2>
                        <button type="button" class="btn btn-success" onclick="exportPdf()">
                            <i class="fas fa-file-pdf me-2"></i>Ekspor PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="statusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $status == 'pesanan_baru' ? 'active' : '' }}"
                                href="{{ route('kasir.riwayat', ['status' => 'pesanan_baru']) }}">
                                <i class="fas fa-clock me-2"></i>Pesanan Baru
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $status == 'dalam_proses' ? 'active' : '' }}"
                                href="{{ route('kasir.riwayat', ['status' => 'dalam_proses']) }}">
                                <i class="fas fa-cog me-2"></i>Dalam Proses
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $status == 'dikirim' ? 'active' : '' }}"
                                href="{{ route('kasir.riwayat', ['status' => 'dikirim']) }}">
                                <i class="fas fa-truck me-2"></i>Dikirim
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $status == 'selesai' ? 'active' : '' }}"
                                href="{{ route('kasir.riwayat', ['status' => 'selesai']) }}">
                                <i class="fas fa-check-circle me-2"></i>Selesai
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $status == 'dibatalkan' ? 'active' : '' }}"
                                href="{{ route('kasir.riwayat', ['status' => 'dibatalkan']) }}">
                                <i class="fas fa-times-circle me-2"></i>Dibatalkan
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Table Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">Nomor Transaksi</th>
                                            <th width="25%">Produk</th>
                                            <th width="12%">Tanggal</th>
                                            <th width="15%">Pelanggan</th>
                                            <th width="15%">Keterangan</th>
                                            <th width="10%">Total</th>
                                            <th width="8%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transaksi as $index => $item)
                                            <tr>
                                                <td>{{ ($page - 1) * 10 + $index + 1 }}</td>
                                                <td>
                                                    <span class="fw-bold">{{ $item->kode_transaksi }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                                        {{ $item->produk ?? 'Tidak ada produk' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d/m/Y H:i') }}
                                                </td>
                                                <td>{{ $item->nama_pelanggan ?? 'Tidak diketahui' }}</td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                                        {{ $item->keterangan ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">
                                                        Rp {{ number_format($item->total, 0, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td>

                                                    <a href="{{ route('admin-admin-transaksi.show', $item->kode_transaksi) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                                        <p>Tidak ada transaksi untuk status ini</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if ($totalPages > 1)
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="text-muted">
                                        Menampilkan {{ count($transaksi) }} dari {{ $total }} data
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            @if ($page > 1)
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ route('kasir.riwayat', ['status' => $status, 'page' => $page - 1]) }}">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            @endif

                                            @for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++)
                                                <li class="page-item {{ $i == $page ? 'active' : '' }}">
                                                    <a class="page-link"
                                                        href="{{ route('kasir.riwayat', ['status' => $status, 'page' => $i]) }}">
                                                        {{ $i }}
                                                    </a>
                                                </li>
                                            @endfor

                                            @if ($page < $totalPages)
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ route('kasir.riwayat', ['status' => $status, 'page' => $page + 1]) }}">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        function exportPdf() {
            const url = new URL("{{ route('kasir.riwayat.export') }}", window.location.origin);
            const status = "{{ $status }}";
            url.searchParams.append('status', status);
            window.open(url.toString(), '_blank');
        }
    </script>
@endpush
