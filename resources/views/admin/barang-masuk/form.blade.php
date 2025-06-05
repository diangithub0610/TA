@extends('admin.layouts.app')
@section('title', isset($barang_masuk) ? 'Edit Barang Masuk' : 'Tambah Barang Masuk')
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
                    <a href="{{ route('barang-masuk.index') }}">Barang Masuk</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">{{ isset($barang_masuk) ? 'Edit' : 'Tambah' }}</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ isset($barang_masuk) ? 'Edit' : 'Tambah' }} Barang Masuk</h4>

                    </div>
                    <div class="card-body">
                        <form
                            action="{{ isset($barang_masuk) ? route('barang-masuk.update', $barang_masuk->kode_pembelian) : route('barang-masuk.store') }}"
                            method="POST" enctype="multipart/form-data" id="barangMasukForm">
                            @csrf
                            @if (isset($barang_masuk))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kode_pembelian" class="form-label">Kode Pembelian</label>
                                        <input type="text"
                                            class="form-control @error('kode_pembelian') is-invalid @enderror"
                                            id="kode_pembelian" name="kode_pembelian"
                                            value="{{ old('kode_pembelian', $barang_masuk->kode_pembelian ?? '') }}"
                                            required maxlength="10" {{ isset($barang_masuk) ? 'readonly' : '' }}>
                                        @error('kode_pembelian')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                                        <input type="date"
                                            class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                            id="tanggal_masuk" name="tanggal_masuk"
                                            value="{{ old('tanggal_masuk', isset($barang_masuk) ? $barang_masuk->tanggal_masuk : date('Y-m-d')) }}"
                                            required>
                                        @error('tanggal_masuk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kode_brand" class="form-label">Brand</label>
                                        <select class="form-select @error('kode_brand') is-invalid @enderror"
                                            id="kode_brand" name="kode_brand" required>
                                            <option value="">Pilih Brand</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->kode_brand }}"
                                                    {{ old('kode_brand', isset($barang_masuk) ? $selectedBrand : '') == $brand->kode_brand ? 'selected' : '' }}>
                                                    {{ $brand->nama_brand }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kode_brand')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_barang" class="form-label">Nama Produk</label>
                                        <select class="form-select @error('nama_barang') is-invalid @enderror"
                                            id="nama_barang" name="nama_barang" required disabled>
                                            <option value="">Pilih Brand terlebih dahulu</option>
                                        </select>
                                        @error('nama_barang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="bukti_pembelian" class="form-label">Bukti Pembelian</label>
                                        <input type="file"
                                            class="form-control @error('bukti_pembelian') is-invalid @enderror"
                                            id="bukti_pembelian" name="bukti_pembelian" accept="image/*,application/pdf"
                                            onchange="previewFile(event)">
                                        @if (isset($barang_masuk) && $barang_masuk->bukti_pembelian)
                                            <input type="hidden" name="existing_bukti"
                                                value="{{ $barang_masuk->bukti_pembelian }}">
                                        @endif
                                        @error('bukti_pembelian')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <!-- Preview File -->
                                        <div class="mt-2" id="file-preview">
                                            @if (isset($barang_masuk) && $barang_masuk->bukti_pembelian)
                                                @php
                                                    $fileExtension = pathinfo(
                                                        $barang_masuk->bukti_pembelian,
                                                        PATHINFO_EXTENSION,
                                                    );
                                                    $isImage = in_array(strtolower($fileExtension), [
                                                        'jpg',
                                                        'jpeg',
                                                        'png',
                                                        'gif',
                                                    ]);
                                                @endphp

                                                @if ($isImage)
                                                    <img src="{{ Storage::url($barang_masuk->bukti_pembelian) }}"
                                                        class="img-fluid" style="max-height: 200px;">
                                                @else
                                                    <a href="{{ Storage::url($barang_masuk->bukti_pembelian) }}"
                                                        target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-file-pdf"></i> Lihat Bukti Pembelian
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-12">
                                    <h5>Detail Barang Masuk
                                        <button type="button" class="btn btn-success btn-sm ms-2" id="tambah-detail-btn"
                                            disabled>
                                            <i class="fas fa-plus"></i> Tambah Detail
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm ms-2" id="tambah-barang-baru-btn">
                                            <i class="fas fa-plus-circle"></i> Tambah Barang Baru
                                        </button>

                                    </h5>
                                </div>
                            </div>

                            <!-- Update bagian detail-container -->
                            <div id="detail-container">
                                @if (isset($barang_masuk) && $barang_masuk->detailBarangMasuk->count() > 0)
                                    @foreach ($barang_masuk->detailBarangMasuk as $index => $detail)
                                        <div class="detail-row row mb-3">
                                            <div class="col-md-4">
                                                <select class="form-select detail-barang-select"
                                                    name="detail_barang[{{ $index }}][kode_detail]" required>
                                                    <option value="">Pilih Detail Barang</option>
                                                    @foreach ($detailBarangs as $detailBarang)
                                                        <option value="{{ $detailBarang->kode_detail }}"
                                                            {{ $detail->kode_detail == $detailBarang->kode_detail ? 'selected' : '' }}>
                                                            {{ $detailBarang->warna }} - {{ $detailBarang->ukuran }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control jumlah-input"
                                                    name="detail_barang[{{ $index }}][jumlah]"
                                                    placeholder="Jumlah" value="{{ $detail->jumlah }}" min="1"
                                                    required>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" class="form-control harga-input"
                                                        name="detail_barang[{{ $index }}][harga_barang_masuk]"
                                                        placeholder="Harga Satuan"
                                                        value="{{ $detail->harga_barang_masuk }}" min="1000"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" class="form-control total-input"
                                                        placeholder="Total" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-detail-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-8"></div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Total Keseluruhan:</h6>
                                            <h4 class="text-primary" id="grand-total">Rp 0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($barang_masuk) ? 'Update' : 'Simpan' }}
                                </button>
                            </div>
                        </form>

                        <!-- Modal Tambah Barang Baru -->
                        <div class="modal fade" id="tambahBarangModal" tabindex="-1"
                            aria-labelledby="tambahBarangModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="tambahBarangModalLabel">Tambah Barang Baru</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="tambahBarangForm" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="source" value="barang-masuk">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_nama_barang" class="form-label">Nama
                                                            Barang</label>
                                                        <input type="text" class="form-control" id="new_nama_barang"
                                                            name="nama_barang" required maxlength="100">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_kode_tipe" class="form-label">Tipe</label>
                                                        <select class="form-select" id="new_kode_tipe" name="kode_tipe"
                                                            required>
                                                            <option value="">Pilih Tipe</option>
                                                            <!-- Options akan diisi via JavaScript berdasarkan brand yang dipilih -->
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_berat" class="form-label">Berat (gram)</label>
                                                        <input type="number" class="form-control" id="new_berat"
                                                            name="berat" required min="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_harga_normal" class="form-label">Harga
                                                            Normal</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input type="number" class="form-control"
                                                                id="new_harga_normal" name="harga_normal" required
                                                                min="1000">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="new_deskripsi" class="form-label">Deskripsi</label>
                                                <textarea class="form-control" id="new_deskripsi" name="deskripsi" rows="3"></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label for="new_gambar" class="form-label">Gambar</label>
                                                <input type="file" class="form-control" id="new_gambar"
                                                    name="gambar" accept="image/*">
                                                <div class="mt-2">
                                                    <img id="new-image-preview" class="img-fluid"
                                                        style="max-height: 200px; display: none;">
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6>Detail Barang
                                                        <button type="button" class="btn btn-success btn-sm ms-2"
                                                            id="tambah-detail-modal-btn">
                                                            <i class="fas fa-plus"></i> Tambah Detail
                                                        </button>
                                                    </h6>
                                                </div>
                                            </div>

                                            <div id="detail-modal-container">
                                                <!-- Detail pertama (minimal 1) -->
                                                <div class="detail-modal-row row mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Warna</label>
                                                        <select class="form-select warna-modal-select"
                                                            name="detail_warnas[0][kode_warna]" required>
                                                            <option value="">Pilih Warna</option>
                                                            @foreach ($warnas as $warna)
                                                                <option value="{{ $warna->kode_warna }}">
                                                                    {{ $warna->warna }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ukuran</label>
                                                        <input type="text" class="form-control ukuran-modal-input"
                                                            name="detail_warnas[0][ukuran]"
                                                            placeholder="Ukuran (mis: 42, 41.5)" pattern="^\d+(\.\d+)?$"
                                                            required>
                                                    </div>
                                                    {{-- <div class="col-md-3">
                                <label class="form-label">Stok</label>
                                <input type="number" class="form-control stok-modal-input" name="detail_warnas[0][stok]" 
                                       placeholder="Stok" min="0" required>
                            </div> --}}
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="button"
                                                            class="btn btn-danger remove-detail-modal-btn" disabled>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-primary" id="simpan-barang-btn">Simpan
                                            Barang</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Tambah Barang Baru -->
                        {{-- <div class="modal fade" id="tambahBarangModal" tabindex="-1"
                            aria-labelledby="tambahBarangModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="tambahBarangModalLabel">Tambah Barang Baru</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="tambahBarangForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_nama_barang" class="form-label">Nama
                                                            Barang</label>
                                                        <input type="text" class="form-control" id="new_nama_barang"
                                                            name="nama_barang" required maxlength="100">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_berat" class="form-label">Berat (gram)</label>
                                                        <input type="number" class="form-control" id="new_berat"
                                                            name="berat" required min="1">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_harga_normal" class="form-label">Harga
                                                            Normal</label>
                                                        <input type="number" class="form-control" id="new_harga_normal"
                                                            name="harga_normal" required min="1000">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_kode_tipe" class="form-label">Tipe</label>
                                                        <select class="form-select" id="new_kode_tipe" name="kode_tipe"
                                                            required>
                                                            <option value="">Pilih Tipe</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="new_deskripsi" class="form-label">Deskripsi</label>
                                                <textarea class="form-control" id="new_deskripsi" name="deskripsi" rows="3"></textarea>
                                            </div>

                                            <hr>
                                            <h6>Detail Varian Barang</h6>
                                            <div id="varian-container">
                                                <div class="varian-row row mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Warna</label>
                                                        <select class="form-select warna-select"
                                                            name="varian[0][kode_warna]" required>
                                                            <option value="">Pilih Warna</option>
                                                            @foreach ($warnas as $warna)
                                                                <option value="{{ $warna->warna }}">
                                                                    {{ $warna->warna }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Ukuran</label>
                                                        <input type="number" class="form-control ukuran-input"
                                                            name="varian[0][ukuran]" step="0.1" min="0.1"
                                                            placeholder="Ukuran" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Jumlah</label>
                                                        <input type="number" class="form-control jumlah-varian-input"
                                                            name="varian[0][jumlah]" min="1" placeholder="Jumlah"
                                                            required>
                                                    </div>
                                                    <div class="col-md-1 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger remove-varian-btn"
                                                            disabled>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-success btn-sm" id="tambah-varian-btn">
                                                <i class="fas fa-plus"></i> Tambah Varian
                                            </button>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-primary" id="simpan-barang-btn">Simpan
                                            Barang</button>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        <div class="row mt-4" id="barang-baru-section" style="display: none;">
                            <div class="col-md-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-plus-circle"></i> Tambah Barang Baru
                                            <button type="button" class="btn btn-sm btn-outline-light float-end"
                                                id="tutup-form-barang">
                                                <i class="fas fa-times"></i> Tutup
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="form-barang-baru">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Barang</label>
                                                        <input type="text" class="form-control" id="nama_barang_baru"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tipe</label>
                                                        <select class="form-select" id="kode_tipe_baru" required>
                                                            <option value="">Pilih Tipe</option>
                                                            <!-- Akan diisi via JavaScript berdasarkan brand yang dipilih -->
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Berat (gram)</label>
                                                        <input type="number" class="form-control" id="berat_baru"
                                                            min="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Harga Normal</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input type="number" class="form-control"
                                                                id="harga_normal_baru" min="1000" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Ukuran</label>
                                                        <input type="text" class="form-control" id="ukuran_baru"
                                                            placeholder="42, 41.5, dll" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Warna</label>
                                                        <select class="form-select" id="kode_warna_baru" required>
                                                            <option value="">Pilih Warna</option>
                                                            @foreach ($warnas as $warna)
                                                                <option value="{{ $warna->kode_warna }}">
                                                                    {{ $warna->warna }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Deskripsi</label>
                                                        <textarea class="form-control" id="deskripsi_baru" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button type="button" class="btn btn-success" id="simpan-barang-baru">
                                                    <i class="fas fa-save"></i> Simpan & Gunakan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let detailIndex = {{ isset($barang_masuk) ? $barang_masuk->detailBarangMasuk->count() : 0 }};
            let varianIndex = 1;
            let availableBarangs = [];

            // Tambahkan di dalam $(document).ready(function() {})

            let detailModalIndex = 1;

            // Preview gambar untuk modal
            $('#new_gambar').change(function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#new-image-preview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Tambah detail barang di modal
            $('#tambah-detail-modal-btn').click(function() {
                const detailHtml = `
        <div class="detail-modal-row row mb-3">
            <div class="col-md-4">
                <select class="form-select warna-modal-select" name="detail_warnas[${detailModalIndex}][kode_warna]" required>
                    <option value="">Pilih Warna</option>
                    @foreach ($warnas as $warna)
                        <option value="{{ $warna->kode_warna }}">{{ $warna->warna }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control ukuran-modal-input" name="detail_warnas[${detailModalIndex}][ukuran]" 
                   placeholder="Ukuran (mis: 42, 41.5)" pattern="^\\d+(\\.\\d+)?$" required>
            </div>
          
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-detail-modal-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

                $('#detail-modal-container').append(detailHtml);
                detailModalIndex++;
                updateRemoveModalButtons();
            });

            // Remove detail barang di modal
            $(document).on('click', '.remove-detail-modal-btn', function() {
                $(this).closest('.detail-modal-row').remove();
                updateRemoveModalButtons();
            });

            // Update status tombol remove di modal
            function updateRemoveModalButtons() {
                const modalRowCount = $('.detail-modal-row').length;
                $('.remove-detail-modal-btn').prop('disabled', modalRowCount <= 1);
            }

            // Simpan barang baru
            $('#simpan-barang-btn').click(function() {
                const formData = new FormData($('#tambahBarangForm')[0]);

                $.ajax({
                    url: '{{ route('barang.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Barang berhasil ditambahkan');
                            $('#tambahBarangModal').modal('hide');

                            // Reset form
                            $('#tambahBarangForm')[0].reset();
                            $('#new-image-preview').hide();

                            // Reset detail container ke kondisi awal
                            $('#detail-modal-container').html(`
                    <div class="detail-modal-row row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Warna</label>
                            <select class="form-select warna-modal-select" name="detail_warnas[0][kode_warna]" required>
                                <option value="">Pilih Warna</option>
                                @foreach ($warnas as $warna)
                                    <option value="{{ $warna->kode_warna }}">{{ $warna->warna }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ukuran</label>
                            <input type="text" class="form-control ukuran-modal-input" name="detail_warnas[0][ukuran]" 
                                   placeholder="Ukuran (mis: 42, 41.5)" pattern="^\\d+(\\.\\d+)?$" required>
                        </div>
                    
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-detail-modal-btn" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `);
                            detailModalIndex = 1;

                            // Refresh data barang jika brand sudah dipilih
                            if ($('#kode_brand').val()) {
                                $('#kode_brand').trigger('change');
                            }
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMessage = 'Validation errors:\n';
                            Object.keys(errors).forEach(key => {
                                errorMessage += `- ${errors[key][0]}\n`;
                            });
                            alert(errorMessage);
                        } else {
                            alert('Terjadi kesalahan saat menyimpan data');
                        }
                    }
                });
            });

            // Event listener untuk perubahan brand
            $('#kode_brand').change(function() {
                const kodeBrand = $(this).val();
                const namaProdukSelect = $('#nama_barang');

                namaProdukSelect.empty().append('<option value="">Loading...</option>').prop('disabled',
                    true);

                if (kodeBrand) {
                    // Get unique product names by brand
                    $.ajax({
                        url: '{{ route('barang-masuk.get-produk-by-brand') }}',
                        type: 'GET',
                        data: {
                            kode_brand: kodeBrand
                        },
                        success: function(response) {
                            namaProdukSelect.empty().append(
                                '<option value="">Pilih Nama Produk</option>');

                            const uniqueProducts = [...new Set(response.map(item => item
                                .nama_barang))];
                            uniqueProducts.forEach(function(nama) {
                                namaProdukSelect.append(
                                    `<option value="${nama}">${nama}</option>`);
                            });

                            namaProdukSelect.prop('disabled', false);
                            availableBarangs = response;
                        },
                        error: function() {
                            namaProdukSelect.empty().append(
                                '<option value="">Error loading data</option>');
                        }
                    });

                    // Load tipe untuk modal tambah barang
                    loadTipeByBrand(kodeBrand);
                } else {
                    namaProdukSelect.empty().append(
                        '<option value="">Pilih Brand terlebih dahulu</option>');
                    resetDetailSection();
                }
            });

            // Event listener untuk perubahan nama produk
            $('#nama_barang').change(function() {
                const namaProduk = $(this).val();
                const kodeBrand = $('#kode_brand').val();

                if (namaProduk && kodeBrand) {
                    // Load detail barang berdasarkan nama produk
                    $.ajax({
                        url: '{{ route('barang-masuk.get-detail-by-produk') }}',
                        type: 'GET',
                        data: {
                            nama_barang: namaProduk,
                            kode_brand: kodeBrand
                        },
                        success: function(response) {
                            availableBarangs =
                                response; // Update available barangs dengan detail barang
                            $('#tambah-detail-btn, #tambah-barang-baru-btn').prop('disabled',
                                false);
                        },
                        error: function() {
                            alert('Error loading detail barang');
                            $('#tambah-detail-btn, #tambah-barang-baru-btn').prop('disabled',
                                true);
                        }
                    });
                } else {
                    $('#tambah-detail-btn, #tambah-barang-baru-btn').prop('disabled', true);
                    resetDetailSection();
                }
            });


            // Fungsi untuk load tipe berdasarkan brand
            function loadTipeByBrand(kodeBrand) {
                $.ajax({
                    url: '{{ route('barang-masuk.get-tipe-by-brand') }}',
                    type: 'GET',
                    data: {
                        kode_brand: kodeBrand
                    },
                    success: function(tipes) {
                        $('#new_kode_tipe').empty().append('<option value="">Pilih Tipe</option>');
                        tipes.forEach(function(tipe) {
                            $('#new_kode_tipe').append(
                                `<option value="${tipe.kode_tipe}">${tipe.nama_tipe}</option>`
                            );
                        });
                    }
                });
            }

            // Fungsi untuk reset detail section
            function resetDetailSection() {
                $('#detail-container').empty();
                updateGrandTotal();
            }

            $('#tambah-detail-btn').click(function() {
                const namaProduk = $('#nama_barang').val();
                if (!namaProduk) {
                    alert('Pilih nama produk terlebih dahulu');
                    return;
                }

                const detailHtml = `
        <div class="detail-row row mb-3">
            <div class="col-md-4">
                <select class="form-select detail-barang-select" name="detail_barang[${detailIndex}][kode_detail]" required>
                    <option value="">Pilih Detail Barang</option>
                    ${availableBarangs.map(detail => 
                        `<option value="${detail.kode_detail}" data-warna="${detail.warna}" data-ukuran="${detail.ukuran}">${detail.warna} - ${detail.ukuran}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control jumlah-input" 
                       name="detail_barang[${detailIndex}][jumlah]" placeholder="Jumlah" min="1" required>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control harga-input" 
                           name="detail_barang[${detailIndex}][harga_barang_masuk]" 
                           placeholder="Harga Satuan" min="1000" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control total-input" placeholder="Total" readonly>
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-detail-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

                $('#detail-container').append(detailHtml);
                detailIndex++;
            });
            // Remove detail barang
            $(document).on('click', '.remove-detail-btn', function() {
                $(this).closest('.detail-row').remove();
                updateGrandTotal();
            });

            // Calculate total per row
            $(document).on('input', '.jumlah-input, .harga-input', function() {
                const row = $(this).closest('.detail-row');
                const jumlah = parseInt(row.find('.jumlah-input').val()) || 0;
                const harga = parseInt(row.find('.harga-input').val()) || 0;
                const total = jumlah * harga;

                row.find('.total-input').val(total.toLocaleString('id-ID'));
                updateGrandTotal();
            });

            // Update grand total
            function updateGrandTotal() {
                let grandTotal = 0;
                $('.detail-row').each(function() {
                    const jumlah = parseInt($(this).find('.jumlah-input').val()) || 0;
                    const harga = parseInt($(this).find('.harga-input').val()) || 0;
                    grandTotal += (jumlah * harga);
                });
                $('#grand-total').text('Rp ' + grandTotal.toLocaleString('id-ID'));
            }

            // Modal tambah barang baru
            $('#tambah-barang-baru-btn').click(function() {
                $('#tambahBarangModal').modal('show');
            });

            // Tambah varian dalam modal
            $('#tambah-varian-btn').click(function() {
                const varianHtml = `
            <div class="varian-row row mb-3">
                <div class="col-md-4">
                    <select class="form-select warna-select" name="varian[${varianIndex}][kode_warna]" required>
                        <option value="">Pilih Warna</option>
                        @foreach ($warnas as $warna)
                            <option value="{{ $warna->kode_warna }}">{{ $warna->warna }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control ukuran-input" name="varian[${varianIndex}][ukuran]" 
                           step="0.1" min="0.1" placeholder="Ukuran" required>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control jumlah-varian-input" name="varian[${varianIndex}][jumlah]" 
                           min="1" placeholder="Jumlah" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-varian-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

                $('#varian-container').append(varianHtml);
                varianIndex++;
                updateRemoveVarianButtons();
            });

            // Remove varian
            $(document).on('click', '.remove-varian-btn', function() {
                $(this).closest('.varian-row').remove();
                updateRemoveVarianButtons();
            });

            // Update status tombol remove varian
            function updateRemoveVarianButtons() {
                const varianCount = $('.varian-row').length;
                $('.remove-varian-btn').prop('disabled', varianCount <= 1);
            }

            // Simpan barang baru
            $('#simpan-barang-btn').click(function() {
                const formData = new FormData($('#tambahBarangForm')[0]);

                // Tambahkan data varian
                $('.varian-row').each(function(index) {
                    const kodeWarna = $(this).find('.warna-select').val();
                    const ukuran = $(this).find('.ukuran-input').val();
                    const jumlah = $(this).find('.jumlah-varian-input').val();

                    formData.append(`varian[${index}][kode_warna]`, kodeWarna);
                    formData.append(`varian[${index}][ukuran]`, ukuran);
                    formData.append(`varian[${index}][jumlah]`, jumlah);
                });

                $.ajax({
                    url: '{{ route('barang-masuk.store-barang-baru') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Barang berhasil ditambahkan');
                            $('#tambahBarangModal').modal('hide');
                            $('#tambahBarangForm')[0].reset();
                            $('#varian-container').html(`
                        <div class="varian-row row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Warna</label>
                                <select class="form-select warna-select" name="varian[0][kode_warna]" required>
                                    <option value="">Pilih Warna</option>
                                    @foreach ($warnas as $warna)
                                        <option value="{{ $warna->kode_warna }}">{{ $warna->warna }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ukuran</label>
                                <input type="number" class="form-control ukuran-input" name="varian[0][ukuran]" 
                                       step="0.1" min="0.1" placeholder="Ukuran" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" class="form-control jumlah-varian-input" name="varian[0][jumlah]" 
                                       min="1" placeholder="Jumlah" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-varian-btn" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `);
                            varianIndex = 1;

                            // Refresh data barang
                            $('#kode_brand').trigger('change');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMessage = 'Validation errors:\n';
                            Object.keys(errors).forEach(key => {
                                errorMessage += `- ${errors[key][0]}\n`;
                            });
                            alert(errorMessage);
                        } else {
                            alert('Terjadi kesalahan saat menyimpan data');
                        }
                    }
                });
            });

            // Initialize calculations for existing data
            $('.jumlah-input, .harga-input').trigger('input');
        });
    </script>
@endpush
