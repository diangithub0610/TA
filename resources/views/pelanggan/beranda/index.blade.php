@extends('pelanggan.layouts.app')
@section('title', 'Beranda')
@section('content')
    <main class="mt-20">
        <section class="relative">
            <div class="glide">
                <div class="glide__track" data-glide-el="track">
                    <ul class="glide__slides">
                        <li class="glide__slide">
                            <div class="relative h-[500px]"> <img
                                    src="https://creatie.ai/ai/api/search-image?query=A modern and stylish collection of premium sneakers and sports shoes displayed elegantly against a clean, minimalist background with soft lighting and subtle shadows, creating an atmosphere of luxury and quality&width=1440&height=500&orientation=landscape&flag=aa7c78d3-099b-40d4-bd90-d0655a202116"
                                    alt="Koleksi Terbaru" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center">
                                    <div class="max-w-8xl mx-auto px-4">
                                        <h1 class="text-5xl font-bold text-white mb-4">Koleksi Terbaru 2024</h1>
                                        <p class="text-xl text-white mb-8">Temukan gaya terbaru untuk setiap langkahmu
                                        </p>
                                        <button class="bg-custom text-white px-8 py-3 !rounded-button hover:bg-custom/90">
                                            Belanja Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="glide__bullets" data-glide-el="controls[nav]">
                    <button class="glide__bullet" data-glide-dir="=0"></button>
                    <button class="glide__bullet" data-glide-dir="=1"></button>
                    <button class="glide__bullet" data-glide-dir="=2"></button>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white px-8 lg:px-20">
            <div class="max-w-8xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Kategori Populer</h2>
                <div class="grid grid-cols-5 gap-6"> <a href="#" class="group">
                        <div class="relative rounded-lg overflow-hidden">
                            <img src="https://creatie.ai/ai/api/search-image?query=A sleek and modern sneaker displayed on a minimalist white platform with soft shadows, showcasing its design details and premium materials&width=300&height=300&orientation=squarish&flag=6bc8c562-4de0-45bd-902f-83284c5ec75c"
                                alt="Sneakers"
                                class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="text-xl font-semibold text-white">Sneakers</span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="group">
                        <div class="relative rounded-lg overflow-hidden">
                            <img src="https://creatie.ai/ai/api/search-image?query=A professional running shoe with dynamic design elements, photographed on a clean white background with emphasis on its performance features&width=300&height=300&orientation=squarish&flag=0e67b03d-52d4-4bc9-b27a-b0728308ef9d"
                                alt="Running"
                                class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="text-xl font-semibold text-white">Running</span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="group">
                        <div class="relative rounded-lg overflow-hidden">
                            <img src="https://creatie.ai/ai/api/search-image?query=A casual lifestyle shoe with comfortable design, displayed on a minimalist white platform showing its versatile everyday appeal&width=300&height=300&orientation=squarish&flag=034da9ea-5d6f-413c-a39e-e75a7a278872"
                                alt="Casual"
                                class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="text-xl font-semibold text-white">Casual</span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="group">
                        <div class="relative rounded-lg overflow-hidden">
                            <img src="https://creatie.ai/ai/api/search-image?query=A high-performance sports shoe with technical features, photographed on a clean white background highlighting its athletic capabilities&width=300&height=300&orientation=squarish&flag=fdf2eb4f-994a-45b2-a25c-873cfa4013f1"
                                alt="Sport"
                                class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="text-xl font-semibold text-white">Sport</span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="group">
                        <div class="relative rounded-lg overflow-hidden">
                            <img src="https://creatie.ai/ai/api/search-image?query=An elegant formal leather shoe with classic design, displayed on a minimalist white platform emphasizing its sophisticated appearance&width=300&height=300&orientation=squarish&flag=085ae2b9-fa7d-48a2-b8f3-adcc886cff11"
                                alt="Formal"
                                class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="text-xl font-semibold text-white">Formal</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="py-16 bg-gray-50 px-8 lg:px-20">
            <div class="max-w-8xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Brand Ternama</h2>
                <div class="flex justify-between items-center">
                    @foreach ($brands as $brand)
                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->nama_brand }}"
                            class="h-12 object-contain">
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-16 bg-white px-8 lg:px-20">
            <div class="max-w-8xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Produk Unggulan</h2>
                <div class="grid grid-cols-4 gap-8">
                    @foreach ($barang as $item)
                        <div class="group">
                            <div class="relative rounded-lg overflow-hidden mb-4">
                                <img src="{{ asset('storage/' . $item->gambar) }}" alt="Sepatu 1"
                                    class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                                <button
                                    class="absolute top-4 right-4 bg-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="far fa-heart text-custom"></i>
                                </button>
                            </div>
                            <h3 class="font-semibold mb-2">{{ $item->nama_barang }}</h3>
                            <div class="flex items-center mb-2">
                                <div class="flex text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-sm text-gray-500 ml-2">(120)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm text-gray-500 line-through">{{ $item->formatted_harga }}</span>
                                    <p class="text-lg font-bold text-custom">{{$item->harga_diskon }}</p>
                                </div>
                                <a href="{{ route('pelanggan.detailBarang' , $item->kode_barang )}}" class="bg-custom text-white px-4 py-2 !rounded-button hover:bg-custom/90">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Beli
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-16 bg-gray-50 px-8 lg:px-20">
            <div class="max-w-8xl mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="relative rounded-lg overflow-hidden"> <img
                            src="https://creatie.ai/ai/api/search-image?query=Stylish athletic shoes arranged in a promotional setting with dynamic lighting and minimal background, perfect for a sale campaign&width=600&height=300&orientation=landscape&flag=c74c3c82-53fd-498c-b6c4-1130019c2c79"
                            alt="Special Deals" class="w-full h-[300px] object-cover">
                        <div class="absolute inset-0 bg-black/40 flex items-center">
                            <div class="p-8">
                                <h3 class="text-3xl font-bold text-white mb-4">Special Deals</h3>
                                <p class="text-white mb-6">Dapatkan diskon hingga 50% untuk produk pilihan</p>
                                <button class="bg-white text-custom px-6 py-2 !rounded-button hover:bg-gray-100"> Lihat
                                    Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="relative rounded-lg overflow-hidden">
                        <img src="https://creatie.ai/ai/api/search-image?query=Limited edition sneakers displayed in an exclusive setting with dramatic lighting and clean background, emphasizing their unique design&width=600&height=300&orientation=landscape&flag=fd7efcff-e0ef-49bd-bcc9-5b199ecd426c"
                            alt="New Collection" class="w-full h-[300px] object-cover">
                        <div class="absolute inset-0 bg-black/40 flex items-center">
                            <div class="p-8">
                                <h3 class="text-3xl font-bold text-white mb-4">Koleksi Terbaru</h3>
                                <p class="text-white mb-6">Temukan koleksi terbaru dari brand favorit</p>
                                <button class="bg-white text-custom px-6 py-2 !rounded-button hover:bg-gray-100"> Lihat
                                    Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-gray-50">
            <div class="max-w-8xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Keunggulan Kami</h2>
                <div class="grid grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4"> <i
                                class="fas fa-shield-alt text-3xl text-custom"></i>
                        </div>
                        <h3 class="font-semibold mb-2">100% Original</h3>
                        <p class="text-gray-600">Jaminan keaslian produk dari brand resmi</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4"> <i
                                class="fas fa-truck text-3xl text-custom"></i>
                        </div>
                        <h3 class="font-semibold mb-2">Gratis Ongkir</h3>
                        <p class="text-gray-600">Pengiriman gratis ke seluruh Indonesia</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4"> <i
                                class="fas fa-undo text-3xl text-custom"></i>
                        </div>
                        <h3 class="font-semibold mb-2">14 Hari Pengembalian</h3>
                        <p class="text-gray-600">Kebijakan pengembalian yang mudah</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4"> <i
                                class="fas fa-lock text-3xl text-custom"></i>
                        </div>
                        <h3 class="font-semibold mb-2">Pembayaran Aman</h3>
                        <p class="text-gray-600">Transaksi aman dan terpercaya</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
