@extends('pelanggan.layouts.app')
@section('title', 'Beranda')
@section('content')
    <!-- Hero Section -->
    <div class="carousel relative w-full">
        <div class="carousel-inner">
            <div class="carousel-item h-[400px] md:h-[500px] relative">
                <div style="background-image: url('https://readdy.ai/api/search-image?query=modern%2520sneakers%2520store%2520with%2520stylish%2520display%2520of%2520premium%2520athletic%2520shoes%2520on%2520minimalist%2520shelves%252C%2520soft%2520lighting%252C%2520clean%2520white%2520background%2520with%2520subtle%2520orange%2520accents%252C%2520professional%2520product%2520photography&width=1200&height=500&seq=1&orientation=landscape'); background-size: cover; background-position: center;"
                    class="w-full h-full">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                        <div class="container mx-auto px-4">
                            <div class="max-w-lg text-white">
                                <h1 class="text-4xl md:text-5xl font-bold mb-4">Koleksi Terbaru 2025</h1>
                                <p class="text-lg mb-6">Temukan sepatu terbaik untuk gaya dan kenyamanan Anda. Diskon hingga
                                    50% untuk pembelian pertama.</p>
                                <button
                                    class="bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-primary/90 whitespace-nowrap">Belanja
                                    Sekarang</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produk Terlaris Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-8">Produk Terlaris</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($barang as $item)
                    <div
                        class="bg-white rounded shadow-sm border border-gray-100 overflow-hidden transition-transform hover:shadow-md flex flex-col h-full">
                        <div class="relative h-64 overflow-hidden">
                            <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_barang }}"
                                class="w-full h-full object-cover object-top">
                            @if(function_exists('isReseller') && isReseller())
                                <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded">Harga Reseller</div>
                            @else
                                <div class="absolute top-2 right-2 bg-primary text-white text-xs px-2 py-1 rounded">Terlaris</div>
                            @endif
                            </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="font-bold text-gray-800 mb-1 min-h-[2.5rem] line-clamp-2">{{ $item->nama_barang }}</h3>
                            <p class="text-sm text-gray-500 mb-1">Ukuran: 40-45</p>
                            <div class="flex star-rating mb-2">
                                <i class="ri-star-fill text-yellow-400"></i>
                                <i class="ri-star-fill text-yellow-400"></i>
                                <i class="ri-star-fill text-yellow-400"></i>
                                <i class="ri-star-fill text-yellow-400"></i>
                                <i class="ri-star-half-fill text-yellow-400"></i>
                                <span class="text-xs text-gray-500 ml-1">(128)</span>
                            </div>
                            <div class="flex items-center justify-between mt-auto">
                                <div class="flex flex-col">
                                    @if(function_exists('isReseller') && isReseller())
                                        <p class="text-sm text-gray-500 line-through">{{ $item->harganormal }}</p>
                                        <p class="text-lg font-bold text-green-600">{{ $item->harga_termurah }}</p>
                                    @elseif(function_exists('isPelanggan') && isPelanggan())
                                        <p class="text-lg font-bold text-primary">{{ $item->harga_termurah }}</p>
                                    @else
                                        <p class="text-lg font-bold text-primary">{{ $item->harga_termurah }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('pelanggan.detailBarang', $item->kode_barang) }}"
                                    class="bg-primary text-white px-3 py-2 rounded-button flex items-center whitespace-nowrap hover:bg-primary-dark transition-colors">
                                    <i class="ri-shopping-cart-2-line mr-1"></i> Beli
                                </a>
                            </div>
                        </div>
                </div> 
                @endforeach
            </div>
        </div>
    </section>

    <!-- Update untuk section Brand Showcase di beranda -->
    {{-- <section class="py-10 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-8">Brand Populer</h2>

            <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-6">
                @foreach ($brands as $brand)
                <a href="{{ route('pelanggan.produk.byBrand', $brand->kode_brand) }}"
                    class="bg-white rounded shadow-sm p-4 flex items-center justify-center h-24 hover:shadow-md transition-shadow group">
                    <div class="w-10 h-10 flex items-center justify-center mr-2">
                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->nama_brand }}"
                            class="h-12 object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <span class="font-medium group-hover:text-primary transition-colors">{{ $brand->nama_brand }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </section> --}}

    <!-- Product Listing Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Produk Terbaru</h2>
                <div class="flex space-x-2">
                    <button
                        class="px-4 py-2 border border-gray-200 rounded-button text-sm hover:bg-gray-50 whitespace-nowrap">Filter</button>
                    <select class="px-4 py-2 border border-gray-200 rounded-button text-sm pr-8 appearance-none bg-white">
                        <option>Urutkan: Terbaru</option>
                        <option>Harga: Rendah ke Tinggi</option>
                        <option>Harga: Tinggi ke Rendah</option>
                        <option>Popularitas</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Product 1 -->
                @foreach ($barang as $item)
                    <div class="bg-white rounded shadow-sm border border-gray-100 overflow-hidden transition-transform hover:shadow-md">
                        <div class="h-64 overflow-hidden">
                            <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_barang }}"
                                class="w-full h-full object-cover object-top">
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800 mb-1">{{ $item->nama_barang }}</h3>
                            <p class="text-sm text-gray-500 mb-1">Ukuran: 38-45</p>
                            <div class="flex star-rating mb-2">
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-fill"></i>
                                <i class="ri-star-line"></i>
                                <span class="text-xs text-gray-500 ml-1">(87)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500 line-through">{{ $item->formatted_harga }}</p>
                                    <p class="text-lg font-bold text-primary">{{ $item->harga_diskon }}</p>
                                </div>
                                <button class="bg-primary text-white px-3 py-2 rounded-button flex items-center whitespace-nowrap">
                                    <i class="ri-shopping-cart-2-line mr-1"></i> Beli
                                </button>
                                </div>
                                </div>
                                </div>
                @endforeach
            </div>

            <div class="mt-10 flex justify-center">
                <button
                    class="bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-button font-medium hover:bg-gray-50 whitespace-nowrap">Lihat
                    Lebih Banyak</button>
            </div>
        </div>
    </section>


    <!-- Update untuk section Brand Ternama di beranda -->
    <section class="py-16 bg-gray-50 px-8 lg:px-20">
        <div class="max-w-8xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Brand Ternama</h2>
            <div class="flex justify-between items-center">
                @foreach ($brands as $brand)
                    <a href="{{ route('pelanggan.produk.byBrand', $brand->kode_brand) }}"
                        class="hover:scale-105 transition-transform">
                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->nama_brand }}"
                            class="h-12 object-contain opacity-70 hover:opacity-100 transition-opacity">
                    </a>
                @endforeach
            </div>
        </div>
    </section>

@endsection