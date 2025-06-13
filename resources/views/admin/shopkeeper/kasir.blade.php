@extends('admin.layouts.app')

@section('title', 'Kasir - Point of Sale')
@section('content')

    @push('styles')
        <style>
            /* Custom CSS untuk Sistem Kasir */

            /* Animasi dan Transisi */
            * {
                transition: all 0.3s ease;
            }

            /* Card Styling */
            .card-shadow {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                border: none;
                border-radius: 15px;
                overflow: hidden;
            }

            .card-shadow:hover {
                box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
                transform: translateY(-2px);
            }

            /* Product Card Animations */
            .product-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .product-card:hover {
                transform: translateY(-8px) scale(1.02);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                border-color: #667eea;
            }

            .product-image {
                transition: transform 0.4s ease;
            }

            .product-card:hover .product-image {
                transform: scale(1.1);
            }

            /* Header Gradients */
            .cart-header {
                background: blue;
                position: relative;
                overflow: hidden;
            }

            .cart-header::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: blue;
                /* animation: shimmer 3s infinite; */
            }

            .product-header {
                background: blue;
                position: relative;
                overflow: hidden;
            }

            .product-header::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: blue;
                /* animation: shimmer 3s infinite; */
            }

            @keyframes shimmer {
                0% {
                    left: -100%;
                }

                100% {
                    left: 100%;
                }
            }

            /* Button Styles */
            .btn-gradient {
                background: linear-gradient(45deg, #667eea, #764ba2);
                border: none;
                color: white;
                position: relative;
                overflow: hidden;
                z-index: 1;
            }

            .btn-gradient::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(45deg, #764ba2, #667eea);
                z-index: -1;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .btn-gradient:hover::before {
                opacity: 1;
            }

            .btn-gradient:hover {
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            }

            /* Cart Item Styling */
            .cart-item {
                border-left: 4px solid #667eea;
                background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
                transition: all 0.3s ease;
            }

            .cart-item:hover {
                border-left-color: #f5576c;
                background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
                transform: translateX(5px);
            }

            /* Quantity Controls */
            .quantity-control {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .quantity-btn {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                border: none;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f9fa;
                color: #495057;
                transition: all 0.3s ease;
            }

            .quantity-btn:hover {
                background: #667eea;
                color: white;
                transform: scale(1.1);
            }

            /* Search Input */
            .search-container {
                position: relative;
            }

            .search-input {
                padding-left: 50px;
                border: 2px solid #e9ecef;
                border-radius: 25px;
                transition: all 0.3s ease;
            }

            .search-input:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
                transform: scale(1.02);
            }

            .search-icon {
                position: absolute;
                left: 18px;
                top: 50%;
                transform: translateY(-50%);
                color: #6c757d;
                z-index: 2;
            }

            /* Transaction Type Cards */
            .transaction-type-card {
                border: 2px solid #e9ecef;
                cursor: pointer;
                transition: all 0.3s ease;
                background: white;
            }

            .transaction-type-card:hover {
                border-color: #667eea;
                background: #f8f9ff;
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
            }

            .transaction-type-card.active {
                border-color: #667eea;
                background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
            }

            /* Marketplace Options */
            .marketplace-option {
                border: 2px solid #e9ecef;
                cursor: pointer;
                transition: all 0.3s ease;
                background: white;
            }

            .marketplace-option:hover {
                border-color: #28a745;
                background: #f8fff9;
                transform: translateY(-3px);
            }

            .marketplace-option.bg-primary {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
                border-color: #007bff;
                transform: scale(1.05);
            }

            /* Scrollbar Styling */
            #productContainer::-webkit-scrollbar,
            #cartItems::-webkit-scrollbar {
                width: 8px;
            }

            #productContainer::-webkit-scrollbar-track,
            #cartItems::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            #productContainer::-webkit-scrollbar-thumb,
            #cartItems::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #667eea, #764ba2);
                border-radius: 10px;
            }

            #productContainer::-webkit-scrollbar-thumb:hover,
            #cartItems::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #764ba2, #667eea);
            }

            /* Badge Animations */
            #cartCount {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.1);
                }

                100% {
                    transform: scale(1);
                }
            }

            /* Empty Cart State */
            #emptyCart {
                opacity: 0.6;
                animation: fadeIn 0.5s ease-in;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 0.6;
                    transform: translateY(0);
                }
            }

            /* Loading States */
            .btn:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }

            /* Variant Modal */
            .variant-card {
                border: 2px solid transparent;
                transition: all 0.3s ease;
            }

            .variant-card:hover {
                border-color: #667eea;
                background: #f8f9ff;
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .card-shadow {
                    margin-bottom: 20px;
                }

                .product-card:hover {
                    transform: translateY(-4px) scale(1.01);
                }

                .search-input {
                    padding-left: 45px;
                }

                .search-icon {
                    left: 15px;
                }
            }

            /* Success/Error States */
            .text-success {
                color: #28a745 !important;
                font-weight: 600;
            }

            .text-danger {
                color: #dc3545 !important;
                font-weight: 600;
            }

            /* Form Controls */
            .form-control:focus,
            .form-select:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            }

            /* Price Display */
            .text-primary {
                color: #667eea !important;
                font-weight: 700;
            }

            /* Total Amount */
            #totalAmount {
                font-size: 1.5rem;
                background: linear-gradient(45deg, #667eea, #764ba2);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
        </style>
    @endpush
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Card Pilih Produk -->
            <div class="col-lg-7 mb-4">
                <div class="card card-shadow h-100">
                    <div class="card-header product-header p-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shopping-cart me-3 fs-4"></i>
                            <h5 class="mb-0 fw-bold">Pilih Produk</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search dan Filter -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="search-container">
                                    <input type="text" id="searchInput" class="form-control search-input"
                                        placeholder="Cari produk...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="brandFilter" class="form-select">
                                    <option value="">Semua Brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->kode_tipe }}">{{ $brand->nama_tipe }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                
                        <!-- Product Grid -->
                        <div id="productContainer">
                            <div class="row" id="productGrid">
                                @foreach ($products as $product)
                                    @php
                                        $stok = $product->detailBarang->sum('stok');
                                        $isOutOfStock = $stok <= 0;
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-3 product-item">
                                        <div class="card product-card h-100 {{ $isOutOfStock ? 'bg-light text-muted' : '' }}"
                                            data-product="{{ json_encode([
                                                'kode_barang' => $product->kode_barang,
                                                'nama_barang' => $product->nama_barang,
                                                'harga_normal' => $product->harga_normal,
                                                'gambar' => $product->gambar,
                                                'brand' => $product->tipe->nama_tipe ?? '',
                                                'stok' => $stok,
                                            ]) }}">
                                            @if ($product->gambar)
                                                <img src="{{ asset('storage/' . $product->gambar) }}"
                                                    class="card-img-top product-image" alt="{{ $product->nama_barang }}"
                                                    style="{{ $isOutOfStock ? 'filter: grayscale(100%); opacity: 0.5;' : '' }}">
                                            @else
                                                <div
                                                    class="product-image bg-secondary d-flex align-items-center justify-content-center {{ $isOutOfStock ? 'opacity-50' : '' }}">
                                                    <i class="fas fa-image text-white fs-1"></i>
                                                </div>
                                            @endif
                                            <div class="card-body p-3 d-flex flex-column">
                                                <h6 class="card-title mb-2 fw-bold">{{ $product->nama_barang }}</h6>
                                                <p class="text-muted mb-1 small">
                                                    {{ $product->tipe->nama_tipe ?? 'No Brand' }}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold text-primary">Rp
                                                        {{ number_format($product->harga_normal, 0, ',', '.') }}</span>
                                                    <small class="text-muted">Stok: {{ $stok }}</small>
                                                </div>
                                                <div class="mt-auto">
                                                    <button class="btn btn-sm w-100 mt-2 add-product-btn {{ $isOutOfStock ? 'btn-secondary' : 'btn-primary' }}"
                                                        {{ $isOutOfStock ? 'disabled' : '' }}>
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ $isOutOfStock ? 'Stok Habis' : 'Tambah' }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Card Keranjang Belanja -->
            <div class="col-lg-5">
                <div class="card card-shadow h-100">
                    <div class="card-header cart-header p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-basket me-3 fs-4"></i>
                                <h5 class="mb-0 fw-bold">Keranjang Belanja</h5>
                            </div>
                            <span id="cartCount" class="badge bg-light text-dark rounded-pill">0</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Jenis Transaksi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Jenis Transaksi</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="transaction-type-card card text-center p-2 active" data-type="offline">
                                        <i class="fas fa-store fs-4 text-primary mb-1"></i>
                                        <small class="fw-bold">Offline</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="transaction-type-card card text-center p-2" data-type="marketplace">
                                        <i class="fas fa-globe fs-4 text-success mb-1"></i>
                                        <small class="fw-bold">Marketplace</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ID Reseller (Offline) -->
                        <div id="resellerSection" class="mb-3">
                            <label class="form-label">ID Reseller (Opsional)</label>
                            <div class="input-group">
                                <input type="text" id="resellerInput" class="form-control"
                                    placeholder="Masukkan ID Reseller">
                                <button class="btn btn-outline-primary" id="checkResellerBtn" type="button">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                            <div id="resellerStatus" class="mt-1"></div>
                        </div>

                        <!-- Marketplace Options -->
                        <div id="marketplaceSection" class="mb-3" style="display: none;">
                            <label class="form-label">Pilih Marketplace</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="marketplace-option card text-center p-2" data-marketplace="shopee">
                                        <i class="fab fa-shopify fs-4 text-orange mb-1"></i>
                                        <small class="fw-bold">Shopee</small>
                                        <input type="radio" name="marketplace" value="shopee" class="d-none">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="marketplace-option card text-center p-2" data-marketplace="tokopedia">
                                        <i class="fas fa-shopping-bag fs-4 text-success mb-1"></i>
                                        <small class="fw-bold">Tokopedia</small>
                                        <input type="radio" name="marketplace" value="tokopedia" class="d-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea id="keteranganInput" class="form-control" rows="2" placeholder="Keterangan tambahan..."></textarea>
                        </div>

                        <!-- Cart Items -->
                        <div id="cartItems" class="mb-3">
                            <div id="emptyCart" class="text-center text-muted py-4">
                                <i class="fas fa-shopping-cart fs-1 mb-3"></i>
                                <p>Keranjang masih kosong</p>
                            </div>
                        </div>

                        <!-- Total dan Actions -->
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold fs-5">Total:</span>
                                <span id="totalAmount" class="fw-bold fs-5 text-primary">Rp 0</span>
                            </div>
                            <div class="d-grid gap-2">
                                <button id="processTransactionBtn" class="btn btn-primary btn-lg" disabled>
                                    <i class="fas fa-credit-card me-2"></i>
                                    Proses Transaksi
                                </button>
                                <button id="clearCartBtn" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>
                                    Hapus Semua
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Varian Produk -->
    <div class="modal fade" id="variantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Varian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="variantList"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let cart = [];
        let currentProduct = null;
        let transactionType = 'offline';
        let selectedMarketplace = '';
        let resellerDiscount = 0;

        // CSRF Token Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // Search functionality
            $('#searchInput').on('input', function() {
                searchProducts();
            });

            // Brand filter
            $('#brandFilter').change(function() {
                searchProducts();
            });

            // Transaction type selection
            $('.transaction-type-card').click(function() {
                $('.transaction-type-card').removeClass('active');
                $(this).addClass('active');
                transactionType = $(this).data('type');

                if (transactionType === 'offline') {
                    $('#resellerSection').show();
                    $('#marketplaceSection').hide();
                } else {
                    $('#resellerSection').hide();
                    $('#marketplaceSection').show();
                }
            });

            // Marketplace selection
            $('.marketplace-option').click(function() {
                $('.marketplace-option').removeClass('bg-primary text-white');
                $(this).addClass('bg-primary text-white');
                selectedMarketplace = $(this).data('marketplace');
                $('input[name="marketplace"][value="' + selectedMarketplace + '"]').prop('checked', true);

                // Update keterangan
                let currentKeterangan = $('#keteranganInput').val();
                if (!currentKeterangan.includes(selectedMarketplace)) {
                    $('#keteranganInput').val(selectedMarketplace.charAt(0).toUpperCase() +
                        selectedMarketplace.slice(1));
                }
            });

            // Check reseller
            $('#checkResellerBtn').click(function() {
                checkReseller();
            });

            // Add product to cart
            $(document).on('click', '.add-product-btn', function() {
                const productCard = $(this).closest('.product-card');
                currentProduct = JSON.parse(productCard.attr('data-product'));
                loadProductVariants(currentProduct.kode_barang);
            });

            // Process transaction
            $('#processTransactionBtn').click(function() {
                processTransaction();
            });

            // Clear cart
            $('#clearCartBtn').click(function() {
                clearCart();
            });

            // Remove item from cart
            $(document).on('click', '.remove-item', function() {
                const index = $(this).data('index');
                removeFromCart(index);
            });

            // Quantity controls
            $(document).on('click', '.quantity-plus', function() {
                const index = $(this).data('index');
                updateQuantity(index, 1);
            });

            $(document).on('click', '.quantity-minus', function() {
                const index = $(this).data('index');
                updateQuantity(index, -1);
            });
        });

        function searchProducts() {
            const search = $('#searchInput').val();
            const brand = $('#brandFilter').val();

            $.get('{{ route('kasir.search') }}', {
                    search: search,
                    brand: brand
                })
                .done(function(data) {
                    updateProductGrid(data.products);
                })
                .fail(function() {
                    alert('Error searching products');
                });
        }

        function updateProductGrid(products) {
            const grid = $('#productGrid');
            grid.empty();

            products.forEach(function(product) {
                const productHtml = `
                    <div class="col-md-6 col-lg-4 mb-3 product-item">
                        <div class="card product-card h-100" data-product='${JSON.stringify(product)}'>
                            ${product.gambar ? 
                                `<img src="/storage/${product.gambar}" class="card-img-top product-image" alt="${product.nama_barang}">` :
                                `<div class="product-image bg-secondary d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image text-white fs-1"></i>
                                        </div>`
                            }
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2 fw-bold">${product.nama_barang}</h6>
                                <p class="text-muted mb-1 small">${product.brand || 'No Brand'}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">Rp ${new Intl.NumberFormat('id-ID').format(product.harga_normal)}</span>
                                    <small class="text-muted">Stok: ${product.stok}</small>
                                </div>
                                <button class="btn btn-primary btn-sm w-100 mt-2 add-product-btn">
                                    <i class="fas fa-plus me-1"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                grid.append(productHtml);
            });
        }

        function loadProductVariants(kode_barang) {
            $.get(`{{ url('kasir/product-variants') }}/${kode_barang}`)
                .done(function(data) {
                    showVariantModal(data.variants);
                })
                .fail(function() {
                    alert('Error loading variants');
                });
        }

        function showVariantModal(variants) {
            const variantList = $('#variantList');
            variantList.empty();

            if (variants.length === 0) {
                variantList.html('<p class="text-muted">Tidak ada varian tersedia</p>');
                return;
            }

            variants.forEach(function(variant) {
                const variantHtml = `
                    <div class="card mb-2 variant-card" style="cursor: pointer;" data-variant='${JSON.stringify(variant)}'>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Ukuran: ${variant.ukuran}</strong>
                                    <span class="badge bg-secondary variant-badge ms-2">${variant.warna}</span>
                                    <br>
                                    <small class="text-muted">Stok: ${variant.stok}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary">Rp ${new Intl.NumberFormat('id-ID').format(variant.harga_normal)}</div>
                                    <button class="btn btn-sm btn-gradient mt-1 add-variant-btn">Tambah</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                variantList.append(variantHtml);
            });

            // Add click handler for variant selection
            $('.add-variant-btn').click(function(e) {
                e.stopPropagation();
                const variantCard = $(this).closest('.variant-card');
                const variant = JSON.parse(variantCard.attr('data-variant'));
                addToCart(currentProduct, variant);
                $('#variantModal').modal('hide');
            });

            $('#variantModal').modal('show');
        }

        function addToCart(product, variant) {
            // Tentukan harga yang akan digunakan
            let finalPrice = variant.harga_normal;

            // Jika ada reseller yang valid, gunakan harga reseller
            const resellerID = $('#resellerInput').val().trim();
            if (resellerID && resellerDiscount > 0) {
                // Ambil harga reseller dari server
                $.post('{{ route('kasir.reseller-price') }}', {
                        kode_detail: variant.kode_detail,
                        id_reseller: resellerID,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                    .done(function(data) {
                        if (!data.error) {
                            finalPrice = data.harga_reseller;

                            // Check if item already exists in cart
                            const existingIndex = cart.findIndex(item =>
                                item.kode_detail === variant.kode_detail
                            );

                            if (existingIndex !== -1) {
                                cart[existingIndex].quantity += 1;
                            } else {
                                cart.push({
                                    kode_detail: variant.kode_detail,
                                    nama_produk: product.nama_barang,
                                    ukuran: variant.ukuran,
                                    warna: variant.warna,
                                    price: finalPrice,
                                    harga_normal: variant.harga_normal,
                                    quantity: 1,
                                    stok: variant.stok
                                });
                            }

                            updateCartDisplay();
                        }
                    });
            } else {
                // Gunakan harga normal
                const existingIndex = cart.findIndex(item =>
                    item.kode_detail === variant.kode_detail
                );

                if (existingIndex !== -1) {
                    cart[existingIndex].quantity += 1;
                } else {
                    cart.push({
                        kode_detail: variant.kode_detail,
                        nama_produk: product.nama_barang,
                        ukuran: variant.ukuran,
                        warna: variant.warna,
                        price: finalPrice,
                        harga_normal: variant.harga_normal,
                        quantity: 1,
                        stok: variant.stok
                    });
                }

                updateCartDisplay();
            }
        }

        function updateCartDisplay() {
            const cartItems = $('#cartItems');
            const emptyCart = $('#emptyCart');

            if (cart.length === 0) {
                emptyCart.show();
                cartItems.find('.cart-item').remove();
                $('#cartCount').text('0');
                $('#totalAmount').text('Rp 0');
                $('#processTransactionBtn').prop('disabled', true);
                return;
            }

            emptyCart.hide();
            cartItems.find('.cart-item').remove();

            let total = 0;
            cart.forEach(function(item, index) {
                const subtotal = item.price * item.quantity;
                total += subtotal;

                // Tampilkan info diskon jika ada
                let priceDisplay =
                    `<span class="fw-bold">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</span>`;
                if (item.harga_normal && item.price < item.harga_normal) {
                    priceDisplay = `
                <span class="fw-bold text-success">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</span>
                <br><small class="text-muted text-decoration-line-through">Rp ${new Intl.NumberFormat('id-ID').format(item.harga_normal)}</small>
            `;
                }

                const itemHtml = `
            <div class="cart-item card mb-2 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${item.nama_produk}</h6>
                        <small class="text-muted">(${item.ukuran}, ${item.warna})</small>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div>${priceDisplay}</div>
                            <div class="quantity-control">
                                <button class="btn btn-sm btn-outline-secondary quantity-minus" data-index="${index}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="mx-2">${item.quantity}</span>
                                <button class="btn btn-sm btn-outline-secondary quantity-plus" data-index="${index}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-primary fw-bold">Subtotal: Rp ${new Intl.NumberFormat('id-ID').format(subtotal)}</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger ms-2 remove-item" data-index="${index}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
                cartItems.append(itemHtml);
            });

            $('#cartCount').text(cart.length);
            $('#totalAmount').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
            $('#processTransactionBtn').prop('disabled', false);
        }

        function updateQuantity(index, change) {
            if (cart[index].quantity + change <= 0) {
                removeFromCart(index);
                return;
            }

            if (cart[index].quantity + change > cart[index].stok) {
                alert('Stok tidak mencukupi!');
                return;
            }

            cart[index].quantity += change;
            updateCartDisplay();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        // function clearCart() {
        //     if (confirm('Yakin ingin mengosongkan keranjang?')) {
        //         cart = [];
        //         updateCartDisplay();
        //     }
        // }
        function clearCart() {
    cart = [];
    updateCartDisplay();
}


        function checkReseller() {
            const resellerID = $('#resellerInput').val().trim();
            const statusDiv = $('#resellerStatus');

            if (!resellerID) {
                statusDiv.html('<small class="text-muted">ID reseller kosong</small>');
                resellerDiscount = 0;
                // Reset harga di cart ke harga normal
                cart.forEach(item => {
                    if (item.harga_normal) {
                        item.price = item.harga_normal;
                    }
                });
                updateCartDisplay();
                return;
            }

            $.post('{{ route('kasir.check-reseller') }}', {
                    id_reseller: resellerID,
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(data) {
                    if (data.valid) {
                        statusDiv.html(
                            `<small class="text-success"><i class="fas fa-check"></i> ${data.nama} - Diskon ${data.discount}%</small>`
                        );
                        resellerDiscount = data.discount;

                        // Update harga di cart dengan harga reseller
                        updateCartPricesForReseller(resellerID);
                    } else {
                        statusDiv.html(
                            '<small class="text-danger"><i class="fas fa-times"></i> ID reseller tidak valid</small>'
                        );
                        resellerDiscount = 0;
                        // Reset ke harga normal
                        cart.forEach(item => {
                            if (item.harga_normal) {
                                item.price = item.harga_normal;
                            }
                        });
                        updateCartDisplay();
                    }
                })
                .fail(function() {
                    statusDiv.html('<small class="text-danger">Error checking reseller</small>');
                    resellerDiscount = 0;
                    updateCartDisplay();
                });
        }

        function updateCartPricesForReseller(resellerID) {
            if (cart.length === 0) return;

            let promises = cart.map(item => {
                return $.post('{{ route('kasir.reseller-price') }}', {
                    kode_detail: item.kode_detail,
                    id_reseller: resellerID,
                    _token: $('meta[name="csrf-token"]').attr('content')
                });
            });

            Promise.all(promises).then(responses => {
                responses.forEach((data, index) => {
                    if (!data.error) {
                        cart[index].price = data.harga_reseller;
                        cart[index].harga_normal = data.harga_normal;
                    }
                });
                updateCartDisplay();
            });
        }

        function processTransaction() {
            if (cart.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }

            // Validate marketplace selection if marketplace type is selected
            if (transactionType === 'marketplace' && !selectedMarketplace) {
                alert('Pilih marketplace terlebih dahulu!');
                return;
            }

            const transactionData = {
                items: cart,
                jenis_transaksi: transactionType,
                marketplace: selectedMarketplace,
                id_reseller: $('#resellerInput').val().trim() || null,
                keterangan: $('#keteranganInput').val().trim()
            };

            $('#processTransactionBtn').prop('disabled', true).html(
                '<i class="fas fa-spinner fa-spin me-2"></i>Processing...');

            $.ajax({
                    url: '{{ route('kasir.process') }}',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(transactionData),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .done(function(data) {
    if (data.success) {
        alert(`Transaksi berhasil!\nKode Transaksi: ${data.kode_transaksi}`);

        // Buka jendela baru untuk cetak struk
        window.open(`/kasir/print/${data.kode_transaksi}`, '_blank');

        // Reset form
        clearCart();
        $('#resellerInput').val('');
        $('#keteranganInput').val('');
        $('#resellerStatus').empty();
        $('.marketplace-option').removeClass('bg-primary text-white');
        selectedMarketplace = '';
        resellerDiscount = 0;

        // Refresh products to update stock
        searchProducts();
    } else {
        alert('Error: ' + data.message);
    }
})

                .fail(function(xhr) {
                    const response = xhr.responseJSON;
                    alert('Error: ' + (response?.message || 'Terjadi kesalahan sistem'));
                })
                .always(function() {
                    $('#processTransactionBtn').prop('disabled', false).html(
                        '<i class="fas fa-credit-card me-2"></i>Proses Transaksi');
                });
        }
    </script>
@endsection
