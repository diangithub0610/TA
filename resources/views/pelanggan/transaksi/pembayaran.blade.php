@extends('pelanggan.layouts.app')
@section('title', 'Pembayaran')
@section('content')
    <main class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mt-20">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Pembayaran</h1>

            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-8">
                    <!-- Info Pembayaran -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Informasi Pembayaran</h2>
                        </div>

                        <div class="p-4">
                            @if ($transaksi->pembayaran->status === 'pending')
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-circle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                Silakan lakukan pembayaran sebelum
                                                <strong>{{ \Carbon\Carbon::parse($transaksi->pembayaran->kadaluarsa_pembayaran)->format('d M Y H:i') }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mb-6">
                                    <p class="text-gray-700 mb-4">Klik tombol di bawah ini untuk melakukan pembayaran:</p>
                                    <button id="pay-button"
                                        class="bg-custom text-white py-3 px-6 rounded-lg hover:bg-custom/90 font-medium">
                                        Bayar Sekarang
                                    </button>
                                </div>

                                <div class="border rounded-lg p-4">
                                    <h3 class="font-medium mb-3">Detail Pesanan:</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Nomor Pesanan:</span>
                                            <span class="font-medium">{{ $transaksi->kode_transaksi }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total Pembayaran:</span>
                                            <span class="font-medium">Rp
                                                {{ number_format($transaksi->total, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Status:</span>
                                            <span
                                                class="text-yellow-600 font-medium">{{ $transaksi->pembayaran->status_label }}</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif($transaksi->pembayaran->status === 'sukses')
                                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle text-green-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-green-700">
                                                Pembayaran telah berhasil. Terima kasih atas pesanan Anda!
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-lg p-4">
                                    <h3 class="font-medium mb-3">Detail Pembayaran:</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Nomor Pesanan:</span>
                                            <span class="font-medium">{{ $transaksi->kode_transaksi }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Metode Pembayaran:</span>
                                            <span class="font-medium">{{ $transaksi->pembayaran->metode_pembayaran }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total Pembayaran:</span>
                                            <span class="font-medium">Rp
                                                {{ number_format($transaksi->total, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Tanggal Pembayaran:</span>
                                            <span
                                                class="font-medium">{{ \Carbon\Carbon::parse($transaksi->pembayaran->tanggal_pembayaran)->format('d M Y H:i') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Status:</span>
                                            <span
                                                class="text-green-600 font-medium">{{ $transaksi->pembayaran->status_label }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-6">
                                    <a href="{{ route('transaksi.detail', $transaksi->kode_transaksi) }}"
                                        class="inline-block bg-custom text-white py-2 px-6 rounded-lg hover:bg-custom/90">
                                        Lihat Detail Pesanan
                                    </a>
                                </div>
                            @elseif($transaksi->pembayaran->status === 'gagal' || $transaksi->pembayaran->status === 'kadaluarsa')
                                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-times-circle text-red-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-red-700">
                                                @if ($transaksi->pembayaran->status === 'gagal')
                                                    Pembayaran gagal. Silakan coba lagi.
                                                @else
                                                    Pembayaran kadaluarsa. Silakan buat pesanan baru.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-lg p-4">
                                    <h3 class="font-medium mb-3">Detail Pesanan:</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Nomor Pesanan:</span>
                                            <span class="font-medium">{{ $transaksi->kode_transaksi }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total Pembayaran:</span>
                                            <span class="font-medium">Rp
                                                {{ number_format($transaksi->total, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Status:</span>
                                            <span
                                                class="text-red-600 font-medium">{{ $transaksi->pembayaran->status_label }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-6">
                                    <a href="{{ route('produk') }}"
                                        class="inline-block bg-custom text-white py-2 px-6 rounded-lg hover:bg-custom/90">
                                        Lihat Produk Lainnya
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informasi Pesanan -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Detail Pesanan</h2>
                        </div>

                        <div class="p-4">
                            <div class="mb-4">
                                <h3 class="font-medium mb-2">Alamat Pengiriman</h3>
                                <div class="border rounded-lg p-3">
                                    <p class="font-medium">{{ $transaksi->alamat->nama_penerima }}
                                        ({{ $transaksi->alamat->no_hp_penerima }})</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $transaksi->alamat->alamat_lengkap }}, {{ $transaksi->alamat->kelurahan }},
                                        {{ $transaksi->alamat->kecamatan }},
                                        {{ $transaksi->alamat->kota }}, {{ $transaksi->alamat->provinsi }},
                                        {{ $transaksi->alamat->kode_pos }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-medium mb-2">Daftar Barang</h3>
                                <div class="border rounded-lg divide-y">
                                    @foreach ($transaksi->detailTransaksi as $detail)
                                        <div class="p-3 flex items-start">
                                            <div class="flex-shrink-0 w-16 h-16">
                                                <img src="{{ asset('storage/' . $detail->detailBarang->barang->gambar) }}"
                                                    alt="{{ $detail->detailBarang->barang->nama_barang }}"
                                                    class="w-full h-full object-cover rounded">
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <div class="flex justify-between">
                                                    <div>
                                                        <h4 class="text-sm font-medium">
                                                            {{ $detail->detailBarang->barang->nama_barang }}</h4>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            {{ $detail->kuantitas }} x Rp
                                                            {{ number_format($detail->harga, 0, ',', '.') }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            Ukuran: {{ $detail->detailBarang->ukuran }} |
                                                            Warna: {{ $detail->detailBarang->warna->warna }}
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-medium">
                                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 mt-6 lg:mt-0">
                    <div class="bg-white rounded-lg shadow sticky top-4">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Ringkasan Pembayaran</h2>
                        </div>

                        <div class="p-4">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Harga ({{ $transaksi->detailTransaksi->count() }}
                                        barang)</span>
                                    <span>Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Biaya Pengiriman</span>
                                    <span>Rp {{ number_format($transaksi->ongkir, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Metode Pengiriman</span>
                                    <span>{{ $transaksi->ekspedisi }} {{ $transaksi->layanan_ekspedisi }}</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t">
                                <div class="flex justify-between">
                                    <span class="font-medium">Total Tagihan</span>
                                    <span class="text-lg font-bold text-custom">
                                        Rp {{ number_format($transaksi->total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @if ($transaksi->pembayaran->status === 'pending' && $transaksi->pembayaran->snap_token)
        <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const payButton = document.getElementById('pay-button');

                payButton.addEventListener('click', function() {
                    // Memanggil snap untuk menampilkan popup pembayaran
                    snap.pay('{{ $transaksi->pembayaran->snap_token }}', {
                        onSuccess: function(result) {
                            window.location.href =
                                '{{ route('pembayaran.finish') }}?order_id={{ $transaksi->kode_transaksi }}';
                        },
                        onPending: function(result) {
                            window.location.href =
                                '{{ route('pembayaran.unfinish') }}?order_id={{ $transaksi->kode_transaksi }}';
                        },
                        onError: function(result) {
                            window.location.href =
                                '{{ route('pembayaran.error') }}?order_id={{ $transaksi->kode_transaksi }}';
                        },
                        onClose: function() {
                            alert('Anda menutup popup pembayaran sebelum menyelesaikan transaksi!');
                        }
                    });
                });
            });
        </script>
    @endif
@endpush
