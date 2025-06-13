<header class="bg-primary shadow-sm">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center">
                {{-- <a href="{{  route('pelanggan.beranda') }}" class="text-3xl font-['roboto'] text-primary mr-8">Warrior Footwear</a> --}}
                <a href="{{ route('pelanggan.beranda') }}" class="flex items-center mr-8">
                    <img src="{{ asset('img/logo/logo-wf.png') }}" alt="navbar brand" class="h-10 w-auto object-contain" />
                </a>

                <!-- Search Bar -->
                <form action="{{ route('pelanggan.barang') }}" method="GET" class="relative hidden md:block">
                    <input type="text" name="cari" value="{{ request('cari') }}"
                        placeholder="Cari produk atau brand..."
                        class="w-80 pl-4 pr-10 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                    <button type="submit"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center text-gray-500 hover:text-primary">
                        <i class="ri-search-line"></i>
                    </button>
                </form>
            </div>

            <!-- Icons -->
            <div class="flex items-center space-x-4">
                <a href="{{ route('keranjang.index') }}"
                    class="relative w-10 h-10 flex items-center justify-center group">
                    <!-- Ikon -->
                    <i
                        class="ri-shopping-cart-2-line ri-xl text-gray-700 group-hover:text-white transition-colors duration-200"></i>
                        @php
                        $jumlahKeranjang = collect(session('keranjang', []))->sum('jumlah');
                    @endphp
                    <!-- Badge jumlah -->
                    <span
                        class="absolute -top-1 -right-1 bg-primary text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        {{  $jumlahKeranjang }}
                    </span>
                </a>
                <div class="relative group">
                    @auth('pelanggan')
                        @php
                            $user = auth('pelanggan')->user();
                            $isReseller = $user->role === 'reseller';
                        @endphp
                
                        <a href="#"
                           class="w-10 h-10 flex items-center justify-center group">
                            @if ($isReseller)
                                <!-- Ikon khusus reseller -->
                                <i class="ri-user-star-line ri-xl text-gray-700 group-hover:text-white transition-colors duration-200"></i>
                            @else
                                <!-- Ikon standar pelanggan biasa -->
                                <i class="ri-user-3-line ri-xl text-gray-700 group-hover:text-white transition-colors duration-200"></i>
                            @endif
                        </a>
                    @endauth
                
                    @guest('pelanggan')
                        <!-- Tampilkan ikon default jika belum login -->
                        <a href="#" class="w-10 h-10 flex items-center justify-center group">
                            <i class="ri-user-3-line ri-xl text-gray-700 group-hover:text-white transition-colors duration-200"></i>
                        </a>
                    @endguest
                
                    <!-- Dropdown Menu -->
                    <div
                        class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200 z-50">
                
                        @auth('pelanggan')
                            <!-- Jika pelanggan sudah login -->
                            <a href="{{ route('profil.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                            <a href="{{ route('transaksi.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Pesanan Saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        @endauth
                
                        @guest('pelanggan')
                            <!-- Jika pelanggan belum login -->
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Login</a>
                            <a href="{{ route('register') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Register</a>
                        @endguest
                
                    </div>
                </div>
                

            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div class="mt-3 md:hidden relative">
            <input type="text" placeholder="Cari produk..."
                class="w-full pl-4 pr-10 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-1 focus:ring-primary">
            <button
                class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 flex items-center justify-center text-gray-500">
                <i class="ri-search-line"></i>
            </button>
        </div>
    </div>
</header>

<!-- Brand Navigation -->
<div class="border-t border-b border-gray-200 bg-white">
    <div class="container mx-auto px-4">
        <div class="brand-list py-2 flex items-center space-x-6 text-sm text-gray-600">
            <a href="{{ route('pelanggan.barang') }}" class="whitespace-nowrap hover:text-primary">
                Produk
            </a>
            @foreach ($brands as $brand)
                <a href="{{ route('pelanggan.produk.byBrand', $brand->kode_brand) }}"
                    class="whitespace-nowrap hover:text-primary">
                    {{ $brand->nama_brand }}
                </a>
            @endforeach

        </div>
    </div>
</div>
