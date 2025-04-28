@extends('pelanggan.layouts.app')
@section('title', 'Detail Transaksi')
@section('content')
    <main class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mt-20">
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('transaksi.index') }}" class="text-gray-500 hover:text-custom mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold">Detail Pesanan #{{ $transaksi->kode_transaksi }}</h1>
    </div>
    
    <!-- Status Pesanan -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium">Status Pesanan</h2>
                <p class="text-sm text-gray-500">{{ $transaksi->tanggal_format }}</p>
            </div>
            @if($transaksi->status === 'dikirim')
            <a href="{{ route('transaksi.terima', $transaksi->kode_transaksi) }}" 
                class="bg-custom text-white py-2 px-4 rounded-lg hover:bg-custom/90"
                onclick="return confirm('Apakah Anda yakin telah menerima pesanan ini?')">
                Pesanan Diterima
            </a>
            @elseif($transaksi->status === 'belum_dibayar' || $transaksi->status === 'menunggu_konfirmasi')
            <a href="{{ route('transaksi.batalkan', $transaksi->kode_transaksi) }}" 
                class="text-red-500 hover:text-red-700"
                onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                Batalkan Pesanan
            </a>
            @endif
        </div>
        
        <div class="mt-6">
            <ol class="flex items-center w-full">
                <li class="flex items-center {{ in_array($transaksi->status, ['belum_dibayar', 'menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai']) ? 'text-custom' : 'text-gray-500' }} space-x-2.5">
                    <span class="flex items-center justify-center w-8 h-8 border {{ in_array($transaksi->status, ['belum_dibayar', 'menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai']) ? 'border-custom' : 'border-gray-500' }} rounded-full shrink-0">
                        1
                    </span>
                    <span>
                        <h3 class="font-medium leading-tight">Pembayaran</h3>
                        <p class="text-sm">{{ $transaksi->pembayaran->status_label }}</p>
                    </span>
                </li>
                <li class="flex w-full items-center {{ in_array($transaksi->status, ['menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai']) ? 'text-custom' : 'text-gray-500' }}">
                    <div class="flex-1 h-0.5 {{ in_array($transaksi->status, ['menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai']) ? 'bg-custom' : 'bg-gray-300' }}"></div>
                </li>
                <li class="flex items-center {{ in_array($transaksi->status, ['diproses', 'dikirim', 'selesai']) ? 'text-custom' : 'text-gray-500' }} space-x-2.5">
                    <span class="flex items-center justify-center w-8 h-8 border {{ in_array($transaksi->status, ['diproses', 'dikirim', 'selesai']) ? 'border-custom' : 'border-gray-500' }} rounded-full shrink-0">
                        2
                    </span>
                    <span>
                        <h3 class="font-medium leading-tight">Diproses</h3>
                        <p class="text-sm">{{ $transaksi->status === 'diproses' ? 'Sedang diproses' : ($transaksi->status === 'dikirim' || $transaksi->status === 'selesai' ? 'Selesai diproses' : 'Menunggu') }}</p>
                    </span>
                </li>
                <li class="flex w-full items-center {{ in_array($transaksi->status, ['dikirim', 'selesai']) ? 'text-custom' : 'text-gray-500' }}">
                    <div class="flex-1 h-0.5 {{ in_array($transaksi->status, ['dikirim', 'selesai']) ? 'bg-custom' : 'bg-gray-300' }}"></div>
                </li>
                <li class="flex items-center {{ in_array($transaksi->status, ['dikirim', 'selesai']) ? 'text-custom' : 'text-gray-500' }} space-x-2.5">
                    <span class="flex items-center justify-center w-8 h-8 border {{ in_array($transaksi->status, ['dikirim', 'selesai']) ? 'border-custom' : 'border-gray-500' }} rounded-full shrink-0">
                        3
                    </span>
                    <span>
                        <h3 class="font-medium leading-tight">Pengiriman</h3>
                        <p class="text-sm">{{ $transaksi->status === 'dikirim' ? 'Dalam pengiriman' : ($transaksi->status === 'selesai' ? 'Terkirim' : 'Menunggu') }}</p>
                    </span>
                </li>
                <li class="flex w-full items-center {{ $transaksi->status === 'selesai' ? 'text-custom' : 'text-gray-500' }}">
                    <div class="flex-1 h-0.5 {{ $transaksi->status === 'selesai' ? 'bg-custom' : 'bg-gray-300' }}"></div>
                </li>
                <li class="flex items-center {{ $transaksi->status === 'selesai' ? 'text-custom' : 'text-gray-500' }} space-x-2.5">
                    <span class="flex items-center justify-center w-8 h-8 border {{ $transaksi->status === 'selesai' ? 'border-custom' : 'border-gray-500' }} rounded-full shrink-0">
                        4
                    </span>
                    <span>
                        <h3 class="font-medium leading-tight">Selesai</h3>
                        <p class="text-sm">{{ $transaksi->status === 'selesai' ? 'Pesanan selesai' : 'Menunggu' }}</p>
                    </span>
                </li>
            </ol>
        </div>
    </div>
    
    <div class="lg:grid lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-8">
            <!-- Informasi Pengiriman -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-medium">Informasi Pengiriman</h2>
                </div>
                
                <div class="p-4">
                    <div class="mb-4">
                        <h3 class="font-medium mb-2">Alamat Pengiriman</h3>
                        <div class="border rounded-lg p-3">
                            <p class="font-medium">{{ $transaksi->alamat->nama_penerima }} ({{ $transaksi->alamat->no_hp_penerima }})</p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $transaksi->alamat->alamat_lengkap }}, {{ $transaksi->alamat->kelurahan }}, {{ $transaksi->alamat->kecamatan }}, 
                                {{ $transaksi->alamat->kota }}, {{ $transaksi->alamat->provinsi }}, {{ $transaksi->alamat->kode_pos }}
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-medium mb-2">Status Pengiriman</h3>
                        <div class="border rounded-lg p-3">
                            <div class="flex justify-between mb-3">
                                <div>
                                    <p class="text-sm font-medium">{{ $transaksi->ekspedisi }} {{ $transaksi->layanan_ekspedisi }}</p>
                                    <p class="text-xs text-gray-500">Estimasi: {{ $transaksi->estimasi_waktu }}</p>
                                </div>
                                @if($transaksi->pengiriman && $transaksi->pengiriman->resi)
                                <div>
                                    <p class="text-sm font-medium">No. Resi:</p>
                                    <p class="text-xs font-medium text-custom">{{ $transaksi->pengiriman->resi }}</p>
                                </div>
                                @endif
                            </div>
                            
                            @if($transaksi->pengiriman)
                            <div class="space-y-4">
                                <div class="relative pl-6 pb-3">
                                    <div class="absolute top-0 left-0 h-full">
                                        <div class="h-full w-0.5 bg-gray-200"></div>
                                    </div>
                                    <div class="absolute top-1 left-0 w-3 h-3 rounded-full bg-custom -translate-x-1.5"></div>
                                    <div>
                                        <p class="text-sm font-medium">Status: {{ $transaksi->pengiriman->status_label }}</p>
                                        @if($transaksi->pengiriman->tanggal_pengiriman)
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($transaksi->pengiriman->tanggal_pengiriman)->format('d M Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                @foreach($transaksi->pengiriman->detailPengiriman as $detail)
                                <div class="relative pl-6 pb-3">
                                    <div class="absolute top-0 left-0 h-full">
                                        <div class="h-full w-0.5 bg-gray-200"></div>
                                    </div>
                                    <div class="absolute top-1 left-0 w-3 h-3 rounded-full bg-gray-300 -translate-x-1.5"></div>
                                    <div>
                                        <p class="text-sm font-medium">{{ $detail->lokasi }}</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($detail->waktu_update)->format('d M Y H:i') }}</p>
                                        <p class="text-xs text-gray-600">{{ $detail->keterangan }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-gray-500">Pengiriman belum diproses</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daftar Barang -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-medium">Daftar Barang</h2>
                </div>
                
                <div class="divide-y divide-gray-200">
                    @foreach($transaksi->detailTransaksi as $detail)
                    <div class="p-4 flex items-start">
                        <div class="flex-shrink-0 w-16 h-16">
                            <img src="{{ asset('storage/' . $detail->detailBarang->barang->gambar_utama) }}" 
                                alt="{{ $detail->detailBarang->barang->nama_barang }}" 
                                class="w-full h-full object-cover rounded">
                        </div>
                        <div class="ml-3 flex-1">
                            <div class="flex justify-between">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">{{ $detail->detailBarang->barang->nama_barang }}</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $detail->kuantitas }} x Rp {{ number_format($detail->harga, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Ukuran: {{ $detail->detailBarang->ukuran }} | 
                                        Warna: {{ $detail->detailBarang->warna->warna }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
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
        
        <div class="lg:col-span-4 mt-6 lg:mt-0">
            <!-- Ringkasan Pesanan -->
            <div class="bg-white rounded-lg shadow sticky top-4">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-medium">Ringkasan Pesanan</h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Harga ({{ $transaksi->detailTransaksi->count() }} barang)</span>
                            <span>Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Biaya Pengiriman</span>
                            <span>Rp {{ number_format($transaksi->ongkir, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t">
                        <div class="flex justify-between mb-4">
                            <span class="font-medium">Total Tagihan</span>
                            <span class="text-lg font-bold text-custom">
                                Rp {{ number_format($transaksi->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Informasi Pembayaran -->
                    <div class="mt-4 pt-4 border-t">
                        <h3 class="font-medium mb-3">Informasi Pembayaran</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="{{ $transaksi->pembayaran->status === 'sukses' ? 'text-green-600' : ($transaksi->pembayaran->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }} font-medium">
                                    {{ $transaksi->pembayaran->status_label }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metode:</span>
                                <span class="font-medium">{{ $transaksi->pembayaran->metode_pembayaran }}</span>
                            </div>
                            @if($transaksi->pembayaran->tanggal_pembayaran)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tanggal:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($transaksi->pembayaran->tanggal_pembayaran)->format('d M Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($transaksi->status === 'belum_dibayar' && $transaksi->pembayaran->status === 'pending')
                    <div class="mt-4">
                        <a href="{{ route('pembayaran.show', $transaksi->kode_transaksi) }}" 
                            class="block w-full bg-custom text-white text-center py-2 rounded-lg hover:bg-custom/90">
                            Bayar Sekarang
                        </a>
                    </div>
                    @endif
                    
                    @if($transaksi->status === 'belum_dibayar' || $transaksi->status === 'menunggu_konfirmasi')
                    <div class="mt-2">
                        <a href="{{ route('transaksi.batalkan', $transaksi->kode_transaksi) }}" 
                            class="block w-full text-red-500 text-center py-2 rounded-lg border border-red-500 hover:bg-red-50"
                            onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                            Batalkan Pesanan
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</main>
@endsection