<div class="sidebar sidebar-style-2" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="index.html" class="logo">
                <img src="{{ asset('img/logo/logo-wf.png') }}" alt="navbar brand" class="navbar-brand" height="40" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <!-- Dashboard -->
                <li class="nav-item {{ Route::is('dashboard') || Route::is('/') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item {{ Route::is('toko.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.toko.index') }}">
                        <i class="fas fa-cog"></i>
                        <p>Pengaturan Toko</p>
                    </a>
                </li>
                <!-- Master Data -->
                @php
                    $masterActive =
                        Route::is('warna.*') || Route::is('brand.*') || Route::is('tipe.*') || Route::is('barang.*');
                @endphp
                <li class="nav-item {{ $masterActive ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#masterData" {{ $masterActive ? 'aria-expanded=true' : '' }}>
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

                <!-- Barang Masuk -->
                <li class="nav-item {{ Route::is('barang-masuk.*') ? 'active' : '' }}">
                    <a href="{{ route('barang-masuk.index') }}">
                        <i class="fas fa-truck-loading"></i>
                        <p>Barang Masuk</p>
                    </a>
                </li>

                <!-- Persediaan -->
                <li class="nav-item {{ Route::is('persediaan.*') ? 'active' : '' }}">
                    <a href="{{ route('persediaan.index') }}">
                        <i class="fas fa-boxes"></i>
                        <p>Persediaan</p>
                    </a>
                </li>

                <!-- Pemusnahan -->
                <li class="nav-item {{ Route::is('pemusnahan-barang.*') ? 'active' : '' }}">
                    <a href="{{ route('pemusnahan-barang.index') }}">
                        <i class="fas fa-trash-alt"></i>
                        <p>Pemusnahan</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
