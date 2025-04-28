@extends('pelanggan.layouts.app')
@section('title', 'Keranjang')
@section('content')
<main class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mt-10">
        <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>

        @if (count($keranjang) > 0)
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Daftar Barang</h2>
                        </div>

                        <div class="divide-y divide-gray-200">
                            {{-- {{dd($keranjang)}} --}}
                            @foreach ($keranjang as $item)
                                <div class="p-4 flex flex-col sm:flex-row">
                                    <div class="flex-shrink-0 sm:w-24 sm:h-24 w-full h-40 mb-4 sm:mb-0">
                                        <img src="{{ asset('storage/' . $item['gambar']) }}"
                                            alt="{{ $item['nama_barang'] }}" class="w-full h-full object-cover rounded">
                                    </div>
                                    <div class="sm:ml-4 flex-1">
                                        <div class="flex flex-col sm:flex-row justify-between">
                                            <div>
                                                <h3 class="text-base font-medium text-gray-900">{{ $item['nama_barang'] }}
                                                </h3>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    Ukuran: {{ $item['ukuran'] }} | Warna:
                                                    <span class="inline-block w-4 h-4 rounded-full ml-1 align-middle"
                                                        style="background-color: #{{ $item['kode_hex'] }}"></span>
                                                </p>
                                                <p class="mt-1 text-sm text-gray-500">Stok: {{ $item['stok'] }}</p>
                                            </div>
                                            <div class="text-right mt-2 sm:mt-0">
                                                <p class="text-base font-medium text-gray-900">Rp
                                                    {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center mt-4">
                                            <div class="flex items-center border border-gray-300 rounded-lg">
                                                <button type="button"
                                                    class="btn-minus px-3 py-1 text-gray-600 hover:text-custom"
                                                    data-kode="{{ $item['kode_detail'] }}">-</button>
                                                <input type="number" class="input-jumlah w-12 text-center border-0"
                                                    min="1" max="{{ $item['stok'] }}" value="{{ $item['jumlah'] }}"
                                                    data-kode="{{ $item['kode_detail'] }}" readonly>
                                                <button type="button"
                                                    class="btn-plus px-3 py-1 text-gray-600 hover:text-custom"
                                                    data-kode="{{ $item['kode_detail'] }}"
                                                    data-stok="{{ $item['stok'] }}">+</button>
                                            </div>

                                            <div>
                                                <span class="text-base font-medium text-custom subtotal"
                                                    data-kode="{{ $item['kode_detail'] }}"
                                                    data-harga="{{ $item['harga'] }}" data-jumlah="{{ $item['jumlah'] }}">
                                                    Rp {{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}
                                                </span>
                                            </div>

                                            <button type="button" class="btn-hapus text-gray-500 hover:text-red-500"
                                                data-kode="{{ $item['kode_detail'] }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 mt-6 lg:mt-0">
                    <div class="bg-white rounded-lg shadow overflow-hidden sticky top-4">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-medium">Ringkasan Belanja</h2>
                        </div>

                        <div class="p-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Total Harga ({{ count($keranjang) }} barang)</span>
                                <span class="font-medium" id="totalHarga">Rp
                                    {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <hr class="my-4">

                            <div class="flex justify-between mb-4">
                                <span class="text-gray-800 font-medium">Total Tagihan</span>
                                <span class="text-custom font-bold text-lg" id="totalTagihan">Rp
                                    {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <a href="{{ route('checkout.index') }}"
                                class="block text-center bg-custom text-white py-3 px-4 rounded-lg hover:bg-custom/90 font-medium">
                                Lanjut ke Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <img src="{{ asset('images/empty-cart.svg') }}" alt="Keranjang Kosong" class="w-64 h-64 mx-auto mb-4">
                <h2 class="text-xl font-medium mb-2">Keranjang Belanja Anda Kosong</h2>
                <p class="text-gray-500 mb-6">Silakan tambahkan barang ke keranjang terlebih dahulu</p>
                <a href="{{ route('pelanggan.produk') }}"
                    class="inline-block bg-custom text-white py-2 px-6 rounded-lg hover:bg-custom/90">
                    Lihat Produk
                </a>
            </div>
        @endif
        </div>

        <!-- Konfirmasi hapus item -->
        <div id="confirmDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 max-w-md mx-4">
                <h3 class="text-lg font-medium mb-4">Hapus Item</h3>
                <p class="mb-6">Apakah Anda yakin ingin menghapus item ini dari keranjang?</p>
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Batal</button>
                    <button id="confirmDeleteBtn"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Hapus</button>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle item quantity update
            const btnMinus = document.querySelectorAll('.btn-minus');
            const btnPlus = document.querySelectorAll('.btn-plus');
            const inputJumlah = document.querySelectorAll('.input-jumlah');

            // Minus button
            btnMinus.forEach(btn => {
                btn.addEventListener('click', function() {
                    const kodeDetail = this.dataset.kode;
                    const input = document.querySelector(
                    `.input-jumlah[data-kode="${kodeDetail}"]`);
                    let jumlah = parseInt(input.value);

                    if (jumlah > 1) {
                        jumlah--;
                        input.value = jumlah;
                        updateItem(kodeDetail, jumlah);
                    }
                });
            });

            // Plus button
            btnPlus.forEach(btn => {
                btn.addEventListener('click', function() {
                    const kodeDetail = this.dataset.kode;
                    const stok = parseInt(this.dataset.stok);
                    const input = document.querySelector(
                    `.input-jumlah[data-kode="${kodeDetail}"]`);
                    let jumlah = parseInt(input.value);

                    if (jumlah < stok) {
                        jumlah++;
                        input.value = jumlah;
                        updateItem(kodeDetail, jumlah);
                    }
                });
            });

            // Update item quantity via AJAX
            function updateItem(kodeDetail, jumlah) {
                fetch('{{ route('keranjang.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            kode_detail: kodeDetail,
                            jumlah: jumlah
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Update subtotal
                            const subtotalElement = document.querySelector(
                                `.subtotal[data-kode="${kodeDetail}"]`);
                            const harga = parseFloat(subtotalElement.dataset.harga);
                            const subtotal = harga * jumlah;

                            subtotalElement.textContent = `Rp ${formatNumber(subtotal)}`;
                            subtotalElement.dataset.jumlah = jumlah;

                            // Update totals
                            updateTotals();
                        } else {
                            alert(data.message);
                            // Reset input to previous value
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        location.reload();
                    });
            }

            // Delete item confirmation
            const btnHapus = document.querySelectorAll('.btn-hapus');
            const confirmDelete = document.getElementById('confirmDelete');
            const cancelDelete = document.getElementById('cancelDelete');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            let deleteItemCode = null;

            btnHapus.forEach(btn => {
                btn.addEventListener('click', function() {
                    deleteItemCode = this.dataset.kode;
                    confirmDelete.classList.remove('hidden');
                });
            });

            cancelDelete.addEventListener('click', function() {
                confirmDelete.classList.add('hidden');
                deleteItemCode = null;
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (deleteItemCode) {
                    deleteItem(deleteItemCode);
                }
            });

            // Delete item via AJAX
            function deleteItem(kodeDetail) {
                fetch(`{{ url('keranjang/hapus') }}/${kodeDetail}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Hide confirmation dialog
                            confirmDelete.classList.add('hidden');

                            // Remove item from DOM
                            const itemElement = document.querySelector(
                                `.input-jumlah[data-kode="${kodeDetail}"]`).closest('.p-4');
                            itemElement.remove();

                            // Update totals
                            updateTotals();

                            // Update cart counter
                            updateCartCounter(data.jumlah_item);

                            // Reload if cart is empty
                            if (data.jumlah_item === 0) {
                                location.reload();
                            }
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            // Update totals
            function updateTotals() {
                let total = 0;
                const subtotals = document.querySelectorAll('.subtotal');

                subtotals.forEach(element => {
                    const harga = parseFloat(element.dataset.harga);
                    const jumlah = parseInt(element.dataset.jumlah);
                    total += harga * jumlah;
                });

                document.getElementById('totalHarga').textContent = `Rp ${formatNumber(total)}`;
                document.getElementById('totalTagihan').textContent = `Rp ${formatNumber(total)}`;
            }

            // Format number to currency
            function formatNumber(number) {
                return number.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&.');
            }

            // Update cart counter in header
            function updateCartCounter(count) {
                const cartCounter = document.querySelector('.cart-counter');
                if (cartCounter) {
                    if (count > 0) {
                        cartCounter.textContent = count;
                        cartCounter.classList.remove('hidden');
                    } else {
                        cartCounter.classList.add('hidden');
                    }
                }
            }
        });
    </script>
@endpush
