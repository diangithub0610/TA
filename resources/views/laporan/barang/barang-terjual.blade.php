@extends('admin.layouts.app')

@section('title', 'Laporan Barang Terjual')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Filter Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filter Laporan</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.barang-terjual') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <select name="brand" id="brand" class="form-select">
                                        <option value="">Semua Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->kode_brand }}" 
                                                {{ $request->brand == $brand->kode_brand ? 'selected' : '' }}>
                                                {{ $brand->nama_brand }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipe">Tipe</label>
                                    <select name="tipe" id="tipe" class="form-select">
                                        <option value="">Semua Tipe</option>
                                        @foreach($tipes as $tipe)
                                            <option value="{{ $tipe->kode_tipe }}" 
                                                {{ $request->tipe == $tipe->kode_tipe ? 'selected' : '' }}>
                                                {{ $tipe->nama_tipe }} ({{ $tipe->nama_brand }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="warna">Warna</label>
                                    <select name="warna" id="warna" class="form-select">
                                        <option value="">Semua Warna</option>
                                        @foreach($warnas as $warna)
                                            <option value="{{ $warna->kode_warna }}" 
                                                {{ $request->warna == $warna->kode_warna ? 'selected' : '' }}>
                                                {{ $warna->warna }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                                           class="form-control" value="{{ $request->tanggal_mulai }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                                           class="form-control" value="{{ $request->tanggal_selesai }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="d-grid">
                                        <a href="{{ route('laporan.barang-terjual') }}" class="btn btn-secondary">
                                            <i class="fas fa-refresh"></i> Reset Filter
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Report Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Laporan Barang Terjual</h4>
                    
                    <!-- Export Buttons -->
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('laporan.barang.export-pdf') }}"
                            style="display: inline;">
                            <input type="hidden" name="brand" value="{{ $request->brand }}">
                            <input type="hidden" name="tipe" value="{{ $request->tipe }}">
                            <input type="hidden" name="warna" value="{{ $request->warna }}">
                            <input type="hidden" name="tanggal_mulai" value="{{ $request->tanggal_mulai }}">
                            <input type="hidden" name="tanggal_selesai" value="{{ $request->tanggal_selesai }}">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Total Item</h5>
                                    <h3>{{ $laporanBarang->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Total Terjual</h5>
                                    <h3>{{ $laporanBarang->sum('jumlah_terjual') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Stok Tersisa</h5>
                                    <h3>{{ $laporanBarang->sum('stok') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>Total Pendapatan</h5>
                                    <h3>Rp {{ number_format($laporanBarang->sum('total_pendapatan'), 0, ',', '.') }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Detail</th>
                                    <th>Nama Barang</th>
                                    <th>Brand</th>
         
                                    <th>Stok</th>
                                    <th>Harga Normal</th>
                                    <th>Jumlah Terjual</th>
                                    <th>Total Pendapatan</th>
                                    <th>Status Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($laporanBarang as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->kode_detail }}</td>
                                        <td>{{ $item->nama_barang ?? '-' }}, {{ $item->nama_tipe ?? '-' }} | {{ $item->warna ?? '-' }} | {{ $item->ukuran }}</td>
                                        <td>{{ $item->nama_brand ?? '-' }}</td>

                                        <td>{{ $item->stok }}</td>
                                        <td>Rp {{ number_format($item->harga_normal, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $item->jumlah_terjual > 0 ? 'success' : 'secondary' }}">
                                                {{ $item->jumlah_terjual }}
                                            </span>
                                        </td>
                                        <td>Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($item->stok < 10)
                                                <span class="badge bg-danger">Harus Restok</span>
                                            @elseif ($item->stok < 20)
                                                <span class="badge bg-warning">Stok Menipis</span>
                                            @else
                                                <span class="badge bg-success">Aman</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">Tidak ada data</td>
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#basic-datatables').DataTable({
        "pageLength": 25,
        "order": [[ 9, "desc" ]], // Sort by Jumlah Terjual descending
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        }
    });
});
</script>
@endpush
@endsection