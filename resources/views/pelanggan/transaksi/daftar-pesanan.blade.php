@extends('pelanggan.layouts.app')

@section('title', 'Daftar Pesanan')

@section('content')
    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Daftar Pesanan</h2>

            <!-- Order Tabs -->
            @php
                $statusAktif = request()->get('status');
                $statuses = [
                    '' => 'Semua',
                    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                    'belum_dibayar' => 'Belum Dibayar',
                    'dalam_proses' => 'Dalam Proses',
                    'dikirim' => 'Dikirim',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                ];
            @endphp

            <div class="flex space-x-1 mb-6 border-b border-gray-200 overflow-x-auto">
                @foreach ($statuses as $key => $label)
                    <a href="{{ route('transaksi.index', ['status' => $key]) }}"
                        class="px-4 py-2.5 text-sm font-medium relative whitespace-nowrap
                       {{ $statusAktif === $key ? 'text-primary after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-primary' : 'text-gray-500 hover:text-gray-700 group' }}">
                        {{ $label }}
                        @if ($statusAktif !== $key)
                            <div class="absolute bottom-0 left-0 w-full h-0.5 bg-transparent group-hover:bg-gray-200"></div>
                        @endif
                    </a>
                @endforeach
            </div>

            <!-- Order List -->
            <div class="space-y-4">
                @forelse ($transaksi as $item)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start">
                            @if ($item->detailTransaksi->isNotEmpty())
                                @php
                                    $firstDetail = $item->detailTransaksi->first();
                                @endphp
                                @if ($firstDetail->detailBarang && $firstDetail->detailBarang->barang && $firstDetail->detailBarang->barang->gambar)
                                    <img src="{{ asset('storage/' . $firstDetail->detailBarang->barang->gambar) }}"
                                        alt="Product Image" class="rounded"
                                        style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            @endif
                            <div class="flex-1 ml-4">
                                <h3 class="font-medium text-gray-800">
                                    {{ $item->detailTransaksi[0]->detailBarang->barang->nama_barang ?? '-' }}
                                </h3>
                                <div class="mt-1 text-sm text-gray-600">
                                    <span>Jumlah: {{ $item->detailTransaksi[0]->kuantitas }}</span>
                                    <span class="mx-2">•</span>
                                    {{-- <span>Status: {{ ucfirst(str_replace('_', ' ', $item->status)) }}</span> --}}
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Total</p>
                                <p class="font-medium text-gray-800">Rp {{ number_format($item->total, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <a href="{{ route('customer.orders.show', $item->kode_transaksi) }}"
                                class="bg-secondary text-white text-sm font-medium px-4 py-2 rounded-button hover:bg-primary-dark transition-colors">
                                Lihat Detail
                            </a>
                            @if($item->status == 'selesai')
                            <a href="{{ route('ulasan.create', $item->kode_transaksi) }}"
                                class="bg-primary text-white text-sm font-medium px-4 py-2 rounded-button hover:bg-primary-dark transition-colors">
                                Beri Ulasan
                            </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500">Tidak ada pesanan ditemukan.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
