@extends('admin.layouts.app')
@section('title', 'Tipe')
@section('content')

    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">@yield('title')</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('barang.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>

        <!-- Header Section -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h1 class="h2 mb-3 mb-md-0 fw-bold text-dark">Persediaan Barang</h1>
            <div class="d-flex gap-2">
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('persediaan.index') }}" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="warna" class="form-select">
                                <option value="">-- Filter Warna --</option>
                                @foreach ($warnas as $warna)
                                    <option value="{{ $warna->kode_warna }}"
                                        {{ $selectedWarna == $warna->kode_warna ? 'selected' : '' }}>
                                        {{ $warna->warna }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="brand" class="form-select">
                                <option value="">-- Filter Brand --</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->kode_brand }}"
                                        {{ $selectedBrand == $brand->kode_brand ? 'selected' : '' }}>
                                        {{ $brand->nama_brand }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="tipe" class="form-select">
                                <option value="">-- Filter Tipe --</option>
                                @foreach ($tipes as $tipe)
                                    <option value="{{ $tipe->kode_tipe }}"
                                        {{ $selectedTipe == $tipe->kode_tipe ? 'selected' : '' }}>
                                        {{ $tipe->nama_tipe }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <!-- Tambahkan ini di bagian card-header pada view index -->
                    @if (Route::currentRouteName() == 'persediaan.index')
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Daftar @yield('title')</h4>

                            <!-- Export Buttons -->
                            <div class="d-flex gap-2">
                                <form method="GET" action="{{ route('persediaan.export.pdf') }}"
                                    style="display: inline;">
                                    <input type="hidden" name="warna" value="{{ $selectedWarna }}">
                                    <input type="hidden" name="brand" value="{{ $selectedBrand }}">
                                    <input type="hidden" name="tipe" value="{{ $selectedTipe }}">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-file-pdf"></i> Export PDF
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode Detail</th>
                                        <th>Nama Barang</th>
                                        <th>Brand</th>
                                        <th>Warna</th>
                                        <th>Tipe</th>
                                        <th>Ukuran</th>
                                        <th>Stok</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detailBarangs as $detail)
                                        <tr>
                                            <td>{{ $detail->kode_detail }}</td>
                                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                            <td>{{ $detail->barang->tipe->brand->nama_brand ?? '-' }}</td>
                                            <td>{{ $detail->warna->warna ?? '-' }}</td>
                                            <td>{{ $detail->barang->tipe->nama_tipe ?? '-' }}</td>
                                            <td>{{ $detail->ukuran }}</td>
                                            <td>{{ $detail->stok }}</td>
                                            <td>
                                                @if ($detail->stok < 10)
                                                    <span class="badge bg-danger">Harus Restok</span>
                                                @else
                                                    <span class="badge bg-success">Aman</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- <!-- Pagination Section -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="mb-3 mb-md-0">Menampilkan 1-10 dari 45 item</div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2">Per Halaman:</span>
                                    <select class="form-select form-select-sm">
                                        <option>10</option>
                                        <option>25</option>
                                        <option>50</option>
                                        <option>100</option>
                                    </select>
                                </div>
                                <nav>
                                    <ul class="pagination mb-0">
                                        <li class="page-item">
                                            <a class="page-link" href="#">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script JavaScript tetap sama seperti sebelumnya
        document.addEventListener("DOMContentLoaded", function() {
            // Filter Script
            document.getElementById("brand-filter").addEventListener("change", function() {
                console.log("Filter brand:", this.value);
            });

            // Pagination Script
            document.querySelectorAll(".page-link").forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    console.log("Navigasi ke:", this.textContent);
                });
            });

            // Export Script
            document.querySelectorAll(".btn-export").forEach(button => {
                button.addEventListener("click", function() {
                    const type = this.querySelector("i").classList.contains("bi-file-pdf") ? "PDF" :
                        "Excel";
                    alert(`Mengekspor data ke ${type}...`);
                });
            });
        });
    </script>
@endpush
