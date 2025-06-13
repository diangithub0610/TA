<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            color: #333;
            background: white;
            font-size: 14px;
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        
        .invoice-container {
            width: 100%;
            max-width: none;
            min-height: 100vh;
            margin: 0;
            background: white;
            box-shadow: none;
            border-radius: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .invoice-header {
            padding: 50px 60px 40px;
            border-bottom: 1px solid #e9ecef;
            background: white;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        
        .company-info h1 {
            font-size: 24px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 8px;
        }
        
        .company-details {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .invoice-meta {
            text-align: right;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .invoice-number {
            font-size: 16px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .invoice-date {
            font-size: 13px;
            color: #9ca3af;
            margin-top: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }
        
        .status-paid {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        /* Bill To Section */
        .bill-section {
            padding: 40px 60px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .bill-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .bill-to h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .bill-details {
            color: #4b5563;
            line-height: 1.7;
        }
        
        .bill-details strong {
            color: #1f2937;
            font-weight: 600;
        }
        
        .shipping-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border-left: 3px solid #2563eb;
        }
        
        .resi-number {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Items Table */
        .items-section {
            padding: 0 60px;
            flex-grow: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        .items-table thead {
            background: #f8fafc;
        }
        
        .items-table th {
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table td {
            padding: 22px 15px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .item-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .item-variant {
            font-size: 12px;
            color: #6b7280;
        }
        
        .quantity {
            text-align: center;
            font-weight: 600;
        }
        
        /* Summary */
        .summary-section {
            padding: 40px 60px;
            background: #f8fafc;
            margin-top: auto;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
            align-items: start;
        }
        
        .payment-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .payment-details {
            color: #6b7280;
            line-height: 1.7;
        }
        
        .summary-table {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .summary-value {
            font-weight: 600;
            color: #1f2937;
        }
        
        .discount-row .summary-value {
            color: #059669;
        }
        
        .total-row {
            border-top: 2px solid #e5e7eb;
            padding-top: 15px;
            margin-top: 10px;
        }
        
        .total-row .summary-label {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .total-row .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #2563eb;
        }
        
        /* Footer */
        .invoice-footer {
            padding: 40px 60px;
            text-align: center;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-text {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .generated-text {
            color: #9ca3af;
            font-size: 12px;
        }
        
        /* Print Styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            body {
                background: white;
                margin: 0;
                padding: 0;
                height: auto;
            }
            
            .invoice-container {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
                min-height: auto;
                width: 100%;
                max-width: none;
            }
            
            .status-badge {
                background: #f3f4f6 !important;
                color: #374151 !important;
                border: 1px solid #d1d5db !important;
            }
            
            @page {
                margin: 0.5in;
                size: A4;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .invoice-container {
                margin: 10px;
                border-radius: 0;
            }
            
            .invoice-header,
            .bill-section,
            .items-section,
            .summary-section,
            .invoice-footer {
                padding-left: 20px;
                padding-right: 20px;
            }
            
            .header-top {
                flex-direction: column;
                gap: 20px;
            }
            
            .invoice-meta {
                text-align: left;
            }
            
            .bill-grid,
            .summary-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .items-table {
                font-size: 12px;
            }
            
            .items-table th,
            .items-table td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-top">
                <div class="company-info">
                    <h1>Warrior Footwear</h1>
                    <div class="company-details">
                        Jl. mergangsang, gondokusuman<br>
                        Kota Baru, Yogyakarta<br>
                        Telp: 082258783132<br>
                        Email: info@warriorfootwear.com
                    </div>
                </div>
                <div class="invoice-meta">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">#{{ $transaksi->kode_transaksi }}</div>
                    <div class="invoice-date">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d M Y') }}</div>
                    @if(in_array($transaksi->status, ['menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai']))
                        <div class="status-badge status-paid">Paid</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bill To Section -->
        <div class="bill-section">
            <div class="bill-grid">
                <div class="bill-to">
                    <h3>Bill To</h3>
                    <div class="bill-details">
                        <strong>{{ $transaksi->pelanggan->nama_pelanggan ?? 'N/A' }}</strong><br>
                        {{ $transaksi->pelanggan->alamat_pengguna ?? 'N/A' }}<br>
                        WhatsApp: {{ $transaksi->pelanggan->no_hp ?? 'N/A' }}<br>
                        Email: {{ $transaksi->pelanggan->email ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="bill-to">
                    <h3>Shipping Info</h3>
                    <div class="shipping-info">
                        <div class="bill-details">
                            <strong>Kurir:</strong> {{ strtoupper($transaksi->ekspedisi ?? 'N/A') }}<br>
                            <strong>Service:</strong> {{ strtoupper($transaksi->layanan_ekspedisi ?? 'N/A') }}<br>
                            @if($transaksi->pengiriman && $transaksi->pengiriman->resi)
                                <strong>No. Resi:</strong> <span class="resi-number">{{ $transaksi->pengiriman->resi }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksi->detailTransaksi as $detail)
                    <tr>
                        <td>
                            <div class="item-name">{{ $detail->detailBarang->barang->nama_barang ?? 'Produk tidak ditemukan' }}</div>
                            <div class="item-variant">
                                Size {{ $detail->detailBarang->ukuran ?? 'N/A' }}
                                @if($detail->detailBarang->warna) • {{ $detail->detailBarang->warna->warna }} @endif
                            </div>
                        </td>
                        <td>Rp{{ number_format($detail->harga, 0, ',', '.') }}</td>
                        <td class="quantity">{{ $detail->kuantitas }}</td>
                        <td>Rp{{ number_format($detail->kuantitas * $detail->harga, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-grid">
                <div class="payment-info">
                    <h4>Payment Information</h4>
                    <div class="payment-details">
                        <strong>Payment Method:</strong> {{ ucfirst($transaksi->metode_pembayaran ?? 'Transfer Bank') }}<br>
                        <strong>Payment Date:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d M Y, H:i') }}
                        @if($transaksi->catatan)
                            <br><strong>Notes:</strong> {{ $transaksi->catatan }}
                        @endif
                        @if($transaksi->is_dropship)
                            <br><strong>Order Type:</strong> <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Dropship</span>
                        @endif
                    </div>
                </div>
                
                <div class="summary-table">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping</span>
                        <span class="summary-value">Rp{{ number_format($transaksi->ongkir, 0, ',', '.') }}</span>
                    </div>
                    @if($transaksi->diskon > 0)
                    <div class="summary-row discount-row">
                        <span class="summary-label">Discount</span>
                        <span class="summary-value">-Rp{{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="summary-row total-row">
                        <span class="summary-label">Total</span>
                        <span class="summary-value">Rp{{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-text">Thank you for your business!</div>
            <div class="generated-text">This invoice was generated on {{ date('d M Y, H:i') }}</div>
        </div>
    </div>
</body>
</html>