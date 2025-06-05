<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Persediaan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .address {
            font-size: 12px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .report-info {
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        
        .info-label {
            width: 120px;
            font-weight: bold;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }
        
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .signature {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">Warrior Footwear</div>
        <div class="address">
            Jl. Contoh Alamat No. 123<br>
            Kota, Provinsi 12345<br>
            Telp: (021) 12345678 | Email: info@warriorfootwear.com
        </div>
    </div>

    <!-- Report Info -->
    <div class="report-info">
        <div class="report-title">Laporan Persediaan Barang</div>
        
        <table class="info-table">
            <tr>
                <td class="info-label">Tanggal Laporan</td>
                <td>: {{ $tanggal }}</td>
            </tr>
            <tr>
                <td class="info-label">Periode</td>
                <td>: Data Persediaan Saat Ini</td>
            </tr>
            <tr>
                <td class="info-label">Filter Warna</td>
                <td>: {{ $filters['warna'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Filter Brand</td>
                <td>: {{ $filters['brand'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Filter Tipe</td>
                <td>: {{ $filters['tipe'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Dibuat oleh</td>
                <td>: {{ $admin }}</td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="8%">No</th>
                <th width="12%">Kode Detail</th>
                <th width="20%">Nama Barang</th>
                <th width="12%">Brand</th>
                <th width="10%">Warna</th>
                <th width="12%">Tipe</th>
                <th width="8%">Ukuran</th>
                <th width="8%">Stok</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detailBarangs as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->kode_detail }}</td>
                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                    <td>{{ $detail->barang->tipe->brand->nama_brand ?? '-' }}</td>
                    <td>{{ $detail->warna->warna ?? '-' }}</td>
                    <td>{{ $detail->barang->tipe->nama_tipe ?? '-' }}</td>
                    <td class="text-center">{{ $detail->ukuran }}</td>
                    <td class="text-center">{{ $detail->stok }}</td>
                    <td class="text-center">
                        @if ($detail->stok < 10)
                            <span class="badge-danger">Harus Restok</span>
                        @else
                            <span class="badge-success">Aman</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    @if($detailBarangs->count() > 0)
    <div class="summary">
        <strong>Ringkasan:</strong><br>
        Total Item: {{ $detailBarangs->count() }} item<br>
        Total Stok: {{ $detailBarangs->sum('stok') }} unit<br>
        Item Perlu Restok: {{ $detailBarangs->where('stok', '<', 10)->count() }} item<br>
        Item Stok Aman: {{ $detailBarangs->where('stok', '>=', 10)->count() }} item
    </div>
    @endif

    <!-- Signature -->
    <div class="signature">
        <div class="signature-box">
            Mengetahui,<br>
            <div class="signature-line">
                {{ $admin }}<br>
                Administrator
            </div>
        </div>
    </div>
</body>
</html>