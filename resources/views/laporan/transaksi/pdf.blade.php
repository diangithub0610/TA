<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan transaksi</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header h2 {
            margin: 5px 0 0;
            font-size: 14px;
            color: #555;
        }
        .filter-info {
            background-color: #f1f3f5;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .filter-info h3 {
            margin: 0 0 8px;
            font-size: 13px;
            color: #333;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .filter-item {
            min-width: 200px;
        }
        .filter-item strong {
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th {
            background-color: #343a40;
            color: #fff;
            padding: 6px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        td {
            padding: 5px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background-color: #28a745; }
        .badge-info { background-color: #17a2b8; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #666;
            text-align: right;
        }
        .summary-table {
            margin-top: 30px;
            width: 50%;
            font-size: 10.5px;
        }
        .summary-table th, .summary-table td {
            padding: 6px;
            border: 1px solid #ccc;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
        @media print {
            body {
                padding: 10px;
            }
            .filter-info {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="logo" style="flex: 0 0 100px;">
        <img src="{{ public_path('img/logo/logo-wf-panjang.png') }}" alt="Logo Warrior" style="height: 40px;">
    </div>

    <div class="header">
        <h1>LAPORAN TRANSAKSI</h1>
        <div class="company-name">Warrior Footwear</div>
        <div class="address">
            Jl. Contoh Alamat No. 123<br>
            Kota, Provinsi 12345<br>
            Telp: (021) 12345678 | Email: info@warriorfootwear.com
        </div>
    </div>

    <div class="filter-info">
        <h3>Filter Laporan:</h3>
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
                {{ request('jenis') ? ucfirst(request('jenis')) : 'Semua Jenis' }}
            </div>
            <div class="filter-item">
                <strong>Periode:</strong>
                @if(request('tanggal_mulai') && request('tanggal_selesai'))
                    {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d/m/Y') }}
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

    {{-- Data Table --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Barang</th>
                <th>Brand</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grouped = $barangTerjual->groupBy('kode_transaksi');
                $no = 1;
            @endphp
            @forelse($grouped as $kode => $items)
                @foreach($items as $index => $item)
                    <tr>
                        @if($index == 0)
                            <td rowspan="{{ $items->count() }}">{{ $no++ }}</td>
                            <td rowspan="{{ $items->count() }}">{{ $item->kode_transaksi }}</td>
                            <td rowspan="{{ $items->count() }}">{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d/m/Y') }}</td>
                            <td rowspan="{{ $items->count() }}" class="text-left">{{ $item->nama_pelanggan ?? 'Umum' }}</td>
                        @endif
                        <td class="text-left">
                            {{ $item->nama_barang }} {{ $item->nama_tipe }}<br>
                            <small>{{ $item->warna }} | {{ $item->ukuran }}</small>
                        </td>
                        <td>{{ $item->nama_brand }}</td>
                        <td>{{ number_format($item->kuantitas) }}</td>
                        <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                        @if($index == 0)
                            <td rowspan="{{ $items->count() }}">
                                @if($item->status == 'selesai')
                                    <span class="badge badge-success">Selesai</span>
                                @elseif($item->status == 'dikirim')
                                    <span class="badge badge-info">Dikirim</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($item->status) }}</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="10" class="no-data">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary Section --}}
    @if($barangTerjual->count() > 0)
        <div class="summary-section">
            <h3 style="margin-top: 40px; margin-bottom: 10px;">Ringkasan Penjualan:</h3>
            <table class="summary-table">
                <tbody>
                    <tr>
                        <th>Total Transaksi</th>
                        <td>{{ $barangTerjual->groupBy('kode_transaksi')->count() }}</td>
                    </tr>
                    <tr>
                        <th>Total Item Terjual</th>
                        <td>{{ number_format($barangTerjual->sum('kuantitas')) }}</td>
                    </tr>
                    <tr>
                        <th>Total Pendapatan</th>
                        <td>Rp {{ number_format($barangTerjual->sum('total_harga'), 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Rata-rata per Transaksi</th>
                        <td>
                            Rp {{ $barangTerjual->groupBy('kode_transaksi')->count() > 0 ? number_format($barangTerjual->sum('total_harga') / $barangTerjual->groupBy('kode_transaksi')->count(), 0, ',', '.') : '0' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Ringkasan per Brand --}}
            <h3 style="margin-top: 25px;">Ringkasan per Brand:</h3>
            <table class="summary-table">
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
                            <td>{{ $summary['brand'] }}</td>
                            <td>{{ number_format($summary['total_qty']) }}</td>
                            <td class="text-right">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i:s') }}<br>
        Halaman ini digenerate otomatis oleh sistem.
    </div>
</body>
</html>
