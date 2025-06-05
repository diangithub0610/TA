@extends('pelanggan.layouts.app')
@section('title', 'profil')
@section('content')
    <div class="flex min-h-screen">
        @include('profil.sidebar')
        <main class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Alamat Saya</h1>
                    <button class="bg-primary text-white px-4 py-2 rounded-button flex items-center whitespace-nowrap">
                        <i class="ri-add-line mr-2"></i> Tambah Alamat Baru
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <!-- Address 1 -->
                    @if ($alamat->count() > 0)
                    @foreach ($alamat as $adr)
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ $adr->nama_penerima }}</h3>
                                    <p class="text-gray-500">({{ $adr->no_hp_penerima }})</p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <button class="text-blue-500 hover:text-blue-600 mb-2">Ubah</button>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="primary_address" class="hidden" checked>
                                        <div
                                            class="w-4 h-4 border border-gray-300 rounded-full mr-2 flex items-center justify-center">
                                            <div class="w-2 h-2 bg-primary rounded-full"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">Atur sebagai utama</span>
                                    </label>
                                </div>
                            </div>
                            <p class="text-gray-700">{{ $adr->nama_alamat }} </p>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 mb-4">Anda belum memiliki alamat pengiriman</p>
                            <button type="button" id="btnTambahAlamat"
                                class="btn-tambah-alamat bg-custom text-white py-2 px-4 rounded-lg hover:bg-custom/90">
                                Tambah Alamat Baru
                            </button>
                        </div>
                    @endif

                


                </div>

        </main>
    </div>
@endsection
