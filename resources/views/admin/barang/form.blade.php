@extends('admin.layouts.app')

@section('title', isset($barang) ? 'Edit Barang' : 'Tambah Barang')

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
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a
                        href="{{ isset($barang) ? route('barang.edit', $barang->kode_barang) : route('barang.create') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ isset($barang) ? 'Edit Barang' : 'Tambah Barang' }}</h4>
                        <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm float-end">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form
                            action="{{ isset($barang) ? route('barang.update', $barang->kode_barang) : route('barang.store') }}"
                            method="POST" enctype="multipart/form-data" id="barangForm">
                            @csrf
                            @if (isset($barang))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <!-- Informasi Barang -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Informasi Barang</h5>

                                    {{-- <div class="mb-3">
                                        <label for="kode_barang" class="form-label">Kode Barang <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('kode_barang') is-invalid @enderror" id="kode_barang"
                                            name="kode_barang" value="{{ old('kode_barang', $barang->kode_barang ?? '') }}"
                                            {{ isset($barang) ? 'readonly' : '' }} required>
                                        @error('kode_barang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div> --}}

                                    <div class="mb-3">
                                        <label for="nama_barang" class="form-label">Nama Barang <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('nama_barang') is-invalid @enderror" id="nama_barang"
                                            name="nama_barang" value="{{ old('nama_barang', $barang->nama_barang ?? '') }}"
                                            required>
                                        @error('nama_barang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="berat" class="form-label">Berat (gram)</label>
                                        <input type="number" class="form-control @error('berat') is-invalid @enderror"
                                            id="berat" name="berat" value="{{ old('berat', $barang->berat ?? '') }}">
                                        @error('berat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Ganti select tipe dengan Select2 -->
                                    <div class="mb-3">
                                        <label for="kode_tipe" class="form-label">Tipe <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('kode_tipe') is-invalid @enderror" id="kode_tipe"
                                            name="kode_tipe" required>
                                            <option value="">Pilih Tipe</option>
                                            @foreach ($tipes as $tipe)
                                                <option value="{{ $tipe->kode_tipe }}"
                                                    {{ old('kode_tipe', $barang->kode_tipe ?? '') == $tipe->kode_tipe ? 'selected' : '' }}>
                                                    {{ $tipe->nama_tipe }} ({{ $tipe->brand->nama_brand }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kode_tipe')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $barang->deskripsi ?? '') }}</textarea>
                                        @error('deskripsi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Gambar -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Gambar</h5>

                                    <!-- Gambar Utama -->
                                    <div class="mb-3">
                                        <label for="gambar_utama" class="form-label">Gambar Utama</label>
                                        <input type="file"
                                            class="form-control @error('gambar_utama') is-invalid @enderror"
                                            id="gambar_utama" name="gambar_utama" accept="image/*">
                                        @error('gambar_utama')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="mt-2">
                                            @if (isset($barang) && $barang->gambar)
                                                <img src="{{ asset('storage/' . $barang->gambar) }}" alt="Gambar Utama"
                                                    class="img-thumbnail" style="max-width: 200px; max-height: 200px;"
                                                    id="current_main_image">
                                            @endif
                                            <div id="main_image_preview" style="display: none;">
                                                <img id="preview_main_image" class="img-thumbnail"
                                                    style="max-width: 200px; max-height: 200px;">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Gambar Pendukung -->
                                    <div class="mb-3">
                                        <label for="gambar_pendukung" class="form-label">Gambar Pendukung</label>
                                        <input type="file"
                                            class="form-control @error('gambar_pendukung.*') is-invalid @enderror"
                                            id="gambar_pendukung" name="gambar_pendukung[]" accept="image/*" multiple>
                                        @error('gambar_pendukung.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="mt-2" id="supporting_images_preview">
                                            @if (isset($barang) && $barang->galeriGambar->count() > 0)
                                                <div class="row">
                                                    @foreach ($barang->galeriGambar as $gambar)
                                                        <div class="col-md-3 mb-2"
                                                            id="existing_image_{{ $gambar->kode_gambar }}">
                                                            <div class="position-relative">
                                                                <img src="{{ asset('storage/barang/' . $gambar->gambar) }}"
                                                                    alt="Gambar Pendukung" class="img-thumbnail"
                                                                    style="width: 100%; height: 100px; object-fit: cover;">
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 delete-supporting-image"
                                                                    data-id="{{ $gambar->kode_gambar }}">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Barang -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5>Detail Barang</h5>
                                        <button type="button" class="btn btn-primary btn-sm" id="addDetailBtn">
                                            <i class="fas fa-plus"></i> Tambah Detail
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <!-- Detail Barang Table - Bagian yang diperbaiki -->
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="detailTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="15%">Ukuran <span class="text-danger">*</span></th>
                                                        <th width="20%">Warna <span class="text-danger">*</span></th>
                                                        <th width="15%">Harga Normal</th>
                                                        <th width="12%">Stok Min</th>
                                                        <th width="15%">Potongan</th>
                                                        @if (isset($barang))
                                                            <th width="12%">Stok</th>
                                                            <th width="15%">Harga Beli</th>
                                                        @endif
                                                        <th width="8%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detailTableBody">
                                                    @if (isset($barang) && $barang->detailBarang->count() > 0)
                                                        @foreach ($barang->detailBarang as $index => $detail)
                                                            <tr>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="detail_barang[{{ $index }}][ukuran]"
                                                                        value="{{ $detail->ukuran }}" step="0.1"
                                                                        required min="0">
                                                                </td>
                                                                <td>
                                                                    <select class="form-select warna-select"
                                                                        name="detail_barang[{{ $index }}][kode_warna]"
                                                                        required>
                                                                        <option value="">Pilih Warna</option>
                                                                        @foreach ($warnas as $warna)
                                                                            <option value="{{ $warna->kode_warna }}"
                                                                                {{ $detail->kode_warna == $warna->kode_warna ? 'selected' : '' }}>
                                                                                {{ $warna->warna }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="detail_barang[{{ $index }}][harga_normal]"
                                                                        value="{{ $detail->harga_normal }}"
                                                                        min="0">
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="detail_barang[{{ $index }}][stok_minimum]"
                                                                        value="{{ $detail->stok_minimum }}"
                                                                        min="0">
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="detail_barang[{{ $index }}][potongan_harga]"
                                                                        value="{{ $detail->potongan_harga }}"
                                                                        min="0">
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="detail_barang[{{ $index }}][stok]"
                                                                        value="{{ $detail->stok }}" min="0"
                                                                        readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="detail_barang[{{ $index }}][harga_beli]"
                                                                        value="{{ $detail->harga_beli }}" min="0"
                                                                        readonly>
                                                                </td>
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-danger btn-sm remove-detail">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td>
                                                                <input type="number" class="form-control"
                                                                    name="detail_barang[0][ukuran]"
                                                                    value="{{ old('detail_barang.0.ukuran') }}"
                                                                    step="0.1" required min="0">
                                                            </td>
                                                            <td>
                                                                <select class="form-select warna-select"
                                                                    name="detail_barang[0][kode_warna]" required>
                                                                    <option value="">Pilih Warna</option>
                                                                    @foreach ($warnas as $warna)
                                                                        <option value="{{ $warna->kode_warna }}"
                                                                            {{ old('detail_barang.0.kode_warna') == $warna->kode_warna ? 'selected' : '' }}>
                                                                            {{ $warna->warna }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control"
                                                                    name="detail_barang[0][harga_normal]"
                                                                    value="{{ old('detail_barang.0.harga_normal') }}"
                                                                    min="0">
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control"
                                                                    name="detail_barang[0][stok_minimum]"
                                                                    value="{{ old('detail_barang.0.stok_minimum') }}"
                                                                    min="0">
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control"
                                                                    name="detail_barang[0][potongan_harga]"
                                                                    value="{{ old('detail_barang.0.potongan_harga') }}"
                                                                    min="0">
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm remove-detail">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i>
                                            {{ isset($barang) ? 'Update' : 'Simpan' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Crop Gambar -->
    <div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropModalLabel">Crop Gambar Utama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img id="cropImage" style="max-width: 100%;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="cropBtn">Crop & Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            let detailIndex = {{ isset($barang) ? $barang->detailBarang->count() : 1 }};
            let cropper = null;
            let mainImageFile = null;

            // Initialize Select2
            $('#kode_tipe').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Tipe',
                allowClear: true
            });

            // Initialize Select2 untuk warna yang sudah ada
            $('.warna-select').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Warna',
                allowClear: true
            });

            // Template untuk baris detail baru (DIPERBAIKI)
            function getDetailRowTemplate(index) {
                return `
        <tr>
            <td>
                <input type="number" class="form-control" name="detail_barang[${index}][ukuran]" step="0.1" required min="0">
            </td>
            <td>
                <select class="form-select warna-select" name="detail_barang[${index}][kode_warna]" required>
                    <option value="">Pilih Warna</option>
                    @foreach ($warnas as $warna)
                        <option value="{{ $warna->kode_warna }}">{{ $warna->warna }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control" name="detail_barang[${index}][harga_normal]" min="0">
            </td>
            <td>
                <input type="number" class="form-control" name="detail_barang[${index}][stok_minimum]" min="0">
            </td>
            <td>
                <input type="number" class="form-control" name="detail_barang[${index}][potongan_harga]" min="0">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-detail">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
            }

            // Tambah detail barang
            $('#addDetailBtn').click(function() {
                const newRow = $(getDetailRowTemplate(detailIndex));
                $('#detailTableBody').append(newRow);

                // Initialize Select2 untuk select yang baru ditambahkan
                newRow.find('.warna-select').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Pilih Warna',
                    allowClear: true
                });

                detailIndex++;
            });
            // Hapus detail barang
            $(document).on('click', '.remove-detail', function() {
                if ($('#detailTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    // Reindex detail array names
                    reindexDetailNames();
                } else {
                    alert('Minimal harus ada 1 detail barang');
                }
            });

            // Reindex nama-nama input detail
            function reindexDetailNames() {
                $('#detailTableBody tr').each(function(index) {
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            let newName = name.replace(/\[\d+\]/, '[' + index + ']');
                            $(this).attr('name', newName);
                        }
                    });
                });
            }

            // Preview gambar pendukung
            $('#gambar_pendukung').change(function() {
                const files = this.files;
                const container = $('#supporting_images_preview');

                // Clear existing previews (but keep existing images)
                container.find('.preview-image').remove();

                if (files.length > 0) {
                    const row = $('<div class="row"></div>');

                    Array.from(files).forEach(function(file, index) {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const col = $(`
                            <div class="col-md-3 mb-2 preview-image">
                                <div class="position-relative">
                                    <img src="${e.target.result}" 
                                         alt="Preview" 
                                         class="img-thumbnail" 
                                         style="width: 100%; height: 100px; object-fit: cover;">
                                    <button type="button" 
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-preview"
                                            data-index="${index}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `);
                                row.append(col);
                            };
                            reader.readAsDataURL(file);
                        }
                    });

                    container.append(row);
                }
            });

            // Hapus preview gambar pendukung
            $(document).on('click', '.remove-preview', function() {
                $(this).closest('.preview-image').remove();
            });

            // Hapus gambar pendukung yang sudah ada
            $(document).on('click', '.delete-supporting-image', function() {
                const imageId = $(this).data('id');
                const element = $(this).closest('.col-md-3');

                if (confirm('Apakah Anda yakin ingin menghapus gambar ini?')) {
                    $.ajax({
                        url: `{{ url('barang/delete-gambar') }}/${imageId}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                element.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                alert('Gagal menghapus gambar');
                            }
                        },
                        error: function(xhr) {
                            alert('Terjadi kesalahan saat menghapus gambar');
                            console.log(xhr.responseText);
                        }
                    });
                }

            });

            // Cropper untuk gambar utama
            $('#gambar_utama').change(function() {
                const file = this.files[0];
                if (file && file.type.startsWith('image/')) {
                    mainImageFile = file;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#cropImage').attr('src', e.target.result);
                        $('#cropModal').modal('show');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Initialize cropper ketika modal dibuka
            $('#cropModal').on('shown.bs.modal', function() {
                const image = document.getElementById('cropImage');
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                });
            });

            // Destroy cropper ketika modal ditutup
            $('#cropModal').on('hidden.bs.modal', function() {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            });

            // Crop dan simpan
            $('#cropBtn').click(function() {
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({
                        width: 400,
                        height: 400,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    canvas.toBlob(function(blob) {
                        // Create new file from cropped image
                        const croppedFile = new File([blob], mainImageFile.name, {
                            type: mainImageFile.type,
                            lastModified: Date.now()
                        });

                        // Update file input
                        const dt = new DataTransfer();
                        dt.items.add(croppedFile);
                        document.getElementById('gambar_utama').files = dt.files;

                        // Show preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#current_main_image').hide();
                            $('#preview_main_image').attr('src', e.target.result);
                            $('#main_image_preview').show();
                        };
                        reader.readAsDataURL(croppedFile);

                        $('#cropModal').modal('hide');
                    }, mainImageFile.type, 0.9);
                }
            });

            // Form validation
            $('#barangForm').submit(function(e) {
                let isValid = true;
                let errorMessage = '';

                // Validasi detail barang
                const detailRows = $('#detailTableBody tr');
                if (detailRows.length === 0) {
                    isValid = false;
                    errorMessage += 'Minimal harus ada 1 detail barang.\n';
                }

                // Validasi setiap baris detail
                detailRows.each(function(index) {
                    const ukuran = $(this).find('input[name*="[ukuran]"]').val();
                    const warna = $(this).find('select[name*="[kode_warna]"]').val();

                    if (!ukuran || !warna) {
                        isValid = false;
                        errorMessage +=
                            `Detail barang baris ${index + 1}: Ukuran dan Warna harus diisi.\n`;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessage);
                    return false;
                }
            });

            // Format angka untuk input harga
            $(document).on('input',
                'input[name*="[harga_beli]"], input[name*="[harga_normal]"], input[name*="[potongan_harga]"]',
                function() {
                    let value = $(this).val().replace(/[^0-9]/g, '');
                    if (value) {
                        $(this).val(parseInt(value));
                    }
                });
        });
    </script>
@endpush
