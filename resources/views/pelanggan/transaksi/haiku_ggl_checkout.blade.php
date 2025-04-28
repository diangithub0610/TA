@extends('pelanggan.layouts.app')
@section('title', 'Checkout')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Alamat Pengiriman -->
            <div class="md:col-span-2 space-y-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Alamat Pengiriman</h2>

                    <div class="space-y-4" id="alamat-container">
                        @foreach ($alamat as $item)
                            <div class="border rounded-lg p-4 relative {{ $item->is_utama ? 'border-custom' : '' }}">
                                <input type="radio" name="alamat" value="{{ $item->id_alamat }}"
                                    class="alamat-radio absolute top-4 right-4" {{ $item->is_utama ? 'checked' : '' }}>

                                <div>
                                    <h3 class="font-medium">{{ $item->nama_alamat }}
                                        @if ($item->is_utama)
                                            <span class="text-custom text-sm ml-2">(Utama)</span>
                                        @endif
                                    </h3>
                                    <p>{{ $item->nama_penerima }} | {{ $item->no_hp_penerima }}</p>
                                    <p>{{ $item->alamat_lengkap }}</p>
                                    <p>{{ $item->kecamatan }}, {{ $item->kota }}, {{ $item->provinsi }}
                                        {{ $item->kode_pos }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button id="tambah-alamat" class="mt-4 w-full bg-custom text-white py-2 rounded-lg">
                        + Tambah Alamat Baru
                    </button>
                </div>

                <!-- Pilihan Pengiriman -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Pilihan Pengiriman</h2>

                    <div id="layanan-pengiriman">
                        <!-- Opsi kurir akan dimuat via JavaScript -->
                        <div class="text-center text-gray-500">
                            Pilih alamat terlebih dahulu
                        </div>
                    </div>
                </div>

                @if (!$is_reseller)
                    <!-- Pilihan Dropship -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="is_dropship" class="mr-2">
                            <label for="is_dropship">Kirim sebagai Dropship</label>
                        </div>

                        <div id="dropship-detail" class="mt-4 hidden">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2">Nama Pengirim</label>
                                    <input type="text" id="nama_pengirim" class="w-full border rounded-lg p-2">
                                </div>
                                <div>
                                    <label class="block mb-2">No. HP Pengirim</label>
                                    <input type="text" id="no_hp_pengirim" class="w-full border rounded-lg p-2">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Ringkasan Pesanan -->
            <div>
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h2 class="text-xl font-semibold mb-4">Ringkasan Pesanan</h2>

                    <div class="space-y-4">
                        @foreach ($keranjang as $item)
                            <div class="flex justify-between">
                                <div>
                                    <p>{{ $item['nama_barang'] }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $item['jumlah'] }} x Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <p>Rp {{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="flex justify-between">
                        <p>Subtotal</p>
                        <p id="subtotal">Rp
                            {{ number_format($keranjang? array_sum(array_map(function ($item) {return $item['harga'] * $item['jumlah'];}, $keranjang)): 0,0,',','.') }}
                        </p>
                    </div>

                    <div class="flex justify-between mt-2">
                        <p>Ongkos Kirim</p>
                        <p id="ongkir">-</p>
                    </div>

                    <hr class="my-4">

                    <div class="flex justify-between font-bold">
                        <p>Total</p>
                        <p id="total">Rp
                            {{ number_format($keranjang? array_sum(array_map(function ($item) {return $item['harga'] * $item['jumlah'];}, $keranjang)): 0,0,',','.') }}
                        </p>
                    </div>

                    <button id="proses-pembayaran" class="mt-4 w-full bg-custom text-white py-2 rounded-lg" disabled>
                        Proses Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Alamat -->
    <div id="modal-tambah-alamat" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Tambah Alamat Baru</h2>

            <form id="form-tambah-alamat">
                <!-- Form input alamat -->
                <!-- Isi form akan ditambahkan dengan JavaScript -->
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalBerat = {{ $total_berat }};
            let selectedAlamat = null;
            let selectedKurir = null;

            // Pilih Alamat
            function setupAlamatRadio() {
                document.querySelectorAll('.alamat-radio').forEach(radio => {
                    radio.addEventListener('change', function() {
                        selectedAlamat = this.value;
                        cariOngkir();
                    });
                });
            }
            setupAlamatRadio();

            // Tambah Alamat Modal
            document.getElementById('tambah-alamat').addEventListener('click', function() {
                document.getElementById('modal-tambah-alamat').classList.remove('hidden');
            });

            // Fungsi Cari Ongkir
            function cariOngkir() {
                if (!selectedAlamat) {
                    console.error('Alamat belum dipilih');
                    return;
                }

                fetch('{{ route('checkout.cek-ongkir') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            origin: '{{ config('services.rajaongkir.origin_id') }}',
                            destination: selectedAlamat,
                            weight: totalBerat
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        const layananPengiriman = document.getElementById('layanan-pengiriman');
                        layananPengiriman.innerHTML = '';

                        if (result.status === 'success' && result.data.length > 0) {
                            result.data.forEach(layanan => {
                                const div = document.createElement('div');
                                div.classList.add('border', 'rounded-lg', 'p-4', 'mb-2',
                                    'cursor-pointer', 'hover:bg-gray-100');
                                div.innerHTML = `
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium">${layanan.name} - ${layanan.service}</h3>
                                <p class="text-sm text-gray-500">${layanan.description}</p>
                                <p class="text-sm text-gray-500">Estimasi: ${layanan.etd} hari</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">Rp ${formatNumber(layanan.cost)}</p>
                            </div>
                        </div>
                    `;

                                div.addEventListener('click', function() {
                                    // Hapus border dari semua layanan
                                    document.querySelectorAll('#layanan-pengiriman > div')
                                        .forEach(el => {
                                            el.classList.remove('border-custom');
                                        });

                                    // Tambahkan border pada layanan yang dipilih
                                    this.classList.add('border-custom');
                                    selectedKurir = layanan;
                                    updateTotalBiaya();
                                });

                                layananPengiriman.appendChild(div);
                            });
                        } else {
                            layananPengiriman.innerHTML =
                                '<p class="text-center text-gray-500">Tidak ada layanan pengiriman tersedia</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error mencari ongkir:', error);
                    });
            }

            // Submit Tambah Alamat
            document.getElementById('form-tambah-alamat').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('{{ route('checkout.tambah-alamat') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            const alamatContainer = document.getElementById('alamat-container');
                            const newAlamatHtml = `
                    <div class="border rounded-lg p-4 relative">
                        <input type="radio" name="alamat" value="${result.alamat.id_alamat}" 
                            class="alamat-radio absolute top-4 right-4" checked>
                        
                        <div>
                            <h3 class="font-medium">${result.alamat.nama_alamat}</h3>
                            <p>${result.alamat.nama_penerima} | ${result.alamat.no_hp_penerima}</p>
                            <p>${result.alamat.alamat_lengkap}</p>
                            <p>${result.alamat.kecamatan}, ${result.alamat.kota}, ${result.alamat.provinsi} ${result.alamat.kode_pos}</p>
                        </div>
                    </div>
                `;

                            alamatContainer.insertAdjacentHTML('beforeend', newAlamatHtml);
                            document.getElementById('modal-tambah-alamat').classList.add('hidden');

                            // Reset form
                            this.reset();

                            // Perbarui radio button
                            setupAlamatRadio();

                            // Set alamat baru sebagai yang terpilih
                            selectedAlamat = result.alamat.id_alamat;
                            cariOngkir();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            // Batal Tambah Alamat
            document.getElementById('batal-tambah-alamat').addEventListener('click', function() {
                document.getElementById('modal-tambah-alamat').classList.add('hidden');
            });

            // Proses Pembayaran
            document.getElementById('proses-pembayaran').addEventListener('click', function() {
                if (!selectedAlamat || !selectedKurir) {
                    alert('Pilih alamat dan kurir terlebih dahulu');
                    return;
                }

                const payload = {
                    alamat_id: selectedAlamat,
                    kurir: selectedKurir,
                    is_dropship: @if (!$is_reseller)
                        document.getElementById('is_dropship').checked:
                            true
                    @endif ,
                    nama_pengirim: @if (!$is_reseller)
                        document.getElementById('nama_pengirim').value:
                            null
                    @endif ,
                    no_hp_pengirim: @if (!$is_reseller)
                        document.getElementById('no_hp_pengirim').value:
                            null
                    @endif
                };

                // Implementasi proses pembayaran Midtrans akan dilakukan selanjutnya
                fetch('{{ route('checkout.proses-pembayaran') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            // Redirect ke halaman pembayaran Midtrans
                            window.location.href = result.redirect_url;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    </script>
@endpush
