<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Terjual</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            float: left;
        }
        .logo img {
            height: 40px;
        }
        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 20px;
        }
        .sub-title {
            text-align: center;
            margin-bottom: 10px;
        }
        .info-table, .data-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .info-table td {
            padding: 4px;
        }
        .data-table th, .data-table td, .summary-table td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }
        .data-table th {
            background-color: #eee;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            color: #fff;
            font-size: 10px;
        }
        .danger { background-color: #dc3545; }
        .warning { background-color: #ffc107; color: #000; }
        .success { background-color: #28a745; }
        .secondary { background-color: #6c757d; }
        .summary {
            margin-top: 30px;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">
            <img src="{{ public_path('img/logo/logo-wf-panjang.png') }}" alt="Logo Warrior">
        </div>
        <div style="clear: both;"></div>
        <div class="report-title">Laporan Barang </div>
        <div class="sub-title">
            Tanggal Laporan: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Brand:</strong></td>
            <td>{{ $filter['brand_nama'] ?? 'Semua' }}</td>
            <td><strong>Tipe:</strong></td>
            <td>{{ $filter['tipe_nama'] ?? 'Semua' }}</td>
        </tr>
        <tr>
            <td><strong>Warna:</strong></td>
            <td>{{ $filter['warna_nama'] ?? 'Semua' }}</td>
            <td><strong>Periode:</strong></td>
            <td>
                {{ $filter['tanggal_mulai'] ? date('d/m/Y', strtotime($filter['tanggal_mulai'])) : '-' }} 
                - 
                {{ $filter['tanggal_selesai'] ? date('d/m/Y', strtotime($filter['tanggal_selesai'])) : '-' }}
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Detail</th>
                <th>Nama Barang</th>
                <th>Brand</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Jumlah Terjual</th>
                <th>Total Pendapatan</th>
                <th>Status Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporanBarang as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->kode_detail }}</td>
                    <td>{{ $item->nama_barang ?? '-' }}, {{ $item->nama_tipe ?? '-' }} | {{ $item->warna ?? '-' }} | {{ $item->ukuran }}</td>
                    <td>{{ $item->nama_brand ?? '-' }}</td>
                    <td>{{ $item->stok }}</td>
                    <td>Rp {{ number_format($item->harga_normal, 0, ',', '.') }}</td>
                    <td>{{ $item->jumlah_terjual }}</td>
                    <td>Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                    <td>
                        @if ($item->stok < 10)
                            <span class="badge danger">Harus Restok</span>
                        @elseif ($item->stok < 20)
                            <span class="badge warning">Stok Menipis</span>
                        @else
                            <span class="badge success">Aman</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h4>Ringkasan</h4>
        <table class="summary-table">
            <tr>
                <td><strong>Total Item:</strong></td>
                <td>{{ $laporanBarang->count() }}</td>
            </tr>
            <tr>
                <td><strong>Total Terjual:</strong></td>
                <td>{{ $laporanBarang->sum('jumlah_terjual') }}</td>
            </tr>
            <tr>
                <td><strong>Stok Tersisa:</strong></td>
                <td>{{ $laporanBarang->sum('stok') }}</td>
            </tr>
            <tr>
                <td><strong>Total Pendapatan:</strong></td>
                <td>Rp {{ number_format($laporanBarang->sum('total_pendapatan'), 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="margin-top: 40px;">
        <p>Dicetak oleh: {{ $admin ?? 'Administrator' }}</p>
        <p>{{ now()->translatedFormat('d F Y, H:i') }}</p>
    </div>

</body>
</html>
