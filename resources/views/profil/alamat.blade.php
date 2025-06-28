@extends('pelanggan.layouts.app')
@section('content')
    <div class="flex min-h-screen">
        @include('profil.sidebar')
        <main class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Alamat Saya</h1>
                    <a href="{{ route('alamat.create') }}" class="ml-auto bg-primary text-white px-4 py-2 rounded-button flex items-center whitespace-nowrap">
                        <i class="ri-add-line mr-2"></i> Tambah Alamat Baru
                    </a>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="space-y-4">
                    @foreach ($alamat as $item)
                        <div class="bg-white rounded-lg shadow-sm p-6 {{ $item->is_utama ? 'border-2 border-green-500' : '' }}">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-gray-800">
                                        {{ $item->nama_penerima }}
                                        @if ($item->is_utama)
                                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded ml-2">Utama</span>
                                        @endif
                                    </h3>
                                    <p class="text-gray-500">({{ $item->no_hp_penerima }})</p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="flex space-x-2 mb-2">
                                        <a href="{{ route('alamat.edit', $item) }}" class="text-blue-500 hover:text-blue-600">
                                            Edit
                                        </a>
                                        <form action="{{ route('alamat.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus alamat?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>

                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio"
                                               name="primary_address"
                                               value="{{ $item->id_alamat }}"
                                               class="hidden"
                                               {{ $item->is_utama ? 'checked' : '' }}>
                                    
                                        <div class="w-4 h-4 border border-gray-300 rounded-full mr-2 flex items-center justify-center">
                                            @if ($item->is_utama)
                                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                            @endif
                                        </div>
                                    
                                        <span class="text-sm text-gray-600 {{ $item->is_utama ? 'font-semibold' : '' }}">
                                            {{ $item->is_utama ? 'Utama' : 'Jadikan Alamat Utama' }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <p class="text-gray-700">{{ $item->alamat_lengkap }}</p>
                            <p class="text-gray-700">{{ $item->kecamatan }}, {{ $item->kota }}, {{ $item->provinsi }} {{ $item->kode_pos }}</p>
                            <p class="text-sm text-gray-500">Jenis: {{ ucfirst($item->jenis) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>
@endsection