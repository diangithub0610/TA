@extends('pelanggan.layouts.app')
@section('title', 'Checkout')
@section('content')
    <main class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mt-10">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Checkout</h1>

            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-8">
                    <!-- Alamat Pengiriman -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="p-4 border-b flex justify-between items-center">
                            <h2 class="text-lg font-medium">Alamat Pengiriman</h2>
                            <button type="button" id="btnTambahAlamat" class="text-custom hover:text-custom/80">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Alamat Baru
                            </button>
                        </div>

                        <div class="p-4">
                            @if ($alamat->count() > 0)
                                <div class="space-y-4">
                                    @foreach ($alamat as $adr)
                                        <div class="border rounded-lg p-4 {{ $adr->is_utama ? 'border-custom' : 'border-gray-200' }} cursor-pointer alamat-card"
                                            data-id="{{ $adr->id_alamat }}" data-kecamatan-id="{{ $adr->kecamatan_id }}">
                                            <div class="flex justify-between">
                                                <div>
                                                    <div class="flex items-center">
                                                        <h3 class="font-medium">{{ $adr->nama_alamat }}</h3>
                                                        @if ($adr->is_utama)
                                                            <span
                                                                class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">Utama</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm text-gray-600 mt-1">{{ $adr->nama_penerima }}
                                                        ({{ $adr->no_hp_penerima }})
                                                    </p>
                                                    <p class="text-sm text-gray-600 mt-1">
                                                       {{ $adr->nama_alamat}}
                                                    </p>
                                                </div>
                                                <div>
                                                    <div>
                                                        <input type="radio" name="alamat_pengiriman"
                                                            id="alamat-{{ $adr->id_alamat }}"
                                                            value="{{ $adr->id_alamat }}"
                                                            data-kecamatan-id="{{ $adr->kecamatan_id }}"
                                                            {{ $adr->is_utama ? 'checked' : '' }} class="radio-alamat">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            // Menangani klik pada card alamat
                                            const alamatCards = document.querySelectorAll('.alamat-card');

                                            // Fungsi untuk mengatur border pada semua card
                                            function updateBorders(selectedId) {
                                                alamatCards.forEach(function(card) {
                                                    const cardId = card.getAttribute('data-id');
                                                    if (cardId === selectedId) {
                                                        card.classList.remove('border-gray-200');
                                                        card.classList.add('border-custom');
                                                    } else {
                                                        card.classList.remove('border-custom');
                                                        card.classList.add('border-gray-200');
                                                    }
                                                });
                                            }

                                            // Set border awal berdasarkan radio button yang sudah checked
                                            const checkedRadio = document.querySelector('input[name="alamat_pengiriman"]:checked');
                                            if (checkedRadio) {
                                                updateBorders(checkedRadio.value);
                                            }

                                            // Event listener untuk klik pada card
                                            alamatCards.forEach(function(card) {
                                                card.addEventListener('click', function() {
                                                    const alamatId = this.getAttribute('data-id');
                                                    const radioBtn = document.getElementById('alamat-' + alamatId);

                                                    // Mengecek semua radio button terkait alamat
                                                    const allRadios = document.querySelectorAll('input[name="alamat_pengiriman"]');
                                                    allRadios.forEach(function(radio) {
                                                        radio.checked = false;
                                                    });

                                                    // Pilih radio button yang sesuai
                                                    radioBtn.checked = true;

                                                    // Update border pada semua card
                                                    updateBorders(alamatId);

                                                    // Trigger event change untuk radio button
                                                    const event = new Event('change');
                                                    radioBtn.dispatchEvent(event);
                                                });
                                            });

                                            // Tambahkan event listener pada radio button untuk menangani kasus klik langsung pada radio
                                            const radioButtons = document.querySelectorAll('.radio-alamat');
                                            radioButtons.forEach(function(radio) {
                                                radio.addEventListener('change', function() {
                                                    if (this.checked) {
                                                        updateBorders(this.value);
                                                    }
                                                });
                                            });
                                        });
                                    </script>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-gray-500 mb-4">Anda belum memiliki alamat pengiriman</p>
                                    <button type="button" id="btnTambahAlamat"
                                        class="btn-tambah-alamat bg-primary text-white py-2 px-4 rounded-lg hover:bg-primary/90">
                                        Tambah Alamat Baru
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>



                    <!-- Metode Pengiriman -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Metode Pengiriman</h2>
                        </div>

                        <div class="p-4" id="pengirimanContainer">
                            <div id="loadingPengiriman" class="text-center py-4 hidden">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-custom">
                                </div>
                                <p class="mt-2 text-gray-600">Mengambil data pengiriman...</p>
                            </div>

                            <div id="errorPengiriman" class="text-center py-4 hidden">
                                <p class="text-red-500">Pilih alamat pengiriman terlebih dahulu</p>
                            </div>

                            <div id="pilihKurir" class="mb-4 hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kurir</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <div class="border rounded-lg p-3 text-center cursor-pointer hover:border-custom kurir-option"
                                        data-kurir="jne">
                                        <img src="{{ asset('img/ekspedisi/jne.png') }}" alt="JNE"
                                            class="h-8 mx-auto mb-1">
                                        <span class="text-sm">JNE</span>
                                    </div>
                                    <div class="border rounded-lg p-3 text-center cursor-pointer hover:border-custom kurir-option"
                                        data-kurir="pos">
                                        <img src="{{ asset('img/ekspedisi/pos.png') }}" alt="POS"
                                            class="h-8 mx-auto mb-1">
                                        <span class="text-sm">POS</span>
                                    </div>
                                    <div class="border rounded-lg p-3 text-center cursor-pointer hover:border-custom kurir-option"
                                        data-kurir="tiki">
                                        <img src="{{ asset('img/ekspedisi/tiki.png') }}" alt="TIKI"
                                            class="h-8 mx-auto mb-1">
                                        <span class="text-sm">TIKI</span>
                                    </div>
                                    <div class="border rounded-lg p-3 text-center cursor-pointer hover:border-custom kurir-option"
                                        data-kurir="jnt">
                                        <img src="{{ asset('img/ekspedisi/jnt.png') }}" alt="J&T"
                                            class="h-8 mx-auto mb-1">
                                        <span class="text-sm">J&T</span>
                                    </div>
                                </div>
                            </div>

                            <div id="layananKurir" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Layanan</label>
                                <div id="layananContainer" class="space-y-2">
                                    <!-- Layanan akan diisi melalui AJAX -->
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
                            @foreach ($keranjang as $item)
                                <div class="p-4 flex items-start">
                                    <div class="flex-shrink-0 w-16 h-16">
                                        <img src="{{ asset('storage/' . $item['gambar']) }}"
                                            alt="{{ $item['nama_barang'] }}" class="w-full h-full object-cover rounded">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex justify-between">
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-900">{{ $item['nama_barang'] }}
                                                </h3>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $item['jumlah'] }} x Rp
                                                    {{ number_format($item['harga'], 0, ',', '.') }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Ukuran: {{ $item['ukuran'] }} | Warna:
                                                    <span class="inline-block w-3 h-3 rounded-full align-middle"
                                                        style="background-color: {{ $item['kode_hex'] }}"></span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900">
                                                    Rp {{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Opsi Dropship -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Pengiriman Dropship</h2>
                        </div>
                        <div class="p-4">
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="is_dropship" name="is_dropship"
                                        class="h-4 w-4 text-custom focus:ring-custom"
                                        {{ auth()->guard('pelanggan')->user()->role === 'reseller' ? 'checked' : '' }}
                                        {{ auth()->guard('pelanggan')->user()->role === 'reseller' ? 'disabled' : '' }}>
                                    <label for="is_dropship" class="ml-2 text-sm text-gray-700">
                                        Kirim sebagai dropship
                                    </label>
                                </div>
                            </div>

                            <div id="dropshipDetails"
                                class="{{ auth()->guard('pelanggan')->user()->role === 'reseller' ? '' : 'hidden' }}">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="nama_pengirim" class="block text-sm font-medium text-gray-700 mb-1">
                                            Nama Pengirim
                                        </label>
                                        <input type="text" id="nama_pengirim" name="nama_pengirim"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                            placeholder="Nama pengirim untuk label">
                                    </div>
                                    <div>
                                        <label for="no_hp_pengirim" class="block text-sm font-medium text-gray-700 mb-1">
                                            No. HP Pengirim
                                        </label>
                                        <input type="text" id="no_hp_pengirim" name="no_hp_pengirim"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                            placeholder="Nomor HP pengirim">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 mt-6 lg:mt-0">
                    <div class="bg-white rounded-lg shadow sticky top-4">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Ringkasan Pesanan</h2>
                        </div>

                        <div class="p-4">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Harga ({{ count($keranjang) }} barang)</span>
                                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Biaya Pengiriman</span>
                                    <span id="ongkirDisplay">Rp 0</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t">
                                <div class="flex justify-between mb-4">
                                    <span class="font-medium">Total Tagihan</span>
                                    <span class="text-lg font-bold text-custom" id="totalDisplay">
                                        {{-- {{$subtotal}} --}}
                                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                                    </span>
                                </div>

                                <form id="checkoutForm" action="{{ route('checkout.proses') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_alamat" id="idAlamat">
                                    <input type="hidden" name="ekspedisi" id="ekspedisi">
                                    <input type="hidden" name="layanan_ekspedisi" id="layananEkspedisi">
                                    <input type="hidden" name="ongkir" id="ongkir" value="0">
                                    <input type="hidden" name="estimasi_waktu" id="estimasiWaktu">
                                    <input type="hidden" name="is_dropship" id="is_dropship_hidden"
                                        value="{{ auth()->guard('pelanggan')->user()->role === 'reseller' ? '1' : '0' }}">
                                    <input type="hidden" name="nama_pengirim" id="nama_pengirim_hidden" value="">
                                    <input type="hidden" name="no_hp_pengirim" id="no_hp_pengirim_hidden"
                                        value="">

                                    <div class="mb-4">
                                        <label for="keterangan"
                                            class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                                        <textarea name="keterangan" id="keterangan"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                            rows="2" placeholder="Catatan untuk pesanan (opsional)"></textarea>
                                    </div>

                                    <button type="submit" id="btnBayar"
                                        class="w-full bg-primary text-white py-3 px-4 rounded-lg hover:bg-primary/90 font-medium disabled:bg-gray-400 disabled:cursor-not-allowed"
                                        disabled>
                                        Bayar Sekarang
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Alamat -->
        <div id="modalAlamat" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Tambah Alamat Baru</h3>
                    <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="formAlamat" method="POST" action="{{ route('checkout.simpan-alamat') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="nama_alamat" class="block text-sm font-medium text-gray-700 mb-1">Label
                                Alamat</label>
                            <input type="text" id="nama_alamat" name="nama_alamat"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                placeholder="Rumah, Kantor, dll" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="nama_penerima" class="block text-sm font-medium text-gray-700 mb-1">Nama
                                    Penerima</label>
                                <input type="text" id="nama_penerima" name="nama_penerima"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                    placeholder="Nama lengkap penerima" required>
                            </div>
                            <div>
                                <label for="no_hp_penerima" class="block text-sm font-medium text-gray-700 mb-1">No. HP
                                    Penerima</label>
                                <input type="text" id="no_hp_penerima" name="no_hp_penerima"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                    placeholder="Nomor HP aktif" required>
                            </div>
                        </div>

                        <!-- Input Search Lokasi -->
                        <div>
                            <label for="search_location" class="block text-sm font-medium text-gray-700 mb-1">Cari
                                Lokasi</label>
                            <div class="flex space-x-2">
                                <input type="text" id="search_location"
                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                    placeholder="Ketik minimal 3 huruf...">
                                <button type="button" id="btn_search_location"
                                    class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                                    Cari
                                </button>
                            </div>
                            <div class="mt-2" id="search_results" style="display:none;">
                                <select id="location_results" size="5"
                                    class="w-full border-gray-300 rounded-md shadow-sm mt-2"></select>
                            </div>
                        </div>

                        <!-- Hidden / Readonly Location Fields -->
                        <input type="hidden" id="provinsi_id" name="provinsi_id">
                        <input type="hidden" id="kota_id" name="kota_id">
                        <input type="hidden" id="kecamatan_id" name="kecamatan_id">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="provinsi"
                                    class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                                <input type="text" id="provinsi" name="provinsi"
                                    class="w-full bg-gray-100 border border-gray-300 rounded-md" readonly required>
                            </div>
                            <div>
                                <label for="kota"
                                    class="block text-sm font-medium text-gray-700 mb-1">Kota/Kabupaten</label>
                                <input type="text" id="kota" name="kota"
                                    class="w-full bg-gray-100 border border-gray-300 rounded-md" readonly required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="kecamatan"
                                    class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                                <input type="text" id="kecamatan" name="kecamatan"
                                    class="w-full bg-gray-100 border border-gray-300 rounded-md" readonly required>
                            </div>
                            <div>
                                <label for="kelurahan"
                                    class="block text-sm font-medium text-gray-700 mb-1">Kelurahan/Desa</label>
                                <input type="text" id="kelurahan" name="kelurahan"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label for="kode_pos" class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                            <input type="text" id="kode_pos" name="kode_pos"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                required>
                        </div>

                        <div>
                            <label for="alamat_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Alamat
                                Lengkap</label>
                            <textarea id="alamat_lengkap" name="alamat_lengkap"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-custom focus:ring focus:ring-custom focus:ring-opacity-50"
                                rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, patokan" required></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_utama" name="is_utama"
                                class="h-4 w-4 text-custom focus:ring-custom">
                            <label for="is_utama" class="ml-2 text-sm text-gray-700">Jadikan sebagai alamat utama</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="btnBatalAlamat"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Simpan
                            Alamat</button>
                    </div>
                </form>

            </div>
        </div>
    </main>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_location');
            const searchButton = document.getElementById('btn_search_location');
            const searchResults = document.getElementById('search_results');
            const locationResults = document.getElementById('location_results');

            const inputFields = {
                provinsi: document.getElementById('provinsi'),
                kota: document.getElementById('kota'),
                kecamatan: document.getElementById('kecamatan'),
                kelurahan: document.getElementById('kelurahan'),
                kode_pos: document.getElementById('kode_pos'),
                provinsi_id: document.getElementById('provinsi_id'),
                kota_id: document.getElementById('kota_id'),
                kecamatan_id: document.getElementById('kecamatan_id')
            };

            searchButton.addEventListener('click', searchLocation);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchLocation();
                }
            });

            locationResults.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (!selectedOption || !selectedOption.dataset.location) return;

                try {
                    const locationData = JSON.parse(selectedOption.dataset.location);
                    inputFields.provinsi.value = locationData.province_name || '';
                    inputFields.kota.value = locationData.city_name || '';
                    inputFields.kecamatan.value = locationData.district_name || '';
                    inputFields.kelurahan.value = locationData.subdistrict_name || '';
                    inputFields.kode_pos.value = locationData.zip_code || '';
                    inputFields.provinsi_id.value = locationData.province_id || '';
                    inputFields.kota_id.value = locationData.city_id || '';
                    inputFields.kecamatan_id.value = locationData.district_id || '';
                } catch (error) {
                    console.error('Parsing Error:', error);
                    alert('Terjadi kesalahan saat memilih lokasi');
                }
            });

            function searchLocation() {
                const keyword = searchInput.value.trim();
                if (keyword.length < 3) {
                    alert('Minimal ketik 3 huruf untuk pencarian');
                    return;
                }

                locationResults.innerHTML = '<option>Mencari...</option>';
                searchResults.style.display = 'block';

                fetch(`/admin/toko/search-destination?keyword=${encodeURIComponent(keyword)}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        locationResults.innerHTML = '';

                        if (!data || data.length === 0) {
                            locationResults.innerHTML = '<option>Tidak ditemukan hasil</option>';
                            return;
                        }

                        data.forEach(location => {
                            const option = document.createElement('option');
                            const display = [
                                location.subdistrict_name,
                                location.district_name,
                                location.city_name,
                                location.province_name,
                                location.zip_code ? `(${location.zip_code})` : ''
                            ].filter(Boolean).join(', ');

                            option.textContent = display;
                            option.dataset.location = JSON.stringify(location);
                            locationResults.appendChild(option);
                        });

                        if (data.length > 0) {
                            locationResults.selectedIndex = 0;
                            locationResults.dispatchEvent(new Event('change'));
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        locationResults.innerHTML = '<option>Gagal mengambil data lokasi</option>';
                        alert('Terjadi kesalahan, silakan coba lagi.');
                    });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables for checkout process
            let selectedAlamat = null;
            let selectedKurir = null;
            let selectedLayanan = null;
            let ongkir = 0;
            let estimasiWaktu = '';
            const subtotal = {{ $subtotal }};

            // Form elements
            const checkoutForm = document.getElementById('checkoutForm');
            const btnBayar = document.getElementById('btnBayar');

            // Hidden inputs
            const idAlamatInput = document.getElementById('idAlamat');
            const ekspedisiInput = document.getElementById('ekspedisi');
            const layananEkspedisiInput = document.getElementById('layananEkspedisi');
            const ongkirInput = document.getElementById('ongkir');
            const estimasiWaktuInput = document.getElementById('estimasiWaktu');
            const isDropshipHidden = document.getElementById('is_dropship_hidden');
            const namaPengirimHidden = document.getElementById('nama_pengirim_hidden');
            const noHpPengirimHidden = document.getElementById('no_hp_pengirim_hidden');

            // Dropship handling
            const isDropshipCheckbox = document.getElementById('is_dropship');
            const dropshipDetails = document.getElementById('dropshipDetails');
            const namaPengirimInput = document.getElementById('nama_pengirim');
            const noHpPengirimInput = document.getElementById('no_hp_pengirim');

            isDropshipCheckbox.addEventListener('change', function() {
                dropshipDetails.classList.toggle('hidden', !this.checked);
                isDropshipHidden.value = this.checked ? '1' : '0';

                if (this.checked) {
                    namaPengirimInput.setAttribute('required', 'required');
                    noHpPengirimInput.setAttribute('required', 'required');
                } else {
                    namaPengirimInput.removeAttribute('required');
                    noHpPengirimInput.removeAttribute('required');
                }
            });

            // Alamat selection
            const radioAlamat = document.querySelectorAll('.radio-alamat');
            radioAlamat.forEach(radio => {
                radio.addEventListener('change', function() {
                    selectedAlamat = this.value;
                    idAlamatInput.value = selectedAlamat;

                    // Reset shipping selection
                    resetShipping();

                    // Show shipping options
                    document.getElementById('errorPengiriman').classList.add('hidden');
                    document.getElementById('pilihKurir').classList.remove('hidden');

                    updateCheckoutButton();
                });
            });

            // Pre-select alamat if only one exists
            if (radioAlamat.length === 1) {
                radioAlamat[0].checked = true;
                selectedAlamat = radioAlamat[0].value;
                idAlamatInput.value = selectedAlamat;
                document.getElementById('pilihKurir').classList.remove('hidden');
            }

            function resetShippingOptions() {
                const layananContainer = document.getElementById('layananContainer');
                const layananKurir = document.getElementById('layananKurir');
                const errorPengiriman = document.getElementById('errorPengiriman');
                const loadingPengiriman = document.getElementById('loadingPengiriman');

                // Clear previous services
                layananContainer.innerHTML = '';

                // Hide shipping options container
                layananKurir.classList.add('hidden');

                // Hide and clear error messages
                errorPengiriman.classList.add('hidden');
                errorPengiriman.innerHTML = '';

                // Hide loading indicator
                loadingPengiriman.classList.add('hidden');

                // Reset shipping-related inputs
                selectedLayanan = null;
                selectedKurir = null;
                ongkir = 0;
                estimasiWaktu = '';

                // Reset display
                document.getElementById('ongkirDisplay').textContent = 'Rp 0';
                document.getElementById('totalDisplay').textContent = `Rp ${formatNumber(subtotal)}`;

                // Disable checkout button
                updateCheckoutButton();
            }

            // Reset shipping selections
            function resetShipping() {
                selectedKurir = null;
                selectedLayanan = null;
                ongkir = 0;
                estimasiWaktu = '';

                // Reset UI
                document.querySelectorAll('.kurir-option').forEach(el => {
                    el.classList.remove('border-custom', 'bg-primary/10');
                });

                document.getElementById('layananKurir').classList.add('hidden');
                document.getElementById('ongkirDisplay').textContent = 'Rp 0';
                document.getElementById('totalDisplay').textContent = `Rp ${formatNumber(subtotal)}`;

                // Reset form inputs
                ekspedisiInput.value = '';
                layananEkspedisiInput.value = '';
                ongkirInput.value = '0';
                estimasiWaktuInput.value = '';
            }

            // Kurir selection
            const kurirOptions = document.querySelectorAll('.kurir-option');
            kurirOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Reset UI
                    kurirOptions.forEach(opt => {
                        opt.classList.remove('border-custom', 'bg-primary/10');
                    });
                    this.classList.add('border-custom', 'bg-primary/10');

                    // Set selected kurir
                    selectedKurir = this.dataset.kurir;
                    ekspedisiInput.value = selectedKurir;

                    // Get shipping costs
                    getShippingCosts(selectedAlamat, selectedKurir);
                });
            });

            // Get shipping costs from RajaOngkir
            function getShippingCosts(alamatId, kurir) {
                // Reset previous shipping options before new request
                resetShippingOptions();

                console.log('Alamat ID:', alamatId);
                console.log('Kurir:', kurir);

                // Cari kecamatan_id dari alamat yang dipilih
                let selectedAddress = document.querySelector(
                    `input[name="alamat_pengiriman"][value="${alamatId}"]`);
                let kecamatanId = selectedAddress ? selectedAddress.dataset.kecamatanId : null;

                console.log('Kecamatan ID:', kecamatanId);

                if (!kecamatanId) {
                    document.getElementById('loadingPengiriman').classList.add('hidden');
                    document.getElementById('errorPengiriman').classList.remove('hidden');
                    document.getElementById('errorPengiriman').innerHTML =
                        'Data kecamatan tidak ditemukan. Pilih alamat lain atau tambah alamat baru.';
                    return;
                }

                // Show loading indicator
                document.getElementById('loadingPengiriman').classList.remove('hidden');
                document.getElementById('errorPengiriman').classList.add('hidden');

                fetch(`{{ route('checkout.cek-ongkir') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            kecamatan_id: kecamatanId,
                            kurir: kurir
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received data:', data);

                        // Hide loading
                        document.getElementById('loadingPengiriman').classList.add('hidden');

                        if (data && data.length > 0) {
                            // Display shipping options
                            displayShippingOptions(data);
                        } else {
                            // Reset and show error for no services
                            resetShippingOptions();
                            document.getElementById('errorPengiriman').classList.remove('hidden');
                            document.getElementById('errorPengiriman').innerHTML =
                                'Layanan pengiriman tidak tersedia untuk alamat ini.';
                        }
                    })
                    .catch(error => {
                        console.error('Full error:', error);

                        // Reset and show error
                        resetShippingOptions();
                        document.getElementById('errorPengiriman').classList.remove('hidden');
                        document.getElementById('errorPengiriman').innerHTML =
                            'Terjadi kesalahan saat mengambil data pengiriman. Silakan coba lagi.';
                    });
            }

            // Display shipping options
            function displayShippingOptions(data) {
                const layananContainer = document.getElementById('layananContainer');
                const errorPengiriman = document.getElementById('errorPengiriman');
                layananContainer.innerHTML = '';

                // Check if data is valid and not empty
                if (!data || data.length === 0) {
                    errorPengiriman.classList.remove('hidden');
                    errorPengiriman.innerHTML = 'Tidak ada layanan pengiriman tersedia untuk kurir ini.';
                    document.getElementById('layananKurir').classList.add('hidden');
                    return;
                }

                // Hide error message
                errorPengiriman.classList.add('hidden');

                // Group services by courier
                const servicesByCourier = {};
                data.forEach(service => {
                    if (!servicesByCourier[service.name]) {
                        servicesByCourier[service.name] = [];
                    }
                    servicesByCourier[service.name].push(service);
                });

                // Create service options for each courier
                Object.keys(servicesByCourier).forEach(courierName => {
                    const courierServices = servicesByCourier[courierName];

                    // Create a header for the courier
                    const courierHeader = document.createElement('div');
                    courierHeader.className = 'text-lg font-semibold mb-2 mt-4';
                    courierHeader.textContent = courierName;
                    layananContainer.appendChild(courierHeader);

                    // Create service options for this courier
                    courierServices.forEach(service => {
                        const serviceElement = document.createElement('div');
                        serviceElement.className =
                            'border rounded-lg p-3 cursor-pointer hover:border-custom layanan-option mb-2';

                        // Set data attributes for later use
                        serviceElement.dataset.layanan = service.service;
                        serviceElement.dataset.nama = service.description;
                        serviceElement.dataset.ongkir = service.cost;
                        serviceElement.dataset.estimasi = service.etd;
                        serviceElement.dataset.kurir = service.name;

                        // Create service details
                        serviceElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium">${service.description} (${service.service})</p>
                            <p class="text-sm text-gray-500">
                                Estimasi tiba: ${service.etd.replace(/HARI|hari|Hari/gi, '').trim()} hari
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">Rp ${formatNumber(service.cost)}</p>
                        </div>
                    </div>
                `;

                        // Add click event to select service
                        serviceElement.addEventListener('click', function() {
                            // Remove selection from all services
                            document.querySelectorAll('.layanan-option').forEach(opt => {
                                opt.classList.remove('border-custom',
                                    'bg-primary/10');
                            });

                            // Highlight selected service
                            this.classList.add('border-custom', 'bg-primary/10');

                            // Update form inputs
                            selectedLayanan = this.dataset.layanan;
                            selectedKurir = this.dataset.kurir;
                            ongkir = parseInt(this.dataset.ongkir);
                            estimasiWaktu = this.dataset.estimasi;

                            // Update hidden input fields
                            layananEkspedisiInput.value =
                                `${selectedKurir} ${this.dataset.nama} (${this.dataset.layanan})`;
                            ekspedisiInput.value = selectedKurir;
                            ongkirInput.value = ongkir;
                            estimasiWaktuInput.value =
                                `${estimasiWaktu.replace(/HARI|hari|Hari/gi, '').trim()} hari`;

                            // Update display
                            document.getElementById('ongkirDisplay').textContent =
                                `Rp ${formatNumber(ongkir)}`;
                            document.getElementById('totalDisplay').textContent =
                                `Rp ${formatNumber(subtotal + ongkir)}`;

                            // Update checkout button state
                            updateCheckoutButton();
                        });

                        layananContainer.appendChild(serviceElement);
                    });
                });

                // Show the layanan container
                document.getElementById('layananKurir').classList.remove('hidden');
            }

            // Update checkout button state
            function updateCheckoutButton() {
                if (selectedAlamat && selectedKurir && selectedLayanan) {
                    btnBayar.disabled = false;
                } else {
                    btnBayar.disabled = true;
                }
            }

            // Form validation and submission
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!selectedAlamat || !selectedKurir || !selectedLayanan) {
                    alert('Silakan pilih alamat pengiriman dan metode pengiriman terlebih dahulu.');
                    return false;
                }

                // Update dropship information before submit
                if (isDropshipCheckbox.checked) {
                    namaPengirimHidden.value = namaPengirimInput.value;
                    noHpPengirimHidden.value = noHpPengirimInput.value;

                    if (!namaPengirimInput.value || !noHpPengirimInput.value) {
                        alert('Silakan lengkapi data pengirim untuk pengiriman dropship.');
                        return false;
                    }
                }

                // Submit the form
                this.submit();
            });

            // Modal Alamat
            const modalAlamat = document.getElementById('modalAlamat');
            const btnTambahAlamat = document.getElementById('btnTambahAlamat');
            const btnBatalAlamat = document.getElementById('btnBatalAlamat');
            const closeModal = document.getElementById('closeModal');
            const formAlamat = document.getElementById('formAlamat');

            btnTambahAlamat.addEventListener('click', function() {
                modalAlamat.classList.remove('hidden');
            });

            function closeModalAlamat() {
                modalAlamat.classList.add('hidden');
                formAlamat.reset();
                document.getElementById('kota_id').disabled = true;
                document.getElementById('kecamatan_id').disabled = true;
            }

            btnBatalAlamat.addEventListener('click', closeModalAlamat);
            closeModal.addEventListener('click', closeModalAlamat);

            // Form alamat submission
            formAlamat.addEventListener('submit', function(e) {
                e.preventDefault();

                // Get form data
                const formData = new FormData(formAlamat);

                // Add province, city, and district names
                const provinsiSelect = document.getElementById('provinsi_id');
                const kotaSelect = document.getElementById('kota_id');
                const kecamatanSelect = document.getElementById('kecamatan_id');

                // Check if selects have valid values
                if (provinsiSelect.selectedIndex === -1 || !provinsiSelect.value) {
                    alert('Silakan pilih provinsi terlebih dahulu');
                    return;
                }

                if (kotaSelect.selectedIndex === -1 || !kotaSelect.value) {
                    alert('Silakan pilih kota/kabupaten terlebih dahulu');
                    return;
                }

                if (kecamatanSelect.selectedIndex === -1 || !kecamatanSelect.value) {
                    alert('Silakan pilih kecamatan terlebih dahulu');
                    return;
                }

                document.getElementById('provinsi').value = provinsiSelect.options[provinsiSelect
                    .selectedIndex].dataset.nama;
                document.getElementById('kota').value = kotaSelect.options[kotaSelect.selectedIndex].dataset
                    .nama;
                document.getElementById('kecamatan').value = kecamatanSelect.options[kecamatanSelect
                    .selectedIndex].dataset.nama;

                // Show loading indicator
                const submitBtn = formAlamat.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span> Menyimpan...';

                fetch('{{ route('checkout.simpan-alamat') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            // Reload page to show new address
                            location.reload();
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat menyimpan alamat.');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan alamat: ' + error.message);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Dynamic dropdowns for address form
            const provinsiSelect = document.getElementById('provinsi_id');
            const kotaSelect = document.getElementById('kota_id');
            const kecamatanSelect = document.getElementById('kecamatan_id');

            provinsiSelect.addEventListener('change', function() {
                const provinsiId = this.value;

                if (provinsiId) {
                    // Enable and populate kota dropdown
                    kotaSelect.disabled = true;
                    kotaSelect.innerHTML = '<option value="">Memuat data...</option>';

                    fetch(`{{ route('checkout.get-kota') }}?provinsi_id=${provinsiId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok: ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(data => {
                            kotaSelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';

                            if (data && data.length > 0) {
                                data.forEach(kota => {
                                    const option = document.createElement('option');
                                    option.value = kota.city_id;
                                    option.textContent = kota.type + ' ' + kota.city_name;
                                    option.dataset.nama = kota.type + ' ' + kota.city_name;
                                    kotaSelect.appendChild(option);
                                });
                            } else {
                                kotaSelect.innerHTML = '<option value="">Tidak ada data kota</option>';
                            }

                            kotaSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            kotaSelect.innerHTML = '<option value="">Error loading data</option>';
                            alert('Gagal memuat data kota: ' + error.message);
                            kotaSelect.disabled = false;
                        });
                } else {
                    kotaSelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
                    kotaSelect.disabled = true;
                    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                    kecamatanSelect.disabled = true;
                }
            });

            kotaSelect.addEventListener('change', function() {
                const kotaId = this.value;

                if (kotaId) {
                    // Enable and populate kecamatan dropdown
                    kecamatanSelect.disabled = true;
                    kecamatanSelect.innerHTML = '<option value="">Memuat data...</option>';

                    fetch(`{{ route('checkout.get-kecamatan') }}?kota_id=${kotaId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok: ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(data => {
                            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';

                            if (data && data.length > 0) {
                                data.forEach(kecamatan => {
                                    const option = document.createElement('option');
                                    option.value = kecamatan.subdistrict_id;
                                    option.textContent = kecamatan.subdistrict_name;
                                    option.dataset.nama = kecamatan.subdistrict_name;
                                    kecamatanSelect.appendChild(option);
                                });
                            } else {
                                kecamatanSelect.innerHTML =
                                    '<option value="">Tidak ada data kecamatan</option>';
                            }

                            kecamatanSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            kecamatanSelect.innerHTML = '<option value="">Error loading data</option>';
                            alert('Gagal memuat data kecamatan: ' + error.message);
                            kecamatanSelect.disabled = false;
                        });
                } else {
                    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                    kecamatanSelect.disabled = true;
                }
            });

            // Format number to currency
            function formatNumber(number) {
                // Ensure number is an integer by using Math.round()
                const roundedNumber = Math.round(Number(number));
                return roundedNumber.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Handle dropship checkbox initial state
            if (isDropshipCheckbox.checked) {
                dropshipDetails.classList.remove('hidden');
                namaPengirimInput.setAttribute('required', 'required');
                noHpPengirimInput.setAttribute('required', 'required');
            }
        });
    </script>
@endpush
