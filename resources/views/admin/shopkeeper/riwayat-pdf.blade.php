<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Riwayat Transaksi</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>

<body>
    <h2>Riwayat Transaksi ({{ ucfirst(str_replace('_', ' ', $status)) }})</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Transaksi</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th>Keterangan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_transaksi }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->nama_pelanggan ?? 'Tidak diketahui' }}</td>
                    <td>{{ $item->produk ?? 'Tidak ada produk' }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                    <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
