@extends('pelanggan.layouts.app')
@section('title', 'Produk')
@section('content')
    {{-- <!-- Breadcrumb -->
    <div class="bg-gray-50 py-4">
        <div class="container mx-auto px-4">
            <nav class="text-sm">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('pelanggan.beranda') }}" class="text-gray-500 hover:text-primary">Beranda</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-800 font-medium">
                        @if (request('brand'))
                            {{ $selectedBrand->nama_brand ?? 'Brand' }}
                        @else
                            Semua Produk
                        @endif
                    </li>
                </ol>
            </nav>
        </div>
    </div> --}}

    <!-- Page Header -->
    <div class="bg-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        @if (request('brand'))
                            Produk {{ $selectedBrand->nama_brand ?? 'Brand' }}
                        @else
                            Semua Produk
                        @endif
                    </h1>
                    <p class="text-gray-600">Menampilkan {{ $barang->count() }} dari {{ $totalBarang }} produk</p>
                </div>
                @if (request('brand'))
                    <a href="{{ route('pelanggan.barang') }}"
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-button hover:bg-gray-200 flex items-center">
                        <i class="ri-close-line mr-1"></i> Hapus Filter Brand
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter & Sort Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Filter Section -->
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Brand Filter -->
                    <div class="relative">
                        <select id="brandFilter"
                            class="px-4 py-2 border border-gray-200 rounded-button text-sm pr-8 appearance-none bg-white min-w-[150px]">
                            <option value="">Semua Brand</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->nama_brand }}
                                </option>
                            @endforeach
                        </select>
                        <i
                            class="ri-arrow-down-s-line absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="flex items-center gap-2">
                        <input type="number" id="minPrice" placeholder="Harga Min"
                            class="px-3 py-2 border border-gray-200 rounded-button text-sm w-24"
                            value="{{ request('min_price') }}">
                        <span class="text-gray-400">-</span>
                        <input type="number" id="maxPrice" placeholder="Harga Max"
                            class="px-3 py-2 border border-gray-200 rounded-button text-sm w-24"
                            value="{{ request('max_price') }}">
                        <button onclick="applyPriceFilter()"
                            class="bg-primary text-white px-4 py-2 rounded-button text-sm hover:bg-primary/90">
                            Filter
                        </button>
                    </div>

                    <!-- Quick Price Filters -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="setPriceRange(0, 500000)"
                            class="px-3 py-1 text-xs border border-gray-200 rounded-full hover:bg-gray-50 {{ request('min_price') == '0' && request('max_price') == '500000' ? 'bg-primary text-white border-primary' : '' }}">
                            < 500rb </button>
                                <button onclick="setPriceRange(500000, 1000000)"
                                    class="px-3 py-1 text-xs border border-gray-200 rounded-full hover:bg-gray-50 {{ request('min_price') == '500000' && request('max_price') == '1000000' ? 'bg-primary text-white border-primary' : '' }}">
                                    500rb - 1jt
                                </button>
                                <button onclick="setPriceRange(1000000, 2000000)"
                                    class="px-3 py-1 text-xs border border-gray-200 rounded-full hover:bg-gray-50 {{ request('min_price') == '1000000' && request('max_price') == '2000000' ? 'bg-primary text-white border-primary' : '' }}">
                                    1jt - 2jt
                                </button>
                                <button onclick="setPriceRange(2000000, null)"
                                    class="px-3 py-1 text-xs border border-gray-200 rounded-full hover:bg-gray-50 {{ request('min_price') == '2000000' && !request('max_price') ? 'bg-primary text-white border-primary' : '' }}">
                                    > 2jt
                                </button>
                    </div>
                </div>

                <!-- Sort Section -->
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">Urutkan:</span>
                    <select id="sortBy"
                        class="px-4 py-2 border border-gray-200 rounded-button text-sm pr-8 appearance-none bg-white min-w-[180px]">
                        <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="terlaris" {{ request('sort') == 'terlaris' ? 'selected' : '' }}>Terlaris</option>
                        <option value="harga_rendah" {{ request('sort') == 'harga_rendah' ? 'selected' : '' }}>Harga:
                            Rendah ke Tinggi</option>
                        <option value="harga_tinggi" {{ request('sort') == 'harga_tinggi' ? 'selected' : '' }}>Harga:
                            Tinggi ke Rendah</option>
                        <option value="nama_asc" {{ request('sort') == 'nama_asc' ? 'selected' : '' }}>Nama: A-Z</option>
                        <option value="nama_desc" {{ request('sort') == 'nama_desc' ? 'selected' : '' }}>Nama: Z-A</option>
                    </select>
                    <i
                        class="ri-arrow-down-s-line absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if (request()->hasAny(['brand', 'min_price', 'max_price', 'sort']))
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-600">Filter aktif:</span>

                        @if (request('brand'))
                            <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs flex items-center">
                                Brand: {{ $selectedBrand->nama_brand ?? 'Unknown' }}
                                <button onclick="removeFilter('brand')" class="ml-2 hover:text-primary/80">
                                    <i class="ri-close-line"></i>
                                </button>
                            </span>
                        @endif

                        @if (request('min_price') || request('max_price'))
                            <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs flex items-center">
                                Harga:
                                @if (request('min_price'))
                                    Rp {{ number_format(request('min_price'), 0, ',', '.') }}
                                @else
                                    0
                                @endif
                                -
                                @if (request('max_price'))
                                    Rp {{ number_format(request('max_price'), 0, ',', '.') }}
                                @else
                                    ∞
                                @endif
                                <button onclick="removeFilter('price')" class="ml-2 hover:text-primary/80">
                                    <i class="ri-close-line"></i>
                                </button>
                            </span>
                        @endif

                        @if (request('sort') && request('sort') != 'terbaru')
                            <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs flex items-center">
                                Urutan:
                                {{ request('sort') == 'terlaris'
                                    ? 'Terlaris'
                                    : (request('sort') == 'harga_rendah'
                                        ? 'Harga Rendah-Tinggi'
                                        : (request('sort') == 'harga_tinggi'
                                            ? 'Harga Tinggi-Rendah'
                                            : (request('sort') == 'nama_asc'
                                                ? 'Nama A-Z'
                                                : (request('sort') == 'nama_desc'
                                                    ? 'Nama Z-A'
                                                    : 'Terbaru')))) }}
                                <button onclick="removeFilter('sort')" class="ml-2 hover:text-primary/80">
                                    <i class="ri-close-line"></i>
                                </button>
                            </span>
                        @endif

                        <button onclick="clearAllFilters()" class="text-xs text-gray-500 hover:text-gray-700 underline">
                            Hapus Semua Filter
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Products Grid -->
    <section class="py-8 bg-white">
        <div class="container mx-auto px-4">
            @if ($barang->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($barang as $item)
                        <div
                            class="bg-white rounded shadow-sm border border-gray-100 overflow-hidden transition-transform hover:shadow-md">
                            <div class="relative h-64 overflow-hidden">
                                <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_barang }}"
                                    class="w-full h-full object-cover object-top">
                                @if ($item->is_terlaris)
                                    <div class="absolute top-2 right-2 bg-primary text-white text-xs px-2 py-1 rounded">
                                        Terlaris</div>
                                @endif
                                @if ($item->discount_percentage > 0)
                                    <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                        -{{ $item->discount_percentage }}%</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="flex items-center mb-1">
                                    @if ($item->brand)
                                        <span class="text-xs text-gray-400 mr-2">{{ $item->brand->nama_brand }}</span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-gray-800 mb-1">{{ $item->nama_barang }}</h3>
                                <p class="text-sm text-gray-500 mb-1">Ukuran: {{ $item->ukuran_tersedia ?? '40-45' }}</p>
                                <div class="flex star-rating mb-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= floor($item->rating ?? 4.5))
                                            <i class="ri-star-fill"></i>
                                        @elseif($i == ceil($item->rating ?? 4.5) && ($item->rating ?? 4.5) - floor($item->rating ?? 4.5) >= 0.5)
                                            <i class="ri-star-half-fill"></i>
                                        @else
                                            <i class="ri-star-line"></i>
                                        @endif
                                    @endfor
                                    <span
                                        class="text-xs text-gray-500 ml-1">({{ $item->review_count ?? rand(50, 200) }})</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        @if ($item->harga_diskon && $item->harga_diskon < $item->harga)
                                            <p class="text-sm text-gray-500 line-through">{{ $item->formatted_harga }}</p>
                                            <p class="text-lg font-bold text-primary">
                                                Rp {{ number_format($item->harga_diskon, 0, ',', '.') }}
                                            </p>
                                        @else
                                            <p class="text-lg font-bold text-primary">{{ $item->formatted_harga }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('pelanggan.detailBarang', $item->kode_barang) }}"
                                        class="bg-primary text-white px-3 py-2 rounded-button flex items-center whitespace-nowrap hover:bg-primary/90">
                                        <i class="ri-shopping-cart-2-line mr-1"></i> Beli
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($barang->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $barang->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <!-- No Products Found -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="ri-search-line text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Produk Tidak Ditemukan</h3>
                    <p class="text-gray-600 mb-6">Maaf, tidak ada produk yang sesuai dengan filter yang Anda pilih.</p>
                    <button onclick="clearAllFilters()"
                        class="bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-primary/90">
                        Hapus Semua Filter
                    </button>
                </div>
            @endif
        </div>
    </section>

    <!-- JavaScript for Filtering -->
    <script>
        // Brand filter change
        document.getElementById('brandFilter').addEventListener('change', function() {
            const selectedBrand = this.value;
            const currentUrl = new URL(window.location.href);

            if (selectedBrand) {
                currentUrl.searchParams.set('brand', selectedBrand);
            } else {
                currentUrl.searchParams.delete('brand');
            }

            // Reset to first page when filtering
            currentUrl.searchParams.delete('page');
            window.location.href = currentUrl.toString();
        });

        // Sort change
        document.getElementById('sortBy').addEventListener('change', function() {
            const selectedSort = this.value;
            const currentUrl = new URL(window.location.href);

            if (selectedSort && selectedSort !== 'terbaru') {
                currentUrl.searchParams.set('sort', selectedSort);
            } else {
                currentUrl.searchParams.delete('sort');
            }

            // Reset to first page when sorting
            currentUrl.searchParams.delete('page');
            window.location.href = currentUrl.toString();
        });

        // Price filter functions
        function applyPriceFilter() {
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;
            const currentUrl = new URL(window.location.href);

            if (minPrice) {
                currentUrl.searchParams.set('min_price', minPrice);
            } else {
                currentUrl.searchParams.delete('min_price');
            }

            if (maxPrice) {
                currentUrl.searchParams.set('max_price', maxPrice);
            } else {
                currentUrl.searchParams.delete('max_price');
            }

            // Reset to first page when filtering
            currentUrl.searchParams.delete('page');
            window.location.href = currentUrl.toString();
        }

        function setPriceRange(min, max) {
            document.getElementById('minPrice').value = min || '';
            document.getElementById('maxPrice').value = max || '';
            applyPriceFilter();
        }

        // Filter removal functions
        function removeFilter(filterType) {
            const currentUrl = new URL(window.location.href);

            switch (filterType) {
                case 'brand':
                    currentUrl.searchParams.delete('brand');
                    break;
                case 'price':
                    currentUrl.searchParams.delete('min_price');
                    currentUrl.searchParams.delete('max_price');
                    break;
                case 'sort':
                    currentUrl.searchParams.delete('sort');
                    break;
            }

            currentUrl.searchParams.delete('page');
            window.location.href = currentUrl.toString();
        }

        function clearAllFilters() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('brand');
            currentUrl.searchParams.delete('min_price');
            currentUrl.searchParams.delete('max_price');
            currentUrl.searchParams.delete('sort');
            currentUrl.searchParams.delete('page');
            window.location.href = currentUrl.toString();
        }

        // Enter key support for price inputs
        document.getElementById('minPrice').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyPriceFilter();
            }
        });

        document.getElementById('maxPrice').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyPriceFilter();
            }
        });
    </script>
@endsection
