<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - {{ $transaksi->kode_transaksi }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
        }

        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .store-info {
            font-size: 10px;
            margin-bottom: 2px;
        }

        .transaction-info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .transaction-info div {
            margin-bottom: 2px;
        }

        .items-header {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item {
            margin-bottom: 8px;
            font-size: 11px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .item-price-qty {
            display: flex;
            justify-content: space-between;
        }

        .total-section {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .thank-you {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .marketplace-info {
            background: #f5f5f5;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .dropship-info {
            background: #fff3cd;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ffeaa7;
            font-size: 10px;
        }

        @media print {
            body {
                background: white;
            }

            .receipt {
                width: 100%;
                margin: 0;
                padding: 5px;
            }

            .no-print {
                display: none;
            }
        }

        /* Print button styles */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Cetak Struk
    </button>

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">TOKO SEPATU XYZ</div>
            <div class="store-info">Jl. Contoh No. 123, Kota ABC</div>
            <div class="store-info">Telp: (021) 1234-5678</div>
            <div class="store-info">Email: info@tokosepatu.com</div>
        </div>

        <!-- Transaction Info -->
        <div class="transaction-info">
            <div><strong>No. Transaksi:</strong> {{ $transaksi->kode_transaksi }}</div>
            <div><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y H:i') }}</div>
            <div><strong>Kasir:</strong> {{ $transaksi->pengguna->nama_admin ?? 'System' }}</div>

            @if ($transaksi->jenis == 'offline' && $transaksi->ekspedisi == 'Marketplace')
                <div><strong>Jenis:</strong> Marketplace</div>
            @else
                <div><strong>Jenis:</strong> Offline</div>
            @endif

            @if ($transaksi->pelanggan && $transaksi->pelanggan->role == 'reseller')
                <div><strong>Reseller:</strong> {{ $transaksi->pelanggan->nama_pelanggan }}</div>
                <div><strong>HP Reseller:</strong> {{ $transaksi->pelanggan->no_hp }}</div>
            @endif
        </div>

        <!-- Marketplace Info -->
        @if ($transaksi->jenis == 'offline' && $transaksi->ekspedisi == 'Marketplace')
            <div class="marketplace-info">
                <strong>INFO MARKETPLACE:</strong><br>
                {{ $transaksi->keterangan ?? 'Transaksi dari marketplace' }}
            </div>
        @endif

        <!-- Dropship Info -->
        @if ($transaksi->is_dropship)
            <div class="dropship-info">
                <strong>DROPSHIP:</strong><br>
                Pengirim: {{ $transaksi->nama_pengirim ?? '-' }}<br>
                HP: {{ $transaksi->no_hp_pengirim ?? '-' }}
            </div>
        @endif

        <!-- Items Header -->
        <div class="items-header">
            DAFTAR ITEM
        </div>

        <!-- Items -->
        @php $total = 0; @endphp
        @foreach ($transaksi->detailTransaksi as $detail)
            @php
                $subtotal = $detail->harga * $detail->qty;
                $total += $subtotal;
            @endphp
            <div class="item">
                <div class="item-name">{{ $detail->detailBarang->barang->nama_barang }}</div>
                <div class="item-details">
                    <span>Ukuran: {{ $detail->detailBarang->ukuran }}</span>
                    @if ($detail->detailBarang->warna)
                        <span>{{ $detail->detailBarang->warna->warna }}</span>
                    @endif
                </div>
                <div class="item-price-qty">
                    <span>{{ $detail->qty }} x Rp {{ number_format($detail->harga, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>

            @if ($transaksi->ongkir > 0)
                <div class="total-row">
                    <span>Ongkir:</span>
                    <span>Rp {{ number_format($transaksi->ongkir, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($total + $transaksi->ongkir, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">TERIMA KASIH</div>
            <div>Barang yang sudah dibeli tidak dapat dikembalikan</div>
            <div>kecuali ada perjanjian khusus</div>
            <div style="margin-top: 10px;">
                Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }

        // Print function
        function printReceipt() {
            window.print();
        }

        // Close window after printing (optional)
        window.onafterprint = function() {
            // window.close();
        }
    </script>
</body>

</html>
