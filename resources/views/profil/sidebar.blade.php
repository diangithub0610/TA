<!-- Sidebar -->
<aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0">
    <div class="p-6 flex flex-col items-center border-b border-gray-200">
        <div class="w-24 h-24 rounded-full overflow-hidden mb-4">
            @if ($pelanggan->foto_profil)
                <img src="{{ asset('storage/profil/' . $pelanggan->foto_profil) }}" alt="Profile"
                    class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gray-200 flex items-center justify-center text-3xl font-bold text-gray-600">
                    {{ strtoupper(substr($pelanggan->nama_pelanggan, 0, 1)) }}
                </div>
            @endif
        </div>
        <h2 class="font-medium">{{ $pelanggan->nama_pelanggan }}</h2>
    </div>

    @php
        $dropdownActive =
            Request::routeIs('profil.index') ||
            Request::routeIs('profil.alamat') ||
            Request::routeIs('change-password');
    @endphp

    <nav class="p-4">
        <div class="space-y-2">
            <!-- Account Menu -->
            <div class="relative">
                <button id="dropdownButton"
                    class="w-full flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center mr-3">
                            <i class="ri-user-3-line"></i>
                        </div>
                        <span>Akun Saya</span>
                    </div>
                    <i id="dropdownIcon"
                        class="ri-arrow-down-s-line transition-transform duration-200 {{ $dropdownActive ? 'rotate-180' : '' }}">
                    </i>
                </button>

                <!-- Dropdown Menu (inline, bukan absolute) -->
                <div id="dropdownMenu" class="mt-1 pl-8 space-y-1 {{ $dropdownActive ? 'flex' : 'hidden' }} flex-col">
                    <a href="{{ route('profil.index') }}"
                        class="block px-4 py-2 text-sm {{ Request::routeIs('profil.index') ? 'text-gray-900 font-medium' : 'text-gray-700' }} hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        Profil
                    </a>
                    <a href="{{ route('profil.alamat') }}"
                        class="block px-4 py-2 text-sm {{ Request::routeIs('profil.alamat') ? 'text-gray-900 font-medium' : 'text-gray-700' }} hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        Alamat
                    </a>
                    <a href="{{ route('change-password') }}"
                        class="block px-4 py-2 text-sm {{ Request::routeIs('change-password') ? 'text-gray-900 font-medium' : 'text-gray-700' }} hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        Ubah Password
                    </a>
                </div>
            </div>

            <!-- Menu Pesanan Saya -->
            <a href="{{ route('transaksi.index') }}"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                <div class="w-5 h-5 flex items-center justify-center mr-3">
                    <i class="ri-file-list-3-line"></i>
                </div>
                <span>Pesanan Saya</span>
            </a>

            <!-- Upgrade Reseller Button (tampilkan hanya jika pelanggan biasa) -->
            @auth('pelanggan')
                @php
                    $user = auth('pelanggan')->user();
                    $isReseller = $user->role === 'reseller';
                @endphp

                @if (!$isReseller)
                    <div class="px-4">
                        <a href="{{ route('register.store', $user->id_pelanggan) }}"
                            class="block w-full text-center bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            Upgrade Reseller
                        </a>
                    </div>
                @endif
            @endauth


            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <div class="w-5 h-5 flex items-center justify-center mr-3">
                        <i class="ri-logout-box-r-line"></i>
                    </div>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>
</aside>

@push('scripts')
    <script>
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const dropdownIcon = document.getElementById('dropdownIcon');

        dropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
            dropdownMenu.classList.toggle('flex');
            dropdownIcon.classList.toggle('rotate-180');
        });

        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
                dropdownMenu.classList.remove('flex');
                dropdownIcon.classList.remove('rotate-180');
            }
        });
    </script>
@endpush
