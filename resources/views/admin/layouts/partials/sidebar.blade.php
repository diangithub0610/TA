@php
    $user = Auth::guard('admin')->user();
    $role = $user?->role;
@endphp

<div class="sidebar sidebar-style-2" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('dashboard') }}" class="logo">
                <img src="{{ asset('img/logo/logo-wf.png') }}" alt="navbar brand" class="navbar-brand" height="40" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
            </div>
            <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
        </div>
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                {{-- Role: OWNER --}}
                <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @if ($role === 'owner')
                    <li class="nav-item {{ Route::is('admin.toko.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.toko.index') }}">
                            <i class="fas fa-cog"></i>
                            <p>Pengaturan Toko</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('pemusnahan-barang.*') ? 'active' : '' }}">
                        <a href="{{ route('pemusnahan-barang.index') }}">
                            <i class="fas fa-trash-alt"></i>
                            <p>Pemusnahan</p>
                        </a>
                    </li>
                    {{-- <div class="collapse {{ Route::is('laporan.*') ? 'show' : '' }}" id="laporanMenu">
                        <ul class="nav nav-collapse">
                            <li class="{{ Route::is('laporan.*') ? 'active' : '' }}">
                                <a href="{{ route('laporan.index') }}">
                                    <span class="sub-item">Laporan</span>
                                </a>
                            </li>
                        </ul>
                    </div>                     --}}

                    @php
                        $laporanActive = Route::is('barang-masuk') || Route::is('barang-terjual');
                    @endphp

                    <li class="nav-item {{ $laporanActive ? 'active' : '' }}">
                        <a data-bs-toggle="collapse" href="#laporanMenu"
                            {{ $laporanActive ? 'aria-expanded=true' : '' }}>
                            <i class="fas fa-file-alt"></i>
                            <p>Laporan</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse {{ $laporanActive ? 'show' : '' }}" id="laporanMenu">
                            <ul class="nav nav-collapse">
                                <li class="{{ Route::is('barang-masuk') ? 'active' : '' }}">
                                    <a href="{{ route('laporan.barang-masuk') }}">
                                        <span class="sub-item">Laporan Barang Masuk</span>
                                    </a>
                                </li>
                                <li class="{{ Route::is('transaksi') ? 'active' : '' }}">
                                    <a href="{{ route('laporan.transaksi') }}">
                                        <span class="sub-item">Laporan transaksi</span>
                                    </a>
                                </li>
                                <li class="{{ Route::is('barang-terjual') ? 'active' : '' }}">
                                    <a href="{{ route('laporan.barang-terjual') }}">
                                        <span class="sub-item">Laporan barang</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item {{ Route::is('management-user.*') ? 'active' : '' }}">
                        <a href="{{ route('management-user.index') }}">
                            <i class="fas fa-users-cog"></i>
                            <p>Management User</p>
                        </a>
                    </li>
                @endif

                {{-- Role: SHOPKEEPER --}}
                @if ($role === 'shopkeeper')
                    <li class="nav-item {{ Route::is('admin-transaksi.*') ? 'active' : '' }}">
                        <a href="{{ route('admin-transaksi.index') }}">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Penjualan</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('kasir.index') ? 'active' : '' }}">
                        <a href="{{ route('kasir.index') }}">
                            <i class="fas fa-store"></i>
                            <p>Kasir</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('kasir.riwayat') ? 'active' : '' }}">
                        <a href="{{ route('kasir.riwayat') }}">
                            <i class="fas fa-receipt"></i>
                            <p>Riwayat</p>
                        </a>
                    </li>
                @endif

                {{-- Role: GUDANG --}}
                @if ($role === 'gudang')
                    @php
                        $masterActive =
                            Route::is('warna.*') ||
                            Route::is('brand.*') ||
                            Route::is('tipe.*') ||
                            Route::is('barang.*');
                    @endphp

                    <li class="nav-item {{ $masterActive ? 'active' : '' }}">
                        <a data-bs-toggle="collapse" href="#masterData"
                            {{ $masterActive ? 'aria-expanded=true' : '' }}>
                            <i class="fas fa-database"></i>
                            <p>Master Data</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse {{ $masterActive ? 'show' : '' }}" id="masterData">
                            <ul class="nav nav-collapse">
                                <li class="{{ Route::is('warna.*') ? 'active' : '' }}">
                                    <a href="{{ route('warna.index') }}">
                                        <span class="sub-item">Warna</span>
                                    </a>
                                </li>
                                <li class="{{ Route::is('brand.*') ? 'active' : '' }}">
                                    <a href="{{ route('brand.index') }}">
                                        <span class="sub-item">Brand</span>
                                    </a>
                                </li>
                                <li class="{{ Route::is('tipe.*') ? 'active' : '' }}">
                                    <a href="{{ route('tipe.index') }}">
                                        <span class="sub-item">Tipe</span>
                                    </a>
                                </li>
                                <li class="{{ Route::is('barang.*') ? 'active' : '' }}">
                                    <a href="{{ route('barang.index') }}">
                                        <span class="sub-item">Barang</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item {{ Route::is('barang-masuk.*') ? 'active' : '' }}">
                        <a href="{{ route('barang-masuk.index') }}">
                            <i class="fas fa-truck-loading"></i>
                            <p>Barang Masuk</p>
                        </a>
                    </li>

                    <li class="nav-item {{ Route::is('persediaan.*') ? 'active' : '' }}">
                        <a href="{{ route('persediaan.index') }}">
                            <i class="fas fa-boxes"></i>
                            <p>Persediaan</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('pemusnahan-barang.*') ? 'active' : '' }}">
                        <a href="{{ route('pemusnahan-barang.index') }}">
                            <i class="fas fa-trash-alt"></i>
                            <p>Pemusnahan</p>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
