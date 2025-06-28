<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang Terjual</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
        
        .filter-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .filter-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 8px;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-item strong {
            color: #495057;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .stat-card {
            flex: 1;
            text-align: center;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        
        .stat-card h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .table-container {
            width: 100%;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        
        th {
            background-color: #343a40;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-left {
            text-align: left !important;
        }
        
        .text-right {
            text-align: right !important;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        
        .badge-success {
            background-color: #28a745;
        }
        
        .badge-info {
            background-color: #17a2b8;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge-primary {
            background-color: #007bff;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            .filter-info {
                margin-bottom: 15px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN BARANG TERJUAL</h1>
        <h2>{{ config('app.name', 'Aplikasi Inventory') }}</h2>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <h3>Informasi Filter:</h3>
        
        <div class="filter-row">
            <div class="filter-item">
                <strong>Brand:</strong> 
                @if(request('brand'))
                    {{ $barangTerjual->where('kode_brand', request('brand'))->first()->nama_brand ?? 'Brand Terpilih' }}
                @else
                    Semua Brand
                @endif
            </div>
            <div class="filter-item">
                <strong>Jenis Transaksi:</strong> 
                @if(request('jenis'))
                    {{ ucfirst(request('jenis')) }}
                @else
                    Semua Jenis
                @endif
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-item">
                <strong>Periode:</strong> 
                @if(request('tanggal_mulai') && request('tanggal_selesai'))
                    {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }} - 
                    {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d/m/Y') }}
                @elseif(request('tanggal_mulai'))
                    Dari {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }}
                @elseif(request('tanggal_selesai'))
                    Sampai {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d/m/Y') }}
                @else
                    Semua Periode
                @endif
            </div>
            <div class="filter-item">
                <strong>Tanggal Cetak:</strong> 
                {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-stats">
        <div class="stat-card">
            <h4>Total Transaksi</h4>
            <div class="value">{{ $barangTerjual->count() }}</div>
        </div>
        <div class="stat-card">
            <h4>Total Item Terjual</h4>
            <div class="value">{{ number_format($barangTerjual->sum('kuantitas')) }}</div>
        </div>
        <div class="stat-card">
            <h4>Total Pendapatan</h4>
            <div class="value">Rp {{ number_format($barangTerjual->sum('total_harga'), 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Rata-rata per Transaksi</h4>
            <div class="value">
                Rp {{ $barangTerjual->count() > 0 ? number_format($barangTerjual->sum('total_harga') / $barangTerjual->count(), 0, ',', '.') : '0' }}
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 4%">No</th>
                    <th style="width: 10%">Kode Transaksi</th>
                    <th style="width: 10%">Tanggal</th>
                    <th style="width: 15%">Pelanggan</th>
                    <th style="width: 20%">Barang</th>
                    <th style="width: 10%">Brand</th>
                    <th style="width: 6%">Qty</th>
                    <th style="width: 10%">Harga Satuan</th>
                    <th style="width: 10%">Total</th>
                    <th style="width: 8%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangTerjual as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">{{ $item->kode_transaksi }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d/m/Y') }}</td>
                        <td class="text-left">{{ $item->nama_pelanggan ?? 'Umum' }}</td>
                        <td class="text-left">
                            {{ $item->nama_barang }} {{ $item->nama_tipe }}<br>
                            <small>{{ $item->warna }} | {{ $item->ukuran }}</small>
                        </td>
                        <td>{{ $item->nama_brand }}</td>
                        <td>{{ number_format($item->kuantitas) }}</td>
                        <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                        <td>
                            @if($item->status == 'selesai')
                                <span class="badge badge-success">Selesai</span>
                            @elseif($item->status == 'dikirim')
                                <span class="badge badge-info">Dikirim</span>
                            @else
                                <span class="badge badge-warning">{{ ucfirst($item->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="no-data">Tidak ada data transaksi yang sesuai dengan filter yang dipilih</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Summary by Brand (if data exists) -->
    @if($barangTerjual->count() > 0)
        <div style="margin-top: 30px;">
            <h3 style="margin-bottom: 15px; color: #495057;">Ringkasan per Brand:</h3>
            <table style="width: 50%; font-size: 11px;">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Jumlah Item</th>
                        <th>Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $brandSummary = $barangTerjual->groupBy('nama_brand')->map(function($items, $brand) {
                            return [
                                'brand' => $brand,
                                'total_qty' => $items->sum('kuantitas'),
                                'total_sales' => $items->sum('total_harga')
                            ];
                        });
                    @endphp
                    @foreach($brandSummary as $summary)
                        <tr>
                            <td class="text-left">{{ $summary['brand'] }}</td>
                            <td>{{ number_format($summary['total_qty']) }}</td>
                            <td class="text-right">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i:s') }}</p>
        <p>Halaman ini digenerate secara otomatis oleh sistem</p>
    </div>
</body>
</html>