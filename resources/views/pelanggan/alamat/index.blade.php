@extends('pelanggan.layouts.app')
@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Alamat Saya</h1>
        <a href="{{ route('alamat.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
            Tambah Alamat Baru
        </a>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4">
            @foreach ($alamat as $item)
                <div class="bg-white shadow-md rounded-lg p-4 {{ $item->is_utama ? 'border-2 border-green-500' : '' }}">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-lg font-semibold">
                            {{ $item->nama_penerima }}
                            @if ($item->is_utama)
                                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded ml-2">Utama</span>
                            @endif
                        </h2>
                        <div class="flex space-x-2">
                            <a href="{{ route('alamat.edit', $item) }}" class="text-blue-500 hover:text-blue-700">
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
                    <p>{{ $item->alamat_lengkap }}</p>
                    <p>{{ $item->kecamatan }}, {{ $item->kota }}, {{ $item->provinsi }} {{ $item->kode_pos }}</p>
                    <p>{{ $item->no_hp_penerima }}</p>
                    <p class="text-sm text-gray-500">Jenis: {{ ucfirst($item->jenis) }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
