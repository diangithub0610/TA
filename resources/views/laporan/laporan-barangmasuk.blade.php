@extends('admin.layouts.app')

@section('title', 'Laporan Barang Masuk')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Laporan Barang Masuk</h3>
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
                    <a href="#">Laporan</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Barang Masuk</a>
                </li>
            </ul>
        </div>

        <!-- Filter Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Filter Laporan</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('laporan.barang-masuk') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal Mulai</label>
                                        <input type="date" class="form-control" name="tanggal_mulai" 
                                               value="{{ request('tanggal_mulai') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal Selesai</label>
                                        <input type="date" class="form-control" name="tanggal_selesai" 
                                               value="{{ request('tanggal_selesai') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Brand</label>
                                        <select class="form-control" name="brand">
                                            <option value="">Semua Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->kode_brand }}" 
                                                    {{ request('brand') == $brand->kode_brand ? 'selected' : '' }}>
                                                    {{ $brand->nama_brand }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Filter
                                            </button>
                                            <a href="{{ route('laporan.barang-masuk') }}" class="btn btn-secondary">
                                                <i class="fas fa-refresh"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Data Barang Masuk</h4>
                        
                        <!-- Export Buttons -->
                        <div class="d-flex gap-2">
                            <form method="GET" action="{{ route('laporan.barang-masuk.export-pdf') }}" style="display: inline;">
                                <input type="hidden" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                                <input type="hidden" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
                                <input type="hidden" name="brand" value="{{ request('brand') }}">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </button>
                            </form>
                            <form method="GET" action="{{ route('laporan.barang-masuk.export-excel') }}" style="display: inline;">
                                <input type="hidden" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                                <input type="hidden" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
                                <input type="hidden" name="brand" value="{{ request('brand') }}">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4>{{ number_format($totalJumlah) }}</h4>
                                                <p class="mb-0">Total Barang Masuk</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-money-bill-wave fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Pembelian</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Admin</th>
                                        <th>Nama Barang</th>
                                        <th>Brand</th>
                                        <th>Tipe</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Total Harga</th>
                                        <th>Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($barangMasuk as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->kode_pembelian }}</td>
                                            <td>{{ date('d/m/Y', strtotime($item->tanggal_masuk)) }}</td>
                                            <td>{{ $item->admin }}</td>
                                            <td>{{ $item->nama_barang }}</td>
                                            <td>{{ $item->nama_brand }}</td>
                                            <td>{{ $item->nama_tipe }}</td>
                                            <td>{{ number_format($item->jumlah) }}</td>
                                            <td>Rp {{ number_format($item->harga_barang_masuk, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                            <td>
                                                @if($item->bukti_pembelian)
                                                    <a href="{{ asset('storage/bukti/' . $item->bukti_pembelian) }}" 
                                                       target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Lihat
                                                    </a>
                                                @else
                                                    <span class="badge bg-secondary">Tidak Ada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">Tidak ada data barang masuk</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#basic-datatables').DataTable({
            "pageLength": 25,
            "responsive": true,
            "order": [[2, "desc"]], // Order by tanggal masuk descending
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });
    });
</script>
@endpush
                                               