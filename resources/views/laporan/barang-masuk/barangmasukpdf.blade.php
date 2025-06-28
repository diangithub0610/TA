<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Masuk</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 10px;
        }
        .title {
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #444;
        }
        th, td {
            padding: 6px;
            text-align: left;
        }
        .summary {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

    <h2 class="title">LAPORAN BARANG MASUK</h2>
    
    @if(request('tanggal_mulai') || request('tanggal_selesai'))
        <p class="subtitle">
            Periode: 
            {{ request('tanggal_mulai') ? date('d-m-Y', strtotime(request('tanggal_mulai'))) : '-' }} 
            s/d 
            {{ request('tanggal_selesai') ? date('d-m-Y', strtotime(request('tanggal_selesai'))) : '-' }}
        </p>
    @endif

    <table>
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
            </tr>
        </thead>
        <tbody>
            @foreach($barangMasuk as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kode_pembelian }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d/m/Y') }}</td>
                    <td>{{ $item->admin }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->nama_brand }}</td>
                    <td>{{ $item->nama_tipe }}</td>
                    <td class="text-right">{{ number_format($item->jumlah_masuk) }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_barang_masuk, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <tr>
            <td class="summary">Total Jumlah Barang</td>
            <td class="summary text-right">{{ number_format($totalJumlah) }}</td>
        </tr>
        <tr>
            <td class="summary">Total Nilai Barang Masuk</td>
            <td class="summary text-right">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
        </tr>
    </table>

</body>
</html>
