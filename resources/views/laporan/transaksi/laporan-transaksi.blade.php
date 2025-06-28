@extends('admin.layouts.app')

@section('title', 'Laporan Barang Terjual')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Laporan Transaksi</h3>
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
                    <a href="{{ route('laporan.barang-terjual') }}">Barang Terjual</a>
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
                        <form method="GET" action="{{ route('laporan.transaksi') }}">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tanggal Mulai</label>
                                        <input type="date" class="form-control" name="tanggal_mulai" 
                                               value="{{ request('tanggal_mulai') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tanggal Selesai</label>
                                        <input type="date" class="form-control" name="tanggal_selesai" 
                                               value="{{ request('tanggal_selesai') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
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
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Jenis Transaksi</label>
                                        <select class="form-control" name="jenis">
                                            <option value="">Semua Jenis</option>
                                            <option value="website" {{ request('jenis') == 'website' ? 'selected' : '' }}>Website</option>
                                            <option value="offline" {{ request('jenis') == 'offline' ? 'selected' : '' }}>Offline</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Filter
                                            </button>
                                            <a href="{{ route('laporan.transaksi') }}" class="btn btn-secondary">
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
                        <h4 class="card-title mb-0">Data Transaksi Penjualan</h4>
                        
                        <!-- Export Buttons -->
                        <div class="d-flex gap-2">
                            <form method="GET" action="{{ route('laporan.barang-terjual.export-pdf') }}" style="display: inline;">
                                <input type="hidden" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                                <input type="hidden" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
                                <input type="hidden" name="brand" value="{{ request('brand') }}">
                                <input type="hidden" name="jenis" value="{{ request('jenis') }}">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </button>
                            </form>
                            {{-- <form method="GET" action="{{ route('laporan.barang-terjual.export-excel') }}" style="display: inline;">
                                <input type="hidden" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                                <input type="hidden" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
                                <input type="hidden" name="brand" value="{{ request('brand') }}">
                                <input type="hidden" name="jenis" value="{{ request('jenis') }}">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button> --}}
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4>{{ number_format($totalKuantitas) }}</h4>
                                                <p class="mb-0">Total Barang Terjual</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-shopping-cart fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4>Rp {{ number_format($totalNilai, 0, ',', '.') }}</h4>
                                                <p class="mb-0">Total Nilai Penjualan</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-money-bill-wave fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4>{{ $barangTerjual->count() }}</h4>
                                                <p class="mb-0">Total Transaksi</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-receipt fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table  class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Pelanggan</th>
                                        <th>Nama Barang</th>
                                        <th>Brand</th>
                                        <th>Tipe</th>
                                        <th>Warna</th>
                                        <th>Ukuran</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Total</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($barangTerjualGrouped as $kodeTransaksi => $items)
                                        @foreach ($items as $index => $item)
                                            <tr>
                                                @if ($index === 0)
                                                    <td rowspan="{{ $items->count() }}">{{ $loop->parent->iteration }}</td>
                                                    <td rowspan="{{ $items->count() }}">{{ $item->kode_transaksi }}</td>
                                                    <td rowspan="{{ $items->count() }}">{{ date('d/m/Y H:i', strtotime($item->tanggal_transaksi)) }}</td>
                                                    <td rowspan="{{ $items->count() }}">{{ $item->nama_pelanggan ?? 'Customer' }}</td>
                                                @endif
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->nama_brand }}</td>
                                                <td>{{ $item->nama_tipe }}</td>
                                                <td>{{ $item->warna }}</td>
                                                <td>{{ $item->ukuran }}</td>
                                                <td>{{ number_format($item->kuantitas) }}</td>
                                                <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge {{ $item->jenis == 'website' ? 'bg-primary' : 'bg-secondary' }}">
                                                        {{ ucfirst($item->jenis) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($item->status == 'selesai')
                                                        <span class="badge bg-success">Selesai</span>
                                                    @elseif($item->status == 'dikirim')
                                                        <span class="badge bg-info">Dikirim</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="14" class="text-center">Tidak ada data barang terjual</td>
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

