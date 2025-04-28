<header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-50">
    <div class="max-w-8xl mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <a href="/" class="flex items-center">
                <img src="https://ai-public.creatie.ai/gen_page/logo_placeholder.png" alt="ShoesStore Logo" class="h-10">
            </a>

            <div class="hidden lg:flex items-center space-x-8">
                <nav class="flex space-x-6"> <a href="#"
                        class="text-gray-700 hover:text-custom font-medium">Beranda</a>
                    <a href="#" class="text-gray-700 hover:text-custom font-medium">Kategori</a>
                    <a href="#" class="text-gray-700 hover:text-custom font-medium">Brand</a>
                    <a href="#" class="text-gray-700 hover:text-custom font-medium">Sale</a>
                </nav>

                <div class="relative">
                    <input type="text" placeholder="Cari produk..."
                        class="w-[400px] pl-4 pr-10 py-2 border border-gray-200 rounded-button focus:outline-none focus:border-custom">
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-custom">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <div class="flex items-center space-x-6">
                    {{-- <a href="#" class="relative text-gray-700 hover:text-custom"> <i
                            class="fas fa-shopping-cart text-xl"></i>
                        <span
                            class="absolute -top-2 -right-2 bg-custom text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </a> --}}
                    <a href="{{ route('keranjang.index') }}" class="relative text-gray-700 hover:text-custom">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        @php
                            $keranjangService = app(\App\Services\KeranjangService::class);
                            $jumlahKeranjang = $keranjangService->jumlahItem();
                        @endphp
                        @if ($jumlahKeranjang > 0)
                            <span
                                class="cart-counter absolute -top-2 -right-2 bg-custom text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $jumlahKeranjang }}
                            </span>
                        @else
                            <span
                                class="cart-counter absolute -top-2 -right-2 bg-custom text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">
                                0
                            </span>
                        @endif
                    </a>
                    <a href="#" class="text-gray-700 hover:text-custom">
                        <i class="fas fa-user text-xl"></i>
                    </a>
                    {{-- <button class="bg-custom text-white px-6 py-2 !rounded-button hover:bg-custom/90">Masuk</button> --}}
                </div>
            </div>
        </div>
    </div>
</header>

{{-- 
<header class="bg-white shadow">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-3">
            <div class="flex items-center">
                <a href="{{ route('pelanggan.beranda') }}" class="text-2xl font-bold text-custom">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10">
                </a>
                <nav class="hidden md:ml-6 md:flex space-x-4">
                    <a href="{{ route('pelanggan.beranda') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('beranda') ? 'text-custom' : 'text-gray-700 hover:text-custom' }}">
                        Beranda
                    </a>
                    <a href="{{ route('pelanggan.produk') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('produk') ? 'text-custom' : 'text-gray-700 hover:text-custom' }}">
                        Produk
                    </a>
                    <a href="#" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-custom">
                        Tentang Kami
                    </a>
                    <a href="#" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-custom">
                        Kontak
                    </a>
                </nav>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="{{ route('keranjang.index') }}" class="relative text-gray-700 hover:text-custom">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    @php
                        $keranjangService = app(\App\Services\KeranjangService::class);
                        $jumlahKeranjang = $keranjangService->jumlahItem();
                    @endphp
                    @if ($jumlahKeranjang > 0)
                    <span class="cart-counter absolute -top-2 -right-2 bg-custom text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {{ $jumlahKeranjang }}
                    </span>
                    @else
                    <span class="cart-counter absolute -top-2 -right-2 bg-custom text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">
                        0
                    </span>
                    @endif
                </a>
                
                @auth('pelanggan')
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-1 text-gray-700 hover:text-custom">
                        <img src="{{ auth()->guard('pelanggan')->user()->foto_profil 
                            ? asset('storage/' . auth()->guard('pelanggan')->user()->foto_profil) 
                            : asset('images/avatar-placeholder.png') }}" 
                            alt="Profile" class="h-8 w-8 rounded-full object-cover">
                        <span class="hidden md:block">{{ auth()->guard('pelanggan')->user()->nama_pelanggan }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                        <a href="{{ route('transaksi.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Pesanan Saya
                        </a>
                        <a href="{{ route('alamat.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Alamat Saya
                        </a>
                        <a href="{{ route('profil.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Profil Saya
                        </a>
                        <hr class="my-1">
                        <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Keluar
                        </a>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="hidden md:inline-block px-4 py-2 text-sm font-medium text-custom border border-custom rounded-md hover:bg-custom/10">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="hidden md:inline-block px-4 py-2 text-sm font-medium text-white bg-custom rounded-md hover:bg-custom/90">
                    Daftar
                </a>
                <button class="md:hidden text-gray-700" id="mobileMenuButton">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                @endauth
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobileMenu">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('pelanggan.beranda') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('beranda') ? 'text-custom' : 'text-gray-700 hover:text-custom' }}">
                    Beranda
                </a>
                <a href="{{ route('pelanggan.produk') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('produk') ? 'text-custom' : 'text-gray-700 hover:text-custom' }}">
                    Produk
                </a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-custom">
                    Tentang Kami
                </a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-custom">
                    Kontak
                </a>
                
                @guest('pelanggan')
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-custom">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-custom">
                        Daftar
                    </a>
                </div>
                @endguest
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script> --}}
