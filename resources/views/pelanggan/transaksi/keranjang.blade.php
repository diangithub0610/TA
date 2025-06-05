@extends('pelanggan.layouts.app')
@section('title', 'Keranjang')
@section('content')
<header class="bg-gray-50 border-b border-gray-200">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4">
            <div class="flex items-center">
                <h1 class="text-xl font-bold">Keranjang Belanja</h1>
            </div>
        </div>
    </div>
</header>
<main class="container mx-auto px-4 py-8">
    @if (count($keranjang) > 0)
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6">
                <!-- Cart Items -->
                <div class="space-y-6 divide-y divide-gray-200">
                    @foreach ($keranjang as $item)
                        <div class="flex items-start pt-6 first:pt-0">
                            <div class="flex-1">
                                <div class="flex">
                                    <div class="w-24 h-24 rounded overflow-hidden">
                                        <img src="{{ asset('storage/' . $item['gambar']) }}" 
                                            alt="{{ $item['nama_barang'] }}" 
                                            class="w-full h-full object-cover object-top">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="font-bold text-gray-800">{{ $item['nama_barang'] }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Ukuran: {{ $item['ukuran'] }} | Warna: 
                                            <span class="inline-block w-4 h-4 rounded-full ml-1 align-middle" 
                                                style="background-color: #{{ $item['kode_hex'] }}"></span>
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">Stok: {{ $item['stok'] }}</p>
                                        <p class="font-bold text-primary mt-1">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                        <div class="flex items-center justify-end mt-3 space-x-4">
                                            <div class="flex">
                                                <button type="button"
                                                    class="btn-minus w-8 h-8 flex items-center justify-center border border-gray-200 rounded-l-button text-gray-500 hover:bg-gray-50"
                                                    data-kode="{{ $item['kode_detail'] }}">
                                                    <i class="ri-subtract-line"></i>
                                                </button>
                                                <input type="number" class="input-jumlah w-12 h-8 border-y border-gray-200 text-center text-sm" 
                                                    min="1" max="{{ $item['stok'] }}" value="{{ $item['jumlah'] }}" 
                                                    data-kode="{{ $item['kode_detail'] }}" readonly>
                                                <button type="button"
                                                    class="btn-plus w-8 h-8 flex items-center justify-center border border-gray-200 rounded-r-button text-gray-500 hover:bg-gray-50"
                                                    data-kode="{{ $item['kode_detail'] }}" 
                                                    data-stok="{{ $item['stok'] }}">
                                                    <i class="ri-add-line"></i>
                                                </button>
                                            </div>
                                            <span class="subtotal font-medium text-primary" 
                                                data-kode="{{ $item['kode_detail'] }}" 
                                                data-harga="{{ $item['harga'] }}" 
                                                data-jumlah="{{ $item['jumlah'] }}">
                                                Rp {{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}
                                            </span>
                                            <button type="button" 
                                                class="btn-hapus w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500"
                                                data-kode="{{ $item['kode_detail'] }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            <input type="checkbox" 
                                                class="item-checkbox h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary"
                                                data-kode="{{ $item['kode_detail'] }}"
                                                data-harga="{{ $item['harga'] }}"
                                                data-jumlah="{{ $item['jumlah'] }}"
                                                checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Cart Summary -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-lg text-gray-800">Total</span>
                        <span class="font-bold text-xl text-primary" id="totalTagihan">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <a href="{{ route('checkout.index') }}" id="checkoutButton"
                        class="mt-6 w-full bg-primary text-white py-3 rounded-button font-medium hover:bg-primary/90 whitespace-nowrap text-center block">
                        Lanjut ke Pembayaran
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <!-- Heroicon: Shopping Cart (Outline) -->
            <svg xmlns="http://www.w3.org/2000/svg" 
                class="w-64 h-64 mx-auto mb-4 text-gray-400" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor" 
                stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" 
                   d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.34 5.36A1 1 0 007 20h10a1 1 0 001-1.25L17 13M9 21h.01M15 21h.01"/>
            </svg>

            <h2 class="text-xl font-medium mb-2">Keranjang Belanja Anda Kosong</h2>
            <p class="text-gray-500 mb-6">Silakan tambahkan barang ke keranjang terlebih dahulu</p>
            <a href="{{ route('keranjang.index') }}"
                class="inline-block bg-custom text-white py-2 px-6 rounded-lg hover:bg-custom/90">
                Lihat Produk
            </a>
        </div>
    @endif

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
        // Initialize checkboxes for selected items
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const checkoutButton = document.getElementById('checkoutButton');
        
        // Handle item quantity update
        const btnMinus = document.querySelectorAll('.btn-minus');
        const btnPlus = document.querySelectorAll('.btn-plus');
        const inputJumlah = document.querySelectorAll('.input-jumlah');

        // Selected items for checkout
        let selectedItems = [];
        
        // Initialize selected items (default all checked)
        checkboxes.forEach(checkbox => {
            const kodeDetail = checkbox.dataset.kode;
            const harga = parseFloat(checkbox.dataset.harga);
            const jumlah = parseInt(checkbox.dataset.jumlah);
            
            if(checkbox.checked) {
                selectedItems.push({
                    kode_detail: kodeDetail,
                    harga: harga,
                    jumlah: jumlah
                });
            }
            
            checkbox.addEventListener('change', function() {
                if(this.checked) {
                    selectedItems.push({
                        kode_detail: kodeDetail,
                        harga: harga,
                        jumlah: jumlah
                    });
                } else {
                    selectedItems = selectedItems.filter(item => item.kode_detail !== kodeDetail);
                }
                updateCheckoutTotal();
            });
        });

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
                    
                    // Update selected items data
                    updateSelectedItemQuantity(kodeDetail, jumlah);
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
                    
                    // Update selected items data
                    updateSelectedItemQuantity(kodeDetail, jumlah);
                }
            });
        });

        // Update selected item quantity
        function updateSelectedItemQuantity(kodeDetail, jumlah) {
            const index = selectedItems.findIndex(item => item.kode_detail === kodeDetail);
            if (index !== -1) {
                selectedItems[index].jumlah = jumlah;
                
                // Update checkbox data attribute
                const checkbox = document.querySelector(`.item-checkbox[data-kode="${kodeDetail}"]`);
                if (checkbox) {
                    checkbox.dataset.jumlah = jumlah;
                }
                
                updateCheckoutTotal();
            }
        }

        // Update checkout total based on selected items
        function updateCheckoutTotal() {
            let total = 0;
            
            selectedItems.forEach(item => {
                total += item.harga * item.jumlah;
            });
            
            document.getElementById('totalTagihan').textContent = `Rp ${formatNumber(total)}`;
            
            // Update checkout URL with selected items
            updateCheckoutUrl();
        }
        
        // Update checkout URL with selected items
        function updateCheckoutUrl() {
            if (checkoutButton) {
                const baseUrl = "{{ route('checkout.index') }}";
                const selectedCodes = selectedItems.map(item => item.kode_detail);
                
                if (selectedCodes.length > 0) {
                    const url = `${baseUrl}?items=${selectedCodes.join(',')}`;
                    checkoutButton.href = url;
                    checkoutButton.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    checkoutButton.href = '#';
                    checkoutButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

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

                        // Update selected items and total
                        updateSelectedItemQuantity(kodeDetail, jumlah);
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
                            `.input-jumlah[data-kode="${kodeDetail}"]`).closest('.flex.items-start');
                        itemElement.remove();

                        // Remove from selected items
                        selectedItems = selectedItems.filter(item => item.kode_detail !== kodeDetail);
                        updateCheckoutTotal();

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
        
        // Initialize checkout URL
        updateCheckoutUrl();
    });
</script>
@endpush