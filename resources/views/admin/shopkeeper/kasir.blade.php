@extends('admin.layouts.app')

@section('title', 'Kasir - Point of Sale')

@section('content')
    <div class="container-fluid py-4">
        <!-- Top Panel - Product Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Pilih Produk</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="searchProduct"
                                        placeholder="Cari produk...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="filterBrand">
                                    <option value="">Semua Brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->kode_brand }}">{{ $brand->nama_brand }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary w-100" id="btnFilter">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div class="row" id="productGrid">
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Memuat produk...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Panel - Cart and Transaction -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-cash-register me-2"></i>Keranjang Belanja</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Transaction Settings -->
                            <div class="col-lg-4 col-md-6">
                                <!-- Transaction Type -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jenis Transaksi</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="jenis_transaksi" id="offline"
                                            value="offline" checked>
                                        <label class="btn btn-outline-primary" for="offline">
                                            <i class="fas fa-store me-1"></i>Offline
                                        </label>
                                        <input type="radio" class="btn-check" name="jenis_transaksi" id="marketplace"
                                            value="marketplace">
                                        <label class="btn btn-outline-info" for="marketplace">
                                            <i class="fas fa-globe me-1"></i>Marketplace
                                        </label>
                                    </div>
                                </div>

                                <!-- Customer Selection (Only for Reseller) -->
                                <div class="mb-3" id="customerSection" style="display: none;">
                                    <label class="form-label fw-bold">Pelanggan Reseller</label>
                                    <select class="form-select" id="selectCustomer">
                                        <option value="">Pilih Reseller...</option>
                                        @foreach ($resellers as $reseller)
                                            <option value="{{ $reseller->id_pelanggan }}">{{ $reseller->nama_pelanggan }} -
                                                {{ $reseller->no_hp }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Marketplace Details -->
                                <div id="marketplaceDetails" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Keterangan Marketplace</label>
                                        <textarea class="form-control" id="keteranganMarketplace" rows="2"
                                            placeholder="Contoh: Shopee - Order #12345, Tokopedia - Atas nama John Doe"></textarea>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="isDropship">
                                        <label class="form-check-label" for="isDropship">
                                            Dropship
                                        </label>
                                    </div>

                                    <div id="dropshipDetails" style="display: none;">
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="form-label">Nama Pengirim</label>
                                                <input type="text" class="form-control" id="namaPengirim"
                                                    placeholder="Nama pengirim">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">No. HP Pengirim</label>
                                                <input type="text" class="form-control" id="noHpPengirim"
                                                    placeholder="081xxxxxxxx">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle Column - Cart Items -->
                            <div class="col-lg-5 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Item Pesanan</label>
                                    <div id="cartItems" class="border rounded p-3"
                                        style="min-height: 250px; max-height: 400px; overflow-y: auto;">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-cart fa-3x mb-2 opacity-25"></i>
                                            <p>Keranjang masih kosong</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Total & Actions -->
                            <div class="col-lg-3 col-md-12">
                                <!-- Total -->
                                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                    <span class="fw-bold fs-5">Total:</span>
                                    <span class="fw-bold fs-4 text-success" id="totalAmount">Rp 0</span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success btn-lg" id="btnProcess" disabled>
                                        <i class="fas fa-check me-2"></i>Proses Transaksi
                                    </button>
                                    <button class="btn btn-outline-danger" id="btnClear">
                                        <i class="fas fa-trash me-2"></i>Hapus Semua
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="productDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Transaksi Berhasil</h5>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h4>Transaksi Berhasil!</h4>
                        <p class="text-muted">Kode Transaksi: <strong id="transactionCode"></strong></p>
                        <p class="text-muted">Total: <strong id="transactionTotal"></strong></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnPrint">
                        <i class="fas fa-print me-2"></i>Cetak Struk
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let cart = [];
            let products = [];

            // Load products on page load
            loadProducts();

            // Event listeners
            $('#btnFilter, #searchProduct').on('click keyup', function(e) {
                if (e.type === 'click' || e.keyCode === 13) {
                    loadProducts();
                }
            });

            $('input[name="jenis_transaksi"]').change(function() {
                if ($(this).val() === 'marketplace') {
                    $('#marketplaceDetails').show();
                    $('#customerSection').show();
                } else {
                    $('#marketplaceDetails').hide();
                    $('#customerSection').hide();
                }
            });

            $('#isDropship').change(function() {
                if ($(this).is(':checked')) {
                    $('#dropshipDetails').show();
                } else {
                    $('#dropshipDetails').hide();
                }
            });

            $('#btnProcess').click(function() {
                processTransaction();
            });

            $('#btnClear').click(function() {
                if (confirm('Hapus semua item dari keranjang?')) {
                    cart = [];
                    updateCartDisplay();
                }
            });

            function loadProducts() {
                const search = $('#searchProduct').val();
                const brand = $('#filterBrand').val();

                $.ajax({
                    url: '{{ route('kasir.barang') }}',
                    method: 'GET',
                    data: {
                        search: search,
                        brand: brand
                    },
                    beforeSend: function() {
                        $('#productGrid').html(`
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat produk...</p>
                    </div>
                `);
                    },
                    success: function(data) {
                        products = data;
                        displayProducts(data);
                    },
                    error: function() {
                        $('#productGrid').html(`
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <p class="text-muted">Gagal memuat produk</p>
                    </div>
                `);
                    }
                });
            }

            function displayProducts(products) {
                if (products.length === 0) {
                    $('#productGrid').html(`
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open text-muted fa-3x mb-3"></i>
                    <p class="text-muted">Tidak ada produk ditemukan</p>
                </div>
            `);
                    return;
                }

                let html = '';
                products.forEach(function(product) {
                    const hasStock = product.detail_barang && product.detail_barang.some(d => d.stok > 0);
                    html += `
                <div class="col-md-6 col-lg-3 col-xl-2 mb-3">
                    <div class="card h-100 product-card ${!hasStock ? 'opacity-50' : ''}" data-kode="${product.kode_barang}">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                            ${product.gambar ? 
                                `<img src="/storage/${product.gambar}" class="img-fluid" style="max-height: 100%;">` : 
                                `<i class="fas fa-image text-muted fa-2x"></i>`
                            }
                        </div>
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1" style="font-size: 0.85rem;">${product.nama_barang}</h6>
                            <p class="card-text text-muted mb-1" style="font-size: 0.75rem;">${product.tipe ? product.tipe.brand.nama_brand : 'No Brand'}</p>
                            <p class="card-text fw-bold text-success mb-1" style="font-size: 0.85rem;">Rp ${Number(product.harga_normal).toLocaleString('id-ID')}</p>
                            <small class="text-muted" style="font-size: 0.7rem;">Stok: ${product.detail_barang ? product.detail_barang.reduce((sum, d) => sum + d.stok, 0) : 0}</small>
                        </div>
                        ${hasStock ? 
                            `<div class="card-footer p-2">
                                    <button class="btn btn-primary btn-sm w-100 btn-add-product">
                                        <i class="fas fa-plus me-1"></i>Tambah
                                    </button>
                                </div>` : 
                            `<div class="card-footer p-2">
                                    <button class="btn btn-secondary btn-sm w-100" disabled>Stok Habis</button>
                                </div>`
                        }
                    </div>
                </div>
            `;
                });
                $('#productGrid').html(html);
            }

            // Add product to cart
            $(document).on('click', '.btn-add-product', function() {
                const kodeBarang = $(this).closest('.product-card').data('kode');
                const product = products.find(p => p.kode_barang === kodeBarang);

                if (product.detail_barang.length === 1) {
                    // Direct add if only one variant
                    addToCart(product.detail_barang[0], product);
                } else {
                    // Show variant selection modal
                    showProductModal(product);
                }
            });

            function showProductModal(product) {
                let html = `
            <h5>${product.nama_barang}</h5>
            <p class="text-muted">${product.deskripsi || 'Tidak ada deskripsi'}</p>
            <hr>
            <h6>Pilih Varian:</h6>
        `;

                product.detail_barang.forEach(function(detail) {
                    if (detail.stok > 0) {
                        html += `
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Ukuran: ${detail.ukuran}</strong>
                                    ${detail.warna ? `<br><small>Warna: ${detail.warna.warna}</small>` : ''}
                                    <br><small class="text-muted">Stok: ${detail.stok}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">Rp ${Number(detail.harga_normal).toLocaleString('id-ID')}</div>
                                    <button class="btn btn-primary btn-sm mt-1 btn-add-variant" data-detail='${JSON.stringify(detail)}' data-product='${JSON.stringify(product)}'>
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    }
                });

                $('#productDetails').html(html);
                $('#productModal').modal('show');
            }

            $(document).on('click', '.btn-add-variant', function() {
                const detail = JSON.parse($(this).attr('data-detail'));
                const product = JSON.parse($(this).attr('data-product'));
                addToCart(detail, product);
                $('#productModal').modal('hide');
            });

            function addToCart(detail, product) {
                const existingItem = cart.find(item => item.kode_detail === detail.kode_detail);

                if (existingItem) {
                    if (existingItem.qty < detail.stok) {
                        existingItem.qty++;
                    } else {
                        alert('Stok tidak mencukupi!');
                        return;
                    }
                } else {
                    cart.push({
                        kode_detail: detail.kode_detail,
                        nama_barang: product.nama_barang,
                        ukuran: detail.ukuran,
                        warna: detail.warna ? detail.warna.warna : null,
                        harga: detail.harga_normal,
                        qty: 1,
                        stok: detail.stok
                    });
                }

                updateCartDisplay();
            }

            function updateCartDisplay() {
                if (cart.length === 0) {
                    $('#cartItems').html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-3x mb-2 opacity-25"></i>
                    <p>Keranjang masih kosong</p>
                </div>
            `);
                    $('#btnProcess').prop('disabled', true);
                    $('#totalAmount').text('Rp 0');
                    return;
                }

                let html = '';
                let total = 0;

                cart.forEach(function(item, index) {
                    const subtotal = item.harga * item.qty;
                    total += subtotal;

                    html += `
                <div class="card mb-2 cart-item">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-size: 0.9rem;">${item.nama_barang}</h6>
                                <small class="text-muted">
                                    Ukuran: ${item.ukuran}
                                    ${item.warna ? ` | Warna: ${item.warna}` : ''}
                                </small>
                                <div class="fw-bold text-success">Rp ${Number(item.harga).toLocaleString('id-ID')}</div>
                            </div>
                            <div class="text-end">
                                <div class="input-group input-group-sm mb-1" style="width: 100px;">
                                    <button class="btn btn-outline-secondary btn-qty-minus" data-index="${index}">-</button>
                                    <input type="number" class="form-control text-center qty-input" value="${item.qty}" min="1" max="${item.stok}" data-index="${index}">
                                    <button class="btn btn-outline-secondary btn-qty-plus" data-index="${index}">+</button>
                                </div>
                                <small class="text-success fw-bold">Rp ${Number(subtotal).toLocaleString('id-ID')}</small>
                                <button class="btn btn-outline-danger btn-sm btn-remove-item ms-1" data-index="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                });

                $('#cartItems').html(html);
                $('#totalAmount').text('Rp ' + Number(total).toLocaleString('id-ID'));
                $('#btnProcess').prop('disabled', false);
            }

            // Cart item controls
            $(document).on('click', '.btn-qty-plus', function() {
                const index = $(this).data('index');
                if (cart[index].qty < cart[index].stok) {
                    cart[index].qty++;
                    updateCartDisplay();
                }
            });

            $(document).on('click', '.btn-qty-minus', function() {
                const index = $(this).data('index');
                if (cart[index].qty > 1) {
                    cart[index].qty--;
                    updateCartDisplay();
                }
            });

            $(document).on('change', '.qty-input', function() {
                const index = $(this).data('index');
                const newQty = parseInt($(this).val());
                if (newQty >= 1 && newQty <= cart[index].stok) {
                    cart[index].qty = newQty;
                    updateCartDisplay();
                } else {
                    $(this).val(cart[index].qty);
                }
            });

            $(document).on('click', '.btn-remove-item', function() {
                const index = $(this).data('index');
                cart.splice(index, 1);
                updateCartDisplay();
            });

            function processTransaction() {
                if (cart.length === 0) {
                    alert('Keranjang masih kosong!');
                    return;
                }

                const data = {
                    items: cart,
                    jenis_transaksi: $('input[name="jenis_transaksi"]:checked').val(),
                    id_pelanggan: $('#selectCustomer').val() || null,
                    keterangan_marketplace: $('#keteranganMarketplace').val() || null,
                    is_dropship: $('#isDropship').is(':checked'),
                    nama_pengirim: $('#namaPengirim').val() || null,
                    no_hp_pengirim: $('#noHpPengirim').val() || null,
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: '{{ route('kasir.store') }}',
                    method: 'POST',
                    data: data,
                    beforeSend: function() {
                        $('#btnProcess').prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#transactionCode').text(response.kode_transaksi);
                            $('#transactionTotal').text('Rp ' + Number(response.total).toLocaleString(
                                'id-ID'));
                            $('#successModal').modal('show');

                            // Reset form
                            cart = [];
                            updateCartDisplay();
                            $('input[name="jenis_transaksi"][value="offline"]').prop('checked', true)
                                .trigger('change');
                            $('#selectCustomer').val('');
                            $('#keteranganMarketplace').val('');
                            $('#isDropship').prop('checked', false).trigger('change');
                            $('#namaPengirim, #noHpPengirim').val('');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        alert(response.message || 'Terjadi kesalahan!');
                    },
                    complete: function() {
                        $('#btnProcess').prop('disabled', false).html(
                            '<i class="fas fa-check me-2"></i>Proses Transaksi');
                    }
                });
            }

            $('#btnPrint').click(function() {
                const kodeTransaksi = $('#transactionCode').text();
                window.open('{{ route('kasir.print', ':kode') }}'.replace(':kode', kodeTransaksi),
                '_blank');
            });
        });
    </script>
@endpush