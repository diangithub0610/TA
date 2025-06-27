@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

    @push('styles')
        <style>
            body {

                min-height: 100vh;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .dashboard-container {
                padding: 30px;
            }

            .stats-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 20px;
                padding: 25px;
                margin-bottom: 25px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .stats-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 20px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .card-header {

                color: white;
                border-radius: 20px 20px 0 0 !important;
                border: none;
                padding: 20px 25px;
            }

            .card-title {
                font-size: 1.2rem;
                font-weight: 600;
                margin: 0;
            }

            .card-body {
                padding: 25px;
            }

            .chart-container {
                position: relative;
                height: 350px;
                margin: 10px 0;
            }

            .stats-number {
                font-size: 2.5rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 5px;
            }

            .stats-label {
                font-size: 1rem;
                color: #64748b;
                margin-bottom: 10px;
            }

            .stats-trend {
                font-size: 0.9rem;
                font-weight: 500;
            }

            .trend-up {
                color: #10b981;
            }

            .trend-down {
                color: #ef4444;
            }

            .period-selector {
                margin-bottom: 20px;
            }

            .btn-period {
                background: rgba(255, 255, 255, 0.9);
                border: 2px solid rgba(59, 130, 246, 0.3);
                color: #3b82f6;
                border-radius: 25px;
                padding: 8px 20px;
                margin: 0 5px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-period:hover,
            .btn-period.active {
                background: #3b82f6;
                color: white;
                border-color: #3b82f6;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
            }

            .dashboard-title {
                color: black;
                text-align: center;
                margin-bottom: 30px;
                font-size: 2.5rem;
                font-weight: 700;
            }

            .icon-stats {
                font-size: 3rem;
                margin-bottom: 15px;
                opacity: 0.8;
            }

            .icon-customers {
                color: #3b82f6;
            }

            .icon-resellers {
                color: #10b981;
            }

            .icon-growth {
                color: #f59e0b;
            }

            .icon-total {
                color: #8b5cf6;
            }

            .loading-spinner {
                display: none;
                text-align: center;
                padding: 50px;
            }

            .spinner-border {
                color: #3b82f6;
            }
        </style>
    @endpush
    <div class="container-fluid">
        <!-- Header -->
        <div class="page-header">
            <h3 class="fw-bold mb-3">Dashboard</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="#"><i class="icon-home"></i></a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Dashboard</a></li>
            </ul>
        </div>

        <!-- Filter Periode -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center">
                            <label class="me-2">Filter Periode:</label>
                            <select name="period" class="form-select me-2" style="width: auto;">
                                <option value="7" {{ $period == 7 ? 'selected' : '' }}>7 Hari Terakhir</option>
                                <option value="30" {{ $period == 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                                <option value="90" {{ $period == 90 ? 'selected' : '' }}>3 Bulan Terakhir</option>
                                <option value="365" {{ $period == 365 ? 'selected' : '' }}>1 Tahun Terakhir</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category text-black">Total Pelanggan</p>
                                    <h4 class="card-title">{{ number_format($totalPelanggan) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-info bubble-shadow-small">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category text-black">Total Reseller</p>
                                    <h4 class="card-title">{{ number_format($totalReseller) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-success bubble-shadow-small">
                                    <i class="fas fa-luggage-cart"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category text-black">Total Penjualan</p>
                                    <h4 class="card-title">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                    <i class="far fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category text-black">Total Order</p>
                                    <h4 class="card-title">{{ number_format($totalOrder) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <!-- Daily Sales Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Penjualan Harian (7 Hari Terakhir)</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="min-height: 300px;">
                            <canvas id="dailySalesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Status -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Status Transaksi</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="min-height: 300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products and Loyal Resellers -->
        <div class="row">
            <!-- Top Selling Products -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Top 10 Barang Terlaris</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Produk</th>
                                        <th>Terjual</th>
                                        <th>Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topBarang as $index => $barang)
                                        <tr>
                                            <td>
                                                <span class="badge badge-primary">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $barang->nama_barang }}</strong><br>
                                                <small class="text-muted">
                                                    {{ $barang->nama_brand }} - {{ $barang->nama_tipe }}<br>
                                                    {{ $barang->warna }} | {{ $barang->ukuran }}
                                                </small>
                                            </td>
                                            <td>{{ number_format($barang->total_terjual) }}</td>
                                            <td>Rp {{ number_format($barang->total_pendapatan, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loyal Resellers -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Top 10 Reseller</div>
                        <div class="card-category">
                            <ul class="nav nav-pills nav-secondary" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="pill" href="#transaction-tab"
                                        role="tab">
                                        By Transaksi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="pill" href="#spend-tab" role="tab">
                                        By Spending
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- By Transaction -->
                            <div class="tab-pane fade show active" id="transaction-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Reseller</th>
                                                <th>No telp</th>
                                                <th>Transaksi</th>
                                                <th>Total Spend</th>
                                                <th>Star</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($loyalResellerByTransaction as $index => $reseller)
                                                <tr>
                                                    <td>
                                                        {{-- @if ($index < 5)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="fas fa-star text-secondary"></i>
                                                @endif --}}
                                                        {{ $index + 1 }}
                                                    </td>
                                                    <td>
                                                        <strong>{{ $reseller->nama_pelanggan }}</strong><br>
                                                        <small class="text-muted">{{ $reseller->email }}</small>
                                                    </td>
                                                    <td>{{ $reseller->no_hp }}</td>
                                                    <td>{{ number_format($reseller->total_transaksi) }}</td>
                                                    <td>Rp {{ number_format($reseller->total_spend, 0, ',', '.') }}</td>
                                                    <td>
                                                        @if ($index == 0)
                                                            {{-- Ranking 1: 5 bintang gold --}}
                                                            @for ($i = 0; $i < 5; $i++)
                                                                <i class="fas fa-star text-warning"></i>
                                                            @endfor
                                                        @elseif($index >= 1 && $index <= 4)
                                                            {{-- Ranking 2-5: Bintang gold menurun dari 4 ke 1 --}}
                                                            @for ($i = 0; $i < 5 - $index; $i++)
                                                                <i class="fas fa-star text-warning"></i>
                                                            @endfor
                                                            @for ($i = 0; $i < $index; $i++)
                                                                <i class="far fa-star text-muted"></i>
                                                            @endfor
                                                        @elseif($index >= 5 && $index <= 9)
                                                            {{-- Ranking 6-10: 1 bintang silver --}}
                                                            <i class="fas fa-star text-secondary"></i>
                                                            @for ($i = 0; $i < 4; $i++)
                                                                <i class="far fa-star text-muted"></i>
                                                            @endfor
                                                        @else
                                                            {{-- Ranking 11+: Tidak ada bintang atau bintang kosong --}}
                                                            @for ($i = 0; $i < 5; $i++)
                                                                <i class="far fa-star text-muted"></i>
                                                            @endfor
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- By Spend -->
                            <div class="tab-pane fade" id="spend-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Reseller</th>
                                                <th>No Whatsapp</th>
                                                <th>Transaksi</th>
                                                <th>Total Spend</th>
                                                <th>Rank</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($loyalResellerBySpend as $index => $reseller)
                                                <tr>
                                                    <td>
                                                        {{-- @if ($index < 5)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="fas fa-star text-secondary"></i>
                                                @endif --}}
                                                        {{ $index + 1 }}
                                                    </td>
                                                    <td>
                                                        <strong>{{ $reseller->nama_pelanggan }}</strong><br>
                                                        <small class="text-muted">{{ $reseller->email }}</small>
                                                    </td>
                                                    <td>{{ $reseller->no_hp }}</td>
                                                    <td>{{ number_format($reseller->total_transaksi) }}</td>
                                                    <td>Rp {{ number_format($reseller->total_spend, 0, ',', '.') }}</td>
                                                    <td>
                                                        @if ($index == 0)
                                                            {{-- Ranking 1: 5 bintang gold --}}
                                                            @for ($i = 0; $i < 5; $i++)
                                                                <i class="fas fa-star text-warning"></i>
                                                            @endfor
                                                        @elseif($index >= 1 && $index <= 4)
                                                            {{-- Ranking 2-5: Bintang gold menurun dari 4 ke 1 --}}
                                                            @for ($i = 0; $i < 5 - $index; $i++)
                                                                <i class="fas fa-star text-warning"></i>
                                                            @endfor
                                                            @for ($i = 0; $i < $index; $i++)
                                                                <i class="far fa-star text-muted"></i>
                                                            @endfor
                                                        @elseif($index >= 5 && $index <= 9)
                                                            {{-- Ranking 6-10: 1 bintang silver --}}
                                                            <i class="fas fa-star text-secondary"></i>
                                                            @for ($i = 0; $i < 4; $i++)
                                                                <i class="far fa-star text-muted"></i>
                                                            @endfor
                                                        @else
                                                            {{-- Ranking 11+: Tidak ada bintang atau bintang kosong --}}
                                                            @for ($i = 0; $i < 5; $i++)
                                                                <i class="far fa-star text-muted"></i>
                                                            @endfor
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Rated Products -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Top 10 Rating Barang</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Produk</th>
                                    <th>Rating</th>
                                    <th>Jumlah Ulasan</th>
                                    <th>Rating Tertinggi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topRatedBarang as $index => $barang)
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $barang->nama_barang }}</strong><br>
                                            <small class="text-muted">
                                                Kode: {{ $barang->kode_barang }}<br>
                                                {{-- @if ($barang->deskripsi)
                                                    {{ Str::limit($barang->deskripsi, 50) }}
                                                @endif --}}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-warning mr-2">
                                                    {{ number_format($barang->avg_rating, 1) }}
                                                </span>
                                                <div class="rating-stars">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= floor($barang->avg_rating))
                                                            <i class="fas fa-star text-warning"></i>
                                                        @elseif($i - 0.5 <= $barang->avg_rating)
                                                            <i class="fas fa-star-half-alt text-warning"></i>
                                                        @else
                                                            <i class="far fa-star text-muted"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ number_format($barang->total_ulasan) }} ulasan
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ $barang->max_rating }}/5
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Stok Menipis (< 10) </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Warna</th>
                                        <th>Ukuran</th>
                                        <th>Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stokMenupis as $stok)
                                        <tr>
                                            <td>{{ $stok->nama_barang }}</td>
                                            <td>{{ $stok->warna }}</td>
                                            <td>{{ $stok->ukuran }}</td>
                                            <td>
                                                <span class="badge badge-{{ $stok->stok <= 5 ? 'danger' : 'warning' }}">
                                                    {{ $stok->stok }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-container">
        <h1 class="dashboard-title">
            <i class="fas fa-chart-line"></i>
            Dashboard Pertumbuhan Pelanggan & Reseller
        </h1>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users icon-stats icon-customers"></i>
                    <div class="stats-number">{{ $totalPelanggan }}</div>
                    <div class="stats-label">Total Pelanggan</div>
                    <div class="stats-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        +{{ $totalPelangganBulanIni }} bulan ini
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-store icon-stats icon-resellers"></i>
                    <div class="stats-number">{{ $totalReseller }}</div>
                    <div class="stats-label">Total Reseller</div>
                    <div class="stats-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        +{{ $totalResellerBulanIni }} bulan ini
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-chart-line icon-stats icon-growth"></i>
                    <div class="stats-number">{{ $totalPelanggan + $totalReseller }}</div>
                    <div class="stats-label">Total Pengguna</div>
                    <div class="stats-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        +{{ $totalPelangganBulanIni + $totalResellerBulanIni }} bulan ini
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-percentage icon-stats icon-total"></i>
                    <div class="stats-number">
                        {{ $totalPelanggan > 0 ? round(($totalReseller / ($totalPelanggan + $totalReseller)) * 100, 1) : 0 }}%
                    </div>
                    <div class="stats-label">Rasio Reseller</div>
                    <div class="stats-trend">
                        <i class="fas fa-info-circle"></i>
                        Dari total pengguna
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Daily Growth Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-area"></i>
                            Pertumbuhan Harian Pengguna
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="period-selector">
                            <button class="btn btn-period active" onclick="changePeriod(7, this)">7 Hari</button>
                            <button class="btn btn-period" onclick="changePeriod(14, this)">14 Hari</button>
                            <button class="btn btn-period" onclick="changePeriod(30, this)">30 Hari</button>
                        </div>
                        <div class="chart-container">
                            <canvas id="dailyGrowthChart"></canvas>
                            <div class="loading-spinner" id="dailyLoading">
                                <div class="spinner-border" role="status"></div>
                                <p class="mt-2">Memuat data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Distribution -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Distribusi Pengguna
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container position-relative" style="height: 300px;">
                            <canvas id="distributionChart" style="width:100%; height:100%; display:none;"></canvas>
                            <div class="loading-spinner d-flex flex-column justify-content-center align-items-center position-absolute top-0 start-0 w-100 h-100 bg-white"
                                id="distributionLoading">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Memuat data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Growth Chart -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Pertumbuhan Bulanan (6 Bulan Terakhir)
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyGrowthChart"></canvas>
                            <div class="loading-spinner" id="monthlyLoading">
                                <div class="spinner-border" role="status"></div>
                                <p class="mt-2">Memuat data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daily Sales Chart
        const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailySalesChart = new Chart(dailySalesCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach ($dailySales as $sale)
                        '{{ date('d/m', strtotime($sale->tanggal)) }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: [
                        @foreach ($dailySales as $sale)
                            {{ $sale->total }},
                        @endforeach
                    ],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Jumlah Order',
                    data: [
                        @foreach ($dailySales as $sale)
                            {{ $sale->jumlah_order }},
                        @endforeach
                    ],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    yAxisID: 'y1',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach ($statusTransaksi as $status)
                        '{{ ucfirst(str_replace('_', ' ', $status->status)) }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach ($statusTransaksi as $status)
                            {{ $status->jumlah }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        let dailyChart, monthlyChart, distributionChart;

        document.addEventListener('DOMContentLoaded', function() {
            loadDailyGrowthChart(7);
            loadMonthlyGrowthChart();
            loadDistributionChart(); // ⬅️ Tambahan distribusi per role
        });

        function changePeriod(period, button) {
            document.querySelectorAll('.btn-period').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            loadDailyGrowthChart(period);
        }

        function loadDailyGrowthChart(period) {
            const loading = document.getElementById('dailyLoading');
            const canvas = document.getElementById('dailyGrowthChart');

            loading.style.display = 'block';
            canvas.style.display = 'none';

            if (dailyChart) dailyChart.destroy();

            fetch(`/dashboard/customer-growth?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    const ctx = canvas.getContext('2d');
                    dailyChart = new Chart(ctx, {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        font: { size: 14, weight: 'bold' }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    borderColor: 'rgba(255, 255, 255, 0.2)',
                                    borderWidth: 1,
                                    cornerRadius: 10,
                                    displayColors: true
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: 'rgba(0, 0, 0, 0.1)', borderDash: [5, 5] },
                                    ticks: { font: { size: 12, weight: 'bold' } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(0, 0, 0, 0.1)', borderDash: [5, 5] },
                                    ticks: { font: { size: 12, weight: 'bold' } }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    });

                    loading.style.display = 'none';
                    canvas.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading daily growth chart:', error);
                    loading.style.display = 'none';
                    canvas.style.display = 'block';
                });
        }

        function loadMonthlyGrowthChart() {
            const loading = document.getElementById('monthlyLoading');
            const canvas = document.getElementById('monthlyGrowthChart');

            loading.style.display = 'block';
            canvas.style.display = 'none';

            fetch('/dashboard/reseller-growth')
                .then(response => response.json())
                .then(data => {
                    const ctx = canvas.getContext('2d');
                    monthlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        font: { size: 14, weight: 'bold' }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    borderColor: 'rgba(255, 255, 255, 0.2)',
                                    borderWidth: 1,
                                    cornerRadius: 10
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 12, weight: 'bold' } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(0, 0, 0, 0.1)', borderDash: [5, 5] },
                                    ticks: { font: { size: 12, weight: 'bold' } }
                                }
                            },
                            animation: {
                                duration: 1200,
                                easing: 'easeInOutQuart'
                            }
                        }
                    });

                    loading.style.display = 'none';
                    canvas.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading monthly growth chart:', error);
                    loading.style.display = 'none';
                    canvas.style.display = 'block';
                });
        }

        function loadDistributionChart() {
            const loading = document.getElementById('distributionLoading');
            const canvas = document.getElementById('distributionChart');

            if (!loading || !canvas) {
                console.error('Element tidak ditemukan!');
                return;
            }

            loading.style.display = 'flex';
            canvas.style.display = 'none';

            fetch('/dashboard/role-distribution')
                .then(response => response.json())
                .then(data => {
                    const ctx = canvas.getContext('2d');

                    if (distributionChart) {
                        distributionChart.destroy();
                    }

                    distributionChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.datasets.data,
                                backgroundColor: data.datasets.backgroundColor,
                                borderColor: data.datasets.borderColor,
                                borderWidth: data.datasets.borderWidth,
                                hoverBorderWidth: data.datasets.hoverBorderWidth,
                                hoverOffset: data.datasets.hoverOffset
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        font: { size: 14, weight: 'bold' },
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    borderColor: 'rgba(255, 255, 255, 0.2)',
                                    borderWidth: 1,
                                    cornerRadius: 10,
                                    callbacks: {
                                        label: function (context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const value = context.parsed;
                                            const percentage = Math.round((value / total) * 100);
                                            return `${context.label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            },
                            cutout: '60%',
                            animation: {
                                duration: 1500,
                                easing: 'easeInOutQuart'
                            }
                        }
                    });

                    loading.style.display = 'none';
                    canvas.style.display = 'block';
                })
                .catch(error => {
                    console.error('Gagal memuat data distribusi pengguna:', error);
                    loading.innerHTML = `<p class="text-danger">Gagal memuat data</p>`;
                });
        }
    </script>
@endpush

@endsection
