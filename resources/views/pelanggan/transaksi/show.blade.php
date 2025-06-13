@extends('pelanggan.layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header dengan Back Button -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <button onclick="history.back()"
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-md hover:shadow-lg transition-all duration-200 group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-800 transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Detail Pesanan</h1>
                        <p class="text-gray-600 mt-1">Nomor: <span
                                class="font-semibold text-blue-600">#{{ $transaksi->kode_transaksi }}</span></p>
                    </div>
                </div>

                <!-- Status Badge dengan Cap Lunas -->
                <div class="flex items-center space-x-3">
                    @if ($transaksi->status == 'selesai')
                        <div class="relative">
                            <div
                                class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-full shadow-lg transform rotate-12 border-4 border-white">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-bold text-sm">LUNAS</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php
                        $statusConfig = [
                            'belum_dibayar' => [
                                'bg' => 'bg-red-100 text-red-800',
                                'label' => 'Belum Dibayar',
                                'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                            'menunggu_konfirmasi' => [
                                'bg' => 'bg-yellow-100 text-yellow-800',
                                'label' => 'Menunggu Konfirmasi',
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                            'diproses' => [
                                'bg' => 'bg-blue-100 text-blue-800',
                                'label' => 'Diproses',
                                'icon' =>
                                    'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                            ],
                            'dikirim' => [
                                'bg' => 'bg-purple-100 text-purple-800',
                                'label' => 'Dikirim',
                                'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                            ],
                            'selesai' => [
                                'bg' => 'bg-green-100 text-green-800',
                                'label' => 'Selesai',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                            'dibatalkan' => [
                                'bg' => 'bg-gray-100 text-gray-800',
                                'label' => 'Dibatalkan',
                                'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                        ];
                        $config = $statusConfig[$transaksi->status] ?? [
                            'bg' => 'bg-gray-100 text-gray-800',
                            'label' => $transaksi->status,
                            'icon' =>
                                'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        ];
                    @endphp

                    <div
                        class="flex items-center space-x-2 {{ $config['bg'] }} px-4 py-2 rounded-full font-semibold shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}">
                            </path>
                        </svg>
                        <span>{{ $config['label'] }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Informasi Pelanggan & Pengiriman -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pelanggan</h3>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Nama</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $transaksi->pelanggan->nama_pelanggan ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">WhatsApp</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $transaksi->pelanggan->no_hp ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Email</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $transaksi->pelanggan->email ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pengiriman</h3>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Alamat</span>
                                        <span
                                            class="font-medium text-gray-900 text-right max-w-48">{{ $transaksi->pelanggan->alamat_pengguna ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Kurir</span>
                                        <span
                                            class="font-medium text-gray-900">{{ strtoupper($transaksi->ekspedisi ?? 'N/A') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Service</span>
                                        <span
                                            class="font-medium text-gray-900">{{ strtoupper($transaksi->layanan_ekspedisi ?? 'N/A') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Estimasi</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $transaksi->estimasi_waktu ?? 'N/A' }}</span>
                                    </div>
                                    @if ($transaksi->pengiriman && $transaksi->pengiriman->resi)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">No. Resi</span>
                                            <span
                                                class="font-mono font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $transaksi->pengiriman->resi }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Produk -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Produk yang Dibeli</h3>
                        </div>

                        <div class="space-y-4">
                            @foreach ($transaksi->detailTransaksi as $detail)
                                <div
                                    class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-16 h-16 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl flex items-center justify-center">
                                            @if ($detail->detailBarang->barang->gambar_barang ?? false)
                                                <img src="{{ asset('storage/' . $detail->detailBarang->barang->gambar_barang) }}"
                                                    alt="{{ $detail->detailBarang->barang->nama_barang }}"
                                                    class="w-full h-full object-cover rounded-xl">
                                            @else
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-gray-900 truncate">
                                            {{ $detail->detailBarang->barang->nama_barang ?? 'Produk tidak ditemukan' }}
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Ukuran {{ $detail->detailBarang->ukuran ?? 'N/A' }}
                                            @if ($detail->detailBarang->warna)
                                                • {{ $detail->detailBarang->warna->warna }}
                                            @endif
                                        </p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-sm text-gray-600">{{ $detail->kuantitas }} x
                                                Rp{{ number_format($detail->harga, 0, ',', '.') }}</span>
                                            <span
                                                class="font-semibold text-gray-900">Rp{{ number_format($detail->kuantitas * $detail->harga, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Ringkasan Pembayaran -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Ringkasan Pembayaran</h3>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Ongkir</span>
                                <span>Rp{{ number_format($transaksi->ongkir, 0, ',', '.') }}</span>
                            </div>
                            @if ($transaksi->diskon > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Diskon</span>
                                    <span>-Rp{{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <hr class="my-3">
                            <div class="flex justify-between text-lg font-bold text-gray-900">
                                <span>Total</span>
                                <span>Rp{{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Pesanan -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Informasi Pesanan</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-start">
                                <span class="text-gray-600">Tanggal Pesanan</span>
                                <span
                                    class="font-medium text-gray-900 text-right">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-gray-600">Metode Pembayaran</span>
                                <span
                                    class="font-medium text-gray-900">{{ ucfirst($transaksi->metode_pembayaran ?? 'Transfer Bank') }}</span>
                            </div>
                            @if ($transaksi->catatan)
                                <div class="flex justify-between items-start">
                                    <span class="text-gray-600">Catatan</span>
                                    <span
                                        class="font-medium text-gray-900 text-right max-w-32">{{ $transaksi->catatan }}</span>
                                </div>
                            @endif
                            @if ($transaksi->is_dropship)
                                <div class="flex items-center justify-center">
                                    <span
                                        class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Dropship</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    {{-- <div class="space-y-3">
                        <a href="{{ route('customer.orders.invoice', $transaksi->kode_transaksi) }}" --}}
                    @if ($transaksi->pembayaran->status === 'pending' && $transaksi->pembayaran->snap_token)
                        <button id="pay-button" type="button"
                            class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-4 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Bayar Sekarang
                        </button>
                    @endif

                    <a href="{{ route('customer.invoice', $transaksi->kode_transaksi) }}"
                        class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-4 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="font-medium">Download Invoice</span>
                    </a>
                    

                    @if (in_array($transaksi->status, ['selesai']))
                        <a href="{{ route('ulasan.create', $transaksi->kode_transaksi) }}"
                            class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 px-4 rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                                </path>
                            </svg>
                            <span class="font-medium">Beri Ulasan</span>
                        </a>
                    @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .bg-gradient-to-br {
                background: white !important;
            }
        }
    </style>
@endsection


@push('scripts')
    @if ($transaksi->pembayaran->status === 'pending' && $transaksi->pembayaran->snap_token)
        <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Payment page loaded successfully');

                const payButton = document.getElementById('pay-button');

                if (payButton) {
                    payButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Initiating payment...');

                        snap.pay('{{ $transaksi->pembayaran->snap_token }}', {
                            onSuccess: function(result) {
                                console.log('Payment success:', result);
                                window.location.href =
                                    '{{ route('transaksi.index', [], false) }}?status=menunggu_konfirmasi';
                            },
                            onPending: function(result) {
                                console.log('Payment pending:', result);
                                window.location.href =
                                    '{{ route('transaksi.index', [], false) }}?status=belum_dibayar';
                            },
                            onError: function(result) {
                                console.log('Payment error:', result);
                                window.location.href =
                                    '{{ route('transaksi.index', [], false) }}?status=dibatalkan';
                            },
                            onClose: function() {
                                console.log('Payment popup closed');
                                alert(
                                    'Anda menutup popup pembayaran sebelum menyelesaikan transaksi!');
                            }
web
                        });
                    });
                } else {
                    console.error('Pay button not found!');
                }
            });
        </script>
    @endif
@endpush
