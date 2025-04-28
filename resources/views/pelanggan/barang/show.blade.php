@extends('pelanggan.layouts.app')
@section('title', 'Detail Produk')
@section('content')
{{-- {{dd($barang)}} --}}
    <main class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mt-20">
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 px-8 lg:px-20 py-8">
            <div class="glide">
                <!-- Main product image -->
                <img src="{{ asset('storage/' . $barang->gambar) }}" alt="{{ $barang->nama_barang }}"
                    class="w-full h-auto object-cover rounded-lg" id="mainImage">

                <!-- Thumbnail images if available -->
                {{-- @if ($barang->gambarBarang->count() > 0)
                    <div class="mt-4 grid grid-cols-4 gap-2">
                        <img src="{{ asset('storage/' . $barang->gambar_utama) }}" alt="{{ $barang->nama_barang }}"
                            class="w-full h-20 object-cover rounded-lg cursor-pointer border-2 border-custom thumbnail-image active"
                            data-src="{{ asset('storage/' . $barang->gambar_utama) }}">

                        @foreach ($barang->gambarBarang as $gambar)
                            <img src="{{ asset('storage/' . $gambar->file_gambar) }}" alt="{{ $barang->nama_barang }}"
                                class="w-full h-20 object-cover rounded-lg cursor-pointer border-2 border-gray-200 thumbnail-image"
                                data-src="{{ asset('storage/' . $gambar->file_gambar) }}">
                        @endforeach
                    </div>
                @endif --}}
            </div>

            <div class="mt-10 lg:mt-0">
                <h1 class="text-3xl font-bold text-gray-900">{{ $barang->nama_barang }}</h1>
                <p class="mt-2 text-sm text-gray-500">{{ $barang->tipe->brand->nama_brand }} {{ $barang->tipe->nama_tipe }}
                </p>

                <div class="mt-6">
                    <!-- Harga reseller dan pelanggan biasa berbeda -->
                    @auth('pelanggan')
                        @if (auth()->guard('pelanggan')->user()->role == 'pelanggan')
                            <span class="text-sm text-gray-500 line-through">Rp
                                {{ number_format($barang->harga_normal * 1.1, 0, ',', '.') }}</span>
                            <h2 class="text-2xl font-bold text-custom">Rp
                                {{ number_format($barang->harga_normal * 1.1  - $barang->tipe->potongan_harga , 0, ',', '.') }}
                            </h2>
                        @else
                            <span class="text-sm text-gray-500 line-through">Rp
                                {{ number_format($barang->harga_normal, 0, ',', '.') }}</span>
                            <h2 class="text-2xl font-bold text-custom">Rp
                                {{ number_format($barang->harga_normal  - $barang->tipe->potongan_harga , 0, ',', '.') }}
                            </h2>
                        @endif
                    @else
                        <!-- Harga untuk pengunjung (belum login) -->
                        <span class="text-sm text-gray-500 line-through">Rp
                            {{ number_format($barang->harga_normal * 1.1, 0, ',', '.') }}</span>
                        <h2 class="text-2xl font-bold text-custom">Rp
                            {{ number_format($barang->harga_normal * 1.1  - $barang->tipe->potongan_harga , 0, ',', '.') }}
                        </h2>
                    @endauth

                    <div class="mt-4 flex items-center">
                        <span class="text-sm text-gray-500" id="stokInfo">Stok: -</span>
                    </div>
                </div>

                <form id="formAddToCart" action="{{ route('keranjang.tambah') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kode_detail" id="kodeDetail" required>

                    <div class="mt-8">
                        <h3 class="text-sm font-medium text-gray-900">Ukuran</h3>
                        <div class="grid grid-cols-4 gap-4 mt-4" id="ukuranContainer">
                            @foreach ($ukuranTersedia as $ukuran)
                                <button type="button" data-ukuran="{{ $ukuran }}"
                                    class="ukuran-btn !rounded-button px-4 py-2 border border-gray-300 text-sm font-medium hover:border-custom">
                                    {{ $ukuran }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-sm font-medium text-gray-900">Warna</h3>
                        <div class="flex space-x-4 mt-4" id="warnaContainer">
                            @foreach ($warnaTersedia as $warna)
                                <button type="button" data-warna-id="{{ $warna->kode_warna }}"
                                    class="warna-btn w-8 h-8 rounded-full border-2 border-white"
                                    style="background-color: #{{ $warna->kode_hex }}"></button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-medium text-gray-900">Jumlah</h3>
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button type="button" id="btnMinus"
                                    class="!rounded-button px-3 py-1 text-gray-600 hover:text-custom">-</button>
                                <input type="number" name="jumlah" id="jumlah" class="w-12 text-center border-0"
                                    value="1" min="1">
                                <button type="button" id="btnPlus"
                                    class="!rounded-button px-3 py-1 text-gray-600 hover:text-custom">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" id="btnBeliSekarang" formaction="{{ route('checkout.beli-langsung') }}"
                            class="!rounded-button w-full bg-custom text-white px-6 py-3 text-base font-medium hover:bg-custom/90 disabled:bg-gray-400 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-credit-card mr-2"></i>
                            Beli Sekarang
                        </button>
                        <button type="submit" id="btnKeranjang"
                            class="!rounded-button w-full mt-4 border border-custom text-custom px-6 py-3 text-base font-medium hover:bg-custom/10 disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Tambah ke Keranjang
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-16 col-span-2">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button id="tabDeskripsi"
                            class="border-custom text-custom hover:text-custom/90 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Deskripsi
                        </button>
                        <button id="tabSpesifikasi"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Spesifikasi
                        </button>
                        <button id="tabUlasan"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Ulasan
                        </button>
                    </nav>
                </div>

                <div id="contentDeskripsi" class="mt-6 prose prose-sm max-w-none">
                    <h3>Deskripsi Produk</h3>
                    {!! $barang->deskripsi !!}
                </div>

                <div id="contentSpesifikasi" class="mt-6 prose prose-sm max-w-none hidden">
                    <h3>Spesifikasi</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-900">Berat</td>
                                <td class="py-2 text-sm text-gray-500">{{ $barang->berat }} gram</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-900">Brand</td>
                                <td class="py-2 text-sm text-gray-500">{{ $barang->tipe->brand->nama_brand }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-900">Tipe</td>
                                <td class="py-2 text-sm text-gray-500">{{ $barang->tipe->nama_tipe }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-900">Warna Tersedia</td>
                                <td class="py-2 text-sm text-gray-500">
                                    @foreach ($warnaTersedia as $index => $warna)
                                        {{ $warna->warna }}{{ $index < count($warnaTersedia) - 1 ? ', ' : '' }}
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-900">Ukuran Tersedia</td>
                                <td class="py-2 text-sm text-gray-500">
                                    @foreach ($ukuranTersedia as $index => $ukuran)
                                        {{ $ukuran }}{{ $index < count($ukuranTersedia) - 1 ? ', ' : '' }}
                                    @endforeach
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="contentUlasan" class="mt-6 prose prose-sm max-w-none hidden">
                    <h3>Ulasan Produk</h3>
                    <p>Belum ada ulasan untuk produk ini.</p>
                </div>
            </div>
        </div>

        @if (!empty($produkTerkait) && $produkTerkait->count())
            <div class="mt-16 px-8 lg:px-20">
                <h2 class="text-2xl font-bold text-gray-900">Produk Terkait</h2>
                <div class="grid grid-cols-4 gap-8">
                    @foreach ($produkTerkait as $item)
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
                                    <p class="text-lg font-bold text-custom">{{ $item->harga_diskon }}</p>
                                </div>
                                <a href="{{ route('pelanggan.detailBarang', $item->kode_barang) }}"
                                    class="bg-custom text-white px-4 py-2 !rounded-button hover:bg-custom/90">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Beli
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data detail barang
            const detailBarang = @json($detailByUkuranWarna);
            let selectedUkuran = null;
            let selectedWarna = null;
            let maxStok = 0;

            // Handle thumbnail click
            const thumbnails = document.querySelectorAll('.thumbnail-image');
            const mainImage = document.getElementById('mainImage');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    mainImage.src = this.dataset.src;
                    thumbnails.forEach(t => t.classList.remove('border-custom', 'active'));
                    this.classList.add('border-custom', 'active');
                });
            });

            // Handle ukuran selection
            const ukuranButtons = document.querySelectorAll('.ukuran-btn');
            ukuranButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    ukuranButtons.forEach(b => {
                        b.classList.remove('bg-custom/10', 'border-custom', 'text-custom');
                        b.classList.add('border-gray-300');
                    });

                    this.classList.add('bg-custom/10', 'border-custom', 'text-custom');
                    this.classList.remove('border-gray-300');

                    // Konversi ke string dengan satu desimal
                    selectedUkuran = parseFloat(this.dataset.ukuran).toFixed(1);
                    updateDetailBarang();
                });
            });

            // Handle warna selection
            const warnaButtons = document.querySelectorAll('.warna-btn');
            warnaButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    warnaButtons.forEach(b => b.classList.remove('ring-2', 'ring-black'));
                    this.classList.add('ring-2', 'ring-black');

                    selectedWarna = this.dataset.warnaId;
                    updateDetailBarang();
                });
            });

            // Update stok & kode_detail berdasarkan ukuran & warna yang dipilih
            function updateDetailBarang() {
                const btnBeliSekarang = document.getElementById('btnBeliSekarang');
                const btnKeranjang = document.getElementById('btnKeranjang');
                const stokInfo = document.getElementById('stokInfo');
                const kodeDetailInput = document.getElementById('kodeDetail');
                const jumlahInput = document.getElementById('jumlah');
// Reset buy buttons
                btnBeliSekarang.disabled = true;
                btnKeranjang.disabled = true;

                if (selectedUkuran && selectedWarna) {
                    // Debugging detail barang
                    console.log('Detail Ukuran:', detailBarang[selectedUkuran]);
                    console.log('Detail Kombinasi:', 
                        detailBarang[selectedUkuran] ? 
                        detailBarang[selectedUkuran][selectedWarna] : 
                        'Tidak ada'
                    );

                    // Cek kombinasi dengan debugging detail
                    if (detailBarang[selectedUkuran] && detailBarang[selectedUkuran][selectedWarna]) {
                        const detail = detailBarang[selectedUkuran][selectedWarna];
                        console.log('Detail Produk:', detail);
                        
                        const maxStok = detail.stok;
                        console.log('Max Stok:', maxStok);

                        if (maxStok > 0) {
                            // Update stok information
                            stokInfo.textContent = `Stok: ${maxStok}`;
                            kodeDetailInput.value = detail.kode_detail;

                            // Reset jumlah input
                            jumlahInput.value = 1;
                            jumlahInput.setAttribute('max', maxStok);

                            // Enable buy buttons
                            btnBeliSekarang.disabled = false;
                            btnKeranjang.disabled = false;
                        } else {
                            stokInfo.textContent = "Stok habis";
                            kodeDetailInput.value = "";
                        }
                    } else {
                        // No stock for this combination
                        stokInfo.textContent = "Kombinasi tidak tersedia";
                        kodeDetailInput.value = "";
                    }
                } else {
                    // No selection made
                    stokInfo.textContent = "Pilih ukuran dan warna";
                    kodeDetailInput.value = "";
                }
            }

            // Handle jumlah (+/-)
            const btnMinus = document.getElementById('btnMinus');
            const btnPlus = document.getElementById('btnPlus');
            const jumlahInput = document.getElementById('jumlah');

            btnMinus.addEventListener('click', function() {
                const currentValue = parseInt(jumlahInput.value);
                if (currentValue > 1) {
                    jumlahInput.value = currentValue - 1;
                }
            });

            btnPlus.addEventListener('click', function() {
                const currentValue = parseInt(jumlahInput.value);
                if (maxStok > 0 && currentValue < maxStok) {
                    jumlahInput.value = currentValue + 1;
                }
            });

            // Handle form submission
            const formAddToCart = document.getElementById('formAddToCart');
            formAddToCart.addEventListener('submit', function(e) {
                if (!selectedUkuran || !selectedWarna) {
                    e.preventDefault();
                    alert('Silakan pilih ukuran dan warna terlebih dahulu.');
                }
            });

            // Tab navigation
            const tabDeskripsi = document.getElementById('tabDeskripsi');
            const tabSpesifikasi = document.getElementById('tabSpesifikasi');
            const tabUlasan = document.getElementById('tabUlasan');

            const contentDeskripsi = document.getElementById('contentDeskripsi');
            const contentSpesifikasi = document.getElementById('contentSpesifikasi');
            const contentUlasan = document.getElementById('contentUlasan');

            tabDeskripsi.addEventListener('click', function() {
                setActiveTab(tabDeskripsi, contentDeskripsi);
            });

            tabSpesifikasi.addEventListener('click', function() {
                setActiveTab(tabSpesifikasi, contentSpesifikasi);
            });

            tabUlasan.addEventListener('click', function() {
                setActiveTab(tabUlasan, contentUlasan);
            });

            function setActiveTab(activeTab, activeContent) {
                // Hide all content
                contentDeskripsi.classList.add('hidden');
                contentSpesifikasi.classList.add('hidden');
                contentUlasan.classList.add('hidden');

                // Remove active class from all tabs
                tabDeskripsi.classList.remove('border-custom', 'text-custom');
                tabDeskripsi.classList.add('border-transparent', 'text-gray-500');

                tabSpesifikasi.classList.remove('border-custom', 'text-custom');
                tabSpesifikasi.classList.add('border-transparent', 'text-gray-500');

                tabUlasan.classList.remove('border-custom', 'text-custom');
                tabUlasan.classList.add('border-transparent', 'text-gray-500');

                // Add active class to selected tab
                activeTab.classList.remove('border-transparent', 'text-gray-500');
                activeTab.classList.add('border-custom', 'text-custom');

                // Show active content
                activeContent.classList.remove('hidden');
            }
        });
    </script>
@endpush
